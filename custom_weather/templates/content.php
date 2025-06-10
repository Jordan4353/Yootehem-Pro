<?php
// $props are the element properties set by the user or from element.php transform

$weather_data = isset($props['weather_data']) ? $props['weather_data'] : null;
$weather_error = isset($props['weather_error']) ? $props['weather_error'] : null; // Though errors are less relevant for static content

if ($weather_error) {
    // Optionally, you could output a simple error text, or nothing
    // echo "<p>Error: " . esc_html($weather_error) . "</p>";
    // For search content, it might be better to output nothing if there's an error or no data.
} elseif ($weather_data) {
    echo "<p>Weather in " . esc_html($weather_data['location_name']) . ": ";
    echo esc_html($weather_data['temperature']) . esc_html($weather_data['units_label_temp']) . ", ";
    echo esc_html($weather_data['condition']) . ". ";
    echo "Humidity: " . esc_html($weather_data['humidity']) . "%. ";
    echo "Wind: " . esc_html($weather_data['wind_speed']) . " " . esc_html($weather_data['units_label_wind']) . ".";
    echo "</p>";
}
?>
