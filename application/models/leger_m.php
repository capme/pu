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

	public function saveRingkasanData($data){
		$this->db->trans_start();

		$method = $data['method'];
		if($method == "identifikasi"){
			//ringkasan data - identifikasi
			$lembar_distribusi_ke = $data['lembar_distribusi_ke'];
			$nomer_lembar = $data['nomer_lembar'];
			$kode_provinsi = $data['kode_provinsi'];
			$nama_provinsi = $data['nama_provinsi'];
			$tahun = $data['tahun'];
			$tipe = $data['tipe'];
			$nomor_panjang_ruas_jalan = $data['nomor_panjang_ruas_jalan'];
			$nama_pengenal_jalan = $data['nama_pengenal_jalan'];
			$titik_awal_ruas_jalan = $data['titik_awal_ruas_jalan'];
			$deskripsi_titik_awal_ruas_jalan = $data['deskripsi_titik_awal_ruas_jalan'];
			$titik_akhir_ruas_jalan = $data['titik_akhir_ruas_jalan'];
			$deskripsi_titik_akhir_ruas_jalan = $data['deskripsi_titik_akhir_ruas_jalan'];
			$titik_ikat_awal_patok = $data['titik_ikat_awal_patok'];
			$deskripsi_titik_ikat_patok = $data['deskripsi_titik_ikat_patok'];
			$sistem_jaringan_jalan = $data['sistem_jaringan_jalan'];
			$peran_jalan = $data['peran_jalan'];
			$status_jalan = $data['status_jalan'];
			$kelas_jalan = $data['kelas_jalan'];
			$pembina_jalan = $data['pembina_jalan'];
			$tanggal_selesai_diwujudkan = $data['tanggal_selesai_diwujudkan'];
			$tanggal_dibuka_untuk_lalin = $data['tanggal_dibuka_untuk_lalin'];
			$tanggal_ditutup_untuk_lalin = $data['tanggal_ditutup_untuk_lalin'];
			$id_master_data = $data['id_master_data'];

			$sql = "insert into ".$this->tableRingkasanDataIdentifikasi." (id_master_data) values (".$id_master_data.")";
			$this->db->query($sql);

		}elseif($method == "lokasi"){
			$id_master_data = $data['id_master_data'];

			$config['upload_path']   = '../public/leger/lokasi/';
			$config['allowed_types'] = 'gif|jpg|png';
			$this->load->library('upload', $config);

			$images = array();

			foreach ($_FILES['lokasi']['name'] as $key => $image) {
				$_FILES['lokasi[]']['name']= $_FILES['lokasi']['name'][$key];
				$_FILES['lokasi[]']['type']= $_FILES['lokasi']['type'][$key];
				$_FILES['lokasi[]']['tmp_name']= $_FILES['lokasi']['tmp_name'][$key];
				$_FILES['lokasi[]']['error']= $_FILES['lokasi']['error'][$key];
				$_FILES['lokasi[]']['size']= $_FILES['lokasi']['size'][$key];

				$fileName = $_FILES['lokasi[]']['name'];

				$images[] = $fileName;

				$config['file_name'] = $id_master_data."_".$fileName;

				$this->upload->initialize($config);

				if ($this->upload->do_upload('lokasi[]')) {
					$this->upload->data();
					if($key == 0){
						$sql = "insert into ".$this->tableRingkasanDataLokasi." (id_master_data, peta_prov) values (".$id_master_data.",'".$id_master_data."_.".$fileName."')";
					}else{
						$sql = "insert into ".$this->tableRingkasanDataLokasi." (id_master_data, peta_lokasi) values (".$id_master_data.",'".$id_master_data."_.".$fileName."')";
					}
					$this->db->query($sql);
				} else {

				}
			}


		}

		$this->db->trans_complete();

	}

	public function deleteRingkasanData($table, $id_master_data){
		$this->db->trans_start();
		$this->db->delete($table, array('id_master_data' => $id_master_data));
		$this->db->trans_complete();
	}
}
?>