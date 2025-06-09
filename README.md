# YOOtheme Pro Custom YouTube Feed Element

This document provides instructions on how to use and integrate the custom YouTube Feed element into your YOOtheme Pro website.

## Files Created

The following files constitute the custom element:

-   `element.json`: Defines the element's settings and properties for the YOOtheme Pro builder.
-   `element.php`: Contains the server-side PHP logic to fetch data from the YouTube API and pass it to the template.
-   `templates/template.php`: The HTML/PHP template for rendering the video feed.
-   `css/element.css`: Basic CSS styles for the element.
-   `js/element.js`: Placeholder JavaScript file for future interactivity.

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
    *   Under "Application restrictions", you might consider restricting it to your web server's IP addresses for better security, though this can be complex to manage. HTTP referrer restrictions are also an option but can sometimes be bypassed. Start with no application restrictions if unsure, but be mindful of your quota.
    *   Save the changes.

**Important:** Keep your API key confidential. Do not embed it directly in client-side JavaScript if you can avoid it. This element uses it server-side in `element.php`.

## 2. Directory Structure and Integration

YOOtheme Pro typically loads custom elements from a child theme or a custom plugin. The exact location can sometimes vary based on YOOtheme Pro updates or specific setups, but a common practice for child themes is:

Assuming you have a YOOtheme Pro child theme active:

1.  **Create a directory for your custom element** within your child theme. A common path is:
    `wp-content/themes/YOUR_CHILD_THEME_NAME/builder/youtube-feed/`
    (For Joomla, the path would be `templates/YOUR_CHILD_THEME_NAME/builder/youtube-feed/`)

    If the `builder` directory doesn't exist in your child theme, create it.
    The `youtube-feed` part is the name of your element.

2.  **Place the generated files into this directory:**
    ```
    YOUR_CHILD_THEME_NAME/
    └── builder/
        └── youtube-feed/
            ├── element.json
            ├── element.php
            ├── templates/
            │   └── template.php
            ├── css/
            │   └── element.css
            └── js/
                └── element.js
    ```

3.  **Element Registration (Important):**
    YOOtheme Pro needs to know about your new element. This is often done via the child theme's `config.php` or a similar mechanism. You might need to add something like this to your child theme's `config.php` (usually located at `wp-content/themes/YOUR_CHILD_THEME_NAME/config.php` or `templates/YOUR_CHILD_THEME_NAME/config.php` for Joomla):

    ```php
    <?php
    // Ensure this file is being included by YOOtheme Pro.
    defined('_JEXEC') or defined('ABSPATH') or die;

    return [
        // ... other configurations ...

        'elements' => [
            // Register a directory for custom elements
            // The key 'myElements' can be anything unique
            'myYouTubeElement' => __DIR__ . '/builder/youtube-feed'
        ],

        // If your element has custom JavaScript that needs to be loaded,
        // you might also need to register it, though often YOOtheme Pro
        // automatically loads element.js if present.
        // Check YOOtheme Pro documentation for specifics on JS loading for elements.

        // ... other configurations ...
    ];
    ```
    **Note:** The exact method for element registration can change. **Always consult the latest YOOtheme Pro developer documentation** for the most up-to-date method of adding custom elements. If the `config.php` method above doesn't work, search their documentation for "custom elements" or "builder elements".

## 3. Dependencies

*   **PHP cURL Extension:** The `element.php` file uses cURL to make requests to the YouTube API. Ensure the cURL extension is installed and enabled on your web server.
*   **YOOtheme Pro:** This element is designed for YOOtheme Pro and relies on its framework.

## 4. Using the Element

Once correctly installed and registered:

1.  Open the YOOtheme Pro builder.
2.  You should find the "YouTube Feed" element in the list of available elements (likely under "Custom Elements" or the group you defined in `element.json`).
3.  Add it to your layout.
4.  Configure the settings:
    *   **YouTube API Key:** Your API key from step 1.
    *   **YouTube Channel ID:** The ID of the channel you want to display videos from (e.g., `UCxxxxxxxxxxxxxxxxx`). You can find this in the channel's URL or its "Advanced settings" on YouTube.
    *   **Number of Videos:** How many videos to show.
    *   **Layout:** Grid or List.
    *   **Show Video Titles/Descriptions:** Toggle visibility.
    *   **Description Max Length:** Control snippet length.

## 5. Important Considerations & Potential Adjustments

*   **API Quotas:** The YouTube Data API has usage quotas. If you make too many requests, your access might be temporarily blocked. Consider caching API responses if your site has high traffic (this element does not include caching by default).
*   **Error Handling:** The provided `element.php` has basic error handling. For a production site, you might want to implement more robust error logging or user-friendly messages.
*   **Styling:** The `css/element.css` provides basic styles. You will likely want to customize these to match your theme's design. You can override these styles in your child theme's custom CSS.
*   **JavaScript:** The `js/element.js` is currently a placeholder. For advanced features like lightboxes or AJAX "load more", you'll need to write custom JavaScript, potentially integrating with YOOtheme Pro's UIkit framework.
*   **Security:** Always keep your API key secure. The current setup uses it server-side, which is good.
*   **YOOtheme Pro Updates:** YOOtheme Pro updates can sometimes introduce changes that affect custom elements. It's good practice to test custom elements after theme updates.
*   **Alternative API Endpoints:** The current element uses the `search` endpoint to get videos by `channelId`. For specific playlists, you might need to adjust the API URL in `element.php` to use the `playlistItems` endpoint.

This guide should help you get started. Good luck!
