<?php
/**
 * Plugin Name: Comment Security Guard Pro
 * Description: Advanced protection against spam comments and script injection with admin UI
 * Version: 2.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load required files
require_once plugin_dir_path(__FILE__) . 'includes/filters.php';
require_once plugin_dir_path(__FILE__) . 'includes/logger.php';
require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/init.php';


class CommentSecurityGuardPro {
    private $filters;
    private $logger;

    public function __construct() {
        $this->filters = new CommentSecurityFilters();
        $this->logger = new CommentSecurityLogger();

        // Comment filters
        add_filter('preprocess_comment', array($this, 'process_comment'), 1);
        add_filter('comment_text', array($this, 'filter_comment_text'), 1);
        add_filter('pre_comment_approved', array($this, 'check_comment_approval'), 99, 2);
        
        // Add custom error message for email validation
        add_filter('comment_form_defaults', array($this, 'add_email_notice'));
        
        // Add admin notice for blocked comments
        add_action('admin_notices', array($this, 'show_blocked_comments_notice'));
    }
  
  
 public function add_email_notice($defaults) {
        $validator = new EmailValidator();
        $defaults['comment_notes_before'] .= '<p class="comment-notes">' . 
            esc_html($validator->getErrorMessage()) . '</p>';
        return $defaults;
    }


    public function process_comment($commentdata) {
        // Check patterns
        if (!$this->filters->check_patterns($commentdata['comment_content'])) {
            $this->logger->log_blocked_comment('Blocked Pattern', $commentdata);
            wp_die('Comment blocked due to suspicious content.');
        }

        // Check number of links
        if (!$this->filters->check_links($commentdata['comment_content'])) {
            $this->logger->log_blocked_comment('Too Many Links', $commentdata);
            wp_die('Too many links in comment.');
        }

        // Check comment length
        if (!$this->filters->check_length($commentdata['comment_content'])) {
            $this->logger->log_blocked_comment('Comment Too Short', $commentdata);
            wp_die('Comment is too short.');
        }

        return $commentdata;
    }

    public function filter_comment_text($content) {
        return $this->filters->strip_scripts($content);
    }

    public function check_comment_approval($approved, $commentdata) {
        // Check email domain
        if (!$this->filters->check_email_domain($commentdata['comment_author_email'])) {
            $this->logger->log_blocked_comment('Blocked Email Domain', $commentdata);
            return 'spam';
        }

        // Check IP
        if (!$this->filters->check_ip($_SERVER['REMOTE_ADDR'])) {
            $this->logger->log_blocked_comment('Blocked IP', $commentdata);
            return 'spam';
        }

        return $approved;
    }

    public function show_blocked_comments_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $recent_blocks = $this->logger->get_recent_blocks(5);
        if (empty($recent_blocks)) {
            return;
        }

        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Recent Blocked Comments:</strong></p>';
        echo '<ul>';
        foreach ($recent_blocks as $block) {
            echo '<li>' . esc_html($block) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
}

// Initialize the plugin
new CommentSecurityGuardPro();