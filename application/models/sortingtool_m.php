<?php
class Sortingtool_m extends MY_Model {

    var $filterSession = "DB_CLIENT_FILTER";
    var $db = null;
    var $table = 'client';
    var $filters = array("id" => "id");
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $tableCatalog="catalog_category";
    var $tableCatalogCategory="catalog_category_product";
    var $tableInv="inv_items";

    var $tableMageCategory = "catalog_category";
    var $tableMageCategoryProduct = "catalog_category_product";
    var $tableSortingConfig = "sorting_config";
    var $filtersproduct = array("product_id" => "product_id");

    var  $defaultSortingConfig = array();


    function __construct(){
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);

        $this->load->model('catalog_m');

        $this->defaultSortingConfig = array(
            'low_price' => Catalog_m::DEFAULT_LOW_PRICE,
            'high_price' => Catalog_m::DEFAULT_HIGH_PRICE,
            'low_constant' => Catalog_m::DEFAULT_LOW_CONST,
            'mid_constant' => Catalog_m::DEFAULT_MID_CONST,
            'high_constant' => Catalog_m::DEFAULT_HIGH_CONST,
            'manual_constant' => Catalog_m::DEFAULT_MANUAL_CONST,
            'ctr_constant' => Catalog_m::DEFAULT_CTR_CONST,
            'cr_constant' => Catalog_m::DEFAULT_CR_CONST,
            'newest_constant' => Catalog_m::DEFAULT_NEWEST_CONST,
            'push_to_magento' => Catalog_m::DEFAULT_PUSH_TO_MAGENTO
        );

    }

    public function getClientSorting()
    {
        $this->db = $this->load->database('mysql', TRUE);
        $iTotalRecords = $this->_doGetTotalRow();
        $iDisplayLength = intval($this->input->post('iDisplayLength'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($this->input->post('iDisplayStart'));
        $sEcho = intval($this->input->post('sEcho'));

        $records = array();
        $records["aaData"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
        $no=0;
        foreach($_row->result() as $_result) {
            list($mageUser) = explode(":", $_result->mage_auth);
            $records["aaData"][] = array(
                '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                $no=$no+1,
                $_result->client_code,
                '<a href="'.site_url("sortingtool/viewcategory/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-list fa-fw"></i> Category</a>',

            );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;

        return $records;
    }

    public function getCatalogById($id){
        $this->db->where($this->pkField,$id);
        $client = $this->db->get($this->table)->row_array();
        $data = $this->db->get($this->tableCatalog."_".$id)->result_array();
        $result=array($client, $data, $id);
        return $result;
    }

    public function getCategory($id, $client){
        $this->db->where('category_id', $id);
        $this->db->group_by('product_id');
        $this->db->order_by('product_id');
        return $this->db->get($this->tableCatalogCategory."_".$client);
    }

    public function getName ($client, $sku){
        $this->db->select('sku_description');
        $this->db->where('sku_config', $sku);
        $this->db->group_by('sku_config');
        return $this->db->get($this->tableInv."_".$client)->row_array();
    }

    public function manageCategory($clientid, $data, $category_id)    {
        $this->db = $this->load->database('mysql', TRUE);
        $this->db->trans_start();
        foreach ($data as $id => $value) {
            $this->db->where(array('product_id' => $id, 'category_id'=> $category_id));
            $this->db->update($this->tableCatalogCategory."_".$clientid, array("manual_weight"=>$value['manualweight']));
        }
        $this->db->trans_complete();
        return $clientid;
    }

    public function clearCurrentFilter() {
        $this->_clearCurrentFilter();
    }

    /**
     * Clear all active filter
     */
    protected function _clearCurrentFilter() {
        $this->aFilters = array();
        $this->session->unset_userdata( $this->filterSession );
    }

    public function getDetailCategory($clientId,$catId){
        $this->db->where('category_id', $catId);
        return $this->db->get($this->tableMageCategory."_".$clientId);
    }

    public function getConfig($clientId,$catId){
        $this->db->where('category_id', $catId);
        $this->db->where('client_id', $clientId);

        return $this->db->get($this->tableSortingConfig);
    }

    public function saveConfig($data = array()){
        $return = array();
        foreach($data as $d){
            $this->db->where($this->pkField, $d['id']);
            $this->db->update($this->tableSortingConfig, $d);
            $return['id'][] = $d['id'];
        }
        return $return;
    }

    /**
     * @param array $data : list of categories (get from magento)
     * @param $client : client id
     * @return array : inserted category;
     */
    public function insertConfig($data = array(), $client){
        $return = array();
        foreach($data as $d){
            $this->db->where('client_id', $client);
            $this->db->where('category_id', $d['category_id']);
            $cekConf = $this->db->get($this->tableSortingConfig, $d)->num_rows();

            if(empty($cekConf)){
                foreach($this->defaultSortingConfig as $name => $value){
                    $insert['client_id'] = $client;
                    $insert['category_id'] = $d['category_id'];
                    $insert['name'] = $name;
                    $insert['value'] = $value;
                    $this->db->insert($this->tableSortingConfig, $insert);
                }
                $return[] = $d;
            }
        }
        return $return;
    }

    public function getCategoryList($client=array()){
        $this->db = $this->load->database('mysql', TRUE);
        $this->table = $this->tableMageCategory."_".$client['id'];
        $this->filters = array("category_id" => "category_id","name"=>"name","path"=>"path","url_path"=>"url_path");
        $this->sorts = array(1 => "id", 2=>"category_id",3=>"name",4=>"path",5=>"url_path",6=>"updated_at");
        $this->listWhere['equal'] = array();
        $this->listWhere['like'] = array("name","path","url_path");

        $iTotalRecords = $this->_doGetTotalRow();
        log_message('debug','===>getCategoryList: '.$this->db->last_query());
        log_message('debug','===>getCategoryList post :  '.print_r($this->input->post(),true));

        $iDisplayLength = intval($this->input->post('iDisplayLength'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($this->input->post('iDisplayStart'));
        $sEcho = intval($this->input->post('sEcho'));

        $records = array();
        $records["aaData"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);

        log_message('debug','===>getCategoryList: '.$this->db->last_query());
        $no=$iDisplayStart;
        foreach($_row->result() as $_result) {
            $records["aaData"][] = array(
                '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                $no=$no+1,
                $_result->category_id,
                $_result->name,
                $_result->path,
                $_result->url_path,
                $_result->updated_at,
                '<a href="'.site_url("sortingtool/config?category_id=".$_result->category_id).'&client='.$client['id'].'" class="btn btn-xs default"><i class="fa fa-cog fa-fw"></i> Config</a> &nbsp; <a href="'.site_url("sortingtool/catalogproduct?category_id=".$_result->category_id).'&client='.$client['id'].'" class="btn btn-xs default"><i class="fa fa-list fa-fw"></i> Product</a>'
            );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;

        return $records;
    }

    public function getCategoryProductList($client="", $category="")
    {
        log_message('debug','getCategoryProductList . post : '.print_r($this->input->post(),true));
        log_message('debug','getCategoryProductList . client:'.$client['id'].'# cat:'.$category);

        $manualWeightStatus= array(
            0 =>array("Not Active", "danger"),
            1 =>array("Active", "success"),
        );
        $filters['groupby'] = 'product_id';

        $iDisplayLength = intval($this->input->post('iDisplayLength'));
        $iDisplayStart = intval($this->input->post('iDisplayStart'));

        $records = array();
        $records["aaData"] = array();

//        $end = $iDisplayStart + $iDisplayLength;
//        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $sort = array(1=>'id',  2=>'product_id',5=>'position',6=>'score',7=>'created_at', 8=>'result_index',9=>'stock',10=>'manual_weight',11=>'updated_at');
        $iSortingCols = $this->input->post("iSortingCols");
        if( !empty($iSortingCols) ) {
            for($i=0; $i<$iSortingCols; $i++) {
                $colId = $this->input->post("iSortCol_{$i}");
                $col = $sort[$colId];
                $dir = $this->input->post("sSortDir_{$i}");
                $filters['orderby'] = array($col,$dir);
            }
        }


        $filter = array('sku'=>'like','manual_weight'=>'equal','sku_description'=>'like');
        if( $this->input->post("sAction") == "filter" ) {
            foreach($filter as $key=>$type){
                $val = $this->input->post($key);
                if(isset($val) && $val != ""){
                    $filters['filter'][$key]['value'] = $val;
                    $filters['filter'][$key]['type'] = $type;
                }
            }
        }

        $this->load->model('catalog_m');
        $iTotalRecords = $this->catalog_m->getCatalogCategoryProduct($client,$category,$filters)->num_rows();

        $filters['limit'] = array('limit'=>$iDisplayLength,'offset'=>$iDisplayStart);
        $_row = $this->catalog_m->getCatalogCategoryProduct($client,$category,$filters);

        $no=$iDisplayStart;
        foreach($_row->result() as $_result) {
            $status=$manualWeightStatus[$_result->manual_weight];
            $records["aaData"][] = array(
                '<input type="checkbox" name="product_id[]" value="'.$_result->product_id.'">',
                $no=$no+1,
                $_result->product_id,
                $_result->sku,
                $_result->sku_description,
                $_result->position,
                (!empty($_result->score) ? $_result->score : 0),
                date('Y-m-d',strtotime($_result->created_at)),
                (!empty($_result->result_index) ? $_result->result_index : ""),
                (int) $_result->stock,
                '<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
                $_result->updated_at,
                ''

//
//                '<a href="'.site_url("sortingtool/categorylist/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-list fa-fw"></i> Category</a>',
////                '<a href="'.site_url("sortingtool/view/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-list fa-fw"></i> Category</a>',

            );
        }

        $records['iTotalRecords'] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;

        return $records;
    }

    public function updateManualWeight($clientId, $categoryId, $ids = array(), $status)
    {
        $producTable = $this->tableMageCategoryProduct."_".$clientId;
        foreach($ids as $_productId){
            $this->db->where('product_id', $_productId);
            $this->db->where('category_id', $categoryId);
            $this->db->update($producTable, array("manual_weight" => $status));
        }
    }

}