<?php
namespace KuraAI_LeadGen\Admin;

class Admin_Menu
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_admin_menu()
    {
        add_submenu_page(
            'woocommerce',
            __('KuraAI Lead Gen', 'kuraai-leadgen'),
            __('KuraAI Lead Gen', 'kuraai-leadgen'),
            'manage_woocommerce',
            'kuraai-leadgen',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings()
    {
        register_setting('kuraai_leadgen_settings', 'kuraai_leadgen_settings');

        add_settings_section(
            'kuraai_leadgen_api_section',
            __('API Settings', 'kuraai-leadgen'),
            [$this, 'render_api_section'],
            'kuraai-leadgen'
        );

        add_settings_field(
            'openai_api_key',
            __('OpenAI API Key', 'kuraai-leadgen'),
            [$this, 'render_openai_api_key_field'],
            'kuraai-leadgen',
            'kuraai_leadgen_api_section'
        );

        add_settings_field(
            'gemini_api_key',
            __('Google Gemini API Key', 'kuraai-leadgen'),
            [$this, 'render_gemini_api_key_field'],
            'kuraai-leadgen',
            'kuraai_leadgen_api_section'
        );

        add_settings_field(
            'enable_tracking',
            __('Enable Product Activity Tracking', 'kuraai-leadgen'),
            [$this, 'render_enable_tracking_field'],
            'kuraai-leadgen',
            'kuraai_leadgen_api_section'
        );
    }

    public function render_settings_page()
    {
        require_once KURAAI_LEADGEN_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    public function render_api_section()
    {
        echo '<p>' . esc_html__('Configure your AI API settings below.', 'kuraai-leadgen') . '</p>';
    }

    public function render_openai_api_key_field()
    {
        $settings = get_option('kuraai_leadgen_settings');
        $value = isset($settings['openai_api_key']) ? $settings['openai_api_key'] : '';
        ?>
        <input type="password" name="kuraai_leadgen_settings[openai_api_key]" value="<?php echo esc_attr($value); ?>"
            class="regular-text">
        <p class="description"><?php esc_html_e('Enter your OpenAI API key to enable AI-powered audits.', 'kuraai-leadgen'); ?>
        </p>
        <?php
    }

    public function render_gemini_api_key_field()
    {
        $settings = get_option('kuraai_leadgen_settings');
        $value = isset($settings['gemini_api_key']) ? $settings['gemini_api_key'] : '';
        ?>
        <input type="password" name="kuraai_leadgen_settings[gemini_api_key]" value="<?php echo esc_attr($value); ?>"
            class="regular-text">
        <p class="description">
            <?php esc_html_e('Enter your Google Gemini API key as an alternative to OpenAI.', 'kuraai-leadgen'); ?></p>
        <?php
    }

    public function render_enable_tracking_field()
    {
        $settings = get_option('kuraai_leadgen_settings');
        ?>
        <input type="checkbox" name="kuraai_leadgen_settings[enable_tracking]" value="yes"
            <?php checked(isset($settings['enable_tracking']) && $settings['enable_tracking'] === 'yes'); ?>>
        <span
            class="description"><?php esc_html_e('Track product views and cart additions for future Pro features.', 'kuraai-leadgen'); ?></span>
        <?php
    }
}