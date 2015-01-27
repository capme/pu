<?php
class Clientoptions_m extends CI_Model {
	var $table = "client_options";
	private $_mysql = null;
	private $_option = array();
	
	const CATALOG_CATEGORY = "catalog_category";
	const CATALOG_PRODUCT_LINK = "catalog_product_link";
	const PRODUCT_ATTRIBUTE = "product_attribute";
	const CLIENT_PRODUCT = "client_product";
	const CLIENT_LIST = "client_list";
	const VELA_CLIENT_ID = "vela_client_id";
	const VELA_3PL_CUSTOMER_NAME = "3pl_customer_name";
	
	public function __construct() {
		$this->_mysql = $this->load->database("mysql", TRUE);
	}
	
	public function save($clientId, $key, $value) {
		$exists = $this->get($clientId, $key, FALSE);
		$data = array("client_id" => $clientId, "option_name" => $key, "option_value" => $value);
		
		if(!empty($exists)) {
			$this->_mysql->update($this->table, $data, array("id" => $exists['id']));
		} else {
			$this->_mysql->insert($this->table, $data);
		}
		
		return $this;
	}
	
	public function get($clientId, $key, $cache = TRUE) {
		if($cache && isset($this->_option[$clientId][$key])) {
			return $this->_option[$clientId][$key];
		}
		
		$data = $this->_mysql->get_where($this->table, array("client_id" => $clientId, "option_name" => $key))->row_array();
		return $data;
	}
	
	public function gets($clientId, $keys) {
		$this->_mysql->where_in("option_name", $keys);
		$rows = $this->_mysql->get_where($this->table, array("client_id" => $clientId))->result_array();
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
		$rows = $this->_mysql->get_where($this->table, array("client_id" => $clientId, "option_name LIKE " => '%'.$key.'%'))->result_array();
		foreach ($rows as $row) {
			$data[] = $row['option_name'];
		}
		return $data;
	}
	
	public function getVelaClientIdMap() {
		$res = $this->_mysql->get_where($this->table, array("option_name" => self::VELA_CLIENT_ID));
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
		
		$res = $this->_mysql->get_where($this->table, array("option_name" => self::VELA_3PL_CUSTOMER_NAME));
		$map = array();
		
		foreach($res->result_array() as $row) {
			$map[$row['client_id']] = $row['option_value'];
		}
		$this->_option[self::VELA_3PL_CUSTOMER_NAME] = $map;
		
		return $this->_option[self::VELA_3PL_CUSTOMER_NAME];
	}


}