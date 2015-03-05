<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Notification
 * @property Notification_m $notification_m
 */
class Notification extends MY_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model("notification_m");
    }

    public function read(){
        $id = $this->input->get('id');
        $url = $this->input->get('url');

        $this->notification_m->setAsRead($id);
        redirect($url);
    }
}