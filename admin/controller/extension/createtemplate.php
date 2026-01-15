<?php 
class ControllerExtensionCreatetemplate extends Controller {
	private $error = array();
 
	public function index() {
		$this->language->load('extension/createtemplate');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/createtemplate');
		
		$this->getList();
	}

	public function insert() {
		$this->language->load('extension/createtemplate');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/createtemplate');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_createtemplate->addCreatetemplate($this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->response->redirect($this->url->link('extension/createtemplate', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function update() {
		$this->language->load('extension/createtemplate');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/createtemplate');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_createtemplate->editCreatetemplate($this->request->get['createtemplate_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
					
			$this->response->redirect($this->url->link('extension/createtemplate', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}
 
	public function delete() {
		$this->language->load('extension/createtemplate');
 
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/createtemplate');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $createtemplate_id) {
				$this->model_extension_createtemplate->deleteCreatetemplate($createtemplate_id);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/createtemplate', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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
			
		$url = '';
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
	
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/createtemplate', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['insert'] = $this->url->link('extension/createtemplate/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		$data['delete'] = $this->url->link('extension/createtemplate/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		$data['createtemplates'] = array();
		
		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$createtemplate_total = $this->model_extension_createtemplate->getTotalCreatetemplates($filter_data);
		$results = $this->model_extension_createtemplate->getCreatetemplates($filter_data);
		foreach ($results as $result) 
		{
			$data['createtemplates'][] = array(
				'createtemplate_id' => $result['createtemplate_id'],
				'name'      => $result['name'],	
				'subject'   => $result['subject'],	
				'message'   => substr(html_entity_decode($result['message'], ENT_QUOTES, 'UTF-8'),0,60),
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'selected'  => isset($this->request->post['selected']) && in_array($result['banner_id'], $this->request->post['selected']),				
				'edit'           => $this->url->link('extension/createtemplate/update', 'user_token=' . $this->session->data['user_token'] . '&createtemplate_id=' . $result['createtemplate_id'] . $url, true)
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_subject'] = $this->language->get('column_subject');
		$data['column_message'] = $this->language->get('column_message');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');	
		$data['text_confirm'] = $this->language->get('text_confirm');	
		$data['button_insert'] = $this->language->get('button_insert');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_edit'] = $this->language->get('button_edit');
 
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
	
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}
		
		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['sort_name'] = $this->url->link('extension/createtemplate', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_status'] = $this->url->link('extension/createtemplate', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $createtemplate_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/createtemplate', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($createtemplate_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($createtemplate_total - $this->config->get('config_limit_admin'))) ? $createtemplate_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $createtemplate_total, ceil($createtemplate_total / $this->config->get('config_limit_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		
		
		
		
		$this->response->setOutput($this->load->view('extension/createtemplate_list', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_form'] = !isset($this->request->get['createtemplate_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['entry_template'] = $this->language->get('entry_template');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_subject'] = $this->language->get('entry_subject');
		$data['entry_message'] = $this->language->get('entry_message');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
	
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		
		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
 		if (isset($this->error['subject'])) {
			$data['error_subject'] = $this->error['subject'];
		} else {
			$data['error_subject'] = '';
		}
 		if (isset($this->error['message'])) {
			$data['error_message'] = $this->error['message'];
		} else {
			$data['error_message'] = '';
		}
		
			
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/createtemplate', 'user_token=' . $this->session->data['user_token'] . $url, true)
      		
   		);
							
		if (!isset($this->request->get['createtemplate_id'])) { 
			$data['action'] = $this->url->link('extension/createtemplate/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/createtemplate/update', 'user_token=' . $this->session->data['user_token'] . '&createtemplate_id=' . $this->request->get['createtemplate_id'] . $url, true);
		}
		
		$data['cancel'] = $this->url->link('extension/createtemplate', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		if (isset($this->request->get['createtemplate_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$createtemplate_info = $this->model_extension_createtemplate->getCreatetemplate($this->request->get['createtemplate_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		
		if (isset($this->request->post['createtemplate'])) {
			$data['createtemplate'] = $this->request->post['createtemplate'];
		} elseif (isset($this->request->get['createtemplate_id'])) {
			$data['createtemplate'] = $this->model_extension_createtemplate->getCreatetemplatedata($this->request->get['createtemplate_id']);
		} else {
			$data['createtemplate'] = array();
		}
								
    	if (isset($this->request->post['status'])) {
      		$data['status'] = $this->request->post['status'];
    	} elseif (!empty($createtemplate_info)) {
			$data['status'] = $createtemplate_info['status'];
		} else {
      		$data['status'] = 1;
    	}
		
		$this->load->model('localisation/language');		
		$data['languages'] = $this->model_localisation_language->getLanguages();

		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/createtemplate_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/createtemplate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		foreach ($this->request->post['createtemplate'] as $language_id => $value) {
      		if ($value['subject']==""){
        		$this->error['subject'][$language_id] = $this->language->get('error_subject');
      		}
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
        		$this->error['name'][$language_id] = $this->language->get('error_name');
      		}
      		if ($value['message']=="") {
        		$this->error['message'][$language_id] = $this->language->get('error_message');

      		}
			
    	}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/createtemplate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	
	
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>