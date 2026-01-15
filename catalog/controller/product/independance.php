<?php
class ControllerProductIndependenceDay extends Controller {
    public function index() {
        $this->load->language('product/independence_day');
        $this->load->model('catalog/independence_day');
        $this->load->model('tool/image');

        // मुख्य category ids (तू हवे ते IDs इथे ठेऊ शकतो)
    $main_categories = [59];

$data['main_categories'] = [];

foreach ($main_categories as $cat_id) {
    // Category info मिळव
    $category_info = $this->model_catalog_independence_day->getCategoryName($cat_id);

    // जर array असेल तर name काढा, अन्यथा direct value घ्या
    $category_name = is_array($category_info) ? $category_info['name'] : $category_info;

    // Subcategories
    $subcategories = $this->model_catalog_independence_day->getActiveDiscountedSubcategories($cat_id);

    $sub_data = [];
    foreach ($subcategories as $subcategory) {
        $sub_data[] = [
            'name'  => $subcategory['name'],
            'thumb' => $subcategory['image'] 
                ? $this->model_tool_image->resize($subcategory['image'], 200, 200) 
                : $this->model_tool_image->resize('placeholder.png', 200, 200),
            'href'  => $this->url->link(
                'product/category',
                'path=' . $cat_id . '_' . $subcategory['category_id'] . '&discount=40'
            )
        ];
    }

    if (!empty($sub_data)) {
        $data['main_categories'][] = [
            'name' => $category_name,
            'subcategories' => $sub_data
        ];
    }
}



        // --- SECOND CATEGORIES BLOCK (4 SUBCATEGORY CARDS) ---

        // दुसरी category (उदा. ID = 88)
$second_cat_id = 59;

$category_name_2 = $this->model_catalog_independence_day->getCategoryName($second_cat_id);
$subcategories_2 = $this->model_catalog_independence_day->getActiveDiscountedSubcategories($second_cat_id);

$sub_data_2 = [];
foreach ($subcategories_2 as $subcategory) {
    $sub_data_2[] = [
        'name'  => $subcategory['name'],
        'thumb' => $subcategory['image'] 
            ? $this->model_tool_image->resize($subcategory['image'], 200, 200) 
            : $this->model_tool_image->resize('placeholder.png', 200, 200),
        'href'  => $this->url->link('product/category', 'path=' . $second_cat_id . '_' . $subcategory['category_id'] . '&discount=40')
    ];
}

// 4 subcategories पर्यंतच limit करा
$sub_data_2 = array_slice($sub_data_2, 0, 4);

$data['second_category'] = [
    'name'         => $category_name_2,
    'subcategories'=> $sub_data_2
];




// --- third CATEGORIES BLOCK (4 SUBCATEGORY CARDS) ---

$third_id = 59;

$category_name_3 = $this->model_catalog_independence_day->getCategoryName($third_id);
$subcategories_3 = $this->model_catalog_independence_day->getActiveDiscountedSubcategories($third_id);

$sub_data_3 = [];

foreach ($subcategories_3 as $subcategory) {
    $lowest_price = $this->model_catalog_independence_day->getLowestDiscountedPrice($subcategory['category_id'], 40, 100);

    $sub_data_3[] = [
        'name'  => $subcategory['name'],
        'lowest_price' => $lowest_price 
            ? $this->currency->format($lowest_price, $this->session->data['currency']) 
            : null,
        'thumb' => $subcategory['image']
            ? $this->model_tool_image->resize($subcategory['image'], 200, 200)
            : $this->model_tool_image->resize('placeholder.png', 200, 200),
        'href'  => $this->url->link('product/category', 'path=' . $third_id . '_' . $subcategory['category_id'] . '&discount=40')
    ];
}

$sub_data_3 = array_slice($sub_data_3, 0, 4);

$data['third_category'] = [
    'name'         => $category_name_3,
    'subcategories'=> $sub_data_3
];












// --- fourth CATEGORIES BLOCK (3 SUBCATEGORY CARDS) ---
$fourth_id = 59;

// Main category name
$category_name_4 = $this->model_catalog_independence_day->getCategoryName($fourth_id);

// Get subcategories
$subcategories_4 = $this->model_catalog_independence_day->getActiveDiscountedSubcategories($fourth_id);

$sub_data_4 = [];

foreach($subcategories_4 as $subcategory) {

    // Lowest price मिळव (40% discount logic)
    $lowest_price = $this->model_catalog_independence_day->getLowestDiscountedPrice(
        $subcategory['category_id'], 
        40, // discount percentage
        100 // optional limit
    );

    $sub_data_4[] = [
        'name' => $subcategory['name'],
        
        // Lowest price format (जर price असेल तर format करा, नसेल तर null)
        'lowest_price' => $lowest_price 
            ? $this->currency->format($lowest_price, $this->session->data['currency']) 
            : null,

        // Image resize (जर image नसेल तर placeholder)
        'thumb' => $subcategory['image']
            ? $this->model_tool_image->resize($subcategory['image'], 200, 300)
            : $this->model_tool_image->resize('placeholder.png', 200, 200),

        // Link तयार करणे
        'href'  => $this->url->link(
            'product/category', 
            'path=' . $fourth_id . '_' . $subcategory['category_id'] . '&discount=40'
        )
    ];
}

// फक्त पहिल्या 3 subcategories घेणे
$sub_data_4 = array_slice($sub_data_4, 0, 3);

// Data view ला पाठवणे
$data['fourth_category'] = [
    'name'         => $category_name_4,
    'subcategories'=> $sub_data_4
];


        $this->document->setTitle('Independence Day Offers');
        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('product/independence_day', $data));
    }
}