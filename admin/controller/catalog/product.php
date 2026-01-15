<?php
class ControllerCatalogProduct extends Controller {
	private $error = array();
    protected $warranty_categories = ['216', '841', '280', '368']; 
    
	public function index() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/product');

        // Add this line on 27-04-2025----------------------
        $data['user_token'] = $this->session->data['user_token'];
        // Load your view template Add this line on 27-04-2025----------------------
        $this->response->setOutput($this->load->view('catalog/product_form', $data));
        
        // added code for the show user  21-05-2025
		$this->load->model('user/user');
		$user_info = $this->model_user_user->getUser($this->user->getId());
		$this->request->post['added_by'] = $user_info['username'];
        $this->request->post['edited_by'] = $user_info['username'];

		// -===========-----------------------------------
        
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');
		
		$data['categories'] = $this->model_catalog_product->getCategories();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$product_id = $this->model_catalog_product->addProduct($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
// 			faq starts 
			if (isset($this->request->post['product_faq'])) {
				$this->model_catalog_product->saveProductFaqs($product_id, $this->request->post['product_faq']);
			}
// 			faq ends 

        if (!in_array($this->request->post['category_level_1'], $this->warranty_categories)) {
        	// product warranty, return , replacement policy start
			if (isset($this->request->post['replacement_policy'])) {
				$this->model_catalog_product->saveReplacementPolicy($product_id, $this->request->post['replacement_policy']);
			}
			
			if (isset($this->request->post['product_warranty'])) {
				$this->model_catalog_product->saveProductWarranty($product_id, $this->request->post['product_warranty']);
			}

			if (isset($this->request->post['product_return'])) {
				$this->model_catalog_product->saveReturnPolicy($product_id, $this->request->post['product_return']);
			}
		}
			// product warranty, return , replacement policy end
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
// 			// Changes 21/05/2025
			$category_level_1 = isset($this->request->post['category_level_1']) ? (int)$this->request->post['category_level_1'] : 0;
			$category_level_2 = isset($this->request->post['category_level_2']) ? (int)$this->request->post['category_level_2'] : 0;
			$category_level_3 = isset($this->request->post['category_level_3']) ? (int)$this->request->post['category_level_3'] : 0;
			$category_level_4 = isset($this->request->post['category_level_4']) ? (int)$this->request->post['category_level_4'] : 0;
			$category_level_5 = isset($this->request->post['category_level_5']) ? (int)$this->request->post['category_level_5'] : 0;

			if ($this->request->post['vendor_id']) {
				$vendor_id = $this->request->post['vendor_id'];
			}
	
			$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_product_category WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_product_category SET 
				vendor_id = '" . (int)$vendor_id . "',
				product_id = '" . (int)$product_id . "',
				category_level_1 = '" . $category_level_1 . "',
				category_level_2 = '" . $category_level_2 . "',
				category_level_3 = '" . $category_level_3 . "',
				category_level_4 = '" . $category_level_4 . "',
				category_level_5 = '" . $category_level_5 . "'");
				
            // added on the 27-04-2025--------------------------------
            $data['error_length'] = isset($this->error['length']) ? $this->error['length'] : '';
            $data['error_width'] = isset($this->error['width']) ? $this->error['width'] : '';
            $data['error_height'] = isset($this->error['height']) ? $this->error['height'] : '';
            // -----------------------------------------------------------------------------
             // variant start 
            if (isset($this->session->data['variant_data'])) {
				$data = [];
				$data['variant_data'] = $this->session->data['variant_data'];
				$this->response->redirect($this->url->link('catalog/product/add','user_token=' . $this->session->data['user_token'] . $url, true));
			} else {
            // 			variant end
    			$this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
		}
        
        // add this block right here on 27-04-2025----------------
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->error = array();  // Clear errors on page load
        }

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/product');
		
		// added code for the show the user name  21-05-2025
		$this->load->model('user/user');
		$user_info = $this->model_user_user->getUser($this->user->getId());
		$this->request->post['edited_by'] = $user_info['username'];

		// -===========-------------------------------------------
		

		// Add this line
		$data['user_token'] = $this->session->data['user_token'];

		// Load your view template
		$this->response->setOutput($this->load->view('catalog/product_form', $data));
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');
		
		$product_id = $this->request->get['product_id'];

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
		    //  variant edit start 
		    $product_id = $this->request->get['product_id'] ?? 0;
			$variant_name = $this->request->post['variant_name'] ?? '';
		
			$this->model_catalog_product->updateProductVariantName($product_id, $variant_name);
            // 			variant edit end 
			$this->model_catalog_product->editProduct($this->request->get['product_id'], $this->request->post);
			
			$category_level_1 = isset($this->request->post['category_level_1']) ? (int)$this->request->post['category_level_1'] : 0;
			$category_level_2 = isset($this->request->post['category_level_2']) ? (int)$this->request->post['category_level_2'] : 0;
			$category_level_3 = isset($this->request->post['category_level_3']) ? (int)$this->request->post['category_level_3'] : 0;
			$category_level_4 = isset($this->request->post['category_level_4']) ? (int)$this->request->post['category_level_4'] : 0;
			$category_level_5 = isset($this->request->post['category_level_5']) ? (int)$this->request->post['category_level_5'] : 0;
	
			// Delete existing categories for product
			$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_product_category WHERE product_id = '" . (int)$product_id . "'");
	
			// Insert new categories for product
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_product_category SET 
				product_id = '" . (int)$product_id . "',
				category_level_1 = '" . $category_level_1 . "',
				category_level_2 = '" . $category_level_2 . "',
				category_level_3 = '" . $category_level_3 . "',
				category_level_4 = '" . $category_level_4 . "',
				category_level_5 = '" . $category_level_5 . "'");

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
// faq start 
			if (isset($this->request->post['product_faq'])) {
				$this->model_catalog_product->saveProductFaqs($product_id, $this->request->post['product_faq']);
			}
// faq ends
//product warranty return, replacement policy start
		if (!in_array($this->request->post['category_level_1'], $this->warranty_categories)) {
			if (isset($this->request->post['replacement_policy'])) {
			$this->model_catalog_product->saveReplacementPolicy($product_id, $this->request->post['replacement_policy']);
		}
			if (isset($this->request->post['product_warranty'])) {
				$this->model_catalog_product->saveProductWarranty($product_id, $this->request->post['product_warranty']);
			}

			if (isset($this->request->post['product_return'])) {
			$this->model_catalog_product->saveReturnPolicy($product_id, $this->request->post['product_return']);
			}
}

			//product warranty return replacement policy end
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
            // added the changes for the validation on 27-04-2025-------------			
			$data['error_length'] = isset($this->error['length']) ? $this->error['length'] : '';
            $data['error_width'] = isset($this->error['width']) ? $this->error['width'] : '';
            $data['error_height'] = isset($this->error['height']) ? $this->error['height'] : '';
            // -------------------------------------------------------------------
            // 			variant start 
			if (isset($this->session->data['variant_data'])) {
				$data = [];
				$data['variant_data'] = $this->session->data['variant_data'];
				$this->response->redirect($this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . $url, true));
			} else {
			 //   variant end 
			$this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
		}
		// added changes on 27-04-2025--------------------
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
				$this->error = array();  // Clear errors on page load
			}
			
		// Get saved category levels for the product
		$category_levels = $this->model_catalog_product->getProductCategoriesinfo($product_id);

		$data['product_categories'] = array();

		foreach ($category_levels as $category) {
			$data['product_categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name']
			);
		}


		$data['selected_categories'] = $category_levels;
	
		// Prepare category dropdowns based on saved selections
		$data['categories']['level_1'] = $this->model_catalog_product->getCategoriesByParentId(0);
		$data['categories']['level_2'] = !empty($category_levels['level_1']) ? $this->model_catalog_product->getCategoriesByParentId($category_levels['level_1']) : [];
		$data['categories']['level_3'] = !empty($category_levels['level_2']) ? $this->model_catalog_product->getCategoriesByParentId($category_levels['level_2']) : [];
		$data['categories']['level_4'] = !empty($category_levels['level_3']) ? $this->model_catalog_product->getCategoriesByParentId($category_levels['level_3']) : [];
		$data['categories']['level_5'] = !empty($category_levels['level_4']) ? $this->model_catalog_product->getCategoriesByParentId($category_levels['level_4']) : [];
	
		// Load full category name paths for display
		$this->load->model('catalog/category');
		$data['category_paths'] = array(); // To store category paths
		foreach ($category_levels as $level => $category_id) {
			if ($category_id) {
				// Category path fetch karo
				$category_path = $this->model_catalog_product->getCategoryPath($category_id);
				if ($category_path) {
					// Category path ko store karo
					$data['category_paths'][$level] = $category_path;
				}
			}
		}
        
		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->deleteProduct($product_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function copy() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->copyProduct($product_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

        // added for filter on 21-05-2025
		$filter_added_by = isset($this->request->get['filter_added_by']) ? $this->request->get['filter_added_by'] : '';
        $filter_edited_by = isset($this->request->get['filter_edited_by']) ? $this->request->get['filter_edited_by'] : '';

		// =====---

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = '';
		}

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = '';
		}

		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
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

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['copy'] = $this->url->link('catalog/product/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/product/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['products'] = array();

		$filter_data = array(
			'filter_name'	  => $filter_name,
			'filter_model'	  => $filter_model,
			'filter_price'	  => $filter_price,
			'filter_quantity' => $filter_quantity,
			'filter_status'   => $filter_status,
			'sort'            => $sort,
			'order'           => $order,
			// ... added on the 21-05-2025
			'filter_added_by'  => $filter_added_by,
			'filter_edited_by' => $filter_edited_by,
			// ...
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');

		$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

		$results = $this->model_catalog_product->getProducts($filter_data);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$special = false;

			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));

					break;
				}
			}

			$data['products'][] = array(
				'product_id' => $result['product_id'],
				'image'      => $image,
				'name'       => $result['name'],
				'model'      => $result['model'],
				'price'      => $this->currency->format($result['price'], $this->config->get('config_currency')),
				'special'    => $special,
				// added code for show username on 21-05-205
				'added_by'  => $result['added_by'],
                'edited_by' => $result['edited_by'],
                'approved_by' => $result['approved_by'],
                // --------------------------------------
				'quantity'   => $result['quantity'],
				'status'     => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'edit'       => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

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

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		// for filter ===--
			if (isset($this->request->get['filter_added_by'])) {
				$url .= '&filter_added_by=' . urlencode(html_entity_decode($this->request->get['filter_added_by'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_edited_by'])) {
				$url .= '&filter_edited_by=' . urlencode(html_entity_decode($this->request->get['filter_edited_by'], ENT_QUOTES, 'UTF-8'));
			}

		// ============/

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url, true);
		$data['sort_model'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.model' . $url, true);
		$data['sort_price'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.price' . $url, true);
		$data['sort_quantity'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.quantity' . $url, true);
		$data['sort_status'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.status' . $url, true);
		$data['sort_order'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;
		$data['filter_price'] = $filter_price;
		$data['filter_quantity'] = $filter_quantity;
		$data['filter_status'] = $filter_status;
		// for fiter====== on 21-05-2025
		$data['filter_added_by'] = $filter_added_by;
        $data['filter_edited_by'] = $filter_edited_by;

		// ========

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
         unset($this->session->data['variant_data']);
		$this->response->setOutput($this->load->view('catalog/product_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}
		// warranty return replace start 
        $data['error_warranty_duration'] = isset($this->error['warranty_duration']) ? $this->error['warranty_duration'] : '';
        $data['error_return_duration_period'] = isset($this->error['return_duration_period']) ? $this->error['return_duration_period'] : '';
        $data['error_replacement_period'] = isset($this->error['replacement_period']) ? $this->error['replacement_period'] : '';
        $data['error_replacement_reason'] = isset($this->error['replacement_reason']) ? $this->error['replacement_reason'] : '';
        $data['error_replacement_policy'] = isset($this->error['replacement_policy']) ? $this->error['replacement_policy'] : '';
         $data['error_is_warranty'] = isset($this->error['is_warranty']) ? $this->error['is_warranty'] : '';
        $data['error_is_replacable'] = isset($this->error['is_replacable']) ? $this->error['is_replacable'] : '';
        $data['error_is_returnable'] = isset($this->error['is_returnable']) ? $this->error['is_returnable'] : '';
        
        
        
        $data['error_return_policy_details'] = isset($this->error['return_policy_details']) ? $this->error['return_policy_details'] : '';
        $data['error_warranty_description'] = isset($this->error['warranty_description']) ? $this->error['warranty_description'] : '';
        $data['error_replacement_description'] = isset($this->error['replacement_description']) ? $this->error['replacement_description'] : '';

			if (isset($this->request->post['replacement_policy'])) {
				$data['replacement_policy'] = $this->request->post['replacement_policy'];
			} elseif (isset($this->request->get['product_id'])) {
				$data['replacement_policy'] = $this->model_catalog_product->getReplacementPolicy($this->request->get['product_id']);
			} else {
				$data['replacement_policy'] = [
					'is_replacable' => '',
					'replacement_reason' => '',
					'replacement_period' => '',
					'replacement_policy' => '',
					'replacement_description' => ''
				];
			}


			// product warranty starts 
			if (isset($this->request->post['product_warranty'])) {
				$data['product_warranty'] = $this->request->post['product_warranty'];
			} elseif (isset($this->request->get['product_id'])) {
				$data['product_warranty'] = $this->model_catalog_product->getProductWarranty($this->request->get['product_id']);
			} else {
				$data['product_warranty'] = [
					'is_warranty'           => '',
					'warranty_by'           => '',
					'warranty_duration'     => '',
					'warranty_description'  => ''
				];
			}

			//return policy
           if (isset($this->request->post['product_return'])) {
				$data['product_return'] = $this->request->post['product_return'];
			} elseif (isset($this->request->get['product_id'])) {
				$data['product_return'] = $this->model_catalog_product->getReturnPolicy($this->request->get['product_id']);
			} else {
				$data['product_return'] = [
					'is_returnable' => '',
					'return_duration_period' => '',
					'return_policy_details' => ''
				];
			}



		// product warranty return  replacement ends
    // variation start 
    	if (isset($this->error['variant_name'])) {
    			$data['error_variant_name'] = $this->error['variant_name'];
    		} else {
    			$data['error_variant_name'] = array();
    		}
    // variation end
    // faq start 
    if (isset($this->request->post['product_faq'])) {
    $data['product_faqs'] = $this->request->post['product_faq'];
    } elseif (isset($this->request->get['product_id'])) {
        $data['product_faqs'] = $this->model_catalog_product->getProductFaqs($this->request->get['product_id']);
    } else {
        $data['product_faqs'] = [];
    }
    // faq end 
		if (isset($this->error['model'])) {
			$data['error_model'] = $this->error['model'];
		} else {
			$data['error_model'] = '';
		}
		
		// For per-row image required error added on 19-04-2025 -----------------------------

		if (isset($this->error['image'])) {
			$data['error_image'] = $this->error['image'];
		} else {
			$data['error_image'] = '';
		}

      
        // if (isset($this->error['product_image'])) {
        //     $data['error_product_image'] = $this->error['product_image'];
        // } else {
        //     $data['error_product_image'] = [];
        // }
        // ---------------------------------------------------
        // added changes on 27-04-2025
        if (isset($this->error['price'])) {
			$data['error_price'] = $this->error['price'];
		} else {
			$data['error_price'] = '';
		}
		
        if (isset($this->error['hsn_code'])) {
			$data['error_hsn_code'] = $this->error['hsn_code'];
		} else {
			$data['error_hsn_code'] = '';
		}

		if (isset($this->error['gst_rate'])) {
			$data['error_gst_rate'] = $this->error['gst_rate'];
		} else {
			$data['error_gst_rate'] = '';
		}
		
		if (isset($this->error['length'])) {
			$data['error_length'] = $this->error['length'];
		} else {
			$data['error_length'] = '';
		}

		if (isset($this->error['width'])) {
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
		}

		if (isset($this->error['weight'])) {
			$data['error_weight'] = $this->error['weight'];
		} else {
			$data['error_weight'] = '';
		}
		
		if (isset($this->error['vendor'])) {
			$data['error_vendor'] = $this->error['vendor'];
		} else {
			$data['error_vendor'] = '';
		}
		

    	if(isset($this->error['manufacturer'])){
    
    		$data['error_manufacturer'] = $this->error['manufacturer'];
    	}else{
    		$data['error_manufacturer'] = '';
    	}



        // -----------------------------------------------------------
        
        
        // For dynamic "select more image(s)" updated on message 30-04-2025
        // if (isset($this->error['dynamic_image_count'])) {
        //     $data['error_dynamic_image_count'] = $this->error['dynamic_image_count'];
        // } else {
        //     $data['error_dynamic_image_count'] = '';
        // }
        // -----------------------------------------------------------------------------

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['product_id'])) {
			$data['action'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['variants']=true;
		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
			// variant edit start 
    	   $data['product_variants'] = $this->model_catalog_product->getProductsVariants($this->request->get['product_id']);
    	   unset($this->session->data['variant_data']);
    	   if(!empty($data['product_variants'])){
    	       
        	   foreach ($data['product_variants'] as $oldVariant) {
        	       if($oldVariant['variant_name']!=''){
        	           
        	       
                    if ($this->request->get['product_id'] == $oldVariant['product_id']) {
                      
                        $getVariantName=$oldVariant['variant_name'];
                    }
                    $this->session->data['variant_data'][] = [
                        'variant_name' => $oldVariant['variant_name'],
                        'product_id'   => $oldVariant['product_id'] ? $oldVariant['product_id'] : 0
                        ];
                    $data['variants']=true;
            	   }else{
            	    $data['variants']=false;
            	   }
        	   }
    	   
    	   }else{
    	       
            $data['variants']=false;
    	   }
            //  $data['current_product_id'] = isset($this->request->get['product_id']) ? $this->request->get['product_id']:0;
	   
	   
	   //variant edit end 
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['product_description'])) {
			$data['product_description'] = $this->request->post['product_description'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_description'] = $this->model_catalog_product->getProductDescriptions($this->request->get['product_id']);
		} else {
			$data['product_description'] = array();
		}

		if (isset($this->request->post['model'])) {
			$data['model'] = $this->request->post['model'];
		} elseif (!empty($product_info)) {
			$data['model'] = $product_info['model'];
		} else {
			$data['model'] = '';
		}
// variant start
		if (isset($this->request->post['variant_name'])) {
			$data['variant_name'] = $this->request->post['variant_name'];
		} elseif (isset($this->request->get['product_id'])){
		    $data['variant_name'] = $getVariantName;
		}else {
			$data['variant_name'] = '';
		}
// variant end
		if (isset($this->request->post['sku'])) {
			$data['sku'] = $this->request->post['sku'];
		} elseif (!empty($product_info)) {
			$data['sku'] = $product_info['sku'];
		} else {
			$data['sku'] = '';
		}

		if (isset($this->request->post['upc'])) {
			$data['upc'] = $this->request->post['upc'];
		} elseif (!empty($product_info)) {
			$data['upc'] = $product_info['upc'];
		} else {
			$data['upc'] = '';
		}

		if (isset($this->request->post['ean'])) {
			$data['ean'] = $this->request->post['ean'];
		} elseif (!empty($product_info)) {
			$data['ean'] = $product_info['ean'];
		} else {
			$data['ean'] = '';
		}

		if (isset($this->request->post['jan'])) {
			$data['jan'] = $this->request->post['jan'];
		} elseif (!empty($product_info)) {
			$data['jan'] = $product_info['jan'];
		} else {
			$data['jan'] = '';
		}

		if (isset($this->request->post['isbn'])) {
			$data['isbn'] = $this->request->post['isbn'];
		} elseif (!empty($product_info)) {
			$data['isbn'] = $product_info['isbn'];
		} else {
			$data['isbn'] = '';
		}

		if (isset($this->request->post['mpn'])) {
			$data['mpn'] = $this->request->post['mpn'];
		} elseif (!empty($product_info)) {
			$data['mpn'] = $product_info['mpn'];
		} else {
			$data['mpn'] = '';
		}

		if (isset($this->request->post['location'])) {
			$data['location'] = $this->request->post['location'];
		} elseif (!empty($product_info)) {
			$data['location'] = $product_info['location'];
		} else {
			$data['location'] = '';
		}
		
		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} elseif (!empty($product_info)) {
			$data['price'] = $product_info['price'];
		} else {
			$data['price'] = '';
		}
        
        // added on 27-04-2025
        // HSN Code
        if (isset($this->request->post['hsn_code'])) {
            $data['hsn_code'] = $this->request->post['hsn_code'];
        } elseif (!empty($product_info)) {
            $data['hsn_code'] = $product_info['hsn_code'];
        } else {
            $data['hsn_code'] = '';
        }
        
        // GST Rate
        if (isset($this->request->post['gst_rate'])) {
            $data['gst_rate'] = $this->request->post['gst_rate'];
        } elseif (!empty($product_info)) {
            $data['gst_rate'] = $product_info['gst_rate'];
        } else {
            $data['gst_rate'] = '';
        }

        // Handling product fields (model, price, etc.)
        // $data['model'] = isset($this->request->post['model']) ? $this->request->post['model'] : (isset($product_info['model']) ? $product_info['model'] : '');
        // $data['price'] = isset($this->request->post['price']) ? $this->request->post['price'] : (isset($product_info['price']) ? $product_info['price'] : '');
        // $data['hsn_code'] = isset($this->request->post['hsn_code']) ? $this->request->post['hsn_code'] : (isset($product_info['hsn_code']) ? $product_info['hsn_code'] : '');
        // $data['gst_rate'] = isset($this->request->post['gst_rate']) ? $this->request->post['gst_rate'] : (isset($product_info['gst_rate']) ? $product_info['gst_rate'] : '');
        // $data['length'] = isset($this->request->post['length']) ? $this->request->post['length'] : (isset($product_info['length']) ? $product_info['length'] : '');
        // $data['width'] = isset($this->request->post['width']) ? $this->request->post['width'] : (isset($product_info['width']) ? $product_info['width'] : '');
        // $data['height'] = isset($this->request->post['height']) ? $this->request->post['height'] : (isset($product_info['height']) ? $product_info['height'] : '');
        // $data['weight'] = isset($this->request->post['weight']) ? $this->request->post['weight'] : (isset($product_info['weight']) ? $product_info['weight'] : '');
        
        // $data['user_token'] = $this->session->data['user_token'];
        
        // $this->response->setOutput($this->load->view('catalog/product_form', $data));

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

		if (isset($this->request->post['product_store'])) {
			$data['product_store'] = $this->request->post['product_store'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_store'] = $this->model_catalog_product->getProductStores($this->request->get['product_id']);
		} else {
			$data['product_store'] = array(0);
		}

		if (isset($this->request->post['shipping'])) {
			$data['shipping'] = $this->request->post['shipping'];
		} elseif (!empty($product_info)) {
			$data['shipping'] = $product_info['shipping'];
		} else {
			$data['shipping'] = 1;
		}

		
        
        // -------------------------------------------------------------------
		$this->load->model('catalog/recurring');

		$data['recurrings'] = $this->model_catalog_recurring->getRecurrings();

		if (isset($this->request->post['product_recurrings'])) {
			$data['product_recurrings'] = $this->request->post['product_recurrings'];
		} elseif (!empty($product_info)) {
			$data['product_recurrings'] = $this->model_catalog_product->getRecurrings($product_info['product_id']);
		} else {
			$data['product_recurrings'] = array();
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['tax_class_id'])) {
			$data['tax_class_id'] = $this->request->post['tax_class_id'];
		} elseif (!empty($product_info)) {
			$data['tax_class_id'] = $product_info['tax_class_id'];
		} else {
			$data['tax_class_id'] = 0;
		}

		if (isset($this->request->post['date_available'])) {
			$data['date_available'] = $this->request->post['date_available'];
		} elseif (!empty($product_info)) {
			$data['date_available'] = ($product_info['date_available'] != '0000-00-00') ? $product_info['date_available'] : '';
		} else {
			$data['date_available'] = date('Y-m-d');
		}

		if (isset($this->request->post['quantity'])) {
			$data['quantity'] = $this->request->post['quantity'];
		} elseif (!empty($product_info)) {
			$data['quantity'] = $product_info['quantity'];
		} else {
			$data['quantity'] = 1;
		}
		


		if (isset($this->request->post['minimum'])) {
			$data['minimum'] = $this->request->post['minimum'];
		} elseif (!empty($product_info)) {
			$data['minimum'] = $product_info['minimum'];
		} else {
			$data['minimum'] = 1;
		}

		if (isset($this->request->post['subtract'])) {
			$data['subtract'] = $this->request->post['subtract'];
		} elseif (!empty($product_info)) {
			$data['subtract'] = $product_info['subtract'];
		} else {
			$data['subtract'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($product_info)) {
			$data['sort_order'] = $product_info['sort_order'];
		} else {
			$data['sort_order'] = 1;
		}
		
		if (isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method'];
		} elseif (!empty($product_info)) {
			$data['payment_method'] = $product_info['payment_method'];
		} else {
			$data['payment_method'] = '';
		}

		$this->load->model('localisation/stock_status');

		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

		if (isset($this->request->post['stock_status_id'])) {
			$data['stock_status_id'] = $this->request->post['stock_status_id'];
		} elseif (!empty($product_info)) {
			$data['stock_status_id'] = $product_info['stock_status_id'];
		} else {
			$data['stock_status_id'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($product_info)) {
			$data['status'] = $product_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['weight'])) {
			$data['weight'] = $this->request->post['weight'];
		} elseif (!empty($product_info)) {
			$data['weight'] = $product_info['weight'];
		} else {
			$data['weight'] = '';
		}
		
		//added on 17-04-2025
		if (isset($this->request->post['volumetric_weight'])) {
			$data['volumetric_weight'] = $this->request->post['volumetric_weight'];
		} elseif (!empty($product_info)) {
			$data['volumetric_weight'] = $product_info['volumetric_weight'];
		} else {
			$data['volumetric_weight'] = '';
		}

		$this->load->model('localisation/weight_class');

		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		if (isset($this->request->post['weight_class_id'])) {
			$data['weight_class_id'] = $this->request->post['weight_class_id'];
		} elseif (!empty($product_info)) {
			$data['weight_class_id'] = $product_info['weight_class_id'];
		} else {
			$data['weight_class_id'] = $this->config->get('config_weight_class_id');
		}

		if (isset($this->request->post['length'])) {
			$data['length'] = $this->request->post['length'];
		} elseif (!empty($product_info)) {
			$data['length'] = $product_info['length'];
		} else {
			$data['length'] = '';
		}

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($product_info)) {
			$data['width'] = $product_info['width'];
		} else {
			$data['width'] = '';
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($product_info)) {
			$data['height'] = $product_info['height'];
		} else {
			$data['height'] = '';
		}

		$this->load->model('localisation/length_class');

		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		if (isset($this->request->post['length_class_id'])) {
			$data['length_class_id'] = $this->request->post['length_class_id'];
		} elseif (!empty($product_info)) {
			$data['length_class_id'] = $product_info['length_class_id'];
		} else {
			$data['length_class_id'] = $this->config->get('config_length_class_id');
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->post['manufacturer_id'])) {
			$data['manufacturer_id'] = $this->request->post['manufacturer_id'];
		} elseif (!empty($product_info)) {
			$data['manufacturer_id'] = $product_info['manufacturer_id'];
		} else {
			$data['manufacturer_id'] = 0;
		}

        //  comment on 15/07/2025 --------
        // 		if (isset($this->request->post['manufacturer'])) {
        // 			$data['manufacturer'] = $this->request->post['manufacturer'];
        // 		} elseif (!empty($product_info)) {
        // 			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);
        
        // 			if ($manufacturer_info) {
        // 				$data['manufacturer'] = $manufacturer_info['name'];
        // 			} else {
        // 				$data['manufacturer'] = '';
        // 			}
        // 		} else {
        // 			$data['manufacturer'] = '';
        // 		}

        
        // Manufacturer name set karna (validate using vendor-manufacturer relation) added changes on 15/07/2025
        
        $this->load->model('catalog/manufacturer');

        $vendor_id = isset($this->request->post['vendor_id'])
            ? (int)$this->request->post['vendor_id']
            : (isset($product_info['vendor_id']) ? (int)$product_info['vendor_id'] : 0);
        
        $manufacturer_id = isset($this->request->post['manufacturer_id'])
            ? (int)$this->request->post['manufacturer_id']
            : (isset($product_info['manufacturer_id']) ? (int)$product_info['manufacturer_id'] : 0);
        
        $data['manufacturer'] = '';
        
        if ($vendor_id && $manufacturer_id) {
            $allowed = $this->model_catalog_manufacturer->getManufacturersByVendor([
                'vendor_id'   => $vendor_id,
                'filter_name' => '',
                'start'       => 0,
                'limit'       => 1000
            ]);
        
            $allowed_ids = array_column($allowed, 'manufacturer_id');
        
            if (in_array($manufacturer_id, $allowed_ids)) {
                foreach ($allowed as $manufacturer) {
                    if ((int)$manufacturer['manufacturer_id'] === $manufacturer_id) {
                        $data['manufacturer'] = $manufacturer['name'];
                        break;
                    }
                }
            } else {
                $this->error['manufacturer'] = 'Selected manufacturer is not linked to the vendor.';
            }
        }
        // --------------- end here---------------------------------------------------
        
		// Categories
		$this->load->model('catalog/category');

		if (isset($this->request->post['product_category'])) {
			$categories = $this->request->post['product_category'];
		} elseif (isset($this->request->get['product_id'])) {
			$categories = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);
		} else {
			$categories = array();
		}

		$data['product_categories'] = array();

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['product_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}

		// Filters
		$this->load->model('catalog/filter');

		if (isset($this->request->post['product_filter'])) {
			$filters = $this->request->post['product_filter'];
		} elseif (isset($this->request->get['product_id'])) {
			$filters = $this->model_catalog_product->getProductFilters($this->request->get['product_id']);
		} else {
			$filters = array();
		}

		$data['product_filters'] = array();

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$data['product_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}

		// Attributes
		$this->load->model('catalog/attribute');

		if (isset($this->request->post['product_attribute'])) {
			$product_attributes = $this->request->post['product_attribute'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_attributes = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
		} else {
			$product_attributes = array();
		}

		$data['product_attributes'] = array();

		foreach ($product_attributes as $product_attribute) {
			$attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

			if ($attribute_info) {
				$data['product_attributes'][] = array(
					'attribute_id'                  => $product_attribute['attribute_id'],
					'name'                          => $attribute_info['name'],
					'product_attribute_description' => $product_attribute['product_attribute_description']
				);
			}
		}

		// Options
		$this->load->model('catalog/option');

		if (isset($this->request->post['product_option'])) {
			$product_options = $this->request->post['product_option'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);
		} else {
			$product_options = array();
		}

		$data['product_options'] = array();

		foreach ($product_options as $product_option) {
			$product_option_value_data = array();

			if (isset($product_option['product_option_value'])) {
				foreach ($product_option['product_option_value'] as $product_option_value) {
					$product_option_value_data[] = array(
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
						'points'                  => $product_option_value['points'],
						'points_prefix'           => $product_option_value['points_prefix'],
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix'],
						'length'                  => $product_option_value['length'],
						'width'                   => $product_option_value['width'],
						'height'                  => $product_option_value['height'],
						'volumetric_weight'       => $product_option_value['volumetric_weight'],
						'mrp'                     => $product_option_value['mrp'],

					);
				}
			}

			$data['product_options'][] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => isset($product_option['value']) ? $product_option['value'] : '',
				'required'             => $product_option['required']
			);
		}

		$data['option_values'] = array();

		foreach ($data['product_options'] as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				if (!isset($data['option_values'][$product_option['option_id']])) {
					$data['option_values'][$product_option['option_id']] = $this->model_catalog_option->getOptionValues($product_option['option_id']);
				}
			}
		}

		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		if (isset($this->request->post['product_discount'])) {
			$product_discounts = $this->request->post['product_discount'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);
		} else {
			$product_discounts = array();
		}

		$data['product_discounts'] = array();

		foreach ($product_discounts as $product_discount) {
			$data['product_discounts'][] = array(
				'customer_group_id' => $product_discount['customer_group_id'],
				'quantity'          => $product_discount['quantity'],
				'max_quantity'      => $product_discount['max_quantity'],
				'priority'          => $product_discount['priority'],
				'price'             => $product_discount['price'],
				'date_start'        => ($product_discount['date_start'] != '0000-00-00') ? $product_discount['date_start'] : '',
				'date_end'          => ($product_discount['date_end'] != '0000-00-00') ? $product_discount['date_end'] : ''
			);
		}

		if (isset($this->request->post['product_special'])) {
			$product_specials = $this->request->post['product_special'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_specials = $this->model_catalog_product->getProductSpecials($this->request->get['product_id']);
		} else {
			$product_specials = array();
		}

		$data['product_specials'] = array();

		foreach ($product_specials as $product_special) {
			$data['product_specials'][] = array(
				'customer_group_id' => $product_special['customer_group_id'],
				'priority'          => $product_special['priority'],
				'price'             => $product_special['price'],
				'date_start'        => ($product_special['date_start'] != '0000-00-00') ? $product_special['date_start'] : '',
				'date_end'          => ($product_special['date_end'] != '0000-00-00') ? $product_special['date_end'] :  ''
			);
		}
		
		// Image
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($product_info)) {
			$data['image'] = $product_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($product_info) && is_file(DIR_IMAGE . $product_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		// Images
		if (isset($this->request->post['product_image'])) {
			$product_images = $this->request->post['product_image'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
		} else {
			$product_images = array();
		}

		$data['product_images'] = array();

		foreach ($product_images as $product_image) {
			if (is_file(DIR_IMAGE . $product_image['image'])) {
				$image = $product_image['image'];
				$thumb = $product_image['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['product_images'][] = array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order' => $product_image['sort_order']
			);
		}

		// Downloads
		$this->load->model('catalog/download');

		if (isset($this->request->post['product_download'])) {
			$product_downloads = $this->request->post['product_download'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_downloads = $this->model_catalog_product->getProductDownloads($this->request->get['product_id']);
		} else {
			$product_downloads = array();
		}

		$data['product_downloads'] = array();

		foreach ($product_downloads as $download_id) {
			$download_info = $this->model_catalog_download->getDownload($download_id);

			if ($download_info) {
				$data['product_downloads'][] = array(
					'download_id' => $download_info['download_id'],
					'name'        => $download_info['name']
				);
			}
		}

		if (isset($this->request->post['product_related'])) {
			$products = $this->request->post['product_related'];
		} elseif (isset($this->request->get['product_id'])) {
			$products = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
		} else {
			$products = array();
		}

		$data['product_relateds'] = array();

		foreach ($products as $product_id) {
			$related_info = $this->model_catalog_product->getProduct($product_id);

			if ($related_info) {
				$data['product_relateds'][] = array(
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				);
			}
		}

		if (isset($this->request->post['points'])) {
			$data['points'] = $this->request->post['points'];
		} elseif (!empty($product_info)) {
			$data['points'] = $product_info['points'];
		} else {
			$data['points'] = '';
		}

		if (isset($this->request->post['product_reward'])) {
			$data['product_reward'] = $this->request->post['product_reward'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_reward'] = $this->model_catalog_product->getProductRewards($this->request->get['product_id']);
		} else {
			$data['product_reward'] = array();
		}

		if (isset($this->request->post['product_seo_url'])) {
			$data['product_seo_url'] = $this->request->post['product_seo_url'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_seo_url'] = $this->model_catalog_product->getProductSeoUrls($this->request->get['product_id']);
		} else {
			$data['product_seo_url'] = array();
		}

		if (isset($this->request->post['product_layout'])) {
			$data['product_layout'] = $this->request->post['product_layout'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_layout'] = $this->model_catalog_product->getProductLayouts($this->request->get['product_id']);
		} else {
			$data['product_layout'] = array();
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		
			// adding courier charges
			if (isset($this->request->post['deliveryOption'])) {
			$data['deliveryOption'] = $this->request->post['deliveryOption'];
		} elseif (!empty($product_info)) {
		    $data['deliveryOption'] = $product_info['delivery_type'];
		}else {
			$data['deliveryOption'] = '';
		}
		if ($data['deliveryOption'] === 'custom') {
			if (isset($this->request->post['pincodeInput'])) {
				$data['pincodeInput'] = $this->request->post['pincodeInput'];
			} elseif (!empty($product_info)) {
		    $data['pincodeInput'] = $product_info['pincodes'];
		}else {
				$data['pincodeInput'] = '';
			}
		}
		if (isset($this->request->post['nationalCharges'])) {
			$data['nationalCharges'] = $this->request->post['nationalCharges'];
		} elseif (!empty($product_info)) {
		    $data['nationalCharges'] = $product_info['national_charges'];
		}else {
			$data['nationalCharges'] = '';
		}
		if (isset($this->request->post['localCharges'])) {
			$data['localCharges'] = $this->request->post['localCharges'];
		} elseif (!empty($product_info)) {
		    $data['localCharges'] = $product_info['local_charges'];
		}else {
			$data['localCharges'] = '';
		}
		if (isset($this->request->post['zonalCharges'])) {
			$data['zonalCharges'] = $this->request->post['zonalCharges'];
		} elseif (!empty($product_info)) {
		    $data['zonalCharges'] = $product_info['zonal_charges'];
		}else {
			$data['zonalCharges'] = '';
		}

		if (isset($this->request->post['courier_free_price'])) {
			$data['courier_free_price'] = $this->request->post['courier_free_price'];
		} elseif (!empty($product_info)) {
		    $data['courier_free_price'] = $product_info['courier_free_price'];
		}else {
			$data['courier_free_price'] = '';
		}
// 		courier charges ends

        $this->loadCategoryLevels($data);

// variant start 
	if (isset($this->session->data['variant_data'])) {

			$data['variant_data'] = $this->session->data['variant_data'];
		}
		if (isset($this->session->data['success'])) {
			$data['success'] = 'variant added Successfully';
		unset($this->session->data['success']);
		} else {
			$data['success'] = null;
		}
// 		var_dump($this->session->data);
// variant end 
		$this->response->setOutput($this->load->view('catalog/product_form', $data));
	}

	protected function validateForm() {
	    
	    // 	  variant start  
	    if (isset($this->session->data['variant_data'])) {

			if ((isset($this->request->post['variant_name'])) && empty($this->request->post['variant_name'])) {
				// var_dump('variant name error', $this->request->post['variant_name']);
				$this->error['variant_name'] = $this->language->get('error_variant_name');
			}else{
			    // Check for duplicate variant_name in session 
			    $duplicate = false;
			     //$this->error['variant_name'] =null;
			    //unset($this->session->data['variant_error']); 
            foreach($this->session->data['variant_data'] as $variant){ 
                if(strtolower($variant['variant_name']) === strtolower($this->request->post['variant_name'])) 
                {
                    $duplicate = true; break; }
                } 
                if (!$duplicate) { 
                    // $this->error['variant_name'] = null; 
                    
                }else{ 
                // Set an error message and prevent further action // $this->session->data['variant_error'] ='This variant name already exists in this session.';
                $this->error['variant_name'] = $this->language->get('error_variant_name_exist');
            }

			}
		}
        // variant end 
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
    
        // added on 27-04-2025------------------------------------
		foreach ($this->request->post['product_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}
        // ----------------------------------------------------------------------------------------
    
        // updated on 27-04-2025------------------------------------------------------------------------
		if ((utf8_strlen($this->request->post['model']) < 3) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}
		
        // added on 27-04-2025-------------------------------------------------		
		if (empty($this->request->post['price'])) {
			$this->error['price'] = $this->language->get('error_price');
		}

		if (empty($this->request->post['hsn_code'])) {
			$this->error['hsn_code'] = $this->language->get('error_hsn_code_required');
		}
		
		if (empty($this->request->post['gst_rate'])) {
			$this->error['gst_rate'] = $this->language->get('error_gst_rate');
		}
		
		if (empty($this->request->post['length'])) {
			$this->error['length'] = $this->language->get('error_length_required');
		}
		
		if (empty($this->request->post['width'])) {
			$this->error['width'] = $this->language->get('error_width_required');
		}
		
		if (empty($this->request->post['height'])) {
			$this->error['height'] = $this->language->get('error_height_required');
		}
		
		if (empty($this->request->post['weight'])) {
			$this->error['weight'] = $this->language->get('error_weight');
		}

		if(!isset($this->request->post['vendor']) || trim($this->request->post['vendor']) == '') {

			$this->error['vendor'] = $this->language->get('error_vendor');
		}

        // commented code on 15/07/2025
        // 		if(!isset($this->request->post['manufacturer']) || trim($this->request->post['manufacturer']) == ''){
        
        // 			$this->error['manufacturer'] = $this->language->get('error_manufacturer');
        // 		}
        
        // added new changes on 15/07/2025 -------------------------------------------------------------
        if (!isset($this->request->post['manufacturer']) || trim($this->request->post['manufacturer']) === '') {
            $this->error['manufacturer'] = $this->language->get('error_manufacturer'); // e.g. 'Manufacturer name is required!'
        }
        
        // Validate that manufacturer_id is actually linked to vendor_id
        if (isset($this->request->post['vendor_id']) && isset($this->request->post['manufacturer_id'])) {
            $vendor_id = (int)$this->request->post['vendor_id'];
            $manufacturer_id = (int)$this->request->post['manufacturer_id'];
        
            $this->load->model('catalog/manufacturer');
        
            $allowed = $this->model_catalog_manufacturer->getManufacturersByVendor([
                'vendor_id' => $vendor_id,
                'filter_name' => '',
                'start' => 0,
                'limit' => 1000
            ]);
        
            $allowed_ids = array_column($allowed, 'manufacturer_id');
        
            if (!in_array($manufacturer_id, $allowed_ids)) {
                $this->error['manufacturer'] = 'Selected manufacturer is not linked to the selected Seller.';
            }
        } else {
            $this->error['manufacturer'] = 'Manufacturer selection is required.';
        }
        
        //------------------------------------------------------------------------------------------------- 
        
        // Shubham Sir Changes - 27/05/2025
        // refurbished 
		if (isset($this->request->post['product_condition'])) {
          $data['product_condition'] = $this->request->post['product_condition'];
        } else {
          $data['product_condition'] = 'New'; // fallback
        }
        
        $data['refurbished_description'] = $this->request->post['refurbished_description'] ?? '';

		// ---------==========
        
       
        
        // added for discount price validation on 01-05-2025
        // === Custom Discount Price Validation ===
		if (isset($this->request->post['product_discount'])) {
			foreach ($this->request->post['product_discount'] as $discount) {
				$discount_price = (float)$discount['price'];
				$special_price = isset($this->request->post['product_special'][0]['price']) ? (float)$this->request->post['product_special'][0]['price'] : null;
				$product_price = (float)$this->request->post['price'];

				if ($special_price !== null) {
					if ($discount_price > $special_price) {
						// $this->error['discount_price'] = 'Discount price cannot be greater than special price.';
						$this->error['warning'] = 'Warning: Discount price cannot be greater than special or base price!';

					}
				} else {
					if ($discount_price > $product_price) {
						$this->error['warning'] = 'Warning: Discount price cannot be greater than special or base price!';
					}
				}
			}
		}

		
        // added on 19-04-2025 ----- regarding to the image validation  --------------------------------------------
        
        // ✅ Main image required
		if (empty($this->request->post['image'])) {
			$this->error['image'] = $this->language->get('error_image');
		}

	
// 		// ✅ Validate additional product images
// 		$selected_images = 0;
	
// 		if (isset($this->request->post['product_image'])) {
// 			foreach ($this->request->post['product_image'] as $index => $image) {
// 				// 1️⃣ Validate: Each image must be selected
// 				if (empty($image['image'])) {
// 					$this->error['product_image'][$index] = $this->language->get('error_image_required');
// 				} else {
// 					$selected_images++;
// 				}
// 			}
	
// 			// 2️⃣ Validate: Total selected images must be exactly 3
// 			if ($selected_images < 3) {
// 				$remaining = 3 - $selected_images;
// 				$this->error['dynamic_image_count'] = sprintf($this->language->get('error_dynamic_image_count'), $remaining);
// 			}
// 		} else {
// 			// No images selected at all
// 			$this->error['dynamic_image_count'] = sprintf($this->language->get('error_dynamic_image_count'), 3);
// 		}
        //-----------------------------------------------------------------------------------------
		
        //------- start here added changes for the error listing 07-05-2025------------------
        
        $field_errors = [];
        $warning_messages = [];
        
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $warning_messages[] = $this->language->get('error_permission');
        }
        
        foreach ($this->request->post['product_description'] as $language_id => $value) {
            if (!isset($value['name']) || utf8_strlen(trim($value['name'])) < 3 || utf8_strlen(trim($value['name'])) > 255) {
                $this->error['name'][$language_id] = $this->language->get('error_name');
                $field_errors[] = ['selector' => "#input-name{$language_id}", 'message' => $this->language->get('error_name')];
                $warning_messages[] = $this->language->get('error_name');
            }
        
            if (!isset($value['meta_title']) || utf8_strlen(trim($value['meta_title'])) < 3 || utf8_strlen(trim($value['meta_title'])) > 255) {
                $this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
                $field_errors[] = ['selector' => "#input-meta-title{$language_id}", 'message' => $this->language->get('error_meta_title')];
                $warning_messages[] = $this->language->get('error_meta_title');
            }
        }
        
        if (!isset($this->request->post['model']) || utf8_strlen(trim($this->request->post['model'])) < 3 || utf8_strlen(trim($this->request->post['model'])) > 64) {
            $this->error['model'] = $this->language->get('error_model');
            $field_errors[] = ['selector' => '#input-model', 'message' => $this->language->get('error_model')];
            $warning_messages[] = $this->language->get('error_model');
        }
        
        if (empty($this->request->post['price'])) {
            $this->error['price'] = $this->language->get('error_price');
            $field_errors[] = ['selector' => '#input-price', 'message' => $this->language->get('error_price')];
            $warning_messages[] = $this->language->get('error_price');
        }
        
        if (empty($this->request->post['hsn_code'])) {
            $this->error['hsn_code'] = $this->language->get('error_hsn_code_required');
            $field_errors[] = ['selector' => '#input-hsn', 'message' => $this->language->get('error_hsn_code_required')];
            $warning_messages[] = $this->language->get('error_hsn_code_required');
        }
        
        if (empty($this->request->post['gst_rate'])) {
            $this->error['gst_rate'] = $this->language->get('error_gst_rate_required');
            $field_errors[] = ['selector' => '#input-gst-rate', 'message' => $this->language->get('error_gst_rate_required')];
            $warning_messages[] = $this->language->get('error_gst_rate_required');
        }
        
        if (empty($this->request->post['length'])) {
            $this->error['length'] = $this->language->get('error_length_required');
            $field_errors[] = ['selector' => '#input-length', 'message' => $this->language->get('error_length_required')];
            $warning_messages[] = $this->language->get('error_length_required');
        }
        
        if (empty($this->request->post['width'])) {
            $this->error['width'] = $this->language->get('error_width_required');
            $field_errors[] = ['selector' => '#input-width', 'message' => $this->language->get('error_width_required')];
            $warning_messages[] = $this->language->get('error_width_required');
        }
        
        if (empty($this->request->post['height'])) {
            $this->error['height'] = $this->language->get('error_height_required');
            $field_errors[] = ['selector' => '#input-height', 'message' => $this->language->get('error_height_required')];
            $warning_messages[] = $this->language->get('error_height_required');
        }
        
        if (empty($this->request->post['weight'])) {
            $this->error['weight'] = $this->language->get('error_weight');
            $field_errors[] = ['selector' => '#input-weight', 'message' => $this->language->get('error_weight')];
            $warning_messages[] = $this->language->get('error_weight');
        }
        
        if (!isset($this->request->post['vendor']) || trim($this->request->post['vendor']) == '') {
            $this->error['vendor'] = $this->language->get('error_vendor');
            $field_errors[] = ['selector' => '#input-vendor', 'message' => $this->language->get('error_vendor')];
            $warning_messages[] = $this->language->get('error_vendor');
        }
        
        if (!isset($this->request->post['manufacturer']) || trim($this->request->post['manufacturer']) == '') {
            $this->error['manufacturer'] = $this->language->get('error_manufacturer');
            $field_errors[] = ['selector' => '#input-manufacturer', 'message' => $this->language->get('error_manufacturer')];
            $warning_messages[] = $this->language->get('error_manufacturer');
        }
        
        
        
        
        // Combine all messages into a single string
        if (!empty($warning_messages)) {
            $this->error['warning'] = implode('<br>', array_unique($warning_messages));
        }
        
        // Pass to view for JavaScript rendering
        $data['field_error_map'] = json_encode($field_errors);
        
        // end here------------------------------------------------------------------------------------------------------

		if ($this->request->post['product_seo_url']) {
			$this->load->model('design/seo_url');
			
			foreach ($this->request->post['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}						
						
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
						
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['product_id']) || (($seo_url['query'] != 'product_id=' . $this->request->get['product_id'])))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
								
								break;
							}
						}
					}
				}
			}
		}
//  warranty return replace start 

		 		$errors = [];

				// === Validate Period Fields ===
				if (isset($this->request->post['product_warranty']['warranty_duration'])) {
					$warranty_duration = (int)$this->request->post['product_warranty']['warranty_duration'];
					if ($warranty_duration > 12) {
						$errors['warranty_duration'] = 'Warranty duration must not exceed 12 months.';
					}
				}

				if (isset($this->request->post['product_return']['return_duration_period'])) {
					$return_duration = (int)$this->request->post['product_return']['return_duration_period'];
					if ($return_duration > 7) {
						$errors['return_duration_period'] = 'Return period must not exceed 7 days.';
					}
				}

					if (isset($this->request->post['replacement_policy']['replacement_period'])) {
						$replacement_period = (int)$this->request->post['replacement_policy']['replacement_period'];
						if ($replacement_period > 7) {
							$errors['replacement_period'] = 'Replacement period must not exceed 7 days.';
						}
					}


				// === Description Keyword Check ===
						$descriptionFields = [
						'product_return' => ['return_policy_details'],
						'product_warranty' => ['warranty_description'],
						'replacement_policy' => [
							'replacement_description',
							'replacement_reason',
							'replacement_policy'
						]
					];

					$restrictedWords = ['ipshopy', 'ipshop', 'ip shopy', 'ip shop', 'this platform', 'platform'];

					foreach ($descriptionFields as $section => $fields) {
						foreach ($fields as $field) {
							if (!empty($this->request->post[$section][$field])) {
								$desc = strtolower($this->request->post[$section][$field]);
								foreach ($restrictedWords as $badWord) {
									if (strpos($desc, $badWord) !== false) {
										$errors[$field] = "Description must not contain: '{$badWord}'";
										break;
									}
								}
							}
						}
					}


				// Add errors to $this->error to pass into view
				foreach ($errors as $key => $msg) {
					$this->error[$key] = $msg;
				}


	
			
					
			if ($this->request->server['REQUEST_METHOD'] == 'POST') {
				if ($this->request->post['replacement_policy']['is_replacable'] == '1') {

					if (empty($this->request->post['replacement_policy']['replacement_reason'])) {
						$this->error['replacement_reason'] = 'Replacement reason is required!';
					}

					if (empty($this->request->post['replacement_policy']['replacement_period'])) {
						$this->error['replacement_period'] = 'Replacement period is required!';
					}

					if (empty($this->request->post['replacement_policy']['replacement_policy'])) {
						$this->error['replacement_policy'] = 'Replacement policy is required!';
					}

					if (empty($this->request->post['replacement_policy']['replacement_description'])) {
						$this->error['replacement_description'] = 'Replacement description is required!';
					}
				}

				if (!$this->error) {
					// Proceed with saving
				} else {
					// Send error to view
					$data['error_replacement_reason'] = $this->error['replacement_reason'] ?? '';
					$data['error_replacement_period'] = $this->error['replacement_period'] ?? '';
					$data['error_replacement_policy'] = $this->error['replacement_policy'] ?? '';
					$data['error_replacement_description'] = $this->error['replacement_description'] ?? '';
				}
			}

			if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			    if (!in_array($this->request->post['category_level_1'], $this->warranty_categories)) {
			    	if (!isset($this->request->post['product_warranty']['is_warranty']) || $this->request->post['product_warranty']['is_warranty'] == '') {
				    
						$this->error['is_warranty'] = 'Warranty is required!';
						
				}
				if (!isset($this->request->post['replacement_policy']['is_replacable']) || $this->request->post['replacement_policy']['is_replacable'] == '') {
				    
						$this->error['is_replacable'] = 'Replacement is required!';
						
				}
				if (!isset($this->request->post['product_return']['is_returnable']) || $this->request->post['product_return']['is_returnable'] == '') {
				    
						$this->error['is_returnable'] = 'Return is required!';
						
				}
				if (isset($this->request->post['product_warranty']['is_warranty']) && $this->request->post['product_warranty']['is_warranty'] == '1') {

					if (empty($this->request->post['product_warranty']['warranty_by'])) {
						$this->error['warranty_by'] = 'Warranty By is required!';
					}

					if (empty($this->request->post['product_warranty']['warranty_duration'])) {
						$this->error['warranty_duration'] = 'Warranty Duration is required!';
					}

					if (empty($this->request->post['product_warranty']['warranty_description'])) {
						$this->error['warranty_description'] = 'Warranty Description is required!';
					}
				}

				if (!$this->error) {
					// Proceed with saving
				} else {
					// Assign error messages to data to be sent to the view
					$data['error_warranty_by']          = $this->error['warranty_by'] ?? '';
					$data['error_warranty_duration']    = $this->error['warranty_duration'] ?? '';
					$data['error_warranty_description'] = $this->error['warranty_description'] ?? '';
				}
			}
				}

				if ($this->request->post['product_return']['is_returnable'] == '1') {
				if (empty($this->request->post['product_return']['return_duration_period'])) {
					$this->error['return_duration_period'] = 'Return duration is required!';
				}

				if (empty($this->request->post['product_return']['return_policy_details'])) {
					$this->error['return_policy_details'] = 'Return policy details are required!';
				}
				}

				if (!empty($this->error)) {
					$data['error_return_duration_period'] = $this->error['return_duration_period'] ?? '';
					$data['error_return_policy_details'] = $this->error['return_policy_details'] ?? '';
				}

//  warranty return replace end
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		
		
		
 // Special price validation: Ensure it is not greater than MRP
    if (isset($this->request->post['product_special'])) {
        foreach ($this->request->post['product_special'] as $special) {
            if ((float)$special['price'] > (float)$this->request->post['price']) {
                $this->error['warning'] = 'Special price cannot be greater than MRP.';
            }
        }
    }

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateCopy() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/option');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);

					if ($option_info) {
						$product_option_value_data = array();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);

							if ($option_value_info) {
								$product_option_value_data[] = array(
									'product_option_value_id' => $product_option_value['product_option_value_id'],
									'option_value_id'         => $product_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $product_option_value['price_prefix']
								);
							}
						}

						$option_data[] = array(
							'product_option_id'    => $product_option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $product_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $product_option['value'],
							'required'             => $product_option['required']
						);
					}
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
    // Added on 27-04-2025-------------------------------
    
    public function fetchGST() {
		$this->load->language('catalog/hsn');
		$json = [];
	
		if (!empty($this->request->post['hsn_code'])) {
			$hsn = $this->request->post['hsn_code'];
	
			$query = $this->db->query("SELECT gst_rate FROM " . DB_PREFIX . "hsn_data WHERE hsn_code = '" . $this->db->escape($hsn) . "'");
	
			if ($query->num_rows) {
				$json['gst_rate'] = $query->row['gst_rate'];
				$json['error'] = '';
			} else {
				$json['gst_rate'] = '';
				$json['error'] = $this->language->get('hsn_error');
			}
		}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    // 	------------------------------------------------------------
    
    //
    // added code changes for the filter username 21-05-2025
	public function autocompleteUsername() {
		$json = [];

		$this->load->model('user/user');

		if (isset($this->request->get['filter_username'])) {
			$filter_username = $this->request->get['filter_username'];
		} else {
			$filter_username = '';
		}

//         if (isset($this->request->get['limit'])) {
// 			$limit = $this->request->get['limit'];
// 		} else {
// 			$limit = 5;
// 		}
			
		$filter_data = [
			'filter_username' => $filter_username,
			'start'           => 0,
			'limit'           => 50
		];

		$results = $this->model_user_user->getUsers($filter_data);

		foreach ($results as $result) {
			$json[] = [
				'user_id'  => $result['user_id'],
				'username' => $result['username']
			];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	//----------------------------------------------------------------------
	
	public function ajaxLoadCategories() {
        // Ensure the user has the correct token to access the request
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->response->addHeader('HTTP/1.1 403 Forbidden');
            return;
        }

        // Validate the user token to prevent unauthorized requests
        if (isset($this->request->get['user_token']) && $this->request->get['user_token'] == $this->session->data['user_token']) {
            // Load the model
            $this->load->model('catalog/product');

            // Fetch categories
            $categories = $this->model_catalog_product->getCategories();

            // Send the response as JSON
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($categories));
        } else {
            // Token mismatch
            $this->response->addHeader('HTTP/1.1 403 Forbidden');
            $this->response->setOutput(json_encode(['error' => 'Invalid user token.']));
        }
    }
    
    protected function loadCategoryLevels(&$data) {
    // Load models
		$this->load->model('catalog/product');

		// Get product_id if editing
		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		// Default selected category levels
		$selected_categories = array(
			'level_1' => '',
			'level_2' => '',
			'level_3' => '',
			'level_4' => '',
			'level_5' => ''
		);

		$data['product_categories'] = array();

		if ($product_id) {
			// Fetch category levels from vendor_product_category
			$query = $this->db->query("
				SELECT 
					vpc.category_level_1,
					vpc.category_level_2,
					vpc.category_level_3,
					vpc.category_level_4,
					vpc.category_level_5,
					cd1.name AS name1
				FROM " . DB_PREFIX . "vendor_product_category vpc
				LEFT JOIN " . DB_PREFIX . "category_description cd1 ON cd1.category_id = vpc.category_level_1 AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'
				WHERE vpc.product_id = '" . (int)$product_id . "'
				LIMIT 1
			");

			if ($query->num_rows) {
				$row = $query->row;

				$selected_categories = array(
					'level_1' => $row['category_level_1'],
					'level_2' => $row['category_level_2'],
					'level_3' => $row['category_level_3'],
					'level_4' => $row['category_level_4'],
					'level_5' => $row['category_level_5']
				);

				// For display: you can add more levels if you want to show all
				if ($row['category_level_1'] && $row['name1']) {
					$data['product_categories'][] = array(
						'category_id' => $row['category_level_1'],
						'name' => $row['name1']
					);
				}
			}
		}

		// Pass selected values to Twig
		$data['selected_categories'] = $selected_categories;

		// Set hidden preselect values
		$data['category_level_1'] = $selected_categories['level_1'];
		$data['category_level_2'] = $selected_categories['level_2'];
		$data['category_level_3'] = $selected_categories['level_3'];
		$data['category_level_4'] = $selected_categories['level_4'];
		$data['category_level_5'] = $selected_categories['level_5'];

		// Load language, header/footer
		$this->load->language('catalog/product');
		$this->document->setTitle($this->language->get('heading_title'));

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		// Render form
		$this->response->setOutput($this->load->view('catalog/product_form', $data));
	}
	
	// added new code for the autocomplete
	public function getManufacturersByVendorId() {
		$this->load->language('catalog/product'); // optional for error handling
	
		$json = [];
	
		if (isset($this->request->get['vendor_id'])) {
			$vendor_id = (int)$this->request->get['vendor_id'];
	        // add the code changes for the autocomplete on 15/07/2025
	        $filter_name = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '';
    
            $this->load->model('catalog/manufacturer');
    
            $filter_data = [
                'vendor_id'    => $vendor_id,
                'filter_name'  => $filter_name,
                'start'        => 0,
                'limit'        => 5
            ];

            $manufacturers = $this->model_catalog_manufacturer->getManufacturersByVendor($filter_data);
	        //-------------end here---------------------------------------------------
            // 	$this->load->model('catalog/manufacturer');
            	
            // 	$manufacturers = $this->model_catalog_manufacturer->getManufacturersByVendor($vendor_id);
	
			foreach ($manufacturers as $manufacturer) {
				$json[] = [
					'manufacturer_id' => $manufacturer['manufacturer_id'],
					'name'            => $manufacturer['name']
				];
			}
		}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}
