<?php
class ControllerProductCategoryCard extends Controller {
  public function index($setting = []) {
    $this->load->model('catalog/category_card');

    $level = $setting['level'] ?? 0;
    $category_id = $setting['category_id'] ?? 0;
    $title = $setting['title'] ?? '';
    $subtitle = $setting['subtitle'] ?? '';
    $bg_color = $setting['bg_color'] ?? '#ffffff';

    $products = $this->model_catalog_category_card->getCategoryCardProducts($category_id,$level, 4);

    $data['main_product'] = $products['main_product'] ?? null;
    $data['related'] = $products['related'] ?? [];
    $data['title'] = $title;
    $data['subtitle'] = $subtitle;
    $data['bg_color'] = $bg_color;

    return $this->load->view('product/category_card', $data);
}

}
