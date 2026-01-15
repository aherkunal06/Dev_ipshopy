<?php
class ControllerVendorTotalRtoInTransitOrders extends Controller {
    public function index() {

        // Load Order Report Model
        $this->load->model('vendor/rto_in_transit_orders');
      
        // Filter data for the logged-in vendor
        $filter_data = array(
            'vendor_id' => $this->vendor->getId()
        );

        // Get total shipped orders
        $data['totalrtointransitorders'] = $this->model_vendor_rto_in_transit_orders->getTotalRtoInTransitOrders($filter_data['vendor_id'],$filter_data);

        // Link to shipped order report page
        // $data['orderhref'] = $this->url->link('vendor/shipped_order_report');

        return $this->load->view('vendor/total_rtointransit_orders', $data);
    }
}
