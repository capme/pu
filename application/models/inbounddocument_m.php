<?php
/*
 * model for handle extracted xls data into table inb_inventory_item(client_id)   
 */
class Inbounddocument_m extends MY_Model {
	
	var $db = null;
	var $table = 'inb_document';
	var $tableInv = 'inb_inventory_item';
	var $tableClient ='client';
	var $sorts = array(1 => "id");
	var $pkField = "id";
	var $path = "";
	
	function __construct()
    {
        parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
		$this->path = BASEPATH ."../public/inbound/catalog_product"; 
		$this->relation = array(
			array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField} AND {$this->table}.status = 1")
		);
		$this->select = array("{$this->table}.*", "{$this->tableClient}.client_code");
		$this->filters = array("client_id"=>"client_id");
		$this->group = array("client_id");
    }
	
	function getInboundInvItem($client, $doc){
		if(!$client) return array();
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->get_where('inb_inventory_item_'.$client, array('doc_number'=>$doc));
		$rows = $query->result_array();
		return $rows;
	}

	function getInboundDocumentRow($id){
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->get_where('inb_document', array('id'=>$id));
		$row = $query->row_array();		
		return $row;
	}

	function getInboundDocumentInfo($client) 
	{
		if(!$client) return array();
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->get_where('inb_document', array('client_id'=>$client, 'status'=>0));
		$row = $query->row_array();		
		return $row;
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
			$status=$statList[$_result->status];
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$_result->client_code,
					$_result->doc_number,
					$_result->note,
					'<a href="'.base_url().'listinbounddoc/exportFormItemImport?client='.$_result->client_id.'&doc='.$_result->doc_number.'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> Download Form Import</a>'
					
			);
		}
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
	}
	
	function updateStatusInboundDocumentList($id){
		$data=array('status'=>1);
		$this->db->where('id',$id);
		$this->db->update('inb_document',$data);
	}
	
	function saveToInboundInventory($client, $doc_number, $created_by, $arr_data){
		//start parse the array from excel
		$sizeRowX = count($arr_data); 
		$sizeRowY = count($arr_data[1]);
		$brandName = trim($arr_data[8]['C']);
		$this->db->trans_start();
		$tmp_A = "";
		$tmp_B = "";
		$tmp_C = "";
		$tmp_D = "";
		$tmp_E = "";
		$tmp_F = "";
		$tmp_G = "";
		$tmp_H = "";
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
			//purchase
			if(isset($arr_data[$x]['H'])){
				if($arr_data[$x]['H'] <> ""){
					$purchase = $arr_data[$x]['H'];
					$tmp_H = $purchase; 	
				}
			}else{
				$purchase = $tmp_H;
			}
			//sku
			if($arr_data[$x]['I'] <> ""){
				$sku = $arr_data[$x]['I'];	
			}
			//product name
			if($arr_data[$x]['J'] <> ""){
				$productname = $arr_data[$x]['J'];	
			}
			//color name
			if($arr_data[$x]['K'] <> ""){
				$colorname = $arr_data[$x]['K'];	
			}
			
			//color code
			$colorcode = $arr_data[$x]['L'];
				
			//fitting
			$fitting = $arr_data[$x]['M'];
				
			//material
			if($arr_data[$x]['N'] <> ""){
				$material = $arr_data[$x]['N'];	
			}
			//description
			$description = $arr_data[$x]['O'];	

			//product instruction
			if($arr_data[$x]['P'] <> ""){
				$productinstruction = $arr_data[$x]['P'];	
			}
			//product description
			if($arr_data[$x]['Q'] <> ""){
				$productdescription = $arr_data[$x]['Q'];	
			}
			//product name revision
			$productnamerevision = $arr_data[$x]['R'];	

			//short description
			$shortdescription = $arr_data[$x]['S'];	

			//meta description
			$metadescription = $arr_data[$x]['T'];	

			//meta keyword
			$metakeyword = $arr_data[$x]['U'];	

			//retail price sing
			$retailpricesing = $arr_data[$x]['W'];
			
			//retail price -> ?
			$retailprice = $arr_data[$x]['X'];
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
			$size = $arr_data[$x]['AA'];
			
			//qty
			$qty = $arr_data[$x]['AB'];
			
			//total value
			$totalvalue = $retailprice*$qty;
			
			//------------------ready for processing the query----------------------------
			//sku_config
			if($sku == ""){
				//sku not exist
				//get 2 digit inisial brand
				$tmp = str_replace(":","",$brandName);
				$tmp = explode(" ",trim($tmp));
					if(count($tmp)>1){
						$iBrand = substr($tmp[0], 0, 1).substr($tmp[1], 0, 1);
					}else{
						$iBrand = substr($tmp[0], 0, 1);
					}
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
				$sku_config = $inBrand."-".$inProdName." ".$inWarna; 
			}else{
				//sku exist
				//get 4 digit inisial product name
				$tmp = explode(" ",$productname);
				if(isset($tmp[1])){
					//more than 1 word
					$inProdName = substr($tmp[0],0,2).substr($tmp[1],0,2);	
				}else{
					//only 1 word
					if(strlen($productname) >= 4){
						$inProdName = substr($productname,0,4);
					}else{
						$inProdName = substr($productname,0,strlen($productname));
					}
				}
				//compose the sku config
				$sku_config = $sku."-".$inProdName; 
			}
			
			//sku simple
			if($size == ""){
				$sku_simple = $sku_config."-"."OS";
			}else{
				$sku_simple = $sku_config."-".$size;
			}
						
			//sku description
				//get 2 digit inisial brand
				$tmp = str_replace(":","",$brandName);
				$tmp = explode(" ",trim($tmp));
					if(count($tmp)>1){
						$iBrand = substr($tmp[0], 0, 1).substr($tmp[1], 0, 1);
					}else{
						$iBrand = substr($tmp[0], 0, 1);
					}
				//get 1 digit inisial gender
				$inGender = substr($gender, 0, 1);
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
						$iBrand = substr($tmp[0], 0, 1).substr($tmp[1], 0, 1);
					}else{
						$iBrand = substr($tmp[0], 0, 1);
					}
				
				$itemAttrSet = "";
				$itemSize = $size;
				$itemColor = $colorname;
				$itemBrand = $iBrand;
				
				$upc = $itemAttrSet."|".$itemSize."|".$itemColor."|".$itemBrand;  
			}else{
				//e2e client
				$itemAttrSet = "";
				$itemSize = $size;
				$itemColor = $colorname;
				
				$upc = $itemAttrSet."|".$itemSize."|".$itemColor;  
			}
			
			//track lot
			$track_lot = "";
			
			//track serial
			$track_serial = "";
			
			//track expdate
			$track_expdate = "";
			
			//primary unit of measure
			$primary_unit_of_measure = "";
			
			//packaging unit
			$packaging_unit = "";
			
			//packaging uom qty
			$packaging_uom_qty = "";
			
			//length
			$length = "";
			
			//width
			$width = "";
			
			//height
			$height = "";
			
			//weiight
			$weiight = "";
			
			//qualifiers
			$qualifiers = "";
			
			//storage_setup
			$storage_setup = "";
			
			//variable_setup
			$variable_setup = "";
			
			//nmfc
			$nmfc = "";
			
			//lot_number_required
			$lot_number_required = "";
			
			//serial_number_required
			$serial_number_required = "";
			
			//serial_number_must_be_unique
			$serial_number_must_be_unique = "";
			
			//exp_date_req
			$exp_date_req = "";
			
			//enable_cost
			$enable_cost = "";
			
			//cost_required
			$cost_required = "";
			
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
			
			if($retailprice <> ""){
									
			$sql = "INSERT INTO ".$this->tableInv."_".$client." (doc_number, sku_config, sku_simple, sku_description, min, max, cycle_count,";
			$sql .= " reorder_qty, inventor_method, temperature, cost, upc, track_lot, track_serial, track_expdate, primary_unit_of_measure,";
			$sql .= " packaging_unit, packaging_uom_qty, length, width, height, weiight, qualifiers, storage_setup, variable_setup, ";
			$sql .= " nmfc, lot_number_required, serial_number_required, serial_number_must_be_unique, exp_date_req, enable_cost, ";
			$sql .= " cost_required, is_haz_mat, haz_mat_id, haz_mat_shipping_name, haz_mat_hazard_class, haz_mat_packing_group,";
			$sql .= " haz_mat_flash_point, haz_mat_label_code, haz_mat_flat, image_url, storage_count_stript_template_id, storage_rates,";
			$sql .= " outbound_mobile_serialization_behavior, price, total_qty, unit_type, updated_by) VALUES";
			$sql .= " (".$doc_number.", '".strtoupper($sku_config)."', '".strtoupper($sku_simple)."', '".$sku_description."', '".$min."', ".$max.", ".$cycle_count.",";
			$sql .= " ".$reorder_qty.", '".$inventor_method."', '".$temperature."', '".$cost."', '".$upc."', '".$track_lot."', '".$track_serial."', '".$track_expdate."', '".$primary_unit_of_measure."',";
			$sql .= " '".$packaging_unit."', '".$packaging_uom_qty."', '".$length."', '".$width."', '".$height."', '".$weiight."', '".$qualifiers."', '".$storage_setup."', '".$variable_setup."', ";
			$sql .= " '".$nmfc."', '".$lot_number_required."', '".$serial_number_required."', '".$serial_number_must_be_unique."', '".$exp_date_req."', '".$enable_cost."', ";
			$sql .= " '".$cost_required."', '".$is_haz_mat."', '".$haz_mat_id."', '".$haz_mat_shipping_name."', '".$haz_mat_hazard_class."', '".$haz_mat_packing_group."',";
			$sql .= " '".$haz_mat_flash_point."', '".$haz_mat_label_code."', '".$haz_mat_flat."', '".$image_url."', '".$storage_count_stript_template_id."', '".$storage_rates."',";
			$sql .= " '".$outbound_mobile_serialization_behavior."', '".$price."', '".$total_qty."', '".$unit_type."', ".$updated_by.")";
			$this->db->query($sql);
			
			}
		}
		$this->db->trans_complete();
		//end parse the array from excel
		
		
		return TRUE;
	}
	

}