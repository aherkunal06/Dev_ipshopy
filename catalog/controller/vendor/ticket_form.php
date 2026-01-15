<?php
//new file--------------------------------------
class ControllerVendorTicketForm extends Controller {
    private $error = array();
	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		$this->load->model('vendor/ticket');

		$this->getTicketForm();
	}
	
    public function getTicketForm() {
        $this->load->language('vendor/ticket_form');  

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {   
            $this->model_vendor_ticket->addTicket($this->request->post, $this->vendor->getID());
            $this->response->redirect($this->url->link('vendor/ticket_form', '', true));
            $this->session->data['success'] = 'Ticket submitted successfully!';
        }
        
        $this->document->setTitle('Raise Ticket');
        
        $data['cancel'] = $this->url->link('vendor/dashboard');
        $data['action'] = $this->url->link('vendor/ticket_form', '', true);
        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');
        $this->response->setOutput($this->load->view('vendor/ticket_form', $data));
    }

     protected function validate() {
         if (empty($this->request->post['subject']) || empty($this->request->post['message'])) {
             $this->error['warning'] = 'Both subject and message are required!';
         }
         $file = $this->request->files['file'];


         return !$this->error;
     }   
}
?>
