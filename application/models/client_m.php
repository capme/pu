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

        if(!empty($post['threepl_user'])) {
            $data['threepl_user'] = $post['threepl_user'];
        } else {
        }

        if(!empty($post['threepl_pass'])) {
            $data['threepl_pass'] = $post['threepl_pass'];
        } else {
        }
	
		if(empty($msg)) {			
			
			$this->db->insert($this->table, $data);			
			$clientId = $this->db->insert_id();
            $this->_createInboundTable($clientId);
			return $clientId;			
		}
		else {
			return $msg;
		}
	}

    private function _createInboundTable($cliendId){
        $this->db = $this->load->database('mysql', TRUE);
        $sqlCreateInboundInvItem = "
            CREATE TABLE IF NOT EXISTS `inb_inventory_item_".$cliendId."` (
            `id` int(11) NOT NULL,
              `doc_number` int(11) NOT NULL,
              `sku_config` varchar(30) NOT NULL,
              `sku_simple` varchar(30) NOT NULL,
              `sku_description` text NOT NULL,
              `min` varchar(30) NOT NULL,
              `max` varchar(30) NOT NULL,
              `cycle_count` int(3) NOT NULL,
              `reorder_qty` varchar(30) NOT NULL,
              `inventor_method` varchar(200) NOT NULL,
              `temperature` varchar(30) NOT NULL,
              `cost` varchar(200) NOT NULL,
              `upc` varchar(200) NOT NULL,
              `track_lot` varchar(200) NOT NULL,
              `track_serial` varchar(200) NOT NULL,
              `track_expdate` varchar(200) NOT NULL,
              `primary_unit_of_measure` varchar(200) NOT NULL,
              `packaging_unit` varchar(200) NOT NULL,
              `packaging_uom_qty` varchar(200) NOT NULL,
              `length` varchar(200) NOT NULL,
              `width` varchar(200) NOT NULL,
              `height` varchar(200) NOT NULL,
              `weiight` varchar(200) NOT NULL,
              `qualifiers` varchar(200) NOT NULL,
              `storage_setup` varchar(200) NOT NULL,
              `variable_setup` varchar(200) NOT NULL,
              `nmfc` varchar(200) NOT NULL,
              `lot_number_required` varchar(200) NOT NULL,
              `serial_number_required` varchar(200) NOT NULL,
              `serial_number_must_be_unique` varchar(200) NOT NULL,
              `exp_date_req` varchar(200) NOT NULL,
              `enable_cost` varchar(200) NOT NULL,
              `cost_required` varchar(200) NOT NULL,
              `is_haz_mat` varchar(200) NOT NULL,
              `haz_mat_id` varchar(200) NOT NULL,
              `haz_mat_shipping_name` varchar(200) NOT NULL,
              `haz_mat_hazard_class` varchar(200) NOT NULL,
              `haz_mat_packing_group` varchar(200) NOT NULL,
              `haz_mat_flash_point` varchar(200) NOT NULL,
              `haz_mat_label_code` varchar(200) NOT NULL,
              `haz_mat_flat` varchar(200) NOT NULL,
              `image_url` varchar(200) NOT NULL,
              `storage_count_stript_template_id` varchar(200) NOT NULL,
              `storage_rates` varchar(200) NOT NULL,
              `outbound_mobile_serialization_behavior` varchar(200) NOT NULL,
              `price` varchar(200) NOT NULL,
              `total_qty` varchar(200) NOT NULL,
              `unit_type` varchar(200) NOT NULL,
              `attribute_set` varchar(200) NOT NULL,
              `po_type` varchar(10) NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` datetime DEFAULT NULL,
              `updated_by` int(11) NOT NULL
            ) ENGINE=InnoDB
        ";
        $this->db->query($sqlCreateInboundInvItem);
        $sqlCreateInboundInvStock="
            CREATE TABLE IF NOT EXISTS `inb_inventory_stock_".$cliendId."` (
            `id` int(11) NOT NULL,
              `item_id` int(11) NOT NULL,
              `doc_number` int(11) NOT NULL,
              `reference_num` varchar(30) NOT NULL,
              `quantity` int(11) NOT NULL,
              `bin_location` varchar(30) NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `created_by` int(11) NOT NULL,
              `updated_at` datetime DEFAULT NULL
            ) ENGINE=InnoDB
        ";
        $this->db->query($sqlCreateInboundInvStock);
        $sqlCreateInvItems = "
            CREATE TABLE IF NOT EXISTS `inv_items_".$cliendId."` (
            `id` int(11) NOT NULL,
              `sku_simple` varchar(200) NOT NULL,
              `sku_config` varchar(200) DEFAULT NULL,
              `sku_description` varchar(300) NOT NULL,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `product_id` int(11) DEFAULT NULL,
              `price` int(11) DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `magestock` int(11) DEFAULT NULL
            ) ENGINE=InnoDB
        ";
        $this->db->query($sqlCreateInvItems);
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
                    $_result->threepl_user,
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

        if(!empty($post['threepl_user'])) {
            $data['threepl_user'] = $post['threepl_user'];
        } else {
            $data['threepl_user'] = '';
        }

        if(!empty($post['threepl_pass'])) {
            $data['threepl_pass'] = $post['threepl_pass'];
        } else {
            $data['threepl_pass'] = '';
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

    function getBrandDescriptionList($withNull = FALSE, $defaultText = "-- Client --") {
        $list = $this->getClientBrandesc();
        $cList = array();
        if($withNull) {
            $cList["-1"] = $defaultText;
        }
        foreach($list as $d) {
            $cList[$d['id']] = $d['client_code'];
        }
        return $cList;
    }

    function getClientBrandesc()
    {
        $this->db = $this->load->database('mysql', TRUE);
        $this->db->select('client.id, client.client_code');
        $this->db->from($this->table);
        $this->db->join('client_options','client.id = client_options.client_id');
        $this->db->where(array('client_options.option_name'=>'multi_brand','option_value'=>1));
        return $this->db->get()->result_array();
    }




}