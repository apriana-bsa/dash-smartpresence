<?php
namespace App\Http\Controllers;

use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use DB;
use PDO;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Worksheet_MemoryDrawing;
use PHPExcel_Style_Fill;

class JadwalShiftController extends Controller
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

    public function getindex()
    {
        if(Utils::cekHakakses('jadwalshift','lum')){
            $bulan = date('m');
            $tahun = date('y');
            $keterangan = '';
            $data = array();
            $jamkerjashift = '';

            if(Session::has('jadwalshift_bulan')){
                $bulan = Session::get('jadwalshift_bulan');
            }
            if(Session::has('jadwalshift_tahun')){
                $tahun = Session::get('jadwalshift_tahun');
            }

            if(Session::has('jadwalshift_aftersave')){
                // hapus session
                Session::forget('jadwalshift_aftersave');
                $pdo = DB::connection('perusahaan_db')->getPdo();
                $this->querySubmit($pdo, $data);
                $bulan = Session::get('jadwalshift_bulan');
                $tahun = Session::get('jadwalshift_tahun');
                $tanggalawal = Session::get('jadwalshift_tanggalawal');
                $tanggalakhir = Session::get('jadwalshift_tanggalakhir');
                $filtermode = Session::get('jadwalshift_filtermode');
                $keterangan = $this->getinfo($bulan,$tahun,$tanggalawal,$tanggalakhir,$filtermode,Session::get('jadwalshift_atribut'));
                $jamkerjashift = $this->getJamKerjaShift();
            }

            $atributs = Utils::getAtributShift();
            $tahundropdown = Utils::tahunDropdown();

//            $currentdate = Utils::getCurrentDate();
            $dataharilibur = Utils::dataHariLiburBulanIni();
            $valuetglawalakhir = Utils::valueTanggalAwalAkhir();
            Utils::insertLogUser('akses menu jadwal shift');
            return view('datainduk/absensi/jadwalshift/index', ['valuetglawalakhir' => $valuetglawalakhir, 'jamkerjashift' => $jamkerjashift, 'dataharilibur' => $dataharilibur, 'tahundropdown' => $tahundropdown, 'atributs' => $atributs, 'data' => $data, "bulansekarang" => $bulan, "tahunsekarang" => $tahun, 'keterangan' => $keterangan, 'menu' => "jadwalshift"]);
        }else{
            return redirect("/");
        }
    }

    public function submit(Request $request)
    {
        if(!Utils::cekDateTime($request->tanggalawal) && !Utils::cekDateTime($request->tanggalakhir)){
            return redirect('datainduk/absensi/jadwalshift')->with('message', trans('all.terjadigangguan'));
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $bulan = $request->bulan;
        $tahun =$request->tahun;
        $tanggalawal = $request->tanggalawal;
        $tanggalakhir = $request->tanggalakhir;
        $filtermode = $request->filtermode;
        $atribut = $request->atributnilai;

        Session::set('jadwalshift_tanggalawal', $tanggalawal);
        Session::set('jadwalshift_tanggalakhir', $tanggalakhir);
        Session::set('jadwalshift_filtermode', $filtermode);
        Session::set('jadwalshift_bulan', $bulan);
        Session::set('jadwalshift_tahun', $tahun);
        Session::set('jadwalshift_atribut', $atribut);

        $keterangan = $this->getinfo($bulan,$tahun,$tanggalawal,$tanggalakhir,$filtermode,$atribut);

        $data = array();
        $this->querySubmit($pdo, $data);

        $jamkerjashift = $this->getJamKerjaShift();
        $atributs = Utils::getAtributShift();
        $tahundropdown = Utils::tahunDropdown();

//        $currentdate = Utils::getCurrentDate();
        $dataharilibur = Utils::dataHariLiburBulanIni();
        if(count($data) > 0){
            return view('datainduk/absensi/jadwalshift/index', ['jamkerjashift' => $jamkerjashift, 'tahundropdown' => $tahundropdown, 'atributs' => $atributs, 'data' => $data, 'dataharilibur' => $dataharilibur, "bulansekarang" => $bulan, "tahunsekarang" => $tahun, 'keterangan' => $keterangan, 'menu' => "jadwalshift"]);
        }else{
            return redirect('datainduk/absensi/jadwalshift')->with('message', trans('all.nodata'));
        }
    }

    public function querySubmit($pdo, &$data)
    {
        $tanggalawal = Session::get('jadwalshift_tanggalawal');
        $tanggalakhir = Session::get('jadwalshift_tanggalakhir');
        $filtermode = Session::get('jadwalshift_filtermode');
        $bulan = Session::get('jadwalshift_bulan');
        $tahun = Session::get('jadwalshift_tahun');

        if($filtermode == 'periode') {
            $sql = 'CALL pegawaishiftyymm(:yymm)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':yymm', $tahun . $bulan);
            $stmt->execute();
        }else{
            $sql = 'CALL pegawaishiftrangetanggal(:tanggalawal,:tanggalakhir)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggalawal', Utils::convertDmy2Ymd($tanggalawal));
            $stmt->bindValue(':tanggalakhir', Utils::convertDmy2Ymd($tanggalakhir));
            $stmt->execute();
        }

        $sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS temp_pegawai
                (
                    id      INT UNSIGNED NOT NULL,
                    nama    VARCHAR(100) NOT NULL,
                    pin    VARCHAR(8) NOT NULL,
                    PRIMARY KEY (id)
                ) Engine = Memory';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $sql = 'TRUNCATE temp_pegawai';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $where = '';
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $where = ' AND p.id IN '.$batasan;
        }

        if(Session::has('jadwalshift_atribut')){
            $atributs = Session::get('jadwalshift_atribut');
            $atributnilai = Utils::atributNilai($atributs);
            $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') )';
        }

        $sql = 'INSERT INTO temp_pegawai SELECT p.id,p.nama,p.pin FROM pegawai p, _pegawai _p WHERE p.id=_p.id AND p.del = "t" '.$where;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $sql = 'SELECT id,nama,pin FROM temp_pegawai ORDER BY nama';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $i = 0;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[$i] = array();
            $data[$i]['id'] = $row['id'];
            $data[$i]['nama'] = "<td class='nama'><span class=detailpegawai onclick=detailpegawai(".$row['id'].") style=cursor:pointer>".$row['nama']."</span></td>";
            $data[$i]['pin'] = "<td width='20px'>".$row['pin']."</td>";
            if(strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'm') !== false){
                $data[$i]['jadwalperbulan'] = '<td width="20px"><i style="cursor:pointer" class="fa fa-calendar-check-o" onclick="modalJadwalPerBulan('.$row['id'].')"></i></td>';
            }

            if($filtermode == 'periode') {
                $sqlPerTanggal = 'CALL pegawaishiftpertanggal(:idpegawai, :yymm)';
                $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
                $stmtPerTanggal->bindValue(':idpegawai', $row['id']);
                $stmtPerTanggal->bindValue(':yymm', $tahun . $bulan);
                $stmtPerTanggal->execute();
            }else{
                $sqlPerTanggal = 'CALL jadwalshiftrangetanggal(:idpegawai, :tanggalawal, :tanggalakhir)';
                $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
                $stmtPerTanggal->bindValue(':idpegawai', $row['id']);
                $stmtPerTanggal->bindValue(':tanggalawal', Utils::convertDmy2Ymd($tanggalawal));
                $stmtPerTanggal->bindValue(':tanggalakhir', Utils::convertDmy2Ymd($tanggalakhir));
                $stmtPerTanggal->execute();
            }

            $sqlPerTanggal = 'SELECT * FROM _jadwalshift ORDER BY tanggal ASC';
            $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
            $stmtPerTanggal->execute();

            while($rowPerTanggal = $stmtPerTanggal->fetch(PDO::FETCH_ASSOC)) {
                $tgl = date_format(date_create($rowPerTanggal['tanggal']),"j");
                $bln = date_format(date_create($rowPerTanggal['tanggal']),"m");
                $tglymd = date_format(date_create($rowPerTanggal['tanggal']),"Ymd");
                if($rowPerTanggal['harilibur'] == 'y'){
                    $cell = '<td style="width:20px;background-color: rgb(221, 107, 85);color:#fff;">';
                }else if($rowPerTanggal['dayinweek'] == 1){
                    $cell = '<td style="width:20px;background-color: #ddd;color:#fff;">';
                }else{
                    $cell = '<td style="width:20px;">';
                }
                $cell .= '<center>';
                if ($rowPerTanggal['idjamkerja']==null) {
                    //jika tidak ada idjamkerja alias libur
                    $cell .= '<i style="color:#c0c0c0" title="'.trans('all.tidakadajamkerja').'" class="fa fa-minus-circle"></i>';
                } else if($rowPerTanggal['jenis']=='full'){
                    if ($rowPerTanggal['alasantidakmasuk']!='') {
                        //jika ada ijin tidak masuk
                        $cell .= '<span style="border-radius:50%;cursor:default;padding:3px;background-color:#f8ac59;color:#fff !important" title="'.trans('all.ijintidakmasuk').'
'.trans('all.alasan').': '.$rowPerTanggal['alasantidakmasuk'].'
'.trans('all.keterangan').': '.$rowPerTanggal['keterangantidakmasuk'].'">'.Utils::getFirstCharInWord($rowPerTanggal['alasantidakmasuk']).'</span>';
                    } else {
                        //jika jamkerja full
                        //$cell = '<i style="color:#c0c0c0" title="'.trans('all.jamkerjafull').' : '.$rowPerTanggal['nama'].'" class="fa fa-minus"></i>';
                        $cell .= '';
                    }
                } else if ($rowPerTanggal['jenis']=='shift') {
                    //ambil data jadwal shift null
                    $sqlJamkerjaNull = 'SELECT idjamkerjashift FROM jadwalshift WHERE idpegawai=:idpegawai AND tanggal=:tanggal AND ISNULL(idjamkerjashift)=true';
                    $stmtJamkerjaNull = $pdo->prepare($sqlJamkerjaNull);
                    $stmtJamkerjaNull->bindValue(':idpegawai', $row['id']);
                    $stmtJamkerjaNull->bindValue(':tanggal', $rowPerTanggal['tanggal']);
                    $stmtJamkerjaNull->execute();

                    //buat detail popup nya
                    if(($rowPerTanggal['alasantidakmasuk']=='' || $stmtJamkerjaNull->rowCount()>0) && (strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'm') !== false)){
                        $cell .= '<a href="#" onclick="return detailjadwalshift('.$row['id'].',\''.$tglymd.'\')">';
                    }

                    if ($stmtJamkerjaNull->rowCount()>0) {
                        //icon silang
                        $cell .= '<i id="item_'.$row['id'].$tglymd.'" title="'.trans('all.libur').'" idjamkerja="'.$rowPerTanggal['idjamkerja'].'" style="padding:3px;color:red" class="fa fa-ban"></i>';
                    }
                    else if ($rowPerTanggal['alasantidakmasuk']!='') {
                        $sql99 = 'SELECT GROUP_CONCAT(CONCAT(jks.namashift,"(",jks.kode,")") SEPARATOR ", ") as jamkerja FROM jamkerjashift jks, jadwalshift js WHERE jks.id=js.idjamkerjashift AND js.idpegawai=:idpegawai AND js.tanggal=:tanggal ORDER BY jks.urutan, jks.namashift';
                        $stmt99 = $pdo->prepare($sql99);
                        $stmt99->bindParam(':idpegawai', $row['id']);
                        $stmt99->bindParam(':tanggal', $rowPerTanggal['tanggal']);
                        $stmt99->execute();
                        $jamkerja99 = trans('all.tidakadajamkerja');
                        if($stmt99->rowCount() > 0) {
                            $row99 = $stmt99->fetch(PDO::FETCH_ASSOC);
                            if($row99['jamkerja'] != '') {
                                $jamkerja99 = $row99['jamkerja'];
                            }
                        }
                        //jika ada ijin tidak masuk
                        $cell .= '<span style="border-radius:50%;cursor:default;padding:3px;background-color:#f8ac59;color:#fff !important" title="'.trans('all.ijintidakmasuk').'
'.trans('all.alasan').': '.$rowPerTanggal['alasantidakmasuk'].'
'.trans('all.keterangan').': '.$rowPerTanggal['keterangantidakmasuk'].'
'.trans('all.jamkerja').': '.$jamkerja99.'">'.Utils::getFirstCharInWord($rowPerTanggal['alasantidakmasuk']).'</span>';
                    }
                    else {
                        $sqlJamkerjaTerpilih = 'SELECT jks.namashift, jks.kode FROM jamkerjashift jks, jadwalshift js WHERE jks.id=js.idjamkerjashift AND js.idpegawai=:idpegawai AND js.tanggal=:tanggal ORDER BY jks.urutan, jks.namashift';
                        $stmtJamkerjaTerpilih = $pdo->prepare($sqlJamkerjaTerpilih);
                        $stmtJamkerjaTerpilih->bindParam(':idpegawai', $row['id']);
                        $stmtJamkerjaTerpilih->bindParam(':tanggal', $rowPerTanggal['tanggal']);
                        $stmtJamkerjaTerpilih->execute();
                        if ($stmtJamkerjaTerpilih->rowCount()==0) {
                            if(strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'm') !== false){
                                //icon tambah
                                $cell .= '<i id="item_'.$row['id'].$tglymd.'" style="color:#c0c0c0" title="'.trans('all.tambah').'" idjamkerja="'.$rowPerTanggal['idjamkerja'].'" class="fa fa-plus"></i>';
                            }else{
                                $cell .= '';
                            }
                        }
                        else {
                            //jika ada jamkerjashift nya, pake huruf pertama nama shift sebagai tampilan dan hover(title) untuk mengetahui nama shift nya
                            while ($rowJamKerjaTerpilih = $stmtJamkerjaTerpilih->fetch(PDO::FETCH_ASSOC)) {
                                $namashift = $rowJamKerjaTerpilih['namashift'];
                                $cell .= '<span id="item_'.$row['id'].$tglymd.'" title="'.$namashift.'" idjamkerja="'.$rowPerTanggal['idjamkerja'].'" style="padding:3px;background-color:' . Utils::getColorBackground($namashift) . ';color:' . Utils::getColorForeground($namashift) . ' !important" class="label">' . $rowJamKerjaTerpilih['kode'] . '</span>';
                            }
                        }
                    }
                    if(($rowPerTanggal['alasantidakmasuk']=='' || $stmtJamkerjaNull->rowCount()>0) && (strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'm') !== false)){
                        $cell .= '</a>';
                    }
                }
                $cell .= '</center>';
                $cell .= '</td>';
                $data[$i]['tgl'.$bln.$tgl] = $cell;
            }
            $i++;
        }
    }

    public function getJamKerjaShift($template = false)
    {
        // legend
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $where .= ' AND jk.id IN(SELECT idjamkerja FROM pegawaijamkerja WHERE idpegawai IN '.$batasan.')';
        }
        if(Session::has('jadwalshift_atribut')){
            $atributs = Session::get('jadwalshift_atribut');
            $atributnilai = Utils::atributNilai($atributs);
            $where .= ' AND jk.id IN (SELECT idjamkerja FROM pegawaijamkerja WHERE idpegawai IN(SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.')))';
        }
        //select jamkerja yg shift
        $sql = 'SELECT
                    jk.nama,
                    GROUP_CONCAT(jks.kode ORDER BY jks.namashift SEPARATOR "|") as kodeshift,
                    GROUP_CONCAT(DISTINCT jks.namashift ORDER BY jks.namashift SEPARATOR "|") as namashift
                FROM
                    jamkerja jk,
                    jamkerjashift jks
                WHERE
                    jk.jenis="shift" AND
                    jk.id=jks.idjamkerja
                    '.$where.'
                GROUP BY
                    jk.id
                ORDER BY
                    jks.urutan';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $jamkerjashift = array();
        $i=0;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $arrshift = explode('|', $row['namashift']);
            $arrkodeshift = explode('|', $row['kodeshift']);
            if($template == false){
                $jamkerjashift[$i] = '<i><b>'.$row['nama'].'</b></i> : ';
                for($j=0;$j<count($arrshift);$j++){
                    $jamkerjashift[$i] = $jamkerjashift[$i].'<span style="padding:3px;background-color:'.Utils::getColorBackground($arrshift[$j]).';color:'.Utils::getColorForeground($arrshift[$j]).' !important;" class="label">'.trim($arrkodeshift[$j]).'</span>&nbsp;'.$arrshift[$j].'&nbsp;&nbsp;';
                }
                $jamkerjashift[$i] = $jamkerjashift[$i].'<br>';
            }else{
                $jamkerjashift[$i]['nama'] = $row['nama'];
                $jamkerjashift[$i]['shift'] = array();
                for($j=0;$j<count($arrshift);$j++){
                    $jamkerjashift[$i]['shift'][$j]['kode'] = trim($arrkodeshift[$j]);
                    $jamkerjashift[$i]['shift'][$j]['nama'] = trim($arrshift[$j]);
                }
            }
            $i++;
        }
        return $jamkerjashift;
    }

    public function popupPerBulan($idpegawai, $fullscreen = 't')
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $tanggalawal = Session::get('jadwalshift_tanggalawal');
        $tanggalakhir = Session::get('jadwalshift_tanggalakhir');
        $filtermode = Session::get('jadwalshift_filtermode');
        $bulan = Session::get('jadwalshift_bulan');
        $tahun = Session::get('jadwalshift_tahun');
        $data = array();
        $legend_harilibur ='t';
        $legend_ijintidakmasuk ='t';
        $namapegawai = '';
        $pinpegawai = '';

        $where = '';
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $where = ' AND id IN '.$batasan;
        }

        $sql = 'SELECT
                    id,
                    nama,
                    pin
                FROM
                    pegawai
                WHERE
                    status="a" AND
                    id=:idpegawai
                    '.$where.'
                ORDER BY
                    nama ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idpegawai', $idpegawai);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $namapegawai = $row['nama'];
            $pinpegawai = $row['pin'];

            //siapkan data jadwal
            if($filtermode == 'periode') {
                $sqlPerTanggal = 'CALL pegawaishiftpertanggal(:idpegawai, :yymm)';
                $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
                $stmtPerTanggal->bindParam(':idpegawai', $idpegawai);
                $stmtPerTanggal->bindValue(':yymm', $tahun . $bulan);
                $stmtPerTanggal->execute();
            }else{
                $sqlPerTanggal = 'CALL jadwalshiftrangetanggal(:idpegawai, :tanggalawal, :tanggalakhir)';
                $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
                $stmtPerTanggal->bindParam(':idpegawai', $idpegawai);
                $stmtPerTanggal->bindValue(':tanggalawal', Utils::convertDmy2Ymd($tanggalawal));
                $stmtPerTanggal->bindValue(':tanggalakhir', Utils::convertDmy2Ymd($tanggalakhir));
                $stmtPerTanggal->execute();
            }

            $i = 0;
            //ambil data jadwal
            $sql = 'SELECT
                        j.tanggal,
                        DATE_FORMAT(j.tanggal,"%Y%m%d") as tanggalymd,
                        DAY(j.tanggal) as hanyatanggal,
                        j.dayinweek,
                        j.idjamkerja,
                        j.nama,
                        j.jenis,
                        j.harilibur,
                        j.idijintidakmasuk,
                        IFNULL(a.alasan,"") as ijintidakmasuk,
                        IFNULL(i.keterangan,"") as ijintidakmasukketerangan
                    FROM
                        _jadwalshift j
                        LEFT JOIN ijintidakmasuk i ON j.idijintidakmasuk=i.id
                        LEFT JOIN alasantidakmasuk a ON i.idalasantidakmasuk=a.id ORDER BY tanggal';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[$i]['tanggal'] = $row['tanggal'];
                $data[$i]['tanggalymd'] = $row['tanggalymd'];
                $data[$i]['hanyatanggal'] = $row['hanyatanggal'];
                $data[$i]['hari'] = Utils::getHari($row['dayinweek']);
                $data[$i]['dayinweek'] = $row['dayinweek'];
                $data[$i]['idjamkerja'] = $row['idjamkerja'];
                $data[$i]['nama'] = $row['nama'];
                $data[$i]['jenis'] = $row['jenis'];
                $data[$i]['harilibur'] = $row['harilibur'];
                $data[$i]['idijintidakmasuk'] = $row['idijintidakmasuk'];
                $data[$i]['ijintidakmasuk'] = $row['ijintidakmasuk'];
                $data[$i]['ijintidakmasukketerangan'] = $row['ijintidakmasukketerangan'];
                $data[$i]['tampilsemua'] = 'y';
                $data[$i]['shift'] = array();

                if ($row['harilibur']=='y') {
                    $legend_harilibur='y';
                }

                if ($row['idijintidakmasuk']!=null) {
                    $legend_ijintidakmasuk='y';
                }

                if ($row['idjamkerja'] != null && $row['jenis'] == 'shift') {
                    //buat data jadwal shift yang off
                    $data[$i]['shift'][0] = array();
                    $data[$i]['shift'][0]['idjamkerjashift'] = null;
                    $data[$i]['shift'][0]['namashift'] = '';
                    $data[$i]['shift'][0]['tampillibur'] = 'y';
                    $data[$i]['shift'][0]['tampilharian'] = 'y';

                    //ambil data jadwal shift null
                    $sqlJamkerjaNull = 'SELECT idjamkerjashift FROM jadwalshift WHERE idpegawai=:idpegawai AND tanggal=:tanggal AND ISNULL(idjamkerjashift)=true';
                    $stmtJamkerjaNull = $pdo->prepare($sqlJamkerjaNull);
                    $stmtJamkerjaNull->bindParam(':idpegawai', $idpegawai);
                    $stmtJamkerjaNull->bindParam(':tanggal', $row['tanggal']);
                    $stmtJamkerjaNull->execute();
                    if ($stmtJamkerjaNull->rowCount() > 0) {
                        $data[$i]['shift'][0]['dijadwalkan'] = '1';
                    } else {
                        $data[$i]['shift'][0]['dijadwalkan'] = '0';
                    }

                    //ambil data jadwal shift
                    $sqlJamkerja = 'SELECT
                                        jks.id,
                                        jks.namashift,
                                        IF(ISNULL(x.idjamkerjashift)=true,"0","1") as dijadwalkan,
                                        IF(jks._0_masuk="y", "y", "t") as tampillibur,
                                        IF(jks._'.$row['dayinweek'].'_masuk="y", "y", "t") as tampilharian
                                    FROM
                                        jamkerjashift jks
                                        LEFT JOIN (SELECT idjamkerjashift FROM jadwalshift WHERE idpegawai=:idpegawai AND tanggal=:tanggal) x ON x.idjamkerjashift=jks.id
                                    WHERE
                                        jks.idjamkerja=:idjamkerja AND
                                        (jks._'.$row['dayinweek'].'_masuk="y" OR jks._0_masuk="y") AND
                                        jks.digunakan="y"
                                    ORDER BY
                                        urutan';
                    $stmtJamkerja = $pdo->prepare($sqlJamkerja);
                    $stmtJamkerja->bindParam(':idpegawai', $idpegawai);
                    $stmtJamkerja->bindParam(':tanggal', $row['tanggal']);
                    $stmtJamkerja->bindParam(':idjamkerja', $row['idjamkerja']);
                    $stmtJamkerja->execute();
                    $j = 1;
                    while ($rowJamkerja = $stmtJamkerja->fetch(PDO::FETCH_ASSOC)) {
                        $data[$i]['shift'][$j] = array();
                        $data[$i]['shift'][$j]['idjamkerjashift'] = $rowJamkerja['id'];
                        $data[$i]['shift'][$j]['namashift'] = $rowJamkerja['namashift'];
                        $data[$i]['shift'][$j]['dijadwalkan'] = $rowJamkerja['dijadwalkan'];
                        $data[$i]['shift'][$j]['tampillibur'] = $rowJamkerja['tampillibur'];
                        $data[$i]['shift'][$j]['tampilharian'] = $rowJamkerja['tampilharian'];
                        $j++;
                    }
                }

                // cek apakah ada yg tidak tampil?
                for($k=0;$k<count($data[$i]['shift']);$k++) {
                    if (($data[$i]['harilibur']=='y' && $data[$i]['shift'][$k]['tampillibur']=='y') ||
                        ($data[$i]['harilibur']=='t' && $data[$i]['shift'][$k]['tampilharian']=='y') ||
                        ($data[$i]['shift'][$k]['dijadwalkan']=='1')) {

                    }
                    else {
                        $data[$i]['tampilsemua'] = 't';
                        break;
                    }
                }
                $i++;
            }
        }

        if($filtermode == 'periode') {
            if($bulan[0] == 0){
                $bulan = str_replace('0', '',$bulan);
            }
            $keterangan = ' '.Utils::getBulan($bulan).' 20'.$tahun;
        }else{
            $tanggalawal = Utils::convertDmy2Ymd(Session::get('jadwalshift_tanggalawal'));
            $tanggalakhir = Utils::convertDmy2Ymd(Session::get('jadwalshift_tanggalakhir'));
            $keterangan = Utils::tanggalCantikDariSampai($tanggalawal,$tanggalakhir);
        }
        $currentdate = Utils::getCurrentDate();
        // dapatkan daftar harilibur
        $sql = 'SELECT 
                    hl.id as idharilibur,
                    hl.tanggalawal,
                    hl.tanggalakhir,
                    hl.keterangan
                FROM 
                    harilibur hl
                WHERE 
                    (
                        DATE_FORMAT(hl.tanggalawal, "%m/%Y") = DATE_FORMAT(:currentdate1,"%m/%Y") OR
                        DATE_FORMAT(hl.tanggalakhir, "%m/%Y") = DATE_FORMAT(:currentdate2,"%m/%Y")
                    )
                ORDER BY 
                    hl.tanggalawal ASC
                LIMIT 3';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':currentdate1', $currentdate);
        $stmt->bindValue(':currentdate2', $currentdate);
        $stmt->execute();
        $dataharilibur = $stmt->fetchAll(PDO::FETCH_OBJ);
        if($fullscreen == 't') {
            return view('datainduk/absensi/jadwalshift/detailperbulan', ['data' => $data, 'dataharilibur' => $dataharilibur, 'idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'pinpegawai' => $pinpegawai, 'legend_harilibur' => $legend_harilibur, 'legend_ijintidakmasuk' => $legend_ijintidakmasuk, 'keterangan' => $keterangan]);
        }else{
            return view('datainduk/absensi/jadwalshift/detailperbulanfullscreen', ['data' => $data, 'idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'pinpegawai' => $pinpegawai, 'legend_harilibur' => $legend_harilibur, 'legend_ijintidakmasuk' => $legend_ijintidakmasuk, 'keterangan' => $keterangan, 'menu' => "jadwalshift"]);
        }
    }

    // submit jadwalshift perbulan
    public function popupPerBulanSubmit(Request $request, $idpegawai)
    {
        $tanggalawal = Session::get('jadwalshift_tanggalawal');
        $filtermode = Session::get('jadwalshift_filtermode');
        $bulan = Session::get('jadwalshift_bulan');
        $tahun = Session::get('jadwalshift_tahun');
        if($filtermode == 'periode') {
            for ($i = 1; $i <= $request->totalhari; $i++) {
                $tgl = $tahun . $bulan . $i;
                $tanggal = '20'.$tahun.$bulan.str_pad($i,2,'0',STR_PAD_LEFT);
                // hapus data lama
                Utils::hapusJadwalShift($idpegawai, $tgl);
                if($request->exists('jadwalshift_' . $tanggal)){
                    for ($j = 0; $j < count($request->input('jadwalshift_' . $tanggal)); $j++) {
                        // simpan data baru
                        $this->saveJadwalShift($idpegawai, $request->input('jadwalshift_' . $tanggal)[$j], $tgl);
                    }
                }
            }
        }else{
            // pertanggal
            $tanggalawal_str = strtotime(Utils::convertDmy2Ymd($tanggalawal));
            for ($i = 0; $i < $request->totalhari; $i++) {
                $tgl = date('ymj', strtotime(date('Y-m-d',$tanggalawal_str) . ' +'.$i.' day'));
                $tanggal = date('Ymd', strtotime(date('Y-m-d',$tanggalawal_str) . ' +'.$i.' day'));
                // hapus data lama
                Utils::hapusJadwalShift($idpegawai, $tgl);
                if($request->exists('jadwalshift_' . $tanggal)) {
                    for ($j = 0; $j < count($request->input('jadwalshift_' . $tanggal)); $j++) {
                        // simpan data baru
                        $this->saveJadwalShift($idpegawai, $request->input('jadwalshift_' . $tanggal)[$j], $tgl);
                    }
                }
            }
        }
        Session::set('jadwalshift_aftersave', 'iya');

        if($filtermode == 'periode') {
            $arrbulan = array('', trans('all.januari'), trans('all.februari'), trans('all.maret'), trans('all.april'), trans('all.mei'), trans('all.juni'), trans('all.juli'), trans('all.agustus'), trans('all.september'), trans('all.oktober'), trans('all.november'), trans('all.desember'));
            if($bulan[0] == 0){
                $bulan = str_replace('0', '',$bulan);
            }
            $keterangan = 'Ubah jadwal shift periode '.$arrbulan[$bulan].' 20'.$tahun;
        }else{
            $tanggalawal = Utils::convertDmy2Ymd(Session::get('jadwalshift_tanggalawal'));
            $tanggalakhir = Utils::convertDmy2Ymd(Session::get('jadwalshift_tanggalakhir'));
            $keterangan = 'Ubah jadwal shift pertanggal '.Utils::tanggalCantikDariSampai($tanggalawal,$tanggalakhir);
        }
        Utils::insertLogUser($keterangan);

        return redirect('datainduk/absensi/jadwalshift')->with('message', trans('all.databerhasildiubah'));
    }

    public function detailPopup($idpegawai, $tanggal, $idjamkerja) {
        if(strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'm') !== false){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            // nama pegawai
            $sql = 'SELECT
                        CONCAT(p.nama," (' . trans('all.pin') . ' : ",p.pin,")") as nama,
                        DAYOFWEEK(STR_TO_DATE(:tanggal,"%Y%m%d")) as hari,
                        j.nama as jamkerja,
                        j.jenis
                    FROM
                        pegawai p,
                        pegawaijamkerja pj,
                        jamkerja j
                    WHERE
                        p.id=pj.idpegawai AND
                        j.id = pj.idjamkerja AND
                        p.del = "t" AND
                        p.id = :idpegawai AND
                        j.id = :idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal', $tanggal);
            $stmt->bindValue(':idpegawai', $idpegawai);
            $stmt->bindValue(':idjamkerja', $idjamkerja);
            $stmt->execute();
            $pegawai = $stmt->fetch(PDO::FETCH_OBJ);

            $jadwalshift = array();

            if ($pegawai->jenis == 'shift') {
                $jadwalshift[0]['idjamkerjashift'] = null;
                $jadwalshift[0]['namashift'] = '';

                //ambil data jadwal shift null
                $sqlJamkerjaNull = 'SELECT idjamkerjashift FROM jadwalshift WHERE idpegawai=:idpegawai AND tanggal=STR_TO_DATE(:tanggal,"%Y%m%d") AND ISNULL(idjamkerjashift)=true';
                $stmtJamkerjaNull = $pdo->prepare($sqlJamkerjaNull);
                $stmtJamkerjaNull->bindValue(':idpegawai', $idpegawai);
                $stmtJamkerjaNull->bindValue(':tanggal', $tanggal);
                $stmtJamkerjaNull->execute();
                if ($stmtJamkerjaNull->rowCount() > 0) {
                    $jadwalshift[0]['dijadwalkan'] = '1';
                } else {
                    $jadwalshift[0]['dijadwalkan'] = '0';
                }

                //ambil data jadwal shift
                $sqlJamkerja = 'SELECT
                                    jks.id,
                                    jks.namashift,
                                    IF(ISNULL(x.idjamkerjashift)=true,"0","1") as dijadwalkan
                                FROM
                                    jamkerjashift jks
                                    LEFT JOIN (SELECT idjamkerjashift FROM jadwalshift WHERE idpegawai=:idpegawai AND tanggal=STR_TO_DATE(:tanggal,"%Y%m%d")) x ON x.idjamkerjashift=jks.id
                                WHERE
                                    jks.idjamkerja=:idjamkerja AND
                                    jks._' . $pegawai->hari . '_masuk="y" AND
                                    jks.digunakan="y"
                                ORDER BY
                                    urutan ASC';
                $stmtJamkerja = $pdo->prepare($sqlJamkerja);
                $stmtJamkerja->bindValue(':idpegawai', $idpegawai);
                $stmtJamkerja->bindValue(':tanggal', $tanggal);
                $stmtJamkerja->bindValue(':idjamkerja', $idjamkerja);
                $stmtJamkerja->execute();
                $j = 1;
                while ($rowJamkerja = $stmtJamkerja->fetch(PDO::FETCH_ASSOC)) {
                    $jadwalshift[$j] = array();
                    $jadwalshift[$j]['idjamkerjashift'] = $rowJamkerja['id'];
                    $jadwalshift[$j]['namashift'] = $rowJamkerja['namashift'];
                    $jadwalshift[$j]['dijadwalkan'] = $rowJamkerja['dijadwalkan'];
                    $j++;
                }
            }
            $tanggallengkap = Utils::getHari($pegawai->hari) . ', ' . substr($tanggal, -2) . ' ' . Utils::getBulan(substr($tanggal, 4 , 2)) . substr($tanggal, 0 , 4);
            return view('datainduk/absensi/jadwalshift/detail', ['idpegawai' => $idpegawai, 'pegawai' => $pegawai->nama, 'jamkerja' => $pegawai->jamkerja, 'jadwalshift' => $jadwalshift, 'tanggal' => $tanggal, 'tanggallengkap' => $tanggallengkap]);
        }
    }

    // submit jadwalshift pertanggal
    public function submitDetailPopup(Request $request)
    {
        $tanggal = date('ymj', strtotime($request->tanggal));
        $idpegawai = $request->idpegawai;
        // hapus data lama
        Utils::hapusJadwalShift($idpegawai, $tanggal);
        //jika ada jadwalshift
        if (isset($request->jadwalshift)) {
            for ($i = 0; $i < count($request->jadwalshift); $i++) {
                // simpan data baru
                $this->saveJadwalShift($idpegawai,$request->jadwalshift[$i],$tanggal);
            }
        }
        Utils::insertLogUser('Ubah jadwal shift "'.Utils::tanggalCantik($request->tanggal).'"');
        Session::set('jadwalshift_aftersave', 'iya');
        return redirect('datainduk/absensi/jadwalshift')->with('message', trans('all.databerhasildiubah'));
    }

    // sewaktu nyimpen ke tabel jadwalshift, ganti pakai fungsi ini
    public function saveJadwalShift($idpegawai,$idjamkerjashift,$tanggal){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'INSERT INTO jadwalshift VALUES(NULL,STR_TO_DATE(:tanggal, "%y%m%e"),:idpegawai,:idjamkerjashift,NOW())';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggal', $tanggal);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':idjamkerjashift', $idjamkerjashift == '' ? NULL : $idjamkerjashift);
        $stmt->execute();
    }

    // untuk informasi ex: periode januari 2019 / 1 - 31 januari 2019
    public function getinfo($bulan,$tahun,$tanggalawal,$tanggalakhir,$filtermode,$atribut){
        $atributnilaiketerangan = '';
        if($atribut != '') {
            $atributnilai = Utils::atributNilai($atribut);
            if ($atributnilai != '') {
                $atributnilaidipilih = Utils::getAtributSelected($atributnilai);
                $atributnilaiketerangan = Utils::atributNilaiKeterangan($atributnilaidipilih);
            }
        }
        if($filtermode == 'periode') {
            if ($bulan[0] == 0) {
                $bulan = str_replace('0', '', $bulan);
            }
            $keterangan = trans('all.periode').' : '.Utils::getBulan($bulan).' 20'.$tahun.' '.$atributnilaiketerangan;
        }else{
            $keterangan = Utils::tanggalCantikDariSampai(Utils::convertDmy2Ymd($tanggalawal),Utils::convertDmy2Ymd($tanggalakhir)).' '.$atributnilaiketerangan;
        }
        return $keterangan;
    }

    public function excel()
    {
        if(strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'l') !== false){
            if (Session::get('perusahaan_expired') == 'ya') {
                return '';
            }

            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.jadwalshift'));

            //set css kolom
            $styleArray = array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
            );

            $whiteText = array(
                'font'  => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            );

            $blackText = array(
                'font'  => array(
                    'color' => array('rgb' => '000000'),
                )
            );

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT * FROM parameterekspor';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rowPE['logokiri'] == '' AND $rowPE['logokanan'] == '' AND $rowPE['header_1_teks'] == '' AND $rowPE['header_2_teks'] == '' AND $rowPE['header_3_teks'] == '' AND $rowPE['header_4_teks'] == '' AND $rowPE['header_5_teks'] == '') {
                $b = 1; //b = baris
            } else {
                $b = 6;
            }

            $b = $b + 1;

            //tempat proses data
            $tanggalawal = Utils::convertDmy2Ymd(Session::get('jadwalshift_tanggalawal'));
            $tanggalakhir = Utils::convertDmy2Ymd(Session::get('jadwalshift_tanggalakhir'));
            $filtermode = Session::get('jadwalshift_filtermode');
            $bulan = Session::get('jadwalshift_bulan');
            $tahun = Session::get('jadwalshift_tahun');

            $totalhari = cal_days_in_month(CAL_GREGORIAN, $bulan, '20' . $tahun);

            if($filtermode == 'periode') {
                $sql = 'CALL pegawaishiftyymm(:yymm)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':yymm', $tahun . $bulan);
                $stmt->execute();
            }else{
                $sql = 'CALL pegawaishiftrangetanggal(:tanggalawal,:tanggalakhir)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':tanggalawal', $tanggalawal);
                $stmt->bindValue(':tanggalakhir', $tanggalakhir);
                $stmt->execute();
            }

            $sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS temp_pegawai
                (
                    id      INT UNSIGNED NOT NULL,
                    nama    VARCHAR(100) NOT NULL,
                    pin    VARCHAR(8) NOT NULL,
                    PRIMARY KEY (id)
                ) Engine = Memory';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'TRUNCATE temp_pegawai';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $where = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $where = ' AND p.id IN ' . $batasan;
            }

            if (Session::has('jadwalshift_atribut')) {
                $atributs = Session::get('jadwalshift_atribut');
                $atributnilai = Utils::atributNilai($atributs);
                $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . $atributnilai . ') )';
            }

            $sql = 'INSERT INTO temp_pegawai SELECT p.id,p.nama,p.pin FROM pegawai p, _pegawai _p WHERE p.id=_p.id AND p.del = "t" ' . $where;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            // select pegawai yang jamkerjanya shift
            $sql = 'SELECT id,nama,pin FROM temp_pegawai ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = $b + 1;
            $hh = '';
            $tes = '';
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['pin']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray($styleArray);

                $tes .= $row['nama'].' ';
                //siapkan data jadwal
                if($filtermode == 'periode') {
                    $sqlPerTanggal = 'CALL pegawaishiftpertanggal(:idpegawai, :yymm)';
                    $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
                    $stmtPerTanggal->bindValue(':idpegawai', $row['id']);
                    $stmtPerTanggal->bindValue(':yymm', $tahun . $bulan);
                    $stmtPerTanggal->execute();
                }else{
                    $sqlPerTanggal = 'CALL jadwalshiftrangetanggal(:idpegawai, :tanggalawal, :tanggalakhir)';
                    $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
                    $stmtPerTanggal->bindValue(':idpegawai', $row['id']);
                    $stmtPerTanggal->bindValue(':tanggalawal', $tanggalawal);
                    $stmtPerTanggal->bindValue(':tanggalakhir', $tanggalakhir);
                    $stmtPerTanggal->execute();
                }

                $sqlPerTanggal = 'SELECT *,DAY(tanggal) as tgl FROM _jadwalshift ORDER BY tanggal ASC';
                $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
                $stmtPerTanggal->execute();
                $hha = 3;
                while($rowPerTanggal = $stmtPerTanggal->fetch(PDO::FETCH_ASSOC)) {
                    $hh = Utils::angkaToHuruf($hha); //hh = huruf header
                   //header
                    $objPHPExcel->getActiveSheet()->setCellValue($hh.$b, $rowPerTanggal['tgl']);
                    $objPHPExcel->getActiveSheet()->getStyle($hh.$b)->applyFromArray($styleArray);

                    $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->applyFromArray($styleArray);
                    if($rowPerTanggal['alasantidakmasuk'] != ''){
                        // kasih background #f8ac59
                        $objPHPExcel->getActiveSheet()->setCellValue($hh.$i, Utils::getFirstCharInWord($rowPerTanggal['alasantidakmasuk']));
                        $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('f8ac59');
                    }else if ($rowPerTanggal['idjamkerja']==null) {
                        //tidak ada jam kerja
                    }else if($rowPerTanggal['jenis']=='full'){
                    }else if ($rowPerTanggal['jenis']=='shift') {
                        //ambil data jadwal shift null
                        $sqlJamkerjaNull = 'SELECT idjamkerjashift FROM jadwalshift WHERE idpegawai=:idpegawai AND tanggal=:tanggal AND ISNULL(idjamkerjashift)=true';
                        $stmtJamkerjaNull = $pdo->prepare($sqlJamkerjaNull);
                        $stmtJamkerjaNull->bindValue(':idpegawai', $row['id']);
                        $stmtJamkerjaNull->bindValue(':tanggal', $rowPerTanggal['tanggal']);
                        $stmtJamkerjaNull->execute();
                        if ($stmtJamkerjaNull->rowCount()>0) {
                            //jadwal libur
                            $objPHPExcel->getActiveSheet()->setCellValue($hh.$i, '<L>');
                        } else {
                            $sqlJamkerjaTerpilih = 'SELECT jks.namashift, jks.kode FROM jamkerjashift jks, jadwalshift js WHERE jks.id=js.idjamkerjashift AND js.idpegawai=:idpegawai AND js.tanggal=:tanggal ORDER BY jks.urutan, jks.namashift';
                            $stmtJamkerjaTerpilih = $pdo->prepare($sqlJamkerjaTerpilih);
                            $stmtJamkerjaTerpilih->bindParam(':idpegawai', $row['id']);
                            $stmtJamkerjaTerpilih->bindParam(':tanggal', $rowPerTanggal['tanggal']);
                            $stmtJamkerjaTerpilih->execute();
                            if ($stmtJamkerjaTerpilih->rowCount()>0) {
                                $kode = '';
                                if($stmtJamkerjaTerpilih->rowCount()>1){
                                    $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->applyFromArray($whiteText);
                                    $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('000000');
                                }
                                while ($rowJamKerjaTerpilih = $stmtJamkerjaTerpilih->fetch(PDO::FETCH_ASSOC)) {
                                    // warna background Utils::getColorBackground($rowJamKerjaTerpilih['namashift'])
                                    $kode .= trim($rowJamKerjaTerpilih['kode']).',';
                                    if($stmtJamkerjaTerpilih->rowCount()==1){
                                        $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB(str_replace('#','',Utils::getColorBackground($rowJamKerjaTerpilih['namashift'])));
                                        if(Utils::getColorForeground($rowJamKerjaTerpilih['namashift']) == '#ffffff'){
                                            $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->applyFromArray($whiteText);
                                        }
                                    }
                                }
                                $kode = $kode != '' ? substr($kode, 0, -1) : '';
                                // $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB(Utils::getColorBackground($rowJamKerjaTerpilih['namashift']));
                                $objPHPExcel->getActiveSheet()->setCellValue($hh.$i, $kode);
                            }
                        }
                    }

                    if($rowPerTanggal['harilibur'] == 'y'){
                        $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DD6B55');
                        $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->applyFromArray($blackText);
                    }else if($rowPerTanggal['dayinweek'] == 1){
                        $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('DDDDDD');
                        $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->applyFromArray($blackText);
                    }
                    $hha++;
                }
                $i++;
            }

            //set isi kolom
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $b, trans('all.pegawai'));
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$b, trans('all.pin'));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $b)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $b)->applyFromArray($styleArray);

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);

            $heightgambar = 99;
            $widthgambar = 99;

            $totalkolom = $totalhari + 1;
            $cg = Utils::angkaToHuruf($totalkolom) . '1';

            // style garis
            $end_i = $i - 1;
            $objPHPExcel->getActiveSheet()->getStyle('A1:' . $hh . $end_i)->applyFromArray($styleArray);

            if ($rowPE['footerkiri_1_teks'] == '' AND $rowPE['footerkiri_2_teks'] == '' AND $rowPE['footerkiri_3_teks'] == '' AND $rowPE['footerkiri_5_teks'] == '' AND $rowPE['footerkiri_6_teks'] == '' AND $rowPE['footertengah_1_teks'] == '' AND $rowPE['footertengah_2_teks'] == '' AND $rowPE['footertengah_3_teks'] == '' AND $rowPE['footertengah_5_teks'] == '' AND $rowPE['footertengah_6_teks'] == '' AND $rowPE['footerkanan_1_teks'] == '' AND $rowPE['footerkanan_2_teks'] == '' AND $rowPE['footerkanan_3_teks'] == '' AND $rowPE['footerkanan_5_teks'] == '' AND $rowPE['footerkanan_6_teks'] == '') {
                $l = $i - 1;
            } else {
                $l = $i + 1;
                Utils::footerExcel($objPHPExcel,'kiri','A','',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'tengah','B','C',$l,$rowPE);
                Utils::footerExcel($objPHPExcel,'kanan','D','D',$l,$rowPE);
            }

            // password
            Utils::passwordExcel($objPHPExcel);

            if ($b != 2) {
                Utils::header5baris($objPHPExcel,$hh,$rowPE);
            }

            //legend
            $jamkerjashift = $this->getJamKerjaShift(true);
            $fl = $l + 3;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+2), trans('all.jamkerjashift')); //eor = end of row
            for($i=0;$i<count($jamkerjashift);$i++){
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $fl, $jamkerjashift[$i]['nama']);
                $ahl = 2; // ahl : angka huruf legend
                for($j=0;$j<count($jamkerjashift[$i]['shift']);$j++){
                    $hl = Utils::angkaToHuruf($ahl);
                    $kodeshift = $jamkerjashift[$i]['shift'][$j]['kode'];
                    $namashift = $jamkerjashift[$i]['shift'][$j]['nama'];
                    $objPHPExcel->getActiveSheet()->setCellValue($hl.$fl, trim($kodeshift).'('.$namashift.') ');
                    $objPHPExcel->getActiveSheet()->getStyle($hl.$fl)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB(str_replace('#','',Utils::getColorBackground($namashift)));
                    $objPHPExcel->getActiveSheet()->getStyle($hl.$fl)->applyFromArray($styleArray);
                    if(Utils::getColorForeground($namashift) == '#ffffff'){
                        $objPHPExcel->getActiveSheet()->getStyle($hl.$fl)->applyFromArray($whiteText);
                    }
                    $ahl++;
                }
                $fl++;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($fl+1), '<L> Libur');

            //footer tanggal file dibuat
            $ft = $fl + 3;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $ft, '*tanggal pembuatan file ' . date('d/m/Y H:i:s'));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $ft)->getFont()->setItalic(true);

            if ($rowPE['logokiri'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokiri'];
                Utils::logoExcel('kiri',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'A1');
            }

            if ($rowPE['logokanan'] != "") {
                $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokanan'];
                Utils::logoExcel('kanan',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,$cg);
            }

            Utils::insertLogUser('Ekspor jadwal shift');
            Utils::setFileNameExcel(trans('all.jadwalshift'));
            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }

    //20181208
    // public function excel()
    // {
    //     if(strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'l') !== false){
    //         if (Session::get('perusahaan_expired') == 'ya') {
    //             return '';
    //         }

    //         $objPHPExcel = new PHPExcel();

    //         Utils::setPropertiesExcel($objPHPExcel);

    //         //set css kolom
    //         $styleArray = array(
    //             'borders' => array(
    //                 'outline' => array(
    //                     'style' => PHPExcel_Style_Border::BORDER_THIN,
    //                     'color' => array('argb' => '000000'),
    //                 ),
    //             ),
    //         );

    //         $pdo = DB::connection('perusahaan_db')->getPdo();
    //         $sql = 'SELECT * FROM parameterekspor';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute();
    //         $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);
    //         if ($rowPE['logokiri'] == '' AND $rowPE['logokanan'] == '' AND $rowPE['header_1_teks'] == '' AND $rowPE['header_2_teks'] == '' AND $rowPE['header_3_teks'] == '' AND $rowPE['header_4_teks'] == '' AND $rowPE['header_5_teks'] == '') {
    //             $b = 1; //b = baris
    //         } else {
    //             $b = 6;
    //         }

    //         $bt = $b; //variabel b tanggal dari sampai
    //         $b = $b + 1;

    //         //tempat proses data
    //         $bulan = Session::get('jadwalshift_bulan');
    //         $tahun = Session::get('jadwalshift_tahun');

    //         $totalhari = cal_days_in_month(CAL_GREGORIAN, $bulan, '20' . $tahun);

    //         $sql = 'CALL pegawaishiftyymm(:yymm)';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->bindValue(':yymm', $tahun . $bulan);
    //         $stmt->execute();

    //         $sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS temp_pegawai
    //             (
    //                 id      INT UNSIGNED NOT NULL,
    //                 nama    VARCHAR(100) NOT NULL,
    //                 pin    VARCHAR(8) NOT NULL,
    //                 PRIMARY KEY (id)
    //             ) Engine = Memory';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute();

    //         $sql = 'TRUNCATE temp_pegawai';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute();

    //         $where = '';
    //         $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
    //         if ($batasan != '') {
    //             $where = ' AND p.id IN ' . $batasan;
    //         }

    //         if (Session::has('jadwalshift_atribut')) {
    //             $atributs = Session::get('jadwalshift_atribut');
    //             $atributnilai = Utils::atributNilai($atributs);
    //             $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . $atributnilai . ') )';
    //         }

    //         $sql = 'INSERT INTO temp_pegawai SELECT p.id,p.nama,p.pin FROM pegawai p, _pegawai _p WHERE p.id=_p.id AND p.del = "t" ' . $where;
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute();

    //         // select pegawai yang jamkerjanya shift
    //         $sql = 'SELECT id,nama FROM temp_pegawai ORDER BY nama';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute();
    //         $data = array();
    //         $i = 0;
    //         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //             $data[$i] = array();
    //             $data[$i]['id'] = $row['id'];
    //             $data[$i]['nama'] = $row['nama'];
    //             $i++;
    //         }
            
    //         for ($j = 1; $j <= $totalhari; $j++) {
    //             $sql = 'SELECT
    //                         tp.id,
    //                         IFNULL(x.namashift,"") as namashift,
    //                         IFNULL(x.kodeshift,"") as kodeshift
    //                     FROM
    //                         temp_pegawai tp
    //                         LEFT JOIN (
    //                             SELECT
    //                                 js.idpegawai,
    //                                 GROUP_CONCAT(jks.namashift SEPARATOR "|") as namashift,
    //                                 GROUP_CONCAT(jks.kode SEPARATOR "|") as kodeshift
    //                             FROM
    //                                 jadwalshift js,
    //                                 jamkerjashift jks
    //                             WHERE
    //                                 jks.id=js.idjamkerjashift AND
    //                                 js.tanggal=STR_TO_DATE(:tanggal, "%y%m%e")
    //                             GROUP BY idpegawai
    //                         ) x ON x.idpegawai=tp.id
    //                     ORDER BY
    //                         tp.nama';
    //             $stmt = $pdo->prepare($sql);
    //             $stmt->bindValue(':tanggal', $tahun . $bulan . $j);
    //             $stmt->execute();

    //             $i = 0;
    //             while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //                 $namashift = '';
    //                 if ($row['namashift'] != '') {
    //                     $arrNamaShift = explode('|', $row['namashift']);
    //                     $arrKodeShift = explode('|', $row['kodeshift']);
    //                     for ($k = 0; $k < count($arrNamaShift); $k++) {
    //                         $namashift .= trim($arrKodeShift[$k]).',';
    //                     }
    //                 }
    //                 $data[$i][$j] = $namashift != '' ? substr($namashift, 0, -1) : '';
    //                 $i++;
    //             }
    //         }
    //         // $objPHPExcel->getActiveSheet()->getStyle($huruf . '5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('BFBFBF');
    //         //akhir tempat proses data

    //         //set isi kolom
    //         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $b, trans('all.pegawai'));
    //         $objPHPExcel->getActiveSheet()->getStyle('A' . $b)->applyFromArray($styleArray);

    //         //header tanggal
    //         $hh = '';
    //         $hha = 2; //hh = huruf header angka
    //         foreach ($data[0] as $key => $value) {
    //             if ($key != 'id' and $key != 'nama') {
    //                 $hh = Utils::angkaToHuruf($hha); //hh = huruf header
    //                 $objPHPExcel->getActiveSheet()->setCellValue($hh . $b, $key);
    //                 $objPHPExcel->getActiveSheet()->getStyle($hh . $b)->applyFromArray($styleArray);
    //                 $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(5);
    //                 $hha++;
    //             }
    //         }

    //         $i = $b + 1;
    //         for ($j = 0; $j < count($data); $j++) {
    //             $ha = 1; //ha = huruf awal
    //             foreach ($data[$j] as $key => $value) {
    //                 if ($key != 'id') {
    //                     $hh = Utils::angkaToHuruf($ha); //hh = huruf header
    //                     $objPHPExcel->getActiveSheet()->setCellValue($hh . $i, $value);
    //                     $objPHPExcel->getActiveSheet()->getStyle($hh . $i)->applyFromArray($styleArray);
    //                     $ha++;
    //                 }
    //             }
    //             $i++;
    //         }

    //         $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);

    //         $heightgambar = 99;
    //         $widthgambar = 99;

    //         $totalkolom = $totalhari + 1;
    //         $cg = Utils::angkaToHuruf($totalkolom) . '1';

    //         // style garis
    //         $end_i = $i - 1;
    //         $objPHPExcel->getActiveSheet()->getStyle('A1:' . $hh . $end_i)->applyFromArray($styleArray);

    //         if ($rowPE['footerkiri_1_teks'] == '' AND $rowPE['footerkiri_2_teks'] == '' AND $rowPE['footerkiri_3_teks'] == '' AND $rowPE['footerkiri_5_teks'] == '' AND $rowPE['footerkiri_6_teks'] == '' AND $rowPE['footertengah_1_teks'] == '' AND $rowPE['footertengah_2_teks'] == '' AND $rowPE['footertengah_3_teks'] == '' AND $rowPE['footertengah_5_teks'] == '' AND $rowPE['footertengah_6_teks'] == '' AND $rowPE['footerkanan_1_teks'] == '' AND $rowPE['footerkanan_2_teks'] == '' AND $rowPE['footerkanan_3_teks'] == '' AND $rowPE['footerkanan_5_teks'] == '' AND $rowPE['footerkanan_6_teks'] == '') {
    //             $l = $i - 1;
    //         } else {
    //             $l = $i + 1;
    //             Utils::footerExcel($objPHPExcel,'kiri','A','',$l,$rowPE);
    //             Utils::footerExcel($objPHPExcel,'tengah','B','C',$l,$rowPE);
    //             Utils::footerExcel($objPHPExcel,'kanan','D','D',$l,$rowPE);
    //         }

    //         // password
    //         Utils::passwordExcel($objPHPExcel);

    //         if ($b != 2) {
    //             Utils::header5baris($objPHPExcel,$hh,$rowPE);
    //         }

    //         //footer tanggal file dibuat
    //         $ft = $l + 2;
    //         $objPHPExcel->getActiveSheet()->setCellValue('A' . $ft, '*tanggal pembuatan file ' . date('d/m/Y H:i:s'));
    //         $objPHPExcel->getActiveSheet()->getStyle('A' . $ft)->getFont()->setItalic(true);

    //         if ($rowPE['logokiri'] != "") {
    //             $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokiri'];
    //             Utils::logoExcel('kiri',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,'A1');
    //         }

    //         if ($rowPE['logokanan'] != "") {
    //             $pathlogo = Session::get('folderroot_perusahaan') . '/parameterekspor/' . $rowPE['logokanan'];
    //             Utils::logoExcel('kanan',$objPHPExcel,$pathlogo,$heightgambar,$widthgambar,$cg);
    //         }

    //         header('Content-Type: application/vnd.ms-excel');
    //         header('Content-Disposition: attachment;filename="' . time() . '_' . trans('all.jadwalshift') . '.xlsx"');
    //         header('Cache-Control: max-age=0');
    //         header('Cache-Control: max-age=1');

    //         header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    //         header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    //         header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    //         header('Pragma: public'); // HTTP/1.0

    //         $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
    //         $writer->save('php://output');
    //     }
    // }

    public function templateExcel()
    {
        if(strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'l') !== false){
            if (Session::get('perusahaan_expired') == 'ya') {
                return '';
            }

            $objPHPExcel = new PHPExcel();

            Utils::setPropertiesExcel($objPHPExcel,trans('all.templatejadwalshift'));

            //set css kolom
            $styleArray = array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
            );

            $whiteText = array(
                'font'  => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            );

            $blackText = array(
                'font'  => array(
                    'color' => array('rgb' => '000000'),
                )
            );

            $pdo = DB::connection('perusahaan_db')->getPdo();
            $b = 1;

            $bt = $b; //variabel b tanggal dari sampai
            $b = $b + 1;

            //tempat proses data
            $tanggalawal = Session::get('jadwalshift_tanggalawal');
            $tanggalakhir = Session::get('jadwalshift_tanggalakhir');
            $filtermode = Session::get('jadwalshift_filtermode');
            $bulan = Session::get('jadwalshift_bulan');
            $tahun = Session::get('jadwalshift_tahun');

            //isi periode
            if($filtermode == 'periode') {
                $keterangan = trans('all.periode').' : '.Utils::getBulan($bulan).' 20'.$tahun;
                $parameter = $tahun . $bulan;
            }else{
                $keterangan = Utils::tanggalCantikDariSampai(Utils::convertDmy2Ymd($tanggalawal),Utils::convertDmy2Ymd($tanggalakhir));
                $parameter = Utils::convertDmy2Ymd($tanggalawal); // karena yang dibutuhkan cuma tanggal awal
            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $keterangan);
            $objPHPExcel->getActiveSheet()->setCellValue('B1', $parameter.'#'.$filtermode);

            $totalhari = cal_days_in_month(CAL_GREGORIAN, $bulan, '20' . $tahun);

            if($filtermode == 'periode') {
                $sql = 'CALL pegawaishiftyymm(:yymm)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':yymm', $tahun . $bulan);
                $stmt->execute();
            }else{
                $sql = 'CALL pegawaishiftrangetanggal(:tanggalawal,:tanggalakhir)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':tanggalawal', Utils::convertDmy2Ymd($tanggalawal));
                $stmt->bindValue(':tanggalakhir', Utils::convertDmy2Ymd($tanggalakhir));
                $stmt->execute();
            }

            $sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS temp_pegawai
                (
                    id      INT UNSIGNED NOT NULL,
                    nama    VARCHAR(100) NOT NULL,
                    pin    VARCHAR(8) NOT NULL,
                    PRIMARY KEY (id)
                ) Engine = Memory';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'TRUNCATE temp_pegawai';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $where = '';
            $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
            if ($batasan != '') {
                $where = ' AND p.id IN ' . $batasan;
            }

            if (Session::has('jadwalshift_atribut')) {
                $atributs = Session::get('jadwalshift_atribut');
                $atributnilai = Utils::atributNilai($atributs);
                $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . $atributnilai . ') )';
            }

            $sql = 'INSERT INTO temp_pegawai SELECT p.id,p.nama,p.pin FROM pegawai p, _pegawai _p WHERE p.id=_p.id AND p.del = "t" ' . $where;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            // select pegawai yang jamkerjanya shift
            $sql = 'SELECT id,nama,pin FROM temp_pegawai ORDER BY nama';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $i = $b + 1;
            $hh = '';
            $tes = '';
            $hha = 4;
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row['nama']);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row['id']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row['pin']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$i)->applyFromArray($styleArray);

                $tes .= $row['nama'].' ';
                //siapkan data jadwal
                if($filtermode == 'periode') {
                    $sqlPerTanggal = 'CALL pegawaishiftpertanggal(:idpegawai, :yymm)';
                    $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
                    $stmtPerTanggal->bindValue(':idpegawai', $row['id']);
                    $stmtPerTanggal->bindValue(':yymm', $tahun . $bulan);
                    $stmtPerTanggal->execute();
                }else{
                    $sqlPerTanggal = 'CALL jadwalshiftrangetanggal(:idpegawai, :tanggalawal, :tanggalakhir)';
                    $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
                    $stmtPerTanggal->bindValue(':idpegawai', $row['id']);
                    $stmtPerTanggal->bindValue(':tanggalawal', Utils::convertDmy2Ymd($tanggalawal));
                    $stmtPerTanggal->bindValue(':tanggalakhir', Utils::convertDmy2Ymd($tanggalakhir));
                    $stmtPerTanggal->execute();
                }

                $sqlPerTanggal = 'SELECT *,DAY(tanggal) as tgl FROM _jadwalshift ORDER BY tanggal ASC';
                $stmtPerTanggal = $pdo->prepare($sqlPerTanggal);
                $stmtPerTanggal->execute();
                $hha = 4;
                while($rowPerTanggal = $stmtPerTanggal->fetch(PDO::FETCH_ASSOC)) {
                    $hh = Utils::angkaToHuruf($hha); //hh = huruf header
                   //header
                    $objPHPExcel->getActiveSheet()->setCellValue($hh.$b, $rowPerTanggal['tgl']);
                    $objPHPExcel->getActiveSheet()->getStyle($hh.$b)->applyFromArray($styleArray);

                    $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->applyFromArray($styleArray);
                    if($rowPerTanggal['alasantidakmasuk'] != ''){
                        // kasih background #f8ac59
                        $objPHPExcel->getActiveSheet()->setCellValue($hh.$i, Utils::getFirstCharInWord($rowPerTanggal['alasantidakmasuk']));
                        $objPHPExcel->getActiveSheet()->getStyle($hh.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('f8ac59');
                    }else if ($rowPerTanggal['idjamkerja']==null) {
                        //tidak ada jam kerja
                    }else if($rowPerTanggal['jenis']=='full'){
                    }else if ($rowPerTanggal['jenis']=='shift') {
                        //ambil data jadwal shift null
                        $sqlJamkerjaNull = 'SELECT idjamkerjashift FROM jadwalshift WHERE idpegawai=:idpegawai AND tanggal=:tanggal AND ISNULL(idjamkerjashift)=true';
                        $stmtJamkerjaNull = $pdo->prepare($sqlJamkerjaNull);
                        $stmtJamkerjaNull->bindValue(':idpegawai', $row['id']);
                        $stmtJamkerjaNull->bindValue(':tanggal', $rowPerTanggal['tanggal']);
                        $stmtJamkerjaNull->execute();
                        if ($stmtJamkerjaNull->rowCount()>0) {
                            //jadwal libur
                            $objPHPExcel->getActiveSheet()->setCellValue($hh.$i, '<L>');
                        } else {
                            $sqlJamkerjaTerpilih = 'SELECT jks.namashift, jks.kode FROM jamkerjashift jks, jadwalshift js WHERE jks.id=js.idjamkerjashift AND js.idpegawai=:idpegawai AND js.tanggal=:tanggal ORDER BY jks.urutan, jks.namashift';
                            $stmtJamkerjaTerpilih = $pdo->prepare($sqlJamkerjaTerpilih);
                            $stmtJamkerjaTerpilih->bindParam(':idpegawai', $row['id']);
                            $stmtJamkerjaTerpilih->bindParam(':tanggal', $rowPerTanggal['tanggal']);
                            $stmtJamkerjaTerpilih->execute();
                            if ($stmtJamkerjaTerpilih->rowCount()>0) {
                                $kode = '';
                                while ($rowJamKerjaTerpilih = $stmtJamkerjaTerpilih->fetch(PDO::FETCH_ASSOC)) {
                                    // warna background Utils::getColorBackground($rowJamKerjaTerpilih['namashift'])
                                    $kode .= trim($rowJamKerjaTerpilih['kode']).',';
                                }
                                $kode = $kode != '' ? substr($kode, 0, -1) : '';
                                $objPHPExcel->getActiveSheet()->setCellValue($hh.$i, $kode);
                            }
                        }
                    }
                    $hha++;
                }
                $i++;
            }
            
            //set isi kolom
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $b, trans('all.pegawai'));
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$b, trans('all.pin'));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $b)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $b)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getStyle('C' . $b)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $b, trans('all.pegawai'));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $b)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $b, 'ID');
            $objPHPExcel->getActiveSheet()->getStyle('B' . $b)->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(0);

            $totalkolom = $totalhari + 1;

            // style garis
            $end_i = $i - 1;
            $objPHPExcel->getActiveSheet()->getStyle('A1:' . $hh . $end_i)->applyFromArray($styleArray);

            $ft = $i;
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $ft, 'eor'); //eor = end of row
            $objPHPExcel->getActiveSheet()->setCellValue(Utils::angkaToHuruf($hha) . '1', 'eoc'); //eor = end of column
            $objPHPExcel->getActiveSheet()->getColumnDimension(Utils::angkaToHuruf($hha))->setWidth(0);
            
            //legend
            $jamkerjashift = $this->getJamKerjaShift(true);
            $fl = $i + 3;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+2), trans('all.jamkerjashift')); //eor = end of row
            for($i=0;$i<count($jamkerjashift);$i++){
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $fl, $jamkerjashift[$i]['nama']);
                $ahl = 3; // ahl : angka huruf legend
                for($j=0;$j<count($jamkerjashift[$i]['shift']);$j++){
                    $hl = Utils::angkaToHuruf($ahl);
                    $kodeshift = $jamkerjashift[$i]['shift'][$j]['kode'];
                    $namashift = $jamkerjashift[$i]['shift'][$j]['nama'];
                    $objPHPExcel->getActiveSheet()->setCellValue($hl.$fl, trim($kodeshift).'('.$namashift.') ');
                    $objPHPExcel->getActiveSheet()->getStyle($hl.$fl)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB(str_replace('#','',Utils::getColorBackground($namashift)));
                    $objPHPExcel->getActiveSheet()->getStyle($hl.$fl)->applyFromArray($styleArray);
                    if(Utils::getColorForeground($namashift) == '#ffffff'){
                        $objPHPExcel->getActiveSheet()->getStyle($hl.$fl)->applyFromArray($whiteText);
                    }
                    $ahl++;
                }
                $fl++;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($fl+1), '<L> Libur');

            //footer tanggal file dibuat
            $ft = $fl + 3;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $ft, '*tanggal pembuatan file ' . date('d/m/Y H:i:s'));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $ft)->getFont()->setItalic(true);

            Utils::insertLogUser('Ekspor template jadwal shift');

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . time() . '_' . trans('all.templatejadwalshift') . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');

            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0

            $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $writer->save('php://output');
        }
    }
    
    //20181210
    // public function templateExcel()
    // {
    //     if(strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'm') !== false){
    //         if (Session::get('perusahaan_expired') == 'ya') {
    //             return '';
    //         }

    //         $objPHPExcel = new PHPExcel();

    //         Utils::setPropertiesExcel($objPHPExcel);

    //         //set css kolom
    //         $styleArray = array(
    //             'borders' => array(
    //                 'outline' => array(
    //                     'style' => PHPExcel_Style_Border::BORDER_THIN,
    //                     'color' => array('argb' => '000000'),
    //                 ),
    //             ),
    //         );

    //         $pdo = DB::connection('perusahaan_db')->getPdo();
    //         //b adalah baris(row)
    //         $b = 2; // 2 karena 1 nya buat tempat periode
    //         //bt adalah baris tanggal
    //         $bt = $b; //variabel b tanggal dari sampai

    //         //tempat proses data
    //         $bulan = Session::get('jadwalshift_bulan');
    //         $tahun = Session::get('jadwalshift_tahun');

    //         //isi periode
    //         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', trans('all.periode').' : '.Utils::getBulan($bulan).' 20'.$tahun);
    //         $objPHPExcel->getActiveSheet()->setCellValue('B1', $tahun . $bulan);

    //         $totalhari = cal_days_in_month(CAL_GREGORIAN, $bulan, '20' . $tahun);

    //         $sql = 'CALL pegawaishiftyymm(:yymm)';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->bindValue(':yymm', $tahun . $bulan);
    //         $stmt->execute();

    //         $sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS temp_pegawai
    //             (
    //                 id      INT UNSIGNED NOT NULL,
    //                 nama    VARCHAR(100) NOT NULL,
    //                 pin    VARCHAR(8) NOT NULL,
    //                 PRIMARY KEY (id)
    //             ) Engine = Memory';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute();

    //         $sql = 'TRUNCATE temp_pegawai';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute();

    //         $where = '';
    //         $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
    //         if ($batasan != '') {
    //             $where = ' AND p.id IN ' . $batasan;
    //         }

    //         if (Session::has('jadwalshift_atribut')) {
    //             $atributs = Session::get('jadwalshift_atribut');
    //             $atributnilai = Utils::atributNilai($atributs);
    //             $where .= ' AND p.id IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN (' . $atributnilai . ') )';
    //         }

    //         $sql = 'INSERT INTO temp_pegawai SELECT p.id,p.nama,p.pin FROM pegawai p, _pegawai _p WHERE p.id=_p.id AND p.del = "t" ' . $where;
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute();

    //         // select pegawai yang jamkerjanya shift
    //         $sql = 'SELECT nama,id,pin FROM temp_pegawai ORDER BY nama';
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute();
    //         $data = array();
    //         $i = 0;
    //         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //             $data[$i] = array();
    //             $data[$i]['nama'] = $row['nama'];
    //             $data[$i]['id'] = $row['id'];
    //             $data[$i]['pin'] = $row['pin'];
    //             $i++;
    //         }
    //         for ($j = 1; $j <= $totalhari; $j++) {
    //             $sql = 'SELECT
    //                         tp.id,
    //                         IFNULL(x.idjamkerjashift,"") as idjamkerjashift,
    //                         IFNULL(x.namashift,"") as namashift,
    //                         IFNULL(x.kodeshift,"") as kodeshift
    //                     FROM
    //                         temp_pegawai tp
    //                         LEFT JOIN (
    //                             SELECT
    //                                 js.idpegawai,
    //                                 GROUP_CONCAT(jks.id SEPARATOR "|") as idjamkerjashift,
    //                                 GROUP_CONCAT(jks.namashift SEPARATOR "|") as namashift,
    //                                 GROUP_CONCAT(jks.kode SEPARATOR "|") as kodeshift
    //                             FROM
    //                                 jadwalshift js,
    //                                 jamkerjashift jks
    //                             WHERE
    //                                 jks.id=js.idjamkerjashift AND
    //                                 js.tanggal=STR_TO_DATE(:tanggal, "%y%m%e")
    //                             GROUP BY idpegawai
    //                         ) x ON x.idpegawai=tp.id
    //                     ORDER BY
    //                         tp.nama';
    //             $stmt = $pdo->prepare($sql);
    //             $stmt->bindValue(':tanggal', $tahun . $bulan . $j);
    //             $stmt->execute();

    //             $i = 0;
    //             while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //                 $namashift = '';
    //                 if ($row['namashift'] != '') {
    //                     $arrNamaShift = explode('|', $row['namashift']);
    //                     $arrKodeShift = explode('|', $row['kodeshift']);
    //                     for ($k = 0; $k < count($arrNamaShift); $k++) {
    //                         $namashift .= trim($arrKodeShift[$k]).',';
    //                     }
    //                 }
    //                 $data[$i][$j] = $namashift != '' ? substr($namashift, 0, -1) : '';
    //                 $i++;
    //             }
    //         }
    //         //akhir tempat proses data

    //         //set isi kolom
    //         $objPHPExcel->getActiveSheet()->setCellValue('A' . $b, trans('all.pegawai'));
    //         $objPHPExcel->getActiveSheet()->getStyle('A' . $b)->applyFromArray($styleArray);
    //         $objPHPExcel->getActiveSheet()->setCellValue('B' . $b, 'ID');
    //         $objPHPExcel->getActiveSheet()->getStyle('B' . $b)->applyFromArray($styleArray);
    //         $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(0);

    //         //header tanggal
    //         $hh = '';
    //         $hha = 3; //hh = huruf header angka
    //         foreach ($data[0] as $key => $value) {
    //             if ($key != 'id' and $key != 'nama') {
    //                 $hh = Utils::angkaToHuruf($hha); //hh = huruf header
    //                 $objPHPExcel->getActiveSheet()->setCellValue($hh . $b, $key);
    //                 $objPHPExcel->getActiveSheet()->getStyle($hh . $b)->applyFromArray($styleArray);
    //                 // $objPHPExcel->getActiveSheet()->getColumnDimension($hh)->setWidth(5);
    //                 $hha++;
    //             }
    //         }

    //         $i = $b + 1;
    //         for ($j = 0; $j < count($data); $j++) {
    //             $ha = 1; //ha = huruf awal
    //             foreach ($data[$j] as $key => $value) {
    //                 // if ($key != 'id') {
    //                     $hh = Utils::angkaToHuruf($ha); //hh = huruf header
    //                     $objPHPExcel->getActiveSheet()->setCellValue($hh . $i, $value);
    //                     $objPHPExcel->getActiveSheet()->getStyle($hh . $i)->applyFromArray($styleArray);
    //                     $ha++;
    //                 // }
    //             }
    //             $i++;
    //         }

    //         $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);

    //         $heightgambar = 99;
    //         $widthgambar = 99;

    //         $totalkolom = $totalhari + 3;
    //         $cg = Utils::angkaToHuruf($totalkolom) . '1';

    //         // style garis
    //         $end_i = $i - 1;
    //         $objPHPExcel->getActiveSheet()->getStyle('A1:' . $hh . $end_i)->applyFromArray($styleArray);

    //         $ft = $i;
    //         $objPHPExcel->getActiveSheet()->setCellValue('B' . $ft, 'eor'); //eor = end of row
    //         $objPHPExcel->getActiveSheet()->setCellValue(Utils::angkaToHuruf($ha) . '1', 'eoc'); //eor = end of column
    //         $objPHPExcel->getActiveSheet()->getColumnDimension(Utils::angkaToHuruf($ha))->setWidth(0);

    //         //legend
    //         $jamkerjashift = $this->getJamKerjaShift(true);
    //         $fl = $i + 3;
    //         $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i+2), trans('all.jamkerjashift')); //eor = end of row
    //         for($i=0;$i<count($jamkerjashift);$i++){
    //             $objPHPExcel->getActiveSheet()->setCellValue('A' . $fl, $jamkerjashift[$i]);
    //             $fl++;
    //         }
    //         $objPHPExcel->getActiveSheet()->setCellValue('A' . ($fl+1), '<L> Libur');

    //         header('Content-Type: application/vnd.ms-excel');
    //         header('Content-Disposition: attachment;filename="' . time() . '_' . trans('all.templatejadwalshift') . '.xlsx"');
    //         header('Cache-Control: max-age=0');
    //         header('Cache-Control: max-age=1');

    //         header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    //         header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    //         header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    //         header('Pragma: public'); // HTTP/1.0

    //         $writer = new PHPExcel_Writer_Excel2007($objPHPExcel);
    //         $writer->save('php://output');
    //     }
    // }

    public function importExcel(Request $request){
      $pdo = DB::connection('perusahaan_db')->getPdo();
      $fileexcel = $request->file('fileexcel');
      if($fileexcel->getMimeType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
        $template = PHPExcel_IOFactory::load($fileexcel);
        $objWorksheet = clone $template->getActiveSheet();
        $parameter = $objWorksheet->getCell('B1')->getValue(); // formatnya paramter#jenisparameter
        $parameter_ex = explode('#', $parameter);

        //2 karena dari B(huruf ke 2 abjad)
        // $idpegawai = $objWorksheet->getCell('B3')->getValue();
        $b = 3; //b = baris
        //looping rows
        while(1 == 1){
          //keluar dari loop jika bertemu dengan eor(end of row)
          if($objWorksheet->getCell('B'.$b)->getValue() == 'eor'){
            break;
          }
          $idpegawai = $objWorksheet->getCell('B'.$b)->getValue();
          // $tes = '';
          $tgl = 1;
          $h = 3; //h = huruf
          $p = 0;
          //looping column
          while(1 == 1){
              if($parameter_ex[1] == 'periode') {
                  $tahun = substr($parameter_ex[0], 0, 2); //2 digit belakang
                  $bulan = substr($parameter_ex[0], -2);
                  $tanggal_yme = $tahun . $bulan . $tgl;
              }else{
                  $tanggal = $parameter_ex[0];
                  $date = date_create($tanggal);
                  date_add($date,date_interval_create_from_date_string($p." days"));
                  $tanggal_yme =  date_format($date,"ymj");
              }
              //cari jamKerjaPegawai
              $sql = 'SELECT getpegawaijamkerja(:id,"id",STR_TO_DATE(:tanggal, "%y%m%e")) as id';
              $stmt = $pdo->prepare($sql);
              $stmt->bindValue(':id', $idpegawai);
              $stmt->bindValue(':tanggal', $tanggal_yme);
              $stmt->execute();
              $idjamkerja = '';
              if ($stmt->rowCount() > 0) {
                  $rowJK = $stmt->fetch(PDO::FETCH_ASSOC);
                  $idjamkerja = $rowJK['id'];
              }
              if ($idjamkerja != '') {
                  $kodejamkerja = $objWorksheet->getCell(Utils::angkaToHuruf($h + 1) . $b)->getValue(); //kenapa $h+1 ? karena $h isinya id
                  // $tes .= $tahun.$bulan.$tgl.'<br>';
                  Utils::hapusJadwalShift($idpegawai, $tanggal_yme);
                  if ($kodejamkerja != '') {
                      $kodejamkerjaexplode = explode(',', $kodejamkerja);
                      for ($i = 0; $i < count($kodejamkerjaexplode); $i++) {
                          //<L> adalah tanda jika libur
                          if ($kodejamkerjaexplode[$i] != '<L>') {
                              //                            $idjamkerjashift = Utils::getDataWhere($pdo,'jamkerjashift','id','kode',trim($kodejamkerjaexplode[$i]));
                              $idjamkerjashift = Utils::getDataCustomWhere($pdo, 'jamkerjashift', 'id', 'kode = "' . trim($kodejamkerjaexplode[$i]) . '" AND idjamkerja = ' . $idjamkerja);
                              if ($idjamkerjashift != '') {
                                  $this->saveJadwalShift($idpegawai,$idjamkerjashift,$tanggal_yme);
                              }
                          } else {
                              $this->saveJadwalShift($idpegawai,'',$tanggal_yme);
                          }
                      }
                  }
              }
              //keluar dari loop jika bertemu dengan eoc(end of column)
              if ($objWorksheet->getCell(Utils::angkaToHuruf($h) . '1')->getValue() == 'eoc') {
                  break 1;
              }
              $tgl++;
              $h++;
              $p++;
          }
          sleep(1);
          $b++;
        }
        // return $tes;
        Utils::insertLogUser('import jadwal shift dari excel');
        $msg = trans('all.databerhasildisimpan');
      }else{
        $msg = trans('all.formatfileexceltidakvalid');
      }
      return redirect('datainduk/absensi/jadwalshift')->with('message', $msg);
    }
}