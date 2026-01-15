<?php
class Controllervendortutorialvideos extends Controller {
    public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		$this->load->language('vendor/tutorialvideos');

		
        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');
        $data['tutorialvideo'] = $this->document->setTitle($this->language->get('heading_title'));
        
        $this->response->setOutput($this->load->view('vendor/tutorialvideos', $data));
	}

}