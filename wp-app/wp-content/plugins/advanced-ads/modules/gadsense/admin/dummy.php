<?php
if ( ! defined( 'WPINC' ) ) {
    die();
}
require_once ADVADS_ABSPATH . '/admin/includes/class-ad-network.php';

class Dummy_Type extends Advanced_Ads_Ad_Type_Abstract{
    public $ID = "dummy2000";
    public function __construct(){
//        $this->ID = "dummy2000";
        $this->title = "ASDF Dummy";
        $this->description = "Some dummy description";

        $this->parameters = [
            'content' => ''
        ];
    }
    public function render_parameters($ad){
        $network = Dummy_Network::get_instance();
        global $dummy_content;
        $dummy_content = (string) ( isset( $ad->content ) ? $ad->content : '' );

        include(ADVADS_ABSPATH . '/modules/gadsense/admin/views/external-ads-links.php');
        include(ADVADS_ABSPATH . '/modules/gadsense/admin/views/external-ads-list.php');
        include(ADVADS_ABSPATH . '/modules/gadsense/admin/views/external-ads-dummy.php');
    }

    public function sanitize_content($content = '') {
        return $content = wp_unslash( $content );
    }

    public function prepare_output($ad) {
        $output = '<div style="background-color:#ff00ff;"><h1>' . $ad->content . '</h1>';
        $output.= "<pre><code>" . json_encode($ad, JSON_PRETTY_PRINT) . "</code></pre>";
        $output.= "</div>";
        return $output;
    }

    protected function append_defaut_responsive_content(&$output, $pub_id, $content) {
        $output .= '<div style="background-color:#00ffff;"><h2>Responsive content</h2>';
    }

}

class Dummy_Network extends Advanced_Ads_Ad_Network {
    private static $instance;
    public static function get_instance(){
        if (! self::$instance) self::$instance = new Dummy_Network();
        return self::$instance;
    }
    public function __construct()
    {
        parent::__construct('dummy2000', 'Testing Dummy Ads');
    }

    protected function register_settings($hook, $section_id)
    {
        // add setting field to disable ads
        add_settings_field(
            'adsense-id',
            __( 'Dummy Account Id', 'advanced-ads' ),
            //array($this, 'render_dummy_account_id'),
            function(){
                echo "Connected: " . (($this->is_account_connected()) ? "Y" : "N");
            },
            $this->settings_page_hook,
            $this->settings_section_id
        );
    }

    protected function sanitize_settings($options)
    {
        return $options;
    }


    public function sanitize_ad_settings($ad_settings_post)
    {
        return $ad_settings_post;
    }

    public function get_ad_type()
    {
        return new Dummy_Type();
    }

    public function get_external_ad_units()
    {
        $units = [];
        for ($i=0; $i<42; $i++){
            $ad_unit = new Advanced_Ads_Ad_Network_Ad_Unit("RAW DATA");
            $ad_unit->id = "Dummy" . $i;
            $ad_unit->slot_id = "Dummy Slot #" . $i;
            $ad_unit->name = "Dummy #" . $i;
            $ad_unit->active = $i % 2 == 0;
            $ad_unit->code = "Some Code";
            $ad_unit->display_type = "Some Type";
            $ad_unit->display_size = "Some Size";
            $units[] = $ad_unit;
        }
        return $units;
    }

    public function is_supported($ad_unit)
    {
        // TODO: Implement is_supported() method.
        return true;
    }

    public function update_external_ad_units()
    {
        //TODO: user cap & nonce check!
        //TODO: copy from class-mapi.php

        $network = $this; //required in templates
        $ad_units = $this->get_external_ad_units();
        $unsupported_ad_type_link = Advanced_Ads_AdSense_MAPI::UNSUPPORTED_TYPE_LINK;

        ob_start();
        require_once GADSENSE_BASE_PATH . 'admin/views/external-ads-list.php';
        require_once GADSENSE_BASE_PATH . 'admin/views/external-ads-dummy.php';
        $html_ad_selector = ob_get_clean();

        $response = [
            'status' => true,
            'html'   => $html_ad_selector,
        ];
        header( 'Content-Type: application/json' );
        echo wp_json_encode( $response );
        die();
    }

    public function is_account_connected()
    {
        return true;
    }


    public function get_javascript_base_path()
    {
        // TODO: Implement get_javascript_base_handle() method.
    }

    public function append_javascript_data(&$data)
    {
        // TODO: Implement append_javascript_data() method.
    }
}

//$network = new Dummy_Network();
//$network->register();
//
//$network = new Advanced_Ads_Network_Adsense();
//$network->register();
