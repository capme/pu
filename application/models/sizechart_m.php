<?php
/*
* @property Ctrconversion_m $ctrconversion_m
 */
class Sizechart_m extends MY_Model {

    var $filterSession = "DB_AWB_FILTER";
    var $db = null;
    var $table = 'brand_size_import';
    var $tableMap='brand_size_map';
    var $tableClient ='client';
    var $tableClientOption='client_options';
    var $sorts = array(2 => "brand_code");
    var $pkField = "id";
    var $status=array("cancel"=>2,"approve"=>1);
    var $path = "";
    var $filters = array("brand_code"=>"brand_code");

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
    }

    public function getSizeChartList(){
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
                '',
                $no=$no+1,
                $_result->brand_code,
                $this->mapping($_result->brand_code),
                $_result->notes,
                $_result->created_at,
                '<a href="'.site_url("sizechart/export/".$_result->brand_code).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-download" ></i> Export</a> |
                <a href="'.site_url("sizechart/view/".$_result->brand_code).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a> |
                <a href="'.site_url("sizechart/delete/".$_result->brand_code).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Delete</a>'
            );
        }
        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    public function mapping($brand){
        $clientname=$this->brandcode_m->getBrandCode($id=6);
        $array = array();
        if (is_object($clientname)) {
            $array = get_object_vars($clientname);
        }
        $ar= array_key_exists($brand, $array);
       return strtoupper($array[$brand]);
    }

    public function saveFile($post) {
        $msg=array();
            $data['brand_code']=$post['brand_code'];

            if(!empty($post['attribute_set'])) {
                $data['attribute_set'] = $post['attribute_set'];
            } else {
                $msg="attribute set is required";
            }

            if(!empty($post['brand_size'])) {
                $data['brand_size'] = $post['brand_size'];
            } else {
                $msg ="brand_size is required";
            }

            if(!empty($post['brand_size_system'])) {
                $data['brand_size_system'] = $post['brand_size_system'];
            } else {
                $msg="brand size system is required";
            }

            if(!empty($post['paraplou_size'])) {
                $data['paraplou_size'] = $post['paraplou_size'];
            } else {
                $msg="paraplou size is required";
            }

            if(!empty($post['position'])) {
                $data['position'] = $post['position'];
            } else {
                $msg="position is required";
            }

        if(empty($msg)) {
            $this->db->insert($this->tableMap, $data);
            return $post['position'];
        }
        else {
            return $msg;
        }
    }

    public function saveImport($post, $brand_code){
        $query = $this->db->get_where($this->table, array('brand_code' => $brand_code))->row_array();
        $path = BASEPATH .'../public/merchandising/size_chart/'.$query['filename'];
        unlink($path);

        $this->db->trans_start();
        $this->db->where('brand_code', $brand_code);
        $this->db->delete($this->table);

        $this->db->insert($this->table, array('brand_code'=>$brand_code,'filename'=>$post['userfile'], 'notes'=>$post['note']));
        $this->db->trans_complete();
    }

    public function deleteTemp($brand_code){
        $this->db->where('brand_code', $brand_code);
        $this->db->delete($this->tableMap);
    }

    public function export($brand_code ){
        $this->db->select('attribute_set, brand_size, brand_size_system, paraplou_size, position');
        $this->db->from($this->tableMap);
        $this->db->where('brand_code', $brand_code);
        return $this->db->get();
    }

    public function delete($brand_code){
        $query = $this->db->get_where($this->table, array('brand_code' => $brand_code))->row_array();
        $path = BASEPATH .'../public/merchandising/size_chart/'.$query['filename'];
        unlink($path);

        $this->db->trans_start();
        $this->db->where('brand_code', $brand_code);
        $this->db->delete($this->tableMap);

        $this->db->where('brand_code', $brand_code);
        $this->db->delete($this->table);
        $this->db->trans_complete();
    }

    public function cekAvailable($brand_code){
        return $this->db->get_where($this->table, array('brand_code'=>$brand_code))->row();
    }

    public function cekMap($brand_code){
        return $this->db->get_where($this->tableMap, array('brand_code'=>$brand_code))->row();
    }

    public function getSizeChartById($brand_code){
        return $this->db->get_where($this->tableMap, array('brand_code'=>$brand_code));
    }

}
