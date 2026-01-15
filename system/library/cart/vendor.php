<?php
namespace Cart;
class Vendor {
	private $vendor_id;
	private $firstname;
	private $email;	
	private $address_1;
	private $config;
	private $db;
	private $request;
	private $session;

	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
		/* 07-02-2019 update  approved code*/
		if (isset($this->session->data['vendor_id'])) {
			$vendor_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . (int)$this->session->data['vendor_id'] . "' AND status = '1' AND approved = '1'");

			if ($vendor_query->num_rows) {
				$this->vendor_id = $vendor_query->row['vendor_id'];
				$this->firstname = $vendor_query->row['firstname'];
				$this->email = $vendor_query->row['email'];
				
				$this->db->query("UPDATE " . DB_PREFIX . "vendor SET language_id = '" . (int)$this->config->get('config_language_id') . "' WHERE vendor_id = '" . (int)$this->vendor_id . "'");
			} else {
				$this->logout();
			}
		}
	}

	public function login($email, $password, $override = false) {
		/* 07-02-2019 update  approved code */
		if ($override) {
			$vendor_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND status = '1' AND approved = '1'");
		} else {
			$vendor_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) OR password = '" . $this->db->escape(md5($password)) . "') AND status = '1' AND approved = '1'");
		}

		if ($vendor_query->num_rows) {
			$this->session->data['vendor_id'] = $vendor_query->row['vendor_id'];

			$this->vendor_id = $vendor_query->row['vendor_id'];
			$this->firstname = $vendor_query->row['firstname'];
			$this->email = $vendor_query->row['email'];
			
			$this->db->query("UPDATE " . DB_PREFIX . "vendor SET language_id = '" . (int)$this->config->get('config_language_id') . "' WHERE vendor_id = '" . (int)$this->vendor_id . "'");

			return true;
		} else {
			return false;
		}
	}

	public function logout() {
		unset($this->session->data['vendor_id']);

		$this->vendor_id = '';		
		$this->firstname = '';		
		$this->email = '';
	}

	public function isLogged() {
		return $this->vendor_id;
	}

	public function getId() {
		return $this->vendor_id;
	}
	
	public function getFirstName() {
		return $this->firstname;
	}

	public function getEmail() {
		return $this->email;
	}

	public function getAddress1() {
		return $this->address_1;
	}
	
}
