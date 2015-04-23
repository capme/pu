<?php


/**
 * Class Notification_m
 */
class Notification_m extends MY_Model{
    var $filterSession = "DB_USER_FILTER";
    var $db = null;
    var $table = 'notification';
    var $pkField = "id";
    var $sorts = array(1 => "id");
    var $tableUsers='auth_users';
    
    var $mailStatus = ['unsend' => 0, 'sent' => 1];
    var $readStatus = ['unread' => 0, 'read' => 1];

    var $mailConfig = [
        'protocol' => 'sendmail',
        'mailpath' => '/usr/sbin/sendmail',
        'charset' => 'iso-8859-',
        'wordwrap' => TRUE
    ];


    public function __construct(){
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
    }

    /**
     * @param $group : auth_user.group
     * @return array
     */
    
    public function getNotificationList(){
        $this->db = $this->load->database('mysql', TRUE);
        $userGroup=$this->session->userdata('group');
        $this->relation = array(array("type" => "inner", "table" => $this->tableUsers, "link" => "{$this->table}.sender_id  = {$this->tableUsers}.pkUserId where group_ids=$userGroup" ));
		$this->select = array("{$this->table}.{$this->pkField}", "{$this->tableUsers}.fullname","{$this->table}.created_at", "{$this->table}.message","{$this->table}.url", "{$this->table}.read");
        
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
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$_result->fullname,
                ($_result->read == 0 ? '<span class="badge badge-roundless badge-important">new</span> ' : '') .$_result->message,
                    $_result->created_at,
                    '<a href="'.site_url("notification/read?id=".$_result->id."&url=".urlencode($_result->url)).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>'
			);
		}
	
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
    }
    
    public function removeNotification($id, $action){
        $this->db = $this->load->database("mysql", TRUE);
		$this->db->where_in($this->pkField, $id);
        $this->db->where('read',1);
		$this->db->delete($this->table);
    }
    
    public function getUnreadNotification($group){
        $notif = [];

        if(!empty($group)){
            $this->db->order_by('id', 'desc');
            $notif = $this->db->get_where($this->table, array('group_ids' => $group, 'read' => $this->readStatus['unread'] ))->result_array();
        }

        return $notif;
    }

    /**
     * @param $id : notification.id
     * @return mixed
     */
    public function setAsRead($id){
        $time=date('Y-m-d H:i:s', now());
        $data['read'] = $this->readStatus['read'];

        $this->db->where($this->pkField, $id);
        $this->db->update($this->table, $data);
        log_message('debug','[notification_m.setAsRead] : id['.$id.'] status['.$this->readStatus['read'].'] updated_at['.$time.']');
        return $id;
    }

    public function getUnsendNotification(){
        $notif = $this->db->get_where($this->table, array('email' => 0));
        return $notif->result_array();
    }

    /**
     * @param $id : notification.id
     * @return mixed
     */
    public function setAsSent($id){
        $time=date('Y-m-d H:i:s', now());
        $data['email'] = $this->mailStatus['sent'];

        $this->db->where($this->pkField, $id);
        $this->db->update($this->table, $data);
        log_message('debug','[notification_m.setAsSent] : id['.$id.'] status['.$this->mailStatus['sent'].'] updated_at['.$time.']');
        return $id;
    }

    /**
     * @param $from : array['fullname' => '', 'email' => '']
     * @param $to : array['fullname' => '', 'email' => '']
     * @param $subject
     * @param $message
     * @return bool
     */
    public function sendEmail($from, $to, $subject, $message){
        if(!empty($from) && !empty($to)){
            $this->load->library('email');

            $this->email->initialize($this->mailConfig);
            $this->email->clear();

            $this->email->from($from['email'], $from['fullname']);
            $this->email->to($to['email']);
            $this->email->subject('[BAYMAX] '.$subject);
            $this->email->message($message);

            $send = $this->email->send();
//            log_message('debug','[cron/notification.sendEmail] : '.$from['email'].'#'.$to['email'].'#'.$subject.'#'.(bool)$send);
//            log_message('debug', 'debug send email' . print_r($this->email->print_debugger(), true));
            return $send;
        } return FALSE;
    }


    /**
     * @param $from : sender id
     * @param $to : group ids
     * @param $url : modules slug
     * @param $message
     * @return mixed
     */
    public  function add($from, $to, $url, $message){

        if( empty($from) || empty($to) ) return '';

        $this->db->trans_start();

        $this->db->insert(
            $this->table,
            array("sender_id" => $from, "url" => $url, "message" => $message, "group_ids" => $to, "read" => $this->readStatus['unread'], "email" => $this->mailStatus['unsend'], "created_at" => date('Y-m-d H:i:s')));

        $this->db->trans_complete();

        $insertedId = $this->db->insert_id();
        return $insertedId;
    }
}