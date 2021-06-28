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

class LogTrackerPegawaiRealTimeController extends Controller
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

            //filter
            $sqlWhere = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan!='') {
                $sqlWhere = ' AND p.id IN '.$batasan;
            }

            if(Session::has('logtrackerpegawairealtime_atributfilter')){
                $atributs = Session::get('logtrackerpegawairealtime_atributfilter');
                $atributnilai = Utils::atributNilai($atributs);
                $sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.'))';
            }

            if(Session::has('logtrackerpegawairealtime_jamkerja')) {
                if (Session::get('logtrackerpegawairealtime_jamkerja') != '') {
                    $sql = 'CALL pegawaijenisjamkerja(CURRENT_DATE(),:jenisjamkerja)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':jenisjamkerja', Session::get('logtrackerpegawairealtime_jamkerja'));
                    $stmt->execute();

                    $sqlWhere .= ' AND p.id IN (SELECT idpegawai FROM _pegawaijenisjamkerja) ';
                }
            }
            
            $data = '';
            $sql = 'SELECT
                        ptl.*,
                        p.nama as pegawai,
                        DATE_FORMAT(ptl.waktu, "%d/%m/%Y %T") as waktu
                    FROM
                        pegawaitracker_log ptl
                        LEFT JOIN (
                            SELECT
                                id,
                                masukkeluar
                            FROM
                                logabsen
                            WHERE
                                waktu BETWEEN SUBDATE(NOW(), INTERVAL 2 HOUR) AND NOW()
                        ) la ON la.id=ptl.idlogabsen,
                        (
                            SELECT
                                idpegawai,
                                MAX(id) as id
                            FROM
                                pegawaitracker_log
                            WHERE
                                waktu BETWEEN SUBDATE(NOW(), INTERVAL 2 HOUR) AND NOW()
                            GROUP BY
                                idpegawai
                        ) x,
                        pegawai p
                    WHERE
                        x.id=ptl.id AND
                        (ISNULL(ptl.idlogabsen)=true OR la.masukkeluar="m") AND
                        ptl.idpegawai=p.id'.$sqlWhere.'
                    ORDER BY ptl.id ASC, ptl.waktu DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            }

            // untuk filter
            $atribut = Utils::getAtribut();
//            $jamkerjakategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
            Utils::insertLogUser('akses menu log tracker pagawai real time');
            return view('logtrackerpegawairealtime/index', ['atribut' => $atribut, 'lokasi' => $lokasi, 'data' => $data, 'menu' => 'logtrackerpegawai']);
        } else {
            return redirect('/');
        }
    }

    public function getData(){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $data = '';
        $sqlWhere = '';
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $sqlWhere = ' AND p.id IN '.$batasan;
        }

        if(Session::has('logtrackerpegawairealtime_atributfilter')){
            $atributs = Session::get('logtrackerpegawairealtime_atributfilter');
            $atributnilai = Utils::atributNilai($atributs);
            $sqlWhere .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.'))';
        }

        $sql = 'SELECT
                        ptl.*,
                        p.nama as pegawai,
                        DATE_FORMAT(ptl.waktu, "%d/%m/%Y %T") as waktu
                    FROM
                        pegawaitracker_log ptl
                        LEFT JOIN (
                            SELECT
                                id,
                                masukkeluar
                            FROM
                                logabsen
                            WHERE
                                waktu BETWEEN SUBDATE(NOW(), INTERVAL 2 HOUR) AND NOW()
                        ) la ON la.id=ptl.idlogabsen,
                        (
                            SELECT
                                idpegawai,
                                MAX(id) as id
                            FROM
                                pegawaitracker_log
                            WHERE
                                waktu BETWEEN SUBDATE(NOW(), INTERVAL 2 HOUR) AND NOW()
                            GROUP BY
                                idpegawai
                        ) x,
                        pegawai p
                    WHERE
                        x.id=ptl.id AND
                        (ISNULL(ptl.idlogabsen)=true OR la.masukkeluar="m") AND
                        ptl.idpegawai=p.id'.$sqlWhere.'
                    ORDER BY ptl.id ASC, ptl.waktu DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        return $data;
    }

    public function submitIndex(Request $request){
        if(isset($request->atributnilai)) {
            Session::set('logtrackerpegawairealtime_atributfilter', $request->atributnilai);
        }else{
            Session::forget('logtrackerpegawairealtime_atributfilter');
        }

        if(isset($request->jamkerja)) {
            Session::set('logtrackerpegawairealtime_jamkerja', $request->jamkerja);
        }else{
            Session::forget('logtrackerpegawairealtime_jamkerja');
        }

        return redirect('logtrackerpegawairealtime');
    }
}