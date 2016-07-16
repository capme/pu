<?php
/*
* @property Area_m $area_m
 */
class Masterdata_m extends MY_Model {
	var $filterSession = "DB_AWB_FILTER";
	var $db = null;
	var $table = 'pu_master_data';
	var $tableArea = 'pu_area';
	var $sorts = array(1 => "pu_master_data.id");
	var $pkField = "id";
    var $path = "";

    function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);

		$this->relation = array(
			array("type" => "inner", "table" => $this->tableArea, "link" => "{$this->table}.id_provinsi  = {$this->tableArea}.{$this->pkField}")
		);
		$this->select = array("{$this->table}.id","{$this->table}.lembar_distribusi","{$this->table}.no_leger","{$this->table}.tipe_leger","{$this->tableArea}.nama");
		$this->filters = array("lembar_distribusi"=>"lembar_distribusi","nama"=>"nama","no_leger"=>"no_leger");
        $this->load->helper('path');
        $this->load->library('va_excel');
	}
	
	public function getMasterdataList()
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
				RINGKASAN DATA <br>
            	';
			if(!$this->checkExistDataItem("data teknik", $_result->id)){
				$btnAction.='
            	<a href="'.site_url("ringkasandata/addDataTeknik/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Data Teknik</a>&nbsp;
            	';
			}else{
				$btnAction.='
            	<a href="'.site_url("ringkasandata/delete?mod=datateknik&id=".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Data Teknik [delete]</a>&nbsp;
            	';
			}
			if(!$this->checkExistDataItem("identifikasi", $_result->id)) {
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/addIdentifikasi/".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Identifikasi</a>&nbsp;
            	';
			}else{
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/delete?mod=identifikasi&id=".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Identifikasi [delete]</a>&nbsp;
            	';
			}
			if(!$this->checkExistDataItem("legalisasi", $_result->id)) {
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/addLegalisasi/".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Legalisasi</a>&nbsp;
            	';
			}else{
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/delete?mod=legalisasi&id=".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Legalisasi [delete]</a>&nbsp;
            	';
			}
			$btnAction.='
            	<br />';
			if(!$this->checkExistDataItem("lintas harian rata2", $_result->id)) {
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/addLintasHarianRata2/".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Lintas Harian rata-rata</a>
            	';
			}else{
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/delete?mod=lintasharianrata2&id=".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Lintas Harian rata-rata [delete]</a>
            	';
			}
			if(!$this->checkExistDataItem("lokasi", $_result->id)) {
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/addLokasi/".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Lokasi</a>&nbsp;
            	';
			}else{
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/delete?mod=lokasi&id=".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Lokasi [delete]</a>&nbsp;
            	';
			}
			if(!$this->checkExistDataItem("luas lahan rumija", $_result->id)) {
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/addLuasLahanRumija/".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Luas Lahan Rumija</a>&nbsp;
            	';
			}else{
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/delete?mod=luaslahanrumija&id=".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Luas Lahan Rumija [delete]</a>&nbsp;
            	';
			}
				$btnAction.='
            	<br />
            	';
			if(!$this->checkExistDataItem("perwujudan", $_result->id)) {
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/addPerwujudan/".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Perwujudan</a>&nbsp;
            	';
			}else{
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/delete?mod=perwujudan&id=".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Perwujudan [delete]</a>&nbsp;
            	';
			}
			$btnAction.='<br><br>
				KARTU JALAN <br>
            	';
			if(!$this->checkExistDataItem("jalan identifikasi", $_result->id)) {
				$btnAction .= '
            	<a href="' . site_url("kartujalan/addIdentifikasi/".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Identifikasi</a>&nbsp;
            	';
			}else{
				$btnAction .= '
            	<a href="' . site_url("kartujalan/delete?mod=jalanidentifikasi&id=".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Identifikasi [delete]</a>&nbsp;
            	';
			}
			$btnAction.='<br><br>
				KARTU JEMBATAN <br>
            	';
			if(!$this->checkExistDataItem("jembatan identifikasi", $_result->id)) {
				$btnAction .= '
            	<a href="' . site_url("kartujembatan/addIdentifikasi/".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Identifikasi</a>&nbsp;
            	';
			}else{
				$btnAction .= '
            	<a href="' . site_url("kartujembatan/delete?mod=jembatanidentifikasi&id=".$_result->id) . '"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Identifikasi [delete]</a>&nbsp;
            	';
			}

			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$_result->id,
					$_result->lembar_distribusi,
					$_result->nama,
					$_result->no_leger,
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

	public function saveMasterData($data){
		$this->db->trans_start();
		$sql = "INSERT INTO ".$this->table." (id_provinsi, lembar_distribusi, no_leger, tipe_leger) VALUES";
			$sql .= " (".$data['id_provinsi'].",".$data['lembar_distribusi'].",'".$data['no_leger']."','".$data['tipe_leger']."')";
			$this->db->query($sql);
		$this->db->trans_complete();
	}

	public function checkExistDataItem($form, $idmasterdata){
		//ringkasan data
		if($form == "data teknik"){
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_ringkasan_data_data_teknik as b";
			$sql .= " on a.id = b.id_master_data where a.id = ".$idmasterdata;
		}elseif($form == "identifikasi"){
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_ringkasan_data_identifikasi as b";
			$sql .= " on a.id = b.id_master_data where a.id = ".$idmasterdata;
		}elseif($form == "legalisasi"){
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_ringkasan_data_legalisasi as b";
			$sql .= " on a.id = b.id_master_data where a.id = ".$idmasterdata;
		}elseif($form == "lintas harian rata2"){
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_ringkasan_data_lintas_harian_rata2 as b";
			$sql .= " on a.id = b.id_master_data where a.id = ".$idmasterdata;
		}elseif($form == "lokasi"){
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_ringkasan_data_lokasi as b";
			$sql .= " on a.id = b.id_master_data where a.id = ".$idmasterdata;
		}elseif($form == "luas lahan rumija"){
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_ringkasan_data_luas_lahan_rumija as b";
			$sql .= " on a.id = b.id_master_data where a.id = ".$idmasterdata;
		}elseif($form == "perwujudan"){
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_ringkasan_data_perwujudan as b";
			$sql .= " on a.id = b.id_master_data where a.id = ".$idmasterdata;
		}

		//kartu jalan
		if($form == "jalan identifikasi") {
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_kartu_jalan_identifikasi as b";
			$sql .= " on a.id = b.id_master_data where a.id = " . $idmasterdata;
		}

		//kartu jembatan
		if($form == "jembatan identifikasi") {
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_kartu_jembatan_identifikasi as b";
			$sql .= " on a.id = b.id_master_data where a.id = " . $idmasterdata;
		}

		$query = $query = $this->db->query($sql);
		if ($query->num_rows() > 0){
			return true;
		}else{
			return false;
		}

	}

}
?>