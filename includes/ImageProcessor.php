<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once 'core/ImageValidator.php';
require_once 'core/SecurityScanner.php';
require_once 'core/FileHandler.php';

class ImageProcessor {
    private $validator;
    private $scanner;
    private $handler;
    
    public function __construct() {
        $this->validator = new ImageValidator();
        $this->scanner = new SecurityScanner();
        $this->handler = new FileHandler();
        
        // Hook into WordPress upload actions
        add_filter('wp_handle_upload_prefilter', [$this, 'validateUpload']);
        add_filter('wp_handle_upload', [$this, 'processUpload']);
    }
    
    public function validateUpload($file) {
        $validation = $this->validator->validate($file);
        
        if (!$validation['valid']) {
            $file['error'] = $validation['error'];
            return $file;
        }
        
        if (!$this->scanner->scan($file['tmp_name'])) {
            $file['error'] = 'Security scan failed';
            return $file;
        }
        
        return $file;
    }
    
    public function processUpload($file) {
        $filename = $this->handler->moveFile($file, basename($file['name']));
        
        if (!$filename) {
            return new WP_Error('upload_error', 'Failed to process upload');
        }
        
        $file['file'] = $this->handler->getUploadPath() . '/' . $filename;
        $file['url'] = $this->handler->getUploadUrl() . '/' . $filename;
        
        return $file;
    }
}