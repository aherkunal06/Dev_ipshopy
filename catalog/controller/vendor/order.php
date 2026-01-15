<?php
class ControllerVendorOrder extends Controller {
    public function index() {
        $this->load->language('vendor/order');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['user_token'] = $this->session->data['user_token'];

        $this->response->setOutput($this->load->view('vendor/order_report', $data));
    }
}
?>
