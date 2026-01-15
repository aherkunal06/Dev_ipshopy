<?php
class ControllerProductTodaysDeals extends Controller {
    public function index() {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $filter_data = [
            'sort'  => 'p.date_added',
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
            ];
        }

        return $this->load->view('product/todays_deals', $data);
    }
}
