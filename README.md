# Show Image Custom Field Errors

Makes custom fields for images visible when there is an error, e.g. empty required fields.

## Purpose

Image fields have three view modes: square grid, proportional grid, and vertical list. In square grid and proportional grid modes only the thumbnail is visible and [custom fields](https://processwire.com/blog/posts/pw-3.0.142/#custom-fields-for-files-images) for an image are hidden until the thumbnail is clicked.

This can cause an issue when any of the custom fields is an error state (e.g. a required that has been left empty) because the relevant field will not be visible in the Page Edit interface.

The Show Image Custom Field Errors module forces image fields into vertical list mode when there is an error in a custom field. When the error is resolved the image field is returned to the view mode that was in use before the error occurred.

![Image](https://github.com/user-attachments/assets/e3af5c10-8baa-4c33-ba77-7f205cc0d5f9)
