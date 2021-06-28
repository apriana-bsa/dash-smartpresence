<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Invoice;
use App\Utils;
use App\Perusahaan;
use App;
use Closure;
use DB;
use PDO;
use Config;

class ConfigMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * untuk mengatur semua keperluan web
     */
    public function handle($request, Closure $next)
    {
        //utc jakarta
        date_default_timezone_set('Asia/Jakarta');
        //conf_ artinya session dari middleware config
        $pdo = DB::getPdo();

        $debug = '';

        //mengatur bahasa web
        if(Session::has('conf_bahasaperusahaan')){
            App::setLocale(Session::get('conf_bahasaperusahaan'));
        }else{
            App::setLocale('id');
            Session::set('conf_bahasaperusahaan', 'id');
        }

        if(!Session::has('versiweb_perusahaan')) {
            Session::set('versiweb_perusahaan', '3.9.8');
        }

        //cek status user
        $sql = 'SELECT status, inserted, onboardingstep, onboardingvideo FROM `user` WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $rowStatus = $stmt->fetch(PDO::FETCH_ASSOC);
            if(date('Y-m-d', strtotime($rowStatus['inserted'])) >= env('ONBOARDING_NEW_USER_RELEASE_DATE', '')){
              //new user
              Session::set('enable_onboarding', 1);
            } else {
              Session::set('enable_onboarding', 0);
            }
            Session::set('onboardingvideo', $rowStatus['onboardingvideo'] && Session::get('enable_onboarding'));
            Session::set('onboardingstep', $rowStatus['onboardingstep']);
            if($rowStatus['status'] == 'b'){
                Auth::logout();
                $bahasa = Session::get('conf_bahasaperusahaan');
                Session::flush(); //menghapus semua session
                Session::set('conf_bahasaperusahaan', $bahasa);
            }
        }

        $apikey = 'AIzaSyA_iruyRPWi6-pJd-diHU6qlSWJBsV6BYg';
        $sql = 'SELECT googlemapsapi FROM pengaturan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        $stmt->execute();
        $rowApiKey = $stmt->fetch(PDO::FETCH_ASSOC);
        if($rowApiKey['googlemapsapi'] != ''){
            $apikey = $rowApiKey['googlemapsapi'];
        }
        Session::set('conf_googlemapsapi', $apikey);

        //user_kotakpesan
        Session::set('conf_user_kotakpesan', Utils::getTotalPesanUser());

        $debug .= ' masuk 1';

        //ajakan
        $sql = 'SELECT COUNT(*) as ajakan FROM ajakan WHERE iduserke = :iduser AND status = "c"';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        $stmt->execute();
        $rowAjakan = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!Session::has('ajakanbaru_perusahaan')){
            Session::set('ajakanbaru_perusahaan', $rowAjakan['ajakan']);
        }else{
            Session::set('ajakanbaru_perusahaan', $rowAjakan['ajakan']);
        }

        $debug .= ' masuk 2';

        if(Session::get('superuser_perusahaan') != ''){
            $debug .= ' masuk superuser';
            if(Session::get('superuser_perusahaan') == 0){ //superuser batasan
                //select idperusahaan dari pengelola
                $sql = 'SELECT group_concat(idperusahaan separator ",") AS idperusahaan FROM pengelola WHERE iduser = :iduser';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                $stmt->execute();
                $pengelola = '';
                if($stmt->rowCount() > 0) {
                    $rowPengelola = $stmt->fetch(PDO::FETCH_ASSOC);
                    $pengelola = $rowPengelola['idperusahaan'];
                }

                //select idperusahaan dari superuser_batasan
                $sql = 'SELECT group_concat(idperusahaan separator ",") AS idperusahaan FROM superuser_batasan WHERE iduser = :iduser';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                $stmt->execute();
                $superuserbatasan = '';
                if($stmt->rowCount() > 0) {
                    $rowSuperuserbatasan = $stmt->fetch(PDO::FETCH_ASSOC);
                    $superuserbatasan = ($pengelola != '' ? ',' : '') . $rowSuperuserbatasan['idperusahaan'];
                }

                $idperusahaangabungan = $pengelola.$superuserbatasan;

                $sqlP = 'SELECT id,nama,status,kode FROM perusahaan WHERE status IN("a","c") AND id IN('.$idperusahaangabungan.') ORDER BY nama';
                $stmtP = $pdo->prepare($sqlP);
                $stmtP->execute();
                $debug .= ' masuk superuser == 0';
            } else {
                $sqlP = 'SELECT id,nama,status,kode FROM perusahaan WHERE status IN("a","c") ORDER BY nama';
                $stmtP = $pdo->prepare($sqlP);
                $stmtP->execute();
                $debug .= ' masuk superuser != 0';
            }
        }else{
            $sqlP = 'SELECT id,nama,status,kode FROM perusahaan WHERE status IN("a","c") AND id IN(SELECT idperusahaan FROM pengelola WHERE iduser = :iduser) ORDER BY nama';
            $stmtP = $pdo->prepare($sqlP);
            $stmtP->bindValue(':iduser', Session::get('iduser_perusahaan'));
            $stmtP->execute();
        }
        $perusahaan = $stmtP->fetchAll(PDO::FETCH_OBJ);
        Session::set('conf_perusahaan', $perusahaan);

        if(Session::has('conf_webperusahaan')){
            $idperusahaan = Session::get('conf_webperusahaan');
            $debug .= ' masuk idperusahaan '.$idperusahaan;
            //cek apakah perusahaan sudah expired
            $sql = 'SELECT IF(aktifsampai<CURRENT_DATE(),"ya","tidak") as expired, GREATEST(0,DATEDIFF(aktifsampai, CURRENT_DATE())) as sisatrial, unitprice, limitpegawai FROM perusahaan_kuota WHERE idperusahaan = :idperusahaan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idperusahaan', $idperusahaan);
            $stmt->execute();
            $rowExpired = $stmt->fetch(PDO::FETCH_ASSOC);
            Session::set('perusahaan_expired', $rowExpired['expired']);
            Session::set('perusahaan_sisatrial', $rowExpired['sisatrial']);
            Session::set('perusahaan_unitprice', $rowExpired['unitprice']);
            Session::set('perusahaan_limitpegawai', $rowExpired['limitpegawai']);

            $returnDataPerusahaan = Perusahaan::where('id', $idperusahaan)->first();
            Session::set('perusahaan_subscription', $returnDataPerusahaan->subscription && $returnDataPerusahaan->iduser == Session::get('iduser_perusahaan'));
            //check user transaction
            $countInvoice = Invoice::where('idperusahaan', $idperusahaan)->where('status_bayar','2')->count();
            Session::set('perusahaan_jumlah_transaksi', $countInvoice);
            //$sql = 'SELECT * FROM hakakses WHERE idperusahaan = :idperusahaan';
            if(Session::get('superuser_perusahaan') != ''){
                $hakaksesSU = (object) array("nama" => "Super Admin", "ajakan" => "i", "alasanmasukkeluar" => "ltuh", "alasantidakmasuk" => "ltuh", "atribut" => "ltuh", "facesample" => "ltuh", "fingersample" => "ltuh", "hakakses" => "ltuh", "harilibur" => "ltuh", "ijintidakmasuk" => "ltuhk","cuti" => "lu", "jamkerja" => "ltuh", "jadwalshift" => "lu", "konfirmasi_flag" => "luk", "payrollpengaturan" => "lu", "payrollkomponenmaster" => "ltuh", "payrollkomponeninputmanual" => "lu", "lokasi" => "ltuh", "logabsen" => "ltuhk", "fingerprintconnector" => "ltuh", "mesin" => "ltuh", "pegawai" => "ltuh", "setulangkatasandipegawai" => "l", "aturatributdanlokasi" => "u", "agama" => "ltuh", "pekerjaan" => "ltuh", "pekerjaanuser" => "ltuh", "pengaturan" => "lu", "pengelola" => "luh", "perusahaan" => "uh", "laporan" => "l", "laporanperpegawai" => "l", "laporanlogabsen" => "l", "laporankehadiran" => "l", "laporanrekapparuhwaktu" => "l", "laporanpertanggal" => "l", "laporanekspor" => "l", "laporanlogtrackerpegawai" => "l", "laporanlainnya" => "l", "laporanperlokasi" => "l", "laporanpekerjaanuser" => "l", "laporancustom" => "ltuhm", "customdashboard" => "ltuh", "riwayatpengguna" => "l", "riwayatpegawai" => "l", "riwayatsms" => "l", "slideshow" => "ltuh", "batasan" => "ltuh", "postingdata" => "i", "hapusdata" => "i", "supervisi" => "i", "notifikasiijintidakmasuk" => "i", "notifikasiriwayatabsen" => "i", "notifikasiterlambat" => "i", "notifikasipulangawal" => "i", "notifikasilembur" => "i", "_flaghapus" => "y");
                $rowhakakses = $hakaksesSU;
            } else {
                $sql = 'SELECT * FROM hakakses WHERE id = (SELECT idhakakses FROM pengelola WHERE iduser = :iduser AND idperusahaan = :idperusahaan LIMIT 1)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                $stmt->bindValue(':idperusahaan', $idperusahaan);
                $stmt->execute();
                $rowhakakses = $stmt->fetch(PDO::FETCH_OBJ);
            }
            $debug .= ' masuk set hak akses';
            Session::set('hakakses_perusahaan', $rowhakakses);

            $sql = 'SELECT dbhost,dbport,dbuser,AES_DECRYPT(dbpass, "e754251708594345576d9407126e4d46") as dbpass,dbname,folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idperusahaan', $idperusahaan);
            $stmt->execute();
            if($stmt->rowCount() > 0) {
                $debug .= ' masuk set koneksi perusahaan';
                $route = $stmt->fetch(PDO::FETCH_OBJ);
                // set koneksi database
                Config::set('database.connections.perusahaan_db.host', $route->dbhost);
                Config::set('database.connections.perusahaan_db.port', $route->dbport);
                Config::set('database.connections.perusahaan_db.username', $route->dbuser);
                Config::set('database.connections.perusahaan_db.password', $route->dbpass);
                Config::set('database.connections.perusahaan_db.database', $route->dbname);

                $sqlWhere = '';
                $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
                if (!($batasan == '' || $batasan == '()')) {
                    $sqlWhere .= ' AND p.id IN ' . $batasan;
                }

                $pdo_p = DB::connection('perusahaan_db')->getPdo();
                //cek apakah payroll diperbolehkan ?
                $sql = 'SELECT payroll_gunakan from pengaturan';
                $stmt = $pdo_p->prepare($sql);
                $stmt->execute();
                $rowPengaturan = $stmt->fetch(PDO::FETCH_ASSOC);
                Session::set('perbolehkanpayroll_perusahaan', $rowPengaturan['payroll_gunakan']);
                $debug .= ' apakah menggunakan payroll ?';

                //hitung total konfirmasi absen
                $konfirmasiLogAbsen = 0;
                if(strpos(Session::get('hakakses_perusahaan')->notifikasiriwayatabsen, 'i') !== false) {
                    $sql = 'SELECT
                            COUNT(*) as jumlah
                        FROM
                            logabsen la,
                            pegawai p
                        WHERE
                            la.idpegawai=p.id AND
                            p.del = "t" AND
                            la.status="c"' . $sqlWhere;
                    $stmt = $pdo_p->prepare($sql);
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $konfirmasiLogAbsen = $row['jumlah'];
                }
                $debug .= ' masuk total konfirmasi : '.$konfirmasiLogAbsen;

                //hitung total konfirmasi ijintidakmasuk
                $konfirmasiIjinTidakMasuk = 0;
                if(strpos(Session::get('hakakses_perusahaan')->notifikasiijintidakmasuk, 'i') !== false) {
                    $sql = 'SELECT
                                COUNT(*) as jumlah
                            FROM
                                ijintidakmasuk itm,
                                pegawai p
                            WHERE
                                itm.idpegawai=p.id AND
                                p.del = "t" AND
                                itm.status="c"' . $sqlWhere;
                    $stmt = $pdo_p->prepare($sql);
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $konfirmasiIjinTidakMasuk = $row['jumlah'];
                }
                $debug .= ' masuk total konfirmasi ijin tidak masuk : '.$konfirmasiIjinTidakMasuk;

                //hitung total konfirmasi flag
                $konfirmasiFlag = 0;
                if(strpos(Session::get('hakakses_perusahaan')->notifikasiriwayatabsen, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasiterlambat, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasipulangawal, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasilembur, 'i') !== false) {
                    $flag = '';
                    if(strpos(Session::get('hakakses_perusahaan')->notifikasiriwayatabsen, 'i') !== false){
                        $flag .= '"lupaabsenmasuk","lupaabsenkeluar",';
                    }
                    if(strpos(Session::get('hakakses_perusahaan')->notifikasiterlambat, 'i') !== false){
                        $flag .= '"tidak-terlambat",';
                    }
                    if(strpos(Session::get('hakakses_perusahaan')->notifikasipulangawal, 'i') !== false){
                        $flag .= '"tidak-pulangawal",';
                    }
                    if(strpos(Session::get('hakakses_perusahaan')->notifikasilembur, 'i') !== false){
                        $flag .= '"lembur",';
                    }
                    $flag = $flag != '' ? substr($flag, 0, -1) : '';
                    $sqlwherekonfirmasi = $flag != '' ? ' AND kf.flag IN('.$flag.')' : '';
//                    $sqlwherekonfirmasi = ' AND kf.flag IN('.$flag.')';
                    $sql = 'SELECT
                                COUNT(*) as jumlah
                            FROM
                                konfirmasi_flag kf,
                                pegawai p
                            WHERE
                                kf.idpegawai=p.id AND
                                p.del = "t" AND
                                kf.status = "c"' . $sqlWhere . $sqlwherekonfirmasi;
                    $stmt = $pdo_p->prepare($sql);
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $konfirmasiFlag = $row['jumlah'];
                }
                $debug .= ' masuk total konfirmasi flag : '.$konfirmasiFlag;

                $konfirmasi = $konfirmasiLogAbsen + $konfirmasiIjinTidakMasuk + $konfirmasiFlag;
                Session::set('conf_konfirmasi', $konfirmasi);
                $debug .= ' masuk totalkonfirmasi : '.$konfirmasi;

                //perlengkapan show_guide cek total pegawai, cek total jamkerja, cek jamkerja pegawai, cek mesin
                Utils::cekGuide();
            }else{
                Session::forget('conf_webperusahaan');
                Session::forget('hakakses_perusahaan');
                Session::forget('pencarian_perusahaan');
                Session::forget('lappertanggal_atribut');
                Session::forget('conf_totalpegawai');
                Session::set('perusahaan_perusahaan', 'Smart Presence');
                $debug .= ' data perusahaan route tidak ditemukan';
            }
        }else{
            if(Session::has('conf_konfirmasi')){
                Session::forget('conf_konfirmasi');
            }
            $debug .= ' user belum memilih perusahaan';
        }

//        Session::set('conf_debug', $debug);

      return $next($request);
    }
}
