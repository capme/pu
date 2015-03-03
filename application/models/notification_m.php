<?php


/**
 * Class Notification_m
 */
class Notification_m extends MY_Model{

    var $db = null;
    var $table = 'notification';
    var $pkField = "id";
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
    public function getUnreadNotification($group){
        $notif = [];

        if(!empty($group)){
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
     * @param $from : array['name' => '', 'email' => '']
     * @param $to : array['name' => '', 'email' => '']
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
            $this->email->subject('[Automatic] Mail Notification :'.$subject);
            $this->email->message($message);

            $send = $this->email->send();
//            log_message('debug','[cron/notification.sendEmail] : '.$from['email'].'#'.$to['email'].'#'.$subject.'#'.$send);
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