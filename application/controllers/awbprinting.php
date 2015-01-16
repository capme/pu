<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property Awbprinting_m $awbprinting_m
 * @property Va_list $va_list
 * @property Clientoptions_m $clientoptions_m
 *
 */
class Awbprinting extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("users_m", "client_m", "awbprinting_m") );
	}
	
	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "AWB Printing";
		$this->data['breadcrumb'] = array("AWB Printing" => "");
		
		$this->awbprinting_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->setListName("AWB Listing")->setAddLabel("Upload new AWB")
			->setMassAction(array("0" => "Print JNE Format", "2" => "Print NEX Format"))
			->setHeadingTitle(array("Record #", "Client Name","Order Number","Status","Name","Address","City"))
			->setHeadingWidth(array(2, 2,2,3,2,3,4));
		
		$this->va_list->setInputFilter(2, array("name" => $this->awbprinting_m->filters['ordernr']))
			->setDropdownFilter(1, array("name" => $this->awbprinting_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
		$this->va_list->setDropdownFilter(3, array("name" => $this->awbprinting_m->filters['status'], "option" => $this->getStatus()));
		
		$this->data['script'] = $this->load->view("script/awbprinting_list", array("ajaxSource" => site_url("awbprinting/awbPrintingList")), true);	
		$this->load->view("template", $this->data);
	}
	
	public function awbPrintingList()
	{
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
			}
		}	
		$data = $this->awbprinting_m->getAwbPrintingList();	
		echo json_encode($data);
	}
	
	public function doPrintAwb() {
		$courier = $this->input->get("courier");
		$ids = explode(",", $this->input->get("ids"));
		$data['list'] = $this->awbprinting_m->getAwbData($ids);
		
		if($data['list']->num_rows()) {
			$this->awbprinting_m->setAsPrinted($ids);
			$this->load->library("va_pdf");
			if($courier == 0) {
				$this->load->view("awb/print_template_jne", $data);
			} else {
				$this->load->view("awb/print_template_nex", $data);
			}
		} else {
			die("Invalid order number");
		}		
	}
	
	public function view($id)
	{
		$data = $this->awbprinting_m->getAwbPrintingById($id);
		if($data->num_rows() < 1) {
			redirect("awbprinting");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "AWB Printing";
		$this->data['breadcrumb'] = array("AWB Printing"=> "", "View AWB Printing" => "");
		$this->data['formTitle'] = "View AWB Printing";
	
		$this->load->library("va_input", array("group" => "returnorder"));
		$this->va_input->setJustView();
		$flashData = $this->session->flashdata("clientError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value = $data->row_array();
		}
		
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value['client_code'], "msg" => @$msg['client_code']) );
		
		$this->va_input->addInput( array("name" => "sku", "placeholder" => "Order Number", "help" => "Order Number", "label" => "Order Number", "value" => @$value['ordernr'], "msg" => @$msg['ordernr']) );
		$this->va_input->addInput( array("name" => "status", "value" => $this->getStatus()[@$value['status']], "msg" => @$msg['status'], "label" => "Status", "help" => "Status") );		
		$this->va_input->addInput( array("name" => "receiver", "value" => @$value['receiver'], "msg" => @$msg['receiver'], "label" => "Name", "help" => "Receiver") );
		$this->va_input->addInput( array("name" => "company", "value" => @$value['company'], "msg" => @$msg['company'], "label" => "Company", "help" => "Company") );
		$this->va_input->addInput( array("name" => "address", "value" => @$value['address'], "msg" => @$msg['address'], "label" => "Address", "help" => "Address") );
		$this->va_input->addInput( array("name" => "city", "value" => @$value['city'], "msg" => @$msg['city'], "label" => "City", "help" => "City") );
		$this->va_input->addInput( array("name" => "province", "value" => @$value['province'], "msg" => @$msg['province'], "label" => "Province", "help" => "Province") );
		$this->va_input->addInput( array("name" => "country", "value" => @$value['country'], "msg" => @$msg['country'], "label" => "Country", "help" => "Country") );
		$this->va_input->addInput( array("name" => "zipcode", "value" => @$value['zipcode'], "msg" => @$msg['zipcode'], "label" => "ZIP code", "help" => "ZIP code") );
		$this->va_input->addInput( array("name" => "phone", "value" => @$value['phone'], "msg" => @$msg['phone'], "label" => "Phone Number", "help" => "Phone Number") );
		$this->va_input->addInput( array("name" => "created_at", "value" => @$value['created_at'], "msg" => @$msg['created_at'], "label" => "Created At", "help" => "Created At") );
		$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['updated_at'], "msg" => @$msg['updated_at'], "label" => "Updated At", "help" => "Updated At") );
		$this->va_input->addCustomField( array("name" =>"items", "placeholder" => "Items", "label" => "Items", "value" => @$value['items'], "msg" => @$msg['items'], "view"=>"form/customItems"));
		
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function add()
	{
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Operation";
		$this->data['breadcrumb'] = array("AWB Printing"=> "awbprinting", "Upload File" => "");
		$this->data['formTitle'] = "Upload File";
		$this->load->library("va_input", array("group" => "user"));
		
		$flashData = $this->session->flashdata("awbError");
		if($flashData !== false) 
		{
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} 
		else 
		{
			$msg = $value = array();
		}
		$this->va_input->addHidden( array("name" => "method", "value" => "new") );		
		$this->va_input->addInput( array("name" => "name", "placeholder" => "Name", "help" => "Name", "label" => "Name *", "value" => @$value['name'], "msg" => @$msg['name']) );
		$this->va_input->addCustomField( array("name" =>"userfile", "placeholder" => "Upload File ", "value" => @$value['userfile'], "msg" => @$msg['userfile'], "label" => "Upload File *", "view"=>"form/upload_csv"));
		$this->data['script'] = $this->load->view("script/awbprinting_add", array(), true);
		$this->load->view('template', $this->data);
	}	
	
	public function save ()
	{
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			redirect("awbprinting/add");
		}		
		$post = $this->input->post("user");
		if(empty($post)) {
			redirect("awbprinting/add");
		}		
		if($post['method'] == "new"){
			$filename=$this->_uploadFile();
			$post['userfile']= $filename['file_name'] ;
			$hasil=$this->awbprinting_m->awbUploadFile($post);
			if ($filename == null){
				$this->session->set_flashdata( array("awbError" => json_encode(array("msg" => $hasil, "data" => $post))) );
				redirect("awbprinting/add");								 
			}
			
			$data = array();
			$datas = $this->_csvToArray($filename['full_path'], chr(9));
			if(!is_array($datas)) {
				$this->session->set_flashdata( array("awbError" => json_encode(array("msg" => array("userfile" => $datas), "data" => $post))) );
				redirect("/awbprinting/add");
			}
			
			$this->load->model('clientoptions_m');
			$cCustomerName = $this->clientoptions_m->getCCustomerName();
			
			foreach($datas as $k => $d) {
				if( !$d['ReferenceNum'] ){continue;}
				
				$clientId = array_search($d['CustomerName'], $cCustomerName);
				if(!$clientId) {
					continue;
				}
				
				$alamat=$d['ShipToCity'];		
				$pecah = explode(",", $alamat);
				$kota = $pecah[0];
				$provinsi = $pecah[0];
			
				$address = array();
				if($d['ShipToAddress1'] != "0") {$address[] = $d['ShipToAddress1'];}
				if($d['ShipToAddress2'] != "0") {$address[] = $d['ShipToAddress2'];}
				
				$country=$d['ShipToCountry'];
				$negara=array('ID'=>'Indonesia',
				'MY'=>'Malaysia');
				$trim=rtrim($d['SkusAndQtys'][1],'.0)');						
				$data[] = array(
					'ordernr' => $d['ReferenceNum'] ,
					'client_id' => $clientId,
					'receiver' => $d['ShipToName'],
					'company' => $d['ShipToCompanyName'],
					'address' => implode("\n", $address),
					'city' => $kota,
					'province' => @$provinsi,
					'zipcode' => $d['ShipToZip'],
					'country' => @$negara[$country],
					'phone' => $d['ShipToPhone'],
					'items' => serialize( array("name" => $d['SkusAndQtys'][0], 'qty' => $trim, 'weight' => 1) ),
					'shipping_type' => $d['ShipService'],
					'package_type' => 2,
					'reference_file_id' => $hasil,
				);
			}
			
			if ($post['userfile']!= null)
			{			
				$this->awbprinting_m->newData($data);
				$this->_fetchOrderAmount($post['client']);
				redirect("awbprinting");
			}					
		}
			
		
	}
	
	private function _fetchOrderAmount($clientId) {
		$cmd = PHP_BINDIR."/php " . FCPATH . "index.php cron/awb getAmountOrder/".$clientId;
		if (substr(php_uname(), 0, 7) == "Windows"){
			pclose(popen("start /B ". $cmd, "r"));
		} else {
			exec($cmd . " > /dev/null &");
		}
	}

	private function _uploadFile() {
		$return = array('error' => false, 'data' => array());
		$config['upload_path'] = '../webroot/';
		$config['allowed_types'] = 'csv|txt';
		$config['max_size']	= '2000';
		$config['encrypt_name'] = TRUE;
		
		$this->load->library('upload', $config);		
		if ( ! $this->upload->do_upload()) {			
			return null;
		} else {			
			$data=$this->upload->data();
			$dataupload=array('file_name'=>$data['file_name'], 'full_path'=>$data['full_path']);			
			return $dataupload;			
		}
	}
	
	private function _csvToArray($filename='', $delimiter=',')
	{
		if(!file_exists($filename) || !is_readable($filename)) return FALSE;
	
		$header = NULL;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE)
		{
			while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE)
			{
				if(sizeof($row) != 52) {
					return "Error! Invalid file csv format";
				}
				
				if(!$header)
					$header = $row;
				else {							
					$parsing=explode('|', $row['47']);
					foreach(@$parsing as $a)
					{
						$parsing2=explode('(',$a);
						foreach($parsing2 as $b)
						{	
							$row['47']=$parsing2;							
						}
						$data[] = array_combine($header, $row);			
					}
				}
			}
			fclose($handle);
		}
		return $data;
	}

	public function getClient()
	{
		$grup=$this->client_m->getClients();
		$opsi=array(""=>"Select Client");
		foreach($grup as $id=>$row)
		{
		$opsi[$row['id']] = $row['client_code'];
		}
		return $opsi;
	}
	
	private function getStatus() {
		return array(-1 => "", 0 => "New Request", 1 => "Printed");
	}
}
?>