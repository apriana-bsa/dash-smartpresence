<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use App\Utils;

class AktivitasController extends Controller
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
	    if(Utils::cekHakakses('pegawai','l')){
            Utils::insertLogUser('akses menu aktivitas');
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $dataaktivitaskategori = Utils::getData($pdo,'aktivitas_kategori','id,nama','digunakan="y"',"nama");
            $aktivitaskategori = '';
            if(Session::has('aktivitas_idaktivitaskategori')){
                $aktivitaskategori = Session::get('aktivitas_idaktivitaskategori');;
            }
            return view('datainduk/pegawai/aktivitas/index', ['dataaktivitaskategori' => $dataaktivitaskategori, 'aktivitaskategori' => $aktivitaskategori, 'menu' => 'aktivitas']);
        }else{
            return redirect('/');
        }
	}

	public function submitFilter(Request $request){
	    if(isset($request->aktivitaskategori) AND $request->aktivitaskategori != ''){
	        Session::set('aktivitas_idaktivitaskategori',$request->aktivitaskategori);
        }else{
	        if(Session::has('aktivitas_idaktivitaskategori')){
	            Session::forget('aktivitas_idaktivitaskategori');
            }
        }
        return redirect('datainduk/pegawai/aktivitas');
    }

	public function show(Request $request)
	{
        if(Utils::cekHakakses('pegawai','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Session::has('aktivitas_idaktivitaskategori')){
                $where .= ' AND idaktivitaskategori = '.Session::get('aktivitas_idaktivitaskategori');
            }
            if(Utils::cekHakakses('pegawai','uhm')){
                $columns = array('', 'urutan', 'pertanyaan', 'keterangan', 'harusdiisi', 'digunakan');
            }else{
                $columns = array('urutan', 'pertanyaan', 'keterangan', 'harusdiisi', 'digunakan');
            }
            $table = '(
                        SELECT
                            a.*,
                            CONCAT(a.jenisinputan," ",a.panjangkarakter," ",a.rentangnilaidari," ",a.rentangnilaisampai," ",IFNULL(GROUP_CONCAT(am.keterangan ORDER BY am.keterangan SEPARATOR " "),"")) as keterangan,
                            IFNULL(GROUP_CONCAT(am.keterangan ORDER BY am.keterangan SEPARATOR ", "),"") as keteranganmultiple
                        FROM
                            aktivitas a
                            LEFT JOIN aktivitas_multiple am ON am.idaktivitas=a.id
                        GROUP BY
                            a.id
                    ) x';
            $totalData = Utils::getDataCustomWhere($pdo,'aktivitas', 'count(id)');
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

            $sql = 'SELECT * FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    if(Utils::cekHakakses('pegawai','um')){
                        $action .= Utils::tombolManipulasi('ubah','aktivitas',$key['id']);
                    }
                    if(Utils::cekHakakses('pegawai','hm')){
                        $action .= Utils::tombolManipulasi('hapus','aktivitas',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }else if($columns[$i] == 'digunakan' || $columns[$i] == 'harusdiisi') {
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]]);
                        }else if($columns[$i] == 'keterangan') {
                            $jenisinputan = $key['jenisinputan'] == 'checkbox' ? 'ceklist' : ($key['jenisinputan'] == 'radiobutton' ? 'opsional' : ($key['jenisinputan'] == 'combobox' ? 'listpilihan' : $key['jenisinputan']));
                            $keterangan = trans('all.jenisinputan').": ".trans('all.'.$jenisinputan).'<br>';
                            if($key['jenisinputan'] == 'karakter' || $key['jenisinputan'] == 'karakterpanjang'){
                                $keterangan .= trans('all.panjangkarakter').": ".$key['panjangkarakter'].'<br>';
                            }
                            if($key['jenisinputan'] == 'angka' || $key['jenisinputan'] == 'desimal'){
                                $rentangnilai = '';
                                if($key['rentangnilaidari'] != '' || $key['rentangnilaisampai'] != ''){
                                    $rentangnilai = $key['rentangnilaidari'].' - '.$key['rentangnilaisampai'];
                                }
                                $keterangan .= trans('all.rentangnilai').": ".$rentangnilai.'<br>';
                            }
                            if($key['jenisinputan'] == 'checkbox' || $key['jenisinputan'] == 'radiobutton' || $key['jenisinputan'] == 'combobox'){
                                $keterangan .= trans('all.pilihan').": ".$key['keteranganmultiple'].'<br>';
                            }
                            $tempdata[$columns[$i]] = $keterangan;
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
        if(Utils::cekHakakses('pegawai','tm') || Session::has('aktivitas_idaktivitaskategori')){
            Utils::insertLogUser('akses menu tambah aktivitas kategori');
            $arrAtribut = Utils::getAtributdanAtributNilaiCrud(0, 'aktivitaskategori', false);
            return view('datainduk/pegawai/aktivitas/create', ['arratribut' => $arrAtribut, 'menu' => 'aktivitas']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idaktivitaskategori = Session::get('aktivitas_idaktivitaskategori');
        $cekadadata = Utils::getDataCustomWhere($pdo,'aktivitas','id','idaktivitaskategori = '.$idaktivitaskategori.' AND pertanyaan = "'.$request->pertanyaan.'"');
        if($cekadadata == ''){
            $sql = 'INSERT INTO aktivitas VALUES(NULL,:idaktivitaskategori,:pertanyaan,:jenisinputan,:panjangkarakter,:rentangnilaidari,:rentangnilaisampai,0,:harusdiisi,:digunakan,NOW(),NULL)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idaktivitaskategori', $idaktivitaskategori);
            $stmt->bindValue(':pertanyaan', $request->pertanyaan);
            $stmt->bindValue(':jenisinputan', $request->jenisinputan);
            $stmt->bindValue(':panjangkarakter', $request->panjangkarakter);
            $stmt->bindValue(':rentangnilaidari', $request->rentangnilaidari);
            $stmt->bindValue(':rentangnilaisampai', $request->rentangnilaisampai);
            $stmt->bindValue(':harusdiisi', $request->harusdiisi);
            $stmt->bindValue(':digunakan', $request->digunakan);
            $stmt->execute();

            $idaktivitas = $pdo->lastInsertId();

            if($request->jenisinputan == 'checkbox' || $request->jenisinputan == 'radiobutton' || $request->jenisinputan == 'combobox') {
                // insert into aktivitas_multiple
                for ($i = 0; $i < count($request->multiple); $i++) {
                    if($request->multiple[$i] != "") {
                        $sql = 'INSERT INTO aktivitas_multiple VALUES(NULL, :idaktivitas, :keterangan)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idaktivitas', $idaktivitas);
                        $stmt->bindValue(':keterangan', $request->multiple[$i]);
                        $stmt->execute();
                    }
                }
            }
            Utils::insertLogUser('Tambah aktivitas "'.$request->pertanyaan.'"');
            return redirect('datainduk/pegawai/aktivitas')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/pegawai/aktivitas/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('pegawai','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM aktivitas WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            $dataaktivitasmultiple = Utils::getData($pdo,'aktivitas_multiple','keterangan','idaktivitas='.$id,'keterangan');

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah aktivitas kategori');
            return view('datainduk/pegawai/aktivitas/edit', ['data' => $data, 'dataaktivitasmultiple' => $dataaktivitasmultiple, 'menu' => 'aktivitas']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idaktivitaskategori = Session::get('aktivitas_idaktivitaskategori');
        $cekadadata = Utils::getDataWhere($pdo,'aktivitas','pertanyaan','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'aktivitas','id','idaktivitaskategori = '.$idaktivitaskategori.' AND pertanyaan = "'.$request->pertanyaan.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $sql = 'UPDATE aktivitas SET pertanyaan = :pertanyaan, jenisinputan = :jenisinputan, panjangkarakter = :panjangkarakter, rentangnilaidari = :rentangnilaidari, rentangnilaisampai = :rentangnilaisampai, harusdiisi = :harusdiisi, digunakan = :digunakan WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':pertanyaan', $request->pertanyaan);
                $stmt->bindValue(':jenisinputan', $request->jenisinputan);
                $stmt->bindValue(':panjangkarakter', $request->panjangkarakter);
                $stmt->bindValue(':rentangnilaidari', $request->rentangnilaidari);
                $stmt->bindValue(':rentangnilaisampai', $request->rentangnilaisampai);
                $stmt->bindValue(':harusdiisi', $request->harusdiisi);
                $stmt->bindValue(':digunakan', $request->digunakan);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::deleteData($pdo,'aktivitas_multiple',$id,'idaktivitas');

                if($request->jenisinputan == 'checkbox' || $request->jenisinputan == 'radiobutton' || $request->jenisinputan == 'combobox') {
                    // insert into aktivitas_multiple
                    for ($i = 0; $i < count($request->multiple); $i++) {
                        $sql = 'INSERT INTO aktivitas_multiple VALUES(NULL, :idaktivitas, :keterangan)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idaktivitas', $id);
                        $stmt->bindValue(':keterangan', $request->multiple[$i]);
                        $stmt->execute();
                    }
                }

                Utils::insertLogUser('Ubah aktivitas "'.$cekadadata.'" => "'.$request->pertanyaan.'"');
    
                return redirect('datainduk/pegawai/aktivitas')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/pegawai/aktivitas/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/pegawai/aktivitas/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('pegawai','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'aktivitas','pertanyaan','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'aktivitas',$id);
                Utils::insertLogUser('Hapus aktivitas "'.$cekadadata.'"');
                return redirect('datainduk/pegawai/aktivitas')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/pegawai/aktivitas')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('pegawai','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.aktivitaskategori'));
            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.urutan'))
                        ->setCellValue('B1', trans('all.pertanyaan'))
                        ->setCellValue('C1', trans('all.keterangan'))
                        ->setCellValue('D1', trans('all.harusdiisi'))
                        ->setCellValue('E1', trans('all.digunakan'));

            $where = '';
            if(Session::has('aktivitas_idaktivitaskategori')){
                $where .= ' AND a.idaktivitaskategori = '.Session::get('aktivitas_idaktivitaskategori');
            }
            $sql = 'SELECT
                        a.*,
                        CONCAT(a.jenisinputan," ",a.panjangkarakter," ",a.rentangnilaidari," ",a.rentangnilaisampai," ",GROUP_CONCAT(am.keterangan ORDER BY am.keterangan SEPARATOR " ")) as keterangan,
                        IFNULL(GROUP_CONCAT(am.keterangan ORDER BY am.keterangan SEPARATOR ", "),"") as keteranganmultiple
                    FROM
                        aktivitas a
                        LEFT JOIN aktivitas_multiple am ON am.idaktivitas=a.id
                    WHERE
                        1=1
                        '.$where.'
                    GROUP BY
                        a.id
                    ORDER BY
                        a.urutan ASC, a.pertanyaan ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $jenisinputan = $row['jenisinputan'] == 'checkbox' ? 'ceklist' : ($row['jenisinputan'] == 'radiobutton' ? 'opsional' : ($row['jenisinputan'] == 'combobox' ? 'listpilihan' : $row['jenisinputan']));
                $keterangan = trans('all.jenisinputan').": ".trans('all.'.$jenisinputan).' ';
                if($row['jenisinputan'] == 'karakter' || $row['jenisinputan'] == 'karakterpanjang'){
                    $keterangan .= trans('all.panjangkarakter').": ".$row['panjangkarakter'].' ';
                }
                if($row['jenisinputan'] == 'angka' || $row['jenisinputan'] == 'desimal'){
                    $rentangnilai = '';
                    if($row['rentangnilaidari'] != '' || $row['rentangnilaisampai'] != ''){
                        $rentangnilai = $row['rentangnilaidari'].' - '.$row['rentangnilaisampai'];
                    }
                    $keterangan .= trans('all.rentangnilai').": ".$rentangnilai.' ';
                }
                if($row['jenisinputan'] == 'checkbox' || $row['jenisinputan'] == 'radiobutton' || $row['jenisinputan'] == 'combobox'){
                    $keterangan .= trans('all.pilihan').": ".$row['keteranganmultiple'].' ';
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['urutan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['pertanyaan']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $keterangan);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['harusdiisi'] = 'y' ? trans('all.ya') : trans('all.tidak'));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['digunakan'] = 'y' ? trans('all.ya') : trans('all.tidak'));

                $objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $i++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor aktivitas');
            $arrWidth = array(10, 50, 100, 15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.aktivitas'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}