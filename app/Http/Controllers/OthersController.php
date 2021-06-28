<?php

namespace App\Http\Controllers;

use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use Hash;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Worksheet_MemoryDrawing;
//use Mail;

class OthersController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }

	public function profil()
	{
		$pdo = DB::getPdo();
        $sql = 'SELECT * FROM user WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
        $stmt->execute();
        $profil = $stmt->fetch(PDO::FETCH_OBJ);
        Utils::insertLogUser('akses menu profil');
		return view('profil', ['profil' => $profil, 'menu' => 'profil']);
	}

	public function profilData(Request $request)
	{
        $pdo = DB::getPdo();
        $where = '';
        if(Session::has('conf_webperusahaan')){
            $where .= ' AND idperusahaan = :idperusahaan';
        }
        $table = '(SELECT
                        l.id,
                        u.id as iduser,
                        p.idperusahaan,
                        l.waktu as tanggal,
                        l.keterangan,
                        l.method,
                        l.path,
                        l.body
                    FROM
                        _loguser l,
                        `user` u,
                        pengelola p
                    WHERE
                        u.id=l.iduser AND u.id=p.iduser) x';
        $columns = array('tanggal','keterangan','method','path','body');
        $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE iduser = :iduser '.$where;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        if(Session::has('conf_webperusahaan')) {
            $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalData = $row['total'];
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumn = $columns[$request->input('order.0.column')];
        $orderAction = $request->input('order.0.dir');
        $orderBy = $orderColumn.' '.$orderAction;

        if(!empty($request->input('search.value'))){
            $search = $request->input('search.value');
            $where .= Utils::searchDatatableQuery($columns);
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE iduser = :iduser '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
            if(Session::has('conf_webperusahaan')) {
                $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
            }
            for($i=0;$i<count($columns);$i++) {
                if($columns[$i] != '') {
                    $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                }
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalFiltered = $row['total'];
        }

        $sql = 'SELECT * FROM '.$table.' WHERE iduser = :iduser ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        if(Session::has('conf_webperusahaan')) {
            $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
        }
        if(!empty($request->input('search.value'))) {
            for($i=0;$i<count($columns);$i++) {
                if($columns[$i] != '') {
                    $stmt->bindValue(':' . $columns[$i], '%' . $request->input('search.value') . '%');
                }
            }
        }
        $stmt->execute();
        $originaldata = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array();
        if(!empty($originaldata)){
            foreach($originaldata as $key){
                $tempdata['tanggal'] = $key['tanggal'];
                for($i=1;$i<count($columns);$i++){
                    $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                }
                $data[] = $tempdata;
            }
        }
        return Utils::jsonDatatable($request->input('draw'),$totalData,$totalFiltered,$data);
	}

	public function ubahProfil()
	{
		$pdo = DB::getPdo();
        $sql = 'SELECT * FROM user WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
        $stmt->execute();
        $profil = $stmt->fetch(PDO::FETCH_OBJ);
        Utils::insertLogUser('akses menu ubah profil');
		return view('gantiprofil', ['profil' => $profil, 'menu' => 'profil']);
	}

	public function submitUbahProfil(Request $request)
	{
		$pdo = DB::getPdo();
		$sql = 'UPDATE user SET nama = :nama, nomorhp = :nomorhp WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nama', $request->nama);
        $stmt->bindValue(':nomorhp', $request->nomorhp);
        $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
        $stmt->execute();

        Session::set('namauser_perusahaan', $request->nama);

		Utils::insertLogUser('Ganti profil');

        return redirect('profil')->with('message', trans('all.databerhasildiubah'));
	}

	public function submitGantiFotoProfil(Request $request)
	{
		$pdo = DB::getPdo();
		if( $request->hasFile('file') ) {

			$fotoprofil = $request->file('file');
			if($fotoprofil->getMimeType() == 'image/jpeg' || $fotoprofil->getMimeType() == 'image/png' || $fotoprofil->getMimeType() == 'image/bmp'){
		        $iduser = Session::get('iduser_perusahaan');
		        $sql = 'SELECT id FROM user WHERE id=:iduser LIMIT 1';
			    $stmt = $pdo->prepare($sql);
			    $stmt->bindValue(':iduser', $iduser);
			    $stmt->execute();
			    if ($stmt->rowCount()>0) {
				    $path = $_SERVER['DOCUMENT_ROOT'].'/'.config('consts.FOLDER_IMG');
					if (!file_exists($path)) {
						mkdir($path, 0777, true);
					}
					$path = $path.'/'.config('consts.FOLDER_FOTO_USER');
					if (!file_exists($path)) {
						mkdir($path, 0777, true);
					}

					Utils::makeThumbnail($fotoprofil, $path.'/'.$iduser);

					Utils::saveUploadImage($fotoprofil, $path.'/'.$iduser);

					$checksum = md5_file($path.'/'.$iduser);

					$sql = 'UPDATE user set checksum_img = :checksum WHERE id = :iduser';
					$stmt = $pdo->prepare($sql);
				    $stmt->bindValue(':checksum', $checksum);
				    $stmt->bindValue(':iduser', $iduser);
				    $stmt->execute();
				    Session::set('fotouser_perusahaan', 'ada');

					Utils::insertLogUser('Ganti foto profil');
				    
					$msg = trans('all.fotoberhasildiubah');
				}
				else {
					$msg = trans('all.usertidakditemukanatautidakdiijinkanmengakses');
				}
			}else{
				$msg = trans('all.formatgambarharusjpg');
            }

	    }else{
			$msg = trans('all.tidakadafoto');
        }
        return redirect('profil')->with('message', $msg);
    }

	public function submitGantiKataSandi(Request $request){

		$iduser = Session::get('iduser_perusahaan');
		// cek katasandi lama
		$pdo = DB::getPdo();
		$sql = 'SELECT password FROM user WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(Hash::check($request->katasandilama, $row['password'])){
        	$sql = 'UPDATE user SET password = :password WHERE id = :id';
        	$stmt = $pdo->prepare($sql);
        	$stmt->bindValue(':password', Hash::make($request->katasandibaru));
        	$stmt->bindValue(':id', Session::get('iduser_perusahaan'));
        	$stmt->execute();

			//hapus token lama
			$sql = 'INSERT INTO authtokenblacklist_user SELECT iduser, idtoken, expired, NOW() FROM authtoken_user WHERE iduser=:iduser ON DUPLICATE KEY UPDATE authtokenblacklist_user.expired=GREATEST(authtokenblacklist_user.expired, authtoken_user.expired);';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':iduser', $iduser);
			$stmt->execute();

            Utils::deleteData($pdo,'authtoken_user',$iduser,'iduser');

			$sql = 'SELECT
						ufp.id
					FROM
						user_forgetpwd ufp,
						`user` u
					WHERE
						ufp.iduser=u.id AND
						u.id=:iduser AND 
						expired>NOW()
					LIMIT 1';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':iduser', $iduser);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

            Utils::deleteData($pdo,'user_forgetpwd',$row['id']);
			Utils::insertLogUser('Ganti katasandi');
        	
        	return redirect('profil')->with('message', trans('all.katasandiberhasildiubah'));
        }else{
        	return redirect('profil/gantikatasandi')->with('message', trans('all.katasandilamasalah'));
        }

	}

	public function bugsreport()
	{
		Utils::insertLogUser('akses menu bugs report');
		return view('bugsreport', ['menu' => 'bugsreport']);
	}

	public function pengaturan()
	{
        if(Utils::cekHakakses('pengaturan','lu')){
			$pdo = DB::connection('perusahaan_db')->getPdo();
			$sql = 'SELECT *,DATE(kuncidatasebelumtanggal) as kuncidatasebelumtanggal,TIME(kuncidatasebelumtanggal) as kuncidatasebelumtanggal_jam FROM pengaturan';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$pengaturan = $stmt->fetchAll(PDO::FETCH_OBJ);

			$sql = 'SELECT id,nama,ijinkanpendaftaran FROM mesin';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$mesin = $stmt->fetchAll(PDO::FETCH_OBJ);

			//jumlah mesin yg di ijinkan pendaftaran
			$sql = 'SELECT COUNT(*) as total FROM mesin WHERE ijinkanpendaftaran = "y"';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$rowssm = $stmt->fetch(PDO::FETCH_ASSOC);
			$totalselectedmesin = $rowssm['total'];
			Utils::insertLogUser('akses menu pengaturan');
			return view('pengaturan', ['pengaturan' => $pengaturan, 'totalselectedmesin' => $totalselectedmesin, 'mesin' => $mesin, 'menu' => 'pengaturanumum']);
		}else{
			return redirect('/');
		}
	}

	public function submitpengaturan(Request $request){
        $pdo = DB::connection('perusahaan_db')->getPdo();
//        return Utils::convertDmy2Ymd($request->kuncidatasebelumtanggal).' '.$request->kuncidatasebelumtanggal_jam;
        if(isset($request->batas_kemiripan_absen_wajah)) {
            if(Utils::cekDateTime($request->end_of_day) || (Utils::cekDateTime($request->kuncidatasebelumtanggal) && Utils::cekDateTime($request->kuncidatasebelumtanggal_jam))) {
                $sql = 'UPDATE pengaturan SET batas_kemiripan_absen_wajah = :batas_kemiripan_absen_wajah, batas_kemiripan_konfirmasi_absen_wajah = :batas_kemiripan_konfirmasi_absen_wajah, batas_kemiripan_pendaftaran_wajah = :batas_kemiripan_pendaftaran_wajah, gunakan_absen_wajah_otomatis = :gunakan_absen_wajah_otomatis, batas_kemiripan_absen_wajah_otomatis = :batas_kemiripan_absen_wajah_otomatis, batas_kemiripan_konfirmasi_absen_wajah_otomatis = :batas_kemiripan_konfirmasi_absen_wajah_otomatis, pemindai_rfid = :pemindai_rfid, pemindai_nfc = :pemindai_nfc, pemindai_barcode = :pemindai_barcode, absen_harus_dengan_alasan = :absen_harus_dengan_alasan, batas_konfirmasi_absen = :batas_konfirmasi_absen, default_konfirmasi_absen = :default_konfirmasi_absen, utc = :utc, toleransi_waktu_server = :toleransi_waktu_server, gps_harus_aktif = :gps_harus_aktif, gps_perbolehkan_absen_diluar_area = :gps_perbolehkan_absen_diluar_area, toleransi_jarak_gps = :toleransi_jarak_gps, tampilkan_flexytime = :tampilkan_flexytime, end_of_day = :end_of_day, mesin_getid_opsi = :mesin_getid_opsi, mesin_polapengaman_pakai = :mesin_polapengaman_pakai, mesin_polapengaman = :mesin_polapengaman, mesin_deteksiekspresi = :mesin_deteksiekspresi, mesin_deteksiekspresi_batas = :mesin_deteksiekspresi_batas, mesin_tampilportal = :mesin_tampilportal, mesin_tampillatarpeta = :mesin_tampillatarpeta, employee_ijinkantukarshift = :employee_ijinkantukarshift, employee_ijinkanpengajuanlembur = :employee_ijinkanpengajuanlembur, employee_ijinkanpengajuanlupaabsen = :employee_ijinkanpengajuanlupaabsen, employee_ijinkanpengajuantidakterlambat = :employee_ijinkanpengajuantidakterlambat, employee_ijinkanpengajuantidakpulangawal = :employee_ijinkanpengajuantidakpulangawal, employee_ijinkangantifotoprofile = :employee_ijinkangantifotoprofile, employee_tracker_gunakandefault = :employee_tracker_gunakandefault, employee_tracker_intervaldefault = :employee_tracker_intervaldefault, employee_tracker_lamashiftberakhir = :employee_tracker_lamashiftberakhir, employee_ijinkansambungdatacapture = :employee_ijinkansambungdatacapture, employee_gunakanaktivitas = :employee_gunakanaktivitas, default_perlakuanlembur = :default_perlakuanlembur, kuncidatasebelumtanggal = :kuncidatasebelumtanggal';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':batas_kemiripan_absen_wajah', $request->batas_kemiripan_absen_wajah);
                $stmt->bindValue(':batas_kemiripan_konfirmasi_absen_wajah', $request->batas_kemiripan_konfirmasi_absen_wajah);
                $stmt->bindValue(':batas_kemiripan_pendaftaran_wajah', $request->batas_kemiripan_pendaftaran_wajah);
                $stmt->bindValue(':gunakan_absen_wajah_otomatis', ($request->gunakan_absen_wajah_otomatis == '' ? 't' : 'y'));
                $stmt->bindValue(':batas_kemiripan_absen_wajah_otomatis', $request->batas_kemiripan_absen_wajah_otomatis);
                $stmt->bindValue(':batas_kemiripan_konfirmasi_absen_wajah_otomatis', $request->batas_kemiripan_konfirmasi_absen_wajah_otomatis);
                $stmt->bindValue(':pemindai_rfid', ($request->pemindai_rfid == '' ? 't' : 'y'));
                $stmt->bindValue(':pemindai_nfc', ($request->pemindai_nfc == '' ? 't' : 'y'));
                $stmt->bindValue(':pemindai_barcode', ($request->pemindai_barcode == '' ? 't' : 'y'));
                $stmt->bindValue(':absen_harus_dengan_alasan', ($request->absen_harus_dengan_alasan == '' ? 't' : 'y'));
                $stmt->bindValue(':batas_konfirmasi_absen', $request->batas_konfirmasi_absen);
                $stmt->bindValue(':default_konfirmasi_absen', $request->default_konfirmasi_absen);
                $stmt->bindValue(':utc', $request->utc);
                $stmt->bindValue(':toleransi_waktu_server', $request->toleransi_waktu_server);
                $stmt->bindValue(':gps_harus_aktif', ($request->gps_harus_aktif == '' ? 't' : 'y'));
                $stmt->bindValue(':gps_perbolehkan_absen_diluar_area', ($request->gps_perbolehkan_absen_diluar_area == '' ? 't' : 'y'));
                $stmt->bindValue(':toleransi_jarak_gps', $request->toleransi_jarak_gps);
                $stmt->bindValue(':tampilkan_flexytime', ($request->tampilkan_flexytime == '' ? 't' : 'y'));
                $stmt->bindValue(':end_of_day', $request->end_of_day);
                $stmt->bindValue(':mesin_getid_opsi', $request->mesin_getid_opsi);
                $stmt->bindValue(':mesin_polapengaman_pakai', ($request->mesin_polapengaman_pakai == '' ? 't' : 'y'));
                $stmt->bindValue(':mesin_polapengaman', $request->mesin_polapengaman);
                $stmt->bindValue(':mesin_deteksiekspresi', ($request->mesin_deteksiekspresi == '' ? 't' : 'y'));
                $stmt->bindValue(':mesin_deteksiekspresi_batas', $request->mesin_deteksiekspresi_batas);
                $stmt->bindValue(':mesin_tampilportal', ($request->mesin_tampilportal == '' ? 't' : 'y'));
                $stmt->bindValue(':mesin_tampillatarpeta', ($request->mesin_tampillatarpeta == '' ? 't' : 'y'));
                $stmt->bindValue(':employee_ijinkantukarshift', ($request->employee_ijinkantukarshift == '' ? 't' : 'y'));
                $stmt->bindValue(':employee_ijinkanpengajuanlembur', ($request->employee_ijinkanpengajuanlembur == '' ? 't' : 'y'));
                $stmt->bindValue(':employee_ijinkanpengajuanlupaabsen', ($request->employee_ijinkanpengajuanlupaabsen == '' ? 't' : 'y'));
                $stmt->bindValue(':employee_ijinkanpengajuantidakterlambat', ($request->employee_ijinkanpengajuantidakterlambat == '' ? 't' : 'y'));
                $stmt->bindValue(':employee_ijinkanpengajuantidakpulangawal', ($request->employee_ijinkanpengajuantidakpulangawal == '' ? 't' : 'y'));
                $stmt->bindValue(':employee_ijinkangantifotoprofile', ($request->employee_ijinkangantifotoprofile == '' ? 't' : 'y'));
                $stmt->bindValue(':employee_tracker_gunakandefault', ($request->gunakandefaulttracker == '' ? 't' : 'y'));
                $stmt->bindValue(':employee_tracker_intervaldefault', $request->trackerintervaldefault);
                $stmt->bindValue(':employee_tracker_lamashiftberakhir', $request->employee_tracker_lamashiftberakhir);
                $stmt->bindValue(':employee_ijinkansambungdatacapture', ($request->employee_ijinkansambungdatacapture == '' ? 't' : 'y'));
                $stmt->bindValue(':employee_gunakanaktivitas', ($request->employee_gunakanaktivitas == '' ? 't' : 'y'));
                $stmt->bindValue(':default_perlakuanlembur', $request->default_perlakuanlembur);
                $stmt->bindValue(':kuncidatasebelumtanggal', $request->kuncidatasebelumtanggal != '' ? Utils::convertDmy2Ymd($request->kuncidatasebelumtanggal) . ' ' . $request->kuncidatasebelumtanggal_jam : NULL);
                $stmt->execute();

                //kirim gcm info absen
                Utils::kirimGCMSync(Session::get('conf_webperusahaan'));
            }else{
                return redirect('pengaturan/umum')->with('message', trans('all.terjadigangguan'));
            }
		}

		//set mesin
		if(isset($request->ijinkanpendaftaranmesin)){
			//set semua mesin ke tidak
			$sql = 'UPDATE mesin SET ijinkanpendaftaran = "t"';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();

			if(isset($request->ijinkanpendaftaran)) {
				if (count($request->ijinkanpendaftaran) > 0) {
					for ($i = 0; $i < count($request->ijinkanpendaftaran); $i++) {
						//set semua mesin sesuai dengan yg di pilih
						$sql = 'UPDATE mesin SET ijinkanpendaftaran = "y" WHERE id = :id';
						$stmt = $pdo->prepare($sql);
						$stmt->bindValue(':id', $request->ijinkanpendaftaran[$i]);
						$stmt->execute();
					}
				}
			}
		}
		Utils::insertLogUser('Ubah pengaturan');
		return redirect('pengaturan/umum')->with('message', trans('all.databerhasildisimpan'));
	}

    public function peringkat()
    {
        if(Utils::cekHakakses('pengaturan','lu')){
			$pdo = DB::connection('perusahaan_db')->getPdo();
			$sql = 'SELECT * FROM pengaturan_peringkat WHERE dipakai = "t"';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$datatersedia = $stmt->fetchAll(PDO::FETCH_OBJ);

			$sql = 'SELECT * FROM pengaturan_peringkat WHERE dipakai = "y" ORDER BY urutan';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$dataterpakai = $stmt->fetchAll(PDO::FETCH_OBJ);
			Utils::insertLogUser('akses menu peringkat');
			return view('pengaturan/peringkat/index', ['datatersedia' => $datatersedia, 'dataterpakai' => $dataterpakai, 'menu' => 'peringkat']);
		}else{
			return redirect('/');
		}
    }

    public function submitPeringkat(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        try {

            $totaldata = count($request->nama);
            $sql = 'UPDATE pengaturan_peringkat SET urutan = NULL, dipakai = "t"';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            if($totaldata > 0) {
                for ($i = 0; $i < $totaldata; $i++) {
                    $urutan = $i + 1;
                    $sql = 'UPDATE pengaturan_peringkat SET urutan = :urutan, `order` = :orderby, dipakai = "y" WHERE nama = :nama';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':urutan', $urutan);
                    $stmt->bindValue(':orderby', $request->urutan[$i]);
                    $stmt->bindValue(':nama', $request->nama[$i]);
                    $stmt->execute();
                }
            }

            self::hitungPeringkat('self');

            $hasil = trans('all.databerhasildisimpan');
        }catch (\Exception $e){
            $hasil = $e->getMessage();
		}
		Utils::insertLogUser('ubah pengaturan peringkat');
        return redirect('pengaturan/peringkat')->with('message', $hasil);
    }

    public function hitungPeringkat($from = "post")
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'CALL buatperingkatabsen(CURRENT_DATE())';
        $stmt = $pdo->prepare($sql);
		$stmt->execute();
		Utils::insertLogUser('hitung peringkat');
        if($from == 'post') {
            $response = array();
            $response['status'] = 'OK';
            $response['pesan'] = trans('all.hitungperingkatberhasil');
            return $response;
        }
    }

	public function formatSmsAbsen()
	{
        if(Utils::cekHakakses('pengaturan','lu')){
			$pdo = DB::connection('perusahaan_db')->getPdo();
			$sql = 'SELECT format_sms_absen,kirimsms FROM pengaturan';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_OBJ);

			if($data->kirimsms == 'tt'){
				return redirect('pengaturan/formatsmsverifikasi');
			}
			Utils::insertLogUser('akses menu format sms absen');
			return view('formatsmsabsen', ['data' => $data,  'menu' => 'formatsms', 'jenis' => 'absen']);
		}else{
			return redirect('/');
		}
	}

	public function formatSmsAbsenSubmit(Request $request)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sql = 'UPDATE pengaturan SET format_sms_absen = :formatsms';
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':formatsms', $request->formatsms);
		$stmt->execute();
		Utils::insertLogUser('ubah pengaturan format sms absen');
		return redirect('pengaturan/formatsmsabsen')->with('message', trans('all.databerhasildisimpan'));
	}

	public function formatSmsVerifikasi()
	{
        if(Utils::cekHakakses('pengaturan','lu')){
			$pdo = DB::connection('perusahaan_db')->getPdo();
			$sql = 'SELECT format_sms_verifikasi_lupa_pwd_pegawai,kirimsms FROM pengaturan';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_OBJ);
			Utils::insertLogUser('akses menu format sms verifikasi');
			return view('formatsmsverifikasi', ['data' => $data,  'menu' => 'formatsms', 'jenis' => 'verifikasi']);
		}else{
			return redirect('/');
		}
	}

	public function formatSmsVerifikasiSubmit(Request $request)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sql = 'UPDATE pengaturan SET format_sms_verifikasi_lupa_pwd_pegawai = :formatsms';
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':formatsms', $request->formatsms);
		$stmt->execute();
		Utils::insertLogUser('ubah format sms verifikasi');
		return redirect('pengaturan/formatsmsverifikasi')->with('message', trans('all.databerhasildisimpan'));
	}

	public function formatSmsLupaPwdPegawai()
	{
        if(Utils::cekHakakses('pengaturan','lu')){
			$pdo = DB::connection('perusahaan_db')->getPdo();
			$sql = 'SELECT format_sms_lupa_pwd_pegawai,kirimsms FROM pengaturan';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_OBJ);
			Utils::insertLogUser('akses menu format sms lupa password pegawai');
			return view('formatsmslupapwdpegawai', ['data' => $data,  'menu' => 'formatsms', 'jenis' => 'lupapwdpegawai']);
		}else{
			return redirect('/');
		}
	}

	public function formatSmsLupaPwdPegawaiSubmit(Request $request)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sql = 'UPDATE pengaturan SET format_sms_lupa_pwd_pegawai = :formatsms';
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':formatsms', $request->formatsms);
		$stmt->execute();
		Utils::insertLogUser('ubah format sms lupa password pegawai');
		return redirect('pengaturan/formatsmslupapwdpegawai')->with('message', trans('all.databerhasildisimpan'));
	}

	public function parameterEkspor()
	{
        if(Utils::cekHakakses('pengaturan','lu')){
			$pdo = DB::connection('perusahaan_db')->getPdo();
			$sql = 'SELECT * FROM parameterekspor';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			Utils::insertLogUser('akses menu parameter ekspor');
			return view('parameterekspor', ['data' => $data,  'menu' => 'parameterekspor']);
		}else{
			return redirect('/');
		}
	}

	public function submitParameterEkspor(Request $request)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sql = 'SELECT gunakanpwd,pwd FROM parameterekspor';
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		if($totaldata == 0){
			$sql = 'INSERT INTO parameterekspor VALUES("t","","","","","normal","","normal","","normal","","normal","","normal","","normal","","normal","","normal","5","","normal","","normal","","normal","","normal","","normal","5","","normal","","normal","","normal","","normal","","normal","5","","normal","","normal",NULL)';
			$stmt= $pdo->prepare($sql);
			$stmt->execute();
		}

		if($request->dari == 'logo'){

			$sql = 'SELECT logokiri,logokanan FROM parameterekspor';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$rowLogo = $stmt->fetch(PDO::FETCH_ASSOC);

			if( $request->hasFile('logokiri') ) {
				$path = Session::get('folderroot_perusahaan').'/parameterekspor/';
				if (!file_exists($path)) {
					mkdir($path, 0777, true);
				}
				if($request->hasFile('logokiri') != ''){
					//hapus file lama
					if($rowLogo['logokiri'] != '') {
						$pathlogokirilama = $path . '/' . $rowLogo['logokiri'];
						if (file_exists($pathlogokirilama)) {
							unlink($pathlogokirilama);
						}
					}

					$logokiri = $request->file('logokiri');
					//dapatkan ukuran gambar(panjang dan lebar)
					if($logokiri->getMimeType() == 'image/jpeg') {
						$img = imagecreatefromjpeg($logokiri);
					}else if($logokiri->getMimeType() == 'image/png'){
						$img = imagecreatefrompng($logokiri);
					}else if($logokiri->getMimeType() == 'image/bmp'){
						$img = imagecreatefrombmp($logokiri);
					}

					$width = imagesx( $img );
					$height = imagesy( $img );

					$gambarsementara = '';
					if($width> 640){
						//ukuran gambar maksimal
						$new_width = 640;
						$new_height = 480;

						//buat temporari gambar
						$tmp_img = imagecreatetruecolor( $new_width, $new_height );
						//resize temporari gambar
						imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
						//simpan gambar dan kasih nama random
						$gambarsementara = $path.'/'.time().'.jpg';
						imagejpeg( $tmp_img, $gambarsementara );
						//file fotoprofil yang sudah diresize
						$logokiri = $gambarsementara;
					}

					$randomnama = 'kiri';
					// jika file foto sudah ada, maka akan di overwrite
					$sql = 'UPDATE parameterekspor SET logokiri = :logokiri';
					$stmt = $pdo->prepare($sql);
					$stmt->bindValue(':logokiri', $randomnama);
					$stmt->execute();

					$dataEnrkip = Utils::encrypt($logokiri);
					file_put_contents($path . '/' . $randomnama, $dataEnrkip);

					//hapus foto temporary yang sudah di kompress
					if(file_exists($gambarsementara)){
						unlink($gambarsementara);
					}
				}
			}

			if( $request->hasFile('logokanan') ) {
				$path = Session::get('folderroot_perusahaan').'/parameterekspor/';
				if (!file_exists($path)) {
					mkdir($path, 0777, true);
				}
				if($request->hasFile('logokanan') != ''){

					//hapus file lama
					if($rowLogo['logokanan'] != ''){
						$pathlogokananlama = $path.'/'.$rowLogo['logokanan'];
						if(file_exists($pathlogokananlama)){
							unlink($pathlogokananlama);
						}
					}

					$logokanan = $request->file('logokanan');
					//dapatkan ukuran gambar(panjang dan lebar)
					$img = imagecreatefromjpeg($logokanan);

					$width = imagesx( $img );
					$height = imagesy( $img );

					$gambarsementara = '';
					if($width> 640){
						//ukuran gambar maksimal
						$new_width = 640;
						$new_height = 480;

						//buat temporari gambar
						$tmp_img = imagecreatetruecolor( $new_width, $new_height );
						//resize temporari gambar
						imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
						//simpan gambar dan kasih nama random
						$gambarsementara = $path.'/'.time().'.jpg';
						imagejpeg( $tmp_img, $gambarsementara );
						//file fotoprofil yang sudah diresize
						$logokanan = $gambarsementara;
					}
					
					$randomnama = 'kanan';
					// jika file foto sudah ada, maka akan di overwrite
					$sql = 'UPDATE parameterekspor SET logokanan = :logokanan';
					$stmt = $pdo->prepare($sql);
					$stmt->bindValue(':logokanan', $randomnama);
					$stmt->execute();

					$dataEnrkip = Utils::encrypt($logokanan);
					file_put_contents($path . '/' . $randomnama, $dataEnrkip);

					//hapus foto temporary yang sudah di kompress
					if(file_exists($gambarsementara)){
						unlink($gambarsementara);
					}
				}
			}

		}elseif($request->dari == 'header5baris'){
			$sql = 'UPDATE parameterekspor SET header_1_teks = :header_1_teks, header_1_fontstyle = :header_1_fontstyle, header_2_teks = :header_2_teks, header_2_fontstyle = :header_2_fontstyle, header_3_teks = :header_3_teks, header_3_fontstyle = :header_3_fontstyle, header_4_teks = :header_4_teks, header_4_fontstyle = :header_4_fontstyle, header_5_teks = :header_5_teks, header_5_fontstyle = :header_5_fontstyle';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':header_1_teks', $request->header_1_teks);
			$stmt->bindValue(':header_1_fontstyle', $request->header_1_fontstyle);
			$stmt->bindValue(':header_2_teks', $request->header_2_teks);
			$stmt->bindValue(':header_2_fontstyle', $request->header_2_fontstyle);
			$stmt->bindValue(':header_3_teks', $request->header_3_teks);
			$stmt->bindValue(':header_3_fontstyle', $request->header_3_fontstyle);
			$stmt->bindValue(':header_4_teks', $request->header_4_teks);
			$stmt->bindValue(':header_4_fontstyle', $request->header_4_fontstyle);
			$stmt->bindValue(':header_5_teks', $request->header_5_teks);
			$stmt->bindValue(':header_5_fontstyle', $request->header_5_fontstyle);
			$stmt->execute();
		}else if($request->dari == 'footer'){
			$sql = 'UPDATE
						parameterekspor
					SET
						footerkiri_1_teks = :footerkiri_1_teks,
						footerkiri_1_fontstyle = :footerkiri_1_fontstyle,
						footerkiri_2_teks = :footerkiri_2_teks,
						footerkiri_2_fontstyle = :footerkiri_2_fontstyle,
						footerkiri_3_teks = :footerkiri_3_teks,
						footerkiri_3_fontstyle = :footerkiri_3_fontstyle,
						footerkiri_4_separator = :footerkiri_4_separator,
						footerkiri_5_teks = :footerkiri_5_teks,
						footerkiri_5_fontstyle = :footerkiri_5_fontstyle,
						footerkiri_6_teks = :footerkiri_6_teks,
						footerkiri_6_fontstyle = :footerkiri_6_fontstyle,
						footertengah_1_teks = :footertengah_1_teks,
						footertengah_1_fontstyle = :footertengah_1_fontstyle,
						footertengah_2_teks = :footertengah_2_teks,
						footertengah_2_fontstyle = :footertengah_2_fontstyle,
						footertengah_3_teks = :footertengah_3_teks,
						footertengah_3_fontstyle = :footertengah_3_fontstyle,
						footertengah_4_separator = :footertengah_4_separator,
						footertengah_5_teks = :footertengah_5_teks,
						footertengah_5_fontstyle = :footertengah_5_fontstyle,
						footertengah_6_teks = :footertengah_6_teks,
						footertengah_6_fontstyle = :footertengah_6_fontstyle,
						footerkanan_1_teks = :footerkanan_1_teks,
						footerkanan_1_fontstyle = :footerkanan_1_fontstyle,
						footerkanan_2_teks = :footerkanan_2_teks,
						footerkanan_2_fontstyle = :footerkanan_2_fontstyle,
						footerkanan_3_teks = :footerkanan_3_teks,
						footerkanan_3_fontstyle = :footerkanan_3_fontstyle,
						footerkanan_4_separator = :footerkanan_4_separator,
						footerkanan_5_teks = :footerkanan_5_teks,
						footerkanan_5_fontstyle = :footerkanan_5_fontstyle,
						footerkanan_6_teks = :footerkanan_6_teks,
						footerkanan_6_fontstyle = :footerkanan_6_fontstyle';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':footerkiri_1_teks', $request->footerkiri_1_teks);
			$stmt->bindValue(':footerkiri_1_fontstyle', $request->footerkiri_1_fontstyle);
			$stmt->bindValue(':footerkiri_2_teks', $request->footerkiri_2_teks);
			$stmt->bindValue(':footerkiri_2_fontstyle', $request->footerkiri_2_fontstyle);
			$stmt->bindValue(':footerkiri_3_teks', $request->footerkiri_3_teks);
			$stmt->bindValue(':footerkiri_3_fontstyle', $request->footerkiri_3_fontstyle);
			$stmt->bindValue(':footerkiri_4_separator', $request->footerkiri_4_separator);
			$stmt->bindValue(':footerkiri_5_teks', $request->footerkiri_5_teks);
			$stmt->bindValue(':footerkiri_5_fontstyle', $request->footerkiri_5_fontstyle);
			$stmt->bindValue(':footerkiri_6_teks', $request->footerkiri_6_teks);
			$stmt->bindValue(':footerkiri_6_fontstyle', $request->footerkiri_6_fontstyle);
			$stmt->bindValue(':footerkiri_5_teks', $request->footerkiri_5_teks);
			$stmt->bindValue(':footerkiri_5_fontstyle', $request->footerkiri_5_fontstyle);
			$stmt->bindValue(':footertengah_1_teks', $request->footertengah_1_teks);
			$stmt->bindValue(':footertengah_1_fontstyle', $request->footertengah_1_fontstyle);
			$stmt->bindValue(':footertengah_2_teks', $request->footertengah_2_teks);
			$stmt->bindValue(':footertengah_2_fontstyle', $request->footertengah_2_fontstyle);
			$stmt->bindValue(':footertengah_3_teks', $request->footertengah_3_teks);
			$stmt->bindValue(':footertengah_3_fontstyle', $request->footertengah_3_fontstyle);
			$stmt->bindValue(':footertengah_4_separator', $request->footertengah_4_separator);
			$stmt->bindValue(':footertengah_5_teks', $request->footertengah_5_teks);
			$stmt->bindValue(':footertengah_5_fontstyle', $request->footertengah_5_fontstyle);
			$stmt->bindValue(':footertengah_6_teks', $request->footertengah_6_teks);
			$stmt->bindValue(':footertengah_6_fontstyle', $request->footertengah_6_fontstyle);
			$stmt->bindValue(':footerkanan_1_teks', $request->footerkanan_1_teks);
			$stmt->bindValue(':footerkanan_1_fontstyle', $request->footerkanan_1_fontstyle);
			$stmt->bindValue(':footerkanan_2_teks', $request->footerkanan_2_teks);
			$stmt->bindValue(':footerkanan_2_fontstyle', $request->footerkanan_2_fontstyle);
			$stmt->bindValue(':footerkanan_3_teks', $request->footerkanan_3_teks);
			$stmt->bindValue(':footerkanan_3_fontstyle', $request->footerkanan_3_fontstyle);
			$stmt->bindValue(':footerkanan_4_separator', $request->footerkanan_4_separator);
			$stmt->bindValue(':footerkanan_5_teks', $request->footerkanan_5_teks);
			$stmt->bindValue(':footerkanan_5_fontstyle', $request->footerkanan_5_fontstyle);
			$stmt->bindValue(':footerkanan_6_teks', $request->footerkanan_6_teks);
			$stmt->bindValue(':footerkanan_6_fontstyle', $request->footerkanan_6_fontstyle);
			$stmt->execute();
		}else if($request->dari == 'others'){
			$sql = 'UPDATE parameterekspor SET gunakanpwd = :gunakanpwd, pwd = :pwd';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':gunakanpwd', $request->gunakanpwd);
			$stmt->bindValue(':pwd', $request->pwd);
			$stmt->execute();
		}

		Utils::insertLogUser('Ubah parameter ekspor');

		return redirect('pengaturan/parameterekspor')->with('message', trans('all.databerhasildisimpan'));
	}
	
	// untuk menu set ulang kata sandi pegawai
	public function setulangkatasandipegawai()
	{
        if(Utils::cekHakakses('setulangkatasandipegawai','l')){
			$atributs = Utils::getAtribut();
			Utils::insertLogUser('akses menu set ulang kata sandi pegawai');
			return view('datainduk/lainlain/setulangkatasandipegawai/index', ['atributs' => $atributs,  'menu' => 'setulangkatasandipegawai']);
		}else{
			return redirect('/');
		}
	}

	public function submitsetulangkatasandipegawai(Request $request)
	{
		$hanyablmadapwd = ($request->hanyablmadapwd == '' ? 't' : 'y');
		$semuapegawai = ($request->semuapegawai == '' ? 't' : 'y');

		$pdo_p = DB::getPdo();
		$sql = 'SELECT
	    			nama as perusahaan_nama,
	    			kode as perusahaan_kode
	    		FROM
	    			perusahaan
	    		WHERE
	    			id=:idperusahaan
	    		LIMIT 1';
	    $stmt = $pdo_p->prepare($sql);
	    $stmt->bindValue('idperusahaan', Session::get('conf_webperusahaan'));
	    $stmt->execute();
	    
	    if ($stmt->rowCount()>0) {
	    	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	    	$perusahaan_nama = $row['perusahaan_nama'];
	    	$perusahaan_kode = $row['perusahaan_kode'];

			$whereBlmAdaPwd = '';
			if ($hanyablmadapwd=='y') {
				$whereBlmAdaPwd = ' AND (password="" OR ISNULL(password)=true)';
			}

			$whereAtributNilai = '';
			if ($semuapegawai=='t') {
				$atributnilai = array();
				if (isset($request->atributnilai) && is_array($request->atributnilai)) {
					$atributnilai = $request->atributnilai;
					if (count($atributnilai)>0) {
						$set_idatributnilai = implode(',',$atributnilai);
						$whereAtributNilai = ' AND id IN (SELECT DISTINCT(idpegawai) FROM pegawaiatribut WHERE idatributnilai IN ('.$set_idatributnilai.'))';
					}
				}
			}
        
            $pdo = DB::connection('perusahaan_db')->getPdo();
			$sql = 'SELECT id, nama, pin, nomorhp FROM pegawai WHERE status="a" AND del = "t" '.$whereBlmAdaPwd.$whereAtributNilai;
		    $stmt = $pdo->prepare($sql);
		    $stmt->execute();
		    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$idpegawai = $row['id'];
				$pegawai_nama = $row['nama'];
		    	$pegawai_pin = $row['pin'];
		    	$pegawai_nomorhp = $row['nomorhp'];			

			    if ($pegawai_nomorhp!='') {	 
			    	$password = Utils::generateRandomAngka(4);  
			    	$pwd_bcrypt = Hash::make($password);
					$sql2 = 'UPDATE pegawai SET password=:password WHERE id=:idpegawai';
					$stmt2 = $pdo->prepare($sql2);
					$stmt2->bindValue('password', $pwd_bcrypt);
					$stmt2->bindValue('idpegawai', $idpegawai);
					$stmt2->execute();

					$sql3 = 'INSERT INTO authtokenblacklist_pegawai SELECT idpegawai, idtoken, expired, NOW() FROM authtoken_pegawai WHERE idpegawai=:idpegawai ON DUPLICATE KEY UPDATE authtokenblacklist_pegawai.expired=GREATEST(authtokenblacklist_pegawai.expired, authtoken_pegawai.expired);';
					$stmt3 = $pdo->prepare($sql3);
					$stmt3->bindValue('idpegawai', $idpegawai);
					$stmt3->execute();

                    Utils::deleteData($pdo,'authtoken_pegawai',$idpegawai,'idpegawai');

					$isi = $perusahaan_nama.chr(13).chr(10).
						   $pegawai_nama.chr(13).chr(10).
						   'USERNAME: '.$perusahaan_kode.$pegawai_pin.chr(13).chr(10).
						   'PASSWORD: '.$password;
					// masukkan ke dalam antrean kirimsms
					$sql3 = 'INSERT INTO _kirimsms VALUES(0, :idperusahaan, :tujuan, LEFT(:isi,159), NOW())';
					$stmt3 = $pdo_p->prepare($sql3);
					$stmt3->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
					$stmt3->bindValue(':tujuan', $pegawai_nomorhp);
					$stmt3->bindValue(':isi', $isi);
					$stmt3->execute();
				}
			}

			Utils::insertLogUser('Setulang katasandi pegawai');

			return redirect('datainduk/lainlain/setulangkatasandipegawai')->with('message', trans('all.katasandipegawaisudahdisetulang'));
		}else{
			return redirect('datainduk/lainlain/setulangkatasandipegawai')->with('message', trans('all.perusahaanbelumdipilih'));
		}
	}

	public function ajakan($ajakan)
	{
		$pdo = DB::getPdo();
		$arr = array('mengajak','diajak','blokir');
		$datas = '';
		$perusahaans = '';
		if (in_array($ajakan,$arr)) {
			if($ajakan == 'diajak'){
                $sql = 'SELECT 
                            a.id,
                            u.id as iduser,
                            u.nama,
                            p.id as idperusahaan,
                            p.nama as perusahaan,
                            h.nama as hakakses
                        FROM 
                            ajakan a,
                            `user` u,
                            perusahaan p,
                            hakakses h
                        WHERE 
                            a.iduserdari=u.id AND
                            a.idperusahaan=p.id AND
                            a.idhakakses=h.id AND
                            a.status="c" AND
                            a.iduserke=:id
                        ORDER BY
                            a.inserted DESC
                        ';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
                $stmt->execute();
                if ($stmt->rowCount()>0) {
                    $datas = $stmt->fetchAll(PDO::FETCH_OBJ);
                }
			}else if($ajakan == 'mengajak'){
				// perusahaan
				$sql = 'SELECT p.id,p.nama FROM perusahaan p, pengelola pn WHERE p.id=pn.idperusahaan AND pn.iduser = :iduser ORDER BY p.nama';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
				$stmt->execute();
				if ($stmt->rowCount()>0) {
					$perusahaans = $stmt->fetchAll(PDO::FETCH_OBJ);

					foreach ($perusahaans as $row) {
			            // ambil data atributnilai
			            $sql = 'SELECT 
			                        h.id,
			                        h.nama
			                    FROM 
			                        hakakses h,
			                        perusahaan p
			                    WHERE 
			                       h.idperusahaan=p.id AND
			                       p.id = :idperusahaan
			                    ORDER BY
			                        h.nama';
			            $stmt2 = $pdo->prepare($sql);
			            $stmt2->bindValue(':idperusahaan', $row->id);
			            $stmt2->execute();            

			            $row->hakakses=$stmt2->fetchAll(PDO::FETCH_OBJ);
			        }
				}

				$sql = 'SELECT 
							a.id,
							u.id as iduser,
							u.nama,
							p.id as idperusahaan,
							p.nama as perusahaan,
							h.nama as hakakses
				        FROM 
				        	ajakan a,
				         	user u,
				         	perusahaan p,
				         	hakakses h
				        WHERE 
				        	a.iduserke=u.id AND
				        	a.idperusahaan=p.id AND
				        	a.idhakakses=h.id AND
				        	a.status="c" AND
				         	a.iduserdari = :id
				        ORDER BY
				        	a.inserted DESC
				        ';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':id', Session::get('iduser_perusahaan'));
				$stmt->execute();
				if ($stmt->rowCount()>0) {
					$datas = $stmt->fetchAll(PDO::FETCH_OBJ);
				}
			}else if($ajakan == 'blokir'){
				$sql = 'SELECT 
							at.id,
							at.iduserdari,
							u.email,
							u.nama
				        FROM 
				        	ajakantolak at,
				        	user u
				        WHERE 
				        	at.iduserdari=u.id AND
				         	at.iduser=:id
				        ORDER BY
				        	u.email ASC
				        ';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':id', Session::get('iduser_perusahaan'));
				$stmt->execute();
				if ($stmt->rowCount()>0) {
					$datas = $stmt->fetchAll(PDO::FETCH_OBJ);
				}
			}

			$adaperusahaan = '';
			if(Session::has('conf_webperusahaan')){
			    $adaperusahaan = 'ada';
            }
			Utils::insertLogUser('akses menu ajakan '. $ajakan);
		    return view('ajakan', ['ajakan' => $ajakan, 'datas' => $datas, 'adaperusahaan' => $adaperusahaan, 'perusahaans' => $perusahaans, 'menu' => 'ajakan']);
		}else{
			abort(404);
		}
	}

	public function submitajakan(Request $request, $ajakan)
	{
		$pdo = DB::getPdo();
		$email = $request->email;

		$arr = array('mengajak','diajak','blokir');
		if (in_array($ajakan,$arr)) {
			if($ajakan == 'mengajak'){
				$perusahaan = $request->perusahaan;
				$hakakses = $request->hakakses;
				// cek apakah email tujuan ada?
			    $sql = 'SELECT id FROM user WHERE email=:email AND status="a" LIMIT 1';
			    $stmt = $pdo->prepare($sql);
			    $stmt->bindValue(':email', $email);
			    $stmt->execute();
			    if ($stmt->rowCount()>0) {
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$iduserke = $row['id'];

					// cek diblok atau tidak?
				    $sql = 'SELECT id FROM ajakantolak WHERE iduser=:iduser AND iduserdari=:iduserdari LIMIT 1';
				    $stmt = $pdo->prepare($sql);
				    $stmt->bindValue(':iduser', $iduserke);
				    $stmt->bindValue(':iduserdari', Session::get('iduser_perusahaan'));
				    $stmt->execute();
				    if ($stmt->rowCount()==0) {
						// cek apakah iduserke tidak berada pada perusahaan yg sama?
					    $sql = 'SELECT id FROM pengelola WHERE iduser=:iduserke AND idperusahaan=:idperusahaan LIMIT 1';
					    $stmt = $pdo->prepare($sql);
					    $stmt->bindValue(':iduserke', $iduserke);
					    $stmt->bindValue(':idperusahaan', $perusahaan);
					    $stmt->execute();
					    if ($stmt->rowCount()==0) {
							// cek apakah idhakakses benar
						    $sql = 'SELECT id FROM hakakses WHERE id=:idhakakses AND idperusahaan=:idperusahaan LIMIT 1';
						    $stmt = $pdo->prepare($sql);
						    $stmt->bindValue(':idhakakses', $hakakses);
						    $stmt->bindValue(':idperusahaan', $perusahaan);
						    $stmt->execute();
						    if ($stmt->rowCount()!=0) {
								// cek apakah iduserke sudah ada di table ajakan
							    $sql = 'SELECT id FROM ajakan WHERE iduserdari=:iduserdari AND iduserke=:iduserke AND idperusahaan=:idperusahaan LIMIT 1';
							    $stmt = $pdo->prepare($sql);
							    $stmt->bindValue(':iduserdari', Session::get('iduser_perusahaan'));
							    $stmt->bindValue(':iduserke', $iduserke);
							    $stmt->bindValue(':idperusahaan', $perusahaan);
							    $stmt->execute();
							    if ($stmt->rowCount()==0) {
									$sql = 'INSERT INTO ajakan VALUES(0, :iduserdari, :iduserke, :idperusahaan, :idhakakses, "c", NOW())';
									$stmt = $pdo->prepare($sql);
									$stmt->bindValue(':iduserdari', Session::get('iduser_perusahaan'));
									$stmt->bindValue(':iduserke', $iduserke);
									$stmt->bindValue(':idperusahaan', $perusahaan);
									$stmt->bindValue(':idhakakses', $hakakses);
									$stmt->execute();

									Utils::insertLogUser('Mengajak "'.$email.'"');

									return redirect('ajakan/'.$ajakan)->with('message', trans('all.ajakanberhasildikirim'));
							    }
							    else {
									return redirect('ajakan/'.$ajakan)->with('message', trans('all.ajakansudahpernahdilakukan'));
							    }
						    }
						    else {
								return redirect('ajakan/'.$ajakan)->with('message', trans('all.hakaksestidakditemukan'));
						    }
					    }
					    else {
							return redirect('ajakan/'.$ajakan)->with('message', trans('all.usertujuansudahberadadalamsatuperusahaanyangsama'));
					    }
				    }
				    else {
						return redirect('ajakan/'.$ajakan)->with('message', trans('all.ajakangagal'));
				    }		    
				}
				else {
					return redirect('ajakan/'.$ajakan)->with('message', trans('all.emailtidakditemukan'));
				}
			}else if($ajakan == 'blokir'){
				
				$email = $request->email;
				$pdo = DB::getPdo();
				// cek apakah email tujuan ada?
			    $sql = 'SELECT id FROM user WHERE email=:email AND status="a" AND id<>:iduser LIMIT 1';
			    $stmt = $pdo->prepare($sql);
			    $stmt->bindValue(':email', $email);
			    $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
			    $stmt->execute();
			    if ($stmt->rowCount()>0) {
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$iduserdari = $row['id'];

				    $sql = 'SELECT id FROM ajakantolak WHERE iduserdari=:iduserdari LIMIT 1';
				    $stmt = $pdo->prepare($sql);
				    $stmt->bindValue(':iduserdari', $iduserdari);
				    $stmt->execute();
				    if ($stmt->rowCount()==0) {
						$sql = 'INSERT INTO ajakantolak VALUES(0, :iduser, :iduserdari, NOW())';
						$stmt = $pdo->prepare($sql);
						$stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
						$stmt->bindValue(':iduserdari', $iduserdari);
						$stmt->execute();

						Utils::insertLogUser('Blokir "'.$email.'"');

						return redirect('ajakan/'.$ajakan)->with('message', trans('all.blokirsukses'));
					}
					else {
						return redirect('ajakan/'.$ajakan)->with('message', trans('all.datasudahada'));
					}
				}
				else {
					return redirect('ajakan/'.$ajakan)->with('message', trans('all.emailtidakditemukan'));
				}
			}
		}
	}

	public function hapusajakan($ajakan, $id){
		$pdo = DB::getPdo();
		$arr = array('mengajak','diajak','blokir');
		if (in_array($ajakan,$arr)) {
			if($ajakan == 'mengajak'){
				$sql = 'SELECT id FROM ajakan WHERE id=:id LIMIT 1';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':id', $id);
				$stmt->execute();
				if ($stmt->rowCount()>0) {
                    Utils::deleteData($pdo,'ajakan',$id);
					Utils::insertLogUser('Hapus ajakan');
			    	return redirect('ajakan/'.$ajakan)->with('message', trans('all.databerhasildihapus'));
			    }else{
			    	return redirect('ajakan/'.$ajakan)->with('message', trans('all.datatidakditemukan'));
			    }
			}else if($ajakan == 'blokir'){
				$sql = 'SELECT id FROM ajakantolak WHERE id=:idajakantolak LIMIT 1';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':idajakantolak', $id);
				$stmt->execute();
				if ($stmt->rowCount()>0) {
                    Utils::deleteData($pdo,'ajakantolak',$id);
					Utils::insertLogUser('Hapus blokir');
					return redirect('ajakan/'.$ajakan)->with('message', trans('all.databerhasildihapus'));
				}else {
					return redirect('ajakan/'.$ajakan)->with('message', trans('all.datatidakditemukan'));
				}
			}
		}
	}

	public function manipulasiajakan($ajakan,$id,$param)
	{
		$pdo = DB::getPdo();
		$arr = array('mengajak','diajak','blokir');
		if (in_array($ajakan,$arr)) {
            $sql = 'SELECT 
                            a.idperusahaan,
                            p.nama as namaperusahaan,
                            a.idhakakses,
                            u.nama as namauserke,
                            a.iduserdari
                        FROM 
                            ajakan a,
                            perusahaan p,
                            `user` u
                        WHERE 
                            a.idperusahaan=p.id AND
                            a.id=:idajakan AND 
                            a.iduserke=:iduserke AND
                            a.iduserke=u.id AND
                            a.status="c" 
                        LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idajakan', $id);
            $stmt->bindValue(':iduserke', Session::get('iduser_perusahaan'));
            $stmt->execute();
            if ($stmt->rowCount()>0) {
			    if($ajakan == 'diajak'){

					$row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $idperusahaan = $row['idperusahaan'];
                    $namaperusahaan = $row['namaperusahaan'];
                    $idhakakses = $row['idhakakses'];
                    $namauserke = $row['namauserke'];
                    $iduserdari = $row['iduserdari'];
                    $msg = $namauserke." telah bergabung dengan ".$namaperusahaan;

					if ($param == 'terima') {
						// ajakan diterima
						$sql = 'INSERT IGNORE INTO pengelola VALUES(NULL, :iduser, :idperusahaan, :idhakakses, NOW())';
						$stmt = $pdo->prepare($sql);
						$stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
						$stmt->bindValue(':idperusahaan', $idperusahaan);
						$stmt->bindValue(':idhakakses', $idhakakses);
						$stmt->execute();

                        Utils::deleteData($pdo,'ajakan',$id);
						Utils::insertLogUser($msg);
                        Utils::simpanUserKotakPesan($iduserdari,$msg);

						return redirect('ajakan/'.$ajakan)->with('message', trans('all.ajakanditerima'));
					}
					else{
						// ajakan ditolak
						$sql = 'UPDATE ajakan SET status="d" WHERE id=:idajakan';
						$stmt = $pdo->prepare($sql);
						$stmt->bindValue(':idajakan', $id);
						$stmt->execute();

						Utils::insertLogUser('Tolak ajakan');

						return redirect('ajakan/'.$ajakan)->with('message', trans('all.ajakanditolak'));
					}
				}
			} else {
                return redirect('ajakan/'.$ajakan)->with('message', trans('all.ajakantidakditemukan'));
            }
		}
	}

	public function postingdata()
	{
        $pdo = DB::connection('perusahaan_db')->getPdo();
	    $atributs = Utils::getAtribut();
        $sql = 'SELECT id,nama FROM jamkerja';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $jamkerja= $stmt->fetchAll(PDO::FETCH_OBJ);
		$valuetglawalakhir = Utils::valueTanggalAwalAkhir();
		Utils::insertLogUser('akses menu posting data');
		return view('datainduk/lainlain/postingdata/index', ['valuetglawalakhir' => $valuetglawalakhir, 'atributs' => $atributs, 'jamkerja' => $jamkerja, 'menu' => 'postingdata']);
	}

	public function submitpostingdata(Request $request)
	{
        $response = array();
        if(!Utils::cekDateTime($request->tanggalawal_separatorminus)){
            $response['status'] = 'error';
            $response['msg'] = 'invalid format date';
            $response['warna'] = '';
            return $response;
        }
        if(!Utils::cekDateTime($request->tanggalakhir_separatorminus)){
            $response['status'] = 'error';
            $response['msg'] = 'invalid format date';
            $response['warna'] = '';
            return $response;
        }

		$pdo = DB::connection('perusahaan_db')->getPdo();
		$response['status'] = 'error';
		$response['msg'] = Utils::cekKunciDataPosting($request->tanggalawal_separatorminus . ' 00:00:00');
		$response['warna'] = '';
        $cekbolehubah = Utils::cekKunciDataPosting($request->tanggalawal_separatorminus . ' 00:00:00');
        if($cekbolehubah == 0) {
            $atributnilai = $request->atributnilai == '' ? NULL : Utils::atributNilai($request->atributnilai);
            $jamkerja = $request->jamkerja == '' ? NULL : Utils::jamKerja($request->jamkerja);
            try {
                if ($request->jenisposting == 'semuapegawai') {
                    if ($atributnilai == NULL && $jamkerja == NULL) {
                        $sql = 'CALL hitungrekapabsen_pertanggal(:tanggalawal, :tanggalakhir)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':tanggalawal', $request->tanggalawal_separatorminus);
                        $stmt->bindValue(':tanggalakhir', $request->tanggalakhir_separatorminus);
                        $stmt->execute();
                    } else {
                        $sql = 'CALL hitungrekapabsen_pertanggal_pakaifilter(:tanggalawal,:tanggalakhir, :atribut, :jamkerja)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':tanggalawal', $request->tanggalawal_separatorminus);
                        $stmt->bindValue(':tanggalakhir', $request->tanggalakhir_separatorminus);
                        $stmt->bindValue(':atribut', $atributnilai);
                        $stmt->bindValue(':jamkerja', $jamkerja);
                        $stmt->execute();
                    }

                    Utils::insertLogUser(trans('all.postingdata') . ' : ' . Utils::tanggalCantikDariSampai($request->tanggalawal_separatorminus, $request->tanggalakhir_separatorminus));

                    $response['status'] = 'OK';
                    $response['msg'] = trans('all.postingdata') . ' : ' . Utils::tanggalCantikDariSampai($request->tanggalawal_separatorminus, $request->tanggalakhir_separatorminus);
                    $response['warna'] = '#1ab394';

                } else {
                    $idpegawai = $request->pegawai;
                    $sql = 'CALL hitungrekapabsen_perpegawai_pertanggal(:idpegawai,:tanggalawal,:tanggalakhir)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->bindValue(':tanggalawal', $request->tanggalawal_separatorminus);
                    $stmt->bindValue(':tanggalakhir', $request->tanggalakhir_separatorminus);
                    $stmt->execute();

                    Utils::insertLogUser(trans('all.postingdata') . ' : ' . Utils::tanggalCantikDariSampai($request->tanggalawal_separatorminus, $request->tanggalakhir_separatorminus) . ' ' . Utils::getNamaPegawai($idpegawai));

                    $response['status'] = 'OK';
                    $response['msg'] = trans('all.postingdata') . ' : ' . Utils::tanggalCantikDariSampai($request->tanggalawal_separatorminus, $request->tanggalakhir_separatorminus) . ' ' . Utils::getNamaPegawai($idpegawai);
                    $response['warna'] = '#1ab394';
                }

            } catch (\Exception $e) {
                $response['status'] = 'error';
                $response['msg'] = $e->getMessage();
                $response['warna'] = '#ed5565';
            }
        } else {
            $response['status'] = 'error';
            $response['msg'] = trans('all.datatidakbisadirubah').Utils::getDataWhere($pdo,'pengaturan','kuncidatasebelumtanggal');
            $response['warna'] = '#ed5565';
        }
		return $response;
	}

	//menu sampel wajah(datainduk/pegawai/facesample)
	public function facesample()
    {
        if(Utils::cekHakakses('facesample','l')){
			Utils::insertLogUser('akses menu face sample');
			return $this->dataFacesample();
		}else{
			return redirect('/');
		}
    }

    //fungsi ini di pake 2 form, yaitu form filter dan form pencarian (pencarian hanya nama pegawai saja)
    public function submitFacesample(Request $request)
    {
        //untuk menangani apabila filter atribut tidak ada yg dipilih/dicentang
        if($request->submitdari == 'filter'){
            if(Session::has('facesample_atributfilter')){
                Session::forget('facesample_atributfilter');
            }
        }

        //filter atribut
        if(isset($request->atributfilter)) {
            Session::set('facesample_atributfilter', '');
            if(count($request->atributfilter) > 0) {
                Session::set('facesample_atributfilter', $request->atributfilter);
            }else{
                Session::forget('facesample_atributfilter');
            }
        }

        //filter jenis
        if(isset($request->jenisfilter)){
            Session::set('facesample_jenisfilter', '');
            if($request->jenisfilter != ''){
                Session::set('facesample_jenisfilter', $request->jenisfilter);
            }else{
                Session::forget('facesample_jenisfilter');
            }
        }

        //pencarian
        if(isset($request->pencarian)) {
            Session::set('facesample_pencarian','');
            if($request->pencarian != ''){
                Session::set('facesample_pencarian', $request->pencarian);
            }else{
                Session::forget('facesample_pencarian');
            }
        }

        return $this->dataFacesample();
    }

    public function dataFacesample(){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        $filter = '';
        //batasan atribut untuk user
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan != '') {
            $where .= ' AND p.id IN ' . $batasan;
        }

        if(Session::has('facesample_atributfilter')) {
            $atributfilter = Session::get('facesample_atributfilter');
            $filter .= '<b>'.trans('all.filter').'</b> ';
            for ($i = 0; $i < count($atributfilter); $i++) {
                $filter .= $this->getAtributNilai($atributfilter[$i]).', ';
            }
            $filter = substr($filter, 0, -2);
            $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . Utils::atributNilai($atributfilter) . ') )';
        }

        if(Session::has('facesample_jenisfilter')){
            $jenisfilter = Session::get('facesample_jenisfilter') == 'punyafacesample' ? ' IN ' : ' NOT IN ';
            $where .= ' AND p.id '.$jenisfilter.'(select idpegawai from facesample)';
            $filter .=  ($filter != '' ? '<br>' : '').' <b>'.trans('all.berdasarkan').'</b> '.(Session::get('facesample_jenisfilter') == 'punyafacesample' ? trans('all.sudahsampel') : trans('all.belumsampel'));
        }

        if(Session::has('facesample_pencarian')){
            $where .= ' AND p.nama LIKE"%'.Session::get('facesample_pencarian').'%"';
        }

//        start from salah
//        CONCAT(REPLACE(TRIM(p.nama)," ","_"),"_",p.id) as startfrom

        //query inti
        $sql = 'SELECT
                    p.id as idpegawai,
                    f.id as idfacesample,
                    CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
                    p.nama as namalengkap,
                    p.checksum_img,
                    f.filename,
                    f.checksum,
                    count(f.checksum) as totalfacesample,
                    p.id as startfrom
                FROM
                    pegawai p 
                    LEFT JOIN facesample f ON f.idpegawai=p.id
                WHERE
                    p.del = "t"
                    '.$where.'
                GROUP BY
                    p.id
                ORDER BY
                    p.nama ASC
                LIMIT '.config('consts.LIMIT_FOTO');
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchALL(PDO::FETCH_OBJ);
        $totaldata = $this->getTotalFaceSample('');

        //untuk filter
        $dataatribut = Utils::getAtribut();
        //dapatkan data terakhir startfrom
        $laststartfrom = Utils::getLastDataFromArray($data,'startfrom');

        return view('datainduk/pegawai/facesample/index', ['data' => $data, 'dataatribut' => $dataatribut,  'totaldata' => $totaldata, 'filter' => $filter, 'startfrom' => $laststartfrom, 'menu' => 'facesample']);
    }
    
    public function loadmoreFacesample($startfrom)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        $startfrom_nama='';

        if ($startfrom!='') {
            $sql = 'SELECT nama FROM pegawai WHERE del="t" AND id=:startfrom LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':startfrom', $startfrom);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $startfrom_nama = $row['nama'];
                $where .= ' AND p.nama > :startfrom_nama ';
            }
        }

        //batasan atribut untuk user
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan != '') {
            $where .= ' AND p.id IN ' . $batasan;
        }

        if(Session::has('facesample_atributfilter')) {
            $atributfilter = Session::get('facesample_atributfilter');
            $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . Utils::atributNilai($atributfilter) . ') )';
        }

        if(Session::has('facesample_jenisfilter')){
            $jenisfilter = Session::get('facesample_jenisfilter') == 'punyafacesample' ? ' IN ' : ' NOT IN ';
            $where .= ' AND p.id '.$jenisfilter.'(select idpegawai from facesample)';
        }

        if(Session::has('facesample_pencarian')){
            $where .= ' AND p.nama LIKE"%'.Session::get('facesample_pencarian').'%"';
        }
        
        $sql = 'SELECT
                    p.id as idpegawai,
                    f.id as idfacesample,
                    CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
                    p.nama as namalengkap,
                    p.checksum_img,
                    f.filename,
                    f.checksum,
                    count(f.checksum) as totalfacesample,
                    p.id as startfrom
                FROM
                    pegawai p 
                    LEFT JOIN facesample f ON f.idpegawai=p.id
                WHERE
                    p.del = "t"
                    '.$where.'
                GROUP BY
                    p.id
                ORDER BY
                    p.nama ASC
                LIMIT '.config('consts.LIMIT_FOTO');
        $stmt = $pdo->prepare($sql);
        if ($startfrom_nama!='') {
            $stmt->bindParam(':startfrom_nama', $startfrom_nama);
        }
        $stmt->execute();
        $data = $stmt->fetchALL(PDO::FETCH_OBJ);
        $totaldata = $this->getTotalFaceSample($startfrom);

        //dapatkan data terakhir startfrom
        $laststartfrom = Utils::getLastDataFromArray($data,'startfrom');

        return view('datainduk/pegawai/facesample/loadmore', ['data' => $data, 'totaldata' => $totaldata, 'startfrom' => $laststartfrom]);
    }
    
    public function getTotalFaceSample($startfrom)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        //batasan atribut untuk user
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan != '') {
            $where .= ' AND id IN ' . $batasan;
        }

        if(Session::has('facesample_atributfilter')) {
            $atributfilter = Session::get('facesample_atributfilter');
            $where .= ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . Utils::atributNilai($atributfilter) . ') )';
        }

        if(Session::has('facesample_jenisfilter')){
            $jenisfilter = Session::get('facesample_jenisfilter') == 'punyafacesample' ? ' IN ' : ' NOT IN ';
            $where .= ' AND id '.$jenisfilter.'(select idpegawai from facesample)';
        }

        if(Session::has('facesample_pencarian')){
            $where .= ' AND nama LIKE"%'.Session::get('facesample_pencarian').'%"';
        }
        
        if($startfrom != ''){
            $sql = 'SELECT nama FROM pegawai WHERE del="t" AND id=:startfrom LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':startfrom', $startfrom);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $where .= ' AND nama > "'.$row['nama'].'"';
            }
//            $sql = 'SELECT id FROM pegawai WHERE CONCAT(REPLACE(nama," ","_"),"_",id) > :startfrom AND del = "t" '.$where;
            $sql = 'SELECT id FROM pegawai WHERE del = "t" '.$where;
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':startfrom', $startfrom);
        }else{
            $sql = 'SELECT id FROM pegawai WHERE del = "t" '.$where;
			$stmt = $pdo->prepare($sql);
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function excelFacesample(){
        if(Utils::cekHakakses('facesample','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            //set css kolom
            $styleArray = array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
            );

            Utils::setPropertiesExcel($objPHPExcel,trans('all.facesample'));

            // atribut unutk header
            $allatribut = Utils::getAllAtribut('blade');
            $atributpenting_controller = ($allatribut['atributpenting_controller'] != '' ? explode('|', $allatribut['atributpenting_controller']) : '');
            $atributpenting_blade = explode('|', $allatribut['atributpenting_blade']);
            $atributvariablepenting_controller = ($allatribut['atributvariablepenting_controller'] != '' ? explode('|', $allatribut['atributvariablepenting_controller']) : '');
            $atributvariablepenting_blade = explode('|', $allatribut['atributvariablepenting_blade']);
            $totalatributvariable = ($atributvariablepenting_controller != '' ? count($atributvariablepenting_controller) : 0);
            $totalatributpenting = ($atributpenting_controller != '' ? count($atributpenting_controller) : 0);

            //set atribut variable
            $ih = 3; // setelah kolom pin(B)
            if ($atributvariablepenting_blade != '') {
                //looping untuk header
                foreach ($atributvariablepenting_blade as $key) {
                    if ($key != '') {
                        $hh = Utils::angkaToHuruf($ih);
                        $objPHPExcel->getActiveSheet()->setCellValue($hh . 1, $key);
                        //lebar kolom
                        $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                        //set bold
                        $objPHPExcel->getActiveSheet()->getStyle($hh . 1)->getFont()->setBold(true);
                        //style
                        $objPHPExcel->getActiveSheet()->getStyle($hh . 1)->applyFromArray($styleArray);

                        $ih++;
                    }
                }
            }

            //set atribut penting
            if ($atributpenting_blade != '') {
                //looping untuk header
                foreach ($atributpenting_blade as $key) {
                    if ($key != '') {
                        $hi = $ih;
                        $hh = Utils::angkaToHuruf($hi);
                        $objPHPExcel->getActiveSheet()->setCellValue($hh . 1, $key);
                        //lebar kolom
                        $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                        //set bold
                        $objPHPExcel->getActiveSheet()->getStyle($hh . 1)->getFont()->setBold(true);
                        //style
                        $objPHPExcel->getActiveSheet()->getStyle($hh . 1)->applyFromArray($styleArray);
                        $ih++;
                    }
                }
            }

            $h1 = Utils::angkaToHuruf($ih);
            $h2 = Utils::angkaToHuruf($ih + 1);

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.pin'))
                        ->setCellValue($h1.'1', trans('all.facesample'))
                        ->setCellValue($h2.'1', trans('all.jumlahfacesample'));

            $where = '';
            //batasan atribut untuk user
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $where .= ' AND p.id IN ' . $batasan;
            }

            if(Session::has('facesample_atributfilter')) {
                $atributfilter = Session::get('facesample_atributfilter');
                $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . Utils::atributNilai($atributfilter) . ') )';
            }

            if(Session::has('facesample_jenisfilter')){
                $jenisfilter = Session::get('facesample_jenisfilter') == 'punyafacesample' ? ' IN ' : ' NOT IN ';
                $where .= ' AND p.id '.$jenisfilter.'(select idpegawai from facesample)';
            }

            if(Session::has('facesample_pencarian')){
                $where .= ' AND p.nama LIKE"%'.Session::get('facesample_pencarian').'%"';
            }

            //set atribut untuk query
            $whereAtribut = '';
            if ($batasan != '') {
                $whereAtribut = ' AND id IN ' . $batasan;
            }
            $allatribut_controller = Utils::getAllAtribut('controller', $whereAtribut);
            $atributvariablepenting = $allatribut_controller['atributvariablepenting'];
            $atributpenting = $allatribut_controller['atributpenting'];

//            $sql = 'SELECT
//                        p.nama,
//                        p.pin,
//                        GROUP_CONCAT(DISTINCT an.nilai separator ", ") as atributnilai,
//                        IF(ISNULL(f.id) = true,"0","1" ) as facesample
//                    FROM
//                        pegawai p
//                        LEFT JOIN facesample f ON f.idpegawai=p.id,
//                        pegawaiatribut pa,
//                        atributnilai an
//                    WHERE
//                        pa.idpegawai=p.id AND
//                        pa.idatributnilai=an.id
//                        '.$where.'
//                    GROUP BY
//                        p.id
//                    ORDER BY
//                        p.nama ASC, p.id ASC';
//            $sql = 'SELECT
//                        p.nama,
//                        p.pin,
//                        GROUP_CONCAT(DISTINCT an.nilai separator ", ") as atributnilai,
//                        IF(ISNULL(f.id) = true,"0","1" ) as facesample
//                    FROM
//                        pegawai p
//                        LEFT JOIN facesample f ON f.idpegawai=p.id
//                        LEFT JOIN pegawaiatribut pa ON pa.idpegawai=p.id
//                        LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
//                    WHERE
//                        p.del = "t"
//                        '.$where.'
//                    GROUP BY
//                        p.id
//                    ORDER BY
//                        p.nama ASC';
            $sql = 'SELECT
                        p.nama,
                        p.pin,
                        '.$atributvariablepenting.'
                        -- IFNULL(x.atributnilai,"") as atributnilai,
                        IF(count(f.checksum) > 0,"1","0") as facesample,
                        COUNT(f.checksum) as jumlahfacesample
                        '.$atributpenting.'
                    FROM
                        pegawai p 
                        LEFT JOIN facesample f ON f.idpegawai=p.id
                        LEFT JOIN (
                            SELECT
                                pa.idpegawai,
                                GROUP_CONCAT(DISTINCT an.nilai separator ", ") as atributnilai
                            FROM
                                pegawaiatribut pa,
                                atributnilai an
                            WHERE
                                pa.idatributnilai=an.id
                            GROUP BY
                                pa.idpegawai
                        ) x ON x.idpegawai=p.id,
                        _pegawailengkap _pa
                    WHERE
                        p.id=_pa.id AND
                        p.del = "t"
                        '.$where.'
                    GROUP BY
                        p.id
                    ORDER BY
                        p.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['pin']);
                $objPHPExcel->getActiveSheet()->setCellValue($h1 . $i, $row['facesample'] == '0' ? trans('all.belumsampel') : trans('all.sudahsampel'));
                $objPHPExcel->getActiveSheet()->setCellValue($h2 . $i, $row['jumlahfacesample']);

                if($atributvariablepenting_controller != '') {
                    $z1 = 3; //iterasi untuk looping atribut penting 3 dari jumlah kolom fix
                    for ($j = 0; $j < $totalatributvariable; $j++) {
                        $hv = Utils::angkaToHuruf($z1);
                        $objPHPExcel->getActiveSheet()->setCellValue($hv . $i, $row[$atributvariablepenting_controller[$j]]);
                        $z1++;
                    }
                }

                if($atributpenting_controller != '') {
                    $z2 = 3 + $totalatributvariable; //iterasi untuk looping atribut penting 3 dari jumlah kolom fix
                    for ($j = 0; $j < $totalatributpenting; $j++) {
                        $hap = Utils::angkaToHuruf($z2);
                        $objPHPExcel->getActiveSheet()->setCellValue($hap . $i, $row[$atributpenting_controller[$j]]);
                        $z2++;
                    }
                }

                for ($j = 1; $j <= 4+$totalatributvariable+$totalatributpenting; $j++) {
                    $huruf = Utils::angkaToHuruf($j);
                    $objPHPExcel->getActiveSheet()->getStyle($huruf . $i)->applyFromArray($styleArray);
                }

                $i++;
            }

            // style nama,pin
            $arrWidth = array('', 50,10);
            for ($j = 1; $j <= 2; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            // style sampelwajah,jumlahsampelwajah
            $arrWidth = array('', 15,19);
            $arrh = array('', $h1,$h2);
            for ($j = 1; $j <= 2; $j++) {
                $huruf = $arrh[$j];
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            // style garis
            $end_i = $i - 1;
            $objPHPExcel->getActiveSheet()->getStyle('A1:C' . $end_i)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->applyFromArray($styleArray);

            $sql = 'SELECT gunakanpwd,pwd FROM parameterekspor';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);

            // password
            Utils::passwordExcel($objPHPExcel);
			Utils::insertLogUser('Ekspor facesample');
            Utils::setFileNameExcel(trans('all.facesample'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
    
    public function getAtributNilai($id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT
                  CONCAT(an.nilai," (",a.atribut,")") as atributnilai
                FROM
                  atributnilai an
                  LEFT JOIN atribut a ON an.idatribut=a.id
                WHERE
                  an.id = :idatributnilai';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idatributnilai', $id);
        $stmt->execute();
        $rowArtibutNilai = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rowArtibutNilai['atributnilai'];
    }
    
    public function facesamplePegawai($idpegawai)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        // select facesample
        $sql = 'SELECT id, filename, checksum FROM facesample WHERE idpegawai=:idpegawai';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idpegawai', $idpegawai);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    
        // select nama pegawai
        $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
		$namapegawai = $stmt->fetch(PDO::FETCH_OBJ);
		Utils::insertLogUser('akses menu face sample pegawai');
        if ($stmt->rowCount()>0) {
            return view('datainduk/pegawai/facesample/facesample', ['idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'data' => $data]);
        }else{
            return redirect('datainduk/pegawai/facesample')->with('message', trans('all.pegawaitidakditemukan'));
        }
    }

	public function deleteFaceSample($idfacesample,$dari)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();

		$sql = 'SELECT idpegawai, filename FROM facesample WHERE id=:idfacesample LIMIT 1';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam('idfacesample', $idfacesample);
		$stmt->execute();
		if ($stmt->rowCount()>0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$idpegawai = $row['idpegawai'];
			$filename = $row['filename'];
			$path = Session::get('folderroot_perusahaan').'/facesample/'.Utils::id2Folder($idpegawai).'/'.$idpegawai.'/'.$filename;

			if (file_exists($path)) {
				unlink($path);
			}

			if (file_exists($path.'_thumb')) {
				unlink($path.'_thumb');
			}

            Utils::deleteData($pdo,'facesample',$idfacesample);
			Utils::insertLogUser('Hapus sample wajah');

			if($dari == 'pegawai'){
				return redirect('datainduk/pegawai/pegawai/facesample/'.$idpegawai)->with('message', trans('all.facesampledihapus'));
			}else{
				return redirect('datainduk/pegawai/facesample/')->with('message', trans('all.facesampledihapus'));
			}
		}
		else {
			if($dari == 'pegawai'){
				return redirect('datainduk/pegawai/pegawai/')->with('message', trans('all.facesampletidakditemukan'));
			}else{
				return redirect('datainduk/pegawai/facesample/')->with('message', trans('all.facesampletidakditemukan'));
			}
		}
	}

	public function deleteFaceSampleRekap($idfacesample)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
        $response = array();
        $response['status'] = 'error';
        $response['msg'] = '';
        $response['data'] = '';
		$sql = 'SELECT idpegawai, filename FROM facesample WHERE id=:idfacesample LIMIT 1';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam('idfacesample', $idfacesample);
		$stmt->execute();
		if ($stmt->rowCount()>0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$idpegawai = $row['idpegawai'];
			$filename = $row['filename'];
			$path = Session::get('folderroot_perusahaan').'/facesample/'.Utils::id2Folder($idpegawai).'/'.$idpegawai.'/'.$filename;

			if (file_exists($path)) {
				unlink($path);
			}

			if (file_exists($path.'_thumb')) {
				unlink($path.'_thumb');
			}

            Utils::deleteData($pdo,'facesample',$idfacesample);
			Utils::insertLogUser('Hapus sample wajah');

			//return $idfacesample;
            $response['status'] = 'OK';
            $response['data'] = $idfacesample;
		}
		else {
			//return '0';
            $response['msg'] = trans('all.facesampletidakditemukan');
		}
		return $response;
	}

	// public function sendMail()
    // {
    //     $data = array('nama'=>"fathoela",'kode'=>'tester','notelp'=>'082257038541','email'=>'lightningmcqueen746@gmail.com');
    //     Mail::send('templateemail.daftar', $data, function($message) use ($data) {
    //         $message->to($data['email'])->subject('Register');
    //         $message->from('no-reply@smartpresence.id','Smart Presence');
    //     });
    // }

	public function detailPegawai($idpegawai)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		// data pegawai
		$sql = 'SELECT
					p.id,
					p.nama,
					p.pin,
					IFNULL(getpegawaijamkerja(p.id,"nama",current_date()),"") as jamkerja,
					IF(p.status="a","'.trans('all.aktif').'","'.trans('all.tidakaktif').'") as status,
					IF(p.nomorhp="","-",p.nomorhp) as nomorhp,
					DATE_FORMAT(p.tanggalaktif, "%d/%m/%Y") as tanggalaktif,
					getatributpegawai_all(p.id) as atributpegawai
				FROM
					pegawai p
				WHERE
					p.id = '.$idpegawai;
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_OBJ);

		// data pegawai lokasi
		$sql = 'SELECT
					GROUP_CONCAT(l.nama SEPARATOR ", ") as lokasi
				FROM
					lokasi l LEFT JOIN pegawailokasi pl ON pl.idlokasi=l.id
				WHERE
					pl.idpegawai = :idpegawai';
		$stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
		$stmt->execute();
		$lokasipegawai = $stmt->fetch(PDO::FETCH_OBJ);

		return view('detailpegawai', ['data' => $data, 'lokasipegawai' => $lokasipegawai]);
	}

	public function detailLogAbsen($idlogabsen){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT
                    la.id,
                    la.waktu,
                    p.nama as pegawai,
                    m.nama as mesin,
                    la.masukkeluar,
                    la.status,
                    la.terhitungkerja,
                    la.sumber
                FROM
                    logabsen la,
                    pegawai p,
                    mesin m
                WHERE
                    la.idpegawai = p.id AND 
                    la.idmesin = m.id AND 
                    la.id = :idlogabsen';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idlogabsen', $idlogabsen);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_OBJ);
        return view('include/detaillogabsen', ['data' => $data]);
    }
}