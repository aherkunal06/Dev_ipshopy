<?php
class ControllerVendorVacationList extends Controller {
    public function index() {
        $this->load->language('vendor/vacation');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('vendor/vacation');
        $this->load->model('vendor/vendor');

        // Validate session token
        if (!isset($this->session->data['user_token']) || !isset($this->request->get['user_token']) || $this->session->data['user_token'] != $this->request->get['user_token']) {
            $this->response->redirect($this->url->link('common/login', '', true));
        }

        // Get filters
        $filter_name = $this->request->get['filter_name'] ?? '';
        $filter_date = $this->request->get['filter_date'] ?? '';
        $filter_approved = $this->request->get['filter_approved'] ?? '';
        $filter_status = $this->request->get['filter_status'] ?? '';
        $page = $this->request->get['page'] ?? 1;
        $limit = 20;

        $url = '';
        if ($filter_name) $url .= '&filter_name=' . urlencode($filter_name);
        if ($filter_date) $url .= '&filter_date=' . urlencode($filter_date);
        if ($filter_approved !== '') $url .= '&filter_approved=' . $filter_approved;
        if ($filter_status !== '') $url .= '&filter_status=' . $filter_status;

		

        // Filter data
        $filter_data = [
            'filter_name'     => $filter_name,
            'filter_date'     => $filter_date,
            'filter_approved' => $filter_approved,
            'filter_status'   => $filter_status,
            'start'           => ($page - 1) * $limit,
            'limit'           => $limit
        ];

        $vacation_total = $this->model_vendor_vacation->getTotalVacations($filter_data);
        $results = $this->model_vendor_vacation->getVacations($filter_data);

        $data['vacations'] = [];

		

        foreach ($results as $result) {
            $vendor = $this->model_vendor_vendor->getVendor($result['vendor_id']);
            $processing_order_count = $this->model_vendor_vacation->getProcessingOrderCount($result['vendor_id']);
            $total_products_row = $this->model_vendor_vendor->getVendorProducts($result['vendor_id']);
            $total_products = $total_products_row['total'] ?? 0;

			

            $data['vacations'][] = [
                'vacation_id'   => $result['vacation_id'],
                'vendor_id'     => $result['vendor_id'],
                'vendor_name'   => ($vendor['firstname'] ?? '') . ' ' . ($vendor['lastname'] ?? ''),
                'display_name'  => $vendor['display_name'] ?? '',
                'processing_order_count' => $processing_order_count,
                'total_products'         => $total_products,
                'start_date'    => $result['start_date'],
                'end_date'      => $result['end_date'],
                'reason'        => $result['reason'],
                'status'        => $result['status'],
                'approval_date' => $result['approval_date'],
                'date_added'    => $result['date_added'],
                'view'          => $this->url->link('vendor/vacation_form', 'user_token=' . $this->session->data['user_token'] . '&vacation_id=' . $result['vacation_id'], true)
            ];
        }

        // Messages
        $data['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        // $data['error_warning'] = $this->session->data['error_warning'] ?? '';
        // unset($this->session->data['error_warning']);

        // Filters for view
        $data['filter_name'] = $filter_name;
        $data['filter_date'] = $filter_date;
        $data['filter_approved'] = $filter_approved;
        $data['filter_status'] = $filter_status;

        // Pagination
        $pagination = new Pagination();
        $pagination->total = $vacation_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('vendor/vacation_list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($vacation_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($vacation_total - $limit)) ? $vacation_total : ((($page - 1) * $limit) + $limit), $vacation_total, ceil($vacation_total / $limit));

        // Language entries
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['column_vendor'] = $this->language->get('column_vendor');
        $data['column_display_name'] = $this->language->get('column_display_name');
        $data['column_start_date'] = $this->language->get('column_start_date');
        $data['column_end_date'] = $this->language->get('column_end_date');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_reason'] = $this->language->get('column_reason');
        $data['column_action'] = $this->language->get('column_action');

        // Layout
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

		$data['user_token'] = $this->session->data['user_token'];

        $this->response->setOutput($this->load->view('vendor/vacation_list', $data));
    }

    public function approve() {
        if (isset($this->request->get['vacation_id'])) {
            $this->load->model('vendor/vacation');
            $this->model_vendor_vacation->updateVacationStatus($this->request->get['vacation_id'], 'Approved');
            $this->session->data['success'] = 'Vacation approved successfully!';
        }
        $this->response->redirect($this->url->link('vendor/vacation_list', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function reject() {
        if (isset($this->request->get['vacation_id'])) {
            $this->load->model('vendor/vacation');
            $this->model_vendor_vacation->updateVacationStatus($this->request->get['vacation_id'], 'Rejected');
            $this->session->data['success'] = 'Vacation rejected successfully!';
        }
        $this->response->redirect($this->url->link('vendor/vacation_list', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    
    public function autocomplete() {
        $json = [];
    
        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_status']) || isset($this->request->get['filter_date_added'])) {
            $this->load->model('vendor/vendor');
    
            $filter_data = [
                'filter_name'        => isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '',
                'filter_status'      => isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : '',
                'filter_date_added'  => isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : '',
                'start'              => 0,
                'limit'              => 5
            ];
    
            $results = $this->model_vendor_vendor->getVendors($filter_data);
    
            foreach ($results as $result) {
                $json[] = [
                    'vendor_id'  => $result['vendor_id'],
                    'vendorname' => $result['firstname'] . ' ' . $result['lastname']
                ];
            }
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
}