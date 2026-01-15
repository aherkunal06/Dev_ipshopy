<?php
class Controllervendorhsnrequestlist extends Controller {
    public function index() {
        $this->load->language('vendor/hsn_request_list');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('vendor/hsn_request_list');

        $data['hsn_requests'] = array();

        $results = $this->model_vendor_hsn_request_list->getHsnRequests();

        foreach ($results as $result) {
            $data['hsn_requests'][] = array(
                'id'          => $result['id'],
                'vendor_id'   => $result['vendor_id'],
                'hsn_code'    => $result['hsn_code'],
                'description' => $result['description'],
                'gst_rate'    => $result['gst_rate'],
                'status'      => $result['status'],
                'date_added'  => $result['date_added'],
             
                'approve'     => $this->url->link('vendor/hsn_request_list/approve', 'user_token=' . $this->session->data['user_token'] . '&id=' . $result['id']),
                'reject'      => $this->url->link('vendor/hsn_request_list/reject', 'user_token=' . $this->session->data['user_token'] . '&id=' . $result['id']),
            );
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['user_token'] = $this->session->data['user_token'];

       $data['success'] = $this->session->data['success'] ?? '';
unset($this->session->data['success']);

        $this->response->setOutput($this->load->view('vendor/hsn_request_list', $data));
    }

  

// protected function sendEmail($to, $subject, $message) {
//     if (empty($to)) {
//         error_log("⚠️ Cannot send email: recipient is empty.");
//         return;
//     }

//     $mail = new Mail();
//     $mail->protocol = $this->config->get('config_mail_protocol');
//     $mail->parameter = $this->config->get('config_mail_parameter');
//     $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
//     $mail->smtp_username = $this->config->get('config_mail_smtp_username');
//     $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
//     $mail->smtp_port = $this->config->get('config_mail_smtp_port');
//     $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

//     $mail->setTo($to);
//     $mail->setFrom($this->config->get('config_email'));
//     $mail->setSender($this->config->get('config_name'));
//     $mail->setSubject($subject);
//     $mail->setText($message);
//     $mail->send();
// }



    public function approve() {
    $this->load->model('vendor/hsn_request_list');
    $vendor = $this->model_vendor_hsn_request_list->updateStatus($this->request->get['id'], 'Approved');

    $subject = "HSN Code Approved";
    $message = "Dear " . $vendor['firstname'] . ",\n\nYour request for HSN Code " . $vendor['hsn_code'] . " has been approved by admin.";

    // $this->sendEmail($vendor['email'], $subject, $message);

    $this->response->redirect($this->url->link('vendor/hsn_request_list', 'user_token=' . $this->session->data['user_token']));
}

public function reject() {
    $this->load->model('vendor/hsn_request_list');
    $vendor = $this->model_vendor_hsn_request_list->updateStatus($this->request->get['id'], 'Rejected');

    $subject = "HSN Code Rejected";
    $message = "Dear " . $vendor['firstname'] . ",\n\nUnfortunately, your request for HSN Code " . $vendor['hsn_code'] . " has been rejected. Please contact support for details.";

    // $this->sendEmail($vendor['email'], $subject, $message);

    $this->response->redirect($this->url->link('vendor/hsn_request_list', 'user_token=' . $this->session->data['user_token']));
}

}

