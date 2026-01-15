<?php
class ControllerVendorTodaysOrder extends Controller {
    public function index() {
        $this->load->language('vendor/todaysorder'); // Load today's order language file
        $this->load->model('vendor/order_report'); // Load order report model

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_view'] = $this->language->get('text_view');

        // Get today's date in the correct format
        $today_date = date('Y-m-d');

        // Filter by vendor ID and today's date
        $filter_data = array(
            'vendor_id'   => $this->vendor->getId(),
            'date_added'  => $today_date // Filter for today's orders
        );

        // Fetch today's total orders count
        $data['todays_order'] = $this->model_vendor_order_report->getTodaysReport($filter_data);

        // Link to vendor order report page
        $data['orderhref'] = $this->url->link('vendor/order_report');

        return $this->load->view('vendor/todaysorder', $data);
    }
}
