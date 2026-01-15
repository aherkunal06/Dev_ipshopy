<?php
class ControllerVendorFaq extends Controller {
    public function index() {
        $this->load->language('vendor/faq');
        $this->load->model('vendor/faq');

        $vendor_id = $this->vendor->getId();

        // Get current page from request
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        
        $limit = 20;
        $start = ($page - 1) * $limit;

        // Get all FAQs for the vendor
        $data['faqs'] = $this->model_vendor_faq->getVendorProductFaqs($vendor_id, $start, $limit);
        $faq_total = $this->model_vendor_faq->getTotalVendorProductFaqs($vendor_id);

        // Pagination setup
        $pagination = new Pagination();
        $pagination->total = $faq_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('vendor/faq', 'page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            ($faq_total) ? (($page - 1) * $limit) + 1 : 0,
            ((($page - 1) * $limit) > ($faq_total - $limit)) ? $faq_total : ((($page - 1) * $limit) + $limit),
            $faq_total,
            ceil($faq_total / $limit)
        );

        // Define action URL for the form
        $data['action'] = $this->url->link('vendor/faq/save', '', true);

        // Load header, footer, and column left templates
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');
        $data['header'] = $this->load->controller('vendor/header');



        

        // Output the view template for the FAQ page
        $this->response->setOutput($this->load->view('vendor/faq', $data));
    }

    public function save() {
        $this->load->model('vendor/faq');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $faq_id = (int)$this->request->post['product_faq_id'];
            $answer = $this->request->post['answer'];
            $product_id = (int)$this->request->post['product_id'];
            $vendor_id = (int)$this->vendor->getId();

            $this->model_vendor_faq->updateAnswerForVendor($faq_id, $vendor_id, $product_id, $answer);
        }

        $this->response->redirect($this->url->link('vendor/faq', '', true));
    }
}
