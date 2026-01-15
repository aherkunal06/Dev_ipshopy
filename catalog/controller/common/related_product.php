<?php
class ControllerCommonRelatedproduct extends Controller {
    public function index() {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

       
        $data['header'] = $this->load->controller('common/header');
        $this->response->setOutput($this->load->view('common/related_product', $data));
    }
      
    
public function loadRelated() {
    $this->load->model('catalog/recent_view');
    $this->load->model('tool/image');

    $recent_products = !empty($this->session->data['sorecentproduct']) 
        ? $this->session->data['sorecentproduct'] 
        : [];

    $related = $this->model_catalog_recent_view->getProductsByCategoryId($recent_products);

    $data['related'] = [];

    foreach ($related as $rel) {
        $data['related'][] = [
            'name'  => $rel['name'],
            'image' => $rel['image']
                ? $this->model_tool_image->resize($rel['image'], 200, 200)
                : $this->model_tool_image->resize('placeholder.png', 200, 200),
            'price'   => $this->currency->format($rel['price'], $this->session->data['currency']),
            'special' => !empty($rel['special']) 
                ? $this->currency->format($rel['special'], $this->session->data['currency']) 
                : false,
            'href'    => $this->url->link('product/product', 'product_id=' . $rel['product_id'])
        ];
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($data));
}
}