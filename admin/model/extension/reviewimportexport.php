<?php
class ModelExtensionreviewimportexport extends Model {
	
	public function updatereview($data)
	{
		$this->db->query("UPDATE " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['author']) . "', product_id = '" . (int)$data['product_id'] . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', rating = '" . (int)$data['rating'] . "', status = '" . (int)$data['status'] . "',  date_added = '" . $this->db->escape($data['date_added']) . "', date_modified = '" . $this->db->escape($data['date_modified']) . "' WHERE review_id = '" . (int)$data['review_id']. "'");

	}
	public function addreview($data)
	{
		$this->db->query("insert " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['author']) . "', product_id = '" . (int)$data['product_id'] . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', rating = '" . (int)$data['rating'] . "', status = '" . (int)$data['status'] . "',  date_added = '" . $this->db->escape($data['date_added']) . "', date_modified = '" . $this->db->escape($data['date_modified']) . "'");

	}
	public function getproductidbymodel($model)
	{
		$query=$this->db->query("select product_id from " . DB_PREFIX . "product WHERE model = '" . $model . "'");
	}
	public function getcustomeridbyemail($email)
	{
		$query=$this->db->query("select customer_id from " . DB_PREFIX . "customer WHERE email = '" . $email . "'");
	}
}