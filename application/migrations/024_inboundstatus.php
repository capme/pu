<?php
class Migration_Inboundstatus extends Base_migration
{
    public function up()
    {
        parent::up();
        $this->load->model('client_m');
        $this->db->trans_start();
        $this->db->query("alter table inb_document modify status tinyint(2)");
        $clients = $this->client_m->getClients();
        foreach($clients as $client) {
            $this->db->query("alter table `inv_items_".$client['id']."` add column sku_config varchar(200) after sku_simple");
        }
        $this->db->trans_complete();
    }

    public function down() {
        parent::down();
        $this->load->model('client_m');
        $this->db->trans_start();
        $this->db->query("alter table inb_document modify status tinyint(1);");
        $clients = $this->client_m->getClients();
        foreach($clients as $client) {
            $this->db->query("alter table `inv_items_".$client['id']."` drop column sku_config");
        }
        $this->db->trans_complete();
    }
}