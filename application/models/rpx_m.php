<?php

class Rpx_m extends MY_Model
{
    var $filterSession = "DB_AWB_FILTER";
    var $db = null;
    var $table = 'rpx_awb_mapping';
    var $tableAwb = 'rpx_awb';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $path = "../public/rpx_doc/awb/";

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
        $this->relation = array(
            array("type" => "left", "table" => $this->tableAwb, "link" => "{$this->table}.id_awb  = {$this->tableAwb}.id")
        );

        $this->select = array("{$this->table}.order_no", "{$this->tableAwb}.*");
        $this->filters = array("awb_number" => "awb_number", "order_no" => "order_no");
        $this->load->helper('path');
    }

    public function getInboundList()
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
            if($_result->type == 1){
                $btnAction='
                    <a href="'.site_url("rpx/edit/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-edit" ></i> Edit</a>
                    <a href="'.site_url("rpx/delete/".$_result->id).'" onClick="return deletechecked()" class="btn btn-xs default"  ><i class="fa fa-trash-o"></i>Delete<a>
                ';
                $records["aaData"][] = array(
                    '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                    $no=$no+1,
                    $_result->awb_number,
                    $_result->order_no,
                    $_result->created_at,
                    $btnAction
                );
            }
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

        $data = $this->extractCsv($post['userfile']);

        if(empty($msg)) {
            $this->db->insert_batch($this->tableAwb, $data);
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

    public function extractCsv($namaFile){
        $file = fopen($this->path.$namaFile,"r");
        $dataRow = array();
        while(! feof($file))
        {
            $arrData = fgetcsv($file);
            if(strlen($arrData[0]) != strlen($arrData[1])) return false;
            $awbFrom = (int)$arrData[0];
            $awbTo = (int)$arrData[1];
            if($awbFrom > $awbTo) return false;
            $prefix = preg_replace("/[^A-Z]+/", "", $arrData[0]);
            for($x=$awbFrom;$x<=$awbTo;$x++){
                $noAWB = $prefix.$this->_retZeroAWBFormat($prefix,strlen($arrData[0]),$x).$x;
                $dataRow[] = array($noAWB);
            }
            //sampai sini
        }

        fclose($file);

        return $dataRow;
    }

}