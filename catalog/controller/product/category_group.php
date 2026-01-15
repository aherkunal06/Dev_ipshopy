<?php
class ControllerProductCategoryGroup extends Controller {
    public function index($setting = []) {
        $this->load->model('catalog/category_group');
        $this->load->model('tool/image');

        $rendered_groups = [];

        // Multiple groups supported
        $groups = $setting['groups'] ?? [];

        foreach ($groups as $group) {
            $group_key       = $group['key'] ?? '';
            $group_title     = $group['title'] ?? '';
            $view_all_href   = $group['view_all_href'] ?? '';
            $rows            = $group['rows'] ?? [];

            $category_groups = [];
            $max_categories_per_row = 4; // Always 4 subcategories per parent

            foreach ($rows as $row) {
                $row_key     = $row['key'] ?? '';
                $parent_ids  = isset($row['parent_ids']) ? (array)$row['parent_ids'] : [];

                $child_categories = [];

                foreach ($parent_ids as $parent_id) {
                    $categories = $this->model_catalog_category_group->getChildCategoriesWithProduct($parent_id);

                    // Slice to max 4
                    $categories = array_slice($categories, 0, $max_categories_per_row);

                    // Fill placeholders if less than 4
                    while (count($categories) < $max_categories_per_row) {
                        $categories[] = [
                            'category_id' => 0,
                            'level'       => 0,
                            'name'        => 'Coming Soon',
                            'image'       => 'no_image.png',
                            'parent_id'   => $parent_id,
                            'product'     => [
                                'product_id' => null,
                                'price'      => null,
                                'image'      => null
                            ]
                        ];
                    }

                    $child_categories = array_merge($child_categories, $categories);
                }

                $rendered_categories = [];

                foreach ($child_categories as $category) {
                     var_dump($category['category_id']);
                    // Default image
                    $image = $this->model_tool_image->resize('no_image.png', 139, 139);

                    // Product image → Category image → Fallback
                    if (!empty($category['product']['image']) && is_file(DIR_IMAGE . $category['product']['image'])) {
                        $image = $this->model_tool_image->resize($category['product']['image'], 139, 139);
                    } elseif (!empty($category['image']) && is_file(DIR_IMAGE . $category['image'])) {
                        $image = $this->model_tool_image->resize($category['image'], 139, 139);
                    }

                    $rendered_categories[] = [
                        'category_id'       => $category['category_id'],
                        'category_name'     => $category['name'],
                        'product_image'     => $image,
                        'price'             => isset($category['product']['price']) && (float)$category['product']['price'] > 0
                            ? $this->currency->format($category['product']['price'], $this->config->get('config_currency'))
                            : 'N/A',
                        'href'              => $category['category_id'] 
                            ? $this->url->link('product/category', 'path=' . $category['category_id'] . '&level=' . $category['level'])
                            : 'javascript:void(0);',
                        'best_selling_tag'  => true
                    ];
                }

                $category_groups[] = [
                    'categories' => $rendered_categories
                ];
            }

            $rendered_groups[$group_key] = [
                'group_combined' => $this->load->view('product/category_group', [
                    'group_title'     => $group_title,
                    'view_all_href'   => $view_all_href,
                    'category_groups' => $category_groups
                ])
            ];
        }

        return ['groups' => $rendered_groups];
    }
}
