<?php
if (!defined('ABSPATH')) {
    exit;
}

class EmailValidator {
    private $allowed_domains = array(
        'gmail.com',
        'yahoo.com',
        'yahoo.co.uk',
        'yahoo.co.in',
        'yahoo.co.jp',
        'hotmail.com',
        'outlook.com',    // Microsoft's Hotmail rebrand
        'live.com'        // Another Microsoft email domain
    );

    public function isValidDomain($email) {
        if (empty($email)) {
            return false;
        }

        // Extract domain from email
        $domain = strtolower(substr(strrchr($email, "@"), 1));
        
        return in_array($domain, $this->allowed_domains);
    }

    public function getErrorMessage() {
        return 'Only Gmail, Yahoo, and Hotmail/Outlook email addresses are allowed to comment.';
    }
}