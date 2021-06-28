<?php
namespace App\Http\Controllers;

use App\AtributVariable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class AtributVariableController extends Controller
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
        if(Utils::cekHakakses('atribut','l')){
            Utils::insertLogUser('akses menu atirbut variable');
            return view('datainduk/pegawai/atributvariable/index', ['menu' => 'atributvariable']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('atribut','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('atribut','uhm')) {
                $columns = array('', 'atribut', 'kode', 'penting');
            } else {
                $columns = array('atribut', 'kode', 'penting');
            }
            $table = 'atributvariable';
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

            $sql = 'SELECT id,atribut,kode,penting FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    if(Utils::cekHakakses('atribut','um')){
                        $action .= Utils::tombolManipulasi('ubah','atributvariable',$key['id']);
                    }
                    if(Utils::cekHakakses('atribut','hm')){
                        $action .= Utils::tombolManipulasi('hapus','atributvariable',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'penting') {
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
        if(Utils::cekHakakses('atribut','tm')){
            Utils::insertLogUser('akses menu tambah atribut variable');
            return view('datainduk/pegawai/atributvariable/create', ['menu' => 'atributvariable']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $carainputan_tipedata = $request->carainputan_tipedata;
        $carainputan_array = array();
        if($carainputan_tipedata != ''){
            $carainputan_array[0]['tipedata'] = $request->carainputan_tipedata;
            $carainputan_array[0]['bolehkosong'] = $request->carainputan_bolehkosong;
            if($carainputan_tipedata == 'text') {
                $carainputan_array[0]['jumlahkarakter'] = $request->carainputan_jumlahkarakter;
                $carainputan_array[0]['regex'] = $request->carainputan_regex;
            }
            if($carainputan_tipedata == 'number') {
                $carainputan_array[0]['range'] = $request->carainputan_jangkauan;
                $carainputan_array[0]['decimal'] = $request->carainputan_desimal;
            }
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM atributvariable WHERE atribut = :atribut';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':atribut', $request->atribut);
        $stmt->execute();
        if ($stmt->rowCount()==0) {

            $sql = 'INSERT INTO atributvariable VALUES(NULL,:atribut,:kode,:carainputan,:penting,NOW())';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':atribut', $request->atribut);
            $stmt->bindValue(':kode', $request->kode == '' ? null : $request->kode);
            $stmt->bindValue(':carainputan', count($carainputan_array) == 0 ? NULL : json_encode($carainputan_array));
            $stmt->bindValue(':penting', $request->penting);
            $stmt->execute();

            Utils::insertLogUser('Tambah atribut variable "'.$request->atribut.'"');
    
            return redirect('datainduk/pegawai/atributvariable')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/pegawai/atributvariable/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('atribut','um')){
            $atribut = AtributVariable::find($id);
            if(!$atribut){
                abort(404);
            }
//            $carainputan_json = json_decode('['.$atribut->carainputan.']');
            $carainputan_json = json_decode($atribut->carainputan);
            $tipedata = '';
            $bolehkosong = '';
            $jumlahkarakter = '';
            $regex = '';
            $rangeawal = '';
            $rangeakhir = '';
            $decimal = '';
            if($carainputan_json != '' && json_encode($carainputan_json) != '[{}]') {
                foreach($carainputan_json as $key){
                    $tipedata = $key->tipedata;
                    $bolehkosong = isset($key->bolehkosong) ? $key->bolehkosong : 'y';
                    $jumlahkarakter = isset($key->jumlahkarakter) ? $key->jumlahkarakter : '';
                    $regex = isset($key->regex) ? $key->regex : '';
                    if(isset($key->range) && $key->range != '' && strpos($key->range, '-') > -1){
                        $rangeexplode = explode('-', $key->range);
                        $rangeawal = $rangeexplode[0];
                        $rangeakhir = $rangeexplode[1];
                    }
                    $decimal = isset($key->decimal) ? $key->decimal : '';
                }
            }
            Utils::insertLogUser('akses menu ubah atribut variable');
            return view('datainduk/pegawai/atributvariable/edit', ['atribut' => $atribut, 'tipedata' => $tipedata, 'bolehkosong' => $bolehkosong, 'jumlahkarakter' => $jumlahkarakter, 'regex' => $regex, 'rangeawal' => $rangeawal, 'rangeakhir' => $rangeakhir, 'decimal' => $decimal, 'menu' => 'atributvariable']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $carainputan_tipedata = $request->carainputan_tipedata;
        $carainputan_array = array();
        if($carainputan_tipedata != ''){
            $carainputan_array[0]['tipedata'] = $request->carainputan_tipedata;
            $carainputan_array[0]['bolehkosong'] = $request->carainputan_bolehkosong;
            if($carainputan_tipedata == 'text') {
                $carainputan_array[0]['jumlahkarakter'] = $request->carainputan_jumlahkarakter;
                $carainputan_array[0]['regex'] = $request->carainputan_regex;
            }
            if($carainputan_tipedata == 'number') {
                $carainputan_array[0]['range'] = $request->carainputan_jangkauan;
                $carainputan_array[0]['decimal'] = $request->carainputan_desimal;
            }
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,atribut FROM atributvariable WHERE id = :idatributvariable';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idatributvariable', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //cek apakah kembar?
            $sql = 'SELECT id FROM atributvariable WHERE atribut=:atribut AND id<>:idatributvariable LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':atribut', $request->atribut);
            $stmt->bindValue(':idatributvariable', $id);
            $stmt->execute();
            if ($stmt->rowCount()==0) {
    
                $atribut = AtributVariable::find($id);
                $atribut->atribut = $request->atribut;
                $atribut->kode = $request->kode == '' ? NULL : $request->kode;
                $atribut->carainputan = count($carainputan_array) == 0 ? NULL : json_encode($carainputan_array);
                $atribut->penting = $request->penting;
                $atribut->save();

                Utils::insertLogUser('Ubah atribut variable "'.$row['atribut'].'" => "'.$request->atribut.'"');
    
                return redirect('datainduk/pegawai/atributvariable')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/pegawai/atributvariable/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/pegawai/atributvariable/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('atribut','hm')){
            //pastikan idatributvariable ada
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,atribut FROM atributvariable WHERE id=:idatributvariable LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idatributvariable', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                AtributVariable::find($id)->delete();
                Utils::insertLogUser('Hapus atribut variable "'.$row['atribut'].'"');
                return redirect('datainduk/pegawai/atributvariable')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/pegawai/atributvariable')->with('message', trans('all.datatidakditemukan'));
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
            Utils::setPropertiesExcel($objPHPExcel,trans('all.atributvariable'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.atribut'))
                        ->setCellValue('B1', trans('all.kode'))
                        ->setCellValue('C1', trans('all.penting'));

            $sql = 'SELECT atribut,kode,penting FROM atributvariable ORDER BY atribut';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['atribut']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['kode']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['penting'] == 'y' ? trans('all.ya') : trans('all.tidak'));

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor atribut variable');
            $arrWidth = array(20, 12, 15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.atributvariable'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}