<?php
namespace App\Http\Controllers;

use App\JamKerjaShiftDetail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class JamKerjaShiftDetailController extends Controller
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

	public function getindex($id, $idjamkerjashift)
	{
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT namashift FROM jamkerjashift WHERE id = :idjamkerjashift';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjashift', $idjamkerjashift);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $jamkerjashift = $row->namashift;
            Utils::insertLogUser('akses menu jam kerja shift detail');
            return view('datainduk/absensi/jamkerja/shift/detail/index', ['idjamkerja' => $id, 'idjamkerjashift' => $idjamkerjashift, 'jamkerjashift' => $jamkerjashift, 'menu' => 'jamkerja']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request, $id, $idjamkerjashift)
	{
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = ' AND idjamkerjashift = '.$idjamkerjashift;
            if(Utils::cekHakakses('jamkerja','uhm')) {
                $columns = array('', 'berlakumulai', 'waktukerja', 'waktuistirahat');
            }else{
                $columns = array('berlakumulai', 'waktukerja', 'waktuistirahat');
            }
            $table = '(
                        SELECT
                            id,
                            idjamkerjashift,
                            DATE_FORMAT(berlakumulai,"%d/%m/%Y") as berlakumulai,
                            CONCAT(TIME_FORMAT(jammasuk,"%H:%i")," - ",TIME_FORMAT(jampulang,"%H:%i")) as waktukerja,
                            CONCAT(TIME_FORMAT(jamistirahatmulai,"%H:%i")," - ",TIME_FORMAT(jamistirahatselesai,"%H:%i")) as waktuistirahat
                        FROM
                            jamkerjashiftdetail
                    ) x';
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idjamkerjashift = :idjamkerjashift '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjashift', $idjamkerjashift);
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
                $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE idjamkerjashift = :idjamkerjashift '.$where;
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idjamkerjashift', $idjamkerjashift);
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                    }
                }
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalFiltered = $row['total'];
            }

            $sql = 'SELECT * FROM '.$table.' WHERE idjamkerjashift = :idjamkerjashift ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjashift', $idjamkerjashift);
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
                    if(Utils::cekHakakses('jamkerja','um')){
                        $action .= Utils::tombolManipulasi('ubah','detail',$key['id']);
                    }
                    if(Utils::cekHakakses('jamkerja','hm')){
                        $action .= Utils::tombolManipulasi('hapus','detail',$key['id']);
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

	public function create($id, $idjamkerjashift)
    {
        if(Utils::cekHakakses('jamkerja','tm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT namashift FROM jamkerjashift WHERE id = :idjamkerjashift';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjashift', $idjamkerjashift);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $jamkerjashift = $row->namashift;
            Utils::insertLogUser('akses menu tambah jam kerja shift detail');
            return view('datainduk/absensi/jamkerja/shift/detail/create', ['idjamkerja' => $id, 'idjamkerjashift' => $idjamkerjashift, 'jamkerjashift' => $jamkerjashift, 'menu' => 'jamkerja']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request, $id, $idjamkerjashift)
    {
        if(!Utils::cekDateTime($request->berlakumulai) && !Utils::cekDateTime($request->jammasuk) && !Utils::cekDateTime($request->jampulang)){
            return redirect('datainduk/absensi/jamkerja/'.$id.'/shift/'.$idjamkerjashift.'/detail/create')->with('message', trans('all.terjadigangguan'));
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idjamkerjashift ada
        $sql = 'SELECT id,namashift FROM jamkerjashift WHERE id=:idjamkerjashift LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('idjamkerjashift', $idjamkerjashift);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //cek apakah berlakumulai kembar?
            $sql = 'SELECT id FROM jamkerjashiftdetail WHERE idjamkerjashift=:idjamkerjashift AND berlakumulai=STR_TO_DATE(:berlakumulai,"%d/%m/%Y") LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjashift', $idjamkerjashift);
            $stmt->bindValue(':berlakumulai', $request->berlakumulai);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $sql = 'INSERT INTO jamkerjashiftdetail VALUES(0,:idjamkerjashift,STR_TO_DATE(:berlakumulai,"%d/%m/%Y"),:jammasuk,:jampulang,:jamistirahatmulai,:jamistirahatselesai,NOW())';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idjamkerjashift', $idjamkerjashift);
                $stmt->bindValue(':berlakumulai', $request->berlakumulai);
                $stmt->bindValue(':jammasuk', $request->jammasuk);
                $stmt->bindValue(':jampulang', $request->jampulang);
                $stmt->bindValue(':jamistirahatmulai', $request->jamistirahatmulai);
                $stmt->bindValue(':jamistirahatselesai', $request->jamistirahatselesai);
                $stmt->execute();

                Utils::insertLogUser('Tambah jam kerja shift detail "'.$row['namashift'].'"');
        
                return redirect('datainduk/absensi/jamkerja/'.$id.'/shift/'.$idjamkerjashift.'/detail')->with('message', trans('all.databerhasildisimpan'));
            }else{
                return redirect('datainduk/absensi/jamkerja/'.$id.'/shift/'.$idjamkerjashift.'/detail/create')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/absensi/jamkerja/'.$id.'/shift/'.$idjamkerjashift.'/detail/create')->with('message', trans('all.datatidakditemukan'));
        }
    }
    
    public function edit($idjamkerja, $idjamkerjashift, $id)
    {
        if(Utils::cekHakakses('jamkerja','um')){
            $shiftdetail = JamKerjaShiftDetail::find($id);

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT namashift FROM jamkerjashift WHERE id = :idjamkerjashift';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjashift', $idjamkerjashift);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $jamkerjashift = $row->namashift;

            if(!$shiftdetail){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah jam kerja shift detail');
            return view('datainduk/absensi/jamkerja/shift/detail/edit', ['idjamkerja' => $id, 'idjamkerjashift' => $idjamkerjashift, 'jamkerjashift' => $jamkerjashift, 'shiftdetail' => $shiftdetail, 'menu' => 'jamkerja']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $idjamkerja, $idjamkerjashift, $id)
    {
        if(!Utils::cekDateTime($request->berlakumulai) && !Utils::cekDateTime($request->jammasuk) && !Utils::cekDateTime($request->jampulang)){
            return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/shift/' . $idjamkerjashift . '/detail/'.$id.'/edit')->with('message', trans('all.terjadigangguan'));
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idjamkerjashiftdetail ada
        $sql = 'SELECT jd.id,j.namashift FROM jamkerjashiftdetail jd, jamkerjashift j WHERE jd.idjamkerjashift=j.id AND jd.id=:idjamkerjashiftdetail LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idjamkerjashiftdetail', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = 'UPDATE jamkerjashiftdetail SET idjamkerjashift = :idjamkerjashift, berlakumulai = STR_TO_DATE(:berlakumulai,"%d/%m/%Y"), jammasuk = :jammasuk, jampulang = :jampulang, jamistirahatmulai = :jamistirahatmulai, jamistirahatselesai = :jamistirahatselesai WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjashift', $idjamkerjashift);
            $stmt->bindValue(':berlakumulai', $request->berlakumulai);
            $stmt->bindValue(':jammasuk', $request->jammasuk);
            $stmt->bindValue(':jampulang', $request->jampulang);
            $stmt->bindValue(':jamistirahatmulai', $request->jamistirahatmulai);
            $stmt->bindValue(':jamistirahatselesai', $request->jamistirahatselesai);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

            Utils::insertLogUser('Ubah jam kerja shift detail "'.$row['namashift'].'"');
    
            return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/shift/' . $idjamkerjashift . '/detail')->with('message', trans('all.databerhasildiubah'));
        }else{
            return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/shift/' . $idjamkerjashift . '/detail/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }

    }

    public function destroy($idjamkerja, $idjamkerjashift, $id)
    {
        if(Utils::cekHakakses('jamkerja','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan idjamkerjashiftdetail ada
            $sql = 'SELECT jd.id,j.namashift FROM jamkerjashiftdetail jd, jamkerjashift j WHERE jd.idjamkerjashift=j.id AND jd.id=:idjamkerjashiftdetail LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idjamkerjashiftdetail', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                JamKerjaShiftDetail::find($id)->delete();
                Utils::insertLogUser('Hapus jam kerja shift detail "'.$row['namashift'].'"');
    
                $msg = trans('all.databerhasildihapus');
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('datainduk/absensi/jamkerja/' . $idjamkerja . '/shift/' . $idjamkerjashift . '/detail')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function excel($id)
    {
        if(Utils::cekHakakses('jamkerja','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.shift').' '.trans('all.detail'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.berlakumulai'))
                        ->setCellValue('B1', trans('all.waktukerja'))
                        ->setCellValue('C1', trans('all.waktuistirahat'));

            $where = 'idjamkerjashift =' . $id;
            $sql = 'SELECT
                        (DATEDIFF(berlakumulai,"1900-01-01")+2) as berlakumulai,
                        CONCAT(TIME_FORMAT(jammasuk,"%H:%i")," - ",TIME_FORMAT(jampulang,"%H:%i")) as waktukerja,
                        CONCAT(TIME_FORMAT(jamistirahatmulai,"%H:%i")," - ",TIME_FORMAT(jamistirahatselesai,"%H:%i")) as waktuistirahat
                    FROM
                        jamkerjashiftdetail
                    WHERE
                        ' . $where . '
                    ORDER BY
                        berlakumulai DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['berlakumulai']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['waktukerja']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['waktuistirahat']);

                // format
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getNumberFormat()->setFormatCode('DD/MM/YYYY');

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor jam kerja shift detail');
            $arrWidth = array(17, 19, 19);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.shift') . '_' . trans('all.detail'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}