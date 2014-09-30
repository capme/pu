<?php
class Changepassword_m extends MY_Model {
	var $table = 'auth_users';
	var $pkField = "pkUserId";
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
	}
	
	public function getUser( $pkUserId ) 
	{
		return $this->db->get_where($this->table, array($this->pkField => $pkUserId));
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
		} 
		else {
			return $msg;
		}
	}
}