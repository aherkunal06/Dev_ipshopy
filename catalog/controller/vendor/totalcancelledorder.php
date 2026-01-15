<?php
class ControllerVendorTotalCancelledOrder extends Controller {
    public function index() {
        $this->load->language('vendor/totalcancelledorder');

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

        // Get total cancelled orders
        $data['totalcancelledorder'] = $this->model_vendor_order_report->getTotalCancelledOrders($filter_data);

        // Link to cancelled order report page
        $data['orderhref'] = $this->url->link('vendor/cancelled_order_report');

        return $this->load->view('vendor/totalcancelledorder', $data);
    }
}
?>
