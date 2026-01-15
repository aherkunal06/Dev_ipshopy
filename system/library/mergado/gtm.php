<?php

namespace mergado;

class Gtm { //Google Tag Manager

    private $registry;
    private $storeid = 0;
    private $default_store_currency;
    private $extension_fullname;
    private $extension_name;
    private $extension_path;
    private $extension_type;

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

    /************ Google Tag Manager **************/
    public function gtmID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_gtm_id');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function gtmIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_gtm_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }

    public function gtmHeaderCodeSnippet() {
        if($this->gtmIsActive() == 0) { return; }

        $output = "";
        $output .= "<!-- Google Tag Manager -->";
        $output .= "<script>";
        $output .= "window.dataLayer = window.dataLayer || [];";
        $output .= "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':";
        $output .= "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],";
        $output .= "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=";
        $output .= "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);";
        $output .= "})(window,document,'script','dataLayer','" . $this->gtmID() . "');</script>";
        $output .= "<!-- End Google Tag Manager -->";

        return $output;
    }

    public function gtmBodyCodeSnippet() {
        if($this->gtmIsActive() == 0) { return; }

        $output  = "";
        $output .= "<!-- Google Tag Manager (noscript) -->";
        $output .= "<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=" . $this->gtmID() . "\"";
        $output .= "height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>";
        $output .= "<!-- End Google Tag Manager (noscript) -->";

        return $output;
    }

    public function gtmSuccessCodeSnippet($data) {

        if($this->gtmIsActive() == 0) { return; }

        $this->load->model('catalog/product');

        $products = array();
        if(!empty($data['products'])) {
            foreach ($data['products'] as $product) {

                $product_categories = $this->model_catalog_product->getCategories($product['product_id']);

                if(empty($product['option'])) {
                    $product_id = $product['product_id'];
                    $product_name = Helper::formatText($product['name']);
                    $price = $product['price'];
                } else {
                    $option_data = Helper::getOptionData($product['product_id'], $product['name'], $product['option']);
                    $product_id = $option_data['id'];
                    $product_name = Helper::formatText($option_data['name']);
                    $price = $product['price'];
                }

                $price = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($price, $this->default_store_currency, $data['currency_code']) : $price;

                $products[] = "{
                    'name': '" . $product_name . "',
                    'id': '" . $product_id . "',
                    'price': '" . $price . "',
                    'category': '" . (!empty($product_categories) ? $this->getFullCategory($product_categories[0]['category_id'], " > ") : '') . "',
                    'quantity': ".$product['quantity'].",
                    'currency' : '".$data['currency_code']."'
                  }";

//                                $products[] = "{
//                    'item_name': '" . $product_name . "',
//                    'item_id': '" . $product_id . "',
//                    'item_price': '" . $price . "',
//                    'item_category': '" . (!empty($product_categories) ? $this->getFullCategory($product_categories[0]['category_id'], " > ") : '') . "',
//                    'quantity': ".$product['quantity']."
//                  }";
            }
        }

        $totals = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($data['sub_totals'], $this->default_store_currency, $data['currency_code']) : $data['sub_totals'];

        $output  = "";
        $output .= "<!-- Google Tag Manager -->";
        $output .= "<script>";
        $output .= "dataLayer.push({";
        $output .=      "'event' : 'purchase',";
        $output .=      "'ecommerce': {";
        $output .=          "'currencyCode' : '".$data['currency_code']."',";
        $output .=          "'purchase': {";
        $output .=              "'actionField': {";
        $output .=                  "'id': '".$data['order_id']."',";
        $output .=                  "'affiliation': '".$data['store_name']."',";
        $output .=                  "'revenue': '". $totals ."',";
        $output .=                  "'tax':'',";
        $output .=                  "'shipping': '',";
        $output .=              "},";
        $output .=              "'products': [".implode(",",$products)."]";
        $output .=          "}";
        $output .=      "}";
        $output .= "});";
        $output .= "</script>";
        $output .= "<!-- ./Google Tag Manager -->";

//        $output  = "";
//        $output .= "<!-- Google Tag Manager -->";
//        $output .= "<script>";
//        $output .= "dataLayer.push({";
//        $output .=      "'event' : 'purchase',";
//        $output .=      "'ecommerce': {";
//        $output .=          "'purchase': {";
//        $output .=              "'transaction_id': '".$data['order_id']."',";
//        $output .=              "'affiliation': '".$data['store_name']."',";
//        $output .=              "'valuw': '". $totals ."',";
//        $output .=              "'tax':'',";
//        $output .=              "'shipping': '',";
//        $output .=              "'currency' : '".$data['currency_code']."',";
//        $output .=              "'items': [".implode(",",$products)."]";
//        $output .=          "}";
//        $output .=      "}";
//        $output .= "});";
//        $output .= "</script>";
        $output .= "<!-- ./Google Tag Manager -->";

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
    /************ ./Google Tag Manager **************/

}
