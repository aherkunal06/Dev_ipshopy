<?php
namespace mpsitemapfeed;
// use in admin only
trait trait_mpsitemapfeed {

	protected $token = 'token';
	protected $ssl = true;
	protected $is_multilingual = false;
	protected $affiliate_show = true;
	protected $extension_page_path = 'extension/extension';
	protected $extension_path = 'extension/';
	protected $extension_prefix = ['module' => '', 'payment' => '', 'shipping' => '', 'total' =>  ''];
	protected $model_file = [
		'extension/extension' => [
			'path' => 'extension/extension',
			'obj' => 'model_extension_extension',
		],
		'extension/module' => [
			'path' => 'extension/module',
			'obj' => 'model_extension_module',
		],
		'customer/custom_field' => [
			'path' => 'customer/custom_field',
			'obj' => 'model_customer_custom_field',
		],
		'extension/event' => [
			'path' => 'extension/event',
			'obj' => 'model_extension_event',
		],
	];


	public function igniteTraitMpSitemapFeed($registry) {

		if (VERSION < '2.2.0.0') {
			$this->ssl = 'ssl';
		}

		if (VERSION <= '2.2.0.0') {
			$this->extension_path = '';
		}

		if (VERSION < '2.0.3.1') {
			$this->model_file['customer/custom_field'] = [
				'path' => 'sale/custom_field',
				'obj' => 'model_sale_custom_field',
			];
		}

		if (VERSION >= '3.0.0.0') {
			$this->is_multilingual = true;
			$this->affiliate_show = false;
			$this->token = 'user_token';
			$this->extension_page_path = 'marketplace/extension';

			$this->extension_prefix = [
				'module' => 'module_',
				'payment' => 'payment_',
				'shipping' => 'shipping_',
				'total' => 'total_',
			];
			$this->model_file['extension/extension'] = [
				'path' => 'setting/extension',
				'obj' => 'model_setting_extension',
			];
			$this->model_file['extension/event'] = [
				'path' => 'setting/event',
				'obj' => 'model_setting_event',
			];
			$this->model_file['extension/module'] = [
				'path' => 'setting/module',
				'obj' => 'model_setting_module',
			];
		}

	}

	public function getCustomerGroups() {
		if (VERSION < '2.2.0.0') {
			$this->load->model('sale/customer_group');
			$model_customer_group = 'model_sale_customer_group';
		} else {
			$this->load->model('customer/customer_group');
			$model_customer_group = 'model_customer_customer_group';
		}
		return $this->{$model_customer_group}->getCustomerGroups();
	}

	public function getLanguages() {
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		if (VERSION >= '2.2.0.0') {
			foreach ($languages as &$language) {
				$language['lang_flag'] = 'language/'.$language['code'].'/'.$language['code'].'.png';
			}
		} else {
			foreach ($languages as &$language) {
				$language['lang_flag'] = 'view/image/flags/'.$language['image'].'';
			}
		}
		return $languages;
	}

	public function viewLoad($path, &$data, $twig=false) {

		$data['token'] = $this->session->data[$this->token];
		$data['get_token'] = $this->token;
		$data['extension_path'] = $this->extension_path;
		$data['is_multilingual'] = $this->is_multilingual;

		if (VERSION < '3.0.0.0') {
			$this->getAllLanguageMpsitemapfeed($data);
		}

		if (VERSION >= '3.0.0.0' && !$twig) {
			$old_template = $this->config->get('template_engine');
			$this->config->set('template_engine', 'template');
		}

		$view = $this->load->view($this->path($path), $data);

		if (VERSION >= '3.0.0.0' && !$twig) {
			$this->config->set('template_engine', $old_template);
		}

		return $view;
	}

	public function path($path) {
		$path_info = pathinfo($path);

		$npath = $path_info['dirname'] . '/'. $path_info['filename'];
		if (VERSION <= '2.3.0.2') {
			$npath.= '.tpl';
		}
		return $npath;
	}

	public function textEditor(&$d) {
		$d['summernote'] = '';
		$data = [];
		return $this->viewLoad($this->extension_path . 'mpsitemapfeed/texteditor', $data);
	}

	public function installDb() {

	}


	public function labelEnableDisable($status) {
		return '<i class="fa fa-circle '. ($status ? 'text-success' : 'text-danger') .'"></i>';
	}

	// explicit code for 2x, lower than 2.3x versions only.
	// call using ocmod

	public function getAllLanguageMpsitemapfeed(&$data) {
		// method comes through ocmod.
		if (method_exists($this->language, 'getAllLanguageMpsitemapfeed')) {
			foreach ($this->language->getAllLanguageMpsitemapfeed() as $key => $value) {
				if (!isset($data[$key])) {
					$data[$key] = $value;
				}
			}
		}
		// from oc2.3x we have language all method.
		if (method_exists($this->language, 'all')) {
			foreach ($this->language->all() as $key => $value) {
				if (!isset($data[$key])) {
					$data[$key] = $value;
				}
			}
		}
	}
}
