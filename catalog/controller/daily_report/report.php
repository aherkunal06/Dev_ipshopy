<?php
class ControllerDailyReportReport extends Controller {
    private $error = array();

    public function index() {
        
        $this->deleteOldReports();
        
        $this->load->language('daily_report/report');
        $this->load->model('daily_report/report');
        $this->load->model('vendor/vendor');

        $totals = $this->model_daily_report_report->TotalOrderStatus();
        $statuses = ['Processing', 'Delivered', 'Complete', 'Label Generated', 'Breached', 'Canceled', 'Reversed', 'Return In Transit', 'Return Delivered', 'RTO', 'RTO In Transit', 'RTO Delivered'];

        // Initialize vendor data array
        $vendor_data = [];

        // Fetch reports and group orders by vendor
        foreach ($statuses as $status) {
            $status_report = $this->model_daily_report_report->getOrdersByStatus($status);  // Pass the status argument

            // Group orders by vendor
            foreach ($status_report as $vendor) {
                $vendor_id = $vendor['vendor_id'];

                // Initialize vendor data if not set
                if (!isset($vendor_data[$vendor_id])) {
                    $vendor_data[$vendor_id] = [
                        'email' => $vendor['email'],
                        'telephone' => $vendor['telephone'],
                        'firstname' => $vendor['firstname'],
                        'order_counts' => [
                            'Processing' => 0,
                            'Delivered' => 0,
                            'Complete' => 0,
                            'Label Generated' => 0,
                            'Breached' => 0,
                            'Canceled' => 0,
                            'Reversed' => 0,
                            'Return In Transit' => 0,
                            'Return Delivered' => 0,
                            'RTO' => 0,
                            'RTO In Transit' => 0,
                            'RTO Delivered' => 0
                        ],
                        'processing_orders' => [],
                        'delivered_orders' => [],
                        'complete_orders' => [],
                        'label_orders' => [],
                        'breached_orders' => [],
                        'canceled_orders' => [],
                        'reversed_orders' => [],
                        'returnintransit_orders' => [],
                        'returndelivered_orders' => [],
                        'rto_orders' => [],
                        'rtointransit_orders' => [],
                        'rtodelivered_orders' => []
                    ];
                }

                // Increment the order count for each status for this vendor
                $vendor_data[$vendor_id]['order_counts'][$vendor['status']]++;

                // Add each order to the corresponding status section
                if ($vendor['status'] == 'Processing') {
                    $vendor_data[$vendor_id]['processing_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'Delivered') {
                    $vendor_data[$vendor_id]['delivered_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'Complete') {
                    $vendor_data[$vendor_id]['complete_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'Label Generated') {
                    $vendor_data[$vendor_id]['label_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'Breached') {
                    $vendor_data[$vendor_id]['breached_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'Canceled') {
                    $vendor_data[$vendor_id]['canceled_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'Reversed') {
                    $vendor_data[$vendor_id]['reversed_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'Return In Transit') {
                    $vendor_data[$vendor_id]['returnintransit_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'Return Delivered') {
                    $vendor_data[$vendor_id]['returndelivered_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'RTO') {
                    $vendor_data[$vendor_id]['rto_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'RTO In Transit') {
                    $vendor_data[$vendor_id]['rtointransit_orders'][] = $vendor;
                }
                if ($vendor['status'] == 'RTO Delivered') {
                    $vendor_data[$vendor_id]['rtodelivered_orders'][] = $vendor;
                }
            }
        }

        // Loop through each vendor and send an email with their orders
        foreach ($vendor_data as $vendor_id => $seller) {
            $vendor_email = $seller['email'];
            $vendor_telephone = $seller['telephone'];
            $vendor_firstname = $seller['firstname'];

            // Get the orders for each status category
            $processing_orders = isset($seller['processing_orders']) ? $seller['processing_orders'] : [];
            $delivered_orders = isset($seller['delivered_orders']) ? $seller['delivered_orders'] : [];
            $complete_orders = isset($seller['complete_orders']) ? $seller['complete_orders'] : [];
            $label_orders = isset($seller['label_orders']) ? $seller['label_orders'] : [];
            $breached_orders = isset($seller['breached_orders']) ? $seller['breached_orders'] : [];
            $canceled_orders = isset($seller['canceled_orders']) ? $seller['canceled_orders'] : [];
            $reversed_orders = isset($seller['reversed_orders']) ? $seller['reversed_orders'] : [];
            $returnintransit_orders = isset($seller['returnintransit_orders']) ? $seller['returnintransit_orders'] : [];
            $returndelivered_orders = isset($seller['returndelivered_orders']) ? $seller['returndelivered_orders'] : [];
            $rto_orders = isset($seller['rto_orders']) ? $seller['rto_orders'] : [];
            $rtointransit_orders = isset($seller['rtointransit_orders']) ? $seller['rtointransit_orders'] : [];
            $rtodelivered_orders = isset($seller['rtodelivered_orders']) ? $seller['rtodelivered_orders'] : [];

            // Prepare the data to be passed to the Twig template
            $data = [
                'text_greeting' => sprintf($this->language->get('text_greeting'), $vendor_firstname),
                'text_change' => $this->language->get('text_change'),
                'text_label' => $this->language->get('text_label'),
                'order_counts' => $seller['order_counts'],  // Pass vendor-specific order counts
            ];

            // Render the Twig template
            $html_message = $this->load->view('daily_report/email_report', $data);  // Render the email body

            // Generate Excel files for forward, backward, and RTO orders
            $filePaths = $this->generateExcelReport(
                $processing_orders, $delivered_orders, $complete_orders, $label_orders, $breached_orders, $canceled_orders,
                $reversed_orders, $returnintransit_orders, $returndelivered_orders, $rto_orders, $rtointransit_orders, $rtodelivered_orders
            );

            // Email Subject
            $subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), date('Y-m-d'));

            // Prepare Mail
            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($vendor_email);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
            $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
            $mail->setHtml($html_message); // ✅ Sending Email as HTML

            // Attach files if they contain data
            foreach ($filePaths as $filePath) {
                if ($filePath) { // Check if file exists
                    $mail->addAttachment($filePath);
                }
            }

            // Send the email
            $mail->send();
        }
        
        $this->session->data['success'] = 'Daily vendor order report emails have been sent successfully.';

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $this->response->redirect($this->url->link('daily_report/report', true));
    }

    private function generateExcelReport($processing_orders, $delivered_orders, $complete_orders, $label_orders, $breached_orders, $canceled_orders, $reversed_orders, $returnintransit_orders, $returndelivered_orders, $rto_orders, $rtointransit_orders, $rtodelivered_orders) {
        // Initialize file paths for each report type
        $filenameForward = $filenameBackward = $filenameRTO = null;
        $fileForward = $fileBackward = $fileRTO = null;

        // Write reports only if there is data for each category
        // Forward Orders
        if (!empty($processing_orders) || !empty($delivered_orders) || !empty($complete_orders) || !empty($label_orders) || !empty($breached_orders) || !empty($canceled_orders)) {
            $filenameForward = DIR_DAILY . 'forward_order_report_' . date('Y-m-d') . '.csv';
            $fileForward = fopen($filenameForward, 'w');
            // Write headers
            fputcsv($fileForward, ['Order ID', 'Product Name', 'Quantity', 'Total Amount', 'Shipping Method', 'AWB No.', 'Status', 'Date Added']);
            // Write data for forward orders
            foreach (array_merge($processing_orders, $delivered_orders, $complete_orders, $label_orders, $breached_orders, $canceled_orders) as $report) {
                fputcsv($fileForward, [
                    $report['order_id'],
                    $report['product_name'],
                    $report['quantity'],
                    'Rs.' . number_format($report['total'], 2),
                    $report['shipping_method'],
                    $report['awbno'],
                    $report['status'],
                    !empty($report['date_added']) ? date('d-m-Y', strtotime($report['date_added'])) : 'N/A'
                ]);
            }
            fclose($fileForward);
        }

        // Backward Orders
        if (!empty($reversed_orders) || !empty($returnintransit_orders) || !empty($returndelivered_orders)) {
            $filenameBackward = DIR_DAILY . 'backward_order_report_' . date('Y-m-d') . '.csv';
            $fileBackward = fopen($filenameBackward, 'w');
            // Write headers
            fputcsv($fileBackward, ['Order ID', 'Product Name', 'Quantity', 'Total Amount', 'Shipping Method', 'AWB No.', 'Status', 'Date Added']);
            // Write data for backward orders
            foreach (array_merge($reversed_orders, $returnintransit_orders, $returndelivered_orders) as $report) {
                fputcsv($fileBackward, [
                    $report['order_id'],
                    $report['product_name'],
                    $report['quantity'],
                    'Rs.' . number_format($report['total'], 2),
                    $report['shipping_method'],
                    $report['awbno'],
                    $report['status'],
                    !empty($report['date_added']) ? date('d-m-Y', strtotime($report['date_added'])) : 'N/A'
                ]);
            }
            fclose($fileBackward);
        }

        // RTO Orders
        if (!empty($rto_orders) || !empty($rtointransit_orders) || !empty($rtodelivered_orders)) {
            $filenameRTO = DIR_DAILY . 'rto_order_report_' . date('Y-m-d') . '.csv';
            $fileRTO = fopen($filenameRTO, 'w');
            // Write headers
            fputcsv($fileRTO, ['Order ID', 'Product Name', 'Quantity', 'Total Amount', 'Shipping Method', 'AWB No.', 'Status', 'Date Added']);
            // Write data for RTO orders
            foreach (array_merge($rto_orders, $rtointransit_orders, $rtodelivered_orders) as $report) {
                fputcsv($fileRTO, [
                    $report['order_id'],
                    $report['product_name'],
                    $report['quantity'],
                    'Rs.' . number_format($report['total'], 2),
                    $report['shipping_method'],
                    $report['awbno'],
                    $report['status'],
                    !empty($report['date_added']) ? date('d-m-Y', strtotime($report['date_added'])) : 'N/A'
                ]);
            }
            fclose($fileRTO);
        }

        // Return an array of non-empty file paths
        $filePaths = [];
        if ($filenameForward) $filePaths[] = $filenameForward;
        if ($filenameBackward) $filePaths[] = $filenameBackward;
        if ($filenameRTO) $filePaths[] = $filenameRTO;

        return $filePaths;  // Return the array of file paths
    } 
  
  
    private function deleteOldReports() {
        foreach (['forward', 'backward', 'RTO'] as $type) {
            foreach (glob(DIR_DAILY . "{$type}_order_report_*.csv") as $file) {
                if (filemtime($file) < time() - 86400) {
                    unlink($file);
                }
            }
        }
    }


    public function secureDownload() {
        // Delete old files (older than 24 hours)
        foreach (glob(DIR_DAILY . "forward_order_report_*.csv") as $old_forwardfile) {
            if (filemtime($old_forwardfile) < time() - 86400) {
                unlink($old_forwardfile);
            }
        }
        foreach (glob(DIR_DAILY . "backward_order_report_*.csv") as $old_backwardfile) {
            if (filemtime($old_backwardfile) < time() - 86400) {
                unlink($old_backwardfile);
            }
        }
        foreach (glob(DIR_DAILY . "RTO_order_report_*.csv") as $old_RTOfile) {
            if (filemtime($old_RTOfile) < time() - 86400) {
                unlink($old_RTOfile);
            }
        }

        $filesforward = glob(DIR_DAILY . "forward_order_report_*.csv");
        $filesbackward = glob(DIR_DAILY . "backward_order_report_*.csv");
        $filesRTO = glob(DIR_DAILY . "RTO_order_report_*.csv");

        if (!$filesforward || !$filesbackward || !$filesRTO) {
            $this->session->data['error'] = 'Error: No report files found.';
            $this->response->redirect($this->url->link('vendor/report', '', true));
        }

        $file_pathforward = end($filesforward);

        // Force Secure File Download
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . basename($file_pathforward) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_pathforward));
        readfile($file_pathforward);
        exit;
    }
    
}
