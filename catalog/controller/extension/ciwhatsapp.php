<?php
class ControllerExtensionCiwhatsapp extends Controller {
	public function bottom($search_data = []) {
		if($this->config->get('ciwhatsapp_setting_status')) {
			if(in_array($this->getDevice(), (array)$this->config->get('ciwhatsapp_setting_device'))) {
				$data = $this->getCodingInspectWhatsapp($search_data);

				$data['shape'] = $this->config->get('ciwhatsapp_setting_shape');
				$data['position'] = $this->config->get('ciwhatsapp_setting_position');
				$data['layout'] = $this->config->get('ciwhatsapp_setting_layout');

				$ciwhatsapp_description = $this->config->get('ciwhatsapp_setting_description');
				$config_language_id = $this->config->get('config_language_id');

				if(isset($ciwhatsapp_description[$config_language_id])) {
					$data['module_title'] = $ciwhatsapp_description[$config_language_id]['title'];
					$data['module_description'] = html_entity_decode($ciwhatsapp_description[$config_language_id]['description'], ENT_QUOTES, 'UTF-8');
					$data['button_text'] = $ciwhatsapp_description[$config_language_id]['button_text'];
				} else {
					$data['module_title'] = '';
					$data['module_description'] = '';
					$data['button_text'] = '';
				}

				return $this->load->view('extension/ciwhatsapp_bottom', $data);
			}
		}
	}

	public function product($search_data = []) {
		if($this->config->get('ciwhatsapp_setting_status')) {
			if(in_array($this->getDevice(), (array)$this->config->get('ciwhatsapp_setting_detailpage_device'))) {
				$data = $this->getCodingInspectWhatsapp($search_data);

				$data['layout'] = $this->config->get('ciwhatsapp_setting_detailpage_layout');
				$data['multi_inspect'] = '';

				return $this->load->view('extension/ciwhatsapp_detail', $data);
			}
		}
	}

	public function getCodingInspectWhatsapp($search_data) {
		// Previous Time Zone
		$website_timezone = date_default_timezone_get();

		// Set CI-Whatsapp Extension Time Zone
		if($this->config->get('ciwhatsapp_setting_timezone')) {
			date_default_timezone_set($this->config->get('ciwhatsapp_setting_timezone'));
		}

		$this->load->language('extension/ciwhatsapp');

		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/jquery/ciwhatsapp/style.css');
		$this->document->addScript('catalog/view/javascript/jquery/ciwhatsapp/ciwhatsapp.js');

		$config_language_id = $this->config->get('config_language_id');

		$members_data = [];

		if($search_data['apply_layout'] == 'layout_pages' && !empty($search_data['custom_members'])) {
			// Custom Layout Members From Layout Module
			$members = [];
			foreach((array)$this->config->get('ciwhatsapp_setting_member') as $member_value) {
				if(in_array($member_value['member_id'], $search_data['custom_members'])) {
					$members[] = $member_value;
				}
			}
		} else if ($this->config->get('ciwhatsapp_setting_member')) {
			// All Members From Main Setting
			$members = (array)$this->config->get('ciwhatsapp_setting_member');
		} else {
			$members = [];
		}

		foreach($members as $member) {
			if($member['status'] == 'hide') {
				continue;
			}

			// Find Layout (Bottom, Product Page, Layout Pages)
			if(!empty($member['page_status'])&& in_array($search_data['apply_layout'], (array)$member['page_status'])) {

				// BY Default Start Time End Empty
				$start_time = ''; $end_time = '';

				// BY Default Offline Status
				$online_status = 0;

				// Greeting Message
				$greeting_message = isset($member['description'][$config_language_id]['greeting_message']) ? html_entity_decode($member['description'][$config_language_id]['greeting_message'], ENT_QUOTES, 'UTF-8') :'';
				$greeting_message = rawurlencode(str_replace('&amp;', '&', $greeting_message));

				// Profile Picture For Member
				if (!empty($member['photo']) && is_file(DIR_IMAGE . $member['photo'])) {
					$photo_thumb = $this->model_tool_image->resize($member['photo'], 250, 250);
				} else {
					$photo_thumb = $this->model_tool_image->resize('no_image.png', 250, 250);
				}

				// Available Text
				if(isset($member['description'][$config_language_id]['time_text']) && !empty($member['time_text_status'])) {
					$time_text = $member['description'][$config_language_id]['time_text'];
				} else {
					$time_text = '';
				}

				// Online Status (Find Online/Offline Status)
				if(in_array($member['status'], ['online'])) {
					$online_status = 1;
				}

				// Online Schedule (Find Online/Offline Status from schedule)
				$number_of_week = date("w");
				$slot_timerunning = false;
				if(in_array($member['status'], ['online_schedule'])) {
						$start_time = $member['weekday'][$number_of_week]['start_time'];
						$end_time = $member['weekday'][$number_of_week]['end_time'];
					if(!empty($member['weekday'][$number_of_week]['status'])) {

						$full_start_time = date("H:i", strtotime($start_time));
						$full_end_time = date("H:i", strtotime($end_time));
						$current_time = date("H:i");
						if(($current_time >= $full_start_time) && ($current_time <= $full_end_time)) {
							$slot_timerunning = true;
						}
					} else {
						$time_text = '';
					}
				}
				// Time running from schedule - It means this is online
				if($slot_timerunning) {
					$online_status = 1;
				}

				// Set Time Text according to short-codes
				if($time_text) {
					$find = [
						'[START_TIME]',
						'[END_TIME]',
					];

					$replace = [
						'START_TIME'	=> $start_time,
						'END_TIME'		=> $end_time,
					];

					$time_text = str_replace($find, $replace, $time_text);
				}

				// Generate API Link For online members
				if($online_status) {
					if($this->getDevice() == 'mobile') {
						$apilink = 'https://api.whatsapp.com/send?phone='. $member['member_number'] .'&text='. $greeting_message;

					} else {
						$apilink = 'https://web.whatsapp.com/send?phone='. $member['member_number'] .'&text='. $greeting_message;
					}
				} else {
					$apilink = 'javascript:void();';
				}

				if($search_data['apply_layout_status'] == 'detail' && !$online_status) {
					// Skip offline members for detail or layout pages
					continue;
				}

				// Create All Members
				$members_data[] = [
					'member_name'				=> $member['member_name'],
					'member_number'				=> $member['member_number'],
					'sort_order'				=> $member['sort_order'],
					'photo_thumb'				=> $photo_thumb,
					'time_text'					=> $time_text,
					'department_name'			=> isset($member['description'][$config_language_id]['department_name']) ? $member['description'][$config_language_id]['department_name'] :'',
					'time_text_status'			=> isset($member['time_text_status']) ? $member['time_text_status'] : '',
					'sort_order'				=> $member['sort_order'] ? $member['sort_order'] : 0,
					'apilink'					=> $apilink,
					'status'					=> isset($member['status']) ? $member['status'] : '',
					'online_status'				=> $online_status,
				];

			}
		}

		$data['members'] = $members_data;

		// Sort Order Members
		usort($data['members'], array($this, 'MemberSortByOrder'));
		usort($data['members'], array($this, 'MemberSortbyOnline'));

		// Set website timezone from previous timezone
		date_default_timezone_set($website_timezone);

		return $data;
	}

	protected function MemberSortByOrder($a, $b) {
	    return $a['sort_order'] - $b['sort_order'];
	}

	protected function MemberSortbyOnline($a, $b) {
	    return $b['online_status'] - $a['online_status'];
	}

	public function getDevice() {
		if(isset($_SERVER["HTTP_USER_AGENT"]) && preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"])) {
			$device = 'mobile';
		} else {
			$device = 'desktop';
		}

		return $device;
	}
}