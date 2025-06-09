// js/element.js

// Ensure this script is loaded after the DOM is ready,
// though YOOtheme Pro might handle script loading in its own way.
document.addEventListener('DOMContentLoaded', function() {
    // Example: Find all instances of our element on the page
    // This is a generic example; specific targeting might be needed.
    const youtubeFeedElements = document.querySelectorAll('.el-youtube-feed');

    if (youtubeFeedElements.length > 0) {
        console.log('YouTube Feed Element JS loaded. Found ' + youtubeFeedElements.length + ' element(s).');

        // You could add event listeners or manipulate elements here.
        // For example, to handle clicks on video items:
        // youtubeFeedElements.forEach(function(element) {
        //     const videoItems = element.querySelectorAll('.el-youtube-feed__video-item a');
        //     videoItems.forEach(function(itemLink) {
        //         itemLink.addEventListener('click', function(event) {
        //             // Example: Could prevent default and open in a custom lightbox
        //             // console.log('Video link clicked:', itemLink.href);
        //             // event.preventDefault(); // Uncomment to stop default link behavior
        //             // alert('Opening video: ' + itemLink.href); // Placeholder for lightbox
        //         });
        //     });
        // });
    }
});

// Note: For more complex interactions, especially within the YOOtheme Pro
// builder or with its APIs (like opening modals/lightboxes), you would
// typically use YOOtheme Pro's JavaScript framework (e.g., UIkit) and
// might need to register your script or component in a specific way defined
// by YOOtheme Pro's developer documentation.
