<?php
/*
 * model for handle operation table inb_inventory_item(client_id),inb_inventory_stock(client_id),inb_document
 *
 */

/**
 * @property Clientoptions_m $clientoptions_m
 */
class Inbounddocument_m extends MY_Model {
	
	var $db = null;
	var $table = 'inb_document';
	var $tableInv = 'inb_inventory_item';
	var $tableInvStock = 'inb_inventory_stock';
	var $tableInvItems = 'inv_items';
	var $tableClient ='client';
	var $sorts = array(1 => "id");
	var $pkField = "id";
	var $path = "";
	var $pathInboundForm = "";
    var $attrList = array();
	
	function __construct()
    {
        parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
		$this->path = BASEPATH ."../public/inbound/catalog_product"; 
		$this->pathInboundForm = BASEPATH ."../public/inbound/inbound_form"; 
		$this->relation = array(
			array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}")
		);
		
		$this->select = array("{$this->table}.doc_number", "{$this->table}.client_id", "{$this->table}.note", "{$this->table}.type", "{$this->table}.status", "{$this->table}.created_at", "{$this->table}.updated_at", "{$this->table}.created_by", "{$this->table}.filename", "{$this->table}.id", "{$this->tableClient}.client_code");
		$this->filters = array("client_id"=>"client_id");
	}
	
	function getInboundInvItem($client, $doc){
		if(!$client) return array();
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->get_where('inb_inventory_item_'.$client, array('doc_number'=>$doc, 'po_type'=>'NEW'));
		$rows = $query->result_array();
		return $rows;
	}

	function getInboundInvItemById($client, $id){
		if(!$client) return array();
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->get_where('inb_inventory_item_'.$client, array('id'=>$id));
		$rows = $query->result_array();
		return $rows;
	}

	function getInboundInvStock($client, $doc){
		if(!$client) return array();
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->get_where('inb_inventory_stock_'.$client, array('doc_number'=>$doc));
		$rows = $query->result_array();
		return $rows;
	}

	function getInboundDocumentRow($id){
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->get_where('inb_document', array('id'=>$id));
		$row = $query->row_array();		
		return $row;
	}

	function getInboundDocumentInfo($client,$type) 
	{
		if(!$client) return array();
		$mysql = $this->load->database('mysql', TRUE);
		$query = $this->db->query("SELECT * FROM inb_document WHERE client_id=".$client." AND type=".$type." AND status=0");
		return $query;
	}

	function getInboundDocumentByReferenceId($reference_id){
		$query = $this->db->query("SELECT * FROM inb_document WHERE reference_id=".$reference_id." AND status=1 ORDER BY id DESC limit 1");
		return $query;
	}
	
	function getInboundDocumentList() 
	{
		$this->db = $this->load->database('mysql', TRUE); 
		$iTotalRecords = $this->_doGetTotalRow();
		$iDisplayLength = intval($this->input->post('iDisplayLength'));
		$iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
		$iDisplayStart = intval($this->input->post('iDisplayStart'));
		$sEcho = intval($this->input->post('sEcho'));
	
		$records = array();
		$records["aaData"] = array();
	
		$statList= array(
				0 =>array("New Uploaded Document", "warning"),
				1 =>array("Imported", "success"),
				
		);
		
		$end = $iDisplayStart + $iDisplayLength;
		$end = $end > $iTotalRecords ? $iTotalRecords : $end;
		
		$_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
		$no=0;
		
		foreach($_row->result() as $_result) {
			$btnAction = "";
			if($_result->type == 1){
				if($_result->status == 1){
					//shows only update attribute button
					$btnAction = '<a href="'.base_url().'listinbounddoc/updateAttr?client='.$_result->client_id.'&doc='.$_result->id.'&id='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon" ></i> Update Attribute Set</a>';
				}elseif($_result->status == 2){
					//shows update attribute button and download form item
					$btnAction = '<a href="'.base_url().'listinbounddoc/updateAttr?client='.$_result->client_id.'&doc='.$_result->id.'&id='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon" ></i> Update Attribute Set</a>';
					$btnAction .= '<br /><br /><a href="'.base_url().'listinbounddoc/exportFormItemImport?client='.$_result->client_id.'&doc='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Form Import</a>';
					$btnAction .= '&nbsp;<a href="'.base_url().'listinbounddoc/downloadInboundForm?client='.$_result->client_id.'&doc='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Inbound Form</a>';
				}elseif($_result->status == 3){
					$btnAction = '<a href="'.base_url().'listinbounddoc/updateAttr?client='.$_result->client_id.'&doc='.$_result->id.'&id='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon" ></i> Update Attribute Set</a>';
					$btnAction .= '<br /><br /><a href="'.base_url().'listinbounddoc/exportFormItemImport?client='.$_result->client_id.'&doc='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Form Import</a>';
					$btnAction .= '&nbsp;<a href="'.base_url().'listinbounddoc/downloadInboundForm?client='.$_result->client_id.'&doc='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Inbound Form</a>';
					//check if any visible PO_TYPE = NEW
					$sql = "SELECT po_type FROM `inb_inventory_item_".$_result->client_id."` WHERE UPPER(po_type) = 'NEW'";
					$query = $this->db->query($sql);
					$num = $query->num_rows();
					if($num > 0){					
						$btnAction .= '<hr /><a href="'.base_url().'listinbounddoc/importItem3PL?client='.$_result->client_id.'&doc='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-export" ></i> Import Item to 3PL</a>';
					}
					$btnAction .= '<br /><br /><a href="'.base_url().'listinbounddoc/downloadReceivingForm?client='.$_result->client_id.'&doc='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Receiving Form</a>';
                    //$btnAction .= '<br /><br /><a href="'.base_url().'listinbounddoc/importItemMage?client='.$_result->client_id.'&doc='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-export" ></i> Import Item to MAGE</a>';
									}
				if($_result->status == 1 or $_result->status == 2 or $_result->status == 3){
					$records["aaData"][] = array(
							'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
							$no=$no+1,
							$_result->client_code,
							$_result->doc_number,
							$_result->note,
							$btnAction
							
					);
				}
			}
		}
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
	}
	
	function updateStatusInboundDocumentList($id, $status){
		$data=array('status'=>$status);
		$this->db->where('id',$id);
		$this->db->update('inb_document',$data);
	}
	
	function updateToInboundInventory($client, $doc_number, $arr_data){
		$sizeRowX = count($arr_data); 
		$sizeRowY = count($arr_data[1]);
		
		
		$this->db->trans_start();
		//delete record that related to the doc number
		$this->db->where('doc_number', $doc_number);
		$this->db->delete($this->tableInv."_".$client);
		for($x=3;$x<=$sizeRowX;$x++){
			//------------------get the field items--------------------------
			//sku code
			if(isset($arr_data[$x]['A'])){
				$skuSimple = $arr_data[$x]['A']; 				
			}else{
				$skuSimple = "";
			}
			//sku description
			if(isset($arr_data[$x]['B'])){
				$skuDescription = $arr_data[$x]['B']; 				
			}else{
				$skuDescription = "";
			}
						
			//sku configs
			if(isset($arr_data[$x]['C'])){
				$skuConfig = $arr_data[$x]['C']; 				
			}else{
				$skuConfig = "";
			}
						
			//min
			if(isset($arr_data[$x]['D'])){
				$min = $arr_data[$x]['D']; 				
			}else{
				$min = "";
			}
						
			//max
			if(isset($arr_data[$x]['E'])){
				$max = $arr_data[$x]['E']; 				
			}else{
				$max = 0;
			}
						
			//CycleCount
			if(isset($arr_data[$x]['F'])){
				$cycleCount = $arr_data[$x]['F']; 				
			}else{
				$cycleCount = 0;
			}
						
			//reorderqty
			if(isset($arr_data[$x]['G'])){
				$reorderQty = $arr_data[$x]['G']; 				
			}else{
				$reorderQty = 0;
			}
						
			//inventory method
			if(isset($arr_data[$x]['H'])){
				$inventoryMethod = $arr_data[$x]['H']; 				
			}else{
				$inventoryMethod = "";
			}
						
			//temperature
			if(isset($arr_data[$x]['I'])){
				$temperature = $arr_data[$x]['I']; 				
			}else{
				$temperature = "";
			}
						
			//cost
			if(isset($arr_data[$x]['J'])){
				$cost = $arr_data[$x]['J']; 				
			}else{
				$cost = "";
			}
						
			//upc
			if(isset($arr_data[$x]['K'])){
				$upc = $arr_data[$x]['K']; 		
				$arrUpc = explode("|", $upc);
				$attrSet = $arrUpc[0]; 
			}else{
				$upc = "";
				$attrSet = "";
			}
						
			//track lot
			if(isset($arr_data[$x]['L'])){
				$trackLot = $arr_data[$x]['L']; 				
			}else{
				$trackLot = "";
			}
						
			//track serial
			if(isset($arr_data[$x]['M'])){
				$trackSerial = $arr_data[$x]['M']; 				
			}else{
				$trackSerial = "";
			}
						
			//track expdate
			if(isset($arr_data[$x]['N'])){
				$trackExpdate = $arr_data[$x]['N']; 				
			}else{
				$trackExpdate = "";
			}
						
			//primary unit of measure
			if(isset($arr_data[$x]['O'])){
				$primaryUnitOfMeasure = $arr_data[$x]['O']; 				
			}else{
				$primaryUnitOfMeasure = "";
			}
						
			//packaging unit
			if(isset($arr_data[$x]['P'])){
				$packagingUnit = $arr_data[$x]['P']; 				
			}else{
				$packagingUnit = "";
			}
						
			//packing uom qty
			if(isset($arr_data[$x]['Q'])){
				$packingUomQty = $arr_data[$x]['Q']; 				
			}else{
				$packingUomQty = "";
			}
						
			//length
			if(isset($arr_data[$x]['R'])){
				$length = $arr_data[$x]['R']; 				
			}else{
				$length = "";
			}
						
			//width
			if(isset($arr_data[$x]['S'])){
				$width = $arr_data[$x]['S']; 				
			}else{
				$width = "";
			}
						
			//height
			if(isset($arr_data[$x]['T'])){
				$height = $arr_data[$x]['T']; 				
			}else{
				$height = "";
			}
						
			//weight
			if(isset($arr_data[$x]['U'])){
				$weight = $arr_data[$x]['U']; 				
			}else{
				$weight = "";
			}
						
			//qualifiers
			if(isset($arr_data[$x]['V'])){
				$qualifiers = $arr_data[$x]['V']; 				
			}else{
				$qualifiers = "";
			}
						
			//storage setup
			if(isset($arr_data[$x]['W'])){
				$storageSetup = $arr_data[$x]['W']; 				
			}else{
				$storageSetup = "";
			}
						
			//variable setup
			if(isset($arr_data[$x]['X'])){
				$variableSetup = $arr_data[$x]['X']; 				
			}else{
				$variableSetup = "";
			}
						
			//nmfc
			if(isset($arr_data[$x]['Y'])){
				$nmfc = $arr_data[$x]['Y']; 				
			}else{
				$nmfc = "";
			}
						
			//lot number required
			if(isset($arr_data[$x]['Z'])){
				$lotNumberReq = $arr_data[$x]['Z']; 				
			}else{
				$lotNumberReq = "";
			}
						
			//serial number required
			if(isset($arr_data[$x]['AA'])){
				$serialNumberReq = $arr_data[$x]['AA']; 				
			}else{
				$serialNumberReq = "";
			}
						
			//serial number must be unique
			if(isset($arr_data[$x]['AB'])){
				$serialNumberMustBeUnique = $arr_data[$x]['AB']; 				
			}else{
				$serialNumberMustBeUnique = "";
			}
						
			//exp date req
			if(isset($arr_data[$x]['AC'])){
				$expDateReq = $arr_data[$x]['AC']; 				
			}else{
				$expDateReq = "";
			}
						
			//enable cost
			if(isset($arr_data[$x]['AD'])){
				$enableCost = $arr_data[$x]['AD']; 				
			}else{
				$enableCost = "";
			}
						
			//cost required
			if(isset($arr_data[$x]['AE'])){
				$costRequired = $arr_data[$x]['AE']; 				
			}else{
				$costRequired = "";
			}
						
			//is haz mat
			if(isset($arr_data[$x]['AF'])){
				$isHazMat = $arr_data[$x]['AF']; 				
			}else{
				$isHazMat = "";
			}
						
			//haz mat id
			if(isset($arr_data[$x]['AG'])){
				$hazMatId = $arr_data[$x]['AG']; 				
			}else{
				$hazMatId = "";
			}
						
			//hazmatshippingname
			if(isset($arr_data[$x]['AH'])){
				$hazMatShippingName = $arr_data[$x]['AH']; 				
			}else{
				$hazMatShippingName = "";
			}
						
			//HazMatHazardClass
			if(isset($arr_data[$x]['AI'])){
				$hazMatHazardClass = $arr_data[$x]['AI']; 				
			}else{
				$hazMatHazardClass = "";
			}
						
			//HazMatPackingGroup
			if(isset($arr_data[$x]['AJ'])){
				$hazMatPackingGroup = $arr_data[$x]['AJ']; 				
			}else{
				$hazMatPackingGroup = "";
			}
						
			//HazMatFlashPoint
			if(isset($arr_data[$x]['AK'])){
				$hazMatFlashPoint = $arr_data[$x]['AK']; 				
			}else{
				$hazMatFlashPoint = "";
			}
						
			//HazMatLabelCode
			if(isset($arr_data[$x]['AL'])){
				$hazMatLabelCode = $arr_data[$x]['AL']; 				
			}else{
				$hazMatLabelCode = "";
			}
						
			//HazMatFlag
			if(isset($arr_data[$x]['AM'])){
				$hazMatFlag = $arr_data[$x]['AM']; 				
			}else{
				$hazMatFlag = "";
			}
						
			//ImageURL
			if(isset($arr_data[$x]['AN'])){
				$imageUrl = $arr_data[$x]['AN']; 				
			}else{
				$imageUrl = "";
			}
						
			//StorageCountScriptTemplateID
			if(isset($arr_data[$x]['AO'])){
				$storageCountScriptTemplateId = $arr_data[$x]['AO']; 				
			}else{
				$storageCountScriptTemplateId = "";
			}
						
			//StorageRates
			if(isset($arr_data[$x]['AP'])){
				$storageRates = $arr_data[$x]['AP']; 				
			}else{
				$storageRates = "";
			}
						
			//OutboundMobileSerializationBehavior
			if(isset($arr_data[$x]['AQ'])){
				$outboundMobileSerializationBehavior = $arr_data[$x]['AQ']; 				
			}else{
				$outboundMobileSerializationBehavior = "";
			}
						
			//Price
			if(isset($arr_data[$x]['AR'])){
				$price = $arr_data[$x]['AR']; 				
			}else{
				$price = "";
			}
						
			//TotalQty
			if(isset($arr_data[$x]['AS'])){
				$totalQty = $arr_data[$x]['AS']; 				
			}else{
				$totalQty = "";
			}
						
			//UnitType
			if(isset($arr_data[$x]['AT'])){
				$unitType = $arr_data[$x]['AT']; 				
			}else{
				$unitType = "";
			}
			
			//POType
			if(isset($arr_data[$x]['AU'])){
				$poType = $arr_data[$x]['AU']; 				
			}else{
				$poType = "";
			}
			
			//updated
			$updatedBy = $user=$this->session->userdata('pkUserId');
						
			if($price <> ""){
				
			//update the record
			$sql = "INSERT INTO ".$this->tableInv."_".$client." (doc_number, sku_config, sku_simple, sku_description, min, max, cycle_count,";
			$sql .= " reorder_qty, inventor_method, temperature, cost, upc, track_lot, track_serial, track_expdate, primary_unit_of_measure,";
			$sql .= " packaging_unit, packaging_uom_qty, length, width, height, weiight, qualifiers, storage_setup, variable_setup, ";
			$sql .= " nmfc, lot_number_required, serial_number_required, serial_number_must_be_unique, exp_date_req, enable_cost, ";
			$sql .= " cost_required, is_haz_mat, haz_mat_id, haz_mat_shipping_name, haz_mat_hazard_class, haz_mat_packing_group,";
			$sql .= " haz_mat_flash_point, haz_mat_label_code, haz_mat_flat, image_url, storage_count_stript_template_id, storage_rates,";
			$sql .= " outbound_mobile_serialization_behavior, price, total_qty, unit_type, updated_by, attribute_set, po_type) VALUES";
			$sql .= " (".$doc_number.", '".strtoupper($skuConfig)."', '".strtoupper($skuSimple)."', '".$skuDescription."', '".$min."', ".$max.", ".$cycleCount.",";
			$sql .= " ".$reorderQty.", '".$inventoryMethod."', '".$temperature."', '".$cost."', '".$upc."', '".$trackLot."', '".$trackSerial."', '".$trackExpdate."', '".$primaryUnitOfMeasure."',";
			$sql .= " '".$packagingUnit."', '".$packingUomQty."', '".$length."', '".$width."', '".$height."', '".$weight."', '".$qualifiers."', '".$storageSetup."', '".$variableSetup."', ";
			$sql .= " '".$nmfc."', '".$lotNumberReq."', '".$serialNumberReq."', '".$serialNumberMustBeUnique."', '".$expDateReq."', '".$enableCost."', ";
			$sql .= " '".$costRequired."', '".$isHazMat."', '".$hazMatId."', '".$hazMatShippingName."', '".$hazMatHazardClass."', '".$hazMatPackingGroup."',";
			$sql .= " '".$hazMatFlashPoint."', '".$hazMatLabelCode."', '".$hazMatFlag."', '".$imageUrl."', '".$storageCountScriptTemplateId."', '".$storageRates."',";
			$sql .= " '".$outboundMobileSerializationBehavior."', '".$price."', '".$totalQty."', '".$unitType."',".$updatedBy.",'".$attrSet."','".$poType."')";
			
			$this->db->query($sql);
			
			}
						
		}

		$this->db->trans_complete();
		
	}
	
	function saveToInboundInventory($client, $doc_number, $created_by, $arr_data){
		$this->load->model( array("invsync_m", 'clientoptions_m') );

		//start parse the array from excel
		$sizeRowX = count($arr_data); 
		$sizeRowY = count($arr_data[1]);
		$brandName = trim($arr_data[8]['C']);
        $brandInitial = $this->clientoptions_m->get( $client, 'brand_initial' );
        $iBrand = strtoupper($brandInitial['option_value']);
        $this->db->trans_begin();
		$tmp_A = "";
		$tmp_B = "";
		$tmp_C = "";
		$tmp_D = "";
		$tmp_E = "";
		$tmp_F = "";
		$tmp_G = "";
		$tmp_H = "";
		$msgRet = array();
		$tmpArrValSKUConfig = array();
		for($x=19;$x<=$sizeRowX;$x++){
			//------------------get the field items--------------------------
			//no
			if(isset($arr_data[$x]['A'])){
				if($arr_data[$x]['A'] <> ""){
					$no = $arr_data[$x]['A'];	
					$tmp_A = $no;
				}
			}else{
				$no = $tmp_A;
			}
			//po type
			if(isset($arr_data[$x]['B'])){
				if($arr_data[$x]['B'] <> ""){
					$poType = $arr_data[$x]['B'];
					$tmp_B = $poType;
				}	
			}else{
				$poType = $tmp_B; 
			}
			//season
			if(isset($arr_data[$x]['C'])){
				if($arr_data[$x]['C'] <> ""){
					$season = $arr_data[$x]['C'];
					$tmp_C = $season; 	
				}
			}else{
				$season = $tmp_C; 
			}
			//year
			if(isset($arr_data[$x]['D'])){
				if($arr_data[$x]['D'] <> ""){
					$year = $arr_data[$x]['D'];
					$tmp_D = $year;
				}	
			}else{
				$year = $tmp_D; 
			}
			//gender
			if(isset($arr_data[$x]['E'])){
				if($arr_data[$x]['E'] <> ""){
					if(strtoupper(trim($arr_data[$x]['E'])) == "MAN" or strtoupper(trim($arr_data[$x]['E'])) == "MEN"){
						$arr_data[$x]['E'] = "M";
					}
					if(strtoupper(trim($arr_data[$x]['E'])) == "LADIES" or strtoupper(trim($arr_data[$x]['E'])) == "WOMAN" or strtoupper(trim($arr_data[$x]['E'])) == "WOMEN"){
						$arr_data[$x]['E'] = "F";
					}
					$gender = $arr_data[$x]['E'];
					$tmp_E = $gender;
				} 	
			}else{
				$gender = $tmp_E; 
			}
			//category
			if(isset($arr_data[$x]['F'])){
				if($arr_data[$x]['F'] <> ""){
					$category = $arr_data[$x]['F'];
					$tmp_F = $category;
				}	
			}else{
				$category = $tmp_F;
			}
			//sub category	
			if(isset($arr_data[$x]['G'])){
				if($arr_data[$x]['G'] <> ""){
					$subcategory = $arr_data[$x]['G'];
					$tmp_G = $subcategory; 	
				}
			}else{
				$subcategory = $tmp_G;
			}

			//sku
			if($arr_data[$x]['H'] <> ""){
				$sku = $arr_data[$x]['H'];
			}
			//product name
			if($arr_data[$x]['I'] <> ""){
				$productname = $arr_data[$x]['I'];
			}
			//color name
			if($arr_data[$x]['J'] <> ""){
				$colorname = $arr_data[$x]['J'];
			}
			
			//material
			if($arr_data[$x]['R'] <> ""){
				$material = $arr_data[$x]['R'];
			}
			//description
			$description = $arr_data[$x]['T'];

			//product instruction
			if($arr_data[$x]['U'] <> ""){
				$productinstruction = $arr_data[$x]['U'];
			}

			//retail price -> ?
			$retailprice = $arr_data[$x]['K'];
			//check if the string contain '=' which refer to another cell value
			if (substr($retailprice, 0, 1) == "=") {
				//remove except alphabet
				$retailprice_string = preg_replace("/[^A-Z]+/", "", $retailprice);
				//remove except numeric
				$retailprice_int = preg_replace('/[^0-9.]+/', '', $retailprice);
				//get the value from another cell
				$retailprice = $arr_data[$retailprice_int][$retailprice_string];
			}			
			
			//size
			$size = $arr_data[$x]['N'];
			
			//qty
			$qty = $arr_data[$x]['O'];
			
			//total value
			$totalvalue = $retailprice*$qty;
			
			//------------------ready for processing the query----------------------------
			//sku_config
			if($sku == ""){
				//sku not exist
				//get 2 digit inisial brand
				//get 3 digit inisial product name
				$tmp = explode(" ",$productname);
				if(isset($tmp[1])){
					//more than 1 word
					$inProdName = substr($tmp[0],0,2).susbtr($tmp[1],0,1);	
				}else{
					//only 1 word
					$inProdName = substr($productname,0,3);
				}
				//get 2 digit inisial color
				$tmp = explode(" ",$colorname);
				if(isset($tmp[1])){
					//more than 1 word
					$inWarna = substr($tmp[0],0,1).susbtr($tmp[1],0,1);	
				}else{
					//only 1 word
					$inWarna = substr($colorname,0,2);
				}
				//compose the sku config
				$sku_config = $iBrand."-".$inProdName." ".$inWarna;
			}else{
				//sku exist
				//compose the sku config
				$sku_config = trim($sku);
			}
			
			//sku simple
			if($size == ""){
				$sku_simple = $sku_config."OS";
                $size = 'One Size';
			}elseif(strtoupper(trim($size)) == "F"){
				$sku_simple = $sku_config."OS";
                $size = 'One Size';
			}else{
				$sku_simple = $sku_config.$size;
			}
						
				//get 2 digit inisial brand
                $iBrand = strtoupper($iBrand);
				//get 1 digit inisial gender
				$inGender = strtoupper(substr($gender, 0, 1));
				//get category
				$inCategory = $category;
				//get sub category
				$inSubCategory = $subcategory;
				//get productname
				$inProductName = $productname;
				//get size
				$inSize = $size;
				//get color
				$inColor = $colorname;
            $isMultiBrand = $this->clientoptions_m->get($client, 'multi_brand');
            if(!empty($isMultiBrand) && $isMultiBrand['option_value'] == 1) { // paraplou use brand code
                $brandList = $this->clientoptions_m->get($client, 'brand_code');
                $_iBrand = strtolower(str_replace(' ', '', $brandName));
                $brandList = json_decode($brandList['option_value'], true);

                $iBrand = array_search($_iBrand, $brandList);

            }

            $sku_description = $iBrand.",".$inGender.",".$inCategory.",".$inSubCategory.",".$inProductName.",S:".$inSize.",".$inColor;

			//min
			$min = "";
			
			//max
			$max = 0;			
			
			//cycle count
			$cycle_count = 30;
			
			//re-order qty
			$reorder_qty = 0;
			
			//inventor method
			$inventor_method = "FIFO";
			
			//temperature
			$temperature = "";
			
			//cost
			$cost = $retailprice;
			
			//upc
			if($client == "6"){
				//internal client
				//get 2 digit inisial brand
				$tmp = str_replace(":","",$brandName);
				$tmp = explode(" ",trim($tmp));
                if(count($tmp)>1){
                    $itemBrand = substr($tmp[0], 0, 1).substr($tmp[1], 0, 1);
                }else{
                    $itemBrand = substr($tmp[0], 0, 2);
                }
                $itemBrand = strtoupper($itemBrand);
				$itemAttrSet = "";
				$itemSize = $size;
				$itemColor = $colorname;

				$upc = $itemAttrSet."|".$itemSize."|".$itemColor."|".$itemBrand;
                $sku_description = explode(',', $sku_description);
                $sku_description[0] = $itemBrand;
                $sku_description = implode(',', $sku_description);
			}else{
				//e2e client
				$itemAttrSet = "";
				$itemSize = $size;
				$itemColor = $colorname;
				
				$upc = $itemAttrSet."|".$itemSize."|".$itemColor;  
			}
			
			//track lot
			$track_lot = "0";
			
			//track serial
			$track_serial = "0";
			
			//track expdate
			$track_expdate = "0";
			
			//primary unit of measure
			$primary_unit_of_measure = "EACH";
			
			//packaging unit
			$packaging_unit = "EACH";
			
			//packaging uom qty
			$packaging_uom_qty = "1";
			
			//length
			$length = "1";
			
			//width
			$width = "1";
			
			//height
			$height = "1";
			
			//weiight
			$weiight = "1";
			
			//qualifiers
			$qualifiers = "";
			
			//storage_setup
			$storage_setup = "";
			
			//variable_setup
			$variable_setup = "";
			
			//nmfc
			$nmfc = "";
			
			//lot_number_required
			$lot_number_required = "0";
			
			//serial_number_required
			$serial_number_required = "0";
			
			//serial_number_must_be_unique
			$serial_number_must_be_unique = "0";
			
			//exp_date_req
			$exp_date_req = "0";
			
			//enable_cost
			$enable_cost = "0";
			
			//cost_required
			$cost_required = "0";
			
			//is_haz_mat
			$is_haz_mat = "";
			
			//haz_mat_id
			$haz_mat_id = "";
			
			//haz_mat_shipping_name
			$haz_mat_shipping_name = "";
			
			//haz_mat_hazard_class
			$haz_mat_hazard_class = "";
			
			//haz_mat_packing_group
			$haz_mat_packing_group = "";
			
			//haz_mat_flash_point
			$haz_mat_flash_point = "";
			
			//haz_mat_label_code
			$haz_mat_label_code = "";
			
			//haz_mat_flat
			$haz_mat_flat = "";
			
			//image_url
			$image_url = "";
			
			//storage_count_stript_template_id
			$storage_count_stript_template_id = "";
			
			//storage_rates
			$storage_rates = "";
			
			//outbound_mobile_serialization_behavior
			$outbound_mobile_serialization_behavior = "";
			
			//price
			$price = $retailprice;
			
			//total_qty
			$total_qty = $qty;
			
			//unit_type
			$unit_type = "";
			
			//updated_by
			$updated_by = $created_by;

            // attribute set
            $attributeSet = $this->_findAttrSet($client, $inGender, $category);
            $upc = $attributeSet['id'] . $upc;
            $itemAttrSet = $attributeSet['id'];

			log_message('debug', "skuconfig::".$sku_config."::retailprice::".$retailprice);

            if($retailprice <> ""){
				//validation for SKU Config
				if(empty($tmpArrValSKUConfig[$productname."-".$colorname])){
					$tmpArrValSKUConfig[$productname."-".$colorname][] = $sku;
				}else{
					if(!in_array($sku, $tmpArrValSKUConfig[$productname."-".$colorname])){	
						$tmpArrValSKUConfig[$productname."-".$colorname][] = $sku;
                    	$msgRet['problemskuconfig'][$productname."-".$colorname] = $tmpArrValSKUConfig[$productname."-".$colorname];
					}
				}
            	
				//check sku simple from 3pl sync table
				$checkReturn = $this->invsync_m->findBySku(strtoupper($sku_simple), $client);

                // check wheter problem detected
				if( !empty($checkReturn) and strtoupper($poType)=='NEW') {
                    $msgRet['problem'][] = array('sku_simple' => $sku_simple, 'poTypeInFile' => $poType, 'poTypeInSys' => 'REPEAT');
                    $poType = "REPEAT";
				} else if( empty($checkReturn) && strtoupper($poType) != 'NEW' ) {
                    $msgRet['problem'][] = array('sku_simple' => $sku_simple, 'poTypeInFile' => $poType, 'poTypeInSys' => 'NEW');
                    $poType = "NEW";
                }
													
				$sql = "INSERT INTO ".$this->tableInv."_".$client." (doc_number, sku_config, sku_simple, sku_description, min, max, cycle_count,";
				$sql .= " reorder_qty, inventor_method, temperature, cost, upc, track_lot, track_serial, track_expdate, primary_unit_of_measure,";
				$sql .= " packaging_unit, packaging_uom_qty, length, width, height, weiight, qualifiers, storage_setup, variable_setup, ";
				$sql .= " nmfc, lot_number_required, serial_number_required, serial_number_must_be_unique, exp_date_req, enable_cost, ";
				$sql .= " cost_required, is_haz_mat, haz_mat_id, haz_mat_shipping_name, haz_mat_hazard_class, haz_mat_packing_group,";
				$sql .= " haz_mat_flash_point, haz_mat_label_code, haz_mat_flat, image_url, storage_count_stript_template_id, storage_rates,";
				$sql .= " outbound_mobile_serialization_behavior, price, total_qty, unit_type, updated_by, attribute_set, po_type) VALUES";
				$sql .= " (".$doc_number.", '".strtoupper($sku_config)."', '".strtoupper($sku_simple)."', '".$sku_description."', '".$min."', ".$max.", ".$cycle_count.",";
				$sql .= " ".$reorder_qty.", '".$inventor_method."', '".$temperature."', '".$cost."', '".$upc."', '".$track_lot."', '".$track_serial."', '".$track_expdate."', '".$primary_unit_of_measure."',";
				$sql .= " '".$packaging_unit."', '".$packaging_uom_qty."', '".$length."', '".$width."', '".$height."', '".$weiight."', '".$qualifiers."', '".$storage_setup."', '".$variable_setup."', ";
				$sql .= " '".$nmfc."', '".$lot_number_required."', '".$serial_number_required."', '".$serial_number_must_be_unique."', '".$exp_date_req."', '".$enable_cost."', ";
				$sql .= " '".$cost_required."', '".$is_haz_mat."', '".$haz_mat_id."', '".$haz_mat_shipping_name."', '".$haz_mat_hazard_class."', '".$haz_mat_packing_group."',";
				$sql .= " '".$haz_mat_flash_point."', '".$haz_mat_label_code."', '".$haz_mat_flat."', '".$image_url."', '".$storage_count_stript_template_id."', '".$storage_rates."',";
				$sql .= " '".$outbound_mobile_serialization_behavior."', '".$price."', '".$total_qty."', '".$unit_type."', ".$updated_by.", ".(int)$itemAttrSet.", '".strtoupper($poType)."')";
				$this->db->query($sql);
			
			}
		}
        //end parse the array from excel

        if(empty($msgRet)) {
            $this->db->trans_commit();
        } else {
            $this->db->trans_rollback();
        }
		return $msgRet;
	}

    protected function _findAttrSet($cId, $gender, $category) {
        $key = $gender.$category;

        if(!isset($this->attrList[$cId])) {
            $this->attrList[$cId] = array();
        }

        if(!isset($this->attrList[$cId][$key])) {

            $this->load->model('clientoptions_m');
            $attrSetList = $this->clientoptions_m->get($cId, 'attribute_set');
            if(!empty($attrSetList)) {
                $attrSetList = json_decode($attrSetList['option_value'], true);
            }

            $attrSetName = array();

            switch($gender) {
                case 'M':
                    $attrSetName[] = 'men'; break;
                case 'F':
                    $attrSetName[] = 'women'; break;
                case 'U':
                    $attrSetName[] = 'unisex'; break;
            }

            $attrSetName[] = strtolower($category);
            $attrSetName = implode('', $attrSetName);
            $attributeSet = array('name' => $attrSetName);
            $attributeSet['id'] = array_search($attrSetName, $attrSetList);

            log_message("debug", 'attribute set::'.$gender.'::'.$category.print_r($attributeSet, true));
            $this->attrList[$cId][$key] = $attributeSet;

        }

        return $this->attrList[$cId][$key];
    }
	
	public function updateAttrSetInboundInventory($client, $doc_number, $data, $id){
        $this->load->model( 'clientoptions_m' );
        $upc = json_decode($data['upc'], true);
        $attrSet = $this->clientoptions_m->get($client, 'attribute_set');
        $attrSet = json_decode($attrSet['option_value'], true);
        $upc[0] = $attrSet[$data['attribute_set']];
		$sql = "UPDATE ".$this->tableInv."_".$client." set attribute_set='".$data['attribute_set']."', upc='".implode('|', $upc)."' WHERE doc_number=".$doc_number." and id=".$id;
		$this->db->query($sql);
	}
	
	public function insertInboundDocument($doc_number, $client_id, $note, $type, $status, $created_by, $filename, $reference_id){
		//check first, if upload inbound form is not new data
		//$sqlCheck = "SELECT * FROM ".$this->table." WHERE reference_id=".$reference_id;
		//$queryCheck = $this->db->query($sqlCheck);
		//$rowCheck = $queryCheck->result_array();

		//if(empty($rowCheck)){
			//insert
			$sql = "INSERT INTO ".$this->table."(doc_number, client_id, note, type, status, created_by, filename, reference_id) VALUES";
			$sql .= " ('".$doc_number."',".$client_id.",'".$note."',".$type.",".$status.",".$created_by.",'".$filename."',".$reference_id.")";
			$this->db->query($sql);
			//update row regarding to upload merchandising
			$sql = "UPDATE ".$this->table." SET status=2 WHERE id=".$reference_id;
			$this->db->query($sql);
		//}else{
		/*
			$this->db->trans_start();
			
			//delete table inb_inventory_stock_<$client_id>
			//$sql = "DELETE FROM ".$this->tableInvStock."_".$client_id." WHERE doc_number=".$rowCheck[0]['id'];
			//$this->db->query($sql);
			//update row regarding to upload inb_document
			$sql = "UPDATE ".$this->table." SET filename='".$filename."', type=".$type.", status=".$status.", updated_at='".date("Y-m-d H:i:s")."' WHERE id=".$rowCheck[0]['id'];
			$this->db->query($sql);
			//update row regarding to upload merchandising
			$sql = "UPDATE ".$this->table." SET status=2 WHERE id=".$reference_id;
			$this->db->query($sql);
			
			$this->db->trans_complete();
		}
		 */
	}

	function saveToInboundInventoryStock($client, $doc_number, $created_by, $arr_data, $reference_id){
		//start parse the array from excel
		$sizeRowX = count($arr_data)+4; 
		$sizeRowY = count($arr_data[1]);

		$this->db->trans_start();
		for($x=12;$x<=$sizeRowX;$x++){
			if(isset($arr_data[$x]['A'])){
				//------------------get the field items--------------------------
				//sku code
				$skuCode = $arr_data[$x]['A'];
				
				//sku description
				$skuDescription = $arr_data[$x]['B'];	
	
				//size
				$size = $arr_data[$x]['C'];	
				
				//qty
				$qty = $arr_data[$x]['D'];
					
				//qty inbound
				if(isset($arr_data[$x]['E'])){
					$qtyInbound = $arr_data[$x]['E'];
				}else{
					$qtyInbound = "";
				}	
	
				//note
				if(isset($arr_data[$x]['F'])){
					$note = $arr_data[$x]['F'];
				}else{
					$note = "";
				}	
	
				//problem
				if(isset($arr_data[$x]['G'])){
					$problem = $arr_data[$x]['G'];
				}else{
					$problem = "";
				}
					
				//actionTaken
				if(isset($arr_data[$x]['H'])){
					$actionTaken = $arr_data[$x]['H'];
				}else{
					$actionTaken = "";
				}
					
				//loc bin
				if(isset($arr_data[$x]['I'])){
					$locBin = $arr_data[$x]['I'];
				}else{
					$locBin = "";
				}	
				
				//check if receiving inbound form more than one time
				$query = $this->db->query("SELECT * FROM inb_document WHERE reference_id=".$reference_id." AND DATE(created_at)='".date("Y-m-d")."'");
				$strRec = "";
				if ($query->num_rows() > 1){
					$strRec = "".($query->num_rows());
				}		
				//------------------ready for processing the query----------------------------
				$query = $this->db->query("SELECT * FROM inb_inventory_item_".$client." WHERE sku_description='".$skuDescription."' AND doc_number=".$reference_id);
				$row = $query->result_array();
				if(isset($row[0]['id'])){
					// item_id
					$item_id = $row[0]['id'];
					
					//doc_number
					//same value with param $doc_number
					
					//reference_num
					$tmpInisialBrand = explode(",",$row[0]['sku_description']);
						$reference_num = "REC".$strRec.$tmpInisialBrand[0].date("dmy");
						
					//quantity
					//same value with field excel $qty
					
					//bin_location
					//same value with field excel $locBin
					
					//created_at
					$created_at = date("Y-m-d H:i:s");
					
					//created_by
					//same value with param $created_by
								
					$sql = "INSERT INTO ".$this->tableInvStock."_".$client." (item_id, doc_number, reference_num, quantity";
					$sql .= ", bin_location, created_at, created_by) VALUES";
					$sql .= " (".$item_id.", ".$doc_number.", '".$reference_num."', ".$qty.", '".$locBin."', '".$created_at."', ".$created_by.")";
					$this->db->query($sql);
				}					
			}
		}
		$this->db->trans_complete();
		//end parse the array from excel
		
		
		return TRUE;
	}

	public function getParamInboundMage($client, $doc){
		$param = array();
		
		//get data from table inb_inventory_item_<client_id>
		$result = $this->getInboundInvItem($client, $doc);
		foreach($result as $item){
			$sku = $item['sku_simple'];
			$set = $item['attribute_set'];
			$type = "simple";
			/*
			 * 'simple' : Simple Product 
			 * 'grouped' : Grouped Product
			 * 'configurable' : Configurable Product
			 * 'virtual' : Virtual Product
			 * 'bundle' : Bundle Product
			 */
			
			$categories = array();
			$websites = array();
				$sku_description =  explode(",",$item['sku_description']);
			$name = $sku_description[4];
			$description = $name." description";
			$short_description = $name." short description";
			$weight = $item['weiight'];
			$status = "1";
			/*
			 1 : enabled
			 2 : disabled
			 */
			$url_key = "";
			$url_path = "";
			$visibility = "1"; 
				/*
				1 : Not Visible Individualy
				2 : Catalog
				3 : Search
				4 : Catalog, Search
				*/
			$category_ids = array();
			$website_ids = array(1);
			$gift_message_available = "";
			$price = $item['price'];
			$tax_class_id = "";
			$meta_title = "";
			$meta_keyword = "";
			$meta_description = "";
			$qty = $item['total_qty'];
			
			$param[] = array($type, $set, $sku, array(
			    'categories' => $categories,
			    'websites' => $websites,
			    'name' => $name,
			    'description' => $description,
			    'short_description' => $short_description,
			    'weight' => $weight,
			    'status' => $status,
			    'url_key' => $url_key,
			    'url_path' => $url_path,
			    'visibility' => $visibility,
			    'price' => $price,
			    'tax_class_id' => $tax_class_id,
			    'meta_title' => $meta_title,
			    'meta_keyword' => $meta_keyword,
			    'meta_description' => $meta_description,
			    'stock_data' => array(
			    					'qty' => $qty
								)
			));			
			
		}

		return $param;
	}

	public function getParamInbound3PL($client, $doc){
		$param = array();

		//get data from table inb_inventory_item_<client_id>
		$result = $this->getInboundInvItem($client, $doc);
		$strItem = "";
		foreach($result as $item){
			$sku_config = $item['sku_config'];
			$sku_simple = $item['sku_simple'];
			$sku_description =  explode(",",$item['sku_description']);
				$name = $sku_description[4];
			if($item['min'] == ""){
				$min = 0;
			}else{
				$min = $item['min'];	
			}
			if($item['max'] == ""){
				$max = 0;
			}else{
				$max = $item['max'];	
			}
			$cycle_count = $item['cycle_count'];
			$reorder_qty = $item['reorder_qty'];
			$inventor_method = $item['inventor_method'];
			$temperature = $item['temperature'];
			$cost = $item['cost'];
			$upc = $item['upc'];
			$track_lot = $item['track_lot'];
			$track_serial = $item['track_serial'];
			$track_expdate = $item['track_expdate'];
			$primary_unit_of_measure = $item['primary_unit_of_measure'];
			$packaging_unit = $item['packaging_unit'];
			$packaging_uom_qty = $item['packaging_uom_qty'];
			$length = $item['length'];
			$width = $item['width'];
			$height = $item['height'];
			$weiight = $item['weiight'];
			$qualifiers = $item['qualifiers'];
			$storage_setup = $item['storage_setup'];
			$variable_setup = $item['variable_setup'];
			$nmfc = $item['nmfc'];
			$lot_number_required = $item['lot_number_required'];
			$serial_number_required = $item['serial_number_required'];
			$serial_number_must_be_unique = $item['serial_number_must_be_unique'];
			$exp_date_req = $item['exp_date_req'];
			$enable_cost = $item['enable_cost'];
			$cost_required = $item['cost_required'];
			$is_haz_mat = $item['is_haz_mat'];
			if($is_haz_mat == ""){
				$is_haz_mat = 0;
			}
			$haz_mat_id = $item['haz_mat_id'];
			$haz_mat_shipping_name = $item['haz_mat_shipping_name'];
			$haz_mat_hazard_class = $item['haz_mat_hazard_class'];
			$haz_mat_packing_group = $item['haz_mat_packing_group'];
			$haz_mat_flash_point = $item['haz_mat_flash_point'];
			$haz_mat_label_code = $item['haz_mat_label_code'];
			$haz_mat_flat = $item['haz_mat_flat'];
			$image_url = $item['image_url'];
			$storage_count_stript_template_id = $item['storage_count_stript_template_id'];
			$storage_rates = $item['storage_rates'];
			$outbound_mobile_serialization_behavior = $item['outbound_mobile_serialization_behavior'];
			$price = $item['price'];
			$total_qty = $item['total_qty'];
			$unit_type = $item['unit_type'];
			$attribute_set = $item['attribute_set'];
			
			$strItem .= "
	         <vias:Item>
	            <!--Optional:-->
	            <vias:SKU>".$sku_simple."</vias:SKU>
	            <!--Optional:-->
	            <vias:Description>".$item['sku_description']."</vias:Description>
	            <!--Optional:-->
	            <vias:Description2></vias:Description2>
	            <!--vias:CustomerID>XXX</vias:CustomerID-->
	            <vias:Min>".$min."</vias:Min>
	            <vias:Max>".$max."</vias:Max>
	            <vias:ReorderQty>".$reorder_qty."</vias:ReorderQty>
	            <vias:CycleCount>".$cycle_count."</vias:CycleCount>
	            <!--Optional:-->
	            <vias:InventoryCategory>?</vias:InventoryCategory>
	            <vias:InventoryMethod>".$inventor_method."</vias:InventoryMethod>
	            <vias:Cost>".$cost."</vias:Cost>
	            <!--Optional:-->
	            <vias:UPC>".$upc."</vias:UPC>
	            <vias:IsTrackLotNumber>".$track_lot."</vias:IsTrackLotNumber>
	            <vias:IsTrackLotNumberRequired>".$lot_number_required."</vias:IsTrackLotNumberRequired>
	            <vias:IsTrackSerialNumber>".$track_serial."</vias:IsTrackSerialNumber>
	            <vias:IsTrackSerialNumberRequired>".$serial_number_required."</vias:IsTrackSerialNumberRequired>
	            <vias:IsTrackSerialNumberUnique>".$serial_number_must_be_unique."</vias:IsTrackSerialNumberUnique>
	            <vias:IsTrackExpirationDate>".$track_expdate."</vias:IsTrackExpirationDate>
	            <vias:IsTrackExpirationDateRequired>0</vias:IsTrackExpirationDateRequired>
	            <vias:IsTrackCost>0</vias:IsTrackCost>
	            <vias:IsTrackCostRequired>".$cost_required."</vias:IsTrackCostRequired>
	            <!--Optional:-->
	            <vias:NMFC>".$nmfc."</vias:NMFC>
	            <!--Optional:-->
	            <vias:InventoryUnitOfMeasure>".$primary_unit_of_measure."</vias:InventoryUnitOfMeasure>
	            <!--Optional:-->
	            <vias:LabelingUnitLength>".$length."</vias:LabelingUnitLength>
	            <vias:LabelingUnitWidth>".$width."</vias:LabelingUnitWidth>
	            <vias:LabelingUnitHeight>".$height."</vias:LabelingUnitHeight>
	            <vias:LabelingUnitWeight>".$weiight."</vias:LabelingUnitWeight>
	            <vias:IsHazMat>".$is_haz_mat."</vias:IsHazMat>
	            <!--Optional:-->
	            <vias:HazMatID>".$haz_mat_id."</vias:HazMatID>
	            <!--Optional:-->
	            <vias:HazMatShippingName>".$haz_mat_shipping_name."</vias:HazMatShippingName>
	            <!--Optional:-->
	            <vias:HazMatHazardClass>".$haz_mat_hazard_class."</vias:HazMatHazardClass>
	            <vias:HazMatPackingGroup>Default</vias:HazMatPackingGroup>
	            <!--Optional:-->
	            <vias:HazMatFlashPoint>".$haz_mat_flash_point."</vias:HazMatFlashPoint>
	            <!--Optional:-->
	            <vias:HazMatLabelCode>".$haz_mat_label_code."</vias:HazMatLabelCode>
	            <vias:HazMatFlag>Default</vias:HazMatFlag>
	            <!--Optional:-->
	            <vias:ImageUrl>".$image_url."</vias:ImageUrl>
	            <!--Optional:-->
	            <vias:ItemQualifiers>
	               <!--Zero or more repetitions:-->
	               <vias:string>".$qualifiers."</vias:string>
	            </vias:ItemQualifiers>
	         </vias:Item>			
			";			
		}
		return "<vias:Items>".$strItem."</vias:Items>";
	}
	
	public function saveToInvItems($client, $data){
		$this->db->trans_start();
		$lup=1;
		foreach($data as $k => $v){
			$sql = "INSERT INTO ".$this->tableInvItems."_".$client." VALUES";
			$sql .= " (".$lup.", \"".$v['SKU']."\", \"".$v['I_DESCRIPTION']."\",\"".date("Y-m-d H:i:s")."\")";
			$sql .= " ON DUPLICATE KEY UPDATE updated_at=\"".date("Y-m-d H:i:s")."\", sku_simple=\"".$v['SKU']."\", sku_description=\"".$v['I_DESCRIPTION']."\"";
			$this->db->query($sql);
			$lup++;
		}
		$this->db->trans_complete();
	}
	
}
