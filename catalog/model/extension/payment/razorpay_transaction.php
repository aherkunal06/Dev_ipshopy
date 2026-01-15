<?php
class ModelExtensionPaymentRazorpayTransaction  extends Model {
    public function insertTransaction(array $data) {
        $sql = "
            INSERT INTO `" . DB_PREFIX . "razorpay_transactions`
            SET
                `ip_transaction_id`  = '" . $this->db->escape($data['ip_transaction_id']) . "',
                `razorpay_order_id`  = '" . $this->db->escape($data['razorpay_order_id']) . "',
                `total_amount`       = " .     (float)$data['total_amount']       . ",
                `currency`           = 'INR',
                `payment_status`     = 'created',
                `created_at`         = NOW(),
                `updated_at`         = NOW()
        ";
        $this->db->query($sql);
    }

    public function getTransactionByIpTransactionId(string $ip_transaction_id) {
        $query = $this->db->query("
            SELECT *
            FROM `" . DB_PREFIX . "razorpay_transactions`
            WHERE `ip_transaction_id` = '" . $this->db->escape($ip_transaction_id) . "'
            LIMIT 1
        ");
        return $query->row;
    }
    
    
}
    