<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use App\Utils;

use Form;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;

class hapusDataController extends Controller
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
    public function indexPage($menu)
    {
        if(Utils::cekHakakses('hapusdata','i')){
            Utils::insertLogUser('akses menu hapus data '.$menu);
            return view('datainduk/lainlain/hapusdata/'.$menu.'/index', ['menu' => 'hapusdata']);
        }else{
            return redirect('/');
        }
    }

    public function data(Request $request, $menu)
    {
        if(Utils::cekHakakses('hapusdata','i')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = ' AND del = "y"';
            $limit = $request->input('length');
            $start = $request->input('start');
            $totalData = Utils::getDataCustomWhere($pdo, $menu, 'count(id)',' del="y"');
            $totalFiltered = $totalData;

            if($menu == 'pegawai') {
                $table = 'pegawai';
                $columns = array('','nama','nomorhp','status','del_waktu');
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

                $sql = 'SELECT p.id,p.nama,p.nomorhp,p.status,p.del_waktu,IFNULL(sg.idpegawai,"t") as adaslipgaji FROM pegawai p LEFT JOIN slipgaji_pegawai sg ON sg.idpegawai=p.id WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
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
                        $msg = trans('all.alerthapuspegawaiberelasi');
                        if($key['adaslipgaji'] == 't'){
                            $msg = trans('all.alerthapus');
                        }
                        $tempdata['action'] = '<center>
                                                    <a title="' . trans('all.batalkanhapus') . '" href="#" onclick="return batalHapus(\'pegawai\',' . $key['id'] . ')"><i class="fa fa-refresh" style="color:#1ab394"></i></a>&nbsp;&nbsp;
                                                    <a title="' . trans('all.hapus') . '" href="#" onclick="return hapusdata(\'pegawai\',' . $key['id'] . ',\''.$msg.'\')"><i class="fa fa-trash" style="color:#ed5565"></i></a>
                                                </center>';
                        for($i=1;$i<count($columns);$i++){
                            if($columns[$i] == 'status'){
                                $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]] == 'a' ? 'aktif' : 'tidakaktif');
                            }else{
                                $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                            }
                        }
                        $data[] = $tempdata;
                    }
                }
            }else{
                $table = '(
                            SELECT
                                m.id,
                                GROUP_CONCAT(a.nilai ORDER BY a.nilai SEPARATOR ", ") as atribut,
                                m.nama,
                                m.jenis,
                                m.deviceid,
                                m.deviceidreset,
                                m.cekjamserver,
                                m.utcdefault,
                                m.utc,
                                m.gcmid,
                                m.status,
                                m.lastsync,
                                m.del
                            FROM
                                mesin m
                                LEFT JOIN mesinatribut ma ON ma.idmesin=m.id
                                LEFT JOIN atributnilai a ON ma.idatributnilai=a.id
                            WHERE
                                del = "y"
                            GROUP BY
                                m.id
                          ) x';
                $columns = array('','nama','atribut','jenis','deviceid','cekjamserver','utc','lastsync','status');
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

                $sql = 'SELECT * FROM '.$table.' WHERE 1=1 '.$where.' ORDER BY '.$orderBy.' LIMIT '.$limit.' OFFSET '.$start;
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
                        $tempdata['action'] = '<center>
                                                    <a title="' . trans('all.batalkanhapus') . '" href="#" onclick="return batalHapus(\'mesin\',' . $key['id'] . ')"><i class="fa fa-refresh" style="color:#1ab394"></i></a>&nbsp;&nbsp;
                                                    <a title="' . trans('all.hapus') . '" href="#" onclick="return hapusdata(\'mesin\',' . $key['id'] . ')"><i class="fa fa-trash" style="color:#ed5565"></i></a>
                                                </center>';
                        for($i=1;$i<count($columns);$i++){
                            if($columns[$i] == 'status' || $columns[$i] == 'cekjamserver' || $columns[$i] == 'utcdefault'){
                                $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]]);
                            }elseif($columns[$i] == 'jenis'){
                                $tempdata[$columns[$i]] = trans('all.'.$key[$columns[$i]]);
                            }elseif($columns[$i] == 'jenis'){
                                $tempdata[$columns[$i]] = $key[$columns[$i]] != '' ? substr($key[$columns[$i]], 5) : '';
                            }else{
                                $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
                            }
                        }
                        $data[] = $tempdata;
                    }
                }
            }
            return Utils::jsonDatatable($request->input('draw'),$totalData,$totalFiltered,$data);
        }
        return '';
    }

    public function hapus($menu,$id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        if($menu == 'pegawai') {
            $where = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $where = ' AND id IN ' . $batasan;
            }

            try {
                $pdo->beginTransaction();

                // delete slip gaji bila ada
                Utils::deleteData($pdo,'slipgaji_pegawai',$id);

                //pastikan idpegawai ada
                $sql = 'SELECT id,nama FROM pegawai WHERE id=:idpegawai AND del = "y" ' . $where . ' LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $id);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    // hapus foto
                    $filename = Session::get('folderroot_perusahaan') . '/pegawai/' . Utils::id2Folder($id) . '/' . $id;
                    if (file_exists($filename)) {
                        unlink($filename);
                    }
                    if (file_exists($filename . '_thumb')) {
                        unlink($filename . '_thumb');
                    }

                    Utils::deleteData($pdo,'pegawai',$id);
                    Utils::insertLogUser('hapus pegawai permanen ' . $row['nama']);

                    $msg = trans('all.databerhasildihapus');
                } else {
                    $msg = trans('all.datatidakditemukan');
                }
                $pdo->commit();
            }catch (\Exception $e){
                $pdo->rollBack();
                $msg = $e->getMessage();
            }
            return redirect('datainduk/lainlain/hapusdata/pegawai')->with('message', $msg);
        }else{
            //pastikan idmesin ada
            $sql = 'SELECT id,nama FROM mesin WHERE id=:id AND del = "y" LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                Utils::deleteData($pdo,'mesin',$id);
                Utils::insertLogUser('hapus mesin permanen '.$row['nama']);
                $msg = trans('all.databerhasildihapus');
            } else {
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('datainduk/lainlain/hapusdata/mesin')->with('message', $msg);
        }
    }

    public function restore($menu,$id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();

        $sql = 'SELECT id FROM '.$menu.' WHERE del = "y" AND id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            if($menu == 'pegawai') {
                //cek perusahaankuota
                $limitpegawai = Utils::cekPegawaiJumlah(false);
                if ($limitpegawai == false) {
                    return redirect('datainduk/lainlain/hapusdata/'.$menu)->with('message', trans('all.jumlahpegawaimencapaibatasygdiijinkan'));
                }

                $sql = 'UPDATE pegawai SET del = "t", del_waktu = NULL WHERE id = :id';
            }else{
                $sql = 'UPDATE mesin SET del = "t" WHERE id = :id';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

            Utils::insertLogUser('restore '.$menu);

            $msg = trans('all.hapusdatadibatalkan');
        }else{
            $msg = trans('all.datatidakditemukan');
        }
        return redirect('datainduk/lainlain/hapusdata/'.$menu)->with('message', $msg);
    }

    public function excel($menu)
    {
        if(Utils::cekHakakses('hapusdata','i')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.hapusdata').' '.trans('all.'.$menu));

            if($menu == "pegawai") {
                //set value kolom
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A1', trans('all.nama'))
                            ->setCellValue('B1', trans('all.nomorhp'))
                            ->setCellValue('C1', trans('all.status'))
                            ->setCellValue('D1', trans('all.delwaktu'));

                $sql = 'SELECT
                            nama,
                            pin,
                            nomorhp,
                            IF(status="a","' . trans('all.aktif') . '","' . trans('all.tidakaktif') . '") as status,
                            (DATEDIFF(del_waktu,"1900-01-01")+2)+ROUND(time_to_sec(timediff(DATE_FORMAT(del_waktu,"%T"),"00:00:00"))/86400,9) as delwaktu
                        FROM
                            pegawai
                        WHERE
                            del = "y"
                        ORDER BY
                            nama';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $i = 2;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['nomorhp']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['status']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['delwaktu']);

                    // format
                    $objPHPExcel->getActiveSheet()->getStyle('D' . $i)->getNumberFormat()->setFormatCode('DD/MM/YYYY HH:MM:SS');

                    $i++;
                }
                $arrWidth = array(35, 20, 12, 25);
            }else{
                //set value kolom
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A1', trans('all.nama'))
                            ->setCellValue('B1', trans('all.atribut'))
                            ->setCellValue('C1', trans('all.jenis'))
                            ->setCellValue('D1', trans('all.deviceid'))
                            ->setCellValue('E1', trans('all.cekjamserver'))
                            ->setCellValue('F1', trans('all.utc'))
                            ->setCellValue('G1', trans('all.status'));

                $sql = 'SELECT
                            m.id,
                            GROUP_CONCAT(a.nilai ORDER BY a.nilai SEPARATOR ", ") as atribut,
                            m.nama,
                            m.jenis,
                            m.deviceid,
                            m.deviceidreset,
                            IF(m.cekjamserver="y","' . trans("all.ya") . '","' . trans("all.tidak") . '") as cekjamserver,
                            if(m.utcdefault = "y", "' . trans("all.default") . '",m.utc) as utcbaru,
                            IF(m.status="bs","' . trans("all.bebas") . '","' . trans("all.terhubung") . '") status
                        FROM
                            mesin m
                            LEFT JOIN mesinatribut ma ON ma.idmesin=m.id
                            LEFT JOIN atributnilai a ON ma.idatributnilai=a.id
                        WHERE
                            del = "y"
                        GROUP BY
                            m.id
                        ORDER BY
                            m.nama';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $i = 2;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['atribut']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, trans('all.'.$row['jenis']));
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['deviceid']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['cekjamserver']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['utcbaru']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['status']);

                    // center text
                    $objPHPExcel->getActiveSheet()->getStyle('E' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $i++;
                }

                $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $arrWidth = array(40, 100, 15, 15, 15, 12, 12);
            }

            Utils::insertLogUser('Ekspor hapus data '.$menu);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.hapusdata') . '_' . trans('all.'.$menu));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}