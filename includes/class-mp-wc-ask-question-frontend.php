<?php
/**
 * Frontend code for Ask Question Form
 */
if (!defined('ABSPATH')) {
    exit;
}

class MP_WC_Ask_Question_Frontend {
    public function __construct() {
        add_action('woocommerce_single_product_summary', [$this, 'custom_ask_question_button'], 25);
        add_action('wp_footer', [$this, 'custom_ask_question_modal']);
        // Handle Ask Question Form Ajax Request
        // 1- Insert the question to database
        // 2- Send an email to the e-mail specified or admin including the question
        add_action('wp_ajax_mmp_submit_ask_question', [$this, 'handle_ask_question_ajax']);
        add_action('wp_ajax_nopriv_mmp_submit_ask_question', [$this, 'handle_ask_question_ajax']);
    }

    // Create the "Ask a Question" Button - Button will be placed in Short Description
    public function custom_ask_question_button() {
        global $product;
	    echo '<button class="mmp-ask-question-button" data-product-id="' . esc_attr($product->get_id()) . '">' . __('Ask a Question', 'woodmart') . '</button>';
    }

    public function custom_ask_question_modal() {
        if(!is_product()) {
            return;
        }

        global $product;

        // Safety fallback if global $product is not set for some themes
        if(empty($product) || !is_a($product, 'WC_Product')) {
            $product = wc_get_product(get_the_ID());
        }
        
        if(!$product) {
            return;
        }

        $product_id = $product->get_id();
        $product_name = $product->get_name();
        ?>
        <div id="mmp-ask-question-modal" class="mmp-ask-question-modal" role="dialog" aria-modal="true" aria-labelled-by="mmp-ask-question-title" aria-hidden="true">
            <div class="mmp-ask-question-content">
                <button class="mmp-ask-question-close" aria-label="<?php _e('Close', 'woodmart') ?>">&times;</button>
                <h2 id="mmp-ask-question-title"><?php _e('Ask a Question about Product', 'woodmart') ?></h2>
                <form id="mmp-ask-question-form">
                    <!-- Product ID (hidden, for backend use) -->
                    <input type="hidden" name="mmp_product_id" value="<?php echo esc_attr($product_id) ?>">

                    <!-- Product Name (readonly for display) -->
                    <div class="mmp-ask-question-input-group">
                        <label for="mmp-product-name"><?php _e('Product', 'woodmart') ?></label>
                        <input type="text" id="mmp-product-name" value="<?php echo esc_attr($product_name) ?>" readonly>
                    </div>
                    
                    <div class="mmp-ask-question-input-group">
                        <label for="mmp-name"><?php _e('Your name', 'woodmart') ?></label>
                        <input type="text" name="mmp_name" id="mmp-name" required>
                    </div>

                    <div class="mmp-ask-question-input-group">
                        <label for="mmp-email"><?php _e('Your Email', 'woodmart') ?></label>
                        <input type="email" name="mmp_email" id="mmp-email" required>
                    </div>

                    <div class="mmp-ask-question-input-group">
                        <label for="mmp-message"><?php _e('Your Question', 'woodmart') ?></label>
                        <textarea name="mmp_message" id="mmp-message" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="mmp-ask-question-submit">
                        <span class="mmp-btn-text"><?php _e('Send Question', 'woodmart') ?></span>
                        <span class="mmp-btn-spinner" aria-hidden="true"></span>
                    </button>
                    <div class="mmp-ask-question-response" aria-live="polite"></div>
                </form>
            </div>
        </div>
        <?php
    }

    public function handle_ask_question_ajax() {
        // Parse ajax request
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $message = sanitize_textarea_field( $_POST['message'] ?? '');
        $product_id = absint($_POST['product_id'] ?? 0);

        if(!$name || !$email || !$message || !$product_id) {
            wp_send_json([
                'success' => false,
                'message' => __('Please fill all fields', 'woodmart'),
            ]);
        }

        $product = wc_get_product($product_id);

        // Save the details to DB
        wp_insert_post([
            'post_type' => 'mmp_product_question',
            'post_status' => 'pending',
            'post_title' => __('Question for ', 'woodmart') . $product->get_name(),
            'post_content' => $message,
            'meta_input' => [
                'mmp_product_id' => $product_id,
                'mmp_user_email' => $email,
                'mmp_user_name' => $name,
            ]
        ]);

        // Send email to admin or selected user (from admin panel)
        $admin_email = get_option('mmp_ask_question_email') ?: get_option('admin_email');

        // Before sending e-mail, change the email content type to html temporarily
        add_filter( 'wp_mail_content_type', function() {
            return 'text/html';
        });

        $site_name = get_bloginfo('name');

        // Sending email to the admin user
        $admin_subject = __('Question for ', 'woodmart') . $product->get_name();
        $admin_html_message = '
            <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.5; color: #333;">
                    <h2 style="color: #0073aa; margin-bottom: 10px;">' . __('New Product Question', 'woodmart') .  '</h2>
                    <table cellpadding="8" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse;">
                        <tr style="background-color: #f5f5f5;">
                            <td style="width: 150px; font-weight: bold;">' . __('Customer Name:', 'woodmart') . '</td>
                            <td>' . esc_html($name) . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">' . __('Email', 'woodmart') . '</td>
                            <td><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></td>
                        </tr>
                        <tr style="background-color: #f5f5f5;">
                            <td style="font-weight: bold;">' . __('Product:', 'woodmart') . '</td>
                            <td>' . esc_html($product->get_name()) . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; vertical-align: top;">' . __('Message:', 'woodmart') . '</td>
                            <td>' . nl2br(esc_html($message)) . '</td>
                        </tr>
                    </table>
                </body>
            </html>
        ';

        // Now we can send the e-mail using html markup to the admin user
        wp_mail($admin_email, $admin_subject, $admin_html_message);

        // Customer Email
        $customer_subject = sprintf(__('We received your question about %s', 'woodmart'), $product->get_name());
        $customer_message = '
            <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.5; color: #333;">
                    <h2 style="color: #0073aa; margin-bottom: 10px;">' . sprintf(__( 'Hello from %s', 'woodmart'), $site_name) .  '</h2>
                    <p>' . sprintf(__('Dear %s', 'woodmart'), $name) . ',</p>
                    <p>' . __('Thank you for reaching out to us! We have received your question and will get back to you as soon as possible.', 'woodmart') . '</p>
                    <p><strong>' . __('Here are the details you sent us:', 'woodmart') . '</strong></p>
                    <table cellpadding="8" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse;">
                        <tr style="background-color: #f5f5f5;">
                            <td style="width: 150px; font-weight: bold;">' . __('Customer Name:', 'woodmart') . '</td>
                            <td>' . esc_html($name) . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">' . __('Email:', 'woodmart') . '</td>
                            <td><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></td>
                        </tr>
                        <tr style="background-color: #f5f5f5;">
                            <td style="font-weight: bold;">' . __('Product:', 'woodmart') . '</td>
                            <td>' . esc_html($product->get_name()) . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; vertical-align: top;">' . __('Message:', 'woodmart') . '</td>
                            <td>' . nl2br(esc_html($message)) . '</td>
                        </tr>
                    </table>
                    <p>' . __('Best regards', 'woodmart') .  ',<br>' . $site_name . '</p>
                </body>
            </html>
        ';
        // Send email to the customer
        wp_mail($email, $customer_subject, $customer_message);

        // Remove the filter for added html content type (Important to avoid affecting other emails)
        remove_filter('wp_mail_content_type', 'set_html_content_type');

        wp_send_json([
            'success' => true,
            'message' => __('Your question has been sent! You can close the Question Form.', 'woodmart'),
        ]);
    }
}