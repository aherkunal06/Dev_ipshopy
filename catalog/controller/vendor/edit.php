<?php
class ControllerVendorEdit extends Controller
{
	private $error = array();

	public function index()
	{
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}

		$this->load->language('vendor/edit');

		$this->document->setTitle($this->language->get('heading_title'));


		$this->load->model('vendor/vendor');


		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_vendor_vendor->editVendor($this->vendor->getid(), $this->request->post);

			$this->response->redirect($this->url->link('vendor/dashboard'));
		}
		
		$data['cancel'] = $this->url->link('vendor/dashboard');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('vendor/edit', '', true)
		);

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('vendor/login', '', true));
		$data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_yes'] 			= $this->language->get('text_yes');
		$data['text_no'] 			= $this->language->get('text_no');
		$data['text_select'] 		= $this->language->get('text_select');
		$data['text_none'] 			= $this->language->get('text_none');
		$data['text_loading'] 		= $this->language->get('text_loading');
		$data['entry_firstname'] 	= $this->language->get('entry_firstname');
		$data['entry_lastname'] 	= $this->language->get('entry_lastname');
		$data['entry_email'] 		= $this->language->get('entry_email');
		$data['entry_telephone'] 	= $this->language->get('entry_telephone');
		$data['entry_gstin'] 			= $this->language->get('entry_gstin');
		$data['entry_company'] 		= $this->language->get('entry_company');
		$data['entry_address_1'] 	= $this->language->get('entry_address_1');
		$data['entry_address_2'] 	= $this->language->get('entry_address_2');

		$data['entry_city'] 		= $this->language->get('entry_city');
		$data['entry_country'] 		= $this->language->get('entry_country');
		$data['entry_zone'] 		= $this->language->get('entry_zone');
		$data['entry_newsletter'] 	= $this->language->get('entry_newsletter');
		$data['entry_password'] 	= $this->language->get('entry_password');
		$data['entry_confirm'] 		= $this->language->get('entry_confirm');
		$data['entry_about'] 		= $this->language->get('entry_about');
		$data['entry_image'] 		= $this->language->get('entry_image');
		$data['entry_display_name'] = $this->language->get('entry_display_name');
		$data['entry_logowidth']    = $this->language->get('entry_logowidth');
		$data['entry_logoheight']    = $this->language->get('entry_logoheight');
		$data['entry_bannerwidth']    = $this->language->get('entry_bannerwidth');
		$data['entry_bannerheight']    = $this->language->get('entry_bannerheight');
		$data['entry_bankname']  		= $this->language->get('entry_bankname');
		$data['entry_bnumber']  		= $this->language->get('entry_bnumber');
		$data['entry_swiftcode']  		= $this->language->get('entry_swiftcode');
		$data['entry_aname']  			= $this->language->get('entry_aname');
		$data['entry_anumber']  		= $this->language->get('entry_anumber');
		$data['entry_Emailid']  		= $this->language->get('entry_Emailid');
		$data['entry_method']  			= $this->language->get('entry_method');
		$data['text_bank']  			= $this->language->get('text_bank');
		$data['text_paypal']  			= $this->language->get('text_paypal');

		$data['entry_storename'] 		= $this->language->get('entry_storename');
		$data['entry_description'] 		= $this->language->get('entry_description');
		$data['entry_shippingpolicy'] 	= $this->language->get('entry_shippingpolicy');
		$data['entry_returnpolicy'] 	= $this->language->get('entry_returnpolicy');
		$data['entry_metakeyword'] 		= $this->language->get('entry_metakeyword');
		$data['entry_metadescription'] 	= $this->language->get('entry_metadescription');
		$data['entry_email'] 			= $this->language->get('entry_email');
		$data['entry_phone'] 			= $this->language->get('entry_phone');
		$data['entry_address'] 			= $this->language->get('entry_address');
		$data['entry_country'] 			= $this->language->get('entry_country');
		$data['entry_zone'] 			= $this->language->get('entry_zone');
		$data['entry_city'] 			= $this->language->get('entry_city');
		$data['entry_postcode'] 		= $this->language->get('entry_postcode');
		$data['entry_detail'] 			= $this->language->get('entry_detail');
		$data['entry_tax'] 				= $this->language->get('entry_tax');
		$data['entry_charges'] 			= $this->language->get('entry_charges');
		$data['entry_url'] 				= $this->language->get('entry_url');
		$data['entry_logo'] 			= $this->language->get('entry_logo');
		$data['entry_banner'] 			= $this->language->get('entry_banner');
		$data['entry_store_about'] 		= $this->language->get('entry_store_about');
		$data['entry_mapurl'] 		    = $this->language->get('entry_mapurl');
		$data['entry_facebookurl'] 		= $this->language->get('entry_facebookurl');
		$data['entry_googleurl'] 		= $this->language->get('entry_googleurl');
		$data['button_upload'] 			= $this->language->get('button_upload');
		$data['button_banner'] 			= $this->language->get('button_banner');
		$data['button_add'] 			= $this->language->get('button_add');
		$data['tab_general'] 			= $this->language->get('tab_general');
		$data['tab_data'] 			    = $this->language->get('tab_data');

		$data['tab_seller'] 		    = $this->language->get('tab_seller');
		$data['tab_generalstore'] 		= $this->language->get('tab_generalstore');
		$data['tab_datastore'] 			= $this->language->get('tab_datastore');
		$data['tab_payment'] 			= $this->language->get('tab_payment');
		$data['tab_seo'] 			    = $this->language->get('tab_seo');
		$data['help_product'] = $this->language->get('help_product');

		$data['button_submit'] 	= $this->language->get('button_submit');
		$data['button_upload'] 		= $this->language->get('button_upload');

		// 04 03 2019 //
		$data['tab_chat'] 				= $this->language->get('tab_chat');
		$data['entry_status'] 			= $this->language->get('entry_status');
		$data['text_yes'] 			= $this->language->get('text_yes');
		$data['text_no'] 			= $this->language->get('text_no');

		// 04 03 2019 //

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		/* <!--04 05 2020-->  */
		if (isset($this->error['error_showorderwarning'])) {
			$data['error_showorderwarning'] = $this->error['error_showorderwarning'];
		} else {
			$data['error_showorderwarning'] = '';
		}
		/* <!--04 05 2020-->  */

		if (isset($this->error['display_name'])) {
			$data['error_display_name'] = $this->error['display_name'];
		} else {
			$data['error_display_name'] = '';
		}

		if (isset($this->error['firstname'])) {
			$data['error_firstname'] = $this->error['firstname'];
		} else {
			$data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$data['error_lastname'] = $this->error['lastname'];
		} else {
			$data['error_lastname'] = '';
		}


		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}

		if (isset($this->error['address_1'])) {
			$data['error_address_1'] = $this->error['address_1'];
		} else {
			$data['error_address_1'] = '';
		}

		if (isset($this->error['city'])) {
			$data['error_city'] = $this->error['city'];
		} else {
			$data['error_city'] = '';
		}

		if (isset($this->error['postcode'])) {
			$data['error_postcode'] = $this->error['postcode'];
		} else {
			$data['error_postcode'] = '';
		}

		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}

		if (isset($this->error['zone'])) {
			$data['error_zone'] = $this->error['zone'];
		} else {
			$data['error_zone'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['paypal'])) {
			$data['error_paypal'] = $this->error['paypal'];
		} else {
			$data['error_paypal'] = '';
		}

		if (isset($this->error['bank_account_name'])) {
			$data['error_bank_account_name'] = $this->error['bank_account_name'];
		} else {
			$data['error_bank_account_name'] = '';
		}

		if (isset($this->error['bank_account_number'])) {
			$data['error_bank_account_number'] = $this->error['bank_account_number'];
		} else {
			$data['error_bank_account_number'] = '';
		}

		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = (int)$this->request->post['country_id'];
		} elseif (isset($this->session->data['shipping_address']['country_id'])) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = (int)$this->request->post['zone_id'];
		} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
			$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}

		$this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['action'] = $this->url->link('vendor/edit', '', true);

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$vendor_info = $this->model_vendor_vendor->getVendor($this->vendor->getId());
			//	print_r($vendor_info);die();
		}

		if (isset($this->request->post['display_name'])) {
			$data['display_name'] = $this->request->post['display_name'];
		} elseif (isset($vendor_info)) {
			$data['display_name'] = $vendor_info['display_name'];
		} else {
			$data['display_name'] = '';
		}

		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} elseif (isset($vendor_info)) {
			$data['firstname'] = $vendor_info['firstname'];
		} else {
			$data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} elseif (isset($vendor_info)) {
			$data['lastname'] = $vendor_info['lastname'];
		} else {
			$data['lastname'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (isset($vendor_info)) {
			$data['email'] = $vendor_info['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} elseif (isset($vendor_info)) {
			$data['telephone'] = $vendor_info['telephone'];
		} else {
			$data['telephone'] = '';
		}

		if (isset($this->request->post['gstin'])) {
			$data['gstin'] = $this->request->post['gstin'];
		} elseif (isset($vendor_info)) {
			$data['gstin'] = $vendor_info['gstin'];
		} else {
			$data['gstin'] = '';
		}

		if (isset($this->request->post['company'])) {
			$data['company'] = $this->request->post['company'];
		} elseif (isset($vendor_info)) {
			$data['company'] = $vendor_info['company'];
		} else {
			$data['company'] = '';
		}

		if (isset($this->request->post['address_1'])) {
			$data['address_1'] = $this->request->post['address_1'];
		} elseif (isset($vendor_info)) {
			$data['address_1'] = $vendor_info['address_1'];
		} else {
			$data['address_1'] = '';
		}

		if (isset($this->request->post['address_2'])) {
			$data['address_2'] = $this->request->post['address_2'];
		} elseif (isset($vendor_info)) {
			$data['address_2'] = $vendor_info['address_2'];
		} else {
			$data['address_2'] = '';
		}

		if (isset($this->request->post['city'])) {
			$data['city'] = $this->request->post['city'];
		} elseif (isset($vendor_info)) {
			$data['city'] = $vendor_info['city'];
		} else {
			$data['city'] = '';
		}

		if (isset($this->request->post['postcode'])) {
			$data['postcode'] = $this->request->post['postcode'];
		} elseif (isset($vendor_info)) {
			$data['postcode'] = $vendor_info['postcode'];
		} else {
			$data['postcode'] = '';
		}

		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = $this->request->post['country_id'];
		} elseif (isset($vendor_info)) {
			$data['country_id'] = $vendor_info['country_id'];
		} else {
			$data['country_id'] = '';
		}

		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = $this->request->post['zone_id'];
		} elseif (isset($vendor_info)) {
			$data['zone_id'] = $vendor_info['zone_id'];
		} else {
			$data['zone_id'] = '';
		}

		if (isset($this->request->post['about'])) {
			$data['about'] = $this->request->post['about'];
		} else if (isset($vendor_info)) {
			$data['about'] = $vendor_info['about'];
		} else {
			$data['about'] = '';
		}

		if (isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method'];
		} elseif (!empty($vendor_info)) {
			$data['payment_method'] = $vendor_info['payment_method'];
		} else {
			$data['payment_method'] = 'paypal';
		}

		if (isset($this->request->post['paypal'])) {
			$data['paypal'] = $this->request->post['paypal'];
		} elseif (!empty($vendor_info)) {
			$data['paypal'] = $vendor_info['paypal'];
		} else {
			$data['paypal'] = '';
		}

		if (isset($this->request->post['bank_name'])) {
			$data['bank_name'] = $this->request->post['bank_name'];
		} elseif (!empty($vendor_info)) {
			$data['bank_name'] = $vendor_info['bank_name'];
		} else {
			$data['bank_name'] = '';
		}

		if (isset($this->request->post['bank_branch_number'])) {
			$data['bank_branch_number'] = $this->request->post['bank_branch_number'];
		} elseif (!empty($vendor_info)) {
			$data['bank_branch_number'] = $vendor_info['bank_branch_number'];
		} else {
			$data['bank_branch_number'] = '';
		}

		if (isset($this->request->post['bank_swift_code'])) {
			$data['bank_swift_code'] = $this->request->post['bank_swift_code'];
		} elseif (!empty($vendor_info)) {
			$data['bank_swift_code'] = $vendor_info['bank_swift_code'];
		} else {
			$data['bank_swift_code'] = '';
		}

		if (isset($this->request->post['bank_account_name'])) {
			$data['bank_account_name'] = $this->request->post['bank_account_name'];
		} elseif (!empty($vendor_info)) {
			$data['bank_account_name'] = $vendor_info['bank_account_name'];
		} else {
			$data['bank_account_name'] = '';
		}

		if (isset($this->request->post['bank_account_number'])) {
			$data['bank_account_number'] = $this->request->post['bank_account_number'];
		} elseif (!empty($vendor_info)) {
			$data['bank_account_number'] = $vendor_info['bank_account_number'];
		} else {
			$data['bank_account_number'] = '';
		}

		/* new code */
		if (isset($this->request->post['store_logowidth'])) {
			$data['store_logowidth'] = $this->request->post['store_logowidth'];
		} else if (isset($vendor_info)) {
			$data['store_logowidth'] = $vendor_info['store_logowidth'];
		} else {
			$data['store_logowidth'] = '';
		}

		if (isset($this->request->post['store_logoheight'])) {
			$data['store_logoheight'] = $this->request->post['store_logoheight'];
		} else if (isset($vendor_info)) {
			$data['store_logoheight'] = $vendor_info['store_logoheight'];
		} else {
			$data['store_logoheight'] = '';
		}
		if (isset($this->request->post['store_bannerwidth'])) {
			$data['store_bannerwidth'] = $this->request->post['store_bannerwidth'];
		} else if (isset($vendor_info)) {
			$data['store_bannerwidth'] = $vendor_info['store_bannerwidth'];
		} else {
			$data['store_bannerwidth'] = '1200';
		}

		if (isset($this->request->post['store_bannerheight'])) {
			$data['store_bannerheight'] = $this->request->post['store_bannerheight'];
		} else if (isset($vendor_info)) {
			$data['store_bannerheight'] = $vendor_info['store_bannerheight'];
		} else {
			$data['store_bannerheight'] = '400';
		}

		/* new code */

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} else if (isset($vendor_info['image'])) {
			$data['image'] = $vendor_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');
		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($vendor_info) && is_file(DIR_IMAGE . $vendor_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($vendor_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

        //------ added on 08-04-2025------------ regarding to the signature upload----
        
        if (isset($this->request->post['sign_image'])) {
            $data['sign_image'] = $this->request->post['sign_image'];
        } elseif (isset($vendor_info['sign_image'])) {
            $data['sign_image'] = $vendor_info['sign_image'];
        } else {
            $data['sign_image'] = '';
        }
        
        $this->load->model('tool/image');
        
        if (isset($this->request->post['sign_image']) && is_file(DIR_IMAGE . $this->request->post['sign_image'])) {
            $data['thumb_sign'] = $this->model_tool_image->resize($this->request->post['sign_image'], 100, 100);
        } elseif (!empty($vendor_info) && isset($vendor_info['sign_image']) && is_file(DIR_IMAGE . $vendor_info['sign_image'])) {  // Corrected line
            $data['thumb_sign'] = $this->model_tool_image->resize($vendor_info['sign_image'], 100, 100);
        } else {
            $data['thumb_sign'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }
        //--------------------------------------------------------------------------------

		/// Seller Store ////

		if (isset($this->request->post['store_description'])) {
			$data['store_description'] = $this->request->post['store_description'];
		} elseif (isset($vendor_info)) {
			$data['store_description'] = $this->model_vendor_vendor->getVendorStoreDescriptions($this->vendor->getId());
		} else {
			$data['store_description'] = array();
		}

		if (isset($this->request->post['shipping_charge'])) {
			$data['shipping_charge'] = $this->request->post['shipping_charge'];
		} elseif (isset($vendor_info['shipping_charge'])) {
			$data['shipping_charge'] = $vendor_info['shipping_charge'];
		} else {
			$data['shipping_charge'] = '';
		}

		if (isset($this->request->post['tax_number'])) {
			$data['tax_number'] = $this->request->post['tax_number'];
		} elseif (isset($vendor_info['tax_number'])) {
			$data['tax_number'] = $vendor_info['tax_number'];
		} else {
			$data['tax_number'] = '';
		}

		if (isset($this->request->post['logo'])) {
			$data['logo'] = $this->request->post['logo'];
		} else if (isset($vendor_info['logo'])) {
			$data['logo'] = $vendor_info['logo'];
		} else {
			$data['logo'] = '';
		}
		// 09 06 2018 ///
		if (isset($this->request->post['keyword'])) {
			$data['keyword'] = $this->request->post['keyword'];
		} else if (isset($vendor_info['keyword'])) {
			$data['keyword'] = $vendor_info['keyword'];
		} else {
			$data['keyword'] = '';
		}
		// 09 06 2018 ///

		if (isset($this->request->post['banner'])) {
			$data['banner'] = $this->request->post['banner'];
		} else if (isset($vendor_info['banner'])) {
			$data['banner'] = $vendor_info['banner'];
		} else {
			$data['banner'] = '';
		}

		if (isset($this->request->post['store_about'])) {
			$data['store_about'] = $this->request->post['store_about'];
		} else if (isset($vendor_info['store_about'])) {
			$data['store_about'] = $vendor_info['store_about'];
		} else {
			$data['store_about'] = '';
		}

		/* 05 02 2021 */
		$data['entry_bank_detail'] 	= $this->language->get('entry_bank_detail');

		if (isset($this->request->post['bank_detail'])) {
			$data['bank_detail'] = $this->request->post['bank_detail'];
		} else if (isset($vendor_info['bank_detail'])) {
			$data['bank_detail'] = $vendor_info['bank_detail'];
		} else {
			$data['bank_detail'] = '';
		}
		/* 05 02 2021 */

		if (isset($this->request->post['map_url'])) {
			$data['map_url'] = $this->request->post['map_url'];
		} elseif (isset($vendor_info)) {
			$data['map_url'] = $vendor_info['map_url'];
		} else {
			$data['map_url'] = '';
		}

		if (isset($this->request->post['facebook_url'])) {
			$data['facebook_url'] = $this->request->post['facebook_url'];
		} elseif (isset($vendor_info)) {
			$data['facebook_url'] = $vendor_info['facebook_url'];
		} else {
			$data['facebook_url'] = '';
		}

		if (isset($this->request->post['google_url'])) {
			$data['google_url'] = $this->request->post['google_url'];
		} elseif (isset($vendor_info)) {
			$data['google_url'] = $vendor_info['google_url'];
		} else {
			$data['google_url'] = '';
		}

		$this->load->model('tool/image');
		if (isset($this->request->post['logo']) && is_file(DIR_IMAGE . $this->request->post['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($this->request->post['logo'], 100, 100);
		} elseif (!empty($vendor_info) && is_file(DIR_IMAGE . $vendor_info['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($vendor_info['logo'], 100, 100);
		} else {
			$data['thumb_logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		if (isset($this->request->post['banner']) && is_file(DIR_IMAGE . $this->request->post['banner'])) {
			$data['thumb_banner'] = $this->model_tool_image->resize($this->request->post['banner'], 100, 100);
		} elseif (!empty($vendor_info) && is_file(DIR_IMAGE . $vendor_info['banner'])) {
			$data['thumb_banner'] = $this->model_tool_image->resize($vendor_info['banner'], 100, 100);
		} else {
			$data['thumb_banner'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['vendor_seo_url'])) {
			$data['vendor_seo_url'] = $this->request->post['vendor_seo_url'];
		} elseif (isset($this->request->get['vendor_id'])) {
			$data['vendor_seo_url'] = $this->model_vendor_vendor->getVendorSeoUrls($this->request->get['vendor_id']);
		} else {
			$data['vendor_seo_url'] = array();
		}

		//06  3 2019 //
		$status_info = $this->model_vendor_vendor->getmsg($this->vendor->getId());

		if (isset($this->request->post['chatstatus'])) {
			$data['chatstatus'] = $this->request->post['chatstatus'];
		} elseif (isset($status_info['chatstatus'])) {
			$data['chatstatus'] = $status_info['chatstatus'];
		} else {
			$data['chatstatus'] = '';
		}

		if (isset($this->request->post['message'])) {
			$data['message'] = $this->request->post['message'];
		} elseif (isset($status_info['message'])) {
			$data['message'] = $status_info['message'];
		} else {
			$data['message'] = '';
		}

		//06  3 2019 //

		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		$data['imageurls'] = str_replace('http:', '', HTTP_SERVER);

		$data['column_left'] 	= $this->load->controller('vendor/column_left');
		$data['column_right'] 	= $this->load->controller('common/column_right');
		$data['content_top'] 	= $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] 		= $this->load->controller('vendor/footer');
		$data['header'] 		= $this->load->controller('vendor/header');


		$this->response->setOutput($this->load->view('vendor/edit', $data));
	}

	private function validate()
	{
		if ((utf8_strlen(trim($this->request->post['display_name'])) < 1) || (utf8_strlen(trim($this->request->post['display_name'])) > 32)) {
			$this->error['display_name'] = $this->language->get('error_display_name');
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
			$this->error['address_1'] = $this->language->get('error_address_1');
		}

		if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
			$this->error['city'] = $this->language->get('error_city');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}

		if ($this->request->post['country_id'] == '') {
			$this->error['country'] = $this->language->get('error_country');
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
			$this->error['zone'] = $this->language->get('error_zone');
		}

		foreach ($this->request->post['store_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if ($this->request->post['payment_method'] == 'paypal') {
			if ($this->request->post['paypal'] == '') {
				$this->error['paypal'] = $this->language->get('error_paypal');
			}
		} elseif ($this->request->post['payment_method'] == 'banktransfer') {
			if ($this->request->post['bank_account_name'] == '') {
				$this->error['bank_account_name'] = $this->language->get('error_bank_account_name');
			}

			if ($this->request->post['bank_account_number'] == '') {
				$this->error['bank_account_number'] = $this->language->get('error_bank_account_number');
			}
		}


		if (empty($this->request->post['vendor_seo_url'])) {
			$this->load->model('vendor/seo_url');

			foreach ($this->request->post['vendor_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}

						$seo_urls = $this->model_vendor_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['vendor_id']) || (($seo_url['query'] != 'vendor_id=' . $this->request->get['vendor_id'])))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');

								break;
							}
						}
					}
				}
			}
		}


		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		/* <!--04 05 2020-->  */
		if ($this->error) {
			$this->error['error_showorderwarning'] =  $this->language->get('error_showorderwarning');
		}
		/* <!--04 05 2020-->  */
		return !$this->error;
	}

	public function upload()
	{
		$this->load->language('tool/upload');
		$json = array();
		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
			// Sanitize the filename
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));
			// Validate the filename length
			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}
			// Allowed file extension types
			$allowed = array();
			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
			$filetypes = explode("\n", $extension_allowed);
			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}
			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}
			// Allowed file mime types
			$allowed = array();
			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));
			$filetypes = explode("\n", $mime_allowed);
			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}
			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}
			// Check to see if any PHP files are trying to be uploaded
			$content = file_get_contents($this->request->files['file']['tmp_name']);
			if (preg_match('/\<\?php/i', $content)) {
				$json['error'] = $this->language->get('error_filetype');
			}
			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}
		/* 17-06-2019 */
		if (!$json) {
			$targetDir = DIR_IMAGE . 'catalog/multivendor/' . $this->vendor->getId() . '/';
			$file = $filename;
			$location = $targetDir . $file;
			$location1 = 'catalog/multivendor/' . $this->vendor->getId() . '/' . $file;
			$location2 = 'catalog/multivendor/' . $this->vendor->getId() . '/' . $file;
			move_uploaded_file($this->request->files['file']['tmp_name'], $location);
			$json['location1'] = $location1;
			$json['success'] = $this->language->get('text_upload');
		}
		/* 17-06-2019 */
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	public function getVendorData()
	{
		$this->load->model('vendor/vendor');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

		// Check if vendor_id is provided in the request (for admin/general access)
		if (isset($this->request->get['id'])) {
			$vendor_id = (int)$this->request->get['id'];
			$vendor_info = $this->model_vendor_vendor->getVendorData($vendor_id);

			if ($vendor_info) {
				$response = $this->formatVendorData($vendor_info);
			} else {
				$response = ['error' => 'Vendor not found'];
			}
		} else {
			// Fetch vendor_id from session for logged-in vendor
			$vendor_id = isset($this->session->data['vendor_id']) ? (int)$this->session->data['vendor_id'] : null;

			if ($vendor_id) {
				$vendor_info = $this->model_vendor_vendor->getVendorData($vendor_id);
				$response = $this->formatVendorData($vendor_info);
			} else {
				// Fetch all vendors if no ID is provided
				$vendors = $this->model_vendor_vendor->getVendorData();
				$response = [];

				foreach ($vendors as $vendor) {
					$response[] = $this->formatVendorData($vendor);
				}
			}
		}

		ob_clean();
		$this->response->addHeader('Content-Type: application/json');
		echo json_encode($response);
		exit;
	}

	// Helper function to format vendor data with country and zone names
	private function formatVendorData($vendor_info)
	{
		$this->load->model('vendor/vendor');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

		// Check if vendor_id is provided in the request
		if (isset($this->request->get['id'])) {
			$vendor_id = (int)$this->request->get['id'];
			$vendor_info = $this->model_vendor_vendor->getVendorData($vendor_id);
		} else {
			$vendor_id = isset($this->session->data['vendor_id']) ? (int)$this->session->data['vendor_id'] : null;
			$vendor_info = $vendor_id ? $this->model_vendor_vendor->getVendorData($vendor_id) : null;
		}

		if ($vendor_info) {
			// Fetch country and zone names based on IDs
			$country_info = $this->model_localisation_country->getCountry($vendor_info['country_id']);
			$zone_info = $this->model_localisation_zone->getZone($vendor_info['zone_id']);

			$response = [
				'vendor_id'     => $vendor_info['vendor_id'],
				'firstname'     => $vendor_info['firstname'],
				'lastname'      => $vendor_info['lastname'],
				'display_name'  => $vendor_info['display_name'],
				'email'         => $vendor_info['email'],
				'telephone'     => $vendor_info['telephone'],
				'gstin'         => $vendor_info['gstin'],
				'company'       => $vendor_info['company'],
				'postcode'      => $vendor_info['postcode'],
				'address_1'     => $vendor_info['address_1'],
				'address_2'     => $vendor_info['address_2'],
				'country'       => $country_info ? $country_info['name'] : '',
				'country_id'    => $vendor_info['country_id'],  // Send the ID too for the select dropdown
				'zone'          => $zone_info ? $zone_info['name'] : '',
				'zone_id'       => $vendor_info['zone_id'],  // Send the ID too for the select dropdown
				'city'          => $vendor_info['city']
			];
		} else {
			$response = ['error' => 'No vendor data found'];
		}
	

		ob_clean();
		$this->response->addHeader('Content-Type: application/json');
		echo json_encode($response);
		exit;
	}


	// ----------------------------------------------------------------
	// for sending warehouse data shipway

	public function warehouseSendDataToShipway() {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
	
		$this->load->model('vendor/vendor');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');
	
		ob_clean(); // Clear any existing output before sending JSON
	
		$vendor_id = isset($this->session->data['vendor_id']) ? (int)$this->session->data['vendor_id'] : 0;
		
		if (!$vendor_id) {
			$response = ['error' => 'Vendor ID not found'];
		} else {
			$vendor_info = $this->model_vendor_vendor->getVendorDataByVendorId($vendor_id);
	
			if ($vendor_info) {
				// $country_info = $this->model_localisation_country->getCountry($vendor_info['country_id']);
				$zone_info = $this->model_localisation_zone->getZone($vendor_info['zone_id']);
	
				// Format phone number with Indian country code +91
				$phone_with_country_code = '+91-' . preg_replace('/[^0-9]/', '', $vendor_info['telephone']);
	
				$shipway_data = [
					'title'               => $vendor_info['display_name'],
					'company'             => $vendor_info['company'],
					'contact_person_name' => trim($vendor_info['firstname'] . ' ' . $vendor_info['lastname']),
					'email'               => $vendor_info['email'],
					'phone'               => $phone_with_country_code,
					'phone_print'         => '',
					'address_1'           => $vendor_info['address_1'],
					'address_2'           => $vendor_info['address_2'],
					'city'                => $vendor_info['city'],
					'state'               => $zone_info ? $zone_info['name'] : '',
					'country'             => 'IN',
					'pincode'             => $vendor_info['postcode'],
					'longitude'           => '',
					'latitude'            => '',
					'gst_no'              => $vendor_info['gstin'],
					'fssai_code'          => ''
				];
	
				file_put_contents(DIR_LOGS . 'shipway_payload.log', json_encode($shipway_data, JSON_PRETTY_PRINT));
	
				$username = 'ipshopy1@gmail.com';
				$license_key = '96V1f01z291K02U1jg35s5Sb93gB4QmY';
	
				$shipway_response = $this->warehouseMakeShipwayRequest($shipway_data, $username, $license_key);
				
				// Add this line
                file_put_contents(DIR_LOGS . 'shipway_raw_response.log', json_encode($shipway_response, JSON_PRETTY_PRINT));
	
				// Check response and update the database if warehouse_id is returned
				if (isset($shipway_response['response']['warehouse_response']['warehouse_id'])) {
					$warehouse_id = $shipway_response['response']['warehouse_response']['warehouse_id'];
	
					// Update the vendor table with the Shipway warehouse ID
					$this->model_vendor_vendor->updateShipwayWarehouseId($vendor_id, $warehouse_id);
					$response = ['success' => 'Warehouse ID saved successfully.', 'warehouse_id' => $warehouse_id];
				} else {
					$response = ['error' => 'Failed to store warehouse ID.', 'shipway_response' => $shipway_response];
				}
			} else {
				$response = ['error' => 'Vendor data not found'];
			}
		}
	
		header('Content-Type: application/json');
		echo json_encode($response);
		exit;
	}


	// Function to send data to Shipway API with improved error handling
	private function warehouseMakeShipwayRequest($data, $username, $license_key)
	{
		$api_url = "https://app.shipway.com/api/warehouse/";

		// Log request before sending
		file_put_contents(DIR_LOGS . 'shipway_api_request.log', json_encode($data, JSON_PRETTY_PRINT));

		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL            => $api_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => [
				'Content-Type: application/json'
			],
			CURLOPT_USERPWD        => $username . ":" . $license_key,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => json_encode($data),
		]);

		$response = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($curl);
		curl_close($curl);

		// Log API response for debugging
		file_put_contents(DIR_LOGS . 'shipway_api_response.log', "HTTP Code: $http_code\nResponse: $response\nError: $curl_error\n", FILE_APPEND);

		if ($http_code == 200 && !empty($response)) {
			return ['success' => 'Warehouse data successfully sent to Shipway.', 'response' => json_decode($response, true)];
		} elseif ($curl_error) {
			return ['error' => 'CURL Error: ' . $curl_error];
		} else {
			return ['error' => 'Failed to send data to Shipway.', 'http_code' => $http_code, 'response' => $response];
		}
	}
	
	//added new function to upload declaration--- 11-04-2025--------------------------
	
// 	public function uploaddeclaration() {
// 		$this->load->language('tool/upload');
// 		$json = array();
// 		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
// 			// Sanitize the filename
// 			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));
// 			// Validate the filename length
// 			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
// 				$json['error'] = $this->language->get('error_filename');
// 			}
// 			// Allowed file extension types
// 			$allowed = array();
// 			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
// 			$filetypes = explode("\n", $extension_allowed);
// 			foreach ($filetypes as $filetype) {
// 				$allowed[] = trim($filetype);
// 			}
// 			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
// 				$json['error'] = $this->language->get('error_filetype');
// 			}
// 			// Allowed file mime types
// 			$allowed = array();
// 			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));
// 			$filetypes = explode("\n", $mime_allowed);
// 			foreach ($filetypes as $filetype) {
// 				$allowed[] = trim($filetype);
// 			}
// 			if (!in_array($this->request->files['file']['type'], $allowed)) {
// 				$json['error'] = $this->language->get('error_filetype');
// 			}
// 			// Check to see if any PHP files are trying to be uploaded
// 			$content = file_get_contents($this->request->files['file']['tmp_name']);
// 			if (preg_match('/\<\?php/i', $content)) {
// 				$json['error'] = $this->language->get('error_filetype');
// 			}
// 			// Return any upload error
// 			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
// 				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
// 			}
// 		} else {
// 			$json['error'] = $this->language->get('error_upload');
// 		}
// 		/* 17-06-2019 */
// 		if (!$json) {
// 			// $targetDir = DIR_IMAGE . 'catalog/multivendor/' . $this->vendor->getId() . '/';
// 			$targetDir = DIR_IMAGE . 'catalog/declaration/';
// 			$file = $filename;
// 			$location = $targetDir . $file;
// 			// $location1 = 'catalog/multivendor/' . $this->vendor->getId() . '/' . $file;
// 			$location1 = 'catalog/declaration/'. $file;
// 			// $location2 = 'catalog/multivendor/' . $this->vendor->getId() . '/' . $file;
// 			$location2 ='catalog/declaration/' . $file;
// 			move_uploaded_file($this->request->files['file']['tmp_name'], $location);
// 			$json['location1'] = $location1;
// 			$json['success'] = $this->language->get('text_upload');
// 		}
// 		/* 17-06-2019 */
// 		$this->response->addHeader('Content-Type: application/json');
// 		$this->response->setOutput(json_encode($json));
// 	}
    //------------------------------------------------------------------------------------- 	
    
    public function uploadDeclaration() {
        $this->load->language('tool/upload');
        $json = array();
    
        if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
            $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));
    
            if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
                $json['error'] = $this->language->get('error_filename');
            }
    
            // Check extension
            $allowed = array_map('trim', explode("\n", preg_replace('\r?\n', "\n", $this->config->get('config_file_ext_allowed'))));
            $file_ext = strtolower(substr(strrchr($filename, '.'), 1));
            if (!in_array($file_ext, $allowed)) {
                $json['error'] = $this->language->get('error_filetype');
            }
    
            // Check MIME type
            $allowed_mime = array_map('trim', explode("\n", preg_replace('\r?\n', "\n", $this->config->get('config_file_mime_allowed'))));
            if (!in_array($this->request->files['file']['type'], $allowed_mime)) {
                $json['error'] = $this->language->get('error_filetype');
            }
    
            // Check PHP injection
            $content = file_get_contents($this->request->files['file']['tmp_name']);
            if (preg_match('/\<\?php/i', $content)) {
                $json['error'] = $this->language->get('error_filetype');
            }
    
            if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
            }
        } else {
            $json['error'] = $this->language->get('error_upload');
        }
    
        if (!$json) {
            // Get vendor ID
            $vendor_id = $this->vendor->getId();
    
            // Get manufacturer_id from URL
            if (isset($this->request->get['manufacturer_id']) && (int)$this->request->get['manufacturer_id'] > 0) {
                $manufacturer_id = (int)$this->request->get['manufacturer_id'];
    
                // ✅ Fetch manufacturer name from DB
                $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
    
                if ($query->num_rows) {
                    $manufacturer_name = $query->row['name'];
    
                    // ✅ Sanitize the name
                    $sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($manufacturer_name));
    
                    // ✅ Path setup
                    $upload_dir = DIR_IMAGE . 'catalog/declaration/' . $vendor_id;
    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
    
                    $new_filename = $sanitized_name . '.pdf';
                    $final_path = $upload_dir . '/' . $new_filename;
                    $relative_path = 'catalog/declaration/' . $vendor_id . '/' . $new_filename;
    
                    // ✅ Move file
                    move_uploaded_file($this->request->files['file']['tmp_name'], $final_path);
    
                    // ✅ Return success
                    $json['success'] = $this->language->get('text_upload');
                    $json['location1'] = $relative_path;
                } else {
                    $json['error'] = 'Manufacturer not found in database!';
                }
            } else {
                $json['error'] = 'Manufacturer ID is missing!';
            }
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
