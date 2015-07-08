<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Holiday extends MY_Controller {
    var $data = array();

    public function __construct(){
        parent::__construct();
        $this->load->model("holiday_m");
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Set Holiday";
        $this->data['breadcrumb'] = array("Operation"=> "", "Set Holiday" => "");

        $this->holiday_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->setListName("Holiday Listing")
            ->setAddLabel("Add Holiday")
            ->setHeadingTitle(array("Record #", "Holiday", "Date"))
            ->setHeadingWidth(array(5, 10, 10));

        $this->data['script'] = $this->load->view("script/holiday_list", array("ajaxSource" => site_url("holiday/holidayList")), true);
        $this->load->view("template", $this->data);
    }

    public function holidayList(){
        $data = $this->holiday_m->getHolidayList();
        echo json_encode($data);
    }

    public function add()
    {
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Add Holiday";
        $this->data['breadcrumb'] = array("Operation"=> "", "Holiday" => "");
        $this->data['formTitle'] = "Add Holiday";

        $this->load->library("va_input", array("group" => "holiday"));
        $flashData = $this->session->flashdata("holidayError");

        if($flashData !== false){
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        }
        else{
            $msg = $value = array();
        }

        $this->va_input->addHidden( array("name" => "method", "value" => "new") );
        $this->va_input->addInput( array("name" => "name", "placeholder" => "Name", "help" => "Name of holiday", "label" => "Name *", "value" => @$value['name'], "msg" => @$msg['name']) );
        $this->va_input->addCustomField( array("name" =>"holiday", "placeholder" => "Date", "label" => "Date *", "value" =>$value, "view"=>"form/customHoliday"));
        $this->data['script'] = $this->load->view("script/holiday_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function save(){
        if($_SERVER['REQUEST_METHOD'] != "POST") {
            redirect("holiday/add");
        }
        $post = $this->input->post("holiday");
        if(empty($post)) {
            redirect("holiday/add");
        }

        if($post['method'] == "new") {
            $result = $this->holiday_m->newHoliday( $post );
            if(is_numeric($result)) {
                redirect("holiday");
            } else {
                $this->session->set_flashdata( array("holidayError" => json_encode(array("msg" => $result, "data" => $post))) );
                redirect("holiday/add");
            }

        }
    }

    public function delete($id){
        $this->holiday_m->deleteHoliday($id);
        redirect('holiday');
    }
}