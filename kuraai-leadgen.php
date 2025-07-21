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
    // Project-specific namespace prefix
    $prefix = 'KuraAI_LeadGen\\';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class_name, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class_name, $len);

    // Replace namespace separators with directory separators
    $path = str_replace('\\', '/', $relative_class);

    // Convert class names to file naming convention
    $path = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $path));

    // Build the full path to the class file
    $file = KURAAI_LEADGEN_PLUGIN_DIR . 'includes/class-' . $path . '.php';

    // Check for admin classes in admin directory
    if (strpos($relative_class, 'Admin\\') === 0) {
        $admin_path = str_replace('Admin/', '', $path);
        $file = KURAAI_LEADGEN_PLUGIN_DIR . 'admin/class-' . $admin_path . '.php';
    }

    // If the file exists, require it
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