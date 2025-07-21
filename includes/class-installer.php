<?php
namespace KuraAI_LeadGen;

class Installer
{
    public static function activate()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        // Create required database tables
        self::create_tables();

        // Schedule cron events
        wp_schedule_event(time(), 'daily', 'kuraai_leadgen_daily_checkup');
    }

    public static function deactivate()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        // Clear scheduled events
        wp_clear_scheduled_hook('kuraai_leadgen_daily_checkup');
    }

    private static function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kuraai_leadgen_audits (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            store_id bigint(20) NOT NULL,
            audit_type varchar(50) NOT NULL,
            audit_data longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY store_id (store_id),
            KEY audit_type (audit_type)
        ) $charset_collate;";

        $sql .= "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kuraai_leadgen_product_activity (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            activity_type varchar(50) NOT NULL,
            activity_count bigint(20) NOT NULL DEFAULT 0,
            last_updated datetime NOT NULL,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY activity_type (activity_type)
        ) $charset_collate;";

        $sql .= "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kuraai_leadgen_activity (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            object_type varchar(50) NOT NULL,
            object_id bigint(20) NOT NULL,
            activity_type varchar(50) NOT NULL,
            activity_data longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY object_type (object_type),
            KEY object_id (object_id),
            KEY activity_type (activity_type)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Add version option to support future updates
        add_option('kuraai_leadgen_db_version', '1.0.0');
    }
}