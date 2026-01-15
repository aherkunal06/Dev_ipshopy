<?php
class ControllerVendorClaim extends Controller {
    
    public function index() {
        // $this->document->setTitle('Return Claims');

        $this->load->model('vendor/claim');

        // // Get all return claims
        // $return_claims = $this->model_vendor_claim->getAllReturnClaims();
            $data['filter_url'] = $this->url->link('vendor/claim', 'user_token=' . $this->session->data['user_token'], true);
            // $data['approval_url'] = $this->url->link('vendor/claim', 'user_token=' . $this->session->data['user_token'], true);
            $data['disapproval_url'] = $this->url->link('vendor/claim/disApproval', 'user_token=' . $this->session->data['user_token'], true);
        // index.php?route=vendor/claim/disApproval
        // Get filter status from URL if any
        $filter_status = isset($this->request->get['status']) ? $this->request->get['status'] : '';
  

        // ✅ Get all return claims
        $return_claims = $this->model_vendor_claim->getAllReturnClaims($filter_status);
        // $data['claim_counts'] = $this->model_vendor_claim->getClaimCounts($vendor_id);
        $data['claim_counts'] = $this->model_vendor_claim->getClaimCounts(); // or with real vendor_id
      
        // $comments = $this->model_vendor_claim->getLatestClaimApprovalComments();

        // Debugging line to check the comments array
    
        foreach ($return_claims as &$claim) {
            if($claim['is_approved']){
                
            // $approve_amount = $claim['claim_amount'] * (1 - ($claim['percentage'] / 100));
            $approve_amount = $claim['claim_amount'] * ($claim['percentage'] / 100);

            $claim['approve_amount'] =  round($approve_amount);
            }
        $claim_id = $claim['claim_id'];
        $claim['approval_comment'] = isset($comments[$claim_id]) ? $comments[$claim_id] : 'No comments';

        $claim['reply_link'] = $this->url->link('vendor/claim/reply_form', 'claim_id=' . $claim_id . '&user_token=' . $this->session->data['user_token'], true);
        }
        // Pass data to view AFTER processing
        $data['return_claims'] = $return_claims;

        // Load common controllers
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
         $data['user_token'] = $this->session->data['user_token'];
        // Now send view
        $this->response->setOutput($this->load->view('vendor/claim/claim', $data));

    }
    
    public function saveComment() {
        $this->load->model('vendor/claim');
    
        $claim_id = (int)$this->request->post['claim_id'];
        $comment = $this->db->escape($this->request->post['comment']);
    
        $this->db->query("INSERT INTO " . DB_PREFIX . "claim_approval_comments SET claim_id = '" . (int)$claim_id . "', comment = '" . $comment . "', comment_by = 'admin', date_added = NOW()");
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => 'Comment Saved']));
     }

    public function comment() {
        $this->load->model('vendor/claim');
    
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $claim_id = (int)$this->request->post['claim_id'];
            $comment = $this->request->post['comment'];
            $this->model_vendor_claim->addClaimComment($claim_id, $comment, 'admin');
            $this->response->redirect($this->url->link('vendor/claim/comment', 'claim_id=' . $claim_id . '&user_token=' . $this->session->data['user_token'], true));
        }
    
        $claim_id = (int)$this->request->get['claim_id'];
        $data['claim_id'] = $claim_id;
        $data['comments'] = $this->model_vendor_claim->getClaimComments($claim_id);
        $data['user_token'] = $this->session->data['user_token'];
    
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
    
        $this->response->setOutput($this->load->view('vendor/claim_comment', $data));
    }


    public function reply_form() {
        $this->load->language('vendor/claim');
        $this->document->setTitle('Reply to Comments');
        $this->load->model('vendor/claim');
    
        $claim_id = isset($this->request->get['claim_id']) ? (int)$this->request->get['claim_id'] : 0;
    
        if (!$claim_id) {
            $this->session->data['error'] = 'Missing claim_id';
            $this->response->redirect($this->url->link('vendor/claim', 'user_token=' . $this->session->data['user_token'], true));
            return;
        }
    
        // ✅ Fetch order_id based on claim_id
        $query_order = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "return_claim WHERE claim_id = '" . (int)$claim_id . "'");
        $order_id = $query_order->num_rows ? (int)$query_order->row['order_id'] : 0;
    
        // ✅ Handle POST
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $comment_text = $this->request->post['reply'];
            $admin_id = $this->user->getId();
            $vendor_id = 0;
            $comment_by = 'admin';
            $media_files = [];
    
            if (!empty($this->request->files['media']['name'][0])) {
                foreach ($this->request->files['media']['name'] as $index => $name) {
                    $tmp = $this->request->files['media']['tmp_name'][$index];
                    $filename = time() . '_' . basename($name);
                    move_uploaded_file($tmp, DIR_IMAGE . 'claim_uploads/' . $filename);
                    $media_files[] = 'claim_uploads/' . $filename;
                }
            }
    
            $this->db->query("INSERT INTO " . DB_PREFIX . "claim_comments SET 
                claim_id = '" . (int)$claim_id . "',
                order_id = '" . (int)$order_id . "',
                comment_text = '" . $this->db->escape($comment_text) . "',
                comment_by = '" . $comment_by . "',
                vendor_id = '" . (int)$vendor_id . "',
                admin_id = '" . (int)$admin_id . "',
                media = '" . $this->db->escape(json_encode($media_files)) . "',
                date_added = NOW()");
    
            $this->session->data['success'] = 'Reply submitted successfully.';
            $this->response->redirect($this->url->link('vendor/claim/reply_form', 'claim_id=' . $claim_id . '&user_token=' . $this->session->data['user_token'], true));
            return;
        }
    
        // Load existing comments
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "claim_comments WHERE claim_id = '" . (int)$claim_id . "' OR order_id = '" . (int)$order_id . "' ORDER BY date_added ASC");
    
        $comments = $query->rows;
        foreach ($comments as &$comment) {
            $comment['media'] = !empty($comment['media']) ? json_decode($comment['media'], true) : [];
        }
    
        // Load view data
        $data['claim_id'] = $claim_id;
        $data['order_id'] = $order_id;
        $data['comments'] = $comments;
        $data['action'] = $this->url->link('vendor/claim/reply_form', 'claim_id=' . $claim_id . '&user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('vendor/claim', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
    
        $this->response->setOutput($this->load->view('vendor/claim/reply_form', $data));
    
    }




    // public function approve() {
    //     if (isset($this->request->get['claim_id'])) {
    //         $claim_id = (int)$this->request->get['claim_id'];
    
    //       $this->db->query("UPDATE " . DB_PREFIX . "return_claim 
    //     SET 
    //         is_approved = 1, 
    //         claim_status_id = 42, 
    //         date_modified = NOW() 
    //     WHERE claim_id = '" . (int)$claim_id . "'");
    
    
    //         $this->session->data['success'] = 'Claim approved!';
    //     }
    
    //     $this->response->redirect($this->url->link('vendor/claim', 'user_token=' . $this->session->data['user_token'], true));
    // }


    public function viewClaimComments() {
        $this->load->model('vendor/claim');
        $claim_id = (int)$this->request->get['claim_id'];
    
        $data['claim_id'] = $claim_id;
        $data['comments'] = $this->model_vendor_claim->getClaimComments($claim_id);
        $data['action'] = $this->url->link('vendor/claim/submitClaimReply', 'claim_id=' . $claim_id . '&user_token=' . $this->session->data['user_token'], true);
        $this->response->setOutput($this->load->view('vendor/claim_reply_form', $data));
    }

    public function submitClaimReply() {
        $this->load->model('vendor/claim');
        $claim_id = (int)$this->request->get['claim_id'];
        $comment = $this->request->post['reply'];
        $vendor_id = $this->vendor->getId();
    
        $allowed = ['jpg','jpeg','png','gif','zip','mp4','pdf','doc','docx'];
        $uploaded_files = [];
    
        if (!empty($_FILES['media']['name'][0])) {
            foreach ($_FILES['media']['name'] as $key => $name) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $tmp = $_FILES['media']['tmp_name'][$key];
                if (!in_array($ext, $allowed)) continue;
    
                if ($_FILES['media']['size'][$key] <= 10 * 1024 * 1024) {
                    $filename = uniqid('claim_', true) . '.' . $ext;
                    move_uploaded_file($tmp, DIR_IMAGE . $filename);
                    $uploaded_files[] = $filename;
                }
            }
        }
    
        $this->model_vendor_claim->submitClaimReply($claim_id, $comment, $uploaded_files, $vendor_id);
        $this->session->data['success'] = 'Reply submitted successfully.';
        $this->response->redirect($this->url->link('vendor/claim', 'user_token=' . $this->session->data['user_token'], true));
    }


    public function toggleApproval() {
        if (isset($this->request->get['claim_id'])) {
            $claim_id = (int)$this->request->get['claim_id'];
    
            // Get current status
            $query = $this->db->query("SELECT is_approved FROM " . DB_PREFIX . "return_claim WHERE claim_id = '" . $claim_id . "'");
            if ($query->num_rows) {
                $current = (int)$query->row['is_approved'];
                $new_status = $current ? 0 : 1;
                $percentage = isset($this->request->get['percentage']) ? (float)$this->request->get['percentage'] : null;
            if($new_status == 1){
                $claim_status_id=42;
            }else{
                $claim_status_id=40;
                $percentage=null;
            }
                // Update status
                $this->db->query("UPDATE " . DB_PREFIX . "return_claim 
                SET 
                    is_approved = '" . (int)$new_status . "', 
                    claim_status_id = '" . (int)$claim_status_id . "', 
                    percentage = '" . (float)$percentage . "', 
                    date_modified = NOW() 
                WHERE claim_id = '" . (int)$claim_id . "'");
    
    
                $this->session->data['success'] = $new_status ? 'Claim approved.' : 'Claim disapproved.';
            } else {
                $this->session->data['error'] = 'Claim not found.';
            }
        } else {
            $this->session->data['error'] = 'Invalid request.';
        }
    
        $this->response->redirect($this->url->link('vendor/claim', 'user_token=' . $this->session->data['user_token'], true));
    }


    public function Approval() {
        if (isset($this->request->get['claim_id'])) {
            $claim_id = (int)$this->request->get['claim_id'];
    
            // Get current status
            $query = $this->db->query("SELECT claim_status_id FROM " . DB_PREFIX . "return_claim WHERE claim_id = '" . $claim_id . "'");
            if ($query->num_rows) {
                $currentStatus = (int)$query->row['claim_status_id'];
            
                $percentage = isset($this->request->get['percentage']) ? (float)$this->request->get['percentage'] : null;
                if($currentStatus == 40){
                    $claim_status_id=42;
                }
                
                // Update status
                $this->db->query("UPDATE " . DB_PREFIX . "return_claim 
                SET 
                    is_approved = 1 , 
                    claim_status_id = '" . (int)$claim_status_id . "', 
                    percentage = '" . (float)$percentage . "', 
                    date_modified = NOW() 
                WHERE claim_id = '" . (int)$claim_id . "'");
    
                $this->session->data['success'] = 'Claim approved.';
                
                // ✅ SEND EMAIL after status update
                $this->load->model('setting/setting');

                $from = $this->model_setting_setting->getSettingValue('config_email');

                if (!$from) {
                    $from = $this->config->get('config_email');
                }

                $mail = new Mail($this->config->get('config_mail_engine'));
        		$mail->parameter = $this->config->get('config_mail_parameter');
        		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
        		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
        		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $vendor_query = $this->db->query("
                    SELECT v.email, v.firstname, rc.order_id
                    FROM " . DB_PREFIX . "vendor v
                    LEFT JOIN " . DB_PREFIX . "return_claim rc ON v.vendor_id = rc.vendor_id
                    WHERE rc.claim_id = '" . (int)$claim_id . "'
                ");

                if ($vendor_query->num_rows) {
                    $vendor_email = $vendor_query->row['email'];
                    $vendor_name = $vendor_query->row['firstname'];
                    $order_id = $vendor_query->row['order_id'];

                    // Load language file
                    $this->load->language('sale/approval');

                    $subject = sprintf($this->language->get('text_subject'), $order_id);

                    $data['text_greeting']  = $this->language->get('text_greeting') . ' ' . $vendor_name . ',';
                    $data['text_start'] = $this->language->get('text_start') . '<br>';
                    $data['text_conversation'] = str_replace('[Order ID]', $order_id, $this->language->get('text_conversation')) . '';

                    $data['info']  = str_replace('[Order ID]', $order_id, $this->language->get('text_info') . '<br>');
                    $data['info']  = str_replace('[Claim ID]', $claim_id, $data['info'] . '<br>');
                    $data['info']  = str_replace('[Approved Percentage]', ($percentage !== null ? $percentage : '0'), $data['info'] . '<br>');

                    $data['text_review'] = $this->language->get('text_review') . '<br>';
                    $data['text_encourage'] = $this->language->get('text_encourage') . '<br>';
                    $data['text_valued'] = $this->language->get('text_valued') . '<br><br>';

                    // Render the Twig template
                    $html_message = $this->load->view('sale/approval', $data);  // Render the email body

                    $mail->setTo($vendor_email);
                    $mail->setFrom($from);
                    $mail->setSender($this->config->get('config_name'));
                    $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
                    $mail->setHtml($html_message);
                    $mail->send();
                }
            } else {
                $this->session->data['error'] = 'Claim not found.';
            }
        } else {
            $this->session->data['error'] = 'Invalid request.';
        }
    
        $this->response->redirect($this->url->link('vendor/claim', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    public function disApproval() {
        if (isset($this->request->get['claim_id'])) {
            $claim_id = (int)$this->request->get['claim_id'];
    
            // Get current status
            $query = $this->db->query("SELECT claim_status_id FROM " . DB_PREFIX . "return_claim WHERE claim_id = '" . $claim_id . "'");
            if ($query->num_rows) {
                $currentStatus = (int)$query->row['claim_status_id'];
               
                $percentage = isset($this->request->get['percentage']) ? (float)$this->request->get['percentage'] : null;
            if($currentStatus == 40){
                $claim_status_id=43;
            }
                // Update status
                $this->db->query("UPDATE " . DB_PREFIX . "return_claim 
                SET 
                    is_approved = 0, 
                    claim_status_id = '" . (int)$claim_status_id . "', 
                    percentage = ' ', 
                    date_modified = NOW() 
                WHERE claim_id = '" . (int)$claim_id . "'");
                
                $this->session->data['success'] = 'Claim disapproved.';
                
                // ✅ SEND EMAIL after status update
                $this->load->model('setting/setting');

                $from = $this->model_setting_setting->getSettingValue('config_email');

                if (!$from) {
                    $from = $this->config->get('config_email');
                }

                $mail = new Mail($this->config->get('config_mail_engine'));
        		$mail->parameter = $this->config->get('config_mail_parameter');
        		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
        		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
        		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $vendor_query = $this->db->query("
                    SELECT v.email, v.firstname, rc.order_id 
                    FROM " . DB_PREFIX . "vendor v
                    LEFT JOIN " . DB_PREFIX . "return_claim rc ON v.vendor_id = rc.vendor_id
                    WHERE rc.claim_id = '" . (int)$claim_id . "'
                ");

                if ($vendor_query->num_rows) {
                    $vendor_email = $vendor_query->row['email'];
                    $vendor_name = $vendor_query->row['firstname'];
                    $order_id = $vendor_query->row['order_id'];

                    // Load language file
                    $this->load->language('sale/disapproval');

                    $subject = sprintf($this->language->get('text_subject'), $order_id);

                    $data['text_greeting']  = $this->language->get('text_greeting') . ' ' . $vendor_name . ',';
                    $data['text_start'] = $this->language->get('text_start') . '<br>';
                    $data['text_conversation'] = str_replace('[Order ID]', $order_id, $this->language->get('text_conversation')) . '<br>';

                    $data['info']  = str_replace('[Order ID]', $order_id, $this->language->get('text_info') . '<br>');
                    $data['info']  = str_replace('[Claim ID]', $claim_id, $data['info'] . '<br>');

                    $data['text_review'] = $this->language->get('text_review') . '<br>';
                    $data['text_encourage'] = $this->language->get('text_encourage') . '<br>';
                    $data['text_valued'] = $this->language->get('text_valued') . '<br><br>';

                    // Render the Twig template
                    $html_message = $this->load->view('sale/disapproval', $data);  // Render the email body

                    $mail->setTo($vendor_email);
                    $mail->setFrom($from);
                    $mail->setSender($this->config->get('config_name'));
                    $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
                    $mail->setHtml($html_message);
                    $mail->send();
                }
            } else {
                $this->session->data['error'] = 'Claim not found.';
            }
        } else {
            $this->session->data['error'] = 'Invalid request.';
        }
    
        $this->response->redirect($this->url->link('vendor/claim', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function viewComments() {
        $this->load->model('vendor/claim');
    
        $claim_id = isset($this->request->get['claim_id']) ? (int)$this->request->get['claim_id'] : 0;
    
        $data['comments'] = $this->model_vendor_claim->getCommentsByClaimId($claim_id);
        $data['claim_id'] = $claim_id;
        $data['user_token'] = $this->session->data['user_token'];
    
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
    
        $this->response->setOutput($this->load->view('vendor/claim_comments', $data));
    }

}
