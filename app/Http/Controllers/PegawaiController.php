<?php
namespace App\Http\Controllers;

use App\Pegawai;
use App\Lokasi;
use App\PegawaiLokasi;
use App\PegawaiAtribut;
use App\AtributVariable;
use App\Atribut;
use App\AtributNilai;
use App\Utils;

use Hamcrest\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use Storage;
use Hash;
use Response;
use File;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Worksheet_MemoryDrawing;

class PegawaiController extends Controller
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
        if(Utils::cekHakakses('pegawai','l')){

            $onboarding = $request->query('onboarding');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'CALL getpegawailengkap_blade(@_atributpenting_controller, @_atributpenting_blade, @_atributvariablepenting_controller, @_atributvariablepenting_blade)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'SELECT @_atributpenting_controller as atributpenting_controller, @_atributpenting_blade as atributpenting_blade, @_atributvariablepenting_controller as atributvariablepenting_controller, @_atributvariablepenting_blade as atributvariablepenting_blade';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $atributpenting_controller = explode('|', $row['atributpenting_controller']);
            $atributpenting_blade = explode('|', $row['atributpenting_blade']);
            $atributvariablepenting_controller = explode('|', $row['atributvariablepenting_controller']);
            $atributvariablepenting_blade = explode('|', $row['atributvariablepenting_blade']);
            //cek perusahaankuota
            $limitpegawai = Utils::cekPegawaiJumlah();
            Utils::insertLogUser('akses menu pegawai');
            return view('datainduk/pegawai/pegawai/index', ['atributpenting_controller' => $atributpenting_controller, 'atributpenting_blade' => $atributpenting_blade, 'atributvariablepenting_controller' => $atributvariablepenting_controller, 'atributvariablepenting_blade' => $atributvariablepenting_blade, 'limitpegawai' => $limitpegawai, 'menu' => 'pegawai', 'onboarding'=>$onboarding]);
        } else {
            return redirect('/');
        }
    }

    public function show(Request $request)
    {
        if(Utils::cekHakakses('pegawai','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $wherebatasan = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $where .= ' AND id IN ' . $batasan;
                $wherebatasan .= ' AND id IN ' . $batasan;
            }

            $sql = 'CALL getpegawailengkap_controller(@_atributpenting, @_atributvariablepenting, "' . str_replace('"', "'", $wherebatasan) . '")';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'SELECT @_atributpenting as atributpenting, @_atributvariablepenting as atributvariablepenting';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $atributpenting = $row['atributpenting'];
            $atributvariablepenting = $row['atributvariablepenting'];
            $stringcolumn = ',nama,jamkerja,'.$atributvariablepenting.'pin,nomorhp,status'.$atributpenting;
            $columns = explode(',',$stringcolumn);
            $table = '(
                        SELECT
                            p.id,
                            p.nama,
                            getpegawaijamkerja(p.id, "nama",CURRENT_DATE()) as jamkerja,
                            ' . $atributvariablepenting . '
                            p.pin,
                            p.nomorhp,
                            p.status
                            ' . $atributpenting . '
                        FROM
                            pegawai p,
                            _pegawailengkap _pa
                        WHERE
                            _pa.id=p.id AND
                            p.del = "t"
                        GROUP BY
                            p.id
                    ) x';
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE 1=1 '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalData = $row['total'];
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $request->input('order.0.column') == 0 ? 'id' : $columns[$request->input('order.0.column')]; //first load order by id desc
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
            $tempdata = array();
            if(!empty($originaldata)){
                foreach($originaldata as $key){
                    $ubah = '';
                    $resetpass = '';
                    $hapus = '';
                    if(Utils::cekHakakses('pegawai','um')){
                        $ubah = '<a title="' . trans('all.ubah') . '" href="pegawai/' . $key['id'] . '/edit"><i class="fa fa-pencil" style="color:#1ab394"></i></a>&nbsp;&nbsp;';
                        $resetpass = '<a title="' . trans('all.resetkatasandi') . '" href="#" onclick="return resetkatasandi(' . $key['id'] . ')"><i class="fa fa-key" style="color:#1c84c6"></i></a>&nbsp;&nbsp;';
                    }
                    if(Utils::cekHakakses('pegawai','hm')){
                        $hapus = Utils::tombolHapusDatatable('pegawai', $key['id']);
                    }
                    $tempdata['action'] = ' <center>
                                                '.$resetpass.'
                                                <a title="' . trans('all.jamkerjapegawai') . '" href="pegawai/jamkerja/' . $key['id'] . '"><i class="fa fa-calendar" style="color:#1c84c6"></i></a>&nbsp;&nbsp;
                                                <a title="' . trans('all.facesample') . '" href="pegawai/facesample/' . $key['id'] . '"><i class="fa fa-smile-o" style="color:#f8ac59"></i></a>&nbsp;&nbsp;
                                                <a title="' . trans('all.voice') . '" href="#" onclick="return aturVoice('.$key['id'].')"><i class="fa fa-play"></i></a>&nbsp;&nbsp;
                                                '.$ubah.'
                                                '.$hapus.'
                                            </center>';
                    for ($i = 1; $i < count($columns); $i++) {
                        if($columns[$i] == 'nama'){
                            $tempdata[$columns[$i]] = '<span class="detailpegawai" onclick="detailpegawai(' . $key['id'] . ')" style="cursor:pointer;">' . $key['nama'] . '</span>';
                        }elseif($columns[$i] == 'status'){
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]] == 'a' ? 'aktif' : 'tidakaktif');
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

    public function create(Request $request)
    {
        if(Utils::cekHakakses('pegawai','tm')){

            $onboarding = $request->query('onboarding');
            //cek perusahaankuota
            $limitpegawai = Utils::cekPegawaiJumlah();
            if ($limitpegawai == false) {
                return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.jumlahpegawaimencapaibatasygdiijinkan'));
            }

            $pdo = DB::connection('perusahaan_db')->getPdo();
            //atribut variable
            $atributvariables = Utils::getData($pdo,'atributvariable', 'id,atribut,IFNULL(carainputan,"") as carainputan','','atribut');
            //lokasi
            $lokasis = Lokasi::select('id', 'nama')->orderBy('nama')->get();

            //jamkerja
            $sql = 'SELECT id,nama FROM jamkerja WHERE digunakan="y"';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $jamkerja = $stmt->fetchAll(PDO::FETCH_OBJ);

            //atribut dan atributnilai
            $arrAtribut = Utils::getAtributdanAtributNilaiCrud(0, 'pegawai', false);
            $agama = Utils::getData($pdo, 'agama', 'id,agama', '', 'urutan');
            Utils::insertLogUser('akses menu tambah pegawai');
            return view('datainduk/pegawai/pegawai/create', ['atributvariables' => $atributvariables, 'jamkerja' => $jamkerja, 'atribut' => $arrAtribut, 'lokasis' => $lokasis, 'agama' => $agama, 'menu' => 'pegawai', 'onboarding' => $onboarding]);
        } else {
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $onboarding = $request->query('onboarding');
        $urlListingPegawai = 'datainduk/pegawai/pegawai';
        $urlListingPegawai = $onboarding ? $urlListingPegawai . '?onboarding=' . $onboarding : $urlListingPegawai;

        //cek perusahaankuota
        $limitpegawai = Utils::cekPegawaiJumlah();
        if ($limitpegawai == false) {
            return redirect($urlListingPegawai)->with('message', trans('all.jumlahpegawaimencapaibatasygdiijinkan'));
        }

        // cek apakah pemindai kembar ?
        $pdo = DB::connection('perusahaan_db')->getPdo();

        if ($request->pemindai != '') {
            //cek apakah pemindai kembar?
            $sql = 'SELECT id FROM pegawai WHERE pemindai=:pemindai AND ISNULL(pemindai)=false LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':pemindai', $request->pemindai);
            $stmt->execute();
            if ($stmt->rowCount() != 0) {
                return Utils::redirectForm(trans('all.pemindaisudahdigunakan'), 'error');
            }
        }

        //cari uniqueid
        $sql = 'SELECT uniqueid FROM uniqueid WHERE uniqueid NOT IN (SELECT pin FROM pegawai WHERE ISNULL(pin)=false) ORDER BY rand() LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //jika pin kosong, maka menggunakan unique id yg telah di select
        $pin = $row['uniqueid'];
        if ($request->pin != '') {
            $pin = $request->pin;
        }

        //cek apakah pin kembar?
        $sql = 'SELECT id FROM pegawai WHERE pin=:pin LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':pin', $pin);
        $stmt->execute();
        if ($stmt->rowCount() != 0) {
            return Utils::redirectForm(trans('all.pinsudahdigunakan'), 'error');
        }

        try {
            $pdo->beginTransaction();

            //cek apakah ada jamkerja
            $idjamkerja = '';
            if ($request->jamkerja != '') {
                $idjamkerja = $request->jamkerja;

                //pastikan idjamkerja ada
                $sql = 'SELECT id FROM jamkerja WHERE id=:idjamkerja LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam('idjamkerja', $idjamkerja);
                $stmt->execute();
                if ($stmt->rowCount() == 0) {
                    $pdo->rollBack();
                    return Utils::redirectForm(trans('all.jamkerjatidakditemukan'), 'error');
                }
            }

            $sqltgltidakaktif = 'NULL';
            if ($request->status == 't') {
                $sqltgltidakaktif = 'STR_TO_DATE("' . $request->tanggaltidakaktif . '","%d/%m/%Y")';
            }

            $sql = 'INSERT INTO pegawai VALUES(NULL, :nama, :idagama, :pin, :pemindai, :nomorhp, :gunakantracker, :password, NULL, "t", "t", "t", :status, STR_TO_DATE(:tanggalaktif,"%d/%m/%Y"), ' . $sqltgltidakaktif . ',NULL,NULL, NOW(),"t",NULL)';
            $stmt = $pdo->prepare($sql);
            if ($request->agama == '') {
                $stmt->bindValue(':idagama', null, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':idagama', $request->agama);
            }
            if ($request->pemindai == '') {
                $stmt->bindValue(':pemindai', null, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':pemindai', $request->pemindai);
            }
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':pin', $pin);
            $stmt->bindValue(':nomorhp', $request->nomorhp);
            $stmt->bindValue(':gunakantracker', $request->gunakantracker);
            $stmt->bindValue(':password', Hash::make(config('consts.PASSWORD_PEGAWAI_DEFAULT')));
            $stmt->bindValue(':status', $request->status);
            $stmt->bindValue(':tanggalaktif', $request->tanggalaktif);
            $stmt->execute();

            $idpegawai = $pdo->lastInsertId();

            //insert ke tabelpegawaijamkerja jika idjamkerja != ''
            if ($idjamkerja != '') {
                $sql = 'INSERT INTO pegawaijamkerja VALUES(NULL,:idpegawai,:idjamkerja,STR_TO_DATE(:berlakumulai,"%d/%m/%Y"),NOW())';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $idpegawai);
                $stmt->bindValue(':idjamkerja', $idjamkerja);
                $stmt->bindValue(':berlakumulai', $request->tanggalaktif);
                $stmt->execute();
            }

            // insert into pegawaiatributvariable
            if (isset($request->av_id)) {
                $totalav = count($request->av_id);
                for ($i = 0; $i < $totalav; $i++) {
                    if ($request->av_value[$i] != '') {
                        // cek apakah id atribut variabel sesuai?
                        $sql = 'SELECT id FROM atributvariable WHERE id = :idatributvariable';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idatributvariable', $request->av_id[$i]);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            $variable = $request->av_value[$i].($request->av_valuetime[$i] != '' ? ' '.$request->av_valuetime[$i] : '');
                            $sql = 'INSERT INTO pegawaiatributvariable VALUES(NULL,:idpegawai,:idatributvariable,:variable)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idpegawai', $idpegawai);
                            $stmt->bindValue(':idatributvariable', $request->av_id[$i]);
                            $stmt->bindValue(':variable', $variable);
                            $stmt->execute();
                        }
                    }
                }
            }

            // insert into pegawaiatribut
            if ($request->atribut != '') {
                for ($i = 0; $i < count($request->atribut); $i++) {
                    // cek
                    $sql = 'INSERT INTO pegawaiatribut VALUES(NULL, :idpegawai, :idatributnilai)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                    $stmt->execute();
                }
            }

            // insert into pegawailokasi
            if ($request->lokasi != '') {
                for ($i = 0; $i < count($request->lokasi); $i++) {
                    $sql = 'INSERT INTO pegawailokasi VALUES(0, :idpegawai, :idlokasi)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->bindValue(':idlokasi', $request->lokasi[$i]);
                    $stmt->execute();
                }
            }

            // simpan foto jika ada
            if ($request->hasFile('foto')) {
                $fotoprofil = $request->file('foto');
                //cek apakah format jpeg?
                if ($fotoprofil->getMimeType() == 'image/jpeg' || $fotoprofil->getMimeType() == 'image/png' || $fotoprofil->getMimeType() == 'image/bmp') {
                    $path = Session::get('folderroot_perusahaan') . '/pegawai';
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $path = $path . '/' . Utils::id2Folder($idpegawai);
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    Utils::makeThumbnail($fotoprofil, $path . '/' . $idpegawai);
                    Utils::saveUploadImage($fotoprofil, $path . '/' . $idpegawai);

                    $checksum = md5_file($path . '/' . $idpegawai);

                    $sql = 'UPDATE pegawai set checksum_img = :checksum WHERE id = :idpegawai';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':checksum', $checksum);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->execute();

                    Session::set('fotopegawai_perusahaan', 'ada');
                } else {
                    $pdo->rollBack();
                    return Utils::redirectForm(trans('all.formatgambartidakvalid'));
                }
            }

            Utils::insertLogUser('Tambah pegawai "' . $request->nama . '"');

            $pdo->commit();

            //store or update user.onboardingstep (untuk tooltip onboarding)
            if(Session::get('onboardingstep')==3) {
                $pdo = DB::getPdo();
                $sql = 'UPDATE user SET onboardingstep = :step WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':step', 4);
                $stmt->bindValue(':id', Session::get('iduser_perusahaan'));
                $stmt->execute();
            }

            return redirect($urlListingPegawai)->with('message', trans('all.databerhasildisimpan'));
        } catch (\Exception $e) {
            $pdo->rollBack();
            return Utils::redirectForm(trans('all.terjadigangguan'), 'error');
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('pegawai','um')){
            //select data pegawai berdasarkan id yg di edit
            $pegawai = Pegawai::find($id);

            //jika error pada waktu select pegawai berdasarkan id yg di edit
            if (!$pegawai) {
                abort(404);
            }

            $pdo = DB::connection('perusahaan_db')->getPdo();
            //master atribut variable pegawai
            $atributvariables = Utils::getData($pdo,'atributvariable', 'id,atribut,IFNULL(carainputan,"") as carainputan','','atribut');
            //atribut variable pegawai
            $sql = 'SELECT idatributvariable,variable FROM pegawaiatributvariable WHERE idpegawai = :idpegawai';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $id);
            $stmt->execute();
            $pegawaiatributvariables = $stmt->fetchAll(PDO::FETCH_OBJ);

            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pengganti master lokasipegawai dan lokasipegawai
            $sql = 'SELECT
                      l.id,
                      l.nama,
                      IF(ISNULL(pl.id),0,1) as dipilih
                    FROM
                      lokasi l
                      LEFT JOIN pegawailokasi pl ON pl.idlokasi=l.id AND pl.idpegawai = :idpegawai
                    ORDER BY
                      l.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $id);
            $stmt->execute();
            $lokasi = $stmt->fetchAll(PDO::FETCH_OBJ);

            //select jamkerja pegawai
            $sql = 'SELECT j.nama FROM jamkerja j, pegawaijamkerja p WHERE j.id=p.idjamkerja AND p.idpegawai = :idpegawai ORDER BY p.berlakumulai DESC LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $id);
            $stmt->execute();
            $jamkerja = '';
            if ($stmt->rowCount() > 0) {
                $rowJamKerja = $stmt->fetch(PDO::FETCH_ASSOC);
                $jamkerja = $rowJamKerja['nama'];
            }

            //atribut dan atribut nilai
            $arrAtribut = Utils::getAtributdanAtributNilaiCrud($id, 'pegawai', false);
            $agama = Utils::getData($pdo, 'agama', 'id,agama', '', 'urutan');
            // return json_decode($atributvariables[1]->carainputan);
            Utils::insertLogUser('akses menu ubah pegawai');
            return view('datainduk/pegawai/pegawai/edit', ['pegawai' => $pegawai, 'jamkerja' => $jamkerja, 'arratribut' => $arrAtribut, 'atributvariables' => $atributvariables, 'pegawaiatributvariables' => $pegawaiatributvariables, 'lokasi' => $lokasi, 'agama' => $agama, 'menu' => 'pegawai']);
            //return view('datainduk/pegawai/pegawai/edit', ['pegawai' => $pegawai, 'jamkerja' => $jamkerja, 'arratribut' => $arrAtribut, 'atributvariables' => $atributvariables, 'pegawaiatributvariables' => $pegawaiatributvariables, 'lokasi' => $lokasi, 'lokasis' => $lokasis, 'pegawailokasis' => $pegawailokasis, 'menu' => 'pegawai']);
        } else {
            return redirect('/');
        }
    }

    public function update(Request $request, $idpegawai)
    {

        $pdo = DB::connection('perusahaan_db')->getPdo();
        if ($request->pemindai != '') {
            //cek apakah pemindai kembar?
            $sql = 'SELECT id,nama,IFNULL(tanggaltdkaktif,"") as tanggaltdkaktif FROM pegawai WHERE pemindai="" AND ISNULL(pemindai)=false AND id<>:idpegawai AND status = "a" AND del = "t" LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->execute();
            if ($stmt->rowCount() != 0) {
                return Utils::redirectForm(trans('all.pemindaisudahdigunakan'),'error');
                //return redirect('datainduk/pegawai/pegawai/' . $idpegawai . '/edit')->with('message', trans('all.pemindaisudahdigunakan'));
            }
        }

        //pastikan pegawai ada
        $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $rowP = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = 'SELECT uniqueid FROM uniqueid WHERE uniqueid NOT IN (SELECT pin FROM pegawai WHERE ISNULL(pin)=false) ORDER BY rand() LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $pin = $row['uniqueid'];
            if ($request->pin != '') {
                $pin = $request->pin;
            }

            //cek apakah pin kembar?
            $sql = 'SELECT id FROM pegawai WHERE id<>:idpegawai AND pin = :pin LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->bindValue(':pin', $pin);
            $stmt->execute();
            if ($stmt->rowCount() != 0) {
                return Utils::redirectForm(trans('all.pinsudahdigunakan'),'error');
                //return redirect('datainduk/pegawai/pegawai/' . $idpegawai . '/edit')->with('message', trans('all.pinsudahdigunakan'));
            }

            $sqltgltidakaktif = 'NULL';
            if ($request->status == 't') {
                $sqltgltidakaktif = 'STR_TO_DATE("' . $request->tanggaltidakaktif . '","%d/%m/%Y")';
            }

            $sql = 'UPDATE pegawai SET nama=:nama, idagama = :idagama, pin=:pin, pemindai=:pemindai, nomorhp=:nomorhp, gunakantracker=:gunakantracker, tanggalaktif=STR_TO_DATE(:tanggalaktif,"%d/%m/%Y"), status = :status, tanggaltdkaktif = ' . $sqltgltidakaktif . ' WHERE id=:idpegawai';
            $stmt = $pdo->prepare($sql);
            if ($request->agama == '') {
                $stmt->bindValue(':idagama', null, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':idagama', $request->agama);
            }
            if ($request->pemindai == '') {
                $stmt->bindValue(':pemindai', null, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':pemindai', $request->pemindai);
            }
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':pin', $pin);
            $stmt->bindValue(':nomorhp', $request->nomorhp);
            $stmt->bindValue(':gunakantracker', $request->gunakantracker);
            $stmt->bindValue(':tanggalaktif', $request->tanggalaktif);
            $stmt->bindValue(':status', $request->status);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->execute();

            // delete pegawai atribut variable
            Utils::deleteData($pdo,'pegawaiatributvariable',$idpegawai,'idpegawai');

            // delete pegawai atribut
            Utils::deleteData($pdo,'pegawaiatribut',$idpegawai,'idpegawai');

            // delete pegawai lokasi
            Utils::deleteData($pdo,'pegawailokasi',$idpegawai,'idpegawai');

            // insert into pegawaiatributvariable
            if (isset($request->av_id)) {
                $totalav = count($request->av_id);
                for ($i = 0; $i < $totalav; $i++) {
                    if ($request->av_value[$i] != '') {
                        // cek apakah id atribut variabel sesuai?
                        $sql = 'SELECT id FROM atributvariable WHERE id = :idatributvariable';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idatributvariable', $request->av_id[$i]);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            $variable = $request->av_value[$i].($request->av_valuetime[$i] != '' ? ' '.$request->av_valuetime[$i] : '');
                            $sql = 'INSERT INTO pegawaiatributvariable VALUES(NULL,:idpegawai,:idatributvariable,:variable)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idpegawai', $idpegawai);
                            $stmt->bindValue(':idatributvariable', $request->av_id[$i]);
                            $stmt->bindValue(':variable', $variable);
                            $stmt->execute();
                        }
                    }
                }
            }

            // insert into pegawai atribut
            if ($request->atribut != '') {
                for ($i = 0; $i < count($request->atribut); $i++) {
                    $sql = 'INSERT INTO pegawaiatribut VALUES(NULL, :idpegawai, :idatributnilai)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                    $stmt->execute();
                }
            }

            // insert into pegawai lokasi
            if ($request->lokasi != '') {
                for ($i = 0; $i < count($request->lokasi); $i++) {
                    $sql = 'INSERT INTO pegawailokasi VALUES(0, :idpegawai, :idlokasi)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->bindValue(':idlokasi', $request->lokasi[$i]);
                    $stmt->execute();
                }
            }


            // simpan foto jika ada
            if ($request->hasFile('foto')) {
                $fotoprofil = $request->file('foto');
                //cek apakah format jpeg?
                if ($fotoprofil->getMimeType() == 'image/jpeg' || $fotoprofil->getMimeType() == 'image/png' || $fotoprofil->getMimeType() == 'image/bmp') {
                    $path = Session::get('folderroot_perusahaan') . '/pegawai';
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $path = $path . '/' . Utils::id2Folder($idpegawai);
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    Utils::makeThumbnail($fotoprofil, $path . '/' . $idpegawai);

                    Utils::saveUploadImage($fotoprofil, $path . '/' . $idpegawai);

                    $checksum = md5_file($path . '/' . $idpegawai);

                    $sql = 'UPDATE pegawai set checksum_img = :checksum WHERE id = :idpegawai';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':checksum', $checksum);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->execute();

                    Session::set('fotopegawai_perusahaan', 'ada');
                } else {
                    return redirect('datainduk/pegawai/pegawai/' . $idpegawai . '/edit')->with('message', trans('all.formatgambartidakvalid'));
                }
            }

            Utils::insertLogUser('Ubah pegawai "' . $rowP['nama'] . '" => "' . $request->nama . '"');

            return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.databerhasildisimpan'));
        } else {
            return Utils::redirectForm(trans('all.datatidakditemukan'));
            //return redirect('datainduk/pegawai/pegawai/' . $idpegawai . '/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('pegawai','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan idpegawai ada
            $sql = 'SELECT id,nama FROM pegawai WHERE id=:idpegawai AND del = "t" LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue('idpegawai', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $sql = 'UPDATE pegawai SET pin=NULL, del="y", del_waktu=NOW() WHERE id=:idpegawai';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idpegawai', $id);
                $stmt->execute();

                $sql = 'INSERT INTO authtokenblacklist_pegawai SELECT idpegawai, idtoken, expired, NOW() FROM authtoken_pegawai WHERE idpegawai=:idpegawai AND expired>NOW()';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idpegawai', $id);
                $stmt->execute();

                Utils::deleteData($pdo,'authtoken_pegawai',$id,'idpegawai');
                Utils::insertLogUser('Hapus pegawai "' . $row['nama'] . '"');

                return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.databerhasildihapus'));
            } else {
                return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function detailVoicePegawai($idpegawai)
    {
        $namapegawai = Utils::getNamaPegawai($idpegawai);
        Utils::insertLogUser('akses menu pegawai detail voice');
        return view('datainduk/pegawai/pegawai/voice', ['idpegawai' => $idpegawai, 'namapegawai' => $namapegawai]);
    }

    public function detailVoicePegawaiRebuild($idpegawai)
    {
        $pdo = DB::getPdo();
        $idperusahaan = Session::get('conf_webperusahaan');
        $sql = 'INSERT INTO _tts VALUES(NULL, :idperusahaan, :idpegawai, 0, "", ADDDATE(NOW(), INTERVAL 24 HOUR), NOW()) ON DUPLICATE KEY UPDATE retry=0, result="", expired=ADDDATE(NOW(), INTERVAL 24 HOUR), inserted=NOW()';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idperusahaan', $idperusahaan);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        Utils::insertLogUser('build ulang pegawai voice');
        return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.buatulangvoiceberhasil'));
    }

    public function jamKerja($idpegawai)
    {
        if(Utils::cekHakakses('pegawai','l')){
            //atribut penting
            $atributpenting = Utils::getAtributPenting();

            //atribut variable
            $atributvariable = Utils::getAtributVariable();

            //cek perusahaankuota
            $limitpegawai = Utils::cekPegawaiJumlah();

            //nama pegawai
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->execute();
            $namapegawai = $stmt->fetch(PDO::FETCH_OBJ);
            Utils::insertLogUser('akses menu pegawai jam kerja');
            return view('datainduk/pegawai/pegawai/jamkerja', ['idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'atributvariable' => $atributvariable, 'atributpenting' => $atributpenting, 'limitpegawai' => $limitpegawai, 'menu' => 'pegawai']);
        } else {
            return redirect('/');
        }
    }

    public function jamKerjaData(Request $request, $idpegawai)
    {
        if(Utils::cekHakakses('pegawai','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','jamkerja','jenis','berlakumulai');
            $table = '(SELECT pj.id,j.nama as jamkerja,j.jenis,pj.berlakumulai,pj.idpegawai FROM jamkerja j, pegawaijamkerja pj WHERE j.id=pj.idjamkerja) x';
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idpegawai = :idpegawai '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
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
                $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idpegawai = :idpegawai '.$where;
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $idpegawai);
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                    }
                }
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalFiltered = $row['total'];
            }

            $sql = 'SELECT * FROM '.$table.' WHERE idpegawai = :idpegawai ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
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
                    if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false){
                        $action .= Utils::tombolManipulasi('ubahcustom',$key['idpegawai'].'/ubah/' . $key['id'],$key['id']);
                    }
                    if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false){
                        $action .= '<a title="' . trans('all.hapus') . '" href="#" onclick="return hapusdata('.$key['id'].',\''.$key['jenis'].'\')"><i class="fa fa-trash" style="color:#ed5565"></i></a>';
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        if($columns[$i] == 'jenis') {
                            $tempdata[$columns[$i]] = trans('all.'.$key[$columns[$i]]);
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

    public function tambahJamKerja($idpegawai)
    {
        if(Utils::cekHakakses('pegawai','tm')){
            //nama pegawai
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->execute();
            $namapegawai = $stmt->fetch(PDO::FETCH_OBJ);

            //jamkerja
            $sql = 'SELECT id,nama FROM jamkerja WHERE digunakan = "y"';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $jamkerja = $stmt->fetchAll(PDO::FETCH_OBJ);
            Utils::insertLogUser('akses menu tambah pegawai jam kerja');
            return view('datainduk/pegawai/pegawai/jamkerja/create', ['idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'jamkerja' => $jamkerja, 'menu' => 'pegawai']);
        } else {
            return redirect('/');
        }
    }

    public function submitTambahJamKerja(Request $request)
    {
        if(Utils::cekHakakses('pegawai','tm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan idpegawai ada
            $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $request->idpegawai);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $rowpegawai = $stmt->fetch(PDO::FETCH_ASSOC);

                //cek apakah ada jadwalshift ditanggal terpilih dan setelahnya?
                $cekjadwalshiftada = Utils::cekJadwalShiftAda($request->idpegawai,$request->berlakumulai);
                if($cekjadwalshiftada == 'y'){
                    return redirect('datainduk/pegawai/pegawai/jamkerja/' . $request->idpegawai)->with('message_error', trans('all.peringatanharaphapusjadwalshift'));
                }

                //cek pengaruhnya pada jadwalshift
                $cekpengaruhjadwalshift = Utils::cekPengaruhJadwalShift($request->idpegawai,$request->jamkerja,$request->berlakumulai);
                if ($cekpengaruhjadwalshift == 'y') {
                    return redirect('datainduk/pegawai/pegawai/jamkerja/' . $request->idpegawai)->with('message_error', trans('all.gagalpenambahanjamkerjapegawai'));
                }else {
                    $sql = 'INSERT INTO pegawaijamkerja VALUES(NULL,:idpegawai,:idjamkerja,STR_TO_DATE(:berlakumulai,"%d/%m/%Y"),NOW())';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $request->idpegawai);
                    $stmt->bindValue(':idjamkerja', $request->jamkerja);
                    $stmt->bindValue(':berlakumulai', $request->berlakumulai);
                    $stmt->execute();

                    Utils::insertLogUser('Tambah Jam Kerja pegawai "' . $rowpegawai['nama'] . '"');

                    return redirect('datainduk/pegawai/pegawai/jamkerja/' . $request->idpegawai)->with('message', trans('all.databerhasildisimpan'));
                }
            } else {
                return redirect('datainduk/pegawai/pegawai/jamkerja/' . $request->idpegawai . '/tambah')->with('message', trans('all.pegawaitidakditemukan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function ubahJamKerja($idpegawai, $idjamkerjapegawai)
    {
        if(Utils::cekHakakses('pegawai','um')){
            //nama pegawai
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->execute();
            $namapegawai = $stmt->fetch(PDO::FETCH_OBJ);

            //jamkerja
            $sql = 'SELECT id,nama FROM jamkerja WHERE digunakan = "y"';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $jamkerja = $stmt->fetchAll(PDO::FETCH_OBJ);

            //jamkerjapegawai
            $sql = 'SELECT
                        id,
                        idjamkerja,
                        DATE_FORMAT(berlakumulai,"%d/%m/%Y") as berlakumulai
                    FROM
                        pegawaijamkerja
                    WHERE
                        id = :idjamkerjapegawai';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjapegawai', $idjamkerjapegawai);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            Utils::insertLogUser('akses menu ubah pegawai jam kerja');
            return view('datainduk/pegawai/pegawai/jamkerja/edit', ['idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'jamkerja' => $jamkerja, 'jamkerjapegawai' => $data, 'menu' => 'pegawai']);
        } else {
            return redirect('/');
        }
    }

    public function submitUbahJamKerja(Request $request)
    {
        if(Utils::cekHakakses('pegawai','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan jamkerjapegawai ada
            $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $request->idpegawai);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                //pastikan idpegawai ada
                $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $request->idpegawai);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    $sql = 'UPDATE pegawaijamkerja SET idjamkerja = :idjamkerja, berlakumulai = STR_TO_DATE(:berlakumulai,"%d/%m/%Y") WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idjamkerja', $request->jamkerja);
                    $stmt->bindValue(':berlakumulai', $request->berlakumulai);
                    $stmt->bindValue(':id', $request->idpegawaijamkerja);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah Jam Kerja pegawai "' . $row['nama'] . '"');

                    return redirect('datainduk/pegawai/pegawai/jamkerja/' . $request->idpegawai)->with('message', trans('all.databerhasildisimpan'));
                } else {
                    return redirect('datainduk/pegawai/pegawai/jamkerja/' . $request->idpegawai . '/ubah/' . $request->idpegawaijamkerja)->with('message', trans('all.pegawaitidakditemukan'));
                }
            } else {
                return redirect('datainduk/pegawai/pegawai/jamkerja/' . $request->idpegawai . '/ubah/' . $request->idpegawaijamkerja)->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function hapusJamKerja($id)
    {
        if(Utils::cekHakakses('pegawai','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan jamkerjapegawai ada
            $sql = 'SELECT idpegawai,berlakumulai FROM pegawaijamkerja WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                //pastikan idpegawai ada
                $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $row['idpegawai']);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $rowP = $stmt->fetch(PDO::FETCH_ASSOC);
//                    $idjamkerja = Utils::getDataCustomWhere($pdo,'pegawaijamkerja','idjamkerja',' id='.$id);
                    // hapus jadwalshift jika sudah ada
//                    $sql = 'DELETE FROM jadwalshift WHERE idpegawai = :idpegawai AND idjamkerjashift IN (SELECT id FROM jamkerjashift WHERE idjamkerja = '.$idjamkerja.')';
                    $sql = 'DELETE FROM jadwalshift WHERE idpegawai = :idpegawai AND tanggal >= :taggalberlakumulaijadwalshift';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $row['idpegawai']);
                    $stmt->bindValue(':taggalberlakumulaijadwalshift', $row['berlakumulai']);
                    $stmt->execute();

                    // hapus jamkerjapegawai
                    Utils::deleteData($pdo,'pegawaijamkerja',$id);
                    Utils::insertLogUser('Hapus Jam Kerja pegawai "' . $rowP['nama'] . '"');

                    return redirect('datainduk/pegawai/pegawai/jamkerja/' . $row['idpegawai'])->with('message', trans('all.databerhasildihapus'));
                } else {
                    return redirect('datainduk/pegawai/pegawai/jamkerja/' . $row['idpegawai'])->with('message', trans('all.pegawaitidakditemukan'));
                }
            } else {
                return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function excelJamKerja($idpegawai)
    {
        if(Utils::cekHakakses('pegawai','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.jamkerjapegawai'));

            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.jamkerja'))
                        ->setCellValue('B1', trans('all.jenis'))
                        ->setCellValue('C1', trans('all.berlakumulai'));

            $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->execute();
            $rowP = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = 'SELECT
                        j.nama as jamkerja,
                        j.jenis,
                        (DATEDIFF(pj.berlakumulai,"1900-01-01")+2) as berlakumulai
                    FROM
                        jamkerja j,
                        pegawaijamkerja pj
                    WHERE
                        j.id=pj.idjamkerja AND
                        pj.idpegawai = :idpegawai';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['jamkerja']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['jenis']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['berlakumulai']);

                // format
                $objPHPExcel->getActiveSheet()->getStyle('C' . $i)->getNumberFormat()->setFormatCode('DD/MM/YYYY');

                $i++;
            }

            Utils::insertLogUser('ekspor pegawai jam kerja');
            $arrWidth = array(40, 12, 17);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.jamkerja') . '_' . $rowP['nama']);
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }

    public function resetKataSandi($id)
    {
        if(Utils::cekHakakses('pegawai','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $pdo2 = DB::getPdo();
            $idpegawai = $id;

            // data perusahaan
            $sql = 'SELECT nama,kode FROM perusahaan WHERE id = :idperusahaan';
            $stmt = $pdo2->prepare($sql);
            $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
            $stmt->execute();
            $rowPerusahaan = $stmt->fetch(PDO::FETCH_ASSOC);

            // data pegawai
            $sql = 'SELECT
                        nama as pegawai_nama,
                        pin,
                        nomorhp
                    FROM
                        pegawai
                    WHERE
                        id=:idpegawai
                    LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $perusahaan_nama = $rowPerusahaan['nama'];
                $perusahaan_kode = $rowPerusahaan['kode'];
                $pegawai_pin = $row['pin'];
                $pegawai_nama = $row['pegawai_nama'];
                $pegawai_nomorhp = $row['nomorhp'];

                if ($pegawai_nomorhp != '') {
                    $password = Utils::generateRandomAngka(4);
                    $pwd_bcrypt = Hash::make($password);
                    $sql = 'UPDATE pegawai SET password=:password WHERE id=:idpegawai';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':password', $pwd_bcrypt);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->execute();

                    // hapus token lama
                    $sql3 = 'INSERT INTO authtokenblacklist_pegawai SELECT idpegawai, idtoken, expired, NOW() FROM authtoken_pegawai WHERE idpegawai=:idpegawai ON DUPLICATE KEY UPDATE authtokenblacklist_pegawai.expired=GREATEST(authtokenblacklist_pegawai.expired, authtoken_pegawai.expired);';
                    $stmt3 = $pdo->prepare($sql3);
                    $stmt3->bindValue(':idpegawai', $idpegawai);
                    $stmt3->execute();

                    Utils::deleteData($pdo,'authtoken_pegawai',$idpegawai,'idpegawai');

                    $formatsms = '';
                    $sql = 'SELECT format_sms_lupa_pwd_pegawai FROM pengaturan LIMIT 1';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $formatsms = $row['format_sms_lupa_pwd_pegawai'];

                        $formatsms = str_replace('{company}', strtoupper($perusahaan_nama), $formatsms);
                        $formatsms = str_replace('{name}', strtoupper($pegawai_nama), $formatsms);
                        $formatsms = str_replace('{pin}', $pegawai_pin, $formatsms);
                        $formatsms = str_replace('{username}', $perusahaan_kode . $pegawai_pin, $formatsms);
                        $formatsms = str_replace('{password}', $password, $formatsms);
                        $formatsms = str_replace('{crlf}', chr(13) . chr(10), $formatsms);
                    }

                    //Utils::kirimSms($pegawai_nomorhp, $formatsms);
                    // masukkan ke dalam antrean kirimsms
                    $sql3 = 'INSERT INTO _kirimsms VALUES(0, :idperusahaan, :tujuan, LEFT(:isi,159), NOW())';
                    $stmt3 = $pdo2->prepare($sql3);
                    $stmt3->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
                    $stmt3->bindValue(':tujuan', $pegawai_nomorhp);
                    $stmt3->bindValue(':isi', $formatsms);
                    $stmt3->execute();

                    Utils::insertLogUser('Reset katasandi "' . $pegawai_nama . '"');

                    return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.katasandiberhasildireset'));
                } else {
                    return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.nomorhppegawaibelumditentukan'));
                }
            } else {
                return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.pegawaitidakditemukan'));
            }
        }
    }

    //facesample dan fingerprint berdasarkan id pegawai
    public function sample($jenis,$idpegawai)
    {
        if(Utils::cekHakakses('pegawai','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //cek apakah atribut pegawai ada batasan?
            $bolehhapus = true;
            $pdo2 = DB::getPdo();
            $sql = 'SELECT email FROM `user` WHERE id=:iduser LIMIT 1';
            $stmt = $pdo2->prepare($sql);
            $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $bolehhapus = false;
            } else {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $email = $row['email'];

                $sql = 'SELECT IFNULL(GROUP_CONCAT(idatributnilai SEPARATOR ","),"") as batasan FROM batasanemail be, batasanatribut ba WHERE be.idbatasan=ba.idbatasan AND be.email=:email';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':email', $email);
                $stmt->execute();
                $batasan = '';
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $batasan = $row['batasan'];
                }

                if ($batasan != '') {
                    $sql = 'SELECT DISTINCT(p.id) FROM pegawai p, pegawaiatribut pa WHERE p.id=pa.idpegawai AND pa.idatributnilai IN (' . $batasan . ') AND p.id = :idpegawai AND p.del = "t"';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpegawai', $idpegawai);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $bolehhapus = true;
                    }
                }
            }

            if ($jenis == 'facesample') {
                // select facesample
                $sql = 'SELECT id, filename, checksum FROM facesample WHERE idpegawai=:idpegawai';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam('idpegawai', $idpegawai);
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            }else{
                //fingerprint
                $sql = 'SELECT id, algoritma, finger_id, `size`, valid, template, checksum, IFNULL(deleted,"") as deleted FROM fingersample WHERE idpegawai=:idpegawai';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam('idpegawai', $idpegawai);
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            }
            Utils::insertLogUser('akses menu pegawai facesample');
            $namapegawai = Utils::getNamaPegawai($idpegawai);
            return view('datainduk/pegawai/pegawai/sample', ['menu' => 'pegawai', 'jenis' => $jenis, 'idpegawai' => $idpegawai, 'bolehhapus' => $bolehhapus, 'namapegawai' => $namapegawai, 'data' => $data]);
        }
    }

    public function getFaceSample($idfacesample)
    {
        if(Utils::cekHakakses('pegawai','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $result = '';
            $sql = 'SELECT idpegawai, filename FROM facesample WHERE id=:idfacesample LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idfacesample', $idfacesample);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $idpegawai = $row['idpegawai'];
                $filename = $row['filename'];

                $path = Session::get('folderroot_perusahaan') . '/facesample/' . Utils::id2Folder($idpegawai) . '/' . $idpegawai . '/' . $filename . '_thumb';
                if (file_exists($path)) {
                    $raw = Utils::decrypt($path);
                    $result = response($raw)->header('Content-Type', 'image/jpeg');
                } else {
                    $path_nopic = $_SERVER['DOCUMENT_ROOT'] . '/' . config('consts.FOLDER_IMG') . '/pegawai_nopic.png';
                    $result = Response::make(File::get($path_nopic))->header('Content-Type', 'image/png');
                }
            } else {
                abort(404);
            }
            return $result;
        }
    }

    public function deleteAllFaceSample($idpegawai)
    {
        if(Utils::cekHakakses('pegawai','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id, filename FROM facesample WHERE idpegawai=:idpegawai';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->execute();
            try {
                $path = Session::get('folderroot_perusahaan') . '/facesample/' . Utils::id2Folder($idpegawai) . '/' . $idpegawai;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $idfacesample = $row['id'];
                    $filename = $row['filename'];

                    if (file_exists($path . '/' . $filename)) {
                        unlink($path . '/' . $filename);
                    }

                    if (file_exists($path . '/' . $filename . '_thumb')) {
                        unlink($path . '/' . $filename . '_thumb');
                    }

                    Utils::deleteData($pdo,'facesample',$idfacesample);
                }

                if (is_dir($path)) {
                    rmdir($path);
                }

                $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND status = "a" AND del = "t"';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $idpegawai);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                Utils::insertLogUser('Hapus semua sampel wajah "' . $row['nama'] . '"');

                return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.facesampledihapus'));
            } catch (\Exception $e) {
                return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.terjadigangguan'));
            }
        }
    }

    public function deleteFaceSample($idfacesample)
    {
        if(Utils::cekHakakses('pegawai','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();

            $sql = 'SELECT idpegawai, filename FROM facesample WHERE id=:idfacesample LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam('idfacesample', $idfacesample);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $idpegawai = $row['idpegawai'];
                $filename = $row['filename'];
                $path = Session::get('folderroot_perusahaan') . '/facesample/' . Utils::id2Folder($idpegawai) . '/' . $idpegawai . '/' . $filename;

                if (file_exists($path)) {
                    unlink($path);
                }

                if (file_exists($path . '_thumb')) {
                    unlink($path . '_thumb');
                }

                Utils::deleteData($pdo,'facesample',$idfacesample);

                $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND status = "a" AND del = "t"';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $idpegawai);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                Utils::insertLogUser('Hapus sampel wajah "' . $row['nama'] . '"');

                return redirect('datainduk/pegawai/pegawai/facesample/' . $idpegawai)->with('message', trans('all.facesampledihapus'));
            } else {
                return redirect('datainduk/pegawai/pegawai/')->with('message', trans('all.facesampletidakditemukan'));
            }
        }
    }

    public function imporExcel()
    {
        if(Utils::cekHakakses('pegawai','tm')){

            //cek perusahaankuota
            $limitpegawai = Utils::cekPegawaiJumlah();
            if ($limitpegawai == false) {
                return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.jumlahpegawaimencapaibatasygdiijinkan'));
            }

            $pdo = DB::connection('perusahaan_db')->getPdo();
            //jamkerja
            $sql = 'SELECT id,nama as jamkerja FROM jamkerja WHERE digunakan="y"';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $jamkerja = $stmt->fetchAll(PDO::FETCH_OBJ);
            $agama = Utils::getData($pdo, 'agama', 'id,agama', '', 'urutan');

            //kolom
            $arrKolom = '';
            for($i=1;$i<=26;$i++){
                $arrKolom[$i] = Utils::angkaToHuruf($i);
            }
            Utils::insertLogUser('akses menu pegawai impor data');
            return view('datainduk/pegawai/pegawai/imporexcel', ['kolom' => $arrKolom, 'jamkerja' => $jamkerja, 'agama' => $agama, 'menu' => 'pegawai']);
        } else {
            return redirect('/');
        }
    }

    public function submitImporExcel(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek perusahaankuota
        $limitpegawai = Utils::cekPegawaiJumlah();
        if ($limitpegawai == false) {
            return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.jumlahpegawaimencapaibatasygdiijinkan'));
        }

        $template = PHPExcel_IOFactory::load($request->file('fileexcel'));
        $objWorksheet = clone $template->getActiveSheet();

        $nama_huruf = Utils::angkaToHuruf($request->nama);
        $agama_huruf = $request->agama != '' ? Utils::angkaToHuruf($request->agama) : '';
        $pin_huruf = $request->pin != '' ? Utils::angkaToHuruf($request->pin) : '';
        $pemindai_huruf = $request->pemindai != '' ? Utils::angkaToHuruf($request->pemindai) : '';
        $nomorhp_huruf = $request->nomorhp != '' ? Utils::angkaToHuruf($request->nomorhp) : '';
        $jamkerja_huruf = $request->jamkerja != '' ? Utils::angkaToHuruf($request->jamkerja) : '';
        $barismulaidata = $request->barismulaidata;
        $barissampaidata = $request->barissampaidata;

        if($jamkerja_huruf != ''){

        }

        $dataagama = Utils::getData($pdo, 'agama', 'id', 'urutan');
        $datajamkerja = Utils::getData($pdo, 'jamkerja', 'id', '', 'nama');
        for($i = $barismulaidata;$i<=$barissampaidata;$i++){
            $nama = $objWorksheet->getCell($nama_huruf.$i)->getValue();
            $pin = $pin_huruf != '' ? $objWorksheet->getCell($pin_huruf.$i)->getValue() : NULL;
            $pemindai = $pemindai_huruf != '' ? $objWorksheet->getCell($pemindai_huruf.$i)->getValue() : NULL;
            $nomorhp = $nomorhp_huruf != '' ? $objWorksheet->getCell($nomorhp_huruf.$i)->getValue() : '';
            $agama = NULL;

            if($agama_huruf != '') {
                $agama = NULL;
                if($objWorksheet->getCell($agama_huruf.$i)->getValue() != '') {
                    foreach ($dataagama as $key) {
                        if ($objWorksheet->getCell($agama_huruf . $i)->getValue() == $request->input('agama_' . $key->id)) {
                            $agama = $key->id;
                        }
                    }
                }
            }

            //cari uniqueid
            $sql = 'SELECT uniqueid FROM uniqueid WHERE uniqueid NOT IN (SELECT pin FROM pegawai WHERE ISNULL(pin)=false) ORDER BY rand() LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //jika pin kosong, maka menggunakan unique id yg telah di select
            if ($pin == '') {
                $pin = $row['uniqueid'];
            }

            //insert ke table pegawai
            $sql = 'INSERT INTO pegawai VALUES(NULL,:nama,:idagama,:pin,:pemindai,:nomorhp,"d",:password,"","t","t","t","a",NOW(),NULL,"","",NOW(),"t",NULL)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $nama);
            $stmt->bindValue(':idagama', $agama);
            $stmt->bindValue(':pin', $pin);
            $stmt->bindValue(':pemindai', $pemindai);
            $stmt->bindValue(':nomorhp', $nomorhp);
            $stmt->bindValue(':password', Hash::make(config('consts.PASSWORD_PEGAWAI_DEFAULT')));
            $stmt->execute();

            if($jamkerja_huruf != ''){
                $jamkerja = NULL;
                $idpegawai = $pdo->lastInsertId();
                if($objWorksheet->getCell($jamkerja_huruf.$i)->getValue() != '') {
                    foreach ($datajamkerja as $key) {
                        if ($objWorksheet->getCell($jamkerja_huruf . $i)->getValue() == $request->input('jamkerja_' . $key->id)) {
                            $jamkerja = $key->id;

                            $sql2 = 'INSERT INTO pegawaijamkerja VALUES(NULL,:idpegawai,:idjamkerja,CURRENT_DATE(),NOW())';
                            $stmt2 = $pdo->prepare($sql2);
                            $stmt2->bindValue(':idpegawai', $idpegawai);
                            $stmt2->bindValue(':idjamkerja', $jamkerja);
                            $stmt2->execute();
                        }
                    }
                }
            }
        }

        Utils::insertLogUser('impor data pegawai');

        //return $objWorksheet->getCell('B1')->getValue();
        return redirect('datainduk/pegawai/pegawai')->with('message', trans('all.databerhasildisimpan'));

    }

    public function excel()
    {
        if(Utils::cekHakakses('pegawai','l')){
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

            Utils::setPropertiesExcel($objPHPExcel,trans('all.pegawai'));

            $sql = 'SELECT * FROM parameterekspor';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rowPE['logokiri'] == '' AND $rowPE['logokanan'] == '' AND $rowPE['header_1_teks'] == '' AND $rowPE['header_2_teks'] == '' AND $rowPE['header_3_teks'] == '' AND $rowPE['header_4_teks'] == '' AND $rowPE['header_5_teks'] == '') {
                $b = 1; //b = baris
            } else {
                $b = 7;
            }

            //get atribut
            $allatribut = Utils::getAllAtribut('blade');
            $atributpenting_controller = ($allatribut['atributpenting_controller'] != '' ? explode('|', $allatribut['atributpenting_controller']) : '');
            $atributpenting_blade = explode('|', $allatribut['atributpenting_blade']);
            $atributvariablepenting_controller = ($allatribut['atributvariablepenting_controller'] != '' ? explode('|', $allatribut['atributvariablepenting_controller']) : '');
            $atributvariablepenting_blade = explode('|', $allatribut['atributvariablepenting_blade']);
            $totalatributvariable = ($atributvariablepenting_controller != '' ? count($atributvariablepenting_controller) : 0);
            $totalatributpenting = ($atributpenting_controller != '' ? count($atributpenting_controller) : 0);

            //set atribut variable
            $ih = 3; //letak mulai setelah kolom fix (i header)
            if ($atributvariablepenting_blade != '') {
                //looping untuk header
                foreach ($atributvariablepenting_blade as $key) {
                    if ($key != '') {
                        $hh = Utils::angkaToHuruf($ih);
                        $objPHPExcel->getActiveSheet()->setCellValue($hh . $b, $key);
                        //lebar kolom
                        $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                        //set bold
                        $objPHPExcel->getActiveSheet()->getStyle($hh . $b)->getFont()->setBold(true);
                        //style
                        $objPHPExcel->getActiveSheet()->getStyle($hh . $b)->applyFromArray($styleArray);

                        $ih++;
                    }
                }
            }

            //set value kolom
            $h1 = Utils::angkaToHuruf($ih);
            $h2 = Utils::angkaToHuruf($ih + 1);
            $h3 = Utils::angkaToHuruf($ih + 2);

            //set atribut penting
            if ($atributpenting_blade != '') {
                //looping untuk header
                foreach ($atributpenting_blade as $key) {
                    if ($key != '') {
                        $hi = $ih + 3;
                        $hh = Utils::angkaToHuruf($hi);
                        $objPHPExcel->getActiveSheet()->setCellValue($hh . $b, $key);
                        //lebar kolom
                        $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(25);
                        //set bold
                        $objPHPExcel->getActiveSheet()->getStyle($hh . $b)->getFont()->setBold(true);
                        //style
                        $objPHPExcel->getActiveSheet()->getStyle($hh . $b)->applyFromArray($styleArray);

                        $ih++;
                    }
                }
            }

            //set atribut untuk query
            $whereAtribut = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $whereAtribut = ' AND id IN ' . $batasan;
            }
            $allatribut_controller = Utils::getAllAtribut('controller', $whereAtribut);
            $atributpenting = $allatribut_controller['atributpenting'];
            $atributvariablepenting = $allatribut_controller['atributvariablepenting'];

            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $b, trans('all.nama'))
                        ->setCellValue('B' . $b, trans('all.jamkerja'))
                        ->setCellValue($h1 . $b, trans('all.pin'))
                        ->setCellValue($h2 . $b, trans('all.nomorhp'))
                        ->setCellValue($h3 . $b, trans('all.status'));

            $sql = 'SELECT
                        p.id,
                        p.nama,
                        ' . $atributvariablepenting . '
                        p.pin,
                        p.nomorhp,
                        IF(p.status="a","' . trans('all.aktif') . '","' . trans('all.tidakaktif') . '") as status,
                        p.tanggalaktif,
                        p.tanggaltdkaktif,
                        getpegawaijamkerja(p.id, "nama",CURRENT_DATE()) as jamkerja
                        ' . $atributpenting . '
                    FROM
                        pegawai p,
                        _pegawailengkap _pa
                    WHERE
                        p.id=_pa.id AND
                        p.del = "t"
                    GROUP BY
                        p.id
                    ORDER BY
                        p.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = $b + 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['jamkerja']);
                $objPHPExcel->getActiveSheet()->setCellValue($h1 . $i, $row['pin']);
                $objPHPExcel->getActiveSheet()->setCellValue($h2 . $i, $row['nomorhp']);
                $objPHPExcel->getActiveSheet()->setCellValue($h3 . $i, $row['status']);

                if($atributvariablepenting_controller != '') {
                    $z1 = 3; //huruf setelah kolom jamkerja
                    for ($j = 0; $j < $totalatributvariable; $j++) {
                        $hv = Utils::angkaToHuruf($z1);
                        $objPHPExcel->getActiveSheet()->setCellValue($hv . $i, $row[$atributvariablepenting_controller[$j]]);

                        $z1++;
                    }
                }

                if($atributpenting_controller != '') {
                    $z2 = 6 + $totalatributvariable; //iterasi untuk looping atribut penting 6 dari jumlah kolom fix
                    for ($j = 0; $j < $totalatributpenting; $j++) {

                        $hap = Utils::angkaToHuruf($z2);
                        $objPHPExcel->getActiveSheet()->setCellValue($hap . $i, $row[$atributpenting_controller[$j]]);

                        $z2++;
                    }
                }

                $objPHPExcel->getActiveSheet()->getStyle($h3 . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                for ($j = 1; $j <= 5 + $totalatributvariable + $totalatributpenting; $j++) {
                    $huruf = Utils::angkaToHuruf($j);
                    $objPHPExcel->getActiveSheet()->getStyle($huruf . $i)->applyFromArray($styleArray);
                }

                $i++;
            }

            $objPHPExcel->getActiveSheet()->getStyle($h3 . $b)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            for ($j = 1; $j <= 5 + $totalatributvariable + $totalatributpenting; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . $b)->getFont()->setBold(true);
            }

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension($h1)->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension($h2)->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension($h3)->setWidth(12);

            $heightgambar = 99;
            $widthgambar = 99;

            $cg1 = Utils::angkaToHuruf(5 + $totalatributvariable + $totalatributpenting);

            if ($rowPE['footerkiri_1_teks'] == '' AND $rowPE['footerkiri_2_teks'] == '' AND $rowPE['footerkiri_3_teks'] == '' AND $rowPE['footerkiri_5_teks'] == '' AND $rowPE['footerkiri_6_teks'] == '' AND $rowPE['footertengah_1_teks'] == '' AND $rowPE['footertengah_2_teks'] == '' AND $rowPE['footertengah_3_teks'] == '' AND $rowPE['footertengah_5_teks'] == '' AND $rowPE['footertengah_6_teks'] == '' AND $rowPE['footerkanan_1_teks'] == '' AND $rowPE['footerkanan_2_teks'] == '' AND $rowPE['footerkanan_3_teks'] == '' AND $rowPE['footerkanan_5_teks'] == '' AND $rowPE['footerkanan_6_teks'] == '') {
            } else {
                $l = $i + 1;
                Utils::footerExcel($objPHPExcel,'kiri','A','A',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'tengah','C','D',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'kanan',$cg1,$cg1,$l,$rowPE);
            }

            // password
            Utils::passwordExcel($objPHPExcel);

            if ($b != 1) {
                Utils::header5baris($objPHPExcel,$cg1,$rowPE);
            }

            if ($rowPE['logokiri'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokiri'];
                Utils::logoExcel('kiri',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'A1');
            }

            if ($rowPE['logokanan'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokanan'];
                Utils::logoExcel('kanan',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,$cg1.'1');
            }

            Utils::insertLogUser('Ekspor pegawai');
            Utils::setFileNameExcel(trans('all.pegawai'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }

    /**
     * Functions singkronisasi_login
     * login proses sebelum akses api klola
     *
     * @return array
     * @author apriana@bsa.id
     */
    public function singkronisasi_login(){
        $url = env("KLOLA_BASE_URL")."/api/auth/login";
        $header =  array();
        $param_body = [
            "email"=> env("KLOLA_LOGIN_USER"),
            "password"=> env("KLOLA_LOGIN_PASSWORD")
        ];
        $result_curl = Utils::curl_post($url, $header, $param_body);

        if($result_curl["code"] == 200) {
            $deSerialise = json_decode($result_curl["result"], true);
            if(array_key_exists("access_token", $deSerialise)) {
                Session::set("klola_access_token", $deSerialise["access_token"]);
            }

            return [
                "status" => true,
                "result" => $deSerialise
            ];
        }

        return [
            "status" => false
        ];
    }

    /**
     * Functions getEmployee
     * mengambil data pegawai dari klola berdasarkan perusahaan
     *
     * @param  String $param
     *
     * @return json
     * @author apriana@bsa.id
     */
    public function getEmployee($param) {
        $url = env("KLOLA_BASE_URL")."/api/employee/".$param;
        $header =  array('Authorization: Bearer '.Session::get("klola_access_token"));
        $result_curl = Utils::curl_get($url, $header);

        if($result_curl["code"] == 200) {
            $deSerialise = json_decode($result_curl["result"], true);

            // ketika token expired
            if($deSerialise["status"] == "Token is Expired") {
                // generate token ulang
                $result_login = $this->singkronisasi_login();
                // panggil ulang diri sendiri
                return $this->getEmployee($param);
            }

            return [
                "status" => true,
                "result" => $deSerialise
            ];
        }

        return [
            "status" => false
        ];
    }


    /**
     * Functions sinkronisasi_data
     * mengambil data pegawai dari klola dan mencocokkan dengan data smartpresence
     *
     * @return json
     * @author apriana@bsa.id
     */
    public function sinkronisasi_data(Request $request) {
        // get data from database
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT * FROM pegawai';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rowPegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // check token is exist or not
        if(!Session::has("klola_access_token")) {
            $result_login = $this->singkronisasi_login();
        }

        $dataCS = array();
        $dataArgenta = array();

        // get klola employee - > Brinks -> CS
        $getEmployeeCS = $this->getEmployee("cs");
        if($getEmployeeCS["status"]) {
            if($getEmployeeCS["result"]["status"] == "sukses") {
                $dataCS = $getEmployeeCS["result"]["data"] ;
            }
        }

        // get klola employee - > Brinks -> Argenta
        $getEmployeeArgenta = $this->getEmployee("argenta");
        if($getEmployeeArgenta["status"]) {
            if($getEmployeeArgenta["result"]["status"] == "sukses") {
                $dataArgenta = $getEmployeeArgenta["result"]["data"] ;
            }
        }

        // MERGE ARRAY
        $dataBrinks = array_merge($dataCS, $dataArgenta);

        $brink_temp = json_decode(json_encode($dataBrinks), true);
        $pegawai_temp = json_decode(json_encode($rowPegawai), true);

        $pegawai_lama = array();
        $pegawai_update = array();
        $pegawai_baru = array();
        $exist_pin = array() ;


        // loop data dari klola
        $i = 0 ;
        foreach ($dataBrinks as $itemBrink) {
            $isMatch = false ;

            if(!in_array($itemBrink["pin"], $exist_pin)) {
                array_push($exist_pin, $itemBrink);

                // loop database pegawai SP
                $j = 0 ;
                foreach ($rowPegawai as $itemPegawai) {
                    if($itemBrink["pin"] == $itemPegawai["pin"]) {
                        $isMatch = true ;
                        unset($pegawai_temp[$j]);
                        break ;
                    }
                    $j++ ;
                }

                if($isMatch) {
                    array_push($pegawai_update, $itemBrink);
                    unset($brink_temp[$i]);
                }
            } else {
                unset($brink_temp[$i]);
            }
            $i++ ;
        }

        $SPPegawaiLama = array_values($pegawai_temp);
        $KlolaPegawaiBaru = array_values($brink_temp);

        $jumlah_pegawai_yang_cocok = count($pegawai_update) ;
        $jumlah_pegawai_baru_yang_ditemukan = count($KlolaPegawaiBaru) ;
        return response()->json([
            "status" => true,
            "jumlah_pegawai_di_sp" => count($rowPegawai),
            "jumlah_pegawai_dari_klola" => count($dataBrinks),
            "jumlah_pegawai_lama_tidak_ditemukan" => count($SPPegawaiLama),
            "jumlah_pegawai_yang_cocok" => $jumlah_pegawai_yang_cocok,
            "jumlah_pegawai_baru_yang_ditemukan" => $jumlah_pegawai_baru_yang_ditemukan,
            "total_pegawai_singkronisasi" => $jumlah_pegawai_yang_cocok + $jumlah_pegawai_baru_yang_ditemukan,
            "pegawai_lama" => $SPPegawaiLama,
            "pegawai_update" => $pegawai_update,
            "pegawai_baru" => $KlolaPegawaiBaru
        ]);

    }
}