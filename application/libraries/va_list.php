<?php
class Va_list {
	private $addDisabled = false;
	private $addUrl = "";
	private $addMethod = "add";
	private $addLabel = "New Record";
	
	private $massAction = array();
	
	private $headingTitle = array();
	private $headingWidth = array();
	private $headingFilter = array();
	
	
	private $listName = "";
	private $_CI = null;

	function __construct() {
		$this->_CI =& get_instance();
		$this->_buildAddUrl();
	}
	
	public function disableAddPlugin() {
		$this->addDisabled = true;
		
		return $this;
	}
	
	public function enableAddPlugin() {
		$this->addDisabled = false;
		
		return $this;
	}
	
	public function isAddPluginActive() {
		return !$this->addDisabled;
	}
	
	public function isMassActive() {
		return sizeof($this->massAction);
	}
	
	public function renderHeading() {
		$html = array();
		foreach($this->headingTitle as $i => $title) {
			$width = $this->headingWidth[$i];
			$html[] = '<th width="'.$width.'%">'.$title.'</th>';
		}
		
		return implode("", $html);
	}
	
	public function renderFilter() {
		$len = sizeof($this->headingTitle);
		$html = array();
		
		for($i = 0; $i < $len; $i++) {
			if( isset($this->headingFilter[$i]) ) {
				$field = $this->headingFilter[$i];
				switch($field['type']) {
					case "text":
						$result = $this->_CI->load->view("list/filter_input", $field, true);
						break;
					case "select":
						$result = $this->_CI->load->view("list/filter_select", $field, true);
						break;
                    case "daterange":
                        $result = $this->_CI->load->view("list/filter_daterange", $field, true);
                        break;
					default:
						$result = "";
						break;
				}
				
				$html[] = "<td>".$result."</td>";
			} else {
				$html[] = "<td></td>";
			}
		}
		
		$html[--$i] = "<td>".$this->_CI->load->view("list/filter_button", array(), true)."</td>";
		
		return implode("", $html);
	}
	
	// SETTER 
	public function setListName($name) {
		$this->listName = $name;
		
		return $this;
	}
	
	public function setAddMethod( $method ) {
		$this->addMethod = $method;
		$this->_buildAddUrl();
		
		return $this;
	}
	
	public function setAddLabel($text) {
		$this->addLabel = $text;
		
		return $this;
	}
	
	public function setMassAction( $action = array() ) {
		foreach($action as $key => $label) {
			$this->massAction[$key] = $label;
		}
		
		return $this;
	}
	
	public function setHeadingTitle($titles = array()) {
		$this->headingTitle[] = '<input type="checkbox" class="group-checkable">';
		foreach($titles as $title) {
			$this->headingTitle[] = $title;
		}
		$this->headingTitle[] = "Actions";
		
		return $this;
	}
	
	public function setHeadingWidth( $widths = array() ) {
		$this->headingWidth[] = 5;
		foreach($widths as $width) {
			$this->headingWidth[] = $width;
		}
		$this->headingWidth[] = 10;
		
		return $this;
	}
	
	public function setInputFilter($index, $data = array()) {
		$index++;
		$config = array();
		$config["type"] = "text";
		$config["name"] = $data["name"];
		
		$this->headingFilter[$index] = $config;

		return $this;
	}

    public function setDateFilter($index, $data = array()) {
        $index++;
        $config = array();
        $config["type"] = "daterange";
        $config["name"] = $data['name'];
        $this->headingFilter[$index] = $config;

        return $this;
    }
	
	public function setDropdownFilter($index, $data = array()) {
		$index++;
		$config = array();
		$config["type"] = "select";
		$config["name"] = $data["name"];
		$config["option"] = $data["option"];
	
		$this->headingFilter[$index] = $config;
	
		return $this;
	}
	
	// GETTER
	public function getAddUrl() {
		return $this->addUrl;
	}
	
	public function getListName() {
		return $this->listName;
	}
	
	public function getAddLabel() {
		return $this->addLabel;
	}
	
	public function getMassAction() {
		return $this->massAction;
	}
	
	private function _buildAddUrl() {
		$this->addUrl = site_url( $this->_CI->router->class . "/" . $this->addMethod);
	}
	
	
}