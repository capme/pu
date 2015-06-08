<?php
class Migration_Sorting extends Base_migration {
    public function up(){
        parent::up();
        $this->db->trans_start();

        $new = array(
            "sorting" => array("name" => "Sorting", "slug" => "", "icon" => "fa-sort", "hidden" => 0, "status" => 1, "parent" => 0, 'sort' => 15),
        );
        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $new= array(
            "sortingtool" => array("name" => "Sorting Tools", "slug" => "sortingtool", "icon" => "fa-sort", "hidden" => 0, "status" => 1, "parent" => $parentTags['sorting'], 'sort' => 15),
        );

        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "Sorting List", "slug" => "sortingtool/sortingToolList", "hidden" => 1, "status" => 1, "parent" => $parentTags['sortingtool']),
            array("name" => "Sorting View", "slug" => "sortingtool/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['sortingtool']),
            array("name" => "Sorting Save", "slug" => "sortingtool/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['sortingtool']),
            array("name" => "Sorting View Category", "slug" => "sortingtool/viewcategory", "hidden" => 1, "status" => 1, "parent" => $parentTags['sortingtool'])

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

    public function down(){
        parent::down();
        $this->db->trans_start();
        $this->db->query("DELETE FROM module WHERE slug like 'sort%'");
        $this->db->trans_complete();
    }
}
?>
