<?php
// Image display helper function
function displayEventImage($image_url, $alt = 'Event Image', $class = 'img-fluid', $style = '') {
    if ($image_url && file_exists($image_url)) {
        return '<img src="' . htmlspecialchars($image_url) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" style="' . $style . '">';
    } else {
        // Default placeholder image
        return '<div class="placeholder-image" style="background: #f8f9fa; border: 2px dashed #dee2e6; padding: 2rem; text-align: center; color: #6c757d;">
                    <i class="fas fa-image fa-3x mb-2"></i><br>
                    <small>No image uploaded</small>
                </div>';
    }
}

// Image upload validation
function validateImageUpload($file) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload failed.';
        return $errors;
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = 'Image size must be less than 5MB.';
    }
    
    // Check file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        $errors[] = 'Invalid image format. Please upload JPG, PNG, GIF, or WebP files.';
    }
    
    return $errors;
}
?>



