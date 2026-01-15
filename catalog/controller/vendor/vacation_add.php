<?php
class ControllerVendorVacationAdd extends Controller
{
    public function index()
    {
        $this->load->language('vendor/vacation');
        $this->load->model('vendor/vacation');
        $this->load->model('vendor/vendor'); // Load vendor model

        $vendor_id = $this->vendor->getId();

    
        $vendor_info = $this->model_vendor_vendor->getVendor($vendor_id);

        // $data['vendor_name'] = $vendor_info['firstname'] . ' ' . $vendor_info['lastname'];
        // $data['display_name'] = $vendor_info['display_name'];
        if ($vendor_info) {
            $data['vendor_name'] = $vendor_info['firstname'] . ' ' . $vendor_info['lastname']; // Concatenate first and last name
            $data['display_name'] = $vendor_info['display_name']; // Store the display name
        } else {
            // If vendor data doesn't exist, handle appropriately (for example, set default values)
            $data['vendor_name'] = 'Unknown Vendor';
            $data['display_name'] = 'No Display Name';
        }

        $processing_count = $this->model_vendor_vacation->getProcessingOrderCount($vendor_id);
        $data['processing_order_count'] = $processing_count;





        // Set default data
        $data['start_date'] = '';
        $data['end_date'] = '';
        $data['reason'] = '';
        $data['success'] = '';
        $data['error_warning'] = '';

        // Check for pending orders (before any submission)
        $has_pending_orders = $this->model_vendor_vacation->hasPendingOrders($vendor_id);
        $data['has_pending_orders'] = $has_pending_orders;

        if ($has_pending_orders) {
            $data['error_warning'] = $this->language->get('error_pending_orders');
        }

        // On POST (form submission)
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

          

            // Checkbox confirmation check
            if (!isset($this->request->post['confirm_clear_orders'])) {
                $this->session->data['error_warning'] = $this->language->get('text_confirm_orders_cleared');
                $this->response->redirect($this->url->link('vendor/vacation_add', '', true));
            }

            // Validate date range
            // $start_date = new DateTime($this->request->post['start_date']);
            $start_date = new DateTime($this->request->post['start_date']);
            $start_date->setTime(0, 0, 0); // Normalize
            $end_date = new DateTime($this->request->post['end_date']);
            $interval = $start_date->diff($end_date)->days;


            if ($interval > 30) {
                $this->session->data['error_warning'] = $this->language->get('error_max_days');
                $this->response->redirect($this->url->link('vendor/vacation_add', '', true));
            }

            // validation for 2days before 
            $today = new DateTime();
            $today->setTime(0, 0, 0);

            $min_start = (clone $today)->modify('+2 days'); // Vacation can start only from day after tomorrow

            if ($start_date < $min_start) {
                $this->session->data['error_warning'] = 'Start date must be at least 1 days from today.';
                $this->response->redirect($this->url->link('vendor/vacation_add', '', true));
            }
            // Check again before disabling products
            // $products_disabled = $this->model_vendor_vacation->disableVendorProductsDuringVacation($vendor_id, $start_date, $end_date);

            // if ($products_disabled) {
            //     $this->session->data['error_warning'] = 'You have pending orders. Products cannot be disabled.';
            //     $this->response->redirect($this->url->link('vendor/vacation_add', '', true));
            // }
            
            $result = $this->model_vendor_vacation->disableVendorProductsDuringVacation($vendor_id, $start_date, $end_date);

            if (!$result['status']) {
                $this->session->data['error_warning'] = 'You have ' . $result['pending_orders'] . ' pending orders.';
                $this->response->redirect($this->url->link('vendor/vacation_add', '', true));
            }





            // Save vacation info
            $this->model_vendor_vacation->addVacation([
                // 'vendor_name'   => $data['vendor_name'],
                // 'display_name'  => $data['display_name'],
                'vendor_name'   => ($vendor_info['firstname'] ?? '') . ' ' . ($vendor_info['lastname'] ?? ''),
                'display_name'  => $vendor_info['display_name'] ?? '',
                'vendor_id'     => $vendor_id,
                'start_date'    => $this->request->post['start_date'],
                'end_date'      => $this->request->post['end_date'],
                'reason'        => $this->request->post['reason'],
                'status'        => 'Pending',
                'date_added'    => date('Y-m-d H:i:s')
            ]);

            $this->session->data['success'] = $this->language->get('text_success_vacation_added');
            $this->response->redirect($this->url->link('vendor/vacation_list', '', true));
        }

        // Show session flash messages
        $data['error_warning'] = $this->session->data['error_warning'] ?? '';
        unset($this->session->data['error_warning']);

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $form_valid) {
            $data['success'] = 'Your form was submitted successfully!';
            $data['form_submitted'] = true;
        } else {
            $data['form_submitted'] = false;
        }

        // Breadcrumbs
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('vendor/dashboard', '', true)
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('vendor/vacation_add', '', true)
            ]
        ];

        $data['action'] = $this->url->link('vendor/vacation_add', '', true);

        // Load common controllers
        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        $this->response->setOutput($this->load->view('vendor/vacation_add', $data));
    }
    
}