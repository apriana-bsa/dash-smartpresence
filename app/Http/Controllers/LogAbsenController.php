<?php
namespace App\Http\Controllers;

use App\LogAbsen;
use App\Mesin;
use App\AlasanMasukKeluar;
use App\Utils;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use Storage;
use Hash;
use Response;
use File;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Worksheet_MemoryDrawing;

class LogAbsenController extends Controller
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
        if(Utils::cekHakakses('logabsen','l')){
            $bulanterpilih = date('m');
            $tahunterpilih = date('Y');
            if(Session::has('logabsen_bulanterpilih') && Session::has('logabsen_tahunterpilih')){
                $bulanterpilih = Session::get('logabsen_bulanterpilih');
                $tahunterpilih = Session::get('logabsen_tahunterpilih');
            }else{
                Session::set('logabsen_bulanterpilih', $bulanterpilih);
                Session::set('logabsen_tahunterpilih', $tahunterpilih);
            }
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'CALL getpegawailengkap_blade(@_atributpenting_controller, @_atributpenting_blade, @_atributvariablepenting_controller, @_atributvariablepenting_blade)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'SELECT @_atributpenting_controller as atributpenting_controller, @_atributpenting_blade as atributpenting_blade, @_atributvariablepenting_controller as atributvariablepenting_controller, @_atributvariablepenting_blade as atributvariablepenting_blade';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $atributpenting_controller = explode('|', $row['atributpenting_controller']);
            $atributpenting_blade = explode('|', $row['atributpenting_blade']);
            $atributvariablepenting_controller = explode('|', $row['atributvariablepenting_controller']);
            $atributvariablepenting_blade = explode('|', $row['atributvariablepenting_blade']);
            Utils::insertLogUser('akses menu log absen');
            return view('datainduk/absensi/logabsen/index', ['atributpenting_controller' => $atributpenting_controller, 'atributpenting_blade' => $atributpenting_blade, 'atributvariablepenting_controller' => $atributvariablepenting_controller, 'atributvariablepenting_blade' => $atributvariablepenting_blade, 'tahun' => Utils::tahunDropdown(), 'bulanterpilih' => $bulanterpilih, 'tahunterpilih' => $tahunterpilih, 'menu' => 'logabsen']);
        } else {
            return redirect('/');
        }
    }

    public function submitPeriode(Request $request){
        Session::set('logabsen_bulanterpilih', $request->bulan);
        Session::set('logabsen_tahunterpilih', $request->tahun);
        return redirect('datainduk/absensi/logabsen');
    }

    public function show(Request $request)
    {
        if(Utils::cekHakakses('logabsen','l')){
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            $firstdate = Session::get('logabsen_tahunterpilih').'-'.Session::get('logabsen_bulanterpilih').'-01';
            $lastdate = date("Y-m-t", strtotime($firstdate));

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $wherebatasan = '';
            if ($batasan != '') {
                $wherebatasan .= ' AND id IN ' . $batasan;
            }
            $sql = 'CALL getpegawailengkap_controller(@_atributpenting, @_atributvariablepenting, "' . str_replace('"', "'", $wherebatasan) . '")';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'SELECT @_atributpenting as atributpenting, @_atributvariablepenting as atributvariablepenting';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $atributpenting = $row['atributpenting'];
            $atributvariablepenting = $row['atributvariablepenting'];

            $where = ' AND waktu>="'.$firstdate.' 00:00:00" AND waktu<="'.$lastdate.' 23:59:59"';
            if ($batasan != '') {
                $where .= ' AND idpegawai IN ' . $batasan;
            }
            if(Utils::cekHakakses('logabsen','uhm')){
//                $columns = array('', 'waktu', 'pegawai', 'mesin', 'masukkeluar', 'alasanmasukkeluar', 'terhitungkerja', 'konfirmasi', 'status');
                $stringcolumn = ',waktu,pegawai,'.$atributvariablepenting.'mesin,masukkeluar,alasanmasukkeluar,terhitungkerja,konfirmasi,status'.$atributpenting;
            }else{
//                $columns = array('waktu', 'pegawai', 'mesin', 'masukkeluar', 'alasanmasukkeluar', 'terhitungkerja', 'konfirmasi', 'status');
                $stringcolumn = 'waktu,pegawai,'.$atributvariablepenting.'mesin,masukkeluar,alasanmasukkeluar,terhitungkerja,konfirmasi,status'.$atributpenting;
            }
            $columns = explode(',',$stringcolumn);
            $table = '(
                        SELECT
                            l.id,
                            p.id as idpegawai,
                            l.waktu,
                            p.nama as pegawai,
                            ' . $atributvariablepenting . '
                            m.nama as mesin,
                            l.masukkeluar,
                            a.alasan as alasanmasukkeluar,
                            l.terhitungkerja,
                            l.status,
                            l.sumber,
                            IFNULL(l.konfirmasi,"") as konfirmasi
                            ' . $atributpenting . '
                        FROM
                            logabsen l
                            LEFT JOIN mesin m ON l.idmesin=m.id
                            LEFT JOIN alasanmasukkeluar a ON l.idalasanmasukkeluar=a.id,
                            pegawai p,
                            _pegawailengkap _pa
                        WHERE
                            l.idpegawai=p.id AND
                            _pa.id=p.id
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
                    if(Utils::cekHakakses('logabsen','um')){
                        $action .= Utils::tombolManipulasi('ubah','logabsen',$key['id']);
                    }
                    if(Utils::cekHakakses('logabsen','hm')){
                        $action .= Utils::tombolManipulasi('hapus','logabsen',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>' . $action . '</center>';
                        }elseif($columns[$i] == 'pegawai') {
                            $tempdata[$columns[$i]] = '<span class="detailpegawai" onclick="detailpegawai(' . $key['idpegawai'] . ')" style="cursor:pointer;">' . $key['pegawai'] . '</span>';
                        }elseif($columns[$i] == 'sumber') {
                            $tempdata[$columns[$i]] = trans('all.'.$key[$columns[$i]]);
                        }elseif($columns[$i] == 'masukkeluar' || $columns[$i] == 'status' || $columns[$i] == 'terhitungkerja') {
                            $kolom = $key[$columns[$i]] == 'v' ? 'valid' : ($key[$columns[$i]] == 'c' ? 'confirm' : ($key[$columns[$i]] == 'na' ? 'notapprove' : $key[$columns[$i]]));
                            $tempdata[$columns[$i]] = Utils::labelKolom($kolom);
                        }elseif($columns[$i] == 'konfirmasi') {
                            $kolom = '';
                            if(strpos($key[$columns[$i]],'l')!==false){
                                $kolom .= '<span class="label label-warning">' . trans('all.lokasi') . '</span>&nbsp;';
                            }
                            if(strpos($key[$columns[$i]],'f')!==false){
                                $kolom .= '<span class="label label-danger">' . trans('all.wajahdiragukan') . '</span>';
                            }
                            $tempdata[$columns[$i]] = '<center>'.$kolom.'</center>';
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
        if(Utils::cekHakakses('logabsen','tm')){

            $mesin = Mesin::select('id', 'nama')->get();

            $alasanmasukkeluar = AlasanMasukKeluar::select('id', 'alasan')->where('digunakan', 'y')->get();
            Utils::insertLogUser('akses menu tambah log absen');
            return view('datainduk/absensi/logabsen/create', ['mesin' => $mesin, 'alasanmasukkeluar' => $alasanmasukkeluar, 'menu' => 'logabsen']);
        } else {
            return redirect('/');
        }
    }

    public function createByAtribut()
    {
        if(Utils::cekHakakses('logabsen','tm')){

            $mesin = Mesin::select('id', 'nama')->get();
            $atribut = Utils::getAtributdanAtributNilaiCrud(0, 'pegawai');
            $alasanmasukkeluar = AlasanMasukKeluar::select('id', 'alasan')->where('digunakan', 'y')->get();
            Utils::insertLogUser('akses menu tambah logabsen berdasarkan atribut');
            return view('datainduk/absensi/logabsen/createbyatribut', ['mesin' => $mesin, 'atribut' => $atribut, 'alasanmasukkeluar' => $alasanmasukkeluar, 'menu' => 'logabsen']);
        } else {
            return redirect('/');
        }
    }

    public function submitCreateByAtribut(Request $request)
    {
        if(Utils::cekHakakses('logabsen','tm')){
            if(Utils::cekDateTime($request->tanggal) && Utils::cekDateTime($request->jam)) {
                $pdo = DB::connection('perusahaan_db')->getPdo();
                $idalasanmasukkeluar = $request->alasan;
                $waktu = $request->tanggal . ' ' . $request->jam;
                $terhitungkerja = 'y';
                $cekbolehubah = Utils::cekKunciDataPosting(Utils::convertDmy2Ymd($request->tanggal) . ' ' . $request->jam);
                if ($cekbolehubah == 0) {
                    if ($idalasanmasukkeluar != '') {
                        $sql = 'SELECT terhitungkerja FROM alasanmasukkeluar WHERE id=:id LIMIT 1';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':id', $idalasanmasukkeluar);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $terhitungkerja = $row['terhitungkerja'];
                        } else {
                            $idalasanmasukkeluar = '';
                        }
                    }

                    try {
                        $pdo->beginTransaction();

                        $atribut = Utils::atributNilai($request->atribut);
                        $sql = 'SELECT p.id FROM pegawai p, pegawaiatribut pa WHERE pa.idpegawai=p.id AND pa.idatributnilai IN(' . $atribut . ')';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();

                        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $arrIdLogabsen = array();
                        for ($i = 0; $i < count($data); $i++) {
                            $sql = 'INSERT INTO logabsen VALUES(NULL, STR_TO_DATE(:waktu,"%d/%m/%Y %T"), :idpegawai, NULL,  :masukkeluar, :idalasanmasukkeluar, :terhitungkerja, NULL, NULL, "v", NULL, NULL, NULL, "manual", :flag, :flagketerangan, :dataasli, NOW(), NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':waktu', $waktu);
                            $stmt->bindValue(':idpegawai', $data[$i]['id']);
                            $stmt->bindValue(':masukkeluar', $request->masukkeluar);
                            if ($idalasanmasukkeluar == '') {
                                $stmt->bindValue(':idalasanmasukkeluar', null, PDO::PARAM_INT);
                            } else {
                                $stmt->bindValue(':idalasanmasukkeluar', $idalasanmasukkeluar);
                            }
                            $stmt->bindValue(':terhitungkerja', $terhitungkerja);
                            $stmt->bindValue(':flag', $request->flag);
                            $stmt->bindValue(':flagketerangan', $request->flagketerangan);
                            $dataasli = $waktu . '|' . $request->pegawai . '|0|' . $request->masukkeluar . '|' . $request->alasan . '|' . $terhitungkerja . '|0|0|v||||manual|' . $request->flag;
                            $stmt->bindValue(':dataasli', $dataasli);
                            $stmt->execute();

                            $arrIdLogabsen[$i] = $pdo->lastInsertId();
                        }
                        Utils::insertLogUser('Tambah log absen berdasarkan atribut "' . $waktu . '"');
                        $pdo->commit();
                    } catch (\Exception $e) {
                        $pdo->rollBack();
                        return redirect('datainduk/absensi/logabsen/createbyatribut')->with('message', trans('all.terjadigangguan') . $e->getMessage());
                    }

                    // posting absen
                    for ($i = 0; $i < count($arrIdLogabsen); $i++) {
                        $sql = 'CALL hitungrekapabsen_log(:idlogabsen, NULL, NULL)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idlogabsen', $arrIdLogabsen[$i]);
                        $stmt->execute();
                    }

                    return redirect('datainduk/absensi/logabsen')->with('message', trans('all.databerhasildisimpan'));

                } else {
                    return redirect('datainduk/absensi/logabsen')->with('message', trans('all.datatidakbisadirubah') . Utils::getDataWhere($pdo, 'pengaturan', 'kuncidatasebelumtanggal'));
                }
            }
            return redirect('datainduk/absensi/logabsen/createbyatribut')->with('message', trans('all.terjadigangguan'));
        } else {
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        if(Utils::cekDateTime($request->tanggal) && Utils::cekDateTime($request->jam)) {
            // cek apakah nama kembar ?
            $waktu = $request->tanggal . ' ' . $request->jam;
            $terhitungkerja = 'y';
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $idpegawai = $request->pegawai;
            $cekbolehubah = Utils::cekKunciDataPosting(Utils::convertDmy2Ymd($request->tanggal) . ' ' . $request->jam);
            if ($cekbolehubah == 0) {
                try {
                    $pdo->beginTransaction();
                    for ($i = 0; $i < count($idpegawai); $i++) {
                        //pastikan idpegawai ada
                        $sql = 'SELECT id FROM pegawai WHERE id=:idpegawai AND status="a" AND del = "t" LIMIT 1';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idpegawai', $idpegawai[$i]);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            $idalasanmasukkeluar = $request->alasan;
                            if ($idalasanmasukkeluar != '') {
                                $sql = 'SELECT terhitungkerja FROM alasanmasukkeluar WHERE id=:id LIMIT 1';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':id', $idalasanmasukkeluar);
                                $stmt->execute();
                                if ($stmt->rowCount() > 0) {
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $terhitungkerja = $row['terhitungkerja'];
                                } else {
                                    $idalasanmasukkeluar = '';
                                }
                            }

                            $sql = 'SELECT id FROM logabsen WHERE idpegawai=:idpegawai AND waktu=STR_TO_DATE(:waktu,"%d/%m/%Y %T") LIMIT 1';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idpegawai', $idpegawai[$i]);
                            $stmt->bindValue(':waktu', $waktu);
                            $stmt->execute();
                            if ($stmt->rowCount() == 0) {
                                $sql = 'INSERT INTO logabsen VALUES(NULL, STR_TO_DATE(:waktu,"%d/%m/%Y %T"), :idpegawai, NULL,  :masukkeluar, :idalasanmasukkeluar, :terhitungkerja, NULL, NULL, "v", NULL, NULL, NULL, "manual", :flag, :flagketerangan, :dataasli, NOW(), NOW())';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':waktu', $waktu);
                                $stmt->bindValue(':idpegawai', $idpegawai[$i]);
                                $stmt->bindValue(':masukkeluar', $request->masukkeluar);
                                if ($idalasanmasukkeluar == '') {
                                    $stmt->bindValue(':idalasanmasukkeluar', null, PDO::PARAM_INT);
                                } else {
                                    $stmt->bindValue(':idalasanmasukkeluar', $idalasanmasukkeluar);
                                }
                                $stmt->bindValue(':terhitungkerja', $terhitungkerja);
                                $stmt->bindValue(':flag', $request->flag);
                                $stmt->bindValue(':flagketerangan', $request->flagketerangan);
                                $dataasli = $waktu . '|' . $idpegawai[$i] . '|0|' . $request->masukkeluar . '|' . $request->alasan . '|' . $terhitungkerja . '|0|0|v||||manual|' . $request->flag . '|' . $request->flagketerangan;
                                $stmt->bindValue(':dataasli', $dataasli);
                                $stmt->execute();

                                $idlogabsen = $pdo->lastInsertId();

                                // posting absen
                                $sql = 'CALL hitungrekapabsen_log(:idlogabsen, NULL, NULL)';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':idlogabsen', $idlogabsen);
                                $stmt->execute();
                            } else {
                                $pdo->commit();
                                return redirect('datainduk/absensi/logabsen/create')->with('message', trans('all.datasudahada'));
                            }
                        } else {
                            $pdo->commit();
                            return redirect('datainduk/absensi/logabsen/create')->with('message', trans('all.pegawaitidakditemukan'));
                        }
                    }
                    Utils::insertLogUser('Tambah log absen "' . $waktu . '"');
                    $pdo->commit();
                    return redirect('datainduk/absensi/logabsen')->with('message', trans('all.databerhasildisimpan'));
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return redirect('datainduk/absensi/logabsen/create')->with('message', trans('all.terjadigangguan') . $e->getMessage());
                }
            } else {
                return redirect('datainduk/absensi/logabsen/create')->with('message', trans('all.datatidakbisadirubah') . Utils::getDataWhere($pdo, 'pengaturan', 'kuncidatasebelumtanggal'));
            }
        }else{
            return redirect('datainduk/absensi/logabsen/create')->with('message', trans('all.terjadigangguan'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('logabsen','um')){

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT
                      l.id,
                      p.nama as pegawai,
                      l.idalasanmasukkeluar,
                      DATE_FORMAT(l.waktu, "%d/%m/%Y") as tanggal,
                      DATE_FORMAT(l.waktu, "%H:%i:%s") as jam,
                      l.masukkeluar,
                      l.status,
                      l.flag,
                      l.flag_keterangan
                    FROM
                      logabsen l
                      LEFT JOIN pegawai p ON l.idpegawai=p.id
                    WHERE
                      l.id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $logabsen = $stmt->fetch(PDO::FETCH_OBJ);

            $alasanmasukkeluar = AlasanMasukKeluar::select('id', 'alasan')->where('digunakan', 'y')->get();

            if (!$id) {
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah log absen ');
            return view('datainduk/absensi/logabsen/edit', ['logabsen' => $logabsen, 'alasanmasukkeluar' => $alasanmasukkeluar, 'menu' => 'logabsen']);
        } else {
            return redirect('/');
        }
    }

    public function update(Request $request, $idlogabsen)
    {
        if(Utils::cekDateTime($request->tanggal) && Utils::cekDateTime($request->jam)) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $waktu = $request->tanggal . ' ' . $request->jam;
            $cekbolehubah = Utils::cekKunciDataPosting(Utils::convertDmy2Ymd($request->tanggal) . ' ' . $request->jam);
            if ($cekbolehubah == 0) {
                $sql = 'SELECT la.idpegawai, la.waktu, la.status, IFNULL(DATE_FORMAT(la.waktu, "%Y%m%d%H%i%s"),"") as waktu01, IFNULL(p.gcmid,"") as gcmid FROM logabsen la, pegawai p WHERE la.idpegawai=p.id AND p.del = "t" AND la.id=:idlogabsen LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idlogabsen', $idlogabsen);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_posting_idpegawai = $row['idpegawai'];
                    $_posting_waktu = $row['waktu'];
                    $_posting_status = $row['status'];
                    $gcm_waktu = $row['waktu01'];
                    $gcm_gcmid = $row['gcmid'];

                    $idalasanmasukkeluar = $request->alasan;
                    $terhitungkerja = 'y';
                    if ($idalasanmasukkeluar != '') {
                        $sql = 'SELECT terhitungkerja FROM alasanmasukkeluar WHERE id=:id LIMIT 1';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':id', $idalasanmasukkeluar);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $terhitungkerja = $row['terhitungkerja'];
                        } else {
                            $idalasanmasukkeluar = '';
                        }
                    }

                    try {

                        $sql = 'UPDATE logabsen SET waktu = STR_TO_DATE(:waktu,"%d/%m/%Y %T"), idalasanmasukkeluar = :idalasanmasukkeluar, terhitungkerja = :terhitungkerja, masukkeluar = :masukkeluar, status = :status, flag = :flag, flag_keterangan = :flagketerangan, updated=NOW() WHERE id=:idlogabsen';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':waktu', $waktu);
                        if ($request->alasan == '') {
                            $stmt->bindValue(':idalasanmasukkeluar', null, PDO::PARAM_INT);
                        } else {
                            $stmt->bindValue(':idalasanmasukkeluar', $idalasanmasukkeluar);
                        }
                        $stmt->bindValue(':terhitungkerja', $terhitungkerja);
                        $stmt->bindValue(':masukkeluar', $request->masukkeluar);
                        $stmt->bindValue(':status', $request->status);
                        $stmt->bindValue(':flag', $request->flag);
                        $stmt->bindValue(':flagketerangan', $request->flagketerangan);
                        $stmt->bindValue(':idlogabsen', $idlogabsen);
                        $stmt->execute();

                        // posting ulang untuk data sebelum diupdate
                        $sql = 'CALL hitungrekapabsen_log(NULL, :idpegawai, :waktu)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam('idpegawai', $_posting_idpegawai);
                        $stmt->bindParam('waktu', $_posting_waktu);
                        $stmt->execute();

                        // posting ulang untuk data setelah diupdate
                        $sql = 'CALL hitungrekapabsen_log(:idlogabsen, NULL, NULL)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam('idlogabsen', $idlogabsen);
                        $stmt->execute();

                        //hanya kirim gcm jika status nya ada (ada pada parameter) dan berubah dari yg sebelumnya.
                        if ($gcm_gcmid != '') {
                            if (isset($request->status)) {
                                if ($request->status != $_posting_status) {
                                    if (isset($waktu)) {
                                        $gcm_waktu = $waktu;
                                    }
                                    //kirim gcm info absen
                                    Utils::kirimGCM($gcm_gcmid, 'konfirmasi', 'server', 'logabsen|' . $idlogabsen . '|' . $request->status . '|' . $gcm_waktu);
                                }
                            }
                        }

                        Utils::insertLogUser('Ubah log absen "' . Utils::tanggalCantik($_posting_waktu) . '" => "' . Utils::tanggalCantik(Utils::convertDmy2Ymd($request->tanggal) . ' ' . $request->jam) . '"');

                        return redirect('datainduk/absensi/logabsen')->with('message', trans('all.databerhasildiubah'));
                    } catch (\Exception $e) {
                        return redirect('datainduk/absensi/logabsen/' . $idlogabsen . '/edit')->with('message', Utils::errHandlerMsg($e->getMessage()));
                    }
                } else {
                    return redirect('datainduk/absensi/logabsen/' . $idlogabsen . '/edit')->with('message', trans('all.datatidakditemukan'));
                }
            } else {
                return redirect('datainduk/absensi/logabsen/' . $idlogabsen . '/edit')->with('message', trans('all.datatidakbisadirubah') . Utils::getDataWhere($pdo, 'pengaturan', 'kuncidatasebelumtanggal'));
            }
        }else{
            return redirect('datainduk/absensi/logabsen/' . $idlogabsen . '/edit')->with('message', trans('all.terjadigangguan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('logabsen','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT 
                        la.idpegawai,
                        la.waktu,
                        la.masukkeluar,
                        DATE_FORMAT(la.waktu, "%Y%m%d%H%i%s") as foto_waktu,
                        la.filename as foto_filename
                    FROM
                        logabsen la
                    WHERE
                        la.id=:idlogabsen
                    LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idlogabsen', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $_posting_idpegawai = $row['idpegawai'];
                $_posting_waktu = $row['waktu'];
                $foto_waktu = $row['foto_waktu'];
                $foto_filename = $row['foto_filename'];
                $masukkeluar = $row['masukkeluar'];

                $cekbolehubah = Utils::cekKunciDataPosting($_posting_waktu);
                if($cekbolehubah == 0) {
                    $sql = 'INSERT INTO logabsen_del 
                              SELECT
                                NULL as id,
                                waktu,
                                idpegawai,
                                idmesin,
                                masukkeluar,
                                idalasanmasukkeluar,
                                terhitungkerja,
                                lat,
                                lon,
                                status,
                                konfirmasi,
                                filename,
                                checksum,
                                sumber,
                                flag,
                                flag_keterangan,
                                dataasli,
                                inserted,
                                updated,                  
                                NOW() as del_waktu
                              FROM
                                logabsen
                              WHERE
                                id=:idlogabsen';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':idlogabsen', $id);
                    $stmt->execute();

                    // hapus fotonya
                    $filename = $_SERVER['DOCUMENT_ROOT'] . '/logabsen/' . Utils::id2Folder($_posting_idpegawai) . '/' . $_posting_idpegawai . '/' . substr($foto_waktu, 0, 4) . '/' . substr($foto_waktu, 4, 2) . '/' . substr($foto_waktu, 6, 2) . '/' . $foto_filename;

                    if (file_exists($filename)) {
                        // hapus foto
                        if (file_exists($filename . '_thumb')) {
                            unlink($filename . '_thumb');
                        }
                        if (!unlink($filename)) {
                            return redirect("datainduk/absensi/logabsen")->with('message', trans('all.terjadigangguan'));
                        }
                    }

                    LogAbsen::find($id)->delete();

                    // posting ulang
                    $sql = 'CALL hitungrekapabsen_log(NULL, :idpegawai, :waktu)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $_posting_idpegawai);
                    $stmt->bindValue(':waktu', $_posting_waktu);
                    $stmt->execute();

                    Utils::insertLogUser('Hapus log presensi ' . ($masukkeluar == 'm' ? 'masuk' : 'keluar') . ' ' . Utils::getNamaPegawai($_posting_idpegawai) . ' ' . Utils::tanggalCantik($_posting_waktu));

                    return redirect('datainduk/absensi/logabsen')->with('message', trans('all.databerhasildihapus'));
                } else {
                    return redirect('datainduk/absensi/logabsen')->with('message', trans('all.datatidakbisadirubah').Utils::getDataWhere($pdo,'pengaturan','kuncidatasebelumtanggal'));
                }
            } else {
                return redirect('datainduk/absensi/logabsen')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('logabsen','l')){
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

            Utils::setPropertiesExcel($objPHPExcel,trans('all.logabsen'));

            $sql = 'SELECT * FROM parameterekspor';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rowPE['logokiri'] == '' AND $rowPE['logokanan'] == '' AND $rowPE['header_1_teks'] == '' AND $rowPE['header_2_teks'] == '' AND $rowPE['header_3_teks'] == '' AND $rowPE['header_4_teks'] == '' AND $rowPE['header_5_teks'] == '') {
                $b = 1; //b = baris
            } else {
                $b = 7;
            }

            //get atribut
            $allatribut = Utils::getAllAtribut('blade');
            $atributpenting_controller = ($allatribut['atributpenting_controller'] != '' ? explode('|', $allatribut['atributpenting_controller']) : '');
            $atributpenting_blade = explode('|', $allatribut['atributpenting_blade']);
            $atributvariablepenting_controller = ($allatribut['atributvariablepenting_controller'] != '' ? explode('|', $allatribut['atributvariablepenting_controller']) : '');
            $atributvariablepenting_blade = explode('|', $allatribut['atributvariablepenting_blade']);
            $totalatributvariable = ($atributvariablepenting_controller != '' ? count($atributvariablepenting_controller) : 0);
            $totalatributpenting = ($atributpenting_controller != '' ? count($atributpenting_controller) : 0);

            //set atribut variable
            $ih = 3; //letak mulai setelah kolom fix (i header)
            if ($atributvariablepenting_blade != '') {
                //looping untuk header
                foreach ($atributvariablepenting_blade as $key) {
                    if ($key != '') {
                        $hh = Utils::angkaToHuruf($ih);
                        $objPHPExcel->getActiveSheet()->setCellValue($hh . $b, $key);
                        //lebar kolom
                        $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                        //set bold
                        $objPHPExcel->getActiveSheet()->getStyle($hh . $b)->getFont()->setBold(true);
                        //style
                        $objPHPExcel->getActiveSheet()->getStyle($hh . $b)->applyFromArray($styleArray);

                        $ih++;
                    }
                }
            }

            //set value kolom
            $h1 = Utils::angkaToHuruf($ih);
            $h2 = Utils::angkaToHuruf($ih + 1);
            $h3 = Utils::angkaToHuruf($ih + 2);
            $h4 = Utils::angkaToHuruf($ih + 3);
            $h5 = Utils::angkaToHuruf($ih + 4);
            $h6 = Utils::angkaToHuruf($ih + 5);
            $h7 = Utils::angkaToHuruf($ih + 6);

            //set atribut penting
            $ha = $h7;
            $hh = $h7;
            if ($atributpenting_blade != '') {
                //looping untuk header
                foreach ($atributpenting_blade as $key) {
                    if ($key != '') {
                        $hi = $ih + 7;
                        $hh = Utils::angkaToHuruf($hi);
                        $objPHPExcel->getActiveSheet()->setCellValue($hh . $b, $key);
                        //lebar kolom
                        $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                        //set bold
                        $objPHPExcel->getActiveSheet()->getStyle($hh . $b)->getFont()->setBold(true);
                        //style
                        $objPHPExcel->getActiveSheet()->getStyle($hh . $b)->applyFromArray($styleArray);

                        $ih++;
                    }
                }
                $ha = $hh;
            }

            //set atribut untuk query
            $whereAtribut = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $whereAtribut = ' AND id IN ' . $batasan;
            }
            $allatribut_controller = Utils::getAllAtribut('controller', $whereAtribut);
            $atributpenting = $allatribut_controller['atributpenting'];
            $atributvariablepenting = $allatribut_controller['atributvariablepenting'];

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $b, trans('all.waktu'))
                        ->setCellValue('B' . $b, trans('all.pegawai'))
                        ->setCellValue($h1 . $b, trans('all.mesin'))
                        ->setCellValue($h2 . $b, trans('all.masukkeluar'))
                        ->setCellValue($h3 . $b, trans('all.alasan'))
                        ->setCellValue($h4 . $b, trans('all.terhitungkerja'))
                        ->setCellValue($h5 . $b, trans('all.konfirmasi'))
                        ->setCellValue($h6 . $b, trans('all.sumber'))
                        ->setCellValue($h7 . $b, trans('all.status'));

            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $batasan = ' AND p.id IN ' . $batasan;
            }

            $firstdate = Session::get('logabsen_tahunterpilih').'-'.Session::get('logabsen_bulanterpilih').'-01';
            $lastdate = date("Y-m-t", strtotime($firstdate));

            $sql = 'SELECT
                        l.id,
                        (DATEDIFF(l.waktu,"1900-01-01")+2)+ROUND(time_to_sec(timediff(DATE_FORMAT(l.waktu,"%T"),"00:00:00"))/86400,9) as waktu,
                        p.nama as pegawai,
                        ' . $atributvariablepenting . '
                        m.nama as mesin,
                        IF(l.masukkeluar="m","' . trans("all.masuk") . '","' . trans("all.keluar") . '") as masukkeluar,
                        a.alasan as alasanmasukkeluar,
                        IF(l.terhitungkerja="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as terhitungkerja,
                        IF(l.status="v","' . trans("all.valid") . '",IF(l.status="c","' . trans("all.konfirmasi") . '",IF(l.status="na","' . trans("all.ditolak") . '","-"))) as status,
                        l.konfirmasi,
                        l.sumber
                        ' . $atributpenting . '
                    FROM
                        logabsen l
                        LEFT JOIN mesin m ON l.idmesin=m.id
                        LEFT JOIN alasanmasukkeluar a ON l.idalasanmasukkeluar=a.id,
                        pegawai p,
                        _pegawailengkap _pa
                    WHERE
                        l.idpegawai=p.id AND
                        l.waktu>="'.$firstdate.' 00:00:00" AND l.waktu<="'.$lastdate.' 23:59:59"
                        '.$batasan.'
                    GROUP BY
                        l.id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = $b + 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $konfirmasi = '';
                if($row['konfirmasi'] == 'l'){
                    $konfirmasi .= trans('all.lokasi');
                }
                if($row['konfirmasi'] == 'f'){
                    $konfirmasi .= trans('all.wajahdiragukan');
                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['waktu']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['pegawai']);
                $objPHPExcel->getActiveSheet()->setCellValue($h1 . $i, $row['mesin']);
                $objPHPExcel->getActiveSheet()->setCellValue($h2 . $i, $row['masukkeluar']);
                $objPHPExcel->getActiveSheet()->setCellValue($h3 . $i, $row['alasanmasukkeluar']);
                $objPHPExcel->getActiveSheet()->setCellValue($h4 . $i, $row['terhitungkerja']);
                $objPHPExcel->getActiveSheet()->setCellValue($h5 . $i, $konfirmasi);
                $objPHPExcel->getActiveSheet()->setCellValue($h6 . $i, trans('all.'.$row['sumber']));
                $objPHPExcel->getActiveSheet()->setCellValue($h7 . $i, $row['status']);

                if($atributvariablepenting_controller != '') {
                    $z1 = 3; //huruf setelah kolom jamkerja
                    for ($j = 0; $j < $totalatributvariable; $j++) {
                        $hv = Utils::angkaToHuruf($z1);
                        $objPHPExcel->getActiveSheet()->setCellValue($hv . $i, $row[$atributvariablepenting_controller[$j]]);

                        $z1++;
                    }
                }

                if($atributpenting_controller != '') {
                    $z2 = 10 + $totalatributvariable; //iterasi untuk looping atribut penting 6 dari jumlah kolom fix
                    for ($j = 0; $j < $totalatributpenting; $j++) {

                        $hap = Utils::angkaToHuruf($z2);
                        $objPHPExcel->getActiveSheet()->setCellValue($hap . $i, $row[$atributpenting_controller[$j]]);

                        $z2++;
                    }
                }

                // format
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getNumberFormat()->setFormatCode('DD/MM/YYYY HH:MM:SS');

                for ($j = 1; $j <= 9 + $totalatributvariable + $totalatributpenting; $j++) {
                    $huruf = Utils::angkaToHuruf($j);
                    $objPHPExcel->getActiveSheet()->getStyle($huruf . $i)->applyFromArray($styleArray);
                }

                $i++;
            }

            $arrWidth = array('', 19, 40);
            for ($j = 1; $j <= 9 + $totalatributvariable; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->applyFromArray($styleArray);
                if($j < 3) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
                }
            }

            $objPHPExcel->getActiveSheet()->getColumnDimension($h1)->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension($h2)->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension($h3)->setWidth(19);
            $objPHPExcel->getActiveSheet()->getColumnDimension($h4)->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension($h5)->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension($h6)->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension($h7)->setWidth(12);

            $heightgambar = 99;
            $widthgambar = 99;

            // style garis
            $end_i = $i - 1;
            $objPHPExcel->getActiveSheet()->getStyle('A1:'.$ha . $end_i)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('A1:'.$ha.'5')->applyFromArray($styleArray);

            if ($rowPE['footerkiri_1_teks'] == '' AND $rowPE['footerkiri_2_teks'] == '' AND $rowPE['footerkiri_3_teks'] == '' AND $rowPE['footerkiri_5_teks'] == '' AND $rowPE['footerkiri_6_teks'] == '' AND $rowPE['footertengah_1_teks'] == '' AND $rowPE['footertengah_2_teks'] == '' AND $rowPE['footertengah_3_teks'] == '' AND $rowPE['footertengah_5_teks'] == '' AND $rowPE['footertengah_6_teks'] == '' AND $rowPE['footerkanan_1_teks'] == '' AND $rowPE['footerkanan_2_teks'] == '' AND $rowPE['footerkanan_3_teks'] == '' AND $rowPE['footerkanan_5_teks'] == '' AND $rowPE['footerkanan_6_teks'] == '') {
            } else {
                $l = $i + 1;
                Utils::footerExcel($objPHPExcel,'kiri','A','B',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'tengah','D','E',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'kanan','G',$ha,$l,$rowPE);
            }

            // password
            Utils::passwordExcel($objPHPExcel);

            if ($b != 1) {
                Utils::header5baris($objPHPExcel,$ha,$rowPE);
            }

            if ($rowPE['logokiri'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokiri'];
                Utils::logoExcel('kiri',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'A1');
            }

            if ($rowPE['logokanan'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokanan'];
                Utils::logoExcel('kanan',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,$ha.'1');
            }

            Utils::insertLogUser('Ekspor logabsen');
            Utils::setFileNameExcel(trans('all.logabsen'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}