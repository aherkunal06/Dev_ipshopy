<?php

namespace mergado;

class Google { //Google Ads

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

    /************ Google Ads Conversion & Remarketing **************/
    public function googleAdsConversionIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_google_ads_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1"))  ? 1 : 0;
    }

    public function googleAdsRemarketingIsActive() {
        $statuses = $this->config->get($this->extension_fullname . '_adverts_google_ads_remarketing_status');
        return !empty($statuses) && isset($statuses[$this->storeid]) && in_array($statuses[$this->storeid], array("on", "1"))  ? 1 : 0;
    }

    public function googleAdsCode() {
        $codes = $this->config->get($this->extension_fullname . '_adverts_google_ads_code');
        return !empty($codes) && isset($codes[$this->storeid]) ? $codes[$this->storeid] : 'undefined';
    }

    public function googleAdsLabel() {
        $labels = $this->config->get($this->extension_fullname . '_adverts_google_ads_label');
        return !empty($labels) && isset($labels[$this->storeid]) ? $labels[$this->storeid] : 'undefined';
    }

    public function googleAdsRemarketingID() {
        $ids = $this->config->get($this->extension_fullname . '_adverts_google_ads_remarketing');
        return !empty($ids) && isset($ids[$this->storeid]) ? $ids[$this->storeid] : 'undefined';
    }

    public function googleAdsGlobalTagHeaderSnippet()
    {
        if ($this->googleAdsConversionIsActive() == 0 && $this->googleAdsRemarketingIsActive() == 0) {
            return;
        }

        $options = "";
        if ($this->googleAdsRemarketingIsActive() == 0) { //disable remarketing
            $options .= ",{";
            $options .= "'restricted_data_processing': true ";
            $options .= "}";
        }

        $output  = "";
        $output  .= "<!-- Google Ads Conversion & Remarketing -->";
        $output  .= "<!-- Global site tag (gtag.js) - Google Ads: " . $this->googleAdsCode() . " -->";
        $output  .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id=" . $this->googleAdsCode() . "\"></script>";
        $output  .= "<script>";
        $output  .= "window.dataLayer = window.dataLayer || [];";
        $output  .= "function gtag(){dataLayer.push(arguments);}";
        $output  .= "gtag('js', new Date());";

        if ($this->googleAdsConversionIsActive() == 1) { //conversion
            $output .= "gtag('config', '" . $this->googleAdsCode() . "'" . $options . ");";
        }

        if ($this->googleAdsRemarketingIsActive() == 1) { //remarketing
            $output  .= "gtag('config', '" . $this->googleAdsRemarketingID() . "');";
        }
        
        $output  .= "</script>";
        $output .= "<!-- ./Google Ads Conversion & Remarketing -->";

        return $output;
    }

    public function googleAdsConversionSuccessCodeSnippet($data) {
        if ($this->googleAdsConversionIsActive() == 0 ) { return; }

        $totals = $data['currency_code'] != $this->default_store_currency ? $this->currency->convert($data['sub_totals'], $this->default_store_currency, $data['currency_code']) : $data['sub_totals'];

        $output  = "";
        $output  .= "<!-- Google Ads Conversion -->";
        $output  .= "<script>";
        $output  .= "gtag('event', 'conversion', {'send_to': '" . $this->googleAdsCode() . "/" . $this->googleAdsLabel() . "',";
        $output  .=     "value: " . $totals . ",";
        $output  .=     "currency: '" . $data['currency_code'] . "',";
        $output  .=     "transaction_id: '" . $data['order_id'] . "'";
        $output  .= "});";
        $output  .= "</script>";
        $output  .= "<!-- ./Google Ads Conversion -->";

        return $output;
    }

    public function googleAdsRemarketingProductCodeSnippet($data) {

        if($this->googleAdsRemarketingIsActive() == 0) { return; }

        $this->load->model('catalog/product');

        $options = $this->model_catalog_product->getProductOptions($data['product_id']);
        $product_categories = $this->model_catalog_product->getCategories($data['product_id']);
        $categories = array();
        if(!empty($product_categories)) {
            foreach($product_categories as $category)
                $categories[] = $this->model_catalog_category->getCategory($category['category_id']);
        }

        $product_ids = array();
        if(empty($options)) {
            $product_ids[] = $data['product_id'];
        } else {
            $product_options_data = Helper::getAllProductOptions($data['product_id'], $data['name'], $options);

            if(!empty($product_options_data['ids'])) {
                $product_ids = $product_options_data['ids'];
            } else {
                $product_ids[] = $data['product_id'];
            }
        }

        $output  = "";
        $output  .= "<!-- Google Ads Remarketing -->";
        $output  .= "<script>";
        $output  .= "gtag('event','view_item', {";
        $output  .= "'items': [";
                                    for($i = 0; $i < count($product_ids); $i++) {
                                        $output .= "{";
                                        $output .= "id: '" . $product_ids[$i] . "',";
                                        $output .= "category: '" . (!empty($categories) ? $this->getFullCategory($categories[0]['category_id'], " > ") : '') . "',";
                                        $output .= "google_business_vertical: 'retail'";
                                        if($i < count($product_ids)-1) {
                                            $output .= "},";
                                        } else {
                                            $output .= "}";
                                        }
                                    }
        $output  .=           "]";
        $output  .= "});";
        $output  .= "</script>";
        $output  .= "<!-- ./Google Ads Remarketing -->";

        return $output;
    }

    public function googleAdsRemarketingCategoryCodeSnippet($data) {

        if($this->googleAdsRemarketingIsActive() == 0) { return; }

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
        $output  .= "<!-- Google Ads Remarketing -->";
        $output  .= "<script>";
        $output  .= "gtag('event','view_item_list', {";
        $output  .= "'items': [";
                                    for($i = 0; $i < count($product_ids); $i++) {
                                        $output .= "{";
                                        $output .= "id: '" . $product_ids[$i] . "',";
                                        $output .= "category: '" . $category['name'] . "',";
                                        $output .= "google_business_vertical: 'retail'";
                                        if($i < count($product_ids)-1) {
                                            $output .= "},";
                                        } else {
                                            $output .= "}";
                                        }
                                    }
        $output  .=           "]";
        $output  .= "});";
        $output  .= "</script>";
        $output  .= "<!-- ./Google Ads Remarketing -->";

        return $output;
    }

    public function googleAdsRemarketingAddToCartCodeSnippet($data) {
        if($this->googleAdsRemarketingIsActive() == 0) { return; }

        $price = $data['currency'] != $this->default_store_currency ? $this->currency->convert($data['price'], $this->default_store_currency, $data['currency']) : $data['price'];

        $output  = "";
        $output .= "<!-- Google Ads Remarketing -->";
        $output .= "<script>";
        $output .= "gtag('event', 'add_to_cart', {";
        $output .= "value: " . $price. ",";
        $output .= "currency: '" . $data['currency'] . "',";
        $output .= "items: [";
        $output .=              "{";
        $output .=                  "id: '" . $data['product_id'] . "',";
        $output .=                  "google_business_vertical: 'retail'";
        $output .=              "}";
        $output .=         "]";
        $output .= "});";
        $output .= "</script>";
        $output .= "<!-- ./Google Ads Remarketing -->";

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
    /************ ./Google Ads Conversion & Remarketing **************/

}
