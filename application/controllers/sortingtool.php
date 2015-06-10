<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Sortingtool extends MY_Controller {
    var $data = array();
    public function __construct()
    {
        parent::__construct();
        $this->load->model("sortingtool_m");
        $this->load->model("client_m");
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Sorting Tool";
        $this->data['breadcrumb'] = array("Sorting"=>"","Sorting Tool" => "sortingtool");

        $this->sortingtool_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->setListName("Sorting Tool")->disableAddPlugin()
            ->setHeadingTitle(array("Record #", "Client Name"))
            ->setHeadingWidth(array(2,10));

        $this->va_list->setDropdownFilter(1, array("name" => $this->sortingtool_m->filters['id'], "option" => $this->client_m->getClientCodeList(TRUE)));

        $this->data['script'] = $this->load->view("script/sortingtool_list", array("ajaxSource" => site_url("sortingtool/sortingToolList")), true);
        $this->load->view("template", $this->data);
    }

    public function sortingToolList(){
        $data = $this->sortingtool_m->getClientSorting();
        echo json_encode($data);
    }

    public function view($id){
        $data = $this->sortingtool_m->getCatalogById($id);
        if(!is_array($data)) {
            redirect("sortingtool");
        }

        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Sorting Tool";
        $this->data['breadcrumb'] = array("Sorting Tool"=> "sortingtool", "Manage Sorting Tool" => "");
        $this->data['formTitle'] = "Manage Sorting Tool";

        $this->load->library("va_input", array("group" => "sortingtool"));
        $flashData = $this->session->flashdata("sortingtoolError");
        if($flashData !== false) {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        } else {
            $msg = array();
            for($a=0; $a < count($data[1]); $a++){
                $data[1][$a]['client_id']=$data[2];
            }
            $value=$data[1];
        }

        $this->va_input->addHidden( array("name" => "method", "value" => "update") );
        $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name ", "value" => @$data[0]['client_code'],"disabled"=>"disabled") );
        $this->va_input->addCustomField( array("name" =>"options", "placeholder" => "Catalog Category", "label" => "Catalog Category", "value" =>$value, "view"=>"form/customSorting"));
        $this->data['script'] = $this->load->view("script/client_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function manage(){
        $client=$this->input->get("client");
        $id=$this->input->get("category_id");
        $data = $this->sortingtool_m->getCategory($id, $client);
        if($data->num_rows() < 1) {
            redirect("sortingtool");
        }

        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Inbound Document";
        $this->data['formTitle'] = "Inbound Document - Update Attribute";
        $this->data['breadcrumb'] = array("Inbound Document"=> "");
        $this->load->library("va_input", array("group" => "sortingtool"));

        $this->va_input->addHidden( array("name" => "method", "value" => "update") );
        $this->va_input->addHidden( array("name" => "id", "value" => $id) );
        $this->va_input->addHidden( array("name" => "client_id", "value" => $client) );
        $value = $data->result_array();
        foreach($value as $itemRows){
            $this->va_input->addSelect( array("name" => "position_".$itemRows['id'],"label" => "", "list" => array(0=>"Non Active", 1 =>"Active"), "value" => @$itemRows['position']) );
            $this->va_input->addSelect( array("name" => "manualweight_".$itemRows['id'],"label" => "", "list" => array(0=>"Non Active", 1 =>"Active"), "value" => @$itemRows['manual_weight']));
        }

        $this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("layout/customManage.php");
        $this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
        $this->load->view('template', $this->data);
    }

    public function viewcategory(){
        $client=$this->input->get("client");
        $id=$this->input->get("category_id");
        $data = $this->sortingtool_m->getCategory($id, $client);
        if($data->num_rows() < 1) {
            redirect("sortingtool");
        }

        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Sorting Tool";
        $this->data['breadcrumb'] = array("Sorting Tool"=> "", "View Sorting" => "");
        $this->data['formTitle'] = "View Sorting Tool";

        $this->load->library("va_input", array("group" => "sortingtool"));
        $flashData = $this->session->flashdata("sortingtoolError");
        if($flashData !== false) {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        } else {
            $msg = array();
            $value = $data->result_array();
        }
        $this->va_input->addCustomField( array("name" =>"catalogcatagoryproduct", "placeholder" => "Catalog Category Product", "label" => "Catalog Category Product", "value" =>$value, "view"=>"form/customCatalogCategoryProduct"));
        $this->data['script'] = $this->load->view("script/client_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function save(){
        $post = $this->input->post("sortingtool");
        if($post['method'] == "update") {
            $clientid = $post['client_id'];
            foreach ($post as $keyItemPost => $itemPost) {
                if (strstr($keyItemPost, "position")) {
                    $tmp = explode("_", $keyItemPost);
                    $data[$tmp[1]]['position'] = $itemPost;
                }

                if (strstr($keyItemPost, "manualweight")) {
                    $tmp = explode("_", $keyItemPost);
                    $data[$tmp[1]]['manualweight'] = $itemPost;
                }
            }

            $result = $this->sortingtool_m->manageCategory($clientid, $data);
            if (is_numeric($result)) {
                redirect("sortingtool/view/" . $post['client_id'] . "");
            } else {
                $this->session->set_flashdata(array("sortingtoolError" => json_encode(array("msg" => $result, "data" => $post))));
                redirect("sortingtool/viewcategory?id=" . $post['id'] . "&client=" . $post['client_id'] . "");
            }
        }
    }
}