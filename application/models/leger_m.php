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

			if($tipe == "asal"){
				$sql = "insert into ".$this->tableRingkasanDataIdentifikasi;
				$sql .= "(id_master_data, asal_tahun, no_panjang_ruas_jalan_asal, nama_pengenal_jalan_asal, titik_awal_ruas_jalan_awal, ";
				$sql .= "desc_titik_awal_ruas_jalan_awal, titik_akhir_ruas_jalan_awal, desc_titik_akhir_ruas_jalan_awal, titik_ikat_awal_patok_km_asal, ";
				$sql .= "desc_titik_ikat_awal_patok_km_asal, sistem_jaringan_jalan_asal, peran_jalan_asal, status_jalan_asal, kelas_jalan_asal, ";
				$sql .= "pembina_jalan_asal, tanggal_selesai_diwujudkan_asal, tanggal_dibuka_lalin_asal, tanggal_ditutup_lalin_asal) values ";
				$sql .= "(".$id_master_data.", '".$tahun."', '".$nomor_panjang_ruas_jalan."', '".$nama_pengenal_jalan."', '".$titik_awal_ruas_jalan;
				$sql .= "', '".$deskripsi_titik_awal_ruas_jalan."', '".$titik_akhir_ruas_jalan."', '".$deskripsi_titik_akhir_ruas_jalan;
				$sql .= "', '".$titik_ikat_awal_patok."', '".$deskripsi_titik_ikat_patok."', '".$sistem_jaringan_jalan."', '".$peran_jalan;
				$sql .= "', '".$status_jalan."', '".$kelas_jalan."', '".$pembina_jalan."', '".$tanggal_selesai_diwujudkan;
				$sql .= "', '".$tanggal_dibuka_untuk_lalin."', '".$tanggal_ditutup_untuk_lalin."')";
			}elseif($tipe == "pemutakhiran1"){
				$sql = "insert into ".$this->tableRingkasanDataIdentifikasi;
				$sql .= "(id_master_data, pemutakhiran_1, no_panjang_ruas_jalan_pemutakhiran_1, nama_pengenal_jalan_pemutakhiran_1, titik_awal_ruas_jalan_pemutakhiran_1, ";
				$sql .= "desc_titik_awal_ruas_jalan_pemutakhiran_1, titik_akhir_ruas_jalan_pemutakhiran_1, desc_titik_akhir_ruas_jalan_pemutakhiran_1, titik_ikat_awal_patok_km_pemutakhiran_1, ";
				$sql .= "desc_titik_ikat_awal_patok_km_pemutakhiran_1, sistem_jaringan_jalan_pemutakhiran_1, peran_jalan_pemutakhiran_1, status_jalan_pemutakhiran_1, kelas_jalan_pemutakhiran_1, ";
				$sql .= "pembina_jalan_pemutakhiran_1, tanggal_selesai_diwujudkan_pemutakhiran_1, tanggal_dibuka_lalin_pemutakhiran_1, tanggal_ditutup_lalin_pemutakhiran_1) values ";
				$sql .= "(".$id_master_data.", '".$tahun."', '".$nomor_panjang_ruas_jalan."', '".$nama_pengenal_jalan."', '".$titik_awal_ruas_jalan;
				$sql .= "', '".$deskripsi_titik_awal_ruas_jalan."', '".$titik_akhir_ruas_jalan."', '".$deskripsi_titik_akhir_ruas_jalan;
				$sql .= "', '".$titik_ikat_awal_patok."', '".$deskripsi_titik_ikat_patok."', '".$sistem_jaringan_jalan."', '".$peran_jalan;
				$sql .= "', '".$status_jalan."', '".$kelas_jalan."', '".$pembina_jalan."', '".$tanggal_selesai_diwujudkan;
				$sql .= "', '".$tanggal_dibuka_untuk_lalin."', '".$tanggal_ditutup_untuk_lalin."')";
			}elseif($tipe == "pemutakhiran2"){
				$sql = "insert into ".$this->tableRingkasanDataIdentifikasi;
				$sql .= "(id_master_data, pemutakhiran_2, no_panjang_ruas_jalan_pemutakhiran_2, nama_pengenal_jalan_pemutakhiran_2, titik_awal_ruas_jalan_pemutakhiran_2, ";
				$sql .= "desc_titik_awal_ruas_jalan_pemutakhiran_2, titik_akhir_ruas_jalan_pemutakhiran_2, desc_titik_akhir_ruas_jalan_pemutakhiran_2, titik_ikat_awal_patok_km_pemutakhiran_2, ";
				$sql .= "desc_titik_ikat_awal_patok_km_pemutakhiran_2, sistem_jaringan_jalan_pemutakhiran_2, peran_jalan_pemutakhiran_2, status_jalan_pemutakhiran_2, kelas_jalan_pemutakhiran_2, ";
				$sql .= "pembina_jalan_pemutakhiran_2, tanggal_selesai_diwujudkan_pemutakhiran_2, tanggal_dibuka_lalin_pemutakhiran_2, tanggal_ditutup_lalin_pemutakhiran_2) values ";
				$sql .= "(".$id_master_data.", '".$tahun."', '".$nomor_panjang_ruas_jalan."', '".$nama_pengenal_jalan."', '".$titik_awal_ruas_jalan;
				$sql .= "', '".$deskripsi_titik_awal_ruas_jalan."', '".$titik_akhir_ruas_jalan."', '".$deskripsi_titik_akhir_ruas_jalan;
				$sql .= "', '".$titik_ikat_awal_patok."', '".$deskripsi_titik_ikat_patok."', '".$sistem_jaringan_jalan."', '".$peran_jalan;
				$sql .= "', '".$status_jalan."', '".$kelas_jalan."', '".$pembina_jalan."', '".$tanggal_selesai_diwujudkan;
				$sql .= "', '".$tanggal_dibuka_untuk_lalin."', '".$tanggal_ditutup_untuk_lalin."')";
			}elseif($tipe == "pemutakhiran3"){
				$sql = "insert into ".$this->tableRingkasanDataIdentifikasi;
				$sql .= "(id_master_data, pemutakhiran_3, no_panjang_ruas_jalan_pemutakhiran_3, nama_pengenal_jalan_pemutakhiran_3, titik_awal_ruas_jalan_pemutakhiran_3, ";
				$sql .= "desc_titik_awal_ruas_jalan_pemutakhiran_3, titik_akhir_ruas_jalan_pemutakhiran_3, desc_titik_akhir_ruas_jalan_pemutakhiran_3, titik_ikat_awal_patok_km_pemutakhiran_3, ";
				$sql .= "desc_titik_ikat_awal_patok_km_pemutakhiran_3, sistem_jaringan_jalan_pemutakhiran_3, peran_jalan_pemutakhiran_3, status_jalan_pemutakhiran_3, kelas_jalan_pemutakhiran_3, ";
				$sql .= "pembina_jalan_pemutakhiran_3, tanggal_selesai_diwujudkan_pemutakhiran_3, tanggal_dibuka_lalin_pemutakhiran_3, tanggal_ditutup_lalin_pemutakhiran_3) values ";
				$sql .= "(".$id_master_data.", '".$tahun."', '".$nomor_panjang_ruas_jalan."', '".$nama_pengenal_jalan."', '".$titik_awal_ruas_jalan;
				$sql .= "', '".$deskripsi_titik_awal_ruas_jalan."', '".$titik_akhir_ruas_jalan."', '".$deskripsi_titik_akhir_ruas_jalan;
				$sql .= "', '".$titik_ikat_awal_patok."', '".$deskripsi_titik_ikat_patok."', '".$sistem_jaringan_jalan."', '".$peran_jalan;
				$sql .= "', '".$status_jalan."', '".$kelas_jalan."', '".$pembina_jalan."', '".$tanggal_selesai_diwujudkan;
				$sql .= "', '".$tanggal_dibuka_untuk_lalin."', '".$tanggal_ditutup_untuk_lalin."')";
			}elseif($tipe == "pemutakhiran4"){
				$sql = "insert into ".$this->tableRingkasanDataIdentifikasi;
				$sql .= "(id_master_data, pemutakhiran_4, no_panjang_ruas_jalan_pemutakhiran_4, nama_pengenal_jalan_pemutakhiran_4, titik_awal_ruas_jalan_pemutakhiran_4, ";
				$sql .= "desc_titik_awal_ruas_jalan_pemutakhiran_4, titik_akhir_ruas_jalan_pemutakhiran_4, desc_titik_akhir_ruas_jalan_pemutakhiran_4, titik_ikat_awal_patok_km_pemutakhiran_4, ";
				$sql .= "desc_titik_ikat_awal_patok_km_pemutakhiran_4, sistem_jaringan_jalan_pemutakhiran_4, peran_jalan_pemutakhiran_4, status_jalan_pemutakhiran_4, kelas_jalan_pemutakhiran_4, ";
				$sql .= "pembina_jalan_pemutakhiran_4, tanggal_selesai_diwujudkan_pemutakhiran_4, tanggal_dibuka_lalin_pemutakhiran_4, tanggal_ditutup_lalin_pemutakhiran_4) values ";
				$sql .= "(".$id_master_data.", '".$tahun."', '".$nomor_panjang_ruas_jalan."', '".$nama_pengenal_jalan."', '".$titik_awal_ruas_jalan;
				$sql .= "', '".$deskripsi_titik_awal_ruas_jalan."', '".$titik_akhir_ruas_jalan."', '".$deskripsi_titik_akhir_ruas_jalan;
				$sql .= "', '".$titik_ikat_awal_patok."', '".$deskripsi_titik_ikat_patok."', '".$sistem_jaringan_jalan."', '".$peran_jalan;
				$sql .= "', '".$status_jalan."', '".$kelas_jalan."', '".$pembina_jalan."', '".$tanggal_selesai_diwujudkan;
				$sql .= "', '".$tanggal_dibuka_untuk_lalin."', '".$tanggal_ditutup_untuk_lalin."')";
			}
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