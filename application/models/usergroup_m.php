<?php
class Usergroup_m extends MY_Model {
	var $filterSession = "DB_USER_FILTER";
	var $db = null;
	var $table = 'auth_group';
	var $filters = array("username" => "username", "group" => "group", "client" => "client","status"=>"status");
	var $sorts = array(1 => "id");
	var $aFilters = array();
	var $id = "id";
	//var $login_timeout = 350; // kelipatan 300 karena $config['sess_time_to_update']	= 300;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
	}
	
	public function getGroupList() 
	{
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
		$statusList = array(
				0 => array("Not Active", "danger"),
				1 => array("Active", "success")
		);
		$no=0;
		foreach($_row->result() as $_result) 
		{		
		$status = $statusList[$_result->status];
		$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$_result->name,
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
					'<a href="'.site_url("usergroup/viewusergroup/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-search"></i> View</a>					
					<a href="'.site_url("usergroup/deleteusergroup/".$_result->id).'" onClick="return deletechecked()" class="btn btn-xs default"  ><i class="fa fa-trash-o"></i>Delete<a>');
					
		}	
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
	
		return $records;
	}
	
	public function getUserGroup($id)
	{
	//return $this->db->get_where($this->table, array($this->id => $id));
	$this->db->where(array($this->id=>$id));
	$this->db->from($this->table);	
	return $this->db->get();
	 
	}
	
	public function deleteUserGroup($id)
	{
	$this->db->where(array($this->id => $id));
	$this->db->delete($this->table);
	}
	
	public function newGroup($post)
	{
		$msg = array();	
		if(!empty($post['group'])) 
		{
			$data['name'] = $post['group'];
		} 
		if(!empty($post['status'])) {
			$data['status'] = $post['status'];
		} else {
			$data['status'] = 0;
		}
		
		if( isset($post['modules']) && sizeof($post['modules'])) {
			$this->load->model("modulemanagement_m");
			$parentMap = $this->modulemanagement_m->getStructuredModule(true);
			$authModule = array();
			foreach($post['modules'] as $mId) {
				$authModule[] = $mId;
				$parent = $parentMap[$mId];
				while($parent > 0) {
					$authModule[] = $parent;
					$parent = $parentMap[$parent];
				}
			}
				
			$data['auth_module'] = json_encode( $authModule );
		} else {
			$data['auth_module'] = json_encode( array() );
		}
				
		if(empty($msg)) 
		{
			
			$this->db->insert($this->table, $data);			
			return $this->db->insert_id();			
			//$this->db->insert($this->table, $data);
		} 
		else 
		{
			return $msg;
		}
	}
	
	public function groupList()
	{
	$grouplist=$this->db->get_where($this->table, array("status" => 1));
	return $grouplist->result_array();
	}
	
	public function updateGroup( $post ) 
	{
		$msg = array();
	
		if(!empty($post['name'])) 
		{
			$data['name'] = $post['name'];
		} 
		if(!empty($post['status'])) {
			$data['status'] = $post['status'];
		} else {
			$data['status'] = 0;
		}
		
		if( isset($post['modules']) && sizeof($post['modules'])) {
			$this->load->model("modulemanagement_m");
			$parentMap = $this->modulemanagement_m->getStructuredModule(true);
			$authModule = array();
			foreach($post['modules'] as $mId) {
				$authModule[] = $mId;
				$parent = $parentMap[$mId];
				while($parent > 0) {
					$authModule[] = $parent;
					$parent = $parentMap[$parent];
				}
			}
			
			$data['auth_module'] = json_encode( $authModule );
		} else {
			$data['auth_module'] = json_encode( array() );
		}
			
		
		if(empty($msg)) 
		{
			$this->db->where($this->id, $post['id']);
			$this->db->update($this->table, $data);
			
		} else {
			return $msg;
		}
	}
	
	public function massUpdate($ids = array(), $status)
	{
		$this->db->where_in($this->id, $ids);
		if( $status == 2 ) 
		{
			$this->db->delete($this->table);
		} 
		else 
		{
			$this->db->update($this->table, array("status" => $status));
		}		
	}
	
	

	
}