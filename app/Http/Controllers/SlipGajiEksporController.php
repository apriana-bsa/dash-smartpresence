<?php
namespace App\Http\Controllers;

use App\slipgaji;
use App\Utils;

use Hamcrest\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_Writer_Excel2007;
use PHPExcel_IOFactory;
use PHPExcel_Style_Border;
use PHPExcel_Style_Alignment;
use PHPExcel_Cell_DataType;
use PHPExcel_Worksheet_MemoryDrawing;
use PHPExcel_Style_NumberFormat;
use ZipArchive;

class SlipGajiEksporController extends Controller
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
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT
                        pp.id,
                        pp.periode,
                        pp.tanggalawal,
                        pp.tanggalakhir,
                        pk.nama as kelompok
                    FROM
                        payroll_posting pp,
                        payroll_posting_komponen ppk,
                        payroll_komponen_master pkm,
                        payroll_kelompok pk
                    WHERE
                        ppk.idpayroll_posting = pp.id AND
                        ppk.komponenmaster_id = pkm.id AND
                        pkm.idpayroll_kelompok= pk.id
                    GROUP BY
                        pp.id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $dataposting = $stmt->fetchAll(PDO::FETCH_OBJ);
            $dataatribut = Utils::getAtribut();
            Utils::insertLogUser('akses menu payroll slip gaji');
            return view('datainduk/payroll/slipgajiekspor/index', ['dataposting' => $dataposting, 'dataatribut' => $dataatribut, 'menu' => 'payrollslipgaji']);
        } else {
            return redirect('/');
        }
    }

    public function excel(Request $request){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $filteratribut = Utils::atributNilai($request->atributnilai);
        $idpayrollposting = $request->payrollposting;
        $periodeyymm = Utils::getDataWhere($pdo, 'payroll_posting', 'periode', 'id', $idpayrollposting);
        $periode = Utils::periodeCantik($periodeyymm);
        $arrnamapegawai = [];
        $n = 0;

        $dataslipgaji = Utils::getData($pdo,'slipgaji','id');
        if($dataslipgaji != '') {
            foreach ($dataslipgaji as $slipgaji) {
                $idslipgaji = $slipgaji->id;
                // data pegawai berdasarkan tabel payroll_posting_pegawai dan slipgaji_pegawai
                $where1 = ' AND p.id IN(SELECT idpegawai FROM slipgaji_pegawai WHERE idslipgaji = ' . $idslipgaji . ')';
                if ($filteratribut != '') {
                    $where1 .= ' AND p.id IN(SELECT idpegawai FROM pegawaiatribut WHERE idatributnilai IN(' . $filteratribut . '))';
                }
                $sql1 = 'SELECT
                            ppp.idpegawai,
                            p.nama as namapegawai,
                            p.pin
                        FROM
                            payroll_posting_pegawai ppp,
                            pegawai p
                        WHERE
                            ppp.idpegawai = p.id AND
                            ppp.idpayroll_posting = :idpayroll_posting
                            ' . $where1 . '
                        GROUP BY
                            ppp.idpegawai';
                $stmt1 = $pdo->prepare($sql1);
                $stmt1->bindValue(':idpayroll_posting', $idpayrollposting);
                $stmt1->execute();

                // cek template slipgaji
//                $template = Utils::getDataWhere($pdo, 'slipgaji', 'template_excel', 'id', $idslipgaji);
                $template = Utils::getDataCustomWhere($pdo, 'slipgaji', 'template_excel', 'id = '.$idslipgaji.' ORDER BY berlakumulai DESC LIMIT 1');
                if ($template != '') {
                    $filetemplate = Session::get('folderroot_perusahaan') . '/payroll/slipgaji/' . $template;
                    if (file_exists($filetemplate)) {
                        // load template
                        $objPHPExcel = PHPExcel_IOFactory::load($filetemplate);
                        $objWorksheet = clone $objPHPExcel->getActiveSheet();
                        while ($row1 = $stmt1->fetch(PDO::FETCH_ASSOC)) {
                            $idpegawai = $row1['idpegawai'];
                            $namapegawai = $row1['namapegawai'];
                            // data payroll per pegawai
                            $sql2 = 'SELECT
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
                                        ppk.komponenmaster_id IN(SELECT idkomponenmaster FROM slipgaji_komponenmaster WHERE idslipgaji=' . $idslipgaji . ')';
                            $stmt2 = $pdo->prepare($sql2);
                            $stmt2->bindValue(':idpegawai', $idpegawai);
                            $stmt2->bindValue(':idpayroll_posting', $idpayrollposting);
                            $stmt2->execute();
                            $datapayroll = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                            $b = 1; // baris
                            $arrkom = [];
                            $x = 0;
                            while ($b < 50) {
                                $k = 1; // kolom
                                while ($k < 50) {
                                    $ak = Utils::angkaToHuruf($k);
                                    if ($objWorksheet->getCell($ak . $b)->getValue() == '<periode>') {
                                        $objPHPExcel->getActiveSheet()->setCellValue($ak . $b, $periode);
                                    }

                                    // jika ditemukan kode payroll komponen master
                                    for ($i = 0; $i < count($datapayroll); $i++) {
                                        if (strtoupper($objWorksheet->getCell($ak . $b)->getValue()) == '{' . strtoupper($datapayroll[$i]['kodekomponenmaster']) . '}') {
                                            $arrkom[$x] = $datapayroll[$i]['kodekomponenmaster'];
                                            $x++;
                                            $val_komponenmaster = $datapayroll[$i]['nominalkomponenmaster'];
                                            if ($datapayroll[$i]['tipedatakomponenmaster'] == 'uang') {
                                                $objPHPExcel->getActiveSheet()->getStyle($ak . $b)->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
                                            } else if ($datapayroll[$i]['tipedatakomponenmaster'] == 'angka') {
                                                $objPHPExcel->getActiveSheet()->getStyle($ak . $b)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
                                            } else if ($datapayroll[$i]['tipedatakomponenmaster'] == 'teks') {
                                                $objPHPExcel->getActiveSheet()->getStyle($ak . $b)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
                                                $val_komponenmaster = $datapayroll[$i]['keterangankomponenmaster'];
                                            }
                                            $objPHPExcel->getActiveSheet()->setCellValue($ak . $b, $val_komponenmaster);
                                        }
                                    }
                                    $k++;
                                }
                                $b++;
                            }

                            Utils::passwordExcel($objPHPExcel);
                            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
                            $dir = '../storage/' . str_replace('/', '', $namapegawai) . '.xlsx';
                            $writer->save($dir);

                            // untuk keperluan zip dan penghapusan file satuan
                            $arrnamapegawai[$n] = $namapegawai;
                            $n++;
                        }
                    }
                }
            }
            if (count($arrnamapegawai) > 0) {
                //proses zip
                $zipname = time() . '_' . trans('all.slipgaji') . '.zip';
                $zip = new ZipArchive;
                $zip->open($zipname, ZipArchive::CREATE);
                for ($i = 0; $i < count($arrnamapegawai); $i++) {
                    $zip->addFile('../storage/' . str_replace('/', '', $arrnamapegawai[$i]) . '.xlsx', str_replace('/', '', $arrnamapegawai[$i]) . '.xlsx');
                }
                $zip->close();

                header('Content-Type: application/zip');
                header('Content-disposition: attachment; filename=' . $zipname);
                header('Content-Length: ' . filesize($zipname));
                readfile($zipname);
                unlink($zipname);
                for ($i = 0; $i < count($arrnamapegawai); $i++) {
                    unlink('../storage/' . str_replace('/', '', $arrnamapegawai[$i]) . '.xlsx');
                }
            } else {
                return redirect('datainduk/payroll/slipgajiekspor')->with('message_error', trans('all.templatetidakditemukan'));
            }
        }else{
            return redirect('datainduk/payroll/slipgajiekspor')->with('message_error', trans('all.slipgajitidakditemukan'));
        }
    }

}