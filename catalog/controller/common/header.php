<?php
class ControllerCommonHeader extends Controller {
	public function index() {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();
		$data['route'] = isset($this->request->get['route']) ? $this->request->get['route'] : '';


		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$this->load->language('common/header');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');
		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');
		
		// getCustomerFirstNameById	added on 13-04-2025 for showing customer name 
		$this->load->model('account/customer');
		$customer_id = $this->customer->getId();
		$data['customer_firstname'] = $this->model_account_customer->getCustomerFirstNameById($customer_id);
        $data['cart_product_count']=$this->cart->countProducts();
        
        // added on 24-06-2025 for canonical 
        // === Canonical Tag Fix for SEO ===
        // if (isset($this->request->get['route'])) {
        //     $route = $this->request->get['route'];
        
        //     // Product page
        //     if ($route == 'product/product' && isset($this->request->get['product_id'])) {
        //         $product_id = (int)$this->request->get['product_id'];
        //         $this->load->model('catalog/product');
        //         $product_info = $this->model_catalog_product->getProduct($product_id);
        //         if ($product_info) {
        //             $canonical_link = $this->url->link('product/product', 'product_id=' . $product_id, true);
        //             $this->document->addLink($canonical_link, 'canonical');
        //         }
        //     }
        
        //     // Category page
        //     if ($route == 'product/category' && isset($this->request->get['path'])) {
        //         $canonical_link = $this->url->link('product/category', 'path=' . $this->request->get['path'], true);
        //         $this->document->addLink($canonical_link, 'canonical');
        //     }
        
        //     // Information page
        //     if ($route == 'information/information' && isset($this->request->get['information_id'])) {
        //         $canonical_link = $this->url->link('information/information', 'information_id=' . $this->request->get['information_id'], true);
        //         $this->document->addLink($canonical_link, 'canonical');
        //     }
        // }

        // end here 
        
        // updated on 25-06-2025 for canonical 
        
        if (isset($this->request->get['route'])) {
            $route = $this->request->get['route'];

            // Product page
            if ($route == 'product/product' && isset($this->request->get['product_id'])) {
                $product_id = (int)$this->request->get['product_id'];
                $this->load->model('catalog/product');
                $product_info = $this->model_catalog_product->getProduct($product_id);
                if ($product_info) {
                    $canonical_link = $this->url->link('product/product', 'product_id=' . $product_id, true);
                    $this->document->addLink($canonical_link, 'canonical');
                }
            }
        
            // Category page
            if ($route == 'product/category' && isset($this->request->get['path'])) {
                $canonical_link = $this->url->link('product/category', 'path=' . $this->request->get['path'], true);
                $this->document->addLink($canonical_link, 'canonical');
            }
        
            // Information page
            if ($route == 'information/information' && isset($this->request->get['information_id'])) {
                $canonical_link = $this->url->link('information/information', 'information_id=' . $this->request->get['information_id'], true);
                $this->document->addLink($canonical_link, 'canonical');
            }
        
            // Search Page (for canonical link)
            if ($route == 'product/search' && isset($this->request->get['search'])) {
                $search_query = $this->request->get['search'];
                $canonical_link = $this->url->link('product/search', 'search=' . urlencode($search_query), true);
                $this->document->addLink($canonical_link, 'canonical');
            }
        
            // Filter Page (for canonical link)
            if ($route == 'product/category' && isset($this->request->get['path']) && isset($this->request->get['filter'])) {
                $canonical_link = $this->url->link('product/category', 'path=' . $this->request->get['path'], true);
                $this->document->addLink($canonical_link, 'canonical');
            }
        
            // Sort, Pagination & Filter (for category, search, etc.)
            if (($route == 'product/category' || $route == 'product/search') && isset($this->request->get['path'])) {
                // Handle base URL for canonical link
                $canonical_link = $this->url->link($route, 'path=' . $this->request->get['path'], true);
        
                // If search page, add the search parameter
                if ($route == 'product/search' && isset($this->request->get['search'])) {
                    $canonical_link .= '&search=' . urlencode($this->request->get['search']);
                }
        
                // Add filter parameter to canonical link if exists
                if (isset($this->request->get['filter'])) {
                    $canonical_link .= '&filter=' . urlencode($this->request->get['filter']);
                }
        
                // Add sort and order to canonical link if exists
                if (isset($this->request->get['sort']) || isset($this->request->get['order'])) {
                    $canonical_link .= '&sort=' . urlencode($this->request->get['sort']) . '&order=' . urlencode($this->request->get['order']);
                }
        
                // Handle pagination by removing the page parameter if it's not the first page
                if (isset($this->request->get['page']) && $this->request->get['page'] > 1) {
                    $canonical_link = preg_replace('/&page=\d+/', '', $canonical_link);
                }
        
                // Add the canonical link to the document
                $this->document->addLink($canonical_link, 'canonical');
            }
        }
        
        // end here 
        
        
$data['seller_home'] = $this->url->link('vendor/seller_pages/seller_landing', '', true);
        
		return $this->load->view('common/header', $data);
	}
}
