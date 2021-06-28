<?php
namespace App\Http\Controllers;

use App\SlideShow;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class SlideShowController extends Controller
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
        if(Utils::cekHakakses('slideshow','l')){
            Utils::insertLogUser('akses menu slideshow');
            return view('pengaturan/slideshow/index', ['menu' => 'slideshow']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
    {
        if(Utils::cekHakakses('slideshow','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','nama','timeout','durasiperslide');
            $totalData = Utils::getDataCustomWhere($pdo,'slideshow', 'count(id)');
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $columns[$request->input('order.0.column')];
            $orderAction = $request->input('order.0.dir');
            $orderBy = $orderColumn.' '.$orderAction;

            if(!empty($request->input('search.value'))){
                $search = $request->input('search.value');
                $where .= Utils::searchDatatableQuery($columns);
                $sql = 'SELECT COUNT(id) as total FROM slideshow WHERE 1=1 '.$where;
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

            $sql = 'SELECT id,nama,timeout,durasiperslide FROM slideshow WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    $action = Utils::tombolManipulasi('detail','slideshow',$key['id']);
                    if(Utils::cekHakakses('slideshow','um')){
                        $action .= Utils::tombolManipulasi('ubah','slideshow',$key['id']);
                    }
                    if(Utils::cekHakakses('slideshow','hm')){
                        $action .= Utils::tombolManipulasi('hapus','slideshow',$key['id']);
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        if($columns[$i] == 'timeout' || $columns[$i] == 'durasiperslide') {
                            $tempdata[$columns[$i]] = $key[$columns[$i]] . ' ' . trans('all.detik');
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
        if(Utils::cekHakakses('slideshow','tm')){
            Utils::insertLogUser('akses menu tambah slideshow');
            return view('pengaturan/slideshow/create', ['menu' => 'slideshow']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek apakah kembar?
        $sql = 'SELECT id FROM slideshow WHERE nama=:nama LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nama', $request->nama);
        $stmt->execute();

        if ($stmt->rowCount()==0) {

            try
            {
                $pdo->beginTransaction();

                $sql = 'INSERT INTO slideshow VALUES(NULL,:nama,:timeout,:durasiperslide,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':timeout', $request->timeout);
                $stmt->bindValue(':durasiperslide', $request->durasiperslide);
                $stmt->execute();

                $idslideshow = $pdo->lastInsertId();

                // simpan ke tabel slideshowwaktu
                if ($request->waktumulai != '') {
                    for ($i = 0; $i < count($request->waktumulai); $i++) {
                        $sql = 'INSERT INTO slideshowwaktu VALUES(NULL,:idslideshow,:waktumulai,:waktuselesai)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idslideshow', $idslideshow);
                        $stmt->bindValue(':waktumulai', $request->waktumulai[$i]);
                        $stmt->bindValue(':waktuselesai', $request->waktuselesai[$i]);
                        $stmt->execute();
                    }
                }

                Utils::insertLogUser('Tambah rangkai salindia "'.$request->nama.'"');

                $pdo->commit();
                return redirect('pengaturan/slideshow')->with('message', trans('all.databerhasildisimpan'));
            } catch (\Exception $e) {
                $pdo->rollBack();
                return redirect('pengaturan/slideshow/')->with('message', trans('all.terjadigangguan'));
            }
        }else{
            return redirect('pengaturan/slideshow/create')->with('message', trans('all.datasudahada'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('slideshow','um')){
            $slideshow = SlideShow::find($id);
            if(!$slideshow){
                abort(404);
            }

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM slideshowwaktu WHERE idslideshow = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $slideshowwaktu = $stmt->fetchAll(PDO::FETCH_OBJ);
            $totalslideshowwaktu = $stmt->rowCount();
            Utils::insertLogUser('akses menu ubah slideshow');
            return view('pengaturan/slideshow/edit', ['slideshow' => $slideshow, 'slideshowwaktu' => $slideshowwaktu, 'totalslideshowwaktu' => $totalslideshowwaktu, 'menu' => 'slideshow']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //pastikan idatribut ada
        $sql = 'SELECT id,nama FROM slideshow WHERE id=:idslideshow LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idslideshow', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //cek apakah kembar?
            $sql = 'SELECT id FROM slideshow WHERE nama=:nama AND id<>:idslideshow LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':idslideshow', $id);
            $stmt->execute();
            if ($stmt->rowCount()==0) {
                try
                {
                    $pdo->beginTransaction();

                    // ubah data slideshow
                    $sql = 'UPDATE slideshow SET nama = :nama, timeout = :timeout, durasiperslide = :durasiperslide WHERE id = :idslideshow';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':nama', $request->nama);
                    $stmt->bindValue(':timeout', $request->timeout);
                    $stmt->bindValue(':durasiperslide', $request->durasiperslide);
                    $stmt->bindValue(':idslideshow', $id);
                    $stmt->execute();

                    //delete slideshowwaktu
                    $sql = 'DELETE FROM slideshowwaktu WHERE idslideshow = :idslideshow';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idslideshow', $id);
                    $stmt->execute();

                    // simpan ke tabel slideshowwaktu
                    if ($request->waktumulai != '') {
                        for ($i = 0; $i < count($request->waktumulai); $i++) {
                            $sql = 'INSERT INTO slideshowwaktu VALUES(NULL,:idslideshow,:waktumulai,:waktuselesai)';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idslideshow', $id);
                            $stmt->bindValue(':waktumulai', $request->waktumulai[$i]);
                            $stmt->bindValue(':waktuselesai', $request->waktuselesai[$i]);
                            $stmt->execute();
                        }
                    }

                    Utils::insertLogUser('Ubah rangkai salindia "'.$row['nama'].'" => "'.$request->nama.'"');

                    $pdo->commit();
                    $msg = trans('all.databerhasildiubah');
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    $msg = $e->getMessage();
                }
                return redirect('pengaturan/slideshow')->with('message', $msg);
            }else{
                $msg = trans('all.datasudahada');
            }
        }else{
            $msg = trans('all.datatidakditemukan');
        }
        return redirect('pengaturan/slideshow/'.$id.'/edit')->with('message', $msg);
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('slideshow','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //pastikan idatribut ada
            $sql = 'SELECT id,nama FROM slideshow WHERE id=:idslideshow LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idslideshow', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                SlideShow::find($id)->delete();
                Utils::insertLogUser('Hapus rangkai salindia "'.$row['nama'].'"');

                return redirect('pengaturan/slideshow')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('pengaturan/slideshow/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('slideshow','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.slideshow'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.timeout'))
                        ->setCellValue('C1', trans('all.durasiperslide'));

            $sql = 'SELECT nama,timeout,durasiperslide FROM slideshow ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['timeout']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['durasiperslide']);

                $i++;
            }

            $arrWidth = array('', 50, 15, 15);
            for ($j = 1; $j <= 3; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor slideshow');
            $arrWidth = array(50, 15, 15);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.slideshow'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }

    public function getDetail($id)
    {
        if(Utils::cekHakakses('slideshow','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            //select nama slideshow
            $sql = 'SELECT nama FROM slideshow WHERE id = :idslideshow';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idslideshow', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $slideshow = $row['nama'];

            $sql = 'SELECT * FROM slideshowimage WHERE idslideshow = :idslideshow';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idslideshow', $id);
            $stmt->execute();
            $data = $stmt->fetchALL(PDO::FETCH_OBJ);
            $totaldata = $stmt->rowCount();
            Utils::insertLogUser('akses menu slideshow detail');
            return view('pengaturan/slideshow/detail', ['data' => $data, 'totaldata' => $totaldata, 'idslideshow' => $id, 'slideshow' => $slideshow, 'menu' => 'slideshow']);
        }else{
            return redirect('/');
        }
    }

    public function submitDetail(Request $request)
    {
        // simpan foto jika ada
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idslideshow = $request->idslideshow;
        if( $request->hasFile('slideshow') ) {
            $path = Session::get('folderroot_perusahaan').'/slideshow/';
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            for($i=0;$i<count($request->file('slideshow'));$i++){
                if($request->file('slideshow')[$i] != '') {
                    $randomnama = date('Ymdhis').'_'.rand(10000,99999).'_'.$i;

                    $fileslideshow = $request->file('slideshow')[$i];
                    //cek apakah format jpeg?
                    if($fileslideshow->getMimeType() == 'image/jpeg') {
                        try {
                            $pdo->beginTransaction();

                            // jika file foto sudah ada, maka akan di overwrite
                            $sql = 'INSERT INTO slideshowimage VALUES(NULL,:idslideshow,:filename,"")';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idslideshow', $idslideshow);
                            $stmt->bindValue(':filename', $randomnama);
                            $stmt->execute();

                            $idslideshowdetail = $pdo->lastInsertId();

                            Utils::makeThumbnail($request->file('slideshow')[$i], $path . '/' . $randomnama);

                            Utils::saveUploadImage($request->file('slideshow')[$i], $path . '/' . $randomnama);

                            $checksum = md5_file($path . '/' . $randomnama);

                            $sql = 'UPDATE slideshowimage set checksum = :checksum WHERE id = :idslideshowdetail';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':checksum', $checksum);
                            $stmt->bindValue(':idslideshowdetail', $idslideshowdetail);
                            $stmt->execute();

                            $pdo->commit();

                        } catch (\Exception $e) {
                            $pdo->rollBack();
                            return redirect('pengaturan/slideshow/' . $idslideshow . '/detail')->with('message', trans('all.terjadigangguan'));
                        }
                    }
                }
            }
        }
        Utils::insertLogUser('ubah slideshow detail');
        return redirect('pengaturan/slideshow/'.$idslideshow.'/detail')->with('message', trans('all.databerhasildisimpan'));
    }
}