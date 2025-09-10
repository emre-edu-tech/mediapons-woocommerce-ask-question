<?php
/**
 * Custom Message to Seller or Ask a Question for Single Product Page
 */
if (!defined('ABSPATH')) {
    exit;
}

class MP_WC_Ask_Question_Admin {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('admin_init', [$this, 'register_admin_settings']);
        add_action('admin_menu', [$this, 'add_admin_settings_submenu']);
    }

    public function register_admin_settings() {
        // Option Group, Option Name
        register_setting('mp_wc_ask_question_settings_group', 'mp_wc_ask_question_turnstile_site_key');

        register_setting('mp_wc_ask_question_settings_group', 'mp_wc_ask_question_turnstile_secret_key');

        register_setting('mp_wc_ask_question_settings_group', 'mp_wc_ask_question_email', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => '',
        ]);

        add_settings_section(
            'mp_wc_ask_question_settings_section',
            __('Cloudflare Turnstile Settings', 'mp-wc-ask-question'),
            null,
            'mp-wc-ask-question-settings',
        );

        // Custom e-mail to send Product Question
        add_settings_field(
            'mp_wc_ask_question_email',
            __('Other E-mail Address for sending Questions', 'mp-wc-ask-question'),
            [$this, 'render_email_field'],
            'mp-wc-ask-question-settings',
            'mp_wc_ask_question_settings_section'
        );

        // Turnstile Site Key for Cloudflare Turnstile
        add_settings_field(
            'mp_wc_ask_question_turnstile_site_key',
            __('Turnstile Site Key', 'mp-wc-ask-question'),
            [$this, 'render_turnstile_site_key_field'],
            'mp-wc-ask-question-settings',
            'mp_wc_ask_question_settings_section'
        );

        // Turnstile Secret Key for Cloudflare Turnstile
        add_settings_field(
            'mp_wc_ask_question_turnstile_secret_key',
            __('Turnstile Secret Key', 'mp-wc-ask-question'),
            [$this, 'render_turnstile_secret_key_field'],
            'mp-wc-ask-question-settings',
            'mp_wc_ask_question_settings_section'
        );
    }

    public function render_email_field($args) {
        $value = get_option('mp_wc_ask_question_email');
        $placeholder = get_option('admin_email');

        echo '<input id="' . esc_attr($args['label_for']) . '" type="email" name="mp_wc_ask_question_email" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '" class="regular-text" />';

        echo '<p class="description">' . __('Leave blank to use the default WordPress admin email.', 'mp-wc-ask-question') . '</p>';
    }

    public function render_turnstile_site_key_field() {
        $value = get_option('mp_wc_ask_question_turnstile_site_key');
        echo '<input type="text" name="mp_wc_ask_question_turnstile_site_key" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Site key will be retrieved after adding your domain to Cloudflare Turnstile widget.', 'mp-wc-ask-question') . '</p>';
    }

    public function render_turnstile_secret_key_field() {
        $value = get_option('mp_wc_ask_question_turnstile_secret_key');
        echo '<input type="text" name="mp_wc_ask_question_turnstile_secret_key" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Secret key will be retrieved after adding your domain to Cloudflare Turnstile widget.', 'mp-wc-ask-question') . '</p>';
    }

    public function add_admin_settings_submenu() {
        add_submenu_page(
            'edit.php?post_type=mp_wc_prod_question',
            __('Product Question Settings', 'mp-wc-ask-question'),
            __('Settings', 'mp-wc-ask-question'),
            'manage_options',
            'mp-wc-ask-question-settings',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('MP WC Product Question Settings', 'mp-wc-ask-question') ?></h1>
            <p><?php _e('Leave blank to disable Cloudflare Turnstile verification.', 'mp-wc-ask-question') ?></p>
            <p><strong><?php _e('Important Note: Your domain must be added to your Cloudflare Turnstile widget.', 'mp-wc-ask-question') ?></strong></p>
            <form action="options.php" method="post">
                <?php
                settings_fields('mp_wc_ask_question_settings_group');   // matches register_setting group
                do_settings_sections('mp-wc-ask-question-settings');    // matches add_settings_section page
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_meta_boxes() {
        // Add Question Details Meta Box
        add_meta_box('mp_wc_ask_question_meta_box', __('Question Details', 'mp-wc-ask-question'), [$this, 'render_product_question_meta_box'], '', 'normal', 'default');

        // Add Answers to Question Meta Box
        add_meta_box('mp_wc_ask_question_answers_meta_box', __('Answers to this Question', 'mp-wc-ask-question'), [$this, 'render_question_answers_meta_box'], 'mp_wc_prod_question', 'normal', 'default');
    }

    public function render_product_question_meta_box($post) {
        // Retrieve meta fields
        $product_id = get_post_meta($post->ID, 'mp_wc_ask_question_product_id', true);
        $user_email = get_post_meta($post->ID, 'mp_wc_ask_question_user_email', true);
        $user_name = get_post_meta($post->ID, 'mp_wc_ask_question_user_name', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label><?php _e('Product Name', 'mp-wc-ask-question') ?> - #<?php echo intval($product_id) ?></label></th>
                <td>
                    <?php if($product_id && get_post_type($product_id) === 'product'): ?>
                        <a href="<?php echo esc_url(get_edit_post_link($product_id)) ?>" title="<?php _e('Go to Product Details', 'mp-wc-ask-question') ?>" target="_blank">
                            <?php echo get_the_title($product_id) ?>
                        </a>
                    <?php else: ?>
                        <span><?php _e('Product not available', 'mp-wc-ask-question') ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label><?php _e('Sender Name', 'mp-wc-ask-question') ?></label></th>
                <td>
                    <input type="text" readonly value="<?php echo esc_attr($user_name) ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label><?php _e('Sender Email', 'mp-wc-ask-question') ?></label></th>
                <td>
                    <input type="email" readonly value="<?php echo esc_attr($user_email) ?>" class="regular-text">
                </td>
            </tr>
        </table>
        <?php
    }

    public function render_question_answers_meta_box($post) {
        $user_email = get_post_meta($post->ID, 'mp_wc_ask_question_user_email', true);
        $user_name = get_post_meta($post->ID, 'mp_wc_ask_question_user_name', true);

        // Fetch existing answers (comments) to this question using comment_type key

    }
}