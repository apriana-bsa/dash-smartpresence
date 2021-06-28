<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use App\Utils;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_Writer_Excel2007;
use PHPExcel_IOFactory;
use PHPExcel_Style_Border;
use PHPExcel_Style_Alignment;
use PHPExcel_Cell_DataType;
use PHPExcel_Worksheet_MemoryDrawing;
use FPDF;

class PayrollPostingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
	 * Displays datatables front end view
	 *
	 * @return \Illuminate\View\View
	 */

	public function index()
	{
        if(Utils::cekHakakses('payrollpengaturan','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $datakelompok = Utils::getData($pdo,'payroll_kelompok','id,nama','','nama');
            $data = '';
            if(Session::has('postingdata_idkelompok')) {
                $sql = 'SELECT
                            pp.id,
                            pp.periode,
                            pp.tanggalawal,
                            pp.tanggalakhir,
                            pp.total,
                            pp.keterangan,
                            IFNULL(x.nilai,"") as atributnilai,
                            DATE_FORMAT(pp.inserted, "%d/%m/%Y %T") as inserted
                        FROM
                            payroll_posting pp
                            LEFT JOIN (
                                SELECT
                                    ppa.idpayroll_posting,
                                    IFNULL(GROUP_CONCAT(an.nilai ORDER BY an.nilai SEPARATOR ", "),"") as nilai
                                FROM
                                    payroll_posting_atribut ppa
                                    LEFT JOIN atributnilai an ON ppa.idatributnilai = an.id
                                GROUP BY
                                    ppa.idpayroll_posting
                            ) x ON x.idpayroll_posting=pp.id,
                            payroll_posting_komponen ppk,
                            payroll_komponen_master pkm
                        WHERE
                            ppk.idpayroll_posting = pp.id AND
                            ppk.komponenmaster_id = pkm.id AND
                            pkm.idpayroll_kelompok = '.Session::get('postingdata_idkelompok').'
                        GROUP BY
                            pp.id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $data = '';
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                }
            }
            Utils::insertLogUser('akses menu payroll posting');
//            $listyymm = Utils::list_yymm(1);
	        return view('datainduk/payroll/payrollposting/index', ['datakelompok' => $datakelompok, 'data' => $data, 'menu' => 'payrollposting']);
        }else{
            return redirect('/');
        }
    }

    public function submit(Request $request){
	    if($request->kelompok != ''){
	        Session::set('postingdata_idkelompok', $request->kelompok);
        }
        return redirect('datainduk/payroll/payrollposting');
    }

    public function generate(){
        $listyymm = Utils::list_yymm(1);
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $kelompok = Utils::getDataWhere($pdo,'payroll_kelompok_atribut','group_concat(idatributnilai)','idpayroll_kelompok',Session::get('postingdata_idkelompok'));
        $kelompok = $kelompok != '' ? '('.$kelompok.')' : '';
        $dataatribut = Utils::getAtribut($kelompok);
        $sql = 'SELECT periode,pertanggal FROM payroll_pengaturan';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rowpengaturan = $stmt->fetch(PDO::FETCH_ASSOC);
        if($rowpengaturan['periode'] == 'bulanan') {
            $tanggal = Utils::valueTanggalAwalAkhir();
        }else{
            $pertanggal = str_pad(5,2,"0",STR_PAD_LEFT);
            $bulan = date('m');
            $tahun = date('Y');
            $tgl = $tahun.'-'.$bulan.'-'.$pertanggal;
            $sql = 'SELECT DATE_FORMAT(:tanggal1,"%d/%m/%Y") as tanggalawal, DATE_FORMAT(DATE_ADD(:tanggal2, INTERVAL 1 MONTH),"%d/%m/%Y") as tanggalakhir';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal1', $tgl);
            $stmt->bindValue(':tanggal2', $tgl);
            $stmt->execute();
            $tanggal = $stmt->fetch(PDO::FETCH_OBJ);
        }
        Utils::insertLogUser('akses menu payroll posting generate');
        return view('datainduk/payroll/payrollposting/generate', ['listyymm' => $listyymm, 'dataatribut' => $dataatribut, 'tanggal' => $tanggal, 'menu' => 'payrollposting']);
    }

    public function getPayrollPerPegawai($pdo, $counter, $idpegawai, $periode, $tanggalawal, $tanggalakhir, $komponen_master) {

        $COUNTER = $counter;

        $errorscript = '';

        $tahunawal = Utils::getYearFromDate($tanggalawal);
        $tahunakhir = Utils::getYearFromDate($tanggalakhir);

        $tanggalawal_str = strtotime($tanggalawal);
        $tanggalakhir_str = strtotime($tanggalakhir);
        $jumlahhari_diff = $tanggalakhir_str - $tanggalawal_str;
        $selisihhari = round($jumlahhari_diff / (60 * 60 * 24)) + 1;

        // cek jumlah cuti
        if($tahunawal != $tahunakhir){
            $cutitahunawal = Utils::getJatahCuti($tahunawal,$idpegawai);
            $cutitahunakhir = Utils::getJatahCuti($tahunakhir,$idpegawai);
            $cuti = $cutitahunawal + $cutitahunakhir;
        }else{
            $cuti = Utils::getJatahCuti($tahunakhir,$idpegawai);
        }

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
                    '.$cuti.' as lamacuti,
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

            $JAMKERJA = array();
            $JADWALSHIFT = array();
            $ijs = 0;
            for($i = 0;$i<$selisihhari;$i++){
                $tgl = date('Y-m-d', strtotime(date('Y-m-d',$tanggalawal_str) . ' +'.$i.' day'));

                // untuk $JAMKERJA
                $JAMKERJA[$i]['idjamkerja'] = Utils::getPegawaiJamKerja('id',$idpegawai,$tgl);
                $JAMKERJA[$i]['jamkerja'] =  Utils::getPegawaiJamKerja('nama',$idpegawai,$tgl);
                $JAMKERJA[$i]['jenisjamkerja'] =  Utils::getPegawaiJamKerja('jenis',$idpegawai,$tgl);

                // untuk $JADWALSHIFT
                $datajadwalshift = Utils::getPegawaiJadwalShift($idpegawai,$tgl);
                if(count($datajadwalshift) > 0){
                    for($j=0;$j<count($datajadwalshift);$j++){
                        $JADWALSHIFT[$ijs]['idjamkerjashift'] = $datajadwalshift[$j]['idjamkerjashift'];
                        $JADWALSHIFT[$ijs]['namashift'] = $datajadwalshift[$j]['idjamkerjashift'];
                        $JADWALSHIFT[$ijs]['kode'] = $datajadwalshift[$j]['idjamkerjashift'];
                        $ijs++;
                    }
                }
            }

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
                        if($komponen_master[$i]['tipedata'] != 'teks'){
                            $PAYROLL[$komponen_master[$i]['kode']] = $row[0]['nominal'];
                        }else{
                            $PAYROLL[$komponen_master[$i]['kode']] = $row[0]['keterangan'];
                        }
                    } else {
                        if($komponen_master[$i]['tipedata'] != 'teks'){
                            $PAYROLL[$komponen_master[$i]['kode']] = 0;
                        }else{
                            $PAYROLL[$komponen_master[$i]['kode']] = '';
                        }
                    }
                } else {
                    if($komponen_master[$i]['tipedata'] != 'teks'){
                        $PAYROLL[$komponen_master[$i]['kode']] = 0;
                    }else{
                        $PAYROLL[$komponen_master[$i]['kode']] = '';
                    }
                }
            }

            $sedang_memproses = 'inisialisasi';
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
                    $temp_formula = PHP_EOL.'$formula_'.$i.' = function($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $REKAPABSEN, $PAYROLL, $JAMKERJA, $JADWALSHIFT){'.PHP_EOL.$temp_formula. '  return $result;'.PHP_EOL.'};'.PHP_EOL;
                    $script = $script . $temp_formula;
                }
            }
            $script = $script . PHP_EOL;
            // panggil temporary function_i
            for($i=0;$i<count($komponen_master);$i++) {
                $kode = strtolower($komponen_master[$i]['kode']);
                if ($komponen_master[$i]['carainput']=='formula' && $komponen_master[$i]['formula']!='') {
                    $script = $script.' $sedang_memproses = "'.$komponen_master[$i]['nama'].'"; ';
                    $script = $script.'$PAYROLL["'.$kode.'"] = $formula_'.$i.'($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $REKAPABSEN, $PAYROLL, $JAMKERJA, $JADWALSHIFT);'.PHP_EOL;
                }
            }
            //buang (unset) temporary function_i tersebut
            $script = $script.PHP_EOL;
            for($i=0;$i<count($komponen_master);$i++) {
                // $kode = strtolower($komponen_master[$i]['kode']);
                if ($komponen_master[$i]['carainput']=='formula' && $komponen_master[$i]['formula']!='') {
                    $script = $script.'unset($formula_'.$i.');'.PHP_EOL;
                }
            }
            $script = $script.PHP_EOL;
            $script = $script.'unset($get);'.PHP_EOL;
            $script = $script.'unset($get_counter);'.PHP_EOL;

            Utils::payroll_replace_variablescript($script);

            try {
                eval($script);
            } catch (\ParseError $e) {
                $errorscript = $sedang_memproses.' : '.$e->getMessage();
//                return $e->getMessage();
            } catch (\Exception $e) {
                $errorscript = $sedang_memproses.' : '.$e->getMessage();
//                return $e->getMessage();
            }

            $result = array();
            $result['idpegawai'] = $idpegawai;
            $result['nama'] = $nama;
            $result['total'] = 0;
            $result['komponen'] = array();
            $result['errorscript'] = $errorscript;

            for ($i=0;$i<count($komponen_master);$i++) {
                if ($komponen_master[$i]['tipedata']!='teks') {
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

    public function generateSubmit(Request $request) {
	    if(Utils::cekDateTime($request->tanggalawal) && Utils::cekDateTime($request->tanggalakhir)) {
            Utils::payroll_init_eval();

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $periode = $request->periode;
            $msg = '';
            $idpayrollkelompok = Session::get('postingdata_idkelompok');

            //buat dulu tanggalawal dan tanggalakhir
//        $sql = 'CALL payroll_getperiode(:periode, @tanggalawal, @tanggalakhir)';
//        $stmt = $pdo->prepare($sql);
//        $stmt->bindValue(':periode',$periode);
//        $stmt->execute();

//        $sql = 'SELECT @tanggalawal as tanggalawal, @tanggalakhir as tanggalakhir';
//        $stmt = $pdo->prepare($sql);
//        $stmt->execute();
//        $pengaturan = $stmt->fetchAll(PDO::FETCH_ASSOC);
//        $tanggalawal = $pengaturan[0]['tanggalawal'];
//        $tanggalakhir = $pengaturan[0]['tanggalakhir'];
            $tanggalawal = Utils::convertDmy2Ymd($request->tanggalawal);
            $tanggalakhir = Utils::convertDmy2Ymd($request->tanggalakhir);

            $where = '';
            // filter atribut
            if (isset($request->atributnilai)) {
                $atributs = $request->atributnilai;
                $atributnilai = Utils::atributNilai($atributs);
            } else {
                // jika tidak ada yang dipilih, maka where nya berdasarkan kelompok payroll nya
                $atributnilai = Utils::getDataWhere($pdo, 'payroll_kelompok_atribut', 'group_concat(idatributnilai)', 'idpayroll_kelompok', $idpayrollkelompok);
            }
            if ($atributnilai != '') {
                $where .= ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (:idatributnilai) )';
            }
            //ambil data pegawai
            $sql = 'SELECT
                    id
                FROM
                    pegawai
                WHERE
                    del = "t" AND
                    `status` = "a"
                    ' . $where . '
                ORDER BY
                    nama';
            $stmt = $pdo->prepare($sql);
            if ($atributnilai != '') {
                $stmt->bindValue(':idatributnilai', $atributnilai);
            }
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
                    pkm.urutan_perhitungan,
                    pkm.urutan_tampilan
                FROM
                    payroll_komponen_master pkm
                    LEFT JOIN payroll_komponen_master_group pkmg ON pkmg.id = pkm.idpayroll_komponen_master_group
                    LEFT JOIN payroll_pengaturan pp ON pp.komponenmaster_total = pkm.id
                WHERE
                    pkm.digunakan="y" AND
                    pkm.idpayroll_kelompok = :idpayrollkelompok
                ORDER BY
                    pkm.urutan_perhitungan ASC, pkm.nama ASC, pkm.id ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpayrollkelompok', $idpayrollkelompok);
            $stmt->execute();
            $komponen_master = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = array();
            for ($i = 0; $i < count($pegawai); $i++) {
                $result[$i] = $this->getPayrollPerPegawai($pdo, $i + 1, $pegawai[$i]['id'], $periode, $tanggalawal, $tanggalakhir, $komponen_master);

//             echo '<xmp>';
//             print_r($result[$i]);
//             echo '</xmp>';
                if ($result[$i]['errorscript'] != '') {
                    $msg = $result[$i]['errorscript'];
                    break;
                }
            }
//        return $result;
//        return $result[8]['komponen'];
//        $tes_total = '';
//        for ($i = 0; $i < count($result); $i++) {
//            for ($j = 0; $j < count($komponen_master); $j++) {
//                $tes_total .= '|'.$result[$i]['komponen'][$j]['result_keterangan'];
//            }
//        }
//        return $tes_total;

//        return;
            if ($msg == '') {
                try {
                    $pdo->beginTransaction();

                    $total = 0;

                    // tambahkan ke table payroll_posting
                    $sql = 'INSERT INTO payroll_posting VALUES(NULL, "", :periode, STR_TO_DATE(:tanggalawal,"%d/%m/%Y"), STR_TO_DATE(:tanggalakhir,"%d/%m/%Y"), 0, NOW(), NULL)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':periode', $periode);
                    $stmt->bindValue(':tanggalawal', $request->tanggalawal);
                    $stmt->bindValue(':tanggalakhir', $request->tanggalakhir);
                    $stmt->execute();
                    $payroll_posting_id = $pdo->lastInsertId();

                    // insert ke tabel payroll_posting_atribut
                    if (isset($request->atributnilai)) {
                        for ($i = 0; $i < count($request->atributnilai); $i++) {
                            $sql = 'INSERT INTO payroll_posting_atribut VALUES(NULL,:payroll_posting_id,:idatributnilai)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':payroll_posting_id', $payroll_posting_id);
                            $stmt->bindValue(':idatributnilai', $request->atributnilai[$i]);
                            $stmt->execute();
                        }
                    }

                    // tambahkan ke table payroll_posting_komponen
                    for ($i = 0; $i < count($komponen_master); $i++) {
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
                        $stmt->bindValue(':komponenmaster_urutan', $komponen_master[$i]['urutan_tampilan']);
                        $stmt->execute();

                        $komponen_master[$i]['idpayroll_posting_komponen'] = $pdo->lastInsertId();
                    }

                    for ($i = 0; $i < count($result); $i++) {
                        $total = $total + $result[$i]['total'];
                        for ($j = 0; $j < count($komponen_master); $j++) {
                            $sql1 = 'INSERT INTO payroll_posting_pegawai VALUES(
                                NULL, 
                                :payroll_posting_id, 
                                :idpegawai, 
                                :idpayroll_posting_komponen, 
                                :nama,
                                :result_nominal, 
                                IFNULL(:result_keterangan,""), 
                                ""
                            )';
                            $stmt1 = $pdo->prepare($sql1);
                            $stmt1->bindValue(':payroll_posting_id', $payroll_posting_id);
                            $stmt1->bindValue(':idpegawai', $result[$i]['idpegawai']);
                            $stmt1->bindValue(':idpayroll_posting_komponen', $komponen_master[$j]['idpayroll_posting_komponen']);
                            $stmt1->bindValue(':nama', $result[$i]['nama']);
                            $stmt1->bindValue(':result_nominal', $result[$i]['komponen'][$j]['result_nominal']);
                            $stmt1->bindValue(':result_keterangan', $result[$i]['komponen'][$j]['result_keterangan']);
                            $stmt1->execute();
                        }
                    }

                    //update total payroll
                    $sql = 'UPDATE payroll_posting SET total=:total WHERE id=:payroll_posting_id LIMIT 1';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':payroll_posting_id', $payroll_posting_id);
                    $stmt->bindValue(':total', $total);
                    $stmt->execute();

                    Utils::insertLogUser('akses menu generate payroll posting');

                    $pdo->commit();
                    $msg = trans('all.generatesukses');
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                    $pdo->rollBack();
                }
            }
        }else{
	        $msg = trans('all.terjadigangguan');
        }
        return redirect('datainduk/payroll/payrollposting')->with('message', $msg);
    }

    public function delete($id){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'payroll_posting','periode','id',$id);
        if($cekadadata != ''){
            Utils::deleteData($pdo,'payroll_posting',$id);
            Utils::insertLogUser('hapus payroll posting');
            $msg = trans('all.databerhasildihapus');
        }else{
            $msg = trans('all.datatidakditemukan');
        }
        return redirect('datainduk/payroll/payrollposting')->with('message', $msg);
    }

    public function excel($id){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $r = 1;
        $lf = 0; // letak footer
        $periode = Utils::getDataWhere($pdo,'payroll_posting','periode','id',$id);
        $tanggalawal = Utils::getDataWhere($pdo,'payroll_posting','tanggalawal','id',$id);
        $tanggalakhir = Utils::getDataWhere($pdo,'payroll_posting','tanggalakhir','id',$id);
        $periode = Utils::periodeCantik($periode).' ('.Utils::tanggalCantikDariSampai($tanggalawal,$tanggalakhir).')';
        $idpayrollkelompok = Session::get('postingdata_idkelompok');
        $sql = 'SELECT template_payrollposting FROM payroll_kelompok WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $idpayrollkelompok);
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            $rowpengaturan = $stmt->fetch(PDO::FETCH_ASSOC);
            $path = Session::get('folderroot_perusahaan') . '/payroll/';
            $fileheader = $rowpengaturan['template_payrollposting'];
            if ($fileheader != '' && file_exists($path . $fileheader)) {
                $objPHPExcel = PHPExcel_IOFactory::load($path.$fileheader);
                $objWorksheet = clone $objPHPExcel->getActiveSheet();
                $b = 1;
                // mencari start mulai nya data setelah header
                while($b < 1000) {
                    $k=1;
                    $found = false;
                    while($k < 1000){
                        $hht = Utils::angkaToHuruf($k); // hht = huruf header template
                        if ($objWorksheet->getCell($hht . $b)->getValue() == '{periode}') {
                            $objPHPExcel->getActiveSheet()->setCellValue($hht . $b, $periode);
                        }

                        if ($objWorksheet->getCell($hht . $b)->getValue() == '{isi}') {
                            $r = $b;
                            $lf = $b+2;
                            $found=true;
                            break;
                        }
                        $k++;
                    }
                    if($found){
                        break;
                    }
                    $b++;
                }
            }
        }

        if($r == 1){
            $objPHPExcel = new PHPExcel();
        }

        //set css kolom
        $styleArray = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ),
            ),
        );

        Utils::setPropertiesExcel($objPHPExcel,'Payroll Posting');

        //header kolom
        $sql = 'SELECT ppk.* FROM payroll_posting_komponen ppk, payroll_komponen_master pkm WHERE ppk.komponenmaster_id=pkm.id AND pkm.digunakan = "y" AND pkm.tampilkan = "y" AND ppk.idpayroll_posting = :idpayroll_posting AND pkm.idpayroll_kelompok = '.$idpayrollkelompok.' ORDER BY ppk.komponenmaster_urutan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpayroll_posting', $id);
        $stmt->execute();
        $totalkolom = $stmt->rowCount();
        $arrkmg = Utils::getArrayTotalKomponenMasterGroup($id);
        if($totalkolom > 0){
            $rowKomponen = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $komponenmaster_group_old = null;
            for($i=0;$i<count($rowKomponen);$i++){
                $hh = Utils::angkaToHuruf($i+1);
                if($rowKomponen[$i]['komponenmaster_group'] != ''){
                    if($rowKomponen[$i]['komponenmaster_group']!=$komponenmaster_group_old) {
                        if(count($arrkmg) > 0) {
                            $totarrkmg = $arrkmg[$rowKomponen[$i]['komponenmaster_group']];
                            $hh_merge = Utils::angkaToHuruf($i + $totarrkmg);
                            $objPHPExcel->getActiveSheet()->mergeCells($hh . $r . ':' . $hh_merge . $r);

                            $objPHPExcel->getActiveSheet()->setCellValue($hh . $r, $rowKomponen[$i]['komponenmaster_group']);
                        }
                    }
                    $komponenmaster_group_old = $rowKomponen[$i]['komponenmaster_group'];

                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                    $objPHPExcel->getActiveSheet()->setCellValue($hh . ($r+1), $rowKomponen[$i]['komponenmaster_nama']);
                    //lebar kolom
                    $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                    //set bold
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->getFont()->setBold(true);
                    //style
                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->applyFromArray($styleArray);
                    //center
                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                }else{
                    $komponenmaster_group_old = null;
                    $objPHPExcel->getActiveSheet()->setCellValue($hh . $r, $rowKomponen[$i]['komponenmaster_nama']);
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
                }
            }
        }

        //cek apakah ada filter atribut nilai pada saat generate
        $sql = 'SELECT IFNULL(GROUP_CONCAT(idatributnilai),"") as idatributnilai FROM payroll_posting_atribut WHERE idpayroll_posting = :idpayroll_posting';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpayroll_posting', $id);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $rowppa = $stmt->fetch(PDO::FETCH_ASSOC);
            if($rowppa['idatributnilai'] != '') {
                $atributnilai = $rowppa['idatributnilai'];
            }else{
                // jika tidak ada yang dipilih, maka where nya berdasarkan kelompok payroll nya
                $atributnilai = Utils::getDataWhere($pdo,'payroll_kelompok_atribut','group_concat(idatributnilai)','idpayroll_kelompok',$idpayrollkelompok);
            }
        }else{
            // jika tidak ada yang dipilih, maka where nya berdasarkan kelompok payroll nya
            $atributnilai = Utils::getDataWhere($pdo,'payroll_kelompok_atribut','group_concat(idatributnilai)','idpayroll_kelompok',$idpayrollkelompok);
        }

        $where = '';
        if($atributnilai != ''){
            $where .= ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . $atributnilai . ') )';
        }

        //isi
        $sql = 'SELECT
                    id as idpegawai
                FROM
                    pegawai
                WHERE
                    del = "t" AND
                    `status` = "a"
                    '.$where.'
                ORDER BY
                    nama';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $totaldata = $stmt->rowCount();
//        $objPHPExcel->getActiveSheet()->insertNewRowBefore($objPHPExcel->getActiveSheet()->getHighestRow(), $totaldata);
        if($lf > 1) {
            $objPHPExcel->getActiveSheet()->insertNewRowBefore($lf, $totaldata);
        }

        $i = $r+2;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sql1 = 'SELECT
                        ppk.komponenmaster_tipedata,
                        ppp.result_nominal,
                        ppp.result_keterangan
                    FROM
                        payroll_posting_pegawai ppp,
                        payroll_posting_komponen ppk,
                        payroll_komponen_master pkm
                    WHERE
                        ppp.idpayroll_posting_komponen = ppk.id AND
                        ppk.komponenmaster_id=pkm.id AND
                        pkm.digunakan = "y" AND
                        pkm.tampilkan = "y" AND
                        ppp.idpayroll_posting = :idpayroll_posting AND
                        ppp.idpegawai = :idpegawai AND
                        pkm.idpayroll_kelompok = :idpayroll_kelompok
                    ORDER BY
                        ppk.komponenmaster_urutan';
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->bindValue(':idpayroll_posting', $id);
            $stmt1->bindValue(':idpegawai', $row['idpegawai']);
            $stmt1->bindValue(':idpayroll_kelompok', $idpayrollkelompok);
            $stmt1->execute();
            $j = 1;
            while($row2 = $stmt1->fetch(PDO::FETCH_ASSOC)) {
                $hi = Utils::angkaToHuruf($j);
                if($row2['komponenmaster_tipedata'] == 'angka'){
                    $objPHPExcel->getActiveSheet()->setCellValue($hi . $i, $row2['result_nominal']);
                }elseif($row2['komponenmaster_tipedata'] == 'uang'){
                    $objPHPExcel->getActiveSheet()->setCellValue($hi . $i, $row2['result_nominal']);
                    $objPHPExcel->getActiveSheet()->getStyle($hi . $i)->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
//                    $objPHPExcel->getActiveSheet()->getStyle($hi . $i)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValue($hi . $i, $row2['result_keterangan'].' ');
                }

                $objPHPExcel->getActiveSheet()->getStyle($hi . $i)->applyFromArray($styleArray);

                $j++;
            }

            $i++;
        }

        Utils::passwordExcel($objPHPExcel);
        Utils::insertLogUser('Ekspor payroll');
        Utils::setFileNameExcel(trans('all.payroll'));
        $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $writer->save('php://output');
    }

//    public function excel($id){
//        $pdo = DB::connection('perusahaan_db')->getPdo();
//        $objPHPExcel = new PHPExcel();
//
//        //set css kolom
//        $styleArray = array(
//            'borders' => array(
//                'outline' => array(
//                    'style' => PHPExcel_Style_Border::BORDER_THIN,
//                    'color' => array('argb' => '000000'),
//                ),
//            ),
//        );
//
//        Utils::setPropertiesExcel($objPHPExcel,'Payroll Posting');
//
//        $r = 8; // karena ada header
//
//        //header kolom
//        $sql = 'SELECT ppk.* FROM payroll_posting_komponen ppk, payroll_komponen_master pkm WHERE ppk.komponenmaster_id=pkm.id AND pkm.digunakan = "y" AND pkm.tampilkan = "y" AND ppk.idpayroll_posting = :idpayroll_posting ORDER BY ppk.komponenmaster_urutan';
//        $stmt = $pdo->prepare($sql);
//        $stmt->bindValue(':idpayroll_posting', $id);
//        $stmt->execute();
//        $totalkolom = $stmt->rowCount();
//        if($totalkolom > 0){
//            $rowKomponen = $stmt->fetchAll(PDO::FETCH_ASSOC);
//            $idpayrollkomponenmastergroup_old = '';
//            for($i=0;$i<count($rowKomponen);$i++){
//                $hh = Utils::angkaToHuruf($i+1);
//
//                if($rowKomponen[$i]['komponenmaster_group'] != ''){
//                    if($idpayrollkomponenmastergroup_old == $rowKomponen[$i]['komponenmaster_group']){
//                        $hin = $i;
//                        $hhn = Utils::angkaToHuruf($hin);
//                        //merge
//                        $objPHPExcel->getActiveSheet()->mergeCells($hhn.$r.':'.$hh.$r);
//                    }
//
//                    $objPHPExcel->getActiveSheet()->setCellValue($hh . $r, $rowKomponen[$i]['komponenmaster_group']);
//                    $objPHPExcel->getActiveSheet()->setCellValue($hh . ($r+1), $rowKomponen[$i]['komponenmaster_nama']);
//                    //lebar kolom
//                    $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
//                    //set bold
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->getFont()->setBold(true);
//                    //style
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->applyFromArray($styleArray);
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->applyFromArray($styleArray);
//                    //center
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
//
//                    $idpayrollkomponenmastergroup_old = $rowKomponen[$i]['komponenmaster_group'];
//                }else{
//                    $objPHPExcel->getActiveSheet()->setCellValue($hh . $r, $rowKomponen[$i]['komponenmaster_nama']);
//                    //lebar kolom
//                    $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
//                    //set bold
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getFont()->setBold(true);
//                    //style
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->applyFromArray($styleArray);
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . ($r+1))->applyFromArray($styleArray);
//                    //merge
//                    $objPHPExcel->getActiveSheet()->mergeCells($hh.$r.':'.$hh.($r+1));
//                    //center
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//                    $objPHPExcel->getActiveSheet()->getStyle($hh . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
//                }
//            }
//        }
//
//        // pengaturan payroll untuk header payroll
//        $sql = 'SELECT * FROM payroll_pengaturan';
//        $stmt = $pdo->prepare($sql);
//        $stmt->execute();
//        $rowPengaturan = $stmt->fetch(PDO::FETCH_ASSOC);
//        $_ha = Utils::angkaToHuruf($totalkolom);
//        $objPHPExcel->getActiveSheet()->setCellValue('A3', strtoupper($rowPengaturan['header1']));
//        $objPHPExcel->getActiveSheet()->setCellValue('A4', strtoupper($rowPengaturan['header2']));
//        if($rowPengaturan['header3'] != ''){
//            $objPHPExcel->getActiveSheet()->setCellValue('A5', strtoupper($rowPengaturan['header3']));
//        }
//        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true); // yg membuat kolom A kecil
//        $objPHPExcel->getActiveSheet()->getStyle('A1:A6')->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A1:A6')->getFont()->setBold(true);
//        //merge cell
//        for($_i = 1;$_i<=6;$_i++){
//            $objPHPExcel->getActiveSheet()->mergeCells('A' . $_i . ':' . $_ha . $_i);
//            $objPHPExcel->getActiveSheet()->getStyle('A' . $_i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        }
//
//        //cek apakah ada filter atribut nilai pada saat generate
//        $atributnilai = '';
//        $sql = 'SELECT GROUP_CONCAT(idatributnilai) as idatributnilai FROM payroll_posting_atribut WHERE idpayroll_posting = :idpayroll_posting';
//        $stmt = $pdo->prepare($sql);
//        $stmt->bindValue(':idpayroll_posting', $id);
//        $stmt->execute();
//        if($stmt->rowCount() > 0){
//            $rowppa = $stmt->fetch(PDO::FETCH_ASSOC);
//            $atributnilai = $rowppa['idatributnilai'];
//        }
//
//        $where = '';
//        if($atributnilai != ''){
//            $where .= ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . $atributnilai . ') )';
//        }
//
//        //isi
//        $sql = 'SELECT
//                    id as idpegawai
//                FROM
//                    pegawai
//                WHERE
//                    del = "t" AND
//                    `status` = "a"
//                    '.$where.'
//                ORDER BY
//                    nama';
//        $stmt = $pdo->prepare($sql);
//        $stmt->execute();
//        $i = $r+2;
//        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//            $sql1 = 'SELECT
//                        ppk.komponenmaster_tipedata,
//                        ppp.result_nominal,
//                        ppp.result_keterangan
//                    FROM
//                        payroll_posting_pegawai ppp,
//                        payroll_posting_komponen ppk,
//                        payroll_komponen_master pkm
//                    WHERE
//                        ppp.idpayroll_posting_komponen = ppk.id AND
//                        ppk.komponenmaster_id=pkm.id AND
//                        pkm.digunakan = "y" AND
//                        pkm.tampilkan = "y" AND
//                        ppp.idpayroll_posting = :idpayroll_posting AND
//                        ppp.idpegawai = :idpegawai';
//            $stmt1 = $pdo->prepare($sql1);
//            $stmt1->bindValue(':idpayroll_posting', $id);
//            $stmt1->bindValue(':idpegawai', $row['idpegawai']);
//            $stmt1->execute();
//            $j = 1;
//            while($row2 = $stmt1->fetch(PDO::FETCH_ASSOC)) {
//                $hi = Utils::angkaToHuruf($j);
//                if($row2['komponenmaster_tipedata'] == 'angka'){
//                    $objPHPExcel->getActiveSheet()->setCellValue($hi . $i, $row2['result_nominal']);
//                }elseif($row2['komponenmaster_tipedata'] == 'uang'){
//                    $objPHPExcel->getActiveSheet()->setCellValue($hi . $i, $row2['result_nominal']);
//                    $objPHPExcel->getActiveSheet()->getStyle($hi . $i)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
//                }else{
//                    $objPHPExcel->getActiveSheet()->setCellValue($hi . $i, $row2['result_keterangan'].' ');
//                }
//
//                $objPHPExcel->getActiveSheet()->getStyle($hi . $i)->applyFromArray($styleArray);
//
//                $j++;
//            }
//
//            $i++;
//        }
//
//        //footer
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+3), $rowPengaturan['footer1']);
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+4), $rowPengaturan['footer2']);
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+5), $rowPengaturan['footer3']);
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+6), $rowPengaturan['footer4']);
//        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+12), $rowPengaturan['ttd']);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i+3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i+6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i+12))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($i+3) . ':C' . ($i+3));
//        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($i+4) . ':C' . ($i+4));
//        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($i+5) . ':C' . ($i+5));
//        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($i+6) . ':C' . ($i+6));
//        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($i+12) . ':C' . ($i+12));
//
//        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A'.($i+3).':A'.($i+12))->getFont()->setSize(16);
//        $objPHPExcel->getActiveSheet()->getStyle('A'.($i+3).':A'.($i+12))->getFont()->setBold(true);
//
//        Utils::passwordExcel($objPHPExcel);
//        Utils::insertLogUser('Ekspor payroll');
//        header('Content-Type: application/vnd.ms-excel');
//        header('Content-Disposition: attachment;filename="' . time() . '_' . trans('all.payroll') . '.xlsx"');
//        header('Cache-Control: max-age=0');
//        header('Cache-Control: max-age=1');
//
//        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
//        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
//        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
//        header('Pragma: public'); // HTTP/1.0
//
//        $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
//        $writer->save('php://output');
//    }

    public function pdf($id){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        require(public_path() .'/fpdf/fpdf.php');
        Utils::insertLogUser('Ekspor payroll pdf');

        $pdf = new FPDF();
        $pdf->AddPage('L');

        $pdf->SetFont('Arial','B',6);

        //Background color of header//
        $pdf->SetFillColor(193,229,252);

        //Cell(width , height , text , border , endline , [align])
        
        // pengaturan payroll untuk header payroll
        $sql = 'SELECT * FROM payroll_pengaturan';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rowPengaturan = $stmt->fetch(PDO::FETCH_ASSOC);
        if($rowPengaturan['header1'] != ''){
            $pdf->Cell($pdf->GetPageWidth(),3,strtoupper($rowPengaturan['header1']),0,0,'C');
            $pdf->Ln();
        }
        if($rowPengaturan['header2'] != ''){
            $pdf->Cell($pdf->GetPageWidth(),3,strtoupper($rowPengaturan['header2']),0,0,'C');
            $pdf->Ln();
        }
        if($rowPengaturan['header3'] != ''){
            $pdf->Cell($pdf->GetPageWidth(),3,strtoupper($rowPengaturan['header3']),0,0,'C');
            $pdf->Ln();
        }
        $pdf->Ln(4);

        //header
        $sql = 'SELECT * FROM payroll_posting_komponen WHERE idpayroll_posting = :idpayroll_posting ORDER BY komponenmaster_urutan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpayroll_posting', $id);
        $stmt->execute();
        $totalkolom = $stmt->rowCount();
        if($totalkolom > 0){
            $rowKomponen = $stmt->fetchAll(PDO::FETCH_ASSOC);
            for($i=0;$i<count($rowKomponen);$i++){
                $pdf->Cell(20,3,strtoupper($rowKomponen[$i]['komponenmaster_nama']),1,0,'C');
            }
        }

        //cek apakah ada filter atribut nilai pada saat generate
        $atributnilai = '';
        $sql = 'SELECT GROUP_CONCAT(idatributnilai) as idatributnilai FROM payroll_posting_atribut WHERE idpayroll_posting = :idpayroll_posting';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpayroll_posting', $id);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $rowppa = $stmt->fetch(PDO::FETCH_ASSOC);
            $atributnilai = $rowppa['idatributnilai'];
        }

        $where = '';
        if($atributnilai != ''){
            $where .= ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . $atributnilai . ') )';
        }

        //isi
        $sql = 'SELECT
                    id as idpegawai
                FROM
                    pegawai
                WHERE
                    del = "t" AND
                    `status` = "a"
                    '.$where.'
                ORDER BY
                    nama';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sql1 = 'SELECT
                        ppk.komponenmaster_tipedata,
                        ppp.result_nominal,
                        ppp.result_keterangan
                    FROM
                        payroll_posting_pegawai ppp,
                        payroll_posting_komponen ppk
                    WHERE
                        ppp.idpayroll_posting_komponen = ppk.id AND
                        ppp.idpayroll_posting = :idpayroll_posting AND
                        ppp.idpegawai = :idpegawai';
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->bindValue(':idpayroll_posting', $id);
            $stmt1->bindValue(':idpegawai', $row['idpegawai']);
            $stmt1->execute();
            while($row2 = $stmt1->fetch(PDO::FETCH_ASSOC)) {
                if($row2['komponenmaster_tipedata'] == 'angka'){
                    $pdf->Cell(20,3,$row2['result_nominal'],1);
                }elseif($row2['komponenmaster_tipedata'] == 'uang'){
                    $pdf->Cell(20,3,number_format($row2['result_nominal'],0,',','.'),1);
                }else{
                    $pdf->Cell(20,3,$row2['result_keterangan'],1);
                }
            }
            $pdf->Ln();
        }

        $pdf->Output('D', time() . '_' . trans('all.payroll') . '.pdf');
    }
}