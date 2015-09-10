<?php
class Migration_Additems extends Base_migration {
    public function up() {
        parent::up();
        $this->db->query("ALTER TABLE `bank_confirmation` ADD COLUMN `items` TEXT");
    }

    public function down() {
        parent::down();
        $this->db->query("ALTER TABLE `bank_confirmation` DROP COLUMN `items`");
    }
}
