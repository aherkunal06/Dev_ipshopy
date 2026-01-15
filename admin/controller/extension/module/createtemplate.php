<?php
//lib
require_once(DIR_SYSTEM.'library/tmd/system.php');
//lib
class ControllerExtensionModuleCreatetemplate extends Controller {
	private $error = array();
public function install()
	{
	$this->load->model('extension/createtemplate');
	$this->model_extension_createtemplate->install();
	}	
	public function uninstall()
	{
	$this->load->model('extension/createtemplate');
	$this->model_extension_createtemplate->uninstall();
	}
	public function index() {
		$this->registry->set('tmd', new TMD($this->registry));
		$keydata=array(
		'code'=>'tmdkey_tmdmailmanagement',
		'eid'=>'MTk3NzY=',
		'route'=>'extension/module/createtemplate',
		);
		$tmdmailmanagement=$this->tmd->getkey($keydata['code']);
		$data['getkeyform']=$this->tmd->loadkeyform($keydata);
		$this->load->language('extension/module/createtemplate');

		$this->document->setTitle($this->language->get('heading_title1'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_createtemplate', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		/**@TMD email template starts*/                       
		$data['tab_email'] = $this->language->get('tab_email');
		$data['entry_affiliate_forgot'] = $this->language->get('entry_affiliate_forgot');
		$data['entry_affiliates'] = $this->language->get('entry_affiliates');
		$data['entry_account_register'] = $this->language->get('entry_account_register');
		$data['entry_forgot_password'] = $this->language->get('entry_forgot_password');
		$data['entry_product_order'] = $this->language->get('entry_product_order');
		$data['entry_product_order_admin'] = $this->language->get('entry_product_order_admin');
		$data['entry_product_return'] = $this->language->get('entry_product_return');
		$data['entry_product_return_admin'] = $this->language->get('entry_product_return_admin');
		$data['entry_product_order'] = $this->language->get('entry_product_order');
		$data['entry_add_product_return'] = $this->language->get('entry_add_product_return');
		$data['entry_add_product_return1'] = $this->language->get('entry_add_product_return1');
		$data['entry_orderstatus'] = $this->language->get('entry_orderstatus');
		/**@TMD email template ends*/

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title1'),
			'href' => $this->url->link('extension/module/createtemplate', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/createtemplate', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_createtemplate_status'])) {
			$data['module_createtemplate_status'] = $this->request->post['module_createtemplate_status'];
		} else {
			$data['module_createtemplate_status'] = $this->config->get('module_createtemplate_status');
		}

		if (isset($this->request->post['module_createtemplate_logo_width'])) {
			$data['module_createtemplate_logo_width'] = $this->request->post['module_createtemplate_logo_width'];
		} else {
			$data['module_createtemplate_logo_width'] = $this->config->get('module_createtemplate_logo_width');
		}

		if (isset($this->request->post['module_createtemplate_logo_height'])) {
			$data['module_createtemplate_logo_height'] = $this->request->post['module_createtemplate_logo_height'];
		} else {
			$data['module_createtemplate_logo_height'] = $this->config->get('module_createtemplate_logo_height');
		}

		/**@TMD email template starts*/                       
		$this->load->model('extension/createtemplate');
		$data['createtemplates'] = $this->model_extension_createtemplate->getCreatetemplates();
		if (isset($this->request->post['module_createtemplate_accountregister_template_id'])) {
			$data['module_createtemplate_accountregister_template_id'] = $this->request->post['module_createtemplate_accountregister_template_id'];
		} else {
			$data['module_createtemplate_accountregister_template_id'] = $this->config->get('module_createtemplate_accountregister_template_id');
		}
		
		if (isset($this->request->post['module_createtemplate_forgotpassword_template_id'])) {
			$data['module_createtemplate_forgotpassword_template_id'] = $this->request->post['module_createtemplate_forgotpassword_template_id'];
		} else {
			$data['module_createtemplate_forgotpassword_template_id'] = $this->config->get('module_createtemplate_forgotpassword_template_id');
		}
		
		if (isset($this->request->post['module_createtemplate_affiliate_template_id'])) {
			$data['module_createtemplate_affiliate_template_id'] = $this->request->post['module_createtemplate_affiliate_template_id'];
		} else {
			$data['module_createtemplate_affiliate_template_id'] = $this->config->get('module_createtemplate_affiliate_template_id');
		}

	   
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['module_createtemplate_orderstatus'])) {
			$data['module_createtemplate_orderstatus'] = $this->request->post['module_createtemplate_orderstatus'];
		} else {
			$data['module_createtemplate_orderstatus'] = $this->config->get('module_createtemplate_orderstatus');
		}
		
		/**@TMD email template ends*/

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/createtemplate', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/createtemplate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$tmdmailmanagement=$this->config->get('tmdkey_tmdmailmanagement');
		if (empty(trim($tmdmailmanagement))) {			
		$this->session->data['warning'] ='Module will Work after add License key!';
		$this->response->redirect($this->url->link('extension/module/createtemplate', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		} 

		return !$this->error;
	}
	public function keysubmit() {
		$json = array(); 
		
      	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$keydata=array(
			'code'=>'tmdkey_tmdmailmanagement',
			'eid'=>'MTk3NzY=',
			'route'=>'extension/module/createtemplate',
			'moduledata_key'=>$this->request->post['moduledata_key'],
			);
			$this->registry->set('tmd', new TMD($this->registry));
            $json=$this->tmd->matchkey($keydata);       
		} 
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
