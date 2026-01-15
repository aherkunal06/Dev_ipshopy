<?php
class ControllerVendorTotaldeliveredOrders extends Controller {
    public function index() {

        // Load Order Report Model
        $this->load->model('vendor/delivered_orders');

     

        // Filter data for the logged-in vendor
        $filter_data = array(
            'vendor_id' => $this->vendor->getId()
        );

        // Get total shipped orders
        $data['totaldeliveredorders'] = $this->model_vendor_delivered_orders->getTotalDeliveredOrders($filter_data['vendor_id'],$filter_data);

        // Link to shipped order report page
        // $data['orderhref'] = $this->url->link('vendor/shipped_order_report');

        return $this->load->view('vendor/totaldeliveredorders', $data);
    }
}
