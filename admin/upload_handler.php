<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

// Initialize response
$response = ['success' => false];
$response['debug'] = [];

$baseDir = "ENTER HERE"; // Base directory for materials from root

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['create_folder'])) {
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $folderPath = isset($_POST['folderPath']) ? $_POST['folderPath'] : '';
        $folderName = isset($_POST['folderName']) ? $_POST['folderName'] : '';
        
        $response['debug']['request'] = [
            'subject' => $subject,
            'folderPath' => $folderPath,
            'folderName' => $folderName
        ];
        
        $subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
        if (!in_array($subject, $subjects)) {
            $response['error'] = 'Invalid subject';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        if (empty($folderName) || !preg_match('/^[a-zA-Z0-9_\-\s]+$/u', $folderName)) {
            $response['error'] = 'Invalid folder name';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        if (!empty($folderPath)) {
            $folderPath = str_replace('..', '', $folderPath);
            $folderPath = trim($folderPath, '/');
        }
        
        $parentDir = $baseDir . "$subject/";
        if (!empty($folderPath)) {
            $parentDir .= "$folderPath/";
        }
        $newFolderPath = $parentDir . $folderName;
        
        $response['debug']['paths'] = [
            'parentDir' => $parentDir,
            'newFolderPath' => $newFolderPath
        ];
        
        if (!file_exists($parentDir)) {
            mkdir($parentDir, 0755, true);
        }
        
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
    
    elseif (isset($_POST['delete_file'])) {
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $folderPath = isset($_POST['folderPath']) ? $_POST['folderPath'] : '';
        $filePath = isset($_POST['filePath']) ? $_POST['filePath'] : '';
        
        $response['debug']['delete_request'] = [
            'subject' => $subject,
            'folderPath' => $folderPath,
            'filePath' => $filePath
        ];
        
        $subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
        if (!in_array($subject, $subjects)) {
            $response['error'] = 'Invalid subject';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        if (!empty($filePath)) {
            $filePath = str_replace('..', '', $filePath);
        }
        
        $fullFilePath = $baseDir . "$subject/" . $filePath;
        
        $response['debug']['fullFilePath'] = $fullFilePath;
        
        if (!file_exists($fullFilePath) || is_dir($fullFilePath)) {
            $response['error'] = 'File not found';
        } else {
            $unlinkResult = unlink($fullFilePath);
            $response['debug']['unlinkResult'] = $unlinkResult ? 'success' : 'failed';
            
            if ($unlinkResult) {
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
    
    elseif (isset($_POST['delete_folder'])) {
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $folderPath = isset($_POST['folderPath']) ? $_POST['folderPath'] : '';
        
        $response['debug']['delete_folder_request'] = [
            'subject' => $subject,
            'folderPath' => $folderPath
        ];
        
        $subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
        if (!in_array($subject, $subjects)) {
            $response['error'] = 'Invalid subject';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        if (empty($folderPath)) {
            $response['error'] = 'Folder path is required';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        $folderPath = str_replace('..', '', $folderPath);
        $folderPath = trim($folderPath, '/');
        
        $fullFolderPath = $baseDir . "$subject/$folderPath";
        
        $response['debug']['fullFolderPath'] = $fullFolderPath;
        
        if (!file_exists($fullFolderPath)) {
            $response['error'] = 'Folder not found';
        } else {
            function deleteDir($dirPath, &$errors = []) {
                if (!is_dir($dirPath)) {
                    $errors[] = "Path is not a directory: $dirPath";
                    return false;
                }
                
                if (!is_readable($dirPath)) {
                    $errors[] = "Directory not readable: $dirPath";
                    return false;
                }
                
                $files = array_diff(scandir($dirPath), ['.', '..']);
                $success = true;
                
                foreach ($files as $file) {
                    $path = $dirPath . '/' . $file;
                    
                    if (is_dir($path)) {
                        if (!deleteDir($path, $errors)) {
                            $success = false;
                        }
                    } else {
                        if (!is_writable($path)) {
                            @chmod($path, 0666);
                        }
                        
                        if (!unlink($path)) {
                            $errors[] = "Failed to delete file: $path - " . error_get_last()['message'];
                            $success = false;
                        }
                    }
                }
                
                if (!is_writable($dirPath)) {
                    @chmod($dirPath, 0777);
                }
                
                if (!rmdir($dirPath)) {
                    $errors[] = "Failed to remove directory: $dirPath - " . error_get_last()['message'];
                    $success = false;
                }
                
                return $success;
            }
            
            $deleteErrors = [];
            
            $deleteDirResult = deleteDir($fullFolderPath, $deleteErrors);
            $response['debug']['deleteDirResult'] = $deleteDirResult ? 'success' : 'failed';
            
            if (!empty($deleteErrors)) {
                $response['debug']['deleteErrors'] = $deleteErrors;
            }
            
            if ($deleteDirResult) {
                $response = [
                    'success' => true,
                    'message' => 'Folder deleted successfully',
                    'debug' => $response['debug']
                ];
            } else {
                $response['error'] = 'Failed to delete folder';
                $response['debug']['error_details'] = $deleteErrors;
                
                $folderOwner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($fullFolderPath)) : 'Unknown';
                $folderPerms = substr(sprintf('%o', fileperms($fullFolderPath)), -4);
                $response['debug']['folder_info'] = [
                    'owner' => is_array($folderOwner) ? $folderOwner['name'] : $folderOwner,
                    'permissions' => $folderPerms
                ];
            }
        }
    }
    
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
        
        $subjects = ['or', 'discrete', 'ethics', 'dld', 'maths', 'hr'];
        if (!in_array($subject, $subjects)) {
            $response['error'] = 'Invalid subject';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        if (!empty($folderPath)) {
            $folderPath = str_replace('..', '', $folderPath);
            $folderPath = trim($folderPath, '/');
        }
        
        $dirPath = $baseDir . "$subject/";
        if (!empty($folderPath)) {
            $dirPath .= "$folderPath/";
        }
        $filePath = $dirPath . $filename;
        
        $response['debug']['filePath'] = $filePath;
        
        if (!file_exists($filePath) || is_dir($filePath)) {
            $response['error'] = 'File not found';
        } else {
            $fileInfo = pathinfo($filename);
            $fileExt = isset($fileInfo['extension']) ? $fileInfo['extension'] : '';
            $fileNameWithoutExt = isset($fileInfo['filename']) ? $fileInfo['filename'] : '';
            
            $cleanName = preg_replace('/^[0-9]+_/', '', $fileNameWithoutExt);
            
            $newFilename = sprintf("%02d", $newOrder) . "_" . $cleanName . "." . $fileExt;
            $newFilePath = $dirPath . $newFilename;
            
            $response['debug']['newFilePath'] = $newFilePath;
            
            if ($filePath === $newFilePath) {
                $response = [
                    'success' => true,
                    'message' => 'File order unchanged',
                    'newFilename' => $newFilename,
                    'debug' => $response['debug']
                ];
            }
            else if (file_exists($newFilePath)) {
                $counter = 1;
                $tempName = $cleanName;
                
                while (file_exists($newFilePath)) {
                    if ($counter > 1) {
                        $tempName = preg_replace('/_\d+$/', '', $cleanName);
                    }
                    
                    $newFilename = sprintf("%02d", $newOrder) . "_" . $tempName . "_" . $counter . "." . $fileExt;
                    $newFilePath = $dirPath . $newFilename;
                    $counter++;
                }
                
                $renameResult = rename($filePath, $newFilePath);
                $response['debug']['renameResult'] = $renameResult ? 'success' : 'failed';
                
                if ($renameResult) {
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
                $renameResult = rename($filePath, $newFilePath);
                $response['debug']['renameResult'] = $renameResult ? 'success' : 'failed';
                
                if ($renameResult) {
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
