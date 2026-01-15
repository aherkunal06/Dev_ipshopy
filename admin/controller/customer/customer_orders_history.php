<?php
class ControllerCustomerCustomerOrdersHistory extends Controller {
    // private $error = array();
    
    public function index() {
        $this->load->language('customer/customer_orders_history');
        $this->document->setTitle($this->language->get('heading_title'));
			
		// ✅ Properly load the model file
		$this->load->model('customer/customer_orders_history');
		$this->load->model('sale/order');
		$this->load->model('customer/customer');
		$this->load->model('tool/image');
		
		
      
        //   $this->response->setOutput($this->load->view('customer/customer_orders_history', $data));

         $this->getList();
        
    }

   

// 	protected function getList() {
//         if (!isset($this->request->get['customer_id'])) {
//             return $this->response->redirect($this->url->link('customer/customer_orders', 'user_token=' . $this->session->data['user_token'], true));
//         }
    
//         $customer_id = (int)$this->request->get['customer_id'];
    
//     	// filter code=+==============
    	
//         // Filters
//         $filter_order_id = isset($this->request->get['filter_order_id']) ? $this->request->get['filter_order_id'] : '';
//         $filter_status = isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : '';
//         $filter_payment_status = isset($this->request->get['filter_payment_status']) ? $this->request->get['filter_payment_status'] : '';
//         $filter_date_added = isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : '';
    
//         $filter_payment_method = isset($this->request->get['filter_payment_method']) ? $this->request->get['filter_payment_method'] : '';
//         // $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
    
//         if (isset($this->request->get['filter_order_status'])) {
//     			$filter_order_status = $this->request->get['filter_order_status'];
//     		} else {
//     			$filter_order_status = '';
//     		}
    
//     		if (isset($this->request->get['filter_order_status_id'])) {
//     			$filter_order_status_id = $this->request->get['filter_order_status_id'];
//     		} else {
//     			$filter_order_status_id = '';
//     		}
    
//         $url = '';
    
//         if ($filter_order_id) {
//             $url .= '&filter_order_id=' . urlencode($filter_order_id);
//         }
    
//         if ($filter_status) {
//             $url .= '&filter_status=' . urlencode($filter_status);
//         }
    
//         if ($filter_payment_status) {
//             $url .= '&filter_payment_status=' . urlencode($filter_payment_status);
//         }
    
//         if ($filter_payment_method) {
//             $url .= '&filter_payment_method=' . urlencode($filter_payment_method);
//         }
    
//         if ($filter_date_added) {
//             $url .= '&filter_date_added=' . urlencode($filter_date_added);
//         }
    
//         if (isset($this->request->get['filter_order_status'])) {
//     			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
//     		}
    
//     		if (isset($this->request->get['filter_order_status_id'])) {
//     			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
//     		}
    
//     	// ---------------------------
    
//         $data['breadcrumbs'] = array(
//             array(
//                 'text' => $this->language->get('text_home'),
//                 'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
//             ),
//             array(
//                 'text' => $this->language->get('text_customer_orders'),
//                 'href' => $this->url->link('customer/customer_orders', 'user_token=' . $this->session->data['user_token'], true)
//             ),
//             array(
//                 'text' => $this->language->get('heading_title'),
//                 'href' => $this->url->link('customer/customer_orders_history', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $customer_id, true)
//             )
//         );
    
    	
//         // Prepare filter data for model
//         $filter_data = array(
//             'customer_id'         => $customer_id,
//             'filter_order_id'     => $filter_order_id,
//             'filter_status'       => $filter_status,
//             'filter_payment_status' => $filter_payment_status,
//             'filter_date_added'   => $filter_date_added,
//             'filter_order_status'    => $filter_order_status,
//     		'filter_order_status_id' => $filter_order_status_id,
//             'filter_payment_method' => $filter_payment_method
    
//         );
    
//         $data['orders'] = array();
//         $data['summary'] = array();
    
//         // Get combined results from model
//         // $results = $this->model_customer_customer_orders_history->getOrdersByCustomerId($customer_id);
//     	// Model should be updated to accept $filter_data
//         $results = $this->model_customer_customer_orders_history->getOrdersByCustomerId($filter_data);
    
//         // Separate detailed orders and summary
//         $order_rows = $results['orders'];
//         $summary_rows = $results['summary'];
    
//         // Loop through detailed orders
//         foreach ($order_rows as $result) {
//             $data['orders'][] = array(
//                 'order_id'        => $result['order_id'],
//                 'status'          => $result['status'],
//                 'product_name'    => $result['product_name'],
//                 'product_quantity'=> $result['product_quantity'],
//                 'product_price'   => $result['product_price'],
//                 'payment_status'  => $result['payment_status'],
//                 'payment_method'  => $result['payment_method'],
//                 'product_image'   => $this->model_tool_image->resize($result['product_image'], 100, 100),
//                 'total'           => $this->currency->format($result['total'], $this->config->get('config_currency')),
//                 'date_added'      => date($this->language->get('date_format_short'), strtotime($result['date_added']))
//                 // 'view'            => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], true),
//                 // 'edit'            => $this->url->link('sale/order/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], true)
//             );
//         }
    
//         // Loop through summary result
//         foreach ($summary_rows as $row) {
//             $data['summary'][] = array(
//                 'order_status'  => $row['order_status'],
//                 'total_orders'  => $row['total_orders'],
//                 'total_amount'  => $this->currency->format($row['total_amount'], $this->config->get('config_currency'))
//             );
//         }
    
    
    	
//         // Set filters for template rendering
//         $data['filter_order_id'] = $filter_order_id;
//         $data['filter_status'] = $filter_status;
//         $data['filter_payment_status'] = $filter_payment_status;
//         $data['filter_date_added'] = $filter_date_added;
//         $data['filter_payment_method'] = $filter_payment_method;
    
    
//         $data['filter_order_status'] = $filter_order_status;
//         $data['filter_order_status_id'] = $filter_order_status_id;
    
//         if (isset($this->request->get['filter_order_status'])) {
//     			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
//     		}
    
//     		if (isset($this->request->get['filter_order_status_id'])) {
//     			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
//     		}
    
//         $this->load->model('localisation/order_status');
    
//         $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
    
//         $data['user_token'] = $this->session->data['user_token'];
//         $data['customer_id'] = $customer_id;
    
//         $data['header'] = $this->load->controller('common/header');
//         $data['column_left'] = $this->load->controller('common/column_left');
//         $data['footer'] = $this->load->controller('common/footer');
    
    
    
//         $this->response->setOutput($this->load->view('customer/customer_orders_history', $data));
//     }

    protected function getList() {
        if (!isset($this->request->get['customer_id'])) {
            return $this->response->redirect($this->url->link('customer/customer_orders', 'user_token=' . $this->session->data['user_token'], true));
        }

        $customer_id = (int)$this->request->get['customer_id'];

        // Filters
        $filter_order_id = isset($this->request->get['filter_order_id']) ? $this->request->get['filter_order_id'] : '';
        $filter_status = isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : '';
        $filter_payment_status = isset($this->request->get['filter_payment_status']) ? $this->request->get['filter_payment_status'] : '';
        $filter_date_added = isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : '';
        $filter_payment_method = isset($this->request->get['filter_payment_method']) ? $this->request->get['filter_payment_method'] : '';
        $filter_order_status = isset($this->request->get['filter_order_status']) ? $this->request->get['filter_order_status'] : '';
        $filter_order_status_id = isset($this->request->get['filter_order_status_id']) ? $this->request->get['filter_order_status_id'] : '';

        $url = '';

        if ($filter_order_id) {
            $url .= '&filter_order_id=' . urlencode($filter_order_id);
        }
        if ($filter_status) {
            $url .= '&filter_status=' . urlencode($filter_status);
        }
        if ($filter_payment_status) {
            $url .= '&filter_payment_status=' . urlencode($filter_payment_status);
        }
        if ($filter_payment_method) {
            $url .= '&filter_payment_method=' . urlencode($filter_payment_method);
        }
        if ($filter_date_added) {
            $url .= '&filter_date_added=' . urlencode($filter_date_added);
        }
        if ($filter_order_status) {
            $url .= '&filter_order_status=' . urlencode($filter_order_status);
        }
        if ($filter_order_status_id) {
            $url .= '&filter_order_status_id=' . urlencode($filter_order_status_id);
        }

        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
            ],
            [
                'text' => $this->language->get('text_customer_orders'),
                'href' => $this->url->link('customer/customer_orders', 'user_token=' . $this->session->data['user_token'], true)
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('customer/customer_orders_history', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $customer_id, true)
            ]
        ];

        // Prepare filter data for model
        $filter_data = [
            'customer_id'           => $customer_id,
            'filter_order_id'       => $filter_order_id,
            'filter_status'         => $filter_status,
            'filter_payment_status' => $filter_payment_status,
            'filter_date_added'     => $filter_date_added,
            'filter_order_status'   => $filter_order_status,
            'filter_order_status_id'=> $filter_order_status_id,
            'filter_payment_method' => $filter_payment_method
        ];

        $data['orders'] = [];
        $data['summary'] = [];

        // Get results from model
        $results = $this->model_customer_customer_orders_history->getOrdersByCustomerId($filter_data);

        // Extract detailed orders and summary
        $order_rows = $results['orders'];
        $summary_rows = $results['summary'];

        foreach ($order_rows as $result) {
            $data['orders'][] = [
                'order_id'         => $result['order_id'],
                'status'           => $result['status'],
                'product_name'     => $result['product_name'],
                'product_quantity' => $result['product_quantity'],
                'product_price'    => $result['product_price'],
                'payment_status'   => $result['payment_status'],
                'payment_method'   => $result['payment_method'],
                'product_image'    => $this->model_tool_image->resize($result['product_image'], 100, 100),
                'total'            => $this->currency->format($result['total'], $this->config->get('config_currency')),
                'date_added'       => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'view'             => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], true),
                'edit'             => $this->url->link('sale/order/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], true)
            ];
        }

        foreach ($summary_rows as $row) {
            $data['summary'][] = [
                'order_status' => $row['order_status'],
                'total_orders' => $row['total_orders'],
                'total_amount' => $this->currency->format($row['total_amount'], $this->config->get('config_currency'))
            ];
        }

        // Add grand totals to data for template
        $data['grand_total_orders'] = $results['grand_total_orders'] ?? 0;
        $data['grand_total_amount'] = $this->currency->format($results['grand_total_amount'] ?? 0, $this->config->get('config_currency'));

        // Set filters for template rendering
        $data['filter_order_id'] = $filter_order_id;
        $data['filter_status'] = $filter_status;
        $data['filter_payment_status'] = $filter_payment_status;
        $data['filter_date_added'] = $filter_date_added;
        $data['filter_payment_method'] = $filter_payment_method;
        $data['filter_order_status'] = $filter_order_status;
        $data['filter_order_status_id'] = $filter_order_status_id;

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['user_token'] = $this->session->data['user_token'];
        $data['customer_id'] = $customer_id;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('customer/customer_orders_history', $data));
    }

}