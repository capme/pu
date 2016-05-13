<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Sortingtool
 * @property Sortingtool_m $sortingtool_m
 */

class Sortingtool extends MY_Controller {
    var $data = array();
    public function __construct()
    {
        parent::__construct();
        $this->load->model("sortingtool_m");
        $this->load->model("client_m");
        $this->load->model("clientoptions_m");
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
        $this->data['formTitle'] = "View Catalog Category";

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

        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Sorting Tool";
        $this->data['formTitle'] = "Manage Catalog Category Product";
        $this->data['breadcrumb'] = array("Sorting Tool"=>"", "Manage Sorting Tool" => "", "Manage Category"=> "");
        $this->load->library("va_input", array("group" => "sortingtool"));

        $this->va_input->addHidden( array("name" => "method", "value" => "update") );
        $this->va_input->addHidden( array("name" => "client_id", "value" => $client) );
        $this->va_input->addHidden( array("name" => "category_id", "value" => $id) );

        $value = $data->result_array();
        foreach($value as $itemRows){
            $this->va_input->addSelect( array("name" => "manualweight_".$itemRows['product_id'],"label" => "", "list" => array(0=>"Not Active", 1 =>"Active"), "value" => @$itemRows['manual_weight']));
        }

        $this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("layout/customManage.php");
        $this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
        $this->load->view('template', $this->data);
    }

    public function save(){
        $post = $this->input->post("sortingtool");
        if($post['method'] == "update") {
            $clientid = $post['client_id'];
            foreach ($post as $keyItemPost => $itemPost) {
                if (strstr($keyItemPost, "manualweight")) {
                    $tmp = explode("_", $keyItemPost);
                    $data[$tmp[1]]['manualweight'] = $itemPost;
                }
            }

            $result = $this->sortingtool_m->manageCategory($clientid, $data, $category_id=$post['category_id']);
            if (is_numeric($result)) {
                redirect("sortingtool/view/" . $post['client_id'] . "");
            } else {
                $this->session->set_flashdata(array("sortingtoolError" => json_encode(array("msg" => $result, "data" => $post))));
                redirect("sortingtool/viewcategory?id=" . $post['id'] . "&client=" . $post['client_id'] . "");
            }
        }
        else {
            redirect("sortingtool");
        }
    }

    public function config(){
        $client=$this->input->get("client");
        $catId=$this->input->get("category_id");

        $clientDetail = $this->clientoptions_m->getClientById($client)->row_array();

        $category = $this->sortingtool_m->getDetailCategory($client,$catId)->row_array();
        $sortingConfig = $this->sortingtool_m->getConfig($client,$catId)->result_array();

        $this->data['content'] = "customForm_v.php";
        $this->data['pageTitle'] = "Sorting Configuration";
        $this->data['breadcrumb'] = array("Sorting Tools"=> "sortingtool","Category"=> "sortingtool/viewcategory/".$client, "Config" => "");
        $this->data['formTitle'] = "Form Score Configuration";
        $this->data['formAction'] = "saveconfig";

        $this->load->library("va_input", array("group" => "sortingtool"));

        $flashData = $this->session->flashdata("categoryError");
        if($flashData !== false) {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        } else {
            $msg = array();
        }

        $this->va_input->addHidden( array("name" => "method", "value" => "update") );
        $this->va_input->addHidden( array("name" => "client_id", "value" => $client) );
        $this->va_input->addHidden( array("name" => "category_id", "value" => $catId) );
        $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client", "label" => "Client Name", "value"=>$clientDetail['client_code'], "disabled"=>"disabled"));

        $this->va_input->addInput( array("name" => "category_name", "placeholder" => "Category Name", "label" => "Category Name", "value"=>$category['name'], "disabled"=>"disabled"));
        $this->va_input->addInput( array("name" => "category_id", "placeholder" => "Category Name", "label" => "Category ID", "value"=>$category['category_id'], "disabled"=>"disabled"));

        $this->va_input->addCustomField( array("name" =>"config", "placeholder" => "Configuration", "label" => "Configuration", "value" =>$sortingConfig, "view"=>"form/customSortingConfig"));
        $this->data['script'] = $this->load->view("script/client_add", array(), true);

        $this->load->view('template', $this->data);
    }

    public function saveconfig(){
        $config = $this->input->post('config');
        $category = $this->input->post('sortingtool')['category_id'];
        $client = $this->input->post('sortingtool')['client_id'];
        if(!empty($config)){
            $update = $this->sortingtool_m->saveConfig($config);
        }

//        redirect("sortingtool/config/?category_id=".$category.'&client='.$client);
        redirect("sortingtool/viewcategory/".$client);
    }

    public function viewcategory($clientid = ""){
        $clientDetail = $this->clientoptions_m->getClientById($clientid)->row_array();

        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Catalog Category (".$clientDetail['client_code'].")";
        $this->data['breadcrumb'] = array("Sorting Tools"=> "sortingtool", "Category" => "");

        $this->sortingtool_m->clearCurrentFilter();
        $this->load->library("va_list");

        $this->va_list->setListName("Category List")->disableAddPlugin()
//            ->setMassAction(array("1" => "Enable", "0" => "Disable"))
            ->setHeadingTitle(array("#", "Category ID","Name","Path","Url Path","Updated at"))
            ->setHeadingWidth(array(2,2,2,2,2,2,2));

        $this->va_list->setInputFilter(1, array("name" => 'category_id'));
        $this->va_list->setInputFilter(2, array("name" => 'name'));
        $this->va_list->setInputFilter(3, array("name" => 'path'));
        $this->va_list->setInputFilter(4, array("name" => 'url_path'));
//        $this->va_list->setDropdownFilter(9, array("name" => 'manual_weight', "option" => $this->getStatus()));

        $this->data['script'] = $this->load->view("script/sortingtool_category_list", array("ajaxSource" => site_url("sortingtool/categorylist?client=".$clientid)), true);

        $this->load->view("template", $this->data);
    }

    public function categorylist($id){


        $client = $this->input->get('client');

        $clientDetail = $this->clientoptions_m->getClientById($client)->row_array();

        $sAction = $this->input->post("sAction");
//        if($sAction == "group_action")
//        {
//            $productIds = $this->input->post("product_id");
//            if(sizeof($productIds) > 0)
//            {
//                $action = $this->input->post("sGroupActionName");
//                $this->sortingtool_m->updateManualWeight($client, $category, $productIds, $action);
//            }
//        }

        $data = $this->sortingtool_m->getCategoryList($clientDetail);
        echo json_encode($data);
    }

    public function catalogproduct(){
        $client = $this->input->get('client');
        $category = $this->input->get('category_id');

        $clientDetail = $this->clientoptions_m->getClientById($client)->row_array();
        $categoryDetail = $this->sortingtool_m->getDetailCategory($client,$category)->row_array();


        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Catalog Category Product (".$clientDetail['client_code'].")";
        $this->data['breadcrumb'] = array("Sorting Tools"=> "sortingtool", "Category" => "sortingtool/viewcategory/".$client, "Product List" => "");

        $this->sortingtool_m->clearCurrentFilter();
        $this->load->library("va_list");

        $this->va_list->setListName("Category Product : ".$categoryDetail['name']." (".$categoryDetail['category_id'].")")->disableAddPlugin()
            ->setMassAction(array("1" => "Active", "0" => "Not Active"))
            ->setHeadingTitle(array("#", "Product ID","SKU","Description","Position","Score","created","Sort","Stock","Manual Weight","Updated at"))
            ->setHeadingWidth(array(2,2,2,2,2,2,2,2,2,2));

        $this->va_list->setInputFilter(2, array("name" => 'sku'));
        $this->va_list->setInputFilter(3, array("name" => 'sku_description'));
        $this->va_list->setDropdownFilter(9, array("name" => 'manual_weight', "option" => $this->getStatus()));

        $this->data['script'] = $this->load->view("script/catalogcategoryproduct_list", array("ajaxSource" => site_url("sortingtool/productlist?client=".$client."&category_id=".$category)), true);

        $this->load->view("template", $this->data);
    }

    public function productlist(){

        $client = $this->input->get('client');
        $category = $this->input->get('category_id');

        $clientDetail = $this->clientoptions_m->getClientById($client)->row_array();

        $sAction = $this->input->post("sAction");
        if($sAction == "group_action")
        {
            $productIds = $this->input->post("product_id");
            if(sizeof($productIds) > 0)
            {
                $action = $this->input->post("sGroupActionName");
                $this->sortingtool_m->updateManualWeight($client, $category, $productIds, $action);
            }
        }
        $data = $this->sortingtool_m->getCategoryProductList($clientDetail,$category);
		echo json_encode($data);
    }
    public function getStatus()
    {
        $status=array(""=>"Select Status",1=>"Active",0=>"Not active");
        return $status;
    }

}