<?php
// Helper function to get image source (handles both URLs and filenames)
function getImageSrc($foto) {
    if (empty($foto)) {
        return 'image/avatar.png'; // Default image
    }
    
    if (filter_var($foto, FILTER_VALIDATE_URL)) {
        return $foto; // It's already a full URL
    } else {
        return 'image/' . $foto; // It's a filename, prepend image/
    }
}
?>
