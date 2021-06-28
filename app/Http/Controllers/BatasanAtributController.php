<?php
namespace App\Http\Controllers;

use App\Atribut;
use App\AtributNilai;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class BatasanAtributController extends Controller
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
        if(Utils::cekHakakses('batasan','l')){
            Utils::insertLogUser('akses menu batasan atribut');
            return view('datainduk/lainlain/batasan/atribut/index', ['menu' => 'batasan']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('batasan','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('batasan','uhm')) {
                $columns = array('', 'namabatasan', 'atributnilai');
            }else{
                $columns = array('namabatasan', 'atributnilai');
            }
            $table = '(
                        SELECT
                            b.id,
                            b.namabatasan,
                            GROUP_CONCAT(a.nilai SEPARATOR ", ") as atributnilai
                        FROM
                            batasan b
                            LEFT JOIN batasanatribut ba ON ba.idbatasan=b.id
                            LEFT JOIN atributnilai a ON ba.idatributnilai=a.id
                        GROUP BY
                            b.id
                      ) x';
            $totalData = Utils::getDataCustomWhere($pdo,$table, 'count(id)',' id in(select idbatasan from batasanatribut)');
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
                    if(Utils::cekHakakses('batasan','um')){
                        $action .= Utils::tombolManipulasi('ubah','batasanatribut',$key['id']);
                    }
                    if(Utils::cekHakakses('batasan','hm')){
                        $action .= Utils::tombolManipulasi('hapus','batasanatribut',$key['id']);
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
        if(Utils::cekHakakses('batasan','tm')){
            $atributnilais = AtributNilai::select('atributnilai.id',DB::raw('atribut.id as idatribut'),'atributnilai.nilai','atribut.atribut')
            ->leftjoin('atribut', 'atributnilai.idatribut', '=', 'atribut.id')
            ->orderby('atribut.atribut', 'ASC')
            ->get();

            $atributs = Atribut::select('id','atribut')->get();
            $pekerjaankategori = Utils::getData(DB::connection('perusahaan_db')->getPdo(),'pekerjaankategori','id,nama','digunakan="y"','nama ASC');

            $atribut = Utils::getAtributdanAtributNilaiCrud(0,'batasan');
            Utils::insertLogUser('akses menu tambah batasan atribut');
            return view('datainduk/lainlain/batasan/atribut/create', ['atribut' => $atribut, 'atributs' => $atributs, 'datapekerjaankategori' => $pekerjaankategori, 'atributnilais' => $atributnilais, 'menu' => 'batasan']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek apakah kembar?
        $cekkembar = Utils::getData($pdo,'batasan','id','namabatasan = "'.$request->namabatasan.'" LIMIT 1');
        if($cekkembar == ''){
            $pdo->beginTransaction();
            try
            {
                $sql = 'INSERT INTO batasan VALUES(NULL,:namabatasan,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':namabatasan', $request->namabatasan);
                $stmt->execute();

                $idbatasan = $pdo->lastInsertId();

                if($request->atribut != ''){
                    for($i=0;$i<count($request->atribut);$i++){
                        $sql = 'INSERT INTO batasanatribut VALUES(NULL,:idbatasan,:idatributnilai)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idbatasan', $idbatasan);
                        $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                        $stmt->execute();
                    }
                }

                if($request->pekerjaankategori != ''){
                    for($i=0;$i<count($request->pekerjaankategori);$i++){
                        $sql = 'INSERT INTO batasanpekerjaankategori VALUES(NULL,:idbatasan,:idpekerjaankategori)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idbatasan', $idbatasan);
                        $stmt->bindValue(':idpekerjaankategori', $request->pekerjaankategori[$i]);
                        $stmt->execute();
                    }
                }

                Utils::insertLogUser('Tambah batasan "'.$request->namabatasan.'"');

                $pdo->commit();
                return redirect('datainduk/lainlain/batasanatribut')->with('message', trans('all.databerhasildisimpan'));

            } catch (\Exception $e) {
                $pdo->rollBack();
                return redirect('datainduk/lainlain/batasanatribut/create')->with('message', trans('all.terjadigangguan'));
            }
        }else{
            return redirect('datainduk/lainlain/batasanatribut/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('batasan','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,namabatasan FROM batasan WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $batasan = $stmt->fetch(PDO::FETCH_OBJ);
            $sql = 'SELECT 
                        at.id,
                        at.atribut,
                        COUNT(*)=SUM(IF(ISNULL(hla.id)=false,1,0)) as flag,
                        SUM(IF(ISNULL(hla.id)=false,1,0))>0 as pakaiheader
                    FROM 
                        atribut at,
                        atributnilai an
                        LEFT JOIN batasanatribut hla ON hla.idatributnilai=an.id AND hla.idbatasan = :idbatasan
                    WHERE 
                        an.idatribut=at.id
                    GROUP BY
                        at.id
                    ORDER BY
                        at.atribut
                    ';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idbatasan', $id);
            $stmt->execute();
            $atributs = $stmt->fetchAll(PDO::FETCH_OBJ);

            foreach ($atributs as $row) {
                // ambil data atributnilai
                $sql = 'SELECT 
                            an.id,
                            an.nilai,
                            IF(ISNULL(pa.id),"0","1") as dipilih
                        FROM 
                            atributnilai an 
                            LEFT JOIN batasanatribut pa ON pa.idatributnilai=an.id AND pa.idbatasan=:idbatasan
                        WHERE 
                            an.idatribut=:idatribut
                        ORDER BY
                            an.nilai';
                $stmt2 = $pdo->prepare($sql);
                $stmt2->bindValue(':idbatasan', $id);
                $stmt2->bindValue(':idatribut', $row->id);
                $stmt2->execute();

                $row->atributnilai=$stmt2->fetchAll(PDO::FETCH_OBJ);
            }

            if(!$batasan){
                abort(404);
            }

            // ambil data pekerjaankategori
            $sql = 'SELECT 
                            pk.id,
                            pk.nama,
                            IF(ISNULL(bpk.id),"0","1") as dipilih
                        FROM 
                            pekerjaankategori pk 
                            LEFT JOIN batasanpekerjaankategori bpk ON bpk.idpekerjaankategori=pk.id AND bpk.idbatasan=:idbatasan
                        WHERE
                            pk.digunakan = "y"
                        ORDER BY
                            pk.nama ASC';
            $stmt2 = $pdo->prepare($sql);
            $stmt2->bindValue(':idbatasan', $id);
            $stmt2->execute();
            $pekerjaankategori = $stmt2->fetchAll(PDO::FETCH_OBJ);

            $atribut = Utils::getAtributdanAtributNilaiCrud($id, 'batasan');
            Utils::insertLogUser('akses menu ubah batasan atribut');
            return view('datainduk/lainlain/batasan/atribut/edit', ['arratribut' => $atribut, 'batasan' => $batasan, 'datapekerjaankategori' => $pekerjaankategori, 'atributs' => $atributs, 'menu' => 'batasan']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idatribut ada
        $cekadadata = Utils::getDataWhere($pdo,'batasan','namabatasan','id',$id);
        if($cekadadata != ''){
            //cek apakah kembar?
            $cekkembar = Utils::getData($pdo,'batasan','id','namabatasan = "'.$request->namabatasan.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                // ubah data batasan
                $pdo->beginTransaction();
                try
                {
                    $sql = 'UPDATE batasan SET namabatasan = :namabatasan WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':namabatasan', $request->namabatasan);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::deleteData($pdo,'batasanatribut',$id,'idbatasan');

                    if ($request->atribut != '') {
                        for ($i = 0; $i < count($request->atribut); $i++) {
                            $sql = 'INSERT INTO batasanatribut VALUES(NULL,:idbatasan,:idatributnilai)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idbatasan', $id);
                            $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                            $stmt->execute();
                        }
                    }

                    Utils::deleteData($pdo,'batasanpekerjaankategori',$id,'idbatasan');

                    if($request->pekerjaankategori != ''){
                        for($i=0;$i<count($request->pekerjaankategori);$i++){
                            $sql = 'INSERT INTO batasanpekerjaankategori VALUES(NULL,:idbatasan,:idpekerjaankategori)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idbatasan', $id);
                            $stmt->bindValue(':idpekerjaankategori', $request->pekerjaankategori[$i]);
                            $stmt->execute();
                        }
                    }

                    Utils::insertLogUser('Ubah batasan "'.$cekadadata.'" => "'.$request->namabatasan.'"');

                    $pdo->commit();
                    return redirect('datainduk/lainlain/batasanatribut')->with('message', trans('all.databerhasildiubah'));

                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return redirect('datainduk/lainlain/batasanatribut/create')->with('message', trans('all.terjadigangguan'));
                }
            }else{
                return redirect('datainduk/lainlain/batasanatribut/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/lainlain/batasanatribut/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('batasan','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan idatribut ada
            $cekadadata = Utils::getDataWhere($pdo,'batasan','namabatasan','id',$id);
            if ($cekadadata != '') {
                Utils::deleteData($pdo,'batasan',$id);
                Utils::insertLogUser('Hapus batasan "'.$cekadadata.'"');
                return redirect('datainduk/lainlain/batasanatribut')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/lainlain/batasanatribut')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('batasan','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.batasan').' '.trans('all.atribut'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.atribut'));

            $sql = 'SELECT
                        b.id,
                        b.namabatasan as nama,
                        GROUP_CONCAT(a.nilai SEPARATOR ", ") as atributnilai
                    FROM
                        batasan b
                        LEFT JOIN batasanatribut ba ON ba.idbatasan=b.id
                        LEFT JOIN atributnilai a ON ba.idatributnilai=a.id
                    GROUP BY
                        b.id
                    ORDER BY
                        b.namabatasan';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['atributnilai']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor batasan atribut');
            $arrWidth = array(15, 150);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.batasan') . '_' . trans('all.atribut'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}