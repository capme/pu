<?php
class Brandcode_m extends MY_Model {
    var $filterSession = "DB_USER_FILTER";
    var $db = null;
    var $table = 'client_options';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $tableClient ='client';
    
    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);

        $this->relation = array(
            array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField} and {$this->table}.option_name='multi_brand' and {$this->table}.option_value=1")
        );
        $this->select = array("{$this->tableClient}.{$this->pkField}", "{$this->tableClient}.client_code");
        $this->filters = array("client_code"=>"client_code");
    }
	
    public function getBrandCodeList()
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
                '<a href="'.site_url("brandcode/update/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-edit" ></i> Update</a>'
        );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }
	public function getOptions($id){
        //$arrayWhere = array('client_id' => $id, 'option_name' => 'brand_code', 'option_name' => 'inbound_type');

		$this->db->select('*');		
		$this->db->where('client_id',$id);
		//$this->db->where('option_name','brand_code');
        //$this->db->where('option_name','inbound_type');
        //$this->db->where($arrayWhere);
        $where = '(option_name="brand_code" or option_name = "inbound_type")';
        $this->db->where($where);
        return $this->db->get($this->table)->result_array();
	}

    public function updateBrand($id,$idinboundtype,$data, $client_id){
        //echo $id."<br>";
        //echo $idinboundtype."<br>";die();
        //print_r($data);die();
        //ready formating array to json
        $arrBrandName = array();
        $arrBrandInbound = array();
        foreach($data as $key => $value){
            $arrBrandName[$key] = $value[0];
            $arrBrandInbound[$key] = $value[1];
        }
        //updating brand name
        //$value=json_encode($data);
        //print_r($arrBrandName);
        //print_r($arrBrandInbound);die();
        $arrBrandNameJson = json_encode($arrBrandName);
        $arrBrandInboundJson = json_encode($arrBrandInbound);

        $this->db->where('id', $id);
        //$this->db->update($this->table,array('option_value'=>$value));
        $this->db->update($this->table,array('option_value'=>$arrBrandNameJson));
        //updating brand inbound type
        //$value=json_encode($data);
        if($idinboundtype == "0"){
            $this->db->insert($this->table, array('client_id' => $client_id, 'option_name' => 'inbound_type', 'option_value' => $arrBrandInboundJson));
        }else {
            $this->db->where('id', $idinboundtype);
            //$this->db->update($this->table,array('option_value'=>$value));
            $this->db->update($this->table, array('option_value' => $arrBrandInboundJson));
        }
	}

   function getList($brandcode, $withNull = FALSE, $defaultText = "-- Brand --") {
       $values=array();
        if($withNull) {
            $values["-1"] = $defaultText;
        }

       $array = array();
       if (is_object($brandcode)) {
           $array = get_object_vars($brandcode);
       }
       ksort($array);
       $key=array_keys($array);
       $value=array_values($array);
       for($i=0; $i < count($key); $i++){
           $values[$key[$i]]=strtoupper($value[$i]);
       }
       return $values;
    }

    function getBrandCode($id){
        $mysql = $this->load->database('mysql', TRUE);
        $query = $mysql->get_where($this->table, array('option_name'=>'brand_code', 'client_id'=>$id))->row_array();
        return json_decode($query['option_value']);
    }
}
