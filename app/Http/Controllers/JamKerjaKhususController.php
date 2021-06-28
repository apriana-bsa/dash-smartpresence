<?php
namespace App\Http\Controllers;

use App\JamKerjaKhusus;
use App\JamKerjaFull;

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
use App\Utils;

class JamKerjaKhususController extends Controller
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
        if(Utils::cekHakakses('jamkerja','l')){
            $tahun = Utils::tahunDropdown();
            Utils::insertLogUser('akses menu jam kerja khusus');
            return view('datainduk/absensi/jamkerjakhusus/index', ['tahun' => $tahun, 'menu' => 'jamkerjakhusus']);
        } else {
            return redirect('/');
        }
    }

    public function submit($tahun)
    {
        Session::set('jamkerjakhusus_tahun', $tahun);
        $response = array();
        $response['status'] = 'OK';
        return $response;
    }

    public function show(Request $request)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if (Session::has('jamkerjakhusus_tahun')) {
                $where = ' AND (DATE_FORMAT(tanggalawal, "%Y%") = ' . Session::get('jamkerjakhusus_tahun') . ' OR DATE_FORMAT(tanggalakhir, "%Y%") = ' . Session::get('jamkerjakhusus_tahun').')';
            }
            $table = '(
                        SELECT
                            jkk.id,
                            jkk.keterangan,
                            GROUP_CONCAT(jk.nama SEPARATOR "<br>") as jamkerja,
                            jkk.tanggalawal,
                            jkk.tanggalakhir,
                            jkk.toleransi,
                            jkk.perhitunganjamkerja,
                            jkk.hitunglemburstlh,
                            CONCAT(DATE_FORMAT(jkk.jammasuk, "%H:%i")," - ",DATE_FORMAT(jkk.jampulang, "%H:%i")) as waktukerja,
                            IFNULL(CONCAT(jkkp.jumlah," ","' . trans('all.pegawai') . '"),"' . trans('all.semuapegawai') . '") as ditujukan
                        FROM
                            jamkerjakhusus jkk
                            LEFT JOIN jamkerjakhususjamkerja jkjk ON jkjk.idjamkerjakhusus=jkk.id
                            LEFT JOIN (SELECT idjamkerjakhusus, COUNT(*) as jumlah FROM jamkerjakhususpegawai GROUP BY idjamkerjakhusus) jkkp ON jkkp.idjamkerjakhusus=jkk.id
                            LEFT JOIN jamkerja jk ON jkjk.idjamkerja=jk.id
                        GROUP BY
                            jkk.id
                    ) x';
            $columns = array('','keterangan','jamkerja','ditujukan','tanggalawal','waktukerja','toleransi','perhitunganjamkerja','hitunglemburstlh');
            $totalData = Utils::getDataCustomWhere($pdo,'jamkerjakhusus', 'count(id)','1=1 '.$where);
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
                    $action = Utils::tombolManipulasi('detail','jamkerjakhusus',$key['id']);;
                    if(Utils::cekHakakses('jamkerja','um')){
                        $action .= Utils::tombolManipulasi('ubah','jamkerjakhusus',$key['id']);
                    }
                    if(Utils::cekHakakses('jamkerja','hm')){
                        $action .= Utils::tombolManipulasi('hapus','jamkerjakhusus',$key['id']);
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        if($columns[$i] == 'toleransi' || $columns[$i] == 'hitunglemburstlh') {
                            $tempdata[$columns[$i]] = $key[$columns[$i]] . ' ' . trans('all.menit');
                        }elseif($columns[$i] == 'perhitunganjamkerja') {
                            $tempdata[$columns[$i]] = trans('all.'.$key[$columns[$i]]);
                        }elseif($columns[$i] == 'tanggalawal') {
                            $tempdata[$columns[$i]] = Utils::tanggalCantikDariSampai($key['tanggalawal'], $key['tanggalakhir']);
                        }else{
                            $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                        }
                    }
                    $data[] = $tempdata;
                }
            }
            return Utils::jsonDatatable($request->input('draw'),$totalData,$totalFiltered,$data);;
        }
        return '';
    }

    public function create()
    {
        if(Utils::cekHakakses('jamkerja','tm')){

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,nama FROM jamkerja ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $jamkerja = $stmt->fetchAll(PDO::FETCH_OBJ);
            Utils::insertLogUser('akses menu jam kerja khusus');
            return view('datainduk/absensi/jamkerjakhusus/create', ['jamkerja' => $jamkerja, 'menu' => 'jamkerjakhusus']);
        } else {
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        if(Utils::cekDateTime($request->tanggalawal) && Utils::cekDateTime($request->tanggalakhir) && isset($request->jammasuk) ? Utils::cekDateTime($request->jammasuk) : true && isset($request->jampulang) ? Utils::cekDateTime($request->jampulang) : true) {
            if (isset($request->jamkerja) && is_array($request->jamkerja)) {
                $jamkerja = $request->jamkerja;

                if (count($jamkerja) > 0) {
                    $set_idjamkerja = implode(',', $jamkerja);

                    $jumlahada = 0;
                    $sql = 'SELECT COUNT(*) as jumlahada FROM jamkerja WHERE id IN (' . $set_idjamkerja . ')';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $jumlahada = intval($row['jumlahada']);
                    }

                    if ($jumlahada != count($jamkerja)) {
                        return redirect('datainduk/absensi/jamkerjakhusus/create')->with('message', trans('all.jamkerjatidakditemukan'));
                    } else {
                        //******************* xyz
                        //cek apakah jamkerja tsb sudah pernah diinsert pada tanggal yang sama?
                        $sql = 'SELECT jkkjk.id FROM jamkerjakhusus jkk, jamkerjakhususjamkerja jkkjk WHERE jkk.id=jkkjk.idjamkerjakhusus AND jkkjk.idjamkerja IN (' . $set_idjamkerja . ') AND (jkk.tanggalawal BETWEEN STR_TO_DATE(:tanggalawal0, "%d/%m/%Y") AND STR_TO_DATE(:tanggalakhir0, "%d/%m/%Y")OR STR_TO_DATE(:tanggalawal1, "%d/%m/%Y") BETWEEN jkk.tanggalawal AND jkk.tanggalakhir) LIMIT 1';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':tanggalawal0', $request->tanggalawal);
                        $stmt->bindValue(':tanggalakhir0', $request->tanggalakhir);
                        $stmt->bindValue(':tanggalawal1', $request->tanggalawal);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            return redirect('datainduk/absensi/jamkerjakhusus/create')->with('message', trans('all.jamkerjabertabrakan'));
                        }
                    }
                }
            }

            try {
                $pdo->beginTransaction();

                $hitunglemburstlh = '';
                if ($request->perhitunganjamkerja == 'normal') {
                    if (isset($request->hitunglembursetelah)) {
                        $hitunglemburstlh = $request->hitunglembursetelah;
                    }
                }

                $sql = 'INSERT INTO jamkerjakhusus VALUES(
                                                NULL, 
                                                :keterangan, 
                                                STR_TO_DATE(:tanggalawal, "%d/%m/%Y"),
                                                STR_TO_DATE(:tanggalakhir, "%d/%m/%Y"),
                                                :toleransi,
                                                :perhitunganjamkerja,
                                                :hitunglemburstlh,
                                                :jammasuk,
                                                :jampulang,
                                                NOW())';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':keterangan', $request->keterangan);
                $stmt->bindValue(':tanggalawal', $request->tanggalawal);
                $stmt->bindValue(':tanggalakhir', $request->tanggalakhir);
                $stmt->bindValue(':toleransi', $request->toleransi);
                $stmt->bindValue(':perhitunganjamkerja', $request->perhitunganjamkerja);
                $stmt->bindValue(':hitunglemburstlh', $hitunglemburstlh);
                if (!isset($request->jammasuk)) {
                    $stmt->bindValue(':jammasuk', null, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(':jammasuk', $request->jammasuk);
                }
                if (!isset($request->jampulang)) {
                    $stmt->bindValue(':jampulang', null, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(':jampulang', $request->jampulang);
                }
                $stmt->execute();

                $idjamkerjakhusus = $pdo->lastInsertId();

                if (isset($request->jamkerja)) {
                    $totaljamkerja = count($request->jamkerja);
                    for ($i = 0; $i < $totaljamkerja; $i++) {
                        if ($request->jamkerja[$i] != '') {
                            $sql = 'INSERT INTO jamkerjakhususjamkerja VALUES(NULL,:idjamkerjakhusus,:idjamkerja,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjakhusus', $idjamkerjakhusus);
                            $stmt->bindValue(':idjamkerja', $request->jamkerja[$i]);
                            $stmt->execute();
                        }
                    }
                }

                $pdo->commit();

                Utils::insertLogUser('Tambah jam kerja khusus "' . $request->keterangan . '"');

                return redirect('datainduk/absensi/jamkerjakhusus')->with('message', trans('all.databerhasildisimpan'));
            } catch (\Exception $e) {
                $pdo->rollBack();
                return redirect('datainduk/absensi/jamkerjakhusus/create')->with('message', $e->getMessage());
            }
        }else{
            return redirect('datainduk/absensi/jamkerjakhusus/create')->with('message', trans('all.terjadigangguan'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('jamkerja','um')){
            $jamkerjakhusus = JamKerjaKhusus::find($id);

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT jamawal,jamakhir FROM jamkerjakhususistirahat WHERE idjamkerjakhusus = :idjamkerjakhusus';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjakhusus', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $jamistirahat = $stmt->fetchAll(PDO::FETCH_OBJ);
            } else {
                $jamistirahat = '';
            }

            $sql = 'SELECT
                        jk.id,
                        jk.nama,
                        IF(ISNULL(jkjk.id),0,1) as dipilih
                    FROM
                        jamkerja jk
                        LEFT JOIN jamkerjakhususjamkerja jkjk ON jkjk.idjamkerja=jk.id AND jkjk.idjamkerjakhusus = :idjamkerjakhusus';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjakhusus', $id);
            $stmt->execute();
            $jamkerja = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (!$jamkerjakhusus) {
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah jam kerja khusus');
            return view('datainduk/absensi/jamkerjakhusus/edit', ['jamkerjakhusus' => $jamkerjakhusus, 'jamistirahat' => $jamistirahat, 'jamkerja' => $jamkerja, 'menu' => 'jamkerjakhusus']);
        } else {
            return redirect('/');
        }
    }

    public function update(Request $request, $idjamkerjakhusus)
    {
        if(Utils::cekDateTime($request->tanggalawal) && Utils::cekDateTime($request->tanggalakhir) && isset($request->jammasuk) ? Utils::cekDateTime($request->jammasuk) : true && isset($request->jampulang) ? Utils::cekDateTime($request->jampulang) : true) {
            $pdo = DB::connection('perusahaan_db')->getPdo();

            $sql = 'SELECT
                    keterangan,
                    tanggalawal,
                    tanggalakhir
                FROM
                    jamkerjakhusus
                WHERE
                    id=:idjamkerjakhusus
                LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idjamkerjakhusus', $idjamkerjakhusus);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $rowJ = $stmt->fetch(PDO::FETCH_ASSOC);

                $keterangan = '';
                if (isset($request->keterangan)) {
                    $keterangan = $request->keterangan;
                }

                $tanggalawal = '';
                if (isset($request->tanggalawal)) {
                    $tanggalawal = $request->tanggalawal;
                }

                $tanggalakhir = '';
                if (isset($request->tanggalakhir)) {
                    $tanggalakhir = $request->tanggalakhir;
                }

                $toleransi = '';
                if (isset($request->toleransi)) {
                    $toleransi = $request->toleransi;
                }

                $hitunglemburstlh = '';
                if ($request->perhitunganjamkerja == 'normal') {
                    if (isset($request->hitunglembursetelah)) {
                        $hitunglemburstlh = $request->hitunglembursetelah;
                    }
                }

                $jammasuk = '';
                if (isset($request->jammasuk)) {
                    $jammasuk = $request->jammasuk;
                }

                $jampulang = '';
                if (isset($request->jampulang)) {
                    $jampulang = $request->jampulang;
                }

                $jumlahada = 0;
                $set_idjamkerja = '';
                $jamkerja = array();
                if (isset($request->jamkerja) && is_array($request->jamkerja)) {
                    $jamkerja = $request->jamkerja;

                    if (count($jamkerja) > 0) {
                        $set_idjamkerja = implode(',', $jamkerja);

                        $sql = 'SELECT COUNT(*) as jumlahada FROM jamkerja WHERE id IN (' . $set_idjamkerja . ')';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $jumlahada = intval($row['jumlahada']);
                        }

                        if ($jumlahada != count($jamkerja)) {
                            return redirect('datainduk/absensi/jamkerjakhusus/' . $idjamkerjakhusus . '/edit')->with('message', trans('all.jamkerjatidaksesuai'));
                        } else {
                            //****************** xyz
                            //cek apakah jamkerja tsb sudah pernah diinsert pada tanggal yang sama?
                            $sql = 'SELECT jkkjk.id
                                FROM
                                    jamkerjakhusus jkk,
                                    jamkerjakhususjamkerja jkkjk
                                WHERE
                                    jkk.id=jkkjk.idjamkerjakhusus AND
                                    jkk.id<>:idjamkerjakhusus AND
                                    jkkjk.idjamkerja IN (' . $set_idjamkerja . ') AND
                                    (
                                        jkk.tanggalawal BETWEEN STR_TO_DATE(:tanggalawal0,"%d/%m/%Y") AND
                                        STR_TO_DATE(:tanggalakhir0,"%d/%m/%Y") OR
                                        STR_TO_DATE(:tanggalawal1,"%d/%m/%Y") BETWEEN jkk.tanggalawal AND
                                        jkk.tanggalakhir
                                    ) LIMIT 1';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':idjamkerjakhusus', $idjamkerjakhusus);
                            $stmt->bindParam(':tanggalawal0', $tanggalawal);
                            $stmt->bindParam(':tanggalakhir0', $tanggalakhir);
                            $stmt->bindParam(':tanggalawal1', $tanggalawal);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                return redirect('datainduk/absensi/jamkerjakhusus/' . $idjamkerjakhusus . '/edit')->with('message', trans('all.datasudahada'));
                            }
                        }
                    }
                }
                //******************* xyz
                //cek apakah pegawai tsb sudah pernah diinsert pada tanggal yang sama?
                $sql = 'SELECT jkkp.id FROM jamkerjakhusus jkk, jamkerjakhususpegawai jkkp WHERE jkk.id=jkkp.idjamkerjakhusus AND jkk.id<>:idjamkerjakhusus0 AND jkkp.idpegawai IN (SELECT idpegawai FROM jamkerjakhususpegawai WHERE idjamkerjakhusus=:idjamkerjakhusus1) AND (jkk.tanggalawal BETWEEN STR_TO_DATE(:tanggalawal0,"%d/%m/%Y") AND STR_TO_DATE(:tanggalakhir0,"%d/%m/%Y") OR STR_TO_DATE(:tanggalawal1,"%d/%m/%Y") BETWEEN jkk.tanggalawal AND jkk.tanggalakhir) LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idjamkerjakhusus0', $idjamkerjakhusus);
                $stmt->bindParam(':idjamkerjakhusus1', $idjamkerjakhusus);
                $stmt->bindParam(':tanggalawal0', $tanggalawal);
                $stmt->bindParam(':tanggalakhir0', $tanggalakhir);
                $stmt->bindParam(':tanggalawal1', $tanggalawal);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    return redirect('datainduk/absensi/jamkerjakhusus/' . $idjamkerjakhusus . '/edit')->with('message', trans('all.jamkerjabertabrakan'));
                }

                try {
                    $pdo->beginTransaction();
                    $sql = 'UPDATE jamkerjakhusus SET keterangan=:keterangan, tanggalawal = STR_TO_DATE(:tanggalawal,"%d/%m/%Y"), tanggalakhir = STR_TO_DATE(:tanggalakhir,"%d/%m/%Y"), toleransi=:toleransi, perhitunganjamkerja=:perhitunganjamkerja, hitunglemburstlh=:hitunglemburstlh, jammasuk=:jammasuk, jampulang=:jampulang WHERE id=:idjamkerjakhusus';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':keterangan', $keterangan);
                    $stmt->bindParam(':tanggalawal', $tanggalawal);
                    $stmt->bindParam(':tanggalakhir', $tanggalakhir);
                    $stmt->bindParam(':toleransi', $toleransi);
                    $stmt->bindValue(':perhitunganjamkerja', $request->perhitunganjamkerja);
                    $stmt->bindParam(':hitunglemburstlh', $hitunglemburstlh);
                    $stmt->bindParam(':jammasuk', $jammasuk);
                    $stmt->bindParam(':jampulang', $jampulang);
                    $stmt->bindParam(':idjamkerjakhusus', $idjamkerjakhusus);
                    $stmt->execute();

                    $sql = 'DELETE FROM jamkerjakhususjamkerja WHERE idjamkerjakhusus=:idjamkerjakhusus';
                    if ($set_idjamkerja != '') {
                        $sql = $sql . ' AND idjamkerja NOT IN (' . $set_idjamkerja . ')';
                    }
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':idjamkerjakhusus', $idjamkerjakhusus);
                    $stmt->execute();

                    foreach ($jamkerja as $item) {
                        $sql = 'INSERT IGNORE INTO jamkerjakhususjamkerja VALUES(NULL, :idjamkerjakhusus, :idjamkerja, NOW())';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':idjamkerjakhusus', $idjamkerjakhusus);
                        $stmt->bindParam(':idjamkerja', $item);
                        $stmt->execute();
                    }

                    $pdo->commit();

                    Utils::insertLogUser('Ubah jam kerja khusus "' . $rowJ['keterangan'] . '" => "' . $keterangan . '"');

                    return redirect('datainduk/absensi/jamkerjakhusus')->with('message', trans('all.databerhasildiubah'));
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return redirect('datainduk/absensi/jamkerjakhusus/' . $idjamkerjakhusus . '/edit')->with('message', $e->getMessage());
                }
            } else {
                return redirect('datainduk/absensi/jamkerjakhusus/' . $idjamkerjakhusus . '/edit')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('datainduk/absensi/jamkerjakhusus/' . $idjamkerjakhusus . '/edit')->with('message', trans('all.terjadigangguan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('jamkerja','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT
                        keterangan,
                        tanggalawal,
                        tanggalakhir
                    FROM
                        jamkerjakhusus
                    WHERE
                        id=:idjamkerjakhusus
                    LIMIT 1
                    ';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjakhusus', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {

                try {
                    $pdo->beginTransaction();

                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    JamKerjaKhusus::find($id)->delete();

                    Utils::insertLogUser('Hapus jam kerja khusus "' . $row['keterangan'] . '"');

                    $pdo->commit();

                    return redirect('datainduk/absensi/jamkerjakhusus')->with('message', trans('all.databerhasildihapus'));
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return redirect('datainduk/absensi/jamkerjakhusus/'.$id.'/edit')->with('message', trans('all.terjadigangguan'));
                }
            } else {
                return redirect('datainduk/absensi/jamkerjakhusus')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function detail($idjamkerjakhusus)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            if ($idjamkerjakhusus != '') {
                $pdo = DB::connection('perusahaan_db')->getPdo();
                $sql = 'SELECT keterangan as jamkerjakhusus FROM jamkerjakhusus WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $idjamkerjakhusus);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $jamkerjakhusus = $row['jamkerjakhusus'];
                    Utils::insertLogUser('akses menu detail jam kerja khusus');
                    return view('datainduk/absensi/jamkerjakhusus/detail', ['jamkerjakhusus' => $jamkerjakhusus, 'idjamkerjakhusus' => $idjamkerjakhusus, 'menu' => 'jamkerjakhusus']);
                } else {
                    return redirect('/');
                }
            } else {
                return redirect('/');
            }
        }
        return '';
    }

    public function detailData(Request $request, $idjamkerjakhusus)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = ' AND idjamkerjakhusus = :idjamkerjakhusus';
            if(Utils::cekHakakses('jamkerja','uhm')) {
                $columns = array('', 'nama', 'pin', 'nomorhp', 'status');
            }else{
                $columns = array('nama', 'pin', 'nomorhp', 'status');
            }
            $table = '(SELECT jkfp.id, jkfp.idjamkerjakhusus, p.id as idpegawai, p.nama, p.pin, p.nomorhp, p.status FROM jamkerjakhususpegawai jkfp, pegawai p WHERE jkfp.idpegawai=p.id AND p.del = "t") x';
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE 1=1 '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjakhusus', $idjamkerjakhusus);
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
                $stmt->bindValue(':idjamkerjakhusus', $idjamkerjakhusus);
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
            $stmt->bindValue(':idjamkerjakhusus', $idjamkerjakhusus);
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
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center><a title="' . trans('all.hapus') . '" href="#" onclick="return hapusdata(' . $key['id'] . ')"><i class="fa fa-trash" style="color:#ed5565"></i></a></center>';
                        }elseif($columns[$i] == 'nama') {
                            $tempdata[$columns[$i]] = '<span class="detailpegawai" onclick="detailpegawai(' . $key['idpegawai'] . ')" style="cursor:pointer;">' . $key['nama'] . '</span>';
                        }elseif($columns[$i] == 'status') {
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]] == 'a' ? 'aktif' : 'tidakaktif');
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

    public function detailTambahData(Request $request, $idjamkerjakhusus, $idatribut)
    {
        if(Utils::cekHakakses('jamkerja','lm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if ($idatribut != 'o') {
                $where = ' AND id IN(SELECT idpegawai FROM pegawaiatribut WHERE del = "t" AND status = "a" AND idatributnilai IN(' . $idatribut . '))';
            }
            $columns = array('','nama','pin','nomorhp','status');
            $totalData = Utils::getDataCustomWhere($pdo,'pegawai', 'count(id)',' 1=1 '.$where);
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $columns[$request->input('order.0.column')];
            $orderAction = $request->input('order.0.dir');
            $orderBy = $orderColumn.' '.$orderAction;

            if(!empty($request->input('search.value'))){
                $search = $request->input('search.value');
                $where .= Utils::searchDatatableQuery($columns);
                $sql = 'SELECT COUNT(id) as total FROM pegawai WHERE 1=1 '.$where;
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

            $sql = 'SELECT id,nama,pin,nomorhp,status FROM pegawai WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    $tempdata['action'] = '<center><input type="checkbox" id="pegawai_' . $key['id'] . '" value="' . $key['id'] . '" name="pegawai[]" class="cekpegawai"></center>';
                    for($i=1;$i<count($columns);$i++){
                        if($columns[$i] == 'status') {
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]] == 'a' ? 'aktif' : 'tidakaktif');
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

    public function submitTambahData(Request $request, $idjamkerjakhusus)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idpegawai = explode('|', $request->idpegawai);
        $implode_idpegawai = implode(',', $idpegawai);
        $hasil = '';

        //pastikan idjamkerjakhusus ada
        $sql = 'SELECT id, tanggalawal, tanggalakhir  FROM jamkerjakhusus WHERE id=:idjamkerjakhusus LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idjamkerjakhusus', $idjamkerjakhusus);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $tanggalawal = $row['tanggalawal'];
            $tanggalakhir = $row['tanggalakhir'];


            //pastikan idpegawai ada
            $sql = 'SELECT COUNT(*) as jumlah FROM pegawai WHERE id IN (' . $implode_idpegawai . ') AND status="a" AND del = "t" LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row['jumlah'] == count($idpegawai)) {
                //cek apakah pegawai tsb sudah pernah diinsert pada tanggal yang sama?
                $sql = 'SELECT jkkp.id FROM jamkerjakhusus jkk, jamkerjakhususpegawai jkkp WHERE jkk.id=jkkp.idjamkerjakhusus AND jkk.id<>:idjamkerjakhusus AND jkkp.idpegawai IN (' . $implode_idpegawai . ') AND (jkk.tanggalawal BETWEEN :tanggalawal0 AND :tanggalakhir0 OR :tanggalawal1 BETWEEN jkk.tanggalawal AND jkk.tanggalakhir) LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idjamkerjakhusus', $idjamkerjakhusus);
                $stmt->bindParam(':tanggalawal0', $tanggalawal);
                $stmt->bindParam(':tanggalakhir0', $tanggalakhir);
                $stmt->bindParam(':tanggalawal1', $tanggalawal);
                $stmt->execute();
                if ($stmt->rowCount() == 0) {
                    try {
                        $pdo->beginTransaction();
                        for ($i = 0; $i < count($idpegawai); $i++) {
                            $sql = 'REPLACE INTO jamkerjakhususpegawai VALUES(NULL, :idjamkerjakhusus, :idpegawai, NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':idjamkerjakhusus', $idjamkerjakhusus);
                            $stmt->bindParam(':idpegawai', $idpegawai[$i]);
                            $stmt->execute();
                        }
                        Utils::insertLogUser('Tambah jam kerja khusus pegawai');

                        $pdo->commit();
                    } catch (\Exception $e) {
                        $pdo->rollBack();
                        $hasil = trans('all.terjadigangguan');
                    }
                } else {
                    $hasil = trans('all.datasudahada');
                }
            } else {
                $hasil = trans('all.pegawaitidakditemukan').' '.$implode_idpegawai.' '.$row['jumlah'].' '.count($idpegawai);
            }
        } else {
            $hasil = trans('all.datatidakditemukan');
        }
        return $hasil;
    }

    public function deleteData($idjamkerjakhusus, $id)
    {
        if(Utils::cekHakakses('jamkerja','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id FROM jamkerjakhususpegawai WHERE id = :id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                Utils::deleteData($pdo,'jamkerjakhususpegawai',$id);
                Utils::insertLogUser('hapus jam kerja khusus pegawai');
                $msg = trans('all.databerhasildihapus');
            } else {
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('datainduk/absensi/jamkerjakhusus/' . $idjamkerjakhusus . '/detail')->with('message', $msg);
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('jamkerja','l')){
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
                $b = 0; //b = baris
            } else {
                $b = 6;
            }

            $b = $b + 1;

            Utils::setPropertiesExcel($objPHPExcel,trans('all.jamkerjakhusus'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $b, trans('all.keterangan'))
                        ->setCellValue('B' . $b, trans('all.jamkerja'))
                        ->setCellValue('C' . $b, trans('all.tanggal'))
                        ->setCellValue('D' . $b, trans('all.waktukerja'))
                        ->setCellValue('E' . $b, trans('all.toleransi'))
                        ->setCellValue('F' . $b, trans('all.hitunglembursetelah'));

            $where = '';
            if (Session::has('jamkerjakhusus_tahun')) {
                $where = ' WHERE DATE_FORMAT(jkk.tanggalawal, "%Y%") = ' . Session::get('jamkerjakhusus_tahun') . ' OR DATE_FORMAT(jkk.tanggalakhir, "%Y%") = ' . Session::get('jamkerjakhusus_tahun');
            }

            $sql = 'SELECT
                        jkk.id,
                        jkk.keterangan,
                        GROUP_CONCAT(jk.nama SEPARATOR ", ") as jamkerja,
                        CONCAT(DATE_FORMAT(jkk.tanggalawal, "%d/%m/%Y"), " - ",DATE_FORMAT(jkk.tanggalakhir, "%d/%m/%Y")) as tanggal,
                        jkk.toleransi,
                        jkk.hitunglemburstlh,
                        CONCAT(DATE_FORMAT(jkk.jammasuk, "%H:%i")," - ",DATE_FORMAT(jkk.jampulang, "%H:%i")) as waktukerja,
                        IFNULL(CONCAT(jkkp.jumlah," ","' . trans('all.pegawai') . '"),"' . trans('all.semuapegawai') . '") as ditujukan
                    FROM
                        jamkerjakhusus jkk
                        LEFT JOIN jamkerjakhususjamkerja jkjk ON jkjk.idjamkerjakhusus=jkk.id
                        LEFT JOIN (SELECT idjamkerjakhusus, COUNT(*) as jumlah FROM jamkerjakhususpegawai GROUP BY idjamkerjakhusus) jkkp ON jkkp.idjamkerjakhusus=jkk.id
                        LEFT JOIN jamkerja jk ON jkjk.idjamkerja=jk.id' . $where . '
                    GROUP BY
                        jkk.id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = $b + 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['keterangan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['jamkerja']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['tanggal']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['waktukerja']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['toleransi']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['hitunglemburstlh']);

                for ($j = 1; $j <= 6; $j++) {
                    $huruf = Utils::angkaToHuruf($j);
                    $objPHPExcel->getActiveSheet()->getStyle($huruf . $i)->applyFromArray($styleArray);
                }

                $i++;
            }

            $arrWidth = array('', 40, 40, 25, 15, 12, 22);
            for ($j = 1; $j <= 6; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            // style garis
            $end_i = $i - 1;
            $objPHPExcel->getActiveSheet()->getStyle('A1:E' . $end_i)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E' . $b)->applyFromArray($styleArray);

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
                Utils::footerExcel($objPHPExcel,'kanan','E','F',$l,$rowPE);
                $l = $l + 7;
            }

            // password
            Utils::passwordExcel($objPHPExcel);

            if ($b != 1) {
                Utils::header5baris($objPHPExcel,'F',$rowPE);
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
                Utils::logoExcel('kanan',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'F1');
            }

            Utils::insertLogUser('Ekspor jam kerja khusus');
            Utils::setFileNameExcel(trans('all.jamkerjakhusus'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }

    public function excelDetail($idjamkerjakhusus)
    {
        if(Utils::cekHakakses('jamkerja','l')){
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

            $b = 1;

            Utils::setPropertiesExcel($objPHPExcel,trans('all.jamkerjakhusus').' '.trans('all.detail'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.pegawai'))
                        ->setCellValue('B1', trans('all.pin'))
                        ->setCellValue('C1', trans('all.nomorhp'))
                        ->setCellValue('D1', trans('all.status'));

            $sql = 'SELECT
                        jkfp.id,
                        p.id as idpegawai,
                        p.nama,
                        p.nomorhp,
                        p.pin,
                        p.status
                    FROM
                        jamkerjakhususpegawai jkfp,
                        pegawai p
                    WHERE
                        jkfp.idpegawai=p.id AND
                        p.status = "a" AND
                        p.del = "t" AND
                        jkfp.idjamkerjakhusus = ' . $idjamkerjakhusus;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            //$i = $b+1;
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $status = $row['status'] == 'a' ? trans('all.aktif') : trans('all.tidakaktif');
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['pin']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['nomorhp']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $status);

                $i++;
            }

            $arrWidth = array('', 40, 15, 20, 15);
            for ($j = 1; $j <= 4; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor jam kerja khusus detail');
            Utils::setFileNameExcel(trans('all.jamkerjakhusus') . '_' . trans('all.detail'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}