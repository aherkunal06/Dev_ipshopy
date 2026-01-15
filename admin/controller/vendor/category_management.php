<?php
class ControllerVendorCategoryManagement extends Controller {
    private $error = [];

    public function index() {
        $this->load->language('vendor/category_management');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('vendor/category_management');

        $this->getList();
    }



    

    public function getList() {
        // Get filter, sort, order, and page from URL
        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'category_id'; // Default sort
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC'; // Default order
        }

        // Set a very high limit to show all records
        $limit = 9999; // This will effectively show all records

        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        // Breadcrumbs
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_category_management'),
            'href' => $this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        // Action Buttons
        $data['add'] = $this->url->link('vendor/category_management/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete_all'] = $this->url->link('vendor/category_management/clearAllCategories', 'user_token=' . $this->session->data['user_token'] . $url, true);
       
        // Handle success/error messages
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        } else {
            $data['error_warning'] = '';
        }

        $data['categories'] = [];

        $filter_data = [
            'filter_name'   => $filter_name,
            'sort'          => $sort,
            'order'         => $order,
            'start'         => 0, // Start from first record
            'limit'         => $limit // Use our high limit
        ];

        $category_total = $this->model_vendor_category_management->getTotalCategories($filter_data);
        $results = $this->model_vendor_category_management->getAllCategories($filter_data);

        foreach ($results as $result) {
            $data['categories'][] = [
                'category_id'   => $result['category_id'],
                'category_name' => $result['category_name'],
                'parent_id'     => $result['parent_id'],
                'level'         => $result['level'],
                'edit'          => $this->url->link('vendor/category_management/edit', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $result['category_id'] . $url, true),
                'delete'        => $this->url->link('vendor/category_management/delete', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $result['category_id'] . $url, true)
            ];
        }

        // Language variables for the view
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['column_category_id'] = $this->language->get('column_category_id');
        $data['column_name'] = $this->language->get('column_name');
        $data['column_parent_id'] = $this->language->get('column_parent_id');
        $data['column_level'] = $this->language->get('column_level');
        $data['column_action'] = $this->language->get('column_action');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['entry_name'] = $this->language->get('entry_name');

        // Sorting
        $data['sort_category_id'] = $this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'] . '&sort=category_id' . (($order == 'ASC') ? '&order=DESC' : '&order=ASC') . $url, true);
        $data['sort_category_name'] = $this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'] . '&sort=category_name' . (($order == 'ASC') ? '&order=DESC' : '&order=ASC') . $url, true);
        $data['sort_parent_id'] = $this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'] . '&sort=parent_id' . (($order == 'ASC') ? '&order=DESC' : '&order=ASC') . $url, true);
        $data['sort_level'] = $this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'] . '&sort=level' . (($order == 'ASC') ? '&order=DESC' : '&order=ASC') . $url, true);

        // Remove pagination since we're showing all records
        $data['pagination'] = '';
        $data['results'] = sprintf($this->language->get('text_pagination'), 1, $category_total, $category_total, 1);

        $data['filter_name'] = $filter_name;
        $data['sort'] = $sort;
        $data['order'] = $order;

        // Make sure user token is set
        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('vendor/category_management_list', $data));
    }


    public function add() {
        $this->load->language('vendor/category_management');
        $this->document->setTitle($this->language->get('text_add'));
        $this->load->model('vendor/category_management');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (empty($this->request->post['category_name'])) {
                $this->error['warning'] = 'Please enter category name.';
            } else {
                // Check if category already exists
                $existing_category = $this->model_vendor_category_management->getCategoryByName($this->request->post['category_name']);
                if ($existing_category) {
                    $this->error['warning'] = 'Category already exists.';
                } else {
                    // Find the last selected category as parent
                    $parent_id = 0;
                    if (!empty($this->request->post['level4_id']) && $this->request->post['level4_id'] != '0') {
                        $parent_id = (int)$this->request->post['level4_id'];
                    } elseif (!empty($this->request->post['level3_id']) && $this->request->post['level3_id'] != '0') {
                        $parent_id = (int)$this->request->post['level3_id'];
                    } elseif (!empty($this->request->post['level2_id']) && $this->request->post['level2_id'] != '0') {
                        $parent_id = (int)$this->request->post['level2_id'];
                    } elseif (!empty($this->request->post['parent_id']) && $this->request->post['parent_id'] != '0') {
                        $parent_id = (int)$this->request->post['parent_id'];
                    }

                    // Get parent category info to calculate level
                    $parent_category = $this->model_vendor_category_management->getCategory($parent_id);
                    $level = $parent_category ? ($parent_category['level'] + 1) : 0;

                    // Get max category ID for new category
                    $max_category_id = $this->model_vendor_category_management->getMaxCategoryId();
                    $new_category_id = $max_category_id + 1;

                    // Save new category
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "all_category` SET
                        `category_id` = '" . (int)$new_category_id . "',
                        `category_name` = '" . $this->db->escape($this->request->post['category_name']) . "',
                        `parent_id` = '" . (int)$parent_id . "',
                        `level` = '" . (int)$level . "'
                    ");

                    $this->session->data['success'] = 'Category added successfully.';
                    $this->response->redirect($this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'], true));
                }
            }
        }

        $this->getForm();
    }
    
    

    public function edit() {
        $this->load->language('vendor/category_management');
        $this->document->setTitle($this->language->get('text_edit'));
        $this->load->model('vendor/category_management');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $category_id = (int)$this->request->get['category_id'];
            $category_info = $this->model_vendor_category_management->getCategory($category_id);
            
            if ($category_info) {
                // Only update the category name, preserve other data
                $this->model_vendor_category_management->editCategory($category_id, [
                    'category_name' => $this->request->post['category_name'],
                    'parent_id' => $category_info['parent_id'], // Preserve original parent_id
                    'level' => $category_info['level'] // Preserve original level
                ]);

                $this->session->data['success'] = $this->language->get('text_success_edit');
            }

            $this->response->redirect($this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getForm();
    }


    public function checkCategoryChildren() {
        $json = array();

        if (!isset($this->session->data['user_token'])) {
            $json['error'] = 'Invalid session. Please refresh the page.';
        } else if (isset($this->request->get['category_id'])) {
            $this->load->model('vendor/category_management');
            $category_id = (int)$this->request->get['category_id'];

            $json['has_children'] = $this->model_vendor_category_management->hasChildCategories($category_id);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


    // public function checkCategoryChildren() {
    //     $this->load->model('vendor/category_management');
    
    //     $json = [];
    
    //     if (isset($this->request->get['category_id'])) {
    //         $category_id = (int)$this->request->get['category_id'];
    //         $hasChildren = $this->model_vendor_category_management->hasChildren($category_id);
    //         $json['has_children'] = $hasChildren;
    //     } else {
    //         $json['has_children'] = false;
    //     }
    
    //     $this->response->addHeader('Content-Type: application/json');
    //     $this->response->setOutput(json_encode($json));
    // }

    // public function delete() {
    //     $this->load->language('vendor/category_management');
    //     $this->load->model('vendor/category_management');

    //     if (isset($this->request->get['category_id'])) {
    //         // Check if this category ID is used as parent_id in any other category
    //         $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "all_category WHERE parent_id = '" . (int)$this->request->get['category_id'] . "'");
    //         if ($query->row['total'] > 0) {
    //             $this->session->data['error_warning'] = 'Cannot delete this category. First delete all its subcategories.';
    //         } else {
    //             $this->db->query("DELETE FROM " . DB_PREFIX . "all_category WHERE category_id = '" . (int)$this->request->get['category_id'] . "'");
    //             $this->session->data['success'] = 'Category deleted successfully!';
    //         }
    //     }
    //     $this->response->redirect($this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'], true));
    // }


    public function delete() {
        $this->load->language('vendor/category_management');
        $this->load->model('vendor/category_management');
        
        $json = array();

        if (!isset($this->session->data['user_token'])) {
            $json['error'] = 'Invalid session. Please refresh the page.';
        } else if (isset($this->request->get['category_id'])) {
            $category_id = (int)$this->request->get['category_id'];

            // Check if category exists
            $category_info = $this->model_vendor_category_management->getCategory($category_id);
            
            if (!$category_info) {
                $json['error'] = 'Category not found!';
            } else {
                // Check for child categories
                if ($this->model_vendor_category_management->hasChildCategories($category_id)) {
                    $json['error'] = 'Cannot delete this category. First delete all its subcategories.';
                } else {
                    // Proceed with deletion
                    if ($this->model_vendor_category_management->deleteCategory($category_id)) {
                        $json['success'] = true;
                    } else {
                        $json['error'] = 'Error deleting category.';
                    }
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function clearAllCategories() {
        $this->load->language('vendor/category_management');
        $this->load->model('vendor/category_management');

        if ($this->user->hasPermission('modify', 'vendor/category_management')) {
            if ($this->model_vendor_category_management->deleteAllCategoriesData()) {
                $this->session->data['success'] = $this->language->get('text_all_categories_deleted');
            } else {
                $this->session->data['error_warning'] = $this->language->get('error_delete_all_categories');
            }
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
        }

        $this->response->redirect($this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'], true));
    }

    protected function getForm() {
        $this->load->model('vendor/category_management');
        $this->load->language('vendor/category_management');
        
        $category_id = isset($this->request->get['category_id']) ? (int)$this->request->get['category_id'] : 0;
        
        if ($category_id) {
            // Load edit form
            $category_info = $this->model_vendor_category_management->getCategory($category_id);
            if ($category_info) {
                $data['category_name'] = $category_info['category_name'];
                $data['parent_id'] = $category_info['parent_id'];
                $data['level'] = $category_info['level'];
                
                // Get the full category path (ancestors)
                $path = $this->model_vendor_category_management->getCategoryPath($category_id);
                $data['category_path'] = [];
                
                // Get details for each category in the path
                foreach ($path as $path_id) {
                    $path_category = $this->model_vendor_category_management->getCategory($path_id);
                    if ($path_category) {
                        $data['category_path'][] = [
                            'category_id' => $path_category['category_id'],
                            'category_name' => $path_category['category_name'],
                            'level' => $path_category['level']
                        ];
                    }
                }
            }
            
            $data['text_form'] = $this->language->get('text_edit');
            $data['action'] = $this->url->link('vendor/category_management/edit', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $category_id, true);
            
            // Load edit form template
            $template = 'vendor/category_management_edit_form';
        } else {
            // Load add form
            $data['category_name'] = '';
            $data['parent_id'] = 0;
            $data['level'] = 0;
            $data['category_path'] = [];
            
            // Get all parent categories (level 0)
            $data['parent_categories'] = $this->model_vendor_category_management->getCategoriesByLevel(0);
            
            $data['text_form'] = $this->language->get('text_add');
            $data['action'] = $this->url->link('vendor/category_management/add', 'user_token=' . $this->session->data['user_token'], true);
            
            // Load add form template
            $template = 'vendor/category_management_form';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['category_name'])) {
            $data['error_category_name'] = $this->error['category_name'];
        } else {
            $data['error_category_name'] = '';
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_parent'] = $this->language->get('entry_parent');
        $data['entry_level'] = $this->language->get('entry_level');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['text_select'] = $this->language->get('text_select');
        
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_category_management'),
            'href' => $this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['cancel'] = $this->url->link('vendor/category_management', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($template, $data));
    }

    public function getCategoryChildrenAjax() {
        $this->load->model('vendor/category_management');
        $json = [];

        if (isset($this->request->get['parent_id'])) {
            $parent_id = (int)$this->request->get['parent_id'];
            $children = $this->model_vendor_category_management->getCategoryChildren($parent_id);

            foreach ($children as $child) {
                $json[] = [
                    'category_id'   => $child['category_id'],
                    'category_name' => $child['category_name']
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }




      //bugs remove method
      public function ajaxLoadCategories() {
        $this->load->model('vendor/category_management');
        $categories = $this->model_vendor_category_management->getAllCategories(); // Or getCategories() if it loads all
    
        // Format for frontend
        $formatted_categories = [];
        foreach ($categories as $category) {
            $formatted_categories[] = [
                'category_id'   => (int)$category['category_id'],
                'category_name' => $category['category_name'],
                'parent_id'     => (int)$category['parent_id'],
                'level'         => (int)$category['level'] // Include level if you need it in JS for more complex logic
            ];
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($formatted_categories));
    }
    

        public function checkCategoryByName() {
            $this->load->language('vendor/category_management'); // Load any language files you might need
            $this->load->model('vendor/category_management');
    
            $json = ['exists' => false];
    
            if (isset($this->request->get['category_name'])) {
                $category_name = trim($this->request->get['category_name']);
    
                // Get category by name from your all_category table
                $category_info = $this->model_vendor_category_management->getCategoryByName($category_name);
    
                if ($category_info) {
                    $json['exists'] = true;
                    $json['category_id'] = $category_info['category_id'];
    
                    // Get the full path of category IDs for the found category
                    // Assuming getCategoryPath returns an array of IDs from root to current
                    $json['path_ids'] = $this->model_vendor_category_management->getCategoryPath($category_info['category_id']);
                }
            }
    
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    



  

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'vendor/category_management')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['category_name']) < 2) || (utf8_strlen($this->request->post['category_name']) > 255)) {
            $this->error['category_name'] = 'Category name must be between 2 and 255 characters!';
        }

        return !$this->error;
    }


    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'vendor/category_management')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

   

    
   
  
    
    public function getParentCategories() {
        $this->load->model('vendor/category_management');
        $json = array();

        if (isset($this->request->get['user_token']) && $this->request->get['user_token'] == $this->session->data['user_token']) {
            $categories = $this->model_vendor_category_management->getCategoriesByLevel(0);
            foreach ($categories as $category) {
                $json[] = array(
                    'category_id' => $category['category_id'],
                    'category_name' => $category['category_name']
                );
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getSubCategories() {
        $this->load->model('vendor/category_management');
        $json = array();

        if (isset($this->request->get['user_token']) && $this->request->get['user_token'] == $this->session->data['user_token']) {
            if (isset($this->request->get['parent_id'])) {
                $categories = $this->model_vendor_category_management->getCategoriesByParentId((int)$this->request->get['parent_id']);
                foreach ($categories as $category) {
                    $json[] = array(
                        'category_id' => $category['category_id'],
                        'category_name' => $category['category_name'],
                        'level' => $category['level']
                    );
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
?>