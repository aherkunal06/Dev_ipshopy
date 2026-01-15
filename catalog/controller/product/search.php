<?php
class ControllerProductSearch extends Controller {
	public function index() {
		$this->load->language('product/search');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');


		
		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {


			$search = '';
		}

		

		if (isset($this->request->get['tag'])) {
			$tag = $this->request->get['tag'];
		} elseif (isset($this->request->get['search'])) {
			$tag = $this->request->get['search'];
		} else {
			$tag = '';
		}

		if (isset($this->request->get['description'])) {
			$description = $this->request->get['description'];
		} else {
			$description = '';
		}

		if (isset($this->request->get['category_id'])) {
			$category_id = $this->request->get['category_id'];
		} else {
			$category_id = 0;
		}

		if (isset($this->request->get['sub_category'])) {
			$sub_category = $this->request->get['sub_category'];
		} else {
			$sub_category = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
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

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		if (isset($this->request->get['search'])) {
			$this->document->setTitle($this->language->get('heading_title') .  ' - ' . $this->request->get['search']);
		} elseif (isset($this->request->get['tag'])) {
			$this->document->setTitle($this->language->get('heading_title') .  ' - ' . $this->language->get('heading_tag') . $this->request->get['tag']);
		} else {
			$this->document->setTitle($this->language->get('heading_title'));
		}
		
        // 		add for search page conanical tags 
		
		if (isset($this->request->get['search'])) {
			$canonical_url = $this->url->link('product/search', 'search=' . urlencode($this->request->get['search']), true);
			$this->document->addLink($canonical_url, 'canonical');
		} else {
			$canonical_url = $this->url->link('product/search', '', true);
			$this->document->addLink($canonical_url, 'canonical');
		}

        // end here 


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$url = '';

		if (isset($this->request->get['search'])) {
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['tag'])) {
			$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['description'])) {
			$url .= '&description=' . $this->request->get['description'];
		}

		if (isset($this->request->get['category_id'])) {
			$url .= '&category_id=' . $this->request->get['category_id'];
		}

		if (isset($this->request->get['sub_category'])) {
			$url .= '&sub_category=' . $this->request->get['sub_category'];
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

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('product/search', $url)
		);

		if (isset($this->request->get['search'])) {
			$data['heading_title'] = $this->language->get('heading_title') .  ' - ' . $this->request->get['search'];
		} else {
			$data['heading_title'] = $this->language->get('heading_title');
		}

		$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

		$data['compare'] = $this->url->link('product/compare');

		$this->load->model('catalog/category');

		// 3 Level Category Search
		$data['categories'] = array();

		$categories_1 = $this->model_catalog_category->getCategories(0);

		foreach ($categories_1 as $category_1) {
			$level_2_data = array();

			$categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);

			foreach ($categories_2 as $category_2) {
				$level_3_data = array();

				$categories_3 = $this->model_catalog_category->getCategories($category_2['category_id']);

				foreach ($categories_3 as $category_3) {
					$level_3_data[] = array(
						'category_id' => $category_3['category_id'],
						'name'        => $category_3['name'],
					);
				}

				$level_2_data[] = array(
					'category_id' => $category_2['category_id'],
					'name'        => $category_2['name'],
					'children'    => $level_3_data
				);
			}

			$data['categories'][] = array(
				'category_id' => $category_1['category_id'],
				'name'        => $category_1['name'],
				'children'    => $level_2_data
			);
		}

		$data['products'] = array();

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
// 			$filter_data = array(
// 				'filter_name'         => $search,
// 				'filter_tag'          => $tag,
// 				'filter_description'  => $description,
// 				'filter_category_id'  => $category_id,
// 				'filter_sub_category' => $sub_category,
// 				'sort'                => $sort,
// 				'order'               => $order,
// 				'start'               => ($page - 1) * $limit,
// 				'limit'               => $limit
// 			);

			$manufacturer_ids = isset($this->request->get['manufacturer']) ? $this->request->get['manufacturer'] : [];
			if (!is_array($manufacturer_ids)) {
				$manufacturer_ids = [$manufacturer_ids];
			}
			// ✅ Get selected colors from request
			$filter_colors = isset($this->request->get['color']) ? $this->request->get['color'] : [];

			$filter_discounts = isset($this->request->get['discount']) ? $this->request->get['discount'] : [];
			$filter_data['filter_discounts'] = $filter_discounts;
			$data['selected_discounts'] = $filter_discounts;

		
			$category_id = isset($this->request->get['category_id']) ?
				(int)$this->request->get['category_id'] :
				0;
			$data['category_id'] = $category_id;


			// Get selected size filters from URL
			$filter_sizes = isset($this->request->get['size']) ? (array)$this->request->get['size'] : [];
			$data['selected_sizes'] = $filter_sizes;

			// Load model
			$this->load->model('catalog/product');
			$data['sizes'] = $this->model_catalog_product->getAvailableSizes($category_id);


			$min_price = isset($this->request->get['min']) ? (float)$this->request->get['min'] : 0;
			$max_price = isset($this->request->get['max']) ? (float)$this->request->get['max'] : 999999;


			$filter_data = array(

				'filter_name'           => $search,
				'filter_tag'            => $tag,
				// 'filter_description'    => $description,
				'filter_category_id'    => $category_id,
				'filter_sub_category'   => $sub_category,
				'filter_manufacturers'  => $manufacturer_ids,
				'filter_colors'         => $filter_colors,
				'filter_sizes'          => $filter_sizes,
				'filter_discounts'      => $filter_discounts, 
				'filter_ratings'        => isset($this->request->get['rating']) ? $this->request->get['rating'] : [],
				'filter_capacities'     => $filter_data['filter_capacities'] ?? '',
				'filter_option_values'  => isset($this->request->get['filter_option_values']) ? $this->request->get['filter_option_values'] : [],
				'min_price'             => $min_price,
				'max_price'             => $max_price,
				'sort'                  => $sort,
				'order'                 => $order,
				'start'                 => ($page - 1) * $limit,
				'limit'                 => $limit
			);

			$data['selected_rating'] = isset($filter_data['filter_rating']) ? $filter_data['filter_rating'] : [];
			$data['selected_sizes'] = $filter_data['filter_sizes'];
			$data['selected_sizes'] = $filter_sizes;

			$data['selected_colors'] = $filter_colors;

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);
			

			
			$index = 0;
		$brands = [];
		$added_groups = [];
			foreach ($results as $result) {

	// 			  echo '<pre>';
    // print_r($result); // Product चं पूर्ण data print होईल
    // echo '</pre>';

                     // First try to get sizes from product_variants table
    $group_info = $this->model_catalog_product->getProductGroupSizes($result['product_id']);

    if ($group_info && isset($group_info['group_id']) && !empty($group_info['sizes'])) {
        // If sizes found in product_variants table
        $group_id = (int)$group_info['group_id'];

        if (in_array($group_id, $added_groups)) {
            continue;
        }

        $added_groups[] = $group_id;
        $sizes = $group_info['sizes'];

    } else {
        // Else get sizes using fallback method (product_option_value table)
        $group_info_alt = $this->model_catalog_product->getProductGroupS($result['product_id']);

        if ($group_info_alt && isset($group_info_alt['group_id'])) {
            $group_id = (int)$group_info_alt['group_id'];

            if (in_array($group_id, $added_groups)) {
                continue;
            }

            $added_groups[] = $group_id;
            $sizes = $group_info_alt['sizes'];

        } else {
            $sizes = array(); 
        }
    }


echo '<pre>';
echo 'Product ID: ' . $result['product_id'] . '<br>';
var_dump($sizes);
echo '</pre>';



				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
				// filter 
            	if ($index == 0) {
					$product_id0 = $result['product_id'];
					$index++;
				}
				$search_product_ids[] = $result['product_id']; 
				if ($result['manufacturer']) {
					$brands[] = [
						'manufacturer_id' => $result['manufacturer_id'],
						'name' => $result['manufacturer']
					];
				}
				// filter end 
				
				// Build availability/stock flags to align with visual search behavior
				$status = isset($result['status']) ? (int)$result['status'] : null;
				$quantity = isset($result['quantity']) ? (int)$result['quantity'] : null;
				$stock_status = isset($result['stock_status']) ? $result['stock_status'] : null;
				$stock_status_id = isset($result['stock_status_id']) ? (int)$result['stock_status_id'] : null;

				$availability_label = '';
				$show_availability = false;
				$can_add_to_cart = true;
				$show_stock_status = false;

				if ($status !== null) {
					// Determine availability rules based on status
					if ($status === 2) {
						$availability_label = 'Coming soon';
						$can_add_to_cart = false;
						$show_availability = true;
					} elseif ($status === 0) {
						$availability_label = 'Currently unavailable';
						$can_add_to_cart = false;
						$show_availability = true;
					} else {
						$can_add_to_cart = true;
					}

					// Compute stock status visibility: only when NOT showing availability and quantity == 0 and stock_status is meaningful
					if (!$show_availability && $quantity !== null && (int)$quantity === 0 && $stock_status) {
						$ss = strtolower(trim($stock_status));
						$meaningless = array('in stock','instock','available');
						if (!in_array($ss, $meaningless, true)) {
							$show_stock_status = true;
						}
					}
				}
				
				//------------------- end here ------------------------
				
				
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'availability_label' => $availability_label,// added on 28-09-2025
					'show_availability'  => $show_availability, // 28-09-2025
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					 'sizes'       => $sizes,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'] . $url)
				);
			}

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/search', 'sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/search', 'sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/search', 'sort=p.price&order=DESC' . $url)
			);

// 			if ($this->config->get('config_review_status')) {
// 				$data['sorts'][] = array(
// 					'text'  => $this->language->get('text_rating_desc'),
// 					'value' => 'rating-DESC',
// 					'href'  => $this->url->link('product/search', 'sort=rating&order=DESC' . $url)
// 				);

// 				$data['sorts'][] = array(
// 					'text'  => $this->language->get('text_rating_asc'),
// 					'value' => 'rating-ASC',
// 					'href'  => $this->url->link('product/search', 'sort=rating&order=ASC' . $url)
// 				);
// 			}

// 			$data['sorts'][] = array(
// 				'text'  => $this->language->get('text_model_asc'),
// 				'value' => 'p.model-ASC',
// 				'href'  => $this->url->link('product/search', 'sort=p.model&order=ASC' . $url)
// 			);

// 			$data['sorts'][] = array(
// 				'text'  => $this->language->get('text_model_desc'),
// 				'value' => 'p.model-DESC',
// 				'href'  => $this->url->link('product/search', 'sort=p.model&order=DESC' . $url)
// 			);

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/search', $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

        // pagination 
        
        
        
        			if (isset($this->request->get['manufacturer'])) {
            foreach ((array)$this->request->get['manufacturer'] as $m) {
                $url .= '&manufacturer[]=' . (int)$m;
            }
        }
        
        if (isset($this->request->get['color'])) {
            foreach ((array)$this->request->get['color'] as $c) {
                $url .= '&color[]=' . urlencode($c);
            }
        }
        
        if (isset($this->request->get['discount'])) {
            foreach ((array)$this->request->get['discount'] as $d) {
                $url .= '&discount[]=' . (int)$d;
            }
        }
        
        if (isset($this->request->get['rating'])) {
            foreach ((array)$this->request->get['rating'] as $r) {
                $url .= '&rating[]=' . (int)$r;
            }
        }
        
        if (isset($this->request->get['size'])) {
            foreach ((array)$this->request->get['size'] as $s) {
                $url .= '&size[]=' . (int)$s;
            }
        }
        
        if (isset($this->request->get['min'])) {
            $url .= '&min=' . (float)$this->request->get['min'];
        }
        
        if (isset($this->request->get['max'])) {
            $url .= '&max=' . (float)$this->request->get['max'];
        }
        // end 
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/search', $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			if (isset($this->request->get['search']) && $this->config->get('config_customer_search')) {
				$this->load->model('account/search');

				if ($this->customer->isLogged()) {
					$customer_id = $this->customer->getId();
				} else {
					$customer_id = 0;
				}

				if (isset($this->request->server['REMOTE_ADDR'])) {
					$ip = $this->request->server['REMOTE_ADDR'];
				} else {
					$ip = '';
				}

				$search_data = array(
					'keyword'       => $search,
					'category_id'   => $category_id,
					'sub_category'  => $sub_category,
					'description'   => $description,
					'products'      => $product_total,
					'customer_id'   => $customer_id,
					'ip'            => $ip
				);

				$this->model_account_search->addSearch($search_data);
			}
		}

		$data['search'] = $search;
		$data['description'] = $description;
		$data['category_id'] = $category_id;
		$data['sub_category'] = $sub_category;

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');


            
		$data['pricefilter'] = $this->load->controller('product/filtercombo', [
			'product_ida0' => $product_id0,
			'brands' => $brands,
            'product_ids'       => $search_product_ids,
		]);

		$this->response->setOutput($this->load->view('product/search', $data));
	}
}
