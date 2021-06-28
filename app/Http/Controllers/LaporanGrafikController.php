<?php

namespace App\Http\Controllers;

use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Worksheet_MemoryDrawing;
use PHPExcel_Cell_DataType;
use PHPExcel_Style_Fill;
use PHPExcel_Style_NumberFormat;

use PHPExcel_Chart_DataSeriesValues;
use PHPExcel_Chart_DataSeries;
use PHPExcel_Chart_Layout;
use PHPExcel_Chart_PlotArea;
use PHPExcel_Chart_Title;
use PHPExcel_Chart_Legend;
use PHPExcel_Chart;
use PHPExcel_IOFactory;

class LaporanGrafikController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function prosentaseAbsen(){
        if(Utils::cekHakakses('laporanlainnya','l')){
            if (Session::get('perusahaan_expired') == 'ya') {
                return redirect('/');
            }

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,nama FROM mesin WHERE status = "th" ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $mesinterhubung = $stmt->fetchAll(PDO::FETCH_OBJ);

            $sql = 'SELECT id,nama FROM mesin WHERE status = "bs" ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $mesinbebas = $stmt->fetchAll(PDO::FETCH_OBJ);

            $tahun = Utils::tahunDropdown();
            $totalhari = cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
            $atributs = Utils::getAtribut();
            $valuetglawalakhir = Utils::valueTanggalAwalAkhir();

            Session::set('lapprosentaseabsengrafik_bulan1', 1);
            Session::set('lapprosentaseabsengrafik_tahun1', date('Y'));
            Session::set('lapprosentaseabsengrafik_bulan2', date('m'));
            Session::set('lapprosentaseabsengrafik_tahun2', date('Y'));
            Session::set('lapprosentaseabsengrafik_jenis', 'perbulan');
            Session::set('lapprosentaseabsengrafik_tanggalawal', $valuetglawalakhir->tanggalawal);
            Session::set('lapprosentaseabsengrafik_tanggalakhir', $valuetglawalakhir->tanggalakhir);
            Utils::insertLogUser('akses menu laporan prosentase absen');
            return view('laporangrafik/prosentaseabsen/index', ['data' => '', 'tahun' => $tahun, 'totalhari' => $totalhari, 'valuetglawalakhir' => $valuetglawalakhir, 'atributs' => $atributs, 'mesinterhubung' => $mesinterhubung, 'mesinbebas' => $mesinbebas, 'atributvariable' => '', 'atributpenting' => '', 'keterangan' => '', 'menu' => 'lainnya']);
        } else {
            return redirect('/');
        }
    }

    public function submitProsentaseAbsen(Request $request)
    {
        if(Utils::cekDateTime($request->tanggalawal) && Utils::cekDateTime($request->tanggalakhir)) {
            Session::set('lapprosentaseabsengrafik_bulan1', $request->bulan1);
            Session::set('lapprosentaseabsengrafik_tahun1', $request->tahun1);
            Session::set('lapprosentaseabsengrafik_bulan2', $request->bulan2);
            Session::set('lapprosentaseabsengrafik_tahun2', $request->tahun2);
            Session::set('lapprosentaseabsengrafik_atribut', $request->atributnilai);
            Session::set('lapprosentaseabsengrafik_jenis', $request->jenis);
            Session::set('lapprosentaseabsengrafik_tanggalawal', $request->tanggalawal);
            Session::set('lapprosentaseabsengrafik_tanggalakhir', $request->tanggalakhir);
            return $this->excelProsentaseAbsen();
        }else{
            abort(404);
        }
    }

    public function excelProsentaseAbsen()
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $objPHPExcel = new PHPExcel();

        Utils::setPropertiesExcel($objPHPExcel,trans('all.prosentaseabsen'));

        $sql = 'SELECT gunakanpwd,pwd FROM parameterekspor';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);
        //sheet 1 data summary
        $objPHPExcel->createSheet();

        $bulan1 = Session::get('lapprosentaseabsengrafik_bulan1');
        $tahun1 = Session::get('lapprosentaseabsengrafik_tahun1');
        $bulan2 = Session::get('lapprosentaseabsengrafik_bulan2');
        $tahun2 = Session::get('lapprosentaseabsengrafik_tahun2');
        $jenislaporan = Session::get('lapprosentaseabsengrafik_jenis');
        $tanggalawal = Session::get('lapprosentaseabsengrafik_tanggalawal');
        $tanggalakhir = Session::get('lapprosentaseabsengrafik_tanggalakhir');

        $date1 = mktime(0,0,0,$bulan1,0,$tahun1); // m d y, use 0 for day
        $date2 = mktime(0,0,0,$bulan2,0,$tahun2); // m d y, use 0 for day
        $selisihbulan = round(($date2-$date1) / 60 / 60 / 24 / 30);

        $tahun = '';
        if($tahun1 != $tahun2){
            $tahun = $tahun1;
        }
        if($jenislaporan == 'perbulan') {
            $keteranganringkasan = trans('all.ringkasan') . ' ' . Utils::getBulan($bulan1, 'singkat') . ' ' . $tahun . ' - ' . Utils::getBulan($bulan2, 'singkat') . ' ' . $tahun2;
        }else{
            $keteranganringkasan = trans('all.ringkasan') . ' ' . Utils::tanggalCantikDariSampai(Utils::convertDmy2Ymd($tanggalawal),Utils::convertDmy2Ymd($tanggalakhir));
        }

        $objPHPExcel->removeSheetByIndex(1);

        $where = '';
        $whereatributnilai = '';
        if (Session::has('lapprosentaseabsengrafik_atribut')) {
            $atributs = Session::get('lapprosentaseabsengrafik_atribut');
            $atributnilai = Utils::atributNilai($atributs);
            $where .= ' AND pa.idatributnilai IN (' . $atributnilai . ')';
            $whereatributnilai .= ' WHERE id IN (' . $atributnilai . ')';
        }

        $sql = 'DROP TEMPORARY TABLE IF EXISTS temp_atribut';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $sql = 'CREATE TEMPORARY TABLE temp_atribut (
              id    INT UNSIGNED NOT NULL,
              nama  VARCHAR(100) NOT NULL,
            ';

        if($jenislaporan == 'perbulan') {
            $dt = '';
            for($i = 0; $i <= $selisihbulan; $i++){
                $dt =  date('Y-m-d', strtotime("+".$i." months", strtotime($tahun1.'-'.str_pad($bulan1,2,0,STR_PAD_LEFT).'-01')));
                $sql .= '_'.date("Ym", strtotime($dt)).' DECIMAL(10,2) NOT NULL DEFAULT 0, ';
//                $where .= ' AND MONTH(tanggal) = ' . date("n", strtotime($dt)) . ' AND YEAR(tanggal) = ' . date("Y", strtotime($dt));
            }
//            if($dt != '') {
//                $where .= ' AND MONTH(tanggal) = ' . date("n", strtotime($dt)) . ' AND YEAR(tanggal) = ' . date("Y", strtotime($dt));
//            }
        }else{
            $selisihbulan = 0;
            $dt = Utils::tanggalCantikDariSampai(Utils::convertDmy2Ymd($tanggalawal),Utils::convertDmy2Ymd($tanggalakhir));
            $sql .= '_pertanggal DECIMAL(10,2) NOT NULL DEFAULT 0, ';
            $where = ' AND tanggal>=CONCAT(STR_TO_DATE("' . $tanggalawal . '","%d/%m/%Y")," 00:00:00") AND tanggal<=CONCAT(STR_TO_DATE("' . $tanggalakhir . '","%d/%m/%Y")," 23:59:59")';
        }

        $sql .= ' PRIMARY KEY (id)) ENGINE=Memory';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $sql = 'INSERT INTO temp_atribut(id, nama) 
                SELECT 
                    id, nilai
                FROM 
                    atributnilai
            '.$whereatributnilai;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $sql = 'DROP TEMPORARY TABLE IF EXISTS temp_atribut_perbulan';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $sql = 'CREATE TEMPORARY TABLE temp_atribut_perbulan (
              id              INT UNSIGNED NOT NULL,
              nama            VARCHAR(100) NOT NULL,
              jumlahpekerja   INT NOT NULL,
              totaltransaksi  INT NOT NULL,
              tidakterlambat  INT NOT NULL,
              terlambat       INT NOT NULL,
              tidakabsen      INT NOT NULL,
              prosentase      DECIMAL(10,2) NOT NULL,
              PRIMARY KEY(id)
            ) Engine=Memory
            ';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        //set css kolom
        $headerBackgroundColor = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '70AD47')
            ),
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK,
                    'color' => array('argb' => '000000'),
                ),
            ),
        );

        $isiBackgroundColor = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FFE699')
            ),
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        );

        for($i = 0; $i <= $selisihbulan; $i++){
            if($jenislaporan == 'perbulan') {
                $dt = date('Y-m-d', strtotime("+" . $i . " months", strtotime($tahun1 . '-' . str_pad($bulan1, 2, 0, STR_PAD_LEFT) . '-01')));
            }
            //sheet detail data per bulan
            $objPHPExcel->createSheet();

            //set value kolom
            $objPHPExcel->setActiveSheetIndex($i+1)
                ->setCellValue('C4', trans('all.nomor_singkat'))
                ->setCellValue('D4', trans('all.atribut'))
                ->setCellValue('E4', trans('all.totalpegawai'))
                ->setCellValue('F4', trans('all.totalpresensi'))
                ->setCellValue('G4', trans('all.tidakterlambat'))
                ->setCellValue('H4', trans('all.terlambat'))
                ->setCellValue('I4', trans('all.belumabsen'))
                ->setCellValue('J4', trans('all.prosentase'));

            if($jenislaporan == 'perbulan') {
                $objPHPExcel->getActiveSheet()->setCellValue('C3', trans('all.ringkasan') . ' ' . Utils::getBulan(date("n", strtotime($dt))) . ' ' . date("Y", strtotime($dt)));
            }else{
                $objPHPExcel->getActiveSheet()->setCellValue('C3', trans('all.ringkasan') . ' ' . $dt);
            }
            $objPHPExcel->getActiveSheet()->getStyle('C3')->getFont()->setBold(true);

            $objPHPExcel->getActiveSheet()->getStyle('C4')->applyFromArray($headerBackgroundColor);
            $objPHPExcel->getActiveSheet()->getStyle('D4')->applyFromArray($headerBackgroundColor);
            $objPHPExcel->getActiveSheet()->getStyle('E4')->applyFromArray($headerBackgroundColor);
            $objPHPExcel->getActiveSheet()->getStyle('F4')->applyFromArray($headerBackgroundColor);
            $objPHPExcel->getActiveSheet()->getStyle('G4')->applyFromArray($headerBackgroundColor);
            $objPHPExcel->getActiveSheet()->getStyle('H4')->applyFromArray($headerBackgroundColor);
            $objPHPExcel->getActiveSheet()->getStyle('I4')->applyFromArray($headerBackgroundColor);
            $objPHPExcel->getActiveSheet()->getStyle('J4')->applyFromArray($headerBackgroundColor);

            $sql = 'TRUNCATE temp_atribut_perbulan';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            if($jenislaporan == 'perbulan'){
                $sql = 'INSERT INTO temp_atribut_perbulan 
                        SELECT
                          x.id,
                          x.nama,
                          x.jumlahpekerja,
                          x.totaltransaksi,
                          x.tidakterlambat,
                          x.terlambat,
                          x.tidakabsen,
                          IF(x.totaltransaksi<=0,0,x.tidakterlambat/x.totaltransaksi) as prosentase
                        FROM
                        (
                        SELECT
                          ta.id,
                          ta.nama,
                          COUNT(DISTINCT pa.idpegawai) as jumlahpekerja,
                          SUM(IF(ra.jadwalmasukkerja="y",1,0)) as totaltransaksi,
                          SUM(IF(ra.masukkerja="y" AND ra.jadwalmasukkerja="y" AND ra.selisihmasuk>=0,1,0)) as tidakterlambat,
                          SUM(IF(ra.masukkerja="y" AND ra.jadwalmasukkerja="y" AND ra.selisihmasuk<0,1,0)) as terlambat,
                          SUM(IF(ra.masukkerja="t" AND ra.jadwalmasukkerja="y",1,0)) as tidakabsen
                        FROM
                          rekapabsen ra,
                          temp_atribut ta,
                          pegawaiatribut pa
                        WHERE
                          ra.idpegawai=pa.idpegawai AND
                          pa.idatributnilai=ta.id AND
                          MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun
                          '.$where.'
                        GROUP BY
                          ta.id
                        ) x';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':bulan', date("n",strtotime($dt)));
                $stmt->bindValue(':tahun', date("Y",strtotime($dt)));
                $stmt->execute();
            }else {
                $sql = 'INSERT INTO temp_atribut_perbulan 
                            SELECT
                              x.id,
                              x.nama,
                              x.jumlahpekerja,
                              x.totaltransaksi,
                              x.tidakterlambat,
                              x.terlambat,
                              x.tidakabsen,
                              IF(x.totaltransaksi<=0,0,x.tidakterlambat/x.totaltransaksi) as prosentase
                            FROM
                            (
                            SELECT
                              ta.id,
                              ta.nama,
                              COUNT(DISTINCT pa.idpegawai) as jumlahpekerja,
                              SUM(IF(ra.jadwalmasukkerja="y",1,0)) as totaltransaksi,
                              SUM(IF(ra.masukkerja="y" AND ra.jadwalmasukkerja="y" AND ra.selisihmasuk>=0,1,0)) as tidakterlambat,
                              SUM(IF(ra.masukkerja="y" AND ra.jadwalmasukkerja="y" AND ra.selisihmasuk<0,1,0)) as terlambat,
                              SUM(IF(ra.masukkerja="t" AND ra.jadwalmasukkerja="y",1,0)) as tidakabsen
                            FROM
                              rekapabsen ra,
                              temp_atribut ta,
                              pegawaiatribut pa
                            WHERE
                              ra.idpegawai=pa.idpegawai AND
                              pa.idatributnilai=ta.id
                              ' . $where . '
                            GROUP BY
                              ta.id
                            ) x';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }

            if($jenislaporan == 'perbulan') {
                $sql = 'UPDATE temp_atribut ta, temp_atribut_perbulan tap SET ta._' . date("Ym", strtotime($dt)) . ' = tap.prosentase WHERE ta.id=tap.id';
            }else{
                $sql = 'UPDATE temp_atribut ta, temp_atribut_perbulan tap SET ta._pertanggal = tap.prosentase WHERE ta.id=tap.id';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'SELECT * FROM temp_atribut_perbulan ORDER BY nama ASC, id ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $j = 5;
            $no = 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $j, $no);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $j, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $j, $row['jumlahpekerja']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $j, $row['totaltransaksi']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $j, $row['tidakterlambat']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $j, $row['terlambat']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $j, $row['tidakabsen']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $j, $row['prosentase']);

                $objPHPExcel->getActiveSheet()->getStyle("J" . $j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

                $objPHPExcel->getActiveSheet()->getStyle('C' . $j)->applyFromArray($isiBackgroundColor);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $j)->applyFromArray($isiBackgroundColor);
                $objPHPExcel->getActiveSheet()->getStyle('E' . $j)->applyFromArray($isiBackgroundColor);
                $objPHPExcel->getActiveSheet()->getStyle('F' . $j)->applyFromArray($isiBackgroundColor);
                $objPHPExcel->getActiveSheet()->getStyle('G' . $j)->applyFromArray($isiBackgroundColor);
                $objPHPExcel->getActiveSheet()->getStyle('H' . $j)->applyFromArray($isiBackgroundColor);
                $objPHPExcel->getActiveSheet()->getStyle('J' . $j)->applyFromArray($isiBackgroundColor);
                $objPHPExcel->getActiveSheet()->getStyle('I' . $j)->applyFromArray($isiBackgroundColor);

                $j++;
                $no++;
            }

            $arrWidth = array('', 5, 30, 14, 14, 14, 14, 14, 14);
            for ($j = 1; $j <= 8; $j++) {
                $huruf = Utils::angkaToHuruf($j+2);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '4')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            if($jenislaporan == 'perbulan') {
                $objPHPExcel->getActiveSheet()->setTitle(Utils::getBulan(date("n", strtotime($dt))) . ' ' . date("Y", strtotime($dt)));
            }else{
                $objPHPExcel->getActiveSheet()->setTitle($dt);
            }

            // password
            Utils::passwordExcel($objPHPExcel);
        }

        //isikan data sheet 1
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B4', trans('all.nomor_singkat'))
            ->setCellValue('C4', trans('all.atribut'));

        //style cell
        $objPHPExcel->getActiveSheet()->getStyle('B4')->applyFromArray($headerBackgroundColor);
        $objPHPExcel->getActiveSheet()->getStyle('C4')->applyFromArray($headerBackgroundColor);

        $objPHPExcel->getActiveSheet()->setCellValue('B3', $keteranganringkasan);
        $objPHPExcel->getActiveSheet()->getStyle('B3')->getFont()->setBold(true);


        $starthurufke = 4;
        for($j = 0; $j <= $selisihbulan; $j++){
            $hurufbulan = Utils::angkaToHuruf($starthurufke);
            if($jenislaporan == 'perbulan') {
                $dt = date('Y-m-d', strtotime("+" . $j . " months", strtotime($tahun1 . '-' . str_pad($bulan1, 2, 0, STR_PAD_LEFT) . '-01')));
                $objPHPExcel->getActiveSheet()->setCellValue($hurufbulan . '4', Utils::getBulan(date("n", strtotime($dt))) . ' ' . date("Y", strtotime($dt)));
            }else{
                $objPHPExcel->getActiveSheet()->setCellValue($hurufbulan . '4', $dt);
            }
            $objPHPExcel->getActiveSheet()->getStyle($hurufbulan . '4')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension($hurufbulan)->setWidth(15);
            $objPHPExcel->getActiveSheet()->getStyle($hurufbulan . '4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle($hurufbulan . '4')->applyFromArray($headerBackgroundColor);

            $starthurufke++;
        }

        $sql = 'SELECT * FROM temp_atribut ORDER BY nama ASC, id ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $labels = array(
//            new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$D$4:$'.Utils::angkaToHuruf($selisihbulan+3).'$4', null, $selisihbulan)
        );
        $categories = array(
//            new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$C$5:$C$'.$stmt->rowCount()+5-1, null, $stmt->rowCount())
            new PHPExcel_Chart_DataSeriesValues('String', trans('all.ringkasan').'!$C$5:$C$'.($stmt->rowCount()+5-1), null, $stmt->rowCount())
        );
        $values = array(
//            new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$D$5:$'.Utils::angkaToHuruf($selisihbulan+3).'$'.$stmt->rowCount()+5, null, $stmt->rowCount()*$selisihbulan)
        );

        $plotlabel = array();
        for($idx = 0; $idx <= $selisihbulan; $idx++) {
            $labels[$idx] = new PHPExcel_Chart_DataSeriesValues('String', trans('all.ringkasan').'!$' . Utils::angkaToHuruf($idx + 4).'$4', null, 1);
            $values[$idx] = new PHPExcel_Chart_DataSeriesValues('Number', trans('all.ringkasan').'!$'.Utils::angkaToHuruf($idx + 4).'$5:$' . Utils::angkaToHuruf($idx + 4) . '$' . ($stmt->rowCount()+5-1), null, $stmt->rowCount());
            $plotlabel[$idx] = $idx;
        }

        $i= 5;
        $no = 1;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $no);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['nama']);

            $starthurufke = 4;
            for($j = 0; $j <= $selisihbulan; $j++){
                $hurufbulan = Utils::angkaToHuruf($starthurufke);
                if($jenislaporan == 'perbulan') {
                    $dt = date('Y-m-d', strtotime("+" . $j . " months", strtotime($tahun1 . '-' . str_pad($bulan1, 2, 0, STR_PAD_LEFT) . '-01')));
                    $objPHPExcel->getActiveSheet()->setCellValue($hurufbulan . $i, $row['_' . date("Ym", strtotime($dt))]);
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValue($hurufbulan . $i, $row['_pertanggal']);
                }
                $objPHPExcel->getActiveSheet()->getStyle($hurufbulan . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
                $objPHPExcel->getActiveSheet()->getStyle($hurufbulan . $i)->applyFromArray($isiBackgroundColor);

                $starthurufke++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getFont()->setBold(true);

            $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray($isiBackgroundColor);
            $objPHPExcel->getActiveSheet()->getStyle('C'.$i)->applyFromArray($isiBackgroundColor);

            $i++;
            $no++;
        }

        $objPHPExcel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);

        // password
        Utils::passwordExcel($objPHPExcel);

        $series = new PHPExcel_Chart_DataSeries(
            PHPExcel_Chart_DataSeries::TYPE_BARCHART,     // plotType
            PHPExcel_Chart_DataSeries::GROUPING_STANDARD,  // plotGrouping
//                array(0,1,2,3,4,5),                                     // plotOrder
            $plotlabel,                                     // plotOrder
            $labels,                                        // plotLabel
            $categories,                                    // plotCategory
            $values                                         // plotValues
        );

        $series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);
        $layout1 = new PHPExcel_Chart_Layout();    // Create object of chart layout to set data label
        $layout1->setShowVal(TRUE);
        $plotarea = new PHPExcel_Chart_PlotArea($layout1, array($series));
        $title    = new PHPExcel_Chart_Title(trans('all.prosentaseabsen'), null);
        $legend   = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_LEFT, null, false);
        $xTitle   = new PHPExcel_Chart_Title('');
        $yTitle   = new PHPExcel_Chart_Title('');
        $chart    = new PHPExcel_Chart(
            trans('all.ringkasan'),                         // name
            $title,                                         // title
            $legend,                                        // legend
            $plotarea,                                      // plotArea
            false,                                           // plotVisibleOnly
            0,                                              // displayBlanksAs
            $xTitle,                                        // xAxisLabel
            $yTitle                                         // yAxisLabel
        );
        if($no < 10 ) {
            $posisihurufchart = $selisihbulan + 25;
            $posisibarischart = '30';
        }else if($no < 30 ){
            $posisihurufchart = $selisihbulan+55+$no;
            $posisibarischart = '45';
        }else{
            $posisihurufchart = $selisihbulan+75;
            $posisibarischart = '80';
        }
        $chart->setTopLeftPosition(Utils::angkaToHuruf($selisihbulan+6).'4');
        $chart->setBottomRightPosition(Utils::angkaToHuruf($posisihurufchart).$posisibarischart);
        $objPHPExcel->getActiveSheet()->addChart($chart);

        $objPHPExcel->getActiveSheet()->setTitle(trans('all.ringkasan'));

        Utils::insertLogUser('Ekspor laporan prosentase absen');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . time() . '_' . trans('all.prosentaseabsen') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $writer->setIncludeCharts(TRUE);
        $writer->save('php://output');
    }
}