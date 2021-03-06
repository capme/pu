<?php

class Rpx_m extends MY_Model
{
    var $filterSession = "DB_AWB_FILTER";
    var $db = null;
    var $table = 'rpx_awb';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $path = "../public/rpx_doc/awb/";

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
        $this->load->library('va_excel');

        $this->select = array("{$this->table}.*");
        $this->filters = array("awb_number" => "awb_number", "order_no" => "order_no", "awb_return" => "awb_return", "pickup_request_no" => "pickup_request_no");
        $this->load->helper('path');
        $this->listWhere['equal'] = array();
        $this->listWhere['like'] = array("awb_number","order_no","awb_return","pickup_request_no");

    }

    public function getRpxList()
    {
        $this->db = $this->load->database('mysql', TRUE);
        $iTotalRecords = $this->_doGetTotalRow();
        $iDisplayLength = intval($this->input->post('iDisplayLength'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($this->input->post('iDisplayStart'));
        $sEcho = intval($this->input->post('sEcho'));

        $records = array();
        $records["aaData"] = array();

        $statList= array(
            "0" =>array("Ready to Send Shipment", "success"),
            "1" => array("Ready to PickupRequest","primary"),
            "2" => array("Complete","warning")
        );


        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);

        $no=0;
        foreach($_row->result() as $_result) {
            $status=$statList[$_result->status];

            $btnAction = "";
            //if($_result->order_no == "" and $_result->awb_return == "" and $_result->pickup_request_no == "") {
            if($_result->status == "0") {
                $btnAction = '
                    <a href="' . site_url("rpx/delete/" . $_result->id) . '" onClick="return deletechecked()" class="btn btn-xs default"  ><i class="fa fa-trash-o"></i>Delete<a>
                    <a href="' . site_url("rpx/shipment?awb=" . $_result->awb_number ."&account_number=" . $_result->account_number) . '" class="btn btn-xs default"  ><i class="glyphicon glyphicon-export"></i>Send Shipment<a>
                ';
            }elseif($_result->status == "1"){
                $btnAction .= '
                    For Pickup Request, <br>use "Pickup Request" <br>option above
                ';
            }else{
                $btnAction = '
                    <a href="' . site_url("rpx/view/" . $_result->id) . '" class="btn btn-xs default"  ><i class="fa fa-truck"></i>View Data Shipping<a>
                ';
            }
            /*
                $btnAction .= '
                    <a href="' . site_url("rpx/shipment?awb=" . $_result->awb_number ."&orderno=" . $_result->order_no) . '" class="btn btn-xs default"  ><i class="glyphicon glyphicon-export"></i>Send Shipment<a>
                    <a href="' . site_url("rpx/pickup?awb=" . $_result->awb_number ."&orderno=" . $_result->order_no) . '" class="btn btn-xs default"  ><i class="glyphicon glyphicon-export"></i>Pickup Request<a>
                ';
            */
                $records["aaData"][] = array(
                    '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                    $no=$no+1,
                    $_result->awb_number,
                    $_result->order_no,
                    $_result->awb_return,
                    $_result->pickup_request_no,
                    '<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
                    $_result->created_at,
                    $btnAction
                );
        }
        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;

    }

    public function saveFile($post)
    {
        $msg = array();
        $user=$this->session->userdata('pkUserId');

        //$data = $this->extractCsv($post['userfile']);
        $data = $this->extractBulkAwb($post['userfile']);

        if(empty($msg)) {
            $this->db->insert_batch($this->table, $data);
            return $this->db->insert_id();
        } else {
            return $msg;
        }
    }

    private function _retZeroAWBFormat($prefix,$strLength,$postfix){
        $lengthPrefix = strlen($prefix);
        $lengthPostfix = strlen($postfix);
        $lupUntil = $strLength - $lengthPrefix - $lengthPostfix;
        $strZero = "";
        for($n=0;$n<$lupUntil;$n++){
            $strZero .= "0";
        }
        return $strZero;
    }

    public function extractBulkAwb($namaFile){
        $file = fopen($this->path.$namaFile,"r");
        $dataRow = array();
        $lup = 0;
        while(! feof($file)) {
            $arrData = fgetcsv($file);
            $lup++;
            if($lup == 1) continue;

            $awb_last_number = $arrData[0];
            $account_number = $arrData[1];

            if($awb_last_number == "" or $account_number == "") continue;
            $dataRow[] = array("awb_number" => $awb_last_number, "status" => "0", "account_number" => $account_number);
        }
        fclose($file);

        return $dataRow;
    }

    public function extractCsv($namaFile){
        $file = fopen($this->path.$namaFile,"r");
        $dataRow = array();
        while(! feof($file))
        {
            $arrData = fgetcsv($file);
            if(strlen($arrData[0]) != strlen($arrData[1])) return false;

            preg_match_all('!\d+!', $arrData[0], $completeAwbFrom);
            preg_match_all('!\d+!', $arrData[1], $completeAwbTo);

            $awbFrom = (int)$completeAwbFrom[0][0];
            $awbTo = (int)$completeAwbTo[0][0];
            if($awbFrom > $awbTo) return false;
            $prefix = preg_replace("/[^A-Z]+/", "", $arrData[0]);
            for($x=$awbFrom;$x<=$awbTo;$x++){
                $noAWB = $prefix.$this->_retZeroAWBFormat($prefix,strlen($arrData[0]),$x).$x;
                $dataRow[] = array("awb_number" => $noAWB, "status" => "0");
            }
        }

        fclose($file);

        return $dataRow;
    }

    public function deleteRpx($id){
        $this->db->delete($this->table, array('id' => $id));
    }

    public function getAWBList($param = null){
        if(is_null($param)){
            $sql = "SELECT * FROM " . $this->table;
        }else {
            $sql = "SELECT * FROM " . $this->table . " WHERE awb_number='".$param."'";
        }
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        $arr = array();
        foreach($rows as $itemRows){
            $arr[$itemRows['awb_number']] = $itemRows['awb_number'];
        }
        return $arr;

    }

    public function getAwbStatus($id){
        $sql = "SELECT * FROM ".$this->table." where id in (".$id.") and (status='0' or status='2')";
        $query = $this->db->query($sql);
        $num = $query->num_rows();
        if($num > 0){
            return false;
        }else{
            return true;
        }
    }

    public function getShippingInfo($id){
        $sql = "SELECT * FROM ".$this->table." where id=".$id;
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        return $rows;
    }

    public function getSumTotalWeight($id){
        $sql = "SELECT sum(total_weight) as jum_total_weight FROM ".$this->table." where id in (".$id.") and status='1'";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        return $rows[0]['jum_total_weight'];
    }

    public function saveAwbReturn($awbReturn, $awb, $orderNo, $total_weight, $paramRpx = array()){
        if(!empty($paramRpx)){
            //param2 rpx
            $strParamRpx = "";
            foreach($paramRpx as $key => $value){
                $strParamRpx .= $key."='".$value."', ";
            }
        }
        $sql = "UPDATE ".$this->table." SET ".$strParamRpx."awb_return='".$awbReturn."', order_no='".$orderNo."', status='1', total_weight='".$total_weight."' WHERE awb_number='".$awb."'";
        $query = $this->db->query($sql);
        return true;
    }

    public function savePickupReturn($pickupReturn, $ids){
        $sql = "UPDATE ".$this->table." SET pickup_request_no='".$pickupReturn."', status='2' WHERE id in ($ids)";
        $query = $this->db->query($sql);
        return true;
    }

}