<?php
class ModelExtensionMpproductsgridCategory extends Model {
	public function getCategoryPath($category_id) {

		$path = array();
		$query = $this->db->query("SELECT * FROM `". DB_PREFIX ."category_path` WHERE category_id = '". (int)$category_id ."' ORDER BY `level` ASC ");
		foreach ($query->rows as $row) {
			if($row['path_id'] != $category_id) {
				$path[] = $row['path_id'];
			}
		}
		return $path;
	}
}