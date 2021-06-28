<?php
namespace App\Http\Controllers;

use App\Atribut;
use App\AtributNilai;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class aturAtributController extends Controller
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
        if(Utils::cekHakakses('aturatributdanlokasi','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
            $wherebatasan = '';
            if($batasan != ''){
                $wherebatasan = ' WHERE a.id IN(SELECT idatribut FROM atributnilai WHERE id IN '.$batasan.')';
            }
            $sql = 'SELECT a.id,a.atribut FROM atribut a'.$wherebatasan;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $dataatribut = $stmt->fetchAll(PDO::FETCH_OBJ);

            if(Session::has('aturatribut_idatribut')){
                //cek email user yang login
                $pdo2 = DB::getPdo();
                $email = Utils::getDataWhere($pdo2,'user','email','id',Session::get('iduser_perusahaan'));

                //atribut nilai
                $sql = 'SELECT
                            a.id,
                            a.atribut,
                            COUNT(*)=SUM(IF(an.id IN ('.Session::get('aturatribut_idatribut').'),1,0)) as flag
                        FROM
                            atribut a
                            LEFT JOIN atributnilai an ON a.id=an.idatribut
                            '.$wherebatasan.'
                        GROUP BY
                            a.id
                        ORDER BY
                            a.atribut';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                $arrAtribut = array();
                $i=0;
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $arrAtribut[$i]['idatribut'] = $row['id'];
                    $arrAtribut[$i]['atribut'] = $row['atribut'];
                    $arrAtribut[$i]['flag'] = $row['flag'];

                    //select atribut nilai sesuai dengan batasan user yang login
                    $sqlAtributNilai = 'SELECT
                                          an.id,
                                          an.idatribut,
                                          an.nilai,
                                          IF(an.id IN ('.Session::get('aturatribut_idatribut').'),1,0) as dipilih,
                                          IF(ISNULL(e.id),"1",IF(ISNULL(b.idatributnilai)=false,"1","0")) as enable
                                        FROM
                                          atributnilai an
                                          LEFT JOIN (SELECT id FROM batasanemail WHERE email=:email1 LIMIT 1) e ON 1=1
                                          LEFT JOIN (SELECT ba.idatributnilai FROM batasanemail be, batasanatribut ba WHERE be.idbatasan=ba.idbatasan AND be.email=:email2) b ON b.idatributnilai=an.id 
                                        WHERE
                                          an.idatribut = :idatribut
                                        ORDER BY
                                          an.nilai';
                    $stmtAtributNilai = $pdo->prepare($sqlAtributNilai);
                    $stmtAtributNilai->bindValue(':email1', $email);
                    $stmtAtributNilai->bindValue(':email2', $email);
                    $stmtAtributNilai->bindValue(':idatribut', $row['id']);
                    $stmtAtributNilai->execute();

                    $arrAtribut[$i]['atributnilai'] = $stmtAtributNilai->fetchAll(PDO::FETCH_OBJ);
                    $i++;
                }

                $atribut = $arrAtribut;
            }else{
                $atribut = Utils::getAtributdanAtributNilaiCrud(0,'pegawai');
            }
            Utils::insertLogUser('akses menu atur atribut pegawai');
            return view('datainduk/pegawai/aturatribut/index', ['dataatribut' => $dataatribut, 'atribut' => $atribut, 'menu' => 'aturatributdanlokasi']);
        }else{
            return redirect('/');
        }
	}

    public function aturAtribut($atribut)
    {
        $hasil = '';
        $response = array();
        $response['data'] = '';
        if($atribut == 'o'){
            if(Session::has('aturatribut_idatribut')){
                Session::forget('aturatribut_idatribut');
                Session::forget('aturatribut_atribut');
            }
        }else {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //nilai atribut yg kepilih
            $sql = 'SELECT GROUP_CONCAT(CONCAT(an.nilai, " (",a.atribut,")") ORDER BY an.nilai SEPARATOR ", ") as atributnilai FROM atributnilai an, atribut a WHERE an.idatribut=a.id AND an.id IN('.$atribut.')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil = trans('all.filter').' : '.$row['atributnilai'];

            Session::set('aturatribut_idatribut', $atribut);
            Session::set('aturatribut_atribut', $hasil);

        }
        Session::regenerateToken();
        $response['data'] = $hasil;
        $response['token'] = csrf_token();
        return $response;
    }

    public function dataPegawai(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        if(Session::has('aturatribut_idatribut')){
            $where = ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.Session::get('aturatribut_idatribut').') AND del = "t" AND status = "a")';
        }
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $where .= ' AND id IN '.$batasan;
        }
        $table = 'pegawai';
        $columns = array('','nama','pin');
        $totalData = Utils::getDataCustomWhere($pdo,$table, 'count(id)', ' 1=1 '.$where);
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

        $sql = 'SELECT id,CONCAT("<input class=cekpegawai type=checkbox id=",id,">") as cekpegawai,nama,pin FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                $tempdata['cekpegawai'] = $key['cekpegawai'];
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

    public function getAtributnilai($idatribut){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
        $where = '';
        if($batasan != ''){
            $where = ' AND id IN '.$batasan;
        }
        $sql = 'SELECT id,nilai FROM atributnilai WHERE idatribut = :idatribut '.$where;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idatribut', $idatribut);
        $stmt->execute();
        $hasil = '<option value=>-- '.trans('all.atributnilai').' --</option>';
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $hasil .= '<option value='.$row['id'].'>'.$row['nilai'].'</option>';
        }
        return $hasil;
    }

    public function dataPegawaiAtribut(Request $request, $idatributnilai)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = ' AND 1=2';
        if($idatributnilai != 'o'){
            $where = ' AND id IN(SELECT idpegawai FROM pegawaiatribut WHERE idatributnilai = '.$idatributnilai.')';
        }
        if(Session::has('aturatribut_idatribut')){
            $where .= ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.Session::get('aturatribut_idatribut').') AND del = "t" AND status = "a")';
        }
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $where .= ' AND id IN '.$batasan;
        }
        $table = 'pegawai';
        $columns = array('','nama','pin');
        $totalData = Utils::getDataCustomWhere($pdo,'pegawai', 'count(id)', ' 1=1 '.$where);
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

        $sql = 'SELECT id,CONCAT("<input onclick=\"tampiltombolhapus()\" class=cekpegawai_2 type=checkbox id=",id,">") as cekpegawai,nama,pin FROM pegawai WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                $tempdata['cekpegawai'] = $key['cekpegawai'];
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

    //sewaktu simpan, pake insert ignore
    //hanya milih 1 atributnilai, cara ngesave buat seperti jamkerjapegawai
    public static function submitSetAtribut(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        Session::regenerateToken();
        $response = array();
        $response['msg'] = 'unknown';
        $response['token'] = csrf_token();
        $hasil = '';
        if($request->idpegawai != ''){
            try {
                $pdo->beginTransaction();
                $idpegawaisplit = explode('|', $request->idpegawai);
                for($i=0;$i<count($idpegawaisplit);$i++){
                    $cekatribut = Utils::cekPegawaiAtributNilai($idpegawaisplit[$i],$request->atributnilai);
                    if($cekatribut == false) {
                        $sql = 'INSERT IGNORE INTO pegawaiatribut VALUES(NULL, :idpegawai, :idatributnilai)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idpegawai', $idpegawaisplit[$i]);
                        $stmt->bindValue(':idatributnilai', $request->atributnilai);
                        $stmt->execute();
                    }else{
                        $hasil = $hasil  . " " . Utils::getNamaPegawai($idpegawaisplit[$i]);
                    }
                }
                Utils::insertLogUser('set pegawai atribut');
                $response['msg'] = $hasil;
                $pdo->commit();
            } catch (\Exception $e) {
                $pdo->rollBack();
                $response['msg'] = $e->getMessage();
            }
        }
        if ($hasil!='') {
            $hasil = trans('all.sebagianpegawaisudahmemilikiatributterpilih').$hasil;
            $response['msg'] = $hasil;
        }
        return $response;
    }

    public static function hapusAtribut(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        Session::regenerateToken();
        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'unknown';
        $response['token'] = csrf_token();
        if($request->idpegawai != ''){
            try
            {
                $pdo->beginTransaction();
                $idpegawaisplit = explode('|', $request->idpegawai);
                for($i=0;$i<count($idpegawaisplit);$i++){
                    $sql = 'DELETE FROM pegawaiatribut WHERE idpegawai = :idpegawai AND idatributnilai = :idatributnilai';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawaisplit[$i]);
                    $stmt->bindValue(':idatributnilai', $request->atributnilai);
                    $stmt->execute();
                }

                Utils::insertLogUser('hapus pegawai atribut');
                $response['status'] = 'OK';
                $response['msg'] = '';
                $pdo->commit();
            } catch (\Exception $e) {
                $pdo->rollBack();
                $response['status'] = 'error';
                $response['msg'] = $e->getMessage();
            }
        }
        return $response;
    }
}