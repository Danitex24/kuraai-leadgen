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
    if (strpos($class_name, 'KuraAI_LeadGen\\') === 0) {
        $class_file = str_replace('KuraAI_LeadGen\\', '', $class_name);
        $class_file = str_replace('_', '-', $class_file);
        $class_file = strtolower($class_file);
        $file_path = KURAAI_LEADGEN_PLUGIN_DIR . 'includes/class-' . $class_file . '.php';

        if (file_exists($file_path)) {
            require_once $file_path;
        }
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