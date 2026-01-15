<?php
class ControllerVendorTotalReturnOrders extends Controller {
    public function index() {

        // Load Order Report Model
        $this->load->model('vendor/return_orders');
      
        // Filter data for the logged-in vendor
        $filter_data = array(
            'vendor_id' => $this->vendor->getId()
        );

        // Get total shipped orders
        $data['totalreturnorders'] = $this->model_vendor_return_orders->getTotalReturnOrders($filter_data['vendor_id'],$filter_data);

        // Link to shipped order report page
        // $data['orderhref'] = $this->url->link('vendor/shipped_order_report');

        return $this->load->view('vendor/total_return_orders', $data);
    }
}
