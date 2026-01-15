<?php
class ControllervendorFindme extends Controller {
	private $error = array();
	public function index() {
		
		$this->load->language('vendor/findme');
			
    
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('vendor/dashboard', '', true)
		);
        
        $this->document->setTitle($this->language->get('heading_title'));

					
		$data['heading_title'] 		= $this->language->get('heading_title');
        
		$data['text_register'] 		= $this->language->get('text_register');
		$data['text_account'] 			= $this->language->get('text_account');
		$data['text_companyname'] 		= $this->language->get('text_companyname');
		$data['text_rating'] 		= $this->language->get('text_rating');
		$data['text_contactname'] 		= $this->language->get('text_contactname');
		$data['text_telephone'] 		= $this->language->get('text_telephone');
		$data['text_fax'] 		= $this->language->get('text_fax');
		$data['text_location'] 		= $this->language->get('text_location');
		$data['text_email'] 	= $this->language->get('text_email');
		$data['text_address'] 		= $this->language->get('text_address');
		$data['text_city'] 			= $this->language->get('text_city');
		$data['text_country']= $this->language->get('text_country');
		$data['text_zone']= $this->language->get('text_zone');
		$data['text_postcode']= $this->language->get('text_postcode');
		
		$this->load->model('vendor/vendor');
		$this->load->model('tool/image');
				
		if(isset($this->request->get['vendor_id'])) {	
			 $vendor_info = $this->model_vendor_vendor->getVendor($this->request->get['vendor_id']);
		}
				
		if(!empty($vendor_info['store_logowidth'])){
			$store_logowidth = $vendor_info['store_logowidth'];
		} else {
			$store_logowidth = 75;
		}
		
		if(!empty($vendor_info['store_logoheight'])){
			$store_logoheight = $vendor_info['store_logoheight'];
		} else {
			$store_logoheight = 75;
		}
		
		if(!empty($vendor_info['logo'])){
			$logos = $this->model_tool_image->resize($vendor_info['logo'],$store_logowidth, $store_logoheight);
		} else {
			$logos = $this->model_tool_image->resize('placeholder.png',$store_logowidth,$store_logoheight);
		}
		
		if(!empty($vendor_info['company'])){
			$company = $vendor_info['company'];
		} else {
			$company = '';
		}
        if(!empty($vendor_info['display_name'])){
			$display_name = $vendor_info['display_name'];
		} else {
			$display_name = '';
		}
		if(!empty($vendor_info['vendor_id'])){
			$vendor_id = $vendor_info['vendor_id'];
		} else {
			$vendor_id = '';
		}
		
		if(!empty($vendor_info['telephone'])){
			$vendortelephone = $vendor_info['telephone'];
		} else {
			$vendortelephone = '';
		}
        
        if(!empty($vendor_info['fax'])){
			$vendorfax = $vendor_info['fax'];
		} else {
			$vendorfax = '';
		}
        
        if(!empty($vendor_info['address_1'])){
			$vendoraddress = $vendor_info['address_1'];
		} else {
			$vendoraddress = '';
		}
        
        if(!empty($vendor_info['city'])){
			$vendorcity = $vendor_info['city'];
		} else {
			$vendorcity = '';
		}
        
        $this->load->model('localisation/country');
        $this->load->model('localisation/zone');
		$data['countries'] = $this->model_localisation_country->getCountries();

		if(!empty($vendor_info['country_id'])){
			$vendorcountry = $this->model_localisation_country->getCountry($vendor_info['country_id']);
			if(isset($vendorcountry['name'])){
				$country_name = $vendorcountry['name'];
			} else {
				$country_name = '';
			}
		} else {
			$vendorcountry ='';
		}

		if(!empty($vendor_info['zone_id'])){
			$vendorzone = $this->model_localisation_zone->getZone($vendor_info['zone_id']);
			if(isset($vendorzone['name'])){
				$zone_name = $vendorzone['name'];
			} else {
				$zone_name = '';
			}
		} else {
			$vendorzone ='';
		}

		if(!empty($vendor_info['name'])){
			$vendorname = $vendor_info['name'];
		} else {
			$vendorname = '';
		}
		
        $map_url = html_entity_decode($vendor_info['map_url'], ENT_QUOTES, 'UTF-8');

		if(!empty($vendor_info['about'])){
			$aboutvendor = $vendor_info['about'];
		} else {
			$aboutvendor = '';
		}
		
		if(!empty($vendor_info['text'])) {
			$ratingtext = $vendor_info['text'];
		} else {
			$ratingtext='';
		}		
				
		if(!empty($vendor_info['email'])){
			$vendoremail = $vendor_info['email'];
		} else {
			$vendoremail = '';
		}
        
        if(!empty($vendor_info['postcode'])) {
			$vendorpostcode = $vendor_info['postcode'];
		} else {
			$vendorpostcode='';
		}
       
		$data['reviewvalue'] = $this->model_vendor_vendor->getVendorSumValue($vendor_id);

		$data['logos'] 		    = $logos;
		$data['name'] 			= $vendorname;
		$data['map_url'] 		= $map_url;
		$data['display_name'] 	= $display_name;
		$data['company'] 	    = $company;
		$data['vendor_id'] 	    = $vendor_id; 
		$data['catevendor_id'] 	= $vendor_id; 
		$data['email'] 			= $vendoremail;
		$data['telephone'] 		= $vendortelephone;
		$data['fax'] 		    = $vendorfax;
		$data['address'] 		= $vendoraddress;
		$data['city'] 		    = $vendorcity;
		$data['country'] 		= $country_name;
		$data['zone'] 			= $zone_name;
		$data['ratingtext'] 	= $ratingtext;
		$data['postcode'] 		= $vendorpostcode;
		
		/* 08 06 2020 */
		$vendor_hidevnames =  $this->config->get('vendor_hidevendorname');
		$vendor_hidevemails =  $this->config->get('vendor_hidevemail');
		$vendor_hidevponenos =  $this->config->get('vendor_hidevponeno');
		
		
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
		/* 08 06 2020 */
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		/* tmd vendor2 seler condtion start */
		$vendorloged = $this->vendor->isLogged();
		$customer2vendor = $this->config->get('vendor_customer2vendor');
		if($customer2vendor==1 || $vendorloged){
		$this->response->setOutput($this->load->view('vendor/findme', $data));
		} else {
		
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			
			$this->document->setTitle($this->language->get('heading_titleseler'));

			$data['heading_title'] = $this->language->get('heading_titleseler');
			$data['text_error'] = $this->language->get('text_error1');
			$data['button_continue'] = $this->language->get('button_continue');
			
			$data['continue'] = $this->url->link('commmon/home');
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
		/* tmd vendor2 seler condtion start */
	}
}