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

class PayrollKelompokAtributController extends Controller
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

	public function getindex($idpayrollkelompok)
	{
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            Utils::insertLogUser('akses menu payroll_kelompok');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $kelompok = Utils::getDataWhere($pdo,'payroll_kelompok','nama','id',$idpayrollkelompok);
	        return view('datainduk/payroll/payrollkelompokatribut/index', ['idpayrollkelompok' => $idpayrollkelompok, 'kelompok' => $kelompok, 'menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request, $idpayrollkelompok)
	{
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('payrollkomponenmaster','uhm')) {
                $columns = array('', 'atribut', 'atributnilai');
            }else{
                $columns = array('atribut', 'atributnilai');
            }
            $table = '(SELECT
                            pka.id,
                            a.atribut,
                            an.nilai as atributnilai,
                            pka.idpayroll_kelompok
                        FROM
                            payroll_kelompok_atribut pka,
                            atributnilai an,
                            atribut a
                        WHERE
                              pka.idatributnilai=an.id AND
                              an.idatribut=a.id
                      ) x';
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idpayroll_kelompok = :idpayroll_kelompok '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpayroll_kelompok', $idpayrollkelompok);
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
                $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idpayroll_kelompok = :idpayroll_kelompok '.$where;
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpayroll_kelompok', $idpayrollkelompok);
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                    }
                }
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalFiltered = $row['total'];
            }

            $sql = 'SELECT * FROM '.$table.' WHERE idpayroll_kelompok = :idpayroll_kelompok ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpayroll_kelompok', $idpayrollkelompok);
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
                        $action .= Utils::tombolManipulasi('ubah','atribut',$key['id']);
                    }
                    if(Utils::cekHakakses('payrollkomponenmaster','hm')){
                        $action .= Utils::tombolManipulasi('hapus','atribut',$key['id']);
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

	public function create($idpayrollkelompok)
    {
        if(Utils::cekHakakses('payrollkomponenmaster','tm')){
            Utils::insertLogUser('akses menu tambah payroll_kelompok');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $dataatribut = Utils::getData($pdo,'atribut','id,atribut as nama','','atribut');
            return view('datainduk/payroll/payrollkelompokatribut/create', ['dataatribut' => $dataatribut, 'idpayrollkelompok' => $idpayrollkelompok, 'menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request, $idpayrollkelompok)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'payroll_kelompok_atribut','id','idatributnilai',$request->atributnilai);
        if($cekadadata == ''){
            $sql = 'INSERT INTO payroll_kelompok_atribut VALUES(NULL,:idpayroll_kelompok,:idatributnilai)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpayroll_kelompok', $idpayrollkelompok);
            $stmt->bindValue(':idatributnilai', $request->atributnilai);
            $stmt->execute();

            Utils::insertLogUser('Tambah payroll kelompok "'.Utils::getDataWhere($pdo,'atributnilai','nilai','id',$request->atributnilai).'"');
    
            return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/atribut')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/atribut/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($idpayrollkelompok, $id)
    {
        if(Utils::cekHakakses('payrollkomponenmaster','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM payroll_kelompok_atribut WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$data){
                abort(404);
            }

            $dataatribut = Utils::getData($pdo,'atribut','id,atribut as nama','','atribut');
//            $idatributselected = Utils::getDataCustomWhere($pdo,'atributnilai','idatribut','id = '.$data->idatributnilai);
            $idatributselected = Utils::getDataWhere($pdo,'atributnilai','idatribut','id',$data->idatributnilai);
            Utils::insertLogUser('akses menu ubah payroll kelompok atribut');
            return view('datainduk/payroll/payrollkelompokatribut/edit', ['data' => $data, 'idatributselected' => $idatributselected, 'idpayrollkelompok' => $idpayrollkelompok, 'dataatribut' => $dataatribut, 'menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $idpayrollkelompok, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'payroll_kelompok_atribut','idatributnilai','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'payroll_kelompok_atribut','id','idatributnilai = "'.$request->atributnilai.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $sql = 'UPDATE payroll_kelompok_atribut SET idatributnilai = :idatributnilai WHERE id = :id LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idatributnilai', $request->atributnilai);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::insertLogUser('Ubah payroll kelompok "'.Utils::getDataWhere($pdo,'atributnilai','nilai','id',$cekadadata).'" => "'.Utils::getDataWhere($pdo,'atributnilai','nilai','id',$request->atributnilai).'"');
    
                return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/atribut')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/atribut/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/atribut/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($idpayrollkelompok, $id)
    {
        if(Utils::cekHakakses('payrollkomponenmaster','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'payroll_kelompok_atribut','idatributnilai','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'payroll_kelompok_atribut',$id);
                Utils::insertLogUser('Hapus payroll kelompok atribut "'.Utils::getDataWhere($pdo,'atributnilai','nilai','id',$cekadadata).'"');
                return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/atribut')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/atribut')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel($idpayrollkelompok)
    {
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.payrollkelompokatribut'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.atribut'))
                        ->setCellValue('B1', trans('all.atributnilai'));

            $sql = 'SELECT
                        pka.id,
                        a.atribut,
                        an.nilai as atributnilai
                    FROM
                        payroll_kelompok_atribut pka,
                        atributnilai an,
                        atribut a
                    WHERE
                      pka.idatributnilai=an.id AND
                      an.idatribut=a.id AND
                      pka.idpayroll_kelompok = '.$idpayrollkelompok.'
                    ORDER BY
                        an.nilai';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['atribut']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['atributnilai']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor payroll kelompok atribut');
            $arrWidth = array(50, 50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.payrollkelompokatribut') . '_'.Utils::getDataWhere($pdo,'payroll_kelompok','nama','id',$idpayrollkelompok));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}