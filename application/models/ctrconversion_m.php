<?php
/*
* @property Ctrconversion_m $ctrconversion_m
 */
class Ctrconversion_m extends MY_Model {

    var $filterSession = "DB_AWB_FILTER";
    var $db = null;
    var $table = 'ctr';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $filters = array("product_id" => "product_id");

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
    }

    public function getCtrList()
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
                $_result->product_id,
                $_result->ctr,
                $_result->conversion,
                $_result->created_at,
                ''
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

        $data['filename'] = $post['userfile'];

        if(!empty($post['ctr'])) {
            $data['ctr'] = $post['ctr'];
        } else {
            $msg ="ctr required";
        }

        if(!empty($post['product_id'])) {
            $data['product_id'] = $post['product_id'];
        } else {
            $msg ="product id required";
        }

        if(!empty($post['conversion'])) {
            $data['conversion'] = $post['conversion'];
        } else {
            $msg ="conversion required";
        }

        if(empty($msg)) {

            $product_id = array($post['product_id']);
            $this->db->where_in('product_id', $product_id);
            $this->db->delete($this->table);

            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        } else {
            return $msg;
        }
    }
}
