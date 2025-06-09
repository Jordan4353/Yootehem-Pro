# YOOtheme Pro Custom YouTube Feed Element (Corrected)

This document provides updated instructions on how to use and integrate the custom YouTube Feed element into your YOOtheme Pro WordPress website, based on current YOOtheme Pro developer documentation.

## Files Created

The custom element consists of the following files:

-   `youtube-feed/` (Your element's root directory)
    -   `element.json`: Defines the element's settings, properties, icons, and templates for the YOOtheme Pro builder. It also imports `element.php`.
    -   `element.php`: Contains server-side PHP logic, specifically a `render` transform that fetches data from the YouTube API and prepares it for the templates by adding it to `$node->props`.
    -   `templates/`
        -   `template.php`: The main HTML/PHP template for rendering the visual output of the video feed.
        -   `content.php`: A simplified HTML/PHP template for search engine indexing and fallback content.
    -   `css/`
        -   `element.css`: Basic CSS styles for the element.
    -   `js/`
        -   `element.js`: Placeholder JavaScript file for future interactivity.
    -   `images/`
        -   `icon.svg`: Icon for the YOOtheme Pro element library.
        -   `iconSmall.svg`: Icon for the YOOtheme Pro builder panel.

## 1. Obtain a YouTube Data API Key

To use this element, you need a YouTube Data API v3 key.

1.  **Go to the Google Cloud Console:** [https://console.cloud.google.com/](https://console.cloud.google.com/)
2.  **Create a new project** (or select an existing one).
3.  **Enable the "YouTube Data API v3":**
    *   Navigate to "APIs & Services" > "Library".
    *   Search for "YouTube Data API v3" and enable it for your project.
4.  **Create Credentials:**
    *   Navigate to "APIs & Services" > "Credentials".
    *   Click "Create Credentials" > "API key".
    *   Your API key will be displayed. Copy it securely.
5.  **Restrict your API Key (Recommended):**
    *   From the Credentials page, click on the name of your API key.
    *   Under "API restrictions", select "Restrict key".
    *   From the dropdown, select "YouTube Data API v3".
    *   Consider further restrictions like "IP addresses" for server-side use if possible.
    *   Save the changes.

**Important:** Keep your API key confidential. This element uses it server-side in `element.php`.

## 2. Integration into YOOtheme Pro (WordPress)

YOOtheme Pro automatically detects custom elements placed in a child theme's `builder` directory.

1.  **Ensure you have a YOOtheme Pro child theme active.** If not, create one first (see YOOtheme Pro documentation for "Child Themes").
2.  **Create the element directory:**
    Navigate to your WordPress child theme's directory:
    `wp-content/themes/YOUR_CHILD_THEME_NAME/`
3.  **Inside your child theme, create a `builder` directory if it doesn't already exist.**
4.  **Inside the `builder` directory, create a directory for your custom element.** For this element, name it `youtube-feed`.
    The final path should look like:
    `wp-content/themes/YOUR_CHILD_THEME_NAME/builder/youtube-feed/`
5.  **Place all the generated element files (as listed above) into this `youtube-feed` directory, maintaining the subdirectory structure (templates, css, js, images).**

    Correct structure:
    ```
    YOUR_CHILD_THEME_NAME/
    └── builder/
        └── youtube-feed/  <-- This is where you copy all the element files
            ├── element.json
            ├── element.php
            ├── templates/
            │   ├── template.php
            │   └── content.php
            ├── css/
            │   └── element.css
            ├── js/
            │   └── element.js
            └── images/
                ├── icon.svg
                └── iconSmall.svg
    ```

6.  **No `config.php` modification is typically needed** for element discovery when using a child theme and this directory structure. YOOtheme Pro should automatically find and register the element.

## 3. How it Works (Key Files)

*   **`element.json`**: This is the main configuration file. It tells YOOtheme Pro about the element, its settings (fields), default values, and importantly:
    *   `"@import": "./element.php"`: Loads the PHP logic file.
    *   `"icon": "${url:images/icon.svg}"` (and `iconSmall`): Specifies the icons.
    *   `"templates": { "render": "./templates/template.php", "content": "./templates/content.php" }`: Defines which files render the element.
*   **`element.php`**: This file doesn't output HTML directly. Its primary role is to prepare data for the templates.
    *   It uses a `transforms['render']` function. This function is executed by YOOtheme Pro before `template.php` is rendered.
    *   Inside this function, it fetches data from the YouTube API using your settings (API Key, Channel ID).
    *   The fetched videos and any error messages are added to the `$node->props` object (e.g., `$node->props['videos']`, `$node->props['youtube_feed_error']`).
*   **`templates/template.php`**: This file generates the actual HTML for the element that users see. It accesses the video data and error messages from the `$props` array (e.g., `$props['videos']`, `$props['youtube_feed_error']`).
*   **`templates/content.php`**: Provides a simplified HTML version of the content for search engines and fallback purposes. It also accesses data from `$props`.

## 4. Dependencies

*   **PHP cURL Extension:** The `element.php` file uses cURL to make requests to the YouTube API. Ensure the cURL extension is installed and enabled on your web server.
*   **YOOtheme Pro (WordPress version):** This element is designed for YOOtheme Pro.

## 5. Using the Element

Once correctly installed:

1.  Open the YOOtheme Pro builder on a page or layout.
2.  You should find the "YouTube Feed" element in the element library (likely under the "Custom" group, or the group defined in `element.json`).
3.  Add it to your layout.
4.  Configure the settings in the builder:
    *   YouTube API Key
    *   YouTube Channel ID
    *   Number of Videos
    *   Layout (Grid/List)
    *   Display options for titles and descriptions.

## 6. Important Considerations & Potential Adjustments

*   **API Quotas:** The YouTube Data API has usage quotas. Consider caching if your site has high traffic (this element does not include caching).
*   **Error Handling:** `element.php` sets error messages in `$props['youtube_feed_error']`, which `template.php` displays using UIkit alert styling. You can customize this.
*   **Styling:** Customize styles in `css/element.css` or your child theme's custom CSS.
*   **JavaScript (`js/element.js`):** Currently a placeholder. Add custom JS here if needed.
*   **Security:** Keep your API key secure.
*   **YOOtheme Pro Updates:** Test custom elements after theme updates, as framework changes can occasionally affect them. Always refer to the latest YOOtheme Pro developer documentation if issues arise.

This updated guide should help you successfully integrate and use the custom YouTube Feed element.
```
