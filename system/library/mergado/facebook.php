<?php

namespace mergado;

class Facebook {

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

    public function pixelID() {
        $pixel_ids = $this->config->get($this->extension_fullname . '_adverts_facebook_pixel_id');
        return !empty($pixel_ids) && isset($pixel_ids[$this->storeid])  ? $pixel_ids[$this->storeid] : 'undefined';
    }

    public function pixelIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_facebook_pixel_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1")) ? 1 : 0;
    }
    
    public function pixelHeaderCodeSnippet() {
        if($this->pixelIsActive() == 0) { return; }

        $output  = "";
        $output .= "<!-- FB Pixel -->";
        $output .= "<script>";
        $output .= "!function(f,b,e,v,n,t,s){";
        $output .= "if(f.fbq)return;n=f.fbq=function(){n.callMethod?";
        $output .= "n.callMethod.apply(n,arguments):n.queue.push(arguments)};";
        $output .= "if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';";
        $output .= "n.queue=[];t=b.createElement(e);t.async=!0;";
        $output .= "t.src=v;s=b.getElementsByTagName(e)[0];";
        $output .= "s.parentNode.insertBefore(t,s)}(window, document,'script',";
        $output .= "'https://connect.facebook.net/en_US/fbevents.js');";
        $output .= "fbq('init', '" . $this->pixelID() . "');";
        $output .= "fbq('track', 'PageView');";
        $output .= "</script>";
        $output .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none\"";
        $output .= "src=\"https://www.facebook.com/tr?id=" . $this->pixelID() . "&ev=PageView&noscript=1\" /></noscript>";
        $output .= "<!-- ./FB Pixel -->";

        return $output;
    }

    public function pixelSuccessCodeSnippet($data) {

        if($this->pixelIsActive() == 0) { return; }

        $products = array();
        if(!empty($data['products'])) {
            foreach ($data['products'] as $product) {

                if(empty($product['option'])) {
                    $product_id = $product['product_id'];
                    $product_name = $product['name'];
                    $price = $product['price'];
                } else {
                    $option_data = Helper::getOptionData($product['product_id'], $product['name'], $product['option']);
                    $product_id = $option_data['id'];
                    $product_name = $option_data['name'];
                    $price = $product['price'];
                }

                $price = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($price, $this->default_store_currency, $data['currency_code']) : $price;
                
                $products[] = "{ 
                    'id': '" . $product_id . "',
                    'name': '" . Helper::formatText($product_name) . "',
                    'quantity': " . $product['quantity'] . ",
                    'price': " . $price . ",
                    'currency': '" . $data['currency_code'] . "',
                  }";
            }
        }

        $totals = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($data['sub_totals'], $this->default_store_currency, $data['currency_code']) : $data['sub_totals'];

        $output  = "";
        $output .= "<!-- FB Pixel -->";
        $output .= "<script>";
        $output .= "fbq('track', 'Purchase',";
        $output .= "{";
        $output .= "value: " . $totals . ",";
        $output .= "currency: '" . $data['currency_code'] . "',";
        $output .= "contents: [";
        $output .= implode(',', $products);
        $output .= "],";
        $output .= "content_type: 'product'";
        $output .= "});";
        $output .= "</script>";
        $output .= "<!-- ./FB Pixel -->";

        return $output;
    }

    public function pixelProductCodeSnippet($data) {

        if($this->pixelIsActive() == 0) { return; }

        $this->load->model('catalog/product');

        $options = $this->model_catalog_product->getProductOptions($data['product_id']);
        $product_categories = $this->model_catalog_product->getCategories($data['product_id']);
        $categories = array();
        if(!empty($product_categories)) {
            foreach($product_categories as $category)
            $categories[] = $this->model_catalog_category->getCategory($category['category_id']);
        }
        
        if(empty($options)) {
            $product_ids = "'" . $data['product_id'] . "'";
        } else {
            $product_options_data = Helper::getAllProductOptions($data['product_id'], $data['name'], $options);

            if(!empty($product_options_data['ids'])) {
                $product_ids = Helper::implodeWithQuotes($product_options_data['ids']);
            } else {
                $product_ids = "'" . $data['product_id'] . "'";
            }
        }

        $currency = $this->session->data['currency'];
        $price = $currency != $this->default_store_currency ? $this->currency->convert($data['price'], $this->default_store_currency, $currency) : $data['price'];

        $output  = "";
        $output .= "<!-- FB Pixel -->";
        $output .= "<script>";
        $output .= "fbq('track', 'ViewContent', {";
        $output .= "content_name: '" . $data['name'] . "',";
        $output .= "content_category: '" . (!empty($categories) ?  $this->getFullCategory($categories[0]['category_id'], " > ")  : '') . "',";
        $output .= "content_ids: [" . $product_ids . "],";
        $output .= "content_type: 'product',";
        $output .= "value:" . $price . ",";
        $output .= "currency: '" . $this->session->data['currency'] . "'";
        $output .= "});";
        $output .= "</script>";
        $output .= "<!-- ./FB Pixel -->";

        return $output;
    }

    public function pixelCategoryCodeSnippet($data) {

        if($this->pixelIsActive() == 0) { return; }

        $this->load->model('catalog/product');

        $product_ids = array(); $i = 1; //max. top 10 products in category
        if(!empty($data['products'])) {
            foreach($data['products'] as $p) {
                if($i > 10) { break; }
                $product_ids[] = $p['product_id'];
                $i++;
            }
        }

        $this->load->model('catalog/category');
        $category = $this->model_catalog_category->getCategory($data['category_id']);

        $output  = "";
        $output .= "<!-- FB Pixel -->";
        $output .= "<script>";
        $output .= "fbq('trackCustom', 'ViewCategory', {";
        $output .= "content_name: '" . $category['name'] . "',";
        $output .= "content_category: '" . $this->getFullCategory($data['category_id'], " > ") . "',";
        $output .= "content_ids: [" . Helper::implodeWithQuotes($product_ids) . "],";
        $output .= "content_type: 'product'";
        $output .= "});";
        $output .= "</script>";
        $output .= "<!-- ./FB Pixel -->";
        return $output;
    }

    public function pixelAddToCartCodeSnippet($data) {

        if($this->pixelIsActive() == 0) { return; }

        $price = $data['currency'] != $this->default_store_currency ? $this->currency->convert($data['price'], $this->default_store_currency, $data['currency']) : $data['price'];

        $output  = "";
        $output .= "<!-- FB Pixel -->";
        $output .= "<script>";
        $output .= "fbq('track', 'AddToCart', {";
        $output .= "content_ids: ['" . $data['product_id'] . "'],";
        $output .= "content_type: 'product',";
        $output .= "value: " . $price . ",";
        $output .= "currency: '" . $data['currency'] . "'";
        $output .= "});";   
        $output .= "</script>";
        $output .= "<!-- ./FB Pixel -->";

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

}
