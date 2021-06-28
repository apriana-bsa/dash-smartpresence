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

class PayrollKelompokController extends Controller
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
            $totaldata = Utils::getTotalData(1,'payroll_kelompok');
            Utils::insertLogUser('akses menu payroll_kelompok');
	        return view('datainduk/payroll/payrollkelompok/index', ['totaldata' => $totaldata, 'menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('', 'nama');
            $table = 'payroll_kelompok';
            $totalData = Utils::getDataCustomWhere($pdo,'payroll_kelompok', 'count(id)');
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

            $sql = 'SELECT id,nama FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    $action = '<a title="' . trans('all.detail') . '" href="payrollkelompok/' . $key['id'] . '/komponenmaster"><i class="fa fa-pencil-square" style="color:#A2A2A2"></i></a>&nbsp;&nbsp;';
                    if(Utils::cekHakakses('payrollkomponenmaster','um')){
                        $action .= Utils::tombolManipulasi('ubah','payrollkelompok',$key['id']);
                    }
                    if(Utils::cekHakakses('payrollkomponenmaster','hm')){
                        $action .= Utils::tombolManipulasi('hapus','payrollkelompok',$key['id']);
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

	public function create()
    {
        if(Utils::cekHakakses('payrollkomponenmaster','tm')){
            Utils::insertLogUser('akses menu tambah payroll_kelompok');
            return view('datainduk/payroll/payrollkelompok/create', ['menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM payroll_kelompok WHERE nama=:nama LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nama', $request->nama);
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            $sql = 'INSERT INTO payroll_kelompok VALUES(NULL,:nama,"","",NOW(),NULL)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->execute();

            $idpayrollkelompok = $pdo->lastInsertId();
            //simpan template payroll posting jika ada
            if ($request->hasFile('templatepayrollposting')) {
                $templatepayroll = $request->file('templatepayrollposting');
                if($templatepayroll->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $templatepayroll->getMimeType() == 'application/vnd.ms-excel'){
                    $path = Session::get('folderroot_perusahaan') . '/payroll/';
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    //simpan file
                    $format = $templatepayroll->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
                    $name = $idpayrollkelompok.'_templatepayrollposting'.date('YmdHis').$format;
                    move_uploaded_file($templatepayroll, $path.$name);

                    //update payroll_pengaturan
                    $sql = 'UPDATE payroll_kelompok SET template_payrollposting = :template_payrollposting WHERE id = :idpayrollkelompok';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':template_payrollposting', $name);
                    $stmt->bindValue(':idpayrollkelompok', $idpayrollkelompok);
                    $stmt->execute();
                }
            }

            //simpan template slip gaji jika ada
            if ($request->hasFile('templateslipgaji')) {
                $templatepayroll = $request->file('templateslipgaji');
                if($templatepayroll->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $templatepayroll->getMimeType() == 'application/vnd.ms-excel'){
                    $path = Session::get('folderroot_perusahaan') . '/payroll/';
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    //simpan file
                    $format = $templatepayroll->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
                    $name = $idpayrollkelompok.'_templateslipgaji'.date('YmdHis').$format;
                    move_uploaded_file($templatepayroll, $path.$name);

                    //update payroll_pengaturan
                    $sql = 'UPDATE payroll_kelompok SET template_slipgaji = :template_slipgaji WHERE id = :idpayrollkelompok';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':template_slipgaji', $name);
                    $stmt->bindValue(':idpayrollkelompok', $idpayrollkelompok);
                    $stmt->execute();
                }
            }

            Utils::insertLogUser('Tambah payroll kelompok "'.$request->nama.'"');
    
            return redirect('datainduk/payroll/payrollkelompok')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/payroll/payrollkelompok/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('payrollkomponenmaster','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM payroll_kelompok WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah payroll_kelompok');
            return view('datainduk/payroll/payrollkelompok/edit', ['data' => $data, 'menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'payroll_kelompok','nama','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'payroll_kelompok','id','nama = "'.$request->nama.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $sql = 'UPDATE payroll_kelompok SET nama = :nama WHERE id = :id LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                //simpan template payroll posting jika ada
                if ($request->hasFile('templatepayrollposting')) {
                    $templatepayroll = $request->file('templatepayrollposting');
                    if($templatepayroll->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $templatepayroll->getMimeType() == 'application/vnd.ms-excel'){
                        $path = Session::get('folderroot_perusahaan') . '/payroll/';
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }

                        //hapus yang lama jika ada
                        $filelama = Utils::getDataWhere($pdo,'payroll_kelompok','template_payrollposting','id',$id);
                        if ($filelama != '' && file_exists($path.$filelama)) {
                            unlink($path.$filelama);
                        }

                        //simpan file
                        $format = $templatepayroll->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
                        $name = $id.'_templatepayrollposting'.date('YmdHis').$format;
                        move_uploaded_file($templatepayroll, $path.$name);

                        //update payroll_pengaturan
                        $sql = 'UPDATE payroll_kelompok SET template_payrollposting = :template_payrollposting WHERE id = :idpayrollkelompok';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':template_payrollposting', $name);
                        $stmt->bindValue(':idpayrollkelompok', $id);
                        $stmt->execute();
                    }
                }

                //simpan template slip gaji jika ada
                if ($request->hasFile('templateslipgaji')) {
                    $templatepayroll = $request->file('templateslipgaji');
                    if($templatepayroll->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $templatepayroll->getMimeType() == 'application/vnd.ms-excel'){
                        $path = Session::get('folderroot_perusahaan') . '/payroll/';
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }

                        //hapus yang lama jika ada
                        $filelama = Utils::getDataWhere($pdo,'payroll_kelompok','template_slipgaji','id',$id);
                        if ($filelama != '' && file_exists($path.$filelama)) {
                            unlink($path.$filelama);
                        }

                        //simpan file
                        $format = $templatepayroll->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
                        $name = $id.'_templateslipgaji'.date('YmdHis').$format;
                        move_uploaded_file($templatepayroll, $path.$name);

                        //update payroll_pengaturan
                        $sql = 'UPDATE payroll_kelompok SET template_slipgaji = :template_slipgaji WHERE id = :idpayrollkelompok';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':template_slipgaji', $name);
                        $stmt->bindValue(':idpayrollkelompok', $id);
                        $stmt->execute();
                    }
                }

                Utils::insertLogUser('Ubah payroll kelompok "'.$cekadadata.'" => "'.$request->nama.'"');
    
                return redirect('datainduk/payroll/payrollkelompok')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/payroll/payrollkelompok/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/payroll/payrollkelompok/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('payrollkomponenmaster','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'payroll_kelompok','nama','id',$id);
            if($cekadadata != ''){
                $gagalkan = false;
                // cek apakah sudah digunakan di tabel payroll_posting
                $datakomponenmaster = Utils::getData($pdo,'payroll_komponen_master','id','idpayroll_kelompok='.$id);
                if($datakomponenmaster != ''){
                    foreach($datakomponenmaster as $key){
                        $datapayrollpostingkomponen = Utils::getDataWhere($pdo,'payroll_posting_komponen','id','komponenmaster_id',$key->id);
                        if($datapayrollpostingkomponen != ''){
                            $gagalkan = true;
                            break;
                        }
                    }
                }
                if($gagalkan){
                    return redirect('datainduk/payroll/payrollkelompok')->with('message_error', trans('all.datasudahberelasidenganpayrolposting'));
                }

                // cek apakah sudah digunakan di tabel slipgaji
                $cekslipgaji = Utils::getData($pdo,'slipgaji','id','idpayrollkelompok='.$id);
                if($cekslipgaji != '') {
                    $gagalkan = true;
                }
                if($gagalkan){
                    return redirect('datainduk/payroll/payrollkelompok')->with('message_error', trans('all.datasudahberelasidenganslipgaji'));
                }

                Utils::deleteData($pdo,'payroll_komponen_master',$id,'idpayroll_kelompok');
                Utils::deleteData($pdo,'payroll_kelompok',$id);
                Utils::insertLogUser('Hapus payroll kelompok "'.$cekadadata.'"');
                return redirect('datainduk/payroll/payrollkelompok')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/payroll/payrollkelompok')->with('message_error', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function hapusTemplate($id, $jenis){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $path = Session::get('folderroot_perusahaan') . '/payroll/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        //hapus yang lama jika ada
        $filelama = Utils::getDataWhere($pdo,'payroll_kelompok',$jenis,'id',$id);
        if ($filelama != '' && file_exists($path.$filelama)) {
            unlink($path.$filelama);
        }

        // set kolom nya jadi ''
        $sql = 'UPDATE payroll_kelompok SET '.$jenis.' = "" WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return redirect('datainduk/payroll/payrollkelompok/'.$id.'/edit')->with('message', trans('all.databerhasildihapus'));
    }

    public function excel()
    {
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.kelompok'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'));

            $sql = 'SELECT
                        nama
                    FROM
                        payroll_kelompok
                    ORDER BY
                        nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor payroll_kelompok');
            $arrWidth = array(50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.kelompok'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}