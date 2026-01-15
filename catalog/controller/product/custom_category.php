<?php
class ControllerProductCustomCategory extends Controller {
    public function index() {
        $this->load->model('catalog/custom_category');
        $this->load->model('tool/image');

        $data['categories'] = [];
        $category_ids = [2123, 2131, 2104, 2112, 1887, 3184, 1999, 2000, 2117, 2102, 3058, 2167];

        foreach ($this->model_catalog_custom_category->getCategoriesByIds($category_ids) as $category) {
            $data['categories'][] = [
                'category_id' => $category['category_id'],
                'name'        => $category['name'],
                'thumb'       => $category['image'] ? $this->model_tool_image->resize($category['image'], 50, 50) : 'placeholder.png',
                'href'        => $this->url->link('product/custom_category', 'category_id=' . $category['category_id'])
            ];
        }

        // Default selected category
        $category_id = isset($this->request->get['category_id']) ? (int)$this->request->get['category_id'] : $category_ids[0];

        // Subcategories only
        $data['subcategories'] = [];
        foreach ($this->model_catalog_custom_category->getSubcategories($category_id) as $subcategory) {
            $data['subcategories'][] = [
                'name'  => $subcategory['name'],
                'thumb' => $subcategory['image'] ? $this->model_tool_image->resize($subcategory['image'], 150, 150) : 'placeholder.png',
                'href'  => $this->url->link('product/custom_category', 'category_id=' . $subcategory['category_id'])
            ];
        }

        $this->response->setOutput($this->load->view('product/custom_category', $data));
    }
}
?>
