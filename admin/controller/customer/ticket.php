<?php
class ControllerCustomerTicket extends Controller {
    public function index() {
        $this->load->language('customer/ticket');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('customer/ticket');

        // Search filter
        $filter_ticket_id = isset($this->request->get['filter_ticket_id']) ? $this->request->get['filter_ticket_id'] : '';

        $filter_data = [
            'filter_ticket_id' => $filter_ticket_id
        ];

        $tickets = $this->model_customer_ticket->getTickets($filter_data);

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

        $data['tickets'] = [];
        foreach ($tickets as $ticket) {
            $category_text = isset($category_map[$ticket['category']]) ? $category_map[$ticket['category']] : $ticket['category'];
            $data['tickets'][] = [
                'ticket_id'      => $ticket['ticket_id'],
                'customer_id'    => $ticket['customer_id'],
                'customer_name'  => $ticket['customer_name'],
                'subject'        => $ticket['subject'],
                'category'       => $category_text, // <-- send text not value
                'message'        => $ticket['description'],
                'status'         => $ticket['status'],
                'date_added'     => $ticket['date_added'],
                'view'           => $this->url->link('customer/ticket/view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $ticket['ticket_id'], true)
            ];
        }

        $data['filter_ticket_id'] = $filter_ticket_id;
        $data['user_token'] = $this->session->data['user_token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('customer/ticket_list', $data));
    }

    public function view() {
        $this->load->language('customer/ticket');
        $this->load->model('customer/ticket');

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->get['ticket_id'])) {
            $ticket_id = (int)$this->request->get['ticket_id'];
            $data['ticket_id'] = $ticket_id;

            // Get ticket details
            $ticket_info = $this->model_customer_ticket->getTicket($ticket_id);

            if ($ticket_info) {
                $data['subject'] = $ticket_info['subject'];
                $data['description'] = $ticket_info['description'];
                $data['status'] = $ticket_info['status'];
                $data['date_added'] = $ticket_info['date_added'];

                // Get replies for the ticket
                $data['replies'] = $this->model_account_ticket->getReplies($ticket_id);
            } else {
                $data['error'] = $this->language->get('error_not_found');
            }
        } else {
            $data['error'] = $this->language->get('error_not_found');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('customer/ticket_view', $data));
    }
}