<?php
class ControllerVendorLatestOrder extends Controller
{
	public function index()
	{
		$this->load->language('vendor/latestorder');
		$this->load->model('vendor/vendor');
		$this->load->model('tool/image');

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_view'] = $this->language->get('text_view');
		$data['text_select'] = $this->language->get('text_select');
		$data['button_view'] = $this->language->get('button_view');
		$data['button_save'] = $this->language->get('button_save');
		$data['text_viewallorders'] = $this->language->get('text_viewallorders');
		$data['viewallorder'] = $this->url->link('vendor/order_report', '', true);

		$data['column_noofproduct'] = $this->language->get('column_noofproduct');

		$this->load->model('localisation/order_status');
		$data['order_statuss'] = $this->model_localisation_order_status->getOrderStatuses($data);

		$data['orders'] = array();
		$filter_data = array(
			'vendor_id' => $this->vendor->getId(),
			'customer_id' => $this->customer->getId(),
			'sort' => 'date_added',
			'order' => 'DESC',
		);

		$orders = $this->model_vendor_vendor->getOrders($filter_data);

		foreach ($orders as $order) {
			$status_info = $this->model_vendor_vendor->getOrderStatus($order['order_status_id']);
			$product_total = $this->model_vendor_vendor->getTotalOrderProductsByOrderId($order['order_id'], $order['vendor_id']);
			$vorder_total = $this->model_vendor_vendor->getvendorOrdertotal($order['vendor_id'], $order['order_id']);

			if (isset($status_info['name'])) {
				$statusname = $status_info['name'];
			} else {
				$statusname = '';
			}

			// Get products for the order
			$products = $this->model_vendor_vendor->getOrderProducts($order['order_id']);

			$product_data = [];
			foreach ($products as $product) {
				$image = $this->model_tool_image->resize($product['image'], 50, 50);
				$product_data[] = [
					'name'  => $product['name'],
					'image' => $image
				];
			}

			$data['orders'][] = array(
				'order_product_id' => $order['order_product_id'],
				'customer_id' => $order['customer_id'],
				'order_status_id' => $order['order_status_id'],
				'order_id' => $order['order_id'],
				'firstname' => $order['firstname'],
				'lastname' => $order['lastname'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($order['date_added'])),
				'total' => $this->currency->format($vorder_total['total'], $this->config->get('config_currency')),
				'statusname' => $statusname,
				'noofproduct' => $product_total,
				'products' => $product_data,  // Pass product data (name & image) to the view
				'view' => $this->url->link('vendor/latestorder/letestview', '&order_id=' . $order['order_id'])
			);
		}

		$data['customer2vendor'] = $this->config->get('vendor_vendor2customer');
		$data['column_order_id'] = $this->language->get('column_order_id');
		$data['column_customer'] = $this->language->get('column_customer');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_date_added'] = $this->language->get('column_date_added');
		$data['column_total'] = $this->language->get('column_total');
		$data['column_action'] = $this->language->get('column_action');

		return $this->load->view('vendor/latestorder', $data);
	}


	public function letestview()
	{

		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		$this->load->language('vendor/latestorder');
		$this->load->model('tool/upload');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}


		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}


		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}
		
		$data['cancel'] = $this->url->link('vendor/all_order');

		$url = '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_view'),
			'href' => $this->url->link('vendor/dashboard')
		);

		$this->document->setTitle($this->language->get('heading_title1'));
		$data['heading_view']          = $this->language->get('heading_view');
		$data['text_view']           	= $this->language->get('text_view');
		$data['text_no_results'] 		= $this->language->get('text_no_results');
		$data['text_confirm']			= $this->language->get('text_confirm');
		$data['text_none'] 				= $this->language->get('text_none');
		$data['text_enable']            = $this->language->get('text_enable');
		$data['text_disable']           = $this->language->get('text_disable');
		$data['text_select']            = $this->language->get('text_select');
		$data['text_payment_address']   = $this->language->get('text_payment_address');
		$data['text_shipping_address']  = $this->language->get('text_shipping_address');
		$data['text_details']  			= $this->language->get('text_details');
		$data['text_order']  			= $this->language->get('text_order');
		$data['text_Payment']  			= $this->language->get('text_Payment');
		$data['text_shipping']  		= $this->language->get('text_shipping');
		$data['text_date']  			= $this->language->get('text_date');
		$data['column_order_id']	    = $this->language->get('column_order_id');
		$data['column_product']		    = $this->language->get('column_product');
		$data['column_model']		    = $this->language->get('column_model');
		$data['column_quantity']		= $this->language->get('column_quantity');
		$data['column_price']		    = $this->language->get('column_price');
		$data['column_total']		    = $this->language->get('column_total');
		$data['column_orderstatus']		= $this->language->get('column_orderstatus');
		$data['column_tracking']		= $this->language->get('column_tracking');
		$data['text_byseller']		    = $this->language->get('text_byseller');
		$data['button_invoice_print']	= $this->language->get('button_invoice_print');
		/* 07 04 2020 */
		$data['column_shipingamount']	  = $this->language->get('column_shipingamount');
		$data['chkshipcost'] = $this->config->get('shipping_shippingcost_status');
		/* 07 04 2020 */

		$data['help_trackingcode'] = $this->language->get('help_trackingcode');
		$data['entry_comment'] = $this->language->get('entry_comment');
		$data['text_history_add']   = $this->language->get('text_history_add');
		$data['entry_order_status']	= $this->language->get('entry_order_status');
		$data['text_loading']	    = $this->language->get('text_loading');
		$data['button_history_add']	= $this->language->get('button_history_add');
		$data['column_productname']	= $this->language->get('column_productname');
		$data['column_updatedstatus']	= $this->language->get('column_updatedstatus');
		$data['column_comment']	= $this->language->get('column_comment');

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();



		if (isset($this->request->post['comment'])) {
			$data['comment'] = $this->request->post['comment'];
		} else {
			$data['comment'] = '';
		}



		$this->load->model('vendor/vendor');
		$vendor_id = $this->vendor->getId();
		$orderprduct_info = $this->model_vendor_vendor->getorderproductid($order_id, $vendor_id);


		$order_info = $this->model_vendor_vendor->getOrder($orderprduct_info['order_id']);
		/* 07-10-2020 */

		if (!empty($order_info)) {
			/* 07-10-2020 */
			$trackingcode_info =  $this->model_vendor_vendor->getTrackingCodeInfo($this->vendor->getId(), $order_id);
			$data['tracking'] = $trackingcode_info['tracking'];

			$data['order_id'] 		= $order_info['order_id'];
			$data['date_added'] 	= $order_info['date_added'];
			$data['payment_method'] = $order_info['payment_method'];
			$data['shipping_method'] = $order_info['shipping_method'];

			$data['invoice'] = $this->url->link('vendor/latestorder/invoice', '&order_id=' .  $order_info['order_id']);
			/* 07-10-2020 */
			$data['shipping'] = $this->url->link('vendor/latestorder/shipping', '&order_id=' .  $order_info['order_id']);
			/* 07-10-2020 */
			// Payment Address
			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
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
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'country'   => $order_info['payment_country']
			);

			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			// Shipping Address
			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'country'   => $order_info['shipping_country']
			);

			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));


			$products = $this->model_vendor_vendor->getSellerOrders($this->request->get['order_id']);

			foreach ($products as $product) {
				$this->load->model('localisation/order_status');
				$this->load->model('vendor/option');

				$seller_info = $this->model_vendor_vendor->getVendor($product['vendor_id']);


				if (isset($seller_info['display_name'])) {
					$sellername = $seller_info['display_name'];
				} else {
					$sellername = '';
				}
				if (isset($seller_info['vendor_id'])) {
					$ids = $seller_info['vendor_id'];
				} else {
					$ids = '';
				}
				/* 20 11 2020 */
				$this->load->model('tool/image');
				$this->load->model('catalog/product');
				$product_info = $this->model_catalog_product->getProduct($product['product_id']);

				if (is_file(DIR_IMAGE . $product_info['image'])) {
					$image = $this->model_tool_image->resize($product_info['image'], 40, 40);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', 40, 40);
				}

				/* 20 11 2020 */
				$status_info = $this->model_localisation_order_status->getOrderStatus($product['order_id']);

				if (isset($status_info['name'])) {
					$statusname = $status_info['name'];
				} else {
					$statusname = '';
				}
				$option_data = array();
				/* 01 02 2020 update */
				$options = $this->model_vendor_option->getOrderOptions($product['order_id'], $product['order_product_id']);

				foreach ($options as $option) {

					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $upload_info['name'],
								'type'  => $option['type'],
								'href'  => $this->url->link('vendor/latestorder/download', '&code=' . $upload_info['code'], true)
							);
						}
					}
				}
				/* 01 02 2020 */

				if ($product['tracking'] == '') {
					$data['trackingcode'] = 'hide';
				} else {
					$data['trackingcode'] =  $product['tracking'];
				}

				if (!empty($product['tmdshippingcost'])) {
					$shippingcost = $product['tmdshippingcost'];
				} else {
					$shippingcost = 0;
				}

				$data['products'][] = array(
					'order_product_id' => $product['order_product_id'],
					'order_status_id' => $product['order_status_id'],
					'statusname' => $statusname,
					'order_id' 	=> $product['order_id'],
					/* 07 04 2020 */
					'tmdshippingcost' 	=> $this->currency->format($product['tmdshippingcost'], $order_info['currency_code'], $order_info['currency_value']),
					/* 07 04 2020 */
					'product_id' 	=> $product['product_id'],
					'name' 		=> $product['name'],
					'model' 	=> $product['model'],
					'quantity'	=> $product['quantity'],
					'tracking' 	=> $product['tracking'],
					'sellername' => $sellername,
					'option'   => $option_data,
					'image'      => $image,
					'price'    	=> $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    	=> $this->currency->format($product['total'] + $shippingcost  + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'href'      => $this->url->link('product/product', '&product_id=' . $product['product_id'] . $url, true),
					'sellerhref' => $this->url->link('vendor/vendor_profile', '&vendor_id=' . $ids . $url, true)
				);
			}

			$data['totals'] = array();

			$totals = $this->model_vendor_vendor->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			$this->load->model('localisation/order_status');
			$data['order_statuss'] = $this->model_localisation_order_status->getOrderStatuses($data);

			$data['histories'] = array();

			$results = $this->model_vendor_vendor->getVendorOrderHistories($orderprduct_info['order_id'], $this->vendor->getId(), ($page - 1) * 10, 10);

			foreach ($results as $result) {
				/* 03 10 2019 s */
				$productname = $this->model_vendor_vendor->getOrderProductsName($result['order_product_id']);


				if (empty($productname)) {
					$productname['name'] = '';
				}

				$status_info = $this->model_vendor_vendor->getCustomerOrderStatus($result['order_status_id']);
				if (isset($status_info['name'])) {
					$statusname = $status_info['name'];
				} else {
					$statusname = '';
				}
				/* 03 10 2019 e */

				$data['histories'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'       => $statusname,
					'productname'  => $productname['name'],
					'updatedstatus' => $result['updateby'],
					'comment'      => $result['comment']

				);
			}


			$history_total = $this->model_vendor_vendor->getTotalOrderHistories($orderprduct_info['order_id'], $vendor_id);

			$pagination = new Pagination();
			$pagination->total = $history_total;
			$pagination->page = $page;
			$pagination->limit = 10;

			$pagination->url = $this->url->link('vendor/latestorder/letestview', '' . '&order_id=' . $this->request->get['order_id'] . '&page={page}', true);

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));
		}
		$data['customer2vendor'] = $this->config->get('vendor_vendor2customer');

		$data['header']      = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer']      = $this->load->controller('vendor/footer');

		$this->response->setOutput($this->load->view('vendor/letestview_list', $data));
	}

	function addtrack()
	{
		$json = array();
		$this->load->model('vendor/vendor');
		$this->load->language('vendor/latestorder');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_vendor_vendor->addTracks($this->request->get['order_id'], $this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addorderstatus()
	{

		$json = array();
		$this->load->model('vendor/vendor');
		$this->load->language('vendor/vendor');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			/* 2020 */
			$vendor_id = $this->vendor->getId();
			$this->model_vendor_vendor->addOrdeStatus($this->request->post['order_id'], $this->request->post, $vendor_id);
			/* 2020 */
			$json['success'] = $this->language->get('text_success');
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addorderstatusview()
	{

		$json = array();
		$this->load->model('vendor/vendor');
		$this->load->language('vendor/vendor');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			/* 2020 */
			$vendor_id = $this->vendor->getId();
			$this->model_vendor_vendor->addOrdeStatus($this->request->get['order_id'], $this->request->post, $vendor_id);
			/* 2020 */
			if (isset($this->request->post['tracking'])) {
				$this->addtrack();
			}

			$json['success'] = $this->language->get('text_success');
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function invoice()
	{

		$this->load->language('vendor/latestorder');
		$this->load->model('localisation/order_status');
		$this->load->model('setting/setting');
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/store');
		$this->load->model('tool/upload');
		$this->load->model('vendor/option');


		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

		$data['text_invoice'] = $this->language->get('text_invoice');
		$data['text_order_detail'] = $this->language->get('text_order_detail');
		$data['text_telephone'] = $this->language->get('text_telephone');
		$data['text_fax'] = $this->language->get('text_fax');
		$data['text_email'] = $this->language->get('text_email');
		$data['text_website'] = $this->language->get('text_website');
		$data['text_invoice_no'] = $this->language->get('text_invoice_no');
		$data['text_order_id'] = $this->language->get('text_order_id');
		$data['text_payment_method'] = $this->language->get('text_payment_method');
		$data['text_shipping_method'] = $this->language->get('text_shipping_method');
		$data['text_payment_address'] = $this->language->get('text_payment_address');
		$data['text_shipping_address'] = $this->language->get('text_shipping_address');
		$data['text_byseller']		   = $this->language->get('text_byseller');

		if (isset($this->request->get['order_product_id'])) {
			$order_product_id = $this->request->get['order_product_id'];
		} else {
			$order_product_id = 0;
		}


		$data['text_address']	  = $this->language->get('text_address');
		$data['text_address2']	  = $this->language->get('text_address2');
		$data['text_city']	      = $this->language->get('text_city');
		$data['text_country']	  = $this->language->get('text_country');
		$data['text_zone']	      = $this->language->get('text_zone');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		$vendor_id = $this->vendor->getId();
		$orderprduct_info = $this->model_vendor_vendor->getorderproductid($order_id, $vendor_id);

		$store_infos = $this->model_vendor_store->getViewStore($vendor_id);


		$data['title'] = $this->language->get('text_invoice');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		$data['lang'] = $this->language->get('code');

		$url = '';

		$data['orders'] = array();

		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($orderprduct_info['order_id'])) {
			$orders[] = $orderprduct_info['order_id'];
		}
	
		foreach ($orders as $order_id) {
			$order_info = $this->model_vendor_vendor->getOrder($order_id);



		// ----------------------------------------------------------------------------------------------------------------------------------------------


							// ✅ GST ZONE LOGIC: check if vendor zone and customer shipping zone are same
				$vendor_zone_id = $store_infos['zone_id']; // From vendor store
				$shipping_zone_id = $order_info['shipping_zone_id']; // From customer shipping address

				if ($vendor_zone_id == $shipping_zone_id) {
					$gst_type = 'cgst_sgst'; // Same zone - apply CGST + SGST
				} else {
					$gst_type = 'igst'; // Different zones - apply IGST
				}

				$data['gst_type'] = $gst_type;

		// ----------------------------------------------------------------------------------------------------------------------------------------------




			// Make sure there is a shipping method
			if ($order_info) {

				if (!empty($store_infos['address_2'])) {
					$store_address2 = $store_infos['address_2'];
				} else {
					$store_address2 = '';
				}

				$country_info = $this->model_localisation_country->getCountry($store_infos['country_id']);
				$zone_info = $this->model_localisation_zone->getZone($store_infos['zone_id']);

				if ($store_infos) {
					$store_address = $store_infos['address_1'];
					$store_email = $store_infos['email'];
					$store_city = $store_infos['city'];
					$store_telephone = $store_infos['telephone'];
					$country = $country_info['name'];
					$zones = $zone_info['name'];
				}


				if ($order_info['invoice_no']) {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$invoice_no = '';
				}


				if ($order_info['payment_address_format']) {
					$format = $order_info['payment_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
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
				);

				$replace = array(
					'firstname' => $order_info['payment_firstname'],
					'lastname'  => $order_info['payment_lastname'],
					'company'   => $order_info['payment_company'],
					'address_1' => $order_info['payment_address_1'],
					'address_2' => $order_info['payment_address_2'],
					'city'      => $order_info['payment_city'],
					'postcode'  => $order_info['payment_postcode'],
					'zone'      => $order_info['payment_zone'],
					'zone_code' => $order_info['payment_code'],
					'country'   => $order_info['payment_country']
				);

				$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));


				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
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
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],

					'country'   => $order_info['shipping_country']
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$product_data = array();

				$products = $this->model_vendor_vendor->getSellerOrders($order_id);




				foreach ($products as $product) {

		// ------------hsn----------------------------------------------------------------------------------------------------------------------------------


					$hsn_result = $this->db->query("SELECT hsn_code FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product['product_id'] . "'");

					if (!empty($hsn_result->row)) {
						$hsn_code = $hsn_result->row['hsn_code'];
					} else {
						$hsn_code = '';
					}
		// ----------------------------------------------------------------------------------------------------------------------------------------------
					

					// -----------------------------------------------------------------------courier charges
				// 	$courier_result = $this->db->query("SELECT total_courier_charges FROM " . DB_PREFIX . "order WHERE order_product_id = '" . (int)$product['order_product_id'] . "'");

					if ($order_info['total_courier_charges']) {
						$product['courier_charges'] = $order_info['total_courier_charges'];
					} else {
						$product['courier_charges'] = 0.00;

			
					}
					// -----------------------------------------------------------------------courier charges

					
		// ------------------return policy----------------------------------------------------------------------------------------------------------------------------
  
					$return_policy_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_return_policy WHERE product_id = '" . (int)$product['product_id'] . "'");

						if ($return_policy_query->num_rows) {
							$return_duration_period = $return_policy_query->row['return_duration_period'];
							$return_policy_details = $return_policy_query->row['return_policy_details'];
						} else {
							$return_duration_period = '-';
							$return_policy_details = 'No Return Policy';
						}

					
		// ----------------------------------------------------------------------------------------------------------------------------------------------



		// ---------------------warranty-------------------------------------------------------------------------------------------------------------------------

					$warranty_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_warranty WHERE product_id = '" . (int)$product['product_id'] . "'");

					if ($warranty_query->num_rows) {
						$product['warranty_by'] = $warranty_query->row['warranty_by'];
						$product['warranty_duration'] = $warranty_query->row['warranty_duration'];
						$product['warranty_description'] = $warranty_query->row['description'];
						$product['warranty'] = 1;
						$product['No_warranty'] =null;
					} else {
						$product['warranty_by'] = '-';
						$product['warranty_duration'] = '-';
						$product['warranty_description'] = '-';
						$product['warranty'] = 0;
						$product['No_warranty'] = 'No Warranty';
					
					}
		// ----------------------------------------------------------------------------------------------------------------------------------------------
                 


					$seller_info = $this->model_vendor_vendor->getVendor($product['vendor_id']);


					$status_info = $this->model_localisation_order_status->getOrderStatus($product['order_status_id']);
					if (isset($status_info['name'])) {
						$statusname = $status_info['name'];
					} else {
						$statusname = '';
					}

					if (isset($seller_info['display_name'])) {
						$sellername = $seller_info['display_name'];
					} else {
						$sellername = '';
					}

					if (isset($seller_info['vendor_id'])) {
						$ids = $seller_info['vendor_id'];
					} else {
						$ids = '';
					}

					$option_data = array();
					/* 01 02 2020 */
					$options = $this->model_vendor_option->getOrderOptions($order_id, $product['order_product_id']);
					/* 01 02 2020 */
					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

							if ($upload_info) {
								$value = $upload_info['name'];
							} else {
								$value = '';
							}
						}

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $value
						);
					}

					/* 07 04 2020 */
					$data['chkshipcost'] = $this->config->get('shipping_shippingcost_status');

					if (!empty($product['tmdshippingcost'])) {
						$shippingcost = $product['tmdshippingcost'];
					} else {
						$shippingcost = 0;
					}




		// -------------------cal---------------------------------------------------------------------------------------------------------------------------



					/* 07 04 2020 */
					$hsn_result = $this->db->query("SELECT hsn_code, gst_rate FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product['product_id'] . "'");
					if (!empty($hsn_result->row)) {
						$hsn_code = $hsn_result->row['hsn_code'];
						$gst_rate = (float)$hsn_result->row['gst_rate'];
					} else {
						$hsn_code = '';
						$gst_rate = 18.00; // default if not set
					}
					
					// Product base values
					$price = (float)$product['price'];
					$quantity = (int)$product['quantity'];
					$courier_charges = isset($product['courier_charges']) ? (float)$product['courier_charges'] : 0.00;
					
					// GST calculations
					$rate = $gst_rate > 0 ? $gst_rate : 18.00;
					$gst_amount = ($rate * $price * $quantity) / 100;
					
					if ($gst_type == 'cgst_sgst') {
						$cgst = $gst_amount / 2;
						$sgst = $gst_amount / 2;
						$igst = 0.00;
					} else {
						$cgst = 0.00;
						$sgst = 0.00;
						$igst = $gst_amount;
					}
					
					$price=$price-($gst_amount/$quantity);
					// Total = base price * qty + GST + courier
					$total_price = ($price * $quantity) + $gst_amount + $courier_charges;
					
					$product_data[] = array(
						'name'     => $product['name'],
						'model'    => $product['model'],
						'quantity' => $quantity,
						'price'    => $this->currency->format($price, $order_info['currency_code'], $order_info['currency_value']),
						'total'    => $this->currency->format($total_price, $order_info['currency_code'], $order_info['currency_value']),
						'hsn_code' => $hsn_code,
						'gst_rate' => $rate,
					
						// Raw GST values
						'cgst'     => $cgst,
						'sgst'     => $sgst,
						'igst'     => $igst,
					
						// Formatted GST values
						'cgst_formatted' => $this->currency->format($cgst, $order_info['currency_code'], $order_info['currency_value']),
						'sgst_formatted' => $this->currency->format($sgst, $order_info['currency_code'], $order_info['currency_value']),
						'igst_formatted' => $this->currency->format($igst, $order_info['currency_code'], $order_info['currency_value']),
						
						// Courier Charges
						'courier_charges' => $this->currency->format($courier_charges, $order_info['currency_code'], $order_info['currency_value']),
					
						// ✅ Return Policy
						'return_duration_period' => $return_duration_period,
						'return_policy_details' => $return_policy_details,
					
						// ✅ Warranty
						'warranty_by' => $product['warranty_by'],
						'warranty_duration' => $product['warranty_duration'],
						'warranty_description' => $product['warranty_description'],
						'warranty'=>$product['warranty'],
						'No_warranty'=>$product['No_warranty']
					);
					



		// ----------------------------------------------------------------------------------------------------------------------------------------------

					
					
				}

				$total_data = array();

				$totals = $this->model_vendor_vendor->getOrderTotals($order_id);

				foreach ($totals as $total) {
					    $total['value']=round($total['value']);
					$total_data[] = array(
						'title' => $total['title'],
						'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$data['orders'][] = array(
					'order_id'	       => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),

					'store_name'       => $store_infos['name'],
					'store_address2'   => $store_address2,
					'store_city'   	   => $store_city,
					'country'   	   => $country,
					'zones'   	  	   => $zones,

					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => $store_email,
					'store_telephone'  => $store_telephone,
					'email'            => $order_info['email'],
					'telephone'        => $order_info['telephone'],
					'shipping_address' => $shipping_address,
					'payment_address' => $payment_address,
					'payment_method'  => $order_info['payment_method'],
					'shipping_method'  => $order_info['shipping_method'],
					'product'          => $product_data,
					'total'            => $total_data,
					'comment'          => nl2br($order_info['comment'])
				);
			}
		}

		$data['customer2vendor'] = $this->config->get('vendor_vendor2customer');

		$this->response->setOutput($this->load->view('vendor/order_invoice2', $data));
	}
	/* 01 02 2020 */
	public function shipping()
	{

		$this->load->language('vendor/latestorder');
		$this->load->model('vendor/store');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');
		$this->load->model('tool/upload');
		$this->load->model('vendor/option');
		$this->load->model('vendor/product');

		$data['title'] = $this->language->get('text_shipping');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['lang'] = $this->language->get('code');

		$this->load->model('vendor/vendor');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		$vendor_id = $this->vendor->getId();

		$orderprduct_info = $this->model_vendor_vendor->getorderproductid($order_id, $vendor_id);

		$store_infos = $this->model_vendor_store->getViewStore($orderprduct_info['vendor_id']);

		$data['orders'] = array();

		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_vendor_vendor->getOrder($order_id);

			// Make sure there is a shipping method
			if ($order_info) {

				if (!empty($store_infos['address_2'])) {
					$store_address2 = $store_infos['address_2'];
				} else {
					$store_address2 = '';
				}

				$country_info = $this->model_localisation_country->getCountry($store_infos['country_id']);
				$zone_info = $this->model_localisation_zone->getZone($store_infos['zone_id']);

				if ($store_infos) {
					$store_address = $store_infos['address_1'];
					$store_email = $store_infos['email'];
					$store_city = $store_infos['city'];
					$store_telephone = $store_infos['telephone'];
					$country = $country_info['name'];
					$zones = $zone_info['name'];
				}

				if ($order_info['invoice_no']) {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$invoice_no = '';
				}

				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
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
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					//'zone_code' => $order_info['shipping_zone_code'],
					'country'   => $order_info['shipping_country']
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$this->load->model('tool/upload');

				$product_data = array();

				$products = $this->model_vendor_vendor->getSellerOrders($order_id);

				foreach ($products as $product) {
					$option_weight = '';

					$product_info = $this->model_vendor_product->getProduct($product['product_id'], $product['vendor_id']);
					if ($product_info) {
						$option_data = array();

						$options = $this->model_vendor_option->getOrderOptions($order_id, $product['order_product_id']);

						foreach ($options as $option) {
							if ($option['type'] != 'file') {
								$value = $option['value'];
							} else {
								$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

								if ($upload_info) {
									$value = $upload_info['name'];
								} else {
									$value = '';
								}
							}

							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $value
							);

							$product_option_value_info = $this->model_vendor_product->getProductOptionValue($product['product_id'], $option['product_option_value_id']);

							if ($product_option_value_info) {
								if ($product_option_value_info['weight_prefix'] == '+') {
									$option_weight += $product_option_value_info['weight'];
								} elseif ($product_option_value_info['weight_prefix'] == '-') {
									$option_weight -= $product_option_value_info['weight'];
								}
							}
						}

						$product_data[] = array(
							'name'     => $product_info['name'],
							'model'    => $product_info['model'],
							'option'   => $option_data,
							'quantity' => $product['quantity'],
							'location' => $product_info['location'],
							'sku'      => $product_info['sku'],
							'upc'      => $product_info['upc'],
							'ean'      => $product_info['ean'],
							'jan'      => $product_info['jan'],
							'isbn'     => $product_info['isbn'],
							'mpn'      => $product_info['mpn'],
							'weight'   => $this->weight->format(($product_info['weight'] + (float)$option_weight) * $product['quantity'], $product_info['weight_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point'))
						);
					}
				}

				$data['orders'][] = array(
					'order_id'	       => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					'store_name'       => $order_info['store_name'],
					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => $store_email,
					'store_telephone'  => $store_telephone,
					'email'            => $order_info['email'],
					'telephone'        => $order_info['telephone'],
					'shipping_address' => $shipping_address,
					'shipping_method'  => $order_info['shipping_method'],
					'product'          => $product_data,
					'comment'          => nl2br($order_info['comment'])
				);
			}
		}

		$this->response->setOutput($this->load->view('vendor/order_shipping', $data));
	}

	public function download()
	{
		$this->load->model('tool/upload');

		if (isset($this->request->get['code'])) {
			$code = $this->request->get['code'];
		} else {
			$code = 0;
		}

		$upload_info = $this->model_tool_upload->getUploadByCode($code);

		if ($upload_info) {
			$file = DIR_UPLOAD . $upload_info['filename'];
			$mask = basename($upload_info['name']);

			if (!headers_sent()) {
				if (is_file($file)) {
					header('Content-Type: application/octet-stream');
					header('Content-Description: File Transfer');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					readfile($file, 'rb');
					exit;
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->load->language('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('error/not_found', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	/* 01 02 2020 */
}
