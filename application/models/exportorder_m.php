<?php
class Exportorder_m extends MY_Model {
    var $filterSession = "DB_CLIENT_FILTER";
    var $db = null;
    var $table = 'client';
    var $tableAwb = 'awb_queue_printing';
    var $tablePaypal ='paypal_order';
    var $tableBankTransfer= 'bank_confirmation';
    var $tableCod='cod_confirmation';
    var $tableCredit='creditcard_order';
    var $filters = array("id" => "id");
    var $sorts = array(1 => "id");
    var $pkField = "id";


    function __construct(){
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
    }

    public function getClient()
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
                '<a href="'.site_url("exportorder/exportPaypal/".$_result->id).'" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt"></i> Paypal </a> |
                <a href="'.site_url("exportorder/exportBankTransfer/".$_result->id).'" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt"></i> Bank Transfer</a> |
                <a href="'.site_url("exportorder/exportCod/".$_result->id).'" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt"></i> COD </a> |
                <a href="'.site_url("exportorder/exportCreditCard/".$_result->id).'" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt"></i> Credit Card</a>',
            );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    public function getPaypal($client){
        $this->db->select('client_code,order_number,'.$this->tablePaypal.'.items,'.$this->tablePaypal.'.status, '.$this->tablePaypal.'.amount, '.$this->tablePaypal.'.created_at');
        $this->db->from($this->tablePaypal);
        $this->db->join($this->table, $this->table.".id  = ".$this->tablePaypal.".client_id");
        $this->db->join($this->tableAwb, $this->tableAwb.".ordernr = ".$this->tablePaypal.".order_number");
        $this->db->where($this->table.".id", $client);
        return $this->db->get()->result_array();
    }

    public function getBankTransfer($client){
        $this->db->select('client_code, order_number,'.$this->tableBankTransfer.'.amount,'.$this->tableBankTransfer.'.status, '.$this->tableBankTransfer.'.created_at ,'.$this->tableAwb.'.items');
        $this->db->from($this->tableBankTransfer);
        $this->db->join($this->table, $this->table.".id = ".$this->tableBankTransfer.".client_id");
        $this->db->join($this->tableAwb, $this->tableAwb.".ordernr = ".$this->tableBankTransfer.".order_number");
        $this->db->where($this->table.".id", $client);
        return $this->db->get()->result_array();
    }

    public function getCod($client){
        $this->db->select('client_code, order_number,'.$this->tableCod.'.amount, '.$this->tableCod.'.status, '.$this->tableCod.'.items, '.$this->tableCod.'.created_at');
        $this->db->from($this->tableCod);
        $this->db->join($this->table, $this->table.".id = ".$this->tableCod.".client_id");
        $this->db->join($this->tableAwb, $this->tableAwb.".ordernr = ".$this->tableCod.".order_number");
        $this->db->where($this->tableCod.".status !=0");
        $this->db->where($this->tableCod.".status !=2");
        $this->db->where($this->table.".id", $client);
        return $this->db->get()->result_array();
    }

    public function getCreditCard($client){
        $this->db->select('client_code, order_number, '.$this->tableCredit.'.amount, '.$this->tableCredit.'.items, '.$this->tableCredit.'.created_at,'.$this->tableCredit.'.status');
        $this->db->from($this->tableCredit);
        $this->db->join($this->table, $this->table.".id = ".$this->tableCredit.".client_id" );
        $this->db->join($this->tableAwb, $this->tableAwb.".ordernr = ".$this->tableCredit.".order_number");
        $this->db->where($this->table.".id", $client);
        return $this->db->get()->result_array();
    }
}