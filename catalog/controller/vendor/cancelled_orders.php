<?php
class ControllerVendorCancelledOrders extends Controller {
    private $error = array();

    public function index() {
        if (!$this->vendor->isLogged()) {
            $this->response->redirect($this->url->link('vendor/login', '', true));
        }

        $this->load->language('vendor/cancelled_orders');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('vendor/cancelled_orders');

        $this->getList();
    }
    
    public function cancelledview()
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
		
		$data['cancel'] = $this->url->link('vendor/cancelled_orders');

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

		$this->response->setOutput($this->load->view('vendor/cancelled_view', $data));
	}

    protected function getList() {
        $this->load->model('tool/image');

        $filter_order_id = isset($this->request->get['filter_order_id']) ? $this->request->get['filter_order_id'] : '';
        $filter_product_name = isset($this->request->get['filter_product_name']) ? $this->request->get['filter_product_name'] : '';
        $filter_date_added = isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : '';
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        
        $data['cancel'] = $this->url->link('vendor/dashboard');

        $url = '';
        if ($filter_order_id) $url .= '&filter_order_id=' . urlencode($filter_order_id);
        if ($filter_product_name) $url .= '&filter_product_name=' . urlencode($filter_product_name);
        if ($filter_date_added) $url .= '&filter_date_added=' . urlencode($filter_date_added);
        if ($page) $url .= '&page=' . $page;

        $data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home', '', true)),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('vendor/cancelled_orders', '', true))
        );

        $data['cancelled_orders'] = array();

        $filter_data = array(
            'filter_order_id' => $filter_order_id,
            'filter_product_name' => $filter_product_name,
            'filter_date_added' => $filter_date_added,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $vendor_id = $this->vendor->getId();
        $order_total = $this->model_vendor_cancelled_orders->getTotalCancelledOrders($vendor_id, $filter_data);
        $results = $this->model_vendor_cancelled_orders->getCancelledOrders($vendor_id, $filter_data);

        $sr = 1;
        foreach ($results as $result) {
            $image = 'no_image.png';
            if (!empty($result['image']) && is_file(DIR_IMAGE . $result['image'])) {
                $image = $this->model_tool_image->resize($result['image'], 40, 40);
            }

            $data['cancelled_orders'][] = array(
                'sr' => $sr++,
                'order_id' => $result['order_id'],
                'image' => $image,
                'Product' => $result['name'],
                'quantity' => $result['quantity'],
                'total' => $this->currency->format($result['total'], $this->config->get('config_currency')),
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'status' => $result['status_name'],
                'view' => $this->url->link('vendor/cancelled_orders/cancelledview', 'order_id=' . $result['order_id'], true)
            );
        }

        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('vendor/cancelled_orders', $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        $this->response->setOutput($this->load->view('vendor/cancelled_orders', $data));
    }
}
?>
