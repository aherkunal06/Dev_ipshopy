<?php
class ControllerCommonHome extends Controller {
	public function index() {
	    
	    $this->db->query("SET time_zone = '+05:30'");

		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		
// 		new section 
		
        $data['categoriesList'] = $this->load->controller('common/category_list');
        
        
        $data['grocery'] = $this->load->controller('product/category_product', ['category_ids'=>[1476]]);
        $data['kitchen_dining'] = $this->load->controller('product/category_product', ['category_ids'=>[61]]);
        $data['water_bottle'] = $this->load->controller('product/category_product', ['category_ids'=>[83]]);
        $data['electronic'] = $this->load->controller('product/category_product', ['category_ids'=>[59]]);
        $data['umbrella'] = $this->load->controller('product/category_product', ['category_ids'=>[1724]]);
        
        // $data['trackpants'] = $this->load->controller('product/category_product', ['category_ids'=>[1640],'title' => 'Track Pants']);
        // $data['bedsheets'] = $this->load->controller('product/category_product', ['category_ids'=>[1470]]);
        $data['mensShoes'] = $this->load->controller('product/category_product', ['category_ids'=>[1472]]);
        
        
        // $data['tripleProductkurti'] = $this->load->controller('product/category_product_triple', ['category_ids'=>[1494]]); 
        // $data['tripleProductwomenjockeytop'] = $this->load->controller('product/category_product_triple', ['category_ids'=>[109]]); 
        // $data['tripleProductwomenescarf'] = $this->load->controller('product/category_product_triple', ['category_ids'=>[1746]]); 
        
        $data['medium_banners'] = $this->load->controller('product/medium_banners'); 
        
        
//         $data['categories_list'] = $this->load->controller('product/category_list', [
//     'rows' => [
//         ['key' => 'Beauty',     'title' => 'Beauty & Personal Care','parent_ids' => [280]],
//         ['key' => 'electronics',   'title' => 'Electronic Appliances','parent_ids' => [1]],
//         ['key' => 'fashion',       'title' => 'Fashion',       'parent_ids' => [280]]
//     ]
// ]);

$category_group_settings = [
    'groups' => [
        [
            'key' => 'Beauty_group',
            'title' => "Beauty & Personal Care",
            'view_all_href' => $this->url->link('product/category', 'path=2166'),
            'rows' => [
                'Lipstick' => ['key' => 'Lipstick', 'parent_ids' => 2185],
                'Bangles' => ['key' => 'Bangles', 'parent_ids' => 2167],
                'Sanitary ' => ['key' => 'Sanitary ', 'parent_ids' => 2166]
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
    //   var_dump($data['category_group2']);
    //   group end
        
        // brands 
        $data['popular_brands']= $this->load->controller('common/popular_brands');

$data['brand_card'] = $this->load->controller('common/brand_product');

        
        
        //category card
        
        $data['category_card_1'] = $this->load->controller('product/category_card', [
            'category_id' => 2118,
            'level' => 3,
            'title' => "Men's Watch",
            'subtitle' => 'Make Every Second count',
            'bg_color' => '#FFE8DE'
        ]);
        
        $data['category_card_2'] = $this->load->controller('product/category_card', [
            'category_id' => 2114,
            'level' => 3,
            'title' => 'Formal Shoes',
            'subtitle' => 'Step Into Comfort & Style',
            'bg_color' => '#FFF7EB'
        ]);
        
        $data['category_card_3'] = $this->load->controller('product/category_card', [
            'category_id' => 2141,
            'level' => 1,
            'title' => "Kid's",
            'subtitle' => 'Outfits for Every Little Occasion',
            'bg_color' => '#FFFCE1'
        ]);
        $data['category_card_4'] = $this->load->controller('product/category_card', [
            'category_id' => 2106,
            'level' => 3,
            'title' => "Shirt's",
            'subtitle' => 'Smart Looks Start Here',
            'bg_color' => '#E0F2FF'
        ]);

// -----------------------------
// second banners
        $data['large_banners'] = $this->load->controller('product/large_banners');


    // referral start {
    	if (isset($this->request->get['ref'])) {
			$this->load->model('ipoffer/offer');
			if (!$this->model_ipoffer_offer->isReferralOfferEnabled()) {
				$this->session->data['error'] = 'Referral offer is currently disabled.';
			} else {
				$referral_code = $this->request->get['ref'];
				$referral = $this->model_ipoffer_offer->getReferralByCode($referral_code);
				if ($referral && $referral['status'] == 1) {
					// Only set session and increment visit if approved
					if ($this->customer->isLogged()) {
					    $sameuser=$this->model_ipoffer_offer->getReferralByCode();
						if($sameuser['customer_id'] != $this->customer->getCustomerID()){
    						if ($this->model_ipoffer_offer->checkuserfirsttime()) {
    							$this->session->data['referral_code'] = $referral_code;
    							$this->model_ipoffer_offer->incrementReferralVisit($referral_code);
    						}
						}
					} else {
						$this->session->data['referral_code'] = $referral_code;
						$this->model_ipoffer_offer->incrementReferralVisit($referral_code);
					}
				} else {
					// Show expired message and do not set session
					$this->session->data['referral_code'] = null;
					// $this->session->data['error'] = 'Referral link is expired.';
				}
			}
		}
    // referral end }
    

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
