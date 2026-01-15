<?php 
class ControllerCommonHomeContent extends Controller {
    public function index() {
        $this->load->model('catalog/home_content');
        $this->load->model('tool/image');

        // -------------------- Section 1 --------------------
        $category_id1 = 2123;
        $subcategories1 = $this->model_catalog_home_content->getSubCategoriesWithPriceAndRating($category_id1);
        $subcategories1 = array_slice($subcategories1, 0, 6);

        $data['category_name1'] = $subcategories1[0]['parent_name'] ?? ''; // heading
        $data['subcategories1'] = [];

        foreach ($subcategories1 as $subcategory1) {
            $data['subcategories1'][] = [
                'name' => $subcategory1['subcategory_name'],
                'thumb'=> $subcategory1['image']
                            ? $this->model_tool_image->resize($subcategory1['image'], 200, 200)
                            : $this->model_tool_image->resize('catalog/banners/logo/logo1.png', 130, 130),
                'href' => $this->url->link('product/category', 'path=' . $subcategory1['category_id']),
                'price' => $subcategory1['lowest_price'],
                'rating' => $subcategory1['highest_rating'] ? $subcategory1['highest_rating'] : 0
            ];
        }

        // -------------------- Section 2 --------------------
        // $category_id2 = 1887;
        // $subcategories2 = $this->model_catalog_home_content->getSubCategoriesWithPriceAndRating($category_id2);
        // $subcategories2 = array_slice($subcategories2, 0, 6);

        // $data['category_name2'] = $subcategories2[0]['parent_name'] ?? ''; // heading
        // $data['subcategories2'] = [];

        // foreach ($subcategories2 as $subcategory2) {
        //     $data['subcategories2'][] = [
        //         'name' => $subcategory2['subcategory_name'],
        //         'thumb'=> $subcategory2['image']
        //                     ? $this->model_tool_image->resize($subcategory2['image'], 200, 200)
        //                     : $this->model_tool_image->resize('placeholder.png', 200, 200),
        //         'href' => $this->url->link('product/category', 'path=' . $subcategory2['category_id']),
        //         'price' => $subcategory2['lowest_price'],
        //         'rating' => $subcategory2['highest_rating'] ? $subcategory2['highest_rating'] : 0
        //     ];
        // }
 $category_group_settings = [
    'groups' => [
        [
            'key' => 'Beauty_group',
            'title' => "Beauty & Personal Care",
            'view_all_href' => $this->url->link('product/category', 'path=2166'),
            'rows' => [
                'Lipstick' => ['key' => 'Lipstick', 'parent_ids' => 2185],
                'Bangles' => ['key' => 'Bangles', 'parent_ids' => 2188],
                'Sanitary ' => ['key' => 'Sanitary ', 'parent_ids' => 2217]
            ]
        ]
        ]
        ];
        $group_blocks= $this->load->controller('product/category_group', $category_group_settings);
      $data['ladies_group'] = $group_blocks['groups']['Beauty_group']['group_combined'] ?? '';
          $data['recent_view'] = $this->load->view('common/recent_view');
		$data['popular_brands']= $this->load->controller('common/popular_brands' );
        // return $this->load->view('common/home_content', $data);
			$data['header'] = $this->load->controller('common/header');
			$data['most_view'] = $this->load->controller('common/most_view');

         $this->response->setOutput($this->load->view('common/home_content', $data));
    }

      
    public function loadMore()
    {
        $this->load->model('catalog/home_content');
        $this->load->model('tool/image');

        $page  = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

        // page 1 → indices 0,1
        // page 2 → indices 2,3
        // page 3 → index 4
        // page 4 → index 5
        // page 5 → index 6

        if ($page <= 2) {
            $limit = 2;
            $start = ($page - 1) * 2;
        } else {
            $limit = 1;
            $start = 4 + ($page - 3);
        }

        // Page wise image sizes
        $page_image_sizes = [
            1 => ['w' => 200, 'h' => 200],
            2 => ['w' => 130, 'h' => 130]
       ];

        $img_w = isset($page_image_sizes[$page]) ? $page_image_sizes[$page]['w'] : 200;
        $img_h = isset($page_image_sizes[$page]) ? $page_image_sizes[$page]['h'] : 200;
        // ----------------------------
          $page_image_sizesP = [
            1 => ['w' => 130, 'h' => 130],
            2 => ['w' => 240, 'h' => 240]
        ];

        $img_wp = isset($page_image_sizesP[$page]) ? $page_image_sizesP[$page]['w'] : 200;
        $img_hp = isset($page_image_sizesP[$page]) ? $page_image_sizesP[$page]['h'] : 200;
        // -------------------------

        $section = [
            'Beauty & Personal Care'    => 2167,
            'Men'    => 2104,
            'Electronics' => 1887,
            'Women Wear'        => 3106,
            'Kids'        => 2141,
            'Men'         => 2103,
            'Sports'      => 2374,

            // 'Fashion'     => 2102,
            'Beauty'      => 2166,
            'Water Bottles' => 3058,
            'Footwear'     => 2112,
            'Electronics' => 1887,
            'Home & Kitchen' => 1999,
            'Pet Supplies' => 2484,
            'Grocery & Gourmet Foods' => 2254,
            'Toys & Games' => 2352,
            'Baby Products' => 2618
        ];

        $paged_sections = array_slice($section, $start, $limit, true);

        if (empty($paged_sections)) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([]));
            return;
        }

        $index = $start;
        $data['sections'] = [];

        foreach ($paged_sections as $name => $category_id) {
            $card_limit = 4;
            if ($index == 0) {
                $card_limit = 6;
            } elseif ($index == 1) {
                $card_limit = 8;
            } elseif ($index == 2) {
                $card_limit = 6;
            } elseif ($index == 3) {
                $card_limit = 3;
            } elseif ($index == 4) {
                $card_limit = 6;
            }

            
            // Subcategory builder with dynamic image size
            $buildSubcats = function ($category_id, $card_limit) use ($img_w, $img_h) {
                $items = [];
                $subcats = $this->model_catalog_home_content->getAllSubCategoriesByParent($category_id, 0, $card_limit);

                foreach ($subcats as $sub) {
                    if (strtolower(trim($sub['name'])) == 'unisex') {
                        continue;
                    }
                    $thumb = !empty($sub['image'])
                        ? $this->model_tool_image->resize($sub['image'], $img_w, $img_h)
                        : $this->model_tool_image->resize('catalog/banners/logo/logo1.png', $img_w, $img_h);

                    $price = !empty($sub['lowest_price'])
                        ? $this->currency->format($sub['lowest_price'], $this->session->data['currency'])
                        : '';

                    $items[] = [
                        'name'  => $sub['name'],
                        'thumb' => $thumb,
                        'price' => $price,
                        'href'  => $this->url->link('product/category', 'path=' . $sub['category_id'])
                    ];
                }
                return $items;
            };

            // ✅ Product builder with dynamic image size
            $buildProducts = function ($category_id, $card_limit) use ($img_wp, $img_hp) {
                $items = [];
                $seen = [];
                $fetch_limit = $card_limit * 3;
                $prods = $this->model_catalog_home_content->getProductsByCategory($category_id, 0, $fetch_limit);

                foreach ($prods as $prod) {
                    if (in_array($prod['product_id'], $seen)) {
                        continue;
                    }

                    $seen[] = $prod['product_id'];

                    $thumb = !empty($prod['image'])
                        ? $this->model_tool_image->resize($prod['image'], $img_wp, $img_hp)
                        : $this->model_tool_image->resize('catalog/banners/logo/logo1.png', $img_wp, $img_hp);

                    $items[] = [
                        'product_id' => $prod['product_id'],
                        'name'    => $prod['name'],
                        'thumb'   => $thumb,
                        'price'   => $this->currency->format($prod['price'], $this->session->data['currency']),
                        'special' => !empty($prod['special'])
                            ? $this->currency->format($prod['special'], $this->session->data['currency'])
                            : '',
                        'rating'  => isset($prod['rating']) ? (int)$prod['rating'] : 0,
                        'href'    => $this->url->link('product/product', 'product_id=' . $prod['product_id'])
                    ];

                    if (count($items) >= $card_limit) {
                        break;
                    }
                }

                return $items;
            };

            // --------------------------------------------
            if ($index >= 4) {
                $items = $buildSubcats($category_id, $card_limit);

                if ($index >= 5) {
                    if (count($items) == 3) {
                        $items = array_slice($items, 0, 2);
                    }
                }

                $data['sections'][] = [
                    'type'  => 'subcategory',
                    'title' => $name,
                    'items' => $items,
                    'limit' => $card_limit
                ];
                $index++;
                continue;
            }

            // Early indices (0..4)
            if ($index % 2 == 0) {
                $items = $buildSubcats($category_id, $card_limit);
                $data['sections'][] = [
                    'type'  => 'subcategory',
                    'title' => $name,
                    'items' => $items,
                    'limit' => $card_limit
                ];
            } else {
                $items = $buildProducts($category_id, $card_limit);
                $data['sections'][] = [
                    'type'  => 'product',
                    'title' => $name,
                    'items' => $items,
                    'limit' => $card_limit
                ];
            }

            $index++;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    public function loadMoreD()
    {
        $this->load->model('catalog/home_content');
        $this->load->model('tool/image');

        $page  = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

        // page 1 → indices 0,1
        // page 2 → indices 2,3
        // page 3 → index 4
        // page 4 → index 5
        // page 5 → index 6
        if ($page <= 2) {
            $limit = 2;
            $start = ($page - 1) * 2;
        } else {
            $limit = 1;
            $start = 4 + ($page - 3);
        }

        // Page wise image sizes
        $page_image_sizes = [
            1 => ['w' => 140, 'h' => 140],
            2 => ['w' => 210, 'h' => 210]

        ];
        

        $img_w = isset($page_image_sizes[$page]) ? $page_image_sizes[$page]['w'] : 200;
        $img_h = isset($page_image_sizes[$page]) ? $page_image_sizes[$page]['h'] : 200;

        $section = [
            'Top Deals in Beauty & Personal Care'    => [2112, 2131],
            'Men\'s Fashion & Lifestyle' => 2103,
            'Latest Electronics & Gadgets' => 1887,
            'Trendy Men\'s Wear'        => 2104,
            'Fashion for Everyone'        => [2102,2104], 
            'Kids Fashion & Accessories'      => [2141, 2352],
            'Exclusive Beauty Picks'      => 2166,
            'Home & Kitchen Essentials' => 1999,
            'Everything for Your Pets' => 2484,
            'Groceries & Daily Needs' => [2254, 2256, 2257],

            'Baby Care & Essentials' => 2618
        ];

        $paged_sections = array_slice($section, $start, $limit, true);

        if (empty($paged_sections)) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([]));
            return;
        }

        $index = $start;
        $data['sections'] = [];
        foreach ($paged_sections as $name => $category_id) {
            $card_limit = 5;
            if ($index == 0) {
                $card_limit = 5;
            } elseif ($index == 1) {
                $card_limit = 6;
            } elseif ($index == 2) {
                $card_limit = 5;
            } elseif ($index == 3) {
                $card_limit = 5;
            } elseif ($index == 4) {
                $card_limit = 8;
            }

            if (isset($this->request->get['card_limit'][$index])) {
                $card_limit = (int)$this->request->get['card_limit'][$index];
            }

            // Subcategory builder with dynamic image size
            $buildSubcats = function ($category_id, $card_limit,$index) use ($img_w, $img_h) {
                $items = [];
                $subcats = $this->model_catalog_home_content->getAllSubCategoriesByParent($category_id, 0, $card_limit);
        $counts=0;

                foreach ($subcats as $sub) {
                    if (strtolower(trim($sub['name'])) == 'unisex') {
                        continue;
                    }
                 
                    if ($index == 4 && $counts == 4) {
                    $thumb = !empty($sub['image'])
                        ? $this->model_tool_image->resize($sub['image'], 530, 530)
                        : $this->model_tool_image->resize('placeholder.png', 530, 530);
                    }else{
                    $thumb = !empty($sub['image'])
                        ? $this->model_tool_image->resize($sub['image'], $img_w, $img_h)
                        : $this->model_tool_image->resize('placeholder.png', $img_w, $img_h);
                        
                    }

                    $price = !empty($sub['lowest_price'])
                        ? $this->currency->format($sub['lowest_price'], $this->session->data['currency'])
                        : '';

                    $items[] = [
                        'name'  => $sub['name'],
                        'thumb' => $thumb,
                        'price' => $price,
                        'href'  => $this->url->link('product/category', 'path=' . $sub['category_id'])
                    ];
                    $counts++;
                }
                return $items;
            };

            // ✅ Product builder with dynamic image size
           // ✅ Product builder with dynamic image size
$buildProducts = function ($category_id, $card_limit) use ($img_w, $img_h) {
    $items = [];
    $seen = [];
    $fetch_limit = $card_limit * 3;
    $prods = $this->model_catalog_home_content->getProductsByCategory($category_id, 0, $fetch_limit);

    // wishlist check
    $wishlist_ids = [];
    if ($this->customer->isLogged()) {
        $this->load->model('account/wishlist');
        $wishlist = $this->model_account_wishlist->getWishlist();
        $wishlist_ids = array_column($wishlist, 'product_id');
    } else {
        if (!empty($this->session->data['wishlist'])) {
            $wishlist_ids = $this->session->data['wishlist'];
        }
    }

    foreach ($prods as $prod) {
        if (in_array($prod['product_id'], $seen)) {
            continue;
        }
        $seen[] = $prod['product_id'];

        $thumb = !empty($prod['image'])
            ? $this->model_tool_image->resize($prod['image'], $img_w, $img_h)
            : $this->model_tool_image->resize('placeholder.png', $img_w, $img_h);

        $items[] = [
            'product_id' => $prod['product_id'],
            'name'    => $prod['name'],
            'thumb'   => $thumb,
            'price'   => $this->currency->format($prod['price'], $this->session->data['currency']),
            'special' => !empty($prod['special'])
                ? $this->currency->format($prod['special'], $this->session->data['currency'])
                : '',
            'rating'  => isset($prod['rating']) ? (int)$prod['rating'] : 0,
            'discount' => (!empty($prod['special']) && $prod['price'] > 0)
                ? round((($prod['price'] - $prod['special']) / $prod['price']) * 100)
                : 0,
                
            'in_wishlist' => in_array($prod['product_id'], $wishlist_ids) ? 1 : 0, // ✅ new field
            'href'    => $this->url->link('product/product', 'product_id=' . $prod['product_id'])
        ];

        if (count($items) >= $card_limit) {
            break;
        }
    }

    return $items;
};


            // --------------------------------------------
            if ($index >= 4) {
                $items = $buildSubcats($category_id, $card_limit,$index);

                if ($index >= 5) {
                    if (count($items) == 3) {
                        $items = array_slice($items, 0, 2);
                    }
                }

                $data['sections'][] = [
                    'type'  => 'subcategory',
                    'title' => $name,
                    'items' => $items,
                    'limit' => $card_limit
                ];
                $index++;
                continue;
            }

            // Early indices (0..4)
            if ($index % 2 == 0) {
                $items = $buildSubcats($category_id, $card_limit,$index);
                $data['sections'][] = [
                    'type'  => 'subcategory',
                    'title' => $name,
                    'items' => $items,
                    'limit' => $card_limit
                ];
            } else {
                $items = $buildProducts($category_id, $card_limit);
                $data['sections'][] = [
                    'type'  => 'product',
                    'title' => $name,
                    'items' => $items,
                    'limit' => $card_limit
                ];
            }

            $index++;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
?>
 