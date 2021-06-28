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

class LaporanKelompokAtributController extends Controller
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

	public function getindex($idlaporankelompok)
	{
        if(Utils::cekHakakses('laporancustom','l')){
            Utils::insertLogUser('akses menu laporan kelompok atribut');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $kelompok = Utils::getDataWhere($pdo,'laporan_kelompok','nama','id',$idlaporankelompok);
	        return view('laporan/custom/kelompokatribut/index', ['idlaporankelompok' => $idlaporankelompok, 'kelompok' => $kelompok, 'menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request, $idlaporankelompok)
	{
        if(Utils::cekHakakses('laporancustom','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','atribut','atributnilai');
            $table = '(SELECT pka.id, a.atribut, an.nilai as atributnilai,pka.idlaporan_kelompok FROM laporan_kelompok_atribut pka, atributnilai an, atribut a WHERE pka.idatributnilai=an.id AND an.idatribut=a.id) x';
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idlaporan_kelompok = :idlaporan_kelompok '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idlaporan_kelompok', $idlaporankelompok);
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
                $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idlaporan_kelompok = :idlaporan_kelompok '.$where;
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idlaporan_kelompok', $idlaporankelompok);
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                    }
                }
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalFiltered = $row['total'];
            }

            $sql = 'SELECT * FROM '.$table.' WHERE idlaporan_kelompok = :idlaporan_kelompok ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idlaporan_kelompok', $idlaporankelompok);
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
                        $action .= Utils::tombolManipulasi('ubah','atribut',$key['id']);
                    }
                    if(Utils::cekHakakses('laporancustom','hm')){
                        $action .= Utils::tombolManipulasi('hapus','atribut',$key['id']);
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

	public function create($idlaporankelompok)
    {
        if(Utils::cekHakakses('laporancustom','tm')){
            Utils::insertLogUser('akses menu tambah laporan kelompok atribut');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $dataatribut = Utils::getData($pdo,'atribut','id,atribut as nama','','atribut');
            return view('laporan/custom/kelompokatribut/create', ['dataatribut' => $dataatribut, 'idlaporankelompok' => $idlaporankelompok, 'menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request, $idlaporankelompok)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM laporan_kelompok_atribut WHERE idatributnilai = :idatributnilai';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idatributnilai', $request->atributnilai);
        $stmt->execute();
        if($stmt->rowCount() == 0){
            $sql = 'INSERT INTO laporan_kelompok_atribut VALUES(NULL,:idlaporan_kelompok,:idatributnilai)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idlaporan_kelompok', $idlaporankelompok);
            $stmt->bindValue(':idatributnilai', $request->atributnilai);
            $stmt->execute();

            Utils::insertLogUser('Tambah laporan kelompok "'.Utils::getDataWhere($pdo,'atributnilai','nilai','id',$request->atributnilai).'"');

            return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/atribut')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/atribut/create')->with('message', trans('all.datasudahada'));
        }
    }

    public function edit($idlaporankelompok, $id)
    {
        if(Utils::cekHakakses('laporancustom','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM laporan_kelompok_atribut WHERE id = :id';
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
            Utils::insertLogUser('akses menu ubah laporan kelompok atribut');
            return view('laporan/custom/kelompokatribut/edit', ['data' => $data, 'idatributselected' => $idatributselected, 'idlaporankelompok' => $idlaporankelompok, 'dataatribut' => $dataatribut, 'menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $idlaporankelompok, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'laporan_kelompok_atribut','idatributnilai','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'laporan_kelompok_atribut','id','idatributnilai = "'.$request->atributnilai.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $sql = 'UPDATE laporan_kelompok_atribut SET idatributnilai = :idatributnilai WHERE id = :id LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idatributnilai', $request->atributnilai);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::insertLogUser('Ubah laporan kelompok "'.Utils::getDataWhere($pdo,'atributnilai','nilai','id',$cekadadata).'" => "'.Utils::getDataWhere($pdo,'atributnilai','nilai','id',$request->atributnilai).'"');

                return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/atribut')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/atribut/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/atribut/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($idlaporankelompok, $id)
    {
        if(Utils::cekHakakses('laporancustom','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'laporan_kelompok_atribut','idatributnilai','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'laporan_kelompok_atribut',$id);
                Utils::insertLogUser('Hapus laporan kelompok atribut "'.Utils::getDataWhere($pdo,'atributnilai','nilai','id',$cekadadata).'"');
                return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/atribut')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/atribut')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel($idlaporankelompok)
    {
        if(Utils::cekHakakses('laporancustom','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.laporankelompokatribut'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.atribut'))
                        ->setCellValue('B1', trans('all.atributnilai'));

            $sql = 'SELECT
                        pka.id,
                        a.atribut,
                        an.nilai as atributnilai
                    FROM
                        laporan_kelompok_atribut pka,
                        atributnilai an,
                        atribut a
                    WHERE
                      pka.idatributnilai=an.id AND
                      an.idatribut=a.id AND
                      pka.idlaporan_kelompok = '.$idlaporankelompok.'
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
            Utils::insertLogUser('Ekspor laporan kelompok atribut');
            $arrWidth = array(50, 50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.laporankelompokatribut') . '_'.Utils::getDataWhere($pdo,'laporan_kelompok','nama','id',$idlaporankelompok));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}