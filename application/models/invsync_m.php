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
        if(!empty($data)) {
            $this->db->truncate($tableName);
            $insert = array();

            foreach ($data as $row) {
                if (!empty($row)) {
                    $catalog = json_decode($row->data);
                    $insert[] = array(
                        'sku_simple' => $row->sku,
                        'sku_config' => $row->description2,
                        'sku_description' => $row->i_description,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'product_id' => $catalog->info->product_id,
                        'price' => (int)$catalog->info->price,
                        'created_at' => (empty($catalog->info->created_at) ? '0000-00-00 00:00:00' : $catalog->info->created_at),
                        'magestock' => &$catalog->inventory
                    );
                }
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

    public function findOldSimilarSku($config, $size, $clientId) {
        if(!is_array($size)) {
            $size = array($size);
        }

        $_sql = array();
        foreach($size as $s) {
            $_sql[] = 'sku_description like "%S:%'.$this->db->escape_like_str($s).',%" OR sku_description like "%S:'.$this->db->escape_like_str($s).',%" OR sku_description like "%size:%'.$this->db->escape_like_str($s).',%" OR sku_description like "%size:'.$this->db->escape_like_str($s).',%"';
        }
        $_sql = '('.implode(' OR ', $_sql).')';


        $sql = 'SELECT * FROM `inv_items_'.$clientId.'` where sku_config = '.$this->db->escape($config);
        $sql .= 'AND '.$_sql;

        $data = $this->db->query($sql)->result_array();
        $similarData = array();

        if(!empty($data)) {
            log_message('debug', 'check similar sku::' . print_r($data, true));
            foreach($data as $d) {
                $skuDesc = explode(',', $d['sku_description']);
                list(,$_size) = explode(":", $skuDesc[5]);
                if(in_array(trim($_size), $size)) {
                    $similarData = $d;
                }

            }
        }

        return $similarData;
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