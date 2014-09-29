<?php
/**
 * 
 * @property Usergroup_m $usergroup_m
 *
 */
class Auth_m extends CI_Model {
	var $db = null;
	var $table = 'auth_users';
	var $allowedModule = array();
	//var $login_timeout = 350; // kelipatan 300 karena $config['sess_time_to_update']	= 300;


	function __construct()
    {
        parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
		$this->load->model("usergroup_m");
    }

	/**
	 * check username and password
	 * set session
	 * return false on error, return user object on success
	 */
	function do_login($username, $password, $remember = false){
		$this->db->where("( (username='".$username."' OR email='".$username."')AND LOWER(password)=LOWER('".sha1(trim($password))."') )");
		$query = $this->db->get($this->table);
		$user = $query->row_array();
		if($user){
			if(empty($user['fullname'])){
				$user['fullname'] = $user['username'];
			}

			$userdata = array(
				'username' => $user['username'],
				//'password' => @$user['password'],
				'loggedin' => TRUE,
				'remember' => $remember,
				'last_activity' => time(),
				'fullname' => $user['fullname'],
				'group' => $user['group'],
				'client' => $user['client'],
			);
			$this->session->set_userdata($userdata);

			// if remember
			if($remember){
				$this->session->sess_expiration = 0; // 3 hours
				$this->session->sess_expire_on_close = TRUE;
				$this->session->sess_update(); //Force an update to the session data
			}

			return $user;
		} else {
			return null;
		}
	}

	function get_user($username){
		$this->db->where('username', $username);
		$query = $this->db->get($this->table);
		$user = $query->row_array();
		(!$user['fullname']) ? $user['fullname'] = $user['username'] : '';
		return $user;
	}

	function is_admin(){
		$session = $this->session->all_userdata();
		$user = $this->get_user(trim($session['username']));
		if( $user['group'] == 'admin' || $user['group'] == '1')  {
			return true;
		}
		return false;
	}
	function is_client(){
		$session = $this->session->all_userdata();
		$user = $this->get_user(trim($session['username']));
		if($user['group'] == 'client')  {
			return true;
		}
		return false;
	}



	function get_session_username(){
		return $this->session->userdata('username');
	}

	function get_session_data()
	{
		return $this->session->all_userdata();
	}


	function update_user($username, $data)
	{

	}

	function is_logged_in()
	{
		$session = $this->session->all_userdata();
		if(@$session['loggedin']){
			return true;
		} else {
			return false;
		}
	}

	function logout()
	{
		$this->session->sess_destroy();
	}

	function is_allowed($currentModule)
	{
		$session = $this->session->all_userdata();
		
		if(!$session) {
			$this->session->set_flashdata('error', 'Session expired. Please re-login.');
			redirect('login?redirect=' . rawurlencode( uri_string() ));
		}
		
		if(empty($currentModule)) {
			return true;
		}
		
		$allowedModule = $this->getAllowedModule();

		if(in_array($currentModule['id'], $allowedModule)) {
			return true;
		}

		return false;
	}
	
	function getAllowedModule() {
		if(empty($this->allowedModule)) {
			$session = $this->session->all_userdata();
			$this->allowedModule = json_decode( $this->usergroup_m->getUserGroup($session['group'])->row_array()['auth_module'] );
		}
		
		return $this->allowedModule;
	}

	/*
	function allow($permissions, $group) {
		$this->allow_group = $group;
		$this->allow_permission = $permissions;
	}
	*/


	function anchor_permission($perm){
		$session = $this->session->all_userdata();

		// get user jadi bisa check group dan permissions
		$user = $this->get_user(trim($session['username']));

		if($user['group'] == 'admin') return true; // admin boleh akses semua

		foreach($this->allows as $allow){
			if($user['group'] == $allow['group'] AND $user[$allow['permission']] == 1) return true;
		}
	}

	function get_all_perms()
	{
		$prefix = "perm_";
		$sql = "DESCRIBE " . $this->table;
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->query($sql);
		$results = $query->result_array();
		$return = array();
		foreach($results as $result){
			if(preg_match('#^'.$prefix.'.*#', $result['Field'])) {
				$return[] =  $result['Field'];
			}
		}
		return $return;	
	}

	function has_permission($perm, $group = null) // digunakan di views buat tampilkan menu atau widget
	{
		$session = $this->session->all_userdata();
		// get user jadi bisa check group dan permissions
		$user = $this->get_user(trim($session['username']));

		if($user['group'] == 'admin') return true; // admin boleh akses semua

		if($perm AND !is_null($group)) {
			foreach($this->allows as $allow){
				if($user['group'] == $allow['group'] AND $user[$allow['permission']] == 1) return true;
			}
		} else if($perm) {
			if($user[$perm] == 1) return true;
		}

		return false;

	}
}