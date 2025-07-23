<?php
namespace KuraAI_LeadGen\Admin;

class Scheduler
{
    public function __construct()
    {
        add_action('kuraai_leadgen_daily_checkup', [$this, 'run_scheduled_checkup']);
        add_filter('cron_schedules', [$this, 'add_custom_schedules']);

        // Register settings for scheduling
        add_action('admin_init', [$this, 'register_schedule_settings']);
    }

    public function render_page()
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Scheduled WooCommerce Checkups', 'kuraai-leadgen'); ?></h1>
            <p><?php esc_html_e('This page is for informational purposes only. The plugin automatically runs scheduled checkups based on your settings.', 'kuraai-leadgen'); ?></p>
        </div>
        <?php
    }

    public function register_schedule_settings()
    {
    }

    public function render_schedule_section()
    {
        echo '<p>' . esc_html__('Configure scheduled store checkups and reporting.', 'kuraai-leadgen') . '</p>';
    }

    public function render_frequency_field()
    {
        $schedule = get_option('kuraai_leadgen_schedule');
        $frequency = $schedule['frequency'] ?? 'weekly';
        ?>
        <select name="kuraai_leadgen_schedule[frequency]">
            <option value="daily" <?php selected($frequency, 'daily'); ?>><?php esc_html_e('Daily', 'kuraai-leadgen'); ?>
            </option>
            <option value="weekly" <?php selected($frequency, 'weekly'); ?>><?php esc_html_e('Weekly', 'kuraai-leadgen'); ?>
            </option>
            <option value="monthly" <?php selected($frequency, 'monthly'); ?>><?php esc_html_e('Monthly', 'kuraai-leadgen'); ?>
            </option>
        </select>
        <?php
    }

    public function render_email_reports_field()
    {
        $schedule = get_option('kuraai_leadgen_schedule');
        $enabled = $schedule['email_reports'] ?? 'no';
        ?>
        <input type="checkbox" name="kuraai_leadgen_schedule[email_reports]" value="yes" <?php checked($enabled, 'yes'); ?>>
        <span class="description"><?php esc_html_e('Send email reports for scheduled checkups', 'kuraai-leadgen'); ?></span>
        <?php
    }

    public function add_custom_schedules($schedules)
    {
        $schedules['weekly'] = [
            'interval' => 604800,
            'display' => __('Once Weekly', 'kuraai-leadgen')
        ];

        $schedules['monthly'] = [
            'interval' => 2635200,
            'display' => __('Once Monthly', 'kuraai-leadgen')
        ];

        return $schedules;
    }

    public function run_scheduled_checkup()
    {
        if (!class_exists('KuraAI_LeadGen\Admin\AI_Auditor')) {
            return;
        }

        $settings = get_option('kuraai_leadgen_settings');
        if (empty($settings['openai_api_key']) && empty($settings['gemini_api_key'])) {
            return;
        }

        $auditor = new \KuraAI_LeadGen\Admin\AI_Auditor();
        $store_data = $auditor->get_store_data();
        $prompt = $auditor->get_audit_prompt($store_data);

        try {
            $response = $auditor->call_ai_api($api_key, $prompt);
            $suggestions = $auditor->parse_ai_response($response);

            // Limit to max suggestions
            $limited_suggestions = array_slice($suggestions, 0, $auditor->max_suggestions);

            // Store the audit results
            $this->store_audit_results($limited_suggestions);

            // Send email if enabled
            $schedule = get_option('kuraai_leadgen_schedule');
            if (isset($schedule['email_reports']) && $schedule['email_reports'] === 'yes') {
                $this->send_audit_email($limited_suggestions);
            }
        } catch (\Exception $e) {
            error_log('KuraAI Lead Gen scheduled checkup failed: ' . $e->getMessage());
        }
    }

    private function store_audit_results($suggestions)
    {
        global $wpdb;

        $data = [
            'store_id' => get_current_blog_id(),
            'audit_type' => 'scheduled',
            'audit_data' => json_encode($suggestions),
            'created_at' => current_time('mysql')
        ];

        $wpdb->insert("{$wpdb->prefix}kuraai_leadgen_audits", $data);
    }

    private function send_audit_email($suggestions)
    {
        $to = get_option('admin_email');
        $subject = __('Your KuraAI Store Audit Report', 'kuraai-leadgen');

        $message = '<h2>' . __('Store Audit Report', 'kuraai-leadgen') . '</h2>';
        $message .= '<p>' . sprintf(__('Here are your latest store audit results from %s:', 'kuraai-leadgen'), get_bloginfo('name')) . '</p>';
        $message .= '<ul>';

        foreach ($suggestions as $suggestion) {
            if ($suggestion['type'] === 'suggestion') {
                $message .= '<li>' . esc_html($suggestion['content']) . '</li>';
            }
        }

        $message .= '</ul>';
        $message .= '<p>' . __('Upgrade to KuraAI Pro to unlock unlimited smart suggestions and deeper optimization insights.', 'kuraai-leadgen') . '</p>';

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($to, $subject, $message, $headers);
    }
}