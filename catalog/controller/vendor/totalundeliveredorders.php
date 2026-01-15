<?php
class ControllerVendorTotalUnDeliveredOrders extends Controller {
    public function index() {

        // Load Order Report Model
        $this->load->model('vendor/undelivered_orders');

        // Filter data for the logged-in vendor
        $filter_data = array(
            'vendor_id' => $this->vendor->getId()
        );

        // Get total shipped orders
        $data['totalundeliveredorders'] = $this->model_vendor_undelivered_orders->getTotalUndeliveredOrders($filter_data['vendor_id'],$filter_data);

        // Link to shipped order report page
        // $data['orderhref'] = $this->url->link('vendor/shipped_order_report');

        return $this->load->view('vendor/totalundeliveredorders', $data);
    }
}
