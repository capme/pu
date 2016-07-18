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


		}elseif($method == "perwujudan"){

			$tipe = $data['tipe'];
			$tahun = $data['tahun'];
			$desain = $data['desain'];
			$pembebasan_lahan = $data['pembebasan_lahan'];
			$pembangunan = $data['pembangunan'];
			$peningkatan = $data['peningkatan'];
			$penunjangan = $data['penunjangan'];
			$pemeliharaan_dan_rehab = $data['pemeliharaan_dan_rehab'];
			$supervisi = $data['supervisi'];
			$tipe_bagian = $data['tipe_bagian'];
			$id_master_data = $data['id_master_data'];

			if($tipe == "asal"){
				if($tipe_bagian == "pelaksana"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, asal, desain_pelaksana_asal, pembebasan_lahan_pelaksana_asal, pembangunan_pelaksana_asal, peningkatan_pelaksana_asal, penunjangan_pelaksana_asal, pemeliharaan_pelaksana_asal, supervisi_pelaksana_asal)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "cacah"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, asal, desain_cacah_asal, pembebasan_lahan_cacah_asal, pembangunan_cacah_asal, peningkatan_cacah_asal, penunjangan_cacah_asal, pemeliharaan_cacah_asal, supervisi_cacah_asal)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "biaya"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, asal, desain_biaya_asal, pembebasan_lahan_biaya_asal, pembangunan_biaya_asal, peningkatan_biaya_asal, penunjangan_biaya_asal, pemeliharaan_biaya_asal, supervisi_biaya_asal)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "sumberdana"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, asal, desain_sumber_dana_asal, pembebasan_lahan_sumber_dana_asal, pembangunan_sumber_dana_asal, peningkatan_sumber_dana_asal, penunjangan_sumber_dana_asal, pemeliharaan_sumber_dana_asal, supervisi_sumber_dana_asal)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}
			}elseif($tipe == "pemutakhiran1"){
				if($tipe_bagian == "pelaksana"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_1, desain_pelaksana_pemutakhiran_1, pembebasan_lahan_pelaksana_pemutakhiran_1, pembangunan_pelaksana_pemutakhiran_1, peningkatan_pelaksana_pemutakhiran_1, penunjangan_pelaksana_pemutakhiran_1, pemeliharaan_pelaksana_pemutakhiran_1, supervisi_pelaksana_pemutakhiran_1)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "cacah"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_1, desain_cacah_pemutakhiran_1, pembebasan_lahan_cacah_pemutakhiran_1, pembangunan_cacah_pemutakhiran_1, peningkatan_cacah_pemutakhiran_1, penunjangan_cacah_pemutakhiran_1, pemeliharaan_cacah_pemutakhiran_1, supervisi_cacah_pemutakhiran_1)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "biaya"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_1, desain_biaya_pemutakhiran_1, pembebasan_lahan_biaya_pemutakhiran_1, pembangunan_biaya_pemutakhiran_1, peningkatan_biaya_pemutakhiran_1, penunjangan_biaya_pemutakhiran_1, pemeliharaan_biaya_pemutakhiran_1, supervisi_biaya_pemutakhiran_1)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "sumberdana"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_1, desain_sumber_dana_pemutakhiran_1, pembebasan_lahan_sumber_dana_pemutakhiran_1, pembangunan_sumber_dana_pemutakhiran_1, peningkatan_sumber_dana_pemutakhiran_1, penunjangan_sumber_dana_pemutakhiran_1, pemeliharaan_sumber_dana_pemutakhiran_1, supervisi_sumber_dana_pemutakhiran_1)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}
			}elseif($tipe == "pemutakhiran2"){
				if($tipe_bagian == "pelaksana"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_2, desain_pelaksana_pemutakhiran_2, pembebasan_lahan_pelaksana_pemutakhiran_2, pembangunan_pelaksana_pemutakhiran_2, peningkatan_pelaksana_pemutakhiran_2, penunjangan_pelaksana_pemutakhiran_2, pemeliharaan_pelaksana_pemutakhiran_2, supervisi_pelaksana_pemutakhiran_2)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "cacah"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_2, desain_cacah_pemutakhiran_2, pembebasan_lahan_cacah_pemutakhiran_2, pembangunan_cacah_pemutakhiran_2, peningkatan_cacah_pemutakhiran_2, penunjangan_cacah_pemutakhiran_2, pemeliharaan_cacah_pemutakhiran_2, supervisi_cacah_pemutakhiran_2)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "biaya"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_2, desain_biaya_pemutakhiran_2, pembebasan_lahan_biaya_pemutakhiran_2, pembangunan_biaya_pemutakhiran_2, peningkatan_biaya_pemutakhiran_2, penunjangan_biaya_pemutakhiran_2, pemeliharaan_biaya_pemutakhiran_2, supervisi_biaya_pemutakhiran_2)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "sumberdana"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_2, desain_sumber_dana_pemutakhiran_2, pembebasan_lahan_sumber_dana_pemutakhiran_2, pembangunan_sumber_dana_pemutakhiran_2, peningkatan_sumber_dana_pemutakhiran_2, penunjangan_sumber_dana_pemutakhiran_2, pemeliharaan_sumber_dana_pemutakhiran_2, supervisi_sumber_dana_pemutakhiran_2)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}
			}elseif($tipe == "pemutakhiran3"){
				if($tipe_bagian == "pelaksana"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_3, desain_pelaksana_pemutakhiran_3, pembebasan_lahan_pelaksana_pemutakhiran_3, pembangunan_pelaksana_pemutakhiran_3, peningkatan_pelaksana_pemutakhiran_3, penunjangan_pelaksana_pemutakhiran_3, pemeliharaan_pelaksana_pemutakhiran_3, supervisi_pelaksana_pemutakhiran_3)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "cacah"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_3, desain_cacah_pemutakhiran_3, pembebasan_lahan_cacah_pemutakhiran_3, pembangunan_cacah_pemutakhiran_3, peningkatan_cacah_pemutakhiran_3, penunjangan_cacah_pemutakhiran_3, pemeliharaan_cacah_pemutakhiran_3, supervisi_cacah_pemutakhiran_3)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "biaya"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_3, desain_biaya_pemutakhiran_3, pembebasan_lahan_biaya_pemutakhiran_3, pembangunan_biaya_pemutakhiran_3, peningkatan_biaya_pemutakhiran_3, penunjangan_biaya_pemutakhiran_3, pemeliharaan_biaya_pemutakhiran_3, supervisi_biaya_pemutakhiran_3)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "sumberdana"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_3, desain_sumber_dana_pemutakhiran_3, pembebasan_lahan_sumber_dana_pemutakhiran_3, pembangunan_sumber_dana_pemutakhiran_3, peningkatan_sumber_dana_pemutakhiran_3, penunjangan_sumber_dana_pemutakhiran_3, pemeliharaan_sumber_dana_pemutakhiran_3, supervisi_sumber_dana_pemutakhiran_3)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}
			}elseif($tipe == "pemutakhiran4"){
				if($tipe_bagian == "pelaksana"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_4, desain_pelaksana_pemutakhiran_4, pembebasan_lahan_pelaksana_pemutakhiran_4, pembangunan_pelaksana_pemutakhiran_4, peningkatan_pelaksana_pemutakhiran_4, penunjangan_pelaksana_pemutakhiran_4, pemeliharaan_pelaksana_pemutakhiran_4, supervisi_pelaksana_pemutakhiran_4)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "cacah"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_4, desain_cacah_pemutakhiran_4, pembebasan_lahan_cacah_pemutakhiran_4, pembangunan_cacah_pemutakhiran_4, peningkatan_cacah_pemutakhiran_4, penunjangan_cacah_pemutakhiran_4, pemeliharaan_cacah_pemutakhiran_4, supervisi_cacah_pemutakhiran_4)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "biaya"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_4, desain_biaya_pemutakhiran_4, pembebasan_lahan_biaya_pemutakhiran_4, pembangunan_biaya_pemutakhiran_4, peningkatan_biaya_pemutakhiran_4, penunjangan_biaya_pemutakhiran_4, pemeliharaan_biaya_pemutakhiran_4, supervisi_biaya_pemutakhiran_4)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}elseif($tipe_bagian == "sumberdana"){
					$sql = "INSERT INTO " . $this->tableRingkasanDataPerwujudan . "(id_master_data, pemutakhiran_4, desain_sumber_dana_pemutakhiran_4, pembebasan_lahan_sumber_dana_pemutakhiran_4, pembangunan_sumber_dana_pemutakhiran_4, peningkatan_sumber_dana_pemutakhiran_4, penunjangan_sumber_dana_pemutakhiran_4, pemeliharaan_sumber_dana_pemutakhiran_4, supervisi_sumber_dana_pemutakhiran_4)";
					$sql .= " values (".$id_master_data.", '".$tahun."', '".$desain."', '".$pembebasan_lahan."', '".$pembangunan."', '".$peningkatan."', '".$penunjangan."', '".$pemeliharaan_dan_rehab."', '".$supervisi."')";
				}
			}

			$this->db->query($sql);

		}elseif($method == "lintas harian rata2"){

			$tipe = $data['tipe'];
			$tahun = $data['tahun'];
			$sepeda_motor = $data['sepeda_motor'];
			$mobil_pribadi = $data['mobil_pribadi'];
			$mobil_penumpang = $data['mobil_penumpang'];
			$mobil_barang = $data['mobil_barang'];
			$bis_kecil = $data['bis_kecil'];
			$bis_besar = $data['bis_besar'];
			$truk_2_sumbu_kecil = $data['truk_2_sumbu_kecil'];
			$truk_2_sumbu_besar = $data['truk_2_sumbu_besar'];
			$truk_3_sumbu_atau_lebih = $data['truk_3_sumbu_atau_lebih'];
			$truk_dengan_gandengan = $data['truk_dengan_gandengan'];
			$truk_semi_trailer = $data['truk_semi_trailer'];
			$kendaraan_tidak_bermotor = $data['kendaraan_tidak_bermotor'];

			$id_master_data = $data['id_master_data'];

			if($tipe == "asal"){
				$sql = "insert into ".$this->tableRingkasanDataLintasHarian;
				$sql .= "(id_master_data, asal, sepeda_mtr_roda_3_asal, mobil_pribadi_asal, mobil_penumpang_asal, mobil_barang_asal, ";
				$sql .= "bis_kecil_asal, bis_besar_asal, truk_2_sumbu_kecil_asal, truck_2_sumbu_besar_asal, ";
				$sql .= "truk_3_sumbu_lebih_asal, truk_gandeng_asal, truk_semi_trailer_asal, kendaraan_tdk_bermotor_asal) values ";
				$sql .= "(".$id_master_data.", '".$tahun."', '".$sepeda_motor."', '".$mobil_pribadi."', '".$mobil_penumpang;
				$sql .= "', '".$mobil_barang."', '".$bis_kecil."', '".$bis_besar;
				$sql .= "', '".$truk_2_sumbu_kecil."', '".$truk_2_sumbu_besar."', '".$truk_3_sumbu_atau_lebih."', '".$truk_dengan_gandengan;
				$sql .= "', '".$truk_semi_trailer."', '".$kendaraan_tidak_bermotor."')";
			}elseif($tipe == "pemutakhiran1"){
				$sql = "insert into ".$this->tableRingkasanDataLintasHarian;
				$sql .= "(id_master_data, pemutakhiran_1, sepeda_mtr_roda_3_pemutakhiran_1, mobil_pribadi_pemutakhiran_1, mobil_penumpang_pemutakhiran_1, mobil_barang_pemutakhiran_1, ";
				$sql .= "bis_kecil_pemutakhiran_1, bis_besar_pemutakhiran_1, truk_2_sumbu_kecil_pemutakhiran_1, truck_2_sumbu_besar_pemutakhiran_1, ";
				$sql .= "truk_3_sumbu_lebih_pemutakhiran_1, truk_gandeng_pemutakhiran_1, truk_semi_trailer_pemutakhiran_1, kendaraan_tdk_bermotor_pemutakhiran_1) values ";
				$sql .= "(".$id_master_data.", '".$tahun."', '".$sepeda_motor."', '".$mobil_pribadi."', '".$mobil_penumpang;
				$sql .= "', '".$mobil_barang."', '".$bis_kecil."', '".$bis_besar;
				$sql .= "', '".$truk_2_sumbu_kecil."', '".$truk_2_sumbu_besar."', '".$truk_3_sumbu_atau_lebih."', '".$truk_dengan_gandengan;
				$sql .= "', '".$truk_semi_trailer."', '".$kendaraan_tidak_bermotor."')";
			}elseif($tipe == "pemutakhiran2"){
				$sql = "insert into ".$this->tableRingkasanDataLintasHarian;
				$sql .= "(id_master_data, pemutakhiran_2, sepeda_mtr_roda_3_pemutakhiran_2, mobil_pribadi_pemutakhiran_2, mobil_penumpang_pemutakhiran_2, mobil_barang_pemutakhiran_2, ";
				$sql .= "bis_kecil_pemutakhiran_2, bis_besar_pemutakhiran_2, truk_2_sumbu_kecil_pemutakhiran_2, truck_2_sumbu_besar_pemutakhiran_2, ";
				$sql .= "truk_3_sumbu_lebih_pemutakhiran_2, truk_gandeng_pemutakhiran_2, truk_semi_trailer_pemutakhiran_2, kendaraan_tdk_bermotor_pemutakhiran_2) values ";
				$sql .= "(".$id_master_data.", '".$tahun."', '".$sepeda_motor."', '".$mobil_pribadi."', '".$mobil_penumpang;
				$sql .= "', '".$mobil_barang."', '".$bis_kecil."', '".$bis_besar;
				$sql .= "', '".$truk_2_sumbu_kecil."', '".$truk_2_sumbu_besar."', '".$truk_3_sumbu_atau_lebih."', '".$truk_dengan_gandengan;
				$sql .= "', '".$truk_semi_trailer."', '".$kendaraan_tidak_bermotor."')";
			}elseif($tipe == "pemutakhiran3"){
				$sql = "insert into ".$this->tableRingkasanDataLintasHarian;
				$sql .= "(id_master_data, pemutakhiran_3, sepeda_mtr_roda_3_pemutakhiran_3, mobil_pribadi_pemutakhiran_3, mobil_penumpang_pemutakhiran_3, mobil_barang_pemutakhiran_3, ";
				$sql .= "bis_kecil_pemutakhiran_3, bis_besar_pemutakhiran_3, truk_2_sumbu_kecil_pemutakhiran_3, truck_2_sumbu_besar_pemutakhiran_3, ";
				$sql .= "truk_3_sumbu_lebih_pemutakhiran_3, truk_gandeng_pemutakhiran_3, truk_semi_trailer_pemutakhiran_3, kendaraan_tdk_bermotor_pemutakhiran_3) values ";
				$sql .= "(".$id_master_data.", '".$tahun."', '".$sepeda_motor."', '".$mobil_pribadi."', '".$mobil_penumpang;
				$sql .= "', '".$mobil_barang."', '".$bis_kecil."', '".$bis_besar;
				$sql .= "', '".$truk_2_sumbu_kecil."', '".$truk_2_sumbu_besar."', '".$truk_3_sumbu_atau_lebih."', '".$truk_dengan_gandengan;
				$sql .= "', '".$truk_semi_trailer."', '".$kendaraan_tidak_bermotor."')";
			}elseif($tipe == "pemutakhiran4"){
				$sql = "insert into ".$this->tableRingkasanDataLintasHarian;
				$sql .= "(id_master_data, pemutakhiran_4, sepeda_mtr_roda_3_pemutakhiran_4, mobil_pribadi_pemutakhiran_4, mobil_penumpang_pemutakhiran_4, mobil_barang_pemutakhiran_4, ";
				$sql .= "bis_kecil_pemutakhiran_4, bis_besar_pemutakhiran_4, truk_2_sumbu_kecil_pemutakhiran_4, truck_2_sumbu_besar_pemutakhiran_4, ";
				$sql .= "truk_3_sumbu_lebih_pemutakhiran_4, truk_gandeng_pemutakhiran_4, truk_semi_trailer_pemutakhiran_4, kendaraan_tdk_bermotor_pemutakhiran_4) values ";
				$sql .= "(".$id_master_data.", '".$tahun."', '".$sepeda_motor."', '".$mobil_pribadi."', '".$mobil_penumpang;
				$sql .= "', '".$mobil_barang."', '".$bis_kecil."', '".$bis_besar;
				$sql .= "', '".$truk_2_sumbu_kecil."', '".$truk_2_sumbu_besar."', '".$truk_3_sumbu_atau_lebih."', '".$truk_dengan_gandengan;
				$sql .= "', '".$truk_semi_trailer."', '".$kendaraan_tidak_bermotor."')";
			}
			$this->db->query($sql);

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