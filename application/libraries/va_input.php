<?php
class Va_input {
	var $fields = array();
	var $groupFields = array();
	private $_CI = null;
	private $_group = "";
	private $_groupedForm = FALSE;
	private $_groupList = array();
	
	function __construct($data) {
		$this->_CI =& get_instance();
		$this->_CI->load->helper("inflector");
		$this->_group = $data['group'];
	}
	
	public function addInput( $conf = array() ) {
		if(!is_array($conf)) {
			return $this;
		}
		
		$input = $this->_prepareBasicField($conf);
		$input["type"] = "text";
		
		
		$this->fields[] = $input;
		
		return $this;
	}
	
	public function addTextarea( $conf = array() ) {
		if(!is_array($conf)) {
			return $this;
		}
	
		$input = $this->_prepareBasicField($conf);
		$input["type"] = "textarea";
	
		$this->fields[] = $input;
	
		return $this;
	}
	
	public function addPassword( $conf = array() ) {
		if(!is_array($conf)) {
			return $this;
		}
		
		$input = $this->_prepareBasicField($conf);
		$input["type"] = "password";
		
		$this->fields[] = $input;
		
		return $this;
	}
	
	public function addCheckbox( $conf = array() ) {
		if(!is_array($conf)) {
			return $this;
		}
		
		$select = array("type" => "checkbox");
		$select['id'] = $select['name'] = $conf['name'];
		
		if(isset($conf['id'])) {
			$select['id'] = $conf['id'];
		}
		
		if(isset($conf['label'])) {
			$select['label'] = $conf['label'];
		} else {
			$select['label'] = humanize($conf['name']);
		}
		
		if(isset($conf['help'])) {
			$select['help'] = $conf['help'];
		} else {
			$select['help'] = "";
		}
		
		if(isset($conf['value'])) {
			$select['value'] = $conf['value'];
		} else {
			$select['value'] = "";
		}
		
		if(isset($conf['list']) && sizeof($conf['list']) ) {
			$select['list'] = $conf['list'];
		}
		
		if(isset($conf['msg'])) {
			$select['msg'] = $conf['msg'];
		} else {
			$select['msg'] = "";
		}
		
		$this->fields[] = $select;
		
		return $this;
	}
	
	public function addHidden( $conf = array() ) {
		if(!is_array($conf)) {
			return $this;
		}
	
		$input = $this->_prepareBasicField($conf);
		$input["type"] = "hidden";
	
		$this->fields[] = $input;
	
		return $this;
	}
	
	public function addCustomField( $conf = array() ) {
		if(!is_array($conf)) {
			return $this;
		}
		
		$input = $this->_prepareBasicField($conf);
		if(isset($conf['list']) && sizeof($conf['list']) ) {
			$input['list'] = $conf['list'];
		}
		$input["type"] = "custom";
		$input["view"] = $conf['view'];
		
		$this->fields[] = $input;
		
		return $this;
	}
	
	public function addSelect( $conf = array() ) {
		if(!is_array($conf)) {
			return $this;
		}
		
		$select = array("type" => "select");
		$select['id'] = $select['name'] = $conf['name'];
		
		if(isset($conf['id'])) {
			$select['id'] = $conf['id'];
		}
		
		if(isset($conf['label'])) {
			$select['label'] = $conf['label'];
		} else {
			$select['label'] = humanize($conf['name']);
		}
		
		if(isset($conf['help'])) {
			$select['help'] = $conf['help'];
		} else {
			$select['help'] = "";
		}
		
		if(isset($conf['value'])) {
			$select['value'] = $conf['value'];
		} else {
			$select['value'] = "";
		}
		
		if(isset($conf['list']) && sizeof($conf['list']) ) {
			$select['list'] = $conf['list'];
		}
		
		if(isset($conf['msg'])) {
			$select['msg'] = $conf['msg'];
		} else {
			$select['msg'] = "";
		}
		
		$this->fields[] = $select;
		
		return $this;
	}
	
	private function _prepareBasicField($conf) {
		$input = array();
	
		$input['id'] = $input['name'] = $conf['name'];
	
		if(isset($conf['id'])) {
			$input['id'] = $conf['id'];
		}
	
		if(isset($conf['label'])) {
			$input['label'] = $conf['label'];
		} else {
			$input['label'] = humanize($conf['name']);
		}
	
		if(isset($conf['help'])) {
			$input['help'] = $conf['help'];
		} else {
			$input['help'] = "";
		}
	
		if(isset($conf['placeholder'])) {
			$input['placeholder'] = $conf['placeholder'];
		} else {
			$input['placeholder'] = "";
		}
	
		if(isset($conf['value'])) {
			$input['value'] = $conf['value'];
		} else {
			$input['value'] = "";
		}
	
		if(isset($conf['msg'])) {
			$input['msg'] = $conf['msg'];
		} else {
			$input['msg'] = "";
		}
	
		return $input;
	}
	
	public function renderField() {
		$html = "";
		if($this->_groupedForm) {
			foreach($this->groupFields as $id => $field) {
				$html .= '<div class="tab-pane '.($id == 0 ? 'active' : '').'" id="tab_'.$id.'"><div class="form-body">';
				$html .= $this->_doRenderField($field);
				$html .= '</div></div>';
			}
		} else {
			$html = $this->_doRenderField($this->fields);
		}
		
		return $html;
	}
	
	private function _doRenderField($fields) {
		$html = "";
		foreach($fields as $field) {
			switch($field['type']) {
				case "text":
					$field['group'] = $this->_group;
					$result = $this->_CI->load->view("form/input", $field, true);
					$html .= $result;
					break;
				case "textarea":
					$field['group'] = $this->_group;
					$result = $this->_CI->load->view("form/textarea", $field, true);
					$html .= $result;
					break;
				case "hidden":
					$field['group'] = $this->_group;
					$result = $this->_CI->load->view("form/hidden", $field, true);
					$html .= $result;
					break;
				case "password":
					$field['group'] = $this->_group;
					$html .= $this->_CI->load->view("form/password", $field, true);
					break;
				case "select":
					$field['group'] = $this->_group;
					$result = $this->_CI->load->view("form/select", $field, true);
					$html .= $result;
					break;
				case "custom":
					$field['group'] = $this->_group;
					$html .= $this->_CI->load->view("form/custom", $field, true);
					break;
				case "checkbox":
					$field['group'] = $this->_group;
					$html .= $this->_CI->load->view("form/checkbox", $field, true);
					break;
			}
		}
		return $html;
	}
	
	public function renderGroupTab() {
		if(!$this->_groupedForm) {
			return "";
		}
		
		$html = array();
		foreach($this->_groupList as $i => $data) {
			$data["index"] = $i; 
			
			if(!isset($data['active'])) $data['active'] = false;
			$html[] = $this->_CI->load->view("form/grouptab", $data);
		}
		
		return implode("\n", $html);
	}
	
	public function getGroup() {
		return $this->_group;
	}
	
	public function setGroupedForm($flag) {
		$this->_groupedForm = $flag;
		
		return $this;
	}
	
	public function setGroupName( $groupList = array() ) {
		if(!is_array($groupList)) {
			$groupList = array($groupList);
		}
		
		foreach($groupList as $i => $v) {
			$this->_groupList[$i]['title'] = $v;
		}
		
		return $this;
	}
	
	public function setActiveGroup($index) {
		if( !sizeof($this->_groupList) || !isset($this->_groupList[$index])){
			return $this;
		}

		$this->_groupList[$index]['active'] = true;
		
		return $this;
	}
	
	public function commitForm($id) {
		$this->groupFields[$id] = $this->fields;
		$this->fields = array();
	}
}
?>