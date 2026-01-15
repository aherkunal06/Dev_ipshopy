<?php
class Controllervendorforgotten extends Controller {
	private $error = array();

	public function index() {
		if ($this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/dashboard', '', true));
		}

		$this->load->language('vendor/forgotten');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/vendor');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$code = token(40);

			$this->model_vendor_vendor->editPasswordemail($this->request->post['email'], $code);

			$data['text_greeting'] = sprintf($this->language->get('text_greeting'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$data['text_newpassword'] = $this->language->get('text_newpassword') . " : " . $code;
			$data['text_change'] = $this->language->get('text_change');
			$data['text_ip'] = $this->language->get('text_ip');
			
			$data['reset'] = $this->url->link('vendor/changepassword');
			$data['ip'] = $this->request->server['REMOTE_ADDR'];
			
// 			----------18-04-2025 Mail template change---------------

			// $subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

			// $message  = sprintf($this->language->get('text_greeting'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')) . "\n\n";
			// /* New Code 23-07-2019 */
			// $message .=$this->language->get('text_newpassword').":".$code."\n\n";
			// $message .= $this->language->get('text_change') . "\n\n";
			// /* New Code 23-07-2019 */
			// $message .= $this->url->link('vendor/changepassword') . "\n\n";
			// /* New Code 23-07-2019 */
			// $message .= sprintf($this->language->get('text_ip'), $this->request->server['REMOTE_ADDR']) . "\n\n";
			
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->request->post['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8'));
			$mail->setHTML($this->load->view('vendor/forgetpassword', $data));
			$mail->send();

			$this->session->data['success'] = $this->language->get('text_success');
	
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('vendor/dashboard', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_forgotten'),
			'href' => $this->url->link('vendor/forgotten', '', true)
		);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('vendor/forgotten', '', true);

		$data['back'] = $this->url->link('vendor/login', '', true);

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('vendor/forgotten', $data));
	}

	protected function validate() {
		if (!isset($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		} elseif (!$this->model_vendor_vendor->getVendorByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		}
		
		// Check if customer has been approved.
		$vender_info = $this->model_vendor_vendor->getVendorByEmail($this->request->post['email']);
		if ($vender_info && !$vender_info['status']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}

		return !$this->error;
	}
}
