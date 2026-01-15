<?php
class ControllerVendorTicketList extends Controller {
    public function index() {
        $this->load->language('vendor/ticket_list');
        $this->load->model('vendor/ticket');
		$data['user_token'] = $this->session->data['user_token'];

        // // ✅ Get user token
        $user_token = $this->request->get['user_token'];
        

        $data['breadcrumbs'][] = array(
			'text' => $this->language->get('TicketList'),
			'href' => $this->url->link('vendor/ticket_list', 'user_token=' . $this->session->data['user_token'], true)
		);
    // --- Get filter from request --

        $filter_ticket_id = isset($this->request->get['filter_ticket_id']) ? $this->request->get['filter_ticket_id'] : '';

        $data['filter_ticket_id'] = $filter_ticket_id;

        // ✅ Setup filter URL string
        $url = '';
        if ($filter_ticket_id) {
            $url .= '&filter_ticket_id=' . urlencode($filter_ticket_id);
        }
        
        // ✅ Prepare filter data for model
        $data_filter = [
            'filter_ticket_id'     => $filter_ticket_id, 
        ];
       // ✅ Fetch data
     $data['results'] = $this->model_vendor_ticket->getTicketId($data_filter);

        $results = $this->model_vendor_ticket->getTickets($data_filter);
		$url = '';
        foreach ($results as $result) {
			$data['tickets'][] = array(
				'ticket_id'      => $result['ticket_id'],
				'vendor_id'      => $result['vendor_id'],
				'firstname'      => $result['firstname'],
                'lastname'      => $result['lastname'],
				'subject'        => $result['subject'],
                'ticket_category'=> $result['ticket_category'],
				'message'        => $result['message'],
				'image'          => $result['image'],
				'status'         => $result['status'],
				'date_added'     => $result['date_added'],
				'view'           => $this->url->link('vendor/ticket_list/ticket_view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $result['ticket_id'], true)
			);
		}
		  
        $this->document->setTitle('Seller Tickets');
        $data['filter_action'] = 'index.php?route=vendor/ticket_list&user_token=' . $user_token . $url;

        // Load header, footer, and sidebar
        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');

        // Load the view template
        $this->response->setOutput($this->load->view('vendor/ticket_list', $data));

    }




   //--view ticket Functinality-------------------------------------------------
    public function ticket_view() {
        $this->load->language('vendor/ticket_list');
        $this->load->model('vendor/ticket');

        $data['user_token'] = $this->session->data['user_token'];

        $data['cancel'] = $this->url->link('vendor/ticket_list', 'user_token=' . $this->session->data['user_token'], true);
        
        if (isset($this->request->get['ticket_id'])) {
            $ticket_id = (int)$this->request->get['ticket_id'];
        } else {
            $ticket_id = 0;
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (empty($this->request->post['message'])) {
                $this->session->data['error'] = 'Message is required';
                $this->response->redirect($this->url->link('vendor/ticket_list/ticket_view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $ticket_id, true));
            }

            if (!isset($this->request->post['status'])) {
                $this->session->data['error'] = 'Status is required';
                $this->response->redirect($this->url->link('vendor/ticket_list/ticket_view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $ticket_id, true));
            }

            // Handle file upload
            $image = '';
            if (!empty($_FILES['attachment']['name'])) {
                $filename = time() . '_' . basename($_FILES['attachment']['name']);
                move_uploaded_file($_FILES['attachment']['tmp_name'], DIR_IMAGE . $filename);
                $image = $filename;
            }

            // Add reply and update status
            $this->model_vendor_ticket->addReply($ticket_id, $this->request->post['message'], $image);
            $this->model_vendor_ticket->updateTicketStatus($ticket_id, $this->request->post['status']);

            // Set success message based on status
            if ($this->request->post['status'] === 'closed') {
                $this->session->data['success'] = 'Ticket has been closed successfully';
                $this->session->data['ticket_closed'] = true;
            } else {
                $this->session->data['success'] = 'Reply sent and status updated successfully';
                $this->session->data['ticket_closed'] = false;
            }
            
            // Redirect back to the same ticket view page
            $this->response->redirect($this->url->link('vendor/ticket_list/ticket_view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $ticket_id, true));
        }

        $data['ticket'] = $this->model_vendor_ticket->getTicket($ticket_id);
        $data['replies'] = $this->model_vendor_ticket->getReplies($ticket_id);
        $data['seller'] = $this->model_vendor_ticket->getsellerReplies($ticket_id);

        if (!$data['ticket']) {
            $this->response->redirect($this->url->link('vendor/ticket_list', 'user_token=' . $this->session->data['user_token'], true));
        }

        // Check if ticket is closed from session or current status
        if (isset($this->session->data['ticket_closed'])) {
            $data['ticket_closed'] = $this->session->data['ticket_closed'];
            unset($this->session->data['ticket_closed']);
        } else {
            $data['ticket_closed'] = ($data['ticket']['status'] === 'closed');
        }

        $data['reply_action'] = $this->url->link('vendor/ticket_list/ticket_view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $ticket_id, true);
        $data['back'] = $this->url->link('vendor/ticket_list', 'user_token=' . $this->session->data['user_token'], true);

        $this->document->setTitle('View Ticket');

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');

        $this->response->setOutput($this->load->view('vendor/ticket_view', $data));
    }
}
?>
