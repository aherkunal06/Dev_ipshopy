<?php
class ControllerExtensionMobileHome extends Controller {
	public function index() {
		
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_home'] = $this->load->controller('extension/soconfig/content_mobile');
		
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		
// 		new sections for mobile 
        $data['categoriesList'] = $this->load->controller('common/category_list');
		$data['medium_banners'] = $this->load->controller('product/medium_banners'); 
		
		 $data['grocery'] = $this->load->controller('product/mobile_category_product', ['category_ids'=>[1476]]);
        $data['kitchen_dining'] = $this->load->controller('product/mobile_category_product', ['category_ids'=>[61]]);
        $data['water_bottle'] = $this->load->controller('product/mobile_category_product', ['category_ids'=>[83]]);
        $data['electronic'] = $this->load->controller('product/mobile_category_product', ['category_ids'=>[59]]);
        $data['umbrella'] = $this->load->controller('product/mobile_category_product', ['category_ids'=>[1724]]);
        
        $data['trackpants'] = $this->load->controller('product/mobile_category_product', ['category_ids'=>[1640]]);
        $data['bedsheets'] = $this->load->controller('product/mobile_category_product', ['category_ids'=>[1470]]);
        $data['mensShoes'] = $this->load->controller('product/mobile_category_product', ['category_ids'=>[1472]]);
// 		var_dump($data['umbrella']);
// 		$data['featured_categories'] = $this->load->controller('extension/module/featured_categories');
// 		$data['popular_brands'] = $this->load->controller('extension/module/popular_brands');


$category_group_settings = [
    'groups' => [
        [
            'key' => 'Beauty_group',
            'title' => "Beauty & Personal Care",
            'view_all_href' => $this->url->link('product/category', 'path=2166'),
            'rows' => [
                'Lipstick' => ['key' => 'Lipstick', 'parent_ids' => 2185],
                'Bangles' => ['key' => 'Bangles', 'parent_ids' => 2167],
                'Sanitary ' => ['key' => 'Sanitary ', 'parent_ids' => 2168]
            ]
        ]
        ]
        ];
        $group_blocks= $this->load->controller('product/category_group', $category_group_settings);
      $data['ladies_group'] = $group_blocks['groups']['Beauty_group']['group_combined'] ?? '';
      
  $category_group_settings2 = [
    'groups' => [
        [
           'key' => 'Beauty_group',
            'title' => "Fashion & Accessories",
            'view_all_href' => $this->url->link('product/category', 'path=3567'),
            'rows' => [
                'Lipstick' => ['key' => 'Lipstick', 'parent_ids' => 3568],
                'Bangles' => ['key' => 'Bangles', 'parent_ids' => 2103],
                'Sanitary ' => ['key' => 'Sanitary ', 'parent_ids' => 2945]
            ]
        ]
        ]
        ];
        $group_blocks2= $this->load->controller('product/category_group', $category_group_settings2);
      $data['category_group2'] = $group_blocks2['groups']['Beauty_group']['group_combined'] ?? '';      
      
        $data['popular_brands']= $this->load->controller('common/popular_brands' );
        $data['brand_product']= $this->load->controller('common/brand_product' );

      
	
	 
        
        //category card
        
        // $data['category_card_1'] = $this->load->controller('product/category_card', [
        //     'category_id' => 2118,
        //     'level' => 3,
        //     'title' => "Men's Watch",
        //     'subtitle' => 'Make Every Second count',
        //     'bg_color' => '#FFE8DE'
        // ]);
        
        // $data['category_card_2'] = $this->load->controller('product/category_card', [
        //     'category_id' => 2114,
        //     'level' => 3,
        //     'title' => 'Formal Shoes',
        //     'subtitle' => 'Step Into Comfort & Style',
        //     'bg_color' => '#FFF7EB'
        // ]);
        // card end 
        $data['todays_deals'] = $this->load->controller('product/todays_deals');
		$this->response->setOutput($this->load->view('mobile/home', $data));
	}
}
