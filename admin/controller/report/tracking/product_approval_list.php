<?php
class ControllerTrackingProductApprovalList extends Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('tracking/product_approval_list');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('tracking/product_approval_list');

        $this->getList();
    }

    // protected function getList(): void {
    //     $user_token    = $this->session->data['user_token'];
    //     $approved_by   = $this->request->get['approved_by']   ?? null;
    //     $approved_date = $this->request->get['approved_date'] ?? null;

    //     $url = '';
    //     if ($approved_by) {
    //         $url .= '&approved_by=' . urlencode($approved_by);
    //     }
    //     if ($approved_date) {
    //         $url .= '&approved_date=' . urlencode($approved_date);
    //     }

    //     // Breadcrumbs
    //     $data['breadcrumbs'] = [
    //         [
    //             'text' => $this->language->get('text_home'),
    //             'href' => $this->url->link('common/dashboard', 'user_token=' . $user_token, true)
    //         ],
    //         [
    //             'text' => $this->language->get('heading_title_back'),
    //             'href' => $this->url->link('tracking/product_approval_history', 'user_token=' . $user_token . $url, true)
    //         ]
    //     ];

    //     // Filters in the form
    //     $data['filter_approved_by']   = $approved_by;
    //     $data['filter_approved_date'] = $approved_date;

    //     // Fetch from model
    //     if ($approved_by && $approved_date) {
    //         $results = $this->model_tracking_product_approval_list
    //             ->getProductsByApprovedByAndDate($approved_by, $approved_date);
    //     } else {
    //         $results = $this->model_tracking_product_approval_list
    //             ->getProductGroupedByApprovedByAndDate();
    //     }

    //     $this->load->model('tool/image');

    //     $data['products'] = [];
    //     foreach ($results as $row) {
    //         $data['products'][] = [
    //             'product_id'    => $row['product_id'],
    //             'name'          => $row['name'],
    //             'model'         => $row['model'],
    //             'price'         => $this->currency->format($row['price'], $this->config->get('config_currency')),
    //             'quantity'      => (int)$row['quantity'],
    //             'status'        => $row['status']
    //                 ? $this->language->get('text_enabled')
    //                 : $this->language->get('text_disabled'),
    //             'added_by'       => $row['added_by'] ?? '',
    //             'edited_by'  => $row['edited_by'] ?? '',
    //             'approved_by'   => $row['approved_by'],
    //             'approved_date' => date($this->language->get('date_format_short'), strtotime($row['approved_date'])),
    //             'image'         => $this->model_tool_image->resize(
    //                 $row['image'] ?? 'no_image.png',
    //                 40, 40
    //             ),
    //             'view'           => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $row['product_id'], true)
    //         ];
    //     }

    //     $data['user_token']    = $user_token;
    //     $data['error_warning'] = '';

    //     $data['header']      = $this->load->controller('common/header');
    //     $data['column_left'] = $this->load->controller('common/column_left');
    //     $data['footer']      = $this->load->controller('common/footer');

    //     $this->response->setOutput(
    //         $this->load->view('tracking/product_approval_list', $data)
    //     );
    // }
    
    protected function getList(): void {
        $user_token    = $this->session->data['user_token'];
        $approved_by   = $this->request->get['approved_by']   ?? null;
        $approved_date = $this->request->get['approved_date'] ?? null;

        $url = '';
        if ($approved_by) {
            $url .= '&approved_by=' . urlencode($approved_by);
        }
        if ($approved_date) {
            $url .= '&approved_date=' . urlencode($approved_date);
        }

        // Breadcrumbs
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $user_token, true)
            ],
            [
                'text' => $this->language->get('heading_title_back'),
                'href' => $this->url->link('tracking/product_approval_history', 'user_token=' . $user_token . $url, true)
            ]
        ];

        // Filters in the form
        $data['filter_approved_by']   = $approved_by;
        $data['filter_approved_date'] = $approved_date;

        // Fetch from model
        if ($approved_by && $approved_date) {
            $results = $this->model_tracking_product_approval_list
                ->getProductsByApprovedByAndDate($approved_by, $approved_date);
        } else {
            $results = $this->model_tracking_product_approval_list
                ->getProductGroupedByApprovedByAndDate();
        }

        $this->load->model('tool/image');

        $data['products'] = [];
        foreach ($results as $row) {
            $data['products'][] = [
                'product_id'    => $row['product_id'],
                'name'          => $row['name'],
                'model'         => $row['model'],
                'price'         => $this->currency->format($row['price'], $this->config->get('config_currency')),
                'quantity'      => (int)$row['quantity'],
                'status'        => $row['status']
                    ? $this->language->get('text_enabled')
                    : $this->language->get('text_disabled'),
                'added_by'       => $row['added_by'] ?? '',
                'edited_by'  => $row['edited_by'] ?? '',
                'approved_by'   => $row['approved_by'],
                'approved_date' => date($this->language->get('date_format_short'), strtotime($row['approved_date'])),
                'image'         => $this->model_tool_image->resize(
                    $row['image'] ?? 'no_image.png',
                    40, 40
                ),
                'view'           => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $row['product_id'], true)
            ];
        }

        $data['user_token']    = $user_token;
        $data['error_warning'] = '';

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput( $this->load->view('tracking/product_approval_list', $data)
        );
    }

    // for filter-------------============
    public function filter(): void {
        $this->load->language('tracking/product_approval_list');
        $this->load->model('tracking/product_approval_list');
        $this->load->model('tool/image');
    
        $user_token = $this->session->data['user_token'];
    
        // Get all filters from GET
        $filter_approved_by   = $this->request->get['approved_by'] ?? null;
        $filter_approved_date = $this->request->get['approved_date'] ?? null;
    
        $filter_name       = $this->request->get['filter_name'] ?? null;
        $filter_model      = $this->request->get['filter_model'] ?? null;
        $filter_price      = $this->request->get['filter_price'] ?? null;
        $filter_quantity   = $this->request->get['filter_quantity'] ?? null;
        $filter_status     = $this->request->get['filter_status'] ?? null;
        $filter_added_by   = $this->request->get['filter_added_by'] ?? null;
        $filter_edited_by  = $this->request->get['filter_edited_by'] ?? null;
    
        // Fetch initial result from model (either filtered by approved_by/date or all)
        if ($filter_approved_by && $filter_approved_date) {
            $results = $this->model_tracking_product_approval_list->getProductsByApprovedByAndDate($filter_approved_by, $filter_approved_date);
        } else {
            $results = $this->model_tracking_product_approval_list->getProductGroupedByApprovedByAndDate();
        }
    
        // Further filter results by your new filters (PHP filtering)
        $filtered = array_filter($results, function($product) use (
            $filter_name, $filter_model, $filter_price, $filter_quantity, 
            $filter_status, $filter_added_by, $filter_edited_by
        ) {
            if ($filter_name && stripos($product['name'], $filter_name) === false) {
                return false;
            }
            if ($filter_model && stripos($product['model'], $filter_model) === false) {
                return false;
            }
            if ($filter_price && $product['price'] != $filter_price) {
                return false;
            }
            if ($filter_quantity && (int)$product['quantity'] != (int)$filter_quantity) {
                return false;
            }
            if ($filter_status !== null && $filter_status !== '' && (int)$product['status'] !== (int)$filter_status) {
                return false;
            }
            if ($filter_added_by && stripos($product['added_by'], $filter_added_by) === false) {
                return false;
            }
            if ($filter_edited_by && stripos($product['edited_by'], $filter_edited_by) === false) {
                return false;
            }
            return true;
        });
    
        // Prepare $data['products'] for view as in getList()
        $data['products'] = [];
        foreach ($filtered as $row) {
            $data['products'][] = [
                'product_id'    => $row['product_id'],
                'name'          => $row['name'],
                'model'         => $row['model'],
                'price'         => $this->currency->format($row['price'], $this->config->get('config_currency')),
                'quantity'      => (int)$row['quantity'],
                'status'        => $row['status']
                                    ? $this->language->get('text_enabled')
                                    : $this->language->get('text_disabled'),
                'added_by'      => $row['added_by'] ?? '',
                'edited_by'     => $row['edited_by'] ?? '',
                'approved_by'   => $row['approved_by'],
                'approved_date' => date($this->language->get('date_format_short'), strtotime($row['approved_date'])),
                'image'         => $this->model_tool_image->resize(
                    $row['image'] ?? 'no_image.png',
                    40, 40
                ),
                'view' => $this->url->link('catalog/product/edit', 'user_token=' . $user_token . '&product_id=' . $row['product_id'], true)
            ];
        }
    
        // Pass filters back to view so form inputs retain values
        $data['filter_name']      = $filter_name;
        $data['filter_model']     = $filter_model;
        $data['filter_price']     = $filter_price;
        $data['filter_quantity']  = $filter_quantity;
        $data['filter_status']    = $filter_status;
        $data['filter_added_by']  = $filter_added_by;
        $data['filter_edited_by'] = $filter_edited_by;
        $data['user_token']       = $user_token;
    
        // Load common parts & render view
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');
    
        $this->response->setOutput($this->load->view('tracking/product_approval_list', $data));
    }
    
}
