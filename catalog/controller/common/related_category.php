<?php
class ControllerCommonRelatedcategory extends Controller {
    public function index() {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

       
        $data['header'] = $this->load->controller('common/header');
        $this->response->setOutput($this->load->view('common/related_category', $data));
    }



    public function loadCategoryId() {
    $this->load->model('catalog/recent_view');
    $this->load->model('tool/image');

    $recent_products = !empty($this->session->data['sorecentproduct']) 
        ? $this->session->data['sorecentproduct'] 
        : [];
       $recent_products = array_slice($recent_products, 0, 10);
    $categories = $this->model_catalog_recent_view->getCategoryInfoByRecent($recent_products);
 $categories = array_slice($categories, 0, 12);
    $data['categories'] = [];

    foreach ($categories as $cat) {
        $data['categories'][] = [
           
            'name'          => $cat['name'],
            'category_name' => $cat['category_name'] ?? $cat['name'],
            'lowest_price'  => $this->currency->format($cat['lowest_price'], $this->session->data['currency']),
            'image'         => $cat['category_image'] 
                                ? $this->model_tool_image->resize($cat['category_image'], 200, 200) 
                                : $this->model_tool_image->resize('placeholder.png', 200, 200)
        ];
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($data));
}
}