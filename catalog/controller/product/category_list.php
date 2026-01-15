<?php
class ControllerProductCategoryList extends Controller {
   public function index($setting = []) {
    $this->load->model('catalog/category_list');
    $this->load->model('tool/image');

    $rows = $setting['rows'] ?? [];

    $rendered_blocks = [];

    foreach ($rows as $row) {
        $key         = $row['key'] ?? null;
        $title       = $row['title'] ?? '';
        $parent_ids  = $row['parent_ids'] ?? [];

        if (!$key) continue; // Skip rows without key

        $row_categories = [];

        foreach ($parent_ids as $parent_id) {
            $categories = $this->model_catalog_category_list->getChildCategoriesWithProduct($parent_id);

            foreach ($categories as $category) {
                $image = 'image/catalog/placeholder.png';

                if (!empty($category['product']['image'])) {
                    $image_path = $category['product']['image'];

                    if (strpos($image_path, 'catalog/') !== 0) {
                        $image_path = 'catalog/' . $image_path;
                    }

                    if (is_file(DIR_IMAGE . $image_path)) {
                        $image = $this->model_tool_image->resize($image_path, 200, 200);
                    }
                }

                $link = $this->url->link('product/category', 'path=' . $category['category_id']);
        if (isset($category['product']['price'])) {

            
            $row_categories[] = [
                'category_id'    => $category['category_id'],
                'category_name'  => $category['category_name'] ,
                'product_image'  => $image,
                'price'          => isset($category['product']['price']) 
                ? $this->currency->format($category['product']['price'], $this->config->get('config_currency')) 
                : 'N/A',
                'href'           => $link
            ];
        }
        }
        }

        $rendered_blocks[$key] = $this->load->view('product/category_list', [
            'category_groups' => [[
                'title'      => $title,
                'categories' => $row_categories
            ]]
        ]);
    }

    return $rendered_blocks;
}
}
