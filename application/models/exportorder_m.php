<?php
class Exportorder_m extends MY_Model {
    var $filterSession = "DB_CLIENT_FILTER";
    var $db = null;
    var $users= 'auth_users';
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
                '<a href="'.site_url("exportorder/export/".$_result->id).'" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt"></i> Export</a>'
            );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    public function getData($client, $period1, $period2){
        $banktransfer=$this->getBankTransfer($client, $period1, $period2);
        $cod=$this->getCod($client, $period1, $period2);
        $paypal=$this->getPaypal($client, $period1, $period2);
        $creditcard= $this->getCreditCard($client, $period1, $period2);
        $clientName= $this->getClientName($client);
        return array($clientName, $banktransfer, $cod, $paypal, $creditcard);
    }

    public function getClientName($client){
       return $this->db->get_where($this->table, array('id'=>$client))->row_array();
    }

    public function getBankTransfer($client, $period1, $period2){
        $this->db->select('client_code, order_number,'.$this->tableBankTransfer.'.amount,'.$this->tableBankTransfer.'.client_id,'.$this->tableBankTransfer.'.updated_at,'.$this->users.'.fullname,'.$this->tableBankTransfer.'.status, '.$this->tableBankTransfer.'.created_at ,'.$this->tableAwb.'.items');
        $this->db->from($this->tableBankTransfer);
        $this->db->join($this->table, $this->table.".id = ".$this->tableBankTransfer.".client_id");
        $this->db->join($this->tableAwb, $this->tableAwb.".ordernr = ".$this->tableBankTransfer.".order_number",  "LEFT");
        $this->db->join($this->users, $this->users.".pkUserId=".$this->tableBankTransfer.".updated_by");
        $this->db->where($this->tableBankTransfer.'.created_at >=',$period1);
        $this->db->where($this->tableBankTransfer.'.created_at <=',$period2);
        $this->db->where($this->table.".id", $client);
        return $this->db->get()->result_array();

    }

    public function getCod($client, $period1, $period2){
        $this->db->select('client_code, order_number,'.$this->tableCod.'.amount,'.$this->tableCod.'.client_id,'.$this->tableCod.'.updated_at,'.$this->tableCod.'.status, '.$this->users.'.fullname,'.$this->tableCod.'.items, '.$this->tableCod.'.created_at');
        $this->db->from($this->tableCod);
        $this->db->join($this->table, $this->table.".id = ".$this->tableCod.".client_id");
        $this->db->join($this->users, $this->users.".pkUserId=".$this->tableCod.".updated_by","LEFT");
        $this->db->where($this->tableCod.'.created_at >=',$period1);
        $this->db->where($this->tableCod.'.created_at <=',$period2);
        $this->db->where($this->table.".id", $client);
        return $this->db->get()->result_array();
    }

    public function getPaypal($client, $period1, $period2){
        $this->db->select('client_code,order_number,'.$this->tablePaypal.'.client_id,'.$this->users.'.fullname,'.$this->tablePaypal.'.items,'.$this->tablePaypal.'.updated_at,'.$this->tablePaypal.'.status, '.$this->tablePaypal.'.amount, '.$this->tablePaypal.'.created_at');
        $this->db->from($this->tablePaypal);
        $this->db->join($this->table, $this->table.".id  = ".$this->tablePaypal.".client_id");
        $this->db->join($this->users, $this->users.".pkUserId=".$this->tablePaypal.".updated_by","LEFT");
        $this->db->where($this->tablePaypal.'.created_at >=',$period1);
        $this->db->where($this->tablePaypal.'.created_at <=',$period2);
        $this->db->where($this->table.".id", $client);
        return $this->db->get()->result_array();
    }

    public function getCreditCard($client, $period1, $period2){
        $this->db->select('client_code, order_number, '.$this->users.'.fullname,'.$this->tableCredit.'.updated_at,'.$this->tableCredit.'.client_id,'.$this->tableCredit.'.amount, '.$this->tableCredit.'.items, '.$this->tableCredit.'.created_at,'.$this->tableCredit.'.status');
        $this->db->from($this->tableCredit);
        $this->db->join($this->table, $this->table.".id = ".$this->tableCredit.".client_id");
        $this->db->join($this->users, $this->users.".pkUserId=".$this->tableCredit.".updated_by", "LEFT");
        $this->db->where($this->tableCredit.'.created_at >=',$period1);
        $this->db->where($this->tableCredit.'.created_at <=',$period2);
        $this->db->where($this->table.".id", $client);
        return $this->db->get()->result_array();
    }

    public function getDescription($client_id, $sku){
        $this->db->select('sku_description');
        $this->db->where('sku_simple', $sku);
        $table = $this->db->get('inv_items_'.$client_id);
        return $table->row_array();
    }
}