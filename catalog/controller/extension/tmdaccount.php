<?php
class ControllerExtensionTmdAccount extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/tmdaccount', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('extension/tmdaccount');
		$this->load->model('extension/tmdaccount');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
				if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'total';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$url = '';

		$data['heading_title']= $this->language->get('heading_title');
		$data['tmdaccount_latestorder']= $this->config->get('tmdaccount_latestorder');
		$data['text_phone']= $this->language->get('text_phone');
		$data['text_email']= $this->language->get('text_email');
		$data['text_address']= $this->language->get('text_address');
		$data['text_edit_profile']= $this->language->get('text_edit_profile');


		$data['text_no_results']= $this->language->get('text_no_results');


		$accountlables=$this->config->get('tmdaccount_lable');

		if(!empty($accountlables[$this->config->get('config_language_id')]['totalodrlabel'])){
		$data['text_total_order']	= $accountlables[$this->config->get('config_language_id')]['totalodrlabel'];
		} else {
		$data['text_total_order'] = $this->language->get('text_total_order');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['totaldownlabel'])){
		$data['text_downloads']	= $accountlables[$this->config->get('config_language_id')]['totaldownlabel'];
		} else {
		$data['text_downloads']= $this->language->get('text_downloads');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['totalwishlabel'])){
		$data['text_total_wishlist']	= $accountlables[$this->config->get('config_language_id')]['totalwishlabel'];
		} else {
		$data['text_total_wishlist']= $this->language->get('text_total_wishlist');
		}


		if(!empty($accountlables[$this->config->get('config_language_id')]['pointslabel'])){
		$data['text_reward_points']	= $accountlables[$this->config->get('config_language_id')]['pointslabel'];
		} else {
		$data['text_reward_points']= $this->language->get('text_reward_points');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['pointslabel'])){
		$data['text_reward_point']	= $accountlables[$this->config->get('config_language_id')]['pointslabel'];
		} else {
		$data['text_reward_point']= $this->language->get('text_reward_point');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['totaltranslabel'])){
		$data['text_transations']	= $accountlables[$this->config->get('config_language_id')]['totaltranslabel'];
		} else {
		$data['text_transations']= $this->language->get('text_transations');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['transctionlabel'])){
		$data['text_transation']	= $accountlables[$this->config->get('config_language_id')]['transctionlabel'];
		} else {
		$data['text_transation']= $this->language->get('text_transation');
		}


		if(!empty($accountlables[$this->config->get('config_language_id')]['editaclabel'])){
		$data['text_edit_account']	= $accountlables[$this->config->get('config_language_id')]['editaclabel'];
		} else {
		$data['text_edit_account']= $this->language->get('text_edit_account');
		}
		if(!empty($accountlables[$this->config->get('config_language_id')]['passlabel'])){
		$data['text_change_password']	= $accountlables[$this->config->get('config_language_id')]['passlabel'];
		} else {
		$data['text_change_password']= $this->language->get('text_change_password');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['booklabel'])){
		$data['text_address_book']	= $accountlables[$this->config->get('config_language_id')]['booklabel'];
		} else {
		$data['text_address_book']= $this->language->get('text_address_book');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['logoutlabel'])){
		$data['text_logout']	= $accountlables[$this->config->get('config_language_id')]['logoutlabel'];
		} else {
		$data['text_logout'] = $this->language->get('text_logout');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['wishlabel'])){
		$data['text_wishlist']	= $accountlables[$this->config->get('config_language_id')]['wishlabel'];
		} else {
		$data['text_wishlist']= $this->language->get('text_wishlist');
		}
		if(!empty($accountlables[$this->config->get('config_language_id')]['orderlabel'])){
		$data['text_order']	= $accountlables[$this->config->get('config_language_id')]['orderlabel'];
		} else {
		$data['text_order']= $this->language->get('text_order');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['downlodlabel'])){
		$data['text_download']	= $accountlables[$this->config->get('config_language_id')]['downlodlabel'];
		} else {
		$data['text_download']= $this->language->get('text_download');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['returnlabel'])){
		$data['text_returnrequest']	= $accountlables[$this->config->get('config_language_id')]['returnlabel'];
		} else {
		$data['text_returnrequest']= $this->language->get('text_returnrequest');
		}
	if(!empty($accountlables[$this->config->get('config_language_id')]['affilatelabel'])){
		$data['text_affilate']	= $accountlables[$this->config->get('config_language_id')]['affilatelabel'];
		} else {
		$data['text_affilate']= $this->language->get('text_affilate');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['paylabel'])){
		$data['text_recurringpayments']	= $accountlables[$this->config->get('config_language_id')]['paylabel'];
		} else {
		$data['text_recurringpayments']= $this->language->get('text_recurringpayments');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['newslabel'])){
		$data['text_newsletter']	= $accountlables[$this->config->get('config_language_id')]['newslabel'];
		} else {
		$data['text_newsletter']= $this->language->get('text_newsletter');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['latestlabel'])){
		$data['text_latest']	= $accountlables[$this->config->get('config_language_id')]['latestlabel'];
		} else {
		$data['text_latest']= $this->language->get('text_latest');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['orderidlabel'])){
		$data['column_order_id']	= $accountlables[$this->config->get('config_language_id')]['orderidlabel'];
		} else {
		$data['column_order_id']= $this->language->get('column_order_id');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['noprolabel'])){
		$data['column_product']	= $accountlables[$this->config->get('config_language_id')]['noprolabel'];
		} else {
		$data['column_product']= $this->language->get('column_product');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['statuslabel'])){
		$data['column_status']	= $accountlables[$this->config->get('config_language_id')]['statuslabel'];
		} else {
		$data['column_status']= $this->language->get('column_status');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['viewalllabel'])){
		$data['text_viewall']	= $accountlables[$this->config->get('config_language_id')]['viewalllabel'];
		} else {
		$data['text_viewall']= $this->language->get('text_viewall');
		}
		if(!empty($accountlables[$this->config->get('config_language_id')]['totalprolabel'])){
		$data['column_total']	= $accountlables[$this->config->get('config_language_id')]['totalprolabel'];
		} else {
		$data['column_total']= $this->language->get('column_total');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['datelabel'])){
		$data['column_date_added']	= $accountlables[$this->config->get('config_language_id')]['datelabel'];
		} else {
		$data['column_date_added']= $this->language->get('column_date_added');
		}

		if(!empty($accountlables[$this->config->get('config_language_id')]['actionlabel'])){
		$data['column_action']	= $accountlables[$this->config->get('config_language_id')]['actionlabel'];
		} else {
		$data['column_action']= $this->language->get('column_action');
		}

		$data['button_view']= $this->language->get('button_view');


		$tmdaccount_info= $this->model_extension_tmdaccount->getShowAccount($this->customer->getId());

		$data['firstname']=$tmdaccount_info['firstname'];
		$data['lastname']=$tmdaccount_info['lastname'];
		$data['telephone']=$tmdaccount_info['telephone'];
		$data['email']=$tmdaccount_info['email'];
		$data['href']=$this->url->link('account/edit','customer_id=' . $tmdaccount_info['customer_id']);
		$data['logout'] = $this->url->link('account/logout', '', true);

		$address_info= $this->model_extension_tmdaccount->getShowAddress($this->customer->getId());




		if(isset($address_info['address_1'])){
		$data['address_1']=$address_info['address_1'];
		} else {
		$data['address_1'] ='';
		}

			if(isset($address_info['city'])){
		$data['city']=$address_info['city'];
		} else {
		$data['city'] ='';
		}

		$data['order_total']= $this->model_extension_tmdaccount->getTotalOrder($this->customer->getId());
		$data['wishlist_total'] = $this->model_extension_tmdaccount->getTotalWishlist($this->customer->getId());
		$data['points']= $this->model_extension_tmdaccount->getTotalRewardPoints($this->customer->getId());
		$data['totaltransaction']= $this->model_extension_tmdaccount->getTotalTransaction($this->customer->getId());
		$data['totaldownload']= $this->model_extension_tmdaccount->getTotalDownload($this->customer->getId());

		$this->load->model('tool/image');

		$this->load->model('account/customer');

		$prodileimages= $this->model_account_customer->getCustomer($this->customer->getId());

		if ($prodileimages) {
		$data['prodileimage'] = $this->model_tool_image->resize($prodileimages['image'], 120,120);
		} else {
			$data['prodileimage'] = '';
		}



		$tmdaccount_bgimage = $this->config->get('tmdaccount_bgimage');

		if (is_file(DIR_IMAGE . $tmdaccount_bgimage)) {
			$data['tmdaccount_bgimage'] = $this->model_tool_image->resize($tmdaccount_bgimage, $this->config->get('tmdaccount_bgwidth'),$this->config->get('tmdaccount_bgheight'));
		} else {
			$data['tmdaccount_bgimage'] = '';
		}


		$defaulpic = $this->config->get('tmdaccount_defaultimage');

		if (is_file(DIR_IMAGE . $defaulpic)) {
			$data['defaulpic'] = $this->model_tool_image->resize($defaulpic, $this->config->get('tmdaccount_defaultwidth'),$this->config->get('tmdaccount_defaultheight'));
		} else {
			$data['defaulpic'] = '';
		}

		$data['placeholder1'] = $this->model_tool_image->resize('placeholder.png',50,50);

		$tmdaccount_totalorders = $this->config->get('tmdaccount_totalorders');
		if (isset($tmdaccount_totalorders)) {
		$data['total_order'] = $tmdaccount_totalorders;
		} else {
		$data['total_order'] = '';
		}

		$tmdaccount_totalwishlist = $this->config->get('tmdaccount_totalwishlist');
		if (isset($tmdaccount_totalwishlist)) {
		$data['total_wishlist'] = $tmdaccount_totalwishlist;
		} else {
		$data['total_wishlist'] = '';
		}

		$tmdaccount_totalreward = $this->config->get('tmdaccount_totalreward');
		if (isset($tmdaccount_totalreward)) {
		$data['total_reward'] = $tmdaccount_totalreward;
		} else {
		$data['total_reward'] = '';
		}

		$tmdaccount_totaldownload = $this->config->get('tmdaccount_totaldownload');
		if (isset($tmdaccount_totaldownload)) {
		$data['total_download'] = $tmdaccount_totaldownload;
		} else {
		$data['total_download'] = '';
		}

		$tmdaccount_totaltransaction = $this->config->get('tmdaccount_totaltransaction');
		if (isset($tmdaccount_totaltransaction)) {
		$data['total_transaction'] = $tmdaccount_totaltransaction;
		} else {
		$data['total_transaction'] = '';
		}

		$tmdaccount_latestorder = $this->config->get('tmdaccount_latestorder');
		if (isset($tmdaccount_latestorder)) {
		$data['latest_order'] = $tmdaccount_latestorder;
		} else {
		$data['latest_order'] = '';
		}
		//
		$tmdaccount_link_editaccount = $this->config->get('tmdaccount_link_editaccount');
		if (isset($tmdaccount_link_editaccount)) {
		$data['link_editaccount'] = $tmdaccount_link_editaccount;
		} else {
		$data['link_editaccount'] = '';
		}

		$tmdaccount_link_password = $this->config->get('tmdaccount_link_password');
		if (isset($tmdaccount_link_password)) {
		$data['link_password'] = $tmdaccount_link_password;
		} else {
		$data['link_password'] = '';
		}

		$tmdaccount_link_address_book = $this->config->get('tmdaccount_link_address_book');
		if (isset($tmdaccount_link_address_book)) {
		$data['link_address_book'] = $tmdaccount_link_address_book;
		} else {
		$data['link_address_book'] = '';
		}

		$tmdaccount_link_wishlist = $this->config->get('tmdaccount_link_wishlist');
		if (isset($tmdaccount_link_wishlist)) {
		$data['link_wishlist'] = $tmdaccount_link_wishlist;
		} else {
		$data['link_wishlist'] = '';
		}

		$tmdaccount_link_order = $this->config->get('tmdaccount_link_order');
		if (isset($tmdaccount_link_order)) {
		$data['link_order'] = $tmdaccount_link_order;
		} else {
		$data['link_order'] = '';
		}

		$tmdaccount_link_downloads = $this->config->get('tmdaccount_link_downloads');
		if (isset($tmdaccount_link_downloads)) {
		$data['link_downloads'] = $tmdaccount_link_downloads;
		} else {
		$data['link_downloads'] = '';
		}

		$tmdaccount_link_reward = $this->config->get('tmdaccount_link_reward');
		if (isset($tmdaccount_link_reward)) {
		$data['link_reward'] = $tmdaccount_link_reward;
		} else {
		$data['link_reward'] = '';
		}

		$tmdaccount_link_returns = $this->config->get('tmdaccount_link_returns');
		if (isset($tmdaccount_link_returns)) {
		$data['link_returns'] = $tmdaccount_link_returns;
		} else {
		$data['link_returns'] = '';
		}

		$tmdaccount_link_transaction = $this->config->get('tmdaccount_link_transaction');
		if (isset($tmdaccount_link_transaction)) {
		$data['link_transaction'] = $tmdaccount_link_transaction;
		} else {
		$data['link_transaction'] = '';
		}

		$tmdaccount_link_newsletter = $this->config->get('tmdaccount_link_newsletter');
		if (isset($tmdaccount_link_newsletter)) {
		$data['link_newsletter'] = $tmdaccount_link_newsletter;
		} else {
		$data['link_newsletter'] = '';
		}

		$tmdaccount_link_affilate = $this->config->get('tmdaccount_link_affilate');
		if (isset($tmdaccount_link_affilate)) {
		$data['link_affilate'] = $tmdaccount_link_affilate;
		} else {
		$data['link_affilate'] = '';
		}

		$tmdaccount_link_payments = $this->config->get('tmdaccount_link_payments');
		if (isset($tmdaccount_link_payments)) {
		$data['link_payments'] = $tmdaccount_link_payments;
		} else {
		$data['link_payments'] = '';
		}

		$filter_data = array(
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);

		/* new code */
		$data['viewalorders'] = $this->url->link('account/order');
		/* new code */
		$url='';

		$data['orders']= array();
		$odr_total = $this->model_extension_tmdaccount->getTotalOrders();

		$results=$this->model_extension_tmdaccount->getOrders(($page - 1) * $limit, $limit);
		
		if(isset($results)){

			foreach($results as $result){



				$data['orders'][] = array(
				'order_id'	     => $result['order_id'],
				'date_added'	 => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'total'	         =>  $this->currency->format($result['total'], $this->session->data['currency']),
				'noof_product'	 => $result['quantity'],
				'status'	     => $result['status'],
				'href'	         => $this->url->link('account/order/info'. '&order_id=' . $result['order_id']),

				);
			}

		}
	


		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}


		$pagination = new Pagination();
		$pagination->total = $odr_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('extension/tmdaccount',$url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($odr_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($odr_total - $limit)) ? $odr_total : ((($page - 1) *$limit) + $limit), $odr_total, ceil($odr_total / $limit));

		

		$data['logged'] = $this->customer->isLogged();
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		$data['return'] = $this->url->link('account/return', '', true);
		$data['transactions'] = $this->url->link('account/transaction', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['affilate'] = $this->url->link('account/affiliate/add', '', true);
		$data['recurring'] = $this->url->link('account/recurring', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist');
		$data['order'] = $this->url->link('account/order', '', true);
		$data['downloads'] = $this->url->link('account/download', '', true);
		$data['reward'] = $this->url->link('account/reward', '', true);
		

		$data['tmdaccount_customcss'] = $this->config->get('tmdaccount_custom_css');

		$data['column_left']	= $this->load->controller('common/column_left');
		$data['column_right']	= $this->load->controller('common/column_right');
		$data['content_top']	= $this->load->controller('common/content_top');
		$data['content_bottom']= $this->load->controller('common/content_bottom');
		$data['footer']		= $this->load->controller('common/footer');
		$data['header']		= $this->load->controller('common/header');

        $accounttemplate = $this->config->get('tmdaccount_template');

        if($accounttemplate=='1'){
		  $this->response->setOutput($this->load->view('extension/tmdaccount', $data));
        } elseif($accounttemplate=='2'){
		  $this->response->setOutput($this->load->view('extension/tmdaccount1', $data));
        } elseif($accounttemplate=='3'){
		  $this->response->setOutput($this->load->view('extension/tmdaccount2', $data));
        } else {
         $this->response->setOutput($this->load->view('extension/tmdaccount', $data));
        }

	}

	public function uploadprofileimage() {
		$this->load->language('tool/upload');
		$this->load->model('tool/image');


		$json = array();

		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
		// Sanitize the filename
		$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

		// Validate the filename length
		if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
			$json['error'] = $this->language->get('error_filename');
		}

		// Allowed file extension types
		$allowed = array();

		$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

		$filetypes = explode("\n", $extension_allowed);

		foreach ($filetypes as $filetype) {
			$allowed[] = trim($filetype);
		}

		if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
			$json['error'] = $this->language->get('error_filetype');
		}

		// Allowed file mime types
		$allowed = array();

		$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

		$filetypes = explode("\n", $mime_allowed);

		foreach ($filetypes as $filetype) {
			$allowed[] = trim($filetype);
		}

		if (!in_array($this->request->files['file']['type'], $allowed)) {
			$json['error'] = $this->language->get('error_filetype');
		}

		// Check to see if any PHP files are trying to be uploaded
		$content = file_get_contents($this->request->files['file']['tmp_name']);

		if (preg_match('/\<\?php/i', $content)) {
			$json['error'] = $this->language->get('error_filetype');
		}

		// Return any upload error
		if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
			$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
		}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json) {
			$file = md5(mt_rand()).$filename ;

			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_IMAGE.'catalog/demo/' . $file);

			// Hide the uploaded file name so people can not link to it directly.
			$this->load->model('tool/upload');

			$json['success'] = $this->language->get('text_upload');
			$json['file'] ='catalog/demo/'.$file;
			$file1=$this->model_tool_image->resize('catalog/demo/'.$file, 100, 100);
			$json['file1'] = $file1;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	public function saveimage() {
		$this->load->language('extension/tmdaccount');

		$json = array();

		if (!$json) {
		
			$this->load->model('extension/tmdaccount');

			$this->model_extension_tmdaccount->editProfileImage($this->request->post);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
