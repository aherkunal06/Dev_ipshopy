<?php

namespace mergado;

class Glami {

    private $registry;
    private $storeid = 0;
    private $default_store_currency;
    private $extension_fullname;
    private $trackers;
    private $extension_name;
    private $extension_path;
    private $extension_type;

    public static $pixel_supported_countries = array(
        'CZ' => array('url' => 'glami.cz', 'currency' => 'CZK'),
        'DE' => array('url' => 'glami.de', 'currency' => 'EUR'),
        'FR' => array('url' => 'glami.fr', 'currency' => 'EUR'),
        'SK' => array('url' => 'glami.sk', 'currency' => 'EUR'),
        'RO' => array('url' => 'glami.ro', 'currency' => 'RON'),
        'HU' => array('url' => 'glami.hu', 'currency' => 'HUF'),
        'RU' => array('url' => 'glami.ru', 'currency' => 'RUB'),
        'GR' => array('url' => 'glami.gr', 'currency' => 'EUR'),
        'TR' => array('url' => 'glami.com.tr', 'currency' => 'TRY'),
        'BG' => array('url' => 'glami.bg', 'currency' => 'BGN'),
        'HR' => array('url' => 'glami.hr', 'currency' => 'HRK'),
        'SI' => array('url' => 'glami.si', 'currency' => 'EUR'),
        'ES' => array('url' => 'glami.es', 'currency' => 'EUR'),
        'BR' => array('url' => 'glami.com.br', 'currency' => 'BRL'),
        'ECO' => array('url' => 'glami.eco', 'currency' => 'EUR'),
    );

    function __construct($registry, $extension_fullname, $storeid, $default_store_currency, $extension_path, $extension_name, $extension_type) {
        $this->registry = $registry;
        $this->extension_fullname = $extension_fullname;
        $this->storeid = $storeid;
        $this->default_store_currency = $default_store_currency;
        $this->extension_name = $extension_name;
        $this->extension_path = $extension_path;
        $this->extension_type = $extension_type;
    }

    public function __get($name) {
		return $this->registry->get($name);
    }

    /*** GLAMI PIXEL ***/

    public function setPixels() {
        $this->trackers = array();

        $statuses = $this->config->get($this->extension_fullname . '_adverts_glami_pixel_id_status');
        $ids = $this->config->get($this->extension_fullname . '_adverts_glami_pixel_id');

        if(isset($statuses[$this->storeid]) && isset($ids[$this->storeid])) {
            foreach($statuses[$this->storeid] as $key => $value) {
                if(in_array($value, array("on", "1")) && isset($ids[$this->storeid][$key])) {
                    $this->trackers[$key] = $ids[$this->storeid][$key]; 
                }
            }
        }

    }

    public function pixelIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_glami_pixel_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function pixelHeaderCodeSnippet() {

        if($this->pixelIsActive() == 0) { return; }

        $this->setPixels();

        if(empty($this->trackers)) { return; }

        $output  = "";
        $output .= "<!-- Glami piXel-->";
        $output .= "<script>";
        $output .= "(function(f, a, s, h, i, o, n) {f['GlamiTrackerObject'] = i;";
        $output .= "f[i]=f[i]||function(){(f[i].q=f[i].q||[]).push(arguments)};o=a.createElement(s),";
        $output .= "n=a.getElementsByTagName(s)[0];o.async=1;o.src=h;n.parentNode.insertBefore(o,n)";
        $output .= "})(window, document, 'script', '//www.glami.cz/js/compiled/pt.js', 'glami');";

        $itterator = 0;
        foreach($this->trackers as $country_code => $pixel) {

            if($itterator == 0) {
                $output .= "glami('create', '" . $pixel . "', '" . $country_code . "');";
                $output .= "glami('track', 'PageView');";
            } else {
                $output .= "glami('create', '" . $pixel . "', '" . $country_code . "', '" . $country_code . "tracker');";
                $output .= "glami('" . $country_code . "tracker.track', 'PageView');";
            }

            $itterator++;
        }
        $output .= "</script>";
        $output .= "<!-- ./Glami piXel -->";

        return $output;
    }

    public function pixelSuccessCodeSnippet($data = array()) {

        if($this->pixelIsActive() == 0) { return; }

        if(empty($this->trackers)) { return; }

        $item_names = array();
        $item_ids = array();
        if(!empty($data['products'])) {
            foreach ($data['products'] as $product) {

                if(empty($product['option'])) {
                    $item_ids[] = $product['product_id'];
                    $item_names[] = Helper::formatText($product['name']);
                } else {
                    $option_data = Helper::getOptionData($product['product_id'], $product['name'], $product['option']);
                    $item_ids[] = $option_data['id'];
                    $item_names[] = Helper::formatText($option_data['name']);
                }
            }
        }

        if(empty($item_names) || empty($item_ids)) { return; }

        $output  = "";
        $output .= "<!-- Glami piXel-->";
        $output .= "<script>";

        $itterator = 0;
        foreach($this->trackers as $country_code => $pixel) {

            if($itterator == 0) {

                $totals = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($data['sub_totals'], $this->default_store_currency, $data['currency_code']) : $data['sub_totals'];

                $output .= "glami('track', 'Purchase', {";
                $output .= "item_ids: [" . Helper::implodeWithQuotes($item_ids, ',') . "],";
                $output .= "product_names: [" . Helper::implodeWithQuotes($item_names, ',') . "],";
                $output .= "value: " . $totals . ",";
                $output .= "currency: '" . $data['currency_code'] . "',";
                $output .= "transaction_id: '" . $data['order_id'] . "' ";
                $output .= "});";
            } else {

                $sub_totals = $this->convert_currency($data['sub_totals'], $data['currency_code'], self::$pixel_supported_countries[strtoupper($country_code)]['currency']);

                $output .= "glami('" . $country_code . "tracker.track', 'Purchase', {";
                $output .= "item_ids: [" . Helper::implodeWithQuotes($item_ids, ',') . "],";
                $output .= "product_names: [" . Helper::implodeWithQuotes($item_names, ',') . "],";
                $output .= "value: " . $sub_totals . ",";
                $output .= "currency: '" . self::$pixel_supported_countries[strtoupper($country_code)]['currency'] . "',";
                $output .= "transaction_id: '" . $data['order_id'] . "' ";
                $output .= "});";
            }

            $itterator++;
        }
        
        $output .= "</script>";
        $output .= "<!-- ./Glami piXel -->";

        return $output;
    }

    public function pixelAddToCartCodeSnippet($data) {

        if($this->pixelIsActive() == 0) { return; }

        if(empty($this->trackers)) { return; }

        $output  = "";
        $output .= "<!-- Glami piXel -->";
        $output .= "<script>";

        $itterator = 0;
        foreach($this->trackers as $country_code => $pixel) {

            if($itterator == 0) {

                $price = $data['currency'] != $this->default_store_currency ? $this->currency->convert($data['price'], $this->default_store_currency, $data['currency']) : $data['price'];

                $output .= "glami('track', 'AddToCart', {";
                $output .= "item_ids: ['" . $data['product_id'] . "'],";
                $output .= "product_names: ['" . $data['name'] . "'],";
                $output .= "value: " . $price . ",";
                $output .= "currency: '" . $data['currency'] . "'";
                $output .= "});"; 

            } else {

                $price = $this->convert_currency($data['price'], $data['currency'], self::$pixel_supported_countries[strtoupper($country_code)]['currency']);

                $output .= "glami('" . $country_code . "tracker.track', 'AddToCart', {";
                $output .= "item_ids: ['" . $data['product_id'] . "'],";
                $output .= "product_names: ['" . $data['name'] . "'],";
                $output .= "value: " . $price . ",";
                $output .= "currency: '" . self::$pixel_supported_countries[strtoupper($country_code)]['currency'] . "'";
                $output .= "});";    
            }

            $itterator++;
        }

        $output .= "</script>";
        $output .= "<!-- ./Glami piXel -->";

        return $output;
    }

    private function convert_currency($price, $default_currency, $final_currency) {

        $computed_price = $this->currency->convert($price, $default_currency, $final_currency);
        $decimal_places = $this->currency->getDecimalPlace($final_currency);
        if(!is_numeric($decimal_places)) { $decimal_places = 2; }
  
        return number_format((float)$computed_price, (int)$decimal_places, '.', '');
    }

    /** GLAMI TOP REVIEWS ***/

    public function reviewsMerchantID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_glami_reviews_merchant_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function reviewsCountry() {
        $countries = $this->config->get($this->extension_fullname . '_adverts_glami_reviews_country');
        return !empty($countries) && isset($countries[$this->storeid]) && $countries[$this->storeid]!= "0" ? $countries[$this->storeid] : '';
    }

    public function reviewsIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_glami_reviews_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function reviewsSuccessCodeSnippet($data = array()) {
        if($this->reviewsIsActive() == 0) { return; }

        $item_names = array();
        $item_ids = array();
        if(!empty($data['products'])) {
            foreach ($data['products'] as $product) {

                if(empty($product['option'])) {
                    $item_ids[] = $product['product_id'];
                    $item_names[] = Helper::formatText($product['name']);
                } else {
                    $option_data = Helper::getOptionData($product['product_id'], $product['name'], $product['option']);
                    $item_ids[] = $option_data['id'];
                    $item_names[] = Helper::formatText($option_data['name']);
                }
            }
        }

        if(empty($item_names) || empty($item_ids) || $this->reviewsCountry() == '') { return; }

        $output  = "";
        $output .= "<!-- Glami Reviews -->";
        $output .= "<script>";
        $output .= "(function (f, a, s, h, i, o, n) {";
        $output .= "f['GlamiOrderReview'] = i;";
        $output .= "f[i] = f[i] || function () {(f[i].q = f[i].q || []).push(arguments);};";
        $output .= "o = a.createElement(s), n = a.getElementsByTagName(s)[0];";
        $output .= "o.async = 1; o.src = h; n.parentNode.insertBefore(o, n);";
        $output .= "})(window,document,'script','//www.glami.cz/js/compiled/or.js', 'glami_or');";

        $output .= "glami_or('addParameter', 'merchant_id','" . $this->reviewsMerchantID() . "', '" . $this->reviewsCountry() . "');";
        $output .= "glami_or('addParameter', 'order_id', '" . $data['order_id'] . "');";
        $output .= "glami_or('addParameter', 'email', '" . $data['order_email'] . "');";
        $output .= "glami_or('addParameter', 'language', '" . $this->language->get('code') . "');";
        $output .= "glami_or('addParameter', 'item_ids', '" . implode(";", $item_ids) . "');";
        $output .= "glami_or('addParameter', 'item_names', '" . implode(";", $item_names) . "');";

        $output .= "glami_or('create');";
        $output .= "</script>";
        $output .= "<!-- ./Glami Reviews -->";

        return $output;
    }


}
