<?php
namespace KuraAI_LeadGen;

class Webhook_Handler
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_webhook_endpoint']);
        add_action('admin_post_kuraai_test_api', [$this, 'test_api_connection']);
    }

    public function register_webhook_endpoint()
    {
        register_rest_route('kuraai-leadgen/v1', '/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_webhook'],
            'permission_callback' => [$this, 'verify_webhook'],
        ]);
    }

    public function handle_webhook(\WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        // Process webhook data here
        // This is a placeholder for Pro version functionality

        return new \WP_REST_Response(['success' => true], 200);
    }

    public function verify_webhook(\WP_REST_Request $request)
    {
        // Implement webhook verification logic
        // For free version, just return true
        return true;
    }

    public function test_api_connection()
    {
        check_admin_referer('kuraai_test_api');

        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Permission denied.', 'kuraai-leadgen'));
        }

        $settings = get_option('kuraai_leadgen_settings');
        $api_key = $settings['openai_api_key'] ?? ($settings['gemini_api_key'] ?? '');

        if (empty($api_key)) {
            wp_redirect(add_query_arg('kuraai_error', urlencode(__('API key is not configured.', 'kuraai-leadgen')), admin_url('admin.php?page=kuraai-leadgen')));
            exit;
        }

        try {
            $auditor = new AI_Auditor();

            if (isset($settings['gemini_api_key'])) {
                $response = $auditor->call_gemini_api($api_key, 'Test connection');
            } else {
                $response = $auditor->call_openai_api($api_key, 'Test connection');
            }

            wp_redirect(add_query_arg('kuraai_success', urlencode(__('API connection successful!', 'kuraai-leadgen')), admin_url('admin.php?page=kuraai-leadgen')));
            exit;
        } catch (\Exception $e) {
            wp_redirect(add_query_arg('kuraai_error', urlencode($e->getMessage()), admin_url('admin.php?page=kuraai-leadgen')));
            exit;
        }
    }
}