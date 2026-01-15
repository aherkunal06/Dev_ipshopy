<?php
class ModelTrackingProductTracking extends Model {

    public function getProductCountByAddedBy() {
        $query = $this->db->query("
            SELECT added_by, COUNT(*) as total 
            FROM " . DB_PREFIX . "product 
            GROUP BY added_by
        ");

        return $query->rows;
    }

    // public function getTrackingList() {
    //     $query = $this->db->query("SELECT u.user_id, u.username, u.email, COUNT(p.product_id) AS total_products 
    //                                FROM " . DB_PREFIX . "user u 
    //                                LEFT JOIN " . DB_PREFIX . "product p ON u.user_id = p.user_id 
    //                                GROUP BY u.user_id");

    //     $data = [];
    //     foreach ($query->rows as $row) {
    //         $data[] = [
    //             'user_id' => $row['user_id'],
    //             'username' => $row['username'],
    //             'email' => $row['email'],
    //             'total_products' => $row['total_products'],
    //             'view_link' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&filter_user_id=' . $row['user_id'], true)
    //         ];
    //     }

    //     return $data;
    // }

    // public function getFilteredTracking($name, $email) {
    //     $sql = "SELECT u.user_id, u.username, u.email, COUNT(p.product_id) AS total_products 
    //             FROM " . DB_PREFIX . "user u 
    //             LEFT JOIN " . DB_PREFIX . "product p ON u.user_id = p.user_id 
    //             WHERE 1";

    //     if (!empty($name)) {
    //         $sql .= " AND u.username LIKE '%" . $this->db->escape($name) . "%'";
    //     }

    //     if (!empty($email)) {
    //         $sql .= " AND u.email LIKE '%" . $this->db->escape($email) . "%'";
    //     }

    //     $sql .= " GROUP BY u.user_id";

    //     $query = $this->db->query($sql);

    //     $data = [];
    //     foreach ($query->rows as $row) {
    //         $data[] = [
    //             'user_id' => $row['user_id'],
    //             'username' => $row['username'],
    //             'email' => $row['email'],
    //             'total_products' => $row['total_products'],
    //             'view_link' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&filter_user_id=' . $row['user_id'], true)
    //         ];
    //     }

    //     return $data;
    // }
}
