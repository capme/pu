<?php
/*
* @property Inbounddocument_m $inbounddocument_m
 */
class Leger_m extends MY_Model {
	var $filterSession = "DB_AWB_FILTER";
	var $db = null;
	var $table = 'pu_leger_ringkasan_data';
    var $tableRingkasanDataIdentifikasi = 'pu_leger_ringkasan_data_identifikasi';
    var $tableRingkasanDataLokasi = 'pu_leger_ringkasan_data_lokasi';
    var $tableRingkasanDataPerwujudan = 'pu_leger_ringkasan_data_perwujudan';
    var $tableRingkasanDataLintasHarian = 'pu_leger_ringkasan_data_lintas_harian_rata2';
    var $tableRingkasanDataLuasRumija = 'pu_leger_ringkasan_data_luas_lahan_rumija';
    var $tableRingkasanDataDataTeknik = 'pu_leger_ringkasan_data_data_teknik';
    var $tableRingkasanDataLegalisasi = 'pu_leger_ringkasan_data_legalisasi';
	var $sorts = array(1 => "id");
	var $pkField = "id";
    var $path = "";

    function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
        $this->relation = array(
			array("type" => "inner", "table" => $this->tableRingkasanDataIdentifikasi, "link" => "{$this->tableRingkasanDataIdentifikasi}.id  = {$this->table}.id_identifikasi")
		);
		
		$this->select = array("{$this->tableRingkasanDataIdentifikasi}.*", "{$this->table}.id_identifikasi");
		$this->filters = array("distribusi_ke"=>"distribusi_ke","no_lembar"=>"no_lembar","nama_prov"=>"nama_prov");
        $this->load->helper('path');
        $this->load->library('va_excel');
	}
	
	public function getRingkasanDataList()
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
            $btnAction='
            	Export to PDF
            	<br />
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Data Teknik</a>&nbsp;
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Identifikasi</a>&nbsp;
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Legalisasi</a>&nbsp;
            	<br />
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Lintas Harian rata-rata</a>
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Lokasi</a>&nbsp;
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Luas Lahan Rumija</a>&nbsp;
            	<br />
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Perwujudan</a>&nbsp;
            	<hr />
            	Delete
            	<br />
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Data Teknik</a>&nbsp;
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Identifikasi</a>&nbsp;
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Legalisasi</a>&nbsp;
            	<br />
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Lintas Harian rata-rata</a>
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Lokasi</a>&nbsp;
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Luas Lahan Rumija</a>&nbsp;
            	<br />
            	<a href="'.site_url("inbounds/download/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Perwujudan</a>&nbsp;
            	';
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$_result->id,
					$_result->distribusi_ke,
					$_result->no_lembar,
					$_result->nama_prov,
   					$btnAction
			);
		}
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
		
	}
	
	public function getRingkasanDataById($id)
	{
		$this->db->select('*, inb_document.id');
		$this->db->from($this->table);
		$this->db->join('client','client.id=inb_document.client_id');
		$this->db->where('inb_document.id', $id);
        return $this->db->get()->row_array();
	}

    public function deleteRingkasanData($id){
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

}
?>