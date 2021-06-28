<?php
namespace App\Http\Controllers;

use App\agama;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class AgamaController extends Controller
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
	    if(Utils::cekHakakses('agama','l')){
            $totaldata = Utils::getTotalData(1,'agama');
            Utils::insertLogUser('akses menu agama');
	        return view('datainduk/pegawai/agama/index', ['totaldata' => $totaldata, 'menu' => 'agama']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('agama','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('agama','uhm')){
                $columns = array('', 'urutan', 'agama');
            }else{
                $columns = array('urutan', 'agama');
            }
            $table = 'agama';
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

            $sql = 'SELECT id,agama,urutan FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    if(Utils::cekHakakses('agama','um')){
                        $action .= Utils::tombolManipulasi('ubah','agama',$key['id']);
                    }
                    if(Utils::cekHakakses('agama','hm')){
                        $action .= Utils::tombolManipulasi('hapus','agama',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
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
        if(Utils::cekHakakses('agama','tm')){
            Utils::insertLogUser('akses menu tambah agama');
            return view('datainduk/pegawai/agama/create', ['menu' => 'agama']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM agama WHERE agama = :agama';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':agama', $request->agama);
        $stmt->execute();
        if($stmt->rowCount() == 0){
            $sql = 'INSERT INTO agama VALUES(NULL,:agama,:urutan,NOW())';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':agama', $request->agama);
            $stmt->bindValue(':urutan', $request->urutan);
            $stmt->execute();

            Utils::insertLogUser('Tambah agama "'.$request->agama.'"');
    
            return redirect('datainduk/pegawai/agama')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/pegawai/agama/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('agama','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM agama WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $agama = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$agama){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah agama');
            return view('datainduk/pegawai/agama/edit', ['agama' => $agama, 'menu' => 'agama']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'agama','agama','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'agama','id','agama = "'.$request->agama.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $sql = 'UPDATE agama SET agama = :agama, urutan = :urutan WHERE id = :idagama LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':agama', $request->agama);
                $stmt->bindValue(':urutan', $request->urutan);
                $stmt->bindValue(':idagama', $id);
                $stmt->execute();

                Utils::insertLogUser('Ubah agama "'.$cekadadata.'" => "'.$request->agama.'"');
    
                return redirect('datainduk/pegawai/agama')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/pegawai/agama/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/pegawai/agama/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('agama','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'agama','agama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'agama',$id);
                Utils::insertLogUser('Hapus agama "'.$cekadadata.'"');
                return redirect('datainduk/pegawai/agama')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/pegawai/agama')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('agama','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.agama'));
            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.urutan'))
                        ->setCellValue('B1', trans('all.agama'));

            $sql = 'SELECT agama,urutan FROM agama ORDER BY urutan ASC, agama ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['urutan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['agama']);
                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor agama');
            $arrWidth = array(10, 25);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.agama'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}