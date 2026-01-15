<?php
require_once(DIR_SYSTEM . 'library/pagination.php');

class ControllerCatalogHsnCode extends Controller {
    public function index() {
        $this->load->language('catalog/hsn_code');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/hsn_code');

        // ✅ Get user token
        $user_token = $this->request->get['user_token'];
        $data['user_token'] = $user_token;

        // ✅ Get filters
        $filter_hsn_code = isset($this->request->get['filter_hsn_code']) ? $this->request->get['filter_hsn_code'] : '';
        $filter_description = isset($this->request->get['filter_description']) ? $this->request->get['filter_description'] : '';
        $filter_gst_rate = isset($this->request->get['filter_gst_rate']) ? $this->request->get['filter_gst_rate'] : '';

        $data['filter_hsn_code'] = $filter_hsn_code;
        $data['filter_description'] = $filter_description;
        $data['filter_gst_rate'] = $filter_gst_rate;

        // ✅ Setup filter URL string
        $url = '';
        if ($filter_hsn_code) {
            $url .= '&filter_hsn_code=' . urlencode($filter_hsn_code);
        }
        if ($filter_description) {
            $url .= '&filter_description=' . urlencode($filter_description);
        }
        if ($filter_gst_rate) {
            $url .= '&filter_gst_rate=' . urlencode($filter_gst_rate);
        }

        // ✅ Pagination
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        $limit = 100;
        $start = ($page - 1) * $limit;

        // ✅ Prepare filter data for model
        $data_filter = [
            'start'               => $start,
            'limit'               => $limit,
            'filter_hsn_code'     => $filter_hsn_code,
            'filter_description'  => $filter_description,
            'filter_gst_rate'     => $filter_gst_rate
        ];

        // ✅ Fetch data
        $data['hsn_codes'] = $this->model_catalog_hsn_code->getHSNCodes($data_filter);
        $total = $this->model_catalog_hsn_code->getTotalHSNCodes($data_filter);

        // ✅ Pagination rendering
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('catalog/hsn_code', 'user_token=' . $user_token . $url . '&page={page}', true);
        $data['pagination'] = $pagination->render();

        // ✅ Form submission (Add new HSN code)
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_catalog_hsn_code->addHSNCode($this->request->post);
            $this->session->data['success'] = 'HSN Code Added Successfully!';
            $this->response->redirect($this->url->link('catalog/hsn_code', 'user_token=' . $user_token, true));
        }

        // ✅ Action URL for both form and filter
        $data['filter_action'] = 'index.php?route=catalog/hsn_code&user_token=' . $user_token . $url;


        // index.php?route=catalog/hsn_code&filter_hsn_code=8411&user_token=pfkJqLInX2TtapVapEKfPTPEUNpBbG46



        // ✅ Layout controllers
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('catalog/hsn_code_list', $data));
    }

    protected function validateForm() {
        return true;
    }

    public function update() {
        $this->load->language('catalog/hsn_code');
        $this->load->model('catalog/hsn_code');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $this->model_catalog_hsn_code->updateHSNCode($this->request->post);
            $json['success'] = $this->language->get('text_success');
        } else {
            $json['error'] = $this->language->get('error_permission');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
}
