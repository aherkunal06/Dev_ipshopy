<?php
class ControllerCommonGaneshFestival extends Controller {
    public function index() {
        $this->load->model('catalog/ganesh_festival');
        $this->load->model('tool/image');

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        // Top Banner
        $data['top_banner'] = 'image/catalog/mobile/banner/home2/Rectangle 3.jpg';

        // Categories
        $category_ids = [2123, 2002, 2131, 2104, 2112,2166, 2123, 2142];
        $categories_raw = $this->model_catalog_ganesh_festival->getFestivalCategories($category_ids);
        $data['categories'] = [];

        foreach ($categories_raw as $category_info) {
            if ($category_info) {
                $data['categories'][] = [
                    'name'  => $category_info['name'],
                    'thumb' => $this->model_tool_image->resize($category_info['image'], 200, 200),
                    'href'  => $this->url->link('product/category', 'path=' . $category_info['category_id'])
                ];
            }
        }

        // Second Banner
        $data['second_banner'] = 'image/catalog/mobile/slider/home2/banner-mobile-1.jpg';

        // Women's Dresses Subcategories 
        $women_category_id = 2122;
        $subcategories_raw = $this->model_catalog_ganesh_festival->getSubcategories($women_category_id);
        $subcategories_raw = array_slice($subcategories_raw, 0, 3);
    // Limit to 6 subcategories for display

       
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
        $data['third_banner'] = 'image/catalog\mobile\slider\home2\banner.png';
    

        // New Categories Section (Women's, Men's, Kids')
// New Categories Section (Women's, Men's, Kids')
$data['festival_cards'] = [
    [
        'name'  => "Women's Wear",
        'thumb' => 'image/catalog/festival/women.png', // Banner सारखा direct path
        'href'  => $this->url->link('product/category', 'path=2103')
    ],
    [
        'name'  => "Men's Wear",
        'thumb' => 'image/catalog/festival/men.png',
        'href'  => $this->url->link('product/category', 'path=2122')
    ],
    [
        'name'  => "Kids' Wear",
        'thumb' => 'image/catalog/festival/kids.png',
        'href'  => $this->url->link('product/category', 'path=2141')
    ],
];


$men_category_id =2103;
       $subcategories = $this->model_catalog_ganesh_festival->getSubcategories($men_category_id);
        
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
                // 'href' =>$this->url->link('product/category','path='.$subcategory['catgeory_id']),
                'href' =>$this->url->link('product/category','path='.$subcategory['category_id']),
                'special_price' => $special_price,
        'original_price' => $original_price        
            ];
        }


// Extra Categories Section (ID wise cards)
$extra_category_ids = [2000, 2002, 3519]; 
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


 $kids = 2141;
        $subcategories = $this->model_catalog_ganesh_festival->getSubcategories( $kids);

         $data['kids']=[];

         foreach($subcategories as $subcategory){
             $special_price = $subcategory['special_price'] 
        ? $this->currency->format($subcategory['special_price'], $this->session->data['currency']) 
        : false;

    $original_price = $subcategory['original_price'] 
        ? $this->currency->format($subcategory['original_price'], $this->session->data['currency']) 
        : false;
            $data['kids'][]=[
                'name' => $subcategory['name'],
                'thumb'=> $subcategory['image']
                          ? $this->model_tool_image->resize($subcategory['image'],200,200)
                          : $this->model_tool_image->resize('placeholder.png',200,200),
                'href' =>$this->url->link('product/category','path='.$subcategory['catgeory_id']),
                'special_price' => $special_price,
        'original_price' => $original_price         
            ];
         }

// Fourth Banner
        $data['fourth_banner'] = 'image\catalog\page-fashion\letter-bg-3.png';


        $elc_catgeory_id=1887;
        $subcategoriess  = $this->model_catalog_ganesh_festival->getSubcategories($elc_catgeory_id);
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
                // 'href' =>$this->url->link('product/category','path='.$subcategory['catgeory_id']),
                'href' =>$this->url->link('product/category','path='.$subcategory['category_id']),

                'special_price' => $special_price,
        'original_price' => $original_price    
            ];
            
        }



        $this->response->setOutput($this->load->view('common/ganesh_festival', $data));
    }
}
