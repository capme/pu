<?php
class Managecampaign_m extends MY_Model {
    var $filterSession = "DB_USER_FILTER";
    var $db = null;
    var $table = 'manage_campaign';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $tableClient ='client';

    function __construct(){
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
        $this->relation = array(
                array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}"));

        $this->select = array(
            "{$this->table}.{$this->pkField}",
            "{$this->table}.sku_simple",
            "{$this->table}.brand_code",
            "{$this->table}.start_date",
            "{$this->table}.end_date",
            "{$this->table}.discount_absorb",
            "{$this->table}.campaign",
            "{$this->table}.status",
            "{$this->tableClient}.client_code");
        $this->filters = array(
            $this->table.".status"=>$this->table."_status",
            $this->table.".client_id"=>$this->table."_client_id",
            "sku_simple"=>"sku_simple");
    }

    public function getManageCampaign(){
        $this->db = $this->load->database('mysql', TRUE);
        $iTotalRecords = $this->_doGetTotalRow();
        $iDisplayLength = intval($this->input->post('iDisplayLength'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($this->input->post('iDisplayStart'));
        $sEcho = intval($this->input->post('sEcho'));
        $records = array();
        $records["aaData"] = array();

        $statList= array(
            0 =>array("not sent to DART yet", "warning"),
            1 =>array("sent to DART", "success")
        );

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;
        $_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);

        $no=0;
        foreach($_row->result() as $_result) {
            $status=$statList[$_result->status];
            $records["aaData"][] = array(
                '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                $no=$no+1,
                $_result->client_code,
                $_result->brand_code,
                $_result->sku_simple,
                $_result->start_date,
                $_result->end_date,
                $_result->discount_absorb,
                $_result->campaign,
                '<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
               '<a href="'.site_url("managecampaign/delete/".$_result->id).'"  enabled="enabled" class="btn btn-xs default" onClick=deletechecked()><i class="fa fa-trash-o" ></i> Delete</a>'
            );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    private function getBrandCode($sku, $client){
        $query = $this->db->get_where('inv_items_'.$client, array('sku_simple' => $sku));
        return $query->row();
    }

    public function getData(){
        return $this->db->get_where($this->table, array("status"=>0))->result_array();
    }

    public function updateCampaign($result){
        foreach($result as $data =>$d){
         $this->db->where('id', $d->id);
         $this->db->update($this->table, array("status"=>1));
        }
    }

    public  function newCampaign($post){
        $msg = array();
        $sku = trim($post['sku']);
        $getbrandcode = $this->getBrandCode($sku, $post['client']);

        if(!empty($getbrandcode)) {
            $bcode = explode(",",$getbrandcode->sku_description);
            $data['brand_code'] = $bcode[0];
        }else {
            $msg['sku'] = "SKU not found in this client";
        }

        $data['sku_simple']=$sku;
        $data['client_id']=$post['client'];
        $data['discount_absorb']=$post['absorb'];
        $data['campaign']=$post['campaign'];
        $data['status']=0;
        $data['start_date']=$post['start_date'];
        $data['end_date']=$post['end_date'];

        if(empty($msg)) {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }else {
            return $msg;
        }
    }

    public function delete($id){
        $this->db->delete($this->table, array('id' => $id));
    }
}
