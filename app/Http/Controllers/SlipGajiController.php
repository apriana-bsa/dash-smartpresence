<?php
namespace App\Http\Controllers;

use App\slipgaji;

use Hamcrest\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class SlipGajiController extends Controller
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
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            Utils::insertLogUser('akses menu slipgaji');
	        return view('datainduk/payroll/slipgaji/index', ['menu' => 'payrollslipgaji']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('', 'berlakumulai', 'kelompok', 'nama', 'keterangan');
            $table = '(SELECT s.id,pk.nama as kelompok,s.nama,s.berlakumulai,s.keterangan FROM slipgaji s,payroll_kelompok pk WHERE s.idpayrollkelompok=pk.id) x';
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE 1=1 '.$where;
            $stmt = $pdo->prepare($sql);
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
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                    }
                }
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalFiltered = $row['total'];
            }

            $sql = 'SELECT * FROM '.$table.' WHERE 1=1 ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    $action = '<a title="' . trans('all.komponenmaster') . '" href="slipgaji/' . $key['id'] . '/komponenmaster"><i class="fa fa-pencil-square" style="color:#A2A2A2"></i></a>&nbsp;&nbsp;';
                    if(Utils::cekHakakses('payrollkomponenmaster','um')){
                        $action .= Utils::tombolManipulasi('ubah','slipgaji',$key['id']);
                    }
                    if(Utils::cekHakakses('payrollkomponenmaster','hm')){
                        $action .= Utils::tombolManipulasi('hapus','slipgaji',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'berlakumulai') {
                            $tempdata[$columns[$i]] = Utils::tanggalCantik($key[$columns[$i]]);
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
        if(Utils::cekHakakses('payrollkomponenmaster','tm')){
            Utils::insertLogUser('akses menu tambah slipgaji');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $datapayrollkelompok = Utils::getData($pdo,'payroll_kelompok', 'id,nama','','nama');
            return view('datainduk/payroll/slipgaji/create', ['datapayrollkelompok' => $datapayrollkelompok, 'menu' => 'payrollslipgaji']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'slipgaji','id','nama',$request->nama);
        if($cekadadata == ''){
            $sql = 'INSERT INTO slipgaji VALUES(NULL,:idpayrollkelompok,:nama,:berlakumulai,"",:keterangan,NOW(),NULL)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpayrollkelompok', $request->payrollkelompok);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':berlakumulai', Utils::convertDmy2Ymd($request->berlakumulai));
            $stmt->bindValue(':keterangan', $request->keterangan);
            $stmt->execute();

            $id = $pdo->lastInsertId();

            //simpan template slip gaji jika ada
            if ($request->hasFile('template_excel')) {
                $template_excel = $request->file('template_excel');
                if($template_excel->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $template_excel->getMimeType() == 'application/vnd.ms-excel'){
                    $path = Session::get('folderroot_perusahaan') . '/payroll/slipgaji/';
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    //simpan file
                    $format = $template_excel->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
                    $name = $id.$format;
                    move_uploaded_file($template_excel, $path.$name);

                    //update payroll_pengaturan
                    $sql = 'UPDATE slipgaji SET template_excel = :template_excel WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':template_excel', $name);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();
                }
            }

            Utils::insertLogUser('Tambah slipgaji "'.$request->payrollkomponenmaster.'"');
    
            return redirect('datainduk/payroll/slipgaji')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/payroll/slipgaji/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('payrollkomponenmaster','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM slipgaji WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah slipgaji');
            $datapayrollkelompok = Utils::getData($pdo,'payroll_kelompok', 'id,nama','','nama');
            return view('datainduk/payroll/slipgaji/edit', ['data' => $data, 'datapayrollkelompok' => $datapayrollkelompok, 'menu' => 'payrollslipgaji']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'slipgaji','nama','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'slipgaji','id','idpayrollkelompok = '.$request->payrollkelompok.' AND nama = "'.$request->nama.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $sql = 'UPDATE slipgaji SET idpayrollkelompok = :idpayrollkelompok, nama = :nama, berlakumulai = :berlakumulai, keterangan = :keterangan WHERE id = :id LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpayrollkelompok', $request->payrollkelompok);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':berlakumulai', Utils::convertDmy2Ymd($request->berlakumulai));
                $stmt->bindValue(':keterangan', $request->keterangan);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                //simpan template slip gaji jika ada
                if ($request->hasFile('template_excel')) {
                    $template_excel = $request->file('template_excel');
                    if($template_excel->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $template_excel->getMimeType() == 'application/vnd.ms-excel'){
                        $path = Session::get('folderroot_perusahaan') . '/payroll/slipgaji/';
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }

                        //simpan file
                        $format = $template_excel->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
                        $name = $id.$format;
                        move_uploaded_file($template_excel, $path.$name);

                        //update payroll_pengaturan
                        $sql = 'UPDATE slipgaji SET template_excel = :template_excel WHERE id = :id';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':template_excel', $name);
                        $stmt->bindValue(':id', $id);
                        $stmt->execute();
                    }
                }

                Utils::insertLogUser('Ubah slipgaji "'.$cekadadata.'" => "'.$request->payrollkomponenmaster.'"');
    
                return redirect('datainduk/payroll/slipgaji')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/payroll/slipgaji/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/payroll/slipgaji/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('payrollkomponenmaster','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'slipgaji','nama','id',$id);
            if($cekadadata != ''){
                $template_excel = Utils::getDataWhere($pdo,'slipgaji','template_excel','id',$id);
                $path = Session::get('folderroot_perusahaan') . '/payroll/slipgaji/'.$template_excel;
                //hapus yang lama jika ada
                if($template_excel != '' && file_exists($path)){
                    unlink($path);
                }
                Utils::deleteData($pdo,'slipgaji',$id);
                Utils::insertLogUser('Hapus slipgaji "'.$cekadadata.'"');
                return redirect('datainduk/payroll/slipgaji')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/payroll/slipgaji')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function hapusTemplate($id){
        if(Utils::cekHakakses('payrollkomponenmaster','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'slipgaji','nama','id',$id);
            if($cekadadata != '') {
                $template_excel = Utils::getDataWhere($pdo, 'slipgaji', 'template_excel', 'id', $id);
                $path = Session::get('folderroot_perusahaan') . '/payroll/slipgaji/' . $template_excel;
                //hapus yang lama jika ada
                if ($template_excel != '' && file_exists($path)) {
                    unlink($path);
                }
                $sql = 'UPDATE slipgaji SET template_excel = "" WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::insertLogUser('Hapus template excel slipgaji "'.$cekadadata.'"');
                return redirect('datainduk/payroll/slipgaji/'.$id.'/edit')->with('message', trans('all.databerhasildihapus'));
            }
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.slipgaji'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.berlakumulai'))
                        ->setCellValue('B1', trans('all.kelompok'))
                        ->setCellValue('C1', trans('all.nama'))
                        ->setCellValue('D1', trans('all.keterangan'));

            $sql = 'SELECT
                        pk.nama as kelompok,
                        s.nama,
                        s.berlakumulai,
                        s.keterangan
                    FROM
                        slipgaji s,
                        payroll_kelompok pk
                    WHERE
                        s.idpayrollkelompok=pk.id
                    ORDER BY
                        s.nama ASC, pk.nama ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, Utils::tanggalCantik($row['berlakumulai']));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['kelompok']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['keterangan']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor slipgaji');
            $arrWidth = array(25, 25, 50, 100);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.slipgaji'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }

    public function komponenMaster($idslipgaji){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $slipgaji = Utils::getDataWhere($pdo,'slipgaji','nama','id',$idslipgaji);
        return view('datainduk/payroll/slipgaji/komponenmaster', ['idslipgaji' => $idslipgaji, 'slipgaji' => $slipgaji, 'menu' => 'payrollslipgaji']);
    }

    // jenis (tersedia dan tersimpan)
    public function komponenMasterData(Request $request, $idslipgaji, $jenis){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idpayrollkelompok = Utils::getDataWhere($pdo,'slipgaji','idpayrollkelompok','id',$idslipgaji);
        $where = ' AND idpayroll_kelompok= :idpayroll_kelompok AND id IN(SELECT idkomponenmaster FROM slipgaji_komponenmaster WHERE idslipgaji = :idslipgaji)';
        if($jenis == 'tersedia'){
            $where = ' AND idpayroll_kelompok= :idpayroll_kelompok AND id NOT IN(SELECT idkomponenmaster FROM slipgaji_komponenmaster WHERE idslipgaji = :idslipgaji)';
        }
        $columns = array('','nama');
        $table = '(SELECT id,idpayroll_kelompok,CONCAT(nama," (",kode,")") as nama FROM payroll_komponen_master) x';
        $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE 1=1 '.$where;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpayroll_kelompok', $idpayrollkelompok);
        $stmt->bindValue(':idslipgaji', $idslipgaji);
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
            $stmt->bindValue(':idpayroll_kelompok', $idpayrollkelompok);
            $stmt->bindValue(':idslipgaji', $idslipgaji);
            for($i=0;$i<count($columns);$i++) {
                if($columns[$i] != '') {
                    $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                }
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalFiltered = $row['total'];
        }
        $sql = 'SELECT * FROM '.$table.' WHERE 1=1 ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpayroll_kelompok', $idpayrollkelompok);
        $stmt->bindValue(':idslipgaji', $idslipgaji);
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
                $tempdata['cek'] = '<input class=cek'.$jenis.' type=checkbox id="'.$key['id'].'">';
                for($i=1;$i<count($columns);$i++){
                    $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                }
                $data[] = $tempdata;
            }
        }

        return Utils::jsonDatatable($request->input('draw'),$totalData,$totalFiltered,$data);
    }

    public function submitKomponenMaster(Request $request, $idslipgaji){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        try
        {
            $pdo->beginTransaction();
            // $request->jenis (simpan,hapus)
            if($request->jenis == 'simpan') {
                $idkomponenmaster = explode(',', $request->idkomponenmaster);
                for ($i = 0; $i < count($idkomponenmaster); $i++) {
                    $sql = 'INSERT INTO slipgaji_komponenmaster VALUES(NULL,:idslipgaji,:idkomponenmaster)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idslipgaji', $idslipgaji);
                    $stmt->bindValue(':idkomponenmaster', $idkomponenmaster[$i]);
                    $stmt->execute();
                }
            }else{
                $sql = 'DELETE FROM slipgaji_komponenmaster WHERE idkomponenmaster IN('.$request->idkomponenmaster.')';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }
            Utils::insertLogUser('Set slip gaji komponen master "'.Utils::getDataWhere($pdo,'slipgaji','nama','id',$idslipgaji).'"');
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            return $e->getMessage();
        }
        return 'ok';
    }

    public function pegawai($idslipgaji){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $slipgaji = Utils::getDataWhere($pdo,'slipgaji','nama','id',$idslipgaji);
        return view('datainduk/payroll/slipgaji/pegawai', ['idslipgaji' => $idslipgaji, 'slipgaji' => $slipgaji, 'menu' => 'payrollslipgaji']);
    }

    // jenis (tersedia dan tersimpan)
    public function pegawaiData(Request $request, $idslipgaji, $jenis){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = ' AND status = "a" AND del = "t" AND id IN(SELECT idpegawai FROM slipgaji_pegawai WHERE idslipgaji = :idslipgaji)';
        if($jenis == 'tersedia'){
            $where = ' AND status = "a" AND del = "t" AND id NOT IN(SELECT idpegawai FROM slipgaji_pegawai WHERE idslipgaji = :idslipgaji)';
        }
        $columns = array('','nama','pin');
        $sql = 'SELECT COUNT(id) as total FROM pegawai WHERE 1=1 '.$where;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idslipgaji', $idslipgaji);
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
            $sql = 'SELECT COUNT(id) as total FROM pegawai WHERE 1=1 '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idslipgaji', $idslipgaji);
            for($i=0;$i<count($columns);$i++) {
                if($columns[$i] != '') {
                    $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                }
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalFiltered = $row['total'];
        }

        $sql = 'SELECT id,nama,pin FROM pegawai WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idslipgaji', $idslipgaji);
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
                $tempdata['cek'] = '<input class=cek'.$jenis.' type=checkbox id="'.$key['id'].'">';
                for($i=1;$i<count($columns);$i++){
                    if($columns[$i] == 'nama'){
                        $tempdata[$columns[$i]] = '<span class="detailpegawai" onclick="detailpegawai('.$key['id'].')" style="cursor:pointer;">'.$key['nama'].'</span>';
                    }else{
                        $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                    }
                }
                $data[] = $tempdata;
            }
        }

        return Utils::jsonDatatable($request->input('draw'),$totalData,$totalFiltered,$data);
    }

    public function submitPegawai(Request $request, $idslipgaji){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        try
        {
            $pdo->beginTransaction();
            // $request->jenis (simpan,hapus)
            if($request->jenis == 'simpan') {
                $idpegawai = explode(',', $request->idpegawai);
                for ($i = 0; $i < count($idpegawai); $i++) {
                    $sql = 'INSERT INTO slipgaji_pegawai VALUES(NULL,:idslipgaji,:idpegawai)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idslipgaji', $idslipgaji);
                    $stmt->bindValue(':idpegawai', $idpegawai[$i]);
                    $stmt->execute();
                }
            }else{
                $sql = 'DELETE FROM slipgaji_pegawai WHERE idpegawai IN('.$request->idpegawai.')';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }
            Utils::insertLogUser('Set slip gaji pegawai "'.Utils::getDataWhere($pdo,'slipgaji','nama','id',$idslipgaji).'"');
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            return $e->getMessage();
        }
        return 'ok';
    }
}