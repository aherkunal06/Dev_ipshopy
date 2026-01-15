<?php
class ControllerTrackingProductAddList extends Controller {
    private $error = array();
    public function index(): void {
        $this->load->language('tracking/product_add_list');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('tracking/product_add_list');

        $this->getList();
    }

    // protected function getList(): void {

    //     $user_token = $this->session->data['user_token'];
        
    //     $added_by = $this->request->get['added_by'] ?? null;
    //     $date_added = $this->request->get['date_added'] ?? null;

    //     $this->load->model('tool/image');

    //     // ✅ Define missing variables
    //     $user_token = $this->session->data['user_token'];
    //     $url = ''; // You can build this dynamically if needed


    //     $data['added_by_name'] = $added_by;
    //     $data['date_added'] = $date_added;


    //      // Breadcrumbs
    //     if (isset($this->request->get['added_by'])) {
    //         $url .= '&added_by=' . urlencode($this->request->get['added_by']);
    //     }

    //     if (isset($this->request->get['date_added'])) {
    //         $url .= '&date_added=' . urlencode($this->request->get['date_added']);
    //     }

    //     $data['breadcrumbs'] = [
    //         [
    //             'text' => $this->language->get('text_home'),
    //             'href' => $this->url->link('common/dashboard', 'user_token=' . $user_token, true)
    //         ],
    //         [
    //             'text' => 'Product Add History',
    //             'href' => $this->url->link('tracking/product_add_history', 'user_token=' . $user_token . $url, true)
    //         ]
    //     ];

    //     if ($added_by && $date_added) {
    //         // Get products for specific user/date
    //         $results = $this->model_tracking_product_add_list->getProductsByAddedByAndDate($added_by, $date_added);
    //     } 
    //     // else {
    //     //     // Get grouped summary
    //     //     $results = $this->model_tracking_product_add_list->getProductGroupedByAddedByAndDate($added_by);
    //     // }

    //     $data['products'] = [];

    //     foreach ($results as $result) {
    //         $data['products'][] = [
    //             'product_id'     => $result['product_id'] ?? '',
    //             'model'          => $result['model'] ?? '',
    //             'name'           => $result['product_name'] ?? '',
    //             'price'          => $this->currency->format($result['price'],
    //              $this->config->get('config_currency')),
    //             'quantity'   => $result['quantity'] ?? 0,
    //             'status'         => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
    //             'added_by'       => $result['added_by'] ?? '',
    //             'edited_by'  => $result['edited_by'] ?? '',
    //             'date_added'     => $result['date_added'] ?? '',
    //             'image'          => $this->model_tool_image->resize(
    //                 $result['product_image'] ?? 'no_image.png',
    //                 40,
    //                 40
    //             ),
    //             'view'           => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'], true)
    //         ];
    //     }

    //     $data['user_token'] = $user_token;
    //     $data['error_warning'] = '';

    //     $data['header'] = $this->load->controller('common/header');
    //     $data['column_left'] = $this->load->controller('common/column_left');
    //     $data['footer'] = $this->load->controller('common/footer');

    //     $this->response->setOutput($this->load->view('tracking/product_add_list', $data));
    // }
    
    protected function getList(): void {
        $this->load->model('tool/image');
        $this->load->model('tracking/product_add_list');
    
        $user_token = $this->session->data['user_token'];
        $url = '';
    
        // Mandatory filters from URL (2nd layer)
        $added_by = $this->request->get['added_by'] ?? '';
        $date_added = $this->request->get['date_added'] ?? '';
    
        // Optional filters (3rd layer)
        $filter_name      = $this->request->get['filter_name'] ?? '';
        $filter_model     = $this->request->get['filter_model'] ?? '';
        $filter_price     = $this->request->get['filter_price'] ?? '';
        $filter_quantity  = $this->request->get['filter_quantity'] ?? '';
        $filter_status    = $this->request->get['filter_status'] ?? '';
        $filter_added_by  = $this->request->get['filter_added_by'] ?? '';
        $filter_edited_by = $this->request->get['filter_edited_by'] ?? '';
    
        // Build URL for pagination/links with all filters preserved
        $filters_for_url = [
            'added_by'        => $added_by,
            'date_added'      => $date_added,
            'filter_name'     => $filter_name,
            'filter_model'    => $filter_model,
            'filter_price'    => $filter_price,
            'filter_quantity' => $filter_quantity,
            'filter_status'   => $filter_status,
            'filter_added_by' => $filter_added_by,
            'filter_edited_by'=> $filter_edited_by
        ];
    
        foreach ($filters_for_url as $key => $value) {
            if ($value !== '') {
                $url .= '&' . $key . '=' . urlencode($value);
            }
        }
    
        // Prepare filter data for model
        $filter_data = [
            'added_by'        => $added_by,
            'date_added'      => $date_added,
            'filter_name'     => $filter_name,
            'filter_model'    => $filter_model,
            'filter_price'    => $filter_price,
            'filter_quantity' => $filter_quantity,
            'filter_status'   => $filter_status,
            'filter_added_by' => $filter_added_by,
            'filter_edited_by'=> $filter_edited_by
        ];
    
        // Fetch filtered products from model
        $results = $this->model_tracking_product_add_list->getFilteredProducts($filter_data);
    
        $data['products'] = [];
    
        foreach ($results as $result) {
            $data['products'][] = [
                'product_id' => $result['product_id'] ?? '',
                'model'      => $result['model'] ?? '',
                'name'       => $result['product_name'] ?? '',
                'price'      => $this->currency->format($result['price'], $this->config->get('config_currency')),
                'quantity'   => $result['quantity'] ?? 0,
                'status'     => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'added_by'   => $result['added_by'] ?? '',
                'edited_by'  => $result['edited_by'] ?? '',
                'date_added' => $result['date_added'] ?? '',
                'image'      => $this->model_tool_image->resize($result['product_image'] ?? 'no_image.png', 40, 40),
                'view'       => $this->url->link('catalog/product/edit', 'user_token=' . $user_token . '&product_id=' . $result['product_id'], true)
            ];
        }
    
        // Pass filter values back to view to fill inputs
        $data['added_by']        = $added_by;
        $data['date_added']      = $date_added;
        $data['filter_name']     = $filter_name;
        $data['filter_model']    = $filter_model;
        $data['filter_price']    = $filter_price;
        $data['filter_quantity'] = $filter_quantity;
        $data['filter_status']   = $filter_status;
        $data['filter_added_by'] = $filter_added_by;
        $data['filter_edited_by']= $filter_edited_by;
    
        $data['user_token'] = $user_token;
    
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $user_token, true)
            ],
            [
                'text' => 'Product Add History',
                'href' => $this->url->link('tracking/product_add_history', 'user_token=' . $user_token . $url, true)
            ]
        ];
    
        $data['error_warning'] = '';
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
    
        $this->response->setOutput($this->load->view('tracking/product_add_list', $data));
    }
    
}
