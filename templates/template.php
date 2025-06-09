<?php
// https://proloyalweb.com

// templates/template.php

if (!empty($props['youtube_feed_error'])) {
    echo '<div class="el-youtube-feed-error uk-alert uk-alert-warning" uk-alert>';
    echo '<a class="uk-alert-close" uk-close></a>';
    echo '<p>' . htmlspecialchars($props['youtube_feed_error']) . '</p>';
    echo '</div>';
    if (strpos($props['youtube_feed_error'], 'API Key') !== false || strpos($props['youtube_feed_error'], 'Content Source ID') !== false) {
        return;
    }
}

if (empty($props['videos'])) {
    if (empty($props['youtube_feed_error'])) {
        echo '<div class="el-youtube-feed-empty uk-alert">';
        echo '<p>No videos found for the specified criteria.</p>';
        echo '</div>';
    }
    return;
}

$element_base_class = 'el-youtube-feed';
$layout_class = isset($props['layout']) ? htmlspecialchars($element_base_class . '-' . $props['layout']) : htmlspecialchars($element_base_class . '-grid');

$show_title = isset($props['show_title']) ? $props['show_title'] : true;
$show_description = isset($props['show_description']) ? $props['show_description'] : false;
$description_max_length = isset($props['description_max_length']) ? (int)$props['description_max_length'] : 100;

// Player & Privacy Options from props
$use_nocookie = isset($props['use_youtube_nocookie']) ? $props['use_youtube_nocookie'] : true;
$player_controls = isset($props['player_controls']) ? $props['player_controls'] : true;
$player_modest_branding = isset($props['player_modest_branding']) ? $props['player_modest_branding'] : false;
$player_loop = isset($props['player_loop']) ? $props['player_loop'] : false;

$base_embed_url = $use_nocookie ? 'https://www.youtube-nocookie.com/embed/' : 'https://www.youtube.com/embed/';

?>

<div class="<?php echo htmlspecialchars($element_base_class); ?> <?php echo $layout_class; ?>">

    <?php foreach ($props['videos'] as $video): ?>
        <?php
        // Construct embed URL with player parameters
        $embed_params = [];
        $embed_params['autoplay'] = 0; // Standard: no autoplay unless explicitly clicked later (lightbox)
        $embed_params['controls'] = $player_controls ? 1 : 0;
        if ($player_modest_branding) {
            // modestbranding=1 is tricky. It works in conjunction with controls=0 sometimes,
            // or requires Flash player. For HTML5, its effect is minimal.
            // A common approach for "less branding" is often to ensure 'showinfo=0' (deprecated)
            // and 'modestbranding=1'. Since showinfo is gone, modestbranding's effect is limited.
            // We'll include it as requested.
            $embed_params['modestbranding'] = 1;
        }
        if ($player_loop) {
            $embed_params['loop'] = 1;
            // For loop to work on a single video, 'playlist' param must be set to the video ID.
            // If it's a playlist source, YouTube handles looping the playlist.
            if ($props['source_type'] === 'videos' && count($props['videos']) === 1) {
                $embed_params['playlist'] = $video['id'];
            }
        }
        // rel=0 to limit related videos to the same channel (mostly, YouTube's behavior can vary)
        $embed_params['rel'] = 0;

        $video_embed_url = $base_embed_url . urlencode($video['id']) . '?' . http_build_query($embed_params);

        // Original watch link (can be kept for other purposes if needed, or remove if lightbox always used)
        // $video_watch_url = 'https://www.youtube.com/watch?v=' . urlencode($video['id']);

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
                    <a href="<?php echo htmlspecialchars($video_embed_url); ?>"
                       data-youtube-id="<?php echo htmlspecialchars($video['id']); ?>"
                       class="el-youtube-feed__videolink" <?php /* Add class for JS targeting for lightbox */ ?>
                       target="_blank" rel="noopener noreferrer">
                        <img src="<?php echo htmlspecialchars($thumbnail_url); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>">
                    </a>
                </div>
            <?php endif; ?>

            <div class="<?php echo htmlspecialchars($element_base_class . '__details'); ?>">
                <?php if ($show_title && !empty($video['title'])): ?>
                    <h3 class="<?php echo htmlspecialchars($element_base_class . '__title'); ?>">
                        <a href="<?php echo htmlspecialchars($video_embed_url); ?>"
                           data-youtube-id="<?php echo htmlspecialchars($video['id']); ?>"
                           class="el-youtube-feed__videolink" <?php /* Add class for JS targeting for lightbox */ ?>
                           target="_blank" rel="noopener noreferrer">
                            <?php echo htmlspecialchars($video['title']); ?>
                        </a>
                    </h3>
                <?php endif; ?>

                <?php if ($show_description && !empty($description_text)): ?>
                    <p class="<?php echo htmlspecialchars($element_base_class . '__description'); ?>">
                        <?php echo htmlspecialchars($description_text); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

</div>
