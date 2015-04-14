<?php
/**
 * 
 * @property Usergroup_m $usergroup_m
 *
 */
class Users_m extends MY_Model {
	var $filterSession = "DB_USER_FILTER";
	var $db = null;
	var $table = 'auth_users';
	var $filters = array("username" => "username", "group" => "group", "client" => "client","status"=>"status");
	var $sorts = array(1 => "pkUserId");
	var $aFilters = array();
	var $pkField = "pkUserId";
	//var $login_timeout = 350; // kelipatan 300 karena $config['sess_time_to_update']	= 300;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
	}
	
	/**
	 *
	 * @param array $post
	 * @return mixed
	 */
	public function newUser($post) 
	{
		$msg = array();
	
		if(!empty($post['username'])) 
		{
			$data['username'] = $post['username'];
		} 
		else 
		{
			$msg['username'] = "Invalid username";
		}
	
		if(!empty($post['password'])) {
			$data['password'] = sha1($post['password']);
		} else {
			$msg['password'] = "Invalid password";
		}
	
		if(!empty($post['fullname'])) {
			$data['fullname'] = $post['fullname'];
		} else {
			$msg['fullname'] = "Invalid full name";
		}
		
		if(!empty($post['group'])) {
			$data['group'] = $post['group'];
		} else {
			$msg['group'] = "Invalid group";
		}
	
		if(!empty($post['email'])) {
			$data['email'] = $post['email'];
		} else {
			$msg['email'] = "Invalid email address";
		}
		
		if(!empty($post['active'])) {
			$data['active'] = $post['active'];
		} else {
			$data['active'] = 0;
		}
		
		
		if(empty($msg)) 
		{
			$this->db->insert($this->table, $data);
			return $this->db->insert_id();
		} 
		else 
		{
			return $msg;
		}
	}
	
	public function updateUser( $post ) {
	$msg = array();
	
		if(!empty($post['username'])) {
			$data['username'] = $post['username'];
		} else {
			$msg['username'] = "Invalid username";
		}
	
		if( isset($post['changepass']) && $post['changepass'] == 1 ) {
			if(!empty($post['password'])) {
				$data['password'] = sha1($post['password']);
			} else {
				$msg['password'] = "Invalid password";
			}
		}
	
		if(!empty($post['fullname'])) {
			$data['fullname'] = $post['fullname'];
		} else {
			$msg['fullname'] = "Invalid full name";
		}
		
		if(!empty($post['group'])) {
			$data['group'] = $post['group'];
		} else {
			$msg['group'] = "Invalid group";
		}
	
		if(!empty($post['email'])) {
			$data['email'] = $post['email'];
		} else {
			$msg['email'] = "Invalid email address";
		}
		
		if(!empty($post['active'])) {
			$data['active'] = $post['active'];
		} else {
			$data['active'] = 0;
		}
		
		if(empty($msg)) 
		{
			$this->db->where($this->pkField, $post['pkUserId']);
			$this->db->update($this->table, $data);
			return $post['pkUserId'];
		} 
		else {
			return $msg;
		}
	}
	
	public function getUser( $id ) {
		return $this->db->get_where($this->table, array($this->pkField => $id));
	}
	
	public function massUpdate($ids = array(), $status) {
		$this->db->where_in($this->pkField, $ids);
		if( $status == 2 ) 
		{
			$this->db->delete($this->table);
		} else {
			$this->db->update($this->table, array("active" => $status));
		}
		
	}
	
	public function getUserList() 
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
		//$_groupList = $this->usergroup_m->groupList(); // array(1 => "ADMIN", 2 => ....)
		$grup=$this->usergroup_m->groupList();
		$opsiarray=array();
		foreach($grup as $id=>$row)
		{
		$opsiarray[$row['id']]=$row['name'];
		}
				
		$statusList = array(0 => array("Not Active", "danger"),1 => array("Active", "success"));
		$no=0;
		foreach($_row->result() as $_result) {
			$status = $statusList[$_result->active];
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->pkUserId.'">',
					$no=$no+1,
					$_result->fullname,
					$_result->username,
					$_result->email,
					@$opsiarray[$_result->group],
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
					'<a href="'.site_url("users/view/".$_result->pkUserId).'" class="btn btn-xs default"><i class="fa fa-search"></i> View</a>
					<a href="'.site_url("users/deleteUser/".$_result->pkUserId).'"  onClick="return deletechecked()" class="btn btn-xs default"><i class="fa fa-trash-o"></i> Delete</a>',
			);
		}	
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;	
		return $records;
	}
	public function deleteUser($pkField)
	{
		$this->db->where(array($this-> pkField=> $pkField));
		$this->db->delete($this->table);
	}
	
	public function changePassword( $post )
	{
		$msg = array();
		if(!empty($post['password'])) {
			$data['password'] = sha1($post['password']);
		} else {
			$msg['password'] = "Invalid password";
		}
				
		if(empty($msg)) 
		{
			$this->db->where($this->pkField, $post['pkUserId']);
			$this->db->update($this->table, $data);
			return $post['pkUserId'];
		} 
		else
		{
			return $msg;
		}
		
	}
		public function userList()
	{
		$grouplist=$this->db->get_where($this->table, array("active" => 1));
		return $grouplist->result_array();
	}

    public function userListByGroup($groupId){
        $grouplist=$this->db->get_where($this->table, array("active" => 1, "group" => $groupId));
		return $grouplist->result_array();
    }
}
?>