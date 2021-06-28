<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use DB;
use PDO;
use App\Utils;

class PayrollPengaturanController extends Controller
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
        if(Utils::cekHakakses('payrollpengaturan','l')){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM payroll_pengaturan';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $data = '';
            if($stmt->rowCount() > 0){
                $data = $stmt->fetch(PDO::FETCH_OBJ);
            }

            $datakomponenmaster = '';
            $sql = 'SELECT id,nama,kode FROM payroll_komponen_master WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $data->komponenmaster_total);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $datakomponenmaster = $stmt->fetch(PDO::FETCH_OBJ);
            }
            Utils::insertLogUser('akses menu payroll pengaturan');
	        return view('datainduk/payroll/payrollpengaturan/index', ['data' => $data, 'datakomponenmaster' => $datakomponenmaster, 'menu' => 'payrollpengaturan']);
        }else{
            return redirect('/');
        }
    }

    public function submit(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'UPDATE payroll_pengaturan SET periode = :periode, pertanggal = :pertanggal, komponenmaster_total = :komponenmaster_total';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':periode', $request->periode);
        $stmt->bindValue(':pertanggal', $request->periode == 'bulanan' ? NULL : $request->pertanggal);
        $stmt->bindValue(':komponenmaster_total', $request->komponenmaster_total == '' ? NULL : $request->komponenmaster_total);
        $stmt->execute();

        //simpan template payroll jika ada
        if ($request->hasFile('templatepayroll')) {
            $templatepayroll = $request->file('templatepayroll');
            if($templatepayroll->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $templatepayroll->getMimeType() == 'application/vnd.ms-excel'){
                $path = Session::get('folderroot_perusahaan') . '/payroll/';
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                //hapus yang lama jika ada
                $filelama = Utils::getDataWhere($pdo,'payroll_pengaturan','templatepayroll');
                if ($filelama != '' && file_exists($path.$filelama)) {
                    unlink($path.$filelama);
                }

                //simpan file
                $format = $templatepayroll->getMimeType() == 'application/vnd.ms-excel' ? '.xls' : '.xlsx';
                $name = 'templatepayroll'.date('Ymd').$format;
                move_uploaded_file($templatepayroll, $path.$name);

                //update payroll_pengaturan
                $sql = 'UPDATE payroll_pengaturan SET templatepayroll = :templatepayroll';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':templatepayroll', $name);
                $stmt->execute();
            }
        }

        Utils::insertLogUser('Ubah pengaturan payroll');

        return redirect('datainduk/payroll/payrollpengaturan')->with('message', trans('all.databerhasildiubah'));
    }

    public function hapusFile(){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $path = Session::get('folderroot_perusahaan') . '/payroll/';
        $filelama = Utils::getDataWhere($pdo,'payroll_pengaturan','templatepayroll');
        if ($filelama != '' && file_exists($path.$filelama)) {
            unlink($path.$filelama);
        }
        //update payroll_pengaturan
        $sql = 'UPDATE payroll_pengaturan SET templatepayroll = ""';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        Utils::insertLogUser('Hapus file template payroll');
        return redirect('datainduk/payroll/payrollpengaturan')->with('message', trans('all.databerhasildihapus'));
    }
}