<?php

class ModelVendorTicket extends Model {
    
    // Get tickets with filtering
    public function getTickets($filter_data = array()) {
        $sql = "SELECT ticket_id, vendor_id, firstname, lastname, subject, ticket_category, message, image, status, date_added 
                FROM " . DB_PREFIX . "vendor_tickets 
                WHERE 1";
    
        // Correcting the variable name to use $filter_data instead of $data
        if (!empty($filter_data['filter_ticket_id'])) {
            $sql .= " AND ticket_id = '" . (int)$filter_data['filter_ticket_id'] . "'";
        }
    
        $sql .= " ORDER BY date_added DESC";
    
        $query = $this->db->query($sql);
    
        // Ensure images are processed correctly
        foreach ($query->rows as &$ticket) {
            if (!empty($ticket['image'])) {
                $ticket['image'] = HTTPS_CATALOG . 'image/' . $ticket['image'];
            }
        }
    
        return $query->rows;
    }

    public function getTicketId($data = []) {
        $sql = "SELECT * FROM " . DB_PREFIX . "vendor_tickets WHERE 1";
    
        if (!empty($data['filter_ticket_id'])) {
            $sql .= " AND ticket_id LIKE '%" . $this->db->escape($data['filter_ticket_id']) . "%'";
        }
    
        // if (!empty($data['filter_description'])) {
        //     $sql .= " AND description LIKE '%" . $this->db->escape($data['filter_description']) . "%'";
        // }
    
        // if (!empty($data['filter_gst_rate'])) {
        //     $sql .= " AND gst_rate = '" . (float)$data['filter_gst_rate'] . "'";
        // }
    
        // $sql .= " ORDER BY hsn_id DESC";
    
        // if (isset($data['start']) && isset($data['limit'])) {
        //     $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        // }
    
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    // Count total tickets with filters
    public function getTicketsbyid($filter_data) {
        $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_tickets WHERE 1";
    
        if (!empty($filter_data['filter_ticket_id'])) {
            $sql .= " AND ticket_id = '" . (int)$filter_data['filter_ticket_id'] . "'";
        }
    
        if (!empty($filter_data['filter_vendorname'])) {
            $sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($filter_data['filter_vendorname']) . "%'";
        }
    
        if (!empty($filter_data['filter_subject'])) {
            $sql .= " AND subject LIKE '%" . $this->db->escape($filter_data['filter_subject']) . "%'";
        }
    
        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    // Get specific ticket by ID
    public function getTicket($ticket_id) {
        $query = $this->db->query("
            SELECT ticket_id, vendor_id, firstname, lastname, subject, ticket_category, message, image, status, date_added 
            FROM " . DB_PREFIX . "vendor_tickets 
            WHERE ticket_id = '" . (int)$ticket_id . "'");

        if ($query->num_rows) {
            $ticket = $query->row;
            if (!empty($ticket['image'])) {
                $ticket['image'] = HTTPS_CATALOG . 'image/' . $ticket['image'];
            }
            return $ticket;
        } else {
            return false;
        }
    }

    // Get ticket replies
    public function getReplies($ticket_id) {
        $query = $this->db->query("
            SELECT message, image, date_added 
            FROM " . DB_PREFIX . "vendor_ticket_replies 
            WHERE ticket_id = '" . (int)$ticket_id . "' ORDER BY date_added ASC");

        foreach ($query->rows as &$reply) {
            if (!empty($reply['image'])) {
                $reply['image'] = HTTPS_CATALOG . 'image/' . $reply['image'];
            }
        }
        return $query->rows;
    }

    // Add a reply to a ticket
    public function addReply($ticket_id, $message, $image) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "vendor_ticket_replies 
            SET ticket_id = '" . (int)$ticket_id . "', 
                admin_id = '" . (int)$this->user->getId() . "', 
                message = '" . $this->db->escape($message) . "', 
                image = '" . $this->db->escape($image) . "', 
                date_added = NOW()");
    }

    // Get seller replies
    public function getsellerReplies($ticket_id) {
        $query = $this->db->query("
            SELECT message, image, date_added 
            FROM " . DB_PREFIX . "admin_ticket_replies 
            WHERE ticket_id = '" . (int)$ticket_id . "' ORDER BY date_added ASC");

        foreach ($query->rows as &$replys) {
            if (!empty($replys['image'])) {
                $replys['image'] = HTTPS_CATALOG . 'image/' . $replys['image'];
            }
        }
        return $query->rows;
    }
    
    // Update ticket status
    public function updateTicketStatus($ticket_id, $status) {
        $this->db->query("
            UPDATE " . DB_PREFIX . "vendor_tickets 
            SET status = '" . $this->db->escape($status) . "'
            WHERE ticket_id = '" . (int)$ticket_id . "'");
    }
    
}
?>
