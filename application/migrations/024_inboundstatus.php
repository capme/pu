<?php
class Migration_Inboundstatus extends Base_migration
{
    public function up()
    {
        parent::up();
        $this->db->trans_start();
        $this->db->query("alter table inb_document modify status tinyint(2)");
        $this->db->trans_complete();
    }

    public function down() {
        parent::down();
        $this->db->trans_start();
        $this->db->query("alter table inb_document modify status tinyint(1);");
        $this->db->trans_complete();
    }
}