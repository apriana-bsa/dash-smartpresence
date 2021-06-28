<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Canopus;
use App\Invoice;
use App\Perusahaan;
use App\Utils;
use Auth;
use DB;
use PDO;
use Hash;
use Storage;

//dihapus kalo sudah tidak diperlukan
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Border;
use PHPExcel_Style_Alignment;
use PHPExcel_Cell_DataType;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    public function index()
	{
        if (Auth::check()) {
          $pdo = DB::getPdo();
          $sql = 'SELECT status FROM `user` WHERE status = "a" AND id = :id';
          $stmt = $pdo->prepare($sql);
          $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
          $stmt->execute();
          if($stmt->rowCount() > 0){
              if(Session::has('userbaru_perusahaan')){
                  Session::forget('userbaru_perusahaan');
              }
          }
          $deskripsibatasan = '';
          if(Session::has('conf_webperusahaan')) {
              $currentdate = Utils::getCurrentDate();
              $waktu_eod = Utils::getCurrentDateTime();

              $sqlWhereID = '';
              $sqlWhereIDpegawai = '';
              $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
              if ($batasan!='') {
                  $sqlWhereID .= ' AND p.id IN '.$batasan;
                  $sqlWhereIDpegawai .= ' AND idpegawai IN '.$batasan;
                  $deskripsibatasan = trans('all.deskripsibatasan');
              }

              $sql = 'SELECT COUNT(*) as totalperusahaan FROM perusahaan WHERE id IN(SELECT idperusahaan FROM pengelola WHERE iduser = :iduser)';
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
              $stmt->execute();
              $rowPerusahaan = $stmt->fetch(PDO::FETCH_ASSOC);
              $totalPerusahaan = $rowPerusahaan['totalperusahaan'];
              
              $pdo = DB::connection('perusahaan_db')->getPdo();

              $sql = 'CALL pegawai_seharusnya_absen(:currentdate)';
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate', $currentdate);
              $stmt->execute();

              //hitung total yang sudah absen
              $sql = 'SELECT 
                            COUNT(DISTINCT idpegawai) as jumlah
                        FROM 
                            _pegawai_seharusnya_absen
                        WHERE 
                            1=1
                            '.$sqlWhereIDpegawai;
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
              $sql = 'SELECT 
                            COUNT(*) as jumlah
                        FROM 
                            pegawai p
                        WHERE 
                            p.del = "t" AND
                            p.status="a"
                        '.$sqlWhereTotalPegawai;
              $stmt = $pdo->prepare($sql);
              $stmt->execute();
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
              $totalPegawai = $row['jumlah'];

              // totalpegawai tidaktaktif
              $sql = 'SELECT 
                            COUNT(*) as jumlah
                        FROM 
                            pegawai p
                        WHERE 
                            p.del = "t" AND
                            p.status != "a"
                        '.$sqlWhereTotalPegawai;
              $stmt = $pdo->prepare($sql);
              $stmt->execute();
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
              $totalPegawaiTidakAktif = $row['jumlah'];

              // totalpegawai terhapus
              $sql = 'SELECT 
                            COUNT(*) as jumlah
                        FROM 
                            pegawai p
                        WHERE 
                            p.del = "y"
                        '.$sqlWhereTotalPegawai;
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
                                    waktu>=CONCAT(:currentdate1) AND waktu<=CONCAT(:currentdate2)
                                   '.$sqlWhereIDpegawai.'
                            )
                            '.$sqlWhereIDpegawai;
                            // waktu>=CONCAT(:currentdate1," 00:00:00") AND waktu<=CONCAT(:currentdate2," 23:59:59")
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate1', $waktu_eod['waktuawal']);
              $stmt->bindValue(':currentdate2', $waktu_eod['waktuakhir']);
              $stmt->execute();
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
              $belumAbsen = $row['jumlah'];
              //$belumAbsen = $totalPegawai - $sudahAbsen;
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
                            la.waktu>=CONCAT(:currentdate1) AND la.waktu<=CONCAT(:currentdate2)'.$sqlWhereID;
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate1', $waktu_eod['waktuawal']);
              $stmt->bindValue(':currentdate2', $waktu_eod['waktuakhir']);
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
                            ra.tanggal=:currentdate '.$sqlWhereID;
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate', $currentdate);
              $stmt->execute();
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
              $terlambat = $row['jumlah'];
              $persenTerlambat = $totalPegawai == 0 ? 0 : round(($terlambat * 100) / $totalPegawai, 2);

              // pulangawal
              $sql = 'SELECT 
                            COUNT(DISTINCT p.id) as jumlah
                        FROM 
                            pegawai p,
                            rekapabsen ra
                        WHERE 
                            p.id=ra.idpegawai AND
                            p.status="a" AND
                            p.del = "t" AND
                            ra.selisihkeluar<0 AND
                            ra.masukkerja = "y" AND
                            ra.tanggal=:currentdate '.$sqlWhereID;
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate', $currentdate);
              $stmt->execute();
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
              $pulangawal = $row['jumlah'];
              $persenPulangAwal = $totalPegawai == 0 ? 0 : round(($pulangawal * 100) / $totalPegawai, 2);
              
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
                                la.waktu>=CONCAT(:currentdate1) AND la.waktu<=CONCAT(:currentdate2)
                                '.$sqlWhereID.'
                            GROUP BY
                                la.idpegawai
                            HAVING 
                                RIGHT(lastabsen,1)="m"
                            ) x';
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate1', $waktu_eod['waktuawal']);
              $stmt->bindValue(':currentdate2', $waktu_eod['waktuakhir']);
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
                        LIMIT 1';
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
                            p.status="a" AND
                            p.del = "t" AND
                            itm.status="a" AND
                            (:currentdate BETWEEN itm.tanggalawal AND itm.tanggalakhir)'.$sqlWhereID;
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate', $currentdate);
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
                            p.del = "t" AND
                            la.waktu>=CONCAT(:currentdate1) AND la.waktu<=CONCAT(:currentdate2)'.$sqlWhereID;
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate1', $waktu_eod['waktuawal']);
              $stmt->bindValue(':currentdate2', $waktu_eod['waktuakhir']);
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
                            la.waktu>=CONCAT(:currentdate1) AND la.waktu<=CONCAT(:currentdate2) AND 
                            lat != 0 AND lon != 0'.$sqlWhereID;
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate1', $waktu_eod['waktuawal']);
              $stmt->bindValue(':currentdate2', $waktu_eod['waktuakhir']);
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
                        la.waktu>=CONCAT(:currentdate1) AND la.waktu<=CONCAT(:currentdate2) AND
                        ISNULL(la.idalasanmasukkeluar) = false'.$sqlWhereID;
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate1', $waktu_eod['waktuawal']);
              $stmt->bindValue(':currentdate2', $waktu_eod['waktuakhir']);
              $stmt->execute();
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
              $totalAlasan = $row['jumlah'];
    
              // grafik
              $sql = 'CALL generategrafikabsen_email(DATE_SUB(:currentdate1, INTERVAL 14-1 DAY), :currentdate2,:email)';
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate1', $currentdate);
              $stmt->bindValue(':currentdate2', $currentdate);
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
                            hl.tanggalawal>=:currentdate1 OR
                            (:currentdate2 BETWEEN hl.tanggalawal AND hl.tanggalakhir)
                          )
                      ORDER BY 
                          hl.tanggalawal ASC
                      LIMIT 3';
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate1', $currentdate);
              $stmt->bindValue(':currentdate2', $currentdate);
              $stmt->execute();
              $harilibur = $stmt->fetchAll(PDO::FETCH_OBJ);

              //total mesin yang digunakan
              $sql = 'SELECT 
                            COUNT(DISTINCT idmesin) as jumlah
                        FROM 
                            logabsen
                        WHERE 
                            waktu>=CONCAT(:currentdate1) AND waktu<=CONCAT(:currentdate2) AND
                            status = "v" '.$sqlWhereIDpegawai;
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':currentdate1', $waktu_eod['waktuawal']);
              $stmt->bindValue(':currentdate2', $waktu_eod['waktuakhir']);
              $stmt->execute();
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
              $mesindigunakan = $row['jumlah'];

              //lokasi
              $sql = 'SELECT id,nama,lat,lon FROM lokasi';
              $stmt = $pdo->prepare($sql);
              $stmt->execute();
              $lokasi = $stmt->fetchAll(PDO::FETCH_OBJ);

              //custom dashboard
              $dataCD = Utils::customDashboard();

              $idcustomdashboard = Utils::getidCustomDashboard();
              if($idcustomdashboard != '') {
                  $custom = Utils::generateCustomDashboard($pdo, date('Y-m-d'), $batasan, Utils::getidCustomDashboard());
              }else{
                  $custom = '';
              }

          }else{
            $sql = 'SELECT "default" as nama,"y" as tampil_konfirmasi,"y" as tampil_peringkat,"y" as tampil_3lingkaran,"y" as tampil_sudahbelumabsen,"y" as tampil_terlambatdll,"y" as tampil_pulangawaldll,"y" as tampil_totalgrafik,"y" as tampil_peta,"y" as tampil_harilibur,"y" as tampil_riwayatdashboard';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $dataCD = $stmt->fetch(PDO::FETCH_OBJ);

            $totalPerusahaan = '';
            $totalPegawai = '';
            $totalPegawaiTidakAktif = '';
            $totalPegawaiTerhapus= '';
            $sudahAbsen = '';
            $persenSudahAbsen = '';
            $belumAbsen = '';
            $persenBelumAbsen = '';
            $terlambat = '';
            $persenTerlambat = '';
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
            $pulangawal = '';
            $persenPulangAwal = '';
            $mesindigunakan = '';
            $lokasi = '';
            $custom = '';
            $currentdate = '';
          }
          Session::set('tampil_konfirmasi', $dataCD->tampil_konfirmasi);
          return view('index', ['totalperusahaan' => $totalPerusahaan,
                              'totalpegawai' => $totalPegawai,
                              'totalpegawaitidakaktif' => $totalPegawaiTidakAktif,
                              'totalpegawaiterhapus' => $totalPegawaiTerhapus,
                              'sudahabsen' => $sudahAbsen,
                              'persensudahabsen' => $persenSudahAbsen,
                              'belumabsen' => $belumAbsen,
                              'persenbelumabsen' => $persenBelumAbsen,
                              'terlambat' => $terlambat,
                              'persenterlambat' => $persenTerlambat,
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
                              'deskripsibatasan' => $deskripsibatasan,
                              'pulangawal' => $pulangawal,
                              'persenpulangawal' => $persenPulangAwal,
                              'mesindigunakan' => $mesindigunakan, //nama menu = data capture
                              'lokasi' => $lokasi, //untuk peta
                              'datacd' => $dataCD, //custom dashboard
                              'custom' => $custom, //custom dashboard node
                              'currentdate' => $currentdate,
                              'menu' => 'beranda']);
        }else{
            return view('login');
        }
    }
    
    public function payrollExcel(){
        $pdo = DB::connection('perusahaan_db')->getPdo();

        $periode = '1810';
        $tanggalawal = '20'.substr($periode, 0, 2).'-'.substr($periode, -2).'-01';
        $tanggalakhir = date("Y-m-t", strtotime($tanggalawal));
        $r = 1; // r = row(baris)

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

        Utils::setPropertiesExcel($objPHPExcel,'Payroll');

        // untuk nama header dan row isian dari query
        $allatribut = Utils::getAllAtribut('blade');
        $atributpenting_controller = ($allatribut['atributpenting_controller'] != '' ? explode('|', $allatribut['atributpenting_controller']) : '');
        $atributpenting_blade = explode('|', $allatribut['atributpenting_blade']);
        $atributvariablepenting_controller = ($allatribut['atributvariablepenting_controller'] != '' ? explode('|', $allatribut['atributvariablepenting_controller']) : '');
        $atributvariablepenting_blade = explode('|', $allatribut['atributvariablepenting_blade']);
        $totalatributvariable = ($atributvariablepenting_controller != '' ? count($atributvariablepenting_controller) : 0);
        $totalatributpenting = ($atributpenting_controller != '' ? count($atributpenting_controller) : 0);
        
        // $sql = 'SELECT id,nama,kode FROM payroll_komponen_master WHERE isnull(kode) = false';
        $sql = 'SELECT id,nama,kode,IFNULL(tipedata,"angka") as tipedata FROM payroll_komponen_master';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $totaldatapayrollkomponenmaster = $stmt->rowCount();
        $datapayrollkomponenmaster = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // return $datapayrollkomponenmaster;

        // pengaturan payroll
        $sql = 'SELECT * FROM payroll_pengaturan';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rowPengaturan = $stmt->fetch(PDO::FETCH_ASSOC);
        // jika pake header
        $r = 8;
        $totalkolom = 2 + $totalatributpenting + $totalatributvariable + $totaldatapayrollkomponenmaster;
        $_ha = Utils::angkaToHuruf($totalkolom);
        $objPHPExcel->getActiveSheet()->setCellValue('A3', strtoupper($rowPengaturan['header1']));
        $objPHPExcel->getActiveSheet()->setCellValue('A4', strtoupper($rowPengaturan['header2']));
        if($rowPengaturan['header3'] != ''){
            $objPHPExcel->getActiveSheet()->setCellValue('A5', strtoupper($rowPengaturan['header3'].' '.Utils::getBulan(substr($periode, -2)).' 20'.substr($periode, 0, 2)));
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A6')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A6')->getFont()->setBold(true);
        //merge cell
        for($_i = 1;$_i<=6;$_i++){
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $_i . ':' . $_ha . $_i);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $_i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        //set value kolom
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$r, trans('all.nama'))
                    ->setCellValue('B'.$r, trans('all.pin'));

        $objPHPExcel->getActiveSheet()->getStyle('A'.$r)->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A'.($r+1))->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$r)->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('B'.($r+1))->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$r.':A'.($r+1));
        $objPHPExcel->getActiveSheet()->mergeCells('B'.$r.':B'.($r+1));
        $objPHPExcel->getActiveSheet()->getStyle('A'.$r)->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$r)->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //set atribut variable
        $i = 3;
        if ($atributvariablepenting_blade != '') {
            //looping untuk header
            foreach ($atributvariablepenting_blade as $key) {
                if ($key != '') {
                    $hh = Utils::angkaToHuruf($i);
                    $objPHPExcel->getActiveSheet()->setCellValue($hh . $r, $key);
                    //font-size
                    $objPHPExcel->getActiveSheet()->getStyle($hh.$r)->getFont()->setSize(12);
                    //lebar kolom
                    $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                    //set bold
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getFont()->setBold(true);
                    //style
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->applyFromArray($styleArray);
                    //merge
                    $objPHPExcel->getActiveSheet()->mergeCells($hh.$r.':'.$hh.($r+1));
                    //center
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                    $i++;
                }
            }
        }

        //set atribut penting
        if ($atributpenting_blade != '') {
            //looping untuk header
            foreach ($atributpenting_blade as $key) {
                if ($key != '') {
                    $hi = $i;
                    $hh = Utils::angkaToHuruf($hi);
                    $objPHPExcel->getActiveSheet()->setCellValue($hh . $r, $key);
                    //font-size
                    $objPHPExcel->getActiveSheet()->getStyle($hh.$r)->getFont()->setSize(12);
                    //lebar kolom
                    $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                    //set bold
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getFont()->setBold(true);
                    //style
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->applyFromArray($styleArray);
                    //merge
                    $objPHPExcel->getActiveSheet()->mergeCells($hh.$r.':'.$hh.($r+1));
                    //center
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                    $i++;
                }
            }
        }

        //untuk komponen payroll
        $sql = 'SELECT id,nama,IFNULL(idpayroll_komponen_master_group,"") as idpayroll_komponen_master_group FROM payroll_komponen_master';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        //rowkp = row komponen payroll
        $idpayrollkomponenmastergroup_old = '';
        while($rowKP = $stmt->fetch(PDO::FETCH_ASSOC)){
            $hi = $i;
            $hh = Utils::angkaToHuruf($hi);
            if($rowKP['idpayroll_komponen_master_group'] == ''){
                $objPHPExcel->getActiveSheet()->setCellValue($hh . $r, $rowKP['nama']);
                //lebar kolom
                $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                //set bold
                $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getFont()->setBold(true);
                //style
                $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->applyFromArray($styleArray);
                //merge
                $objPHPExcel->getActiveSheet()->mergeCells($hh.$r.':'.$hh.($r+1));
                //center
                $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            }else{
                if($idpayrollkomponenmastergroup_old == $rowKP['idpayroll_komponen_master_group']){
                    $hin = $i-1;
                    $hhn = Utils::angkaToHuruf($hin);
                    //merge
                    $objPHPExcel->getActiveSheet()->mergeCells($hhn.$r.':'.$hh.$r);
                }
                $objPHPExcel->getActiveSheet()->setCellValue($hh . $r, strtoupper(Utils::getDataWhere($pdo,'payroll_komponen_master_group','nama','id',$rowKP['idpayroll_komponen_master_group'])));
                $objPHPExcel->getActiveSheet()->setCellValue($hh . ($r+1), $rowKP['nama']);
                //lebar kolom
                $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                //set bold
                $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getFont()->setBold(true);
                //style
                $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->applyFromArray($styleArray);
                //center
                $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $idpayrollkomponenmastergroup_old = $rowKP['idpayroll_komponen_master_group'];
            }
            $objPHPExcel->getActiveSheet()->getStyle($hh.$r)->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle($hh.$r)->getFont()->setSize(12);
            $objPHPExcel->getActiveSheet()->getStyle($hh.($r+1))->getFont()->setSize(12);
            $objPHPExcel->getActiveSheet()->getStyle($hh.$r)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($hh.($r+1))->getFont()->setBold(true);
            $i++;
        }

        // untuk where di query
        $allatribut_controller = Utils::getAllAtribut('controller');
        $atributpenting = $allatribut_controller['atributpenting'];
        $atributvariablepenting = $allatribut_controller['atributvariablepenting'];

        $sql = 'SELECT
                    p.id,
                    p.nama,
                    '.$atributvariablepenting.'
                    p.pin,
                    lower(payroll_getatributnilai(p.id)) as payroll_atributnilai,
                    lower(payroll_getatributvariable(p.id)) as payroll_atributvariable
                    '.$atributpenting.'
                FROM
                    pegawai p,
                    _pegawailengkap pa
                WHERE
                    pa.id=p.id
                ORDER BY
                    p.nama';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $i = $r + 2;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['pin']);

            if($atributvariablepenting_controller != '') {
                $z1 = 3; //huruf setelah kolom terakhir
                for ($j = 0; $j < $totalatributvariable; $j++) {
                    $hv = Utils::angkaToHuruf($z1);
                    $objPHPExcel->getActiveSheet()->setCellValue($hv . $i, $row[$atributvariablepenting_controller[$j]]);
                    $objPHPExcel->getActiveSheet()->getStyle($hv . $i)->applyFromArray($styleArray);

                    $z1++;
                }
            }

            if($atributpenting_controller != '') {
                $z2 = 3 + $totalatributvariable; //iterasi untuk looping atribut penting 2 dari jumlah kolom fix
                for ($j = 0; $j < $totalatributpenting; $j++) {

                    $hap = Utils::angkaToHuruf($z2);
                    $objPHPExcel->getActiveSheet()->setCellValue($hap . $i, $row[$atributpenting_controller[$j]]);
                    $objPHPExcel->getActiveSheet()->getStyle($hap . $i)->applyFromArray($styleArray);

                    $z2++;
                }
            }

            //payroll
            // getPayrollValue($idpegawai,$pegawai_atributnilai,$pegawai_atributvariable,$periode,$tanggalawal,$tanggalakhir)
            $payroll = $this->getPayrollValue($i,  $row['id'],$row['payroll_atributnilai'],$row['payroll_atributvariable'],$periode,$tanggalawal,$tanggalakhir);
            $z3 = 3 + $totalatributvariable + $totalatributpenting; //iterasi untuk looping atribut penting 2 dari jumlah kolom fix
            foreach($datapayrollkomponenmaster as $key){
                $hap = Utils::angkaToHuruf($z3);
                if($key['tipedata'] == 'angka'){
                    $objPHPExcel->getActiveSheet()->setCellValue($hap . $i, $payroll[strtolower($key['kode'])]);
                    $objPHPExcel->getActiveSheet()->getStyle($hap . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    // $objPHPExcel->getActiveSheet()->getStyle($hap . $i)->getNumberFormat()->setFormatCode("_(\"$\"* #,##0.00_);_(\"$\"* \(#,##0.00\);_(\"$\"* \"-\"??_);_(@_)");
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValue($hap . $i, $payroll[strtolower($key['kode'])].' ');
                }
                $objPHPExcel->getActiveSheet()->getStyle($hap . $i)->applyFromArray($styleArray);
                $z3++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleArray);

            // print_r($this->cobaeval($row['id']));
            // echo $payroll['lmb'];
            // echo "<br><br>";

            $i++;
        }
        // return;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);

        $objPHPExcel->getActiveSheet()->getStyle('A'.$r)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$r)->getFont()->setBold(true);

        //footer
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+3), $rowPengaturan['footer1']);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+4), $rowPengaturan['footer2']);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+5), $rowPengaturan['footer3']);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+6), $rowPengaturan['footer4']);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+12), $rowPengaturan['ttd']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i+3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i+6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i+12))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($i+3) . ':C' . ($i+3));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($i+4) . ':C' . ($i+4));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($i+5) . ':C' . ($i+5));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($i+6) . ':C' . ($i+6));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($i+12) . ':C' . ($i+12));

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.($i+3).':A'.($i+12))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A'.($i+3).':A'.($i+12))->getFont()->setBold(true);

        Utils::passwordExcel($objPHPExcel);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . time() . '_' . trans('all.payroll') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $writer->save('php://output');
    }

    public function payroll_posting($periode) {
        Utils::payroll_init_eval();

        $pdo = DB::connection('perusahaan_db')->getPdo();

        //buat dulu tanggalawal dan tanggalakhir
        $sql = 'CALL payroll_getperiode(:periode, @tanggalawal, @tanggalakhir)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':periode',$periode);
        $stmt->execute();

        $sql = 'SELECT @tanggalawal as tanggalawal, @tanggalakhir as tanggalakhir';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pengaturan = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tanggalawal = $pengaturan[0]['tanggalawal'];
        $tanggalakhir = $pengaturan[0]['tanggalakhir'];

        //ambil data pegawai
        $sql = 'SELECT
                    id
                FROM
                    pegawai
                WHERE
                    del="t" AND
                    status="a"
                ORDER BY
                    nama';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //ambil data komponen_master
        $sql = 'SELECT
                    pkm.id as id,
                    pkm.nama as nama,
                    lower(pkm.kode) as kode,
                    pkm.tipedata as tipedata,
                    pkm.carainput as carainput,
                    pkm.formula,
                    IF(ISNULL(pp.komponenmaster_total)=FALSE,"y","t") as istotal,
                    IFNULL(pkmg.nama,"") as `group`,
                    pkm.urutan as urutan                     
                FROM
                    payroll_komponen_master pkm
                    LEFT JOIN payroll_komponen_master_group pkmg ON pkmg.id = pkm.idpayroll_komponen_master_group
                    LEFT JOIN payroll_pengaturan pp ON pp.komponenmaster_total = pkm.id
                WHERE
                    pkm.digunakan="y"
                ORDER BY
                    pkm.urutan ASC, pkm.nama ASC, pkm.id ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $komponen_master = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array();
        for($i=0;$i<count($pegawai);$i++) {
            $result[$i] = $this->getPayrollValue($pdo, $i, $pegawai[$i]['id'], $periode, $tanggalawal, $tanggalakhir, $komponen_master);

            // echo '<xmp>';
            // print_r($result[$i]);
            // echo '</xmp>';
            // return;
        }

        try {
            $pdo->beginTransaction();

            $total = 0;

            // hapus payroll yang periodenya sama
            Utils::deleteData($pdo,'payroll_posting',$periode,'periode');

            // tambahkan ke table payroll_posting
            $sql = 'INSERT INTO payroll_posting VALUES(NULL, :periode, 0, "", NOW(), NULL)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':periode', $periode);
            $stmt->execute();
            $payroll_posting_id = $pdo->lastInsertId();
            
            // tambahkan ke table payroll_posting_komponen
            for($i=0;$i<count($komponen_master);$i++) {
                $sql = 'INSERT INTO payroll_posting_komponen VALUES(
                            NULL, 
                            :payroll_posting_id, 
                            :komponenmaster_id,
                            :komponenmaster_nama,
                            :komponenmaster_kode,
                            :komponenmaster_tipedata,
                            :komponenmaster_carainput,
                            :komponenmaster_istotal,
                            :komponenmaster_group,
                            :komponenmaster_urutan
                        )';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':payroll_posting_id', $payroll_posting_id);
                $stmt->bindValue(':komponenmaster_id', $komponen_master[$i]['id']);
                $stmt->bindValue(':komponenmaster_nama', $komponen_master[$i]['nama']);
                $stmt->bindValue(':komponenmaster_kode', $komponen_master[$i]['kode']);
                $stmt->bindValue(':komponenmaster_tipedata', $komponen_master[$i]['tipedata']);
                $stmt->bindValue(':komponenmaster_carainput', $komponen_master[$i]['carainput']);
                $stmt->bindValue(':komponenmaster_istotal', $komponen_master[$i]['istotal']);
                $stmt->bindValue(':komponenmaster_group', $komponen_master[$i]['group']);
                $stmt->bindValue(':komponenmaster_urutan', $komponen_master[$i]['urutan']);
                $stmt->execute();

                $komponen_master[$i]['idpayroll_posting_komponen'] = $pdo->lastInsertId();
            }

            for($i=0;$i<count($result);$i++) {
                $total = $total + $result[$i]['total'];
                for($j=0;$j<count($komponen_master);$j++) {
                    $sql = 'INSERT INTO payroll_posting_pegawai VALUES(
                                NULL, 
                                :payroll_posting_id, 
                                :idpegawai, 
                                :idpayroll_posting_komponen, 
                                :nama,
                                :result_nominal, 
                                IFNULL(:result_keterangan,""),
                                ""
                            )';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':payroll_posting_id', $payroll_posting_id);
                    $stmt->bindValue(':idpegawai', $result[$i]['idpegawai']);
                    $stmt->bindValue(':idpayroll_posting_komponen', $komponen_master[$j]['idpayroll_posting_komponen']);
                    $stmt->bindValue(':nama', $result[$i]['nama']);
                    $stmt->bindValue(':result_nominal', $result[$i]['komponen'][$j]['result_nominal']);
                    $stmt->bindValue(':result_keterangan', $result[$i]['komponen'][$j]['result_keterangan']);
                    $stmt->execute();
                }
            }

            //update total payroll
            $sql = 'UPDATE payroll_posting SET total=:total WHERE id=:payroll_posting_id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':payroll_posting_id', $payroll_posting_id);
            $stmt->bindValue(':total', $total);
            $stmt->execute();
            
            $pdo->commit();
        }
        catch(\Exception $e) {
            $pdo->rollBack();
            return $e->getMessage();
        }
    }

    public function getPayrollValue($pdo, $counter, $idpegawai, $periode, $tanggalawal, $tanggalakhir, $komponen_master) {

        $COUNTER = $counter;

        $sql = 'SELECT
                    p.nama,
                    p.idagama,
                    IFNULL(a.agama, "") as agama,
                    p.pin,
                    p.pemindai,
                    p.nomorhp,
                    p.flexytime,
                    p.status,
                    p.tanggalaktif,
                    lower(payroll_getatributnilai(p.id)) as payroll_atributnilai,
                    lower(payroll_getatributvariable(p.id)) as payroll_atributvariable
                FROM
                    pegawai p
                    LEFT JOIN agama a ON a.id=p.idagama
                WHERE 
                    p.id = :idpegawai';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $nama = $row[0]['nama'];

            Utils::payroll_fetch_pegawai($PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $row[0]);

            // ambil rekapabsen
            $sql = "SELECT * FROM rekapabsen WHERE idpegawai=:idpegawai AND tanggal BETWEEN :tanggalawal AND :tanggalakhir";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":idpegawai",$idpegawai);
            $stmt->bindValue(":tanggalawal",$tanggalawal);
            $stmt->bindValue(":tanggalakhir",$tanggalakhir);
            $stmt->execute();
            $REKAPABSEN = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ambil komponen_master
            $PAYROLL = array();
            for($i=0;$i<count($komponen_master);$i++) {
                if ($komponen_master[$i]['carainput']=='inputmanual') {
                    $sql = 'SELECT
                                IFNULL(nominal,0) as nominal,
                                keterangan
                            FROM
                                payroll_komponen_inputmanual
                            WHERE
                                idpayroll_komponen_master=:idpayroll_komponen_master AND
                                periode=:periode AND
                                idpegawai=:idpegawai
                            LIMIT 1';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpayroll_komponen_master', $komponen_master[$i]['id']);
                    $stmt->bindValue(':periode', $periode);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->execute();
                    if ($stmt->rowCount()>0) {
                        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if($komponen_master[$i]['tipedata'] == 'angka'){
                            $PAYROLL[$komponen_master[$i]['kode']] = $row[0]['nominal'];
                        }else{
                            $PAYROLL[$komponen_master[$i]['kode']] = $row[0]['keterangan'];
                        }
                    }
                    else {
                        $PAYROLL[$komponen_master[$i]['kode']] = 0;
                    }
                }
                else {
                    $PAYROLL[$komponen_master[$i]['kode']] = 0;
                }
            }
        
            $script = '';
            // buat formula menjadi temporary fungsi (function_i)
            for($i=0;$i<count($komponen_master);$i++) {
                if ($komponen_master[$i]['carainput']=='formula' && $komponen_master[$i]['formula']!='') {
                    $formula = $komponen_master[$i]['formula'];
                    $lines = explode(PHP_EOL, $formula);
                    $temp_formula = '';
                    for($j=0;$j<count($lines);$j++) {
                        $temp_formula = $temp_formula . '   '. $lines[$j].PHP_EOL;
                    }
                    $temp_formula = PHP_EOL.'$formula_'.$i.' = function($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $REKAPABSEN, $PAYROLL){'.PHP_EOL.$temp_formula. '  return $result;'.PHP_EOL.'};'.PHP_EOL; 
                    $script = $script . $temp_formula;
                }
            }
            $script = $script . PHP_EOL;
            // panggil temporary function_i
            for($i=0;$i<count($komponen_master);$i++) {
                $kode = strtolower($komponen_master[$i]['kode']);
                if ($komponen_master[$i]['carainput']=='formula' && $komponen_master[$i]['formula']!='') {
                    $script = $script.'$PAYROLL["'.$kode.'"] = $formula_'.$i.'($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $REKAPABSEN, $PAYROLL);'.PHP_EOL;
                }
            }
            //buang (unset) temporary function_i tersebut
            $script = $script.PHP_EOL;
            for($i=0;$i<count($komponen_master);$i++) {
                $kode = strtolower($komponen_master[$i]['kode']);
                if ($komponen_master[$i]['carainput']=='formula' && $komponen_master[$i]['formula']!='') {
                    $script = $script.'unset($formula_'.$i.');'.PHP_EOL;
                }
            }
            $script = $script.PHP_EOL;
            $script = $script.'unset($get);'.PHP_EOL;
            $script = $script.'unset($get_counter);'.PHP_EOL;

            Utils::payroll_replace_variablescript($script);
            eval($script);

            $result = array();
            $result['idpegawai'] = $idpegawai;
            $result['nama'] = $nama;
            $result['total'] = 0;
            $result['komponen'] = array();

            for ($i=0;$i<count($komponen_master);$i++) {
                if ($komponen_master[$i]['tipedata']=='angka') {
                    $result['komponen'][$i]['result_nominal'] = $PAYROLL[$komponen_master[$i]['kode']];
                    $result['komponen'][$i]['result_keterangan'] = '';
                    if ($komponen_master[$i]['istotal']=='y') {
                        $result['total']= $result['komponen'][$i]['result_nominal'];
                    }
                }
                else if ($komponen_master[$i]['tipedata']=='teks') {
                    $result['komponen'][$i]['result_nominal'] = 0;
                    $result['komponen'][$i]['result_keterangan'] = $PAYROLL[$komponen_master[$i]['kode']];
                }
            }
            return $result;
        }

        return '';
    }

    public function disableVideoOnboarding(Request $request){
      if (Auth::check()) {
        try {
          $pdo = DB::getPdo();
          $sql = 'UPDATE user SET onboardingvideo = :show WHERE id = :id';
          $stmt = $pdo->prepare($sql);
          $stmt->bindValue(':show', 0);
          $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
          $stmt->execute();

          Session::set('onboardingvideo', 0);
          return response()->json([
            'message' => 'success',
          ]);

        } catch (\Exception $e) {
          return response()->json([
            'message' => $e->getMessage(),
          ]);
        }
      } else {
        return response()->json([
          'message' => 'unauthorized',
        ]);
      }
    }

    public function pembayaran(){
      if (Auth::check()) {
        return view('index_expired', ['menu' => 'pembayaran']);
      } else {
        return redirect('/')->with('message', "Silakan log in terlebih dahulu");
      }
    }

    public static function incrementOnboardingstep($onboardingstep){
        if(Session::get('onboardingstep')==$onboardingstep) {
            $pdo = DB::getPdo();
            $sql = 'UPDATE user SET onboardingstep = :step WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':step', $onboardingstep + 1);
            $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
            $stmt->execute();
        }
    }

    public function checkout(Request $request){
      if (Auth::check()) {
        $idperusahaan = Session::get('conf_webperusahaan');
        $periode = $request->periode;
        if($periode == 1 || $periode == 3 || $periode == 6 || $periode==12) {
          $kuota = Session::get('perusahaan_limitpegawai');
          if($periode==1) {
            $kuota = $request->kuota_1bulan ? $request->kuota_1bulan : Session::get('perusahaan_limitpegawai');
          } else if($periode==3) {
            $kuota = $request->kuota_3bulan ? $request->kuota_3bulan : Session::get('perusahaan_limitpegawai');
          } else if($periode==6) {
            $kuota = $request->kuota_6bulan ? $request->kuota_6bulan : Session::get('perusahaan_limitpegawai');
          } else if($periode==12) {
            $kuota = $request->kuota_12bulan ? $request->kuota_12bulan : Session::get('perusahaan_limitpegawai');
          }

          $pegawaiCount = Utils::getTotalData(1, 'pegawai', 'del = \'t\'');
//            return redirect(url()->previous())->with('error', $pegawaiCount." - ".Session::get('perusahaan_limitpegawai')." - ".$kuota);
//          if($kuota < Session::get('perusahaan_limitpegawai') && $pegawaiCount > $kuota) {
//            return redirect(url()->previous())->with('error', 'Jumlah pegawai yang sudah ada melebihi kuota yang anda masukkan, silakan hapus beberapa pegawai terlebih dahulu');
//          }

          if($pegawaiCount > $kuota) {
              return redirect(url()->previous())->with('overkuota', trans("all.overkuota"));
          }

          $total = Session::get('perusahaan_unitprice') * $kuota * $periode;
          if($periode == 12) {
            $discount = Session::get('perusahaan_unitprice') > env('YEARLY_DISCOUNT',0) ? env('YEARLY_DISCOUNT',0) : 0;
            $total = (Session::get('perusahaan_unitprice') - $discount) * $kuota * $periode;
          }
          $total = $total * 1.1; //with ppn10%
          //getperusahaankuota
          $pdo = DB::getPdo();
          $sql = 'SELECT aktifsampai FROM `perusahaan_kuota` WHERE idperusahaan = :id';
          $stmt = $pdo->prepare($sql);
          $stmt->bindValue(':id', $idperusahaan);
          $stmt->execute();
          $perusahaanKuota = $stmt->fetch(PDO::FETCH_ASSOC);

          //insert invoice
          $invoice = new Invoice();
          $invoice->idperusahaan = $idperusahaan;
          $invoice->total = $total;
          $invoice->periode = $periode;
          $invoice->status_bayar = '0';
          $invoice->user_kuota = $kuota;
          $invoice->created_by = Session::get('iduser_perusahaan');
          $invoice->due_date = $perusahaanKuota['aktifsampai'];
          $invoice->save();
          //update invoice
          $invoiceId = $invoice->id;
          $invoiceupdate = Invoice::find($invoiceId);
          $invoiceupdate->order_id = 'SPA'.$idperusahaan.'-'.$invoiceId;
          $invoiceupdate->save();
          //getuser
          $pdo = DB::getPdo();
          $sql = 'SELECT nama, nomorhp, email FROM `user` WHERE id = :id';
          $stmt = $pdo->prepare($sql);
          $stmt->bindValue(':id', $invoiceupdate->created_by);
          $stmt->execute();
          $user = $stmt->fetch(PDO::FETCH_ASSOC);
          //canopus param
          $data = array (
            'orderId' => $invoiceupdate->order_id,
            'amount' => $invoiceupdate->total,
            'periode' => $invoiceupdate->periode,
            'customerDetails' => array (
                'firstName' => $user['nama'],
                'email' => $user['email'],
                'phone' => $user['nomorhp']
              )
          );

          $canopus = Canopus::createCart($data);
          // [ "status" => true/false, "message"=> "message success / error", "checkout_url"=> "url" ]
          if($canopus['status']) {
            return redirect($canopus['checkoutUrl']);
          } else {
            $invoiceupdate->delete();
            return redirect(url()->previous())->with('error', $canopus['message']);
          }
        } else {
          return redirect(url()->previous())->with('error', "Periode Not Allowed");
        }
      } else {
        return redirect('/')->with('message', "Silakan log in terlebih dahulu");
      }
    }
    // public function getPayrollValue(){
    //     $periode = '1810';
    //     $bataspresensi_tanggalawal = '2018-10-01';
    //     $bataspresensi_tanggalakhir = '2018-10-31';
    //     $idpegawai = 1167;
    //     $url = 'http://dash.smartpresence.test:8080/tes/10950';

    //     $curl = curl_init();

    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_TIMEOUT => 30000,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => "GET"
    //     ));
    //     $result = curl_exec($curl);
    //     $err = curl_error($curl);
    //     // Close connection
    //     curl_close($curl);

    //     $json = json_decode($result, true);
    //     return $json['uktp'];
    // }

    // public function cobaeval($idpegawai,$idpayrollkomponenmaster=3,$periode='1810',$tanggalawal='2018-10-01',$tanggalakhir='2018-10-31') {
    //     // $temp_formula = '$result = <km>GPOK</km> + <km>UKTP</km> + <km>INSL</km> + <km>HARDS</km> + <km>TUNJD</km> + 10000000;';
    //     // return preg_replace('/\<km\>\w+<\/km\>/', '$komponen_master_result["$0"]', $temp_formula);
        
    //     $idperusahaan = Session::get('conf_webperusahaan');
    
    //     // $pdoa = DB::getPdo();
    //     // $sql = 'SELECT dbhost,dbport,dbuser,AES_DECRYPT(dbpass, "e754251708594345576d9407126e4d46") as dbpass,dbname,folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
    //     // $stmt = $pdoa->prepare($sql);
    //     // $stmt->bindValue(':idperusahaan', $idperusahaan);
    //     // $stmt->execute();
    //     // if($stmt->rowCount() > 0) {
    //     //     $route = $stmt->fetch(PDO::FETCH_OBJ);
    //     //     // set koneksi database
    //     //     Config::set('database.connections.perusahaan_db.host', $route->dbhost);
    //     //     Config::set('database.connections.perusahaan_db.port', $route->dbport);
    //     //     Config::set('database.connections.perusahaan_db.username', $route->dbuser);
    //     //     Config::set('database.connections.perusahaan_db.password', $route->dbpass);
    //     //     Config::set('database.connections.perusahaan_db.database', $route->dbname);
    //     // }
    
    //     $pdo = DB::connection('perusahaan_db')->getPdo();
    //     // $periode = '1810';
    //     // $idpayrollkomponenmaster = Session::get('payrollkomponeninputmanual_payrollkomponenmaster');
    //     // $idpayrollkomponenmaster = 3;
    //     // $tanggalawal = '2018-10-01';
    //     // $tanggalakhir = '2018-10-31';
    //     // $idpegawai = 1167;
    
    //     $sql = 'SELECT
    //                 p.id,
    //                 p.pin,
    //                 p.nama,
    //                 CONCAT("<span title=\"",p.nama,"\" class=\"detailpegawai\" onclick=\"detailpegawai(,p.id,)\" style=\"cursor:pointer;\">",p.nama,"</span>") as pegawai,
    //                 getatributpegawai_all(p.id) as atribut,
    //                 lower(payroll_getatributnilai(p.id)) as payroll_atributnilai,
    //                 lower(payroll_getatributvariable(p.id)) as payroll_atributvariable,
    //                 prki.nominal
    //             FROM
    //                 pegawai p
    //                 LEFT JOIN payroll_komponen_inputmanual prki ON prki.idpegawai=p.id AND prki.idpayroll_komponen_master = '.$idpayrollkomponenmaster.'
    //                 LEFT JOIN pegawaiatribut pa ON pa.idpegawai=p.id
    //                 LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
    //                 LEFT JOIN atribut a ON an.idatribut=a.id
    //             WHERE
    //                 p.del = "t" AND p.id=1170
    //             GROUP BY
    //                 p.id
    //             ORDER BY
    //                 p.nama';
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->execute();
    //     $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    //     $pegawai_atributnilai = $row[0]['payroll_atributnilai'];
    //     $pegawai_atributvariable = $row[0]['payroll_atributvariable'];
    
    //     $sql = 'SELECT
    //                 pkm.id,
    //                 pkm.kode,
    //                 pkm.carainput,
    //                 pkm.formula,
    //                 IFNULL(pkim.nominal,0) as nominal
    //             FROM
    //                 payroll_komponen_master pkm
    //                 LEFT JOIN payroll_komponen_inputmanual pkim ON pkim.periode=:periode AND pkim.idpegawai=:idpegawai AND pkim.idpayroll_komponen_master=pkm.id
    //             ORDER BY
    //                 pkm.urutan ASC
    //            ';
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->bindValue(':periode', $periode);
    //     $stmt->bindValue(':idpegawai', $idpegawai);
    //     $stmt->execute();
    //     $komponen_master = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    //     $komponen_master_result = array();
    //     for($i=0;$i<count($komponen_master);$i++) {
    //         $kode = '<km>'.strtolower($komponen_master[$i]['kode']).'</km>';
    //         if ($komponen_master[$i]['carainput']=='inputmanual') {
    //             $komponen_master_result[$kode] = $komponen_master[$i]['nominal'];
    //         }
    //         else {
    //             $komponen_master_result[$kode] = 0;
    //         }
    //     }
    
    //     $script = '';
    
    //     // macam2 tag: av, an, pr, km
    //     for($i=0;$i<count($komponen_master);$i++) {
    //         // if ($komponen_master[$i]['id']==6) {
    //         if ($komponen_master[$i]['carainput']=='formula' && $komponen_master[$i]['formula']!='') {
    //             $formula = $komponen_master[$i]['formula'];
    //             $formula = str_replace('get_rekapabsen()', 'get_rekapabsen("'.$idpegawai.'","'.$tanggalawal.'","'.$tanggalakhir.'")', $formula);
    //             $lines = explode(PHP_EOL, $formula);
    //             $temp_formula = '';
    //             for($j=0;$j<count($lines);$j++) {
    //                 $temp_formula = $temp_formula . '  '. $lines[$j].PHP_EOL;
    //             }
    
    //             $arr_tag_atributnilai = Utils::payroll_SliceTag($formula, '/\<an\>\w+\<\/an\>/');
    //             $arr_payroll_atributnilai = Utils::payroll_Explode3level($pegawai_atributnilai, '|:;', 'an');
    //             $temp_formula = Utils::payroll_ReplaceTag($arr_tag_atributnilai, $arr_payroll_atributnilai, $temp_formula, "array");  
    
    //             $arr_tag_atributvariable = Utils::payroll_SliceTag($formula, '/\<av\>\w+\<\/av\>/');
    //             $arr_payroll_atributvariable = Utils::payroll_Explode2level($pegawai_atributvariable, '|:', 'av');
    //             $temp_formula = Utils::payroll_ReplaceTag($arr_tag_atributvariable, $arr_payroll_atributvariable, $temp_formula, "teks");  
    
    //             // $arr_tag_komponenmaster = Utils::payroll_SliceTag($formula, '/\<km\>\w+\<\/km\>/');
    //             // $temp_formula = Utils::payroll_ReplaceTag($arr_tag_komponenmaster, $komponen_master_result, $temp_formula, "angka");  
    
    //             $temp_formula = preg_replace('/\<km\>\w+<\/km\>/', '$komponen_master_result[strtolower("$0")]', $temp_formula);
    //             $temp_formula = PHP_EOL.'$formula_'.$i.' = function($komponen_master_result){'.PHP_EOL.$temp_formula. '  return $result;'.PHP_EOL.'};'.PHP_EOL; 
    //             $script = $script . $temp_formula;
    //         }
    //     }
    //     $script = $script . PHP_EOL;
    //     // $script = $script.str_replace('$', '$___', $temp_formula);
    //     for($i=0;$i<count($komponen_master);$i++) {
    //         $kode = '<km>'.strtolower($komponen_master[$i]['kode']).'</km>';
    //         if ($komponen_master[$i]['carainput']=='formula' && $komponen_master[$i]['formula']!='') {
    //             $script = $script.'$komponen_master_result["'.$kode.'"] = $formula_'.$i.'($komponen_master_result);'.PHP_EOL.'unset($formula_'.$i.');';
    //         }
    //     }
    //     // echo "<xmp>";
    //     // echo $script;
    //     // echo "</xmp>";
    
    //     eval($script);
    
    //     $result = array();
    //     foreach ($komponen_master_result as $key => $value) {
    //         $key_new = str_replace('<km>', '', str_replace('</km>', '', str_replace(' ', '', $key)));
    //         $result[$key_new] = $value;
    //     }
    //     return $result;
    
    //     // $result = array();
    //     // for($i=0;$i<count($komponen_master_result);$i++){
    //     //     $result[$i]['kode'] = $komponen_master_result['kode'];
    //     // }
    //     // return $result;
    
    //     // print_r($komponen_master_result);
    //     // return $komponen_master_result['<km>lmb</km>'];
    //     // return $komponen_master_result;
    
    //    // $url = Request::input('file');
    //    // return $url;
    // //    $datetime1 = strtotime("2018-10-02 08:00:00");
    // //     $datetime2 = strtotime("2018-10-02 17:00:00");
    // //     $interval  = abs($datetime2 - $datetime1);
    // //     $minutes   = round($interval / 3600);
    // //     echo 'Diff. in minutes is: '.$minutes; 
    //     // $string = '!@#$%^&*() tester bro';
    //     // if (strlen($string) != strlen(utf8_decode($string)))
    //     // {
    //     //     return 'is unicode';
    //     // }else{
    //     //     return 'normal string';
    //     // }
    //     // return Utils::cekPegawaiAtributNilai('1170','281') == true ? 'true' : 'false';
    //     // return Utils::cekPegawaiAtributNilai('1170','281');
    //     //  $waktu = Utils::getCurrentDateTime();
    //     //  return $waktu['waktuawal'].' '.$waktu['waktuakhir'];
    //      //return Utils::list_yymm_before(12);
    //      //return $_SERVER['DOCUMENT_ROOT'] .'/'.config('consts.FOLDER_RAW').'/script.sql';
    // }

}