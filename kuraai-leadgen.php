<?php
/**
 * Plugin Name: KuraAI Lead Gen
 * Plugin URI: https://kuraai-lead-gen.danovatesolutions.org
 * Description: AI-powered WooCommerce audits and sales improvement suggestions.
 * Version: 1.0.0
 * Author: Daniel Abughdyer
 * Author URI: https://kuraai-lead-gen.danovatesolutions.org
 * License: GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 * License URI: https://www.gnu.org/licenses
 * Text Domain: kuraai-leadgen
 * Domain Path: /languages
 * WC requires at least: 5.0.0
 * WC tested up to: 8.0.0
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('KURAAI_LEADGEN_VERSION', '1.0.0');
define('KURAAI_LEADGEN_PLUGIN_FILE', __FILE__);
define('KURAAI_LEADGEN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KURAAI_LEADGEN_PLUGIN_URL', plugin_dir_url(__FILE__));

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
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name
    $relative_class = substr($class_name, $len);

    // Replace the namespace prefix with the base directory
    // Replace namespace separators with directory separators
    $path = str_replace('\\', '/', $relative_class);

    // Convert class names to file naming convention
    $path = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $path));

    // Build the full path to the class file
    $file = KURAAI_LEADGEN_PLUGIN_DIR . 'includes/class-' . $path . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
add_action('plugins_loaded', function () {
    // Load text domain
    load_plugin_textdomain('kuraai-leadgen', false, dirname(plugin_basename(__FILE__)) . '/languages/');

    // Initialize main components
    if (is_admin()) {
        new KuraAI_LeadGen\Admin\Admin_Menu();
        new KuraAI_LeadGen\Admin\Scheduler();
    }

    new KuraAI_LeadGen\AI_Auditor();
    new KuraAI_LeadGen\Webhook_Handler();
    new KuraAI_LeadGen\Logger();
    new KuraAI_LeadGen\Hooks();
});

// Register activation and deactivation hooks
register_activation_hook(__FILE__, ['KuraAI_LeadGen\Installer', 'activate']);
register_deactivation_hook(__FILE__, ['KuraAI_LeadGen\Installer', 'deactivate']);