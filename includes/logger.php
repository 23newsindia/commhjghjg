<?php
if (!defined('ABSPATH')) {
    exit;
}

class CommentSecurityLogger {
    private $log_file;

    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/comment-security.log';
    }

    public function log_blocked_comment($reason, $comment_data) {
        if (!file_exists($this->log_file)) {
            touch($this->log_file);
            chmod($this->log_file, 0600);
        }

        $log_entry = sprintf(
            "[%s] Blocked comment from IP: %s, Email: %s, Reason: %s\n",
            current_time('mysql'),
            $_SERVER['REMOTE_ADDR'],
            $comment_data['comment_author_email'],
            $reason
        );

        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }

    public function get_recent_blocks($limit = 100) {
        if (!file_exists($this->log_file)) {
            return array();
        }

        $lines = array_slice(file($this->log_file), -$limit);
        return array_map('trim', $lines);
    }
}