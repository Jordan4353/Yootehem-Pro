<?php
// Prevent direct access to this file
defined('_JEXEC') or defined('ABSPATH') or die;

// Element settings are available here (e.g., $layout, $show_title, $video_count)
// Video data is in $videos array

if (empty($videos)) {
    echo '<p>No videos found or an error occurred.</p>';
    return;
}

// Use element-specific class names to help with styling and avoid conflicts
$element_base_class = 'el-youtube-feed';
?>

<div class="<?php echo htmlspecialchars($element_base_class); ?> <?php echo htmlspecialchars($element_base_class . '-' . $layout); ?>">

    <?php foreach ($videos as $video): ?>
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
                    echo '<p class="' . htmlspecialchars($element_base_class . '__date') . '">Published: ' . htmlspecialchars(date('F j, Y', strtotime($video['published_at']))) . '</p>';
                }
                */ ?>
            </div>
        </div>
    <?php endforeach; ?>

</div>
