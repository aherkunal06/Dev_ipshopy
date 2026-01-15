<?php
class ModelVendorMail extends Model {
		/* Signup*/
	public  function getMailInfo($sellertype){
		
		
		$query=$this->db->query("select * from " . DB_PREFIX . "vendor_mail vm LEFT JOIN " . DB_PREFIX . "vendor_mail_language vml on(vm.mail_id=vml.mail_id) where vm.sellertype='" .$sellertype."'and vml.language_id = '" . (int)$this->config->get('config_language_id') . "'");
	
		return $query->row;
		
	}
	
	public function getTemplateinfo($sellertype,$language_id=0){ 
		if(!empty($language_id)){
			$language_id=$language_id;
		} else {
			$language_id=$this->config->get('config_language_id'); 
		}
		$query=$this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_mail vm LEFT JOIN " . DB_PREFIX . "vendor_mail_language vml on(vm.mail_id=vml.mail_id) WHERE vm.sellertype='" .$sellertype."' AND vml.language_id = '" . (int)$language_id. "' limit 0,1");
		 return $query->row;
	}
	
	
}