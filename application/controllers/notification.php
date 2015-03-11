<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Notification
 * @property Notification_m $notification_m
 */
class Notification extends MY_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model("users_m");
        $this->load->model("notification_m");
    }
    
    public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Notification";
		$this->data['breadcrumb'] = array("Operation"=> "", "Notification" => "");
	
		$this->notification_m->clearCurrentFilter();
	
		$this->load->library("va_list");
		$this->va_list->disableAddPlugin()->setListName("Notification")->setMassAction(array("2" => "Remove")
		)->setHeadingTitle(array("Record #", "Sender", "Message","Time")
		)->setHeadingWidth(array(5, 10, 10,10,5,5)
		);
					
		$this->data['script'] = $this->load->view("script/viewnotification_list", array("ajaxSource" => site_url("notification/notificationList")), true);
		$this->load->view("template", $this->data);
	}
    
    public function notificationList(){
        $sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
				$this->notification_m->removeNotification($id, $action);
			}
		}	
		$data = $this->notification_m->getNotificationList();	
		echo json_encode($data);
    }

    public function read(){
        $ids = $this->input->get('ids');
        $url = $this->input->get('url');
        $doc = $this->input->get('doc');
        $id = $this->input->get('id');        
        $urls=$url."&doc=".$doc."&id=".$id;
       
        $this->notification_m->setAsRead($ids);
        redirect($urls);
    }
}