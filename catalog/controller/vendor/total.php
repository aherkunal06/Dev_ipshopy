<?php
class ControllerVendorTotal extends Controller {
	public function index() {
		$this->load->language('vendor/total');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');
		$data['totalreviews'] = $this->model_vendor_vendor->getTotalDashboardReviews($this->vendor->getId());
		
		/* tmd 05-03-2019 */
		$data['reviewhref'] = $this->url->link('vendor/review');		
		/* tmd 05-03-2019 */
		return $this->load->view('vendor/total', $data);
	}
}
