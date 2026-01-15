<?php
class Controllervendorsellerpagesregistrationguide extends Controller {
    public function index() {
     // $this->load->language('vendor/seller_landing');
        $data['heading_title'] = $this->load->language('vendor/seller_pages/registration_guide');
        
$data['header'] = $this->load->controller('vendor/seller_pages/header');
        // $data['footer'] = $this->load->view('vendor/seller_pages/footer');
        $data['footer'] = $this->load->controller('vendor/seller_pages/footer');
        $this->response->setOutput($this->load->view('vendor/seller_pages/registration_guide', $data));

  }
}
