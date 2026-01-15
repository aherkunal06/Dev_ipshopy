<?php
class ControllerCommonNavigation extends Controller {
    public function index() {
       
        // $data['header'] = $this->load->controller('common/header');
               
                $this->response->setOutput($this->load->view('common/navigation', $data));
    }
    public function categoriesJson() {
    $this->load->model('catalog/navigation');

    $main_category_ids = [2122, 2103, 2141, 2166, 1887, 1999, 3058, 2254,2484];
    $categories = [];

    foreach ($main_category_ids as $category_id) {
        $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description 
            WHERE category_id = '" . (int)$category_id . "' 
            AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

        $categories[] = [
            'category_id' => $category_id,
            'name'        => $query->row['name'],
            'href'        => $this->url->link('product/category', 'path=' . $category_id),
            'children'    => $this->model_catalog_navigation->getCategories($category_id)
        ];
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($categories));
}

}
