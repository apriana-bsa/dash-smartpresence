<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use App\Canopus;
use App\Pegawai;
use App\Utils;
use Yajra\Datatables\Datatables;

//tester
Route::get('mockdata/{search?}', function($search='') {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        exit(0);
    }
    $pdo = DB::getPdo();
    $where = '';
    if($search != ''){
        $where = ' AND nama LIKE :search';
    }
    $sql = 'SELECT id as `value`,nama as label FROM `user` WHERE 1=1 '.$where.' ORDER BY label ASC, id ASC LIMIT 10';
    $stmt = $pdo->prepare($sql);
    if($search != '') {
        $stmt->bindValue(':search', '%'.str_replace('+',' ', $search).'%');
    }
    $stmt->execute();
    $data = array();
    if($stmt->rowCount() > 0){
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $response = array();
    $response['status'] = 'OK';
    $response['data'] = $data;
    return $response;
//    return '{"status":"OK", "data":[
//        { "value": "chocolate", "label": "Chocolate" },
//        { "value": "strawberry", "label": "Strawberry" },
//        { "value": "vanilla", "label": "Vanilla" },
//        { "value": "apple", "label": "Apple" }
//    ]}';
//    return '{"status":"OK", "data":[]}';
});

Route::get('coba', function(){
    return (Request::get('page') == '' ? 'kosong' : Request::get('page')).' '.Request::get('search');
});

Route::get('mockdataatribut/{page?}/{limit?}/{sort?}/{sortDirection?}/{search?}', function($page=1, $limit = 10, $sort = 'nama', $sortDirection = 'ASC', $search = '') {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        exit(0);
    }
    $pdo = DB::getPdo();
    $resp = [];
    $resp['status'] = 'OK';
    $resp['data'] = [];
    $resp['data_total'] = 0;
    $resp['data_total_filter'] = 0;
    $startFrom = ($page-1)*$limit.', ';
    $where = '';
    if($search != ''){
        $where .= ' AND (nama LIKE :search OR email LIKE :search)';
    }
    $sql = 'SELECT id,nama,email FROM `user` WHERE 1=1 '.$where.' ORDER BY '.$sort.' '.$sortDirection.' LIMIT '.$startFrom.$limit;
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', '%'.$search.'%');
    $stmt->execute();
    $resp['data_total_filter'] = $stmt->rowCount();
    if($stmt->rowCount() > 0){
        $resp['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // get total data
    $sql = 'SELECT id FROM user';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resp['data_total'] = $stmt->rowCount();

    return $resp;
});

Route::get('mocklogin', function() {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        exit(0);
    }
//    session_set_cookie_params ( int 6000 [, string '' [, string $domain [, bool $secure = false [, bool $httponly = false ]]]] );
//    session_set_cookie_params ( 10, '/','localhost', true, true);
//    setcookie('tescookies','tes akses token baru');
//    echo "cookies ";
//    setcookie('access_token_new','tes akses token baru',time()+3600,'/','localhost',false,true);
//    setcookie('tescookies','tes akses token baru',0,'/','localhost',false,false);
//    return '{"status":"OK", "access_token":"'.$_COOKIE['access_token_new'].'", "refresh_token":"refresh token"}';
    return '{"status":"OK", "access_token":"tes akses token", "refresh_token":"refresh token"}';
//    return '{"status":"Error", "errormsg":"tes error msg"}';
});

Route::get('mocekdatacases', function() {
    return '{
        "id": "70d8df48-343c-4ab6-8b37-3baac367fdcb",
        "patient_id": "b6da07c3-5e2c-4722-ad83-ffbb9cda1366",
        "symptom": "{\"batuk\":1}",
        "comorbid": "{}",
        "onset_date": "2020-09-29",
        "case_criteria": "Selesai Isolasi",
        "isolation_date": null,
        "isolation_end_date": null,
        "isolation_type": "Self Isolation",
        "icu": null,
        "intubate": null,
        "emco": null,
        "patient_status": "meninggal",
        "referral_hospital": null,
        "health_facility_reporter": "PKM Cilincing",
        "travel_history": "Tidak",
        "travel_date": null,
        "arrived_date": null,
        "contact_history": "Tidak Ada Kontak",
        "contact_date": null,
        "close_contact": "-",
        "animal_market_contact": null,
        "visiting_health_facility": "Tidak",
        "medical_staff": "Tidak",
        "swab": "Ya",
        "swab_date": "2020-10-01 00:00:00",
        "rapid_test_id": null,
        "destination_lab": null,
        "lab_examination": null,
        "lung_x_ray": null,
        "lung_x_ray_result": null,
        "leukocyte_examination": "Tidak",
        "leukocyte_examination_result": "{\"lekosit\":\"\",\"limposit\":\"\",\"trombosit\":\"\"}",
        "lab_examinator_name": null,
        "lab_examination_date": null,
        "note": null,
        "lab_result": null,
        "official_lab_result": null,
        "created_at": "2020-10-16 15:22:36",
        "latest": true,
        "nar_id_sampel": "0",
        "destination_lab_name": null
    }';
});
Route::get('mockdatapasien', function(){
//    $tgl = \App\Http\Controllers\NotifikasiCanopusController::getAktifSampai("2020-11-05", "2020-11-16", 1);
//    return $tgl->format('Y-m-d');
    return '{
                "id": "b6da07c3-5e2c-4722-ad83-ffbb9cda1366",
                "nik": "3172045907020006",
                "name": "REGINA JULIANI",
                "dob": "2002-07-19",
                "pob": null,
                "marital_status": "2",
                "religion": "1",
                "job": null,
                "address": "Bloks no 6 rt 02/16 semper barat",
                "rt": "02",
                "rw": "16",
                "village_id": "3175060006",
                "district_id": "3175060",
                "regency_id": "3175",
                "sex": "P",
                "phone_number": "08976566111",
                "citizenship": "WNI",
                "residence_address": "Bloks no 6 rt 02/16 semper barat",
                "country": "",
                "village": {
                    "id": "3175060006",
                    "district_id": "3175060",
                    "name": "SEMPER BARAT",
                    "created_at": null,
                    "updated_at": null
                },
                "district": {
                    "id": "3175060",
                    "regency_id": "3175",
                    "name": "CILINCING",
                    "created_at": null,
                    "updated_at": null
                },
                "regency": {
                    "id": "3175",
                    "province_id": "31",
                    "name": "KOTA JAKARTA UTARA",
                    "created_at": null,
                    "updated_at": null
                }
            }';
//    $a = '../storage/pot training jkt - FS.xlsx';
//    return substr($a, 11);
//    return Utils::min2hhmm(90270).' '.Utils::sec2hhmm(5416200);
//    return str_replace('/','','halo / bossku');
//    return Utils::min2hhmm(89820).' '.Utils::sec2hhmm(5389200);
//    if('2020-01-02' == '2020-01-01'){
//        return 'sama';
//    }
//    return 'beda';
//    $pdo = DB::getPdo();
//    $where = ' nama LIKE :iduser';
//    $sql = 'SELECT * from `user` WHERE '.$where;
//    $stmt = $pdo->prepare($sql);
//    $stmt->bindValue(':iduser', '%hoela%');
//    $stmt->execute();
//    return $stmt->fetchAll(PDO::FETCH_OBJ);
//    $inject = ' AND (SELECT 2350 FROM (SELECT(SLEEP(5)))lxgK) AND "sXaE"="​sXaE​';
//    $inject = '2020-01-01 01:00:00';
//    $inject = '04/10/2020 09:31:00';
//    $inject = '11/01/2011';
////    $inject = '00:00:00';
////    $inject = '2:00:09';
//    $inject = '2020-08-01';
//    return Utils::cekDateTime($inject) ? 'benar' : 'salah '.$inject;
//    return Utils::convertYmd2Dmy('2020-08-01');
//    return Utils::convertDmy2Ymd('01/10/2020');
//    $urlimage = 'https://dash.smartpresence.id/fotonormal/perusahaan/10950';
////    $urlimage = 'http://localhost:5566/fotonormal/perusahaan/10950';
////    $urlimage = 'https://upload.wikimedia.org/wikipedia/en/4/47/Iron_Man_%28circa_2018%29.png';
////    return exif_imagetype($urlimage);
//    return exif_imagetype($urlimage);
//    if(exif_imagetype($urlimage) == 2){
//        // JPEG bernilai 2
//        $format = 'JPEG';
//    }else{
//        // PNG bernilai 3
//        $format = 'PNG';
//    }
//    return $format;

//    $pdo = DB::getPdo();
//    $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
//    $stmt = $pdo->prepare($sql);
//    $stmt->bindValue(':idperusahaan', 10950);
//    $stmt->execute();
//    $row = $stmt->fetch(PDO::FETCH_ASSOC);
//    $path = $row['folderroot'] . '/logo_perusahaan';
//    $raw = Utils::decrypt($path);
//    return exif_imagetype($raw);
//    $path_temp = $row['folderroot'] . '/temp/';
//    if (!file_exists($path_temp)) {
//        mkdir($path_temp, 0777, true);
//    }
//    $img = imagejpeg($raw, 'temp_logo_perusahaan');
//    move_uploaded_file($img,$path_temp.'/_temp_logo_perusahaan');
//    return $raw;
//    return Utils::convertYmd2Dmy('2020-01-10');
    abort(404);
});

Route::get('tester/{tanggal?}', function($tanggal = ''){
    $arr = ['','A','B','C','D'];
//    $arr = [];
    $h = 6;
    for($i=0;$i<count($arr);$i++){
        echo $h.'<br>';
        $h++;
    }
    return $h.'bossku';
//    $tgl = '2020-10-01,2020-10-02,2020-10-03,2020-12-04,2020-12-10,2021-01-09';
//    return Utils::KumpulanTanggal($tgl);
//    return Hash::make('1234');
//    $datetime = Utils::tanggalCantik(date('Y-m-d H:i:s'));
//    return Utils::sec2hhmm(1410980);
//    return Utils::sec2hhmm(556200);
//    return Utils::sec2hhmm(39401);
//    return Utils::sec2hhmm(14522);
//    return Utils::sec2hhmm(183341);
//    return Utils::sec2hhmm(996114);
//    return Utils::sec2hhmm(513500);
//    return Utils::sec2hhmm(514430);
//    return Utils::sec2hhmm(482614);
//    return Utils::sec2hhmm(482614);
//    return Utils::sec2hhmm(482614);
//    $path ="/some/path/to/myfilename.html";
//    $path = Session::get('folderroot_perusahaan').'/parameterekspor/kiri';
//    $raw = Utils::decrypt($path);
//    return $path->getMimeType();
////    return File::mimeType(file_get_contents($path));
//    return response($raw)->header('Content-Type', 'image/jpeg');
//    return Response::make(File::get($path), 200)->header("Content-Type", File::mimeType($path));
//    return response($raw)->header('Content-Type', File::mimeType($path));
//    return File::mimeType($raw);
//    return pathinfo($raw, PATHINFO_EXTENSION);
//    $path = Session::get('folderroot_perusahaan').'/parameterekspor/kiri';
//    $format = '';
//    if(file_exists($path)) {
////        $raw = Utils::decrypt($path);
////        $result = response($raw)->header('Content-Type', 'image/jpeg');
////        return exif_imagetype($path);
//
////        if (exif_imagetype($path) == 2) {
////            // JPEG bernilai 2
////            $format = 'JPEG';
////        } else {
////            // PNG bernilai 3
////            $format = 'PNG';
////        }
//    }
//    return $format;
//    $a = '12345';
//    return strlen($a);
//    return redirect('https://smartpresence.id/thankyou/');
//    return is_numeric('23ter') ? 'ya' : 'tidak';
//    return Utils::cekHariLibur('2020-06-01',"") ? 'libur' : 'tidaklibur';
//    Artisan::call('cache:clear');
//    return "Cache is cleared";
//    return exif_imagetype('http://127.0.0.1/foto/perusahaan/10950');
//    return file_get_contents('../../img_v3/no-image.png');
//    return exif_imagetype('http://localhost:55555/foto/perusahaan/11741');
//    $path_nopic = $_SERVER['DOCUMENT_ROOT'].'/'.config('consts.FOLDER_IMG').'/perusahaan_nopic.png';
//    return exif_imagetype($path_nopic);
//    $url = 'https://dash.smartpresence.id/foto/perusahaan/10950';
//    $type = get_headers($url, 1)["Content-Type"];
//    return $type;
//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $sql = 'CALL generateharilibur("2020")';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//    $sql = 'DESC _harilibur';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//    $kolom = $stmt->fetchAll(PDO::FETCH_COLUMN);
//    array_splice($kolom, 0, 1);
//    array_splice($kolom, 1, 1);
//    return $kolom;
//    return substr('2020-01-01', 0, 4);
//    return round(10.2362390482357820893,2);
//    return  10*12.5*100/100;
    abort(404);
//    $menu = 'logabsen';
//    return Utils::cekHakakses($menu,'trls') ? 'ada' : 'tidak';
//    return substr(' pegawai like "%tester%" OR', 0, -2);
//    $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
//    if ($batasan != '') {
//        $batasan = ' AND id IN ' . $batasan;
//    }
//    $sql = 'CALL getpegawailengkap_controller(@_atributpenting, @_atributvariablepenting, "' . str_replace('"', "'", $batasan) . '")';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//
//    $sql = 'SELECT @_atributpenting as atributpenting, @_atributvariablepenting as atributvariablepenting';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//    $row = $stmt->fetch(PDO::FETCH_ASSOC);
////    $atributpenting = $row['atributpenting'] != '' ? substr($row['atributpenting'],1) : '';
////    $atributvariablepenting = $row['atributvariablepenting'] != '' ? substr($row['atributvariablepenting'], 0, -1) : '';
//    $atributpenting = $row['atributpenting'];
//    $atributvariablepenting = $row['atributvariablepenting'];
//    return $atributpenting.'<br>'.$atributvariablepenting;

//    $array = array('','nama','alamat','nomorhp');
////    array_push($array);
//    $join = array('nip','status');
//    $join2 = array('waktu masuk','waktu pulang');
//    $join3 = 'umur';
//    $join4 = 'status';
//    array_push($array,'tester',$join3);
//    array_push($array,$join4);
////    print_r($array);
//    return $array;
//    $atributvariable = Utils::getAtributVariable();
//    $hasil = [];
//    foreach($atributvariable as $key){
//        $hasil[] = $key->nama;
//    }
//    return implode(',',$hasil);
//    $arr_atributvariable = Utils::getAtributVariable();
//    $arr_namaatributvariable = [];
//    foreach($arr_atributvariable as $key){
//        $arr_namaatributvariable[] = $key->nama;
//    }
//    return implode('<br>',$arr_namaatributvariable).'<br><br>'.Utils::getAtributVariableQuery('p.id').'<br>';
//    abort(404);
//    return substr('a',0,-1);
//    $string = ',nama,alamat,notelp';
//    return explode(',',$string);
//    $data = array('nama' => 'paijo 20190223', 'kode' => '112', 'nomorhp' => '082257038541', 'email' => 'fathoela@gmail.com', 'iduserkonfirmasi' => '1');
//    Mail::send('templateemail.daftar', $data, function($message) use ($data) {
//        $message->to($data['email'])->subject('Register');
//        $message->from('no-reply@smartpresence.id','Smart Presence');
//    });
//    return;
//    $pdo = DB::getPdo();
//    $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = 10950';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//    $row = $stmt->fetch(PDO::FETCH_ASSOC);
//    $path = $row['folderroot'].'/logo_perusahaan';
//    $raw = Utils::decrypt($path);
//    $format = exif_imagetype($path) == 2 ? 'jpeg' : 'png';
////    return response($raw)->header('Content-Type', 'image/'.$format);
//    return $format;
//    abort(404);
//    $a = [{"idgroup":"1","angkahuruf":3},{"idgroup":"1","angkahuruf":4},{"idgroup":"1","angkahuruf":5}];
//    $a = ['a','b','a','c','e','a'];
//    return array_count_values($a);
//    $a = false;
//    return $a ? 'benar' : 'salah';
    // Create new PHPExcel object
//    $objPHPExcel = new PHPExcel();
//    $path = Session::get('folderroot_perusahaan') . '/payroll/slipgaji/1.xls';
//    $objPHPExcel = PHPExcel_IOFactory::load($path);
//
//    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
//    $objPHPExcel->setActiveSheetIndex(0);
//
////    return dirname(__FILE__).'/../../public/template.xlsx';
//    // tcpdf folder
//    $rendererName = PHPExcel_Settings::PDF_RENDERER_TCPDF;
//    $rendererLibraryPath = dirname(__FILE__).'/../../public/tcpdf';
//
//    //setting column heading
////    $objPHPExcel->getActiveSheet()->setCellValue('A1',"Name");
////    $objPHPExcel->getActiveSheet()->setCellValue('B1',"Phone");
//
//    // Rename sheet
//    $objPHPExcel->getActiveSheet()->setTitle('Simple');
//
//
//    if (!PHPExcel_Settings::setPdfRenderer(
//        $rendererName,
//        $rendererLibraryPath
//    )) {
//        die(
//            'NOTICE: Please set the $rendererName and $rendererLibraryPath values' .
//            '<br />' .
//            'at the top of this script as appropriate for your directory structure'
//        );
//    }
//
//// Redirect output to a client’s web browser (PDF)
//    header('Content-Type: application/pdf');
//    header('Content-Disposition: attachment;filename="01simple.pdf"');
//    header('Cache-Control: max-age=0');
//
//    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
//    $objWriter->setIncludeCharts(TRUE);
//    $objWriter->save('php://output');
//    exit;
//    $objPHPExcel = new PHPExcel();
//
//    Utils::setPropertiesExcel($objPHPExcel,'tes');
//
//    // set active sheet
//    $objPHPExcel->setActiveSheetIndex(0);
//
//    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'tes1');
//    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'tes2');
//    $totalhari = 5;
//    $objPHPExcel->getActiveSheet()->insertNewColumnBefore('B', $totalhari);
//    for($i=0;$i<$totalhari;$i++){
//        $objPHPExcel->getActiveSheet()->setCellValue('B1', 'insert');
//    }
//    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'tes3');
//    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'tes4');
//    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'tes5');
//
//    Utils::insertLogUser('Ekspor Laporan Custom');
//    Utils::setFileNameExcel(trans('all.laporancustom'));
//    $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
//    $writer->save('php://output');
//    return 'ok';
//    return '';
//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $datakomponenmaster = Utils::getData($pdo,'payroll_komponen_master','id');
//    $komponenmaster = '';
//    if($datakomponenmaster != ''){
//        foreach($datakomponenmaster as $key){
//            $komponenmaster .= $key->id.' ';
//            if($key->id == 13){
//                break;
//            }
//        }
//    }
//    return $komponenmaster;
//    return Utils::getTotalHariFrom2Date('2019-07-10','2019-07-03');
//    return Utils::cekJumlahLibur('2019-07-03','2019-07-10', '1170');
//    $a = true;
//    if(!$a){ return 'ok'; }else{ return 'not ok'; }
//    return date('w', strtotime(date('Y-m-d',strtotime('2019-07-03')) . ' +0 day')) + 1;
//    return Utils::getIndexLembur(Utils::sec2prettyCustom(26439,false,false,'menit'),3,'y', '2019-06-05',2176);
//    $tanggalawal = '29/05/2019';
//    $tanggalakhir = '05/06/2019';
//    return Utils::getTotalHariFrom2Date(Utils::convertDmy2Ymd($tanggalakhir),Utils::convertDmy2Ymd($tanggalawal));
//    28800,4,y,2291
//    return Utils::getIndexJamKerja(Utils::sec2prettyCustom('28800',false,false,'menit'),4,'y', '2019-06-06' ,2291);
//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $index = '600=1';
//    $menit= 54570;
//    $hasil = 0;
//    $jenishari = 'biasa';
//    $dataidxatribut = Utils::getDataCustomWhere($pdo,'indexjamkerja_atribut','IFNULL(GROUP_CONCAT(idatributnilai),"")','idindexjamkerja=5');
//    if($dataidxatribut != ''){
//        $datapegawaiatribut = Utils::getDataCustomWhere($pdo,'pegawaiatribut','id','idpegawai=3162 AND idatributnilai IN('.$dataidxatribut.')');
//        if($datapegawaiatribut != ''){
//            $index_ex = explode(';', $index);
//            for($i=0;$i<count($index_ex);$i++){
//                $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                if($menit > $index_ex2[0]){
//                    $hasil = $index_ex2[1];
//                }
//            }
//        }
//    }else{
//        $index_ex = explode(';', $index);
//        for($i=0;$i<count($index_ex);$i++){
//            $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//            if($menit > $index_ex2[0]){
//                $hasil = $index_ex2[1];
//            }
//        }
//    }
//    return $hasil;
//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $index = '600=1';
//    $menit= 54570;
//    $harilibur = 't';
//    $hasil = 0;
//    $jenishari = 'biasa';
//    $idpegawai = 3162;
//    $idxhari = 5;
//    $idjamkerja = 5;
//    //khusus hari libur, karena hari libur adalah hirarki tertinggi
//    if($jenishari == 'harilibur' && $index != '' && $harilibur == 'y'){
//        if($idpegawai != ''){
//            //data indexjamkerja atribut
//            $dataidxatribut = Utils::getDataCustomWhere($pdo,'indexjamkerja_atribut','IFNULL(GROUP_CONCAT(idatributnilai),"")','idindexjamkerja='.$idjamkerja);
//            if($dataidxatribut != ''){
//                $datapegawaiatribut = Utils::getDataCustomWhere($pdo,'pegawaiatribut','id','idpegawai='.$idpegawai.' AND idatributnilai IN('.$dataidxatribut.')');
//                if($datapegawaiatribut != ''){
//                    $index_ex = explode(';', $index);
//                    for($i=0;$i<count($index_ex);$i++){
//                        $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                        if($menit > $index_ex2[0]){
//                            $hasil = $index_ex2[1];
//                        }
//                    }
//                }
//            }else{
//                $index_ex = explode(';', $index);
//                for($i=0;$i<count($index_ex);$i++){
//                    $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                    if($menit > $index_ex2[0]){
//                        $hasil = $index_ex2[1];
//                    }
//                }
//            }
//        }else{
//            $index_ex = explode(';', $index);
//            for($i=0;$i<count($index_ex);$i++){
//                $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                if($menit > $index_ex2[0]){
//                    $hasil = $index_ex2[1];
//                }
//            }
//        }
//    } else if($idxhari != 0  && $harilibur == 't'){ // 1 - 6(senin sampai jum'at)
//        if($jenishari == 'biasa' && $index != ''){
//            if($idpegawai != ''){
//                //data indexjamkerja atribut
//                $dataidxatribut = Utils::getDataCustomWhere($pdo,'indexjamkerja_atribut','IFNULL(GROUP_CONCAT(idatributnilai),"")','idindexjamkerja='.$idjamkerja);
//                if($dataidxatribut != ''){
//                    $datapegawaiatribut = Utils::getDataCustomWhere($pdo,'pegawaiatribut','id','idpegawai='.$idpegawai.' AND idatributnilai IN('.$dataidxatribut.')');
//                    if($datapegawaiatribut != ''){
//                        $index_ex = explode(';', $index);
//                        for($i=0;$i<count($index_ex);$i++){
//                            $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                            if($menit > $index_ex2[0]){
//                                $hasil = $index_ex2[1];
//                            }
//                        }
//                    }
//                }else{
//                    $index_ex = explode(';', $index);
//                    for($i=0;$i<count($index_ex);$i++){
//                        $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                        if($menit > $index_ex2[0]){
//                            $hasil = $index_ex2[1];
//                        }
//                    }
//                }
//            }else{
//                $index_ex = explode(';', $index);
//                for($i=0;$i<count($index_ex);$i++){
//                    $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                    if($menit > $index_ex2[0]){
//                        $hasil = $index_ex2[1];
//                    }
//                }
//            }
//        }
//    }else{
//        //hari minggu
//        if($jenishari == 'hariminggu' && $index != ''  && $harilibur == 't'){
//            if($idpegawai != ''){
//                //data indexjamkerja atribut
//                $dataidxatribut = Utils::getDataCustomWhere($pdo,'indexjamkerja_atribut','IFNULL(GROUP_CONCAT(idatributnilai),"")','idindexjamkerja='.$idjamkerja);
//                if($dataidxatribut != ''){
//                    $datapegawaiatribut = Utils::getDataCustomWhere($pdo,'pegawaiatribut','id','idpegawai='.$idpegawai.' AND idatributnilai IN('.$dataidxatribut.')');
//                    if($datapegawaiatribut != ''){
//                        $index_ex = explode(';', $index);
//                        for($i=0;$i<count($index_ex);$i++){
//                            $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                            if($menit > $index_ex2[0]){
//                                $hasil = $index_ex2[1];
//                            }
//                        }
//                    }
//                }else{
//                    $index_ex = explode(';', $index);
//                    for($i=0;$i<count($index_ex);$i++){
//                        $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                        if($menit > $index_ex2[0]){
//                            $hasil = $index_ex2[1];
//                        }
//                    }
//                }
//            }else{
//                $index_ex = explode(';', $index);
//                for($i=0;$i<count($index_ex);$i++){
//                    $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                    if($menit > $index_ex2[0]){
//                        $hasil = $index_ex2[1];
//                    }
//                }
//            }
//        }
//    }
//    return $hasil;

//    return date('ymj', strtotime('20190503'));
//    return Utils::tanggalCantik('20190503');
//    return substr('20190510', -2);
//    $index = array();
//    $index[0] = array();
//    $index[0]['lebihdari'] = 0;
//    $index[0]['indexlembur'] = 2;
//    $index[1] = array();
//    $index[1]['lebihdari'] = 30;
//    $index[1]['indexlembur'] = 3;
//    $index[2] = array();
//    $index[2]['lebihdari'] = 60;
//    $index[2]['indexlembur'] = 4;
//
//    $lamalembur = 10;
//
//    $indexlembur = 0;
//    for($i=0;$i<count($index);$i++) {
//        if ($lamalembur>0) {
//            if ($i < count($index) - 1) {
//                $blok = $index[$i + 1]['lebihdari'] - $index[$i]['lebihdari'];
//                if ($lamalembur>$blok) {
//                    $durasi = $blok;
//                }
//                else {
//                    $durasi = $lamalembur;
//                }
//                $lamalembur = $lamalembur - $durasi;
//                $indexlembur = $indexlembur + ($index[$i]['indexlembur'] * ($durasi / 60));
//            } else {
//                //jika yang paling terakhir
//                $indexlembur = $indexlembur + ($index[$i]['indexlembur'] * ($lamalembur / 60));
//            }
//        }
//    }
//
//    echo round($indexlembur, 1);

//    return Utils::sec2prettyCustom('22440',false,false,'menit');

//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $sql = 'SELECT id,nama,pin from pegawai limit 1';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//    $row = $stmt->fetch(PDO::FETCH_OBJ);
//    $indexlembur = 0;
//    $lamalembur = 374;
//    $sql = 'SELECT `index` FROM indexlembur WHERE id = 3';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//    $row = $stmt->fetch(PDO::FETCH_ASSOC);
//    $index = array();
//    if($row['index'] != '') {
//        $ex_index = explode(';', $row['index']);
//        for ($i = 0; $i < count($ex_index); $i++) {
//            $ex_value = explode('=', $ex_index[$i]); // 0 brp menit, 1 index nya
//            $index[$i] = array();
//            $index[$i]['lebihdari'] = $ex_value[0];
//            $index[$i]['indexlembur'] = $ex_value[1];
//        }
//
//        for ($i = 0; $i < count($index); $i++) {
//            if ($lamalembur > 0) {
//                if ($i < count($index) - 1) {
//                    $blok = $index[$i + 1]['lebihdari'] - $index[$i]['lebihdari'];
//                    if ($lamalembur > $blok) {
//                        $durasi = $blok;
//                    } else {
//                        $durasi = $lamalembur;
//                    }
//                    $lamalembur = $lamalembur - $durasi;
//                    $indexlembur = $indexlembur + ($index[$i]['indexlembur'] * ($durasi / 60));
//                } else {
//                    //jika yang paling terakhir
//                    $indexlembur = $indexlembur + ($index[$i]['indexlembur'] * ($lamalembur / 60));
//                }
//            }
//        }
//    }
//
//    echo round($indexlembur, 1);
//    return Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
//    $date = date_create('2019-04-15');
//    for($j = 0;$j<=35;$j++) {
//        $date = date_create('2019-03-15');
//        date_add($date,date_interval_create_from_date_string($j." days"));
//        $a =  date_format($date,"j").'<br>';
//        echo $a;
//    }
//    echo date_format(date_create('2019-04-15'),"j");
//    return;
//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $sql = 'SELECT * FROM atribut';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
//    return $row[1];
//    return substr('tgl0112',5);
//    return Utils::getIdIndexLemburFromPegawai(3,'t',2235);
//    abort(404);
//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
//    $pdo->exec('
//        set @i_terlambat = 0;
//        DROP TEMPORARY TABLE IF EXISTS test20190506;
//        CREATE TEMPORARY TABLE test20190506 (
//              peringkat        INT UNSIGNED NOT NULL,
//              nama             VARCHAR(100) NOT NULL
//            ) Engine=Memory;
//        INSERT INTO test20190506
//            SELECT
//                @i_terlambat := @i_terlambat + 1,
//                p.nama
//            FROM
//                pegawai p,
//                _peringkatabsen pra
//            WHERE
//                pra.idpegawai=p.id
//            ORDER BY
//                pra.terlambat DESC,
//                pra.terlambatlama DESC,
//                pra.peringkat;
//    ');
//
//    $sql = 'SELECT * FROM test20190506';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//    return $stmt->fetchAll(PDO::FETCH_OBJ);
//    return 'ok';
//    return Utils::getFirstCharInWord(' CUTI 2 3 ');
//    return view('tesonly');
//    $result = floor(38913/3600);
//    return substr($result, strpos($result, ".") + 1);
//    return $result;
//    return 86040/3600;
//    return Utils::sec2prettyCustom(32400,false,false,'menit');
//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    return Utils::getData($pdo,'indexjamkerja','*','berlakumulai <= CURRENT_DATE()','nama');
//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $sql = 'SELECT
//                getlokasiabsen(p.lat, p.lon) as lokasi,
//                COUNT(*) as jumlah
//            FROM
//                (
//                    SELECT
//                        pg.id,
//                        MAX(CONCAT(DATE_FORMAT(la.waktu,"%Y%m%d%H%i%s"),la.masukkeluar)) as lastabsen,
//                        la.lat,
//                        la.lon
//                    FROM
//                        logabsen la,
//                        pegawai pg
//                    WHERE
//                        la.idpegawai=pg.id AND
//                        la.status = "v" AND
//                        pg.del = "t" AND
//                        pg.status = "a"
//                    GROUP BY
//                        la.idpegawai
//                    HAVING
//                        RIGHT(lastabsen,1)="m"
//                ) p
//            WHERE
//              1=1
//            GROUP BY
//                lokasi
//            ORDER BY
//                lokasi ASC
//            LIMIT '.config('consts.LIMIT_6_KOLOM');
//        $stmt = $pdo->prepare($sql);
//        $stmt->execute();
//        return $stmt->fetchAll(PDO::FETCH_OBJ);
//    $tes = '<br>DH/L - HALIM/FXZ (AVSEC)';
//    return str_replace('/', '', $tes);
//    return htmlentities($tes);
//    if (Hash::check('1234', '$2y$10$yLSrmS4slLovFirKe14EUOBf3VLaeJ0m.zB4Yb8sowgcYUc9Fy6VS')) {
//        // The passwords match...
//        return 'benar';
//    }else{
//        return 'salah';
//    }
//    $a = floor(27000/60); // jam kerja efektif
//    $b = floor(10650/60); // terlambat aktual
//    $c = $a-$b; // jam kerja aktual
//    $hasil_a = Utils::min2hhmm($a);
//    $hasil_b = Utils::min2hhmm($b);
//    $hasil_c = Utils::min2hhmm($c);
//    return $hasil_a.' '.$hasil_b.' '.$hasil_c;
//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $sql = 'SELECT ppk.* FROM payroll_posting_komponen ppk, payroll_komponen_master pkm WHERE ppk.komponenmaster_id=pkm.id AND pkm.digunakan = "y" AND pkm.tampilkan = "y" AND ppk.idpayroll_posting = :idpayroll_posting ORDER BY ppk.komponenmaster_urutan';
//    $stmt = $pdo->prepare($sql);
//    $stmt->bindValue(':idpayroll_posting', 9);
//    $stmt->execute();
//    $totalkolom = $stmt->rowCount();
//    $arrs = []; //array jika komponenmaster_group sama
//    $arrkg = []; // array komponenmaster group
//    $hasil = '';
//    if($totalkolom > 0) {
//        $rowKomponen = $stmt->fetchAll(PDO::FETCH_ASSOC);
//        $idpayrollkomponenmastergroup_old = '';
//        for ($i = 0; $i < count($rowKomponen); $i++) {
//            if ($rowKomponen[$i]['komponenmaster_group'] != '') {
//                if($idpayrollkomponenmastergroup_old == $rowKomponen[$i]['komponenmaster_group']){
//                    array_push($arrs, '<'.$i.'>'.$rowKomponen[$i]['komponenmaster_group']);
//                }
////                if (!in_array($rowKomponen[$i]['komponenmaster_group'], $arrs, true)) {
////                array_push($arrkg, $i.'|'.$rowKomponen[$i]['komponenmaster_group']);
////                    array_push($arrs, '<'.$i.'>');
////                    array_push($arrkg, $rowKomponen[$i]['komponenmaster_group']);
////                    $hasil .= implode(array_count_values($arrs));
////                    $hasil .= implode($arrs).'<br>';
////                    $hasil .= count($arrs).'<br>';
////                }
//
////                $hasil .= count($arrs) > 1 ? ($arrs[0]).' | '.end($arrs). '<br>' : '';
//                $idpayrollkomponenmastergroup_old = $rowKomponen[$i]['komponenmaster_group'];
//            }
//        }
//    }
//    return implode($arrs).'<br>'.implode(array_count_values($arrkg));
//    return $arrkg;
//    return $hasil;
//    return $arrs;
//    $totalarr = array_count_values($arrkg);
//    return $totalarr;
//    return Utils::getArrayTotalKomponenMasterGroup(9);

//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $sql = 'SELECT fileheader,filefooter FROM payroll_pengaturan';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//    if($stmt->rowCount() > 0){
//        $rowpengaturan = $stmt->fetch(PDO::FETCH_ASSOC);
//        $path = Session::get('folderroot_perusahaan') . '/payroll/';
//        $fileheader = $rowpengaturan['fileheader'];
//        if ($fileheader != '' && file_exists($path.$fileheader)) {
//            $objPHPExcel = PHPExcel_IOFactory::load($path.$fileheader);
//            $objWorksheet = clone $objPHPExcel->getActiveSheet();
//
////            $objWorksheet->insertNewRowBefore(17, 1);
//            $b = 1;
//            while(1 == 1) {
//                //keluar dari loop jika bertemu dengan end
//                if ($objWorksheet->getCell('A' . $b)->getValue() == '{isi}') {
//                    break;
//                }
//                $b++;
//            }
////            return $b;
//
////            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B5', 'tester');
//
//            $objPHPExcel->getActiveSheet()->insertNewRowBefore($b, 1);
//            $objPHPExcel->getActiveSheet()->setCellValue('A9', 'insert row');
////            $objPHPExcel->getActiveSheet()->setCellValue('A9', 'insert row 2');
//
//            header('Content-Type: application/vnd.ms-excel');
//            header('Content-Disposition: attachment;filename="' . time() . '_tester.xlsx"');
//            header('Cache-Control: max-age=0');
//            header('Cache-Control: max-age=1');
//
//            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
//            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
//            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
//            header('Pragma: public'); // HTTP/1.0
//
//            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
//            $writer->save('php://output');
//        }
//    }
//    $pdo = DB::connection('perusahaan_db')->getPdo();
//    $sql = 'SELECT fileheader,filefooter FROM payroll_pengaturan';
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute();
//    $isi = '';
//    if($stmt->rowCount() > 0){
//        $rowpengaturan = $stmt->fetch(PDO::FETCH_ASSOC);
//        $path = Session::get('folderroot_perusahaan') . '/payroll/';
//        $fileheader = $rowpengaturan['fileheader'];
//        if ($fileheader != '' && file_exists($path.$fileheader)) {
//            $template = PHPExcel_IOFactory::load($path.$fileheader);
//            $objWorksheet = clone $template->getActiveSheet();
////            // dapatkan cell yang di merge
////            foreach ($objWorksheet->getMergeCells() as $cells) {
//////                $isi .= $cells;
////                //bisa langsung buat looping merge nya
////            }
//            // dapatkan gambar hasilnya obj
//            $drawings = $objWorksheet->getDrawingCollection();
//            $posisi = '';
//            foreach($drawings as $drawing){
////                $isi .= 'width:'. $drawing->getWidth().' nama: '.$drawing->getName().' path: '.substr($drawing->getPath(), 6).' format: '.$drawing->getExtension().' '.$drawing->getIndexedFilename().'<br>';
//                $string = $drawing->getCoordinates();
//                $coordinate = PHPExcel_Cell::coordinateFromString($string);
//                $posisi .= $coordinate[0].$coordinate[1].' ';
//            }
//
////            return $objWorksheet->getHeaderFooter()->getImages();
//            $b = 1; //baris
//            $style='';
//            while(1 == 1) {
//                //keluar dari loop jika bertemu dengan end
//                if ($objWorksheet->getCell('A' . $b)->getValue() == 'end') {
//                    break;
//                }
//
//                $h = 1; // huruf / kolom
//                while(1 == 1){
//                    //keluar dari loop jika bertemu dengan end
//                    if($objWorksheet->getCell(Utils::angkaToHuruf($h).'1')->getValue() == 'end'){
//                        break 1;
//                    }
//
////                    $isi .= $objWorksheet->getCell(Utils::angkaToHuruf($h).$b)->getValue().' ';
//
//                    $isi .= Utils::angkaToHuruf($h).$b.' : '.$objWorksheet->getStyle(Utils::angkaToHuruf($h).$b)->getFont()->getSize().'<br>';
//
//                    $style.= Utils::angkaToHuruf($h).$b.' :'.$objWorksheet->getStyle(Utils::angkaToHuruf($h).$b)->getAlignment()->getHorizontal().'<br>';
//                    $style.= Utils::angkaToHuruf($h).$b.' :'.$objWorksheet->getStyle(Utils::angkaToHuruf($h).$b)->getAlignment()->getVertical().'<br>';
//
//                    $h++;
//                }
//
//                $b++;
//            }
//        }
//    }
//    return $isi;
    // $code = '';
    // try {
    //     eval($code);
    //     return 'eval ok';
    // } catch (ParseError $e) {
    //     return $e->getMessage();
    //     // Report error somehow
    // }
//    $gambar = '<img src="'.asset('logo_sp_small.png').'">';
//    $gambar = '<img src="../../img_v3/no-image.png">';
//    $path = '../../img_v3/no-image.png';
//    $gambar = Response::make(File::get($path))->header('Content-Type', 'image/png');
//    return $gambar;
    // return Utils::cekMasukDiHariLibur('2018-10-11',1167) == true ? 'y' : 't';
    // return Utils::cekHariLibur('2018-10-15') == true ? 'y' : 't';
//    return str_pad('12',2,'0',STR_PAD_LEFT);
//     $tanggalawal_str = strtotime(Utils::convertDmy2Ymd('26/01/2019'));
//     return $hari = date('ymj', strtotime(date('Y-m-d',$tanggalawal_str) . ' +6 day'));
    // return Utils::getIndexLembur(60,0,1167).'<br>'.(1+1.9+2.4);
    // return Utils::getIndexLembur(60,1,1167);
    // return Utils::getIndexLembur(337,2,'y',1167);
    // return Utils::getIndexLembur(Utils::sec2prettyCustom(20256,false,false,'menit'),'2','t',1167);
    // return $hari = date('N', strtotime(date('Y-m-d','2019-01-01')));
    // return Utils::sec2prettyCustom(600,true,'jam');
    // 20256
    // return Utils::sec2prettyCustom(20256,false,false,'menit');
    // $code = '
    //     $hitung=1+2+3;
    //     $r1=array();
    //     for($a=0;$a<100;$a++) {
    //         $b = "ok";
    //     }
    //     $result = $a;
    //     round($a);
    //     $iii=10/0;
    // ';
    // return Utils::eval_not_evil($code);
    // $waktu = abs(strtotime('2019-01-01 01:00:00') - strtotime('2019-01-01 00:02:10'));
    // $waktu = abs(strtotime('2019-01-01 00:02:10'));
    // $to_time=strtotime("2011-01-12 10:42:59");
    // $from_time=strtotime("2011-01-12 10:21:00");
    // $waktu = floor(round(abs($to_time - $from_time) / 60,2));
    // return $waktu.' menit';

    // return Utils::sec2hhmm(60.999999);
    // $urlimage = url('foto/perusahaan').'/'.Session::get('conf_webperusahaan');
    // $urlimage = url('fotonormal/pegawai/1167');
    // $urlimage = url('foto/user/4');
    // return exif_imagetype($urlimage);
    // $pdop = DB::getPdo();
    // $sql1 = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
    // $stmt1 = $pdop->prepare($sql1);
    // $stmt1->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
    // $stmt1->execute();
    // $rowp = $stmt1->fetch(PDO::FETCH_ASSOC);
    // $path = $rowp['folderroot'].'/logo_perusahaan';
    // if (file_exists($path)) {
    //     $raw = Utils::decrypt($path);
    //     return exif_imagetype($raw);
    // }
    // return Hash::make('1234');
    // include(app_path() . 'sesuatu yg mau di include');
    // require_once('safereval/config.safereval.php');
    // require_once('safereval/class.safereval.php');

    // $code = '
    //     $hitung=1+2+3;
    //     $r1=array();s
    //     for($a=0;$a<100;$a++) {
    //         $b = "ok";
    //     }
    //     $result = $a;
    //     round($a);
    //     $iii=10/0;
    // ';
    // $se = new SaferEval();
    // $errors = $se->checkScript($code, false);
    // if($errors != ''){
    //     $e = '';
    //     foreach ($errors as $key => $value) {
    //         $e = $value;
    //     }
    //     echo($e['name']);
    //     echo($e['line']);
    // }else{
    //     echo "ok";
    // }
    // return;
    // $nama = $errors;
    // return $nama;
    // print_r($errors);
    // try {

    // }
    // catch(\Exception $e) {
    //     print_r($e->getMessage());
    // }

    // $periode = '1810';
    // $tanggalawal = '20'.substr($periode, 0, 2).'-'.substr($periode, -2).'-01';
    // $tanggalakhir = date("Y-m-t", strtotime($tanggalawal));
    // return $periode.' '.substr($periode, 0, 2).' '.$tanggalawal.' '.$tanggalakhir;

    // @link http://www.php.net/manual/en/class.datetime.php
    // $d1 = new DateTime('2018-12-30');
    // $d2 = new DateTime('2018-11-01');

    // // @link http://www.php.net/manual/en/class.dateinterval.php
    // $interval = $d1->diff($d2);

    // return $interval->format('%m');

    // $datetime1 = date_create('2018-10-01');
    // $datetime2 = date_create('2018-11-31');

    // // calculates the difference between DateTime objects
    // $interval = date_diff($datetime1, $datetime2);

    // // printing result in days format
    // // echo round($interval->format('%a')/30);
    // echo round($interval->format('%a'));

//    return utils::encrypt('/Users/sierra/Desktop/periksa/1D5C60B7-AC03-437B-999A-A865D2927159.jpg');
//    $data = utils::encrypt('/Users/sierra/Desktop/periksa/input');
//    $data = utils::encrypt('/Users/sierra/Library/Developer/CoreSimulator/Devices/2B71D78B-7064-4632-B1BE-362191EAD71E/data/Containers/Data/Application/6D342537-91D8-4EFE-B884-748B8DAEE1D9/Documents/input');
//    file_put_contents('/Users/sierra/Desktop/periksa/output/raw', $data);
//    file_put_contents('/Users/sierra/Desktop/periksa/output/base64', base64_encode($data));
//    return 'selesai';

//     $data = '2018-09-07 06:00:00|2018-09-07 12:15:00|2018-09-07 13:00:00|2018-09-07 19:00:00';
//     $jadwalKerja ='';
//     $explode = explode('|', $data);
//     $jumlah = floor(count($explode) / 2);
//     $a = 0;
//     for($i=0;$i<$jumlah;$i++) {
// //    for($i=0;$i<count($explode)-1;$i++) {
//         $awal_tgl = substr($explode[$i+$a],0,10);
//         $awal_jam = substr($explode[$i+$a],11,8);
//         $akhir_tgl = substr($explode[$i+$a+1],0,10);
//         $akhir_jam = substr($explode[$i+$a+1],11,8);
//         $tandaBedaHari = '';
//         if ($awal_tgl != $akhir_tgl) {
//             $tandaBedaHari = '*';
//         }
//         $hoverspan = '';
//         if($tandaBedaHari != ''){
//             $hoverspan = 'title="'.trans('all.bedahari').'"';
//         }
//         $jadwalKerja .= '<span '.$hoverspan.'>'.$awal_jam.' ~ '.$akhir_jam.' '.$tandaBedaHari.'</span>';
//         if ($i < $jumlah) {
//             $jadwalKerja .= '<br>';
//         }
//         $a++;
//     }
//     return $jadwalKerja;

//    $data = array('nama' => 'tester', 'nomorhp' => '08123456789', 'email' => 'fathoela@gmail.com', 'idperusahaankonfirmasi' => '1', 'kode' => '123');
//    Mail::send('templateemail.buatperusahaan', $data, function ($message) use ($data) {
//        $message->to($data['email'])->subject('Konfirmasi Perusahaan Baru');
//        $message->from('no-reply@smartpresence.id', 'Smart Presence');
//    });
//    return view('templateemail/buatperusahaan', ['nama' => 'tester', 'email' => 'tester@gmail.com', 'nomorhp' => '0812341', 'idperusahaankonfirmasi' => '2', 'kode' => '1234']);
//    return view('templateemail/daftar', ['nama' => 'tester', 'email' => 'tester@gmail.com', 'nomorhp' => '0812341', 'iduserkonfirmasi' => '2', 'kode' => '1234']);
    //$cookie = \Cookie::forget('showcase_cookies_'.Session::get('iduser_perusahaan'));
    //return $cookie;
//    if(Request::cookie('name') == ''){
//        //Create a response instance
//        $response = new Illuminate\Http\Response('Hello World');
//
//        //Call the withCookie() method with the response method
//        $response->withCookie(cookie('name', 'fathoela', 2));
//        return $response;
//    }else{
//        return Request::cookie('name');
//    }
    //return strlen('2017101');
//    $hakaksesSU = (object) array("nama" => "Super Admin","ajakan" => "i","alasanmasukkeluar" => "lm","alasantidakmasuk" => "lm","atribut" => "lm","facesample" => "lm","fingersample" => "lm","hakakses" => "lm","harilibur" => "lm","ijintidakmasuk" => "lm","cuti" => "lm","jamkerja" => "lm","jadwalshift" => "lm","lokasi" => "lm","logabsen" => "lm","fingerprintconnector" => "lm","mesin" => "lm","pegawai" => "lm","setulangkatasandipegawai" => "l","aturatributdanlokasi" => "m","agama" => "lm","pengaturan" => "lm","pengelola" => "lm","perusahaan" => "lm","laporan" => "l","riwayatpengguna" => "l","riwayatpegawai" => "l","riwayatsms" => "l","slideshow" => "lm","batasan" => "lm","postingdata" => "i","hapusdata" => "i","_flaghapus" => "t");
//    //$data = json_encode($hakaksesSU);
//    //$hakaksesSU = [{"id":33,"idperusahaan":33,"nama":"Super Admin","ajakan":"i","alasanmasukkeluar":"lm","alasantidakmasuk":"lm","atribut":"lm","facesample":"lm","fingersample":"lm","hakakses":"lm","harilibur":"lm","ijintidakmasuk":"lm","cuti":"lm","jamkerja":"lm","jadwalshift":"lm","lokasi":"lm","logabsen":"lm","fingerprintconnector":"lm","mesin":"lm","pegawai":"lm","setulangkatasandipegawai":"l","aturatributdanlokasi":"m","agama":"lm","pengaturan":"lm","pengelola":"lm","perusahaan":"lm","laporan":"l","riwayatpengguna":"l","riwayatpegawai":"l","riwayatsms":"l","slideshow":"lm","batasan":"lm","postingdata":"i","hapusdata":"i","_flaghapus":"t","inserted":"2016-08-21 23:48:58"}];
//    //return response()->json($data);
//    return $hakaksesSU->nama;

    //return Utils::list_yymm_before(12);
    //return $_SERVER['DOCUMENT_ROOT'] .'/'.config('consts.FOLDER_RAW').'/script.sql';
});
Route::get('tes2', 'Controller@getPayrollValue');
Route::get('tes/{idperusahaan}', function($idperusahaan){
    abort(404);
//    return 'ok';
//     // $temp_formula = '$result = <km>GPOK</km> + <km>UKTP</km> + <km>INSL</km> + <km>HARDS</km> + <km>TUNJD</km> + 10000000;';
//     // return preg_replace('/\<km\>\w+<\/km\>/', '$komponen_master_result["$0"]', $temp_formula);

//     $pdoa = DB::getPdo();
//     $sql = 'SELECT dbhost,dbport,dbuser,AES_DECRYPT(dbpass, "e754251708594345576d9407126e4d46") as dbpass,dbname,folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
//     $stmt = $pdoa->prepare($sql);
//     $stmt->bindValue(':idperusahaan', $idperusahaan);
//     $stmt->execute();
//     if($stmt->rowCount() > 0) {
//         $route = $stmt->fetch(PDO::FETCH_OBJ);
//         // set koneksi database
//         Config::set('database.connections.perusahaan_db.host', $route->dbhost);
//         Config::set('database.connections.perusahaan_db.port', $route->dbport);
//         Config::set('database.connections.perusahaan_db.username', $route->dbuser);
//         Config::set('database.connections.perusahaan_db.password', $route->dbpass);
//         Config::set('database.connections.perusahaan_db.database', $route->dbname);
//     }

//     $pdo = DB::connection('perusahaan_db')->getPdo();
//     $periode = '1810';
//     // $idpayrollkomponenmaster = Session::get('payrollkomponeninputmanual_payrollkomponenmaster');
//     $idpayrollkomponenmaster = 3;
//     $bataspresensi_tanggalawal = '2018-10-01';
//     $bataspresensi_tanggalakhir = '2018-10-31';
//     $idpegawai = 1167;

//     $sql = 'SELECT
//                 p.id,
//                 p.pin,
//                 p.nama,
//                 CONCAT("<span title=\"",p.nama,"\" class=\"detailpegawai\" onclick=\"detailpegawai(,p.id,)\" style=\"cursor:pointer;\">",p.nama,"</span>") as pegawai,
//                 getatributpegawai_all(p.id) as atribut,
//                 lower(payroll_getatributnilai(p.id)) as payroll_atributnilai,
//                 lower(payroll_getatributvariable(p.id)) as payroll_atributvariable,
//                 prki.nominal
//             FROM
//                 pegawai p
//                 LEFT JOIN payroll_komponen_inputmanual prki ON prki.idpegawai=p.id AND prki.idpayroll_komponen_master = '.$idpayrollkomponenmaster.'
//                 LEFT JOIN pegawaiatribut pa ON pa.idpegawai=p.id
//                 LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
//                 LEFT JOIN atribut a ON an.idatribut=a.id
//             WHERE
//                 p.del = "t" AND p.id=1170
//             GROUP BY
//                 p.id
//             ORDER BY
//                 p.nama';
//     $stmt = $pdo->prepare($sql);
//     $stmt->execute();
//     $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     $pegawai_atributnilai = $row[0]['payroll_atributnilai'];
//     $pegawai_atributvariable = $row[0]['payroll_atributvariable'];

//     $sql = 'SELECT
//                 pkm.id,
//                 pkm.kode,
//                 pkm.carainput,
//                 pkm.formula,
//                 IFNULL(pkim.nominal,0) as nominal
//             FROM
//                 payroll_komponen_master pkm
//                 LEFT JOIN payroll_komponen_inputmanual pkim ON pkim.periode=:periode AND pkim.idpegawai=:idpegawai AND pkim.idpayroll_komponen_master=pkm.id
//             ORDER BY
//                 pkm.urutan ASC
//            ';
//     $stmt = $pdo->prepare($sql);
//     $stmt->bindValue(':periode', $periode);
//     $stmt->bindValue(':idpegawai', $idpegawai);
//     $stmt->execute();
//     $komponen_master = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     $komponen_master_result = array();
//     for($i=0;$i<count($komponen_master);$i++) {
//         $kode = '<km>'.strtolower($komponen_master[$i]['kode']).'</km>';
//         if ($komponen_master[$i]['carainput']=='inputmanual') {
//             $komponen_master_result[$kode] = $komponen_master[$i]['nominal'];
//         }
//         else {
//             $komponen_master_result[$kode] = 0;
//         }
//     }

//     $script = '
// function get_rekapabsen() {
//   $pdo = DB::connection("perusahaan_db")->getPdo();
//   $sql = "SELECT * FROM rekapabsen WHERE idpegawai=:idpegawai AND tanggal BETWEEN :tanggalawal AND :tanggalakhir";
//   $stmt = $pdo->prepare($sql);
//   $stmt->bindValue(":idpegawai","'.$idpegawai.'");
//   $stmt->bindValue(":tanggalawal","'.$bataspresensi_tanggalawal.'");
//   $stmt->bindValue(":tanggalakhir","'.$bataspresensi_tanggalakhir.'");
//   $stmt->execute();
//   $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
//   return $row;
// }'.PHP_EOL;

//     // macam2 tag: av, an, pr, km
//     for($i=0;$i<count($komponen_master);$i++) {
//         // if ($komponen_master[$i]['id']==6) {
//         if ($komponen_master[$i]['carainput']=='formula' && $komponen_master[$i]['formula']!='') {
//             $formula = $komponen_master[$i]['formula'];
//             $lines = explode(PHP_EOL, $formula);
//             $temp_formula = '';
//             for($j=0;$j<count($lines);$j++) {
//                 $temp_formula = $temp_formula . '  '. $lines[$j].PHP_EOL;
//             }

//             $arr_tag_atributnilai = Utils::payroll_SliceTag($formula, '/\<an\>\w+\<\/an\>/');
//             $arr_payroll_atributnilai = Utils::payroll_Explode3level($pegawai_atributnilai, '|:;', 'an');
//             $temp_formula = Utils::payroll_ReplaceTag($arr_tag_atributnilai, $arr_payroll_atributnilai, $temp_formula, "array");

//             $arr_tag_atributvariable = Utils::payroll_SliceTag($formula, '/\<av\>\w+\<\/av\>/');
//             $arr_payroll_atributvariable = Utils::payroll_Explode2level($pegawai_atributvariable, '|:', 'av');
//             $temp_formula = Utils::payroll_ReplaceTag($arr_tag_atributvariable, $arr_payroll_atributvariable, $temp_formula, "teks");

//             // $arr_tag_komponenmaster = Utils::payroll_SliceTag($formula, '/\<km\>\w+\<\/km\>/');
//             // $temp_formula = Utils::payroll_ReplaceTag($arr_tag_komponenmaster, $komponen_master_result, $temp_formula, "angka");

//             $temp_formula = preg_replace('/\<km\>\w+<\/km\>/', '$komponen_master_result[strtolower("$0")]', $temp_formula);
//             $temp_formula = PHP_EOL.'function formula_'.$i.'($komponen_master_result) {'.PHP_EOL.$temp_formula. '  return $result;'.PHP_EOL.'}'.PHP_EOL;
//             $script = $script . $temp_formula;
//         }
//     }
//     $script = $script . PHP_EOL;
//     // $script = $script.str_replace('$', '$___', $temp_formula);
//     for($i=0;$i<count($komponen_master);$i++) {
//         $kode = '<km>'.strtolower($komponen_master[$i]['kode']).'</km>';
//         if ($komponen_master[$i]['carainput']=='formula' && $komponen_master[$i]['formula']!='') {
//             $script = $script.'$komponen_master_result["'.$kode.'"] = formula_'.$i.'($komponen_master_result);'.PHP_EOL;
//         }
//     }
//     // echo "<xmp>";
//     // echo $script;
//     // echo "</xmp>";

//     eval($script);

//     $result = array();
//     foreach ($komponen_master_result as $key => $value) {
//         $key_new = str_replace('<km>', '', str_replace('</km>', '', str_replace(' ', '', $key)));
//         $result[$key_new] = $value;
//     }
//     return $result;

    // $result = array();
    // for($i=0;$i<count($komponen_master_result);$i++){
    //     $result[$i]['kode'] = $komponen_master_result['kode'];
    // }
    // return $result;

    // print_r($komponen_master_result);
    // return $komponen_master_result['<km>lmb</km>'];
    // return $komponen_master_result;

   // $url = Request::input('file');
   // return $url;
//    $datetime1 = strtotime("2018-10-02 08:00:00");
//     $datetime2 = strtotime("2018-10-02 17:00:00");
//     $interval  = abs($datetime2 - $datetime1);
//     $minutes   = round($interval / 3600);
//     echo 'Diff. in minutes is: '.$minutes;
    // $string = '!@#$%^&*() tester bro';
    // if (strlen($string) != strlen(utf8_decode($string)))
    // {
    //     return 'is unicode';
    // }else{
    //     return 'normal string';
    // }
    // return Utils::cekPegawaiAtributNilai('1170','281') == true ? 'true' : 'false';
    // return Utils::cekPegawaiAtributNilai('1170','281');
    //  $waktu = Utils::getCurrentDateTime();
    //  return $waktu['waktuawal'].' '.$waktu['waktuakhir'];
     //return Utils::list_yymm_before(12);
     //return $_SERVER['DOCUMENT_ROOT'] .'/'.config('consts.FOLDER_RAW').'/script.sql';
});

//Route::post('tester', function(){
//    abort(404);
////   $url = Request::input('file');
////   return $url;
//
//    //return Utils::list_yymm_before(12);
//    //return $_SERVER['DOCUMENT_ROOT'] .'/'.config('consts.FOLDER_RAW').'/script.sql';
//});

Route::get('tespdf', function(){
//    abort(404);
     require('fpdf/fpdf.php');
    //Cell(width , height , text , border , endline , [align])
     $pdf = new FPDF();
     $pdf->AddPage();
    $urlimage = 'https://dash.smartpresence.id/fotonormal/perusahaan/10950';
////            $urlimage = 'https://dash.smartpresence.id/fotonormal/perusahaan/'.Session::get('conf_webperusahaan');
    if(exif_imagetype($urlimage) == 2){
        // JPEG bernilai 2
        $format = 'JPEG';
    }else{
        // PNG bernilai 3
        $format = 'PNG';
    }
    if(file_exists($urlimage)) {
        $pdf->Cell(20, 20, $pdf->Image($urlimage, $pdf->GetX(), $pdf->GetY(), 20, 20));
        $pdf->Cell(0, 10, '', 0, 1);
    }
    $pdf->SetFont('Arial','B',16);
    $pdf->SetFillColor(235,236,236);

    $pdo = DB::getPdo();
    $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':idperusahaan', 10950);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $path = $row['folderroot'] . '/logo_perusahaan';
    $raw = Utils::decrypt($path);
    return $raw;
//    $pdf->Cell(20, 20, $pdf->Image('https://upload.wikimedia.org/wikipedia/en/4/47/Iron_Man_%28circa_2018%29.png', $pdf->GetX(), $pdf->GetY(), 20, 20));
    $pdf->Cell(20, 20, $pdf->Image($raw, $pdf->GetX(), $pdf->GetY(), 20, 20));
    $pdf->Cell(0, 10, '', 0, 1);

    $pdf->Cell(40,20,'Hello World!',1);
     $pdf->Cell(80,10,'Hello World!',1);
     $pdf->Cell(40,20,'Hello World!',1);
     $pdf->Cell(0,10,'',0,1);
     $pdf->Cell(40,10,'',0,0);
     $pdf->Cell(40,10,'Hello World!',1);
     $pdf->Cell(40,10,'Hello World!',1);
     $pdf->Output('D');
//    $data = array('nama' => 'paijo 20190223', 'kode' => '112', 'nomorhp' => '082257038541', 'email' => 'fathoela@gmail.com', 'iduserkonfirmasi' => '1');
//    $pdf = PDF::loadView('templateemail.daftar', $data);
//    return $pdf->stream('document.pdf');
});

// route custom
Route::get('setulang/{menu}', function($menu){
    if (Auth::check()) {
        if ($menu == 'aktivitas') {
            Utils::deleteSession('aktivitas_idaktivitaskategori');
            return redirect('datainduk/pegawai/aktivitas');
        }
        if ($menu == 'perlokasi') {
            Utils::deleteSession('lapperlokasi_tanggalawal');
            Utils::deleteSession('lapperlokasi_tanggalakhir');
            Utils::deleteSession('lapperlokasi_atribut');
            Utils::deleteSession('lapperlokasi_lokasi');
            Utils::deleteSession('lapperlokasi_filtermode');
            Utils::deleteSession('lapperlokasi_tanggal');
            Utils::deleteSession('lapperlokasi_bulan');
            Utils::deleteSession('lapperlokasi_tahun');
            return redirect('laporan/perlokasi');
        }
    }
    abort(404);
});

//Route::get('tesheader', function(Illuminate\Http\Request $request) {
//    $header = $request->header('Authorization');
//    return $header;
////    return Utils::decodeJWT($header);
//});

Route::get('aktivitaspegawai', function(Illuminate\Http\Request $request) {
    $header = $request->header('Authorization');
    try {
        $auth = Utils::decodeJWT($header);
        if ($auth != '') {
            $idperusahaan = $auth['pid'];
            $idpegawai = $auth['id'];
            $pdo = Utils::connectPerusahaan($idperusahaan);
            if ($pdo != 'error') {
//                $cekidpegawai = Utils::getDataCustomWhere($pdo, 'pegawai', 'nama', 'id=' . $idpegawai);
                $cekidpegawai = Utils::getDataWhere($pdo, 'pegawai', 'nama', 'id', $idpegawai);
                if ($cekidpegawai != '') {
                    $idaktivitaskategori = '';
                    $sql = 'SELECT
                                aka.idaktivitaskategori
                            FROM
                                pegawaiatribut pa,
                                aktivitas_kategori_atribut aka
                            WHERE
                                pa.idatributnilai=aka.idatributnilai AND
                                pa.idpegawai = :idpegawai
                            GROUP BY
                                aka.idaktivitaskategori';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $idaktivitaskategori .= ',' . $row['idaktivitaskategori'];
                        }
                        $idaktivitaskategori = substr($idaktivitaskategori, 1);
                    }
                    $where = ' AND 1=2';
                    if($idaktivitaskategori != ''){
                        $where = 'AND ak.id IN(:idaktivitaskategori)';
                    }
                    $sql = '(
                                SELECT
                                   ak.id,
                                    ak.nama
                                FROM
                                    aktivitas_kategori ak
                                WHERE
                                    ak.digunakan = "y" AND
                                    ak.id NOT IN (SELECT idaktivitaskategori FROM aktivitas_kategori_atribut)
                            ) UNION (
                                SELECT
                                    ak.id,
                                    ak.nama
                                FROM
                                    aktivitas_kategori ak
                                WHERE
                                    ak.digunakan = "y"
                                    '.$where.'
                            )';
                    $stmt = $pdo->prepare($sql);
                    if($idaktivitaskategori != ''){
                        $stmt->bindValue(':idaktivitaskategori', $idaktivitaskategori);
                    }
                    $stmt->execute();
                    $dataaktivitaskategori = $stmt->fetchAll(PDO::FETCH_OBJ);

                    return view('aktivitaspegawai', ['dataaktivitaskategori' => $dataaktivitaskategori, 'idperusahaan' => $idperusahaan, 'idpegawai' => $idpegawai, 'namapegawai' => $cekidpegawai]);
                } else {
                    //        return 'Pegawai tidak ditemukan';
                    abort(404);
                }
            } else {
                //        return 'Perusahaan tidak ditemukan';
                abort(404);
            }
        } else {
            abort(404);
        }
    }catch (\Exception $e){
        return $e->getMessage();
    }
});

//Route::get('aktivitaspegawai/{idperusahaan}/{idpegawai}', function($idperusahaan,$idpegawai) {
//    $pdo = Utils::connectPerusahaan($idperusahaan);
//    if($pdo != 'error') {
//        $cekidpegawai = Utils::getDataCustomWhere($pdo, 'pegawai', 'nama', 'id=' . $idpegawai);
//        if ($cekidpegawai != '') {
//            $idaktivitaskategori = '';
//            $where = '';
//            $sql = 'SELECT
//                        aka.idaktivitaskategori
//                    FROM
//                        pegawaiatribut pa,
//                        aktivitas_kategori_atribut aka
//                    WHERE
//                        pa.idatributnilai=aka.idatributnilai AND
//                        pa.idpegawai = :idpegawai
//                    GROUP BY
//                        aka.idaktivitaskategori';
//            $stmt = $pdo->prepare($sql);
//            $stmt->bindValue(':idpegawai', $idpegawai);
//            $stmt->execute();
//            if($stmt->rowCount() > 0){
//                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//                    $idaktivitaskategori .= ','.$row['idaktivitaskategori'];
//                }
//                $idaktivitaskategori = substr($idaktivitaskategori, 1);
//            }
//
//            $sql = '(
//                        SELECT
//                           ak.id,
//                            ak.nama
//                        FROM
//                            aktivitas_kategori ak
//                        WHERE
//                            ak.digunakan = "y" AND
//                            ak.id NOT IN (SELECT idaktivitaskategori FROM aktivitas_kategori_atribut)
//                    ) UNION (
//                        SELECT
//                            ak.id,
//                            ak.nama
//                        FROM
//                            aktivitas_kategori ak
//                        WHERE
//                            ak.digunakan = "y" AND
//                            ak.id IN('.$idaktivitaskategori.')
//                    )';
//            $stmt = $pdo->prepare($sql);
//            $stmt->execute();
//            $dataaktivitaskategori = $stmt->fetchAll(PDO::FETCH_OBJ);
//
//            return view('aktivitaspegawai', ['dataaktivitaskategori' => $dataaktivitaskategori, 'idperusahaan' => $idperusahaan, 'idpegawai' => $idpegawai, 'namapegawai' => $cekidpegawai]);
//        }else{
////        return 'Pegawai tidak ditemukan';
//            abort(404);
//        }
//    }else{
////        return 'Perusahaan tidak ditemukan';
//        abort(404);
//    }
//})->where(['idperusahaan' => '[0-9]+', 'idpegawai' => '[0-9]+']);

Route::post('setaktivitaskategori', function(){
    if (Auth::check()) {
        $idperusahaan = Request::input('idperusahaan');
        $idpegawai = Request::input('idpegawai');
        $idaktivitaskategori = Request::input('aktivitaskategori');
        $pdo = Utils::connectPerusahaan($idperusahaan);
        if ($pdo != 'error') {
//        $cekadadata = Utils::getDataCustomWhere($pdo,'aktivitas_kategori','id','id = '.$idaktivitaskategori);
            $cekadadata = Utils::getDataWhere($pdo, 'aktivitas_kategori', 'id', 'id', $idaktivitaskategori);
            if ($cekadadata != '') {
                return redirect('aktivitaspegawai/' . $idperusahaan . '/' . $idpegawai . '/' . $idaktivitaskategori);
            } else {
//            return 'Data tidak ditemukan';
                abort(404);
            }
        } else {
//        return 'Perusahaan tidak ditemukan';
            abort(404);
        }
    }else{
        abort(404);
    }
});

Route::get('aktivitaspegawai/{idperusahaan}/{idpegawai}/{idaktivitaskategori}', function($idperusahaan,$idpegawai,$idaktivitaskategori){
//    if (Auth::check()) {
        // tinggal penyesuaian untuk idperusahaan dan idpegawai dan keamanan dipikirkan oleh pak ardian
        $pdo = Utils::connectPerusahaan($idperusahaan);
        if ($pdo != 'error') {
//        $cekidpegawai = Utils::getDataCustomWhere($pdo,'pegawai','nama','id='.$idpegawai);
            $cekidpegawai = Utils::getDataWhere($pdo, 'pegawai', 'nama', 'id', $idpegawai);
            if ($cekidpegawai != '') {
                $ceksudahdientri = Utils::getDataCustomWhere($pdo, 'aktivitas_pegawai', 'id', 'tanggal = CURRENT_DATE() AND idaktivitaskategori = ' . $idaktivitaskategori . ' AND idpegawai = ' . $idpegawai);
                if ($ceksudahdientri != '') {
                    return view('aktivitaspegawai', ['errormsg' => 'Anda sudah mengisi form aktivitas untuk hari ini', 'namapegawai' => $cekidpegawai]);
                } else {
                    $sql = 'SELECT
                            a.id,
                            a.idaktivitaskategori,
                            a.pertanyaan,
                            a.jenisinputan,
                            a.panjangkarakter,
                            a.rentangnilaidari,
                            a.rentangnilaisampai,
                            a.harusdiisi
                        FROM
                            aktivitas a
                        WHERE
                            a.digunakan = "y" AND
                            a.idaktivitaskategori = :idaktivitaskategori
                        ORDER BY
                            a.urutan ASC,
                            a.pertanyaan ASC';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idaktivitaskategori', $idaktivitaskategori);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $dataaktivitas = $stmt->fetchAll(PDO::FETCH_OBJ);

                        $sql = 'SELECT
                                id,
                                idaktivitas,
                                keterangan
                            FROM
                                aktivitas_multiple';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $dataaktivitasmultiple = $stmt->fetchAll(PDO::FETCH_OBJ);

                        return view('aktivitaspegawai', ['dataaktivitas' => $dataaktivitas, 'dataaktivitasmultiple' => $dataaktivitasmultiple, 'idperusahaan' => $idperusahaan, 'idpegawai' => $idpegawai, 'namapegawai' => $cekidpegawai, 'idaktivitaskategori' => $idaktivitaskategori]);
                    } else {
//                    return 'Data tidak ditemukan';
                        return view('aktivitaspegawai', ['errormsg' => 'Data belum siap', 'namapegawai' => $cekidpegawai]);
//                    abort(404);
                    }
                }
            } else {
//            return 'Pegawai tidak ditemukan';
                abort(404);
            }
        } else {
//        return 'Perusahaan tidak ditemukan';
            abort(404);
        }
//    }else{
//        abort(404);
//    }
})->where(['idperusahaan' => '[0-9]+', 'idpegawai' => '[0-9]+', 'idaktivitaskategori' => '[0-9]+']);

Route::post('aktivitaspegawai', function(){
//    if (Auth::check()) {
        $idaktivitas = Request::input('idaktivitas');
        $jenisinputan = Request::input('jenisinputan');
        $pertanyaan = Request::input('pertanyaan');
        $idperusahaan = Request::input('idperusahaan');
        $idpegawai = Request::input('idpegawai');
        $idaktivitaskategori = Request::input('idaktivitaskategori');
        $pdo = Utils::connectPerusahaan($idperusahaan);
        $msg = 'Perusahaan tidak ditemukan';
        if ($pdo != 'error') {
            $msg = 'Data tidak ditemukan';
            if (count($idaktivitas) == count($jenisinputan)) {

                $cekadadata = Utils::getDataCustomWhere($pdo, 'aktivitas_pegawai', 'id', 'tanggal = CURRENT_DATE() AND idpegawai = ' . $idpegawai);
                if ($cekadadata == '') {
                    try {
                        $pdo->beginTransaction();

                        // insert ke table aktivitas_pegawai
                        $sql = 'INSERT INTO aktivitas_pegawai VALUES(null,CURRENT_DATE(),:idaktivitaskategori,:idpegawai,NOW(),NULL)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idaktivitaskategori', $idaktivitaskategori);
                        $stmt->bindValue(':idpegawai', $idpegawai);
                        $stmt->execute();

                        $idaktivitaspegawai = $pdo->lastInsertId();

                        for ($i = 0; $i < count($idaktivitas); $i++) {
                            $multiple = 't';
                            $input = Request::input($jenisinputan[$i] . '_' . $idaktivitas[$i]);
                            if (isset($input)) {
                                if ($jenisinputan[$i] == 'checkbox') {
                                    $input = implode(", ", Request::input($jenisinputan[$i] . '_' . $idaktivitas[$i]));
                                }
                            } else {
                                $input = '';
                                if ($jenisinputan[$i] == 'tanggaldanjam') {
                                    $input = Request::input($jenisinputan[$i] . '_tgl_' . $idaktivitas[$i]);
                                    $input .= ' ' . Request::input($jenisinputan[$i] . '_jam_' . $idaktivitas[$i]);
                                }
                            }
                            if ($jenisinputan[$i] == 'checkbox' || $jenisinputan[$i] == 'radiobutton' || $jenisinputan[$i] == 'combobox') {
                                $multiple = 'y';
                            }
                            // insert ke tabel aktivitas_pegawai
//                        $sql = 'INSERT INTO aktivitas_pegawai VALUES(null,CURRENT_DATE(),:idaktivitas,:idpegawai,:multiple,:jawaban,NOW(),NULL)';
//                        $stmt = $pdo->prepare($sql);
//                        $stmt->bindValue(':idaktivitas',$idaktivitas[$i]);
//                        $stmt->bindValue(':idpegawai',$idpegawai);
//                        $stmt->bindValue(':multiple',$multiple);
//                        $stmt->bindValue(':jawaban',$input);
//                        $stmt->execute();
                            // insert ke table aktivitas_pegawai_detail
                            $sql = 'INSERT INTO aktivitas_pegawai_detail VALUES(null,:idaktivitaspegawai,:idaktivitas,:multiple,:pertanyaan,:jawaban,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idaktivitaspegawai', $idaktivitaspegawai);
                            $stmt->bindValue(':idaktivitas', $idaktivitas[$i]);
                            $stmt->bindValue(':multiple', $multiple);
                            $stmt->bindValue(':pertanyaan', $pertanyaan[$i]);
                            $stmt->bindValue(':jawaban', $input);
                            $stmt->execute();
                        }
                        $pdo->commit();
                        $msg = 'Data berhasil disimpan';
                    } catch (\Exception $e) {
                        $pdo->rollBack();
//                    $msg = $e->getMessage();
                        $msg = 'Terjadi gangguan, harap coba lagi nanti';
                    }
                } else {
                    $msg = 'Anda sudah mengisi form aktivitas untuk hari ini';
                }
            }
        }
        return redirect('aktivitaspegawai/' . $idperusahaan . '/' . $idpegawai . '/' . $idaktivitaskategori)->with('message', $msg);
//    }else{
//        abort(404);
//    }
});

//template payroll excel
Route::get('payrollexcel', 'Controller@payrollExcel');

Route::get('payroll_posting/{periode}', 'Controller@payroll_posting');

Route::get('pengaturan/strukturatribut', function(){
    if (Auth::check()) {
        return view('pengaturan/index', ['menu' => 'pengatuan']);
    }else{
        abort(404);
    }
});

Route::get('cookie/{jenis}/{menu}', function($jenis, $menu){
    $cookiename = $menu . '_cookies_' . Session::get('iduser_perusahaan');
    if (Request::cookie($cookiename) == '') {
        if($jenis == 'set') {
            //Create a response instance
            $response = new Illuminate\Http\Response('Smart Presence Cookies');

            //Call the withCookie() method with the response method
            //$response->withCookie(cookie($cookiename, 'cookies set', 2)); //time limit 2 minutes
            $response->withCookie(cookie($cookiename, 'cookies set', 1440)); //time limit 1 day
            //$response->withCookie(cookie()->forever($cookiename, 'cookies set')); //time limit forever
            return $response;
        }else{
            return 'unset';
        }
    } else {
        return Request::cookie($cookiename);
    }
})->where(['jenis' => '(cek|set)']);

Route::get('totalhari/{bulan}/{tahun}', function($bulan, $tahun){
    $totalhari = cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
    return $totalhari;
});

Route::get('notif/{jenis}', function($jenis){
    if (Auth::check()) {
        if ($jenis == 'perusahaan') {
            $pdo = DB::getPdo();
            // session untuk pilih perusahaan di menu atas
            if (Session::get('superuser_perusahaan') != '') {
                if (Session::get('superuser_perusahaan') == 0) { //superuser batasan
                    //select idperusahaan dari pengelola
                    $sql = 'SELECT group_concat(idperusahaan separator ",") AS idperusahaan FROM pengelola WHERE iduser = :iduser';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                    $stmt->execute();
                    $pengelola = '';
                    if ($stmt->rowCount() > 0) {
                        $rowPengelola = $stmt->fetch(PDO::FETCH_ASSOC);
                        $pengelola = $rowPengelola['idperusahaan'];
                    }

                    //select idperusahaan dari superuser_batasan
                    $sql = 'SELECT group_concat(idperusahaan separator ",") AS idperusahaan FROM superuser_batasan WHERE iduser = :iduser';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                    $stmt->execute();
                    $superuserbatasan = '';
                    if ($stmt->rowCount() > 0) {
                        $rowSuperuserbatasan = $stmt->fetch(PDO::FETCH_ASSOC);
                        $superuserbatasan = ($pengelola != '' ? ',' : '') . $rowSuperuserbatasan['idperusahaan'];
                    }

                    $idperusahaangabungan = $pengelola . $superuserbatasan;

                    $sqlP = 'SELECT id,nama,status,kode FROM perusahaan WHERE status IN("a","c") AND id IN(' . $idperusahaangabungan . ') ORDER BY nama';
                    $stmtP = $pdo->prepare($sqlP);
                    $stmtP->execute();
                } else {
                    $sqlP = 'SELECT id,nama,status,kode FROM perusahaan WHERE status IN("a","c") ORDER BY nama';
                    $stmtP = $pdo->prepare($sqlP);
                    $stmtP->execute();
                }
            } else {
                $sqlP = 'SELECT id,nama,status,kode FROM perusahaan WHERE status IN("a","c") AND id IN(SELECT idperusahaan FROM pengelola WHERE iduser = :iduser) ORDER BY nama';
                $stmtP = $pdo->prepare($sqlP);
                $stmtP->bindValue(':iduser', Session::get('iduser_perusahaan'));
                $stmtP->execute();
            }
            $perusahaan = $stmtP->fetchAll(PDO::FETCH_OBJ);
            $totalperusahaan = $stmtP->rowCount();
            Session::set('conf_perusahaan', $perusahaan);

            return view('notif', ['jenis' => $jenis, 'perusahaan' => $perusahaan, 'totalperusahaan' => $totalperusahaan]);
        } else if ($jenis == 'inbox') {
            //pesan inbox user
            $pdo = DB::getPdo();
            $sql = 'SELECT id,pesan,DATE(inserted) as tanggal, TIME(inserted) as jam, isread FROM user_kotakpesan WHERE iduser = :iduser ORDER BY inserted DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            $totaldata = $stmt->rowCount();

            return view('notif', ['jenis' => $jenis, 'data' => $data, 'totaldata' => $totaldata]);
        } else {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sqlWhere = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if (!($batasan == '' || $batasan == '()')) {
                $sqlWhere .= ' AND p.id IN ' . $batasan;
            }

            //konfirmasiflag
            $flag = '';
            if (strpos(Session::get('hakakses_perusahaan')->notifikasiriwayatabsen, 'i') !== false) {
                $flag .= '"lupaabsenmasuk","lupaabsenkeluar",';
            }
            if (strpos(Session::get('hakakses_perusahaan')->notifikasiterlambat, 'i') !== false) {
                $flag .= '"tidak-terlambat",';
            }
            if (strpos(Session::get('hakakses_perusahaan')->notifikasipulangawal, 'i') !== false) {
                $flag .= '"tidak-pulangawal",';
            }
            if (strpos(Session::get('hakakses_perusahaan')->notifikasilembur, 'i') !== false) {
                $flag .= '"lembur",';
            }
            $flag = $flag != '' ? substr($flag, 0, -1) : '';
            $sqlwherekonfirmasi = $flag != '' ? ' AND k.flag IN(' . $flag . ')' : '';

            $sql = 'SELECT
                    k.id,
                    k.idpegawai,
                    p.nama,
                    k.idlogabsen,
                    k.flag,
                    IF(ISNULL(la.masukkeluar) = false,if(la.masukkeluar="m","<label class=\"label label-primary\">' . trans('all.masuk') . '</label>","<label class=\"label label-danger\">' . trans('all.keluar') . '</label>"),"") as masukkeluar,
                    IFNULL(DATE_FORMAT(la.waktu,"%d/%m/%Y %T"),DATE_FORMAT(k.waktu,"%d/%m/%Y %T")) as waktu,
                    IF(ISNULL(la.konfirmasi) = false,IF(la.konfirmasi="l","' . trans('all.lokasitidakcocok') . '",IF(la.konfirmasi="f","' . trans('all.sampelwajahtidakcocok') . '",IF(la.konfirmasi="lf","' . trans('all.lokasitidakcocok') . ', ' . trans('all.sampelwajahtidakcocok') . '",""))),"") as konfirmasi
                FROM
                    konfirmasi_flag k
                    LEFT JOIN logabsen la ON k.idlogabsen=la.id,
                    pegawai p
                WHERE
                    k.idpegawai=p.id AND
                    k.status = "c"
                    ' . $sqlWhere . '
                    ' . $sqlwherekonfirmasi . '
                ORDER BY
                    k.waktu DESC, la.waktu DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $totalkonfirmasiflag = $stmt->rowCount();
            $konfirmasiflag = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_OBJ) : '';

            //logabsen
            $sql = 'SELECT
                    la.id as idlogabsen,
                    p.id as idpegawai,
                    p.nama,
                    IF(la.konfirmasi="l","' . trans('all.lokasitidakcocok') . '",IF(la.konfirmasi="f","' . trans('all.sampelwajahtidakcocok') . '",IF(la.konfirmasi="lf","' . trans('all.lokasitidakcocok') . ', ' . trans('all.sampelwajahtidakcocok') . '",""))) as konfirmasi,
                    DATE_FORMAT(la.waktu,"%d/%m/%Y %T") as waktu,
                    if(la.masukkeluar="m","<label class=\"label label-primary\">' . trans('all.masuk') . '</label>","<label class=\"label label-danger\">' . trans('all.keluar') . '</label>") as masukkeluar
                FROM
                    logabsen la,
                    pegawai p
                WHERE
                    la.idpegawai=p.id AND
                    p.del = "t" AND
                    la.status="c"' . $sqlWhere . '
                ORDER BY
                    la.waktu DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $totallogabsen = $stmt->rowCount();
            $logabsen = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_OBJ) : '';

            //ijintidakmasuk
            $sql = 'SELECT
                    itm.id as idijintidakmasuk,
                    p.id as idpegawai,
                    p.nama,
                    CONCAT(DATE_FORMAT(itm.tanggalawal,"%d/%m/%Y")," - ",DATE_FORMAT(itm.tanggalakhir,"%d/%m/%Y")) as waktu,
                    itm.tanggalawal,
                    itm.tanggalakhir,
                    itm.filename,
                    itm.keterangan
                FROM
                    ijintidakmasuk itm,
                    pegawai p
                WHERE
                    itm.idpegawai=p.id AND
                    p.del = "t" AND
                    itm.status="c"' . $sqlWhere . '
                ORDER BY
                    itm.tanggalakhir DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $totalijintidakmasuk = $stmt->rowCount();
            $ijintidakmasuk = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_OBJ) : '';

            $konfirmasi = $totalkonfirmasiflag + $totalijintidakmasuk + $totallogabsen;
            Session::set('conf_konfirmasi', $konfirmasi);
            return view('notif', ['jenis' => $jenis, 'konfirmasiflag' => $konfirmasiflag, 'logabsen' => $logabsen, 'ijintidakmasuk' => $ijintidakmasuk]);
        }
    }else{
        abort(404);
    }
});

Route::get('detailpesan/{id}', function($id){
    if (Auth::check()) {
        $pdo = DB::getPdo();

        $sql = 'UPDATE user_kotakpesan SET isread = "y" WHERE iduser = :iduser AND id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        $stmt->execute();

        $sql = 'SELECT pesan,DATE(inserted) as tanggal, TIME(inserted) as jam FROM user_kotakpesan WHERE iduser = :iduser AND id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_OBJ);
        $totaldata = $stmt->rowCount();

        return view('include/detailpesanpopup', ['data' => $data, 'totaldata' => $totaldata]);
    }
    abort(404);
});

// halaman beranda
Route::get('pencarian', 'BerandaController@pencarianKe');
Route::post('pencarian', 'BerandaController@pencarian');
Route::get('detailpencarian/{pencarian}/{id}', 'BerandaController@detailPencarian')->where(['id' => '[0-9]+']);
Route::get('datapencarianperusahaan/{pencarian}', 'BerandaController@dataPencarianPerusahaan');
Route::get('datapencarianpegawai/{pencarian}', 'BerandaController@dataPencarianPegawai');
Route::get('customdashboard/{id}/{tanggal}/{key?}', 'BerandaController@customDashboard')->where(['id' => '[0-9]+']);
Route::post('customdashboard/{id}/{tanggal}', 'BerandaController@customDashboardAtributFilter')->where(['id' => '[0-9]+']);
Route::get('sudahabsen/{tanggal}', 'BerandaController@sudahAbsen');
Route::post('sudahabsen/{tanggal}', 'BerandaController@sudahAbsenAtributFilter');
Route::get('belumabsen/{tanggal}', 'BerandaController@belumAbsen');
Route::post('belumabsen/{tanggal}', 'BerandaController@belumAbsenAtributFilter');
Route::get('terlambat/{tanggal}', 'BerandaController@terlambat');
Route::post('terlambat/{tanggal}', 'BerandaController@terlambatAtributFilter');
Route::get('datangawal/{tanggal}', 'BerandaController@datangAwal');
Route::post('datangawal/{tanggal}', 'BerandaController@datangAwalAtributFilter');
Route::get('adadikantor/{startfrom}', 'BerandaController@adaDiKantor');
Route::post('adadikantor/{tanggal}', 'BerandaController@adaDiKantorAtributFilter');
Route::get('totalpegawai/{jenis}/{startfrom}', 'BerandaController@totalPegawaiJenis');
Route::post('totalpegawai/{jenis}/{tanggal}', 'BerandaController@totalPegawaiJenisAtributFilter');
Route::get('ijintidakmasuk/{startfrom}', 'BerandaController@ijinTidakMasuk');
Route::post('ijintidakmasuk/{tanggal}', 'BerandaController@ijinTidakMasukAtributFilter');
Route::get('riwayat/{tanggal}', 'BerandaController@riwayat');
Route::post('riwayat/{tanggal}', 'BerandaController@riwayatAtributFilter');
Route::get('rekap/{tanggal}', 'BerandaController@rekap');
Route::post('rekap/{tanggal}', 'BerandaController@rekapAtributFilter');
Route::get('alasan/{tanggal}', 'BerandaController@alasan');
Route::post('alasan/{tanggal}', 'BerandaController@alasanAtributFilter');
Route::get('peta/{tanggal}', 'BerandaController@peta');
Route::post('peta/{tanggal}', 'BerandaController@petaAtributFilter');
Route::get('pulangawal/{tanggal}', 'BerandaController@pulangAwal');
Route::get('lembur/{tanggal}', 'BerandaController@lembur');
Route::get('peringkat/{jenis}/excel', 'BerandaController@peringkatExcel')->where(['jenis' => '(peringkatterbaik|peringkatterlambat|peringkatpulangawal|peringkatlamakerja|peringkatlamalembur)']);
Route::get('peringkat/{jenis}/{startfrom}', 'BerandaController@peringkat');
Route::post('peringkat/{jenis}/{startfrom}', 'BerandaController@peringkatAtributFilter');
Route::get('riwayatberanda', 'BerandaController@riwayatBeranda');
Route::get('riwayatberanda/{tanggal}/{startfrom}', 'BerandaController@riwayatBerandaData');
Route::post('pencariandetail', 'BerandaController@pencarianDetail');
Route::get('logabsenijintidakmasuk', 'BerandaController@logAbsenIjinTidakMasuk');
Route::get('notifdetail/{menu}', 'BerandaController@notifDetail');
Route::post('notifdetail/{menu}', 'BerandaController@submitFilterNotifDetail');
Route::get('lokasiabsen/{tanggal?}', function($tanggal=""){
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        if ($tanggal == '') {
            $tanggal = date('Ymd');
        }
        $sqlWhere = '';
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan != '') {
            $sqlWhere .= ' AND p.id IN ' . $batasan;
        }

        $sql = 'SELECT
                la.id,
                la.lat,
                la.lon as lng
            FROM
                logabsen la,
                pegawai p
            WHERE
                la.idpegawai=p.id AND
                la.waktu>=CONCAT(STR_TO_DATE(:tanggal01, "%Y%m%d")," 00:00:00") AND la.waktu<=CONCAT(STR_TO_DATE(:tanggal02, "%Y%m%d")," 23:59:59") AND
                la.status = "v" AND
                p.del = "t" AND
                ISNULL(la.lat)=false AND
                ISNULL(la.lon)=false AND
                la.lat<>0 AND
                la.lon<>0' . $sqlWhere;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggal01', $tanggal); //DATE(waktu)=STR_TO_DATE(:tanggal, "%Y%m%d") AND
        $stmt->bindValue(':tanggal02', $tanggal); //DATE(waktu)=STR_TO_DATE(:tanggal, "%Y%m%d") AND
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        return response()->json($data);
    }else{
        abort(404);
    }
});

Route::get('datacapture/{tanggal}', 'BerandaController@dataCapture');
Route::get('datacapture/detail/{tanggal}/{id}/{startfrom?}', 'BerandaController@dataCaptureDetail');
Route::get('pengaturan/customtv', 'customTvController@index');
Route::post('pengaturan/customtv', 'customTvController@submit');
Route::get('pengaturan/tv/{id}/detail', 'tvController@detail');
Route::post('pengaturan/tv/{id}/detail', 'tvController@submitDetail');
Route::get('pengaturan/givefotopegawai', function(){
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT
                idpegawai,filename
            FROM
                facesample
            WHERE
                idpegawai IN (SELECT id FROM pegawai WHERE isnull(checksum_img) = true OR checksum_img = "")
            GROUP BY idpegawai';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        try {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $id2folder = Utils::id2Folder($row['idpegawai']);
                $pathfacesample = Session::get('folderroot_perusahaan') . '/facesample/' . $id2folder . '/' . $row['idpegawai'] . '/' . $row['filename'];
                $pathfacesamplethumb = Session::get('folderroot_perusahaan') . '/facesample/' . $id2folder . '/' . $row['idpegawai'] . '/' . $row['filename'] . '_thumb';
                $pathfotopegawai = Session::get('folderroot_perusahaan') . '/pegawai/' . $id2folder . '/' . $row['idpegawai'];
                $pathfotopegawaithumb = Session::get('folderroot_perusahaan') . '/pegawai/' . $id2folder . '/' . $row['idpegawai'] . '_thumb';

                if (file_exists($pathfacesample) && file_exists($pathfacesamplethumb)) {
                    //jika tidak ada folder yg dituju, buat folder nya
                    if (!file_exists(Session::get('folderroot_perusahaan') . '/pegawai/' . $id2folder)) {
                        mkdir(Session::get('folderroot_perusahaan') . '/pegawai/' . $id2folder, 0777, true);
                    }

                    // \File::copy($pathfacesample, $pathfotopegawai);
                    // \File::copy($pathfacesamplethumb, $pathfotopegawaithumb);

                    if (!copy($pathfacesample, $pathfotopegawai)) {
//                    return 'gagal1';
                    }
                    if (!copy($pathfacesamplethumb, $pathfotopegawaithumb)) {
//                    return 'gagal2';
                    }

                    //return base_path('../folderroot/packages.json');
                    if (file_exists($pathfotopegawai)) {
                        $checksum = md5_file($pathfotopegawai);
                        $sql1 = 'UPDATE pegawai set checksum_img = :checksum WHERE id = :idpegawai';
                        $stmt1 = $pdo->prepare($sql1);
                        $stmt1->bindValue(':checksum', $checksum);
                        $stmt1->bindValue(':idpegawai', $row['idpegawai']);
                        $stmt1->execute();
                    }
                }
            }
        } catch (\Exception $e) {
            return $e;
        }
        return 'ok';
    }else{
        return '';
    }
});

//tambah tanggal
Route::get('sudahabsen/{tanggal}/{startfrom}', 'BerandaController@sudahAbsenPerTanggal');
Route::get('belumabsen/{tanggal}/{startfrom}', 'BerandaController@belumAbsenPerTanggal');
Route::get('terlambat/{tanggal}/{startfrom}', 'BerandaController@terlambatPerTanggal');
Route::get('datangawal/{tanggal}/{startfrom}', 'BerandaController@datangAwalPerTanggal');
Route::get('riwayat/{tanggal}/{startfrom}', 'BerandaController@riwayatPerTanggal');
Route::get('rekap/{tanggal}/{startfrom}', 'BerandaController@rekapPerTanggal');
Route::get('alasan/{tanggal}/{startfrom}', 'BerandaController@alasanPerTanggal');
Route::get('peta/{tanggal}/{startfrom}', 'BerandaController@petaPerTanggal');
Route::get('pulangawal/{tanggal}/{startfrom}', 'BerandaController@pulangAwalPerTanggal');
Route::get('lembur/{tanggal}/{startfrom}', 'BerandaController@lemburPerTanggal');
Route::get('datacapture/{tanggal}/{startfrom}', 'BerandaController@dataCapturePerTanggal');
Route::get('customdashboarddata/{id}/{tanggal?}/{key?}/{startfrom?}', 'BerandaController@customDashboardData');

//halaman urutan
Route::get('urutan/{menu}/{id?}', function($menu, $id = ''){
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $data = '';
        $breadcrumb = '';
        $url = '';
        if ($menu == 'agama') {
            $breadcrumb = '<li>' . trans('all.datainduk') . '</li><li>' . trans('all.pegawai') . '</li><li>' . trans('all.agama') . '</li>';
            $url = url('datainduk/pegawai/agama');
            $data = Utils::getData($pdo, 'agama', 'id,agama as nama', '', 'urutan');
        } else if ($menu == 'alasanmasukkeluar') {
            $breadcrumb = '<li>' . trans('all.datainduk') . '</li><li>' . trans('all.catatankehadiran') . '</li><li>' . trans('all.menu_alasanmasukkeluar') . '</li>';
            $url = url('datainduk/alasan/alasanmasukkeluar');
            $data = Utils::getData($pdo, 'alasanmasukkeluar', 'id,alasan as nama', '', 'urutan');
        } else if ($menu == 'alasantidakmasuk') {
            $breadcrumb = '<li>' . trans('all.datainduk') . '</li><li>' . trans('all.catatankehadiran') . '</li><li>' . trans('all.menu_alasantidakmasuk') . '</li>';
            $url = url('datainduk/alasan/alasantidakmasuk');
            $data = Utils::getData($pdo, 'alasantidakmasuk', 'id,alasan as nama', '', 'urutan');
        } else if ($menu == 'atributnilai') {
            $breadcrumb = '<li>' . trans('all.datainduk') . '</li><li>' . trans('all.kepegawaian') . '</li><li>' . trans('all.atributnilai') . '</li>';
            $url = url('datainduk/pegawai/atribut/' . $id . '/detail');
            $data = Utils::getData($pdo, 'atributnilai', 'id,nilai as nama', 'idatribut = ' . $id, 'urutan ASC, nama ASC');
        } else if ($menu == 'payrollkomponenmasterurutanperhitungan') {
            $breadcrumb = '<li>' . trans('all.datainduk') . '</li><li>' . trans('all.payroll') . '</li><li>' . trans('all.kelompok') . '</li><li>' . trans('all.payrollkomponenmaster') . '</li><li>' . trans('all.digunakan') . '</li>';
            $url = url('datainduk/payroll/payrollkelompok/' . $id . '/komponenmaster');
            $data = Utils::getData($pdo, 'payroll_komponen_master', 'id,CONCAT(nama," (",kode,")") as nama', 'idpayroll_kelompok = ' . $id, 'urutan_perhitungan ASC, nama ASC');
        } else if ($menu == 'payrollkomponenmasterurutantampilan') {
            $breadcrumb = '<li>' . trans('all.datainduk') . '</li><li>' . trans('all.payroll') . '</li><li>' . trans('all.kelompok') . '</li><li>' . trans('all.payrollkomponenmaster') . '</li><li>' . trans('all.tampilan') . '</li>';
            $url = url('datainduk/payroll/payrollkelompok/' . $id . '/komponenmaster');
            $data = Utils::getData($pdo, 'payroll_komponen_master', 'id,CONCAT(nama," (",kode,")") as nama', 'idpayroll_kelompok = ' . $id, 'urutan_tampilan ASC, nama ASC');
        } else if ($menu == 'laporankomponenmasterurutanperhitungan') {
            $breadcrumb = '<li>' . trans('all.laporan') . '</li><li>' . trans('all.custom') . '</li><li>' . trans('all.kelompok') . '</li><li>' . trans('all.payrollkomponenmaster') . '</li><li>' . trans('all.tampilan') . '</li>';
            $url = url('laporan/custom/kelompok/' . $id . '/komponenmaster');
            $data = Utils::getData($pdo, 'laporan_komponen_master', 'id,CONCAT(nama," (",kode,")") as nama', 'idlaporan_kelompok = ' . $id, 'urutan_perhitungan ASC, nama ASC');
        } else if ($menu == 'laporankomponenmasterurutantampilan') {
            $breadcrumb = '<li>' . trans('all.laporan') . '</li><li>' . trans('all.custom') . '</li><li>' . trans('all.kelompok') . '</li><li>' . trans('all.payrollkomponenmaster') . '</li><li>' . trans('all.digunakan') . '</li>';
            $url = url('laporan/custom/kelompok/' . $id . '/komponenmaster');
            $data = Utils::getData($pdo, 'laporan_komponen_master', 'id,CONCAT(nama," (",kode,")") as nama', 'idlaporan_kelompok = ' . $id, 'urutan_tampilan ASC, nama ASC');
        } else if ($menu == 'aktivitas') {
            $breadcrumb = '<li>' . trans('all.datainduk') . '</li><li>' . trans('all.kepegawaian') . '</li><li>' . trans('all.aktivitas') . '</li><li>' . trans('all.urutan') . '</li>';
            $url = url('datainduk/pegawai/aktivitas');
            $data = Utils::getData($pdo, 'aktivitas', 'id,pertanyaan as nama', 'idaktivitaskategori = ' . Session::get('aktivitas_idaktivitaskategori'), 'urutan ASC, nama ASC');
        }
        Utils::insertLogUser('akses menu urutan ' . $menu);
        return view('include/urutan', ['data' => $data, 'breadcrumb' => $breadcrumb, 'url' => $url, 'menu' => $menu == 'atributnilai' ? 'atribut' : $menu]);
    }else{
        abort(404);
    }
})->where(['menu' => '(agama|alasanmasukkeluar|alasantidakmasuk|atributnilai|payrollkomponenmasterurutanperhitungan|payrollkomponenmasterurutantampilan|laporankomponenmasterurutanperhitungan|laporankomponenmasterurutantampilan|aktivitas)']);

Route::post('urutan/{menu}/{id?}', function($menu, $id = ''){
    if (Auth::check()) {
        $idurutan = Request::input('idurutan');
        $url = Request::input('url');
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $totaldata = count($idurutan);
        $sqlurutan = 'urutan';
        if ($menu == 'payrollkomponenmasterurutanperhitungan') {
            $menu = 'payroll_komponen_master';
            $sqlurutan = 'urutan_perhitungan';
        } else if ($menu == 'payrollkomponenmasterurutantampilan') {
            $menu = 'payroll_komponen_master';
            $sqlurutan = 'urutan_tampilan';
        } else if ($menu == 'laporankomponenmasterurutanperhitungan') {
            $menu = 'laporan_komponen_master';
            $sqlurutan = 'urutan_perhitungan';
        } else if ($menu == 'laporankomponenmasterurutantampilan') {
            $menu = 'laporan_komponen_master';
            $sqlurutan = 'urutan_tampilan';
        }
        try {
            if ($totaldata > 0) {
                $where = '';
                if ($id != '' && $menu == 'atributnilai') {
                    //jika ada id nya adalah menu atributnilai jadi memakai idatribut
                    $where = ' AND idatribut = :idatribut';
                }
                for ($i = 0; $i < $totaldata; $i++) {
                    $urutan = $i + 1;
                    $sql = 'UPDATE ' . $menu . ' SET ' . $sqlurutan . ' = :urutan WHERE id = :idurutan ' . $where;
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':urutan', $urutan);
                    $stmt->bindValue(':idurutan', $idurutan[$i]);
                    if ($id != '' && $menu == 'atributnilai') {
                        $stmt->bindValue(':idatribut', $id);
                    }
                    $stmt->execute();
                }
            }
            Utils::insertLogUser('ubah urutan ' . $menu);
            $hasil = trans('all.databerhasildisimpan');
        } catch (\Exception $e) {
            $hasil = $e->getMessage();
        }
        return redirect($url)->with('message', $hasil);
    }else{
        abort(404);
    }
})->where(['menu' => '(agama|alasanmasukkeluar|alasantidakmasuk|atributnilai|payrollkomponenmasterurutanperhitungan|payrollkomponenmasterurutantampilan|laporankomponenmasterurutanperhitungan|laporankomponenmasterurutantampilan|aktivitas)']);

//logabsen tambah berdasarkan atribut
Route::get('datainduk/absensi/logabsen/createbyatribut', 'LogAbsenController@createByAtribut');
Route::post('datainduk/absensi/logabsen/submitcreatebyatribut', 'LogAbsenController@submitCreateByAtribut');
Route::post('datainduk/absensi/logabsen/submitperiode', 'LogAbsenController@submitPeriode');

//impor data pegawai
Route::get('datainduk/pegawai/pegawai/imporexcel', 'PegawaiController@imporExcel');
Route::post('datainduk/pegawai/pegawai/imporexcel', 'PegawaiController@submitImporExcel');

//halaman tab
Route::get('datainduk/pegawai/atribut/perlakuanlembur', 'AtributController@perlakuanLembur');
Route::post('datainduk/pegawai/atribut/perlakuanlembur', 'AtributController@perlakuanLemburSubmit');
Route::get('datainduk/pegawai/atribut/perlakuanlembur/{sisi}/index-data/{util?}', 'AtributController@perlakuanLemburData')->where(['sisi' => '(kanan|kiri)', 'perlakuanlembur' => '(|tanpalembur|konfirmasi|lembur|[0-9]+)']);

//halaman pekerjaan item(pekerjaan kategori detail)
Route::get('datainduk/pegawai/pekerjaanitem/{idpekerjaankategori}', 'PekerjaanItemController@getIndex');
Route::post('datainduk/pegawai/pekerjaanitem/{idpekerjaankategori}/index-data', 'PekerjaanItemController@data');
Route::post('datainduk/pegawai/pekerjaanitem/{idpekerjaankategori}', 'PekerjaanItemController@store');
Route::get('datainduk/pegawai/pekerjaanitem/{idpekerjaankategori}/create', 'PekerjaanItemController@create');
Route::get('datainduk/pegawai/pekerjaanitem/{idpekerjaankategori}/{id}/edit', 'PekerjaanItemController@edit');
Route::post('datainduk/pegawai/pekerjaanitem/{idpekerjaankategori}/{id}/edit', 'PekerjaanItemController@update');
Route::post('datainduk/pegawai/pekerjaanitem/{idpekerjaankategori}/{id}/hapus', 'PekerjaanItemController@destroy');
Route::get('datainduk/pegawai/pekerjaanitem/{idpekerjaankategori}/excel', 'PekerjaanItemController@excel');

// halaman ekspor
Route::get('pengelolas/excel', 'PengelolaController@excel');
Route::get('perusahaan/excel/download', 'PerusahaanController@excel');
Route::get('pegawai/excel', 'PegawaiController@excel');
Route::get('agama/excel', 'AgamaController@excel');
Route::get('aktivitas/excel', 'AktivitasController@excel');
Route::get('aktivitaskategori/excel', 'AktivitasKategoriController@excel');
Route::get('pekerjaankategori/excel', 'PekerjaanKategoriController@excel');
Route::get('pekerjaaninput/excel', 'PekerjaanInputController@excel');
Route::get('atributvariable/excel', 'AtributVariableController@excel');
Route::get('atribut/excel', 'AtributController@excel');
Route::get('atributdetail/excel/{id}', 'AtributNilaiController@excel')->where(['id' => '[0-9]+']);
Route::get('lokasi/excel', 'LokasiController@excel');
Route::get('alasanmasukkeluar/excel', 'AlasanMasukKeluarController@excel');
Route::get('alasantidakmasuk/excel', 'AlasanTidakMasukController@excel');
Route::get('mesin/excel', 'MesinController@excel');
Route::get('indexlembur/excel', 'IndexLemburController@excel');
Route::get('indexjamkerja/excel', 'indexJamKerjaController@excel');
Route::get('fingerprintconnector/excel', 'FingerprintConnectorController@excel');
Route::get('jamkerja/excel', 'JamKerjaController@excel');
Route::get('jamkerjakategori/excel', 'JamKerjaKategoriController@excel');
Route::get('jamkerjashiftjenis/excel', 'JamKerjaShiftJenisController@excel');
Route::get('jamkerjafull/excel/{id}', 'JamKerjaFullController@excel')->where(['id' => '[0-9]+']);
Route::get('jamkerjashift/excel/{id}', 'JamKerjaShiftController@excel')->where(['id' => '[0-9]+']);
Route::get('jamkerjashiftdetail/excel/{id}', 'JamKerjaShiftDetailController@excel')->where(['id' => '[0-9]+']);
Route::get('jamkerjakhusus/excel', 'JamKerjaKhususController@excel');
Route::get('harilibur/excel', 'HariLiburController@excel');
// Route::get('datainduk/absensi/ijintidakmasukexcel', 'IjinTidakMasukController@excel');
Route::get('hakakses/excel', 'HakAksesController@excel');
Route::get('logabsen/excel', 'LogAbsenController@excel');
Route::get('payrollkelompok/excel', 'PayrollKelompokController@excel');
Route::get('payrollkelompokatribut/{id}/excel', 'PayrollKelompokAtributController@excel');
Route::get('payrollkomponenmastergroup/excel', 'PayrollKomponenMasterGroupController@excel');
Route::get('payrollkomponenmaster/{id}/excel', 'PayrollKomponenMasterController@excel');
Route::get('payrollkomponeninputmanual/excel', 'PayrollKomponenInputManualController@excel');
Route::get('payrollposting/excel/{id}', 'PayrollPostingController@excel');
Route::get('payrollposting/pdf/{id}', 'PayrollPostingController@pdf');
//Route::get('payrollslipgaji/excel/{id}', 'PayrollSlipGajiController@excel');
//Route::get('payrollslipgaji/pdf/{id}', 'PayrollSlipGajiController@pdf');
Route::get('jadwalshift/excel', 'JadwalShiftController@excel');
Route::get('slideshow/excel', 'SlideShowController@excel');
Route::get('batasanatribut/excel', 'BatasanAtributController@excel');
Route::get('batasanemail/excel', 'BatasanEmailController@excel');
Route::get('jamkerjakhusus/{idjamkerjakhusus}/detail/excel', 'JamKerjaKhususController@excelDetail')->where(['idjamkerjakhusus' => '[0-9]+']);
Route::get('konfirmasiflag/excel', 'konfirmasiFlagController@excel');
Route::get('pengaturan/tv/excel', 'tvController@excel');
Route::get('pengaturan/tvgroup/excel', 'tvGroupController@excel');
Route::get('pengaturan/tvdetail/excel', 'tvDetailController@excel');
Route::get('pengaturan/customdashboard/excel', 'customDashboardController@excel');
Route::get('pengaturan/customdashboardnode/excel', 'customDashboardNodeController@excel');
Route::get('pengaturan/customdashboardemail/excel', 'customDashboardEmailController@excel');
Route::get('laporan/custom/kelompok/excel', 'LaporanKelompokController@excel');
Route::get('laporan/custom/komponenmastergroup/excel', 'LaporanKomponenMasterGroupController@excel');
Route::get('laporan/custom/kelompok/{idkelompok}/komponenmaster/excel', 'LaporanKomponenMasterController@excel');
Route::get('laporan/custom/kelompok/{idkelompok}/atribut/excel', 'LaporanKelompokAtributController@excel');
Route::get('datainduk/payroll/slipgaji/excel', 'SlipGajiController@excel');

//url load data datatable
Route::post('perusahaan/index-data', 'PerusahaanController@show');
Route::post('pengelola/index-data', 'PengelolaController@show');
Route::post('pekerjaaninput/index-data', 'PekerjaanInputController@show');
Route::post('datainduk/pegawai/atributvariable/index-data', 'AtributVariableController@show');
Route::post('datainduk/pegawai/atribut/index-data', 'AtributController@show');
Route::post('datainduk/pegawai/atribut/{idatribut}/detail/index-data', 'AtributNilaiController@show')->where(['idatribut' => '[0-9]+']);
Route::post('datainduk/pegawai/lokasi/index-data', 'LokasiController@show');
Route::post('datainduk/pegawai/pegawai/index-data', 'PegawaiController@show');
Route::post('datainduk/pegawai/aktivitas/index-data', 'AktivitasController@show');
Route::post('datainduk/pegawai/aktivitaskategori/index-data', 'AktivitasKategoriController@show');
Route::post('datainduk/pegawai/agama/index-data', 'AgamaController@show');
Route::post('datainduk/pegawai/pekerjaankategori/index-data', 'PekerjaanKategoriController@show');
Route::post('datainduk/pegawai/pekerjaanitem/{id}/index-data', 'PekerjaanKategoriController@dataDetail');
Route::post('datainduk/alasan/alasanmasukkeluar/index-data', 'AlasanMasukKeluarController@show');
Route::post('datainduk/alasan/alasantidakmasuk/index-data', 'AlasanTidakMasukController@show');
Route::post('datainduk/absensi/mesin/index-data', 'MesinController@show');
Route::post('datainduk/absensi/indexlembur/index-data', 'IndexLemburController@show');
Route::post('datainduk/absensi/indexjamkerja/index-data', 'IndexJamKerjaController@show');
Route::post('datainduk/absensi/fingerprintconnector/index-data', 'FingerprintConnectorController@show');
Route::post('datainduk/absensi/jamkerja/index-data', 'JamKerjaController@show');
Route::post('datainduk/absensi/jamkerjakategori/index-data', 'JamKerjaKategoriController@show');
Route::post('datainduk/absensi/jamkerjashiftjenis/index-data', 'JamKerjaShiftJenisController@show');
Route::post('datainduk/absensi/jamkerja/{id}/full/index-data', 'JamKerjaFullController@show');
Route::post('datainduk/absensi/jamkerja/{id}/shift/index-data', 'JamKerjaShiftController@show')->where(['id' => '[0-9]+']);
Route::post('datainduk/absensi/jamkerja/{id}/shift/{idjamkerjashift}/detail/index-data', 'JamKerjaShiftDetailController@show')->where(['id' => '[0-9]+', 'idjamkerjashift' => '[0-9]+']);
Route::post('datainduk/absensi/jamkerjakhusus/index-data', 'JamKerjaKhususController@show');
Route::post('datainduk/absensi/harilibur/index-data', 'HariLiburController@show');
Route::post('datainduk/absensi/ijintidakmasuk/index-data', 'IjinTidakMasukController@show');
Route::post('datainduk/absensi/logabsen/index-data', 'LogAbsenController@show');
Route::post('datainduk/payroll/payrollkelompok/index-data', 'PayrollKelompokController@show');
Route::post('datainduk/payroll/payrollkelompok/{id}/komponenmaster/index-data', 'PayrollKomponenMasterController@show');
Route::post('datainduk/payroll/payrollkelompok/{id}/atribut/index-data', 'PayrollKelompokAtributController@show');
Route::post('datainduk/payroll/payrollkomponenmastergroup/index-data', 'PayrollKomponenMasterGroupController@show');
Route::post('pengaturan/slideshow/index-data', 'SlideShowController@show');
Route::post('pengaturan/tv/index-data', 'tvController@show');
Route::post('pengaturan/tvgroup/index-data', 'tvGroupController@show');
Route::post('pengaturan/tvdetail/index-data', 'tvDetailController@show');
Route::post('pengaturan/customdashboard/index-data', 'customDashboardController@show');
Route::post('pengaturan/customdashboardnode/index-data', 'customDashboardNodeController@show');
Route::post('pengaturan/customdashboardemail/index-data', 'customDashboardEmailController@show');
Route::post('datainduk/lainlain/batasanemail/index-data', 'BatasanEmailController@show');
Route::post('datainduk/lainlain/batasanatribut/index-data', 'BatasanAtributController@show');
Route::post('datainduk/lainlain/hakakses/index-data', 'HakAksesController@show');
Route::post('datainduk/lainlain/hapusdata/{menu}/index-data', 'hapusDataController@data')->where(['menu' => '(pegawai|mesin)']);
Route::post('laporan/riwayat/penggunaweb/index-data', 'LaporanController@riwayatPenggunaWebData');
Route::post('laporan/riwayat/pegawai/index-data', 'LaporanController@riwayatPegawaiData');
Route::post('laporan/riwayat/sms/index-data', 'LaporanController@riwayatSmsData');
Route::post('logtrackerpegawai/index-data', 'LogTrackerPegawaiController@show');
Route::post('laporan/perlokasi/index-data', 'LaporanController@perLokasiData');
Route::post('laporan/pekerjaaninput/index-data', 'LaporanController@pekerjaanInputData');
Route::post('laporan/custom/kelompok/index-data', 'LaporanKelompokController@show');
Route::post('laporan/custom/komponenmastergroup/index-data', 'LaporanKomponenMasterGroupController@show');
Route::post('laporan/custom/kelompok/{idkelompok}/komponenmaster/index-data', 'LaporanKomponenMasterController@show');
Route::post('laporan/custom/kelompok/{idkelompok}/atribut/index-data', 'LaporanKelompokAtributController@show');
Route::post('datainduk/payroll/slipgaji/index-data', 'SlipGajiController@show');
Route::get('datainduk/payroll/slipgaji/{id}/komponenmaster/{jenis}', 'SlipGajiController@komponenMasterData');
Route::get('datainduk/payroll/slipgaji/{id}/pegawai/{jenis}', 'SlipGajiController@pegawaiData');

//halaman CRUD
Route::group(['middlewares' =>['web']], function () {
	Route::resources([
		//pengelola
        'pengelola' => 'PengelolaController',

        'pekerjaaninput' => 'PekerjaanInputController',
        //datainduk
        'perusahaan' => 'PerusahaanController',
        //pegawai
        //'datainduk/pegawai/pegawai' => 'PegawaiController',
        'datainduk/pegawai/agama' => 'AgamaController',
        // 'datainduk/pegawai/atribut' => 'AtributController',
        'datainduk/pegawai/atributvariable' => 'AtributVariableController',
        'datainduk/pegawai/lokasi' => 'LokasiController',
        'datainduk/pegawai/pekerjaankategori' => 'PekerjaanKategoriController',
        'datainduk/pegawai/aktivitas' => 'AktivitasController',
        'datainduk/pegawai/aktivitaskategori' => 'AktivitasKategoriController',
        //alasan
        'datainduk/alasan/alasanmasukkeluar' => 'AlasanMasukKeluarController',
        'datainduk/alasan/alasantidakmasuk' => 'AlasanTidakMasukController',
        //absensi
        // 'datainduk/absensi/mesin' => 'MesinController',
        'datainduk/absensi/indexlembur' => 'IndexLemburController',
        'datainduk/absensi/indexjamkerja' => 'IndexJamKerjaController',
        'datainduk/absensi/fingerprintconnector' => 'FingerprintConnectorController',
        //'datainduk/absensi/jamkerja' => 'JamKerjaController',
        'datainduk/absensi/jamkerjakategori' => 'JamKerjaKategoriController',
        'datainduk/absensi/jamkerjashiftjenis' => 'JamKerjaShiftJenisController',
        'datainduk/absensi/jamkerjakhusus' => 'JamKerjaKhususController',
        'datainduk/absensi/jamkerjakhususIstirahat' => 'JamKerjaKhususIstirahatController',
        'datainduk/absensi/harilibur' => 'HariLiburController',
        'datainduk/absensi/ijintidakmasuk' => 'IjinTidakMasukController',
        'datainduk/absensi/logabsen' => 'LogAbsenController',
        //payroll
        'datainduk/payroll/payrollkelompok' => 'PayrollKelompokController',
        'datainduk/payroll/payrollkomponenmastergroup' => 'PayrollKomponenMasterGroupController',
        'datainduk/payroll/slipgaji' => 'SlipGajiController',
//        'datainduk/payroll/payrollkomponenmaster' => 'PayrollKomponenMasterController',
        //lainlain
        'datainduk/lainlain/batasanemail' => 'BatasanEmailController',
        'datainduk/lainlain/batasanatribut' => 'BatasanAtributController',
        'datainduk/lainlain/hakakses' => 'HakAksesController',
        //pengaturan
        'pengaturan/slideshow' => 'SlideShowController',
        'pengaturan/tv' => 'tvController',
        'pengaturan/tvgroup' => 'tvGroupController',
        'pengaturan/tvdetail' => 'tvDetailController',
        'pengaturan/customdashboard' => 'customDashboardController',
        'pengaturan/customdashboardnode' => 'customDashboardNodeController',
        'pengaturan/customdashboardemail' => 'customDashboardEmailController',

        // laporan custom
        'laporan/custom/kelompok' => 'LaporanKelompokController',
        'laporan/custom/komponenmastergroup' => 'LaporanKomponenMasterGroupController',

        'datainduk/pengguna' => 'PenggunaController',
	]);


    Route::resource('datainduk/absensi/jamkerja', 'JamKerjaController', ['except' => ['index']]);
    Route::get('datainduk/absensi/jamkerja', 'JamKerjaController@showIndex');
	Route::resource('datainduk/pegawai/pegawai', 'PegawaiController', ['except' => ['index']]);
    Route::get('datainduk/pegawai/pegawai', 'PegawaiController@showIndex');
    Route::resource('datainduk/pegawai/atribut', 'AtributController', ['except' => ['index']]);
    Route::get('datainduk/pegawai/atribut', 'AtributController@showIndex');

	//crud halaman yang pakai detail
	Route::resource('datainduk/absensi/jamkerja/{id}/full', 'JamKerjaFullController', ['except' => ['index']]);
	Route::resource('datainduk/absensi/jamkerja/{id}/shift', 'JamKerjaShiftController', ['except' => ['index']]);
	Route::resource('datainduk/absensi/jamkerja/{id}/shift/{idjamkerjashift}/detail', 'JamKerjaShiftDetailController', ['except' => ['index']]);
	Route::resource('datainduk/pegawai/atribut/{id}/detail', 'AtributNilaiController', ['except' => ['index']]);
	Route::resource('datainduk/payroll/payrollkelompok/{id}/komponenmaster', 'PayrollKomponenMasterController', ['except' => ['index']]);
    Route::resource('datainduk/payroll/payrollkelompok/{id}/atribut', 'PayrollKelompokAtributController', ['except' => ['index']]);

    Route::resource('laporan/custom/kelompok/{id}/komponenmaster', 'LaporanKomponenMasterController', ['except' => ['index']]);
    Route::resource('laporan/custom/kelompok/{id}/atribut', 'LaporanKelompokAtributController', ['except' => ['index']]);
});

//payroll
Route::get('datainduk/payroll/payrollpengaturan', 'PayrollPengaturanController@index');
Route::post('datainduk/payroll/payrollpengaturan', 'PayrollPengaturanController@submit');
Route::get('datainduk/payroll/payrollpengaturan/hapusfile', 'PayrollPengaturanController@hapusFile');
Route::get('datainduk/payroll/payrollkelompok/hapustemplate/{id}/{jenis}', 'PayrollKelompokController@hapusTemplate');
Route::get('datainduk/payroll/payrollposting', 'PayrollPostingController@index');
Route::post('datainduk/payroll/payrollposting', 'PayrollPostingController@submit');
Route::get('datainduk/payroll/payrollposting/generatepayroll', 'PayrollPostingController@generate');
Route::post('datainduk/payroll/payrollposting/generatepayroll', 'PayrollPostingController@generateSubmit');
Route::get('datainduk/payroll/payrollposting/{id}/hapus', 'PayrollPostingController@delete');
Route::get('datainduk/payroll/payrollslipgaji', 'PayrollSlipGajiController@index');
Route::post('datainduk/payroll/payrollslipgaji', 'PayrollSlipGajiController@submit');
Route::post('datainduk/payroll/payrollslipgaji/generate', 'PayrollSlipGajiController@generate');
//Route::get('datainduk/payroll/payrollkomponenmaster/{id}/script', 'PayrollKomponenMasterController@script');
//Route::post('datainduk/payroll/payrollkomponenmaster/{id}/script', 'PayrollKomponenMasterController@scriptSubmit');
Route::get('datainduk/payroll/payrollkelompok/{idpayrollkelompok}/komponenmaster/{id}/script', 'PayrollKomponenMasterController@script');
Route::post('datainduk/payroll/payrollkelompok/{idpayrollkelompok}/komponenmaster/{id}/script', 'PayrollKomponenMasterController@scriptSubmit');
Route::get('datainduk/payroll/slipgaji/{id}/komponenmaster', 'SlipGajiController@komponenMaster');
Route::post('datainduk/payroll/slipgaji/{id}/komponenmaster', 'SlipGajiController@submitKomponenMaster');
Route::get('datainduk/payroll/slipgaji/{id}/pegawai', 'SlipGajiController@pegawai');
Route::post('datainduk/payroll/slipgaji/{id}/pegawai', 'SlipGajiController@submitPegawai');
Route::get('datainduk/payroll/slipgajiekspor', 'SlipGajiEksporController@index');
Route::post('datainduk/payroll/slipgajiekspor', 'SlipGajiEksporController@excel');
Route::get('datainduk/payroll/slipgaji/hapustemplate/{id}', 'SlipGajiController@hapusTemplate');

// laporan custom
Route::get('laporan/custom/kelompok/{idlaporankelompok}/komponenmaster/{id}/script', 'LaporanKomponenMasterController@script');
Route::post('laporan/custom/kelompok/{idlaporankelompok}/komponenmaster/{id}/script', 'LaporanKomponenMasterController@scriptSubmit');
Route::get('laporan/custom/ekspor', 'LaporanCustomEksporController@index');
Route::post('laporan/custom/ekspor', 'LaporanCustomEksporController@submit');

//submit filter tahun menu harilibur,ijintidakmasuk,logabsen,jamkerjakhususfilter
Route::post('datainduk/abensi/hariliburfilter', 'HariLiburController@submit');
Route::post('datainduk/abensi/ijintidakmasukfilter', 'IjinTidakMasukController@submit');
Route::get('datainduk/abensi/jamkerjakhususfilter/{tahun}', 'JamKerjaKhususController@submit');

// menu jadwalshift
Route::get('datainduk/absensi/jadwalshift', 'JadwalShiftController@getindex');
Route::get('datainduk/absensi/jadwalshift/setulang', function(){
    if (Auth::check()) {
        if (Session::has('jadwalshift_bulan')) {
            Session::forget('jadwalshift_bulan');
        }
        if (Session::has('jadwalshift_tahun')) {
            Session::forget('jadwalshift_tahun');
        }

        return redirect('datainduk/absensi/jadwalshift');
    }else{
        abort(404);
    }
});
Route::post('datainduk/absensi/jadwalshift', 'JadwalShiftController@submit');
Route::post('datainduk/absensi/jadwalshift/submit', 'JadwalShiftController@submitDetailPopup');
Route::get('datainduk/absensi/jadwalshiftdetail/{idpegawai}/{tanggal}/{idjamkerja}', 'JadwalShiftController@detailPopup')->where(['idpegawai' => '[0-9]+', 'idjamkerja' => '[0-9]+']);
Route::get('datainduk/absensi/jadwalshiftperbulan/{idpegawai}/{fullscreen?}', 'JadwalShiftController@popupPerBulan')->where(['idpegawai' => '[0-9]+']);
Route::post('datainduk/absensi/jadwalshiftperbulan/{idpegawai}', 'JadwalShiftController@popupPerBulanSubmit')->where(['idpegawai' => '[0-9]+']);
Route::get('datainduk/absensi/jadwalshift/excel', 'JadwalShiftController@excel');
Route::get('datainduk/absensi/jadwalshift/templateexcel', 'JadwalShiftController@templateExcel');
Route::post('datainduk/absensi/jadwalshift/importexcel', 'JadwalShiftController@importExcel');

// index halaman yang pakai detail
Route::resource('datainduk/absensi/mesin', 'MesinController', ['except' => ['index']]);
Route::get('datainduk/absensi/mesin', 'MesinController@showIndex');
Route::get('datainduk/pegawai/lokasi/{id}/area', 'LokasiController@area')->where(['id' => '[0-9]+']);
Route::post('datainduk/pegawai/lokasi/{id}/submitarea', 'LokasiController@submitArea')->where(['id' => '[0-9]+']);
Route::get('datainduk/absensi/jamkerja/{idjamkerja}/full', 'JamKerjaFullController@getindex')->where(['idjamkerja' => '[0-9]+']);
Route::get('datainduk/absensi/jamkerja/{idjamkerja}/shift', 'JamKerjaShiftController@getindex')->where(['idjamkerja' => '[0-9]+']);
Route::get('datainduk/absensi/mesin/{id}/{jenis}', 'MesinController@verifikasi')->where(['id' => '[0-9]+', 'jenis' => '(verifikasi|putussambungan)']);
Route::put('datainduk/absensi/mesin/{id}/submitverifikasi', 'MesinController@submitVerifikasi')->where(['id' => '[0-9]+']);
Route::get('mesin/{id}/fingerprint', 'MesinController@pengaturanFingerPrint')->where(['id' => '[0-9]+']);
Route::post('mesin/{id}/fingerprint', 'MesinController@submitPengaturanFingerPrint')->where(['id' => '[0-9]+']);
Route::post('mesin/fingerprint/importpegawai', 'MesinController@importPegawaiFingerPrint');
Route::post('mesin/fingerprint/importsemuapegawai', 'MesinController@importSemuaPegawaiFingerPrint');
Route::post('mesin/fingerprint/importfingersample', 'MesinController@importFingerSampleFingerPrint');
Route::post('mesin/fingerprint/importsemuafingersample', 'MesinController@importSemuaFingerSampleFingerPrint');
Route::get('datainduk/absensi/fingerptintconnector/{id}/resetpassword', 'FingerprintConnectorController@resetPassword')->where(['id' => '[0-9]+']);
Route::get('datainduk/pegawai/atribut/{id}/detail', 'AtributNilaiController@getindex')->where(['id' => '[0-9]+']);
Route::get('datainduk/absensi/jamkerja/{idjamkerja}/shift/{id}/detail', 'JamKerjaShiftDetailController@getindex')->where(['idjamkerja' => '[0-9]+', 'id' => '[0-9]+']);
Route::get('pengaturan/slideshow/{id}/detail', 'SlideShowController@getDetail')->where(['id' => '[0-9]+']);
Route::post('pengaturan/slideshow/{id}/detail', 'SlideShowController@submitDetail')->where(['id' => '[0-9]+']);
Route::get('pengaturan/slideshow/export/excel', 'SlideShowController@excel');
Route::get('pengaturan/customdashboard/{id}/detail', 'customDashboardController@detail')->where(['id' => '[0-9]+']);
Route::post('pengaturan/customdashboard/{id}/detail/submit', 'customDashboardController@submitDetail')->where(['id' => '[0-9]+']);
Route::get('datainduk/payroll/payrollkelompok/{idpayrollkelompok}/komponenmaster', 'PayrollKomponenMasterController@getindex')->where(['id' => '[0-9]+']);
Route::get('datainduk/payroll/payrollkelompok/{idpayrollkelompok}/atribut', 'PayrollKelompokAtributController@getindex')->where(['id' => '[0-9]+']);
Route::get('laporan/custom/kelompok/{idkelompok}/komponenmaster', 'LaporanKomponenMasterController@getindex')->where(['id' => '[0-9]+']);
Route::get('laporan/custom/kelompok/{idkelompok}/atribut', 'LaporanKelompokAtributController@getindex')->where(['id' => '[0-9]+']);

//detail pegawai jam kerja
Route::get('datainduk/pegawai/pegawai/jamkerja/{id}', 'PegawaiController@jamKerja')->where(['id' => '[0-9]+']);
Route::post('datainduk/pegawai/pegawai/jamkerja/{id}/data', 'PegawaiController@jamKerjaData')->where(['id' => '[0-9]+']);
Route::get('datainduk/pegawai/pegawai/jamkerja/{id}/tambah', 'PegawaiController@tambahjamKerja')->where(['id' => '[0-9]+']);
Route::post('datainduk/pegawai/pegawai/jamkerja/submittambah', 'PegawaiController@submitTambahJamKerja');
Route::get('datainduk/pegawai/pegawai/jamkerja/{id}/ubah/{idjamkerjapegawai}', 'PegawaiController@ubahjamKerja')->where(['id' => '[0-9]+', 'idjamkerjapegawai' => '[0-9]+']);
Route::post('datainduk/pegawai/pegawai/jamkerja/submitubah', 'PegawaiController@submitUbahJamKerja');
Route::get('datainduk/pegawai/pegawai/jamkerja/hapus/{id}', 'PegawaiController@hapusJamKerja')->where(['id' => '[0-9]+']);
Route::get('datainduk/pegawai/pegawai/jamkerja/excel/{idpegawai}', 'PegawaiController@excelJamKerja')->where(['idpegawai' => '[0-9]+']);

//menu tukar shift dan keperluannya
Route::get('datainduk/absensi/tukarshift', 'TukarShiftController@getindex');
Route::post('datainduk/absensi/tukarshift/submit', 'TukarShiftController@submit');
Route::post('datainduk/absensi/tukarshift/submittampilkan', 'TukarShiftController@submitTampilkan');
Route::get('jamkerjashiftpegawai/{idpegawai}/{tanggal}/{dari}', 'TukarShiftController@jamKerjaPegawai')->where(['idpegawai' => '[0-9]+']);
Route::post('datainduk/absensi/tukarshift/step1', 'TukarShiftController@submitStep1');
Route::post('datainduk/absensi/tukarshift/submit', 'TukarShiftController@submit');

//koreksi shift
Route::get('datainduk/absensi/koreksishift', 'TukarShiftController@koreksiShiftgetIndex');
Route::get('datainduk/absensi/koreksishift', 'TukarShiftController@koreksiShiftgetIndex');
Route::post('datainduk/absensi/koreksishift/submittampilkan', 'TukarShiftController@koreksiShiftSubmitTampilkan');
Route::post('datainduk/absensi/koreksishift/submit', 'TukarShiftController@koreksiShiftSubmit');

//menu cuti
Route::get('datainduk/absensi/cuti', 'CutiController@index');
Route::post('datainduk/absensi/cuti', 'CutiController@submit');
Route::post('datainduk/absensi/cuti/submitsimpan', 'CutiController@submitSimpan');
Route::get('datainduk/absensi/cuti/excel', 'CutiController@excel');
Route::get('datainduk/absensi/cuti/setulang', function(){
    if (Auth::check()) {
        if (Session::has('cuti_tahun')) {
            Session::forget('cuti_tahun');
        }
        if (Session::has('cuti_atribut')) {
            Session::forget('cuti_atribut');
        }
        return redirect('datainduk/absensi/cuti');
    }else{
        abort(404);
    }
});

//menu jam kerja pegawai
Route::get('datainduk/absensi/jamkerjapegawai', 'JamKerjaPegawaiController@showIndex');
Route::get('datainduk/absensi/jamkerjapegawai/aturatribut/{atributnilai}/{jamkerja?}', 'JamKerjaPegawaiController@aturAtribut');
Route::post('datainduk/absensi/jamkerjapegawai/{jenis}', 'JamKerjaPegawaiController@dataPegawai')->where(['jenis' => '(pegawai|pegawaijamkerja)']);
Route::post('datainduk/absensi/jamkerjapegawai/delete', 'JamKerjaPegawaiController@hapusJamKerja');
Route::get('datainduk/absensi/getpegawaijamkerja/{idjamkerja}/{berlakumulai}', 'JamKerjaPegawaiController@pilihJamKerja')->where(['idjamkerja' => '[0-9]+']);
Route::post('datainduk/absensi/jamkerjapegawai/submit', 'JamKerjaPegawaiController@submitJamKerja');
Route::post('datainduk/absensi/jamkerjapegawai/hapus', 'JamKerjaPegawaiController@hapusJamKerjaTerpilih');
Route::get('datainduk/absensi/jamkerjapegawai/setulang', function(){
    if (Auth::check()) {
        Session::forget('jamkerjapegawai_idjamkerja');
        Session::forget('jamkerjapegawai_filteridjamkerja');
        Session::forget('jamkerjapegawai_berlakumulai');
        Session::forget('jamkerjapegawai_keterangan');
        Session::forget('jamkerjapegawai_idatribut');
        Session::forget('jamkerjapegawai_atribut');
        return redirect('datainduk/absensi/jamkerjapegawai');
    }else{
        abort(404);
    }
});

// menu datainduk/payroll/payrollkomponeninputmanual
Route::get('datainduk/payroll/payrollkomponeninputmanual', 'PayrollKomponenInputManualController@index');
Route::get('datainduk/payroll/payrollkomponeninputmanual/excel', 'PayrollKomponenInputManualController@excel');
Route::post('datainduk/payroll/payrollkomponeninputmanual/nextstep', 'PayrollKomponenInputManualController@nextstep');
Route::get('datainduk/payroll/payrollkomponeninputmanual/nextstep', 'PayrollKomponenInputManualController@nextstepGet');
Route::get('datainduk/payroll/payrollkomponeninputmanual/{idpayrollkomponenmaster}/ubah', 'PayrollKomponenInputManualController@data');
Route::get('datainduk/payroll/payrollkomponeninputmanual/{idpayrollkomponenmaster}/hapus', 'PayrollKomponenInputManualController@dataHapus');
Route::post('datainduk/payroll/payrollkomponeninputmanual/submitsimpan', 'PayrollKomponenInputManualController@submitSimpan');
Route::get('datainduk/payroll/payrollkomponeninputmanual/setulang', function(){
    if (Auth::check()) {
        if (Session::has('payrollkomponeninputmanual_bulan')) {
            Session::forget('payrollkomponeninputmanual_bulan');
        }
        if (Session::has('payrollkomponeninputmanual_tahun')) {
            Session::forget('payrollkomponeninputmanual_tahun');
        }
        if (Session::has('payrollkomponeninputmanual_atribut')) {
            Session::forget('payrollkomponeninputmanual_atribut');
        }
        if (Session::has('payrollkomponeninputmanual_payrollkomponenmaster')) {
            Session::forget('payrollkomponeninputmanual_payrollkomponenmaster');
        }
        return Redirect::back();
    }else{
        abort(404);
    }
});

// menu laporan/custom/komponeninputmanual
Route::get('laporan/custom/komponeninputmanual', 'LaporanKomponenInputManualController@index');
Route::get('laporan/custom/komponeninputmanual/excel', 'LaporanKomponenInputManualController@excel');
Route::post('laporan/custom/komponeninputmanual/nextstep', 'LaporanKomponenInputManualController@nextstep');
Route::get('laporan/custom/komponeninputmanual/nextstep', 'LaporanKomponenInputManualController@nextstepGet');
Route::get('laporan/custom/komponeninputmanual/{idlaporankomponenmaster}/ubah', 'LaporanKomponenInputManualController@data');
Route::get('laporan/custom/komponeninputmanual/{idlaporankomponenmaster}/hapus', 'LaporanKomponenInputManualController@dataHapus');
Route::post('laporan/custom/komponeninputmanual/submitsimpan', 'LaporanKomponenInputManualController@submitSimpan');
Route::get('laporan/custom/komponeninputmanual/setulang', function(){
    if (Auth::check()) {
        if (Session::has('laporankomponeninputmanual_bulan')) {
            Session::forget('laporankomponeninputmanual_bulan');
        }
        if (Session::has('laporankomponeninputmanual_tahun')) {
            Session::forget('laporankomponeninputmanual_tahun');
        }
        if (Session::has('laporankomponeninputmanual_atribut')) {
            Session::forget('laporankomponeninputmanual_atribut');
        }
        if (Session::has('laporankomponeninputmanual_laporankomponenmaster')) {
            Session::forget('laporankomponeninputmanual_laporankomponenmaster');
        }
        return Redirect::back();
    }else{
        abort(404);
    }
});

//menu datainduk/lainlain/hapusdata
Route::get('datainduk/lainlain/hapusdata/{menu}', 'hapusDataController@indexPage')->where(['menu' => '(pegawai|mesin)']);
Route::get('datainduk/lainlain/hapusdata/{menu}/hapus/{id}', 'hapusDataController@hapus')->where(['menu' => '(pegawai|mesin)', 'id' => '[0-9]+']);
Route::get('datainduk/lainlain/hapusdata/{menu}/restore/{id}', 'hapusDataController@restore')->where(['menu' => '(pegawai|mesin)', 'id' => '[0-9]+']);
Route::get('datainduk/lainlain/hapusdata/{menu}/excel', 'hapusDataController@excel')->where(['id' => '(pegawai|mesin)']);

//get totalpegawai perusahaan yang dipilih
Route::get('gettotalpegawai', function(){
    if (Auth::check()) {
        $hasil = '';
        if (Session::has('conf_webperusahaan')) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT COUNT(id) as totalpegawai FROM pegawai WHERE del = "t"';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil = $row['totalpegawai'];
        }
        return $hasil;
    }else{
        abort(404);
    }
});

// halaman pengaturan, ajakan, lainlain (halaman nonCRUD)
Route::get('ajakan/{ajakan}', 'OthersController@ajakan');
Route::post('ajakan/{ajakan}', 'OthersController@submitajakan');
Route::delete('ajakan/{ajakan}/{id}', 'OthersController@hapusajakan')->where(['id' => '[0-9]+']);
Route::get('ajakan/{ajakan}/{id}/{param}', 'OthersController@manipulasiajakan')->where(['id' => '[0-9]+']);
Route::get('bugsreport', 'OthersController@bugsreport');
Route::get('pengaturan/umum', 'OthersController@pengaturan');
Route::post('pengaturan/umum', 'OthersController@submitpengaturan');
Route::get('pengaturan/peringkat', 'OthersController@peringkat');
Route::post('pengaturan/peringkat', 'OthersController@submitPeringkat');
Route::post('pengaturan/peringkat/hitungperingkat', 'OthersController@hitungPeringkat');
Route::get('pengaturan/formatsmsabsen', 'OthersController@formatSmsAbsen');
Route::post('pengaturan/formatsmsabsen', 'OthersController@formatSmsAbsenSubmit');
Route::get('pengaturan/formatsmsverifikasi', 'OthersController@formatSmsVerifikasi');
Route::post('pengaturan/formatsmsverifikasi', 'OthersController@formatSmsVerifikasiSubmit');
Route::get('pengaturan/formatsmslupapwdpegawai', 'OthersController@formatSmsLupaPwdPegawai');
Route::post('pengaturan/formatsmslupapwdpegawai', 'OthersController@formatSmsLupaPwdPegawaiSubmit');
Route::get('pengaturan/parameterekspor', 'OthersController@parameterEkspor');
Route::post('pengaturan/parameterekspor', 'OthersController@submitParameterEkspor');
Route::get('datainduk/lainlain/postingdata', 'OthersController@postingdata');
Route::post('datainduk/lainlain/postingdata', 'OthersController@submitpostingdata');
Route::get('datainduk/lainlain/setulangkatasandipegawai', 'OthersController@setulangkatasandipegawai');
Route::post('datainduk/lainlain/setulangkatasandipegawai', 'OthersController@submitsetulangkatasandipegawai');
Route::get('datainduk/pegawai/facesample', 'OthersController@facesample');
Route::post('datainduk/pegawai/facesample', 'OthersController@submitFacesample');
Route::get('datainduk/pegawai/facesample/export/excel', 'OthersController@excelFacesample');
Route::get('facesample/loadmore/{startfrom}', 'OthersController@loadmoreFacesample');
Route::get('datainduk/pegawai/facesample/all/{idpegawai}', 'OthersController@facesamplePegawai')->where(['idpegawai' => '[0-9]+']);
Route::get('datainduk/pegawai/facesample/setulang', function(){
    if (Auth::check()) {
        if (Session::has('facesample_search')) {
            Session::forget('facesample_search');
        }
        if (Session::has('facesample_atributnilai')) {
            Session::forget('facesample_atributnilai');
        }

        return redirect('datainduk/pegawai/facesample');
    }else{
        abort(404);
    }
});
Route::post('dataindfuk/absensi/submitjadwalshift', 'JadwalShiftController@submit');
Route::get('datainduk/pegawai/resetkatasandi/{id}', 'PegawaiController@resetKataSandi')->where(['id' => '[0-9]+']);
Route::get('datainduk/pegawai/pegawai/{jenis}/{id}', 'PegawaiController@sample')->where(['id' => '[0-9]+', 'jenis' => '(facesample|fingerprint)']);
Route::get('datainduk/pegawai/facesample/{id}', 'PegawaiController@getFaceSample')->where(['id' => '[0-9]+']);
Route::get('datainduk/pegawai/facesample/delete/{id}', 'PegawaiController@deleteFaceSample')->where(['id' => '[0-9]+']);
Route::get('datainduk/pegawai/facesample/deleteall/{id}', 'PegawaiController@deleteAllFaceSample')->where(['id' => '[0-9]+']);
Route::get('fingerprint/ubahstatus/{idpegawai}/{id}', function($idpegawai,$id){
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM fingersample WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {

            $sql = 'UPDATE fingersample SET deleted = NOW() WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

            $namapegawai = Utils::getNamaPegawai($idpegawai);

            Utils::insertLogUser('hapus fingerprint "' . $namapegawai . '"');

            $msg = trans('all.statustelahdiubahmenjaditerhapus');
        } else {
            $msg = trans('all.datatidakditemukan');
        }
        return redirect('datainduk/pegawai/pegawai/fingerprint/' . $idpegawai)->with('message', $msg);
    }else{
        abort(404);
    }
});

Route::get('facesample/delete/{id}/{dari}', 'OthersController@deleteFaceSample')->where(['id' => '[0-9]+']);
Route::get('rekap/deletefacesample/{id}/{tanggal}', 'OthersController@deleteFaceSampleRekap')->where(['id' => '[0-9]+']);
Route::get('detail/rekap/{id}', 'BerandaController@detailRekap')->where(['id' => '[0-9]+']);
Route::get('detailriwayatpresensi/{id}', 'BerandaController@detailRiwayatPresensi')->where(['id' => '[0-9]+']);
Route::get('detailrekappresensi/{id}', 'BerandaController@detailRekapPresensi')->where(['id' => '[0-9]+']);
Route::get('datainduk/absensi/jamkerjakhusus/{idjamkerjakhusus}/detail', 'JamKerjaKhususController@detail')->where(['idjamkerjakhusus' => '[0-9]+']);
Route::post('datainduk/absensi/jamkerjakhusus/{idjamkerjakhusus}/detail', 'JamKerjaKhususController@submitTambahData')->where(['idjamkerjakhusus' => '[0-9]+']);
Route::get('datainduk/absensi/jamkerjakhusus/{idjamkerjakhusus}/detail/index-data', 'JamKerjaKhususController@detailData')->where(['idjamkerjakhusus' => '[0-9]+']);
Route::get('datainduk/absensi/jamkerjakhusus/{idjamkerjakhusus}/detailtambah/index-data/{idatirbut}', 'JamKerjaKhususController@detailTambahData')->where(['idjamkerjakhusus' => '[0-9]+', 'idatribut' => '[0-9]+']);
Route::get('datainduk/absensi/jamkerjakhusus/{idjamkerjakhusus}/delete/{id}', 'JamKerjaKhususController@deleteData')->where(['idjamkerjakhusus' => '[0-9]+', 'id' => '[0-9]+']);

Route::get('datainduk/pegawai/pegawai/aturatributdanlokasi/aturatribut', 'aturAtributController@index');
Route::get('datainduk/pegawai/pegawai/aturatributdanlokasi/aturatribut/aturatribut/{idatributnilai}', 'aturAtributController@aturAtribut');
Route::get('datainduk/pegawai/pegawai/aturatributdanlokasi/aturatribut/getatributnilai/{idatribut}', 'aturAtributController@getAtributnilai')->where(['idatribut' => '[0-9]+']);
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/aturatribut/pegawai', 'aturAtributController@dataPegawai');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/aturatribut/pegawaiatribut/{idatributnilai}', 'aturAtributController@dataPegawaiAtribut');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/aturatribut/submitsetatribut', 'aturAtributController@submitSetAtribut');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/aturatribut/hapusatribut', 'aturAtributController@hapusAtribut');

Route::get('datainduk/pegawai/pegawai/aturatributdanlokasi/aturlokasi', 'aturLokasiController@index');
Route::get('datainduk/pegawai/pegawai/aturatributdanlokasi/aturlokasi/aturatribut/{idatributnilai}', 'aturLokasiController@aturAtribut');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/aturlokasi/pegawai', 'aturLokasiController@dataPegawai');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/aturlokasi/pegawailokasi/{idlokasi}', 'aturLokasiController@dataPegawaiLokasi');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/aturlokasi/submitsetlokasi', 'aturLokasiController@submitSetLokasi');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/aturlokasi/hapuslokasi', 'aturLokasiController@hapusLokasi');

Route::get('datainduk/pegawai/pegawai/aturatributdanlokasi/aturflexytime', 'aturFlexytimeController@index');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/aturflexytime/index-data/{flexytime}', 'aturFlexytimeController@dataPegawai')->where(['flexytime' => '(y|t)']);
Route::get('datainduk/pegawai/pegawai/aturatributdanlokasi/aturflexytime/aturatribut/{idatributnilai}', 'aturFlexytimeController@aturAtribut');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/aturflexytime/submit', 'aturFlexytimeController@submit');

Route::get('datainduk/pegawai/pegawai/aturatributdanlokasi/ijinkansambungdatacapture', 'ijinkanSambungDataCaptureController@index');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/ijinkansambungdatacapture/index-data/{ijinkansambungdatacapture}', 'ijinkanSambungDataCaptureController@dataPegawai')->where(['ijinkansambungdatacapture' => '(y|t)']);
Route::get('datainduk/pegawai/pegawai/aturatributdanlokasi/ijinkansambungdatacapture/aturatribut/{idatributnilai}', 'ijinkanSambungDataCaptureController@aturAtribut');
Route::post('datainduk/pegawai/pegawai/aturatributdanlokasi/ijinkansambungdatacapture/submit', 'ijinkanSambungDataCaptureController@submit');

Route::get('datainduk/absensi/konfirmasiflag', 'konfirmasiFlagController@index');
Route::post('datainduk/absensi/konfirmasiflag', 'konfirmasiFlagController@submitFilter');
Route::post('datainduk/absensi/konfirmasiflag/konfirmasidataterpilih', 'konfirmasiFlagController@konfirmasiDataTerpilih');
Route::get('datainduk/absensi/konfirmasiflag/setkonfirmasi/{id}', 'konfirmasiFlagController@setKonfirmasi');
Route::post('datainduk/absensi/konfirmasiflag/index-data', 'konfirmasiFlagController@show');
Route::get('logtrackerpegawai', 'LogTrackerPegawaiController@index');
Route::post('logtrackerpegawai', 'LogTrackerPegawaiController@submitIndex');
Route::get('logtrackerpegawai/reset', 'LogTrackerPegawaiController@reset');
Route::get('logtrackerpegawairealtime', 'LogTrackerPegawaiRealTimeController@index');
Route::post('logtrackerpegawairealtime', 'LogTrackerPegawaiRealTimeController@submitIndex');
Route::get('logtrackerpegawairealtime/data', 'LogTrackerPegawaiRealTimeController@getData');

Route::post('datainduk/pegawai/aktivitas/submitfilter', 'AktivitasController@submitFilter');

//setulang atur atribut dan lokasi dan flexytime
Route::get('datainduk/pegawai/pegawai/aturatributdanlokasi/setulang/{jenis}', function($jenis){
    if (Auth::check()) {
        if (Session::has($jenis . '_idatribut')) {
            Session::forget($jenis . '_idatribut');
        }
        if (Session::has($jenis . '_atribut')) {
            Session::forget($jenis . '_atribut');
        }
        return Redirect::back();
    }else{
        abort(404);
    }
});

// halaman laporan
Route::get('laporan/perpegawai', 'LaporanController@perPegawai');
Route::get('laporan/perpegawai/data/{idpegawai}/{tanggalawal}/{tanggalakhir}', 'LaporanController@perPegawaiSubmitTampilan')->where(['idpegawai' => '[0-9]+']);
Route::post('laporan/perpegawai/riwayat/{idpegawai}', 'LaporanController@perPegawaiRiwayatData')->where(['idpegawai' => '[0-9]+']);
Route::post('laporan/perpegawai/rekapitulasi/{idpegawai}', 'LaporanController@perPegawaiRekapitulasiData')->where(['idpegawai' => '[0-9]+']);
Route::post('laporan/perpegawai/ijintidakmasuk/{idpegawai}', 'LaporanController@perPegawaiIjinTidakMasukData')->where(['idpegawai' => '[0-9]+']);
Route::post('laporan/perpegawai/jadwalshift/{idpegawai}', 'LaporanController@perPegawaiJadwalShiftData')->where(['idpegawai' => '[0-9]+']);
Route::get('laporan/perpegawai/excel/{idpegawai}', 'LaporanController@perPegawaiExcel')->where(['idpegawai' => '[0-9]+']);
// Route::get('laporan/perpegawai/pdf/{idpegawai}', 'LaporanController@perPegawaiPDF')->where(['idpegawai' => '[0-9]+']);
Route::get('laporan/logabsen', 'LaporanController@logabsen');
Route::post('laporan/logabsen', 'LaporanController@submitLogAbsen');
Route::get('laporan/logabsen/excel', 'LaporanController@excelLogabsen');
Route::post('laporan/logabsen/index-data', 'LaporanController@logabsenData');
Route::get('laporan/terbaik', 'LaporanController@terbaik');
Route::post('laporan/terbaik', 'LaporanController@submitTerbaik');
Route::get('laporan/terbaik/excel', 'LaporanController@excelTerbaik');
Route::post('laporan/terbaik/index-data', 'LaporanController@terbaikData');
Route::get('laporan/kehadiran', 'LaporanController@kehadiran');
Route::post('laporan/kehadiran', 'LaporanController@submitKehadiran');
Route::get('laporan/kehadiran/excel', 'LaporanController@excelKehadiran');
Route::post('laporan/kehadiran/index-data', 'LaporanController@kehadiranData');
Route::get('laporan/terlambat', 'LaporanController@terlambat');
Route::post('laporan/terlambat', 'LaporanController@submitTerlambat');
Route::get('laporan/terlambat/excel', 'LaporanController@excelTerlambat');
Route::post('laporan/terlambat/index-data', 'LaporanController@terlambatData');
Route::get('laporan/pulangawal', 'LaporanController@pulangAwal');
Route::post('laporan/pulangawal', 'LaporanController@submitPulangAwal');
Route::get('laporan/pulangawal/excel', 'LaporanController@excelPulangAwal');
Route::post('laporan/pulangawal/index-data', 'LaporanController@pulangAwalData');
//Route::get('laporan/rekapitulasi', 'LaporanController@rekapitulasi');
//Route::post('laporan/rekapitulasi', 'LaporanController@submitRekapitulasi');
//Route::post('laporan/rekapitulasi/index-data', 'LaporanController@rekapitulasiData');
//Route::get('laporan/rekapitulasi/excel', 'LaporanController@excelRekapitulasi');
Route::get('laporan/pertanggal', 'LaporanController@perTanggal');
Route::post('laporan/pertanggal', 'LaporanController@submitPerTanggal');
Route::post('laporan/pertanggal/index-data', 'LaporanController@perTanggalData');
Route::get('laporan/pertanggal/excel', 'LaporanController@excelPerTanggal');
Route::get('laporan/rekapabsen', 'LaporanController@rekapAbsen');
Route::post('laporan/rekapabsen/excel', 'LaporanController@rekapAbsenSubmit');
Route::get('laporan/riwayat/penggunaweb', 'LaporanController@riwayatPenggunaWeb');
Route::post('laporan/riwayat/penggunaweb', 'LaporanController@submitRiwayatPenggunaWeb');
Route::get('laporan/riwayat/penggunaweb/excel', 'LaporanController@excelRiwayatPenggunaWeb');
//Route::get('laporan/riwayat/penggunamobile', 'LaporanController@riwayatPenggunaMobile');
//Route::post('laporan/riwayat/penggunamobile', 'LaporanController@submitRiwayatPenggunaMobile');
//Route::get('laporan/riwayat/penggunamobile/index-data', 'LaporanController@riwayatPenggunaMobileData');
Route::get('laporan/riwayat/pengguna/excel', 'LaporanController@excelRiwayatpenggunaMobile');
Route::get('laporan/riwayat/pegawai', 'LaporanController@riwayatPegawai');
Route::post('laporan/riwayat/pegawai', 'LaporanController@submitRiwayatPegawai');
Route::get('laporan/riwayat/pegawai/excel', 'LaporanController@excelRiwayatPegawai');
Route::get('laporan/riwayat/sms', 'LaporanController@riwayatSms');
Route::post('laporan/riwayat/sms', 'LaporanController@submitRiwayatSms');
Route::get('laporan/riwayat/sms/excel', 'LaporanController@excelRiwayatSms');
Route::get('laporan/lainnya/harilibur', 'LaporanController@hariLibur');
Route::post('laporan/lainnya/harilibur', 'LaporanController@hariLiburSubmit');
Route::post('laporan/lainnya/harilibur/index-data', 'LaporanController@hariLiburData');
Route::get('laporan/lainnya/harilibur/excel', 'LaporanController@excelHariLibur');
Route::get('laporan/lainnya/prosentaseabsen', 'LaporanGrafikController@prosentaseAbsen');
Route::post('laporan/lainnya/prosentaseabsen', 'LaporanGrafikController@submitProsentaseAbsen');
Route::get('laporan/lainnya/prosentaseabsen/excel', 'LaporanGrafikController@excelProsentaseAbsen');
Route::get('laporan/lainnya/pegawai', 'LaporanController@laporanEksporPegawai');
Route::post('laporan/lainnya/pegawai', 'LaporanController@submitLaporanEksporPegawai');
Route::get('laporan/lainnya/pegawai/excel', 'LaporanController@excelLaporanEksporPegawai');
Route::get('laporan/lainnya/{jenis}', 'LaporanController@laporanPresensi')->where(['jenis' => '(terlambat|pulangawal|belumabsenmasuk|belumabsenpulang|masuktanpajadwal)']);
Route::post('laporan/lainnya/{jenis}', 'LaporanController@submitLaporanPresensi')->where(['jenis' => '(terlambat|pulangawal|belumabsenmasuk|belumabsenpulang|masuktanpajadwal)']);
Route::post('laporan/lainnya/{jenis}/index-data', 'LaporanController@laporanPresensiData')->where(['jenis' => '(terlambat|pulangawal|belumabsenmasuk|belumabsenpulang|masuktanpajadwal)']);
Route::get('laporan/lainnya/{jenis}/excel', 'LaporanController@excelLaporanPresensi');
Route::get('laporan/rekapshift', 'LaporanController@rekapShift');
Route::post('laporan/rekapshift', 'LaporanController@submitRekapShift');
Route::post('laporan/rekapshift/index-data', 'LaporanController@rekapShiftData');
Route::get('laporan/rekapshift/excel', 'LaporanController@excelRekapShift');
Route::get('laporan/perlokasi', 'LaporanController@perLokasi');
Route::post('laporan/perlokasi', 'LaporanController@perLokasiSubmit');
Route::get('laporan/perlokasi/excel', 'LaporanController@excelPerLokasi');
Route::get('laporan/pekerjaaninput', 'LaporanController@pekerjaanInput');
Route::post('laporan/pekerjaaninput', 'LaporanController@submitPekerjaanInput');
Route::get('laporan/pekerjaaninput/excel', 'LaporanController@excelPekerjaanInput');
Route::get('laporan/aktivitas', 'LaporanController@aktivitas');
Route::post('laporan/aktivitas', 'LaporanController@submitAktivitas');
Route::get('laporan/aktivitas/excel', 'LaporanController@excelAktivitas');
Route::post('laporan/aktivitas/index-data', 'LaporanController@aktivitasData');
Route::get('laporan/aktivitas/excel', 'LaporanController@aktivitasExcel');

//halaman detail laporan
Route::get('laporanperpegawai/detailrekapitulasi/{idrekapabsen}', function($idrekapabsen){
    if (Auth::check()) {
        $tanggal = Utils::tanggalCantik(Utils::getDataWhere(DB::connection('perusahaan_db')->getPdo(), 'rekapabsen', 'tanggal', 'id', $idrekapabsen), 'panjang');
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //hasil
        $sql = 'SELECT
              IFNULL(jks.namashift,"") as shift,
              DATE_FORMAT(rah.waktu,"%d/%m/%Y %T") as waktu,
              rah.masukkeluar
            FROM
              rekapabsen_hasil rah
              LEFT JOIN jamkerjashift jks ON rah.idjamkerjashift=jks.id
            WHERE
              rah.idrekapabsen = :idrekapabsen
            ORDER BY
              rah.waktu DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idrekapabsen', $idrekapabsen);
        $stmt->execute();
        $datahasil = $stmt->fetchAll(PDO::FETCH_OBJ);

        //jadwal
        $sql = 'SELECT
              IFNULL(jks.namashift,"") as shift,
              DATE_FORMAT(raj.waktu,"%d/%m/%Y %T") as waktu,
              raj.masukkeluar,
              raj.checking,
              raj.shiftpertamaterakhir,
              raj.shiftsambungan
            FROM
              rekapabsen_jadwal raj
              LEFT JOIN jamkerjashift jks ON raj.idjamkerjashift=jks.id
            WHERE
              raj.idrekapabsen = ' . $idrekapabsen . '
            ORDER BY
              raj.waktu DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idrekapabsen', $idrekapabsen);
        $stmt->execute();
        $datajadwal = $stmt->fetchAll(PDO::FETCH_OBJ);

        //logabsen
        $sql = 'SELECT
                  DATE_FORMAT(ral.waktu,"%d/%m/%Y %T") as waktu,
                  ral.masukkeluar,
                  a.alasan,
                  ral.terhitungkerja
                FROM
                  rekapabsen_logabsen ral
                  LEFT JOIN alasanmasukkeluar a ON ral.idalasan=a.id
                WHERE
                  ral.idrekapabsen = ' . $idrekapabsen . '
                ORDER BY
                  ral.waktu DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idrekapabsen', $idrekapabsen);
        $stmt->execute();
        $datalogabsen = $stmt->fetchAll(PDO::FETCH_OBJ);

        Utils::insertLogUser('akses menu laporan perpegawai detail rekapitulasi');

        return view('laporan/perpegawai/detailrekapitulasi', ['idrekapabsen' => $idrekapabsen, 'tanggal' => $tanggal, 'datahasil' => $datahasil, 'datajadwal' => $datajadwal, 'datalogabsen' => $datalogabsen]);
    }else{
        abort(404);
    }
})->where(['idrekapabsen' => '[0-9]+']);
//Route::post('laporanperpegawai/detailrekapitulasi/{jenis}/{idrekapabsen}/index-data', function($jenis, $idrekapabsen){
//    if($jenis == 'hasil') {
//        $sql = 'SELECT
//                  IFNULL(jks.namashift,"") as shift,
//                  rah.waktu,
//                  rah.masukkeluar
//                FROM
//                  rekapabsen_hasil rah
//                  LEFT JOIN jamkerjashift jks ON rah.idjamkerjashift=jks.id
//                WHERE
//                  rah.idrekapabsen = ' . $idrekapabsen . '
//                ORDER BY
//                  rah.waktu DESC';
//        $data = DB::connection('perusahaan_db')->table(DB::raw("($sql) as data"));
//        return Datatables::of($data)
//        ->editColumn('masukkeluar', '<center>@if($masukkeluar=="m") <span class="label label-info">' . trans('all.masuk') . '</span> @else <span class="label label-warning">' . trans('all.keluar') . '</span> @endif</center>')
//        ->make(true);
//    }else if($jenis == 'jadwal'){
//        $sql = 'SELECT
//                  IFNULL(jks.namashift,"") as shift,
//                  raj.waktu,
//                  raj.masukkeluar,
//                  raj.checking,
//                  raj.shiftpertamaterakhir,
//                  raj.shiftsambungan
//                FROM
//                  rekapabsen_jadwal raj
//                  LEFT JOIN jamkerjashift jks ON raj.idjamkerjashift=jks.id
//                WHERE
//                  raj.idrekapabsen = ' . $idrekapabsen . '
//                ORDER BY
//                  raj.waktu DESC';
//        $data = DB::connection('perusahaan_db')->table(DB::raw("($sql) as data"));
//        return Datatables::of($data)
//        ->editColumn('masukkeluar', '<center>@if($masukkeluar=="m") <span class="label label-info">' . trans('all.masuk') . '</span> @else <span class="label label-warning">' . trans('all.keluar') . '</span> @endif</center>')
//        ->editColumn('shiftpertamaterakhir', function($data){
//            return '<center><span class="label label-info">' . trans('all.'.$data->shiftpertamaterakhir) . '</span></center>';
//        })
//        ->make(true);
//    }else if($jenis == 'logabsen'){
//        $sql = 'SELECT
//                  ral.waktu,
//                  ral.masukkeluar,
//                  a.alasan,
//                  ral.terhitungkerja
//                FROM
//                  rekapabsen_logabsen ral
//                  LEFT JOIN alasanmasukkeluar a ON ral.idalasan=a.id
//                WHERE
//                  ral.idrekapabsen = ' . $idrekapabsen . '
//                ORDER BY
//                  ral.waktu DESC';
//        $data = DB::connection('perusahaan_db')->table(DB::raw("($sql) as data"));
//        return Datatables::of($data)
//        ->editColumn('masukkeluar', '<center>@if($masukkeluar=="m") <span class="label label-info">' . trans('all.masuk') . '</span> @else <span class="label label-warning">' . trans('all.keluar') . '</span> @endif</center>')
//        ->editColumn('terhitungkerja', '<center>@if($terhitungkerja=="m") <span class="label label-info">' . trans('all.ya') . '</span> @else <span class="label label-warning">' . trans('all.tidak') . '</span> @endif</center>')
//        ->make(true);
//    }
//})->where(['idrekapabsen' => '[0-9]+']);

//set ulang laporan
Route::get('laporan/setulang/{laporan}', function($laporan){
    if (Auth::check()) {
        $jenislaporan = array('logabsen', 'rekapitulasi', 'rekapitulasishift', 'shift', 'pertanggal', 'riwayatpenggunaweb', 'riwayatpenggunamobile', 'riwayatpegawai', 'riwayatsms', 'kehadiran', 'rekapshift', 'pekerjaaninput', 'prosentaseabsen', 'aktivitas');
        if (in_array($laporan, $jenislaporan)) {
            if (Session::has('lap' . $laporan . '_atribut')) {
                Session::forget('lap' . $laporan . '_atribut');
            }

            if ($laporan == 'logabsen' or $laporan == 'rekapitulasi' or $laporan == 'rekapitulasishift' or $laporan == 'shift' or $laporan == 'riwayatpenggunaweb' or $laporan == 'riwayatpenggunamobile' or $laporan == 'riwayatpegawai' or $laporan == 'riwayatsms' or $laporan == 'kehadiran' or $laporan == 'terlambat' or $laporan == 'pulangawal' or $laporan == 'rekapshift' or $laporan == 'pekerjaaninput' or $laporan == 'prosentaseabsen' or $laporan == 'aktivitas') {
                if (Session::has('lap' . $laporan . '_tanggalawal')) {
                    Session::forget('lap' . $laporan . '_tanggalawal');
                }
                if (Session::has('lap' . $laporan . '_tanggalakhir')) {
                    Session::forget('lap' . $laporan . '_tanggalakhir');
                }
            }

            if ($laporan == 'kehadiran') {
                if (Session::has('lapkehadiran_jenis')) {
                    Session::forget('lapkehadiran_jenis');
                }
            }

            if ($laporan == 'pertanggal') {
                if (Session::has('lappertanggal_bulan')) {
                    Session::forget('lappertanggal_bulan');
                }
                if (Session::has('lappertanggal_tahun')) {
                    Session::forget('lappertanggal_tahun');
                }
            }

            if ($laporan == 'aktivitas') {
                if (Session::has('lapaktivitas_idaktivitaskategori')) {
                    Session::forget('lapaktivitas_idaktivitaskategori');
                }
                if (Session::has('lapaktivitas_tanggal')) {
                    Session::forget('lapaktivitas_tanggal');
                }
                if (Session::has('lapaktivitas_atribut')) {
                    Session::forget('lapaktivitas_atribut');
                }
            }

            if ($laporan == 'riwayatpenggunaweb' or $laporan == 'riwayatpenggunamobile' or $laporan == 'riwayatpegawai' or $laporan == 'riwayatsms') {
                return redirect('laporan/riwayat' . '/' . str_replace('riwayat', '', $laporan));
            } else if ($laporan == 'prosentaseabsen') {
                return redirect('laporan/lainnya/' . $laporan);
            } else {
                return redirect('laporan/' . $laporan);
            }
        } else {
            abort(404);
        }
    }else{
        abort(404);
    }
});
Route::get('laporan/log', 'LaporanController@excelLog'); //belum fix
Route::get('laporan/baru', 'LaporanController@excelBaru'); //belum fix

//halaman laporan grafik
Route::get('laporangrafik/logabsen', 'LaporanGrafikController@logAbsen');
Route::post('laporangrafik/logabsen', 'LaporanGrafikController@submitLogAbsen');
Route::get('laporangrafik/prosentaseabsen', 'LaporanGrafikController@prosentaseAbsen');
Route::post('laporangrafik/prosentaseabsen', 'LaporanGrafikController@submitProsentaseAbsen');
Route::get('laporangrafik/prosentaseabsen/excel', 'LaporanGrafikController@excelProsentaseAbsen');

//menu sinkronisasi
Route::get('sinkronisasi', function(){
    if (Auth::check()) {
        // Set POST variables
        //$url = 'https://gcm-http.googleapis.com/gcm/send';
        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = array(
            'Authorization: key=' . config('consts.GCM_API_KEY'),
            'Content-Type: application/json'
        );

        $fields = array(
            'content_available' => true,
            'to' => '/topics/mesin_' . Session::get('conf_webperusahaan'),
            'data' => array(
                'cmd' => 'sync',
                'sender' => 'server',
                'msg' => ''
            )
        );

        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);

        Utils::insertLogUser('Sinkronisasi');

        return Redirect::back();
    }else{
        abort(404);
    }
});

//halaman cek nama pegawai
Route::get('ceknamapegawai/{id}/{nama}', function($id,$nama){
    $pdo = DB::connection('perusahaan_db')->getPdo();
    $response = array();
    $response['msg'] = '';
    if($id != 0){
        $sql = 'SELECT id FROM pegawai WHERE id<>'.$id.' AND nama="'.$nama.'" AND del = "t" LIMIT 1';
    }else{
        $sql = 'SELECT id FROM pegawai WHERE nama="'.$nama.'" AND del = "t" LIMIT 1';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    if ($stmt->rowCount()>0) {
        $response['msg'] = 'sama';
    }
    return $response;
})->where(['id' => '[0-9]+']);

//cek limit pegawai
Route::get('ceklimitpegawai', function(){
    if (Auth::check()) {
        $hasil = Utils::cekPegawaiJumlah();
        $response = array();
        $response['msg'] = $hasil == true ? 't' : 'y';;
        return $response;
    }else{
        abort(404);
    }
});

Route::get('getatributnilai/combobox/{idatribut}/{idatributcompare?}', function($idatribut, $idatributcompare=''){
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,nilai FROM atributnilai WHERE idatribut = :idatribut';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idatribut', $idatribut);
        $stmt->execute();
        $hasil = '<option value=""></option>';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $selected = '';
            if ($row['id'] == $idatributcompare) {
                $selected = 'selected';
            }
            $hasil .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['nilai'] . '</option>';
        }
        return $hasil;
    }else{
        abort(404);
    }
})->where(['idatribut' => '[0-9]+']);

//halaman percobaan kirim email
//Route::get('kirimemail', 'OthersController@sendMail');

//popup
Route::get('detailpegawai/{idpegawai}', 'OthersController@detailPegawai')->where(['idpegawai' => '[0-9]+']);
Route::get('detailvoicepegawai/{idpegawai}', 'PegawaiController@detailVoicePegawai')->where(['idpegawai' => '[0-9]+']);
Route::get('datainduk/pegawai/pegawai/buatulangvoice/{idpegawai}', 'PegawaiController@detailVoicePegawaiRebuild')->where(['idpegawai' => '[0-9]+']);
Route::get('logabsen/{idpegawai}/{startfrom}/{yymm}', 'BerandaController@logAbsenPegawai')->where(['idpegawai' => '[0-9]+']);
Route::get('rekapabsen/{idpegawai}/{startfrom}/{yymm}', 'BerandaController@rekapAbsenPegawai')->where(['idpegawai' => '[0-9]+']);
Route::get('flaglogabsen/{idpegawai}/{idlogabsen}/{menu}', 'BerandaController@flagLogAbsen')->where(['idpegawai' => '[0-9]+', 'idlogabsen' => '[0-9]+']);
Route::post('flaglogabsen/submit', 'BerandaController@flagLogAbsenSubmit');
Route::post('modalmarkerpeta', 'BerandaController@popupMarkerPeta');
Route::get('detailkonfirmasiabsen/{jenis}/{idkonfirmasiabsen}/{menu}', 'BerandaController@detailKonfirmasiAbsen')->where(['idkonfirmasiabsen' => '[0-9]+']);
Route::get('submitkonfirmasiabsen/{jenis}/{status}/{idkonfirmasiabsen}/{dari?}', 'BerandaController@submitDetailKonfirmasiAbsen')->where(['idkonfirmasiabsen' => '[0-9]+']);
Route::post('submitkonfirmasiabsenterpilih/{status}', 'BerandaController@submitDetailKonfirmasiAbsenTerpilih');
Route::post('submitkonfirmasiabsen', 'BerandaController@submitKonfirmasiAbsen');
Route::get('jamkerjapegawai/{tanggal}/{jenis}/{id}/{more?}', 'BerandaController@jamKerjaPegawai')->where(['jenis' => '(sudahabsen|belumabsen)', 'id' => '[0-9]+', 'more' => '(|[0-9]+)']);
Route::get('jadwalshift/{tanggal}/{jenis}/{id}/{more?}', 'BerandaController@jadwalShift')->where(['jenis' => '(sudahabsen|belumabsen)', 'id' => '[0-9]+', 'more' => '(|[0-9]+)']);
Route::get('detaillogabsen/{idlogabsen}', 'OthersController@detailLogAbsen')->where(['id' => '[0-9]+']);
Route::get('atributnilai/{idatribut}/{formid}', function($idatribut,$formid){
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,nilai as nama,IFNULL(kode,"") as kode FROM atributnilai WHERE idatribut = :idatribut ORDER BY urutan ASC, nilai ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idatribut', $idatribut);
        $stmt->execute();
        $data = '';
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        $atribut = Utils::getDataWhere($pdo, 'atribut', 'atribut', 'id', $idatribut);
        $atributkode = Utils::getDataWhere($pdo, 'atribut', 'kode', 'id', $idatribut);
        // return $atributkode;
        return view('include/atributnilai', ['data' => $data, 'formid' => $formid, 'atribut' => $atribut, 'atributkode' => $atributkode]);
    }else{
        abort(404);
    }
})->where(['idatribut' => '[0-9]+']);
Route::get('atributvariable/{formid}', function($formid){
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,atribut as nama,IFNULL(kode,"") as kode FROM atributvariable ORDER BY atribut ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = '';
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        // formid adalah id inputan yang akan di beri kode sesuai yg dipilih
        return view('include/modalatribut', ['data' => $data, 'formid' => $formid, 'jenis' => 'atributvariable']);
    }else{
        abort(404);
    }
})->where(['idatribut' => '[0-9]+']);

//check script di menu payrollkomponen master
Route::post('payrollkomponenmaster/checkscript', function(){
    if (Auth::check()) {
        $code = Request::input('script');
        return Utils::eval_not_evil($code);
    }else{
        abort(404);
    }
});
Route::post('payrollkomponenmaster/tesoutput', 'PayrollKomponenMasterController@tesOutput');
Route::post('laporankomponenmaster/tesoutput', 'LaporanKomponenMasterController@tesOutput');

//check data periode payroll posting data
// Route::post('payrollpostingdata/checkperiode', function(){
//     $periode = Request::input('periode');
//     $pdo = DB::connection('perusahaan_db')->getPdo();
//     return Utils::getDataWhere($pdo,'payroll_posting','id','periode',$periode);
// });

// load more popup
Route::get('logabsen/moredetail/{idpegawai}/{startfrom}/{yymm}', 'BerandaController@logAbsenPegawai')->where(['idpegawai' => '[0-9]+']);
Route::get('rekapabsen/moredetail/{idpegawai}/{startfrom}/{yymm}', 'BerandaController@rekapAbsenPegawai')->where(['idpegawai' => '[0-9]+']);

// halaman keperluan user (daftar,login,profil,logout,lupakatasandi)
Route::get('daftar', function(){
    return view('daftar', ['origin' => '']);
});
Route::get('daftar-ads', function(){
    return view('daftar', ['origin' => 'ads']);
});
Route::get('daftar-ads/terimakasih', function(){
    return view('terimakasih');
});
Route::post('daftar', 'Auth\AuthController@daftar');
Route::get('daftar/konfirmasi/{iduserkonfrmasi}/{kode}', 'Auth\AuthController@konfirmasiDaftar')->where(['iduserkonfirmasi' => '[0-9]+']);
Route::get('daftar/kirimulang/{iduser}', 'Auth\AuthController@kirimUlangKonfirmasiDaftar')->where(['iduser' => '[0-9]+']);
Route::get('lupakatasandi', function(){
    return view('lupakatasandi');
});
Route::get('lupakatasandi_verifikasi', function(){
    return view('lupakatasandi_verifikasi', ['jenis' => 'lupakatasandi_verifikasi']);
});
Route::get('lupakatasandi_verifikasialternatif', function(){
    return view('lupakatasandi_verifikasi', ['jenis' => 'lupakatasandi_verifikasialternatif']);
});
Route::post('lupakatasandi', 'Auth\AuthController@lupaKataSandi');
Route::post('lupakatasandi_verifikasi', 'Auth\AuthController@lupaKataSandi_verifikasi');
Route::post('lupakatasandi_verifikasialternatif', 'Auth\AuthController@lupaKataSandi_verifikasiAlternatif');
Route::get('login', 'Auth\AuthController@loginpage');
Route::post('login', 'Auth\AuthController@loginpro');
Route::get('logout', 'Auth\AuthController@logout');
Route::get('profil', 'OthersController@profil');
Route::get('profil/ubah', 'OthersController@ubahProfil');
Route::post('profil', 'OthersController@submitUbahProfil');
Route::get('profil/gantikatasandi', function(){
    if (Auth::check()) {
        return view('gantikatasandi', ['menu' => 'profil']);
    }else{
        abort(404);
    }
});
Route::post('profil/gantikatasandi', 'OthersController@submitGantiKataSandi');
Route::post('profil/gantifoto', 'OthersController@submitGantiFotoProfil');
Route::post('profil/index-data', 'OthersController@profilData');
Route::get('/', 'Controller@index');
Route::get('disable_video_onboarding', 'Controller@disableVideoOnboarding');
Route::get('pembayaran', 'Controller@pembayaran');
Route::post('pembayaran', 'Controller@checkout');
Route::get('terimakasih', function(){
  return redirect('/')->with('message', "Terima kasih sudah melakukan proses pembayaran silahkan menunggu proses perpanjangan masa aktif jika sudah melakukan pembayaran");
});
Route::post('canopus/notifikasi', 'NotifikasiCanopusController@notificationHandler');

//cari id (untuk menu customdashboard)
Route::get('cariid/{jenis}', function($jenis){
    if (Auth::check()) {
        return view('include/cariid', ['jenis' => $jenis]);
    }else{
        abort(404);
    }
});

Route::get('cariid/data/{jenis}', function($jenis){
    if (Auth::check()) {
        $hasil = '';
        $pdo = DB::connection('perusahaan_db')->getPdo();
        if ($jenis == 'pegawai') {
            $hasil = Utils::getData($pdo, 'pegawai', 'id,nama,pin,nomorhp', '', 'nama');
        } elseif ($jenis == 'atributnilai') {
            $hasil = Utils::getData($pdo, 'atributnilai an,atribut a', 'an.id,a.atribut,an.nilai', 'an.idatribut=a.id', 'an.nilai');
        } elseif ($jenis == 'agama') {
            $hasil = Utils::getData($pdo, 'agama', 'id,agama,urutan', '', 'urutan');
        } elseif ($jenis == 'jamkerja') {
            $sql = 'SELECT jk.id,jk.nama,IFNULL(jkk.nama,"") as kategori,jk.jenis,IF(jk.digunakan = "y","' . trans('all.ya') . '","' . trans('all.tidak') . '") as digunakan FROM jamkerja jk LEFT JOIN jamkerjakategori jkk ON jk.idkategori=jkk.id ORDER BY jk.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $hasil = $stmt->fetchAll(PDO::FETCH_OBJ);
        } elseif ($jenis == 'lokasi') {
            $hasil = Utils::getData($pdo, 'lokasi', 'id,nama,lat,lon', '', 'nama');
        } elseif ($jenis == 'jamkerjashift') {
            $sql = 'SELECT jks.id,jk.nama as jamkerja,jks.namashift,jks.kode,IFNULL(jksj.nama,"") as jenis,jks.urutan,IF(jks.digunakan = "y","' . trans('all.ya') . '","' . trans('all.tidak') . '") as digunakan FROM jamkerjashift jks LEFT JOIN jamkerjashift_jenis jksj ON jks.idjenis=jksj.id,jamkerja jk WHERE jks.idjamkerja=jk.id ORDER BY jk.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $hasil = $stmt->fetchAll(PDO::FETCH_OBJ);
        } elseif ($jenis == 'jamkerjashift_jenis') {
            $sql = 'SELECT id,nama,IF(digunakan = "y","' . trans('all.ya') . '","' . trans('all.tidak') . '") as digunakan FROM jamkerjashift_jenis ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $hasil = $stmt->fetchAll(PDO::FETCH_OBJ);
        } elseif ($jenis == 'jamkerjakategori') {
            $sql = 'SELECT id,nama,IF(digunakan = "y","' . trans('all.ya') . '","' . trans('all.tidak') . '") as digunakan FROM jamkerjakategori ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $hasil = $stmt->fetchAll(PDO::FETCH_OBJ);
        } elseif ($jenis == 'alasantidakmasuk') {
            $sql = 'SELECT id,alasan,IF(kategori="s","' . trans("all.sakit") . '",IF(kategori="i","' . trans("all.ijin") . '",IF(kategori="d","' . trans("all.dispensasi") . '","' . trans("all.tidakmasuk") . '"))) as kategori,urutan,IF(digunakan = "y","' . trans('all.ya') . '","' . trans('all.tidak') . '") as digunakan FROM alasantidakmasuk ORDER BY alasan';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $hasil = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        return $hasil;
    }else{
        abort(404);
    }
});

//modal laporan pekerjaan user ketka di klik detail(i)
Route::get('detailpekerjaan/{idpegawai}', function($idpegawai){
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT
                pi.id as idpekerjaaninput,
                pi.tanggal,
                pi.iduser,
                pk.nama as pekerjaan,
                pi.keterangan
            FROM
                pekerjaaninput pi,
                pekerjaankategori pk
            WHERE
                pi.idpekerjaankategori=pk.id AND
                pi.idpegawai = :idpegawai
            ORDER BY
                pi.tanggal DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        $data = '';
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        return view('include/detailpekerjaanpengguna', ['data' => $data, 'idpegawai' => $idpegawai]);
    }else{
        abort(404);
    }
});

// ajax dari payroll komponen input manual
Route::get('payrollgetinput/{yymm}', function($yymm){
    if (Auth::check()) {
        if (Session::has('payrollkomponeninputmanual_payrollkomponenmaster')) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT idpegawai,FORMAT(nominal, 0,"de_DE") as nominal,keterangan FROM payroll_komponen_inputmanual WHERE periode = :periode AND idpayroll_komponen_master = :idpayrollkomponenmaster';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':periode', $yymm);
            $stmt->bindValue(':idpayrollkomponenmaster', Session::get('payrollkomponeninputmanual_payrollkomponenmaster'));
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        }
    }else{
        abort(404);
    }
});

//ajax pekerjaan item di crud pekerjaan input
Route::get('getpekerjaanitem/{idpekerjaankategori}', function($idpekerjaankategori){
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,item,satuan FROM pekerjaanitem WHERE idpekerjaankategori = :idpekerjaankategori AND digunakan = "y" ORDER BY urutan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpekerjaankategori', $idpekerjaankategori);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }else{
        abort(404);
    }
})->where(['idpekerjaankategori' => '[0-9]+']);

// token input facebook perusahaan
Route::get('tokenperusahaan', function(){
    if (Auth::check()) {
        $letters = Request::input('q');
        $pdo = DB::getPdo();
        $sql = 'SELECT pr.id,CONCAT(pr.nama,"<span style=\'margin-left:5px;float:right;font-weight:bold\'>",pr.kode,"</span>") as nama FROM perusahaan pr, pengelola p WHERE p.idperusahaan=pr.id AND p.iduser = :iduser AND pr.nama LIKE :letters GROUP BY pr.id ORDER BY nama LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':letters', '%' . $letters . '%');
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }else{
        abort(404);
    }
});

// token input facebook payroll_komponen_master
Route::get('tokenpayrollkomponenmaster', function(){
    if (Auth::check()) {
        $letters = Request::input('q');
        $pdo = DB::connection('perusahaan_db')->getPdo();
        // $sql = 'SELECT id,CONCAT(nama,"<span style=\'margin-left:5px;float:right;font-weight:bold\'>",kode,"</span>") as nama FROM payroll_komponen_master WHERE nama LIKE "%'.$letters.'%" GROUP BY id ORDER BY urutan LIMIT 10';
        $sql = 'SELECT id,nama FROM payroll_komponen_master WHERE tipedata <> "teks" AND digunakan = "y" AND tampilkan = "y" AND nama LIKE :letters GROUP BY id ORDER BY urutan LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':letters', '%' . $letters . '%');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }else{
        abort(404);
    }
});

// select2
Route::get('select2pegawai', function(){
    if (Auth::check()) {
        $letters = Request::input('search');
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        $where = '';
        if ($batasan != '') {
            $where = ' AND id IN ' . $batasan;
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,CONCAT(nama," ",IFNULL(pin,"")) as text FROM pegawai WHERE status = "a" AND del = "t" AND nama LIKE :letters1 OR pin LIKE :letters2 ' . $where . ' ORDER BY nama';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':letters1', '%' . $letters . '%');
        $stmt->bindValue(':letters2', '%' . $letters . '%');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }else{
        abort(404);
    }
});

// token input facebook pegawai
Route::get('tokenpegawai', function(){
    if (Auth::check()) {
        $letters = Request::input('q');

//    $pegawais = Pegawai::select('id','nama')->where('del', 't')
//    ->where('nama', 'LIKE', '%' . $letters . '%')
//    ->limit('10')
//    ->get();
//
//    return Response::json($pegawais->toArray());
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan != '') {
            $batasan = ' AND id IN ' . $batasan;
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,CONCAT(nama,"<span style=\'margin-left:5px;float:right;font-weight:bold\'>",IFNULL(pin,""),"</span>") as nama FROM pegawai WHERE status = "a" AND del = "t" AND nama LIKE :letters ' . $batasan . ' ORDER BY nama LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':letters', '%' . $letters . '%');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }else{
        abort(404);
    }
});

// token input facebook atribut
Route::get('tokenatribut', function(){
    if (Auth::check()) {
        $letters = Request::input('q');
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,atribut as nama FROM atribut WHERE atribut LIKE :letters ORDER BY atribut LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':letters', '%' . $letters . '%');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
//    return Utils::getData($pdo,'atribut','id,atribut as nama','atribut LIKE "%'.$letters.'%"','atribut LIMIT 10');
    }else{
        abort(404);
    }
});

// token input facebook atributnilai
Route::get('tokenatributnilai', function(){
    if (Auth::check()) {
        $letters = Request::input('q');
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,nilai as nama FROM atributnilai WHERE nilai LIKE :letters ORDER BY nilai LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':letters', '%' . $letters . '%');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
//    return Utils::getData($pdo,'atributnilai','id,nilai as nama','nilai LIKE "%'.$letters.'%"','nilai LIMIT 10');
    }else{
        abort(404);
    }
});

// typeaheade user email
Route::get('typeaheaduseremail/{query}', function($query){
    if (Auth::check()) {
        //$letters = Request::input('query');
        $letters = $query;

        $pdo = DB::getPdo();
        $sql = 'SELECT email as nama FROM `user` WHERE id IN(SELECT iduser FROM pengelola WHERE idperusahaan = :idperusahaan) AND email LIKE :letters ORDER BY email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
        $stmt->bindValue(':letters', '%' . $letters . '%');
        $stmt->execute();
        $array = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $array[] = $row['nama'];
        }
        return $array;
    }else{
        abort(404);
    }
});

// token input facebook atribut nilai
Route::get('atributnilai', function(){
    if (Auth::check()) {
        $letters = Request::input('q');

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT an.id,CONCAT(an.nilai," (",a.atribut,")") as nama FROM atributnilai an LEFT JOIN atribut a ON an.idatribut=a.id WHERE an.nilai LIKE :letters1 or a.atribut LIKE :letters2 ORDER BY an.nilai';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':letters1', '%' . $letters . '%');
        $stmt->bindValue(':letters2', '%' . $letters . '%');
        $stmt->execute();
        $atributnilai = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $atributnilai;
    }else{
        abort(404);
    }
});

// get data payroll posting dari id kelompok
Route::get('getpayrollposting/{idkelompok}/{postingdatadefault?}', function($idkelompok, $postingdatadefault = '') {
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT
                pp.id,
                pp.periode,
                pp.tanggalawal,
                pp.tanggalakhir
            FROM
                payroll_posting pp,
                payroll_posting_komponen ppk,
                payroll_komponen_master pkm
            WHERE
                ppk.idpayroll_posting = pp.id AND
                ppk.komponenmaster_id = pkm.id AND
                pkm.idpayroll_kelompok = :idkelompok
            GROUP BY
                pp.id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idkelompok', $idkelompok);
        $stmt->execute();
        $data = [];
        if ($stmt->rowCount() > 0) {
            $i = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[$i]['id'] = $row['id'];
                $data[$i]['periode'] = Utils::periodeCantik($row['periode']) . ' (' . Utils::tanggalCantikDariSampai($row['tanggalawal'], $row['tanggalakhir']) . ')';
                $data[$i]['selected'] = $postingdatadefault != '' ? ($row['id'] == $postingdatadefault ? 'selected' : '') : '';
                $i++;
            }
        }
        return $data;
    }else{
        abort(404);
    }
})->where(['idperusahaankonfirmasi' => '[0-9]+']);

// get data payroll posting dari id kelompok
Route::get('getpostingdata/{idslipgaji}', function($idslipgaji) {
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idkelompok = Utils::getDataWhere($pdo, 'slipgaji', 'idpayrollkelompok', 'id', $idslipgaji);
        $sql = 'SELECT
                pp.id,
                pp.periode,
                pp.tanggalawal,
                pp.tanggalakhir
            FROM
                payroll_posting pp,
                payroll_posting_komponen ppk,
                payroll_komponen_master pkm
            WHERE
                ppk.idpayroll_posting = pp.id AND
                ppk.komponenmaster_id = pkm.id AND
                pkm.idpayroll_kelompok = :idkelompok
            GROUP BY
                pp.id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idkelompok', $idkelompok);
        $stmt->execute();
        $data = [];
        if ($stmt->rowCount() > 0) {
            $i = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[$i]['id'] = $row['id'];
                $data[$i]['periode'] = Utils::periodeCantik($row['periode']) . ' (' . Utils::tanggalCantikDariSampai($row['tanggalawal'], $row['tanggalakhir']) . ')';
                $i++;
            }
        }
        return $data;
    }else{
        abort(404);
    }
})->where(['idslipgaji' => '[0-9]+']);

Route::get('generatecsrftoken', function(){
    if (Auth::check()) {
        Session::regenerateToken();
        return csrf_token();
    }else{
        abort(404);
    }
});

// user baru tambah perusahaan
Route::get('tambahperusahaanbaru', function(){
    if (Auth::check()) {
        return view('perusahaan/create', ['dari' => 'index', 'menu' => 'perusahaan']);
    }else{
        abort(404);
    }
});

//konfirmasi email buat perusahaan baru
Route::get('perusahaan/konfirmasi/{idperusahaankonfirmasi}/{kode}', function($idperusahaankonfirmasi, $kode){
    if (Auth::check()) {
        //return $kode;
        $pdo = DB::getPdo();
        $sql = 'SELECT pk.idperusahaan,pk.status,p.ispremium FROM perusahaan_konfirmasi pk, perusahaan p WHERE pk.idperusahaan = p.id AND pk.id=:id AND pk.kode = :kode';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $idperusahaankonfirmasi);
        $stmt->bindValue(':kode', $kode);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $idperusahaan = $row['idperusahaan'];
            if ($row['status'] == 't') {
                //$sukses = false;
                $status = 'w';
                if ($row['ispremium'] == 'y') {
                    $status = 'tp';
                }
                try {
                    $pdo->beginTransaction();
                    $sql2 = 'UPDATE perusahaan_konfirmasi set status = "v" WHERE id = :idperusahaankonfirmasi AND kode = :kode';
                    $stmt2 = $pdo->prepare($sql2);
                    $stmt2->bindValue(':idperusahaankonfirmasi', $idperusahaankonfirmasi);
                    $stmt2->bindValue(':kode', $kode);
                    $stmt2->execute();

                    $sql3 = 'UPDATE `perusahaan` SET status = :status WHERE id = :idperusahaan';
                    $stmt3 = $pdo->prepare($sql3);
                    $stmt3->bindValue(':status', $status);
                    $stmt3->bindValue(':idperusahaan', $idperusahaan);
                    $stmt3->execute();

                    $pdo->commit();
                    $msg = "ok";
                    //$sukses = true;
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    $msg = trans('all.terjadigangguan');
                }
            } else if ($row['status'] == "v") {
                $msg = trans('all.andasudahmelakukankonfirmasi');
            } else {
                $msg = trans('all.terjadigangguan');
            }
        } else {
            $msg = trans('all.datatidakditemukan');
        }
        return view('perusahaan/sukses', ['message' => $msg]);
    }else{
        abort(404);
    }
})->where(['idperusahaankonfirmasi' => '[0-9]+']);

//kirim ulangkonfirmasi email buat perusahaan baru
Route::get('perusahaan/kirimulangkonfirmasi/{idperusahaan}', function($idperusahaan){
    if (Auth::check()) {
        //return $kode;
        $pdo = DB::getPdo();
        $sql = 'SELECT nama, pic_email, pic_notelp, status FROM perusahaan WHERE id = :idperusahaan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idperusahaan', $idperusahaan);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $nama = $row['nama'];
            $pic_email = $row['pic_email'];
            $pic_notelp = $row['pic_notelp'];
            $status = $row['status'];

            if ($status == 'c') {
                $sql = 'SELECT id as idperusahaankonfirmasi, kode, status FROM perusahaan_konfirmasi WHERE idperusahaan=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $idperusahaan);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row['status'] == 't') {
                        $sql3 = 'UPDATE perusahaan_konfirmasi SET jumlahpercobaan = jumlahpercobaan+1 WHERE id = :id';
                        $stmt3 = $pdo->prepare($sql3);
                        $stmt3->bindValue(':id', $row['idperusahaankonfirmasi']);
                        $stmt3->execute();

                        $data = array('nama' => $nama, 'nomorhp' => $pic_notelp, 'email' => $pic_email, 'idperusahaankonfirmasi' => $row['idperusahaankonfirmasi'], 'kode' => $row['kode']);
                        Mail::send('templateemail.buatperusahaan', $data, function ($message) use ($data) {
                            $message->to($data['email'])->subject('Konfirmasi Perusahaan Baru');
                            $message->from('no-reply@smartpresence.id', 'Smart Presence');
                        });

                        return redirect('/')->with('message', trans('all.kirimulangberhasil'));
                    } else {
                        return redirect('/')->with('message', trans('all.andasudahmelakukankonfirmasi'));
                    }
                } else {
                    $kode = md5($idperusahaan . '_create_perusahaan_smartpresence!');
                    $sql1 = 'INSERT INTO perusahaan_konfirmasi VALUES(NULL,:idperusahaan,:kode,1,"t",NOW())';
                    $stmt1 = $pdo->prepare($sql1);
                    $stmt1->bindValue(':idperusahaan', $idperusahaan);
                    $stmt1->bindValue(':kode', $kode);
                    $stmt1->execute();

                    $idperusahaankonfirmasi = $pdo->lastInsertId();

                    $data = array('nama' => $nama, 'nomorhp' => $pic_notelp, 'email' => $pic_email, 'idperusahaankonfirmasi' => $idperusahaankonfirmasi, 'kode' => $kode);
                    Mail::send('templateemail.buatperusahaan', $data, function ($message) use ($data) {
                        $message->to($data['email'])->subject('Konfirmasi Perusahaan Baru');
                        $message->from('no-reply@smartpresence.id', 'Smart Presence');
                    });

                    return redirect('/')->with('message', trans('all.kirimulangberhasil'));
                }
            } else if ($status == 'a') {
                return redirect('/')->with('message', trans('all.andasudahmelakukankonfirmasi'));
            } else {
                return redirect('/')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('/')->with('message', trans('all.datatidakditemukan'));
        }
    }else{
        abort(404);
    }
})->where(['idperusahaan' => '[0-9]+']);

// dapatkan foto logabsen
Route::get('fotologabsen/{idlogabsen}/{jenis}', function($idlogabsen,$jenis){
    if (Auth::check()) {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/pegawai_nopic.png';

        $sql = 'SELECT DATE_FORMAT(DATE(la.waktu),"%Y%m%d") as tanggal, la.idpegawai, IFNULL(la.filename, "") as filename FROM logabsen la WHERE la.id=:idlogabsen LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idlogabsen', $idlogabsen);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tanggal = $row['tanggal'];
            $idpegawai = $row['idpegawai'];
            $filename = $row['filename'];

            if ($filename == '') {
                $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
            } else {
                if ($jenis == 'normal') {
                    $path = Session::get('folderroot_perusahaan') . '/logabsen/' . Utils::id2Folder($idpegawai) . '/' . $idpegawai . '/' . substr($tanggal, 0, 4) . '/' . substr($tanggal, 4, 2) . '/' . substr($tanggal, 6, 2) . '/' . $filename;
                } else {
                    $path = Session::get('folderroot_perusahaan') . '/logabsen/' . Utils::id2Folder($idpegawai) . '/' . $idpegawai . '/' . substr($tanggal, 0, 4) . '/' . substr($tanggal, 4, 2) . '/' . substr($tanggal, 6, 2) . '/' . $filename . '_thumb';
                }
                if (file_exists($path)) {
                    $raw = Utils::decrypt($path);
                    $result = response($raw)->header('Content-Type', 'image/jpeg');
                } else {
                    $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
                }
            }
        } else {
            $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
        }

        return $result;
    }else{
        abort(404);
    }
})->where(['idlogabsen' => '[0-9]+', 'jenis' => '(normal|thumb)']);

// dapatkan fotonormal
Route::get('fotonormal/{siapa}/{id}', function($siapa, $id){
    if (Auth::check()) {
        $path = '';
        $path_nopic = '';

        if ($siapa == 'user') {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/user/' . Utils::id2Folder($id) . '/' . $id;
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/user_nopic.png';
        } else if ($siapa == 'pegawai') {
            $path = Session::get('folderroot_perusahaan') . '/pegawai/' . Utils::id2Folder($id) . '/' . $id;
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/pegawai_nopic.png';
        } else if ($siapa == 'perusahaan') {
            $pdo = DB::getPdo();
            $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idperusahaan', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $path = $row['folderroot'] . '/logo_perusahaan';
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/perusahaan_nopic.png';
        } else if ($siapa == 'slideshow') {
            $s_id = explode('-', $id);
            $path = Session::get('folderroot_perusahaan') . '/slideshow/' . $s_id[1];
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/no-image.png';
        } else if ($siapa == 'logoparameterekspor') {
            // $id = nama logonya
            $path = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $id;
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/no-image.png';
        } else if ($siapa == 'ijintidakmasuk') {

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT filename FROM ijintidakmasuk WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $path = Session::get('folderroot_perusahaan') . '/ijintidakmasuk/' . $row['filename'];
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/no-image.png';
        }

        if (file_exists($path)) {
            $raw = Utils::decrypt($path);
            Session::set('foto' . $siapa . '_perusahaan', 'ada');
            $result = response($raw)->header('Content-Type', 'image/jpeg');
        } else {
            Session::set('foto' . $siapa . '_perusahaan', 'tidakada');
            $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
        }

        return $result;
    }else{
        abort(404);
    }
})->where(['siapa' => '(user|pegawai|perusahaan|slideshow|logoparameterekspor|ijintidakmasuk)', 'id' => '[0-9]+']);

// dapatkan foto thumbnail
Route::get('foto/{siapa}/{id}', function($siapa, $id){
    if (Auth::check()) {
        $path = '';
        $path_nopic = '';

        if ($siapa == 'user') {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/user/' . $id . '_thumb';
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/user_nopic.png';
        } else if ($siapa == 'pegawai') {
            $path = Session::get('folderroot_perusahaan') . '/pegawai/' . Utils::id2Folder($id) . '/' . $id . '_thumb';
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/pegawai_nopic.png';
        } else if ($siapa == 'perusahaan') {
            $pdo = DB::getPdo();
            $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idperusahaan', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $path = $row['folderroot'] . '/logo_perusahaan_thumb';
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/perusahaan_nopic.png';
        } else if ($siapa == 'slideshow') {
            $s_id = explode('-', $id);
            $path = Session::get('folderroot_perusahaan') . '/slideshow/' . $s_id[1];
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/no-image.png';
        } else if ($siapa == 'logoparameterekspor') {
            // $id = nama logonya
            $path = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $id;
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/no-image.png';
        } else if ($siapa == 'ijintidakmasuk') {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT filename FROM ijintidakmasuk WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $path = Session::get('folderroot_perusahaan') . '/ijintidakmasuk/' . $row['filename'] . '_thumb';
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/no-image.png';
        }

        if (file_exists($path)) {
            $raw = Utils::decrypt($path);
            Session::set('foto' . $siapa . '_perusahaan', 'ada');
            $result = response($raw)->header('Content-Type', 'image/jpeg');
        } else {
            Session::set('foto' . $siapa . '_perusahaan', 'tidakada');
            $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
        }

        return $result;
    }else{
        abort(404);
    }
})->where(['jenis' => '(user|pegawai|perusahaan|slideshow|logoparameterekspor|ijintidakmasuk)']);

//dapatkan logo perusahaan
Route::get('logoperusahaan/{id}/{jenis}/{thumb?}', function($id, $jenis, $thumb = ''){
    if (Auth::check()) {
        $pdo = DB::getPdo();
        $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idperusahaan', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $path = $row['folderroot'] . '/logo_' . $jenis . '_app' . $thumb;
        $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/perusahaan_nopic.png';
        if (file_exists($path)) {
            $raw = Utils::decrypt($path);
            $result = response($raw)->header('Content-Type', 'image/png');
        } else {
            $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
        }
        return $result;
    }else{
        abort(404);
    }
})->where(['jenis' => '(employee|datacapture)', 'thumb' => '(|_thumb)']);

//hapus logo perusahaan
Route::get('hapuslogoperusahaan/{id}/{jenis}', function($id, $jenis){
    if (Auth::check()) {
        $pdo = DB::getPdo();
        $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idperusahaan', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $path = $row['folderroot'] . '/logo_' . $jenis . '_app';
        if (file_exists($path)) {
            unlink($path);
            if (file_exists($path . '_thumb')) {
                unlink($path . '_thumb');
            }
            return redirect('perusahaan/' . $id . '/edit')->with('message', trans('all.fotoberhasildihapus'));
        } else {
            return redirect('perusahaan/' . $id . '/edit')->with('message', trans('all.fototidakditemukan'));
        }
    }else{
        abort(404);
    }
})->where(['jenis' => '(employee|datacapture)', 'thumb' => '(|_thumb)']);

Route::get('mapmarker/{jenis}', function($jenis){
    if (Auth::check()) {
        $result = '';
        if ($jenis == 'absenawal') {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/tracker/map.png';
            $result = Response::make(File::get($path))->header('Content-Type', 'image/png');
        } else if ($jenis == 'tracker') {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/tracker/marker.png';
            $result = Response::make(File::get($path))->header('Content-Type', 'image/png');
        } else if ($jenis == 'absenakhir') {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/tracker/user.png';
            $result = Response::make(File::get($path))->header('Content-Type', 'image/png');
        }
        return $result;
    }else{
        abort(404);
    }
});

// bugs hapus foto user outout foto tidak ditemukan

// hapus foto
Route::get('hapusfoto/{siapa}/{id}', function($siapa, $id){
    if (Auth::check()) {
        if ($siapa == 'user') {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/user/' . Utils::id2Folder($id) . '/' . $id;
            if (file_exists($path)) {
                unlink($path);
                if (file_exists($path . '_thumb')) {
                    unlink($path . '_thumb');
                }
                Session::set('fotouser_perusahaan', 'tidakada');
                return redirect('profil')->with('message', trans('all.fotoberhasildihapus'));
            } else {
                return redirect('profil')->with('message', trans('all.fototidakditemukan'));
            }
        } else if ($siapa == 'pegawai') {
            $path = Session::get('folderroot_perusahaan') . '/pegawai/' . Utils::id2Folder($id) . '/' . $id;
            if (file_exists($path)) {
                unlink($path);
                if (file_exists($path . '_thumb')) {
                    unlink($path . '_thumb');
                }
                Session::set('fotopegawai_perusahaan', 'tidakada');
                $pdo = DB::connection('perusahaan_db')->getPdo();
                $sql = 'UPDATE pegawai SET checksum_img = "" WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                return redirect('datainduk/pegawai/pegawai/' . $id . '/edit')->with('message', trans('all.fotoberhasildihapus'));
            } else {
                return redirect('datainduk/pegawai/pegawai/' . $id . '/edit')->with('message', trans('all.fototidakditemukan'));
            }
        } else if ($siapa == 'perusahaan') {
            $pdo = DB::getPdo();
            $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idperusahaan', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $path = $row['folderroot'] . '/logo_perusahaan';
            if (file_exists($path)) {
                unlink($path);
                if (file_exists($path . '_thumb')) {
                    unlink($path . '_thumb');
                }
                Session::set('fotoperusahaan_perusahaan', 'tidakada');
                return redirect('perusahaan/' . $id . '/edit')->with('message', trans('all.fotoberhasildihapus'));
            } else {
                return redirect('perusahaan/' . $id . '/edit')->with('message', trans('all.fototidakditemukan'));
            }
        } else if ($siapa == 'slideshow') {
            $s_id = explode('-', $id);
            $path = Session::get('folderroot_perusahaan') . '/slideshow/' . $s_id[2];

            $pdo = DB::connection('perusahaan_db')->getPdo();
            Utils::deleteData($pdo, 'slideshowimage', $s_id[0]);

            if (file_exists($path)) {
                unlink($path);
                if (file_exists($path . '_thumb')) {
                    unlink($path . '_thumb');
                }
                return redirect('pengaturan/slideshow/' . $s_id[1] . '/detail')->with('message', trans('all.fotoberhasildihapus'));
            } else {
                return redirect('pengaturan/slideshow/' . $s_id[1] . '/detail')->with('message', trans('all.fototidakditemukan'));
            }
        } else if ($siapa == 'logoparameterekspor') {
            $s_id = explode('-', $id);
            $path = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $s_id[1];

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'UPDATE parameterekspor SET logo' . $s_id[0] . ' = ""';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            if (file_exists($path)) {
                unlink($path);
                return redirect('pengaturan/parameterekspor')->with('message', trans('all.fotoberhasildihapus'));
            } else {
                return redirect('pengaturan/parameterekspor')->with('message', trans('all.fototidakditemukan'));
            }
        } else if ($siapa == 'ijintidakmasuk') {

            $pdo = DB::connection('perusahaan_db')->getPdo();
            //select filename dari tabel ijintidakmasuk
            $sql = 'SELECT filename FROM ijintidakmasuk WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row['filename'] != '') {
                $path = Session::get('folderroot_perusahaan') . '/ijintidakmasuk/' . $row['filename'];

                //ubah filename jadi ''
                $sql = 'UPDATE ijintidakmasuk SET filename = ""';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                //hapus file
                if (file_exists($path)) {
                    unlink($path);
                    if (file_exists($path . '_thumb')) {
                        unlink($path . '_thumb');
                    }
                    return redirect('datainduk/absensi/ijintidakmasuk/' . $id . '/edit')->with('message', trans('all.lampiranberhasildihapus'));
                } else {
                    return redirect('datainduk/absensi/ijintidakmasuk/' . $id . '/edit')->with('message', trans('all.lampirantidakditemukan'));
                }
            } else {
                return redirect('datainduk/absensi/ijintidakmasuk/' . $id . '/edit')->with('message', trans('all.lampirantidakditemukan'));
            }
        }
    }else{
        abort(404);
    }
})->where(['jenis' => '(user|pegawai|perusahaan|slideshow|logoparameterekspor|ijintidakmasuk)']);

//dapatkan 1 facesample
Route::get('getfacesample/{idfacesample?}/{jenis?}', function($idfacesample='', $jenis='') {
    if (Auth::check()) {
        if ($idfacesample == '') {
            $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/pegawai_nopic.png';
            $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
        } else {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $result = '';
            $sql = 'SELECT idpegawai, filename FROM facesample WHERE id=:idfacesample LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idfacesample', $idfacesample);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $idpegawai = $row['idpegawai'];
                $filename = $row['filename'];

                $path = Session::get('folderroot_perusahaan') . '/facesample/' . Utils::id2Folder($idpegawai) . '/' . $idpegawai . '/' . $filename . $jenis;
                if (file_exists($path)) {
                    $raw = Utils::decrypt($path);
                    $result = response($raw)->header('Content-Type', 'image/jpeg');
                } else {
                    $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/pegawai_nopic.png';
                    $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
                }
            }
        }
        return $result;
    }else{
        abort(404);
    }
})->where(['idfacesample' => '(|[0-9]+)', 'jenis' => '(|_thumb)']);

// dapatkan facesample berdasarkan jumlah($i)
Route::get('facesample/{id}/{i}', function($id, $i) {
    if (Auth::check()) {
        if (is_nan($i) == false) {
            if ($i > 0) {
                $pdo = DB::connection('perusahaan_db')->getPdo();
                $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/pegawai_nopic.png';
                $sql = 'SELECT idpegawai, filename FROM facesample WHERE idpegawai=:idpegawai LIMIT ' . $i;
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $id);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $idpegawai = $row['idpegawai'];
                    $filename = $row['filename'];

                    $path = Session::get('folderroot_perusahaan') . '/facesample/' . Utils::id2Folder($idpegawai) . '/' . $idpegawai . '/' . $filename;
                    if (file_exists($path)) {
                        $raw = Utils::decrypt($path);
                        $result = response($raw)->header('Content-Type', 'image/jpeg');
                    } else {
                        $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
                    }
                } else {
                    $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
                }

                return $result;
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }
    }else{
        abort(404);
    }
})->where(['id' => '[0-9]+', 'i' => '[0-9]+']);

// set bahasa
Route::get('bahasa/{bahasa}', function($bahasa){
	Session::set('conf_bahasaperusahaan', $bahasa);
	return Redirect::back();
})->where(['bahasa' => '(id|en|cn)']);

// set perusahaan
Route::get('setperusahaan/{perusahaan}', function($perusahaan){
    if (Auth::check()) {
        $bahasa = Session::get('conf_bahasaperusahaan');
        $iduser = Session::get('iduser_perusahaan');
        $namauser = Session::get('namauser_perusahaan');
        $emailuser = Session::get('emailuser_perusahaan');
        $superuser = Session::get('superuser_perusahaan');
        $user = Auth::user(); ///data login
        Session::flush(); //menghapus semua session
        Auth::login($user);
        Session::set('conf_bahasaperusahaan', $bahasa);
        Session::set('iduser_perusahaan', $iduser);
        Session::set('namauser_perusahaan', $namauser);
        Session::set('emailuser_perusahaan', $emailuser);
        Session::set('superuser_perusahaan', $superuser);

        if ($perusahaan == 'null') {
            Session::forget('conf_webperusahaan');
            Session::forget('hakakses_perusahaan');
            Session::forget('pencarian_perusahaan');
            Session::forget('lappertanggal_atribut');
            return redirect('/');
        } else {
            if (Session::get('superuser_perusahaan') != '') {
                Session::set('conf_webperusahaan', $perusahaan);
                Session::set('perusahaan_perusahaan', 'Smart Presence');

                $pdo = DB::getPdo();
                $sql = 'SELECT nama,ispremium, kode FROM perusahaan WHERE id = :idperusahaan';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idperusahaan', $perusahaan);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                Session::set('perusahaan_perusahaan', $row['nama']);
                Session::set('perusahaan_ispremium', $row['ispremium']);
                Session::set('perusahaan_kode', $row['kode']);

                // buat session folderroot perusahaan untuk semua foto
                $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan AND status IN("a","c")';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idperusahaan', $perusahaan);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $rowPR = $stmt->fetch(PDO::FETCH_ASSOC);
                    Session::set('folderroot_perusahaan', $rowPR['folderroot']);
                    return redirect('/');
                } else {
                    return redirect('/')->with('error', trans('all.terjadigangguansistem'));
                }
            } else {
                $pdo = DB::getPdo();
                $sql = 'SELECT id FROM pengelola WHERE idperusahaan = :idperusahaan AND iduser = :iduser';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idperusahaan', $perusahaan);
                $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    Session::set('conf_webperusahaan', $perusahaan);
                    Session::set('perusahaan_perusahaan', 'Smart Presence');

                    $pdo = DB::getPdo();
                    $sql = 'SELECT nama,ispremium, kode FROM perusahaan WHERE id = :idperusahaan';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idperusahaan', $perusahaan);
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    Session::set('perusahaan_perusahaan', $row['nama']);
                    Session::set('perusahaan_ispremium', $row['ispremium']);
                    Session::set('perusahaan_kode', $row['kode']);

                    // buat session folderroot perusahaan untuk semua foto
                    $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan AND status IN("a","c")';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idperusahaan', $perusahaan);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $rowPR = $stmt->fetch(PDO::FETCH_ASSOC);
                        Session::set('folderroot_perusahaan', $rowPR['folderroot']);
                        return redirect('/');
                    } else {
                        return redirect('/')->with('error', trans('all.terjadigangguansistem'));
                    }
                } else {
                    abort(404);
                }
            }
        }
    }else{
        abort(404);
    }
})->where(['perusahaan' => '(null|[0-9]+)']);

Route::get('datainduk/pegawai/sinkronisasi', 'PegawaiController@sinkronisasi_data');
