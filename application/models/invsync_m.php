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
                $insert[] = array('sku_simple' => $row->sku, 'sku_config' => $row->description2, 'sku_description' => $row->i_description, 'updated_at' => date('Y-m-d H:i:s'));
            }
        }

        if(!empty($insert)) {
            $this->db->insert_batch($tableName, $insert);
        }
	$this->db->trans_complete();
    }

    public function findBySku($sku, $clientId) {
        return $this->db->get_where($this->table.$clientId, array('sku_simple' => $sku))->result_array();
    }

    public function findConfigs($configs, $clientId) {
        $this->db->distinct();
        $this->db->select('sku_config');
        $this->db->where_in('sku_config', $configs);
        $rows = $this->db->get($this->table.$clientId)->result_array();
        if(empty($rows)) {
            return end($configs);
        }

        $availConfigs = array();
        foreach($rows as $config) {
            $availConfigs = $config;
        }

        foreach($configs as $candidate) {
            if(in_array($candidate, $availConfigs)) {
                return $candidate;
            }
        }

        return end($configs);
    }

    public function findByProdColorSize($prod, $color, $size, $clientId) {
        return $this->db->query("SELECT * FROM ".$this->table.$clientId." WHERE sku_description like '%".$prod."%:".$size."%".$color."%'")->result_array();
    }

}