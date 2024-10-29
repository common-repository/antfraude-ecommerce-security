<?php
/**
 * Antfraude Integration
 * 
 * @package Woocommerc Antfraude Plugin Integration
 * @category Integration
 * @author AntFraude - Ecommerce Security <suporte@antfraude.com>
 */
if(!class_exists('WC_Antfraude_plugin_Integration')):
    class WC_Antfraude_plugin_Integration extends WC_Integration
    {
        /**
         * Init and hook in the integration
         */
        public function __construct()
        {
            global $woocommerce;
            $this->id                 = 'antfraude-plugin';
            $this->method_title       = __('Plugin Antfraude');
            $this->method_description = __('Plugin Antfraude para analise de dados de compra');
            // Load Settings
            $this->init_form_fields();
            $this->init_settings();
            // Define User set variables
            $this->antfraude_access_key = $this->get_option( 'antfraude_access_key' );
            // Actions
            add_action('woocommerce_update_options_integration_' . $this->id, [$this, 'process_admin_options']);
        }
        /**
         * Initialize integration settings form fileds
         */
        public function init_form_fields()
        {
            $this->form_fields = [
                'antfraude_access_key' => [
                    'title'       => __('Key'),
                    'type'        => 'text',
                    'description' => __('Infome a chave de indentificação'),
                    'desc_tip'    => true,
                    'default'     => '',
                    'css'         => 'width:300px;'
                ]
            ];
        }
    }
endif;