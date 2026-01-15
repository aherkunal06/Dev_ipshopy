<?php
class ControllerCommonRecentView extends Controller {
    public function index() {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

       
        $data['header'] = $this->load->controller('common/header');
        $this->response->setOutput($this->load->view('common/recent_view', $data));
    }

public function loadView() {
    $this->load->model('catalog/recent_view'); 
    $this->load->model('tool/image');

    $recent_products = !empty($this->session->data['sorecentproduct']) 
        ? $this->session->data['sorecentproduct'] 
        : [];

    $recent_products = array_unique($recent_products);

    // Current product id passed via GET param 'exclude_product_id'
    $current_product_id = isset($this->request->get['exclude_product_id']) ? (int)$this->request->get['exclude_product_id'] : null;

    // Remove current product id from recent products list
    if ($current_product_id !== null) {
        $recent_products = array_filter($recent_products, function($pid) use ($current_product_id) {
            return $pid != $current_product_id;
        });
    }

    $recent_products = array_slice($recent_products, 0, 10);

    $results = $this->model_catalog_recent_view->getRecentProductsByCategory($recent_products, 10);

    $data['products'] = [];

    foreach ($results as $product_info) {
        $data['products'][] = [
            'name'  => $product_info['name'],
            'image' => $product_info['image'] 
                ? $this->model_tool_image->resize($product_info['image'], 200, 200) 
                : 'image/no_image.png',
            'price'   => $this->currency->format($product_info['price'], $this->session->data['currency']),
            'special' => !empty($product_info['special']) 
                ? $this->currency->format($product_info['special'], $this->session->data['currency']) 
                : false,
            'rating' => isset($product_info['rating']) ? round($product_info['rating']) : 0,
            'href'    => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])          
        ];
    }

    $data['customer_name'] = $this->customer->isLogged() 
        ? $this->customer->getFirstName() 
        : 'Guest';

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($data));
}

// public function loadRelated() {
//     $this->load->model('catalog/recent_view');
//     $this->load->model('tool/image');

//     $recent_products = !empty($this->session->data['sorecentproduct']) 
//         ? $this->session->data['sorecentproduct'] 
//         : [];

//     $related = $this->model_catalog_recent_view->getProductsByCategoryId($recent_products);

//     $data['related'] = [];

//     foreach ($related as $rel) {
//         $data['related'][] = [
//             'name'  => $rel['name'],
//             'image' => $rel['image']
//                 ? $this->model_tool_image->resize($rel['image'], 200, 200)
//                 : $this->model_tool_image->resize('placeholder.png', 200, 200),
//             'price'   => $this->currency->format($rel['price'], $this->session->data['currency']),
//             'special' => !empty($rel['special']) 
//                 ? $this->currency->format($rel['special'], $this->session->data['currency']) 
//                 : false,
//             'href'    => $this->url->link('product/product', 'product_id=' . $rel['product_id'])
//         ];
//     }

//     $this->response->addHeader('Content-Type: application/json');
//     $this->response->setOutput(json_encode($data));
// }

// public function loadCategoryId() {
//     $this->load->model('catalog/recent_view');
//     $this->load->model('tool/image');

//     $recent_products = !empty($this->session->data['sorecentproduct']) 
//         ? $this->session->data['sorecentproduct'] 
//         : [];
//        $recent_products = array_slice($recent_products, 0, 10);
//     $categories = $this->model_catalog_recent_view->getCategoryInfoByRecent($recent_products);
//  $categories = array_slice($categories, 0, 12);
//     $data['categories'] = [];

//     foreach ($categories as $cat) {
//         $data['categories'][] = [
           
//             'name'          => $cat['name'],
//             'category_name' => $cat['category_name'] ?? $cat['name'],
//             'lowest_price'  => $this->currency->format($cat['lowest_price'], $this->session->data['currency']),
//             'image'         => $cat['category_image'] 
//                                 ? $this->model_tool_image->resize($cat['category_image'], 200, 200) 
//                                 : $this->model_tool_image->resize('placeholder.png', 200, 200)
//         ];
//     }

//     $this->response->addHeader('Content-Type: application/json');
//     $this->response->setOutput(json_encode($data));
// }


}
