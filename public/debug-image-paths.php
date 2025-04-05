<?php
header('Content-Type: application/json');

$baseDir = __DIR__;
$imagesDir = $baseDir . '/images';
$productsImagesDir = $baseDir . '/images/products';

// Check directory permissions
function getPermissions($path) {
    if (!file_exists($path)) {
        return [
            'exists' => false,
            'permissions' => null,
            'readable' => false,
            'writable' => false
        ];
    }
    
    $perms = fileperms($path);
    $permissions = substr(sprintf('%o', $perms), -4);
    
    return [
        'exists' => true,
        'permissions' => $permissions,
        'readable' => is_readable($path),
        'writable' => is_writable($path),
        'owner' => function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($path))['name'] : fileowner($path),
        'group' => function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($path))['name'] : filegroup($path)
    ];
}

// List images in a directory
function listImages($dir) {
    if (!is_dir($dir)) {
        return [];
    }
    
    $images = [];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    foreach (new DirectoryIterator($dir) as $file) {
        if ($file->isFile()) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, $allowedExtensions)) {
                $images[] = [
                    'name' => $file->getFilename(),
                    'path' => str_replace(__DIR__, '', $file->getPathname()),
                    'size' => $file->getSize(),
                    'permissions' => substr(sprintf('%o', $file->getPerms()), -4)
                ];
            }
        }
    }
    
    return $images;
}

// Prepare response
$response = [
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'script_path' => __FILE__,
    'base_dir' => $baseDir,
    'directories' => [
        'base' => getPermissions($baseDir),
        'images' => getPermissions($imagesDir),
        'products_images' => getPermissions($productsImagesDir)
    ],
    'images' => []
];

// Try to create directories if they don't exist
if (!is_dir($imagesDir)) {
    $mkdirResult = mkdir($imagesDir, 0755, true);
    $response['directories']['images_created'] = $mkdirResult;
    if ($mkdirResult) {
        $response['directories']['images'] = getPermissions($imagesDir);
    }
}

if (!is_dir($productsImagesDir)) {
    $mkdirResult = mkdir($productsImagesDir, 0755, true);
    $response['directories']['products_images_created'] = $mkdirResult;
    if ($mkdirResult) {
        $response['directories']['products_images'] = getPermissions($productsImagesDir);
    }
}

// List images
if (is_dir($productsImagesDir)) {
    $response['images'] = listImages($productsImagesDir);
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);