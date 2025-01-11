<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once 'ImageProcessor.php';

function init_secure_image_uploads() {
    new ImageProcessor();
}

add_action('init', 'init_secure_image_uploads');