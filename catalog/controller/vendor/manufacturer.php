<?php
class ControllerVendorManufacturer extends Controller {
	private $error = array();

	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		$this->load->language('vendor/manufacturer');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/manufacturer');

		$this->getList();
	}

	public function add() {
		$this->load->language('vendor/manufacturer');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/manufacturer');
		
		

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
		    
		  //  Shubham Sir Changes --------------
		    $tempRelativePath = '';
            if (!empty($_FILES['declaration_form_file']['name']) && is_uploaded_file($_FILES['declaration_form_file']['tmp_name'])) {
                $vendor_id = $this->vendor->getId();
                $temp_filename = 'temp_' . time() . '.pdf';
                $folderPath = DIR_IMAGE . 'catalog/declaration/' . $vendor_id;
    
                if (!is_dir($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }
    
                $tempRelativePath = 'catalog/declaration/' . $vendor_id . '/' . $temp_filename;
                $fullTempPath = $folderPath . '/' . $temp_filename;
    
                move_uploaded_file($_FILES['declaration_form_file']['tmp_name'], $fullTempPath);
            }
            
            // Step 2: Add manufacturer and get ID
            $this->request->post['declaration_form'] = ''; // Default, updated below
			$manufacturer_id = $this->model_vendor_manufacturer->addManufacturer($this->request->post);
			
    			// Step 3: If a file was uploaded, rename it using the manufacturer name
            if (!empty($tempRelativePath)) {
                $vendor_id = $this->vendor->getId();
    
                // ✅ Fetch manufacturer name from DB
                $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
    
                if ($query->num_rows) {
                    $manufacturer_name = $query->row['name'];
    
                    // ✅ Sanitize name: remove spaces/special characters
                    $sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($manufacturer_name));
    
                    // ✅ Rename and update
                    $finalRelativePath = 'catalog/declaration/' . $vendor_id . '/' . $sanitized_name . '.pdf';
                    $finalFullPath = DIR_IMAGE . $finalRelativePath;
    
                    // Rename file
                    rename(DIR_IMAGE . $tempRelativePath, $finalFullPath);
    
                    // Save path in DB
                    $this->db->query("UPDATE " . DB_PREFIX . "vendor_to_manufacturer SET declaration = '" . $this->db->escape($finalRelativePath) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "' and vendor_id='".$vendor_id."'");
                }
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

			$this->response->redirect($this->url->link('vendor/manufacturer', '', true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('vendor/manufacturer');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/manufacturer');
		
		$manufacturer_id = (int)$this->request->get['manufacturer_id'];
		$vendor_id = $this->vendor->getId();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
		    
		    // Fetch manufacturer name from POST or fallback from DB
			$manufacturer_name = !empty($this->request->post['name'])
				? $this->request->post['name']
				: $this->model_vendor_manufacturer->getManufacturerName($manufacturer_id); // You can write a helper if needed

			// Sanitize manufacturer name for filename
			$sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($manufacturer_name));
			$new_filename = $sanitized_name . '.pdf';

			$folderPath = DIR_IMAGE . 'catalog/declaration/' . $vendor_id;
			$relativePath = 'catalog/declaration/' . $vendor_id . '/' . $new_filename;
			$fullPath = $folderPath . '/' . $new_filename;

			// Ensure folder exists
			if (!is_dir($folderPath)) {
				mkdir($folderPath, 0755, true);
			}

			// Step 2: If file uploaded, save and update path
			if (!empty($_FILES['declaration_form_file']['name']) && is_uploaded_file($_FILES['declaration_form_file']['tmp_name'])) {
				// Remove existing file if it exists
				if (file_exists($fullPath)) {
					unlink($fullPath);
				}

				move_uploaded_file($_FILES['declaration_form_file']['tmp_name'], $fullPath);

				// Save the path to POST for DB update
				$this->request->post['declaration_form'] = $relativePath;
			} else {
				// If no file uploaded, keep existing declaration path from DB
				$query = $this->db->query("SELECT declaration FROM " . DB_PREFIX . "vendor_to_manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "' vendor_id='".(int)$vendor_id."'");
				$this->request->post['declaration_form'] = $query->row['declaration'];
			}
			$vendor_id = $this->vendor->getId();
// 			var_dump($this->request->post,$this->request->get);
			$this->model_vendor_manufacturer->editManufacturer($this->request->get['manufacturer_id'], $this->request->post,$vendor_id);

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

			$this->response->redirect($this->url->link('vendor/manufacturer', '', true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('vendor/manufacturer');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/manufacturer');
		$vendor_id = $this->vendor->getId();

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $manufacturer_id) {
				$this->model_vendor_manufacturer->deleteManufacturer($manufacturer_id,$vendor_id);
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

			$this->response->redirect($this->url->link('vendor/manufacturer', '', true));
		}

		$this->getList();
	}

	protected function getList() {
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
		
		$data['cancel'] = $this->url->link('vendor/dashboard');

		$url = '';
		
		// added changes for the filter 25-04-2025=============
		$filter_name = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '';
        $filter_status = isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : '';
        // ------------------------------------------------

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
			'href' => $this->url->link('vendor/manufacturer', '', true)
		);

		$data['add'] = $this->url->link('vendor/manufacturer/add', '', true);
		$data['delete'] = $this->url->link('vendor/manufacturer/delete', '', true);

		$data['manufacturers'] = array();

		$filter_data = array(
			'vendor_id'   => $this->vendor->getId(),
			// added changes for the filter 25-04-2025
			'filter_name'   => $filter_name,
			'filter_status' => $filter_status,
			// -------------------------------
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

// 		$manufacturer_total = $this->model_vendor_manufacturer->getTotalManufacturers($filter_data);

		$results = $this->model_vendor_manufacturer->getManufacturers($filter_data);
		
		$manufacturer_total = count($results);

		foreach ($results as $result) {
		  //  added changes for the manufactirer 25-04-2025---------------------
		    if (!$result['status'] || $result['status'] == 2) {
				$statuss = $this->url->link('vendor/manufacturer/status', 'user_token=' . $this->session->data['user_token'] . '&manufacturer_id=' . $result['manufacturer_id'] . $url, true);
			} else {
				$statuss = '';
			}
			
			if ($result['status'] == 2) {
				$cstatus = "Approval Pending";
			} elseif ($result['status'] == 1) {
				$cstatus = "Enabled";
			} elseif ($result['status'] == 0) {
				$cstatus = "Disabled";
			} else {
				$cstatus = '';
			}

			/*-- added on changes regarding to the comments 25-04-2025-------------------------------*/

			$this->load->model('vendor/manufacturer');
			// approval comment
			$vendor_id = $this->vendor->getId();
			$comments = $this->model_vendor_manufacturer->getAllManufacturerComments($result['manufacturer_id'],$vendor_id);

			$latest_comment = '';
			if ($result['status'] == 0) { // Only when disapproved
				$latest_comment = $this->model_vendor_manufacturer->getLatestAdminComment($result['manufacturer_id'],$vendor_id);
			}


			// --------------------------------------------------------------------------------------------------------------
		    
			$data['manufacturers'][] = array(
				'manufacturer_id' => $result['manufacturer_id'],
				'name'            => $result['name'],
				'view_declaration_url' => !empty($result['declaration']) ? 'image/' . $result['declaration'] : '',
				'sort_order'      => $result['sort_order'],
				'edit'            => $this->url->link('vendor/manufacturer/edit','manufacturer_id=' . $result['manufacturer_id'] . $url, true),
				// added new code on 25-04-2025-----------------------
				'status'          => $cstatus,
				'comment_thread' => $comments,
				'statusvalue' => $result['status'],
				'view_comment_url' => $this->url->link('vendor/manufacturer/viewCommentModal', 'manufacturer_id=' . $result['manufacturer_id'] . '&user_token=' . $this->session->data['user_token'], true),
				'latest_comment' => $latest_comment
				// ----------------------------------------------------------
			);
		}
		
		$data['heading_title']          = $this->language->get('heading_title');
		$data['text_list']           	= $this->language->get('text_list');
		$data['text_no_results'] 		= $this->language->get('text_no_results');
		$data['text_confirm']			= $this->language->get('text_confirm');
		$data['text_male']				= $this->language->get('Male');
		$data['text_female'] 			= $this->language->get('Female');
		$data['text_none'] 				= $this->language->get('text_none');
	 	$data['text_enable']            = $this->language->get('text_enable');
		$data['text_disable']           = $this->language->get('text_disable');
		$data['text_select']            = $this->language->get('text_select');
		$data['column_name']			= $this->language->get('column_name');
		$data['entry_album']			= $this->language->get('entry_album');
		$data['column_sort_order']		= $this->language->get('column_sort_order');
		$data['column_status']			= $this->language->get('column_status');
		$data['column_action']			= $this->language->get('column_action');
		$data['button_remove']          = $this->language->get('button_remove');
		$data['button_edit']            = $this->language->get('button_edit');
		$data['button_add']             = $this->language->get('button_add');
		$data['button_filter']          = $this->language->get('button_filter');
		$data['button_delete']          = $this->language->get('button_delete');
		$data['button_approve']         = $this->language->get('button_approve');
		$data['button_desapprove']      = $this->language->get('button_desapprove');
		$data['text_confirm']           = $this->language->get('text_confirm');
		
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

        // added changes for manufacturer 25-04-205
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		// ----------------------------------------------------------

		$url = '';
		
		// Filter parameters for URL construction 25-04-2025


		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

        // ---------------------------------------------------------------------

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('vendor/manufacturer','sort=name' . $url, true);
		$data['sort_sort_order'] = $this->url->link('vendor/manufacturer','sort=sort_order' . $url, true);

		$url = '';
		
		// added changes for manufacturer 25-04-2025=========================

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		// =================------------------------------------------

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $manufacturer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/manufacturer',$url . 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($manufacturer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($manufacturer_total - $this->config->get('config_limit_admin'))) ? $manufacturer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $manufacturer_total, ceil($manufacturer_total / $this->config->get('config_limit_admin')));

        // added changes for manufacturer filter 25-04-2025 ----------
		$data['filter_name'] = $filter_name;
		$data['filter_status'] = $filter_status;
		// --------------------------------------
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');
		
		$data['manufacturer_video']= $this->load->controller('common/video_popup', ['video_url' => 'https://www.youtube.com/embed/K_WOVqi4cWs']);

		$this->response->setOutput($this->load->view('vendor/manufacturer_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['manufacturer_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['heading_title']           = $this->language->get('heading_title');
		$data['text_form']               = !isset($this->request->get['information_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_default']            = $this->language->get('text_default');
		$data['text_enable']             = $this->language->get('text_enable');
		$data['text_disable']            = $this->language->get('text_disable');
		$data['text_select']             = $this->language->get('text_select');
		$data['entry_name']        		 = $this->language->get('entry_name');
		$data['entry_store']        	 = $this->language->get('entry_store');
		$data['entry_keyword']        	 = $this->language->get('entry_keyword');
		$data['entry_image']    		 = $this->language->get('entry_image');
		$data['entry_sort_order']        = $this->language->get('entry_sort_order');
		$data['button_save']             = $this->language->get('button_save');
		$data['button_add']              = $this->language->get('button_add');
		$data['button_remove']           = $this->language->get('button_remove');
		$data['button_cancel']           = $this->language->get('button_cancel');
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
		
        // added new code---11-04-2025 regarding to the manufacturer required
        
        if ( isset( $this->error[ 'declaration_form' ] ) ) {
            $data[ 'error_file' ] = $this->error[ 'declaration_form' ];
        } else {
            $data[ 'error_file' ] = '';
        }
        // ----------------------------------------------------------------------

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';
		
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
			'href' => $this->url->link('vendor/manufacturer', '', true)
		);

		if (!isset($this->request->get['manufacturer_id'])) {
			$data['action'] = $this->url->link('vendor/manufacturer/add', '', true);
		} else {
			$data['action'] = $this->url->link('vendor/manufacturer/edit','manufacturer_id=' . $this->request->get['manufacturer_id'] . $url, true);
			
		}

		$data['cancel'] = $this->url->link('vendor/manufacturer', '', true);
$vendor_id = $this->vendor->getId();
		if (isset($this->request->get['manufacturer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$manufacturer_info = $this->model_vendor_manufacturer->getManufacturer($this->request->get['manufacturer_id'],$vendor_id);
		}
		
		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($manufacturer_info)) {
			$data['name'] = $manufacturer_info['name'];
		} else {
			$data['name'] = '';
		}
		
// 		Shubham Sir Changes - 26/06/2025
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
			$data['manufacturer_store'] = $this->model_vendor_manufacturer->getManufacturerStores($this->request->get['manufacturer_id']);
		} else {
			$data['manufacturer_store'] = array(0);
		}

		if (isset($this->request->post['product_seo_url'])) {
			$data['product_seo_url'] = $this->request->post['product_seo_url'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_seo_url'] = $this->model_vendor_product->getProductSeoUrls($this->request->get['product_id']);
		} else {
			$data['product_seo_url'] = array();
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
		
        // ---------- added on 10-04-2025 changes regarding to the manufacturer declaration----------------------------------------	
        if (!empty($_FILES['declaration_form']['name']) && is_uploaded_file($_FILES['declaration_form']['tmp_name'])) {
   
            $filename = str_replace(' ', '_', basename($_FILES['declaration_form']['name']));
        
        
            $relativePath = 'catalog/declaration/' . $filename;
            $fullPath = DIR_DECLARATION . $relativePath;
        
        
            if (!is_dir(DIR_DECLARATION . 'catalog/declaration')) {
              
            }
        	
           
            move_uploaded_file($_FILES['declaration_form']['tmp_name'], $fullPath);
        
           
            $this->request->post['declaration_form'] = $relativePath;
        }
		//---------------------------------------------------------------------------------------------------------------------------

		$this->load->model('localisation/language');
		$this->load->model('vendor/manufacturer');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['manufacturer_seo_url'])) {
			$data['manufacturer_seo_url'] = $this->request->post['manufacturer_seo_url'];
		} elseif (isset($this->request->get['manufacturer_id'])) {
			$data['manufacturer_seo_url'] = $this->model_vendor_manufacturer->getManufacturerSeoUrls($this->request->get['manufacturer_id']);
		} else {
			$data['manufacturer_seo_url'] = array();
		}
				
		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');

		$this->response->setOutput($this->load->view('vendor/manufacturer_form', $data));
	}

// commented on 11-04-2025-----------------and added new function-------------------------
// 	protected function validateForm() {
		
// 		if ((utf8_strlen($this->request->post['name']) < 2) || (utf8_strlen($this->request->post['name']) > 64)) {
// 			$this->error['name'] = $this->language->get('error_name');
// 		}

// 		if ($this->request->post['manufacturer_seo_url']) {
// 			$this->load->model('vendor/seo_url');
			
// 			foreach ($this->request->post['manufacturer_seo_url'] as $store_id => $language) {
// 				foreach ($language as $language_id => $keyword) {
// 					if (!empty($keyword)) {
// 						if (count(array_keys($language, $keyword)) > 1) {
// 							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
// 						}							
						
// 						$seo_urls = $this->model_vendor_seo_url->getSeoUrlsByKeyword($keyword);
						
// 						foreach ($seo_urls as $seo_url) {
// 							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['manufacturer_id']) || (($seo_url['query'] != 'manufacturer_id=' . $this->request->get['manufacturer_id'])))) {
// 								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}

// 		return !$this->error;
// 	}

        protected function validateForm() {
    		// Validate Name Field
    		if ((utf8_strlen($this->request->post['name']) < 2) || (utf8_strlen($this->request->post['name']) > 64)) {
    			$this->error['name'] = $this->language->get('error_name');
    		}
    	
    		// File Validation
    		if (isset($this->request->files['declaration_form_file']) && is_uploaded_file($this->request->files['declaration_form_file']['tmp_name'])) {
    			$file = $this->request->files['declaration_form_file'];
    	
    			// Store filename for view
    			$this->request->post['declaration_form_file_name'] = $file['name'];
    	
    			// Extension check
    			$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    			if ($extension !== 'pdf') {
    				$this->error['declaration_form'] = $this->language->get('error_filetype');
    			}
    	
    			// Size check (max 100KB)
    			if ($file['size'] > (100 * 1024)) {
    				$this->error['declaration_form'] = $this->language->get('error_filesize');
    			}
    	
    		} else {
    			$this->error['declaration_form'] = $this->language->get('error_file');
    		}
    	
    		// SEO URL validation (your existing logic)
    		if ($this->request->post['manufacturer_seo_url']) {
    			$this->load->model('vendor/seo_url');
    			foreach ($this->request->post['manufacturer_seo_url'] as $store_id => $language) {
    				foreach ($language as $language_id => $keyword) {
    					if (!empty($keyword)) {
    						if (count(array_keys($language, $keyword)) > 1) {
    							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
    						}
    	
    						$seo_urls = $this->model_vendor_seo_url->getSeoUrlsByKeyword($keyword);
    	
    						foreach ($seo_urls as $seo_url) {
    							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['manufacturer_id']) || ($seo_url['query'] != 'manufacturer_id=' . $this->request->get['manufacturer_id']))) {
    								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
    							}
    						}
    					}
    				}
    			}
    		}
        	
		return !$this->error;
	}
// ----------------------------------------------------------

	protected function validateDelete() {
		
		$this->load->model('vendor/product');

		foreach ($this->request->post['selected'] as $manufacturer_id) {
			$product_total = $this->model_vendor_product->getTotalProductsByManufacturerId($manufacturer_id);

			if ($product_total) {
				$this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
			}
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('vendor/manufacturer');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'vendor_id'   => $this->vendor->getId(),
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_vendor_manufacturer->getManufacturers($filter_data);

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
	
	// added changes for manufacturer 25-04-2025======----------------------------------------
	public function filterautocomplete() {
		$json = array();
	
		if (isset($this->request->get['filter_name'])) {
			$this->load->model('vendor/manufacturer');
	
			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);
	
			$manufacturers = $this->model_vendor_manufacturer->getManufacturers($filter_data);
	
			foreach ($manufacturers as $manufacturer) {
				$json[] = array(
					'manufacturer_id' => $manufacturer['manufacturer_id'],
					'name'            => strip_tags(html_entity_decode($manufacturer['name'], ENT_QUOTES, 'UTF-8'))
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
	//--------------------------------------------------
	
	
	
	// added on 25-04-2025------------manufacturer comment--------------------------------------------------

    public function viewComment() {
    	$manufacturer_id = (int)$this->request->get['manufacturer_id'];
    	$vendor_id = $this->vendor->getId();
    
    	$this->load->model('vendor/manufacturer');
    	$data['comments'] = $this->model_vendor_manufacturer->getAllManufacturerComments($manufacturer_id,$vendor_id);
    	$data['manufacturer_id'] = $manufacturer_id;
    
    	return $this->load->view('vendor/manufacturer_comment_modal', $data);
    }

    public function viewCommentModal() {
    	$manufacturer_id = (int)$this->request->get['manufacturer_id'];
        $vendor_id = $this->vendor->getId();
    	$this->load->model('vendor/manufacturer');
    	$data['comments'] = $this->model_vendor_manufacturer->getAllManufacturerComments($manufacturer_id,$vendor_id);
    	$data['manufacturer_id'] = $manufacturer_id;
    
    	return $this->load->view('vendor/manufacturer_comment_modal', $data);
    }

    public function replyForm() {
    	$this->load->language('vendor/manufacturer');
    	$this->load->model('vendor/manufacturer');
    
    	$manufacturer_id = $this->request->get['manufacturer_id'] ?? 0;
    	$vendor_id = $this->vendor->getId();
    
    	$data['manufacturer_id'] = $manufacturer_id;
    	$data['comments'] = $this->model_vendor_manufacturer->getAllManufacturerComments($manufacturer_id,$vendor_id);
    	$data['action'] = $this->url->link('vendor/manufacturer/replySave', 'manufacturer_id=' . $manufacturer_id . '&user_token=' . $this->session->data['user_token'], true);
    
    	$this->response->setOutput($this->load->view('vendor/manufacturer_reply_form', $data));
    }

    public function replySave() {
    	$this->load->model('vendor/manufacturer');
    	$manufacturer_id = (int)$this->request->get['manufacturer_id'];
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
    
    	$this->model_vendor_manufacturer->submitVendorReply($manufacturer_id, $comment, $uploaded_files, $vendor_id);
    
    	$this->session->data['success'] = $this->language->get('text_reply_success');
    	$this->response->redirect($this->url->link('vendor/manufacturer', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function reply() {
    	$this->load->model('vendor/manufacturer');
    
    	$manufacturer_id = $this->request->get['manufacturer_id'];
    	$comment = $this->request->post['reply'];
    	$media = [];
    
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
    
    	$this->model_vendor_manufacturer->submitVendorReply($manufacturer_id, $comment, $media);
    
    	$this->response->redirect($this->url->link('vendor/manufacturer'));
    }
// ---------------------------------------------------------


// added changes for the  25-04-2025-----------------------------------------------

	public function quickStatus() {
		$json = array();
	
		$this->load->model('vendor/manufacturer'); // Load your manufacturer model
		$this->load->language('vendor/manufacturer'); // Load the language file
    	$vendor_id = $this->vendor->getId();
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (isset($this->request->get['status']) && isset($this->request->get['manufacturer_id'])) {
				$this->model_vendor_manufacturer->quickStatus($this->request->get['status'], $this->request->get['manufacturer_id'],$vendor_id);
	
				$json['success'] = $this->language->get('text_statussuccess');
			} else {
				$json['error'] = $this->language->get('error_missing_data');
			}
		}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
// 	-------------------------------------------------------------------------------------------------------------------
public function autocompletemfg(): void {
    $this->load->language('vendor/manufacturer'); // Optional: if you want to use language entries
    $json = [];

    if (isset($this->request->get['filter_name'])) {
        $this->load->model('vendor/manufacturer');

        $filter_data = [
            'filter_name' => trim($this->request->get['filter_name']),
            'start'       => 0,
            'limit'       => 5
        ];

        $results = $this->model_vendor_manufacturer->getManufacturersByName($filter_data);

        foreach ($results as $result) {
            $json[] = [
                'manufacturer_id' => $result['manufacturer_id'],
                'name'            => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')
            ];
        }
    }

    // Make sure no output or whitespace has been sent before this
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}
}
