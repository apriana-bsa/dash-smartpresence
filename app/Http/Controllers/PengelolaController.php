<?php
namespace App\Http\Controllers;

use App\HakAkses;
use App\Pengelola;
use App\Utils;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_Writer_Excel2007;

class PengelolaController extends Controller
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
        if(Utils::cekHakakses('pengelola','l')){
            Utils::insertLogUser('akses menu pengelola');
            return view('pengelola/index', ['menu' => 'pengelola']);
        } else {
            return redirect('/');
        }
    }

    public function show(Request $request)
    {
        if(Utils::cekHakakses('pengelola','l')){
            $pdo = DB::getPdo();
            $where = ' AND 1=2';
            if (Session::has('conf_webperusahaan')) {
                $where = ' AND idperusahaan = :idperusahaan';
            }
            if(Utils::cekHakakses('pengelola','u') || Utils::cekHakakses('pengelola','h') || Utils::cekHakakses('pengelola','m')) {
                $columns = array('', 'nama', 'email', 'hakakses', 'status');
            }else{
                $columns = array('nama', 'email', 'hakakses', 'status');
            }
            $table = '(SELECT u.id,p.id as idpengelola,p.idperusahaan,u.nama,u.email,u.status,IFNULL(h.nama,"") as hakakses FROM `user` u LEFT JOIN pengelola p ON p.iduser=u.id LEFT JOIN hakakses h ON p.idhakakses=h.id) x';
//            $totalData = Utils::getDataCustomWhere($pdo,'user u LEFT JOIN pengelola p ON p.iduser=u.id LEFT JOIN hakakses h ON p.idhakakses=h.id', 'count(u.id)', ' 1=1 '.$where);
            $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE 1=1 '.$where;
            $stmt = $pdo->prepare($sql);
            if (Session::has('conf_webperusahaan')) {
                $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
            }
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
                $sql = 'SELECT COUNT(id) as total FROM '.$table.' WHERE 1=1 '.$where;
                $stmt = $pdo->prepare($sql);
                if (Session::has('conf_webperusahaan')) {
                    $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
                }
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
            if (Session::has('conf_webperusahaan')) {
                $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
            }
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
                    if(Utils::cekHakakses('pengelola','um')){
                        $action .= Utils::tombolManipulasi('ubah','pengelola',$key['idpengelola']);
                    }
                    if(Utils::cekHakakses('pengelola','hm')){
                        $action .= Utils::tombolManipulasi('hapus','pengelola',$key['idpengelola']);
                    }
                    $tempdata = array();
                    for($i=0;$i<count($columns);$i++){
                        if($columns[$i] == '') {
                            $tempdata['action'] = '<center>'.$action.'</center>';
                        }elseif($columns[$i] == 'status') {
                            $tempdata[$columns[$i]] = Utils::labelKolom($key[$columns[$i]] == 'a' ? 'aktif' : 'tidakaktif');
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

    public function edit($id)
    {
        if(Utils::cekHakakses('pengelola','um')){
            $hakaksess = HakAkses::select('id', 'nama')
                ->where('idperusahaan', Session::get('conf_webperusahaan'))
                ->get();

            $pengelola = Pengelola::find($id);

            if (!$pengelola) {
                abort(404);
            }
            Utils::insertLogUser('akses menu ubah pengelola');
            return view('pengelola/edit', ['hakaksess' => $hakaksess, 'pengelola' => $pengelola, 'menu' => 'pengelola']);
        } else {
            return redirect('/');
        }
    }

    public function update(Request $request, $id)
    {
        $pdo = DB::getPdo();
        $sql = 'SELECT id FROM hakakses WHERE id=:idhakakses AND idperusahaan=:idperusahaan LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idhakakses', $request->hakakses);
        $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $sql = 'UPDATE pengelola SET idhakakses = :idhakakses WHERE id = :idpengelola';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idhakakses', $request->hakakses);
            $stmt->bindValue(':idpengelola', $id);
            $stmt->execute();

            Utils::insertLogUser('Ubah pengelola');

            return redirect('pengelola')->with('message', trans('all.databerhasildiubah'));
        } else {
            return view('pengelola/' . $id . '/edit', ['message' => trans('all.datatidakditemukan'), 'menu' => 'pengelola']);
        }
    }

    public function destroy($id)
    {
        if(Utils::cekHakakses('pengelola','hm')){
            $pdo = DB::getPdo();
            $sql = 'SELECT idperusahaan FROM pengelola WHERE id=:idpengelola LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue('idpengelola', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $pengelola = Pengelola::find($id);
                $pengelola->delete();

                Utils::insertLogUser('Hapus pengelola');

                return redirect('pengelola')->with('message', trans('all.databerhasildihapus'));
            } else {
                return redirect('pengelola')->with('message', trans('all.datatidakditemukan'));
            }
        } else {
            return redirect('/');
        }
    }

    public function excel()
    {
        if(Utils::cekHakakses('pengelola','l')){
            $pdo = DB::getPdo();
            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.pengelola'));

            //set value kolom
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', trans('all.nama'))
                        ->setCellValue('B1', trans('all.email'))
                        ->setCellValue('C1', trans('all.hakakses'))
                        ->setCellValue('D1', trans('all.status'));

            $where = ' WHERE 1=2';
            if (Session::has('conf_webperusahaan')) {
                $where = ' WHERE p.idperusahaan = ' . Session::get('conf_webperusahaan');
            }
            $sql = 'SELECT
                    u.id,
                    p.id as idpengelola,
                    u.nama,
                    u.email,
                    IF(u.status="a","' . trans('all.aktif') . '",IF(u.status="tk","' . trans('all.tidakaktif') . '","' . trans('all.blokir') . '")) as status,
                    IFNULL(h.nama,"") as hakakses
                FROM
                    user u
                    LEFT JOIN pengelola p ON p.iduser=u.id
                    LEFT JOIN hakakses h ON p.idhakakses=h.id' . $where;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = 2;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['email']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['hakakses']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['status']);

                $i++;
            }

            $arrWidth = array('', 40, 30, 15, 12);
            for ($j = 1; $j <= 4; $j++) {
                $huruf = Utils::angkaToHuruf($j);
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
            }

            Utils::passwordExcel($objPHPExcel);
            Utils::insertLogUser('Ekspor pengelola');
            $arrWidth = array(40, 30, 15, 12);
            Utils::setHeaderStyleExcel($objPHPExcel,$arrWidth);
            Utils::setFileNameExcel(trans('all.pengelola'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
}