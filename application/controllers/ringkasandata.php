<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property leger_m
 * @property Va_list $va_list
 * @property Va_input $va_input
 * @property Leger_m $leger_m
 * @property Masterdata_m $masterdata_m
 *
 */
class Ringkasandata extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("leger_m", "masterdata_m") );
        $this->load->library('va_excel');
	}
	
	public function index(){
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "";
		$this->data['breadcrumb'] = array("List Ringkasan Data"=>"ringkasandata");
		
		$this->leger_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->disableAddPlugin()->setListName("Ringkasan Data")
			->setHeadingTitle(array("Record #", "Distribusi Ke","No Lembar","Nama Provinsi"))
			->setHeadingWidth(array(2, 2,2,4));
		
		$this->va_list->setInputFilter(1, array("name" => $this->leger_m->filters['distribusi_ke']));
        $this->va_list->setInputFilter(2, array("name" => $this->leger_m->filters['no_lembar']));
        $this->va_list->setInputFilter(3, array("name" => $this->leger_m->filters['nama_prov']));

		$this->data['script'] = $this->load->view("script/RingkasanData_list", array("ajaxSource" => site_url("ringkasandata/RingkasanDataList")), true);
		$this->load->view("template", $this->data);
	}
	
	public function RingkasanDataList(){
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
			}
		}	
		$data = $this->leger_m->getRingkasanDataList();
		echo json_encode($data);
	}
    
    public function delete(){
		$mod = $_GET['mod'];
		$id = $_GET['id'];
		if($mod == "datateknik"){
			$table = "pu_leger_ringkasan_data_data_teknik";
		}elseif($mod == "identifikasi"){
			$table = "pu_leger_ringkasan_data_identifikasi";
		}elseif($mod == "legalisasi"){
			$table = "pu_leger_ringkasan_data_legalisasi";
		}elseif($mod == "lintasharianrata2"){
			$table = "pu_leger_ringkasan_data_lintas_harian_rata2";
		}elseif($mod == "lokasi"){
			$table = "pu_leger_ringkasan_data_lokasi";
		}elseif($mod == "luaslahanrumija"){
			$table = "pu_leger_ringkasan_data_luas_lahan_rumija";
		}elseif($mod == "perwujudan"){
			$table = "pu_leger_ringkasan_data_perwujudan";
		}
        $data = $this->leger_m->deleteRingkasanData($table, $id);
    	redirect('masterdata');
    }
    
    public function download($id){
        $name=$this->leger_m->getRingkasanDataById($id);
        $base=site_url();
        $data = file_get_contents($base."/inbound/catalog_product/".$name['filename']);
        force_download($name['filename'],$data);       
    }

	public function save(){
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			redirect("ringkasandata");
		}
		$post = $this->input->post("ringkasandata");
		if(empty($post)) {
			redirect("ringkasandata");
		}

		$this->leger_m->saveRingkasanData($post);
		redirect("masterdata");
	}

    public function addIdentifikasi($id){
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "";
        $this->data['formTitle'] = "Ringkasan Data - Identifikasi";
        $this->data['breadcrumb'] = array("Identifikasi"=> "ringkasandata/addIdentifikasi");
        $this->load->library("va_input", array("group" => "ringkasandata"));

		$dataMaster = $this->getDataMasterById($id);

        $this->va_input->addHidden( array("name" => "method", "value" => "identifikasi") );
        $this->va_input->addInputPu( array("name" => "lembar_distribusi_ke", "maxlength" => "1", "size" => "1", "label" => "", "value" => $dataMaster[0]['lembar_distribusi']) );
		$this->va_input->addInputPu( array("name" => "nomer_lembar", "maxlength" => "9", "size" => "9", "label" => "", "value" => $dataMaster[0]['no_leger']) );
		$this->va_input->addInputPu( array("name" => "kode_provinsi", "maxlength" => "2", "size" => "2", "label" => "", "value" => $dataMaster[0]['id_provinsi']) );
		$this->va_input->addInputPu( array("name" => "nama_provinsi", "maxlength" => "40", "size" => "40", "label" => "", "value" => $dataMaster[0]['nama']) );
		$dataList = array(
			"" => "-- pilih tipe dokumen --",
			"asal" => "asal",
			"pemutakhiran1" => "pemutakhiran 1",
			"pemutakhiran2" => "pemutakhiran 2",
			"pemutakhiran3" => "pemutakhiran 3",
			"pemutakhiran4" => "pemutakhiran 4"
		);
		$this->va_input->addSelect( array("name" => "tipe","label" => "", "list" => $dataList, "value" => "") );
		$this->va_input->addInputPu( array("name" => "nomor_panjang_ruas_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "nama_pengenal_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "titik_awal_ruas_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "deskripsi_titik_awal_ruas_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "titik_akhir_ruas_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "deskripsi_titik_akhir_ruas_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "titik_ikat_awal_patok", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "deskripsi_titik_ikat_patok", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "sistem_jaringan_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "peran_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "status_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "kelas_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "pembina_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "tanggal_selesai_diwujudkan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "tanggal_dibuka_untuk_lalin", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "tanggal_ditutup_untuk_lalin", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "tahun", "maxlength" => "4", "size" => "4", "label" => "") );

		$this->va_input->addHidden( array("name" => "id_master_data", "value" => $id) );

		$this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("ringkasandata/addIdentifikasi.php");

        $this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
        $this->load->view('template', $this->data);

    }

	public function addLokasi($id){
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "";
		$this->data['formTitle'] = "Ringkasan Data - Lokasi";
		$this->data['breadcrumb'] = array("Lokasi"=> "ringkasandata/addLokasi");
		$this->load->library("va_input", array("group" => "ringkasandata"));

		$dataMaster = $this->getDataMasterById($id);

		$this->va_input->addHidden( array("name" => "method", "value" => "lokasi") );
		$this->va_input->addInputPu( array("name" => "lembar_distribusi_ke", "maxlength" => "1", "size" => "1", "label" => "", "value" => $dataMaster[0]['lembar_distribusi']) );
		$this->va_input->addInputPu( array("name" => "nomer_lembar", "maxlength" => "9", "size" => "9", "label" => "", "value" => $dataMaster[0]['no_leger']) );
		$this->va_input->addInputPu( array("name" => "kode_provinsi", "maxlength" => "2", "size" => "2", "label" => "", "value" => $dataMaster[0]['id_provinsi']) );
		$this->va_input->addInputPu( array("name" => "nama_provinsi", "maxlength" => "40", "size" => "40", "label" => "", "value" => $dataMaster[0]['nama']) );
		$this->va_input->addCustomField( array("name" =>"lokasi[]", "placeholder" => "Upload File ", "view"=>"form/upload_peta", "label"=>"Provinsi"));
		$this->va_input->addCustomField( array("name" =>"lokasi[]", "placeholder" => "Upload File ", "view"=>"form/upload_peta", "label"=>"Jalan"));
		$this->va_input->addHidden( array("name" => "id_master_data", "value" => $id) );
		$this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("ringkasandata/addLokasi.php");

		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);

	}

	public function addPerwujudan($id){
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "";
		$this->data['formTitle'] = "Ringkasan Data - Perwujudan";
		$this->data['breadcrumb'] = array("Perwujudan"=> "ringkasandata/addPerwujudan");
		$this->load->library("va_input", array("group" => "ringkasandata"));

		$dataMaster = $this->getDataMasterById($id);

		$this->va_input->addHidden( array("name" => "method", "value" => "perwujudan") );
		$this->va_input->addInputPu( array("name" => "lembar_distribusi_ke", "maxlength" => "1", "size" => "1", "label" => "", "value" => $dataMaster[0]['lembar_distribusi']) );
		$this->va_input->addInputPu( array("name" => "nomer_lembar", "maxlength" => "9", "size" => "9", "label" => "", "value" => $dataMaster[0]['no_leger']) );
		$this->va_input->addInputPu( array("name" => "kode_provinsi", "maxlength" => "2", "size" => "2", "label" => "", "value" => $dataMaster[0]['id_provinsi']) );
		$this->va_input->addInputPu( array("name" => "nama_provinsi", "maxlength" => "40", "size" => "40", "label" => "", "value" => $dataMaster[0]['nama']) );
		$dataList = array(
			"" => "-- pilih tipe dokumen --",
			"asal" => "asal",
			"pemutakhiran1" => "pemutakhiran 1",
			"pemutakhiran2" => "pemutakhiran 2",
			"pemutakhiran3" => "pemutakhiran 3",
			"pemutakhiran4" => "pemutakhiran 4"
		);
		$dataListBagian = array(
			"" => "-- pilih tipe perwujudan --",
			"pelaksana" => "pelaksana",
			"cacah" => "cacah",
			"biaya" => "biaya (Rp.1000)",
			"sumberdana" => "sumber dana"
		);
		$this->va_input->addSelect( array("name" => "tipe","label" => "", "list" => $dataList, "value" => "") );
		$this->va_input->addInputPu( array("name" => "tahun", "maxlength" => "4", "size" => "4", "label" => "") );
		$this->va_input->addInputPu( array("name" => "desain", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "pembebasan_lahan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "pembangunan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "peningkatan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "penunjangan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "pemeliharaan_dan_rehab", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "supervisi", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addSelect( array("name" => "tipe_bagian","label" => "", "list" => $dataListBagian, "value" => "") );

		$this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("ringkasandata/addPerwujudan.php");

		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);

	}

	public function addLintasHarianRata2($id){
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "";
		$this->data['formTitle'] = "Ringkasan Data - Lintas Harian Rata-rata";
		$this->data['breadcrumb'] = array("Lintas Harian Rata-rata"=> "ringkasandata/addLintasHarianRata2");
		$this->load->library("va_input", array("group" => "ringkasandata"));

		$dataMaster = $this->getDataMasterById($id);

		$this->va_input->addHidden( array("name" => "method", "value" => "lintas harian rata2") );
		$this->va_input->addInputPu( array("name" => "lembar_distribusi_ke", "maxlength" => "1", "size" => "1", "label" => "", "value" => $dataMaster[0]['lembar_distribusi']) );
		$this->va_input->addInputPu( array("name" => "nomer_lembar", "maxlength" => "9", "size" => "9", "label" => "", "value" => $dataMaster[0]['no_leger']) );
		$this->va_input->addInputPu( array("name" => "kode_provinsi", "maxlength" => "2", "size" => "2", "label" => "", "value" => $dataMaster[0]['id_provinsi']) );
		$this->va_input->addInputPu( array("name" => "nama_provinsi", "maxlength" => "40", "size" => "40", "label" => "", "value" => $dataMaster[0]['nama']) );
		$dataList = array(
			"" => "-- pilih tipe dokumen --",
			"asal" => "asal",
			"pemutakhiran1" => "pemutakhiran 1",
			"pemutakhiran2" => "pemutakhiran 2",
			"pemutakhiran3" => "pemutakhiran 3",
			"pemutakhiran4" => "pemutakhiran 4"
		);

		$this->va_input->addSelect( array("name" => "tipe","label" => "", "list" => $dataList, "value" => "") );
		$this->va_input->addInputPu( array("name" => "tahun", "maxlength" => "4", "size" => "4", "label" => "") );
		$this->va_input->addInputPu( array("name" => "sepeda_motor", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "mobil_pribadi", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "mobil_penumpang", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "mobil_barang", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "bis_kecil", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "bis_besar", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "truk_2_sumbu_kecil", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "truk_2_sumbu_besar", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "truk_3_sumbu_atau_lebih", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "truk_dengan_gandengan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "truk_semi_trailer", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "kendaraan_tidak_bermotor", "maxlength" => "40", "size" => "30", "label" => "") );

		$this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("ringkasandata/addLintasHarianRata2.php");

		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);

	}

	public function addLuasLahanRumija($id){
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "";
		$this->data['formTitle'] = "Ringkasan Data - Luas Lahan Rumija";
		$this->data['breadcrumb'] = array("Luas Lahan Rumija"=> "ringkasandata/addLuasLahanRumija");
		$this->load->library("va_input", array("group" => "ringkasandata"));

		$dataMaster = $this->getDataMasterById($id);

		$this->va_input->addHidden( array("name" => "method", "value" => "luas_lahan_rumija") );
		$this->va_input->addInputPu( array("name" => "lembar_distribusi_ke", "maxlength" => "1", "size" => "1", "label" => "", "value" => $dataMaster[0]['lembar_distribusi']) );
		$this->va_input->addInputPu( array("name" => "nomer_lembar", "maxlength" => "9", "size" => "9", "label" => "", "value" => $dataMaster[0]['no_leger']) );
		$this->va_input->addInputPu( array("name" => "kode_provinsi", "maxlength" => "2", "size" => "2", "label" => "", "value" => $dataMaster[0]['id_provinsi']) );
		$this->va_input->addInputPu( array("name" => "nama_provinsi", "maxlength" => "40", "size" => "40", "label" => "", "value" => $dataMaster[0]['nama']) );
		$dataList = array(
			"" => "-- pilih tipe dokumen --",
			"asal" => "asal",
			"pemutakhiran1" => "pemutakhiran 1",
			"pemutakhiran2" => "pemutakhiran 2",
			"pemutakhiran3" => "pemutakhiran 3",
			"pemutakhiran4" => "pemutakhiran 4"
		);

		$this->va_input->addSelect( array("name" => "tipe","label" => "", "list" => $dataList, "value" => "") );
		$this->va_input->addInputPu( array("name" => "tahun", "maxlength" => "4", "size" => "4", "label" => "") );
		$this->va_input->addInputPu( array("name" => "luas", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "data_perolehan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "njop", "maxlength" => "40", "size" => "30", "label" => "") );

		$this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("ringkasandata/addLuasLahanRumija.php");

		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);

	}

	public function addDataTeknik($id){
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "";
		$this->data['formTitle'] = "Ringkasan Data - Data Teknik";
		$this->data['breadcrumb'] = array("Data Teknik"=> "ringkasandata/addDataTeknik");
		$this->load->library("va_input", array("group" => "ringkasandata"));

		$dataMaster = $this->getDataMasterById($id);

		$this->va_input->addHidden( array("name" => "method", "value" => "data_teknik") );
		$this->va_input->addInputPu( array("name" => "lembar_distribusi_ke", "maxlength" => "1", "size" => "1", "label" => "", "value" => $dataMaster[0]['lembar_distribusi']) );
		$this->va_input->addInputPu( array("name" => "nomer_lembar", "maxlength" => "9", "size" => "9", "label" => "", "value" => $dataMaster[0]['no_leger']) );
		$this->va_input->addInputPu( array("name" => "kode_provinsi", "maxlength" => "2", "size" => "2", "label" => "", "value" => $dataMaster[0]['id_provinsi']) );
		$this->va_input->addInputPu( array("name" => "nama_provinsi", "maxlength" => "40", "size" => "40", "label" => "", "value" => $dataMaster[0]['nama']) );
		$dataList = array(
			"" => "-- pilih tipe dokumen --",
			"asal" => "asal",
			"pemutakhiran1" => "pemutakhiran 1",
			"pemutakhiran2" => "pemutakhiran 2",
			"pemutakhiran3" => "pemutakhiran 3",
			"pemutakhiran4" => "pemutakhiran 4"
		);
		$dataListBagian = array(
			"" => "-- pilih tipe data teknik --",
			"km" => "KM",
			"m2" => "M2"
		);
		$this->va_input->addSelect( array("name" => "tipe","label" => "", "list" => $dataList, "value" => "") );
		$this->va_input->addSelect( array("name" => "tipe_bagian","label" => "", "list" => $dataListBagian, "value" => "") );
		$this->va_input->addInputPu( array("name" => "tahun", "maxlength" => "4", "size" => "4", "label" => "") );

		$this->va_input->addInputPu( array("name" => "a_tanah", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "a_kerikil", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "a_aspal_beton", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "a_aspal_lainnya", "maxlength" => "40", "size" => "30", "label" => "") );

		$this->va_input->addInputPu( array("name" => "b_belum_ada", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "b_pelayangan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "b_sementara", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "b_semi_permanen", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "b_permanen", "maxlength" => "40", "size" => "30", "label" => "") );

		$this->va_input->addInputPu( array("name" => "c_gorong_gorong", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "c_saluran_permanen", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "c_drainase_bawah_tanah", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "c_manhole", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "c_riol", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "c_bangunan_penahan_tanah", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "c_kerb", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "c_penutup_lereng", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "c_krib", "maxlength" => "40", "size" => "30", "label" => "") );

		$this->va_input->addInputPu( array("name" => "d_pagar_pengaman", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_dinding_pengaman", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_patok_pemandu", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_patok_kilometer", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_patok_hektometer", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_patok_leger_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_patok_rumija", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_marka_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_rambu_lalin", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_lampu_lalin", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_lampu_penerangan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_jembatan_penyebrangan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_shelter_bis", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_cermin_jalan", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "d_lainnya", "maxlength" => "40", "size" => "30", "label" => "") );

		$this->va_input->addInputPu( array("name" => "e_prasarana_air", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_prasarana_listrik", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_prasarana_listrik_dalam_tanah", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_prasarana_telepon", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_prasarana_telepon_dalam_tanah", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_prasarana_minyak", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_prasarana_gas", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_prasarana_hidran", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_prasarana_rumah_kabel", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_prasarana_lainnya", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_sarana_air", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_sarana_listrik", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_sarana_listrik_dalam_tanah", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_sarana_telepon", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_sarana_telepon_dalam_tanah", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_sarana_minyak", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_sarana_gas", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_sarana_hidran", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_sarana_rumah_kabel", "maxlength" => "40", "size" => "30", "label" => "") );
		$this->va_input->addInputPu( array("name" => "e_sarana_lainnya", "maxlength" => "40", "size" => "30", "label" => "") );

		$this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("ringkasandata/addDataTeknik.php");

		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);

	}

	public function addLegalisasi($id){
		//sampai sini
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "";
		$this->data['formTitle'] = "Ringkasan Data - Legalisasi";
		$this->data['breadcrumb'] = array("Data Teknik"=> "ringkasandata/addLegalisasi");
		$this->load->library("va_input", array("group" => "ringkasandata"));

		$dataMaster = $this->getDataMasterById($id);

		$this->va_input->addHidden( array("name" => "method", "value" => "legalisasi") );
		$this->va_input->addInputPu( array("name" => "lembar_distribusi_ke", "maxlength" => "1", "size" => "1", "label" => "", "value" => $dataMaster[0]['lembar_distribusi']) );
		$this->va_input->addInputPu( array("name" => "nomer_lembar", "maxlength" => "9", "size" => "9", "label" => "", "value" => $dataMaster[0]['no_leger']) );
		$this->va_input->addInputPu( array("name" => "kode_provinsi", "maxlength" => "2", "size" => "2", "label" => "", "value" => $dataMaster[0]['id_provinsi']) );
		$this->va_input->addInputPu( array("name" => "nama_provinsi", "maxlength" => "40", "size" => "40", "label" => "", "value" => $dataMaster[0]['nama']) );

	}

	private function getDataMasterById($id){
		$list = $this->masterdata_m->getMasterDataById($id);
		return $list;
	}
}
?>
