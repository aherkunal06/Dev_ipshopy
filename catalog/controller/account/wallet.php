<?php
class ControllerAccountWallet extends Controller {
    public function index() {
        if (!$this->customer->isLogged()) {
            return $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/wallet');
        

       $this->load->model('account/wallet');

        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('Account'),
            'href' => $this->url->link('account/account', '', true)
        ];
        
        $data['breadcrumbs'][] = [
            'text' => 'My Wallet',
            'href' => $this->url->link('account/wallet', '', true)
        ];

    

$wallet = $this->model_account_wallet->getWalletByCustomerId($this->customer->getId());
// $data['wallet_balance'] = $wallet ? (float)$wallet['wallet_balance'] : 0;

        $data['transactions'] = $this->model_account_wallet->getWalletTransactions($this->customer->getId());
        $data['download_pdf_url'] = $this->url->link('account/wallet/downloadPdf', '', true);
        $data['withdraw_action'] = $this->url->link('account/wallet/withdraw', '', true);


$wallet_info = $this->model_account_wallet->getWalletByCustomerId($this->customer->getId());

$data['default_upi_id'] = !empty($wallet_info['default_upi_id']) ? $wallet_info['default_upi_id'] : '';

$data['wallet_balance'] = $wallet_info['balance'] ?? 0;
$data['default_upi_id'] = $wallet_info['default_upi_id'] ?? '';


if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['amount'])) {
    $customer_id = $this->customer->getId();
    $amount = (float)$this->request->post['amount'];
    $reason = $this->request->post['reason'] ?? '';
    $upi_id = $this->request->post['upi_id'] ?? '';

    $this->load->model('account/wallet');

    if ($amount > 0 && $amount <= 100000) {
        if (!empty($upi_id)) {
            // Debit via UPI
            $this->model_account_wallet->addTransaction($customer_id, $amount, 'debit', $reason ?: 'UPI Transfer', $upi_id);
            $this->session->data['success'] = 'Wallet debited successfully using UPI!';
        } else {
            // Credit (add money)
            $this->model_account_wallet->addTransaction($customer_id, $amount, 'credit', $reason ?: 'Manual top-up from customer');
            $this->session->data['success'] = 'Money added to wallet successfully!';
        }
    } else {
        $this->session->data['error'] = 'Invalid amount entered.';
    }

    $this->response->redirect($this->url->link('account/wallet', '', true));
}

        $data['withdraw_error'] = $this->session->data['withdraw_error'] ?? '';
        unset($this->session->data['withdraw_error']);
        
        $data['withdraw_success'] = $this->session->data['withdraw_success'] ?? '';
        unset($this->session->data['withdraw_success']);
        
        $data['action_withdraw_wallet'] = $this->url->link('account/wallet/withdraw', '', true);
        

        $data['back'] = $this->url->link('account/account', '', true);
        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');

        // Show referral earnings as in the referral customers table
        $this->load->model('ipoffer/offer');
        $referral_info = $this->model_ipoffer_offer->getCustomerReferral($this->customer->getId());
        $data['referral_earnings'] = isset($referral_info['earned']) ? ceil($referral_info['earned']) : 0;

        // Set wallet balance to referral earnings
        $data['wallet_balance'] = $data['referral_earnings'];
        $data['action_purchase_wallet'] = $this->url->link('common/home', '', true);

        $this->response->setOutput($this->load->view('account/wallet', $data));
    }
    
    
    // public function withdraw() {
    //     $this->load->model('account/wallet');
    
    //     if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['amount'])) {
    //         $customer_id = $this->customer->getId();
    //         $amount = (float)$this->request->post['amount'];
    //         $reason = $this->request->post['reason'] ?? 'Withdrawal';
    
    //         $wallet = $this->model_account_wallet->getWalletBalance($customer_id);
    
    //         if ($amount > 0 && $amount <= $wallet) {
    //             $this->model_account_wallet->addTransaction($customer_id, $amount, 'debit', $reason);
    //             $this->session->data['withdraw_success'] = 'Withdrawal successful.';
    //         } else {
    //             $this->session->data['withdraw_error'] = 'Insufficient balance or invalid amount.';
    //         }
    //     }
    
    //     $this->response->redirect($this->url->link('account/wallet', '', true));
    // }
    

    public function downloadPdf() {
        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('account/login', '', true));
        }
    
        $this->load->model('account/wallet');
    
        require_once(DIR_SYSTEM . 'library/tcpdf/tcpdf.php');
    
        $transactions = $this->model_account_wallet->getWalletTransactions($this->customer->getId());
    
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $html = '<h2>Wallet Transaction History</h2><br><table border="1" cellpadding="5">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead><tbody>';
    
         foreach ($transactions as $txn) {
         $amount = $txn['type'] == 'credit' ? '+ Rs. ' . $txn['amount'] : '- Rs. ' . $txn['amount'];

            $color = $txn['type'] == 'credit' ? 'green' : 'red';
    
            $html .= '<tr>
                        <td>' . $txn['date_added'] . '</td>
                        <td>' . ucfirst($txn['type']) . '</td>
                        <td style="color:' . $color . ';">' . $amount . '</td>
                        <td>' . $txn['reason'] . '</td>
                        <td>' . $txn['status'] . '</td>
                      </tr>';
        }
    
        $html .= '</tbody></table>';
    
        $pdf->writeHTML($html, true, false, true, false, '');
    
        $pdf->Output('wallet_transactions.pdf', 'D'); // D = force download
    }
    

 public function addmoney() {
    if (!$this->customer->isLogged()) {
        $this->response->redirect($this->url->link('account/login', '', true));
    }

    $this->load->language('account/wallet');
    $this->document->setTitle('Add Money');
    $this->load->model('account/wallet');


    $data['breadcrumbs'] = array(
        [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ],
        [
            'text' => 'Wallet',
            'href' => $this->url->link('account/wallet')
        ],
        [
            'text' => 'Payment options',
            'href' => $this->url->link('account/wallet/paymentoptions')
        ],
         [
            'text' => 'Add money',
            'href' => $this->url->link('wallet/paymentoptions/addmoney')
        ]
    );




    $data['amount'] = isset($this->request->get['amount']) ? (float)$this->request->get['amount'] : 0;
    $data['amount'] = isset($this->request->get['amount']) ? (float)$this->request->get['amount'] : 0;


    // Get current default UPI ID
  $this->load->model('account/wallet');

$customer_id = $this->customer->getId();

// Fetch wallet info (with default_upi_id)
$wallet = $this->model_account_wallet->getWalletByCustomerId($customer_id);

// If form is submitted
if ($this->request->server['REQUEST_METHOD'] == 'POST') {
    $amount = (float)$this->request->post['amount'];
    $upi_id = $this->request->post['upi_id'];
    $reason = $this->request->post['reason'];

    // Save default UPI if not already set
    if (!$wallet || !$wallet['default_upi_id']) {
        $this->model_account_wallet->updateDefaultUpiId($customer_id, $upi_id);
    }

    // Add transaction (custom UPI may be different)
    $this->model_account_wallet->addTransaction($customer_id, $amount, 'credit', $reason, $upi_id);

    $this->response->redirect($this->url->link('account/wallet', '', true));
}

$data['default_upi_id'] = $wallet ? $wallet['default_upi_id'] : '';

if (isset($this->request->get['amount'])) {
    $data['amount'] = (float)$this->request->get['amount'];
} else {
    $data['amount'] = '';
}
// Get the type from GET, default to 'add'
    $data['type'] = isset($this->request->get['type']) ? $this->request->get['type'] : 'add';


    // Get default UPI ID
    $wallet = $this->model_account_wallet->getWalletByCustomerId($this->customer->getId());
    $data['default_upi_id'] = isset($wallet['default_upi_id']) ? $wallet['default_upi_id'] : '';

    $data['action'] = $this->url->link('account/wallet/confirm_addmoney', '', true); // Your form POST route
    $data['continue'] = $this->url->link('account/wallet', '', true);

    // Load standard layout components
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('account/add_money', $data));
}



public function confirm_addmoney() {
    if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['amount'])) {
        $customer_id = $this->customer->getId();
        $amount = (float)$this->request->post['amount'];
        $upi_id = $this->request->post['upi_id'];
        $reason = $this->request->post['reason'];

        if ($amount > 0 && !empty($upi_id)) {
            $this->load->model('account/wallet');
            $this->model_account_wallet->addTransaction($customer_id, $amount, 'credit', $reason, $upi_id);

            $this->session->data['success'] = 'Money added successfully!';
        } else {
            $this->session->data['error'] = 'Invalid amount or UPI ID!';
        }
 }

    $this->response->redirect($this->url->link('account/wallet', '', true));
}




public function paymentoptions() {
    if (!$this->customer->isLogged()) {
        $this->response->redirect($this->url->link('account/login', '', true));
    }

    $this->load->language('account/wallet');
    $this->document->setTitle('Wallet Payment Options');

    $data['breadcrumbs'] = array(
        [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ],
        [
            'text' => 'Wallet',
            'href' => $this->url->link('account/wallet')
        ],
        [
            'text' => 'Payment Options',
            'href' => $this->url->link('account/wallet/paymentoptions')
        ]
    );

$type = isset($this->request->get['type']) ? $this->request->get['type'] : 'add';
$data['type'] = $type;

$data['amount'] = isset($this->request->get['amount']) ? (float)$this->request->get['amount'] : 0.0;

if ($type == 'add') {
    $data['payment_heading'] = 'Add Amount to Wallet';
} else {
    $data['payment_heading'] = 'Withdraw Amount from Wallet';
}




    

    $data['heading_title'] = 'Choose Payment Method';
    $data['continue'] = $this->url->link('account/wallet', '', true);

    $data['amount'] = isset($this->request->get['amount']) ? (float)$this->request->get['amount'] : 0.00;

    $data['action'] = $this->url->link('account/wallet/processpayment', '', true);

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('account/payment_options', $data));
}

public function withdraw() {
    if (!$this->customer->isLogged()) {
        $this->response->redirect($this->url->link('account/login', '', true));
    }

    $this->load->language('account/wallet');
    $this->document->setTitle('Withdraw Money');
    $this->load->model('account/wallet');

    $data['breadcrumbs'] = array(
        [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ],
        [
            'text' => 'Wallet',
            'href' => $this->url->link('account/wallet')
        ],
        [
            'text' => 'Payment options',
            'href' => $this->url->link('account/wallet/paymentoptions')
        ],
        [
            'text' => 'Withdraw',
            'href' => $this->url->link('account/wallet/withdraw')
        ]
    );

     $wallet = $this->model_account_wallet->getWalletByCustomerId($customer_id);
            $balance = isset($wallet['balance']) ? (float)$wallet['balance'] : 0;

    $customer_id = $this->customer->getId();
    $wallet = $this->model_account_wallet->getWalletByCustomerId($customer_id);
    $data['default_upi_id'] = isset($wallet['default_upi_id']) ? $wallet['default_upi_id'] : '';

    $data['amount'] = isset($this->request->get['amount']) ? (float)$this->request->get['amount'] : 0;

    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
        $amount = (float)$this->request->post['amount'];
        $upi_id = $this->request->post['upi_id'];
        $reason = $this->request->post['reason'];

        // Save default UPI if not already set
        if (!$wallet || !$wallet['default_upi_id']) {
            $this->model_account_wallet->updateDefaultUpiId($customer_id, $upi_id);
        }

        // Add withdrawal transaction (debit)
        $this->model_account_wallet->addTransaction($customer_id, $amount, 'debit', $reason, $upi_id);

        $this->response->redirect($this->url->link('account/wallet', '', true));
    }

    $data['action'] = $this->url->link('account/wallet/confirm_withdraw', '', true); // Route for POST
    $data['continue'] = $this->url->link('account/wallet', '', true);

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('account/add_money', $data));
}

public function confirm_withdraw() {
    if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['amount'])) {
        $customer_id = $this->customer->getId();
        $amount = (float)$this->request->post['amount'];
        $upi_id = $this->request->post['upi_id'];
        $reason = $this->request->post['reason'];

        if ($amount > 0 && !empty($upi_id)) {
            $this->load->model('account/wallet');

            // Optionally: Check if wallet balance is enough
            $wallet = $this->model_account_wallet->getWalletByCustomerId($customer_id);
            $balance = isset($wallet['balance']) ? (float)$wallet['balance'] : 0;

            if ($amount <= $balance) {
                $this->model_account_wallet->addTransaction($customer_id, $amount, 'debit', $reason, $upi_id);
                $this->session->data['success'] = 'Withdrawal request submitted successfully!';
            } else {
                $this->session->data['error'] = 'Insufficient wallet balance!';
            }
        } else {
            $this->session->data['error'] = 'Invalid amount or UPI ID!';
        }
    }

    $this->response->redirect($this->url->link('account/wallet', '', true));
}

    public function canPayWithWallet() {
        $json = array();
        if (!$this->customer->isLogged()) {
            $json['success'] = false;
            $json['error'] = 'Not logged in';
        } else {
            $this->load->model('account/wallet');
            $wallet_balance = $this->model_account_wallet->getWalletBalance($this->customer->getId());
            $amount = isset($this->request->get['amount']) ? (float)$this->request->get['amount'] : 0;
            $json['wallet_balance'] = $wallet_balance;
            $json['amount'] = $amount;
            $json['can_pay'] = ($wallet_balance >= $amount && $amount > 0);
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


}