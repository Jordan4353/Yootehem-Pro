# YOOtheme Pro Custom YouTube Feed Element (Corrected & Enhanced - v1.2)

This document provides updated instructions for the enhanced YouTube Feed custom element for YOOtheme Pro WordPress. This version includes a reorganized settings panel, dynamic content capabilities, multiple content sources, player customization, caching, and privacy options.

## Files Created (Structure)

(File structure remains the same as v1.1 - no changes here)
-   `youtube-feed/`
    -   `element.json`
    -   `element.php`
    -   `templates/` (template.php, content.php)
    -   `css/` (element.css)
    -   `js/` (element.js)
    -   `images/` (icon.svg, iconSmall.svg)

## 1. Obtain a YouTube Data API Key

(This section remains the same - ensure you have a valid YouTube Data API v3 key.)

## 2. Integration into YOOtheme Pro (WordPress)

(This section remains the same - place the `youtube-feed` directory into your child theme's `builder` folder: `wp-content/themes/YOUR_CHILD_THEME_NAME/builder/youtube-feed/`)

## 3. Element Settings (Organized into Tabs)

The element settings are now organized into three main tabs in the YOOtheme Pro builder: "Content", "Settings", and "Advanced".

### Content Tab:
*   **API Key**: Your YouTube Data API v3 Key (Required).
    *   ✨ *This field supports YOOtheme Pro's Dynamic Content feature.*
*   **Content Source**: Choose how to fetch videos:
    *   `YouTube Channel`: Displays recent videos from a channel.
        *   **YouTube Channel ID**: The ID of the channel (e.g., `UCXXXXX`). Required if selected.
            *   ✨ *This field supports Dynamic Content.*
    *   `YouTube Playlist`: Displays videos from a specific playlist.
        *   **YouTube Playlist ID**: The ID of the playlist (e.g., `PLXXXXX`). Required if selected.
            *   ✨ *This field supports Dynamic Content.*
    *   `Specific Video(s)`: Displays one or more specific videos.
        *   **Specific Video IDs**: Comma-separated YouTube Video IDs (e.g., `videoID1,videoID2`). Max 50. Required if selected.
            *   ✨ *This field supports Dynamic Content.*
*   **Max Videos to Display**: Number of videos to fetch (applies to Channel and Playlist sources, max 50).

### Settings Tab:
This tab groups configuration for layout, display, player appearance, and caching.

*   **Layout & Display (Heading)**
    *   **Layout**: `Grid` or `List`.
    *   **Show Video Titles**: Checkbox.
    *   **Show Video Descriptions**: Checkbox.
    *   **Description Max Length**: Number input (shown if descriptions are enabled).
*   **Player Options (Heading)**
    *   **Show Player Controls**: Checkbox.
    *   **Modest YouTube Branding**: Checkbox.
    *   **Loop Video(s)**: Checkbox.
*   **Caching (Heading)**
    *   **Cache Duration (Minutes)**: How long to store API results (0 to disable). Default: `60`.
    *   **Cache Info (Description)**: "To manually clear the cache for this element during editing, append `?clear_yt_cache=YOUR_ELEMENT_ID` to the URL in the builder and reload the page. (YOUR_ELEMENT_ID will be visible in advanced settings once the element is saved)."

### Advanced Tab:
This tab contains privacy settings and is also where YOOtheme Pro adds its standard advanced element settings (like Name, Status, ID, Class, Attributes, CSS).

*   **Privacy (Heading)**
    *   **Use YouTube Nocookie Domain**: Checkbox (default enabled). Embeds using `youtube-nocookie.com`.
*   **(Standard YOOtheme Pro Advanced Settings)**: Fields for Element Name, Status, ID, Class, Custom Attributes, Custom CSS, etc., are typically available here, added by YOOtheme Pro itself.

### ✨ Using Dynamic Content

Fields marked with ✨ (API Key, Channel ID, Playlist ID, Video IDs) now support YOOtheme Pro's Dynamic Content feature. This means instead of manually typing in these values, you can pull them from other sources within your WordPress site, such as:

*   **Custom Fields:** If you have a post or page with custom fields storing your YouTube IDs.
*   **Site Settings:** Global site options or settings managed elsewhere.
*   **Post Data:** (Less common for these specific fields, but possible for some use cases).

To use it, click the "Dynamic" button next to the field in the YOOtheme Pro builder and select your content source and field. This is very useful for reusing API keys or managing video sources centrally.

## 4. How it Works (Key File Logic)

(This section remains largely the same, explaining `element.json` definition, `element.php` data fetching/caching, and template rendering.)

## 5. Dependencies

(This section remains the same: PHP cURL, YOOtheme Pro, WordPress Transients.)

## 6. Important Considerations

(This section remains largely the same, API quotas, cache clearing, styling, JS, etc.)

This updated guide reflects the new settings organization and Dynamic Content capabilities.
```
