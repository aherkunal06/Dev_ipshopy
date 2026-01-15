<?php
class ControllerAccountTicketView extends Controller {
    public function index() {
        $this->view();
    }

    public function view() {
        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->model('account/ticket');
        $ticket_id = isset($this->request->get['ticket_id']) ? (int)$this->request->get['ticket_id'] : 0;
        $customer_id = $this->customer->getId();
        $ticket = $this->model_account_ticket->getTicket($ticket_id, $customer_id);

        if (!$ticket) {
            $this->response->redirect($this->url->link('account/ticket_list', '', true));
        }

        $data['ticket'] = $ticket;
        $data['replies'] = $this->model_account_ticket->getReplies($ticket_id);
        $data['reply_action'] = $this->url->link('account/ticket_view/reply', 'ticket_id=' . $ticket_id, true);

        $ticket_info = $this->model_account_ticket->getTicket($ticket_id, $this->customer->getId());
        $data['status'] = isset($ticket_info['status']) ? $ticket_info['status'] : '';

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

    $data['column_left_account'] = $this->load->controller('account/column_left_account');
        $this->response->setOutput($this->load->view('account/ticket_view', $data));
    }

    public function reply() {
        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/login', '', true));
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['message'])) {
            $this->load->model('account/ticket');
            $ticket_id = isset($this->request->get['ticket_id']) ? (int)$this->request->get['ticket_id'] : 0;
            $message = $this->request->post['message'];
            $file = '';

            if (!empty($_FILES['file']['name'])) {
                $upload_dir = DIR_IMAGE . 'ticket_uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file = uniqid() . '_' . basename($_FILES['file']['name']);
                move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $file);
            }

            $this->model_account_ticket->addReply($ticket_id, $message, $file, 'customer');
        }
        $this->response->redirect($this->url->link('account/ticket_view', 'ticket_id=' . $this->request->get['ticket_id'], true));
    }
}
?>
<form action="index.php?route=account/ticket_view/reply&ticket_id=<?php echo $ticket_id; ?>" method="post" enctype="multipart/form-data">
