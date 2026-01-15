<?php
class ControllerVendorVacationList extends Controller
{
    public function index()
    {
        $this->load->language('vendor/vacation');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('vendor/vacation');
        $this->load->model('vendor/vendor');

        $data['vacations'] = [];
        $vendor_id = $this->vendor->getId(); // Logged-in vendor ID

        // $results = $this->model_vendor_vacation->getAllVacations();
        $results = $this->model_vendor_vacation->getVacationsByVendorId($vendor_id);
        
        // added at 27-06-2025 Check if there are any pending vacation requests
       // check if there is any pending vacation
        $pending_vacation = false;
        foreach ($results as $vacation) {
            if (strtolower($vacation['status']) == 'pending') {
                $pending_vacation = true;
                break;
            }
        }
        $data['pending_vacation'] = $pending_vacation;
        
        // initialize
        $data['vacations'] = [];
        
        // ------------===============================================


        foreach ($results as $result) {
            
            
            $data['vacations'][] = [
                'vacation_id'   => $result['vacation_id'],
                'vendor_name'   => $result['firstname'], $result['lastname'],
                'display_name'  => $result['display_name'],
                'start_date'    => $result['start_date'],
                'end_date'      => $result['end_date'],
                'reason'        => $result['reason'],
                'status'        => $result['status'],
                'date_added'    => $result['date_added'],
                // 'view'          => $this->url->link('vendor/vacation_form', 'vacation_id=' . $result['vacation_id'], true),
                $data['add_vacation_url'] = $this->url->link('vendor/vacation_add', 'user_token=' . $this->session->data['user_token'], true),
                // 'edit'          => $this->url->link('vendor/vacation_form', 'user_token=' . $this->session->data['user_token'] . '&vacation_id=' . $result['vacation_id'], true),
                'edit'          => $this->url->link('vendor/vacation_form', 'vacation_id=' . $result['vacation_id'], true)

            ];
        }
        $data['add_vacation_url'] = $this->url->link('vendor/vacation_add', '', true);
        
        $data['cancel'] = $this->url->link('vendor/dashboard');

        // $data['action'] = $this->url->link('vendor/vacation_form', '', true);

        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        $this->response->setOutput($this->load->view('vendor/vacation_list', $data));
    }
  
}
