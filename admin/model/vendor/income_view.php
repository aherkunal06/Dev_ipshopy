<?php
class ControllerVendorIncomeView extends Controller {
    public function index() {
        $this->load->language('vendor/income'); // Optional: create this if needed
        $this->document->setTitle('Paid Income Orders');

        $this->load->model('vendor/income'); // You need to have this model

        // Get vendor_id from the URL
        $vendor_id = isset($this->request->get['vendor_id']) ? (int)$this->request->get['vendor_id'] : 0;

        // Fetch vendor product data (you should have this function in model_vendor_income)
        $data['vendor_products'] = $this->model_vendor_income->getVendorPaidProductDetails($vendor_id);

        // Breadcrumbs
        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => 'Income View',
            'href' => $this->url->link('vendor/seller_payments', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['vendor_id'] = $vendor_id;

        $data['cancel'] = $this->url->link('vendor/seller_payments', 'user_token=' . $this->session->data['user_token'], true);
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Load standard layout
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('vendor/income_view', $data));
    }

    public function view() {
        $this->load->language('vendor/income');
        $this->document->setTitle($this->language->get('heading_title')); // Uses lang file
    
        $this->load->model('vendor/income');
    
        $vendor_id = isset($this->request->get['vendor_id']) ? (int)$this->request->get['vendor_id'] : 0;
    
        $data['vendor_products'] = $this->model_vendor_income->getVendorPaidProductDetails($vendor_id);
        $data['vendor_id'] = $vendor_id;
    
        // Breadcrumbs (optional, cleaner UI)
        $data['breadcrumbs'] = [];
    
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
    
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_seller_payments'),
            'href' => $this->url->link('vendor/seller_payments', 'user_token=' . $this->session->data['user_token'], true)
        ];
    
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_income_view'),
            'href' => $this->url->link('vendor/income_view/view', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $vendor_id, true)
        ];
    
        $data['cancel'] = $this->url->link('vendor/seller_payments', 'user_token=' . $this->session->data['user_token'], true);
        $data['button_cancel'] = $this->language->get('button_cancel');
    
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
    
        $this->response->setOutput($this->load->view('vendor/income_view', $data));
    }
    
}
