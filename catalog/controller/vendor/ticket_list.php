<?php
class ControllerVendorTicketList extends Controller {
	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		
		$this->load->model('vendor/ticket_list');
		$this->getTicketList();
	}

	 public function viewTickets() {
        if (!$this->vendor->isLogged()) {
            $this->response->redirect($this->url->link('vendor/login', '', true));
        }

        $this->load->language('vendor/ticket_list');
        $this->load->model('vendor/ticket');

        // Get only the current vendor's tickets
        $vendor_id = $this->vendor->getID();
        $data['tickets'] = $this->model_vendor_ticket->getVendorTicketsId($vendor_id);

        // Use correct OpenCart structure
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('vendor/ticket_list', $data));
    }

	
    public function getTicketList() {
        $this->load->language('vendor/ticket_list');
        $this->load->model('vendor/ticket_list');
		$vendor_id = $this->session->data['vendor_id']; // or however you retrieve the current vendor ID
        // $tickets = $this->model_vendor_ticket_list->getVendorTickets($vendor_id);

        $data['tickets'] = array();
        $results = $this->model_vendor_ticket_list->getVendorTickets($vendor_id);
		$url = '';
        foreach ($results as $result) {
			$data['tickets'][] = array(
				'ticket_id'      => $result['ticket_id'],
				'vendor_id'      => $result['vendor_id'],
				'firstname'      => $result['firstname'],
				'subject'        => $result['subject'],
				'message'        => $result['message'],
				'image'          => $result['image'],
				'status'         => $result['status'],
				'date_added'     =>$result['date_added'],
				'view'           => $this->url->link('vendor/ticket_list/ticket_view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $result['ticket_id'], true)
			);
		}
		
		// Fix: assign tickets array for statistics
        $tickets = $data['tickets'];

        // Additional data for ticket statistics
        $data['total_tickets'] = count($tickets);
        $data['open_tickets'] = 0;
        $data['in_progress_tickets'] = 0;
        $data['closed_tickets'] = 0;

        foreach ($tickets as $ticket) {
            if ($ticket['status'] == 'Open') {
                $data['open_tickets']++;
            } elseif ($ticket['status'] == 'pending') {
                $data['in_progress_tickets']++;
            } elseif ($ticket['status'] == 'Closed') {
                $data['closed_tickets']++;
            }
        }
        
        // Load header, footer, and sidebar
        
        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        // Load the view template
        $this->response->setOutput($this->load->view('vendor/ticket_list', $data));
    }

    public function ticket_view() {
        $this->load->language('vendor/ticket_list');
        $this->load->model('vendor/ticket_list');
    
        $data['user_token'] = $this->session->data['user_token'];
        
        $data['cancel'] = $this->url->link('vendor/ticket_list');
    
        if (isset($this->request->get['ticket_id'])) {
            $ticket_id = (int)$this->request->get['ticket_id'];
        } else {
            $ticket_id = 0;
        }
    
        // Correctly get the ticket details
        $data['ticket'] = $this->model_vendor_ticket_list->getTicket($ticket_id);

        $data['replies'] = $this->model_vendor_ticket_list->getReplies($ticket_id);

        $data['seller'] = $this->model_vendor_ticket_list->getsellerReplies($ticket_id);

        // Redirect if ticket not found
        if (!$data['ticket']) {
            $this->response->redirect($this->url->link('vendor/ticket_list', 'user_token=' . $this->session->data['user_token'], true));
        }
    
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!empty($_FILES['attachment']['name'])) {
                $filename = time() . '_' . basename($_FILES['attachment']['name']);
                move_uploaded_file($_FILES['attachment']['tmp_name'], DIR_IMAGE . $filename);
                $image = $filename;
            } else {
                $image = '';
            }
    
            // Add reply to the ticket
            $this->model_vendor_ticket_list->addReply($ticket_id, $this->request->post['message'], $image);
            // $this->model_vendor_ticket_list->addsellerReply($ticket_id, $this->request->post['message'], $image);

            $this->response->redirect($this->url->link('vendor/ticket_list/ticket_view', 'user_token=' . $this->session->data['user_token'] . '&ticket_id=' . $ticket_id, true));
        }
    
        $this->document->setTitle('View Ticket');
    
        // Load header, footer, and sidebar
        $data['header'] = $this->load->controller('vendor/header');
        $data['footer'] = $this->load->controller('vendor/footer');
        $data['column_left'] = $this->load->controller('vendor/column_left');
    
        // Load the view template
        $this->response->setOutput($this->load->view('vendor/ticket_view', $data));
    }
    

}
?>