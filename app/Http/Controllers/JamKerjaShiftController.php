<?php
namespace App\Http\Controllers;

use App\JamKerjaShift;
use App\JamKerjaShiftDetail;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class JamKerjaShiftController extends Controller
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
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama FROM jamkerja WHERE id = :idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $jamkerja = $row->nama;
            Utils::insertLogUser('akses menu jam kerja shift');
            return view('datainduk/absensi/jamkerja/shift/index', ['idjamkerja' => $id, 'jamkerja' => $jamkerja, 'menu' => 'jamkerja']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request, $id)
	{
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','urutan','namashift','jenis','keterangan','kode','digunakan');
            $table = '(
                        SELECT
                            js.id,
                            js.idjamkerja,
                            js.namashift,
                            jsj.nama as jenis,
                            js.kode,
                            js.digunakan,
                            js.urutan,
                            IF(js._1_masuk = "y" AND js._2_masuk = "y" AND js._3_masuk = "y"  AND js._4_masuk = "y" AND js._5_masuk = "y" AND js._6_masuk = "y" AND js._7_masuk = "y",
                                "'.trans('all.berlakusetiaphari').'",
                                CONCAT(
                                    if(js._1_masuk = "y", "<span class=\"label label-success\" title=\"'.trans('all.minggu').'\">'.trans('all.singkatminggu').'</span>  ", "<span class=\"label\" title=\"'.trans('all.minggu').'\">'.trans('all.singkatminggu').'</span>  " ),
                                    if(js._2_masuk = "y", "<span class=\"label label-success\" title=\"'.trans('all.senin').'\">'.trans('all.singkatsenin').'</span>  ", "<span class=\"label\" title=\"'.trans('all.senin').'\">'.trans('all.singkatsenin').'</span>  " ),
                                    if(js._3_masuk = "y", "<span class=\"label label-success\" title=\"'.trans('all.selasa').'\">'.trans('all.singkatselasa').'</span>  ", "<span class=\"label\" title=\"'.trans('all.selasa').'\">'.trans('all.singkatselasa').'</span>  " ),
                                    if(js._4_masuk = "y", "<span class=\"label label-success\" title=\"'.trans('all.rabu').'\">'.trans('all.singkatrabu').'</span>  ", "<span class=\"label\" title=\"'.trans('all.rabu').'\">'.trans('all.singkatrabu').'</span>  " ),
                                    if(js._5_masuk = "y", "<span class=\"label label-success\" title=\"'.trans('all.kamis').'\">'.trans('all.singkatkamis').'</span>  ", "<span class=\"label\" title=\"'.trans('all.kamis').'\">'.trans('all.singkatkamis').'</span>  " ),
                                    if(js._6_masuk = "y", "<span class=\"label label-success\" title=\"'.trans('all.jumat').'\">'.trans('all.singkatjumat').'</span>  ", "<span class=\"label\" title=\"'.trans('all.jumat').'\">'.trans('all.singkatjumat').'</span>  " ),
                                    if(js._7_masuk = "y", "<span class=\"label label-success\" title=\"'.trans('all.sabtu').'\">'.trans('all.singkatsabtu').'</span>", "<span class=\"label\" title=\"'.trans('all.sabtu').'\">'.trans('all.singkatsabtu').'</span>")
                                )
                            ) as keterangan
                        FROM
                            jamkerjashift js
                            LEFT JOIN jamkerjashift_jenis jsj ON js.idjenis=jsj.id
                    ) x';
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idjamkerja = :idjamkerja '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $id);
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
                $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idjamkerja = :idjamkerja '.$where;
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idjamkerja', $id);
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                    }
                }
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalFiltered = $row['total'];
            }

            $sql = 'SELECT * FROM '.$table.' WHERE idjamkerja = :idjamkerja ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $id);
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
                    $action = Utils::tombolManipulasi('detail','shift',$key['id']);
                    if(Utils::cekHakakses('jamkerja','um')){
                        $action .= Utils::tombolManipulasi('ubah','shift',$key['id']);
                    }
                    if(Utils::cekHakakses('jamkerja','hm')){
                        $action .= Utils::tombolManipulasi('hapus','shift',$key['id']);
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        if($columns[$i] == 'digunakan') {
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]]);
                        }else if($columns[$i] == 'keterangan') {
                            $tempdata[$columns[$i]] = $key[$columns[$i]];
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

	public function create($id)
    {
        if(Utils::cekHakakses('jamkerja','tm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = "SELECT nama FROM jamkerja WHERE id = :idjamkerja";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $jamkerja = $row->nama;

            $jenis = Utils::getData($pdo,'jamkerjashift_jenis','id,nama','digunakan="y"','nama');
            Utils::insertLogUser('akses menu tambah jam kerja shift');
            return view('datainduk/absensi/jamkerja/shift/create', ['idjamkerja' => $id, 'jamkerja' => $jamkerja, 'jenis' => $jenis, 'menu' => 'jamkerja']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $pdo->beginTransaction();
        //pastikan idjamkerja ada
        $sql = 'SELECT id FROM jamkerja WHERE jenis="shift" AND id=:idjamkerja LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idjamkerja', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            //cek apakah namashift kembar?
            $sql = 'SELECT id FROM jamkerjashift WHERE idjamkerja=:idjamkerja AND namashift=:namashift LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $id);
            $stmt->bindValue(':namashift', $request->nama);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                //cek apakah kode kembar
                $sql = 'SELECT id FROM jamkerjashift WHERE idjamkerja=:idjamkerja AND kode=:kode LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idjamkerja', $id);
                $stmt->bindValue(':kode', $request->kode);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    return redirect('datainduk/absensi/jamkerja/' . $id . '/shift/create')->with('message', trans('all.kodetelahdigunakan'));
                }

                try
                {
                    $jamkerjashift = new JamKerjaShift;
                    $jamkerjashift->idjamkerja = $id;
                    $jamkerjashift->namashift = $request->nama;
                    $jamkerjashift->kode = $request->kode;
                    $jamkerjashift->idjenis = $request->jenis == '' ? NULL : $request->jenis;
                    if(isset($request->setiaphari)){
                        $jamkerjashift->_0_masuk = isset($request->harilibur) ? 'y' : 't';
                        $jamkerjashift->_1_masuk = 'y';
                        $jamkerjashift->_2_masuk = 'y';
                        $jamkerjashift->_3_masuk = 'y';
                        $jamkerjashift->_4_masuk = 'y';
                        $jamkerjashift->_5_masuk = 'y';
                        $jamkerjashift->_6_masuk = 'y';
                        $jamkerjashift->_7_masuk = 'y';
                    }else {
                        $jamkerjashift->_0_masuk = isset($request->harilibur) ? 'y' : 't';
                        $jamkerjashift->_1_masuk = isset($request->minggu) ? 'y' : 't';
                        $jamkerjashift->_2_masuk = isset($request->senin) ? 'y' : 't';
                        $jamkerjashift->_3_masuk = isset($request->selasa) ? 'y' : 't';
                        $jamkerjashift->_4_masuk = isset($request->rabu) ? 'y' : 't';
                        $jamkerjashift->_5_masuk = isset($request->kamis) ? 'y' : 't';
                        $jamkerjashift->_6_masuk = isset($request->jumat) ? 'y' : 't';
                        $jamkerjashift->_7_masuk = isset($request->sabtu) ? 'y' : 't';
                    }
                    $jamkerjashift->digunakan = $request->digunakan;
                    $jamkerjashift->urutan = $request->urutan;
                    $jamkerjashift->inserted = date('Y-m-d H:i:s');
                    $jamkerjashift->save();

                    if ($request->berlakumulai != "") {
                        for ($i = 0; $i < count($request->berlakumulai); $i++) {
                            $jamkerjashiftdetail = new JamKerjaShiftDetail;
                            $jamkerjashiftdetail->idjamkerjashift = $jamkerjashift->id;
                            $jamkerjashiftdetail->berlakumulai = date('Y-m-d', strtotime(str_replace('/', '-', $request->berlakumulai[$i])));
                            $jamkerjashiftdetail->jammasuk = $request->jammasuk[$i];
                            $jamkerjashiftdetail->jampulang = $request->jampulang[$i];
                            $jamkerjashiftdetail->jamistirahatmulai = $request->istirahatmulai[$i];
                            $jamkerjashiftdetail->jamistirahatselesai = $request->istirahatselesai[$i];
                            $jamkerjashiftdetail->inserted = date('Y-m-d H:i:s');
                            $jamkerjashiftdetail->save();
                        }
                    }

                    Utils::insertLogUser('Tambah jam kerja shift "'.$request->nama.'"');

                    $pdo->commit();

                    return redirect('datainduk/absensi/jamkerja/' . $id . '/shift')->with('message', trans('all.databerhasildisimpan'));
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return redirect('datainduk/absensi/jamkerja/' . $id . '/shift/create')->with('message', trans('all.terjadigangguan'));
                }
            }else{
                return redirect('datainduk/absensi/jamkerja/' . $id . '/shift/create')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/absensi/jamkerja/' . $id . '/shift/create')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function edit($idjamkerja, $id)
    {
        if(Utils::cekHakakses('jamkerja','um')){
            $jamkerjashift = JamKerjaShift::find($id);

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama FROM jamkerja WHERE id = :idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $idjamkerja);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $jamkerja = $row->nama;

            $jenis = Utils::getData($pdo,'jamkerjashift_jenis','id,nama','digunakan="y"','nama');

            if(!$jamkerjashift){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah jam kerja shift');
            return view('datainduk/absensi/jamkerja/shift/edit', ['jamkerjashift' => $jamkerjashift, 'jenis' => $jenis, 'jamkerja' => $jamkerja, 'menu' => 'jamkerja']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $idjamkerja, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT idjamkerja,namashift FROM jamkerjashift WHERE id=:idjamkerjashift LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idjamkerjashift', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //cek apakah berlakumulai kembar?
            $sql = 'SELECT id FROM jamkerjashift WHERE idjamkerja=:idjamkerja AND namashift=:namashift AND id<>:idjamkerjashift LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $idjamkerja);
            $stmt->bindValue(':namashift', $request->nama);
            $stmt->bindValue(':idjamkerjashift', $id);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                //cek apakah kode kembar
                $sql = 'SELECT id FROM jamkerjashift WHERE idjamkerja=:idjamkerja AND kode=:kode AND id != :id LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idjamkerja', $idjamkerja);
                $stmt->bindValue(':kode', $request->kode);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/shift/'.$id.'/edit')->with('message', trans('all.kodetelahdigunakan'));
                }

                $jamkerjashift = JamKerjaShift::find($id);
                $jamkerjashift->idjamkerja = $idjamkerja;
                $jamkerjashift->namashift = $request->nama;
                $jamkerjashift->kode = $request->kode;
                $jamkerjashift->idjenis = $request->jenis == '' ? NULL : $request->jenis;
                if(isset($request->setiaphari)){
                    $jamkerjashift->_0_masuk = isset($request->harilibur) ? 'y' : 't';
                    $jamkerjashift->_1_masuk = 'y';
                    $jamkerjashift->_2_masuk = 'y';
                    $jamkerjashift->_3_masuk = 'y';
                    $jamkerjashift->_4_masuk = 'y';
                    $jamkerjashift->_5_masuk = 'y';
                    $jamkerjashift->_6_masuk = 'y';
                    $jamkerjashift->_7_masuk = 'y';
                }else {
                    $jamkerjashift->_0_masuk = isset($request->harilibur) ? 'y' : 't';
                    $jamkerjashift->_1_masuk = isset($request->minggu) ? 'y' : 't';
                    $jamkerjashift->_2_masuk = isset($request->senin) ? 'y' : 't';
                    $jamkerjashift->_3_masuk = isset($request->selasa) ? 'y' : 't';
                    $jamkerjashift->_4_masuk = isset($request->rabu) ? 'y' : 't';
                    $jamkerjashift->_5_masuk = isset($request->kamis) ? 'y' : 't';
                    $jamkerjashift->_6_masuk = isset($request->jumat) ? 'y' : 't';
                    $jamkerjashift->_7_masuk = isset($request->sabtu) ? 'y' : 't';
                }
                $jamkerjashift->digunakan = $request->digunakan;
                $jamkerjashift->urutan = $request->urutan;
                $jamkerjashift->save();

                Utils::insertLogUser('Ubah jam kerja shift "'.$row['namashift'].'" => "'.$request->nama.'"');

                return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/shift')->with('message', trans('all.databerhasildiubah'));
            }else{
                $msg = trans('all.datasudahada');
            }
        }else{
            $msg = trans('all.datatidakditemukan');
        }
        return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/shift/'.$id.'/edit')->with('message', $msg);
    }

    public function destroy($idjamkerja, $id)
    {
        if(Utils::cekHakakses('jamkerja','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'jamkerjashift','namashift','id',$id);
            if($cekadadata != ''){
                //cek apakah sudah digunakan
                $sql = 'SELECT id FROM jadwalshift where idjamkerjashift IN (SELECT id FROM jamkerjashift WHERE id=:id) LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                if($stmt->rowCount() == 0) {
                    JamKerjaShift::find($id)->delete();
                    Utils::insertLogUser('Hapus jam kerja shift "' . $cekadadata . '"');
                    return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/shift')->with('message', trans('all.databerhasildihapus'));
                }else{
                    return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/shift')->with('message', trans('all.datasudahadarelasidenganjadwalshift'));
                }
            }else{
                return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/shift')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel($id)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.shift'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.urutan'))
                        ->setCellValue('B1', trans('all.nama'))
                        ->setCellValue('C1', trans('all.jenis'))
                        ->setCellValue('D1', trans('all.keterangan'))
                        ->setCellValue('E1', trans('all.kode'))
                        ->setCellValue('F1', trans('all.digunakan'));

            $sql = 'SELECT
                        js.urutan,
                        js.namashift as nama,
                        jsj.nama as jenis,
                        js.kode,
                        IF(js.digunakan="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as digunakan,
                        IF(js._1_masuk = "y" AND js._2_masuk = "y" AND js._3_masuk = "y" AND js._4_masuk = "y" AND js._5_masuk = "y" AND js._6_masuk = "y" AND js._7_masuk = "y",
                            "'.trans('all.berlakusetiaphari').'",
                            CONCAT(
                                if(js._1_masuk = "y", "'.trans('all.minggu').'  ", ""),
                                if(js._2_masuk = "y", "'.trans('all.senin').'  ", ""),
                                if(js._3_masuk = "y", "'.trans('all.selasa').'  ", ""),
                                if(js._4_masuk = "y", "'.trans('all.rabu').'  ", ""),
                                if(js._5_masuk = "y", "'.trans('all.kamis').'  ", ""),
                                if(js._6_masuk = "y", "'.trans('all.jumat').'  ", ""),
                                if(js._7_masuk = "y", "'.trans('all.sabtu').'", "")
                            )
                        )as keterangan
                    FROM
                        jamkerjashift js
                        LEFT JOIN jamkerjashift_jenis jsj ON js.idjenis=jsj.id
                    WHERE
                        js.idjamkerja = :id
                    ORDER BY
                        js.urutan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['urutan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['jenis']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['keterangan']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['kode']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['digunakan']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor jam kerja shift');
            $arrWidth = array(12, 40, 40, 100, 15, 11);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.shift'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}
