<?php
class ControllerVendorsellerpagesHeader extends Controller {
    public function index() {
        $data['seller_home']     = $this->url->link('vendor/seller_pages/seller_landing', '', true);
        $data['seller_login']    = $this->url->link('vendor/login', '', true);
        $data['seller_register'] = $this->url->link('vendor/vendor', '', true);
        $data['seller_pricing']  = $this->url->link('vendor/seller_pages/fees', '', true);
        $data['seller_success']  = $this->url->link('vendor/seller_pages/success_stories', '', true);

        return $this->load->view('vendor/seller_pages/header', $data);
    }
}
