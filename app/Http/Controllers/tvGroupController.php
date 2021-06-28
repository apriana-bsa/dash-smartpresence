<?php
namespace App\Http\Controllers;

use App\tv;

use Hamcrest\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use App\Utils;

class tvGroupController extends Controller
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
	    if (Utils::cekHakakses('pengaturan','lum')) {
            $totaldata = Utils::getTotalData(1,'tv');
            Utils::insertLogUser('akses menu tv group');
	        return view('pengaturan/tvgroup/index', ['totaldata' => $totaldata, 'menu' => 'tv']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if (Utils::cekHakakses('pengaturan','lum')) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','nama','judul','baris','warna','atribut');
            $table = '(
                        SELECT
                            tg.id,
                            "" as baris,
                            "" as warna,
                            tg.nama,
                            tg.judul,
                            tg.jenis,
                            tg.baris1_label,
                            tg.baris1_data,
                            tg.baris2_label,
                            tg.baris2_data,
                            tg.baris3_label,
                            tg.baris3_data,
                            tg.warna_background,
                            tg.warna_teks,
                            GROUP_CONCAT(an.nilai ORDER BY an.nilai SEPARATOR " ,") as atribut
                        FROM
                            tvgroup tg
                            LEFT JOIN tvgroupatribut tga ON tga.idtvgroup=tg.id
                            LEFT JOIN atributnilai an ON tga.idatributnilai=an.id
                        GROUP BY
                            tg.id
                    ) x';
            $totalData = Utils::getDataCustomWhere($pdo,'tvgroup', 'count(id)');
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
                    $action = Utils::tombolManipulasi('ubah','tvgroup',$key['id']);
                    $action .= Utils::tombolManipulasi('hapus','tvgroup',$key['id']);
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        $hasil = htmlentities($key[$columns[$i]]);
                        if($columns[$i] == 'judul') {
                            $hasil = '<table>
                                        <tr>
                                            <td>'.trans('all.judul').'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td>'.$key['judul'].'</td>
                                        </tr>
                                        <tr>
                                            <td>'.trans('all.jenis').'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td>'.trans('all.'.$key['jenis']).'</td>
                                        </tr>
                                      </table>';
                        }elseif($columns[$i] == 'baris') {
                            $hasil = '<table>
                                        <tr>
                                            <td>'.$key['baris1_label'].'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td>'.$key['baris1_data'].'</td>
                                        </tr>
                                        <tr>
                                            <td>'.$key['baris2_label'].'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td>'.$key['baris2_data'].'</td>
                                        </tr>
                                        <tr>
                                            <td>'.$key['baris3_label'].'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td>'.$key['baris3_data'].'</td>
                                        </tr>
                                      </table>';
                        }elseif($columns[$i] == 'warna') {
                            $hasil = '<table>
                                        <tr>
                                            <td>'.trans('all.latarbelakang').'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td style="padding-bottom:10px"><span style="padding:5px;background-color: #'.$key['warna_background'].';color:'.Utils::getTextColor($key['warna_background']).'">'.$key['warna_background'].'</span></td>
                                        </tr>
                                        <tr>
                                            <td>'.trans('all.teks').'</td>
                                            <td>&nbsp;:&nbsp;</td>
                                            <td style="padding-top:5px"><span style="padding:5px;background-color: #'.$key['warna_teks'].';color:'.Utils::getTextColor($key['warna_teks']).'">'.$key['warna_teks'].'</span></td>
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
        if (Utils::cekHakakses('pengaturan','um')) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $dataatribut = Utils::getData($pdo,'atribut','id,atribut','','atribut');
            $dataatributnilai = Utils::getAtributdanAtributNilaiCrud(0, 'tvgroup', false);
            $dataatributvariable = Utils::getData($pdo,'atributvariable','id,atribut','','atribut');
            Utils::insertLogUser('akses menu tambah tv group');
            return view('pengaturan/tvgroup/create', ['dataatribut' => $dataatribut, 'dataatributvariable' => $dataatributvariable, 'dataatributnilai' => $dataatributnilai, 'menu' => 'tv']);
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

            $sql = 'INSERT INTO tvgroup VALUES(NULL,:nama,:judul,:jenis,:baris1_label,:baris1_data,:baris2_label,:baris2_data,:baris3_label,:baris3_data,:warna_background,:warna_teks)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':judul', $request->judul);
            $stmt->bindValue(':jenis', $request->jenis);
            $stmt->bindValue(':baris1_label', $request->baris1_label);
            $stmt->bindValue(':baris1_data', $request->baris1_data);
            $stmt->bindValue(':baris2_label', $request->baris2_label);
            $stmt->bindValue(':baris2_data', $request->baris2_data);
            $stmt->bindValue(':baris3_label', $request->baris3_label);
            $stmt->bindValue(':baris3_data', $request->baris3_data);
            $stmt->bindValue(':warna_background', $request->warna_background);
            $stmt->bindValue(':warna_teks', $request->warna_teks);
            $stmt->execute();

            $id = $pdo->lastInsertId();

            if ($request->atribut != '') {
                for ($i = 0; $i < count($request->atribut); $i++) {
                    $sql = 'INSERT INTO tvgroupatribut VALUES(:idtvgroup,:idatributnilai)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idtvgroup', $id);
                    $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                    $stmt->execute();
                }
            }

            Utils::insertLogUser('Tambah tv group "'.$request->nama.'"');
    
            return redirect('pengaturan/tvgroup')->with('message', trans('all.databerhasildisimpan'));
        }else{
            return redirect('pengaturan/tvgroup/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if (Utils::cekHakakses('pengaturan','um')) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM tvgroup WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            $dataatribut = Utils::getData($pdo,'atribut','id,atribut','','atribut');
            $dataatributnilai = Utils::getAtributdanAtributNilaiCrud($id, 'tvgroup', false);
            $dataatributvariable = Utils::getData($pdo,'atributvariable','id,atribut','','atribut');

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah tv group');
            return view('pengaturan/tvgroup/edit', ['data' => $data, 'dataatribut' => $dataatribut, 'dataatributnilai' => $dataatributnilai, 'dataatributvariable' => $dataatributvariable, 'menu' => 'tv']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,nama FROM tvgroup WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //cek apakah kembar?
            $sql = 'SELECT id FROM tvgroup WHERE nama=:nama AND id<>:id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nama', $request->nama);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount()==0) {

                $sql = 'UPDATE tvgroup SET nama = :nama, judul = :judul, jenis = :jenis, baris1_label = :baris1_label, baris1_data = :baris1_data, baris2_label = :baris2_label, baris2_data = :baris2_data, baris3_label = :baris3_label, baris3_data = :baris3_data, warna_background = :warna_background, warna_teks = :warna_teks WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':judul', $request->judul);
                $stmt->bindValue(':jenis', $request->jenis);
                $stmt->bindValue(':baris1_label', $request->baris1_label);
                $stmt->bindValue(':baris1_data', $request->baris1_data);
                $stmt->bindValue(':baris2_label', $request->baris2_label);
                $stmt->bindValue(':baris2_data', $request->baris2_data);
                $stmt->bindValue(':baris3_label', $request->baris3_label);
                $stmt->bindValue(':baris3_data', $request->baris3_data);
                $stmt->bindValue(':warna_background', $request->warna_background);
                $stmt->bindValue(':warna_teks', $request->warna_teks);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::deleteData($pdo,'tvgroupatribut',$id,'idtvgroup');

                if ($request->atribut != '') {
                    for ($i = 0; $i < count($request->atribut); $i++) {
                        $sql = 'INSERT INTO tvgroupatribut VALUES(:idtvgroup,:idatributnilai)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idtvgroup', $id);
                        $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                        $stmt->execute();
                    }
                }

                Utils::insertLogUser('Ubah tv group "'.$row['nama'].'" => "'.$request->nama.'"');
    
                return redirect('pengaturan/tvgroup')->with('message', trans('all.databerhasildiubah'));
            }else{
                return redirect('pengaturan/tvgroup/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('pengaturan/tvgroup/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if (Utils::cekHakakses('pengaturan','um')) {
            //pastikan idtv ada
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,nama FROM tvgroup WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                Utils::deleteData($pdo,'tvgroup',$id);
                Utils::insertLogUser('Hapus tv group "'.$row['nama'].'"');
                return redirect('pengaturan/tvgroup')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('pengaturan/tvgroup')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if (Utils::cekHakakses('pengaturan','lum')) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.tvgroup'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.judul'))
                        ->setCellValue('C1', trans('all.baris'))
                        ->setCellValue('D1', trans('all.warna'))
                        ->setCellValue('E1', trans('all.atribut'));

            $sql = 'SELECT
                      tg.id,
                      tg.nama,
                      tg.judul,
                      tg.jenis,
                      tg.baris1_label,
                      tg.baris1_data,
                      tg.baris2_label,
                      tg.baris2_data,
                      tg.baris3_label,
                      tg.baris3_data,
                      tg.warna_background,
                      tg.warna_teks,
                      GROUP_CONCAT(an.nilai ORDER BY an.nilai SEPARATOR " ,") as atribut
                    FROM
                      tvgroup tg
                      LEFT JOIN tvgroupatribut tga ON tga.idtvgroup=tg.id
                      LEFT JOIN atributnilai an ON tga.idatributnilai=an.id
                    GROUP BY
                      tg.id
                    ORDER BY
                      tg.nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $judul = trans('all.judul').' : '.$row['judul'].'
'.trans('all.jenis').' : '.$row['jenis'];
                $baris = $row['baris1_label'].' : '.$row['baris1_data'].'
'.$row['baris2_label'].' : '.$row['baris2_data'].'
'.$row['baris3_label'].' : '.$row['baris3_data'];
                $warna = trans('all.latarbelakang').' : '.$row['warna_background'].'
'.trans('all.teks').' : '.$row['warna_teks'];
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $judul);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $baris);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $warna);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['atribut']);

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

            $arrWidth = array('', 25, 25, 35, 35, 50);
            for ($j = 1; $j <= 5; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor tv group');
            $arrWidth = array(25, 25, 35, 35, 50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.tvgroup'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}