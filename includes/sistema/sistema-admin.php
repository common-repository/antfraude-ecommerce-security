<?php
class ATFR_AdminSystem
{
    public function sendData($transaction_data)
    {
        // Get Token
        include_once dirname(__FILE__) . '/class-antfraude-jwt-token.php';
        $token = new ATFR_AntFraudeJwtToken();

        // gets our accesss key
        $uid = get_option('woocommerce_antfraude-plugin_settings', 'default text');

        $transaction_data['token']              = $token->token();
        $transaction_data['unique_customer_id'] = $uid['antfraude_access_key'];
        
        $args = [
            'body'        => $transaction_data,
            'timeout'     => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => [],
            'cookies'     => []
        ];

        $url = 'https://app.antfraude.com/index.php?order';
        wp_remote_post($url, $args);
    }
}