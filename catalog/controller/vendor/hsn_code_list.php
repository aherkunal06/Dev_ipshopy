<?php
require_once(DIR_SYSTEM . 'library/pagination.php');

class ControllerVendorHsnCodeList extends Controller {
    public function index() {
        $this->load->language('vendor/hsn_request_form');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('vendor/hsn_code_list');

        // ✅ Filters
        $filter_hsn_code = $this->request->get['filter_hsn_code'] ?? '';
        $filter_description = $this->request->get['filter_description'] ?? '';
        $filter_gst_rate = $this->request->get['filter_gst_rate'] ?? '';

        $data['filter_hsn_code'] = $filter_hsn_code;
        $data['filter_description'] = $filter_description;
        $data['filter_gst_rate'] = $filter_gst_rate;
        
        $data['cancel'] = $this->url->link('vendor/dashboard');

        // ✅ Filter URL string for pagination/filter form
        $url = '';
        if ($filter_hsn_code) $url .= '&filter_hsn_code=' . urlencode($filter_hsn_code);
        if ($filter_description) $url .= '&filter_description=' . urlencode($filter_description);
        if ($filter_gst_rate) $url .= '&filter_gst_rate=' . urlencode($filter_gst_rate);

        // ✅ Pagination
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        $limit = 100;
        $start = ($page - 1) * $limit;

        $data_filter = [
            'start' => $start,
            'limit' => $limit,
            'filter_hsn_code' => $filter_hsn_code,
            'filter_description' => $filter_description,
            'filter_gst_rate' => $filter_gst_rate
        ];

        // ✅ Fetch HSN data
        $hsn_rows = $this->model_vendor_hsn_code_list->getHSNCodes($data_filter);
        $data['hsn_code'] = is_array($hsn_rows) ? $hsn_rows : [];

        // var_dump($data['hsn_code']); // Debugging line

        $total = $this->model_vendor_hsn_code_list->getTotalHSNCodes($data_filter);

        // ✅ Pagination Rendering
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('vendor/hsn_code_list', $url . '&page={page}');
        $data['pagination'] = $pagination->render();

        // ✅ Filter + form submission URL
        $data['filter_action'] = $this->url->link('vendor/hsn_code_list', $url);

        // ✅ Form submission (optional)
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_vendor_hsn_code_list->addHSNCode($this->request->post);
            $this->session->data['success'] = 'HSN Code Added Successfully!';
            $this->response->redirect($this->url->link('vendor/hsn_code_list'));
        }

                $data['hsn_request_list'] = $this->url->link('vendor/hsn_request_form', '', true);

        // ✅ Layout
        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        
        $this->response->setOutput($this->load->view('vendor/hsn_code_list', $data));
    }

    protected function validateForm() {
        // Add validation logic if needed
        return true;
    }

    public function update() {
        $this->load->language('vendor/hsn_code_list');
        $this->load->model('vendor/hsn_code_list');

        $json = [];

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $this->model_vendor_hsn_code_list->updateHSNCode($this->request->post);
            $json['success'] = $this->language->get('text_success');
        } else {
            $json['error'] = $this->language->get('error_permission');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
