<?php
namespace App\Http\Controllers;

use App\Mesin;
use App\MesinAtribut;
use App\Atribut;
use App\AtributNilai;
use App\Utils;
use App\SlideShow;

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
use Hash;

class FingerprintConnectorController extends Controller
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
        if(Utils::cekHakakses('fingerprintconnector','l')){
            Utils::insertLogUser('akses menu fingerprint connector');
            return view('datainduk/absensi/mesin/fingerprintconnector/index', ['menu' => 'mesin']);
        } else {
            return redirect('/');
        }
    }

    public function show(Request $request)
    {
        if(Utils::cekHakakses('fingerprintconnector','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('fingerprintconnector','uhm')) {
                $columns = array('', 'nama', 'username', 'pushapi', 'keterangan', 'intervalceksync', 'sync_data_pada', 'clear_data_pada', 'lastsync', 'status');
            }else{
                $columns = array('nama', 'username', 'pushapi', 'keterangan', 'intervalceksync', 'sync_data_pada', 'clear_data_pada', 'lastsync', 'status');
            }
            $table = 'fingerprintconnector';
            $totalData = Utils::getDataCustomWhere($pdo,$table, 'count(id)');
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $columns[$request->input('order.0.column')];
            $orderAction = $request->input('order.0.dir');
            $orderBy = $orderColumn.' '.$orderAction;

            if(!empty($request->input('search.value'))){
                $search = $request->input('search.value');
                $where .= Utils::searchDatatableQuery($columns);
                $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE 1=1 '.$where;
                $stmt = $pdo->prepare($sql);
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                    }
                }
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalFiltered = $row['total'];
            }

            $sql = 'SELECT
                        id,
                        nama,
                        username,
                        pushapi,
                        keterangan,
                        intervalceksync,
                        sync_data_pada,
                        clear_data_pada,
                        lastsync,
                        status
                    FROM
                        '.$table.'
                    WHERE
                        1=1
                        '.$where.'
                    ORDER BY
                        '.$orderBy.'
                    LIMIT
                        '.$limit. '
                    OFFSET
                        '.$start;
            $stmt = $pdo->prepare($sql);
            if(!empty($request->input('search.value'))) {
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $request->input('search.value') . '%');
                    }
                }
            }
            $stmt->execute();
            $originaldata = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = array();
            if(!empty($originaldata)){
                foreach($originaldata as $key){
                    $action = '';
                    if(Utils::cekHakakses('fingerprintconnector','um')){
                        $action .= Utils::tombolManipulasi('resetkatasandi','fingerprintconnector',$key['id']);
                        $action .= Utils::tombolManipulasi('sinkronisasi','fingerprintconnector',$key['pushapi']);
                        $action .= Utils::tombolManipulasi('ubah','fingerprintconnector',$key['id']);
                    }
                    if(Utils::cekHakakses('fingerprintconnector','hm')){
                        $action .= Utils::tombolManipulasi('hapus','fingerprintconnector',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        $column = $columns[$i];
                        if($column == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($column == 'intervalceksync'){
                            $tempdata[$columns[$i]] = $key[$columns[$i]].' '.trans('all.detik');
                        }elseif($column == 'status'){
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]] == 'a' ? 'aktif' : 'tidakaktif');
                        }elseif($column == 'sync_data_pada' || $column == 'clear_data_pada'){
                            $hasil = '';
                            $datatemp = explode('|', $key[$columns[$i]]);
                            for($j=0;$j<count($datatemp);$j++){
                                $hasil .= $datatemp[$j].'<br>';
                            }
                            $tempdata[$columns[$i]] = $hasil;
                        }else{
                            $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                        }
                    }
                    $data[] = $tempdata;
                }
            }
            return Utils::jsonDatatable($request->input('draw'),$totalData,$totalFiltered,$data);
        }
        return '';
    }

    public function create()
    {
        if(Utils::cekHakakses('fingerprintconnector','tm')){
            $atributnilais = AtributNilai::select('atributnilai.id', DB::raw('atribut.id as idatribut'), 'atributnilai.nilai', 'atribut.atribut')
            ->leftjoin('atribut', 'atributnilai.idatribut', '=', 'atribut.id')
            ->get();

            $atributs = Atribut::select('id', 'atribut')->get();
            $slideshows = SlideShow::select('id', 'nama')->get();

            $atribut = Utils::getAtributdanAtributNilaiCrud(0, 'mesin');
            $pdo = DB::Connection('perusahaan_db')->getPdo();
            $fingerprintconnector = Utils::getData($pdo,'fingerprintconnector','id,nama','','nama');
            Utils::insertLogUser('akses menu tambah fingerprint connector');
            return view('datainduk/absensi/mesin/fingerprintconnector/create', ['fingerprintconnector' => $fingerprintconnector, 'atribut' => $atribut, 'atributnilais' => $atributnilais, 'atributs' => $atributs, 'slideshows' => $slideshows, 'menu' => 'mesin']);
        } else {
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'fingerprintconnector','id','nama',$request->nama);
        if($cekadadata == ''){
            $syncdatapada = '';
            for($i=0;$i<count($request->syncdatapadadari);$i++){
                $syncdatapada .= '|'.$request->syncdatapadadari[$i].'-'.$request->syncdatapadake[$i];
            }
            $syncdatapada = substr($syncdatapada,1);
            $cleardatapada = '';
            for($i=0;$i<count($request->cleardatapadadari);$i++){
                $cleardatapada .= '|'.$request->cleardatapadadari[$i].'-'.$request->cleardatapadake[$i];
            }
            $cleardatapada = substr($cleardatapada,1);

            $sql = 'SELECT uniqueid FROM uniqueid WHERE uniqueid NOT IN (SELECT username FROM fingerprintconnector) ORDER BY rand() LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $username = $row['uniqueid'];
            $katasandi = str_pad(rand(0, 9999),4,'0',STR_PAD_LEFT);
            $password = Hash::make($katasandi);

            $sql = 'INSERT INTO fingerprintconnector VALUES(NULL,:nama,:username,:password,:keterangan,:pushapi,:intervalceksync,:syncdatapada,:cleardatapada,:status,NULL,NOW())';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':password', $password);
            $stmt->bindValue(':keterangan', $request->keterangan);
            $stmt->bindValue(':pushapi', $request->pushapi);
            $stmt->bindValue(':intervalceksync', $request->intervalceksync);
            $stmt->bindValue(':syncdatapada', $syncdatapada);
            $stmt->bindValue(':cleardatapada', $cleardatapada);
            $stmt->bindValue(':status', $request->status);
            $stmt->execute();

            Utils::insertLogUser('Tambah fingerprint connector "' . $request->nama . '"');

            return redirect('datainduk/absensi/fingerprintconnector')->with('alert', trans('all.username').' : '.$username.' '.trans('all.katasandi').' : '.$katasandi);
        } else {
            return redirect('datainduk/absensi/fingerprintconnector/fingerprintconnector/create')->with('message', trans('all.datasudahada'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('fingerprintconnector','um')){
            $pdo = DB::Connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM fingerprintconnector WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            $syncdatapadadari = '';
            $syncdatapadake = '';
            if($data->sync_data_pada != '') {
                $syncdatapada_ex = explode("|", $data->sync_data_pada);
                for ($i = 0; $i < count($syncdatapada_ex); $i++) {
                    $dataex = explode("-", $syncdatapada_ex[$i]);
                    $syncdatapadadari .= '|' . $dataex[0];
                    if(count($dataex) > 1) {
                        $syncdatapadake .= '|' . $dataex[1];
                    }
                }
                $syncdatapadadari = explode("|", substr($syncdatapadadari, 1));
                $syncdatapadake = explode("|", substr($syncdatapadake, 1));
            }

            $cleardatapadadari = '';
            $cleardatapadake = '';
            if($data->clear_data_pada != '') {
                $cleardatapada_ex = explode("|", $data->clear_data_pada);
                for ($i = 0; $i < count($cleardatapada_ex); $i++) {
                    $dataex = explode("-", $cleardatapada_ex[$i]);
                    $cleardatapadadari .= '|' . $dataex[0];
                    if(count($dataex) > 1) {
                        $cleardatapadake .= '|' . $dataex[1];
                    }
                }
                $cleardatapadadari = explode("|", substr($cleardatapadadari, 1));
                $cleardatapadake = explode("|", substr($cleardatapadake, 1));
            }
            Utils::insertLogUser('akses menu ubah fingerprint conector');
            return view('datainduk/absensi/mesin/fingerprintconnector/edit', ['data' => $data, 'syncdatapadadari' => $syncdatapadadari, 'syncdatapadake' => $syncdatapadake, 'cleardatapadadari' => $cleardatapadadari, 'cleardatapadake' => $cleardatapadake, 'menu' => 'mesin']);
        } else {
            return redirect('/');
        }
    }


    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'fingerprintconnector','nama','id',$id);
        if($cekadadata != ''){
            $syncdatapada = '';
            for($i=0;$i<count($request->syncdatapadadari);$i++){
                $syncdatapada .= '|'.$request->syncdatapadadari[$i].'-'.$request->syncdatapadake[$i];
            }
            $syncdatapada = substr($syncdatapada,1);
            $cleardatapada = '';
            for($i=0;$i<count($request->cleardatapadadari);$i++){
                $cleardatapada .= '|'.$request->cleardatapadadari[$i].'-'.$request->cleardatapadake[$i];
            }
            $cleardatapada = substr($cleardatapada,1);

            $sql = 'UPDATE fingerprintconnector SET nama = :nama, keterangan = :keterangan, pushapi = :pushapi, intervalceksync = :intervalceksync, sync_data_pada = :syncdatapada, clear_data_pada = :cleardatapada, status = :status WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':keterangan', $request->keterangan);
            $stmt->bindValue(':pushapi', $request->pushapi);
            $stmt->bindValue(':intervalceksync', $request->intervalceksync);
            $stmt->bindValue(':syncdatapada', $syncdatapada);
            $stmt->bindValue(':cleardatapada', $cleardatapada);
            $stmt->bindValue(':status', $request->status);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

            Utils::insertLogUser('Ubah fingerprint connector "' . $cekadadata . '" => "' . $request->nama . '"');

            return redirect('datainduk/absensi/fingerprintconnector')->with('message', trans('all.databerhasildiubah'));
        } else {
            return redirect('datainduk/absensi/fingerprintconnector/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('fingerprintconnector','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'fingerprintconnector','nama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'fingerprintconnector',$id);
                Utils::insertLogUser('Hapus fingerprint connector "' . $cekadadata . '"');
                $msg = trans('all.databerhasildihapus');
            } else {
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('datainduk/absensi/fingerprintconnector')->with('message', $msg);
        } else {
            return redirect('/');
        }
    }

    public function verifikasi($id,$jenis)
    {
        if(Utils::cekHakakses('fingerprintconnector','um')){
            if($jenis == 'verifikasi') {
                return view('datainduk/absensi/mesin/fingerprintconnector/verifikasi', ['idmesin' => $id, 'menu' => 'mesin']);
            }else{
                $msg = '';
                //putussambungan
                $pdo = DB::connection('perusahaan_db')->getPdo();
                $sql = 'SELECT id, nama, status FROM mesin WHERE id=:idmesin LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idmesin', $id);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row['status'] == 'th') {
                        $sql = 'UPDATE mesin SET deviceid=NULL, deviceidreset=NULL, status="bs" WHERE id=:idmesin';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':idmesin', $id);
                        $stmt->execute();

                        Utils::insertLogUser('Memutus sambungan mesin "' . $row['nama'] . '"');
                        $msg = trans('all.sambunganmesinberhasildiputuskan');
                    } else {
                        $msg = trans('all.mesindalamkondisibebas');
                    }
                } else {
                    $msg = trans('all.datatidakditemukan');
                }
                return redirect('datainduk/absensi/mesin')->with('message', $msg);
            }
        } else {
            return redirect('/');
        }
    }

    public function submitVerifikasi(Request $request, $id)
    {

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $pdo2 = DB::getPdo();
        $kode = Utils::getDataWhere($pdo2,'perusahaan','kode','id',Session::get('conf_webperusahaan'));
        if($kode != ''){
            $sql = 'SELECT nama FROM mesin WHERE id=:idmesin AND status= "bs" LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idmesin', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $kodeverifikasi = $kode . Utils::generateRandomString(4);
                $deviceid = $request->deviceid . '-' . $kodeverifikasi;

                $sql = 'UPDATE mesin SET deviceid=:deviceid, deviceidreset=DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE id=:idmesin';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':deviceid', $deviceid);
                $stmt->bindValue(':idmesin', $id);
                $stmt->execute();

                Utils::insertLogUser('Meminta kode verifikasi untuk menyambungkan mesin "' . $row['nama'] . '"');

                $msg = trans('all.kodeverifikasi').' : '.$kodeverifikasi;
            } else {
                $msg = trans('all.datatidakditemukan');
            }
        } else {
            $msg = trans('all.perusahaantidakditemukan');
        }
        return redirect('datainduk/absensi/mesin')->with('message', trans('all.datatidakditemukan'));
    }

    public function resetPassword($id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $username = Utils::getDataWhere($pdo,'fingerprintconnector','username','id',$id);
        if($username != ''){
            $password = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $pwd_hash = Hash::make($password);
            $sql = 'UPDATE fingerprintconnector SET password=:password WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':password', $pwd_hash);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            Utils::insertLogUser('reset password fingerprint connector');

            return redirect('datainduk/absensi/fingerprintconnector')->with('alert', trans('all.username').' : '.$username.' '.trans('all.katasandi').' : '.$password);
        }
        else {
            return redirect('datainduk/absensi/fingerprintconnector')->with('message', trans('all.terjadigangguan'));
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('fingerprintconnector','l')){
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

            $sql = 'SELECT * FROM parameterekspor';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rowPE['logokiri'] == '' AND $rowPE['logokanan'] == '' AND $rowPE['header_1_teks'] == '' AND $rowPE['header_2_teks'] == '' AND $rowPE['header_3_teks'] == '' AND $rowPE['header_4_teks'] == '' AND $rowPE['header_5_teks'] == '') {
                $b = 1; //b = baris
            } else {
                $b = 6;
            }

            $b = $b + 1;

            Utils::setPropertiesExcel($objPHPExcel,trans('all.fingerprintconnector'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $b, trans('all.nama'))
                        ->setCellValue('B' . $b, trans('all.username'))
                        ->setCellValue('C' . $b, trans('all.keterangan'))
                        ->setCellValue('D' . $b, trans('all.intervalceksync'))
                        ->setCellValue('E' . $b, trans('all.syncdatapada'))
                        ->setCellValue('F' . $b, trans('all.cleardatapada'))
                        ->setCellValue('G' . $b, trans('all.lastsync'))
                        ->setCellValue('H' . $b, trans('all.status'));

            $sql = 'SELECT
                        id,
                        nama,
                        username,
                        keterangan,
                        intervalceksync,
                        sync_data_pada,
                        clear_data_pada,
                        lastsync,
                        IF(status="a","' . trans("all.aktif") . '","' . trans("all.tidakaktif") . '") as status
                    FROM
                        fingerprintconnector
                    ORDER BY
                        nama';

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = $b + 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $syncdatapada_ex = explode('|', $row['sync_data_pada']);
                $syncdatapada = '';
                for($j=0;$j<count($syncdatapada_ex);$j++){
                    $syncdatapada .= $syncdatapada_ex[$j].'
';
                }
                
                $cleardatapada_ex = explode('|', $row['clear_data_pada']);
                $cleardatapada = '';
                for($j=0;$j<count($cleardatapada_ex);$j++){
                    $cleardatapada .= $cleardatapada_ex[$j].'
';
                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['username']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['keterangan']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['intervalceksync']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $syncdatapada);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $cleardatapada);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['lastsync']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $row['status']);

                for ($j = 1; $j <= 8; $j++) {
                    $huruf = Utils::angkaToHuruf($j);
                    $objPHPExcel->getActiveSheet()->getStyle($huruf . $i)->applyFromArray($styleArray);
                }

                $i++;
            }

            $arrWidth = array('', 50, 15, 100, 25, 20, 20, 20, 15);
            for ($j = 1; $j <= 8; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            // style garis
            $end_i = $i - 1;
            $objPHPExcel->getActiveSheet()->getStyle('A1:H' . $end_i)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('A1:H' . $b)->applyFromArray($styleArray);

            $sql = 'SELECT * FROM parameterekspor';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($rowPE['footerkiri_1_teks'] == '' AND $rowPE['footerkiri_2_teks'] == '' AND $rowPE['footerkiri_3_teks'] == '' AND $rowPE['footerkiri_5_teks'] == '' AND $rowPE['footerkiri_6_teks'] == '' AND $rowPE['footertengah_1_teks'] == '' AND $rowPE['footertengah_2_teks'] == '' AND $rowPE['footertengah_3_teks'] == '' AND $rowPE['footertengah_5_teks'] == '' AND $rowPE['footertengah_6_teks'] == '' AND $rowPE['footerkanan_1_teks'] == '' AND $rowPE['footerkanan_2_teks'] == '' AND $rowPE['footerkanan_3_teks'] == '' AND $rowPE['footerkanan_5_teks'] == '' AND $rowPE['footerkanan_6_teks'] == '') {
                $l = $i - 1;
            } else {
                $l = $i + 1;
                Utils::footerExcel($objPHPExcel,'kiri','A','A',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'tengah','C','D',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'kanan','G','H',$l,$rowPE);
            }

            // password
            Utils::passwordExcel($objPHPExcel);

            if ($b != 1) {
                Utils::header5baris($objPHPExcel,'H',$rowPE);
            }

            //footer tanggal file dibuat
            date_default_timezone_set('Asia/Jakarta');
            $ft = $l + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $ft, '*tanggal pembuatan file ' . date('d/m/Y H:i:s'));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $ft)->getFont()->setItalic(true);

            $heightgambar = 99;
            $widthgambar = 99;
            if ($rowPE['logokiri'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokiri'];
                Utils::logoExcel('kiri',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'A1');
            }

            if ($rowPE['logokanan'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokanan'];
                Utils::logoExcel('kanan',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'H1');
            }

            Utils::insertLogUser('Ekspor fingerprint connector');
            Utils::setFileNameExcel(trans('all.fingerprintconnector'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}