<?php
class ControllerVendorProduct extends Controller {
	private $error = array();
    protected $warranty_categories = ['216', '841', '280', '368']; 
	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		$this->load->language('vendor/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/product');

// 		$this->getList();
	// ✅ Call custom warehouse check before getList
		if ($this->checkWarehouseBeforeListing()) {
			$this->getList();
		}
		
	}
	

	// ... other methods unchanged ...
	protected function checkWarehouseBeforeListing() {
		$vendor_id = $this->vendor->getId();
	
		$query = $this->db->query("SELECT shipway_warehouse_id, bank_account_number FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . (int)$vendor_id . "'");
	
		$has_warehouse = !empty($query->row['shipway_warehouse_id']);
		$has_bank = !empty($query->row['bank_account_number']);
	
		if (!$has_warehouse || !$has_bank) {
			$data['header'] = $this->load->controller('vendor/header');
			$data['column_left'] = $this->load->controller('vendor/column_left');
			$data['footer'] = $this->load->controller('vendor/footer');
	
			$data['show_access_warning'] = true;
			$data['show_warehouse_button'] = !$has_warehouse;
			$data['show_bank_button'] = !$has_bank;
			$data['warehouse_link'] = $this->url->link('vendor/warehouse/add', '', true);
			$data['bank_link'] = $this->url->link('vendor/bank/add', '', true);
			$data['edit_seller'] = $this->url->link('vendor/edit', 'vendor_id='. $vendor_id, true);	
	
			if (!$has_warehouse && !$has_bank) {
				$data['alert_message'] = 'You haven’t created a Shipway Warehouse or added a bank account yet.';
			} elseif (!$has_warehouse) {
				$data['alert_message'] = 'You haven’t created a Shipway Warehouse yet.';
			} elseif (!$has_bank) {
				$data['alert_message'] = 'Your bank account is not verified. Please add it to upload products.';
			}
	
			$this->response->setOutput($this->load->view('vendor/product_list', $data));
			return false;
		}
	
		return true;
	}
	

	public function add() {
	    $this->load->model('vendor/product'); 
		$this->load->language('vendor/product');
		//product draft start 23/06/25
		

$vendor_id = $this->session->data['vendor_id'] ?? 0;

    // ✅ SAVE AS DRAFT: Detect the draft button
    if (
        $this->request->server['REQUEST_METHOD'] == 'POST' &&
        isset($this->request->post['cancel']) &&
        $this->request->post['cancel'] == '1'
    ) {
        // Never save product_id in draft to avoid confusion
        unset($this->request->post['product_id']);

        $this->model_vendor_product->saveProductDraft($vendor_id, $this->request->post);
        $this->session->data['success'] = 'Draft saved successfully.';

        $this->response->redirect($this->url->link('vendor/product', 'user_token=' . $this->session->data['user_token'], true));
        return; // 🛑 CRITICAL: Prevent saving to oc_product
    }

    // ✅ LOAD DRAFT: Only on GET request
    if ($this->request->server['REQUEST_METHOD'] != 'POST') {
        $draftData = $this->model_vendor_product->getProductDraft($vendor_id);

        if ($draftData) {
            unset($draftData['product_id']); // Prevent accidental product overwrite
            $this->request->post = array_merge($this->request->post, $draftData);
            $this->session->data['success'] = 'Draft loaded successfully.';
        }
    }

	//product draft end 23/06/25
	
        
        // Add this line 28-04-2025 ------------------------------------
		$data['user_token'] = $this->session->data['user_token'];
		
	 
		$this->load->model('vendor/vendor'); // You'll need to create or use this model
		// added code for show the username 21-05-2025 - fetch vendor full name
		$vendor_id = $this->vendor->getId(); // Assuming this returns the logged-in vendor ID


		$vendor_info = $this->model_vendor_vendor->getVendor($vendor_id);

		if ($vendor_info) {
			$vendor_name = trim($vendor_info['firstname'] . ' ' . $vendor_info['lastname']);
			$this->request->post['added_by'] = $vendor_name;
			$this->request->post['edited_by'] = $vendor_name;
		}

		// --=====-------------------
// 		// Load your view template
// 		$this->response->setOutput($this->load->view('vendor/product_form', $data));
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/product');
		$this->load->model('vendor/category'); // ✅ Added
		
		$data['categories'] = $this->model_vendor_category->getCategories();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$product_id = $this->model_vendor_product->addProduct($this->request->post);
	//product draft start
				$this->model_vendor_product->deleteProductDraft($vendor_id);
			//product draft end
// 			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
// faq start 
		if (isset($this->request->post['product_faq'])) {
				$this->model_vendor_product->saveProductFaqs($product_id, $this->request->post['product_faq']);
			}
// faq end 

			// product warranty return replacement policy start
			if (!in_array($this->request->post['category_level_1'], $this->warranty_categories)) {
			if (isset($this->request->post['replacement_policy'])) {
				$this->model_vendor_product->saveReplacementPolicy($product_id, $this->request->post['replacement_policy']);
			}
			
					if (isset($this->request->post['product_warranty'])) {
				$this->model_vendor_product->saveProductWarranty($product_id, $this->request->post['product_warranty']);
			}

				if (isset($this->request->post['product_return'])) {
				$this->model_vendor_product->saveReturnPolicy($product_id, $this->request->post['product_return']);
			}
}
			// product warranty return replacement policy end

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
			
			// Category values with fallback
			$category_level_1 = isset($this->request->post['category_level_1']) ? (int)$this->request->post['category_level_1'] : 0;
			$category_level_2 = isset($this->request->post['category_level_2']) ? (int)$this->request->post['category_level_2'] : 0;
			$category_level_3 = isset($this->request->post['category_level_3']) ? (int)$this->request->post['category_level_3'] : 0;
			$category_level_4 = isset($this->request->post['category_level_4']) ? (int)$this->request->post['category_level_4'] : 0;
			$category_level_5 = isset($this->request->post['category_level_5']) ? (int)$this->request->post['category_level_5'] : 0;
	
			$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_product_category WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_product_category SET 
				vendor_id = '" . (int)$this->vendor->getId() . "',
				product_id = '" . (int)$product_id . "',
				category_level_1 = '" . $category_level_1 . "',
				category_level_2 = '" . $category_level_2 . "',
				category_level_3 = '" . $category_level_3 . "',
				category_level_4 = '" . $category_level_4 . "',
				category_level_5 = '" . $category_level_5 . "'");

            // added on 28-04-2025 ---------------------------------
            $data['error_length'] = isset($this->error['length']) ? $this->error['length'] : '';
            $data['error_width'] = isset($this->error['width']) ? $this->error['width'] : '';
            $data['error_height'] = isset($this->error['height']) ? $this->error['height'] : '';
            // -------------------------------------------------------
            $this->session->data['success'] = $this->language->get('text_success');
            // variant start commented on the 06-05-2025
            if (isset($this->session->data['variant_data']) && !empty($this->session->data['variant_data'])) {
				$data = [];
				$data['variant_data'] = $this->session->data['variant_data'];
				$this->response->redirect($this->url->link('vendor/product/add', $url, true));
			} else {
            // 			variant end
			$this->response->redirect($this->url->link('vendor/product', $url, true));
			}
		}
		// add this block right here on 28-04-2025 ----------------------
		 if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$this->error = array();  // Clear errors on page load
		}
		
		// Load your view template
		$this->response->setOutput($this->load->view('vendor/product_form', $data));

		$this->getForm($data);
	}

	public function edit() {
		$this->load->language('vendor/product');

        // added code for show username on 21-05-2025 - fetch vendor full name
		$vendor_id = $this->vendor->getId(); // Assuming this returns the logged-in vendor ID

		$this->load->model('vendor/vendor'); // You'll need to create or use this model

		$vendor_info = $this->model_vendor_vendor->getVendor($vendor_id);

		if ($vendor_info) {
			$vendor_name = trim($vendor_info['firstname'] . ' ' . $vendor_info['lastname']);
			$this->request->post['added_by'] = $vendor_name;
			$this->request->post['edited_by'] = $vendor_name;
		}

		// --=====------====
        
        //  added on 28-04-2025----------------------------------
        
		$data['user_token'] = $this->session->data['user_token'];

		// Load your view template
		$this->response->setOutput($this->load->view('vendor/product_form', $data));
        //-------------------------------------------------------------

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/product');
		$this->load->model('vendor/category'); // Ensure this is loaded for category path
		
		$product_id = $this->request->get['product_id'];

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
		     //  variant edit start 
		    $product_id = $this->request->get['product_id'] ?? 0;
			$variant_name = $this->request->post['variant_name'] ?? '';
		
			$this->model_vendor_product->updateProductVariantName($product_id, $variant_name);
// 			variant edit end 
			$this->model_vendor_product->editProduct($this->request->get['product_id'], $this->request->post);
			
			$category_level_1 = isset($this->request->post['category_level_1']) ? (int)$this->request->post['category_level_1'] : 0;
			$category_level_2 = isset($this->request->post['category_level_2']) ? (int)$this->request->post['category_level_2'] : 0;
			$category_level_3 = isset($this->request->post['category_level_3']) ? (int)$this->request->post['category_level_3'] : 0;
			$category_level_4 = isset($this->request->post['category_level_4']) ? (int)$this->request->post['category_level_4'] : 0;
			$category_level_5 = isset($this->request->post['category_level_5']) ? (int)$this->request->post['category_level_5'] : 0;

			// Delete existing categories for product
			$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_product_category WHERE product_id = '" . (int)$product_id . "'");

			// Insert new categories for product
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_product_category SET 
				vendor_id = '" . (int)$this->vendor->getId() . "',
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
				$this->model_vendor_product->saveProductFaqs($product_id, $this->request->post['product_faq']);
			}
// faq end 
			//product warranty return replacement policy start
if (!in_array($this->request->post['category_level_1'], $this->warranty_categories)) {
		
			if (isset($this->request->post['replacement_policy'])) {
			$this->model_vendor_product->saveReplacementPolicy($product_id, $this->request->post['replacement_policy']);
		}
			if (isset($this->request->post['product_warranty'])) {
				$this->model_vendor_product->saveProductWarranty($product_id, $this->request->post['product_warranty']);
			}

			if (isset($this->request->post['product_return'])) {
			$this->model_vendor_product->saveReturnPolicy($product_id, $this->request->post['product_return']);
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
            
            // added on 28-04-2025
            $data['error_length'] = isset($this->error['length']) ? $this->error['length'] : '';
            $data['error_width'] = isset($this->error['width']) ? $this->error['width'] : '';
            $data['error_height'] = isset($this->error['height']) ? $this->error['height'] : '';

           
            //----------------------------------------------------------------
            // $this->session->data['success'] = $this->language->get('text_success');
            // 			variant start 
			if (isset($this->session->data['variant_data'])) {
				$data = [];
				$data['variant_data'] = $this->session->data['variant_data'];
				$this->response->redirect($this->url->link('vendor/product/edit','product_id=' . $this->request->get['product_id'] . $url, true));
			} else {
			 //   variant end 
			$this->response->redirect($this->url->link('vendor/product', $url, true));
			}
		}
        // added on 28-04-2025--------------
        // add this block right here
		 if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$this->error = array();  // Clear errors on page load
		}
        // ------------------------------------------
        
        // Get product info
		$product_info = $this->model_vendor_product->getProduct($product_id, $this->vendor->getId());
		
		$data['product_info'] = $product_info;
	
		// Get saved category levels for the product
		$category_levels = $this->model_vendor_product->getProductCategoriesinfo($product_id);

		$data['product_categories'] = array();

		foreach ($category_levels as $category) {
			$data['product_categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name']
			);
		}


		$data['selected_categories'] = $category_levels;
	
		// Prepare category dropdowns based on saved selections
		$data['categories']['level_1'] = $this->model_vendor_product->getCategoriesByParentId(0);
		$data['categories']['level_2'] = !empty($category_levels['level_1']) ? $this->model_vendor_product->getCategoriesByParentId($category_levels['level_1']) : [];
		$data['categories']['level_3'] = !empty($category_levels['level_2']) ? $this->model_vendor_product->getCategoriesByParentId($category_levels['level_2']) : [];
		$data['categories']['level_4'] = !empty($category_levels['level_3']) ? $this->model_vendor_product->getCategoriesByParentId($category_levels['level_3']) : [];
		$data['categories']['level_5'] = !empty($category_levels['level_4']) ? $this->model_vendor_product->getCategoriesByParentId($category_levels['level_4']) : [];
	
		// Load full category name paths for display
		$this->load->model('catalog/category');
		$data['category_paths'] = array(); // To store category paths
		foreach ($category_levels as $level => $category_id) {
			if ($category_id) {
				// Category path fetch karo
				$category_path = $this->model_vendor_category->getCategoryPath($category_id);
				if ($category_path) {
					// Category path ko store karo
					$data['category_paths'][$level] = $category_path;
				}
			}
		}

		$this->getForm($data);
	}

	public function delete() {
		$this->load->language('vendor/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/product');

		if (isset($this->request->post['selected']) ) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_vendor_product->deleteProduct($product_id);
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

			$this->response->redirect($this->url->link('vendor/product', $url, true));
		}

		$this->getList();
	}

	public function copy() {
		$this->load->language('vendor/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/product');

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_vendor_product->copyProduct($product_id);
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

			$this->response->redirect($this->url->link('vendor/product', $url, true));
		}

		$this->getList();
	}

	protected function getList() {
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
		
		$data['cancel'] = $this->url->link('vendor/dashboard');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/product', '', true)
		);

		$data['add'] = $this->url->link('vendor/product/add', '', true);
		$data['copy'] = $this->url->link('vendor/product/copy', '', true);
		$data['delete'] = $this->url->link('vendor/product/delete', '', true);

		$data['products'] = array();

		$filter_data = array(
			'filter_name'	  => $filter_name,
			'filter_model'	  => $filter_model,
			'filter_price'	  => $filter_price,
			'filter_quantity' => $filter_quantity,
			'filter_status'   => $filter_status,
			'vendor_id'   	  => $this->vendor->getId(),
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');

		$product_total = $this->model_vendor_product->getTotalProducts($filter_data);

		$results = $this->model_vendor_product->getProducts($filter_data);
		//print_r($results);die();
		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$special = false;

			$product_specials = $this->model_vendor_product->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));

					break;
				}
			}

			// show order total for vendor - update by sagar

// 			$data['totals'] = array();

// 			$totals = $this->model_vendor_product->getVendorOrderTotals($this->request->get['order_id']);

// 			foreach ($totals as $total) {
// 				$data['totals'][] = array(
// 					'title' => $total['title'],
// 					'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
// 				);
// 			}

			// -------------------------------------------------------------------------------------
						 
			if($result['status']==2){				
				$autoapprovedproduct =  $this->config->get('vendor_proautoapprove');				
				if($autoapprovedproduct==1){
				$status= $this->language->get('text_enable');
				} else {
				 $status=  $this->language->get('text_approvelpending');
				}	
				
					
			} elseif($result['status']==1){ 
				$status= $this->language->get('text_enable');
			} elseif($result['status']==0){
				$status= $this->language->get('text_disabled');
			} else {
				$status='';
			} 
			
			$chkautoapproved =  $this->config->get('vendor_proautoapprove');
			if($chkautoapproved==1){
				$chkstatus= $result['status'];
			} else {
				$chkstatus=$status;
			} 
			
			$data['chkautoapprovedproduct'] =  $this->config->get('vendor_proautoapprove');
			/* 2020 */
			/*-- added on 04-04-2025-------------------------------*/
			
			$this->load->model('vendor/product');
			// approval comment
			$comments = $this->model_vendor_product->getAllComments($result['product_id']);
			
			// disapproval status
            $latest_comment = '';
            if ($result['status'] == 0) { // Only when disapproved
                $latest_comment = $this->model_vendor_product->getLatestAdminComment($result['product_id']);
            }
            // ----------------------------------------------------------			
		
			$data['products'][] = array(
				'product_id' => $result['product_id'],
				'image'      => $image,
				'statusvalue'     => $result['status'],
				'status'     => $status,
				'chkstatus'     => $chkstatus,
				'name'       => $result['name'],
				'model'      => $result['model'],
				'price'      => $this->currency->format($result['price'], $this->config->get('config_currency')),
				'special'    => $special,
				'quantity'   => $result['quantity'],
				'edit'       => $this->url->link('vendor/product/edit','product_id=' . $result['product_id'] . $url, true),
				'comment_thread' => $comments,
				'statusvalue' => $result['status'],
				'view_comment_url' => $this->url->link('vendor/product/viewCommentModal', 'product_id=' . $result['product_id'] . '&user_token=' . $this->session->data['user_token'], true),
				'latest_comment' => $latest_comment
			);
				
		}
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_enable'] = $this->language->get('text_enable');
		$data['text_approve'] = $this->language->get('text_approve');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['text_select'] = $this->language->get('text_select');

		$data['column_image'] = $this->language->get('column_image');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_model'] = $this->language->get('column_model');
		$data['column_price'] = $this->language->get('column_price');
		$data['column_quantity'] = $this->language->get('column_quantity');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_model'] = $this->language->get('entry_model');
		$data['entry_price'] = $this->language->get('entry_price');
		$data['entry_quantity'] = $this->language->get('entry_quantity');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_feed'] = $this->language->get('entry_feed');

		$data['button_copy'] = $this->language->get('button_copy');
		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_filter'] = $this->language->get('button_filter');
		
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

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('vendor/product','sort=pd.name' . $url, true);
		$data['sort_model'] = $this->url->link('vendor/product','sort=p.model' . $url, true);
		$data['sort_price'] = $this->url->link('vendor/product','sort=p.price' . $url, true);
		$data['sort_quantity'] = $this->url->link('vendor/product','sort=p.quantity' . $url, true);
		$data['sort_status'] = $this->url->link('vendor/product','sort=p.status' . $url, true);
		$data['sort_order'] = $this->url->link('vendor/product','sort=p.sort_order' . $url, true);

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
		$pagination->url = $this->url->link('vendor/product',$url . 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;
		$data['filter_price'] = $filter_price;
		$data['filter_quantity'] = $filter_quantity;
		$data['filter_status'] = $filter_status;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');

		
		// availability 
		$vendorResult = $this->model_vendor_product->getvendorPincode();
		// var_dump($vendorResult);
		$data['vendorState'] = $vendorResult['state'];
		$data['vendorCity'] = $vendorResult['city'];
		if (isset($this->request->post['deliveryOption'])) {
			$data['deliveryOption'] = $this->request->post['deliveryOption'];
		} else {
			$data['deliveryOption'] = '';
		}
		if ($data['deliveryOption'] === 'custom') {
			if (isset($this->request->post['pincodeInput'])) {
				$data['pincodeInput'] = $this->request->post['pincodeInput'];
			} else {
				$data['pincodeInput'] = '';
			}
		}
		// var_dump($data['deliveryOption']);
		// adding courier charges
		if (isset($this->request->post['nationalCharges'])) {
			$data['nationalCharges'] = $this->request->post['nationalCharges'];
		} else {
			$data['nationalCharges'] = '';
		}
		if (isset($this->request->post['localCharges'])) {
			$data['localCharges'] = $this->request->post['localCharges'];
		} else {
			$data['localCharges'] = '';
		}
		if (isset($this->request->post['zonalCharges'])) {
			$data['zonalCharges'] = $this->request->post['zonalCharges'];
		} else {
			$data['zonalCharges'] = '';
		}

		if (isset($this->request->post['courier_free_price'])) {
			$data['courier_free_price'] = $this->request->post['courier_free_price'];
		} else {
			$data['courier_free_price'] = '';
		}

		// local zonal national ends 
        unset($this->session->data['variant_data']);
        // $data['product_video']= $this->load->controller('common/video_popup', ['video_url' => 'https://www.youtube.com/embed/Sn0fUwBwhBs']);

		$this->response->setOutput($this->load->view('vendor/product_list', $data));
	}

	protected function getForm() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		/* 10 01 2020 */
		$vendor_id = $this->vendor->getId();
		$data['chkautoapprovedproduct'] =  $this->config->get('vendor_proautoapprove');
		/* 10 01 2020 */
		$data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_enable'] = $this->language->get('text_enable');
		$data['text_approve'] = $this->language->get('text_approve');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_plus'] = $this->language->get('text_plus');
		$data['text_minus'] = $this->language->get('text_minus');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_option'] = $this->language->get('text_option');
		$data['text_option_value'] = $this->language->get('text_option_value');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_percent'] = $this->language->get('text_percent');
		$data['text_amount'] = $this->language->get('text_amount');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_meta_title'] = $this->language->get('entry_meta_title');
		$data['entry_meta_description'] = $this->language->get('entry_meta_description');
		$data['entry_meta_keyword'] = $this->language->get('entry_meta_keyword');
		$data['entry_model'] = $this->language->get('entry_model');
		$data['entry_sku'] = $this->language->get('entry_sku');
		$data['entry_upc'] = $this->language->get('entry_upc');
		$data['entry_ean'] = $this->language->get('entry_ean');
		$data['entry_jan'] = $this->language->get('entry_jan');
		$data['entry_isbn'] = $this->language->get('entry_isbn');
		$data['entry_mpn'] = $this->language->get('entry_mpn');
		$data['entry_location'] = $this->language->get('entry_location');
		$data['entry_minimum'] = $this->language->get('entry_minimum');
		$data['entry_shipping'] = $this->language->get('entry_shipping');
		$data['entry_date_available'] = $this->language->get('entry_date_available');
		$data['entry_quantity'] = $this->language->get('entry_quantity');
		$data['entry_stock_status'] = $this->language->get('entry_stock_status');
		$data['entry_price'] = $this->language->get('entry_price');
		///// xml
		$data['entry_cost'] = $this->language->get('entry_cost');
		$data['entry_profit'] = $this->language->get('entry_profit');
		///// xml
		$data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$data['entry_points'] = $this->language->get('entry_points');
		$data['entry_option_points'] = $this->language->get('entry_option_points');
		$data['entry_subtract'] = $this->language->get('entry_subtract');
		$data['entry_weight_class'] = $this->language->get('entry_weight_class');
		$data['entry_weight'] = $this->language->get('entry_weight');
		$data['entry_dimension'] = $this->language->get('entry_dimension');
		$data['entry_length_class'] = $this->language->get('entry_length_class');
		$data['entry_length'] = $this->language->get('entry_length');
		$data['entry_width'] = $this->language->get('entry_width');
		$data['entry_height'] = $this->language->get('entry_height');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_additional_image'] = $this->language->get('entry_additional_image');
		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
		$data['entry_download'] = $this->language->get('entry_download');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_filter'] = $this->language->get('entry_filter');
		$data['entry_related'] = $this->language->get('entry_related');
		$data['entry_attribute'] = $this->language->get('entry_attribute');
		$data['entry_text'] = $this->language->get('entry_text');
		$data['entry_option'] = $this->language->get('entry_option');
		$data['entry_option_value'] = $this->language->get('entry_option_value');
		$data['entry_required'] = $this->language->get('entry_required');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_date_start'] = $this->language->get('entry_date_start');
		$data['entry_date_end'] = $this->language->get('entry_date_end');
		$data['entry_priority'] = $this->language->get('entry_priority');
		$data['entry_tag'] = $this->language->get('entry_tag');
		$data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$data['entry_reward'] = $this->language->get('entry_reward');
		$data['entry_layout'] = $this->language->get('entry_layout');
		$data['entry_recurring'] = $this->language->get('entry_recurring');
		$data['entry_feed'] = $this->language->get('entry_feed');
		
// 		//added on 02-04-2025
		$data['entry_volumetric_weight'] = $this->language->get('entry_volumetric_weight');

		$data['help_sku'] = $this->language->get('help_sku');
		$data['help_upc'] = $this->language->get('help_upc');
		$data['help_ean'] = $this->language->get('help_ean');
		$data['help_jan'] = $this->language->get('help_jan');
		$data['help_isbn'] = $this->language->get('help_isbn');
		$data['help_mpn'] = $this->language->get('help_mpn');
		$data['help_minimum'] = $this->language->get('help_minimum');
		$data['help_manufacturer'] = $this->language->get('help_manufacturer');
		$data['help_stock_status'] = $this->language->get('help_stock_status');
		$data['help_points'] = $this->language->get('help_points');
		$data['help_category'] = $this->language->get('help_category');
		$data['help_filter'] = $this->language->get('help_filter');
		$data['help_download'] = $this->language->get('help_download');
		$data['help_related'] = $this->language->get('help_related');
		$data['help_tag'] = $this->language->get('help_tag');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_attribute_add'] = $this->language->get('button_attribute_add');
		$data['button_option_add'] = $this->language->get('button_option_add');
		$data['button_option_value_add'] = $this->language->get('button_option_value_add');
		$data['button_discount_add'] = $this->language->get('button_discount_add');
		$data['button_special_add'] = $this->language->get('button_special_add');
		$data['button_image_add'] = $this->language->get('button_image_add');
		$data['button_remove'] = $this->language->get('button_remove');
		$data['button_recurring_add'] = $this->language->get('button_recurring_add');

		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_data'] = $this->language->get('tab_data');
		$data['tab_attribute'] = $this->language->get('tab_attribute');
		$data['tab_option'] = $this->language->get('tab_option');
		$data['tab_recurring'] = $this->language->get('tab_recurring');
		$data['tab_discount'] = $this->language->get('tab_discount');
		$data['tab_special'] = $this->language->get('tab_special');
		$data['tab_image'] = $this->language->get('tab_image');
		$data['tab_links'] = $this->language->get('tab_links');
		$data['tab_reward'] = $this->language->get('tab_reward');
		$data['tab_seo'] = $this->language->get('tab_seo');
		$data['tab_design'] = $this->language->get('tab_design');
		$data['tab_openbay'] = $this->language->get('tab_openbay');
		
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

        // 		if (isset($this->error['meta_title'])) {
        // 			$data['error_meta_title'] = $this->error['meta_title'];
        // 		} else {
        // 			$data['error_meta_title'] = array();
        // 		}
        
        // warranty return replace start 
    	// === Error Mapping for Twig View ===
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
				$data['replacement_policy'] = $this->model_vendor_product->getReplacementPolicy($this->request->get['product_id']);
			} else {
				$data['replacement_policy'] = [
					'is_replacable' => '',
					'replacement_reason' => '',
					'replacement_period' => '',
					'replacement_policy' => '',
					'replacement_description' => ''
				];
			}

            $data['warranty_categories']= $this->warranty_categories;
			// product warranty starts 
			if (isset($this->request->post['product_warranty'])) {
				$data['product_warranty'] = $this->request->post['product_warranty'];
			} elseif (isset($this->request->get['product_id'])) {
				$data['product_warranty'] = $this->model_vendor_product->getProductWarranty($this->request->get['product_id']);
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
				$data['product_return'] = $this->model_vendor_product->getReturnPolicy($this->request->get['product_id']);
			} else {
				$data['product_return'] = [
					'is_returnable' => '',
					'return_duration_period' => '',
					'return_policy_details' => ''
				];
			}



// 		warranty return replace end 

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
        $data['product_faqs'] = $this->model_vendor_product->getProductFaqs($this->request->get['product_id']);
    } else {
        $data['product_faqs'] = [];
    }
    
    // faq end
   
		if (isset($this->error['model'])) {
			$data['error_model'] = $this->error['model'];
		} else {
			$data['error_model'] = '';
		}
		
        // added the changes on 28-04-2025-------------------------------------
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
		
        // Changes By Sheetal

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


		if(isset($this->error['manufacturer'])){

			$data['error_manufacturer'] = $this->error['manufacturer'];
		}else{
			$data['error_manufacturer'] = '';
		}
	
//         if($this->error){

// 			$data['error_warning'] = $this->language->get('error_warning');
// 		} else {

// 			$data['error_warning'] = '';
// 		}

        // ----------------------------------------------------------------------
	  // For per-row image required error updated on 30-04-2025 -----------------------------

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
        
        // // For dynamic "select more image(s)" message 19-04-2025
        // if (isset($this->error['dynamic_image_count'])) {
        //     $data['error_dynamic_image_count'] = $this->error['dynamic_image_count'];
        // } else {
        //     $data['error_dynamic_image_count'] = '';
        // }
        // -----------------------------------------------------------------------------
        
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
			'href' => $this->url->link('common/home', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/product', '', true)
		);

		if (!isset($this->request->get['product_id'])) {
			$data['action'] = $this->url->link('vendor/product/add', '', true);
		} else {
			$data['action'] = $this->url->link('vendor/product/edit','product_id=' . $this->request->get['product_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('vendor/product', '', true);
		
            $data['variants']=true;

		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			
			/* 10 01 2020 add vendor_id */
			$product_info = $this->model_vendor_product->getProduct($this->request->get['product_id'], $vendor_id);
		 // variant edit start 
    	   $data['product_variants'] = $this->model_vendor_product->getProductsVariants($this->request->get['product_id']);
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
    	   
    	   }
    	   else{
    	       
            $data['variants']=false;
    	   }
    	  
	   
	   //variant edit end 
			
// 			var_dump($product_info);
			/* 10 01 2020 add new code */
			
			if(isset($product_info['product_id'])){
				$data['product_id'] =$this->request->get['product_id'];
			} else {
				if(empty($product_info['product_id'])){
					
					$this->response->redirect($this->url->link('vendor/product', '', true));
				} else {
					
				}
			}
			/* 10 01 2020 add new code */
		}
		
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
        
		if (isset($this->request->post['product_description'])) {
			$data['product_description'] = $this->request->post['product_description'];
		} elseif (isset($this->request->get['product_id'])) {
			/* 10 01 2020 add vendor_id */
			$data['product_description'] = $this->model_vendor_product->getProductDescriptions($this->request->get['product_id'], $vendor_id);
		} else {
			$data['product_description'] = array();
		}
// getting courier charges 
        $productCourierCharges=$this->model_vendor_product->getProductCourierCharges($this->request->get['product_id']);    			    
        
       
		
		if (isset($this->request->post['nationalCharges'])) {
			$data['nationalCharges'] = $this->request->post['nationalCharges'];
		}elseif (!empty($productCourierCharges)) {
			$data['nationalCharges'] = $productCourierCharges['national_charges'];
		} else {
			$data['nationalCharges'] = '';
		}
		if (isset($this->request->post['localCharges'])) {
			$data['localCharges'] = $this->request->post['localCharges'];
		}elseif (!empty($productCourierCharges)) {
			$data['localCharges'] = $productCourierCharges['local_charges'];
		} else {
			$data['localCharges'] = '';
		}
		if (isset($this->request->post['zonalCharges'])) {
			$data['zonalCharges'] = $this->request->post['zonalCharges'];
		} elseif (!empty($productCourierCharges)) {
			$data['zonalCharges'] = $productCourierCharges['zonal_charges'];
		}else {
			$data['zonalCharges'] = '';
		}

		if (isset($this->request->post['courier_free_price'])) {
			$data['courier_free_price'] = $this->request->post['courier_free_price'];
		} elseif (!empty($productCourierCharges)) {
			$data['courier_free_price'] = $productCourierCharges['courier_free_price'];
		}else {
			$data['courier_free_price'] = '';
		}

// 		courier charges ends 

		if (isset($this->request->post['model'])) {
			$data['model'] = $this->request->post['model'];
		} elseif (!empty($product_info)) {
			$data['model'] = $product_info['model'];
		} else {
			$data['model'] = '';
		}
// // 		added on 19-02-2025
		
// 		if (isset($this->error['price'])) {
// 			$data['error_price'] = $this->error['price'];
// 		} else {
// 			$data['error_price'] = '';
// 		}
// 		if (isset($this->error['length'])) {
// 			$data['error_length'] = $this->error['length'];
// 		} else {
// 			$data['error_length'] = '';
// 		}
// 		if (isset($this->error['width'])) {
// 			$data['error_width'] = $this->error['width'];
// 		} else {
// 			$data['error_width'] = '';
// 		}
// 		if (isset($this->error['height'])) {
// 			$data['error_height'] = $this->error['height'];
// 		} else {
// 			$data['error_height'] = '';
// 		}
// 		if (isset($this->error['weight'])) {
// 			$data['error_weight'] = $this->error['weight'];
// 		} else {
// 			$data['error_weight'] = '';
// 		}
		
// // 		end 

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
		
        //  added on 28-04-2025-------------------------------------------
        // if (isset($this->request->post['price'])) {
        //     $data['price'] = $this->request->post['price'];
        // } elseif (!empty($product_info)) {
        //     $data['price'] = $product_info['price'];
        // } else {
        //     $data['price'] = '';
        // }
        
        // // HSN Code
        // if (isset($this->request->post['hsn_code'])) {
        //     $data['hsn_code'] = $this->request->post['hsn_code'];
        // } elseif (!empty($product_info)) {
        //     $data['hsn_code'] = $product_info['hsn_code'];
        // } else {
        //     $data['hsn_code'] = '';
        // }
        
        // // GST Rate
        // if (isset($this->request->post['gst_rate'])) {
        //     $data['gst_rate'] = $this->request->post['gst_rate'];
        // } elseif (!empty($product_info)) {
        //     $data['gst_rate'] = $product_info['gst_rate'];
        // } else {
        //     $data['gst_rate'] = '';
        // }
        
        
        
        
        // // Length
        // if (isset($this->request->post['length'])) {
        //     $data['length'] = $this->request->post['length'];
        // } elseif (!empty($product_info)) {
        //     $data['length'] = $product_info['length'];
        // } else {
        //     $data['length'] = '';
        // }
        
        // // Width
        // if (isset($this->request->post['width'])) {
        //     $data['width'] = $this->request->post['width'];
        // } elseif (!empty($product_info)) {
        //     $data['width'] = $product_info['width'];
        // } else {
        //     $data['width'] = '';
        // }
        
        // // Height
        // if (isset($this->request->post['height'])) {
        //     $data['height'] = $this->request->post['height'];
        // } elseif (!empty($product_info)) {
        //     $data['height'] = $product_info['height'];
        // } else {
        //     $data['height'] = '';
        // }
        
        
        
        // if (isset($this->request->post['weight'])) {
        //     $data['weight'] = $this->request->post['weight'];
        // } elseif (!empty($product_info)) {
        //     $data['weight'] = $product_info['weight'];
        // } else {
        //     $data['weight'] = '';
        // }
        // --------------------------------------------------------------------

		if (isset($this->request->post['location'])) {
			$data['location'] = $this->request->post['location'];
		} elseif (!empty($product_info)) {
			$data['location'] = $product_info['location'];
		} else {
			$data['location'] = '';
		}

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


		if (isset($this->request->post['product_store'])) {
			$data['product_store'] = $this->request->post['product_store'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_store'] = $this->model_vendor_product->getProductStores($this->request->get['product_id']);
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

		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} elseif (!empty($product_info)) {
			$data['price'] = $product_info['price'];
		} else {
			$data['price'] = '';
		}

		$this->load->model('vendor/recurring');
		$filter1=array(
			'vendor_id'  => $this->vendor->getId()
		);

		$data['recurrings'] = $this->model_vendor_recurring->getRecurrings($filter1);

		if (isset($this->request->post['product_recurrings'])) {
			$data['product_recurrings'] = $this->request->post['product_recurrings'];
		} elseif (!empty($product_info)) {
			$data['product_recurrings'] = $this->model_vendor_product->getRecurrings($product_info['product_id']);
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
		
		if (!empty($this->request->post['payment_method'])) {
            $data['payment_method'] = $this->request->post['payment_method'];
        } elseif (!empty($product_info)) {
            $data['payment_method'] = $product_info['payment_method'];
        } else {
            $data['payment_method'] = 'Both';
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
			$data['status'] = '';
		}

        // added on 17-04-2025
		if (isset($this->request->post['volumetric_weight'])) {
			$data['volumetric_weight'] = $this->request->post['volumetric_weight'];
		} elseif (!empty($product_info)) {
			$data['volumetric_weight'] = $product_info['volumetric_weight'];
		} else {
			$data['volumetric_weight'] = '';
		}
		
		// added on 19-04-2025---------------------
		if (isset($this->request->post['hsn_code'])) {
			$data['hsn_code'] = $this->request->post['hsn_code'];
		} elseif (!empty($product_info)) {
			$data['hsn_code'] = $product_info['hsn_code'];
		} else {
			$data['hsn_code'] = '';
		}
        // --------------------------------------------------
        
        // added on 21-04-2025 to show the gst_rate ---------------------
		if (isset($this->request->post['gst_rate'])) {
			$data['gst_rate'] = $this->request->post['gst_rate'];
		} elseif (!empty($product_info)) {
			$data['gst_rate'] = $product_info['gst_rate'];
		} else {
			$data['gst_rate'] = '';
		}
        // --------------------------------------------------------------
        
        if (isset($this->request->post['weight'])) {
			$data['weight'] = $this->request->post['weight'];
		} elseif (!empty($product_info)) {
			$data['weight'] = $product_info['weight'];
		} else {
			$data['weight'] = '';
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

		$this->load->model('vendor/manufacturer');

		if (isset($this->request->post['manufacturer_id'])) {
			$data['manufacturer_id'] = $this->request->post['manufacturer_id'];
		} elseif (!empty($product_info)) {
			$data['manufacturer_id'] = $product_info['manufacturer_id'];
		} else {
			$data['manufacturer_id'] = 0;
		}

		if (isset($this->request->post['manufacturer'])) {
			$data['manufacturer'] = $this->request->post['manufacturer'];
		} elseif (!empty($product_info)) {
			$manufacturer_info = $this->model_vendor_manufacturer->getManufacturer($product_info['manufacturer_id']);

			if ($manufacturer_info) {
				$data['manufacturer'] = $manufacturer_info['name'];
			} else {
				$data['manufacturer'] = '';
			}
		} else {
			$data['manufacturer'] = '';
		}

		// Categories
		$this->load->model('vendor/category');

		if (isset($this->request->post['product_category'])) {
			$categories = $this->request->post['product_category'];
		} elseif (isset($this->request->get['product_id'])) {
			$categories = $this->model_vendor_product->getProductCategories($this->request->get['product_id']);
		} else {
			$categories = array();
		}

		$data['product_categories'] = array();

		foreach ($categories as $category_id) {
			$category_info = $this->model_vendor_category->getCategory($category_id);

			if ($category_info) {
				$data['product_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}

		// Filters
		$this->load->model('vendor/filter');

		if (isset($this->request->post['product_filter'])) {
			$filters = $this->request->post['product_filter'];
		} elseif (isset($this->request->get['product_id'])) {
			$filters = $this->model_vendor_product->getProductFilters($this->request->get['product_id']);
		} else {
			$filters = array();
		}

		$data['product_filters'] = array();

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_vendor_filter->getFilter($filter_id);

			if ($filter_info) {
				$data['product_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}

		// Attributes
		$this->load->model('vendor/attribute');

		if (isset($this->request->post['product_attribute'])) {
			$product_attributes = $this->request->post['product_attribute'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_attributes = $this->model_vendor_product->getProductAttributes($this->request->get['product_id']);
		} else {
			$product_attributes = array();
		}

		$data['product_attributes'] = array();

		foreach ($product_attributes as $product_attribute) {
			$attribute_info = $this->model_vendor_attribute->getAttribute($product_attribute['attribute_id']);

			if ($attribute_info) {
				$data['product_attributes'][] = array(
					'attribute_id'                  => $product_attribute['attribute_id'],
					'name'                          => $attribute_info['name'],
					'product_attribute_description' => $product_attribute['product_attribute_description']
				);
			}
		}

		// Options
		$this->load->model('vendor/option');

		if (isset($this->request->post['product_option'])) {
			$product_options = $this->request->post['product_option'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_options = $this->model_vendor_product->getProductOptions($this->request->get['product_id']);
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
						'weight_prefix'           => $product_option_value['weight_prefix']
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
					$data['option_values'][$product_option['option_id']] = $this->model_vendor_option->getOptionValues($product_option['option_id']);
				}
			}
		}

		$this->load->model('account/customer_group');

		$data['customer_groups'] = $this->model_account_customer_group->getCustomerGroups();

		if (isset($this->request->post['product_discount'])) {
			$product_discounts = $this->request->post['product_discount'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_discounts = $this->model_vendor_product->getProductDiscounts($this->request->get['product_id']);
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
			$product_specials = $this->model_vendor_product->getProductSpecials($this->request->get['product_id']);
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
			$product_images = $this->model_vendor_product->getProductImages($this->request->get['product_id']);
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
		$this->load->model('vendor/download');

		if (isset($this->request->post['product_download'])) {
			$product_downloads = $this->request->post['product_download'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_downloads = $this->model_vendor_product->getProductDownloads($this->request->get['product_id']);
		} else {
			$product_downloads = array();
		}

		$data['product_downloads'] = array();

		foreach ($product_downloads as $download_id) {
			$download_info = $this->model_vendor_download->getDownload($download_id);

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
			$products = $this->model_vendor_product->getProductRelated($this->request->get['product_id']);
		} else {
			$products = array();
		}

		$data['product_relateds'] = array();
		/* 13 02 2020 */
		$vendor_id= $this->vendor->getId();		
		/* 13 02 2020 */
		foreach ($products as $product_id) {
			$related_info = $this->model_vendor_product->getProduct($product_id, $vendor_id);

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
			$data['product_reward'] = $this->model_vendor_product->getProductRewards($this->request->get['product_id']);
		} else {
			$data['product_reward'] = array();
		}

		if (isset($this->request->post['product_seo_url'])) {
			$data['product_seo_url'] = $this->request->post['product_seo_url'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_seo_url'] = $this->model_vendor_product->getProductSeoUrls($this->request->get['product_id']);
		} else {
			$data['product_seo_url'] = array();
		}

		if (isset($this->request->post['product_layout'])) {
			$data['product_layout'] = $this->request->post['product_layout'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_layout'] = $this->model_vendor_product->getProductLayouts($this->request->get['product_id']);
		} else {
			$data['product_layout'] = array();
		}

		
		$this->load->model('vendor/vendor');
		$data['layouts'] = $this->model_vendor_vendor->getLayouts();
		
	
		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');

		// var_dump($data['deliveryOption']);
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
// 		var_dump($data['replacement_policy'],$data['product_warranty']);
// variant end 
		$this->response->setOutput($this->load->view('vendor/product_form', $data));
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
		// added on 28-04-2025----------------------
// 		if (!$this->user->hasPermission('modify', 'catalog/product')) {
// 			$this->error['warning'] = $this->language->get('error_permission');
// 		}
		
		foreach ($this->request->post['product_description'] as $language_id => $value) {
// 			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
// 				$this->error['name'][$language_id] = $this->language->get('error_name');
// 			}

// 			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
// 				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
// 			}

            // added on 28-04-2025 --------------------------------------------------
            if (!isset($value['name']) || utf8_strlen(trim($value['name'])) < 3 || utf8_strlen(trim($value['name'])) > 255) {
            	$this->error['name'][$language_id] = $this->language->get('error_name');
            }
            
            // if (!isset($value['meta_title']) || utf8_strlen(trim($value['meta_title'])) < 3 || utf8_strlen(trim($value['meta_title'])) > 255) {
            // 	$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
            // }

            // -------------------------------------------------------------------------
		}
		
	     	if (isset($this->request->post['product_special'])) {
                foreach ($this->request->post['product_special'] as $special) {
                  // Check if the special price is empty
                if ($special['price'] === '') {
                      $this->error['special_price'] = 'Special Price cannot be empty!';
                    }
                  // Check if the special price is greater than or equal to the MRP
              elseif ($special['price'] >= $this->request->post['price']) {
                $this->error['special_price'] = 'Special Price must be less than the MRP (Price)!';
              }
              }
            }
// // added on 19-02-2025
// 		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
// 			$this->error['model'] = $this->language->get('error_model');
// 		}
// 		if ((utf8_strlen($this->request->post['price']) < 1) || !is_numeric($this->request->post['price'])) {
// 			$this->error['price'] = $this->language->get('error_price');
// 		} else {
// 			unset($this->error['price']);  
// 		}
// 	if ((utf8_strlen(trim($this->request->post['length'])) < 0) || !is_numeric($this->request->post['length'])) {
// 			$this->error['length'] = $this->language->get('error_length');
// 		}


// 		if ((utf8_strlen(trim($this->request->post['width'])) < 0) || !is_numeric($this->request->post['width'])) {
// 			$this->error['width'] = $this->language->get('error_width');
// 		}

// 		if ((utf8_strlen(trim($this->request->post['height'])) < 0) || !is_numeric($this->request->post['height'])) {
// 			$this->error['height'] = $this->language->get('error_height');
// 		}

// 		if ((utf8_strlen(trim($this->request->post['weight'])) < 0) || !is_numeric($this->request->post['weight'])) {
// 			$this->error['weight'] = $this->language->get('error_weight');
// 		}
// // end 

        // added changes regarding to the validation 28-04-2025-------------------
        if (!isset($this->request->post['model']) || utf8_strlen(trim($this->request->post['model'])) < 3 || utf8_strlen(trim($this->request->post['model'])) > 64) {
            	$this->error['model'] = $this->language->get('error_model');
            }
            
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
            
            if(!isset($this->request->post['manufacturer']) || trim($this->request->post['manufacturer']) == ''){
            
            	$this->error['manufacturer'] = $this->language->get('error_manufacturer');
            }
        // ----------------------------------------------------------------------------

// 		if ((utf8_strlen($this->request->post['price']) <= 0)) {
// 			$this->error['price'] = $this->language->get('error_price');
// 		}

        // $price = $this->request->post['price'];
        // // Trim any leading zeros (if any)
        // $price = ltrim($price, '0');
        // // Check if the price is empty or not a valid numeric value or less than or equal to 0
        // if (!is_numeric($price) || $price <= 0) {
        //     $this->error['price'] = $this->language->get('error_price');
        // }

        // $price = $this->request->post['price'];

        // Trim any leading zeros (if any)
        // $price = ltrim($price, '0');
        
        // Check if the price is empty or not a valid numeric value or less than or equal to 0
        // if (!is_numeric($price) || $price <= 0) {
        //     $this->error['price'] = $this->language->get('error_price');
        // }
        
        // Ensure that the 'price' is passed to the template
       // $data['price'] = $price;  // This is important to pass the price value to the template
        
        // In case there's an error, pass the error message
//         if (isset($this->error['price'])) {
//             $data['error_price'] = $this->error['price'];
//         } else {
//             $data['error_price'] = '';  // Clear error if there is no error
//         }

// 		if ((utf8_strlen(trim($this->request->post['length'])) <= 0) || !is_numeric($this->request->post['length'])) {
// 			$this->error['length'] = $this->language->get('error_length');
// 		}
		
// 		if ((utf8_strlen(trim($this->request->post['width'])) <= 0) || !is_numeric($this->request->post['width'])) {
// 			$this->error['width'] = $this->language->get('error_width');
// 		}
		
// 		if ((utf8_strlen(trim($this->request->post['height'])) <= 0) || !is_numeric($this->request->post['height'])) {
// 			$this->error['height'] = $this->language->get('error_height');
// 		}

// 		if ((utf8_strlen(trim($this->request->post['weight'])) <= 0) || !is_numeric($this->request->post['weight'])) {
// 			$this->error['weight'] = $this->language->get('error_weight');
// 		}
		
		// if ($this->error && !isset($this->error['warning'])) {
		// 	$this->error['warning'] = 'Please check the form for errors!';
		// }
		
		// Shubham Sir Changes - 27/05/2025
        // refurbished 
		if (isset($this->request->post['product_condition'])) {
          $data['product_condition'] = $this->request->post['product_condition'];
        } else {
          $data['product_condition'] = 'New'; // fallback
        }
        
        $data['refurbished_description'] = $this->request->post['refurbished_description'] ?? '';
		
// 		added for discount validation on 01-05-2025

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
		
		
		
        // updated on 30-04-2025 ----- regarding to the image validation -------------------------------
        
        // ✅ Main image required
		if (empty($this->request->post['image'])) {
			$this->error['image'] = $this->language->get('error_image');
		}

	
		// ✅ Validate additional product images
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
		
		if ($this->request->post['product_seo_url']) {
			$this->load->model('vendor/seo_url');
			
			foreach ($this->request->post['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}						
						
						$seo_urls = $this->model_vendor_seo_url->getSeoUrlsByKeyword($keyword);
						
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
		
// 		warranty return replace start


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

// 		warranty return replace end
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateCopy() {
		

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('vendor/product');
			$this->load->model('vendor/option');

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
				'limit'        => $limit,
				'vendor_id'   => $this->vendor->getId(),
			);

			$results = $this->model_vendor_product->getProducts($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_vendor_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_vendor_option->getOption($product_option['option_id']);

					if ($option_info) {
						$product_option_value_data = array();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_vendor_option->getOptionValue($product_option_value['option_value_id']);

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

	public function categoryautocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('vendor/category');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_vendor_category->getAllCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function filterautocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('vendor/filter');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$filters = $this->model_vendor_filter->getFilters($filter_data);

			foreach ($filters as $filter) {
				$json[] = array(
					'filter_id' => $filter['filter_id'],
					'name'      => strip_tags(html_entity_decode($filter['group'] . ' &gt; ' . $filter['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	// new code 5 march 2020 //
	public function quickStatus() {
        $json = array();
        $this->load->model('vendor/product');
        $this->load->language('vendor/product');
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $this->model_vendor_product->QuickStatus($this->request->get['status'],$this->request->get['product_id']);
            $json['success'] = $this->language->get('text_statussuccess');
                    
        }                   
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
	// new code 5 march 2020 //
	
	// added on 04-04-2025-------------product comment--------------------------------------------------
	
	public function viewComment() {
		$product_id = (int)$this->request->get['product_id'];

		$this->load->model('vendor/product');
		$data['comments'] = $this->model_vendor_product->getAllComments($product_id);
		$data['product_id'] = $product_id;

		return $this->load->view('vendor/product_comment_modal', $data);
	}

	public function viewCommentModal() {
		$product_id = (int)$this->request->get['product_id'];

		$this->load->model('vendor/product');
		$data['comments'] = $this->model_vendor_product->getAllComments($product_id);
		$data['product_id'] = $product_id;

		return $this->load->view('vendor/product_comment_modal', $data);
	}

	public function replyForm() {
		$this->load->language('vendor/product');
		$this->load->model('vendor/product');
	
		$product_id = $this->request->get['product_id'] ?? 0;
	
		$data['product_id'] = $product_id;
		$data['comments'] = $this->model_vendor_product->getAllComments($product_id);
		$data['action'] = $this->url->link('vendor/product/replySave', 'product_id=' . $product_id . '&user_token=' . $this->session->data['user_token'], true);
	
		$this->response->setOutput($this->load->view('vendor/reply_form', $data));
	}
	
	public function replySave() {
        $this->load->model('vendor/product');
        $product_id = (int)$this->request->get['product_id'];
        $comment = $this->request->post['reply'];
		$vendor_id = $this->vendor->getId(); // Assuming vendor is logged in

        $allowed = ['jpg','jpeg','png','gif','zip','mp4','pdf','doc','docx'];
        $uploaded_files = [];

        if (!empty($_FILES['media']['name'][0])) {
            foreach ($_FILES['media']['name'] as $key => $name) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $tmp = $_FILES['media']['tmp_name'][$key];

                if (!in_array($ext, $allowed)) continue;

                if ($_FILES['media']['size'][$key] <= 10 * 1024 * 1024) {
                    $filename = uniqid('vendor_', true) . '.' . $ext;
                    move_uploaded_file($tmp, DIR_IMAGE . $filename);
                    $uploaded_files[] = $filename;
                }
            }
        }

        $this->model_vendor_product->submitVendorReply($product_id, $comment, $uploaded_files, $vendor_id);

        $this->session->data['success'] = $this->language->get('text_reply_success');
        $this->response->redirect($this->url->link('vendor/product', 'user_token=' . $this->session->data['user_token'], true));
    }
	
	
	public function reply() {
		$this->load->model('vendor/product');
	
		$product_id = $this->request->get['product_id'];
		$comment = $this->request->post['reply'];
		$media = [];
	
		// Validate file uploads
		if (!empty($_FILES['media']['name'][0])) {
			foreach ($_FILES['media']['name'] as $i => $name) {
				$tmp = $_FILES['media']['tmp_name'][$i];
				$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
	
				$allowed = ['jpg', 'jpeg', 'png', 'mp4', 'pdf', 'zip', 'docx'];
				if (in_array($ext, $allowed) && $_FILES['media']['size'][$i] < 5242880) {
					$filename = 'vendor_' . uniqid() . '.' . $ext;
					move_uploaded_file($tmp, DIR_IMAGE . $filename);
					$media[] = $filename;
				}
			}
		}
	
		$this->model_vendor_product->submitVendorReply($product_id, $comment, $media);
	
		// Optional: Notify admin via email
		// $this->sendAdminNotification(...);
	
		$this->response->redirect($this->url->link('vendor/product'));
	}
	//--------------------------------------------------------------
	
	public function ajaxLoadCategories() {
		$query = $this->db->query("SELECT category_id, parent_id, category_name FROM oc_all_category");
		$categories = $query->rows;
	
		$json = [];
		foreach ($categories as $category) {
			$json[] = [
				'category_id'   => $category['category_id'],
				'category_name' => $category['category_name'],
				'parent_id'     => $category['parent_id']
			];
		}
	
		// Fetch selected categories for a specific product ID
		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
			$query_selected = $this->db->query("SELECT category_level_1, category_level_2, category_level_3, category_level_4, category_level_5 FROM oc_vendor_product_category WHERE product_id = '$product_id'");
	
			if ($query_selected->num_rows) {
				$selected_categories = $query_selected->row;
				$json['selected_categories'] = [
					'level_1' => (int)$selected_categories['category_level_1'],
					'level_2' => (int)$selected_categories['category_level_2'],
					'level_3' => (int)$selected_categories['category_level_3'],
					'level_4' => (int)$selected_categories['category_level_4'],
					'level_5' => (int)$selected_categories['category_level_5'],
				];
			} else {
				// Default values if no categories are selected
				$json['selected_categories'] = [
					'level_1' => 0,
					'level_2' => 0,
					'level_3' => 0,
					'level_4' => 0,
					'level_5' => 0,
				];
			}
		}
	
		// Ensure JSON output format
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function loadCategoryLevels(&$data) {
    // Load models
		$this->load->model('vendor/product');

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
		$this->load->language('vendor/product');
		$this->document->setTitle($this->language->get('heading_title'));

		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');

		// Render form
		$this->response->setOutput($this->load->view('vendor/product_form', $data));
	}
}
