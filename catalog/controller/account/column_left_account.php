<?php
class ControllerAccountColumnLeftAccount extends Controller
{
  public function index()
  {
      
    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('common/column_left_account', '', true);

      $this->response->redirect($this->url->link('account/login', '', true));
      $this->load->model('account/customer');
    }

// $this->load->model('account/customer');
// $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

// // Set image or default
// if (!empty($customer_info['image']) && is_file(DIR_IMAGE . $customer_info['image'])) {
//     $this->load->model('tool/image');
//     $data['profile_image'] = $this->model_tool_image->resize($customer_info['image'], 100, 100); // adjust size
// } else {
//     $data['profile_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);
// }
// $this->load->model('account/customer');
// $this->load->model('tool/image'); // ✅ REQUIRED!

// $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

// if (!empty($customer_info['image']) && is_file(DIR_IMAGE . $customer_info['image'])) {
//     $data['profile_image'] = $this->model_tool_image->resize($customer_info['image'], 100, 100);
// } else {
//     $data['profile_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);
// }


$this->load->model('account/customer');
$this->load->model('tool/image');

$image_path = $this->model_account_customer->getProfileImage($this->customer->getId());

if (!empty($image_path) && is_file(DIR_IMAGE . $image_path)) {
    $data['profile_image'] = $this->model_tool_image->resize($image_path, 200, 200);
} else {
    $data['profile_image'] = $this->model_tool_image->resize('no_image.png', 200, 200);
}
$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

$data['firstname'] = $customer_info['firstname'] ?? '';
$data['lastname'] = $customer_info['lastname'] ?? '';
$data['email'] = $customer_info['email'] ?? '';


$data['current_route'] = isset($this->request->get['route']) ? $this->request->get['route'] : '';




    $this->load->language('account/column_left_account');

    $data['account'] = $this->url->link('account/account', '', true);
    $data['edit'] = $this->url->link('account/edit', '', true);
    $data['password'] = $this->url->link('account/password', '', true);
    $data['address'] = $this->url->link('account/address', '', true);

    $data['credit_cards'] = array();

    $files = glob(DIR_APPLICATION . 'controller/extension/credit_card/*.php');

    foreach ($files as $file) {
      $code = basename($file, '.php');

      if ($this->config->get('payment_' . $code . 'status') && $this->config->get('payment' . $code . '_card')) {
        $this->load->language('extension/credit_card/' . $code, 'extension');

        $data['credit_cards'][] = array(
          'name' => $this->language->get('extension')->get('heading_title'),
          'href' => $this->url->link('extension/credit_card/' . $code, '', true)
        );
      }
    }
	$data['logged'] = $this->customer->isLogged();
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);
    $data['wishlist'] = $this->url->link('account/wishlist');
    $data['order'] = $this->url->link('account/order', '', true);
    // 		added on 07-07-2025 for customer ticket raise
		$data['raise_ticket'] = $this->url->link('account/ticket_list', '' ,true);
// 		end
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

    // $data['column_left'] = $this->load->controller('common/column_left');
    // $data['column_right'] = $this->load->controller('common/column_right');
    // $data['content_top'] = $this->load->controller('common/content_top');
    // $data['content_bottom'] = $this->load->controller('common/content_bottom');
    // $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
// var_dump($data['edit']);
// Check if referral offer is enabled
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer WHERE offer_type = 'referral' AND status = 1 LIMIT 1");
        $data['show_referral_program'] = $query->num_rows>0;
if( $data['show_referral_program']){
    
      $data['Referral'] = $this->url->link('account/referral', '', true);
}else{
    
      $data['Referral'] = null;
}
      $data['ReferPoints'] = $this->url->link('account/referpoints', '', true);
return $this->load->view('account/column_left_account', $data);

    // $this->response->setOutput($this->load->view('account/column_left_account', $data));
  }
}