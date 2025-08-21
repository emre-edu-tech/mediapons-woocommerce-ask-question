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
    }

    public function register_meta_boxes() {
        add_meta_box('mmp_product_question_meta_box', __('Question Details', 'mp-wc-ask-question'), [$this, 'render_product_question_meta_box'], 'mmp_product_question', 'normal', 'default');
    }

    public function render_product_question_meta_box($post) {
        // Retrieve meta fields
        $product_id = get_post_meta($post->ID, 'mmp_product_id', true);
        $user_email = get_post_meta($post->ID, 'mmp_user_email', true);
        $user_name = get_post_meta($post->ID, 'mmp_user_name', true);
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
}