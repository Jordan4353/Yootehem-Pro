<?php

// Ensure this file is being included by YOOtheme Pro or a similar framework.
// This is a basic check; a real environment might have a more robust one.
defined('_JEXEC') or defined('ABSPATH') or die;

class ElementYouTubeFeed {

    /**
     * Main render function for the element.
     *
     * @param array $props The element properties (settings).
     * @return string The rendered HTML output, or an error message.
     */
    public static function render($props) {
        // Extract properties with defaults
        $api_key = isset($props['api_key']) ? $props['api_key'] : '';
        $channel_id = isset($props['channel_id']) ? $props['channel_id'] : '';
        $video_count = isset($props['video_count']) ? (int)$props['video_count'] : 6;
        // $layout = isset($props['layout']) ? $props['layout'] : 'grid'; // Layout handled in template
        // $show_title = isset($props['show_title']) ? (bool)$props['show_title'] : true;
        // $show_description = isset($props['show_description']) ? (bool)$props['show_description'] : false;
        // $description_max_length = isset($props['description_max_length']) ? (int)$props['description_max_length'] : 100;


        if (empty($api_key) || empty($channel_id)) {
            return '<p>Error: YouTube API Key and Channel ID are required.</p>';
        }

        // Construct the YouTube API URL
        // This typically fetches channel uploads. Adjust 'playlistId' based on actual needs (e.g., search, specific playlist).
        // For simplicity, we'll use search to get videos from a channel.
        // Note: Using 'channelId' with 'search' endpoint is a common way.
        // The 'order=date' ensures newest videos first.
        $api_url = sprintf(
            'https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&order=date&maxResults=%d&channelId=%s&key=%s',
            $video_count,
            $channel_id,
            $api_key
        );

        $videos = [];
        $error_message = '';

        // Use cURL to fetch data from YouTube API
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 10 seconds timeout
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); // Should be true in production
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);   // Should be 2 in production

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            $error_message = '<p>API Request Error (cURL): ' . htmlspecialchars($curl_error) . '</p>';
        } elseif ($http_code !== 200) {
            $error_data = json_decode($response, true);
            $api_error_msg = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'Unknown API error';
            $error_message = sprintf('<p>API Request Error (HTTP %d): %s</p>', $http_code, htmlspecialchars($api_error_msg));
        } else {
            $data = json_decode($response, true);
            if (isset($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (isset($item['id']['videoId']) && isset($item['snippet'])) {
                        $videos[] = [
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
            } elseif (isset($data['error'])) {
                 $error_message = '<p>API Error: ' . htmlspecialchars($data['error']['message']) . '</p>';
            } else {
                $error_message = '<p>Could not parse YouTube API response or no videos found.</p>';
            }
        }

        if (!empty($error_message)) {
            // In a real YOOtheme Pro element, you might log this error instead of displaying it directly
            // or provide a more user-friendly message.
            // For debugging, we'll return it.
            return $error_message;
        }

        // Pass data to the template
        // YOOtheme Pro would typically have a way to load a template file associated with the element.
        // For now, we'll assume a function `load_template` exists or handle it directly.
        // We'll pass both props and videos to the template.
        $template_data = array_merge($props, ['videos' => $videos]);

        // This is a placeholder for how YOOtheme Pro might load a sub-template.
        // The actual mechanism will depend on YOOtheme Pro's API.
        // We'll assume the template file is in 'templates/template.php' relative to this element's root.
        $template_path = __DIR__ . '/templates/template.php';

        if (file_exists($template_path)) {
            ob_start();
            // Make $template_data available to the included file
            extract($template_data);
            include $template_path;
            return ob_get_clean();
        } else {
            return '<p>Error: Element template file not found.</p>';
        }
    }
}

// Example of how YOOtheme Pro might call this (simplified):
// $props = [ /* ... props from element.json defaults + user settings ... */ ];
// echo ElementYouTubeFeed::render($props);

?>
