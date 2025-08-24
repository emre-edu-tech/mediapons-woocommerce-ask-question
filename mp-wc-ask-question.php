<?php
/*
    Plugin Name: MediaPons Woocommerce Ask Question
    Plugin URI: https://media-pons.de
    Description: Custom Woocommerce Ask Question Form for Product
    Version: 1.0.0
    Author: Media Pons
    Author URI: https://media-pons.de
    License: GNU General Public License v2 or later
    License URI: http://www.gnu.org/licenses/gpl-2.0.html
    Text Domain: mp-wc-ask-question
    Domain Path: /languages
*/

if(!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Plugin Constants
define('MP_WC_ASK_QUESTION_PLUGIN_VERSION', '1.0.0');
define('MP_WC_ASK_QUESTION_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MP_WC_ASK_QUESTION_PLUGIN_URL', plugin_dir_url(__FILE__));

// Includes
require_once MP_WC_ASK_QUESTION_PLUGIN_PATH . 'includes/class-mp-wc-ask-question-cpt.php';
require_once MP_WC_ASK_QUESTION_PLUGIN_PATH . 'includes/class-mp-wc-ask-question-admin.php';
require_once MP_WC_ASK_QUESTION_PLUGIN_PATH . 'includes/class-mp-wc-ask-question-enqueue.php';
require_once MP_WC_ASK_QUESTION_PLUGIN_PATH . 'includes/class-mp-wc-ask-question-frontend.php';

function mp_wc_ask_question_init() {
    new MP_WC_Ask_Question_CPT();
    new MP_WC_Ask_Question_Admin();
    new MP_WC_Ask_Question_Enqueue();
    new MP_WC_Ask_Question_Frontend();
}

add_action('plugins_loaded', 'mp_wc_ask_question_init');