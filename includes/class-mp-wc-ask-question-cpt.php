<?php
/**
 * Custom Message to Seller or Ask a Question for Single Product Page
 */
if (!defined('ABSPATH')) {
    exit;
}

class MP_WC_Ask_Question_CPT {
    public function __construct() {
        // Register Custom Post Types
        add_action('init', [$this, 'register_cpt']);
    }

    // Create Custom Post Type called 'mp_wc_prod_question'
    public function register_cpt() {
        $labels = [
            'name' => __('Product Questions', 'mp-wc-ask-question'),
            'singular_name' => __('Product Question', 'mp-wc-ask-question'),
            'add_new_item' => __('Add Question', 'mp-wc-ask-question'),
            'edit_item' => __('Edit Question', 'mp-wc-ask-question'),
        ];

        $args = [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'menu_position' => 30,
            'menu_icon' => 'dashicons-format-chat',
            'supports' => ['title', 'editor']
        ];

	    register_post_type('mp_wc_prod_question', $args);
    }
}