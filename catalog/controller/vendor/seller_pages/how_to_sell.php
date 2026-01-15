<?php
class ControllerVendorsellerpageshowtosell extends Controller
{
  

  public function index()
  {
      
$data['header'] = $this->load->controller('vendor/seller_pages/header');
    // $data['footer'] = $this->load->view('vendor/seller_pages/footer');
    $data['footer'] = $this->load->controller('vendor/seller_pages/footer');
    $this->response->setOutput($this->load->view('vendor/seller_pages/how_to_sell' , $data));
  }
}