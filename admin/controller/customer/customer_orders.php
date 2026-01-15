<?php
class ControllerCustomerCustomerOrders extends Controller {

	private $error = array();

    public function index() {
		$this->load->language('customer/customer_orders');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('customer/customer_orders');

        $this->getList();
    }

	protected function getList() {

        $filter_customer_id = isset($this->request->get['filter_customer_id']) ? $this->request->get['filter_customer_id'] : '';
        $filter_name = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '';
        $filter_email = isset($this->request->get['filter_email']) ? $this->request->get['filter_email'] : '';
        $filter_date_added = isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : '';
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

        $url = '';
        if ($filter_customer_id) $url .= '&filter_customer_id=' . urlencode($filter_customer_id);
        if ($filter_name) $url .= '&filter_name=' . urlencode($filter_name);
        if ($filter_email) $url .= '&filter_email=' . urlencode($filter_email);
        if ($filter_date_added) $url .= '&filter_date_added=' . urlencode($filter_date_added);
        if ($page) $url .= '&page=' . $page;
        
        
        // Ensure $url is always defined
        $url = isset($url) ? $url : '';

        $data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home', '', true)),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('customer/customer_orders', '', true))
        );

        // Added by shubham at 12-05-2025
        // $data['add'] = $this->url->link('customer/customer_orders/history', 'user_token=' . $this->session->data['user_token'] . $url, true);
        // ----------===============---------

        $data['customer_orders'] = array();

        $filter_data = array(
            'filter_customer_id' => $filter_customer_id,
            'filter_name' => $filter_name,
            'filter_email' => $filter_email,
            'filter_date_added' => $filter_date_added,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $results = $this->model_customer_customer_orders->getCustomers($filter_data);

        // $sr = 1;
        foreach ($results as $result) {

            $data['customer_orders'][] = array(
                // 'sr' => $sr++,
                'customer_id' => $result['customer_id'],
                'customer' => $result['customer'],
                'email' => $result['email'],
                'telephone' => $result['telephone'],
                'customer_group' => $result['customer_group'],
                // 'total' => $this->currency->format($result['total'], $this->config->get('config_currency')),
                // 'status' => $result['status'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                // 'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date modified'])),
                // added by shubham at 12-05-2025
                // 'view'    => $this->url->link('customer/customer_orders_history', 'user_token=' . $this->session->data['user_token'] . $url, true)
                'view'       => $this->url->link('customer/customer_orders_history', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $result['customer_id'] . $url,true)
				// 'edit'          => $this->url->link('sale/order/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $result['customer_id'] . $url, true)
                // =----------------================
            );
        }

      
        $data['filter_name'] = $filter_name;
		$data['filter_email'] = $filter_email;
        // added by shubham at 12-05-2025
         $data['user_token'] = $this->session->data['user_token'];
        // ===-----------------===========

		$data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('customer/customer_orders', $data));
    }

}