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

class customDashboardNodeController extends Controller
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
        if(Utils::cekHakakses('customdashboard','l')){
            Utils::insertLogUser('akses menu custom dsahboard node');
            return view('pengaturan/customdashboardnode/index', ['menu' => 'customdashboard']);
        }else{
            return redirect('/');
        }
	}

	public function show(Request $request)
    {
        if(Utils::cekHakakses('customdashboard','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $where = '';
            if(Utils::cekHakakses('customdashboard','uhm')) {
                $columns = array('', 'nama', 'judul', 'icon', 'warna', 'query_jenis', 'query_kehadiran', 'query_kehadiran_data', 'query_kehadiran_if', 'query_kehadiran_group', 'query_kehadiran_periode', 'query_master_data', 'query_master_if', 'query_master_group', 'query_master_periode', 'waktutampil', 'waktutampil_awal', 'waktutampil_akhir');
            }else{
                $columns = array('nama', 'judul', 'icon', 'warna', 'query_jenis', 'query_kehadiran', 'query_kehadiran_data', 'query_kehadiran_if', 'query_kehadiran_group', 'query_kehadiran_periode', 'query_master_data', 'query_master_if', 'query_master_group', 'query_master_periode', 'waktutampil', 'waktutampil_awal', 'waktutampil_akhir');
            }
            $table = 'customdashboard_node';
            $totalData = Utils::getDataCustomWhere($pdo,$table, 'count(id)', $where);
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

            $sql = 'SELECT
                        id,
                        nama,
                        judul,
                        icon,
                        warna,
                        query_jenis,
                        query_kehadiran,
                        query_kehadiran_data,
                        query_kehadiran_if,
                        query_kehadiran_group,
                        query_kehadiran_periode,
                        query_master_data,
                        query_master_if,
                        query_master_group,
                        query_master_periode,
                        waktutampil,
                        waktutampil_awal,
                        waktutampil_akhir
                    FROM
                        '.$table.'
                    WHERE
                        1=1
                        '.$where.'
                    ORDER BY
                        '.$orderBy.'
                    LIMIT
                        '.$limit.'
                    OFFSET
                        '.$start;
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
                    if(Utils::cekHakakses('customdashboard','um')){
                        $action .= Utils::tombolManipulasi('ubah','customdashboardnode',$key['id']);
                    }
                    if(Utils::cekHakakses('customdashboard','hm')){
                        $action .= Utils::tombolManipulasi('hapus','customdashboardnode',$key['id']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        $column = $columns[$i];
                        if($column == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($column == 'icon'){
                            $tempdata[$column] = $key[$column] != '' ? '<center><i class="fa '.$key[$column].'"></i></center>' : '';
                        }elseif($column == 'warna'){
                            $tempdata[$column] = $key[$column] != '' ? '<center><span class="simplecolorpicker icon" title="'.$key[$column].'" style="cursor:default;background-color: '.Utils::getWarnaHex($key[$column]).';"></span></center>' : '';
                        }elseif($column == 'query_jenis' || $column == 'query_kehadiran_data' || $column == 'query_kehadiran_group' || $column == 'query_kehadiran_periode' || $column == 'query_master_data' || $column == 'query_master_group' || $column == 'query_master_periode'){
                            $tempdata[$column] = $key[$column] != '' ? trans('all.'.$key[$column]) : '';
                        }else{
                            $tempdata[$column] = htmlentities($key[$column]);
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
        if(Utils::cekHakakses('customdashboard','tm')){
            Utils::insertLogUser('akses menu tambah custom dashboard node');
            return view('pengaturan/customdashboardnode/create', ['menu' => 'customdashboard']);
        }else{
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //return 'nama '.$request->nama.' judul: '.$request->judul.' icon: '.$request->icon.' warna: '.$request->warna.' query_jenis: '.$request->query_jenis.' query_kehadiran: '.$request->query_kehadiran.' query_kehadiran_data: '.$request->query_kehadiran_data.' query_kehadiran_if: '.$request->query_kehadiran_if.' query_kehadiran_group: '.$request->query_kehadiran_group.' query_kehadiran_periode: '.$request->query_kehadiran_periode.' query_master_data: '.$request->query_master_data.' query_master_if: '.$request->query_master_if.' query_master_group: '.$request->query_master_group.' query_master_periode: '.$request->query_master_periode.' waktutampil: '.$request->waktutampil.' waktutampil_awal: '.$request->waktutampil_awal.' waktutampil_akhir: '.$request->waktutampil_akhir;
        //cek apakah kembar?
        $cekadadata = Utils::getDataWhere($pdo,'customdashboard_node','id','nama',$request->nama);
        if($cekadadata == ''){
            try
            {
                //$pdo->beginTransaction();

                $sql = 'INSERT INTO customdashboard_node VALUES(NULL,:nama,:judul,:icon,:warna,:query_jenis,:query_kehadiran,:query_kehadiran_data,:query_kehadiran_if,:query_kehadiran_group,:query_kehadiran_periode,:query_master_data,:query_master_if,:query_master_group,:query_master_periode,:waktutampil,:waktutampil_awal,:waktutampil_akhir,NOW(),NULL)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama', $request->nama);
                $stmt->bindValue(':judul', $request->judul);
                $stmt->bindValue(':icon', $request->icon);
                $stmt->bindValue(':warna', $request->warna);
                $stmt->bindValue(':query_jenis', $request->query_jenis);
                $stmt->bindValue(':query_kehadiran', $request->query_kehadiran);
                $stmt->bindValue(':query_kehadiran_data', $request->query_kehadiran_data);
                $stmt->bindValue(':query_kehadiran_if', $request->query_kehadiran_if);
                $stmt->bindValue(':query_kehadiran_group', $request->query_kehadiran_group);
                $stmt->bindValue(':query_kehadiran_periode', $request->query_kehadiran_periode);
                $stmt->bindValue(':query_master_data', $request->query_master_data);
                $stmt->bindValue(':query_master_if', $request->query_master_if);
                $stmt->bindValue(':query_master_group', $request->query_master_group);
                $stmt->bindValue(':query_master_periode', $request->query_master_periode);
                $stmt->bindValue(':waktutampil', $request->waktutampil);
                $stmt->bindValue(':waktutampil_awal', $request->waktutampil_awal);
                $stmt->bindValue(':waktutampil_akhir', $request->waktutampil_akhir);
                $stmt->execute();

                Utils::insertLogUser('Tambah custom dashboard node "'.$request->nama.'"');

                //$pdo->commit();
                return redirect('pengaturan/customdashboardnode')->with('message', trans('all.databerhasildisimpan'));
            } catch (\Exception $e) {
                //$pdo->rollBack();
                return redirect('pengaturan/customdashboardnode/')->with('message', Utils::errHandlerMsg($e->getMessage()));
            }
        }else{
            return redirect('pengaturan/customdashboardnode/create')->with('message', trans('all.datasudahada'));
        }
    }

    public function edit($id)
    {
        if(Utils::cekHakakses('customdashboard','um')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM customdashboard_node WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            $warna = '';
            if($data->warna != ''){
                if($data->warna == 'soft read'){
                    $warna = '#EC644B';
                }else if($data->warna == 'chestnut'){
                    $warna = '#D24D57';
                }else if($data->warna == 'flamingo'){
                    $warna = '#EF4836';
                }else if($data->warna == 'tall poppy'){
                    $warna = '#C0392B';
                }else if($data->warna == 'razzmatazz'){
                    $warna = '#DB0A5B';
                }else if($data->warna == 'wax flower'){
                    $warna = '#F1A9A0';
                }else if($data->warna == 'cabaret'){
                    $warna = '#D2527F';
                }else if($data->warna == 'lavender'){
                    $warna = '#947CB0';
                }else if($data->warna == 'honey'){
                    $warna = '#674172';
                }else if($data->warna == 'wistful'){
                    $warna = '#AEA8D3';
                }else if($data->warna == 'medium'){
                    $warna = '#BF55EC';
                }else if($data->warna == 'wisteria'){
                    $warna = '#9B59B6';
                }else if($data->warna == 'sherpa'){
                    $warna = '#013243';
                }else if($data->warna == 'picton'){
                    $warna = '#59ABE3';
                }else if($data->warna == 'royal blue'){
                    $warna = '#4183D7';
                }else if($data->warna == 'alice blue'){
                    $warna = '#E4F1FE';
                }else if($data->warna == 'shakespear'){
                    $warna = '#52B3D9';
                }else if($data->warna == 'madison'){
                    $warna = '#2C3E50';
                }else if($data->warna == 'ming'){
                    $warna = '#336E7B';
                }else if($data->warna == 'chambray'){
                    $warna = '#3A539B';
                }else if($data->warna == 'jacksons'){
                    $warna = '#1F3A93';
                }else if($data->warna == 'fountain'){
                    $warna = '#5C97BF';
                }else if($data->warna == 'malachite'){
                    $warna = '#00E640';
                }else if($data->warna == 'summer'){
                    $warna = '#91B496';
                }else if($data->warna == 'aqua'){
                    $warna = '#A2DED0';
                }else if($data->warna == 'gossip'){
                    $warna = '#87D37C';
                }else if($data->warna == 'mountain'){
                    $warna = '#1BBC9B';
                }else if($data->warna == 'riptide'){
                    $warna = '#86E2D5';
                }else if($data->warna == 'shamrock'){
                    $warna = '#2ECC71';
                }else if($data->warna == 'confetty'){
                    $warna = '#E9D460';
                }else if($data->warna == 'jungle'){
                    $warna = '#26C281';
                }else if($data->warna == 'california'){
                    $warna = '#F89406';
                }else if($data->warna == 'casablanca'){
                    $warna = '#F4B350';
                }else if($data->warna == 'buttercup'){
                    $warna = '#F39C12';
                }else if($data->warna == 'jaffa'){
                    $warna = '#F27935';
                }else if($data->warna == 'lynch'){
                    $warna = '#6C7A89';
                }else if($data->warna == 'porcelain'){
                    $warna = '#ECF0F1';
                }else if($data->warna == 'silver'){
                    $warna = '#BFBFBF';
                }else if($data->warna == 'iron'){
                    $warna = '#DADFE1';
                }
            }

            if(!$data){
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah custom dashbaord node');
            return view('pengaturan/customdashboardnode/edit', ['data' => $data, 'warna' => $warna, 'menu' => 'customdashboard']);
        }else{
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $cekadadata = Utils::getDataWhere($pdo,'customdashboard_node','nama','id',$id);
        if($cekadadata != ''){
            //cek apakah kembar?
            $cekkembar = Utils::getData($pdo,'customdashboard_node','id','nama = "'.$request->nama.'" AND id<>'.$id.' LIMIT 1');
            if($cekkembar == ''){
                try
                {
                    //$pdo->beginTransaction();

                    // ubah data customdashboard
                    $sql = 'UPDATE customdashboard_node SET nama = :nama, judul = :judul, icon = :icon, warna = :warna, query_jenis = :query_jenis, query_kehadiran = :query_kehadiran, query_kehadiran_data = :query_kehadiran_data, query_kehadiran_if = :query_kehadiran_if, query_kehadiran_group = :query_kehadiran_group, query_kehadiran_periode = :query_kehadiran_periode, query_master_data = :query_master_data, query_master_if = :query_master_if, query_master_group = :query_master_group, query_master_periode = :query_master_periode, waktutampil = :waktutampil, waktutampil_awal = :waktutampil_awal, waktutampil_akhir = :waktutampil_akhir, updated = NOW() WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':nama', $request->nama);
                    $stmt->bindValue(':judul', $request->judul);
                    $stmt->bindValue(':icon', $request->icon);
                    $stmt->bindValue(':warna', $request->warna);
                    $stmt->bindValue(':query_jenis', $request->query_jenis);
                    $stmt->bindValue(':query_kehadiran', $request->query_kehadiran);
                    $stmt->bindValue(':query_kehadiran_data', $request->query_kehadiran_data);
                    $stmt->bindValue(':query_kehadiran_if', $request->query_kehadiran_if);
                    $stmt->bindValue(':query_kehadiran_group', $request->query_kehadiran_group);
                    $stmt->bindValue(':query_kehadiran_periode', $request->query_kehadiran_periode);
                    $stmt->bindValue(':query_master_data', $request->query_master_data);
                    $stmt->bindValue(':query_master_if', $request->query_master_if);
                    $stmt->bindValue(':query_master_group', $request->query_master_group);
                    $stmt->bindValue(':query_master_periode', $request->query_master_periode);
                    $stmt->bindValue(':waktutampil', $request->waktutampil);
                    $stmt->bindValue(':waktutampil_awal', ($request->waktutampil == 'y' ? $request->waktutampil_awal : ''));
                    $stmt->bindValue(':waktutampil_akhir', ($request->waktutampil == 'y' ? $request->waktutampil_akhir : ''));
                    $stmt->bindValue(':id', $id);
                    $stmt->execute();

                    Utils::insertLogUser('Ubah custom dashboard "'.$cekadadata.'" => "'.$request->nama.'"');

                    //$pdo->commit();
                    $msg = trans('all.databerhasildiubah');
                } catch (\Exception $e) {
                    //$pdo->rollBack();
                    $msg = Utils::errHandlerMsg($e->getMessage());
                }
                return redirect('pengaturan/customdashboardnode')->with('message', $msg);
            }else{
                $msg = trans('all.datasudahada');
            }
        }else{
            $msg = trans('all.datatidakditemukan');
        }
        return redirect('pengaturan/customdashboardnode/'.$id.'/edit')->with('message', $msg);
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('customdashboard','hm')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $cekadadata = Utils::getDataWhere($pdo,'customdashboard_node','nama','id',$id);
            if($cekadadata != ''){
                Utils::deleteData($pdo,'customdashboard_node',$id);
                Utils::insertLogUser('Hapus custom dashboard node "'.$cekadadata.'"');
                $msg = trans('all.databerhasildihapus');
            }else{
                $msg = trans('all.datatidakditemukan');
            }
            return redirect('pengaturan/customdashboardnode')->with('message', $msg);
        }else{
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('customdashboard','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $objPHPExcel = new PHPExcel();
            Utils::setPropertiesExcel($objPHPExcel,trans('all.node'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.judul'))
                        ->setCellValue('C1', trans('all.icon'))
                        ->setCellValue('D1', trans('all.warna'))
                        ->setCellValue('E1', trans('all.query_jenis'))
                        ->setCellValue('F1', trans('all.query_kehadiran'))
                        ->setCellValue('G1', trans('all.query_kehadiran_data'))
                        ->setCellValue('H1', trans('all.query_kehadiran_if'))
                        ->setCellValue('I1', trans('all.query_kehadiran_group'))
                        ->setCellValue('J1', trans('all.query_kehadiran_periode'))
                        ->setCellValue('K1', trans('all.query_master_data'))
                        ->setCellValue('L1', trans('all.query_master_if'))
                        ->setCellValue('M1', trans('all.query_master_group'))
                        ->setCellValue('N1', trans('all.query_master_periode'));

            $sql = 'SELECT
                        nama,
                        judul,
                        icon,
                        warna,
                        query_jenis,
                        query_kehadiran,
                        query_kehadiran_data,
                        query_kehadiran_if,
                        query_kehadiran_group,
                        query_kehadiran_periode,
                        query_master_data,
                        query_master_if,
                        query_master_group,
                        query_master_periode
                    FROM
                        customdashboard_node
                    ORDER BY
                        nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['judul']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['icon']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['warna']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['query_jenis']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['query_kehadiran']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['query_kehadiran_data']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $row['query_kehadiran_if']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $row['query_kehadiran_group']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $row['query_kehadiran_periode']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, $row['query_master_data']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, $row['query_master_if']);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, $row['query_master_group']);
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, $row['query_master_periode']);

                $i++;
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor custom dashboard node');
            $arrWidth = array(30, 30, 15, 10, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.customdashboard') .' ' . trans('all.node'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}