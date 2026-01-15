<?php
class ControllerAccountAccount extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/account', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
			$this->load->model('account/customer');

	}
          

	$this->load->language('account/account');

    $data['breadcrumbs'] = array();

    $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_home'),
        'href' => $this->url->link('common/home')
    );

    $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_account'),
        'href' => $this->url->link('account/account', '', true)
    );

    // Success message
    if (isset($this->session->data['success'])) {
        $data['success'] = $this->session->data['success'];
        unset($this->session->data['success']);
    } else {
        $data['success'] = '';
    }

    // Load the rest of your data, view, etc.
    $data['heading_title'] = $this->language->get('heading_title');
    
    // ... (any other $data assignments)
	$data['column_left_account'] = $this->load->controller('account/column_left_account');
// 	var_dump($data['column_left_account']);
    $this->response->setOutput($this->load->view('account/account', $data));
		
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		
		$data['credit_cards'] = array();
		
		$files = glob(DIR_APPLICATION . 'controller/extension/credit_card/*.php');
		
		foreach ($files as $file) {
			$code = basename($file, '.php');
			
			if ($this->config->get('payment_' . $code . '_status') && $this->config->get('payment_' . $code . '_card')) {
				$this->load->language('extension/credit_card/' . $code, 'extension');

				$data['credit_cards'][] = array(
					'name' => $this->language->get('extension')->get('heading_title'),
					'href' => $this->url->link('extension/credit_card/' . $code, '', true)
				);
			}
		}
		
		$data['wishlist'] = $this->url->link('account/wishlist');
		$data['order'] = $this->url->link('account/order', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		
		if ($this->config->get('total_reward_status')) {
			$data['reward'] = $this->url->link('account/reward', '', true);
		} else {
			$data['reward'] = '';
		}		
		
		$data['return'] = $this->url->link('account/return', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['recurring'] = $this->url->link('account/recurring', '', true);
		
		$this->load->model('account/customer');
		
		$affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());
		
		if (!$affiliate_info) {	
			$data['affiliate'] = $this->url->link('account/affiliate/add', '', true);
		} else {
			$data['affiliate'] = $this->url->link('account/affiliate/edit', '', true);
		}
		
		if ($affiliate_info) {		
			$data['tracking'] = $this->url->link('account/tracking', '', true);
		} else {
			$data['tracking'] = '';
		}
		
	
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
// 		var_dump($data['column_right']);
// 		var_dump($data['column_left']);
		
		$this->response->setOutput($this->load->view('account/account', $data));

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->load->model('account/customer');
		
			// Update basic info
			$this->model_account_customer->editCustomernew([
				'firstname' => $this->request->post['firstname'],
				'lastname'  => $this->request->post['lastname'],
				'email'     => $this->request->post['email'],
				'telephone' => $this->request->post['telephone']
				
			]);
		
           // ✅ Image upload logic
			if (!empty($this->request->files['profile_photo']['name'])) {
				$file = $this->request->files['profile_photo'];
				$filename = basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8'));
		
				$allowed = ['jpg', 'jpeg', 'png', 'gif'];
				$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		
				if (in_array($ext, $allowed)) {
					$newName = 'profile_' . $this->customer->getId() . '_' . time() . '.' . $ext;
					$targetPath = DIR_IMAGE . 'catalog/customer_images/';
		
					// Create directory if it doesn't exist
					if (!is_dir($targetPath)) {
						mkdir($targetPath, 0755, true);
					}
		
					if (move_uploaded_file($file['tmp_name'], $targetPath . $newName)) {
						$this->model_account_customer->editProfileImage(
							$this->customer->getId(),
							'catalog/customer_images/' . $newName
						);
					}
				}
			}
		
			// Redirect back to account page after saving
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		

		$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
		if (!$this->customer->isLogged()) {
			$this->response->redirect($this->url->link('account/login', '', true));
		}
	
		$this->load->language('account/account');
		$this->load->model('account/customer');
	
		$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
	
		$data['firstname'] = $this->request->post['firstname'] ?? $customer_info['firstname'] ?? '';
		$data['lastname'] = $this->request->post['lastname'] ?? $customer_info['lastname'] ?? '';
		$data['email'] = $this->request->post['email'] ?? $customer_info['email'] ?? '';
		$data['telephone'] = $this->request->post['telephone'] ?? $customer_info['telephone'] ?? '';
	
		// ... rest of your existing $data setup
		$image_path = $this->model_account_customer->getProfileImage($this->customer->getId());
		$data['profile_photo_url'] = $image_path 
			? $this->config->get('config_url') . 'image/' . $image_path 
			: $this->config->get('config_url') . 'image/default-profile.png';
		
		$this->response->setOutput($this->load->view('account/account', $data));
	

		
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    
    // aaded code changes for the pincode serviceability on the 04/06/2025
    public function getPincodeHistory() {
        $json = array();
        if (!$this->customer->isLogged()) {
            $json['error'] = 'Not logged in';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        $this->load->model('account/customer');
        $pincodes = $this->model_account_customer->getPincodeHistory($this->customer->getId());
        $json['success'] = true;
        $json['pincodes'] = $pincodes;
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function addPincodeHistory() {
        $json = array();
        if (!$this->customer->isLogged()) {
            $json['error'] = 'Not logged in';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        if (isset($this->request->post['pincode'])) {
            $this->load->model('account/customer');
            $this->model_account_customer->addPincodeHistory($this->customer->getId(), $this->request->post['pincode']);
            $json['success'] = true;
        } else {
            $json['error'] = 'No pincode provided';
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    //----------------------------------end here-----------------------------------------
	
}
