<?php
class ControllerCommonBrandProduct extends Controller {
    public function index() {
        $this->load->model('catalog/brand_product');
        $this->load->model('tool/image');

        $brand_ids = [35, 10]; // ✅ Add your brand IDs here
        $data['brands'] = [];

        foreach ($brand_ids as $brand_id) {
            $brand_name = $this->model_catalog_brand_product->getBrandName($brand_id);
            $products_raw = $this->model_catalog_brand_product->getProductsByBrand($brand_id, 6);

            $products = [];
            foreach ($products_raw as $product) {
                $products[] = [
                    'product_id' => $product['product_id'],
                    'name'       => utf8_substr($product['name'], 0, 30) . '...',
                    'image'      => $this->model_tool_image->resize($product['image'], 200, 200),
                    'price'      => $this->currency->format($product['price'], $this->session->data['currency']),
                    'special'    => $product['special'] ? $this->currency->format($product['special'], $this->session->data['currency']) : false,
                    'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
                ];
            }

            $data['brands'][] = [
                'brand_name' => $brand_name,
                'view_all' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $brand_id),

                'products'   => $products
            ];
        }

        // return $this->load->view('common/brand_product', $data);
$this->load->view('common/brand_product', $data);

    }
}
