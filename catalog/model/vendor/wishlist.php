<?php
class ModelVendorWishlist extends Model {
	public function addWishlist($product_id) {
		$this->event->trigger('pre.wishlist.add');

		$this->db->query("INSERT INTO " . DB_PREFIX . "vendorproduct_wishlist SET vendor_id = '" . (int)$this->vendor->getId() . "', product_id = '" . (int)$product_id . "', date_added = NOW()");

		$this->event->trigger('post.wishlist.add');
	}
}
