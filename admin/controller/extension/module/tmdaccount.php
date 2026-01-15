<?php
//lib
require_once(DIR_SYSTEM.'library/tmd/system.php');
//lib
class ControllerExtensionModuleTmdAccount extends Controller {
	private $error = array();

	public function install(){
		$this->load->model('extension/tmdaccount');
		$this->model_extension_tmdaccount->install();
	}
	public function uninstall(){
		$this->load->model('extension/tmdaccount');
		$this->model_extension_tmdaccount->uninstall();
	}

	public function index() {
		$this->document->addScript('view/javascript/bootstrap/js/highlight.js');
		$this->document->addScript('view/javascript/bootstrap/js/bootstrap-switch.js');
		$this->document->addScript('view/javascript/bootstrap/js/main.js');
		$this->document->addStyle('view/javascript/bootstrap/css/bootstrap-switch.css');
		
		$this->document->addScript('view/javascript/colorbox/jquery.minicolors.js');
		$this->document->addScript('view/javascript/summernote/summernote.js');
		$this->document->addScript('view/javascript/summernote/opencart.js');
		$this->document->addScript('view/javascript/dist/js/fontawesome-iconpicker.js');
		$this->document->addStyle('view/stylesheet/jquery.minicolors.css');
		$this->document->addStyle('view/javascript/summernote/summernote.css');
		$this->document->addStyle('view/javascript/dist/css/fontawesome-iconpicker.min.css');
		
		$this->registry->set('tmd', new TMD($this->registry));
		$keydata=array(
		'code'=>'tmdkey_accountdashboard',
		'eid'=>'MzIxNzY=',
		'route'=>'extension/module/tmdaccount',
		);
		$tmdaccount=$this->tmd->getkey($keydata['code']);
		$data['getkeyform']=$this->tmd->loadkeyform($keydata);
		$this->load->language('extension/module/tmdaccount');

		if(isset($this->session->data['token'])){
          $tokenexchange = 'token=' . $this->session->data['token'];
		} else{
		  $tokenexchange='user_token=' . $this->session->data['user_token'];
		}

		$this->document->setTitle($this->language->get('heading_title1'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {


			if(isset($this->request->post['tmdaccount_status'])){
				$status=$this->request->post['tmdaccount_status'];
	
			}

			$postdata['module_tmdaccount_status']=$status;
			$this->model_setting_setting->editSetting('module_tmdaccount',$postdata);

			$this->model_setting_setting->editSetting('tmdaccount', $this->request->post);


             // seo url 
           $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'extension/tmdaccount'");
           if(isset($this->request->post['tmdaccount_seo_url'])) {
            foreach ($this->request->post['tmdaccount_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (!empty($keyword)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'extension/tmdaccount', keyword = '" . $this->db->escape($keyword) . "'");
                    }
                }
            }
            }
          

			$this->session->data['success'] = $this->language->get('text_success');
			
			if(isset($this->request->get['status'])) {
				$this->response->redirect($this->url->link('extension/module/tmdaccount', $tokenexchange, true));
			} else {
				$this->response->redirect($this->url->link('marketplace/extension', $tokenexchange. '&type=module', true));
			}
		}
		
		// Heading
		$data['heading_title'] = $this->language->get('heading_title');

		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['error_keynotfound'])) {
			$data['error_keynotfound'] = $this->error['error_keynotfound'];
		} else {
			$data['error_keynotfound'] = '';
		}				
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $tokenexchange, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', $tokenexchange . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/tmdaccount', $tokenexchange, true)
		);

		$data['action'] = $this->url->link('extension/module/tmdaccount', $tokenexchange, true);

		$data['staysave'] = $this->url->link('extension/module/tmdaccount', '&status=1&'.$tokenexchange, true);

		$data['cancel'] = $this->url->link('marketplace/extension', $tokenexchange . '&type=module', true);
		$data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['tmdaccount_template'])) {
			$data['tmdaccount_template'] = $this->request->post['tmdaccount_template'];
		} else {
			$data['tmdaccount_template'] = $this->config->get('tmdaccount_template');
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['tmdaccount_bgimage'])) {
			$data['tmdaccount_bgimage'] = $this->request->post['tmdaccount_bgimage'];
		}  else {
			$data['tmdaccount_bgimage'] = $this->config->get('tmdaccount_bgimage');;
		}
		//seo
         $this->load->model('extension/tmdaccount');
          if (isset($this->request->post['tmdaccount_seo_url'])) {
            $data['tmdaccount_seo_url'] = $this->request->post['tmdaccount_seo_url'];
          } else{
            $data['tmdaccount_seo_url'] = $this->model_extension_tmdaccount->getSeoUrls('extension/tmdaccount');
          } 
        //seo

		if (isset($this->request->post['tmdaccount_bgimage']) && is_file(DIR_IMAGE . $this->request->post['tmdaccount_bgimage'])) {
			$data['tmdaccount_thumb'] = $this->model_tool_image->resize($this->request->post['tmdaccount_bgimage'], 100, 100);
		} elseif ($this->config->get('tmdaccount_bgimage') && is_file(DIR_IMAGE . $this->config->get('tmdaccount_bgimage'))) {
			$data['tmdaccount_thumb'] = $this->model_tool_image->resize($this->config->get('tmdaccount_bgimage'), 100, 100);
		} else {
			$data['tmdaccount_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		if (isset($this->request->post['tmdaccount_defaultimage'])) {
			$data['tmdaccount_defaultimage'] = $this->request->post['tmdaccount_defaultimage'];
		}  else {
			$data['tmdaccount_defaultimage'] = $this->config->get('tmdaccount_defaultimage');;
		}

		if (isset($this->request->post['tmdaccount_defaultimage']) && is_file(DIR_IMAGE . $this->request->post['tmdaccount_defaultimage'])) {
			$data['tmdaccount_defaultpic'] = $this->model_tool_image->resize($this->request->post['tmdaccount_defaultimage'], 100, 100);
		} elseif ($this->config->get('tmdaccount_defaultimage') && is_file(DIR_IMAGE . $this->config->get('tmdaccount_defaultimage'))) {
			$data['tmdaccount_defaultpic'] = $this->model_tool_image->resize($this->config->get('tmdaccount_defaultimage'), 100, 100);
		} else {
			$data['tmdaccount_defaultpic'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['tmdaccount_bgwidth'])) {
			$data['tmdaccount_bgwidth'] = $this->request->post['tmdaccount_bgwidth'];
		} else {
			$data['tmdaccount_bgwidth'] = $this->config->get('tmdaccount_bgwidth');
		}

		if (isset($this->request->post['tmdaccount_bgheight'])) {
			$data['tmdaccount_bgheight'] = $this->request->post['tmdaccount_bgheight'];
		} else {
			$data['tmdaccount_bgheight'] = $this->config->get('tmdaccount_bgheight');
		}

		if (isset($this->request->post['tmdaccount_defaultwidth'])) {
			$data['tmdaccount_defaultwidth'] = $this->request->post['tmdaccount_defaultwidth'];
		} else {
			$data['tmdaccount_defaultwidth'] = $this->config->get('tmdaccount_defaultwidth');
		}

		if (isset($this->request->post['tmdaccount_defaultheight'])) {
			$data['tmdaccount_defaultheight'] = $this->request->post['tmdaccount_defaultheight'];
		} else {
			$data['tmdaccount_defaultheight'] = $this->config->get('tmdaccount_defaultheight');
		}

		if (isset($this->request->post['tmdaccount_myaccount'])) {
			$data['tmdaccount_myaccount'] = $this->request->post['tmdaccount_myaccount'];
		} else {
			$data['tmdaccount_myaccount'] = $this->config->get('tmdaccount_myaccount');
		}

		if (isset($this->request->post['tmdaccount_editaccount'])) {
			$data['tmdaccount_editaccount'] = $this->request->post['tmdaccount_editaccount'];
		} else {
			$data['tmdaccount_editaccount'] = $this->config->get('tmdaccount_editaccount');
		}

		if (isset($this->request->post['tmdaccount_password'])) {
			$data['tmdaccount_password'] = $this->request->post['tmdaccount_password'];
		} else {
			$data['tmdaccount_password'] = $this->config->get('tmdaccount_password');
		}

		if (isset($this->request->post['tmdaccount_address_book'])) {
			$data['tmdaccount_address_book'] = $this->request->post['tmdaccount_address_book'];
		} else {
			$data['tmdaccount_address_book'] = $this->config->get('tmdaccount_address_book');
		}

		if (isset($this->request->post['tmdaccount_wishlist'])) {
			$data['tmdaccount_wishlist'] = $this->request->post['tmdaccount_wishlist'];
		} else {
			$data['tmdaccount_wishlist'] = $this->config->get('tmdaccount_wishlist');
		}

		if (isset($this->request->post['tmdaccount_newsletter'])) {
			$data['tmdaccount_newsletter'] = $this->request->post['tmdaccount_newsletter'];
		} else {
			$data['tmdaccount_newsletter'] = $this->config->get('tmdaccount_newsletter');
		}

		if (isset($this->request->post['tmdaccount_logout'])) {
			$data['tmdaccount_logout'] = $this->request->post['tmdaccount_logout'];
		} else {
			$data['tmdaccount_logout'] = $this->config->get('tmdaccount_logout');
		}

		if (isset($this->request->post['tmdaccount_order'])) {
			$data['tmdaccount_order'] = $this->request->post['tmdaccount_order'];
		} else {
			$data['tmdaccount_order'] = $this->config->get('tmdaccount_order');
		}

		if (isset($this->request->post['tmdaccount_downloads'])) {
			$data['tmdaccount_downloads'] = $this->request->post['tmdaccount_downloads'];
		} else {
			$data['tmdaccount_downloads'] = $this->config->get('tmdaccount_downloads');
		}

		if (isset($this->request->post['tmdaccount_payments'])) {
			$data['tmdaccount_payments'] = $this->request->post['tmdaccount_payments'];
		} else {
			$data['tmdaccount_payments'] = $this->config->get('tmdaccount_payments');
		}

		if (isset($this->request->post['tmdaccount_reward'])) {
			$data['tmdaccount_reward'] = $this->request->post['tmdaccount_reward'];
		} else {
			$data['tmdaccount_reward'] = $this->config->get('tmdaccount_reward');
		}

		if (isset($this->request->post['tmdaccount_returns'])) {
			$data['tmdaccount_returns'] = $this->request->post['tmdaccount_returns'];
		} else {
			$data['tmdaccount_returns'] = $this->config->get('tmdaccount_returns');
		}

		if (isset($this->request->post['tmdaccount_transaction'])) {
			$data['tmdaccount_transaction'] = $this->request->post['tmdaccount_transaction'];
		} else {
			$data['tmdaccount_transaction'] = $this->config->get('tmdaccount_transaction');
		}

		if (isset($this->request->post['tmdaccount_login'])) {
			$data['tmdaccount_login'] = $this->request->post['tmdaccount_login'];
		} else {
			$data['tmdaccount_login'] = $this->config->get('tmdaccount_login');
		}

		if (isset($this->request->post['tmdaccount_register'])) {
			$data['tmdaccount_register'] = $this->request->post['tmdaccount_register'];
		} else {
			$data['tmdaccount_register'] = $this->config->get('tmdaccount_register');
		}

		if (isset($this->request->post['tmdaccount_forgot'])) {
			$data['tmdaccount_forgot'] = $this->request->post['tmdaccount_forgot'];
		} else {
			$data['tmdaccount_forgot'] = $this->config->get('tmdaccount_forgot');
		}

		if (isset($this->request->post['tmdaccount_totalorders'])) {
			$data['tmdaccount_totalorders'] = $this->request->post['tmdaccount_totalorders'];
		} else {
			$data['tmdaccount_totalorders'] = $this->config->get('tmdaccount_totalorders');
		}

		if (isset($this->request->post['tmdaccount_totalwishlist'])) {
			$data['tmdaccount_totalwishlist'] = $this->request->post['tmdaccount_totalwishlist'];
		} else {
			$data['tmdaccount_totalwishlist'] = $this->config->get('tmdaccount_totalwishlist');
		}

		if (isset($this->request->post['tmdaccount_totalreward'])) {
			$data['tmdaccount_totalreward'] = $this->request->post['tmdaccount_totalreward'];
		} else {
			$data['tmdaccount_totalreward'] = $this->config->get('tmdaccount_totalreward');
		}

		if (isset($this->request->post['tmdaccount_totaldownload'])) {
			$data['tmdaccount_totaldownload'] = $this->request->post['tmdaccount_totaldownload'];
		} else {
			$data['tmdaccount_totaldownload'] = $this->config->get('tmdaccount_totaldownload');
		}

		if (isset($this->request->post['tmdaccount_totaltransaction'])) {
			$data['tmdaccount_totaltransaction'] = $this->request->post['tmdaccount_totaltransaction'];
		} else {
			$data['tmdaccount_totaltransaction'] = $this->config->get('tmdaccount_totaltransaction');
		}

		if (isset($this->request->post['tmdaccount_latestorder'])) {
			$data['tmdaccount_latestorder'] = $this->request->post['tmdaccount_latestorder'];
		} else {
			$data['tmdaccount_latestorder'] = $this->config->get('tmdaccount_latestorder');
		}

		if (isset($this->request->post['tmdaccount_status'])) {
			$data['tmdaccount_status'] = $this->request->post['tmdaccount_status'];
		} else {
			$data['tmdaccount_status'] = $this->config->get('tmdaccount_status');
		}

		//color
		if (isset($this->request->post['tmdaccount_totalorders_bg'])) {
			$data['tmdaccount_totalorders_bg'] = $this->request->post['tmdaccount_totalorders_bg'];
		} else {
			$data['tmdaccount_totalorders_bg'] = $this->config->get('tmdaccount_totalorders_bg');
		}

		if (isset($this->request->post['tmdaccount_totalwishlist_bg'])) {
			$data['tmdaccount_totalwishlist_bg'] = $this->request->post['tmdaccount_totalwishlist_bg'];
		} else {
			$data['tmdaccount_totalwishlist_bg'] = $this->config->get('tmdaccount_totalwishlist_bg');
		}

		if (isset($this->request->post['tmdaccount_totalreward_bg'])) {
			$data['tmdaccount_totalreward_bg'] = $this->request->post['tmdaccount_totalreward_bg'];
		} else {
			$data['tmdaccount_totalreward_bg'] = $this->config->get('tmdaccount_totalreward_bg');
		}

		if (isset($this->request->post['tmdaccount_totaldownload_bg'])) {
			$data['tmdaccount_totaldownload_bg'] = $this->request->post['tmdaccount_totaldownload_bg'];
		} else {
			$data['tmdaccount_totaldownload_bg'] = $this->config->get('tmdaccount_totaldownload_bg');
		}

		if (isset($this->request->post['tmdaccount_totaltransaction_bg'])) {
			$data['tmdaccount_totaltransaction_bg'] = $this->request->post['tmdaccount_totaltransaction_bg'];
		} else {
			$data['tmdaccount_totaltransaction_bg'] = $this->config->get('tmdaccount_totaltransaction_bg');
		}

		if (isset($this->request->post['tmdaccount_latestorder_bg'])) {
			$data['tmdaccount_latestorder_bg'] = $this->request->post['tmdaccount_latestorder_bg'];
		} else {
			$data['tmdaccount_latestorder_bg'] = $this->config->get('tmdaccount_latestorder_bg');
		}

		if (isset($this->request->post['tmdaccount_pbtncolor'])) {
			$data['tmdaccount_pbtncolor'] = $this->request->post['tmdaccount_pbtncolor'];
		} else {
			$data['tmdaccount_pbtncolor'] = $this->config->get('tmdaccount_pbtncolor');
		}


		if (isset($this->request->post['tmdaccount_pbtntextcolor'])) {
			$data['tmdaccount_pbtntextcolor'] = $this->request->post['tmdaccount_pbtntextcolor'];
		} else {
			$data['tmdaccount_pbtntextcolor'] = $this->config->get('tmdaccount_pbtntextcolor');
		}
		
		if (isset($this->request->post['tmdaccount_midbgcolor'])) {
			$data['tmdaccount_midbgcolor'] = $this->request->post['tmdaccount_midbgcolor'];
		} else {
			$data['tmdaccount_midbgcolor'] = $this->config->get('tmdaccount_midbgcolor');
		}

		if (isset($this->request->post['tmdaccount_sidebarbg'])) {
			$data['tmdaccount_sidebarbg'] = $this->request->post['tmdaccount_sidebarbg'];
		} else {
			$data['tmdaccount_sidebarbg'] = $this->config->get('tmdaccount_sidebarbg');
		}

		if (isset($this->request->post['tmdaccount_sidebarcolor'])) {
			$data['tmdaccount_sidebarcolor'] = $this->request->post['tmdaccount_sidebarcolor'];
		} else {
			$data['tmdaccount_sidebarcolor'] = $this->config->get('tmdaccount_sidebarcolor');
		}

		if (isset($this->request->post['tmdaccount_sidebartcolor'])) {
			$data['tmdaccount_sidebartcolor'] = $this->request->post['tmdaccount_sidebartcolor'];
		} else {
			$data['tmdaccount_sidebartcolor'] = $this->config->get('tmdaccount_sidebartcolor');
		}

		if (isset($this->request->post['tmdaccount_sidebarhover'])) {
			$data['tmdaccount_sidebarhover'] = $this->request->post['tmdaccount_sidebarhover'];
		} else {
			$data['tmdaccount_sidebarhover'] = $this->config->get('tmdaccount_sidebarhover');
		}

		if (isset($this->request->post['tmdaccount_sidebarboxhover'])) {
			$data['tmdaccount_sidebarboxhover'] = $this->request->post['tmdaccount_sidebarboxhover'];
		} else {
			$data['tmdaccount_sidebarboxhover'] = $this->config->get('tmdaccount_sidebarboxhover');
		}

		if (isset($this->request->post['tmdaccount_sidebarboxbg'])) {
			$data['tmdaccount_sidebarboxbg'] = $this->request->post['tmdaccount_sidebarboxbg'];
		} else {
			$data['tmdaccount_sidebarboxbg'] = $this->config->get('tmdaccount_sidebarboxbg');
		}

		if (isset($this->request->post['tmdaccount_sidebarleftborder'])) {
			$data['tmdaccount_sidebarleftborder'] = $this->request->post['tmdaccount_sidebarleftborder'];
		} else {
			$data['tmdaccount_sidebarleftborder'] = $this->config->get('tmdaccount_sidebarleftborder');
		}

		if (isset($this->request->post['tmdaccount_sidebaricon'])) {
			$data['tmdaccount_sidebaricon'] = $this->request->post['tmdaccount_sidebaricon'];
		} else {
			$data['tmdaccount_sidebaricon'] = $this->config->get('tmdaccount_sidebaricon');
		}
		
		if (isset($this->request->post['tmdaccount_icon'])) {
			$data['tmdaccounticons'] = $this->request->post['tmdaccount_icon'];
		} else {
			$data['tmdaccounticons'] = $this->config->get('tmdaccount_icon');
		}

		//link account setting
		if (isset($this->request->post['tmdaccount_link_editaccount'])) {
			$data['tmdaccount_link_editaccount'] = $this->request->post['tmdaccount_link_editaccount'];
		} else {
			$data['tmdaccount_link_editaccount'] = $this->config->get('tmdaccount_link_editaccount');
		}

		if (isset($this->request->post['tmdaccount_link_password'])) {
			$data['tmdaccount_link_password'] = $this->request->post['tmdaccount_link_password'];
		} else {
			$data['tmdaccount_link_password'] = $this->config->get('tmdaccount_link_password');
		}

		if (isset($this->request->post['tmdaccount_link_address_book'])) {
			$data['tmdaccount_link_address_book'] = $this->request->post['tmdaccount_link_address_book'];
		} else {
			$data['tmdaccount_link_address_book'] = $this->config->get('tmdaccount_link_address_book');
		}

		if (isset($this->request->post['tmdaccount_link_wishlist'])) {
			$data['tmdaccount_link_wishlist'] = $this->request->post['tmdaccount_link_wishlist'];
		} else {
			$data['tmdaccount_link_wishlist'] = $this->config->get('tmdaccount_link_wishlist');
		}

		if (isset($this->request->post['tmdaccount_link_newsletter'])) {
			$data['tmdaccount_link_newsletter'] = $this->request->post['tmdaccount_link_newsletter'];
		} else {
			$data['tmdaccount_link_newsletter'] = $this->config->get('tmdaccount_link_newsletter');
		}

		if (isset($this->request->post['tmdaccount_link_order'])) {
			$data['tmdaccount_link_order'] = $this->request->post['tmdaccount_link_order'];
		} else {
			$data['tmdaccount_link_order'] = $this->config->get('tmdaccount_link_order');
		}

		if (isset($this->request->post['tmdaccount_link_downloads'])) {
			$data['tmdaccount_link_downloads'] = $this->request->post['tmdaccount_link_downloads'];
		} else {
			$data['tmdaccount_link_downloads'] = $this->config->get('tmdaccount_link_downloads');
		}

		if (isset($this->request->post['tmdaccount_link_reward'])) {
			$data['tmdaccount_link_reward'] = $this->request->post['tmdaccount_link_reward'];
		} else {
			$data['tmdaccount_link_reward'] = $this->config->get('tmdaccount_link_reward');
		}

		if (isset($this->request->post['tmdaccount_link_returns'])) {
			$data['tmdaccount_link_returns'] = $this->request->post['tmdaccount_link_returns'];
		} else {
			$data['tmdaccount_link_returns'] = $this->config->get('tmdaccount_link_returns');
		}

		if (isset($this->request->post['tmdaccount_link_transaction'])) {
			$data['tmdaccount_link_transaction'] = $this->request->post['tmdaccount_link_transaction'];
		} else {
			$data['tmdaccount_link_transaction'] = $this->config->get('tmdaccount_link_transaction');
		}

		if (isset($this->request->post['tmdaccount_link_payments'])) {
			$data['tmdaccount_link_payments'] = $this->request->post['tmdaccount_link_payments'];
		} else {
			$data['tmdaccount_link_payments'] = $this->config->get('tmdaccount_link_payments');
		}

		if (isset($this->request->post['tmdaccount_link_affilate'])) {
			$data['tmdaccount_link_affilate'] = $this->request->post['tmdaccount_link_affilate'];
		} else {
			$data['tmdaccount_link_affilate'] = $this->config->get('tmdaccount_link_affilate');
		}

		if (isset($this->request->post['tmdaccount_custom_css'])) {
			$data['tmdaccount_custom_css'] = $this->request->post['tmdaccount_custom_css'];
		} else {
			$data['tmdaccount_custom_css'] = $this->config->get('tmdaccount_custom_css');
		}

		//label
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['tmdaccount_lable'])) {
			$data['tmdaccount_lable'] = $this->request->post['tmdaccount_lable'];
		} else {
			$data['tmdaccount_lable'] = $this->config->get('tmdaccount_lable');
		}

		$this->load->model('setting/store');

		$data['stores'] = [];

		$data['stores'][] = [
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		];

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = [
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			];
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/tmdaccount', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/tmdaccount')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$tmdaccount=$this->config->get('tmdkey_accountdashboard');
		 if (empty(trim($tmdaccount))) {			
		$this->error['warning'] ='Module will Work after add License key!';
		 }

		return !$this->error;
	}
	
	public function keysubmit() {
		$json = array(); 
		
      	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$keydata=array(
			'code'=>'tmdkey_accountdashboard',
			'eid'=>'MzIxNzY=',
			'route'=>'extension/module/tmdaccount',
			'moduledata_key'=>$this->request->post['moduledata_key'],
			);
			$this->registry->set('tmd', new TMD($this->registry));
            $json=$this->tmd->matchkey($keydata);       
		} 
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}