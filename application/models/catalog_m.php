<?php

class Catalog_m extends MY_Model
{

    const LOW_CONST = 3;
    const MID_CONST = 2;
    const HIGH_CONST = 1;
    const MANUAL_CONST = 1;
    const CTR_CONST = 4;
    const CR_CONST = 4;
    const LOW_PRICE = 500000;
    const HIGH_PRICE = 2000000;

    var $tableCategory = "catalog_category_";
    var $tableCategoryProduct = "catalog_category_product_";
    var $tableCtr = "ctr";

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

    public function getCategory( $client) {
        $tableCategory = $this->tableCategory.$client['id'];

        return $this->db->get($tableCategory)->result_array();
    }

    public function insertCategoryProduct($data, $client) {
        $tableCategoryProduct = $this->tableCategoryProduct.$client['id'];

        $this->db->trans_start();
//            $this->db->truncate($tableCategoryProduct);

        $insertCatalogCategoryProduct = array();

        foreach($data as $_id => $row) {
            foreach($row as $_store => $_products) {
                $this->db->delete($tableCategoryProduct, array("category_id" => $_id, "store_id" => $_store));

                if(!empty($_products)) {
                    foreach ($_products as $_product) {
//                        if($_product['type'] == 'configurable'){
                            $insertCatalogCategoryProduct[] = array(
                                'category_id' => $_id,
                                'product_id' => $_product['product_id'],
                                'sku' => $_product['sku'],
                                'position' => $_product['position'],
                                'store_id ' => $_store,
                                'updated_at' => date('Y-m-d H:i:s')
                            );
                            if($_product['product_id'] == '890'){
                                print_r($_store);
                                print_r($_product);
                            }
//                        }

                    }
                }
            }
        }

        if(!empty($insertCatalogCategoryProduct)) {
            $this->db->insert_batch($tableCategoryProduct, $insertCatalogCategoryProduct);
            log_message('[debug','catalog_m.insertCategoryProduct] inserted:'.count($insertCatalogCategoryProduct));
        }

        $this->db->trans_complete();
    }

    public function updateCatalogCategoryProduct($client, $data){
        $tableCategoryProduct = $this->tableCategoryProduct.$client['id'];

        $this->db->trans_start();
        usort($data, array($this, "sort_by_score"));
//        print_r($data);
        foreach($data as $i => $_data){
            $_update = array('result_index' => $i+1, 'score' => $_data['score']);
            $where = array('category_id'=>$_data['category_id'], 'product_id'=>$_data['product_id']);
            $this->db->where($where);
            $this->db->update($tableCategoryProduct,$_update);
        }
        $this->db->trans_complete();
    }

    public function getCatalogCategoryProduct($client, $categoryId = "", $filters=array()){
        $tableCategoryProduct = $this->tableCategoryProduct.$client['id'];

        $tableInvItems = 'inv_items_'.$client['id'];

        $mysql = $this->load->database('mysql', TRUE);

        $mysql->select($tableCategoryProduct.'.*, '.$tableInvItems.'.price, '.$tableInvItems.'.created_at');

        $mysql->join($tableInvItems,$tableCategoryProduct.'.sku = '.$tableInvItems.'.sku_config', 'left');

        if(!empty($categoryId)){
            $mysql->where( $tableCategoryProduct.".category_id = ", $categoryId);
        }

//
//        $filter = $filters['filter'];
//        foreach($filter as $key => $val) {
//            if(!empty($val)) {
//                $mysql->where( self::VIEW_STOCKS.'.'.$key, $val);
//            }
//        }

        if(!empty($filters['groupby'])){
            $mysql->group_by( $tableCategoryProduct.'.'.$filters['groupby']);
        } else {
            $mysql->group_by( $tableCategoryProduct.'.id');
        }

        $result = $mysql->get($tableCategoryProduct)->result_array();

        // cek sql query
         log_message('debug','getCatalogCategoryProduct : '.$mysql->last_query());

        return $result;
    }


    public function updateSorting($client, $datas = array()){
        foreach($datas as $data){
            $score = $this->score($data);
            $updateData[] = array('id'=>$data['id'],'score'=>$score,'category_id'=>$data['category_id'],'product_id'=>$data['product_id']);
        }
        usort($updateData, array($this, "sort_by_score"));
        $this->updateCatalogCategoryProduct($client, $updateData);
//        return $updateData;
    }

    public function score($data = array()){

        $rand = $this->random(6);

        // hitung price weight
        if((int) $data['price'] <= self::LOW_PRICE){
            $price_value = self::LOW_CONST * $rand;
        } elseif( (int) $data['price'] > self::LOW_PRICE && (int) $data['price'] < self::HIGH_PRICE  ){
            $price_value = self::MID_CONST * $rand;
        } else {
            $price_value = self::HIGH_CONST * $rand;
        }

        $manual_weight = self::MANUAL_CONST * (int) $data['manual_weight'] * $rand;

        //get CTR and Conversion
        $dataCtr = $this->getCtr($data['product_id']);
        if(!is_null($dataCtr)) {
            $itemCtr = self::CTR_CONST * $dataCtr[0]['ctr'];
            $itemCr = self::CR_CONST * $dataCtr[0]['conversion'];
        }else{
            $itemCtr = 0;
            $itemCr = 0;
        }

        $score = $price_value + $manual_weight + $itemCtr + $itemCr;
        log_message('debug','score '.$data['product_id']." : ".$score." = ".$price_value." + ".$manual_weight." + ".$itemCtr." + ".$itemCr);

//        print "score : $score\n\n";

        return $score;
    }

    private function random($exp = 1){
        $max = pow(10,$exp);
        $random = mt_rand(0, $max) / $max;

        return $random;
    }

    private static function sort_by_score($a, $b){
        if ($a['score'] == $b['score']) return 0;
        return ($a['score'] > $b['score']) ? -1 : 1 ;
    }

    public function getCtr($product_id) {
        //currently only supported for PARAPLOU
        $mysql = $this->load->database('mysql', TRUE);
        $query = $mysql->get_where($this->tableCtr, array('product_id'=>$product_id));
        $rows = $query->result_array();
        if(empty($rows)){
            return null;
        }else{
            return $rows;
        }
    }

}