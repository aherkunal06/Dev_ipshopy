<?php
class ControllerVendorOrderInvoice extends Controller {

    public function onOrderComplete(&$route, &$args, &$output) {
        $order_id = (int)$args[0];
        $order_status_id = isset($args[1]) ? (int)$args[1] : 0;

        // ✅ Check status ID directly as scalar
        if ($order_status_id !== 5) {
            return;
        }

        $this->load->model('vendor/order_invoice');
        $this->load->language('vendor/order_invoice');

        $vendors = $this->model_vendor_order_invoice->getVendorsWithInvoices($order_id);

        foreach ($vendors as $vendor) {

            $invoice_file = DIR_INVOICE . basename($vendor['invoice_path']);
            $vendor_email = $vendor['email'];
            $vendor_id = $vendor['vendor_id'];
            $vendor_name = $vendor['firstname'];

            // Email Subject
            $subject = sprintf($this->language->get('mail_subject'), $order_id);

            $data['text_greeting'] = sprintf($this->language->get('text_greeting'), $vendor_name);
		    $data['text_change'] = $this->language->get('text_change');
		    $data['text_info'] = sprintf($this->language->get('text_info'), $order_id); 
		    $data['text_assist'] = $this->language->get('text_assist');
		    $data['text_thanks'] = $this->language->get('text_thanks');
		    $data['text_regards'] = $this->language->get('text_regards');

            // Render the Twig template
            $html_message = $this->load->view('vendor/invoice_mail_template', $data);  // Render the email body

            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($vendor_email);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender($this->config->get('config_name'));
            $mail->setSubject($subject);
            $mail->setHtml($html_message);

            if (!empty($invoice_file) && is_file($invoice_file)) {
                $mail->addAttachment($invoice_file);
            } else {
                continue;
            }

            try {
                $mail->send();
                $timestamp = date('Y-m-d H:i:s');
                file_put_contents(DIR_LOGS . 'vendor_invoice_mail.log', "[$timestamp] ✅ Invoice mailed for order $order_id to vendor $vendor_id ($vendor_email)\n", FILE_APPEND);
            } catch (Exception $e) {
                $timestamp = date('Y-m-d H:i:s');
                file_put_contents(DIR_LOGS . 'vendor_invoice_mail.log', "[$timestamp] ❌ Mail send error for order $order_id, vendor $vendor_id: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }
    }

}