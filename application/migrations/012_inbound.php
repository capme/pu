<?php

/**
 * Class Migration_Inbound
 * @property Client_m $client_m
 */
class Migration_Inbound extends Base_migration {
	public function up() {
		parent::up();
		$this->load->model('client_m');
		$clients = $this->client_m->getClients();
		$this->db->trans_start();
		foreach($clients as $client) {
			$this->_createInbTable($client['id']);
		}
		$this->db->trans_complete();
	}
	
	public function down() {
		parent::down();

		$this->load->model('client_m');
		$clients = $this->client_m->getClients();
		$this->db->trans_start();
		foreach($clients as $client) {
			$this->db->query("DROP TABLE IF EXISTS `inb_inventory_item_".$client['id']."`");
			$this->db->query("DROP TABLE IF EXISTS `inb_inventory_stock_".$client['id']."`");
		}
		$this->db->trans_complete();
	}

	protected function _createInbTable($cId) {
		$this->db->query("CREATE TABLE `inb_inventory_item_".$cId."` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_number` int(11) NOT NULL,
  `sku_config` varchar(30) NOT NULL,
  `sku_simple` varchar(30) NOT NULL,
  `sku_description` text NOT NULL,
  `min` varchar(30) NOT NULL,
  `max` int(11) NOT NULL,
  `cycle_count` int(3) NOT NULL,
  `reorder_qty` int(3) NOT NULL,
  `inventor_method` varchar(200) NOT NULL,
  `temperature` varchar(30) NOT NULL,
  `cost` varchar(200) NOT NULL,
  `upc` varchar(200) NOT NULL,
  `track_lot` varchar(200) NOT NULL,
  `track_serial` varchar(200) NOT NULL,
  `track_expdate` varchar(200) NOT NULL,
  `primary_unit_of_measure` varchar(200) NOT NULL,
  `packaging_unit` varchar(200) NOT NULL,
  `packaging_uom_qty` varchar(200) NOT NULL,
  `length` varchar(200) NOT NULL,
  `width` varchar(200) NOT NULL,
  `height` varchar(200) NOT NULL,
  `weiight` varchar(200) NOT NULL,
  `qualifiers` varchar(200) NOT NULL,
  `storage_setup` varchar(200) NOT NULL,
  `variable_setup` varchar(200) NOT NULL,
  `nmfc` varchar(200) NOT NULL,
  `lot_number_required` varchar(200) NOT NULL,
  `serial_number_required` varchar(200) NOT NULL,
  `serial_number_must_be_unique` varchar(200) NOT NULL,
  `exp_date_req` varchar(200) NOT NULL,
  `enable_cost` varchar(200) NOT NULL,
  `cost_required` varchar(200) NOT NULL,
  `is_haz_mat` varchar(200) NOT NULL,
  `haz_mat_id` varchar(200) NOT NULL,
  `haz_mat_shipping_name` varchar(200) NOT NULL,
  `haz_mat_hazard_class` varchar(200) NOT NULL,
  `haz_mat_packing_group` varchar(200) NOT NULL,
  `haz_mat_flash_point` varchar(200) NOT NULL,
  `haz_mat_label_code` varchar(200) NOT NULL,
  `haz_mat_flat` varchar(200) NOT NULL,
  `image_url` varchar(200) NOT NULL,
  `storage_count_stript_template_id` varchar(200) NOT NULL,
  `storage_rates` varchar(200) NOT NULL,
  `outbound_mobile_serialization_behavior` varchar(200) NOT NULL,
  `price` varchar(200) NOT NULL,
  `total_qty` varchar(200) NOT NULL,
  `unit_type` varchar(200) NOT NULL,
  `attribute_set` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NULL,
  `updated_by` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->db->query("CREATE TABLE `inb_inventory_stock_".$cId."` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `doc_number` int(11) NOT NULL,
  `reference_num` varchar(30) NOT NULL,
  `quantity` int(11) NOT NULL,
  `bin_location` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	}
}
?>