<?php
class ControllerExtensionModuleModuleCiwhatsapp extends Controller {
	private $error = array();

	private $module_token = '';
	private $ci_token = '';

	public function __construct($registry) {
		parent :: __construct($registry);

		if(VERSION <= '2.3.0.2') {
			$this->module_token = 'token';
			$this->ci_token = $this->session->data['token'];
		} else {
			$this->module_token = 'user_token';
			$this->ci_token = $this->session->data['user_token'];
		}

		$this->load->model('extension/ciwhatsapp/setting');
	}

	public function index() {
		$this->document->addStyle('view/stylesheet/ciwhatsapp/style.css');

		$this->load->language('extension/module/module_ciwhatsapp');

		$this->document->setTitle($this->language->get('heading_title_page'));

		if(VERSION <= '2.3.0.2') {
			$this->load->model('extension/module');
			$setting_model = 'model_extension_module';
			$module_list = 'extension/extension';
		} else {
			$this->load->model('setting/module');
			$setting_model = 'model_setting_module';
			$module_list = 'marketplace/extension';
		}

		$this->load->model('tool/image');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->{$setting_model}->addModule('module_ciwhatsapp', $this->request->post);
			} else {
				$this->{$setting_model}->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->get['module_id'])) {
				$this->response->redirect($this->url->link('extension/module/module_ciwhatsapp', $this->module_token .'=' . $this->ci_token . '&module_id='. $this->request->get['module_id'], true));
			} else {
				$this->response->redirect($this->url->link($module_list, $this->module_token .'=' . $this->ci_token . '&type=module', true));
			}
		}

		$data['heading_title'] = $this->language->get('heading_title_page');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_single_line_layout'] = $this->language->get('text_single_line_layout');
		$data['text_multi_line_layout'] = $this->language->get('text_multi_line_layout');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_member'] = $this->language->get('entry_member');
		$data['entry_module_layout'] = $this->language->get('entry_module_layout');

		$data['button_setting'] = $this->language->get('button_setting');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['member'])) {
			$data['error_member'] = $this->error['member'];
		} else {
			$data['error_member'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->module_token .'=' . $this->ci_token, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link($module_list, $this->module_token .'=' . $this->ci_token . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title_page'),
				'href' => $this->url->link('extension/module/module_ciwhatsapp', $this->module_token .'=' . $this->ci_token, true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title_page'),
				'href' => $this->url->link('extension/module/module_ciwhatsapp', $this->module_token .'=' . $this->ci_token . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		$data['buttons'] = $this->model_extension_ciwhatsapp_setting->getButtons('ciwhatsapp');

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/module_ciwhatsapp', $this->module_token .'=' . $this->ci_token, true);
		} else {
			$data['action'] = $this->url->link('extension/module/module_ciwhatsapp', $this->module_token .'=' . $this->ci_token . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['setting'] = $this->url->link('extension/ciwhatsapp', $this->module_token .'=' . $this->ci_token . '&type=module', true);
		$data['cancel'] = $this->url->link($module_list, $this->module_token .'=' . $this->ci_token . '&type=module', true); $data['buttons'] ? $this->response->redirect($this->url->link('extension/ciwhatsapp_about', $this->module_token .'=' . $this->ci_token, true)) : '';

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->{$setting_model}->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		if (isset($this->request->post['member'])) {
			$data['members'] = (array)$this->request->post['member'];
		} elseif (!empty($module_info['member'])) {
			$data['members'] = $module_info['member'];
		} else {
			$data['members'] = [];
		}

		if (isset($this->request->post['design_layout'])) {
			$data['design_layout'] = $this->request->post['design_layout'];
		} elseif (!empty($module_info['design_layout'])) {
			$data['design_layout'] = $module_info['design_layout'];
		} else {
			$data['design_layout'] = 'multi_line_layout';
		}

		if(is_array($this->config->get('ciwhatsapp_setting_member'))) {
			$all_layout_members = $this->config->get('ciwhatsapp_setting_member');
		} else {
			$all_layout_members  = [];
		}

		$data['all_layout_members'] = [];
		foreach($all_layout_members as $all_layout_member) {
			if(in_array('layout_pages', $all_layout_member['page_status'])) {
				if (!empty($all_layout_member['photo']) && is_file(DIR_IMAGE . $all_layout_member['photo'])) {
					$photo_thumb = $this->model_tool_image->resize($all_layout_member['photo'], 70, 70);
				} else {
					$photo_thumb = $this->model_tool_image->resize('no_image.png', 70, 70);
				}

				$data['all_layout_members'][] = [
					'member_id'				=> $all_layout_member['member_id'],
					'member_name'			=> $all_layout_member['member_name'],
					'photo_thumb'			=> $photo_thumb,
					'sort_order'			=> $all_layout_member['sort_order'],
				];
			}
		}

		usort($data['all_layout_members'], array($this, 'MemberSortByOrder'));
		$data['ci_token'] = $this->ci_token;
		$data['module_token'] = $this->module_token;
		$data['info_member'] = $this->language->get('info_member');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if(VERSION <= '2.3.0.2') {
			$this->response->setOutput($this->load->view('extension/module/module_ciwhatsapp.tpl', $data));
		} else {
			$file_variable = 'template_engine';
			$file_type = 'template';
			$this->config->set($file_variable, $file_type);
			$this->response->setOutput($this->load->view('extension/module/module_ciwhatsapp', $data));
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/module_ciwhatsapp')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (empty($this->request->post['member'])) {
			$this->error['member'] = $this->language->get('error_member');
			$this->error['warning'] = $this->language->get('error_member');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function MemberSortByOrder($a, $b) {
	    return $a['sort_order'] - $b['sort_order'];
	}
}