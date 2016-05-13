<?php
class Exportpaypal_m extends MY_Model {
    var $filterSession = "DB_CLIENT_FILTER";
    var $db = null;
    var $table = 'client';
    var $tableAwb = 'awb_queue_printing';
    var $tablePaypal ='paypal_order';
    var $filters = array("id" => "id");
    var $sorts = array(1 => "id");
    var $pkField = "id";


    function __construct(){
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
    }

    public function getClientPaypal()
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
                '<a href="'.site_url("exportpaypal/export/".$_result->id).'" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt"></i> Export</a>',
            );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    public function getOrderPaypal($client){
        $this->db->select('client_code,order_number, paypal_order.items, paypal_order.status,paypal_order.amount, paypal_order.created_at');
        $this->db->from($this->tablePaypal);
        $this->db->join($this->table, $this->table.".id  = ".$this->tablePaypal.".client_id");
        $this->db->join($this->tableAwb, $this->tableAwb.".ordernr = ".$this->tablePaypal.".order_number");
        $this->db->where($this->table.".id", $client);
        return $this->db->get()->result_array();
    }
}