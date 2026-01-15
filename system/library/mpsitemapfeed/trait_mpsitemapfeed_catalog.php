<?php
namespace mpsitemapfeed;
// use in admin only
trait trait_mpsitemapfeed_catalog {

	protected $is_multilingual = false;
	protected $path_application;
	protected $server;
	protected $total_language;
	protected $extension_path = 'extension/';
	public function igniteTraitMpsitemapfeed($registry) {
		$this->path_application = str_replace('catalog/', '', DIR_APPLICATION);

		$this->total_language = 1;

		if ($this->request->server['HTTPS']) {
			$this->server = $this->config->get('config_ssl');
		} else {
			$this->server = $this->config->get('config_url');
		}

		if (VERSION >= '3.0.0.0') {
			$this->is_multilingual = true;
		}

		if (VERSION <= '2.2.0.0') {
			$this->extension_path = '';
		}
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

	public function viewLoad($path, &$data) {

		if (VERSION >= '2.2.0.0') {
			$view = $this->load->view($this->path($path), $data);
		} else {
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . $this->path($path))) {
				$view = $this->load->view($this->config->get('config_template') . '/template/' . $this->path($path), $data);
			 } else {
				$view = $this->load->view('default/template/' . $this->path($path), $data);
			 }
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


	public function installDb() {

	}

}
