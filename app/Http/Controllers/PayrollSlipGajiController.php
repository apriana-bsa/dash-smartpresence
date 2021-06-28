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
use ZipArchive;

class PayrollSlipGajiController extends Controller
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
            $datapayrollposting = Utils::getData($pdo,'payroll_posting','id,periode,tanggalawal,tanggalakhir');
            $datakomponenmaster = [];
            $dataatribut = Utils::getData($pdo,'atribut','id,atribut,kode','','atribut');
            Utils::insertLogUser('akses menu payroll slip gaji');
	        return view('datainduk/payroll/payrollslipgaji/index', ['datapayrollposting' => $datapayrollposting, 'datakomponenmaster' => $datakomponenmaster, 'datakelompok' => $datakelompok, 'dataatribut' => $dataatribut, 'menu' => 'payrollslipgaji']);
        }else{
            return redirect('/');
        }
    }

    public function submit(Request $request){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $datakelompok = Utils::getData($pdo,'payroll_kelompok','id,nama','','nama');
        $datapayrollposting = Utils::getData($pdo,'payroll_posting','id,periode,tanggalawal,tanggalakhir');
        $datakomponenmaster = [];
	    if($request->payrollposting != ''){
	        Session::set('payrollslipgaji_idkelompok', $request->kelompok);
	        Session::set('payrollslipgaji_idpayrollposting', $request->payrollposting);
	        $sql = 'SELECT
                        komponenmaster_id as id,
                        komponenmaster_nama as nama,
                        komponenmaster_kode as kode
	                FROM
	                    payroll_posting_komponen
                    WHERE
                        idpayroll_posting = :idpayrollposting
                    ORDER BY
                        komponenmaster_urutan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpayrollposting', $request->payrollposting);
            $stmt->execute();
            $datakomponenmaster = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        $dataatribut = Utils::getData($pdo,'atribut','id,atribut,kode','','atribut');
        return view('datainduk/payroll/payrollslipgaji/index', ['datapayrollposting' => $datapayrollposting, 'datakomponenmaster' => $datakomponenmaster, 'datakelompok' => $datakelompok, 'dataatribut' => $dataatribut, 'menu' => 'payrollslipgaji']);
    }

    public function generate(Request $request){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idpayrollkelompok = Session::get('payrollslipgaji_idkelompok');
        $idpayrollposting = Session::get('payrollslipgaji_idpayrollposting');
        $idkomponenmaster_penerimaan = implode(',',$request->penerimaan_komponenmaster);
        $jumlah_penerimaan = count($request->penerimaan_komponenmaster);
        $idkomponenmaster_potongan = '';
        $jumlah_potongan = 0;
        if(isset($request->potongan_komponenmaster)){
            $idkomponenmaster_potongan = implode(',',$request->potongan_komponenmaster);
            $jumlah_potongan = count($request->potongan_komponenmaster);
        }
        // cari jumlah komponenmaster terbanyak untuk penambahan kolom
        $jumlahpenambahankolom = $jumlah_penerimaan > $jumlah_potongan ? $jumlah_penerimaan : $jumlah_potongan;

        $periodeyymm = Utils::getDataWhere($pdo,'payroll_posting','periode','id',$idpayrollposting);
        $periode = Utils::periodeCantik($periodeyymm);
        $template_slipgaji = Utils::getDataWhere($pdo,'payroll_kelompok','template_slipgaji','id',$idpayrollkelompok);
        if($template_slipgaji != '') {
            $path = Session::get('folderroot_perusahaan') . '/payroll/';
            $filetemplate = $path . $template_slipgaji;
            if (file_exists($filetemplate)) {
                $sqlP = 'SELECT
                            ppp.idpegawai,
                            p.nama as namapegawai,
                            p.pin
                        FROM
                            payroll_posting_pegawai ppp,
                            pegawai p
                        WHERE
                            ppp.idpegawai = p.id AND
                            ppp.idpayroll_posting = :idpayroll_posting
                        GROUP BY
                            ppp.idpegawai';
                $stmtP = $pdo->prepare($sqlP);
                $stmtP->bindValue(':idpayroll_posting', $idpayrollposting);
                $stmtP->execute();
                $arrnamapegawai = [];
                $n = 0;
                while($rowP = $stmtP->fetch(PDO::FETCH_ASSOC)) {
                    $idpegawai = $rowP['idpegawai'];
                    $namapegawai = $rowP['namapegawai'];
                    $objPHPExcel = PHPExcel_IOFactory::load($filetemplate);
                    $objWorksheet = clone $objPHPExcel->getActiveSheet();

                    // data payroll penerimaan
                    $sql3 = 'SELECT
                                ppk.komponenmaster_id as idkomponenmaster,
                                ppk.komponenmaster_kode as kodekomponenmaster,
                                ppk.komponenmaster_nama as namakomponenmaster,
                                ppk.komponenmaster_tipedata as tipedatakomponenmaster,
                                ppp.result_nominal as nominalkomponenmaster,
                                ppp.result_keterangan as keterangankomponenmaster
                            FROM
                                payroll_posting_pegawai ppp,
                                payroll_posting_komponen ppk
                            WHERE
                                ppp.idpayroll_posting_komponen = ppk.id AND
                                ppk.idpayroll_posting = :idpayroll_posting AND
                                ppp.idpegawai = :idpegawai AND
                                ppk.komponenmaster_id IN(' . $idkomponenmaster_penerimaan . ')';
                    $stmt3 = $pdo->prepare($sql3);
                    $stmt3->bindValue(':idpegawai', $idpegawai);
                    $stmt3->bindValue(':idpayroll_posting', $idpayrollposting);
                    $stmt3->execute();
                    $datapayrollpenerimaan = $stmt3->fetchAll(PDO::FETCH_ASSOC);

                    $datapayrollpotongan = '';
                    if($idkomponenmaster_potongan != '') {
                        // data payroll potongan
                        $sql3 = 'SELECT
                                    ppk.komponenmaster_id as idkomponenmaster,
                                    ppk.komponenmaster_kode as kodekomponenmaster,
                                    ppk.komponenmaster_nama as namakomponenmaster,
                                    ppk.komponenmaster_tipedata as tipedatakomponenmaster,
                                    ppp.result_nominal as nominalkomponenmaster,
                                    ppp.result_keterangan as keterangankomponenmaster
                                FROM
                                    payroll_posting_pegawai ppp,
                                    payroll_posting_komponen ppk
                                WHERE
                                    ppp.idpayroll_posting_komponen = ppk.id AND
                                    ppk.idpayroll_posting = :idpayroll_posting AND
                                    ppp.idpegawai = :idpegawai AND
                                    ppk.komponenmaster_id IN(' . $idkomponenmaster_potongan . ')';
                        $stmt3 = $pdo->prepare($sql3);
                        $stmt3->bindValue(':idpegawai', $idpegawai);
                        $stmt3->bindValue(':idpayroll_posting', $idpayrollposting);
                        $stmt3->execute();
                        $datapayrollpotongan = $stmt3->fetchAll(PDO::FETCH_ASSOC);
                    }

                    // dataatribut
                    $dataatribut = Utils::getData($pdo,'atribut','id,kode');

                    $b = 1; // baris
                    $arrkompenerimaan = [];
                    $arrkompotongan = [];
                    $x=0; // untuk penerimaan
                    $y=0; // untuk potongan
                    while ($b < 50) {
                        $k = 1; // kolom
                        while ($k < 50) {
                            $ak = Utils::angkaToHuruf($k);
                            if ($objWorksheet->getCell($ak . $b)->getValue() == '{periode}') {
                                $objPHPExcel->getActiveSheet()->setCellValue($ak . $b, $periode);
                            }
                            if ($objWorksheet->getCell($ak . $b)->getValue() == '{pegawainama}') {
                                $objPHPExcel->getActiveSheet()->setCellValue($ak . $b, $namapegawai);
                            }
                            if ($objWorksheet->getCell($ak . $b)->getValue() == '{pegawainip}') {
                                $objPHPExcel->getActiveSheet()->setCellValue($ak . $b, $rowP['pin']);
                            }

                            // atribut
                            $ia = 0;
                            if($dataatribut != '') {
                                foreach ($dataatribut as $key) {
                                    if ($objWorksheet->getCell($ak . $b)->getValue() == '{' . $key->kode . '}') {
                                        $sqlA = 'SELECT
                                                    an.nilai
                                                FROM
                                                    pegawaiatribut pa,
                                                    atributnilai an,
                                                    atribut a
                                                WHERE
                                                    pa.idatributnilai = an.id AND
                                                    an.idatribut = a.id AND
                                                    pa.idpegawai = :idpegawai AND
                                                    a.kode = :kode';
                                        $stmtA = $pdo->prepare($sqlA);
                                        $stmtA->bindValue(':idpegawai', $idpegawai);
                                        $stmtA->bindValue(':kode', $key->kode);
                                        $stmtA->execute();
                                        $pegawaiatribut = '';
                                        if($stmtA->rowCount()> 0){
                                            $rowA = $stmtA->fetch(PDO::FETCH_ASSOC);
                                            $pegawaiatribut = $rowA['nilai'];
                                        }
                                        $objPHPExcel->getActiveSheet()->setCellValue($ak . $b, $pegawaiatribut);
                                    }
                                    $ia++;
                                }
                            }

                            // jika ditemukan kode payroll komponen master penerimaan
                            for($i = 0;$i<count($datapayrollpenerimaan);$i++){
//                                if ($objWorksheet->getCell($ak . $b)->getValue() == '{'.strtoupper($datapayrollpenerimaan[$i]['kodekomponenmaster']).'}') {
                                if (strtoupper($objWorksheet->getCell($ak . $b)->getValue()) == '{'.strtoupper($datapayrollpenerimaan[$i]['kodekomponenmaster']).'}') {
                                    $arrkompenerimaan[$x] = $datapayrollpenerimaan[$i]['kodekomponenmaster'];
                                    $x++;
                                    $val_komponenmaster = $datapayrollpenerimaan[$i]['nominalkomponenmaster'];
                                    if ($datapayrollpenerimaan[$i]['tipedatakomponenmaster'] == 'uang') {
                                        $objPHPExcel->getActiveSheet()->getStyle($ak . $b)->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
                                    } else if ($datapayrollpenerimaan[$i]['tipedatakomponenmaster'] == 'teks') {
                                        $val_komponenmaster = $datapayrollpenerimaan[$i]['keterangankomponenmaster'];
                                    }
                                    $objPHPExcel->getActiveSheet()->setCellValue($ak . $b, $val_komponenmaster);
                                }
                            }

                            // jika ditemukan kode payroll komponen master potongan
                            if($idkomponenmaster_potongan != '') {
                                for ($i = 0; $i < count($datapayrollpotongan); $i++) {
//                                    if ($objWorksheet->getCell($ak . $b)->getValue() == '{' . strtoupper($datapayrollpotongan[$i]['kodekomponenmaster']) . '}') {
                                    if (strtoupper($objWorksheet->getCell($ak . $b)->getValue()) == '{' . strtoupper($datapayrollpotongan[$i]['kodekomponenmaster']) . '}') {
                                        $arrkompotongan[$y] = $datapayrollpotongan[$i]['kodekomponenmaster'];
                                        $y++;
                                        $val_komponenmaster = $datapayrollpotongan[$i]['nominalkomponenmaster'];
                                        if ($datapayrollpotongan[$i]['tipedatakomponenmaster'] == 'uang') {
                                            $objPHPExcel->getActiveSheet()->getStyle($ak . ($b + count($datapayrollpotongan)))->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
                                        } else if ($datapayrollpotongan[$i]['tipedatakomponenmaster'] == 'teks') {
                                            $val_komponenmaster = $datapayrollpotongan[$i]['keterangankomponenmaster'];
                                        }
                                        $objPHPExcel->getActiveSheet()->setCellValue($ak . $b, $val_komponenmaster);
                                    }
                                }
                            }
                            $k++;
                        }
                        $b++;
                    }

                    if( $jumlah_penerimaan > $jumlah_potongan) {
                        $jumlahpenambahankolomfix = $jumlahpenambahankolom - $x;
                    }else{
                        $jumlahpenambahankolomfix = $jumlahpenambahankolom - $y;
                    }

                    $b = 1; // baris
                    while ($b < 50) {
                        $k = 1; // kolom
                        while ($k < 50) {
                            $ak = Utils::angkaToHuruf($k);
                            // komponen master penerimaan
                            if ($objWorksheet->getCell($ak . $b)->getValue() == '{komponenmasterpenerimaan}') {
                                $bn = $b;
                                // tambahkan kolom sebanyak $jumlahpenambahan kolom
                                $objPHPExcel->getActiveSheet()->insertNewRowBefore(($b + 1), $jumlahpenambahankolomfix);
                                $datafound = false;
                                for ($i = 0; $i < count($datapayrollpenerimaan); $i++) {
                                    if(count($arrkompenerimaan) > 0){
                                        if(!in_array($datapayrollpenerimaan[$i]['kodekomponenmaster'],$arrkompenerimaan)){
                                            $datafound = true;
                                        }
                                    } else {
                                        $datafound = true;
                                    }
                                    if($datafound){
                                        $datafound = false;
                                        $objPHPExcel->getActiveSheet()->setCellValue($ak . $bn, $datapayrollpenerimaan[$i]['namakomponenmaster']);
                                        $bn++;
                                    }
                                }
                            }
                            if ($objWorksheet->getCell($ak . $b)->getValue() == '{rppenerimaan}') {
                                $bn = $b;
                                $datafound = false;
                                for($i = 0;$i<count($datapayrollpenerimaan);$i++){
                                    if(count($arrkompenerimaan) > 0){
                                        if(!in_array($datapayrollpenerimaan[$i]['kodekomponenmaster'],$arrkompenerimaan)){
                                            $datafound = true;
                                        }
                                    } else {
                                        $datafound = true;
                                    }
                                    if($datafound) {
                                        $datafound = false;
                                        $objPHPExcel->getActiveSheet()->setCellValue($ak . $bn, 'Rp');
                                        $bn++;
                                    }
                                }
                            }
                            if ($objWorksheet->getCell($ak . $b)->getValue() == '{jumlahpenerimaan}') {
                                $bn = $b;
                                $datafound = false;
                                for($i = 0;$i<count($datapayrollpenerimaan);$i++){
                                    if(count($arrkompenerimaan) > 0){
                                        if(!in_array($datapayrollpenerimaan[$i]['kodekomponenmaster'],$arrkompenerimaan)){
                                            $datafound = true;
                                        }
                                    } else {
                                        $datafound = true;
                                    }
                                    if($datafound) {
                                        $datafound = false;
                                        $val_komponenmaster = $datapayrollpenerimaan[$i]['nominalkomponenmaster'];
                                        if ($datapayrollpenerimaan[$i]['tipedatakomponenmaster'] == 'uang') {
                                            $objPHPExcel->getActiveSheet()->getStyle($ak . $bn)->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
                                        } else if ($datapayrollpenerimaan[$i]['tipedatakomponenmaster'] == 'teks') {
                                            $val_komponenmaster = $datapayrollpenerimaan[$i]['keterangankomponenmaster'];
                                        }
                                        $objPHPExcel->getActiveSheet()->setCellValue($ak . $bn, $val_komponenmaster);
                                        $bn++;
                                    }
                                }
                            }

                            // komponen master potongan
                            if($idkomponenmaster_potongan != '') {
                                if ($objWorksheet->getCell($ak . $b)->getValue() == '{komponenmasterpotongan}') {
                                    $bn = $b;
                                    $datafound = false;
                                    for ($i = 0; $i < count($datapayrollpotongan); $i++) {
                                        if(count($arrkompotongan) > 0){
                                            if(!in_array($datapayrollpotongan[$i]['kodekomponenmaster'],$arrkompotongan)){
                                                $datafound = true;
                                            }
                                        } else {
                                            $datafound = true;
                                        }
                                        if($datafound) {
                                            $datafound = false;
                                            $objPHPExcel->getActiveSheet()->setCellValue($ak . $bn, $datapayrollpotongan[$i]['namakomponenmaster']);
                                            $bn++;
                                        }
                                    }
                                }

                                if ($objWorksheet->getCell($ak . $b)->getValue() == '{rppotongan}') {
                                    $bn = $b;
                                    $datafound = false;
                                    for($i = 0;$i<count($datapayrollpotongan);$i++){
                                        if(count($arrkompotongan) > 0){
                                            if(!in_array($datapayrollpotongan[$i]['kodekomponenmaster'],$arrkompotongan)){
                                                $datafound = true;
                                            }
                                        } else {
                                            $datafound = true;
                                        }
                                        if($datafound) {
                                            $datafound = false;
                                            $objPHPExcel->getActiveSheet()->setCellValue($ak . $bn, 'Rp');
                                            $bn++;
                                        }
                                    }
                                }

                                if ($objWorksheet->getCell($ak . $b)->getValue() == '{jumlahpotongan}') {
                                    $bn = $b;
                                    $datafound = false;
                                    for($i = 0;$i<count($datapayrollpotongan);$i++){
                                        if(count($arrkompotongan) > 0){
                                            if(!in_array($datapayrollpotongan[$i]['kodekomponenmaster'],$arrkompotongan)){
                                                $datafound = true;
                                            }
                                        } else {
                                            $datafound = true;
                                        }
                                        if($datafound) {
                                            $datafound = false;
                                            $val_komponenmaster = $datapayrollpotongan[$i]['nominalkomponenmaster'];
                                            if ($datapayrollpotongan[$i]['tipedatakomponenmaster'] == 'uang') {
                                                $objPHPExcel->getActiveSheet()->getStyle($ak . $bn)->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
                                            } else if ($datapayrollpotongan[$i]['tipedatakomponenmaster'] == 'teks') {
                                                $val_komponenmaster = $datapayrollpotongan[$i]['keterangankomponenmaster'];
                                            }
                                            $objPHPExcel->getActiveSheet()->setCellValue($ak . $bn, $val_komponenmaster);
                                            $bn++;
                                        }
                                    }
                                }
                            }
                            $k++;
                        }
                        $b++;
                    }
//                    return '<br>ok';
                    $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
                    $dir = '../storage/' . str_replace('/', '', $namapegawai) . '.xlsx';
                    $writer->save($dir);

                    // untuk keperluan zip dan penghapusan file satuan
                    $arrnamapegawai[$n] = $namapegawai;
                    $n++;
                }
                //proses zip
                $zipname = time().'_'.trans('all.slipgaji').'.zip';
                $zip = new ZipArchive;
                $zip->open($zipname, ZipArchive::CREATE);
                for($i=0;$i<count($arrnamapegawai);$i++){
                    $zip->addFile('../storage/' . str_replace('/', '', $arrnamapegawai[$i]) . '.xlsx', str_replace('/', '', $arrnamapegawai[$i]).'.xlsx');
                }
                $zip->close();

                header('Content-Type: application/zip');
                header('Content-disposition: attachment; filename='.$zipname);
                header('Content-Length: ' . filesize($zipname));
                readfile($zipname);
                unlink($zipname);
                for($i=0;$i<count($arrnamapegawai);$i++){
                    unlink('../storage/' . str_replace('/', '', $arrnamapegawai[$i]) . '.xlsx');
                }
            }else{
                return redirect('datainduk/payroll/payrollslipgaji')->with('message',trans('all.templatetidakditemukan'));
            }
        }else{
            // jika tanpa template
            $sqlP = 'SELECT
                    ppp.idpegawai,
                    p.nama as namapegawai
                FROM
                    payroll_posting_pegawai ppp,
                    pegawai p
                WHERE
                    ppp.idpegawai = p.id AND
                    ppp.idpayroll_posting = :idpayroll_posting
                GROUP BY
                    ppp.idpegawai';
            $stmtP = $pdo->prepare($sqlP);
            $stmtP->bindValue(':idpayroll_posting', $idpayrollposting);
            $stmtP->execute();
            $arrnamapegawai = [];
            $n = 0;
            while($rowP = $stmtP->fetch(PDO::FETCH_ASSOC)) {
                $idpegawai = $rowP['idpegawai'];
                $namapegawai = $rowP['namapegawai'];
                $objPHPExcel = new PHPExcel();

                Utils::setPropertiesExcel($objPHPExcel, trans('all.slipgaji'));

                //set value kolom
                $objPHPExcel->setActiveSheetIndex(0);

                // penerimaan
                $sql = 'SELECT
                        ppp.nama,
                        ppk.komponenmaster_id as idkomponenmaster,
                        ppk.komponenmaster_kode as kodekomponenmaster,
                        ppk.komponenmaster_nama as namakomponenmaster,
                        ppk.komponenmaster_tipedata as tipedatakomponenmaster,
                        ppp.result_nominal as nominalkomponenmaster,
                        ppp.result_keterangan as keterangankomponenmaster
                    FROM
                        payroll_posting_pegawai ppp,
                        payroll_posting_komponen ppk
                    WHERE
                        ppp.idpayroll_posting_komponen = ppk.id AND
                        ppk.idpayroll_posting = :idpayroll_posting AND
                        ppp.idpegawai = :idpegawai AND
                        ppk.komponenmaster_id IN(' . $idkomponenmaster_penerimaan . ')';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $idpegawai);
                $stmt->bindValue(':idpayroll_posting', $idpayrollposting);
                $stmt->execute();
                $i = 2;

                $objPHPExcel->getActiveSheet()->setCellValue('A1', trans('all.penerimaan'));
                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $val_komponenmaster = $row['nominalkomponenmaster'];
                    if ($row['tipedatakomponenmaster'] == 'uang') {
                        $objPHPExcel->getActiveSheet()->getStyle('C' . $i)->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
                    } else if ($row['tipedatakomponenmaster'] == 'teks') {
                        $val_komponenmaster = $row['keterangankomponenmaster'];
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['namakomponenmaster']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, 'Rp.');
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $val_komponenmaster);
                    $i++;
                }

                if($idkomponenmaster_potongan != '') {
                    // potongan
                    $sql = 'SELECT
                            ppp.nama,
                            ppk.komponenmaster_id as idkomponenmaster,
                            ppk.komponenmaster_kode as kodekomponenmaster,
                            ppk.komponenmaster_nama as namakomponenmaster,
                            ppk.komponenmaster_tipedata as tipedatakomponenmaster,
                            ppp.result_nominal as nominalkomponenmaster,
                            ppp.result_keterangan as keterangankomponenmaster
                        FROM
                            payroll_posting_pegawai ppp,
                            payroll_posting_komponen ppk
                        WHERE
                            ppp.idpayroll_posting_komponen = ppk.id AND
                            ppk.idpayroll_posting = :idpayroll_posting AND
                            ppp.idpegawai = :idpegawai AND
                            ppk.komponenmaster_id IN(' . $idkomponenmaster_potongan . ')';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->bindValue(':idpayroll_posting', $idpayrollposting);
                    $stmt->execute();

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i + 1), trans('all.potongan'));
                    $objPHPExcel->getActiveSheet()->getStyle('A' . ($i + 1))->getFont()->setBold(true);

                    $i = $i + 2;
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $val_komponenmaster = $row['nominalkomponenmaster'];
                        if ($row['tipedatakomponenmaster'] == 'uang') {
                            $objPHPExcel->getActiveSheet()->getStyle('C' . $i)->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
                        } else if ($row['tipedatakomponenmaster'] == 'teks') {
                            $val_komponenmaster = $row['keterangankomponenmaster'];
                        }

                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['namakomponenmaster']);
                        $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, 'Rp.');
                        $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $val_komponenmaster);
                        $i++;
                    }
                }

                $arrWidth = array('', 35, 3, 25);
                for ($j = 1; $j <= 3; $j++) {
                    $huruf = Utils::angkaToHuruf($j);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
                }

                $sql = 'SELECT gunakanpwd,pwd FROM parameterekspor';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);
                // password
                if ($rowPE['gunakanpwd'] == 'y') {
                    Utils::passwordExcel($objPHPExcel, $rowPE['pwd']);
                }

                $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $dir = '../storage/' . str_replace('/', '', $namapegawai) . '.xlsx';
                // $writer->save('php://output');
                $writer->save($dir);

                // untuk keperluan zip dan penghapusan file satuan
                $arrnamapegawai[$n] = $namapegawai;
                $n++;
            }

            Utils::insertLogUser('Ekspor slip gaji');

            //proses zip
            $zipname = time().'_'.trans('all.slipgaji').'.zip';
            $zip = new ZipArchive;
            $zip->open($zipname, ZipArchive::CREATE);
            for($i=0;$i<count($arrnamapegawai);$i++){
                $zip->addFile('../storage/' . str_replace('/', '', $arrnamapegawai[$i]) . '.xlsx', str_replace('/', '', $arrnamapegawai[$i]).'.xlsx');
            }
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename='.$zipname);
            header('Content-Length: ' . filesize($zipname));
            readfile($zipname);
            unlink($zipname);
            for($i=0;$i<count($arrnamapegawai);$i++){
                unlink('../storage/' . str_replace('/', '', $arrnamapegawai[$i]) . '.xlsx');
            }
        }
    }
}