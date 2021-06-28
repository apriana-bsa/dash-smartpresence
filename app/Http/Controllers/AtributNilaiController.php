<?php
namespace App\Http\Controllers;

use App\AtributNilai;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class AtributNilaiController extends Controller
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

	public function getindex($id)
	{
        if(Utils::cekHakakses('atribut','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $atribut = Utils::getDataWhere($pdo,'atribut','atribut','id',$id);
            Utils::insertLogUser('akses menu artibut nilai');
            return view('datainduk/pegawai/atribut/detail/index', ['idatribut' => $id, 'atribut' => $atribut, 'menu' => 'atribut']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request, $id)
	{
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = ' AND idatribut = :idatribut';
        if(Utils::cekHakakses('atribut','uhm')){
            $columns = array('', 'urutan', 'nilai', 'kode');
        }else{
            $columns = array('urutan', 'nilai', 'kode');
        }
        $table = 'atributnilai';
        $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE 1=1 '.$where;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idatribut', $id);
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
            $stmt->bindValue(':idatribut', $id);
            for($i=0;$i<count($columns);$i++) {
                if($columns[$i] != '') {
                    $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                }
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalFiltered = $row['total'];
        }

        $sql = 'SELECT id,urutan,nilai,kode FROM atributnilai WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idatribut', $id);
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
                    $action .= Utils::tombolManipulasi('ubah','detail',$key['id']);
                }
                if(Utils::cekHakakses('atribut','hm')){
                    $action .= Utils::tombolManipulasi('hapus','detail',$key['id']);
                }
                $tempdata = array();
                for($i=0;$i<count($columns);$i++){
                    if($columns[$i] == '') {
                        $tempdata['action'] = '<center>'.$action.'</center>';
                    }else {
                        $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                    }
                }
                $data[] = $tempdata;
            }
        }
        return Utils::jsonDatatable($request->input('draw'),$totalData,$totalFiltered,$data);
	}

	public function create($id)
    {
        if(Utils::cekHakakses('atribut','tm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $atribut = Utils::getDataWhere($pdo,'atribut','atribut','id',$id);
            Utils::insertLogUser('akses menu tambah atribut nilai');
            return view('datainduk/pegawai/atribut/detail/create', ['idatribut' => $id, 'atribut' => $atribut, 'menu' => 'atribut']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idatribut ada
        $cekadadata = Utils::getDataWhere($pdo,'atribut','id','id',$id);
        if($cekadadata != ''){
            //cek apakah nilai kembar?
            $cekkembar = Utils::getData($pdo,'atributnilai','id','idatribut = "'.$id.'" AND nilai = "'.$request->nilai.'" LIMIT 1');
            if($cekkembar == ''){
                $cekkodekembar = '';
                if($request->kode != ''){
//                    $cekkodekembar = Utils::getDataCustomWhere($pdo,'atributnilai','id','kode = "'.$request->kode.'"');
                    $cekkodekembar = Utils::getDataWhere($pdo,'atributnilai','id','kode',$request->kode);
                }
                if($cekkodekembar == ''){
                    $sql = 'INSERT INTO atributnilai VALUES(NULL,:idatribut,:nilai,:kode,0,NOW())';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idatribut', $id);
                    $stmt->bindValue(':nilai', $request->nilai);
                    $stmt->bindValue(':kode', $request->kode != '' ? $request->kode : null);
                    $stmt->execute();

                    Utils::insertLogUser('Tambah atribut nilai "'.$request->nilai.'"');
    
                    return redirect('datainduk/pegawai/atribut/'.$id.'/detail')->with('message', trans('all.databerhasildisimpan'));
                }else{
                    return redirect('datainduk/pegawai/atribut/'.$id.'/detail/create')->with('message', trans('all.kodesudahdigunakan'));
                }
            }else{
                return redirect('datainduk/pegawai/atribut/'.$id.'/detail/create')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/pegawai/atribut/'.$id.'/detail/create')->with('message', trans('all.datatidakditemukan'));
        }
    }
    
    public function edit($idatribut, $id)
    {
        if(Utils::cekHakakses('atribut','um')){
            $atributnilai = AtributNilai::find($id);
            if(!$atributnilai){
                abort(404);
            }
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $atribut = Utils::getDataWhere($pdo,'atribut','atribut','id',$idatribut);
            Utils::insertLogUser('akses menu ubah atribut nilai');
            return view('datainduk/pegawai/atribut/detail/edit', ['atributnilai' => $atributnilai, 'atribut' => $atribut, 'menu' => 'atribut']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $idatribut, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan data idatributnilai ada
//        $cekadadata = Utils::getDataCustomWhere($pdo,'atributnilai','nilai','id = '.$id);
        $cekadadata = Utils::getDataWhere($pdo,'atributnilai','nilai','id',$id);
        if ($cekadadata != '') {
            $cekatributnilaikembar = Utils::getDataCustomWhere($pdo,'atributnilai','id','idatribut = "'.$idatribut.'" AND nilai = "'.$request->nilai.'" AND id <> '.$id);
            if($cekatributnilaikembar == ''){
                $cekkodekembar = '';
                if($request->kode != ''){
                    $cekkodekembar = Utils::getDataCustomWhere($pdo,'atributnilai','id','kode = "'.$request->kode.'" AND id != '.$id);
                }
                if($cekkodekembar == ''){
                    $sql = 'UPDATE atributnilai SET idatribut = :idatribut, nilai = :nilai, kode = :kode WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idatribut', $idatribut);
                    $stmt->bindValue(':nilai', $request->nilai);
                    $stmt->bindValue(':kode', $request->kode != '' ? $request->kode : null);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah atribut nilai "'.$cekadadata.'" => "'.$request->nilai.'"');
        
                    return redirect('datainduk/pegawai/atribut/' . $idatribut . '/detail')->with('message', trans('all.databerhasildiubah'));
                }else{
                    return redirect('datainduk/pegawai/atribut/' . $idatribut . '/detail/'.$id.'/edit')->with('message', trans('all.kodesudahdigunakan'));
                }
            }else{
                return redirect('datainduk/pegawai/atribut/' . $idatribut . '/detail/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/pegawai/atribut/' . $idatribut . '/detail/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($idatribut, $id)
    {
        if(Utils::cekHakakses('atribut','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
//            $cekadadata = Utils::getDataCustomWhere($pdo,'atributnilai','nilai','id = '.$id);
            $cekadadata = Utils::getDataWhere($pdo,'atributnilai','nilai','id',$id);
            if ($cekadadata != '') {
                AtributNilai::find($id)->delete();
                Utils::insertLogUser('Hapus atribut nilai "'.$cekadadata.'"');
                $msg = trans('all.databerhasildihapus');
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('datainduk/pegawai/atribut/' . $idatribut . '/detail')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function excel($id)
    {
        if(Utils::cekHakakses('atribut','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.atributnilai'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.urutan'))
                        ->setCellValue('B1', trans('all.nilai'))
                        ->setCellValue('C1', trans('all.kode'));

            $sql = 'SELECT
                        urutan,
                        nilai,
                        kode
                    FROM
                        atributnilai
                    WHERE
                        idatribut = :idatribut
                    ORDER BY
                      urutan, nilai';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idatribut', $id);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['urutan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['nilai']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['kode']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor atribut nilai');
            $arrWidth = array(10, 40, 10);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.atributnilai'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}
