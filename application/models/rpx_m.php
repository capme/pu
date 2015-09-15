<?php

class Rpx_m extends MY_Model
{
    var $db = null;
    var $tableRpx = 'rpx_awb';
    var $tableRpxMapping = 'rpx_awb_mapping';

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
        $this->relation = array(
            array("type" => "left", "table" => $this->tableRpx, "link" => "{$this->tableRpx}.id  = {$this->tableRpxMapping}.id_awb")
        );

        $this->select = array("{$this->tableRpx}.*", "{$this->tableRpxMapping}.*");
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
                    $_result->client_code,
                    $_result->doc_number,
                    $_result->note,
                    '<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
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
}