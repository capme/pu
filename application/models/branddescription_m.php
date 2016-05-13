<?php
/*
* @property Inbounddocument_m $inbounddocument_m
 */
class Branddescription_m extends MY_Model {

    var $filterSession = "DB_AWB_FILTER";
    var $db = null;
    var $table = 'cnt_brand_description';
    var $tableClient ='client';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $status=array("cancel"=>2,"approve"=>1);
    var $path = "";

    function __construct()
    {
        parent::__construct();
        //$this->load->model( array("inbounddocument_m", "notification_m") );
        $this->db = $this->load->database('mysql', TRUE);
        $this->relation = array(
            array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField} ")
        );
        $this->select = array("{$this->table}.*", "{$this->tableClient}.client_code");
        $this->group=array('client_id');
        $this->filters = array("client_id"=>"client_id");

        $this->load->helper('path');
        $this->load->library('va_excel');
    }

    public function getBrandDescriptionList()
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
                $records["aaData"][] = array(
                    '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                    $no=$no+1,
                    $_result->client_code,
                    $_result->updated_at,
                    '<a href="'.site_url("branddescription/view/".$_result->client_id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>'
                );
        }
        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    public function deleteBrand($id){
        $query = $this->db->get_where($this->table, array('id' => $id));
        $name = $query->row();
        $path = BASEPATH .'../public/content/brand_description/'.$name->filename;
        unlink($path);
        $this->db->delete($this->table, array('id' => $id));
    }

    public function getBrandDescriptionById($id){
        $this->db = $this->load->database('mysql', TRUE);
        $this->db->select('client_code, brand_code, description_id, description_en');
        $this->db->from($this->table);
        $this->db->join($this->tableClient,'client.id = '.$this->table.'.client_id');
        $this->db->where('client_id', $id);
        return $this->db->get();
    }

    public function saveFile($post){

        $msg = array();
        if(!empty($post['userfile'])) {
            $data['filename'] = $post['userfile'];
        } else {}

        if(!empty($post['client'])) {
            $data['client_id'] = $post['client'];
        } else {}

        if(!empty ($post['brand_code'])){
            $data['brand_code'] = $post['brand_code'];
        } else{}

        if(!empty ($post['description'])){
            $data['description_id'] = $post['description'];
        } else{}

        if(!empty ($post['description_en'])){
            $data['description_en'] = $post['description_en'];
        } else{}

        if(empty($msg)) {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        } else {
            return $msg;
        }

    }
}
?>