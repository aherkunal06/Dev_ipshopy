<?php
class ModelIpofferOffer extends Model
{
    public function install()
    {
        // Create referral customers table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ipoffer_referral_customers (
                referral_id int(11) NOT NULL AUTO_INCREMENT,
                customer_id int(11) NOT NULL,
                customer_name varchar(255) NOT NULL,
                customer_email varchar(255) NOT NULL,
                referrer_name varchar(255) NOT NULL DEFAULT '',
                referrer_email varchar(255) NOT NULL DEFAULT '',
                refer_code varchar(255) NOT NULL DEFAULT '',
                refer_link varchar(255) NOT NULL DEFAULT '',
                status tinyint(1) NOT NULL DEFAULT '0',
                date_added datetime NOT NULL,
                date_modified datetime NOT NULL,
                PRIMARY KEY (referral_id),
                KEY customer_id (customer_id),
                KEY status (status)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "ipoffer_referral_customers");
    }

    public function addOffer($data)
    {
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

    public function editOffer($ipoffer_id, $data)
    {
        $offer_type = isset($data['offer_type']) ? $data['offer_type'] : 'first_time';

        $this->db->query("UPDATE " . DB_PREFIX . "ipoffer SET 
            offer_name = '" . $this->db->escape($data['offer_name']) . "', 
            percentage = '" . (float)$data['percentage'] . "', 
            offer_type = '" . $this->db->escape($offer_type) . "',
            status = '" . (int)$data['status'] . "', 
            date_modified = NOW() 
            WHERE ipoffer_id = '" . (int)$ipoffer_id . "'");
    }

    public function deleteOffer($ipoffer_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "ipoffer WHERE ipoffer_id = '" . (int)$ipoffer_id . "'");
    }

    public function getOffer($ipoffer_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer WHERE ipoffer_id = '" . (int)$ipoffer_id . "'");

        return $query->row;
    }

    public function getOffers($data = array())
    {
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

    public function getTotalOffers()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ipoffer");

        return $query->row['total'];
    }

    public function getActiveOffers()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer WHERE status = 1");
        return $query->rows;
    }

    // Referral customer methods
    public function addReferralCustomer($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "ipoffer_referral_customers SET 
            customer_id = '" . (int)$data['customer_id'] . "',
            customer_name = '" . $this->db->escape($data['customer_name']) . "',
            customer_email = '" . $this->db->escape($data['customer_email']) . "',
            referrer_name = '" . $this->db->escape(isset($data['referrer_name']) ? $data['referrer_name'] : '') . "',
            referrer_email = '" . $this->db->escape(isset($data['referrer_email']) ? $data['referrer_email'] : '') . "',
            refer_code = '" . $this->db->escape(isset($data['refer_code']) ? $data['refer_code'] : '') . "',
            refer_link = '" . $this->db->escape(isset($data['refer_link']) ? $data['refer_link'] : '') . "',
            status = '" . (int)$data['status'] . "',
            date_added = NOW(),
            date_modified = NOW()");

        return $this->db->getLastId();
    }

    public function editReferralCustomer($referral_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "ipoffer_referral_customers SET 
            customer_name = '" . $this->db->escape($data['customer_name']) . "',
            customer_email = '" . $this->db->escape($data['customer_email']) . "',
            referrer_name = '" . $this->db->escape($data['referrer_name']) . "',
            referrer_email = '" . $this->db->escape($data['referrer_email']) . "',
            status = '" . (int)$data['status'] . "',
            date_modified = NOW()
            WHERE referral_id = '" . (int)$referral_id . "'");
    }

    public function deleteReferralCustomer($referral_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "ipoffer_referral_customers WHERE referral_id = '" . (int)$referral_id . "'");
    }

    public function getCustomerReferral($customer_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer_referral_customers 
            WHERE customer_id = '" . (int)$customer_id . "' 
            ORDER BY date_added DESC LIMIT 1");

        return $query->row;
    }

    public function getReferralCustomers($data = array())
    {
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

    public function getTotalReferralCustomers()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ipoffer_referral_customers");
        return $query->row['total'];
    }

    public function getReferralOffer()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer WHERE offer_type = 'referral' AND status = 1 ORDER BY date_added DESC LIMIT 1");
        return $query->row;
    }

    public function getOrCreateReferralCode($customer_id)
    {
        // Check if customer already has a referral code
        $query = $this->db->query("SELECT refer_code FROM " . DB_PREFIX . "ipoffer_referral_customers 
            WHERE customer_id = '" . (int)$customer_id . "' AND refer_code != '' 
            ORDER BY date_added DESC LIMIT 1");

        if ($query->num_rows) {
            return $query->row['refer_code'];
        }

        // Generate a new unique referral code
        $code = substr(md5(uniqid(mt_rand(), true)), 0, 10);

        // Ensure code is unique
        $unique = false;
        while (!$unique) {
            $check_query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ipoffer_referral_customers 
                WHERE refer_code = '" . $this->db->escape($code) . "'");

            if ($check_query->row['total'] == 0) {
                $unique = true;
            } else {
                $code = substr(md5(uniqid(mt_rand(), true)), 0, 10);
            }
        }

        return $code;
    }

    public function approveReferral($referral_id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "ipoffer_referral_customers SET status = 1, date_modified = NOW() WHERE referral_id = '" . (int)$referral_id . "'");
    }

    public function disapproveReferral($referral_id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "ipoffer_referral_customers SET status = 2, date_modified = NOW() WHERE referral_id = '" . (int)$referral_id . "'");
    }

    public function incrementReferralVisit($referral_code)
    {
        // Find the referral by code
        $query = $this->db->query("SELECT referral_id FROM " . DB_PREFIX . "ipoffer_referral_customers 
            WHERE refer_code = '" . $this->db->escape($referral_code) . "' LIMIT 1");

        if ($query->num_rows) {
            // Increment the visit count
            $this->db->query("UPDATE " . DB_PREFIX . "ipoffer_referral_customers 
                SET visit = visit + 1, date_modified = NOW() 
                WHERE referral_id = '" . (int)$query->row['referral_id'] . "'");
            return true;
        }

        return false;
    }
    public function disReferralVisit($referral_code)
    {
        // Find the referral by code
        $query = $this->db->query("SELECT referral_id FROM " . DB_PREFIX . "ipoffer_referral_customers 
            WHERE refer_code = '" . $this->db->escape($referral_code) . "' LIMIT 1");

        if ($query->num_rows) {
            // Increment the visit count
            $this->db->query("UPDATE " . DB_PREFIX . "ipoffer_referral_customers 
                SET visit = visit - 1, date_modified = NOW() 
                WHERE referral_id = '" . (int)$query->row['referral_id'] . "'");
            return true;
        }

        return false;
    }

    /**
     * Set conversion and earned amount for a referral
     * 
     * @param string $referral_code The referral code
     * @param float $earned The earned amount based on order total and percentage
     * @return bool Success or failure
     */
    public function setReferralConversionAndEarned($referral_code, $earned)
    {
        $earned = $earned * 0.05;
        // Only update if referral is approved (status = 1)
        $query = $this->db->query("SELECT referral_id, conversion FROM " . DB_PREFIX . "ipoffer_referral_customers 
            WHERE refer_code = '" . $this->db->escape($referral_code) . "' AND status = 1 LIMIT 1");
        if ($query->num_rows) {
            // Increment conversion count and add earned amount
            $this->db->query("UPDATE " . DB_PREFIX . "ipoffer_referral_customers 
                SET conversion = conversion + 1, 
                    earned = earned + '" . (float)$earned . "', 
                    date_modified = NOW() 
                WHERE referral_id = '" . (int)$query->row['referral_id'] . "'");
            return true;
        }
        return false;
    }

    // Fetch a referral customer by code
    public function getReferralByCode($referral_code)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ipoffer_referral_customers WHERE refer_code = '" . $this->db->escape($referral_code) . "' LIMIT 1");
        return $query->row;
    }

    // Check if referral offer is enabled
    public function isReferralOfferEnabled()
    {
        $query = $this->db->query("SELECT offer_id FROM " . DB_PREFIX . "ipoffer WHERE offer_type = 'referral' AND status = 1 LIMIT 1");

        if ($query->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function checkuserfirsttime()
    {
        $customer_id = $this->customer->getId();
        $order_query = $this->db->query(
            "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order WHERE customer_id = '" . (int)$customer_id  . "'"
        );
        if ($order_query->row['total'] > 0) {
            return false;
        } else {
            return true;
        }
    }
    public function getFirstTimeOfferPercentageIfActive()
    {
        $query = $this->db->query("SELECT percentage FROM " . DB_PREFIX . "ipoffer WHERE offer_type = 'first_time' AND status = 1 ORDER BY date_added DESC LIMIT 1");
        if ($query->num_rows) {
            return (float)$query->row['percentage'];
        }
        return null;
    }
}
