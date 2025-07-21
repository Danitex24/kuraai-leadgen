<?php
namespace KuraAI_LeadGen;

class Logger
{
    public function __construct()
    {
        $settings = get_option('kuraai_leadgen_settings');

        if (isset($settings['enable_tracking']) && $settings['enable_tracking'] === 'yes') {
            add_action('woocommerce_before_single_product', [$this, 'track_product_view']);
            add_action('woocommerce_add_to_cart', [$this, 'track_add_to_cart']);
        }
    }

    public function track_product_view()
    {
        global $product;

        if (!is_product() || !$product) {
            return;
        }

        $this->log_activity($product->get_id(), 'view');
    }

    public function track_add_to_cart($cart_item_key, $product_id)
    {
        $this->log_activity($product_id, 'cart_add');
    }

    private function log_activity($product_id, $activity_type)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'kuraai_leadgen_activity';

        // Check if record exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE product_id = %d AND activity_type = %s",
            $product_id,
            $activity_type,
        ));

        if ($existing) {
            // Update existing record
            $wpdb->update(
                $table_name,
                [
                    'activity_count' => $existing->activity_count + 1,
                    'last_updated' => current_time('mysql')
                ],
                [
                    'id' => $existing->id
                ],
            );
        } else {
            // Insert new record
            $wpdb->insert(
                $table_name,
                [
                    'product_id' => $product_id,
                    'activity_type' => $activity_type,
                    'activity_count' => 1,
                    'last_updated' => current_time('mysql')
                ],
            );
        }
    }
}