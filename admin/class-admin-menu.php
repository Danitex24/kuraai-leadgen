<?php
/**
 * Class Admin_Menu
 *
 * @package KuraAI_LeadGen\Admin
 */

namespace KuraAI_LeadGen\Admin;

/**
 * Class Admin_Menu
 *
 * @package KuraAI_LeadGen\Admin
 */
class Admin_Menu {

    /**
     * Admin_Menu constructor.
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add the admin menu and submenus.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('KuraAI Lead Gen', 'kuraai-leadgen'),
            __('KuraAI Lead Gen', 'kuraai-leadgen'),
            'manage_options',
            'kuraai-leadgen',
            [$this, 'render_settings_page'],
            'dashicons-admin-generic',
            56
        );

        add_submenu_page(
            'kuraai-leadgen',
            __('Settings', 'kuraai-leadgen'),
            __('Settings', 'kuraai-leadgen'),
            'manage_options',
            'kuraai-leadgen',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'kuraai-leadgen',
            __('AI-Powered Website Audit', 'kuraai-leadgen'),
            __('AI-Powered Website Audit', 'kuraai-leadgen'),
            'manage_options',
            'kuraai-leadgen-ai-audit',
            [new AI_Audit(), 'render_page']
        );

        add_submenu_page(
            'kuraai-leadgen',
            __('Scheduled WooCommerce Checkups', 'kuraai-leadgen'),
            __('Scheduled WooCommerce Checkups', 'kuraai-leadgen'),
            'manage_options',
            'kuraai-leadgen-scheduler',
            [new Scheduler(), 'render_page']
        );

        add_submenu_page(
            'kuraai-leadgen',
            __('Competitor Audit Tool', 'kuraai-leadgen'),
            __('Competitor Audit Tool', 'kuraai-leadgen'),
            'manage_options',
            'kuraai-leadgen-competitor-audit',
            [new Competitor_Audit(), 'render_page']
        );

        add_submenu_page(
            'kuraai-leadgen',
            __('Product Activity Tracking', 'kuraai-leadgen'),
            __('Product Activity Tracking', 'kuraai-leadgen'),
            'manage_options',
            'kuraai-leadgen-product-activity',
            [new Product_Activity(), 'render_page']
        );

        add_submenu_page(
            'kuraai-leadgen',
            __('WooCommerce Hook Integration', 'kuraai-leadgen'),
            __('WooCommerce Hook Integration', 'kuraai-leadgen'),
            'manage_options',
            'kuraai-leadgen-woocommerce-hooks',
            [new WooCommerce_Hooks(), 'render_page']
        );
    }

    /**
     * Register the plugin settings.
     */
    public function register_settings() {
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

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        require_once KURAAI_LEADGEN_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    /**
     * Render the API settings section.
     */
    public function render_api_section() {
        echo '<p>' . esc_html__('Configure your AI API settings below.', 'kuraai-leadgen') . '</p>';
    }

    /**
     * Render the OpenAI API key field.
     */
    public function render_openai_api_key_field() {
        $settings = get_option('kuraai_leadgen_settings');
        $value    = isset($settings['openai_api_key']) ? $settings['openai_api_key'] : '';
        ?>
        <input type="password" name="kuraai_leadgen_settings[openai_api_key]" value="<?php echo esc_attr($value); ?>"
            class="regular-text">
        <p class="description"><?php esc_html_e('Enter your OpenAI API key to enable AI-powered audits.', 'kuraai-leadgen'); ?>
        </p>
        <?php
    }

    /**
     * Render the Google Gemini API key field.
     */
    public function render_gemini_api_key_field() {
        $settings = get_option('kuraai_leadgen_settings');
        $value    = isset($settings['gemini_api_key']) ? $settings['gemini_api_key'] : '';
        ?>
        <input type="password" name="kuraai_leadgen_settings[gemini_api_key]" value="<?php echo esc_attr($value); ?>"
            class="regular-text">
        <p class="description">
            <?php esc_html_e('Enter your Google Gemini API key as an alternative to OpenAI.', 'kuraai-leadgen'); ?></p>
        <?php
    }

    /**
     * Render the enable tracking field.
     */
    public function render_enable_tracking_field() {
        $settings = get_option('kuraai_leadgen_settings');
        ?>
        <input type="checkbox" name="kuraai_leadgen_settings[enable_tracking]" value="yes"
            <?php checked(isset($settings['enable_tracking']) && $settings['enable_tracking'] === 'yes'); ?>>
        <span
            class="description"><?php esc_html_e('Track product views and cart additions for future Pro features.', 'kuraai-leadgen'); ?></span>
        <?php
    }
}