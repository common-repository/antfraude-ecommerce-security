<?php
/**
 * Plugin Name:       AntFraude - Ecommerce Security
 * Plugin URI:        https://www.antfraude.com
 * Description:       Plugin para analise de Veracidade de informações
 * Version:           0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author URI:        https://www.antfraude.com
 * Author URI:        https://antfraude.com/
 * License:           GPL v2 or later
 * License URI:       https://antfraude/licenses/antfraude-wp-plugin.html
 * Text Domain:       ant-fraude-ecommerce-security
 * Domain Path:       /anfraude
 */
if(!class_exists('WC_antfraude_plugin')):

    include_once dirname(__FILE__) . '/includes/pagarme/pagarme.php';
    include_once dirname(__FILE__) . '/includes/email/envia-email.php';
    include_once dirname(__FILE__) . '/includes/sistema/sistema-admin.php';

    class WC_antfraude_plugin
    {
        private $url;
        private $api;
        private $sendMail;
        private $adminSystem;
        /**
         * Construct the plugin
         */
        public function __construct()
        {
            //$dados = get_option( 'woocommerce_pagseguro_settings' );
            
            $this->url         = 'http://sistema-antfraude-php56/index.php?api/add';
            $this->sendMail    = new ATFR_SendMail();
            $this->adminSystem = new ATFR_AdminSystem();

            
            add_action('plugins_loaded', [$this, 'init']);

        }

        /**
         * Initialize the plugin
         */
        public function init()
        {
            // Check if Woocommerce is installed.
            if(class_exists('WC_Integration'))
            {
                // Include our integration class.
                include_once 'class-wc-integration.php';
                // Register the integration
                add_filter('woocommerce_integrations', [$this, 'add_integration']);
                // set the plugin slug
                define('ATFR_PLUGIN_SLUG', 'wc-settings');
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'WC_antfraude_plugin_action_links']);

                add_action( 'woocommerce_thankyou', [$this, 'get_payment_info'], 1);
            }
        }

        /**
         * add a new integration to Woocommerce
         */
        public function add_integration($integrations)
        {
            $integrations[] = 'WC_Antfraude_plugin_Integration';
            return $integrations;
        }

        /**
         * add Link
         */
        public function WC_antfraude_plugin_action_links($links)
        {
            $links[] = '<a href="'.menu_page_url(ATFR_PLUGIN_SLUG, false) . '&tab=integration">Configurações</a>';
            return $links;
        }

        /**
         * Send information to api
         */
        public function action_checkout_order_processed($order_id, $posted_data, $order)
        {
            // include our token 
            include_once 'class-antfraude-jwt-token.php';
            $token = new AntFraudeJwtToken();


            // gets our accesss key
            $uid = get_option('woocommerce_antfraude-plugin_settings', 'default text');
            
            // Gets order details
            $order = wc_get_order($order_id);
            $data  = $order->get_data();
            
            $to = $data['billing']['email'];
            $headers = [
                'Content-Type: text/html; charset=UTF-8'
            ];
            $message = <<<EOD
                <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" style="width:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">
                 <head> 
                  <meta charset="UTF-8"> 
                  <meta content="width=device-width, initial-scale=1" name="viewport"> 
                  <meta name="x-apple-disable-message-reformatting"> 
                  <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
                  <meta content="telephone=no" name="format-detection"> 
                  <title>New email</title> 
                  <!--[if (mso 16)]>
                    <style type="text/css">
                    a {text-decoration: none;}
                    </style>
                    <![endif]--> 
                  <!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--> 
                  <!--[if gte mso 9]>
                <xml>
                    <o:OfficeDocumentSettings>
                    <o:AllowPNG></o:AllowPNG>
                    <o:PixelsPerInch>96</o:PixelsPerInch>
                    </o:OfficeDocumentSettings>
                </xml>
                <![endif]--> 
                  <style type="text/css">
                @media only screen and (max-width:600px) {p, ul li, ol li, a { font-size:16px!important; line-height:150%!important } h1 { font-size:30px!important; text-align:center; line-height:120%!important } h2 { font-size:26px!important; text-align:center; line-height:120%!important } h3 { font-size:20px!important; text-align:center; line-height:120%!important } h1 a { font-size:30px!important } h2 a { font-size:26px!important } h3 a { font-size:20px!important } .es-menu td a { font-size:16px!important } .es-header-body p, .es-header-body ul li, .es-header-body ol li, .es-header-body a { font-size:16px!important } .es-footer-body p, .es-footer-body ul li, .es-footer-body ol li, .es-footer-body a { font-size:16px!important } .es-infoblock p, .es-infoblock ul li, .es-infoblock ol li, .es-infoblock a { font-size:12px!important } *[class="gmail-fix"] { display:none!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3 { text-align:right!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-button-border { display:block!important } a.es-button { font-size:20px!important; display:block!important; border-width:10px 0px 10px 0px!important } .es-btn-fw { border-width:10px 0px!important; text-align:center!important } .es-adaptive table, .es-btn-fw, .es-btn-fw-brdr, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .es-adapt-td { display:block!important; width:100%!important } .adapt-img { width:100%!important; height:auto!important } .es-m-p0 { padding:0px!important } .es-m-p0r { padding-right:0px!important } .es-m-p0l { padding-left:0px!important } .es-m-p0t { padding-top:0px!important } .es-m-p0b { padding-bottom:0!important } .es-m-p20b { padding-bottom:20px!important } .es-mobile-hidden, .es-hidden { display:none!important } tr.es-desk-hidden, td.es-desk-hidden, table.es-desk-hidden { width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } tr.es-desk-hidden { display:table-row!important } table.es-desk-hidden { display:table!important } td.es-desk-menu-hidden { display:table-cell!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } table.es-social { display:inline-block!important } table.es-social td { display:inline-block!important } }
                #outlook a {
                    padding:0;
                }
                .ExternalClass {
                    width:100%;
                }
                .ExternalClass,
                .ExternalClass p,
                .ExternalClass span,
                .ExternalClass font,
                .ExternalClass td,
                .ExternalClass div {
                    line-height:100%;
                }
                .es-button {
                    mso-style-priority:100!important;
                    text-decoration:none!important;
                }
                a[x-apple-data-detectors] {
                    color:inherit!important;
                    text-decoration:none!important;
                    font-size:inherit!important;
                    font-family:inherit!important;
                    font-weight:inherit!important;
                    line-height:inherit!important;
                }
                .es-desk-hidden {
                    display:none;
                    float:left;
                    overflow:hidden;
                    width:0;
                    max-height:0;
                    line-height:0;
                    mso-hide:all;
                }
                </style> 
                 </head> 
                 <body style="width:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0"> 
                  <div class="es-wrapper-color" style="background-color:#F6F6F6"> 
                   <!--[if gte mso 9]>
                            <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
                                <v:fill type="tile" color="#f6f6f6"></v:fill>
                            </v:background>
                        <![endif]--> 
                   <table cellpadding="0" cellspacing="0" class="es-wrapper" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top"> 
                     <tr style="border-collapse:collapse"> 
                      <td valign="top" style="padding:0;Margin:0"> 
                       <table cellpadding="0" cellspacing="0" class="es-content" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%"> 
                         <tr style="border-collapse:collapse"> 
                          <td align="center" style="padding:0;Margin:0"> 
                           <table class="es-content-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px"> 
                             <tr style="border-collapse:collapse"> 
                              <td align="left" style="padding:20px;Margin:0"> 
                               <!--[if mso]><table style="width:560px"><tr><td style="width:356px" valign="top"><![endif]--> 
                               <table cellpadding="0" cellspacing="0" class="es-left" align="left" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:left"> 
                                 <tr style="border-collapse:collapse"> 
                                  <td class="es-m-p0r es-m-p20b" valign="top" align="center" style="padding:0;Margin:0;width:356px"> 
                                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"> 
                                     <tr style="border-collapse:collapse"> 
                                      <td align="left" class="es-infoblock es-m-txt-c" style="padding:0;Margin:0;line-height:14px;font-size:12px;color:#CCCCCC"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:12px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:14px;color:#CCCCCC">Se você não conseguir ver esse email só clicar no link ao lado</p></td> 
                                     </tr> 
                                   </table></td> 
                                 </tr> 
                               </table> 
                               <!--[if mso]></td><td style="width:20px"></td><td style="width:184px" valign="top"><![endif]--> 
                               <table cellpadding="0" cellspacing="0" align="right" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"> 
                                 <tr style="border-collapse:collapse"> 
                                  <td align="left" style="padding:0;Margin:0;width:184px"> 
                                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"> 
                                     <tr style="border-collapse:collapse"> 
                                      <td align="right" class="es-infoblock es-m-txt-c" style="padding:0;Margin:0;line-height:14px;font-size:12px;color:#CCCCCC"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:12px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:14px;color:#CCCCCC"><a target="_blank" href="http://viewstripo.email" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-size:12px;text-decoration:underline;color:#2CB543">Ver esse email no navegador</a></p></td> 
                                     </tr> 
                                   </table></td> 
                                 </tr> 
                               </table> 
                               <!--[if mso]></td></tr></table><![endif]--></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table> 
                       <table cellpadding="0" cellspacing="0" class="es-content" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%"> 
                         <tr style="border-collapse:collapse"> 
                          <td align="center" style="padding:0;Margin:0"> 
                           <table class="es-content-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px"> 
                             <tr style="border-collapse:collapse"> 
                              <td align="left" style="padding:20px;Margin:0;background-color:#E3FBE7" bgcolor="#e3fbe7"> 
                               <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"> 
                                 <tr style="border-collapse:collapse"> 
                                  <td align="center" valign="top" style="padding:0;Margin:0;width:560px"> 
                                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"> 
                                     <tr style="border-collapse:collapse"> 
                                      <td align="center" style="padding:0;Margin:0;font-size:0px"><img class="adapt-img" src="assets/images/90621598630471859.png" alt style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" width="180" height="180"></td> 
                                     </tr> 
                                     <tr style="border-collapse:collapse"> 
                                      <td align="left" style="padding:0;Margin:0;padding-bottom:15px"><h2 style="Margin:0;line-height:29px;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-size:24px;font-style:normal;font-weight:normal;color:#333333;text-align:center">Sua compra está passando por uma breve análise!</h2></td> 
                                     </tr> 
                                     <tr style="border-collapse:collapse"> 
                                      <td align="left" style="padding:0;Margin:0;padding-top:20px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333">Sentimos muito por esse inconveniente, mas para proteger seus dados, dados do titular do cartão e possível chargeback na loja&nbsp;é necessário passar por uma breve analise para evitar que possíveis fraudes venham à acontecer.<br><br><span style="color:#FF0000">Essa análise poderá durar até 24 horas.</span><br></p></td> 
                                     </tr> 
                                   </table></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table> 
                       <table cellpadding="0" cellspacing="0" class="es-footer" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top"> 
                         <tr style="border-collapse:collapse"> 
                          <td align="center" style="padding:0;Margin:0"> 
                           <table class="es-footer-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px"> 
                             <tr style="border-collapse:collapse"> 
                              <td align="left" bgcolor="#e3fbe7" style="padding:0;Margin:0;padding-top:20px;padding-left:20px;padding-right:20px;background-color:#E3FBE7"> 
                               <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"> 
                                 <tr style="border-collapse:collapse"> 
                                  <td align="center" valign="top" style="padding:0;Margin:0;width:560px"> 
                                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"> 
                                     <tr style="border-collapse:collapse"> 
                                      <td align="center" style="padding:0;Margin:0;padding-top:10px;padding-bottom:10px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:11px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:17px;color:#333333">© 2020 AntFraude</p></td> 
                                     </tr> 
                                   </table></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table> 
                       <table cellpadding="0" cellspacing="0" class="es-content" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%"> 
                         <tr style="border-collapse:collapse"> 
                          <td align="center" style="padding:0;Margin:0"> 
                           <table class="es-content-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px"> 
                             <tr style="border-collapse:collapse"> 
                              <td align="left" bgcolor="#e3fbe7" style="padding:0;Margin:0;padding-left:20px;padding-right:20px;padding-bottom:30px;background-color:#E3FBE7"> 
                               <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"> 
                                 <tr style="border-collapse:collapse"> 
                                  <td align="center" valign="top" style="padding:0;Margin:0;width:560px"> 
                                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px"> 
                                     <tr style="border-collapse:collapse"> 
                                      <td align="center" style="padding:0;Margin:0;font-size:0px"><img class="adapt-img" src="assets/images/90621598630471859.png" alt style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" width="100" height="100"></td> 
                                     </tr> 
                                   </table></td> 
                                 </tr> 
                               </table></td> 
                             </tr> 
                           </table></td> 
                         </tr> 
                       </table></td> 
                     </tr> 
                   </table> 
                  </div>  
                 </body>
                </html>
            EOD;

            $subject = 'Seus dados em analise';
            
        }        

        public function get_payment_info( $order_id ){

          $order       = wc_get_order( $order_id );
          $transaction = $order->get_data();
          
          $data        = [];

          // Get Order ID and Key
          $data['purchase_id']                          = $order->get_id();
          $data['order']['key']                         = $order->get_order_key();

          // Get Order Totals $0.00
          $data['order']['total']                       = $order->get_formatted_order_total();
          $data['order']['cart_tax']                    = $order->get_cart_tax();
          $data['order']['currency']                    = $order->get_currency();
          $data['order']['discount_tax']                = $order->get_discount_tax();
          //$data['order']['discount_to_display']         = $order->get_discount_to_display();
          $data['order']['discount_total']              = $order->get_discount_total();
          $data['order']['fees']                        = $order->get_fees();
          $data['order']['shipping_tax']                = $order->get_shipping_tax();
          $data['order']['shipping_total']              = $order->get_shipping_total();
          $data['order']['subtotal']                    = $order->get_subtotal();
          //$data['order']['subtotal_to_display']         = $order->get_subtotal_to_display();
          $data['order']['tax_totals']                  = $order->get_tax_totals();
          $data['order']['taxes']                       = $order->get_taxes();
          $data['order']['total']                       = $order->get_total();
          $data['order']['discount']                    = $order->get_total_discount();
          $data['order']['total_tax']                   = $order->get_total_tax();
          $data['order']['total_refund']                = $order->get_total_refunded();
          $data['order']['total_tax_refunded']          = $order->get_total_tax_refunded();
          $data['order']['total_shipping_total']        = $order->get_total_shipping_refunded();
          $data['order']['item_count_refunded']         = $order->get_item_count_refunded();
          $data['order']['total_qty_refunded']          = $order->get_total_qty_refunded();

          // Get and Loop Over Order Items
          foreach ( $order->get_items() as $item_id => $item ) {
            $data['order']['products'][] = [
              'product_id'                => $item->get_product_id(),
              'variation_id'              => $item->get_variation_id(),
              'product_sku'               => wc_get_product($item->get_product_id())->get_sku(),
              'product_price'             => wc_get_product($item->get_product_id())->get_price(),
              'product_regular_price'     => wc_get_product($item->get_product_id())->get_regular_price(),
              'product_sale_price'        => wc_get_product($item->get_product_id())->get_sale_price(),
              'product_date_on_sale_from' => wc_get_product($item->get_product_id())->get_date_on_sale_from(),
              'product_date_on_sale_to'   => wc_get_product($item->get_product_id())->get_date_on_sale_to(),
              'product_total_sales'       => wc_get_product($item->get_product_id())->get_total_sales(),
              'product_tax_status'        => wc_get_product($item->get_product_id())->get_tax_status(),
              'product_tax_class'         => wc_get_product($item->get_product_id())->get_tax_class(),
              'product_manage_stock'      => wc_get_product($item->get_product_id())->get_manage_stock(),
              'product_stock_quantity'    => wc_get_product($item->get_product_id())->get_stock_quantity(),
              'product_stock_status'      => wc_get_product($item->get_product_id())->get_stock_status(),
              'product_backorders'        => wc_get_product($item->get_product_id())->get_backorders(),
              'product_low_stock_amount'  => wc_get_product($item->get_product_id())->get_low_stock_amount(),
              'product_sold_individually' => wc_get_product($item->get_product_id())->get_sold_individually(),
              'product_weight'            => wc_get_product($item->get_product_id())->get_weight(),
              'product_length'            => wc_get_product($item->get_product_id())->get_length(),
              'product_width'             => wc_get_product($item->get_product_id())->get_width(),
              'product_height'            => wc_get_product($item->get_product_id())->get_height(),
              'product_parent_id'         => wc_get_product($item->get_product_id())->get_parent_id(),
              'product_purchase_note'     => wc_get_product($item->get_product_id())->get_purchase_note(),
              'name'                      => $item->get_name(),
              'quantity'                  => $item->get_quantity(),
              'subtotal'                  => $item->get_subtotal(),
              'total'                     => $item->get_total(),
              'tax'                       => $item->get_subtotal_tax(),
              'taxclass'                  => $item->get_tax_class(),
              'taxstat'                   => $item->get_tax_status(),
              'allmeta'                   => $item->get_meta_data(),
              'somemeta'                  => $item->get_meta( '_whatever', true ),
              'type'                      => $item->get_type()
            ];
          }

          // Other Secondary Items Stuff
          $data['order']['tax_classes']                 = $order->get_items_tax_classes();
          $data['order']['item_count']                  = $order->get_item_count();
          $data['order']['downloadable_items']          = $order->get_downloadable_items();

          // Get Order Shipping
          $data['order']['shipping_method']             = $order->get_shipping_method();
          $data['order']['shiiping_methods']            = $order->get_shipping_methods();
          $data['order']['shipping_to_display']         = $order->get_shipping_to_display();

          // Get Order Dates
          $data['order']['date_created']                = $order->get_date_created();
          $data['order']['date_modified']               = $order->get_date_modified();
          $data['order']['date_completed']              = $order->get_date_completed();
          $data['order']['date_paid']                   = $order->get_date_paid();

          // Get Order User, Billing & Shipping Addresses
          $data['order']['customer_id']                 = $order->get_customer_id();
          $data['order']['user_id']                     = $order->get_user_id();
          $data['order']['user']                        = $order->get_user();
          $data['order']['customer_ip_address']         = $order->get_customer_ip_address();
          $data['order']['customer_user_agent']         = $order->get_customer_user_agent();
          $data['order']['created_via']                 = $order->get_created_via();
          $data['order']['customer_note']               = $order->get_customer_note();
          $data['order']['cpf']                         = $order->get_meta('_billing_cpf');
          $data['order']['billing_first_name']          = $order->get_billing_first_name();
          $data['order']['billing_last_name']           = $order->get_billing_last_name();
          $data['order']['billing_company']             = $order->get_billing_company();
          $data['order']['billing_address_1']           = $order->get_billing_address_1();
          $data['order']['billing_address_2']           = $order->get_billing_address_2();
          $data['order']['billing_city']                = $order->get_billing_city();
          $data['order']['billing_state']               = $order->get_billing_state();
          $data['order']['billing_postcode']            = $order->get_billing_postcode();
          $data['order']['billing_country']             = $order->get_billing_country();
          $data['order']['billing_email']               = $order->get_billing_email();
          $data['order']['billing_phone']               = $order->get_billing_phone();
          $data['order']['shipping_first_name']         = $order->get_shipping_first_name();
          $data['order']['shipping_last_name']          = $order->get_shipping_last_name();
          $data['order']['shipping_company']            = $order->get_shipping_company();
          $data['order']['shipping_address_1']          = $order->get_shipping_address_1();
          $data['order']['shipping_address_2']          = $order->get_shipping_address_2();
          $data['order']['shipping_city']               = $order->get_shipping_city();
          $data['order']['shipping_state']              = $order->get_shipping_state();
          $data['order']['shipping_postcode']           = $order->get_shipping_postcode();
          $data['order']['shipping_country']            = $order->get_shipping_country();
          $data['order']['address']                     = $order->get_address();
          $data['order']['shipping_address_map_url']    = $order->get_shipping_address_map_url();
          $data['order']['billing_full_name']           = $order->get_formatted_billing_full_name();
          $data['order']['formated_shipping_full_name'] = $order->get_formatted_shipping_full_name();
          $data['order']['formated_billing_address']    = $order->get_formatted_billing_address();
          $data['order']['formated_shipping_address']   = $order->get_formatted_shipping_address();

          // Get Order Payment Details
          $data['order']['payment_method']              = $order->get_payment_method();
          $data['order']['payment_method_title']        = $order->get_payment_method_title();
          $data['order']['transaction_id']              = $order->get_transaction_id();

          // Get Order URLs
          $data['order']['checkout_payment_url']        = $order->get_checkout_payment_url();
          $data['order']['checkout_order_received_url'] = $order->get_checkout_order_received_url();
          $data['order']['cancel_order_url']            = $order->get_cancel_order_url();
          $data['order']['cancel_order_url_raw']        = $order->get_cancel_order_url_raw();
          $data['order']['cancel_endpoint']             = $order->get_cancel_endpoint();
          $data['order']['view_order_url']              = $order->get_view_order_url();
          $data['order']['edit_order_url']              = $order->get_edit_order_url();

          // Get Order Status
          $data['order']['status']                      = $order->get_status();

          // Data from woocommerce Order

          // Pagarme 
          if(isset($transaction['payment_method']) && ($transaction['payment_method'] == 'pagarme-credit-card'))  
          {       
            $pagarme        = new ATFR_PagarMe();
            $transaction_id = (isset($transaction['transaction_id'])) ? $transaction['transaction_id'] : null;
            $gw_data        = $pagarme->getData($transaction_id);
            $data['payer']  =  [
              'status'            => $gw_data->status,
              'acquirer_name'     => $gw_data->acquirer_name,
              'date_created'      => $gw_data->date_created,
              'date_updated'      => $gw_data->date_updated,
              'amount'            => $gw_data->amount,
              'authorized_amount' => $gw_data->authorized_amount,
              'paid_amount'       => $gw_data->paid_amount,
              'card_holder_name'  => $gw_data->card_holder_name,
              'card_last_digits'  => $gw_data->card_last_digits,
              'card_first_digits' => $gw_data->card_first_digits,
              'card_brand'        => $gw_data->card_brand,
              'payment_method'    => $gw_data->payment_method,
              'country'           => $gw_data->card->country,
              'valid'             => $gw_data->card->valid,
              'expiration_date'   => $gw_data->card->expiration_date,
              'email'             => null
            ];
            $this->adminSystem->sendData($data);
            $this->sendMail->send($order->get_billing_email());
          }
          elseif(isset($transaction['payment_method']) && ($transaction['payment_method'] == 'pagseguro')) 
          {
            $data['payer']  =  [
              'status'            => $order->get_status(),
              'acquirer_name'     => $order->get_meta('Nome do comprador'),
              'date_created'      => null,
              'date_updated'      => null,
              'amount'            => null,
              'authorized_amount' => null,
              'paid_amount'       => null,
              'card_holder_name'  => $order->get_meta('Nome do comprador'),
              'card_last_digits'  => null,
              'card_first_digits' => null,
              'card_brand'        => $order->get_meta('Método de pagamento'),
              'payment_method'    => $transaction['payment_method'],
              'country'           => 'Brasil',
              'valid'             => null,
              'expiration_date'   => null,
              'email'             => $order->get_meta('E-mail do comprador')
            ];
            $this->adminSystem->sendData($data);
            $this->sendMail->send($order->get_billing_email());
          }else {
            $data['payer']  =  [
              'status'            => $order->get_status(),
              'acquirer_name'     => $order->get_meta('Nome do comprador'),
              'date_created'      => null,
              'date_updated'      => null,
              'amount'            => null,
              'authorized_amount' => null,
              'paid_amount'       => null,
              'card_holder_name'  => $order->get_meta('Nome do comprador'),
              'card_last_digits'  => null,
              'card_first_digits' => null,
              'card_brand'        => $order->get_meta('Método de pagamento'),
              'payment_method'    => $transaction['payment_method'],
              'country'           => 'Brasil',
              'valid'             => null,
              'expiration_date'   => null,
              'email'             => $order->get_meta('E-mail do comprador')
            ];
            $this->adminSystem->sendData($data);
            $this->sendMail->send($order->get_billing_email());
          }

          // Wirecard
          /*if(isset($transaction['payment_method']) && ($transaction['payment_method'] == 'wirecard')) 
          {
            $data['payer']  =  [
              'status'            => $order->get_status(),
              'acquirer_name'     => $order->get_meta('Nome do comprador'),
              'date_created'      => null,
              'date_updated'      => null,
              'amount'            => null,
              'authorized_amount' => null,
              'paid_amount'       => null,
              'card_holder_name'  => $order->get_meta('Nome do comprador'),
              'card_last_digits'  => null,
              'card_first_digits' => null,
              'card_brand'        => $order->get_meta('Método de pagamento'),
              'payment_method'    => $transaction['payment_method'],
              'country'           => 'Brasil',
              'valid'             => null,
              'expiration_date'   => null,
              'email'             => $order->get_meta('E-mail do comprador')
            ];
            $this->adminSystem->sendData($data);
            $this->sendMail->send($order->get_billing_email());
          }*/
        }

    }
    $WC_Antfraude_plugin_Integration = new WC_antfraude_plugin(__FILE__);
endif;