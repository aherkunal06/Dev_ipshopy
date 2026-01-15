<?php

class ControllerVendorFaq extends Controller {
 public function index() {
    $this->load->language('vendor/faq');
    $this->load->model('vendor/faq');

    // Add these two lines in index() after loading model
$data['count_answered'] = $this->model_vendor_faq->countFaqsByStatus(-1, 'answered');
$data['count_unanswered'] = $this->model_vendor_faq->countFaqsByStatus(-1, 'unanswered');


    $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
    $limit = 20;
    $start = ($page - 1) * $limit;

    $status = isset($this->request->get['filter_status']) ? (int)$this->request->get['filter_status'] : -1;

    // ✅ NEW: answer filter
    $filter_answer = isset($this->request->get['filter_answer']) ? $this->request->get['filter_answer'] : '';

    $data['filter_status'] = $status;
    $data['filter_answer'] = $filter_answer;

    // Count total
    $total_faqs = $this->model_vendor_faq->countFaqsByStatus($status, $filter_answer);

    $data['faqs'] = [];
    if ($start < $total_faqs) {
        $data['faqs'] = $this->model_vendor_faq->getAllProductFaqs($status, $start, $limit, $filter_answer);

        // pagination
        $this->load->library('pagination');
        $pagination = new Pagination();
        $pagination->total = $total_faqs;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('vendor/faq', 'user_token=' . $this->session->data['user_token'] . '&filter_status=' . $status . '&filter_answer=' . $filter_answer . '&page={page}', true);
        $data['pagination'] = $pagination->render();
    } else {
        $data['pagination'] = '';
    }

    // Count boxes
    $data['count_all'] = $this->model_vendor_faq->countFaqsByStatus(-1);
    $data['count_approved'] = $this->model_vendor_faq->countFaqsByStatus(1);
    $data['count_disapproved'] = $this->model_vendor_faq->countFaqsByStatus(0);

    // ✅ URLs with answer filter
    $data['url_all'] = $this->url->link('vendor/faq', 'user_token=' . $this->session->data['user_token'], true);
    $data['url_approved'] = $this->url->link('vendor/faq', 'user_token=' . $this->session->data['user_token'] . '&filter_status=1', true);
    $data['url_disapproved'] = $this->url->link('vendor/faq', 'user_token=' . $this->session->data['user_token'] . '&filter_status=0', true);
    $data['url_answered'] = $this->url->link('vendor/faq', 'user_token=' . $this->session->data['user_token'] . '&filter_answer=answered', true);
    $data['url_unanswered'] = $this->url->link('vendor/faq', 'user_token=' . $this->session->data['user_token'] . '&filter_answer=unanswered', true);

    $data['action'] = $this->url->link('vendor/faq/save', 'user_token=' . $this->session->data['user_token'], true);
    $data['user_token'] = $this->session->data['user_token'];

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('vendor/faq', $data));
}


    public function save() {
        $this->load->model('vendor/faq');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $faq_id = (int)$this->request->post['product_faq_id'];
            $answer = $this->request->post['answer'];

            $this->model_vendor_faq->saveAdminAnswer($faq_id, $answer);
        }

        $this->response->redirect($this->url->link('vendor/faq', 'user_token=' . $this->session->data['user_token'], true));
    }

  public function changeStatus() {
    $this->load->model('vendor/faq');

    $faq_id = (int)$this->request->get['faq_id'];
    $status = (int)$this->request->get['status'];
    $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
    $filter_status = isset($this->request->get['filter_status']) ? (int)$this->request->get['filter_status'] : -1;

    $this->model_vendor_faq->updateFaqStatus($faq_id, $status);

    $this->response->redirect(
        $this->url->link(
            'vendor/faq',
            'user_token=' . $this->session->data['user_token'] . '&page=' . $page . '&filter_status=' . $filter_status,
            true
        )
    );
}

}
