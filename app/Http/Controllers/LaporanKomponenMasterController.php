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

class LaporanKomponenMasterController extends Controller
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

	public function getindex($idkelompok)
	{
        if(Utils::cekHakakses('laporancustom','l')){
            $totaldata = Utils::getTotalData(1,'laporan_komponen_master','idlaporan_kelompok = '.$idkelompok);
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $kelompok = Utils::getDataWhere($pdo,'laporan_kelompok','nama','id',$idkelompok);
            Utils::insertLogUser('akses menu laporan komponen master');
	        return view('laporan/custom/komponenmaster/index', ['totaldata' => $totaldata, 'idlaporankelompok' => $idkelompok, 'kelompok' => $kelompok, 'menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request, $idlaporankelompok)
	{
        if(Utils::cekHakakses('laporancustom','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','urutan','nama','kode','tipekolom','tipedata','carainput','digunakan','tampilkan');
            $table = '(SELECT id,idlaporan_kelompok,nama,kode,tipekolom,IFNULL(tipedata,"") as tipedata,carainput,urutan_tampilan as urutan,digunakan,tampilkan FROM laporan_komponen_master) x';
//            $totalData = Utils::getDataCustomWhere($pdo,'laporan_komponen_master', 'count(id)',' 1=1 '.$where);
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idlaporan_kelompok = :idlaporan_kelompok '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idlaporan_kelompok', $idlaporankelompok);
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
                $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idlaporan_kelompok = :idlaporan_kelompok '.$where;
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idlaporan_kelompok', $idlaporankelompok);
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                    }
                }
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalFiltered = $row['total'];
            }

            $sql = 'SELECT * FROM '.$table.' WHERE idlaporan_kelompok = :idlaporan_kelompok ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idlaporan_kelompok', $idlaporankelompok);
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
                    $iconubahscript = $key['carainput'] == 'inputmanual' ? 'fa-terminal' :'fa-code';
                    $action = '';
                    if(Utils::cekHakakses('laporancustom','um')){
                        $action .= '<a title="' . trans('all.script') . '" href="komponenmaster/' . $key['id'] . '/script"><i class="fa '.$iconubahscript.'" style="color:#1ab394"></i></a>&nbsp;&nbsp;';
                        $action .= Utils::tombolManipulasi('ubah','komponenmaster',$key['id']);
                    }
                    if(Utils::cekHakakses('laporancustom','um')){
                        $action .= Utils::tombolManipulasi('hapus','komponenmaster',$key['id']);
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        if($columns[$i] == 'carainput' || $columns[$i] == 'tipedata' || $columns[$i] == 'tipekolom') {
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

	public function create($idlaporankelompok)
    {
        if(Utils::cekHakakses('laporancustom','tm')){
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
            $datalaporankomponenmaster = Utils::getData($pdo,'laporan_komponen_master','id,nama,kode','','kode ASC, nama ASC');
            $datalaporankomponenmastergroup = Utils::getData($pdo,'laporan_komponen_master_group','id,nama','','nama ASC');
            $jenislaporankelompok = Utils::getDataWhere($pdo,'laporan_kelompok','jenis','id',$idlaporankelompok);
            Utils::insertLogUser('akses menu tambah laporan komponen master');
            return view('laporan/custom/komponenmaster/create', ['dataatribut' =>  $dataatribut, 'idlaporankelompok' => $idlaporankelompok, 'jenislaporankelompok' => $jenislaporankelompok, 'dataatributvariable' => $dataatributvariable, 'datalaporankomponenmaster' => $datalaporankomponenmaster, 'datalaporankomponenmastergroup' => $datalaporankomponenmastergroup, 'menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request, $idlaporankelompok)
    {
        if(Utils::cekHakakses('laporancustom','tm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'laporan_komponen_master','id','kode',$request->kode);
            if($cekadadata == ''){
                $sql = 'INSERT INTO laporan_komponen_master VALUES(NULL,:idlaporan_kelompok,:nama,:kode,:tipekolom,:tipedata,:carainput,"","",:idlaporan_komponen_master_group,0,0,:digunakan,:tampilkan,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idlaporan_kelompok', $idlaporankelompok);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':kode', $request->kode);
                $stmt->bindValue(':tipekolom', $request->tipekolom);
                $stmt->bindValue(':tipedata', $request->tipedata);
                $stmt->bindValue(':carainput', $request->carainput);
                $stmt->bindValue(':idlaporan_komponen_master_group', $request->laporankomponenmastergroup == '' ? NULL : $request->laporankomponenmastergroup);
                $stmt->bindValue(':digunakan', $request->digunakan);
                $stmt->bindValue(':tampilkan', $request->tampilkan);
                $stmt->execute();

                Utils::insertLogUser('Tambah payoll komponen master "'.$request->nama.'"');
        
                return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster')->with('message', trans('all.databerhasildisimpan'));
            }else{
                return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster/create')->with('message', trans('all.kodesudahdigunakan'));
            }
        }else{
            return redirect('/');
        }
    }
    
    public function edit($idlaporankelompok, $id)
    {
        if(Utils::cekHakakses('laporancustom','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM laporan_komponen_master WHERE id = :id';
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
            $datalaporankomponenmaster = Utils::getData($pdo,'laporan_komponen_master','id,nama,kode','','kode ASC, nama ASC');
            $datalaporankomponenmastergroup = Utils::getData($pdo,'laporan_komponen_master_group','id,nama','','nama ASC');
            $jenislaporankelompok = Utils::getDataWhere($pdo,'laporan_kelompok','jenis','id',$idlaporankelompok);
            Utils::insertLogUser('akses menu ubah laporan komponen master');
            return view('laporan/custom/komponenmaster/edit', ['dataatribut' => $dataatribut, 'idlaporankelompok' => $idlaporankelompok, 'jenislaporankelompok' => $jenislaporankelompok, 'dataatributvariable' => $dataatributvariable, 'datalaporankomponenmaster' => $datalaporankomponenmaster, 'datalaporankomponenmastergroup' => $datalaporankomponenmastergroup, 'data' => $data, 'menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $idlaporankelompok, $id)
    {
        if(Utils::cekHakakses('laporancustom','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'laporan_komponen_master','nama','id',$id);
            if($cekadadata != ''){
                $cekkembar = Utils::getData($pdo,'laporan_komponen_master','id','kode = "'.$request->kode.'" AND id<>'.$id.' LIMIT 1');
                if($cekkembar == ''){
                    $sql = 'UPDATE laporan_komponen_master SET nama = :nama, kode = :kode, tipekolom = :tipekolom, tipedata = :tipedata, carainput = :carainput, idlaporan_komponen_master_group = :idlaporan_komponen_master_group, digunakan = :digunakan, tampilkan = :tampilkan, updated = NOW() WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':nama', $request->nama);
                    $stmt->bindValue(':kode', $request->kode);
                    $stmt->bindValue(':tipekolom', $request->tipekolom);
                    $stmt->bindValue(':tipedata', $request->tipedata);
                    $stmt->bindValue(':carainput', $request->carainput);
                    $stmt->bindValue(':idlaporan_komponen_master_group', $request->laporankomponenmastergroup == '' ? NULL : $request->laporankomponenmastergroup);
                    $stmt->bindValue(':digunakan', $request->digunakan);
                    $stmt->bindValue(':tampilkan', $request->tampilkan);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah laporan komponen master "'.$cekadadata.'" => "'.$request->nama.'"');
        
                    return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster')->with('message', trans('all.databerhasildiubah'));
                }else{
                    $msg = trans('all.kodesudahdigunakan');
                }
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster'.$id.'/edit')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function destroy($idlaporankelompok, $id)
    {
        if(Utils::cekHakakses('laporancustom','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'laporan_komponen_master','nama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'laporan_komponen_master',$id);
                Utils::insertLogUser('Hapus laporan komponen master "'.$cekadadata.'"');
                return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function script($idlaporankelompok, $id){
        if(Utils::cekHakakses('laporancustom','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT
                        id,
                        nama,
                        kode,
                        carainput,
                        inputmanual_filter,
                        formula
                    FROM
                        laporan_komponen_master
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
            $valuetglawalakhir = Utils::valueTanggalAwalAkhir();
            $dataatributvariable = Utils::getData($pdo,'atributvariable','id,atribut as nama,kode','','kode ASC, atribut ASC');
            $datalaporankomponenmaster = Utils::getData($pdo,'laporan_komponen_master','id,nama,kode,tipedata','digunakan="y" AND idlaporan_kelompok='.$idlaporankelompok,'urutan_tampilan ASC, kode ASC, nama ASC');
            $datalaporankomponenmastergroup = Utils::getData($pdo,'laporan_komponen_master_group','id,nama','','nama ASC');
            $jenislaporankelompok = Utils::getDataWhere($pdo,'laporan_kelompok','jenis','id',$idlaporankelompok);
            Utils::insertLogUser('akses menu laporan komponen master script');
            return view('laporan/custom/komponenmaster/script', ['idlaporankelompok' => $idlaporankelompok, 'jenislaporankelompok' => $jenislaporankelompok, 'valuetglawalakhir' => $valuetglawalakhir, 'data' => $data, 'dataatribut' =>  $dataatribut, 'dataatributvariable' => $dataatributvariable, 'datalaporankomponenmaster' => $datalaporankomponenmaster, 'datalaporankomponenmastergroup' => $datalaporankomponenmastergroup, 'menu' => 'laporankomponenmaster']);
        }else{
            return redirect('/');
        }
    }

    public function scriptSubmit(Request $request, $idlaporankelompok, $id){
        if(Utils::cekHakakses('laporancustom','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'laporan_komponen_master','nama','id',$id);
            if($cekadadata != ''){
                $carainput = Utils::getDataWhere($pdo,'laporan_komponen_master','carainput','id',$id);
                $script = '';
                if($carainput == 'inputmanual'){
                    $script = $request->inputmanual_filter;
                }else if($carainput == 'formula'){
                    $script = $request->formula;
                }
                $cekeval = Utils::eval_not_evil($script);
                if($cekeval === ''){
                    $sql = 'UPDATE laporan_komponen_master SET inputmanual_filter = :inputmanual_filter, formula = :formula, updated = NOW() WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':inputmanual_filter', $request->inputmanual_filter);
                    $stmt->bindValue(':formula', $request->formula);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah script laporan komponen master "'.$cekadadata.'"');
        
                    return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster')->with('message', trans('all.databerhasildiubah'));
                }else{
                    $msg = $cekeval;
                }
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster/'.$id.'/script')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function tesOutput(Request $request){
	    $idlaporankelompok = $request->idlaporankelompok;
	    $idpegawai = $request->idpegawai;
        $idkomponenmaster = $request->idkomponenmaster;
        $komponenmaster = json_decode($request->komponenmaster);
        $formula = $request->script;
        $result = '';
        if($formula != ''){
            if(isset($request->tanggal)) {
                if (!Utils::cekDateTime($request->tanggal)) {
                    return '';
                }
            }
            if(isset($request->tanggalawal)) {
                if (!Utils::cekDateTime($request->tanggalawal)) {
                    return '';
                }
            }
            if(isset($request->tanggalakhir)) {
                if (!Utils::cekDateTime($request->tanggalakhir)) {
                    return '';
                }
            }

            $cekeval = Utils::eval_not_evil($formula);
            if($cekeval === ''){
                $pdo = DB::connection('perusahaan_db')->getPdo();

                //ambil data komponen_master
                $sql = 'SELECT
                                pkm.id as id,
                                pkm.nama as nama,
                                lower(pkm.kode) as kode,
                                pkm.tipedata as tipedata,
                                pkm.carainput as carainput,
                                pkm.formula,
                                IFNULL(pkmg.nama,"") as `group`,
                                pkm.urutan_tampilan as urutan
                            FROM
                                laporan_komponen_master pkm
                                LEFT JOIN laporan_komponen_master_group pkmg ON pkmg.id = pkm.idlaporan_komponen_master_group
                            WHERE
                                pkm.digunakan="y" AND
                                pkm.idlaporan_kelompok = :idlaporankelompok
                            ORDER BY
                                pkm.urutan_tampilan ASC, pkm.urutan_perhitungan ASC, pkm.kode ASC, pkm.nama ASC';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idlaporankelompok', $idlaporankelompok);
                $stmt->execute();
                $komponen_master = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $jenislaporan = Utils::getDataWhere($pdo,'laporan_kelompok','jenis','id',$idlaporankelompok);
                if($jenislaporan == 'rekap') {
                    $tanggalawal = Utils::convertDmy2Ymd($request->tanggalawal);
                    $tanggalakhir = Utils::convertDmy2Ymd($request->tanggalakhir);
                    $tahunawal = Utils::getYearFromDate($request->tanggalawal,'/');
                    $tahunakhir = Utils::getYearFromDate($request->tanggalakhir,'/');

                    $tanggalawal_str = strtotime($tanggalawal);
                    $tanggalakhir_str = strtotime($tanggalakhir);
                    $jumlahhari_diff = $tanggalakhir_str - $tanggalawal_str;
                    $selisihhari = round($jumlahhari_diff / (60 * 60 * 24)) + 1;

                    $COUNTER = 1;

                    // cek jumlah cuti
                    if($tahunawal != $tahunakhir){
                        $cutitahunawal = Utils::getJatahCuti($tahunawal,$idpegawai);
                        $cutitahunakhir = Utils::getJatahCuti($tahunakhir,$idpegawai);
                        $cuti = $cutitahunawal + $cutitahunakhir;
                    }else{
                        $cuti = Utils::getJatahCuti($tahunakhir,$idpegawai);
                    }

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
                    if ($stmt->rowCount() > 0) {
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

                        // ambil logabsen
                        $sql = "SELECT * FROM logabsen WHERE idpegawai=:idpegawai AND waktu >= CONCAT(:tanggalawal,' 00:00:00') AND  waktu <= CONCAT(:tanggalakhir,' 23:59:59') ";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(":idpegawai", $idpegawai);
                        $stmt->bindValue(":tanggalawal", $tanggalawal);
                        $stmt->bindValue(":tanggalakhir", $tanggalakhir);
                        $stmt->execute();
                        $LOGABSEN = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // ambil rekapabsen
                        $sql = "SELECT *,getpegawaijamkerja(idpegawai,\"id\",tanggal) as idjamkerja FROM rekapabsen WHERE idpegawai=:idpegawai AND tanggal BETWEEN :tanggalawal AND :tanggalakhir";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(":idpegawai", $idpegawai);
                        $stmt->bindValue(":tanggalawal", $tanggalawal);
                        $stmt->bindValue(":tanggalakhir", $tanggalakhir);
                        $stmt->execute();
                        $REKAPABSEN = $stmt->fetchAll(PDO::FETCH_ASSOC);
//                        return $idpegawai.' '.$tanggalawal.' '.$tanggalakhir;

                        // ambil komponen_master
                        $LAPORAN = array();
                        for ($i = 0; $i < count($komponen_master); $i++) {
                            if ($komponen_master[$i]['id'] == $komponenmaster[$i]->id) {
                                if ($komponen_master[$i]['tipedata'] != 'teks') {
                                    $LAPORAN[$komponen_master[$i]['kode']] = $komponenmaster[$i]->value != '' ? intval($komponenmaster[$i]->value) : 0;
                                } else {
                                    $LAPORAN[$komponen_master[$i]['kode']] = $komponenmaster[$i]->value;
                                }
                            }
                        }

                        $script = '';
                        // buat formula menjadi temporary fungsi (function_i)
                        for ($i = 0; $i < count($komponen_master); $i++) {
                            if ($komponen_master[$i]['id'] == $idkomponenmaster) {
                                $lines = explode(PHP_EOL, $formula);
                                $temp_formula = '';
                                for ($j = 0; $j < count($lines); $j++) {
                                    $temp_formula = $temp_formula . '   ' . $lines[$j] . PHP_EOL;
                                }
                                $temp_formula = PHP_EOL . '$formula_' . $i . ' = function($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $LOGABSEN, $REKAPABSEN, $LAPORAN, $JAMKERJA, $JADWALSHIFT){' . PHP_EOL . $temp_formula . '  return $result;' . PHP_EOL . '};' . PHP_EOL;
                                $script = $script . $temp_formula;
                            }
                        }
                        $script = $script . PHP_EOL;
                        // panggil temporary function_i
                        for ($i = 0; $i < count($komponen_master); $i++) {
                            if ($komponen_master[$i]['id'] == $idkomponenmaster) {
                                $kode = strtolower($komponen_master[$i]['kode']);
                                $script = $script . '$LAPORAN["' . $kode . '"] = $formula_' . $i . '($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $LOGABSEN, $REKAPABSEN, $LAPORAN, $JAMKERJA, $JADWALSHIFT);' . PHP_EOL;
                            }
                        }
                        //buang (unset) temporary function_i tersebut
                        $script = $script . PHP_EOL;
                        for ($i = 0; $i < count($komponen_master); $i++) {
                            if ($komponen_master[$i]['id'] == $idkomponenmaster) {
                                if ($komponen_master[$i]['carainput'] == 'formula' && $komponen_master[$i]['formula'] != '') {
                                    $script = $script . 'unset($formula_' . $i . ');' . PHP_EOL;
                                }
                            }
                        }
                        $script = $script . PHP_EOL;
                        $script = $script . 'unset($get);' . PHP_EOL;
                        $script = $script . 'unset($get_counter);' . PHP_EOL;

                        Utils::payroll_replace_variablescript($script);

                        try {
                            eval($script);
                        } catch (\ParseError $e) {
                            return $e->getMessage();
                        } catch (\Exception $e) {
                            return $e->getMessage();
                        }

                        $res = array();
                        for ($i = 0; $i < count($komponen_master); $i++) {
                            if ($komponen_master[$i]['id'] == $idkomponenmaster) {
                                $res[$i] = $LAPORAN[$komponen_master[$i]['kode']];
                                if ($komponen_master[$i]['id'] == $idkomponenmaster) {
                                    $result = $LAPORAN[$komponen_master[$i]['kode']];
                                }
                                if ($komponen_master[$i]['tipedata'] == 'uang') {
                                    $result = is_numeric($result) ? number_format($result, 0, ',', '.') : $result;
                                }
                            }
                        }
                        return $result;
                    }
                }else{
                    // laporan berjenis detail
                    $tanggal = Utils::convertDmy2Ymd($request->tanggal);
                    $tahun = Utils::getYearFromDate($request->tanggal,'/');

                    $COUNTER = 1;

                    // cek jumlah cuti
                    $sql = 'CALL get_cuti(:tahun)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':tahun', $tahun);
                    $stmt->execute();

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
                                IFNULL(c.lama,0) as lamacuti,
                                lower(payroll_getatributnilai(p.id)) as payroll_atributnilai,
                                lower(payroll_getatributvariable(p.id)) as payroll_atributvariable
                            FROM
                                pegawai p
                                LEFT JOIN agama a ON a.id=p.idagama
                                LEFT JOIN _cuti c ON c.idpegawai=p.id
                            WHERE 
                                p.id = :idpegawai';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        Utils::payroll_fetch_pegawai($PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $row);

                        // ambil logabsen
                        $sql = "SELECT * FROM logabsen WHERE idpegawai=:idpegawai AND waktu >= CONCAT(:tanggalawal,' 00:00:00') AND  waktu <= CONCAT(:tanggalakhir,' 23:59:59') ";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(":idpegawai", $idpegawai);
                        $stmt->bindValue(":tanggalawal", $tanggal);
                        $stmt->bindValue(":tanggalakhir", $tanggal);
                        $stmt->execute();
                        $LOGABSEN = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // ambil rekapabsen left joinkan semua ynag berelasi
//                        $sql = "SELECT * FROM rekapabsen WHERE idpegawai=:idpegawai AND tanggal = :tanggal";
                        $sql = 'SELECT
                                    ra.*,
                                    IFNULL(ha.tanggalawal,"") as harilibur_tanggalawal,
                                    IFNULL(ha.tanggalakhir,"") as harilibur_tanggalakhir,
                                    IFNULL(ha.keterangan,"") as harilibur_keterangan,
                                    IFNULL(atm.alasan,"") as alasantidakmasuk_alasan,
                                    IFNULL(atm.hitunguangmakan,"") as alasantidakmasuk_hitunguangmakan,
                                    IFNULL(jk.nama,"") as jamkerja_nama,
                                    IFNULL(jk.toleransi,"") as jamkerja_toleransi,
                                    IFNULL(jk.acuanterlambat,"") as jamkerja_acuanterlambat,
                                    IFNULL(jk.hitunglemburstlh,"") as jamkerja_hitunglemburstlh,
                                    IFNULL(jk.digunakan,"") as jamkerja_digunakan,
                                    IFNULL(jkk.keterangan,"") as jamkerjakhusus_keterangan,
                                    IFNULL(jkk.tanggalawal,"") as jamkerjakhusus_tanggalawal,
                                    IFNULL(jkk.tanggalakhir,"") as jamkerjakhusus_tanggalakhir,
                                    IFNULL(jkk.toleransi,"") as jamkerjakhusus_toleransi,
                                    IFNULL(jkk.perhitunganjamkerja,"") as jamkerjakhusus_perhitunganjamkerja,
                                    IFNULL(jkk.hitunglemburstlh,"") as jamkerjakhusus_hitunglemburstlh,
                                    IFNULL(jkk.jammasuk,"") as jamkerjakhusus_jammasuk,
                                    IFNULL(jkk.jampulang,"") as jamkerjakhusus_jampulang,
                                    IFNULL(amk.alasan,"") as alasanmasukkeluar_alasan,
                                    IFNULL(amk.icon,"") as alasanmasukkeluar_icon,
                                    IFNULL(amk.tampilsaat,"") as alasanmasukkeluar_tampilsaat,
                                    IFNULL(amk.tampilpadamesin,"") as alasanmasukkeluar,
                                    IFNULL(amk.terhitungkerja,"") as alasanmasukkeluar_terhitungkerja,
                                    IFNULL(amk.digunakan,"") as alasanmasukkeluar_digunakan,
                                    IFNULL(GROUP_CONCAT(TIME(raj.waktu) SEPARATOR " - " ),"") as rekapabsenjadwal_jadwalkerja
                                FROM
                                    rekapabsen ra
                                    LEFT JOIN harilibur ha ON ra.idharilibur=ha.id
                                    LEFT JOIN alasantidakmasuk atm ON ra.idalasantidakmasuk=atm.id
                                    LEFT JOIN jamkerja jk ON ra.idjamkerja=jk.id
                                    LEFT JOIN jamkerjakhusus jkk ON ra.idjamkerjakhusus=jkk.id
                                    LEFT JOIN alasanmasukkeluar amk ON ra.idalasanmasuk=amk.id
                                    -- LEFT JOIN rekapabsen_hasil rah ON ra.id=rah.idrekapabsen
                                    LEFT JOIN rekapabsen_jadwal raj ON ra.id=raj.idrekapabsen AND raj.checking IN ("start", "end")
                                    -- LEFT JOIN rekapabsen_logabsen rala ON ra.id=rala.idrekapabsen
                                    -- LEFT JOIN rekapabsen_logabsen_all ralaa ON ra.id=ralaa.idrekapabsen
                                    -- LEFT JOIN rekapabsen_penempatan rap ON ra.id=rap.idrekapabsen
                                WHERE
                                    ra.idpegawai=:idpegawai AND
                                    ra.tanggal = :tanggal
                                GROUP BY
                                    ra.id';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(":idpegawai", $idpegawai);
                        $stmt->bindValue(":tanggal", $tanggal);
                        $stmt->execute();
                        $REKAPABSEN = [];
                        IF($stmt->rowCount() > 0) {
                            $REKAPABSEN = $stmt->fetch(PDO::FETCH_ASSOC);
                        }

                        // ambil komponen_master
                        $LAPORAN = array();
                        for ($i = 0; $i < count($komponen_master); $i++) {
                            if ($komponen_master[$i]['id'] == $komponenmaster[$i]->id) {
                                if ($komponen_master[$i]['tipedata'] != 'teks') {
                                    $LAPORAN[$komponen_master[$i]['kode']] = $komponenmaster[$i]->value != '' ? intval($komponenmaster[$i]->value) : 0;
                                } else {
                                    $LAPORAN[$komponen_master[$i]['kode']] = $komponenmaster[$i]->value;
                                }
                            }
                        }

                        $script = '';
                        // buat formula menjadi temporary fungsi (function_i)
                        for ($i = 0; $i < count($komponen_master); $i++) {
                            if ($komponen_master[$i]['id'] == $idkomponenmaster) {
                                $lines = explode(PHP_EOL, $formula);
                                $temp_formula = '';
                                for ($j = 0; $j < count($lines); $j++) {
                                    $temp_formula = $temp_formula . '   ' . $lines[$j] . PHP_EOL;
                                }
                                $temp_formula = PHP_EOL . '$formula_' . $i . ' = function($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $LOGABSEN, $REKAPABSEN, $LAPORAN){' . PHP_EOL . $temp_formula . '  return $result;' . PHP_EOL . '};' . PHP_EOL;
                                $script = $script . $temp_formula;
                            }
                        }
                        $script = $script . PHP_EOL;
                        // panggil temporary function_i
                        for ($i = 0; $i < count($komponen_master); $i++) {
                            if ($komponen_master[$i]['id'] == $idkomponenmaster) {
                                $kode = strtolower($komponen_master[$i]['kode']);
                                $script = $script . '$LAPORAN["' . $kode . '"] = $formula_' . $i . '($COUNTER, $PEGAWAI, $ATRIBUTNILAI, $ATRIBUTVARIABLE, $LOGABSEN, $REKAPABSEN, $LAPORAN);' . PHP_EOL;
                            }
                        }
                        //buang (unset) temporary function_i tersebut
                        $script = $script . PHP_EOL;
                        for ($i = 0; $i < count($komponen_master); $i++) {
                            if ($komponen_master[$i]['id'] == $idkomponenmaster) {
                                if ($komponen_master[$i]['carainput'] == 'formula' && $komponen_master[$i]['formula'] != '') {
                                    $script = $script . 'unset($formula_' . $i . ');' . PHP_EOL;
                                }
                            }
                        }
                        $script = $script . PHP_EOL;
                        $script = $script . 'unset($get);' . PHP_EOL;
                        $script = $script . 'unset($get_counter);' . PHP_EOL;

                        Utils::payroll_replace_variablescript($script);

                        try {
                            eval($script);
                        } catch (\ParseError $e) {
                            return $e->getMessage();
                        } catch (\Exception $e) {
                            return $e->getMessage();
                        }

                        $res = array();
                        for ($i = 0; $i < count($komponen_master); $i++) {
                            if ($komponen_master[$i]['id'] == $idkomponenmaster) {
                                $res[$i] = $LAPORAN[$komponen_master[$i]['kode']];
                                if ($komponen_master[$i]['id'] == $idkomponenmaster) {
                                    $result = $LAPORAN[$komponen_master[$i]['kode']];
                                }
                                if ($komponen_master[$i]['tipedata'] == 'uang') {
                                    $result = is_numeric($result) ? number_format($result, 0, ',', '.') : $result;
                                }
                            }
                        }
                        return $result;
                    }
                }
            }else{
                return $cekeval;
            }
        }
        return '';
    }

    public function excel($idlaporankelompok)
    {
        if(Utils::cekHakakses('laporancustom','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.laporankomponenmaster'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.urutan'))
                        ->setCellValue('B1', trans('all.nama'))
                        ->setCellValue('C1', trans('all.kode'))
                        ->setCellValue('D1', trans('all.tipekolom'))
                        ->setCellValue('E1', trans('all.tipedata'))
                        ->setCellValue('F1', trans('all.carainput'))
                        ->setCellValue('G1', trans('all.digunakan'))
                        ->setCellValue('H1', trans('all.tampilkan'));

            $sql = 'SELECT
                        id,
                        nama,
                        kode,
                        tipekolom,
                        tipedata,
                        carainput,
                        urutan_tampilan as urutan,
                        digunakan,
                        tampilkan
                    FROM
                        laporan_komponen_master
                    WHERE
                        idlaporan_kelompok = '.$idlaporankelompok.'
                    ORDER BY
                        urutan ASC, nama ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['urutan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['kode']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, trans('all.'.$row['tipekolom']));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, trans('all.'.$row['tipedata']));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, trans('all.'.$row['carainput']));
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['digunakan'] == 'y' ? trans('all.ya') : trans('all.tidak'));
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $row['tampilkan'] == 'y' ? trans('all.ya') : trans('all.tidak'));

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor laporan komponen master');
            $arrWidth = array(8, 35, 19, 20, 20, 15, 10);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.laporankomponenmaster') . '_'.Utils::getDataWhere($pdo,'laporan_kelompok','nama','id',$idlaporankelompok));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}