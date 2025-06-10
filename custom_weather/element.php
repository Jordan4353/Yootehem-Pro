<?php

return [
    'transforms' => [
        'render' => function ($node, array $params) {
            if (!isset($node->props)) {
                $node->props = [];
            }

            // Get settings
            $api_key = !empty($node->props['api_key']) ? $node->props['api_key'] : null;
            $manual_location = !empty($node->props['location']) ? $node->props['location'] : null;
            $units = !empty($node->props['units']) ? $node->props['units'] : 'metric';
            $cache_duration = !empty($node->props['cache_duration']) ? intval($node->props['cache_duration']) : 3600;

            // New settings
            $auto_location_enabled = !empty($node->props['auto_location']) ? $node->props['auto_location'] : false;
            $forecast_days = !empty($node->props['forecast_days']) ? intval($node->props['forecast_days']) : 0;
            // show_humidity and show_wind are for template logic, not directly used in PHP data fetching beyond being part of cache key perhaps.

            $node->props['weather_data'] = null;
            $node->props['weather_error'] = null;
            $determined_location_query = null;
            $location_source = ''; // To inform the template if needed

            if (empty($api_key)) {
                $node->props['weather_error'] = 'OpenWeatherMap API Key is missing in element settings.';
                return true;
            }

            // 1. Determine Location (Auto or Manual)
            if ($auto_location_enabled) {
                // Simple way to get client IP. More robust methods exist (checking various headers).
                $client_ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;

                // Handle specific IPs for local testing or known non-public ranges if necessary
                // For example, you might want to skip geolocation for '127.0.0.1' or '::1'
                // or provide a test IP if $client_ip is in a private range.
                // This example doesn't add a public test IP by default to avoid unexpected behavior.
                // Consider that `$_SERVER['REMOTE_ADDR']` might not always be the true client IP if behind a proxy.
                // For production, ensure X-Forwarded-For or similar headers are trusted and checked if applicable.

                if ($client_ip && !in_array($client_ip, ['127.0.0.1', '::1'])) { // Avoid local calls
                    $geo_api_url = 'http://ip-api.com/json/' . $client_ip; // Free tier is HTTP
                    $geo_response = wp_remote_get($geo_api_url, ['timeout' => 5]);

                    if (!is_wp_error($geo_response) && wp_remote_retrieve_response_code($geo_response) === 200) {
                        $geo_body = wp_remote_retrieve_body($geo_response);
                        $geo_data = json_decode($geo_body, true);
                        if ($geo_data && $geo_data['status'] === 'success' && !empty($geo_data['city'])) {
                            $determined_location_query = $geo_data['city'];
                            if (!empty($geo_data['countryCode'])) {
                                $determined_location_query .= ',' . $geo_data['countryCode'];
                            }
                            $location_source = 'auto';
                        } else {
                            $node->props['weather_error'] = 'Automatic location detection failed (API status: ' . ($geo_data['status'] ?? 'unknown') . '). ';
                        }
                    } else {
                         $node->props['weather_error'] = 'Geolocation service error. ';
                         if(is_wp_error($geo_response)) $node->props['weather_error'] .= $geo_response->get_error_message();
                    }
                } elseif (in_array($client_ip, ['127.0.0.1', '::1'])) {
                    $node->props['weather_error'] = 'Automatic location detection skipped for local address. ';
                }
                else {
                    $node->props['weather_error'] = 'Could not determine client IP for automatic location. ';
                }

                if (empty($determined_location_query) && !empty($manual_location)) {
                    $determined_location_query = $manual_location;
                    $location_source = 'manual_fallback';
                    $node->props['weather_error'] = ($node->props['weather_error'] ?? '') . 'Using manual location as fallback.';
                } elseif (empty($determined_location_query) && empty($manual_location)) {
                     $node->props['weather_error'] = ($node->props['weather_error'] ?? '') . 'Manual location is also empty.';
                     return true;
                }
            } else {
                if (empty($manual_location)) {
                    $node->props['weather_error'] = 'Manual Location is not set and auto-detection is off.';
                    return true;
                }
                $determined_location_query = $manual_location;
                $location_source = 'manual';
            }

            if(empty($determined_location_query)) {
                if(empty($node->props['weather_error'])) { // Should be set if auto-detect failed
                    $node->props['weather_error'] = 'Location could not be determined.';
                }
                return true;
            }

            // 2. Caching Logic
            $transient_key_parts = [
                'custom_weather_v2b', // increment version for structure changes
                strtolower($determined_location_query),
                $units,
                $forecast_days
            ];
             // md5 each part to keep key length reasonable and consistent
            $transient_key_hashed_parts = array_map('md5', $transient_key_parts);
            $transient_key = 'cw_' . implode('_', $transient_key_hashed_parts); // cw_ prefix for custom weather

            $cached_data = get_transient($transient_key);

            if ($cached_data !== false && is_array($cached_data)) { // Ensure cache is array
                $node->props['weather_data'] = $cached_data;
                // location_source might change per request even if weather data is cached for the location
                $node->props['weather_data']['location_source'] = $location_source;
                $node->props['weather_data']['determined_location_for_display'] = $determined_location_query;
                return true;
            }

            // 3. Fetch Current Weather Data
            $current_weather_data = null;
            $api_error_current = null;

            $current_api_url = sprintf(
                'https://api.openweathermap.org/data/2.5/weather?q=%s&units=%s&appid=%s',
                rawurlencode($determined_location_query), $units, $api_key
            );
            $response = wp_remote_get($current_api_url, ['timeout' => 10]);

            if (is_wp_error($response)) {
                $api_error_current = 'Error fetching current weather: ' . $response->get_error_message();
            } else {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                $http_code = wp_remote_retrieve_response_code($response);

                if ($http_code !== 200 || empty($data) || !isset($data['weather'])) {
                    $api_error_current = 'Could not retrieve valid current weather data.';
                    if (!empty($data['message'])) {
                        $api_error_current .= ' API: ' . esc_html($data['message']);
                    } elseif ($http_code !== 200) {
                        $api_error_current .= ' HTTP Error: ' . $http_code;
                    }
                } else {
                    $current_weather_data = [
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
                }
            }

            if ($api_error_current) {
                 $node->props['weather_error'] = ($node->props['weather_error'] ? $node->props['weather_error'] . ' ' : '') . $api_error_current;
            }

            // 4. Fetch Forecast Data
            $forecast_weather_data_processed = [];
            $api_error_forecast = null;

            if ($forecast_days > 0) {
                // OpenWeatherMap free tier gives 5 days, 3-hourly. cnt = 5 days * 8 intervals/day = 40
                // We request up to forecast_days (max 5 for free tier)
                $effective_forecast_days_for_api = min($forecast_days, 5);
                $cnt_param = $effective_forecast_days_for_api * 8;

                $forecast_api_url = sprintf(
                    'https://api.openweathermap.org/data/2.5/forecast?q=%s&units=%s&appid=%s&cnt=%d',
                    rawurlencode($determined_location_query), $units, $api_key, $cnt_param
                );
                $forecast_response = wp_remote_get($forecast_api_url, ['timeout' => 10]);

                if (is_wp_error($forecast_response)) {
                    $api_error_forecast = 'Error fetching forecast: ' . $forecast_response->get_error_message();
                } else {
                    $forecast_body = wp_remote_retrieve_body($forecast_response);
                    $forecast_api_data = json_decode($forecast_body, true);
                    $forecast_http_code = wp_remote_retrieve_response_code($forecast_response);

                    if ($forecast_http_code !== 200 || empty($forecast_api_data) || !isset($forecast_api_data['list'])) {
                        $api_error_forecast = 'Could not retrieve valid forecast data.';
                        if (!empty($forecast_api_data['message'])) {
                            $api_error_forecast .= ' API: ' . esc_html($forecast_api_data['message']);
                        }
                    } else {
                        $daily_entries = [];
                        foreach ($forecast_api_data['list'] as $item) {
                            $date = gmdate('Y-m-d', $item['dt']);
                            // Group by date, then select one entry (e.g., midday)
                             if (!isset($daily_entries[$date])) {
                                $daily_entries[$date] = [];
                            }
                            $daily_entries[$date][] = $item;
                        }

                        $processed_count = 0;
                        foreach ($daily_entries as $date => $entries_for_day) {
                            if($processed_count >= $effective_forecast_days_for_api) break;

                            // Select entry closest to midday (12:00 PM UTC)
                            $midday_entry = null;
                            foreach($entries_for_day as $entry) {
                                if(gmdate('H', $entry['dt']) >= 12) {
                                    $midday_entry = $entry;
                                    break;
                                }
                            }
                            if(!$midday_entry) $midday_entry = $entries_for_day[0]; // fallback to first entry if no midday one

                            $forecast_weather_data_processed[] = [
                                'date' => gmdate('D, M j', $midday_entry['dt']),
                                'temp' => isset($midday_entry['main']['temp']) ? round($midday_entry['main']['temp']) : 'N/A',
                                'condition' => isset($midday_entry['weather'][0]['description']) ? ucfirst($midday_entry['weather'][0]['description']) : 'N/A',
                                'icon_url' => isset($midday_entry['weather'][0]['icon']) ? 'https://openweathermap.org/img/wn/' . $midday_entry['weather'][0]['icon'] . '@2x.png' : null,
                            ];
                            $processed_count++;
                        }
                    }
                }
                if($api_error_forecast){
                    $node->props['weather_error'] = ($node->props['weather_error'] ? $node->props['weather_error'] . ' ' : '') . $api_error_forecast;
                }
            }

            if (empty($current_weather_data) && empty($forecast_weather_data_processed) && !empty($node->props['weather_error'])) {
                return true; // All data fetching failed, error already set
            }

            $node->props['weather_data'] = [
                'current' => $current_weather_data,
                'forecast' => $forecast_weather_data_processed,
                'location_source' => $location_source,
                'determined_location_for_display' => $determined_location_query,
                'units_label_temp' => ($units === 'metric') ? '°C' : '°F', // For forecast
                'units_label_wind' => ($units === 'metric') ? 'm/s' : 'mph', // For forecast (if wind added later)
            ];

            if (!empty($current_weather_data) || !empty($forecast_weather_data_processed)) {
                 set_transient($transient_key, $node->props['weather_data'], $cache_duration);
            }

            return true;
        },
    ],
];
