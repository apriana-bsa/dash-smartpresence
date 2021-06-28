<?php
namespace App\Http\Controllers;

use App\agama;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
use App\Utils;

class LaporanCustomEksporController extends Controller
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
        if(Utils::cekHakakses('laporancustom','l')){
            Utils::insertLogUser('akses menu laporan custom ekspor');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $datakelompok = Utils::getData($pdo,'laporan_kelompok','id,nama','','nama');
            $dataatribut = Utils::getAtribut();
            $tanggal = Utils::valueTanggalAwalAkhir();
            $tahun = Utils::tahunDropdown();
            return view('laporan/custom/ekspor/index', ['datakelompok' => $datakelompok, 'dataatribut' => $dataatribut, 'tahun' => $tahun, 'tanggal' => $tanggal, 'menu' => 'laporancustomekspor']);
        }else{
            return redirect('/');
        }
	}

	public function submit(Request $request){
        if(Utils::cekHakakses('laporancustom','l')){
            if($request->filtermode == 'jangkauantanggal' && !Utils::cekDateTime($request->tanggalawal) && !Utils::cekDateTime($request->tanggalakhir)){
                return redirect('laporan/custom/ekspor');
            }

            Utils::payroll_init_eval();
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $idkelompok = $request->kelompok;
            $tanggalawal = $request->tanggalawal; //format dd/mm/yyyy
            $tanggalakhir = $request->tanggalakhir; //format dd/mm/yyyy
            $filtermode = $request->filtermode;
            $bulan = $request->bulan;
            $tahun = $request->tahun;

            if(!Utils::cekDateTime($tanggalawal)){
                return redirect('laporan/custom/ekspor')->with('message', trans('all.terjadigangguan'));
            }
            if(!Utils::cekDateTime($tanggalakhir)){
                return redirect('laporan/custom/ekspor')->with('message', trans('all.terjadigangguan'));
            }

            // cek jenis laporan
            $jenislaporan = Utils::getDataWhere($pdo,'laporan_kelompok','jenis','id',$idkelompok);
                if($jenislaporan == 'rekap') {
                    if ($filtermode == 'jangkauantanggal') {
                        $tglawal = Utils::convertDmy2Ymd($tanggalawal); // format yyy-mm-dd
                        $tglakhir = Utils::convertDmy2Ymd($tanggalakhir); // format yyy-mm-dd
                    } else {
                        $tglawal = $tahun . '-' . $bulan . '-01';
                        $tglakhir = $tahun . '-' . $bulan . '-' . cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
                    }
                    $msg = '';

                    $where = '';
                    if (isset($request->atributnilai)) {
                        $atributnilai = Utils::atributNilai($request->atributnilai);
                        $where .= ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . $atributnilai . ') )';
                    }

                    $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
                    if ($batasan!='') {
                        $where .= ' AND id IN '.$batasan;
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
                    $stmt->execute();
                    $pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // looping dari laporan_komponen_master
                    $sql1 = 'SELECT
                                lkm.id,
                                lkm.nama,
                                lower(lkm.kode) as kode,
                                lkm.tipekolom,
                                lkm.tipedata,
                                lkm.carainput,
                                lkm.inputmanual_filter,
                                IFNULL(lkm.idlaporan_komponen_master_group,"") as idlaporan_komponen_master_group,
                                lkm.formula,
                                lkm.carainput,
                                lkm.tampilkan,
                                lkm.urutan_perhitungan,
                                lkm.urutan_tampilan,
                                IFNULL(lkmg.nama,"") as laporankomponenmastergroup
                            FROM
                                laporan_komponen_master lkm
                                LEFT JOIN laporan_komponen_master_group lkmg ON lkm.idlaporan_komponen_master_group=lkmg.id
                            WHERE
                                lkm.idlaporan_kelompok = :idkelompok AND
                                lkm.digunakan = "y"
                            ORDER BY
                                lkm.urutan_tampilan ASC, lkm.nama ASC, lkm.id ASC';
                    $stmt1 = $pdo->prepare($sql1);
                    $stmt1->bindValue(':idkelompok', $idkelompok);
                    $stmt1->execute();
                    $datakomponen_master = $stmt1->fetchAll(PDO::FETCH_ASSOC);

                    // jangan lupa ditutup
                    // ini_set('memory_limit', '-1');
                    $result = array();
                    for ($i = 0; $i < count($pegawai); $i++) {
                        $result[$i] = $this->getLaporanCustomPerPegawai($pdo, $i + 1, $pegawai[$i]['id'], $tglawal, $tglakhir, $idkelompok);
                        if ($result[$i]['errorscript'] != '') {
                            $msg = $result[$i]['errorscript'];
                            break;
                        }
                    }

                    if (count($result) > 0 || $msg == '') {
                        // jumlah hari antara tanggalawal dan tanggalakhir
                        $sql = 'SELECT DATEDIFF(:tanggal2,:tanggal1) as totalhari';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':tanggal1', $tglawal);
                        $stmt->bindValue(':tanggal2', $tglakhir);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $totalhari = $row['totalhari'] + 1;

                        // jadikan file excel
                        $objPHPExcel = new PHPExcel();

                        Utils::setPropertiesExcel($objPHPExcel, trans('all.laporancustom'));

                        // set active sheet
                        $objPHPExcel->setActiveSheetIndex(0);

                        $arrHurufDel = [];
                        $ahd = 0;
                        $adagroup = false;
                        $ibarisheader = 1;

                        // cek apakah ada group
                        if (Utils::getDataCustomWhere($pdo, 'laporan_komponen_master', 'id', 'idlaporan_kelompok = ' . $idkelompok . ' AND isnull(idlaporan_komponen_master_group) = false LIMIT 1') != '') {
                            $adagroup = true;
                        }

                        // header dari komponen master
                        $idgroup_last = '';
                        for ($j = 0; $j < count($datakomponen_master); $j++) {
                            $huruf = Utils::angkaToHuruf($j + 1);

                            // header
                            if ($datakomponen_master[$j]['tipekolom'] != 'rangetanggal') {
                                // jika header menggunakan group
                                if ($datakomponen_master[$j]['idlaporan_komponen_master_group'] != '') {
                                    // merge
                                    if ($datakomponen_master[$j]['idlaporan_komponen_master_group'] != $idgroup_last) {
                                        $hurufawal = $huruf;
                                        $hurufakhir = Utils::angkaToHuruf($j + Utils::getTotalData(1, 'laporan_komponen_master', 'idlaporan_kelompok = ' . $idkelompok . ' AND idlaporan_komponen_master_group = ' . $datakomponen_master[$j]['idlaporan_komponen_master_group']));
                                        $objPHPExcel->getActiveSheet()->mergeCells($hurufawal . '1:' . $hurufakhir . '1');
                                    }
                                    $idgroup_last = $datakomponen_master[$j]['idlaporan_komponen_master_group'];
                                    $group = Utils::getDataWhere($pdo, 'laporan_komponen_master_group', 'nama', 'id', $datakomponen_master[$j]['idlaporan_komponen_master_group']);
                                    $objPHPExcel->getActiveSheet()->setCellValue($huruf . $ibarisheader, $group);
                                    $objPHPExcel->getActiveSheet()->setCellValue($huruf . '2', $datakomponen_master[$j]['nama']);
                                    $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                    $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    $objPHPExcel->getActiveSheet()->getStyle($huruf . '2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                    $objPHPExcel->getActiveSheet()->getStyle($huruf . '2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    $objPHPExcel->getActiveSheet()->getStyle($huruf . '2')->getFont()->setBold(true);
                                } else {
                                    $idgroup_last = '';
                                    $objPHPExcel->getActiveSheet()->setCellValue($huruf . $ibarisheader, $datakomponen_master[$j]['nama']);
                                    if ($datakomponen_master[$j]['idlaporan_komponen_master_group'] == '') {
                                        $objPHPExcel->getActiveSheet()->mergeCells($huruf . '1:' . $huruf . '2');
                                        $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                        $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    }
                                }
                                $objPHPExcel->getActiveSheet()->getStyle($huruf . $ibarisheader)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth(25);
                            } else {
                                $arrHurufDel[$ahd] = $huruf;
                                $ahd++;
                            }

                            // isi komponenmaster berjenis satuan
//                            $i = $adagroup ? 3 : 2; // baris mulai
                            $i = 3; // baris mulai
                            for ($k = 0; $k < count($result); $k++) {
                                for ($l = 0; $l < count($result[$k]['komponen']); $l++) {
                                    $huruf_isi = Utils::angkaToHuruf($l + 1);
                                    if ($result[$k]['komponen'][$l]['tipedata'] != 'teks' and $result[$k]['komponen'][$l]['tipedata'] != 'tanggal') {
                                        $isi = $result[$k]['komponen'][$l]['result_nominal'];
                                    } else {
                                        $isi = $result[$k]['komponen'][$l]['result_keterangan'];
                                    }
                                    if ($result[$k]['komponen'][$l]['tipekolom'] == 'satuan') {
                                        if ($result[$k]['komponen'][$l]['tipedata'] == 'angka') {
                                            $objPHPExcel->getActiveSheet()->getStyle($huruf_isi . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
                                        }
                                        if ($result[$k]['komponen'][$l]['tipedata'] == 'uang') {
                                            $objPHPExcel->getActiveSheet()->getStyle($huruf_isi . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                                        }
                                        if ($result[$k]['komponen'][$l]['tipedata'] == 'teks' && is_numeric($isi) && strlen($isi) > 11) {
                                            $isi = '\'' . $isi;
                                        }
                                        $objPHPExcel->getActiveSheet()->setCellValue($huruf_isi . $i, $isi);

                                    }
                                }
                                $i++;
                            }
                        }

                        // hapus kolom kosong(komponenmaster jenis rangetanggal)
                        for ($i = 0; $i < count($arrHurufDel); $i++) {
                            $objPHPExcel->getActiveSheet()->insertNewColumnBefore($arrHurufDel[$i], $totalhari);
                            $objPHPExcel->getActiveSheet()->removeColumn($arrHurufDel[$i]);
                        }

                        // looping untuk insert kolom komponenmaster berjenis rangetanggal
                        $h = $adagroup ? 3 : 2; // baris mulai
                        for ($k = 0; $k < count($result); $k++) {
                            for ($l = 0; $l < count($result[$k]['komponen']); $l++) {
                                if ($result[$k]['komponen'][$l]['tipekolom'] == 'rangetanggal') {
                                    if ($result[$k]['komponen'][$l]['tipedata'] != 'teks' and $result[$k]['komponen'][$l]['tipedata'] != 'tanggal') {
                                        $isi = $result[$k]['komponen'][$l]['result_nominal'];
                                    } else {
                                        $isi = $result[$k]['komponen'][$l]['result_keterangan'];
                                    }
                                    for ($j = 0; $j < $totalhari; $j++) {
                                        $huruf = Utils::angkaToHuruf($l + $j + 1);
                                        // header tanggal
                                        $newtgl = Utils::tanggalCantik(date('Y-m-d', strtotime($tglawal . ' +' . $j . ' day')));
                                        $objPHPExcel->getActiveSheet()->setCellValue($huruf . $ibarisheader, $newtgl);
                                        $objPHPExcel->getActiveSheet()->getStyle($huruf . $ibarisheader)->getFont()->setBold(true);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth(15);
                                        if ($result[$k]['komponen'][$l]['idlaporan_komponen_master_group'] == '') {
                                            $objPHPExcel->getActiveSheet()->mergeCells($huruf . '1:' . $huruf . '2');
                                            $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                            $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                        }

                                        // isi
                                        if (is_array($isi)) {
                                            if ($j < count($isi)) {
                                                $objPHPExcel->getActiveSheet()->setCellValue($huruf . $h, $isi[$j]);
                                            }
                                        } else {
                                            $objPHPExcel->getActiveSheet()->setCellValue($huruf . $h, $isi);
                                        }
                                    }
                                }
                            }
                            $h++;
                        }

                        Utils::passwordExcel($objPHPExcel);
                        Utils::insertLogUser('Ekspor Laporan Custom');
                        Utils::setFileNameExcel(trans('all.laporancustom'));
                        $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
                        $writer->save('php://output');
                        return '';
                    }
                    return redirect('laporan/custom/ekspor')->with('message', $msg);
                }else{
                    // jenis laporan detail
                    $tglawal = Utils::convertDmy2Ymd($tanggalawal);
                    $tglakhir = Utils::convertDmy2Ymd($tanggalakhir);
                    // jumlah hari antara tanggalawal dan tanggalakhir
                    $sql = 'SELECT DATEDIFF(:tanggal2,:tanggal1) as totalhari';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':tanggal1', $tglawal);
                    $stmt->bindValue(':tanggal2', $tglakhir);
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $totalhari = $row['totalhari'] + 1;

                    $where = '';
                    if (isset($request->atributnilai)) {
                        $atributnilai = Utils::atributNilai($request->atributnilai);
                        $where .= ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . $atributnilai . ') )';
                    }
                    $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
                    if ($batasan!='') {
                        $where .= ' AND id IN '.$batasan;
                    }
                    //ambil data pegawai
                    $sql = 'SELECT
                                id,
                                nama
                            FROM
                                pegawai
                            WHERE
                                del = "t" AND
                                `status` = "a"
                                ' . $where . '
                            ORDER BY
                                nama';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();

                    // jadikan file excel
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

                    Utils::setPropertiesExcel($objPHPExcel, trans('all.laporancustom'));

                    // set active sheet
                    $objPHPExcel->setActiveSheetIndex(0);
                    $b = 1; // baris(row)
                    while ($rowPegawai = $stmt->fetch(PDO::FETCH_ASSOC)){
                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$b, trans('all.nama'));
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$b, $rowPegawai['nama']);
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$b)->getFont()->setBold(true);

                        // lpm = laporan komponen master
                        $sqlLPM = 'SELECT
                                        lkm.id,
                                        lkm.nama,
                                        lkm.kode,
                                        lkm.tipekolom,
                                        lkm.tipedata,
                                        lkm.carainput,
                                        lkm.inputmanual_filter,
                                        IFNULL(lkm.idlaporan_komponen_master_group,"") as idlaporan_komponen_master_group,
                                        lkm.formula,
                                        lkm.carainput,
                                        lkm.tampilkan,
                                        lkm.urutan_perhitungan,
                                        lkm.urutan_tampilan,
                                        IFNULL(lkmg.nama,"") as laporankomponenmastergroup
                                    FROM
                                        laporan_komponen_master lkm
                                        LEFT JOIN laporan_komponen_master_group lkmg ON lkm.idlaporan_komponen_master_group=lkmg.id
                                    WHERE
                                        lkm.idlaporan_kelompok = :idkelompok AND
                                        lkm.digunakan = "y"
                                    ORDER BY
                                        lkm.urutan_tampilan ASC, lkm.nama ASC, lkm.id ASC';
                        $stmtLPM = $pdo->prepare($sqlLPM);
                        $stmtLPM->bindValue(':idkelompok', $idkelompok);
                        $stmtLPM->execute();
                        $huruf = 2; // karena dari B
                        $rt = $b;
                        $counter = 0;
                        while($rowLPM = $stmtLPM->fetch(PDO::FETCH_ASSOC)){
                            $hurufalpabet =Utils::angkaToHuruf($huruf);
                            $objPHPExcel->getActiveSheet()->setCellValue('A'.($b+1), trans('all.tanggal'));
                            $objPHPExcel->getActiveSheet()->setCellValue($hurufalpabet.($b+1), $rowLPM['nama']);
                            $objPHPExcel->getActiveSheet()->getStyle('A' . ($b+1))->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle($hurufalpabet . ($b+1))->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($hurufalpabet)->setWidth(25);
                            $objPHPExcel->getActiveSheet()->getStyle('A' . ($b+1))->applyFromArray($styleArray);
                            $objPHPExcel->getActiveSheet()->getStyle($hurufalpabet . ($b+1))->applyFromArray($styleArray);

                            $rt = $b+2; // rt = rowterakhir
                            for($j = 0;$j<$totalhari;$j++){
                                $tglharian = Utils::tanggalCantik(date('Y-m-d', strtotime($tglawal . ' +' . $j . ' day')));
                                $tgl = date('Y-m-d', strtotime($tglawal . ' +' . $j . ' day'));
                                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rt, $tglharian);
                                $objPHPExcel->getActiveSheet()->getStyle('A'.$rt)->applyFromArray($styleArray);

                                // isi
                                $result = $this->getLaporanCustomPerPegawaiPerTanggal($pdo, $counter + 1, $rowPegawai['id'], $tgl, $rowLPM);
                                if ($result['komponen']['tipedata'] != 'teks' and $result['komponen']['tipedata'] != 'tanggal') {
                                    $isi = $result['komponen']['result_nominal'];
                                } else {
                                    $isi = $result['komponen']['result_keterangan'];
                                }
                                if ($result['komponen']['tipekolom'] == 'satuan') {
                                    $objPHPExcel->getActiveSheet()->setCellValue($hurufalpabet . $rt, $isi);
                                    $objPHPExcel->getActiveSheet()->getStyle($hurufalpabet . $rt)->applyFromArray($styleArray);
                                    if ($result['komponen']['tipedata'] == 'angka') {
                                        $objPHPExcel->getActiveSheet()->getStyle($hurufalpabet . $rt)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
                                    }
                                    if ($result['komponen']['tipedata'] == 'uang') {
                                        $objPHPExcel->getActiveSheet()->getStyle($hurufalpabet . $rt)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                                    }
                                }
//                                if ($result['errorscript'] != '') {
//                                    $msg = $result['errorscript'];
//                                    break;
//                                }

                                $rt++;
                            }
                            $huruf++;
                        }
                        $b = $rt;
                        $b = $b+1;
                    }

                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(19);

                    $sql = 'SELECT gunakanpwd,pwd FROM parameterekspor';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);
                    // password
                    if ($rowPE['gunakanpwd'] == 'y') {
                        Utils::passwordExcel($objPHPExcel, $rowPE['pwd']);
                    }

                    Utils::insertLogUser('Ekspor Laporan Custom');
                    Utils::setFileNameExcel(trans('all.laporancustom'));
                    $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
                    $writer->save('php://output');
                }
        }else{
            return redirect('/');
        }
    }

    public function getLaporanCustomPerPegawai($pdo, $counter, $idpegawai, $tanggalawal, $tanggalakhir, $idkelompok){
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
            $cutitahunawal = Utils::getLamaCuti($tahunawal,$idpegawai);
            $cutitahunakhir = Utils::getLamaCuti($tahunakhir,$idpegawai);
            $cuti = $cutitahunawal + $cutitahunakhir;
        }else{
            $cuti = Utils::getLamaCuti($tahunakhir,$idpegawai);
        }

        // looping dari laporan_komponen_master untuk urutan perhitungan
        $sql1 = 'SELECT
                    lkm.id,
                    lkm.nama,
                    lower(lkm.kode) as kode,
                    lkm.tipekolom,
                    lkm.tipedata,
                    lkm.carainput,
                    lkm.inputmanual_filter,
                    IFNULL(lkm.idlaporan_komponen_master_group,"") as idlaporan_komponen_master_group,
                    lkm.formula,
                    lkm.carainput,
                    lkm.tampilkan,
                    lkm.urutan_perhitungan,
                    lkm.urutan_tampilan,
                    IFNULL(lkmg.nama,"") as laporankomponenmastergroup
                FROM
                    laporan_komponen_master lkm
                    LEFT JOIN laporan_komponen_master_group lkmg ON lkm.idlaporan_komponen_master_group=lkmg.id
                WHERE
                    lkm.idlaporan_kelompok = :idkelompok AND
                    lkm.digunakan = "y"
                ORDER BY
                    lkm.urutan_perhitungan ASC, lkm.nama ASC, lkm.id ASC';
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->bindValue(':idkelompok', $idkelompok);
        $stmt1->execute();
        $datakomponenmaster = $stmt1->fetchAll(PDO::FETCH_ASSOC);

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
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $namapegawai = $row['nama'];

            Utils::payroll_fetch_pegawai($PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $row);

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

            // ambil logabsen
            $sql = "SELECT * FROM logabsen WHERE idpegawai=:idpegawai AND waktu >= CONCAT(:tanggalawal,' 00:00:00') AND  waktu <= CONCAT(:tanggalakhir,' 23:59:59') ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":idpegawai",$idpegawai);
            $stmt->bindValue(":tanggalawal",$tanggalawal);
            $stmt->bindValue(":tanggalakhir",$tanggalakhir);
            $stmt->execute();
            $LOGABSEN = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ambil rekapabsen
            $sql = "SELECT * FROM rekapabsen WHERE idpegawai=:idpegawai AND tanggal >= :tanggalawal AND tanggal <= :tanggalakhir";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":idpegawai",$idpegawai);
            $stmt->bindValue(":tanggalawal",$tanggalawal);
            $stmt->bindValue(":tanggalakhir",$tanggalakhir);
            $stmt->execute();
            $REKAPABSEN = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ambil data komponenmaster
            $LAPORAN = array();
            for($i=0;$i<count($datakomponenmaster);$i++) {
                if ($datakomponenmaster[$i]['carainput']=='inputmanual') {
                    $sql = 'SELECT
                                IFNULL(nominal,0) as nominal,
                                keterangan
                            FROM
                                laporan_komponen_inputmanual
                            WHERE
                                idlaporan_komponen_master=:idlaporan_komponen_master AND
                                idpegawai=:idpegawai
                            LIMIT 1';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idlaporan_komponen_master', $datakomponenmaster[$i]['id']);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->execute();
                    if ($stmt->rowCount()>0) {
                        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if($datakomponenmaster[$i]['tipedata'] != 'teks' and $datakomponenmaster[$i]['tipedata'] != 'tanggal') {
                            $LAPORAN[$datakomponenmaster[$i]['kode']] = $row[0]['nominal'];
                        }else{
                            $LAPORAN[$datakomponenmaster[$i]['kode']] = $row[0]['keterangan'];
                        }
                    } else {
                        if($datakomponenmaster[$i]['tipedata'] != 'teks' and $datakomponenmaster[$i]['tipedata'] != 'tanggal') {
                            $LAPORAN[$datakomponenmaster[$i]['kode']] = 0;
                        }else{
                            $LAPORAN[$datakomponenmaster[$i]['kode']] = '';
                        }
                    }
                } else {
                    if($datakomponenmaster[$i]['tipedata'] != 'teks' and $datakomponenmaster[$i]['tipedata'] != 'tanggal') {
                        $LAPORAN[$datakomponenmaster[$i]['kode']] = 0;
                    }else{
                        $LAPORAN[$datakomponenmaster[$i]['kode']] = '';
                    }
                }
            }

            $sedang_memproses = 'inisialisasi';
            $script = '';
            // buat formula menjadi temporary fungsi (function_i)
            for($i=0;$i<count($datakomponenmaster);$i++) {
                if ($datakomponenmaster[$i]['carainput']=='formula' && $datakomponenmaster[$i]['formula']!='') {
                    $formula = $datakomponenmaster[$i]['formula'];
                    $lines = explode(PHP_EOL, $formula);
                    $temp_formula = '';
                    for($j=0;$j<count($lines);$j++) {
                        $temp_formula = $temp_formula . '   '. $lines[$j].PHP_EOL;
                    }
                    $temp_formula = PHP_EOL.'$formula_'.$i.' = function($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $LOGABSEN, $REKAPABSEN, $LAPORAN, $JAMKERJA, $JADWALSHIFT){'.PHP_EOL.$temp_formula. '  return $result;'.PHP_EOL.'};'.PHP_EOL;
                    $script = $script . $temp_formula;
                }
            }
            $script = $script . PHP_EOL;
            // panggil temporary function_i
            for($i=0;$i<count($datakomponenmaster);$i++) {
                $kode = strtolower($datakomponenmaster[$i]['kode']);
                if ($datakomponenmaster[$i]['carainput']=='formula' && $datakomponenmaster[$i]['formula']!='') {
                    $script = $script.' $sedang_memproses = "'.$datakomponenmaster[$i]['nama'].'"; ';
                    $script = $script.'$LAPORAN["'.$kode.'"] = $formula_'.$i.'($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $LOGABSEN, $REKAPABSEN, $LAPORAN, $JAMKERJA, $JADWALSHIFT);'.PHP_EOL;
                }
            }
            //buang (unset) temporary function_i tersebut
            $script = $script.PHP_EOL;
            for($i=0;$i<count($datakomponenmaster);$i++) {
                if ($datakomponenmaster[$i]['carainput']=='formula' && $datakomponenmaster[$i]['formula']!='') {
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
            $result['nama'] = $namapegawai;
            $result['total'] = 0;
            $result['komponen'] = array();
            $result['errorscript'] = $errorscript;
//            $result['script'] = $PEGAWAI;

            // untuk urutan tampilan
            $sql1 = 'SELECT
                    lkm.id,
                    lkm.nama,
                    lower(lkm.kode) as kode,
                    lkm.tipekolom,
                    lkm.tipedata,
                    lkm.carainput,
                    lkm.inputmanual_filter,
                    IFNULL(lkm.idlaporan_komponen_master_group,"") as idlaporan_komponen_master_group,
                    lkm.formula,
                    lkm.carainput,
                    lkm.tampilkan,
                    lkm.urutan_perhitungan,
                    lkm.urutan_tampilan,
                    IFNULL(lkmg.nama,"") as laporankomponenmastergroup
                FROM
                    laporan_komponen_master lkm
                    LEFT JOIN laporan_komponen_master_group lkmg ON lkm.idlaporan_komponen_master_group=lkmg.id
                WHERE
                    lkm.idlaporan_kelompok = :idkelompok AND
                    lkm.digunakan = "y"
                ORDER BY
                    lkm.urutan_tampilan ASC, lkm.nama ASC, lkm.id ASC';
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->bindValue(':idkelompok', $idkelompok);
            $stmt1->execute();
            $datakomponenmaster = $stmt1->fetchAll(PDO::FETCH_ASSOC);

            for ($i=0;$i<count($datakomponenmaster);$i++) {
                if($datakomponenmaster[$i]['tipedata'] != 'teks' and $datakomponenmaster[$i]['tipedata'] != 'tanggal') {
                    $result['komponen'][$i]['result_nominal'] = $LAPORAN[$datakomponenmaster[$i]['kode']];
                    $result['komponen'][$i]['result_keterangan'] = '';
                } else {
                    $result['komponen'][$i]['result_nominal'] = 0;
                    $result['komponen'][$i]['result_keterangan'] = $LAPORAN[$datakomponenmaster[$i]['kode']];
                }
                $result['komponen'][$i]['tipedata'] = $datakomponenmaster[$i]['tipedata'];
                $result['komponen'][$i]['tipekolom'] = $datakomponenmaster[$i]['tipekolom'];
                $result['komponen'][$i]['idlaporan_komponen_master_group'] = $datakomponenmaster[$i]['idlaporan_komponen_master_group'];
            }
            return $result;
        }
        return '';
    }

    public function getLaporanCustomPerPegawaiPerTanggal($pdo, $counter, $idpegawai, $tanggal, $datakomponenmaster){
        $COUNTER = $counter;
        $errorscript = '';
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
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $namapegawai = $row['nama'];

            Utils::payroll_fetch_pegawai($PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $row);

            // ambil logabsen
            $sql = "SELECT * FROM logabsen WHERE idpegawai=:idpegawai AND waktu >= CONCAT(:tanggalawal,' 00:00:00') AND  waktu <= CONCAT(:tanggalakhir,' 23:59:59') ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":idpegawai",$idpegawai);
            $stmt->bindValue(":tanggalawal",$tanggal);
            $stmt->bindValue(":tanggalakhir",$tanggal);
            $stmt->execute();
            $LOGABSEN = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ambil rekapabsen
//            $sql = "SELECT * FROM rekapabsen WHERE idpegawai=:idpegawai AND tanggal = :tanggal";
            $sql = 'SELECT
                        ra.*,
                        IFNULL(ha.tanggalawal,"") as harilibur_tanggalawal,
                        IFNULL(ha.tanggalakhir,"") as harilibur_tanggalakhir,
                        IFNULL(ha.keterangan,"") as harilibur_keterangan,
                        IFNULL(atm.alasan,"") as alasantidakmasuk_alasan,
                        IFNULL(atm.hitunguangmakan,"") as alasantidakmasuk_hitunguangmakan,
                        IFNULL(jk.nama,"") as jamkerja_nama,
                        IFNULL(jk.toleransi,"") as jamkerja_toleransi,
                        IFNULL(jk.acuanterlambat,"") as jamkerja_acuanterlambat,
                        IFNULL(jk.hitunglemburstlh,"") as jamkerja_hitunglemburstlh,
                        IFNULL(jk.digunakan,"") as jamkerja_digunakan,
                        IFNULL(jkk.keterangan,"") as jamkerjakhusus_keterangan,
                        IFNULL(jkk.tanggalawal,"") as jamkerjakhusus_tanggalawal,
                        IFNULL(jkk.tanggalakhir,"") as jamkerjakhusus_tanggalakhir,
                        IFNULL(jkk.toleransi,"") as jamkerjakhusus_toleransi,
                        IFNULL(jkk.perhitunganjamkerja,"") as jamkerjakhusus_perhitunganjamkerja,
                        IFNULL(jkk.hitunglemburstlh,"") as jamkerjakhusus_hitunglemburstlh,
                        IFNULL(jkk.jammasuk,"") as jamkerjakhusus_jammasuk,
                        IFNULL(jkk.jampulang,"") as jamkerjakhusus_jampulang,
                        IFNULL(amk.alasan,"") as alasanmasukkeluar_alasan,
                        IFNULL(amk.icon,"") as alasanmasukkeluar_icon,
                        IFNULL(amk.tampilsaat,"") as alasanmasukkeluar_tampilsaat,
                        IFNULL(amk.tampilpadamesin,"") as alasanmasukkeluar,
                        IFNULL(amk.terhitungkerja,"") as alasanmasukkeluar_terhitungkerja,
                        IFNULL(amk.digunakan,"") as alasanmasukkeluar_digunakan,
                        IFNULL(GROUP_CONCAT(TIME(raj.waktu) SEPARATOR " - " ),"") as rekapabsenjadwal_jadwalkerja
                    FROM
                        rekapabsen ra
                        LEFT JOIN harilibur ha ON ra.idharilibur=ha.id
                        LEFT JOIN alasantidakmasuk atm ON ra.idalasantidakmasuk=atm.id
                        LEFT JOIN jamkerja jk ON ra.idjamkerja=jk.id
                        LEFT JOIN jamkerjakhusus jkk ON ra.idjamkerjakhusus=jkk.id
                        LEFT JOIN alasanmasukkeluar amk ON ra.idalasanmasuk=amk.id
                        -- LEFT JOIN rekapabsen_hasil rah ON ra.id=rah.idrekapabsen
                        LEFT JOIN rekapabsen_jadwal raj ON ra.id=raj.idrekapabsen AND raj.checking IN ("start", "end")
                        -- LEFT JOIN rekapabsen_logabsen rala ON ra.id=rala.idrekapabsen
                        -- LEFT JOIN rekapabsen_logabsen_all ralaa ON ra.id=ralaa.idrekapabsen
                        -- LEFT JOIN rekapabsen_penempatan rap ON ra.id=rap.idrekapabsen
                    WHERE
                        ra.idpegawai=:idpegawai AND
                        ra.tanggal = :tanggal
                    GROUP BY
                        ra.id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":idpegawai",$idpegawai);
            $stmt->bindValue(":tanggal",$tanggal);
            $stmt->execute();
            $REKAPABSEN = [];
            IF($stmt->rowCount() > 0) {
                $REKAPABSEN = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // ambil data komponenmaster
            $LAPORAN = array();
            if ($datakomponenmaster['carainput']=='inputmanual') {
                $sql = 'SELECT
                            IFNULL(nominal,0) as nominal,
                            keterangan
                        FROM
                            laporan_komponen_inputmanual
                        WHERE
                            idlaporan_komponen_master=:idlaporan_komponen_master AND
                            idpegawai=:idpegawai
                        LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idlaporan_komponen_master',$datakomponenmaster ['id']);
                $stmt->bindValue(':idpegawai', $idpegawai);
                $stmt->execute();
                if ($stmt->rowCount()>0) {
                    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if($datakomponenmaster['tipedata'] != 'teks' and $datakomponenmaster['tipedata'] != 'tanggal') {
                        $LAPORAN[$datakomponenmaster['kode']] = $row[0]['nominal'];
                    }else{
                        $LAPORAN[$datakomponenmaster['kode']] = $row[0]['keterangan'];
                    }
                } else {
                    if($datakomponenmaster['tipedata'] != 'teks' and $datakomponenmaster['tipedata'] != 'tanggal') {
                        $LAPORAN[$datakomponenmaster['kode']] = 0;
                    }else{
                        $LAPORAN[$datakomponenmaster['kode']] = '';
                    }
                }
            } else {
                if($datakomponenmaster['tipedata'] != 'teks' and $datakomponenmaster['tipedata'] != 'tanggal') {
                    $LAPORAN[$datakomponenmaster['kode']] = 0;
                }else{
                    $LAPORAN[$datakomponenmaster['kode']] = '';
                }
            }

            $sedang_memproses = 'inisialisasi';
            $script = '';
            // buat formula menjadi temporary fungsi (function)
            if ($datakomponenmaster['carainput']=='formula' && $datakomponenmaster['formula']!='') {
                $formula = $datakomponenmaster['formula'];
                $lines = explode(PHP_EOL, $formula);
                $temp_formula = '';
                for($j=0;$j<count($lines);$j++) {
                    $temp_formula = $temp_formula . '   '. $lines[$j].PHP_EOL;
                }
                $temp_formula = PHP_EOL.'$formula = function($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $LOGABSEN, $REKAPABSEN, $LAPORAN){'.PHP_EOL.$temp_formula. '  return $result;'.PHP_EOL.'};'.PHP_EOL;
                $script = $script . $temp_formula;
            }
            $script = $script . PHP_EOL;
            // panggil temporary function_i
            
            $kode = strtolower($datakomponenmaster['kode']);
            if ($datakomponenmaster['carainput']=='formula' && $datakomponenmaster['formula']!='') {
                $script = $script.' $sedang_memproses = "'.$datakomponenmaster['nama'].'"; ';
                $script = $script.'$LAPORAN["'.$kode.'"] = $formula($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $LOGABSEN, $REKAPABSEN, $LAPORAN);'.PHP_EOL;
            }
            //buang (unset) temporary function_i tersebut
            $script = $script.PHP_EOL;
            if ($datakomponenmaster['carainput']=='formula' && $datakomponenmaster['formula']!='') {
                $script = $script.'unset($formula);'.PHP_EOL;
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
            $result['nama'] = $namapegawai;
            $result['total'] = 0;
            $result['komponen'] = array();
            $result['errorscript'] = $errorscript;

            if($datakomponenmaster['tipedata'] != 'teks' and $datakomponenmaster['tipedata'] != 'tanggal') {
                $result['komponen']['result_nominal'] = $LAPORAN[$kode];
                $result['komponen']['result_keterangan'] = '';
            } else {
                $result['komponen']['result_nominal'] = 0;
                $result['komponen']['result_keterangan'] = $LAPORAN[$kode];
            }
            $result['komponen']['tipedata'] = $datakomponenmaster['tipedata'];
            $result['komponen']['tipekolom'] = $datakomponenmaster['tipekolom'];
            $result['komponen']['idlaporan_komponen_master_group'] = $datakomponenmaster['idlaporan_komponen_master_group'];
            return $result;
        }
        return '';
    }
}