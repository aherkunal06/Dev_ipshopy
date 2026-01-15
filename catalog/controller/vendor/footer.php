<?php
class ControllerVendorFooter extends Controller {
	public function index() {
		$this->load->language('vendor/footer');

		$data['text_footer'] = $this->language->get('text_footer');

		if ($this->vendor->isLogged() && isset($this->request->get['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			$data['text_version'] = sprintf($this->language->get('text_version'), VERSION);
		} else {
			$data['text_version'] = '';
		}
		return $this->load->view('vendor/footer', $data);
		
		
	}
}
