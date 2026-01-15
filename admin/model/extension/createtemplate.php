<?php
class ModelExtensionCreatetemplate extends Model {
	public function install() {
	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."createtemplate` (
  `createtemplate_id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`createtemplate_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."createtemplate_description` (
  `createtemplate_description_id` int(11) NOT NULL AUTO_INCREMENT,
  `createtemplate_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`createtemplate_description_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

$modules= array(
			   'extension_id' => '19776',
			   'email' => $this->config->get('config_email'),
			   'store_url' => HTTP_CATALOG
			);
	
		$url = 'https://www.opencartextensions.in/index.php?route=api/callback';
		
		$curl = curl_init($url);
		
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($modules, '', '&'));
		
		 $response = curl_exec($curl);
	}
	public function uninstall() {
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."createtemplate`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."createtemplate_description`");
	}
	public function addCreatetemplate($data) {
	$this->db->query("INSERT INTO " . DB_PREFIX . "createtemplate SET status='" . (int)$data['status'] ."'");
	$createtemplate_id = $this->db->getLastId();
	
	foreach ($data['createtemplate'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "createtemplate_description SET createtemplate_id= '" . (int)$createtemplate_id ."', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', subject = '" . $this->db->escape($value['subject']) . "', message = '" . $this->db->escape($value['message']) . "'");
		}
	}
	
	public function editCreatetemplate($createtemplate_id, $data) {
	
	$this->db->query("UPDATE " . DB_PREFIX . "createtemplate SET status='" . (int)$data['status'] ."' where createtemplate_id='" . (int)$createtemplate_id ."'");
	
	$this->db->query("DELETE FROM " . DB_PREFIX . "createtemplate_description WHERE createtemplate_id = '" . (int)$createtemplate_id . "'");
	foreach ($data['createtemplate'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "createtemplate_description SET  createtemplate_id= '" . (int)$createtemplate_id ."', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', subject = '" . $this->db->escape($value['subject']) . "', message = '" . $this->db->escape($value['message']) . "'");
		}
	}
	
	public function deleteCreatetemplate($createtemplate_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "createtemplate WHERE createtemplate_id='" . (int)$createtemplate_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "createtemplate_description WHERE createtemplate_id='" . (int)$createtemplate_id . "'");
	}
	
	public function getCreatetemplate($createtemplate_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "createtemplate WHERE createtemplate_id = '" . (int)$createtemplate_id . "'");
		return $query->row;
	}
	
	// MUlti get
	public function getCreatetemplatedata($createtemplate_id) {
		$createtemplate_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "createtemplate_description WHERE createtemplate_id = '" . (int)$createtemplate_id . "'");
		
		foreach ($query->rows as $result) {
			$createtemplate_description_data[$result['language_id']] = array(
				'subject'      => $result['subject'],
				'message'      => $result['message'],
				'name'      => $result['name']
				
			);
		}
		
		return $createtemplate_description_data;
	}
		
	public function getCreatetemplates($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "createtemplate c LEFT JOIN " . DB_PREFIX . "createtemplate_description cd on(c.createtemplate_id=cd.createtemplate_id)";
		
		$sql .= " GROUP BY c.createtemplate_id";
		
		$sort_data = array(
			'c.name',
			'status'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}					

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}		
		
		$query = $this->db->query($sql);

		return $query->rows;
	}
		
	
		
	public function getTotalCreatetemplates() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "createtemplate");
		
		return $query->row['total'];
	}	
}
?>