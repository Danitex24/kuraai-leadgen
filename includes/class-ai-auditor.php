<?php
namespace KuraAI_LeadGen;

class AI_Auditor
{
    private $max_suggestions = 5;

    public function __construct()
    {
        add_action('wp_ajax_kuraai_run_audit', [$this, 'run_audit']);
        add_action('wp_ajax_kuraai_run_competitor_audit', [$this, 'run_competitor_audit']);
    }

    public function run_audit()
    {
        check_ajax_referer('kuraai_audit_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Permission denied.', 'kuraai-leadgen'));
        }

        $settings = get_option('kuraai_leadgen_settings');
        $api_key = $settings['openai_api_key'] ?? ($settings['gemini_api_key'] ?? '');

        if (empty($api_key)) {
            wp_send_json_error(__('Please configure your API key first.', 'kuraai-leadgen'));
        }

        $store_data = $this->get_store_data();
        $prompt = $this->get_audit_prompt($store_data);

        try {
            $response = $this->call_ai_api($api_key, $prompt);
            $suggestions = $this->parse_ai_response($response);

            // Limit to max suggestions
            $limited_suggestions = array_slice($suggestions, 0, $this->max_suggestions);

            // Add upgrade CTA if suggestions exceed limit
            if (count($suggestions) > $this->max_suggestions) {
                $limited_suggestions[] = [
                    'type' => 'cta',
                    'content' => __('Upgrade to KuraAI Pro to unlock unlimited smart suggestions and deeper optimization insights.', 'kuraai-leadgen')
                ];
            }

            wp_send_json_success($limited_suggestions);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function run_competitor_audit()
    {
        check_ajax_referer('kuraai_competitor_audit_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Permission denied.', 'kuraai-leadgen'));
        }

        $competitor_url = isset($_POST['competitor_url']) ? esc_url_raw($_POST['competitor_url']) : '';
        if (empty($competitor_url)) {
            wp_send_json_error(__('Please enter a valid competitor URL.', 'kuraai-leadgen'));
        }

        $settings = get_option('kuraai_leadgen_settings');
        $api_key = $settings['openai_api_key'] ?? ($settings['gemini_api_key'] ?? '');

        if (empty($api_key)) {
            wp_send_json_error(__('Please configure your API key first.', 'kuraai-leadgen'));
        }

        $prompt = sprintf(
            "Analyze the e-commerce strategy of this competitor website: %s. Provide 2-3 bullet points summarizing their key strengths and strategies. Focus on product presentation, pricing, and marketing approaches.",
            $competitor_url,
        );

        try {
            $response = $this->call_ai_api($api_key, $prompt);
            $summary = $this->parse_ai_response($response);

            // Limit to 3 bullets and add upgrade CTA
            $limited_summary = array_slice($summary, 0, 3);
            $limited_summary[] = [
                'type' => 'cta',
                'content' => __('Upgrade to Pro to audit up to 10 competitors and access detailed product strategy breakdowns.', 'kuraai-leadgen')
            ];

            wp_send_json_success($limited_summary);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    private function get_store_data()
    {
        $data = [];

        // Get basic store info
        $data['store_name'] = get_bloginfo('name');
        $data['store_url'] = get_site_url();

        // Get product counts
        $data['product_count'] = wp_count_posts('product')->publish;
        $data['category_count'] = wp_count_terms('product_cat');

        // Get order data
        $data['order_count'] = wp_count_posts('shop_order')->wc - completed;

        return $data;
    }

    private function get_audit_prompt($store_data)
    {
        return sprintf(
            "Analyze this WooCommerce store and provide %d specific suggestions for improvement in these areas: SEO, product descriptions, user experience, and conversion rate optimization. Store details: Name: %s, URL: %s, Products: %d, Categories: %d, Orders: %d. Provide concise, actionable recommendations.",
            $this->max_suggestions + 3, // Get a few extra to allow for filtering
            $store_data['store_name'],
            $store_data['store_url'],
            $store_data['product_count'],
            $store_data['category_count'],
            $store_data['order_count'],
        );
    }

    private function call_ai_api($api_key, $prompt)
    {
        $settings = get_option('kuraai_leadgen_settings');
        $is_gemini = isset($settings['gemini_api_key']) && !empty($settings['gemini_api_key']);

        if ($is_gemini) {
            return $this->call_gemini_api($api_key, $prompt);
        } else {
            return $this->call_openai_api($api_key, $prompt);
        }
    }

    private function call_openai_api($api_key, $prompt)
    {
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ],
            'body' => json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000
            ]),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            throw new \Exception($body['error']['message']);
        }

        return $body['choices'][0]['message']['content'] ?? '';
    }

    private function call_gemini_api($api_key, $prompt)
    {
        $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'contents' => [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            throw new \Exception($body['error']['message']);
        }

        return $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    private function parse_ai_response($response)
    {
        $suggestions = [];

        // Split response into lines and filter empty ones
        $lines = array_filter(explode("\n", $response));

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and lines that are just numbers or bullets
            if (empty($line) || preg_match('/^[\d•\-]+$/', $line)) {
                continue;
            }

            // Remove leading numbers or bullets
            $line = preg_replace('/^[\d•\-]+\.?\s*/', '', $line);

            if (!empty($line)) {
                $suggestions[] = [
                    'type' => 'suggestion',
                    'content' => $line
                ];
            }
        }

        return $suggestions;
    }
}