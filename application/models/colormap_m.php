<?php
/*
* @property colormap_m $colormap_m
 */
class Colormap_m extends MY_Model {
    var $filterSession = "DB_AWB_FILTER";
    var $db = null;
    var $table = 'color_map';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $path = "";
    var $filters = array("original_color" => "original_color","color_map"=>"color_map","color_code"=>"color_code");

    var $cache = array();

    function __construct(){
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
        $this->load->helper('path');
        $this->load->library('va_excel');
        $this->listWhere['equal'] = array();
        $this->listWhere['like'] = array("color_map","original_color","color_code");
    }

    public function getColorMapList()
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
                $no=$no+1,
                $_result->original_color,
                $_result->color_map,
                $_result->color_code,
                '<td> </td>'
            );
        }
        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    public function truncate(){
       $query = $this->db->query('TRUNCATE TABLE color_map');
     return $query;
    }

    public function saveFile($original_color, $mapping_color, $color_code){
        $msg = array();
            $data['original_color'] = trim(strtoupper($original_color));
            $data['color_map'] = trim(strtoupper($mapping_color));
            $data['color_code'] = trim(strtoupper($color_code));
        if(empty($msg)) {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        } else {
            return $msg;
        }
    }

    public function getDataColor(){
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    public function getByOrigColor($origColor) {
        if(!isset($this->cache[$origColor])) {
            $row = $this->db->get_where($this->table, array('original_color' => $origColor))->row_array();
            if(empty($row)) {
                $row = false;
            }

            $this->cache[$origColor] = $row;
        }

        return $this->cache[$origColor];
    }
}
?>