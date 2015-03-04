<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
  
class Listinbounddoc extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("users_m", "client_m", "inbounddocument_m") );
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
			->setHeadingTitle(array("No #", "Client Name","DO Number","Note"))
			->setHeadingWidth(array(2, 2,2,3,2));
		$this->va_list->setDropdownFilter(1, array("name" => $this->inbounddocument_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));
		
		$this->data['script'] = $this->load->view("script/inbounddocument_list", array("ajaxSource" => site_url("listinbounddoc/inboundDocList")), true);	
		$this->load->view("template", $this->data);
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
		$client = $this->input->get('client');
		$doc = $this->input->get('doc');
				
		$this->va_excel->setActiveSheetIndex(0);
		
		$this->va_excel->getActiveSheet()->setTitle('Standard Import - Tab1');
		
		$this->va_excel->getActiveSheet()->setCellValue('A1', 'SKU Code');
		$this->va_excel->getActiveSheet()->setCellValue('B1', 'SKU Description');
		$this->va_excel->getActiveSheet()->setCellValue('C1', 'SKU Configs');
		$this->va_excel->getActiveSheet()->setCellValue('D1', 'Min');
		$this->va_excel->getActiveSheet()->setCellValue('E1', 'Max');
		$this->va_excel->getActiveSheet()->setCellValue('F1', 'CycleCount');
		$this->va_excel->getActiveSheet()->setCellValue('G1', 'ReorderQty');
		$this->va_excel->getActiveSheet()->setCellValue('H1', 'InventoryMethod');
		$this->va_excel->getActiveSheet()->setCellValue('I1', 'Temperature');
		$this->va_excel->getActiveSheet()->setCellValue('J1', 'Cost');
		$this->va_excel->getActiveSheet()->setCellValue('K1', 'UPC');
		$this->va_excel->getActiveSheet()->setCellValue('L1', 'Track Lot');
		$this->va_excel->getActiveSheet()->setCellValue('M1', 'Track Serial');
		$this->va_excel->getActiveSheet()->setCellValue('N1', 'Track ExpDate');
		$this->va_excel->getActiveSheet()->setCellValue('O1', 'Primary Unit of Measure');
		$this->va_excel->getActiveSheet()->setCellValue('P1', 'Packaging Unit');
		$this->va_excel->getActiveSheet()->setCellValue('Q1', 'Packing UoM QTY');
		$this->va_excel->getActiveSheet()->setCellValue('R1', 'Length');
		$this->va_excel->getActiveSheet()->setCellValue('S1', 'Width');
		$this->va_excel->getActiveSheet()->setCellValue('T1', 'Height');
		$this->va_excel->getActiveSheet()->setCellValue('U1', 'Weight');
		$this->va_excel->getActiveSheet()->setCellValue('V1', 'Qualifiers');
		$this->va_excel->getActiveSheet()->setCellValue('W1', 'Storage Setup');
		$this->va_excel->getActiveSheet()->setCellValue('X1', 'Variable Setup');
		$this->va_excel->getActiveSheet()->setCellValue('Y1', 'NMFC#');
		$this->va_excel->getActiveSheet()->setCellValue('Z1', 'Lot Number Required');
		$this->va_excel->getActiveSheet()->setCellValue('AA1', 'Serial Number Required');
		$this->va_excel->getActiveSheet()->setCellValue('AB1', 'Serial Number Must Be Unique');
		$this->va_excel->getActiveSheet()->setCellValue('AC1', 'Exp Date Req');
		$this->va_excel->getActiveSheet()->setCellValue('AE1', 'Enable Cost');
		$this->va_excel->getActiveSheet()->setCellValue('AF1', 'Cost Required');
		$this->va_excel->getActiveSheet()->setCellValue('AG1', 'IsHazMat');
		$this->va_excel->getActiveSheet()->setCellValue('AH1', 'HazMatID');
		$this->va_excel->getActiveSheet()->setCellValue('AI1', 'HazMatShippingName');
		$this->va_excel->getActiveSheet()->setCellValue('AJ1', 'HazMatHazardClass');
		$this->va_excel->getActiveSheet()->setCellValue('AK1', 'HazMatPackingGroup');
		$this->va_excel->getActiveSheet()->setCellValue('AL1', 'HazMatFlashPoint');
		$this->va_excel->getActiveSheet()->setCellValue('AM1', 'HazMatLabelCode');
		$this->va_excel->getActiveSheet()->setCellValue('AN1', 'HazMatFlag');
		$this->va_excel->getActiveSheet()->setCellValue('AO1', 'ImageURL');
		$this->va_excel->getActiveSheet()->setCellValue('AP1', 'StorageCountScriptTemplateID');
		$this->va_excel->getActiveSheet()->setCellValue('AQ1', 'StorageRates');
		$this->va_excel->getActiveSheet()->setCellValue('AR1', 'OutboundMobileSerializationBehavior');
		$this->va_excel->getActiveSheet()->setCellValue('AS1', 'Price');
		$this->va_excel->getActiveSheet()->setCellValue('AT1', 'TotalQty');
		$this->va_excel->getActiveSheet()->setCellValue('AU1', 'UnitType');
		
		$result = $this->inbounddocument_m->getInboundInvItem($client, $doc);
		$lup = 2;
		
		foreach($result as $item){
			$this->va_excel->getActiveSheet()->setCellValue('A'.$lup, $item['sku_simple']);
			$this->va_excel->getActiveSheet()->setCellValue('B'.$lup, $item['sku_description']);
			$this->va_excel->getActiveSheet()->setCellValue('C'.$lup, $item['sku_config']);
			$this->va_excel->getActiveSheet()->setCellValue('D'.$lup, $item['min']);
			$this->va_excel->getActiveSheet()->setCellValue('E'.$lup, $item['max']);
			$this->va_excel->getActiveSheet()->setCellValue('F'.$lup, $item['cycle_count']);
			$this->va_excel->getActiveSheet()->setCellValue('G'.$lup, $item['reorder_qty']);
			$this->va_excel->getActiveSheet()->setCellValue('H'.$lup, $item['inventor_method']);
			$this->va_excel->getActiveSheet()->setCellValue('I'.$lup, $item['temperature']);
			$this->va_excel->getActiveSheet()->setCellValue('J'.$lup, $item['cost']);
			$this->va_excel->getActiveSheet()->setCellValue('K'.$lup, $item['upc']);
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
			$this->va_excel->getActiveSheet()->setCellValue('W'.$lup, $item['storage_setup']);
			$this->va_excel->getActiveSheet()->setCellValue('X'.$lup, $item['variable_setup']);
			$this->va_excel->getActiveSheet()->setCellValue('Y'.$lup, $item['nmfc']);
			$this->va_excel->getActiveSheet()->setCellValue('Z'.$lup, $item['lot_number_required']);
			$this->va_excel->getActiveSheet()->setCellValue('AA'.$lup, $item['serial_number_required']);
			$this->va_excel->getActiveSheet()->setCellValue('AB'.$lup, $item['serial_number_must_be_unique']);
			$this->va_excel->getActiveSheet()->setCellValue('AC'.$lup, $item['exp_date_req']);
			$this->va_excel->getActiveSheet()->setCellValue('AE'.$lup, $item['enable_cost']);
			$this->va_excel->getActiveSheet()->setCellValue('AF'.$lup, $item['cost_required']);
			$this->va_excel->getActiveSheet()->setCellValue('AG'.$lup, $item['is_haz_mat']);
			$this->va_excel->getActiveSheet()->setCellValue('AH'.$lup, $item['haz_mat_id']);
			$this->va_excel->getActiveSheet()->setCellValue('AI'.$lup, $item['haz_mat_shipping_name']);
			$this->va_excel->getActiveSheet()->setCellValue('AJ'.$lup, $item['haz_mat_hazard_class']);
			$this->va_excel->getActiveSheet()->setCellValue('AK'.$lup, $item['haz_mat_packing_group']);
			$this->va_excel->getActiveSheet()->setCellValue('AL'.$lup, $item['haz_mat_flash_point']);
			$this->va_excel->getActiveSheet()->setCellValue('AM'.$lup, $item['haz_mat_label_code']);
			$this->va_excel->getActiveSheet()->setCellValue('AN'.$lup, $item['haz_mat_flat']);
			$this->va_excel->getActiveSheet()->setCellValue('AO'.$lup, $item['image_url']);
			$this->va_excel->getActiveSheet()->setCellValue('AP'.$lup, $item['storage_count_stript_template_id']);
			$this->va_excel->getActiveSheet()->setCellValue('AQ'.$lup, $item['storage_rates']);
			$this->va_excel->getActiveSheet()->setCellValue('AR'.$lup, $item['outbound_mobile_serialization_behavior']);
			$this->va_excel->getActiveSheet()->setCellValue('AS'.$lup, $item['price']);
			$this->va_excel->getActiveSheet()->setCellValue('AT'.$lup, $item['total_qty']);
			$this->va_excel->getActiveSheet()->setCellValue('AU'.$lup, $item['unit_type']);
			$lup++;
		}
								
		//$this->va_excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
		
		//$this->va_excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		
		//$this->va_excel->getActiveSheet()->mergeCells('A1:D1');
		
		//$this->va_excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$filename='Form Item Import.xls'; //save our workbook as this file name
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
		header('Cache-Control: max-age=0'); //no cache
		            
		
		$objWriter = PHPExcel_IOFactory::createWriter($this->va_excel, 'Excel5');  
		
		$objWriter->save('php://output');
		
	}
	
}
?>