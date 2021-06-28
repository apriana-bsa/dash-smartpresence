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

class PekerjaanItemController extends Controller
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

	public function getIndex($idpekerjaankategori)
	{
        if(Utils::cekHakakses('pekerjaan','l')){
            $pekerjaankategori = Utils::getDataWhere(DB::connection('perusahaan_db')->getPdo(),'pekerjaankategori','nama','id',$idpekerjaankategori);
            Utils::insertLogUser('akses menu pekerjaan item');
	        return view('datainduk/pegawai/pekerjaanitem/index', ['idpekerjaankategori' => $idpekerjaankategori, 'pekerjaankategori' => $pekerjaankategori, 'menu' => 'pekerjaankategori']);
        }else{
            return redirect('/');
        }
	}

	public function data(Request $request, $idpekerjaankategori)
	{
        if(Utils::cekHakakses('pekerjaan','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('pekerjaan','uhm')){
                $columns = array('','urutan','item','satuan','digunakan');
            }else{
                $columns = array('urutan','item','satuan','digunakan');
            }
            $totalData = Utils::getDataCustomWhere($pdo,'pekerjaanitem', 'count(id)', '1=1 '.$where);
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $columns[$request->input('order.0.column')];
            $orderAction = $request->input('order.0.dir');
            $orderBy = $orderColumn.' '.$orderAction;

            if(!empty($request->input('search.value'))){
                $search = $request->input('search.value');
                $where .= Utils::searchDatatableQuery($columns);
                $sql = 'SELECT COUNT(id) as total FROM pekerjaanitem WHERE idpekerjaankategori = :idpekerjaankategori '.$where;
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpekerjaankategori', $idpekerjaankategori);
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                    }
                }
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalFiltered = $row['total'];
            }

            $sql = 'SELECT id,idpekerjaankategori,item,satuan,urutan,digunakan FROM pekerjaanitem WHERE idpekerjaankategori = :idpekerjaankategori '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpekerjaankategori', $idpekerjaankategori);
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
                    if(Utils::cekHakakses('pekerjaan','um')){
                        $action .= Utils::tombolManipulasi('ubahcustom',$idpekerjaankategori.'/'.$key['id'].'/edit',$key['id']);
                    }
                    if(Utils::cekHakakses('pekerjaan','hm')){
                        $action .= '<a title="' . trans('all.hapus') . '" href="#" onclick="return submithapus(\'' . $key['id'] . '\',\''.trans('all.alerthapus').'\',\''.trans('all.ya').'\',\''.trans('all.tidak').'\')"><i class="fa fa-trash" style="color:#ed5565"></i></a>
                                    <form id="formhapus" action="'.$idpekerjaankategori.'/' . $key['id'] . '/hapus" method="post">
                                      <input type="hidden" name="_token" value="' . csrf_token() . '">
                                      <input type="submit" id="' . $key['id'] . '" style="display:none" name="delete" value="' . trans('all.hapus') . '">
                                    </form>';
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'digunakan') {
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

	public function create($idpekerjaankategori)
    {
        if(Utils::cekHakakses('pekerjaan','tm')){
            $pekerjaankategori = Utils::getDataWhere(DB::connection('perusahaan_db')->getPdo(),'pekerjaankategori','nama','id',$idpekerjaankategori);
            Utils::insertLogUser('akses menu tambah pekerjaan item');
            return view('datainduk/pegawai/pekerjaanitem/create', ['idpekerjaankategori' => $idpekerjaankategori, 'pekerjaankategori' => $pekerjaankategori, 'menu' => 'pekerjaankategori']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request, $idpekerjaankategori)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataCustomWhere($pdo,'pekerjaanitem','id','idpekerjaankategori = '.$idpekerjaankategori.' AND item = "'.$request->item.'"');
        if($cekadadata == ''){
            try {
                $pdo->beginTransaction();
                $sql = 'INSERT INTO pekerjaanitem VALUES(NULL,:idpekerjaankategori,:item,:satuan,:urutan,:digunakan,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpekerjaankategori', $idpekerjaankategori);
                $stmt->bindValue(':item', $request->item);
                $stmt->bindValue(':satuan', $request->satuan);
                $stmt->bindValue(':urutan', 1);
                $stmt->bindValue(':digunakan', $request->digunakan);
                $stmt->execute();

                Utils::insertLogUser('Tambah pekerjaan item"' . $request->item . '"');
                $pdo->commit();

                return redirect('datainduk/pegawai/pekerjaanitem/'.$idpekerjaankategori)->with('message', trans('all.databerhasildisimpan'));
            } catch (\Exception $e){
                $pdo->rollBack();
                return redirect('datainduk/pegawai/pekerjaanitem/'.$idpekerjaankategori)->with('message', $e->getMessage());
            }
        }else{
            return redirect('datainduk/pegawai/pekerjaanitem/'.$idpekerjaankategori.'/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($idpekerjaankategori, $id)
    {
        if(Utils::cekHakakses('pekerjaan','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM pekerjaanitem WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            $datapekerjaanitem = Utils::getData($pdo,'pekerjaanitem','id,item,satuan,digunakan','idpekerjaankategori='.$id,'urutan');
            $pekerjaankategori = Utils::getDataWhere(DB::connection('perusahaan_db')->getPdo(),'pekerjaankategori','nama','id',$idpekerjaankategori);

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah pekerjaan item');
            return view('datainduk/pegawai/pekerjaanitem/edit', ['pekerjaankategori' => $pekerjaankategori, 'idpekerjaankategori' => $idpekerjaankategori, 'data' => $data, 'datapekerjaanitem' => $datapekerjaanitem, 'menu' => 'pekerjaankategori']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $idpekerjaankategori, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'pekerjaanitem','item','id',$id);
        if($cekadadata != ''){
            $cekkembar = Utils::getData($pdo,'pekerjaanitem','id','item = "'.$request->item.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                try {
                    $sql = 'UPDATE pekerjaanitem SET item = :item, satuan = :satuan, urutan = :urutan, digunakan = :digunakan WHERE id = :id LIMIT 1';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':item', $request->item);
                    $stmt->bindValue(':satuan', $request->satuan);
                    $stmt->bindValue(':urutan', $request->urutan);
                    $stmt->bindValue(':digunakan', $request->digunakan);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah pekerjaan kategori "' . $cekadadata . '" => "' . $request->item . '"');

                    return redirect('datainduk/pegawai/pekerjaanitem/'.$idpekerjaankategori)->with('message', trans('all.databerhasildiubah'));
                } catch (\Exception $e){
                    return redirect('datainduk/pegawai/pekerjaanitem/'.$idpekerjaankategori)->with('message', trans('all.terjadigangguan'));
                }
            }else{
                return redirect('datainduk/pegawai/pekerjaanitem/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('datainduk/pegawai/pekerjaanitem/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($idpekerjaankategori,$id)
    {
        if(Utils::cekHakakses('pekerjaan','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'pekerjaanitem','item','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'pekerjaanitem',$id);
                Utils::insertLogUser('Hapus pekerjaan item"'.$cekadadata.'"');
                return redirect('datainduk/pegawai/pekerjaanitem/'.$idpekerjaankategori)->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('datainduk/pegawai/pekerjaanitem/'.$idpekerjaankategori)->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel($idpekerjaankategori)
    {
        if(Utils::cekHakakses('pekerjaan','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel);

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.urutan'))
                        ->setCellValue('B1', trans('all.nama'))
                        ->setCellValue('C1', trans('all.satuan'))
                        ->setCellValue('D1', trans('all.digunakan'));

            $sql = 'SELECT
                      item,
                      satuan,
                      urutan,
                      digunakan
                    FROM
                      pekerjaanitem
                    WHERE
                      idpekerjaankategori = :idpekerjaankategori
                    ORDER BY
                      urutan ASC, item ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpekerjaankategori', $idpekerjaankategori);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['urutan']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['item']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['satuan']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['digunakan'] == 'y' ? trans('all.ya') : trans('all.tidak'));

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor pekerjaan item');
            $arrWidth = array(8, 25, 15, 15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(Utils::getDataWhere($pdo,'pekerjaankategori','nama','id',$idpekerjaankategori));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}