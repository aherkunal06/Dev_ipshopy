<?php
//----new file------------------------------------------
    class ModelVendorTicket extends Model {
        public function addTicket($data, $vendor_id = null) {
            // If vendor_id is not provided, get the current vendor's ID
            if ($vendor_id === null) {
                $vendor_id = $this->vendor->getID();
            }
    
            // Fetch vendor's first name and last name from oc_vendor table
            $query = $this->db->query("SELECT firstname, lastname FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . (int)$vendor_id . "'");
    
            if ($query->num_rows > 0) {
                $firstname = $this->db->escape($query->row['firstname']);
                $lastname = $this->db->escape($query->row['lastname']);
            } else {
                $firstname = 'Unknown';
                $lastname = 'Vendor';
            }
    
            // Handle Image Upload
            $image_path = "";
            if (!empty($_FILES['ticket_image']['name'])) {
                $upload_dir = DIR_IMAGE . 'vendor_tickets/'; // Define the upload directory
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true); // Create directory if it doesn't exist
                }
    
                $filename = uniqid() . '_' . basename($_FILES['ticket_image']['name']);
                $target_file = $upload_dir . $filename;
    
                // Move uploaded file
                if (move_uploaded_file($_FILES['ticket_image']['tmp_name'], $target_file)) {
                    $image_path = 'vendor_tickets/' . $filename; // Save relative path
                }
            }
    
            // Insert ticket into the database with vendor's name and other details
            $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_tickets SET 
                vendor_id = '" . (int)$vendor_id . "', 
                firstname = '" . $firstname . "', 
                lastname = '" . $lastname . "', 
                subject = '" . $this->db->escape($data['subject']) . "',
                ticket_category = '" . $this->db->escape($data['ticket_category']) . "',
                message = '" . $this->db->escape($data['message']) . "', 
                image = '" . $this->db->escape($image_path) . "', 
                status = 'Open', 
                date_added = NOW()");
                // $data['ticket_category'] = isset($this->request->post['ticket_category']) ? $this->request->post['ticket_category'] : '';

        }
        
        public function getTicketsByVendorId($vendor_id) {
            $query = $this->db->query("
                SELECT ticket_id, subject, message, image, status, date_added 
                FROM " . DB_PREFIX . "vendor_tickets 
                WHERE vendor_id = '" . (int)$vendor_id . "'
                ORDER BY date_added DESC");
    
            // Modify image path before returning
            foreach ($query->rows as &$ticket) {
                if (!empty($ticket['image'])) {
                    $ticket['image'] = 'image/' . $ticket['image'];
                }
            }
            return $query->rows;
        }
        
    }
    
    
?>
