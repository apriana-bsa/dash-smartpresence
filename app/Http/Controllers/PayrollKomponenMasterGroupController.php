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

class PayrollKomponenMasterGroupController extends Controller
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
	    if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('payrollkomponenmaster','l')){
            Utils::insertLogUser('akses menu payroll komponen master group');
            return view('datainduk/payroll/payrollkomponenmastergroup/index', ['menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('payrollkomponenmaster','uhm')) {
                $columns = array('', 'nama');
            }else{
                $columns = array('nama');
            }
            $totalData = Utils::getDataCustomWhere($pdo,'payroll_komponen_master_group', 'count(id)');
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $columns[$request->input('order.0.column')];
            $orderAction = $request->input('order.0.dir');
            $orderBy = $orderColumn.' '.$orderAction;

            if(!empty($request->input('search.value'))){
                $search = $request->input('search.value');
                $where .= Utils::searchDatatableQuery($columns);
                $sql = 'SELECT COUNT(id) as total FROM payroll_komponen_master_group WHERE 1=1 '.$where;
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

            $sql = 'SELECT id,nama FROM payroll_komponen_master_group WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    if(Utils::cekHakakses('payrollkomponenmaster','um')){
                        $action .= Utils::tombolManipulasi('ubah','payrollkomponenmastergroup',$key['id']);
                    }
                    if(Utils::cekHakakses('payrollkomponenmaster','hm')){
                        $action .= Utils::tombolManipulasi('hapus','payrollkomponenmastergroup',$key['id']);
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
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && (strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 't') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'm') !== false)){
            Utils::insertLogUser('akses menu tambah payroll komponen master group');
            return view('datainduk/payroll/payrollkomponenmastergroup/create', ['menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y'){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id FROM payroll_komponen_master_group WHERE nama=:nama LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->execute();
            if ($stmt->rowCount()==0) {
                $sql = 'INSERT INTO payroll_komponen_master_group VALUES(NULL,:nama,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->execute();

                Utils::insertLogUser('Tambah payoll komponen master group "'.$request->nama.'"');
        
                return redirect('datainduk/payroll/payrollkomponenmastergroup')->with('message', trans('all.databerhasildisimpan'));
            }else{
                return redirect('datainduk/payroll/payrollkomponenmastergroup/create')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('/');
        }
    }
    
    public function edit($id)
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('payrollkomponenmaster','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM payroll_komponen_master_group WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah payroll komponen master group');
            return view('datainduk/payroll/payrollkomponenmastergroup/edit', ['data' => $data, 'menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y'){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'payroll_komponen_master_group','nama','id',$id);
            if($cekadadata != ''){
                //cek apakah kembar?
                $cekkembar = Utils::getData($pdo,'payroll_komponen_master_group','id','nama = "'.$request->nama.'" AND id<>'.$id.' LIMIT 1');
                if($cekkembar == ''){
                    $sql = 'UPDATE payroll_komponen_master_group SET nama = :nama, updated = NOW() WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':nama', $request->nama);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah payroll komponen master group "'.$cekadadata.'" => "'.$request->nama.'"');
        
                    return redirect('datainduk/payroll/payrollkomponenmastergroup')->with('message', trans('all.databerhasildiubah'));
                }else{
                    return redirect('datainduk/payroll/payrollkomponenmastergroup/'.$id.'/edit')->with('message', trans('all.datasudahada'));
                }
            }else{
                return redirect('datainduk/payroll/payrollkomponenmastergroup/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function destroy($id)
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('pekerjaan','hm')){
            //pastikan idpayrollkomponenmaster ada
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'payroll_komponen_master_group','nama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'payroll_komponen_master_group',$id);
                Utils::insertLogUser('Hapus payroll komponen master group "'.$cekadadata.'"');
                return redirect('datainduk/payroll/payrollkomponenmastergroup')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/payroll/payrollkomponenmastergroup')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if (Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('payrollkomponenmaster','l')) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.payrollkomponenmastergroup'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'));

            $sql = 'SELECT nama FROM payroll_komponen_master_group ORDER BY nama';
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
            Utils::insertLogUser('Ekspor payroll komponen master group');
            $arrWidth = array(50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.payrollkomponenmastergroup'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}