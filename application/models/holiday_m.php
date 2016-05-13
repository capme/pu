<?php
class Holiday_m extends MY_Model {
    var $filterSession = "DB_CLIENT_FILTER";
    var $db = null;
    var $table = 'holiday';
    var $sorts = array(1 => "id");
    var $pkField = "id";

    function __construct(){
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);$this->db = $this->load->database('mysql', TRUE);
    }

    public function getHolidayList() {
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

        $BulanIndo = array("Januari", "Februari", "Maret",
            "April", "Mei", "Juni",
            "Juli", "Agustus", "September",
            "Oktober", "November", "Desember");
        $no=0;
        foreach($_row->result() as $_result) {
            $tahun = substr($_result->date, 0, 4);
            $bulan = substr($_result->date, 5, 2);
            $tgl   = substr($_result->date, 8, 2);
            $result = $tgl . " " . $BulanIndo[(int)$bulan-1] . " ". $tahun;

            $records["aaData"][] = array(
                '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                $no=$no+1,
                $_result->name,
                $result,
                '<a href="'.site_url("holiday/delete/".$_result->id).'" onClick="return deletechecked()" class="btn btn-xs default"><i class="fa fa-trash-o"></i> Delete</a>',
            );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;

        return $records;
    }

    public function newHoliday($post) {
        $msg = array();

        if(!empty($post['name'])) {
            $data['name'] = $post['name'];
        }
        else {
            $msg['name'] = "Name is Required";
        }

        if(!empty($post['period1'])) {
            $data['from'] = $post['period1'];
        }
        else {
            $msg['period1'] = "Date is Required";
        }

        if(!empty($post['period2'])) {
            $data['to'] = $post['period2'];
        }
        else {
            $msg['period2'] = "Peiod2 is Required";
        }

        if(empty($msg)) {
            $a=$post['period1'];
            $b=$post['period2'];
            $start    = new DateTime($a);
            $end      = new DateTime($b);
            $interval = new DateInterval('P1D'); // 1 day interval
            $period   = new DatePeriod($start, $interval, $end);

            foreach ($period as $day) {
                $date=$day->format('Y:m:d 00:00:00');
                $this->db->insert($this->table, array("name"=>$post['name'], "date"=>$date));
            }
            $this->db->insert($this->table, array("name"=>$post['name'], "date"=>$b));
            $clientId = $this->db->insert_id();
            return $clientId;
        }
        else {
            return $msg;
        }
    }

    public function deleteHoliday($id){
        $this->db->delete($this->table, array('id'=>$id));
    }
}