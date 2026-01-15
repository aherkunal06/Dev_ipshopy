<?php

class ControllerExtensionMpImporterExporterExportOrder extends \MpImporterExporter\Controller {

	private $error = [];

	public function getMenu() {
		$this->load->language('mpimporterexporter/export_order_menu');
		$menu = [];
		if ($this->user->hasPermission('access', 'mpimporterexporter/export_order')) {
			$menu = [
				'name'	   => $this->language->get('text_order_exporter'),
				'href'     => $this->url->link($this->isdir_extension . 'mpimporterexporter/export_order', $this->token . '=' . $this->session->data[$this->token], true),
				'children' => []
			];
		}
		return $menu;
	}

	public function index() {
		$this->load->language('mpimporterexporter/export_order');
		$this->load->model($this->isdir_extension . 'mpimporterexporter/export_order');

		$this->document->addStyle('view/stylesheet/mpimporterexporter/export_order.css');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$this->breadcrumbs($data);
		// reload page specific language file to avoid language variable conflicts
		$this->load->language('mpimporterexporter/export_order');

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->isdir_extension . 'mpimporterexporter/export_order', $this->token . '=' . $this->session->data[$this->token], true)
		];

		$this->backLink($data);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['extrafields'] = $this->{'model_' . $this->model_extension . 'mpimporterexporter_export_order'}->getExtraFields();

		$data['exporter_action'] = $this->url->link($this->isdir_extension . 'mpimporterexporter/export_order/export', $this->token . '=' . $this->session->data[$this->token], true);

		$this->load->model('setting/store');
		$this->load->model('localisation/order_status');
		$this->load->model('localisation/country');
		$this->load->model('localisation/language');
		$this->load->model('localisation/currency');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$data['stores'] = [];
		$stores = $this->model_setting_store->getStores();
		$data['stores'][] = [
			'store_id' => 0,
			'name' => $this->language->get('text_default')
		];
		foreach ($stores as $store) {
			$data['stores'][] = [
				'store_id' => $store['store_id'],
				'name' => $store['name']
			];
		}

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$this->load->model('setting/extension');
		$language = new Language($this->config->get('language_default'));
		$language->load($this->config->get('language_default'));

		$payment_methods = $this->model_setting_extension->getInstalled('payment');
		$data['payment_methods'] = [];
		foreach ($payment_methods as $payment_method) {
				if ($this->config->get('payment_'.$payment_method . '_status')) {
					$language->load($this->isdir_extension . 'payment/'.$payment_method);
					$data['payment_methods'][] = [
						'code' => $payment_method,
						'heading_title' => $language->get('heading_title')
					];
				}
		}

		$shipping_methods = $this->model_setting_extension->getInstalled('shipping');
		$data['shipping_methods'] = [];
		foreach ($shipping_methods as $shipping_method) {
				if ($this->config->get('shipping_'.$shipping_method . '_status')) {
					$language->load($this->isdir_extension . 'shipping/'.$shipping_method);
					$data['shipping_methods'][] = [
						'code' => $shipping_method,
						'heading_title' => $language->get('heading_title')
					];
				}
		}

		$this->load->model($this->models['customer/customer_group']['path']);
		$data['customer_groups'] = $this->{$this->models['customer/customer_group']['obj']}->getCustomerGroups();

		$data['sorts'] = [];
		$data['sorts'][] = [
			'value' => 'order_id',
			'text' => $this->language->get('text_order_id')
		];
		$data['sorts'][] = [
			'value' => 'customer',
			'text' => $this->language->get('text_customer')
		];
		$data['sorts'][] = [
			'value' => 'order_status',
			'text' => $this->language->get('text_order_status')
		];
		$data['sorts'][] = [
			'value' => 'date_added',
			'text' => $this->language->get('text_date_added')
		];
		$data['sorts'][] = [
			'value' => 'date_modified',
			'text' => $this->language->get('text_date_modified')
		];
		$data['sorts'][] = [
			'value' => 'total',
			'text' => $this->language->get('text_total')
		];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->loadView($this->isdir_extension . 'mpimporterexporter/export_order', $data));
	}

	// Order Export Function
	public function export() {
		$json = [];

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->accessValidate()) {

		$this->load->language('mpimporterexporter/export_order');
		$this->load->model($this->isdir_extension . 'mpimporterexporter/export_order');
		$this->load->model('sale/order');
		$this->load->model('tool/upload');

		$filter_data = [
			'sort'  => 'cf.sort_order',
			'order' => 'ASC'
		];

		$this->load->model('customer/custom_field');
		$custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);


		if (isset($this->request->post['find_order_id']) && $this->request->post['find_order_id'] != '') {
			$find_order_id = $this->request->post['find_order_id'];
		}else{
			$find_order_id = null;
		}

		if (isset($this->request->post['find_total']) && $this->request->post['find_total'] != '') {
			$find_total = $this->request->post['find_total'];
		}else{
			$find_total = null;
		}

		if (isset($this->request->post['find_order_status']) && $this->request->post['find_order_status'] != '') {
			$find_order_status = $this->request->post['find_order_status'];
		}else{
			$find_order_status = null;
		}

		if (isset($this->request->post['find_customer_group']) && $this->request->post['find_customer_group'] != '') {
			$find_customer_group = $this->request->post['find_customer_group'];
		}else{
			$find_customer_group = null;
		}


		if (isset($this->request->post['find_date_start']) && $this->request->post['find_date_start'] != '') {
			$find_date_start = $this->request->post['find_date_start'];
		}else{
			$find_date_start = '';
		}

		if (isset($this->request->post['find_date_end']) && $this->request->post['find_date_end'] != '') {
			$find_date_end = $this->request->post['find_date_end'];
		}else{
			$find_date_end = '';
		}

		if (isset($this->request->post['find_limit_start']) && $this->request->post['find_limit_start'] != '') {
			$find_limit_start = $this->request->post['find_limit_start'];
		}else{
			$find_limit_start = '';
		}

		if (isset($this->request->post['find_limit_end']) && $this->request->post['find_limit_end'] != '') {
			$find_limit_end = $this->request->post['find_limit_end'];
		}else{
			$find_limit_end = '';
		}

		if (isset($this->request->post['find_payment_method']) && !empty($this->request->post['find_payment_method'])) {
			$find_payment_method = (array)$this->request->post['find_payment_method'];
		}else{
			$find_payment_method = [];
		}

		if (isset($this->request->post['find_shipping_method']) && !empty($this->request->post['find_shipping_method'])) {
			$find_shipping_method = (array)$this->request->post['find_shipping_method'] ;
		}else{
			$find_shipping_method = [];
		}

		if (isset($this->request->post['find_store_id']) && $this->request->post['find_store_id'] != '') {
			$find_store_id = $this->request->post['find_store_id'];
		}else{
			$find_store_id = null;
		}

		if (isset($this->request->post['find_product']) && $this->request->post['find_product'] != '') {
			$find_product = $this->request->post['find_product'];
		}else{
			$find_product = null;
		}

		if (isset($this->request->post['find_customer']) && $this->request->post['find_customer'] != '') {
			$find_customer = $this->request->post['find_customer'];
		}else{
			$find_customer = null;
		}

		if (isset($this->request->post['find_payment_country_id']) && $this->request->post['find_payment_country_id'] != '') {
			$find_payment_country_id = $this->request->post['find_payment_country_id'];
		}else{
			$find_payment_country_id = null;
		}

		if (isset($this->request->post['find_payment_zone_id']) && $this->request->post['find_payment_zone_id'] != '') {
			$find_payment_zone_id = $this->request->post['find_payment_zone_id'];
		}else{
			$find_payment_zone_id = null;
		}

		if (isset($this->request->post['find_payment_postcode']) && $this->request->post['find_payment_postcode'] != '') {
			$find_payment_postcode = $this->request->post['find_payment_postcode'];
		}else{
			$find_payment_postcode = null;
		}

		if (isset($this->request->post['find_shipping_country_id']) && $this->request->post['find_shipping_country_id'] != '') {
			$find_shipping_country_id = $this->request->post['find_shipping_country_id'];
		}else{
			$find_shipping_country_id = null;
		}

		if (isset($this->request->post['find_shipping_zone_id']) && $this->request->post['find_shipping_zone_id'] != '') {
			$find_shipping_zone_id = $this->request->post['find_shipping_zone_id'];
		}else{
			$find_shipping_zone_id = null;
		}

		if (isset($this->request->post['find_shipping_postcode']) && $this->request->post['find_shipping_postcode'] != '') {
			$find_shipping_postcode = $this->request->post['find_shipping_postcode'];
		}else{
			$find_shipping_postcode = null;
		}

		if (isset($this->request->post['find_language_id']) && $this->request->post['find_language_id'] != '') {
			$find_language_id = $this->request->post['find_language_id'];
		}else{
			$find_language_id = null;
		}

		if (isset($this->request->post['find_currency_id']) && $this->request->post['find_currency_id'] != '') {
			$find_currency_id = $this->request->post['find_currency_id'];
		}else{
			$find_currency_id = null;
		}

		if (isset($this->request->post['find_invoice_prefix']) && $this->request->post['find_invoice_prefix'] != '') {
			$find_invoice_prefix = $this->request->post['find_invoice_prefix'];
		}else{
			$find_invoice_prefix = null;
		}

		if (isset($this->request->post['find_invoice']) && $this->request->post['find_invoice'] != '') {
			$find_invoice = $this->request->post['find_invoice'];
		}else{
			$find_invoice = null;
		}


		if (isset($this->request->post['find_orderdetail']) && $this->request->post['find_orderdetail'] != '') {
			$find_orderdetail = $this->request->post['find_orderdetail'];
		}else{
			$find_orderdetail = null;
		}

		if (isset($this->request->post['find_customerdetail']) && $this->request->post['find_customerdetail'] != '') {
			$find_customerdetail = $this->request->post['find_customerdetail'];
		}else{
			$find_customerdetail = null;
		}

		if (isset($this->request->post['find_productdetail']) && $this->request->post['find_productdetail'] != '') {
			$find_productdetail = $this->request->post['find_productdetail'];
		}else{
			$find_productdetail = null;
		}

		if (isset($this->request->post['find_voucherdetail']) && $this->request->post['find_voucherdetail'] != '') {
			$find_voucherdetail = $this->request->post['find_voucherdetail'];
		}else{
			$find_voucherdetail = null;
		}
		if (isset($this->request->post['find_ordertotals']) && $this->request->post['find_ordertotals'] != '') {
			$find_ordertotals = $this->request->post['find_ordertotals'];
		}else{
			$find_ordertotals = null;
		}

		if (isset($this->request->post['find_shippingaddress']) && $this->request->post['find_shippingaddress'] != '') {
			$find_shippingaddress = $this->request->post['find_shippingaddress'];
		}else{
			$find_shippingaddress = null;
		}

		if (isset($this->request->post['find_paymentaddress']) && $this->request->post['find_paymentaddress'] != '') {
			$find_paymentaddress = $this->request->post['find_paymentaddress'];
		}else{
			$find_paymentaddress = null;
		}

		if (isset($this->request->post['find_customfields']) && $this->request->post['find_customfields'] != '') {
			$find_customfields = $this->request->post['find_customfields'];
		}else{
			$find_customfields = null;
		}

		if (isset($this->request->post['find_extrafields']) && $this->request->post['find_extrafields'] != '') {
			$find_extrafields = $this->request->post['find_extrafields'];
		}else{
			$find_extrafields = null;
		}

		if (isset($this->request->post['find_format']) && $this->request->post['find_format'] != '') {
			$find_format = in_array($this->request->post['find_format'], ['csv','xls','xlsx','json','xml']) ? $this->request->post['find_format'] : 'xlsx';
		}else{
			$find_format = 'xlsx';
		}

		if (!empty($this->request->post['find_sort'])) {
			$find_sort = $this->request->post['find_sort'];
		}else{
			$find_sort = 'o.order_id';
		}

		if (!empty($this->request->post['find_order'])) {
			$find_order = $this->request->post['find_order'];
		}else{
			$find_order = 'DESC';
		}

		$valid = ($find_orderdetail || $find_customerdetail || $find_productdetail || $find_voucherdetail || $find_ordertotals || $find_shippingaddress || $find_paymentaddress || $find_customfields || $find_extrafields);

		if ($valid) {
			$filter_data = [
				'find_order_id' => $find_order_id,
				'find_total' => $find_total,
				'find_order_status' => $find_order_status,
				'find_customer_group' => $find_customer_group,
				'find_date_start' => $find_date_start,
				'find_date_end' => $find_date_end,
				'find_limit_start' => $find_limit_start,
				'find_limit_end' => $find_limit_end,
				'find_payment_method' => $find_payment_method,
				'find_shipping_method' => $find_shipping_method,
				'find_store_id' => $find_store_id,
				'find_product' => $find_product,
				'find_customer' => $find_customer,
				'find_payment_country_id' => $find_payment_country_id,
				'find_payment_zone_id' => $find_payment_zone_id,
				'find_payment_postcode' => $find_payment_postcode,
				'find_shipping_country_id'=> $find_shipping_country_id,
				'find_shipping_zone_id' => $find_shipping_zone_id,
				'find_shipping_postcode' => $find_shipping_postcode,
				'find_language_id' => $find_language_id,
				'find_currency_id' => $find_currency_id,
				'find_invoice_prefix' => $find_invoice_prefix,
				'find_invoice' => $find_invoice,
				'find_orderdetail' => $find_orderdetail,
				'find_customerdetail' => $find_customerdetail,
				'find_productdetail' => $find_productdetail,
				'find_voucherdetail' => $find_voucherdetail,
				'find_ordertotals' => $find_ordertotals,
				'find_shippingaddress' => $find_shippingaddress,
				'find_paymentaddress' => $find_paymentaddress,
				'find_customfields' => $find_customfields,
				'find_extrafields' => $find_extrafields,
				'find_format' => $find_format,
				'find_sort' => $find_sort,
				'find_order' => $find_order,
			];

			// Fetch Orders
			$results = $this->{'model_' . $this->model_extension . 'mpimporterexporter_export_order'}->getOrders($filter_data);

			if (in_array($find_format, ['xls','xlsx','csv'])) {


				$objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

				$objPHPExcel->setActiveSheetIndex(0);

				// $objPHPExcel->getProperties()
				//     ->setCreator("Module Points")
				//     ->setLastModifiedBy("Module Points")
				//     ->setTitle("Order Export Suite")
				//     ->setSubject("Order Export Suite by ". $this->user->getUserName())
				//     ->setDescription("Order export suite is suitable to take products backup in popular method (Excel File) export")
				//     ->setKeywords("order export suite")
				//     ->setCategory("Order Export");

				$i = 1;
				$char = 'A';

				// https://stackoverflow.com/questions/19764155/phpexcel-how-to-apply-styles-and-set-cell-width-and-cell-height-to-cell-generate
				// set column width
				// $objWorksheet->getActiveSheet()->getColumnDimension('A')->setWidth(100);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
				// or define auto-size:
				// $objWorksheet->getRowDimension('1')->setRowHeight(-1);

				// $objPHPExcel->getActiveSheet()->freezePane('D2');

				$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_order_id'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

				if ($find_orderdetail) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_invoice_prefix'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_invoice_no'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_store_id'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_store_name'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_store_url'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}

				if ($find_customerdetail) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_customer_id'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_customer'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_email'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_telephone'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_fax'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}

				if ($find_productdetail) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_order_products'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_order_options'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}

				if ($find_voucherdetail) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_order_vouchers'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}

				if ($find_ordertotals) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_order_totals'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}

				if ($find_customfields) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_customfields'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}

				if ($find_paymentaddress) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_paymentaddress'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}

				if ($find_customfields) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_paymentcustomfields'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}

				if ($find_shippingaddress) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_shippingaddress'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}

				if ($find_customfields) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_shipping_customfields'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}

				if ($find_orderdetail) {
					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_payment_method'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_payment_code'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_shipping_method'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_shipping_code'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_comment'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_total'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_order_status_id'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_order_status'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_affiliate_id'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_commission'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_marketing_id'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_tracking'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_language_id'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_currency_code'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_currency_value'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_ip'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_forwarded_ip'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_user_agent'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_accept_language'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_date_added'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);

					$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $this->language->get('export_date_modified'))->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
				}


				if (!empty($find_extrafields)) {
					foreach ($find_extrafields as $find_extrafield) {
						$find_extrafield = array_map('trim', explode('::', $find_extrafield));
						if (isset($find_extrafield[0]) && isset($find_extrafield[1])) {
							$objPHPExcel->getActiveSheet()->setCellValue($char .$i, $find_extrafield[0].'::'.$find_extrafield[1])->getColumnDimension($char)->setAutoSize(true); $objPHPExcel->getActiveSheet()->getStyle($char++ .$i)->getAlignment()->setWrapText(true);
						}
					}
				}

				// Background Color
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.$objPHPExcel->getActiveSheet()->getHighestColumn().'1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('1A017FBE');
				// Font Color
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.$objPHPExcel->getActiveSheet()->getHighestColumn().'1')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFFFF');

				if ($results) {
					// Fetch Total Orders
					$objPHPExcel->getActiveSheet()->setTitle(sprintf($this->language->get('export_title'), count($results)));

					foreach ($results as $result) {
						$char_value = 'A'; $i++;

						$order_info = $this->{'model_' . $this->model_extension . 'mpimporterexporter_export_order'}->getOrder($result['order_id']);

						if ($order_info) {
							// Set Cell Values
							if ($find_productdetail) {
								$order_products = $this->model_sale_order->getOrderProducts($order_info['order_id']);
								$order_products_data = [];
								$order_products_option_data = [];
								foreach ($order_products as $order_product) {
									$order_product['name'] = html_entity_decode($this->ifnull($order_product['name']), ENT_QUOTES, 'UTF-8');

									$order_options = $this->model_sale_order->getOrderOptions($order_product['order_id'], $order_product['order_product_id']);

									$order_options_data = '';
									$option_row = 1;
									foreach ($order_options as $order_option) {
										if ($option_row == '1') {
											$option_string = $order_product['name']. ' >> ';
										}else{
											$option_string = '';
										}

										$order_options_data .= $option_string . $order_option['name'] .' :: '. $order_option['value'];
										if (count($order_options) != $option_row) {
											$order_options_data .= ' || ';
										}

										$option_row++;
									}

									if ($order_options_data) {
										$order_products_option_data[] = $order_options_data;
									}

									$order_products_data[] = $order_product['name'] .' >> '. $order_product['model'] .' :: '. $order_product['quantity'] .' :: '. $this->currency->format($order_product['price'], $order_info['currency_code'], $order_info['currency_value']) .' :: '. $this->currency->format($order_product['tax'], $order_info['currency_code'], $order_info['currency_value']) .' :: '. $this->currency->format($order_product['total'], $order_info['currency_code'], $order_info['currency_value']);
								}

								if ($order_products_option_data) {
									$order_info['order_products_option_data'] = implode(';; ', $order_products_option_data);
								} else {
									$order_info['order_products_option_data'] = '';
								}

								if ($order_products_data) {
									$order_info['order_products'] = implode("; \n", $order_products_data);
								} else{
									$order_info['order_products'] = '';
								}
							}

							if ($find_voucherdetail) {
								$order_vouchers = $this->model_sale_order->getOrderVouchers($order_info['order_id']);
								$order_vouchers_data = [];

								foreach ($order_vouchers as $order_voucher) {
									$order_vouchers_data[] = $order_voucher['code'] .' :: '. $order_voucher['from_name'] .' :: '. $order_voucher['from_email'] .' :: '. $order_voucher['to_name'] .' :: '. $order_voucher['to_email'].' :: '. $order_voucher['message'];
								}

								if ($order_vouchers_data) {
									$order_info['order_vouchers_data'] = implode("; \n", $order_vouchers_data);
								} else{
									$order_info['order_vouchers_data'] = '';
								}
							}

							// Assign Cell Values
							$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['order_id']);

							// Order Details
							if ($find_orderdetail) {
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['invoice_prefix']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['invoice_no']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['store_id']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['store_name']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['store_url']);
							}

							// Customer Details
							if ($find_customerdetail) {
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['customer_id']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['customer']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['email']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['telephone']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['fax']);
							}

							// Product Details
							if ($find_productdetail) {
								$objPHPExcel->getActiveSheet()->setCellValue($char_value .$i, $order_info['order_products'])->getColumnDimension($char_value)->setAutoSize(true);
								$objPHPExcel->getActiveSheet()->getStyle($char_value++ .$i)->getAlignment()->setWrapText(true);

								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['order_products_option_data']);
							}

							// Voucher Details
							if ($find_voucherdetail) {
								$objPHPExcel->getActiveSheet()->setCellValue($char_value .$i, $order_info['order_vouchers_data'])->getColumnDimension($char_value)->setAutoSize(true);
								$objPHPExcel->getActiveSheet()->getStyle($char_value++ .$i)->getAlignment()->setWrapText(true);
							}

							// Order Totals Details
							if ($find_ordertotals) {
								$order_totals = $this->model_sale_order->getOrderTotals($order_info['order_id']);
								$order_totals_data = [];
								foreach ($order_totals as $order_total) {
								$order_total['title'] = html_entity_decode($this->ifnull($order_total['title']), ENT_QUOTES, 'UTF-8');
									$order_totals_data[] = $order_total['title'] .' - '. $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']);
								}

								$order_info['order_totals'] = implode("; \n", $order_totals_data);

								$objPHPExcel->getActiveSheet()->setCellValue($char_value .$i, $order_info['order_totals'])->getColumnDimension($char_value)->setAutoSize(true);
								$objPHPExcel->getActiveSheet()->getStyle($char_value++ .$i)->getAlignment()->setWrapText(true);
							}

							// Order Custom Field
							if ($find_customfields) {
								$data['account_custom_fields'] = [];
								if ($order_info['custom_field']) {
									foreach ($custom_fields as $custom_field) {
										if ($custom_field['location'] == 'account' && isset($order_info['custom_field'][$custom_field['custom_field_id']])) {
											if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
												$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['custom_field'][$custom_field['custom_field_id']]);

												if ($custom_field_value_info) {
													$data['account_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
												}
											}

											if ($custom_field['type'] == 'checkbox' && is_array($order_info['custom_field'][$custom_field['custom_field_id']])) {
												foreach ($order_info['custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
													$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

													if ($custom_field_value_info) {
														$data['account_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
													}
												}
											}

											if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
												$data['account_custom_fields'][] = $custom_field['name'] .' - '. $order_info['custom_field'][$custom_field['custom_field_id']];
											}

											if ($custom_field['type'] == 'file') {
												$upload_info = $this->model_tool_upload->getUploadByCode($order_info['custom_field'][$custom_field['custom_field_id']]);

												if ($upload_info) {
													$data['account_custom_fields'][] = $custom_field['name'] .' - '. $upload_info['name'];
												}
											}
										}
									}
								}

								$account_custom_fields = implode('; ', $data['account_custom_fields']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $account_custom_fields);
							}

							// Payment Address
							if ($find_paymentaddress) {
								if ($order_info['payment_address_format']) {
									$format = $order_info['payment_address_format'];
								} else {
									$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
								}

								$find = [
									'{firstname}',
									'{lastname}',
									'{company}',
									'{address_1}',
									'{address_2}',
									'{city}',
									'{postcode}',
									'{zone}',
									'{zone_code}',
									'{country}'
								];

								$replace = [
									'firstname' => $order_info['payment_firstname'],
									'lastname'  => $order_info['payment_lastname'],
									'company'   => $order_info['payment_company'],
									'address_1' => $order_info['payment_address_1'],
									'address_2' => $order_info['payment_address_2'],
									'city'      => $order_info['payment_city'],
									'postcode'  => $order_info['payment_postcode'],
									'zone'      => $order_info['payment_zone'],
									'zone_code' => $order_info['payment_zone_code'],
									'country'   => $order_info['payment_country']
								];

								$payment_address = str_replace(["\r\n", "\r", "\n"], " :: ", preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], ' :: ', trim(str_replace($find, $replace, $format))));

								$objPHPExcel->getActiveSheet()->setCellValue($char_value .$i, $payment_address)->getColumnDimension($char_value)->setAutoSize(true);
								$objPHPExcel->getActiveSheet()->getStyle($char_value++ .$i)->getAlignment()->setWrapText(true);
							}

							// Payment Custom Field
							if ($find_customfields) {
								$data['payment_custom_fields'] = [];

								foreach ($custom_fields as $custom_field) {
									if ($custom_field['location'] == 'address' && isset($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
										if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
											$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

											if ($custom_field_value_info) {
												$data['payment_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
											}
										}

										if ($custom_field['type'] == 'checkbox' && is_array($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
											foreach ($order_info['payment_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
												$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

												if ($custom_field_value_info) {
													$data['payment_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
												}
											}
										}

										if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
											$data['payment_custom_fields'][] = $custom_field['name'] .' - '. $order_info['payment_custom_field'][$custom_field['custom_field_id']];
										}

										if ($custom_field['type'] == 'file') {
											$upload_info = $this->model_tool_upload->getUploadByCode($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

											if ($upload_info) {
												$data['payment_custom_fields'][] = $upload_info['name'] .' - '. $upload_info['name'];
											}
										}
									}
								}

								$payment_custom_fields = implode('; ', $data['payment_custom_fields']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $payment_custom_fields);
							}

							// Shipping Address
							if ($find_shippingaddress) {
								// Shipping Address
								if ($order_info['shipping_address_format']) {
									$format = $order_info['shipping_address_format'];
								} else {
									$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
								}

								$find = [
									'{firstname}',
									'{lastname}',
									'{company}',
									'{address_1}',
									'{address_2}',
									'{city}',
									'{postcode}',
									'{zone}',
									'{zone_code}',
									'{country}'
								];

								$replace = [
									'firstname' => $order_info['shipping_firstname'],
									'lastname'  => $order_info['shipping_lastname'],
									'company'   => $order_info['shipping_company'],
									'address_1' => $order_info['shipping_address_1'],
									'address_2' => $order_info['shipping_address_2'],
									'city'      => $order_info['shipping_city'],
									'postcode'  => $order_info['shipping_postcode'],
									'zone'      => $order_info['shipping_zone'],
									'zone_code' => $order_info['shipping_zone_code'],
									'country'   => $order_info['shipping_country']
								];

								$shipping_address = str_replace(["\r\n", "\r", "\n"], " :: ", preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], ' :: ', trim(str_replace($find, $replace, $format))));

								$objPHPExcel->getActiveSheet()->setCellValue($char_value .$i, $shipping_address)->getColumnDimension($char_value)->setAutoSize(true);
								$objPHPExcel->getActiveSheet()->getStyle($char_value++ .$i)->getAlignment()->setWrapText(true);
							}

							// Shipping Custom Field
							if ($find_customfields) {
								$data['shipping_custom_fields'] = [];

								foreach ($custom_fields as $custom_field) {
									if ($custom_field['location'] == 'address' && isset($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
										if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
											$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

											if ($custom_field_value_info) {
												$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
											}
										}

										if ($custom_field['type'] == 'checkbox' && is_array($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
											foreach ($order_info['shipping_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
												$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

												if ($custom_field_value_info) {
													$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
												}
											}
										}

										if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
											$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $order_info['shipping_custom_field'][$custom_field['custom_field_id']];
										}

										if ($custom_field['type'] == 'file') {
											$upload_info = $this->model_tool_upload->getUploadByCode($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

											if ($upload_info) {
												$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $upload_info['name'];
											}
										}
									}
								}

								$shipping_custom_fields = implode('; ', $data['shipping_custom_fields']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $shipping_custom_fields);
							}

							// Order Details
							if ($find_orderdetail) {
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['payment_method']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['payment_code']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['shipping_method']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['shipping_code']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['comment']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']));
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['order_status_id']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['order_status']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['affiliate_id']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['commission']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['marketing_id']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['tracking']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['language_id']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['currency_code']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['currency_value']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['ip']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['forwarded_ip']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['user_agent']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['accept_language']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['date_added']);
								$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $order_info['date_modified']);
							}

							if (!empty($find_extrafields)) {
								foreach ($find_extrafields as $find_extrafield) {
									$find_extrafield = array_map('trim', explode('::', $find_extrafield));
									if (isset($find_extrafield[0]) && isset($find_extrafield[1])) {
										$objPHPExcel->getActiveSheet()->setCellValue($char_value++ .$i, $result[$find_extrafield[1]]);
									}
								}
							}
						}
					}

					// Find Format
					if ($find_format == 'xls') {
						$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xls($objPHPExcel);
						$file_name = 'OrderList.xls';
					} elseif ($find_format == 'xlsx') {
						$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
						$file_name = 'OrderList.xlsx';
					} elseif ($find_format == 'csv') {
						$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($objPHPExcel);
						$file_name = 'OrderList.csv';
					} else {
						$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
						$file_name = 'OrderList.xlsx';
					}

					$file_to_save = DIR_UPLOAD . $file_name;
					$objWriter->save(DIR_UPLOAD . $file_name);

				}
			}

			if ('json' == $find_format) {
				$export_data = [];
				// add meta data in json file, if possible with php

				if ($results) {
					// Fetch Total Orders
					// $objPHPExcel->getActiveSheet()->setTitle(sprintf($this->language->get('export_title'), count($results)));

					$i = 0;
					foreach ($results as $result) {

						$order_info = $this->{'model_' . $this->model_extension . 'mpimporterexporter_export_order'}->getOrder($result['order_id']);

						if ($order_info) {

							if ($find_productdetail) {
								$order_products = $this->model_sale_order->getOrderProducts($order_info['order_id']);
								$order_products_data = [];
								$order_products_option_data = [];
								foreach ($order_products as $order_product) {

									$order_product['name'] = html_entity_decode($this->ifnull($order_product['name']), ENT_QUOTES, 'UTF-8');

									$order_options = $this->model_sale_order->getOrderOptions($order_product['order_id'], $order_product['order_product_id']);

									$order_options_data = '';
									$option_row = 1;
									foreach ($order_options as $order_option) {
										if ($option_row == '1') {
											$option_string = $order_product['name']. ' >> ';
										}else{
											$option_string = '';
										}

										$order_options_data .= $option_string . $order_option['name'] .' :: '. $order_option['value'];
										if (count($order_options) != $option_row) {
											$order_options_data .= ' || ';
										}

										$option_row++;
									}

									if ($order_options_data) {
										$order_products_option_data[] = $order_options_data;
									}

									$order_products_data[] = $order_product['name'] .' >> '. $order_product['model'] .' :: '. $order_product['quantity'] .' :: '. $this->currency->format($order_product['price'], $order_info['currency_code'], $order_info['currency_value']) .' :: '. $this->currency->format($order_product['tax'], $order_info['currency_code'], $order_info['currency_value']) .' :: '. $this->currency->format($order_product['total'], $order_info['currency_code'], $order_info['currency_value']);
								}

								if ($order_products_option_data) {
									$order_info['order_products_option_data'] = implode(';; ', $order_products_option_data);
								} else {
									$order_info['order_products_option_data'] = '';
								}

								if ($order_products_data) {
									$order_info['order_products'] = implode("; \n", $order_products_data);
								} else{
									$order_info['order_products'] = '';
								}
							}

							if ($find_voucherdetail) {
								$order_vouchers = $this->model_sale_order->getOrderVouchers($order_info['order_id']);
								$order_vouchers_data = [];

								foreach ($order_vouchers as $order_voucher) {
									$order_vouchers_data[] = $order_voucher['code'] .' :: '. $order_voucher['from_name'] .' :: '. $order_voucher['from_email'] .' :: '. $order_voucher['to_name'] .' :: '. $order_voucher['to_email'].' :: '. $order_voucher['message'];
								}

								if ($order_vouchers_data) {
									$order_info['order_vouchers_data'] = implode("; \n", $order_vouchers_data);
								} else{
									$order_info['order_vouchers_data'] = '';
								}
							}

							$export_data[$i]['order_id'] = [
									'text' => $this->language->get('export_order_id'),
									'value' => $order_info['order_id']
							];

							// Order Details
							if ($find_orderdetail) {
								$export_data[$i]['invoice_prefix'] = [
										'text' => $this->language->get('export_invoice_prefix'),
										'value' => $order_info['invoice_prefix']
								];
								$export_data[$i]['invoice_no'] = [
										'text' => $this->language->get('export_invoice_no'),
										'value' => $order_info['invoice_no']
								];
								$export_data[$i]['store_id'] = [
										'text' => $this->language->get('export_store_id'),
										'value' => $order_info['store_id']
								];
								$export_data[$i]['store_name'] = [
										'text' => $this->language->get('export_store_name'),
										'value' => $order_info['store_name']
								];
								$export_data[$i]['store_url'] = [
										'text' => $this->language->get('export_store_url'),
										'value' => $order_info['store_url']
								];
							}

							// Customer Details
							if ($find_customerdetail) {
								$export_data[$i]['customer_id'] = [
										'text' => $this->language->get('export_customer_id'),
										'value' => $order_info['customer_id']
								];
								$export_data[$i]['customer_name'] = [
										'text' => $this->language->get('export_customer'),
										'value' => $order_info['customer']
								];
								$export_data[$i]['email'] = [
										'text' => $this->language->get('export_email'),
										'value' => $order_info['email']
								];
								$export_data[$i]['telephone'] = [
										'text' => $this->language->get('export_telephone'),
										'value' => $order_info['telephone']
								];
								$export_data[$i]['fax'] = [
										'text' => $this->language->get('export_fax'),
										'value' => $order_info['fax']
								];
							}

							// Product Details
							if ($find_productdetail) {

								$export_data[$i]['order_products'] = [
										'text' => $this->language->get('export_order_products'),
										'value' => $order_info['order_products']
								];

								$export_data[$i]['order_products_options'] = [
										'text' => $this->language->get('export_order_options'),
										'value' => $order_info['order_products_option_data']
								];

							}

							// Voucher Details
							if ($find_voucherdetail) {
								$export_data[$i]['order_vouchers'] = [
										'text' => $this->language->get('export_order_vouchers'),
										'value' => $order_info['order_vouchers_data']
								];
							}

							// Order Totals Details
							if ($find_ordertotals) {
								$order_totals = $this->model_sale_order->getOrderTotals($order_info['order_id']);
								$order_totals_data = [];
								foreach ($order_totals as $order_total) {
								$order_total['title'] = html_entity_decode($this->ifnull($order_total['title']), ENT_QUOTES, 'UTF-8');
									$order_totals_data[] = $order_total['title'] .' - '. $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']);
								}

								$order_info['order_totals'] = implode("; \n", $order_totals_data);

								$export_data[$i]['order_totals'] = [
										'text' => $this->language->get('export_order_totals'),
										'value' => $order_info['order_totals']
								];

							}

							// Order Custom Field
							if ($find_customfields) {
								$data['account_custom_fields'] = [];
								if ($order_info['custom_field']) {
									foreach ($custom_fields as $custom_field) {
										if ($custom_field['location'] == 'account' && isset($order_info['custom_field'][$custom_field['custom_field_id']])) {
											if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
												$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['custom_field'][$custom_field['custom_field_id']]);

												if ($custom_field_value_info) {
													$data['account_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
												}
											}

											if ($custom_field['type'] == 'checkbox' && is_array($order_info['custom_field'][$custom_field['custom_field_id']])) {
												foreach ($order_info['custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
													$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

													if ($custom_field_value_info) {
														$data['account_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
													}
												}
											}

											if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
												$data['account_custom_fields'][] = $custom_field['name'] .' - '. $order_info['custom_field'][$custom_field['custom_field_id']];
											}

											if ($custom_field['type'] == 'file') {
												$upload_info = $this->model_tool_upload->getUploadByCode($order_info['custom_field'][$custom_field['custom_field_id']]);

												if ($upload_info) {
													$data['account_custom_fields'][] = $custom_field['name'] .' - '. $upload_info['name'];
												}
											}
										}
									}
								}

								$account_custom_fields = implode('; ', $data['account_custom_fields']);

								$export_data[$i]['account_custom_fields'] = [
										'text' => $this->language->get('export_customfields'),
										'value' => $account_custom_fields
								];
							}

							// Payment Address
							if ($find_paymentaddress) {
								if ($order_info['payment_address_format']) {
									$format = $order_info['payment_address_format'];
								} else {
									$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
								}

								$find = [
									'{firstname}',
									'{lastname}',
									'{company}',
									'{address_1}',
									'{address_2}',
									'{city}',
									'{postcode}',
									'{zone}',
									'{zone_code}',
									'{country}'
								];

								$replace = [
									'firstname' => $order_info['payment_firstname'],
									'lastname'  => $order_info['payment_lastname'],
									'company'   => $order_info['payment_company'],
									'address_1' => $order_info['payment_address_1'],
									'address_2' => $order_info['payment_address_2'],
									'city'      => $order_info['payment_city'],
									'postcode'  => $order_info['payment_postcode'],
									'zone'      => $order_info['payment_zone'],
									'zone_code' => $order_info['payment_zone_code'],
									'country'   => $order_info['payment_country']
								];

								$payment_address = str_replace(["\r\n", "\r", "\n"], " :: ", preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], ' :: ', trim(str_replace($find, $replace, $format))));

								$export_data[$i]['payment_address'] = [
										'text' => $this->language->get('export_paymentaddress'),
										'value' => $payment_address
								];

							}

							// Payment Custom Field
							if ($find_customfields) {
								$data['payment_custom_fields'] = [];

								foreach ($custom_fields as $custom_field) {
									if ($custom_field['location'] == 'address' && isset($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
										if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
											$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

											if ($custom_field_value_info) {
												$data['payment_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
											}
										}

										if ($custom_field['type'] == 'checkbox' && is_array($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
											foreach ($order_info['payment_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
												$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

												if ($custom_field_value_info) {
													$data['payment_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
												}
											}
										}

										if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
											$data['payment_custom_fields'][] = $custom_field['name'] .' - '. $order_info['payment_custom_field'][$custom_field['custom_field_id']];
										}

										if ($custom_field['type'] == 'file') {
											$upload_info = $this->model_tool_upload->getUploadByCode($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

											if ($upload_info) {
												$data['payment_custom_fields'][] = $upload_info['name'] .' - '. $upload_info['name'];
											}
										}
									}
								}

								$payment_custom_fields = implode('; ', $data['payment_custom_fields']);

								$export_data[$i]['payment_custom_fields'] = [
										'text' => $this->language->get('export_paymentcustomfields'),
										'value' => $payment_custom_fields
								];

							}

							// Shipping Address
							if ($find_shippingaddress) {
								// Shipping Address
								if ($order_info['shipping_address_format']) {
									$format = $order_info['shipping_address_format'];
								} else {
									$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
								}

								$find = [
									'{firstname}',
									'{lastname}',
									'{company}',
									'{address_1}',
									'{address_2}',
									'{city}',
									'{postcode}',
									'{zone}',
									'{zone_code}',
									'{country}'
								];

								$replace = [
									'firstname' => $order_info['shipping_firstname'],
									'lastname'  => $order_info['shipping_lastname'],
									'company'   => $order_info['shipping_company'],
									'address_1' => $order_info['shipping_address_1'],
									'address_2' => $order_info['shipping_address_2'],
									'city'      => $order_info['shipping_city'],
									'postcode'  => $order_info['shipping_postcode'],
									'zone'      => $order_info['shipping_zone'],
									'zone_code' => $order_info['shipping_zone_code'],
									'country'   => $order_info['shipping_country']
								];

								$shipping_address = str_replace(["\r\n", "\r", "\n"], " :: ", preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], ' :: ', trim(str_replace($find, $replace, $format))));

								$export_data[$i]['shipping_address'] = [
										'text' => $this->language->get('export_shippingaddress'),
										'value' => $shipping_address
								];

							}

							// Shipping Custom Field
							if ($find_customfields) {
								$data['shipping_custom_fields'] = [];

								foreach ($custom_fields as $custom_field) {
									if ($custom_field['location'] == 'address' && isset($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
										if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
											$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

											if ($custom_field_value_info) {
												$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
											}
										}

										if ($custom_field['type'] == 'checkbox' && is_array($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
											foreach ($order_info['shipping_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
												$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

												if ($custom_field_value_info) {
													$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
												}
											}
										}

										if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
											$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $order_info['shipping_custom_field'][$custom_field['custom_field_id']];
										}

										if ($custom_field['type'] == 'file') {
											$upload_info = $this->model_tool_upload->getUploadByCode($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

											if ($upload_info) {
												$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $upload_info['name'];
											}
										}
									}
								}

								$shipping_custom_fields = implode('; ', $data['shipping_custom_fields']);

								$export_data[$i]['shipping_custom_fields'] = [
										'text' => $this->language->get('export_shipping_customfields'),
										'value' => $shipping_custom_fields
								];

							}

							// Order Details
							if ($find_orderdetail) {

								$export_data[$i]['payment_method'] = [
										'text' => $this->language->get('export_payment_method'),
										'value' => $order_info['payment_method']
								];
								$export_data[$i]['payment_code'] = [
										'text' => $this->language->get('export_payment_code'),
										'value' => $order_info['payment_code']
								];
								$export_data[$i]['shipping_method'] = [
										'text' => $this->language->get('export_shipping_method'),
										'value' => $order_info['shipping_method']
								];
								$export_data[$i]['shipping_code'] = [
										'text' => $this->language->get('export_shipping_code'),
										'value' => $order_info['shipping_code']
								];
								$export_data[$i]['comment'] = [
										'text' => $this->language->get('export_comment'),
										'value' => $order_info['comment']
								];
								$export_data[$i]['total'] = [
										'text' => $this->language->get('export_total'),
										'value' => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'])
								];
								$export_data[$i]['order_status_id'] = [
										'text' => $this->language->get('export_order_status_id'),
										'value' => $order_info['order_status_id']
								];
								$export_data[$i]['order_status'] = [
										'text' => $this->language->get('export_order_status'),
										'value' => $order_info['order_status']
								];
								$export_data[$i]['affiliate_id'] = [
										'text' => $this->language->get('export_affiliate_id'),
										'value' => $order_info['affiliate_id']
								];
								$export_data[$i]['commission'] = [
										'text' => $this->language->get('export_commission'),
										'value' => $order_info['commission']
								];
								$export_data[$i]['marketing_id'] = [
										'text' => $this->language->get('export_marketing_id'),
										'value' => $order_info['marketing_id']
								];
								$export_data[$i]['tracking'] = [
										'text' => $this->language->get('export_tracking'),
										'value' => $order_info['tracking']
								];
								$export_data[$i]['language_id'] = [
										'text' => $this->language->get('export_language_id'),
										'value' => $order_info['language_id']
								];
								$export_data[$i]['currency_code'] = [
										'text' => $this->language->get('export_currency_code'),
										'value' => $order_info['currency_code']
								];
								$export_data[$i]['currency_value'] = [
										'text' => $this->language->get('export_currency_value'),
										'value' => $order_info['currency_value']
								];
								$export_data[$i]['ip'] = [
										'text' => $this->language->get('export_ip'),
										'value' => $order_info['ip']
								];
								$export_data[$i]['forwarded_ip'] = [
										'text' => $this->language->get('export_forwarded_ip'),
										'value' => $order_info['forwarded_ip']
								];
								$export_data[$i]['user_agent'] = [
										'text' => $this->language->get('export_user_agent'),
										'value' => $order_info['user_agent']
								];
								$export_data[$i]['accept_language'] = [
										'text' => $this->language->get('export_accept_language'),
										'value' => $order_info['accept_language']
								];
								$export_data[$i]['date_added'] = [
										'text' => $this->language->get('export_date_added'),
										'value' => $order_info['date_added']
								];
								$export_data[$i]['date_modified'] = [
										'text' => $this->language->get('export_date_modified'),
										'value' => $order_info['date_modified']
								];
							}

							if (!empty($find_extrafields)) {
								foreach ($find_extrafields as $find_extrafield) {
									$find_extrafield = array_map('trim', explode('::', $find_extrafield));
									if (isset($find_extrafield[0]) && isset($find_extrafield[1])) {
										$export_data[$i][$find_extrafield[0].'.'.$find_extrafield[1]] = [
												'text' => $find_extrafield[0].'::'.$find_extrafield[1],
												'value' => $result[$find_extrafield[1]]
										];

									}
								}
							}
							$i++;
						}
					}

					$file_name = 'OrderList.json';

					$file_to_save = DIR_UPLOAD . $file_name;

					$handle = fopen($file_to_save, "w");

					fwrite($handle, json_encode($export_data, JSON_PRETTY_PRINT));
					fclose($handle);

				}

			}

			if ('xml' == $find_format) {
				// add meta data in xml file, if possible with php
				$export_data = [];
				if ($results) {

					$xml = new \DOMDocument('1.0', 'UTF-8');

			    $xml->preserveWhiteSpace = false;
					$xml->formatOutput=true;

					$xml_orders = $xml->createElement("orders");
					$xml->appendChild($xml_orders);

					// Fetch Total Orders
					// $objPHPExcel->getActiveSheet()->setTitle(sprintf($this->language->get('export_title'), count($results)));

					foreach ($results as $result) {
						$export_data = [];
						$i = 0;
						$order_info = $this->{'model_' . $this->model_extension . 'mpimporterexporter_export_order'}->getOrder($result['order_id']);

						if ($order_info) {

							if ($find_productdetail) {
								$order_products = $this->model_sale_order->getOrderProducts($order_info['order_id']);
								$order_products_data = [];
								$order_products_option_data = [];
								foreach ($order_products as $order_product) {

									$order_product['name'] = html_entity_decode($this->ifnull($order_product['name']), ENT_QUOTES, 'UTF-8');

									$order_options = $this->model_sale_order->getOrderOptions($order_product['order_id'], $order_product['order_product_id']);

									$order_options_data = '';
									$option_row = 1;
									foreach ($order_options as $order_option) {
										if ($option_row == '1') {
											$option_string = $order_product['name']. ' >> ';
										}else{
											$option_string = '';
										}

										$order_options_data .= $option_string . $order_option['name'] .' :: '. $order_option['value'];
										if (count($order_options) != $option_row) {
											$order_options_data .= ' || ';
										}

										$option_row++;
									}

									if ($order_options_data) {
										$order_products_option_data[] = $order_options_data;
									}

									$order_products_data[] = $order_product['name'] .' >> '. $order_product['model'] .' :: '. $order_product['quantity'] .' :: '. $this->currency->format($order_product['price'], $order_info['currency_code'], $order_info['currency_value']) .' :: '. $this->currency->format($order_product['tax'], $order_info['currency_code'], $order_info['currency_value']) .' :: '. $this->currency->format($order_product['total'], $order_info['currency_code'], $order_info['currency_value']);
								}

								if ($order_products_option_data) {
									$order_info['order_products_option_data'] = implode(';; ', $order_products_option_data);
								} else {
									$order_info['order_products_option_data'] = '';
								}

								if ($order_products_data) {
									$order_info['order_products'] = implode("; \n", $order_products_data);
								} else{
									$order_info['order_products'] = '';
								}
							}

							if ($find_voucherdetail) {
								$order_vouchers = $this->model_sale_order->getOrderVouchers($order_info['order_id']);
								$order_vouchers_data = [];

								foreach ($order_vouchers as $order_voucher) {
									$order_vouchers_data[] = $order_voucher['code'] .' :: '. $order_voucher['from_name'] .' :: '. $order_voucher['from_email'] .' :: '. $order_voucher['to_name'] .' :: '. $order_voucher['to_email'].' :: '. $order_voucher['message'];
								}

								if ($order_vouchers_data) {
									$order_info['order_vouchers_data'] = implode("; \n", $order_vouchers_data);
								} else{
									$order_info['order_vouchers_data'] = '';
								}
							}

							$export_data[$i]['order_id'] = [
									'text' => $this->language->get('export_order_id'),
									'value' => $order_info['order_id']
							];

							// Order Details
							if ($find_orderdetail) {
								$export_data[$i]['invoice_prefix'] = [
										'text' => $this->language->get('export_invoice_prefix'),
										'value' => $order_info['invoice_prefix']
								];
								$export_data[$i]['invoice_no'] = [
										'text' => $this->language->get('export_invoice_no'),
										'value' => $order_info['invoice_no']
								];
								$export_data[$i]['store_id'] = [
										'text' => $this->language->get('export_store_id'),
										'value' => $order_info['store_id']
								];
								$export_data[$i]['store_name'] = [
										'text' => $this->language->get('export_store_name'),
										'value' => $order_info['store_name']
								];
								$export_data[$i]['store_url'] = [
										'text' => $this->language->get('export_store_url'),
										'value' => $order_info['store_url']
								];
							}

							// Customer Details
							if ($find_customerdetail) {
								$export_data[$i]['customer_id'] = [
										'text' => $this->language->get('export_customer_id'),
										'value' => $order_info['customer_id']
								];
								$export_data[$i]['customer_name'] = [
										'text' => $this->language->get('export_customer'),
										'value' => $order_info['customer']
								];
								$export_data[$i]['email'] = [
										'text' => $this->language->get('export_email'),
										'value' => $order_info['email']
								];
								$export_data[$i]['telephone'] = [
										'text' => $this->language->get('export_telephone'),
										'value' => $order_info['telephone']
								];
								$export_data[$i]['fax'] = [
										'text' => $this->language->get('export_fax'),
										'value' => $order_info['fax']
								];
							}

							// Product Details
							if ($find_productdetail) {

								$export_data[$i]['order_products'] = [
										'text' => $this->language->get('export_order_products'),
										'value' => $order_info['order_products']
								];

								$export_data[$i]['order_products_options'] = [
										'text' => $this->language->get('export_order_options'),
										'value' => $order_info['order_products_option_data']
								];

							}

							// Voucher Details
							if ($find_voucherdetail) {
								$export_data[$i]['order_vouchers'] = [
										'text' => $this->language->get('export_order_vouchers'),
										'value' => $order_info['order_vouchers_data']
								];
							}

							// Order Totals Details
							if ($find_ordertotals) {
								$order_totals = $this->model_sale_order->getOrderTotals($order_info['order_id']);
								$order_totals_data = [];
								foreach ($order_totals as $order_total) {
								$order_total['title'] = html_entity_decode($this->ifnull($order_total['title']), ENT_QUOTES, 'UTF-8');
									$order_totals_data[] = $order_total['title'] .' - '. $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']);
								}

								$order_info['order_totals'] = implode("; \n", $order_totals_data);

								$export_data[$i]['order_totals'] = [
										'text' => $this->language->get('export_order_totals'),
										'value' => $order_info['order_totals']
								];

							}

							// Order Custom Field
							if ($find_customfields) {
								$data['account_custom_fields'] = [];
								if ($order_info['custom_field']) {
									foreach ($custom_fields as $custom_field) {
										if ($custom_field['location'] == 'account' && isset($order_info['custom_field'][$custom_field['custom_field_id']])) {
											if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
												$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['custom_field'][$custom_field['custom_field_id']]);

												if ($custom_field_value_info) {
													$data['account_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
												}
											}

											if ($custom_field['type'] == 'checkbox' && is_array($order_info['custom_field'][$custom_field['custom_field_id']])) {
												foreach ($order_info['custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
													$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

													if ($custom_field_value_info) {
														$data['account_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
													}
												}
											}

											if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
												$data['account_custom_fields'][] = $custom_field['name'] .' - '. $order_info['custom_field'][$custom_field['custom_field_id']];
											}

											if ($custom_field['type'] == 'file') {
												$upload_info = $this->model_tool_upload->getUploadByCode($order_info['custom_field'][$custom_field['custom_field_id']]);

												if ($upload_info) {
													$data['account_custom_fields'][] = $custom_field['name'] .' - '. $upload_info['name'];
												}
											}
										}
									}
								}

								$account_custom_fields = implode('; ', $data['account_custom_fields']);

								$export_data[$i]['account_custom_fields'] = [
										'text' => $this->language->get('export_customfields'),
										'value' => $account_custom_fields
								];
							}

							// Payment Address
							if ($find_paymentaddress) {
								if ($order_info['payment_address_format']) {
									$format = $order_info['payment_address_format'];
								} else {
									$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
								}

								$find = [
									'{firstname}',
									'{lastname}',
									'{company}',
									'{address_1}',
									'{address_2}',
									'{city}',
									'{postcode}',
									'{zone}',
									'{zone_code}',
									'{country}'
								];

								$replace = [
									'firstname' => $order_info['payment_firstname'],
									'lastname'  => $order_info['payment_lastname'],
									'company'   => $order_info['payment_company'],
									'address_1' => $order_info['payment_address_1'],
									'address_2' => $order_info['payment_address_2'],
									'city'      => $order_info['payment_city'],
									'postcode'  => $order_info['payment_postcode'],
									'zone'      => $order_info['payment_zone'],
									'zone_code' => $order_info['payment_zone_code'],
									'country'   => $order_info['payment_country']
								];

								$payment_address = str_replace(["\r\n", "\r", "\n"], " :: ", preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], ' :: ', trim(str_replace($find, $replace, $format))));

								$export_data[$i]['payment_address'] = [
										'text' => $this->language->get('export_paymentaddress'),
										'value' => $payment_address
								];

							}

							// Payment Custom Field
							if ($find_customfields) {
								$data['payment_custom_fields'] = [];

								foreach ($custom_fields as $custom_field) {
									if ($custom_field['location'] == 'address' && isset($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
										if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
											$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

											if ($custom_field_value_info) {
												$data['payment_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
											}
										}

										if ($custom_field['type'] == 'checkbox' && is_array($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
											foreach ($order_info['payment_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
												$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

												if ($custom_field_value_info) {
													$data['payment_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
												}
											}
										}

										if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
											$data['payment_custom_fields'][] = $custom_field['name'] .' - '. $order_info['payment_custom_field'][$custom_field['custom_field_id']];
										}

										if ($custom_field['type'] == 'file') {
											$upload_info = $this->model_tool_upload->getUploadByCode($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

											if ($upload_info) {
												$data['payment_custom_fields'][] = $upload_info['name'] .' - '. $upload_info['name'];
											}
										}
									}
								}

								$payment_custom_fields = implode('; ', $data['payment_custom_fields']);

								$export_data[$i]['payment_custom_fields'] = [
										'text' => $this->language->get('export_paymentcustomfields'),
										'value' => $payment_custom_fields
								];

							}

							// Shipping Address
							if ($find_shippingaddress) {
								// Shipping Address
								if ($order_info['shipping_address_format']) {
									$format = $order_info['shipping_address_format'];
								} else {
									$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
								}

								$find = [
									'{firstname}',
									'{lastname}',
									'{company}',
									'{address_1}',
									'{address_2}',
									'{city}',
									'{postcode}',
									'{zone}',
									'{zone_code}',
									'{country}'
								];

								$replace = [
									'firstname' => $order_info['shipping_firstname'],
									'lastname'  => $order_info['shipping_lastname'],
									'company'   => $order_info['shipping_company'],
									'address_1' => $order_info['shipping_address_1'],
									'address_2' => $order_info['shipping_address_2'],
									'city'      => $order_info['shipping_city'],
									'postcode'  => $order_info['shipping_postcode'],
									'zone'      => $order_info['shipping_zone'],
									'zone_code' => $order_info['shipping_zone_code'],
									'country'   => $order_info['shipping_country']
								];

								$shipping_address = str_replace(["\r\n", "\r", "\n"], " :: ", preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], ' :: ', trim(str_replace($find, $replace, $format))));

								$export_data[$i]['shipping_address'] = [
										'text' => $this->language->get('export_shippingaddress'),
										'value' => $shipping_address
								];

							}

							// Shipping Custom Field
							if ($find_customfields) {
								$data['shipping_custom_fields'] = [];

								foreach ($custom_fields as $custom_field) {
									if ($custom_field['location'] == 'address' && isset($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
										if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
											$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

											if ($custom_field_value_info) {
												$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
											}
										}

										if ($custom_field['type'] == 'checkbox' && is_array($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
											foreach ($order_info['shipping_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
												$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

												if ($custom_field_value_info) {
													$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $custom_field_value_info['name'];
												}
											}
										}

										if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
											$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $order_info['shipping_custom_field'][$custom_field['custom_field_id']];
										}

										if ($custom_field['type'] == 'file') {
											$upload_info = $this->model_tool_upload->getUploadByCode($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

											if ($upload_info) {
												$data['shipping_custom_fields'][] = $custom_field['name'] .' - '. $upload_info['name'];
											}
										}
									}
								}

								$shipping_custom_fields = implode('; ', $data['shipping_custom_fields']);

								$export_data[$i]['shipping_custom_fields'] = [
										'text' => $this->language->get('export_shipping_customfields'),
										'value' => $shipping_custom_fields
								];

							}

							// Order Details
							if ($find_orderdetail) {

								$export_data[$i]['payment_method'] = [
										'text' => $this->language->get('export_payment_method'),
										'value' => $order_info['payment_method']
								];
								$export_data[$i]['payment_code'] = [
										'text' => $this->language->get('export_payment_code'),
										'value' => $order_info['payment_code']
								];
								$export_data[$i]['shipping_method'] = [
										'text' => $this->language->get('export_shipping_method'),
										'value' => $order_info['shipping_method']
								];
								$export_data[$i]['shipping_code'] = [
										'text' => $this->language->get('export_shipping_code'),
										'value' => $order_info['shipping_code']
								];
								$export_data[$i]['comment'] = [
										'text' => $this->language->get('export_comment'),
										'value' => $order_info['comment']
								];
								$export_data[$i]['total'] = [
										'text' => $this->language->get('export_total'),
										'value' => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'])
								];
								$export_data[$i]['order_status_id'] = [
										'text' => $this->language->get('export_order_status_id'),
										'value' => $order_info['order_status_id']
								];
								$export_data[$i]['order_status'] = [
										'text' => $this->language->get('export_order_status'),
										'value' => $order_info['order_status']
								];
								$export_data[$i]['affiliate_id'] = [
										'text' => $this->language->get('export_affiliate_id'),
										'value' => $order_info['affiliate_id']
								];
								$export_data[$i]['commission'] = [
										'text' => $this->language->get('export_commission'),
										'value' => $order_info['commission']
								];
								$export_data[$i]['marketing_id'] = [
										'text' => $this->language->get('export_marketing_id'),
										'value' => $order_info['marketing_id']
								];
								$export_data[$i]['tracking'] = [
										'text' => $this->language->get('export_tracking'),
										'value' => $order_info['tracking']
								];
								$export_data[$i]['language_id'] = [
										'text' => $this->language->get('export_language_id'),
										'value' => $order_info['language_id']
								];
								$export_data[$i]['currency_code'] = [
										'text' => $this->language->get('export_currency_code'),
										'value' => $order_info['currency_code']
								];
								$export_data[$i]['currency_value'] = [
										'text' => $this->language->get('export_currency_value'),
										'value' => $order_info['currency_value']
								];
								$export_data[$i]['ip'] = [
										'text' => $this->language->get('export_ip'),
										'value' => $order_info['ip']
								];
								$export_data[$i]['forwarded_ip'] = [
										'text' => $this->language->get('export_forwarded_ip'),
										'value' => $order_info['forwarded_ip']
								];
								$export_data[$i]['user_agent'] = [
										'text' => $this->language->get('export_user_agent'),
										'value' => $order_info['user_agent']
								];
								$export_data[$i]['accept_language'] = [
										'text' => $this->language->get('export_accept_language'),
										'value' => $order_info['accept_language']
								];
								$export_data[$i]['date_added'] = [
										'text' => $this->language->get('export_date_added'),
										'value' => $order_info['date_added']
								];
								$export_data[$i]['date_modified'] = [
										'text' => $this->language->get('export_date_modified'),
										'value' => $order_info['date_modified']
								];
							}

							if (!empty($find_extrafields)) {
								foreach ($find_extrafields as $find_extrafield) {
									$find_extrafield = array_map('trim', explode('::', $find_extrafield));
									if (isset($find_extrafield[0]) && isset($find_extrafield[1])) {
										$export_data[$i][$find_extrafield[0].'.'.$find_extrafield[1]] = [
												'text' => $find_extrafield[0].'::'.$find_extrafield[1],
												'value' => $result[$find_extrafield[1]]
										];

									}
								}
							}

						}

						$xml_order = $xml->createElement("order");
						$xml_orders->appendChild($xml_order);
						foreach ($export_data[$i] as $key => $edata) {
							if ($edata['value'] == '') {
								$edata['value'] = " ";
							}
							// if ($key == 'custom_field') {
							// 	continue;
							// }

							$xml_edata = $xml->createElement($key, htmlspecialchars($edata['value'], ENT_QUOTES, 'UTF-8'));
							// $xml_edata->setAttribute("text", $edata['text']);
							$xml_order->appendChild($xml_edata);

							// $xml_attr = $xml->createAttribute('text');
							// $xml_attr->value = $edata['text'];
							// $xml_edata->appendChild($xml_attr);
						}
					}

					$file_name = 'OrderList.xml';
					$file_to_save = DIR_UPLOAD . $file_name;

					// echo $xml->saveXML();
					$xml->save($file_to_save);
				}
			}

			if ($results) {
				$json['href'] = str_replace('&amp;', '&', $this->url->link($this->isdir_extension . 'mpimporterexporter/export_order/fileDownload', $this->token . '='. $this->session->data[$this->token] .'&file_name='. $file_name .'&find_format='. $find_format, true));

				$json['success'] = $this->language->get('text_success');
			} else {
				$json['error'] = $this->language->get('text_no_results');

			}
		} else{
			$json['error'] = $this->language->get('error_onerequired');
		}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function accessValidate() {
		if (!$this->user->hasPermission('access', $this->isdir_extension . 'mpimporterexporter/export_order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}

if (VERSION <= '2.2.0.0') {
	class ControllerMpImporterExporterExportOrder extends ControllerExtensionMpImporterExporterExportOrder { }
}
