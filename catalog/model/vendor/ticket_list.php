<?php
//new file--------------------------------------
class ModelVendorTicketList extends Model {
    public function getVendorTickets($vendor_id = null) {
        $sql = "
            SELECT ticket_id, vendor_id, firstname, lastname, subject, message, image, status, date_added 
            FROM " . DB_PREFIX . "vendor_tickets";
    
        if ($vendor_id !== null) {
            $sql .= " WHERE vendor_id = '" . (int)$vendor_id . "'";
        }
    
        $sql .= " ORDER BY date_added DESC";

        $query = $this->db->query($sql);
    
        // Modify image path before returning
        foreach ($query->rows as &$ticket) {
            if (!empty($ticket['image'])) {
                $ticket['image'] = 'image/' . $ticket['image'];
            }
        }
        return $query->rows;
    }

    public function getTicket($ticket_id) {
        $query = $this->db->query("
            SELECT ticket_id, vendor_id, firstname, lastname, subject, message, image, status, date_added 
            FROM " . DB_PREFIX . "vendor_tickets 
            WHERE ticket_id = '" . (int)$ticket_id . "'");
    
        if ($query->num_rows) {
            $ticket = $query->row;
            if (!empty($ticket['image'])) {
                $ticket['image'] =  'image/' . $ticket['image'];
            }
            return $ticket;
        } else {
            return false;
        }
    }

    public function getReplies($ticket_id) {
        $query = $this->db->query("
            SELECT message, image, date_added 
            FROM " . DB_PREFIX . "vendor_ticket_replies 
            WHERE ticket_id = '" . (int)$ticket_id . "' ORDER BY date_added ASC");
    
        foreach ($query->rows as &$reply) {
            if (!empty($reply['image'])) {
                $reply['image'] = 'image/' . $reply['image'];
            }
        }
        return $query->rows;
    }



//--seller reply to admin--------------------------------------
    // public function getReplies($ticket_id) {
    //     $query = $this->db->query("
    //         SELECT message, image, date_added 
    //         FROM " . DB_PREFIX . "admin_ticket_replies 
    //         WHERE ticket_id = '" . (int)$ticket_id . "' ORDER BY date_added ASC");
    
    //     foreach ($query->rows as &$reply) {
    //         if (!empty($reply['image'])) {
    //             $reply['image'] = HTTPS_CATALOG . 'image/' . $reply['image'];
    //         }
    //     }
    //     return $query->rows;
    // }
        // admin_id = '" . (int)$this->user->vendor_Id() . "', 

    public function addReply($ticket_id, $message, $image) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "admin_ticket_replies 
            SET ticket_id = '" . (int)$ticket_id . "', 
            
                message = '" . $this->db->escape($message) . "', 
                image = '" . $this->db->escape($image) . "', 
                date_added = NOW()");
    }

    public function getsellerReplies($ticket_id) {
        $query = $this->db->query("
            SELECT message, image, date_added 
            FROM " . DB_PREFIX . "admin_ticket_replies 
            WHERE ticket_id = '" . (int)$ticket_id . "' ORDER BY date_added ASC");
    
        foreach ($query->rows as &$replys) {
            if (!empty($replys['image'])) {
                $replys['image'] = 'image/' . $replys['image'];
            }
        }
        return $query->rows;
    }
    
}
?>