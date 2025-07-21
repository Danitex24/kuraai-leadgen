<?php
namespace KuraAI_LeadGen;

class Hooks
{
    public function __construct()
    {
        // WooCommerce order hooks
        add_action('woocommerce_new_order', [$this, 'on_new_order']);
        add_action('woocommerce_order_status_changed', [$this, 'on_order_status_change'], 10, 3);

        // Product hooks
        add_action('woocommerce_new_product', [$this, 'on_new_product']);
        add_action('woocommerce_update_product', [$this, 'on_update_product']);

        // Customer hooks
        add_action('woocommerce_created_customer', [$this, 'on_new_customer']);

        // Cart hooks
        add_action('woocommerce_cart_emptied', [$this, 'on_cart_emptied']);
    }

    /**
     * Handle new order creation
     * 
     * @param int $order_id
     */
    public function on_new_order($order_id)
    {
        $order = wc_get_order($order_id);

        // Basic tracking for free version
        $this->log_order_activity($order_id, 'created', [
            'status' => $order->get_status(),
            'total' => $order->get_total()
        ]);

        // Placeholder for Pro version functionality
        do_action('kuraai_pro_order_created', $order_id);
    }

    /**
     * Handle order status changes
     * 
     * @param int $order_id
     * @param string $old_status
     * @param string $new_status
     */
    public function on_order_status_change($order_id, $old_status, $new_status)
    {
        // Basic tracking for free version
        $this->log_order_activity($order_id, 'status_change', [
            'from' => $old_status,
            'to' => $new_status
        ]);

        // Placeholder for Pro version functionality
        do_action('kuraai_pro_order_status_changed', $order_id, $old_status, $new_status);
    }

    /**
     * Handle new product creation
     * 
     * @param int $product_id
     */
    public function on_new_product($product_id)
    {
        $product = wc_get_product($product_id);

        // Basic tracking for free version
        $this->log_product_activity($product_id, 'created', [
            'type' => $product->get_type(),
            'status' => $product->get_status()
        ]);

        // Placeholder for Pro version functionality
        do_action('kuraai_pro_product_created', $product_id);
    }

    /**
     * Handle product updates
     * 
     * @param int $product_id
     */
    public function on_update_product($product_id)
    {
        // Basic tracking for free version
        $this->log_product_activity($product_id, 'updated');

        // Placeholder for Pro version functionality
        do_action('kuraai_pro_product_updated', $product_id);
    }

    /**
     * Handle new customer registration
     * 
     * @param int $customer_id
     */
    public function on_new_customer($customer_id)
    {
        // Basic tracking for free version
        $this->log_customer_activity($customer_id, 'registered');

        // Placeholder for Pro version functionality
        do_action('kuraai_pro_customer_created', $customer_id);
    }

    /**
     * Handle cart emptied
     */
    public function on_cart_emptied()
    {
        // Basic tracking for free version
        $this->log_general_activity('cart_emptied');

        // Placeholder for Pro version functionality
        do_action('kuraai_pro_cart_emptied');
    }

    /**
     * Log order activity
     * 
     * @param int $order_id
     * @param string $action
     * @param array $data
     */
    private function log_order_activity($order_id, $action, $data = [])
    {
        global $wpdb;

        $wpdb->insert(
            "{$wpdb->prefix}kuraai_leadgen_activity",
            [
                'object_type' => 'order',
                'object_id' => $order_id,
                'activity_type' => $action,
                'activity_data' => json_encode($data),
                'created_at' => current_time('mysql')
            ],
        );
    }

    /**
     * Log product activity
     * 
     * @param int $product_id
     * @param string $action
     * @param array $data
     */
    private function log_product_activity($product_id, $action, $data = [])
    {
        global $wpdb;

        $wpdb->insert(
            "{$wpdb->prefix}kuraai_leadgen_activity",
            [
                'object_type' => 'product',
                'object_id' => $product_id,
                'activity_type' => $action,
                'activity_data' => json_encode($data),
                'created_at' => current_time('mysql')
            ],
        );
    }

    /**
     * Log customer activity
     * 
     * @param int $customer_id
     * @param string $action
     * @param array $data
     */
    private function log_customer_activity($customer_id, $action, $data = [])
    {
        global $wpdb;

        $wpdb->insert(
            "{$wpdb->prefix}kuraai_leadgen_activity",
            [
                'object_type' => 'customer',
                'object_id' => $customer_id,
                'activity_type' => $action,
                'activity_data' => json_encode($data),
                'created_at' => current_time('mysql')
            ],
        );
    }

    /**
     * Log general activity
     * 
     * @param string $action
     * @param array $data
     */
    private function log_general_activity($action, $data = [])
    {
        global $wpdb;

        $wpdb->insert(
            "{$wpdb->prefix}kuraai_leadgen_activity",
            [
                'object_type' => 'system',
                'object_id' => 0,
                'activity_type' => $action,
                'activity_data' => json_encode($data),
                'created_at' => current_time('mysql')
            ],
        );
    }
}