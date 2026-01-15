<?php
class ControllerVendorTotalLostShipmentOrders extends Controller {
    public function index() {

        // Load Order Report Model
        $this->load->model('vendor/lost_shipments_orders');
      
        // Filter data for the logged-in vendor
        $filter_data = array(
            'vendor_id' => $this->vendor->getId()
        );

        // Get total shipped orders
        $data['totallostshipmentorders'] = $this->model_vendor_lost_shipments_orders->getTotalLostShipmentsOrders($filter_data['vendor_id'],$filter_data);

        // Link to shipped order report page
        // $data['orderhref'] = $this->url->link('vendor/shipped_order_report');

        return $this->load->view('vendor/total_lostshipment_orders', $data);
    }
}
