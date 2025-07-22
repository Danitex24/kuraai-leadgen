<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if (isset($_GET['kuraai_success'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html(urldecode($_GET['kuraai_success'])); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['kuraai_error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html(urldecode($_GET['kuraai_error'])); ?></p>
        </div>
    <?php endif; ?>

    <div class="kuraai-settings-container">
        <div class="kuraai-settings-main">
            <form method="post" action="options.php">
                <?php
                settings_fields('kuraai_leadgen_settings');
                do_settings_sections('kuraai-leadgen');
                submit_button(__('Save Settings', 'kuraai-leadgen'));
                ?>
            </form>

            <div class="kuraai-webhook-section">
                <h2><?php esc_html_e('Webhook Endpoint', 'kuraai-leadgen'); ?></h2>
                <p><?php esc_html_e('Use this webhook URL to receive real-time updates (Pro feature):', 'kuraai-leadgen'); ?>
                </p>
                <div class="webhook-url-container">
                    <input type="text" id="kuraai-webhook-url"
                        value="<?php echo esc_url(get_rest_url(null, 'kuraai-leadgen/v1/webhook')); ?>" readonly>
                    <button type="button" class="button button-secondary" onclick="copyWebhookUrl()">
                        <?php esc_html_e('Copy', 'kuraai-leadgen'); ?>
                    </button>
                </div>
                <p class="description">
                    <?php esc_html_e('Upgrade to Pro to configure webhook events and authentication.', 'kuraai-leadgen'); ?>
                </p>
            </div>

            <div class="kuraai-test-api-section">
                <h2><?php esc_html_e('Test API Connection', 'kuraai-leadgen'); ?></h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="kuraai_test_api">
                    <?php wp_nonce_field('kuraai_test_api'); ?>
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Test API Connection', 'kuraai-leadgen'); ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="kuraai-settings-sidebar">
            <div class="kuraai-pro-promo">
                <h3><?php esc_html_e('Upgrade to KuraAI Pro', 'kuraai-leadgen'); ?></h3>
                <ul>
                    <li><?php esc_html_e('Unlimited AI suggestions', 'kuraai-leadgen'); ?></li>
                    <li><?php esc_html_e('Audit up to 10 competitors', 'kuraai-leadgen'); ?></li>
                    <li><?php esc_html_e('Detailed product strategy breakdowns', 'kuraai-leadgen'); ?></li>
                    <li><?php esc_html_e('Historical audit storage', 'kuraai-leadgen'); ?></li>
                    <li><?php esc_html_e('PDF/CSV reporting', 'kuraai-leadgen'); ?></li>
                </ul>
                <a href="#" class="button button-primary"><?php esc_html_e('Learn More', 'kuraai-leadgen'); ?></a>
            </div>
        </div>
    </div>
</div>

<script>
    function copyWebhookUrl() {
        const copyText = document.getElementById("kuraai-webhook-url");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");

        alert("<?php esc_html_e('Webhook URL copied to clipboard!', 'kuraai-leadgen'); ?>");
    }
</script>