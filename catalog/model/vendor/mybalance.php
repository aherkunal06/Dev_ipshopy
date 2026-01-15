<?php
class ModelVendorMybalance extends Model {
		
	public function getAmount($vendor_id){
		$sql = "SELECT sum(amount) AS total FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id='".(int)$this->vendor->getId()."'";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
			
 	public function getVendorTotal($vendor_id) {
		
		$implode = array();
		
		
		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
		
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
		
		/*############26 06 2025 Update code################*/
		$sql = "SELECT sum(vop.total) as total,sum(tax*quantity) as tax FROM " . DB_PREFIX . "vendor_order_product vop 
		LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) 
		WHERE vop.vendor_id='".(int)$this->vendor->getId()."' AND vop.order_status_id IN(5)"; //" . implode(",", $implode) . "
		
		
			$query = $this->db->query($sql);
		
		 $query->row['total'] += $query->row['tax'];	
		 /*############13 02 2021 Update code################*/
		return $query->row['total'];
	}

	public function getTotalAmount($vendor_id){
		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
		
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
		// updated logic to get only complete order balance on 26-06-2025
		$sql = "SELECT sum(vop.totalcommission) as total FROM " . DB_PREFIX . "vendor_order_product vop 
		LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) 
		WHERE vop.vendor_id='".(int)$this->vendor->getId()."' AND  vop.order_status_id IN(5)"; //" . implode(",", $implode) . "
		
			$query = $this->db->query($sql);
			
		return $query->row['total'];
	}
	/* 27 03 2020 */
	public function getVendorOrder($vendor_id){
		$implode = array();
		
		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
		
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
		/*############26 06 2021 update code################*/
		$sql = "SELECT sum(tmdshippingcost) as tmdshippingcost FROM " . DB_PREFIX . "vendor_order_product  
		WHERE vendor_id='".$this->vendor->getId()."' AND order_status_id IN(5)"; //" . implode(",", $implode) . "
		
		$query = $this->db->query($sql);
		
		return $query->row;
	}
	
	public function getTotalCommissionamount($data,$vendor_id){
		$implode = array();
		
		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
		
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
		

		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
						
		$sql .= " AND vendor_id ='".(int)$vendor_id. "'";

		// update the code changes regarding to the 26-06-2025
		$sql .= " AND vendor_id ='".(int)$vendor_id. "' AND order_status_id IN(5)"; //" . implode(",", $implode) . "
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}	
	/* 27 03 2020 */
}
?>