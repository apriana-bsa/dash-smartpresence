<?php
namespace App\Http\Controllers;

use App\AlasanTidakMasuk;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class AlasanTidakMasukController extends Controller
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
        if(Utils::cekHakakses('alasantidakmasuk','l')){
            $totaldata = Utils::getTotalData(1,'alasantidakmasuk');
            Utils::insertLogUser('akses menu alasan tidak masuk');
            return view('datainduk/alasan/alasantidakmasuk/index', ['totaldata' => $totaldata, 'menu' => 'alasantidakmasuk']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        if(Utils::cekHakakses('alasantidakmasuk','uhm')){
            $columns = array('','urutan','alasan','kategori','hitunguangmakan','digunakan');
        }else{
            $columns = array('urutan','alasan','kategori','hitunguangmakan','digunakan');
        }
        $table = 'alasantidakmasuk';
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

        $sql = 'SELECT id,urutan,alasan,kategori,hitunguangmakan,digunakan FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                if(Utils::cekHakakses('alasantidakmasuk','um')){
                    $action .= Utils::tombolManipulasi('ubah','alasantidakmasuk',$key['id']);
                }
                if(Utils::cekHakakses('alasantidakmasuk','hm')){
                    $action .= Utils::tombolManipulasi('hapus','alasantidakmasuk', $key['id']);
                }
                $tempdata = array();
                for($i=0;$i<count($columns);$i++){
                    if($columns[$i] == '') {
                        $tempdata['action'] = '<center>'.$action.'</center>';
                    }elseif($columns[$i] == 'kategori' || $columns[$i] == 'hitunguangmakan' || $columns[$i] == 'digunakan') {
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

	public function create()
    {
        if(Utils::cekHakakses('alasantidakmasuk','tm')){
            Utils::insertLogUser('akses menu tambah alasan tidak masuk');
            return view('datainduk/alasan/alasantidakmasuk/create', ['menu' => 'alasantidakmasuk']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek apakah alasan kembar?
        $cekadadata = Utils::getDataWhere($pdo,'alasantidakmasuk','id','alasan',$request->alasan);
        if($cekadadata == ''){
            $alasantidakmasuk = new AlasanTidakMasuk;
            $alasantidakmasuk->alasan = $request->alasan;
            $alasantidakmasuk->kategori = $request->kategori;
            $alasantidakmasuk->hitunguangmakan = $request->hitunguangmakan;
            $alasantidakmasuk->urutan = $request->urutan;
            $alasantidakmasuk->digunakan = $request->digunakan;
            $alasantidakmasuk->save();

            Utils::insertLogUser('Tambah alasan tidak masuk "'.$request->alasan.'"');
    
            return redirect('datainduk/alasan/alasantidakmasuk')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/alasan/alasantidakmasuk/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('alasantidakmasuk','um')){
            $alasantidakmasuk = AlasanTidakMasuk::find($id);
            if(!$alasantidakmasuk){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah alasan tidak masuk');
            return view('datainduk/alasan/alasantidakmasuk/edit', ['alasantidakmasuk' => $alasantidakmasuk, 'menu' => 'alasantidakmasuk']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idalasantidakmasuk ada
        $cekadadata = Utils::getDataWhere($pdo,'alasantidakmasuk','alasan','id',$id);
        if($cekadadata != ''){
            //cek apakah kembar?
            $cekkembar = Utils::getData($pdo,'alasantidakmasuk','id','alasan = "'.$request->alasan.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $alasantidakmasuk = AlasanTidakMasuk::find($id);
                $alasantidakmasuk->alasan = $request->alasan;
                $alasantidakmasuk->kategori = $request->kategori;
                $alasantidakmasuk->hitunguangmakan = $request->hitunguangmakan;
                $alasantidakmasuk->urutan = $request->urutan;
                $alasantidakmasuk->digunakan = $request->digunakan;
                $alasantidakmasuk->save();

                Utils::insertLogUser('Ubah alasan tidak masuk "'.$cekadadata.'" => "'.$request->alasan.'"');
    
                return redirect('datainduk/alasan/alasantidakmasuk')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/alasan/alasantidakmasuk/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/alasan/alasantidakmasuk/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('alasantidakmasuk','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'alasantidakmasuk','alasan','id',$id);
            if($cekadadata != ''){
                AlasanTidakMasuk::find($id)->delete();
                Utils::insertLogUser('Hapus alasan tidak masuk "'.$cekadadata.'"');
                return redirect('datainduk/alasan/alasantidakmasuk')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/alasan/alasantidakmasuk')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('alasantidakmasuk','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.alasantidakmasuk'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.urutan'))
                        ->setCellValue('B1', trans('all.alasan'))
                        ->setCellValue('C1', trans('all.kategori'))
                        ->setCellValue('D1', trans('all.hitunguangmakan'))
                        ->setCellValue('E1', trans('all.digunakan'));

            $sql = 'SELECT
                        urutan,
                        alasan,
                        IF(kategori="s","' . trans("all.sakit") . '",IF(kategori="i","' . trans("all.ijin") . '",IF(kategori="d","' . trans("all.dispensasi") . '","' . trans("all.tidakmasuk") . '"))) as kategori,
                        IF(hitunguangmakan="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as hitunguangmakan,
                        IF(digunakan="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as digunakan
                    FROM
                        alasantidakmasuk
                    ORDER BY
                        urutan';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['urutan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['alasan']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['kategori']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['hitunguangmakan']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['digunakan']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor alasan tidak masuk');
            $arrWidth = array(8, 30, 15, 17, 12);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.alasantidakmasuk'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}