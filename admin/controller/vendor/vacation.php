<?php
class ControllerVendorVacation extends Controller
{
    public function index()
    {
        $this->load->language('vendor/vacation');
        $this->load->model('vendor/vacation');

        // $data['vacations'] = $this->model_vendor_vacation->getAllVacations();


        $data['vacations'] = [];

        $results = $this->model_vendor_vacation->getAllVacations();
        $data['user_token'] = $this->session->data['user_token'];

        foreach ($results as $result) {
            $data['vacations'][] = array(
                'vacation_id' => $result['vacation_id'],
                'vendor_id'   => $result['vendor_id'],
                'start_date'  => $result['start_date'],
                'end_date'    => $result['end_date'],
                'reason'      => $result['reason'],
                'status'      => $result['status'],
                'date_added'  => $result['date_added'],
                'vendor_name' => $result['firstname'] . ' ' . $result['lastname'], // join from oc_vendor
                // 'view'        => $this->url->link('vendor/vacation_form', 'user_token=' . $this->session->data['user_token'] . '&vacation_id=' . $result['vacation_id'], true)
                'view' => $this->url->link(
                    'vendor/vacation_form',
                    'user_token=' . $this->session->data['user_token'] . '&vacation_id=' . $result['vacation_id'],
                    true
                )

                // 'view'        => $this->url->link('vendor/vacation_form', 'user_token=' . $this->session->data['user_token'] . '&vacation_id=' . $result['vacation_id'], true)
            );
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['column_vendor'] = $this->language->get('column_vendor');
        $data['column_start_date'] = $this->language->get('column_start_date');
        $data['column_end_date'] = $this->language->get('column_end_date');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_action'] = $this->language->get('column_action');

        $data['approve'] = $this->url->link('vendor/vacation/approve', 'user_token=' . $this->session->data['user_token'], true);
        $data['reject'] = $this->url->link('vendor/vacation/reject', 'user_token=' . $this->session->data['user_token'], true);

        $data['user_token'] = $this->session->data['user_token'];
        $data['breadcrumbs'] = array(
            array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            ),
            array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('vendor/vacation', 'user_token=' . $this->session->data['user_token'], true)
            )
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('vendor/vacation_list', $data));
    }



    public function approve()
    {
        if (isset($this->request->get['vacation_id'])) {
            $this->load->model('vendor/vacation');
            $this->model_vendor_vacation->updateVacationStatus($this->request->get['vacation_id'], 'Approved');
            $this->session->data['success'] = 'Vacation Approved Successfully!';
        }
        $this->response->redirect($this->url->link('vendor/vacation', '', true));
    }

    public function reject()
    {
        if (isset($this->request->get['vacation_id'])) {
            $this->load->model('vendor/vacation');
            $this->model_vendor_vacation->updateVacationStatus($this->request->get['vacation_id'], 'Rejected');
            $this->session->data['success'] = 'Vacation Rejected Successfully!';
        }
        $this->response->redirect($this->url->link('vendor/vacation', '', true));
        // $this->response->setOutput($this->load->view('vendor/vacation', $data));

    }
    
//     public function autocomplete()
// 	{
// 		$json = array();

// 		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
// 			$this->load->model('catalog/product');
// 			$this->load->model('catalog/option');

// 			if (isset($this->request->get['filter_name'])) {
// 				$filter_name = $this->request->get['filter_name'];
// 			} else {
// 				$filter_name = '';
// 			}

// 			if (isset($this->request->get['filter_model'])) {
// 				$filter_model = $this->request->get['filter_model'];
// 			} else {
// 				$filter_model = '';
// 			}

// 			if (isset($this->request->get['limit'])) {
// 				$limit = $this->request->get['limit'];
// 			} else {
// 				$limit = 5;
// 			}

// 			$filter_data = array(
// 				'filter_name'  => $filter_name,
// 				'filter_model' => $filter_model,
// 				'start'        => 0,
// 				'limit'        => $limit
// 			);

// 			$results = $this->model_catalog_product->getProducts($filter_data);

// 			foreach ($results as $result) {
// 				$option_data = array();

// 				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

// 				foreach ($product_options as $product_option) {
// 					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);

// 					if ($option_info) {
// 						$product_option_value_data = array();

// 						foreach ($product_option['product_option_value'] as $product_option_value) {
// 							$option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);

// 							if ($option_value_info) {
// 								$product_option_value_data[] = array(
// 									'product_option_value_id' => $product_option_value['product_option_value_id'],
// 									'option_value_id'         => $product_option_value['option_value_id'],
// 									'name'                    => $option_value_info['name'],
// 									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
// 									'price_prefix'            => $product_option_value['price_prefix']
// 								);
// 							}
// 						}

// 						$option_data[] = array(
// 							'product_option_id'    => $product_option['product_option_id'],
// 							'product_option_value' => $product_option_value_data,
// 							'option_id'            => $product_option['option_id'],
// 							'name'                 => $option_info['name'],
// 							'type'                 => $option_info['type'],
// 							'value'                => $product_option['value'],
// 							'required'             => $product_option['required']
// 						);
// 					}
// 				}

// 				$json[] = array(
// 					'product_id' => $result['product_id'],
// 					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
// 					'model'      => $result['model'],
// 					'option'     => $option_data,
// 					'price'      => $result['price']
// 				);
// 			}
// 		}

// 		$this->response->addHeader('Content-Type: application/json');
// 		$this->response->setOutput(json_encode($json));
// 	}
	
}
