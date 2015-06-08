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
        $this->db->select('name, manual_weight, position, sku, url_path,'.$this->tableCatalogCategory."_".$client.'.updated_at,'.$this->tableCatalogCategory."_".$client.'.id');
        $this->db->from($this->tableCatalog."_".$client);
        $this->db->join($this->tableCatalogCategory."_".$client, $this->tableCatalog."_".$client.".category_id=".$this->tableCatalogCategory."_".$client.".category_id");
        $this->db->where($this->tableCatalogCategory."_".$client.".category_id", $id);
        return $this->db->get();
    }

    public function manageCategory($post){
        $this->db = $this->load->database('mysql', TRUE);
        if(!empty($post['position'])) {
            $data['position'] = $post['position'];
        } else {
            $data['position'] = 0;
        }
        if(!empty($post['manual_weight'])) {
            $data['manual_weight'] = $post['manual_weight'];
        } else {
            $data['manual_weight'] = 0;
        }
        $this->db->where($this->pkField, $post['id']);
        $this->db->update($this->tableCatalogCategory."_".$post['client_id'], $data);
        return $post['id'];
    }
}