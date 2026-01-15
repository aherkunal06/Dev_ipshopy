<?php
class ControllerAssignAssignWork extends Controller {
    public function index() {
        $this->load->language('assign/assign_work');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('assign/assign_work');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['user_token'] = $this->session->data['user_token'];

        $filter_username = $this->request->get['filter_username'] ?? '';

        $filter_data = [];
        if (!empty($filter_username)) {
            $filter_data['filter_username'] = $filter_username;
        }

        $users = $this->model_assign_assign_work->getUsersWithSellerCount($filter_data);

        foreach ($users as &$user) {
            $user['view_url'] = $this->url->link(
                'assign/assign_work/viewDetails',
                'user_token=' . $data['user_token'] . '&user_id=' . $user['user_id'],
                true
            );
        }

        $data['users'] = $users;
        $data['filter_username'] = $filter_username;
        $data['add_url'] = $this->url->link('assign/assign_work/addForm', 'user_token=' . $data['user_token'], true);
        $data['action'] = $this->url->link('assign/assign_work', 'user_token=' . $data['user_token'], true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        // added the code on 13-07-2025 --------------------------
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        //--------------------------------------------
        $this->response->setOutput($this->load->view('assign/assign_work', $data));
    }

    public function autocomplete() {
        $json = [];

        if (isset($this->request->get['filter_username'])) {
            $this->load->model('user/user');

            $filter_data = [
                'filter_username' => $this->request->get['filter_username'],
                'start' => 0,
                'limit' => 5
            ];

            $results = $this->model_user_user->getUsers($filter_data);

            foreach ($results as $result) {
                $json[] = [
                    'user_id' => $result['user_id'],
                    'username' => html_entity_decode($result['username'], ENT_QUOTES, 'UTF-8')
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

   public function viewDetails() {
    $this->load->language('assign/assign_work');
    $this->document->setTitle($this->language->get('heading_title'));
    $this->load->model('assign/assign_work');

    $user_id = (int)($this->request->get['user_id'] ?? 0);
    $filter_username = $this->request->get['filter_username'] ?? '';
    $filter_assign_date = $this->request->get['filter_assign_date'] ?? '';
    $specific_date = $this->request->get['assign_date'] ?? '';
    
 // New parameter for specific date click

    $filter_data = [
        'filter_username' => $filter_username,
        'filter_assign_date' => $filter_assign_date
    ];

    // Get different data based on whether we're viewing a specific date or filtered results
    if ($specific_date) {
        // Get sellers for specific date
        $data['sellers'] = $this->model_assign_assign_work->getSellersByDate($user_id, $specific_date);
        $template = 'assign/seller_details'; // Use seller details template
    } else {
        // Get assignment counts (your existing functionality)
        $data['assignments'] = $this->model_assign_assign_work->getSellerCountByDate($user_id, $filter_data);
        $template = 'assign/view_details'; // Use your original template
    }

    $data['user_id'] = $user_id;
    $data['user_token'] = $this->session->data['user_token'];
    $data['filter_username'] = $filter_username;
    $data['filter_assign_date'] = $filter_assign_date;
    $data['specific_date'] = $specific_date; // Pass to view if needed

    $url = '';
    if ($filter_username) {
        $url .= '&filter_username=' . urlencode($filter_username);
    }
    if ($filter_assign_date) {
        $url .= '&filter_assign_date=' . urlencode($filter_assign_date);
    }

    $data['action'] = $this->url->link('assign/assign_work/viewDetails', 'user_token=' . $data['user_token'] . '&user_id=' . $user_id . $url, true);
    $data['back_url'] = $this->url->link('assign/assign_work', 'user_token=' . $data['user_token'], true);

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    if ($specific_date) {
    $data['sellers'] = $this->model_assign_assign_work->getSellersByDate($user_id, $specific_date);
    $template = 'assign/seller_details';
}


    $this->response->setOutput($this->load->view($template, $data));
}
    public function addForm() {
        $this->load->language('assign/assign_work');
        $this->document->setTitle('Assign Seller');

        $this->load->model('user/user');
        $this->load->model('assign/assign_work');

        $data['heading_title'] = 'Assign Seller';
        $data['user_token'] = $this->session->data['user_token'];
        $data['users'] = $this->model_user_user->getUsers();
        $data['sellers'] = $this->model_assign_assign_work->getSellers();

        $data['action'] = $this->url->link('assign/assign_work/saveAssignment', 'user_token=' . $data['user_token'], true);
        $data['back_url'] = $this->url->link('assign/assign_work', 'user_token=' . $data['user_token'], true);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('assign/add_form', $data));
    }

 public function viewSellerDetails() {
    $this->load->language('assign/assign_work');
    $this->document->setTitle($this->language->get('heading_title'));
    $this->load->model('assign/assign_work');

    $user_id = isset($this->request->get['user_id']) ? (int)$this->request->get['user_id'] : 0;
    $assign_date = isset($this->request->get['assign_date']) ? $this->request->get['assign_date'] : '';

    $data['user_id'] = $user_id;
    $data['user_token'] = $this->session->data['user_token'];
    $data['assign_date'] = $assign_date;
    
    // Fetch seller details - filtered by date if provided
    $seller_data = $this->model_assign_assign_work->getSellerDetailsByUserId($user_id, $assign_date);

    // ✅ Add edit_url for each seller
    foreach ($seller_data as &$seller) {
        // existing code...
            $seller['edit_url'] = $this->url->link(
                'vendor/vendor/edit',
                'user_token=' . $data['user_token'] . '&vendor_id=' . $seller['seller_id'],
                true
            );
        }

        $data['sellers'] = $seller_data;
        $data['back_url'] = $this->url->link('assign/assign_work/viewDetails', 'user_token=' . $data['user_token'] . '&user_id=' . $user_id, true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('assign/seller_details', $data));
    }

//vaishnavi
    public function saveAssignment() {
        $this->load->model('assign/assign_work');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $user_id = (int)$this->request->post['user_id'];
            $seller_ids = isset($this->request->post['seller_id']) ? $this->request->post['seller_id'] : [];

            if ($user_id && !empty($seller_ids)) {
                // Counter for successful assignments
                $assigned_count = 0;
                
                // Process each selected seller
                foreach ($seller_ids as $seller_id) {
                    $seller_id = (int)$seller_id;
                    
                    // Call model method to save assignment
                    $this->model_assign_assign_work->assignSeller($user_id, $seller_id);
                    $assigned_count++;
                    
                    // Get the username for the current user
                    $this->load->model('user/user');
                    $user_info = $this->model_user_user->getUser($user_id);
                    $username = $user_info['username'];
                    
                    // Create seller-specific folder if it doesn't exist
                    $seller_folder = DIR_IMAGE . 'catalog/multivendor/' . $seller_id;
                    if (!is_dir($seller_folder)) {
                        mkdir($seller_folder, 0777, true);
                        file_put_contents($seller_folder . '/index.html', '');
                    }
                    
                    // Create username folder inside seller folder
                    $username_folder = $seller_folder . '/' . $username;
                    if (!is_dir($username_folder)) {
                        mkdir($username_folder, 0777, true);
                        file_put_contents($username_folder . '/index.html', '');
                    }
                }
                
                // Set success message with count
                $this->session->data['success'] = $assigned_count . ' seller(s) successfully assigned to user.';
                
                // Redirect to the main assign work list page
                $this->response->redirect($this->url->link('assign/assign_work', 'user_token=' . $this->session->data['user_token'], true));
                return;
            } else {
                // Handle missing input
                $this->session->data['error'] = 'Please select a user and at least one seller.';
                $this->response->redirect($this->url->link('assign/assign_work/addForm', 'user_token=' . $this->session->data['user_token'], true));
                return;
            }
        }

        $this->response->redirect($this->url->link('assign/assign_work', 'user_token=' . $this->session->data['user_token'], true));
    }
//Vaishnavi
    public function unassignSeller() {
        $this->load->language('assign/assign_work');
        $json = [];

        if (!$this->user->hasPermission('modify', 'assign/assign_work')) {
            $json['error'] = 'Permission Denied!';
        } elseif (!isset($this->request->post['seller_id']) || !is_numeric($this->request->post['seller_id']) 
                || !isset($this->request->post['user_id']) || !is_numeric($this->request->post['user_id'])) {
            $json['error'] = 'Invalid Seller ID or User ID';
        } else {
            $seller_id = (int)$this->request->post['seller_id'];
            $user_id = (int)$this->request->post['user_id'];

            $this->load->model('assign/assign_work');

            $unassign = $this->model_assign_assign_work->unassignSeller($user_id, $seller_id);

            if ($unassign) {
                $json['success'] = 'Seller unassigned successfully';
            } else {
                $json['error'] = 'Failed to unassign seller';
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
  
    public function unassignAllSellers() {
        $this->load->language('assign/assign_work');
        $json = [];

        if (!$this->user->hasPermission('modify', 'assign/assign_work')) {
            $json['error'] = 'Permission Denied!';
        } elseif (!isset($this->request->post['user_id']) || !is_numeric($this->request->post['user_id'])) {
            $json['error'] = 'Invalid User ID';
        } else {
            $user_id = (int)$this->request->post['user_id'];

            $this->load->model('assign/assign_work');

            $unassign_count = $this->model_assign_assign_work->unassignAllSellers($user_id);

            if ($unassign_count > 0) {
                $json['success'] = $unassign_count . ' seller(s) unassigned successfully';
            } else {
                $json['error'] = 'No sellers were unassigned';
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
}

