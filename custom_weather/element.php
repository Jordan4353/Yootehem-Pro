<?php

return [
    // Define transforms for the element node
    'transforms' => [
        // The function is executed before the template is rendered
        'render' => function ($node, array $params) {
            // Ensure props are available
            if (!isset($node->props)) {
                $node->props = []; // Initialize if not set
            }

            // Get settings from element properties
            $api_key = !empty($node->props['api_key']) ? $node->props['api_key'] : null;
            $location = !empty($node->props['location']) ? $node->props['location'] : null;
            $units = !empty($node->props['units']) ? $node->props['units'] : 'metric';
            $cache_duration = !empty($node->props['cache_duration']) ? intval($node->props['cache_duration']) : 3600;

            // Default weather data and error
            $node->props['weather_data'] = null;
            $node->props['weather_error'] = null;

            // If API key or location is missing, don't render and set an error message for the template
            if (empty($api_key) || empty($location)) {
                $node->props['weather_error'] = 'Please provide both API Key and Location in the element settings.';
                // Returning true to allow the template to render the error message
                // If we return false, nothing renders, not even a message in the builder.
                return true;
            }

            // Create a unique transient key
            $transient_key = 'custom_weather_' . md5(strtolower($location) . '_' . $units);

            // Try to get cached data
            $cached_data = get_transient($transient_key);

            if ($cached_data !== false) {
                $node->props['weather_data'] = $cached_data;
                return true; // Use cached data
            }

            // If no cache, fetch data from API
            $api_url = sprintf(
                'https://api.openweathermap.org/data/2.5/weather?q=%s&units=%s&appid=%s',
                rawurlencode($location),
                $units,
                $api_key
            );

            $response = wp_remote_get($api_url, ['timeout' => 10]); // 10 second timeout

            if (is_wp_error($response)) {
                $node->props['weather_error'] = 'Error fetching weather data: ' . $response->get_error_message();
                return true; // Allow template to show this error
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            $http_code = wp_remote_retrieve_response_code($response);

            if ($http_code !== 200 || empty($data) || !isset($data['weather'])) {
                $error_message = 'Could not retrieve valid weather data.';
                if (!empty($data['message'])) {
                    $error_message .= ' API Error: ' . esc_html($data['message']);
                } elseif ($http_code !== 200) {
                    $error_message .= ' HTTP Error: ' . $http_code;
                }
                $node->props['weather_error'] = $error_message;
                return true; // Allow template to show this error
            }

            // Process and store relevant data
            $weather_info = [
                'location_name' => isset($data['name']) ? $data['name'] : 'N/A',
                'temperature' => isset($data['main']['temp']) ? round($data['main']['temp']) : 'N/A',
                'condition' => isset($data['weather'][0]['description']) ? ucfirst($data['weather'][0]['description']) : 'N/A',
                'icon_code' => isset($data['weather'][0]['icon']) ? $data['weather'][0]['icon'] : null,
                'humidity' => isset($data['main']['humidity']) ? $data['main']['humidity'] : 'N/A',
                'wind_speed' => isset($data['wind']['speed']) ? $data['wind']['speed'] : 'N/A',
                'units_label_temp' => ($units === 'metric') ? '°C' : '°F',
                'units_label_wind' => ($units === 'metric') ? 'm/s' : 'mph',
                'icon_url' => isset($data['weather'][0]['icon']) ? 'https://openweathermap.org/img/wn/' . $data['weather'][0]['icon'] . '@2x.png' : null,
            ];

            $node->props['weather_data'] = $weather_info;

            // Cache the data
            set_transient($transient_key, $weather_info, $cache_duration);

            return true; // Data fetched and processed
        },
    ],
];
