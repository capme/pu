<?php
class Autocancel_m extends MY_Model {
    var $filterSession = "DB_USER_FILTER";
    var $db = null;
    var $table = 'expired_order';
    var $sorts = array(1 => "expired_date");
    var $pkField = "id";
    var $status=array("cancel"=>2,"pending cancel"=>0);
    var $tableClient ='client';

    function __construct(){
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
        $this->relation = array(
            array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField} and {$this->table}.status != 1")
        );

        $this->select = array(
            "{$this->table}.{$this->pkField}",
            "{$this->table}.order_number",
            "{$this->table}.expired_date",
            "{$this->table}.status",
            "{$this->tableClient}.client_code",
            "{$this->table}.created_date",
            "{$this->table}.order_method",
            "{$this->table}.client_id",
        );

        $this->filters = array(
            "status"=>"status",
            "order_number"=>"order_number",
            "client_id"=>"client_id"
        );
        $this->listWhere['equal'] = array();
        $this->listWhere['like'] = array("order_number");
    }

    public function getReadyToCancelList(){
        $this->db = $this->load->database('mysql', TRUE);
        $iTotalRecords = $this->_doGetTotalRow();
        $iDisplayLength = intval($this->input->post('iDisplayLength'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($this->input->post('iDisplayStart'));
        $sEcho = intval($this->input->post('sEcho'));

        $records = array();
        $records["aaData"] = array();

        $grup=$this->users_m->userList();
        $opsiarray=array();
        foreach($grup as $pkUserId=>$row){
            $opsiarray[$row['pkUserId']]=$row['username'];
        }

        $statList= array(
            0 =>array("Pending Cancel", "warning"),
            1 =>array("Approve", "success"),
            2 =>array("Canceled","danger")
        );

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
        $no=0;
        foreach($_row->result() as $_result) {
            $_resultArr = (array)$_result;
            $status=$statList[$_result->status];
            if($_resultArr['status'] == 0){
                $action='<a href="'.site_url("readytocancel/view?id=".$_result->id.'&method='.$_result->order_method.'').'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a> |
                 <a href="'.site_url("readytocancel/update?id=".$_result->id.'&method='.$_result->order_method.'&client='.$_result->client_id.'&order_number='.$_result->order_number.'').'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-check" ></i> Update</a>';
            }
            else{
                $action='<a href="'.site_url("readytocancel/view?id=".$_result->id.'&method='.$_result->order_method.'').'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>';
            }
            $records["aaData"][] = array(
                '',
                $no=$no+1,
                $_result->client_code,
                $_result->order_number,
                $_result->created_date,
                $_result->expired_date,
                $_result->order_method,
               '<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
                $action
            );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    public function update($post){
        $this->db->where("order_method",$post['order_method']);
        $this->db->where("id",$post['id']);
        $this->db->update($this->table, array("expired_date"=>$post['date']));
        return $post['id'];
    }

    public function getData($id, $method){
        $this->db->select('*');
        $this->db->where('id', $id);
        $this->db->where('order_method', $method);
        return $this->db->get($this->table)->row_array();
    }

    public function getOrder(){
        return $this->db->get($this->table, array("status"=>0))->result_array();
    }

    public function canceled($ordernr, $id){
        $this->db->where('order_number', $ordernr);
        $this->db->where('id', $id);
        $this->db->update($this->table, array('status'=>2));
    }

    public function cekOrder($ordernr, $client_id){
        return $this->db->query("select * from expired_order where order_number= '$ordernr' and client_id = '$client_id'")->row_array();
    }
}
