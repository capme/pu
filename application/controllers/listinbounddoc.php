<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Listinbounddoc
 * @property Inbounddocument_m $inbounddocument_m
 * @property Clientoptions_m $clientoptions_m
 */
class Listinbounddoc extends MY_Controller {
	const TAG = "[Inbound import]";
	
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("users_m", "client_m", "inbounddocument_m", "inbound_m") );
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
			->setMassAction(array("0" => "Revise", "1" => "Upload Inbound Form"))
			->setHeadingTitle(array("No #", "Client Name","DO Number","Note"))
			->setHeadingWidth(array(2, 2,2,3,2));
		$this->va_list->setDropdownFilter(1, array("name" => $this->inbounddocument_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));
		
		$this->data['script'] = $this->load->view("script/inbounddocument_list", array("ajaxSource" => site_url("listinbounddoc/inboundDocList")), true);	
		$this->load->view("template", $this->data);
	}
	
	public function save(){
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			redirect("listinbounddoc");
		}		
		$post = $this->input->post("listinbounddoc");
		if(empty($post)) {
			redirect("listinbounddoc");
		}		
		
		if($post['method'] == "revise"){
			$filename=$this->_uploadFile($post['listid'], $post['method']);
			if ($filename == null){
				$this->session->set_flashdata( array("listinbounddocError" => json_encode(array("msg" => "Upload failed.", "data" => $post))) );
				redirect("listinbounddoc/revise?ids=".$post['listid']."&command=".$post['command']);								 
			}else{
				//parse excel file
				$return = true;
				foreach($filename as $itemFilename){
					$datas = $this->inbounddocument_m->getInboundDocumentRow($itemFilename);
					$doc_number = $datas['doc_number']; 
					$client_id = $datas['client_id']; 
					$id = $datas['id'];
					$res = $this->_parseFile($itemFilename, $id, $client_id);
					$return = $return && $res;
					unlink($this->inbounddocument_m->path."/tmp_".$itemFilename."_".$id.".xls");
				}
				if(!$return){
					$this->session->set_flashdata( array("listinbounddocError" => json_encode(array("msg" => "Wrong excel file format. Please check your format data.", "data" => $post))) );
					redirect("listinbounddoc/revise?ids=".$post['listid']."&command=".$post['command']);
				}else{
					redirect("listinbounddoc");
				}
			}
		}elseif($post['method'] == "updateattr"){
			$this->_saveAttributeSet($post);
			redirect("listinbounddoc");
		
		}elseif($post['method'] == "uploadinbform"){
				
			$filename=$this->_uploadFile($post['listid'], $post['method']);
			if ($filename == null){
				$this->session->set_flashdata( array("listinbounddocError" => json_encode(array("msg" => "Upload failed.", "data" => $post))) );
				redirect("listinbounddoc/uploadInboundForm?ids=".$post['listid']."&command=".$post['command']);
			}else{
				//saving into table
				foreach($filename as $key => $itemFilename){
					$param_reference_id = $key;
						$datas = $this->inbounddocument_m->getInboundDocumentRow($key);
						$param_doc_number = $datas['doc_number'];
						$param_client_id =  $datas['client_id'];
						$param_note =  $datas['note'];
					
					$param_filename = $itemFilename;
					$param_status = 0;
					$param_type = 2;
					$param_created_by = $user=$this->session->userdata('pkUserId');
					$this->inbounddocument_m->insertInboundDocument($param_doc_number, $param_client_id, $param_note, $param_type, $param_status, $param_created_by, $param_filename, $param_reference_id);
				}
                $command = "cron/extractinboundform run";
                execProcess($command);

				redirect("listinbounddoc");
			}								 
						
		}
	}

    private function _saveAttributeSet($post) {
        foreach($post as $keyItemPost => $itemPost){
            if(strstr($keyItemPost,"attrset")){
                $tmp = explode("_",$keyItemPost);
                $data[$tmp[1]]['attribute_set'] = $itemPost;
            }elseif(strstr($keyItemPost,"upc")){
                $tmp = explode("_",$keyItemPost);
                $data[$tmp[1]]['upc'] = json_encode(explode('|', $itemPost));
            }elseif($keyItemPost == "client"){
                $client = $itemPost;
            }elseif($keyItemPost == "doc"){
                $doc_number = $itemPost;
            }elseif($keyItemPost == "id"){
                $id = $itemPost;
            }
        }
        //updating attribute set
        foreach($data as $keyItemData => $itemData){
            $this->inbounddocument_m->updateAttrSetInboundInventory($client, $doc_number, $itemData, $keyItemData);
        }

        //updating inbound document table
        $this->inbounddocument_m->updateStatusInboundDocumentList($id,2);
    }
	
	private function _validateFile($data){
		if(strtoupper($data[2]['A']) != strtoupper("SKU Code")){
			return false;
		}
		if(strtoupper($data[2]['B']) != strtoupper("SKU Description")){
			return false;
		}
		if(strtoupper($data[2]['C']) != strtoupper("SKU Configs")){
			return false;
		}
		if(strtoupper($data[2]['D']) != strtoupper("Min")){
			return false;
		}
		if(strtoupper($data[2]['E']) != strtoupper("Max")){
			return false;
		}
		if(strtoupper($data[2]['F']) != strtoupper("CycleCount")){
			return false;
		}
		if(strtoupper($data[2]['G']) != strtoupper("ReorderQty")){
			return false;
		}
		if(strtoupper($data[2]['H']) != strtoupper("InventoryMethod")){
			return false;
		}
		if(strtoupper($data[2]['I']) != strtoupper("Temperature")){
			return false;
		}
		if(strtoupper($data[2]['J']) != strtoupper("Cost")){
			return false;
		}
		if(strtoupper($data[2]['K']) != strtoupper("UPC")){
			return false;
		}
		if(strtoupper($data[2]['L']) != strtoupper("Track Lot")){
			return false;
		}
		if(strtoupper($data[2]['M']) != strtoupper("Track Serial")){
			return false;
		}
		if(strtoupper($data[2]['N']) != strtoupper("Track ExpDate")){
			return false;
		}
		if(strtoupper($data[2]['O']) != strtoupper("Primary Unit of Measure")){
			return false;
		}
		if(strtoupper($data[2]['P']) != strtoupper("Packaging Unit")){
			return false;
		}
		if(strtoupper($data[2]['Q']) != strtoupper("Packing UoM QTY")){
			return false;
		}
		if(strtoupper($data[2]['R']) != strtoupper("Length")){
			return false;
		}
		if(strtoupper($data[2]['S']) != strtoupper("Width")){
			return false;
		}
		if(strtoupper($data[2]['T']) != strtoupper("Height")){
			return false;
		}
		if(strtoupper($data[2]['U']) != strtoupper("Weight")){
			return false;
		}
		if(strtoupper($data[2]['V']) != strtoupper("Qualifiers")){
			return false;
		}
		if(strtoupper($data[2]['W']) != strtoupper("Storage Setup")){
			return false;
		}
		if(strtoupper($data[2]['X']) != strtoupper("Variable Setup")){
			return false;
		}
		if(strtoupper($data[2]['Y']) != strtoupper("NMFC#")){
			return false;
		}
		if(strtoupper($data[2]['Z']) != strtoupper("Lot Number Required")){
			return false;
		}
		if(strtoupper($data[2]['AA']) != strtoupper("Serial Number Required")){
			return false;
		}
		if(strtoupper($data[2]['AB']) != strtoupper("Serial Number Must Be Unique")){
			return false;
		}
		if(strtoupper($data[2]['AC']) != strtoupper("Exp Date Req")){
			return false;
		}
		if(strtoupper($data[2]['AD']) != strtoupper("Enable Cost")){
			return false;
		}
		if(strtoupper($data[2]['AE']) != strtoupper("Cost Required")){
			return false;
		}
		if(strtoupper($data[2]['AF']) != strtoupper("IsHazMat")){
			return false;
		}
		if(strtoupper($data[2]['AG']) != strtoupper("HazMatID")){
			return false;
		}
		if(strtoupper($data[2]['AH']) != strtoupper("HazMatShippingName")){
			return false;
		}
		if(strtoupper($data[2]['AI']) != strtoupper("HazMatHazardClass")){
			return false;
		}
		if(strtoupper($data[2]['AJ']) != strtoupper("HazMatPackingGroup")){
			return false;
		}
		if(strtoupper($data[2]['AK']) != strtoupper("HazMatFlashPoint")){
			return false;
		}
		if(strtoupper($data[2]['AL']) != strtoupper("HazMatLabelCode")){
			return false;
		}
		if(strtoupper($data[2]['AM']) != strtoupper("HazMatFlag")){
			return false;
		}
		if(strtoupper($data[2]['AN']) != strtoupper("ImageURL")){
			return false;
		}
		if(strtoupper($data[2]['AO']) != strtoupper("StorageCountScriptTemplateID")){
			return false;
		}
		if(strtoupper($data[2]['AP']) != strtoupper("StorageRates")){
			return false;
		}
		if(strtoupper($data[2]['AQ']) != strtoupper("OutboundMobileSerializationBehavior")){
			return false;
		}
		if(strtoupper($data[2]['AR']) != strtoupper("Price")){
			return false;
		}
		if(strtoupper($data[2]['AS']) != strtoupper("TotalQty")){
			return false;
		}
		if(strtoupper($data[2]['AT']) != strtoupper("UnitType")){
			return false;
		}
						
		return true;
	}
	
	
	private function _parseFile($itemFilename, $doc_number, $client_id){
		$path_file = $this->inbounddocument_m->path;

        try {
            $objPHPExcel = PHPExcel_IOFactory::load($path_file."/tmp_".$itemFilename."_".$doc_number.".xls");
        } catch (Exception $e) {
            // Use PCLZip rather than ZipArchive to read the Excel2007 OfficeOpenXML file
            PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($path_file."/tmp_".$itemFilename."_".$doc_number.".xlsx");
        }

					
		$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
					
		foreach ($cell_collection as $cell) {
		    $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
		    $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
		    $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
					
	        $arr_data[$row][$column] = $data_value;
		}
		if(!$this->_validateFile($arr_data)){
			return false;
		}				
					try {
						$return = $this->inbounddocument_m->updateToInboundInventory($client_id, $doc_number, $arr_data);
					} catch( Exception $e ) {
						echo $e->getMessage();	
					}
					
					return true;
		
	}
	
	public function revise(){
		if($_GET['ids'] == ""){
			redirect("listinbounddoc"); 	
		}
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Inbound Document";
		$this->data['formTitle'] = "Inbound Document - Revise";
		$this->data['breadcrumb'] = array("Inbound Document"=> "");
		$this->load->library("va_input", array("group" => "listinbounddoc"));
		
		$flashData = $this->session->flashdata("listinbounddocError");
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
		
	    $this->va_input->addCustomField( array("name" =>"userfile[]", "placeholder" => "Upload File ", "value" => @$value['userfile'], "msg" => @$msg, "label" => "Upload File *", "view"=>"form/upload_xls"));
		$this->va_input->addHidden( array("name" => "listid", "value" => $_GET['ids']) );
		$this->va_input->addHidden( array("name" => "method", "value" => "revise") );		
		$this->va_input->addHidden( array("name" => "command", "value" => $_GET['command']) );		
				
		$this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("layout/inboundDocRevise.php");

		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);
				
				
	}
	
	private function _uploadFile($listid, $method) {
		$arrItemId = explode(",", $listid);
 		$return = array('error' => false, 'data' => array());
		if($method == "revise"){
			$config['upload_path'] = '../public/inbound/catalog_product/';
		}elseif($method == "uploadinbform"){
			$config['upload_path'] = '../public/inbound/inbound_form/';
		}
		$config['allowed_types'] = 'xls|xlsx';
		$config['max_size']	= '2000';
		
		if($method == "revise"){
			
			$this->load->library('upload');		
			$files = $_FILES;
		    $cpt = count($_FILES['userfile']['name']); 
		    for($i=0; $i<$cpt; $i++)
		    {
		    				
		        $_FILES['userfile']['name']= $files['userfile']['name'][$i];
		        $_FILES['userfile']['type']= $files['userfile']['type'][$i];
		        $_FILES['userfile']['tmp_name']= $files['userfile']['tmp_name'][$i];
		        $_FILES['userfile']['error']= $files['userfile']['error'][$i];
		        $_FILES['userfile']['size']= $files['userfile']['size'][$i];    
				
		
					$datas = $this->inbounddocument_m->getInboundDocumentRow($arrItemId[$i]);
					$doc_number = $datas['doc_number'];
					$id = $datas['id'];
					 
		    	    $config['file_name'] = "tmp_".$arrItemId[$i]."_".$id;
			
		    	$this->upload->initialize($config);
				if ( ! $this->upload->do_upload()) {			
					return null;
				}	
		
		    }
			return $arrItemId;
			
		}elseif($method == "uploadinbform"){
			
			$this->load->library('upload');		
			$files = $_FILES;
		    $cpt = count($_FILES['userfile']['name']);
			$listFileName = array(); 
		    for($i=0; $i<$cpt; $i++)
		    {
		        $_FILES['userfile']['name']= $files['userfile']['name'][$i];
		        $_FILES['userfile']['type']= $files['userfile']['type'][$i];
		        $_FILES['userfile']['tmp_name']= $files['userfile']['tmp_name'][$i];
		        $_FILES['userfile']['error']= $files['userfile']['error'][$i];
		        $_FILES['userfile']['size']= $files['userfile']['size'][$i];    
		    	
		    	    $config['file_name'] = time();
			
		    	$this->upload->initialize($config);
				if ( ! $this->upload->do_upload()) {			
					return null;
				}else{
					$listFileName[$arrItemId[$i]] = $config['file_name'].".xls"; 
				}	
			}
			return $listFileName;
			
		}
		
	}

	
	public function inboundDocList()
	{
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
			}
		}	
		$data = $this->inbounddocument_m->getInboundDocumentList();	
		echo json_encode($data);
	}
	
	public function exportFormItemImport(){
        $this->load->model(array("clientoptions_m", "inbound_m"));
		$client = $this->input->get('client');
		$doc = $this->input->get('doc');
				
		$this->va_excel->setActiveSheetIndex(0);
		
		$this->va_excel->getActiveSheet()->setTitle('Standard Import - Tab1');
        $this->va_excel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
        $this->va_excel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $this->va_excel->getActiveSheet()->freezePane('D3');
        
        
        $sharedStyle1 = new PHPExcel_Style();
        $sharedStyle2 = new PHPExcel_Style();
        $sharedStyle3 = new PHPExcel_Style();
        $sharedStyle4 = new PHPExcel_Style();
        $yellow       = new PHPExcel_Style();
        $pinkDinamis  = new PHPExcel_Style();
        $blue         = new PHPExcel_Style();
        $title         = new PHPExcel_Style();
        $black         = new PHPExcel_Style();
        
        $sharedStyle1->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => '48D1CC')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
							),
          'font' => array('bold' => true,'size'  => 12),
          'alignment' => array(
                                'wrap'       => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                        )                   
		 ));
                
        $title->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => 'FFFFFF')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
							),
          'font' => array('bold' => true,'size'  => 16),
          'alignment' => array(
                                'wrap'       => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                        )                   
		 ));
		
        $sharedStyle2->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => 'FFFAFA')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'left'  	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
							),
           'font' => array('bold' => true,'size'  => 12),
           'alignment' => array(
                                'wrap'       => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                        )
		 ));
         
        $sharedStyle3->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => 'AFEEEE')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
							),
          'font' => array('bold' => true,'size'  => 12)
		 ));
         
         $sharedStyle4->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => 'F08080')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
							),
          'font' => array('bold' => true,'size'  => 12)
		 ));
         
        $yellow->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => 'FFFF00')
							)
		 ));
         
        $pinkDinamis->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => 'F08080')
							)
		 ));
         
        $blue->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => '48D1CC')
							)
		 ));
        $black->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => '000000')
							)
		 ));
		$this->va_excel->getActiveSheet()->mergeCells('A1:C1');
        $this->va_excel->getActiveSheet()->setCellValue('A1', 'ITEM IMPORT SPREADSHEET')->setSharedStyle($title, "A1");
		$this->va_excel->getActiveSheet()->setCellValue('A2', 'SKU Code')->setSharedStyle($sharedStyle1, "A2:B2")->getColumnDimension('A')->setWidth(25);
		$this->va_excel->getActiveSheet()->setCellValue('B2', 'SKU Description')->setSharedStyle($sharedStyle2, "C2:K2")->getColumnDimension('B')->setWidth(90);
		$this->va_excel->getActiveSheet()->setCellValue('C2', 'SKU Configs')->getColumnDimension('C')->setWidth(25);
		$this->va_excel->getActiveSheet()->setCellValue('D2', 'Min')->getColumnDimension('D')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('E2', 'Max')->getColumnDimension('E')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('F2', 'CycleCount')->getColumnDimension('F')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('G2', 'ReorderQty')->getColumnDimension('G')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('H2', 'InventoryMethod')->getColumnDimension('H')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('I2', 'Temperature')->getColumnDimension('I')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('J2', 'Cost')->getColumnDimension('J')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('K2', 'UPC')->getColumnDimension('K')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('L2', 'Track Lot')->setSharedStyle($sharedStyle1,"L2:Q2")->getColumnDimension('L')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('M2', 'Track Serial')->getColumnDimension('M')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('N2', 'Track ExpDate')->getColumnDimension('N')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('O2', 'Primary Unit of Measure')->getColumnDimension('O')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('P2', 'Packaging Unit')->getColumnDimension('P')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('Q2', 'Packing UoM QTY')->getColumnDimension('Q')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('R2', 'Length')->setSharedStyle($sharedStyle3,"R2:U2");
		$this->va_excel->getActiveSheet()->setCellValue('S2', 'Width');
		$this->va_excel->getActiveSheet()->setCellValue('T2', 'Height');
		$this->va_excel->getActiveSheet()->setCellValue('U2', 'Weight');
		$this->va_excel->getActiveSheet()->setCellValue('V2', 'Qualifiers')->setSharedStyle($sharedStyle2, "V2")->getColumnDimension('V')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('W2', 'Storage Setup')->setSharedStyle($sharedStyle4, "W2:Y2")->getColumnDimension('W')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('X2', 'Variable Setup')->getColumnDimension('X')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('Y2', 'NMFC#')->getColumnDimension('Y')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('Z2', 'Lot Number Required')->setSharedStyle($sharedStyle1, "Z2:AE2")->getColumnDimension('Z')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AA2', 'Serial Number Required')->getColumnDimension('AA')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AB2', 'Serial Number Must Be Unique')->getColumnDimension('AB')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AC2', 'Exp Date Req')->getColumnDimension('AC')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AD2', 'Enable Cost')->getColumnDimension('AD')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AE2', 'Cost Required')->getColumnDimension('AE')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AF2', 'IsHazMat')->setSharedStyle($sharedStyle2, "AF2:AR2")->getColumnDimension('AF')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AG2', 'HazMatID')->getColumnDimension('AG')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AH2', 'HazMatShippingName')->getColumnDimension('AH')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AI2', 'HazMatHazardClass')->getColumnDimension('AI')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AJ2', 'HazMatPackingGroup')->getColumnDimension('AJ')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AK2', 'HazMatFlashPoint')->getColumnDimension('AK')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AL2', 'HazMatLabelCode')->getColumnDimension('AL')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AM2', 'HazMatFlag')->getColumnDimension('AM')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AN2', 'ImageURL')->getColumnDimension('AN')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AO2', 'StorageCountScriptTemplateID')->getColumnDimension('AO')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AP2', 'StorageRates')->getColumnDimension('AP')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AQ2', 'OutboundMobileSerializationBehavior')->getColumnDimension('AQ')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AR2', 'Price')->getColumnDimension('AR')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AS2', 'TotalQty')->setSharedStyle($sharedStyle1, "AS2:AU2")->getColumnDimension('AS')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AT2', 'UnitType')->getColumnDimension('AT')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('AU2', 'POType')->getColumnDimension('AU')->setAutoSize(true);
        $this->va_excel->getActiveSheet()->setCellValue('AV2', 'AttrSet')->getColumnDimension('AV')->setAutoSize(true);
		
		$result = $this->inbounddocument_m->getInboundInvItem($client, $doc);
        $docDetail = $this->inbound_m->getInboundById($doc);        
		$lup = 3;

        $attrList = $this->clientoptions_m->get($client, 'attribute_set');
        $attrList = json_decode($attrList['option_value'], true);

		foreach($result as $item){
			$this->va_excel->getActiveSheet()->setCellValue('A'.$lup, $item['sku_simple'])->setSharedStyle($yellow, "A".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('B'.$lup, $item['sku_description'])->setSharedStyle($yellow, "B".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('C'.$lup, $item['sku_config'])->setSharedStyle($yellow, "C".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('D'.$lup, $item['min']);
			$this->va_excel->getActiveSheet()->setCellValue('E'.$lup, $item['max']);
			$this->va_excel->getActiveSheet()->setCellValue('F'.$lup, $item['cycle_count'])->setSharedStyle($yellow, "F".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('G'.$lup, $item['reorder_qty']);
			$this->va_excel->getActiveSheet()->setCellValue('H'.$lup, $item['inventor_method'])->setSharedStyle($yellow, "H".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('I'.$lup, $item['temperature']);
			$this->va_excel->getActiveSheet()->setCellValue('J'.$lup, $item['cost'])->setSharedStyle($yellow, "J".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('K'.$lup, $item['upc'])->setSharedStyle($yellow, "K".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('L'.$lup, $item['track_lot']);
			$this->va_excel->getActiveSheet()->setCellValue('M'.$lup, $item['track_serial']);
			$this->va_excel->getActiveSheet()->setCellValue('N'.$lup, $item['track_expdate']);
			$this->va_excel->getActiveSheet()->setCellValue('O'.$lup, $item['primary_unit_of_measure']);
			$this->va_excel->getActiveSheet()->setCellValue('P'.$lup, $item['packaging_unit']);
			$this->va_excel->getActiveSheet()->setCellValue('Q'.$lup, $item['packaging_uom_qty']);
			$this->va_excel->getActiveSheet()->setCellValue('R'.$lup, $item['length']);
			$this->va_excel->getActiveSheet()->setCellValue('S'.$lup, $item['width']);
			$this->va_excel->getActiveSheet()->setCellValue('T'.$lup, $item['height']);
			$this->va_excel->getActiveSheet()->setCellValue('U'.$lup, $item['weiight']);
			$this->va_excel->getActiveSheet()->setCellValue('V'.$lup, $item['qualifiers']);
			$this->va_excel->getActiveSheet()->setCellValue('W'.$lup, $item['storage_setup'])->setSharedStyle($pinkDinamis, "W".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('X'.$lup, $item['variable_setup'])->setSharedStyle($pinkDinamis, "X".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('Y'.$lup, $item['nmfc'])->setSharedStyle($pinkDinamis, "Y".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('Z'.$lup, $item['lot_number_required']);
			$this->va_excel->getActiveSheet()->setCellValue('AA'.$lup, $item['serial_number_required']);
			$this->va_excel->getActiveSheet()->setCellValue('AB'.$lup, $item['serial_number_must_be_unique']);
			$this->va_excel->getActiveSheet()->setCellValue('AC'.$lup, $item['exp_date_req']);
			$this->va_excel->getActiveSheet()->setCellValue('AD'.$lup, $item['enable_cost']);
			$this->va_excel->getActiveSheet()->setCellValue('AE'.$lup, $item['cost_required']);
			$this->va_excel->getActiveSheet()->setCellValue('AF'.$lup, $item['is_haz_mat']);
			$this->va_excel->getActiveSheet()->setCellValue('AG'.$lup, $item['haz_mat_id']);
			$this->va_excel->getActiveSheet()->setCellValue('AH'.$lup, $item['haz_mat_shipping_name']);
			$this->va_excel->getActiveSheet()->setCellValue('AI'.$lup, $item['haz_mat_hazard_class']);
			$this->va_excel->getActiveSheet()->setCellValue('AJ'.$lup, $item['haz_mat_packing_group']);
			$this->va_excel->getActiveSheet()->setCellValue('AK'.$lup, $item['haz_mat_flash_point']);
			$this->va_excel->getActiveSheet()->setCellValue('AL'.$lup, $item['haz_mat_label_code']);
			$this->va_excel->getActiveSheet()->setCellValue('AM'.$lup, $item['haz_mat_flat']);
			$this->va_excel->getActiveSheet()->setCellValue('AN'.$lup, $item['image_url']);
			$this->va_excel->getActiveSheet()->setCellValue('AO'.$lup, $item['storage_count_stript_template_id']);
			$this->va_excel->getActiveSheet()->setCellValue('AP'.$lup, $item['storage_rates']);
			$this->va_excel->getActiveSheet()->setCellValue('AQ'.$lup, $item['outbound_mobile_serialization_behavior']);
			$this->va_excel->getActiveSheet()->setCellValue('AR'.$lup, $item['price']);
			$this->va_excel->getActiveSheet()->setCellValue('AS'.$lup, $item['total_qty']);
			$this->va_excel->getActiveSheet()->setCellValue('AT'.$lup, $item['unit_type'])->setSharedStyle($blue, "AT".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('AU'.$lup, $item['po_type'])->setSharedStyle($black, "AU".$lup);
            $this->va_excel->getActiveSheet()->setCellValue('AV'.$lup, $item['attribute_set'])->setSharedStyle($black, "AV".$lup);
			$lup++;
		}
								
		$dataClient = $this->client_m->getClientById($client);
		$dataClientRows = $dataClient->row_array();
        
        $sheetName=explode("/",$docDetail['doc_number']);
        $this->va_excel->getActiveSheet()->setTitle($sheetName[0]."-".$sheetName[1]."-".$sheetName[2]."-".$sheetName[3]);
        
        $filename='Form Item Import (Client : '.$dataClientRows['client_code'].' Do Number : '.$docDetail['doc_number'].').xls';
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename="'.$filename.'"'); 
		header('Cache-Control: max-age=0');
		            
		
		$objWriter = PHPExcel_IOFactory::createWriter($this->va_excel, 'Excel5');  
		
		$objWriter->save('php://output');
		
	}

	public function updateAttr(){
        $this->load->model("clientoptions_m");
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Inbound Document";
		$this->data['formTitle'] = "Inbound Document - Update Attribute";
		$this->data['breadcrumb'] = array("Inbound Document"=> "");
		$this->load->library("va_input", array("group" => "listinbounddoc"));

		$this->va_input->addHidden( array("name" => "method", "value" => "updateattr") );
		$this->va_input->addHidden( array("name" => "client", "value" => $_GET['client']) );		
		$this->va_input->addHidden( array("name" => "doc", "value" => $_GET['doc']) );		
		$this->va_input->addHidden( array("name" => "id", "value" => $_GET['id']) );		

        $client = $_GET['client'];
		$doc = $_GET['doc'];
		$rows = $this->inbounddocument_m->getInboundInvItem($client, $doc);
        $attrList = $this->clientoptions_m->get($client, 'attribute_set');
        $attrList = json_decode($attrList['option_value'], true);

		foreach($rows as $itemRows){
			if($itemRows['attribute_set'] <> ""){
				$this->va_input->addSelect( array("name" => "attrset_".$itemRows['id'],"label" => "", "list" => $attrList, "value" => @$itemRows['attribute_set'], "msg" => @$msg['client']) );
			}else{
				$this->va_input->addSelect( array("name" => "attrset_".$itemRows['id'],"label" => "", "list" => $attrList, "value" => "", "msg" => @$msg['client']) );
			}
            $this->va_input->addHidden( array("name" => "upc_".$itemRows['id'], "value" => $itemRows['upc']) );
		}
		$this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("layout/inboundDocUpdateAttrSet.php");

		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);
		
	}
	
	public function downloadInboundForm(){
		$client = $this->input->get('client');
		$doc = $this->input->get('doc');

        $docDetail = $this->inbound_m->getInboundById($doc);
		$dataClient = $this->client_m->getClientById($client);
		$dataClientRows = $dataClient->row_array();
				
		$this->va_excel->setActiveSheetIndex(0);
        $this->va_excel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
        $this->va_excel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
        $this->va_excel->getActiveSheet()->getRowDimension('11')->setRowHeight(30);
		$this->va_excel->getActiveSheet()->freezePane('A13');
        
        $styleReport1 = new PHPExcel_Style();
        $styleReport1Dinamis = new PHPExcel_Style();
        $styleReport2 = new PHPExcel_Style();
        $styleReport3 = new PHPExcel_Style();
        $styleReport4 = new PHPExcel_Style();
        
        $styleReport1->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => 'FFD700')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
							),
          'font' => array('bold' => true,'size'  => 13),
          'alignment' => array(
                                'wrap'       => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                        )
                   
		 ));
         
        $styleReport1Dinamis->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => 'FFD700')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_THIN)
							)               
		 ));
         
        $styleReport2->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => '00BFFF')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
							),
          'font' => array('bold' => true,'size'  => 13),
          'alignment' => array(
                                'wrap'       => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                )
                   
		 ));
         
        $styleReport3->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => '98FB98')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
							),
          'font' => array('bold' => true,'size'  => 13),
          'alignment' => array(
                                'wrap'       => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                )
                   
		 ));
         
        $styleReport4->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => '32CD32')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'top'   	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
							),
          'font' => array('bold' => true,'size'  => 13),
          'alignment' => array(
                                'wrap'       => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                )
         ));
        
		$this->va_excel->getActiveSheet()->setTitle('Standard Import - Tab1');

		$this->va_excel->getActiveSheet()->setCellValue('A1', 'INBOUND REPORT')->mergeCells('A1:B1')->getStyle('A1:B1')->getFont()->setSize(15)->setBold(true);
		$this->va_excel->getActiveSheet()->setCellValue('A3', 'Clients :')->getStyle('A3')->getFont()->setSize(12)->setBold(true);
		$this->va_excel->getActiveSheet()->setCellValue('B3', $dataClientRows['client_code'])->getStyle('B3')->getFont()->setSize(12)->setBold(true);
		$this->va_excel->getActiveSheet()->setCellValue('A4', 'Physical Inbound Date :')->getStyle('A4')->getFont()->setSize(12)->setBold(true);
		$this->va_excel->getActiveSheet()->setCellValue('A5', 'Doc Number :')->getStyle('A5')->getFont()->setSize(12)->setBold(true);
        $this->va_excel->getActiveSheet()->setCellValue('B5', $docDetail['doc_number'])->getStyle('B5')->getFont()->setSize(12)->setBold(true);
		$this->va_excel->getActiveSheet()->setCellValue('A7', 'Berikut adalah hasil pengecekan (Quality Control) atas barang masuk ke Warehouse - Taman Tekno yang telah dilakukan sebelumnya:');
		
			$this->va_excel->getActiveSheet()->mergeCells('A9:A12');
		$this->va_excel->getActiveSheet()->setCellValue('A9', 'SKU Code')->setSharedStyle($styleReport1, "A9")->getColumnDimension('A')->setWidth(25);
			$this->va_excel->getActiveSheet()->mergeCells('B9:B12');
		$this->va_excel->getActiveSheet()->setCellValue('B9', 'SKU Description')->setSharedStyle($styleReport1, "B9")->getColumnDimension('B')->setWidth(80);
            $this->va_excel->getActiveSheet()->mergeCells('C9:D9')->setSharedStyle($styleReport1, "C10");
        $this->va_excel->getActiveSheet()->setCellValue('C9', 'Product Catalog')->setSharedStyle($styleReport1, "C9")->getColumnDimension('C')->setWidth(15);
			$this->va_excel->getActiveSheet()->mergeCells('C10:C12')->setSharedStyle($styleReport1, "C10");
		$this->va_excel->getActiveSheet()->setCellValue('C10', 'Size');
			$this->va_excel->getActiveSheet()->mergeCells('D10:D12')->setSharedStyle($styleReport1, "D10");
		$this->va_excel->getActiveSheet()->setCellValue('D10', 'Qty');
			$this->va_excel->getActiveSheet()->mergeCells('E9:E12');
		$this->va_excel->getActiveSheet()->setCellValue('E9', 'Qty Inbound (SJ)')->setSharedStyle($styleReport2, "E9")->getColumnDimension('E')->setAutoSize(true);
			$this->va_excel->getActiveSheet()->mergeCells('F9:F12');
		$this->va_excel->getActiveSheet()->setCellValue('F9', 'Note')->setSharedStyle($styleReport3, "F9:H9")->getColumnDimension('F')->setAutoSize(true);
			$this->va_excel->getActiveSheet()->mergeCells('G9:G12');
		$this->va_excel->getActiveSheet()->setCellValue('G9', 'Problem')->getColumnDimension('G')->setAutoSize(true);
			$this->va_excel->getActiveSheet()->mergeCells('H9:H12');
		$this->va_excel->getActiveSheet()->setCellValue('H9', 'Action Taken')->getColumnDimension('H')->setAutoSize(true);
			$this->va_excel->getActiveSheet()->mergeCells('I9:I12');
		$this->va_excel->getActiveSheet()->setCellValue('I9', 'Loc. Bin')->setSharedStyle($styleReport4, "I9")->getColumnDimension('I')->setWidth(25);
		
		$result = $this->inbounddocument_m->getInboundInvItem($client, $doc, 'ALL');
		$lup = 13;
		
		foreach($result as $item){
			$skuDescription = $item['sku_description'];
			$arrTmp = explode("S:",$skuDescription);
				$arrTmp1 = explode(",",$arrTmp[1]);
				$size = $arrTmp1[0]; 
			$this->va_excel->getActiveSheet()->setCellValue('A'.$lup, $item['sku_simple'])->setSharedStyle($styleReport1Dinamis, "A".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('B'.$lup, $item['sku_description'])->setSharedStyle($styleReport1Dinamis, "B".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('C'.$lup, $size)->setSharedStyle($styleReport1Dinamis, "C".$lup);
			$this->va_excel->getActiveSheet()->setCellValue('D'.$lup, $item['total_qty'])->setSharedStyle($styleReport1Dinamis, "D".$lup);
			$lup++;
		}
		
		$filename='Form Inbound ('.$dataClientRows['client_code'].' Do Number : '.$docDetail['doc_number'].').xls';
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename="'.$filename.'"'); 
		header('Cache-Control: max-age=0');
		            
		
		$objWriter = PHPExcel_IOFactory::createWriter($this->va_excel, 'Excel5');  
		
		$objWriter->save('php://output');
		
	}

	public function downloadReceivingForm(){
		$client = $this->input->get('client');
		$doc = $this->input->get('doc');
				$query = $this->inbounddocument_m->getInboundDocumentByReferenceId($doc);
				$row = $query->result_array();
				$id = $row[0]['id'];
				$doc = $id;

        $docDetail = $this->inbound_m->getInboundById($doc);
		$dataClient = $this->client_m->getClientById($client);
		$dataClientRows = $dataClient->row_array();
				
		$this->va_excel->setActiveSheetIndex(0);
		$this->va_excel->getActiveSheet()->freezePane('A2');
        $this->va_excel->getActiveSheet()->getRowDimension('1')->setRowHeight(25);
		$this->va_excel->getActiveSheet()->setTitle('Standard Import - Tab1');
        
        $receivStyle1 = new PHPExcel_Style();    
        $receivStyle2 = new PHPExcel_Style();     
        $receivStyle1->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => '1E90FF')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
							),
          'font' => array('bold' => true,'size'=>12),
          'alignment' => array(
                                'wrap'       => true,
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
                                        )                   
		 ));
         
        $receivStyle2->applyFromArray(
        array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('rgb' => 'FFD700')
							),
		  'borders' => array(
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'left'	    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                'top'	    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_THIN)
							)               
		 ));

		$this->va_excel->getActiveSheet()->setCellValue('A1', 'ReferenceNumber')->setSharedStyle($receivStyle1, 'A1:D1')->getColumnDimension('A')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('B1', 'Sku')->getColumnDimension('B')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('C1', 'Quantity')->getColumnDimension('C')->setAutoSize(true);
		$this->va_excel->getActiveSheet()->setCellValue('D1', 'LocationField1')->getColumnDimension('D')->setAutoSize(true);
		
		$result = $this->inbounddocument_m->getInboundInvStock($client, $doc);
		$lup = 2;
		
		foreach($result as $item){
			$item_id = $item['item_id'];
			//get sku code
			$query = $this->inbounddocument_m->getInboundInvItemById($client, $item_id);
			$sku_simple = $query[0]['sku_simple']; 
			
			$this->va_excel->getActiveSheet()->setCellValue('A'.$lup, $item['reference_num']);
			$this->va_excel->getActiveSheet()->setCellValue('B'.$lup, $sku_simple)->setSharedStyle($receivStyle2, 'B'.$lup);
			$this->va_excel->getActiveSheet()->setCellValue('C'.$lup, $item['quantity']);
			$this->va_excel->getActiveSheet()->setCellValue('D'.$lup, $item['bin_location']);
			$lup++;
		}
		
		$filename='Receiving Form ('.$dataClientRows['client_code'].' Do Number : '.$docDetail['doc_number'].').xlsx';
		header('Content-Type: application/vnd.ms-excel'); 
		header('Content-Disposition: attachment;filename="'.$filename.'"'); 
		header('Cache-Control: max-age=0');
		            
		
		$objWriter = PHPExcel_IOFactory::createWriter($this->va_excel, 'Excel2007');
		
		$objWriter->save('php://output');
		
	}
	
	public function uploadInboundForm(){
		if($_GET['ids'] == ""){
			redirect("listinbounddoc"); 	
		}
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Inbound Document";
		$this->data['formTitle'] = "Inbound Document - Upload Inb. Form";
		$this->data['breadcrumb'] = array("Inbound Document"=> "");
		$this->load->library("va_input", array("group" => "listinbounddoc"));
		
		$flashData = $this->session->flashdata("listinbounddocError");
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
		
	    $this->va_input->addCustomField( array("name" =>"userfile[]", "placeholder" => "Upload File ", "value" => @$value['userfile'], "msg" => @$msg, "label" => "Upload File *", "view"=>"form/upload_xls"));
		$this->va_input->addHidden( array("name" => "listid", "value" => $_GET['ids']) );
		$this->va_input->addHidden( array("name" => "method", "value" => "uploadinbform") );		
		$this->va_input->addHidden( array("name" => "command", "value" => $_GET['command']) );		
				
		$this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("layout/inboundDocRevise.php");

		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);
				
				
	}

	public function importItem3PL(){
		$this->load->add_package_path(APPPATH."third_party/threepl/");
		$this->load->library("inbound_threepl", null, "inbound_threepl");
		$this->load->model( array("client_m", "inbounddocument_m") );
		
		$client = $_GET['client'];
		$doc = $_GET['doc'];
		
		$client = $this->client_m->getClientById($client);
		$client = $client->row_array();
		
		if(!$client['threepl_user'] && !$client['threepl_pass']) {
			log_message("debug", self::TAG . " Client doesn't had 3PL detail");
			die;
		}
		
		
		$c['threepluser'] = $client['threepl_user'];
		$c['threeplpass'] = $client['threepl_pass'];
		
		$this->inbound_threepl->setConfig( array("username" => $c['threepluser'], "password" => $c['threeplpass']) );
		$returnMsgItem = $this->inbounddocument_m->getParamInbound3PL($_GET['client'], $doc);
		$return = $this->inbound_threepl->createItems($returnMsgItem);
				if(is_array($return)){
					redirect("listinbounddoc");
				}else{
					echo "Something wrong when calling 3PL. See the log file.<input type='button' value='Back' onclick='window.history.back()'>";
				}
	}

	public function importItemMage(){
		$this->load->library("Mageapi");
		$this->load->model( array("client_m", "inbounddocument_m") );
		
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
			
		$param = $this->inbounddocument_m->getParamInboundMage($_GET['client'], $doc);		
							
		if( $this->mageapi->initSoap($config) ) {
				$return = $this->mageapi->inboundCreateItem($param);
				if(is_array($return)){
					$flagError = false;
					foreach($return as $itemReturn){
						if(isset($itemReturn['isFault'])){
							$flagError = true;
						}
					}
					if(!$flagError){
						redirect("listinbounddoc");
					}else{
						echo "Something wrong when calling Mage. See the log file.<input type='button' value='Back' onclick='window.history.back()'>";
					}
				}else{
					echo "Something wrong when calling Mage. See the log file.<input type='button' value='Back' onclick='window.history.back()'>";
				}
		}
	}
	
}
?>
