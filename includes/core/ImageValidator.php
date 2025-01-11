<?php
if (!defined('ABSPATH')) {
    exit;
}

class ImageValidator {
    private $allowed_mime_types;
    private $max_file_size;
    
    public function __construct() {
        $this->allowed_mime_types = apply_filters('secure_image_allowed_types', [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ]);
        
        $this->max_file_size = apply_filters('secure_image_max_size', 5 * 1024 * 1024); // 5MB default
    }
    
    public function validate($file) {
        if (!$this->checkFileExists($file)) {
            return ['valid' => false, 'error' => 'No file uploaded'];
        }
        
        if (!$this->checkFileSize($file)) {
            return ['valid' => false, 'error' => 'File size exceeds limit'];
        }
        
        if (!$this->checkMimeType($file)) {
            return ['valid' => false, 'error' => 'Invalid file type'];
        }
        
        if (!$this->checkDimensions($file)) {
            return ['valid' => false, 'error' => 'Invalid image dimensions'];
        }
        
        return ['valid' => true];
    }
    
    private function checkFileExists($file) {
        return isset($file['tmp_name']) && !empty($file['tmp_name']);
    }
    
    private function checkFileSize($file) {
        return $file['size'] <= $this->max_file_size;
    }
    
    private function checkMimeType($file) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        return in_array($mime_type, $this->allowed_mime_types);
    }
    
    private function checkDimensions($file) {
        $imageinfo = @getimagesize($file['tmp_name']);
        return $imageinfo !== false && $imageinfo[0] > 0 && $imageinfo[1] > 0;
    }
}