<?php
namespace KuraAI_LeadGen\Admin;

class Competitor_Audit
{
    public function __construct()
    {
        add_action('wp_ajax_kuraai_leadgen_audit_competitor', [$this, 'audit_competitor']);
    }

    public function audit_competitor()
    {
        check_ajax_referer('kuraai_leadgen_competitor_audit_nonce', 'nonce');

        $settings = get_option('kuraai_leadgen_settings');
        if (empty($settings['openai_api_key']) && empty($settings['gemini_api_key'])) {
            wp_send_json_error(__('Please set your OpenAI or Gemini API key in the settings.', 'kuraai-leadgen'));
        }

        // This is a placeholder for the actual logic to audit a competitor
        // and interact with an AI API.
        $results = [
            'The competitor is running a sale on all of their products.',
            'The competitor is using a new marketing campaign to target a specific audience.',
            'The competitor has recently launched a new product.',
        ];

        wp_send_json_success($results);
    }

    public function render_page()
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Competitor Audit Tool', 'kuraai-leadgen'); ?></h1>
            <form id="competitor-audit-form" method="post" action="">
                <?php wp_nonce_field('kuraai_leadgen_competitor_audit_nonce', 'kuraai_leadgen_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Competitor URL', 'kuraai-leadgen'); ?></th>
                        <td><input type="text" name="competitor_url" class="regular-text"></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="audit_competitor" id="audit_competitor" class="button button-primary" value="<?php esc_attr_e('Audit Competitor', 'kuraai-leadgen'); ?>">
                </p>
            </form>
            <div id="competitor-audit-results">
                <!-- Competitor audit results will be displayed here -->
            </div>
            <div id="pro-cta-competitor" style="display: none;">
                <p><?php esc_html_e('Upgrade to Pro to audit up to 10 competitors and access detailed product strategy breakdowns.', 'kuraai-leadgen'); ?></p>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#competitor-audit-form').on('submit', function(e) {
                    e.preventDefault();
                    var form = $(this);
                    var button = form.find('#audit_competitor');
                    var resultsContainer = $('#competitor-audit-results');
                    var proCta = $('#pro-cta-competitor');
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'kuraai_leadgen_audit_competitor',
                            nonce: form.find('#kuraai_leadgen_nonce').val(),
                            competitor_url: form.find('input[name="competitor_url"]').val()
                        },
                        beforeSend: function() {
                            button.prop('disabled', true);
                            resultsContainer.html('<p>Auditing competitor...</p>');
                        },
                        success: function(response) {
                            button.prop('disabled', false);
                            if (response.success) {
                                var results = response.data;
                                var html = '<ul>';
                                for (var i = 0; i < results.length; i++) {
                                    html += '<li>' + results[i] + '</li>';
                                }
                                html += '</ul>';
                                resultsContainer.html(html);
                                proCta.show();
                            } else {
                                resultsContainer.html('<p>' + response.data + '</p>');
                            }
                        }
                    });
                });
            });
        </script>
        <?php
    }
}
