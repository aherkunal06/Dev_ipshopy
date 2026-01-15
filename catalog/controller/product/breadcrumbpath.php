<?php
class ControllerProductBreadcrumbpath extends Controller {
    public function index() {
        $this->load->language('common/header');
        $home_text = $this->language->get('text_home') ?: 'Home';

        $this->load->model('catalog/breadcrumbpath');

        // category_id ओळखणे
        if (isset($this->request->get['category_id'])) {
            $category_id = (int)$this->request->get['category_id'];
        } elseif (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            $category_id = (int)end($parts);
        } else {
            $category_id = 0;
        }

        $data['breadcrumb_path'] = [];

        if ($category_id > 0) {
            $row = $this->model_catalog_breadcrumbpath->getBreadcrumbHierarchy($category_id);

            if (!empty($row)) {
                // Reverse order: top to bottom
                $ordered = [];
                for ($i = 5; $i >= 1; $i--) {
                    $id_key = "level{$i}_id";
                    $name_key = "level{$i}_name";
                    if (!empty($row[$id_key])) {
                        $name = !empty($row[$name_key]) ? $row[$name_key] : ('Category ' . $row[$id_key]);
                        $ordered[] = [
                            'id'   => (int)$row[$id_key],
                            'name' => $name
                        ];
                    }
                }

                // Add Home first
                $breadcrumb = [];
                $breadcrumb[] = [
                    'name' => $home_text,
                    'href' => $this->url->link('common/home')
                ];

                // Add category hierarchy with href
                $path_ids = [];
                foreach ($ordered as $item) {
                    $path_ids[] = $item['id'];
                    $breadcrumb[] = [
                        'name' => $item['name'],
                        'href' => $this->url->link('product/category', 'path=' . implode('_', $path_ids))
                    ];
                }

                $data['breadcrumb_path'] = $breadcrumb;
            }
        }
        //  var_dump($category_id); // Debugging line to check if the breadcrumb data is correct
        return $this->load->view('product/custombreadcrumb', $data);
    }
}
