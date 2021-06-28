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

class customTvController extends Controller
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
        if(Utils::cekHakakses('pengaturan','lum')){
	        $pdo = DB::connection('perusahaan_db')->getPdo();
	        $sql = 'SELECT header1,header2,bahasa,IFNULL(atribut_nip,"") as atribut_nip,atribut_nip_caption,IFNULL(atribut_jabatan,"") as atribut_jabatan,atribut_jabatan_caption,tampil_terlambat,tampil_pulangawal,tampil_ijintidakmasuk,tampil_kehadiranterbaik,tampil_belumabsen,tampil_logabsen,warna_background,warna_headerfooter,warna_headerfooter_text,warna_card,warna_card_text FROM customtv';
	        $stmt = $pdo->prepare($sql);
	        $stmt->execute();
	        $data = $stmt->fetch(PDO::FETCH_OBJ);

	        $dataatributvariable = Utils::getData($pdo,'atributvariable','id,atribut','','atribut');
			$dataatribut = Utils::getData($pdo,'atribut','id,atribut','','atribut');
			Utils::insertLogUser('akses menu custom tv');
            return view('pengaturan/customtv/index', ['data' => $data, 'dataatributvariable' => $dataatributvariable, 'dataatribut' => $dataatribut, 'menu' => 'customtv']);
        }else{
            return redirect('/');
        }
	}

	public function submit(Request $request)
	{
	    if(strpos(Session::get('hakakses_perusahaan')->pengaturan, 'l') !== false){
	        $pdo = DB::connection('perusahaan_db')->getPdo();
	        $sql = 'UPDATE customtv SET header1 = :header1, header2 = :header2 ,bahasa = :bahasa ,atribut_nip = :atribut_nip , atribut_nip_caption = :atribut_nip_caption ,atribut_jabatan = :atribut_jabatan ,atribut_jabatan_caption = :atribut_jabatan_caption,tampil_terlambat = :tampil_terlambat, tampil_pulangawal = :tampil_pulangawal, tampil_ijintidakmasuk = :tampil_ijintidakmasuk, tampil_kehadiranterbaik = :tampil_kehadiranterbaik, tampil_belumabsen = :tampil_belumabsen, tampil_logabsen = :tampil_logabsen, warna_background = :warna_background, warna_headerfooter = :warna_headerfooter, warna_headerfooter_text = :warna_headerfooter_text, warna_card = :warna_card, warna_card_text = :warna_card_text';
	        $stmt = $pdo->prepare($sql);
	        $stmt->bindValue(':header1', $request->header1);
	        $stmt->bindValue(':header2', $request->header2);
	        $stmt->bindValue(':bahasa', $request->bahasa);
            $stmt->bindValue(':atribut_nip', ($request->atribut_nip == '' ? NULL : $request->atribut_nip));
            $stmt->bindValue(':atribut_nip_caption', $request->atribut_nip_caption);
            $stmt->bindValue(':atribut_jabatan', ($request->atribut_jabatan == '' ? NULL : $request->atribut_jabatan));
            $stmt->bindValue(':atribut_jabatan_caption', $request->atribut_jabatan_caption);
            $stmt->bindValue(':tampil_terlambat', ($request->tampil_terlambat == '' ? 't' : 'y'));
	        $stmt->bindValue(':tampil_pulangawal', ($request->tampil_pulangawal == '' ? 't' : 'y'));
	        $stmt->bindValue(':tampil_ijintidakmasuk', ($request->tampil_ijintidakmasuk == '' ? 't' : 'y'));
	        $stmt->bindValue(':tampil_kehadiranterbaik', ($request->tampil_kehadiranterbaik == '' ? 't' : 'y'));
	        $stmt->bindValue(':tampil_belumabsen', ($request->tampil_belumabsen == '' ? 't' : 'y'));
	        $stmt->bindValue(':tampil_logabsen', ($request->tampil_logabsen == '' ? 't' : 'y'));
            $stmt->bindValue(':warna_background', $request->warna_background);
            $stmt->bindValue(':warna_headerfooter', $request->warna_headerfooter);
            $stmt->bindValue(':warna_headerfooter_text', $request->warna_headerfooter_text);
            $stmt->bindValue(':warna_card', $request->warna_card);
            $stmt->bindValue(':warna_card_text', $request->warna_card_text);
			$stmt->execute();
			
			Utils::insertLogUser('Ubah custom tv');

            return redirect('pengaturan/customtv')->with('message', trans('all.databerhasildiubah'));
        }else{
            return redirect('/');
        }
	}


}