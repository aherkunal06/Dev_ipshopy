<?php
class ModelExtensionPaymentRazorpaySplit  extends Model
{
    public function insertSplitOrder(array $data)
    {
        $sql = "
            INSERT INTO `" . DB_PREFIX . "razorpay_split_orders`
            SET
                `order_id`           = " .     (int)$data['order_id']           . ",
                `ip_transaction_id`  = '" .  $this->db->escape($data['ip_transaction_id']) . "',
                `product_id`         = " .     (int)$data['product_id']          . ",
                `product_total`      = " .     (float)$data['product_total']     . ",
                `shipping_total`     = " .     (float)$data['shipping_total']    . ",
                `order_total`        = " .    ((float)$data['product_total'] + (float)$data['shipping_total']) . ",
                `payment_status`     = 'pending',
                `refund_amount`      = 0,
                `refund_status`      = 'none',
                `created_at`         = NOW(),
                `updated_at`         = NOW()
        ";
        $this->db->query($sql);
    }


    public function getTotalByInternalId($ip_transaction_id)
    {
        $query = $this->db->query("SELECT SUM(product_total + shipping_total) AS total FROM " . DB_PREFIX . "razorpay_split_orders WHERE ip_transaction_id = '" . $this->db->escape($ip_transaction_id) . "'");
        return $query->row['total'];
    }

    public function getShippingByOrderId($order_id)
    {
        $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "order_total 
            WHERE order_id = '" . (int)$order_id . "' AND code = 'shipping'");

        if ($query->num_rows) {
            return (float)$query->row['value'];
        } else {
            return 0.00;
        }
    }

    public function getLowOrderFeeByOrderId($order_id)
    {
        $query = $this->db->query("SELECT `value` FROM " . DB_PREFIX . "order_total WHERE `title` = 'low order fee' AND `order_id` = '" . (int)$order_id . "'");
        return $query->row ? $query->row['value'] : 0;
    }
}
