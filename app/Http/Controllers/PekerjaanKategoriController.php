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

class PekerjaanKategoriController extends Controller
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
        if(Utils::cekHakakses('pekerjaan','l')){
            Utils::insertLogUser('akses menu pekerjaan kategori');
	        return view('datainduk/pegawai/pekerjaankategori/index', ['menu' => 'pekerjaankategori']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('pekerjaan','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','nama','digunakan');
            $totalData = Utils::getDataCustomWhere($pdo,'pekerjaankategori', 'count(id)');
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $columns[$request->input('order.0.column')];
            $orderAction = $request->input('order.0.dir');
            $orderBy = $orderColumn.' '.$orderAction;

            if(!empty($request->input('search.value'))){
                $search = $request->input('search.value');
                $where .= Utils::searchDatatableQuery($columns);
                $sql = 'SELECT COUNT(id) as total FROM pekerjaankategori WHERE 1=1 '.$where;
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

            $sql = 'SELECT id,nama,digunakan FROM pekerjaankategori WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    $action = '<a title="' . trans('all.detail') . '" href="pekerjaanitem/' . $key['id'] . '"><i class="fa fa-pencil-square-o" style="color:#1c84c6"></i></a>&nbsp;&nbsp;';
                    if(Utils::cekHakakses('pekerjaan','um')){
                        $action .= Utils::tombolManipulasi('ubah','pekerjaankategori',$key['id']);
                    }
                    if(Utils::cekHakakses('pekerjaan','hm')){
                        $action .= Utils::tombolManipulasi('hapus','pekerjaankategori',$key['id']);
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        if($columns[$i] == 'digunakan') {
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
        if(Utils::cekHakakses('pekerjaan','tm')){
            Utils::insertLogUser('akses menu tambah pekerjaan kategori');
            return view('datainduk/pegawai/pekerjaankategori/create', ['menu' => 'pekerjaankategori']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM pekerjaankategori WHERE nama=:nama LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nama', $request->nama);
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            try {
                $pdo->beginTransaction();
                $sql = 'INSERT INTO pekerjaankategori VALUES(NULL,:nama,:digunakan,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':digunakan', $request->digunakan);
                $stmt->execute();

                $idpekerjaankategori = $pdo->lastInsertId();
                //simpan ke pekerjaan item
                if (isset($request->itemnama)) {
                    for ($i = 0; $i < count($request->itemnama); $i++) {
                        if($request->itemnama[$i] != '' && $request->itemsatuan[$i] != '') {
                            $sql = 'INSERT INTO pekerjaanitem VALUES(NULL,:idpekerjaankategori,:item,:satuan,:urutan,:digunakan,NOW(),NULL)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idpekerjaankategori', $idpekerjaankategori);
                            $stmt->bindValue(':item', $request->itemnama[$i]);
                            $stmt->bindValue(':satuan', $request->itemsatuan[$i]);
                            $stmt->bindValue(':urutan', $i+1);
                            $stmt->bindValue(':digunakan', $request->itemdigunakan[$i]);
                            $stmt->execute();
                        }
                    }
                }

                Utils::insertLogUser('Tambah pekerjaan kategori"' . $request->nama . '"');
                $pdo->commit();

                return redirect('datainduk/pegawai/pekerjaankategori')->with('message', trans('all.databerhasildisimpan'));
            } catch (\Exception $e){
                $pdo->rollBack();
//                return redirect('datainduk/pegawai/pekerjaankategori')->with('message', trans('all.terjadigangguan'));
                return redirect('datainduk/pegawai/pekerjaankategori')->with('message', $e->getMessage());
            }
        }else{
            return redirect('datainduk/pegawai/pekerjaankategori/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('pekerjaan','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM pekerjaankategori WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            $datapekerjaanitem = Utils::getData($pdo,'pekerjaanitem','id,item,satuan,digunakan','idpekerjaankategori='.$id,'urutan');

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah pekerjaan kategori');
            return view('datainduk/pegawai/pekerjaankategori/edit', ['data' => $data, 'datapekerjaanitem' => $datapekerjaanitem, 'menu' => 'pekerjaankategori']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'pekerjaankategori','nama','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'pekerjaankategori','id','nama = "'.$request->nama.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                try {

                    $sql = 'UPDATE pekerjaankategori SET nama = :nama, digunakan = :digunakan WHERE id = :id LIMIT 1';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':nama', $request->nama);
                    $stmt->bindValue(':digunakan', $request->digunakan);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah pekerjaan kategori "' . $cekadadata . '" => "' . $request->nama . '"');

                    return redirect('datainduk/pegawai/pekerjaankategori')->with('message', trans('all.databerhasildiubah'));
                } catch (\Exception $e){
                    return redirect('datainduk/pegawai/pekerjaankategori')->with('message', trans('all.terjadigangguan'));
                }
            }else{
                return redirect('datainduk/pegawai/pekerjaankategori/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/pegawai/pekerjaankategori/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('pekerjaan','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'pekerjaankategori','nama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'pekerjaankategori',$id);
                Utils::insertLogUser('Hapus pekerjaan kategori"'.$cekadadata.'"');
                return redirect('datainduk/pegawai/pekerjaankategori')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/pegawai/pekerjaankategori')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('pekerjaan','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.pekerjaankategori'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.digunakan'));

            $sql = 'SELECT
                        nama,
                        digunakan
                    FROM
                        pekerjaankategori
                    ORDER BY
                        nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['digunakan'] == 'y' ? trans('all.ya') : trans('all.tidak'));

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor pekerjaan kategori');
            $arrWidth = array(25, 15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.kategoripekerjaan'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}