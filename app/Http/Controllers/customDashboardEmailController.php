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

class customDashboardEmailController extends Controller
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
        if(Utils::cekHakakses('customdashboard','l')){
            Utils::insertLogUser('akses menu custom dashboard email');
            return view('pengaturan/customdashboardemail/index', ['menu' => 'customdashboard']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
    {
        if(Utils::cekHakakses('customdashboard','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('customdashboard','uhm')) {
                $columns = array('', 'email', 'customdashboard');
            }else{
                $columns = array('email', 'customdashboard');
            }
            $table = '(
                        SELECT
                            cde.id,
                            cde.email,
                            IFNULL(cd.nama,"") as customdashboard
                        FROM
                            customdashboard_email cde
                            LEFT JOIN customdashboard cd ON cde.idcustomdashboard=cd.id
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
                    if(Utils::cekHakakses('customdashboard','um')){
                        $action .= Utils::tombolManipulasi('ubah','customdashboardemail',$key['id']);
                    }
                    if(Utils::cekHakakses('customdashboard','hm')){
                        $action .= Utils::tombolManipulasi('hapus','customdashboardemail',$key['id']);
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
        if(Utils::cekHakakses('customdashboard','tm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $datacustomdashboard = Utils::getData($pdo,'customdashboard','id,nama','','nama');
            Utils::insertLogUser('akses menu tambah custom dsahboard email');
            return view('pengaturan/customdashboardemail/create', ['datacustomdashboard' => $datacustomdashboard, 'menu' => 'customdashboard']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek apakah kembar?
        $sql = 'SELECT id FROM customdashboard_email WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $request->email);
        $stmt->execute();
        if($stmt->rowCount() == 0){
            try
            {
                $pdo->beginTransaction();

                $sql = 'INSERT INTO customdashboard_email VALUES(NULL,:email,:customdashboard,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':email', $request->email);
                $stmt->bindValue(':customdashboard', $request->customdashboard);
                $stmt->execute();

//                $idcustomdashboard = $pdo->lastInsertId();
//
//                // simpan ke tabel customdashboardwaktu
//                if ($request->waktumulai != '') {
//                    for ($i = 0; $i < count($request->waktumulai); $i++) {
//                        $sql = 'INSERT INTO customdashboardwaktu VALUES(NULL,:idcustomdashboard,:waktumulai,:waktuselesai)';
//                        $stmt = $pdo->prepare($sql);
//                        $stmt->bindValue(':idcustomdashboard', $idcustomdashboard);
//                        $stmt->bindValue(':waktumulai', $request->waktumulai[$i]);
//                        $stmt->bindValue(':waktuselesai', $request->waktuselesai[$i]);
//                        $stmt->execute();
//                    }
//                }

                Utils::insertLogUser('Tambah custom dashboard email "'.$request->email.'"');

                $pdo->commit();
                return redirect('pengaturan/customdashboardemail')->with('message', trans('all.databerhasildisimpan'));
            } catch (\Exception $e) {
                $pdo->rollBack();
                return redirect('pengaturan/customdashboardemail/')->with('message', trans('all.terjadigangguan'));
            }
        }else{
            return redirect('pengaturan/customdashboardemail/create')->with('message', trans('all.datasudahada'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('customdashboard','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,email,idcustomdashboard FROM customdashboard_email WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $datacustomdashboard = Utils::getData($pdo,'customdashboard','id,nama','','nama');

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu custom dsahboard email');
            return view('pengaturan/customdashboardemail/edit', ['data' => $data, 'datacustomdashboard' => $datacustomdashboard, 'menu' => 'customdashboard']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan ada
        $cekadadata = Utils::getDataWhere($pdo,'customdashboard_email','email','id',$id);
        if($cekadadata != ''){
            //cek apakah kembar?
            $cekkembar = Utils::getData($pdo,'customdashboard_email','id','email = "'.$request->email.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                try
                {
                    $pdo->beginTransaction();
                    // ubah data customdashboard
                    $sql = 'UPDATE customdashboard_email SET email = :email, idcustomdashboard = :customdashboard WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':email', $request->email);
                    $stmt->bindValue(':customdashboard', $request->customdashboard);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah custom dashboard email "'.$cekadadata.'"');

                    $pdo->commit();
                    $msg = trans('all.databerhasildiubah');
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    $msg = $e->getMessage();
                }
                return redirect('pengaturan/customdashboardemail')->with('message', $msg);
            }else{
                $msg = trans('all.datasudahada');
            }
        }else{
            $msg = trans('all.datatidakditemukan');
        }
        return redirect('pengaturan/customdashboardemail/'.$id.'/edit')->with('message', $msg);
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('customdashboard','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'customdashboard_email','email','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'customdashboard_email',$id);
                Utils::insertLogUser('Hapus custom dashboard email "'.$cekadadata.'"');
                $msg = trans('all.databerhasildihapus');
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('pengaturan/customdashboardemail')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('customdashboard','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.customdashboard').' '.trans('all.email'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.email'))
                        ->setCellValue('B1', trans('all.customdashboard'));

            $sql = 'SELECT
                        cde.email,
                        IFNULL(cd.nama, "") as customdashboard
                    FROM
                        customdashboard_email cde
                        LEFT JOIN customdashboard cd ON cde.idcustomdashboard=cd.id
                    ORDER BY
                        email';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['email']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['customdashboard']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor custom dashboard email');
            $arrWidth = array(50, 50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.customdashboard') .' ' . trans('all.email'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}