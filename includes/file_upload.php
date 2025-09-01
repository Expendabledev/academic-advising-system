<?php
require_once __DIR__ . '/../config/config.php';

class FileUpload {
    public static function upload($file, $directory, $allowedTypes = []) {
        // Validate file upload
        if (!isset($file) || !is_array($file)) {
            throw new Exception('Invalid file upload');
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . self::getUploadErrorMessage($file['error']));
        }
       
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File size exceeds maximum allowed size of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
        }
       
        // Check file type
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
        }
        
        // Additional security: Check MIME type
        $allowedMimeTypes = self::getAllowedMimeTypes($allowedTypes);
        if (!empty($allowedMimeTypes)) {
            $fileMimeType = mime_content_type($file['tmp_name']);
            if (!in_array($fileMimeType, $allowedMimeTypes)) {
                throw new Exception('Invalid file type detected');
            }
        }
       
        // Sanitize directory path
        $directory = rtrim($directory, '/');
        
        // Create directory if it doesn't exist
        if (!file_exists($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
        
        // Check if directory is writable
        if (!is_writable($directory)) {
            throw new Exception('Upload directory is not writable');
        }
       
        // Generate unique filename with original name prefix for better organization
        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
        $fileName = $sanitizedName . '_' . uniqid() . '.' . $fileType;
        $targetPath = $directory . '/' . $fileName;
       
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Set proper file permissions
        chmod($targetPath, 0644);
       
        return $fileName;
    }
    
    public static function delete($filePath) {
        // Security check: ensure file is within allowed directories
        $realPath = realpath($filePath);
        $allowedPaths = [
            realpath(__DIR__ . '/../uploads/'),
            realpath(__DIR__ . '/../temp/')
        ];
        
        $isAllowed = false;
        foreach ($allowedPaths as $allowedPath) {
            if ($allowedPath && strpos($realPath, $allowedPath) === 0) {
                $isAllowed = true;
                break;
            }
        }
        
        if (!$isAllowed) {
            throw new Exception('File deletion not allowed outside designated directories');
        }
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    private static function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File size exceeds upload_max_filesize directive';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File size exceeds MAX_FILE_SIZE directive';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    private static function getAllowedMimeTypes($extensions) {
        $mimeMap = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'txt' => ['text/plain'],
            'csv' => ['text/csv', 'application/csv']
        ];
        
        $allowedMimes = [];
        foreach ($extensions as $ext) {
            if (isset($mimeMap[$ext])) {
                $allowedMimes = array_merge($allowedMimes, $mimeMap[$ext]);
            }
        }
        
        return $allowedMimes;
    }
}
?>