<?php
class ControllerExtensionModuleCiwhatsappSetting extends Controller {
	// Trigger for catalog/view/common/footer/before
	public function createFooterWidget(&$route, &$data, &$code) {
		if(!$this->config->get('ciwhatsapp_setting_status') || !$this->config->get('ciwhatsapp_type_d')) {
			return;
		}

		$data['ciwhatsapp_status'] = $this->config->get('ciwhatsapp_setting_status');
	    $data['ciwhatsapp_css'] = $this->config->get('ciwhatsapp_setting_css');
	    $data['ciwhatsapp_color'] = $this->config->get('ciwhatsapp_setting_color');

	    $ciwhatsapp_filter = [];
	    $ciwhatsapp_filter['apply_layout_status'] = 'bottom';
	    $ciwhatsapp_filter['apply_layout'] = 'bottom';

	    $data['ciwhatsapp'] = $this->load->controller('extension/ciwhatsapp/bottom', $ciwhatsapp_filter);

	}

	// Trigger for catalog/view/common/footer/after
	public function addFooterWidget(&$route, &$data, &$output) {
		if(!$this->config->get('ciwhatsapp_setting_status') || !$this->config->get('ciwhatsapp_type_d')) {
			return;
		}

		$find = '</body>';

		$add_string = '';

		if($data['ciwhatsapp']) {
			$add_string .= $data['ciwhatsapp'];
			$add_string .= "\n";
			$add_string .= "\n";
			$add_string .= '<style type="text/css">';
			$add_string .= "\n";
			if($data['ciwhatsapp_color']['theme_background']) {
				$add_string .= '.whstapp_noti, .chat_main .top_chat, .whstapp_noti .web_icon, .whatsapp_products .single_member { background-color: '. $data['ciwhatsapp_color']['theme_background'] .'; }';
			}

			$add_string .= "\n";

			if($data['ciwhatsapp_color']['theme_font']) {
				$add_string .= '.chat_main .top_chat h4, .chat_main .top_chat, .whstapp_noti, .whatsapp_products .user_content h5, .whatsapp_products .user_content h4, .whatsapp_products .user_content p { color: '. $data['ciwhatsapp_color']['theme_font'] .'; }';
			}
		}

		$add_string .= "\n";
		$add_string .= $data['ciwhatsapp_css'];
		$add_string .= "\n";
		$add_string .= '</style>';

		$output = str_replace($find, $add_string ."\n". $find, $output);

	}

	// Trigger for catalog/view/product/product/before
	public function createProductWidget(&$route, &$data, &$code) {
		if(!$this->config->get('ciwhatsapp_setting_status') || !$this->config->get('ciwhatsapp_type_d')) {
			return;
		}

		$data['ciwhatsapp_status'] = $this->config->get('ciwhatsapp_setting_status');

	    $ciwhatsapp_filter = [];
	    $ciwhatsapp_filter['apply_layout_status'] = 'detail';
	    $ciwhatsapp_filter['apply_layout'] = 'product';

	    $data['ciwhatsapp'] = $this->load->controller('extension/ciwhatsapp/product', $ciwhatsapp_filter);

	}

	// Trigger for catalog/view/product/product/after
	public function addProductWidget(&$route, &$data, &$output) {
		if(!$this->config->get('ciwhatsapp_setting_status') || !$this->config->get('ciwhatsapp_type_d')) {
			return;
		}

		$find = '<div id="product"';

		$add_string = '';

		if($data['ciwhatsapp_status']) {
			$add_string .= $data['ciwhatsapp'];
		}

		$output = str_replace($find, $add_string ."\n". $find, $output);
	}


	// Trigger for catalog/model/design/layout/getLayoutModules/after
	public function setPosition(&$route, &$args, &$output) {
		/*
	 	* setPosition, getPosition use to set/get position of layout module in column_left, column_right, content_top, content_bottom
		*/

		if($this->config->get('ciwhatsapp_setting_status')) {
			$this->config->set('ciwhatsapp_position', $args[1]);
		}
	}

}