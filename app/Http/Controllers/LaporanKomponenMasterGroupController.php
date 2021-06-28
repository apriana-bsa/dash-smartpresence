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

class LaporanKomponenMasterGroupController extends Controller
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
        if(Utils::cekHakakses('laporancustom','l')){
            Utils::insertLogUser('akses menu laporan komponen master group');
            return view('laporan/custom/komponenmastergroup/index', ['menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('laporancustom','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','nama');
            $table = 'laporan_komponen_master_group';
            $totalData = Utils::getDataCustomWhere($pdo,'laporan_komponen_master_group', 'count(id)');
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

            $sql = 'SELECT id,nama FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    if(Utils::cekHakakses('laporancustom','um')){
                        $action .= Utils::tombolManipulasi('ubah','komponenmastergroup',$key['id']);
                    }
                    if(Utils::cekHakakses('laporancustom','hm')){
                        $action .= Utils::tombolManipulasi('hapus','komponenmastergroup',$key['id']);
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
        if(Utils::cekHakakses('laporancustom','tm')){
            Utils::insertLogUser('akses menu tambah laporan komponen master group');
            return view('laporan/custom/komponenmastergroup/create', ['menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        if(Utils::cekHakakses('laporancustom','tm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'laporan_komponen_master','id','nama',$request->nama);
            if($cekadadata == ''){
                $sql = 'INSERT INTO laporan_komponen_master_group VALUES(NULL,:nama,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->execute();

                Utils::insertLogUser('Tambah payoll komponen master group "'.$request->nama.'"');
        
                return redirect('laporan/custom/komponenmastergroup')->with('message', trans('all.databerhasildisimpan'));
            }else{
                return redirect('laporan/custom/komponenmastergroup/create')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('/');
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('laporancustom','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM laporan_komponen_master_group WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah laporan komponen master group');
            return view('laporan/custom/komponenmastergroup/edit', ['data' => $data, 'menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        if(Utils::cekHakakses('laporancustom','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'laporan_komponen_master_group','nama','id',$id);
            if($cekadadata != ''){
                //cek apakah kembar?
                $cekkembar = Utils::getData($pdo,'laporan_komponen_master_group','id','nama = "'.$request->nama.'" AND id<>'.$id.' LIMIT 1');
                if($cekkembar == ''){
                    $sql = 'UPDATE laporan_komponen_master_group SET nama = :nama, updated = NOW() WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':nama', $request->nama);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah laporan komponen master group "'.$cekadadata.'" => "'.$request->nama.'"');
        
                    return redirect('laporan/custom/komponenmastergroup')->with('message', trans('all.databerhasildiubah'));
                }else{
                    return redirect('laporan/custom/komponenmastergroup/'.$id.'/edit')->with('message', trans('all.datasudahada'));
                }
            }else{
                return redirect('laporan/custom/komponenmastergroup/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('laporancustom','hm')){
            //pastikan idlaporankomponenmaster ada
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'laporan_komponen_master_group','nama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'laporan_komponen_master_group',$id);
                Utils::insertLogUser('Hapus laporan komponen master group "'.$cekadadata.'"');
                return redirect('laporan/custom/komponenmastergroup')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('laporan/custom/komponenmastergroup')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('laporancustom','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.laporankomponenmastergroup'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'));

            $sql = 'SELECT nama FROM laporan_komponen_master_group ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);

                $i++;
            }

            $arrWidth = array('', 50);
            for ($j = 1; $j <= 1; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor laporan komponen master group');
            $arrWidth = array(50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.laporankomponenmastergroup'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}