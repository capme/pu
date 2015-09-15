<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* @property Rpx_m $rpx_m
 */
class Rpx extends MY_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model( array("rpx_m") );
        $this->load->library("rpx_lib");
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "RPX AWB";
        $this->data['breadcrumb'] = array("RPX AWB"=>"Manage");

        $this->rpx_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->setListName("RPX")->setAddLabel("Upload AWB")
            ->setHeadingTitle(array("Record #", "AWB Number","Order Number","Created At"))
            ->setHeadingWidth(array(2, 2,2,3,2,4));

        $this->va_list->setInputFilter(2, array("name" => $this->rpx_m->filters['awb_number']));
        $this->va_list->setInputFilter(3, array("name" => $this->rpx_m->filters['order_no']));

        $this->data['script'] = $this->load->view("script/Rpx_list", array("ajaxSource" => site_url("rpx/RpxList")), true);
        $this->load->view("template", $this->data);
    }

    public function RpxList(){
        $sAction = $this->input->post("sAction");
        if($sAction == "group_action") {
            $id = $this->input->post("id");
            if(sizeof($id) > 0) {
                $action = $this->input->post("sGroupActionName");
            }
        }
        $data = $this->rpx_m->getInboundList();
        echo json_encode($data);
    }

}