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
		
		$statList= array(
            0 =>array("Pending", "info"),
            1 => array("Configure Attribute-Set","success"),
            2 => array("Form Inbounding","primary"),
			3 => array("Ready to Import 3PL","default"),
            4 => array("Ready to Import Mage","warning"),
			9 => array("Extracting","danger")
        );
		
		$no=0;
		foreach($_row->result() as $_result) {
			$status=$statList[$_result->status];		
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
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
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
        $query = $this->db->get_where($this->table, array('id' => $id));
        $name = $query->row();
        $path = BASEPATH .'../public/inbound/catalog_product/'.$name->filename;
        @unlink($path);

        // delete file, inbound file, inbound item, receving item
        $this->db->trans_start();

        $this->db->where_in($this->pkField, $id)->delete($this->table);
        $this->db->delete('inb_inventory_item_' . $name->client_id, array('doc_number' => $id));
        $inbFiles = $this->db->get_where($this->table, array('reference_id' => $id))->result_array();
        if(!empty($inbFiles)) {
            foreach($inbFiles as $inbFile) {
                $this->db->delete($this->table, array('id' => $inbFile['id']));
                $this->db->delete('inb_inventory_stock_' . $name->client_id, array('doc_number' => $inbFile['id']));
            }
        }

        $this->db->trans_complete();
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

    public function saveFile($post)
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

        $data['filename'] = $post['userfile'];
        $data['created_by']=$user;
        $data['status']=0;
        $data['type']=1;

        if(empty($msg)) {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        } else {
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