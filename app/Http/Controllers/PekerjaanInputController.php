<?php
namespace App\Http\Controllers;

use Hamcrest\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use App\Utils;

class PekerjaanInputController extends Controller
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
        if(Utils::cekHakakses('pekerjaanuser','l')){
            $dataCD = Utils::customDashboard();
            Utils::insertLogUser('akses menu pekerjaan pengguna');
	        return view('pekerjaaninput/index', ['datacd' => $dataCD, 'menu' => 'beranda']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
	{
        if(Utils::cekHakakses('pekerjaanuser','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            $columns = array('','tanggal','pekerjaan','pegawai','keterangan');
            $table = '(SELECT
                            pi.id,
                            pi.idpekerjaankategori,
                            pi.iduser,
                            p.id as idpekerjaan,
                            p.nama as pekerjaan,
                            IFNULL(pg.nama,"") as pegawai,
                            pi.tanggal,
                            pi.keterangan
                        FROM
                            pekerjaaninput pi
                            LEFT JOIN pegawai pg ON pi.idpegawai=pg.id,
                            pekerjaankategori p
                        WHERE
                            pi.idpekerjaankategori = p.id) x';
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE iduser = :iduser '.$where;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalData = $row['total'];
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $orderColumn = $columns[$request->input('order.0.column')];
            $orderAction = $request->input('order.0.dir');
            $orderBy = $orderColumn.' '.$orderAction;

            if(!empty($request->input('search.value'))){
                $search = $request->input('search.value');
                $where .= Utils::searchDatatableQuery($columns);
                $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE iduser = :iduser '.$where;
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                for($i=0;$i<count($columns);$i++) {
                    if($columns[$i] != '') {
                        $stmt->bindValue(':' . $columns[$i], '%' . $search . '%');
                    }
                }
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalFiltered = $row['total'];
            }

            $sql = 'SELECT * FROM '.$table.' WHERE iduser = :iduser ' . $where . ' ORDER BY '.$orderBy.' LIMIT '.$limit. ' OFFSET '.$start;
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
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
                    if(Utils::cekHakakses('pekerjaanuser','um')){
                        $action .= Utils::tombolManipulasi('ubah','pekerjaaninput',$key['id']);
                    }
                    if(Utils::cekHakakses('pekerjaanuser','hm')){
                        $action .= Utils::tombolManipulasi('hapus','pekerjaaninput',$key['id']);
                    }
                    $tempdata['action'] = '<center>'.$action.'</center>';
                    for($i=1;$i<count($columns);$i++){
                        $tempdata[$columns[$i]] = htmlentities($key[$columns[$i]]);
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
        if(Utils::cekHakakses('pekerjaanuser','tem')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $batasanpekerjaankategori = Utils::getBatasanPekerjaanKategori(Session::get('iduser_perusahaan'));
            $wheredatapekerjaankategori = $batasanpekerjaankategori != '' ? ' AND id IN'.$batasanpekerjaankategori : '';
            $datapekerjaankategori = Utils::getData($pdo,'pekerjaankategori','id,nama','digunakan="y"'.$wheredatapekerjaankategori,'nama');
            Utils::insertLogUser('akses menu tambah pekerjaan pengguna');
            return view('pekerjaaninput/create', ['datapekerjaankategori' => $datapekerjaankategori, 'menu' => 'beranda']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        if(!Utils::cekDateTime($request->tanggal)){
            return redirect('pekerjaaninput')->with('message', trans('all.terjadigangguan'));
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM pekerjaaninput WHERE idpekerjaankategori = :idpekerjaankategori AND tanggal = STR_TO_DATE(:tanggal,"%d/%m/%Y") AND keterangan = :keterangan AND iduser = :iduser';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpekerjaankategori', $request->pekerjaankategori);
        $stmt->bindValue(':tanggal', $request->tanggal);
        $stmt->bindValue(':keterangan', $request->keterangan);
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            $idpegawai = explode(',', $request->pegawai);
            try {
                $pdo->beginTransaction();
                for ($i = 0; $i < count($idpegawai); $i++) {
                    $sql = 'INSERT INTO pekerjaaninput VALUES(NULL,:idpekerjaankategori,:iduser,:idpegawai,STR_TO_DATE(:tanggal,"%d/%m/%Y"),:keterangan,NOW(),NULL)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpekerjaankategori', $request->pekerjaankategori);
                    $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
                    $stmt->bindValue(':idpegawai', $idpegawai[$i]);
                    $stmt->bindValue(':tanggal', $request->tanggal);
                    $stmt->bindValue(':keterangan', $request->keterangan);
                    $stmt->execute();

                    $idpekerjaaninput = $pdo->lastInsertId();

                    if (isset($request->idpekerjaanitem) && isset($request->jumlahitem)) {
                        for ($j = 0; $j < count($request->idpekerjaanitem); $j++) {
                            if ($request->jumlahitem[$j] != '') {
                                $sql = 'INSERT INTO pekerjaaniteminput VALUES(NULL,:idpekerjaaninput,:idpekerjaanitem,:jumlah,NOW(),NULL)';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':idpekerjaaninput', $idpekerjaaninput);
                                $stmt->bindValue(':idpekerjaanitem', $request->idpekerjaanitem[$j]);
                                $stmt->bindValue(':jumlah', $request->jumlahitem[$j]);
                                $stmt->execute();
                            }
                        }
                    }
                }

                Utils::insertLogUser('Tambah pekerjaan pengguna "' . Utils::getDataSelected($pdo, 'nama', 'pekerjaankategori', $request->pekerjaankategori) . '"');
                $pdo->commit();

                return redirect('pekerjaaninput')->with('message', trans('all.databerhasildisimpan'));
            } catch(\Exception $e){
                $pdo->rollBack();
                return redirect('pekerjaaninput')->with('message', trans('all.terjadigangguan'));
//                return redirect('pekerjaaninput')->with('message', $e->getMessage());
            }
        }else{
            return redirect('pekerjaaninput/create')->with('message', trans('all.datasudahada'));
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('pekerjaanuser','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM pekerjaaninput WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            $sql = 'SELECT
                        pi.item,
                        pi.satuan,
                        pi.id as idpekerjaanitem,
                        IFNULL(pii.jumlah,"") as jumlah
                    FROM
                        pekerjaanitem pi,
                        pekerjaaniteminput pii
                    WHERE
                        pii.idpekerjaanitem=pi.id AND
                        pii.idpekerjaaninput = :id
                    ORDER BY
                        pi.urutan';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $datapekerjaaniteminput = $stmt->fetchAll(PDO::FETCH_OBJ);

            if(!$data){
                abort(404);
            }
            $batasanpekerjaankategori = Utils::getBatasanPekerjaanKategori(Session::get('iduser_perusahaan'));
            $wheredatapekerjaankategori = $batasanpekerjaankategori != '' ? ' AND id IN('.$batasanpekerjaankategori.')' : '';
            $datapekerjaankategori = Utils::getData($pdo,'pekerjaankategori','id,nama','digunakan="y"'.$wheredatapekerjaankategori,'nama');
            Utils::insertLogUser('akses menu ubah pekerjaan pengguna');
            return view('pekerjaaninput/edit', ['data' => $data, 'datapekerjaankategori' => $datapekerjaankategori, 'datapekerjaaniteminput' => $datapekerjaaniteminput, 'menu' => 'beranda']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        if(!Utils::cekDateTime($request->tanggal)){
            return redirect('pekerjaaninput/'.$id.'/edit')->with('message', trans('all.terjadigangguan'));
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id,idpekerjaankategori FROM pekerjaaninput WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //cek apakah kembar?
            $sql = 'SELECT id FROM pekerjaaninput WHERE idpekerjaankategori = :idpekerjaankategori AND tanggal = STR_TO_DATE(:tanggal,"%d/%m/%Y") AND keterangan = :keterangan AND iduser = :iduser AND id <> :id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpekerjaankategori', $request->pekerjaankategori);
            $stmt->bindValue(':tanggal', $request->tanggal);
            $stmt->bindValue(':keterangan', $request->keterangan);
            $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount()==0) {

                try {
                    $pdo->beginTransaction();

                    $sql = 'UPDATE pekerjaaninput SET idpekerjaankategori = :idpekerjaankategori, idpegawai = :idpegawai, tanggal= STR_TO_DATE(:tanggal,"%d/%m/%Y"), keterangan = :keterangan WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idpekerjaankategori', $request->pekerjaankategori);
                    $stmt->bindValue(':idpegawai', $request->pegawai);
                    $stmt->bindValue(':tanggal', $request->tanggal);
                    $stmt->bindValue(':keterangan', $request->keterangan);
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    //hapus data lama tabel pekerjaaniteminput
                    Utils::deleteData($pdo,'pekerjaaniteminput',$id,'idpekerjaaninput');

                    //simpan data baru ke tabel pekerjaaninput
                    if (isset($request->idpekerjaanitem) && isset($request->jumlahitem)) {
                        for ($i = 0; $i < count($request->idpekerjaanitem); $i++) {
                            if ($request->jumlahitem[$i] != '') {
                                $sql = 'INSERT INTO pekerjaaniteminput VALUES(NULL,:idpekerjaaninput,:idpekerjaanitem,:jumlah,NOW(),NULL)';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindValue(':idpekerjaaninput', $id);
                                $stmt->bindValue(':idpekerjaanitem', $request->idpekerjaanitem[$i]);
                                $stmt->bindValue(':jumlah', $request->jumlahitem[$i]);
                                $stmt->execute();
                            }
                        }
                    }

                    Utils::insertLogUser('Ubah pekerjaan pengguna "' . Utils::getDataSelected($pdo, 'nama', 'pekerjaankategori', $row['idpekerjaankategori']) . '" => "' . Utils::getDataSelected($pdo, 'nama', 'pekerjaankategori', $request->pekerjaankategori) . '"');
                    $pdo->commit();

                    return redirect('pekerjaaninput')->with('message', trans('all.databerhasildiubah'));
                } catch(\Exception $e){
                    $pdo->rollBack();
                    return redirect('pekerjaaninput')->with('message', trans('all.terjadigangguan'));
                }
            }else{
                return redirect('pekerjaaninput/'.$id.'/edit')->with('message', trans('all.datasudahada'));
            }
        }else{
            return redirect('pekerjaaninput/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('pekerjaanuser','hm')){
            //pastikan idpekerjaan ada
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT id,tanggal,idpekerjaankategori FROM pekerjaaninput WHERE id=:id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                Utils::deleteData($pdo,'pekerjaaninput',$id);
                Utils::insertLogUser('Hapus pekerjaan pengguna tanggal"'.Utils::tanggalCantik($row['tanggal']).'" pekerjaan "'.Utils::getDataSelected($pdo,'nama','pekerjaankategori',$row['idpekerjaankategori']).'"');
                return redirect('pekerjaaninput')->with('message', trans('all.databerhasildihapus'));
            }else{
                return redirect('pekerjaaninput')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('pekerjaanuser','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.pekerjaanuser'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.tanggal'))
                        ->setCellValue('B1', trans('all.pekerjaan'))
                        ->setCellValue('C1', trans('all.pegawai'))
                        ->setCellValue('D1', trans('all.keterangan'));

            $sql = 'SELECT
                        pi.id,
                        p.id as idpekerjaan,
                        p.nama as pekerjaan,
                        IFNULL(pg.nama,"") as pegawai,
                        (DATEDIFF(pi.tanggal,"1900-01-01")+2) as tanggal,
                        pi.keterangan
                    FROM
                        pekerjaaninput pi
                        LEFT JOIN pegawai pg ON pi.idpegawai=pg.id,
                        pekerjaankategori p
                    WHERE
                        pi.idpekerjaankategori = p.id AND
                        pi.iduser = :iduser
                    ORDER BY
                        pi.tanggal DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':iduser',Session::get('iduser_perusahaan'));
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['tanggal']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['pekerjaan']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['pegawai']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['keterangan']);

                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getNumberFormat()->setFormatCode('DD/MM/YYYY');

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor pekerjaan pengguna');
            $arrWidth = array(15, 25, 35, 50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.pekerjaanuser'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}