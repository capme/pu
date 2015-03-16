<?php
class Config {
	private $_config = array();
	
	function __construct() {
		$this->_config = parse_ini_file( dirname(__FiLE__)."/../config.ini" );
		
		return $this;
	}
	
	public function get($key="", $default="") {
		if(empty($key)) {
			return $this->_config;
		} else if( !isset($this->_config[$key]) ) {
			return $default;
		}
		
		return $this->_config[$key];
	}
	
	public function set($key="", $value="") {
		$this->_config[$key] = $value;
	}
	
	public function getAll() {
		return $this->_config;
	}
	
}