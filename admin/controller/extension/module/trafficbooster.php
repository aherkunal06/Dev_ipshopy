<?php class ControllerExtensionModuleTrafficbooster extends Controller {
        private $error = array();

        public function index() {
                $this->language->load('extension/module/trafficbooster');
                $this->document->setTitle($this->language->get('heading_title'));

         $this->load->model('setting/setting');
		$this->load->model('setting/store');
		
		$this->stores = $this->getStores();
		
                if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
					
				foreach($this->stores as $store) {

				$saveSettings = array();
				
$saveSettings["module_trafficbooster_code"] = $this->request->post["trafficbooster_code"];
				$saveSettings["module_trafficbooster_status"] = $this->request->post["module_trafficbooster_status"];
				$this->model_setting_setting->editSetting('module_trafficbooster', $saveSettings, $store['id']);
			}
			
                        $this->session->data['success'] = $this->language->get('text_success');

                         $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
                }
                
                $data = array();

                $data['heading_title'] = $this->language->get('heading_title');
				$data['text_edit'] = $this->language->get('text_edit');
		        $data['tb_code'] = ($this->config->get('module_trafficbooster_code'));
                $data['trafficbooster_code'] = $this->language->get('trafficbooster_code');
                $data['button_save'] = $this->language->get('button_save');
                $data['button_cancel'] = $this->language->get('button_cancel');
                $data['button_add_module'] = $this->language->get('button_add_module');
                $data['button_remove'] = $this->language->get('button_remove');
                
				
				$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/trafficbooster', 'user_token=' . $this->session->data['user_token'] , true)
		);

                $data['action'] = $this->url->link('extension/module/trafficbooster', 'user_token=' . $this->session->data['user_token'] , true);

                $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
				
				
		if (isset($this->request->post['module_trafficbooster_status'])) {
			$data['module_trafficbooster_status'] = $this->request->post['module_trafficbooster_status'];
		} else {
			$data['module_trafficbooster_status'] = $this->model_setting_setting->getSettingValue('module_trafficbooster_status', 0); //replace 0 with store id
		}
		
				
				$data['header'] = $this->load->controller('common/header');
				$data['column_left'] = $this->load->controller('common/column_left');
				$data['footer'] = $this->load->controller('common/footer');
				$this->response->setOutput($this->load->view('extension/module/trafficbooster', $data));
         }

	private function getStores(){
		$stores = $this->model_setting_store->getStores();

		$storesList = array();

		$storesList[] = array(
			'id'      => '0',
			'name'    => $this->config->get('config_name')
		);

		foreach($stores as $store) {
			$storesList[] = array(
				'id'      => $store['store_id'],
				'name'    => $store['name']
			);
		}

		return $storesList;

	}

}
		
?>