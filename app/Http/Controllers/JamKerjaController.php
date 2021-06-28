<?php
namespace App\Http\Controllers;

use App\JamKerja;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class JamKerjaController extends Controller
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
            $onboarding = $request->query('onboarding');
            Utils::insertLogUser('akses menu jam kerja');
            return view('datainduk/absensi/jamkerja/index', ['menu' => 'jamkerja', 'onboarding'=>$onboarding]);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $onboarding = $request->query('onboarding');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $table = '(
                        SELECT
                            jk.id,
                            jk.nama,
                            jkk.nama as kategori,
                            jk.toleransi,
                            jk.acuanterlambat,
                            jk.hitunglemburstlh,
                            jk.jenis,
                            jk.digunakan
                        FROM
                            jamkerja jk
                            LEFT JOIN jamkerjakategori jkk ON jk.idkategori=jkk.id
                      ) x';
            $columns = array('','nama','kategori','toleransi','acuanterlambat','hitunglemburstlh','jenis','digunakan');
            $totalData = Utils::getDataCustomWhere($pdo,'jamkerja', 'count(id)');
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
            if(!empty($originaldata)){
                foreach($originaldata as $key){
                    $onboardingParam = $onboarding ? '?onboarding='. $onboarding : '';
                    $action = '<a title="' . trans('all.detail') . '" href="jamkerja/' . $key['id'] . '/' . $key['jenis'] . $onboardingParam . '"><i class="fa fa-pencil-square" style="color:#A2A2A2"></i></a>&nbsp;&nbsp;';
                    if(Utils::cekHakakses('jamkerja','um')){
                        $action .= Utils::tombolManipulasi('ubah','jamkerja',$key['id']);
                    }
                    if(Utils::cekHakakses('jamkerja','hm')){
                        $action .= Utils::tombolManipulasi('hapus','jamkerja',$key['id']);
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        if($columns[$i] == 'toleransi' || $columns[$i] == 'hitunglemburstlh') {
                            $tempdata[$columns[$i]] = $key[$columns[$i]].' '.trans('all.menit');
                        }elseif($columns[$i] == 'acuanterlambat') {
                            $tempdata[$columns[$i]] = trans('all.'.$key[$columns[$i]]);
//                        }elseif($columns[$i] == 'hitunglemburstlh') {
//                            $tempdata[$columns[$i]] = $key[$columns[$i]];
                        }elseif($columns[$i] == 'jenis' || $columns[$i] == 'digunakan') {
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

	public function create(Request $request)
    {
        if(Utils::cekHakakses('jamkerja','tm')){
            $onboarding = $request->query('onboarding');
            Utils::insertLogUser('akses menu tambah jam kerja');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $dataKategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
            return view('datainduk/absensi/jamkerja/create', ['datakategori' => $dataKategori, 'menu' => 'jamkerja', 'onboarding'=>$onboarding]);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $onboarding = $request->query('onboarding');
        $urlListingJamKerja = 'datainduk/absensi/jamkerja';
        $urlListingJamKerjaCreate = $urlListingJamKerja . '/create';
        $urlListingJamKerja = $onboarding ? $urlListingJamKerja . '?onboarding=' . $onboarding : $urlListingJamKerja;
        $urlListingJamKerjaCreate = $onboarding ? $urlListingJamKerjaCreate . '?onboarding=' . $onboarding : $urlListingJamKerjaCreate;

        $pdo = DB::connection('perusahaan_db')->getPdo();
        // //cek apakah nama kembar?
        $sql = 'SELECT id FROM jamkerja WHERE nama=:nama LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nama', $request->nama);
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            $jamkerja = new JamKerja;
            $jamkerja->nama = $request->nama;
            $jamkerja->idkategori = ($request->kategori == '' ? NULL : $request->kategori);
            $jamkerja->jenis = $request->jenis;
            $jamkerja->toleransi = $request->toleransi;
            $jamkerja->acuanterlambat = $request->acuanterlambat;
            $jamkerja->hitunglemburstlh = $request->hitunglemburstlh;
            $jamkerja->digunakan = $request->digunakan;
            $jamkerja->inserted = date('Y-m-d H:i:s');
            $jamkerja->save();

            Utils::insertLogUser('Tambah jam kerja "'.$request->nama.'"');
    
            return redirect($urlListingJamKerja)->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect($urlListingJamKerjaCreate)->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('jamkerja','um')){
            $jamkerja = JamKerja::find($id);

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $dataKategori = Utils::getData($pdo,'jamkerjakategori','id,nama','digunakan="y"','nama');
            $sql = 'SELECT id FROM jamkerjafull WHERE idjamkerja = :idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $id);
            $stmt->execute();
            $totalJamkerjaFull = $stmt->rowCount();

            $sql = 'SELECT id FROM jamkerjashift WHERE idjamkerja = :idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerja', $id);
            $stmt->execute();
            $totalJamkerjaShift = $stmt->rowCount();

            $adajamkerja = false;
            if($totalJamkerjaFull != 0 or $totalJamkerjaShift != 0){
                $adajamkerja = true;
            }
            if(!$jamkerja){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah jam kerja');
            return view('datainduk/absensi/jamkerja/edit', ['jamkerja' => $jamkerja, 'datakategori' => $dataKategori, 'adajamkerja' => $adajamkerja, 'menu' => 'jamkerja']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'jamkerja','nama','id',$id);
        if($cekadadata != ''){
            $sql = 'SELECT id FROM jamkerja WHERE nama=:nama AND id<>:idjamkerja LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':idjamkerja', $id);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $jamkerja = JamKerja::find($id);
                $jamkerja->nama = $request->nama;
                $jamkerja->idkategori = ($request->kategori == '' ? NULL : $request->kategori);
                $jamkerja->jenis = $request->jenis;
                $jamkerja->toleransi = $request->toleransi;
                $jamkerja->acuanterlambat = $request->acuanterlambat;
                $jamkerja->hitunglemburstlh = $request->hitunglemburstlh;
                $jamkerja->digunakan = $request->digunakan;
                $jamkerja->save();

                Utils::insertLogUser('Ubah jam kerja "'.$cekadadata.'" => "'.$request->nama.'"');
    
                return redirect('datainduk/absensi/jamkerja')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/absensi/jamkerja/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/absensi/jamkerja/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('jamkerja','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'jamkerja','nama','id',$id);
            if($cekadadata != ''){
                //cek apakah sudah digunakan
                $sql = 'SELECT id FROM jadwalshift where idjamkerjashift IN (SELECT id FROM jamkerjashift WHERE idjamkerja=:id) LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                if($stmt->rowCount() == 0) {
                    JamKerja::find($id)->delete();
                    Utils::insertLogUser('Hapus jam kerja "' . $cekadadata . '"');
                    return redirect('datainduk/absensi/jamkerja')->with('message', trans('all.databerhasildihapus'));
                }else{
                    return redirect('datainduk/absensi/jamkerja')->with('message_error', trans('all.datasudahadarelasidenganjadwalshift'));
                }
            }else{
                return redirect('datainduk/absensi/jamkerja')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.jamkerja'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.kategori'))
                        ->setCellValue('C1', trans('all.jenis'))
                        ->setCellValue('D1', trans('all.toleransi'))
                        ->setCellValue('E1', trans('all.acuanterlambat'))
                        ->setCellValue('F1', trans('all.hitunglembursetelah'))
                        ->setCellValue('G1', trans('all.digunakan'));

            $sql = 'SELECT
                        jk.id,
                        jk.nama,
                        jkk.nama as kategori,
                        jk.toleransi,
                        jk.acuanterlambat,
                        jk.hitunglemburstlh,
                        IF(jk.jenis="full","' . trans("all.full") . '","' . trans("all.shift") . '") as jenis,
                        IF(jk.digunakan="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as digunakan
                    FROM
                        jamkerja jk LEFT JOIN jamkerjakategori jkk ON jk.idkategori=jkk.id
                    ORDER BY
                        nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['kategori']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['jenis']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['toleransi']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, trans('all.'.$row['acuanterlambat']));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['hitunglemburstlh']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['digunakan']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor jam kerja');
            $arrWidth = array(40, 20, 20, 10, 20, 15, 15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.jamkerja'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}