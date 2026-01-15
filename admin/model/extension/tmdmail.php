<?php
class ModelExtensionTmdmail extends Model {
	/* Signup*/
	public  function getTemplateinfo($template_id)
	{
		$query=$this->db->query("select * from " . DB_PREFIX . "createtemplate c LEFT JOIN " . DB_PREFIX . "createtemplate_description cd on(c.createtemplate_id=cd.createtemplate_id) where c.createtemplate_id='" .$template_id."'");
		 return $query->row;
	}
	
	
	/* Signup End*/
}
?>