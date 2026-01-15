<?php
class ControllervendorsellerpagessellerFAQ extends Controller
{
    public function index()
    {

        $this->load->language('vendor/seller_pages/seller_faq');
        $data['heading_title'] = $this->language->get('heading_title');


     
// $data['header'] = $this->load->view('vendor/seller_pages/header');

$data['header'] = $this->load->controller('vendor/seller_pages/header');
    // $data['footer'] = $this->load->view('vendor/seller_pages/footer');
    $data['footer'] = $this->load->controller('vendor/seller_pages/footer');

        $this->response->setOutput($this->load->view('vendor/seller_pages/seller_faq', $data));
    }
}
