<?php
class ControllerIpofferOffer extends Controller {
    private $error = [];

    public function index() {
        $this->load->language('ipoffer/offer');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('ipoffer/offer');

        $this->getList();
    }

    public function add() {
        $this->load->language('ipoffer/offer');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('ipoffer/offer');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->validateForm()) {
                $result = $this->model_ipoffer_offer->addOffer($this->request->post);
                if ($result === false) {
                    $this->error['warning'] = 'This offer name already exists! You can only add each offer once.';
                } else {
                    $this->session->data['success'] = $this->language->get('text_success');
                    $this->response->redirect($this->url->link('ipoffer/offer', 'user_token=' . $this->session->data['user_token'], true));
                }
            }
        }

        $this->getForm();
    }
    
    public function edit() {
        $this->load->language('ipoffer/offer');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('ipoffer/offer');
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->validateForm()) {
                $this->model_ipoffer_offer->editOffer($this->request->get['ipoffer_id'], $this->request->post);
                
                $this->session->data['success'] = $this->language->get('text_success');
                
                $this->response->redirect($this->url->link('ipoffer/offer', 'user_token=' . $this->session->data['user_token'], true));
            }
        }
        
        $this->getForm();
    }
    
    public function delete() {
        $this->load->language('ipoffer/offer');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('ipoffer/offer');
        
        if (isset($this->request->get['ipoffer_id'])) {
            $this->model_ipoffer_offer->deleteOffer($this->request->get['ipoffer_id']);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('ipoffer/offer', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $this->getList();
    }

    public function view() {
        $this->load->language('ipoffer/offer');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('ipoffer/offer');
        
        $this->getReferralList();
    }

    public function approve() {
        $this->load->language('ipoffer/offer');
        $this->load->model('ipoffer/offer');
        
        if (isset($this->request->get['referral_id'])) {
            $this->model_ipoffer_offer->approveReferral($this->request->get['referral_id']);
            $this->session->data['success'] = 'Referral approved successfully!';
        }
        
        $this->response->redirect($this->url->link('ipoffer/offer/view', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function disapprove() {
        $this->load->language('ipoffer/offer');
        $this->load->model('ipoffer/offer');
        
        if (isset($this->request->get['referral_id'])) {
            $this->model_ipoffer_offer->disapproveReferral($this->request->get['referral_id']);
            $this->session->data['success'] = 'Referral disapproved successfully!';
        }
        
        $this->response->redirect($this->url->link('ipoffer/offer/view', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'ipoffer/offer')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if ((utf8_strlen($this->request->post['offer_name']) < 1) || (utf8_strlen($this->request->post['offer_name']) > 255)) {
            $this->error['offer_name'] = $this->language->get('error_offer_name');
        }
        
        if (!isset($this->request->post['percentage']) || !preg_match('/^[1-9]$/', $this->request->post['percentage'])) {
            $this->error['percentage'] = $this->language->get('error_percentage');
        }
        
        return !$this->error;
    }

    protected function getList() {
        $this->load->language('ipoffer/offer');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('ipoffer/offer');
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('ipoffer/offer', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['offers'] = [];

        $results = $this->model_ipoffer_offer->getOffers();

        foreach ($results as $result) {
            $data['offers'][] = [
                'ipoffer_id'   => $result['ipoffer_id'],
                'offer_name'   => $result['offer_name'],
                'percentage'   => $result['percentage'],
                'offer_type'   => isset($result['offer_type']) ? $result['offer_type'] : 'first_time',
                'status'       => $result['status'],
                'date_added'   => $result['date_added'],
                'date_modified'=> $result['date_modified'],
                'edit'         => $this->url->link('ipoffer/offer/edit', 'user_token=' . $this->session->data['user_token'] . '&ipoffer_id=' . $result['ipoffer_id'], true),
                'delete'       => $this->url->link('ipoffer/offer/delete', 'user_token=' . $this->session->data['user_token'] . '&ipoffer_id=' . $result['ipoffer_id'], true),
                'view'         => isset($result['offer_type']) && $result['offer_type'] == 'referral' ? $this->url->link('ipoffer/offer/view', 'user_token=' . $this->session->data['user_token'], true) : ''
            ];
        }

        $data['add'] = $this->url->link('ipoffer/offer/add', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('ipoffer/offer', $data));
    }

    protected function getReferralList() {
        $this->load->language('ipoffer/offer');
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('ipoffer/offer', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => 'Referral Customers',
            'href' => $this->url->link('ipoffer/offer/view', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['referrals'] = [];
        $results = $this->model_ipoffer_offer->getReferralCustomers();
        foreach ($results as $result) {
            $status_text = '';
            switch ($result['status']) {
                case 0:
                    $status_text = 'Pending';
                    break;
                case 1:
                    $status_text = 'Approved';
                    break;
                case 2:
                    $status_text = 'Disapproved';
                    break;
            }
            $data['referrals'][] = [
                'referral_id'    => $result['referral_id'],
                'customer_name'  => $result['customer_name'],
                'customer_email' => $result['customer_email'],
                'refer_code'     => $result['refer_code'],
                'refer_link'     => $result['refer_link'],
                'visit'          => isset($result['visit']) ? $result['visit'] : 0,
                'conversion'     => $result['conversion'],
                'earned'         => $result['earned'],
                'status'         => $result['status'],
                'status_text'    => $status_text,
                'date_added'     => $result['date_added'],
                'date_modified'  => $result['date_modified'],
                'approve'        => $this->url->link('ipoffer/offer/approve', 'user_token=' . $this->session->data['user_token'] . '&referral_id=' . $result['referral_id'], true),
                'disapprove'     => $this->url->link('ipoffer/offer/disapprove', 'user_token=' . $this->session->data['user_token'] . '&referral_id=' . $result['referral_id'], true),
                'view'           => $this->url->link('ipoffer/offer/referredbuyers', 'user_token=' . $this->session->data['user_token'] . '&referral_id=' . $result['referral_id'], true)
            ];
        }
        $data['back'] = $this->url->link('ipoffer/offer', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['referral_buyers']= $this->url->link('ipoffer/offer/referral_buyers', 'user_token=' . $this->session->data['user_token'], true);
        $this->response->setOutput($this->load->view('ipoffer/referral_list', $data));
    }

    protected function getForm() {
        $this->load->language('ipoffer/offer');
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['offer_name'])) {
            $data['error_offer_name'] = $this->error['offer_name'];
        } else {
            $data['error_offer_name'] = '';
        }
        
        if (isset($this->error['percentage'])) {
            $data['error_percentage'] = $this->error['percentage'];
        } else {
            $data['error_percentage'] = '';
        }
        
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('ipoffer/offer', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        if (!isset($this->request->get['ipoffer_id'])) {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_add'),
                'href' => $this->url->link('ipoffer/offer/add', 'user_token=' . $this->session->data['user_token'], true)
            ];
            
            $data['action'] = $this->url->link('ipoffer/offer/add', 'user_token=' . $this->session->data['user_token'], true);
            $data['text_form'] = $this->language->get('text_add');
        } else {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('ipoffer/offer/edit', 'user_token=' . $this->session->data['user_token'] . '&ipoffer_id=' . $this->request->get['ipoffer_id'], true)
            ];
            
            $data['action'] = $this->url->link('ipoffer/offer/edit', 'user_token=' . $this->session->data['user_token'] . '&ipoffer_id=' . $this->request->get['ipoffer_id'], true);
            $data['text_form'] = $this->language->get('text_edit');
        }
        
        $data['cancel'] = $this->url->link('ipoffer/offer', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];
        
        if (isset($this->request->get['ipoffer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $offer_info = $this->model_ipoffer_offer->getOffer($this->request->get['ipoffer_id']);
        }
        
        if (isset($this->request->post['offer_name'])) {
            $data['offer_name'] = $this->request->post['offer_name'];
        } elseif (!empty($offer_info)) {
            $data['offer_name'] = $offer_info['offer_name'];
        } else {
            $data['offer_name'] = '';
        }
        
        if (isset($this->request->post['percentage'])) {
            $data['percentage'] = $this->request->post['percentage'];
        } elseif (!empty($offer_info)) {
            $data['percentage'] = $offer_info['percentage'];
        } else {
            $data['percentage'] = '';
        }

        if (isset($this->request->post['offer_type'])) {
            $data['offer_type'] = $this->request->post['offer_type'];
        } elseif (!empty($offer_info)) {
            $data['offer_type'] = isset($offer_info['offer_type']) ? $offer_info['offer_type'] : 'first_time';
        } else {
            $data['offer_type'] = 'first_time';
        }
        
        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($offer_info)) {
            $data['status'] = $offer_info['status'];
        } else {
            $data['status'] = 1;
        }
        
        if (isset($this->request->get['ipoffer_id'])) {
            $data['ipoffer_id'] = $this->request->get['ipoffer_id'];
        } else {
            $data['ipoffer_id'] = 0;
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('ipoffer/offer_form', $data));
    }

    public function referral_buyers() {
        $this->load->language('ipoffer/offer');
        $this->document->setTitle('Referred Buyers');
        $this->load->model('ipoffer/offer');

        $referral_id = isset($this->request->get['referral_id']) ? (int)$this->request->get['referral_id'] : 0;
        $buyers = [];
        if ($referral_id) {
            // Get all conversions for this referral with full details
            $conversions = $this->model_ipoffer_offer->getReferralConversionsByReferralId($referral_id);
            foreach ($conversions as $row) {
                $buyers[] = [
                    'order_id'        => $row['order_id'],
                    'order_date'      => $row['order_date'],
                    'customer_name'   => isset($row['customer_name']) ? $row['customer_name'] : '',
                    'customer_email'  => isset($row['customer_email']) ? $row['customer_email'] : '',
                    'product_id'      => $row['product_id'],
                    'product_name'    => $row['product_name'],
                    'price'           => $row['price'],
                    'quantity'        => $row['quantity'],
                    'status'          => $row['status'],
                    'earned'          => $row['earned'],
                ];
            }
        }
        $data['buyers'] = $buyers;
        $referrer_name = '';
        $referrer_email = '';
        if ($referral_id) {
            $referral = $this->model_ipoffer_offer->getReferralById($referral_id);
            if ($referral) {
                $referrer_name = $referral['customer_name'];
                $referrer_email = $referral['customer_email'];
            }
        }
        $data['referrer_name'] = $referrer_name;
        $data['referrer_email'] = $referrer_email;
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('ipoffer/referral_buyers', $data));
    }
}