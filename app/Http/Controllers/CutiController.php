<?php
namespace App\Http\Controllers;

use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class CutiController extends Controller
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
        if(Utils::cekHakakses('cuti','lum')){
            if(Session::has('cuti_tahun')){
                return $this->data();
            }else{
                $tahun = Utils::tahunDropdown();
                $atributs = Utils::getAtribut();
                Utils::insertLogUser('akses menu cuti');
                return view('datainduk/absensi/cuti/index', ['tahun' => $tahun, 'atributs' => $atributs, 'menu' => 'cuti']);
            }
        }else{
            return redirect('/');
        }
	}

    public function submit(Request $request)
    {
        Session::set('cuti_tahun',$request->tahun);
        Session::set('cuti_atribut',$request->atributnilai);

        if($request->tahun == ''){
            Session::forget('cuti_tahun');
        }
        if($request->atributnilai == ''){
            Session::forget('cuti_atribut');
        }

        return $this->data();
    }

    public function data(){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $data = '';
        if(Session::has('cuti_tahun')){
            $where = '';
            if(Session::has('cuti_atribut')){
                $atributwhere = Session::get('cuti_atribut');
                $atributnilaiwhere = Utils::atributNilai($atributwhere);
                $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilaiwhere.') )';
            }

            $sql = 'CALL get_cuti(:tahun)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tahun', Session::get('cuti_tahun'));
            $stmt->execute();

            $sql = 'SELECT
                        p.id,
                        p.pin,
                        p.nama,
                        CONCAT("<span title=\"",p.nama,"\" class=\"detailpegawai\" onclick=\"detailpegawai(,p.id,)\" style=\"cursor:pointer;\">",p.nama,"</span>") as pegawai,
                        getatributpegawai_all(p.id) as atribut,
                        IFNULL(c.jumlah,0) as jumlahcuti, 
                        IFNULL(cc.lama,0) as cutiterpakai,
                        CONCAT(IFNULL(c.jumlah,0) - IFNULL(cc.lama,0)) as sisa
                    FROM
                        pegawai p
                        LEFT JOIN cuti c ON c.idpegawai=p.id AND c.tahun=:tahun 
                        LEFT JOIN _cuti cc ON cc.idpegawai=p.id 
                        LEFT JOIN pegawaiatribut pa ON pa.idpegawai=p.id
                        LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
                        LEFT JOIN atribut a ON an.idatribut=a.id
                    WHERE
                        p.del = "t"
                    '.$where.'
                    GROUP BY
                        p.id
                    ORDER BY
                        p.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tahun', Session::get('cuti_tahun'));
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        $atributs = Session::get('cuti_atribut');
        $atributnilai = Utils::atributNilai($atributs);
        $atributnilaiketerangan = '';
        if($atributnilai != ''){
            $atributnilaidipilih = Utils::getAtributSelected($atributnilai);
            $atributnilaiketerangan = Utils::atributNilaiKeterangan($atributnilaidipilih);
        }

        $keterangan = trans('all.tahun').': '.Session::get('cuti_tahun').' '.$atributnilaiketerangan;
        return view('datainduk/absensi/cuti/data', ['data' => $data, 'keterangan' => $keterangan, 'menu' => 'cuti']);
    }

    public function submitSimpan(Request $request)
    {
//        return count($request->idpegawai);
        if(strpos(Session::get('hakakses_perusahaan')->cuti, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->cuti, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->cuti, 'm') !== false){
            if (Session::has('cuti_tahun')) {
                $pdo = DB::connection('perusahaan_db')->getPdo();
                try {
                    $pdo->beginTransaction();
                    for ($i = 0; $i < count($request->idpegawai); $i++) {
                        if ($request->jumlahcuti[$i] != '') {
                            $sql = 'INSERT INTO cuti VALUES(NULL,:tahun,:idpegawai,:jumlah) ON DUPLICATE KEY UPDATE jumlah = :jumlah2';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tahun', Session::get('cuti_tahun'));
                            $stmt->bindValue(':idpegawai', $request->idpegawai[$i]);
                            $stmt->bindValue(':jumlah', $request->jumlahcuti[$i]);
                            $stmt->bindValue(':jumlah2', $request->jumlahcuti[$i]);
                            $stmt->execute();
                        }
                    }
                    $pdo->commit();
                    Utils::insertLogUser('Ubah cuti tahun ' . Session::get('cuti_tahun'));
                    return redirect('datainduk/absensi/cuti')->with('message', trans('all.databerhasildisimpan'));
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return redirect('datainduk/absensi/cuti')->with('message', $e->getMessage());
                }
            }
        }
        return '';
    }

    public function excel()
    {
        if(strpos(Session::get('hakakses_perusahaan')->cuti, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->cuti, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->cuti, 'm') !== false){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.cuti'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.pin'))
                        ->setCellValue('B1', trans('all.nama'))
                        ->setCellValue('C1', trans('all.atribut'))
                        ->setCellValue('D1', trans('all.jumlahcuti'))
                        ->setCellValue('E1', trans('all.terpakai'))
                        ->setCellValue('F1', trans('all.sisa'));

            $where = '';
            if(Session::has('cuti_atribut')){
                $atributwhere = Session::get('cuti_atribut');
                $atributnilaiwhere = Utils::atributNilai($atributwhere);
                $where .= ' WHERE p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilaiwhere.') )';
            }

            $sql = 'CALL get_cuti(:tahun)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tahun', Session::get('cuti_tahun'));
            $stmt->execute();

            $sql = 'SELECT
                        p.id,
                        p.pin,
                        p.nama,
                        IF(a.penting="y",GROUP_CONCAT(a.atribut," : ",an.nilai ORDER BY a.atribut SEPARATOR ", "),"") as atribut,
                        IFNULL(c.jumlah,0) as jumlahcuti, 
                        IFNULL(cc.lama,0) as cutiterpakai,
                        CONCAT(IFNULL(c.jumlah,0) - IFNULL(cc.lama,0)) as sisa
                    FROM
                        pegawai p
                        LEFT JOIN cuti c ON c.idpegawai=p.id AND c.tahun=:tahun 
                        LEFT JOIN _cuti cc ON cc.idpegawai=p.id 
                        LEFT JOIN pegawaiatribut pa ON pa.idpegawai=p.id
                        LEFT JOIN atributnilai an ON pa.idatributnilai=an.id
                        LEFT JOIN atribut a ON an.idatribut=a.id
                    '.$where.'
                    GROUP BY
                        p.id
                    ORDER BY
                        p.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tahun', Session::get('cuti_tahun'));
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['pin']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['atribut']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['jumlahcuti']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['cutiterpakai']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['sisa']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor cuti');
            $arrWidth = array(10, 50, 50, 15, 15, 15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.cuti'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}