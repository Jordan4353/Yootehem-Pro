<?php
// templates/content.php
// This template is used for search indexing and as a fallback.
// It should output plain text content.

// $props is available here, populated by element.php's render transform.

if (!empty($props['youtube_feed_error'])) {
    // Optionally, you could output the error for debugging in fallback,
    // but for search indexing, it's often better to output nothing or a generic message.
    // For now, let's keep it simple and not output technical errors here.
    // echo "<p>Error: " . htmlspecialchars($props['youtube_feed_error']) . "</p>";
    return;
}

if (empty($props['videos'])) {
    // echo "<p>No videos to display.</p>"; // Optional: message for fallback
    return;
}

// Output video titles and descriptions as simple text
foreach ($props['videos'] as $video) {
    if (!empty($props['show_title']) && !empty($video['title'])) {
        echo "<h3>" . htmlspecialchars($video['title']) . "</h3>
";
    }
    if (!empty($props['show_description']) && !empty($video['description'])) {
        // Use the full description here, not truncated, for better indexing.
        echo "<p>" . htmlspecialchars($video['description']) . "</p>

";
    }
}
?>
