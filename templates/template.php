<?php
// templates/template.php
// Main rendering template for the YouTube Feed element.

// $props is available here, populated by element.php's render transform.
// $props['videos'] contains the video data.
// $props['youtube_feed_error'] contains any error messages.
// Other settings like $props['layout'], $props['show_title'] are also in $props.


// Element settings are now in $props:
// e.g., $props['layout'], $props['show_title'], $props['video_count']
// Video data is in $props['videos']
// Error messages are in $props['youtube_feed_error']

// Handle and display errors first
if (!empty($props['youtube_feed_error'])) {
    echo '<div class="el-youtube-feed-error uk-alert uk-alert-warning" uk-alert>';
    echo '<a class="uk-alert-close" uk-close></a>';
    echo '<p>' . htmlspecialchars($props['youtube_feed_error']) . '</p>';
    echo '</div>';
    // Depending on the severity or type of error, you might want to stop further rendering.
    // If API keys are missing, perhaps nothing more should be shown.
    if (strpos($props['youtube_feed_error'], 'API Key and Channel ID are required') !== false) {
        return; // Stop if critical configuration is missing
    }
}

if (empty($props['videos'])) {
    // If there was no error, but videos are empty, it means "no videos found".
    if (empty($props['youtube_feed_error'])) {
        echo '<div class="el-youtube-feed-empty uk-alert">';
        echo '<p>No videos found for the specified channel or criteria.</p>';
        echo '</div>';
    }
    return; // Nothing more to display if no videos (and error already handled or "no videos" message shown)
}

// Use element-specific class names to help with styling and avoid conflicts
$element_base_class = 'el-youtube-feed';
$layout_class = isset($props['layout']) ? htmlspecialchars($element_base_class . '-' . $props['layout']) : htmlspecialchars($element_base_class . '-grid');

// Extract display settings for easier use, with defaults
$show_title = isset($props['show_title']) ? $props['show_title'] : true;
$show_description = isset($props['show_description']) ? $props['show_description'] : false;
$description_max_length = isset($props['description_max_length']) ? (int)$props['description_max_length'] : 100;

?>

<div class="<?php echo htmlspecialchars($element_base_class); ?> <?php echo $layout_class; ?>">

    <?php foreach ($props['videos'] as $video): ?>
        <?php
        $video_url = 'https://www.youtube.com/watch?v=' . urlencode($video['id']);
        $thumbnail_url = !empty($video['thumbnail_high']) ? $video['thumbnail_high'] : (!empty($video['thumbnail_medium']) ? $video['thumbnail_medium'] : $video['thumbnail_default']);

        $description_text = '';
        if ($show_description && !empty($video['description'])) {
            if (strlen($video['description']) > $description_max_length) {
                $description_text = substr($video['description'], 0, $description_max_length) . '...';
            } else {
                $description_text = $video['description'];
            }
        }
        ?>
        <div class="<?php echo htmlspecialchars($element_base_class . '__video-item'); ?>">
            <?php if (!empty($thumbnail_url)): ?>
                <div class="<?php echo htmlspecialchars($element_base_class . '__thumbnail'); ?>">
                    <a href="<?php echo htmlspecialchars($video_url); ?>" target="_blank" rel="noopener noreferrer">
                        <img src="<?php echo htmlspecialchars($thumbnail_url); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>">
                    </a>
                </div>
            <?php endif; ?>

            <div class="<?php echo htmlspecialchars($element_base_class . '__details'); ?>">
                <?php if ($show_title && !empty($video['title'])): ?>
                    <h3 class="<?php echo htmlspecialchars($element_base_class . '__title'); ?>">
                        <a href="<?php echo htmlspecialchars($video_url); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo htmlspecialchars($video['title']); ?>
                        </a>
                    </h3>
                <?php endif; ?>

                <?php if ($show_description && !empty($description_text)): ?>
                    <p class="<?php echo htmlspecialchars($element_base_class . '__description'); ?>">
                        <?php echo htmlspecialchars($description_text); ?>
                    </p>
                <?php endif; ?>

                <?php /*
                // Optional: Display publish date
                if (!empty($video['published_at'])) {
                    // Ensure date formatting is safe and consider localization if needed
                    try {
                        $date = new DateTime($video['published_at']);
                        echo '<p class="' . htmlspecialchars($element_base_class . '__date') . '">Published: ' . htmlspecialchars($date->format('F j, Y')) . '</p>';
                    } catch (Exception $e) {
                        // Handle potential DateTime parse error
                        // echo '<p class="' . htmlspecialchars($element_base_class . '__date') . '">Published: ' . htmlspecialchars($video['published_at']) . '</p>';
                    }
                }
                */ ?>
            </div>
        </div>
    <?php endforeach; ?>

</div>
