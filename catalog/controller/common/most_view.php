<?php
class ControllerCommonMostView extends Controller {
    public function index() {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        // header add केला
        // $data['header'] = $this->load->controller('common/header');

        // $this->response->setOutput($this->load->view('common/most_view', $data));
		return $this->load->view('common/most_view', $data);
        
    }

    public function mostView() {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $limit = 5; 
        $results = $this->model_catalog_product->getPopularProducts($limit);

        $data['products'] = [];

        foreach ($results as $result) {
    if ($result) {
   
        $discount = 0;
        if (!empty($result['special']) && $result['special'] < $result['price']) {
            $discount = round((($result['price'] - $result['special']) / $result['price']) * 100);
        }

        $data['products'][] = [
            'product_id' => $result['product_id'],
            'image'      => $result['image'] 
                ? $this->model_tool_image->resize($result['image'], 200, 200) 
                : $this->model_tool_image->resize('placeholder.png', 200, 200),
            'name'       => $result['name'],
            'price'      => $this->currency->format($result['price'], $this->session->data['currency']),
            'special'    => $result['special'] 
                ? $this->currency->format($result['special'], $this->session->data['currency']) 
                : false,
            'discount'   => $discount, 
            'href'       => $this->url->link('product/product', 'product_id=' . $result['product_id'])
        ];
    }
}


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
