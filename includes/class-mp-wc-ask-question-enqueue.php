<?php
/**
 * Enqueue scripts and styles and localizing variables.
 */
if (!defined('ABSPATH')) {
    exit;
}

class MP_WC_Ask_Question_Enqueue {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'load_custom_assets']);
    }

    function load_custom_assets() {
        wp_enqueue_script('mmp-custom-script', MP_WC_ASK_QUESTION_PLUGIN_URL . '/assets/js/main.js', [], filemtime(MP_WC_ASK_QUESTION_PLUGIN_PATH . '/assets/js/main.js'), true );

        // Use also for javascript translations
        // Do not bother to use wp_set_script_translations() method, it does not work properly with Loco Translate
        // Always try to use wp_localize_script() function to make it work
        wp_localize_script('mmp-custom-script', 'mmpData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'sending' => __('Sending...', 'mp-wc-ask-question'),
            'send_question' => __('Send Question', 'mp-wc-ask-question'),
            'question_sent' => __('Your question has been sent! You can close the Question Form.', 'mp-wc-ask-question'),
            'fill_fields' => __('Please fill all fields', 'mp-wc-ask-question'),
            'error_occured' => __('An error occured. Please try again later.', 'mp-wc-ask-question'),
        ]);

        wp_enqueue_style( 'mmp-custom-style', MP_WC_ASK_QUESTION_PLUGIN_URL . '/assets/css/main.css', [], MP_WC_ASK_QUESTION_PLUGIN_PATH . '/assets/css/main.css');

        // Modal Form Functionality
        if(is_product()) {
            wp_enqueue_script('mmp-modal-script', MP_WC_ASK_QUESTION_PLUGIN_URL . '/assets/js/modal-function.js', [], filemtime(MP_WC_ASK_QUESTION_PLUGIN_PATH . '/assets/js/modal-function.js'), true );
            // Add the Cloudflare Turnstile JS
            wp_enqueue_script('cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, true);
        }
    }
}