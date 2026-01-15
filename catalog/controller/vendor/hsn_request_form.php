<?php
class Controllervendorhsnrequestform extends Controller
{
    private $error = [];

    public function index()
    {
        $this->load->language('vendor/hsn_request_form');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('vendor/hsn_request_form');

        // Pass form action
        $data['action'] = $this->url->link('vendor/hsn_request_form/save', 'user_token=' . $this->session->data['user_token'], true);

        // Language variables
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_form'] = $this->language->get('text_form');
        $data['entry_hsn_code'] = $this->language->get('entry_hsn_code');
        $data['entry_description'] = $this->language->get('entry_description');
        $data['entry_gst_rate'] = $this->language->get('entry_gst_rate');
        $data['button_submit'] = $this->language->get('button_submit');
        $data['button_reset'] = $this->language->get('button_reset');

        // Errors from session if redirected back
        $errors = $this->session->data['hsn_errors'] ?? [];

        $data['error_warning']     = $errors['warning'] ?? '';
        $data['error_description'] = $errors['description'] ?? '';
        $data['error_gst_rate']    = $errors['gst_rate'] ?? '';

        // Clear session errors once read
        // unset($this->session->data['hsn_errors']); 
        
        // Cancel link
        $data['cancel'] = $this->url->link('vendor/hsn_code_list', 'user_token=' . $this->session->data['user_token'], true);
        
        // Layout
        $data['header']       = $this->load->controller('vendor/header');
        $data['column_left']  = $this->load->controller('vendor/column_left');
        $data['footer']       = $this->load->controller('vendor/footer');
        $data['user_token']   = $this->session->data['user_token'];
        
        // var_dump($this->session->data['hsn_errors']['hsn_code'] ?? ''); // Debugging line
        $data['error_hsn_code']    = $this->session->data['hsn_errors']['hsn_code'] ?? '';
        $this->response->setOutput($this->load->view('vendor/hsn_request_form', $data));
    }

    // public function save()
    // {
    //     $this->load->language('vendor/hsn_request_form');
    //     $this->load->model('vendor/hsn_request_form');

    //     $json = [];

    //     if ($this->request->server['REQUEST_METHOD'] == 'POST') {
    //         if ($this->validate()) {
    //             $this->model_vendor_hsn_request_form->addHsnRequest($this->request->post);
    //             $json['success'] = 'HSN request submitted successfully!';
    //         } else {
    //             $json['error'] = $this->error;
    //             $this->session->data['hsn_errors'] = $this->error;
    //         }
    //     }

    //     $this->response->addHeader('Content-Type: application/json');
    //     $this->response->setOutput(json_encode($json));

    // }

    public function save() {
    $this->load->language('vendor/hsn_request_form');
    $this->load->model('vendor/hsn_request_form');

    $json = [];

    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
        // Clear previous error
        $this->error = [];

        if (empty($this->request->post['hsn_code'])) {
            $this->error['hsn_code'] = 'HSN Code is required!';
        }

        if (empty($this->request->post['description'])) {
            $this->error['description'] = 'Description is required!';
        }

        if (!isset($this->request->post['gst_rate']) || $this->request->post['gst_rate'] === '') {
            $this->error['gst_rate'] = 'GST Rate is required!';
        } else {
            $gst = $this->request->post['gst_rate'];
            if (!is_numeric($gst) || $gst < 0 || $gst > 100) {
                $this->error['gst_rate'] = 'GST Rate must be between 0 and 100!';
            }
        }

        if (!$this->error) {
            // Valid input — save it
            $this->model_vendor_hsn_request_form->addHsnRequest($this->request->post);
            $json['success'] = 'HSN request submitted successfully!';
        } else {
            // Return error object
            $this->error['warning'] = 'Please fix the errors!';
            $json['error'] = $this->error;
        }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}


    protected function validate()
    {
        $this->error = []; // reset

        if (empty($this->request->post['hsn_code'])) {
            $this->error['hsn_code'] = 'HSN Code is required!';
        }

        if (empty($this->request->post['description'])) {
            $this->error['description'] = 'Description is required!';
        }

        if (!isset($this->request->post['gst_rate']) || $this->request->post['gst_rate'] === '') {
            $this->error['gst_rate'] = 'GST Rate is required!';
        } else {
            $gst = $this->request->post['gst_rate'];
            if (!is_numeric($gst) || $gst < 0 || $gst > 100) {
                $this->error['gst_rate'] = 'GST Rate must be between 0 and 100!';
            }
        }

        if ($this->error) {
            $this->error['warning'] = 'Please fix the errors!';
        }

        return !$this->error;
    }
}
