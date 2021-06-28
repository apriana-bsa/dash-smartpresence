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

class IndexJamKerjaController extends Controller
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
        if(Utils::cekHakakses('mesin','l')){
            Utils::insertLogUser('akses menu index jamkerja');
	        return view('datainduk/absensi/indexjamkerja/index', ['menu' => 'indexlemburdanjamkerja']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('mesin','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('mesin','uhm')){
                $columns = array('','nama','jenishari','berlakumulai','index');
            }else{
                $columns = array('nama','jenishari','berlakumulai','index');
            }
            $table = 'indexjamkerja';
            $totalData = Utils::getDataCustomWhere($pdo,$table, 'count(id)');
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

            $sql = 'SELECT id,nama,jenishari,berlakumulai,`index` FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    if(Utils::cekHakakses('mesin','um')){
                        $action .= Utils::tombolManipulasi('ubah','indexjamkerja',$key['id']);
                    }
                    if(Utils::cekHakakses('mesin','hm')){
                        $action .= Utils::tombolManipulasi('hapus','indexjamkerja',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'jenishari') {
                            $tempdata[$columns[$i]] = trans('all.' . $key[$columns[$i]]);
                        }elseif($columns[$i] == 'index'){
                            $index = $key[$columns[$i]];
                            $hasil = '';
                            if($index != ''){
                                $index_ex = explode(';', $index);
                                for($j = 0;$j<count($index_ex);$j++){
                                    if($index_ex[$j] != ''){
                                        $index_ex2 = explode('=', $index_ex[$j]);
                                        $hasil .= trans('all.lebihdari').' '.$index_ex2[0].' '.trans('all.menit').', '.trans('all.indexjamkerjaadalah').' '.$index_ex2[1].'<br>';
                                    }
                                }
                            }
                            $tempdata[$columns[$i]] = $hasil;
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
        if(Utils::cekHakakses('mesin','tm')){
            Utils::insertLogUser('akses menu tambah index jamkerja');
            $dataatribut = Utils::getAtributdanAtributNilaiCrud(0, 'indexjamkerja', false);
            return view('datainduk/absensi/indexjamkerja/create', ['dataatribut' => $dataatribut, 'menu' => 'indexlemburdanjamkerja']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM indexjamkerja WHERE nama = :nama';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nama', $request->nama);
        $stmt->execute();
        if($stmt->rowCount() == 0){
            $index = '';
            $jumlahmenit = $request->jumlahmenit;
            $pengali = $request->pengali;
            if(count($jumlahmenit) > 0 && count($pengali) > 0 && count($jumlahmenit) == count($pengali)){
                for($i = 0;$i<count($jumlahmenit);$i++){
                    $index .= $jumlahmenit[$i].'='.$pengali[$i].';';
                }
            }
            $index = $index != '' ? substr($index, 0, -1) : '';
            $sql = 'INSERT INTO indexjamkerja VALUES(NULL,:nama,:jenishari,STR_TO_DATE(:berlakumulai,"%d/%m/%Y"),:index)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':jenishari', $request->jenishari);
            $stmt->bindValue(':berlakumulai', $request->berlakumulai);
            $stmt->bindValue(':index', $index);
            $stmt->execute();

            $idindexjamkerja = $pdo->lastInsertId();

            // insert into indexjamkerja_atribut
            if ($request->atribut != '') {
                for ($i = 0; $i < count($request->atribut); $i++) {
                    $sql = 'INSERT INTO indexjamkerja_atribut VALUES(NULL, :idindexjamkerja, :idatributnilai)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idindexjamkerja', $idindexjamkerja);
                    $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                    $stmt->execute();
                }
            }

            Utils::insertLogUser('Tambah index jamkerja "'.$request->nama.'"');
    
            return redirect('datainduk/absensi/indexjamkerja')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/absensi/indexjamkerja/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('mesin','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,nama,jenishari,DATE_FORMAT(berlakumulai,"%d/%m/%Y") as berlakumulai,`index` FROM indexjamkerja WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $indexjamkerja = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$indexjamkerja){
                abort(404);
            }
            $index = $indexjamkerja->index;
            $dataindex = array();
            if($index != ''){
                $index_ex = explode(';', $index);
                for($i = 0;$i<count($index_ex);$i++){
                    if($index_ex[$i] != ''){
                        $index_ex2 = explode('=', $index_ex[$i]);
                        $dataindex[$i]['jumlahmenit'] = $index_ex2[0];
                        $dataindex[$i]['pengali'] = $index_ex2[1];
                    }
                }
            }
            $dataatribut = Utils::getAtributdanAtributNilaiCrud($id, 'indexjamkerja', false);
            Utils::insertLogUser('akses menu ubah index jamkerja');
            return view('datainduk/absensi/indexjamkerja/edit', ['indexjamkerja' => $indexjamkerja, 'dataatribut' => $dataatribut, 'dataindex' => $dataindex, 'menu' => 'indexlemburdanjamkerja']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'indexjamkerja','nama','id',$id);
        if($cekadadata != ''){
            //cek apakah kembar?
            $cekkembar = Utils::getData($pdo,'indexjamkerja','id','nama = "'.$request->nama.'" AND id <> '.$id.' LIMIT 1');
            if($cekkembar == ''){
                $index = '';
                $jumlahmenit = $request->jumlahmenit;
                $pengali = $request->pengali;
                if(count($jumlahmenit) > 0 && count($pengali) > 0 && count($jumlahmenit) == count($pengali)){
                    for($i = 0;$i<count($jumlahmenit);$i++){
                        $index .= $jumlahmenit[$i].'='.$pengali[$i].';';
                    }
                }
                $index = $index != '' ? substr($index, 0, -1) : '';
                $sql = 'UPDATE indexjamkerja SET nama = :nama, jenishari = :jenishari, berlakumulai = STR_TO_DATE(:berlakumulai,"%d/%m/%Y"), `index` = :index WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':jenishari', $request->jenishari);
                $stmt->bindValue(':berlakumulai', $request->berlakumulai);
                $stmt->bindValue(':index', $index);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                // delete pegawai atribut
                Utils::deleteData($pdo,'indexjamkerja_atribut',$id,'idindexjamkerja');

                // insert into indexjamkerja_atribut
                if ($request->atribut != '') {
                    for ($i = 0; $i < count($request->atribut); $i++) {
                        $sql = 'INSERT INTO indexjamkerja_atribut VALUES(NULL, :idindexjamkerja, :idatributnilai)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idindexjamkerja', $id);
                        $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                        $stmt->execute();
                    }
                }

                Utils::insertLogUser('Ubah index jamkerja "'.$cekadadata.'" => "'.$request->nama.'"');
    
                return redirect('datainduk/absensi/indexjamkerja')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/absensi/indexjamkerja/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/absensi/indexjamkerja/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id){
        if(Utils::cekHakakses('mesin','hm')){
            //pastikan idindexjamkerja ada
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'indexjamkerja','nama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'indexjamkerja',$id);
                Utils::insertLogUser('Hapus index jamkerja "'.$cekadadata.'"');
                return redirect('datainduk/absensi/indexjamkerja')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/absensi/indexjamkerja')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('mesin','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.indexjamkerja'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.jenishari'))
                        ->setCellValue('C1', trans('all.berlakumulai'))
                        ->setCellValue('D1', trans('all.index'));

            $sql = 'SELECT
                        nama,
                        jenishari,
                        (DATEDIFF(berlakumulai,"1900-01-01")+2) as berlakumulai,
                        `index`
                    FROM
                        indexjamkerja
                    ORDER BY
                        jenishari';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $index = $row['index'];
                $hasil = '';
                if($index != ''){
                    $index_ex = explode(';', $index);
                    for($j = 0;$j<count($index_ex);$j++){
                        if($index_ex[$j] != ''){
                            $index_ex2 = explode('=', $index_ex[$j]);
                            $hasil .= trans('all.lebihdari').' '.$index_ex2[0].' '.trans('all.menit').', '.trans('all.indexjamkerjaadalah').' '.$index_ex2[1].', ';
                        }
                    }
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, trans('all.'.$row['jenishari']));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['berlakumulai']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $hasil);

                $objPHPExcel->getActiveSheet()->getStyle('C' . $i)->getNumberFormat()->setFormatCode('DD/MM/YYYY');

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor index jamkerja');
            $arrWidth = array(25, 20, 20, 100);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.indexjamkerja'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}