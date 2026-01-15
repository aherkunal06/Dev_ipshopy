<?php
class ControllerTrackingSellerlist extends Controller {

    // Index method to display seller registrations with filtering
    public function index() {
        $this->load->model('tracking/seller_list');

        // Get the filter parameters from the request
        $filter_username = $this->request->get['filter_username'] ?? ''; // Filter by username
        $filter_date = $this->request->get['filter_date'] ?? ''; // Filter by date

        // Fetch the seller registrations with filtering
        $sellers = $this->model_tracking_seller_list->getSellerRegistrations($filter_username, $filter_date);

        $data['sellers'] = [];

        foreach ($sellers as $result) {
            $data['sellers'][] = [
                'date' => $result['date'],
                'registered_by' => $result['registered_by'],
                'total_registrations' => $result['total_registrations'],
                'approve_count' => $result['approve_count'],
                'disapprove_count' => $result['disapprove_count'],
                'pending_count' => $result['pending_count'],
                'view' => $this->url->link(
                    'tracking/seller_list/view',
                    'user_token=' . $this->session->data['user_token'] . '&filter_username=' . urlencode($result['registered_by']) . '&date=' . $result['date'],
                    true
                )
            ];
        }

        // Set the user_token
        $data['user_token'] = $this->session->data['user_token'];

        // Load the common headers, footer, and left column
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Set the output to the template with the data
        $this->response->setOutput($this->load->view('tracking/seller_list', $data));
    }

    // View method to show vendor details for specific registration and date
    public function view() {
        $this->load->language('tracking/seller_list');
        $this->document->setTitle('Vendor Details');

        // Fetch the required parameters from the URL
        $filter_username = $this->request->get['filter_username'] ?? ''; // The filter username
        $date = $this->request->get['date'] ?? '';

        $this->load->model('tracking/seller_list');

        // Fetch the vendors using the model
        $vendors = $this->model_tracking_seller_list->getVendorsByRegisteredByAndDate($filter_username, $date);

        $data['vendors'] = [];
        foreach ($vendors as $vendor) {
            $data['vendors'][] = [
                'vendor_id'      => $vendor['vendor_id'] ?? '',
                'firstname'      => $vendor['firstname'] ?? '',
                'lastname'       => $vendor['lastname'] ?? '',
                'email'          => $vendor['email'] ?? '',
                'display_name'   => $vendor['display_name'] ?? '',
                'status'         => $vendor['status'] ?? '',
                'approved'       => $vendor['approved'] ?? '',
                'product_status' => $vendor['product_status'] ?? '',
                'edit'           => $this->url->link('vendor/vendor/edit', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $vendor['vendor_id'], true),
                'approve'        => $this->url->link('vendor/vendor/approve', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $vendor['vendor_id'], true)
            ];
        }

        // Return back to the seller list view
        $data['back'] = $this->url->link('tracking/seller_list', 'user_token=' . $this->session->data['user_token'] . '&filter_username=' . $this->request->get['filter_username'], true);

        // Set the user_token for the view
        $data['user_token'] = $this->session->data['user_token'];

        // Load common parts of the admin panel
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');  

        // Set the output of the view template
        $this->response->setOutput($this->load->view('tracking/seller_view', $data));
    }

    // User-wise seller registration method to show total registrations
    public function user() {
        $this->load->language('tracking/seller_list');
        $this->document->setTitle('User-wise Seller Registrations');

        $this->load->model('tracking/seller_list');
        
        // Get the filter username from the request
        $filter_username = isset($this->request->get['filter_username']) ? $this->request->get['filter_username'] : '';

        // Pass the filter to the model method
        $results = $this->model_tracking_seller_list->getTotalRegistrationsByUser($filter_username);

        $data['users'] = [];

        foreach ($results as $result) {
            $data['users'][] = [
                'registered_by' => $result['registered_by'],
                'total'         => $result['total'],
                'view'          => $this->url->link(
                    'tracking/seller_list',
                    'user_token=' . $this->session->data['user_token'] . '&filter_username=' . urlencode($result['registered_by']),
                    true
                )
            ];
        }

        // Pass the current filter value to the view
        $data['filter_username'] = $filter_username;

        // Set the user_token for the view
        $data['user_token'] = $this->session->data['user_token'];

        // Load common headers, footer, and left column
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Set the output to the template with the data
        $this->response->setOutput($this->load->view('tracking/seller_user', $data));
    }

    public function autocomplete() {
        $json = [];

        if (isset($this->request->get['filter_username'])) {
            $this->load->model('user/user');

            $filter_data = [
                'filter_username' => $this->request->get['filter_username'],
                'start'           => 0,
                'limit'           => 10
            ];
            ////////////////////////////////////////////////
            // chnged function getusers() to support filtering 
            /////////////////////////////////////////////
            $results = $this->model_user_user->getUsers($filter_data);

            foreach ($results as $result) {
                $json[] = [
                    'user_id'  => $result['user_id'],
                    'username' => html_entity_decode($result['username'], ENT_QUOTES, 'UTF-8')
                ];
            }
        }

        $sort_order = [];

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['username'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


}
