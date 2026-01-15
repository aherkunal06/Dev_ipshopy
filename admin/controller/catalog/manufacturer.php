<?php
class ControllerCatalogManufacturer extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/manufacturer');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/manufacturer');

		$this->getList();
	}

// 	public function add() {
// 		$this->load->language('catalog/manufacturer');

// 		$this->document->setTitle($this->language->get('heading_title'));

// 		$this->load->model('catalog/manufacturer');
		
// 		$tempRelativePath = '';

// 		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
// 		  // added code for the handling the declaration on 13/07/2025
// 		  // === Handle Declaration PDF Upload ===
// 			if (!empty($_FILES['declaration_form_file']['name']) && is_uploaded_file($_FILES['declaration_form_file']['tmp_name'])) {
// 				$vendor_id = isset($this->request->post['vendor_id']) ? (int)$this->request->post['vendor_id'] : 0;
// 				$seller_name = isset($this->request->post['seller_name']) ? $this->request->post['seller_name'] : 'unknown';

// 				// Sanitize seller name
// 				$sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($seller_name));

// 				// Create directory
// 				$folderPath = DIR_IMAGE . 'catalog/declaration/' . $vendor_id;
// 				if (!is_dir($folderPath)) {
// 					mkdir($folderPath, 0755, true);
// 				}

// 				// Temp file upload
// 				$temp_filename = 'temp_' . time() . '.pdf';
// 				$tempRelativePath = 'catalog/declaration/' . $vendor_id . '/' . $temp_filename;
// 				$fullTempPath = $folderPath . '/' . $temp_filename;

// 				move_uploaded_file($_FILES['declaration_form_file']['tmp_name'], $fullTempPath);
// 			}

// 			// Pass empty declaration_form path initially
// 			$this->request->post['declaration_form'] = '';
// 		    // ------------ end here------------------------------------
		    
// 			$this->model_catalog_manufacturer->addManufacturer($this->request->post);
			
// 			// If file uploaded, rename it and save in vendor_to_manufacturer 
// 			if (!empty($tempRelativePath)) {
// 				$vendor_id = isset($this->request->post['vendor_id']) ? (int)$this->request->post['vendor_id'] : 0;
// 				$seller_name = isset($this->request->post['seller_name']) ? $this->request->post['seller_name'] : 'unknown';

// 				$sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($seller_name));
// 				$finalRelativePath = 'catalog/declaration/' . $vendor_id . '/' . $sanitized_name . '.pdf';
// 				$finalFullPath = DIR_IMAGE . $finalRelativePath;

// 				rename(DIR_IMAGE . $tempRelativePath, $finalFullPath);

// 				// ✅ Update declaration in vendor_to_manufacturer
// 				// $this->db->query("UPDATE " . DB_PREFIX . "vendor_to_manufacturer SET declaration = '" . $this->db->escape($finalRelativePath) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND vendor_id = '" . (int)$vendor_id . "'");
// 			    $this->model_catalog_manufacturer->updateVendorDeclaration($manufacturer_id,$vendor_id,$finalRelativePath);
			 
// 			}
// 			//----------------- end here -----------------------------------

// 			$this->session->data['success'] = $this->language->get('text_success_add');

// 			$url = '';
			
//             // added changes for manufacturer 25-04-2025-----------------------
            
//             if (isset($this->request->get['filter_name'])) {
// 				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
// 			}
	
// 			if (isset($this->request->get['filter_status'])) {
// 				$url .= '&filter_status=' . $this->request->get['filter_status'];
// 			}
//             // ----------------------------------------------------------------------

// 			if (isset($this->request->get['sort'])) {
// 				$url .= '&sort=' . $this->request->get['sort'];
// 			}

// 			if (isset($this->request->get['order'])) {
// 				$url .= '&order=' . $this->request->get['order'];
// 			}

// 			if (isset($this->request->get['page'])) {
// 				$url .= '&page=' . $this->request->get['page'];
// 			}

// 			$this->response->redirect($this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true));
// 		}

// 		$this->getForm();
// 	}

    //new
    public function add() {
		$this->load->language('catalog/manufacturer');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/manufacturer');

		$tempRelativePath = '';

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			// === Handle Declaration PDF Upload ===
			if (!empty($_FILES['declaration_form_file']['name']) && is_uploaded_file($_FILES['declaration_form_file']['tmp_name'])) {
				$vendor_id = isset($this->request->post['vendor_id']) ? (int)$this->request->post['vendor_id'] : 0;
				$seller_name = isset($this->request->post['seller_name']) ? $this->request->post['seller_name'] : 'unknown';

				// Sanitize seller name
				$sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($seller_name));

				// Create directory
				$folderPath = DIR_IMAGE . 'catalog/declaration/' . $vendor_id;
				if (!is_dir($folderPath)) {
					mkdir($folderPath, 0755, true);
				}

				// Temp file upload
				$temp_filename = 'temp_' . time() . '.pdf';
				$tempRelativePath = 'catalog/declaration/' . $vendor_id . '/' . $temp_filename;
				$fullTempPath = $folderPath . '/' . $temp_filename;

				move_uploaded_file($_FILES['declaration_form_file']['tmp_name'], $fullTempPath);
			}

			// Pass empty declaration_form path initially
			$this->request->post['declaration_form'] = '';
			$manufacturer_id = $this->model_catalog_manufacturer->addManufacturer($this->request->post);

			// If file uploaded, rename it and save in vendor_to_manufacturer
			if (!empty($tempRelativePath)) {
				$vendor_id = isset($this->request->post['vendor_id']) ? (int)$this->request->post['vendor_id'] : 0;
				$seller_name = isset($this->request->post['seller_name']) ? $this->request->post['seller_name'] : 'unknown';

				$sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($seller_name));
				$finalRelativePath = 'catalog/declaration/' . $vendor_id . '/' . $sanitized_name . '.pdf';
				$finalFullPath = DIR_IMAGE . $finalRelativePath;

				rename(DIR_IMAGE . $tempRelativePath, $finalFullPath);

				// ✅ Update declaration in vendor_to_manufacturer
				$this->db->query("UPDATE " . DB_PREFIX . "vendor_to_manufacturer SET declaration = '" . $this->db->escape($finalRelativePath) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND vendor_id = '" . (int)$vendor_id . "'");
			}

			$this->session->data['success'] = $this->language->get('text_success_add');

			$url = '';
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
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

			$this->response->redirect($this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

// 	public function edit() {
// 		$this->load->language('catalog/manufacturer');

// 		$this->document->setTitle($this->language->get('heading_title'));

// 		$this->load->model('catalog/manufacturer');
		
// 		$manufacturer_id = (int)$this->request->get['manufacturer_id'];
//         $vendor_id = (int)$this->request->get['vendor_id'] ? (int)$this->request->get['vendor_id'] : 0 ;
        
//         if (isset($this->request->get['vendor_id'])) {
// 			$vendor_id = (int)$this->request->get['vendor_id'];
// 		} elseif (isset($this->request->post['vendor_id'])) {
// 			$vendor_id = (int)$this->request->post['vendor_id'];
// 		} else {
// 			$vendor_id = 0;
// 		}
    
//         $this->request->get['vendor_id'] = $vendor_id;
        
// 		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
// 		    // added code changes on 13/07/2025 -=------------------------------------------
// 		    // Step 1: Sanitize manufacturer name for filename
// 			$manufacturer_name = !empty($this->request->post['name']) ? $this->request->post['name'] : 'manufacturer';
// 			$sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($manufacturer_name));
// 			$new_filename = $sanitized_name . '.pdf';

// 			$folderPath = DIR_IMAGE . 'catalog/declaration/' . $vendor_id;
// 			$relativePath = 'catalog/declaration/' . $vendor_id . '/' . $new_filename;
// 			$fullPath = $folderPath . '/' . $new_filename;

// 			// Create folder if not exists
// 			if (!is_dir($folderPath)) {
// 				mkdir($folderPath, 0755, true);
// 			}

// 			// Step 2: Handle File Upload
// 			if (!empty($_FILES['declaration_form_file']['name']) && is_uploaded_file($_FILES['declaration_form_file']['tmp_name'])) {
// 				// Remove old file if it exists
// 				if (file_exists($fullPath)) {
// 					unlink($fullPath);
// 				}

// 				// Save new file
// 				move_uploaded_file($_FILES['declaration_form_file']['tmp_name'], $fullPath);
// 				$this->request->post['declaration_form'] = $relativePath;
// 			} else {
// 				// Preserve old file if no new upload
// 				$query = $this->db->query("SELECT declaration FROM " . DB_PREFIX . "vendor_to_manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND vendor_id = '" . (int)$vendor_id . "'");
// 				$this->request->post['declaration_form'] = !empty($query->row['declaration']) ? $query->row['declaration'] : '';
// 			}

// 			// Include vendor_id in post data for saving
// 			$this->request->post['vendor_id'] = $vendor_id;
// 		    //-------------end here --------------------------------------------------------
		    
// 			$this->model_catalog_manufacturer->editManufacturer($this->request->get['manufacturer_id'], $this->request->post);

// 			$this->session->data['success'] = $this->language->get('text_success_edit');

// 			$url = '';
			
//             // added changes for manufacturer on 25-04-2025--------------
//             if (isset($this->request->get['filter_name'])) {
// 				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
// 			}
	
// 			if (isset($this->request->get['filter_status'])) {
// 				$url .= '&filter_status=' . $this->request->get['filter_status'];
// 			}
	
// 			if (isset($this->request->get['filter_approval_status'])) {
// 				$url .= '&filter_approval_status=' . $this->request->get['filter_approval_status'];
// 			}
	
// 			if (isset($this->request->get['filter_comment'])) {
// 				$url .= '&filter_comment=' . urlencode(html_entity_decode($this->request->get['filter_comment'], ENT_QUOTES, 'UTF-8'));
// 			}
//             // ---------------------------------------------------------------

// 			if (isset($this->request->get['sort'])) {
// 				$url .= '&sort=' . $this->request->get['sort'];
// 			}

// 			if (isset($this->request->get['order'])) {
// 				$url .= '&order=' . $this->request->get['order'];
// 			}

// 			if (isset($this->request->get['page'])) {
// 				$url .= '&page=' . $this->request->get['page'];
// 			}
            
//             // Pass vendor_id in redirect as well
// 			$url .= '&vendor_id=' . $vendor_id;
            
// 			$this->response->redirect($this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true));
// 		}

// 		$this->getForm();
// 	}
    // new
    public function edit() {
		$this->load->language('catalog/manufacturer');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/manufacturer');
		


		$manufacturer_id = (int)$this->request->get['manufacturer_id'];

		$vendor_id = isset($this->request->get['vendor_id']) ? (int)$this->request->get['vendor_id'] : 0;
		
		if (isset($this->request->get['vendor_id'])) {
			$vendor_id = (int)$this->request->get['vendor_id'];
		} elseif (isset($this->request->post['vendor_id'])) {
			$vendor_id = (int)$this->request->post['vendor_id'];
		} else {
			$vendor_id = 0;
		}

		$this->request->get['vendor_id'] = $vendor_id;


		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			
			// Step 1: Sanitize manufacturer name for filename
			$manufacturer_name = !empty($this->request->post['name']) ? $this->request->post['name'] : 'manufacturer';
			$sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($manufacturer_name));
			$new_filename = $sanitized_name . '.pdf';

			$folderPath = DIR_IMAGE . 'catalog/declaration/' . $vendor_id;
			$relativePath = 'catalog/declaration/' . $vendor_id . '/' . $new_filename;
			$fullPath = $folderPath . '/' . $new_filename;

			// Create folder if not exists
			if (!is_dir($folderPath)) {
				mkdir($folderPath, 0755, true);
			}

			// Step 2: Handle File Upload
			if (!empty($_FILES['declaration_form_file']['name']) && is_uploaded_file($_FILES['declaration_form_file']['tmp_name'])) {
				// Remove old file if it exists
				if (file_exists($fullPath)) {
					unlink($fullPath);
				}

				// Save new file
				move_uploaded_file($_FILES['declaration_form_file']['tmp_name'], $fullPath);
				$this->request->post['declaration_form'] = $relativePath;
			} else {
				// Preserve old file if no new upload
				$query = $this->db->query("SELECT declaration FROM " . DB_PREFIX . "vendor_to_manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND vendor_id = '" . (int)$vendor_id . "'");
				$this->request->post['declaration_form'] = !empty($query->row['declaration']) ? $query->row['declaration'] : '';
			}

			// Include vendor_id in post data for saving
			$this->request->post['vendor_id'] = $vendor_id;

			// Save manufacturer data
			$this->model_catalog_manufacturer->editManufacturer($manufacturer_id, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success_edit');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_approval_status'])) {
				$url .= '&filter_approval_status=' . $this->request->get['filter_approval_status'];
			}

			if (isset($this->request->get['filter_comment'])) {
				$url .= '&filter_comment=' . urlencode(html_entity_decode($this->request->get['filter_comment'], ENT_QUOTES, 'UTF-8'));
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

			// Pass vendor_id in redirect as well
			$url .= '&vendor_id=' . $vendor_id;

			$this->response->redirect($this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}
    
	public function delete() {
		$this->load->language('catalog/manufacturer');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $manufacturer_id) {
				$this->model_catalog_manufacturer->deleteManufacturer($manufacturer_id);
			}

			$this->session->data['success'] = $this->language->get('text_success_delete');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}
	
	// new function added for manufacturer 25-04-2025----------------------------

// 	public function status() {
// 		$this->load->language('catalog/manufacturer');
// 		$this->document->setTitle($this->language->get('heading_title'));
	
// 		$this->load->model('catalog/manufacturer');
	
// 		$status_ids = array();
	
// 		// Get selected manufacturers from POST
// 		if (isset($this->request->post['selected'])) {
// 			$status_ids = $this->request->post['selected'];
// 		}
// 		// Or single manufacturer from GET
// 		elseif (isset($this->request->get['manufacturer_id'])) {
// 			$status_ids[] = $this->request->get['manufacturer_id'];
// 		}
	
// 		// Validate and apply status changes
// 		if ($status_ids && $this->validateStatus()) {
// 			foreach ($status_ids as $manufacturer_id) {
// 				$this->model_catalog_manufacturer->status($manufacturer_id);
// 			}
	
// 			$this->session->data['success'] = $this->language->get('text_success');
	
// 			// Build redirect URL
// 			$url = '';
	
// 			if (isset($this->request->get['sort'])) {
// 				$url .= '&sort=' . $this->request->get['sort'];
// 			}
	
// 			if (isset($this->request->get['order'])) {
// 				$url .= '&order=' . $this->request->get['order'];
// 			}
	
// 			if (isset($this->request->get['page'])) {
// 				$url .= '&page=' . $this->request->get['page'];
// 			}
	
// 			// Redirect back to manufacturer list
// 			$this->response->redirect($this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true));
// 		}
	
// 		// Show manufacturer list again if status not set or invalid
// 		$this->getList();
// 	}
    public function status() {
    	$this->load->language('catalog/manufacturer');
    	$this->document->setTitle($this->language->get('heading_title'));
    
    	$this->load->model('catalog/manufacturer');
    
    	$status_ids = array();
    	$vendor_id = isset($this->request->get['vendor_id']) ? (int)$this->request->get['vendor_id'] : 0;
    
    	if (isset($this->request->post['selected'])) {
    		$status_ids = $this->request->post['selected'];
    	} elseif (isset($this->request->get['manufacturer_id'])) {
    		$status_ids[] = $this->request->get['manufacturer_id'];
    	}
    
    	// ✅ FIXED THIS LINE
    	if ($status_ids && $this->validateStatus()) {
    		foreach ($status_ids as $manufacturer_id) {
    			$this->model_catalog_manufacturer->status($manufacturer_id, $vendor_id);
    		}
    
    		$this->session->data['success'] = $this->language->get('text_success');
    
    		$url = '';
    		if (isset($this->request->get['sort'])) {
    			$url .= '&sort=' . $this->request->get['sort'];
    		}
    		if (isset($this->request->get['order'])) {
    			$url .= '&order=' . $this->request->get['order'];
    		}
    		if (isset($this->request->get['page'])) {
    			$url .= '&page=' . $this->request->get['page'];
    		}
    
    		$this->response->redirect($this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true));
    	}
    
    	$this->getList();
    }

	// ===============================================================================

	protected function getList() {
	    
	   //  added changes for the manufacturer on 25-04-2025---------------------
	   // new function added by shubham 19-04-2025

    	$this->load->model('tool/image');
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/product');
	
		// 1. Handle Filters, Sorting, and Pagination
		$filter_name = $this->request->get['filter_name'] ?? null;
		$filter_approval_status = $this->request->get['filter_approval_status'] ?? null;

		// added by shubham for filter

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_vendor'])) {
			$filter_vendor = $this->request->get['filter_vendor'];
		} else {
			$filter_vendor = null;
		}
		/* 11 02 2020 */
		if (isset($this->request->get['filter_vendor1'])) {
			$filter_vendor1 = $this->request->get['filter_vendor1'];
		} else {
			$filter_vendor1 = null;
		}
		/* 11 02 2020 */

		// ==========---------------------------------------


		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}

	// =============================================================
	   //-----------------------------------------------------------------------------------
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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
		
		// added for manufacturer on 25-04-2025================================================

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}
		
		
		if (isset($this->request->get['filter_vendor1'])) {
			$url .= '&filter_vendor1=' . $this->request->get['filter_vendor1'];
		}
		

		// ----==============================-------------------------

// 		if (isset($this->request->get['sort'])) {
// 			$url .= '&sort=' . $this->request->get['sort'];
// 		}

// 		if (isset($this->request->get['order'])) {
// 			$url .= '&order=' . $this->request->get['order'];
// 		}

// 		if (isset($this->request->get['page'])) {
// 			$url .= '&page=' . $this->request->get['page'];
// 		}
        
        // ---- added changes for manufacturer on 25-04-2025 ==============================-------------------------
		foreach (['filter_name', 'filter_approval_status', 'filter_vendor','filter_status', 'filter_vendor1', 'sort', 'order', 'page'] as $param) {
			if (isset($this->request->get[$param])) {
				$url .= '&' . $param . '=' . urlencode($this->request->get[$param]);
			}
		}
        // --------------------------------------------------------------------------------
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/manufacturer/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/manufacturer/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['manufacturers'] = array();

		$filter_data = array(
		  //  --- added changes for the manufacturer on 25-04-2025---
		  'filter_name'            => $filter_name,
			'filter_status'          => $filter_status,
			'filter_approval_status' => $filter_approval_status,
			'filter_vendor'          => $filter_vendor,
			'filter_vendor1'         => $filter_vendor1,
		  //  ------------------------------------------------
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$manufacturer_total = $this->model_catalog_manufacturer->getTotalManufacturers($filter_data);

		$results = $this->model_catalog_manufacturer->getManufacturers($filter_data);

		foreach ($results as $result) {
		  //   added changs for the manufacturer on 25-04-2025------------------------
		    $image = (!empty($result['image']) && is_file(DIR_IMAGE . $result['image']))
				? $this->model_tool_image->resize($result['image'], 40, 40)
				: $this->model_tool_image->resize('no_image.png', 40, 40);

				if (!$result['status'] || $result['status'] == 2) {
				// 	$statuss = $this->url->link('catalog/manufacturer/status', 'user_token=' . $this->session->data['user_token'] . '&manufacturer_id=' . $result['manufacturer_id'] . $url, true);
				$statuss = $this->url->link(
            		'catalog/manufacturer/status',
            		'user_token=' . $this->session->data['user_token'] .
            		'&manufacturer_id=' . $result['manufacturer_id'] .
            		'&vendor_id=' . $result['vendor_id'] . $url,
            		true
            	);

				} else {
					$statuss = '';
				}
				
				$comment_data = $this->model_catalog_manufacturer->getManufacturerCommentThread($result['manufacturer_id']); // You need to create this in model
				
				if ($result['status'] == 2) {
					$cstatus = "Approval Pending";
				} elseif ($result['status'] == 1) {
					$cstatus = "Enabled";
				} elseif ($result['status'] == 0) {
					$cstatus = "Disabled";
				} else {
					$cstatus = '';
				}
				
	
    			$approval_status = $result['approval_status'] ?? '';
    			$approval_comment = $result['approval_comment'] ?? '';
    	
    			switch ($approval_status) {
    				case '0': $approval_status_text = 'Pending'; break;
    				case '1': $approval_status_text = 'Approved'; break;
    				case '2': $approval_status_text = 'Rejected'; break;
    				default:  $approval_status_text = 'N/A'; break;
    			}
    	
    			$vendor_id = $result['vendor_id'] ?? 0;
    			
    			$sellers = $this->model_vendor_vendor->getVendor($result['vendor_id']);
    			/* 19 02 2020 */
    			if(!empty($sellers['firstname'])){
    				$firstname = $sellers['firstname'];
    			} else {
    				$firstname ='';
    			}
    			if(!empty($sellers['lastname'])){
    				$lastname = $sellers['lastname'];
    			} else {
    				$lastname ='';
    			}
    			
    			
    			$sellername = $firstname.' '.$lastname;
    			$latest_comment = $this->model_catalog_manufacturer->getLatestAdminCommentForManufacturer($result['manufacturer_id']);

			// =========================================================================
			$this->load->model('catalog/manufacturer');
            //------------------------------------------------------------------
		    
			$data['manufacturers'][] = array(
				'manufacturer_id' => $result['manufacturer_id'],
				// ------- added for manufacturer on 25-04-2025------------------------
				'image'            => $image,
				'cstatus'      => $cstatus,
				'statuss'	 => $statuss,
				'view_declaration_url' => !empty($result['declaration']) ? HTTP_CATALOG . 'image/' . $result['declaration'] : '',
                //----- added changes
	            'comment_thread'   => $comment_data, // Keep this if you're using same logic for threads
                'latest_comment'   => $latest_comment, // Same logic for fetching latest
                
                'view_comment_url' => $this->url->link(
                    'catalog/manufacturer/viewManufacturerComment',
                    'user_token=' . $this->session->data['user_token'] . '&manufacturer_id=' . $result['manufacturer_id'],
                    true
                ),

				'approval_status'  => $approval_status_text,
				'approval_comment' => $approval_comment,
				'sellername'       => $sellername,
				'vendorstorename'  => $result['vendorstorename'] ?? '',
				'sellerpage'       => $this->url->link('vendor/vendor/edit', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $vendor_id, true),
				// ------------------------------------------------------------------------------------
				'name'            => $result['name'],
				'sort_order'      => $result['sort_order'],
				// 'edit'            => $this->url->link('catalog/manufacturer/edit', 'user_token=' . $this->session->data['user_token'] . '&manufacturer_id=' . $result['manufacturer_id'] . $url, true)
				'edit' => $this->url->link('catalog/manufacturer/edit','user_token=' . $this->session->data['user_token'] .'&manufacturer_id=' . $result['manufacturer_id'] .'&vendor_id=' . $result['vendor_id'] . $url,true
                )
				
			    
			);
		}

        //  added changes for manufactiurer 25-04-2025 -------------------------------------------------------
            // 8. Set Filters in View
    		$data['filter_name'] = $filter_name;
    		$data['filter_approval_status'] = $filter_approval_status;
    		$data['filter_vendor'] = $filter_vendor;
    		
    		$data['filter_vendor1'] = $filter_vendor1;
    		$data['sort'] = $sort;
    		
    		// added by shuham 23-04-2025======
    		$data['user_token'] = $this->session->data['user_token'];
        // ----------------------------------
        // -----------------------------------------------------------------------------------------
        
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

// 		if ($order == 'ASC') {
// 			$url .= '&order=DESC';
// 		} else {
// 			$url .= '&order=ASC';
// 		}
        // added changes on 25-04-2025---------------------------------
        // 10. Sorting URLs
		$sort_url = ($order == 'ASC') ? '&order=DESC' : '&order=ASC';
		foreach (['filter_name', 'filter_approval_status', 'page'] as $param) {
			if (isset($this->request->get[$param])) {
				$sort_url .= '&' . $param . '=' . urlencode($this->request->get[$param]);
			}
		}
        // ---------------------------------------------------------

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_sort_order'] = $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);

		$url = '';

// 		if (isset($this->request->get['sort'])) {
// 			$url .= '&sort=' . $this->request->get['sort'];
// 		}

// 		if (isset($this->request->get['order'])) {
// 			$url .= '&order=' . $this->request->get['order'];
// 		}
        // --- added changes for the manufacturer 25-04-2025-----------
        
        // added by shubham =====================
		$this->load->model('vendor/vendor');
		if(isset($data['filter_vendor'])) {
			$vendor_info = $this->model_vendor_vendor->getVendor($data['filter_vendor']);
		}
		/* 23 04 2025 update vname */
		if(isset($vendor_info['vname'])) {
			$data['sellernme'] = $vendor_info['vname'];
		} else {
			$data['sellernme'] ='';
		}

		// -----------------====================-------------------
        // -------------------------------------------------------------------
        
		$pagination = new Pagination();
		$pagination->total = $manufacturer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($manufacturer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($manufacturer_total - $this->config->get('config_limit_admin'))) ? $manufacturer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $manufacturer_total, ceil($manufacturer_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/manufacturer_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['manufacturer_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        // for edit manufacturer  added code changes on 14/07/2025
		$data['vendor_id'] = isset($this->request->post['vendor_id']) ? $this->request->post['vendor_id'] : '';
		$data['vendor_id'] = isset($this->request->get['vendor_id']) ? $this->request->get['vendor_id'] : 0;

		
		$data['seller_name'] = isset($this->request->post['seller_name']) ? $this->request->post['seller_name'] : '';

		// ---=============

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

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

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
			'href' => $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['manufacturer_id'])) {
			$data['action'] = $this->url->link('catalog/manufacturer/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/manufacturer/edit', 'user_token=' . $this->session->data['user_token'] . '&manufacturer_id=' . $this->request->get['manufacturer_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['manufacturer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id'], $data['vendor_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($manufacturer_info)) {
			$data['name'] = $manufacturer_info['name'];
		} else {
			$data['name'] = '';
		}
		
		// added new code for the edit the manufacturer on 14/07/2025
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($manufacturer_info)) {
			$data['status'] = $manufacturer_info['status'];
		} else {
			$data['status'] = 2;
		}
		
        if(isset($this->request->post['seller_name'])){
          $data['seller_name'] = $this->request->post['seller_name'];
        }elseif(!empty($manufacturer_info)){
          $data['seller_name'] = $manufacturer_info['seller_name'];
        }else{
          $data['seller_name'] = '';
        }


		if (isset($this->request->post['declaration'])) {
			$data['declaration'] = $this->request->post['declaration'];
		} elseif (!empty($manufacturer_info['declaration'])) {
			$data['declaration'] = $manufacturer_info['declaration'];
		} else {
			$data['declaration'] = '';
		}

		if (!empty($data['declaration']) && !is_file(DIR_IMAGE . $data['declaration'])) {
			$data['declaration'] = ''; // Prevent broken link if file is missing
		}

        // ==================------------------------------------

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

		if (isset($this->request->post['manufacturer_store'])) {
			$data['manufacturer_store'] = $this->request->post['manufacturer_store'];
		} elseif (isset($this->request->get['manufacturer_id'])) {
			$data['manufacturer_store'] = $this->model_catalog_manufacturer->getManufacturerStores($this->request->get['manufacturer_id']);
		} else {
			$data['manufacturer_store'] = array(0);
		}

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($manufacturer_info)) {
			$data['image'] = $manufacturer_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($manufacturer_info) && is_file(DIR_IMAGE . $manufacturer_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($manufacturer_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($manufacturer_info)) {
			$data['sort_order'] = $manufacturer_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['manufacturer_seo_url'])) {
			$data['manufacturer_seo_url'] = $this->request->post['manufacturer_seo_url'];
		} elseif (isset($this->request->get['manufacturer_id'])) {
			$data['manufacturer_seo_url'] = $this->model_catalog_manufacturer->getManufacturerSeoUrls($this->request->get['manufacturer_id']);
		} else {
			$data['manufacturer_seo_url'] = array();
		}
				
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/manufacturer_form', $data));
	}
    // protected function getForm() {
    // 		$data['text_form'] = !isset($this->request->get['manufacturer_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
    
    // 		if (isset($this->error['warning'])) {
    // 			$data['error_warning'] = $this->error['warning'];
    // 		} else {
    // 			$data['error_warning'] = '';
    // 		}
    
    // 		if (isset($this->error['name'])) {
    // 			$data['error_name'] = $this->error['name'];
    // 		} else {
    // 			$data['error_name'] = '';
    // 		}
    
    // 		if (isset($this->error['keyword'])) {
    // 			$data['error_keyword'] = $this->error['keyword'];
    // 		} else {
    // 			$data['error_keyword'] = '';
    // 		}
    
    // 		$url = '';
    
    // 		if (isset($this->request->get['sort'])) {
    // 			$url .= '&sort=' . $this->request->get['sort'];
    // 		}
    
    // 		if (isset($this->request->get['order'])) {
    // 			$url .= '&order=' . $this->request->get['order'];
    // 		}
    
    // 		if (isset($this->request->get['page'])) {
    // 			$url .= '&page=' . $this->request->get['page'];
    // 		}
    
    // 		$data['breadcrumbs'] = array();
    
    // 		$data['breadcrumbs'][] = array(
    // 			'text' => $this->language->get('text_home'),
    // 			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    // 		);
    
    // 		$data['breadcrumbs'][] = array(
    // 			'text' => $this->language->get('heading_title'),
    // 			'href' => $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true)
    // 		);
    
    // 		// if (!isset($this->request->get['manufacturer_id'])) {
    // 		// 	$data['action'] = $this->url->link('catalog/manufacturer/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
    // 		// } else {
    // 		// 	$data['action'] = $this->url->link('catalog/manufacturer/edit', 'user_token=' . $this->session->data['user_token'] . '&manufacturer_id=' . $this->request->get['manufacturer_id'] . $url, true);
    // 		// }
    // 		$vendor_id = isset($this->request->get['vendor_id']) ? (int)$this->request->get['vendor_id'] : 0;
    
    // if ($vendor_id) {
    //     $url .= '&vendor_id=' . $vendor_id;
    // }
    
    // if (!isset($this->request->get['manufacturer_id'])) {
    //     $data['action'] = $this->url->link(
    //         'catalog/manufacturer/add',
    //         'user_token=' . $this->session->data['user_token'] . $url,
    //         true
    //     );
    // } else {
    //     $data['action'] = $this->url->link(
    //         'catalog/manufacturer/edit',
    //         'user_token=' . $this->session->data['user_token'] .
    //         '&manufacturer_id=' . (int)$this->request->get['manufacturer_id'] . $url,
    //         true
    //     );
    // }
    
    
    // 		$data['cancel'] = $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'] . $url, true);
    
    // 		if (isset($this->request->get['manufacturer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
    // 			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id'],$this->request->get['vendor_id']);
    // 		}
    
    // 		$data['user_token'] = $this->session->data['user_token'];
    
    // 		if (isset($this->request->post['name'])) {
    // 			$data['name'] = $this->request->post['name'];
    // 		} elseif (!empty($manufacturer_info)) {
    // 			$data['name'] = $manufacturer_info['name'];
    // 		} else {
    // 			$data['name'] = '';
    // 		}
    
    // 		$this->load->model('setting/store');
    
    // 		$data['stores'] = array();
    		
    // 		$data['stores'][] = array(
    // 			'store_id' => 0,
    // 			'name'     => $this->language->get('text_default')
    // 		);
    		
    // 		$stores = $this->model_setting_store->getStores();
    
    // 		foreach ($stores as $store) {
    // 			$data['stores'][] = array(
    // 				'store_id' => $store['store_id'],
    // 				'name'     => $store['name']
    // 			);
    // 		}
    
    // 		if (isset($this->request->post['manufacturer_store'])) {
    // 			$data['manufacturer_store'] = $this->request->post['manufacturer_store'];
    // 		} elseif (isset($this->request->get['manufacturer_id'])) {
    // 			$data['manufacturer_store'] = $this->model_catalog_manufacturer->getManufacturerStores($this->request->get['manufacturer_id']);
    // 		} else {
    // 			$data['manufacturer_store'] = array(0);
    // 		}
    
    // 		if (isset($this->request->post['image'])) {
    // 			$data['image'] = $this->request->post['image'];
    // 		} elseif (!empty($manufacturer_info)) {
    // 			$data['image'] = $manufacturer_info['image'];
    // 		} else {
    // 			$data['image'] = '';
    // 		}
    
    // 		$this->load->model('tool/image');
    
    // 		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
    // 			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
    // 		} elseif (!empty($manufacturer_info) && is_file(DIR_IMAGE . $manufacturer_info['image'])) {
    // 			$data['thumb'] = $this->model_tool_image->resize($manufacturer_info['image'], 100, 100);
    // 		} else {
    // 			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
    // 		}
    
    // 		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
    
    // 		if (isset($this->request->post['sort_order'])) {
    // 			$data['sort_order'] = $this->request->post['sort_order'];
    // 		} elseif (!empty($manufacturer_info)) {
    // 			$data['sort_order'] = $manufacturer_info['sort_order'];
    // 		} else {
    // 			$data['sort_order'] = '';
    // 		}
    
    // 		$this->load->model('localisation/language');
    
    // 		$data['languages'] = $this->model_localisation_language->getLanguages();
    		
    // 		if (isset($this->request->post['manufacturer_seo_url'])) {
    // 			$data['manufacturer_seo_url'] = $this->request->post['manufacturer_seo_url'];
    // 		} elseif (isset($this->request->get['manufacturer_id'])) {
    // 			$data['manufacturer_seo_url'] = $this->model_catalog_manufacturer->getManufacturerSeoUrls($this->request->get['manufacturer_id']);
    // 		} else {
    // 			$data['manufacturer_seo_url'] = array();
    // 		}
    				
    // 		$this->load->model('catalog/manufacturer');
    
    // // Check if editing an existing manufacturer
    // if (isset($this->request->get['manufacturer_id'])) {
    // 	$data['assigned_vendors'] = $this->model_catalog_manufacturer->getVendorsByManufacturer($this->request->get['manufacturer_id']);
    // } else {
    // 	$data['assigned_vendors'] = array();
    // }
    
    // 		$data['header'] = $this->load->controller('common/header');
    // 		$data['column_left'] = $this->load->controller('common/column_left');
    // 		$data['footer'] = $this->load->controller('common/footer');
    
    // 		$this->response->setOutput($this->load->view('catalog/manufacturer_form', $data));
    // 	}

    //  added changes for the mqanufacturer on 25-04-2025---
    // added by shubham 19-04-2025
	protected function validateStatus() {
		if (!$this->user->hasPermission('modify', 'catalog/manufacturer')) {
			$this->error['warning'] = $this->language->get('error_permission');
			return false;
		}
		return true;
	}

	// ----------------------------
    // -----------------------------------------------------

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/manufacturer')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ($this->request->post['manufacturer_seo_url']) {
			$this->load->model('design/seo_url');
			
			foreach ($this->request->post['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}							
						
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
						
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['manufacturer_id']) || (($seo_url['query'] != 'manufacturer_id=' . $this->request->get['manufacturer_id'])))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
							}
						}
					}
				}
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/manufacturer')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('catalog/product');

		foreach ($this->request->post['selected'] as $manufacturer_id) {
			$product_total = $this->model_catalog_product->getTotalProductsByManufacturerId($manufacturer_id);

			if ($product_total) {
				$this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
			}
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/manufacturer');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_catalog_manufacturer->getManufacturers($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'manufacturer_id' => $result['manufacturer_id'],
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}
		
		if (isset($this->request->get['seller_id'])) {
			$this->load->model('catalog/manufacturer');

			$results = $this->model_catalog_manufacturer->getManufacturersByVendor($this->request->get['seller_id']);

			foreach ($results as $result) {
				$json[] = array(
					'manufacturer_id' => $result['manufacturer_id'],
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
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
	
    // 	 added changes for manufacturer comments 25-04-2025----------
   
	//  manufacturer approval comment
    public function saveManufacturerComment() {
    	$json = [];
    	if ($this->request->server['REQUEST_METHOD'] == 'POST') {
    		$manufacturer_id = $this->request->post['manufacturer_id'];
    		$comment = $this->request->post['comment'];
    
    		$this->load->model('catalog/manufacturer');
    		$this->model_catalog_manufacturer->submitAdminComment($manufacturer_id, $comment);
    
    		$json['success'] = 'Comment saved successfully.';
    	}
    	$this->response->addHeader('Content-Type: application/json');
    	$this->response->setOutput(json_encode($json));
    }
    
    public function getManufacturerCommentThread() {
    	$this->load->model('catalog/manufacturer');
    	$manufacturer_id = $this->request->get['manufacturer_id'];
    	$comments = $this->model_catalog_manufacturer->getAllComments($manufacturer_id);
    	$this->response->addHeader('Content-Type: application/json');
    	$this->response->setOutput(json_encode($comments));
    }
    
    public function replyManufacturer() {
    	$this->load->model('catalog/manufacturer');
    
    	$manufacturer_id = $this->request->get['manufacturer_id'];
    	$comment = $this->request->post['reply'];
    	$uploaded_files = [];
    
    	if (!empty($_FILES['media']['name'][0])) {
    		foreach ($_FILES['media']['name'] as $key => $name) {
    			$tmp_name = $_FILES['media']['tmp_name'][$key];
    			$ext = pathinfo($name, PATHINFO_EXTENSION);
    			$filename = uniqid('catalog_', true) . '.' . $ext;
    			move_uploaded_file($tmp_name, DIR_IMAGE . $filename);
    			$uploaded_files[] = $filename;
    		}
    	}
    
    	$this->model_catalog_manufacturer->submitVendorReply($manufacturer_id, $comment, $uploaded_files);
    
    	$this->response->redirect($this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    public function viewManufacturerComment() {
    	$this->load->language('catalog/manufacturer');
    	$this->load->model('catalog/manufacturer');
    
    	$manufacturer_id = isset($this->request->get['manufacturer_id']) ? (int)$this->request->get['manufacturer_id'] : 0;
    
    	$comments = $this->model_catalog_manufacturer->getAllManufacturerComments($manufacturer_id);
    
    	foreach ($comments as &$comment) {
    		$comment['media'] = [];
    
    		if (!empty($comment['media_files'])) {
    			$comment['media'] = explode(',', $comment['media_files']);
    		}
    	}
    
    	$data['comments'] = $comments;
    	$data['allow_reply'] = true;
    	$data['reply_action'] = $this->url->link('catalog/manufacturer/replyManufacturer', 'manufacturer_id=' . $manufacturer_id . '&user_token=' . $this->session->data['user_token'], true);
    
    	$this->response->setOutput($this->load->view('catalog/manufacturer_comment_modal', $data));
    }
    
    // // Save vendor reply and media ================
    
    // public function submitVendorReply($manufacturer_id, $comment, $media_files = []) {
    // 	$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_approval_comments SET 
    // 		manufacturer_id = '" . (int)$manufacturer_id . "', 
    // 		comment_by = 'admin', 
    // 		comment = '" . $this->db->escape($comment) . "', 
    // 		date_added = NOW()");
    	
    // 	$comment_id = $this->db->getLastId();
    
    // 	if (!empty($media_files)) {
    // 		foreach ($media_files as $file) {
    // 			$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_comment_media SET 
    // 				comment_id = '" . (int)$comment_id . "', 
    // 				file = '" . $this->db->escape($file) . "'");
    // 		}
    // 	}
    // }
    
    public function getAllComments($manufacturer_id) {
    	$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_approval_comments WHERE manufacturer_id = '" . (int)$manufacturer_id . "' ORDER BY date_added ASC");
    
    	$comments = $query->rows;
    
    	foreach ($comments as &$comment) {
    		$media_query = $this->db->query("SELECT file FROM " . DB_PREFIX . "manufacturer_comment_media WHERE comment_id = '" . (int)$comment['id'] . "'");
    		$comment['media'] = array_column($media_query->rows, 'file');
    	}
    
    	return $comments;
    }
    
    // --------------------------------
    
    
    public function replyformManufacturer() {
    	$this->load->language('catalog/manufacturer');
    	$this->load->model('catalog/manufacturer');
    
    	$manufacturer_id = $this->request->get['manufacturer_id'];
    	$data['comments'] = $this->model_catalog_manufacturer->getAllComments($manufacturer_id);
    
    	$data['reply_action'] = $this->url->link('catalog/manufacturer/replyManufacturer', 'manufacturer_id=' . $manufacturer_id . '&user_token=' . $this->session->data['user_token'], true);
    	$data['back'] = $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'], true);
    
    	$this->response->setOutput($this->load->view('catalog/manufacturer_reply_form', $data));
    }
    
    protected function sendAdminNotificationForManufacturer($manufacturer_id, $comment) {
    	$mail = new Mail();
    	$mail->protocol = $this->config->get('config_mail_protocol');
    	$mail->parameter = $this->config->get('config_mail_parameter');
    	$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
    	$mail->smtp_username = $this->config->get('config_mail_smtp_username');
    	$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
    	$mail->smtp_port = $this->config->get('config_mail_smtp_port');
    	$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
    
    	$mail->setTo($this->config->get('config_email'));
    	$mail->setFrom($this->config->get('config_email'));
    	$mail->setSender($this->config->get('config_name'));
    	$mail->setSubject("New Vendor Reply on Manufacturer #$manufacturer_id");
    	$mail->setText("Vendor replied: " . $comment);
    	$mail->send();
    }
    
    
    public function uploadDeclaration() {
        $this->load->language('tool/upload');
    
        $json = [];
    
        // Clean output buffering to prevent hidden output before JSON
        if (ob_get_level()) {
            ob_end_clean();
        }
    
        // Basic file checks
        if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
            $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));
    
            if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
                $json['error'] = $this->language->get('error_filename');
            }
    
            $allowed = array_map('trim', explode("\n", preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'))));
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
            if (!in_array($file_ext, $allowed)) {
                $json['error'] = $this->language->get('error_filetype');
            }
    
            $allowed_mime = array_map('trim', explode("\n", preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'))));
            if (!in_array($this->request->files['file']['type'], $allowed_mime)) {
                $json['error'] = $this->language->get('error_filetype');
            }
    
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
            $vendor_id = isset($this->vendor) && method_exists($this->vendor, 'getId') ? (int)$this->vendor->getId() : 0;
    
            if ($vendor_id <= 0) {
                $json['error'] = 'Vendor not logged in or invalid vendor ID!';
            } elseif (isset($this->request->get['manufacturer_id']) && (int)$this->request->get['manufacturer_id'] > 0) {
                $manufacturer_id = (int)$this->request->get['manufacturer_id'];
    
                $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
                if ($query->num_rows) {
                    $manufacturer_name = $query->row['name'];
                    $sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($manufacturer_name));
    
                    $upload_dir = DIR_IMAGE . 'catalog/declaration/' . $vendor_id;
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
    
                    $new_filename = $sanitized_name . '.pdf';
                    $final_path = $upload_dir . '/' . $new_filename;
                    $relative_path = 'catalog/declaration/' . $vendor_id . '/' . $new_filename;
    
                    move_uploaded_file($this->request->files['file']['tmp_name'], $final_path);
    
                    $json['success'] = $this->language->get('text_upload');
                    $json['location1'] = $relative_path;
                    $json['image_url'] = HTTP_CATALOG; // Provide image URL base
                } else {
                    $json['error'] = 'Manufacturer not found in database!';
                }
            } else {
                $json['error'] = 'Manufacturer ID is missing!';
            }
        }
    
        // Make sure no output before this point
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // new autocomplete for seller
    public function autocompleteSeller() {
        $json = [];
    
        if (isset($this->request->get['filter_name'])) {
            $this->load->model('catalog/manufacturer');
    
            $filter_name = $this->request->get['filter_name'];
    
            $results = $this->model_catalog_manufacturer->getVendorsByName($filter_name);
    
            foreach ($results as $result) {
                $json[] = [
                    'vendor_id' => $result['vendor_id'],
                    'name'      => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')
                ];
            }
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    
    // -----------------------------------------------------------------------
}