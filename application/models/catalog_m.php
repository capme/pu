<?php

class Catalog_m extends MY_Model
{

    const DEFAULT_LOW_CONST = 4;
    const DEFAULT_MID_CONST = 2;
    const DEFAULT_HIGH_CONST = 1;
    const DEFAULT_MANUAL_CONST = 1;
    const DEFAULT_CTR_CONST = 4;
    const DEFAULT_CR_CONST = 4;
    const DEFAULT_LOW_PRICE = 500000;
    const DEFAULT_HIGH_PRICE = 1500000;
    const DEFAULT_NEWEST_CONST = 0;
    const DEFAULT_PUSH_TO_MAGENTO = 1;

    const ITEMS_PER_PAGE = 12;
    const MAX_BRAND_PER_PAGE = 3;

    var $tableCategory = "catalog_category_";
    var $tableCategoryProduct = "catalog_category_product_";
    var $tableCtr = "ctr";
    var $sorted = array();
    var $sum = array();
    var $out = array();

    var $productStatus = array('synced','sorted','pushed');

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

        $debug = array();
        foreach($data as $_id => $row) {
            foreach($row as $_store => $_products) {
//                $this->db->delete($tableCategoryProduct, array("category_id" => $_id, "store_id" => $_store));

                if(!empty($_products)) {
                    foreach ($_products as $_product) {
                        if($_product['type'] == 'configurable'){
                            $this->db->where('category_id',$_id);
                            $this->db->where('product_id',$_product['product_id']);
                            $this->db->where('store_id',$_store);
                            $cek = $this->db->get($tableCategoryProduct)->num_rows();
                            if(empty($cek)){
                                $insertCatalogCategoryProduct[] = array(
                                    'category_id' => $_id,
                                    'product_id' => $_product['product_id'],
                                    'sku' => $_product['sku'],
                                    'position' => $_product['position'],
                                    'store_id' => $_store,
                                    'updated_at' => date('Y-m-d H:i:s'),
                                    'status' => array_search('synced', $this->productStatus)
                                );
                                $debug['debug.insertCategoryProduct'][$_id][$_store][$_product['type']]++;

                            } else {
                                $updateCatalogCategoryProduct[] = array(
                                    'category_id' => $_id,
                                    'product_id' => $_product['product_id'],
                                    'sku' => $_product['sku'],
                                    'position' => $_product['position'],
                                    'store_id' => $_store,
                                    'updated_at' => date('Y-m-d H:i:s'),
                                    'status' => array_search('synced', $this->productStatus)
                                );
                                $debug['debug.updateCategoryProduct'][$_id][$_store][$_product['type']]++;

                            }
                        }
                    }
                }
            }
        }
        log_message("debug","catalog_m.insertCategoryProduct] debug:".print_r($debug,true));

        if(!empty($insertCatalogCategoryProduct)) {
            log_message("debug","catalog_m.insertCategoryProduct] inserted:".count($insertCatalogCategoryProduct));
            $this->db->insert_batch($tableCategoryProduct, $insertCatalogCategoryProduct);
        }

        if(!empty($updateCatalogCategoryProduct)){
            foreach($updateCatalogCategoryProduct as $_update){
                $this->db->where(array('category_id'=>$_update['category_id'], 'product_id' => $_update['product_id'], 'store_id' => $_update['store_id']));
                $this->db->update($tableCategoryProduct,array('position'=>$_update['position'],'status'=>$_update['status'],'updated_at' => $_update['updated_at']));
            }
        }

        $this->db->trans_complete();
    }

    public function updateCatalogCategoryProduct($client, $data){
        $tableCategoryProduct = $this->tableCategoryProduct.$client['id'];

        $this->db->trans_start();
        usort($data, array($this, "sort_by_score"));

        $this->sorted = array();
        $this->sum = array();
        $this->out = array();

        $this->repositionByBrand($data);
//        $sorteddata = $this->sorted;
        $sorteddata = array_reverse($this->sorted); //magento best value : order by position desc

        $this->db->trans_start();
        foreach($sorteddata as $i => $_data){
            $_update = array('result_index' => $i+1, 'score' => $_data['score'], 'status' => 1);
            $where = array('category_id'=>$_data['category_id'], 'product_id'=>$_data['product_id']);
            $this->db->where($where);
            $this->db->update($tableCategoryProduct,$_update);
        }
        $this->db->trans_complete();
    }

    public function updateStatusCategoryProduct($client,$data){
        $tableCategoryProduct = $this->tableCategoryProduct.$client['id'];

        $this->db->trans_start();
        foreach($data as $i => $_data){
            if(!empty($_data['pushed'])){
                $_update = array('status' => 2);
                $where = array('category_id'=>$_data['category_id'], 'product_id'=>$_data['product_id']);
                $this->db->where($where);
                $this->db->update($tableCategoryProduct,$_update);
            }

        }
        $this->db->trans_complete();
    }

    public function repositionByBrand(array $data){
        $insert=0;
        foreach($data as $i => $_d) {

            $page = floor(count($this->sorted) / self::ITEMS_PER_PAGE);
            $_brand = explode(',',$_d['sku_description']);

            if(!empty($_d['ctr_stat'])) {
                $this->sorted[$_d['product_id']] = $_d;
                $this->sum[$page][$_brand[0]]++;
//                print "page : {$page}, brand: {$_brand[0]}, CTR , jml: {$this->sum[$page][$_brand[0]]}, score: {$_d['score']}, price : {$_d['price']} product_id : {$_d['product_id']}\n";
                $insert++;
                log_message('debug',"[catalog_m.repositionByBrand] ===> page : {$page}, brand: ".$_brand[0].", CTR, ".(isset($_d['out']) ? 'OUT':'').", score: ".$_d['score'].", price : ".$_d['price']." product_id : ".$_d['product_id']);

            } else {
                if($this->sum[$page][$_brand[0]] < self::MAX_BRAND_PER_PAGE ){
                    if(!empty($this->out)){
                        $_out = 0;
                        foreach($this->out as $a=>$x){
                            $xpage = floor(count($this->sorted) / self::ITEMS_PER_PAGE);
                            $_xbrand = explode(',',$_d['sku_description']);
                            if($this->sum[$xpage][$_xbrand[0]] < self::MAX_BRAND_PER_PAGE ){
                                if(!isset($this->sorted[$x['product_id']])) {
                                    $this->sorted[$x['product_id']] = $x;
                                    $this->sum[$xpage][$_xbrand[0]]++;
                                    $_out++;
                                    $insert++;

                                    unset($this->out[$x['product_id']]);
//                                    print "===> OUT page : {$xpage}, brand: {$_xbrand[0]}, REG ".(isset($x['out']) ? 'OUT':'')." , jml: {$this->sum[$page][$_xbrand[0]]}, score: {$x['score']}, price : {$x['price']} product_id : {$x['product_id']}\n";
                                    log_message('debug',"[catalog_m.repositionByBrand] ===> page : {$xpage}, brand: ".$_xbrand[0].", REG ".(isset($x['out']) ? 'OUT':'').", score: ".$x['score'].", price : ".$x['price']." product_id : ".$x['product_id']);

                                }
                            }
                        }
                    }
                    if(empty($_out)){
                        if(!isset($this->sorted[$_d['product_id']])){
                            $this->sorted[$_d['product_id']] = $_d;
                            $this->sum[$page][$_brand[0]]++;
//                            print "page : {$page}, brand: {$_brand[0]}, REG ".(isset($_d['out']) ? 'OUT':'')." , jml: {$this->sum[$page][$_brand[0]]}, score: {$_d['score']}, price : {$_d['price']} product_id : {$_d['product_id']}\n";
                            $insert++;
                            log_message('debug',"[catalog_m.repositionByBrand] ===> page : {$page}, brand: ".$_brand[0].", CTR, ".(isset($_d['out']) ? 'OUT':'').", score: ".$_d['score'].", price : ".$_d['price']." product_id : ".$_d['product_id']);

                        }
                    } else {
                        $this->out[$_d['product_id']] = $_d;
                        $this->out[$_d['product_id']]['out'] = 1;
                    }
                } else {
                    $this->out[$_d['product_id']] = $_d;
                    $this->out[$_d['product_id']]['out'] = 1;
                }
            }
        }

        $this->sorted = array_merge($this->sorted, $this->out);

        return $insert;
    }

    public function getCatalogCategoryProduct($client, $categoryId = "", $filters=array()){


        $tableCategoryProduct = $this->tableCategoryProduct.$client['id'];

        $tableInvItems = 'inv_items_'.$client['id'];

        $mysql = $this->load->database('mysql', TRUE);

        $mysql->select($tableCategoryProduct.'.*, '.$tableInvItems.'.price, '.$tableInvItems.'.created_at, '.$tableInvItems.'.sku_description');

        $mysql->join($tableInvItems,$tableCategoryProduct.'.sku = '.$tableInvItems.'.sku_config', 'left');

        if(!empty($categoryId)){
            $mysql->where( $tableCategoryProduct.".category_id = ", $categoryId);
        }

//
        if(!empty($filters['filter'])) {
            foreach($filters['filter'] as $key => $val) {
                if(!empty($val)) {
                    if($val['type'] == 'like'){
//                        $mysql->like($tableCategoryProduct.'.'.$key, $val['value']);
                        $mysql->like($key, $val['value']);
                    } else {
//                        $mysql->where($tableCategoryProduct.'.'.$key, $val['value']);
                        $mysql->where($key, $val['value']);
                    }
                }
            }
        }


        if(!empty($filters['limit'])){
            $mysql->limit($filters['limit']['limit'], $filters['limit']['offset']);
        }

        if(!empty($filters['orderby'])){
            $mysql->order_by($filters['orderby'][0], $filters['orderby'][1]);
        }

        if(!empty($filters['groupby'])){
            $mysql->group_by( $tableCategoryProduct.'.'.$filters['groupby']);
        } else {
            $mysql->group_by( $tableCategoryProduct.'.id');
        }


//        $result = $mysql->get($tableCategoryProduct)->result_array();
        $result = $mysql->get($tableCategoryProduct);

        // cek sql query
         log_message('debug','getCatalogCategoryProduct : '.$mysql->last_query());

        return $result;
    }


    public function updateSorting($client, $datas = array(), $sortingConfig){
//        $rand = $this->random(6);

        foreach($datas as $i => $data){
            $rand = $this->random(6);
            $score = $this->score($data,$rand,$sortingConfig,$i);
            $updateData[$i] = $data;
            $updateData[$i]['score'] = $score['score'];
            $updateData[$i]['price_stat'] = $score['price_stat'];
            $updateData[$i]['ctr_stat'] = $score['ctr_stat'];
//            $updateData[] = array('id'=>$data['id'],'score'=>$score,'category_id'=>$data['category_id'],'product_id'=>$data['product_id'],'created_at'=>$data['created_at'],'sku'=>$data['sku']);
        }
        usort($updateData, array($this, "sort_by_score"));
        $this->updateCatalogCategoryProduct($client, $updateData);
    }

    public function score($data = array(),$rand=null,$config,$pos){

        if(!$rand){
            $rand = $this->random(2);
        }

        // hitung price weight
        if((int) $data['price'] <= (int) $config['low_price']){
            $price_value =  (int) $config['low_constant'] * $rand;
            $price_stat = 'low';
        } elseif( (int) $data['price'] > (int) $config['low_price'] && (int) $data['price'] < (int) $config['low_price'] ){
            $price_value = (int) $config['mid_constant'] * $rand;
            $price_stat = 'mid';
        } else {
            $price_value = (int) $config['high_constant'] * $rand;
            $price_stat = 'high';
        }


        $manual_weight = (int) $config['manual_constant'] * (int) $data['manual_weight'] * $rand;
        log_message('debug','score '.$data['product_id']." : ".$config['manual_constant'].':'.$data['manual_weight']);

        //get CTR and Conversion
        $dataCtr = $this->getCtr($data['product_id']);
        if(!is_null($dataCtr)) {
            $itemCtr = (int) $config['ctr_constant'] * $dataCtr[0]['ctr'];
            $itemCr = (int) $config['cr_constant'] * $dataCtr[0]['conversion'];
            $ctr_stat = 1;
        }else{
            $itemCtr = 0;
            $itemCr = 0;
            $ctr_stat = 0;
        }

        //newest weight
        $newest_weight = $config['newest_constant'] * $rand * ($pos / 1000);

        $score = $price_value + $manual_weight + $itemCtr + $itemCr + $newest_weight;
        log_message('debug','score '.$data['product_id']." : ".$score." = ".$price_value." + ".$manual_weight." + ".$itemCtr." + ".$itemCr." + ".$newest_weight." ==> rand(".$rand.")");

//        print "score : $score\n\n";
        $return = array('score'=>$score, 'price_stat'=>$price_stat, 'ctr_stat'=>$ctr_stat);
        return $return;
//        return $score;
    }

    private function random($exp = 1){
        $max = pow(10,$exp);
//        $random = mt_rand(0, $max) / $max;
        $random = mt_rand(1, $max-1) / $max;

        return $random;
    }

    private static function sort_by_score($a, $b){
        if ($a['score'] == $b['score']) return 0;
        return ($a['score'] > $b['score']) ? -1 : 1 ;
//        return ($a['score'] < $b['score']) ? -1 : 1 ;

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