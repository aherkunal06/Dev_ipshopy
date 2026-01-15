<?php

class ControllerExtensionFeedMergadoMarketingPack extends Controller {

  private $extension_path = 'feed/';
  private $extension_name = 'mergado_marketing_pack'; 
  private $extension_fullname = 'feed_mergado_marketing_pack';
  private $extension_type = 'feed';
  private $error = array();
  private $stores = array();
  private $currentLang;
  private $model = array();

  public function index() {

    $this->currentLang = $this->language->get('code');

    //load mergado libraries
    $this->registry->set('mergado', new Mergado($this->registry));
    $mergado = $this->mergado;
    
    //load models
    $this->loadModels();

    //auto update of extension db tables
    $this->mergado->dbmodel->needUpdate();

    //load translations
    $this->load->language('extension/' . $this->extension_path . $this->extension_name);

    //localization
    $this->document->setTitle($this->language->get('heading_title'));
    $this->document->addScript($mergado::TOGGLE_BUTTON_JS_URL);
    $this->document->addStyle($mergado::TOGGLE_BUTTON_CSS_URL);
    $this->document->addScript($mergado::JS_URL);
    $this->document->addStyle($mergado::CSS_URL);

    //get all stores
    $data['stores'] = $this->getStores();

    //get all languages
    $data['languages'] = $this->model['language']->getLanguages();

    //get all currencies
    $data['currencies'] = $this->model['currency']->getCurrencies();

    //process data
    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() ) {

      $this->logChanges($this->request->post, $mergado::VERSION);

      $this->model['settings']->editSetting($this->extension_fullname, $this->request->post);
          
      $this->session->data['success'] = $this->language->get('text_success');
          
      $this->response->redirect($this->url->link('extension/' . $this->extension_path . $this->extension_name, 'user_token=' . $this->session->data['user_token'] . '&type=' . $this->extension_type . (isset($this->request->get['tab']) ? '&tab=' . $this->request->get['tab'] : ''), true));
    }

    //settings
    $settings = $this->model['settings']->getSetting($this->extension_fullname);
    if (!empty($settings)) {
      foreach($settings as $key=>$value) {
        if (!isset($data[$key])) {
          $data[$key] = $value;
        }
      }
    }

    //set warnings
    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }

    //breadcrumbs menu
    $data['breadcrumbs'] = array();

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=' . $this->extension_type, true)
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('extension/' . $this->extension_path . $this->extension_name, 'user_token=' . $this->session->data['user_token'], true)
    );

    //data for view
    $data['action'] = $this->url->link('extension/' . $this->extension_path . $this->extension_name, 'user_token=' . $this->session->data['user_token'] . '&type=' . $this->extension_type . (isset($this->request->get['tab']) ? '&tab=' . $this->request->get['tab'] : ''), true);
    $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=' . $this->extension_type, true);
    
    $data['user_token'] = $this->session->data['user_token'];
    $data['route'] = 'extension/' . $this->extension_path . $this->extension_name;
    $data['extension_fullname'] = $this->extension_fullname;
    $data['tab_name'] = isset($this->request->get['tab']) ? $this->request->get['tab'] : '';

    //form data
    if (!empty($this->request->post)) {
      foreach($this->request->post as $key=>$value) {
        $data[$key] = $value;
      }
    }

    if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$data['base_url'] = HTTPS_SERVER;
		} else {
			$data['base_url'] = HTTP_SERVER;
    }


    if(intval($this->config->get($this->extension_fullname . '_already_rated')) == 1) { //widget was hidden by user 30 days before or already rated
        $data['hideRatingWidget'] = 1;
    } elseif(isset($_COOKIE['mergadoRatingWidget'])) {
        $lastShow = intval($this->config->get($this->extension_fullname . '_rating_widget_showed'));
        $now = time();
        if(($now - $lastShow) <= $mergado::RATING_WIDGET_COOKIE_LIFETIME) {
          $data['hideRatingWidget'] = 1;
        } else {
          $data['hideRatingWidget'] = 0;
        }
    } else {
        $installed = intval($this->config->get($this->extension_fullname . '_install_date'));
        $now = time();

        if($installed == 0 || ($now - $installed) <= $mergado::RATING_WIDGET_COOKIE_LIFETIME) {
            $data['hideRatingWidget'] = 1;
        } else {
            $data['hideRatingWidget'] = 0;
        }

    }
    
    $data['product_feeds'] = array();
    $data['category_feeds'] = array();
    $data['heureka_availability_feeds'] = array();
    $data['store_logs'] = array();
    $mergado_token = $this->model['main']->getMergadoToken();
    
    $data = $this->getStoreLogs($data, $mergado_token);
    $data = $this->getProductFeeds($data, $mergado_token);
    $data = $this->getCategoryFeeds($data, $mergado_token);
    $data = $this->getHeurekaAvailabilityFeeds($data, $mergado_token);
 
    $data['mergado_token'] = $mergado_token;
    $data['version'] = $mergado::VERSION;
    $data['php_version'] = phpversion();
    $data['opencart_version'] = VERSION;
    $data['logo_url'] = $data['base_url'] . $mergado::LOGO_URL;
    $data['logo_pack_url'] = $data['base_url'] . $mergado::LOGO_PACK_URL;
    $data['infographics_url'] = $data['base_url'] . $mergado::INFOGRAPHICS_URL;
    $data['sidebar_data'] = str_replace("%REVIEW_URL%", "javascript:showRatingModal();",file_get_contents($mergado::SIDEBAR_URL));
    $data['bottom_banner_data'] = str_replace("%REVIEW_URL%", "javascript:showRatingModal();",file_get_contents($mergado::BOTTOM_BANNER_URL));
    $data['rating_url'] = $mergado::PLUGIN_MARKETPLACE_URL;
    $data['rating_popup_img1']=  $mergado::IMG_URL . 'pop-up_hodnoceni_1.svg';
    $data['rating_popup_img2']=  $mergado::IMG_URL . 'pop-up_hodnoceni_2.svg';
    $data['rating_popup_img3']=  $mergado::IMG_URL . 'pop-up_hodnoceni_3.svg';

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $data['news'] = $this->mergado->news->getNews($this->config->get('config_admin_language'));

    $glami = $mergado->glami;
    $data['glami_pixel_countries'] = $glami::$pixel_supported_countries;

    $data['heureka_supported_countries'] = array('cz','sk');

    //subviews
    $data['sidebar_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_sidebar', $data);
    $data['bottom_banner_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_bottom_banner', $data);
    $data['header_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_header', $data);
    $data['home_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_home', $data);
    $data['menu_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_menu', $data);
    $data['settings_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_settings', $data);
    $data['systems_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_systems', $data);
    $data['cron_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_cron', $data);
    $data['xml_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_xml', $data);
    $data['support_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_support', $data);
    $data['license_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_license', $data);
    $data['news_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_news', $data);
    $data['rating_view'] = $this->load->view('extension/' . $this->extension_path . $this->extension_name . '_rating_modal', $data);

    //render view
    $this->response->setOutput($this->load->view('extension/' . $this->extension_path . $this->extension_name, $data));
 
  }
  
  protected function validate() {
  
    //check permissions
    if (!$this->user->hasPermission('modify', 'extension/' . $this->extension_path . $this->extension_name)) {
      $this->error['warning'] = $this->language->get('error_permission');
      return !$this->error;
    }

    foreach ($this->stores as $store) {
      //loop
    } 

    return !$this->error;
  }

  public function install() {

    //load mergado libraries
    $this->registry->set('mergado', new Mergado($this->registry)); 

    //load models
    $this->loadModels();

    $args = array(
        $this->extension_fullname . '_logs' => 1,
        $this->extension_fullname . '_install_date' => time()
    );

    //enable log
    $this->model['settings']->editSetting($this->extension_fullname ,  $args);

    //create db scheme
    $this->mergado->dbmodel->create();

  }

  public function uninstall() {

    //load mergado libraries
    $this->registry->set('mergado', new Mergado($this->registry));

    //load models
    $this->loadModels();

    //delete db scheme
    $this->mergado->dbmodel->delete();

    //delete feed files
    $this->deleteFeedFiles();

    //delete module settings
    $this->model['settings']->deleteSetting($this->extension_fullname);

  }

  private function deleteFeedFiles() {
    
    $root_dir = DIR_APPLICATION.'../mergado/';
    if(file_exists($root_dir)) {
      $files = scandir($root_dir);

      //delete all .xml files
      array_map('unlink', glob( $root_dir . "*.xml"));

      //delete dir
      rmdir($root_dir);
    }
  }

  public function deleteFeed() {

      //set logger
      $logs = is_null($this->config->get($this->extension_fullname . '_logs')) ? 0 : 1;
      define('MERGADO_LOGGER_ENABLED', $logs);

      $this->load->model('extension/'. $this->extension_path . $this->extension_name . '_logger');
      $this->load->model('extension/'. $this->extension_path . $this->extension_name . '_generator');

      $model = "model_extension_" . $this->extension_type . "_" . $this->extension_name;
      $model_logger = "model_extension_" . $this->extension_type . "_" . $this->extension_name . '_logger';
      $model_generator = "model_extension_" . $this->extension_type . "_" . $this->extension_name . '_generator';

      $path = $this->request->post['path'];
      $hash = $this->request->post['hash'];

      if(file_exists($path)) {
          unlink($path);
          $this->{$model_generator}->delete($hash);
          $this->{$model_logger}->log('Feed {$hash} was successfully deleted.', array());
          echo 1;
      } else {
          $this->{$model_logger}->log('Deletion of feed {$hash} failed.', array());
          echo 0;
      }
  }

  public function clearAllLogs() {
    
    //load models
    $this->loadModels();

    $this->model['logger']->clearAll();
  }

  public function exportCsvLogs() {

    //load models
    $this->loadModels();

    header('Content-Type: text/plain');
		header('Content-Disposition: attachment;filename="'.date('Y_m_d') .'_mergado_log.csv"');
    header('Cache-Control: max-age=0');
    
    $this->model['logger']->exportCSV();
  }

  private function getStores() {

    //get stores
    $stores = $this->model['store']->getStores();

    $this->stores[] = array( //default store
      'store_id' => 0,
      'name'     => $this->config->get('config_name'),
      'url'      => isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')) ? HTTPS_CATALOG : HTTP_CATALOG
    );
    
    foreach ($stores as $store) {
      $this->stores[] = array(
        'store_id' => $store['store_id'],
        'name'     => $store['name'],
        'url'      => !empty($store['ssl']) ? $store['ssl'] : $store['url']
      );
    }

    return $this->stores;

  }

  private function getProductFeeds($data, $token) {
    
    foreach($data['stores'] as $store) {
      foreach($data['languages'] as $lang) {
        foreach($data['currencies'] as $currency) {
          $currency_code = strtolower($currency['code']);
          
          //product
          $product_hash = md5('pf_' . $store['store_id'] . '_' . $lang['code'] . '_' . $currency_code);
          $product_cron_hash = md5($token . '_pf_' . $store['store_id'] . '_' . $lang['code'] . '_' . $currency_code);
          $product_path = $data['stores'][0]['url'] . 'mergado/pf_' . $product_hash . '.xml';
          $product_abs_path =  DIR_APPLICATION . '../' . 'mergado/pf_' . $product_hash . '.xml';

          $status = '-';
          $last_change = '-';
          $last_creation = '-';
          $progress = '0';
          if($result = $this->model['generator']->getData($product_hash)) {
            $status = isset($result[0]['status']) && $result[0]['status'] == 1 ? $result[0]['status'] : '-';
            if(isset($result[0]['change_date']) && $result[0]['change_date']!='0000-00-00 00:00:00') {
              $last_change = $this->currentLang == 'en' ? date('Y.m.d H:i:s', strtotime($result[0]['change_date'])) : date('d.m.Y H:i:s', strtotime($result[0]['change_date']));
            }
            if(isset($result[0]['last_creation_date']) && $result[0]['last_creation_date']!='0000-00-00 00:00:00'){
             $last_creation =  $this->currentLang == 'en' ? date('Y.m.d H:i:s', strtotime($result[0]['last_creation_date'])) : date('d.m.Y H:i:s', strtotime($result[0]['last_creation_date']));
            }
            $offset = isset($result[0]['current_offset']) && is_numeric($result[0]['current_offset']) ? $result[0]['current_offset'] : 0;
            $items_count = isset($result[0]['items_count']) && is_numeric($result[0]['items_count']) ? $result[0]['items_count'] : 0;
            $progress = $offset > 0 && $items_count > 0 ? (int) (($offset / $items_count) * 100) : 0;
          }
          
          //product feeds
          $data['product_feeds'][$store['store_id']][$lang['code']][$currency_code] = array(
              'abs_path' => $product_abs_path,
              'path' => $product_path,
              'hash' => $product_hash,
              'cron_hash' => $product_cron_hash,
              'log_hash' => $data['store_logs'][$store['store_id']]['hash'],
              'exists' => $this->urlExists($product_path) ? 1 : 0,
              'status' => $status,
              'last_change' => $last_change,
              'last_creation' => $last_creation,
              'progress' => $progress
          );
        }
      }
    }

    return $data;
  }

  private function getCategoryFeeds($data, $token) {

    foreach($data['stores'] as $store) {
      foreach($data['languages'] as $lang) {
        foreach($data['currencies'] as $currency) {
          $currency_code = strtolower($currency['code']);
          
          //category
          $category_hash = md5('cf_' . $store['store_id'] . '_' . $lang['code'] . '_' . $currency_code);
          $category_cron_hash = md5($token . '_cf_' . $store['store_id'] . '_' . $lang['code'] . '_' . $currency_code);
          $category_path = $data['stores'][0]['url'] . 'mergado/cf_' . $category_hash . '.xml';
          $category_abs_path =  DIR_APPLICATION . '../' . 'mergado/cf_' . $category_hash . '.xml';

          $status = '-';
          $last_change = '-';
          $last_creation = '-';
          $progress = '0';
          if($result = $this->model['generator']->getData($category_hash)) {
            $status = isset($result[0]['status']) && $result[0]['status'] == 1 ? $result[0]['status'] : '-';
            if(isset($result[0]['change_date']) && $result[0]['change_date']!='0000-00-00 00:00:00') {
              $last_change = $this->currentLang == 'en' ? date('Y.m.d H:i:s', strtotime($result[0]['change_date'])) : date('d.m.Y H:i:s', strtotime($result[0]['change_date']));
            }
            if(isset($result[0]['last_creation_date']) && $result[0]['last_creation_date']!='0000-00-00 00:00:00'){
             $last_creation =  $this->currentLang == 'en' ? date('Y.m.d H:i:s', strtotime($result[0]['last_creation_date'])) : date('d.m.Y H:i:s', strtotime($result[0]['last_creation_date']));
            }
            $offset = isset($result[0]['current_offset']) && is_numeric($result[0]['current_offset']) ? $result[0]['current_offset'] : 0;
            $items_count = isset($result[0]['items_count']) && is_numeric($result[0]['items_count']) ? $result[0]['items_count'] : 0;
            $progress = $offset > 0 && $items_count > 0 ? (int) (($offset / $items_count) * 100) : 0;
          }
          
          //category feeds
          $data['category_feeds'][$store['store_id']][$lang['code']][$currency_code] = array(
              'abs_path' => $category_abs_path,
              'path' => $category_path,
              'hash' => $category_hash,
              'cron_hash' => $category_cron_hash,
              'log_hash' => $data['store_logs'][$store['store_id']]['hash'],
              'exists' => $this->urlExists($category_path) ? 1 : 0,
              'status' => $status,
              'last_change' => $last_change,
              'last_creation' => $last_creation,
              'progress' => $progress
          );
        }
      }
    }

    return $data;
    
  }

  private function getHeurekaAvailabilityFeeds($data, $token) {
    
    foreach($data['stores'] as $store) {

          //heureka availability
          $heureka_availability_hash = md5('haf_' . $store['store_id']);
          $heureka_availability_cron_hash = md5($token . '_haf_' . $store['store_id']);
          $heureka_availability_path = $data['stores'][0]['url'] . 'mergado/haf_' . $heureka_availability_hash . '.xml';
          $heureka_availability_abs_path =  DIR_APPLICATION . '../' . 'mergado/haf_' . $heureka_availability_hash . '.xml';

          $status = '-';
          $last_change = '-';
          $last_creation = '-';
          $progress = '0';
          if($result = $this->model['generator']->getData($heureka_availability_hash)) {
            $status = isset($result[0]['status']) && $result[0]['status'] == 1 ? $result[0]['status'] : '-';
            if(isset($result[0]['change_date']) && $result[0]['change_date']!='0000-00-00 00:00:00') {
              $last_change = $this->currentLang == 'en' ? date('Y.m.d H:i:s', strtotime($result[0]['change_date'])) : date('d.m.Y H:i:s', strtotime($result[0]['change_date']));
            }
            if(isset($result[0]['last_creation_date']) && $result[0]['last_creation_date']!='0000-00-00 00:00:00'){
             $last_creation =  $this->currentLang == 'en' ? date('Y.m.d H:i:s', strtotime($result[0]['last_creation_date'])) : date('d.m.Y H:i:s', strtotime($result[0]['last_creation_date']));
            }
            $offset = isset($result[0]['current_offset']) && is_numeric($result[0]['current_offset']) ? $result[0]['current_offset'] : 0;
            $items_count = isset($result[0]['items_count']) && is_numeric($result[0]['items_count']) ? $result[0]['items_count'] : 0;
            $progress = $offset > 0 && $items_count > 0 ? (int) (($offset / $items_count) * 100) : 0;
          }
          
          //heureka availability feeds
          $data['heureka_availability_feeds'][$store['store_id']] = array(
              'abs_path' => $heureka_availability_abs_path,
              'path' => $heureka_availability_path,
              'hash' => $heureka_availability_hash,
              'cron_hash' => $heureka_availability_cron_hash,
              'log_hash' => $data['store_logs'][$store['store_id']]['hash'],
              'exists' => $this->urlExists($heureka_availability_path) ? 1 : 0,
              'status' => $status,
              'last_change' => $last_change,
              'last_creation' => $last_creation,
              'progress' => $progress
          );
    }

    return $data;
  }

  private function loadModels() {

    $this->load->model('setting/setting');
    $this->load->model('setting/store');
    $this->load->model('localisation/language');
    $this->load->model('localisation/currency');
    $this->load->model('extension/'. $this->extension_path . $this->extension_name);
    $this->load->model('extension/'. $this->extension_path . $this->extension_name . '_logger');
    $this->load->model('extension/'. $this->extension_path . $this->extension_name . '_generator');


    //custom models
    $model_name = "model_extension_" . $this->extension_type . "_" . $this->extension_name;
    $model_logger = "model_extension_" . $this->extension_type . "_" . $this->extension_name . '_logger';
    $model_generator = "model_extension_" . $this->extension_type . "_" . $this->extension_name . '_generator';

    $this->model['settings'] = $this->model_setting_setting;
    $this->model['currency'] = $this->model_localisation_currency;
    $this->model['language'] = $this->model_localisation_language;
    $this->model['store'] = $this->model_setting_store;
    $this->model['main'] = $this->{$model_name};
    $this->model['logger'] = $this->{$model_logger};
    $this->model['generator'] = $this->{$model_generator};

  }

  private function getStoreLogs($data, $token) {
    foreach($data['stores'] as $store) {
      
      //store logs
      $log_hash = md5($token . '_log_' . $store['store_id']);
      $data['store_logs'][$store['store_id']] = array(
        'hash' => $log_hash
      );

    }

    return $data;
  }

  private function urlExists($url) {
    if($url == NULL) return false;  
    
    $ch = curl_init($url);  
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);  
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
    $data = curl_exec($ch);  
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
    curl_close($ch); 

    if($httpcode >= 200 && $httpcode < 300) {  
      return true;  
    } else {  
      return false;  
    }
  }

  private function logChanges($data, $version) {

    $params = array();

    $params['general_settings']['extension_version'] = $version; 
    foreach($data as $key => $value) {

      if($key == $this->extension_fullname . '_status') {
        $params['general_settings']['extension_enabled'] = $value;
      }

      if($key == $this->extension_fullname . '_product_feed_limit') {
        $params['general_settings']['product_feed_limit'] = $value; 
      }

      if($key == $this->extension_fullname . '_product_feed_limit') {
        $params['general_settings']['product_feed_limit'] = $value; 
      }

      if($key == $this->extension_fullname . '_category_feed_limit') {
        $params['general_settings']['category_feed_limit'] = $value; 
      }

      if($key == $this->extension_fullname . '_logs') {
        $params['support_settings']['logs_enabled'] = $value;
      }

      if($key == $this->extension_fullname . '_debug_mode') {
        $params['support_settings']['debug_mode'] = $value;
      }

      //facebook pixel
      if($key == $this->extension_fullname . '_adverts_facebook_pixel_id') {
        $params['systems']['facebook_pixel_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_facebook_pixel_status') {
        $params['systems']['facebook_pixel_status'] = $value;
      }

      //google tag manager
      if($key == $this->extension_fullname . '_adverts_gtm_id') {
        $params['systems']['gtm_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_gtm_global_site_tracking_code') {
        $params['systems']['gtm_global_site_tracking_code'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_gtm_ecommerce_tracking') {
        $params['systems']['gtm_ecommerce_tracking'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_gtm_enhanced_ecommerce_tracking') {
        $params['systems']['gtm_enhanced_ecommerce_tracking'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_gtm_status') {
        $params['systems']['gtm_status'] = $value;
      }

      //google analytics
      if($key == $this->extension_fullname . '_adverts_ga_id') {
        $params['systems']['ga_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_ga_global_site_tracking_code') {
        $params['systems']['ga_global_site_tracking_code'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_ga_ecommerce_tracking') {
        $params['systems']['ga_ecommerce_tracking'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_ga_enhanced_ecommerce_tracking') {
        $params['systems']['ga_enhanced_ecommerce_tracking'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_ga_status') {
        $params['systems']['ga_status'] = $value;
      }

      //google ads conversion
      if($key == $this->extension_fullname . '_adverts_google_ads_code') {
        $params['systems']['google_ads_code'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_google_ads_label') {
        $params['systems']['google_ads_label'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_google_ads_status') {
        $params['systems']['google_ads_status'] = $value;
      }

      //google ads remarketing
      if($key == $this->extension_fullname . '_adverts_google_ads_remarketing') {
        $params['systems']['google_ads_remarketing'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_google_ads_remarketing_status') {
        $params['systems']['google_ads_remarketing_status'] = $value;
      }

      //google customer reviews
      if($key == $this->extension_fullname . '_adverts_gcr_id') {
        $params['systems']['gcr_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_gcr_order_time') {
        $params['systems']['gcr_order_time'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_gcr_delivery_days') {
        $params['systems']['gcr_delivery_days'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_gcr_position') {
        $params['systems']['gcr_position'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_gcr_status') {
        $params['systems']['gcr_status'] = $value;
      }

      //glami reviews
      if($key == $this->extension_fullname . '_adverts_glami_reviews_merchant_id') {
        $params['systems']['glami_reviews_merchant_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_glami_reviews_status') {
        $params['systems']['glami_reviews_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_glami_reviews_country') {
        $params['systems']['glami_reviews_country'] = $value;
      }

      //glami pixel
      if($key == $this->extension_fullname . '_adverts_glami_pixel_status') {
        $params['systems']['glami_pixel_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_glami_pixel_id') {
        $params['systems']['glami_pixel_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_glami_pixel_id_status') {
        $params['systems']['glami_pixel_id_status'] = $value;
      }

      //heureka verified by customers
      if($key == $this->extension_fullname . '_adverts_heureka_customer_cz_status') {
         $params['systems']['_adverts_heureka_customer_cz_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_cz_id') {
         $params['systems']['_adverts_heureka_customer_cz_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_cz_status') {
         $params['systems']['_adverts_heureka_customer_widget_cz_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_cz_position') {
         $params['systems']['_adverts_heureka_customer_widget_cz_position'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_cz_top_margin') {
         $params['systems']['_adverts_heureka_customer_widget_cz_top_margin'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_cz_mobile_status') {
         $params['systems']['_adverts_heureka_customer_widget_cz_mobile_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_cz_min_screen_width') {
         $params['systems']['_adverts_heureka_customer_widget_cz_min_screen_width'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_cz_id') {
         $params['systems']['_adverts_heureka_customer_widget_cz_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_sk_status') {
         $params['systems']['_adverts_heureka_customer_sk_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_sk_id') {
         $params['systems']['_adverts_heureka_customer_sk_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_sk_status') {
         $params['systems']['_adverts_heureka_customer_widget_sk_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_sk_position') {
         $params['systems']['_adverts_heureka_customer_widget_sk_position'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_sk_top_margin') {
         $params['systems']['_adverts_heureka_customer_widget_sk_top_margin'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_sk_mobile_status') {
         $params['systems']['_adverts_heureka_customer_widget_sk_mobile_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_sk_min_screen_width') {
         $params['systems']['_adverts_heureka_customer_widget_sk_min_screen_width'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_customer_widget_sk_id') {
         $params['systems']['_adverts_heureka_customer_widget_sk_id'] = $value;
      }

      //heureka conversion tracking
      if($key == $this->extension_fullname . '_adverts_heureka_conversion_cz_status') {
        $params['systems']['_adverts_heureka_conversion_cz_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_conversion_cz_id') {
        $params['systems']['_adverts_heureka_conversion_cz_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_conversion_sk_status') {
        $params['systems']['_adverts_heureka_conversion_sk_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_heureka_conversion_sk_id') {
        $params['systems']['_adverts_heureka_conversion_sk_id'] = $value;
      }

      //sklik conversion tracking
      if($key == $this->extension_fullname . '_adverts_sklik_conversion_status') {
          $params['systems']['_adverts_sklik_conversion_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_sklik_conversion_id') {
          $params['systems']['_adverts_sklik_conversion_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_sklik_conversion_value') {
          $params['systems']['_adverts_sklik_conversion_value'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_sklik_dph') {
          $params['systems']['_adverts_sklik_dph'] = $value;
      }

      //sklik retargeting
      if($key == $this->extension_fullname . '_adverts_sklik_retargeting_status') {
          $params['systems']['_adverts_sklik_retargeting_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_sklik_retargeting_id') {
          $params['systems']['_adverts_sklik_retargeting_id'] = $value;
      }

      //zbozi conversion tracking
      if($key == $this->extension_fullname . '_adverts_zbozi_status') {
          $params['systems']['_adverts_zbozi_sklik_status'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_zbozi_standard') {
          $params['systems']['_adverts_zbozi_standard'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_zbozi_shop_id') {
          $params['systems']['_adverts_zbozi_shop_id'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_zbozi_secret_key') {
          $params['systems']['_adverts_zbozi_secret_key'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_zbozi_debug') {
          $params['systems']['_adverts_zbozi_debug'] = $value;
      }
      if($key == $this->extension_fullname . '_adverts_zbozi_dph') {
          $params['systems']['_adverts_zbozi_dph'] = $value;
      }

    }

    $this->model['logger']->log('Saved Extension Settings', $params);
  }

  public function hideRating() {
    $interval = $this->request->post['interval'];
    $this->registry->set('mergado', new Mergado($this->registry));
    $mergado = $this->mergado;
    $this->load->model('setting/setting');
    if($interval == '30days' || $interval == 'now') {
        setcookie('mergadoRatingWidget', 1, time() + $mergado::RATING_WIDGET_COOKIE_LIFETIME, '/', $this->request->server['HTTP_HOST']);
        $this->model_setting_setting->editSettingValue($this->mergado->extension_fullname,$this->mergado->extension_fullname . '_already_rated',0);
        $this->model_setting_setting->editSettingValue($this->mergado->extension_fullname,$this->mergado->extension_fullname . '_rating_widget_showed',time());
    } else {
        $this->model_setting_setting->editSettingValue($this->mergado->extension_fullname,$this->mergado->extension_fullname . '_already_rated',1);
        $this->model_setting_setting->editSettingValue($this->mergado->extension_fullname,$this->mergado->extension_fullname . '_rating_widget_showed', time());
    }
  }

}
