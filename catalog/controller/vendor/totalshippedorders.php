<?php
class ControllerVendorTotalShippedOrders extends Controller {
    public function index() {
        $this->load->language('vendor/totalshippedorders');

        // Load Order Report Model
        $this->load->model('vendor/order_report');

        // Set Heading Title
        $data['heading_title'] = $this->language->get('heading_title');

        // View Text
        $data['text_view'] = $this->language->get('text_view');

        // Filter data for the logged-in vendor
        $filter_data = array(
            'vendor_id' => $this->vendor->getId()
        );

        // Get total shipped orders
        $data['totalshippedorders'] = $this->model_vendor_order_report->getTotalShippedOrders($filter_data);

        // Link to shipped order report page
        $data['orderhref'] = $this->url->link('vendor/shipped_order_report');

        return $this->load->view('vendor/totalshippedorders', $data);
    }
}
