<?php
//lib
require_once(DIR_SYSTEM.'library/tmd/system.php');
//lib
class ControllerExtensionModuleTmdVendor extends Controller {
	private $error = array();
public function install()
	{
	$this->load->model('extension/tmd_vendor');
	$this->model_extension_tmd_vendor->install();
	}	
	public function uninstall()
	{
	$this->load->model('extension/tmd_vendor');
	$this->model_extension_tmd_vendor->uninstall();
	}
	public function index() {
		
		$this->registry->set('tmd', new TMD($this->registry));
		$keydata=array(
		'code'=>'tmdkey_tmd_vendor',
		'eid'=>'MzM5MjA=',
		'route'=>'extension/module/tmd_vendor',
		);
		$tmd_vendor=$this->tmd->getkey($keydata['code']);
		$data['getkeyform']=$this->tmd->loadkeyform($keydata);
		
		$this->load->language('extension/module/tmd_vendor');

		$this->document->setTitle($this->language->get('heading_title1'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			if(isset($this->request->post['tmd_vendor_status']))
			{
				$status=$this->request->post['tmd_vendor_status'];
			}
			
			$postdata['module_tmd_vendor_status']=$status;
			
			$this->model_setting_setting->editSetting('module_tmd_vendor',$postdata);
			
			$this->model_setting_setting->editSetting('tmd_vendor', $this->request->post);			

			$this->session->data['success'] = $this->language->get('text_success');
			if(isset($this->request->get['status']))
			{
				$this->response->redirect($this->url->link('extension/module/tmd_vendor', 'user_token=' . $this->session->data['user_token'], true));
			}
			else
			{	$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', true));
			}
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_textcolor'] = $this->language->get('entry_textcolor');
		$data['entry_bgcolor'] = $this->language->get('entry_bgcolor');
		$data['entry_imgsize'] = $this->language->get('entry_imgsize');
		$data['entry_imgwidth'] = $this->language->get('entry_imgwidth');
		$data['entry_imgheight'] = $this->language->get('entry_imgheight');
		$data['entry_imgbotrder'] = $this->language->get('entry_imgbotrder');
		$data['entry_round'] = $this->language->get('entry_round');
		$data['entry_squre'] = $this->language->get('entry_squre');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->session->data['warning'])) {
			$data['error_warning'] = $this->session->data['warning'];
		
			unset($this->session->data['warning']);
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
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/tmd_vendor', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/tmd_vendor', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['tmd_vendor_status'])) {
			$data['tmd_vendor_status'] = $this->request->post['tmd_vendor_status'];
		} else {
			$data['tmd_vendor_status'] = $this->config->get('tmd_vendor_status');
		}

		if (isset($this->request->post['tmd_vendor_textcolor'])) {
			$data['tmd_vendor_textcolor'] = $this->request->post['tmd_vendor_textcolor'];
		} else {
			$data['tmd_vendor_textcolor'] = $this->config->get('tmd_vendor_textcolor');
		}

		if (isset($this->request->post['tmd_vendor_bgcolor'])) {
			$data['tmd_vendor_bgcolor'] = $this->request->post['tmd_vendor_bgcolor'];
		} else {
			$data['tmd_vendor_bgcolor'] = $this->config->get('tmd_vendor_bgcolor');
		}

		if (isset($this->request->post['tmd_vendor_imgwidth'])) {
			$data['tmd_vendor_imgwidth'] = $this->request->post['tmd_vendor_imgwidth'];
		} else {
			$data['tmd_vendor_imgwidth'] = $this->config->get('tmd_vendor_imgwidth');
		}

		if (isset($this->request->post['tmd_vendor_imgheight'])) {
			$data['tmd_vendor_imgheight'] = $this->request->post['tmd_vendor_imgheight'];
		} else {
			$data['tmd_vendor_imgheight'] = $this->config->get('tmd_vendor_imgheight');
		}

		if (isset($this->request->post['tmd_vendor_imagetype'])) {
			$data['tmd_vendor_imagetype'] = $this->request->post['tmd_vendor_imagetype'];
		} else {
			$data['tmd_vendor_imagetype'] = $this->config->get('tmd_vendor_imagetype');
		}


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/tmd_vendor', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/tmd_vendor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		$tmd_vendor=$this->config->get('tmdkey_tmd_vendor');
		if (empty(trim($tmd_vendor))) {			
		$this->session->data['warning'] ='Module will Work after add License key!';
		$this->response->redirect($this->url->link('extension/module/tmd_vendor', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}


		return !$this->error;
	}
	public function keysubmit() {
		$json = array(); 
		
      	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$keydata=array(
			'code'=>'tmdkey_tmd_vendor',
			'eid'=>'MzM5MjA=',
			'route'=>'extension/module/tmd_vendor',
			'moduledata_key'=>$this->request->post['moduledata_key'],
			);
			$this->registry->set('tmd', new TMD($this->registry));
            $json=$this->tmd->matchkey($keydata);       
		} 
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}