<?php
namespace App\Http\Controllers;

use App\HakAkses;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;

use Form;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class HakAksesController extends Controller
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
        if(Utils::cekHakakses('hakakses','l')){
            Utils::insertLogUser('akses menu hak akses');
            return view('datainduk/lainlain/hakakses/index', ['menu' => 'hakakses']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
    {
        if(Utils::cekHakakses('hakakses','l')){
            $pdo = DB::getPdo();
            $where = ' AND idperusahaan = '.Session::get('conf_webperusahaan').' AND _flaghapus = "y" ';
            if(Utils::cekHakakses('hakakses','uhm')) {
                $columns = array('', 'nama');
            }else{
                $columns = array('nama');
            }
            $table = 'hakakses';
            $totalData = Utils::getDataCustomWhere($pdo,$table, 'count(id)', '1=1 '.$where);
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
                    $action = '';
                    if(Utils::cekHakakses('hakakses','um')){
                        $action .= Utils::tombolManipulasi('ubah','hakakses',$key['id']);
                    }
                    if(Utils::cekHakakses('hakakses','hm')){
                        $action .= Utils::tombolManipulasi('hapus','hakakses',$key['id']);
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
        if(Utils::cekHakakses('hakakses','tm')){
            Utils::insertLogUser('akses menu tambah hak akses');
            return view('datainduk/lainlain/hakakses/create', ['menu' => 'hakakses']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        //varialbel
        $ajakan = '';
        if(isset($request->ajakan_ijinkan)){ $ajakan .= $request->ajakan_ijinkan; }
        $alasanmasukkeluar = '';
        if(isset($request->alasanmasukkeluar_lihat)){ $alasanmasukkeluar .= $request->alasanmasukkeluar_lihat; }
        if(isset($request->alasanmasukkeluar_tambah)){ $alasanmasukkeluar .= $request->alasanmasukkeluar_tambah; }
        if(isset($request->alasanmasukkeluar_ubah)){ $alasanmasukkeluar .= $request->alasanmasukkeluar_ubah; }
        if(isset($request->alasanmasukkeluar_hapus)){ $alasanmasukkeluar .= $request->alasanmasukkeluar_hapus; }
        $alasantidakmasuk = '';
        if(isset($request->alasantidakmasuk_lihat)){ $alasantidakmasuk .= $request->alasantidakmasuk_lihat; }
        if(isset($request->alasantidakmasuk_tambah)){ $alasantidakmasuk .= $request->alasantidakmasuk_tambah; }
        if(isset($request->alasantidakmasuk_ubah)){ $alasantidakmasuk .= $request->alasantidakmasuk_ubah; }
        if(isset($request->alasantidakmasuk_hapus)){ $alasantidakmasuk .= $request->alasantidakmasuk_hapus; }
        $atribut = '';
        if(isset($request->atribut_lihat)){ $atribut .= $request->atribut_lihat; }
        if(isset($request->atribut_tambah)){ $atribut .= $request->atribut_tambah; }
        if(isset($request->atribut_ubah)){ $atribut .= $request->atribut_ubah; }
        if(isset($request->atribut_hapus)){ $atribut .= $request->atribut_hapus; }
        $facesample = '';
        if(isset($request->facesample_lihat)){ $facesample .= $request->facesample_lihat; }
        if(isset($request->facesample_tambah)){ $facesample .= $request->facesample_tambah; }
        if(isset($request->facesample_ubah)){ $facesample .= $request->facesample_ubah; }
        if(isset($request->facesample_hapus)){ $facesample .= $request->facesample_hapus; }
        $fingersample = '';
        if(isset($request->fingersample_lihat)){ $fingersample .= $request->fingersample_lihat; }
        if(isset($request->fingersample_tambah)){ $fingersample .= $request->fingersample_tambah; }
        if(isset($request->fingersample_ubah)){ $fingersample .= $request->fingersample_ubah; }
        if(isset($request->fingersample_hapus)){ $fingersample .= $request->fingersample_hapus; }
        $hakakses = '';
        if(isset($request->hakakses_lihat)){ $hakakses .= $request->hakakses_lihat; }
        if(isset($request->hakakses_tambah)){ $hakakses .= $request->hakakses_tambah; }
        if(isset($request->hakakses_ubah)){ $hakakses .= $request->hakakses_ubah; }
        if(isset($request->hakakses_hapus)){ $hakakses .= $request->hakakses_hapus; }
        $batasan = '';
        if(isset($request->batasan_lihat)){ $batasan .= $request->batasan_lihat; }
        if(isset($request->batasan_tambah)){ $batasan .= $request->batasan_tambah; }
        if(isset($request->batasan_ubah)){ $batasan .= $request->batasan_ubah; }
        if(isset($request->batasan_hapus)){ $batasan .= $request->batasan_hapus; }
        $harilibur = '';
        if(isset($request->harilibur_lihat)){ $harilibur .= $request->harilibur_lihat; }
        if(isset($request->harilibur_tambah)){ $harilibur .= $request->harilibur_tambah; }
        if(isset($request->harilibur_ubah)){ $harilibur .= $request->harilibur_ubah; }
        if(isset($request->harilibur_hapus)){ $harilibur .= $request->harilibur_hapus; }
        $ijintidakmasuk = '';
        if(isset($request->ijintidakmasuk_lihat)){ $ijintidakmasuk .= $request->ijintidakmasuk_lihat; }
        if(isset($request->ijintidakmasuk_tambah)){ $ijintidakmasuk .= $request->ijintidakmasuk_tambah; }
        if(isset($request->ijintidakmasuk_ubah)){ $ijintidakmasuk .= $request->ijintidakmasuk_ubah; }
        if(isset($request->ijintidakmasuk_hapus)){ $ijintidakmasuk .= $request->ijintidakmasuk_hapus; }
        if(isset($request->ijintidakmasuk_konfirmasi)){ $ijintidakmasuk .= $request->ijintidakmasuk_konfirmasi; }
        $cuti = '';
        if(isset($request->cuti_lihat)){ $cuti .= $request->cuti_lihat; }
        if(isset($request->cuti_ubah)){ $cuti .= $request->cuti_ubah; }
        $jamkerja = '';
        if(isset($request->jamkerja_lihat)){ $jamkerja .= $request->jamkerja_lihat; }
        if(isset($request->jamkerja_tambah)){ $jamkerja .= $request->jamkerja_tambah; }
        if(isset($request->jamkerja_ubah)){ $jamkerja .= $request->jamkerja_ubah; }
        if(isset($request->jamkerja_hapus)){ $jamkerja .= $request->jamkerja_hapus; }
        $jadwalshift = '';
        if(isset($request->jadwalshift_lihat)){ $jadwalshift .= $request->jadwalshift_lihat; }
        if(isset($request->jadwalshift_ubah)){ $jadwalshift .= $request->jadwalshift_ubah; }
        $konfirmasi_flag = '';
        if(isset($request->konfirmasi_flag_lihat)){ $konfirmasi_flag .= $request->konfirmasi_flag_lihat; }
        if(isset($request->konfirmasi_flag_ubah)){ $konfirmasi_flag .= $request->konfirmasi_flag_ubah; }
        if(isset($request->konfirmasi_flag_konfirmasi)){ $konfirmasi_flag .= $request->konfirmasi_flag_konfirmasi; }
        $payrollpengaturan = '';
        if(isset($request->payrollpengaturan_lihat)){ $payrollpengaturan .= $request->payrollpengaturan_lihat; }
        if(isset($request->payrollpengaturan_ubah)){ $payrollpengaturan .= $request->payrollpengaturan_ubah; }
        $payrollkomponenmaster = '';
        if(isset($request->payrollkomponenmaster_lihat)){ $payrollkomponenmaster .= $request->payrollkomponenmaster_lihat; }
        if(isset($request->payrollkomponenmaster_tambah)){ $payrollkomponenmaster .= $request->payrollkomponenmaster_tambah; }
        if(isset($request->payrollkomponenmaster_ubah)){ $payrollkomponenmaster .= $request->payrollkomponenmaster_ubah; }
        if(isset($request->payrollkomponenmaster_hapus)){ $payrollkomponenmaster .= $request->payrollkomponenmaster_hapus; }
        $payrollkomponeninputmanual = '';
        if(isset($request->payrollkomponeninputmanual_lihat)){ $payrollkomponeninputmanual .= $request->payrollkomponeninputmanual_lihat; }
        if(isset($request->payrollkomponeninputmanual_ubah)){ $payrollkomponeninputmanual .= $request->payrollkomponeninputmanual_ubah; }
        $lokasi = '';
        if(isset($request->lokasi_lihat)){ $lokasi .= $request->lokasi_lihat; }
        if(isset($request->lokasi_tambah)){ $lokasi .= $request->lokasi_tambah; }
        if(isset($request->lokasi_ubah)){ $lokasi .= $request->lokasi_ubah; }
        if(isset($request->lokasi_hapus)){ $lokasi .= $request->lokasi_hapus; }
        $logabsen = '';
        if(isset($request->logabsen_lihat)){ $logabsen .= $request->logabsen_lihat; }
        if(isset($request->logabsen_tambah)){ $logabsen .= $request->logabsen_tambah; }
        if(isset($request->logabsen_ubah)){ $logabsen .= $request->logabsen_ubah; }
        if(isset($request->logabsen_hapus)){ $logabsen .= $request->logabsen_hapus; }
        if(isset($request->logabsen_konfirmasi)){ $logabsen .= $request->logabsen_konfirmasi; }
        $fingerprintconnector = '';
        if(isset($request->fingerprintconnector_lihat)){ $fingerprintconnector .= $request->fingerprintconnector_lihat; }
        if(isset($request->fingerprintconnector_tambah)){ $fingerprintconnector .= $request->fingerprintconnector_tambah; }
        if(isset($request->fingerprintconnector_ubah)){ $fingerprintconnector .= $request->fingerprintconnector_ubah; }
        if(isset($request->fingerprintconnector_hapus)){ $fingerprintconnector .= $request->fingerprintconnector_hapus; }
        $mesin = '';
        if(isset($request->mesin_lihat)){ $mesin .= $request->mesin_lihat; }
        if(isset($request->mesin_tambah)){ $mesin .= $request->mesin_tambah; }
        if(isset($request->mesin_ubah)){ $mesin .= $request->mesin_ubah; }
        if(isset($request->mesin_hapus)){ $mesin .= $request->mesin_hapus; }
        $pegawai = '';
        if(isset($request->pegawai_lihat)){ $pegawai .= $request->pegawai_lihat; }
        if(isset($request->pegawai_tambah)){ $pegawai .= $request->pegawai_tambah; }
        if(isset($request->pegawai_ubah)){ $pegawai .= $request->pegawai_ubah; }
        if(isset($request->pegawai_hapus)){ $pegawai .= $request->pegawai_hapus; }
        $setulangkatasandipegawai = '';
        if(isset($request->setulangkatasandipegawai_lihat)){ $setulangkatasandipegawai .= $request->setulangkatasandipegawai_lihat; }
        $aturatributdanlokasi = '';
        if(isset($request->aturatributdanlokasi_ubah)){ $aturatributdanlokasi .= $request->aturatributdanlokasi_ubah; }
        $agama = '';
        if(isset($request->agama_lihat)){ $agama .= $request->agama_lihat; }
        if(isset($request->agama_tambah)){ $agama .= $request->agama_tambah; }
        if(isset($request->agama_ubah)){ $agama .= $request->agama_ubah; }
        if(isset($request->agama_hapus)){ $agama .= $request->agama_hapus; }
        $pekerjaan = '';
        if(isset($request->pekerjaan_lihat)){ $pekerjaan .= $request->pekerjaan_lihat; }
        if(isset($request->pekerjaan_tambah)){ $pekerjaan .= $request->pekerjaan_tambah; }
        if(isset($request->pekerjaan_ubah)){ $pekerjaan .= $request->pekerjaan_ubah; }
        if(isset($request->pekerjaan_hapus)){ $pekerjaan .= $request->pekerjaan_hapus; }
        $pekerjaanuser = '';
        if(isset($request->pekerjaanuser_lihat)){ $pekerjaanuser .= $request->pekerjaanuser_lihat; }
        if(isset($request->pekerjaanuser_tambah)){ $pekerjaanuser .= $request->pekerjaanuser_tambah; }
        if(isset($request->pekerjaanuser_ubah)){ $pekerjaanuser .= $request->pekerjaanuser_ubah; }
        if(isset($request->pekerjaanuser_hapus)){ $pekerjaanuser .= $request->pekerjaanuser_hapus; }
        $pengaturan = '';
        if(isset($request->pengaturan_ubah)){ $pengaturan .= 'l'.$request->pengaturan_ubah; }
        $pengelola = '';
        if(isset($request->pengelola_lihat)){ $pengelola .= $request->pengelola_lihat; }
        if(isset($request->pengelola_ubah)){ $pengelola .= $request->pengelola_ubah; }
        if(isset($request->pengelola_hapus)){ $pengelola .= $request->pengelola_hapus; }
        $perusahaan = '';
        if(isset($request->perusahaan_ubah)){ $perusahaan .= $request->perusahaan_ubah; }
        if(isset($request->perusahaan_hapus)){ $perusahaan .= $request->perusahaan_hapus; }
        $laporan = '';
        if(isset($request->laporan_lihat)){ $laporan .= $request->laporan_lihat; }
        $laporanperpegawai = '';
        if(isset($request->laporanperpegawai_lihat)){ $laporanperpegawai .= $request->laporanperpegawai_lihat; }
        $laporanlogabsen = '';
        if(isset($request->laporanlogabsen_lihat)){ $laporanlogabsen .= $request->laporanlogabsen_lihat; }
        $laporankehadiran = '';
        if(isset($request->laporankehadiran_lihat)){ $laporankehadiran .= $request->laporankehadiran_lihat; }
        $laporanrekapparuhwaktu = '';
        if(isset($request->laporanrekapparuhwaktu_lihat)){ $laporanrekapparuhwaktu .= $request->laporanrekapparuhwaktu_lihat; }
        $laporanpertanggal = '';
        if(isset($request->laporanpertanggal_lihat)){ $laporanpertanggal .= $request->laporanpertanggal_lihat; }
        $laporanekspor = '';
        if(isset($request->laporanekspor_lihat)){ $laporanekspor .= $request->laporanekspor_lihat; }
        $laporanlogtrackerpegawai = '';
        if(isset($request->laporanlogtrackerpegawai_lihat)){ $laporanlogtrackerpegawai .= $request->laporanlogtrackerpegawai_lihat; }
        $laporanlainnya = '';
        if(isset($request->laporanlainnya_lihat)){ $laporanlainnya .= $request->laporanlainnya_lihat; }
        $laporanperlokasi = '';
        if(isset($request->laporanperlokasi_lihat)){ $laporanperlokasi .= $request->laporanperlokasi_lihat; }
        $laporanpekerjaanuser = '';
        if(isset($request->laporanpekerjaanuser_lihat)){ $laporanpekerjaanuser .= $request->laporanpekerjaanuser_lihat; }
        $laporancustom = '';
        if(isset($request->laporancustom_lihat)){ $laporancustom .= $request->laporancustom_lihat; }
        if(isset($request->laporancustom_tambah)){ $laporancustom .= $request->laporancustom_tambah; }
        if(isset($request->laporancustom_ubah)){ $laporancustom .= $request->laporancustom_ubah; }
        if(isset($request->laporancustom_hapus)){ $laporancustom .= $request->laporancustom_hapus; }
        $customdashboard = '';
        if(isset($request->customdashboard_lihat)){ $customdashboard .= $request->customdashboard_lihat; }
        if(isset($request->customdashboard_tambah)){ $customdashboard .= $request->customdashboard_tambah; }
        if(isset($request->customdashboard_ubah)){ $customdashboard .= $request->customdashboard_ubah; }
        if(isset($request->customdashboard_hapus)){ $customdashboard .= $request->customdashboard_hapus; }
        $riwayatpengguna = '';
        if(isset($request->riwayatpengguna_lihat)){ $riwayatpengguna .= $request->riwayatpengguna_lihat; }
        $riwayatpegawai = '';
        if(isset($request->riwayatpegawai_lihat)){ $riwayatpegawai .= $request->riwayatpegawai_lihat; }
        $riwayatsms = '';
        if(isset($request->riwayatsms_lihat)){ $riwayatsms .= $request->riwayatsms_lihat; }
        $postingdata = '';
        if(isset($request->postingdata_ijinkan)){ $postingdata .= $request->postingdata_ijinkan; }
        $hapusdata = '';
        if(isset($request->hapusdata_ijinkan)){ $hapusdata .= $request->hapusdata_ijinkan; }
        $supervisi = '';
        if(isset($request->supervisi_ijinkan)){ $supervisi .= $request->supervisi_ijinkan; }
        $notifikasiijintidakmasuk = '';
        if(isset($request->notifikasiijintidakmasuk_ijinkan)){ $notifikasiijintidakmasuk .= $request->notifikasiijintidakmasuk_ijinkan; }
        $notifikasiriwayatabsen = '';
        if(isset($request->notifikasiriwayatabsen_ijinkan)){ $notifikasiriwayatabsen .= $request->notifikasiriwayatabsen_ijinkan; }
        $notifikasiterlambat = '';
        if(isset($request->notifikasiterlambat_ijinkan)){ $notifikasiterlambat .= $request->notifikasiterlambat_ijinkan; }
        $notifikasipulangawal = '';
        if(isset($request->notifikasipulangawal_ijinkan)){ $notifikasipulangawal .= $request->notifikasipulangawal_ijinkan; }
        $notifikasilembur = '';
        if(isset($request->notifikasilembur_ijinkan)){ $notifikasilembur .= $request->notifikasilembur_ijinkan; }
        $slideshow = '';
        if(isset($request->slideshow_lihat)){ $slideshow .= $request->slideshow_lihat; }
        if(isset($request->slideshow_tambah)){ $slideshow .= $request->slideshow_tambah; }
        if(isset($request->slideshow_ubah)){ $slideshow .= $request->slideshow_ubah; }
        if(isset($request->slideshow_hapus)){ $slideshow .= $request->slideshow_hapus; }

        $alasanmasukkeluar .= strpos($alasanmasukkeluar, 'ltuh') !== false ? 'm' : '';
        $alasantidakmasuk .= strpos($alasantidakmasuk, 'ltuh') !== false ? 'm' : '';
        $atribut .= strpos($atribut, 'ltuh') !== false ? 'm' : '';
        $facesample .= strpos($facesample, 'ltuh') !== false ? 'm' : '';
        $fingersample .= strpos($fingersample, 'ltuh') !== false ? 'm' : '';
        $hakakses .= strpos($hakakses, 'ltuh') !== false ? 'm' : '';
        $harilibur .= strpos($harilibur, 'ltuh') !== false ? 'm' : '';
        $ijintidakmasuk .= strpos($ijintidakmasuk, 'ltuhk') !== false ? 'm' : '';
        $cuti .= strpos($cuti, 'lu') !== false ? 'm' : '';
        $jamkerja .= strpos($jamkerja, 'ltuh') !== false ? 'm' : '';
        $jadwalshift .= strpos($jadwalshift, 'lu') !== false ? 'm' : '';
        $konfirmasi_flag .= strpos($konfirmasi_flag, 'luk') !== false ? 'm' : '';
        $lokasi .= strpos($lokasi, 'ltuh') !== false ? 'm' : '';
        $logabsen .= strpos($logabsen, 'ltuhk') !== false ? 'm' : '';
        $fingerprintconnector .= strpos($fingerprintconnector, 'ltuh') !== false ? 'm' : '';
        $mesin .= strpos($mesin, 'ltuh') !== false ? 'm' : '';
        $pegawai .= strpos($pegawai, 'ltuh') !== false ? 'm' : '';
        $setulangkatasandipegawai .= strpos($setulangkatasandipegawai, 'ltuh') !== false ? 'm' : '';
        $aturatributdanlokasi .= strpos($aturatributdanlokasi, 'u') !== false ? 'm' : '';
        $agama .= strpos($agama, 'ltuh') !== false ? 'm' : '';
        $pekerjaan .= strpos($pekerjaan, 'ltuh') !== false ? 'm' : '';
        $pekerjaanuser .= strpos($pekerjaanuser, 'ltuh') !== false ? 'm' : '';
        $pengaturan .= strpos($pengaturan, 'lu') !== false ? 'm' : '';
        $pengelola .= strpos($pengelola, 'luh') !== false ? 'm' : '';
        $perusahaan .= strpos($perusahaan, 'uh') !== false ? 'lm' : '';
        $laporanperpegawai .= strpos($laporanperpegawai, 'ltuh') !== false ? 'm' : '';
        $laporanlogabsen .= strpos($laporanlogabsen, 'ltuh') !== false ? 'm' : '';
        $laporankehadiran .= strpos($laporankehadiran, 'ltuh') !== false ? 'm' : '';
        $laporanrekapparuhwaktu .= strpos($laporanrekapparuhwaktu, 'ltuh') !== false ? 'm' : '';
        $laporanpertanggal .= strpos($laporanpertanggal, 'ltuh') !== false ? 'm' : '';
        $laporanekspor .= strpos($laporanekspor, 'ltuh') !== false ? 'm' : '';
        $laporanlogtrackerpegawai .= strpos($laporanlogtrackerpegawai, 'ltuh') !== false ? 'm' : '';
        $laporanlainnya .= strpos($laporanlainnya, 'ltuh') !== false ? 'm' : '';
        $laporanperlokasi .= strpos($laporanperlokasi, 'ltuh') !== false ? 'm' : '';
        $laporanpekerjaanuser .= strpos($laporanpekerjaanuser, 'ltuh') !== false ? 'm' : '';
        $laporancustom .= strpos($laporancustom, 'ltuh') !== false ? 'm' : '';
        $customdashboard .= strpos($customdashboard, 'ltuh') !== false ? 'm' : '';
        $riwayatpengguna .= strpos($riwayatpengguna, 'ltuh') !== false ? 'm' : '';
        $riwayatpegawai .= strpos($riwayatpegawai, 'ltuh') !== false ? 'm' : '';
        $riwayatsms .= strpos($riwayatsms, 'ltuh') !== false ? 'm' : '';
        $slideshow .= strpos($slideshow, 'ltuh') !== false ? 'm' : '';
        $batasan .= strpos($batasan, 'ltuh') !== false ? 'm' : '';
        $postingdata .= strpos($postingdata, 'ltuh') !== false ? 'm' : '';
        
        $pdo = DB::getPdo();
        $sql = 'SELECT id FROM hakakses WHERE idperusahaan = :idperusahaan AND nama = :nama';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
        $stmt->bindValue(':nama', $request->nama);
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            $sql = 'INSERT INTO hakakses VALUES(NULL,
                                                :idperusahaan,
                                                :nama,
                                                :ajakan,
                                                :alasanmasukkeluar,
                                                :alasantidakmasuk,
                                                :atribut,
                                                :facesample,
                                                :fingersample,
                                                :hakakses,
                                                :harilibur,
                                                :ijintidakmasuk,
                                                :cuti,
                                                :jamkerja,
                                                :jadwalshift,
                                                :konfirmasi_flag,
                                                :payrollpengaturan,
                                                :payrollkomponenmaster,
                                                :payrollkomponeninputmanual,
                                                :lokasi,
                                                :logabsen,
                                                :fingerprintconnector,
                                                :mesin,
                                                :pegawai,
                                                :setulangkatasandipegawai,
                                                :aturatributdanlokasi,
                                                :agama,
                                                :pekerjaan,
                                                :pekerjaanuser,
                                                :pengaturan,
                                                :pengelola,
                                                :perusahaan,
                                                :laporan,
                                                :laporanperpegawai,
                                                :laporanlogabsen,
                                                :laporankehadiran,
                                                :laporanrekapparuhwaktu,
                                                :laporanpertanggal,
                                                :laporanekspor,
                                                :laporanlogtrackerpegawai,
                                                :laporanlainnya,
                                                :laporanperlokasi,
                                                :laporanpekerjaanuser,
                                                :laporancustom,
                                                :customdashboard,
                                                :riwayatpengguna,
                                                :riwayatpegawai,
                                                :riwayatsms,
                                                :slideshow,
                                                :batasan,
                                                :postingdata,
                                                :hapusdata,
                                                :supervisi,
                                                :notifikasiijintidakmasuk,
                                                :notifikasiriwayatabsen,
                                                :notifikasiterlambat,
                                                :notifikasipulangawal,
                                                :notifikasilembur,
                                                "y",
                                                NOW())';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':ajakan', $ajakan);
            $stmt->bindValue(':alasanmasukkeluar', $alasanmasukkeluar);
            $stmt->bindValue(':alasantidakmasuk', $alasantidakmasuk);
            $stmt->bindValue(':atribut', $atribut);
            $stmt->bindValue(':facesample', $facesample);
            $stmt->bindValue(':fingersample', $fingersample);
            $stmt->bindValue(':hakakses', $hakakses);
            $stmt->bindValue(':harilibur', $harilibur);
            $stmt->bindValue(':ijintidakmasuk', $ijintidakmasuk);
            $stmt->bindValue(':cuti', $cuti);
            $stmt->bindValue(':jamkerja', $jamkerja);
            $stmt->bindValue(':jadwalshift', $jadwalshift);
            $stmt->bindValue(':konfirmasi_flag', $konfirmasi_flag);
            $stmt->bindValue(':payrollpengaturan', $payrollpengaturan);
            $stmt->bindValue(':payrollkomponenmaster', $payrollkomponenmaster);
            $stmt->bindValue(':payrollkomponeninputmanual', $payrollkomponeninputmanual);
            $stmt->bindValue(':lokasi', $lokasi);
            $stmt->bindValue(':logabsen', $logabsen);
            $stmt->bindValue(':fingerprintconnector', $fingerprintconnector);
            $stmt->bindValue(':mesin', $mesin);
            $stmt->bindValue(':pegawai', $pegawai);
            $stmt->bindValue(':setulangkatasandipegawai', $setulangkatasandipegawai);
            $stmt->bindValue(':aturatributdanlokasi', $aturatributdanlokasi);
            $stmt->bindValue(':agama', $agama);
            $stmt->bindValue(':pekerjaan', $pekerjaan);
            $stmt->bindValue(':pekerjaanuser', $pekerjaanuser);
            $stmt->bindValue(':pengaturan', $pengaturan);
            $stmt->bindValue(':pengelola', $pengelola);
            $stmt->bindValue(':perusahaan', $perusahaan);
            $stmt->bindValue(':laporan', $laporan);
            $stmt->bindValue(':laporanperpegawai', $laporanperpegawai);
            $stmt->bindValue(':laporanlogabsen', $laporanlogabsen);
            $stmt->bindValue(':laporankehadiran', $laporankehadiran);
            $stmt->bindValue(':laporanrekapparuhwaktu', $laporanrekapparuhwaktu);
            $stmt->bindValue(':laporanpertanggal', $laporanpertanggal);
            $stmt->bindValue(':laporanekspor', $laporanekspor);
            $stmt->bindValue(':laporanlogtrackerpegawai', $laporanlogtrackerpegawai);
            $stmt->bindValue(':laporanlainnya', $laporanlainnya);
            $stmt->bindValue(':laporanperlokasi', $laporanperlokasi);
            $stmt->bindValue(':laporanpekerjaanuser', $laporanpekerjaanuser);
            $stmt->bindValue(':laporancustom', $laporancustom);
            $stmt->bindValue(':customdashboard', $customdashboard);
            $stmt->bindValue(':riwayatpengguna', $riwayatpengguna);
            $stmt->bindValue(':riwayatpegawai', $riwayatpegawai);
            $stmt->bindValue(':riwayatsms', $riwayatsms);
            $stmt->bindValue(':slideshow', $slideshow);
            $stmt->bindValue(':batasan', $batasan);
            $stmt->bindValue(':postingdata', $postingdata);
            $stmt->bindValue(':hapusdata', $hapusdata);
            $stmt->bindValue(':supervisi', $supervisi);
            $stmt->bindValue(':notifikasiijintidakmasuk', $notifikasiijintidakmasuk);
            $stmt->bindValue(':notifikasiriwayatabsen', $notifikasiriwayatabsen);
            $stmt->bindValue(':notifikasiterlambat', $notifikasiterlambat);
            $stmt->bindValue(':notifikasipulangawal', $notifikasipulangawal);
            $stmt->bindValue(':notifikasilembur', $notifikasilembur);
            $stmt->execute();

            Utils::insertLogUser('Tambah hak akses "'.$request->nama.'"');
    
            return redirect('datainduk/lainlain/hakakses')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/lainlain/hakakses/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('hakakses','um')){
            $hakakses = HakAkses::find($id);
            if(!$hakakses){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah hak akses');
            return view('datainduk/lainlain/hakakses/edit', ['hakakses' => $hakakses, 'menu' => 'hakakses']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::getPdo();
        $sql = 'SELECT idperusahaan,nama FROM hakakses WHERE id=:idhakakses LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idhakakses', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //varialbel
            $ajakan = '';
            if(isset($request->ajakan_ijinkan)){ $ajakan .= $request->ajakan_ijinkan; }
            $alasanmasukkeluar = '';
            if(isset($request->alasanmasukkeluar_lihat)){ $alasanmasukkeluar .= $request->alasanmasukkeluar_lihat; }
            if(isset($request->alasanmasukkeluar_tambah)){ $alasanmasukkeluar .= $request->alasanmasukkeluar_tambah; }
            if(isset($request->alasanmasukkeluar_ubah)){ $alasanmasukkeluar .= $request->alasanmasukkeluar_ubah; }
            if(isset($request->alasanmasukkeluar_hapus)){ $alasanmasukkeluar .= $request->alasanmasukkeluar_hapus; }
            $alasantidakmasuk = '';
            if(isset($request->alasantidakmasuk_lihat)){ $alasantidakmasuk .= $request->alasantidakmasuk_lihat; }
            if(isset($request->alasantidakmasuk_tambah)){ $alasantidakmasuk .= $request->alasantidakmasuk_tambah; }
            if(isset($request->alasantidakmasuk_ubah)){ $alasantidakmasuk .= $request->alasantidakmasuk_ubah; }
            if(isset($request->alasantidakmasuk_hapus)){ $alasantidakmasuk .= $request->alasantidakmasuk_hapus; }
            $atribut = '';
            if(isset($request->atribut_lihat)){ $atribut .= $request->atribut_lihat; }
            if(isset($request->atribut_tambah)){ $atribut .= $request->atribut_tambah; }
            if(isset($request->atribut_ubah)){ $atribut .= $request->atribut_ubah; }
            if(isset($request->atribut_hapus)){ $atribut .= $request->atribut_hapus; }
            $facesample = '';
            if(isset($request->facesample_lihat)){ $facesample .= $request->facesample_lihat; }
            if(isset($request->facesample_tambah)){ $facesample .= $request->facesample_tambah; }
            if(isset($request->facesample_ubah)){ $facesample .= $request->facesample_ubah; }
            if(isset($request->facesample_hapus)){ $facesample .= $request->facesample_hapus; }
            $fingersample = '';
            if(isset($request->fingersample_lihat)){ $fingersample .= $request->fingersample_lihat; }
            if(isset($request->fingersample_tambah)){ $fingersample .= $request->fingersample_tambah; }
            if(isset($request->fingersample_ubah)){ $fingersample .= $request->fingersample_ubah; }
            if(isset($request->fingersample_hapus)){ $fingersample .= $request->fingersample_hapus; }
            $hakakses = '';
            if(isset($request->hakakses_lihat)){ $hakakses .= $request->hakakses_lihat; }
            if(isset($request->hakakses_tambah)){ $hakakses .= $request->hakakses_tambah; }
            if(isset($request->hakakses_ubah)){ $hakakses .= $request->hakakses_ubah; }
            if(isset($request->hakakses_hapus)){ $hakakses .= $request->hakakses_hapus; }
            $batasan = '';
            if(isset($request->batasan_lihat)){ $batasan .= $request->batasan_lihat; }
            if(isset($request->batasan_tambah)){ $batasan .= $request->batasan_tambah; }
            if(isset($request->batasan_ubah)){ $batasan .= $request->batasan_ubah; }
            if(isset($request->batasan_hapus)){ $batasan .= $request->batasan_hapus; }
            $harilibur = '';
            if(isset($request->harilibur_lihat)){ $harilibur .= $request->harilibur_lihat; }
            if(isset($request->harilibur_tambah)){ $harilibur .= $request->harilibur_tambah; }
            if(isset($request->harilibur_ubah)){ $harilibur .= $request->harilibur_ubah; }
            if(isset($request->harilibur_hapus)){ $harilibur .= $request->harilibur_hapus; }
            $ijintidakmasuk = '';
            if(isset($request->ijintidakmasuk_lihat)){ $ijintidakmasuk .= $request->ijintidakmasuk_lihat; }
            if(isset($request->ijintidakmasuk_tambah)){ $ijintidakmasuk .= $request->ijintidakmasuk_tambah; }
            if(isset($request->ijintidakmasuk_ubah)){ $ijintidakmasuk .= $request->ijintidakmasuk_ubah; }
            if(isset($request->ijintidakmasuk_hapus)){ $ijintidakmasuk .= $request->ijintidakmasuk_hapus; }
            if(isset($request->ijintidakmasuk_konfirmasi)){ $ijintidakmasuk .= $request->ijintidakmasuk_konfirmasi; }
            $cuti = '';
            if(isset($request->cuti_lihat)){ $cuti .= $request->cuti_lihat; }
            if(isset($request->cuti_ubah)){ $cuti .= $request->cuti_ubah; }
            $jamkerja = '';
            if(isset($request->jamkerja_lihat)){ $jamkerja .= $request->jamkerja_lihat; }
            if(isset($request->jamkerja_tambah)){ $jamkerja .= $request->jamkerja_tambah; }
            if(isset($request->jamkerja_ubah)){ $jamkerja .= $request->jamkerja_ubah; }
            if(isset($request->jamkerja_hapus)){ $jamkerja .= $request->jamkerja_hapus; }
            $jadwalshift = '';
            if(isset($request->jadwalshift_lihat)){ $jadwalshift .= $request->jadwalshift_lihat; }
            if(isset($request->jadwalshift_ubah)){ $jadwalshift .= $request->jadwalshift_ubah; }
            $konfirmasi_flag = '';
            if(isset($request->konfirmasi_flag_lihat)){ $konfirmasi_flag .= $request->konfirmasi_flag_lihat; }
            if(isset($request->konfirmasi_flag_ubah)){ $konfirmasi_flag .= $request->konfirmasi_flag_ubah; }
            if(isset($request->konfirmasi_flag_konfirmasi)){ $konfirmasi_flag .= $request->konfirmasi_flag_konfirmasi; }
            $payrollpengaturan = '';
            if(isset($request->payrollpengaturan_lihat)){ $payrollpengaturan .= $request->payrollpengaturan_lihat; }
            if(isset($request->payrollpengaturan_ubah)){ $payrollpengaturan .= $request->payrollpengaturan_ubah; }
            $payrollkomponenmaster = '';
            if(isset($request->payrollkomponenmaster_lihat)){ $payrollkomponenmaster .= $request->payrollkomponenmaster_lihat; }
            if(isset($request->payrollkomponenmaster_tambah)){ $payrollkomponenmaster .= $request->payrollkomponenmaster_tambah; }
            if(isset($request->payrollkomponenmaster_ubah)){ $payrollkomponenmaster .= $request->payrollkomponenmaster_ubah; }
            if(isset($request->payrollkomponenmaster_hapus)){ $payrollkomponenmaster .= $request->payrollkomponenmaster_hapus; }
            $payrollkomponeninputmanual = '';
            if(isset($request->payrollkomponeninputmanual_lihat)){ $payrollkomponeninputmanual .= $request->payrollkomponeninputmanual_lihat; }
            if(isset($request->payrollkomponeninputmanual_ubah)){ $payrollkomponeninputmanual .= $request->payrollkomponeninputmanual_ubah; }
            $lokasi = '';
            if(isset($request->lokasi_lihat)){ $lokasi .= $request->lokasi_lihat; }
            if(isset($request->lokasi_tambah)){ $lokasi .= $request->lokasi_tambah; }
            if(isset($request->lokasi_ubah)){ $lokasi .= $request->lokasi_ubah; }
            if(isset($request->lokasi_hapus)){ $lokasi .= $request->lokasi_hapus; }
            $logabsen = '';
            if(isset($request->logabsen_lihat)){ $logabsen .= $request->logabsen_lihat; }
            if(isset($request->logabsen_tambah)){ $logabsen .= $request->logabsen_tambah; }
            if(isset($request->logabsen_ubah)){ $logabsen .= $request->logabsen_ubah; }
            if(isset($request->logabsen_hapus)){ $logabsen .= $request->logabsen_hapus; }
            if(isset($request->logabsen_konfirmasi)){ $logabsen .= $request->logabsen_konfirmasi; }
            $fingerprintconnector = '';
            if(isset($request->fingerprintconnector_lihat)){ $fingerprintconnector .= $request->fingerprintconnector_lihat; }
            if(isset($request->fingerprintconnector_tambah)){ $fingerprintconnector .= $request->fingerprintconnector_tambah; }
            if(isset($request->fingerprintconnector_ubah)){ $fingerprintconnector .= $request->fingerprintconnector_ubah; }
            if(isset($request->fingerprintconnector_hapus)){ $fingerprintconnector .= $request->fingerprintconnector_hapus; }
            $mesin = '';
            if(isset($request->mesin_lihat)){ $mesin .= $request->mesin_lihat; }
            if(isset($request->mesin_tambah)){ $mesin .= $request->mesin_tambah; }
            if(isset($request->mesin_ubah)){ $mesin .= $request->mesin_ubah; }
            if(isset($request->mesin_hapus)){ $mesin .= $request->mesin_hapus; }
            $pegawai = '';
            if(isset($request->pegawai_lihat)){ $pegawai .= $request->pegawai_lihat; }
            if(isset($request->pegawai_tambah)){ $pegawai .= $request->pegawai_tambah; }
            if(isset($request->pegawai_ubah)){ $pegawai .= $request->pegawai_ubah; }
            if(isset($request->pegawai_hapus)){ $pegawai .= $request->pegawai_hapus; }
            $setulangkatasandipegawai = '';
            if(isset($request->setulangkatasandipegawai_lihat)){ $setulangkatasandipegawai .= $request->setulangkatasandipegawai_lihat; }
            $aturatributdanlokasi = '';
            if(isset($request->aturatributdanlokasi_ubah)){ $aturatributdanlokasi .= $request->aturatributdanlokasi_ubah; }
            $agama = '';
            if(isset($request->agama_lihat)){ $agama .= $request->agama_lihat; }
            if(isset($request->agama_tambah)){ $agama .= $request->agama_tambah; }
            if(isset($request->agama_ubah)){ $agama .= $request->agama_ubah; }
            if(isset($request->agama_hapus)){ $agama .= $request->agama_hapus; }
            $pekerjaan = '';
            if(isset($request->pekerjaan_lihat)){ $pekerjaan .= $request->pekerjaan_lihat; }
            if(isset($request->pekerjaan_tambah)){ $pekerjaan .= $request->pekerjaan_tambah; }
            if(isset($request->pekerjaan_ubah)){ $pekerjaan .= $request->pekerjaan_ubah; }
            if(isset($request->pekerjaan_hapus)){ $pekerjaan .= $request->pekerjaan_hapus; }
            $pekerjaanuser = '';
            if(isset($request->pekerjaanuser_lihat)){ $pekerjaanuser .= $request->pekerjaanuser_lihat; }
            if(isset($request->pekerjaanuser_tambah)){ $pekerjaanuser .= $request->pekerjaanuser_tambah; }
            if(isset($request->pekerjaanuser_ubah)){ $pekerjaanuser .= $request->pekerjaanuser_ubah; }
            if(isset($request->pekerjaanuser_hapus)){ $pekerjaanuser .= $request->pekerjaanuser_hapus; }
            $pengaturan = '';
            if(isset($request->pengaturan_ubah)){ $pengaturan .= 'l'.$request->pengaturan_ubah; }
            $pengelola = '';
            if(isset($request->pengelola_lihat)){ $pengelola .= $request->pengelola_lihat; }
            if(isset($request->pengelola_ubah)){ $pengelola .= $request->pengelola_ubah; }
            if(isset($request->pengelola_hapus)){ $pengelola .= $request->pengelola_hapus; }
            $perusahaan = '';
            if(isset($request->perusahaan_ubah)){ $perusahaan .= $request->perusahaan_ubah; }
            if(isset($request->perusahaan_hapus)){ $perusahaan .= $request->perusahaan_hapus; }
            $laporan = '';
            if(isset($request->laporan_lihat)){ $laporan .= $request->laporan_lihat; }
            $laporanperpegawai = '';
            if(isset($request->laporanperpegawai_lihat)){ $laporanperpegawai .= $request->laporanperpegawai_lihat; }
            $laporanlogabsen = '';
            if(isset($request->laporanlogabsen_lihat)){ $laporanlogabsen .= $request->laporanlogabsen_lihat; }
            $laporankehadiran = '';
            if(isset($request->laporankehadiran_lihat)){ $laporankehadiran .= $request->laporankehadiran_lihat; }
            $laporanrekapparuhwaktu = '';
            if(isset($request->laporanrekapparuhwaktu_lihat)){ $laporanrekapparuhwaktu .= $request->laporanrekapparuhwaktu_lihat; }
            $laporanpertanggal = '';
            if(isset($request->laporanpertanggal_lihat)){ $laporanpertanggal .= $request->laporanpertanggal_lihat; }
            $laporanekspor = '';
            if(isset($request->laporanekspor_lihat)){ $laporanekspor .= $request->laporanekspor_lihat; }
            $laporanlogtrackerpegawai = '';
            if(isset($request->laporanlogtrackerpegawai_lihat)){ $laporanlogtrackerpegawai .= $request->laporanlogtrackerpegawai_lihat; }
            $laporanlainnya = '';
            if(isset($request->laporanlainnya_lihat)){ $laporanlainnya .= $request->laporanlainnya_lihat; }
            $laporanperlokasi = '';
            if(isset($request->laporanperlokasi_lihat)){ $laporanperlokasi .= $request->laporanperlokasi_lihat; }
            $laporanpekerjaanuser = '';
            if(isset($request->laporanpekerjaanuser_lihat)){ $laporanpekerjaanuser .= $request->laporanpekerjaanuser_lihat; }
            $laporancustom = '';
            if(isset($request->laporancustom_lihat)){ $laporancustom .= $request->laporancustom_lihat; }
            if(isset($request->laporancustom_tambah)){ $laporancustom .= $request->laporancustom_tambah; }
            if(isset($request->laporancustom_ubah)){ $laporancustom .= $request->laporancustom_ubah; }
            if(isset($request->laporancustom_hapus)){ $laporancustom .= $request->laporancustom_hapus; }
            $customdashboard = '';
            if(isset($request->customdashboard_lihat)){ $customdashboard .= $request->customdashboard_lihat; }
            if(isset($request->customdashboard_tambah)){ $customdashboard .= $request->customdashboard_tambah; }
            if(isset($request->customdashboard_ubah)){ $customdashboard .= $request->customdashboard_ubah; }
            if(isset($request->customdashboard_hapus)){ $customdashboard .= $request->customdashboard_hapus; }
            $riwayatpengguna = '';
            if(isset($request->riwayatpengguna_lihat)){ $riwayatpengguna .= $request->riwayatpengguna_lihat; }
            $riwayatpegawai = '';
            if(isset($request->riwayatpegawai_lihat)){ $riwayatpegawai .= $request->riwayatpegawai_lihat; }
            $riwayatsms = '';
            if(isset($request->riwayatsms_lihat)){ $riwayatsms .= $request->riwayatsms_lihat; }
            $postingdata = '';
            if(isset($request->postingdata_ijinkan)){ $postingdata .= $request->postingdata_ijinkan; }
            $hapusdata = '';
            if(isset($request->hapusdata_ijinkan)){ $hapusdata .= $request->hapusdata_ijinkan; }
            $supervisi = '';
            if(isset($request->supervisi_ijinkan)){ $supervisi .= $request->supervisi_ijinkan; }
            $notifikasiijintidakmasuk = '';
            if(isset($request->notifikasiijintidakmasuk_ijinkan)){ $notifikasiijintidakmasuk .= $request->notifikasiijintidakmasuk_ijinkan; }
            $notifikasiriwayatabsen = '';
            if(isset($request->notifikasiriwayatabsen_ijinkan)){ $notifikasiriwayatabsen .= $request->notifikasiriwayatabsen_ijinkan; }
            $notifikasiterlambat = '';
            if(isset($request->notifikasiterlambat_ijinkan)){ $notifikasiterlambat .= $request->notifikasiterlambat_ijinkan; }
            $notifikasipulangawal = '';
            if(isset($request->notifikasipulangawal_ijinkan)){ $notifikasipulangawal .= $request->notifikasipulangawal_ijinkan; }
            $notifikasilembur = '';
            if(isset($request->notifikasilembur_ijinkan)){ $notifikasilembur .= $request->notifikasilembur_ijinkan; }
            $slideshow = '';
            if(isset($request->slideshow_lihat)){ $slideshow .= $request->slideshow_lihat; }
            if(isset($request->slideshow_tambah)){ $slideshow .= $request->slideshow_tambah; }
            if(isset($request->slideshow_ubah)){ $slideshow .= $request->slideshow_ubah; }
            if(isset($request->slideshow_hapus)){ $slideshow .= $request->slideshow_hapus; }
            
            $alasanmasukkeluar .= strpos($alasanmasukkeluar, 'ltuh') !== false ? 'm' : '';
            $alasantidakmasuk .= strpos($alasantidakmasuk, 'ltuh') !== false ? 'm' : '';
            $atribut .= strpos($atribut, 'ltuh') !== false ? 'm' : '';
            $facesample .= strpos($facesample, 'ltuh') !== false ? 'm' : '';
            $fingersample .= strpos($fingersample, 'ltuh') !== false ? 'm' : '';
            $hakakses .= strpos($hakakses, 'ltuh') !== false ? 'm' : '';
            $harilibur .= strpos($harilibur, 'ltuh') !== false ? 'm' : '';
            $ijintidakmasuk .= strpos($ijintidakmasuk, 'ltuhk') !== false ? 'm' : '';
            $cuti .= strpos($cuti, 'lu') !== false ? 'm' : '';
            $jamkerja .= strpos($jamkerja, 'ltuh') !== false ? 'm' : '';
            $jadwalshift .= strpos($jadwalshift, 'lu') !== false ? 'm' : '';
            $konfirmasi_flag .= strpos($konfirmasi_flag, 'luk') !== false ? 'm' : '';
            $payrollkomponenmaster .= strpos($payrollkomponenmaster, 'ltuh') !== false ? 'm' : '';
            $lokasi .= strpos($lokasi, 'ltuh') !== false ? 'm' : '';
            $logabsen .= strpos($logabsen, 'ltuhk') !== false ? 'm' : '';
            $fingerprintconnector .= strpos($fingerprintconnector, 'ltuh') !== false ? 'm' : '';
            $mesin .= strpos($mesin, 'ltuh') !== false ? 'm' : '';
            $pegawai .= strpos($pegawai, 'ltuh') !== false ? 'm' : '';
            $setulangkatasandipegawai .= strpos($setulangkatasandipegawai, 'ltuh') !== false ? 'm' : '';
            $aturatributdanlokasi .= strpos($aturatributdanlokasi, 'u') !== false ? 'm' : '';
            $agama .= strpos($agama, 'ltuh') !== false ? 'm' : '';
            $pekerjaan .= strpos($pekerjaan, 'ltuh') !== false ? 'm' : '';
            $pekerjaanuser .= strpos($pekerjaanuser, 'ltuh') !== false ? 'm' : '';
            $pengaturan .= strpos($pengaturan, 'lu') !== false ? 'm' : '';
            $pengelola .= strpos($pengelola, 'luh') !== false ? 'm' : '';
            $perusahaan .= strpos($perusahaan, 'uh') !== false ? 'lm' : '';
            $laporanperpegawai .= strpos($laporanperpegawai, 'ltuh') !== false ? 'm' : '';
            $laporanlogabsen .= strpos($laporanlogabsen, 'ltuh') !== false ? 'm' : '';
            $laporankehadiran .= strpos($laporankehadiran, 'ltuh') !== false ? 'm' : '';
            $laporanrekapparuhwaktu .= strpos($laporanrekapparuhwaktu, 'ltuh') !== false ? 'm' : '';
            $laporanpertanggal .= strpos($laporanpertanggal, 'ltuh') !== false ? 'm' : '';
            $laporanekspor .= strpos($laporanekspor, 'ltuh') !== false ? 'm' : '';
            $laporanlogtrackerpegawai .= strpos($laporanlogtrackerpegawai, 'ltuh') !== false ? 'm' : '';
            $laporanlainnya .= strpos($laporanlainnya, 'ltuh') !== false ? 'm' : '';
            $laporanperlokasi .= strpos($laporanperlokasi, 'ltuh') !== false ? 'm' : '';
            $laporanpekerjaanuser .= strpos($laporanpekerjaanuser, 'ltuh') !== false ? 'm' : '';
            $laporancustom .= strpos($laporancustom, 'ltuh') !== false ? 'm' : '';
            $customdashboard .= strpos($customdashboard, 'ltuh') !== false ? 'm' : '';
            $riwayatpengguna .= strpos($riwayatpengguna, 'ltuh') !== false ? 'm' : '';
            $riwayatpegawai .= strpos($riwayatpegawai, 'ltuh') !== false ? 'm' : '';
            $riwayatsms .= strpos($riwayatsms, 'ltuh') !== false ? 'm' : '';
            $slideshow .= strpos($slideshow, 'ltuh') !== false ? 'm' : '';
            $batasan .= strpos($batasan, 'ltuh') !== false ? 'm' : '';
            $postingdata .= strpos($postingdata, 'ltuh') !== false ? 'm' : '';

            $pdo = DB::getPdo();
            $sql = 'SELECT id FROM hakakses WHERE idperusahaan = :idperusahaan AND nama = :nama AND id != :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount()==0) {
                $hakaksess = HakAkses::find($id);
                $hakaksess->idperusahaan = Session::get('conf_webperusahaan');
                $hakaksess->nama = $request->nama;
                $hakaksess->ajakan = $ajakan;
                $hakaksess->alasanmasukkeluar = $alasanmasukkeluar;
                $hakaksess->alasantidakmasuk = $alasantidakmasuk;
                $hakaksess->atribut = $atribut;
                $hakaksess->facesample = $facesample;
                $hakaksess->fingersample = $fingersample;
                $hakaksess->hakakses = $hakakses;
                $hakaksess->harilibur = $harilibur;
                $hakaksess->ijintidakmasuk = $ijintidakmasuk;
                $hakaksess->cuti = $cuti;
                $hakaksess->jamkerja = $jamkerja;
                $hakaksess->jadwalshift = $jadwalshift;
                $hakaksess->konfirmasi_flag = $konfirmasi_flag;
                $hakaksess->payrollpengaturan = $payrollpengaturan;
                $hakaksess->payrollkomponenmaster = $payrollkomponenmaster;
                $hakaksess->payrollkomponeninputmanual = $payrollkomponeninputmanual;
                $hakaksess->lokasi = $lokasi;
                $hakaksess->logabsen = $logabsen;
                $hakaksess->fingerprintconnector = $fingerprintconnector;
                $hakaksess->mesin = $mesin;
                $hakaksess->pegawai = $pegawai;
                $hakaksess->setulangkatasandipegawai = $setulangkatasandipegawai;
                $hakaksess->aturatributdanlokasi = $aturatributdanlokasi;
                $hakaksess->agama = $agama;
                $hakaksess->pekerjaan = $pekerjaan;
                $hakaksess->pekerjaanuser = $pekerjaanuser;
                $hakaksess->pengaturan = $pengaturan;
                $hakaksess->pengelola = $pengelola;
                $hakaksess->perusahaan = $perusahaan;
                $hakaksess->slideshow = $slideshow;
                $hakaksess->batasan = $batasan;
                $hakaksess->postingdata = $postingdata;
                $hakaksess->laporan = $laporan;
                $hakaksess->laporanperpegawai = $laporanperpegawai;
                $hakaksess->laporanlogabsen = $laporanlogabsen;
                $hakaksess->laporankehadiran = $laporankehadiran;
                $hakaksess->laporanrekapparuhwaktu = $laporanrekapparuhwaktu;
                $hakaksess->laporanpertanggal = $laporanpertanggal;
                $hakaksess->laporanekspor = $laporanekspor;
                $hakaksess->laporanlogtrackerpegawai = $laporanlogtrackerpegawai;
                $hakaksess->laporanlainnya = $laporanlainnya;
                $hakaksess->laporanperlokasi = $laporanperlokasi;
                $hakaksess->laporanpekerjaanuser = $laporanpekerjaanuser;
                $hakaksess->laporancustom = $laporancustom;
                $hakaksess->customdashboard = $customdashboard;
                $hakaksess->riwayatpengguna = $riwayatpengguna;
                $hakaksess->riwayatpegawai = $riwayatpegawai;
                $hakaksess->riwayatsms = $riwayatsms;
                $hakaksess->hapusdata = $hapusdata;
                $hakaksess->supervisi = $supervisi;
                $hakaksess->notifikasiijintidakmasuk = $notifikasiijintidakmasuk;
                $hakaksess->notifikasiriwayatabsen = $notifikasiriwayatabsen;
                $hakaksess->notifikasiterlambat = $notifikasiterlambat;
                $hakaksess->notifikasipulangawal = $notifikasipulangawal;
                $hakaksess->notifikasilembur = $notifikasilembur;
                $hakaksess->save();

                Utils::insertLogUser('Ubah hak akses "' . $row['nama'] . '" => "' . $request->nama . '"');

                return redirect('datainduk/lainlain/hakakses')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/lainlain/hakakses/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/lainlain/hakakses/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('hakakses','hm')){
            $pdo = DB::getPdo();
            $sql = 'SELECT _flaghapus FROM hakakses WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row['_flaghapus'] == 't'){
                return redirect('datainduk/lainlain/hakakses')->with('message', trans('all.datatidakbolehdihapus'));
            }

            $sql = 'SELECT nama FROM hakakses WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $hakakses = HakAkses::find($id);
                $hakakses->delete();
                Utils::insertLogUser('Hapus hak akses "'.$row['nama'].'"');
                
                return redirect('datainduk/lainlain/hakakses')->with('message', trans('all.databerhasildihapus'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('hakakses','l')){
            $pdo = DB::getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.hakakses'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', trans('all.nama'));

            $where = '1=2';
            if (Session::has('conf_webperusahaan')) {
                $where = 'idperusahaan =' . Session::get('conf_webperusahaan');
            }
            $sql = 'SELECT nama FROM hakakses WHERE ' . $where . ' ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor hak akses');
            $arrWidth = array(50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.hakakses'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}