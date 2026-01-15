<?php
class ControllerExtensionModuleModuleCiwhatsapp extends Controller {
	public function index($setting) {
		if($this->config->get('ciwhatsapp_setting_status')) {
			if(in_array($this->load->controller('extension/ciwhatsapp/getDevice'), (array)$this->config->get('ciwhatsapp_setting_detailpage_device'))) {

				if(in_array($this->config->get('ciwhatsapp_position'), ['column_left', 'column_right'])) {
					$setting['ci_whatsapp_sidebar'] = 'ci_whatsapp_sidebar';
				}

				$data['ciwhatsapp_status'] = $this->config->get('ciwhatsapp_setting_status');

			    $ciwhatsapp_filter = [];

			    // For Online/Offline
			    $ciwhatsapp_filter['apply_layout_status'] = 'detail';

			    // Check Position
			    $ciwhatsapp_filter['apply_layout'] = 'layout_pages';

			    // Add Only Custom Members
				$ciwhatsapp_filter['custom_members'] = (!empty($setting['member']) ? (array)$setting['member'] : []);

				// Load Data
				$data = $this->load->controller('extension/ciwhatsapp/getCodingInspectWhatsapp', $ciwhatsapp_filter);

				$data['layout'] = $setting['design_layout'];
				$data['multi_inspect'] = 'multi_inspect';
				$data['custom_class']  = isset($setting['ci_whatsapp_sidebar']) ? $setting['ci_whatsapp_sidebar'] : 'ci_whastapp_content';

				return $this->load->view('extension/ciwhatsapp_detail', $data);
			}
		}
	}
}
