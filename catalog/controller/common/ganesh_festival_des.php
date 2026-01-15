<?php
class ControllerCommonGaneshFestivalDes extends Controller {
    public function index() {
        $this->load->model('catalog/ganesh_festival');
        $this->load->model('tool/image');

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        // Top Banner
        $data['top_banner'] = 'image\catalog\festival\ganesha123.jpg';

        // Categories
        // $category_ids = [2123, 2002, 2131, 2104, 2112,2166, 2123, 2142];
        // $categories_raw = $this->model_catalog_ganesh_festival->getFestivalCategories($category_ids);
        // $data['categories'] = [];

        // foreach ($categories_raw as $category_info) {
        //     if ($category_info) {
        //         $data['categories'][] = [
        //             'name'  => $category_info['name'],
        //             'thumb' => $this->model_tool_image->resize($category_info['image'], 200, 200),
        //             'href'  => $this->url->link('product/category', 'path=' . $category_info['category_id'])
        //         ];
        //     }
        // }





        // Example: Festival Cards Data
$data['categories'] = [
    [
         'name'  => "Home decore",
        'thumb' => 'image\catalog\product\fashion\home.png',
        'href'  => $this->url->link('product/category', 'path=2002')
    ],
    [
        'name'  => "Beauty",
        'thumb' => 'image\catalog\product\fashion\buatyandcare.jpg',
        'href'  => $this->url->link('product/category', 'path=2166')
    ],
    [
        'name'  => "Women",
        'thumb' => 'image\catalog\product\fashion\women.jpg',
        'href'  => $this->url->link('product/category', 'path=2123')
    ],
    [
        'name'  => "Women's Accessories",
        'thumb' => 'image\catalog\product\fashion\womenASS.jpg',
        'href'  => $this->url->link('product/category', 'path=2136')
    ],
    [
        'name'  => "Electronics",
        'thumb' => 'image\catalog\product\fashion\wireless-earbuds.jpg',
        'href'  => $this->url->link('product/category', 'path=1887')
    ],
    [
        'name'  => "Men's Accessories",
        'thumb' => 'image\catalog\product\fashion\male-wedding-shoes.jpg',
        'href'  => $this->url->link('product/category', 'path=2117')
    ],
    [
        'name'  => "Jewellary",
        'thumb' => 'image\catalog\product\fashion\luxury-shin.jpg',
        'href'  => $this->url->link('product/category', 'path=3184')
    ]
];




        // Second Banner
        $data['second_banner'] = 'image\catalog\banners\slide_banner_2.png';

        // Women's Dresses Subcategories 
        $category_ids = [2124, 2125, 3108,3110,3107,2130];
$subcategories_raw = $this->model_catalog_ganesh_festival->getCategoriesByIds($category_ids);

$data['subcategories'] = [];

foreach ($subcategories_raw as $subcategory) {
    $special_price = $subcategory['special_price'] 
        ? $this->currency->format($subcategory['special_price'], $this->session->data['currency']) 
        : false;

    $original_price = $subcategory['original_price'] 
        ? $this->currency->format($subcategory['original_price'], $this->session->data['currency']) 
        : false;

    $data['subcategories'][] = [
        'name'  => $subcategory['name'],
        'thumb' => $subcategory['image']
            ? $this->model_tool_image->resize($subcategory['image'], 200, 200) 
            : $this->model_tool_image->resize('placeholder.png', 200, 200),
        'href'  => $this->url->link('product/category', 'path=' . $subcategory['category_id']),
        'special_price' => $special_price,
        'original_price' => $original_price
    ];
}

        // Third Banner
        $data['third_banner'] = 'image\catalog\banners\slide_banner_3.png';
    


// New Categories Section (Women's, Men's, Kids')
$data['festival_cards'] = [
    [
        'name'  => "Women's Wear",
        'thumb' => 'image/catalog/festival/women.png',
        'href'  => $this->url->link('product/category', 'path=2122')
    ],
    [
        'name'  => "Men's Wear",
        'thumb' => 'image/catalog/festival/men.png',
        'href'  => $this->url->link('product/category', 'path=2103')
    ],
    [
        'name'  => "Kids' Wear",
        'thumb' => 'image/catalog/festival/kids.png',
        'href'  => $this->url->link('product/category', 'path=2141')
    ],
    [
        'name'  => "Women's Footwear",
        'thumb' => 'image\catalog\product\fashion\shoesw.png',
        'href'  => $this->url->link('product/category', 'path=2131')
    ],
    [
        'name'  => "men's Footwear",
        'thumb' => 'image\catalog\product\fashion\—Pngtree—white shoes_20355730.png',
        'href'  => $this->url->link('product/category', 'path=2112')
    ],
    [
    'name'  => "kid's Footwear",
    'thumb' => 'image\catalog\product\fashion\—Pngtree—cute bunny kids shoes_22112103.png',
    'href'  => $this->url->link('product/category', 'path=2141,3231')
],

];

if ($this->config->get('config_theme') == 'so-mobile') {
    $data['festival_cards'] = array_slice($data['festival_cards'], 0, 3);
}




$men_category_id =[2105,2106,2112,2117,2107,2109];
        $subcategories = $this->model_catalog_ganesh_festival->getCategoriesByIds($men_category_id);
        $subcategories = array_slice($subcategories, 0, 6);
        $data['men_subcategories']=[];

        foreach($subcategories as $subcategory){
               $special_price = $subcategory['special_price'] 
        ? $this->currency->format($subcategory['special_price'], $this->session->data['currency']) 
        : false;

    $original_price = $subcategory['original_price'] 
        ? $this->currency->format($subcategory['original_price'], $this->session->data['currency']) 
        : false;

            $data['men_subcategories'][]=[
                'name' => $subcategory['name'],
                'thumb'=> $subcategory['image']
                          ? $this->model_tool_image->resize($subcategory['image'],200,200)
                          : $this->model_tool_image->resize('placeholder.png',200,200),
                'href' =>$this->url->link('product/category','path='.$subcategory['category_id']),
                'special_price' => $special_price,
        'original_price' => $original_price        
            ];
        }


// Extra Categories Section (ID wise cards)
$extra_category_ids = [2000,2001, 2002, 2003,2004,2005,2006]; 
$extra_categories_raw = $this->model_catalog_ganesh_festival->getFestivalCategories($extra_category_ids);

$data['festival_extra_cards'] = [];

foreach ($extra_categories_raw as $category_info) {
    if ($category_info) {
        $data['festival_extra_cards'][] = [
            'name'  => $category_info['name'],
            'thumb' => $this->model_tool_image->resize($category_info['image'], 300, 350),
            'href'  => $this->url->link('product/category', 'path=' . $category_info['category_id'])
        ];
    }
    
}


$kids = [2142, 2147, 3623,2352,3624,3727,2169];
$subcategories = $this->model_catalog_ganesh_festival->getCategoriesByIds($kids);

$data['kids'] = [];

foreach ($subcategories as $subcategory) {
    $special_price = $subcategory['special_price'] 
        ? $this->currency->format($subcategory['special_price'], $this->session->data['currency']) 
        : false;

    $original_price = $subcategory['original_price'] 
        ? $this->currency->format($subcategory['original_price'], $this->session->data['currency']) 
        : false;

    $data['kids'][] = [
        'name'  => $subcategory['name'],
        'thumb' => $subcategory['image']
            ? $this->model_tool_image->resize($subcategory['image'], 200, 200)
            : $this->model_tool_image->resize('placeholder.png', 200, 200),
        'href'  => $this->url->link('product/category', 'path=' . $subcategory['category_id']),
        'special_price'  => $special_price,
        'original_price' => $original_price         
    ];
}

// Fourth Banner
        $data['fourth_banner'] = 'image\catalog\banners\slide_banner_3.png';


        $elc_catgeory_id=[1888,1943,3389,3582,3044,1904];
        $subcategoriess  = $this->model_catalog_ganesh_festival->getCategoriesByIds($elc_catgeory_id);
        $data['electronics']=[];

        foreach($subcategoriess as $subcategory){
           $special_price = $subcategory['special_price'] 
        ? $this->currency->format($subcategory['special_price'], $this->session->data['currency']) 
        : false;

    $original_price = $subcategory['original_price'] 
        ? $this->currency->format($subcategory['original_price'], $this->session->data['currency']) 
        : false;
            $data['electronics'][]=[
                'name' => $subcategory['name'],
                'thumb'=> $subcategory['image']
                          ? $this->model_tool_image->resize($subcategory['image'],200,200)
                          : $this->model_tool_image->resize('placeholder.png',200,200),
                'href' =>$this->url->link('product/category','path='.$subcategory['category_id']),
                'special_price' => $special_price,
        'original_price' => $original_price    
            ];
            
        }




     if ($this->request->server['HTTP_USER_AGENT'] && strpos($this->request->server['HTTP_USER_AGENT'], 'Mobile') !== false) {
    // Mobile असल्यास वेगळी file load कर
    $this->response->setOutput($this->load->view('common/ganesh_festival', $data));
} else {
    // Desktop साठी ganesh_festival_des.twig
    $this->response->setOutput($this->load->view('common/ganesh_festival_des', $data));
}

    }
}