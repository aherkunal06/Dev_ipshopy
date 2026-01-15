<?php
class ControllerVendorTotalOrder extends Controller {
	public function index() {
		$this->load->language('vendor/totalorder');
			/* update 02 11 2020 */
// 		$this->load->model('vendor/order_report');
    $this->load->model('vendor/all_order');
			/* update 02 11 2020 */
		$data['heading_title'] = $this->language->get('heading_title');
// 		 $filter_data = array(
//       'filter_order_id' => $filter_order_id,
//       'filter_product_name' => $filter_product_name,
//       'filter_date_added' => $filter_date_added,
//       'start' => ($page - 1) * $this->config->get('config_limit_admin'),
//       'limit' => $this->config->get('config_limit_admin')
//     );

		$data['text_view'] = $this->language->get('text_view');
// 		$filter_data=array(
$vendor_id = $this->vendor->getId();		
// 		);
			/* update 02 11 2020 */
		$data['totalorder'] = $this->model_vendor_all_order->getTotalAllOrders($vendor_id);
		/* update 02 11 2020 */
		$data['orderhref'] = $this->url->link('vendor/all_order');
		
		return $this->load->view('vendor/totalorder', $data);
	}



	
}
