<?php
class ControllerProductIndependence extends Controller {
    public function index() {
        $this->load->language('product/independence');
        $this->load->model('catalog/independence');
        $this->load->model('tool/image');

        // URL मधून category_id घेणे
        $category_id = isset($this->request->get['category_id']) ? (int)$this->request->get['category_id'] : 0;

        $data['products'] = [];

        if ($category_id) {
            $results = $this->model_catalog_independence->getDiscountedProductsByCategory($category_id, 40);

            foreach ($results as $result) {
                $image = $result['image'] ? $this->model_tool_image->resize($result['image'], 200, 200) : false;

                $special = false;
                if ((float)$result['special']) {
                    $special = $this->currency->format($result['special'], $this->session->data['currency']);
                }

                $price = $this->currency->format($result['price'], $this->session->data['currency']);

                $data['products'][] = [
                    'product_id' => $result['product_id'],
                    'thumb'      => $image,
                    'name'       => $result['name'],
                    'price'      => $price,
                    'special'    => $special,
                    'href'       => $this->url->link('product/product', 'product_id=' . $result['product_id'])
                ];
            }
        }

        $this->response->setOutput($this->load->view('product/independence', $data));
    }
}
