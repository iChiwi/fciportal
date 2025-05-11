<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once '../config.php';

// Get subject parameter
$subject = $_GET['subject'] ?? '';

// Validate subject
$subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
if (!in_array($subject, $subjects)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'المادة غير صالحة']);
    exit();
}

// Build the base directory path
$baseDir = dirname($_SERVER['DOCUMENT_ROOT']) . "/material/$subject/";

// Recursive function to get all folders and subfolders
function getFolders($dir, $base = '') {
    $folders = [];
    
    // Add root folder
    if (empty($base)) {
        $folders[] = [
            'path' => '',
            'name' => 'المجلد الرئيسي'
        ];
    }
    
    // Ensure directory exists
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    
    $items = scandir($dir);
    
    foreach ($items as $item) {
        // Skip . and .. and hidden files
        if ($item[0] === '.') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            // Calculate relative path
            $relativePath = $base ? $base . '/' . $item : $item;
            
            // Add current folder
            $folders[] = [
                'path' => $relativePath,
                'name' => $item
            ];
            
            // Add subfolders recursively
            $subfolders = getFolders($path, $relativePath);
            $folders = array_merge($folders, $subfolders);
        }
    }
    
    return $folders;
}

// Get all folders
try {
    $folders = getFolders($baseDir);
    
    // Sort folders by path
    usort($folders, function($a, $b) {
        return strcmp($a['path'], $b['path']);
    });
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'subject' => $subject,
        'folders' => $folders
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}
?>
