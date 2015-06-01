<?php

class Catalog_m extends MY_Model
{

    var $tableCategory = "catalog_category_";
    var $tableCategoryProduct = "catalog_category_product_";

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
    }

    public function saveCategory($data, $client) {
        $tableCategory = $this->tableCategory.$client['id'];

        $this->db->trans_start();
        $this->db->truncate($tableCategory);

        $insertCatalogCategory = array();

        foreach($data as $row) {
            if(!empty($row)) {
                $insertCatalogCategory[] = array('category_id' => $row['category_id'], 'name' => $row['name'], 'path' => $row['path'], 'url_path' => $row['url_path'], 'updated_at' => date('Y-m-d H:i:s'));
            }
        }

        if(!empty($insertCatalogCategory)) {
            $this->db->insert_batch($tableCategory, $insertCatalogCategory);
        }

        $this->db->trans_complete();
    }
}