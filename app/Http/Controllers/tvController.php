<?php
namespace App\Http\Controllers;

use App\tv;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use App\Utils;

class tvController extends Controller
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
        if(Utils::cekHakakses('pengaturan','lum')){
            $totaldata = Utils::getTotalData(1,'tv');
            Utils::insertLogUser('akses menu tv');
	        return view('pengaturan/tv/index', ['totaldata' => $totaldata, 'menu' => 'tv']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('pengaturan','lum')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','nama','header_baris1','orientasi','interval_refresh_data','warna_background');
            $totalData = Utils::getDataCustomWhere($pdo,'tv', 'count(id)');
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $columns[$request->input('order.0.column')];
            $orderAction = $request->input('order.0.dir');
            $orderBy = $orderColumn.' '.$orderAction;

            if(!empty($request->input('search.value'))){
                $search = $request->input('search.value');
                $where .= Utils::searchDatatableQuery($columns);
                $sql = 'SELECT COUNT(id) as total FROM tv WHERE 1=1 '.$where;
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

            $sql = 'SELECT * FROM tv WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                    $action = Utils::tombolManipulasi('detail','tv',$key['id']);
                    $action .= Utils::tombolManipulasi('ubah','tv',$key['id']);
                    $action .= Utils::tombolManipulasi('hapus','tv',$key['id']);
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        $hasil = htmlentities($key[$columns[$i]]);
                        if($columns[$i] == 'header_baris1') {
                            $hasil = $key['header_baris1'].'<br>'.$key['header_baris2'];
                        }elseif($columns[$i] == 'orientasi') {
                            $hasil = '<table>
                                        <tr>
                                            <td>'.trans('all.orientasi').'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td>'.($key['orientasi'] == 'vertical' ? trans('all.vertikal') : trans('all.horisontal')).'</td>
                                        </tr>';
                                        if($key['orientasi'] == 'horizontal') {
                                            $hasil .= '<tr>
                                                <td>' . trans('all.jumlah_kolom_horizontal') . '</td>
                                                <td>&nbsp;:&nbsp;</td>
                                                <td>' . $key['jumlah_kolom_horizontal'] . '</td>
                                            </tr>';
                                        }
                                        $hasil .= ' <tr>
                                            <td>'.trans('all.bahasa').'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td>'.($key['bahasa'] == 'id' ? 'Indonesia' : ($key['bahasa'] == 'en' ? 'English' : '中国')).'</td>
                                        </tr>
                                      </table>';
                        }elseif($columns[$i] == 'interval_refresh_data') {
                            $hasil = '<table>
                                            <tr>
                                                <td>'.trans('all.refresh').'</td>
                                                <td>&nbsp;:&nbsp;</td>
                                                <td>'.$key['interval_refresh_data'].' '.trans('all.detik').'</td>
                                            </tr>
                                            <tr>
                                                <td>'.trans('all.slide').'</td>
                                                <td>&nbsp;:&nbsp;</td>
                                                <td>'.$key['interval_slide'].' '.trans('all.detik').'</td>
                                            </tr>
                                        </table>';
                        }elseif($columns[$i] == 'warna_background') {
                            $hasil = '<table>
                                        <tr>
                                            <td>'.trans('all.latarbelakang').'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td style="padding-bottom:10px"><span style="padding:5px;background-color: #'.$key['warna_background'].';color:'.Utils::getTextColor($key['warna_background']).'">'.$key['warna_background'].'</span></td>
                                        </tr>
                                        <tr>
                                            <td>'.trans('all.headerfooter').'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td style="padding-bottom:10px;padding-top:5px"><span style="padding:5px;background-color: #'.$key['headerfooter_warna_background'].';color:'.Utils::getTextColor($key['headerfooter_warna_background']).'">'.$key['headerfooter_warna_background'].'</span></td>
                                        </tr>
                                        <tr>
                                            <td>'.trans('all.headerfooterteks').'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td style="padding-top:5px"><span style="padding:5px;background-color: #'.$key['headerfooter_warna_teks'].';color:'.Utils::getTextColor($key['headerfooter_warna_teks']).'">'.$key['headerfooter_warna_teks'].'</span></td>
                                        </tr>
                                    </table>';
                        }
                        $tempdata[$columns[$i]] = $hasil;
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
        if(Utils::cekHakakses('pengaturan','lum')){
            Utils::insertLogUser('akses menu tambah tv');
            return view('pengaturan/tv/create', ['menu' => 'tv']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM tv WHERE nama= :nama';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nama', $request->nama);
        $stmt->execute();
        if ($stmt->rowCount()==0) {

            $sql = 'INSERT INTO tv VALUES(NULL,:nama,:header_baris1,:header_baris2,:orientasi,:jumlah_kolom_horizontal,:interval_refresh_data,:interval_slide,:bahasa,:warna_background,:headerfooter_warna_background,:headerfooter_warna_teks)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':header_baris1', $request->header_baris1);
            $stmt->bindValue(':header_baris2', $request->header_baris2);
            $stmt->bindValue(':orientasi', $request->orientasi);
            $stmt->bindValue(':jumlah_kolom_horizontal', $request->jumlah_kolom_horizontal);
            $stmt->bindValue(':interval_refresh_data', $request->interval_refresh_data);
            $stmt->bindValue(':interval_slide', $request->interval_slide);
            $stmt->bindValue(':bahasa', $request->bahasa);
            $stmt->bindValue(':warna_background', $request->warna_background);
            $stmt->bindValue(':headerfooter_warna_background', $request->headerfooter_warna_background);
            $stmt->bindValue(':headerfooter_warna_teks', $request->headerfooter_warna_teks);
            $stmt->execute();

            Utils::insertLogUser('Tambah tv "'.$request->nama.'"');
    
            return redirect('pengaturan/tv')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('pengaturan/tv/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('pengaturan','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM tv WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah tv');
            return view('pengaturan/tv/edit', ['data' => $data, 'menu' => 'tv']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,nama FROM tv WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //cek apakah kembar?
            $sql = 'SELECT id FROM tv WHERE nama=:nama AND id<>:idtv LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':idtv', $id);
            $stmt->execute();
            if ($stmt->rowCount()==0) {

                $sql = 'UPDATE tv SET nama = :nama, header_baris1 = :header_baris1, header_baris2 = :header_baris2, orientasi = :orientasi, jumlah_kolom_horizontal = :jumlah_kolom_horizontal, interval_refresh_data = :interval_refresh_data, interval_slide = :interval_slide, bahasa = :bahasa, warna_background = :warna_background, headerfooter_warna_background = :headerfooter_warna_background, headerfooter_warna_teks = :headerfooter_warna_teks WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':header_baris1', $request->header_baris1);
                $stmt->bindValue(':header_baris2', $request->header_baris2);
                $stmt->bindValue(':orientasi', $request->orientasi);
                $stmt->bindValue(':jumlah_kolom_horizontal', $request->jumlah_kolom_horizontal);
                $stmt->bindValue(':interval_refresh_data', $request->interval_refresh_data);
                $stmt->bindValue(':interval_slide', $request->interval_slide);
                $stmt->bindValue(':bahasa', $request->bahasa);
                $stmt->bindValue(':warna_background', $request->warna_background);
                $stmt->bindValue(':headerfooter_warna_background', $request->headerfooter_warna_background);
                $stmt->bindValue(':headerfooter_warna_teks', $request->headerfooter_warna_teks);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::insertLogUser('Ubah tv "'.$row['nama'].'" => "'.$request->nama.'"');
    
                return redirect('pengaturan/tv')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('pengaturan/tv/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('pengaturan/tv/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('pengaturan','um')){
            //pastikan idtv ada
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,nama FROM tv WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                Utils::deleteData($pdo,'tv',$id);
                Utils::insertLogUser('Hapus tv "'.$row['nama'].'"');
                return redirect('pengaturan/tv')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('pengaturan/tv')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function detail($idtv)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $datatvgroup = Utils::getData($pdo,'tvgroup','id,nama','id NOT IN(SELECT idtvgroup FROM tvdetail WHERE idtv = '.$idtv.')','nama');
        $sql = 'SELECT g.id,g.nama FROM tvdetail d, tvgroup g WHERE d.idtvgroup = g.id AND d.idtv = :idtv ORDER BY d.urutan ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idtv', $idtv);
        $stmt->execute();
        $datatvdetail = $stmt->fetchAll(PDO::FETCH_OBJ);

        $tv = Utils::getDataWhere($pdo,'tv','nama','id',$idtv);
        Utils::insertLogUser('akses menu tv detail');
        return view('pengaturan/tv/detail', ['idtv' => $idtv, 'tv' => $tv, 'datatvgroup' => $datatvgroup, 'datatvdetail' => $datatvdetail, 'menu' => 'tv']);
    }

    public function submitDetail(Request $request, $idtv)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        if(count($request->id) > 0){
            Utils::deleteData($pdo,'tvdetail',$idtv,'idtv');
            if(count($request->idtvgroup) > 0) {
                for ($i = 0; $i < count($request->idtvgroup); $i++) {
                    $urutan = $i + 1;
                    $sql = 'INSERT INTO tvdetail VALUES(NULL,:idtv,:idtvgroup,:urutan)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idtv', $idtv);
                    $stmt->bindValue(':idtvgroup', $request->idtvgroup[$i]);
                    $stmt->bindValue(':urutan', $urutan);
                    $stmt->execute();
                }
            }
            Utils::insertLogUser('Simpan tv detail');
        }
        return redirect('pengaturan/tv/'.$idtv.'/detail')->with('message', trans('all.databerhasildisimpan'));
    }

    public function excel()
    {
        if(Utils::cekHakakses('pengaturan','lum')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.tv'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.header'))
                        ->setCellValue('C1', trans('all.layout'))
                        ->setCellValue('D1', trans('all.interval'))
                        ->setCellValue('E1', trans('all.warna'));

            $sql = 'SELECT
                        *
                    FROM
                        tv
                    ORDER BY
                        nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $header = $row['header_baris1'].'
'.$row['header_baris2'];
                $interval = trans('all.refreshdata').' : '.$row['interval_refresh_data'].' '.trans('all.detik').'
'.trans('all.slide').' : '.$row['interval_slide'].' '.trans('all.detik');
                $warna = trans('all.latarbelakang').' : '.$row['warna_background'].'
'.trans('all.headerfooter').' : '.$row['headerfooter_warna_background'].'
'.trans('all.headerfooterteks').' : '.$row['headerfooter_warna_teks'];


                $layout = trans('all.orientasi').' : '.($row['orientasi'] == 'vertical' ? trans('all.vertikal') : trans('all.horisontal'));
                if($row['orientasi'] == 'horizontal') {
                    $layout .= '
'.trans('all.jumlah_kolom_horizontal') . ' : ' . $row['jumlah_kolom_horizontal'];
                }
                $layout .= '
'.trans('all.bahasa').' : '.($row['bahasa'] == 'id' ? 'Indonesia' : ($row['bahasa'] == 'en' ? 'English' : '中国'));

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $header);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $layout);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $interval);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $warna);

                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('B' . $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('C' . $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('E' . $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

                $objPHPExcel->getActiveSheet()->getStyle('B' . $i)->getAlignment()->setWrapText(true);
                $objPHPExcel->getActiveSheet()->getStyle('C' . $i)->getAlignment()->setWrapText(true);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $i)->getAlignment()->setWrapText(true);
                $objPHPExcel->getActiveSheet()->getStyle('E' . $i)->getAlignment()->setWrapText(true);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor tv');
            $arrWidth = array(25, 25, 30, 25, 25);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.tv'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}