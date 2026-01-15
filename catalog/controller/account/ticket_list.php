<?php

class ControllerAccountTicketList extends Controller {

public function index() {
    if (!$this->customer->isLogged()) {
        $this->response->redirect($this->url->link('account/login', '', true));
    }

    $this->load->language('account/ticket_list');
    $this->document->setTitle($this->language->get('heading_title'));
    $this->load->model('account/ticket');

    // ✅ Get filters from URL
    $status = isset($this->request->get['status']) ? $this->request->get['status'] : 'all';
    $ticket_id_filter = isset($this->request->get['ticket_id']) ? $this->request->get['ticket_id'] : '';

    // ✅ Fetch filtered tickets (ID + status)
    if (!empty($ticket_id_filter)) {
        $tickets = $this->model_account_ticket->getTicketById($this->customer->getId(), $status, $ticket_id_filter);
    } else {
        $tickets = $this->model_account_ticket->getTickets($this->customer->getId(), $status);
    }
    $category_map = [
    1 => 'Order Issues',
    2 => 'Payment and Billing Issues',
    3 => 'Shipping and Delivery',
    4 => 'Product or Service Inquiries',
    5 => 'Returns and Exchanges',
    6 => 'Others'
];


    $data['tickets'] = [];
    foreach ($tickets as $ticket) {
        $data['tickets'][] = [
            'ticket_id'    => $ticket['ticket_id'],
            'subject'      => $ticket['subject'],
            'description'  => $ticket['description'],
            'status'       => $ticket['status'],
            'date_added'   => $ticket['date_added'],
            'category' => isset($category_map[$ticket['category']]) ? $category_map[$ticket['category']] : 'Unknown',
            'file'         => $ticket['file']
        ];
    }

    // ✅ For counters (total tickets etc.) - unfiltered
    $all_tickets = $this->model_account_ticket->getTickets($this->customer->getId());

    $total_tickets = count($all_tickets);
    $open_count = 0;
    $pending_count = 0;
    $closed_count = 0;

    foreach ($all_tickets as $ticket) {
        $status_lower = strtolower($ticket['status']);
        if ($status_lower == 'open') {
            $open_count++;
        } elseif ($status_lower == 'pending') {
            $pending_count++;
        } elseif ($status_lower == 'closed') {
            $closed_count++;
        }
    }

    // ✅ Pass filters and counters to view
    $data['ticket_id_filter'] = $ticket_id_filter;
    $data['status_filter'] = $status;

    $data['total_tickets'] = $total_tickets;
    $data['open_count'] = $open_count;
    $data['pending_count'] = $pending_count;
    $data['closed_count'] = $closed_count;

    // ✅ Links and layout
    $data['action_filter'] = $this->url->link('account/ticket_list', '', true);
    $data['url_ticket_view'] = $this->url->link('account/ticket_view', '', true);
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');


    $data['column_left_account'] = $this->load->controller('account/column_left_account');

    $this->response->setOutput($this->load->view('account/ticket_list', $data));
}

 public function filter() {
    if (!$this->customer->isLogged()) {
        $this->response->redirect($this->url->link('account/login', '', true));
    }

    $this->load->model('account/ticket');

    $ticket_id = isset($this->request->get['ticket_id']) ? $this->request->get['ticket_id'] : '';
    $customer_id = $this->customer->getId();

    $tickets = $this->model_account_ticket->getTicketById($customer_id, 'all', $ticket_id);

    $this->response->addHeader('Content-Type: text/html; charset=utf-8');

    if ($tickets) {
        foreach ($tickets as $ticket) {
            echo '<div class="ticket-card">';
            echo '<p><strong>ID:</strong> ' . $ticket['ticket_id'] . '</p>';
            echo '<p><strong>Subject:</strong> ' . $ticket['subject'] . '</p>';
            echo '<p><strong>Status:</strong> ' . $ticket['status'] . '</p>';
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-info">No tickets found.</div>';
    }
}


}
