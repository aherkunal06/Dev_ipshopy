<?php
class ControllerProductIndependenceDay extends Controller {
    public function index() {
        $this->load->language('product/independence_day');
        $this->load->model('catalog/independence_day');
        $this->load->model('tool/image');



        
$main_categories = [59]; // Example main category ID(s)

$data['main_categories'] = [];

foreach ($main_categories as $cat_id) {
    $category_info = $this->model_catalog_independence_day->getCategoryName($cat_id);
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
            'href' => $this->url->link(
                'product/category',
                'path=' . $cat_id . '&discount=40'
            ),
            'subcategories' => $sub_data
        ];
    }
}



        // --- SECOND CATEGORIES BLOCK (4 SUBCATEGORY CARDS) ---

        
$second_cat_id = 2123;

$category_name_2 = $this->model_catalog_independence_day->getCategoryName($second_cat_id);
$subcategories_2 = $this->model_catalog_independence_day->getActiveDiscountedSubcategories($second_cat_id);

$sub_data_2 = [];
foreach ($subcategories_2 as $subcategory) {
    $product = $this->model_catalog_independence_day->getLowestDiscountedProductWithPrice($subcategory['category_id'], 40, 100);

    if ($product) {
        $original_price = $product['original_price'];
        $special_price = $product['special_price'];
        $discount = (($original_price - $special_price) / $original_price) * 100;

        $lowest_price_formatted = $this->currency->format($special_price, $this->session->data['currency']);
        $original_price_formatted = $this->currency->format($original_price, $this->session->data['currency']);
        $discount_percent = round($discount, 2);
    } else {
        $lowest_price_formatted = null;
        $original_price_formatted = null;
        $discount_percent = null;
    }

    $sub_data_2[] = [
        'name'             => $subcategory['name'],
        'lowest_price'     => $lowest_price_formatted,
        'original_price'   => $original_price_formatted,
        'discount_percent' => $discount_percent,
        'thumb'            => $subcategory['image'] 
                               ? $this->model_tool_image->resize($subcategory['image'], 200, 200) 
                               : $this->model_tool_image->resize('placeholder.png', 200, 200),
        'href'             => $this->url->link('product/category', 'path=' . $second_cat_id . '_' . $subcategory['category_id'] . '&discount=40')
    ];
}


$sub_data_2 = array_slice($sub_data_2, 0, 4);

$data['second_category'] = [
    'name'         => $category_name_2,
    'subcategories'=> $sub_data_2
];




// --- third CATEGORIES BLOCK (4 SUBCATEGORY CARDS) ---
$third_id = 2104;

$category_name_3 = $this->model_catalog_independence_day->getCategoryName($third_id);
$subcategories_3 = $this->model_catalog_independence_day->getActiveDiscountedSubcategories($third_id);

$sub_data_3 = [];

foreach ($subcategories_3 as $subcategory) {
   
    $product = $this->model_catalog_independence_day->getLowestDiscountedProductWithPrice($subcategory['category_id'], 40, 100);

    if ($product) {
        $original_price = $product['original_price'];
        $special_price  = $product['special_price'];
        $discount       = (($original_price - $special_price) / $original_price) * 100;

        $lowest_price_formatted   = $this->currency->format($special_price, $this->session->data['currency']);
        $original_price_formatted = $this->currency->format($original_price, $this->session->data['currency']);
        $discount_percent         = round($discount, 2);
    } else {
        $lowest_price_formatted   = null;
        $original_price_formatted = null;
        $discount_percent         = null;
    }

    $sub_data_3[] = [
        'name'             => $subcategory['name'],
        'lowest_price'     => $lowest_price_formatted,
        'original_price'   => $original_price_formatted,
        'discount_percent' => $discount_percent,
        'thumb'            => $subcategory['image']
                               ? $this->model_tool_image->resize($subcategory['image'], 200, 200)
                               : $this->model_tool_image->resize('placeholder.png', 200, 200),
        'href'             => $this->url->link('product/category', 'path=' . $third_id . '_' . $subcategory['category_id'] . '&discount=40')
    ];
}

$sub_data_3 = array_slice($sub_data_3, 0, 4);

$data['third_category'] = [
    'name'         => $category_name_3,
    'subcategories'=> $sub_data_3
];











// --- fourth CATEGORIES BLOCK (3 SUBCATEGORY CARDS) ---



$fourth_id = 2117;


$category_name_4 = $this->model_catalog_independence_day->getCategoryName($fourth_id);


$subcategories_4 = $this->model_catalog_independence_day->getActiveDiscountedSubcategories($fourth_id);

$sub_data_4 = [];

foreach ($subcategories_4 as $subcategory) {

    
    $product = $this->model_catalog_independence_day->getLowestDiscountedProductWithPrice(
        $subcategory['category_id'], 
        40, 
        100 
    );

    if ($product) {
        $original_price = $product['original_price'];
        $special_price  = $product['special_price'];
        $discount       = (($original_price - $special_price) / $original_price) * 100;

        $lowest_price_formatted   = $this->currency->format($special_price, $this->session->data['currency']);
        $original_price_formatted = $this->currency->format($original_price, $this->session->data['currency']);
        $discount_percent         = round($discount, 2);
    } else {
        $lowest_price_formatted   = null;
        $original_price_formatted = null;
        $discount_percent         = null;
    }

    $sub_data_4[] = [
        'name'             => $subcategory['name'],
        'lowest_price'     => $lowest_price_formatted,
        'original_price'   => $original_price_formatted,
        'discount_percent' => $discount_percent,

       
        'thumb' => $subcategory['image']
            ? $this->model_tool_image->resize($subcategory['image'], 200, 300)
            : $this->model_tool_image->resize('placeholder.png', 200, 200),

      
        'href'  => $this->url->link(
            'product/category', 
            'path=' . $fourth_id . '_' . $subcategory['category_id'] . '&discount=40'
        )
    ];
}

$sub_data_4 = array_slice($sub_data_4, 0, 3);


$data['fourth_category'] = [
    'name'         => $category_name_4,
    'subcategories'=> $sub_data_4
];





$category_ids = [2123, 2131, 2104, 2112, 1887, 3184, 1999, 2000, 2117, 2102,3058,2167];

$category_list = [];

foreach ($category_ids as $cat_id) {
   
    $category_name = $this->model_catalog_independence_day->getCategoryName($cat_id);

    $category_list[] = [
        'name'   => $category_name,
       
        'href'   => $this->url->link('product/category', 'path=' . $cat_id . '&discount=40'),
      
        'active' => isset($this->request->get['path']) && $this->request->get['path'] == $cat_id
    ];
}

$data['manual_categories'] = $category_list;




        $this->document->setTitle('Independence Day Offers');
        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('product/independence_day', $data));
    }
}