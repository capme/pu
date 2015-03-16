<?php
class Inbound_m extends MY_Model {
	var $filterSession = "DB_AWB_FILTER";
	var $db = null;
	var $table = 'inb_document';
	var $tableClient ='client';
	var $sorts = array(1 => "id");
	var $pkField = "id";
	var $status=array("cancel"=>2,"approve"=>1);
	
	function __construct()
	{
		parent::__construct();
        
		$this->db = $this->load->database('mysql', TRUE);
        $this->relation = array(
			array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField} ")
		);
		
		$this->select = array("{$this->table}.*", "{$this->tableClient}.client_code");
		$this->filters = array("doc_number"=>"doc_number","client_id"=>"client_id");
	}
	
	public function getInboundList()
	{
		$this->db = $this->load->database('mysql', TRUE); 
		$iTotalRecords = $this->_doGetTotalRow();
		$iDisplayLength = intval($this->input->post('iDisplayLength'));
		$iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
		$iDisplayStart = intval($this->input->post('iDisplayStart'));
		$sEcho = intval($this->input->post('sEcho'));
	
		$records = array();
		$records["aaData"] = array();
        
        $end = $iDisplayStart + $iDisplayLength;
		$end = $end > $iTotalRecords ? $iTotalRecords : $end;
		
		$_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
		$no=0;
		
		foreach($_row->result() as $_result) {
			if($_result->type == 1){
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$_result->client_code,
					$_result->doc_number,
                    $_result->note,
					$_result->created_at,
					'<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Download</a>
                    <a href="'.site_url("inbounds/delete/".$_result->id).'" onClick="return deletechecked()" class="btn btn-xs default"  ><i class="fa fa-trash-o"></i>Delete<a>'
					
			);
			}
		}
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
		
	}
	
	public function getInboundById($id)
	{
		$this->db->select('filename');
		$this->db->from($this->table);
		$this->db->where('id', $id);		
		return $this->db->get()->row_array(); 
         
	}
    
    public function deleteInbound($id){
        $query=$this->db->get_where($this->table, array('id' => $id));
        $name =$query->row(); 
        $path= BASEPATH .'../public/inbound/catalog_product/'.$name->filename;        
        $result = unlink($path); 
        if($result == true){
           $this->db->where_in($this->pkField, $id)->delete($this->table); 
        }
        else{
          return false;
          redirect('inbounds');
        }        
    }

	public function uploadFile($post, $filename)
	{
	   $msg = array();       
       $user=$this->session->userdata('pkUserId');
       
	   if(!empty($post['client']) ) {
        $data['client_id'] = $post['client'];
        } else {
        $msg['client'] = "Invalid name";
        }
                
        if(!empty($post['docnumber'])) {
        $data['doc_number'] = $post['docnumber'];
        } else {
        $msg['docnumber'] = "Invalid docnumber";
        }
                
        if(!empty($post['note'])) {
        $data['note'] = $post['note'];
        } else {}
                
        if(!empty($post['userfile'])&& $filename !=null) {
        $data['filename'] = $post['userfile'];
        } else {
        $msg['userfile'][0]="Invalid filename";
        return $msg;
        }
        
        $data['created_by']=$user;
        $data['status']=0;
        $data['type']=1;
        
       $objPHPExcel = PHPExcel_IOFactory::load($post['full_path']);            
       $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
                            
       foreach ($cell_collection as $cell) {                
               $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
               $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
               $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();               
               $arr_data[$row][$column] = $data_value;                
            }
            
        if (strtoupper($arr_data[15]['A'])!='NO' 
            && strtoupper($arr_data[15]['B']) != 'PO TYPE' 
            && strtoupper($arr_data[15]['C']) != 'SEASON' 
            && strtoupper($arr_data[15]['D']) != 'YEAR' 
            && strtoupper($arr_data[15]['E']) != 'GENDER (M/F/U)'
            && strtoupper($arr_data[15]['F']) != 'CATEGORY (TOP / BOTTOM / FOOTWEAR / ACCESSORIES' 
            && strtoupper($arr_data[15]['G']) != 'SUB CATEGORY'
            && strtoupper($arr_data[15]['H']) != 'CONSIGNMENT or DIRECT PURCHASE'
            && strtoupper($arr_data[15]['I']) != 'SUPPLIER STYLE CODE / SKU'
            && strtoupper($arr_data[15]['J']) != 'SUPPLIER'
            && strtoupper($arr_data[15]['N']) != 'FABRIC/MATERIAL COMPOSITION'
            && strtoupper($arr_data[15]['O']) != 'DETAIL INFO'
            && strtoupper($arr_data[15]['R']) != 'Product Name Revisions'
            && strtoupper($arr_data[15]['S']) != 'SHORT DESCRIPTION'
            && strtoupper($arr_data[15]['T']) != 'Meta Description'
            && strtoupper($arr_data[15]['U']) != 'Meta Keywords'
            && strtoupper($arr_data[15]['V']) != 'PICTURES'
            && strtoupper($arr_data[15]['X']) != 'VALUE'
            && strtoupper($arr_data[15]['AA']) != 'QTY / SIZES'
            && strtoupper($arr_data[15]['AC']) != 'TOTAL VALUE (Rp)'
            && strtoupper($arr_data[15]['AE']) != 'EXP. DELIV. DATE'
            && strtoupper($arr_data[15]['AF']) != 'EXP. DELIV. SLOT')            
            {
                unlink($post['full_path']);
                $msg['userfile'][1]="Uploaded file using invalid format";               
            }
                    
        if(empty($msg)) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
        }
        
        else {
        return $msg;
        }
  }
}
?>