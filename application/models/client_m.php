<?php
class Client_m extends MY_Model {
	
	var $filterSession = "DB_CLIENT_FILTER";
	var $db = null;
	var $table = 'client';
	var $filters = array("client_code" => "client_code");
	var $sorts = array(1 => "id");
	var $pkField = "id";
	
	function __construct()
    {
        parent::__construct();
    }

	function getClientDetail($client) 
	{
		if(!$client) return array();
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->get_where('client', array('client_code'=>$client));
		$row = $query->row_array();		
		return $row;
	}
	
	public function newClient($post) {		
		$this->db = $this->load->database('mysql', TRUE);
		$msg = array();
	
		if(!empty($post['client_code'])) 
		{
			$status = $this->getClientDetail($post['client_code']);
			if(empty($status))
				$data['client_code'] = $post['client_code'];
			else 
				$msg['client_code'] = "Client name already exists";
		} else {
			$msg['client_code'] = "Invalid client name";
		}
		
		if(!empty($post['mage_auth'])) {
			$data['mage_auth'] = $post['mage_auth'];
		} else {
		}
		
		if(!empty($post['mage_wsdl'])) {
			$data['mage_wsdl'] = $post['mage_wsdl'];
		} else {
		}
	
		if(empty($msg)) {			
			
			$this->db->insert($this->table, $data);			
			$clientId = $this->db->insert_id();
			return $clientId;			
		}
		else {
			return $msg;
		}
	}
	
	public function getClientList() {
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
					$mageUser,
					$_result->mage_wsdl,
					'<a href="'.site_url("clients/view/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-search"></i> View</a>',
			);
		}
	
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
	
		return $records;
	}
	
	public function getClientById( $id )
	{
		$this->db = $this->load->database('mysql', TRUE);
	
		$this->db->where_in($this->pkField, $id);
		return $this->db->get($this->table);		
	}
	
	public function updateClient($post) 
	{
		$this->db = $this->load->database('mysql', TRUE);
		$msg = array();		
		
		if(!empty($post['client_code'])) {
			$data['client_code'] = $post['client_code'];
		} 
		else {
		}
		if(!empty($post['mage_auth'])) {
			$data['mage_auth'] = $post['mage_auth'];
		} 
		else {
		}
		
		if(!empty($post['mage_wsdl'])) {
			$data['mage_wsdl'] = $post['mage_wsdl'];
		} else {
		}
				
		if(empty($msg)) 
		{
			$this->db->where($this->pkField, $post['id']);
			$this->db->update($this->table, $data);
			return $post['id'];
		} 
		else {
			return $msg;
		}
		
	}
	
	public function removeClient($id, $action) 
	{
		$this->db = $this->load->database("mysql", TRUE);
		$this->db->where_in($this->pkField, $id);
		$this->db->delete($this->table);
	}
	
	function getClients()
	{
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->get($this->table);
		return $query->result_array();
	}
	
	function getClientCodeList($withNull = FALSE, $defaultText = "-- Client --") {
		$list = $this->getClients();
		$cList = array();
		if($withNull) {
			$cList["-1"] = $defaultText;
		}
		foreach($list as $d) {
			$cList[$d['id']] = $d['client_code'];
		}
		
		return $cList;
	}
	

}