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
			 if($_result->status < 3){
		          $btnAction='<a href="'.site_url("inbounds/edit/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-edit" ></i> Edit</a>
                  <a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Download</a>
                    <a href="'.site_url("inbounds/delete/".$_result->id).'" onClick="return deletechecked()" class="btn btn-xs default"  ><i class="fa fa-trash-o"></i>Delete<a>';
		      }
              else {
		          $btnAction='<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Download</a>
                    <a href="'.site_url("inbounds/delete/".$_result->id).'" onClick="return deletechecked()" class="btn btn-xs default"  ><i class="fa fa-trash-o"></i>Delete<a>';  
		      }		      
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$_result->client_code,
					$_result->doc_number,
                    $_result->note,
					$_result->created_at,
					$btnAction
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
		$this->db->select('*, inb_document.id');
		$this->db->from($this->table);
		$this->db->join('client','client.id=inb_document.client_id');
		$this->db->where('inb_document.id', $id);
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
    
   public function countDocClient($client)
	{
       $this->db->select('*');
       $this->db->from($this->table);
       $this->db->where('client_id', $client);
       $this->db->where('type',1);
       $this->db->where('created_at >',date('Y-m').'-01 00:00:00');
       $this->db->where('created_at <',date('Y-m').'-31 00:00:00');
       $query = $this->db->get();
       return $rowcount = $query->num_rows();  
	}

	public function uploadFile($post, $filename)
	{      
       $msg = array();       
       $user=$this->session->userdata('pkUserId');
       
	   if(!empty($post['client']) ) {
            $data['client_id'] = $post['client'];
            $count= $this->countDocClient($post['client']);
            $docnumber= $count + 1;
            $client_option=$this->clientoptions_m->get($post['client'], 'brand_initial');
            
            if ( !empty($client_option) && isset($client_option['option_name']) ){
                $data['doc_number']="PC/".date('Y')."/".date('m')."/".$client_option['option_value']."-".$docnumber;
                }
            else{
                 $client=$this->client_m->getClientCodeList();
                 foreach($client as $inisial){          
                 $data['doc_number']="PC/".date('Y')."/".date('m')."/".$client[$post['client']]."-".$docnumber;
                 }
                }
            
        }else {
            $msg['client'] = "Invalid name";
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
            && strtoupper($arr_data[15]['H']) != 'CONSIGNMENT OR DIRECT PURCHASE'
            && strtoupper($arr_data[15]['I']) != 'SUPPLIER STYLE CODE / SKU'
            && strtoupper($arr_data[15]['J']) != 'SUPPLIER'
            && strtoupper($arr_data[15]['N']) != 'FABRIC/MATERIAL COMPOSITION'
            && strtoupper($arr_data[15]['O']) != 'DETAIL INFO'
            && strtoupper($arr_data[15]['R']) != 'PRODUCT NAME REVISIONS'
            && strtoupper($arr_data[15]['S']) != 'SHORT DESCRIPTION'
            && strtoupper($arr_data[15]['T']) != 'META DESCRIPTION'
            && strtoupper($arr_data[15]['U']) != 'META KEYWORDS'
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
  
    public function editProductCatalogue($post){
       $msg = array();       
       $user=$this->session->userdata('pkUserId');
    
       if(!empty($post['userfile'])&& $post['full_path'] !=null) {
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
            && strtoupper($arr_data[15]['H']) != 'CONSIGNMENT OR DIRECT PURCHASE'
            && strtoupper($arr_data[15]['I']) != 'SUPPLIER STYLE CODE / SKU'
            && strtoupper($arr_data[15]['J']) != 'SUPPLIER'
            && strtoupper($arr_data[15]['N']) != 'FABRIC/MATERIAL COMPOSITION'
            && strtoupper($arr_data[15]['O']) != 'DETAIL INFO'
            && strtoupper($arr_data[15]['R']) != 'PRODUCT NAME REVISIONS'
            && strtoupper($arr_data[15]['S']) != 'SHORT DESCRIPTION'
            && strtoupper($arr_data[15]['T']) != 'META DESCRIPTION'
            && strtoupper($arr_data[15]['U']) != 'META KEYWORDS'
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
            $path= BASEPATH .'../public/inbound/catalog_product/'.$post['filename'];        
            $result = unlink($path);
            
            $this->db->where($this->pkField, $post['id']);			
    		$this->db->update($this->table, $data);
            
            $this->db->where('doc_number', $post['id']);
            $this->db->delete('inb_inventory_item_'.$post['client_id']);            
                      
            return $post['id'];
        }
        
        else {
        return $msg;
        }
  }
}
?>