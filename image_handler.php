<?php
// Image Storage Configurations
$config = [
    'internal_dir' => 'png_penting/', // Internal directory
    'external_dir' => '/var/www/uploads/sdn_kauman02/news/' // External directory
];

function displayImage($filename, $config) {
    // Check internal directory
    $internal_path = $config['internal_dir'] . $filename;
    if (file_exists($internal_path)) {
        $image_path = $internal_path;
    } else {
        // Check external directory
        $external_path = $config['external_dir'] . $filename;
        if (file_exists($external_path)) {
            $image_path = $external_path;
        } else {
            // Image not found
            header('HTTP/1.0 404 Not Found');
            echo 'Image not found';
            exit;
        }
    }

    // Detect mime type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $image_path);
    finfo_close($finfo);

    // Allowed mime types
    $allowed_mime_types = [
        'image/jpeg', 
        'image/png', 
        'image/gif'
    ];

    if (in_array($mime_type, $allowed_mime_types)) {
        header('Content-Type: ' . $mime_type);
        readfile($image_path);
        exit;
    }

    // Invalid image type
    header('HTTP/1.0 403 Forbidden');
    echo 'Invalid image type';
    exit;
}

// Handle image request
if (isset($_GET['image'])) {
    $filename = basename($_GET['image']); // Sanitize filename
    displayImage($filename, $config);
}
?>