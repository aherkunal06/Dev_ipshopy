<?php
class ModelVendorTmdShopbycategory extends Model {
		public function getVendors(){
		
		$sql="select * from " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd on(v.vendor_id = vd.vendor_id) WHERE v.vendor_id<>0 AND v.approved!=0   AND v.status!=0  AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		$query = $this->db->query($sql);
		return $query->rows;	
	}
}
?>