<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use App\Utils;

class LaporanKelompokController extends Controller
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
            $totaldata = Utils::getTotalData(1,'laporan_kelompok');
            Utils::insertLogUser('akses menu laporan kelompok');
	        return view('laporan/custom/kelompok/index', ['totaldata' => $totaldata, 'menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('laporancustom','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','nama','jenis');
            $totalData = Utils::getDataCustomWhere($pdo,'laporan_kelompok', 'count(id)');
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $columns[$request->input('order.0.column')];
            $orderAction = $request->input('order.0.dir');
            $orderBy = $orderColumn.' '.$orderAction;

            if(!empty($request->input('search.value'))){
                $search = $request->input('search.value');
                $where .= Utils::searchDatatableQuery($columns);
                $sql = 'SELECT COUNT(id) as total FROM laporan_kelompok WHERE 1=1 '.$where;
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

            $sql = 'SELECT id,nama,jenis FROM laporan_kelompok WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    $action = '<a title="' . trans('all.detail') . '" href="kelompok/' . $key['id'] . '/komponenmaster"><i class="fa fa-pencil-square" style="color:#A2A2A2"></i></a>&nbsp;&nbsp;';
                    if(Utils::cekHakakses('laporancustom','um')){
                        $action .= Utils::tombolManipulasi('ubah','kelompok',$key['id']);
                    }
                    if(Utils::cekHakakses('laporancustom','hm')){
                        $action .= Utils::tombolManipulasi('hapus','kelompok',$key['id']);
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        if($columns[$i] == 'jenis') {
                            $tempdata[$columns[$i]] = '<center>'.trans('all.'.$key[$columns[$i]]).'</center>';
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
        if(Utils::cekHakakses('laporancustom','tm')){
            Utils::insertLogUser('akses menu tambah laporan_kelompok');
            return view('laporan/custom/kelompok/create', ['menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM laporan_kelompok WHERE nama = :nama';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nama', $request->nama);
        $stmt->execute();
        if($stmt->rowCount() == 0){
            $sql = 'INSERT INTO laporan_kelompok VALUES(NULL,:nama,:jenis,"","",NOW(),NULL)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':jenis', $request->jenis);
            $stmt->execute();

//            $idlaporankelompok = $pdo->lastInsertId();
//            //simpan template laporan posting jika ada
//            if ($request->hasFile('templatelaporanposting')) {
//                $templatelaporan = $request->file('templatelaporanposting');
//                if($templatelaporan->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $templatelaporan->getMimeType() == 'application/vnd.ms-excel'){
//                    $path = Session::get('folderroot_perusahaan') . '/laporan/';
//                    if (!file_exists($path)) {
//                        mkdir($path, 0777, true);
//                    }
//
//                    //simpan file
//                    $format = $templatelaporan->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
//                    $name = $idlaporankelompok.'_templatelaporanposting'.date('YmdHis').$format;
//                    move_uploaded_file($templatelaporan, $path.$name);
//
//                    //update laporan_pengaturan
//                    $sql = 'UPDATE laporan_kelompok SET template_laporanposting = :template_laporanposting WHERE id = :idlaporankelompok';
//                    $stmt = $pdo->prepare($sql);
//                    $stmt->bindValue(':template_laporanposting', $name);
//                    $stmt->bindValue(':idlaporankelompok', $idlaporankelompok);
//                    $stmt->execute();
//                }
//            }

            //simpan template slip gaji jika ada
//            if ($request->hasFile('templateslipgaji')) {
//                $templatelaporan = $request->file('templateslipgaji');
//                if($templatelaporan->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $templatelaporan->getMimeType() == 'application/vnd.ms-excel'){
//                    $path = Session::get('folderroot_perusahaan') . '/laporan/';
//                    if (!file_exists($path)) {
//                        mkdir($path, 0777, true);
//                    }
//
//                    //simpan file
//                    $format = $templatelaporan->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
//                    $name = $idlaporankelompok.'_templateslipgaji'.date('YmdHis').$format;
//                    move_uploaded_file($templatelaporan, $path.$name);
//
//                    //update laporan_pengaturan
//                    $sql = 'UPDATE laporan_kelompok SET template_slipgaji = :template_slipgaji WHERE id = :idlaporankelompok';
//                    $stmt = $pdo->prepare($sql);
//                    $stmt->bindValue(':template_slipgaji', $name);
//                    $stmt->bindValue(':idlaporankelompok', $idlaporankelompok);
//                    $stmt->execute();
//                }
//            }

            Utils::insertLogUser('Tambah laporan kelompok "'.$request->nama.'"');
    
            return redirect('laporan/custom/kelompok')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('laporan/custom/kelompok/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('laporancustom','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM laporan_kelompok WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah laporan_kelompok');
            return view('laporan/custom/kelompok/edit', ['data' => $data, 'menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'laporan_kelompok','nama','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'laporan_kelompok','id','nama = "'.$request->nama.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $sql = 'UPDATE laporan_kelompok SET nama = :nama, jenis = :jenis WHERE id = :id LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':jenis', $request->jenis);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                //simpan template laporan posting jika ada
//                if ($request->hasFile('templatelaporanposting')) {
//                    $templatelaporan = $request->file('templatelaporanposting');
//                    if($templatelaporan->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $templatelaporan->getMimeType() == 'application/vnd.ms-excel'){
//                        $path = Session::get('folderroot_perusahaan') . '/laporan/';
//                        if (!file_exists($path)) {
//                            mkdir($path, 0777, true);
//                        }
//
//                        //hapus yang lama jika ada
//                        $filelama = Utils::getDataWhere($pdo,'laporan_kelompok','template_laporanposting','id',$id);
//                        if ($filelama != '' && file_exists($path.$filelama)) {
//                            unlink($path.$filelama);
//                        }
//
//                        //simpan file
//                        $format = $templatelaporan->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
//                        $name = $id.'_templatelaporanposting'.date('YmdHis').$format;
//                        move_uploaded_file($templatelaporan, $path.$name);
//
//                        //update laporan_pengaturan
//                        $sql = 'UPDATE laporan_kelompok SET template_laporanposting = :template_laporanposting WHERE id = :idlaporankelompok';
//                        $stmt = $pdo->prepare($sql);
//                        $stmt->bindValue(':template_laporanposting', $name);
//                        $stmt->bindValue(':idlaporankelompok', $id);
//                        $stmt->execute();
//                    }
//                }

                //simpan template slip gaji jika ada
//                if ($request->hasFile('templateslipgaji')) {
//                    $templatelaporan = $request->file('templateslipgaji');
//                    if($templatelaporan->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $templatelaporan->getMimeType() == 'application/vnd.ms-excel'){
//                        $path = Session::get('folderroot_perusahaan') . '/laporan/';
//                        if (!file_exists($path)) {
//                            mkdir($path, 0777, true);
//                        }
//
//                        //hapus yang lama jika ada
//                        $filelama = Utils::getDataWhere($pdo,'laporan_kelompok','template_slipgaji','id',$id);
//                        if ($filelama != '' && file_exists($path.$filelama)) {
//                            unlink($path.$filelama);
//                        }
//
//                        //simpan file
//                        $format = $templatelaporan->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
//                        $name = $id.'_templateslipgaji'.date('YmdHis').$format;
//                        move_uploaded_file($templatelaporan, $path.$name);
//
//                        //update laporan_pengaturan
//                        $sql = 'UPDATE laporan_kelompok SET template_slipgaji = :template_slipgaji WHERE id = :idlaporankelompok';
//                        $stmt = $pdo->prepare($sql);
//                        $stmt->bindValue(':template_slipgaji', $name);
//                        $stmt->bindValue(':idlaporankelompok', $id);
//                        $stmt->execute();
//                    }
//                }

                Utils::insertLogUser('Ubah laporan kelompok "'.$cekadadata.'" => "'.$request->nama.'"');
    
                return redirect('laporan/custom/kelompok')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('laporan/custom/kelompok/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('laporan/custom/kelompok/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('laporancustom','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'laporan_kelompok','nama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'laporan_komponen_master',$id,'idlaporan_kelompok');
                Utils::deleteData($pdo,'laporan_kelompok',$id);
                Utils::insertLogUser('Hapus laporan kelompok "'.$cekadadata.'"');
                return redirect('laporan/custom/kelompok')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('laporan/custom/kelompok')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function hapusTemplate($id, $jenis){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $path = Session::get('folderroot_perusahaan') . '/laporan/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        //hapus yang lama jika ada
        $filelama = Utils::getDataWhere($pdo,'laporan_kelompok',$jenis,'id',$id);
        if ($filelama != '' && file_exists($path.$filelama)) {
            unlink($path.$filelama);
        }

        // set kolom nya jadi ''
        $sql = 'UPDATE laporan_kelompok SET '.$jenis.' = "" WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return redirect('laporan/custom/kelompok/'.$id.'/edit')->with('message', trans('all.databerhasildihapus'));
    }

    public function excel()
    {
        if(Utils::cekHakakses('laporancustom','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.kelompok'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.jenis'));

            $sql = 'SELECT nama, jenis FROM laporan_kelompok ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, trans('all.'.$row['jenis']));
                $objPHPExcel->getActiveSheet()->getStyle('B' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $i++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            // password
            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor laporan kelompok');
            $arrWidth = array(50,15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.laporankelompok'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}