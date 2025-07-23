<?php
/**
 * Class WooCommerce_Hooks
 *
 * @package KuraAI_LeadGen\Admin
 */

namespace KuraAI_LeadGen\Admin;

/**
 * Class WooCommerce_Hooks
 *
 * @package KuraAI_LeadGen\Admin
 */
class WooCommerce_Hooks {

    /**
     * WooCommerce_Hooks constructor.
     */
    public function __construct() {
        add_action('woocommerce_new_order', [$this, 'log_new_order']);
        add_action('woocommerce_new_product', [$this, 'log_new_product']);
        add_action('woocommerce_order_status_completed', [$this, 'log_completed_order']);
    }

    /**
     * Log a new order.
     *
     * @param int $order_id The ID of the new order.
     */
    public function log_new_order($order_id) {
        error_log('New order created: ' . $order_id);
    }

    /**
     * Log a new product.
     *
     * @param int $product_id The ID of the new product.
     */
    public function log_new_product($product_id) {
        error_log('New product created: ' . $product_id);
    }

    /**
     * Log a completed order.
     *
     * @param int $order_id The ID of the completed order.
     */
    public function log_completed_order($order_id) {
        error_log('Order completed: ' . $order_id);
    }

    /**
     * Render the WooCommerce Hook Integration page.
     */
    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('WooCommerce Hook Integration', 'kuraai-leadgen'); ?></h1>
            <p><?php esc_html_e('This page is for informational purposes only. The plugin automatically integrates with WooCommerce hooks to monitor orders, new products, and revenue events.', 'kuraai-leadgen'); ?></p>
        </div>
        <?php
    }
}
