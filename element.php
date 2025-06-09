<?php
// https://proloyalweb.com
return [
    'transforms' => [
        'render' => function ($node, array $params) {
            // --- Element Settings ---
            $api_key = isset($node->props['api_key']) ? $node->props['api_key'] : '';
            $source_type = isset($node->props['source_type']) ? $node->props['source_type'] : 'channel';
            $channel_id = isset($node->props['channel_id']) ? $node->props['channel_id'] : '';
            $playlist_id = isset($node->props['playlist_id']) ? $node->props['playlist_id'] : '';
            $video_ids_str = isset($node->props['video_ids']) ? $node->props['video_ids'] : '';
            $video_count = isset($node->props['video_count']) ? (int)$node->props['video_count'] : 6;
            $cache_duration_minutes = isset($node->props['cache_duration']) ? (int)$node->props['cache_duration'] : 60;
            $cache_duration_seconds = $cache_duration_minutes * 60;

            // Initialize props for template
            $node->props['videos'] = [];
            $node->props['youtube_feed_error'] = '';

            // --- Basic Validation ---
            if (empty($api_key)) {
                $node->props['youtube_feed_error'] = 'YouTube API Key is required.';
                return;
            }

            $valid_source = false;
            if ($source_type === 'channel' && !empty($channel_id)) $valid_source = true;
            if ($source_type === 'playlist' && !empty($playlist_id)) $valid_source = true;
            if ($source_type === 'videos' && !empty($video_ids_str)) $valid_source = true;

            if (!$valid_source) {
                $node->props['youtube_feed_error'] = 'A valid Content Source ID (Channel, Playlist, or Video IDs) is required.';
                return;
            }

            // --- Caching Setup ---
            $transient_key_base = 'yt_feed_' . ($params['id'] ?? md5(json_encode($node->props))); // Use element ID if available, else hash props
            $query_params_for_key = [
                'source' => $source_type,
                'count' => $video_count,
                'cid' => $channel_id,
                'pid' => $playlist_id,
                'vids' => $video_ids_str
            ];
            $transient_key = $transient_key_base . '_' . md5(http_build_query($query_params_for_key));

            // --- Manual Cache Clearing (Basic) ---
            // Accessing $_GET directly in a transform might not be ideal or always possible depending on YOOtheme's execution context.
            // A more robust solution would be a WP AJAX action or a dedicated settings page.
            // This is a simplified example.
            if (isset($_GET['clear_yt_cache']) && $_GET['clear_yt_cache'] === ($params['id'] ?? '')) {
                delete_transient($transient_key);
                $node->props['youtube_feed_error'] = 'Cache cleared for this element. Refresh to fetch new data.'; // Temporary message
            }

            if ($cache_duration_seconds > 0) {
                $cached_videos = get_transient($transient_key);
                if ($cached_videos !== false && is_array($cached_videos)) {
                    $node->props['videos'] = $cached_videos;
                    $node->props['youtube_feed_error'] = '<!-- Loaded from cache -->'; // Optional debug message
                    return;
                }
            }

            // --- API URL Construction ---
            $api_url = '';
            $api_base = 'https://www.googleapis.com/youtube/v3/';

            switch ($source_type) {
                case 'channel':
                    // Search endpoint is often used to get recent videos from a channel
                    // Alternatively, playlistItems with the channel's "uploads" playlistId could be used.
                    // For simplicity with 'order=date', search is fine.
                    $api_url = $api_base . sprintf(
                        'search?part=snippet&type=video&order=date&maxResults=%d&channelId=%s&key=%s',
                        $video_count, $channel_id, $api_key
                    );
                    break;
                case 'playlist':
                    $api_url = $api_base . sprintf(
                        'playlistItems?part=snippet&maxResults=%d&playlistId=%s&key=%s',
                        $video_count, $playlist_id, $api_key
                    );
                    break;
                case 'videos':
                    $video_ids_array = array_map('trim', explode(',', $video_ids_str));
                    $video_ids_sanitized = array_filter($video_ids_array, function($vid) { return !empty($vid); });
                    if (empty($video_ids_sanitized)) {
                        $node->props['youtube_feed_error'] = 'No valid Video IDs provided.';
                        return;
                    }
                    // YouTube API limits 50 video IDs per request for the 'videos' endpoint.
                    $video_ids_chunk = array_slice($video_ids_sanitized, 0, 50);
                    $api_url = $api_base . sprintf(
                        'videos?part=snippet,contentDetails&id=%s&key=%s', // contentDetails for duration
                        implode(',', $video_ids_chunk), $api_key
                    );
                    break;
            }

            if (empty($api_url)) {
                $node->props['youtube_feed_error'] = 'Could not determine API endpoint.';
                return;
            }

            // --- API Call ---
            $videos_data = [];
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($curl);
            curl_close($curl);

            // --- Response Processing ---
            if ($curl_error) {
                $node->props['youtube_feed_error'] = 'API Request Error (cURL): ' . htmlspecialchars($curl_error);
            } elseif ($http_code !== 200) {
                $api_error_msg = 'Unknown API error';
                if ($response) {
                    $error_data = json_decode($response, true);
                    $api_error_msg = $error_data['error']['message'] ?? $api_error_msg;
                }
                $node->props['youtube_feed_error'] = sprintf('API Request Error (HTTP %d): %s', $http_code, htmlspecialchars($api_error_msg));
            } else {
                $data = json_decode($response, true);
                if (isset($data['error'])) {
                    $node->props['youtube_feed_error'] = 'API Error: ' . htmlspecialchars($data['error']['message']);
                } elseif (isset($data['items']) && is_array($data['items'])) {
                    if (empty($data['items'])) {
                         $node->props['youtube_feed_error'] = 'No videos found for the specified criteria.';
                    }
                    foreach ($data['items'] as $item) {
                        $snippet = $item['snippet'] ?? null;
                        $video_id = '';

                        if ($source_type === 'playlist' ) {
                            $video_id = $snippet['resourceId']['videoId'] ?? '';
                        } elseif ($source_type === 'channel') { // 'search' for channel
                            $video_id = $item['id']['videoId'] ?? '';
                        } elseif ($source_type === 'videos') {
                            $video_id = $item['id'] ?? '';
                        }

                        if (!$snippet || empty($video_id)) continue;

                        $videos_data[] = [
                            'id' => $video_id,
                            'title' => $snippet['title'] ?? 'No title',
                            'description' => $snippet['description'] ?? '',
                            'thumbnail_default' => $snippet['thumbnails']['default']['url'] ?? '',
                            'thumbnail_medium' => $snippet['thumbnails']['medium']['url'] ?? '',
                            'thumbnail_high' => $snippet['thumbnails']['high']['url'] ?? '',
                            'published_at' => $snippet['publishedAt'] ?? '',
                            // 'duration' => $source_type === 'videos' ? ($item['contentDetails']['duration'] ?? '') : '' // Requires contentDetails part
                        ];
                    }

                    if (!empty($videos_data) && $cache_duration_seconds > 0) {
                        set_transient($transient_key, $videos_data, $cache_duration_seconds);
                    }
                } else {
                    $node->props['youtube_feed_error'] = 'Could not parse YouTube API response or no items found.';
                }
            }
            $node->props['videos'] = $videos_data;

            // Player options are already in $node->props by default from element.json,
            // so no need to explicitly pass them here unless they need transformation.
        },
    ],
    'updates' => [], // Placeholder for future schema updates
];
?>
