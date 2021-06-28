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

class LaporanKomponenInputManualController extends Controller
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
        if(Utils::cekHakakses('laporancustom','lm')){
            $bulan = date('m');
            $tahun = date('y');
            $keterangan = '';

            if(Session::has('laporankomponeninputmanual_bulan')){
                $bulan = Session::get('laporankomponeninputmanual_bulan');
            }
            if(Session::has('laporankomponeninputmanual_tahun')){
                $tahun = Session::get('laporankomponeninputmanual_tahun');
            }

//            $atributs = Utils::getAtributShift();
            $atributs = Utils::getAtribut();
            $tahundropdown = Utils::tahunDropdown();
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $datalaporankomponenmaster = Utils::getData($pdo,'laporan_komponen_master','id,nama','carainput="inputmanual"','urutan_tampilan ASC, nama ASC');
            Utils::insertLogUser('akses menu laporan komponen input manual');
            return view('laporan/custom/komponeninputmanual/index', ['tahundropdown' => $tahundropdown, 'atributs' => $atributs, 'datalaporankomponenmaster' => $datalaporankomponenmaster, "bulansekarang" => $bulan, "tahunsekarang" => $tahun, 'keterangan' => $keterangan, 'menu' => 'laporankomponeninputmanual']);
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

        Session::set('laporankomponeninputmanual_bulan', $bulan);
        Session::set('laporankomponeninputmanual_tahun', $tahun);
        Session::set('laporankomponeninputmanual_atribut', $atributnilai);

        if($bulan == ''){
            Session::forget('laporankomponeninputmanual_bulan');
        }

        if($tahun == ''){
            Session::forget('laporankomponeninputmanual_tahun');
        }

        if($atributnilai == ''){
            Session::forget('laporankomponeninputmanual_atribut');
        }

        $atributs = Session::get('laporankomponeninputmanual_atribut');
        return $this->dataNextStep($bulan,$tahun,$atributs);
    }

    public function nextStepGet(){
        if(Session::has('laporankomponeninputmanual_bulan') && Session::has('laporankomponeninputmanual_tahun') && Session::has('laporankomponeninputmanual_laporankomponenmaster')){
            $bulan = Session::get('laporankomponeninputmanual_bulan');
            $tahun = Session::get('laporankomponeninputmanual_tahun');
            $atributs = Session::get('laporankomponeninputmanual_atribut');
            // if(!is_array($atributs)){
            //     $atributs = $atributs != '' ? explode("|", $atributs) : '';
            // }
            return $this->dataNextStep($bulan,$tahun,$atributs);
        }
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
                    laporan_komponen_master pkm
                    LEFT JOIN laporan_komponen_inputmanual pkim ON pkim.idlaporan_komponen_master=pkm.id AND pkim.periode = :periode
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
        $datalaporankomponenmaster = '';
        if($stmt->rowCount() > 0){
            $datalaporankomponenmaster = $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        return view('laporan/custom/komponeninputmanual/nextstep', ['datalaporankomponenmaster' => $datalaporankomponenmaster, 'keterangan' => $keterangan, 'menu' => 'laporankomponeninputmanual']);
    }

    public function data($idlaporankomponenmaster)
    {
        $bulan = Session::get('laporankomponeninputmanual_bulan');
        $tahun = Session::get('laporankomponeninputmanual_tahun');
        $atributnilai = Session::get('laporankomponeninputmanual_atribut');
        Session::set('laporankomponeninputmanual_laporankomponenmaster', $idlaporankomponenmaster);

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $data = '';
        $tipedata = '';
        if(Session::has('laporankomponeninputmanual_bulan') && Session::has('laporankomponeninputmanual_tahun') && Session::has('laporankomponeninputmanual_laporankomponenmaster')){
            $where = '';
            if(Session::has('laporankomponeninputmanual_atribut')){
                $atributwhere = Session::get('laporankomponeninputmanual_atribut');
                $atributnilaiwhere = Utils::atributNilai($atributwhere);
                $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilaiwhere.') )';
            }

            $sql = 'SELECT tipedata,inputmanual_filter FROM laporan_komponen_master WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $idlaporankomponenmaster);
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
                            LEFT JOIN laporan_komponen_inputmanual prki ON prki.idpegawai=p.id AND prki.periode = "'.$tahun.$bulan.'" AND prki.idlaporan_komponen_master = '.$idlaporankomponenmaster.'
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

        $atributs = Session::get('laporankomponeninputmanual_atribut');
        $atributnilai = Utils::atributNilai($atributs);
        $atributnilaiketerangan = '';
        if($atributnilai != ''){
            $atributnilaidipilih = Utils::getAtributSelected($atributnilai);
            $atributnilaiketerangan = Utils::atributNilaiKeterangan($atributnilaidipilih);
        }

        if($bulan[0] == 0){
            $bulan = str_replace('0', '',$bulan);
        }
        $keterangan = trans('all.periode').' : '.Utils::getBulan($bulan).' 20'.$tahun.' '.trans('all.laporankomponenmaster').' : '.Utils::getDataWhere($pdo,'laporan_komponen_master','nama','id',$idlaporankomponenmaster).' '.$atributnilaiketerangan;
        
        // keperluan modal set semua nominal / keterangan
        $periode = $tahun.str_pad($bulan,2,"0",STR_PAD_LEFT);
        $tanggalperiode = '20'.substr($periode, 0, 2).'-'.substr($periode, -2).'-01';
        $tanggalakhir = date("Y-m-t", strtotime($tanggalperiode));
        $selisihbulan = Utils::selisihBulan(date('Y-m-d'),$tanggalakhir)+1;
        $listyymm = Utils::list_yymm(-$selisihbulan);

        return view('laporan/custom/komponeninputmanual/data', ['data' => $data, 'tipedata' => $tipedata, 'listyymm' => $listyymm, 'keterangan' => $keterangan, 'menu' => 'laporankomponeninputmanual']);
    }

    public function dataHapus($idlaporankomponenmaster){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $bulan = Session::get('laporankomponeninputmanual_bulan');
        $tahun = Session::get('laporankomponeninputmanual_tahun');
        
        $cekadadata = Utils::getDataWhere($pdo,'laporan_komponen_master','nama','id',$idlaporankomponenmaster);
        if($cekadadata != ''){
            //hapus
            Utils::deleteData($pdo,'laporan_komponen_inputmanual',$idlaporankomponenmaster,'idlaporan_komponen_master');
            Utils::insertLogUser('Hapus laporan komponen input manual periode ' . Utils::getBulan($bulan).' 20'.Session::get('laporankomponeninputmanual_tahun'));
            return redirect('laporan/custom/komponeninputmanual/nextstep')->with('message', trans('all.databerhasildihapus'));
        }
    }

    public function submitSimpan(Request $request)
    {
        if(strpos(Session::get('hakakses_perusahaan')->laporancustom, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->laporancustom, 'm') !== false){
            if(Session::has('laporankomponeninputmanual_bulan') && Session::has('laporankomponeninputmanual_tahun') && Session::has('laporankomponeninputmanual_laporankomponenmaster')){
                $pdo = DB::connection('perusahaan_db')->getPdo();
                try {
                    $pdo->beginTransaction();
                    for ($i = 0; $i < count($request->idpegawai); $i++) {
                        if($request->tipedata == 'teks'){
                            $sql = 'INSERT INTO laporan_komponen_inputmanual VALUES(NULL,:idlaporan_komponen_master,:idpegawai,:periode,0,:keterangan,NOW(),NULL) ON DUPLICATE KEY UPDATE keterangan = :keterangan2, updated = NOW()';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idlaporan_komponen_master', Session::get('laporankomponeninputmanual_laporankomponenmaster'));
                            $stmt->bindValue(':idpegawai', $request->idpegawai[$i]);
                            $stmt->bindValue(':periode', Session::get('laporankomponeninputmanual_tahun').Session::get('laporankomponeninputmanual_bulan'));
                            $stmt->bindValue(':keterangan', str_replace('.','',$request->keterangan[$i]));
                            $stmt->bindValue(':keterangan2', str_replace('.','',$request->keterangan[$i]));
                            $stmt->execute();
                        } else {
                            // untuk angka dan uang
                            $nominal = $request->nominal[$i];
                            if ($nominal == '') {
                                $nominal = 0;
                            }
                            $sql = 'INSERT INTO laporan_komponen_inputmanual VALUES(NULL,:idlaporan_komponen_master,:idpegawai,:periode,:nominal,"",NOW(),NULL) ON DUPLICATE KEY UPDATE nominal = :nominal2, updated = NOW()';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idlaporan_komponen_master', Session::get('laporankomponeninputmanual_laporankomponenmaster'));
                            $stmt->bindValue(':idpegawai', $request->idpegawai[$i]);
                            $stmt->bindValue(':periode', Session::get('laporankomponeninputmanual_tahun').Session::get('laporankomponeninputmanual_bulan'));
                            $stmt->bindValue(':nominal', str_replace('.','',$nominal));
                            $stmt->bindValue(':nominal2', str_replace('.','',$nominal));
                            $stmt->execute();
                        }
                    }
                    $pdo->commit();
                    $bulan = Session::get('laporankomponeninputmanual_bulan');
                    if($bulan[0] == 0){
                        $bulan = str_replace('0', '',$bulan);
                    }
                    Utils::insertLogUser('Ubah laporan komponen input manual periode ' . Utils::getBulan($bulan).' 20'.Session::get('laporankomponeninputmanual_tahun'));
                    return redirect('laporan/custom/komponeninputmanual/nextstep')->with('message', trans('all.databerhasildisimpan'));
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return redirect('laporan/custom/komponeninputmanual/nextstep')->with('message', $e->getMessage());
                }
            }
        }
    }

    // public function submit(Request $request)
    // {
    //     $bulan = $request->bulan;
    //     $tahun =$request->tahun;
    //     $atributnilai = $request->atributnilai;
    //     $laporankomponenmaster = $request->laporankomponenmaster;
        
    //     Session::set('laporankomponeninputmanual_bulan', $bulan);
    //     Session::set('laporankomponeninputmanual_tahun', $tahun);
    //     Session::set('laporankomponeninputmanual_atribut', $atributnilai);
    //     Session::set('laporankomponeninputmanual_laporankomponenmaster', $laporankomponenmaster);

    //     if($bulan == ''){
    //         Session::forget('laporankomponeninputmanual_bulan');
    //     }
        
    //     if($tahun == ''){
    //         Session::forget('laporankomponeninputmanual_tahun');
    //     }

    //     if($atributnilai == ''){
    //         Session::forget('laporankomponeninputmanual_atribut');
    //     }

    //     if($laporankomponenmaster == ''){
    //         Session::forget('laporankomponeninputmanual_laporankomponenmaster');
    //     }

    //     $pdo = DB::connection('perusahaan_db')->getPdo();
    //     $data = '';
    //     if(Session::has('laporankomponeninputmanual_bulan') && Session::has('laporankomponeninputmanual_tahun') && Session::has('laporankomponeninputmanual_laporankomponenmaster')){
    //         $where = '';
    //         if(Session::has('laporankomponeninputmanual_atribut')){
    //             $atributwhere = Session::get('laporankomponeninputmanual_atribut');
    //             $atributnilaiwhere = Utils::atributNilai($atributwhere);
    //             $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilaiwhere.') )';
    //         }

    //         $sql = 'SELECT tipedata,inputmanual_filter FROM laporan_komponen_master WHERE id=:id';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->bindValue(':id', $laporankomponenmaster);
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
    //                     lower(laporan_getatributnilai(p.id)) as laporan_atributnilai,
    //                     lower(laporan_getatributvariable(p.id)) as laporan_atributvariable,
    //                     IFNULL(prki.nominal,0) as nominal,
    //                     prki.keterangan
    //                 FROM
    //                     pegawai p
    //                     LEFT JOIN laporan_komponen_inputmanual prki ON prki.idpegawai=p.id AND prki.periode = "'.$tahun.$bulan.'" AND prki.idlaporan_komponen_master = '.Session::get('laporankomponeninputmanual_laporankomponenmaster').'
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

    //         Utils::laporan_init_eval();

    //         $data = array();
    //         for ($i=0; $i<count($row); $i++) {
    //             Utils::laporan_fetch_pegawai($PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $row[$i]);

    //             $___batas_bawah = 0;
    //             $___batas_atas = 9999999999;

    //             $filter = $inputmanual_filter;
    //             if($filter != '' && $tipedata == 'angka'){
    //                 Utils::laporan_replace_variablescript($filter);

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

    //     $atributs = Session::get('laporankomponeninputmanual_atribut');
    //     $atributnilai = Utils::atributNilai($atributs);
    //     $atributnilaiketerangan = '';
    //     if($atributnilai != ''){
    //         $atributnilaidipilih = Utils::getAtributSelected($atributnilai);
    //         $atributnilaiketerangan = Utils::atributNilaiKeterangan($atributnilaidipilih);
    //     }

    //     if($bulan[0] == 0){
    //         $bulan = str_replace('0', '',$bulan);
    //     }
    //     $keterangan = trans('all.periode').' : '.Utils::getBulan($bulan).' 20'.$tahun.' '.trans('all.laporankomponenmaster').' : '.Utils::getDataWhere($pdo,'laporan_komponen_master','nama','id',$laporankomponenmaster).' '.$atributnilaiketerangan;
        
    //     // keperluan modal set semua nominal / keterangan
    //     $listyymm = Utils::list_yymm(-1);

    //     return view('laporan/custom/komponeninputmanual/data', ['data' => $data, 'tipedata' => $tipedata, 'listyymm' => $listyymm, 'keterangan' => $keterangan, 'menu' => 'laporankomponeninputmanual']);
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
        if(strpos(Session::get('hakakses_perusahaan')->laporancustom, 'l') !== false){
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

            Utils::setPropertiesExcel($objPHPExcel,trans('all.laporankomponeninputmanual'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.pin'))
                        ->setCellValue('B1', trans('all.nama'))
                        ->setCellValue('C1', trans('all.atribut'))
                        ->setCellValue('D1', trans('all.nominal'));

            $where = '';
            if(Session::has('laporankomponeninputmanual_atribut')){
                $atributwhere = Session::get('laporankomponeninputmanual_atribut');
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
                        LEFT JOIN laporan_komponen_inputmanual prki ON prki.idpegawai=p.id AND prki.idlaporan_komponen_master = '.Session::get('laporankomponeninputmanual_laporankomponenmaster').'
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
            $stmt->bindValue(':tahun', Session::get('laporankomponeninputmanual_tahun'));
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
            Utils::insertLogUser('Ekspor laporan komponen input manual');
            Utils::setFileNameExcel(trans('all.laporankomponeninputmanual'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}