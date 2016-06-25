<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property masterdata_m
 * @property Va_list $va_list
 * @property Va_input $va_input
 * @property Masterdata_m $masterdata_m
 * @property Area_m $area_m
 */
class Masterdata extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("masterdata_m") );
		$this->load->model( array("area_m") );
        $this->load->library('va_excel');
	}

	public function index(){
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "";
		$this->data['breadcrumb'] = array("List Master Data"=>"masterdata");

		$this->masterdata_m->clearCurrentFilter();

		$this->load->library("va_list");
		$this->va_list->setListName("Master Data")
			->setHeadingTitle(array("Record #", "Lembar distribusi","Provinsi","No. Leger"))
			->setHeadingWidth(array(2, 2,2,4));

		$this->va_list->setInputFilter(1, array("name" => $this->masterdata_m->filters['lembar_distribusi']));
        $this->va_list->setInputFilter(2, array("name" => $this->masterdata_m->filters['nama']));
		$this->va_list->setInputFilter(3, array("name" => $this->masterdata_m->filters['no_leger']));

		$this->data['script'] = $this->load->view("script/Masterdata_list", array("ajaxSource" => site_url("masterdata/MasterdataList")), true);
		$this->load->view("template", $this->data);
	}

	public function MasterdataList(){
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
			}
		}
		$data = $this->masterdata_m->getMasterdataList();
		echo json_encode($data);
	}

    public function delete($id){
        $data = $this->masterdata_m->deleteArea($id);
    	redirect('ringkasandata');
    }

	public function save(){
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			redirect("masterdata");
		}
		$post = $this->input->post("area");
		if(empty($post)) {
			redirect("masterdata");
		}

		if($post['method'] == "area") {
			redirect("masterdata");
		}
	}

    public function add(){
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "";
        $this->data['formTitle'] = "Master Data";
        $this->data['breadcrumb'] = array("Master Data"=> "masterdata/add");
        $this->load->library("va_input", array("group" => "masterdata"));

        $this->va_input->addHidden( array("name" => "method", "value" => "masterdata") );
        $this->va_input->addInput( array("name" => "lembar_distribusi", "maxlength" => "1", "size" => "1", "label" => "Lembar Distribusi") );

		$rawListArea = $this->getIdProvinsiList();
		$dataArea = [];
		foreach($rawListArea as $item){
			$dataArea[$item['kode']] = $item['nama'];
		}

		$this->va_input->addSelect( array("name" => "id_provinsi","label" => "Area Provinsi", "list" => $dataArea, "value" => "") );
		$this->va_input->addInput( array("name" => "no_leger", "maxlength" => "50", "size" => "30", "label" => "No Leger") );
		$dataTipeLegerList = array(
			"" => "-- pilih tipe leger --",
			"ringkasandata" => "Ringkasan Data",
			"jalan" => "Jalan",
			"jembatan" => "Jembatan"
		);
		$this->va_input->addSelect( array("name" => "tipe_leger","label" => "Tipe Master Data", "list" => $dataTipeLegerList, "value" => "") );

        $this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
        $this->load->view('template', $this->data);

    }

	private function getIdProvinsiList(){
		$list = $this->area_m->getAreaData();
		return $list;
	}
}
?>
