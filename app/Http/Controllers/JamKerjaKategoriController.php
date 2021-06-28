<?php
namespace App\Http\Controllers;

use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class JamKerjaKategoriController extends Controller
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
            Utils::insertLogUser('akses menu jam kerja kategori');
            return view('datainduk/absensi/jamkerjakategori/index', ['menu' => 'jamkerja']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('jamkerja','uhm')) {
                $columns = array('', 'nama', 'digunakan');
            }else{
                $columns = array('nama', 'digunakan');
            }
            $table = 'jamkerjakategori';
            $totalData = Utils::getDataCustomWhere($pdo,'jamkerjakategori', 'count(id)');
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

            $sql = 'SELECT id,nama,digunakan FROM '.$table.' WHERE 1=1 ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                        $action .= Utils::tombolManipulasi('ubah','jamkerjakategori',$key['id']);
                    }
                    if(Utils::cekHakakses('jamkerja','hm')){
                        $action .= Utils::tombolManipulasi('hapus','jamkerjakategori',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'digunakan') {
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

	public function create()
    {
        if(Utils::cekHakakses('jamkerja','tm')){
            Utils::insertLogUser('akses menu tambah jam kerja kategori');
            return view('datainduk/absensi/jamkerjakategori/create', ['menu' => 'jamkerja']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'jamkerjakategori','id','nama',$request->nama);
        if($cekadadata == ''){
            $sql = 'INSERT INTO jamkerjakategori VALUES(NULL,:nama,:digunakan,NOW())';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':digunakan', $request->digunakan);
            $stmt->execute();

            Utils::insertLogUser('Tambah jam kerja kategori "'.$request->nama.'"');
    
            return redirect('datainduk/absensi/jamkerjakategori')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/absensi/jamkerjakategori/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('jamkerja','um')){

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,nama,digunakan FROM jamkerjakategori WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah jam kerja kategori');
            return view('datainduk/absensi/jamkerjakategori/edit', ['data' => $data, 'menu' => 'jamkerja']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'jamkerjakategori','nama','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'jamkerjakategori','id','nama = "'.$request->nama.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $sql = 'UPDATE jamkerjakategori SET nama = :nama, digunakan = :digunakan WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':digunakan', $request->digunakan);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::insertLogUser('Ubah jam kerja kategori"'.$cekadadata.'" => "'.$request->nama.'"');
    
                return redirect('datainduk/absensi/jamkerjakategori')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/absensi/jamkerjakategori/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/absensi/jamkerjakategori/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('jamkerja','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'jamkerjakategori','nama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'jamkerjakategori',$id);
                Utils::insertLogUser('Hapus jam kerja kategori "'.$cekadadata.'"');
                $msg = trans('all.databerhasildihapus');
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('datainduk/absensi/jamkerjakategori')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.kategorijamkerja'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.digunakan'));

            $sql = 'SELECT
                        nama,
                        IF(digunakan="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as digunakan
                    FROM
                        jamkerjakategori
                    ORDER BY
                        nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['digunakan']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor jam kerja kategori');
            $arrWidth = array(40, 20);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.kategorijamkerja'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}