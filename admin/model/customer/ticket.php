<?php

class ModelCustomerTicket extends Model
{
    public function getTickets($data = [])
    {
        $sql = "SELECT ct.*, c.firstname, c.lastname FROM " . DB_PREFIX . "customer_ticket ct
                LEFT JOIN " . DB_PREFIX . "customer c ON ct.customer_id = c.customer_id
                WHERE 1";

        if (!empty($data['filter_ticket_id'])) {
            $sql .= " AND ct.ticket_id = '" . (int)$data['filter_ticket_id'] . "'";
        }

        $sql .= " ORDER BY ct.date_added DESC";

        $query = $this->db->query($sql);

        $result = [];
        foreach ($query->rows as $row) {
            $row['customer_name'] = $row['firstname'] . ' ' . $row['lastname'];
            $result[] = $row;
        }
        return $result;
    }

    public function getTicket($ticket_id)
    {
        $query = $this->db->query("SELECT ct.*, c.firstname, c.lastname FROM " . DB_PREFIX . "customer_ticket ct
            LEFT JOIN " . DB_PREFIX . "customer c ON ct.customer_id = c.customer_id
            WHERE ct.ticket_id = '" . (int)$ticket_id . "'");
        if ($query->row) {
            $query->row['customer_name'] = $query->row['firstname'] . ' ' . $query->row['lastname'];
        }

        return $query->row;
    }

    // adding the reply of admin to the ticket

    public function addAdminReply($ticket_id, $message, $file = '')
    {
        $this->db->query("UPDATE " . DB_PREFIX . "customer_ticket SET admin_reply = '" . $this->db->escape($message) . "', admin_reply_file = '" . $this->db->escape($file) . "' WHERE ticket_id = '" . (int)$ticket_id . "'");
    }
    // Add a reply
    public function addReply($ticket_id, $message, $file = '', $user_type = 'admin')
    {
        $customer_id = 0;
        if ($user_type == 'customer') {
            // Fetch customer_id from the ticket
            $query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer_ticket WHERE ticket_id = '" . (int)$ticket_id . "'");
            if ($query->num_rows) {
                $customer_id = (int)$query->row['customer_id'];
            }
        }
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_ticket_reply SET ticket_id = '" . (int)$ticket_id . "', customer_id = '" . (int)$customer_id . "', user_type = '" . $this->db->escape($user_type) . "', message = '" . $this->db->escape($message) . "', file = '" . $this->db->escape($file) . "', date_added = NOW()");
    }

    // Get all replies for a ticket
    public function getReplies($ticket_id)
    {
        $query = $this->db->query("
        SELECT r.*, c.firstname 
        FROM " . DB_PREFIX . "customer_ticket_reply r
        LEFT JOIN " . DB_PREFIX . "customer c ON r.customer_id = c.customer_id
        WHERE r.ticket_id = '" . (int)$ticket_id . "'
        ORDER BY r.date_added ASC
    ");

        return $query->rows;
    }
    // status handling code
    public function updateStatus($ticket_id, $status)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "customer_ticket SET status = '" . $this->db->escape($status) . "' WHERE ticket_id = '" . (int)$ticket_id . "'");
    }
}
