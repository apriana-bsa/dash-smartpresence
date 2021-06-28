<?php
namespace App\Http\Controllers;

use App\Perusahaan;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Worksheet_MemoryDrawing;

class PerusahaanController extends Controller
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
        Utils::insertLogUser('akses menu perusahaan');
        return view('perusahaan/index', ['menu' => 'perusahaan']);
    }
    
    public function show(Request $request)
    {
        $pdo = DB::getPdo();
        $where = ' AND 1=2';
        if (Session::has('iduser_perusahaan')) {
            $where = ' AND pn.idperusahaan=p.id AND p.status IN("a","c") AND pn.iduser = :iduser';
        }
        $columns = array('','nama','kode','status');
        $sql = 'SELECT COUNT(p.id) as total FROM pengelola pn, perusahaan p WHERE 1=1 '.$where;
        $stmt = $pdo->prepare($sql);
        if (Session::has('iduser_perusahaan')) {
            $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalData = $row['total'];
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumn = $columns[$request->input('order.0.column')];
        $orderAction = $request->input('order.0.dir');
        $orderBy = $orderColumn.' '.$orderAction;

        if(!empty($request->input('search.value'))){
            $search = $request->input('search.value');
            $where .= Utils::searchDatatableQuery($columns);
            $sql = 'SELECT COUNT(p.id) as total FROM pengelola pn, perusahaan p WHERE 1=1 '.$where;
            $stmt = $pdo->prepare($sql);
            if (Session::has('iduser_perusahaan')) {
                $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
            }
            for($i=0;$i<count($columns);$i++) {
                if($columns[$i] != '') {
                    $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                }
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalFiltered = $row['total'];
        }

        $sql = 'SELECT p.id, p.nama, p.kode, p.status FROM pengelola pn, perusahaan p WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
        $stmt = $pdo->prepare($sql);
        if (Session::has('iduser_perusahaan')) {
            $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        }
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
                if(strpos(Session::get('hakakses_perusahaan')->perusahaan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->perusahaan, 'm') !== false){
                    $action .= Utils::tombolManipulasi('ubah','perusahaan',$key['id']);
                }
                if(strpos(Session::get('hakakses_perusahaan')->perusahaan, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->perusahaan, 'm') !== false){
                    $action .= Utils::tombolManipulasi('hapus','perusahaan',$key['id']);
                }
                $tempdata['action'] = '<center>'.$action.'</center>';
                for($i=1;$i<count($columns);$i++){
                    if($columns[$i] == 'status') {
                        $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]] == 'a' ? 'aktif' : ($key[$columns[$i]] == 't' ? 'tidakaktif' : ($key[$columns[$i]] == 'c' ? 'confirm' : '-')));
                    }else{
                        $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                    }
                }
                $data[] = $tempdata;
            }
        }
        return Utils::jsonDatatable($request->input('draw'),$totalData,$totalFiltered,$data);
    }
    
    public function create()
    {
        Utils::insertLogUser('akses menu tambah perusahaan');
        return view('perusahaan/create', ['menu' => 'perusahaan']);
    }
    
    public function store(Request $request)
    {
        $db = DB::getPdo();

        //cek captcha
//        $captcha = Utils::captchaCheck($request->input('g-recaptcha-response'));
//        //$captcha = 1;
//        if($captcha == 1) {
            try {
                $db->beginTransaction();
                $sql = 'CALL buat_perusahaan_baru(
                                            :iduser,
                                            :namaperusahaan,
                                            :pic_nama,
                                            :pic_alamat,
                                            :pic_notelp,
                                            :pic_email,
                                            @_idperusahaan,
                                            @_dbhost,
                                            @_dbport,
                                            @_dbuser,
                                            @_dbpass,
                                            @_dbname,
                                            @_folderroot,
                                            @_status
                                          )';
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                $stmt->bindValue(':namaperusahaan', $request->nama);
                $stmt->bindValue(':pic_nama', $request->pic_nama);
                $stmt->bindValue(':pic_alamat', $request->pic_alamat);
                $stmt->bindValue(':pic_notelp', $request->pic_notelp);
                $stmt->bindValue(':pic_email', $request->pic_email);
                $stmt->execute();

                $sql = 'SELECT 
                        @_idperusahaan as idperusahaan,
                        @_dbhost as dbhost,
                        @_dbport as dbport,
                        @_dbuser as dbuser,
                        @_dbpass as dbpass,
                        @_dbname as dbname,
                        @_folderroot as folderroot,
                        @_status as status';
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $idperusahaan = $row['idperusahaan'];

                if ($row['status'] == 'OK') {

                    // simpan foto jika ada
                    if ($request->hasFile('foto')) {
                        $fotoprofil = $request->file('foto');
                        //cek apakah format jpeg?
                        if ($fotoprofil->getMimeType() == 'image/jpeg' || $fotoprofil->getMimeType() == 'image/png' || $fotoprofil->getMimeType() == 'image/bmp') {
                            $path = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG');
                            if (!file_exists($path)) {
                                mkdir($path, 0777, true);
                            }
                            $path = $path . '/' . config('consts.FOLDER_FOTO_PERUSAHAAN');
                            if (!file_exists($path)) {
                                mkdir($path, 0777, true);
                            }
                            $path = $path . '/' . Utils::id2Folder($idperusahaan);
                            if (!file_exists($path)) {
                                mkdir($path, 0777, true);
                            }

                            Utils::makeThumbnail($fotoprofil, $path . '/logo_perusahaan_thumb');
                            Utils::saveUploadImage($fotoprofil, $path . '/logo_perusahaan');

                            $checksum = md5_file($path . '/' . $idperusahaan);

                            $sql = 'UPDATE perusahaan set checksum_img = :checksum WHERE id = :idperusahaan';
                            $stmt = $db->prepare($sql);
                            $stmt->bindValue(':checksum', $checksum);
                            $stmt->bindValue(':idperusahaan', $idperusahaan);
                            $stmt->execute();

                            Session::set('fotoperusahaan_perusahaan', 'ada');
                        } else {
                            return redirect('perusahaan/create')->with('message', trans('all.formatgambartidakvalid'));
                        }
                    }

                    $kode = md5($idperusahaan.'_create_perusahaan_smartpresence!');
                    $sql1 = 'INSERT INTO perusahaan_konfirmasi VALUES(NULL,:idperusahaan,:kode,1,"t",NOW())';
                    $stmt1 = $db->prepare($sql1);
                    $stmt1->bindValue(':idperusahaan', $idperusahaan);
                    $stmt1->bindValue(':kode', $kode);
                    $stmt1->execute();

                    $idperusahaankonfirmasi = $db->lastInsertId();

                    $data = array('nama' => $request->nama, 'nomorhp' => $request->pic_notelp, 'email' => $request->pic_email, 'idperusahaankonfirmasi' => $idperusahaankonfirmasi, 'kode' => $kode);
                    Mail::send('templateemail.buatperusahaan', $data, function($message) use ($data) {
                        $message->to($data['email'])->subject('Konfirmasi Perusahaan Baru');
                        $message->from('no-reply@smartpresence.id','Smart Presence');
                    });

                    Utils::insertLogUser('tambah perushaaan "'.$request->nama.'"');

                    $db->commit();
                    if ($request->dari == 'index') {
                        return redirect('/')->with('pesansukses', $idperusahaan.'|'.trans('all.perusahaanberhasildibuatdandalamprosesaktivasi'));
                    } else {
                        return redirect('perusahaan')->with('message', trans('all.perusahaanberhasildibuatdandalamprosesaktivasi'));
                    }
                } else {
                    $db->rollBack();
                    if ($request->dari == 'index') {
                        return redirect('/')->with('message', trans('all.terjadigangguan'));
                    } else {
                        return redirect('perusahaan')->with('message', trans('all.terjadigangguan'));
                    }
                }
            } catch (\Exception $e) {
                $db->rollBack();
                // return $e->getmessage();
                // return redirect('perusahaan')->with('message', trans('all.terjadigangguan'));
                // return redirect('perusahaan')->with('message', $e->getmessage());
                if ($request->dari == 'index') {
                    return redirect('/')->with('message', trans('all.terjadigangguan'));
//                    return redirect('/')->with('message', $e->getmessage());
                } else {
                    return redirect('perusahaan')->with('message', trans('all.terjadigangguan'));
//                    return redirect('perusahaan')->with('message', $e->getmessage());
                }
            }
//        }else{
//            return redirect('perusahaan/create')->with('message', trans('all.captchatidakvalid'));
//        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('perusahaan','um')){

            $perusahaan = Perusahaan::find($id);
            $pdo = DB::getPdo();
            $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idperusahaan', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $path = $row['folderroot'].'/logo_perusahaan_thumb';
            $pathlogoemployeeapp = $row['folderroot'].'/logo_employee_app_thumb';
            $pathlogodatacaptureapp = $row['folderroot'].'/logo_datacapture_app_thumb';
            $adafoto = false;
            if (file_exists($path)) {
                $adafoto = true;
            }

            $adalogoemployee = false;
            if (file_exists($pathlogoemployeeapp)) {
                $adalogoemployee = true;
            }

            $adalogodatacapture = false;
            if (file_exists($pathlogodatacaptureapp)) {
                $adalogodatacapture = true;
            }
            
            if (!$perusahaan) {
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah perusahaan');
            return view('perusahaan/edit', ['perusahaan' => $perusahaan, 'adafoto' => $adafoto, 'adalogoemployee' => $adalogoemployee, 'adalogodatacapture' => $adalogodatacapture, 'menu' => 'perusahaan']);
        } else {
            return redirect('/');
        }
    }
    
    public function update(Request $request, $id)
    {
        $pdo = DB::getPdo();
        //cek apakah kembar?
        $sql = 'SELECT id FROM perusahaan WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount()>0) {

            $perusahaan = Perusahaan::find($id);
            $perusahaan->nama = $request->nama;
            $perusahaan->status = $request->status;
            $perusahaan->pic_nama = $request->pic_nama;
            $perusahaan->pic_alamat = $request->pic_alamat;
            $perusahaan->pic_notelp = $request->pic_notelp;
            $perusahaan->pic_email = $request->pic_email;
            $perusahaan->save();

            $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idperusahaan', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $idperusahaan = $id;
            if ($request->hasFile('foto')) {

                $path = $row['folderroot'].'/logo_perusahaan';

                $fotoprofil = $request->file('foto');
                //cek apakah format jpeg?
//                if ($fotoprofil->getMimeType() == 'image/jpeg' || $fotoprofil->getMimeType() == 'image/png' || $fotoprofil->getMimeType() == 'image/bmp') {
                if ($fotoprofil->getMimeType() == 'image/jpeg' || $fotoprofil->getMimeType() == 'image/png') {
                    Utils::makeThumbnail($fotoprofil, $path);
                    Utils::saveUploadImage($fotoprofil, $path);

                    $checksum = md5_file($path);

                    $sql = 'UPDATE perusahaan set checksum_img = :checksum WHERE id = :idperusahaan';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':checksum', $checksum);
                    $stmt->bindValue(':idperusahaan', $idperusahaan);
                    $stmt->execute();

                    Session::set('fotoperusahaan_perusahaan', 'ada');
                } else {
                    return redirect('perusahaan/'.$id.'/edit')->with('message', trans('all.formatgambartidakvalid'));
                }
            }

            if ($request->hasFile('logoemployee')) {
                $logoemployee = $request->file('logoemployee');
                $pathlogoemployee = $row['folderroot'] . '/logo_employee_app';
                //cek apakah format jpeg?
                if ($logoemployee->getMimeType() == 'image/jpeg' || $logoemployee->getMimeType() == 'image/png') {
                    Utils::makeThumbnail($logoemployee, $pathlogoemployee);
                    Utils::saveUploadImage($logoemployee, $pathlogoemployee);
                } else {
                    return redirect('perusahaan/' . $id . '/edit')->with('message', trans('all.formatgambartidakvalid'));
                }
            }

            if ($request->hasFile('logodatacapture')) {
                $logodatacapture = $request->file('logodatacapture');
                $pathlogodatacapture = $row['folderroot'] . '/logo_datacapture_app';
                //cek apakah format jpeg?
                if ($logodatacapture->getMimeType() == 'image/jpeg' || $logodatacapture->getMimeType() == 'image/png') {
                    Utils::makeThumbnail($logodatacapture, $pathlogodatacapture);
                    Utils::saveUploadImage($logodatacapture, $pathlogodatacapture);
                } else {
                    return redirect('perusahaan/' . $id . '/edit')->with('message', trans('all.formatgambartidakvalid'));
                }
            }

            return redirect('perusahaan')->with('message', trans('all.databerhasildiubah'));
        }else{
            return Utils::redirectForm(trans('all.datatidakditemukan'));
        }
    }
    
    public function destroy($id)
    {
        if(Utils::cekHakakses('perusahaan','hm')){
            $pdo = DB::getPdo();
            //pastikan perusahaan ada
            $sql = 'SELECT id FROM perusahaan WHERE id = :idperusahaan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idperusahaan', $id);
            $stmt->execute();
            if ($stmt->rowCount() != 0) {
                $sql = 'UPDATE perusahaan SET status = "t" WHERE id = :idperusahaan';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idperusahaan', $id);
                $stmt->execute();

                if($id == Session::get('conf_webperusahaan')){
                    Session::forget('conf_webperusahaan');
                    Session::forget('hakakses_perusahaan');
                    Session::forget('pencarian_perusahaan');
                    Session::forget('lappertanggal_atribut');

                    return redirect('/');
                }
                Utils::insertLogUser('hapus perusahaan "'.Utils::getDataWhere($pdo,'perusahaan','nama','id',$id).'"');
                return redirect('perusahaan')->with('message', trans('all.databerhasildihapus'));
            } else {
                return redirect('perusahaan')->with('message', trans('all.terjadigangguan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function excel()
    {
        $pdo = DB::getPdo();
        $objPHPExcel = new PHPExcel();

        Utils::setPropertiesExcel($objPHPExcel,trans('all.perusahaan'));

        //set value kolom
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A7', trans('all.nama'))
                    ->setCellValue('B7', trans('all.kode'))
                    ->setCellValue('C7', trans('all.status'));

        //set css kolom
        $styleArray = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ),
            ),
        );

        $where = ' AND 1=2';
        if(Session::has('iduser_perusahaan')){
            $where = ' AND pn.iduser ='.Session::get('iduser_perusahaan');
        }
        $sql = 'SELECT 
                    p.id,
                    p.nama,
                    p.kode,
                    IF(p.status="a","'.trans("all.aktif").'","'.trans("all.tidakaktif").'") as status
                FROM 
                    pengelola pn,
                    perusahaan p
                WHERE 
                    pn.idperusahaan=p.id
                    '.$where.'
                ORDER BY
                    p.nama ASC              
                ';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $i = 8;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['nama']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['kode']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['status']);

            for($j=1;$j<=3;$j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf.$i)->applyFromArray($styleArray);
            }
            
            $i++;
        }

        $arrWidth= array('',40,30,15);
        for($j=1;$j<=3;$j++) {
            $huruf = Utils::angkaToHuruf($j);
            $objPHPExcel->getActiveSheet()->getStyle($huruf.'7')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($huruf.'7')->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
        }

        $heightgambar = 99;
        $widthgambar = 99;

        // style garis
        $end_i = $i-1;
        $objPHPExcel->getActiveSheet()->getStyle('A1:C'.$end_i)->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A1:C5')->applyFromArray($styleArray);

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT * FROM parameterekspor';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rowPE['footerkiri_1_teks'] == '' AND $rowPE['footerkiri_2_teks'] == '' AND $rowPE['footerkiri_3_teks'] == '' AND $rowPE['footerkiri_5_teks'] == '' AND $rowPE['footerkiri_6_teks'] == '' AND $rowPE['footertengah_1_teks'] == '' AND $rowPE['footertengah_2_teks'] == '' AND $rowPE['footertengah_3_teks'] == '' AND $rowPE['footertengah_5_teks'] == '' AND $rowPE['footertengah_6_teks'] == '' AND $rowPE['footerkanan_1_teks'] == '' AND $rowPE['footerkanan_2_teks'] == '' AND $rowPE['footerkanan_3_teks'] == '' AND $rowPE['footerkanan_5_teks'] == '' AND $rowPE['footerkanan_6_teks'] == '') {
        } else {
            $l = $i + 1;
            Utils::footerExcel($objPHPExcel,'kiri','A','A',$l,$rowPE);
            Utils::footerExcel($objPHPExcel,'tengah','B','B',$l,$rowPE);
            Utils::footerExcel($objPHPExcel,'kanan','C','C',$l,$rowPE);
        }

        // password
        Utils::passwordExcel($objPHPExcel);
        Utils::header5baris($objPHPExcel,'C',$rowPE);
        if ($rowPE['logokiri'] != "") {
            $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokiri'];
            Utils::logoExcel('kiri',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'A1');
        }

        if ($rowPE['logokanan'] != "") {
            $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokanan'];
            Utils::logoExcel('kanan',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'C1');
        }

        Utils::insertLogUser('Ekspor perusahaan');
        Utils::setFileNameExcel(trans('all.perusahaan'));
        $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $writer->save('php://output');
    }

    public function konfirmasiPerusahaanBaru($idperusahaankonfirmasi, $kode)
    {
        $pdo = DB::getPdo();
        try {
            $pdo->beginTransaction();
            $sql = 'SELECT idperusahaan,status FROM perusahaan_konfirmasi WHERE id=:id AND kode = :kode FOR UPDATE';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $idperusahaankonfirmasi);
            $stmt->bindValue(':kode', $kode);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $idperusahaan = $row['idperusahaan'];
                if($row['status'] == 't') {

                    $sql2 = 'UPDATE perusahaan_konfirmasi set status = "v" WHERE id = :idperusahaankonfirmasi AND kode = :kode';
                    $stmt2 = $pdo->prepare($sql2);
                    $stmt2->bindValue(':idperusahaankonfirmasi', $idperusahaankonfirmasi);
                    $stmt2->bindValue(':kode', $kode);
                    $stmt2->execute();

                    $sql3 = 'UPDATE `perusahaan` SET status = "w" WHERE id = :idperusahaan';
                    $stmt3 = $pdo->prepare($sql3);
                    $stmt3->bindValue(':idperusahaan', $idperusahaan);
                    $stmt3->execute();

                    Utils::insertLogUser('konfirmasi perusahaan baru');
                    $msg = "ok";

                }else if($row['status'] == "v"){
                    $msg = trans('all.andasudahmelakukankonfirmasi');
                }else{
                    $msg = trans('all.terjadigangguan');
                }
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            $msg = trans('all.terjadigangguan');
        }
        return view('perusahaan/sukses', ['message' => $msg]);
    }
}