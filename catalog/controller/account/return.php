<?php
class ControllerAccountReturn extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/return');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/return', $url, true)
		);

		$this->load->model('account/return');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['returns'] = array();

		$return_total = $this->model_account_return->getTotalReturns();

		$results = $this->model_account_return->getReturns(($page - 1) * 10, 10);

		foreach ($results as $result) {
			$image = $this->getProductImage($result['model']);

	
			$data['returns'][] = array(
				'return_id'    => $result['return_id'],
				'order_id'     => $result['order_id'],
				'name'         => $result['firstname'] . ' ' . $result['lastname'],
			
				'status'       => $result['status'],
				'date_added'   => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'href'         => $this->url->link('account/return/info', 'return_id=' . $result['return_id'], true),
				'return_image_href' => $this->url->link('account/return_image', 'return_id=' . $result['return_id'], true),
			       // coommented on 20-05-2025
				'track_link'   => '#',
					'product_name' => $result['product'],
				'sku'          => $result['model'],
				'image'        => $image
			);
		}

		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		$pagination->url = $this->url->link('account/return', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($return_total) ? (($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) + 1 : 0, ((($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) > ($return_total - $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'))) ? $return_total : ((($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) + $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')), $return_total, ceil($return_total / $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')));

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

    // 	$data['column_left_account'] = $this->load->controller('account/column_left_account'); commentes on 20-05-2025
		$this->response->setOutput($this->load->view('account/return_list', $data));
	}

	public function info() {
		$this->load->language('account/return');

		if (isset($this->request->get['return_id'])) {
			$return_id = $this->request->get['return_id'];
		} else {
			$return_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return/info', 'return_id=' . $return_id, true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/return');

		$return_info = $this->model_account_return->getReturn($return_id);
		
		//-------------- added changes for product return 20-05-2025 -----------------------
		
		if (isset($this->request->get['return_id'])) {
			$return_id = (int)$this->request->get['return_id'];
		} else {
			$return_id = 0;
		}
	
		$return_info = $this->model_account_return->getReturn($return_id);
	
		if ($return_info) {
			$data['return_id'] = $return_id;
			$data['product'] = $return_info['product'];
			$data['model'] = $return_info['model'];
			$data['return_reason'] = $return_info['return_reason'];
			$data['comment'] = $return_info['comment'];
			$data['date_added'] = $return_info['date_added'];
	
			// Fetch return images
			$return_images = $this->model_account_return->getReturnImages($return_id);
			$data['return_images'] = [];
	
			$this->load->model('tool/image');
	
			foreach ($return_images as $image) {
				$data['return_images'][] = [
					'thumb' => $this->model_tool_image->resize($image['image'], 200, 200), // Resize for display
					'full'  => HTTPS_SERVER . 'image/' . $image['image'] // Full-size image
				];
			}
	
			$this->response->setOutput($this->load->view('account/return_info', $data));
		} else {
			$this->response->redirect($this->url->link('account/return', '', true));
		}


        //--------------------------------------------------------------------------------------- 		

		if ($return_info) {
			$this->document->setTitle($this->language->get('text_return'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', '', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', true)
			);

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/return', $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_return'),
				'href' => $this->url->link('account/return/info', 'return_id=' . $this->request->get['return_id'] . $url, true)
			);

			$data['return_id'] = $return_info['return_id'];
			$data['order_id'] = $return_info['order_id'];
			$data['date_ordered'] = date($this->language->get('date_format_short'), strtotime($return_info['date_ordered']));
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($return_info['date_added']));
			$data['firstname'] = $return_info['firstname'];
			$data['lastname'] = $return_info['lastname'];
			$data['email'] = $return_info['email'];
			$data['telephone'] = $return_info['telephone'];
			$data['product'] = $return_info['product'];
			$data['model'] = $return_info['model'];
			$data['quantity'] = $return_info['quantity'];
			$data['reason'] = $return_info['reason'];
			$data['opened'] = $return_info['opened'] ? $this->language->get('text_yes') : $this->language->get('text_no');
			$data['comment'] = nl2br($return_info['comment']);
			$data['action'] = $return_info['action'];

			$data['histories'] = array();

			$results = $this->model_account_return->getReturnHistories($this->request->get['return_id']);

			foreach ($results as $result) {
				$data['histories'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => nl2br($result['comment'])
				);
			}
			
			$data['cancel'] = $this->url->link('account/return');

			$data['continue'] = $this->url->link('account/return', $url, true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			$data['column_left_account'] = $this->load->controller('account/column_left_account'); //commented on 20-05-2025 for product return
			
			$this->response->setOutput($this->load->view('account/return_info', $data));
			
		} else {
			$this->document->setTitle($this->language->get('text_return'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/return', '', true)
			);

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_return'),
				'href' => $this->url->link('account/return/info', 'return_id=' . $return_id . $url, true)
			);

			$data['continue'] = $this->url->link('account/return', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

        	$data['column_left_account'] = $this->load->controller('account/column_left_account'); // commented on 20-05-2025
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function add() {
		$this->load->language('account/return');
		$this->load->model('vendor/order_report');

		$this->load->model('account/return');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
            //    _____ added changes for product return on 20-05-2025 __________________________________________________________________
            $data = $this->request->post;
            var_dump("testing the code ------->",$data);
    		
            // Initialize an array for images
            $data['return_images'] = [];
    
            // Handle Multiple Image Uploads (from 4 different input fields)
            for ($i = 1; $i <= 4; $i++) {
                $input_name = 'return_image_' . $i;
    
                if (isset($this->request->files[$input_name]) && is_uploaded_file($this->request->files[$input_name]['tmp_name'])) {
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = basename($this->request->files[$input_name]['name']);
                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
                    if (in_array($file_ext, $allowed_extensions)) {
                        $new_filename = 'return_' . uniqid() . '.' . $file_ext;
                        $file_path = 'catalog/return_images/' . $new_filename;
    
                        if (!is_dir(DIR_IMAGE . 'catalog/return_images')) {
                            mkdir(DIR_IMAGE . 'catalog/return_images', 0777, true);
                        }
    
                        if (move_uploaded_file($this->request->files[$input_name]['tmp_name'], DIR_IMAGE . $file_path)) {
                            $data['return_images'][] = $file_path;
                        }
                    }
                }
            }
    		// ////////////new add
    			$order_status=12;
            // Insert Return Data and Get ID
            $return_id = $this->model_account_return->addReturn($data);
    		$this->model_vendor_order_report->changeOrderStatus($data['order_id'],$order_status);
    
            // Insert Images into Database
            if (!empty($data['return_images'])) {
                foreach ($data['return_images'] as $image) {
                    $this->model_account_return->addReturnImage($return_id, $image);
                }
            }
    		
            //-------------------------------------------------------------------------------------------

			$this->response->redirect($this->url->link('account/return/success', '', true));
		}

		$this->getForm();
		

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/return/add', '', true)
		);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['order_id'])) {
			$data['error_order_id'] = $this->error['order_id'];
		} else {
			$data['error_order_id'] = '';
		}

		if (isset($this->error['firstname'])) {
			$data['error_firstname'] = $this->error['firstname'];
		} else {
			$data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$data['error_lastname'] = $this->error['lastname'];
		} else {
			$data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}

		if (isset($this->error['product'])) {
			$data['error_product'] = $this->error['product'];
		} else {
			$data['error_product'] = '';
		}

		if (isset($this->error['model'])) {
			$data['error_model'] = $this->error['model'];
		} else {
			$data['error_model'] = '';
		}

		if (isset($this->error['reason'])) {
			$data['error_reason'] = $this->error['reason'];
		} else {
			$data['error_reason'] = '';
		}

		$data['action'] = $this->url->link('account/return/add', '', true);

		$this->load->model('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_account_order->getOrder($this->request->get['order_id']);
		}

		$this->load->model('catalog/product');

		if (isset($this->request->get['product_id'])) {
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		}

		if (isset($this->request->post['order_id'])) {
			$data['order_id'] = $this->request->post['order_id'];
		} elseif (!empty($order_info)) {
			$data['order_id'] = $order_info['order_id'];
		} else {
			$data['order_id'] = '';
		}
		
		// added code for the product return on 21-05-2025
		if (isset($this->request->post['product_id'])) {
			$data['product_id'] = $this->request->post['product_id'];
		} elseif (!empty($product_info)) {
			$data['product_id'] = $product_info['product_id'];
		} else {
			$data['product_id'] = 0;
		}
        //-----------------------------------------------------

		if (isset($this->request->post['date_ordered'])) {
			$data['date_ordered'] = $this->request->post['date_ordered'];
		} elseif (!empty($order_info)) {
			$data['date_ordered'] = date('Y-m-d', strtotime($order_info['date_added']));
		} else {
			$data['date_ordered'] = '';
		}

		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($order_info)) {
			$data['firstname'] = $order_info['firstname'];
		} else {
			$data['firstname'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} elseif (!empty($order_info)) {
			$data['lastname'] = $order_info['lastname'];
		} else {
			$data['lastname'] = $this->customer->getLastName();
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($order_info)) {
			$data['email'] = $order_info['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} elseif (!empty($order_info)) {
			$data['telephone'] = $order_info['telephone'];
		} else {
			$data['telephone'] = $this->customer->getTelephone();
		}

		if (isset($this->request->post['product'])) {
			$data['product'] = $this->request->post['product'];
		} elseif (!empty($product_info)) {
			$data['product'] = $product_info['name'];
		} else {
			$data['product'] = '';
		}

		if (isset($this->request->post['model'])) {
			$data['model'] = $this->request->post['model'];
		} elseif (!empty($product_info)) {
			$data['model'] = $product_info['model'];
		} else {
			$data['model'] = '';
		}

		if (isset($this->request->post['quantity'])) {
			$data['quantity'] = $this->request->post['quantity'];
		} else {
			$data['quantity'] = 1;
		}

		if (isset($this->request->post['opened'])) {
			$data['opened'] = $this->request->post['opened'];
		} else {
			$data['opened'] = false;
		}

		if (isset($this->request->post['return_reason_id'])) {
			$data['return_reason_id'] = $this->request->post['return_reason_id'];
		} else {
			$data['return_reason_id'] = '';
		}

		$this->load->model('localisation/return_reason');

		$data['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons();

		if (isset($this->request->post['comment'])) {
			$data['comment'] = $this->request->post['comment'];
		} else {
			$data['comment'] = '';
		}

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('return', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_return_id'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->request->post['agree'])) {
			$data['agree'] = $this->request->post['agree'];
		} else {
			$data['agree'] = false;
		}

		$data['back'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

	$data['column_left_account'] = $this->load->controller('account/column_left_account');
		$this->response->setOutput($this->load->view('account/return_form', $data));
	}

	protected function validate() {
		if (!$this->request->post['order_id']) {
			$this->error['order_id'] = $this->language->get('error_order_id');
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ((utf8_strlen($this->request->post['product']) < 1) || (utf8_strlen($this->request->post['product']) > 255)) {
			$this->error['product'] = $this->language->get('error_product');
		}

		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}

		if (empty($this->request->post['return_reason_id'])) {
			$this->error['reason'] = $this->language->get('error_reason');
		}

		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('return', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}
		if (empty($this->request->files['return_image_1']['name']) && empty($this->request->files['return_image_2']['name']) && 
		empty($this->request->files['return_image_3']['name']) && 
        empty($this->request->files['return_image_4']['name'])) {
        $this->error['warning'] = $this->language->get('error_image_required');
        }

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		return !$this->error;
	}

	public function success() {
		$this->load->language('account/return');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/return', '', true)
		);

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

	$data['column_left_account'] = $this->load->controller('account/column_left_account');
		$this->response->setOutput($this->load->view('common/success', $data));
	}
		
	private function getProductImage($model) {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
    
        $product = $this->model_catalog_product->getProductByModel($model);
        
        if ($product && !empty($product['image']) && is_file(DIR_IMAGE . $product['image'])) {
            return $this->model_tool_image->resize($product['image'], 60, 60);
        } else {
            return $this->model_tool_image->resize('placeholder.png', 60, 60);
        }
    }
    
    //---- added changes of the product return on 20-05-2025 ----------------
    protected function getForm() {
        // View data preparation
        $data['action'] = $this->url->link('account/return/add', '', true);
        $data['return_image'] = '';

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('account/return_form', $data));
    }
    // -------------------------------------------------------------------------
}
