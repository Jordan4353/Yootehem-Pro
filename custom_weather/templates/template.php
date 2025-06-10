<?php
// $props are the element properties set by the user or from element.php transform
// $attrs contains HTML attributes for the wrapping element (e.g., class, id)

// Ensure weather_data and weather_error are initialized to avoid undefined index notices
$weather_data = isset($props['weather_data']) ? $props['weather_data'] : null;
$weather_error = isset($props['weather_error']) ? $props['weather_error'] : null;

// Main wrapping element
$el = $this->el('div', [
    // Add the class from the element's settings
    'class' => [
        'el-element', // YOOtheme Pro specific class if needed, or custom
        'custom-weather-element',
        isset($props['class']) ? $props['class'] : '' // User-defined class from Advanced settings
    ],
    // Add the ID from the element's settings
    'id' => isset($props['id']) ? $props['id'] : null, // User-defined ID
    // Add custom attributes from Advanced settings
    'attrs' => isset($props['attributes']) ? $props['attributes'] : [],
]);

// Output user-defined CSS from the Advanced settings
if (!empty($props['css'])) {
    echo "<style>{$props['css']}</style>";
}

?>

<?= $el($props, $attrs) // Render the opening tag of the main element ?>

    <?php if ($weather_error) : ?>
        <div class="uk-alert uk-alert-danger">
            <p><?= esc_html($weather_error) ?></p>
        </div>
    <?php elseif ($weather_data) : ?>
        <div class="weather-details uk-panel">
            <h3 class="uk-panel-title uk-margin-remove-bottom">Weather in <?= esc_html($weather_data['location_name']) ?></h3>

            <div class="uk-grid-small uk-child-width-expand@s uk-text-center" uk-grid>
                <div>
                    <div class="uk-card uk-card-body uk-card-small">
                        <?php if ($weather_data['icon_url']) : ?>
                            <img src="<?= esc_url($weather_data['icon_url']) ?>" alt="<?= esc_attr($weather_data['condition']) ?>" class="weather-icon uk-align-center">
                        <?php endif; ?>
                        <p class="uk-text-large uk-margin-small"><?= esc_html($weather_data['temperature']) ?><?= esc_html($weather_data['units_label_temp']) ?></p>
                        <p class="uk-text-meta uk-margin-remove"><?= esc_html($weather_data['condition']) ?></p>
                    </div>
                </div>
                <div>
                    <div class="uk-card uk-card-body uk-card-small">
                        <p class="uk-text-emphasis">Details</p>
                        <ul class="uk-list uk-list-divider">
                            <li>Humidity: <?= esc_html($weather_data['humidity']) ?>%</li>
                            <li>Wind: <?= esc_html($weather_data['wind_speed']) ?> <?= esc_html($weather_data['units_label_wind']) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="uk-alert">
            <p>Weather data is currently unavailable. Please check the element settings or try again later.</p>
        </div>
    <?php endif; ?>

<?= $el->end() // Render the closing tag of the main element ?>
