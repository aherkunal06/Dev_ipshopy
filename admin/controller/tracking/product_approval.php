<?php
class ControllerTrackingProductApproval extends Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('tracking/product_approval');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('tracking/product_approval');
        $this->load->model('user/user');
        $this->load->model('vendor/vendor');

        $this->getList();
    }

    protected function getList(): void {
        // Sorting and pagination
        $sort = $this->request->get['sort'] ?? 'username';
        $order = $this->request->get['order'] ?? 'ASC';
        $page = $this->request->get['page'] ?? 1;
        
        // Filters
        $filter_user_id = $this->request->get['filter_user_id'] ?? '';
        $filter_name = $this->request->get['filter_name'] ?? '';
        $filter_email = $this->request->get['filter_email'] ?? '';
    		
        // -----------=

        $url = '';
        // for filter
        if ($filter_user_id) $url .= '&filter_user_id=' . $filter_user_id;
        if ($filter_name) $url .= '&filter_name=' . urlencode(html_entity_decode($filter_name, ENT_QUOTES, 'UTF-8'));
        if ($filter_email) $url .= '&filter_email=' . urlencode(html_entity_decode($filter_email, ENT_QUOTES, 'UTF-8'));
        // ---------============-
        if (isset($this->request->get['sort'])) $url .= '&sort=' . $this->request->get['sort'];
        if (isset($this->request->get['order'])) $url .= '&order=' . $this->request->get['order'];
        if (isset($this->request->get['page'])) $url .= '&page=' . $this->request->get['page'];

        $user_token = $this->session->data['user_token'];

        // Breadcrumbs
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $user_token, true)
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('tracking/product_approval', 'user_token=' . $user_token . $url, true)
            ]
        ];

        $limit = $this->config->get('config_limit_admin');
        $start = ($page - 1) * $limit;

        $filter_data = [
            'filter_user_id' => $filter_user_id,
            'filter_name'   => $filter_name,
			'filter_email'  => $filter_email,
            'sort'  => $sort,
            'order' => $order,
            'start' => $start,
            'limit' => $limit
        ];

      // Fetch users & vendors
        $user_results = $this->model_user_user->getUsers($filter_data);
        $vendor_results = $this->model_vendor_vendor->getVendors($filter_data);

        // Fetch approval summary grouped by approved_by (name)
        $approval_summary = $this->model_tracking_product_approval->getProductApprovalSummaryByApprovedBy();

        // Convert to indexed array for easy lookup by name
        $approval_counts = [];
        foreach ($approval_summary as $row) {
            $approval_counts[$row['approved_by']] = [
                'approved'     => $row['approved'] ?? 0,
                'disapproved'  => $row['disapproved'] ?? 0,
                'total'        => $row['total'] ?? 0
            ];
        }

        $combined_list = [];

        // Users
        foreach ($user_results as $result) {
            $display_name = $result['username'] ?? trim(($result['firstname'] ?? '') . ' ' . ($result['lastname'] ?? ''));

            $approved = $approval_counts[$display_name]['approved'] ?? 0;
            $disapproved = $approval_counts[$display_name]['disapproved'] ?? 0;
            $total = $approval_counts[$display_name]['total'] ?? 0;

            $combined_list[] = [
                'id'              => $result['user_id'],
                'username'        => $display_name,
                'email'           => $result['email'] ?? '',
                'status'          => (!empty($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
                'type'            => 'User',
                'date_added'      => isset($result['date_added']) ? date($this->language->get('date_format_short'), strtotime($result['date_added'])) : '',
                'approved_total'   => $approved,
                'disapproved_total'=> $disapproved,
                'view'            => $this->url->link(
                    'tracking/product_approval_history',
                    'user_token=' . $user_token .
                    '&approved_by=' . urlencode($display_name),
                    true
                )
            ];
        }

        // Vendors
        // foreach ($vendor_results as $result) {
        //     $display_name = $result['username'] ?? trim(($result['firstname'] ?? '') . ' ' . ($result['lastname'] ?? ''));

        //     $approved = $approval_counts[$display_name]['approved'] ?? 0;
        //     $disapproved = $approval_counts[$display_name]['disapproved'] ?? 0;
        //     $total = $approval_counts[$display_name]['total'] ?? 0;

        //     $combined_list[] = [
        //         'id'              => $result['vendor_id'],
        //         'username'        => $display_name,
        //         'email'           => $result['email'] ?? '',
        //         'status'          => (!empty($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
        //         'type'            => 'Vendor',
        //         'date_added'      => isset($result['date_added']) ? date($this->language->get('date_format_short'), strtotime($result['date_added'])) : '',
        //         'approved_total'   => $approved,
        //         'disapproved_total'=> $disapproved,
        //         'view'            => $this->url->link(
        //             'tracking/product_approval_history',
        //             'user_token=' . $user_token .
        //             '&approved_by=' . urlencode($display_name),
        //             true
        //         )
        //     ];
        // }
        
        $url='';
        if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

        $data['combined_list'] = $combined_list;
        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['user_token'] = $user_token;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}
        $data['filter_user_id'] = $filter_user_id;

        $data['filter_name'] = $filter_name;
		$data['filter_email'] = $filter_email;

        $this->response->setOutput($this->load->view('tracking/product_approval', $data));
    }
    
    public function autocompleteName() {
        $json = [];
    
        if (isset($this->request->get['filter_name'])) {
            $this->load->model('user/user');
    
            $filter_data = [
                'filter_name' => $this->request->get['filter_name'],
                'start'       => 0,
                'limit'       => 5
            ];
    
            $results = $this->model_user_user->getUsers($filter_data);
    
            foreach ($results as $result) {
                $json[] = [
                    'user_id' => $result['user_id'],
                    'name'    => $result['username']
                ];
            }
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function autocompleteEmail() {
        $json = [];
    
        if (isset($this->request->get['filter_email'])) {
            $this->load->model('user/user');
    
            $filter_data = [
                'filter_email' => $this->request->get['filter_email'],
                'start'        => 0,
                'limit'        => 5
            ];
    
            $results = $this->model_user_user->getUsers($filter_data);
    
            foreach ($results as $result) {
                $json[] = [
                    'user_id' => $result['user_id'],
                    'email'   => $result['email']
                ];
            }
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
}
