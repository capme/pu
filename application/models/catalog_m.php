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

    public function saveCategoryProductLink($data, $client) {
        $tableCategoryProduct = $this->tableCategoryProduct.$client['id'];

        $this->db->trans_start();
//            $this->db->truncate($tableCategoryProduct);

        $insertCatalogCategoryProduct = array();

        foreach($data as $_id => $row) {
            foreach($row as $_store => $_products) {
                $this->db->delete($tableCategoryProduct, array("category_id" => $_id, "store_id" => $_store));

                if(!empty($_products)) {
                    foreach ($_products as $_product) {
                        $insertCatalogCategoryProduct[] = array(
                            'category_id' => $_id,
                            'product_id' => $_product['product_id'],
                            'sku' => $_product['sku'],
                            'position' => $_product['position'],
                            'store_id ' => $_store,
                            'updated_at' => date('Y-m-d H:i:s')
                        );
                    }
                }
            }
        }

        if(!empty($insertCatalogCategoryProduct)) {
            $this->db->insert_batch($tableCategoryProduct, $insertCatalogCategoryProduct);
        }

        $this->db->trans_complete();
    }
}