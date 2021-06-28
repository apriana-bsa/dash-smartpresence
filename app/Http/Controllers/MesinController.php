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

class MesinController extends Controller
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

    public function showIndex(Request $request)
    {
        $isOnboarding = $request->query('onboarding');
        if(Utils::cekHakakses('mesin','l')){
            Utils::insertLogUser('akses menu mesin');
            return view('datainduk/absensi/mesin/index', ['menu' => 'mesin', 'onboarding' => $isOnboarding]);
        } else {
            return redirect('/');
        }
    }

    public function show(Request $request)
    {
        $isOnboarding = $request->query('onboarding');
        if(Utils::cekHakakses('mesin','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('mesin','uhm')){
                $columns = array('','nama','atribut','jenis','deviceid','cekjamserver','utcbaru','lastsync','status','id');
            }else{
                $columns = array('nama','atribut','jenis','deviceid','cekjamserver','utcbaru','lastsync','status','id');
            }
            $table = '(
                        SELECT
                            m.id,
                            m.nama,
                            GROUP_CONCAT(a.nilai ORDER BY a.nilai SEPARATOR ", ") as atribut,
                            m.jenis,
                            m.deviceid,
                            m.deviceidreset,
                            m.cekjamserver,
                            m.utcdefault,
                            if(m.utcdefault = "y", "' . trans("all.default") . '",m.utc) as utcbaru,
                            m.utc,
                            m.gcmid,
                            m.status,
                            IFNULL(m.lastsync,"") as lastsync,
                            m.kamera_opsi
                        FROM
                            mesin m
                            LEFT JOIN mesinatribut ma ON ma.idmesin=m.id
                            LEFT JOIN atributnilai a ON ma.idatributnilai=a.id
                        WHERE
                            del = "t"
                        GROUP BY
                            m.id
                    ) x';
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE 1=1 '.$where;
            $stmt = $pdo->prepare($sql);
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

            $sql = 'SELECT * FROM '.$table.' WHERE 1=1 ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    if(Utils::cekHakakses('mesin','um')){
                        if($key['jenis'] == 'smartphone') {
                            if ($key['status'] == 'bs') {
                                if ($isOnboarding){
                                    $action .= '<a title="' . trans('all.mintakodeverifikasi') . '" href="mesin/' . $key['id'] . '/verifikasi?onboarding=true"><i class="fa fa-link" style="color:#1ab394"></i></a>&nbsp;&nbsp;';
                                } else {
                                    $action .= '<a title="' . trans('all.mintakodeverifikasi') . '" href="mesin/' . $key['id'] . '/verifikasi"><i class="fa fa-link" style="color:#1ab394"></i></a>&nbsp;&nbsp;';
                                }
                            } else if ($key['status'] == 'th') {
                                $action .= '<a title="' . trans('all.putussambungan') . '" href="#" onclick="return putusSambungan(' . $key['id'] . ')"><i class="fa fa-unlink" style="color:#1ab394"></i></a>&nbsp;&nbsp;';
                            }
                        }
                        $action .= Utils::tombolManipulasi('ubah','mesin',$key['id']);
                    }
                    if(Utils::cekHakakses('mesin','hm')){
                        $action .= Utils::tombolManipulasi('hapus','mesin',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'deviceid') {
                            $tempdata[$columns[$i]] = $key[$columns[$i]] != '' ? substr($key[$columns[$i]], 5) : '';
                        }elseif($columns[$i] == 'kamera_opsi') {
                            $tempdata[$columns[$i]] = trans('all.'.$key[$columns[$i]]);
                        }elseif($columns[$i] == 'jenis') {
                            $pengaturanfingerprint = '';
                            if($key[$columns[$i]] == 'fingerprint'){
                                $pengaturanfingerprint = '<i class="fa fa-gear" style="cursor:pointer" onclick="return pengaturanFingerPrint('.$key['id'].')"></i>';
                            }
                            $tempdata[$columns[$i]] = trans('all.'.$key[$columns[$i]]).' '.$pengaturanfingerprint;
                        }elseif($columns[$i] == 'status' || $columns[$i] == 'cekjamserver' || $columns[$i] == 'utcdefault') {
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]]);
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

    public function create(Request $request)
    {
        if(Utils::cekHakakses('mesin','tm')){
            $atributnilais = AtributNilai::select('atributnilai.id', DB::raw('atribut.id as idatribut'), 'atributnilai.nilai', 'atribut.atribut')
            ->leftjoin('atribut', 'atributnilai.idatribut', '=', 'atribut.id')
            ->get();

            $atributs = Atribut::select('id', 'atribut')->get();
            $slideshows = SlideShow::select('id', 'nama')->get();

            $atribut = Utils::getAtributdanAtributNilaiCrud(0, 'mesin');
            $pdo = DB::Connection('perusahaan_db')->getPdo();
            $fingerprintconnector = Utils::getData($pdo,'fingerprintconnector','id,nama','','nama');
            $datalokasi = Utils::getData($pdo,'lokasi','lat,lon,nama','','nama');
            Utils::insertLogUser('akses menu tambah mesin');
            $isOnboarding = $request->query('onboarding');
            return view('datainduk/absensi/mesin/create', ['fingerprintconnector' => $fingerprintconnector, 'datalokasi' => $datalokasi, 'atribut' => $atribut, 'atributnilais' => $atributnilais, 'atributs' => $atributs, 'slideshows' => $slideshows, 'menu' => 'mesin', 'onboarding' => $isOnboarding]);
        } else {
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $isOnboarding = $request->query('onboarding');
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM mesin WHERE nama=:nama LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nama', $request->nama);
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            $fp_komunikasi = $request->metode_utility_mesin.
                            $request->metode_pegawai_read.
                            $request->metode_pegawai_insert.
                            $request->metode_pegawai_delete.
                            $request->metode_fingersample_read.
                            $request->metode_fingersample_insert.
                            $request->metode_fingersample_delete.
                            $request->metode_logabsen_read.
                            $request->metode_logabsen_deleteall.
                            $request->kunci_saat_sinkron.
                            $request->kunci_setelah_deleteall.
                            $request->restart_setelah_delete_all;

            $mesin = new Mesin;
            $mesin->nama = $request->nama;
            $mesin->jenis = $request->jenis;
            $mesin->cekjamserver = $request->cekjamserver;
            $mesin->utcdefault = $request->utcdefault;
            $mesin->utc = $request->utc;
            $mesin->fixgps_gunakan = $request->fixgps_gunakan;
            $mesin->fixgps_latitude = $request->fixgps_latitude;
            $mesin->fixgps_longitude = $request->fixgps_longitude;
            $mesin->deteksiekspresi = $request->deteksiekspresi;
            $mesin->idslideshow = ($request->slideshow == '' ? NULL : $request->slideshow);
            $mesin->kamera_opsi = $request->kamera_opsi;
            $mesin->getid_opsi = $request->getid_opsi;
            $mesin->perangkat_bt_rfidnfc = $request->perangkat_bt_rfidnfc;
            $mesin->perangkat_bt_bukakunci = $request->perangkat_bt_bukakunci;
            $mesin->status = 'bs';
            $mesin->fp_comkey = $request->fp_comkey;
            $mesin->fp_ip = $request->fp_ip;
            $mesin->fp_soapport = $request->fp_soapport;
            $mesin->fp_udpport = $request->fp_udpport;
            $mesin->fp_idfingerprintconnector = ($request->fp_idfingerprintconnector == '' ? NULL : $request->fp_idfingerprintconnector);
            $mesin->fp_serialnumber = $request->fp_serialnumber;
            $mesin->fp_algoritma = $request->fp_algoritma;
            $mesin->fp_intervaltarik = $request->fp_intervaltarik;
            $mesin->fp_ijinkanadmin = $request->fp_ijinkanadmin;
            $mesin->fp_lat = $request->fp_lat;
            $mesin->fp_lon = $request->fp_lon;
            $mesin->fp_komunikasi = ($request->jenis == 'fingerprint' ? $fp_komunikasi : '');
            $mesin->fp_status = $request->fp_status;
            $mesin->inserted = date('Y-m-d H:i:s');
            $mesin->del = "t";
            $mesin->save();

            Utils::insertLogUser('Tambah mesin "' . $request->nama . '"');

            if ($request->atribut != '') {
                for ($i = 0; $i < count($request->atribut); $i++) {
                    $mesinatribut = new MesinAtribut;
                    $mesinatribut->idmesin = $mesin->id;
                    $mesinatribut->idatributnilai = $request->atribut[$i];
                    $mesinatribut->save();
                }
            }
            if ($isOnboarding){
                return redirect('datainduk/absensi/mesin?onboarding=true')->with('message', trans('all.databerhasildisimpan'));
            }
            return redirect('datainduk/absensi/mesin')->with('message', trans('all.databerhasildisimpan'));
        } else {
            if ($isOnboarding){
                return redirect('datainduk/absensi/mesin/create?onboarding=true')->with('message', trans('all.datasudahada'));
            }
            return redirect('datainduk/absensi/mesin/create')->with('message', trans('all.datasudahada'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('mesin','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM mesin WHERE id=:idmesin AND del = "t" LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idmesin', $id);
            $stmt->execute();
            $mesin = $stmt->fetch(PDO::FETCH_OBJ);
            if($stmt->rowCount() == 0){
                abort(404);
            }

            $atribut = Utils::getAtributdanAtributNilaiCrud($id, 'mesin');
            $slideshows = SlideShow::select('id', 'nama')->get();
            $fingerprintconnector = Utils::getData($pdo,'fingerprintconnector','id,nama','','nama');
            $datalokasi = Utils::getData($pdo,'lokasi','lat,lon,nama','','nama');
            Utils::insertLogUser('akses menu ubah mesin');
            return view('datainduk/absensi/mesin/edit', ['fingerprintconnector' => $fingerprintconnector, 'datalokasi' => $datalokasi, 'mesin' => $mesin, 'arratribut' => $atribut, 'slideshows' => $slideshows, 'menu' => 'mesin']);
        } else {
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idmesin ada
        $sql = 'SELECT id,nama,jenis,IFNULL(gcmid,"") as gcmid FROM mesin WHERE id=:idmesin LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idmesin', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row['jenis'] == 'fingerprint'){
                $fp_komunikasi = $request->metode_utility_mesin.
                                $request->metode_pegawai_read.
                                $request->metode_pegawai_insert.
                                $request->metode_pegawai_delete.
                                $request->metode_fingersample_read.
                                $request->metode_fingersample_insert.
                                $request->metode_fingersample_delete.
                                $request->metode_logabsen_read.
                                $request->metode_logabsen_deleteall.
                                $request->kunci_saat_sinkron.
                                $request->kunci_setelah_deleteall.
                                $request->restart_setelah_delete_all;

                $mesin = Mesin::find($id);
                $mesin->nama = $request->nama;
                $mesin->cekjamserver = $request->cekjamserver;
                $mesin->utcdefault = $request->utcdefault;
                $mesin->utc = $request->utc;
                $mesin->ijinkanpendaftaran = $request->ijinkanpendaftaran;
                $mesin->idslideshow = ($request->slideshow == '' ? NULL : $request->slideshow);
                $mesin->getid_opsi = $request->getid_opsi;
                $mesin->perangkat_bt_rfidnfc = $request->perangkat_bt_rfidnfc;
                $mesin->perangkat_bt_bukakunci = $request->perangkat_bt_bukakunci;
                $mesin->fp_comkey = $request->fp_comkey;
                $mesin->fp_ip = $request->fp_ip;
                $mesin->fp_soapport = $request->fp_soapport;
                $mesin->fp_udpport = $request->fp_udpport;
                $mesin->fp_idfingerprintconnector = ($request->fp_idfingerprintconnector == '' ? NULL : $request->fp_idfingerprintconnector);
                $mesin->fp_serialnumber = $request->fp_serialnumber;
                $mesin->fp_algoritma = $request->fp_algoritma;
                $mesin->fp_intervaltarik = $request->fp_intervaltarik;
                $mesin->fp_ijinkanadmin = $request->fp_ijinkanadmin;
                $mesin->fp_lat = $request->fp_lat;
                $mesin->fp_lon = $request->fp_lon;
                $mesin->fp_komunikasi = $fp_komunikasi;
                $mesin->fp_status = $request->fp_status;
                $mesin->save();
            }else{
                $mesin = Mesin::find($id);
                $mesin->nama = $request->nama;
                $mesin->cekjamserver = $request->cekjamserver;
                $mesin->utcdefault = $request->utcdefault;
                $mesin->utc = $request->utc;
                $mesin->fixgps_gunakan = $request->fixgps_gunakan;
                $mesin->fixgps_latitude = $request->fixgps_latitude;
                $mesin->fixgps_longitude = $request->fixgps_longitude;
                $mesin->deteksiekspresi = $request->deteksiekspresi;
                $mesin->ijinkanpendaftaran = $request->ijinkanpendaftaran;
                $mesin->idslideshow = ($request->slideshow == '' ? NULL : $request->slideshow);
                $mesin->getid_opsi = $request->getid_opsi;
                $mesin->kamera_opsi = $request->kamera_opsi;
                $mesin->perangkat_bt_rfidnfc = $request->perangkat_bt_rfidnfc;
                $mesin->perangkat_bt_bukakunci = $request->perangkat_bt_bukakunci;
                $mesin->save();
            }

            MesinAtribut::where('idmesin', '=', $id)->delete();

            if ($request->atribut != '') {
                for ($i = 0; $i < count($request->atribut); $i++) {
                    $mesinatribut = new MesinAtribut;
                    $mesinatribut->idmesin = $mesin->id;
                    $mesinatribut->idatributnilai = $request->atribut[$i];
                    $mesinatribut->save();
                }
            }

            //sinkronisasi jika ada gcmid
            if($row['gcmid'] != ''){
                $url = 'https://fcm.googleapis.com/fcm/send';

                $headers = array(
                    'Authorization: key=' . config('consts.GCM_API_KEY'),
                    'Content-Type: application/json'
                );

                $fields = array(
                    'content_available' => true,
                    'to' => $row['gcmid'],
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
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  0);

                curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
                curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

                // Execute post
                $result = curl_exec($ch);
                if ($result === FALSE) {
                    die('Curl failed: ' . curl_error($ch));
                }

                // Close connection
                curl_close($ch);
            }

            Utils::insertLogUser('Ubah mesin "' . $row['nama'] . '" => "' . $request->nama . '"');

            return redirect('datainduk/absensi/mesin')->with('message', trans('all.databerhasildiubah'));
        } else {
            return redirect('datainduk/absensi/mesin/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('mesin','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'mesin','nama','id',$id);
            if($cekadadata != ''){
                //Mesin::find($id)->delete();
                $sql = 'UPDATE mesin SET del = "y" WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::insertLogUser('Hapus mesin "' . $cekadadata . '"');

                return redirect('datainduk/absensi/mesin')->with('message', trans('all.databerhasildihapus'));
            } else {
                return redirect('datainduk/absensi/mesin')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function verifikasi(Request $request,$id,$jenis)
    {
        if(Utils::cekHakakses('mesin','um')){
            $isOnboarding = $request->query('onboarding');
            if($jenis == 'verifikasi') {
                return view('datainduk/absensi/mesin/verifikasi', ['idmesin' => $id, 'menu' => 'mesin', 'onboarding' => $isOnboarding]);
            }else{
                //putussambungan
                $pdo = DB::connection('perusahaan_db')->getPdo();
                $cekadadata = Utils::getDataWhere($pdo,'mesin','nama','id',$id);
                if($cekadadata != ''){
                    $status = Utils::getDataWhere($pdo,'mesin','status','id',$id);
                    if ($status == 'th') {
                        $sql = 'UPDATE mesin SET deviceid=NULL, deviceidreset=NULL, status="bs" WHERE id=:idmesin';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':idmesin', $id);
                        $stmt->execute();
                        Utils::insertLogUser('Memutus sambungan mesin "' . $cekadadata . '"');
                        return redirect('datainduk/absensi/mesin')->with('message', trans('all.sambunganmesinberhasildiputuskan'));
                    } else {
                        return redirect('datainduk/absensi/mesin')->with('message', trans('all.mesindalamkondisibebas'));
                    }
                } else {
                    return redirect('datainduk/absensi/mesin')->with('message', trans('all.datatidakditemukan'));
                }
            }
        } else {
            return redirect('/');
        }
    }

    public function submitVerifikasi(Request $request, $id)
    {

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $pdo2 = DB::getPdo();
        $sql = 'SELECT kode FROM perusahaan WHERE id=:idperusahaan LIMIT 1';
        $stmt = $pdo2->prepare($sql);
        $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $kode = $row['kode'];

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

                $with = [
                    'alert'=>trans('all.kodeverifikasi').' : '.$kodeverifikasi
                  ];
  
                if(Session::get('onboardingstep')==5) {
                    $pdo = DB::getPdo();
                    $sql = 'UPDATE user SET onboardingstep = :step WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':step', 6);
                    $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
                    $stmt->execute();
                }

                $isOnboarding = $request->query('onboarding');
                if($isOnboarding) {
                    return redirect('datainduk/absensi/mesin?onboarding=true')->with($with);
                }
  
                return redirect('datainduk/absensi/mesin')->with($with);

            } else {
                return redirect('datainduk/absensi/mesin')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('datainduk/absensi/mesin')->with('message', trans('all.perusahaantidakditemukan'));
        }
    }

    public function pengaturanFingerPrint($id)
    {
        if(Utils::cekHakakses('mesin','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT
                        m.id as idmesin,
                        m.nama,
                        m.fp_comkey,
                        m.fp_ip,
                        m.fp_soapport,
                        m.fp_udpport,
                        fpc.nama as fp_connector,
                        m.fp_serialnumber,
                        fpc.pushapi as fp_pushapi
                    FROM
                        mesin m
                        LEFT JOIN fingerprintconnector fpc ON m.fp_idfingerprintconnector=fpc.id
                    WHERE
                        m.id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            return view('datainduk/absensi/mesin/pengaturanfingerprint', ['data' => $data, 'menu' => 'mesin']);
        }else{
            return redirect('/');
        }
    }

    public function submitPengaturanFingerPrint(Request $request, $id)
    {
        $url = $request->url;

        $ch = curl_init($url);

        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->methods);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->objectdata);
        $output = curl_exec ($ch);
        $info = curl_getinfo($ch);
        $http_result = $info ['http_code'];
        curl_close ($ch);

        //return $request->objectdata;
        return response()->json(json_decode($output));
        //return json_encode($output);
    }

    public function importPegawaiFingerPrint(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $lanjut = false;
        $msg = '';
//        return $request->tumpukdatajikakembar;

        $idpegawai = 0;
        $sql = 'SELECT id FROM pegawai WHERE pin = :pin LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':pin', $request->pin);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $idpegawai = $row['id'];
        }

        if($idpegawai!=0) {
            if ($request->tumpukdatajikakembar == '1') {
                $sql = 'UPDATE pegawai SET nama=:nama WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':id', $idpegawai);
                $stmt->execute();

                Utils::insertLogUser('Tambah pegawai melalui pengguna fingerprint (tumpuk)');

                $lanjut = true;
            } else {
                $msg = trans('all.datasudahada');
            }
        } else {
                $sql = 'INSERT INTO pegawai VALUES(NULL,:nama,NULL,:pin,NULL,"","","","t","a",NOW(),NULL,NULL,NULL,NOW(),"t",NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':pin', $request->pin);
                $stmt->execute();

                $idpegawai = $pdo->lastInsertId();

                Utils::insertLogUser('Tambah pegawai melalui pengguna fingerprint');

                $lanjut = true;
        }
        if ($lanjut==true) {
            if ($request->simpanpakaifingersample == '1') {
                //minta data ke fingerprint melalui CURL
                $url = $request->url;

                $ch = curl_init($url);

                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request->objectdata);
                $output = curl_exec ($ch);
                $info = curl_getinfo($ch);
                $http_result = $info ['http_code'];
                curl_close ($ch);

                $json = json_decode($output);
                if ($json->status=='OK') {
                    $algoritma = $json->algoritma;
                    for ($i = 0; $i < count($json->data); $i++) {
                        $sql = 'INSERT IGNORE INTO fingersample VALUES(NULL,:idpegawai,:algoritma,:finger_id,:size,:valid,:template,MD5(:checksum),NOW(),NULL)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idpegawai', $idpegawai);
                        $stmt->bindValue(':algoritma', $algoritma);
                        $stmt->bindValue(':finger_id', $json->data[$i]->FingerID);
                        $stmt->bindValue(':size', $json->data[$i]->Size);
                        $stmt->bindValue(':valid', $json->data[$i]->Valid);
                        $stmt->bindValue(':template', $json->data[$i]->Template);
                        $stmt->bindValue(':checksum', $json->data[$i]->Template);
                        $stmt->execute();
                    }

                    $msg = 'ok';
                }
                else {
                    $msg = 'error';
                }
            }
            else {
                $msg = 'ok';
            }
        }
        return $msg;
    }

    public function importSemuaPegawaiFingerPrint(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $url = $request->url;

        $ch = curl_init($url);

        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->methods);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->objectdata);
        $output = curl_exec ($ch);
        $info = curl_getinfo($ch);
        $http_result = $info ['http_code'];
        curl_close ($ch);

        $json = json_decode($output);
        if ($json->status=='OK') {
            for ($i = 0; $i < count($json->data); $i++) {
                $idpegawai = 0;
                $lanjut = false;
                $sql = 'SELECT id FROM pegawai WHERE pin = :pin LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':pin', $json->data[$i]->pin);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $idpegawai = $row['id'];
                }

                if($idpegawai!=0) {
                    if ($request->tumpukdatajikakembar == '1') {
                        $sql = 'UPDATE pegawai SET nama=:nama WHERE id=:id';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':nama', $json->data[$i]->nama);
                        $stmt->bindValue(':id', $idpegawai);
                        $stmt->execute();

                        Utils::insertLogUser('Tambah pegawai melalui pengguna fingerprint (tumpuk)');

                        $lanjut = true;
                    }
                } else {
                    $sql = 'INSERT INTO pegawai VALUES(NULL,:nama,NULL,:pin,NULL,"","","","t","a",NOW(),NULL,NULL,NULL,NOW(),"t",NULL)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':nama', $json->data[$i]->nama);
                    $stmt->bindValue(':pin', $json->data[$i]->pin);
                    $stmt->execute();

                    $idpegawai = $pdo->lastInsertId();

                    Utils::insertLogUser('Tambah pegawai melalui pengguna fingerprint');

                    $lanjut = true;
                }
                if ($lanjut==true) {
                    if ($request->simpanpakaifingersample == '1') {
                        //minta data ke fingerprint melalui CURL
                        $url2 = $request->pushapi.'/fingersample/get/'.$json->data[$i]->pin;

                        $ch2 = curl_init($url);

                        curl_setopt($ch2, CURLOPT_URL,$url2);
                        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch2, CURLOPT_POSTFIELDS, $request->objectdata);
                        $output2 = curl_exec ($ch2);
                        $info2 = curl_getinfo($ch2);
                        $http_result2 = $info2 ['http_code'];
                        curl_close ($ch2);

                        $json2 = json_decode($output2);
                        if ($json2->status=='OK') {
                            $algoritma = $json2->algoritma;
                            for ($j = 0; $j < count($json2->data); $j++) {
                                $sql = 'INSERT IGNORE INTO fingersample VALUES(NULL,:idpegawai,:algoritma,:finger_id,:size,:valid,:template,MD5(:checksum),NOW(),NULL)';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':idpegawai', $idpegawai);
                                $stmt->bindValue(':algoritma', $algoritma);
                                $stmt->bindValue(':finger_id', $json2->data[$j]->FingerID);
                                $stmt->bindValue(':size', $json2->data[$j]->Size);
                                $stmt->bindValue(':valid', $json2->data[$j]->Valid);
                                $stmt->bindValue(':template', $json2->data[$j]->Template);
                                $stmt->bindValue(':checksum', $json2->data[$j]->Template);
                                $stmt->execute();
                            }
                        }
                    }
                }
            }
            return 'ok';
        }else{
            return $json->status;
        }
    }

    public function importFingerSampleFingerPrint(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM pegawai WHERE pin = :pin';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':pin', $request->pin);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $algoritma = $request->algoritma;
            $sql = 'INSERT IGNORE INTO fingersample VALUES(NULL,:idpegawai,:algoritma,:finger_id,:size,:valid,:template,MD5(:checksum),NOW(),NULL)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $row['id']);
            $stmt->bindValue(':algoritma', $algoritma);
            $stmt->bindValue(':finger_id', $request->fingerid);
            $stmt->bindValue(':size', $request->size);
            $stmt->bindValue(':valid', $request->valid);
            $stmt->bindValue(':template', $request->template);
            $stmt->bindValue(':checksum', $request->template);
            $stmt->execute();

            Utils::insertLogUser('import fingersample fingerprint');

            $msg = 'ok';
        }else{
            $msg = trans('all.datatidakditemukan');
        }
        return $msg;
    }

    public function importSemuaFingerSampleFingerPrint(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $url = $request->url;

        $ch = curl_init($url);

        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->methods);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->objectdata);
        $output = curl_exec ($ch);
        $info = curl_getinfo($ch);
        $http_result = $info ['http_code'];
        curl_close ($ch);

        $json = json_decode($output);
        if ($json->status=='OK') {

            $sql = 'SELECT id FROM pegawai WHERE pin = :pin';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':pin', $request->pin);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $algoritma = $json->algoritma;
                for ($j = 0; $j < count($json->data); $j++) {
                    $sql = 'INSERT IGNORE INTO fingersample VALUES(NULL,:idpegawai,:algoritma,:finger_id,:size,:valid,:template,MD5(:checksum),NOW(),NULL)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $row['id']);
                    $stmt->bindValue(':algoritma', $algoritma);
                    $stmt->bindValue(':finger_id', $json->data[$j]->FingerID);
                    $stmt->bindValue(':size', $json->data[$j]->Size);
                    $stmt->bindValue(':valid', $json->data[$j]->Valid);
                    $stmt->bindValue(':template', $json->data[$j]->Template);
                    $stmt->bindValue(':checksum', $json->data[$j]->Template);
                    $stmt->execute();
                }
                Utils::insertLogUser('import semua fingersample fingerprint');
                return 'ok';
            }else{
                return trans('all.datatidakditemukan');
            }
        }else{
            return $json->status;
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('mesin','l')){
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

            Utils::setPropertiesExcel($objPHPExcel,trans('all.mesin'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $b, trans('all.nama'))
                        ->setCellValue('B' . $b, trans('all.atribut'))
                        ->setCellValue('C' . $b, trans('all.jenis'))
                        ->setCellValue('D' . $b, trans('all.deviceid'))
                        ->setCellValue('E' . $b, trans('all.cekjamserver'))
                        ->setCellValue('F' . $b, trans('all.utc'))
                        ->setCellValue('G' . $b, trans('all.status'));

            $sql = 'SELECT
                        m.id,
                        GROUP_CONCAT(a.nilai ORDER BY a.nilai SEPARATOR ", ") as atribut,
                        m.nama,
                        m.jenis,
                        m.deviceid,
                        m.deviceidreset,
                        IF(m.cekjamserver="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as cekjamserver,
                        if(m.utcdefault = "y", "' . trans("all.default") . '",m.utc) as utcbaru,
                        IF(m.status="bs","' . trans("all.bebas") . '","' . trans("all.terhubung") . '") status
                    FROM
                        mesin m
                        LEFT JOIN mesinatribut ma ON ma.idmesin=m.id
                        LEFT JOIN atributnilai a ON ma.idatributnilai=a.id
                    GROUP BY
                        m.id
                    ORDER BY
                        m.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = $b + 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['atribut']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, trans('all.'.$row['jenis']));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['deviceid']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['cekjamserver']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['utcbaru']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['status']);

                // center text
                $objPHPExcel->getActiveSheet()->getStyle('E' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                for ($j = 1; $j <= 7; $j++) {
                    $huruf = Utils::angkaToHuruf($j);
                    $objPHPExcel->getActiveSheet()->getStyle($huruf . $i)->applyFromArray($styleArray);
                }

                $i++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('E' . $b)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('G' . $b)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $arrWidth = array('', 40, 100, 15, 15, 15, 12, 12);
            for ($j = 1; $j <= 7; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            $heightgambar = 99;
            $widthgambar = 99;

            // style garis
            $end_i = $i - 1;
            $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $end_i)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $b)->applyFromArray($styleArray);

            $sql = 'SELECT * FROM parameterekspor';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($rowPE['footerkiri_1_teks'] == '' AND $rowPE['footerkiri_2_teks'] == '' AND $rowPE['footerkiri_3_teks'] == '' AND $rowPE['footerkiri_5_teks'] == '' AND $rowPE['footerkiri_6_teks'] == '' AND $rowPE['footertengah_1_teks'] == '' AND $rowPE['footertengah_2_teks'] == '' AND $rowPE['footertengah_3_teks'] == '' AND $rowPE['footertengah_5_teks'] == '' AND $rowPE['footertengah_6_teks'] == '' AND $rowPE['footerkanan_1_teks'] == '' AND $rowPE['footerkanan_2_teks'] == '' AND $rowPE['footerkanan_3_teks'] == '' AND $rowPE['footerkanan_5_teks'] == '' AND $rowPE['footerkanan_6_teks'] == '') {
                $l = $i - 1;
            } else {
                // footer kiri
                $l = $i + 1;
                Utils::footerExcel($objPHPExcel,'kiri','A','A',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'tengah','B','D',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'kanan','F','G',$l,$rowPE);
                $l = $l + 7;
            }

            // password
            Utils::passwordExcel($objPHPExcel);

            if ($b != 1) {
                Utils::header5baris($objPHPExcel,'G',$rowPE);
            }

            //footer tanggal file dibuat
            date_default_timezone_set('Asia/Jakarta');
            $ft = $l + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $ft, '*tanggal pembuatan file ' . date('d/m/Y H:i:s'));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $ft)->getFont()->setItalic(true);

            if ($rowPE['logokiri'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokiri'];
                Utils::logoExcel('kiri',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'A1');
            }

            if ($rowPE['logokanan'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokanan'];
                Utils::logoExcel('kanan',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'G1');
            }

            Utils::insertLogUser('Ekspor mesin');
            Utils::setFileNameExcel(trans('all.mesin'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}