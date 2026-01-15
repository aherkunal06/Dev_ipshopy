<?php
class ControllerExtensionModuleTmdVendor extends Controller {
	public function index() {
		$this->load->language('extension/module/tmd_vendor');

		$data['heading_title'] = $this->language->get('heading_title');

		/// seller Product Start ////
			$data['text_sellerinformation'] = $this->language->get('text_sellerinformation');
			$data['text_contactseller'] = $this->language->get('text_contactseller');
			$data['text_seller'] = $this->language->get('text_seller');
			$data['text_from'] = $this->language->get('text_from');
			$data['text_totalproducts'] = $this->language->get('text_totalproducts');
			$vendor_hidevnames =  $this->config->get('vendor_hidevendorname');
			if(isset($vendor_hidevnames)){
				$data['vendor_hidevname'] = $vendor_hidevnames;
			} else {
				$data['vendor_hidevname'] = '';
			}
			$data['headingbg'] = $this->config->get('tmd_vendor_bgcolor');
			$data['textcolor'] = $this->config->get('tmd_vendor_textcolor');
			$imagetype = $this->config->get('tmd_vendor_imagetype');
			if($imagetype=='round'){
			$data['imgeborder'] = 'roundborder';
			} elseif($imagetype=='rect'){
				
			$data['imgeborder'] = 'rectborder';
			}
			
			
			$vendor_status = $this->config->get('tmd_vendor_status');
			
			$imagewidths = $this->config->get('tmd_vendor_imgwidth');
			$imageheights = $this->config->get('tmd_vendor_imgheight');
			
			if(!empty($imagewidths)){
				$imagewidth = $imagewidths;
			} else {
				$imagewidth = 100;
				
			}

			if(!empty($imageheights)){
				$imageheight = $imageheights;
			} else {
				$imageheight = 100;
				
			}	
			
			$this->load->model('vendor/vendor');
			$this->load->model('tool/image');
			if(isset($this->request->get['product_id'])){
			$sellerproduct_info = $this->model_vendor_vendor->getSellerProduct($this->request->get['product_id']);
				if(isset($sellerproduct_info['vendor_id'])) {
					$seller_info = $this->model_vendor_vendor->getSellerInfo($sellerproduct_info['vendor_id']);
				}
			}
			
			if(isset($seller_info['display_name'])){
				$dname = $seller_info['display_name'];
			} else {
				$dname='';
			}
			
			if(isset($seller_info['countryname'])){
				$data['countryname'] = $seller_info['countryname'];
			} else {
				$data['countryname']='';
			}
			
			if(isset($sellerproduct_info['vendor_id'])){
			$seller_vendor_id = $sellerproduct_info['vendor_id'];
			} else {				
			$seller_vendor_id='';	
			}
			$totalcount = $this->model_vendor_vendor->getTotalCollections($seller_vendor_id);
			
			
			if(isset($totalcount)){
				$data['totalproducts'] = $totalcount;
			} else {
				$data['totalproducts']='';
			}
			
			if(isset($seller_info['vendor_id'])){
				$vendor_ids = $seller_info['vendor_id'];
			} else {
				$vendor_ids='';
			}
			
			if(!empty($seller_info['image'])){
				$sellerimage = $this->model_tool_image->resize($seller_info['image'],$imagewidth,$imageheight);
			} else {
				$sellerimage = $this->model_tool_image->resize('placeholder.png',$imagewidth,$imageheight);
			}
			/* new code 07 10 2021 */
			$data['reviewvalue'] = $this->model_vendor_vendor->getVendorSumValue($vendor_ids);
			$data['sellertotal'] = $this->model_vendor_vendor->getTotalSellerReview($vendor_ids);
			$vendor_hidevsocialicons =  $this->config->get('vendor_hidevsocialicon');
			if(isset($vendor_hidevsocialicons)){
			$data['vendor_hidevsocialicon'] = $vendor_hidevsocialicons;
			} else {
				$data['vendor_hidevsocialicon'] = '';
			}
			
			if(!empty($seller_info['facebook_url'])){
			$facebookurl = $seller_info['facebook_url'];
			} else {
				$facebookurl = '';
			}

			if(!empty($seller_info['google_url'])){
				$googleurl = $seller_info['google_url'];
			} else {
				$googleurl = '';
			}
			$data['facebookurl'] = $facebookurl;
			$data['googleurl']   = $googleurl;
			
			/* new code 07 10 2021 */
			$data['sellerimage'] = $sellerimage;
			$data['dname'] = $dname;
			$data['href']  = $this->url->link('vendor/vendor_profile&vendor_id='.$vendor_ids, '', true);
			$data['vendor_ids']   = $vendor_ids;
					
		/// seller Product End ////	
		/* tmd vendor2 seler condtion start */
		$customer2vendor = $this->config->get('vendor_customer2vendor');
		if($customer2vendor==1){
			if(!empty($vendor_ids) || !empty($vendor_statu)){
			return $this->load->view('extension/module/tmd_vendor', $data);
			}
		}
		/* tmd vendor2 seler condtion end */	
	}
}