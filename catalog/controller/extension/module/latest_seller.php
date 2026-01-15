<?php
class ControllerExtensionModuleLatestseller extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/latest_seller');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_tax'] = $this->language->get('text_tax');

		$data['button_cart'] = $this->language->get('button_cart');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');
		
		$vendor_hidevnames =  $this->config->get('vendor_hidevendorname');
		$vendor_hidevemails =  $this->config->get('vendor_hidevemail');
		$vendor_hidevponenos =  $this->config->get('vendor_hidevponeno');
		$vendor_hidevsocialicons =  $this->config->get('vendor_hidevsocialicon');
		
		
		if(isset($vendor_hidevnames)){
			$data['vendor_hidevname'] = $vendor_hidevnames;
		} else {
			$data['vendor_hidevname'] = '';
		}
		
		if(isset($vendor_hidevemails)){
			$data['vendor_hidevemail'] = $vendor_hidevemails;
		} else {
			$data['vendor_hidevemail'] = '';
		}
		
		if(isset($vendor_hidevponenos)){
			$data['vendor_hidevponeno'] = $vendor_hidevponenos;
		} else {
			$data['vendor_hidevponeno'] = '';
		}
		
		if(isset($vendor_hidevsocialicons)){
			$data['vendor_hidevsocialicon'] = $vendor_hidevsocialicons;
		} else {
			$data['vendor_hidevsocialicon'] = '';
		}
		
		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['vendors'] = array();

		$filter_data = array(
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => 0,
			'limit' => $setting['limit']
		);
		
		$this->load->model('vendor/allseller');
		$this->load->model('vendor/vendor');
		$results = $this->model_vendor_vendor->getVendors($filter_data);

		if ($results) {
			foreach ($results as $result) {
				
				if (is_file(DIR_IMAGE . $result['banner'])) {
				$image = $this->model_tool_image->resize($result['banner'], 600, 200);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', 600, 200);
				}
				
				if (is_file(DIR_IMAGE . $result['image'])) {
					$smallimage = $this->model_tool_image->resize($result['image'], 70, 70);
				} else {
					$smallimage = $this->model_tool_image->resize('no_image.png', 70, 70);
				}
				
				
				$abouts = strlen($result['about']);
				
				if ($abouts > 150) {
					$about = utf8_substr(strip_tags(html_entity_decode($result['about'], ENT_QUOTES, 'UTF-8')), 0, 150) . '<a href='.$this->url->link('vendor/vendor_profile', 'vendor_id=' . $result['vendor_id']).'> <br/>Read More </a>';
				} else {
					$about =$result['about'];
				}
				
				$store_info = $this->model_vendor_allseller->getVendordescription($result['vendor_id']);
				if(isset($store_info['name'])){
					$storename = $store_info['name'];
				} else {
					$storename = '';
				}
				if(isset($result['vendor_id'])){
				$totalproduct = $this->model_vendor_allseller->getTotalProduct($result['vendor_id']);
				}
				
				$data['vendors'][] = array(
					'vendor_id'   => $result['vendor_id'],
					'thumb'       => $image,
					'smallthumb'  => $smallimage,
					'totalproduct'=> $totalproduct ,
					'firstname'   => $result['firstname'].' '.$result['lastname'],
					'email'   	  => $result['email'],
					'telephone'   	  => $result['telephone'],
					'facebookurl'   	  => $result['facebook_url'],
					'googleurl'   	  => $result['google_url'],
					'storename'       => $storename,
					'href'        => $this->url->link('vendor/vendor_profile', 'vendor_id=' . $result['vendor_id']),
				);
			}

			$customer2vendor = $this->config->get('vendor_customer2vendor');
			if($customer2vendor==1){
			return $this->load->view('extension/module/latest_seller', $data);
			}
		}
	}
}