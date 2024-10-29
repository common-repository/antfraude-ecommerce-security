<?php
/**
 * @author AntFraude Ecommerce Security
 * @category consulta remota no pagarme
 */
class ATFR_PagarMe 
{
    private $guid;

    public function __construct()
    {
        $this->guid = get_option('woocommerce_pagarme-credit-card_settings', 'default text');
    }
    public function getData($id)
    {
        
        // Access params
        $key      = (isset($this->guid['api_key'])) ? $this->guid['api_key'] : null;
        $endpoint = "https://api.pagar.me/1/transactions/";
        $params   = ['api_key' => $key];
        $url      = $endpoint . $id . '?' . http_build_query($params);       
        
        // Get data from gateway
        $response = wp_remote_get($url, $params);
        // $ch       = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // $output = curl_exec($ch);
        // curl_close($ch);
        //error_log(print_r($output, true));
        //return json_decode($output);
        return json_decode( wp_remote_retrieve_body( $response ) );
    }

}
