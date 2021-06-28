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

class PayrollKomponenMasterController extends Controller
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

	public function getindex($idpayrollkelompok)
	{
	    if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('payrollkomponenmaster','l')){
            $totaldata = Utils::getTotalData(1,'payroll_komponen_master','idpayroll_kelompok = '.$idpayrollkelompok);
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $kelompok = Utils::getDataWhere($pdo,'payroll_kelompok','nama','id',$idpayrollkelompok);
            Utils::insertLogUser('akses menu payroll komponen master');
	        return view('datainduk/payroll/payrollkomponenmaster/index', ['totaldata' => $totaldata, 'idpayrollkelompok' => $idpayrollkelompok, 'kelompok' => $kelompok, 'menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request, $idpayrollkelompok)
	{
        if(Utils::cekHakakses('payrollkomponenmaster','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = ' AND idpayroll_kelompok = '.$idpayrollkelompok;
            if(Utils::cekHakakses('payrollkomponenmaster','uhm')) {
                $columns = array('', 'urutan', 'nama', 'kode', 'tipedata', 'carainput', 'digunakan', 'tampilkan',);
            }else{
                $columns = array('urutan', 'nama', 'kode', 'tipedata', 'carainput', 'digunakan', 'tampilkan',);
            }
            $table = '(SELECT id,idpayroll_kelompok,nama,kode,IFNULL(tipedata,"") as tipedata,carainput,urutan_tampilan as urutan,digunakan,tampilkan FROM payroll_komponen_master) x';
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
                    $iconubahscript = 'fa-code';
                    if($key['carainput'] == 'inputmanual'){
                        $iconubahscript = 'fa-terminal';
                    }
                    $action = '';
                    if(Utils::cekHakakses('payrollkomponenmaster','um')){
                        $action .= '<a title="' . trans('all.script') . '" href="komponenmaster/' . $key['id'] . '/script"><i class="fa '.$iconubahscript.'" style="color:#1ab394"></i></a>&nbsp;&nbsp;';
                        $action .= Utils::tombolManipulasi('ubah','komponenmaster',$key['id']);
                    }
                    if(Utils::cekHakakses('payrollkomponenmaster','hm')){
                        $action .= Utils::tombolManipulasi('hapus','komponenmaster',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'carainput' || $columns[$i] == 'tipedata') {
                            $tempdata[$columns[$i]] = $key[$columns[$i]] != '' ? trans('all.'.$key[$columns[$i]]) : '';
                        }elseif($columns[$i] == 'digunakan' || $columns[$i] == 'tampilkan') {
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]]);
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

	public function create($idpayrollkelompok)
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('payrollkomponenmaster','tm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
            if ($batasan!='') {
                $batasan = ' AND an.id IN '.$batasan;
            }
            
            $sql = 'SELECT a.id,a.atribut as nama,IFNULL(a.kode,"") as kode FROM atribut a,atributnilai an WHERE an.idatribut=a.id'.$batasan.' GROUP BY a.id ORDER BY a.atribut ASC, a.kode ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $dataatribut = $stmt->fetchAll(PDO::FETCH_OBJ);

            foreach ($dataatribut as $row) {
                // ambil data atributnilai
                $sql = 'SELECT
                            an.id,
                            an.nilai as nama,
                            an.kode
                        FROM
                            atributnilai an
                        WHERE
                            an.idatribut=:idatribut
                            '.$batasan.'
                        ORDER BY
                            an.urutan ASC, an.nilai ASC, kode ASC';
                $stmt2 = $pdo->prepare($sql);
                $stmt2->bindValue(':idatribut', $row->id);
                $stmt2->execute();

                $row->atributnilai=$stmt2->fetchAll(PDO::FETCH_OBJ);
            }
            $dataatributvariable = Utils::getData($pdo,'atributvariable','id,atribut as nama,kode','','kode ASC, atribut ASC');
            $datapayrollkomponenmaster = Utils::getData($pdo,'payroll_komponen_master','id,nama,kode','','kode ASC, nama ASC');
            $datapayrollkomponenmastergroup = Utils::getData($pdo,'payroll_komponen_master_group','id,nama','','nama ASC');
            Utils::insertLogUser('akses menu tambah payroll komponen master');
            return view('datainduk/payroll/payrollkomponenmaster/create', ['dataatribut' =>  $dataatribut, 'idpayrollkelompok' => $idpayrollkelompok, 'dataatributvariable' => $dataatributvariable, 'datapayrollkomponenmaster' => $datapayrollkomponenmaster, 'datapayrollkomponenmastergroup' => $datapayrollkomponenmastergroup, 'menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request, $idpayrollkelompok)
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y'){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id FROM payroll_komponen_master WHERE kode = :kode';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':kode', $request->kode);
            $stmt->execute();
            if($stmt->rowCount() == 0){
                $sql = 'INSERT INTO payroll_komponen_master VALUES(NULL,:idpayroll_kelompok,:nama,:kode,:tipedata,:carainput,"","",:idpayroll_komponen_master_group,0,0,0,:digunakan,:tampilkan,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpayroll_kelompok', $idpayrollkelompok);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':kode', $request->kode);
                $stmt->bindValue(':tipedata', $request->tipedata);
                $stmt->bindValue(':carainput', $request->carainput);
                $stmt->bindValue(':idpayroll_komponen_master_group', $request->payrollkomponenmastergroup == '' ? NULL : $request->payrollkomponenmastergroup);
                $stmt->bindValue(':digunakan', $request->digunakan);
                $stmt->bindValue(':tampilkan', $request->tampilkan);
                $stmt->execute();

                Utils::insertLogUser('Tambah payoll komponen master "'.$request->nama.'"');
        
                return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/komponenmaster')->with('message', trans('all.databerhasildisimpan'));
            }else{
                return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/komponenmaster/create')->with('message', trans('all.kodesudahdigunakan'));
            }
        }else{
            return redirect('/');
        }
    }
    
    public function edit($idpayrollkelompok, $id)
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('payrollkomponenmaster','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM payroll_komponen_master WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$data){
                abort(404);
            }
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
            if ($batasan!='') {
                $batasan = ' AND an.id IN '.$batasan;
            }

            $sql = 'SELECT a.id,a.atribut as nama,IFNULL(a.kode,"") as kode FROM atribut a,atributnilai an WHERE an.idatribut=a.id'.$batasan.' GROUP BY a.id ORDER BY a.atribut ASC, a.kode ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $dataatribut = $stmt->fetchAll(PDO::FETCH_OBJ);

            foreach ($dataatribut as $row) {
                // ambil data atributnilai
                $sql = 'SELECT
                            an.id,
                            an.nilai as nama,
                            an.kode
                        FROM
                            atributnilai an
                        WHERE
                            an.idatribut=:idatribut
                            '.$batasan.'
                        ORDER BY
                            an.urutan ASC, an.nilai ASC, kode ASC';
                $stmt2 = $pdo->prepare($sql);
                $stmt2->bindValue(':idatribut', $row->id);
                $stmt2->execute();

                $row->atributnilai=$stmt2->fetchAll(PDO::FETCH_OBJ);
            }
            $dataatributvariable = Utils::getData($pdo,'atributvariable','id,atribut as nama,kode','','kode ASC, atribut ASC');
            $datapayrollkomponenmaster = Utils::getData($pdo,'payroll_komponen_master','id,nama,kode','','kode ASC, nama ASC');
            $datapayrollkomponenmastergroup = Utils::getData($pdo,'payroll_komponen_master_group','id,nama','','nama ASC');
            Utils::insertLogUser('akses menu ubah payroll komponen master');
            return view('datainduk/payroll/payrollkomponenmaster/edit', ['dataatribut' => $dataatribut, 'dataatributvariable' => $dataatributvariable, 'datapayrollkomponenmaster' => $datapayrollkomponenmaster, 'datapayrollkomponenmastergroup' => $datapayrollkomponenmastergroup, 'data' => $data, 'menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $idpayrollkelompok, $id)
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y'){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama FROM payroll_komponen_master WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $sql = 'SELECT id FROM payroll_komponen_master WHERE kode = :kode AND id <> :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':kode', $request->kode);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                if($stmt->rowCount() == 0){
                    $sql = 'UPDATE payroll_komponen_master SET nama = :nama, kode = :kode, tipedata = :tipedata, carainput = :carainput, idpayroll_komponen_master_group = :idpayroll_komponen_master_group, digunakan = :digunakan, tampilkan = :tampilkan, updated = NOW() WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':nama', $request->nama);
                    $stmt->bindValue(':kode', $request->kode);
                    $stmt->bindValue(':tipedata', $request->tipedata);
                    $stmt->bindValue(':carainput', $request->carainput);
                    $stmt->bindValue(':idpayroll_komponen_master_group', $request->payrollkomponenmastergroup == '' ? NULL : $request->payrollkomponenmastergroup);
                    $stmt->bindValue(':digunakan', $request->digunakan);
                    $stmt->bindValue(':tampilkan', $request->tampilkan);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah payroll komponen master "'.$row['nama'].'" => "'.$request->nama.'"');
        
                    return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/komponenmaster')->with('message', trans('all.databerhasildiubah'));
                }else{
                    $msg = trans('all.kodesudahdigunakan');
                }
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/komponenmaster/'.$id.'/edit')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function destroy($idpayrollkelompok, $id)
    {
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('payrollkomponenmaster','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama FROM payroll_komponen_master WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                Utils::deleteData($pdo,'payroll_komponen_master',$id);
                Utils::insertLogUser('Hapus payroll komponen master "'.$row['nama'].'"');
                return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/komponenmaster')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/komponenmaster')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function script($idpayrollkelompok, $id){
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('payrollkomponenmaster','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT
                        id,
                        nama,
                        kode,
                        carainput,
                        inputmanual_filter,
                        formula
                    FROM
                        payroll_komponen_master
                    WHERE
                        id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id',$id);
            $stmt->execute();
            $data = '';
            if($stmt->rowCount() > 0){
                $data = $stmt->fetch(PDO::FETCH_OBJ);
            }

            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
            if ($batasan!='') {
                $batasan = ' AND an.id IN '.$batasan;
            }
            
            $sql = 'SELECT a.id,a.atribut as nama,IFNULL(a.kode,"") as kode FROM atribut a,atributnilai an WHERE an.idatribut=a.id'.$batasan.' GROUP BY a.id ORDER BY a.atribut ASC, a.kode ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $dataatribut = $stmt->fetchAll(PDO::FETCH_OBJ);

            foreach ($dataatribut as $row) {
                // ambil data atributnilai
                $sql = 'SELECT
                            an.id,
                            an.nilai as nama,
                            an.kode
                        FROM
                            atributnilai an
                        WHERE
                            an.idatribut=:idatribut
                            '.$batasan.'
                        ORDER BY
                            an.urutan ASC, an.nilai ASC, kode ASC';
                $stmt2 = $pdo->prepare($sql);
                $stmt2->bindValue(':idatribut', $row->id);
                $stmt2->execute();

                $row->atributnilai=$stmt2->fetchAll(PDO::FETCH_OBJ);
            }
            $dataatributvariable = Utils::getData($pdo,'atributvariable','id,atribut as nama,kode','','kode ASC, atribut ASC');
            $datapayrollkomponenmaster = Utils::getData($pdo,'payroll_komponen_master','id,nama,kode,tipedata','digunakan="y" AND idpayroll_kelompok = '.$idpayrollkelompok,'urutan ASC, kode ASC, nama ASC');
            $datapayrollkomponenmastergroup = Utils::getData($pdo,'payroll_komponen_master_group','id,nama','','nama ASC');
            $valuetglawalakhir = Utils::valueTanggalAwalAkhir();
            Utils::insertLogUser('akses menu payroll komponen master script');
            return view('datainduk/payroll/payrollkomponenmaster/script', ['idpayrollkelompok' => $idpayrollkelompok, 'valuetglawalakhir' => $valuetglawalakhir, 'data' => $data, 'dataatribut' =>  $dataatribut, 'dataatributvariable' => $dataatributvariable, 'datapayrollkomponenmaster' => $datapayrollkomponenmaster, 'datapayrollkomponenmastergroup' => $datapayrollkomponenmastergroup, 'menu' => 'payrollkomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function scriptSubmit(Request $request, $idpayrollkelompok, $id){
        if(Session::get('perbolehkanpayroll_perusahaan') == 'y'){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'payroll_komponen_master','nama','id',$id);
            if($cekadadata != ''){
                $carainput = Utils::getDataWhere($pdo,'payroll_komponen_master','carainput','id',$id);
                $script = '';
                if($carainput == 'inputmanual'){
                    $script = $request->inputmanual_filter;
                }else if($carainput == 'formula'){
                    $script = $request->formula;
                }
                $cekeval = Utils::eval_not_evil($script);
                if($cekeval === ''){
                    $sql = 'UPDATE payroll_komponen_master SET inputmanual_filter = :inputmanual_filter, formula = :formula, updated = NOW() WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':inputmanual_filter', $request->inputmanual_filter);
                    $stmt->bindValue(':formula', $request->formula);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah script payroll komponen master "'.$cekadadata.'"');
        
                    return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/komponenmaster')->with('message', trans('all.databerhasildiubah'));
                }else{
                    $msg = $cekeval;
                }
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/komponenmaster'.$id.'/script')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function tesOutput(Request $request){
        $idpegawai = $request->idpegawai;
        $idkomponenmaster = $request->idkomponenmaster;
        $idpayrollkelompok = $request->idpayrollkelompok;
        $komponenmaster = json_decode($request->komponenmaster);
        $tanggalawal = Utils::convertDmy2Ymd($request->tanggalawal);
        $tanggalakhir = Utils::convertDmy2Ymd($request->tanggalakhir);
        $formula = $request->script;
        $tahunawal = Utils::getYearFromDate($request->tanggalawal,'/');
        $tahunakhir = Utils::getYearFromDate($request->tanggalakhir,'/');

        $tanggalawal_str = strtotime($tanggalawal);
        $tanggalakhir_str = strtotime($tanggalakhir);
        $jumlahhari_diff = $tanggalakhir_str - $tanggalawal_str;
        $selisihhari = round($jumlahhari_diff / (60 * 60 * 24)) + 1;

        // cek jumlah cuti
        if($tahunawal != $tahunakhir){
            $cutitahunawal = Utils::getJatahCuti($tahunawal,$idpegawai);
            $cutitahunakhir = Utils::getJatahCuti($tahunakhir,$idpegawai);
            $cuti = $cutitahunawal + $cutitahunakhir;
        }else{
            $cuti = Utils::getJatahCuti($tahunakhir,$idpegawai);
        }

        $result = '';
        if($formula != ''){
            $cekeval = Utils::eval_not_evil($formula);
            if($cekeval === ''){
                $pdo = DB::connection('perusahaan_db')->getPdo();
                $COUNTER = 1;
                //ambil data komponen_master
                $sql = 'SELECT
                            pkm.id as id,
                            pkm.nama as nama,
                            lower(pkm.kode) as kode,
                            pkm.tipedata as tipedata,
                            pkm.carainput as carainput,
                            pkm.formula,
                            IF(ISNULL(pp.komponenmaster_total)=FALSE,"y","t") as istotal,
                            IFNULL(pkmg.nama,"") as `group`,
                            pkm.urutan as urutan
                        FROM
                            payroll_komponen_master pkm
                            LEFT JOIN payroll_komponen_master_group pkmg ON pkmg.id = pkm.idpayroll_komponen_master_group
                            LEFT JOIN payroll_pengaturan pp ON pp.komponenmaster_total = pkm.id
                        WHERE
                            pkm.digunakan="y" AND
                            pkm.idpayroll_kelompok = :idpayrollkelompok
                        ORDER BY
                            pkm.urutan ASC, pkm.kode ASC, pkm.nama ASC';
                $stmt = $pdo->prepare($sql);
                // $stmt->bindValue(':idkomponenmaster', $idkomponenmaster);
                 $stmt->bindValue(':idpayrollkelompok', $idpayrollkelompok);
                $stmt->execute();
                $komponen_master = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $sql = 'SELECT
                            p.nama,
                            p.idagama,
                            IFNULL(a.agama, "") as agama,
                            p.pin,
                            p.pemindai,
                            p.nomorhp,
                            p.flexytime,
                            p.status,
                            p.tanggalaktif,
                            '.$cuti.' as lamacuti,
                            lower(payroll_getatributnilai(p.id)) as payroll_atributnilai,
                            lower(payroll_getatributvariable(p.id)) as payroll_atributvariable
                        FROM
                            pegawai p
                            LEFT JOIN agama a ON a.id=p.idagama
                        WHERE 
                            p.id = :idpegawai';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $idpegawai);
                $stmt->execute();
                if ($stmt->rowCount()>0) {
                    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $nama = $row[0]['nama'];

                    Utils::payroll_fetch_pegawai($PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $row[0]);

                    $JAMKERJA = array();
                    $JADWALSHIFT = array();
                    $ijs = 0;
                    for($i = 0;$i<$selisihhari;$i++){
                        $tgl = date('Y-m-d', strtotime(date('Y-m-d',$tanggalawal_str) . ' +'.$i.' day'));

                        // untuk $JAMKERJA
                        $JAMKERJA[$i]['idjamkerja'] = Utils::getPegawaiJamKerja('id',$idpegawai,$tgl);
                        $JAMKERJA[$i]['jamkerja'] =  Utils::getPegawaiJamKerja('nama',$idpegawai,$tgl);
                        $JAMKERJA[$i]['jenisjamkerja'] =  Utils::getPegawaiJamKerja('jenis',$idpegawai,$tgl);

                        // untuk $JADWALSHIFT
                        $datajadwalshift = Utils::getPegawaiJadwalShift($idpegawai,$tgl);
                        if(count($datajadwalshift) > 0){
                            for($j=0;$j<count($datajadwalshift);$j++){
                                $JADWALSHIFT[$ijs]['idjamkerjashift'] = $datajadwalshift[$j]['idjamkerjashift'];
                                $JADWALSHIFT[$ijs]['namashift'] = $datajadwalshift[$j]['idjamkerjashift'];
                                $JADWALSHIFT[$ijs]['kode'] = $datajadwalshift[$j]['idjamkerjashift'];
                                $ijs++;
                            }
                        }
                    }

                    // ambil rekapabsen
                    $sql = "SELECT * FROM rekapabsen WHERE idpegawai=:idpegawai AND tanggal BETWEEN :tanggalawal AND :tanggalakhir";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(":idpegawai",$idpegawai);
                    $stmt->bindValue(":tanggalawal",$tanggalawal);
                    $stmt->bindValue(":tanggalakhir",$tanggalakhir);
                    $stmt->execute();
                    $REKAPABSEN = $stmt->fetchAll(PDO::FETCH_ASSOC);
//                    return $idpegawai.' '.$tanggalawal.' '.$tanggalakhir;

                    // ambil komponen_master
                    $PAYROLL = array();
                    for($i=0;$i<count($komponen_master);$i++) {
                        if($komponen_master[$i]['id'] == $komponenmaster[$i]->id){
                            if($komponen_master[$i]['tipedata'] != 'teks'){
                                $PAYROLL[$komponen_master[$i]['kode']] = $komponenmaster[$i]->value != '' ? intval($komponenmaster[$i]->value) : 0;
                            }else{
                                $PAYROLL[$komponen_master[$i]['kode']] = $komponenmaster[$i]->value;
                            }
                        }
                    }
                    
                    $script = '';
                    // buat formula menjadi temporary fungsi (function_i)
                    for($i=0;$i<count($komponen_master);$i++) {
                        if($komponen_master[$i]['id'] == $idkomponenmaster){
                            $lines = explode(PHP_EOL, $formula);
                            $temp_formula = '';
                            for($j=0;$j<count($lines);$j++) {
                                $temp_formula = $temp_formula . '   '. $lines[$j].PHP_EOL;
                            }
                            $temp_formula = PHP_EOL.'$formula_'.$i.' = function($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $REKAPABSEN, $PAYROLL, $JAMKERJA, $JADWALSHIFT){'.PHP_EOL.$temp_formula. '  return $result;'.PHP_EOL.'};'.PHP_EOL;
                            $script = $script . $temp_formula;
                        }
                    }
                    $script = $script . PHP_EOL;
                    // panggil temporary function_i
                    for($i=0;$i<count($komponen_master);$i++) {
                        if($komponen_master[$i]['id'] == $idkomponenmaster){
                            $kode = strtolower($komponen_master[$i]['kode']);
                            $script = $script.'$PAYROLL["'.$kode.'"] = $formula_'.$i.'($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $REKAPABSEN, $PAYROLL, $JAMKERJA, $JADWALSHIFT);'.PHP_EOL;
                        }
                    }
                    // buang (unset) temporary function_i tersebut
                    $script = $script.PHP_EOL;
                    for($i=0;$i<count($komponen_master);$i++) {
                        if($komponen_master[$i]['id'] == $idkomponenmaster){
                            // $kode = strtolower($komponen_master[$i]['kode']);
                            if ($komponen_master[$i]['carainput']=='formula' && $komponen_master[$i]['formula']!='') {
                                $script = $script.'unset($formula_'.$i.');'.PHP_EOL;
                            }
                        }
                    }
                    $script = $script.PHP_EOL;
                    $script = $script.'unset($get);'.PHP_EOL;
                    $script = $script.'unset($get_counter);'.PHP_EOL;

                    Utils::payroll_replace_variablescript($script);
                    try {
                        eval($script);
                    } catch (\ParseError $e) {
                        return $e->getMessage();
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }

                    $res = array();
                    for ($i=0;$i<count($komponen_master);$i++) {
                        if($komponen_master[$i]['id'] == $idkomponenmaster){
                            $res[$i] = $PAYROLL[$komponen_master[$i]['kode']];
                            if($komponen_master[$i]['id'] == $idkomponenmaster){
                                $result = $PAYROLL[$komponen_master[$i]['kode']];
                            }
                            if($komponen_master[$i]['tipedata'] == 'uang'){
                                $result = is_numeric($result) ? number_format($result,0,',','.') : $result;
                            }
                        }
                    }
                    return $result;
//                    return is_numeric($result) ? number_format($result,0,',','.') : $result;
//                    return $result;
                }
            }else{
                return $cekeval;
            }
        }
        return '';
    }

    public function excel($idpayrollkelompok)
    {
        if (Session::get('perbolehkanpayroll_perusahaan') == 'y' && Utils::cekHakakses('payrollkomponenmaster','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.payrollkomponenmaster'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.urutan'))
                        ->setCellValue('B1', trans('all.nama'))
                        ->setCellValue('C1', trans('all.kode'))
                        ->setCellValue('D1', trans('all.tipedata'))
                        ->setCellValue('E1', trans('all.carainput'))
                        ->setCellValue('F1', trans('all.digunakan'))
                        ->setCellValue('G1', trans('all.tampilkan'));

            $sql = 'SELECT
                        id,
                        nama,
                        kode,
                        tipedata,
                        carainput,
                        urutan_tampilan as urutan,
                        digunakan,
                        tampilkan
                    FROM
                        payroll_komponen_master
                    WHERE
                        idpayroll_kelompok = '.$idpayrollkelompok.'
                    ORDER BY
                        urutan ASC, nama ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['urutan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['kode']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, trans('all.'.$row['tipedata']));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, trans('all.'.$row['carainput']));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['digunakan'] == 'y' ? trans('all.ya') : trans('all.tidak'));
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['tampilkan'] == 'y' ? trans('all.ya') : trans('all.tidak'));

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor payroll komponen master');
            $arrWidth = array(8, 35, 19, 20, 15, 10);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.payrollkomponenmaster') . '_'.Utils::getDataWhere($pdo,'payroll_kelompok','nama','id',$idpayrollkelompok));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}