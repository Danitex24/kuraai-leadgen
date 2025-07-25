<?php
/**
 * Class AI_Audit
 *
 * @package KuraAI_LeadGen\Admin
 */

namespace KuraAI_LeadGen\Admin;

/**
 * Class AI_Audit
 *
 * @package KuraAI_LeadGen\Admin
 */
class AI_Audit {

    /**
     * AI_Audit constructor.
     */
    public function __construct() {
        add_action('wp_ajax_kuraai_leadgen_fetch_woocommerce_data', [$this, 'fetch_woocommerce_data']);
    }

    /**
     * Fetch WooCommerce data and generate AI suggestions.
     */
    public function fetch_woocommerce_data() {
        check_ajax_referer('kuraai_leadgen_ai_audit_nonce', 'nonce');

        $settings = get_option('kuraai_leadgen_settings');
        if (empty($settings['openai_api_key']) && empty($settings['gemini_api_key'])) {
            wp_send_json_error(__('Please set your OpenAI or Gemini API key in the settings.', 'kuraai-leadgen'));
        }

        // This is a placeholder for the actual logic to fetch WooCommerce data
        // and interact with an AI API.
        $suggestions = [
            'Improve your product descriptions with more engaging copy.',
            'Optimize your product images for faster loading times.',
            'Add a related products section to your product pages.',
            'Implement a customer reviews system.',
            'Offer a discount for first-time customers.',
            'Use a more prominent call-to-action button.',
            'Simplify your checkout process.',
            'Add a live chat feature to your website.',
            'Create a sense of urgency with limited-time offers.',
            'Use A/B testing to optimize your landing pages.',
        ];

        wp_send_json_success($suggestions);
    }

    /**
     * Render the AI-Powered Website Audit page.
     */
    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('AI-Powered Website Audit', 'kuraai-leadgen'); ?></h1>
            <form id="ai-audit-form" method="post" action="">
                <?php wp_nonce_field('kuraai_leadgen_ai_audit_nonce', 'kuraai_leadgen_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Business Description', 'kuraai-leadgen'); ?></th>
                        <td><textarea name="business_description" rows="5" cols="50" class="large-text"></textarea></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="fetch_woocommerce_data" id="fetch_woocommerce_data" class="button button-primary" value="<?php esc_attr_e('Fetch WooCommerce Data', 'kuraai-leadgen'); ?>">
                </p>
            </form>
            <div id="ai-suggestions">
                <!-- AI suggestions will be displayed here -->
            </div>
            <div id="pro-cta" style="display: none;">
                <p><?php esc_html_e('Upgrade to KuraAI Pro to unlock unlimited smart suggestions and deeper optimization insights.', 'kuraai-leadgen'); ?></p>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#ai-audit-form').on('submit', function(e) {
                    e.preventDefault();
                    var form = $(this);
                    var button = form.find('#fetch_woocommerce_data');
                    var suggestionsContainer = $('#ai-suggestions');
                    var proCta = $('#pro-cta');
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'kuraai_leadgen_fetch_woocommerce_data',
                            nonce: form.find('#kuraai_leadgen_nonce').val(),
                            business_description: form.find('textarea[name="business_description"]').val()
                        },
                        beforeSend: function() {
                            button.prop('disabled', true);
                            suggestionsContainer.html('<p>Fetching data and generating suggestions...</p>');
                        },
                        success: function(response) {
                            button.prop('disabled', false);
                            if (response.success) {
                                var suggestions = response.data;
                                var html = '<ul>';
                                for (var i = 0; i < suggestions.length; i++) {
                                    html += '<li>' + suggestions[i] + '</li>';
                                }
                                html += '</ul>';
                                suggestionsContainer.html(html);
                                if (suggestions.length >= 10) {
                                    proCta.show();
                                }
                            } else {
                                suggestionsContainer.html('<p>' + response.data + '</p>');
                            }
                        }
                    });
                });
            });
        </script>
        <?php
    }
}
