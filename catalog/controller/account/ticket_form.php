<?php
//new file--------------------------------------
class ControllerAccountTicketForm extends Controller {
    public function index() {
        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/login', '', true));
        }
        $this->load->language('account/ticket_form');
        $this->document->setTitle($this->language->get('heading_title'));           

        $this->load->model('account/ticket');

        // Example categories (replace with DB fetch if needed)
        $data['categories'] = [
            ['category_id' => '', 'name' => $this->language->get('text_select_category')],
            ['category_id' => '1', 'name' => 'Order Issues'],
            ['category_id' => '2', 'name' => 'Payment and Billing Issues'],
            ['category_id' => '3', 'name' => 'Shipping and Delivery'],
            ['category_id' => '4', 'name' => 'Product or Service Inquiries'],
            ['category_id' => '5', 'name' => 'Returns and Exchanges'],
            ['category_id' => '6', 'name' => 'Others'],
        ];

        $data['action'] = $this->url->link('account/ticket_form', '', true);
        $data['view_tickets'] = $this->url->link('account/ticket_list', '', true); // You need to create ticket_list controller/view
        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $data['success'] = '';
        $data['error_warning'] = '';

        // Pre-fill form values
        $data['subject'] = isset($this->request->post['subject']) ? $this->request->post['subject'] : '';
        $data['category'] = isset($this->request->post['category']) ? $this->request->post['category'] : '';
        $data['description'] = isset($this->request->post['description']) ? $this->request->post['description'] : '';

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (empty($this->request->post['subject']) || empty($this->request->post['category']) || empty($this->request->post['description'])) {
                $data['error_warning'] = 'All fields are required!';
            } else {
                // File upload logic (optional)
                $file_name = '';
                if (!empty($this->request->files['file']['name'])) {
                    $upload_dir = DIR_IMAGE . 'ticket_uploads/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $file_name = uniqid() . '_' . basename($this->request->files['file']['name']);
                    move_uploaded_file($this->request->files['file']['tmp_name'], $upload_dir . $file_name);
                }
                $this->model_account_ticket->addTicket($this->request->post, $this->customer->getId(), $file_name);
                $data['success'] = 'Your ticket has been submitted!';
                $data['subject'] = '';
                $data['category'] = '';
                $data['description'] = '';
            }
        }



$data['column_left_account'] = $this->load->controller('account/column_left_account');
        $this->response->setOutput($this->load->view('account/ticket_form', $data));
    }
}
?>

