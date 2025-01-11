<?php
if (!defined('ABSPATH')) {
    exit;
}

class CommentImageSecurity {
    private $allowed_mime_types = array(
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    );

    private $blocked_strings = array(
        '<?php',
        '<?=',
        '<%',
        '<script',
        'eval(',
        'exec(',
        'system(',
        'shell_exec(',
        'passthru(',
        'base64_decode(',
        '/bin/sh',
        '/bin/bash'
    );

    public function validate_image($file) {
        // Check if file exists
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }

        // Verify mime type
        $mime_type = $this->get_real_mime_type($file['tmp_name']);
        if (!in_array($mime_type, $this->allowed_mime_types)) {
            return false;
        }

        // Check for malicious content
        if ($this->contains_malicious_code($file['tmp_name'])) {
            return false;
        }

        // Verify image dimensions
        list($width, $height) = getimagesize($file['tmp_name']);
        if ($width === 0 || $height === 0) {
            return false;
        }

        return true;
    }

    private function get_real_mime_type($file_path) {
        // Use FileInfo instead of relying on file extension
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        return $mime_type;
    }

    private function contains_malicious_code($file_path) {
        // Read file content
        $content = file_get_contents($file_path);
        
        // Check for blocked strings
        foreach ($this->blocked_strings as $string) {
            if (stripos($content, $string) !== false) {
                return true;
            }
        }

        // Check for hidden PHP code
        if (preg_match('/<\?(?:php|=)|<%/i', $content)) {
            return true;
        }

        return false;
    }

    public function sanitize_filename($filename) {
        // Remove any directory traversal attempts
        $filename = basename($filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Add random suffix for uniqueness
        $info = pathinfo($filename);
        return sprintf(
            '%s-%s.%s',
            $info['filename'],
            substr(md5(uniqid()), 0, 8),
            $info['extension']
        );
    }
}