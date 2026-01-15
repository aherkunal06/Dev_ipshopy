<?php
class ControllerCommonAllCategories extends Controller {
    public function index() {

        if (!empty($this->request->get['ajax']) && isset($this->request->get['group_ids'])) {
            $this->load->model('catalog/all_categories');
            $this->load->model('tool/image');
            $group_ids = array_map('intval', explode(',', $this->request->get['group_ids']));

            $categories_with_children = $this->getCategoriesData($group_ids);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($categories_with_children));
            return;
        }

        // Normal page load
        $this->load->model('catalog/all_categories');
        $this->load->model('tool/image');

        
        // $sections = [
        //     'Footwear'    => [2112, 2131, 3223],
        //     'Electronics' => [1887],
        //     'Clothes'     => [2104, 2123,2142 ],
        //     'bags'        =>[1410],
        //     'Home Appliances'=>[120],
        //     'Grocery & Gourmet Foods'=>[368],
        //     'Pet Supplies' =>[598],
        //     'Beauty & Personal Care'=>[280]

        // ];

// "D:\xampp\htdocs\dev.ipshopy\image\catalog\demo\apple_logo.jpg"
// "D:\xampp\htdocs\dev.ipshopy\image\catalog\demo\banners\iPhone6.jpg"
  $sections = [
    'Footwear' => [
        'ids' => [2112, 2131, 3223],
        'image' => 'catalog/demo/apple_logo.jpg',
        'banner' => 'catalog\shoes\banner-01.png'
    ],
    'Electronics' => [
        'ids' => [1887],
        'image' => 'catalog/demo/sony_vaio_2.jpg',
        'banner' => 'catalog/demo/banners/MacBookAir.jpg'
    ],
    'Clothes' => [
        'ids' => [2104, 2123, 2142],
        'image' => 'catalog\mobile\banner\womens\2download.jpg',
        'banner' => 'catalog\mobile\banner\womens\1download.jpg'

    ],
    'bags' => [
        'ids' => [3296, 3567],
        'image' => 'catalog\mobile\banner\womens\2download.jpg',
        'banner' => 'catalog\mobile\banner\womens\1download.jpg'
    ],
    'Home Appliances'=>[
    'ids' => [2006],
     'image' => 'catalog\mobile\banner\womens\2download.jpg',
        'banner' => 'catalog\mobile\banner\womens\1download.jpg'
    ],
    'Grocery & Gourmet Foods'=>[
    'ids' => [2254],
         'image' => 'catalog\mobile\banner\womens\2download.jpg',
        'banner' => 'catalog\mobile\banner\womens\1download.jpg'
    ],
    'Pet Supplies' =>[
        'ids' => [2484],
         'image' => 'catalog\mobile\banner\womens\2download.jpg',
        'banner' => 'catalog\mobile\banner\womens\1download.jpg'
    ],
     'Beauty & Personal Care'=>[
        'ids' => [2166],
         'image' => 'catalog\mobile\banner\womens\2download.jpg',
        'banner' => 'catalog\mobile\banner\womens\1download.jpg'
     ]
];

$data['sections'] = [];
foreach ($sections as $section_name => $info) {
    $data['sections'][] = [
        'name' => $section_name,
        'href' => 'javascript:void(0);',
        'cat_ids' => implode(',', $info['ids']),
        'thumb' => $this->model_tool_image->resize($info['image'], 38, 38),
        'banner' => $this->model_tool_image->resize($info['banner'], 800, 200)
    ];
}


        $data['categories_with_children'] = [];

        if (isset($this->request->get['group_ids'])) {
            $group_ids = array_map('intval', explode(',', $this->request->get['group_ids']));
            $data['categories_with_children'] = $this->getCategoriesData($group_ids);
        }

        $data['custom_breadcrumb'] = $this->load->controller('product/breadcrumbpath');
        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('common/all_categories', $data));
    }

    private function getCategoriesData($group_ids) {
        $this->load->model('catalog/all_categories');
        $this->load->model('tool/image');
        $categories_with_children = [];

        foreach ($group_ids as $gid) {
            $parent_id = $this->model_catalog_all_categories->getCategoryParentId($gid);

            if ($parent_id == 0) {
                $subcategories = $this->model_catalog_all_categories->getSubcategories($gid);

                foreach ($subcategories as $sub) {
                    $sub_subcategories = $this->model_catalog_all_categories->getSubcategories($sub['category_id']);
                    $children_list = [];

                    foreach ($sub_subcategories as $child) {
                        $product_count = $this->model_catalog_all_categories->getProductCountByCategory($child['category_id']);
                        if ($product_count > 0) {
                            $children_list[] = [
                                'name'  => $child['name'],
                                'thumb' => $child['image'] ? $this->model_tool_image->resize($child['image'], 150, 150) : 'placeholder.png',
                                'href'  => $this->url->link('product/category', 'path=' . $sub['category_id'] . '_' . $child['category_id'])
                            ];
                        }
                    }

                    if (!empty($children_list)) {
                        $categories_with_children[] = [
                            'heading'  => $sub['name'],
                            'children' => $children_list
                        ];
                    }
                }
            } else {
                $children = $this->model_catalog_all_categories->getSubcategories($gid);
                $children_list = [];

                foreach ($children as $child) {
                    $product_count = $this->model_catalog_all_categories->getProductCountByCategory($child['category_id']);
                    if ($product_count > 0) {
                        $children_list[] = [
                            'name'  => $child['name'],
                            'thumb' => $child['image'] ? $this->model_tool_image->resize($child['image'], 150, 150) : 'placeholder.png',
                            'href'  => $this->url->link('product/category', 'path=' . $gid . '_' . $child['category_id'])
                        ];
                    }
                }

                if (!empty($children_list)) {
                    $cat_name = $this->model_catalog_all_categories->getCategoryName($gid);
                    $categories_with_children[] = [
                        'heading'  => $cat_name,
                        'children' => $children_list
                    ];
                }
            }
        }

        return $categories_with_children;
    }
}