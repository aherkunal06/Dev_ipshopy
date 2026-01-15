<?php
class ControllerVendorVacationForm extends Controller {
    
    public function index() {
        $this->load->language('vendor/vacation');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('vendor/vacation');

        if (isset($this->request->get['vacation_id'])) {
            $vacation_id = (int)$this->request->get['vacation_id'];
        } else {
            $vacation_id = 0;
        }
// date validation for 30 days only
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $start_date = new DateTime($this->request->post['start_date']);
            $end_date = new DateTime($this->request->post['end_date']);
            $interval = $start_date->diff($end_date)->days;
        
            if ($interval > 30) {
                $this->session->data['error_warning'] = $this->language->get('error_max_days');
                $this->response->redirect($this->url->link('vendor/vacation_form', '', true));
            }
        
            if (!isset($this->request->post['confirm_clear_orders'])) {
                $this->session->data['error_warning'] = $this->language->get('error_orders_not_cleared');
                $this->response->redirect($this->url->link('vendor/vacation_form', '', true));
            }
            
        }
        
        $vacation_info = $this->model_vendor_vacation->getVacation($vacation_id);
        
        if ($vacation_info) {
            $data['vacation_id'] = $vacation_id;
            $data['vendor_id'] = $vacation_info['vendor_id'];
            $data['vendor_name'] = $vacation_info['firstname'] . ' ' . $vacation_info['lastname'];
            $data['display_name'] = $vacation_info['display_name'];
            // $data['start_date'] = $vacation_info['start_date'];
            // $data['end_date'] = $vacation_info['end_date'];

            // $data['start_date'] = $this->request->post['start_date'];
            // $data['end_date'] = $this->request->post['end_date'];
            $data['start_date'] = isset($vacation_info['start_date']) ? $vacation_info['start_date'] : '';
            $data['end_date'] = isset($vacation_info['end_date']) ? $vacation_info['end_date'] : '';
            
            $data['reason'] = $vacation_info['reason'];
            $data['status'] = $vacation_info['status'];
            $data['approval_date'] = $vacation_info['approval_date'];
        } else {
            $this->response->redirect($this->url->link('vendor/vacation_list', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data['approve'] = $this->url->link('vendor/vacation_list/approve', 'user_token=' . $this->session->data['user_token'] . '&vacation_id=' . $vacation_id, true);
        $data['reject'] = $this->url->link('vendor/vacation_list/reject', 'user_token=' . $this->session->data['user_token'] . '&vacation_id=' . $vacation_id, true);

        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        // $this->model_vendor_vacation->addVacation($this->customer->getId(), $this->request->post); // Assuming vendor is logged in
        $this->session->data['success'] = $this->language->get('text_vacation_success');
            
        $this->response->setOutput($this->load->view('vendor/vacation_form', $data));
    }
}
