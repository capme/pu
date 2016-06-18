<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property leger_m
 * @property Va_list $va_list
 * @property Va_input $va_input
 * @property Area_m $area_m
 *
 */
class Area extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("area_m") );
        $this->load->library('va_excel');
	}
	
	public function index(){
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "";
		$this->data['breadcrumb'] = array("List Area"=>"area");
		
		$this->area_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->setListName("Area")
			->setHeadingTitle(array("Record #", "Kode Area","Tipe Area","Nama Area"))
			->setHeadingWidth(array(2, 2,2,4));
		
		$this->va_list->setInputFilter(1, array("name" => $this->area_m->filters['kode_area']));
        $this->va_list->setInputFilter(3, array("name" => $this->area_m->filters['nama_area']));

		$this->data['script'] = $this->load->view("script/Area_list", array("ajaxSource" => site_url("area/AreaList")), true);
		$this->load->view("template", $this->data);
	}
	
	public function AreaList(){
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
			}
		}	
		$data = $this->area_m->getAreaList();
		echo json_encode($data);
	}
    
    public function delete($id){
        $data = $this->area_m->deleteArea($id);
    	redirect('ringkasandata');
    }
    
	public function save(){
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			redirect("area");
		}
		$post = $this->input->post("area");
		if(empty($post)) {
			redirect("area");
		}

		if($post['method'] == "area") {
			redirect("area");
		}
	}

    public function add(){
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "";
        $this->data['formTitle'] = "Area";
        $this->data['breadcrumb'] = array("Area"=> "area/addArea");
        $this->load->library("va_input", array("group" => "area"));

        $this->va_input->addHidden( array("name" => "method", "value" => "area") );
        $this->va_input->addInput( array("name" => "kode_area", "maxlength" => "5", "size" => "5", "label" => "Kode Area") );
		$dataList = array(
			"" => "-- pilih tipe area --",
			"provinsi" => "provinsi",
			"kabupatenkota" => "Kabupaten / Kota",
			"kecamatan" => "Kecamatan",
			"desakelurahan" => "Desa / Kelurahan"
		);
		$this->va_input->addSelect( array("name" => "tipe","label" => "Tipe Area", "list" => $dataList, "value" => "") );
		$this->va_input->addInput( array("name" => "nama", "maxlength" => "40", "size" => "30", "label" => "Nama Area") );

        $this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
        $this->load->view('template', $this->data);

    }

}
?>
