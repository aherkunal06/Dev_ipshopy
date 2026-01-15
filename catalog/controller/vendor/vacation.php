<?php
class ControllerVendorVacation extends Controller {
    public function index() {
        $this->load->language('vendor/vacation');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('vendor/vacation');

        $data['vacations'] = [];
        

        $results = $this->model_vendor_vacation->getAllVacations();

        foreach ($results as $result) {
            $data['vacations'][] = [
                'vacation_id'   => $result['vacation_id'],
                'firstname'     => $result['firstname'],
                'lastname'      => $result['lastname'],
                'display_name'  => $result['display_name'],
                'start_date'    => $result['start_date'],
                'end_date'      => $result['end_date'],
                'reason'        => $result['reason'],
                'status'        => $result['status'],
                'date_added'    => $result['date_added'],
                'edit'          => $this->url->link('vendor/vacation_form', 'user_token=' . $this->session->data['user_token'] . '&vacation_id=' . $result['vacation_id'], true)
            ];
        }

        $data['approve'] = $this->url->link('vendor/vacation/approve', 'user_token=' . $this->session->data['user_token'], true);
        $data['reject'] = $this->url->link('vendor/vacation/reject', 'user_token=' . $this->session->data['user_token'], true);

        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        $this->response->setOutput($this->load->view('vendor/vacation_list', $data));
    }

    public function approve() {
        if (isset($this->request->get['vacation_id'])) {
            $this->load->model('vendor/vacation');
            $this->model_vendor_vacation->updateVacationStatus($this->request->get['vacation_id'], 'Approved');
            $this->session->data['success'] = 'Vacation approved successfully!';
        }

        $this->response->redirect($this->url->link('vendor/vacation', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function reject() {
        if (isset($this->request->get['vacation_id'])) {
            $this->load->model('vendor/vacation');
            $this->model_vendor_vacation->updateVacationStatus($this->request->get['vacation_id'], 'Rejected');
            $this->session->data['success'] = 'Vacation rejected successfully!';
        }

        $this->response->redirect($this->url->link('vendor/vacation', 'user_token=' . $this->session->data['user_token'], true));
    }
}
