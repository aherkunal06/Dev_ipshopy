<?php
class ModelAccountWallet extends Model {
    public function getWalletBalance($customer_id) {
        $query = $this->db->query("SELECT balance FROM " . DB_PREFIX . "wallet_balance WHERE customer_id = '" . (int)$customer_id . "'");

        
        return $query->row ? $query->row['balance'] : 0;
    }

    public function getWalletTransactions($customer_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "wallet_transaction WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC");
        return $query->rows;
    }
   
 

public function addTransaction($customer_id, $amount, $type = 'credit', $reason = '', $upi_id = '', $order_id = 0) {
    if (!in_array($type, ['credit', 'debit'])) {
        throw new Exception('Invalid transaction type');
    }

    // Insert transaction with UPI ID and order_id used in this transaction
    $this->db->query("INSERT INTO " . DB_PREFIX . "wallet_transaction SET customer_id = '" . (int)$customer_id . "', amount = '" . (float)$amount . "', type = '" . $this->db->escape($type) . "', reason = '" . $this->db->escape($reason) . "', upi_id = '" . $this->db->escape($upi_id) . "', order_id = '" . (int)$order_id . "', date_added = NOW()");

    // Update wallet balance
    $check = $this->db->query("SELECT * FROM " . DB_PREFIX . "wallet_balance WHERE customer_id = '" . (int)$customer_id . "'");

    if ($check->num_rows) {
        $new_balance = $type === 'credit' ? $check->row['balance'] + $amount : $check->row['balance'] - $amount;
        $this->db->query("UPDATE " . DB_PREFIX . "wallet_balance SET balance = '" . (float)$new_balance . "' WHERE customer_id = '" . (int)$customer_id . "'");
    } else {
        // New wallet entry if not exists
        $this->db->query("INSERT INTO " . DB_PREFIX . "wallet_balance SET customer_id = '" . (int)$customer_id . "', balance = '" . (float)($type === 'credit' ? $amount : -$amount) . "', default_upi_id = '" . $this->db->escape($upi_id) . "'");
    }
}


public function getWalletByCustomerId($customer_id) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "wallet_balance WHERE customer_id = '" . (int)$customer_id . "'");
    return $query->row;
}


public function updateDefaultUpiId($customer_id, $upi_id) {
    // Check if wallet row exists
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "wallet_balance WHERE customer_id = '" . (int)$customer_id . "'");
    
    if ($query->num_rows) {
        // Update existing record
        $this->db->query("UPDATE " . DB_PREFIX . "wallet_balance SET default_upi_id = '" . $this->db->escape($upi_id) . "' WHERE customer_id = '" . (int)$customer_id . "'");
    } else {
        // Insert new record
        $this->db->query("INSERT INTO " . DB_PREFIX . "wallet_balance SET customer_id = '" . (int)$customer_id . "', balance = 0.00, default_upi_id = '" . $this->db->escape($upi_id) . "', date_added = NOW()");
    }
}

    /**
     * Add referral reward to wallet for a specific customer.
     * @param int $customer_id The customer who earned the referral reward
     * @param float $amount The amount to credit
     * @param int $order_id (optional) The order that triggered the reward
     */
    public function addReferralReward($customer_id, $amount, $order_id = 0) {
        // Always credits the wallet of the specified customer_id
        $reason = 'Referral Reward';
        $upi_id = '';
        $this->addTransaction($customer_id, $amount, 'credit', $reason, $upi_id, $order_id);
        // Optionally, you can store order_id in wallet_transaction if you add a column for it
    }


}