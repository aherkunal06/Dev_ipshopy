<?php
class ControllerExtensionModuleCiwhatsappSetting extends Controller {
	private $error = array();

	private $file_routes = [
		'extension/ciwhatsapp_about',
	];

	private $code = 'ci_whatsapp';
	private $description = 'Whatsapp Chat Manager - Codinginspect';
	private $status = 1;
	private $sort_order = 0;
	private $events = [
		'admin'	=> [
			[
				'trigger'	=> 'admin/view/common/column_left/before',
				'action'	=> 'extension/module/ciwhatsapp_setting/createMainMenu',
			],
			[
				'trigger'	=> 'admin/view/common/header/after',
				'action'	=> 'extension/module/ciwhatsapp_setting/addHeaderScript',
			],
		],
		'catalog'	=> [
			[
				'trigger'	=> 'catalog/view/common/footer/before',
				'action'	=> 'extension/module/ciwhatsapp_setting/createFooterWidget',
			],
			[
				'trigger'	=> 'catalog/view/common/footer/after',
				'action'	=> 'extension/module/ciwhatsapp_setting/addFooterWidget',
			],
			[
				'trigger'	=> 'catalog/view/product/product/before',
				'action'	=> 'extension/module/ciwhatsapp_setting/createProductWidget',
			],
			[
				'trigger'	=> 'catalog/view/product/product/after',
				'action'	=> 'extension/module/ciwhatsapp_setting/addProductWidget',
			],[
				'trigger'	=> 'catalog/model/design/layout/getLayoutModules/after',
				'action'	=> 'extension/module/ciwhatsapp_setting/setPosition',
			],
		],
	];


	public function __construct($registry) {
		parent :: __construct($registry);

		if(VERSION <= '2.3.0.2') {
			$this->module_token = 'token';
			$this->ci_token = isset($this->session->data['token']) ? $this->session->data['token'] : '';
		} else {
			$this->module_token = 'user_token';
			$this->ci_token = isset($this->session->data['user_token']) ? $this->session->data['user_token'] : '';
		}

		$this->load->model('extension/ciwhatsapp/setting');

		/* Compatibility for oc 2.3x starts */
		if(VERSION <= '2.3.0.2') {
			foreach($this->events['catalog'] as $key => $value) {
				if(strpos($value['trigger'], 'common/menu') !== false) {
					$this->events['catalog'][$key]['trigger'] = str_replace('common/menu', 'common/header', $this->events['catalog'][$key]['trigger']);
				}

				$explode = explode('/', $value['trigger']);
				if(strpos($value['trigger'], 'catalog/view') !== false && end($explode) == 'after') {
					$this->events['catalog'][$key]['trigger'] = 'catalog/view/*/template/'. substr($value['trigger'], strlen('catalog/view/'));
				}
			}
		}
		/* Compatibility for oc 2.3x ends */
	}

	public function install() {
		$filter_data = [
			'events'		=> $this->events,
			'code'			=> $this->code,
			'description'	=> $this->description,
			'status'		=> $this->status,
			'sort_order'	=> $this->sort_order,
		];

		// Remove Events
		$this->model_extension_ciwhatsapp_setting->removeEvents($filter_data);

		// Create Events
		$this->model_extension_ciwhatsapp_setting->createEvents($filter_data);

		// Create Permission
		$this->model_extension_ciwhatsapp_setting->cratePermissions($this->file_routes);

		// Create Tables
		$this->model_extension_ciwhatsapp_setting->createTables();

		// Add Sample Data
		$this->model_extension_ciwhatsapp_setting->addSampleData();

		// Force Change Sort Order for advertise/google
		if(VERSION >= '3.0.0.0') {
			$this->db->query("UPDATE " . DB_PREFIX . "event SET sort_order = 2 WHERE `code` = 'advertise_google' AND `trigger` = 'catalog/view/product/product/after' AND `action` = 'extension/advertise/google/google_dynamic_remarketing_product' AND `sort_order` = 0");
		}
	}

	public function uninstall() {
		$filter_data = [
			'events'		=> $this->events,
			'code'			=> $this->code,
			'description'	=> $this->description,
			'status'		=> $this->status,
			'sort_order'	=> $this->sort_order,
		];

		$this->model_extension_ciwhatsapp_setting->removeEvents($filter_data);
	}

	public function enableEvents() {
		$this->load->language('extension/module/ciwhatsapp_setting');

		$json = [];

		if(!$this->config->get('ciwhatsapp_setting_status')) {
			$json['warning'] = $this->language->get('error_permission');
		}

		if (!$this->user->hasPermission('modify', 'extension/module/ciwhatsapp_setting')) {
			$json['warning'] = $this->language->get('error_permission');
		}

		if(!$json) {
			foreach ($this->events as $folder => $folder_info) {
				$this->model_extension_ciwhatsapp_setting->enableEvents($this->code .'_'. $folder);
			}

			$this->session->data['success'] = $this->language->get('text_enable_event_success');

			$json['success'] = str_replace('&amp;', '&', $this->url->link('extension/module/ciwhatsapp_setting', $this->module_token .'=' . $this->ci_token, true));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function showevents() {
		echo "<pre>";
		if(isset($this->request->get['test']) && $this->request->get['test'] == 'db') {
			// Database Events
			foreach ($this->events as $folder => $folder_info) {
				if($folder_info) {
					$db_events = [];
					$db_events[$folder] = $this->model_extension_ciwhatsapp_setting->getEventsByCode(['code'	=> $this->code .'_'. $folder]);
					echo "--- (". count($db_events[$folder]).") Database Event for ". $folder;
					echo "\n";
					print_r($db_events);
					echo "\n";
				}
			}
		} else {
			// Private Events
			foreach ($this->events as $folder => $folder_info) {
				if($folder_info) {
					$pr = [];
					foreach ($folder_info as $event) {
						$pr[$folder][] = [
							'event_id'	=> 0,
							'code'		=> $this->code .'_'. $folder,
							'trigger'	=> $event['trigger'],
							'action'	=> $event['action'],
							'status'	=> 1,
							'sort_order'=> 0,
						];
					}

					echo "--- (". count($pr[$folder]).") Privatee Event for ". $folder;
					echo "\n";
					print_r($pr);

					echo "\n";
				}
			}
		}

		echo "</pre>";
	}

	public function index() {
		$this->load->language('extension/module/ciwhatsapp_setting');

		$this->document->setTitle($this->language->get('heading_title_page'));

		/* checking disabled events starts */
		$data['button_enable_event'] = $this->language->get('button_enable_event');
		$data['info_disabled_events'] = $this->language->get('info_disabled_events');

		$disabled_events = 0;
		if($this->config->get('ciwhatsapp_setting_status') && count($this->events)) {
			foreach ($this->events as $folder => $folder_info) {
				$filter_data = [
					'code'			=> $this->code .'_'. $folder,
					'filter_status' => 0,
				];

				$disabled_events += $this->model_extension_ciwhatsapp_setting->getTotalEvents($filter_data);
			}
		}

		if($disabled_events) {
			$data['action_enable_events'] = str_replace('&amp;', '&', $this->url->link('extension/module/ciwhatsapp_setting/enableEvents', $this->module_token .'=' . $this->ci_token, true));
		} else {
			$data['action_enable_events'] = '';
		}
		/* checking disabled events ends */

		/* sync new events starts */
		$add_data = [
			'events'		=> $this->events,
			'code'			=> $this->code,
			'description'	=> $this->description,
			'status'		=> $this->status,
			'sort_order'	=> $this->sort_order,
		];

		$this->model_extension_ciwhatsapp_setting->syncEvents($add_data);
		/* sync new events ends */

		$this->document->addStyle('view/javascript/ciwhatsapp/colorpicker/css/colorpicker.css');
 		$this->document->addScript('view/javascript/ciwhatsapp/colorpicker/js/colorpicker.js');

		$this->document->addStyle('view/stylesheet/ciwhatsapp/style.css');


		$this->load->model('setting/setting');

		$this->load->model('setting/store');

		$this->load->model('tool/image');

		$this->load->model('localisation/language');

		if(VERSION <= '2.3.0.2') {
			$module_list = 'extension/extension';
		} else {
			$module_list = 'marketplace/extension';
		}

		if(isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
		} else{
			$store_id = 0;
		}

		$data['buttons'] = $this->model_extension_ciwhatsapp_setting->getButtons('ciwhatsapp');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if(VERSION >= '3.0.0.0') {
				$module_data = [
					'module_ciwhatsapp_setting_status'			=> isset($this->request->post['ciwhatsapp_setting_status']) ? $this->request->post['ciwhatsapp_setting_status'] : 0,
				];

				$this->model_setting_setting->editSetting('module_ciwhatsapp_setting', $module_data, $store_id);
			}

			$this->model_setting_setting->editSetting('ciwhatsapp_setting', $this->request->post, $store_id);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/ciwhatsapp_setting', $this->module_token .'=' . $this->ci_token . '&store_id='. $store_id, true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['member_name'])) {
			$data['error_member_name'] = $this->error['member_name'];
		} else {
			$data['error_member_name'] = '';
		}

		if (isset($this->error['member_number'])) {
			$data['error_member_number'] = $this->error['member_number'];
		} else {
			$data['error_member_number'] = '';
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

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_page'),
			'href' => $this->url->link('extension/module/ciwhatsapp_setting', $this->module_token .'=' . $this->ci_token, true)
		);

		$data['store_id'] = $store_id;

		if(isset($store_id)) {
			$data['action'] = $this->url->link('extension/module/ciwhatsapp_setting', $this->module_token .'=' . $this->ci_token . '&store_id='. $store_id, true);
		} else {
			$data['action'] = $this->url->link('extension/module/ciwhatsapp_setting', $this->module_token .'=' . $this->ci_token . '', true);
		}

		$data['cancel'] = $this->url->link('common/dashboard', $this->module_token .'=' . $this->ci_token . '&type=module', true);
		$data['extensions'] = $this->url->link($module_list, $this->module_token .'=' . $this->ci_token . '&type=module', true);

		if($data['buttons']) {
			$buttons_links = $this->response->redirect($this->url->link(end($this->file_routes), $this->module_token .'=' . $this->ci_token, true));
		} else {
			$buttons_links = '';
		}

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$module_info = $this->model_setting_setting->getSetting('ciwhatsapp_setting',  $store_id);
		}

		// Stores
		$data['stores'] = array();
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default'),
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name'],
			);
		}

		if (isset($this->request->post['ciwhatsapp_setting_status'])) {
			$data['ciwhatsapp_setting_status'] = $this->request->post['ciwhatsapp_setting_status'];
		} elseif (!empty($module_info['ciwhatsapp_setting_status'])) {
			$data['ciwhatsapp_setting_status'] = $module_info['ciwhatsapp_setting_status'];
		} else {
			$data['ciwhatsapp_setting_status'] = '';
		}

		if (isset($this->request->post['ciwhatsapp_setting_timezone'])) {
			$data['ciwhatsapp_setting_timezone'] = $this->request->post['ciwhatsapp_setting_timezone'];
		} elseif (!empty($module_info['ciwhatsapp_setting_timezone'])) {
			$data['ciwhatsapp_setting_timezone'] = $module_info['ciwhatsapp_setting_timezone'];
		} else {
			$data['ciwhatsapp_setting_timezone'] = 'UTC';
		}

		// Set Time Zone
		$data['timezones'] = array();

		$timestamp = time();

		$timezones = timezone_identifiers_list();

		foreach($timezones as $timezone) {
			date_default_timezone_set($timezone);
			$hour = ' (' . date('P', $timestamp) . ')';
			$data['timezones'][] = array(
				'text'  => $timezone . $hour,
				'value' => $timezone
			);
		}

		if (isset($this->request->post['ciwhatsapp_setting_device'])) {
			$data['ciwhatsapp_setting_device'] = $this->request->post['ciwhatsapp_setting_device'];
		} elseif (!empty($module_info)) {
			$data['ciwhatsapp_setting_device'] = (isset($module_info['ciwhatsapp_setting_device']) ? (array)$module_info['ciwhatsapp_setting_device'] : []);
		} else {
			$data['ciwhatsapp_setting_device'] = ['desktop', 'mobile'];
		}

		if (isset($this->request->post['ciwhatsapp_setting_position'])) {
			$data['ciwhatsapp_setting_position'] = $this->request->post['ciwhatsapp_setting_position'];
		} elseif (!empty($module_info['ciwhatsapp_setting_position'])) {
			$data['ciwhatsapp_setting_position'] = $module_info['ciwhatsapp_setting_position'];
		} else {
			$data['ciwhatsapp_setting_position'] = 'right_side';
		}

		if (isset($this->request->post['ciwhatsapp_setting_layout'])) {
			$data['ciwhatsapp_setting_layout'] = $this->request->post['ciwhatsapp_setting_layout'];
		} elseif (!empty($module_info['ciwhatsapp_setting_layout'])) {
			$data['ciwhatsapp_setting_layout'] = $module_info['ciwhatsapp_setting_layout'];
		} else {
			$data['ciwhatsapp_setting_layout'] = 'list_work';
		}

		if (isset($this->request->post['ciwhatsapp_setting_shape'])) {
			$data['ciwhatsapp_setting_shape'] = $this->request->post['ciwhatsapp_setting_shape'];
		} elseif (!empty($module_info['ciwhatsapp_setting_shape'])) {
			$data['ciwhatsapp_setting_shape'] = $module_info['ciwhatsapp_setting_shape'];
		} else {
			$data['ciwhatsapp_setting_shape'] = 'round';
		}


		if (isset($this->request->post['ciwhatsapp_setting_detailpage_device'])) {
			$data['ciwhatsapp_setting_detailpage_device'] = $this->request->post['ciwhatsapp_setting_detailpage_device'];
		} elseif (!empty($module_info)) {
			$data['ciwhatsapp_setting_detailpage_device'] = (isset($module_info['ciwhatsapp_setting_detailpage_device']) ? (array)$module_info['ciwhatsapp_setting_detailpage_device'] : []);
		} else {
			$data['ciwhatsapp_setting_detailpage_device'] = ['desktop', 'mobile'];
		}

		if (isset($this->request->post['ciwhatsapp_setting_detailpage_layout'])) {
			$data['ciwhatsapp_setting_detailpage_layout'] = $this->request->post['ciwhatsapp_setting_detailpage_layout'];
		} elseif (!empty($module_info['ciwhatsapp_setting_detailpage_layout'])) {
			$data['ciwhatsapp_setting_detailpage_layout'] = $module_info['ciwhatsapp_setting_detailpage_layout'];
		} else {
			$data['ciwhatsapp_setting_detailpage_layout'] = 'single_line_layout';
		}

		if (isset($this->request->post['ciwhatsapp_setting_color'])) {
			$data['ciwhatsapp_setting_color'] = $this->request->post['ciwhatsapp_setting_color'];
		} elseif (!empty($module_info['ciwhatsapp_setting_color'])) {
			$data['ciwhatsapp_setting_color'] = (array)$module_info['ciwhatsapp_setting_color'];
		} else {
			$data['ciwhatsapp_setting_color'] = [
				'theme_background'			=> '#03b948',
				'theme_font'				=>  '#ffffff',
			];
		}

		if (isset($this->request->post['ciwhatsapp_setting_css'])) {
			$data['ciwhatsapp_setting_css'] = $this->request->post['ciwhatsapp_setting_css'];
		} elseif (!empty($module_info['ciwhatsapp_setting_css'])) {
			$data['ciwhatsapp_setting_css'] = $module_info['ciwhatsapp_setting_css'];
		} else {
			$data['ciwhatsapp_setting_css'] = '';
		}

		if (isset($this->request->post['ciwhatsapp_setting_description'])) {
			$data['ciwhatsapp_setting_description'] = $this->request->post['ciwhatsapp_setting_description'];
		} elseif (!empty($module_info['ciwhatsapp_setting_description'])) {
			$data['ciwhatsapp_setting_description'] = (array)$module_info['ciwhatsapp_setting_description'];
		} else {
			$data['ciwhatsapp_setting_description'] = [];
		}

		if (isset($this->request->post['ciwhatsapp_setting_member'])) {
			$members = $this->request->post['ciwhatsapp_setting_member'];
		} elseif (!empty($module_info['ciwhatsapp_setting_member'])) {
			$members = (array)$module_info['ciwhatsapp_setting_member'];
		} else {
			$members = [];
		}

		$data['members'] = [];
		foreach($members as $key => $member) {

			if (!empty($member['photo']) && is_file(DIR_IMAGE . $member['photo'])) {
				$photo_thumb = $this->model_tool_image->resize($member['photo'], 100, 100);
			} else {
				$photo_thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
			}

			$data['members'][] = [
				'member_id'					=> (!empty($member['member_id']) ? (int)$member['member_id'] : (int)($key + 1)),

				'member_name'				=> $member['member_name'],

				'member_number'				=> $member['member_number'],

				'sort_order'				=> $member['sort_order'],

				'photo'						=> $member['photo'],

				'photo_thumb'				=> $photo_thumb,

				'description'				=> $member['description'],

				'status'					=> isset($member['status']) ? $member['status'] : '',

				'weekday'					=> (isset($member['weekday']) ? $member['weekday'] : []),

				'page_status'				=> isset($member['page_status']) ? $member['page_status'] : [],

				'time_text_status'			=> isset($member['time_text_status']) ? $member['time_text_status'] : '',
			];
		}

		usort($data['members'], array($this, 'MemberSortByOrder'));

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['heading_title'] = $this->language->get('heading_title_page');

		$data['tab_control'] = $this->language->get('tab_control');
		$data['tab_language'] = $this->language->get('tab_language');
		$data['tab_supporting_member'] = $this->language->get('tab_supporting_member');
		$data['tab_member'] = $this->language->get('tab_member');
		$data['tab_design'] = $this->language->get('tab_design');
		$data['tab_support'] = $this->language->get('tab_support');

		$data['legend_module_setting'] = $this->language->get('legend_module_setting');
		$data['legend_widget_bottom'] = $this->language->get('legend_widget_bottom');
		$data['legend_widget_detailpage'] = $this->language->get('legend_widget_detailpage');
		$data['legend_language'] = $this->language->get('legend_language');
		$data['legend_member'] = $this->language->get('legend_member');
		$data['legend_availability'] = $this->language->get('legend_availability');
		$data['legend_widget_member'] = $this->language->get('legend_widget_member');
		$data['legend_color'] = $this->language->get('legend_color');
		$data['legend_css'] = $this->language->get('legend_css');
		$data['legend_timezone'] = $this->language->get('legend_timezone');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_position'] = $this->language->get('entry_position');
		$data['entry_layout'] = $this->language->get('entry_layout');
		$data['entry_product_layout'] = $this->language->get('entry_product_layout');
		$data['entry_other_layout'] = $this->language->get('entry_other_layout');
		$data['entry_shape'] = $this->language->get('entry_shape');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_button_text'] = $this->language->get('entry_button_text');
		$data['entry_member_name'] = $this->language->get('entry_member_name');
		$data['entry_member_number'] = $this->language->get('entry_member_number');
		$data['entry_department_name'] = $this->language->get('entry_department_name');
		$data['entry_greeting_message'] = $this->language->get('entry_greeting_message');
		$data['entry_member_status'] = $this->language->get('entry_member_status');
		$data['entry_photo'] = $this->language->get('entry_photo');
		$data['entry_theme_background'] = $this->language->get('entry_theme_background');
		$data['entry_theme_font'] = $this->language->get('entry_theme_font');
		$data['entry_css'] = $this->language->get('entry_css');
		$data['entry_time_text_status'] = $this->language->get('entry_time_text_status');
		$data['entry_time_text'] = $this->language->get('entry_time_text');
		$data['entry_time_text_system_value'] = $this->language->get('entry_time_text_system_value');
		$data['entry_device'] = $this->language->get('entry_device');
		$data['entry_timezone'] = $this->language->get('entry_timezone');
		$data['entry_widget_member'] = $this->language->get('entry_widget_member');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_grid_work'] = $this->language->get('text_grid_work');
		$data['text_list_work'] = $this->language->get('text_list_work');
		$data['text_layout_1'] = $this->language->get('text_layout_1');
		$data['text_layout_2'] = $this->language->get('text_layout_2');
		$data['text_left_side'] = $this->language->get('text_left_side');
		$data['text_right_side'] = $this->language->get('text_right_side');
		$data['text_square'] = $this->language->get('text_square');
		$data['text_round'] = $this->language->get('text_round');
		$data['text_online'] = $this->language->get('text_online');
		$data['text_online_schedule'] = $this->language->get('text_online_schedule');
		$data['text_offline'] = $this->language->get('text_offline');
		$data['text_hide'] = $this->language->get('text_hide');
		$data['text_show'] = $this->language->get('text_show');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_desktop'] = $this->language->get('text_desktop');
		$data['text_mobile'] = $this->language->get('text_mobile');
		$data['text_single_line_layout'] = $this->language->get('text_single_line_layout');
		$data['text_multi_line_layout'] = $this->language->get('text_multi_line_layout');
		$data['text_widget_bottom'] = $this->language->get('text_widget_bottom');
		$data['text_widget_product'] = $this->language->get('text_widget_product');
		$data['text_widget_layout_pages'] = $this->language->get('text_widget_layout_pages');

		$data['sys_always_online'] = $this->language->get('sys_always_online');
		$data['sys_online_schedule'] = $this->language->get('sys_online_schedule');
		$data['sys_offline'] = $this->language->get('sys_offline');

		$data['column_status'] = $this->language->get('column_status');
		$data['column_weekday'] = $this->language->get('column_weekday');
		$data['column_start_time'] = $this->language->get('column_start_time');
		$data['column_end_time'] = $this->language->get('column_end_time');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['button_add_member'] = $this->language->get('button_add_member');
		$data['button_extensions'] = $this->language->get('button_extensions');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['weekdays'] = [
		  $this->language->get('text_sunday'),
		  $this->language->get('text_monday'),
		  $this->language->get('text_tuesday'),
		  $this->language->get('text_wednesday'),
		  $this->language->get('text_thursday'),
		  $this->language->get('text_friday'),
		  $this->language->get('text_saturday'),
		];

		$data['ci_token'] = $this->ci_token;
		$data['module_token'] = $this->module_token;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if(VERSION <= '2.3.0.2') {
			$this->response->setOutput($this->load->view('extension/module/ciwhatsapp_setting.tpl', $data));
		} else {
			$file_variable = 'template_engine';
			$file_type = 'template';
			$this->config->set($file_variable, $file_type);
			$this->response->setOutput($this->load->view('extension/module/ciwhatsapp_setting', $data));
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ciwhatsapp_setting')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if(!empty($this->request->post['ciwhatsapp_setting_member'])) {
			foreach($this->request->post['ciwhatsapp_setting_member'] as $key => $member) {
				if ((utf8_strlen($member['member_name']) < 3) || (utf8_strlen($member['member_name']) > 255)) {
					$this->error['member_name'][$key] = $this->language->get('error_member_name');
				}

				if ((utf8_strlen($member['member_number']) < 3) || (utf8_strlen($member['member_number']) > 32)) {
					$this->error['member_number'][$key] = $this->language->get('error_member_number');
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function MemberSortByOrder($a, $b) {
	    return $a['sort_order'] - $b['sort_order'];
	}

	// Trigger for admin/view/common/column_left/before
	public function createMainMenu(&$route, &$data, &$code) {
		if(VERSION >= '3.0.0.0') {
			$this->load->model('setting/extension');
			$installed_extensions_codes = $this->model_setting_extension->getInstalled('module');
		} else {
			$this->load->model('extension/extension');
			$installed_extensions_codes = $this->model_extension_extension->getInstalled('module');
		}

		$my_extension_code = 'ciwhatsapp_setting';

		if(in_array($my_extension_code, $installed_extensions_codes)) {
			$module_installed = true;
		} else {
			$module_installed = false;
		}

		$p = 3;
		$m = [];

      $whatsapp = [];
      $this->load->language('extension/ciwhatsapp_menu');

      if ($this->user->hasPermission('access', 'extension/module/ciwhatsapp_setting')) {
			if($module_installed) {
				$whatsapp[] = array(
					'name'	   => $this->language->get('menu_ciwhatsapp_setting'),
					'href'     => $this->url->link('extension/module/ciwhatsapp_setting', $this->module_token .'=' . $this->ci_token, true),
					'children' => []
				);
			} else {
				$whatsapp[] = array(
					'name'	   => $this->language->get('menu_ciwhatsapp_setting'),
					'href'     => $this->url->link($this->extension_path, $this->module_token .'=' . $this->ci_token .'&type=module', true),
					'children' => []
				);
			}
		}

      	if ($this->user->hasPermission('access', 'extension/module/module_ciwhatsapp')) {
        	if(VERSION <= '2.3.0.2') {
				$module_list_link = 'extension/extension';
			} else {
				$module_list_link = 'marketplace/extension';
			}



    		if(VERSION <= '2.3.0.2') {
          		$this->load->model('extension/module');
          		$whatsapp_modules = $this->model_extension_module->getModulesByCode('module_ciwhatsapp');
        	} else {
          		$this->load->model('setting/module');
          		$whatsapp_modules = $this->model_setting_module->getModulesByCode('module_ciwhatsapp');
        	}

	        $whatsapp[] = array(
	          	'name'     => $this->language->get('menu_ciwhatsapp_module'),
	          	'href'     => (count($whatsapp_modules) >= 1) ? $this->url->link('extension/module/module_ciwhatsapp', $this->module_token .'=' . $this->ci_token, true) : $this->url->link($module_list_link, $this->module_token .'=' . $this->ci_token .'&type=module', true),
	          	'children' => array()
	        );

			foreach ($whatsapp_modules as $whatsapp_module) {
          		$whatsapp[] = array(
            		'name'     => $this->language->get('menu_ciwhatsapp_child') . $whatsapp_module['name'],
            		'href'     => $this->url->link('extension/module/module_ciwhatsapp', $this->module_token .'=' . $this->ci_token .'&module_id='. $whatsapp_module['module_id'], true),
            		'children' => array()
          		);
        	}
      	}

      	if ($this->user->hasPermission('access', 'extension/ciwhatsapp_about')) {
        	$whatsapp[] = array(
	          	'name'     => $this->language->get('menu_ciwhatsapp_about'),
	          	'href'     => $this->url->link('extension/ciwhatsapp_about', $this->module_token .'=' . $this->ci_token, true),
	          	'children' => array()
        	);
      	}

      	if ($whatsapp) {
			$m = array(
				'id'			=> 'menu-ciwhatsapp',
				'icon'			=> 'fa fa-whatsapp',
				'name'	   		=> $this->language->get('menu_ciwhatsapp'),
				'href'     		=> '',
				'children' 		=> $whatsapp
			);
		}

      	if($m) {
			$data['menus'] = array_merge(array_slice($data['menus'], 0, $p), array($m), array_slice($data['menus'], $p));
		}
	}

	// Trigger for admin/view/common/header/after
	public function addHeaderScript(&$route, &$data, &$output) {
		$find = '<script type="text/javascript" src="view/javascript/bootstrap/js/bootstrap.min.js"></script>';

		$add_src = 'view/javascript/jquery/jquery-ui/jquery-ui.js';
		$add_string = '<script type="text/javascript" src="'. $add_src .'"></script>';

		if(utf8_strpos($output, $add_src) === false) {
			$output = str_replace($find, $add_string ."\n". $find, $output);
		}
	}
}