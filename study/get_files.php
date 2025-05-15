<?php
require_once '../config.php';

// Get parameters
$subject = isset($_GET['subject']) ? $_GET['subject'] : '';
$folderPath = isset($_GET['path']) ? $_GET['path'] : '';

// Debug information (will be included in response when needed)
$debug = [];

// Validate subject
$subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
if (!in_array($subject, $subjects)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid subject']);
    exit();
}

// Sanitize folder path for security
if (!empty($folderPath)) {
    $folderPath = str_replace('..', '', $folderPath); // Prevent directory traversal
    $folderPath = trim($folderPath, '/');
}

// Build the full server path
$baseDir = "/var/www/fci.ichiwi.me/material/";
$fullPath = "{$baseDir}{$subject}/";

if (!empty($folderPath)) {
    $fullPath .= "{$folderPath}/";
}

// Helper function to format file size for display
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Helper function to get appropriate font awesome icon class based on file extension
function getFileIconClass($extension) {
    $iconMap = [
        'pdf' => 'fa-file-pdf',
        'doc' => 'fa-file-word',
        'docx' => 'fa-file-word',
        'ppt' => 'fa-file-powerpoint',
        'pptx' => 'fa-file-powerpoint',
        'xls' => 'fa-file-excel',
        'xlsx' => 'fa-file-excel',
        'txt' => 'fa-file-alt',
        'zip' => 'fa-file-archive',
        'rar' => 'fa-file-archive',
        'jpg' => 'fa-file-image',
        'jpeg' => 'fa-file-image',
        'png' => 'fa-file-image',
        'gif' => 'fa-file-image',
    ];
    
    return isset($iconMap[$extension]) ? $iconMap[$extension] : 'fa-file';
}

// Process directories and files
$items = [];
try {
    if (is_dir($fullPath)) {
        $files = scandir($fullPath);
        
        foreach ($files as $file) {
            // Skip . and ..
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $path = $fullPath . $file;
            $relativePath = !empty($folderPath) ? $folderPath . '/' . $file : $file;
            
            if (is_dir($path)) {
                // This is a folder
                $items[] = [
                    'name' => $file,
                    'path' => $relativePath,
                    'type' => 'folder',
                    'icon' => 'fa-folder'
                ];
            } else {
                // This is a file
                $fileInfo = pathinfo($file);
                $extension = isset($fileInfo['extension']) ? strtolower($fileInfo['extension']) : '';
                $order = 0;
                
                // Check if file has an order prefix (e.g., "01_filename.pdf")
                if (preg_match('/^([0-9]+)_/', $fileInfo['filename'], $matches)) {
                    $order = (int)$matches[1];
                }
                
                $items[] = [
                    'name' => $file,
                    'path' => $relativePath,
                    'type' => 'file',
                    'extension' => $extension,
                    'icon' => getFileIconClass($extension),
                    'size' => formatFileSize(filesize($path)),
                    'order' => $order,
                    'url' => "/material/$subject/" . ($folderPath ? "$folderPath/" : "") . $file
                ];
            }
        }
        
        // Sort items: folders first, then files by order/name
        usort($items, function($a, $b) {
            // Folders come before files
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'folder' ? -1 : 1;
            }
            
            // If both are files and have different order numbers
            if ($a['type'] === 'file' && isset($a['order']) && isset($b['order']) && $a['order'] !== $b['order']) {
                return $a['order'] - $b['order'];
            }
            
            // Otherwise sort by name
            return strcmp($a['name'], $b['name']);
        });
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Folder not found',
            'subject' => $subject,
            'currentPath' => $folderPath,
            'items' => []
        ]);
        exit();
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Error processing directory',
        'subject' => $subject,
        'currentPath' => $folderPath,
        'items' => []
    ]);
    exit();
}

// Prepare the response
$response = [
    'success' => true,
    'subject' => $subject,
    'currentPath' => $folderPath,
    'items' => $items
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
