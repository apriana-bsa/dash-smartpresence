<?php
namespace App\Http\Controllers;

use App\HariLibur;
use App\Atribut;
use App\AtributNilai;
use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class HariLiburController extends Controller
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
        if(Utils::cekHakakses('harilibur','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT DISTINCT(yyyy) as yyyy FROM ((SELECT DISTINCT(YEAR(tanggalawal)) as yyyy FROM harilibur) UNION (SELECT DISTINCT(YEAR(tanggalakhir)) as yyyy FROM harilibur)) x ORDER BY yyyy DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $tahun = $stmt->fetchAll(PDO::FETCH_OBJ);
            Utils::insertLogUser('akses menu hari libur');
            return view('datainduk/absensi/harilibur/index', ['tahun' => $tahun, 'menu' => 'harilibur']);
        }else{
            return redirect('/');
        }
	}

    public function submit(Request $request)
    {
        Session::set('harilibur_tahun',$request->tahun);
        if($request->tahun == ''){
            Session::forget('harilibur_tahun');
        }
        return redirect('datainduk/absensi/harilibur');
    }

	public function show(Request $request)
	{
        if(Utils::cekHakakses('harilibur','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Session::has('harilibur_tahun')){
                $where = ' AND DATE_FORMAT(tanggalawal, "%Y%") = '.Session::get('harilibur_tahun').' OR DATE_FORMAT(tanggalakhir, "%Y%") = '.Session::get('harilibur_tahun');
            }
            if(Utils::cekHakakses('harilibur','uhm')) {
                $columns = array('', 'tanggalawal', 'keterangan', 'nilai', 'agama');
            }else{
                $columns = array('tanggalawal', 'keterangan', 'nilai', 'agama');
            }
            $table = '(
                        SELECT
                            h.id,
                            h.tanggalawal,
                            h.tanggalakhir,
                            h.keterangan,
                            GROUP_CONCAT(DISTINCT(a.nilai) ORDER BY a.nilai SEPARATOR ", ") as nilai,
                            GROUP_CONCAT(DISTINCT(ag.agama) ORDER BY ag.urutan SEPARATOR ", ") as agama
                        FROM
                            harilibur h
                            LEFT JOIN hariliburatribut ha ON ha.idharilibur=h.id
                            LEFT JOIN atributnilai a ON ha.idatributnilai=a.id
                            LEFT JOIN hariliburagama hag ON hag.idharilibur=h.id
                            LEFT JOIN agama ag ON hag.idagama=ag.id
                        GROUP BY
                            h.id
                    ) x';
            $totalData = Utils::getDataCustomWhere($pdo,'harilibur', 'count(id)', ' 1=1 '.$where);
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
                    $action = '';
                    if(Utils::cekHakakses('harilibur','um')){
                        $action .= Utils::tombolManipulasi('ubah','harilibur',$key['id']);
                    }
                    if(Utils::cekHakakses('harilibur','hm')){
                        $action .= Utils::tombolManipulasi('hapus','harilibur',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'tanggalawal') {
                            $tempdata[$columns[$i]] = Utils::tanggalCantikDariSampai($key['tanggalawal'],$key['tanggalakhir']);
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
        if(Utils::cekHakakses('harilibur','tm')){
            $atributnilais = AtributNilai::select('atributnilai.id',DB::raw('atribut.id as idatribut'),'atributnilai.nilai','atribut.atribut')
                                            ->leftjoin('atribut', 'atributnilai.idatribut', '=', 'atribut.id')
                                            ->get();
            $atributs = Atribut::select('id','atribut')->get();

            $atribut = Utils::getAtributdanAtributNilaiCrud(0,'harilibur');

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $agama = Utils::getData($pdo,'agama','id,agama','','urutan');
            Utils::insertLogUser('akses menu tambah hari libur');
            return view('datainduk/absensi/harilibur/create', ['atribut' => $atribut, 'atributnilais' => $atributnilais, 'atributs' => $atributs, 'agama' => $agama, 'menu' => 'harilibur']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        if(!Utils::cekDateTime($request->tanggalawal) && !Utils::cekDateTime($request->tanggalakhir)){
            return redirect('datainduk/absensi/harilibur/create')->with('message', trans('all.terjadigangguan'));
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $pdo->beginTransaction();
        try
        {
            $sql = 'INSERT INTO harilibur VALUES(NULL,STR_TO_DATE(:tanggalawal,"%d/%m/%Y"),STR_TO_DATE(:tanggalakhir,"%d/%m/%Y"),:keterangan,NOW())';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggalawal', $request->tanggalawal);
            $stmt->bindValue(':tanggalakhir', $request->tanggalakhir);
            $stmt->bindValue(':keterangan', $request->keterangan);
            $stmt->execute();
    
            $idharilibur = $pdo->lastInsertId();
    
            if($request->atribut != ''){
                for($i=0;$i<count($request->atribut);$i++){
                    $sql = 'INSERT INTO hariliburatribut VALUES(NULL,:idharilibur,:idatributnilai)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idharilibur', $idharilibur);
                    $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                    $stmt->execute();
                }
            }
            
            if($request->agama != ''){
                for($i=0;$i<count($request->agama);$i++){
                    $sql = 'INSERT INTO hariliburagama VALUES(:idharilibur,:idagama,NOW())';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idharilibur', $idharilibur);
                    $stmt->bindValue(':idagama', $request->agama[$i]);
                    $stmt->execute();
                }
            }
    
            // posting absen
            $sql = 'CALL hitungrekapabsen_harilibur(:idharilibur, NULL, NULL)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idharilibur', $idharilibur);
            $stmt->execute();

            Utils::insertLogUser('Tambah hari libur "'.$request->keterangan.'"');
            
            $pdo->commit();
    
            return redirect('datainduk/absensi/harilibur')->with('message', trans('all.databerhasildisimpan'));
        } catch (\Exception $e) {
            $pdo->rollBack();
            return redirect('datainduk/absensi/harilibur/create')->with('message', $e->getMessage());
        }
    }
    
    public function edit($id)
    {
        if(Utils::cekHakakses('harilibur','um')){
            $harilibur = HariLibur::find($id);

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT 
                        at.id,
                        at.atribut,
                        COUNT(*)=SUM(IF(ISNULL(hla.id)=false,1,0)) as flag,
                        SUM(IF(ISNULL(hla.id)=false,1,0))>0 as pakaiheader
                    FROM 
                        atribut at,
                        atributnilai an
                        LEFT JOIN hariliburatribut hla ON hla.idatributnilai=an.id AND hla.idharilibur = :idharilibur
                    WHERE 
                        an.idatribut=at.id
                    GROUP BY
                        at.id
                    ORDER BY
                        at.atribut
                    ';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idharilibur', $id);
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
                            LEFT JOIN hariliburatribut pa ON pa.idatributnilai=an.id AND pa.idharilibur=:idharilibur
                        WHERE 
                            an.idatribut=:idatribut
                        ORDER BY
                            an.nilai
                        ';
                $stmt2 = $pdo->prepare($sql);
                $stmt2->bindValue(':idharilibur', $id);
                $stmt2->bindValue(':idatribut', $row->id);
                $stmt2->execute();            

                $row->atributnilai=$stmt2->fetchAll(PDO::FETCH_OBJ);
            }

            // ambil data agama
            $sql = 'SELECT 
                        a.id,
                        a.agama,
                        IF(ISNULL(pa.idagama),"0","1") as dipilih
                    FROM 
                        agama a 
                        LEFT JOIN hariliburagama pa ON pa.idagama=a.id AND pa.idharilibur=:idharilibur
                    ORDER BY
                        a.urutan';
            $stmt3 = $pdo->prepare($sql);
            $stmt3->bindValue(':idharilibur', $id);
            $stmt3->execute();
            $agama = $stmt3->fetchAll(PDO::FETCH_OBJ);

            //data agama yg terpilih
            $sql = 'SELECT idharilibur from hariliburagama WHERE idharilibur = :idharilibur';
            $stmt4 = $pdo->prepare($sql);
            $stmt4->bindValue(':idharilibur', $id);
            $stmt4->execute();
            $jumlahagamaterpilih = $stmt4->rowCount();

            if(!$harilibur){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah hari libur');
            $atribut = Utils::getAtributdanAtributNilaiCrud($id, 'harilibur');
            return view('datainduk/absensi/harilibur/edit', ['harilibur' => $harilibur, 'arratribut' => $atribut, 'atributs' => $atributs, 'agama' => $agama, 'jumlahagamaterpilih' => $jumlahagamaterpilih, 'menu' => 'harilibur']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        if(!Utils::cekDateTime($request->tanggalawal) && !Utils::cekDateTime($request->tanggalakhir)){
            return redirect('datainduk/absensi/harilibur/'.$id.'/edit')->with('message', trans('all.terjadigangguan'));
        }
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $pdo->beginTransaction();
        $sql = 'SELECT tanggalawal, tanggalakhir, keterangan FROM harilibur WHERE id=:idharilibur LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idharilibur', $id);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            try {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $posting_tanggalawal = $row['tanggalawal'];
                $posting_tanggalakhir = $row['tanggalakhir'];
    
                $sql = 'UPDATE harilibur SET tanggalawal = STR_TO_DATE(:tanggalawal,"%d/%m/%Y"), tanggalakhir = STR_TO_DATE(:tanggalakhir,"%d/%m/%Y"), keterangan = :keterangan WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':tanggalawal', $request->tanggalawal);
                $stmt->bindValue(':tanggalakhir', $request->tanggalakhir);
                $stmt->bindValue(':keterangan', $request->keterangan);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                Utils::deleteData($pdo,'hariliburatribut',$id,'idharilibur');
    
                if ($request->atribut != '') {
                    for ($i = 0; $i < count($request->atribut); $i++) {
                        $sql = 'INSERT IGNORE INTO hariliburatribut VALUES(NULL,:idharilibur,:idatributnilai)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idharilibur', $id);
                        $stmt->bindValue(':idatributnilai', $request->atribut[$i]);
                        $stmt->execute();
                    }
                }

                Utils::deleteData($pdo,'hariliburagama',$id,'idharilibur');

                if ($request->agama != '') {
                    for ($i = 0; $i < count($request->agama); $i++) {
                        $sql = 'INSERT IGNORE INTO hariliburagama VALUES(:idharilibur,:idagama,NOW())';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idharilibur', $id);
                        $stmt->bindValue(':idagama', $request->agama[$i]);
                        $stmt->execute();
                    }
                }
    
                // posting ulang untuk data sebelum diupdate
                $sql = 'CALL hitungrekapabsen_harilibur(NULL, :tanggalawal, :tanggalakhir)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':tanggalawal', $posting_tanggalawal);
                $stmt->bindValue(':tanggalakhir', $posting_tanggalakhir);
                $stmt->execute();
    
                // posting absen untuk data setelah diupdate
                $sql = 'CALL hitungrekapabsen_harilibur(:idharilibur, NULL, NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idharilibur', $id);
                $stmt->execute();

                Utils::insertLogUser('Ubah hari libur "'.$row['keterangan'].'" => "'.$request->keterangan.'"');
    
                $pdo->commit();
    
                return redirect('datainduk/absensi/harilibur')->with('message', trans('all.databerhasildiubah'));
            } catch (\Exception $e) {
                $pdo->rollBack();
                return redirect('datainduk/absensi/harilibur/'.$id.'/edit')->with('message', $e->getMessage());
            }
        }else{
            return redirect('datainduk/absensi/harilibur/'.$id.'/edit')->with('message', trans('all.datatidakditemukan'));
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('harilibur','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT tanggalawal, tanggalakhir,keterangan FROM harilibur WHERE id=:idharilibur LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idharilibur', $id);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $posting_tanggalawal = $row['tanggalawal'];
                $posting_tanggalakhir = $row['tanggalakhir'];
                
                HariLibur::find($id)->delete();
                Utils::insertLogUser('Hapus hari libur "'.$row['keterangan'].'"');
    
                // posting absen
                $sql = 'CALL hitungrekapabsen_harilibur(NULL, :tanggalawal, :tanggalakhir)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':tanggalawal', $posting_tanggalawal);
                $stmt->bindValue(':tanggalakhir', $posting_tanggalakhir);
                $stmt->execute();
    
                return redirect('datainduk/absensi/harilibur')->with('message', trans('all.databerhasildihapus'));
            }
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('harilibur','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.harilibur'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.tanggal'))
                        ->setCellValue('B1', trans('all.keterangan'))
                        ->setCellValue('C1', trans('all.atribut'));

            $where = '';
            if (Session::has('harilibur_tahun')) {
                $where = ' WHERE DATE_FORMAT(h.tanggalawal, "%Y%") = ' . Session::get('harilibur_tahun') . ' OR DATE_FORMAT(h.tanggalakhir, "%Y%") = ' . Session::get('harilibur_tahun');
            }

            $sql = 'SELECT
                        CONCAT(DATE_FORMAT(h.tanggalawal, "%d/%m/%Y"), " - ",DATE_FORMAT(h.tanggalakhir, "%d/%m/%Y")) as tanggal,
                        h.keterangan,
                        GROUP_CONCAT(a.nilai ORDER BY a.nilai SEPARATOR ", ") as nilai
                    FROM
                        harilibur h
                        LEFT JOIN hariliburatribut ha ON ha.idharilibur=h.id
                        LEFT JOIN atributnilai a ON ha.idatributnilai=a.id
                    ' . $where . '
                    GROUP BY
                        h.id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['tanggal']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['keterangan']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['nilai']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor harilibur');
            $arrWidth = array(25, 50, 50);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.harilibur'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}