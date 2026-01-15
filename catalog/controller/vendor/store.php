<?php
class ControllerVendorStore extends Controller {
	private $error = array();

	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', 'SSL'));
		}
		$this->load->language('vendor/store');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/store');

		$this->getList();
	}

	protected function getList() {
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'r.date_added';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
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
		
		$data['cancel'] = $this->url->link('vendor/dashboard');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/store')
		);
		
		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}


		$data['stores'] = array();

		$filter_data = array(
			'vendor_id' 		=> $this->vendor->getId(),
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$this->load->model('vendor/store');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

		$store_total = $this->model_vendor_store->getTotalVendorStore($filter_data);
		$stores = $this->model_vendor_store->getStores($filter_data);
		foreach($stores as $store){
			
			$country_info = $this->model_localisation_country->getCountry($store['country_id']);
			if(isset($country_info['name'])){
				$cnames = $country_info['name'];
			} else {
				$cnames ='';
			}
			$zone_info = $this->model_localisation_zone->getZone($store['zone_id']);
			if(isset($zone_info['name'])){
				$znames = $zone_info['name'];
			} else {
				$znames ='';
			}
			$data['stores'][]=array(
				'vendor_id' => $store['vendor_id'],
				'name'  => $store['name'],
				'display_name'=>$store['display_name'],
				'cnames'=> $cnames,
				'znames'=> $znames,
				'view'  => $this->url->link('vendor/vendor_profile','&vendor_id=' .$store['vendor_id'])
			);
		}
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['column_name']  		= $this->language->get('column_name');
		$data['column_vendor']  	= $this->language->get('column_vendor');
		$data['column_country']  	= $this->language->get('column_country');
		$data['column_zone']  	    = $this->language->get('column_zone');
		$data['column_action']  	= $this->language->get('column_action');

		$data['entry_product'] 		= $this->language->get('entry_product');
		$data['entry_author'] 		= $this->language->get('entry_author');
		$data['entry_rating'] 		= $this->language->get('entry_rating');
		$data['entry_status'] 		= $this->language->get('entry_status');
		$data['entry_date_added'] 	= $this->language->get('entry_date_added');

		$data['button_add'] 		= $this->language->get('button_add');
		$data['button_edit'] 		= $this->language->get('button_edit');
		$data['button_delete'] 		= $this->language->get('button_delete');
		$data['button_filter'] 		= $this->language->get('button_filter');
		$data['button_view']        = $this->language->get('button_view');
		
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

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name']   = $this->url->link('vendor/store/slist','&sort=vd.name'.$url , 'SSL');
		$data['sort_vendor'] = $this->url->link('vendor/store/slist','&sort=vendor'.$url , 'SSL');
		$data['sort_country']= $this->url->link('vendor/store/slist','&sort=country'.$url , 'SSL');
		$data['sort_zone']   = $this->url->link('vendor/store/slist','&sort=zone'.$url , 'SSL');
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $store_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/store',$url . 'page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($store_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($store_total - $this->config->get('config_limit_admin'))) ? $store_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $store_total, ceil($store_total / $this->config->get('config_limit_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');

		
		$this->response->setOutput($this->load->view('vendor/store_list', $data));
	}

	public function view() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', 'SSL'));
		}
		
		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = '';
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
			$limit = $this->config->get($this->config->get('config_theme') . '_product_limit');
		}
		
		$url = '';
		
		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
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
		
		$this->load->language('vendor/store');
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_store'),
			'href' => $this->url->link('vendor/store', '', 'SSL')
		);
		
		
		$this->load->model('vendor/store');
		$this->load->model('tool/image');
		$this->document->setTitle($this->language->get('heading_title'));	
		$data['heading_title']  	= $this->language->get('heading_title');
		$data['text_select']  		= $this->language->get('text_select');
		$data['text_loading'] 		= $this->language->get('text_loading');
		$data['text_none'] 			= $this->language->get('text_none');
		$data['text_success'] 		= $this->language->get('text_success');
		$data['column_name']  		= $this->language->get('column_name');
		$data['column_vendor']  	= $this->language->get('column_vendor');
		$data['column_country']  	= $this->language->get('column_country');
		$data['column_zone']  	    = $this->language->get('column_zone');
		$data['column_action']  	= $this->language->get('column_action');
		$data['button_add']         = $this->language->get('Add');
		$data['button_cancle']     	= $this->language->get('button_cancle');
		$data['button_delete']      = $this->language->get('button_delete');
		$data['text_confirm']      	= $this->language->get('text_confirm');
		$data['button_filter']      = $this->language->get('button_filter');
		$data['button_view']        = $this->language->get('button_view');
		$data['button_edit']        = $this->language->get('button_edit');
		$data['text_limit']         = $this->language->get('text_limit');
		
		$store_info = $this->model_vendor_store->getViewStore($this->request->get['vendor_id']);
		//print_r($store_info);die();
		/* new code */
		if(!empty($store_info['store_logowidth'])){
			$store_logowidth = $store_info['store_logowidth'];
		} else {
			$store_logowidth = 265;
		}
		
		if(!empty($store_info['store_logoheight'])){
			$store_logoheight = $store_info['store_logoheight'];
		} else {
			$store_logoheight = 50;
		}
		
		if(!empty($store_info['store_bannerwidth'])){
			$store_bannerwidth = $store_info['store_bannerwidth'];
		} else {
			$store_bannerwidth = 1140;
		}
		
		if(!empty($store_info['store_bannerheight'])){
			$store_bannerheight = $store_info['store_bannerheight'];
		} else {
			$store_bannerheight = 200;
		}
		
		if(!empty($store_info['banner'])){
			$banners = $this->model_tool_image->resize($store_info['banner'],$store_info['store_bannerwidth'],$store_info['store_bannerheight']);
		} else {
			$banners = $this->model_tool_image->resize('placeholder.png',$store_info['store_bannerwidth'],$store_info['store_bannerheight']);
		}
		
		if(!empty($store_info['logo'])){
			$logos = $this->model_tool_image->resize($store_info['logo'],$store_info['store_logowidth'],$store_info['store_logoheight']);
		} else {
			$logos = $this->model_tool_image->resize('placeholder.png',$store_info['store_logowidth'],$store_info['store_logoheight']);
		}
		
		/* new code */
		
		$data['banners'] = $banners;
		$data['logos']   = $logos;
		$data['name']    = $store_info['name'];
		$data['display_name'] = $store_info['display_name'];
		$data['telephone']    = $store_info['telephone'];
		
		
		$data['products'] =array();
		$filter_data=array(
			'vendor_id' => $this->vendor->getId(),
			'vendor_id' => $this->request->get['vendor_id'],
			'filter_filter'      => $filter,
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);
		
		$product_total = $this->model_vendor_store->getTotalProducts($filter_data);
		$products = $this->model_vendor_store->getProducts($filter_data);
		//print_r($products);die();
		foreach($products as $product) {
			if (is_file(DIR_IMAGE . $product['image'])){
				$pimage = $this->model_tool_image->resize($product['image'], 200, 200);
			}else{
				$pimage = $this->model_tool_image->resize('no_image.png', 200, 200);
			}
			//print_r($pimage);die();
			$data['products'][] = array(
				'product_id' => $product['product_id'],
				'name'   => $product['name'],
				'price'  => $this->currency->format($product['price'],$this->session->data['currency']),
				'pimage' => $pimage,
				'href'   => $this->url->link('product/product','&product_id=' .$product['product_id'])
			);
			//print_r($data['products']);die();
		}
		
// Get Review Start //
		$data['reviews']=array();
		$filter1=array(
			'vendor_id' => $this->vendor->getId()
		);
		$reviews = $this->model_vendor_store->getReviews($filter1);
		
		foreach($reviews as $review){
			
			$fields = $this->model_vendor_store->getReviewField($review['review_id']);
			if(isset($fields['value'])) {
				$reviewvalue = $fields['value'];
			} else {
				$reviewvalue='';
			}
			$data['reviews'][]=array(
				'review_id' => $review['review_id'],
				'reviewvalue' => $reviewvalue
			);
		}
// Get Review End //
		$data['sellertotal'] = $this->model_vendor_store->getTotalSellerReview($filter1);
		
		$url = '';

		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
		
		$data['sorts'] = array();

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_default'),
			'value' => 'p.sort_order-ASC',
			'href'  => $this->url->link('vendor/store/view','vendor_id=' . $store_info['vendor_id'] .'&sort=p.sort_order&order=ASC' . $url)
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->url->link('vendor/store/view','vendor_id=' . $store_info['vendor_id'] . '&sort=pd.name&order=ASC' . $url)
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->url->link('vendor/store/view','vendor_id=' . $store_info['vendor_id'] .'&sort=pd.name&order=DESC' . $url)
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_price_asc'),
			'value' => 'p.price-ASC',
			'href'  => $this->url->link('vendor/store/view','vendor_id=' . $store_info['vendor_id'] .'&sort=p.price&order=ASC' . $url)
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_price_desc'),
			'value' => 'p.price-DESC',
			'href'  => $this->url->link('vendor/store/view','vendor_id=' . $store_info['vendor_id'] .'&sort=p.price&order=DESC' . $url)
		);

		if ($this->config->get('config_review_status')) {
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->url->link('vendor/store/view','vendor_id=' . $store_info['vendor_id'] .'&sort=rating&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->url->link('vendor/store/view','vendor_id=' . $store_info['vendor_id'] .'&sort=rating&order=ASC' . $url)
			);
		}

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_model_asc'),
			'value' => 'p.model-ASC',
			'href'  => $this->url->link('vendor/store/view','vendor_id=' . $store_info['vendor_id'] .'&sort=p.model&order=ASC' . $url)
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_model_desc'),
			'value' => 'p.model-DESC',
			'href'  => $this->url->link('vendor/store/view','vendor_id=' . $store_info['vendor_id'] .'&sort=p.model&order=DESC' . $url)
		);
		
		$url = '';

		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['limits'] = array();

		$limits = array_unique(array($this->config->get($this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

		sort($limits);

		foreach($limits as $value) {
			$data['limits'][] = array(
				'text'  => $value,
				'value' => $value,
				'href'  => $this->url->link('vendor/store/view', 'vendor_id=' . $store_info['vendor_id'] . $url . '&limit=' . $value)
			);
		}

		$url = '';

		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
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

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/store/view', 'vendor_id=' . $store_info['vendor_id'] . $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;
			
		$data['column_left'] 	= $this->load->controller('common/column_left');
		$data['column_right']	= $this->load->controller('common/column_right');
		$data['content_top'] 	= $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] 		= $this->load->controller('common/footer');
		$data['header'] 		= $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('vendor/view', $data));
	}

}
