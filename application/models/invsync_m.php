<?php

class Invsync_m extends MY_Model {
    var $table = 'inv_items_';

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
    }

    public function save($data, $client) {
        $tableName = $this->table.$client['id'];
        $this->db->trans_start();
        $this->db->truncate($tableName);
        $insert = array();

        foreach($data as $row) {
            if(!empty($row)) {
                $insert[] = array('sku_simple' => $row->sku, 'sku_description' => $row->i_description, 'updated_at' => date('Y-m-d H:i:s'));
            }
        }

        if(!empty($insert)) {
            $this->db->insert_batch($tableName, $insert);
        }
    }

    public function findBySku($sku, $clientId) {
        return $this->db->get_where($this->table.$clientId, array('sku_simple' => $sku))->result_array();
    }

    public function findByProdColorSize($prod, $color, $size, $clientId) {
        return $this->db->query("SELECT * FROM ".$this->table.$clientId." WHERE sku_description like '%".$prod."%:".$size."%".$color."%'")->result_array();
    }

}