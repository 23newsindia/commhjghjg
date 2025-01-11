<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once 'image-security.php';

class CommentImageHandler {
    private $image_security;
    private $upload_dir;

    public function __construct() {
        $this->image_security = new CommentImageSecurity();
        $this->upload_dir = wp_upload_dir();
    }

    public function process_image($file) {
        // Validate image
        if (!$this->image_security->validate_image($file)) {
            return false;
        }

        // Sanitize filename
        $safe_filename = $this->image_security->sanitize_filename($file['name']);
        
        // Create upload path
        $upload_path = $this->upload_dir['path'] . '/' . $safe_filename;
        
        // Move file to uploads directory
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            return false;
        }

        return array(
            'url' => $this->upload_dir['url'] . '/' . $safe_filename,
            'path' => $upload_path
        );
    }
}