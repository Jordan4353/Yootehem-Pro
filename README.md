# YOOtheme Pro Custom YouTube Feed Element (Corrected & Enhanced - v1.1)

This document provides updated instructions for the enhanced YouTube Feed custom element for YOOtheme Pro WordPress. This version includes multiple content sources, player customization, caching, and privacy options.

## Files Created (Structure)

-   `youtube-feed/` (Your element's root directory)
    -   `element.json`: Defines settings, properties, icons, templates, and imports `element.php`.
    -   `element.php`: Contains server-side PHP logic (API calls, caching, data preparation via `render` transform).
    -   `templates/`
        -   `template.php`: Main HTML/PHP template for rendering the visual output of the video feed.
        -   `content.php`: Simplified HTML/PHP template for search indexing and fallback.
    -   `css/`
        -   `element.css`: Basic CSS styles.
    -   `js/`
        -   `element.js`: Placeholder JavaScript (future enhancements like lazy loading, lightbox).
    -   `images/`
        -   `icon.svg`: Icon for the YOOtheme Pro element library.
        -   `iconSmall.svg`: Icon for the YOOtheme Pro builder panel.

## 1. Obtain a YouTube Data API Key

(This section remains the same - ensure you have a valid YouTube Data API v3 key.)

1.  **Go to the Google Cloud Console:** [https://console.cloud.google.com/](https://console.cloud.google.com/)
2.  **Create/select a project.**
3.  **Enable "YouTube Data API v3".**
4.  **Create an API key** (APIs & Services > Credentials).
5.  **Restrict your API Key (Recommended).**

## 2. Integration into YOOtheme Pro (WordPress)

(This section remains the same - place the `youtube-feed` directory into your child theme's `builder` folder.)

-   Path: `wp-content/themes/YOUR_CHILD_THEME_NAME/builder/youtube-feed/`
-   YOOtheme Pro automatically detects elements in this directory. No `config.php` modification is needed.

## 3. Element Settings (Fields in `element.json`)

The element now offers the following settings, organized into tabs in the YOOtheme Pro builder:

### Content Tab:
*   **API Key**: Your YouTube Data API v3 Key (Required).
*   **Content Source**: Choose how to fetch videos:
    *   `YouTube Channel`: Displays recent videos from a channel.
        *   **YouTube Channel ID**: The ID of the channel (e.g., `UCXXXXX`). Required if this source is selected.
    *   `YouTube Playlist`: Displays videos from a specific playlist.
        *   **YouTube Playlist ID**: The ID of the playlist (e.g., `PLXXXXX`). Required if this source is selected.
    *   `Specific Video(s)`: Displays one or more specific videos.
        *   **Specific Video IDs**: Comma-separated list of YouTube Video IDs (e.g., `videoID1,videoID2`). Max 50. Required if this source is selected.
*   **Max Videos to Display**: Number of videos to fetch (applies to Channel and Playlist sources, max 50).

### Layout & Display Tab:
*   **Layout**: `Grid` or `List`.
*   **Show Video Titles**: Checkbox to display titles.
*   **Show Video Descriptions**: Checkbox to display descriptions.
*   **Description Max Length**: Number input to truncate descriptions (only if "Show Video Descriptions" is checked).

### Player & Privacy Tab:
*   **Player Options (Heading)**
*   **Show Player Controls**: Checkbox to show/hide YouTube player controls (e.g., play/pause, volume). Affects embedded player.
*   **Modest YouTube Branding**: Checkbox. Attempts to reduce YouTube logo visibility in the player (effectiveness can vary).
*   **Loop Video(s)**: Checkbox. Enables video looping. For single videos, it loops that video. For playlists, it loops the playlist.
*   **Privacy (Heading)**
*   **Use YouTube Nocookie Domain**: Checkbox (default enabled). Embeds videos using `youtube-nocookie.com` for enhanced privacy.

### Caching Tab:
*   **Caching (Heading)**
*   **Cache Duration (Minutes)**: How long to store YouTube API results in the cache (WordPress Transients). Set to `0` to disable caching. Default: `60` minutes.
*   **Cache Info (Description)**: Explains how to attempt manual cache clearing.
    *   "To manually clear the cache for this element during editing, append `?clear_yt_cache=YOUR_ELEMENT_ID` to the URL in the builder and reload the page. (YOUR_ELEMENT_ID will be visible in advanced settings once the element is saved)."

## 4. How it Works (Key File Logic)

*   **`element.json`**: Defines all settings and points to other files.
*   **`element.php`**:
    *   The `transforms['render']` function is key.
    *   It reads your settings from `$node->props`.
    *   **Content Fetching**: Based on "Content Source", it calls the appropriate YouTube API endpoint (search for channel, playlistItems for playlist, videos for specific IDs).
    *   **Caching**:
        *   It generates a unique cache key based on your settings.
        *   If valid cached data (a "transient" in WordPress) exists for this key, it uses that.
        *   Otherwise, it calls the API, and if successful, stores the video data in the cache for the specified "Cache Duration".
        *   The basic manual cache clear (via URL parameter) attempts to delete this transient.
    *   The fetched videos and any errors are added to `$node->props` (e.g., `$node->props['videos']`) for the templates.
*   **`templates/template.php`**:
    *   Renders the visual feed using data from `$props`.
    *   Constructs video embed URLs using "Player Options" and "Privacy" settings (e.g., choosing `youtube-nocookie.com`, adding `controls`, `loop` parameters).
*   **`templates/content.php`**: Provides simplified content for search engines/fallback.

## 5. Dependencies

*   PHP cURL Extension.
*   YOOtheme Pro (WordPress).
*   WordPress Transients API for caching (part of WordPress core).

## 6. Important Considerations

*   **API Quotas**: Caching helps, but be mindful of YouTube API usage.
*   **Manual Cache Clearing**: The URL parameter method is basic. For more robust clearing, especially on live sites without builder access, alternative methods might be needed (e.g., a small custom plugin or WP-CLI command).
*   **Styling & JavaScript**: Further customization can be done in `css/element.css` and `js/element.js`. Advanced features like lazy loading and lightboxes are planned for future updates.

This updated guide should help you make the most of the new features in the YouTube Feed element.
```
