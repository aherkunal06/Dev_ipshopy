<?php
class ModelVendorKeysubmit extends Model {
	
	public function Checklicensekey($data) {
		$query=$this->db->query("SELECT * FROM " . DB_PREFIX . "allorder where module_key = '".$data['moduledata_key']."' and order_status='Complete'");
	
		
		return $query->row;
		
	}	
	
}
