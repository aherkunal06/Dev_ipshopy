<?php

namespace mergado;

class Seznam {

    //docs -> https://github.com/seznam/zbozi-konverze

    const ZBOZI_PROD_BACKEND_URL = 'https://www.zbozi.cz/action/SHOP_ID/conversion/backend';
    const ZBOZI_DEV_BACKEND_URL = 'https://sandbox.zbozi.cz/action/TEST_ID/conversion/backend';

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

    /************ Sklik conversion tracking **************/
    public function sklikConversionID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_sklik_conversion_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function sklikConversionValue() {
        $values = $this->config->get($this->extension_fullname . '_adverts_sklik_conversion_value');
        return !empty($values) && isset($values[$this->storeid]) ? $values[$this->storeid] : '';
    }

    public function sklikConversionIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_sklik_conversion_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function sklikConversionDPHIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_sklik_dph');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function sklikConversionSuccessCodeSnippet($data) {

        if($this->sklikConversionIsActive() == 0 && $data['currency_code'] != 'CZK') { return; }

        if($this->sklikConversionDPHIsActive()) {
            $price = $data['totals'];
        } else {
            $price = $data['sub_totals'];
        }
        $totals = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($price, $this->default_store_currency, $data['currency_code']) : $price;

        $output   = "";
        $output  .= "<!-- Sklik conversion tracking -->";
        $output  .= "<script type='text/javascript'>";
        $output  .=     "var seznam_cId = " . $this->sklikConversionID() . ";";
        $output  .=     "var seznam_value = " . ( $this->sklikConversionValue() == '' ? $totals : $this->sklikConversionValue() ) . ";";
        $output  .= "</script>";
        if(!$this->zboziConversionIsActive()) {
            $output .= "<script type=\"text/javascript\" src=\"https://www.seznam.cz/rs/static/rc.js\" async></script>";
        }
        $output  .= "<!-- ./Sklik conversion tracking -->";
        return $output;
    }
    /************ ./Sklik conversion tracking **************/

    /************ Sklik retargeting **************/
    public function sklikRetargetingID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_sklik_retargeting_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function sklikRetargetingIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_sklik_retargeting_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function sklikFooterCodeSnippet() {

        if($this->sklikRetargetingIsActive() == 0) { return; }

        $output   = "";
        $output  .= "<!-- Sklik retargeting-->";
        $output  .= "<script type='text/javascript'>";
        $output  .= "/* <![CDATA[ */\r\n";
        $output  .= "var seznam_retargetingId = " . $this->sklikRetargetingID() . ";\r\n";
        $output  .= "/* ]]> */";
        $output  .= "</script>";
        $output  .= "<script type=\"text/javascript\" src=\"//c.imedia.cz/js/retargeting.js\"></script>";
        $output  .= "<!-- ./Sklik retargeting -->";
        return $output;
    }

    public function sklikProductCodeSnippet($data) {

        if($this->sklikRetargetingIsActive() == 0) { return; }

        $output   = "";
        $output  .= "<!-- Sklik retargeting - product -->";
        $output  .= "<script type='text/javascript'>";
        $output  .= "/* <![CDATA[ */\r\n";
        $output  .=  "var seznam_itemId = \"" . $data['product_id'] ."\";\r\n";
        $output  .=  "var seznam_pagetype = \"offerdetail\";\r\n";
        $output  .= "/* ]]> */";
        $output  .= "</script>";
        $output  .= "<!-- ./Sklik retargeting - product -->";
        return $output;

    }

    public function sklikCategoryCodeSnippet($data) {

        if($this->sklikRetargetingIsActive() == 0) { return; }

        $this->load->model('catalog/category');
        $category = $this->model_catalog_category->getCategory($data['category_id']);

        $output   = "";
        $output  .= "<!-- Sklik retargeting - category -->";
        $output  .= "<script type='text/javascript'>";
        $output  .= "/* <![CDATA[ */\r\n";
        $output  .=  "var seznam_category = \"" . $this->getFullCategory($data['category_id'], " | ") . "\";\r\n";
        $output  .=  "var seznam_pagetype = \"category\";\r\n";
        $output  .= "/* ]]> */";
        $output  .= "</script>";
        $output  .= "<!-- ./Sklik retargeting - category -->";
        return $output;
    }

    private function getFullCategory($category_id, $delimiter = " / ") {
        $categories = array();

        $this->load->model('catalog/category');


        $category_data = $this->model_catalog_category->getCategory($category_id);

        if(empty($category_data)) { return ""; }

        $categories[] = $category_data['name'];

        while( isset($category_data['parent_id']) && $category_data['parent_id'] != 0 ){
            $category_data = $this->model_catalog_category->getCategory($category_data['parent_id']);
            if(isset($category_data['name'])) {
                $categories[] = $category_data['name'];
            }
        }

        return implode($delimiter, array_reverse($categories));
    }
    /************ ./Sklik retargeting **************/

    /************ Zbozi conversion Tracking **************/
    public function zboziConversionShopID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_zbozi_shop_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function zboziConversionSecretKey() {
        $keys = $this->config->get($this->extension_fullname . '_adverts_zbozi_secret_key');
        return !empty($keys) && isset($keys[$this->storeid]) ? $keys[$this->storeid] : 'undefined';
    }

    public function zboziConversionIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_zbozi_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function zboziConversionIsStandard() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_zbozi_standard');
        return (!empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1"))) || empty($statuses) ? 1 : 0;
    }

    public function zboziDebugModeIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_zbozi_debug');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function zboziDPHIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_zbozi_dph');
        return (!empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1"))) || empty($statuses) ? 1 : 0;
    }

    public function zboziConversionSuccessRequest($data)
    {

        if (($this->zboziConversionIsActive() == 0 || $this->zboziConversionIsStandard() == 0) && $data['currency_code'] != 'CZK') { return; }

        $requestUrl = "";
        if($this->zboziDebugModeIsActive()) {
            $requestUrl = str_replace('TEST_ID', $this->zboziConversionShopID(),self::ZBOZI_DEV_BACKEND_URL);
        } else {
            $requestUrl = str_replace('SHOP_ID', $this->zboziConversionShopID(), self::ZBOZI_PROD_BACKEND_URL);
        }

        $requestUrl .= "?PRIVATE_KEY=" . $this->zboziConversionSecretKey();
        $requestUrl .= "&email=" . urlencode($data['order_email']);
        $requestUrl .= "&orderId=" . urlencode($data['order_id']);
        $requestUrl .= "&paymentType=" . urlencode(Helper::formatText(($data['payment_method'])));
        if(isset($this->session->data['shipping_method'])) {
            $shipping_price = $this->session->data['shipping_method']['cost'];
            $shipping_tax_class_id = $this->session->data['shipping_method']['tax_class_id'];
            if($this->zboziDPHIsActive()) {
                $shipping_price = number_format((float)$this->tax->calculate($shipping_price, $shipping_tax_class_id), 2, '.', '');
            }
            $shipping_price = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($shipping_price, $this->default_store_currency, $data['currency_code']) : $shipping_price;
            $requestUrl .= "&deliveryPrice=" . $shipping_price;
        }else{
            $requestUrl .= "&deliveryPrice=0.0";
        }
        $requestUrl .= "&otherCosts=0.0";
        if(!empty($data['products'])) {
            foreach ($data['products'] as $product) {

                if(empty($product['option'])) {
                    if($this->zboziDPHIsActive()) {
                        $price = number_format((float)$this->tax->calculate($product['price'], $product['tax_class_id']), 2, '.', '');
                    } else {
                        $price = $product['price'];
                    }
                    $price = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($price, $this->default_store_currency, $data['currency_code']) : $price;

                    $requestUrl .= "&cart=itemId:" . urlencode($product['product_id']) . ';';
                    $requestUrl .= "quantity:" . $product['quantity'] . ';';
                    $requestUrl .= "unitPrice:" . $price . ';';
                    $requestUrl .= "productName:" . urlencode(Helper::formatText($product['name'])) . ';';
                } else {
                    $option_data = Helper::getOptionData($product['product_id'], $product['name'], $product['option']);
                    if($this->zboziDPHIsActive()) {
                        $price = number_format((float)$this->tax->calculate($product['price'], $product['tax_class_id']), 2, '.', '');
                    } else {
                        $price = $product['price'];
                    }
                    $price = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($price, $this->default_store_currency, $data['currency_code']) : $price;

                    $requestUrl .= "&cart=itemId:" . urlencode($option_data['id']) . ';';
                    $requestUrl .= "quantity:" . $product['quantity'] . ';';
                    $requestUrl .= "unitPrice:" . $price . ';';
                    $requestUrl .= "productName:" . urlencode(Helper::formatText($option_data['name'])) . ';';
                }
            }
        }

        if(MERGADO_DEBUG_MODE) {
            $this->logger->log('zbozi_conversion_request', array('request' => $requestUrl));
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

    public function zboziConversionSuccessCodeSnippet($data) {

        if($this->zboziConversionIsActive() == 0 && $data['currency_code'] != 'CZK') { return; }

        $output  = "";
        $output  .= "<!-- Zbozi.cz : conversion tracking -->";
        $output  .= "<script type='text/javascript'>";
        $output  .=     "var seznam_zboziId = " . $this->zboziConversionShopID() . ";";
        $output  .=     "var seznam_orderId = \"" . urlencode($data['order_id']). "\";";
        if($this->zboziDebugModeIsActive()) {
            // zapnutí testovacího režimu
            $output .= "var seznam_zboziType = \"sandbox\";";
        }
        $output  .= "</script>";
        $output  .= "<script type=\"text/javascript\" src=\"https://www.seznam.cz/rs/static/rc.js\" async></script>";
        $output  .= "<!-- ./Zbozi.cz : conversion tracking -->";


        return $output;
    }
    /************ ./Zbozi.cz conversion Tracking **************/

}
