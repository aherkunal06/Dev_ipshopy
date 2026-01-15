<?php
class ControllerTrackingProductAddHistory extends Controller {

 private $error = array();

    public function index(): void {
        $this->load->language('tracking/product_add_history');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('tracking/product_add_history');

        $this->getList();
    }

    protected function getList(): void {
        $user_token = $this->session->data['user_token'];

        $url = '';
        $filter_date = $this->request->get['filter_date'] ?? null;
        if ($filter_date) {
            $url .= '&filter_date=' . urlencode($filter_date);
        }
        if (isset($this->request->get['added_by'])) {
            $url .= '&added_by=' . urlencode($this->request->get['added_by']);
        }

        // Breadcrumbs
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $user_token, true)
            ],
            [
                'text' => 'Product Add User List',
                'href' => $this->url->link('tracking/product_tracking', 'user_token=' . $user_token . $url, true)
            ]
        ];

        // Fetch and prepare product data
        $added_by = $this->request->get['added_by'] ?? null;
        $data['added_by_name'] = $added_by;

        $product_rows = $this->model_tracking_product_add_history->getProductGroupedByAddedByAndDate($added_by, $filter_date);
        

        // Add view links for each group (if not filtered)
        $data['products'] = [];

        foreach ($product_rows as $row) {
            $display_name = $row['added_by'];

            $data['products'][] = [
                'added_by'    => $display_name,
                'date_added'  => $row['date_added'] ?? '',
                'product_name'=> $row['product_name'] ?? '',
                'approved'     => $row['approved'] ?? 0,
                'disapproved'  => $row['disapproved'] ?? 0,
                'pending'      => $row['pending'] ?? 0,
                'total'        => $row['total'] ?? 0,
                'view' => $this->url->link(
                'tracking/product_add_list',
                'user_token=' . $this->session->data['user_token'] .
                '&added_by=' . urlencode($row['added_by']) .
                '&date_added=' . urlencode($row['date_added']),
                true)
            ];
        }
        
        // Pass the date filter to the view to prefill filter input
        $data['filter_date_added'] = $filter_date;

        $data['user_token'] = $user_token;

        // Load common parts
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('tracking/product_add_history', $data));
    }
    
    // for filter=============-----------
    public function filter(): void {
        $this->load->model('tracking/product_add_history');
    
        // Get both filters from AJAX GET parameters
        $filter_date = $this->request->get['filter_date_added'] ?? null;
        $added_by = $this->request->get['added_by'] ?? null; // ✅ this was missing
    
        // Pass both filters to the model
        $results = $this->model_tracking_product_add_history->getProductGroupedByAddedByAndDate($added_by, $filter_date);
    
        $json = [];
    
        foreach ($results as $row) {
            $json[] = [
                'username' => $row['added_by'],
                'date_added' => $row['date_added'],
                'total_products' => $row['total'],
                'approved_products' => $row['approved'],
                'disapproved_products' => $row['disapproved'],
                'pending_products' => $row['pending'],
                'view_link' => $this->url->link(
                    'tracking/product_add_list',
                    'user_token=' . $this->session->data['user_token'] .
                    '&added_by=' . urlencode($row['added_by']) .
                    '&date_added=' . urlencode($row['date_added']),
                    true
                )
            ];
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    // --------====================

    
}


