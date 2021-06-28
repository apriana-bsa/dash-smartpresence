<?php

namespace App\Http\Controllers;

use App\Utils;
use Hamcrest\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class BerandaController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function pencarian(Request $request)
	{
		if(!Session::has('conf_webperusahaan')){
			Session::forget('pencarian_perusahaan');
			return redirect('/');
		}
		$pencarian = $request->pencarian;
		if($pencarian == ''){
			Session::forget('pencarian_perusahaan');
			return redirect('/');
		}
		Session::set('pencarian_perusahaan', $pencarian);
		return $this->dataPencarian($pencarian);
	}

	public function pencarianKe(Request $request)
	{
		$pencarian = Session::get('pencarian_perusahaan');
		return $this->dataPencarian($pencarian);
	}

	public function dataPencarian($pencarian)
	{
		$sql = '(
				    SELECT
				        "perusahaan" as data,
				        p.id,
				        p.nama,
				        p.kode,
				        "" as atribut
				    FROM
				        absensi_v3.pengelola pn,
				        absensi_v3.perusahaan p
				    WHERE
				        pn.idperusahaan=p.id
				        AND pn.iduser = '.Session::get('iduser_perusahaan').' AND p.nama LIKE "%'.$pencarian.'%"
				    ORDER BY
				        p.nama ASC
				)
				UNION
				(
				    SELECT
				        "pegawai" as data,
				        p.id,
				        p.nama,
				        p.nomorhp as kode,
				        GROUP_CONCAT(a.nilai SEPARATOR ", ") as atribut
				    FROM
				        pegawai p
				        LEFT JOIN pegawaiatribut pa ON pa.idpegawai=p.id
				        LEFT JOIN atributnilai a ON pa.idatributnilai=a.id
				    WHERE
				        p.del = "t" AND
				        p.nama LIKE "%'.$pencarian.'%"
				    GROUP BY
				        p.id
				    ORDER BY p.nama
				) ORDER BY nama';
		$hasilpencarians = DB::connection('perusahaan_db')->table(DB::raw('('.$sql.') as pencarians'))->paginate(15);
		Utils::insertLogUser('akses menu pencarian "'.$pencarian.'"');
		return view('pencarian', ['pencarian' => $pencarian, 'hasilpencarians' => $hasilpencarians, 'menu' => 'beranda']);
	}

	public function detailPencarian($pencarian, $id){

		$data = '';
		$pdo = DB::getPdo();
        $pdo_perusahaan = DB::connection('perusahaan_db')->getPdo();
		if($pencarian == 'perusahaan'){
			$sql = 'SELECT id,"perusahaan" as jenis,nama,kode,checksum_img,if(status="a","'.trans("all.aktif").'","'.trans("all.tidakaktif").'") as status FROM perusahaan WHERE id = :idperusahaan';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':idperusahaan', $id);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_OBJ);
		}else if($pencarian == 'pegawai'){
			$sql = 'SELECT
						p.id,
						"pegawai" as jenis,
						p.nama,
						p.pin,
						IF(p.nomorhp="","-",p.nomorhp) as nomorhp,
						date_format(p.tanggalaktif, "%d/%m/%Y") as tanggalaktif,
						p.checksum_img,
						if(p.status="a","'.trans("all.aktif").'","'.trans("all.tidakaktif").'") as status
					FROM
						pegawai p
					WHERE
						p.del = "t" AND
						p.id = :idpegawai';
			$stmt = $pdo_perusahaan->prepare($sql);
			$stmt->bindValue(':idpegawai', $id);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_OBJ);
		}
		return view('pencariandetail', ['data' => $data, 'menu' => 'beranda']);
	}

	public function riwayatBeranda()
	{
		return view('indexriwayat', ['menu' => 'beranda']);
	}

	public function riwayatBerandaData($tanggal,$startfrom)
	{
		$deskripsibatasan = '';
		if(Session::has('conf_webperusahaan')) {
			$pdo = DB::getPdo();
			$waktu_eof = Utils::getCurrentDateTime($tanggal);

			$sqlWhereID = '';
			$sqlWhere = '';
			$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
			if ($batasan!='') {
				$sqlWhereID .= ' AND p.id IN '.$batasan;
				$sqlWhere .= ' AND idpegawai IN '.$batasan;
				$deskripsibatasan = trans('all.deskripsibatasan');
			}

			$sql = 'SELECT COUNT(*) as totalperusahaan FROM perusahaan WHERE id IN(SELECT idperusahaan FROM pengelola WHERE iduser = :iduser)';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
			$stmt->execute();
			$rowPerusahaan = $stmt->fetch(PDO::FETCH_ASSOC);
			$totalPerusahaan = $rowPerusahaan['totalperusahaan'];

			$pdo = DB::connection('perusahaan_db')->getPdo();

            $sql = 'CALL pegawai_seharusnya_absen(STR_TO_DATE(:tanggal, "%Y%m%d"))';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal', $tanggal);
            $stmt->execute();

            //hitung total yang sudah absen
            $sql = 'SELECT COUNT(DISTINCT idpegawai) as jumlah FROM _pegawai_seharusnya_absen WHERE 1=1 '.$sqlWhere;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalPegawaiSeharusnyaMasuk = $row['jumlah'];

            // batasan untuk total pegawai
            $sqlWhereTotalPegawai = '';
            $batasanTotalPegawai = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
            if ($batasanTotalPegawai!='') {
                $sqlWhereTotalPegawai .= ' AND p.id IN (SELECT idpegawai FROM pegawaiatribut WHERE idatributnilai IN '.$batasanTotalPegawai.')';
            }

            // totalpegawai aktif
            $sql = 'SELECT COUNT(*) as jumlah FROM pegawai p WHERE p.del = "t" AND p.status="a" '.$sqlWhereTotalPegawai;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalPegawai = $row['jumlah'];

            // totalpegawai tidaktaktif
            $sql = 'SELECT COUNT(*) as jumlah FROM pegawai p WHERE p.del = "t" AND p.status != "a" '.$sqlWhereTotalPegawai;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalPegawaiTidakAktif = $row['jumlah'];

            // totalpegawai terhapus
            $sql = 'SELECT COUNT(*) as jumlah FROM pegawai p WHERE p.del = "y" '.$sqlWhereTotalPegawai;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalPegawaiTerhapus = $row['jumlah'];

            // belumabsen
            $sql = 'SELECT
                            COUNT(DISTINCT idpegawai) as jumlah
                        FROM
                            _pegawai_seharusnya_absen
                        WHERE
                            idpegawai NOT IN
                            (
                                SELECT
                                    idpegawai
                                FROM
                                    logabsen
                                WHERE
                                    masukkeluar="m" AND
                                    status = "v" AND
                                    (waktu>=:tanggal1 AND waktu<=:tanggal2)
                                   '.$sqlWhere.'
                            )
                            '.$sqlWhere;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal1', $waktu_eof['waktuawal']);
            $stmt->bindValue(':tanggal2', $waktu_eof['waktuakhir']);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $belumAbsen = $row['jumlah'];
            $persenBelumAbsen = $totalPegawaiSeharusnyaMasuk == 0 ? 0 : round(($belumAbsen * 100) / $totalPegawaiSeharusnyaMasuk, 2);

            // sudahabsen
            $sql = 'SELECT
                            COUNT(DISTINCT p.id) as jumlah
                        FROM
                            pegawai p,
                            logabsen la
                        WHERE
                            la.masukkeluar="m" AND
                            la.status = "v" AND
                            p.id=la.idpegawai AND
                            p.status="a" AND
                            p.del = "t" AND
                            (la.waktu>=:tanggal1 AND la.waktu<=:tanggal2) '.$sqlWhereID;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal1', $waktu_eof['waktuawal']);
            $stmt->bindValue(':tanggal2', $waktu_eof['waktuakhir']);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $sudahAbsen = $row['jumlah'];
            $persenSudahAbsen = $totalPegawaiSeharusnyaMasuk == 0 ? 0 : round((($totalPegawaiSeharusnyaMasuk-$belumAbsen) * 100) / $totalPegawaiSeharusnyaMasuk, 2);

			// terlambat
			$sql = 'SELECT
						COUNT(DISTINCT p.id) as jumlah
					FROM
						pegawai p,
						rekapabsen ra
					WHERE
						p.id=ra.idpegawai AND
						p.status="a" AND
						p.del = "t" AND
						ra.selisihmasuk<0 AND
						ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d")'.$sqlWhereID;
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal', $tanggal);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$terlambat = $row['jumlah'];
			$persenTerlambat = $totalPegawai == 0 ? 0 : round(($terlambat * 100) / $totalPegawai, 2);

			// datangawal
			$sql = 'SELECT
						COUNT(DISTINCT p.id) as jumlah
					FROM
						pegawai p,
						rekapabsen ra
					WHERE
						p.id=ra.idpegawai AND
						p.status="a" AND
						p.del = "t" AND
						ra.selisihmasuk>0 AND
						ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d")'.$sqlWhereID;
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal', $tanggal);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$datangawal = $row['jumlah'];
			$persendatangawal = $totalPegawai == 0 ? 0 : round(($datangawal * 100) / $totalPegawai, 2);

			// ada dikantor
			$sql = 'SELECT
						COUNT(*) as jumlah
					FROM
						(
						SELECT
							la.idpegawai,
							MAX(CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),la.masukkeluar)) as lastabsen
						FROM
							logabsen la,
							pegawai p
						WHERE
							la.idpegawai=p.id AND
							la.status = "v" AND
							p.del = "t" AND
							la.waktu>=:tanggal01 AND la.waktu<=:tanggal02
							'.$sqlWhereID.'
						GROUP BY
							la.idpegawai
						HAVING
							RIGHT(lastabsen,1)="m"
						) x';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal01', $waktu_eof['waktuawal']);
			$stmt->bindValue(':tanggal02', $waktu_eof['waktuakhir']);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$adaDikantor = $row['jumlah'];
			$persenAdaDikantor = $totalPegawai == 0 ? 0 : round(($adaDikantor * 100) / $totalPegawai, 2);

			//peringkat absen
			$sql = 'SELECT
						p.id as idpegawai,
						p.nama
					FROM
						_peringkatabsen pa,
						pegawai p
					WHERE
						pa.idpegawai=p.id AND
						p.del = "t" AND
						pa.peringkat=1
						'.$sqlWhereID.'
					LIMIT 1
					';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$peringkatAbsen = $stmt->fetchALL(PDO::FETCH_OBJ);

			//hitung total konfirmasi absen
			$sql = 'SELECT
						COUNT(*) as jumlah
					FROM
						logabsen la,
						pegawai p
					WHERE
						la.idpegawai=p.id AND
						p.del = "t" AND
						la.status="c"'.$sqlWhereID;
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$konfirmasiLogAbsen = $row['jumlah'];

			//hitung total konfirmasi ijintidakmasuk
			$sql = 'SELECT
						COUNT(*) as jumlah
					FROM
						ijintidakmasuk itm,
						pegawai p
					WHERE
						itm.idpegawai=p.id AND
						p.del = "t" AND
						itm.status="c"'.$sqlWhereID;
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$konfirmasiIjinTidakMasuk = $row['jumlah'];

			$konfirmasi = $konfirmasiLogAbsen + $konfirmasiIjinTidakMasuk;

			//hitung total ijin tidak masuk
			$sql = 'SELECT
						COUNT(DISTINCT p.id) as jumlah
					FROM
						pegawai p,
						ijintidakmasuk itm
					WHERE
						p.id=itm.idpegawai AND
						p.del = "t" AND
						p.status="a" AND
						itm.status="a" AND
						(STR_TO_DATE(:tanggal, "%Y%m%d") BETWEEN itm.tanggalawal AND itm.tanggalakhir)'.$sqlWhereID;
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal', $tanggal);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$ijinTidakMasuk = $row['jumlah'];
			$persenIjinTidakMasuk = $totalPegawai == 0 ? 0 : round(($ijinTidakMasuk * 100) / $totalPegawai, 2);

			//hitung logabsen
			$sql = 'SELECT
                        COUNT(*) as jumlah
                    FROM
                        logabsen la,
                        pegawai p
                    WHERE
                        la.idpegawai=p.id AND
                        la.waktu>=:tanggal01 AND la.waktu<=:tanggal02
                    '.$sqlWhereID;
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal01', $waktu_eof['waktuawal']);
			$stmt->bindValue(':tanggal02', $waktu_eof['waktuakhir']);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$totalLogAbsen = $row['jumlah'];

			//hitung logabsen dengan lokasi
			$sql = 'SELECT
                            COUNT(*) as jumlah
                        FROM
                            logabsen la,
                            pegawai p
                        WHERE
                            la.idpegawai=p.id AND
                            la.status = "v" AND
                            p.del = "t" AND
                            la.waktu>=:tanggal01 AND la.waktu<=:tanggal02 AND
                            lat != 0 AND lon != 0'.$sqlWhereID;
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal01', $waktu_eof['waktuawal']);
			$stmt->bindValue(':tanggal02', $waktu_eof['waktuakhir']);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$totalLogAbsenLokasi = $row['jumlah'];

			//hitung alasan
			$sql = 'SELECT
                        COUNT(*) as jumlah
                    FROM
                        logabsen la,
                        pegawai p
                    WHERE
                        la.idpegawai=p.id AND
                        la.status = "v" AND
                        p.del = "t" AND
                        la.waktu>=:tanggal01 AND la.waktu<=:tanggal02 AND
                        ISNULL(la.idalasanmasukkeluar) = false'.$sqlWhereID;
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal01', $waktu_eof['waktuawal']);
			$stmt->bindValue(':tanggal02', $waktu_eof['waktuakhir']);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$totalAlasan = $row['jumlah'];

			// grafik
			$sql = 'CALL generategrafikabsen_email(DATE_SUB(STR_TO_DATE(:tanggal, "%Y%m%d"), INTERVAL 14-1 DAY), STR_TO_DATE(:tanggal2, "%Y%m%d"),:email)';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal', $tanggal);
			$stmt->bindValue(':tanggal2', $tanggal);
			$stmt->bindValue(':email', Session::get('emailuser_perusahaan'));
			$stmt->execute();

			$sql = 'SELECT DATE_FORMAT(tanggal,"%Y, %m, %d") as tanggal,jum_masuk,jum_terlambat FROM _grafikabsen ORDER BY tanggal ASC';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$jsonGrafik = $stmt->fetchAll(PDO::FETCH_OBJ);

			$sql = 'SELECT jum_masuk,ROUND(AVG(jum_masuk*100/jadwal_masuk),2) as ratarata FROM _grafikabsen';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$ratarataMasuk = ($row['ratarata'] == null ? 0 : $row['ratarata']);
			$jumMasuk = $row['jum_masuk'];

			$sql = 'SELECT jum_terlambat,ROUND(AVG(jum_terlambat*100/jadwal_masuk),2) as ratarata FROM _grafikabsen';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$ratarataTerlambat = ($row['ratarata'] == null ? 0 : $row['ratarata']);
			$jumTerlambat = $row['jum_terlambat'];

			$sql = 'SELECT jum_tdk_masuk,ROUND(AVG(jum_tdk_masuk*100/jadwal_masuk),2) as ratarata FROM _grafikabsen';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$ratarataTidakMasuk = ($row['ratarata'] == null ? 0 : $row['ratarata']);
			$jumTidakMasuk = $row['jum_tdk_masuk'];

			// dapatkan daftar harilibur
			$sql = 'SELECT
                          hl.id as idharilibur,
                          DATE_FORMAT(hl.tanggalawal,"%d/%m/%Y") as tanggalawal,
                          DATE_FORMAT(hl.tanggalakhir,"%d/%m/%Y") as tanggalakhir,
                          hl.keterangan
                      FROM
                          harilibur hl
                      WHERE
                          (
                            hl.tanggalawal>=STR_TO_DATE(:tanggal, "%Y%m%d") OR
                            (STR_TO_DATE(:tanggal2, "%Y%m%d") BETWEEN hl.tanggalawal AND hl.tanggalakhir)
                          )
                      ORDER BY
                          hl.tanggalawal ASC
                      LIMIT 3';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal', $tanggal);
			$stmt->bindValue(':tanggal2', $tanggal);
			$stmt->execute();
			$harilibur = $stmt->fetchAll(PDO::FETCH_OBJ);

			//pulangawal
			$sql = 'SELECT COUNT(*) as pulangawal FROM rekapabsen WHERE selisihkeluar<0 AND tanggal = :tanggal'.$sqlWhere;
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal', $tanggal);
			$stmt->execute();
			$rowPA = $stmt->fetch(PDO::FETCH_ASSOC);
			$pulangawal = $rowPA['pulangawal'];

			//lembur
			$sql = 'SELECT COUNT(*) as lembur FROM rekapabsen WHERE lamalembur>0 AND tanggal = :tanggal'.$sqlWhere;
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal', $tanggal);
			$stmt->execute();
			$rowL = $stmt->fetch(PDO::FETCH_ASSOC);
			$lembur = $rowL['lembur'];

			//lokasi
			$sql = 'SELECT id,nama,lat,lon FROM lokasi';
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$lokasi = $stmt->fetchAll(PDO::FETCH_OBJ);

			//lokasiabsen
			$sql = 'SELECT
						id,
						lat,
						lon as lng
					FROM
						logabsen
					WHERE
						waktu>=:tanggal01 AND waktu<=:tanggal02 AND
                        status = "v" AND
						ISNULL(lat)=false AND
						ISNULL(lon)=false AND
						lat<>0 AND
						lon<>0'.$sqlWhere;
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tanggal01', $waktu_eof['waktuawal']);
			$stmt->bindValue(':tanggal02', $waktu_eof['waktuakhir']);
			$stmt->execute();

			//$lokasiabsen =  response()->json($data);
			if($stmt->rowCount() > 0) {
				$lokasiabsen = $stmt->fetchAll(PDO::FETCH_OBJ);
			}else{
				$lokasiabsen = '';
			}

		}else{
			$totalPerusahaan = '';
			$totalPegawai = '';
            $totalPegawaiTidakAktif = '';
            $totalPegawaiTerhapus = '';
			$sudahAbsen = '';
			$persenSudahAbsen = '';
			$belumAbsen = '';
			$persenBelumAbsen = '';
			$terlambat = '';
            $persenTerlambat = '';
            $datangawal = '';
            $persendatangawal = '';
            $adaDikantor = '';
			$persenAdaDikantor = '';
			$jsonGrafik = '';
			$jumMasuk = '';
			$jumTerlambat = '';
			$jumTidakMasuk = '';
			$ratarataMasuk = '';
			$ratarataTidakMasuk = '';
			$ratarataTerlambat = '';
			$harilibur = '';
			$peringkatAbsen = '';
			$totalLogAbsen = '';
			$totalLogAbsenLokasi = '';
			$totalAlasan = '';
			$ijinTidakMasuk = '';
			$persenIjinTidakMasuk = '';
			$lokasi = '';
			$pulangawal = '';
			$lembur = '';
			$lokasiabsen = '';
		}
		return view('indexriwayatdetail', ['totalperusahaan' => $totalPerusahaan,
			'totalpegawai' => $totalPegawai,
            'totalpegawaitidakaktif' => $totalPegawaiTidakAktif,
            'totalpegawaiterhapus' => $totalPegawaiTerhapus,
			'sudahabsen' => $sudahAbsen,
			'persensudahabsen' => $persenSudahAbsen,
			'belumabsen' => $belumAbsen,
			'persenbelumabsen' => $persenBelumAbsen,
			'terlambat' => $terlambat,
			'persenterlambat' => $persenTerlambat,
            'datangawal' => $datangawal,
            'persendatangawal' => $persendatangawal,
            'adadikantor' => $adaDikantor,
			'persenadadikantor' => $persenAdaDikantor,
			'grafik' => $jsonGrafik,
			'rataratamasuk' => $ratarataMasuk,
			'jummasuk' => $jumMasuk,
			'rataratatidakmasuk' => $ratarataTidakMasuk,
			'jumtidakmasuk' => $jumTidakMasuk,
			'ratarataterlambat' => $ratarataTerlambat,
			'jumterlambat' => $jumTerlambat,
			'harilibur' => $harilibur,
			'peringkatabsen' => $peringkatAbsen,
			'riwayat' => $totalLogAbsen,
			'peta' => $totalLogAbsenLokasi,
			'alasan' => $totalAlasan,
			'ijintidakmasuk' => $ijinTidakMasuk,
			'persenijintidakmasuk' => $persenIjinTidakMasuk,
			'lokasi' => $lokasi,
			'pulangawal' => $pulangawal,
			'lembur' => $lembur,
			'lokasiabsen' => $lokasiabsen,
			'tanggal' => $tanggal,
			'deskripsibatasan' => $deskripsibatasan,
			'datacd' => Utils::customDashboard(),
			'menu' => 'beranda']);
	}

	public function pencarianDetail(Request $request)
	{
	    //parameter
		$hasil = '';
		$jenis = $request->jenis;
		$tanggal = $request->tanggal;
		$pencarian = $request->pencarian;
        if(!Utils::cekDateTime($tanggal)){
            return redirect('/')->with('message', trans('all.terjadigangguan'));
        }

		if($pencarian == ''){
	   		if(Session::has($jenis . '_pencarian_detail')){
						Session::forget($jenis . '_pencarian_detail');
			 	}
        } else {
            //set session
            Session::set($jenis . '_pencarian_detail', $pencarian);
        }

		//load data sesuai jenis
		if($jenis == 'sudahabsen'){
			$hasil = $this->sudahAbsenPerTanggal($tanggal, 'o');
		}else if($jenis == 'belumabsen'){
			$hasil = $this->belumAbsenPerTanggal($tanggal, 'o');
		}else if($jenis == 'terlambat'){
			$hasil = $this->terlambatPerTanggal($tanggal, 'o');
		}else if($jenis == 'adadikantor'){
			$hasil = $this->adaDiKantor('o',true);
		}else if($jenis == 'ijintidakmasuk'){
			$hasil = $this->ijinTidakMasuk('o',true);
		}else if($jenis == 'pulangawal'){
			$hasil = $this->pulangAwalPerTanggal($tanggal, 'o');
		}else if($jenis == 'riwayat'){
			$hasil = $this->riwayatPerTanggal($tanggal, 'o');
		}else if($jenis == 'rekap'){
			$hasil = $this->rekapPerTanggal($tanggal, 'o');
		}else if($jenis == 'peta'){
			$hasil = $this->petaPerTanggal($tanggal, 'o');
		}else if($jenis == 'peringkatterbaik'){
			$hasil = $this->peringkat($jenis, 'o', 'pencarian');
		}else if($jenis == 'peringkatterlambat'){
			$hasil = $this->peringkat($jenis, 'o', 'pencarian');
		}else if($jenis == 'peringkatpulangawal'){
			$hasil = $this->peringkat($jenis, 'o', 'pencarian');
		}else if($jenis == 'peringkatlamakerja'){
			$hasil = $this->peringkat($jenis, 'o', 'pencarian');
		}else if($jenis == 'peringkatlamalembur'){
			$hasil = $this->peringkat($jenis, 'o', 'pencarian');
		}else if($jenis == 'totalpegawai'){
            $hasil = $this->totalPegawaiJenis($request->jenis_pegawai,'o',true);
		}else if($jenis == 'datacapture'){
			$hasil = $this->dataCapturePerTanggal($tanggal, 'o');
		}else if($jenis == 'customdashboard'){
		    //$hasil = $this->customDashboardData($request->idcustomdashboard_node,$tanggalkalender,'',$tanggalstartfrom);
			$hasil = $this->customDashboardData($request->idcustomdashboard_node,$tanggal,$request->keys,'');
		}
		return $hasil;
	}

	public function sudahAbsen($tanggal)
	{
		$tanggal = $tanggal == 'o' ? '' : $tanggal;
		$atribut = Utils::getAtribut();
    $pdo = DB::connection('perusahaan_db')->getPdo();
    $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail2', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'detail' => 'sudahabsen', 'tanggal' => $tanggal, 'menu' => 'beranda']);
	}

	public function sudahAbsenAtributFilter(Request $request, $tanggal)
	{
		if(isset($request->atributnilai)) {
			Session::set('sudahabsen_atributfilter', $request->atributnilai);
		}else{
			Session::forget('sudahabsen_atributfilter');
		}

		if(isset($request->jamkerja)) {
			Session::set('sudahabsen_jamkerja', $request->jamkerja);
		}else{
			Session::forget('sudahabsen_jamkerja');
		}

        if(isset($request->kategorijamkerja)) {
            Session::set('sudahabsen_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('sudahabsen_kategorijamkerja');
        }
		//return redirect('sudahabsen/'.$tanggal);
	}

	public function sudahAbsenPerTanggal($tanggal, $startfrom)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sqlWhere = '';
        $sqlWhereR = '';
		$startfrom_nama='';
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
		$more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)
        $dataringkasan = '';
        $waktu_eod = Utils::getCurrentDateTime($tanggal);

		if ($startfrom!='') {
			$sql = 'SELECT nama FROM pegawai WHERE id=:startfrom AND status = "a" AND del = "t" LIMIT 1';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':startfrom', $startfrom);
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$startfrom_nama = $row['nama'];
				$sqlWhere .= ' AND pg.nama > :startfrom_nama ';
				$more = true;
			}
		}

		if(Session::has('sudahabsen_pencarian_detail')){
			$sqlWhere .= ' AND pg.nama LIKE "%'.Session::get('sudahabsen_pencarian_detail').'%" ';
			$sqlWhereR .= ' AND pg.nama LIKE "%'.Session::get('sudahabsen_pencarian_detail').'%" ';
		}

		if(Session::has('sudahabsen_atributfilter')){
			$atributs = Session::get('sudahabsen_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND pg.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.'))';
			$sqlWhereR .= ' AND pg.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.'))';
		}

		if(Session::has('sudahabsen_kategorijamkerja')){
			$atributs = Session::get('sudahabsen_kategorijamkerja');
			$atributnilai = Utils::splitArray($atributs);
			$sqlWhere .= ' AND pg.idjamkerja IN (SELECT id FROM jamkerja WHERE idkategori IN ('.$atributnilai.'))';
			$sqlWhereR .= ' AND pg.idjamkerja IN (SELECT id FROM jamkerja WHERE idkategori IN ('.$atributnilai.'))';
		}

		if(Session::has('sudahabsen_jamkerja')) {
			if (Session::get('sudahabsen_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(STR_TO_DATE(:tanggal, "%Y%m%d"),:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':tanggal', $tanggal);
				$stmt->bindValue(':jenisjamkerja', Session::get('sudahabsen_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND pg.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
				$sqlWhereR .= ' AND pg.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND pg.id IN '.$batasan;
			$sqlWhereR .= ' AND pg.id IN '.$batasan;
		}

		if($more == false) {
            $sql = 'SELECT
                        IFNULL(jk.id,0) as id,
                        IFNULL(jk.nama, "-") as jamkerja,
                        COUNT(DISTINCT la.idpegawai) as jumlah
                    FROM
                        logabsen la,
                        (
                            SELECT
                                id,
                                nama,
                                getpegawaijamkerja(id, "id", STR_TO_DATE(:tanggal1, "%Y%m%d")) as idjamkerja
                            FROM
                                pegawai
                            WHERE
                                del = "t" AND
                                status="a"
                        ) pg
                        LEFT JOIN jamkerja jk ON jk.id=pg.idjamkerja
                    WHERE
                        la.idpegawai=pg.id AND
                        la.status = "v" AND
                        la.masukkeluar="m" AND
                        la.waktu>=:tanggal01 AND la.waktu<=:tanggal02
                        ' . $sqlWhereR . '
                    GROUP BY
                        pg.idjamkerja
                    ORDER BY
                        jk.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal1', $tanggal);
            $stmt->bindValue(':tanggal01', $waktu_eod['waktuawal']);
            $stmt->bindValue(':tanggal02', $waktu_eod['waktuakhir']);
            $stmt->execute();
            $dataringkasan = $stmt->fetchAll(PDO::FETCH_OBJ);
        }

		$sql = 'SELECT
					la.id as idlogabsen,
					pg.id as idpegawai,
					CONCAT("<span class=detailpegawai onclick=detailpegawai(",pg.id,") style=cursor:pointer>",pg.nama,"</span>") as nama,
					pg.nama as namapegawai,
					MIN(DATE_FORMAT(la.waktu,"%d/%m/%Y %T")) as waktu,
					pg.id as startfrom
		        FROM
		        	logabsen la
		        	LEFT JOIN mesin m ON la.idmesin=m.id
		        	LEFT JOIN alasanmasukkeluar amk ON la.idalasanmasukkeluar=amk.id,
		        	pegawai pg
		         	LEFT JOIN pegawaiatribut pa ON pa.idpegawai=pg.id
		        	LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
		        WHERE
		        	la.idpegawai=pg.id AND
		        	la.status = "v" AND
		        	pg.del = "t" AND
                    pg.status="a" AND
		        	la.masukkeluar="m" AND
		         	la.waktu>=:tanggal01 AND la.waktu<=:tanggal02
		         	'.$sqlWhere.'
		        GROUP BY
		        	pg.id
	         	ORDER BY
	         		pg.nama
		        LIMIT '.config('consts.LIMIT_6_KOLOM');

		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal01', $waktu_eod['waktuawal']);
		$stmt->bindValue(':tanggal02', $waktu_eod['waktuakhir']);
		if ($startfrom_nama!='') {
			$stmt->bindValue(':startfrom_nama', $startfrom_nama);
		}
		$stmt->execute();
		$datas = $stmt->fetchAll(PDO::FETCH_OBJ);

		$sql = 'SELECT
					la.id as idlogabsen
		        FROM
		        	logabsen la
		        	LEFT JOIN mesin m ON la.idmesin=m.id
		        	LEFT JOIN alasanmasukkeluar amk ON la.idalasanmasukkeluar=amk.id,
		        	pegawai pg
		         	LEFT JOIN pegawaiatribut pa ON pa.idpegawai=pg.id
		        	LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
		        WHERE
		        	la.idpegawai=pg.id AND
		        	la.masukkeluar = "m" AND
		        	la.status = "v" AND
		        	pg.del = "t" AND
		        	pg.status = "a" AND
		         	la.waktu>=:tanggal01 AND la.waktu<=:tanggal02
		         	'.$sqlWhere.'
			    GROUP BY
		        	pg.id';

		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal01', $waktu_eod['waktuawal']);
		$stmt->bindValue(':tanggal02', $waktu_eod['waktuakhir']);
		if ($startfrom_nama!='') {
			$stmt->bindValue(':startfrom_nama', $startfrom_nama);
		}
		$stmt->execute();
		$totaldata = $stmt->rowCount();

        //jadwalshift
        $sql = 'SELECT
                    IFNULL(jks.idjenis, 0) as idjenis,
                    IFNULL(jksj.nama, "-") as jenis,
                    SUM(IF(ISNULL(rs.idpegawai)=false,1,0)) as masuk
                FROM
                    (
                        SELECT
                            pg.id,
                            pg.nama,
                            js.idjamkerjashift
                        FROM
                            pegawai pg,
                            jadwalshift js
                        WHERE
                            js.idpegawai=pg.id AND
                            js.tanggal=STR_TO_DATE(:tanggal1, "%Y%m%d") AND
                            pg.del = "t" AND
                            pg.status="a"
                    ) pg
                    LEFT JOIN jamkerjashift jks ON pg.idjamkerjashift=jks.id
                    LEFT JOIN jamkerjashift_jenis jksj ON jks.idjenis=jksj.id
                    LEFT JOIN rekapshift rs ON masukkerja="y" AND rs.idjamkerjashift=pg.idjamkerjashift AND rs.idpegawai=pg.id AND tanggal=STR_TO_DATE(:tanggal2, "%Y%m%d")
                WHERE
                    1=1 '.$sqlWhereR.'
                GROUP BY
                    jks.idjenis
                HAVING
                    masuk>0
                ORDER BY
                    jenis';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggal1', $tanggal);
        $stmt->bindValue(':tanggal2', $tanggal);
        $stmt->execute();
        $datajadwalshift = $stmt->fetchAll(PDO::FETCH_OBJ);

		return view('detailmore', ['datas' => $datas, 'dataringkasan' => $dataringkasan, 'datajadwalshift' => $datajadwalshift, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'tanggal' => $tanggal, 'detail' => 'sudahabsen', 'more' => $more ]);
	}

	public function belumAbsen($tanggal)
	{
		$tanggal = $tanggal == 'o' ? '' : $tanggal;
		$atribut = Utils::getAtribut();
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail2', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'detail' => 'belumabsen', 'tanggal' => $tanggal, 'menu' => 'beranda']);
	}

	public function belumAbsenAtributFilter(Request $request, $tanggal)
	{

		if(isset($request->atributnilai)) {
			Session::set('belumabsen_atributfilter', $request->atributnilai);
		}else{
			Session::forget('belumabsen_atributfilter');
		}
		if(isset($request->jamkerja)) {
			Session::set('belumabsen_jamkerja', $request->jamkerja);
		}else{
			Session::forget('belumabsen_jamkerja');
		}

        if(isset($request->kategorijamkerja)) {
            Session::set('belumabsen_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('belumabsen_kategorijamkerja');
        }
	}

	public function belumAbsenPerTanggal($tanggal, $startfrom)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sqlWhere = '';
        $sqlWhereStartFrom = '';
		$startfrom_nama='';
		$having = '';
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
        $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)
        $dataringkasan = '';
        $waktu_eod = Utils::getCurrentDateTime($tanggal);

		if ($startfrom!='') {
			$sql = 'SELECT nama FROM pegawai WHERE id=:startfrom AND status = "a" AND del = "t" LIMIT 1';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':startfrom', $startfrom);
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$startfrom_nama = $row['nama'];
                $sqlWhereStartFrom .= ' AND p.nama > :startfrom_nama ';
				$more = true;
			}
		}

		if(Session::has('belumabsen_pencarian_detail')){
			$sqlWhere .= ' AND p.nama LIKE "%'.Session::get('belumabsen_pencarian_detail').'%" ';
		}

		if(Session::has('belumabsen_atributfilter')){
			$atributs = Session::get('belumabsen_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa, pegawai pg WHERE pa.idpegawai=pg.id AND pg.del = "t" AND pg.status = "a" AND pa.idatributnilai IN ('.$atributnilai.') )';
		}

        if(Session::has('belumabsen_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('belumabsen_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

		if(Session::has('belumabsen_jamkerja')) {
			if (Session::get('belumabsen_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(STR_TO_DATE(:tanggal, "%Y%m%d"),:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':tanggal', $tanggal);
				$stmt->bindValue(':jenisjamkerja', Session::get('belumabsen_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

		$sqlHaving = '';
		if ($having!='') {
			$sqlHaving = 'AND (nama LIKE :having1 OR jamkerja LIKE :having2 OR atribut LIKE :having3) ';
		}

        //persiapkan temporary table dahulu
        $sql = 'CALL pegawai_seharusnya_absen(STR_TO_DATE(:tanggal0, "%Y%m%d"));';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tanggal0', $tanggal);
        $stmt->execute();

        if($more == false) {

            //persiapkan temporary table lagi untuk keperluan ringkasannya
            $sql = 'DROP TEMPORARY TABLE IF EXISTS _pegawai_seharusnya_absen2';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'CREATE TEMPORARY TABLE _pegawai_seharusnya_absen2 LIKE _pegawai_seharusnya_absen';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'INSERT INTO _pegawai_seharusnya_absen2 SELECT * FROM _pegawai_seharusnya_absen';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'SELECT
                        IFNULL(jk.id,0) as id,
                        IFNULL(jk.nama, "-") as jamkerja,
                        IFNULL(p.jumlah,0)-IFNULL(pa.jumlah,0) as jumlah
                    FROM
                        (
                            SELECT
                                p.id,
                                _psa.idjamkerja as idjamkerja,
                                count(*) as jumlah
                            FROM
                                _pegawai_seharusnya_absen _psa,
                                pegawai p
                            WHERE
                                p.id=_psa.idpegawai AND
                                p.del = "t" AND
                                p.status="a"
                                ' . $sqlWhere . '
                            GROUP BY
                                idjamkerja
                        ) p
                        LEFT JOIN jamkerja jk ON jk.id=p.idjamkerja
                        LEFT JOIN
                        (
                            SELECT
                                pg.idjamkerja,
                                COUNT(DISTINCT la.idpegawai) as jumlah
                            FROM
                                logabsen la,
                                (
                                    SELECT
                                        p.id,
                                        getpegawaijamkerja(p.id, "id", STR_TO_DATE(:tanggal2, "%Y%m%d")) as idjamkerja
                                    FROM
                                        _pegawai_seharusnya_absen2 _psa2,
                                        pegawai p
                                    WHERE
                                        p.id=_psa2.idpegawai AND
                                        p.del = "t" AND
                                        p.status="a"
                                        ' . $sqlWhere . '
                                ) pg
                            WHERE
                                la.idpegawai=pg.id AND
                                la.status = "v" AND
                                la.masukkeluar="m" AND
                                la.waktu>=:tanggal3a AND la.waktu<=:tanggal3b
                            GROUP BY
                                pg.idjamkerja
                        )
                        pa ON p.idjamkerja=pa.idjamkerja
                    ORDER BY
                        jk.nama ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal2', $tanggal);
            $stmt->bindValue(':tanggal3a', $waktu_eod['waktuawal']);
            $stmt->bindValue(':tanggal3b', $waktu_eod['waktuakhir']);
            $stmt->execute();
            $dataringkasan = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        //IFNULL(_psa.idjamkerjakhusus,"") as idjamkerjakhusus,
        $sql = 'SELECT
                    p.id as idpegawai,
                    CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
                    p.nama as namapegawai,
                    p.nomorhp,
                    getatributtampilpadaringkasan(p.id) as atribut,
                    IFNULL(jk.nama,"") as jamkerja,
                    la.id as flaglogabsen,
                    IFNULL(_psa.idjamkerjakhusus,"") as idjamkerjakhusus,
                    p.id as startfrom
                FROM
                    _pegawai_seharusnya_absen _psa
                    LEFT JOIN jamkerja jk ON _psa.idjamkerja=jk.id,
                    pegawai p
                    LEFT JOIN
                    (
                        SELECT
                            pg.id
                        FROM
                            logabsen la,
                            pegawai pg
                        WHERE
                            la.idpegawai=pg.id AND
                            la.status = "v" AND
                            pg.status="a" AND
                            pg.del="t" AND
                            la.masukkeluar="m" AND
                            la.waktu>=:tanggal1 AND la.waktu<=:tanggal2
                        GROUP BY
                            pg.id
                    ) la ON la.id=p.id
                WHERE
                    p.id=_psa.idpegawai AND
                    p.status="a" AND
                    p.del="t"
                    '.$sqlWhere.'
                    '.$sqlWhereStartFrom.'
                GROUP BY
                    p.id
                HAVING
                    ISNULL(flaglogabsen)=true
                '.$sqlHaving.'
                ORDER BY
                    p.nama
                LIMIT '.config('consts.LIMIT_6_KOLOM');
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal1', $waktu_eod['waktuawal']);
		$stmt->bindValue(':tanggal2', $waktu_eod['waktuakhir']);
		if ($startfrom_nama!='') {
			$stmt->bindValue(':startfrom_nama', $startfrom_nama);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
			$stmt->bindValue(':having3', $having);
		}
		$stmt->execute();
		$datas = $stmt->fetchAll(PDO::FETCH_OBJ);

        $sql = 'SELECT
                    la.id as flaglogabsen
                FROM
                    _pegawai_seharusnya_absen _psa
                    LEFT JOIN jamkerja jk ON _psa.idjamkerja=jk.id,
                    pegawai p
                    LEFT JOIN
                    (
                        SELECT
                            pg.id
                        FROM
                            logabsen la,
                            pegawai pg
                        WHERE
                            la.idpegawai=pg.id AND
                            pg.status="a" AND
                            pg.del="t" AND
                            la.masukkeluar="m" AND
                            la.waktu>=:tanggal1 AND la.waktu<=:tanggal2
                        GROUP BY
                            pg.id
                    ) la ON la.id=p.id
                WHERE
                    p.id=_psa.idpegawai AND
                    p.status="a" AND
                    p.del="t"
                    '.$sqlWhere.'
                    '.$sqlWhereStartFrom.'
                GROUP BY
                    p.id
                HAVING
                    ISNULL(flaglogabsen)=true
                '.$sqlHaving;
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal1', $waktu_eod['waktuawal']);
		$stmt->bindValue(':tanggal2', $waktu_eod['waktuakhir']);
		if ($startfrom_nama!='') {
			$stmt->bindValue(':startfrom_nama', $startfrom_nama);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
			$stmt->bindValue(':having3', $having);
		}
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		//jadwalshift
        $sql = 'SELECT
                    idjenis,
                    jenis,
                    totalpegawai-sudahabsen as masuk
                FROM
                (
                    SELECT
                        IFNULL(jks.idjenis, 0) as idjenis,
                        IFNULL(jksj.nama, "-") as jenis,
                        COUNT(*) as totalpegawai,
                        SUM(IF(ISNULL(rs.idpegawai)=false,1,0)) as sudahabsen
                    FROM
                        (
                            SELECT
                                p.id,
                                js.idjamkerjashift
                            FROM
                                pegawai p,
                                jadwalshift js
                            WHERE
                                js.idpegawai=p.id AND
                                js.tanggal=STR_TO_DATE(:tanggal1, "%Y%m%d") AND
                                p.del = "t" AND
                                p.status="a"
                        ) pg
                        LEFT JOIN jamkerjashift jks ON pg.idjamkerjashift=jks.id
                        LEFT JOIN jamkerjashift_jenis jksj ON jks.idjenis=jksj.id
                        LEFT JOIN rekapshift rs ON masukkerja="y" AND rs.idjamkerjashift=pg.idjamkerjashift AND rs.idpegawai=pg.id AND tanggal=STR_TO_DATE(:tanggal2, "%Y%m%d")
                    GROUP BY
                        jks.idjenis
                ) x
                WHERE
                    totalpegawai-sudahabsen>0
                ORDER BY
                    jenis ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggal1', $tanggal);
        $stmt->bindValue(':tanggal2', $tanggal);
        $stmt->execute();
        $datajadwalshift = $stmt->fetchAll(PDO::FETCH_OBJ);

		return view('detailmore', ['datas' => $datas, 'dataringkasan' => $dataringkasan, 'datajadwalshift' => $datajadwalshift, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'tanggal' => $tanggal, 'detail' => 'belumabsen', 'more' => $more]);
	}

    public function jamKerjaPegawai($tanggal,$jenis,$id,$more='')
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $data = '';
        $totaldata = 0;
        $limitdata = config('consts.LIMIT_6_KOLOM');
        $where = '';
        $startfrom_nama = '';
        $waktu_eod = Utils::getCurrentDateTime($tanggal);

        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $where .= ' AND p.id IN '.$batasan;
        }

        if($more != ''){
            $sql = 'SELECT nama FROM pegawai WHERE id=:more AND status = "a" AND del = "t" LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':more', $more);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $startfrom_nama = $row['nama'];
                $where .= ' AND p.nama > :startfrom_nama ';
            }
		}
		
		if(Session::has($jenis.'_pencarian_detail')){
			$where .= ' AND p.nama LIKE "%'.Session::get($jenis.'_pencarian_detail').'%" ';
		}

		if(Session::has($jenis.'_atributfilter')){
			$atributs = Session::get($jenis.'_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa, pegawai pg WHERE pa.idpegawai=pg.id AND pg.del = "t" AND pg.status = "a" AND pa.idatributnilai IN ('.$atributnilai.') )';
		}

		if(Session::has($jenis.'_kategorijamkerja')){
			$kategorijamkerja = Utils::splitArray(Session::get($jenis.'_kategorijamkerja'));
			$where .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
		}

		if(Session::has('belumabsen_jamkerja')) {
			if (Session::get('belumabsen_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(STR_TO_DATE(:tanggal, "%Y%m%d"),:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':tanggal', $tanggal);
				$stmt->bindValue(':jenisjamkerja', Session::get('belumabsen_jamkerja'));
				$stmt->execute();

				$where .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

        if($jenis == 'sudahabsen'){
            $sql = 'SELECT
                        la.id as idlogabsen,
                        p.id as id,
                        p.nama,
                        MIN(DATE_FORMAT(la.waktu,"%d/%m/%Y %T")) as waktu,
                        p.id as startfrom
                    FROM
                        logabsen la
                        LEFT JOIN mesin m ON la.idmesin=m.id
                        LEFT JOIN alasanmasukkeluar amk ON la.idalasanmasukkeluar=amk.id,
                        pegawai p
                        LEFT JOIN pegawaiatribut pa ON pa.idpegawai=p.id
                        LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
                    WHERE
                        la.idpegawai=p.id AND
                        la.status = "v" AND
                        p.del = "t" AND
                        p.status="a" AND
                        la.masukkeluar="m" AND
                        la.waktu>=:tanggal01 AND la.waktu<=:tanggal02 AND
                        ((:id0=0 AND ISNULL(CAST(getpegawaijamkerja(p.id, "id", STR_TO_DATE(:tanggal0, "%Y%m%d")) AS BINARY))=true) OR CAST(getpegawaijamkerja(p.id, "id", STR_TO_DATE(:tanggal1, "%Y%m%d")) AS BINARY)=:id1)
                        '.$where.'
                    GROUP BY
                        p.id
                    ORDER BY
                        p.nama
                    LIMIT :limit';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal01', $waktu_eod['waktuawal']);
            $stmt->bindValue(':tanggal02', $waktu_eod['waktuakhir']);
            $stmt->bindValue(':tanggal0', $tanggal);
            $stmt->bindValue(':tanggal1', $tanggal);
            $stmt->bindValue(':id0', $id);
            $stmt->bindValue(':id1', $id);
            $stmt->bindValue(':limit', $limitdata);
            if($more != ''){
                $stmt->bindValue(':startfrom_nama', $startfrom_nama);
            }
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        }else if($jenis == 'belumabsen') {
			//persiapkan temporary table dahulu
            $sql = 'CALL pegawai_seharusnya_absen(STR_TO_DATE(:tanggal0, "%Y%m%d"));';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':tanggal0', $tanggal);
            $stmt->execute();

            $sql = 'SELECT
                        p.id,
                        p.nama,
                        p.nomorhp,
                        getatributtampilpadaringkasan(p.id) as atribut,
                        IFNULL(jk.nama,"") as jamkerja,
                        la.id as flaglogabsen,
                        p.id as startfrom
                    FROM
                        _pegawai_seharusnya_absen _psa
                        LEFT JOIN jamkerja jk ON _psa.idjamkerja=jk.id,
                        pegawai p
                        LEFT JOIN
                        (
                            SELECT
                                pg.id
                            FROM
                                logabsen la,
                                pegawai pg
                            WHERE
                                la.idpegawai=pg.id AND
                                la.status = "v" AND
                                pg.status="a" AND
                                pg.del="t" AND
                                la.masukkeluar="m" AND
                                la.waktu>=:tanggal1 AND la.waktu<=:tanggal2
                            GROUP BY
                                pg.id
                        ) la ON la.id=p.id
                    WHERE
                        p.id=_psa.idpegawai AND
                        p.status="a" AND
                        p.del="t" AND
                        IFNULL(_psa.idjamkerja,0)=:idjamkerja
                        '.$where.'
                    GROUP BY
                        p.id
                    HAVING
                        ISNULL(flaglogabsen)=true
                    ORDER BY
                        p.nama
                    LIMIT :limit';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal1', $waktu_eod['waktuawal']);
            $stmt->bindValue(':tanggal2', $waktu_eod['waktuakhir']);
            $stmt->bindValue(':idjamkerja', $id);
            $stmt->bindValue(':limit', $limitdata);
            if($more != ''){
                $stmt->bindValue(':startfrom_nama', $startfrom_nama);
            }
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            //tentukan totaldata
            $sql = 'SELECT
                        la.id as flaglogabsen
                    FROM
                        _pegawai_seharusnya_absen _psa
                        LEFT JOIN jamkerja jk ON _psa.idjamkerja=jk.id,
                        pegawai p
                        LEFT JOIN
                        (
                            SELECT
                                pg.id
                            FROM
                                logabsen la,
                                pegawai pg
                            WHERE
                                la.idpegawai=pg.id AND
                                la.status = "v" AND
                                pg.status="a" AND
                                pg.del="t" AND
                                la.masukkeluar="m" AND
                                la.waktu>=:tanggal1 AND la.waktu<=:tanggal2
                            GROUP BY
                                pg.id
                        ) la ON la.id=p.id
                    WHERE
                        p.id=_psa.idpegawai AND
                        p.status="a" AND
                        p.del="t" AND
                        IFNULL(_psa.idjamkerja,0)=:idjamkerja
                        '.$where.'
                    GROUP BY
                        p.id
                    HAVING
                        ISNULL(flaglogabsen)=true';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal1', $waktu_eod['waktuawal']);
            $stmt->bindValue(':tanggal2', $waktu_eod['waktuakhir']);
            $stmt->bindValue(':idjamkerja', $id);
            if($more != ''){
                $stmt->bindValue(':startfrom_nama', $startfrom_nama);
            }
            $stmt->execute();
            $totaldata = $stmt->rowCount();
        }

        return view('include/ringkasanjamkerja', ['idjamkerja' => $id, 'jenis' => $jenis, 'tanggal' => $tanggal, 'totaldata' => $totaldata, 'limitdata' => $limitdata, 'more' => $more, 'data' => $data]);
    }

    public function jadwalShift($tanggal,$jenis,$idjenis,$more='')
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $data = '';
        $totaldata = 0;
        $limitdata = config('consts.LIMIT_6_KOLOM');
        $where = '';
        $startfrom_nama = '';

        if ($idjenis == 0) {
            $sqlWhereIdJenis = ' AND ISNULL(jks.idjenis)=true ';
        } else {
            $sqlWhereIdJenis = ' AND jks.idjenis= '.$idjenis;
        }

        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $where .= ' AND pg.id IN '.$batasan;
        }

        if($more != ''){
            $sql = 'SELECT nama FROM pegawai WHERE id=:more AND status = "a" AND del = "t" LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':more', $more);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $startfrom_nama = $row['nama'];
                $where .= ' AND pg.nama > :startfrom_nama ';
            }
        }

        if($jenis == 'sudahabsen'){
            $sql = 'SELECT
                        pg.id,
                        pg.nama,
                        pg.pin,
                        getatributtampilpadaringkasan(pg.id) as atribut,
                        rs.waktumasuk,
                        pg.id as startfrom
                    FROM
                        pegawai pg,
                        rekapshift rs,
                        jamkerjashift jks
                    WHERE
                        pg.del = "t" AND
                        pg.status="a" AND
                        rs.masukkerja="y" AND
                        rs.idpegawai=pg.id AND
                        rs.tanggal=:tanggal AND
                        jks.id=rs.idjamkerjashift
                        ' . $sqlWhereIdJenis . '
                        ' . $where . '
                    GROUP BY
                        pg.id
                    ORDER BY
                        pg.nama
                    LIMIT :limit';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal', $tanggal);
            $stmt->bindValue(':limit', $limitdata);
            if($more != ''){
                $stmt->bindValue(':startfrom_nama', $startfrom_nama);
            }
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);


        }else if($jenis == 'belumabsen') {
            $sql = 'SELECT
                        pg.id,
                        pg.nama,
                        pg.pin,
                        getatributtampilpadaringkasan(pg.id) as atribut,
                        pg.id as startfrom
                    FROM
                        (
                            SELECT
                                p.id,
                                p.nama,
                                p.pin,
                                js.idjamkerjashift
                            FROM
                                pegawai p,
                                jadwalshift js,
                                jamkerjashift jks
                            WHERE
                                jks.id=js.idjamkerjashift AND
                                js.idpegawai=p.id AND
                                js.tanggal=STR_TO_DATE(:tanggal1, "%Y%m%d") AND
                                p.del = "t" AND
                                p.status="a"
                                '.$sqlWhereIdJenis.'
                        ) pg
                        LEFT JOIN rekapshift rs ON masukkerja="y" AND rs.idjamkerjashift=pg.idjamkerjashift AND rs.idpegawai=pg.id AND tanggal=STR_TO_DATE(:tanggal2, "%Y%m%d")
                    WHERE
                        ISNULL(rs.idjamkerjashift)=true
                        ' . $where . '
                    GROUP BY
                        pg.id
                    ORDER BY
                        pg.nama
                    LIMIT :limit';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal1', $tanggal);
            $stmt->bindValue(':tanggal2', $tanggal);
            $stmt->bindValue(':limit', $limitdata);
            if($more != ''){
                $stmt->bindValue(':startfrom_nama', $startfrom_nama);
            }
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            //tentukan totaldata
            $sql = 'SELECT
                    COUNT(DISTINCT pg.id) as jumlah
                FROM
                    (
                        SELECT
                            p.id,
                            p.nama,
                            p.pin,
                            js.idjamkerjashift
                        FROM
                            pegawai p,
                            jadwalshift js,
                            jamkerjashift jks
                        WHERE
                            jks.id=js.idjamkerjashift AND
                            js.idpegawai=p.id AND
                            js.tanggal=:tanggal1 AND
                            p.del = "t" AND
                            p.status="a"
                            '.$sqlWhereIdJenis.'
                    ) pg
                    LEFT JOIN rekapshift rs ON masukkerja="y" AND rs.idjamkerjashift=pg.idjamkerjashift AND rs.idpegawai=pg.id AND tanggal=:tanggal2
                WHERE
                    ISNULL(rs.idjamkerjashift)=true'.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal1', $tanggal);
            $stmt->bindValue(':tanggal2', $tanggal);
            if($more != ''){
                $stmt->bindValue(':startfrom_nama', $startfrom_nama);
            }
            $stmt->execute();
            $rowTotal = $stmt->fetch(PDO::FETCH_ASSOC);
            $totaldata = $rowTotal['jumlah'];
        }
        return view('include/jadwalshift', ['idjenis' => $idjenis, 'jenis' => $jenis, 'tanggal' => $tanggal, 'totaldata' => $totaldata, 'limitdata' => $limitdata, 'more' => $more, 'data' => $data]);
    }

	public function terlambat($tanggal)
	{
		$tanggal = $tanggal == 'o' ? '' : $tanggal;
		$atribut = Utils::getAtribut();
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail2', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'detail' => 'terlambat', 'tanggal' => $tanggal, 'menu' => 'beranda']);
	}

	public function terlambatAtributFilter(Request $request, $tanggal)
	{
		if(isset($request->atributnilai)) {
			Session::set('terlambat_atributfilter', $request->atributnilai);
		}else{
			Session::forget('terlambat_atributfilter');
		}
		if(isset($request->jamkerja)) {
			Session::set('terlambat_jamkerja', $request->jamkerja);
		}else{
			Session::forget('terlambat_jamkerja');
		}

        if(isset($request->kategorijamkerja)) {
            Session::set('terlambat_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('terlambat_kategorijamkerja');
        }
		//return redirect('terlambat/'.$tanggal);
	}

	public function terlambatPerTanggal($tanggal, $startfrom)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$having = '';
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
        $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)

		$sqlWhere = '';
		if ($startfrom!='') {
			$sqlWhere = ' AND CONCAT(LPAD(-1*ra.selisihmasuk,12,"0"),"_",LPAD(p.id,9,"0")) < :startfrom ';
			$more = true;
		}
		$sqlHaving = '';
		if ($having!='') {
			$sqlHaving = 'HAVING nama LIKE :having1 OR atribut LIKE :having2 ';
		}

		if(Session::has('terlambat_pencarian_detail')){
			$sqlWhere .= ' AND p.nama LIKE "%'.Session::get('terlambat_pencarian_detail').'%" ';
		}

		if(Session::has('terlambat_atributfilter')){
			$atributs = Session::get('terlambat_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa, pegawai pg WHERE pa.idpegawai=pg.id AND pg.del = "t" AND pg.status = "a" AND pa.idatributnilai IN ('.$atributnilai.') )';
		}

        if(Session::has('terlambat_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('terlambat_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

		if(Session::has('terlambat_jamkerja')) {
			if (Session::get('terlambat_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(STR_TO_DATE(:tanggal, "%Y%m%d"),:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':tanggal', $tanggal);
				$stmt->bindValue(':jenisjamkerja', Session::get('terlambat_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

		$sql = 'SELECT
                    ra.id,
                    p.id as idpegawai,
                    CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
					p.nama as namapegawai,
					DATE_FORMAT(ra.waktumasuk,"%d/%m/%Y %T") as waktumasuk,
                    getatributtampilpadaringkasan(p.id) as atribut,
                    -1*ra.selisihmasuk as terlambat,
                    CONCAT(LPAD(-1*ra.selisihmasuk,12,"0"),"_",LPAD(p.id,9,"0")) as startfrom
                FROM
                    rekapabsen ra,
                    pegawai p
                WHERE
                    p.id=ra.idpegawai AND
                    p.del = "t" AND
                    p.status="a" AND
                    ra.selisihmasuk<0 AND
                    ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d")
                    '.$sqlWhere.'
                GROUP BY
                    p.id
                '.$sqlHaving.'
                ORDER BY
                    -1*ra.selisihmasuk DESC,
                    p.id DESC
                LIMIT '.config('consts.LIMIT_6_KOLOM');
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom!='') {
			$stmt->bindValue(':startfrom', $startfrom);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
		}
		$stmt->execute();
		$datas = $stmt->fetchAll(PDO::FETCH_OBJ);

		$sql = 'SELECT
                    ra.id
                FROM
                    rekapabsen ra,
                    pegawai p
                WHERE
                    p.id=ra.idpegawai AND
                    p.del = "t" AND
                    p.status="a" AND
                    ra.selisihmasuk<0 AND
                    ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d")
                    '.$sqlWhere.'
                GROUP BY
                    p.id
                '.$sqlHaving;
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom!='') {
			$stmt->bindValue(':startfrom', $startfrom);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
		}
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		return view('detailmore', ['datas' => $datas, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'tanggal' => $tanggal, 'more' => $more, 'detail' => 'terlambat', 'menu' => 'beranda']);
	}

	public function datangAwal($tanggal)
	{
		$tanggal = $tanggal == 'o' ? '' : $tanggal;
		$atribut = Utils::getAtribut();
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail2', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'detail' => 'datangawal', 'tanggal' => $tanggal, 'menu' => 'beranda']);
	}

	public function datangAwalAtributFilter(Request $request, $tanggal)
	{
		if(isset($request->atributnilai)) {
			Session::set('datangawal_atributfilter', $request->atributnilai);
		}else{
			Session::forget('datangawal_atributfilter');
		}
		if(isset($request->jamkerja)) {
			Session::set('datangawal_jamkerja', $request->jamkerja);
		}else{
			Session::forget('datangawal_jamkerja');
		}

        if(isset($request->kategorijamkerja)) {
            Session::set('datangawal_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('datangawal_kategorijamkerja');
        }
		//return redirect('datangawal/'.$tanggal);
	}

	public function datangAwalPerTanggal($tanggal, $startfrom)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$having = '';
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
        $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)

		$sqlWhere = '';
		if ($startfrom!='') {
			$sqlWhere = ' AND CONCAT(LPAD(-1*ra.selisihmasuk,12,"0"),"_",LPAD(p.id,9,"0")) < :startfrom ';
			$more = true;
		}
		$sqlHaving = '';
		if ($having!='') {
			$sqlHaving = 'HAVING nama LIKE :having1 OR atribut LIKE :having2 ';
		}

		if(Session::has('datangawal_pencarian_detail')){
			$sqlWhere .= ' AND p.nama LIKE "%'.Session::get('datangawal_pencarian_detail').'%" ';
		}

		if(Session::has('datangawal_atributfilter')){
			$atributs = Session::get('datangawal_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa, pegawai pg WHERE pa.idpegawai=pg.id AND pg.del = "t" AND pg.status = "a" AND pa.idatributnilai IN ('.$atributnilai.') )';
		}

        if(Session::has('datangawal_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('datangawal_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

		if(Session::has('datangawal_jamkerja')) {
			if (Session::get('datangawal_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(STR_TO_DATE(:tanggal, "%Y%m%d"),:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':tanggal', $tanggal);
				$stmt->bindValue(':jenisjamkerja', Session::get('datangawal_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

		$sql = 'SELECT
                    ra.id,
                    p.id as idpegawai,
                    CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
					p.nama as namapegawai,
					DATE_FORMAT(ra.waktumasuk,"%d/%m/%Y %T") as waktumasuk,
                    getatributtampilpadaringkasan(p.id) as atribut,
                    CONCAT(ra.selisihmasuk) as datangawal,
                    CONCAT(LPAD(ra.selisihmasuk,12,"0"),"_",LPAD(p.id,9,"0")) as startfrom
                FROM
                    rekapabsen ra,
                    pegawai p
                WHERE
                    p.id=ra.idpegawai AND
                    p.del = "t" AND
                    p.status="a" AND
                    ra.selisihmasuk>0 AND
                    ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d")
                    '.$sqlWhere.'
                GROUP BY
                    p.id
                '.$sqlHaving.'
                ORDER BY
                    ra.selisihmasuk DESC,
                    p.id DESC
                LIMIT '.config('consts.LIMIT_6_KOLOM');
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom!='') {
			$stmt->bindValue(':startfrom', $startfrom);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
		}
		$stmt->execute();
		$datas = $stmt->fetchAll(PDO::FETCH_OBJ);

		$sql = 'SELECT
                    ra.id
                FROM
                    rekapabsen ra,
                    pegawai p
                WHERE
                    p.id=ra.idpegawai AND
                    p.del = "t" AND
                    p.status="a" AND
                    ra.selisihmasuk>0 AND
                    ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d")
                    '.$sqlWhere.'
                GROUP BY
                    p.id
                '.$sqlHaving;
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom!='') {
			$stmt->bindValue(':startfrom', $startfrom);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
		}
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		return view('detailmore', ['datas' => $datas, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'tanggal' => $tanggal, 'more' => $more, 'detail' => 'datangawal', 'menu' => 'beranda']);
	}

	public function adaDiKantorAtributFilter(Request $request, $tanggal)
	{
		if(isset($request->atributnilai)) {
			Session::set('adadikantor_atributfilter', $request->atributnilai);
		}else{
			Session::forget('adadikantor_atributfilter');
		}
		if(isset($request->jamkerja)) {
			Session::set('adadikantor_jamkerja', $request->jamkerja);
		}else{
			Session::forget('adadikantor_jamkerja');
		}

        if(isset($request->kategorijamkerja)) {
            Session::set('adadikantor_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('adadikantor_kategorijamkerja');
        }

		return $this->adaDiKantor('o',true);
	}

	public function adaDiKantor($startfrom,$filter=false)
	{
        $pdo = DB::connection('perusahaan_db')->getPdo();
		$having = '';
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
        $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)
        $currentdate = Utils::getCurrentDate();
        $waktu_eod = Utils::getCurrentDateTime();

        $sqlWhere = '';
        if ($startfrom!='') {
            $sqlWhere = ' AND p.lastabsen < :startfrom ';
            $more = true;
        }

		if(Session::has('adadikantor_pencarian_detail')){
			$sqlWhere .= ' AND p.nama LIKE "%'.Session::get('adadikantor_pencarian_detail').'%" ';
		}

		if(Session::has('adadikantor_atributfilter')){
			$atributs = Session::get('adadikantor_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') )';
		}

        if(Session::has('adadikantor_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('adadikantor_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

		if(Session::has('adadikantor_jamkerja')) {
			if (Session::get('adadikantor_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(:currentdate,:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':currentdate', $currentdate);
				$stmt->bindValue(':jenisjamkerja', Session::get('adadikantor_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

        $sqlHaving = '';
        if ($having!='') {
            $sqlHaving = 'HAVING nama LIKE :having1 OR atribut LIKE :having2 ';
        }

        //data tab pertama
        $sql = 'SELECT
                getlokasiabsen(p.lat, p.lon) as lokasi,
                COUNT(*) as jumlah
            FROM
                (
                    SELECT
                        pg.id,
                        MAX(CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),la.masukkeluar)) as lastabsen,
                        la.lat,
                        la.lon
                    FROM
                        logabsen la,
                        pegawai pg
                    WHERE
                        la.idpegawai=pg.id AND
                        la.status = "v" AND
                        pg.del = "t" AND
                        pg.status = "a" AND
                        la.waktu>=:currentdate1 AND la.waktu<=:currentdate2
                    GROUP BY
                        la.idpegawai
                    HAVING
                        RIGHT(lastabsen,1)="m"
                ) p
            WHERE
              1=1
            GROUP BY
                lokasi
            ORDER BY
                lokasi ASC
            LIMIT '.config('consts.LIMIT_6_KOLOM');
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':currentdate1', $waktu_eod['waktuawal']);
        $stmt->bindValue(':currentdate2', $waktu_eod['waktuakhir']);
        $stmt->execute();
        $dataperlokasi = $stmt->fetchAll(PDO::FETCH_OBJ);


		$sql = 'SELECT
					p.id as idpegawai,
					p.idlogabsen,
					CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
					p.nama as namapegawai,
					CONCAT(SUBSTRING(p.lastabsen,9,2),":",SUBSTRING(p.lastabsen,11,2)) as sejak,
					getlokasiabsen(p.lat, p.lon) as lokasi,
					p.lastabsen as startfrom
		        FROM
		        	(
						SELECT
							pg.id,
							pg.nama,
							la.id as idlogabsen,
							MIN(la.waktu) as firstabsen,
							MAX(CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),la.masukkeluar)) as lastabsen,
							la.lat,
							la.lon
						FROM
							logabsen la,
							pegawai pg
						WHERE
							la.idpegawai=pg.id AND
							la.status = "v" AND
							pg.del = "t" AND
							pg.status = "a" AND
						 	la.waktu>=:currentdate1 AND la.waktu<=:currentdate2
						GROUP BY
							la.idpegawai
						HAVING
							RIGHT(lastabsen,1)="m"
					) p
                WHERE
                  1=1
		          '.$sqlWhere.'
		        GROUP BY
		        	p.id
	         	'.$sqlHaving.'
		        ORDER BY
		         	p.lastabsen DESC
		        LIMIT '.config('consts.LIMIT_6_KOLOM');
        ///la.waktu>=CONCAT(:currentdate1," 00:00:00") AND la.waktu<=CONCAT(:currentdate2," 23:59:59")
		$stmt = $pdo->prepare($sql);
        $stmt->bindValue(':currentdate1', $waktu_eod['waktuawal']);
        $stmt->bindValue(':currentdate2', $waktu_eod['waktuakhir']);
        if ($startfrom!='') {
            $stmt->bindValue(':startfrom', $startfrom);
        }
        if ($having!='') {
            $having = '%'.$having.'%';
            $stmt->bindValue(':having1', $having);
            $stmt->bindValue(':having2', $having);
        }
		$stmt->execute();
		$datas = $stmt->fetchAll(PDO::FETCH_OBJ);

        $sql = 'SELECT
					p.id
		        FROM
		        	(
						SELECT
							pg.id,
							pg.nama,
							la.id as idlogabsen,
							MIN(la.waktu) as firstabsen,
							MAX(CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),la.masukkeluar)) as lastabsen
						FROM
							logabsen la,
							pegawai pg
						WHERE
							la.idpegawai=pg.id AND
							la.status = "v" AND
							pg.del = "t" AND
							pg.status = "a" AND
						 	la.waktu>=:currentdate1 AND la.waktu<=:currentdate2
						GROUP BY
							la.idpegawai
						HAVING
							RIGHT(lastabsen,1)="m"
					) p
                WHERE
                    1=1
		            '.$sqlWhere.'
		        GROUP BY
		        	p.id
	         	'.$sqlHaving;

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':currentdate1', $waktu_eod['waktuawal']);
        $stmt->bindValue(':currentdate2', $waktu_eod['waktuakhir']);
        if ($startfrom!='') {
            $stmt->bindValue(':startfrom', $startfrom);
        }
        if ($having!='') {
            $having = '%'.$having.'%';
            $stmt->bindValue(':having1', $having);
            $stmt->bindValue(':having2', $having);
        }
        $stmt->execute();
        $totaldata = $stmt->rowCount();

		$atribut = Utils::getAtribut();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');

        if($startfrom != '' or $filter == true){
            return view('detailmore', ['datas' => $datas, 'dataperlokasi' => $dataperlokasi, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'detail' => 'adadikantor', 'more' => $more, 'menu' => 'beranda']);
        }else{
            return view('indexdetailnocalendar', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'datas' => $datas, 'dataperlokasi' => $dataperlokasi, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'tanggal' => '', 'detail' => 'adadikantor', 'menu' => 'beranda']);
        }
	}

    public function totalPegawaiJenisAtributFilter(Request $request, $jenis, $tanggal)
    {
        if(isset($request->atributnilai)) {
            Session::set('totalpegawai_atributfilter', $request->atributnilai);
        }else{
            Session::forget('totalpegawai_atributfilter');
        }
        if(isset($request->jamkerja)) {
            Session::set('totalpegawai_jamkerja', $request->jamkerja);
        }else{
            Session::forget('totalpegawai_jamkerja');
        }

        if(isset($request->kategorijamkerja)) {
            Session::set('totalpegawai_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('totalpegawai_kategorijamkerja');
        }

        return $this->totalPegawaiJenis($jenis,'o',true);
    }

    public function totalPegawaiJenis($jenis,$startfrom,$filter=false)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $startfrom = $startfrom == 'o' ? '' : $startfrom;
        $having = '';
        $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)
        $currentdate = Utils::getCurrentDate();

        $sqlWhere = '';
        $startfrom_nama='';
        if ($startfrom!='') {
            $sql = 'SELECT nama FROM pegawai WHERE id=:startfrom LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':startfrom', $startfrom);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $startfrom_nama = $row['nama'];
                $sqlWhere = ' AND p.nama > :startfrom_nama ';
            }
            $more = true;
        }

        if(Session::has('totalpegawai_pencarian_detail')){
            $sqlWhere .= ' AND p.nama LIKE "%'.Session::get('totalpegawai_pencarian_detail').'%" ';
        }

        if(Session::has('totalpegawai_atributfilter')){
            $atributs = Session::get('totalpegawai_atributfilter');
            $atributnilai = Utils::atributNilai($atributs);
            $sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') )';
        }

        if(Session::has('totalpegawai_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('totalpegawai_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

        if(Session::has('totalpegawai_jamkerja')) {
            if (Session::get('totalpegawai_jamkerja') != '') {
                $sql = 'CALL pegawaijenisjamkerja(:currentdate,:jenisjamkerja)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':currentdate', $currentdate);
                $stmt->bindValue(':jenisjamkerja', Session::get('totalpegawai_jamkerja'));
                $stmt->execute();

                $sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
            }
        }

        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
        if ($batasan!='') {
            $sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM pegawaiatribut WHERE idatributnilai IN '.$batasan.')';
        }

        $sqlHaving = '';
        if ($having!='') {
            $sqlHaving = 'HAVING (nama LIKE :having1 OR jamkerja LIKE :having2 OR atribut LIKE :having3) ';
        }

        if($jenis == 'aktif') {
            $sql = 'SELECT
                        p.id as idpegawai,
                        CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
                        p.nama as namapegawai,
                        IF(p.nomorhp="","-",p.nomorhp) as nomorhp,
                        getatributtampilpadaringkasan(p.id) as atribut,
                        p.id as startfrom
                    FROM
                        pegawai p
                    WHERE
                        p.del = "t" AND
                        p.status="a"
                        ' . $sqlWhere . '
                    GROUP BY
                        p.id
                    ' . $sqlHaving . '
                    ORDER BY
                        p.nama ASC
                    LIMIT ' . config('consts.LIMIT_6_KOLOM');
        }else if($jenis == 'tidakaktif'){
            $sql = 'SELECT
                        p.id as idpegawai,
                        CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
                        p.nama as namapegawai,
                        IF(p.nomorhp="","-",p.nomorhp) as nomorhp,
                        getatributtampilpadaringkasan(p.id) as atribut,
                        p.id as startfrom
                    FROM
                        pegawai p
                    WHERE
                        p.del = "t" AND
                        p.status != "a"
                        ' . $sqlWhere . '
                    GROUP BY
                        p.id
                    ' . $sqlHaving . '
                    ORDER BY
                        p.nama ASC
                    LIMIT ' . config('consts.LIMIT_6_KOLOM');
        }else{
            //terhapus
            $sql = 'SELECT
                        p.id as idpegawai,
                        CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
                        p.nama as namapegawai,
                        IF(p.nomorhp="","-",p.nomorhp) as nomorhp,
                        getatributtampilpadaringkasan(p.id) as atribut,
                        p.id as startfrom
                    FROM
                        pegawai p
                    WHERE
                        p.del = "y"
                        ' . $sqlWhere . '
                    GROUP BY
                        p.id
                    ' . $sqlHaving . '
                    ORDER BY
                        p.nama ASC
                    LIMIT ' . config('consts.LIMIT_6_KOLOM');
        }
        $stmt = $pdo->prepare($sql);
        if ($startfrom_nama!='') {
            $stmt->bindValue(':startfrom_nama', $startfrom_nama);
        }
        if ($having!='') {
            $having = '%'.$having.'%';
            $stmt->bindValue(':having1', $having);
            $stmt->bindValue(':having2', $having);
            $stmt->bindValue(':having3', $having);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($jenis == 'aktif') {
            $sql = 'SELECT
                        p.id
                    FROM
                        pegawai p
                    WHERE
                        p.del = "t" AND
                        p.status="a"
                        ' . $sqlWhere . '
                    GROUP BY
                        p.id
                    ' . $sqlHaving;
        }else if($jenis == 'tidakaktif'){
            $sql = 'SELECT
                        p.id
                    FROM
                        pegawai p
                    WHERE
                        p.del = "t" AND
                        p.status != "a"
                        ' . $sqlWhere . '
                    GROUP BY
                        p.id
                    ' . $sqlHaving;
        }else{
            $sql = 'SELECT
                        p.id
                    FROM
                        pegawai p
                    WHERE
                        p.del = "y"
                        ' . $sqlWhere . '
                    GROUP BY
                        p.id
                    ' . $sqlHaving;
        }
        $stmt = $pdo->prepare($sql);
        if ($startfrom_nama!='') {
            $stmt->bindValue(':startfrom_nama', $startfrom_nama);
        }
        if ($having!='') {
            $having = '%'.$having.'%';
            $stmt->bindValue(':having1', $having);
            $stmt->bindValue(':having2', $having);
            $stmt->bindValue(':having3', $having);
        }
        $stmt->execute();
        $totaldata = $stmt->rowCount();

        $atribut = Utils::getAtribut();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');

        if($startfrom != '' or $filter == true){
            return view('detailmore', ['datas' => $data, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'more' => $more, 'detail' => 'totalpegawai', 'menu' => 'beranda']);
        }else{
            return view('indexdetailnocalendar', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'datas' => $data, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'tanggal' => '', 'detail' => 'totalpegawai', 'jenis' => $jenis, 'menu' => 'beranda']);
        }
    }

	public function dataCapture($tanggal)
	{
		$tanggal = $tanggal == 'o' ? '' : $tanggal;
		$atribut = Utils::getAtribut();
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail2', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'detail' => 'datacapture', 'tanggal' => $tanggal, 'menu' => 'beranda']);
	}

	public function dataCapturePerTanggal($tanggal)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sqlWhere = '';
        $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)
        $waktu_eof = Utils::getCurrentDateTime($tanggal);

		if(Session::has('datacapture_pencarian_detail')){
			$sqlWhere .= ' AND m.nama LIKE "%'.Session::get('datacapture_pencarian_detail').'%" ';
		}

        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $sqlWhere .= ' AND la.idpegawai IN '.$batasan;
        }

		$sql = 'SELECT
					m.id,
					m.nama,
					DATE_FORMAT(m.lastsync,"%d/%m/%Y %H:%i:%s") as lastsync,
					COUNT(*) as jumlah
				FROM
					logabsen la,
					mesin m
				WHERE
					la.idmesin=m.id AND
					la.status = "v" AND
					la.waktu>=:tanggal01 AND la.waktu<=:tanggal02
					'.$sqlWhere.'
				GROUP BY
					m.id
				ORDER BY
					m.nama ASC';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':tanggal01', $waktu_eof['waktuawal']);
		$stmt->bindParam(':tanggal02', $waktu_eof['waktuakhir']);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_OBJ);
		$totaldata = $stmt->rowCount();

		return view('detailmore', ['datas' => $data, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'tanggal' => $tanggal, 'more' => $more, 'detail' => 'datacapture']);
	}

    public function dataCaptureDetail($tanggal,$id,$startfrom='')
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        if($startfrom != ''){
            $where = ' AND l.id < '.$startfrom;
        }

        $sql = 'SELECT
					l.id,
					p.nama,
					p.nomorhp,
					DATE(l.waktu) as tanggal,
					TIME(l.waktu) as jam,
					l.masukkeluar,
					l.status,
					IFNULL(m.nama,"") as mesin,
					IFNULL(a.alasan,"") as alasan
				FROM
					logabsen l
                    LEFT JOIN alasanmasukkeluar a ON l.idalasanmasukkeluar=a.id
                    LEFT JOIN mesin m ON l.idmesin=m.id,
                    pegawai p
				WHERE
				    l.idpegawai=p.id AND
				    l.status = "v" AND
				    DATE(l.waktu)=STR_TO_DATE(:tanggal, "%Y%m%d") AND
				    l.idmesin = :idmesin
				    '.$where.'
                ORDER BY
                    l.id DESC
                LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':idmesin', $id);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        //total data
        $sql = 'SELECT
					l.id
				FROM
					logabsen l,
					pegawai p
				WHERE
					l.idpegawai=p.id AND
					l.status = "v" AND
				    DATE(l.waktu)=STR_TO_DATE(:tanggal, "%Y%m%d") AND
				    l.idmesin = :idmesin
				    '.$where.'
				ORDER BY
					l.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':idmesin', $id);
        $stmt->execute();
        $totaldata = $stmt->rowCount();
        return view('include/detailpopup', ['data' => $data, 'totaldata' => $totaldata, 'idmesin' => $id, 'tanggal' => $tanggal]);
    }

	public function ijinTidakMasukAtributFilter(Request $request, $tanggal)
	{
		if(isset($request->atributnilai)) {
			Session::set('ijintidakmasuk_atributfilter', $request->atributnilai);
		}else{
			Session::forget('ijintidakmasuk_atributfilter');
		}
		if(isset($request->jamkerja)) {
			Session::set('ijintidakmasuk_jamkerja', $request->jamkerja);
		}else{
			Session::forget('ijintidakmasuk_jamkerja');
		}

        if(isset($request->kategorijamkerja)) {
            Session::set('ijintidakmasuk_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('ijintidakmasuk_kategorijamkerja');
        }
		return $this->ijinTidakMasuk('o',true);
	}

	public function ijinTidakMasuk($startfrom,$filter=false)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
		$having = '';
        $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)
        $currentdate = Utils::getCurrentDate();

		$sqlWhere = '';
		if ($startfrom!='') {
			$sql = 'SELECT nama FROM pegawai WHERE id=:startfrom AND status = "a" LIMIT 1';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':startfrom', $startfrom);
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$sqlWhere = ' AND p.nama > "'. $row['nama'].'" ';
			}
			$more = true;
		}

		if(Session::has('ijintidakmasuk_pencarian_detail')){
			$sqlWhere .= ' AND p.nama LIKE "%'.Session::get('ijintidakmasuk_pencarian_detail').'%" ';
		}

		if(Session::has('ijintidakmasuk_atributfilter')){
			$atributs = Session::get('ijintidakmasuk_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') )';
		}

        if(Session::has('ijintidakmasuk_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('ijintidakmasuk_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

		if(Session::has('ijintidakmasuk_jamkerja')) {
			if (Session::get('ijintidakmasuk_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(:currentdate,:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':currentdate', $currentdate);
				$stmt->bindValue(':jenisjamkerja', Session::get('ijintidakmasuk_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

		$sqlHaving = '';
		if ($having!='') {
			$sqlHaving = 'HAVING nama LIKE :having1 OR atribut LIKE :having2 ';
		}

		$sql = 'SELECT
					p.id as idpegawai,
					CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
					p.nama as namapegawai,
					IF(p.nomorhp="","-",p.nomorhp) as nomorhp,
					getatributtampilpadaringkasan(p.id) as atribut,
					p.id as startfrom
				FROM
					ijintidakmasuk itm
					LEFT JOIN alasantidakmasuk atm ON atm.id=itm.idalasantidakmasuk,
					pegawai p
				WHERE
					p.id=itm.idpegawai AND
					p.status="a" AND
					itm.status="a" AND
					(:currentdate BETWEEN itm.tanggalawal AND itm.tanggalakhir)
					'.$sqlWhere.'
				GROUP BY
					p.id
				'.$sqlHaving.'
				ORDER BY
					p.nama
				LIMIT '.config('consts.LIMIT_3_KOLOM');

		$stmt = $pdo->prepare($sql);
        $stmt->bindValue(':currentdate', $currentdate);
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
		}
		$stmt->execute();
		$datas = $stmt->fetchAll(PDO::FETCH_OBJ);

		foreach($datas as $row){
			$sql = 'SELECT
						itm.tanggalawal,
						itm.tanggalakhir,
						itm.idalasantidakmasuk,
						IFNULL(atm.alasan,"") as alasantidakmasuk,
						itm.keterangan
					FROM
						ijintidakmasuk itm
						LEFT JOIN alasantidakmasuk atm ON itm.idalasantidakmasuk=atm.id
					WHERE
						itm.idpegawai=:idpegawai AND
						itm.status="a" AND
						(:currentdate BETWEEN itm.tanggalawal AND itm.tanggalakhir)
					ORDER BY
						itm.tanggalawal ASC';
			$stmt2 = $pdo->prepare($sql);
			$stmt2->bindValue(':currentdate', $currentdate);
			$stmt2->bindValue(':idpegawai', $row->idpegawai);
			$stmt2->execute();

			$row->ijintidakmasuk=$stmt2->fetchAll(PDO::FETCH_OBJ);
		}

		//total data
		$sql = 'SELECT
				p.id as idpegawai,
				p.nama,
				IF(p.nomorhp="","-",p.nomorhp) as nomorhp,
				getatributtampilpadaringkasan(p.id) as atribut,
				p.id as startfrom
	        FROM
	         	ijintidakmasuk itm
	         	LEFT JOIN alasantidakmasuk atm ON atm.id=itm.idalasantidakmasuk,
	        	pegawai p
	        WHERE
	         	p.id=itm.idpegawai AND
	         	p.status="a" AND
	         	itm.status="a" AND
	         	(:currentdate BETWEEN itm.tanggalawal AND itm.tanggalakhir)
	         	'.$sqlWhere;
		$stmt = $pdo->prepare($sql);
        $stmt->bindValue(':currentdate', $currentdate);
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindParam('having1', $having);
			$stmt->bindParam('having2', $having);
		}
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		$atribut = Utils::getAtribut();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');

		if($startfrom != '' or $filter == true){
			return view('detailmore', ['datas' => $datas, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_3_KOLOM'), 'detail' => 'ijintidakmasuk', 'more' => $more, 'menu' => 'beranda']);
		}else{
			return view('indexdetailnocalendar', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'datas' => $datas, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_3_KOLOM'), 'tanggal' => '', 'detail' => 'ijintidakmasuk', 'menu' => 'beranda']);
		}
	}

	public function riwayat($tanggal)
	{
		$tanggal = $tanggal == 'o' ? '' : $tanggal;
		$atribut = Utils::getAtribut();
    $pdo = DB::connection('perusahaan_db')->getPdo();
    $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail2', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'detail' => 'riwayat', 'tanggal' => $tanggal, 'menu' => 'beranda']);
	}

	public function riwayatAtributFilter(Request $request, $tanggal)
	{
		if(isset($request->atributnilai)) {
			Session::set('riwayat_atributfilter', $request->atributnilai);
		}else{
			Session::forget('riwayat_atributfilter');
		}
		if(isset($request->jamkerja)) {
			Session::set('riwayat_jamkerja', $request->jamkerja);
		}else{
			Session::forget('riwayat_jamkerja');
		}

    if(isset($request->kategorijamkerja)) {
        Session::set('riwayat_kategorijamkerja', $request->kategorijamkerja);
    }else{
        Session::forget('riwayat_kategorijamkerja');
    }
		//return redirect('riwayat/'.$tanggal);
	}

	public function riwayatPerTanggal($tanggal, $startfrom)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
		$having = '';
    $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)
    $waktu_eof = Utils::getCurrentDateTime($tanggal);

		$sqlHaving = '';
		if ($having!='') {
			$sqlHaving = 'HAVING (nama LIKE :having1 OR mesin LIKE :having2 OR alasanmasukkeluar LIKE :having3) ';
		}

		$sqlWhere = '';
		if ($startfrom!='') {
			$sqlWhere = ' AND CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),"_",DATE_FORMAT(la.inserted,"%Y%m%d%H%i%s")) < :startfrom ';
		}

		if(Session::has('riwayat_pencarian_detail')){
			$sqlWhere .= ' AND p.nama LIKE "%'.Session::get('riwayat_pencarian_detail').'%" ';
		}

		if(Session::has('riwayat_atributfilter')){
			$atributs = Session::get('riwayat_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') )';
		}

    if(Session::has('riwayat_kategorijamkerja')){
        $kategorijamkerja = Utils::splitArray(Session::get('riwayat_kategorijamkerja'));
        $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
    }

		if(Session::has('riwayat_jamkerja')) {
			if (Session::get('riwayat_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(STR_TO_DATE(:tanggal, "%Y%m%d"),:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':tanggal', $tanggal);
				$stmt->bindValue(':jenisjamkerja', Session::get('riwayat_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

		$sql = 'SELECT
					la.id,
					DATE_FORMAT(la.waktu,"%d/%m/%Y %T") as waktu,
					la.idpegawai,
					CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
					p.nama as namapegawai,
					IFNULL(m.nama, "-") as mesin,
					IF(la.masukkeluar="m","<span class=\"label label-primary\">'.trans('all.masuk').'</span>","<span class=\"label label-danger\">'.trans('all.keluar').'</span>") as masukkeluar,
					IFNULL(amk.alasan, "") as alasanmasukkeluar,
					la.lat,
					la.lon,
					la.sumber,
					la.status,
					la.konfirmasi,
					la.inserted,
					la.updated,
					CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),"_",DATE_FORMAT(la.inserted,"%Y%m%d%H%i%s")) as startfrom
				FROM
					logabsen la
					LEFT JOIN mesin m ON m.id=la.idmesin
					LEFT JOIN alasanmasukkeluar amk ON amk.id=la.idalasanmasukkeluar,
					pegawai p
				WHERE
					p.id=la.idpegawai AND
					la.waktu>=:tanggal01 AND la.waktu<=:tanggal02
					'.$sqlWhere.'
					'.$sqlHaving.'
				ORDER BY
					startfrom DESC
				LIMIT '.config('consts.LIMIT_3_KOLOM');
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal01', $waktu_eof['waktuawal']);
		$stmt->bindValue(':tanggal02', $waktu_eof['waktuakhir']);
		if ($startfrom!='') {
			$stmt->bindValue(':startfrom', $startfrom);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
			$stmt->bindValue(':having3', $having);
		}
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_OBJ);

		//total data
		$sql = 'SELECT
					p.nama,
					IFNULL(m.nama, "-") as mesin,
					IFNULL(amk.alasan, "") as alasanmasukkeluar
				FROM
					logabsen la
					LEFT JOIN mesin m ON m.id=la.idmesin
					LEFT JOIN alasanmasukkeluar amk ON amk.id=la.idalasanmasukkeluar,
					pegawai p
				WHERE
					p.id=la.idpegawai AND
					la.waktu>=:tanggal01 AND la.waktu<=:tanggal02
					'.$sqlWhere.'
				'.$sqlHaving;
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal01', $waktu_eof['waktuawal']);
		$stmt->bindValue(':tanggal02', $waktu_eof['waktuakhir']);
		if ($startfrom!='') {
			$stmt->bindValue(':startfrom', $startfrom);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
			$stmt->bindValue(':having3', $having);
		}
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		//lokasi
		$sql = 'SELECT id,nama,lat,lon FROM lokasi';
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$lokasi = $stmt->fetchAll(PDO::FETCH_OBJ);

		if($startfrom != ''){
		    $more = true;
        }
		// return $waktu_eof;
		return view('detailmore', ['datas' => $data, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_3_KOLOM'), 'lokasi' => $lokasi, 'tanggal' => $tanggal, 'more' => $more, 'detail' => 'riwayat', 'menu' => 'beranda']);
	}

	public function rekap($tanggal)
	{
		$tanggal = $tanggal == 'o' ? '' : $tanggal;
		$atribut = Utils::getAtribut();
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail2', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'detail' => 'rekap', 'tanggal' => $tanggal, 'menu' => 'beranda']);
	}

	public function rekapAtributFilter(Request $request, $tanggal)
	{
		if(isset($request->atributnilai)) {
			Session::set('rekap_atributfilter', $request->atributnilai);
		}else{
			Session::forget('rekap_atributfilter');
		}
		if(isset($request->jamkerja)) {
			Session::set('rekap_jamkerja', $request->jamkerja);
		}else{
			Session::forget('rekap_jamkerja');
		}

        if(isset($request->kategorijamkerja)) {
            Session::set('rekap_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('rekap_kategorijamkerja');
        }
	}

	public function rekapPerTanggal($tanggal, $startfrom)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
		$having = '';
        $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)

		$sqlWhere = '';
		$startfrom_nama='';
		if ($startfrom!='') {
			$sql = 'SELECT nama FROM pegawai WHERE id=:startfrom AND status = "a" LIMIT 1';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':startfrom', $startfrom);
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$startfrom_nama = $row['nama'];
				$sqlWhere = ' AND p.nama > :startfrom_nama ';
			}
			$more = true;
		}

		if(Session::has('rekap_pencarian_detail')){
			$sqlWhere .= ' AND p.nama LIKE "%'.Session::get('rekap_pencarian_detail').'%" ';
		}

		if(Session::has('rekap_atributfilter')){
			$atributs = Session::get('rekap_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') )';
		}

        if(Session::has('rekap_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('rekap_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

		if(Session::has('rekap_jamkerja')) {
			if (Session::get('rekap_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(STR_TO_DATE(:tanggal, "%Y%m%d"),:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':tanggal', $tanggal);
				$stmt->bindValue(':jenisjamkerja', Session::get('rekap_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

		$sqlHaving = '';
		if ($having!='') {
			$sqlHaving = 'HAVING (nama LIKE :having1 OR alasantidakmasuk LIKE :having2) ';
		}
        //data normal
		$sql = 'SELECT
					ra.id,
					DATE_FORMAT(ra.tanggal,"%d/%m/%Y") as waktu,
					ra.idpegawai,
					CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
					p.nama as namapegawai,
					IFNULL(hl.keterangan,"") as harilibur,
					ra.masukkerja as masukkerja,
					IFNULL(atm.alasan, "") as alasantidakmasuk,
					TIME(ra.waktumasuk) as waktumasuk,
                	TIME(ra.waktukeluar) as waktukeluar,
					IF(ra.selisihmasuk<0,-1*ra.selisihmasuk ,0) as terlambat,
					IF(ra.selisihkeluar<0,-1*ra.selisihkeluar ,0) as pulangawal,
					ra.lamakerja,
					ra.lamalembur,
					ra.jenisjamkerja,
					IFNULL(ra.idjamkerjakhusus, "") as idjamkerjakhusus,
					IFNULL(ra.idharilibur, "") as idharilibur,
					ra.jadwalmasukkerja,
					p.id as startfrom
				FROM
					rekapabsen ra
					LEFT JOIN harilibur hl ON hl.id=ra.idharilibur
					LEFT JOIN alasantidakmasuk atm ON atm.id=ra.idalasantidakmasuk,
					pegawai p
				WHERE
					p.id=ra.idpegawai AND
					ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d")
					'.$sqlWhere.'
				'.$sqlHaving.'
				ORDER BY
					p.nama
				LIMIT '.config('consts.LIMIT_3_KOLOM');
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom_nama!='') {
			$stmt->bindValue(':startfrom_nama', $startfrom_nama);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
		}
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_OBJ);

		//total data
		$sql = 'SELECT
					p.nama,
					IFNULL(atm.alasan, "") as alasantidakmasuk
				FROM
					rekapabsen ra
					LEFT JOIN harilibur hl ON hl.id=ra.idharilibur
					LEFT JOIN alasantidakmasuk atm ON atm.id=ra.idalasantidakmasuk,
					pegawai p
				WHERE
					p.id=ra.idpegawai AND
					ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d")
					'.$sqlWhere.'
				'.$sqlHaving;
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom_nama != '') {
			$stmt->bindValue(':startfrom_nama', $startfrom_nama);
		}
		if ($having != '') {
			$having = '%' . $having . '%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
		}
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		//data shift
        $sql = 'SELECT
					rs.id,
					DATE_FORMAT(rs.tanggal,"%d/%m/%Y") as waktu,
					rs.idpegawai,
					IFNULL(jks.namashift,"") as jamkerjashift,
					CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
					p.nama as namapegawai,
					rs.masukkerja as masukkerja,
					TIME(rs.waktumasuk) as waktumasuk,
                	TIME(rs.waktukeluar) as waktukeluar,
					IF(rs.selisihmasuk<0,-1*rs.selisihmasuk ,0) as terlambat,
					IF(rs.selisihkeluar<0,-1*rs.selisihkeluar ,0) as pulangawal,
					rs.lamakerja,
					rs.lamalembur,
					p.id as startfrom
				FROM
					rekapshift rs
					LEFT JOIN jamkerjashift jks ON rs.idjamkerjashift=jks.id,
					pegawai p
				WHERE
					p.id=rs.idpegawai AND
					rs.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d")
					'.$sqlWhere.'
				ORDER BY
					p.nama
				LIMIT '.config('consts.LIMIT_3_KOLOM');
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggal', $tanggal);
        if ($startfrom_nama!='') {
            $stmt->bindValue(':startfrom_nama', $startfrom_nama);
        }
        $stmt->execute();
        $datashift = $stmt->fetchAll(PDO::FETCH_OBJ);

        //total data
        $sql = 'SELECT
					p.nama
				FROM
					rekapshift rs,
					pegawai p
				WHERE
					p.id=rs.idpegawai AND
					rs.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d")
					'.$sqlWhere;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggal', $tanggal);
        if ($startfrom_nama != '') {
            $stmt->bindValue(':startfrom_nama', $startfrom_nama);
        }
        $stmt->execute();
        $totaldatashift = $stmt->rowCount();
		return view('detailmore', ['datas' => $data, 'datashift' => $datashift, 'totaldata' => $totaldata, 'totaldatashift' => $totaldatashift, 'totaldatalimit' => config('consts.LIMIT_3_KOLOM'), 'tanggal' => $tanggal, 'more' => $more, 'detail' => 'rekap']);
	}

	public function alasan($tanggal)
	{
		$tanggal = $tanggal == 'o' ? '' : $tanggal;
		$atribut = Utils::getAtribut();
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'detail' => 'alasan', 'tanggal' => $tanggal, 'menu' => 'beranda']);
	}

	public function alasanAtributFilter(Request $request, $tanggal)
	{
		if(isset($request->atributnilai)) {
			Session::set('alasan_atributfilter', $request->atributnilai);
		}else{
			Session::forget('alasan_atributfilter');
		}
		if(isset($request->jamkerja)) {
			Session::set('alasan_jamkerja', $request->jamkerja);
		}else{
			Session::forget('alasan_jamkerja');
		}

        if(isset($request->kategorijamkerja)) {
            Session::set('alasan_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('alasan_kategorijamkerja');
        }
		//return redirect('alasan/'.$tanggal);
	}

	public function alasanPerTanggal($tanggal, $startfrom)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
		$having = '';
        $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)
        $waktu_eof = Utils::getCurrentDateTime($tanggal);

		$sqlHaving = '';
		if ($having!='') {
			$sqlHaving = 'HAVING (nama LIKE :having1 OR mesin LIKE :having2 OR alasanmasukkeluar LIKE :having3) ';
		}

		$sqlWhere = '';
		if ($startfrom!='') {
			$sqlWhere = ' AND CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),"_",DATE_FORMAT(la.inserted,"%Y%m%d%H%i%s")) < :startfrom ';
			$more = true;
		}

		if(Session::has('alasan_atributfilter')){
			$atributs = Session::get('alasan_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') )';
		}

        if(Session::has('alasan_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('alasan_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

		if(Session::has('alasan_jamkerja')) {
			if (Session::get('alasan_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(STR_TO_DATE(:tanggal, "%Y%m%d"),:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':tanggal', $tanggal);
				$stmt->bindValue(':jenisjamkerja', Session::get('alasan_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

		$sql = 'SELECT
					la.id,
					DATE_FORMAT(la.waktu,"%T") as waktu,
					la.idpegawai,
					CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
					p.nama as namapegawai,
					IFNULL(m.nama, "-") as mesin,
					IF(la.masukkeluar="m","<span class=\"label label-primary\">'.trans('all.masuk').'</span>","<span class=\"label label-danger\">'.trans('all.keluar').'</span>") as masukkeluar,
					IFNULL(amk.alasan, "") as alasanmasukkeluar,
					la.lat,
					la.lon,
					la.status,
					la.konfirmasi,
					la.inserted,
					la.updated,
					CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),"_",DATE_FORMAT(la.inserted,"%Y%m%d%H%i%s")) as startfrom
				FROM
					logabsen la
					LEFT JOIN mesin m ON m.id=la.idmesin
					LEFT JOIN alasanmasukkeluar amk ON amk.id=la.idalasanmasukkeluar,
					pegawai p
				WHERE
					ISNULL(la.idalasanmasukkeluar)=false AND
					la.status = "v" AND
					p.id=la.idpegawai AND
					la.waktu>=:tanggal01 AND la.waktu<=:tanggal02
					'.$sqlWhere.'
				'.$sqlHaving.'
				ORDER BY
					startfrom DESC
				LIMIT '.config('consts.LIMIT_6_KOLOM');
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal01', $waktu_eof['waktuawal']);
		$stmt->bindValue(':tanggal02', $waktu_eof['waktuakhir']);
		if ($startfrom!='') {
			$stmt->bindValue(':startfrom', $startfrom);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
			$stmt->bindValue(':having3', $having);
		}
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_OBJ);

		$sql = 'SELECT
					p.nama,
					IFNULL(m.nama, "-") as mesin,
					IFNULL(amk.alasan, "") as alasanmasukkeluar
				FROM
					logabsen la
					LEFT JOIN mesin m ON m.id=la.idmesin
					LEFT JOIN alasanmasukkeluar amk ON amk.id=la.idalasanmasukkeluar,
					pegawai p
				WHERE
					ISNULL(la.idalasanmasukkeluar)=false AND
					p.id=la.idpegawai AND
					la.status = "v" AND
					la.waktu>=:tanggal01 AND la.waktu<=:tanggal02
					'.$sqlWhere.'
				'.$sqlHaving;
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal01', $waktu_eof['waktuawal']);
		$stmt->bindValue(':tanggal02', $waktu_eof['waktuakhir']);
		if ($startfrom!='') {
			$stmt->bindValue(':startfrom', $startfrom);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
			$stmt->bindValue(':having3', $having);
		}
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		return view('detailmore', ['datas' => $data, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'tanggal' => $tanggal, 'more' => $more, 'detail' => 'alasan', 'menu' => 'beranda']);
	}

	public function peta($tanggal)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sql = 'SELECT id,nama,lat,lon FROM lokasi';
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$lokasi = $stmt->fetchAll(PDO::FETCH_OBJ);

		$tanggal = $tanggal == 'o' ? '' : $tanggal;

		$atribut = Utils::getAtribut();
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'detail' => 'peta', 'tanggal' => $tanggal, 'lokasi' => $lokasi, 'menu' => 'beranda']);
	}

	public function petaAtributFilter(Request $request, $tanggal)
	{
		if(isset($request->atributnilai)) {
			Session::set('peta_atributfilter', $request->atributnilai);
		}else{
			Session::forget('peta_atributfilter');
		}
		if(isset($request->jamkerja)) {
			Session::set('peta_jamkerja', $request->jamkerja);
		}else{
			Session::forget('peta_jamkerja');
		}

        if(isset($request->kategorijamkerja)) {
            Session::set('peta_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('peta_kategorijamkerja');
        }
		//return redirect('peta/'.$tanggal);
	}

	public function petaPerTanggal($tanggal, $startfrom)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
		$having = '';

		$sqlWhere = '';
		if(Session::has('peta_atributfilter')){
			$atributs = Session::get('peta_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') )';
		}

        if(Session::has('peta_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('peta_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

		if(Session::has('peta_jamkerja')) {
			if (Session::get('peta_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(STR_TO_DATE(:tanggal, "%Y%m%d"),:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':tanggal', $tanggal);
				$stmt->bindValue(':jenisjamkerja', Session::get('peta_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		if(Session::has('peta_pencarian_detail')){
			$sqlWhere .= ' AND p.nama LIKE "%'.Session::get('peta_pencarian_detail').'%" ';
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

		$sql = 'SELECT
					l.id,
					l.lat,
					l.lon as lng
				FROM
					logabsen l,
					pegawai p
				WHERE
					l.idpegawai=p.id AND
					l.status = "v" AND
					DATE(l.waktu)=STR_TO_DATE(:tanggal, "%Y%m%d") AND
					ISNULL(l.lat)=false AND
					ISNULL(l.lon)=false AND
					l.lat<>0 AND
					l.lon<>0'.$sqlWhere;
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom!='') {
			$stmt->bindValue(':startfrom', $startfrom);
		}
		if ($having!='') {
			$having = '%'.$having.'%';
			$stmt->bindValue(':having1', $having);
			$stmt->bindValue(':having2', $having);
			$stmt->bindValue(':having3', $having);
		}
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_OBJ);

		return response()->json($data);
	}

	public function peringkatAtributFilter(Request $request, $jenis, $startfrom)
	{
		if(isset($request->atributnilai)) {
			Session::set($jenis.'_atributfilter', $request->atributnilai);
		}else{
			Session::forget($jenis.'_atributfilter');
		}
		if(isset($request->jamkerja)) {
			Session::set('peringkat_jamkerja', $request->jamkerja);
		}else{
			Session::forget('peringkat_jamkerja');
		}
        if(isset($request->kategorijamkerja)) {
            Session::set('peringkat_kategorijamkerja', $request->kategorijamkerja);
        }else{
            Session::forget('peringkat_kategorijamkerja');
        }
		return redirect('peringkat/'.$jenis.'/'.$startfrom);
	}

	public function peringkat($jenis, $startfrom, $dari='')
	{
        $data = '';
		$having = '';
		$sqlWhere = '';
		$totaldata = 0;
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$currentdate = Utils::getCurrentDate();

		if(Session::has($jenis.'_atributfilter')){
			$atributs = Session::get($jenis.'_atributfilter');
			$atributnilai = Utils::atributNilai($atributs);
			$sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') )';
		}

        if(Session::has('peringkat_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('peringkat_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

		if(Session::has('peringkat_jamkerja')) {
			if (Session::get('peringkat_jamkerja') != '') {
				$sql = 'CALL pegawaijenisjamkerja(:currentdate,:jenisjamkerja)';
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':currentdate', $currentdate);
				$stmt->bindValue(':jenisjamkerja', Session::get('peringkat_jamkerja'));
				$stmt->execute();

				$sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
			}
		}

		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

		if(Session::has($jenis.'_pencarian_detail')){
			$sqlWhere .= ' AND p.nama LIKE "%'.Session::get($jenis.'_pencarian_detail').'%" ';
		}

		if($jenis == 'peringkatterbaik'){

			if ($startfrom!='') {
				$sqlWhere .= ' AND pra.peringkat > :startfrom ';
			}

			$sqlHaving = '';
			if ($having!='') {
				$sqlHaving = 'HAVING nama LIKE :having1 OR atribut LIKE :having2 ';
			}

			// select untuk data
			$sql = 'SELECT
						pra.peringkat,
						p.id as idpegawai,
						CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
						p.nama as namapegawai,
						getatributtampilpadaringkasan(p.id) as atribut,
						pra.masukkerja,
						pra.lamakerja,
						pra.lamalembur,
						pra.terlambat,
						pra.terlambatlama,
						pra.pulangawal,
						pra.pulangawallama,
						pra.peringkat as startfrom
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
						'.$sqlWhere.'
					GROUP BY
						p.id
					'.$sqlHaving.'
					ORDER BY
						pra.peringkat ASC
					LIMIT '.config('consts.LIMIT_3_KOLOM');
			$stmt = $pdo->prepare($sql);
            if ($startfrom!='') {
				$stmt->bindValue(':startfrom', $startfrom);
			}
			if ($having!='') {
				$having = '%'.$having.'%';
				$stmt->bindValue(':having1', $having);
				$stmt->bindValue(':having2', $having);
			}
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_OBJ);

			// select untuk total
			$sql = 'SELECT
						p.id
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
						'.$sqlWhere.'
					GROUP BY
						p.id
					'.$sqlHaving;
			$stmt = $pdo->prepare($sql);
            if ($startfrom!='') {
				$stmt->bindValue(':startfrom', $startfrom);
			}
			if ($having!='') {
				$having = '%'.$having.'%';
				$stmt->bindValue(':having1', $having);
				$stmt->bindValue(':having2', $having);
			}
			$stmt->execute();
			$totaldata = $stmt->rowCount();

        }else if($jenis == 'peringkatterlambat'){

            if ($startfrom!='') {
				$sqlWhere .= ' AND CONCAT(LPAD(pra.terlambat,5,"0"),"_",LPAD(pra.terlambatlama,14,"0"),"_",LPAD(pra.peringkat,5,"0")) < :startfrom ';
			}

			$sqlHaving = '';
			if ($having!='') {
				$sqlHaving = 'HAVING nama LIKE :having1 OR atribut LIKE :having2 ';
			}

			// select untuk data
			$sql = 'SELECT
						pra.peringkat,
						p.id as idpegawai,
						CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
						p.nama as namapegawai,
						getatributtampilpadaringkasan(p.id) as atribut,
						pra.terlambat,
						pra.terlambatlama,
						CONCAT(LPAD(pra.terlambat,5,"0"),"_",LPAD(pra.terlambatlama,14,"0"),"_",LPAD(pra.peringkat,5,"0")) as startfrom
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
						'.$sqlWhere.'
					GROUP BY
						p.id
					'.$sqlHaving.'
					ORDER BY
						pra.terlambat DESC,
						pra.terlambatlama DESC,
						pra.peringkat
					LIMIT '.config('consts.LIMIT_3_KOLOM');
			$stmt = $pdo->prepare($sql);
            if ($startfrom!='') {
				$stmt->bindValue(':startfrom', $startfrom);
			}
			if ($having!='') {
				$having = '%'.$having.'%';
				$stmt->bindValue(':having1', $having);
				$stmt->bindValue(':having2', $having);
			}
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_OBJ);

            //select untuk total
            $sql = 'SELECT
						p.id
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
						'.$sqlWhere.'
					GROUP BY
						p.id
					'.$sqlHaving;
            $stmt = $pdo->prepare($sql);
            if ($startfrom!='') {
                $stmt->bindValue(':startfrom', $startfrom);
            }
            if ($having!='') {
                $having = '%'.$having.'%';
                $stmt->bindValue(':having1', $having);
                $stmt->bindValue(':having2', $having);
            }
            $stmt->execute();
            $totaldata = $stmt->rowCount();

        }else if($jenis == 'peringkatpulangawal'){

            if ($startfrom!='') {
				$sqlWhere .= ' AND CONCAT(LPAD(pra.pulangawal,5,"0"),"_",LPAD(pra.pulangawallama,14,"0"),"_",LPAD(pra.peringkat,5,"0")) < :startfrom ';
			}

			$sqlHaving = '';
			if ($having!='') {
				$sqlHaving = 'HAVING nama LIKE :having1 OR atribut LIKE :having2 ';
			}

			// select untuk data
			$sql = 'SELECT
						pra.peringkat,
						p.id as idpegawai,
						CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
						p.nama as namapegawai,
						getatributtampilpadaringkasan(p.id) as atribut,
						pra.pulangawal,
						pra.pulangawallama,
						CONCAT(LPAD(pra.pulangawal,5,"0"),"_",LPAD(pra.pulangawallama,14,"0"),"_",LPAD(pra.peringkat,5,"0")) as startfrom
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
						'.$sqlWhere.'
					GROUP BY
						p.id
					'.$sqlHaving.'
					ORDER BY
						pra.pulangawal DESC,
						pra.pulangawallama DESC,
						pra.peringkat
					LIMIT '.config('consts.LIMIT_3_KOLOM');
			$stmt = $pdo->prepare($sql);
            if ($startfrom!='') {
				$stmt->bindValue(':startfrom', $startfrom);
			}
			if ($having!='') {
				$having = '%'.$having.'%';
				$stmt->bindValue(':having1', $having);
				$stmt->bindValue(':having2', $having);
			}
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_OBJ);

            //select untuk totaldata
            $sql = 'SELECT
						p.id
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
						'.$sqlWhere.'
					GROUP BY
						p.id
					'.$sqlHaving;
            $stmt = $pdo->prepare($sql);
            if ($startfrom!='') {
                $stmt->bindValue(':startfrom', $startfrom);
            }
            if ($having!='') {
                $having = '%'.$having.'%';
                $stmt->bindValue(':having1', $having);
                $stmt->bindValue(':having2', $having);
            }
            $stmt->execute();
            $totaldata = $stmt->rowCount();

        }else if($jenis == 'peringkatlamakerja'){

			if ($startfrom!='') {
				$sqlWhere .= ' AND CONCAT(LPAD(pra.lamakerja,14,"0"),"_",LPAD(pra.peringkat,5,"0")) < :startfrom ';
			}

			$sqlHaving = '';
			if ($having!='') {
				$sqlHaving = 'HAVING nama LIKE :having1 OR atribut LIKE :having2 ';
			}

			// select untuk data
			$sql = 'SELECT
						pra.peringkat,
						p.id as idpegawai,
						CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
						p.nama as namapegawai,
						getatributtampilpadaringkasan(p.id) as atribut,
						pra.lamakerja,
						CONCAT(LPAD(pra.lamakerja,14,"0"),"_",LPAD(pra.peringkat,5,"0")) as startfrom
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
						'.$sqlWhere.'
					GROUP BY
						p.id
					'.$sqlHaving.'
					ORDER BY
						pra.lamakerja DESC,
						pra.peringkat
					LIMIT '.config('consts.LIMIT_3_KOLOM');
			$stmt = $pdo->prepare($sql);
            if ($startfrom!='') {
				$stmt->bindValue(':startfrom', $startfrom);
			}
			if ($having!='') {
				$having = '%'.$having.'%';
				$stmt->bindValue(':having1', $having);
				$stmt->bindValue(':having2', $having);
			}
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_OBJ);

            // select untuk totaldata
            $sql = 'SELECT
						p.id
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
						'.$sqlWhere.'
					GROUP BY
						p.id
					'.$sqlHaving;
            $stmt = $pdo->prepare($sql);
            if ($startfrom!='') {
                $stmt->bindValue(':startfrom', $startfrom);
            }
            if ($having!='') {
                $having = '%'.$having.'%';
                $stmt->bindValue(':having1', $having);
                $stmt->bindValue(':having2', $having);
            }
            $stmt->execute();
            $totaldata = $stmt->rowCount();

        }else if($jenis == 'peringkatlamalembur'){

			if ($startfrom!='') {
				$sqlWhere .= ' AND CONCAT(LPAD(pra.lamalembur,14,"0"),"_",LPAD(pra.peringkat,5,"0")) < :startfrom ';
			}

			$sqlHaving = '';
			if ($having!='') {
				$sqlHaving = 'HAVING nama LIKE :having1 OR atribut LIKE :having2 ';
			}

			// select untuk data
			$sql = 'SELECT
						pra.peringkat,
						p.id as idpegawai,
						CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
						p.nama as namapegawai,
						getatributtampilpadaringkasan(p.id) as atribut,
						pra.lamalembur,
						CONCAT(LPAD(pra.lamalembur,14,"0"),"_",LPAD(pra.peringkat,5,"0")) as startfrom
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
						'.$sqlWhere.'
					GROUP BY
						p.id
					'.$sqlHaving.'
					ORDER BY
						pra.lamalembur DESC,
						pra.peringkat
					LIMIT '.config('consts.LIMIT_3_KOLOM');
			$stmt = $pdo->prepare($sql);
            if ($startfrom!='') {
				$stmt->bindValue(':startfrom', $startfrom);
			}
			if ($having!='') {
				$having = '%'.$having.'%';
				$stmt->bindValue(':having1', $having);
				$stmt->bindValue(':having2', $having);
			}
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_OBJ);

            // select untuk totaldata
            $sql = 'SELECT
						p.id
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
						'.$sqlWhere.'
					GROUP BY
						p.id
					'.$sqlHaving;
            $stmt = $pdo->prepare($sql);
            if ($startfrom!='') {
                $stmt->bindValue(':startfrom', $startfrom);
            }
            if ($having!='') {
                $having = '%'.$having.'%';
                $stmt->bindValue(':having1', $having);
                $stmt->bindValue(':having2', $having);
            }
            $stmt->execute();
            $totaldata = $stmt->rowCount();

		}

		$atribut = Utils::getAtribut();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');

        if ($startfrom!='') {
            return view('peringkatmore', ['data' => $data, 'totaldata' => $totaldata, 'jenis' => $jenis]);
		}else {
			if($dari != '') {
			    return view('peringkatmore', ['data' => $data, 'totaldata' => $totaldata, 'jenis' => $jenis]);
			}else {
				return view('peringkat', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'data' => $data, 'totaldata' => $totaldata, 'jenis' => $jenis, 'menu' => 'beranda']);
			}
		}
	}

    public function peringkatExcel($jenis)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $objPHPExcel = new PHPExcel();
        $currentdate = Utils::getCurrentDate();

        Utils::setPropertiesExcel($objPHPExcel,trans('all.'.str_replace('peringkat','',$jenis)));

        $sqlWhere = '';
        if(Session::has($jenis.'_pencarian_detail')){
            $sqlWhere .= ' AND p.nama LIKE "%'.Session::get($jenis.'_pencarian_detail').'%" ';
        }

        if(Session::has($jenis.'_atributfilter')){
            $atributs = Session::get($jenis.'_atributfilter');
            $atributnilai = Utils::atributNilai($atributs);
            $sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') )';
        }

        if(Session::has('peringkat_kategorijamkerja')){
            $kategorijamkerja = Utils::splitArray(Session::get('peringkat_kategorijamkerja'));
            $sqlWhere .= ' AND p.id IN (SELECT pj.idpegawai FROM pegawaijamkerja pj, jamkerja j WHERE pj.idjamkerja=j.id AND j.idkategori IN ('.$kategorijamkerja.'))';
        }

        if(Session::has('peringkat_jamkerja')) {
            if (Session::get('peringkat_jamkerja') != '') {
                $sql = 'CALL pegawaijenisjamkerja(:currentdate,:jenisjamkerja)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':currentdate', $currentdate);
                $stmt->bindValue(':jenisjamkerja', Session::get('peringkat_jamkerja'));
                $stmt->execute();

                $sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
            }
        }

        $arrstrreplaceatributpegawai = array('<b>','</b>');
        $arrWidth = array();
        if($jenis == 'peringkatterbaik') {
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.peringkatglobal'))
                        ->setCellValue('B1', trans('all.peringkatfilter'))
                        ->setCellValue('C1', trans('all.nama'))
                        ->setCellValue('D1', trans('all.atribut'))
                        ->setCellValue('E1', trans('all.masukkerja'))
                        ->setCellValue('F1', trans('all.terlambat'))
                        ->setCellValue('G1', trans('all.pulangawal'))
                        ->setCellValue('H1', trans('all.lamakerja'))
                        ->setCellValue('I1', trans('all.lamalembur'));

            $sql = 'SELECT
                        pra.peringkat,
                        p.nama,
                        pra.masukkerja,
                        ROUND(pra.lamakerja, 1) as lamakerja,
                        ROUND(pra.lamalembur, 1) as lamalembur,
                        pra.terlambat,
                        ROUND(pra.terlambatlama, 1) as terlambatlama,
                        pra.pulangawal,
                        ROUND(pra.pulangawallama, 1) as pulangawallama,
                        getatributpegawai_all(p.id) as atributpegawai
                    FROM
                        _peringkatabsen pra,
                        pegawai p
                    WHERE
                        pra.idpegawai=p.id
                        '.$sqlWhere.'
                    GROUP BY
                        p.id
                    ORDER BY
                        pra.peringkat ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            $peringkatfilter = 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $masukkerja = $row['masukkerja'] . ' ' . trans('all.hari');
                $terlambat = $row['terlambat'] . ' ' . trans('all.kali') . ' (' . Utils::sec2pretty($row['terlambatlama']) . ')';
                $pulangawal = $row['pulangawal'] . ' ' . trans('all.kali') . ' (' . Utils::sec2pretty($row['pulangawallama']) . ')';
                $lamakerja = Utils::sec2pretty($row['lamakerja']);
                $lamalembur = Utils::sec2pretty($row['lamalembur']);

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['peringkat']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $peringkatfilter);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, str_replace($arrstrreplaceatributpegawai,'',$row['atributpegawai']));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $masukkerja);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $terlambat);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $pulangawal);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $lamakerja);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $lamalembur);

                $i++;
                $peringkatfilter++;
            }

            $arrWidth = array(15, 15, 30, 30, 15, 20, 15, 15, 15);
        }else if($jenis == 'peringkatterlambat'){
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.peringkatglobal'))
                        ->setCellValue('B1', trans('all.peringkatfilter'))
                        ->setCellValue('C1', trans('all.nama'))
                        ->setCellValue('D1', trans('all.atribut'))
                        ->setCellValue('E1', trans('all.terlambat'));

            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $pdo->exec('
                set @i_terlambat = 0;
                DROP TEMPORARY TABLE IF EXISTS temp_peringkatterlambat;
                CREATE TEMPORARY TABLE temp_peringkatterlambat (
                    id               INT UNSIGNED NOT NULL,
                    peringkat        INT UNSIGNED NOT NULL,
                    nama             VARCHAR(100) NOT NULL,
                    terlambat        INT UNSIGNED NOT NULL,
                    terlambatlama    INT UNSIGNED NOT NULL
                ) Engine=Memory;
                INSERT INTO temp_peringkatterlambat
                    SELECT
                        p.id,
						@i_terlambat := @i_terlambat + 1,
						p.nama,
						pra.terlambat,
						ROUND(pra.terlambatlama, 1)
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
					ORDER BY
						pra.terlambat DESC,
						pra.terlambatlama DESC,
						pra.peringkat;
            ');

            $sql = 'SELECT
						p.peringkat,
						p.nama,
						p.terlambat,
						p.terlambatlama,
						getatributpegawai_all(p.id) as atributpegawai
					FROM
						temp_peringkatterlambat p
					WHERE
					    1=1
						'.$sqlWhere;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            $peringkatfilter = 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $terlambat = $row['terlambat'] . ' ' . trans('all.kali') . ' (' . Utils::sec2pretty($row['terlambatlama']) . ')';

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['peringkat']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $peringkatfilter);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, str_replace($arrstrreplaceatributpegawai,'',$row['atributpegawai']));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $terlambat);

                $i++;
                $peringkatfilter++;
            }

            $arrWidth = array(15, 15, 30, 30, 20);
        }else if($jenis == 'peringkatpulangawal'){
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.peringkatglobal'))
                        ->setCellValue('B1', trans('all.peringkatfilter'))
                        ->setCellValue('C1', trans('all.nama'))
                        ->setCellValue('D1', trans('all.atribut'))
                        ->setCellValue('E1', trans('all.pulangawal'));

            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $pdo->exec('
                set @i_pulangawal = 0;
                DROP TEMPORARY TABLE IF EXISTS temp_peringkatpulangawal;
                CREATE TEMPORARY TABLE temp_peringkatpulangawal (
                    id               INT UNSIGNED NOT NULL,
                    peringkat        INT UNSIGNED NOT NULL,
                    nama             VARCHAR(100) NOT NULL,
                    pulangawal        INT UNSIGNED NOT NULL,
                    pulangawallama    INT UNSIGNED NOT NULL
                ) Engine=Memory;
                INSERT INTO temp_peringkatpulangawal
                    SELECT
                        p.id,
						@i_pulangawal := @i_pulangawal + 1,
						p.nama,
						pra.pulangawal,
						ROUND(pra.pulangawallama, 1)
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
					ORDER BY
						pra.pulangawal DESC,
						pra.pulangawallama DESC,
						pra.peringkat;
            ');

            $sql = 'SELECT
						p.peringkat,
						p.nama,
						p.pulangawal,
						p.pulangawallama,
						getatributpegawai_all(p.id) as atributpegawai
					FROM
						temp_peringkatpulangawal p
					WHERE
					    1=1
						'.$sqlWhere;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            $peringkatfilter = 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pulangawal = $row['pulangawal'] . ' ' . trans('all.kali') . ' (' . Utils::sec2pretty($row['pulangawallama']) . ')';

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['peringkat']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $peringkatfilter);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, str_replace($arrstrreplaceatributpegawai,'',$row['atributpegawai']));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $pulangawal);

                $i++;
                $peringkatfilter++;
            }

            $arrWidth = array(15, 15, 30, 30, 20);
        }else if($jenis == 'peringkatlamakerja'){
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.peringkatglobal'))
                        ->setCellValue('B1', trans('all.peringkatfilter'))
                        ->setCellValue('C1', trans('all.nama'))
                        ->setCellValue('D1', trans('all.atribut'))
                        ->setCellValue('E1', trans('all.lamakerja'));

            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $pdo->exec('
                set @i_lamakerja = 0;
                DROP TEMPORARY TABLE IF EXISTS temp_peringkatlamakerja;
                CREATE TEMPORARY TABLE temp_peringkatlamakerja (
                    id               INT UNSIGNED NOT NULL,
                    peringkat        INT UNSIGNED NOT NULL,
                    nama             VARCHAR(100) NOT NULL,
                    lamakerja        INT UNSIGNED NOT NULL
                ) Engine=Memory;
                INSERT INTO temp_peringkatlamakerja
                    SELECT
                        p.id,
						@i_lamakerja := @i_lamakerja + 1,
						p.nama,
						pra.lamakerja
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
					ORDER BY
						pra.lamakerja DESC,
						pra.peringkat;
            ');

            $sql = 'SELECT
						p.peringkat,
						p.nama,
						p.lamakerja,
						getatributpegawai_all(p.id) as atributpegawai
					FROM
						temp_peringkatlamakerja p
					WHERE
					    1=1
						'.$sqlWhere;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            $peringkatfilter = 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lamakerja = Utils::sec2pretty($row['lamakerja']);

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['peringkat']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $peringkatfilter);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, str_replace($arrstrreplaceatributpegawai,'',$row['atributpegawai']));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $lamakerja);

                $i++;
                $peringkatfilter++;
            }

            $arrWidth = array(15, 15, 30, 30, 20);
        }else if($jenis == 'peringkatlamalembur'){
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.peringkatglobal'))
                        ->setCellValue('B1', trans('all.peringkatfilter'))
                        ->setCellValue('C1', trans('all.nama'))
                        ->setCellValue('D1', trans('all.atribut'))
                        ->setCellValue('E1', trans('all.lamalembur'));

            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $pdo->exec('
                set @i_lamalembur = 0;
                DROP TEMPORARY TABLE IF EXISTS temp_peringkatlamalembur;
                CREATE TEMPORARY TABLE temp_peringkatlamalembur (
                    id               INT UNSIGNED NOT NULL,
                    peringkat        INT UNSIGNED NOT NULL,
                    nama             VARCHAR(100) NOT NULL,
                    lamalembur        INT UNSIGNED NOT NULL
                ) Engine=Memory;
                INSERT INTO temp_peringkatlamalembur
                    SELECT
                        p.id,
						@i_lamalembur := @i_lamalembur + 1,
						p.nama,
						pra.lamalembur
					FROM
						_peringkatabsen pra,
						pegawai p
					WHERE
						pra.idpegawai=p.id
					ORDER BY
						pra.lamalembur DESC,
						pra.peringkat;
            ');

            $sql = 'SELECT
						p.peringkat,
						p.nama,
						p.lamalembur,
						getatributpegawai_all(p.id) as atributpegawai
					FROM
						temp_peringkatlamalembur p
					WHERE
					    1=1
						'.$sqlWhere;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            $peringkatfilter = 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lamalembur = Utils::sec2pretty($row['lamalembur']);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['peringkat']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $peringkatfilter);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, str_replace($arrstrreplaceatributpegawai,'',$row['atributpegawai']));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $lamalembur);

                $i++;
                $peringkatfilter++;
            }

            $arrWidth = array(15, 15, 30, 30, 20);
        }
        Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);

        Utils::passwordExcel($objPHPExcel);
        Utils::setFileNameExcel(trans('all.'.str_replace('peringkat','',$jenis)));
        $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $writer->save('php://output');
    }

    public function logAbsenPegawai($idpegawai, $startfrom, $yymm)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $startfrom = $startfrom == 'o' ? '' : $startfrom;
		//untuk membedakan ini load awal atau load dari ajax ganti bulan
		$paramyymm = $yymm;
        $yymm = $yymm == 'o' ? date('ym') : $yymm;

        $sqlWhere = '';
        if ($startfrom!='') {
            $sqlWhere = ' AND CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),"_",DATE_FORMAT(la.inserted,"%Y%m%d%H%i%s")) < :startfrom ';
        }

        $sql = 'SELECT
					la.id,
					la.waktu,
					IFNULL(m.nama, "-") as mesin,
					IF(la.masukkeluar="m","<span class=\"label label-primary\">'.trans('all.masuk').'</span>","<span class=\"label label-danger\">'.trans('all.keluar').'</span>") as masukkeluar,
					IFNULL(amk.alasan, "") as alasanmasukkeluar,
					la.lat,
					la.lon,
					la.status,
					la.konfirmasi,
					la.inserted,
					la.updated,
					CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),"_",DATE_FORMAT(la.inserted,"%Y%m%d%H%i%s")) as startfrom
				FROM
					logabsen la
					LEFT JOIN mesin m ON m.id=la.idmesin
					LEFT JOIN alasanmasukkeluar amk ON amk.id=la.idalasanmasukkeluar
				WHERE
					la.idpegawai=:idpegawai AND
					DATE_FORMAT(la.waktu, "%y%m") = :yymm
					'.$sqlWhere.'
				ORDER BY
					startfrom DESC
				LIMIT '.config('consts.LIMIT_FOTO');
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':yymm', $yymm);
        if ($startfrom!='') {
            $stmt->bindValue(':startfrom', $startfrom);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        $sql = 'SELECT
					la.id
				FROM
					logabsen la
					LEFT JOIN mesin m ON m.id=la.idmesin
					LEFT JOIN alasanmasukkeluar amk ON amk.id=la.idalasanmasukkeluar
				WHERE
					la.idpegawai=:idpegawai AND
					DATE_FORMAT(la.waktu, "%y%m") = :yymm
					'.$sqlWhere;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':yymm', $yymm);
        if ($startfrom!='') {
            $stmt->bindValue(':startfrom', $startfrom);
        }
        $stmt->execute();
        $totaldata = $stmt->rowCount();

		$namapegawai = Utils::getNamaPegawai($idpegawai);
		$listyymm = Utils::list_yymm();
//		if($startfrom != ''){
//			return view('detailberandamore', ['data' => $data, 'idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'totaldata' => $totaldata, 'jenis' => 'logabsen']);
//		}else{
//        	return view('detailberanda', ['data' => $data, 'listyymm' => $listyymm, 'yymm' => $yymm, 'paramyymm' => $paramyymm, 'idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'totaldata' => $totaldata, 'jenis' => 'logabsen']);
//		}
        return view('detailberanda', ['data' => $data, 'listyymm' => $listyymm, 'yymm' => $yymm, 'paramyymm' => $paramyymm, 'idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'totaldata' => $totaldata, 'jenis' => 'logabsen']);
    }

    public function rekapAbsenPegawai($idpegawai, $startfrom, $yymm)
    {
        //startfrom tidak digunakan disini cuma sebagai pelengkap dan startfrom gunanya untuk keperluan loadmore saja
		$pdo = DB::connection('perusahaan_db')->getPdo();
		//untuk membedakan ini load awal atau load dari ajax ganti bulan
		$paramyymm = $yymm;
		$yymm = $yymm == 'o' ? date('ym') : $yymm;

        $sql = 'SELECT
					ra.id,
					ra.tanggal as waktu,
					ra.idpegawai,
					IFNULL(hl.keterangan,"") as harilibur,
					ra.masukkerja as masukkerja,
					IFNULL(atm.alasan, "") as alasantidakmasuk,
					DATE_FORMAT(ra.waktumasuk, "%T") as waktumasuk,
					DATE_FORMAT(ra.waktukeluar, "%T") as waktukeluar,
					IF(ra.selisihmasuk<0,-1*ra.selisihmasuk ,0) as terlambat,
					IF(ra.selisihkeluar<0,-1*ra.selisihkeluar ,0) as pulangawal,
					ra.lamakerja,
					ra.lamalembur
				FROM
					rekapabsen ra
					LEFT JOIN harilibur hl ON hl.id=ra.idharilibur
					LEFT JOIN alasantidakmasuk atm ON atm.id=ra.idalasantidakmasuk
				WHERE
					ra.idpegawai=:idpegawai AND
					DATE_FORMAT(ra.tanggal, "%y%m") = :yymm
				ORDER BY
					ra.tanggal DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':yymm', $yymm);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        $sql = 'SELECT
					ra.id
				FROM
					rekapabsen ra
					LEFT JOIN harilibur hl ON hl.id=ra.idharilibur
					LEFT JOIN alasantidakmasuk atm ON atm.id=ra.idalasantidakmasuk
				WHERE
					ra.idpegawai=:idpegawai AND
					DATE_FORMAT(ra.tanggal, "%y%m") = :yymm';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':yymm', $yymm);
        $stmt->execute();
        $totaldata = $stmt->rowCount();

		//nama pegawai
		$namapegawai = Utils::getNamaPegawai($idpegawai);
		$listyymm = Utils::list_yymm();

        return view('detailberanda', ['data' => $data, 'listyymm' => $listyymm, 'yymm' => $yymm, 'paramyymm' => $paramyymm, 'idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'totaldata' => $totaldata, 'jenis' => 'rekapabsen']);
    }

    public function flagLogAbsen($idpegawai,$idlogabsen,$menu)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,DATE_FORMAT(waktu,"%d/%m/%Y %T") as waktu,flag,flag_keterangan FROM logabsen WHERE id = :idlogabsen';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idlogabsen', $idlogabsen);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_OBJ);
        $namapegawai = Utils::getNamaPegawai($idpegawai);
        return view('include/flaglogabsen', ['data' => $data, 'menu' => $menu, 'nama' => $namapegawai]);
    }

    public function flagLogAbsenSubmit(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'unknown';
        $data = Utils::getDataWhere($pdo,'logabsen','waktu','id',$request->idlogabsen); // berisi waktu logabsen
        if($data != ''){
            $cekbolehubah = Utils::cekKunciDataPosting($data);
            if($cekbolehubah == 0) {
                $sql = 'UPDATE logabsen SET flag = :flag, flag_keterangan = :flagketerangan WHERE id = :idlogabsen';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':flag', $request->flag);
                $stmt->bindValue(':flagketerangan', $request->flagketerangan);
                $stmt->bindValue(':idlogabsen', $request->idlogabsen);
                $stmt->execute();

                $sql = 'CALL hitungrekapabsen_log(:idlogabsen, NULL, NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idlogabsen', $request->idlogabsen);
                $stmt->execute();

                $response['status'] = 'ok';
                $response['msg'] = trans('all.databerhasildisimpan');
            } else {
                $response['msg'] = trans('all.datatidakbisadirubah').Utils::getDataWhere($pdo,'pengaturan','kuncidatasebelumtanggal');
            }
        }else{
            $response['msg'] = trans('all.datatidakditemukan');
        }
        return $response;
    }

    public function popupMarkerPeta(Request $request){

		$idlogabsens = $request->marker;
		$pdo = DB::connection('perusahaan_db')->getPdo();

		$startfrom = $request->startfrom;
		$where = '';
		if($startfrom != 'o'){
			$where = ' AND l.id < '.$startfrom;
		}

		$sql = 'SELECT
					l.id,
					DATE(l.waktu) as tanggal,
					TIME(l.waktu) as jam,
					p.nama,
					l.masukkeluar,
					IFNULL(m.nama,"-") as mesin,
					l.status
				FROM
					logabsen l
					LEFT JOIN mesin m ON l.idmesin=m.id,
					pegawai p
				WHERE
					l.idpegawai=p.id AND
					l.status = "v" AND
					l.id IN('.$idlogabsens.')
					'.$where.'
				ORDER BY
					l.id DESC
				LIMIT 10';
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_OBJ);

		//total data
		$sql = 'SELECT
					l.id
				FROM
					logabsen l,
					pegawai p
				WHERE
					l.idpegawai=p.id AND
					l.status = "v" AND
					l.id IN('.$idlogabsens.')
					'.$where.'
				ORDER BY
					l.id DESC';
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		return view('popupmarkerpeta', ['data' => $data, 'totaldata' => $totaldata, 'idlogabsen' => $idlogabsens]);
	}

	//$menu adalah, pendeteksi apakah dia berasal dari menu atau bukan contoh berasal dari menu konfirmasiflag
	public function detailKonfirmasiAbsen($jenis,$idkonfirmasiabsen,$menu,$dari="popup")
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$currentdate = Utils::getCurrentDate();
		$hasil = '';
		$datafacesample = '';
		if($jenis == 'logabsen'){
			$sql = 'SELECT
						la.id as idkonfirmasiabsen,
						pg.id as idpegawai,
						pg.nama,
						la.lat,
						la.lon,
						IFNULL(amk.alasan,"") as alasan,
						IF(la.konfirmasi="l","'.trans('all.lokasitidakcocok').'",IF(la.konfirmasi="f","'.trans('all.sampelwajahtidakcocok').'",IF(la.konfirmasi="lf" or la.konfirmasi="fl","'.trans('all.lokasitidakcocok').', '.trans('all.sampelwajahtidakcocok').'","-"))) as konfirmasi,
						DATE_FORMAT(la.waktu,"%d/%m/%Y %T") as waktu,
						if(la.masukkeluar="m","<label class=\"label label-primary\">'.trans('all.masuk').'</label>","<label class=\"label label-danger\">'.trans('all.keluar').'</label>") as masukkeluar,
						a.nilai
					FROM
						logabsen la
						LEFT JOIN alasanmasukkeluar amk ON la.idalasanmasukkeluar=amk.id,
						pegawai pg
						LEFT JOIN pegawaiatribut pa ON pa.idpegawai=pg.id
						LEFT JOIN atributnilai a ON pa.idatributnilai=a.id
					WHERE
						la.idpegawai=pg.id AND
						la.id = :idkonfirmasiabsen';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':idkonfirmasiabsen', $idkonfirmasiabsen);
			$stmt->execute();
			$hasil = $stmt->fetch(PDO::FETCH_OBJ);

            // select facesample
            $sql = 'SELECT id, filename, checksum FROM facesample WHERE idpegawai=:idpegawai';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam('idpegawai', $hasil->idpegawai);
            $stmt->execute();
            $datafacesample = $stmt->fetchAll(PDO::FETCH_OBJ);
		}

		if($jenis == 'konfirmasiflag'){
		    $sql = 'SELECT
						kf.id as idkonfirmasiabsen,
						IFNULL(la.id,"") as idlogabsen,
						pg.id as idpegawai,
						pg.nama,
						IFNULL(la.lat,"") as lat,
						IFNULL(la.lon,"") as lon,
						kf.flag,
						kf.keterangan,
						IF(ISNULL(la.konfirmasi) = false,IF(la.konfirmasi="l","'.trans('all.lokasitidakcocok').'",IF(la.konfirmasi="f","'.trans('all.sampelwajahtidakcocok').'","-")),"-") as konfirmasi,
						IFNULL(DATE_FORMAT(la.waktu,"%d/%m/%Y %T"),DATE_FORMAT(kf.waktu,"%d/%m/%Y %T")) as waktu,
						IF(ISNULL(la.masukkeluar) = false,if(la.masukkeluar="m","<label class=\"label label-primary\">'.trans('all.masuk').'</label>","<label class=\"label label-danger\">'.trans('all.keluar').'</label>"),"") as masukkeluar,
						a.nilai,
						kf.status
					FROM
						konfirmasi_flag kf
						LEFT JOIN logabsen la ON kf.idlogabsen=la.id,
						pegawai pg
						LEFT JOIN pegawaiatribut pa ON pa.idpegawai=pg.id
						LEFT JOIN atributnilai a ON pa.idatributnilai=a.id
					WHERE
						kf.idpegawai=pg.id AND
						kf.id = :idkonfirmasiabsen';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':idkonfirmasiabsen', $idkonfirmasiabsen);
			$stmt->execute();
			$hasil = $stmt->fetch(PDO::FETCH_OBJ);

            // select facesample
            $sql = 'SELECT id, filename, checksum FROM facesample WHERE idpegawai=:idpegawai';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam('idpegawai', $hasil->idpegawai);
            $stmt->execute();
            $datafacesample = $stmt->fetchAll(PDO::FETCH_OBJ);
		}

		if($jenis == 'ijintidakmasuk'){
			$sql = 'SELECT
						itm.id as idkonfirmasiabsen,
						atm.alasan as alasantidakmasuk,
						pg.id as idpegawai,
						pg.nama,
						pg.nomorhp,
						pg.pin,
						getpegawaijamkerja(pg.id, "nama", :currentdate) as jamkerja,
						itm.tanggalawal,
						itm.tanggalakhir,
						keterangan,
						itm.filename
					FROM
						ijintidakmasuk itm,
						pegawai pg,
						alasantidakmasuk atm
					WHERE
						itm.idpegawai=pg.id AND
						itm.idalasantidakmasuk=atm.id AND
						itm.id = :idkonfirmasiabsen';
			$stmt = $pdo->prepare($sql);
            $stmt->bindValue(':currentdate', $currentdate);
			$stmt->bindValue(':idkonfirmasiabsen', $idkonfirmasiabsen);
			$stmt->execute();
			$hasil = $stmt->fetch(PDO::FETCH_OBJ);
		}

		//lokasi
		$sql = 'SELECT id,nama,lat,lon FROM lokasi';
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$lokasi = $stmt->fetchAll(PDO::FETCH_OBJ);

		return view('detailkonfirmasiabsen', ['data' => $hasil, 'datafacesample' => $datafacesample, 'lokasi' => $lokasi, 'jenis' => $jenis, 'menu' => $menu, 'dari' => $dari]);
	}

	public function submitKonfirmasiAbsen(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'unknown';

        $jenis = $request->jenis;
        $idkonfirmasiabsen = $request->idkonfirmasiabsen;
        $status = $request->status;
        $keterangan = $request->keterangan;
        $idlogabsen = '';
        $idpegawai = '';
        $waktu = '';
        $flag = '';

        if($jenis == 'logabsen'){
            try {
                $pdo->beginTransaction();

                $status = $status == 'terima' ? 'v' : 'na';
                $idlogabsen = $idkonfirmasiabsen;
                $sql = 'SELECT la.idpegawai, la.waktu, la.status, IFNULL(DATE_FORMAT(la.waktu, "%d/%m/%Y %T"),"") as waktu01, IFNULL(p.gcmid,"") as gcmid FROM logabsen la, pegawai p WHERE la.idpegawai=p.id AND la.id=:idlogabsen LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idlogabsen', $idkonfirmasiabsen);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_posting_idpegawai = $row['idpegawai'];
                    $_posting_waktu = $row['waktu'];
                    $_posting_status = $row['status'];
                    $gcm_waktu = $row['waktu01'];
                    $gcm_gcmid = $row['gcmid'];
                    $idpegawai = $row['idpegawai'];
                    $waktu = $row['waktu'];

                    $cekbolehubah = Utils::cekKunciDataPosting($_posting_waktu);
                    if($cekbolehubah == 0) {
                        $sql = 'UPDATE logabsen SET status = :status, flag_keterangan = :keterangan WHERE id = :idkonfirmasiabsen';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idkonfirmasiabsen', $idkonfirmasiabsen);
                        $stmt->bindValue(':status', $status);
                        $stmt->bindValue(':keterangan', ($status == 'c' ? '' : $keterangan));
                        $stmt->execute();

                        // posting ulang
                        $sql = 'CALL hitungrekapabsen_log(NULL, :idpegawai, :waktu)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam('idpegawai', $_posting_idpegawai);
                        $stmt->bindParam('waktu', $_posting_waktu);
                        $stmt->execute();

                        //hanya kirim gcm jika status nya ada (ada pada parameter) dan berubah dari yg sebelumnya.
                        if ($gcm_gcmid != '') {
                            if ("v" != $_posting_status) {
                                if (isset($waktu)) {
                                    $gcm_waktu = $waktu;
                                }
                                //kirim gcm info absen
                                Utils::kirimGCM($gcm_gcmid, 'konfirmasi', 'server', 'logabsen|' . $idkonfirmasiabsen . '|v|' . $gcm_waktu);
                            }
                        }

                        $pdo->commit();
                        $response['status'] = 'ok';
                        $response['msg'] = '';
                    } else {
                        $idlogabsen = '';
                        $pdo->commit();
                        $response['msg'] = trans('all.datatidakbisadirubah').Utils::getDataWhere($pdo,'pengaturan','kuncidatasebelumtanggal');
                    }
                }
            }catch (\Exception $e){
                $pdo->rollBack();
                $response['msg'] = $e->getMessage();
            }
        }

        if($jenis == 'konfirmasiflag'){
			try {
                $pdo->beginTransaction();

                $status = $status == 'terima' ? 'a' : 'na';
                $sql = 'SELECT flag, idpegawai, waktu, IFNULL(idlogabsen,"") as idlogabsen, idalasanmasukkeluar FROM konfirmasi_flag WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $idkonfirmasiabsen);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $flag = $row['flag'];	
                    $idpegawai = $row['idpegawai'];
                    $waktu = $row['waktu'];
                    $idlogabsen = $row['idlogabsen'];
					$idalasanmasukkeluar = $row['idalasanmasukkeluar'];

                    $cekbolehubah = Utils::cekKunciDataPosting($waktu);
                    if($cekbolehubah == 0) {
                        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
                        if ($batasan != '') {
                            $batasan = ' AND la.idpegawai IN ' . $batasan;
                        }

                        $sql1 = 'UPDATE konfirmasi_flag SET status = :status, keterangankonfirmasi = :keterangan WHERE id = :id';
                        $stmt1 = $pdo->prepare($sql1);
                        $stmt1->bindValue(':status', $status);
                        $stmt1->bindValue(':keterangan', ($status == 'c' ? '' : $keterangan));
                        $stmt1->bindValue(':id', $idkonfirmasiabsen);
                        $stmt1->execute();

                        // if ($status == 'a') {
                        $terhitungkerja = 'y';

                        $sqlupdate_terhitungkerja = '';
                        if ($idalasanmasukkeluar != null && $idalasanmasukkeluar != '' && $idalasanmasukkeluar > 0) {
                            $sql = 'SELECT terhitungkerja FROM alasanmasukkeluar WHERE id=:idalasanmasukkeluar LIMIT 1';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':idalasanmasukkeluar', $idalasanmasukkeluar);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $terhitungkerja = $row['terhitungkerja'];
                                $sqlupdate_terhitungkerja = ' terhitungkerja="' . $terhitungkerja . '", ';
                            }
                        }

                        if ($flag == 'tidak-terlambat' || $flag == 'tidak-pulangawal' || $flag == 'lembur') {
                            //pastikan data logabsen ada
                            $sql = 'SELECT la.id,la.waktu FROM logabsen la, pegawai p WHERE la.idpegawai=:idpegawai AND la.idpegawai=p.id AND p.del="t" AND la.id=:idlogabsen ' . $batasan . ' LIMIT 1';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':idpegawai', $idpegawai);
                            $stmt->bindParam(':idlogabsen', $idlogabsen);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $rowl = $stmt->fetch(PDO::FETCH_ASSOC);
                                $waktu = $rowl['waktu'];

//                                $sql = 'UPDATE logabsen SET status=:status, flag=:flag, flag_keterangan=:flag_keterangan, ' . $sqlupdate_terhitungkerja . ' updated=NOW() WHERE id=:idlogabsen';
//                                $stmt = $pdo->prepare($sql);
//                                $stmt->bindValue(':status', $status == 'a' ? 'v' : $status);
//                                $stmt->bindValue(':flag', $flag);
//                                $stmt->bindValue(':flag_keterangan', $keterangan);
//                                $stmt->bindValue(':idlogabsen', $idlogabsen);
//                                $stmt->execute();

                                $sql = 'UPDATE logabsen SET flag=:flag, flag_keterangan=:flag_keterangan, ' . $sqlupdate_terhitungkerja . ' updated=NOW() WHERE id=:idlogabsen';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':flag', $flag);
                                $stmt->bindValue(':flag_keterangan', $keterangan);
                                $stmt->bindValue(':idlogabsen', $idlogabsen);
                                $stmt->execute();
                            } else {
                                $pdo->commit();
                                $response['msg'] = trans('all.datatidakditemukan');
                            }
                        } else if ($flag == 'lupaabsenmasuk' || $flag == 'lupaabsenkeluar') {
                            //cek waktu dan idpegawai dari tabel logabsen
                            $sql = 'SELECT id FROM logabsen WHERE idpegawai = :idpegawai AND waktu = :waktu';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idpegawai', $idpegawai);
                            $stmt->bindValue(':waktu', $waktu);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $idlogabsen = $row['id'];
                            } else {
                                $sql = 'INSERT INTO logabsen VALUES(NULL, :waktu, :idpegawai, NULL,  :masukkeluar, :idalasanmasukkeluar, :terhitungkerja, NULL, NULL, :status, NULL, NULL, NULL, "manual", "", :flag_keterangan, :dataasli, NOW(), NOW())';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':waktu', $waktu);
                                $stmt->bindValue(':idpegawai', $idpegawai);
                                $stmt->bindValue(':masukkeluar', $flag == 'lupaabsenmasuk' ? 'm' : 'k');
                                $stmt->bindValue(':idalasanmasukkeluar', $idalasanmasukkeluar);
                                $stmt->bindValue(':terhitungkerja', $terhitungkerja);
                                $stmt->bindValue(':status', $status == 'a' ? 'v' : $status);
                                $stmt->bindValue(':dataasli', 'dari pengajuan: ' . $flag);
                                $stmt->bindValue(':flag_keterangan', $keterangan);
                                $stmt->execute();

                                $idlogabsen = $pdo->lastInsertId();
                            }

                            $sql1 = 'UPDATE konfirmasi_flag SET idlogabsen = :idlogabsen WHERE id = :id';
                            $stmt1 = $pdo->prepare($sql1);
                            $stmt1->bindValue(':idlogabsen', $idlogabsen);
                            $stmt1->bindValue(':id', $idkonfirmasiabsen);
                            $stmt1->execute();

                            $sql = 'UPDATE logabsen SET status=:status, flag_keterangan=:flag_keterangan, ' . $sqlupdate_terhitungkerja . ' updated=NOW() WHERE id=:idlogabsen';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':status', $status == 'a' ? 'v' : $status);
                            $stmt->bindValue(':flag_keterangan', $keterangan);
                            $stmt->bindValue(':idlogabsen', $idlogabsen);
                            $stmt->execute();
                        }
                        // }

                        Utils::kirimGCM(Utils::getDataWhere($pdo, 'pegawai', 'gcmid', 'id', $idpegawai), 'konfirmasi_flag', 'server', $idlogabsen . '|' . $status . '|' . $flag);

                        $pdo->commit();
                        $response['status'] = 'ok';
                        $response['msg'] = '';
                    } else {
                        $idlogabsen = '';
                        $pdo->commit();
                        $response['msg'] = trans('all.datatidakbisadirubah').Utils::getDataWhere($pdo,'pengaturan','kuncidatasebelumtanggal');
                    }
                }
            }catch (\Exception $e){
                $pdo->rollBack();
                $response['msg'] = $e->getMessage();
            }
        }

        if($jenis == 'ijintidakmasuk'){
            $status = $status == 'terima' ? 'a' : 'na';
            $this->eksekusiDetailKonfirmasiAbsen($jenis,$status,$idkonfirmasiabsen,$keterangan);
            $response['status'] = 'ok';
            $response['msg'] = '';
        }

        if($idlogabsen != '') {
            // posting ulang
            $sql = 'CALL hitungrekapabsen_log(:idlogabsen, NULL, NULL)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idlogabsen', $idlogabsen);
            $stmt->execute();
        }

        // karena ijin tidak masuk memakai class eksekusiDetailKonfirmasiAbsen yang sudah ada input loguser nya
        if($jenis != 'ijintidakmasuk') {
            // jika jenis konfirmasi flag, maka tulisan konfirmasi jadikan flag nya
            $konfirmasi = $jenis != 'konfirmasiflag' ? ' '.trans('all.konfirmasi') : ' '.trans('all.konfirmasi_flag').' '.trans('all.'.str_replace('-','',$flag));
            $keterangan = trans('all.' . $request->status) . $konfirmasi . ' ' . ($request->jenis == 'konfirmasiflag' ? '' : trans('all.' . $jenis)) . ' ' . Utils::tanggalCantik($waktu) . ' ' . Utils::getNamaPegawai($idpegawai) . ' melalui notifikasi';
            Utils::insertLogUser($keterangan);
        }
        return $response;
    }

    // untuk logabsen, ijin tidak masuk dan konfirmasi pengajuan
    public function submitDetailKonfirmasiAbsenTerpilih(Request $request, $status)
    {
        $menu = $request->menu;
        $statusbaru = '';
        // jika melalui tombol terima terpilih / tolak terpilih
        if($status == 'terimaterpilih' || $status == 'tolakterpilih'){
            $id = $request->id; // checkbox
            if(isset($id)) {
                if (count($id) > 0) {
                    for ($i = 0; $i < count($id); $i++) {
//                        if($menu == 'logabsen'){
//                            $statusbaru = $status == 'terimaterpilih' ? 'v' : 'na';
//                            $this->eksekusiDetailKonfirmasiAbsen($menu,$statusbaru,$id[$i]);
//                        }
//                        if($menu == 'ijintidakmasuk' || $menu == 'konfirmasi_flag'){
//                            $keterangan = $request->input('keterangan_'.$id[$i]);
//                            $statusbaru = $status == 'terimaterpilih' ? 'a' : 'na';
//                            $this->eksekusiDetailKonfirmasiAbsen($menu,$statusbaru,$id[$i],$keterangan);
//                        }
                        if ($menu == 'logabsen') {
                            $statusbaru = $status == 'terimaterpilih' ? 'v' : 'na';
                        }
                        if ($menu == 'ijintidakmasuk' || $menu == 'konfirmasi_flag') {
                            $statusbaru = $status == 'terimaterpilih' ? 'a' : 'na';
                        }
                        $keterangan = $request->input('keterangan_' . $id[$i]);
                        if ($statusbaru != '') {
                            $this->eksekusiDetailKonfirmasiAbsen($menu, $statusbaru, $id[$i], $keterangan);
                        }
                    }
                    Utils::insertLogUser(trans('all.' . $status) . ' ' . trans('all.' . $menu));
                    return 'ok';
                } else {
                    return 'error';
                }
            }else{
                return 'error';
            }
        }else{
            // jika melalui tombol terima / tolak di per kotak ijin tidak masuk
            $idsatuan = $request->idsatuan;
//            if($menu == 'logabsen'){
//                $statusbaru = $status == 'terima' ? 'v' : 'na';
//                $this->eksekusiDetailKonfirmasiAbsen($menu,$statusbaru,$idsatuan);
//            }
//            if($menu == 'ijintidakmasuk' || $menu == 'konfirmasi_flag'){
//                $keterangan = $request->input('keterangan_'.$idsatuan);
//                $statusbaru = $status == 'terima' ? 'a' : 'na';
//                $this->eksekusiDetailKonfirmasiAbsen($menu,$statusbaru,$idsatuan,$keterangan);
//            }
            if($menu == 'logabsen'){
                $statusbaru = $status == 'terima' ? 'v' : 'na';
            }
            if($menu == 'ijintidakmasuk' || $menu == 'konfirmasi_flag'){
                $statusbaru = $status == 'terima' ? 'a' : 'na';
            }
            $keterangan = $request->input('keterangan_'.$idsatuan);
            if($statusbaru != '') {
                $this->eksekusiDetailKonfirmasiAbsen($menu, $statusbaru, $idsatuan, $keterangan);
            }
            Utils::insertLogUser(trans('all.'.$status).' '.trans('all.'.$menu));
            return 'ok';
        }
    }

	public function submitDetailKonfirmasiAbsen($jenis,$status,$idkonfirmasiabsen,$dari="popup")
	{
	    $result = '';
		if($jenis == 'logabsen'){
            $status = $status == 'terima' ? 'v' : 'na';
            $result = $this->eksekusiDetailKonfirmasiAbsen($jenis,$status,$idkonfirmasiabsen);
		}

        if($jenis == 'ijintidakmasuk' || $jenis == 'konfirmasi_flag'){
            $status = $status == 'terima' ? 'a' : 'na';
            $result = $this->eksekusiDetailKonfirmasiAbsen($jenis,$status,$idkonfirmasiabsen);
        }

        if($dari == 'popup') {
            if($result != ''){
                return redirect('/')->with('message', $result);
            } else {
                return redirect('/');
            }
        } else {
            if($result != ''){
                return redirect('notifdetail/' . $jenis)->with('message', $result);
            } else {
                return redirect('notifdetail/' . $jenis);
            }
        }
	}

    public function eksekusiDetailKonfirmasiAbsen($jenis,$status,$id,$keterangan=''){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sukses = false;
        $idpegawai = '';
        $waktu = '';
        if($jenis == 'logabsen'){
            $sql = 'SELECT la.idpegawai, la.waktu, la.status, IFNULL(DATE_FORMAT(la.waktu, "%d/%m/%Y %T"),"") as waktu01, IFNULL(p.gcmid,"") as gcmid FROM logabsen la, pegawai p WHERE la.idpegawai=p.id AND la.id=:id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $_posting_idpegawai = $row['idpegawai'];
                $_posting_waktu = $row['waktu'];
                $_posting_status = $row['status'];
                $gcm_waktu = $row['waktu01'];
                $gcm_gcmid = $row['gcmid'];
                $idpegawai = $_posting_idpegawai;
                $waktu = Utils::tanggalCantik($row['waktu']);

                $cekbolehubah = Utils::cekKunciDataPosting($_posting_waktu);
                if($cekbolehubah == 0) {
//                    $sql = 'UPDATE logabsen SET status = :status WHERE id = :id';
//                    $stmt = $pdo->prepare($sql);
//                    $stmt->bindValue(':id', $id);
//                    $stmt->bindValue(':status', $status);
//                    $stmt->execute();
                    $sql = 'UPDATE logabsen SET status = :status, flag_keterangan = :keterangan WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':id', $id);
                    $stmt->bindValue(':status', $status);
                    $stmt->bindValue(':keterangan', $keterangan);
                    $stmt->execute();

                    // posting ulang untuk data sebelum diupdate
                    $sql = 'CALL hitungrekapabsen_log(NULL, :idpegawai, :waktu)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam('idpegawai', $_posting_idpegawai);
                    $stmt->bindParam('waktu', $_posting_waktu);
                    $stmt->execute();

                    // posting ulang untuk data setelah diupdate
                    $sql = 'CALL hitungrekapabsen_log(:idlogabsen, NULL, NULL)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam('idlogabsen', $id);
                    $stmt->execute();

                    //hanya kirim gcm jika status nya ada (ada pada parameter) dan berubah dari yg sebelumnya.
                    if ($gcm_gcmid != '') {
                        if ("v" != $_posting_status) {
                            if (isset($waktu)) {
                                $gcm_waktu = $waktu;
                            }
                            //kirim gcm info absen
                            Utils::kirimGCM($gcm_gcmid, 'konfirmasi', 'server', 'logabsen|' . $id . '|v|' . $gcm_waktu);
                        }
                    }
                    $sukses = true;
                }
            }
        }

        if($jenis == 'ijintidakmasuk'){
            $sql = 'SELECT
						itm.idpegawai,
						itm.tanggalawal,
						itm.tanggalakhir,
						itm.keterangan,
						itm.idalasantidakmasuk,
						itm.status,
						IFNULL(p.gcmid,"") as gcmid
					FROM
						ijintidakmasuk itm,
						pegawai p
					WHERE
						p.id=itm.idpegawai AND
						itm.id=:idijintidakmasuk
					LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idijintidakmasuk', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $_posting_idpegawai = $row['idpegawai'];
                $_posting_tanggalawal = $row['tanggalawal'];
                $_posting_tanggalakhir = $row['tanggalakhir'];
                $_posting_keterangan = $row['keterangan'];
                $_posting_idalasantidakmasuk = $row['idalasantidakmasuk'];
                $_posting_status = $row['status'];
                $_posting_gcmid = $row['gcmid'];
                $idpegawai = $_posting_idpegawai;
                $waktu = Utils::tanggalCantikDariSampai($_posting_tanggalawal,$_posting_tanggalakhir);

                $sql = 'SELECT kategori,DATEDIFF(:tanggalakhir,:tanggalawal) as jumlahcuti FROM alasantidakmasuk WHERE id=:idalasantidakmasuk LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':tanggalawal', $_posting_tanggalawal);
                $stmt->bindValue(':tanggalakhir', $_posting_tanggalakhir);
                $stmt->bindValue(':idalasantidakmasuk', $_posting_idalasantidakmasuk);
                $stmt->execute();
                if ($stmt->rowCount() != 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $jumlahcuti = $row['jumlahcuti'] + 1;
                    if ($row['kategori'] == 'c' && $status == "a") {
                        $tahunawal = substr($_posting_tanggalawal, 0, 4);
                        $tahunakhir = substr($_posting_tanggalakhir, 0, 4);
                        if ($tahunawal == $tahunakhir) {
                            $lamacuti = Utils::getLamaCuti($tahunawal,$_posting_idpegawai);

                            $sql = 'CALL get_cuti_pegawai(:tahun,:idpegawai,:idijintidakmasuk)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tahun', $tahunawal);
                            $stmt->bindValue(':idpegawai', $_posting_idpegawai);
                            $stmt->bindValue(':idijintidakmasuk', $id);
                            $stmt->execute();

                            $sql = 'SELECT * FROM _cuti_pegawai';
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $row1 = $stmt->fetch(PDO::FETCH_ASSOC);
                                $sisajatah = $row1['jatah'] - $lamacuti;
                                if($lamacuti >= $row1['jatah']){
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'),'msgwarning');
                                }else if($jumlahcuti > $sisajatah){
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'),'msgwarning');
                                }
                            }
                        } else {
                            $lamacuti = Utils::getLamaCuti($tahunawal,$_posting_idpegawai);

                            $sql = 'CALL get_cuti_pegawai(:tahunawal,:idpegawai,:idijintidakmasuk)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tahunawal', $tahunawal);
                            $stmt->bindValue(':idpegawai', $_posting_idpegawai);
                            $stmt->bindValue(':idijintidakmasuk', $id);
                            $stmt->execute();

                            $sql = 'SELECT * FROM _cuti_pegawai';
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $row1 = $stmt->fetch(PDO::FETCH_ASSOC);
                                $sisajatah = $row1['jatah'] - $lamacuti;
                                if($lamacuti >= $row1['jatah']){
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'),'msgwarning');
                                }else if($jumlahcuti > $sisajatah){
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'),'msgwarning');
                                }
                            }

                            $lamacuti = Utils::getLamaCuti($tahunakhir,$_posting_idpegawai);

                            $sql = 'CALL get_cuti_pegawai(:tahun,:idpegawai,NULL)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tahun', $tahunakhir);
                            $stmt->bindValue(':idpegawai', $_posting_idpegawai);
                            $stmt->execute();

                            $sql = 'SELECT * FROM _cuti_pegawai';
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $row1 = $stmt->fetch(PDO::FETCH_ASSOC);
                                $sisajatah = $row1['jatah'] - $lamacuti;
                                if($lamacuti >= $row1['jatah']){
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'),'msgwarning');
                                }else if($jumlahcuti > $sisajatah){
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'),'msgwarning');
                                }
                            }
                        }
                    }
                }

                $cekbolehubah = Utils::cekKunciDataPosting($_posting_tanggalawal.' 00:00:00');
                if($cekbolehubah == 0) {
                    $sql = 'UPDATE ijintidakmasuk SET status = :status, keterangankonfirmasi = :keterangankonfirmasi WHERE id = :idkonfirmasiabsen';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':status', $status);
                    $stmt->bindValue(':keterangankonfirmasi', $keterangan);
                    $stmt->bindValue(':idkonfirmasiabsen', $id);
                    $stmt->execute();

                    // posting ulang untuk data sebelum diupdate
                    $sql = 'CALL hitungrekapabsen_ijintidakmasuk(NULL, :idpegawai, :tanggalawal, :tanggalakhir, :status)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $_posting_idpegawai);
                    $stmt->bindValue(':tanggalawal', $_posting_tanggalawal);
                    $stmt->bindValue(':tanggalakhir', $_posting_tanggalakhir);
                    $stmt->bindValue(':status', $_posting_status);
                    $stmt->execute();

                    // posting absen untuk data setelah diupdate
                    $sql = 'CALL hitungrekapabsen_ijintidakmasuk(:idijintidakmasuk, NULL, NULL, NULL, NULL)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idijintidakmasuk', $id);
                    $stmt->execute();

                    //hanya kirim gcm jika status nya ada (ada pada parameter) dan berubah dari yg sebelumnya.
                    if ($_posting_gcmid != '') {
                        if ('a' != $_posting_status) {
                            //kirim gcm info absen
                            Utils::kirimGCM($_posting_gcmid, 'konfirmasi', 'server', 'ijintidakmasuk|' . $id . '|'.$status.'|' . $_posting_keterangan);
                        }
                    }
                    $sukses = true;
                }
            }
        }

        if($jenis == 'konfirmasi_flag'){
            try {
                $pdo->beginTransaction();

                $sql = 'SELECT flag, idpegawai, waktu, IFNULL(idlogabsen,"") as idlogabsen, idalasanmasukkeluar FROM konfirmasi_flag WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $flag = $row['flag'];
                    $idpegawai = $row['idpegawai'];
                    $waktu = $row['waktu'];
                    $idlogabsen = $row['idlogabsen'];
                    $idalasanmasukkeluar = $row['idalasanmasukkeluar'];

                    $cekbolehubah = Utils::cekKunciDataPosting($waktu);
                    if($cekbolehubah == 0) {
                        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
                        if ($batasan != '') {
                            $batasan = ' AND la.idpegawai IN ' . $batasan;
                        }

                        $sql1 = 'UPDATE konfirmasi_flag SET status = :status, keterangankonfirmasi = :keterangan WHERE id = :id';
                        $stmt1 = $pdo->prepare($sql1);
                        $stmt1->bindValue(':status', $status);
                        $stmt1->bindValue(':keterangan', ($status == 'c' ? '' : $keterangan));
                        $stmt1->bindValue(':id', $id);
                        $stmt1->execute();

                        $terhitungkerja = 'y';

                        $sqlupdate_terhitungkerja = '';
                        if ($idalasanmasukkeluar != null && $idalasanmasukkeluar != '' && $idalasanmasukkeluar > 0) {
                            $sql = 'SELECT terhitungkerja FROM alasanmasukkeluar WHERE id=:idalasanmasukkeluar LIMIT 1';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':idalasanmasukkeluar', $idalasanmasukkeluar);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $terhitungkerja = $row['terhitungkerja'];
                                $sqlupdate_terhitungkerja = ' terhitungkerja="' . $terhitungkerja . '", ';
                            }
                        }

                        if ($flag == 'tidak-terlambat' || $flag == 'tidak-pulangawal' || $flag == 'lembur') {
                            //pastikan data logabsen ada
                            $sql = 'SELECT la.id,la.waktu FROM logabsen la, pegawai p WHERE la.idpegawai=:idpegawai AND la.idpegawai=p.id AND p.del="t" AND la.id=:idlogabsen ' . $batasan . ' LIMIT 1';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':idpegawai', $idpegawai);
                            $stmt->bindParam(':idlogabsen', $idlogabsen);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $rowl = $stmt->fetch(PDO::FETCH_ASSOC);
                                $waktu = $rowl['waktu'];

                                $sql = 'UPDATE logabsen SET status=:status, flag=:flag, flag_keterangan=:flag_keterangan, ' . $sqlupdate_terhitungkerja . ' updated=NOW() WHERE id=:idlogabsen';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':status', $status == 'a' ? 'v' : $status);
                                $stmt->bindValue(':flag', $flag);
                                $stmt->bindValue(':flag_keterangan', $keterangan);
                                $stmt->bindValue(':idlogabsen', $idlogabsen);
                                $stmt->execute();
                            } else {
                                $pdo->commit();
                            }
                        } else if ($flag == 'lupaabsenmasuk' || $flag == 'lupaabsenkeluar') {
                            //cek waktu dan idpegawai dari tabel logabsen
                            $sql = 'SELECT id FROM logabsen WHERE idpegawai = :idpegawai AND waktu = :waktu';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idpegawai', $idpegawai);
                            $stmt->bindValue(':waktu', $waktu);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $idlogabsen = $row['id'];
                            } else {
                                $sql = 'INSERT INTO logabsen VALUES(NULL, :waktu, :idpegawai, NULL,  :masukkeluar, :idalasanmasukkeluar, :terhitungkerja, NULL, NULL, :status, NULL, NULL, NULL, "manual", "", :flag_keterangan, :dataasli, NOW(), NOW())';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':waktu', $waktu);
                                $stmt->bindValue(':idpegawai', $idpegawai);
                                $stmt->bindValue(':masukkeluar', $flag == 'lupaabsenmasuk' ? 'm' : 'k');
                                $stmt->bindValue(':idalasanmasukkeluar', $idalasanmasukkeluar);
                                $stmt->bindValue(':terhitungkerja', $terhitungkerja);
                                $stmt->bindValue(':status', $status == 'a' ? 'v' : $status);
                                $stmt->bindValue(':dataasli', 'dari pengajuan: ' . $flag);
                                $stmt->bindValue(':flag_keterangan', $keterangan);
                                $stmt->execute();

                                $idlogabsen = $pdo->lastInsertId();
                            }

                            $sql1 = 'UPDATE konfirmasi_flag SET idlogabsen = :idlogabsen WHERE id = :id';
                            $stmt1 = $pdo->prepare($sql1);
                            $stmt1->bindValue(':idlogabsen', $idlogabsen);
                            $stmt1->bindValue(':id', $id);
                            $stmt1->execute();

                            $sql = 'UPDATE logabsen SET status=:status, flag_keterangan=:flag_keterangan, ' . $sqlupdate_terhitungkerja . ' updated=NOW() WHERE id=:idlogabsen';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':status', $status == 'a' ? 'v' : $status);
                            $stmt->bindValue(':flag_keterangan', $keterangan);
                            $stmt->bindValue(':idlogabsen', $idlogabsen);
                            $stmt->execute();
                        }

                        Utils::kirimGCM(Utils::getDataWhere($pdo, 'pegawai', 'gcmid', 'id', $idpegawai), 'konfirmasi_flag', 'server', $idlogabsen . '|' . $status . '|' . $flag);
                        $sukses = true;
                        $pdo->commit();
                    }
                }
            }catch (\Exception $e){
                $pdo->rollBack();
            }
        }

        if($sukses == true){
            $keterangan = trans('all.' . ($status == 'v' ? 'terima' : ($status == 'a' ? 'terima' : 'tolak'))) . ' ' . trans('all.konfirmasi') . ' ' . ($jenis == 'konfirmasiflag' ? '' : trans('all.' . $jenis)) . ' ' . $waktu . ' ' . Utils::getNamaPegawai($idpegawai) . ' melalui notifikasi';
            Utils::insertLogUser($keterangan);
//            Utils::insertLogUser(trans('all.'.$status).' '.trans('all.'.$jenis).' '.($idpegawai != '' ? Utils::getNamaPegawai($idpegawai) : '').($tanggal != '' ? ' '.trans('all.tanggal').' '.$tanggal : '').' melalui notifikasi');
        }
    }

	public function pulangAwal($tanggal)
	{
		$tanggal = $tanggal == 'o' ? '' : $tanggal;
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail2', ['jamkerjakategori' => $jamkerjakategori, 'detail' => 'pulangawal', 'tanggal' => $tanggal, 'menu' => 'beranda']);
	}

	public function pulangAwalPerTanggal($tanggal, $startfrom)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sqlWhere = '';
		$where = '';
		$startfrom_nama='';
		$startfrom = $startfrom == 'o' ? '' : $startfrom;
        $more = false; //digunakan untuk mengetahui apakah load awal / dari load more(sewaktu scroll kebawah)

		if ($startfrom!='') {
			$sql = 'SELECT nama FROM pegawai WHERE id=:startfrom AND status = "a" LIMIT 1';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':startfrom', $startfrom);
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$startfrom_nama = $row['nama'];
				$sqlWhere = ' AND pg.nama > :startfrom_nama ';
			}
			$more = true;
		}

		if(Session::has('pulangawal_pencarian_detail')){
			$sqlWhere .= ' AND pg.nama LIKE "%'.Session::get('pulangawal_pencarian_detail').'%" ';
		}

        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $sqlWhere .= ' AND pg.id IN '.$batasan;
        }

		if ($where!='') {
			$sqlWhere = $sqlWhere.' AND (pg.nama LIKE :where1 OR m.nama LIKE :where2 OR amk.alasan LIKE :where3) ';
		}

		$sql = 'SELECT
					ra.id as idrekapabsen,
					ra.id,
					pg.id as idpegawai,
					CONCAT("<span class=detailpegawai onclick=detailpegawai(",pg.id,") style=cursor:pointer>",pg.nama,"</span>") as nama,
					pg.nama as namapegawai,
					getatributtampilpadaringkasan(pg.id) as atribut,
					DATE_FORMAT(ra.waktukeluar,"%d/%m/%Y %T") as waktukeluar,
					-1*ra.selisihkeluar as pulangawalmenit,
					pg.id as startfrom
		        FROM
		        	rekapabsen ra,
		        	pegawai pg
		        WHERE
		        	ra.idpegawai=pg.id AND
		         	ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d") AND
		         	ra.selisihkeluar<0
		         	'.$sqlWhere.'
		        GROUP BY
		        	pg.id
	         	ORDER BY
	         		pg.nama
		        LIMIT '.config('consts.LIMIT_6_KOLOM');

		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom_nama!='') {
			$stmt->bindValue(':startfrom_nama', $startfrom_nama);
		}
		if ($where!='') {
			$where = '%'.$where.'%';
			$stmt->bindValue(':where1', $where);
			$stmt->bindValue(':where2', $where);
			$stmt->bindValue(':where3', $where);
		}
		$stmt->execute();
		$datas = $stmt->fetchAll(PDO::FETCH_OBJ);

		$sql = 'SELECT
					ra.id as idrekapabsen
		        FROM
		        	rekapabsen ra,
		        	pegawai pg
		        WHERE
		        	ra.idpegawai=pg.id AND
		         	ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d") AND
		         	ra.selisihkeluar<0
		         	'.$sqlWhere.'
		        GROUP BY
		        	pg.id';

		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom_nama!='') {
			$stmt->bindValue(':startfrom_nama', $startfrom_nama);
		}
		if ($where!='') {
			$where = '%'.$where.'%';
			$stmt->bindValue(':where1', $where);
			$stmt->bindValue(':where2', $where);
			$stmt->bindValue(':where3', $where);
		}
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		return view('detailmore', ['datas' => $datas, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'tanggal' => $tanggal, 'more' => $more, 'detail' => 'pulangawal']);
	}

	public function lembur($tanggal)
	{
		$tanggal = $tanggal == 'o' ? '' : $tanggal;
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
		return view('indexdetail', ['jamkerjakategori' => $jamkerjakategori, 'detail' => 'lembur', 'tanggal' => $tanggal, 'menu' => 'beranda']);
	}

	public function lemburPerTanggal($tanggal, $startfrom)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sqlWhere = '';
		$where = '';
		$startfrom_nama='';
		$startfrom = $startfrom == 'o' ? '' : $startfrom;

		if ($startfrom!='') {
			$sql = 'SELECT nama FROM pegawai WHERE id=:startfrom AND status = "a" LIMIT 1';
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':startfrom', $startfrom);
			$stmt->execute();
			if ($stmt->rowCount()>0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$startfrom_nama = $row['nama'];
				$sqlWhere = ' AND pg.nama > :startfrom_nama ';
			}
		}

		if ($where!='') {
			$sqlWhere = $sqlWhere.' AND (pg.nama LIKE :where1 OR m.nama LIKE :where2 OR amk.alasan LIKE :where3) ';
		}

		// as lemburmenit,
		$sql = 'SELECT
					ra.id as idrekapabsen,
					pg.id as idpegawai,
					CONCAT("<span class=detailpegawai onclick=detailpegawai(",pg.id,") style=cursor:pointer>",pg.nama,"</span>") as nama,
					pg.nama as namapegawai,
					getatributtampilpadaringkasan(pg.id) as atribut,
					ra.lamalembur,
					pg.id as startfrom
		        FROM
		        	rekapabsen ra,
		        	pegawai pg
		        WHERE
		        	ra.idpegawai=pg.id AND
		         	ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d") AND
		         	ra.lamalembur>0
		         	'.$sqlWhere.'
		        GROUP BY
		        	pg.id
	         	ORDER BY
	         		pg.nama
		        LIMIT '.config('consts.LIMIT_6_KOLOM');

		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom_nama!='') {
			$stmt->bindValue(':startfrom_nama', $startfrom_nama);
		}
		if ($where!='') {
			$where = '%'.$where.'%';
			$stmt->bindValue(':where1', $where);
			$stmt->bindValue(':where2', $where);
			$stmt->bindValue(':where3', $where);
		}
		$stmt->execute();
		$datas = $stmt->fetchAll(PDO::FETCH_OBJ);

		$sql = 'SELECT
					ra.id as idrekapabsen
		        FROM
		        	rekapabsen ra,
		        	pegawai pg
		        WHERE
		        	ra.idpegawai=pg.id AND
		         	ra.tanggal=STR_TO_DATE(:tanggal, "%Y%m%d") AND
		         	ra.lamalembur>0
		         	'.$sqlWhere.'
		        GROUP BY
		        	pg.id';

		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':tanggal', $tanggal);
		if ($startfrom_nama!='') {
			$stmt->bindValue(':startfrom_nama', $startfrom_nama);
		}
		if ($where!='') {
			$where = '%'.$where.'%';
			$stmt->bindValue(':where1', $where);
			$stmt->bindValue(':where2', $where);
			$stmt->bindValue(':where3', $where);
		}
		$stmt->execute();
		$totaldata = $stmt->rowCount();

		return view('detailmore', ['datas' => $datas, 'totaldata' => $totaldata, 'totaldatalimit' => config('consts.LIMIT_6_KOLOM'), 'tanggal' => $tanggal, 'detail' => 'lembur']);
	}

	public function detailRekap($idrekapabsen)
	{
		$pdo = DB::Connection('perusahaan_db')->getPdo();
		$sql = 'SELECT
                ra.tanggal,
                DAYOFWEEK(ra.tanggal) as hari,
                ra.idpegawai,
                IFNULL(p.nama,"") as pegawai_nama,
                IFNULL(p.pin,"") as pegawai_pin,
                IFNULL(hl.keterangan,"") as harilibur,
                IF(ra.masukkerja="y","'.trans('all.ya').'",IF(ra.masukkerja = "t","'.trans('all.tidak').'","-")) as masukkerja,
                ra.jumlahsesi,
                IFNULL(atm.alasan,"") as alasantidakmasuk,
                ra.alasantidakmasukkategori,
                IFNULL(jk.nama,"") as jamkerja,
                IF(ISNULL(ra.idjamkerjakhusus),"'.trans('all.tidak').'","'.trans('all.ya').'") as jamkerjakhusus,
                IF(ra.jadwalmasukkerja="y","'.trans('all.ya').'",IF(ra.jadwalmasukkerja = "t","'.trans('all.tidak').'","-")) as jadwalmasukkerja,
                ra.jenisjamkerja,
                ra.jadwallamakerja as jadwallamakerja,
                IFNULL(amk.alasan,"") as alasanmasuk,
                ra.waktumasuk,
                ra.waktukeluar,
                ra.selisihmasuk,
                ra.selisihkeluar,
                ra.lamakerja,
                ra.lamalembur,
                ra.overlap,
                ra.status
            FROM
                rekapabsen ra
                LEFT JOIN pegawai p ON p.id=ra.idpegawai
                LEFT JOIN harilibur hl ON hl.id=ra.idharilibur
                LEFT JOIN alasantidakmasuk atm ON atm.id=ra.idalasantidakmasuk
                LEFT JOIN jamkerja jk ON jk.id=ra.idjamkerja
                LEFT JOIN alasanmasukkeluar amk ON amk.id=ra.idalasanmasuk
            WHERE
                ra.id=:idrekapabsen
            LIMIT 1
	        ';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':idrekapabsen', $idrekapabsen);
		$stmt->execute();
		$rekapabsen = array();
		if ($stmt->rowCount() != 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			$rekapabsen['tanggal'] = $row['tanggal'];
			$rekapabsen['idpegawai'] = $row['idpegawai'];
			$rekapabsen['pegawai_nama'] = $row['pegawai_nama'];
			$rekapabsen['pegawai_pin'] = $row['pegawai_pin'];
			$rekapabsen['harilibur'] = $row['harilibur'];
			$rekapabsen['masukkerja'] = $row['masukkerja'];
			$rekapabsen['jumlahsesi'] = $row['jumlahsesi'];
			$rekapabsen['alasantidakmasuk'] = $row['alasantidakmasuk'];
			$rekapabsen['alasantidakmasukkategori'] = $row['alasantidakmasukkategori'];
			$rekapabsen['jamkerja'] = $row['jamkerja'];
			$rekapabsen['jamkerjakhusus'] = $row['jamkerjakhusus'];
			$rekapabsen['jadwalmasukkerja'] = $row['jadwalmasukkerja'];
			$rekapabsen['jenisjamkerja'] = $row['jenisjamkerja'];
			$rekapabsen['jadwallamakerja'] = $row['jadwallamakerja'];
			$rekapabsen['alasanmasuk'] = $row['alasanmasuk'];
			$rekapabsen['waktumasuk'] = $row['waktumasuk'];
			$rekapabsen['waktukeluar'] = $row['waktukeluar'];
			$rekapabsen['selisihmasuk'] = $row['selisihmasuk'];
			$rekapabsen['selisihkeluar'] = $row['selisihkeluar'];
			$rekapabsen['lamakerja'] = $row['lamakerja'];
			$rekapabsen['lamalembur'] = $row['lamalembur'];
			$rekapabsen['overlap'] = $row['overlap'];
			$rekapabsen['status'] = $row['status'];

			//select dari rekapabsen_jadwal
			$sql02='SELECT
						CONCAT(DATE_FORMAT(waktu,"%Y-%m-%d")) as tanggal,
						TIME(waktu) as jam,
						IF(masukkeluar="m","'.trans('all.masuk').'","'.trans('all.keluar').'") as masukkeluar,
						checking,
						shiftpertamaterakhir,
						IF(shiftsambungan="y","'.trans('all.ya').'","'.trans('all.tidak').'") as shiftsambungan
					FROM
						rekapabsen_jadwal
					WHERE
						idrekapabsen = :idrekapabsen
					ORDER BY
						waktu';
			$stmt02 = $pdo->prepare($sql02);
			$stmt02->bindParam(':idrekapabsen', $idrekapabsen);
			$stmt02->execute();
			$rekapabsen['rekapabsen_jadwal'] = $stmt02->fetchAll(PDO::FETCH_OBJ);

			//select dari rekapabsen_logabsen / riwayat
			$sql03='SELECT
						CONCAT(DATE_FORMAT(r.waktu,"%Y-%m-%d")) as tanggal,
						TIME(r.waktu) as jam,
						IF(r.masukkeluar="m","'.trans('all.masuk').'","'.trans('all.keluar').'") as masukkeluar,
						a.alasan,
						IF(r.terhitungkerja="y","'.trans('all.ya').'","'.trans('all.tidak').'") as terhitungkerja
					FROM
						rekapabsen_logabsen r
						LEFT JOIN alasanmasukkeluar a ON r.idalasan=a.id
					WHERE
						idrekapabsen = :idrekapabsen
					ORDER BY
						r.waktu';
			$stmt03 = $pdo->prepare($sql03);
			$stmt03->bindParam(':idrekapabsen', $idrekapabsen);
			$stmt03->execute();
			$rekapabsen['rekapabsen_riwayat'] = $stmt03->fetchAll(PDO::FETCH_OBJ);

			//select dari rekapabsen_hasil
			$sql04='SELECT
						IF(terhitung="k","'.trans('all.kerja').'","'.trans('all.lembur').'") as terhitung,
						IF(flag="j","'.trans('all.jadwal').'","'.trans('all.pegawai').'") as flag,
						CONCAT(DATE_FORMAT(waktu,"%Y-%m-%d")) as tanggal,
						TIME(waktu) as jam,
						IF(masukkeluar="m","'.trans('all.masuk').'","'.trans('all.keluar').'") as masukkeluar,
						IF(override="m","'.trans('all.ya').'","'.trans('all.tidak').'") as override
					FROM
						rekapabsen_hasil
					WHERE
						idrekapabsen = :idrekapabsen
					ORDER BY
						waktu';
			$stmt04 = $pdo->prepare($sql04);
			$stmt04->bindParam(':idrekapabsen', $idrekapabsen);
			$stmt04->execute();
			$rekapabsen['rekapabsen_hasil'] = $stmt04->fetchAll(PDO::FETCH_OBJ);

			return view('detailrekap', ['data' => $rekapabsen]);
		}
		else {
			return 'gangguan';
		}
	}

	public function logAbsenIjinTidakMasuk()
	{
		$sqlWhere = '';
		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND p.id IN '.$batasan;
		}

		$pdo = DB::connection('perusahaan_db')->getPdo();
		//konfirmasi absen
		$sql = 'SELECT
                        la.id as idlogabsen,
                        p.id as idpegawai,
                        p.nama,
                        DATE_FORMAT(la.waktu,"%d/%m/%Y %T") as waktu,
                        if(la.masukkeluar="m","<label class=\"label label-primary\">'.trans('all.masuk').'</label>","<label class=\"label label-danger\">'.trans('all.keluar').'</label>") as masukkeluar
                    FROM
                        logabsen la,
                        pegawai p
                    WHERE
                        la.idpegawai=p.id AND
                        la.status="c"'.$sqlWhere;
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$logabsen = $stmt->fetchAll(PDO::FETCH_OBJ);

		//hitung total konfirmasi ijintidakmasuk
		$sql = 'SELECT
					itm.id as idijintidakmasuk,
					p.id as idpegawai,
					p.nama,
					CONCAT(DATE_FORMAT(itm.tanggalawal,"%d/%m/%Y")," - ",DATE_FORMAT(itm.tanggalakhir,"%d/%m/%Y")) as waktu,
					keterangan
				FROM
					ijintidakmasuk itm,
					pegawai p
				WHERE
					itm.idpegawai=p.id AND
					itm.status="c"'.$sqlWhere;
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$ijintidakmasuk = $stmt->fetchAll(PDO::FETCH_OBJ);
		//Session::set('conf_ijintidakmasuk', $ijintidakmasuk);
		return $logabsen;
	}

	public function detailRiwayatPresensi($idpegawai,$yymm,$startfrom)
	{
		$idperusahaan = Session::get('conf_webperusahaan');
		$pdo = DB::connection('perusahaan_db')->getPdo();
		$sqlWhere = '';
		$batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
		if ($batasan!='') {
			$sqlWhere .= ' AND la.idpegawai IN '.$batasan;
		}

		if ($startfrom!='') {
			$sqlWhere = ' AND CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),"_",DATE_FORMAT(la.inserted,"%Y%m%d%H%i%s")) < :startfrom ';
		}

		$sql = 'SELECT
				la.id,
				la.waktu,
				IFNULL(m.nama, "-") as mesin,
				la.masukkeluar,
				IFNULL(amk.alasan, "") as alasanmasukkeluar,
				IFNULL(la.lat,0) as lat,
				IFNULL(la.lon,0) as lon,
				la.status,
				la.konfirmasi,
				la.inserted,
				la.updated,
				CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),"_",DATE_FORMAT(la.inserted,"%Y%m%d%H%i%s")) as startfrom
	        FROM
	        	logabsen la
	        	LEFT JOIN mesin m ON m.id=la.idmesin
	        	LEFT JOIN alasanmasukkeluar amk ON amk.id=la.idalasanmasukkeluar
	        WHERE
	        	la.idpegawai=:idpegawai AND
	         	DATE_FORMAT(la.waktu, "%y%m") = :yymm
	         	'.$sqlWhere.'
	        ORDER BY
	        	startfrom DESC
	        LIMIT '.config('consts.LIMIT_6_KOLOM').'
	        ';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':idpegawai', $idpegawai);
		$stmt->bindParam(':yymm', $yymm);
		if ($startfrom!='') {
			$stmt->bindParam(':startfrom', $startfrom);
		}
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_OBJ);

		return 'ok';
	}

	public function detailRekapPresensi($idpegawai)
	{
		$pdo = DB::connection('perusahaan_db')->getPdo();
		return 'ok';
	}

	//notifikasi(lonceng)
    public function notifDetail($menu){
        $data = '';
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $currentdate = Utils::getCurrentDate();

        $where = '';
        $filter = '';
        if(Session::has('notif'.$menu.'_atributfilter')){
            $atributs = Session::get('notif'.$menu.'_atributfilter');
            $filter = Utils::atributNilai($atributs);
        }
        if ($filter!='') {
            $where .= 'AND pg.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$filter.')) ';
        }

        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $where .= ' AND pg.id IN '.$batasan;
        }

        if($menu == 'ijintidakmasuk'){
            $sql = 'SELECT
						itm.id,
						pg.id as idpegawai,
						pg.nama,
						pg.nomorhp,
						pg.pin,
						getpegawaijamkerja(pg.id, "nama", :currentdate) as jamkerja,
						itm.tanggalawal,
						itm.tanggalakhir,
						keterangan,
						itm.filename
					FROM
						ijintidakmasuk itm,
						pegawai pg
					WHERE
						itm.idpegawai=pg.id AND
						itm.status = "c"
						'.$where.'
                    ORDER BY
                        itm.tanggalakhir DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':currentdate', $currentdate);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        }else if($menu == 'logabsen'){
            $sql = 'SELECT
						la.id,
						pg.id as idpegawai,
						pg.nama,
						la.lat,
						la.lon,
						IF(la.konfirmasi="l","'.trans('all.lokasitidakcocok').'",IF(la.konfirmasi="f","'.trans('all.sampelwajahtidakcocok').'",IF(la.konfirmasi="lf" or la.konfirmasi="fl","'.trans('all.lokasitidakcocok').', '.trans('all.sampelwajahtidakcocok').'","-"))) as konfirmasi,
						DATE_FORMAT(la.waktu,"%d/%m/%Y %T") as waktu,
						if(la.masukkeluar="m","<label class=\"label label-primary\">'.trans('all.masuk').'</label>","<label class=\"label label-danger\">'.trans('all.keluar').'</label>") as masukkeluar,
						GROUP_CONCAT(a.nilai SEPARATOR ", ") as nilai
					FROM
						logabsen la,
						pegawai pg
						LEFT JOIN pegawaiatribut pa ON pa.idpegawai=pg.id
						LEFT JOIN atributnilai a ON pa.idatributnilai=a.id
					WHERE
						la.idpegawai=pg.id AND
						la.status = "c"
						'.$where.'
                    GROUP BY
                        la.id
                    ORDER BY
                        la.waktu DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        }else if($menu == 'konfirmasi_flag'){
            $sql = 'SELECT
                        k.id,
                        k.idpegawai,
                        p.nama,
                        k.idlogabsen,
                        k.flag,
                        IF(ISNULL(la.masukkeluar) = false,if(la.masukkeluar="m","<label class=\"label label-primary\">' . trans('all.masuk') . '</label>","<label class=\"label label-danger\">' . trans('all.keluar') . '</label>"),"") as masukkeluar,
                        IFNULL(la.waktu,k.waktu) as waktu,
                        IF(ISNULL(la.konfirmasi) = false,IF(la.konfirmasi="l","' . trans('all.lokasitidakcocok') . '",IF(la.konfirmasi="f","' . trans('all.sampelwajahtidakcocok') . '",IF(la.konfirmasi="lf","' . trans('all.lokasitidakcocok') . ', ' . trans('all.sampelwajahtidakcocok') . '",""))),"") as konfirmasi
                    FROM
                        konfirmasi_flag k
                        LEFT JOIN logabsen la ON k.idlogabsen=la.id,
                        pegawai p
                    WHERE
                        k.idpegawai=p.id AND
                        k.status = "c"
                        ' . $where . '
                    ORDER BY
                        la.waktu DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        //lokasi
        $sql = 'SELECT id,nama,lat,lon FROM lokasi';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $lokasi = $stmt->fetchAll(PDO::FETCH_OBJ);

        $atribut = Utils::getAtribut();

        return view('notifdetail',['data' => $data, 'atribut' => $atribut, 'lokasi' => $lokasi, 'jenis' => $menu, 'menu' => 'beranda']);
    }

    public function submitFilterNotifDetail(Request $request, $jenis){
        if(isset($request->atributnilai)) {
            Session::set('notif'.$jenis.'_atributfilter', $request->atributnilai);
            //keterangan filter
            $atributs = Session::get('notif'.$jenis.'_atributfilter');
            $atributnilai = Utils::atributNilai($atributs);
            $atributnilaiketerangan = '';
            if ($atributnilai != '') {
                $atributnilaidipilih = Utils::getAtributSelected($atributnilai);
                $atributnilaiketerangan = Utils::atributNilaiKeterangan($atributnilaidipilih);
            }
            Session::set('notif'.$jenis.'_keteranganatributfilter', $atributnilaiketerangan);
        }else{
            Session::forget('notif'.$jenis.'_atributfilter');
            Session::forget('notif'.$jenis.'_keteranganatributfilter');
        }

        return redirect('notifdetail/'.$jenis);
    }

    public function customDashboard($idcustomdashboard_node,$tanggal='',$key='')
    {
        $tanggal = $tanggal == 'o' ? '' : $tanggal;
        $atribut = Utils::getAtribut();
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
        $sql = 'SELECT * FROM customdashboard_node WHERE id = :idcustomdashboard_node';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idcustomdashboard_node', $idcustomdashboard_node);
        $stmt->execute();
        $customdashbord_node = $stmt->fetch(PDO::FETCH_OBJ);
        $navigasitanggal = 'y';
        if($customdashbord_node->query_jenis == 'master') {
            if ($customdashbord_node->query_master_periode != 'navigasi-tanggal') {
                $navigasitanggal = 't';
            }
        }else {
            if ($customdashbord_node->query_kehadiran_periode != 'navigasi-tanggal') {
                $navigasitanggal = 't';
            }
        }

        return view('indexdetail2', ['jamkerjakategori' => $jamkerjakategori, 'atribut' => $atribut, 'customdashboard_node' => $customdashbord_node, 'keys' => $key, 'detail' => 'customdashboard', 'navigasitanggal' => $navigasitanggal, 'tanggal' => $tanggal, 'menu' => 'beranda']);
    }

    public function customDashboardAtributFilter(Request $request, $tanggal)
    {
        if(isset($request->atributnilai)) {
            Session::set('customdashboard_atributfilter', $request->atributnilai);
        }else{
            Session::forget('customdashboard_atributfilter');
        }
        if(isset($request->jamkerja)) {
            Session::set('customdashboard_jamkerja', $request->jamkerja);
        }else{
            Session::forget('customdashboard_jamkerja');
        }

//        if(isset($request->kategorijamkerja)) {
//            Session::set('customdashboard_kategorijamkerja', $request->kategorijamkerja);
//        }else{
//            Session::forget('customdashboard_kategorijamkerja');
//        }
    }

    //public function customDashboardData($idcustomdashboard_node, $tanggal='', $startfrom = '', $key = '', $filter = '', $jenisjamkerja = '', $where = '')
    public function customDashboardData($idcustomdashboard_node, $tanggal='', $key = '', $startfrom = '')
    {
        $data = '';
        $jenisdata = '';
        $tampilgroup = 't';
        $pdo = DB::connection('perusahaan_db')->getPdo();

        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        $where_batasan = '';
        if ($batasan!='') {
            $where_batasan = ' AND p.id IN '.$batasan;
        }

        if ($tanggal=='') {
            $currentdate = Utils::getCurrentDate();
            $sql = 'SELECT DATE_FORMAT(:currentdate,"%Y-%m-%d") as tanggal';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':currentdate', $currentdate);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tanggal = $row['tanggal'];
        } else {
            $sql = 'SELECT STR_TO_DATE(:tanggal, "%Y%m%d") as tanggal';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tanggal = $row['tanggal'];
        }

        $startfrom_nama='';
        $sqlWhere_StartFrom = '';

        $sqlFilter = '';
        $filter = '';
        if(Session::has('customdashboard_atributfilter')){
            $atributs = Session::get('customdashboard_atributfilter');
            $filter = Utils::atributNilai($atributs);
        }
        if ($filter!='') {
            $sqlFilter = 'AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$filter.')) ';
        }

        $sqlJenisJamKerja = '';
        $jenisjamkerja = Session::has('customdashboard_jamkerja') ? Session::get('customdashboard_jamkerja') : '';
        if ($jenisjamkerja!='') {
            $sql = 'CALL pegawaijenisjamkerja(:tanggal,:jenisjamkerja)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->bindParam(':jenisjamkerja', $jenisjamkerja);
            $stmt->execute();

            $sqlJenisJamKerja = ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
        }

        $sqlWhere = '';
        $where = '';
        if (Session::has('customdashboard_pencarian_detail')) {
            //$where = '%'.$where.'%';
            $where = '%'.Session::get('customdashboard_pencarian_detail').'%';
            $sqlWhere = ' AND (p.nama LIKE :where1 OR p.nomorhp LIKE :where2) ';
        }

        $sql = 'SELECT
                cdn.id,
                cdn.judul,
                cdn.icon,
                cdn.warna,
                cdn.query_jenis,
                cdn.query_kehadiran,
                cdn.query_kehadiran_data,
                cdn.query_kehadiran_if,
                cdn.query_kehadiran_group,
                cdn.query_kehadiran_periode,
                cdn.query_master_data,
                cdn.query_master_if,
                cdn.query_master_group,
                cdn.query_master_periode
	        FROM
	        	customdashboard_node cdn
	        WHERE
	        	cdn.id=:id
	        LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $idcustomdashboard_node);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $customdashboard = $stmt->fetch(PDO::FETCH_ASSOC);
            $jenisdata = $customdashboard['query_kehadiran_data'];
            if($key == '') {
                if ($customdashboard['query_jenis'] == 'master') {
                    $tampilgroup = $customdashboard['query_master_group'] != '' ? 'y' : 't';
                } else {
                    $tampilgroup = $customdashboard['query_kehadiran_group'] != '' ? 'y' : 't';
                }
            }

            $where_data = '';
            $group_by = '';
            $group_by_select = '';
            $group_by_order = '';
            $where_key = '';
            $kolom_keterangan = '""';
            $kolom_order_by = '';

            $tentukan_startfrom = '';
            $tentukan_orderby = '';

            $from01 = '';
            $where01 = '';

            $adatable_logabsen = 't';
            $adatable_rekapabsen = 't';
            $adatable_rekapshift = 't';
            $adatable_jadwalshift = 't';
            $adatable_ijintidakmasuk = 't';
            $adatable_alasantidakmasuk = 't';
            $adatable_atributnilai = 't';
            $adatable_agama = 't';
            $adatable_jamkerja = 't';
            $adatable_lokasi = 't';
            $adatable_jamkerjashift = 't';
            $adatable_jamkerjashift_jenis = 't';
            $adatable_jamkerjakategori = 't';

            if ($customdashboard['query_jenis']=='kehadiran') {
                Utils::enumCustomDashboard_Kehadiran(
                    $tanggal,
                    $customdashboard,
                    $where_data,
                    'detail',
                    $kolom_keterangan,
                    $kolom_order_by,
                    $adatable_rekapabsen,
                    $adatable_rekapshift,
                    $adatable_ijintidakmasuk,
                    $adatable_alasantidakmasuk
                );

                $where_if = $customdashboard['query_kehadiran_if']; //idpegawai, idatributnilai, idagama, idjamkerja, idlokasi, idjamkerjashift, idjamkerjashift_jenis
                Utils::enumCustomDashboard_WhereIf(
                    'kehadiran',
                    $where_if,
                    $adatable_rekapabsen,
                    $adatable_rekapshift,
                    $adatable_jadwalshift,
                    $adatable_ijintidakmasuk,
                    $adatable_alasantidakmasuk,
                    $adatable_atributnilai,
                    $adatable_agama,
                    $adatable_jamkerja,
                    $adatable_lokasi,
                    $adatable_jamkerjashift,
                    $adatable_jamkerjashift_jenis,
                    $adatable_jamkerjakategori
                );

                if ($key!='') {
                    $where_group_column = Utils::enumCustomDashboard_PrepareGroupBy($customdashboard['query_kehadiran_group']);
                    if ($key=='0') {
                        $where_key = ' AND ISNULL('.$where_group_column.')=true ';
                    }
                    else {
                        $where_key = ' AND '.$where_group_column.'="' . $key . '" ';
                    }
                }

                if ($key== '' || $customdashboard['query_kehadiran_group'] != '') {
                    $group_by = $customdashboard['query_kehadiran_group'];
                    Utils::enumCustomDashboard_GroupBy(
                        'kehadiran',
                        $group_by,
                        $group_by_select,
                        $group_by_order,
                        $adatable_rekapshift,
                        $adatable_jadwalshift,
                        $adatable_ijintidakmasuk,
                        $adatable_alasantidakmasuk,
                        $adatable_agama,
                        $adatable_jamkerja,
                        $adatable_jamkerjashift,
                        $adatable_jamkerjashift_jenis,
                        $adatable_jamkerjakategori
                    );
                }

                Utils::enumCustomDashboard_FromWhere(
                    $tanggal,
                    $from01,
                    $where01,
                    $where_del,
                    $adatable_logabsen,
                    $adatable_rekapabsen,
                    $adatable_rekapshift,
                    $adatable_jadwalshift,
                    $adatable_ijintidakmasuk,
                    $adatable_alasantidakmasuk,
                    $adatable_atributnilai,
                    $adatable_agama,
                    $adatable_jamkerja,
                    $adatable_lokasi,
                    $adatable_jamkerjashift,
                    $adatable_jamkerjashift_jenis,
                    $adatable_jamkerjakategori
                );

                //jika tidak punya group, tampilkan totalnya
                if ($key!= '' || $customdashboard['query_kehadiran_group'] == '') {
                    Utils::enumCustomDashboard_TentukanStartFrom(
                        $pdo,
                        'kehadiran',
                        $customdashboard,
                        $tentukan_startfrom,
                        $tentukan_orderby,
                        $startfrom_nama,
                        $sqlWhere_StartFrom,
                        $startfrom
                    );

                    if ($customdashboard['query_kehadiran_data'] == 'belumabsen') {
                        $from01 = '_pegawai_seharusnya_absen psa, '.$from01;

                        //persiapkan temporary table dahulu
                        $sql = 'CALL pegawai_seharusnya_absen(:tanggal);';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':tanggal', $tanggal);
                        $stmt->execute();
                    }

                    try {
                        $sql = 'SELECT
                                p.id as idpegawai,
                                p.nama as namapegawai,
                                CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
                                getatributtampilpadaringkasan(p.id) as pegawai_atribut,
                                p.nomorhp as pegawai_nomorhp,
                                '.$kolom_keterangan.' as keterangan,
                                '.$tentukan_startfrom.' as startfrom
                            FROM
                                '.$from01.'
                            WHERE
                                1=1
                                '.$where01.'
                                '.$where_del.'
                                '.$where_data.'
                                '.$where_if.'
                                '.$where_batasan.'
                                '.$where_key.'
                                '.$sqlFilter.'
                                '.$sqlJenisJamKerja.'
                                '.$sqlWhere.'
                                '.$sqlWhere_StartFrom.'
                            ORDER BY
                                '.$kolom_order_by.'
                                '.$tentukan_orderby.'
                            LIMIT '.config('consts.LIMIT_6_KOLOM');
//                        return $sql;
                        $stmt = $pdo->prepare($sql);
                        if ($startfrom_nama!='') {
                            $stmt->bindParam(':startfrom_nama', $startfrom_nama);
                        }
                        if ($where!='') {
                            $stmt->bindParam(':where1', $where);
                            $stmt->bindParam(':where2', $where);
                        }
                        $stmt->execute();
                        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

                        //buatkan juga untuk rowcount-nya
                        if ($startfrom=='') {
                            $sql = 'SELECT
                                  count(*) as jumlah
                                FROM
                                    '.$from01.'
                                WHERE
                                    1=1
                                    '.$where01.'
                                    '.$where_del.'
                                    '.$where_data.'
                                    '.$where_if.'
                                    '.$where_batasan.'
                                    '.$where_key.'
                                    '.$sqlFilter.'
                                    '.$sqlJenisJamKerja.'
                                    '.$sqlWhere.'
                                ';
                            $stmt = $pdo->prepare($sql);
                            if ($where!='') {
                                $stmt->bindParam(':where1', $where);
                                $stmt->bindParam(':where2', $where);
                            }
                            $stmt->execute();
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $response['total'] = $row['jumlah'];
                        }
                    } catch (\Exception $e) { }
                }
                else {
                    //jika menggunakan group
                    try {
                        $sql = 'SELECT
                                '.$group_by_select.'
                            FROM
                                '.$from01.'
                            WHERE
                                1=1
                                '.$where01.'
                                '.$where_del.'
                                '.$where_data.'
                                '.$where_if.'
                                '.$where_batasan.'
                                '.$sqlFilter.'
                                '.$sqlJenisJamKerja.'
                                '.$sqlWhere.'
                            GROUP BY
                                '.$group_by.'
                            ORDER BY
                                '.$group_by_order.'
                            ';
//                    echo $sql;return;

                        $stmt = $pdo->prepare($sql);
                        if ($where!='') {
                            $stmt->bindParam(':where1', $where);
                            $stmt->bindParam(':where2', $where);
                        }
                        $stmt->execute();
                        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                    } catch (\Exception $e) { }
                }
//                return $sql.'<br><br>#######'.$where01.'#########'.$where_del.'######'.$where_data.'######'.$where_if.'######'.$where_batasan.'######'.$sqlFilter.'######'.$sqlJenisJamKerja.'######'.$sqlWhere.'######';
            }else if ($customdashboard['query_jenis']=='master') {
                if ($key!='') {
                    $where_group_column = Utils::enumCustomDashboard_PrepareGroupBy($customdashboard['query_master_group']);
                    if ($key=='0') {
                        $where_key = ' AND ISNULL('.$where_group_column.')=true ';
                    }
                    else {
                        $where_key = ' AND '.$where_group_column.'="' . $key . '" ';
                    }
                }

                if ($key== '' || $customdashboard['query_master_group'] != '') {
                    $group_by = $customdashboard['query_master_group'];
                    Utils::enumCustomDashboard_GroupBy(
                        'master',
                        $group_by,
                        $group_by_select,
                        $group_by_order,
                        $adatable_rekapshift,
                        $adatable_jadwalshift,
                        $adatable_ijintidakmasuk,
                        $adatable_alasantidakmasuk,
                        $adatable_agama,
                        $adatable_jamkerja,
                        $adatable_jamkerjashift,
                        $adatable_jamkerjashift_jenis,
                        $adatable_jamkerjakategori
                    );
                }

                $where_if = $customdashboard['query_master_if']; //idpegawai, idatributnilai, idagama, idjamkerja, idlokasi, idjamkerjashift, idjamkerjashift_jenis
                Utils::enumCustomDashboard_WhereIf(
                    'master',
                    $where_if,
                    $adatable_rekapabsen,
                    $adatable_rekapshift,
                    $adatable_jadwalshift,
                    $adatable_ijintidakmasuk,
                    $adatable_alasantidakmasuk,
                    $adatable_atributnilai,
                    $adatable_agama,
                    $adatable_jamkerja,
                    $adatable_lokasi,
                    $adatable_jamkerjashift,
                    $adatable_jamkerjashift_jenis,
                    $adatable_jamkerjakategori
                );

                //jika tidak ada logabsen, rekapabsen, rekapshift, maka, pegawai harus ada del='t'
                $where_del = ' AND ((p.status="a" AND (ISNULL(p.tanggaltdkaktif)=true OR (ISNULL(p.tanggaltdkaktif)=false AND p.tanggalaktif<="'.$tanggal.'"))) OR (p.status="t" AND ISNULL(p.tanggaltdkaktif)=false AND p.tanggaltdkaktif>"'.$tanggal.'"))';

                if ($customdashboard['query_master_data'] == 'pegawai') {

                    Utils::enumCustomDashboard_FromWhere(
                        $tanggal,
                        $from01,
                        $where01,
                        $where_del,
                        $adatable_logabsen,
                        $adatable_rekapabsen,
                        $adatable_rekapshift,
                        $adatable_jadwalshift,
                        $adatable_ijintidakmasuk,
                        $adatable_alasantidakmasuk,
                        $adatable_atributnilai,
                        $adatable_agama,
                        $adatable_jamkerja,
                        $adatable_lokasi,
                        $adatable_jamkerjashift,
                        $adatable_jamkerjashift_jenis,
                        $adatable_jamkerjakategori
                    );

                    if ($key!= '' || $customdashboard['query_master_group'] == '') {

                        Utils::enumCustomDashboard_TentukanStartFrom(
                            $pdo,
                            'master',
                            $customdashboard,
                            $tentukan_startfrom,
                            $tentukan_orderby,
                            $startfrom_nama,
                            $sqlWhere_StartFrom,
                            $startfrom
                        );

                        try {
                            $sql = 'SELECT
                                    p.id as idpegawai,
                                    p.nama as namapegawai,
                                    CONCAT("<span class=detailpegawai onclick=detailpegawai(",p.id,") style=cursor:pointer>",p.nama,"</span>") as nama,
                                    getatributtampilpadaringkasan(p.id) as pegawai_atribut,
                                    p.nomorhp as pegawai_nomorhp,
                                    '.$kolom_keterangan.' as keterangan,
                                    '.$tentukan_startfrom.' as startfrom
                                FROM
                                    ' . $from01 . '
                                WHERE
                                    1=1
                                    ' . $where01 . '
                                    ' . $where_del . '
                                    ' . $where_data . '
                                    ' . $where_if . '
                                    ' . $where_batasan.'
                                    ' . $where_key.'
                                    '.$sqlFilter.'
                                    '.$sqlJenisJamKerja.'
                                    '.$sqlWhere.'
                                    '.$sqlWhere_StartFrom.'
                                ORDER BY
                                    p.nama ASC
                                LIMIT '.config('consts.LIMIT_6_KOLOM');
                            $stmt = $pdo->prepare($sql);
                            if ($startfrom_nama!='') {
                                $stmt->bindParam(':startfrom_nama', $startfrom_nama);
                            }
                            if ($where!='') {
                                $stmt->bindParam(':where1', $where);
                                $stmt->bindParam(':where2', $where);
                            }
                            $stmt->execute();
                            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

                            if ($startfrom=='') {
                                $sql = 'SELECT
                                        COUNT(*) as jumlah
                                    FROM
                                        ' . $from01 . '
                                    WHERE
                                        1=1
                                        ' . $where01 . '
                                        ' . $where_del . '
                                        ' . $where_data . '
                                        ' . $where_if . '
                                        ' . $where_batasan.'
                                        ' . $where_key.'
                                        '.$sqlFilter.'
                                        '.$sqlJenisJamKerja.'
                                        '.$sqlWhere.'
                                    ';
                                $stmt = $pdo->prepare($sql);
                                if ($where!='') {
                                    $stmt->bindParam(':where1', $where);
                                    $stmt->bindParam(':where2', $where);
                                }
                                $stmt->execute();
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $response['total'] = $row['jumlah'];
                            }
                        } catch (\Exception $e) { }
                    }
                    else {
                        //jika menggunakan group
                        try {
                            $sql = 'SELECT
                                    '.$group_by_select.'
                                FROM
                                    '.$from01.'
                                WHERE
                                    1=1
                                    '.$where01.'
                                    '.$where_del.'
                                    '.$where_data.'
                                    '.$where_if.'
                                    '.$where_batasan.'
                                    '.$sqlFilter.'
                                    '.$sqlJenisJamKerja.'
                                    '.$sqlWhere.'
                                GROUP BY
                                    '.$group_by.'
                                ORDER BY
                                    '.$group_by_order;
                            $stmt = $pdo->prepare($sql);
                            if ($where!='') {
                                $stmt->bindParam(':where1', $where);
                                $stmt->bindParam(':where2', $where);
                            }
                            $stmt->execute();
                            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                        } catch (\Exception $e) { }
                    }
                }
            }
        }
//        return $data;
        $totaldata = $data != '' ? count($data) : 0;
        return view('detailmore', ['datas' => $data, 'jenisdata' => $jenisdata, 'idcustomdashboard_node' => $idcustomdashboard_node, 'tanggal' => $tanggal, 'totaldata' => $totaldata, 'tampilgroup' => $tampilgroup, 'totaldatalimit' => config('consts.LIMIT_3_KOLOM'), 'detail' => 'customdashboard', 'menu' => 'beranda']);
    }
}
