<?php
namespace App\Http\Controllers;

use App\Lokasi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use App\Utils;

class LokasiController extends Controller
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
        if(Utils::cekHakakses('lokasi','l')){
            Utils::insertLogUser('akses menu lokasi');
            return view('datainduk/pegawai/lokasi/index', ['menu' => 'lokasi']);
        } else {
            return redirect('/');
        }
    }

    public function show(Request $request)
    {
        if(Utils::cekHakakses('lokasi','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','nama','lat','lon','jaraktoleransi','radius');
            $table = 'lokasi';
            $totalData = Utils::getDataCustomWhere($pdo,'lokasi', 'count(id)');
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

            $sql = 'SELECT id,nama,lat,lon,penentuanlokasi,jaraktoleransi,radius FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    $action = '<a title="' . trans('all.lokasi') . '" href="#" onclick="return lihatLokasi(\'' . $key['lat'] . '\',\'' . $key['lon'] . '\')"><i class="fa fa-map-marker" style="color:#ed5565"></i></a>&nbsp;&nbsp;';
                    if($key['penentuanlokasi'] == 'poligon'){
                        $action .= '<a title="' . trans('all.area') . '" href="lokasi/'.$key['id'].'/area"><i class="fa fa-map"></i></a>&nbsp;&nbsp;';
                    }
                    if(Utils::cekHakakses('lokasi','um')){
                        $action .= Utils::tombolManipulasi('ubah','lokasi',$key['id']);
                    }
                    if(Utils::cekHakakses('lokasi','hm')){
                        $action .= Utils::tombolManipulasi('hapus','lokasi',$key['id']);
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        if($columns[$i] == 'jaraktoleransi') {
                            $tempdata[$columns[$i]] = '<center>' . trans('all.'.$key[$columns[$i]]) . '</center>';
                        }elseif($columns[$i] == 'radius') {
                            $tempdata[$columns[$i]] = $key[$columns[$i]] != '' ? $key[$columns[$i]] . ' ' . trans('all.meter') : '';
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
        if(Utils::cekHakakses('lokasi','tm')){
            Utils::insertLogUser('akses menu tambah lokasi');
            return view('datainduk/pegawai/lokasi/create', ['menu' => 'lokasi']);
        } else {
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM lokasi WHERE nama=:nama LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nama', $request->nama);
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            $lokasi = new Lokasi;
            $lokasi->nama = $request->nama;
            $lokasi->lat = $request->lat;
            $lokasi->lon = $request->lon;
            $lokasi->penentuanlokasi = $request->penentuanlokasi;
            $lokasi->jaraktoleransi = $request->jaraktoleransi;
            $lokasi->radius = $request->radius;
            $lokasi->save();

            Utils::insertLogUser('Tambah lokasi "' . $request->nama . '"');

            return redirect('datainduk/pegawai/lokasi')->with('message', trans('all.databerhasildisimpan'));
        } else {
            return redirect('datainduk/pegawai/lokasi/create')->with('message', trans('all.datasudahada'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('lokasi','um')){
            $lokasi = Lokasi::find($id);
            if (!$lokasi) {
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah lokasi');
            return view('datainduk/pegawai/lokasi/edit', ['lokasi' => $lokasi, 'menu' => 'lokasi']);
        } else {
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'lokasi','nama','id',$id);
        if($cekadadata != ''){
            $sql = 'SELECT id FROM lokasi WHERE nama=:nama AND id<>:idlokasi LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':idlokasi', $id);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $lokasi = Lokasi::find($id);
                $lokasi->nama = $request->nama;
                $lokasi->lat = $request->lat;
                $lokasi->lon = $request->lon;
                $lokasi->penentuanlokasi = $request->penentuanlokasi;
                $lokasi->jaraktoleransi = $request->jaraktoleransi;
                $lokasi->radius = $request->radius;
                $lokasi->save();

                Utils::insertLogUser('Ubah lokasi "' . $cekadadata . '" => "' . $request->nama . '"');

                return redirect('datainduk/pegawai/lokasi')->with('message', trans('all.databerhasildiubah'));
            } else {
                return redirect('datainduk/pegawai/lokasi/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        } else {
            return redirect('datainduk/pegawai/lokasi/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('lokasi','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'lokasi','nama','id',$id);
            if($cekadadata != ''){
                Lokasi::find($id)->delete();
                Utils::insertLogUser('Hapus lokasi "' . $cekadadata . '"');
                return redirect('datainduk/pegawai/lokasi')->with('message', trans('all.databerhasildihapus'));
            } else {
                return redirect('datainduk/pegawai/lokasi')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function area($idlokasi){
        if(Utils::cekHakakses('lokasi','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $ceklokasi = Utils::getData($pdo,'lokasi','id','id='.$idlokasi.' AND penentuanlokasi="poligon"');
            if($ceklokasi != ''){
                $namalokasi = Utils::getDataWhere($pdo,'lokasi','nama','id',$idlokasi);
                $datapoligon = Utils::getData($pdo,'lokasipoligon','lat,lon','idlokasi='.$idlokasi);
                if($datapoligon == ''){
                    $dataarealatlng = '-5.94005,110.79068#-8.03983,114.23133#-7.92633,104.99376';
                }else{
                    $dataarealatlng = implode(', ', array_map(function ($entry) {
                        return $entry->lat.','.$entry->lon.'#';
                    }, $datapoligon));
                }
                Utils::insertLogUser('akses menu lokasi area poligon');
                return view('datainduk/pegawai/lokasi/area', ['idlokasi' => $idlokasi, 'namalokasi' => $namalokasi, 'dataarealatlng' => $dataarealatlng, 'datapoligon' => $datapoligon, 'menu' => 'lokasi']);
            }else{
                return redirect('datainduk/pegawai/lokasi');
            }
        }else{
            return redirect('/');
        }
    }

    public function submitArea(Request $request, $idlokasi){
        $msg = '';
        $latlng = $request->latlng;
        $latlndexplode = explode('#', $latlng);
        $jumlahlatlng = count($latlndexplode);
        // return $latlng;
        if($jumlahlatlng > 1){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            try {
                $pdo->beginTransaction();

                //delete latlng yg lama
                Utils::deleteData($pdo,'lokasipoligon',$idlokasi,'idlokasi');

                for ($i = 0; $i < $jumlahlatlng; $i++) {
                    $latlngsatuan = explode(',', $latlndexplode[$i]);
                    $lat = $latlngsatuan[0];
                    $lng = $latlngsatuan[1];

                    $sql1 = 'INSERT INTO lokasipoligon VALUES(NULL,:idlokasi,:lat,:lng)';
                    $stmt1 = $pdo->prepare($sql1);
                    $stmt1->bindValue(':idlokasi', $idlokasi);
                    $stmt1->bindValue(':lat', $lat);
                    $stmt1->bindValue(':lng', $lng);
                    $stmt1->execute();
                }

                Utils::insertLogUser('Set Lokasi Poligon "'.Utils::getDataWhere($pdo,'lokasi','nama','id',$idlokasi).'"');

                $pdo->commit();
                $msg = trans('all.lokasiberhasildiset');
            }catch (\Exception $e){
                $pdo->rollBack();
                $msg = $e->getMessage();
            }
        }
        return redirect("datainduk/pegawai/lokasi")->with('message', $msg);
    }

    public function excel()
    {
        if(Utils::cekHakakses('lokasi','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.lokasi'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.lat'))
                        ->setCellValue('C1', trans('all.lon'))
                        ->setCellValue('D1', trans('all.jaraktoleransi'))
                        ->setCellValue('E1', trans('all.radius'));

            $sql = 'SELECT
                        nama,
                        lat,
                        lon,
                        jaraktoleransi,
                        radius
                    FROM
                        lokasi
                     ORDER BY 
                        nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['lat']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['lon']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, trans('all.'.$row['jaraktoleransi']));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['radius'] != '' ? $row['radius'].' '.trans('all.meter') : '');

                $objPHPExcel->getActiveSheet()->getStyle('D' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $i++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor lokasi');
            $arrWidth = array(40, 20, 20, 20, 15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.lokasi'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}