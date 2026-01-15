<?php
class ControllerInformationCareers extends Controller {
    public function index() {
        $this->load->language('information/careers');
        
        // Default data for the page
        $data['heading_title'] = $this->language->get('heading_title');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/careers', $data));
    }
}
?>
