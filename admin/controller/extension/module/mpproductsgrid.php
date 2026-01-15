<?php
class ControllerExtensionModuleMpproductsgrid extends Controller {
	private $error = array();

	public function install() {

		// Do module install tasks
		$this->load->model('setting/event');
		$code = 'mpproductsgrid';

		$trigger = 'admin/view/common/column_left/before';
		$action = 'extension/module/mpproductsgrid/column_left';
		$this->model_setting_event->addEvent($code, $trigger, $action);
	}

	public function uninstall() {

		// Do module uninstall tasks
		$this->load->model('setting/event');
		$code = 'mpproductsgrid';

		$this->model_setting_event->deleteEventByCode($code);
	}

	public function column_left(&$route, &$data, &$code) {

		$menu = array();

		$this->load->language('extension/module/mpproductsgrid');
		$this->load->model('setting/extension');
		$results = $this->model_setting_extension->getInstalled('module');

		if (!in_array('mpproductsgrid', $results)) {
			return;
		}

		if ($this->user->hasPermission('access', 'extension/module/mpproductsgrid')) {
			$menu = array(
				'name'	   => $this->language->get('heading_title'),
				'href'     => $this->url->link('extension/module/mpproductsgrid', 'user_token=' . $this->session->data['user_token'], true),
				'children' => array()
			);
		}

		if ($menu) {

			foreach ($data['menus'] as $key => $value) {
				if ($value['id'] == 'menu-extension') {
					$data['menus'][$key]['children'][] = $menu;
					break;
				}
			}
		}
	}

	public function index() {
		$this->load->language('extension/module/mpproductsgrid');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			if (!isset($this->request->get['module_id'])) {
				$this->request->post['module_id'] = 0;
				$this->model_setting_module->addModule('mpproductsgrid', $this->request->post);
			} else {
				$this->request->post['module_id'] = $this->request->get['module_id'];
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/mpproductsgrid', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/mpproductsgrid', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/mpproductsgrid', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/mpproductsgrid', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (isset($module_info['name'])) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (isset($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = 0;
		}

		if (isset($this->request->post['pheading_title'])) {
			$data['pheading_title'] = $this->request->post['pheading_title'];
		} elseif (isset($module_info['pheading_title'])) {
			$data['pheading_title'] = $module_info['pheading_title'];
		} else {
			$data['pheading_title'] = array();
		}

		if (isset($this->request->post['show_product_name'])) {
			$data['show_product_name'] = $this->request->post['show_product_name'];
		} elseif (isset($module_info['show_product_name'])) {
			$data['show_product_name'] = $module_info['show_product_name'];
		} else {
			$data['show_product_name'] = 1;
		}

		if (isset($this->request->post['show_product_price'])) {
			$data['show_product_price'] = $this->request->post['show_product_price'];
		} elseif (isset($module_info['show_product_price'])) {
			$data['show_product_price'] = $module_info['show_product_price'];
		} else {
			$data['show_product_price'] = 1;
		}

		if (isset($this->request->post['show_product_description'])) {
			$data['show_product_description'] = $this->request->post['show_product_description'];
		} elseif (isset($module_info['show_product_description'])) {
			$data['show_product_description'] = $module_info['show_product_description'];
		} else {
			$data['show_product_description'] = 0;
		}

		if (isset($this->request->post['product_description_length'])) {
			$data['product_description_length'] = $this->request->post['product_description_length'];
		} elseif (isset($module_info['product_description_length'])) {
			$data['product_description_length'] = $module_info['product_description_length'];
		} else {
			$data['product_description_length'] = 100;
		}

		if (isset($this->request->post['show_product_review'])) {
			$data['show_product_review'] = $this->request->post['show_product_review'];
		} elseif (isset($module_info['show_product_review'])) {
			$data['show_product_review'] = $module_info['show_product_review'];
		} else {
			$data['show_product_review'] = 0;
		}

		if (isset($this->request->post['show_product_additional_images'])) {
			$data['show_product_additional_images'] = $this->request->post['show_product_additional_images'];
		} elseif (isset($module_info['show_product_additional_images'])) {
			$data['show_product_additional_images'] = $module_info['show_product_additional_images'];
		} else {
			$data['show_product_additional_images'] = 0;
		}

		if (isset($this->request->post['show_prevnextbuttons'])) {
			$data['show_prevnextbuttons'] = $this->request->post['show_prevnextbuttons'];
		} elseif (isset($module_info['show_prevnextbuttons'])) {
			$data['show_prevnextbuttons'] = $module_info['show_prevnextbuttons'];
		} else {
			$data['show_prevnextbuttons'] = 0;
		}

		if (isset($this->request->post['show_pagedots'])) {
			$data['show_pagedots'] = $this->request->post['show_pagedots'];
		} elseif (isset($module_info['show_pagedots'])) {
			$data['show_pagedots'] = $module_info['show_pagedots'];
		} else {
			$data['show_pagedots'] = 0;
		}

		if (isset($this->request->post['slider_pauseautoplayonhover'])) {
			$data['slider_pauseautoplayonhover'] = $this->request->post['slider_pauseautoplayonhover'];
		} elseif (isset($module_info['slider_pauseautoplayonhover'])) {
			$data['slider_pauseautoplayonhover'] = $module_info['slider_pauseautoplayonhover'];
		} else {
			$data['slider_pauseautoplayonhover'] = 0;
		}

		if (isset($this->request->post['slider_autoplay'])) {
			$data['slider_autoplay'] = $this->request->post['slider_autoplay'];
		} elseif (isset($module_info['slider_autoplay'])) {
			$data['slider_autoplay'] = $module_info['slider_autoplay'];
		} else {
			$data['slider_autoplay'] = 0;
		}

		if (isset($this->request->post['slider_autoplayspeed'])) {
			$data['slider_autoplayspeed'] = $this->request->post['slider_autoplayspeed'];
		} elseif (isset($module_info['slider_autoplayspeed'])) {
			$data['slider_autoplayspeed'] = $module_info['slider_autoplayspeed'];
		} else {
			$data['slider_autoplayspeed'] = 1500;
		}

		if (isset($this->request->post['filter_display'])) {
			$data['filter_display'] = $this->request->post['filter_display'];
		} elseif (isset($module_info['filter_display'])) {
			$data['filter_display'] = $module_info['filter_display'];

		} else {
			$data['filter_display'] = 2;
		}

		if (isset($this->request->post['grid_category'])) {
			$grid_categories = $this->request->post['grid_category'];
		} elseif (isset($module_info['grid_category'])) {
			$grid_categories = $module_info['grid_category'];
		} else {
			$grid_categories = array();
		}
		
		if (isset($this->request->post['grid_categoryproducts'])) {
			$grid_categoryproducts = $this->request->post['grid_categoryproducts'];
		} elseif (isset($module_info['grid_categoryproducts'])) {
			$grid_categoryproducts = $module_info['grid_categoryproducts'];
		} else {
			$grid_categoryproducts = array();
		}

		$data['grid_categories'] = array();
		$data['category_products'] = array();

		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		foreach ($grid_categories as $category_id) {
			
			$categoryinfo = $this->model_catalog_category->getCategory($category_id);

			if (!empty($categoryinfo)) {
				$highlightproducts = array();

				$categoryproducts = $this->model_catalog_product->getProductsByCategoryId($categoryinfo['category_id']);

				foreach ($categoryproducts as $categoryproduct) {
					if (isset($grid_categoryproducts[$categoryinfo['category_id']]['product_id']) && in_array($categoryproduct['product_id'], $grid_categoryproducts[$categoryinfo['category_id']]['product_id'] ) ) {
						$highlightproducts[] = array(
							'product_id' => $categoryproduct['product_id'],
							'name' => $categoryproduct['name'],
						);
					}
				}

				$data['grid_categories'][] = array(
					'category_id' => $categoryinfo['category_id'],
					'name' => $categoryinfo['name'],
					'sort_order' => isset($grid_categoryproducts[$categoryinfo['category_id']]['sort_order']) ? $grid_categoryproducts[$categoryinfo['category_id']]['sort_order'] : 0,
				);
				$data['category_products'][ $categoryinfo['category_id'] ] = array(
					'category_id' => $categoryinfo['category_id'],
					'name' => $categoryinfo['name'],
					'highlightproducts' => $highlightproducts,
					'status' => isset($grid_categoryproducts[$categoryinfo['category_id']]['status']) ? (int)$grid_categoryproducts[$categoryinfo['category_id']]['status'] : 0,
					'sort_order' => isset($grid_categoryproducts[$categoryinfo['category_id']]['sort_order']) ? (int)$grid_categoryproducts[$categoryinfo['category_id']]['sort_order'] : 0,
				);

			}

		}

		// sort category products by sort order
		$sort_order = array();
		foreach ($data['category_products'] as $key => $value) {
		    
		    $sort_order[$key]  = $value['sort_order'];
		}
		array_multisort($sort_order, SORT_ASC, $data['category_products']);

		$sort_order = array();
		foreach ($data['grid_categories'] as $key => $value) {
		    
		    $sort_order[$key]  = $value['sort_order'];
		}
		array_multisort($sort_order, SORT_ASC, $data['grid_categories']);



		if (isset($this->request->post['grid_manufacturer'])) {
			$grid_manufacturers = $this->request->post['grid_manufacturer'];
		} elseif (!empty($module_info['grid_manufacturer'])) {
			$grid_manufacturers = $module_info['grid_manufacturer'];
		} else {
			$grid_manufacturers = array();
		}
		
		if (isset($this->request->post['grid_manufacturerproducts'])) {
			$grid_manufacturerproducts = $this->request->post['grid_manufacturerproducts'];
		} elseif (!empty($module_info['grid_manufacturerproducts'])) {
			$grid_manufacturerproducts = $module_info['grid_manufacturerproducts'];
		} else {
			$grid_manufacturerproducts = array();
		}



		$data['grid_manufacturers'] = array();
		$data['manufacturer_products'] = array();

		$this->load->model('catalog/manufacturer');
		$this->load->model('extension/mpproductsgrid/product');
		foreach ($grid_manufacturers as $manufacturer_id) {
			
			$manufacturerinfo = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

			if (!empty($manufacturerinfo)) {
				$highlightproducts = array();

				$manufacturerproducts = $this->model_extension_mpproductsgrid_product->getProductsByManufacturerId($manufacturerinfo['manufacturer_id']);

				foreach ($manufacturerproducts as $manufacturerproduct) {
					if (isset($grid_manufacturerproducts[$manufacturerinfo['manufacturer_id']]['product_id']) && in_array($manufacturerproduct['product_id'], $grid_manufacturerproducts[$manufacturerinfo['manufacturer_id']]['product_id'] ) ) {
					$highlightproducts[] = array(
						'product_id' => $manufacturerproduct['product_id'],
						'name' => $manufacturerproduct['name'],
					);
					}
				}

				$data['grid_manufacturers'][] = array(
					'manufacturer_id' => $manufacturerinfo['manufacturer_id'],
					'name' => $manufacturerinfo['name'],
					'sort_order' => isset($grid_manufacturerproducts[$manufacturerinfo['manufacturer_id']]['sort_order']) ? $grid_manufacturerproducts[$manufacturerinfo['manufacturer_id']]['sort_order'] : 0,
				);
				$data['manufacturer_products'][ $manufacturerinfo['manufacturer_id'] ] = array(
					'manufacturer_id' => $manufacturerinfo['manufacturer_id'],
					'name' => $manufacturerinfo['name'],
					'highlightproducts' => $highlightproducts,
					'status' => isset($grid_manufacturerproducts[$manufacturerinfo['manufacturer_id']]['status']) ? (int)$grid_manufacturerproducts[$manufacturerinfo['manufacturer_id']]['status'] : 0,
					'sort_order' => isset($grid_manufacturerproducts[$manufacturerinfo['manufacturer_id']]['sort_order']) ? (int)$grid_manufacturerproducts[$manufacturerinfo['manufacturer_id']]['sort_order'] : 0,
				);

			}

		}
		
		// sort manufacturer products by sort order
		$sort_order = array();
		foreach ($data['manufacturer_products'] as $key => $value) {
		    
		    $sort_order[$key]  = $value['sort_order'];
		}
		array_multisort($sort_order, SORT_ASC, $data['manufacturer_products']);

		$sort_order = array();
		foreach ($data['grid_manufacturers'] as $key => $value) {
		    
		    $sort_order[$key]  = $value['sort_order'];
		}
		array_multisort($sort_order, SORT_ASC, $data['grid_manufacturers']);

		
		if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (isset($module_info['limit'])) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 5;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/mpproductsgrid', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/mpproductsgrid')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}


	public function getProducts() {
		$json = array();
		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_category_id']) || isset($this->request->get['filter_manufacturer_id'])) {
			$this->load->model('extension/mpproductsgrid/product');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_category_id'])) {
				$filter_category_id = $this->request->get['filter_category_id'];
			} else {
				$filter_category_id = '';
			}

			if (isset($this->request->get['filter_manufacturer_id'])) {
				$filter_manufacturer_id = $this->request->get['filter_manufacturer_id'];
			} else {
				$filter_manufacturer_id = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}

			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}

			if (isset($this->request->get['sort'])) {
				$sort = $this->request->get['sort'];
			} else {
				$sort = 'pd.name';
			}

			if (isset($this->request->get['order'])) {
				$order = $this->request->get['order'];
			} else {
				$order = 'ASC';
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_category_id' => $filter_category_id,
				'filter_manufacturer_id' => $filter_manufacturer_id,
				'start'        => ($page - 1 ) * $limit,
				'limit'        => $limit
			);


			$results = $this->model_extension_mpproductsgrid_product->getProducts($filter_data);


			foreach ($results as $result) {
				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
				);
			}

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
