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
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$_result->client_code,
					$_result->doc_number,                   					
					$_result->created_at,
					$_result->updated_at,
					'<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Download</a>
                    <a href="'.site_url("inbounds/delete/".$_result->id).'" onClick="return deletechecked()" class="btn btn-xs default"  ><i class="fa fa-trash-o"></i>Delete<a>'
					
			);
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
        $name = $this->db->select('filename')->get($this->table)->row_array();
        $path= BASEPATH .'../public/inbound/catalog_product/'.$name['filename'];        
        $result = unlink($path); 
        if($result == true){
           $this->db->where_in($this->pkField, $id)->delete($this->table); 
        }else{
          return false;
          redirect('inbounds');
        }        
    }

	public function UploadFile($post, $filename)
	{
	    $msg = array();	
	    if ($filename == null){
				return null;								 
			}
        else {   
                $user=$this->session->userdata('pkUserId');
        		if(!empty($post['client'])) {
        			$data['client_id'] = $post['client'];
        		} else {}
                
                if(!empty($post['docnumber'])) {
        			$data['doc_number'] = $post['docnumber'];
        		} else {}
                
                if(!empty($post['note'])) {
        			$data['note'] = $post['note'];
        		} else {}
                
        		if(!empty($post['userfile'])) {
        			$data['filename'] = $post['userfile'];
        		} else {}
          
                $data['created_by']=$user;
                $data['status']=0;
                $data['type']=1;
                
        		if(empty($msg)) {
        			$this->db->insert($this->table, $data);
        			return $this->db->insert_id();
        		}      
 	        }       
	}
}
?>