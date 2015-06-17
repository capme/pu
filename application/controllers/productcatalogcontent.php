<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Listinbounddoc
 * @property Productcatalogcontent_m $productcatalogcontent_m
 * @property Clientoptions_m $clientoptions_m
 * @property Inbound_threepl $inbound_threepl
 * @property Mageapi $mageapi
 * @property Va_list $va_list
 */
class Productcatalogcontent extends MY_Controller {
    const TAG = "[Inbound import]";

    var $data = array();
    public function __construct()
    {
        parent::__construct();
        $this->load->model( array("users_m", "client_m", "inbound_m","productcatalogcontent_m") );
        $this->load->library('va_excel');
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Inbound Document";
        $this->data['breadcrumb'] = array("Inbound Document" => "");

        $this->inbounddocument_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->setListName("Inbound Document Listing")->disableAddPlugin()
            ->setHeadingTitle(array("No #", "Client Name","DO Number","Status","Note"))
            ->setHeadingWidth(array(2, 2,2,3,2,2));

        $this->va_list->setDropdownFilter(1, array("name" => $this->productcatalogcontent_m->filters[$this->productcatalogcontent_m->table.'.client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));
        $this->va_list->setInputFilter(2, array("name" => $this->productcatalogcontent_m->filters["doc_number"]));

        $this->data['script'] = $this->load->view("script/inbounddocument_list", array("ajaxSource" => site_url("productcatalogcontent/productCatalogContentList")), true);
        $this->load->view("template", $this->data);
    }

    public function productCatalogContentList()
    {
        $sAction = $this->input->post("sAction");
        if($sAction == "group_action") {
            $id = $this->input->post("id");
            if(sizeof($id) > 0) {
                $action = $this->input->post("sGroupActionName");
            }
        }
        $data = $this->productcatalogcontent_m->getInboundDocumentList();
        echo json_encode($data);
    }

    public function importItemMage(){
        $this->load->library("Mageapi");
        $this->load->model( array("client_m", "productcatalogcontent_m") );

        $client = $_GET['client'];
        $doc = $_GET['doc'];

        $client = $this->client_m->getClientById($client);
        $client = $client->row_array();

        if(!$client['mage_auth'] && !$client['mage_wsdl']) {
            log_message("debug", self::TAG . " Client doesn't had Mage detail");
            die;
        }

        $config = array(
            "auth" => $client['mage_auth'],
            "url" => $client['mage_wsdl']
        );

        $param = $this->productcatalogcontent_m->getParamInboundMage($_GET['client'], $doc);

       if( $this->mageapi->initSoap($config) ) {

            $return = $this->mageapi->inboundCreateItem($param);
            if(is_array($return)){
                $flagError = false;
                $strError = "";
                foreach($return as $itemReturn){
                    if(isset($itemReturn['status']) and $itemReturn['status']=="failed"){
                        $flagError = true;
                        foreach($itemReturn['msg'] as $itemMsg){
                            $strError .= $itemMsg."<br>";
                        }
                    }
                    else if (isset($itemReturn['faultMessage'])){
                        $flagError = true;
                        $strError .= $itemReturn['faultMessage']."<br>";
                    }
                }

                if(!$flagError){
                    redirect("productcatalogcontent");
                }else{
                    echo "Something wrong when calling Mage.<br> ".$strError."<input type='button' value='Back' onclick='window.history.back()'>";
                }
            }
            else{
                echo "Something wrong when calling Mage. See the log file.<input type='button' value='Back' onclick='window.history.back()'>";
            }
        }
    }

}

