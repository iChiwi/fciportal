<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

require_once '../config.php';

// Initialize response
$response = ['success' => false];

// Define allowed file extensions and max file size
$allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'gif'];
$maxFileSize = 50 * 1024 * 1024; // 50MB

// Debug info
$response['debug'] = [];

// Material directory path
$baseDir = "/var/www/fci.ichiwi.me/material/";

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // File Upload
    if (isset($_FILES['file'])) {
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $folderPath = isset($_POST['folderPath']) ? $_POST['folderPath'] : '';
        
        $response['debug']['request'] = [
            'subject' => $subject,
            'folderPath' => $folderPath,
            'file' => $_FILES['file']['name']
        ];
        
        // Validate subject
        $subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
        if (!in_array($subject, $subjects)) {
            $response['error'] = 'Invalid subject';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        // Sanitize folder path
        if (!empty($folderPath)) {
            $folderPath = str_replace('..', '', $folderPath); // Prevent directory traversal
            $folderPath = trim($folderPath, '/');
        }
        
        // Build upload directory path
        $uploadDir = $baseDir . "$subject/";
        if (!empty($folderPath)) {
            $uploadDir .= "$folderPath/";
        }
        
        $response['debug']['paths'] = [
            'baseDir' => $baseDir,
            'uploadDir' => $uploadDir
        ];
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            $response['debug']['mkdir'] = "Attempting to create: $uploadDir";
            $mkdirResult = mkdir($uploadDir, 0755, true);
            $response['debug']['mkdirResult'] = $mkdirResult ? 'success' : 'failed';
            
            if (!$mkdirResult) {
                $response['debug']['mkdir_error'] = error_get_last();
                $response['error'] = 'Failed to create directory';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }
        }
        
        // Process the uploaded file
        $file = $_FILES['file'];
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $fileTemp = $file['tmp_name'];
        $fileError = $file['error'];
        
        $response['debug']['file'] = [
            'name' => $fileName,
            'size' => $fileSize,
            'temp' => $fileTemp,
            'error' => $fileError
        ];
        
        // Get file extension
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validate file
        if ($fileError !== 0) {
            $response['error'] = 'File upload error: ' . $fileError;
        } elseif (!in_array($fileExt, $allowedExtensions)) {
            $response['error'] = 'File type not allowed: ' . $fileExt;
        } elseif ($fileSize > $maxFileSize) {
            $response['error'] = 'File too large (max 50MB)';
        } else {
            // Generate unique filename with order if provided
            $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
            $uniqueFileName = $fileName;
            
            // Check for file order if provided
            $order = isset($_POST['order']) ? intval($_POST['order']) : 0;
            
            // Add order prefix if needed
            if ($order > 0) {
                // Remove any existing order prefix
                $fileNameWithoutExt = preg_replace('/^[0-9]+_/', '', $fileNameWithoutExt);
                $uniqueFileName = sprintf("%02d", $order) . "_" . $fileNameWithoutExt . "." . $fileExt;
            }
            
            // Ensure filename is unique
            $counter = 1;
            while (file_exists($uploadDir . $uniqueFileName)) {
                if ($order > 0) {
                    $uniqueFileName = sprintf("%02d", $order) . "_" . $fileNameWithoutExt . "_" . $counter . "." . $fileExt;
                } else {
                    $uniqueFileName = $fileNameWithoutExt . "_" . $counter . "." . $fileExt;
                }
                $counter++;
            }
            
            $uploadFilePath = $uploadDir . $uniqueFileName;
            $response['debug']['uploadFilePath'] = $uploadFilePath;
            
            // Move uploaded file to destination
            $moveResult = move_uploaded_file($fileTemp, $uploadFilePath);
            $response['debug']['moveResult'] = $moveResult ? 'success' : 'failed';
            
            if ($moveResult) {
                // Set proper permissions
                chmod($uploadFilePath, 0644);
                
                // Record the upload in database if needed
                try {
                    if (isset($pdo)) {
                        $stmt = $pdo->prepare("INSERT INTO uploaded_files (subject, filename, filepath, uploaded_by, file_size) 
                                    VALUES (:subject, :filename, :filepath, :admin, :filesize)");
                        
                        $filePath = $subject . '/' . ($folderPath ? $folderPath . '/' : '') . $uniqueFileName;
                        
                        $stmt->execute([
                            'subject' => $subject,
                            'filename' => $uniqueFileName,
                            'filepath' => $filePath,
                            'admin' => $_SESSION['admin'],
                            'filesize' => $fileSize
                        ]);
                    }
                    
                    $response = [
                        'success' => true,
                        'message' => 'File uploaded successfully',
                        'filename' => $uniqueFileName,
                        'filepath' => ($folderPath ? $folderPath . '/' : '') . $uniqueFileName,
                        'debug' => $response['debug']
                    ];
                } catch (Exception $e) {
                    // File uploaded successfully even if DB failed
                    $response = [
                        'success' => true,
                        'message' => 'File uploaded successfully',
                        'filename' => $uniqueFileName,
                        'filepath' => ($folderPath ? $folderPath . '/' : '') . $uniqueFileName,
                        'dbError' => $e->getMessage(),
                        'debug' => $response['debug']
                    ];
                }
            } else {
                $response['error'] = 'Failed to move uploaded file';
                $response['debug']['move_error'] = error_get_last();
            }
        }
    }
    
    // Create Folder
    elseif (isset($_POST['create_folder'])) {
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $folderPath = isset($_POST['folderPath']) ? $_POST['folderPath'] : '';
        $folderName = isset($_POST['folderName']) ? $_POST['folderName'] : '';
        
        $response['debug']['request'] = [
            'subject' => $subject,
            'folderPath' => $folderPath,
            'folderName' => $folderName
        ];
        
        // Validate subject
        $subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
        if (!in_array($subject, $subjects)) {
            $response['error'] = 'Invalid subject';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        // Validate folder name
        if (empty($folderName) || !preg_match('/^[a-zA-Z0-9_\-\s]+$/u', $folderName)) {
            $response['error'] = 'Invalid folder name';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        // Sanitize folder path
        if (!empty($folderPath)) {
            $folderPath = str_replace('..', '', $folderPath);
            $folderPath = trim($folderPath, '/');
        }
        
        // Build the new folder path
        $parentDir = $baseDir . "$subject/";
        if (!empty($folderPath)) {
            $parentDir .= "$folderPath/";
        }
        $newFolderPath = $parentDir . $folderName;
        
        $response['debug']['paths'] = [
            'parentDir' => $parentDir,
            'newFolderPath' => $newFolderPath
        ];
        
        // Create parent directory if needed
        if (!file_exists($parentDir)) {
            mkdir($parentDir, 0755, true);
        }
        
        // Create the new folder
        if (file_exists($newFolderPath)) {
            $response['error'] = 'Folder already exists';
        } else {
            $mkdirResult = mkdir($newFolderPath, 0755);
            $response['debug']['mkdirResult'] = $mkdirResult ? 'success' : 'failed';
            
            if ($mkdirResult) {
                $response = [
                    'success' => true,
                    'message' => 'Folder created successfully',
                    'folderPath' => ($folderPath ? $folderPath . '/' : '') . $folderName,
                    'debug' => $response['debug']
                ];
            } else {
                $response['error'] = 'Failed to create folder';
                $response['debug']['mkdir_error'] = error_get_last();
            }
        }
    }
    
    // Delete File
    elseif (isset($_POST['delete_file'])) {
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $folderPath = isset($_POST['folderPath']) ? $_POST['folderPath'] : '';
        $filePath = isset($_POST['filePath']) ? $_POST['filePath'] : '';
        
        $response['debug']['delete_request'] = [
            'subject' => $subject,
            'folderPath' => $folderPath,
            'filePath' => $filePath
        ];
        
        // Validate subject
        $subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
        if (!in_array($subject, $subjects)) {
            $response['error'] = 'Invalid subject';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        // Sanitize paths
        if (!empty($filePath)) {
            $filePath = str_replace('..', '', $filePath);
        }
        
        // Build the full file path
        $fullFilePath = $baseDir . "$subject/" . $filePath;
        
        $response['debug']['fullFilePath'] = $fullFilePath;
        
        // Check if file exists
        if (!file_exists($fullFilePath) || is_dir($fullFilePath)) {
            $response['error'] = 'File not found';
        } else {
            // Delete the file
            $unlinkResult = unlink($fullFilePath);
            $response['debug']['unlinkResult'] = $unlinkResult ? 'success' : 'failed';
            
            if ($unlinkResult) {
                // Delete from database if needed
                try {
                    if (isset($pdo)) {
                        $fileName = basename($filePath);
                        $stmt = $pdo->prepare("DELETE FROM uploaded_files WHERE subject = :subject AND filename = :filename");
                        $stmt->execute([
                            'subject' => $subject,
                            'filename' => $fileName
                        ]);
                    }
                } catch (Exception $e) {
                    $response['debug']['db_error'] = $e->getMessage();
                }
                
                $response = [
                    'success' => true,
                    'message' => 'File deleted successfully',
                    'debug' => $response['debug']
                ];
            } else {
                $response['error'] = 'Failed to delete file';
                $response['debug']['unlink_error'] = error_get_last();
            }
        }
    }
    
    // Delete Folder
    elseif (isset($_POST['delete_folder'])) {
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $folderPath = isset($_POST['folderPath']) ? $_POST['folderPath'] : '';
        
        $response['debug']['delete_folder_request'] = [
            'subject' => $subject,
            'folderPath' => $folderPath
        ];
        
        // Validate subject
        $subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
        if (!in_array($subject, $subjects)) {
            $response['error'] = 'Invalid subject';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        // Sanitize folder path
        if (empty($folderPath)) {
            $response['error'] = 'Folder path is required';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        $folderPath = str_replace('..', '', $folderPath);
        $folderPath = trim($folderPath, '/');
        
        // Build the full folder path
        $fullFolderPath = $baseDir . "$subject/$folderPath";
        
        $response['debug']['fullFolderPath'] = $fullFolderPath;
        
        // Check if folder exists
        if (!is_dir($fullFolderPath)) {
            $response['error'] = 'Folder not found';
        } else {
            // Function to recursively delete a directory with detailed error tracking
            function deleteDir($dirPath, &$errors = []) {
                if (!is_dir($dirPath)) {
                    $errors[] = "Path is not a directory: $dirPath";
                    return false;
                }
                
                // First make sure we can list directory contents
                if (!is_readable($dirPath)) {
                    $errors[] = "Directory not readable: $dirPath";
                    return false;
                }
                
                $files = array_diff(scandir($dirPath), ['.', '..']);
                $success = true;
                
                foreach ($files as $file) {
                    $path = $dirPath . '/' . $file;
                    
                    if (is_dir($path)) {
                        // Recursively delete subdirectory
                        if (!deleteDir($path, $errors)) {
                            $success = false;
                        }
                    } else {
                        // Try to make sure file is writable before unlinking
                        if (!is_writable($path)) {
                            @chmod($path, 0666); // Try to add write permission
                        }
                        
                        if (!unlink($path)) {
                            $errors[] = "Failed to delete file: $path - " . error_get_last()['message'];
                            $success = false;
                        }
                    }
                }
                
                // Make directory writable if needed
                if (!is_writable($dirPath)) {
                    @chmod($dirPath, 0777);
                }
                
                // Remove empty directory
                if (!rmdir($dirPath)) {
                    $errors[] = "Failed to remove directory: $dirPath - " . error_get_last()['message'];
                    $success = false;
                }
                
                return $success;
            }
            
            // Initialize error array
            $deleteErrors = [];
            
            // Delete the folder
            $deleteDirResult = deleteDir($fullFolderPath, $deleteErrors);
            $response['debug']['deleteDirResult'] = $deleteDirResult ? 'success' : 'failed';
            
            if (!empty($deleteErrors)) {
                $response['debug']['deleteErrors'] = $deleteErrors;
            }
            
            if ($deleteDirResult) {
                // Delete from database if needed
                try {
                    if (isset($pdo)) {
                        $stmt = $pdo->prepare("DELETE FROM uploaded_files WHERE subject = :subject AND filepath LIKE :folderPath");
                        $stmt->execute([
                            'subject' => $subject,
                            'folderPath' => "$subject/$folderPath/%"
                        ]);
                    }
                } catch (Exception $e) {
                    $response['debug']['db_error'] = $e->getMessage();
                }
                
                $response = [
                    'success' => true,
                    'message' => 'Folder deleted successfully',
                    'debug' => $response['debug']
                ];
            } else {
                $response['error'] = 'Failed to delete folder';
                $response['debug']['error_details'] = $deleteErrors;
                
                // Check ownership and permissions of folder
                $folderOwner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($fullFolderPath)) : 'Unknown';
                $folderPerms = substr(sprintf('%o', fileperms($fullFolderPath)), -4);
                $response['debug']['folder_info'] = [
                    'owner' => is_array($folderOwner) ? $folderOwner['name'] : $folderOwner,
                    'permissions' => $folderPerms
                ];
            }
        }
    }
    
    // Reorder File
    elseif (isset($_POST['reorder_file'])) {
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $folderPath = isset($_POST['folderPath']) ? $_POST['folderPath'] : '';
        $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
        $newOrder = isset($_POST['newOrder']) ? intval($_POST['newOrder']) : 0;
        
        $response['debug']['reorder_request'] = [
            'subject' => $subject,
            'folderPath' => $folderPath,
            'filename' => $filename,
            'newOrder' => $newOrder
        ];
        
        // Validate subject
        $subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
        if (!in_array($subject, $subjects)) {
            $response['error'] = 'Invalid subject';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        // Sanitize folder path
        if (!empty($folderPath)) {
            $folderPath = str_replace('..', '', $folderPath);
            $folderPath = trim($folderPath, '/');
        }
        
        // Build file path
        $dirPath = $baseDir . "$subject/";
        if (!empty($folderPath)) {
            $dirPath .= "$folderPath/";
        }
        $filePath = $dirPath . $filename;
        
        $response['debug']['filePath'] = $filePath;
        
        // Check if file exists
        if (!file_exists($filePath) || is_dir($filePath)) {
            $response['error'] = 'File not found';
        } else {
            // Get file info
            $fileInfo = pathinfo($filename);
            $fileExt = isset($fileInfo['extension']) ? $fileInfo['extension'] : '';
            $fileNameWithoutExt = isset($fileInfo['filename']) ? $fileInfo['filename'] : '';
            
            // Remove existing order prefix if any
            $cleanName = preg_replace('/^[0-9]+_/', '', $fileNameWithoutExt);
            
            // Create new filename with order prefix
            $newFilename = sprintf("%02d", $newOrder) . "_" . $cleanName . "." . $fileExt;
            $newFilePath = $dirPath . $newFilename;
            
            $response['debug']['newFilePath'] = $newFilePath;
            
            // Check if we're trying to rename to the same filename
            if ($filePath === $newFilePath) {
                $response = [
                    'success' => true,
                    'message' => 'File order unchanged',
                    'newFilename' => $newFilename,
                    'debug' => $response['debug']
                ];
            }
            // Handle existing files with the same order
            else if (file_exists($newFilePath)) {
                // Generate a unique name by appending a counter
                $counter = 1;
                $tempName = $cleanName;
                
                while (file_exists($newFilePath)) {
                    if ($counter > 1) {
                        // Remove the previous counter suffix if it exists
                        $tempName = preg_replace('/_\d+$/', '', $cleanName);
                    }
                    
                    $newFilename = sprintf("%02d", $newOrder) . "_" . $tempName . "_" . $counter . "." . $fileExt;
                    $newFilePath = $dirPath . $newFilename;
                    $counter++;
                }
                
                $renameResult = rename($filePath, $newFilePath);
                $response['debug']['renameResult'] = $renameResult ? 'success' : 'failed';
                
                if ($renameResult) {
                    // Update database if needed
                    try {
                        if (isset($pdo)) {
                            $stmt = $pdo->prepare("UPDATE uploaded_files SET filename = :newFilename 
                                        WHERE subject = :subject AND filename = :oldFilename");
                            
                            $stmt->execute([
                                'newFilename' => $newFilename,
                                'subject' => $subject,
                                'oldFilename' => $filename
                            ]);
                        }
                    } catch (Exception $e) {
                        $response['debug']['db_error'] = $e->getMessage();
                    }
                    
                    $response = [
                        'success' => true,
                        'message' => 'File reordered successfully',
                        'newFilename' => $newFilename,
                        'debug' => $response['debug']
                    ];
                } else {
                    $response['error'] = 'Failed to reorder file';
                    $response['debug']['rename_error'] = error_get_last();
                }
            } else {
                // Normal case - rename the file with new order
                $renameResult = rename($filePath, $newFilePath);
                $response['debug']['renameResult'] = $renameResult ? 'success' : 'failed';
                
                if ($renameResult) {
                    // Update database if needed
                    try {
                        if (isset($pdo)) {
                            $stmt = $pdo->prepare("UPDATE uploaded_files SET filename = :newFilename 
                                        WHERE subject = :subject AND filename = :oldFilename");
                            
                            $stmt->execute([
                                'newFilename' => $newFilename,
                                'subject' => $subject,
                                'oldFilename' => $filename
                            ]);
                        }
                    } catch (Exception $e) {
                        $response['debug']['db_error'] = $e->getMessage();
                    }
                    
                    $response = [
                        'success' => true,
                        'message' => 'File reordered successfully',
                        'newFilename' => $newFilename,
                        'debug' => $response['debug']
                    ];
                } else {
                    $response['error'] = 'Failed to reorder file';
                    $response['debug']['rename_error'] = error_get_last();
                }
            }
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
