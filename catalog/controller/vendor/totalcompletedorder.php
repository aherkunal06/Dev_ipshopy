<?php
class ControllerVendorTotalCompletedOrder extends Controller {
    public function index() {
        $this->load->language('vendor/totalcompletedorder');

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

        // Get total completed orders
        $data['totalcompletedorder'] = $this->model_vendor_order_report->getTotalCompletedOrders($filter_data);

        // Link to completed order report page
        $data['orderhref'] = $this->url->link('vendor/completed_order_report');

        return $this->load->view('vendor/totalcompletedorder', $data);
    }
}
