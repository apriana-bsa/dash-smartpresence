<?php
namespace App\Http\Controllers;

use App\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;

class ijinkanSambungDataCaptureController extends Controller
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

            if(Session::has('ijinkansambungdatacapture_idatribut')){
                //cek email user yang login
                $pdo2 = DB::getPdo();
                $sql = 'SELECT email FROM `user` WHERE id=:iduser LIMIT 1';
                $stmt = $pdo2->prepare($sql);
                $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $email = $row['email'];

                $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
                $wherebatasan = '';
                if ($batasan!='') {
                    $wherebatasan = ' WHERE a.id IN(SELECT idatribut FROM atributnilai WHERE id IN '.$batasan.')';
                }

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
                                          IF(an.id IN ('.Session::get('ijinkansambungdatacapture_idatribut').'),1,0) as dipilih,
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
            Utils::insertLogUser('akses menu ijinkan smabungan datacapture');
            return view('datainduk/pegawai/ijinkansambungdatacapture/index', ['atribut' => $atribut, 'menu' => 'aturatributdanlokasi']);
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
            if(Session::has('ijinkansambungdatacapture_idatribut')){
                Session::forget('ijinkansambungdatacapture_idatribut');
                Session::forget('ijinkansambungdatacapture_atribut');
            }
        }else {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //nilai atribut yg kepilih
            $sql = 'SELECT GROUP_CONCAT(CONCAT(an.nilai, " (",a.atribut,")") ORDER BY an.nilai SEPARATOR ", ") as atributnilai FROM atributnilai an, atribut a WHERE an.idatribut=a.id AND an.id IN('.$atribut.')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil = trans('all.filter').' : '.$row['atributnilai'];

            Session::set('ijinkansambungdatacapture_idatribut', $atribut);
            Session::set('ijinkansambungdatacapture_atribut', $hasil);

        }
        Session::regenerateToken();
        $response['data'] = $hasil;
        $response['token'] = csrf_token();
        return $response;
    }

    public function dataPegawai(Request $request, $ijinkansambungdatacapture)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        if(Session::has('ijinkansambungdatacapture_idatribut')){
            $where .= ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.Session::get('ijinkansambungdatacapture_idatribut').') AND del = "t" AND status = "a")';
        }
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $where .= ' AND id IN '.$batasan;
        }
        $classcheckbox = 'cekpegawai';
        if($ijinkansambungdatacapture == 'y'){
            $classcheckbox = 'cekpegawai_2';
        }
        $table = 'pegawai';
        $columns = array('','nama','pin');
        $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE ijinkansambungdatacapture = :ijinkansambungdatacapture '.$where;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':ijinkansambungdatacapture', $ijinkansambungdatacapture);
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
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE ijinkansambungdatacapture = :ijinkansambungdatacapture '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':ijinkansambungdatacapture', $ijinkansambungdatacapture);
            for($i=0;$i<count($columns);$i++) {
                if($columns[$i] != '') {
                    $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                }
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalFiltered = $row['total'];
        }

        $sql = 'SELECT id,CONCAT("<input class=\"'.$classcheckbox.'\" type=checkbox id=",id,">") as cekpegawai,nama,pin FROM pegawai WHERE ijinkansambungdatacapture = :ijinkansambungdatacapture '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':ijinkansambungdatacapture', $ijinkansambungdatacapture);
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
                    $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                }
                $data[] = $tempdata;
            }
        }
        return Utils::jsonDatatable($request->input('draw'),$totalData,$totalFiltered,$data);
    }

    public static function submit(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        Session::regenerateToken();
        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'error';
        $response['token'] = csrf_token();
        if($request->idpegawai != ''){
            try {
                $pdo->beginTransaction();
                $idpegawaisplit = explode('|', $request->idpegawai);
                for($i=0;$i<count($idpegawaisplit);$i++){
                    $sql = 'UPDATE pegawai SET ijinkansambungdatacapture = :ijinkansambungdatacapture WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':ijinkansambungdatacapture', $request->ijinkansambungdatacapture);
                    $stmt->bindValue(':id', $idpegawaisplit[$i]);
                    $stmt->execute();
                }

                Utils::insertLogUser('Set ijinkan sambungan Data Capture Pegawai');
                $response['status'] = 'ok';
                $response['msg'] = '';

                $pdo->commit();
            } catch (\Exception $e) {
                $pdo->rollBack();
                $response['msg'] = Utils::errHandlerMsg($e->getMessage());
            }
        }
        return $response;
    }
}