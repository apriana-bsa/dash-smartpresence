<?php
namespace App\Http\Controllers;

use App\Atribut;
use App\AtributNilai;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class BatasanEmailController extends Controller
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
        if(Utils::cekHakakses('batasan','l')){
            Utils::insertLogUser('akses menu batasan email');
            return view('datainduk/lainlain/batasan/email/index', ['menu' => 'batasan']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
    {
        if(Utils::cekHakakses('batasan','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('batasan','uhm')) {
                $columns = array('', 'email', 'namabatasan');
            }else{
                $columns = array('email', 'namabatasan');
            }
            $table = '(
                        SELECT
                            be.id,
                            b.namabatasan,
                            be.email
                        FROM
                            batasan b,
                            batasanemail be
                        WHERE
                            be.idbatasan=b.id
                      ) x';
            $totalData = Utils::getDataCustomWhere($pdo,'batasan b,batasanemail be', 'count(b.id)',' be.idbatasan=b.id');
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
                    $action = '';
                    if(Utils::cekHakakses('batasan','um')){
                        $action .= Utils::tombolManipulasi('ubah','batasanemail',$key['id']);
                    }
                    if(Utils::cekHakakses('batasan','hm')){
                        $action .= Utils::tombolManipulasi('hapus','batasanemail',$key['id']);
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
        if(Utils::cekHakakses('batasan','tm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,namabatasan FROM batasan';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $batasan = $stmt->fetchAll(PDO::FETCH_OBJ);
            Utils::insertLogUser('akses menu tambah batasan email');
            return view('datainduk/lainlain/batasan/email/create', ['batasan' => $batasan, 'menu' => 'batasan']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek apakah kembar?
        $sql = 'SELECT id FROM batasanemail WHERE email=:email LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $request->email);
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            $pdo->beginTransaction();
            try
            {
                $sql = 'INSERT INTO batasanemail VALUES(NULL,:email,:idbatasan,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':email', $request->email);
                $stmt->bindValue(':idbatasan', $request->batasan);
                $stmt->execute();

                Utils::insertLogUser('Tambah batasan email "'.$request->email.'"');

                $pdo->commit();
                return redirect('datainduk/lainlain/batasanemail')->with('message', trans('all.databerhasildisimpan'));

            } catch (\Exception $e) {
                $pdo->rollBack();
                return redirect('datainduk/lainlain/batasanemail/create')->with('message', trans('all.terjadigangguan'));
            }
        }else{
            return redirect('datainduk/lainlain/batasanemail/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('batasan','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,email,idbatasan FROM batasanemail WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $batasanemail = $stmt->fetch(PDO::FETCH_OBJ);

            $sql = 'SELECT id,namabatasan FROM batasan';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $batasan = $stmt->fetchAll(PDO::FETCH_OBJ);

            if(!$batasanemail){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah batasan email');
            return view('datainduk/lainlain/batasan/email/edit', ['batasanemail' => $batasanemail, 'batasan' => $batasan, 'menu' => 'batasan']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idatribut ada
        $cekadadata = Utils::getDataWhere($pdo,'batasanemail','email','id',$id);
        if($cekadadata != ''){
            //cek apakah kembar?
            $cekkembar = Utils::getData($pdo,'batasanemail','id','email = "'.$request->email.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                // ubah data batasan
                $pdo->beginTransaction();
                try
                {
                    $sql = 'UPDATE batasanemail SET email = :email, idbatasan = :idbatasan WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':email', $request->email);
                    $stmt->bindValue(':idbatasan', $request->batasan);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah batasan email "'.$cekadadata.'"');

                    $pdo->commit();
                    return redirect('datainduk/lainlain/batasanemail')->with('message', trans('all.databerhasildiubah'));

                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return redirect('datainduk/lainlain/batasanemail/'.$id.'/edit')->with('message', trans('all.terjadigangguan'));
                }
            }else{
                return redirect('datainduk/lainlain/batasanemail/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/lainlain/batasanemail/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('batasan','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan idatribut ada
            $sql = 'SELECT id,email FROM batasanemail WHERE id=:id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                Utils::deleteData($pdo,'batasanemail',$id);
                Utils::insertLogUser('Hapus batasan email "'.$row['email'].'"');
                return redirect('datainduk/lainlain/batasanemail')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/lainlain/batasanemail')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('batasan','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.batasan').' '.trans('all.email'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.email'))
                        ->setCellValue('B1', trans('all.batasan'));

            $sql = 'SELECT
                        be.id,
                        b.namabatasan,
                        be.email
                    FROM
                        batasanemail be
                        LEFT JOIN batasan b ON be.idbatasan=b.id
                    ORDER BY
                      email';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['email']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['namabatasan']);

                $i++;
            }

            $arrWidth = array('', 25, 25);
            for ($j = 1; $j <= 2; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor batasan email');
            $arrWidth = array(25, 25);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.batasan') . '_' . trans('all.email'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}