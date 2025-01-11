<?php
if (!defined('ABSPATH')) {
    exit;
}

class FileHandler {
    private $upload_path;
    
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->upload_path = $upload_dir['path'];
    }
    
    public function moveFile($file, $filename) {
        $safe_filename = $this->sanitizeFilename($filename);
        $destination = $this->upload_path . '/' . $safe_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return false;
        }
        
        return $safe_filename;
    }
    
    private function sanitizeFilename($filename) {
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        $info = pathinfo($filename);
        return sprintf(
            '%s-%s.%s',
            $info['filename'],
            substr(md5(uniqid()), 0, 8),
            $info['extension']
        );
    }
}