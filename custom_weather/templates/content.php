<?php
// $props are the element properties set by the user or from element.php transform

$weather_data_container = isset($props['weather_data']) && is_array($props['weather_data']) ? $props['weather_data'] : null;
$current_weather = isset($weather_data_container['current']) && is_array($weather_data_container['current']) ? $weather_data_container['current'] : null;
$forecast_weather = isset($weather_data_container['forecast']) && is_array($weather_data_container['forecast']) ? $weather_data_container['forecast'] : [];
$weather_error = isset($props['weather_error']) ? $props['weather_error'] : null;

// User display preferences (less critical for content.php, but could be used)
// $show_humidity = isset($props['show_humidity']) ? $props['show_humidity'] : true;
// $show_wind = isset($props['show_wind']) ? $props['show_wind'] : true;

if ($weather_error) {
    // For search content, outputting an error might not be desired.
    // echo "<p>Error: " . esc_html($weather_error) . "</p>";
} elseif ($current_weather) {
    $output = "<p>";
    $output .= "Current weather in " . esc_html($current_weather['location_name']) . ": ";
    $output .= esc_html($current_weather['temperature']) . esc_html($current_weather['units_label_temp']) . ", ";
    $output .= esc_html($current_weather['condition']) . ". ";

    // For content.php, usually better to include if available rather than check show_humidity/show_wind
    if (isset($current_weather['humidity'])) {
        $output .= "Humidity: " . esc_html($current_weather['humidity']) . "%. ";
    }
    if (isset($current_weather['wind_speed'])) {
        $output .= "Wind: " . esc_html($current_weather['wind_speed']) . " " . esc_html($current_weather['units_label_wind']) . ".";
    }
    $output .= "</p>";
    echo $output;

    if (!empty($forecast_weather)) {
        $forecast_output = "<p>Forecast: ";
        $forecast_entries = [];
        foreach ($forecast_weather as $forecast_day) {
            $forecast_entries[] = esc_html($forecast_day['date']) . ": " . esc_html($forecast_day['temp']) . (isset($weather_data_container['units_label_temp']) ? esc_html($weather_data_container['units_label_temp']) : "") . ", " . esc_html($forecast_day['condition']);
        }
        $forecast_output .= implode("; ", $forecast_entries) . ".</p>";
        echo $forecast_output;
    }
}
?>
