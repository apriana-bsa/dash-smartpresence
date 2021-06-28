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

class AktivitasKategoriController extends Controller
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
            Utils::insertLogUser('akses menu aktivitas kategori');
	        return view('datainduk/pegawai/aktivitaskategori/index', ['menu' => 'aktivitas']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('pegawai','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('pegawai','uhm')){
                $columns = array('', 'nama', 'atributnilai', 'digunakan');
            }else{
                $columns = array('nama', 'atributnilai', 'digunakan');
            }
            $table = '(
                        SELECT
                            ak.id,
                            ak.nama,
                            ak.digunakan,
                            GROUP_CONCAT(an.nilai ORDER BY an.nilai SEPARATOR " ,") as atributnilai
                        FROM
                            aktivitas_kategori ak
                            LEFT JOIN aktivitas_kategori_atribut aka ON aka.idaktivitaskategori=ak.id
                            LEFT JOIN atributnilai an ON aka.idatributnilai=an.id
                        GROUP BY
                            ak.id
                    ) x';
            $totalData = Utils::getDataCustomWhere($pdo,'aktivitas_kategori', 'count(id)');
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
                        $action .= Utils::tombolManipulasi('ubah','aktivitaskategori',$key['id']);
                    }
                    if(Utils::cekHakakses('pegawai','hm')){
                        $action .= Utils::tombolManipulasi('hapus','aktivitaskategori',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }else if($columns[$i] == 'digunakan') {
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

	public function create()
    {
        if(Utils::cekHakakses('pegawai','tm')){
            Utils::insertLogUser('akses menu tambah aktivitas kategori');
            $arrAtribut = Utils::getAtributdanAtributNilaiCrud(0, 'aktivitaskategori', false);
            return view('datainduk/pegawai/aktivitaskategori/create', ['arratribut' => $arrAtribut, 'menu' => 'aktivitas']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'aktivitas_kategori','id','nama',$request->nama);
        if($cekadadata == ''){
            $sql = 'INSERT INTO aktivitas_kategori VALUES(NULL,:nama,:digunakan,NOW(),NULL)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':digunakan', $request->digunakan);
            $stmt->execute();

            $idaktivitaskategori = $pdo->lastInsertId();

            // insert into aktivitas_kategori_atribut
            if ($request->atribut != '') {
                for ($i = 0; $i < count($request->atribut); $i++) {
                    $sql = 'INSERT INTO aktivitas_kategori_atribut VALUES(NULL, :idaktivitaskategori, :idatributnilai)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idaktivitaskategori', $idaktivitaskategori);
                    $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                    $stmt->execute();
                }
            }

            Utils::insertLogUser('Tambah aktivitas kategori "'.$request->nama.'"');
    
            return redirect('datainduk/pegawai/aktivitaskategori')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('datainduk/pegawai/aktivitaskategori/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('pegawai','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM aktivitas_kategori WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            //atribut dan atribut nilai
            $arrAtribut = Utils::getAtributdanAtributNilaiCrud($id, 'aktivitaskategori', false);

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah aktivitas kategori');
            return view('datainduk/pegawai/aktivitaskategori/edit', ['data' => $data, 'arratribut' => $arrAtribut, 'menu' => 'aktivitas']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'aktivitas_kategori','nama','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'aktivitas_kategori','id','nama = "'.$request->nama.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                $sql = 'UPDATE aktivitas_kategori SET nama = :nama, digunakan = :digunakan WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':digunakan', $request->digunakan);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::deleteData($pdo,'aktivitas_kategori_atribut',$id,'idaktivitaskategori');

                // insert into aktivitas_kategori_atribut
                if ($request->atribut != '') {
                    for ($i = 0; $i < count($request->atribut); $i++) {
                        $sql = 'INSERT INTO aktivitas_kategori_atribut VALUES(NULL, :idaktivitaskategori, :idatributnilai)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idaktivitaskategori', $id);
                        $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                        $stmt->execute();
                    }
                }

                Utils::insertLogUser('Ubah aktivitas kategori "'.$cekadadata.'" => "'.$request->nama.'"');
    
                return redirect('datainduk/pegawai/aktivitaskategori')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('datainduk/pegawai/aktivitaskategori/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/pegawai/aktivitaskategori/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('pegawai','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'aktivitas_kategori','nama','id',$id);
            if($cekadadata != ''){
                $cekaktivitas = Utils::getDataWhere($pdo,'aktivitas','id','idaktivitaskategori',$id);
                if($cekaktivitas == '') {
                    Utils::deleteData($pdo, 'aktivitas_kategori', $id);
                    Utils::insertLogUser('Hapus aktivitas kategori "' . $cekadadata . '"');
                    return redirect('datainduk/pegawai/aktivitaskategori')->with('message', trans('all.databerhasildihapus'));
                }else{
                    return redirect('datainduk/pegawai/aktivitaskategori')->with('message', trans('all.datasudahdigunakan'));
                }
            }else{
                return redirect('datainduk/pegawai/aktivitaskategori')->with('message', trans('all.datatidakditemukan'));
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
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.atributnilai'))
                        ->setCellValue('C1', trans('all.digunakan'));

            $sql = 'SELECT
                        ak.id,
                        ak.nama,
                        ak.digunakan,
                        GROUP_CONCAT(an.nilai ORDER BY an.nilai SEPARATOR " ,") as atributnilai
                    FROM
                        aktivitas_kategori ak
                        LEFT JOIN aktivitas_kategori_atribut aka ON aka.idaktivitaskategori=ak.id
                        LEFT JOIN atributnilai an ON aka.idatributnilai=an.id
                    GROUP BY
                        ak.id
                    ORDER BY ak.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['atributnilai']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['digunakan'] = 'y' ? trans('all.ya') : trans('all.tidak'));

                $objPHPExcel->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $i++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor aktivitas kategori');
            $arrWidth = array(35, 50, 15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.aktivitaskategori'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}