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

class konfirmasiFlagController extends Controller
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
	    if(Utils::cekHakakses('konfirmasi_flag','lum')){
	        $data = array();
	        $data['filter_status'] = '';
	        $data['filter_flag'] = '';
	        $keteranganfilter = '';
            Utils::insertLogUser('akses menu konfirmasi flag');
            if(Session::has('filter_konfirmasiflag_status')){
                $data['filter_status'] = Session::get('filter_konfirmasiflag_status');
                if(Session::get('filter_konfirmasiflag_status') != '') {
                    $status = Session::get('filter_konfirmasiflag_status') == 'c' ? trans('all.konfirmasi') : (Session::get('filter_konfirmasiflag_status') == 'a' ? trans('all.terima') : trans('all.tolak'));
                    $keteranganfilter .= trans('all.status') . ' : ' . $status;
                }
            }
            if(Session::has('filter_konfirmasiflag_flag')){
                $data['filter_flag'] = Session::get('filter_konfirmasiflag_flag');
                if(Session::get('filter_konfirmasiflag_flag') != '') {
                    $keteranganfilter .= ($keteranganfilter != '' ? ', ' : '').trans('all.flag') . ' : ' . trans('all.'.str_replace('-','',Session::get('filter_konfirmasiflag_flag')));
                }
            }
            $data['keteranganfilter'] = $keteranganfilter;
            return view('datainduk/absensi/konfirmasiflag/index', ['data' => $data, 'menu' => 'konfirmasiflag']);
        }else{
            return redirect('/');
        }
	}

	public function submitFilter(Request $request){
        if(isset($request->status)){
            Session::set('filter_konfirmasiflag_status',$request->status);
        }
        if(isset($request->flag)){
            Session::set('filter_konfirmasiflag_flag',$request->flag);
        }
        return redirect('datainduk/absensi/konfirmasiflag');
    }

	public function show(Request $request)
	{
        if(Utils::cekHakakses('konfirmasi_flag','lum')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $where .= ' AND idpegawai IN ' . $batasan;
            }
            if(Session::has('filter_konfirmasiflag_status') && Session::get('filter_konfirmasiflag_status') != ''){
                $where .= ' AND status = "'.Session::get('filter_konfirmasiflag_status').'"';
            }
            if(Session::has('filter_konfirmasiflag_flag') && Session::get('filter_konfirmasiflag_flag') != ''){
                $where .= ' AND flag = "'.Session::get('filter_konfirmasiflag_flag').'"';
            }
            if(Utils::cekHakakses('konfirmasi_flag','uhm')) {
                $columns = array('', '', 'waktu', 'nama', 'flag', 'status', 'keterangan', 'keterangankonfirmasi',);
            }else{
                $columns = array('waktu', 'nama', 'flag', 'status', 'keterangan', 'keterangankonfirmasi',);
            }
            $table = '(
                        SELECT
                            kl.id,
                            kl.idlogabsen,
                            kl.idpegawai,
                            pg.nama,
                            IFNULL(la.flag_keterangan,kl.keterangan) as flag_keterangan,
                            kl.keterangan,
                            kl.keterangankonfirmasi,
                            IFNULL(la.waktu,kl.waktu) as waktu,
                            kl.status,
                            kl.flag
                        FROM
                            konfirmasi_flag kl
                             LEFT JOIN logabsen la ON kl.idlogabsen=la.id,
                            pegawai pg
                        WHERE
                            kl.idpegawai=pg.id
                    ) x';
            $totalData = Utils::getDataCustomWhere($pdo,'konfirmasi_flag', 'count(id)',' 1=1 '.$where);
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
                    $cekkonfirmasi = '<input onclick="cekKonfirmasiTerpilih()" value="'.$key['id'].'" class=cek_konfirmasi type=checkbox id="'.$key['id'].'">';
                    $action = '<span style="cursor: pointer;" onclick="detailKonfirmasiAbsen('.$key['id'].',\'konfirmasiflag\',\'y\')"><i class="fa fa-pencil" style="color:#1ab394"></i></span>&nbsp;';
                    $tempdata['cekkonfirmasi'] = $cekkonfirmasi;
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'flag') {
                            $tempdata[$columns[$i]] = '<center><span class="label label-warning">'.trans('all.'.str_replace('-','',$key['flag'])).'</span></center>';
                        }elseif($columns[$i] == 'status') {
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]] == 'c' ? 'confirm' : ($key[$columns[$i]] == 'a' ? 'approve' : ($key[$columns[$i]] == 'na' ? 'notapprove' : $key[$columns[$i]])));
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

    // rawan ngebug
	public function setKonfirmasi($id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $msg = trans('all.datatidakditemukan');
        $sql = 'SELECT IFNULL(idlogabsen,"") as idlogabsen FROM konfirmasi_flag WHERE id=:id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount()==1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $idlogabsen = $row['idlogabsen'];
            $cekbolehubah = Utils::cekKunciDataPosting(Utils::getDataWhere($pdo,'logabsen','waktu','id',$idlogabsen));
            if($cekbolehubah == 0) {
                $sql1 = 'UPDATE konfirmasi_flag SET status = "c" WHERE id = :id';
                $stmt1 = $pdo->prepare($sql1);
                $stmt1->bindValue(':id', $id);
                $stmt1->execute();

                $sql2 = 'SELECT id FROM logabsen WHERE id = :id LIMIT 1';
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->bindValue(':id', $idlogabsen);
                $stmt2->execute();
                if ($stmt2->rowCount() == 1) {

                    $sql = 'UPDATE logabsen SET status = "c", flag = "", flag_keterangan = "" WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':id', $idlogabsen);
                    $stmt->execute();

                    // posting ulang
                    $sql = 'CALL hitungrekapabsen_log(:idlogabsen, NULL, NULL)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':idlogabsen', $idlogabsen);
                    $stmt->execute();
                }
                Utils::insertLogUser('Set konfirmasi flag');
                $msg = trans('all.setkonfirmasiberhasil');
            } else {
//                $msg = trans('all.datatidakbisadirubah').Utils::getDataWhere($pdo,'pengaturan','kuncidatasebelumtanggal');
                $msg = trans('all.datatidakbisadirubah');
            }
        }
        return redirect('datainduk/absensi/konfirmasiflag')->with('message', $msg);
    }

    public function konfirmasiDataTerpilih(Request $request){
        $idkonfirmasi = explode(',', $request->idkonfirmasi);
        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'unknown';
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek apakah benar id yg dikirimkan ada di tabel konfirmasi_flag
        $sql = 'SELECT waktu FROM konfirmasi_flag WHERE id IN('.$request->idkonfirmasi.') LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if($stmt->rowCount() == 1){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cekbolehubah = Utils::cekKunciDataPosting($row['waktu']);
            if($cekbolehubah == 0) {
                try {
                    $pdo->beginTransaction();
                    // update tabel konfirmasi_flag sesuai jenis yg dikirimkan
                    for ($i = 0; $i < count($idkonfirmasi); $i++) {
                        $sql1 = 'UPDATE konfirmasi_flag SET status = :status, keterangankonfirmasi = :keterangan WHERE id = :id';
                        $stmt1 = $pdo->prepare($sql1);
                        $stmt1->bindValue(':id', $idkonfirmasi[$i]);
                        $stmt1->bindValue(':status', $request->status);
                        $stmt1->bindValue(':keterangan', $request->keterangan);
                        $stmt1->execute();

                        //get idlogabsen dari tabel konfirmasi_flag
                        $idlogabsen = Utils::getDataWhere($pdo, 'konfirmasi_flag', 'idlogabsen', 'id', $idkonfirmasi[$i]);
                        if ($idlogabsen != '') {
                            $sql2 = 'SELECT id FROM logabsen WHERE id = :id LIMIT 1';
                            $stmt2 = $pdo->prepare($sql2);
                            $stmt2->bindValue(':id', $idlogabsen);
                            $stmt2->execute();
                            if ($stmt2->rowCount() == 1) {

//                                $sql = 'UPDATE logabsen SET status = :status, flag_keterangan = :keterangan WHERE id = :id';
//                                $stmt = $pdo->prepare($sql);
//                                $stmt->bindValue(':status', $request->status == 'a' ? 'v' : $request->status);
//                                $stmt->bindValue(':keterangan', $request->keterangan);
//                                $stmt->bindValue(':id', $idlogabsen);
//                                $stmt->execute();
                                $sql = 'UPDATE logabsen SET flag_keterangan = :keterangan WHERE id = :id';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':keterangan', $request->keterangan);
                                $stmt->bindValue(':id', $idlogabsen);
                                $stmt->execute();

                                // posting ulang
                                $sql = 'CALL hitungrekapabsen_log(:idlogabsen, NULL, NULL)';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':idlogabsen', $idlogabsen);
                                $stmt->execute();
                            }
                        } else {
                            // select tabel konfirmasi_flag untuk keperluan insert logabsen
                            $sql = 'SELECT flag, idpegawai, waktu, idalasanmasukkeluar FROM konfirmasi_flag WHERE id=:id';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':id', $idkonfirmasi[$i]);
                            $stmt->execute();
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $flag = $row['flag'];
                            $idpegawai = $row['idpegawai'];
                            $waktu = $row['waktu'];
                            $idalasanmasukkeluar = $row['idalasanmasukkeluar'];
                            $terhitungkerja = 'y';

                            //cek apakah terhitung kerja berdasarkan idalasanmasukkeluar
                            if ($idalasanmasukkeluar != null && $idalasanmasukkeluar != '' && $idalasanmasukkeluar > 0) {
                                $terhitungkerja = Utils::getDataWhere($pdo, 'alasanmasukkeluar', 'terhitungkerja', 'id', $idalasanmasukkeluar);
                                if ($terhitungkerja == '') {
                                    $terhitungkerja = 'y';
                                }
                            }

                            //cek waktu dan idpegawai dari tabel logabsen
                            $sql = 'SELECT id FROM logabsen WHERE idpegawai = :idpegawai AND waktu = :waktu';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idpegawai', $idpegawai);
                            $stmt->bindValue(':waktu', $waktu);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $rowLogAbsen = $stmt->fetch(PDO::FETCH_ASSOC);
                                //update idlogabsen di tabel konfirmasi flag
                                $sql = 'UPDATE konfirmasi_flag SET idlogabsen = :idlogabsen WHERE id = :id';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':idlogabsen', $rowLogAbsen['id']);
                                $stmt->bindValue(':id', $idkonfirmasi[$i]);
                                $stmt->execute();

                                //update status logabsen
                                $sql = 'UPDATE logabsen SET status = :status, flag_keterangan = :keterangan WHERE id = :id';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':status', $request->status == 'a' ? 'v' : $request->status);
                                $stmt->bindValue(':keterangan', $request->keterangan);
                                $stmt->bindValue(':id', $rowLogAbsen['id']);
                                $stmt->execute();
                            } else {
                                //tambahkan ke logabsen
                                $sql = 'INSERT INTO logabsen VALUES(NULL, :waktu, :idpegawai, NULL,  :masukkeluar, :idalasanmasukkeluar, :terhitungkerja, NULL, NULL, :status, NULL, NULL, NULL, "manual", "", :flag_keterangan, :dataasli, NOW(), NOW())';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':waktu', $waktu);
                                $stmt->bindValue(':idpegawai', $idpegawai);
                                $stmt->bindValue(':masukkeluar', $flag == 'lupaabsenmasuk' ? 'm' : 'k');
                                $stmt->bindValue(':idalasanmasukkeluar', $idalasanmasukkeluar);
                                $stmt->bindValue(':terhitungkerja', $terhitungkerja);
                                $stmt->bindValue(':status', $request->status == 'a' ? 'v' : $request->status);
                                $stmt->bindValue(':dataasli', 'dari pengajuan: ' . $flag);
                                $stmt->bindValue(':flag_keterangan', $request->keterangan);
                                $stmt->execute();
                            }
                        }
                    }
                    Utils::insertLogUser('Set konfirmasi flag data terpilih ke "' . $request->status . '"');
                    $response['status'] = 'ok';
                    $response['msg'] = '';
                    $pdo->commit();
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    $response['status'] = 'error';
                    $response['msg'] = $e->getMessage();
                    // return redirect('datainduk/absensi/logabsen/createbyatribut')->with('message', trans('all.terjadigangguan').$e->getMessage());
                }
            } else {
                $response['status'] = 'error';
                $response['msg'] = trans('all.datatidakbisadirubah').Utils::getDataWhere($pdo,'pengaturan','kuncidatasebelumtanggal');
            }
        }
        return $response;
    }

    public function excel()
    {
        if(Utils::cekHakakses('konfirmasi_flag','lum')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.konfirmasi_flag'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.waktu'))
                        ->setCellValue('B1', trans('all.pegawai'))
                        ->setCellValue('C1', trans('all.pengajuan'))
                        ->setCellValue('D1', trans('all.status'))
                        ->setCellValue('E1', trans('all.keteranganpengajuan'))
                        ->setCellValue('F1', trans('all.keterangankonfirmasi'));

            $where = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $where .= ' AND pg.id IN ' . $batasan;
            }
            if(Session::has('filter_konfirmasiflag_status') && Session::get('filter_konfirmasiflag_status') != ''){
                $where .= ' AND kl.status = "'.Session::get('filter_konfirmasiflag_status').'"';
            }
            if(Session::has('filter_konfirmasiflag_flag') && Session::get('filter_konfirmasiflag_flag') != ''){
                $where .= ' AND kl.flag = "'.Session::get('filter_konfirmasiflag_flag').'"';
            }

            $sql = 'SELECT 
						kl.id,
						pg.nama,
						la.flag_keterangan,
						(DATEDIFF(IFNULL(la.waktu,kl.waktu),"1900-01-01")+2)+ROUND(time_to_sec(timediff(DATE_FORMAT(IFNULL(la.waktu,kl.waktu),"%T"),"00:00:00"))/86400,9) as waktu,
						kl.status,
						kl.flag,
                        kl.keterangankonfirmasi
					FROM 
					    konfirmasi_flag kl
						LEFT JOIN logabsen la ON kl.idlogabsen=la.id,
						pegawai pg
					WHERE 
					    kl.idpegawai=pg.id
						'.$where.'
                    ORDER BY
                        la.waktu DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $status = '';
                if($row['status'] == 'c'){
                    $status = trans('all.konfirmasi');
                }else if($row['status'] == 'a'){
                    $status = trans('all.diterima');
                }else if($row['status'] == 'na'){
                    $status = trans('all.ditolak');
                }
                $pengajuan = trans('all.'.str_replace('-','',$row['flag']));
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['waktu']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $pengajuan);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $status);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['flag_keterangan']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['keterangankonfirmasi']);

                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getNumberFormat()->setFormatCode('DD/MM/YYYY HH:MM:SS');

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor konfirmasi flag');
            $arrWidth = array(25, 35, 20, 15, 100, 100);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.konfirmasi_flag'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}