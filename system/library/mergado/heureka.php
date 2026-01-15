<?php

namespace mergado;

class Heureka {

    const HEUREKA_URL_CZ = 'https://www.heureka.cz/direct/dotaznik/objednavka.php';
    const HEUREKA_URL_SK = 'https://www.heureka.sk/direct/dotaznik/objednavka.php';

    private $registry;
    private $storeid = 0;
    private $default_store_currency;
    private $extension_fullname;
    private $extension_name;
    private $extension_path;
    private $extension_type;
    private $logger;

    function __construct($registry, $extension_fullname, $storeid, $default_store_currency, $extension_path, $extension_name, $extension_type) {
        $this->registry = $registry;
        $this->extension_fullname = $extension_fullname;
        $this->storeid = $storeid;
        $this->default_store_currency = $default_store_currency;
        $this->extension_name = $extension_name;
        $this->extension_path = $extension_path;
        $this->extension_type = $extension_type;

        //custom models
        $this->load->model('extension/' . $this->extension_path . $this->extension_name . '_logger');
        $model_logger = "model_extension_" . $this->extension_type . "_" . $this->extension_name . '_logger';
        $this->logger = $this->{$model_logger};

    }

    public function __get($name) {
		return $this->registry->get($name);
    }

    /************ Heureka.cz/.sk veryfied by customers **************/
    public function heurekaCustomerCzID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_cz_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function heurekaCustomerCzIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_cz_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function heurekaCustomerWidgetCzIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_cz_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function heurekaCustomerWidgetCzID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_cz_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function heurekaCustomerWidgetCzPosition() {
        $positions = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_cz_position');
        return !empty($positions) && isset($positions[$this->storeid]) ? $positions[$this->storeid] : 21;
    }

    public function heurekaCustomerWidgetCzTopMargin() {
        $margins = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_cz_top_margin');
        return !empty($margins) && isset($margins[$this->storeid]) ? $margins[$this->storeid] : 60;
    }

    public function heurekaCustomerWidgetCzIsActiveOnMobile() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_cz_mobile_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function heurekaCustomerWidgetCzMinScreenWidth() {
        $widths = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_cz_min_screen_width');
        return !empty($widths) && isset($widths[$this->storeid]) ? $widths[$this->storeid] : 0;
    }

    public function heurekaCustomerSkID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_sk_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function heurekaCustomerSkIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_sk_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function heurekaCustomerWidgetSkIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_sk_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function heurekaCustomerWidgetSkPosition() {
        $positions = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_sk_position');
        return !empty($positions) && isset($positions[$this->storeid]) ? $positions[$this->storeid] : 21;
    }

    public function heurekaCustomerWidgetSkID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_sk_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function heurekaCustomerWidgetSkTopMargin() {
        $margins = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_sk_top_margin');
        return !empty($margins) && isset($margins[$this->storeid]) ? $margins[$this->storeid] : 60;
    }

    public function heurekaCustomerWidgetSkIsActiveOnMobile() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_sk_mobile_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function heurekaCustomerWidgetSkMinScreenWidth() {
        $widths = $this->config->get($this->extension_fullname . '_adverts_heureka_customer_widget_sk_min_screen_width');
        return !empty($widths) && isset($widths[$this->storeid]) ? $widths[$this->storeid] : 0;
    }

    public function heurekaCustomerSuccessRequest($data) {

        if($this->heurekaCustomerCzIsActive() == 0 && $this->heurekaCustomerSkIsActive() == 0) { return; }

        $requestUrl = "";
        if(in_array(strtolower($this->language->get('code')), array('cs','cs_cz')) && $data['currency_code'] == 'CZK') { //CZ
            $ID = $this->heurekaCustomerCzID();
            $requestUrl = $this::HEUREKA_URL_CZ;
        } elseif (in_array(strtolower($this->language->get('code')), array('sk','sk_sk')) && $data['currency_code'] == 'EUR') { //SK
            $ID = $this->heurekaCustomerSKID();
            $requestUrl = $this::HEUREKA_URL_SK;
        } else {
            return;
        }

        $requestUrl .= "?id=" . $ID;
        $requestUrl .= "&email=" . urlencode($data['order_email']);
        $requestUrl .= "&orderId=" . urlencode($data['order_id']);
        if(!empty($data['products'])) {
            foreach ($data['products'] as $product) {

                if(empty($product['option'])) {
                    $requestUrl .= "&itemId[]=" . urlencode($product['product_id']);
                    $requestUrl .= "&produkt[]=" . urlencode(Helper::formatText($product['name']));
                } else {
                    $option_data = Helper::getOptionData($product['product_id'], $product['name'], $product['option']);
                    $requestUrl .= "&itemId[]=" . urlencode($option_data['id']);
                    $requestUrl .= "&produkt[]=" . urlencode(Helper::formatText($option_data['name']));
                }
            }
        }

        if(MERGADO_DEBUG_MODE) {
            $this->logger->log('heureka_conversion_request', array('request' => $requestUrl));
        }

        try {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $requestUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $output = curl_exec($ch);

          curl_close($ch);
        } catch (Exception $e) {
            //log
        }

        return $output;
    }

    public function heurekaCustomerHeaderSettings() {

        if(($this->heurekaCustomerWidgetCzIsActive() == 0 && $this->heurekaCustomerWidgetSkIsActive() == 0) || !in_array(strtolower($this->language->get('code')), array('cs','cs_cz','sk','sk_sk'))) { return; }
        if(in_array(strtolower($this->language->get('code')), array('cs','cs_cz'))) { //CZ
            $minScreenWidth = $this->heurekaCustomerWidgetCzMinScreenWidth();
            $onMobile = $this->heurekaCustomerWidgetCzIsActiveOnMobile();
        } elseif (in_array(strtolower($this->language->get('code')), array('sk','sk_sk'))) { //SK
            $minScreenWidth = $this->heurekaCustomerWidgetSkMinScreenWidth();
            $onMobile = $this->heurekaCustomerWidgetSkIsActiveOnMobile();
        }

        $output   = "";
        $output  .= "<!-- Heureka.cz/.sk : veryfied by customers widget -->";
        $output  .= "<script type='text/javascript'>";
        $output  .= "var mergado_heureka_widget_min_screen_width = " . intval($minScreenWidth) . ";";
        $output  .= "var mergado_heureka_show_on_mobile = " . intval($onMobile) . ";";
        $output  .= "</script>";
        $output  .= "<!-- ./Heureka.cz/.sk : veryfied by customers widget -->";
        return $output;
    }

    public function heurekaCustomerWidget() {
        
        if(($this->heurekaCustomerWidgetCzIsActive() == 0 && $this->heurekaCustomerWidgetSkIsActive() == 0) || !in_array(strtolower($this->language->get('code')), array('cs','cs_cz','sk','sk_sk'))) { return; }

        if(in_array(strtolower($this->language->get('code')), array('cs','cs_cz'))) { //CZ
            $widgetID = $this->heurekaCustomerWidgetCzID();
            $domain_suffix = "cz";
            $widgetPosition = $this->heurekaCustomerWidgetCzPosition();
            $topMargin = $this->heurekaCustomerWidgetCzTopMargin();
        } elseif (in_array(strtolower($this->language->get('code')), array('sk','sk_sk'))) { //SK
            $widgetID = $this->heurekaCustomerWidgetSkID();
            $domain_suffix = "sk";
            $widgetPosition = $this->heurekaCustomerWidgetSkPosition();
            $topMargin = $this->heurekaCustomerWidgetSkTopMargin();
        }

        $output  = "";
        $output  .= "<!-- Heureka.cz/.sk : veryfied by customers widget -->";
        $output  .= "<script type='text/javascript'>";
        $output  .= "var widgetID = '" . $widgetID . "';";
        $output  .=    "//<![CDATA[ \r\n";
        $output  .=    "var _hwq = _hwq || [];";
        $output  .=    "_hwq.push(['setKey', widgetID]);";
        $output  .=    "_hwq.push(['setTopPos', '" . $topMargin . "']);";
        $output  .=    "_hwq.push(['showWidget', '" . $widgetPosition . "']);";
        $output  .=    "(function () {";
        $output  .=        "var ho = document.createElement('script');";
        $output  .=        "ho.type = 'text/javascript';";
        $output  .=        "ho.async = true;";
        $output  .=        "ho.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.heureka." . $domain_suffix . "/direct/i/gjs.php?n=wdgt&sak=' + widgetID;";
        $output  .=        "var s = document.getElementsByTagName('script')[0];";
        $output  .=        "s.parentNode.insertBefore(ho, s);";
        $output  .=    "})();";
        $output  .=    "\r\n//]]>";
        $output  .= "</script>";
        $output  .= "<!-- ./Heureka.cz/.sk : veryfied by customers widget -->";

        return $output;
    }
    /************ ./Heureka.cz/.sk veryfied by customers **************/

    /************ Heureka.cz/.sk conversion Tracking **************/
    public function heurekaConversionCzID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_heureka_conversion_cz_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function heurekaConversionCzIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_heureka_conversion_cz_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function heurekaConversionSkID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_heureka_conversion_sk_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function heurekaConversionSkIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_heureka_conversion_sk_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function heurekaConversionSuccessCodeSnippet($data) {

        if($this->heurekaConversionCzIsActive() == 0 && $this->heurekaConversionSkIsActive() == 0) { return; }

        if(in_array(strtolower($this->language->get('code')), array('cs','cs_cz')) && $data['currency_code'] == 'CZK') { //CZ
            $conversionID = $this->heurekaConversionCzID();
            $domain = "cz";
        } elseif (in_array(strtolower($this->language->get('code')), array('sk','sk_sk')) && $data['currency_code'] == 'EUR') { //SK
            $conversionID = $this->heurekaConversionSkID();
            $domain = "sk";
        } else {
            return;
        }

        $products = "";
        if(!empty($data['products'])) {
            foreach ($data['products'] as $product) {

                if(empty($product['option'])) {
                    $product_name = Helper::formatText($product['name']);
                    $price = $product['price'];
                } else {
                    $option_data = Helper::getOptionData($product['product_id'], $product['name'], $product['option']);
                    $product_name = Helper::formatText($option_data['name']);
                    $price = $product['price'];
                }

                $price = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($price, $this->default_store_currency, $data['currency_code']) : $price;

                $products .= "_hrq.push(['addProduct',";
                $products .= "'" . $product_name . "',";
                $products .= "'". $price ."',";
                $products .= "'". $product['quantity']. "'";
                $products .= "]);";

            }
        }

        $output  = "";
        $output .= "<!-- Heureka.cz/.sk : conversion tracking -->";
        $output .= "<script type=\"text/javascript\">";
        $output .= "var _hrq = _hrq || [];";
        $output .= "_hrq.push(['setKey', '" . $conversionID . "']);";
        $output .= "_hrq.push(['setOrderId', '" . $data['order_id'] . "']);";
        $output .= $products;
        $output .= "_hrq.push(['trackOrder']);";

        $output .= "(function() {";
        $output .=    "var ho = document.createElement('script'); ho.type = 'text/javascript'; ho.async = true;";
        if($domain == 'cz') {
            $output .=    "ho.src = 'https://im9.cz/js/ext/1-roi-async.js';";
        } else {
            $output .=    "ho.src = 'https://im9.cz/sk/js/ext/2-roi-async.js';";
        }
        $output .=    "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ho, s);";
        $output .= "})();";
        $output .= "</script>";
        $output .= "<!-- ./Heureka.cz/.sk : conversion tracking -->";

        return $output;
    }
    /************ ./Heureka.cz/.sk conversion Tracking **************/


}
