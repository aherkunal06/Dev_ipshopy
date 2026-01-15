<?php

class ControllerExtensionModuleMpsitemapfeed extends Controller {
	use mpsitemapfeed\trait_mpsitemapfeed;
	private $error = [];
	private $installed = [];

	private $events_code = 'module_mpsitemapfeed';

	private $events = [[
			'trigger' => 'admin/view/common/column_left/before',
			'action' => 'extension/module/mpsitemapfeed/getMenu'
		]];

	public function __construct($registry) {
		parent :: __construct($registry);
		$this->igniteTraitMpSitemapFeed($registry);

		/* OC2.3x event: view/after fix starts */
		if (VERSION > '2.2.0.0' && VERSION <= '2.3.0.2') {

			foreach ($this->events as $key => $value) {

				// oc2.3x common/menu.php controller not exists.
				if (strpos($value['trigger'], 'admin/') !== false) {
					continue;
				}

				$trigger_parts = explode('/', $value['trigger']);
				$tigger_end = end($trigger_parts);

				$str_part = 'catalog/view/';
				if (strpos($value['trigger'], 'catalog/view') !== false &&  $tigger_end === 'after') {
					$this->events[$key]['trigger'] = $str_part . '*/' . substr($value['trigger'], strlen($str_part));
				}
			}
		}
		/* OC2.3x event: view/after fix ends */
	}

	public function install() {
		$this->createEvents($this->events, $this->events_code);
	}

	public function uninstall() {
		$this->removeEventsByCode($this->events_code);
	}

	public function index() {
		$this->load->language($this->extension_path . 'module/mpsitemapfeed');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/stylesheet/mpsitemapfeed/stylesheet.css');

		// module events starts
		$data['text_disable_events'] = '';
		$data['disable_events'] = false;
		if ($this->user->hasPermission('modify', $this->extension_path . 'module/mpsitemapfeed')) {
			$this->createEvents($this->events, $this->events_code);
			$disable_events = $this->areEventsDisable($this->events_code);
			if ($disable_events) {
				$data['disable_events'] = true;
				$data['text_disable_events'] = $this->language->get('text_disable_events');
			}
		}
		// module events ends

		$this->load->model('setting/setting');

		$data['store_id'] = $store_id = 0;

		if (isset($this->request->get['store_id'])) {
			$data['store_id'] = $store_id = (int)$this->request->get['store_id'];
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('module_mpsitemapfeed', $this->request->post, $store_id);

			if (VERSION < '3.0.0.0') {
				$post = [];
				$post['mpsitemapfeed_status'] = $this->request->post['module_mpsitemapfeed_status'];
				$this->model_setting_setting->editSetting('mpsitemapfeed', $post, $store_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['stay_here']) && $this->request->post['stay_here'] == 1) {
				$this->response->redirect($this->url->link($this->extension_path . 'module/mpsitemapfeed', $this->token . '=' . $this->session->data[$this->token] . '&type=module', true));
			}

			$this->response->redirect($this->url->link($this->extension_page_path, $this->token . '=' . $this->session->data[$this->token] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['width'])) {
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
		}

		if (isset($this->error['caption_limit'])) {
			$data['error_caption_limit'] = $this->error['caption_limit'];
		} else {
			$data['error_caption_limit'] = '';
		}

		if (isset($this->error['limit'])) {
			$data['error_limit'] = $this->error['limit'];
		} else {
			$data['error_limit'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->token . '=' . $this->session->data[$this->token], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link($this->extension_page_path, $this->token . '=' . $this->session->data[$this->token] . '&type=module', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->extension_path . 'module/mpsitemapfeed', $this->token . '=' . $this->session->data[$this->token], true)
		];

		$data['action'] = $this->url->link($this->extension_path . 'module/mpsitemapfeed', $this->token . '=' . $this->session->data[$this->token] . '&store_id=' . $store_id, true);

		$data['cancel'] = $this->url->link($this->extension_page_path, $this->token . '=' . $this->session->data[$this->token] . '&type=module', true);

		$data['languages'] = $this->getLanguages();

		$this->load->model('setting/store');
		$stores = $this->model_setting_store->getStores();

		array_unshift($stores, [
			'store_id' => '0',
			'name' => strip_tags($this->language->get('text_default')),
			'url' => HTTP_CATALOG,
			'ssl' => HTTPS_CATALOG,
		]);
		$data['stores'] = [];
		foreach ($stores as $store) {
			if ($store['store_id'] == $store_id) {

				if ($this->request->server['HTTPS']) {
					$sitemap_url = $store['ssl'] . 'index.php?route=' . $this->extension_path . 'mpsitemapfeed/mpsitemapfeed';
				} else {
					$sitemap_url = $store['url'] . 'index.php?route=' . $this->extension_path . 'mpsitemapfeed/mpsitemapfeed';
				}
			}

			$data['stores'][] = [
				'store_id' => $store['store_id'],
				'name' => $store['name'],
				'href' => $this->url->link($this->extension_path . 'module/mpsitemapfeed', $this->token . '=' . $this->session->data[$this->token] . '&store_id=' . $store['store_id'], true),
				'sitemap_url' => $sitemap_url,
				];
		}

		$module_info = $this->model_setting_setting->getSetting('module_mpsitemapfeed', $store_id);

		if (isset($this->request->post['module_mpsitemapfeed_status'])) {
			$data['module_mpsitemapfeed_status'] = $this->request->post['module_mpsitemapfeed_status'];
		} else if (isset($module_info['module_mpsitemapfeed_status'])) {
			$data['module_mpsitemapfeed_status'] = $module_info['module_mpsitemapfeed_status'];
		} else {
			$data['module_mpsitemapfeed_status'] = 0;
		}

		if (isset($this->request->post['module_mpsitemapfeed_limit'])) {
			$data['module_mpsitemapfeed_limit'] = $this->request->post['module_mpsitemapfeed_limit'];
		} else if (isset($module_info['module_mpsitemapfeed_limit'])) {
			$data['module_mpsitemapfeed_limit'] = $module_info['module_mpsitemapfeed_limit'];
		} else {
			$data['module_mpsitemapfeed_limit'] = 1000;
		}

		if (isset($this->request->post['module_mpsitemapfeed_url'])) {
			$data['module_mpsitemapfeed_url'] = $this->request->post['module_mpsitemapfeed_url'];
		} else if (isset($module_info['module_mpsitemapfeed_url'])) {
			$data['module_mpsitemapfeed_url'] = $module_info['module_mpsitemapfeed_url'];
		} else {
			$data['module_mpsitemapfeed_url'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_product_status'])) {
			$data['module_mpsitemapfeed_product_status'] = $this->request->post['module_mpsitemapfeed_product_status'];
		} else if (isset($module_info['module_mpsitemapfeed_product_status'])) {
			$data['module_mpsitemapfeed_product_status'] = $module_info['module_mpsitemapfeed_product_status'];
		} else {
			$data['module_mpsitemapfeed_product_status'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_product_multilangurl'])) {
			$data['module_mpsitemapfeed_product_multilangurl'] = $this->request->post['module_mpsitemapfeed_product_multilangurl'];
		} else if (isset($module_info['module_mpsitemapfeed_product_multilangurl'])) {
			$data['module_mpsitemapfeed_product_multilangurl'] = $module_info['module_mpsitemapfeed_product_multilangurl'];
		} else {
			$data['module_mpsitemapfeed_product_multilangurl'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_product_frequency'])) {
			$data['module_mpsitemapfeed_product_frequency'] = $this->request->post['module_mpsitemapfeed_product_frequency'];
		} else if (isset($module_info['module_mpsitemapfeed_product_frequency'])) {
			$data['module_mpsitemapfeed_product_frequency'] = $module_info['module_mpsitemapfeed_product_frequency'];
		} else {
			$data['module_mpsitemapfeed_product_frequency'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_product_priority'])) {
			$data['module_mpsitemapfeed_product_priority'] = $this->request->post['module_mpsitemapfeed_product_priority'];
		} else if (isset($module_info['module_mpsitemapfeed_product_priority'])) {
			$data['module_mpsitemapfeed_product_priority'] = $module_info['module_mpsitemapfeed_product_priority'];
		} else {
			$data['module_mpsitemapfeed_product_priority'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_product_ids'])) {
			$mpsitemapfeed_product_ids = $this->request->post['module_mpsitemapfeed_product_ids'];
		} else if (isset($module_info['module_mpsitemapfeed_product_ids'])) {
			$mpsitemapfeed_product_ids = $module_info['module_mpsitemapfeed_product_ids'];
		} else {
			$mpsitemapfeed_product_ids = [];
		}

		$data['products'] = [];
		$this->load->model('catalog/product');
		foreach ($mpsitemapfeed_product_ids as $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info) {
				$data['products'][] = [
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name']
				];
			}
		}

		if (isset($this->request->post['module_mpsitemapfeed_category_status'])) {
			$data['module_mpsitemapfeed_category_status'] = $this->request->post['module_mpsitemapfeed_category_status'];
		} else if (isset($module_info['module_mpsitemapfeed_category_status'])) {
			$data['module_mpsitemapfeed_category_status'] = $module_info['module_mpsitemapfeed_category_status'];
		} else {
			$data['module_mpsitemapfeed_category_status'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_category_multilangurl'])) {
			$data['module_mpsitemapfeed_category_multilangurl'] = $this->request->post['module_mpsitemapfeed_category_multilangurl'];
		} else if (isset($module_info['module_mpsitemapfeed_category_multilangurl'])) {
			$data['module_mpsitemapfeed_category_multilangurl'] = $module_info['module_mpsitemapfeed_category_multilangurl'];
		} else {
			$data['module_mpsitemapfeed_category_multilangurl'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_category_frequency'])) {
			$data['module_mpsitemapfeed_category_frequency'] = $this->request->post['module_mpsitemapfeed_category_frequency'];
		} else if (isset($module_info['module_mpsitemapfeed_category_frequency'])) {
			$data['module_mpsitemapfeed_category_frequency'] = $module_info['module_mpsitemapfeed_category_frequency'];
		} else {
			$data['module_mpsitemapfeed_category_frequency'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_category_priority'])) {
			$data['module_mpsitemapfeed_category_priority'] = $this->request->post['module_mpsitemapfeed_category_priority'];
		} else if (isset($module_info['module_mpsitemapfeed_category_priority'])) {
			$data['module_mpsitemapfeed_category_priority'] = $module_info['module_mpsitemapfeed_category_priority'];
		} else {
			$data['module_mpsitemapfeed_category_priority'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_category_ids'])) {
			$mpsitemapfeed_category_ids = $this->request->post['module_mpsitemapfeed_category_ids'];
		} else if (isset($module_info['module_mpsitemapfeed_category_ids'])) {
			$mpsitemapfeed_category_ids = $module_info['module_mpsitemapfeed_category_ids'];
		} else {
			$mpsitemapfeed_category_ids = [];
		}

		$data['categories'] = [];
		$this->load->model('catalog/category');
		foreach ($mpsitemapfeed_category_ids as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['categories'][] = [
					'category_id' => $category_info['category_id'],
					'name'       => $category_info['name']
				];
			}
		}

		if (isset($this->request->post['module_mpsitemapfeed_manufacturer_status'])) {
			$data['module_mpsitemapfeed_manufacturer_status'] = $this->request->post['module_mpsitemapfeed_manufacturer_status'];
		} else if (isset($module_info['module_mpsitemapfeed_manufacturer_status'])) {
			$data['module_mpsitemapfeed_manufacturer_status'] = $module_info['module_mpsitemapfeed_manufacturer_status'];
		} else {
			$data['module_mpsitemapfeed_manufacturer_status'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_manufacturer_multilangurl'])) {
			$data['module_mpsitemapfeed_manufacturer_multilangurl'] = $this->request->post['module_mpsitemapfeed_manufacturer_multilangurl'];
		} else if (isset($module_info['module_mpsitemapfeed_manufacturer_multilangurl'])) {
			$data['module_mpsitemapfeed_manufacturer_multilangurl'] = $module_info['module_mpsitemapfeed_manufacturer_multilangurl'];
		} else {
			$data['module_mpsitemapfeed_manufacturer_multilangurl'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_manufacturer_frequency'])) {
			$data['module_mpsitemapfeed_manufacturer_frequency'] = $this->request->post['module_mpsitemapfeed_manufacturer_frequency'];
		} else if (isset($module_info['module_mpsitemapfeed_manufacturer_frequency'])) {
			$data['module_mpsitemapfeed_manufacturer_frequency'] = $module_info['module_mpsitemapfeed_manufacturer_frequency'];
		} else {
			$data['module_mpsitemapfeed_manufacturer_frequency'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_manufacturer_priority'])) {
			$data['module_mpsitemapfeed_manufacturer_priority'] = $this->request->post['module_mpsitemapfeed_manufacturer_priority'];
		} else if (isset($module_info['module_mpsitemapfeed_manufacturer_priority'])) {
			$data['module_mpsitemapfeed_manufacturer_priority'] = $module_info['module_mpsitemapfeed_manufacturer_priority'];
		} else {
			$data['module_mpsitemapfeed_manufacturer_priority'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_manufacturer_ids'])) {
			$mpsitemapfeed_manufacturer_ids = $this->request->post['module_mpsitemapfeed_manufacturer_ids'];
		} else if (isset($module_info['module_mpsitemapfeed_manufacturer_ids'])) {
			$mpsitemapfeed_manufacturer_ids = $module_info['module_mpsitemapfeed_manufacturer_ids'];
		} else {
			$mpsitemapfeed_manufacturer_ids = [];
		}

		$data['manufacturers'] = [];
		$this->load->model('catalog/manufacturer');
		foreach ($mpsitemapfeed_manufacturer_ids as $manufacturer_id) {
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

			if ($manufacturer_info) {
				$data['manufacturers'][] = [
					'manufacturer_id' => $manufacturer_info['manufacturer_id'],
					'name'       => $manufacturer_info['name']
				];
			}
		}

		if (isset($this->request->post['module_mpsitemapfeed_information_status'])) {
			$data['module_mpsitemapfeed_information_status'] = $this->request->post['module_mpsitemapfeed_information_status'];
		} else if (isset($module_info['module_mpsitemapfeed_information_status'])) {
			$data['module_mpsitemapfeed_information_status'] = $module_info['module_mpsitemapfeed_information_status'];
		} else {
			$data['module_mpsitemapfeed_information_status'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_information_multilangurl'])) {
			$data['module_mpsitemapfeed_information_multilangurl'] = $this->request->post['module_mpsitemapfeed_information_multilangurl'];
		} else if (isset($module_info['module_mpsitemapfeed_information_multilangurl'])) {
			$data['module_mpsitemapfeed_information_multilangurl'] = $module_info['module_mpsitemapfeed_information_multilangurl'];
		} else {
			$data['module_mpsitemapfeed_information_multilangurl'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_information_frequency'])) {
			$data['module_mpsitemapfeed_information_frequency'] = $this->request->post['module_mpsitemapfeed_information_frequency'];
		} else if (isset($module_info['module_mpsitemapfeed_information_frequency'])) {
			$data['module_mpsitemapfeed_information_frequency'] = $module_info['module_mpsitemapfeed_information_frequency'];
		} else {
			$data['module_mpsitemapfeed_information_frequency'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_information_priority'])) {
			$data['module_mpsitemapfeed_information_priority'] = $this->request->post['module_mpsitemapfeed_information_priority'];
		} else if (isset($module_info['module_mpsitemapfeed_information_priority'])) {
			$data['module_mpsitemapfeed_information_priority'] = $module_info['module_mpsitemapfeed_information_priority'];
		} else {
			$data['module_mpsitemapfeed_information_priority'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_information_ids'])) {
			$mpsitemapfeed_information_ids = $this->request->post['module_mpsitemapfeed_information_ids'];
		} else if (isset($module_info['module_mpsitemapfeed_information_ids'])) {
			$mpsitemapfeed_information_ids = $module_info['module_mpsitemapfeed_information_ids'];
		} else {
			$mpsitemapfeed_information_ids = [];
		}

		$data['informations'] = [];
		$this->load->model('catalog/information');
		foreach ($mpsitemapfeed_information_ids as $module_information_id) {
			$information_info = $this->getInformation($module_information_id);

			if ($information_info) {
				$data['informations'][] = [
					'information_id' => $information_info['information_id'],
					'name'       => $information_info['title']
				];
			}
		}

		if (isset($this->request->post['module_mpsitemapfeed_image_status'])) {
			$data['module_mpsitemapfeed_image_status'] = $this->request->post['module_mpsitemapfeed_image_status'];
		} else if (isset($module_info['module_mpsitemapfeed_image_status'])) {
			$data['module_mpsitemapfeed_image_status'] = $module_info['module_mpsitemapfeed_image_status'];
		} else {
			$data['module_mpsitemapfeed_image_status'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_title_status'])) {
			$data['module_mpsitemapfeed_title_status'] = $this->request->post['module_mpsitemapfeed_title_status'];
		} else if (isset($module_info['module_mpsitemapfeed_title_status'])) {
			$data['module_mpsitemapfeed_title_status'] = $module_info['module_mpsitemapfeed_title_status'];
		} else {
			$data['module_mpsitemapfeed_title_status'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_caption_status'])) {
			$data['module_mpsitemapfeed_caption_status'] = $this->request->post['module_mpsitemapfeed_caption_status'];
		} else if (isset($module_info['module_mpsitemapfeed_caption_status'])) {
			$data['module_mpsitemapfeed_caption_status'] = $module_info['module_mpsitemapfeed_caption_status'];
		} else {
			$data['module_mpsitemapfeed_caption_status'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_caption_limit'])) {
			$data['module_mpsitemapfeed_caption_limit'] = $this->request->post['module_mpsitemapfeed_caption_limit'];
		} else if (isset($module_info['module_mpsitemapfeed_caption_limit'])) {
			$data['module_mpsitemapfeed_caption_limit'] = $module_info['module_mpsitemapfeed_caption_limit'];
		} else {
			$data['module_mpsitemapfeed_caption_limit'] = '100';
		}

		if (isset($this->request->post['module_mpsitemapfeed_resize_image'])) {
			$data['module_mpsitemapfeed_resize_image'] = $this->request->post['module_mpsitemapfeed_resize_image'];
		} else if (isset($module_info['module_mpsitemapfeed_resize_image'])) {
			$data['module_mpsitemapfeed_resize_image'] = $module_info['module_mpsitemapfeed_resize_image'];
		} else {
			$data['module_mpsitemapfeed_resize_image'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_image_width'])) {
			$data['module_mpsitemapfeed_image_width'] = $this->request->post['module_mpsitemapfeed_image_width'];
		} else if (isset($module_info['module_mpsitemapfeed_image_width'])) {
			$data['module_mpsitemapfeed_image_width'] = $module_info['module_mpsitemapfeed_image_width'];
		} else {
			$data['module_mpsitemapfeed_image_width'] = '100';
		}

		if (isset($this->request->post['module_mpsitemapfeed_image_height'])) {
			$data['module_mpsitemapfeed_image_height'] = $this->request->post['module_mpsitemapfeed_image_height'];
		} else if (isset($module_info['module_mpsitemapfeed_image_height'])) {
			$data['module_mpsitemapfeed_image_height'] = $module_info['module_mpsitemapfeed_image_height'];
		} else {
			$data['module_mpsitemapfeed_image_height'] = '100';
		}

		if (isset($this->request->post['module_mpsitemapfeed_custom_link_status'])) {
			$data['module_mpsitemapfeed_custom_link_status'] = $this->request->post['module_mpsitemapfeed_custom_link_status'];
		} else if (isset($module_info['module_mpsitemapfeed_custom_link_status'])) {
			$data['module_mpsitemapfeed_custom_link_status'] = $module_info['module_mpsitemapfeed_custom_link_status'];
		} else {
			$data['module_mpsitemapfeed_custom_link_status'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_custom_link_frequency'])) {
			$data['module_mpsitemapfeed_custom_link_frequency'] = $this->request->post['module_mpsitemapfeed_custom_link_frequency'];
		} else if (isset($module_info['module_mpsitemapfeed_custom_link_frequency'])) {
			$data['module_mpsitemapfeed_custom_link_frequency'] = $module_info['module_mpsitemapfeed_custom_link_frequency'];
		} else {
			$data['module_mpsitemapfeed_custom_link_frequency'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_custom_link_priority'])) {
			$data['module_mpsitemapfeed_custom_link_priority'] = $this->request->post['module_mpsitemapfeed_custom_link_priority'];
		} else if (isset($module_info['module_mpsitemapfeed_custom_link_priority'])) {
			$data['module_mpsitemapfeed_custom_link_priority'] = $module_info['module_mpsitemapfeed_custom_link_priority'];
		} else {
			$data['module_mpsitemapfeed_custom_link_priority'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_custom_link'])) {
			$data['module_mpsitemapfeed_custom_links'] = $this->request->post['module_mpsitemapfeed_custom_link'];
		} else if (isset($module_info['module_mpsitemapfeed_custom_link'])) {
			$data['module_mpsitemapfeed_custom_links'] = $module_info['module_mpsitemapfeed_custom_link'];
		} else {
			$data['module_mpsitemapfeed_custom_links'] = [];
		}

		if (isset($this->request->post['module_mpsitemapfeed_j3_blogpost_status'])) {
			$data['module_mpsitemapfeed_j3_blogpost_status'] = $this->request->post['module_mpsitemapfeed_j3_blogpost_status'];
		} else if (isset($module_info['module_mpsitemapfeed_j3_blogpost_status'])) {
			$data['module_mpsitemapfeed_j3_blogpost_status'] = $module_info['module_mpsitemapfeed_j3_blogpost_status'];
		} else {
			$data['module_mpsitemapfeed_j3_blogpost_status'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_j3_blogpost_multilangurl'])) {
			$data['module_mpsitemapfeed_j3_blogpost_multilangurl'] = $this->request->post['module_mpsitemapfeed_j3_blogpost_multilangurl'];
		} else if (isset($module_info['module_mpsitemapfeed_j3_blogpost_multilangurl'])) {
			$data['module_mpsitemapfeed_j3_blogpost_multilangurl'] = $module_info['module_mpsitemapfeed_j3_blogpost_multilangurl'];
		} else {
			$data['module_mpsitemapfeed_j3_blogpost_multilangurl'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_j3_blogpost_frequency'])) {
			$data['module_mpsitemapfeed_j3_blogpost_frequency'] = $this->request->post['module_mpsitemapfeed_j3_blogpost_frequency'];
		} else if (isset($module_info['module_mpsitemapfeed_j3_blogpost_frequency'])) {
			$data['module_mpsitemapfeed_j3_blogpost_frequency'] = $module_info['module_mpsitemapfeed_j3_blogpost_frequency'];
		} else {
			$data['module_mpsitemapfeed_j3_blogpost_frequency'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_j3_blogpost_priority'])) {
			$data['module_mpsitemapfeed_j3_blogpost_priority'] = $this->request->post['module_mpsitemapfeed_j3_blogpost_priority'];
		} else if (isset($module_info['module_mpsitemapfeed_j3_blogpost_priority'])) {
			$data['module_mpsitemapfeed_j3_blogpost_priority'] = $module_info['module_mpsitemapfeed_j3_blogpost_priority'];
		} else {
			$data['module_mpsitemapfeed_j3_blogpost_priority'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_j3_blogpost_ids'])) {
			$mpsitemapfeed_blogpost_ids = $this->request->post['module_mpsitemapfeed_j3_blogpost_ids'];
		} else if (isset($module_info['module_mpsitemapfeed_j3_blogpost_ids'])) {
			$mpsitemapfeed_blogpost_ids = $module_info['module_mpsitemapfeed_j3_blogpost_ids'];
		} else {
			$mpsitemapfeed_blogpost_ids = [];
		}

		$data['blogposts'] = [];

		if (($this->config->get('config_theme') == 'theme_journal3') || ($this->config->get('config_theme') == 'journal3')) {
			$this->load->model('journal3/blog_post');

			foreach ($mpsitemapfeed_blogpost_ids as $post_id) {
				$post_info = $this->model_journal3_blog_post->get($post_id);

				if ($post_info) {
					$data['blogposts'][] = [
						'post_id' => $post_id,
						'name'       => $post_info['name']['lang_'. $this->config->get('config_language_id')]
					];
				}
			}
		}

		if (isset($this->request->post['module_mpsitemapfeed_j3_blogcategory_status'])) {
			$data['module_mpsitemapfeed_j3_blogcategory_status'] = $this->request->post['module_mpsitemapfeed_j3_blogcategory_status'];
		} else if (isset($module_info['module_mpsitemapfeed_j3_blogcategory_status'])) {
			$data['module_mpsitemapfeed_j3_blogcategory_status'] = $module_info['module_mpsitemapfeed_j3_blogcategory_status'];
		} else {
			$data['module_mpsitemapfeed_j3_blogcategory_status'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_j3_blogcategory_multilangurl'])) {
			$data['module_mpsitemapfeed_j3_blogcategory_multilangurl'] = $this->request->post['module_mpsitemapfeed_j3_blogcategory_multilangurl'];
		} else if (isset($module_info['module_mpsitemapfeed_j3_blogcategory_multilangurl'])) {
			$data['module_mpsitemapfeed_j3_blogcategory_multilangurl'] = $module_info['module_mpsitemapfeed_j3_blogcategory_multilangurl'];
		} else {
			$data['module_mpsitemapfeed_j3_blogcategory_multilangurl'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_j3_blogcategory_frequency'])) {
			$data['module_mpsitemapfeed_j3_blogcategory_frequency'] = $this->request->post['module_mpsitemapfeed_j3_blogcategory_frequency'];
		} else if (isset($module_info['module_mpsitemapfeed_j3_blogcategory_frequency'])) {
			$data['module_mpsitemapfeed_j3_blogcategory_frequency'] = $module_info['module_mpsitemapfeed_j3_blogcategory_frequency'];
		} else {
			$data['module_mpsitemapfeed_j3_blogcategory_frequency'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_j3_blogcategory_priority'])) {
			$data['module_mpsitemapfeed_j3_blogcategory_priority'] = $this->request->post['module_mpsitemapfeed_j3_blogcategory_priority'];
		} else if (isset($module_info['module_mpsitemapfeed_j3_blogcategory_priority'])) {
			$data['module_mpsitemapfeed_j3_blogcategory_priority'] = $module_info['module_mpsitemapfeed_j3_blogcategory_priority'];
		} else {
			$data['module_mpsitemapfeed_j3_blogcategory_priority'] = '';
		}

		if (isset($this->request->post['module_mpsitemapfeed_j3_blogcategory_ids'])) {
			$mpsitemapfeed_blogcategory_ids = $this->request->post['module_mpsitemapfeed_j3_blogcategory_ids'];
		} else if (isset($module_info['module_mpsitemapfeed_j3_blogcategory_ids'])) {
			$mpsitemapfeed_blogcategory_ids = $module_info['module_mpsitemapfeed_j3_blogcategory_ids'];
		} else {
			$mpsitemapfeed_blogcategory_ids = [];
		}

		$data['blogcategories'] = [];
		if (($this->config->get('config_theme') == 'theme_journal3') || ($this->config->get('config_theme') == 'journal3')) {
			$this->load->model('journal3/blog_category');
			foreach ($mpsitemapfeed_blogcategory_ids as $category_id) {
				$blogcategory_info = $this->model_journal3_blog_category->get($category_id);

				if ($blogcategory_info) {
					$data['blogcategories'][] = [
						'category_id' => $category_id,
						'name'       => $blogcategory_info['name']['lang_'. $this->config->get('config_language_id')]
					];
				}
			}
		}

		$data['j3_active'] = false;
		if (($this->config->get('config_theme') == 'theme_journal3') || ($this->config->get('config_theme') == 'journal3')) {
			$data['j3_active'] = true;
		}

		$data['config_theme'] = $this->config->get('config_theme');

		$data['priorities'] = $this->getPriorities();
		$data['frequencies'] = $this->getFrequencies();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->viewLoad($this->extension_path . 'module/mpsitemapfeed', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->extension_path . 'module/mpsitemapfeed')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->request->post['module_mpsitemapfeed_image_status']) {
			if ($this->request->post['module_mpsitemapfeed_resize_image']) {
				if (!$this->request->post['module_mpsitemapfeed_image_width']) {
					$this->error['width'] = $this->language->get('error_width');
				}

				if (!$this->request->post['module_mpsitemapfeed_image_height']) {
					$this->error['height'] = $this->language->get('error_height');
				}
			}
		}

		if ($this->request->post['module_mpsitemapfeed_limit'] < 100 || $this->request->post['module_mpsitemapfeed_limit'] > 50000) {
			$this->error['limit'] = $this->language->get('error_limit');
		}

		return !$this->error;
	}

	protected function accessValidate() {
		if (!$this->user->hasPermission('access', $this->extension_path . 'module/mpsitemapfeed')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function moduleIsInstalled($module, $type = 'module') {
		if (empty($this->installed[$type])) {
			$this->load->model($this->model_file['extension/extension']['path']);
			$this->installed[$type] = $this->{$this->model_file['extension/extension']['obj']}->getInstalled($type);
		}

		return in_array($module, $this->installed[$type]);
	}

	// module events starts
	public function activateEvents() {
		$json = [];

		if (($this->request->server['REQUEST_METHOD'] == 'GET') && $this->accessValidate() && isset($this->request->get['ae']) && $this->request->get['ae'] == '1') {

			$this->load->language($this->extension_path . 'module/mpsitemapfeed');
			$this->load->model($this->model_file['extension/event']['path']);

			$disable_events = $this->areEventsDisable($this->events_code);

			if ($disable_events) {
				foreach ($disable_events as $event_id) {
					$this->{$this->model_file['extension/event']['obj']}->enableEvent($event_id);
				}

				$json['success'] = $this->language->get('text_success_activate_events');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	// module events ends

	//'trigger' => 'admin/view/common/column_left/before',
	public function getMenu(&$route, &$data) {
		$menu = [];
		$this->load->language($this->extension_path . 'mpsitemapfeed/mpsitemapfeed_menu');

		if ($this->user->hasPermission('access', 'extension/module/mpsitemapfeed')) {
			$menu = [
				'name'	   => $this->labelEnableDisable((int)$this->config->get('module_mpsitemapfeed_status')) . ' ' . $this->language->get('text_mpsitemapfeed'),
				'href'     => $this->url->link('extension/module/mpsitemapfeed', $this->token . '=' . $this->session->data[$this->token], true),
				'children' => []
			];
		}

		if ($menu && $this->moduleIsInstalled('mpsitemapfeed')) {
			$data['menus'] = array_map(function($v) use ($menu) {
				if ($v['id'] == 'menu-extension') {
					$v['children'] = array_merge($v['children'], [$menu]);
				}
				return $v;
			}, $data['menus']);
		}
	}

	public function getPriorities() {
		return ['0.1', '0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9', '1.0'];
	}

	public function getFrequencies() {
		$frequency = [];

		$frequency[] = [
			'key' => 'always',
			'text' => $this->language->get('text_always'),
		];

		$frequency[] = [
			'key' => 'hourly',
			'text' => $this->language->get('text_hourly'),
		];

		$frequency[] = [
			'key' => 'daily',
			'text' => $this->language->get('text_daily'),
		];

		$frequency[] = [
			'key' => 'weekly',
			'text' => $this->language->get('text_weekly'),
		];

		$frequency[] = [
			'key' => 'monthly',
			'text' => $this->language->get('text_monthly'),
		];

		$frequency[] = [
			'key' => 'yearly',
			'text' => $this->language->get('text_yearly'),
		];

		$frequency[] = [
			'key' => 'never',
			'text' => $this->language->get('text_never'),
		];

		return $frequency;
	}

	public function informationAutocomplete() {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/information');

			$filter_data = [
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			];

			$results = $this->model_catalog_information->getInformations($filter_data);

			foreach ($results as $result) {
				$json[] = [
					'information_id' => $result['information_id'],
					'name'            => strip_tags(html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'))
				];
			}
		}

		$sort_order = [];

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function blogpostAutocomplete() {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('journal3/blog_post');

			$filter_data = [
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			];

			$results = $this->model_journal3_blog_post->all($filter_data);

			foreach ($results['items'] as $result) {
				$json[] = [
					'post_id' => $result['id'],
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				];
			}
		}

		$sort_order = [];

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function blogcategoryAutocomplete() {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('journal3/blog_category');

			$filter_data = [
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			];

			$results = $this->model_journal3_blog_category->all($filter_data);

			foreach ($results['items'] as $result) {
				$json[] = [
					'category_id' => $result['id'],
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				];
			}
		}

		$sort_order = [];

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getInformation($information_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.information_id = '" . (int)$information_id . "'");

		return $query->row;
	}

	// model like functions starts
	public function areEventsDisable($code) {
		$disable_events = [];

		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return $disable_events;
		}

		// get events from db
		$query = $this->db->query("SELECT DISTINCT `event_id` FROM `" . DB_PREFIX . "event` WHERE `code`='" . $this->db->escape($code) . "' AND `status`=0");

		foreach ($query->rows as $key => $value) {
			$disable_events[] = $value['event_id'];
		}

		return $disable_events;
	}

	public function removeEventsByCode($code) {
		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return;
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code`='" . $this->db->escape($code) . "'");
	}

	public function removeEvent($event_id) {
		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return;
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `event_id`='" . (int)$event_id . "'");
	}

	public function createEvents($events, $code) {

		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return;
		}

		$this->load->model($this->model_file['extension/event']['path']);
		$defaults = [
			'status' => 1,
			'sort_order' => 0,
			'description' => '',
		];

		// get events from db
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "event` WHERE `code`='" . $this->db->escape($code) . "'");

		$db_events = [];
		foreach ($query->rows as $key => $value) {
			$triact = "{$value['trigger']}==={$value['action']}";
			$db_events[] = $triact;
		}

		$removed_events_in_db = [];
		$trion = [];
		foreach ($events as $key => $event) {
			$triact = "{$event['trigger']}==={$event['action']}";
			$trion[] = $triact;
			if (!in_array($triact, $db_events)) {
				$removed_events_in_db[] = $event;
			}
		}

		// non required events present in database.
		$non_required_events = [];
		foreach ($query->rows as $key => $value) {
			$triact = "{$value['trigger']}==={$value['action']}";
			if (!in_array($triact, $trion)) {
				$non_required_events[] = $value;
			}
		}

		// delete non required events from database
		foreach ($non_required_events as $key => $value) {
			$this->removeEvent($value['event_id']);
		}

		foreach ($removed_events_in_db as $event) {

			// add default keys in array
			foreach ($defaults as $key => $value) {
				if (!isset($event[$key])) {
					$event[$key] = $value;
				}
			}

			$this->{$this->model_file['extension/event']['obj']}->addEvent($code, $event['trigger'], $event['action'], $event['status'], $event['sort_order']);
		}
	}
	// model like functions ends

}