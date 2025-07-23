<?php
/**
 * Plugin Name: KuraAI Lead Gen
 * Plugin URI: https://kuraai-lead-gen.danovatesolutions.org
 * Description: AI-powered WooCommerce audits and sales improvement suggestions.
 * Version: 1.0.1
 * Author: Daniel Abughdyer
 * Author URI: https://kuraai-lead-gen.danovatesolutions.org
 * License: GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 * License URI: https://www.gnu.org/licenses
 * Text Domain: kuraai-leadgen
 * Domain Path: /languages
 * WC requires at least: 5.0.0
 * WC tested up to: 8.0.0
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('KURAAI_LEADGEN_VERSION', '1.0.1');
define('KURAAI_LEADGEN_PLUGIN_FILE', __FILE__);
define('KURAAI_LEADGEN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KURAAI_LEADGEN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KURAAI_LEADGEN_NONCE', 'kuraai_leadgen_nonce_' . md5(__FILE__));

// Check PHP version
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('KuraAI Lead Gen requires PHP 7.4 or higher. Please upgrade your PHP version.', 'kuraai-leadgen');
        echo '</p></div>';
    });
    return;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('KuraAI Lead Gen requires WooCommerce to be installed and active.', 'kuraai-leadgen');
        echo '</p></div>';
    });
    return;
}

// Autoloader for plugin classes
spl_autoload_register(function ($class_name) {
    $prefix = 'KuraAI_LeadGen\\';

    if (strncmp($prefix, $class_name, strlen($prefix)) !== 0) {
        return;
    }

    $relative_class = substr($class_name, strlen($prefix));
    $parts = explode('\\', $relative_class);
    $base_dir = KURAAI_LEADGEN_PLUGIN_DIR;

    if ($parts[0] === 'Admin') {
        $filename = 'class-' . str_replace('_', '-', strtolower(implode('-', array_slice($parts, 1)))) . '.php';
        $file = $base_dir . 'admin/' . $filename;
    } else {
        $filename = 'class-' . str_replace('_', '-', strtolower(implode('-', $parts))) . '.php';
        $file = $base_dir . 'includes/' . $filename;
    }

    if (file_exists($file)) {
        require $file;
    } else {
        error_log("KuraAI Lead Gen: Class file not found: {$file}");
    }
});

// Add settings link to plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=kuraai-leadgen') . '">' . __('Settings', 'kuraai-leadgen') . '</a>';
    $docs_link = '<a href="https://kuraai-lead-gen.danovatesolutions.org/docs" target="_blank">' . __('Docs', 'kuraai-leadgen') . '</a>';
    array_unshift($links, $settings_link, $docs_link);
    return $links;
});

// Initialize the plugin
add_action('plugins_loaded', function () {
    // Load text domain
    load_plugin_textdomain('kuraai-leadgen', false, dirname(plugin_basename(__FILE__)) . '/languages/');

    // Check for API keys
    $settings = get_option('kuraai_leadgen_settings');
    if (empty($settings['openai_api_key']) && empty($settings['gemini_api_key'])) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('KuraAI Lead Gen requires an OpenAI or Google Gemini API key to be set. Please go to the settings page to add your API key.', 'kuraai-leadgen');
            echo '</p></div>';
        });
        return;
    }

    // Initialize main components
    if (is_admin() && !wp_doing_ajax()) {
        new KuraAI_LeadGen\Admin\Admin_Menu();
        new KuraAI_LeadGen\Admin\Scheduler();
    }

    new KuraAI_LeadGen\AI_Auditor();
    new KuraAI_LeadGen\Webhook_Handler();
    new KuraAI_LeadGen\Logger();
    new KuraAI_LeadGen\Hooks();
}, 20); // Priority 20 to ensure WooCommerce is loaded

// Register activation and deactivation hooks
register_activation_hook(__FILE__, ['KuraAI_LeadGen\Installer', 'activate']);
register_deactivation_hook(__FILE__, ['KuraAI_LeadGen\Installer', 'deactivate']);