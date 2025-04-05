<?php
header('Content-Type: application/json');

// Log received data
error_log("Received upload request");
error_log("POST data: " . json_encode($_POST));
error_log("FILES data: " . json_encode($_FILES));

// Check if an image was uploaded
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/images/products/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to create upload directory',
                'path' => $uploadDir
            ]);
            exit;
        }
    }
    
    // Generate a unique filename
    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
    $uploadPath = $uploadDir . $filename;
    
    // Try to move the uploaded file
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file' => [
                'name' => $_FILES['image']['name'],
                'type' => $_FILES['image']['type'],
                'size' => $_FILES['image']['size'],
                'path' => '/images/products/' . $filename
            ]
        ]);
    } else {
        $uploadError = error_get_last();
        echo json_encode([
            'success' => false,
            'error' => 'Failed to move uploaded file',
            'php_error' => $uploadError ? $uploadError['message'] : 'Unknown error',
            'file' => [
                'name' => $_FILES['image']['name'],
                'type' => $_FILES['image']['type'],
                'size' => $_FILES['image']['size'],
                'tmp_name' => $_FILES['image']['tmp_name']
            ]
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No file uploaded or upload error',
        'files' => $_FILES
    ]);
}