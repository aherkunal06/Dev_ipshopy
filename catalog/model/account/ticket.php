<?php
class ModelAccountTicket extends Model {
    public function addTicket($data, $customer_id, $file = '') {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_ticket SET customer_id = '" . (int)$customer_id . "', subject = '" . $this->db->escape($data['subject']) . "', category = '" . $this->db->escape($data['category']) . "', description = '" . $this->db->escape($data['description']) . "', file = '" . $this->db->escape($file) . "', date_added = NOW()");
    }

public function getTicket($ticket_id) {
    // $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_ticket WHERE ticket_id = '" . (int)$ticket_id . "'");
    // return $query->row;
    $query = $this->db->query("SELECT t.*, c.firstname, c.lastname 
        FROM " . DB_PREFIX . "customer_ticket t 
        LEFT JOIN " . DB_PREFIX . "customer c ON t.customer_id = c.customer_id 
        WHERE t.ticket_id = '" . (int)$ticket_id . "' 
        AND t.customer_id = '" . (int)$this->customer->getId() . "'");

    if ($query->row) {
        $query->row['customer_name'] = $query->row['firstname'] . ' ' . $query->row['lastname'];
    }

    return $query->row;

}
public function getReplies($ticket_id) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_ticket_reply WHERE ticket_id = '" . (int)$ticket_id . "' ORDER BY date_added ASC");
    return $query->rows;
}

    public function getTickets($customer_id, $status = 'all') {
    $sql = "SELECT * FROM " . DB_PREFIX . "customer_ticket WHERE customer_id = '" . (int)$customer_id . "'";

    if ($status != 'all') {
        $sql .= " AND status = '" . $this->db->escape($status) . "'";
    }

    $sql .= " ORDER BY ticket_id DESC";

    $query = $this->db->query($sql);
    return $query->rows;
}


    public function addReply($ticket_id, $message, $file = '') {
        // Fetch customer_id from the ticket
        $query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer_ticket WHERE ticket_id = '" . (int)$ticket_id . "'");
        $customer_id = 0;
        if ($query->num_rows) {
            $customer_id = (int)$query->row['customer_id'];
        }
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_ticket_reply SET ticket_id = '" . (int)$ticket_id . "', customer_id = '" . (int)$customer_id . "', user_type = 'customer', message = '" . $this->db->escape($message) . "', file = '" . $this->db->escape($file) . "', date_added = NOW()");
    }
     // status handling code
    public function updateStatus($ticket_id, $status) {
        $this->db->query("UPDATE " . DB_PREFIX . "customer_ticket SET status = '" . $this->db->escape($status) . "' WHERE ticket_id = '" . (int)$ticket_id . "'");
    }

public function getTicketById($customer_id, $status = 'all', $ticket_id = '') {
    $sql = "SELECT * FROM " . DB_PREFIX . "customer_ticket WHERE customer_id = '" . (int)$customer_id . "'";

    if ($status != 'all') {
        $sql .= " AND LOWER(status) = '" . $this->db->escape(strtolower($status)) . "'";
    }

    if (!empty($ticket_id)) {
        $sql .= " AND ticket_id = '" . (int)$ticket_id . "'";
    }

    $sql .= " ORDER BY date_added DESC";

    $query = $this->db->query($sql);
    return $query->rows;
}


   
}

// 		$output = $this->registry->get('view')->getOutput($route, $data);
// 		$trigger = 'view/' . $route;
