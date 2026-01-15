<?php

class ControllerExtensionModuleGoogleMerchantApi extends Controller

{
    private $error = array();
    private $channel = "online";
    private $projectName = 'Google merchant Content Api';

    private $systemLibrary = DIR_SYSTEM . 'library/google_merchant_api/';
    private $redirectUri = HTTP_CATALOG . 'index.php?route=extension/module/google_merchant_api';


    public function index()
    {
        try {
            @include_once $this->systemLibrary . "vendor/autoload.php";
        } catch (\Throwable $e) {
        }

        include_once $this->systemLibrary . "vendor/autoload.php";

        $this->load->language('extension/module/google_merchant_api');
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->load->model('localisation/currency');
        $this->load->model('localisation/language');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $data['module_content_api_settings'] = $this->request->post;


            $this->model_setting_setting->editSetting('module_content_api_settings', $data);

            $json['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/module/google_merchant_api', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        //  $ip = $_SERVER['REMOTE_ADDR'];


        if (file_exists($this->systemLibrary . "token.json")) {
            $accessToken = json_decode(
                file_get_contents($this->systemLibrary . "token.json"),
                true
            );
        }

        if (isset($accessToken)) {
            $validate = $this->validateContentApi();
            if (isset($validate['error']['message'])) {
                $this->model_setting_setting->editSetting('module_google_merchant_api_status', ['module_google_merchant_api_status' => 0]);
                $data['validate'] = $validate['error']['message'];
            } else {
                $this->model_setting_setting->editSetting('module_google_merchant_api_status', ['module_google_merchant_api_status' => 1]);
                $data['validate'] = "";
            }
        } else {
            $data['validate'] = "";
        }


        if (isset($accessToken['access_token'])) {

            $data['access_token'] = $accessToken['access_token'];
            $data['access_code'] = $this->config->get("module_content_api_code");
        } else {

            $data['access_token'] = "-";
            $data['access_code'] = "-";
        }

        $data['languages'] = $this->model_localisation_language->getLanguages();
        $data['currencies'] = $this->model_localisation_currency->getCurrencies();
        $data['countries'] = $this->model_localisation_country->getCountries();

        $settings = $this->config->get('module_content_api_settings');

        if ($settings) {
            $data['country_selected'] = $settings['countries'];
            $data['language_selected'] = $settings['languages'];
            $data['currency_selected'] = $settings['currencies'];
            $data['merchant_id'] = $settings['merchant_id'];
            $data['prefixOfferId'] = $settings['prefixOfferId'];
            $data['category_status'] =  isset($settings['category_status']) ? $settings['category_status'] : "";
            $data['taxex_status'] = isset($settings['taxex_status']) ? $settings['taxex_status'] : "";

            $data['deleteproduct_status'] = $settings['deleteproduct_status'];
            $data['addproduct_status'] = $settings['addproduct_status'];
            $data['editproduct_status'] = $settings['editproduct_status'];
        } else {
            $data['country_selected'] = "";
            $data['language_selected'] = "";
            $data['currency_selected'] = "";
            $data['merchant_id'] = "";
            $data['prefixOfferId'] = "";
            $data['category_status'] = "";
            $data['taxex_status'] = "";

            $data['deleteproduct_status'] = 0;
            $data['addproduct_status'] = 0;
            $data['editproduct_status'] = 0;
        }



        if (file_exists($this->systemLibrary . "logs.txt"))
            $data['logs'] = file($this->systemLibrary . "logs.txt");

        $data['clearlogs'] = $this->url->link('extension/module/google_merchant_api/clearLogs', 'user_token=' . $this->session->data['user_token'], true);




        $data['client_secret'] = $this->isClientSecretJson() ? 1 : 0;




        $this->document->setTitle(strip_tags($this->language->get('heading_title')));
        // $this->document->addStyle('../extension/ContentApi/admin/view/stylesheet/module-google_merchant_api.css');
        $this->document->addScript('view/javascript/content_api.js');


        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );




        $data['action'] = $this->url->link('extension/module/google_merchant_api', 'user_token=' . $this->session->data['user_token'], true);
        $data['login'] = $this->url->link('extension/module/google_merchant_api/login', 'user_token=' . $this->session->data['user_token'], true);
        $data['loadJson'] = $this->url->link('extension/module/google_merchant_api/loadJson', 'user_token=' . $this->session->data['user_token'], true);
        $data['loadProducts'] = $this->url->link('extension/module/google_merchant_api/loadProducts', 'user_token=' . $this->session->data['user_token'], true);
        $data['deleteAllProducts'] = $this->url->link('extension/module/google_merchant_api/deleteAllProducts', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['redirect_url'] = $this->redirectUri;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/google_merchant_api', $data));
    }

    public function clearLogs()
    {
        $json = [];
        file_put_contents($this->systemLibrary . "logs.txt", '');
        $json['success'] = "Success";
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }



    private function isClientSecretJson()
    {
        if (file_exists($this->systemLibrary . "client_secret.json")) {
            return true;
        } else {
            return false;
        }
    }


    private function getClient($ajax = 0)
    {

        include_once $this->systemLibrary . "vendor/autoload.php";


        $client = new \Google_Client();
        $client->setApplicationName($this->projectName);
        $client->setScopes('https://www.googleapis.com/auth/content');
        try {
            $client->setAuthConfig($this->systemLibrary . "client_secret.json");
        } catch (\Throwable $e) {
        }

        $client->setRedirectUri($this->redirectUri);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        if ($ajax) {
            if (file_exists($this->systemLibrary . "token.json")) {
                unlink($this->systemLibrary . "token.json");
            }
        }

        // Load previously authorized credentials from a file.
        if (file_exists($this->systemLibrary . "token.json")) {
            $accessToken = json_decode(
                file_get_contents($this->systemLibrary . "token.json"),
                true
            );
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            if ($ajax) {
                $json = [];
                $json['href'] = $authUrl;
            } else {
                header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
            }

            if ($ajax) {
                return $json;
            }
        }

        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {

            // $client->refreshToken($accessToken['access_token']);

            // save refresh token to some variable

            $refreshTokenSaved = $client->getRefreshToken();

            // $refreshToken = $accessToken["access_token"]["refresh_token"];

            // update access token
            $client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

            // pass access token to some variable
            $accessTokenUpdated = $client->getAccessToken();

            // append refresh token
            $accessTokenUpdated['refresh_token'] = $refreshTokenSaved;

            //Set the new acces token
            $accessToken = $refreshTokenSaved;
            $client->setAccessToken($accessToken);

            // save to file
            file_put_contents(
                $this->systemLibrary . "token.json",
                json_encode($accessTokenUpdated)
            );
        }
        return $client;
    }

    private function validateContentApi()
    {
        $this->load->language('extension/module/google_merchant_api');

        $settings = $this->config->get('module_content_api_settings');

        if (!$settings) {
            return ["error" => ["message" => $this->language->get('text_error_merchant_id')]];
        }

        $merchantId = trim($settings['merchant_id']);

        if (file_exists($this->systemLibrary . "token.json")) {
            $accessToken = json_decode(
                file_get_contents($this->systemLibrary . "token.json"),
                true
            );
        }

        if (!isset($accessToken) || !$accessToken) {
            return ["error" => ["message" => $this->language->get('text_error_token')]];
        }

        $client = $this->getClient();
        $client->setAccessToken($accessToken);
        $service = new \Google_Service_ShoppingContent($client);

        $product = new \Google_Service_ShoppingContent_Product();

        $product->setOfferId('test');                 //id
        $product->setTitle('Content Api test');           // name
        $product->setChannel("online");
        $product->setContentLanguage("en");
        $product->setTargetCountry("UA");

        try {
            $result = $service->products->insert($merchantId, $product);
            $result = $service->products->delete($merchantId, "online:en:UA:test");
            return ['success' => 1];
        } catch (\Throwable $e) {
            return $this->getErrorThrowable($e);
        }
    }


    public function getErrorThrowable($e)
    {

        $info = json_decode($e->getMessage(), true);

        if (is_null($info)) {
            $info['error']['message'] = $e->getMessage();
        }
        return $info;
    }

    public function login()
    {
        $this->load->language('extension/module/google_merchant_api');

        $json = [];

        if (!$this->isClientSecretJson()) {
            $json['error'] = $this->language->get('error_upload_json');
        }

        if (!$json) {
            $ajax = 1;
            $client = $this->getClient($ajax);

            if (is_array($client) && isset($client['href'])) {
                $json['href'] = $client['href'];
            } else {
                //  $json['success'] = "Already Authorized";
            }
        }

        if ($this->request->post) {

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }



    public function loadJson()
    {
        $this->load->language('extension/module/google_merchant_api');

        $json = [];
        if (isset($this->request->files['file']['name'])) {
            $filename = basename($this->request->files['file']['name']);


            if ((utf8_strlen($filename) < 1) || (utf8_strlen($filename) > 128)) {
                $json['error'] = $this->language->get('error_filename');
            }

            if (substr($filename, -5) != '.json') {
                $json['error'] = $this->language->get('error_file_type');
            }

            if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
            }
        } else {
            $json['error'] = $this->language->get('error_upload');
        }

        if (!$json) {


            $file = $this->systemLibrary . "client_secret.json";

            $json['success'] = $this->language->get('text_success_client_secret');

            $json['data'] = $this->language->get('entry_success_upload_json_file');

            move_uploaded_file($this->request->files['file']['tmp_name'], $file);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


    public function loadProducts()
    {
        // $start = microtime(true);
        $json = [];

        $this->load->language('extension/module/google_merchant_api');

        $validate = $this->validateContentApi();

        if (isset($validate['error']['message'])) {
            $json['error'] = $validate['error']['message'];
        }


        if (!$json) {

            $client = $this->getClient();
            $service = new \Google_Service_ShoppingContent($client);
            $settings = $this->config->get('module_content_api_settings');
            $prefix = $settings['prefixOfferId'];
            $merchantId = trim($settings['merchant_id']);
            $this->load->model('catalog/product');

            // взять товары
            $filter_data = [
                'filter_status' => 1,
                // 'filter_price' => 0
            ];

            $results = $this->model_catalog_product->getProducts($filter_data);

            $data = [];
            $count_inserted = 0;
            $count_already_in = 0;
            $products_ids = [];


            $products = $service->products->listProducts($merchantId);
            while (!empty($products->getResources())) {

                // Обновить токен если время истекло вовремя загрузки
                if ($client->isAccessTokenExpired()) {
                    $client = $this->getClient();
                    $service = new \Google_Service_ShoppingContent($client);
                    $products = $service->products->listProducts($merchantId);
                }
                foreach ($products->getResources() as $product) {
                    $products_ids[$product->offerId] = 1;
                }
                if (is_null($products->getNextPageToken())) {
                    break;
                }
                $parameters['pageToken'] = $products->nextPageToken;
                $products = $service->products->listProducts($merchantId, $parameters);
            }


            if ($results) {
                foreach ($results as $result) {
                    $product_id = $result['product_id'];


                    if (isset($products_ids[$settings['prefixOfferId'] . $product_id])) {
                        $count_already_in++;
                        continue;
                    }

                    $data = $this->getProduct($product_id);
                    // print_R($data);
                    if (!$data)
                        continue;


                    $product = new \Google_Service_ShoppingContent_Product();

                    $product = $this->getProductApiData($product, $data);

                    // Обновить токен если время истекло вовремя загрузки
                    if ($client->isAccessTokenExpired()) {
                        $client = $this->getClient();
                        $service = new \Google_Service_ShoppingContent($client);
                    }

                    try {
                        $result = $service->products->insert($merchantId, $product);

                        $this->addLog("SUCCESS LOAD PRODUCTS $prefix$product_id");

                        $count_inserted++;
                    } catch (\Throwable $e) {
                        $info = $this->getErrorThrowable($e);
                        $error_log = $info['error']['message'];

                        $this->addLog("ERROR LOAD PRODUCTS $prefix$product_id  $error_log ");
                    }
                }


                if (count($results) > ($count_inserted + $count_already_in)) {

                    $json['error'] = sprintf($this->language->get("text_error_load_products_log"), $count_inserted + $count_already_in, count($results));
                } else {
                    $json['success'] = sprintf($this->language->get("text_success_load_products"), $count_inserted, $count_already_in);
                }
            } else {
                $json['error'] = $this->language->get("text_error_load_products");
            }
        }

        //       print_R('Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.');

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }



    private function getProductApiData(object $product, array $data)
    {

        $settings = $this->config->get('module_content_api_settings');

        $product->setOfferId($data['product_id']);                 //id
        $product->setTitle($data['name']);           // name
        $product->setDescription($data['description']);     // desc
        $product->setLink($data['href']);    //href
        $product->setImageLink($data['image']);    //image
        $product->setBrand($data['manufacturer']);    //manufacturer

        if ($data['images'])
            $product->setAdditionalImageLinks($data['images']);

        $product->setContentLanguage($data['contentLanguage']);      // oc_language select
        $product->setTargetCountry($data['targetCountry']);        // oc_country select 

        $product->setChannel($this->channel);

        $product->setAvailability($data['stock']);      // quantity  out of stock

        //    $product->setCondition('new');
        // $product->setTaxes(['rate' => 2]);

        if ($data['category'] && isset($settings['category_status'])) {
            $product->setGoogleProductCategory($data['category']);
            $product->setProductTypes($data['category']);
        }


        // $product->setGtin('9780007350896');

        $price = new \Google_Service_ShoppingContent_Price();
        $price->setValue($data['price']);        // price
        $price->setCurrency($data['currency']);     // oc_currency  select 

        //  $shipping_price = new \Google_Service_ShoppingContent_Price();
        //   $shipping_price->setValue('0.99');
        //  $shipping_price->setCurrency('GBP');


        // $shipping = new \Google_Service_ShoppingContent_ProductShipping();
        // $shipping->setPrice($shipping_price);
        //  $shipping->setCountry('GB');
        //  $shipping->setService('Standard shipping');

        //  $shipping_weight = new \Google_Service_ShoppingContent_ProductShippingWeight();
        // $shipping_weight->setValue(200);
        // $shipping_weight->setUnit('grams');

        $product->setPrice($price);

        //  $product->setShipping(array($shipping));
        // $product->setShippingWeight($shipping_weight);

        return $product;
    }

    private function getProduct($product_id = 0)
    {
        $settings = $this->config->get('module_content_api_settings');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/manufacturer');
        $this->load->model('tool/image');
        $this->load->model('localisation/currency');

        $this->load->model('localisation/language');

        $country = $settings['countries'];
        $language = $settings['languages'];
        $currency = $settings['currencies'];

        $language_id = $this->model_localisation_language->getLanguageByCode($language);

        if (!$language_id) {

            $this->addLog("GET PRODUCT DATA LANGUAGE ERROR language_code=$language product_id=$product_id");
            return false;
        }
        $language_id = ['language_id'];

        $result =   $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$language_id . "'")->row;


        if (!$result) {
            $this->addLog("GET PRODUCT DATA ERROR product_id=$product_id language_id=$language_id");
            return false;
        }

        $data = [];

        // print_R($result);

        if ($result) {

            if ($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], 248, 248);
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', 248, 248);
            }

            $images_opencart = $this->model_catalog_product->getProductImages($result['product_id']);
            $images = [];
            foreach ($images_opencart as $imageAdd) {
                $imageAdd = $this->model_tool_image->resize(html_entity_decode($imageAdd['image'], ENT_QUOTES, 'UTF-8'), 248, 248);
                if ($imageAdd == $image) {
                    continue;
                }
                $images[] = $imageAdd;
            }
            $manufacturer = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);

            // PRICE
            $currency_symbol = $this->model_localisation_currency->getCurrencyByCode($currency);
            $currency_symbol = $currency_symbol['symbol_left'] ?  $currency_symbol['symbol_left'] : $currency_symbol['symbol_right'];


            if (isset($result['special'])) {
                $special = $this->currency->format($result['special'], $currency);
            } else {
                $special = false;
            }

            $price = $this->currency->format($result['price'], $currency);
            $tax = $this->currency->format($this->tax->calculate(isset($result['special']) ? $result['special'] : $result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);


            if (isset($settings['taxex_status']) && $settings['taxex_status']) {
                $price = $tax;
            } else {
                if ($special)
                    $price = $special;
            }
            $price = preg_replace("#\\$currency_symbol#", '', $price);
            $price = preg_replace("#\,#", '', $price);
            $price = (float)$price;
            // PRICE

            $category = $this->model_catalog_category->getCategory($result['product_id']);
            $category = isset($category['path']) ? $category['path'] : "";
            $category_ids = $this->model_catalog_product->getProductCategories($result['product_id']);
            $path = 0;
            if ($category_ids) {
                $category_id = $category_ids[0];
                $paths = $this->model_catalog_category->getCategoryPath($category_id);
                if ($paths) {
                    foreach ($paths as $path) {
                        $pathsIm[] = $path['path_id'];
                    }
                }
                $path = implode("_", $pathsIm);
            }

            $href = HTTP_CATALOG . 'index.php?route=product/product' . "&path=$path"  . '&product_id=' . $result['product_id'];


            $data = [
                'product_id' => $settings['prefixOfferId'] . $result['product_id'],
                'name'       => $result['name'],
                'description' => html_entity_decode($result['description']),
                'href'        => $href,
                'image'      => $image,
                'images' => $images,
                'manufacturer' => isset($manufacturer['name']) ? $manufacturer['name'] : "",
                'currency' => $currency,
                'price'      => $price,
                'contentLanguage' =>  substr($language, 0, 2),
                'targetCountry' => $country,
                'stock' => ($result['quantity'] > 0) ? "in stock" : "out of stock",
                'category' => html_entity_decode($category),
                // 'model'      => $result['model'],
                // 'special'    => $special,
                'quantity'   => $result['quantity'],
                'status'     => $result['status'],

            ];
        }
        return $data;
    }


    public function deleteAllProducts()
    {

        $json = [];
        $validate = $this->validateContentApi();
        $this->load->language('extension/module/google_merchant_api');

        if (isset($validate['error']['message'])) {
            $json['error'] = $validate['error']['message'];
        }

        $this->load->language('extension/module/google_merchant_api');
        if (!$json) {
            $client = $this->getClient();

            $service = new \Google_Service_ShoppingContent($client);
            $settings = $this->config->get('module_content_api_settings');
            $merchantId = trim($settings['merchant_id']);


            $products = $service->products->listProducts($merchantId);


            if ($products->getResources()) {
                $counts_delete = 0;

                while (!empty($products->getResources())) {

                    foreach ($products->getResources() as $product) {



                        $product_delete = $product->getId();

                        // Обновить токен если время истекло вовремя загрузки
                        if ($client->isAccessTokenExpired()) {
                            $client = $this->getClient();
                            $service = new \Google_Service_ShoppingContent($client);
                            $products = $service->products->listProducts($merchantId);
                        }

                        try {
                            $result = $service->products->delete($merchantId, $product_delete);
                            $this->addLog("SUCCESS DELETE ALL PRODUCTS $product->offerId");
                            $counts_delete++;
                        } catch (\Throwable $e) {

                            $info = $this->getErrorThrowable($e);
                            $error_log = $info['error']['message'];

                            $this->addLog("ERROR DELETE ALL PRODUCTS $product->offerId $error_log ");
                        }
                    }

                    if (is_null($products->getNextPageToken())) {
                        break;
                    }
                    $parameters['pageToken'] = $products->nextPageToken;
                    $products = $service->products->listProducts($merchantId, $parameters);
                }

                $json['success'] = sprintf($this->language->get("text_success_delete_all_products"), $counts_delete);
            } else {
                $json['success'] = $this->language->get('text_error_delete_all_products');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


    public function copyProduct($a, $b, $c)
    {
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product`ORDER BY `" . DB_PREFIX . "product`.`date_modified` DESC")->row;

        if (isset($result['product_id'])) {
            $this->addProduct(false, false, $result['product_id']);
        }
    }

    public function addProduct($a, $b, $c)
    {


        $settings = $this->config->get('module_content_api_settings');

        if (!isset($settings['addproduct_status']) || !$settings['addproduct_status']) {
            return false;
        }

        $product_id = $c;
        $json = [];

        $validate = $this->validateContentApi();

        if (isset($validate['error']['message'])) {
            $json['error'] = $validate['error']['message'];
            $error = $json['error'];
            $this->addLog("ERROR TRIGGER ADD PRODUCT $error ");
        }


        if (!$json) {
            $client = $this->getClient();

            $service = new \Google_Service_ShoppingContent($client);

            $merchantId = trim($settings['merchant_id']);
            $prefix = $settings['prefixOfferId'];

            $data = $this->getProduct($product_id);

            if ($data) {

                $product = new \Google_Service_ShoppingContent_Product();

                $product = $this->getProductApiData($product, $data);

                try {
                    $result = $service->products->insert($merchantId, $product);
                    $this->addLog("SUCCESS TRIGGER ADD PRODUCT $prefix$product_id ");
                } catch (\Throwable $e) {

                    $info = $this->getErrorThrowable($e);
                    $error_log = $info['error']['message'];

                    $this->addLog("ERROR TRIGGER ADD PRODUCT $prefix$product_id  $error_log ");
                }
            } else {
                $language_id = (int)$this->config->get('config_language_id');
                $this->addLog("ERROR TRIGGER ADD PRODUCT No product with id=$product_id and language_id=$language_id ");
            }
        }
    }


    public function editProduct($a, $b, $c)
    {

        $settings = $this->config->get('module_content_api_settings');

        if (!isset($settings['editproduct_status']) || !$settings['editproduct_status']) {
            return false;
        }

        $product_id = $b[0];
        $json = [];



        $validate = $this->validateContentApi();

        if (isset($validate['error']['message'])) {
            $json['error'] = $validate['error']['message'];
            $error = $json['error'];
            $this->addLog("ERROR TRIGGER EDIT PRODUCT $error ");
        }


        if (!$json) {
            $client = $this->getClient();

            $service = new \Google_Service_ShoppingContent($client);

            $merchantId = trim($settings['merchant_id']);
            $prefix = $settings['prefixOfferId'];

            $data = $this->getProduct($product_id);

            if ($data) {

                $product = new \Google_Service_ShoppingContent_Product();

                $product = $this->getProductApiData($product, $data);

                try {
                    $result = $service->products->insert($merchantId, $product);
                    $this->addLog("SUCCESS TRIGGER EDIT PRODUCT $prefix$product_id ");
                } catch (\Throwable $e) {

                    $info = $this->getErrorThrowable($e);
                    $error_log = $info['error']['message'];

                    $this->addLog("ERROR TRIGGER EDIT PRODUCT $prefix$product_id  $error_log ");
                }
            } else {
                $language_id = (int)$this->config->get('config_language_id');
                $this->addLog("ERROR TRIGGER EDIT PRODUCT No product with id=$product_id and language_id=$language_id ");
            }
        }
    }

    public function deleteProduct($a, $b, $c)
    {
        $settings = $this->config->get('module_content_api_settings');

        if (!isset($settings['deleteproduct_status']) || !$settings['deleteproduct_status']) {
            return false;
        }

        $product_id = $b[0];
        $json = [];


        $validate = $this->validateContentApi();

        if (isset($validate['error']['message'])) {
            $json['error'] = $validate['error']['message'];
            $error = $json['error'];
            $this->addLog("ERROR TRIGGER DELETE PRODUCT $error ");
        }


        if (!$json) {
            $client = $this->getClient();
            $service = new \Google_Service_ShoppingContent($client);

            $merchantId = trim($settings['merchant_id']);
            $prefix = $settings['prefixOfferId'];

            $products = $service->products->listProducts($merchantId);

            while (!empty($products->getResources())) {

                // Обновить токен если время истекло вовремя загрузки
                if ($client->isAccessTokenExpired()) {
                    $client = $this->getClient();
                    $service = new \Google_Service_ShoppingContent($client);
                    $products = $service->products->listProducts($merchantId);
                }

                foreach ($products->getResources() as $product) {
                    if ($product->offerId == $settings['prefixOfferId'] . $product_id) {

                        $product_delete = $product->getId();

                        try {
                            $result = $service->products->delete($merchantId, $product_delete);
                            $this->addLog("SUCCESS TRIGGER DELETE PRODUCT $prefix$product_id ");
                        } catch (\Throwable $e) {
                            $info = $this->getErrorThrowable($e);
                            $error_log = $info['error']['message'];

                            $this->addLog("ERROR TRIGGER DELETE PRODUCT $prefix$product_id  $error_log ");
                        }
                        return true;
                    }
                }



                if (is_null($products->getNextPageToken())) {
                    break;
                }
                $parameters['pageToken'] = $products->nextPageToken;
                $products = $service->products->listProducts($merchantId, $parameters);
            }
        }
        $this->addLog("ERROR TRIGGER DELETE PRODUCT NO PRODUCT $prefix$product_id ");
    }




    public function install()
    {



        $this->load->model('setting/setting');
        $this->load->model('setting/event');

        //    $this->model_setting_setting->editSetting("module_content_api_settings");


        if (!is_dir($this->systemLibrary . "vendor")) {
            $zip = new \ZipArchive();
            $zip->open($this->systemLibrary . "google_merchant_api.zip");
            $zip->extractTo($this->systemLibrary);
            $zip->close();
        }
        $this->model_setting_event->addEvent("activity_content_api_copy_after",  "admin/model/catalog/product/copyProduct/after", "extension/module/google_merchant_api/copyProduct");

        $this->model_setting_event->addEvent("activity_content_api_add_after",  "admin/model/catalog/product/addProduct/after", "extension/module/google_merchant_api/addProduct");
        $this->model_setting_event->addEvent("activity_content_api_delete_after",  "admin/model/catalog/product/deleteProduct/after", "extension/module/google_merchant_api/deleteProduct");
        $this->model_setting_event->addEvent("activity_content_api_edit_after", "admin/model/catalog/product/editProduct/after", "extension/module/google_merchant_api/editProduct");
    }


    public function uninstall()
    {
        $this->load->model('setting/event');
        $this->load->model('setting/setting');

        $this->model_setting_setting->deleteSetting("module_content_api_settings");
        $this->model_setting_setting->deleteSetting("module_content_api_code");
        $this->model_setting_setting->deleteSetting("module_google_merchant_api_status");


        if (file_exists($this->systemLibrary . "token.json")) {
            unlink($this->systemLibrary . "token.json");
        }
        if (file_exists($this->systemLibrary . "client_secret.json")) {
            unlink($this->systemLibrary . "client_secret.json");
        }
        if (file_exists($this->systemLibrary . "logs.txt")) {
            unlink($this->systemLibrary . "logs.txt");
        }

        if (is_dir($this->systemLibrary . "vendor")) {
            $this->dirDel($this->systemLibrary . "vendor");
        }

        $this->model_setting_event->deleteEventByCode("activity_content_api_copy_after");
        $this->model_setting_event->deleteEventByCode("activity_content_api_add_after");
        $this->model_setting_event->deleteEventByCode("activity_content_api_delete_after");
        $this->model_setting_event->deleteEventByCode("activity_content_api_edit_after");
    }


    private function addLog($message)
    {
        file_put_contents($this->systemLibrary . "logs.txt", '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n", FILE_APPEND);
    }

    private function dirDel($dir)
    {
        $d = opendir($dir);
        while (($entry = readdir($d)) !== false) {
            if ($entry != "." && $entry != "..") {
                if (is_dir($dir . "/" . $entry)) {
                    $this->dirDel($dir . "/" . $entry);
                } else {
                    unlink($dir . "/" . $entry);
                }
            }
        }
        closedir($d);
        rmdir($dir);
    }
    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/google_merchant_api')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
