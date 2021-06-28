<?php
namespace App\Http\Controllers;

use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use Redirect;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Border;

class PayrollKomponenInputManualController extends Controller
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
	    if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('payrollkomponeninputmanual','lm')){
            $bulan = date('m');
            $tahun = date('y');
            $keterangan = '';

            if(Session::has('payrollkomponeninputmanual_bulan')){
                $bulan = Session::get('payrollkomponeninputmanual_bulan');
            }
            if(Session::has('payrollkomponeninputmanual_tahun')){
                $tahun = Session::get('payrollkomponeninputmanual_tahun');
            }

            $atributs = Utils::getAtribut();
            $tahundropdown = Utils::tahunDropdown();
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $datapayrollkomponenmaster = Utils::getData($pdo,'payroll_komponen_master','id,nama','carainput="inputmanual"','urutan_tampilan ASC, nama ASC');
            Utils::insertLogUser('akses menu payroll komponen input manual');
            return view('datainduk/payroll/payrollkomponeninputmanual/index', ['tahundropdown' => $tahundropdown, 'atributs' => $atributs, 'datapayrollkomponenmaster' => $datapayrollkomponenmaster, "bulansekarang" => $bulan, "tahunsekarang" => $tahun, 'keterangan' => $keterangan, 'menu' => 'payrollkomponeninputmanual']);
        }else{
            return redirect('/');
        }
    }
    
    public function nextStep(Request $request)
    {
        $bulan = $request->bulan;
        $tahun =$request->tahun;
        $atributnilai = $request->atributnilai;
        if(!is_array($atributnilai)){
            $atributnilai = $request->atributnilai != '' ? explode("|", $request->atributnilai) : '';
        }

        Session::set('payrollkomponeninputmanual_bulan', $bulan);
        Session::set('payrollkomponeninputmanual_tahun', $tahun);
        Session::set('payrollkomponeninputmanual_atribut', $atributnilai);

        if($bulan == ''){
            Session::forget('payrollkomponeninputmanual_bulan');
        }

        if($tahun == ''){
            Session::forget('payrollkomponeninputmanual_tahun');
        }

        if($atributnilai == ''){
            Session::forget('payrollkomponeninputmanual_atribut');
        }

        $atributs = Session::get('payrollkomponeninputmanual_atribut');
        return $this->dataNextStep($bulan,$tahun,$atributs);
    }

    public function nextStepGet(){
        if(Session::has('payrollkomponeninputmanual_bulan') && Session::has('payrollkomponeninputmanual_tahun') && Session::has('payrollkomponeninputmanual_payrollkomponenmaster')){
            $bulan = Session::get('payrollkomponeninputmanual_bulan');
            $tahun = Session::get('payrollkomponeninputmanual_tahun');
            $atributs = Session::get('payrollkomponeninputmanual_atribut');
            // if(!is_array($atributs)){
            //     $atributs = $atributs != '' ? explode("|", $atributs) : '';
            // }
            return $this->dataNextStep($bulan,$tahun,$atributs);
        }
        return '';
    }

    public function dataNextStep($bulan,$tahun,$atributs){
        $atributnilai = Utils::atributNilai($atributs);
        $atributnilaiketerangan = '';
        if($atributnilai != ''){
            $atributnilaidipilih = Utils::getAtributSelected($atributnilai);
            $atributnilaiketerangan = Utils::atributNilaiKeterangan($atributnilaidipilih);
        }

        $keterangan = trans('all.periode').' : '.Utils::getBulan($bulan).' 20'.$tahun.' '.$atributnilaiketerangan;

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT
                    pkm.id,
                    pkm.nama,
                    pkm.kode,
                    IF(ISNULL(pkim.id)=true,"'.trans('all.tidak').'","'.trans('all.ya').'") as adadata
                FROM
                    payroll_komponen_master pkm
                    LEFT JOIN payroll_komponen_inputmanual pkim ON pkim.idpayroll_komponen_master=pkm.id AND pkim.periode = :periode
                WHERE
                    pkm.carainput = "inputmanual" AND
                    pkm.digunakan="y"
                GROUP BY
                    pkm.id
                ORDER BY
                    pkm.urutan_tampilan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':periode', $tahun.$bulan);
        $stmt->execute();
        $datapayrollkomponenmaster = '';
        if($stmt->rowCount() > 0){
            $datapayrollkomponenmaster = $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        return view('datainduk/payroll/payrollkomponeninputmanual/nextstep', ['datapayrollkomponenmaster' => $datapayrollkomponenmaster, 'keterangan' => $keterangan, 'menu' => 'payrollkomponeninputmanual']);
    }

    public function data($idpayrollkomponenmaster)
    {
        $bulan = Session::get('payrollkomponeninputmanual_bulan');
        $tahun = Session::get('payrollkomponeninputmanual_tahun');
        $atributnilai = Session::get('payrollkomponeninputmanual_atribut');
        Session::set('payrollkomponeninputmanual_payrollkomponenmaster', $idpayrollkomponenmaster);

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $data = '';
        $tipedata = '';
        if(Session::has('payrollkomponeninputmanual_bulan') && Session::has('payrollkomponeninputmanual_tahun') && Session::has('payrollkomponeninputmanual_payrollkomponenmaster')){
            $where = '';
            if(Session::has('payrollkomponeninputmanual_atribut')){
                $atributwhere = Session::get('payrollkomponeninputmanual_atribut');
                $atributnilaiwhere = Utils::atributNilai($atributwhere);
                $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilaiwhere.') )';
            }

            $sql = 'SELECT tipedata,inputmanual_filter FROM payroll_komponen_master WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $idpayrollkomponenmaster);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $tipedata = $row[0]['tipedata'];
                $inputmanual_filter = $row[0]['inputmanual_filter'];

                $sql = 'SELECT
                            p.id,
                            p.pin,
                            p.nama,
                            p.idagama,
                            IFNULL(a.agama, "") as agama,
                            p.pin,
                            p.pemindai,
                            p.nomorhp,
                            p.flexytime,
                            p.status,
                            p.tanggalaktif,
                            CONCAT("<span title=\"",p.nama,"\" class=\"detailpegawai\" onclick=\"detailpegawai(,p.id,)\" style=\"cursor:pointer;\">",p.nama,"</span>") as pegawai,
                            getatributpegawai_all(p.id) as atribut,
                            lower(payroll_getatributnilai(p.id)) as payroll_atributnilai,
                            lower(payroll_getatributvariable(p.id)) as payroll_atributvariable,
                            IFNULL(prki.nominal,0) as nominal,
                            prki.keterangan
                        FROM
                            pegawai p
                            LEFT JOIN payroll_komponen_inputmanual prki ON prki.idpegawai=p.id AND prki.periode = "'.$tahun.$bulan.'" AND prki.idpayroll_komponen_master = '.$idpayrollkomponenmaster.'
                            LEFT JOIN pegawaiatribut pa ON pa.idpegawai=p.id
                            LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
                            LEFT JOIN atribut atr ON an.idatribut=atr.id
                            LEFT JOIN agama a ON p.idagama=a.id
                        WHERE
                            p.del = "t"
                        '.$where.'
                        GROUP BY
                            p.id
                        ORDER BY
                            p.nama';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

                Utils::payroll_init_eval();

                $data = array();
                for ($i=0; $i<count($row); $i++) {
                    Utils::payroll_fetch_pegawai($PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $row[$i]);

                    $___batas_bawah = 0;
                    $___batas_atas = 9999999999;

                    $filter = $inputmanual_filter;
                    if($filter != '' && $tipedata == 'angka'){
                        Utils::payroll_replace_variablescript($filter);

                        eval($filter);
                    }

                    $data[$i]['id'] = $row[$i]['id'];
                    $data[$i]['pin'] = $row[$i]['pin'];
                    $data[$i]['nama'] = $row[$i]['nama'];
                    $data[$i]['pegawai'] = $row[$i]['pegawai'];
                    $data[$i]['atribut'] = $row[$i]['atribut'];
                    $data[$i]['batas_bawah'] = $___batas_bawah;
                    $data[$i]['batas_atas'] = $___batas_atas;
                    $data[$i]['nominal'] = $row[$i]['nominal'];
                    $data[$i]['keterangan'] = $row[$i]['keterangan'];
                }
            }
        }

        $atributs = Session::get('payrollkomponeninputmanual_atribut');
        $atributnilai = Utils::atributNilai($atributs);
        $atributnilaiketerangan = '';
        if($atributnilai != ''){
            $atributnilaidipilih = Utils::getAtributSelected($atributnilai);
            $atributnilaiketerangan = Utils::atributNilaiKeterangan($atributnilaidipilih);
        }

        if($bulan[0] == 0){
            $bulan = str_replace('0', '',$bulan);
        }
        $keterangan = trans('all.periode').' : '.Utils::getBulan($bulan).' 20'.$tahun.' '.trans('all.payrollkomponenmaster').' : '.Utils::getDataWhere($pdo,'payroll_komponen_master','nama','id',$idpayrollkomponenmaster).' '.$atributnilaiketerangan;
        
        // keperluan modal set semua nominal / keterangan
        $periode = $tahun.str_pad($bulan,2,"0",STR_PAD_LEFT);
        $tanggalperiode = '20'.substr($periode, 0, 2).'-'.substr($periode, -2).'-01';
        $tanggalakhir = date("Y-m-t", strtotime($tanggalperiode));
        $selisihbulan = Utils::selisihBulan(date('Y-m-d'),$tanggalakhir)+1;
        $listyymm = Utils::list_yymm(-$selisihbulan);

        return view('datainduk/payroll/payrollkomponeninputmanual/data', ['data' => $data, 'tipedata' => $tipedata, 'listyymm' => $listyymm, 'keterangan' => $keterangan, 'menu' => 'payrollkomponeninputmanual']);
    }

    public function dataHapus($idpayrollkomponenmaster){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $bulan = Session::get('payrollkomponeninputmanual_bulan');
        $tahun = Session::get('payrollkomponeninputmanual_tahun');
        
        $cekadadata = Utils::getDataWhere($pdo,'payroll_komponen_master','nama','id',$idpayrollkomponenmaster);
        if($cekadadata != ''){
            //hapus
            Utils::deleteData($pdo,'payroll_komponen_inputmanual',$idpayrollkomponenmaster,'idpayroll_komponen_master');
            Utils::insertLogUser('Hapus payroll komponen input manual periode ' . Utils::getBulan($bulan).' 20'.Session::get('payrollkomponeninputmanual_tahun'));
            return redirect('datainduk/payroll/payrollkomponeninputmanual/nextstep')->with('message', trans('all.databerhasildihapus'));
        }
    }

    public function submitSimpan(Request $request)
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && (strpos(Session::get('hakakses_perusahaan')->payrollkomponeninputmanual, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponeninputmanual, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponeninputmanual, 'm') !== false)){
            if(Session::has('payrollkomponeninputmanual_bulan') && Session::has('payrollkomponeninputmanual_tahun') && Session::has('payrollkomponeninputmanual_payrollkomponenmaster')){
                $pdo = DB::connection('perusahaan_db')->getPdo();
                try {
                    $pdo->beginTransaction();
                    for ($i = 0; $i < count($request->idpegawai); $i++) {
                        if($request->tipedata == 'teks'){
                            $sql = 'INSERT INTO payroll_komponen_inputmanual VALUES(NULL,:idpayroll_komponen_master,:idpegawai,:periode,0,:keterangan,NOW(),NULL) ON DUPLICATE KEY UPDATE keterangan = :keterangan2, updated = NOW()';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idpayroll_komponen_master', Session::get('payrollkomponeninputmanual_payrollkomponenmaster'));
                            $stmt->bindValue(':idpegawai', $request->idpegawai[$i]);
                            $stmt->bindValue(':periode', Session::get('payrollkomponeninputmanual_tahun').Session::get('payrollkomponeninputmanual_bulan'));
                            $stmt->bindValue(':keterangan', str_replace('.','',$request->keterangan[$i]));
                            $stmt->bindValue(':keterangan2', str_replace('.','',$request->keterangan[$i]));
                            $stmt->execute();
                        } else {
                            // untuk angka dan uang
                            $nominal = $request->nominal[$i];
                            if ($nominal == '') {
                                $nominal = 0;
                            }
                            $sql = 'INSERT INTO payroll_komponen_inputmanual VALUES(NULL,:idpayroll_komponen_master,:idpegawai,:periode,:nominal,"",NOW(),NULL) ON DUPLICATE KEY UPDATE nominal = :nominal2, updated = NOW()';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idpayroll_komponen_master', Session::get('payrollkomponeninputmanual_payrollkomponenmaster'));
                            $stmt->bindValue(':idpegawai', $request->idpegawai[$i]);
                            $stmt->bindValue(':periode', Session::get('payrollkomponeninputmanual_tahun').Session::get('payrollkomponeninputmanual_bulan'));
                            $stmt->bindValue(':nominal', str_replace('.','',$nominal));
                            $stmt->bindValue(':nominal2', str_replace('.','',$nominal));
                            $stmt->execute();
                        }
                    }
                    $pdo->commit();
                    $bulan = Session::get('payrollkomponeninputmanual_bulan');
                    if($bulan[0] == 0){
                        $bulan = str_replace('0', '',$bulan);
                    }
                    Utils::insertLogUser('Ubah payroll komponen input manual periode ' . Utils::getBulan($bulan).' 20'.Session::get('payrollkomponeninputmanual_tahun'));
                    return redirect('datainduk/payroll/payrollkomponeninputmanual/nextstep')->with('message', trans('all.databerhasildisimpan'));
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return redirect('datainduk/payroll/payrollkomponeninputmanual/nextstep')->with('message', $e->getMessage());
                }
            }
        }
    }

    // public function submit(Request $request)
    // {
    //     $bulan = $request->bulan;
    //     $tahun =$request->tahun;
    //     $atributnilai = $request->atributnilai;
    //     $payrollkomponenmaster = $request->payrollkomponenmaster;
        
    //     Session::set('payrollkomponeninputmanual_bulan', $bulan);
    //     Session::set('payrollkomponeninputmanual_tahun', $tahun);
    //     Session::set('payrollkomponeninputmanual_atribut', $atributnilai);
    //     Session::set('payrollkomponeninputmanual_payrollkomponenmaster', $payrollkomponenmaster);

    //     if($bulan == ''){
    //         Session::forget('payrollkomponeninputmanual_bulan');
    //     }
        
    //     if($tahun == ''){
    //         Session::forget('payrollkomponeninputmanual_tahun');
    //     }

    //     if($atributnilai == ''){
    //         Session::forget('payrollkomponeninputmanual_atribut');
    //     }

    //     if($payrollkomponenmaster == ''){
    //         Session::forget('payrollkomponeninputmanual_payrollkomponenmaster');
    //     }

    //     $pdo = DB::connection('perusahaan_db')->getPdo();
    //     $data = '';
    //     if(Session::has('payrollkomponeninputmanual_bulan') && Session::has('payrollkomponeninputmanual_tahun') && Session::has('payrollkomponeninputmanual_payrollkomponenmaster')){
    //         $where = '';
    //         if(Session::has('payrollkomponeninputmanual_atribut')){
    //             $atributwhere = Session::get('payrollkomponeninputmanual_atribut');
    //             $atributnilaiwhere = Utils::atributNilai($atributwhere);
    //             $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilaiwhere.') )';
    //         }

    //         $sql = 'SELECT tipedata,inputmanual_filter FROM payroll_komponen_master WHERE id=:id';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->bindValue(':id', $payrollkomponenmaster);
    //         $stmt->execute();
    //         $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //         $tipedata = $row[0]['tipedata'];
    //         $inputmanual_filter = $row[0]['inputmanual_filter'];

    //         $sql = 'SELECT
    //                     p.id,
    //                     p.pin,
    //                     p.nama,
    //                     p.idagama,
    //                     IFNULL(a.agama, "") as agama,
    //                     p.pin,
    //                     p.pemindai,
    //                     p.nomorhp,
    //                     p.flexytime,
    //                     p.status,
    //                     p.tanggalaktif,
    //                     CONCAT("<span title=\"",p.nama,"\" class=\"detailpegawai\" onclick=\"detailpegawai(,p.id,)\" style=\"cursor:pointer;\">",p.nama,"</span>") as pegawai,
    //                     getatributpegawai_all(p.id) as atribut,
    //                     lower(payroll_getatributnilai(p.id)) as payroll_atributnilai,
    //                     lower(payroll_getatributvariable(p.id)) as payroll_atributvariable,
    //                     IFNULL(prki.nominal,0) as nominal,
    //                     prki.keterangan
    //                 FROM
    //                     pegawai p
    //                     LEFT JOIN payroll_komponen_inputmanual prki ON prki.idpegawai=p.id AND prki.periode = "'.$tahun.$bulan.'" AND prki.idpayroll_komponen_master = '.Session::get('payrollkomponeninputmanual_payrollkomponenmaster').'
    //                     LEFT JOIN pegawaiatribut pa ON pa.idpegawai=p.id
    //                     LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
    //                     LEFT JOIN atribut atr ON an.idatribut=atr.id
    //                     LEFT JOIN agama a ON p.idagama=a.id
    //                 WHERE
    //                     p.del = "t"
    //                 '.$where.'
    //                 GROUP BY
    //                     p.id
    //                 ORDER BY
    //                     p.nama';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute();
    //         $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //         Utils::payroll_init_eval();

    //         $data = array();
    //         for ($i=0; $i<count($row); $i++) {
    //             Utils::payroll_fetch_pegawai($PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $row[$i]);

    //             $___batas_bawah = 0;
    //             $___batas_atas = 9999999999;

    //             $filter = $inputmanual_filter;
    //             if($filter != '' && $tipedata == 'angka'){
    //                 Utils::payroll_replace_variablescript($filter);

    //                 eval($filter);
    //             }

    //             $data[$i]['id'] = $row[$i]['id'];
    //             $data[$i]['pin'] = $row[$i]['pin'];
    //             $data[$i]['nama'] = $row[$i]['nama'];
    //             $data[$i]['pegawai'] = $row[$i]['pegawai'];
    //             $data[$i]['atribut'] = $row[$i]['atribut'];
    //             $data[$i]['batas_bawah'] = $___batas_bawah;
    //             $data[$i]['batas_atas'] = $___batas_atas;
    //             $data[$i]['nominal'] = $row[$i]['nominal'];
    //             $data[$i]['keterangan'] = $row[$i]['keterangan'];
    //         }
    //     }

    //     $atributs = Session::get('payrollkomponeninputmanual_atribut');
    //     $atributnilai = Utils::atributNilai($atributs);
    //     $atributnilaiketerangan = '';
    //     if($atributnilai != ''){
    //         $atributnilaidipilih = Utils::getAtributSelected($atributnilai);
    //         $atributnilaiketerangan = Utils::atributNilaiKeterangan($atributnilaidipilih);
    //     }

    //     if($bulan[0] == 0){
    //         $bulan = str_replace('0', '',$bulan);
    //     }
    //     $keterangan = trans('all.periode').' : '.Utils::getBulan($bulan).' 20'.$tahun.' '.trans('all.payrollkomponenmaster').' : '.Utils::getDataWhere($pdo,'payroll_komponen_master','nama','id',$payrollkomponenmaster).' '.$atributnilaiketerangan;
        
    //     // keperluan modal set semua nominal / keterangan
    //     $listyymm = Utils::list_yymm(-1);

    //     return view('datainduk/payroll/payrollkomponeninputmanual/data', ['data' => $data, 'tipedata' => $tipedata, 'listyymm' => $listyymm, 'keterangan' => $keterangan, 'menu' => 'payrollkomponeninputmanual']);
    // }

    // public function checkScript_InputManual() {
    //     eval('function in_arrayi($needle, $haystack) { return in_array(strtolower($needle), array_map("strtolower", $haystack));}');

    //     $___batas_bawah = 0;
    //     $___batas_atas = 9999999999;

    //     $filter = $inputmanual_filter;
    //     if($filter != ''){
    //         //cek apakah ada evil?
    //         //if ()

    //         $filter = preg_replace('/\<av\>\w+<\/av\>/', '""', $filter);
    //         $filter = preg_replace('/\<an\>\w+<\/an\>/', 'array("")', $filter);

    //         // return $filter;
    //         $filter = str_replace('$', '$___', $filter);

    //         try {
    //             $result = eval($filter);
    //             return true;
    //         } catch (ParseError $e) {
    //         }
    //     }
    //     return false;
    // }

    public function excel()
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && strpos(Session::get('hakakses_perusahaan')->payrollkomponeninputmanual, 'l') !== false){
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

            Utils::setPropertiesExcel($objPHPExcel,trans('all.payrollkomponeninputmanual'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.pin'))
                        ->setCellValue('B1', trans('all.nama'))
                        ->setCellValue('C1', trans('all.atribut'))
                        ->setCellValue('D1', trans('all.nominal'));

            $where = '';
            if(Session::has('payrollkomponeninputmanual_atribut')){
                $atributwhere = Session::get('payrollkomponeninputmanual_atribut');
                $atributnilaiwhere = Utils::atributNilai($atributwhere);
                $where .= ' WHERE p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilaiwhere.') )';
            }

            $sql = 'SELECT
                        p.id,
                        p.pin,
                        p.nama,
                        IF(a.penting="y",GROUP_CONCAT(a.atribut," : ",an.nilai ORDER BY a.atribut SEPARATOR ", "),"") as atribut,
                        prki.nominal
                    FROM
                        pegawai p
                        LEFT JOIN payroll_komponen_inputmanual prki ON prki.idpegawai=p.id AND prki.idpayroll_komponen_master = '.Session::get('payrollkomponeninputmanual_payrollkomponenmaster').'
                        LEFT JOIN pegawaiatribut pa ON pa.idpegawai=p.id
                        LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
                        LEFT JOIN atribut a ON an.idatribut=a.id
                    WHERE
                        p.del = "t"
                        '.$where.'
                    GROUP BY
                        p.id
                    ORDER BY
                        p.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tahun', Session::get('payrollkomponeninputmanual_tahun'));
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['pin']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['atribut']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, number_format($row['nominal'],2,",","."));

                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $i)->applyFromArray($styleArray);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            $arrWidth = array(10, 50, 50, 15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth,$styleArray);
            Utils::insertLogUser('Ekspor payroll komponen input manual');
            Utils::setFileNameExcel(trans('all.payrollkomponeninputmanual'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}