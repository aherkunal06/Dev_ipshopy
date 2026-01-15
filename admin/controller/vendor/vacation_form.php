<?php
class ControllerVendorVacationForm extends Controller {
    public function index() {
        $this->load->language('vendor/vacation');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('vendor/vacation');
        $this->load->model('vendor/vendor');

        $vacation_id = isset($this->request->get['vacation_id']) ? (int)$this->request->get['vacation_id'] : 0;

        $vacation_info = $this->model_vendor_vacation->getVacation($vacation_id);

        if ($vacation_info && is_array($vacation_info)) {
            $vendor_id = $vacation_info['vendor_id'];
            $vendor = $this->model_vendor_vendor->getVendor($vendor_id);

            if (!$vendor) {
                $this->session->data['error_warning'] = 'Vendor not found for this vacation request.';
                $this->response->redirect($this->url->link('vendor/vacation_list', 'user_token=' . $this->session->data['user_token'], true));
            }
            else{
                unset($this->session->data['error_warning']);
            }

            // Fill vacation data
            $data['vacation_id']   = $vacation_info['vacation_id'];
            $data['vendor_id']     = $vendor_id;
            $data['start_date']    = $vacation_info['start_date'];
            $data['end_date']      = $vacation_info['end_date'];
            $data['reason']        = $vacation_info['reason'];
            $data['status']        = $vacation_info['status'];
            $data['approval_date'] = $vacation_info['approval_date'];
            // Vendor details
            $data['firstname']     = $vendor['firstname'] ?? '';
            $data['lastname']      = $vendor['lastname'] ?? '';
            $data['display_name']  = $vendor['display_name'] ?? '';

            // Product details
            $data['active_products'] = $this->model_vendor_vacation->getVendorActiveProductCount($vendor_id);
            $data['total_quantity']  = $this->model_vendor_vacation->getVendorTotalQuantity($vendor_id);
        } else {
            $this->session->data['error_warning'] = 'Vacation not found.';
            $this->response->redirect($this->url->link('vendor/vacation_list', 'user_token=' . $this->session->data['user_token'], true));
        }

        // Action buttons
        $data['approve'] = $this->url->link('vendor/vacation_list/approve', 'user_token=' . $this->session->data['user_token'] . '&vacation_id=' . $vacation_id, true);
        $data['reject']  = $this->url->link('vendor/vacation_list/reject', 'user_token=' . $this->session->data['user_token'] . '&vacation_id=' . $vacation_id, true);
        
        // added on 08-05-2025--------------------
        $url = '';
        $data['cancel'] = $this->url->link('vendor/vacation_form', 'user_token=' . $this->session->data['user_token'] . $url, true);
        //-----------------------------------------------------
        // Load common views
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('vendor/vacation_form', $data));
    }

    
}