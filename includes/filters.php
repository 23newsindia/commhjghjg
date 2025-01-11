<?php
if (!defined('ABSPATH')) {
    exit;
}


require_once 'EmailValidator.php';

class CommentSecurityFilters {
    private $options;
    private $email_validator;

    public function __construct() {
        $this->options = get_option('comment_security_options', array(
            'blocked_patterns' => "telegra.ph\nbitcoin\nBTC\nwithdraw",
            'blocked_emails' => "setxko.com",
            'blocked_ips' => "",
            'max_links' => 2,
            'min_length' => 5,
            'enable_script_filter' => 'yes'
        ));
    }

    public function check_patterns($comment_text) {
        if (empty($comment_text)) {
            return true;
        }

        $patterns = array_filter(explode("\n", isset($this->options['blocked_patterns']) ? $this->options['blocked_patterns'] : ''));
        
        // Add common spam patterns
        $patterns = array_merge($patterns, array(
            'telegra.ph',
            'bitcoin',
            'BTC',
            'withdraw',
            'notification',
            '🔑',
            '📭',
            '🔩',
            '⚖',
            '🖲',
            '📣',
            '🗂'
        ));

        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);
            if (!empty($pattern) && stripos($comment_text, $pattern) !== false) {
                return false;
            }
        }
        return true;
    }

    public function check_email_domain($email) {
        if (empty($email)) {
            return false;
        }

        // First check if email is from allowed domains
        if (!$this->email_validator->isValidDomain($email)) {
            return false;
        }

        // Then check against blocked domains
        $domains = array_filter(explode("\n", isset($this->options['blocked_emails']) ? $this->options['blocked_emails'] : ''));
        foreach ($domains as $domain) {
            $domain = trim($domain);
            if (!empty($domain) && stripos($email, '@' . $domain) !== false) {
                return false;
            }
        }

        return true;
    }

    public function check_ip($ip) {
        if (empty($ip)) {
            return true;
        }

        $blocked_ips = array_filter(explode("\n", isset($this->options['blocked_ips']) ? $this->options['blocked_ips'] : ''));
        
        // Add known spam IPs
        $blocked_ips = array_merge($blocked_ips, array(
            '172.71.172.230',
            '162.158.94.134',
            '172.70.247.42',
            '162.158.202.100',
            '162.158.203.66',
            '162.158.18.124',
            '162.158.202.42'
        ));

        return !in_array($ip, array_map('trim', $blocked_ips));
    }

    public function strip_scripts($content) {
        if (empty($content)) {
            return '';
        }

        // Remove script tags and their contents
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
        
        // Remove onclick and other event handlers
        $content = preg_replace('/\son\w+\s*=\s*["\'][^"\']*["\']/', '', $content);
        
        // Remove iframe tags
        $content = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $content);
        
        // Remove data: URLs
        $content = preg_replace('/data:\s*[^\s]*\s*/i', '', $content);
        
        return $content;
    }

    public function check_links($content) {
        if (empty($content)) {
            return true;
        }

        $max_links = isset($this->options['max_links']) ? (int)$this->options['max_links'] : 2;
        return substr_count(strtolower($content), 'http') <= $max_links;
    }

    public function check_length($content) {
        if (empty($content)) {
            return false;
        }

        $min_length = isset($this->options['min_length']) ? (int)$this->options['min_length'] : 5;
        return strlen(trim($content)) >= $min_length;
    }
}