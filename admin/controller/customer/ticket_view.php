<?php

class ControllerCustomerTicketView extends Controller
{
    public function index()
    {
        $this->load->language('customer/ticket');
        $this->document->setTitle('Ticket Details');
        $this->load->model('customer/ticket');
        $this->load->model('customer/customer');

        $ticket_id = isset($this->request->get['ticket_id']) ? (int)$this->request->get['ticket_id'] : 0;
        $ticket = $this->model_customer_ticket->getTicket($ticket_id);

        // Category mapping
        $category_map = [
            '1' => 'Product Listing',
            '2' => 'Technical',
            '3' => 'Order Issues',
            '4' => 'Payment and Billing Issues',
            '5' => 'Shipping and Delivery',
            '6' => 'Product or Service Inquiries',
            '7' => 'Returns and Exchanges'
        ];

        if ($ticket) {
            $ticket['category'] = isset($category_map[$ticket['category']]) ? $category_map[$ticket['category']] : $ticket['category'];
        }

        $data['ticket'] = $ticket;
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['user_token'] = $this->session->data['user_token'];
        $data['replies'] = $this->model_customer_ticket->getReplies($ticket_id);

        foreach ($data['replies'] as $key => $result) {
            if ($result['user_type'] == 'customer') {
                $customer_info = $this->model_customer_customer->getCustomer($result['customer_id']);
                $customer_name = $customer_info ? $customer_info['firstname'] : 'Customer';
            } else {
                $customer_name = 'Admin';
            }

            $data['replies'][$key]['customer_name'] = $customer_name;
        }

        $data['status_action'] = $this->url->link('customer/ticket_view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $ticket_id, true);
        $data['back'] = $this->url->link('customer/ticket', 'user_token=' . $this->session->data['user_token'], true);

        $this->response->setOutput($this->load->view('customer/ticket_view', $data));
    }
    // handle file upload and save reply
    public function reply()
    {
        $this->load->model('customer/ticket');
        $ticket_id = (int)$this->request->get['ticket_id'];

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && !empty($this->request->post['reply_message'])) {
            $reply_message = $this->request->post['reply_message'];
            $reply_file = '';

            // Handle file upload
            if (!empty($_FILES['reply_file']['name'])) {
                $filename = uniqid() . '_' . basename($_FILES['reply_file']['name']);

                $upload_dir = DIR_IMAGE . 'ticket_uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                move_uploaded_file($_FILES['reply_file']['tmp_name'], $upload_dir . $filename);
                $reply_file = $filename;
            }

            // Save reply in DB
            $this->model_customer_ticket->addReply($ticket_id, $reply_message, $reply_file, 'admin');
        }

        $this->response->redirect($this->url->link('customer/ticket_view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $ticket_id, true));
    }
    // status handling code
    public function updateStatus()
    {
        if (isset($this->request->post['ticket_status'])) {
            $ticket_id = isset($this->request->get['ticket_id']) ? (int)$this->request->get['ticket_id'] : 0;
            $status = $this->request->post['ticket_status'];
            $this->load->model('customer/ticket');
            $this->model_customer_ticket->updateStatus($ticket_id, $status);
            $this->response->redirect($this->url->link('customer/ticket_view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $ticket_id, true));
        }
    }
}
