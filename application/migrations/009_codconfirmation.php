<?php
class Migration_Codconfirmation extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("DELETE FROM module WHERE slug like 'cod%'");
        $this->db->insert("module", array("name" => "COD Order", "slug" => "codconfirmation", "icon" => "fa-shopping-cart", "parent" => 1, "hidden" => 0, "status" => 1));
        $newIds = array();

        $parentModule = $this->db->insert_id();
        $newIds[] = $parentModule;

        $newModule = array(
            "order" => array("name" => "Order Confirmation", "slug" => "codconfirmation", "icon" => "fa-shopping-cart", "hidden" => 0, "status" => 1, "parent" => $parentModule),
            "payment" => array("name" => "Payment Confirmation", "slug" => "codpaymentconfirmation", "icon" => "fa-money", "hidden" => 0, "status" => 1, "parent" => $parentModule)
        );
        $parentTags = array();
        foreach($newModule as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }
        $newModule = array(
            array("name" => "List", "slug" => "codconfirmation/CodConfirmationList", "hidden" => 1, "status" => 1, "parent" => $parentTags['order']),
            array("name" => "View", "slug" => "codconfirmation/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['order']),
            array("name" => "Cancel", "slug" => "codconfirmation/cancel", "hidden" => 1, "status" => 1, "parent" => $parentTags['order']),
            array("name" => "Approve", "slug" => "codconfirmation/approve", "hidden" => 1, "status" => 1, "parent" => $parentTags['order']),
            array("name" => "Save", "slug" => "codconfirmation/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['order']),
            array("name" => "List", "slug" => "codpaymentconfirmation/CodPaymentConfirmationList", "hidden" => 1, "status" => 1, "parent" => $parentTags['payment']),
            array("name" => "View", "slug" => "codpaymentconfirmation/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['payment']),
            array("name" => "Receive", "slug" => "codpaymentconfirmation/receive", "hidden" => 1, "status" => 1, "parent" => $parentTags['payment']),
            array("name" => "Cancel", "slug" => "codpaymentconfirmation/cancel", "hidden" => 1, "status" => 1, "parent" => $parentTags['payment']),
            array("name" => "Save", "slug" => "codpaymentconfirmation/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['payment']),
        );
        foreach($newModule as $module) {
            $this->db->insert("module", $module);
            $newIds[] = $this->db->insert_id();
        }

        $authData = $this->db->get_where("auth_group", array("id" => 1))->row_array();
        $authData['auth_module'] = json_decode($authData['auth_module']);
        $authData['auth_module'] = array_merge($authData['auth_module'], $newIds);

        $this->db->where("id", 1);
        $this->db->update("auth_group", array("auth_module" => json_encode($authData['auth_module'])));

        $this->db->trans_complete();
    }

    public function down() {
        parent::down();
        $this->db->query("DELETE FROM module WHERE slug like 'cod%'");
    }
}
?>