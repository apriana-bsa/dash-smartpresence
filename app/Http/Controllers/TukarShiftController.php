<?php
namespace App\Http\Controllers;

use App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use DB;
use PDO;

class TukarShiftController extends Controller
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
            $periode = $this->getPeriode();
            Utils::insertLogUser('akses menu tukar shift');
            return view('datainduk/absensi/tukarshift/index', ['menu' => "tukarshift", 'jenis' => 'tukarshift', 'periode' => $periode]);
        } else {
            return redirect("/");
        }
    }

    public function submitTampilkan(request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idpegawai = $request->pegawai;
        $periode = $request->periode; //yymm
        $tahun = substr($periode, 0, -2);
        $bulan = substr($periode, -2);
        //$totalhari = cal_days_in_month(CAL_GREGORIAN,$bulan,'20'.$tahun);

        //set session idpegawai dan periode
        Session::set('tukarshift_idpegawai_'.$request->dari, $idpegawai);
        Session::set('tukarshift_periode_'.$request->dari, $periode);

        $data = array();
        $sql = 'CALL pegawaishiftterpilihpertanggal(:idpegawai,:periode)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':periode', $periode);
        $stmt->execute();

        $sql = 'SELECT
                    DAY(tanggal) as tanggal,
                    dayinweek,
                    idjamkerja,
                    nama,
                    jenis,
                    jadwal,
                    alasantidakmasuk,
                    keterangantidakmasuk
                FROM
                    _jadwalshift
                ORDER BY
                    tanggal';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $i = 0;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

            $data[$i]['tanggal'] = $row['tanggal'];
            $data[$i]['hari'] = Utils::getHari($row['dayinweek']);
            $data[$i]['idjamkerja'] = $row['idjamkerja'];
            $data[$i]['nama'] = $row['nama'];
            $data[$i]['jenis'] = $row['jenis'];
            $data[$i]['ijintidakmasuk'] = $row['alasantidakmasuk'];
            $data[$i]['ijintidakmasukketerangan'] = $row['keterangantidakmasuk'];
            $data[$i]['libur'] = $row['jadwal'] == 'libur' ? 'y' : 't';
            $data[$i]['jadwal'] = array();
            if($row['jadwal'] != ''){
                if($row['jadwal'] != 'libur'){
                    //pecah |
                    $jadwal = explode('|', $row['jadwal']);
                    for ($j = 0; $j < count($jadwal); $j++) {
                        //pecah #
                        $jadwalshift = explode('#', $jadwal[$j]);
                        $data[$i]['jadwal'][$j]['idshift'] = $jadwalshift[0];
                        $data[$i]['jadwal'][$j]['namashift'] = $jadwalshift[1];
                    }
                }
            }

            $i++;
        }

        return view('datainduk/absensi/tukarshift/listjamkerjashift', ['data' => $data, 'dari' => $request->dari]);
    }

    public function jamKerjaShiftPegawai($idpegawai, $tanggal, $dari)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT 
                    js.idpegawai,
                    js.id as idjadwalshift,
                    jks.namashift
                FROM 
                    jadwalshift js,
                    jamkerjashift jks
                WHERE
                    jks.id=js.idjamkerjashift AND
                    js.tanggal=STR_TO_DATE(:tanggal, "%d-%m-%Y") AND
                    js.idpegawai = :idpegawai
                ORDER BY
                  jks.urutan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggal', $tanggal);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        return view('datainduk/absensi/tukarshift/listjamkerjashift', ['data' => $data, 'dari' => $dari]);
    }

    public function submitStep1(Request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND status = "a" AND del = "t"';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $request->pegawai1);
        $stmt->execute();
        $rowP = $stmt->fetch(PDO::FETCH_ASSOC);
        $pegawai = $rowP['nama'];
        $tanggal = $request->tanggal1;

        $sql = 'SELECT jks.namashift FROM jadwalshift js, jamkerjashift jks WHERE js.idjamkerjashift=jks.id AND js.id = :idjadwalshift';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idjadwalshift', $request->idjadwalshift_1);
        $stmt->execute();
        $rowS = $stmt->fetch(PDO::FETCH_ASSOC);
        $shift = $rowS['namashift'];
        $tukardengan = trans('all.' . $request->jenispenukaran);

        return view('datainduk/absensi/tukarshift/step1', ['idpegawai1' => $request->pegawai1, 'idjadwalshift_1' => $request->idjadwalshift_1, 'pegawai1' => $pegawai, 'tanggal1' => $tanggal, 'shift1' => $shift, 'tukardengan' => $tukardengan, 'menu' => 'tukarshift']);
    }

    public function submit(Request $request)
    {
        //session idpegawai dan periode berasal ketika submit milih pegawai dan periode
        $idpegawai_a = Session::get('tukarshift_idpegawai_1');
        $idpegawai_b = Session::get('tukarshift_idpegawai_2');
        $periode_a = Session::get('tukarshift_periode_1');
        $periode_b = Session::get('tukarshift_periode_2');
        $idjamkerja_a = explode('|', $request->idjamkerja_a);
        $idjamkerja_b = explode('|', $request->idjamkerja_b);
        //return 'idpegawai_a: '.$idpegawai_a.' idpegawai_b: '.$idpegawai_b.' periode_a: '.$periode_a.' periode_b: '.$periode_b.' idjmkerja_a: '.$request->idjamkerja_a.' idjamkerja_b: '.$request->idjamkerja_b.' tanggal_a: '.$request->tanggal_a.' tanggal_b: '.$request->tanggal_b;
        $idjamkerjashift_a = explode('|', $request->idjamkerjashift_a);
        $idjamkerjashift_b = explode('|', $request->idjamkerjashift_b);
        $tanggal_a = explode('|', $request->tanggal_a);
        $tanggal_b = explode('|', $request->tanggal_b);
        $namapegawai_a = Utils::getNamaPegawai($idpegawai_a);
        $namapegawai_b = Utils::getNamaPegawai($idpegawai_b);
        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'unknown';

        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cek jumlah harus sama
        if (count($idjamkerjashift_a)>0 && count($idjamkerjashift_a)==count($idjamkerjashift_b)) {
            $jumlahAB = count($idjamkerjashift_a);

            // persiapan _jadwalshift_a dan _jadwalshift_b --> berisi jadwal shift per tanggalnya sesuai dgn periode
            $sql = 'DROP TEMPORARY TABLE IF EXISTS _jadwalshift_a';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'CREATE TEMPORARY TABLE _jadwalshift_a
                    (
                        `tanggal`           DATE NOT NULL,
                        `dayinweek`         INT NOT NULL,
                        `idjamkerja`        INT(11) UNSIGNED,
                        `nama`              VARCHAR(100) NOT NULL,
                        `jenis`             ENUM("full","shift") NOT NULL
                    ) Engine=MEMORY;
               ';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'CALL pegawaishiftpertanggal(:idpegawai_a, :periode_b)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai_a', $idpegawai_a);
            $stmt->bindParam(':periode_b', $periode_b);
            $stmt->execute();

            $sql = 'INSERT INTO _jadwalshift_a SELECT tanggal, dayinweek, idjamkerja, nama, jenis  FROM _jadwalshift';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'DROP TEMPORARY TABLE IF EXISTS _jadwalshift_b';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = '    CREATE TEMPORARY TABLE _jadwalshift_b
                    (
                        `tanggal`           DATE NOT NULL,
                        `dayinweek`         INT NOT NULL,
                        `idjamkerja`        INT(11) UNSIGNED,
                        `nama`              VARCHAR(100) NOT NULL,
                        `jenis`             ENUM("full","shift") NOT NULL
                    ) Engine=MEMORY;
               ';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'CALL pegawaishiftpertanggal(:idpegawai_b, :periode_a)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai_b', $idpegawai_b);
            $stmt->bindParam(':periode_a', $periode_a);
            $stmt->execute();

            $sql = 'INSERT INTO _jadwalshift_b SELECT tanggal, dayinweek, idjamkerja, nama, jenis FROM _jadwalshift';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            // persiapan _jadwalshift_ab berisi jadwal shift untuk pegawai a dan b (bukan per tanggal)

            $sql = 'DROP TEMPORARY TABLE IF EXISTS _jadwalshift_ab';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'CREATE TEMPORARY TABLE _jadwalshift_ab (
                    `id`                        INT(11) UNSIGNED,
                    `tanggal`                   DATE NOT NULL,
                    `idpegawai`                 INT(11) UNSIGNED NOT NULL,
                    `idjamkerja`                INT(11) UNSIGNED,
                    `idjamkerjashift`           INT(11) UNSIGNED,
                    INDEX `idx__jadwalshift_ab_tanggal` (`tanggal`),
                    INDEX `idx__jadwalshift_ab_idpegawai` (`idpegawai`),
                    INDEX `idx__jadwalshift_ab_idjamkerja` (`idjamkerja`),
                    INDEX `idx__jadwalshift_ab_idjamkerjashift` (`idjamkerjashift`),
                    PRIMARY KEY (`id`)
                ) ENGINE=Memory;
               ';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'INSERT INTO _jadwalshift_ab SELECT id, tanggal, idpegawai, NULL, idjamkerjashift FROM jadwalshift WHERE idpegawai=:idpegawai AND DATE_FORMAT(tanggal, "%y%m")=:periode';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai', $idpegawai_a);
            $stmt->bindParam(':periode', $periode_a);
            $stmt->execute();

            $sql = 'INSERT INTO _jadwalshift_ab SELECT id, tanggal, idpegawai, NULL, idjamkerjashift FROM jadwalshift WHERE idpegawai=:idpegawai AND DATE_FORMAT(tanggal, "%y%m")=:periode';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai', $idpegawai_b);
            $stmt->bindParam(':periode', $periode_b);
            $stmt->execute();

            $sql = 'UPDATE _jadwalshift_ab _jsab, _jadwalshift_a _jsa SET _jsab.idjamkerja=_jsa.idjamkerja WHERE _jsab.tanggal=_jsa.tanggal AND _jsab.idpegawai=:idpegawai';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai', $idpegawai_a);
            $stmt->execute();

            $sql = 'UPDATE _jadwalshift_ab _jsab, _jadwalshift_b _jsb SET _jsab.idjamkerja=_jsb.idjamkerja WHERE _jsab.tanggal=_jsb.tanggal AND _jsab.idpegawai=:idpegawai';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai', $idpegawai_b);
            $stmt->execute();

            // persiapan _jadwalshifttukar berisi jadwal shift pegawai a dan b yang akan ditukar (bukan per tanggal)
            $sql = 'DROP TEMPORARY TABLE IF EXISTS _jadwalshifttukar';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'CREATE TEMPORARY TABLE _jadwalshifttukar (
                    `id`                        INT(11) UNSIGNED,
                    `tanggal`                   DATE NOT NULL,
                    `idpegawai`                 INT(11) UNSIGNED NOT NULL,
                    `idjamkerja`                INT(11) UNSIGNED,
                    `idjamkerjashift`           INT(11) UNSIGNED,
                    INDEX `idx__jadwalshifttukar_tanggal` (`tanggal`),
                    INDEX `idx__jadwalshifttukar_idpegawai` (`idpegawai`),
                    INDEX `idx__jadwalshifttukar_idjamkerja` (`idjamkerja`),
                    INDEX `idx__jadwalshifttukar_idjamkerjashift` (`idjamkerjashift`),
                    PRIMARY KEY (`id`)
                ) ENGINE=Memory;
               ';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            //persiapan temporary table supaya tidak memberatkan disk
            $sqlCekAB = '';
            for ($i=0;$i<$jumlahAB;$i++) {
                if (isset($idjamkerjashift_a[$i]) && $idjamkerjashift_a[$i]!='') {
                    $where_idjamkerjashift_a = 'js.idjamkerjashift=' . $idjamkerjashift_a[$i];
                }
                else {
                    $where_idjamkerjashift_a = 'ISNULL(js.idjamkerjashift)=true';
                }

                if (isset($idjamkerjashift_b[$i]) && $idjamkerjashift_b[$i]!='') {
                    $where_idjamkerjashift_b = 'js.idjamkerjashift='.$idjamkerjashift_b[$i];
                }
                else {
                    $where_idjamkerjashift_b = 'ISNULL(js.idjamkerjashift)=true';
                }

                $sqlCekAB = $sqlCekAB.' OR (
                                                js.tanggal=STR_TO_DATE("'.$periode_a.$tanggal_a[$i].'", "%y%m%e") AND
                                                js.idpegawai='.$idpegawai_a.' AND
                                                '.$where_idjamkerjashift_a.'
                                            )
                                        OR (
                                                js.tanggal=STR_TO_DATE("'.$periode_b.$tanggal_b[$i].'", "%y%m%e") AND
                                                js.idpegawai='.$idpegawai_b.' AND
                                                '.$where_idjamkerjashift_b.'
                                            )';
            }
            //hapus OR didepan
            $sqlCekAB = substr($sqlCekAB,4);

            $sql = 'INSERT INTO _jadwalshifttukar
                        SELECT
                            js.id,
                            js.tanggal,
                            js.idpegawai,
                            jks.idjamkerja,
                            js.idjamkerjashift
                        FROM
                            jadwalshift js
                            LEFT JOIN jamkerjashift jks ON js.idjamkerjashift=jks.id
                        WHERE
                            '.$sqlCekAB;
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            //cek apakah jumlah record sama dgn 2x jumlah parameter?
            $sql = 'SELECT COUNT(*) as jumlah FROM _jadwalshifttukar';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (2*$jumlahAB != $row['jumlah']) {
                //return trans('all.datatidakditemukan');
                $response['msg'] = trans('all.datatidakditemukan');
                return $response;
            }

            //untuk data yg idjamkerja adalah NULL, isikan dari _jadwalshift_a dan _jadwalshift_b
            $sql = 'UPDATE _jadwalshifttukar _jst, _jadwalshift_a _jsa SET _jst.idjamkerja=_jsa.idjamkerja WHERE _jst.tanggal=_jsa.tanggal AND _jst.idpegawai=:idpegawai AND ISNULL(_jst.idjamkerja)=true';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai', $idpegawai_a);
            $stmt->execute();

            $sql = 'UPDATE _jadwalshifttukar _jst, _jadwalshift_b _jsb SET _jst.idjamkerja=_jsb.idjamkerja WHERE _jst.tanggal=_jsb.tanggal AND _jst.idpegawai=:idpegawai AND ISNULL(_jst.idjamkerja)=true';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai', $idpegawai_b);
            $stmt->execute();

            //cek apakah data yang post ada dan valid (jumlah sama/jamkerja seseuai/dll)?
            $sql = 'SELECT idjamkerja, SUM(IF(idpegawai='.$idpegawai_a.',1,0)) as jumlah_a, SUM(IF(idpegawai='.$idpegawai_b.',1,0)) as jumlah_b FROM _jadwalshifttukar GROUP BY idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $idjamkerja = $row['idjamkerja'];
                if ($row['jumlah_a']==$row['jumlah_b']) {
                    //cek apakah pada tanggal yang akan ditukar sudah benar idjamkerjanya untuk pegawai A?
                    $sql01 = 'SELECT 
                                IFNULL(GROUP_CONCAT(DISTINCT _jsa.tanggal ORDER BY _jsa.tanggal ASC SEPARATOR ","),"") as jamkerjasalah
                              FROM
                                _jadwalshift_a _jsa,
                                _jadwalshifttukar _jst
                              WHERE 
                                _jsa.tanggal=_jst.tanggal AND 
                                _jst.idpegawai=:idpegawai_b AND 
                                _jst.idjamkerja=:idjamkerja0 AND 
                                (ISNULL(_jsa.idjamkerja) OR _jsa.idjamkerja<>:idjamkerja1)';
                    $stmt01 = $pdo->prepare($sql01);
                    $stmt01->bindParam(':idpegawai_b', $idpegawai_b);
                    $stmt01->bindParam(':idjamkerja0', $idjamkerja);
                    $stmt01->bindParam(':idjamkerja1', $idjamkerja);
                    $stmt01->execute();
                    $row01 = $stmt01->fetch(PDO::FETCH_ASSOC);
                    $jamkerjasalah = $row01['jamkerjasalah'];
                    if ($jamkerjasalah!='') {
                        //return trans('all.jamkerjashifttidaksesuai').' '.$namapegawai_a.' '.Utils::tanggalCantik($jamkerjasalah);
                        $response['msg'] = trans('all.jamkerjakshifttidaksesuai').' '.$namapegawai_a.' '.Utils::tanggalCantik($jamkerjasalah);
                        return $response;
                    }

                    //cek apakah pada tanggal yang akan ditukar sudah benar idjamkerjanya untuk pegawai B?
                    $sql01 = 'SELECT
                                IFNULL(GROUP_CONCAT(DISTINCT _jsb.tanggal ORDER BY _jsb.tanggal ASC SEPARATOR ","),"") as jamkerjasalah
                              FROM
                                _jadwalshift_b _jsb,
                                _jadwalshifttukar _jst
                              WHERE
                                _jsb.tanggal=_jst.tanggal AND
                                _jst.idpegawai=:idpegawai_a AND
                                _jst.idjamkerja=:idjamkerja0 AND
                                (ISNULL(_jsb.idjamkerja) OR _jsb.idjamkerja<>:idjamkerja1)';
                    $stmt01 = $pdo->prepare($sql01);
                    $stmt01->bindParam(':idpegawai_a', $idpegawai_a);
                    $stmt01->bindParam(':idjamkerja0', $idjamkerja);
                    $stmt01->bindParam(':idjamkerja1', $idjamkerja);
                    $stmt01->execute();
                    $row01 = $stmt01->fetch(PDO::FETCH_ASSOC);
                    $jamkerjasalah = $row01['jamkerjasalah'];
                    if ($jamkerjasalah!='') {
                        //return trans('all.jamkerjashifttidaksesuai').' '.$namapegawai_b.' '.tanggalCantik($jamkerjasalah);
                        $response['msg'] = trans('all.jamkerjashifttidaksesuai').' '.$namapegawai_b.' '.Utils::tanggalCantik($jamkerjasalah);
                        return $response;
                    }
                }
                else {
                    //return trans('all.jumlahshifttidaksesuai');
                    $response['msg'] = trans('all.jumlahshifttidaksesuai');
                    return $response;
                }
            }

            //cek apakah setalah ditukar nanti ada yg kembar shiftnya?
            $sql = 'SELECT
                      IFNULL(GROUP_CONCAT(DISTINCT _js.tanggal ORDER BY _js.tanggal ASC SEPARATOR ","),"") as tanggalkembar
                    FROM
                      _jadwalshift_ab _js,
                      _jadwalshifttukar _jst
                    WHERE
                      _js.tanggal=_jst.tanggal AND
                      _js.idjamkerjashift=_jst.idjamkerjashift AND
                      _js.idpegawai=:idpegawai_a AND
                      _jst.idpegawai=:idpegawai_b';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai_a', $idpegawai_a);
            $stmt->bindParam(':idpegawai_b', $idpegawai_b);
            $stmt->execute();
            $row01 = $stmt->fetch(PDO::FETCH_ASSOC);
            $tanggalkembar = $row01['tanggalkembar'];
            if ($tanggalkembar!='') {
                //return trans('all.tanggalkembar').' '.$namapegawai_a.' '.Utils::tanggalCantik($tanggalkembar);
                $response['msg'] = trans('all.tanggalkembar').' '.$namapegawai_a.' '.Utils::tanggalCantik($tanggalkembar);
                return $response;
            }

            $sql = 'SELECT
                      IFNULL(GROUP_CONCAT(DISTINCT _js.tanggal ORDER BY _js.tanggal ASC SEPARATOR ","),"") as tanggalkembar 
                    FROM 
                      _jadwalshift_ab _js,
                      _jadwalshifttukar _jst
                    WHERE
                      _js.tanggal=_jst.tanggal AND
                      _js.idjamkerjashift=_jst.idjamkerjashift AND
                      _js.idpegawai=:idpegawai_b AND
                      _jst.idpegawai=:idpegawai_a';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai_a', $idpegawai_a);
            $stmt->bindParam(':idpegawai_b', $idpegawai_b);
            $stmt->execute();
            $row01 = $stmt->fetch(PDO::FETCH_ASSOC);
            $tanggalkembar = $row01['tanggalkembar'];
            if ($tanggalkembar!='') {
                //return trans('all.tanggalkembar').' '.$namapegawai_b.' '.Utils::tanggalCantik($tanggalkembar);
                $response['msg'] = trans('all.tanggalkembar').' '.$namapegawai_b.' '.Utils::tanggalCantik($tanggalkembar);
                return $response;
            }

            //simpan perubahan
            try {
                $pdo->beginTransaction();

                //simpan riwayat
                $sql = 'INSERT INTO jadwalshifttukar VALUES(NULL, :idpegawai_a, :periode_a, :idpegawai_b, :periode_b, "v", NOW())';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idpegawai_a', $idpegawai_a);
                $stmt->bindParam(':periode_a', $periode_a);
                $stmt->bindParam(':idpegawai_b', $idpegawai_b);
                $stmt->bindParam(':periode_b', $periode_b);
                $stmt->execute();

                $idjadwalshifttukar = $pdo->lastInsertId();

                $sql = 'INSERT INTO jadwalshifttukardetail SELECT NULL, :idjadwalshifttukar, IF(idpegawai=:idpegawai_a,"a",IF(idpegawai=:idpegawai_b,"b","")), tanggal, idjamkerjashift FROM _jadwalshifttukar';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idjadwalshifttukar', $idjadwalshifttukar);
                $stmt->bindParam(':idpegawai_a', $idpegawai_a);
                $stmt->bindParam(':idpegawai_b', $idpegawai_b);
                $stmt->execute();

                //eksekusi tukar shift
                $sql = 'UPDATE _jadwalshifttukar SET idpegawai=idpegawai*100';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();


                $sql = 'UPDATE _jadwalshifttukar SET idpegawai=:idpegawai_a WHERE idpegawai=:idpegawai_b*100';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idpegawai_a', $idpegawai_a);
                $stmt->bindParam(':idpegawai_b', $idpegawai_b);
                $stmt->execute();

                $sql = 'UPDATE _jadwalshifttukar SET idpegawai=:idpegawai_b WHERE idpegawai=:idpegawai_a*100';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idpegawai_a', $idpegawai_a);
                $stmt->bindParam(':idpegawai_b', $idpegawai_b);
                $stmt->execute();

                $sql = 'UPDATE _jadwalshifttukar _jst, jadwalshift js SET js.idpegawai=_jst.idpegawai WHERE _jst.id=js.id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                //update juga temporari-nya untuk memudahkan pengecekan null (libur) atau tidak
                $sql = 'UPDATE _jadwalshifttukar _jst, _jadwalshift_ab _jsab SET _jsab.idpegawai=_jst.idpegawai WHERE _jst.id=_jsab.id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                //hapus off pada tanggal ditukar yang mempunyai jadwal (ada null dan ada idjamkerjashift)
                $sql = 'SELECT
                          _jsab.idpegawai,
                          _jsab.tanggal
                        FROM
                          _jadwalshift_ab _jsab,
                          _jadwalshifttukar _jst
                        WHERE 
                          _jst.idpegawai=_jsab.idpegawai AND
                          _jst.tanggal=_jsab.tanggal
                        GROUP BY
                          _jsab.idpegawai, _jsab.tanggal
                        HAVING
                          SUM(IF(ISNULL(_jsab.idjamkerjashift)=true,1,0))>0 AND 
                          SUM(IF(ISNULL(_jsab.idjamkerjashift)=false,1,0))>0
                        ';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    //hapus juga di temporari table supada memudahkan untuk pengecekan **2
                    $sql01 = 'DELETE FROM _jadwalshift_ab WHERE ISNULL(idjamkerjashift)=true AND tanggal=:tanggal AND idpegawai=:idpegawai';
                    $stmt01 = $pdo->prepare($sql01);
                    $stmt01->bindParam(':tanggal', $row['tanggal']);
                    $stmt01->bindParam(':idpegawai', $row['idpegawai']);
                    $stmt01->execute();

                    $sql01 = 'DELETE FROM jadwalshift WHERE ISNULL(idjamkerjashift)=true AND tanggal=:tanggal AND idpegawai=:idpegawai';
                    $stmt01 = $pdo->prepare($sql01);
                    $stmt01->bindParam(':tanggal', $row['tanggal']);
                    $stmt01->bindParam(':idpegawai', $row['idpegawai']);
                    $stmt01->execute();
                }

                //**2 hapus off yang recordnya lebih dari 1. off off off --> menjadi off
                $sql = 'SELECT
                          idpegawai,
                          tanggal,
                          COUNT(*) as jumlah
                        FROM
                          _jadwalshift_ab
                        WHERE 
                          ISNULL(idjamkerjashift)=true
                        GROUP BY
                          idpegawai, tanggal
                        HAVING 
                          jumlah>1
                        ';
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $tanggal = $row['tanggal'];
                    $idpegawai = $row['idpegawai'];
                    $jumlah = $row['jumlah']-1;

                    //hapus juga di temporari table supada memudahkan untuk pengecekan **3
                    $sql01 = 'DELETE FROM _jadwalshift_ab WHERE tanggal=:tanggal AND idpegawai=:idpegawai AND ISNULL(idjamkerjashift)=true LIMIT :jumlah';
                    $stmt01 = $pdo->prepare($sql01);
                    $stmt01->bindParam(':tanggal', $tanggal);
                    $stmt01->bindParam(':idpegawai', $idpegawai);
                    $stmt01->bindParam(':jumlah', $jumlah);
                    $stmt01->execute();

                    $sql01 = 'DELETE FROM jadwalshift WHERE tanggal=:tanggal AND idpegawai=:idpegawai AND ISNULL(idjamkerjashift)=true LIMIT :jumlah';
                    $stmt01 = $pdo->prepare($sql01);
                    $stmt01->bindParam(':tanggal', $tanggal);
                    $stmt01->bindParam(':idpegawai', $idpegawai);
                    $stmt01->bindParam(':jumlah', $jumlah);
                    $stmt01->execute();
                }

                //**3 tambahkan off pada tanggal ditukar yang tidak mempunyai jadwal (stlh ditukar tidak ada data tapi tidak NULL)
                $sql = 'SELECT
                          IF(idpegawai=:idpegawai_a,:idpegawai_b1,(IF(idpegawai=:idpegawai_b,:idpegawai_a1,NULL))) as idpegawai00,
                          tanggal
                        FROM
                          _jadwalshifttukar
                        GROUP BY
                          idpegawai00,
                          tanggal
                        HAVING 
                          ISNULL(idpegawai00)=false
                        ';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':idpegawai_a', $idpegawai_a);
                $stmt->bindParam(':idpegawai_b1', $idpegawai_b);
                $stmt->bindParam(':idpegawai_b', $idpegawai_b);
                $stmt->bindParam(':idpegawai_a1', $idpegawai_a);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $tanggal = $row['tanggal'];
                    $idpegawai = $row['idpegawai00'];

                    $sql01 = 'SELECT COUNT(*) as jumlah FROM _jadwalshift_ab WHERE tanggal=:tanggal AND idpegawai=:idpegawai';
                    $stmt01 = $pdo->prepare($sql01);
                    $stmt01->bindParam(':tanggal', $tanggal);
                    $stmt01->bindParam(':idpegawai', $idpegawai);
                    $stmt01->execute();
                    $row01 = $stmt01->fetch(PDO::FETCH_ASSOC);
                    if ($row01['jumlah']==0) {
                        $sql02 = 'INSERT INTO jadwalshift VALUES(NULL, :tanggal, :idpegawai, NULL, NOW())';
                        $stmt02 = $pdo->prepare($sql02);
                        $stmt02->bindParam(':tanggal', $tanggal);
                        $stmt02->bindParam(':idpegawai', $idpegawai);
                        $stmt02->execute();
                    }
                }

                $pdo->commit();
                Utils::insertLogUser('Tukar Shift "'.$namapegawai_a.'" dengan "'.$namapegawai_b.'"');
                $response['status'] = 'OK';
                $response['msg'] = '';
                return $response;
            } catch(\Exception $e) {
                $pdo->rollBack();
                $response['msg'] = $e->getMessage();
                return $response;
            }
        }
        else {
            $response['msg'] = trans('all.jumlahshifttidaksesuai');
            return $response;
        }
    }

    public function querySubmit($pdo, $bulan, $tahun, &$data)
    {
        $totalhari = cal_days_in_month(CAL_GREGORIAN,$bulan,'20'.$tahun);

        $sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS temp_pegawai
                (
                    id      INT UNSIGNED NOT NULL,
                    nama    VARCHAR(100) NOT NULL,
                    PRIMARY KEY (id)
                ) Engine = Memory';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $sql = 'TRUNCATE temp_pegawai';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $wherepegawai = '';
        $batasan = Utils::getBatasanAtribut(Session::get('iduser_perusahaan'), true);
        if ($batasan!='') {
            $wherepegawai = ' AND id IN '.$batasan;
        }

        $where = '';
        if(Session::has('jadwalshift_atribut')){
            $atributs = Session::get('jadwalshift_atribut');
            $atributnilai = Utils::atributNilai($atributs);
            $where .= ' AND js.idpegawai IN (SELECT pa.idpegawai FROM pegawaiatribut pa WHERE pa.idatributnilai IN ('.$atributnilai.') AND status = "a")';
        }

        $sql = 'INSERT INTO temp_pegawai SELECT id,nama FROM pegawai WHERE status = "a" AND del = "t" AND idjamkerja IN (SELECT id FROM jamkerja WHERE jenis = "shift") '.$wherepegawai.$where.' ORDER BY nama';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // select pegawai yang jamkerjanya shift
        $sql = 'SELECT id,nama FROM temp_pegawai ORDER BY id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $i = 0;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[$i] = array();
            $data[$i]['id'] = $row['id'];
            $data[$i]['nama'] = $row['nama'];
            $data[$i]['jadwalperbulan'] = '<i style="cursor:pointer" class="fa fa-calendar-check-o" onclick="modalJadwalPerBulan('.$row['id'].')"></i>';

            $i++;
        }

        for ($j=1;$j<=$totalhari;$j++) {
            $sql = 'SELECT
                        tp.id,
                        IF(off.namashift="off","off",IFNULL(x.namashift,"")) as namashift
                    FROM
                        temp_pegawai tp
                        LEFT JOIN (
                            SELECT 
                                js.idpegawai,
                                GROUP_CONCAT(jks.namashift SEPARATOR "|") as namashift
                            FROM 
                                jadwalshift js,
                                jamkerjashift jks
                            WHERE
                                jks.id=js.idjamkerjashift AND
                                js.tanggal=STR_TO_DATE(:tanggal1, "%y%m%e")
                            GROUP BY idpegawai
                        ) x ON x.idpegawai=tp.id
                        LEFT JOIN (
                            SELECT 
                                js.idpegawai,
                                IFNULL(js.idjamkerjashift, "off") as namashift
                            FROM 
                                jadwalshift js
                            WHERE
                                js.tanggal=STR_TO_DATE(:tanggal2, "%y%m%e")
                            GROUP BY idpegawai
                        ) off ON off.idpegawai=tp.id
                    ORDER BY
                        tp.id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal1', $tahun.$bulan.$j);
            $stmt->bindValue(':tanggal2', $tahun.$bulan.$j);
            $stmt->execute();

            $i = 0;
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $namashift = '';
                if ($row['namashift']!='') {
                    if($row['namashift'] == 'off'){
                        $namashift .= '<i style="padding:3px;color:red" class="fa fa-ban"></i>';
                    }else {
                        $arrNamaShift = explode('|', $row['namashift']);
                        for ($k = 0; $k < count($arrNamaShift); $k++) {
                            $namashift .= '<span style="padding:3px;background-color:' . Utils::getColorBackground($arrNamaShift[$k]) . ';color:' . Utils::getColorForeground($arrNamaShift[$k]) . ' !important" class="label">' . Utils::getFirstCharInWord($arrNamaShift[$k]) . '</span>';
                        }
                    }
                }
                $data[$i][$j] = $namashift;
                $i++;
            }
        }
    }

    public function getJamKerjaShift()
    {
        // legend
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //select jamkerja yg shift
        $sql = 'SELECT jk.nama, GROUP_CONCAT(DISTINCT jks.namashift ORDER BY jks.namashift SEPARATOR "|") as namashift FROM jamkerja jk, jamkerjashift jks WHERE jk.jenis="shift" AND jk.id=jks.idjamkerja GROUP BY jk.id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $jamkerjashift = array();
        $i=0;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $jamkerjashift[$i] = '<i><b>'.$row['nama'].'</b></i> : ';
            $arrshift = explode('|', $row['namashift']);
            for($j=0;$j<count($arrshift);$j++){
                $jamkerjashift[$i] = $jamkerjashift[$i].'<span style="padding:3px;background-color:'.Utils::getColorBackground($arrshift[$j]).';color:'.Utils::getColorForeground($arrshift[$j]).' !important;" class="label">'.Utils::getFirstCharInWord($arrshift[$j]).'</span>&nbsp;'.$arrshift[$j].'&nbsp;&nbsp;';
            }
            $jamkerjashift[$i] = $jamkerjashift[$i].'<br>';
            $i++;
        }
        return $jamkerjashift;
    }

    public function getPeriode()
    {
        $pdo = DB::getPdo();
        $sql = 'SELECT listyymm(3) as periode';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $listyymm = substr($row['periode'],0, -1);
        //explode |
        $listyymm_ex = explode('|', $listyymm);
        $arryymm = array();
        $arrbulan = array('', trans('all.januari'), trans('all.februari'), trans('all.maret'), trans('all.april'), trans('all.mei'), trans('all.juni'), trans('all.juli'), trans('all.agustus'), trans('all.september'), trans('all.oktober'), trans('all.november'), trans('all.desember'));
        for($i=0;$i<count($listyymm_ex);$i++){
            $tahun = substr($listyymm_ex[$i], 0, -2);
            $bulan = substr($listyymm_ex[$i], -2);
            if ($bulan[0] == 0) {
                $bulan = str_replace('0', '', $bulan);
            }
            $periode = ' ' . $arrbulan[$bulan] . ' 20' . $tahun;

            $arryymm[$i]['isi'] = $listyymm_ex[$i]; //isi nya yymm = 1701
            $arryymm[$i]['tampilan'] = $periode; //isinya januari 2017
        }
        return $arryymm;
    }

    public function koreksiShiftgetIndex()
    {
        if(Utils::cekHakakses('jadwalshift','lum')){
            $periode = $this->getPeriode();
            Utils::insertLogUser('akses menu ubah tukar shift');
            return view('datainduk/absensi/tukarshift/koreksi', ['menu' => "tukarshift", 'jenis' => 'koreksishift', 'periode' => $periode]);
        } else {
            return redirect("/");
        }
    }

    public function koreksiShiftSubmitTampilkan(request $request)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idpegawai = $request->pegawai;
        $periode = $request->periode; //yymm
        $tahun = substr($periode, 0, -2);
        $bulan = substr($periode, -2);

        //set session idpegawai dan periode
        Session::set('koreksishift_idpegawai_'.$request->dari, $idpegawai);
        Session::set('koreksishift_periode_'.$request->dari, $periode);

        $yymm = $periode;
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
            $sql = 'CALL pegawaishiftpertanggal(:idpegawai, :yymm)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idpegawai', $idpegawai);
            $stmt->bindParam(':yymm', $yymm);
            $stmt->execute();

            $i = 0;

            //ambil data jadwal
            $sql = 'SELECT
                        tanggal,
                        DAY(tanggal) as hanyatanggal,
                        dayinweek,
                        idjamkerja,
                        nama,
                        jenis,
                        harilibur,
                        idijintidakmasuk,
                        alasantidakmasuk,
                        keterangantidakmasuk
                    FROM
                        _jadwalshift';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[$i]['tanggal'] = $row['tanggal'];
                $data[$i]['hanyatanggal'] = $row['hanyatanggal'];
                $data[$i]['hari'] = Utils::getHari($row['dayinweek']);
                $data[$i]['idjamkerja'] = $row['idjamkerja'];
                $data[$i]['nama'] = $row['nama'];
                $data[$i]['jenis'] = $row['jenis'];
                $data[$i]['harilibur'] = $row['harilibur'];
                $data[$i]['idijintidakmasuk'] = $row['idijintidakmasuk'];
                $data[$i]['ijintidakmasuk'] = $row['alasantidakmasuk'];
                $data[$i]['ijintidakmasukketerangan'] = $row['keterangantidakmasuk'];
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

        $bln = $bulan;
        if ($bulan[0] == 0) {
            $bln = str_replace('0', '', $bulan);
        }
        $periode = ' ' . Utils::getBulan($bln) . ' 20' . $tahun;
        Utils::insertLogUser('akses menu ubah tukrashift detail');
        return view('datainduk/absensi/tukarshift/koreksidetail', ['data' => $data, 'idpegawai' => $idpegawai, 'namapegawai' => $namapegawai, 'pinpegawai' => $pinpegawai, 'legend_harilibur' => $legend_harilibur, 'legend_ijintidakmasuk' => $legend_ijintidakmasuk, 'dari' => $request->dari, 'periode' => $periode]);
    }

    public function koreksiShiftSubmit(Request $request)
    {
        //set session idpegawai dan periode
        $idpegawai_1 = Session::get('koreksishift_idpegawai_1');
        $idpegawai_2 = Session::get('koreksishift_idpegawai_2');
        $periode_1 = Session::get('koreksishift_periode_1');
        $periode_2 = Session::get('koreksishift_periode_2');
        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'unknown';

        //cek apakah ada session idpegawai 1 dan 2
        if(isset($idpegawai_1) && isset($idpegawai_2)) {
            $pdo = DB::connection('perusahaan_db')->getPdo();
            try
            {
                $pdo->beginTransaction();

                //pegawai_1
                for ($i = 1; $i <= $request->totalhari_1; $i++) {
                    $tgl = $periode_1.$i; //yme(17011) tahun 2 digit terakhir,bulan,tanggal tanpa 0
                    // hapus data lama pegawai_1
                    Utils::hapusJadwalShift($idpegawai_1, $tgl);
                    //looping totaldata pegawai_1
                    for ($j = 0; $j < count($request->input('jadwalshift_' . $i . '_1')); $j++) {
                        // simpan data baru pegawai_1
                        if ($request->input('jadwalshift_' . $i . '_1')[$j] != '') {
                            $sql = 'INSERT INTO jadwalshift VALUES(NULL,STR_TO_DATE(:tanggal, "%y%m%e"),:idpegawai,:idjamkerjashift,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tanggal', $tgl);
                            $stmt->bindValue(':idpegawai', $idpegawai_1);
                            $stmt->bindValue(':idjamkerjashift', $request->input('jadwalshift_' . $i . '_1')[$j]);
                            $stmt->execute();
                        } else {
                            $sql = 'INSERT INTO jadwalshift VALUES(NULL,STR_TO_DATE(:tanggal, "%y%m%e"),:idpegawai,NULL,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tanggal', $tgl);
                            $stmt->bindValue(':idpegawai', $idpegawai_1);
                            $stmt->execute();
                        }
                    }
                }

                //pegawai_2
                for ($i = 1; $i <= $request->totalhari_2; $i++) {
                    $tgl = $periode_2.$i;
                    // hapus data lama pegawai_2
                    Utils::hapusJadwalShift($idpegawai_2, $tgl);
                    //looping totaldata pegawai_2
                    for ($j = 0; $j < count($request->input('jadwalshift_' . $i . '_2')); $j++) {
                        // simpan data baru pegawai_2
                        if ($request->input('jadwalshift_' . $i . '_2')[$j] != '') {
                            $sql = 'INSERT INTO jadwalshift VALUES(NULL,STR_TO_DATE(:tanggal, "%y%m%e"),:idpegawai,:idjamkerjashift,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tanggal', $tgl);
                            $stmt->bindValue(':idpegawai', $idpegawai_2);
                            $stmt->bindValue(':idjamkerjashift', $request->input('jadwalshift_' . $i . '_2')[$j]);
                            $stmt->execute();
                        } else {
                            $sql = 'INSERT INTO jadwalshift VALUES(NULL,STR_TO_DATE(:tanggal, "%y%m%e"),:idpegawai,NULL,NOW())';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':tanggal', $tgl);
                            $stmt->bindValue(':idpegawai', $idpegawai_2);
                            $stmt->execute();
                        }
                    }
                }

                $pdo->commit();
                $namapegawai1 = Utils::getNamaPegawai($idpegawai_1);
                $namapegawai2 = Utils::getNamaPegawai($idpegawai_2);
                Utils::insertLogUser('Koreksi Shift "'.$namapegawai1.'" dan "'.$namapegawai2.'"');
                $response['status'] = 'OK';
                $response['msg'] = 'unknown';
            } catch (\Exception $e) {
                $pdo->rollBack();
                $response['msg'] = $e->getMessage();
            }
        }else{
            $response['msg'] = trans('all.datatidakditemukan');
        }
        return $response;
    }
}