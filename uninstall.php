<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Remove plugin options
delete_option('kuraai_leadgen_settings');
delete_option('kuraai_leadgen_version');

// Remove database tables
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}kuraai_leadgen_audits");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}kuraai_leadgen_activity");

// Clear scheduled events
wp_clear_scheduled_hook('kuraai_leadgen_daily_checkup');