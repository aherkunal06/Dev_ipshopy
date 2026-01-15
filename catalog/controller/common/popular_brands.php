<?php
class ControllerCommonPopularBrands extends Controller {
    public function index() {
    $this->load->model('catalog/manufacturer');

    $brands_data = $this->model_catalog_manufacturer->getManufacturersWithImageLimit($limit = 20);
    $data['brands'] = [];

    foreach ($brands_data as $brand) {
        if ($brand['image']) {
            $data['brands'][] = [
                'name'  => $brand['name'],
                'image' => $this->model_tool_image->resize($brand['image'], 100, 60)
            ];
        }
    }

    return $this->load->view('common/popular_brands', $data);
}

}
