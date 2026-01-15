<?php
class ControllerVendorStore extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('vendor/store');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/store');

		$this->getList();
	}

	public function add() {
		$this->load->language('vendor/store');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/store');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') 	) {
			$this->model_vendor_store->addStore($this->request->post);
			//print_r($this->request->post);die();
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('vendor/store');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/store');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_store->editStore($this->request->get['vs_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('vendor/store');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/store');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $vs_id) {
				$this->model_vendor_store->deleteStore($vs_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('vendor/store/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('vendor/store/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['stores'] = array();

		$filter_data = array(
			
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$this->load->model('vendor/vendor');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');
		
		$store_total = $this->model_vendor_store->getTotalStore($filter_data);

		$results = $this->model_vendor_store->getStores($filter_data);

		foreach ($results as $result) {
			
			$vendors = $this->model_vendor_vendor->getVendor($result['vendor_id']);
			if(isset($vendors['firstname'])){
				$vname = $vendors['firstname'];
			} else {
				$vname ='';
			}
			
			$country_info = $this->model_localisation_country->getCountry($result['country_id']);
			if(isset($country_info['name'])) {
				$mcountry = $country_info['name'];			
			} else {
				$mcountry='';			
			}
			
			$zone_info = $this->model_localisation_zone->getZone($result['zone_id']);
			if(isset($zone_info['name'])) {
				$mzone = $zone_info['name'];
			} else {
				$mzone='';			
			}
			
			$data['stores'][] = array(
				'vs_id'       => $result['vs_id'],
				'name'     	  => $result['name'],
				'vname'       => $vname,
				'mcountry'	  => $mcountry,
				'mzone'		  => $mzone,
				'edit'        => $this->url->link('vendor/store/edit', 'user_token=' . $this->session->data['user_token'] . '&vs_id=' . $result['vs_id'] . $url, true)
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['user_token'] = $this->session->data['user_token'];
		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['text_enable'] = $this->language->get('text_enable');
		$data['text_disable'] = $this->language->get('text_disable');
		$data['text_select'] = $this->language->get('text_select');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_vendorname'] = $this->language->get('column_vendorname');
		$data['column_country'] = $this->language->get('column_country');
		$data['column_zone'] = $this->language->get('column_zone');
		
		
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_action'] = $this->language->get('column_action');
		
		$data['entry_name'] = $this->language->get('entry_name');	
		
		
		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_filter'] = $this->language->get('button_filter');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_vendorname'] = $this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . '&sort=vendorname' . $url, true);
		$data['sort_country'] = $this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . '&sort=country' . $url, true);
		$data['sort_zone'] = $this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . '&sort=zone' . $url, true);
		$data['sort_sort_order'] = $this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);

		$url = '';
		
		if (isset($this->request->get['name'])) {
			$url .= '&sort=' . $this->request->get['name'];
		}
		
		if (isset($this->request->get['vendorname'])) {
			$url .= '&vendorname=' . $this->request->get['vendorname'];
		}
		
		if (isset($this->request->get['country'])) {
			$url .= '&country=' . $this->request->get['country'];
		}
		
		if (isset($this->request->get['zone'])) {
			$url .= '&zone=' . $this->request->get['zone'];
		}
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $store_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($store_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($store_total - $this->config->get('config_limit_admin'))) ? $store_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $store_total, ceil($store_total / $this->config->get('config_limit_admin')));
		
		$data['sort'] 			  = $sort;
		$data['order'] 			  = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		
		$this->response->setOutput($this->load->view('vendor/store_list', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['manufacturer_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_percent'] = $this->language->get('text_percent');
		$data['text_amount'] = $this->language->get('text_amount');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_enable'] = $this->language->get('text_enable');
		$data['text_disable'] = $this->language->get('text_disable');
		
		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_data'] = $this->language->get('tab_data');
		$data['tab_commission'] = $this->language->get('tab_commission');
		
		$data['column_name'] = $this->language->get('column_name');
		$data['column_lastname'] = $this->language->get('column_lastname');
		
		//// general tab ////
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_shipping_policy'] = $this->language->get('entry_shipping_policy');
		$data['entry_return_policy'] = $this->language->get('entry_return_policy');
		$data['entry_meta_keyword'] = $this->language->get('entry_meta_keyword');
		$data['entry_meta_description'] = $this->language->get('entry_meta_description');
		//// general tab ////
		
		//// data tab ////
		$data['entry_vendorname'] = $this->language->get('entry_vendorname');
		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_phone'] = $this->language->get('entry_phone');
		$data['entry_address'] = $this->language->get('entry_address');
		$data['entry_city'] = $this->language->get('entry_city');
		$data['entry_country'] = $this->language->get('entry_country');
		$data['entry_zone'] = $this->language->get('entry_zone');
		$data['entry_postcode'] = $this->language->get('entry_postcode');
		$data['entry_bank_detail'] = $this->language->get('entry_bank_detail');
		$data['entry_tax_number'] = $this->language->get('entry_tax_number');
		$data['entry_shipping_charge'] = $this->language->get('entry_shipping_charge');
		$data['entry_url'] = $this->language->get('entry_url');
		$data['entry_banner'] = $this->language->get('entry_banner');
		$data['entry_logo'] = $this->language->get('entry_logo');
		$data['entry_about'] = $this->language->get('entry_about');
		////data tab ////
		$data['entry_commission'] = $this->language->get('entry_commission');
		

		$data['help_keyword'] = $this->language->get('help_keyword');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
		
		if (isset($this->error['vendorname'])) {
			$data['error_vendorname'] = $this->error['vendorname'];
		} else {
			$data['error_vendorname'] = '';
		}
		
		if (isset($this->error['phone'])) {
			$data['error_phone'] = $this->error['phone'];
		} else {
			$data['error_phone'] = '';
		}
		
		if (isset($this->error['address'])) {
			$data['error_address'] = $this->error['address'];
		} else {
			$data['error_address'] = '';
		}
		
		
		if (isset($this->error['city'])) {
			$data['error_city'] = $this->error['city'];
		} else {
			$data['error_city'] = '';
		}
		
		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}
		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = (int)$this->request->post['country_id'];
		} elseif (isset($this->session->data['shipping_address']['country_id'])) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}
		
		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = (int)$this->request->post['zone_id'];
		} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
			$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}
		
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['vs_id'])) {
			$data['action'] = $this->url->link('vendor/store/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('vendor/store/edit', 'user_token=' . $this->session->data['user_token'] . '&vs_id=' . $this->request->get['vs_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('vendor/store', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['vs_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$store_info=$this->model_vendor_store->getStore($this->request->get['vs_id']);
			
		}
		
		//print_r($store_info);die();
		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->request->post['store_description'])) {
			$data['store_description'] = $this->request->post['store_description'];
		} elseif (isset($store_info)) {
			$data['store_description'] = $this->model_vendor_store->getVendorStoreDescriptions($this->request->get['vs_id']);
		} else {
			$data['store_description'] = array();
		}
		
		if (isset($this->request->post['vendor_id'])) {
			$data['vendor_id'] = $this->request->post['vendor_id'];
		} elseif (isset($store_info['vendor_id'])){
			$data['vendor_id'] = $store_info['vendor_id'];
		} else {
			$data['vendor_id'] = '';
		}
		
		if(!empty($data['vendor_id'])){	
			$this->load->model('vendor/vendor');
			$vendor_info=$this->model_vendor_vendor->getVendor($data['vendor_id']);
			$data['vendor']=$vendor_info['firstname'];
		} else {
			$data['vendor']='';
		}
		
		if (isset($this->request->post['store_about'])) {
			$data['store_about'] = $this->request->post['store_about'];
		} elseif (isset($store_info['store_about'])){
			$data['store_about'] = $store_info['store_about'];
		} else {
			$data['store_about'] = '';
		}
		
		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (isset($store_info['email'])){
			$data['email'] = $store_info['email'];
		} else {
			$data['email'] = '';
		}
		
		if (isset($this->request->post['phone'])) {
			$data['phone'] = $this->request->post['phone'];
		} elseif (isset($store_info['phone'])){
			$data['phone'] = $store_info['phone'];
		} else {
			$data['phone'] = '';
		}
		
		if (isset($this->request->post['address'])) {
			$data['address'] = $this->request->post['address'];
		} elseif (isset($store_info['address'])){
			$data['address'] = $store_info['address'];
		} else {
			$data['address'] = '';
		}
		
		if (isset($this->request->post['city'])) {
			$data['city'] = $this->request->post['city'];
		} elseif (isset($store_info['city'])){
			$data['city'] = $store_info['city'];
		} else {
			$data['city'] = '';
		}
		
		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = $this->request->post['country_id'];
		} elseif (isset($store_info['country_id'])){
			$data['country_id'] = $store_info['country_id'];
		} else {
			$data['country_id'] = '';
		}
		
		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = $this->request->post['zone_id'];
		} elseif (isset($store_info['zone_id'])){
			$data['zone_id'] = $store_info['zone_id'];
		} else {
			$data['zone_id'] = '';
		}
		
		if (isset($this->request->post['banner'])) {
			$data['banner'] = $this->request->post['banner'];
		} elseif (isset($store_info['banner'])){
			$data['banner'] = $store_info['banner'];
		} else {
			$data['banner'] = '';
		}
		
		
		if (isset($this->request->post['logo'])) {
			$data['logo'] = $this->request->post['logo'];
		} elseif (isset($store_info['logo'])){
			$data['logo'] = $store_info['logo'];
		} else {
			$data['logo'] = '';
		}
		
		
		if (isset($this->request->post['bank_detail'])) {
			$data['bank_detail'] = $this->request->post['bank_detail'];
		} elseif (isset($store_info['bank_detail'])){
			$data['bank_detail'] = $store_info['bank_detail'];
		} else {
			$data['bank_detail'] = '';
		}
		
		if (isset($this->request->post['postcode'])) {
			$data['postcode'] = $this->request->post['postcode'];
		} elseif (isset($store_info['postcode'])){
			$data['postcode'] = $store_info['postcode'];
		} else {
			$data['postcode'] = '';
		}
		
		if (isset($this->request->post['tax_number'])) {
			$data['tax_number'] = $this->request->post['tax_number'];
		} elseif (isset($store_info['tax_number'])){
			$data['tax_number'] = $store_info['tax_number'];
		} else {
			$data['tax_number'] = '';
		}
		
		if (isset($this->request->post['shipping_charge'])) {
			$data['shipping_charge'] = $this->request->post['shipping_charge'];
		} elseif (isset($store_info['shipping_charge'])){
			$data['shipping_charge'] = $store_info['shipping_charge'];
		} else {
			$data['shipping_charge'] = '';
		}
		
		if (isset($this->request->post['url'])) {
			$data['url'] = $this->request->post['url'];
		} elseif (isset($store_info['url'])){
			$data['url'] = $store_info['url'];
		} else {
			$data['url'] = '';
		}
		
		if (isset($this->request->post['commission'])) {
			$data['commission'] = $this->request->post['commission'];
		} elseif (isset($store_info['commission'])){
			$data['commission'] = $store_info['commission'];
		} else {
			$data['commission'] = '';
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('tool/image');
		
		if (isset($this->request->post['banner']) && is_file(DIR_IMAGE . $this->request->post['banner'])) {
			$data['thumb_banner'] = $this->model_tool_image->resize($this->request->post['banner'], 100, 100);
		} elseif (!empty($store_info) && is_file(DIR_IMAGE . $store_info['banner'])) {
			$data['thumb_banner'] = $this->model_tool_image->resize($store_info['banner'], 100, 100);
		} else {
			$data['thumb_banner'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if (isset($this->request->post['logo']) && is_file(DIR_IMAGE . $this->request->post['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($this->request->post['banner'], 100, 100);
		} elseif (!empty($store_info) && is_file(DIR_IMAGE . $store_info['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($store_info['logo'], 100, 100);
		} else {
			$data['thumb_logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/store_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'vendor/store')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['store_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}
		if ((utf8_strlen($this->request->post['phone']) < 2) || (utf8_strlen($this->request->post['phone']) > 64)) {
			$this->error['phone'] = $this->language->get('error_phone');
		}
		
		if ((utf8_strlen($this->request->post['address']) < 2) || (utf8_strlen($this->request->post['address']) > 64)) {
			$this->error['address'] = $this->language->get('error_address');
		}
		
		if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 64)) {
			$this->error['city'] = $this->language->get('error_city');
		}
		
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/store')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		
		return !$this->error;
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function autocomplete(){
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		$this->load->model('vendor/vendor');
			
		$filter_data = array(
		'sort'  => $sort,
		'order' => $order,
		//'filter_name' => $filter_name,
		'start' => ($page - 1) * $this->config->get('config_limit_admin'),
		'limit' => $this->config->get('config_limit_admin')
		);
		$accounts = $this->model_vendor_vendor->getVendors($filter_data);
		foreach ($accounts as $account) {

		$json[] = array(
		'vendor_id'  => $account['vendor_id'],
		'firstname'   => strip_tags(html_entity_decode($account['firstname'], ENT_QUOTES, 'UTF-8'))
		);
		}
		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['firstname'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	/* 27 01 2020 */
	public function storeautocomplete(){
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		$this->load->model('vendor/vendor');
			
		$filter_data = array(
		'sort'  => $sort,
		'order' => $order,
		//'filter_name' => $filter_name,
		'start' => ($page - 1) * $this->config->get('config_limit_admin'),
		'limit' => $this->config->get('config_limit_admin')
		);
		$accounts = $this->model_vendor_vendor->getVendorsStore($filter_data);
		
		foreach ($accounts as $account) {

		$json[] = array(
		'vendor_id'  => $account['vendor_id'],
		'name'   => strip_tags(html_entity_decode($account['name'], ENT_QUOTES, 'UTF-8'))
		);
		}
		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	/* 27 01 2020 */
	
}