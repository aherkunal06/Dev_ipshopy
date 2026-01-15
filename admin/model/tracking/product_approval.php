
<?php
class ModelTrackingProductApproval extends Model {

   public function getProductApprovalSummaryByApprovedBy() {
        $query = $this->db->query("
            SELECT 
                approved_by,
                SUM(status = 1) AS approved,
                SUM(status = 0) AS disapproved,
                COUNT(*) AS total
            FROM " . DB_PREFIX . "product
            WHERE approved_by IS NOT NULL AND approved_by != ''
            GROUP BY approved_by
        ");
    
        return $query->rows;
    }
    
    

}
