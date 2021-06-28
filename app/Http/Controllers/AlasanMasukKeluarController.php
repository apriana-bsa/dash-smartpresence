<?php
namespace App\Http\Controllers;

use App\AlasanMasukKeluar;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class AlasanMasukKeluarController extends Controller
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
        if(Utils::cekHakakses('alasanmasukkeluar','l')){
            $totaldata = Utils::getTotalData(1,'alasanmasukkeluar');
            Utils::insertLogUser('akses menu alasan masuk keluar');
	        return view('datainduk/alasan/alasanmasukkeluar/index', ['totaldata' => $totaldata, 'menu' => 'alasanmasukkeluar']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        if(Utils::cekHakakses('alasanmasukkeluar','uhm')) {
            $columns = array('', 'urutan', 'alasan', 'icon', 'tampilsaat', 'tampilpadamesin', 'terhitungkerja', 'digunakan');
        }else{
            $columns = array('urutan', 'alasan', 'icon', 'tampilsaat', 'tampilpadamesin', 'terhitungkerja', 'digunakan');
        }
        $table = '(
                    SELECT
                        amk.id,
                        IFNULL(la.idalasanmasukkeluar,"") as adalogabsen,
                        amk.alasan,
                        amk.icon,
                        amk.tampilsaat,
                        amk.tampilpadamesin,
                        amk.terhitungkerja,
                        amk.urutan,
                        amk.digunakan
                    FROM
                        alasanmasukkeluar amk
                        LEFT JOIN logabsen la ON amk.id=la.idalasanmasukkeluar
                    GROUP BY
                        amk.id
                  ) x';
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

        $sql = 'SELECT * FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                if(Utils::cekHakakses('alasanmasukkeluar','um')){
                    $action .= Utils::tombolManipulasi('ubah','alasanmasukkeluar',$key['id']);
                }
                if(Utils::cekHakakses('alasanmasukkeluar','hm')){
                    $hapus = Utils::tombolManipulasi('hapus','alasanmasukkeluar',$key['id']);
                    if($key['adalogabsen'] != '') {
                        $hapus = Utils::tombolManipulasi('hapus','alasanmasukkeluar',$key['id'],trans('all.alerthapuscatatanmasukkeluar'));
                    }
                    $action .= $hapus;
                }
                $tempdata = array();
                for($i=0;$i<count($columns);$i++){
                    if($columns[$i] == '') {
                        $tempdata['action'] = '<center>'.$action.'</center>';
                    }elseif($columns[$i] == 'icon'){
                        $tempdata[$columns[$i]] = '<center><img style="background:#f8ac59;" width=40px height=40px src="'.asset("lib/icon_alasan/".$key['icon'].".png").'"></center>';
                    }elseif($columns[$i] == 'tampilsaat' || $columns[$i] == 'tampilpadamesin' || $columns[$i] == 'terhitungkerja' || $columns[$i] == 'digunakan') {
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
        if(Utils::cekHakakses('alasanmasukkeluar','tm')){
            Utils::insertLogUser('akses menu tambah alasan masuk keluar');
            return view('datainduk/alasan/alasanmasukkeluar/create', ['menu' => 'alasanmasukkeluar']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek apakah alasan kembar?
        $sql = 'SELECT id FROM alasanmasukkeluar WHERE alasan=:alasan LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':alasan', $request->alasan);
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            $alasanmasukkeluar = new AlasanMasukKeluar;
            $alasanmasukkeluar->alasan = $request->alasan;
            $alasanmasukkeluar->icon = $request->icon;
            $alasanmasukkeluar->tampilsaat = $request->tampilsaat;
            $alasanmasukkeluar->tampilpadamesin = $request->tampilpadamesin;
            $alasanmasukkeluar->terhitungkerja = $request->terhitungkerja;
            $alasanmasukkeluar->urutan = $request->urutan;
            $alasanmasukkeluar->digunakan = $request->digunakan;
            $alasanmasukkeluar->save();

            Utils::insertLogUser('Tambah alasan masuk keluar "'.$request->alasan.'"');
    
            return redirect('datainduk/alasan/alasanmasukkeluar')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/alasan/alasanmasukkeluar/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('alasanmasukkeluar','um')){
            $alasanmasukkeluar = AlasanMasukKeluar::find($id);
            if(!$alasanmasukkeluar){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah alasan masuk keluar');
            return view('datainduk/alasan/alasanmasukkeluar/edit', ['alasanmasukkeluar' => $alasanmasukkeluar, 'menu' => 'alasanmasukkeluar']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idalasanmasukkeluar ada
        $cekadadata = Utils::getDataWhere($pdo,'alasanmasukkeluar','alasan','id',$id);
        if ($cekadadata != '') {
            //cek data kembar?
            $cekkembar = Utils::getData($pdo,'alasanmasukkeluar','id','alasan = "'.$request->alasan.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $alasanmasukkeluar = AlasanMasukKeluar::find($id);
                $alasanmasukkeluar->alasan = $request->alasan;
                $alasanmasukkeluar->icon = $request->icon;
                $alasanmasukkeluar->tampilsaat = $request->tampilsaat;
                $alasanmasukkeluar->tampilpadamesin = $request->tampilpadamesin;
                $alasanmasukkeluar->terhitungkerja = $request->terhitungkerja;
                $alasanmasukkeluar->urutan = $request->urutan;
                $alasanmasukkeluar->digunakan = $request->digunakan;
                $alasanmasukkeluar->save();

                Utils::insertLogUser('Ubah alasan masuk keluar "'.$cekadadata.'" => "'.$request->alasan.'"');
    
                return redirect('datainduk/alasan/alasanmasukkeluar')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/alasan/alasanmasukkeluar/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/alasan/alasanmasukkeluar/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('alasanmasukkeluar','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $ceklogabsen = Utils::getDataWhere($pdo,'logabsen','id','idalasanmasukkeluar',$id);
            if($ceklogabsen == '') {
                //pastikan idalasanmasukkeluar ada
                $cekadadata = Utils::getDataWhere($pdo,'alasanmasukkeluar','alasan','id',$id);
                if ($cekadadata != '') {
                    AlasanMasukKeluar::find($id)->delete();
                    // update idalasanmasukkeluar di logabsen menjadi null
                    $sql = 'UPDATE logabsen SET idalasanmasukkeluar = NULL WHERE idalasanmasukkeluar = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Hapus alasan masuk keluar "' . $cekadadata . '"');
                    $msg = trans('all.databerhasildihapus');
                } else {
                    $msg = trans('all.datatidakditemukan');
                }
            }else{
                $msg = trans('all.datasudahdigunakan');
            }
            return redirect('datainduk/alasan/alasanmasukkeluar')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('alasanmasukkeluar','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.alasanmasukkeluar'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.urutan'))
                        ->setCellValue('B1', trans('all.alasan'))
                        ->setCellValue('C1', trans('all.icon'))
                        ->setCellValue('D1', trans('all.tampilsaat'))
                        ->setCellValue('E1', trans('all.tampilpadamesin'))
                        ->setCellValue('F1', trans('all.terhitungkerja'))
                        ->setCellValue('G1', trans('all.digunakan'));

            $sql = 'SELECT
                        urutan,
                        alasan,
                        icon,
                        IF(tampilsaat="m","' . trans("all.masuk") . '",IF(tampilsaat="k","' . trans("all.keluar") . '","' . trans("all.masukkeluar") . '")) as tampilsaat,
                        IF(tampilpadamesin="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as tampilpadamesin,
                        IF(terhitungkerja="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as terhitungkerja,
                        IF(digunakan="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as digunakan
                    FROM
                        alasanmasukkeluar
                    ORDER BY
                        urutan';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['urutan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['alasan']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['icon']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['tampilsaat']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['tampilpadamesin']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['terhitungkerja']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['digunakan']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor alasan masuk keluar');
            $arrWidth = array(10, 30, 17, 14, 15, 15, 12);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.alasanmasukkeluar'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}