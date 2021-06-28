<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class customDashboardController extends Controller
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
        if(Utils::cekHakakses('customdashboard','l')){
            Utils::insertLogUser('akses menu custom dashboard');
            return view('pengaturan/customdashboard/index', ['menu' => 'customdashboard']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
    {
        if(Utils::cekHakakses('customdashboard','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('customdashboard','uhm')) {
                $columns = array('', 'nama', 'tampil_konfirmasi', 'tampil_peringkat', 'tampil_3lingkaran', 'tampil_sudahbelumabsen', 'tampil_terlambatdll', 'tampil_pulangawaldll', 'tampil_totalgrafik', 'tampil_peta', 'tampil_harilibur', 'tampil_riwayatdashboard');
            }else{
                $columns = array('nama', 'tampil_konfirmasi', 'tampil_peringkat', 'tampil_3lingkaran', 'tampil_sudahbelumabsen', 'tampil_terlambatdll', 'tampil_pulangawaldll', 'tampil_totalgrafik', 'tampil_peta', 'tampil_harilibur', 'tampil_riwayatdashboard');
            }
            $table = 'customdashboard';
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

            $sql = 'SELECT id,nama,tampil_konfirmasi,tampil_peringkat,tampil_3lingkaran,tampil_sudahbelumabsen,tampil_terlambatdll,tampil_pulangawaldll,tampil_totalgrafik,tampil_peta,tampil_harilibur,tampil_riwayatdashboard FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    $action = Utils::tombolManipulasi('detail','customdashboard',$key['id']);
                    if(Utils::cekHakakses('customdashboard','um')){
                        $action .= Utils::tombolManipulasi('ubah','customdashboard',$key['id']);
                    }
                    if(Utils::cekHakakses('customdashboard','hm')){
                        $action .= Utils::tombolManipulasi('hapus','customdashboard',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'nama'){
                            $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                        }else{
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]]);
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
        if(Utils::cekHakakses('customdashboard','tm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $datacustomdashboardnode = Utils::getData($pdo,'customdashboard_node','id,nama','','nama');
            Utils::insertLogUser('akses menu tambah custom dashboard');
            return view('pengaturan/customdashboard/create', ['datacustomdashboardnode' => $datacustomdashboardnode, 'menu' => 'customdashboard']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek apakah kembar?
        $cekadadata = Utils::getDataWhere($pdo,'customdashboard','id','nama',$request->nama);
        if($cekadadata == ''){
            try
            {
                $pdo->beginTransaction();

                $sql = 'INSERT INTO customdashboard VALUES(NULL,:nama,:tampil_konfirmasi,:tampil_peringkat,:tampil_3lingkaran,:tampil_sudahbelumabsen,:tampil_terlambatdll,:tampil_pulangawaldll,:tampil_totalgrafik,:tampil_peta,:tampil_harilibur,:tampil_riwayatdashboard,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':tampil_konfirmasi', $request->tampil_konfirmasi);
                $stmt->bindValue(':tampil_peringkat', $request->tampil_peringkat);
                $stmt->bindValue(':tampil_3lingkaran', $request->tampil_3lingkaran);
                $stmt->bindValue(':tampil_sudahbelumabsen', $request->tampil_sudahbelumabsen);
                $stmt->bindValue(':tampil_terlambatdll', $request->tampil_terlambatdll);
                $stmt->bindValue(':tampil_pulangawaldll', $request->tampil_pulangawaldll);
                $stmt->bindValue(':tampil_totalgrafik', $request->tampil_totalgrafik);
                $stmt->bindValue(':tampil_peta', $request->tampil_peta);
                $stmt->bindValue(':tampil_harilibur', $request->tampil_harilibur);
                $stmt->bindValue(':tampil_riwayatdashboard', $request->tampil_riwayatdashboard);
                $stmt->execute();

                Utils::insertLogUser('Tambah custom dashboard "'.$request->nama.'"');

                $pdo->commit();
                return redirect('pengaturan/customdashboard')->with('message', trans('all.databerhasildisimpan'));
            } catch (\Exception $e) {
                $pdo->rollBack();
                return redirect('pengaturan/customdashboard/')->with('message', trans('all.terjadigangguan'));
            }
        }else{
            return redirect('pengaturan/customdashboard/create')->with('message', trans('all.datasudahada'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('customdashboard','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM customdashboard WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah custom dashboard');
            return view('pengaturan/customdashboard/edit', ['data' => $data, 'menu' => 'customdashboard']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idatribut ada
        $cekadadata = Utils::getDataWhere($pdo,'customdashboard','nama','id',$id);
        if($cekadadata != ''){
            //cek apakah kembar?
            $cekkembar = Utils::getData($pdo,'customdashboard','id','nama = "'.$request->nama.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                try
                {
                    $pdo->beginTransaction();

                    // ubah data customdashboard
                    $sql = 'UPDATE customdashboard SET nama = :nama, tampil_konfirmasi = :tampil_konfirmasi, tampil_peringkat = :tampil_peringkat, tampil_3lingkaran = :tampil_3lingkaran, tampil_sudahbelumabsen = :tampil_sudahbelumabsen, tampil_terlambatdll = :tampil_terlambatdll, tampil_pulangawaldll = :tampil_pulangawaldll, tampil_totalgrafik = :tampil_totalgrafik, tampil_peta = :tampil_peta, tampil_harilibur = :tampil_harilibur, tampil_riwayatdashboard = :tampil_riwayatdashboard WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':nama', $request->nama);
                    $stmt->bindValue(':tampil_konfirmasi', $request->tampil_konfirmasi);
                    $stmt->bindValue(':tampil_peringkat', $request->tampil_peringkat);
                    $stmt->bindValue(':tampil_3lingkaran', $request->tampil_3lingkaran);
                    $stmt->bindValue(':tampil_sudahbelumabsen', $request->tampil_sudahbelumabsen);
                    $stmt->bindValue(':tampil_terlambatdll', $request->tampil_terlambatdll);
                    $stmt->bindValue(':tampil_pulangawaldll', $request->tampil_pulangawaldll);
                    $stmt->bindValue(':tampil_totalgrafik', $request->tampil_totalgrafik);
                    $stmt->bindValue(':tampil_peta', $request->tampil_peta);
                    $stmt->bindValue(':tampil_harilibur', $request->tampil_harilibur);
                    $stmt->bindValue(':tampil_riwayatdashboard', $request->tampil_riwayatdashboard);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah custom dashboard "'.$cekadadata.'" => "'.$request->nama.'"');

                    $pdo->commit();
                    $msg = trans('all.databerhasildiubah');
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    $msg = $e->getMessage();
                }
                return redirect('pengaturan/customdashboard')->with('message', $msg);
            }else{
                $msg = trans('all.datasudahada');
            }
        }else{
            $msg = trans('all.datatidakditemukan');
        }
        return redirect('pengaturan/customdashboard/'.$id.'/edit')->with('message', $msg);
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('customdashboard','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan idatribut ada
            $cekadadata = Utils::getDataWhere($pdo,'customdashboard','nama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'customdashboard',$id);
                Utils::insertLogUser('Hapus custom dashboard "'.$cekadadata.'"');
                $msg = trans('all.databerhasildihapus');
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('pengaturan/customdashboard')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function detail($id)
    {
	    $pdo = DB::connection('perusahaan_db')->getPdo();
	    $customdashboard = Utils::getDataWhere($pdo,'customdashboard','nama','id',$id);
	    $datacustomdashboardnode = Utils::getData($pdo,'customdashboard_node','id,nama','id NOT IN(SELECT idcustomdashboard_node FROM customdashboard_detail WHERE idcustomdashboard = '.$id.')','nama');
	    $sql = 'SELECT
                    cdd.idcustomdashboard_node,
                    cdn.nama as customdashboardnode
	            FROM
	                customdashboard_detail cdd
	                LEFT JOIN customdashboard_node cdn ON cdd.idcustomdashboard_node=cdn.id
	            WHERE
	                cdd.idcustomdashboard = :id
                ORDER BY
                    cdd.urutan';
	    $stmt = $pdo->prepare($sql);
	    $stmt->bindValue(':id', $id);
	    $stmt->execute();
        $datacustomdashboarddetail = $stmt->fetchAll(PDO::FETCH_OBJ);
        Utils::insertLogUser('akses menu detail custom dashboard');
        return view('pengaturan/customdashboard/detail', ['data' => '', 'customdashboard' => $customdashboard, 'datacustomdashboardnode' => $datacustomdashboardnode, 'datacustomdashboarddetail' => $datacustomdashboarddetail, 'id' => $id, 'menu' => 'customdashboard']);
    }

    public function submitDetail(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        if(count($request->id) > 0){
            Utils::deleteData($pdo,'customdashboard_detail',$id,'idcustomdashboard');
            if(count($request->idcdd) > 0) {
                for ($i = 0; $i < count($request->idcdd); $i++) {
                    $urutan = $i + 1;
                    $sql = 'INSERT INTO customdashboard_detail VALUES(NULL,:urutan,:idcustomdashboard,:idcustomdashboard_node)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':urutan', $urutan);
                    $stmt->bindValue(':idcustomdashboard', $id);
                    $stmt->bindValue(':idcustomdashboard_node', $request->idcdd[$i]);
                    $stmt->execute();
                }
            }
            Utils::insertLogUser('Simpan custom dashboard detail');
        }
        return redirect('pengaturan/customdashboard/'.$id.'/detail')->with('message', trans('all.databerhasildisimpan'));
    }

    public function excel()
    {
        if(Utils::cekHakakses('customdashboard','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.customdashboard'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.tampil_konfirmasi'))
                        ->setCellValue('C1', trans('all.tampil_peringkat'))
                        ->setCellValue('D1', trans('all.tampil_3lingkaran'))
                        ->setCellValue('E1', trans('all.tampil_sudahbelumabsen'))
                        ->setCellValue('F1', trans('all.tampil_terlambatdll'))
                        ->setCellValue('G1', trans('all.tampil_pulangawaldll'))
                        ->setCellValue('H1', trans('all.tampil_totalgrafik'))
                        ->setCellValue('I1', trans('all.tampil_peta'))
                        ->setCellValue('J1', trans('all.tampil_harilibur'))
                        ->setCellValue('K1', trans('all.tampil_riwayatdashboard'));

            $sql = 'SELECT
                        nama,
                        IF(tampil_konfirmasi="y","'.trans('all.ya').'","'.trans('all.tidak').'") as tampil_konfirmasi,
                        IF(tampil_peringkat="y","'.trans('all.ya').'","'.trans('all.tidak').'") as tampil_peringkat,
                        IF(tampil_3lingkaran="y","'.trans('all.ya').'","'.trans('all.tidak').'") as tampil_3lingkaran,
                        IF(tampil_sudahbelumabsen="y","'.trans('all.ya').'","'.trans('all.tidak').'") as tampil_sudahbelumabsen,
                        IF(tampil_terlambatdll="y","'.trans('all.ya').'","'.trans('all.tidak').'") as tampil_terlambatdll,
                        IF(tampil_pulangawaldll="y","'.trans('all.ya').'","'.trans('all.tidak').'") as tampil_pulangawaldll,
                        IF(tampil_totalgrafik="y","'.trans('all.ya').'","'.trans('all.tidak').'") as tampil_totalgrafik,
                        IF(tampil_peta="y","'.trans('all.ya').'","'.trans('all.tidak').'") as tampil_peta,
                        IF(tampil_harilibur="y","'.trans('all.ya').'","'.trans('all.tidak').'") as tampil_harilibur,
                        IF(tampil_riwayatdashboard="y","'.trans('all.ya').'","'.trans('all.tidak').'") as tampil_riwayatdashboard
                    FROM
                        customdashboard
                    ORDER BY
                        nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['tampil_konfirmasi']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['tampil_peringkat']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['tampil_3lingkaran']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['tampil_sudahbelumabsen']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['tampil_terlambatdll']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['tampil_pulangawaldll']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $row['tampil_totalgrafik']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $row['tampil_peta']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $row['tampil_harilibur']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, $row['tampil_riwayatdashboard']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor customdashboard');
            $arrWidth = array(30, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.customdashboard'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}