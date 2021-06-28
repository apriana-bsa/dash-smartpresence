<?php
namespace App\Http\Controllers;

use App\JamKerjaFull;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class JamKerjaFullController extends Controller
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

    public function getindex(Request $request, $id)
    {
        $onboarding = $request->query('onboarding');
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama FROM jamkerja WHERE id = :idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $jamkerja = $row->nama;
            Utils::insertLogUser('akses menu jam kerja full');
            return view('datainduk/absensi/jamkerja/full/index', ['idjamkerja' => $id, 'jamkerja' => $jamkerja, 'menu' => 'jamkerja', 'onboarding' => $onboarding]);
        } else {
            return redirect('/');
        }
    }

    public function show(Request $request, $id)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('jamkerja','uhm')){
                $columns = array('','berlakumulai','keterangan');
            }else{
                $columns = array('berlakumulai','keterangan');
            }
            $table = '(
                        SELECT
                            id,
                            berlakumulai,
                            CONCAT(
                              if(_1_masukkerja = "y", CONCAT("'.trans('all.minggu').' : ",_1_jammasuk," - ",_1_jampulang,"<br>"), ""),
                              if(_2_masukkerja = "y", CONCAT("'.trans('all.senin').' : ",_2_jammasuk," - ",_2_jampulang,"<br>"), ""),
                              if(_3_masukkerja = "y", CONCAT("'.trans('all.selasa').' : ",_3_jammasuk," - ",_3_jampulang,"<br>"), ""),
                              if(_4_masukkerja = "y", CONCAT("'.trans('all.rabu').' : ",_4_jammasuk," - ",_4_jampulang,"<br>"), ""),
                              if(_5_masukkerja = "y", CONCAT("'.trans('all.kamis').' : ",_5_jammasuk," - ",_5_jampulang,"<br>"), ""),
                              if(_6_masukkerja = "y", CONCAT("'.trans('all.jumat').' : ",_6_jammasuk," - ",_6_jampulang,"<br>"), ""),
                              if(_7_masukkerja = "y", CONCAT("'.trans('all.sabtu').' : ",_7_jammasuk," - ",_7_jampulang,"<br>"), "")
                            ) as keterangan
                        FROM
                            jamkerjafull
                        WHERE
                            idjamkerja =' . $id . '
                        ) x';
            $totalData = Utils::getDataWhere($pdo,'jamkerjafull', 'count(id)','idjamkerja',$id);
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
                    if(Utils::cekHakakses('jamkerja','um')){
                        $action .= Utils::tombolManipulasi('ubah','full',$key['id']);
                    }
                    if(Utils::cekHakakses('jamkerja','hm')){
                        $action .= Utils::tombolManipulasi('hapus','full',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'berlakumulai') {
                            $tempdata[$columns[$i]] = Utils::tanggalCantik($key[$columns[$i]]);
                        }elseif($columns[$i] == 'keterangan') {
                            $tempdata[$columns[$i]] = $key[$columns[$i]];
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

    public function create(Request $request, $id)
    {
        if(Utils::cekHakakses('jamkerja','tm')){
            $onboarding = $request->query('onboarding');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama FROM jamkerja WHERE id = :idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $jamkerja = $row->nama;
            Utils::insertLogUser('akses menu jam kerja full');
            return view('datainduk/absensi/jamkerja/full/create', ['idjamkerja' => $id, 'jamkerja' => $jamkerja, 'menu' => 'jamkerja', 'onboarding' => $onboarding]);
        } else {
            return redirect('/');
        }
    }

    public function store(Request $request, $id)
    {
        $onboarding = $request->query('onboarding');
        $urlDetailJamKerja = 'datainduk/absensi/jamkerja/' . $id . '/full';
        $urlDetailJamKerjaCreate = $urlDetailJamKerja . '/create';
        $urlDetailJamKerja = $onboarding ? $urlDetailJamKerja . '?onboarding=' . $onboarding : $urlDetailJamKerja;
        $urlDetailJamKerjaCreate = $onboarding ? $urlDetailJamKerjaCreate . '?onboarding=' . $onboarding : $urlDetailJamKerjaCreate;

        if(!Utils::cekDateTime($request->full_berlakumulai)){
            return redirect($urlDetailJamKerja)->with('message', trans('all.terjadigangguan'));
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idjamkerja ada
        $sql = 'SELECT id FROM jamkerja WHERE jenis="full" AND id=:idjamkerja LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idjamkerja', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            //cek apakah berlakumulai kembar?
            $sql = 'SELECT id FROM jamkerjafull WHERE idjamkerja=:idjamkerja AND berlakumulai=STR_TO_DATE(:berlakumulai,"d/%m/%Y") LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $id);
            $stmt->bindValue(':berlakumulai', $request->full_berlakumulai);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $jamkerjafull = new JamKerjaFull;
                $jamkerjafull->idjamkerja = $id;
                $jamkerjafull->berlakumulai = date('Y-m-d', strtotime(str_replace('/', '-', $request->full_berlakumulai)));
                $jamkerjafull->_1_masukkerja = ($request->full_masukkerjaminggu == '' ? 't' : 'y');
                $jamkerjafull->_1_jammasuk = $request->full_jammasukminggu;
                $jamkerjafull->_1_jampulang = $request->full_jampulangminggu;
                $jamkerjafull->_2_masukkerja = ($request->full_masukkerjasenin == '' ? 't' : 'y');
                $jamkerjafull->_2_jammasuk = $request->full_jammasuksenin;
                $jamkerjafull->_2_jampulang = $request->full_jampulangsenin;
                $jamkerjafull->_3_masukkerja = ($request->full_masukkerjaselasa == '' ? 't' : 'y');
                $jamkerjafull->_3_jammasuk = $request->full_jammasukselasa;
                $jamkerjafull->_3_jampulang = $request->full_jampulangselasa;
                $jamkerjafull->_4_masukkerja = ($request->full_masukkerjarabu == '' ? 't' : 'y');
                $jamkerjafull->_4_jammasuk = $request->full_jammasukrabu;
                $jamkerjafull->_4_jampulang = $request->full_jampulangrabu;
                $jamkerjafull->_5_masukkerja = ($request->full_masukkerjakamis == '' ? 't' : 'y');
                $jamkerjafull->_5_jammasuk = $request->full_jammasukkamis;
                $jamkerjafull->_5_jampulang = $request->full_jampulangkamis;
                $jamkerjafull->_6_masukkerja = ($request->full_masukkerjajumat == '' ? 't' : 'y');
                $jamkerjafull->_6_jammasuk = $request->full_jammasukjumat;
                $jamkerjafull->_6_jampulang = $request->full_jampulangjumat;
                $jamkerjafull->_7_masukkerja = ($request->full_masukkerjasabtu == '' ? 't' : 'y');
                $jamkerjafull->_7_jammasuk = $request->full_jammasuksabtu;
                $jamkerjafull->_7_jampulang = $request->full_jampulangsabtu;
                $jamkerjafull->save();

                $idjamkerjafull = $jamkerjafull->id;

                //insert into jamkerjafullistirahat
                if (isset($request->full_istirahatmulaisenin)) {
                    $totalsenin = count($request->full_istirahatmulaisenin);
                    for ($i = 0; $i < $totalsenin; $i++) {
                        if ($request->full_istirahatmulaisenin[$i] != '' and $request->full_istirahatselesaisenin[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,2,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaisenin[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaisenin[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulaiselasa)) {
                    $totalselasa = count($request->full_istirahatmulaiselasa);
                    for ($i = 0; $i < $totalselasa; $i++) {
                        if ($request->full_istirahatmulaiselasa[$i] != '' and $request->full_istirahatselesaiselasa[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,3,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaiselasa[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaiselasa[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulairabu)) {
                    $totalrabu = count($request->full_istirahatmulairabu);
                    for ($i = 0; $i < $totalrabu; $i++) {
                        if ($request->full_istirahatmulairabu[$i] != '' and $request->full_istirahatselesairabu[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,4,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulairabu[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesairabu[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulaikamis)) {
                    $totalkamis = count($request->full_istirahatmulaikamis);
                    for ($i = 0; $i < $totalkamis; $i++) {
                        if ($request->full_istirahatmulaikamis[$i] != '' and $request->full_istirahatselesaikamis[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,5,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaikamis[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaikamis[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulaijumat)) {
                    $totaljumat = count($request->full_istirahatmulaijumat);
                    for ($i = 0; $i < $totaljumat; $i++) {
                        if ($request->full_istirahatmulaijumat[$i] != '' and $request->full_istirahatselesaijumat[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,6,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaijumat[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaijumat[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulaisabtu)) {
                    $totalsabtu = count($request->full_istirahatmulaisabtu);
                    for ($i = 0; $i < $totalsabtu; $i++) {
                        if ($request->full_istirahatmulaisabtu[$i] != '' and $request->full_istirahatselesaisabtu[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,7,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaisabtu[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaisabtu[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulaiminggu)) {
                    $totalminggu = count($request->full_istirahatmulaiminggu);
                    for ($i = 0; $i < $totalminggu; $i++) {
                        if ($request->full_istirahatmulaiminggu[$i] != '' and $request->full_istirahatselesaiminggu[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,1,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaiminggu[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaiminggu[$i]);
                            $stmt->execute();
                        }
                    }
                }

                Utils::insertLogUser('Tambah jam kerja full "' . $request->berlakumulai . '"');

                $with = [
                  'message'=>trans('all.databerhasildisimpan'),
                  'success_add_data'=>'1',
                ];
                //store or update user.onboardingstep (untuk tooltip onboarding)
                if(Session::get('onboardingstep')==2) {
                    $pdo = DB::getPdo();
                    $sql = 'UPDATE user SET onboardingstep = :step WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':step', 3);
                    $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
                    $stmt->execute();
                }

                return redirect($urlDetailJamKerja)->with($with);
            } else {
                return redirect($urlDetailJamKerjaCreate)->with('message', trans('all.datasudahada'));
            }
        } else {
            return redirect($urlDetailJamKerjaCreate)->with('message', trans('all.jamkerjatidakditemukan'));
        }
    }

    public function edit($idjamkerja, $id)
    {
        if(Utils::cekHakakses('jamkerja','um')){
            $jamkerjafull = JamKerjaFull::find($id);

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama FROM jamkerja WHERE id = :idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $idjamkerja);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $jamkerja = $row->nama;

            $sql = 'SELECT jamawal,jamakhir FROM jamkerjafullistirahat WHERE idjamkerjafull = :idjamkerjafull AND hari = 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjafull', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $jamistirahatminggu = $stmt->fetchAll(PDO::FETCH_OBJ);
            } else {
                $jamistirahatminggu = '';
            }

            $sql = 'SELECT jamawal,jamakhir FROM jamkerjafullistirahat WHERE idjamkerjafull = :idjamkerjafull AND hari = 2';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjafull', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $jamistirahatsenin = $stmt->fetchAll(PDO::FETCH_OBJ);
            } else {
                $jamistirahatsenin = '';
            }

            $sql = 'SELECT jamawal,jamakhir FROM jamkerjafullistirahat WHERE idjamkerjafull = :idjamkerjafull AND hari = 3';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjafull', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $jamistirahatselasa = $stmt->fetchAll(PDO::FETCH_OBJ);
            } else {
                $jamistirahatselasa = '';
            }

            $sql = 'SELECT jamawal,jamakhir FROM jamkerjafullistirahat WHERE idjamkerjafull = :idjamkerjafull AND hari = 4';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjafull', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $jamistirahatrabu = $stmt->fetchAll(PDO::FETCH_OBJ);
            } else {
                $jamistirahatrabu = '';
            }

            $sql = 'SELECT jamawal,jamakhir FROM jamkerjafullistirahat WHERE idjamkerjafull = :idjamkerjafull AND hari = 5';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjafull', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $jamistirahatkamis = $stmt->fetchAll(PDO::FETCH_OBJ);
            } else {
                $jamistirahatkamis = '';
            }

            $sql = 'SELECT jamawal,jamakhir FROM jamkerjafullistirahat WHERE idjamkerjafull = :idjamkerjafull AND hari = 6';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjafull', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $jamistirahatjumat = $stmt->fetchAll(PDO::FETCH_OBJ);
            } else {
                $jamistirahatjumat = '';
            }

            $sql = 'SELECT jamawal,jamakhir FROM jamkerjafullistirahat WHERE idjamkerjafull = :idjamkerjafull AND hari = 7';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjafull', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $jamistirahatsabtu = $stmt->fetchAll(PDO::FETCH_OBJ);
            } else {
                $jamistirahatsabtu = '';
            }

            if (!$jamkerjafull) {
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah jam kerja full');
            return view('datainduk/absensi/jamkerja/full/edit', ['jamkerjafull' => $jamkerjafull, 'jamistirahatminggu' => $jamistirahatminggu, 'jamistirahatsenin' => $jamistirahatsenin, 'jamistirahatselasa' => $jamistirahatselasa, 'jamistirahatrabu' => $jamistirahatrabu, 'jamistirahatkamis' => $jamistirahatkamis, 'jamistirahatjumat' => $jamistirahatjumat, 'jamistirahatsabtu' => $jamistirahatsabtu, 'jamkerja' => $jamkerja, 'menu' => 'jamkerja']);
        } else {
            return redirect('/');
        }
    }

    public function update(Request $request, $idjamkerja, $id)
    {
        if(!Utils::cekDateTime($request->full_berlakumulai)){
            return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/full/'.$id.'/edit')->with('message', trans('all.terjadigangguan'));
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idjamkerjafull ada
        $sql = 'SELECT id,DATE_FORMAT(berlakumulai, "%d/%m/%Y") as berlakumulai FROM jamkerjafull WHERE id=:idjamkerjafull LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idjamkerjafull', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //pastikan idjamkerja ada
            $sql = 'SELECT id FROM jamkerja WHERE id=:idjamkerja LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $idjamkerja);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $jamkerjafull = JamKerjaFull::find($id);
                $jamkerjafull->idjamkerja = $idjamkerja;
                $jamkerjafull->berlakumulai = date('Y-m-d', strtotime(str_replace('/', '-', $request->full_berlakumulai)));
                $jamkerjafull->_1_masukkerja = ($request->full_masukkerjaminggu == '' ? 't' : 'y');
                $jamkerjafull->_1_jammasuk = $request->full_jammasukminggu;
                $jamkerjafull->_1_jampulang = $request->full_jampulangminggu;
                $jamkerjafull->_2_masukkerja = ($request->full_masukkerjasenin == '' ? 't' : 'y');
                $jamkerjafull->_2_jammasuk = $request->full_jammasuksenin;
                $jamkerjafull->_2_jampulang = $request->full_jampulangsenin;
                $jamkerjafull->_3_masukkerja = ($request->full_masukkerjaselasa == '' ? 't' : 'y');
                $jamkerjafull->_3_jammasuk = $request->full_jammasukselasa;
                $jamkerjafull->_3_jampulang = $request->full_jampulangselasa;
                $jamkerjafull->_4_masukkerja = ($request->full_masukkerjarabu == '' ? 't' : 'y');
                $jamkerjafull->_4_jammasuk = $request->full_jammasukrabu;
                $jamkerjafull->_4_jampulang = $request->full_jampulangrabu;
                $jamkerjafull->_5_masukkerja = ($request->full_masukkerjakamis == '' ? 't' : 'y');
                $jamkerjafull->_5_jammasuk = $request->full_jammasukkamis;
                $jamkerjafull->_5_jampulang = $request->full_jampulangkamis;
                $jamkerjafull->_6_masukkerja = ($request->full_masukkerjajumat == '' ? 't' : 'y');
                $jamkerjafull->_6_jammasuk = $request->full_jammasukjumat;
                $jamkerjafull->_6_jampulang = $request->full_jampulangjumat;
                $jamkerjafull->_7_masukkerja = ($request->full_masukkerjasabtu == '' ? 't' : 'y');
                $jamkerjafull->_7_jammasuk = $request->full_jammasuksabtu;
                $jamkerjafull->_7_jampulang = $request->full_jampulangsabtu;
                $jamkerjafull->save();

                $idjamkerjafull = $id;
                Utils::deleteData($pdo,'jamkerjafullistirahat',$idjamkerjafull,'idjamkerjafull');

                //insert into jamkerjafullistirahat
                if (isset($request->full_istirahatmulaisenin)) {
                    $totalsenin = count($request->full_istirahatmulaisenin);
                    for ($i = 0; $i < $totalsenin; $i++) {
                        if ($request->full_istirahatmulaisenin[$i] != '' and $request->full_istirahatselesaisenin[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,2,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaisenin[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaisenin[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulaiselasa)) {
                    $totalselasa = count($request->full_istirahatmulaiselasa);
                    for ($i = 0; $i < $totalselasa; $i++) {
                        if ($request->full_istirahatmulaiselasa[$i] != '' and $request->full_istirahatselesaiselasa[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,3,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaiselasa[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaiselasa[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulairabu)) {
                    $totalrabu = count($request->full_istirahatmulairabu);
                    for ($i = 0; $i < $totalrabu; $i++) {
                        if ($request->full_istirahatmulairabu[$i] != '' and $request->full_istirahatselesairabu[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,4,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulairabu[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesairabu[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulaikamis)) {
                    $totalkamis = count($request->full_istirahatmulaikamis);
                    for ($i = 0; $i < $totalkamis; $i++) {
                        if ($request->full_istirahatmulaikamis[$i] != '' and $request->full_istirahatselesaikamis[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,5,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaikamis[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaikamis[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulaijumat)) {
                    $totaljumat = count($request->full_istirahatmulaijumat);
                    for ($i = 0; $i < $totaljumat; $i++) {
                        if ($request->full_istirahatmulaijumat[$i] != '' and $request->full_istirahatselesaijumat[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,6,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaijumat[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaijumat[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulaisabtu)) {
                    $totalsabtu = count($request->full_istirahatmulaisabtu);
                    for ($i = 0; $i < $totalsabtu; $i++) {
                        if ($request->full_istirahatmulaisabtu[$i] != '' and $request->full_istirahatselesaisabtu[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,7,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaisabtu[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaisabtu[$i]);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($request->full_istirahatmulaiminggu)) {
                    $totalminggu = count($request->full_istirahatmulaiminggu);
                    for ($i = 0; $i < $totalminggu; $i++) {
                        if ($request->full_istirahatmulaiminggu[$i] != '' and $request->full_istirahatselesaiminggu[$i] != '') {
                            $sql = 'INSERT INTO jamkerjafullistirahat VALUES(NULL,:idjamkerjafull,1,:jamawal,:jamakhir,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idjamkerjafull', $idjamkerjafull);
                            $stmt->bindValue(':jamawal', $request->full_istirahatmulaiminggu[$i]);
                            $stmt->bindValue(':jamakhir', $request->full_istirahatselesaiminggu[$i]);
                            $stmt->execute();
                        }
                    }
                }

                Utils::insertLogUser('Ubah jam kerja full "' . $row['berlakumulai'] . '" => "' . $request->berlakumulai . '"');

                return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/full')->with('message', trans('all.databerhasildiubah'));
            } else {
                return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/full/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/full/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($idjamkerja, $id)
    {
        if(Utils::cekHakakses('jamkerja','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan idjamkerjafull ada
            $sql = 'SELECT id,DATE_FORMAT(berlakumulai, "%d/%m/%Y") as berlakumulai FROM jamkerjafull WHERE id=:idjamkerjafull LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjafull', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                JamKerjaFull::find($id)->delete();

                Utils::insertLogUser('Hapus jam kerja full "' . $row['berlakumulai'] . '"');

                return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/full')->with('message', trans('all.databerhasildihapus'));
            } else {
                return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/full')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function excel($id)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.full'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', trans('all.berlakumulai'));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', trans('all.keterangan'));

            $sql = 'SELECT
                        berlakumulai,
                        CONCAT(
                          if(_1_masukkerja = "y", CONCAT("'.trans('all.minggu').' : ",_1_jammasuk," - ",_1_jampulang," "), ""),
                          if(_2_masukkerja = "y", CONCAT("'.trans('all.senin').' : ",_2_jammasuk," - ",_2_jampulang," "), ""),
                          if(_3_masukkerja = "y", CONCAT("'.trans('all.selasa').' : ",_3_jammasuk," - ",_3_jampulang," "), ""),
                          if(_4_masukkerja = "y", CONCAT("'.trans('all.rabu').' : ",_4_jammasuk," - ",_4_jampulang," "), ""),
                          if(_5_masukkerja = "y", CONCAT("'.trans('all.kamis').' : ",_5_jammasuk," - ",_5_jampulang," "), ""),
                          if(_6_masukkerja = "y", CONCAT("'.trans('all.jumat').' : ",_6_jammasuk," - ",_6_jampulang," "), ""),
                          if(_7_masukkerja = "y", CONCAT("'.trans('all.sabtu').' : ",_7_jammasuk," - ",_7_jampulang," "), "")
                        ) as keterangan
                    FROM
                        jamkerjafull
                    WHERE
                        idjamkerja = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, Utils::tanggalCantik($row['berlakumulai']));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['keterangan']);

                // format
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getNumberFormat()->setFormatCode('DD/MM/YYYY');

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor jam kerja full');
            $arrWidth = array(25, 1000);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.full'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}