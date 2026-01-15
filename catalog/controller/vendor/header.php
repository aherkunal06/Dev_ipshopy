<?php
class ControllerVendorHeader extends Controller {
	public function index() {
		$data['title'] = $this->document->getTitle();

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}
		
		/* 03 10 2019 */
		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}
		$data['customer2vendor'] = $this->config->get('vendor_customer2vendor');
		/* 03 10 2019 */
		
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$this->load->language('vendor/header');
		
		$this->load->model('vendor/vendor');
		$this->load->model('tool/image');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_order'] = $this->language->get('text_order');
		$data['text_processing_status'] = $this->language->get('text_processing_status');
		$data['text_complete_status'] = $this->language->get('text_complete_status');
		$data['text_return'] = $this->language->get('text_return');
		$data['text_customer'] = $this->language->get('text_customer');
		$data['text_online'] = $this->language->get('text_online');
		$data['text_approval'] = $this->language->get('text_approval');
		$data['text_product'] = $this->language->get('text_product');
		$data['text_stock'] = $this->language->get('text_stock');
		$data['text_review'] = $this->language->get('text_review');
		$data['text_affiliate'] = $this->language->get('text_affiliate');
		$data['text_store'] = $this->language->get('text_store');
		$data['text_front'] = $this->language->get('text_front');
		$data['text_help'] = $this->language->get('text_help');
		$data['text_homepage'] = $this->language->get('text_homepage');
		$data['text_documentation'] = $this->language->get('text_documentation');
		
		/* 3-12-2018 */
		$data['text_myprofile'] = $this->language->get('text_myprofile');
		$data['text_allsellers'] = $this->language->get('text_allsellers');
		/* 3-12-2018 */
		$data['text_support'] = $this->language->get('text_support');
		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('vendor/account', '', 'SSL'), $this->vendor->getFirstName(), $this->url->link('vendor/logout', '', 'SSL'));
		$data['text_logout'] = $this->language->get('text_logout');
		$data['seller_logged'] = $this->vendor->isLogged();
		$data['allseller'] = $this->url->link('vendor/allseller', '', 'SSL');
		if (!$this->vendor->isLogged()) {
			$data['logged'] = '';

			$data['home'] = $this->url->link('vendor/dashboard', '', 'SSL');
			
			
		} else {
			$data['logged'] = 'SSL';

// 			$data['home'] = $this->url->link('common/home', '', 'SSL');
			$data['home'] = $this->url->link('vendor/dashboard', '', 'SSL');
			
			$data['logout'] = $this->url->link('vendor/logout', '', 'SSL');
			$data['myprofile'] = $this->url->link('vendor/vendor_profile', 'vendor_id=' . $this->vendor->getId());
// 			$data['myprofile'] = $this->url->link('vendor/dashboard', 'vendor_id=' . $this->vendor->getId());
		}
		$seller_info = $this->model_vendor_vendor->getVendor($this->vendor->getId());
		$logo_info = $this->model_vendor_vendor->getVendorLogo($seller_info);
		if(!empty($logo_info['logo'])){
			$logos = $this->model_tool_image->resize($logo_info['logo'],50,50);
		} else {
			$logos = $this->model_tool_image->resize('placeholder.png',50,50);
		}
		$data['logos'] = $logos;
		if(isset($logo_info['name'])){
			$data['name'] = $logo_info['name'];
		}
		return $this->load->view('vendor/header', $data);
	}
}
