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
            	Add
            	<br />
            	';
			if(!$this->checkExistDataItem("data teknik", $_result->id)){
				$btnAction.='
            	<a href="'.site_url("ringkasandata/addDataTeknik").'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Data Teknik</a>&nbsp;
            	';
			}else{
				$btnAction.='
            	<a href="'.site_url("ringkasandata/addDataTeknik").'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Data Teknik</a>&nbsp;
            	';
			}
			if(!$this->checkExistDataItem("identifikasi", $_result->id)) {
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/addIdentifikasi") . '"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Identifikasi</a>&nbsp;
            	';
			}else{
				$btnAction .= '
            	<a href="' . site_url("ringkasandata/addIdentifikasi") . '"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-trash-o" ></i> Identifikasi</a>&nbsp;
            	';
			}
			$btnAction.='
            	<a href="'.site_url("ringkasandata/addLegalisasi").'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Legalisasi</a>&nbsp;
            	<br />
            	<a href="'.site_url("ringkasandata/addLintasHarianRata2").'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Lintas Harian rata-rata</a>
            	<a href="'.site_url("ringkasandata/addLokasi").'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Lokasi</a>&nbsp;
            	<a href="'.site_url("ringkasandata/addLuasLahanRumija").'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Luas Lahan Rumija</a>&nbsp;
            	<br />
            	<a href="'.site_url("ringkasandata/addPerwujudan").'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-download-alt" ></i> Perwujudan</a>&nbsp;
            	';
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
		if($form == "data teknik"){
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_ringkasan_data_data_teknik as b";
			$sql .= " on a.id = b.id_master_data where a.id = ".$idmasterdata;
		}elseif($form == "identifikasi"){
			$sql = "select a.id_provinsi, a.lembar_distribusi, a.no_leger, a.tipe_leger, b.id, b.id_master_data";
			$sql .= " from pu_master_data as a inner join pu_leger_ringkasan_data_identifikasi as b";
			$sql .= " on a.id = b.id_master_data where a.id = ".$idmasterdata;
		}elseif($form == "legalisasi"){

		}elseif($form == "lintas harian rata2"){

		}elseif($form == "lokasi"){

		}elseif($form == "luas lahan rumija"){

		}elseif($form == "perwujudan"){

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