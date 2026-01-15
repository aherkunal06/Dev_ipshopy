<?php
class ControllerProductTodaysDeals extends Controller {
    public function index() {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $filter_data = [
            'sort'  => 'p.viewed',
            'order' => 'DESC',
            'start' => 0,
            'limit' => 5
        ];

        $results = $this->model_catalog_product->getProducts($filter_data);

        $data['products'] = [];

      foreach ($results as $result) {
            $data['products'][] = [
                'name' => $result['name'],
                'thumb' => $this->model_tool_image->resize($result['image'], 200, 200),
                  'price' => $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
                  'special' => $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
                  'product_link' =>$this->url->link('product/product', 'product_id=' . $result['product_id']) 
        ];
}

        return $this->load->view('extension/module/todays_deals', $data);
    }
}
