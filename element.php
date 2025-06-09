<?php

// Corresponds to element.php from YOOtheme Pro documentation
// This file should return an array with 'transforms', 'updates', etc.

return [

    // Define transforms for the element node
    'transforms' => [

        // The 'render' function is executed before the template.php is rendered
        'render' => function ($node, array $params) {
            // $node->props contains the element's settings from element.json
            // $params contains context like $params['builder']

            // --- Start of our YouTube Data Fetching Logic ---

            $api_key = isset($node->props['api_key']) ? $node->props['api_key'] : '';
            $channel_id = isset($node->props['channel_id']) ? $node->props['channel_id'] : '';
            $video_count = isset($node->props['video_count']) ? (int)$node->props['video_count'] : 6;

            // Add a placeholder for videos and errors in props
            $node->props['videos'] = [];
            $node->props['youtube_feed_error'] = '';

            if (empty($api_key) || empty($channel_id)) {
                $node->props['youtube_feed_error'] = 'YouTube API Key and Channel ID are required and configured in the element settings.';
                // As per YOOtheme docs on "Collapsing Layout",
                // returning false prevents rendering if essential content is missing.
                // However, we might want to show the error message in the template.
                // So, we'll let it render and the template can check for the error.
                // If you strictly want it to collapse, return false here.
                // For now, we'll allow rendering to show the error.
                // return false; // Uncomment if strict collapse is needed
                return; // Exit the transform if essential keys are missing but still render to show error
            }

            $api_url = sprintf(
                'https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&order=date&maxResults=%d&channelId=%s&key=%s',
                $video_count,
                $channel_id,
                $api_key
            );

            $videos_data = [];
            $error_message_for_prop = '';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $api_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($curl);
            curl_close($curl);

            if ($curl_error) {
                $error_message_for_prop = 'API Request Error (cURL): ' . htmlspecialchars($curl_error);
            } elseif ($http_code !== 200) {
                $api_error_msg = 'Unknown API error';
                if ($response) {
                    $error_data = json_decode($response, true);
                    if (isset($error_data['error']['message'])) {
                        $api_error_msg = $error_data['error']['message'];
                    }
                }
                $error_message_for_prop = sprintf('API Request Error (HTTP %d): %s', $http_code, htmlspecialchars($api_error_msg));
            } else {
                $data = json_decode($response, true);
                if (isset($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if (isset($item['id']['videoId']) && isset($item['snippet'])) {
                            $videos_data[] = [
                                'id' => $item['id']['videoId'],
                                'title' => isset($item['snippet']['title']) ? $item['snippet']['title'] : 'No title',
                                'description' => isset($item['snippet']['description']) ? $item['snippet']['description'] : '',
                                'thumbnail_default' => isset($item['snippet']['thumbnails']['default']['url']) ? $item['snippet']['thumbnails']['default']['url'] : '',
                                'thumbnail_medium' => isset($item['snippet']['thumbnails']['medium']['url']) ? $item['snippet']['thumbnails']['medium']['url'] : '',
                                'thumbnail_high' => isset($item['snippet']['thumbnails']['high']['url']) ? $item['snippet']['thumbnails']['high']['url'] : '',
                                'published_at' => isset($item['snippet']['publishedAt']) ? $item['snippet']['publishedAt'] : '',
                            ];
                        }
                    }
                    if (empty($videos_data) && empty($data['items'])) { // No items found, not an error but empty result
                         $error_message_for_prop = 'No videos found for this channel, or the channel has no recent uploads.';
                    }
                } elseif (isset($data['error'])) {
                    $error_message_for_prop = 'API Error: ' . htmlspecialchars($data['error']['message']);
                } else {
                    $error_message_for_prop = 'Could not parse YouTube API response or no videos found.';
                }
            }

            if (!empty($error_message_for_prop)) {
                $node->props['youtube_feed_error'] = $error_message_for_prop;
            }

            $node->props['videos'] = $videos_data;

            // According to docs, to prevent rendering if content is empty (e.g. title AND content)
            // return $node->props['title'] || $node->props['content'];
            // In our case, we want to render even if there's an error (to show the error).
            // If videos array is empty AND no error, it means no videos found, which is a valid state to render (showing "no videos").
            // So, we don't need to return false here unless API keys are missing and we choose strict collapse.
            // If API keys are missing, we already set an error and returned early from the transform.
        },
    ],

    // Define updates for the element node (optional, for version migrations)
    'updates' => [
        // Example:
        // '1.0.1' => function ($node, array $params) {
        //     // Make changes to $node->props if the element was saved with an older version
        //     if (isset($node->props['old_setting'])) {
        //         $node->props['new_setting'] = $node->props['old_setting'];
        //         unset($node->props['old_setting']);
        //     }
        // },
    ],

];

?>
