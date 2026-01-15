<?php
class ControllerVendorClaimApprovalComment extends Controller {
    public function index() {
        $this->document->setTitle('Claim Approval Comments');

        $this->load->model('vendor/claim/returnClaim');

        $claim_id = isset($this->request->get['claim_id']) ? (int)$this->request->get['claim_id'] : 0;

        // Fetch claim details to validate
        $claim_info = $this->model_vendor_claim_returnClaim->getClaimById($claim_id);
        if (!$claim_info) {
            $this->session->data['error'] = 'Invalid claim ID.';
            $this->response->redirect($this->url->link('vendor/claim/claim_list', '', true));
        }

        // Handle POST submission
        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            $vendor_id = $this->vendor->getId();
            $comment_text = $this->request->post['reply'];
            $media_files = [];

            if (!empty($this->request->files['media']['name'][0])) {
                foreach ($this->request->files['media']['name'] as $index => $name) {
                    $tmp = $this->request->files['media']['tmp_name'][$index];
                    $filename = 'vendor_' . time() . '_' . basename($name);
                    move_uploaded_file($tmp, DIR_IMAGE . 'claim_uploads/' . $filename);
                    $media_files[] = 'claim_uploads/' . $filename;
                }
            }

            $this->db->query("INSERT INTO " . DB_PREFIX . "claim_comments SET 
                claim_id = '" . (int)$claim_id . "',
                order_id = '" . (int)$claim_info['order_id'] . "',
                comment_text = '" . $this->db->escape($comment_text) . "',
                comment_by = 'vendor',
                vendor_id = '" . (int)$vendor_id . "',
                admin_id = 0,
                media = '" . $this->db->escape(json_encode($media_files)) . "',
                date_added = NOW()");

            $this->session->data['success'] = 'Reply submitted!';
            $this->response->redirect($this->url->link('vendor/claim/approval_comment', 'claim_id=' . $claim_id, true));
        }

        // Fetch comment history
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "claim_comments WHERE claim_id = '" . (int)$claim_id . "' ORDER BY date_added ASC");
        $comments = $query->rows;

        foreach ($comments as &$comment) {
            $comment['media'] = !empty($comment['media']) ? json_decode($comment['media'], true) : [];
        }

        $data['comments'] = $comments;
        $data['claim_id'] = $claim_id;
        $data['action'] = $this->url->link('vendor/claim/approval_comment', 'claim_id=' . $claim_id, true);
        $data['cancel'] = $this->url->link('vendor/claim/claim_list', '', true);

        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        $this->response->setOutput($this->load->view('vendor/claim/approval_comment', $data));
    }
}
