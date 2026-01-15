<?php
class ControllerVendorSellerPayments extends Controller {
    public function index() {
        $this->load->language('vendor/seller_payments');
        $this->document->setTitle('Seller Payments');
    
        $this->load->model('vendor/seller_payments');
    
        $filter_data = []; // Add your filter logic if needed
    
        $results = $this->model_vendor_seller_payments->getSellerPayments($filter_data);
    
        $data['sellers'] = [];

        foreach ($results as $result) {
            $data['sellers'][] = array(
                'vendor_id'      => $result['vendor_id'],
                'firstname'      => $result['firstname'],
                'lastname'       => $result['lastname'],
                'payment_method' => $result['payment_method'],
                'amount'         => $result['amount'],
                'date_added'     => $result['date_added'],
                'reference_number' => $result['reference_number'],
                'view'           => $this->url->link(
                    'vendor/income_view/view',
                    'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $result['vendor_id'] . 
                    '&reference_number=' . $result['reference_number'] . 
                    '&date=' . $result['date_added'],
                    true
                ),
            );
        }
        
        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Seller Payments',
            'href' => $this->url->link('vendor/seller_payments', 'user_token=' . $this->session->data['user_token'], true)
        );

        
    
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
    
        $this->response->setOutput($this->load->view('vendor/seller_payments', $data));
    }

    
}
