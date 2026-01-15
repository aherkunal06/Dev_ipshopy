<?php
class ModelToolBrokenLinks extends Model {

    public function clearBrokenLinks() {
        $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "broken_links");
    }

    public function addBrokenLink($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "broken_links 
            SET page_url = '" . $this->db->escape($data['page_url']) . "',
                link_url = '" . $this->db->escape($data['link_url']) . "',
                http_code = '" . (int)$data['http_code'] . "',
                checked_at = NOW(),
                remarks = '" . $this->db->escape($data['remarks']) . "'");
    }

    public function getBrokenLinks() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "broken_links ORDER BY checked_at DESC");
        return $query->rows;
    }
}
?>
