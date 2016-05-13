<?php
class Clientoptions_m extends MY_Model {
	var $filterSession = "DB_USER_FILTER";
	var $db = null;
	var $table = 'client_options';
	var $sorts = array(1 => "id");
	var $pkField = "id";	
	var $tableClient ='client';
	var $filters = array("id" => "id");
	private $_option = array();
	
	const CATALOG_CATEGORY = "catalog_category";
	const CATALOG_PRODUCT_LINK = "catalog_product_link";
	const PRODUCT_ATTRIBUTE = "product_attribute";
	const CLIENT_PRODUCT = "client_product";
	const CLIENT_LIST = "client_list";
	const VELA_CLIENT_ID = "vela_client_id";
	const VELA_3PL_CUSTOMER_NAME = "3pl_customer_name";
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
		$this->relation = array(array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}"));
		$this->select = array("{$this->table}.{$this->pkField}", "{$this->tableClient}.client_code", "{$this->table}.client_id");
		$this->filters = array("client_id"=>"client_id"); 
		$this->group= array ("client_id");
	}
	
	public function getClientOptions() 
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
					'<input type="checkbox" name="id[]" value="'.$_result->client_id.'">',
					$no=$no+1,
					$_result->client_code,
					'<a href="'.site_url("clientoptions/view/".$_result->client_id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>'
			);
		}
	
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
	}
	
	public function getClientById($id){
		$this->db->select('*');
		$this->db->where('id',$id); 
		return $this->db->get($this->tableClient);		
	}
	
	public function getOptions($id){
		$this->db->select('*');		
		$this->db->where('client_id',$id);
		return $this->db->get($this->table)->result_array();
	}
	
	public function clientOptionSave($data){		
		$update['option_value']= $data['value'];		
		$this->db->where($this->pkField, $data['update']);			
		$this->db->update($this->table,$update);		
		return $data['update'];	
	}
	
	public function clientOptionUpdate($data){	
		$update['option_value']=$data['value'];
		$this->db->where($this->pkField, $data['id']);
		$this->db->update($this->table, $update);
		return $data['id'];	
	}
	
	public function clientOptionDelete($data){
		$this->db->delete($this->table, array('id' => $data['delete'])); 
		return $data['delete'];
	}
	
	public function newClientOptions($post){
		$msg = array();        

		if(!empty($post['client'])) {
			$data['client_id'] = $post['client'];
		} else {
		}
        
        if(!empty($post['option_name'])) {
			if ($post['option_name'][0]== null && $post['option_name'][1] != null){
		     $data['option_name']=$post['option_name'][1];
            }
            else if($post['option_name'][0]!= null && $post['option_name'][1]!= null){
             $data['option_name']=$post['option_name'][0];
            }
            else{
              $data['option_name']=$post['option_name'][0];  
            }
           
		} else {
		}
		
		if(!empty($post['option_value'])) {
			$data['option_value'] = $post['option_value'];
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
	
	public function save($clientId, $key, $value) {
		$exists = $this->get($clientId, $key, FALSE);
		$data = array("client_id" => $clientId, "option_name" => $key, "option_value" => $value);
		
		if(!empty($exists)) {
			$this->db->update($this->table, $data, array("id" => $exists['id']));
		} else {
			$this->db->insert($this->table, $data);
		}
		
		return $this;
	}
	
	public function get($clientId, $key, $cache = TRUE) {
		if($cache && isset($this->_option[$clientId][$key])) {
			return $this->_option[$clientId][$key];
		}
		
		$data = $this->db->get_where($this->table, array("client_id" => $clientId, "option_name" => $key))->row_array();
		return $data;
	}
	
	public function gets($clientId, $keys) {
		$this->db->where_in("option_name", $keys);
		$rows = $this->db->get_where($this->table, array("client_id" => $clientId))->result_array();
		$data = array();
		
		foreach($rows as $row) {
			$tmp = json_decode($row['option_value'], true);
			if(json_last_error() == JSON_ERROR_NONE) {
				$data[$row['option_name']] = $tmp;
			} else {
				$data[$row['option_name']] = $row['option_value'];
			}
			
		}
		
		return $data;		
	}

	public function checkOption($clientId, $key) {
		$data = array();
		$rows = $this->db->get_where($this->table, array("client_id" => $clientId, "option_name LIKE " => '%'.$key.'%'))->result_array();
		foreach ($rows as $row) {
			$data[] = $row['option_name'];
		}
		return $data;
	}
	
	public function getVelaClientIdMap() {
		$res = $this->db->get_where($this->table, array("option_name" => self::VELA_CLIENT_ID));
		$map = array();
		
		foreach($res->result_array() as $row) {
			$map[$row['client_id']] = $row['option_value'];
		}
		
		return $map;
	}
	
	public function getCCustomerName($cache = TRUE) {
		if($cache && isset($this->_option[self::VELA_3PL_CUSTOMER_NAME])) {
			return $this->_option[self::VELA_3PL_CUSTOMER_NAME];
		}
		
		$res = $this->db->get_where($this->table, array("option_name" => self::VELA_3PL_CUSTOMER_NAME));
		$map = array();
		
		foreach($res->result_array() as $row) {
			$map[$row['client_id']] = $row['option_value'];
		}
		$this->_option[self::VELA_3PL_CUSTOMER_NAME] = $map;
		
		return $this->_option[self::VELA_3PL_CUSTOMER_NAME];
	}
    
    public function getOptionName(){
        $this->db->select('option_name');
        $this->db->from($this->table);
        $this->db->group_by('option_name');
       return  $this->db->get();
    }

    public function getBrandCode($client){
        $this->db->select('option_value');
        $this->db->from($this->table);
        $this->db->where(array('client_id'=>$client,'option_name'=>'brand_code'));
        return $this->db->get()->result_array();
    }

    public function checkIfBrandIsVirtual($brandInitial){
        $this->db->select('client_id');
        $this->db->from($this->table);
        $this->db->where(array('option_name'=>"brand_initial",'option_value'=>strtoupper($brandInitial)));
        $res = $this->db->get()->result_array();
        $numrows = count($res);
        if($numrows > 0){
            return $res[0]['client_id'];
        }else{
            return -1;
        }
    }
}