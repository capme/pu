<?php
/*
* @property Ctrconversion_m $ctrconversion_m
 */
class Sizechart_m extends MY_Model {

    var $filterSession = "DB_AWB_FILTER";
    var $db = null;
    var $table = 'brand_size_import';
    var $tableMap='brand_size_map';
    var $tableClient ='client';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $status=array("cancel"=>2,"approve"=>1);
    var $path = "";

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
        $this->relation = array(
            array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField} ")
        );

        $this->select = array("{$this->table}.*", "{$this->tableClient}.client_code");
        $this->filters = array("client_id"=>"client_id");
    }

    public function getSizeChartList(){
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
                '',
                $no=$no+1,
                $_result->client_code,
                $_result->notes,
                $_result->created_at,
                '<a href="'.site_url("sizechart/export/".$_result->client_id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-download" ></i> Export</a> |
                <a href="'.site_url("sizechart/delete/".$_result->client_id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Delete</a> |
                <a href="'.site_url("sizechart/view/".$_result->client_id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> View</a>'
            );
        }
        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    public function saveFile($post) {
        $msg=array();
        $data['brand_code']=$post['brand_code'];
            if(!empty($post['attribute_set'])) {
                $data['attribute_set'] = $post['attribute_set'];
            } else {
                $msg="attribute set is required";
            }

            if(!empty($post['brand_size'])) {
                $data['brand_size'] = $post['brand_size'];
            } else {
                $msg ="brand_size is required";
            }

            if(!empty($post['brand_size_system'])) {
                $data['brand_size_system'] = $post['brand_size_system'];
            } else {
                $msg="brand size system is required";
            }

            if(!empty($post['paraplou_size'])) {
                $data['paraplou_size'] = $post['paraplou_size'];
            } else {
                $msg="paraplou size is required";
            }

            if(!empty($post['position'])) {
                $data['position'] = $post['position'];
            } else {
                $msg="position is required";
            }
            if (!empty($post['client_id'])) {
                $data['client_id'] = $post['client_id'];
            } else {
            }


        if(empty($msg)) {
            $this->db->insert($this->tableMap, $data);
            return $post['client_id'];
        } else {
            return $msg;
        }
    }

    public function saveImport($post, $brand_code){
        return $this->db->insert($this->table, array('client_id'=>$post['client_id'], 'brand_code'=>$brand_code,'filename'=>$post['userfile'], 'notes'=>$post['note']));
    }

    public function deleteTemp($id){
        $this->db->where('client_id', $id);
        $this->db->delete($this->tableMap);
    }

    public function export($client_id){
        $this->db->select('brand_code,attribute_set, brand_size, brand_size_system, paraplou_size, position');
        $this->db->from($this->tableMap);
        $this->db->where('client_id', $client_id);
        return $this->db->get();
    }

    public function delete($client_id){
        $query = $this->db->get_where($this->table, array('client_id' => $client_id))->row_array();
        $path = BASEPATH .'../public/mechandising/size_chart/'.$query['filename'];
        unlink($path);

        $this->db->trans_start();
        $this->db->where('client_id', $client_id);
        $this->db->delete($this->tableMap);
        $this->db->where('client_id', $client_id);
        $this->db->delete($this->table);
        $this->db->trans_complete();
    }

    public function cekAvailable($brand_code){
        return $this->db->get_where($this->table, array('brand_code'=>$brand_code))->row();
    }

    public function cekMap($client_id){
        return $this->db->get_where($this->table, array('client_id'=>$client_id))->row();
    }

    public function getSizeChartById($client_id){
        return $this->db->get_where($this->tableMap, array('client_id'=>$client_id));
    }
}
