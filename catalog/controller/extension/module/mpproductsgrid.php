<?php
class ControllerExtensionModuleMpProductsGrid extends Controller {

	public function index($setting) {

		if (!isset($setting['grid_manufacturer'])) {
			$setting['grid_manufacturer'] = array();
		}

		if (!isset($setting['grid_manufacturerproducts'])) {
			$setting['grid_manufacturerproducts'] = array();
		}

		if (!isset($setting['grid_category'])) {
			$setting['grid_category'] = array();
		}

		if (!isset($setting['grid_categoryproducts'])) {
			$setting['grid_categoryproducts'] = array();
		}


		$this->load->language('extension/module/mpproductsgrid');
		
		static $module = 0;

		// <!-- General demo styles & header -->

		// $this->document->addStyle('catalog/view/javascript/mpproductsgrid/css/demo.css');
		// //<!-- Flickity gallery styles -->
		// $this->document->addStyle('catalog/view/javascript/mpproductsgrid/css/flickity.css');
		// //<!-- Component styles -->
		// $this->document->addStyle('catalog/view/javascript/mpproductsgrid/css/component.css');

		$this->document->addStyle('catalog/view/javascript/mpproductsgrid/css/main.css');

		// $this->document->addScript('catalog/view/javascript/mpproductsgrid/js/modernizr.custom.js');

		// // scripts were in footer using ocmod
		// $this->document->addScript('catalog/view/javascript/mpproductsgrid/js/isotope.pkgd.min.js');
		// $this->document->addScript('catalog/view/javascript/mpproductsgrid/js/flickity.pkgd.min.js');

		// add js in twig
		// $this->document->addScript('catalog/view/javascript/mpproductsgrid/js/main.js');

		$data['img_loader'] = 'catalog/view/javascript/mpproductsgrid/images/grid.svg';
		$data['img_loader_width'] = '60';

		$data['heading_title'] = $this->language->get('heading_title');

		if (!empty($setting['pheading_title'][ $this->config->get('config_language_id') ] )) {
			$data['heading_title'] = $setting['pheading_title'][ $this->config->get('config_language_id') ];
		}

		$setting['image_width'] = 500;
		$setting['image_height'] = 500; 
		if (empty($setting['limit']) || $setting['limit'] <= 0) {
			$setting['limit'] = 5;
		}
		

		$data['page'] = 1;

		$this->getProducts($data, $setting);
		
		$data['module'] = $module++;
		

		return $this->load->view('extension/module/mpproductsgrid', $data);
		
	}

	protected function getProducts(&$data, &$setting) {

		$data['setting'] = $setting['module_id'];

		if (!empty($setting['grid_category']) && is_array($setting['grid_category'])) {
			$grid_category = $setting['grid_category'];
		} else {
			$grid_category = array();
		}

		if (!empty($setting['grid_categoryproducts']) && is_array($setting['grid_categoryproducts'])) {
			$gridcategoryproducts = $setting['grid_categoryproducts'];
		} else {
			$gridcategoryproducts = array();
		}


		foreach ($grid_category as $grid_category_id) {
			$gridcategoryproducts[$grid_category_id]['category_id'] = $grid_category_id;
		}


		// sort grid categories by order defined in settings
		$sort_order = array();

		foreach ($gridcategoryproducts as $key => $value) {
		    $sort_order[$key]  = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC,SORT_NUMERIC, $gridcategoryproducts);


		$grid_category = array();

		$data['grid_categoryproducts'] = array();
		$grid_categoryproducts = array();

		foreach ($gridcategoryproducts as $gridcategoryproduct) {
			$grid_category[] = $gridcategoryproduct['category_id'];
			$data['grid_categoryproducts'][ $gridcategoryproduct['category_id'] ] = $gridcategoryproduct;
			$grid_categoryproducts[ $gridcategoryproduct['category_id'] ] = $gridcategoryproduct;
		}

		if (!empty($setting['grid_manufacturer']) && is_array($setting['grid_manufacturer'])) {
			$grid_manufacturer = $setting['grid_manufacturer'];
		} else {
			$grid_manufacturer = array();
		}

		if (!empty($setting['grid_manufacturerproducts']) && is_array($setting['grid_manufacturerproducts'])) {
			$gridmanufacturerproducts = $setting['grid_manufacturerproducts'];
		} else{
			$gridmanufacturerproducts = array();
		}


		foreach ($grid_manufacturer as $grid_manufacturer_id) {

			$gridmanufacturerproducts[$grid_manufacturer_id]['manufacturer_id'] = $grid_manufacturer_id;

		}

		// sort grid manufacturers by order defined in settings
		$sort_order = array();
		foreach ($gridmanufacturerproducts as $key => $value) {
		    
		    $sort_order[$key]  = $value['sort_order'];
		}
		array_multisort($sort_order, SORT_ASC, $gridmanufacturerproducts);

		$grid_manufacturer = array();
		$data['grid_manufacturerproducts'] = array();
		$grid_manufacturerproducts = array();

		foreach ($gridmanufacturerproducts as $grid_manufacturerproduct) {
			$grid_manufacturer[] = $grid_manufacturerproduct['manufacturer_id'];
			$grid_manufacturerproducts[ $grid_manufacturerproduct['manufacturer_id'] ] = $grid_manufacturerproduct;
		}

		$data['grid_manufacturerproducts'] = $grid_manufacturerproducts;



		$data['show_product_name'] = $show_product_name = $setting['show_product_name'];
		$data['show_product_price'] = $show_product_price = $setting['show_product_price'];
		$data['show_product_description'] = $show_product_description = $setting['show_product_description'];
		$data['product_description_length'] = $product_description_length = $setting['product_description_length'];
		$data['show_product_review'] = $show_product_review = $setting['show_product_review'];
		$data['filter_display'] = $filter_display = $setting['filter_display'];

		$data['show_product_additional_images'] = $show_product_additional_images = $setting['show_product_additional_images'];
		$data['show_prevnextbuttons'] = $show_prevnextbuttons = $setting['show_prevnextbuttons'];
		$data['show_pagedots'] = $show_pagedots = $setting['show_pagedots'];
		$data['slider_pauseautoplayonhover'] = $slider_pauseautoplayonhover = $setting['slider_pauseautoplayonhover'];
		$data['slider_autoplay'] = $slider_autoplay = $setting['slider_autoplay'];
		$data['slider_autoplayspeed'] = $slider_autoplayspeed = $setting['slider_autoplayspeed'];


		$data['button_cart'] = $this->language->get('button_cart');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');

		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('catalog/manufacturer');
		$this->load->model('extension/mpproductsgrid/category');
		$this->load->model('tool/image');

		$data['products'] = array();
		$data['products']['category'] = array();
		
		$data['mpgrids'] = array();
		$data['mpgrids']['category'] = array();
		foreach ($grid_category as $category_id) {

			// check if category is enable / disable
			if (isset($grid_categoryproducts[$category_id])) {
				if (!$grid_categoryproducts[$category_id]['status']) {
					continue;
				}
			}

			$filter_data = array(
				'filter_category_id' => $category_id,
				'start'        => ($data['page'] - 1 ) * $setting['limit'],
				'limit'        => $setting['limit']
			);

			$categorypath = $this->model_extension_mpproductsgrid_category->getCategoryPath($category_id);

			$categorypath[] = $category_id;
			$path = implode('_', $categorypath);

			// get category info
			$categoryinfo = $this->model_catalog_category->getCategory($category_id);
			if (!empty($categoryinfo)) {

				if (!empty($categoryinfo['image']) && file_exists(DIR_IMAGE . $categoryinfo['image'])) {
					$image = $this->model_tool_image->resize($categoryinfo['image'], 40, 40);
				} else {
					$image = false;
				}

				// if display filter text only.
				if ($filter_display==1) {
					$image = false;	
				}

				$name = $categoryinfo['name'];
				if ($image != false && $filter_display==0) {
					$name = false;
				}

				$data['mpgrids']['category'][$category_id] = array(
					'category_id' => $categoryinfo['category_id'],
					'image' => $image,
					'path' => $path,
					'name' => $name,
					'id' => str_replace(array(' ','&nbsp;','>','&gt;'),'',strtolower(strtoupper($categoryinfo['name']))).'-'.$path,
				);
			}

			$products = $this->model_catalog_product->getProducts($filter_data);


			foreach ($products as $product) {

				if (!empty($product['image']) && file_exists(DIR_IMAGE . $product['image'])) {
					$image = $this->model_tool_image->resize($product['image'], $setting['image_width'], $setting['image_height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['image_width'], $setting['image_height']);
				}

				if ($show_product_price) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product['special']) {
					$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($show_product_review) {
					$rating = $product['rating'];
				} else {
					$rating = false;
				}
				
				if (isset($grid_categoryproducts[$category_id]['product_id']) && in_array($product['product_id'], $grid_categoryproducts[$category_id]['product_id'])) {
					$highlight = true;
				} else {
					$highlight = false;
				}

				$images = array();

				if ($show_product_additional_images) {
					$results = $this->model_catalog_product->getProductImages($product['product_id']);

					foreach ($results as $result) {
						$images[] = array(
							'thumb' => $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height'])
						);
					}
				}

				$data['products']['category'][$category_id][] = array(
					'product_id'  => $product['product_id'],
					'thumb'       => $image,
					'images'       => $images,
					'name'        => $product['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, $product_description_length),
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $rating,
					'highlight'      => $highlight,
					'href'        => $this->url->link('product/product', 'path='. $path .'&product_id=' . $product['product_id'])
				);
			}
		}

		$data['products']['manufacturer'] = array();
		$data['mpgrids']['manufacturer'] = array();

		foreach ($grid_manufacturer as $manufacturer_id) {

			// check if manufacturer is enable / disable
			if (isset($grid_manufacturerproducts[$manufacturer_id])) {
				if (!$grid_manufacturerproducts[$manufacturer_id]['status']) {
					continue;
				}
			}

			$filter_data = array(
				'filter_manufacturer_id' => $manufacturer_id,
				'start'        => ($data['page'] - 1 ) * $setting['limit'],
				'limit'        => $setting['limit']
			);

			// get manufacturer info
			$manufacturerinfo = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

			if (!empty($manufacturerinfo)) {

				if (!empty($manufacturerinfo['image']) && file_exists(DIR_IMAGE . $manufacturerinfo['image'])) {
					$image = $this->model_tool_image->resize($manufacturerinfo['image'], 40, 40);
				} else {
					$image = false;
				}

				// if display filter text only.
				if ($filter_display == 1) {
					$image = false;	
				}

				$name = $manufacturerinfo['name'];
				if ($image != false && $filter_display == 0) {
					$name = false;
				}

				$data['mpgrids']['manufacturer'][$manufacturer_id] = array(
					'manufacturer_id' => $manufacturerinfo['manufacturer_id'],
					'name' => $name,
					'image' => $image,
					'id' => str_replace(array(' ','&nbsp;','>','&gt;'),'',strtolower(strtoupper($manufacturerinfo['name']))).'-'.$manufacturerinfo['manufacturer_id'],
				);
			}

			$products = $this->model_catalog_product->getProducts($filter_data);
			foreach ($products as $product) {

				if (!empty($product['image']) && file_exists(DIR_IMAGE . $product['image'])) {
					$image = $this->model_tool_image->resize($product['image'], $setting['image_width'], $setting['image_height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['image_width'], $setting['image_height']);
				}

				if ($show_product_price) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product['special']) {
					$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($show_product_review) {
					$rating = $product['rating'];
				} else {
					$rating = false;
				}
				
				if (isset($grid_manufacturerproducts[$manufacturer_id]['product_id']) && in_array($product['product_id'], $grid_manufacturerproducts[$manufacturer_id]['product_id'])) {
					$highlight = true;
				} else {
					$highlight = false;
				}

				$images = array();

				if ($show_product_additional_images) {
				$results = $this->model_catalog_product->getProductImages($product['product_id']);

				foreach ($results as $result) {
					$images[] = array(
						'thumb' => $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height'])
					);
				}

				}

				$data['products']['manufacturer'][$manufacturer_id][] = array(
					'product_id'  => $product['product_id'],
					'thumb'       => $image,
					'images'       => $images,
					'name'        => $product['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, $product_description_length),
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $rating,
					'highlight'      => $highlight,
					'href'        => $this->url->link('product/product', 'manufacturer_id='. $manufacturer_id .'&product_id=' . $product['product_id'])
				);
			}
		}

		$data['productsgrids'] = $this->load->view('extension/module/mpproductsgrids', $data);
	}

	public function showMore() {
		$this->response->addHeader('Content-Type: application/json');

		$json = array();
		$this->load->language('extension/module/mpproductsgrid');

    if ( !isset($this->request->post['request']) || empty($this->request->post['request']) || !isset($this->request->post['setting']) || empty($this->request->post['setting'])) {
        $json['warning'] = $this->language->get('error_invalid');
        $this->response->setOutput(json_encode($json));
        $this->response->output();
        exit();
    }

    // get setting array from db. if module id crafted then not process this request as well.

    $this->load->model('setting/module');

    $setting = $this->model_setting_module->getModule($this->request->post['setting']);

    if (!isset($setting['module_id']) || $setting['module_id'] != $this->request->post['setting'])	{
			$json['warning'] = $this->language->get('error_invalid');
			$this->response->setOutput(json_encode($json));
			$this->response->output();
			exit();
    }

    if (!isset($setting['grid_manufacturer'])) {
			$setting['grid_manufacturer'] = array();
		}

		if (!isset($setting['grid_manufacturerproducts'])) {
			$setting['grid_manufacturerproducts'] = array();
		}

		if (!isset($setting['grid_category'])) {
			$setting['grid_category'] = array();
		}

		if (!isset($setting['grid_categoryproducts'])) {
			$setting['grid_categoryproducts'] = array();
		}

    $data['text_tax'] = $this->language->get('text_tax');

		$setting['image_width'] = 500;
		$setting['image_height'] = 500; 
		
		if (empty($setting['limit']) || $setting['limit'] <= 0) {
			$setting['limit'] = 5;
		}

		
		$data['page'] = $this->request->post['page'] + 1;


		$this->getProducts($data, $setting);
		$json['success'] = 1;
		// count if we got any new products or not. if not revert page variable to old page and hide button
		if (count($data['products']['manufacturer']) <= 0 && count($data['products']['category']) <= 0) {
			$data['page'] = $this->request->post['page'];
			$json['success'] = 0;
			$json['message'] = $this->language->get('text_noproducts');
		}

		$json['productsgrids'] = $data['productsgrids'];
		$json['page'] = $data['page'];

		$this->response->setOutput(json_encode($json));
	}


}