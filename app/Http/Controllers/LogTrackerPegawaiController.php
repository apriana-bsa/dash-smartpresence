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

class LogTrackerPegawaiController extends Controller
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
        if(Utils::cekHakakses('laporanlogtrackerpegawai','l')){
            //lokasi
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,nama,lat,lon FROM lokasi';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $lokasi = $stmt->fetchAll(PDO::FETCH_OBJ);

            $pegawai = '';
            $data = '';
            $tanggalawal = date('d/m/Y');
            $tanggalakhir = date('d/m/Y');
            $jamawal = '00:00:00';
            $jamakhir = '23:59:59';
            if(Session::has('logtrackerpegawai_idpegawai')){
//                $valuetglawalakhir = array();
                $tanggalawal = Session::get('logtrackerpegawai_tanggalawal');
                $tanggalakhir = Session::get('logtrackerpegawai_tanggalakhir');
                $jamawal = Session::get('logtrackerpegawai_jamawal');
                $jamakhir = Session::get('logtrackerpegawai_jamakhir');
                $pegawai = Utils::getData($pdo,'pegawai','id,nama','id='.Session::get('logtrackerpegawai_idpegawai'));

                $sql = 'SELECT
                            ptl.id,
                            ptl.lat,
                            ptl.lon,
                            ptl.waktu as waktunormal,
                            DATE_FORMAT(ptl.waktu, "%d/%m/%Y %T") as waktu,
                            IFNULL(ptl.idlogabsen,"") as idlogabsen,
                            IFNULL(la.masukkeluar,"t") as jenis
                        FROM
                            pegawaitracker_log ptl
                            LEFT JOIN
                            (
                              SELECT 
                                id,
                                masukkeluar
                              FROM
                                logabsen 
                              WHERE
                                waktu BETWEEN STR_TO_DATE(:tanggal1_la,"%d/%m/%Y %T")  AND STR_TO_DATE(:tanggal2_la,"%d/%m/%Y %T")
                            ) la ON ISNULL(ptl.idlogabsen) = false AND ptl.idlogabsen=la.id
                        WHERE
                            ptl.idpegawai=:idpegawai AND
                            ptl.waktu BETWEEN STR_TO_DATE(:tanggal1,"%d/%m/%Y %T")  AND STR_TO_DATE(:tanggal2,"%d/%m/%Y %T")
                        ORDER BY waktu ASC';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', Session::get('logtrackerpegawai_idpegawai'));
                $stmt->bindValue(':tanggal1', Session::get('logtrackerpegawai_tanggalawal').' '.Session::get('logtrackerpegawai_jamawal'));
                $stmt->bindValue(':tanggal2', Session::get('logtrackerpegawai_tanggalakhir').' '.Session::get('logtrackerpegawai_jamakhir'));
                $stmt->bindValue(':tanggal1_la', Session::get('logtrackerpegawai_tanggalawal').' '.Session::get('logtrackerpegawai_jamawal'));
                $stmt->bindValue(':tanggal2_la', Session::get('logtrackerpegawai_tanggalakhir').' '.Session::get('logtrackerpegawai_jamakhir'));
                $stmt->execute();
                if($stmt->rowCount() > 0){
                    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                }
            }
            Utils::insertLogUser('akses menu log tracker pegawai');
            return view('logtrackerpegawai/index', ['tanggalawal' => $tanggalawal, 'tanggalakhir' => $tanggalakhir, 'jamawal' => $jamawal, 'jamakhir' => $jamakhir, 'pegawai' => $pegawai, 'lokasi' => $lokasi, 'data' => $data, 'menu' => 'logtrackerpegawai']);
        } else {
            return redirect('/');
        }
    }

    public function submitIndex(Request $request){

        if(Utils::cekDateTime($request->tanggalawal) && Utils::cekDateTime($request->tanggalakhir) && Utils::cekDateTime($request->jamawal) && Utils::cekDateTime($request->jamakhir)) {
            $idpegawai = $request->pegawai;
            $tanggalawal = $request->tanggalawal;
            $tanggalakhir = $request->tanggalakhir;
            $jamawal = $request->jamawal;
            $jamakhir = $request->jamakhir;

            Session::set('logtrackerpegawai_idpegawai', $idpegawai);
            Session::set('logtrackerpegawai_tanggalawal', $tanggalawal);
            Session::set('logtrackerpegawai_tanggalakhir', $tanggalakhir);
            Session::set('logtrackerpegawai_jamawal', $jamawal);
            Session::set('logtrackerpegawai_jamakhir', $jamakhir);
        }
        return redirect('logtrackerpegawai');
    }

    public function reset(){
        if(Session::has('logtrackerpegawai_idpegawai')){
            Session::forget('logtrackerpegawai_idpegawai');
            Session::forget('logtrackerpegawai_tanggalawal');
            Session::forget('logtrackerpegawai_tanggalakhir');
            Session::forget('logtrackerpegawai_jamawal');
            Session::forget('logtrackerpegawai_jamakhir');
        }

        return redirect('logtrackerpegawai');
    }
}