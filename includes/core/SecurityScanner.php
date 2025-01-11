<?php
if (!defined('ABSPATH')) {
    exit;
}

class SecurityScanner {
    private $blocked_patterns;
    
    public function __construct() {
        $this->blocked_patterns = apply_filters('secure_image_blocked_patterns', [
            '/<\?(?:php|=)|<%/i',
            '/\beval\s*\(/i',
            '/\bexec\s*\(/i',
            '/\bsystem\s*\(/i',
            '/\bshell_exec\s*\(/i',
            '/\bpassthru\s*\(/i',
            '/\bbase64_decode\s*\(/i',
            '/\/bin\/(?:sh|bash)/i',
            '/<script[^>]*>/i'
        ]);
    }
    
    public function scan($file_path) {
        $content = file_get_contents($file_path);
        
        foreach ($this->blocked_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return false;
            }
        }
        
        return true;
    }
}