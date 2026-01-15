<?php
class ControllerTrackingProductTracking extends Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('tracking/product_tracking');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('tracking/product_tracking');
        $this->load->model('user/user');
        $this->load->model('vendor/vendor'); // Load vendor model

        $this->getList();
    }

    protected function getList(): void {
        // Default sorting and pagination
        $sort = $this->request->get['sort'] ?? 'username';
        $order = $this->request->get['order'] ?? 'ASC';
        $page = $this->request->get['page'] ?? 1;

        $url = '';
        // for filter code Shubham Sir - 29/05/2025------------=====
        $filter_name  = $this->request->get['filter_name'] ?? '';
        $filter_email = $this->request->get['filter_email'] ?? '';
        // -------=================--
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
                'href' => $this->url->link('tracking/product_tracking', 'user_token=' . $user_token . $url, true)
            ]
        ];

        $data['add'] = ''; // Add URL if needed
        $data['delete'] = ''; // Delete URL if needed

        $limit = $this->config->get('config_limit_admin');
        $start = ($page - 1) * $limit;

        $filter_data = [
            'filter_name'  => $filter_name,
            'filter_email' => $filter_email,
            'sort'         => $sort,
            'order'        => $order,
            'start'        => $start,
            'limit'        => $limit
        ];
        

        // Fetch user and vendor data
        $user_results = $this->model_user_user->getUsers($filter_data);
        $vendor_results = $this->model_vendor_vendor->getVendors($filter_data); // You need to create this

       
        $added_by_counts_raw = $this->model_tracking_product_tracking->getProductCountByAddedBy();


        $added_by_counts = [];
        foreach ($added_by_counts_raw as $row) {
            $added_by_counts[$row['added_by']] = $row['total'];
        }


       $combined_list = [];

        // Step 3: Combine users
        foreach ($user_results as $result) {
            $display_name = isset($result['username']) ? $result['username'] : '';
            if (empty($display_name)) {
                $firstname = isset($result['firstname']) ? $result['firstname'] : '';
                $lastname = isset($result['lastname']) ? $result['lastname'] : '';
                $display_name = trim($firstname . ' ' . $lastname);
            }

            $combined_list[] = [
                'id'            => $result['user_id'],
                'username'      => $display_name,
                'email'         => isset($result['email']) ? $result['email'] : '',
                'status'        => (!empty($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
                'type'          => 'User',
                'date_added'    => isset($result['date_added']) ? date($this->language->get('date_format_short'), strtotime($result['date_added'])) : '',
                'product_total' => isset($added_by_counts[$display_name]) ? $added_by_counts[$display_name] : 0,
                'view'          => $this->url->link('tracking/product_add_history', 'user_token=' . $this->session->data['user_token'] . '&added_by=' . urlencode($display_name), true)
            ];
        }

        // Step 4: Combine vendors
        foreach ($vendor_results as $result) {
            $display_name = isset($result['username']) ? $result['username'] : '';
            if (empty($display_name)) {
                $firstname = isset($result['firstname']) ? $result['firstname'] : '';
                $lastname = isset($result['lastname']) ? $result['lastname'] : '';
                $display_name = trim($firstname . ' ' . $lastname);
            }

            $combined_list[] = [
                'id'            => $result['vendor_id'],
                'username'      => $display_name,
                'email'         => isset($result['email']) ? $result['email'] : '',
                'status'        => (!empty($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
                'type'          => 'Vendor',
                'date_added'    => isset($result['date_added']) ? date($this->language->get('date_format_short'), strtotime($result['date_added'])) : '',
                'product_total' => isset($added_by_counts[$display_name]) ? $added_by_counts[$display_name] : 0,
                'view'          => $this->url->link('tracking/product_add_history', 'user_token=' . $this->session->data['user_token'] . '&added_by=' . urlencode($display_name), true)
            ];
        }


        $data['combined_list'] = $combined_list;



        // Set other pagination and sort variables as needed (you can adapt later if you paginate both separately)
        $data['sort'] = $sort;
        $data['order'] = $order;
        // for filter ====--------=
        $data['filter_name']  = $filter_name;
        $data['filter_email'] = $filter_email;
        // ------========-----

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['user_token'] = $this->session->data['user_token'];

        $this->response->setOutput($this->load->view('tracking/product_tracking', $data));
    }
    
    // for filter autocomplete Shubham Sir - 29/05/2025===========
   public function autocompleteName() {
        $json = [];
    
        if (isset($this->request->get['filter_name'])) {
            // ✅ Load required models
            $this->load->model('user/user');
            $this->load->model('vendor/vendor');
    
            $filter_data = [
                'filter_name' => $this->request->get['filter_name'],
                'start'       => 0,
                'limit'       => 5
            ];
    
            // ✅ Get users and vendors
            $users = $this->model_user_user->getUsers($filter_data);
            $vendors = $this->model_vendor_vendor->getVendors($filter_data);
    
            $results = array_merge($users, $vendors);
    
            foreach ($results as $result) {
                $json[] = [
                    'user_id'   => isset($result['user_id']) ? $result['user_id'] : '',
                    'vendor_id' => isset($result['vendor_id']) ? $result['vendor_id'] : '',
                    'name'      => isset($result['username']) && $result['username']
                                    ? $result['username']
                                    : (isset($result['firstname']) || isset($result['lastname'])
                                        ? trim($result['firstname'] . ' ' . $result['lastname'])
                                        : (isset($result['name']) ? $result['name'] : '')
                                    )
                ];
            }
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

   public function autocompleteEmail() {
        $json = [];
    
        if (isset($this->request->get['filter_email'])) {
            // ✅ Load models
            $this->load->model('user/user');
            $this->load->model('vendor/vendor');
    
            $filter_data = [
                'filter_email' => $this->request->get['filter_email'],
                'start'        => 0,
                'limit'        => 5
            ];
    
            // ✅ Get users and vendors
            $users = $this->model_user_user->getUsers($filter_data);
            $vendors = $this->model_vendor_vendor->getVendors($filter_data);
    
            $results = array_merge($users, $vendors);
    
            foreach ($results as $result) {
                $json[] = [
                    'user_id'   => isset($result['user_id']) ? $result['user_id'] : '',
                    'vendor_id' => isset($result['vendor_id']) ? $result['vendor_id'] : '',
                    'email'     => isset($result['email']) ? $result['email'] : ''
                ];
            }
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }   

    // ===---------------=========
}




