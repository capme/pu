<?php
class Migration_Expired extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("DROP TABLE IF EXISTS expired_order");

        $this->db->query("CREATE TABLE `expired_order`
            (`id` int(10),`client_id` int(3),
            `order_number` varchar(30),
            `status` int (3),
            `expired_date` TIMESTAMP,
			primary key (`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $this->db->trans_complete();

    }

    public function down() {
        parent::down();
        $this->db->query("DROP TABLE IF EXISTS holiday");
    }
}