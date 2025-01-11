<?php
if (!defined('ABSPATH')) {
    exit;
}

class CommentSecuritySettings {
    private $options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    public function add_plugin_page() {
        add_options_page(
            'Comment Security Settings',
            'Comment Security',
            'manage_options',
            'comment-security',
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page() {
        $this->options = get_option('comment_security_options', array(
            'max_links' => 2,
            'min_length' => 5,
            'blocked_patterns' => "telegra.ph\nbitcoin\nBTC\nwithdraw",
            'blocked_emails' => "setxko.com",
            'blocked_ips' => "172.71.172.230\n162.158.94.134",
            'enable_emoji_filter' => 'yes',
            'enable_script_filter' => 'yes',
            'enable_moderation' => 'yes'
        ));
        ?>
        <div class="wrap">
            <h1>Comment Security Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('comment_security_group');
                do_settings_sections('comment-security');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'comment_security_group',
            'comment_security_options',
            array($this, 'sanitize')
        );

        add_settings_section(
            'general_settings',
            'General Settings',
            array($this, 'print_section_info'),
            'comment-security'
        );

        add_settings_field(
            'max_links',
            'Maximum Links Allowed',
            array($this, 'max_links_callback'),
            'comment-security',
            'general_settings'
        );

        add_settings_field(
            'min_length',
            'Minimum Comment Length',
            array($this, 'min_length_callback'),
            'comment-security',
            'general_settings'
        );

        add_settings_field(
            'blocked_patterns',
            'Blocked Patterns (one per line)',
            array($this, 'blocked_patterns_callback'),
            'comment-security',
            'general_settings'
        );

        add_settings_field(
            'blocked_emails',
            'Blocked Email Domains (one per line)',
            array($this, 'blocked_emails_callback'),
            'comment-security',
            'general_settings'
        );

        add_settings_field(
            'blocked_ips',
            'Blocked IPs (one per line)',
            array($this, 'blocked_ips_callback'),
            'comment-security',
            'general_settings'
        );

        add_settings_field(
            'enable_emoji_filter',
            'Block Spam Emojis',
            array($this, 'emoji_filter_callback'),
            'comment-security',
            'general_settings'
        );

        add_settings_field(
            'enable_script_filter',
            'Remove Script Tags',
            array($this, 'script_filter_callback'),
            'comment-security',
            'general_settings'
        );

        add_settings_field(
            'enable_moderation',
            'Enable Auto-Moderation',
            array($this, 'moderation_callback'),
            'comment-security',
            'general_settings'
        );
    }

    public function sanitize($input) {
        $new_input = array();
        
        $new_input['max_links'] = absint($input['max_links']);
        $new_input['min_length'] = absint($input['min_length']);
        $new_input['blocked_patterns'] = sanitize_textarea_field($input['blocked_patterns']);
        $new_input['blocked_emails'] = sanitize_textarea_field($input['blocked_emails']);
        $new_input['blocked_ips'] = sanitize_textarea_field($input['blocked_ips']);
        $new_input['enable_emoji_filter'] = isset($input['enable_emoji_filter']) ? 'yes' : 'no';
        $new_input['enable_script_filter'] = isset($input['enable_script_filter']) ? 'yes' : 'no';
        $new_input['enable_moderation'] = isset($input['enable_moderation']) ? 'yes' : 'no';

        return $new_input;
    }

    public function print_section_info() {
        print 'Configure your comment security settings below:';
    }

    public function max_links_callback() {
        printf(
            '<input type="number" id="max_links" name="comment_security_options[max_links]" value="%s" />',
            isset($this->options['max_links']) ? esc_attr($this->options['max_links']) : '2'
        );
    }

    public function min_length_callback() {
        printf(
            '<input type="number" id="min_length" name="comment_security_options[min_length]" value="%s" />',
            isset($this->options['min_length']) ? esc_attr($this->options['min_length']) : '5'
        );
    }

    public function blocked_patterns_callback() {
        printf(
            '<textarea id="blocked_patterns" name="comment_security_options[blocked_patterns]" rows="5" cols="50">%s</textarea>',
            isset($this->options['blocked_patterns']) ? esc_textarea($this->options['blocked_patterns']) : ''
        );
    }

    public function blocked_emails_callback() {
        printf(
            '<textarea id="blocked_emails" name="comment_security_options[blocked_emails]" rows="5" cols="50">%s</textarea>',
            isset($this->options['blocked_emails']) ? esc_textarea($this->options['blocked_emails']) : ''
        );
    }

    public function blocked_ips_callback() {
        printf(
            '<textarea id="blocked_ips" name="comment_security_options[blocked_ips]" rows="5" cols="50">%s</textarea>',
            isset($this->options['blocked_ips']) ? esc_textarea($this->options['blocked_ips']) : ''
        );
    }

    public function emoji_filter_callback() {
        printf(
            '<input type="checkbox" id="enable_emoji_filter" name="comment_security_options[enable_emoji_filter]" %s />',
            (isset($this->options['enable_emoji_filter']) && $this->options['enable_emoji_filter'] === 'yes') ? 'checked' : ''
        );
    }

    public function script_filter_callback() {
        printf(
            '<input type="checkbox" id="enable_script_filter" name="comment_security_options[enable_script_filter]" %s />',
            (isset($this->options['enable_script_filter']) && $this->options['enable_script_filter'] === 'yes') ? 'checked' : ''
        );
    }

    public function moderation_callback() {
        printf(
            '<input type="checkbox" id="enable_moderation" name="comment_security_options[enable_moderation]" %s />',
            (isset($this->options['enable_moderation']) && $this->options['enable_moderation'] === 'yes') ? 'checked' : ''
        );
    }
}

if (is_admin()) {
    new CommentSecuritySettings();
}