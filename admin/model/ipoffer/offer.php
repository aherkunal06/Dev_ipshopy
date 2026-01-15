<?php
class ModelIpofferOffer extends Model {
    public function install() {
        // Create referral customers table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ipoffer_referral_customers (
                referral_id int(11) NOT NULL AUTO_INCREMENT,
                customer_id int(11) NOT NULL,
                customer_name varchar(255) NOT NULL,
                customer_email varchar(255) NOT NULL,
                referrer_name varchar(255) NOT NULL,
                referrer_email varchar(255) NOT NULL,
                status tinyint(1) NOT NULL DEFAULT '0',
                date_added datetime NOT NULL,
                date_modified datetime NOT NULL,
                PRIMARY KEY (referral_id),
                KEY customer_id (customer_id),
                KEY status (status)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "ipoffer_referral_customers");
    }

    public function addOffer($data) {
        // Check for duplicate offer name
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ipoffer WHERE offer_name = '" . $this->db->escape($data['offer_name']) . "'");
        if ($query->row['total'] > 0) {
            return false; // Duplicate found, do not add
        }
        
        $offer_type = isset($data['offer_type']) ? $data['offer_type'] : 'first_time';
        
        $this->db->query("INSERT INTO " . DB_PREFIX . "ipoffer SET 
            offer_name = '" . $this->db->escape($data['offer_name']) . "', 
            percentage = '" . (float)$data['percentage'] . "', 
            offer_type = '" . $this->db->escape($offer_type) . "',
            status = '" . (int)$data['status'] . "', 
            date_added = NOW(), 
            date_modified = NOW()");
        return true;
    }

    public function editOffer($ipoffer_id, $data) {
        $offer_type = isset($data['offer_type']) ? $data['offer_type'] : 'first_time';
        
        $this->db->query("UPDATE " . DB_PREFIX . "ipoffer SET 
            offer_name = '" . $this->db->escape($data['offer_name']) . "', 
            percentage = '" . (float)$data['percentage'] . "', 
            offer_type = '" . $this->db->escape($offer_type) . "',
            status = '" . (int)$data['status'] . "', 
            date_modified = NOW() 
            WHERE ipoffer_id = '" . (int)$ipoffer_id . "'");
    }

    public function deleteOffer($ipoffer_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "ipoffer WHERE ipoffer_id = '" . (int)$ipoffer_id . "'");
    }

    public function getOffer($ipoffer_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer WHERE ipoffer_id = '" . (int)$ipoffer_id . "'");

        return $query->row;
    }

    public function getOffers($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "ipoffer";

        $sort_data = array(
            'offer_name',
            'percentage',
            'offer_type',
            'status',
            'date_added',
            'date_modified'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY date_added";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        
        return $query->rows;
    }

    public function getTotalOffers() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ipoffer");

        return $query->row['total'];
    }

    public function getActiveOffers() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer WHERE status = 1");
        return $query->rows;
    }

    // Referral customer methods
    public function addReferralCustomer($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "ipoffer_referral_customers SET 
            customer_id = '" . (int)$data['customer_id'] . "',
            customer_name = '" . $this->db->escape($data['customer_name']) . "',
            customer_email = '" . $this->db->escape($data['customer_email']) . "',
            referrer_name = '" . $this->db->escape($data['referrer_name']) . "',
            referrer_email = '" . $this->db->escape($data['referrer_email']) . "',
            status = '" . (int)$data['status'] . "',
            date_added = NOW(),
            date_modified = NOW()");
        
        return $this->db->getLastId();
    }

    public function editReferralCustomer($referral_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "ipoffer_referral_customers SET 
            customer_name = '" . $this->db->escape($data['customer_name']) . "',
            customer_email = '" . $this->db->escape($data['customer_email']) . "',
            referrer_name = '" . $this->db->escape($data['referrer_name']) . "',
            referrer_email = '" . $this->db->escape($data['referrer_email']) . "',
            status = '" . (int)$data['status'] . "',
            date_modified = NOW()
            WHERE referral_id = '" . (int)$referral_id . "'");
    }

    public function deleteReferralCustomer($referral_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "ipoffer_referral_customers WHERE referral_id = '" . (int)$referral_id . "'");
    }

    public function getReferralCustomer($referral_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer_referral_customers WHERE referral_id = '" . (int)$referral_id . "'");
        return $query->row;
    }

    public function getReferralCustomers($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "ipoffer_referral_customers";

        $sort_data = array(
            'customer_name',
            'customer_email',
            'referrer_name',
            'referrer_email',
            'status',
            'date_added',
            'date_modified'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY date_added";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getTotalReferralCustomers() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ipoffer_referral_customers");
        return $query->row['total'];
    }

    public function getReferralOffer() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer WHERE offer_type = 'referral' AND status = 1 ORDER BY date_added DESC LIMIT 1");
        return $query->row;
    }

    public function approveReferral($referral_id) {
        $this->db->query("UPDATE " . DB_PREFIX . "ipoffer_referral_customers SET status = 1, date_modified = NOW() WHERE referral_id = '" . (int)$referral_id . "'");
    }

    public function disapproveReferral($referral_id) {
        $this->db->query("UPDATE " . DB_PREFIX . "ipoffer_referral_customers SET status = 2, date_modified = NOW() WHERE referral_id = '" . (int)$referral_id . "'");
    }

    public function getReferralConversionsByReferralId($referral_id) {
        $sql = "SELECT o.order_id, o.date_added AS order_date, c.firstname AS customer_firstname, c.lastname AS customer_lastname, c.email AS customer_email, op.product_id, op.name AS product_name, op.price, op.quantity
                FROM " . DB_PREFIX . "order o
                LEFT JOIN " . DB_PREFIX . "customer c ON o.customer_id = c.customer_id
                LEFT JOIN " . DB_PREFIX . "order_product op ON o.order_id = op.order_id
                WHERE o.referral_id = '" . (int)$referral_id . "' ORDER BY o.order_id DESC, op.product_id ASC";
        $query = $this->db->query($sql);
        $status_map = [5 => 'Completed', 1 => 'Pending', 7 => 'Canceled'];
        $offer = $this->getReferralOffer();
        $percentage = isset($offer['percentage']) ? (float)$offer['percentage'] : 0;
        $rows = [];
        foreach ($query->rows as $row) {
            // Fetch latest order_status_id and its name from order_history
            $history = $this->db->query("SELECT oh.order_status_id, os.name FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' WHERE oh.order_id = '" . (int)$row['order_id'] . "' ORDER BY oh.date_added DESC, oh.order_history_id DESC LIMIT 1");
            if ($history->num_rows) {
                $order_status_id = (int)$history->row['order_status_id'];
                $status = $history->row['name'];
            } else {
                // Fallback to order_status_id from oc_order if no history exists
                $order_status_id_query = $this->db->query("SELECT order_status_id FROM " . DB_PREFIX . "order WHERE order_id = '" . (int)$row['order_id'] . "' LIMIT 1");
                $order_status_id = $order_status_id_query->num_rows ? (int)$order_status_id_query->row['order_status_id'] : 0;
                $status_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1");
                $status = $status_query->num_rows ? $status_query->row['name'] : 'Unknown';
            }
            $earned = ($order_status_id == 5) ? round($row['price'] * $percentage / 100) : 0;
            $rows[] = [
                'order_id' => $row['order_id'],
                'order_date' => $row['order_date'],
                'customer_name' => $row['customer_firstname'] . ' ' . $row['customer_lastname'],
                'customer_email' => isset($row['email']) ? $row['email'] : '',
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'price' => $row['price'],
                'quantity' => $row['quantity'],
                'status' => $status,
                'earned' => $earned,
            ];
        }
        return $rows;
    }

    public function getReferralById($referral_id) {
        $sql = "SELECT c.firstname, c.lastname, c.email
                FROM " . DB_PREFIX . "ipoffer_referral_customers rc
                LEFT JOIN " . DB_PREFIX . "customer c ON rc.customer_id = c.customer_id
                WHERE rc.referral_id = '" . (int)$referral_id . "' LIMIT 1";
        $query = $this->db->query($sql);
        if ($query->num_rows) {
            return [
                'customer_name' => $query->row['firstname'] . ' ' . $query->row['lastname'],
                'customer_email' => $query->row['email']
            ];
        } else {
            return false;
        }
    }
}