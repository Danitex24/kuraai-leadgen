<?php
namespace KuraAI_LeadGen\Admin;

class Product_Activity
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings()
    {
        register_setting('kuraai_leadgen_product_activity_settings', 'kuraai_leadgen_product_activity_settings');

        add_settings_section(
            'kuraai_leadgen_product_activity_section',
            __('Product Activity Tracking', 'kuraai-leadgen'),
            null,
            'kuraai-leadgen-product-activity'
        );

        add_settings_field(
            'enable_tracking',
            __('Enable Tracking', 'kuraai-leadgen'),
            [$this, 'render_enable_tracking_field'],
            'kuraai-leadgen-product-activity',
            'kuraai_leadgen_product_activity_section'
        );
    }

    public function render_page()
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Product Activity Tracking', 'kuraai-leadgen'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('kuraai_leadgen_product_activity_settings');
                do_settings_sections('kuraai-leadgen-product-activity');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_enable_tracking_field()
    {
        $options = get_option('kuraai_leadgen_product_activity_settings');
        ?>
        <input type="checkbox" name="kuraai_leadgen_product_activity_settings[enable_tracking]" value="1" <?php checked($options['enable_tracking'], 1); ?>>
        <?php
    }
}
