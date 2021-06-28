<?php
namespace App\Http\Controllers;

use App\Atribut;
use App\AtributNilai;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class AtributController extends Controller
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
        $onboarding = $request->query('onboarding');
        if(Utils::cekHakakses('atribut','l')){
            Utils::insertLogUser('akses menu atribut');
            return view('datainduk/pegawai/atribut/index', ['menu' => 'atribut', 'onboarding'=>$onboarding]);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        $table = '(
                    SELECT
                        a.id,
                        a.atribut,
                        a.tampilpadaringkasan,
                        a.penting,
                        a.jumlahinputan,
                        GROUP_CONCAT(an.nilai ORDER BY an.nilai SEPARATOR ", ") as nilai
                    FROM
                        atribut a
                        LEFT JOIN atributnilai an ON an.idatribut=a.id
                    GROUP BY
                        a.id
                   ) x';
        $columns = array('','atribut','tampilpadaringkasan','penting','jumlahinputan','nilai');
        $totalData = Utils::getDataCustomWhere($pdo,'atribut', 'count(id)');
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumn = $request->input('order.0.column') == 0 ? 'id' : $columns[$request->input('order.0.column')]; //first load order by id desc
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
                $action = Utils::tombolManipulasi('detail','atribut',$key['id']);
                if(Utils::cekHakakses('atribut','um')){
                    $action .= Utils::tombolManipulasi('ubah','atribut',$key['id']);
                }
                if(Utils::cekHakakses('atribut','hm')){
                    $action .= Utils::tombolManipulasi('hapus','atribut',$key['id']);
                }
                $tempdata['action'] = '<center>'.$action.'</center>';
                for($i=1;$i<count($columns);$i++){
                    if($columns[$i] == 'tampilpadaringkasan' || $columns[$i] == 'penting' || $columns[$i] == 'jumlahinputan') {
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

	public function create(Request $request)
    {
        $isOnboarding = $request->query('onboarding');
        if(Utils::cekHakakses('atribut','tm')){
            Utils::insertLogUser('akses menu tambah atribut');
            return view('datainduk/pegawai/atribut/create', ['menu' => 'atribut', 'onboarding' => $isOnboarding]);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek apakah kembar?
        $cekkembar = Utils::getDataWhere($pdo,'atribut','id','atribut',$request->atribut);
        if ($cekkembar == '') {
            $cekkode = Utils::getDataWhere($pdo,'atribut','id','kode',$request->kode);
            if($cekkode == ''){
                $sql = 'INSERT INTO atribut VALUES(NULL,:atribut,:kode,:jumlahinputan,:tampilpadaringkasan,:penting,NOW())';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':atribut', $request->atribut);
                $stmt->bindValue(':kode', $request->kode == '' ? NULL : $request->kode);
                $stmt->bindValue(':jumlahinputan', $request->jumlahinputan);
                $stmt->bindValue(':tampilpadaringkasan', $request->tampilpadaringkasan);
                $stmt->bindValue(':penting', $request->penting);
                $stmt->execute();
                $idatribut = $pdo->lastInsertId();
                if ($request->nilai != '') {
                    for ($i = 0; $i < count($request->nilai); $i++) {
                        $urutan = $i+1;
                        $sql1 = 'INSERT INTO atributnilai VALUES(NULL,:idatribut,:nilai,:kode,:urutan,NOW())';
                        $stmt1 = $pdo->prepare($sql1);
                        $stmt1->bindValue(':idatribut', $idatribut);
                        $stmt1->bindValue(':nilai', $request->nilai[$i]);
                        $stmt1->bindValue(':kode', $request->nilaikode[$i] == '' ? NULL : $request->nilaikode[$i]);
                        $stmt1->bindValue(':urutan', $urutan);
                        $stmt1->execute();
                    }
                }

                Utils::insertLogUser('Tambah atribut "'.$request->atribut.'"');

                $with = [
                  'message'=>trans('all.databerhasildisimpan')
                ];

                if(Session::get('onboardingstep')==1) {
                  $pdo = DB::getPdo();
                  $sql = 'UPDATE user SET onboardingstep = :step WHERE id = :id';
                  $stmt = $pdo->prepare($sql);
                  $stmt->bindValue(':step', 2);
                  $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
                  $stmt->execute();

                  $with['success_add_attribut'] = '1';
                }

                $onboarding = $request->query('onboarding');
                if($onboarding) {
                  return redirect('datainduk/pegawai/atribut?onboarding='.$onboarding)->with($with);
                } else {
                  return redirect('datainduk/pegawai/atribut')->with($with);
                }
            }else{
                return redirect('datainduk/pegawai/atribut/create')->with('message', trans('all.kodesudahdigunakan'));
            }
        }else{
            return redirect('datainduk/pegawai/atribut/create')->with('message', trans('all.datasudahada'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('atribut','um')){
            $atribut = Atribut::find($id);
            if(!$atribut){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah atribut');
            return view('datainduk/pegawai/atribut/edit', ['atribut' => $atribut, 'menu' => 'atribut']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idatribut ada
        $cekadadata = Utils::getDataWhere($pdo,'atribut','atribut','id',$id);
        if($cekadadata != ''){
            //cek apakah kembar?
            $cekkembar = Utils::getData($pdo,'atribut','id','atribut = "'.$request->atribut.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                // ubah data atribut
                $atribut = Atribut::find($id);
                $atribut->atribut = $request->atribut;
                $atribut->kode = $request->kode == '' ? null : $request->kode;
                $atribut->jumlahinputan = $request->jumlahinputan;
                $atribut->tampilpadaringkasan = $request->tampilpadaringkasan;
                $atribut->penting = $request->penting;
                $atribut->save();

                Utils::insertLogUser('Ubah atribut "'.$cekadadata.'" => "'.$request->atribut.'"');

                return redirect('datainduk/pegawai/atribut')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/pegawai/atribut/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/pegawai/atribut/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('atribut','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan idatribut ada
            $cekadadata = Utils::getDataWhere($pdo,'atribut','atribut','id',$id);
            if($cekadadata != ''){
                Atribut::find($id)->delete();
                Utils::insertLogUser('Hapus atribut "'.$cekadadata.'"');
                return redirect('datainduk/pegawai/atribut')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/pegawai/atribut/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('atribut','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.atribut'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.atribut'))
                        ->setCellValue('B1', trans('all.tampilpadaringkasan'))
                        ->setCellValue('C1', trans('all.penting'))
                        ->setCellValue('D1', trans('all.jumlahinputan'))
                        ->setCellValue('E1', trans('all.nilai'));

            $sql = 'SELECT
                        a.atribut,
                        IF(a.tampilpadaringkasan="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as tampilpadaringkasan,
                        IF(a.penting="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as penting,
                        GROUP_CONCAT(an.nilai ORDER BY an.nilai SEPARATOR ", ") as nilai,
                        a.jumlahinputan
                    FROM
                        atribut a
                        LEFT JOIN atributnilai an ON an.idatribut=a.id
                    GROUP BY
                        a.id
                    ORDER BY
                        a.atribut';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['atribut']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['tampilpadaringkasan']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['penting']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['jumlahinputan']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['nilai']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor atribut');
            $arrWidth = array(15, 20, 10, 15, 1000);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.atribut'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }

    public function perlakuanLembur()
    {
        if(Utils::cekHakakses('atribut','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $dataatribut = Utils::getData($pdo,'atribut','id,atribut','','atribut');
            Utils::insertLogUser('akses menu perlakuan lembur atribut');
            return view('datainduk/pegawai/atribut/perlakuanlembur/index', ['dataatribut' => $dataatribut, 'menu' => 'atribut']);
        }else{
            return redirect('/');
        }
    }

    public function perlakuanLemburSubmit(Request $request)
    {
        $response = array();
        $response['msg'] = trans('all.andatidakdiizinkan');
        if(strpos(Session::get('hakakses_perusahaan')->atribut, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->atribut, 'm') !== false){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            try {
                $pdo->beginTransaction();
                $idatributnilai = explode('|', $request->idatributnilai);
                if($request->sisi == 'kiri') {
                    for($i=0;$i<count($idatributnilai);$i++){
                        $sql = 'INSERT INTO perlakuanlembur_atribut VALUES(NULL,:idatributnilai,:perlakuanlembur)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idatributnilai', $idatributnilai[$i]);
                        $stmt->bindValue(':perlakuanlembur', $request->perlakuanlembur);
                        $stmt->execute();
                    }
                    Utils::insertLogUser('tambah perlakuan lembur atribut');
                }else{
                    for($i=0;$i<count($idatributnilai);$i++) {
                        Utils::deleteData($pdo,'perlakuanlembur_atribut',$idatributnilai[$i],'idatributnilai');
                    }
                    Utils::insertLogUser('hapus perlakuan lembur atribut');
                }

                $pdo->commit();
                $response['msg'] = '';

            } catch (\Exception $e) {
                $pdo->rollBack();
                $response['msg'] = $e->getMessage();
            }
        }
        return $response;
    }

    public function perlakuanLemburData(Request $request,$sisi,$util="")
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        if($sisi == 'kiri' && $util != ''){
            $where .= ' AND id NOT IN(SELECT idatributnilai FROM perlakuanlembur_atribut) AND idatribut = "'.$util.'"';
        }elseif($sisi == 'kiri' && $util == ''){
            $where .= ' AND id NOT IN(SELECT idatributnilai FROM perlakuanlembur_atribut)';
        }
        if($sisi == 'kanan' && $util != ''){
            $where .= ' AND id IN(SELECT idatributnilai FROM perlakuanlembur_atribut WHERE perlakuanlembur = "'.$util.'")';
        }else if($sisi == 'kanan' && $util == ''){
            $where .= ' AND 1=2';
        }
        $columns = array('','atribut','atributnilai');
        $table = '(SELECT an.id,a.atribut,an.idatribut,an.nilai as atributnilai FROM atributnilai an, atribut a WHERE an.idatribut = a.id) x';
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
                $tempdata['cek'.$sisi] = '<input class="cek'.$sisi.'" type="checkbox" id="'.$key['id'].'">';
                for($i=1;$i<count($columns);$i++){
                    $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                }
                $data[] = $tempdata;
            }
        }
        return Utils::jsonDatatable($request->input('draw'),$totalData,$totalFiltered,$data);
    }
}