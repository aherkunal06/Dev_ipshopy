<?php
class ControllerIpofferOffer extends Controller {
    public function index() {
        $this->load->language('ipoffer/offer');
        $this->load->model('ipoffer/offer');
        
        $data['heading_title'] = $this->language->get('heading_title');
        
        // Get active offers
        $offers = $this->model_ipoffer_offer->getActiveOffers();
        $data['offers'] = [];
        
        foreach ($offers as $offer) {
            $data['offers'][] = [
                'offer_name' => $offer['offer_name'],
                'percentage' => $offer['percentage'],
                'offer_type' => isset($offer['offer_type']) ? $offer['offer_type'] : 'first_time'
            ];
        }
        
        // Check if user is logged in
        if ($this->customer->isLogged()) {
            $data['customer_logged'] = true;
            $data['customer_id'] = $this->customer->getId();
            $data['customer_name'] = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
            $data['customer_email'] = $this->customer->getEmail();
        } else {
            $data['customer_logged'] = false;
        }
        
        $data['action'] = $this->url->link('ipoffer/offer/submit', '', true);
        
        return $this->load->view('ipoffer/offer', $data);
    }
    
    public function submit() {
        $this->load->language('ipoffer/offer');
        $this->load->model('ipoffer/offer');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // Validate input
            if (empty($this->request->post['customer_name'])) {
                $json['error'] = 'Customer name is required!';
            }
            
            if (empty($this->request->post['customer_email'])) {
                $json['error'] = 'Customer email is required!';
            }
            
            if (empty($this->request->post['referrer_name'])) {
                $json['error'] = 'Referrer name is required!';
            }
            
            if (empty($this->request->post['referrer_email'])) {
                $json['error'] = 'Referrer email is required!';
            }
            
            if (!$json) {
                $referral_data = [
                    'customer_id' => isset($this->request->post['customer_id']) ? (int)$this->request->post['customer_id'] : 0,
                    'customer_name' => $this->request->post['customer_name'],
                    'customer_email' => $this->request->post['customer_email'],
                    'referrer_name' => $this->request->post['referrer_name'],
                    'referrer_email' => $this->request->post['referrer_email'],
                    'status' => 0 // Pending by default
                ];
                
                $this->model_ipoffer_offer->addReferralCustomer($referral_data);
                
                $json['success'] = 'Referral submitted successfully! It will be reviewed by admin.';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function getReferralOffer() {
        $this->load->model('ipoffer/offer');
        
        $json = [];
        
        if ($this->customer->isLogged()) {
            $customer_id = $this->customer->getId();
            
            // Check if customer has approved referral
            $referral = $this->model_ipoffer_offer->getReferralOffer();
            
            if ($referral) {
                // Check if this customer has an approved referral
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer_referral_customers WHERE customer_id = '" . (int)$customer_id . "' AND status = 1");
                
                if ($query->num_rows > 0) {
                    $json['success'] = true;
                    $json['offer'] = [
                        'offer_name' => $referral['offer_name'],
                        'percentage' => $referral['percentage']
                    ];
                } else {
                    $json['success'] = false;
                    $json['message'] = 'No approved referral found for this customer.';
                }
            } else {
                $json['success'] = false;
                $json['message'] = 'No active referral offer available.';
            }
        } else {
            $json['success'] = false;
            $json['message'] = 'Customer not logged in.';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
} 