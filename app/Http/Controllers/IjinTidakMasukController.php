<?php
namespace App\Http\Controllers;

use App\IjinTidakMasuk;
use App\Pegawai;
use App\AlasanTidakMasuk;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Worksheet_MemoryDrawing;
use App\Utils;

class IjinTidakMasukController extends Controller
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
        if(Utils::cekHakakses('ijintidakmasuk','l')){
            $tahun = Utils::tahunDropdown();
            if(Session::has('ijintidakmasuk_tahun')){
                $tahunterpilih = Session::get('ijintidakmasuk_tahun');
            }else{
                $tahunterpilih = date('Y');
                Session::set('ijintidakmasuk_tahun', $tahunterpilih);
            }
            Utils::insertLogUser('akses menu ijin tidak masuk');
            return view('datainduk/absensi/ijintidakmasuk/index', ['tahun' => $tahun, 'tahunterpilih' => $tahunterpilih, 'menu' => 'ijintidakmasuk']);
        }else{
            return redirect('/');
        }
	}

    public function submit(Request $request)
    {
        Session::set('ijintidakmasuk_tahun',$request->tahun);
        return redirect('datainduk/absensi/ijintidakmasuk');
    }

	public function show(Request $request)
    {
        if(Utils::cekHakakses('ijintidakmasuk','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $where .= ' AND idpegawai IN ' . $batasan;
            }

            if (Session::has('ijintidakmasuk_tahun')) {
                $where .= ' AND (YEAR(tanggalawal) = ' . Session::get('ijintidakmasuk_tahun') . ' OR YEAR(tanggalakhir) = ' . Session::get('ijintidakmasuk_tahun').')';
            }
            $table = '(
                        SELECT
                            i.id,
                            p.id as idpegawai,
                            p.nama,
                            i.tanggalawal,
                            i.tanggalakhir,
                            IFNULL(a.alasan,"") as alasan,
                            i.keterangan,
                            i.status,
                            i.filename
                        FROM
                            ijintidakmasuk i
                            LEFT JOIN alasantidakmasuk a ON i.idalasantidakmasuk=a.id,
                            pegawai p
                        WHERE
                            i.idpegawai=p.id AND
                            p.status = "a" AND
                            p.del = "t"          
                      ) x';
            $columns = array('', 'nama', 'tanggalawal', 'alasan', 'keterangan', 'status');
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
                    $action = '';
                    if($key['filename'] != ''){
                        $action .= '<a href="' . url('fotonormal/ijintidakmasuk/' . $key['id']) . '" title="' . trans('lampiran') . '" data-gallery=""><i class="fa fa-camera" style="color:#1c84c6"></i></a>&nbsp;&nbsp;';
                    }
                    if(Utils::cekHakakses('ijintidakmasuk','um')){
                        $action .= Utils::tombolManipulasi('ubah','ijintidakmasuk',$key['id']);
                    }
                    if(Utils::cekHakakses('ijintidakmasuk','hm')){
                        $action .= Utils::tombolManipulasi('hapus','ijintidakmasuk',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'tanggalawal') {
                            $tempdata[$columns[$i]] = Utils::tanggalCantikDariSampai($key['tanggalawal'], $key['tanggalakhir']);
                        }elseif($columns[$i] == 'status'){
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]] == 'c' ? 'confirm' : ($key[$columns[$i]] == 'a' ? 'approve' : 'notapprove'));
                        }elseif($columns[$i] == 'nama'){
                            $tempdata[$columns[$i]] = '<span class="detailpegawai" onclick="detailpegawai(' . $key['idpegawai'] . ')" style="cursor:pointer;">' . $key[$columns[$i]] . '</span>';
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
        if(Utils::cekHakakses('ijintidakmasuk','tm')){
            $alasantidakmasuks = AlasanTidakMasuk::select('id','alasan')->where('digunakan', 'y')->get();
            Utils::insertLogUser('akses menu tambah ijin tidak masuk');
            return view('datainduk/absensi/ijintidakmasuk/create', ['alasantidakmasuks' => $alasantidakmasuks, 'menu' => 'ijintidakmasuk']);
        }else{
            return redirect('/');
        }
    }

    // class sementara
    public function getLamaCuti($tahun,$idpegawai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'CALL get_cuti(:tahun)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tahun', $tahun);
        $stmt->execute();

        $sql = 'SELECT lama FROM _cuti WHERE idpegawai = :idpegawai';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        $hasil = 0;
        if($stmt->rowCOunt() > 0) {
            $rowCuti = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil = $rowCuti['lama'] == '' ? 0 : $rowCuti['lama'];
        }
        return $hasil;
    }

    public function store(Request $request)
    {
        if(Utils::cekDateTime($request->tanggalawal) && Utils::cekDateTime($request->tanggalakhir)) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //cek alasantidakmasuk harus ada
            $sql = 'SELECT DATEDIFF(STR_TO_DATE(:tanggalakhir,"%d/%m/%Y"), STR_TO_DATE(:tanggalawal,"%d/%m/%Y")) as lama';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggalawal', $request->tanggalawal);
            $stmt->bindValue(':tanggalakhir', $request->tanggalakhir);
            $stmt->execute();
            if ($stmt->rowCount() != 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row['lama'] > 180) {
                    return Utils::redirectForm(trans('all.ijintidakmasukterlalulama'));
                }
            }
            try {
                $pdo->beginTransaction();
                $idpegawai = $request->pegawai;
                for ($i = 0; $i < count($idpegawai); $i++) {
                    //pastikan idpegawai ada
                    $sql = 'SELECT id FROM pegawai WHERE id=:idpegawai AND status="a" AND del = "t" LIMIT 1';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawai[$i]);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        //cek jumlah cuti apakah melebihi batas cuti ? dan cek alasantidakmasuk harus ada
                        $sql = 'SELECT kategori,DATEDIFF(STR_TO_DATE(:tanggalakhir,"%d/%m/%Y"),STR_TO_DATE(:tanggalawal,"%d/%m/%Y")) as jumlahcuti FROM alasantidakmasuk WHERE id=:idalasantidakmasuk LIMIT 1';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':tanggalawal', $request->tanggalawal);
                        $stmt->bindValue(':tanggalakhir', $request->tanggalakhir);
                        $stmt->bindValue(':idalasantidakmasuk', $request->alasan);
                        $stmt->execute();
                        if ($stmt->rowCount() != 0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $jumlahcuti = $row['jumlahcuti'] + 1;
                            if ($row['kategori'] == 'c') {

                                $jumlahlibur = Utils::cekJumlahLibur(Utils::convertDmy2Ymd($request->tanggalawal), Utils::convertDmy2Ymd($request->tanggalakhir), $idpegawai[$i]);
                                $jumlahcuti = $jumlahcuti - $jumlahlibur;

                                $tahunawal = substr($request->tanggalawal, 6, 6);
                                $tahunakhir = substr($request->tanggalakhir, 6, 6);
                                if ($tahunawal == $tahunakhir) {
                                    $lamacuti = $this->getLamaCuti($tahunawal, $idpegawai[$i]);

                                    $sql = 'CALL get_cuti_pegawai(:tahun,:idpegawai,NULL)';
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->bindValue(':tahun', $tahunawal);
                                    $stmt->bindValue(':idpegawai', $idpegawai[$i]);
                                    $stmt->execute();

                                    $sql = 'SELECT * FROM _cuti_pegawai';
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();
                                    if ($stmt->rowCount() > 0) {
                                        $row1 = $stmt->fetch(PDO::FETCH_ASSOC);
//                                    $sisajatah = $row1['jatah'] - $row1['lama'];
                                        $sisajatah = $row1['jatah'] - $lamacuti; // sementara
                                        if ($lamacuti >= $row1['jatah']) {
                                            $pdo->rollBack();
                                            return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                        } else if ($jumlahcuti > $sisajatah) {
                                            $pdo->rollBack();
                                            return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                        }
                                    }
                                } else {
                                    $lamacuti = $this->getLamaCuti($tahunawal, $idpegawai[$i]);

                                    $sql = 'CALL get_cuti_pegawai(:tahunawal,:idpegawai,NULL)';
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->bindValue(':tahunawal', $tahunawal);
                                    $stmt->bindValue(':idpegawai', $idpegawai[$i]);
                                    $stmt->execute();

                                    $sql = 'SELECT * FROM _cuti_pegawai';
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();
                                    if ($stmt->rowCount() > 0) {
                                        $row1 = $stmt->fetch(PDO::FETCH_ASSOC);
                                        //                            $sisajatah = $row1['jatah'] - $row1['lama'];
                                        $sisajatah = $row1['jatah'] - $lamacuti; // sementara
                                        if ($lamacuti >= $row1['jatah']) {
                                            $pdo->rollBack();
                                            return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                        } else if ($jumlahcuti > $sisajatah) {
                                            $pdo->rollBack();
                                            return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                        }
                                    }

                                    $lamacuti = $this->getLamaCuti($tahunakhir, $idpegawai[$i]);

                                    $sql = 'CALL get_cuti_pegawai(:tahun,:idpegawai,NULL)';
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->bindValue(':tahun', $tahunakhir);
                                    $stmt->bindValue(':idpegawai', $idpegawai[$i]);
                                    $stmt->execute();

                                    $sql = 'SELECT * FROM _cuti_pegawai';
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();
                                    if ($stmt->rowCount() > 0) {
                                        $row1 = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $sisajatah = $row1['jatah'] - $lamacuti;
                                        if ($lamacuti >= $row1['jatah']) {
                                            $pdo->rollBack();
                                            return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                        } else if ($jumlahcuti > $sisajatah) {
                                            $pdo->rollBack();
                                            return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                        }
                                    }
                                }
                            }

                            $sql = 'INSERT INTO ijintidakmasuk VALUES(0,:idpegawai,STR_TO_DATE(:tanggalawal,"%d/%m/%Y"),STR_TO_DATE(:tanggalakhir,"%d/%m/%Y"),:idalasantidakmasuk,:keterangan,"","","a",NOW(),NULL)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idpegawai', $idpegawai[$i]);
                            $stmt->bindValue(':tanggalawal', $request->tanggalawal);
                            $stmt->bindValue(':tanggalakhir', $request->tanggalakhir);
                            $stmt->bindValue(':idalasantidakmasuk', $request->alasan);
                            $stmt->bindValue(':keterangan', $request->keterangan);
                            $stmt->execute();

                            $idijintidakmasuk = $pdo->lastInsertId();

                            // simpan foto jika ada
                            if ($request->hasFile('foto')) {
                                $fotoprofil = $request->file('foto');
                                //cek apakah format jpeg?
                                if ($fotoprofil->getMimeType() == 'image/jpeg' || $fotoprofil->getMimeType() == 'image/png') {
                                    $yyyy = date("Y");
                                    $mm = date("m");
                                    $path = Session::get('folderroot_perusahaan') . '/ijintidakmasuk';
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777, true);
                                    }
                                    $path = $path . '/' . $yyyy;
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777, true);
                                    }
                                    $path = $path . '/' . $mm;
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777, true);
                                    }
                                    $filename = date('Ymdhis') . '_' . rand(10000, 99999);

                                    Utils::makeThumbnail($fotoprofil, $path . '/' . $filename, 480);
                                    Utils::saveUploadImage($fotoprofil, $path . '/' . $filename, false);

                                    $filename = $yyyy . '/' . $mm . '/' . $filename;

                                    $sql = 'UPDATE ijintidakmasuk set filename = :filename WHERE id = :idijintidakmasuk';
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->bindValue(':filename', $filename);
                                    $stmt->bindValue(':idijintidakmasuk', $idijintidakmasuk);
                                    $stmt->execute();

                                    Session::set('fotopegawai_perusahaan', 'ada');
                                } else {
                                    return Utils::redirectForm(trans('all.formatgambarharusjpg'));
                                }
                            }

                            // posting absen
                            $sql = 'CALL hitungrekapabsen_ijintidakmasuk(:idijintidakmasuk, NULL, NULL, NULL, NULL)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam('idijintidakmasuk', $idijintidakmasuk);
                            $stmt->execute();
                        } else {
                            $pdo->commit();
                            return redirect('datainduk/absensi/ijintidakmasuk')->with('message', trans('all.alasantidakditemukan'));
                        }
                    } else {
                        $pdo->commit();
                        return redirect('datainduk/absensi/ijintidakmasuk')->with('message', trans('all.pegawaitidakditemukan'));
                    }
                }
                $pdo->commit();
                Utils::insertLogUser('Tambah ijin tidak masuk "' . $request->keterangan . '"');
                return redirect('datainduk/absensi/ijintidakmasuk')->with('message', trans('all.databerhasildisimpan'));
            } catch (\Exception $e) {
                $pdo->rollBack();
                return redirect('datainduk/absensi/ijintidakmasuk')->with('message', trans('all.terjadigangguan'));
            }
        }else{
            return redirect('datainduk/absensi/ijintidakmasuk')->with('message', trans('all.terjadigangguan'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('ijintidakmasuk','um')){
            $ijintidakmasuk = IjinTidakMasuk::find($id);
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,nama,pin from pegawai WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $ijintidakmasuk->idpegawai);
            $stmt->execute();
            $pegawai = $stmt->fetch(PDO::FETCH_OBJ);
            $alasantidakmasuks = AlasanTidakMasuk::select('id','alasan')->where('digunakan', 'y')->get();
            if(!$ijintidakmasuk){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah ijin tidak masuk');
            return view('datainduk/absensi/ijintidakmasuk/edit', ['ijintidakmasuk' => $ijintidakmasuk, 'alasantidakmasuks' => $alasantidakmasuks, 'pegawai' => $pegawai, 'menu' => 'ijintidakmasuk']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        if(Utils::cekDateTime($request->tanggalawal) && Utils::cekDateTime($request->tanggalakhir)) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //cek alasantidakmasuk harus ada
            $sql = 'SELECT DATEDIFF(STR_TO_DATE(:tanggalakhir,"%d/%m/%Y"), STR_TO_DATE(:tanggalawal,"%d/%m/%Y")) as lama';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggalawal', $request->tanggalawal);
            $stmt->bindValue(':tanggalakhir', $request->tanggalakhir);
            $stmt->execute();
            if ($stmt->rowCount() != 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row['lama'] > 180) {
                    return redirect('datainduk/absensi/ijintidakmasuk/' . $id . '/edit')->with('message', trans('all.ijintidakmasukterlalulama'));
                }
            }

            $sql = 'SELECT
                    itm.idpegawai,
                    itm.tanggalawal,
                    itm.tanggalakhir,
                    itm.keterangan,
                    itm.status,
                    IFNULL(p.gcmid,"") as gcmid
                FROM
                    ijintidakmasuk itm,
                    pegawai p
                WHERE
                    p.id=itm.idpegawai AND
                    p.del = "t" AND
                    itm.id=:idijintidakmasuk
                LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idijintidakmasuk', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $_posting_idpegawai = $row['idpegawai'];
                $_posting_tanggalawal = $row['tanggalawal'];
                $_posting_tanggalakhir = $row['tanggalakhir'];
                $_posting_keterangan = $row['keterangan'];
                $_posting_status = $row['status'];
                $_posting_gcmid = $row['gcmid'];
                $keterangan = $row['keterangan'];

                //cek alasantidakmasuk harus ada
                $sql = 'SELECT kategori,DATEDIFF(STR_TO_DATE(:tanggalakhir,"%d/%m/%Y"),STR_TO_DATE(:tanggalawal,"%d/%m/%Y")) as jumlahcuti FROM alasantidakmasuk WHERE id=:idalasantidakmasuk LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':tanggalawal', $request->tanggalawal);
                $stmt->bindValue(':tanggalakhir', $request->tanggalakhir);
                $stmt->bindValue(':idalasantidakmasuk', $request->alasan);
                $stmt->execute();
                if ($stmt->rowCount() != 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $jumlahcuti = $row['jumlahcuti'] + 1;
                    if ($row['kategori'] == 'c' && $request->status == 'a') {
                        $tahunawal = substr($request->tanggalawal, 6, 6);
                        $tahunakhir = substr($request->tanggalakhir, 6, 6);
                        if ($tahunawal == $tahunakhir) {
                            $lamacuti = $this->getLamaCuti($tahunawal, $request->pegawai);

                            $sql = 'CALL get_cuti_pegawai(:tahun,:idpegawai,:idijintidakmasuk)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tahun', $tahunawal);
                            $stmt->bindValue(':idpegawai', $request->pegawai);
                            $stmt->bindValue(':idijintidakmasuk', $id);
                            $stmt->execute();

                            $sql = 'SELECT * FROM _cuti_pegawai';
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $row1 = $stmt->fetch(PDO::FETCH_ASSOC);
                                $sisajatah = $row1['jatah'] - $lamacuti;
                                if ($lamacuti >= $row1['jatah']) {
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                } else if ($jumlahcuti > $sisajatah) {
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                }
                            }
                        } else {
                            $lamacuti = $this->getLamaCuti($tahunawal, $request->pegawai);

                            $sql = 'CALL get_cuti_pegawai(:tahunawal,:idpegawai,:idijintidakmasuk)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tahunawal', $tahunawal);
                            $stmt->bindValue(':idpegawai', $request->pegawai);
                            $stmt->bindValue(':idijintidakmasuk', $id);
                            $stmt->execute();

                            $sql = 'SELECT * FROM _cuti_pegawai';
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $row1 = $stmt->fetch(PDO::FETCH_ASSOC);
                                $sisajatah = $row1['jatah'] - $lamacuti;
                                if ($lamacuti >= $row1['jatah']) {
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                } else if ($jumlahcuti > $sisajatah) {
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                }
                            }

                            $lamacuti = $this->getLamaCuti($tahunakhir, $request->pegawai);

                            $sql = 'CALL get_cuti_pegawai(:tahun,:idpegawai,NULL)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tahun', $tahunakhir);
                            $stmt->bindValue(':idpegawai', $request->pegawai);
                            $stmt->execute();

                            $sql = 'SELECT * FROM _cuti_pegawai';
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $row1 = $stmt->fetch(PDO::FETCH_ASSOC);
                                $sisajatah = $row1['jatah'] - $lamacuti;
                                if ($lamacuti >= $row1['jatah']) {
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                } else if ($jumlahcuti > $sisajatah) {
                                    return Utils::redirectForm(trans('all.jumlahcutimencapaibatasmaks'));
                                }
                            }
                        }
                    }

                    $sql = 'UPDATE ijintidakmasuk SET idpegawai = :idpegawai, tanggalawal = STR_TO_DATE(:tanggalawal,"%d/%m/%Y"), tanggalakhir = STR_TO_DATE(:tanggalakhir,"%d/%m/%Y"), idalasantidakmasuk = :idalasantidakmasuk, keterangan = :keterangan, status = :status, updated = NOW() WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $request->pegawai);
                    $stmt->bindValue(':tanggalawal', $request->tanggalawal);
                    $stmt->bindValue(':tanggalakhir', $request->tanggalakhir);
                    $stmt->bindValue(':idalasantidakmasuk', $request->alasan);
                    $stmt->bindValue(':keterangan', $request->keterangan);
                    $stmt->bindValue(':status', $request->status);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    // cek jika foto ada
                    if (isset($request->flagpakailampiran)) {
                        // simpan foto jika ada
                        if ($request->hasFile('foto')) {

                            // hapus lampiran lama
                            //select filename dari tabel ijintidakmasuk
                            $sql = 'SELECT filename,keterangan FROM ijintidakmasuk WHERE id = :id';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':id', $id);
                            $stmt->execute();
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($row['filename'] != '') {
                                $path = Session::get('folderroot_perusahaan') . '/ijintidakmasuk/' . $row['filename'];

                                //ubah filename jadi ''
                                $sql = 'UPDATE ijintidakmasuk SET filename = ""';
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();

                                //hapus file
                                if (file_exists($path)) {
                                    unlink($path);
                                }
                                if (file_exists($path . '_thumb')) {
                                    unlink($path . '_thumb');
                                }
                            }

                            $fotoprofil = $request->file('foto');
                            //cek apakah format jpeg?
                            if ($fotoprofil->getMimeType() == 'image/jpeg' || $fotoprofil->getMimeType() == 'image/png') {
                                $yyyy = date("Y");
                                $mm = date("m");
                                $path = Session::get('folderroot_perusahaan') . '/ijintidakmasuk';
                                if (!file_exists($path)) {
                                    mkdir($path, 0777, true);
                                }
                                $path = $path . '/' . $yyyy;
                                if (!file_exists($path)) {
                                    mkdir($path, 0777, true);
                                }
                                $path = $path . '/' . $mm;
                                if (!file_exists($path)) {
                                    mkdir($path, 0777, true);
                                }
                                $filename = date('Ymdhis') . '_' . rand(10000, 99999);

                                Utils::makeThumbnail($fotoprofil, $path . '/' . $filename, 480);

                                Utils::saveUploadImage($fotoprofil, $path . '/' . $filename, false);

                                $filename = $yyyy . '/' . $mm . '/' . $filename;

                                $sql = 'UPDATE ijintidakmasuk set filename = :filename WHERE id = :idijintidakmasuk';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':filename', $filename);
                                $stmt->bindValue(':idijintidakmasuk', $id);
                                $stmt->execute();

                                Session::set('fotopegawai_perusahaan', 'ada');
                            } else {
                                return redirect('datainduk/absensi/ijintidakmasuk/create')->with('message', trans('all.formatgambarharusjpg'));
                            }
                        }
                    }

                    // posting ulang untuk data sebelum diupdate
                    $sql = 'CALL hitungrekapabsen_ijintidakmasuk(NULL, :idpegawai, :tanggalawal, :tanggalakhir, :status)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $_posting_idpegawai);
                    $stmt->bindValue(':tanggalawal', $_posting_tanggalawal);
                    $stmt->bindValue(':tanggalakhir', $_posting_tanggalakhir);
                    $stmt->bindValue(':status', $_posting_status);
                    $stmt->execute();

                    // posting absen untuk data setelah diupdate
                    $sql = 'CALL hitungrekapabsen_ijintidakmasuk(:idijintidakmasuk, NULL, NULL, NULL, NULL)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idijintidakmasuk', $id);
                    $stmt->execute();

                    //hanya kirim gcm jika status nya ada (ada pada parameter) dan berubah dari yg sebelumnya.
                    if ($_posting_gcmid != '') {
                        if ($request->status != $_posting_status) {
                            if (isset($request->keterangan)) {
                                $_posting_keterangan = $request->keterangan;
                            }
                            //kirim gcm info absen
                            Utils::kirimGCM($_posting_gcmid, 'konfirmasi', 'server', 'ijintidakmasuk|' . $id . '|' . $request->status . '|' . $_posting_keterangan);
                        }
                    }

                    Utils::insertLogUser('Ubah ijin tidak masuk "' . $keterangan . '" => "' . $request->keterangan . '"');

                    return redirect('datainduk/absensi/ijintidakmasuk')->with('message', trans('all.databerhasildiubah'));
                } else {
                    return redirect('datainduk/absensi/ijintidakmasuk/' . $id . '/edit')->with('message', trans('all.alasantidakditemukan'));
                }
            } else {
                return redirect('datainduk/absensi/ijintidakmasuk/' . $id . '/edit')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('datainduk/absensi/ijintidakmasuk/' . $id . '/edit')->with('message', trans('all.terjadigangguan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('ijintidakmasuk','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT 
                        idpegawai,
                        tanggalawal,
                        tanggalakhir,
                        keterangan,
                        status
                    FROM
                        ijintidakmasuk
                    WHERE
                        id=:idijintidakmasuk
                    LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idijintidakmasuk', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $_posting_idpegawai = $row['idpegawai'];
                $_posting_tanggalawal = $row['tanggalawal'];
                $_posting_tanggalakhir = $row['tanggalakhir'];
                $_posting_status = $row['status'];

                IjinTidakMasuk::find($id)->delete();

                // posting ulang
                $sql = 'CALL hitungrekapabsen_ijintidakmasuk(NULL, :idpegawai, :tanggalawal, :tanggalakhir, :status)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue('idpegawai', $_posting_idpegawai);
                $stmt->bindValue('tanggalawal', $_posting_tanggalawal);
                $stmt->bindValue('tanggalakhir', $_posting_tanggalakhir);
                $stmt->bindValue('status', $_posting_status);
                $stmt->execute();

                Utils::insertLogUser('Hapus ijin tidak masuk "'.$row['keterangan'].'"');

                return redirect('datainduk/absensi/ijintidakmasuk')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/absensi/ijintidakmasuk')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('ijintidakmasuk','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            //set css kolom
            $styleArray = array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
            );

            $sql = 'SELECT * FROM parameterekspor';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rowPE['logokiri'] == '' AND $rowPE['logokanan'] == '' AND $rowPE['header_1_teks'] == '' AND $rowPE['header_2_teks'] == '' AND $rowPE['header_3_teks'] == '' AND $rowPE['header_4_teks'] == '' AND $rowPE['header_5_teks'] == '') {
                $b = 1; //b = baris
            } else {
                $b = 7;
            }

            Utils::setPropertiesExcel($objPHPExcel,trans('all.ijintidakmasuk'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $b, trans('all.pegawai'))
                        ->setCellValue('B' . $b, trans('all.tanggal'))
                        ->setCellValue('C' . $b, trans('all.alasan'))
                        ->setCellValue('D' . $b, trans('all.keterangan'))
                        ->setCellValue('E' . $b, trans('all.status'));

            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $batasan = ' WHERE p.id IN ' . $batasan;
            }

            $where = '';
            if (Session::has('ijintidakmasuk_tahun')) {
                if ($batasan == '') {
                    $where = ' WHERE DATE_FORMAT(i.tanggalawal, "%Y%") = ' . Session::get('ijintidakmasuk_tahun') . ' OR DATE_FORMAT(i.tanggalakhir, "%Y%") = ' . Session::get('ijintidakmasuk_tahun');
                } else {
                    $where = ' AND DATE_FORMAT(i.tanggalawal, "%Y%") = ' . Session::get('ijintidakmasuk_tahun') . ' OR DATE_FORMAT(i.tanggalakhir, "%Y%") = ' . Session::get('ijintidakmasuk_tahun');
                }
            }

            $sql = 'SELECT
                        p.nama as pegawai,
                        CONCAT(DATE_FORMAT(i.tanggalawal, "%d/%m/%Y"), " - ",DATE_FORMAT(i.tanggalakhir, "%d/%m/%Y")) as tanggal,
                        a.alasan,
                        i.keterangan,
                        IF(i.status="a","' . trans("all.diterima") . '",IF(i.status="c","' . trans("all.confirm") . '","' . trans("all.ditolak") . '")) as status
                    FROM
                        ijintidakmasuk i
                        LEFT JOIN pegawai p ON i.idpegawai=p.id
                        LEFT JOIN alasantidakmasuk a ON i.idalasantidakmasuk=a.id
                    ' . $batasan . $where . '
                    ORDER BY
                      p.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = $b == 1 ? 2 : 8;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['pegawai']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['tanggal']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['alasan']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['keterangan']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['status']);

                // center text
                $objPHPExcel->getActiveSheet()->getStyle('E' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                for ($j = 1; $j <= 5; $j++) {
                    $huruf = Utils::angkaToHuruf($j);
                    $objPHPExcel->getActiveSheet()->getStyle($huruf . $i)->applyFromArray($styleArray);
                }

                $i++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('E7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $heightgambar = 99;
            $widthgambar = 99;

            $cg = Utils::angkaToHuruf(5) . '1';

            // style garis
            $end_i = $i - 1;
            $objPHPExcel->getActiveSheet()->getStyle('A1:E' . $end_i)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('A1:E5')->applyFromArray($styleArray);

            $sql = 'SELECT * FROM parameterekspor';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($rowPE['footerkiri_1_teks'] == '' AND $rowPE['footerkiri_2_teks'] == '' AND $rowPE['footerkiri_3_teks'] == '' AND $rowPE['footerkiri_5_teks'] == '' AND $rowPE['footerkiri_6_teks'] == '' AND $rowPE['footertengah_1_teks'] == '' AND $rowPE['footertengah_2_teks'] == '' AND $rowPE['footertengah_3_teks'] == '' AND $rowPE['footertengah_5_teks'] == '' AND $rowPE['footertengah_6_teks'] == '' AND $rowPE['footerkanan_1_teks'] == '' AND $rowPE['footerkanan_2_teks'] == '' AND $rowPE['footerkanan_3_teks'] == '' AND $rowPE['footerkanan_5_teks'] == '' AND $rowPE['footerkanan_6_teks'] == '') {
            } else {
                $l = $i + 1;
                Utils::footerExcel($objPHPExcel,'kiri','A','A',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'tengah','C','C',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'kanan','D','E',$l,$rowPE);
            }

            // password
            Utils::passwordExcel($objPHPExcel);

            if ($b != 1) {
                Utils::header5baris($objPHPExcel,'E',$rowPE);
            }

            if ($rowPE['logokiri'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokiri'];
                Utils::logoExcel('kiri',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'A1');
            }

            if ($rowPE['logokanan'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokanan'];
                Utils::logoExcel('kanan',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,$cg);
            }

            Utils::insertLogUser('Ekspor ijin tidak masuk');
            $arrWidth = array(40, 25, 25, 50, 12);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth,$styleArray);
            Utils::setFileNameExcel(trans('all.ijintidakmasuk'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}