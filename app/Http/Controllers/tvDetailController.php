<?php
namespace App\Http\Controllers;

use App\tv;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class tvDetailController extends Controller
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
        if(Utils::cekHakakses('pengaturan','lu')){
            $totaldata = Utils::getTotalData(1,'tv');
            Utils::insertLogUser('akses menu tv detail');
	        return view('pengaturan/tvdetail/index', ['totaldata' => $totaldata, 'menu' => 'tv']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('pengaturan','lu')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','urutan','tv','group');
            $table = '(SELECT d.id,d.urutan,t.nama as tv,g.nama as `group` FROM tvdetail d, tv t, tvgroup g WHERE d.idtv = t.id AND d.idtvgroup = g.id) x';
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
                    if(Utils::cekHakakses('pengaturan','um')){
                        $action .= Utils::tombolManipulasi('ubah','tvdetail',$key['id']);
                    }
                    if(Utils::cekHakakses('pengaturan','hm')){
                        $action .= Utils::tombolManipulasi('hapus','tvdetail',$key['id']);
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
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
        if(Utils::cekHakakses('pengaturan','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $datatv = Utils::getData($pdo,'tv','id,nama','','nama');
            $datatvgroup = Utils::getData($pdo,'tvgroup','id,nama','','nama');
            Utils::insertLogUser('akses menu tambah tv detail');
            return view('pengaturan/tvdetail/create', ['datatv' => $datatv, 'datatvgroup' => $datatvgroup, 'menu' => 'tv']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM tvdetail WHERE idtv = :idtv AND idtvgroup = :idtvgroup';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idtv', $request->tv);
        $stmt->bindValue(':idtvgroup', $request->tvgroup);
        $stmt->execute();
        if ($stmt->rowCount()==0) {

            $sql = 'INSERT INTO tvdetail VALUES(NULL,:idtv,:idtvgroup,:urutan)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idtv', $request->tv);
            $stmt->bindValue(':idtvgroup', $request->tvgroup);
            $stmt->bindValue(':urutan', $request->urutan);
            $stmt->execute();

            Utils::insertLogUser('Tambah tv detail ');
    
            return redirect('pengaturan/tvdetail')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('pengaturan/tvdetail/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('pengaturan','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM tvdetail WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            $datatv = Utils::getData($pdo,'tv','id,nama','','nama');
            $datatvgroup = Utils::getData($pdo,'tvgroup','id,nama','','nama');

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah tv detail');
            return view('pengaturan/tvdetail/edit', ['data' => $data, 'datatv' => $datatv, 'datatvgroup' => $datatvgroup, 'menu' => 'tv']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM tvdetail WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {

            //cek apakah kembar?
            $sql = 'SELECT id FROM tvdetail WHERE idtv = :idtv AND idtvgroup = :idtvgroup AND id<>:id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idtv', $request->tv);
            $stmt->bindValue(':idtvgroup', $request->tvgroup);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount()==0) {

                $sql = 'UPDATE tvdetail SET urutan = :urutan, idtv = :idtv, idtvgroup = :idtvgroup WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':urutan', $request->urutan);
                $stmt->bindValue(':idtv', $request->tv);
                $stmt->bindValue(':idtvgroup', $request->tvgroup);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::insertLogUser('Ubah tv detail ');
    
                return redirect('pengaturan/tvdetail')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('pengaturan/tvdetail/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('pengaturan/tvdetail/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('pengaturan','um')){
            //pastikan idtv ada
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id FROM tvdetail WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                Utils::deleteData($pdo,'tvdetail',$id);
                Utils::insertLogUser('Hapus tv detail');
                return redirect('pengaturan/tvdetail')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('pengaturan/tvdetail')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('pengaturan','lum')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.tvdetail'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.urutan'))
                        ->setCellValue('B1', trans('all.tv'))
                        ->setCellValue('C1', trans('all.tvgroup'));

            $sql = 'SELECT
                        d.id,
                        d.urutan,
                        t.nama as tv,
                        g.nama as `group`
                    FROM
                        tvdetail d,
                        tv t,
                        tvgroup g
                    WHERE
                        d.idtv = t.id AND
                        d.idtvgroup = g.id
                    ORDER BY
                        d.urutan';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['urutan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['tv']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['group']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor tv detail');
            $arrWidth = array(10, 50, 50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.tvdetail'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}