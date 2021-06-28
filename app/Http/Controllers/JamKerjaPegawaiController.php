<?php
namespace App\Http\Controllers;

use App\User;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class JamKerjaPegawaiController extends Controller
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

    public function showIndex(Request $request)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            if(session::has('jamkerjapegawai_filteridjamkerja')){
                $sql = 'SELECT id,nama,IF(id IN (' . Session::get('jamkerjapegawai_filteridjamkerja') . '),1,0) as dipilih FROM jamkerja WHERE digunakan = "y" ORDER BY nama';
            }else{
                $sql = 'SELECT id,nama,"0" as dipilih FROM jamkerja WHERE digunakan = "y" ORDER BY nama';
            }
            //$sql = 'SELECT id,nama,IF(an.id IN (' . Session::get('jamkerjapegawai_idjamkerja') . '),1,0) as dipilih FROM jamkerja WHERE digunakan = "y" ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $jamkerja = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (Session::has('jamkerjapegawai_idatribut')) {
                //cek email user yang login
                $pdo2 = DB::getPdo();
                $sql = 'SELECT email FROM `user` WHERE id=:iduser LIMIT 1';
                $stmt = $pdo2->prepare($sql);
                $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $email = $row['email'];

                //atribut nilai
                $sql = 'SELECT
                            a.id,
                            a.atribut,
                            COUNT(*)=SUM(IF(an.id IN (' . Session::get('jamkerjapegawai_idatribut') . '),1,0)) as flag
                        FROM
                            atribut a
                            LEFT JOIN atributnilai an ON a.id=an.idatribut
                        GROUP BY
                            a.id
                        ORDER BY
                            a.atribut';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                $arrAtribut = array();
                $i = 0;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $arrAtribut[$i]['idatribut'] = $row['id'];
                    $arrAtribut[$i]['atribut'] = $row['atribut'];
                    $arrAtribut[$i]['flag'] = $row['flag'];

                    //select atribut nilai sesuai dengan batasan user yang login
                    $sqlAtributNilai = 'SELECT
                                          an.id,
                                          an.idatribut,
                                          an.nilai,
                                          IF(an.id IN (' . Session::get('jamkerjapegawai_idatribut') . '),1,0) as dipilih,
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
            } else {
                $atribut = Utils::getAtributdanAtributNilaiCrud(0, 'pegawai');
            }
            Utils::insertLogUser('akses menu jam kerja pegawai');
            $isOnboarding = $request->query('onboarding');
            return view('datainduk/absensi/jamkerjapegawai/index', ['atribut' => $atribut, 'jamkerja' => $jamkerja, 'menu' => 'jamkerjapegawai', 'onboarding' => $isOnboarding]);
        } else {
            return redirect('/');
        }
    }

    public function aturAtribut($atribut, $jamkerja='')
    {
        $hasil = '';
        $response = array();
        $response['data'] = '';
        //atribut
        if ($atribut == 'o') {
            if (Session::has('jamkerjapegawai_idatribut')) {
                Session::forget('jamkerjapegawai_idatribut');
                Session::forget('jamkerjapegawai_atribut');
            }
        } else {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //nilai atribut yg kepilih
            $sql = 'SELECT GROUP_CONCAT(CONCAT(an.nilai, " (",a.atribut,")") ORDER BY an.nilai SEPARATOR ", ") as atributnilai FROM atributnilai an, atribut a WHERE an.idatribut=a.id AND an.id IN(' . $atribut . ')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil .= trans('all.atribut') . ' : ' . $row['atributnilai'].'<br>';

            Session::set('jamkerjapegawai_idatribut', $atribut);
//            Session::set('jamkerjapegawai_atribut', $hasil);

        }

        //jamkerja
        if($jamkerja == ''){
            if (Session::has('jamkerjapegawai_filteridjamkerja')) {
                Session::forget('jamkerjapegawai_filteridjamkerja');
            }
        }else{
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //nilai jamkerja yg kepilih
            $sql = 'SELECT GROUP_CONCAT(nama ORDER BY nama SEPARATOR ", ") as jamkerja FROM jamkerja WHERE id IN(' . $jamkerja . ')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil .= trans('all.jamkerja') . ' : ' . $row['jamkerja'];

            Session::set('jamkerjapegawai_filteridjamkerja', $jamkerja);
        }
        Session::set('jamkerjapegawai_atribut', $hasil);
        Session::regenerateToken();
        $response['data'] = $hasil;
        $response['token'] = csrf_token();
        return $response;
    }

    public function pilihJamKerja($idjamkerja, $berlakumulai)
    {
        if(Utils::cekDateTime(str_replace('-', '/', $berlakumulai))) {
            Session::set('jamkerjapegawai_idjamkerja', $idjamkerja);
            Session::set('jamkerjapegawai_berlakumulai', str_replace('-', '/', $berlakumulai)); //format dd/mm/yyyy

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama,jenis FROM jamkerja WHERE id = :idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $idjamkerja);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $keterangan = '<center>' . trans('all.jamkerja') . ' : ' . $row['nama'] . ', ' . trans('all.berlakumulai') . ' : ' . str_replace('-', '/', $berlakumulai) . '</center>';
            Session::set('jamkerjapegawai_jenisjamkerja', $row['jenis']);
            Session::set('jamkerjapegawai_keterangan', $keterangan);
            $data = array();
            $data[0] = $row['jenis'];
            $data[1] = $keterangan;
            return $data;
        }else{
            return array();
        }
    }

    public function dataPegawai(Request $request,$jenis)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = ' AND del = "t"';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if (!($batasan == '' || $batasan == '()')) {
                $where .= ' AND id IN ' . $batasan;
            }
            if($jenis == 'pegawaijamkerja') {
                $where = ' AND 1=2';
                if (Session::has('jamkerjapegawai_idjamkerja') and Session::has('jamkerjapegawai_berlakumulai')) {
                    $where = ' AND id IN(SELECT idpegawai FROM pegawaijamkerja WHERE idjamkerja = ' . Session::get('jamkerjapegawai_idjamkerja') . ' AND berlakumulai = STR_TO_DATE("' . Session::get('jamkerjapegawai_berlakumulai') . '","%d/%m/%Y"))';
                }
            }
            if (Session::has('jamkerjapegawai_idatribut')) {
                $where .= ' AND id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . Session::get('jamkerjapegawai_idatribut') . ') AND status = "a")';
            }
            if (Session::has('jamkerjapegawai_filteridjamkerja')) {
                $where .= ' AND getpegawaijamkerja(id,"id",CURRENT_DATE()) IN ('. Session::get('jamkerjapegawai_filteridjamkerja') . ')';
            }
            $table = 'pegawai';
            $columns = array('','nama','pin');
            $totalData = Utils::getDataCustomWhere($pdo,$table, 'count(id)','1=1 '.$where);
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

            $sql = 'SELECT id,nama,pin FROM pegawai WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    $action = '<input class=cekpegawai type=checkbox id="'.$key['id'].'">';
                    if($jenis == 'pegawaijamkerja'){
                        $action = '<input onclick="tampiltombolhapus()" class=cekpegawai_2 type=checkbox id="'.$key['id'].'">';
                    }
                    $tempdata['cekpegawai'] = $action;
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
        return '';
    }

    public function submitJamKerja(Request $request)
    {
        Session::regenerateToken();
        $pesan = '';
        $response = array();
        $response['msg'] = 'unknown';
        $response['token'] = csrf_token();
        if (Session::has('jamkerjapegawai_idjamkerja') and Session::has('jamkerjapegawai_berlakumulai')) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $idjamkerja = Session::get('jamkerjapegawai_idjamkerja');
            $berlakumulai = Session::get('jamkerjapegawai_berlakumulai');
            $idpegawai = $request->idpegawai;
            $idpegawaiexplode = explode('|', $idpegawai);
            $idpegawaiimplode = implode(',', $idpegawaiexplode);

            //cek apakah pegawai terpilih sudah ada di jamkerja?
            $sql = 'SELECT id FROM pegawaijamkerja WHERE idpegawai IN(' . $idpegawaiimplode . ') AND berlakumulai=STR_TO_DATE(:berlakumulai,"%d/%m/%Y") LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':berlakumulai', $berlakumulai);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                for ($i = 0; $i < count($idpegawaiexplode); $i++) {
                    //select namapegawai
                    $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawaiexplode[$i]);
                    $stmt->execute();
                    $rowPegawai = $stmt->fetch(PDO::FETCH_ASSOC);

                    //cek apakah ada jadwalshift ditanggal terpilih dan setelahnya?
                    $cekjadwalshiftada = Utils::cekJadwalShiftAda($idpegawaiexplode[$i],$berlakumulai);
                    //cek pengaruhnya pada jadwalshift
                    $cekpengaruhjadwalshift = Utils::cekPengaruhJadwalShift($idpegawaiexplode[$i],$idjamkerja,$berlakumulai);
                    if($cekjadwalshiftada == 'y'){
                        $pesan .= '<b>'.$rowPegawai['nama'] . '</b> "' . trans('all.peringatanharaphapusjadwalshift') . '"<br> ';
                    }else if ($cekpengaruhjadwalshift == 'y') {
                        $pesan .= $rowPegawai['nama'] . ' "' . trans('all.gagalpenambahanjamkerjapegawai') . '"<br> ';
                    } else {
                        $sql = 'INSERT INTO pegawaijamkerja VALUES(NULL,:idpegawai,:idjamkerja,STR_TO_DATE(:berlakumulai,"%d/%m/%Y"),NOW())';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idpegawai', $idpegawaiexplode[$i]);
                        $stmt->bindValue(':idjamkerja', $idjamkerja);
                        $stmt->bindValue(':berlakumulai', $berlakumulai);
                        $stmt->execute();
                        Utils::insertLogUser('Tambah Jam Kerja Pegawai');
                    }
                }
                $pesan = $pesan != '' ? substr($pesan, 0, -5) : $pesan;
                if (Session::get('onboardingstep') == 4) {
                    Session::set('onboardingstep', 5);
                    $user = User::find(Session::get('iduser_perusahaan'));
                    $user -> onboardingstep = 5;
                    $user -> save();
                }
            } else {
                $pesan = trans('all.datasudahada');
            }
            $response['msg'] = $pesan;
        }
        return $response;
    }

    public function hapusJamKerjaTerpilih(Request $request)
    {
        Session::regenerateToken();
        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'unknown';
        $response['token'] = csrf_token();
        if (Session::has('jamkerjapegawai_idjamkerja') and Session::has('jamkerjapegawai_berlakumulai')) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $idjamkerja = Session::get('jamkerjapegawai_idjamkerja');
            $berlakumulai = Session::get('jamkerjapegawai_berlakumulai');
            $idpegawai = $request->idpegawai; //string
            $idpegawaiexplode = explode('|', $idpegawai); //array
            $idpegawaiimplode = implode(',', $idpegawaiexplode); //string
            //cek data apakah ada
            $sql = 'SELECT id FROM pegawaijamkerja WHERE idpegawai IN(' . $idpegawaiimplode . ') AND idjamkerja = :idjamkerja AND berlakumulai = STR_TO_DATE(:berlakumulai,"%d/%m/%Y")';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $idjamkerja);
            $stmt->bindValue(':berlakumulai', $berlakumulai);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                // hapus jadwalshift jika sudah ada
//                $sql = 'DELETE FROM jadwalshift WHERE idpegawai IN(' . $idpegawaiimplode . ') AND idjamkerjashift IN (SELECT id FROM jamkerjashift WHERE idjamkerja = '.$idjamkerja.')';
                $sql = 'DELETE FROM jadwalshift WHERE idpegawai IN(' . $idpegawaiimplode . ') AND tanggal >= STR_TO_DATE(:berlakumulai,"%d/%m/%Y")';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':berlakumulai', $berlakumulai);
                $stmt->execute();

                //hapus data
                $sql = 'DELETE FROM pegawaijamkerja WHERE idpegawai IN(' . $idpegawaiimplode . ') AND idjamkerja = :idjamkerja AND berlakumulai = STR_TO_DATE(:berlakumulai,"%d/%m/%Y")';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idjamkerja', $idjamkerja);
                $stmt->bindValue(':berlakumulai', $berlakumulai);
                $stmt->execute();

                Utils::insertLogUser('Hapus Jam Kerja Pegawai');
                $response['status'] = 'ok';
                $response['msg'] = '';
            } else {
                $response['msg'] = trans('all.datatidakditemukan');
            }
        }
        return $response;
    }
}