<?php
class ControllerProductMobileCategoryProduct extends Controller {
  public function index($args = []) {
        $this->load->language('product/category_product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $category_ids = [];

        if (isset($args['category_ids']) && is_array($args['category_ids'])) {
            $category_ids = $args['category_ids'];
        } elseif (isset($args['category_id'])) {
            $category_ids = [(int)$args['category_id']];
        }

foreach ($category_ids as $category_id) {
    $category_info = $this->model_catalog_category->getCategory($category_id);
    if ($category_info) {
        $categories[] = $category_info;
    }
}
// var_dump($args);
if ( $args['title']) {
    $data['category_name'] = $args['title'];
    $data['category'] = [
        'view_more' => $this->url->link('product/category', 'path=' . $category_info['category_id'])
    ];
}else if ($category_info) {
    $data['category_name'] = $category_info['name'];
    $data['category'] = [
        'view_more' => $this->url->link('product/category', 'path=' . $category_info['category_id'])
    ];
}

// Prepare category name string
if (count($categories) === 1) {
    $data['category_name'] = $categories[0]['name'];
} elseif (count($categories) > 1) {
    // Join multiple category names by comma
    $names = array_column($categories, 'name'); // Extract all names
    $data['category_name'] = implode(', ', $names);
} else {
    $data['category_name'] = 'No Category';  // fallback text if none found
}

        $category_products = []; // Used for interleaving

        foreach ($category_ids as $category_id) {
            // $category = $this->model_catalog_category->getCategory($category_id);
            // if (!$category) continue;

            $products = $this->model_catalog_product->getProducts([
                'filter_category_id' => $category_id,
                'start' => 0,
                'limit' => 10
            ]);

            $product_data = [];

            foreach ($products as $product) {
                $product_info = [
                    'product_id' => $product['product_id'],
                    'thumb'      => $this->model_tool_image->resize($product['image'], 240, 160),
                    'name'       => $product['name'],
                    'price'      => $this->currency->format(
                        $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')),
                        $this->session->data['currency']
                    ),
                    'special'    => (float)$product['special'] ? $this->currency->format($product['special'], $this->session->data['currency']) : false,
                    'discount'   => (float)$product['special'] ? round(100 - ($product['special'] / $product['price'] * 100)) . '%' : '',
                    'rating'     => isset($product['rating']) ? $product['rating'] : false,
                    'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                    'href2' => $this->url->link('extension/soconfig/quickview', 'product_id=' . $product['product_id']),

                ];

                $product_data[] = $product_info;
            }

            // Save for rendering individual category rows
            // $categories[] = [
            //     'category_id' => $category['category_id'],
            //     'name'        => $category['name'],
            //     'view_more'   => $this->url->link('product/category', 'path=' . $category['category_id']),
            //     'products'    => $product_data
            // ];

            // Save for interleaving
            $category_products[] = $product_data;
        }

        // Interleave products: one per category per round
        $interleaved = [];
        $index = 0;

        while (true) {
            $added = false;
            foreach ($category_products as $product_list) {
                if (isset($product_list[$index])) {
                    $interleaved[] = $product_list[$index];
                    $added = true;
                }
            }
            if (!$added) break;
            $index++;
        }

        $data['categories'] = $categories;    // For normal category-wise rendering
        $data['products'] = $interleaved;     // For interleaved single row display

        return $this->load->view('product/mobile_category_product', $data);
    }
}
