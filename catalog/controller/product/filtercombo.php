<?php
class ControllerProductFiltercombo extends Controller
{
    public function index($params = [])
    {
        $this->load->language('product/search');
        $this->load->model('catalog/manufacturer');
        $this->load->model('catalog/product');


        $data['action'] = $this->url->link('product/search');

        // $category_id = isset($this->request->get['category_id']) ? (int)$this->request->get['category_id'] : 0;
          $category_id = 0;
if (isset($this->request->get['path'])) {
    $parts = explode('_', $this->request->get['path']);
    $category_id = (int)array_pop($parts);
} elseif (isset($this->request->get['category_id'])) {
    $category_id = (int)$this->request->get['category_id'];
}
        $search = isset($this->request->get['search']) ? trim($this->request->get['search']) : '';
        // Check: category-based
        if ($category_id > 0) {
            $sizes = $this->model_catalog_product->getAvailableSizes($category_id);
        } elseif (!empty($search)) {
            if ($params['product_ida0']) {

                $sizes = $this->model_catalog_product->getSizesByKeyword($params['product_ida0']);
            }
        }
         $data['sizes'] = $sizes;
        $data['show_size_filter'] = (!empty($sizes) && is_array($sizes) && count($sizes) > 0);
        $data['selected_sizes'] = isset($this->request->get['size']) ? $this->request->get['size'] : [];

        
        
        // Rating filter

        // $data['selected_rating'] = isset($this->request->get['rating']) ? (int)$this->request->get['rating'] : 0;

        // $data['rating_filters'] = [
        //     ['value' => 4, 'label' => '4 ★ & above'],
        //     ['value' => 3, 'label' => '3 ★ & above'],
        //     ['value' => 2, 'label' => '2 ★ & above'],
        //     ['value' => 1, 'label' => '1 ★ & above']
        // ];
        // $data['selected_ratings'] = isset($this->request->get['rating']) ? $this->request->get['rating'] : [];
        // $data['rating_filters'] = [4, 3, 2, 1]; 




        $ratings = [];

if ($category_id > 0) {
    $ratings = $this->model_catalog_product->getAvailableRatingsByCategory($category_id);
} elseif (!empty($search)) {
    $ratings = $this->model_catalog_product->getAvailableRatingsBySearch($search);
} else {
    $ratings = $this->model_catalog_product->getAllAvailableRatings();
}

$allowed_ratings = [5,4, 3, 2, 1];
$filtered_ratings = [];

foreach ($ratings as $item) {
    $val = (int)$item['rating'];
    if (in_array($val, $allowed_ratings)) {
        $filtered_ratings[] = $val;
    }
}

$data['rating_filters'] = array_unique($filtered_ratings);
$data['selected_ratings'] = isset($this->request->get['rating']) ? $this->request->get['rating'] : [];



        //  Get all brands
        // $data['brands'] = $this->model_catalog_manufacturer->getManufacturers();
        
        function unique_multidim_array($array, $key)
        {
            $temp_array = [];
            $key_array = [];

            foreach ($array as $val) {
                if (!in_array($val[$key], $key_array)) {
                    $key_array[] = $val[$key];
                    $temp_array[] = $val;
                }
            }
            return $temp_array;
        }


        // if ($params['brands']) {

        //     $data['brands'] = unique_multidim_array($params['brands'], 'name');
        // } else {
        //     $data['brands'] = '';
        // }

             $brands = [];

if ($category_id > 0) {
    // Load all brands for this category
    $brands = $this->model_catalog_product->getAvailableBrandsByCategory($category_id);
} elseif (!empty($search)) {
    // Load all brands for search results
    $brands = $this->model_catalog_product->getAvailableBrandsBySearch($search);
} else {
    // Load all brands (fallback)
    $brands = $this->model_catalog_product->getAllAvailableBrands();
}

$data['brands'] = $brands;

















        $data['selected_manufacturers'] = isset($this->request->get['manufacturer']) ? $this->request->get['manufacturer'] : [];

        // Get selected colors
         $data['selected_colors'] = isset($this->request->get['color']) ? $this->request->get['color'] : [];

        // $data['selected_discounts'] = isset($this->request->get['discount']) ? $this->request->get['discount'] : [];
        // $data['discount_ranges'] = [20, 30, 40, 50, 60, 70]; 




                     // ✅ Dynamic Discount Filter Calculation
$discounts = [];

if ($category_id > 0) {
    $discounts = $this->model_catalog_product->getAvailableDiscountsByCategory($category_id);
} elseif (!empty($search)) {
    $discounts = $this->model_catalog_product->getAvailableDiscountsBySearch($search);
} else {
    $discounts = $this->model_catalog_product->getAllAvailableDiscounts();
}

// Filter only from allowed list
$allowed_discounts = [20, 30, 40, 50, 60, 70];
$filtered_discounts = [];

foreach ($discounts as $item) {
    $val = (int)$item['discount'];
    if (in_array($val, $allowed_discounts)) {
        $filtered_discounts[] = $val;
    }
}

$data['discount_ranges'] = array_unique($filtered_discounts);
$data['selected_discounts'] = isset($this->request->get['discount']) ? $this->request->get['discount'] : [];



                       





        // Get price
// ✅ Price range calculation
if ($category_id > 0) {
    $range = $this->model_catalog_product->getPriceRangeByCategory($category_id);
} elseif (!empty($search)) {
    $range = $this->model_catalog_product->getPriceRangeBySearch($search);
} else {
    $range = ['min' => 0, 'max' => 30000];
}

$data['min'] = isset($this->request->get['min']) ? (float)$this->request->get['min'] : (float)$range['min'];
$data['max'] = isset($this->request->get['max']) ? (float)$this->request->get['max'] : (float)$range['max'];








        // Get unique colors from `product_variants`
//        $colors = [];

// if (!empty($params['product_ids']) && is_array($params['product_ids'])) {
//     $colors = $this->model_catalog_product->getAvailableColorsByProductIds($params['product_ids']);
// }

// $data['colors'] = $colors;





$colors = [];

if ($category_id > 0) {
    // Load all colors for this category (not only filtered)
    $colors = $this->model_catalog_product->getAvailableColorsByCategory($category_id);
} elseif (!empty($search)) {
    // Load all colors that match the search keyword
    $colors = $this->model_catalog_product->getAvailableColorsBySearch($search);
} else {
    // Load all colors if no category/search is selected
    $colors = $this->model_catalog_product->getAllAvailableColors();
}

$data['colors'] = $colors;




        // Form action
        $data['route'] = $this->request->get['route'] ?? 'product/search';
        $data['path'] = $this->request->get['path'] ?? '';
        $data['search'] = $this->request->get['search'] ?? '';

        if ($data['route'] == 'product/category' && $data['path']) {
            $data['action'] = $this->url->link('product/category', 'path=' . $data['path']);
        } elseif ($data['route'] == 'product/search' && $data['search']) {
            $data['action'] = $this->url->link('product/search', 'search=' . $data['search']);
        } else {
            $data['action'] = $this->url->link('product/category');
        }

        return $this->load->view('product/filtercombo', $data);
    }
}
