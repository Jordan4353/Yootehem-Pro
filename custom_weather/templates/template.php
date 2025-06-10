<?php
// $props are the element properties set by the user or from element.php transform
// $attrs contains HTML attributes for the wrapping element (e.g., class, id)

// Retrieve new display settings
$show_humidity = isset($props['show_humidity']) ? $props['show_humidity'] : true; // Default to true if not set
$show_wind = isset($props['show_wind']) ? $props['show_wind'] : true; // Default to true if not set

// Main weather data and error
$weather_data_container = isset($props['weather_data']) && is_array($props['weather_data']) ? $props['weather_data'] : null;
$current_weather = isset($weather_data_container['current']) && is_array($weather_data_container['current']) ? $weather_data_container['current'] : null;
$forecast_weather = isset($weather_data_container['forecast']) && is_array($weather_data_container['forecast']) ? $weather_data_container['forecast'] : [];
$weather_error = isset($props['weather_error']) ? $props['weather_error'] : null;

// $location_source_msg = ''; // This logic is no longer directly displayed by default
// if (isset($weather_data_container['location_source'])) {
// if ($weather_data_container['location_source'] === 'auto') {
// $location_source_msg = '(Location automatically detected)';
// } elseif ($weather_data_container['location_source'] === 'manual_fallback') {
// $location_source_msg = '(Using manual location as fallback)';
// }
// }

// Main wrapping element
$el = $this->el('div', [
    'class' => [
        'el-element',
        'custom-weather-element',
        isset($props['class']) ? $props['class'] : ''
    ],
    'id' => isset($props['id']) ? $props['id'] : null,
    'attrs' => isset($props['attributes']) ? $props['attributes'] : [],
]);

if (!empty($props['css'])) {
    echo "<style>{$props['css']}</style>";
}

?>

<?= $el($props, $attrs) // Render the opening tag ?>

    <?php if ($weather_error) : ?>
        <div class="uk-alert uk-alert-danger">
            <p><?= esc_html($weather_error) ?></p>
            <?php if (strpos($weather_error, 'API Key is missing') === false && strpos($weather_error, 'Location could not be determined') === false && strpos($weather_error, 'Manual location is also empty') === false && strpos($weather_error, 'Manual Location is not set') === false && $current_weather && isset($current_weather['location_name'])) : ?>
                 <p class="uk-text-meta">Despite the error, some current weather data for <?= esc_html($current_weather['location_name']) ?> might be shown below if available from cache or partial fetch.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($current_weather) : ?>
        <div class="current-weather uk-panel uk-margin-bottom">
            <h3 class="uk-panel-title uk-margin-remove-bottom">
                Current Weather: <?= esc_html($current_weather['location_name']) ?>
            </h3>

            <div class="uk-grid-small uk-child-width-expand@s uk-text-center" uk-grid>
                <div>
                    <div class="uk-card uk-card-body uk-card-small">
                        <?php if (!empty($current_weather['icon_url'])) : ?>
                            <img src="<?= esc_url($current_weather['icon_url']) ?>" alt="<?= esc_attr($current_weather['condition']) ?>" class="weather-icon uk-align-center">
                        <?php endif; ?>
                        <p class="uk-text-large uk-margin-small"><?= esc_html($current_weather['temperature']) ?><?= esc_html($current_weather['units_label_temp']) ?></p>
                        <p class="uk-text-meta uk-margin-remove"><?= esc_html($current_weather['condition']) ?></p>
                    </div>
                </div>
                <?php if ($show_humidity || $show_wind) : ?>
                <div>
                    <div class="uk-card uk-card-body uk-card-small">
                        <p class="uk-text-emphasis">Details</p>
                        <ul class="uk-list uk-list-divider">
                            <?php if ($show_humidity && isset($current_weather['humidity'])) : ?>
                                <li>Humidity: <?= esc_html($current_weather['humidity']) ?>%</li>
                            <?php endif; ?>
                            <?php if ($show_wind && isset($current_weather['wind_speed'])) : ?>
                                <li>Wind: <?= esc_html($current_weather['wind_speed']) ?> <?= esc_html($current_weather['units_label_wind']) ?></li>
                            <?php endif; ?>
                        </ul>
                         <?php if ((!$show_humidity || !isset($current_weather['humidity'])) && (!$show_wind || !isset($current_weather['wind_speed']))) : ?>
                            <p class="uk-text-meta">Details display toggled off or not available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif (!$weather_error) : // Only show this if there's no specific API/config error and no current weather data ?>
        <div class="uk-alert">
            <p>Current weather data is currently unavailable. Please check settings or try again later.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($forecast_weather)) : ?>
        <div class="forecast-weather uk-margin-top">
            <h4 class="uk-heading-divider">Weather Forecast</h4>
            <div class="uk-grid-small uk-child-width-1-2@s uk-child-width-1-<?= count($forecast_weather) > 3 ? '4' : (count($forecast_weather) > 0 ? count($forecast_weather) : '1') ?>@m" uk-grid>
                <?php foreach ($forecast_weather as $forecast_day) : ?>
                    <div>
                        <div class="uk-card uk-card-body uk-card-small uk-text-center">
                            <p class="uk-text-bold uk-margin-small-bottom"><?= esc_html($forecast_day['date']) ?></p>
                            <?php if (!empty($forecast_day['icon_url'])) : ?>
                                <img src="<?= esc_url($forecast_day['icon_url']) ?>" alt="<?= esc_attr($forecast_day['condition']) ?>" class="weather-icon uk-align-center uk-margin-small-bottom" style="width:40px; height:40px;">
                            <?php endif; ?>
                            <p class="uk-margin-small"><?= esc_html($forecast_day['temp']) ?><?= esc_html($weather_data_container['units_label_temp'] ?? '') ?></p>
                            <p class="uk-text-meta uk-margin-remove uk-text-small"><?= esc_html($forecast_day['condition']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

<?= $el->end() // Render the closing tag ?>
