<?php
class ModelExtensionCiwhatsappSetting extends Model {
	public function __construct($registery) {
		parent::__construct($registery);

		$this->load->model('user/user_group');

		if(VERSION <= '2.3.0.2') {
			$this->module_token = 'token';
			$this->ci_token = isset($this->session->data['token']) ? $this->session->data['token'] : '';
		} else {
			$this->module_token = 'user_token';
			$this->ci_token = isset($this->session->data['user_token']) ? $this->session->data['user_token'] : '';
		}

		if(VERSION >= '3.0.0.0') {
			$this->load->model('setting/event');
		} else {
			$this->load->model('extension/event');
		}

		$this->load->model('setting/setting');
	}

	public function createTables() {
	}

	public function cratePermissions($file_routes) {
		$user_group_id = $this->user->getGroupId();

		foreach ($file_routes as $route) {
			$this->model_user_user_group->removePermission($user_group_id, 'access', $route);
			$this->model_user_user_group->removePermission($user_group_id, 'modify', $route);

			$this->model_user_user_group->addPermission($user_group_id, 'access', $route);
			$this->model_user_user_group->addPermission($user_group_id, 'modify', $route);
		}
	}

	public function addSampleData() {

	}

	private function recursionLanguageArray(&$module_ciwhatsapp) {
		if(is_array($module_ciwhatsapp)) {
			foreach ($module_ciwhatsapp as $key => &$value) {
				if($key === 'ACTIVE_LANGUAGE') {
					$description = [];
					foreach ($this->model_localisation_language->getLanguages() as $language) {
							$description[$language['language_id']] = $value;
				   		}
					$module_ciwhatsapp = $description;
				}

				if(is_array($value)) {
					$this->recursionLanguageArray($value);
				}
			}
		}
	}

	public function createEvents($data) {
		foreach ($data['events'] as $folder => $folder_info) {
			foreach ($folder_info as $event) {
				if(VERSION >= '3.0.0.0') {
					$this->model_setting_event->addEvent($data['code'] .'_'. $folder, $event['trigger'], $event['action'], $data['status'], $data['sort_order']);
				} else {
					$this->model_extension_event->addEvent($data['code'] .'_'. $folder, $event['trigger'], $event['action'], $data['status'], $data['sort_order']);
				}
			}
		}

	}

	public function enableEvents($code) {
		$query = $this->db->query("UPDATE `" . DB_PREFIX . "event` SET status = 1 WHERE `code` = '" . $this->db->escape($code) . "'");
	}

	public function syncEvents($data) {
		/* Create Missing Events Into Database */
		$found_missing_events = [];
		foreach ($data['events'] as $folder => $folder_info) {
			foreach ($folder_info as $event) {
				$filter_data = [
					'code'		=> $data['code'] .'_'. $folder,
					'trigger'	=> $event['trigger'],
					'action'	=> $event['action'],
				];

				$existing_event = $this->getEventByCode($filter_data);
				if(!$existing_event) {
					$found_missing_events[$folder][] = $event;
				}
			}
		}

		if($found_missing_events) {
			$add_data = [
				'events'		=> $found_missing_events,
				'code'			=> $data['code'],
				'description'	=> $data['description'],
				'status'		=> $data['status'],
				'sort_order'	=> $data['sort_order'],
			];
			$this->createEvents($add_data);
		}
		/* Create Missing Events Ends */

		/* Remove Extra Events from Database Starts */
		$file_string = [];
		$codes = [];
		foreach ($data['events'] as $folder => $folder_info) {
			foreach ($folder_info as $event) {
				$file_string[] = $event['trigger'] .':'. $event['action'];
			}

			$codes[] = $data['code'] .'_'. $folder;
		}

		$filter_data = [
			'codes'		=> $codes,
		];

		$db_events = $this->getEventsByCode($filter_data);

		foreach($db_events as $db_event) {
			if(!in_array($db_event['trigger'] .':'. $db_event['action'], $file_string)) {
				$this->deleteEvent($db_event['event_id']);
			}
		}
		/* Remove Extra Events from Database Ends */

		/* Remove Duplicate Events from Database Starts */
		$filter_data = [
			'codes'		=> $codes,
		];
		$duplicates = $this->getDuplicateEvents($filter_data);
		foreach ($duplicates as $duplicate) {
			$this->deleteEvent($duplicate['event_id']);
		}
		/* Remove Duplicate Events from Database Ends */
	}

	public function getLinks($buttons_links) {
		$this->response->redirect($buttons_links);
	}

	public function getHeader($type, $data) {
		$this->getPage($type, $data);
	}

	public function getFooter($type, $data) {
		$this->getPage($type, $data);
	}

	public function getPage($type, $data) {
		$this->model_setting_setting->editSetting($type, $data);
	}

	public function removeEvents($data) {
		foreach ($data['events'] as $folder => $folder_info) {
			$this->deleteEventByCode($data['code'] .'_'. $folder);
		}
	}

	public function deleteEvent($event_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `event_id` = '" . (int)$event_id . "'");
	}

	public function deleteEventByCode($code) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($code) . "'");
	}

	public function getEventByCode($data) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($data['code']) . "'";

		if(!empty($data['trigger'])) {
			$sql .= " AND `trigger` = '" . $this->db->escape($data['trigger']) . "'";
		}

		if(!empty($data['action'])) {
			$sql .= " AND `action` = '" . $this->db->escape($data['action']) . "'";
		}

		$sql .= " ORDER BY event_id ASC";

		$query = $this->db->query($sql);

		return $query->row;
	}

	public function getEventsByCode($data) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "event` WHERE event_id > 0";

		if(!empty($data['codes'])) {
			$implode = array();
			$sql .= " AND (";
			foreach ($data['codes'] as $code) {
				$implode[] = "`code` = '" . $this->db->escape($code) . "'";
			}

			if ($implode) {
				$sql .= " " . implode(" OR ", $implode) . "";
			}

			$sql .= ")";
		} else {
			$sql .= " AND `code` = '" . $this->db->escape($data['code']) . "'";
		}

		if(!empty($data['trigger'])) {
			$sql .= " AND `trigger` = '" . $this->db->escape($data['trigger']) . "'";
		}

		if(!empty($data['action'])) {
			$sql .= " AND `action` = '" . $this->db->escape($data['action']) . "'";
		}

		$sql .= " ORDER BY event_id ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getEventsByShortCode($code) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "event` WHERE `code` LIKE '" . $this->db->escape($code) . "%'";

		$sql .= " ORDER BY event_id ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getDuplicateEvents($data) {
		$sql = "SELECT event_id, `trigger`, COUNT(`trigger`), `action`, COUNT(`action`) FROM `" . DB_PREFIX . "event` WHERE event_id > 0";

      	if(!empty($data['codes'])) {
			$implode = array();
			$sql .= " AND (";
			foreach ($data['codes'] as $code) {
				$implode[] = "`code` = '" . $this->db->escape($code) . "'";
			}

			if ($implode) {
				$sql .= " " . implode(" OR ", $implode) . "";
			}

			$sql .= ")";
		} else {
			$sql .= " AND `code` = '" . $this->db->escape($data['code']) . "'";
		}

		$sql .= " GROUP BY `trigger`,`action` HAVING COUNT(`trigger`) > 1 AND COUNT(`action`) > 1";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalEvents($data = []) {

		$sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($data['code']) . "'";

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getButtons($type = '') {
		$modules = $type . '_type'; $d_type = 'd';

		$this->load->model('setting/setting');

		$e_type = 'e'; $l_type = 'l';

		$module_info = $this->model_setting_setting->getSetting($modules, 0);

		if($module_info) {
			$fields = $module_info[$modules .'_'. $d_type] .'-'. $this->config->get('module_ciwhatsapp_key') .'-'. $module_info[$modules .'_'. $e_type];
		} else {
			$fields = '';
		}

		if($module_info) {
			$all_fields = md5($fields);
		} else {
			$all_fields = '';
		}

		if($all_fields) {
			$button = $this->config->get($all_fields);
		} else {
			$button = '';
		}

		return !$button;
	}
}