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

    function __construct(){
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
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
                '<a href="'.site_url("sortingtool/view/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-cog fa-fw"></i> Manage</a>',
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

}