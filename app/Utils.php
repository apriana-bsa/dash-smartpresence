<?php

namespace App;
require_once __DIR__ . '/../vendor/autoload.php';

use Lcobucci\JWT\Parser;
use Illuminate\Support\Facades\Session;
use DB;
use PDO;
use PHPExcel_Worksheet_MemoryDrawing;
use PHPExcel_Style_Alignment;
use ReCaptcha\ReCaptcha;
use DateTime;
use SaferEval;
use Config;
use PDF;
use Mail;
use NumberFormatter;
use Log;

class Utils
{
	private static $iv = 'ba18723f635d8c62';
    private static $key = '5284069c2bf03b31';

	public static function generateRandomString($length = 4) {
	    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	public static function generateRandomAngka($length = 4) {
	    $characters = '0123456789';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	public static function kirimSms($notujuan,$isisms)
	{
		if (trim($notujuan)!='' && trim($isisms)!='') {
			$isisms = substr($isisms, 0, 159);

			//$url = 'http://do.seonsms.com/sender.php';
            $url = 'http://bimasaktischool.com/forwarder-seon/index.php';
			$tujuan=$notujuan;
			$isi=base64_encode($isisms);
			$username=strtolower('demo100');
			$password='demo';
			$hash_password=md5(strtolower('hash01='.$tujuan.$username.md5($password)));
			$input_xml = ('<?xml version="1.0"?><pesan5326fcfde15a5228c62782d1e3e8989b><tujuan2997c1c7ced12dcc6dae2e8fb4d84a37>'.$tujuan.'</tujuan2997c1c7ced12dcc6dae2e8fb4d84a37><isia7a1b335247554be6612f583e32b64cb>'.$isi.'</isia7a1b335247554be6612f583e32b64cb><username14c4b06b824ec593239362517f538b29>'.$username.'</username14c4b06b824ec593239362517f538b29><hash0800fc577294c34e0b28ad2839435945>'.$hash_password.'</hash0800fc577294c34e0b28ad2839435945></pesan5326fcfde15a5228c62782d1e3e8989b>');
			$headers = array(
				'Content-type: text/xml;charset=\'utf-8\'',
				'Accept: text/xml',
				'Cache-Control: no-cache',
				'Pragma: no-cache',
				'SOAPAction: \'run\''
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$input_xml);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$data = curl_exec($ch);
			curl_close($ch);

			return $data;
		}
	}

	public static function id2Folder($idpegawai) {
		return strval(floor($idpegawai / 100)+1);
	}

    public static function pkcs5_pad ($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        return substr($text, 0, -1 * $pad);
    }

    public static function encrypt($filename)
    {
        $text = file_get_contents($filename);
        $enkrip = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, self::$key, self::pkcs5_pad($text, 16), MCRYPT_MODE_CBC, self::$iv);
        return $enkrip;
    }

    public static function decrypt($filename)
    {
        $text = file_get_contents($filename);
        $dekrip = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, self::$key, $text, MCRYPT_MODE_CBC, self::$iv);
        return self::pkcs5_unpad($dekrip);
    }

    public static function angkaToHuruf($num) {
		$numeric = ($num - 1) % 26;
		$letter = chr(65 + $numeric);
		$num2 = intval(($num - 1) / 26);
		if ($num2 > 0) {
		    return self::angkaToHuruf($num2) . $letter;
		} else {
		    return $letter;
		}
	}

    public static function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir.'/'.$object) == 'dir')
                        self::rrmdir($dir.'/'.$object);
                    else
                        unlink   ($dir.'/'.$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public static function getFirstCharInWord($param)
    {
        $words = preg_split('/[\s,_-]+/', trim($param));
        $acronym = '';
        foreach ($words as $w) {
            $acronym .= $w[0];
        }
        return strtoupper(str_replace(array('(',')','[',']','{','}'), '', $acronym));
    }

    public static function getColorBackground($param)
    {
        $color = array ('#ca7aa9','#cc9911','#194444','#7744dd','#f7dd2e','#b3f9ff','#D1EEFC','#dd2266','#ff33aa');
        $total=0;
        for ($i=0;$i<strlen($param);$i++) {
            $total=$total+ord(substr($param,$i,1));
        }
        return $color[$total % sizeof($color)];
    }

    public static function getColorForeground($param)
    {
        $color = array ('#ffffff','#ffffff','#ffffff','#ffffff','#000000','#000000','#000000','#ffffff','#ffffff');
        $total=0;
        for ($i=0;$i<strlen($param);$i++) {
            $total=$total+ord(substr($param,$i,1));
        }
        return $color[$total % sizeof($color)];
    }

    public static function getAtribut($where = '')
    {
        $sqlwhere = '';
        $batasan = self::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
        if ($batasan!='') {
            $sqlwhere .= ' AND an.id IN '.$batasan;
        }

        // ex $where = (1,2,3)
        if($where != ''){
            $sqlwhere .= ' AND an.id IN '.$where;
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT a.id,a.atribut FROM atribut a,atributnilai an WHERE an.idatribut=a.id '.$sqlwhere.' GROUP BY a.id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $atributs = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($atributs as $row) {
            // ambil data atributnilai
            $sql = 'SELECT
                        an.id,
                        an.nilai
                    FROM
                        atributnilai an
                    WHERE
                        an.idatribut=:idatribut
                        '.$sqlwhere.'
                    ORDER BY
                        an.nilai';
            $stmt2 = $pdo->prepare($sql);
            $stmt2->bindValue(':idatribut', $row->id);
            $stmt2->execute();

            $row->atributnilai=$stmt2->fetchAll(PDO::FETCH_OBJ);
        }

        return $atributs;
    }

    public static function getAtributShift()
    {
        $batasan = self::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
        if ($batasan!='') {
            $batasan = ' AND an.id IN '.$batasan;
        }

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT
                  a.id,
                  a.atribut
                FROM
                  pegawai p,
                  pegawaiatribut pa,
                  pegawaijamkerja pj,
                  jamkerja jk,
                  atribut a,
                  atributnilai an
                WHERE
                  p.status="a"AND
                  p.del = "t" AND
                  p.id=pj.idpegawai AND
                  jk.id=pj.idjamkerja AND
                  jk.jenis="shift" AND
                  p.id=pa.idpegawai AND
                  pa.idatributnilai=an.id AND
                  an.idatribut=a.id'.$batasan.'
                GROUP BY a.id
                ORDER BY atribut';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $atributs = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($atributs as $row) {
            // ambil data atributnilai
            $sql = 'SELECT
                        an.id,
                        an.nilai
                    FROM
                        pegawai p,
                        pegawaiatribut pa,
                        pegawaijamkerja pj,
                        jamkerja jk,
                        atributnilai an
                    WHERE
                        p.status="a" AND
                        p.del = "t" AND
                        p.id=pj.idpegawai AND
                        jk.id=pj.idjamkerja AND
                        jk.jenis="shift" AND
                        p.id=pa.idpegawai AND
                        pa.idatributnilai=an.id AND
                        an.idatribut=:idatribut
                        '.$batasan.'
                    GROUP BY
                        an.id
                    ORDER BY
                        an.nilai';
            $stmt2 = $pdo->prepare($sql);
            $stmt2->bindValue(':idatribut', $row->id);
            $stmt2->execute();

            $row->atributnilai=$stmt2->fetchAll(PDO::FETCH_OBJ);
        }

        return $atributs;
    }

    public static function getAtributSelected($atributnilai)
    {
        $atributnilaidipilih = '';
        if($atributnilai != ''){
            $pdo = DB::connection('perusahaan_db')->getPdo();
            $sql = 'SELECT
                        a.atribut,
                        GROUP_CONCAT(an.nilai) as nilai
                    FROM
                        atribut a,
                        atributnilai an
                    WHERE
                        a.id=an.idatribut AND
                        an.id IN ('.$atributnilai.')
                    GROUP BY
                        a.id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $atributnilaidipilih = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        return $atributnilaidipilih;
    }

    public static function splitArray($param,$separator=',')
    {
        $totaldata = count($param);
        $data = '';
        if($totaldata > 0){
            for($i=0;$i<$totaldata;$i++){
                $data .= $param[$i].$separator;
            }
            $data = substr($data, 0, -1);
        }
        return $data;
    }

    public static function atributNilai($atributs)
    {
//        $totalatribut = $atributs != '' ? count($atributs) : 0;
//        $atributnilai = '';
//        if($totalatribut > 0){
//            for($i=0;$i<$totalatribut;$i++){
//                $atributnilai .= $atributs[$i].',';
//            }
//            $atributnilai = substr($atributnilai, 0, -1);
//        }
        return $atributs != '' ? implode(',', $atributs) : '';
    }

    public static function atributNilaiKeterangan($atributnilaidipilih)
    {
        $atributnilaiketerangan = '';
        foreach($atributnilaidipilih as $key){
            $atributnilaiketerangan .= $key->atribut.' ('.$key->nilai.'), ';
        }
        $atributnilaiketerangan = trans('all.atribut').' : '.substr($atributnilaiketerangan, 0, -2);
        return $atributnilaiketerangan;
    }

    public static function getBatasanAtribut($iduser,$sqlmode) {
        $batasan = '';
        $pdo = DB::getPdo();
        $sql = 'SELECT email FROM `user` WHERE id=:iduser LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':iduser', $iduser);
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            return '()';
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $row['email'];

        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT IFNULL(GROUP_CONCAT(idatributnilai SEPARATOR ","),"") as batasan FROM batasanemail be, batasanatribut ba WHERE be.idbatasan=ba.idbatasan AND be.email=:email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $batasan = $row['batasan'];
        }

        if ($batasan!='') {
            if ($sqlmode==true) {
                $batasan = '(SELECT DISTINCT(p.id) FROM pegawai p, pegawaiatribut pa WHERE p.id=pa.idpegawai AND pa.idatributnilai IN ('.$batasan.') AND p.status = "a" AND p.del = "t")';
            }
            else {
                $batasan = '('.$batasan.')';
            }
        }

        return $batasan;
    }

    public static function getBatasanPekerjaanKategori($iduser) {
        $batasan = '';
        $pdo = DB::getPdo();
        $sql = 'SELECT email FROM `user` WHERE id=:iduser LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':iduser', $iduser);
        $stmt->execute();
        if ($stmt->rowCount()==0) {
            return '()';
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $row['email'];
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT IFNULL(GROUP_CONCAT(idpekerjaankategori SEPARATOR ","),"") as batasan FROM batasanemail be, batasanpekerjaankategori bpk WHERE be.idbatasan=bpk.idbatasan AND be.email=:email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $batasan = $row['batasan'];
        }

        if ($batasan!='') {
            $batasan = '('.$batasan.')';
        }

        return $batasan;
    }

    public static function getAtributPenting()
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id, md5(CONCAT(atribut,"_atributpenting",id)) as nama,atribut FROM atribut WHERE penting="y" ORDER BY nama';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $atributpenting = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $atributpenting;
    }

    public static function getAtributPentingQuery($paramidpegawai)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $atributpenting = '';
        $sql = 'SELECT id, md5(CONCAT(atribut,"_atributpenting",id)) as nama FROM atribut WHERE penting="y" ORDER BY nama';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $atributpenting = $atributpenting . 'getatributpegawai('.$paramidpegawai.', '.$row['id'].') as "'.$row['nama'].'",';
        }

        return $atributpenting;
    }

    public static function getAllAtribut($jenis,$where = '')
    {
        $hasil = '';
        $pdo = DB::connection('perusahaan_db')->getPdo();
        if($jenis == 'blade'){
            $sql = 'CALL getpegawailengkap_blade(@_atributpenting_controller, @_atributpenting_blade, @_atributvariablepenting_controller, @_atributvariablepenting_blade)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'SELECT @_atributpenting_controller as atributpenting_controller, @_atributpenting_blade as atributpenting_blade, @_atributvariablepenting_controller as atributvariablepenting_controller, @_atributvariablepenting_blade as atributvariablepenting_blade';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $hasil = $stmt->fetch(PDO::FETCH_ASSOC);
        }else if($jenis == 'controller'){
            $sql = 'CALL getpegawailengkap_controller(@_atributpenting, @_atributvariablepenting,"'.str_replace('"', "'", $where).'")';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sql = 'SELECT @_atributpenting as atributpenting, @_atributvariablepenting as atributvariablepenting';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $hasil = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $hasil;
    }

    public static function getAtributVariable()
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id, md5(CONCAT(atribut,"_atributvariable",id)) as nama,atribut FROM atributvariable WHERE penting = "y" ORDER BY nama';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $atributvariable = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $atributvariable;
    }

    public static function getAtributVariableQuery($paramidpegawai)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $atributvariable = '';
        $sql = 'SELECT id, md5(CONCAT(atribut,"_atributvariable",id)) as nama FROM atributvariable WHERE penting = "y" ORDER BY nama';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $atributvariable = $atributvariable . 'getatributvariablepegawai('.$paramidpegawai.', '.$row['id'].') as "'.$row['nama'].'",';
        }

        return $atributvariable;
    }

    public static function jamKerja($idjamkerja)
    {
        $totalatribut = count($idjamkerja);
        $jamkerja = '';
        if($totalatribut > 0){
            for($i=0;$i<$totalatribut;$i++){
                $jamkerja .= $idjamkerja[$i].',';
            }
            $jamkerja = substr($jamkerja, 0, -1);
        }
        return $jamkerja;
    }

    public static function cekPegawaiJumlah($alldata=true)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $where = '';
        if(!$alldata){
            $where = ' AND del = "t"';
        }
        $sql = 'SELECT id FROM pegawai WHERE 1=1 '.$where;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $totalpegawai = $stmt->rowCount();

        $pdo = DB::getPdo();
        $sql = 'SELECT limitpegawai FROM perusahaan_kuota WHERE idperusahaan = :idperusahaan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $limitpegawai = $row['limitpegawai'];

        $hasil = false;
        if($totalpegawai < $limitpegawai){
            //masih belum limit
            $hasil = true;
        }

        return $hasil;

    }

    public static function kirimGCM($to, $cmd, $sender, $msg) {
        if ($to != '') {
            // Set POST variables
            //$url = 'https://gcm-http.googleapis.com/gcm/send';
            $url = 'https://fcm.googleapis.com/fcm/send';

            $headers = array(
                'Authorization: key=' . config('consts.GCM_API_KEY'),
                'Content-Type: application/json'
            );

            $fields = array(
                'content_available' => true,
                'to' => $to,
                'data' => array(
                    'cmd' => $cmd,
                    'sender' => $sender,
                    'msg' => $msg
                )
            );

            // Open connection
            $ch = curl_init();

            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  0);

            curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
            curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

            // Execute post
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }

            // Close connection
            curl_close($ch);
        }
    }

    public static function kirimGCMSync($idperusahaan) {
        // Set POST variables
        //$url = 'https://gcm-http.googleapis.com/gcm/send';
        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = array(
            'Authorization: key=' . config('consts.GCM_API_KEY'),
            'Content-Type: application/json'
        );

        $fields = array(
            'content_available' => true,
            'to' => '/topics/mesin_'.$idperusahaan,
            'data' => array(
                'cmd' => 'sync',
                'sender' => 'server',
                'msg' => ''
            )
        );

        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  0);

        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);
    }

    public static function insertLogUser($keterangan) {
        //insert ke log user
        $pdo = DB::getPdo();
        $idperusahaan = NULL;
        if(Session::has('conf_webperusahaan')){
            $idperusahaan = Session::get('conf_webperusahaan');
        }
        $sql = 'INSERT INTO _loguser VALUES(NULL,NOW(),:iduser,:idperusahaan,:keterangan,"WEB","","",:superuser)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        $stmt->bindValue(':idperusahaan', $idperusahaan);
        $stmt->bindValue(':keterangan', $keterangan);
        $stmt->bindValue(':superuser', Session::get('superuser_perusahaan'));
        $stmt->execute();
    }

    public static function tanggalCantik($tanggal,$jenis="singkat")
    {
        $hasil = '';
        $jam = '';
        if($tanggal != '') {
            if (self::cekDateTime($tanggal)) {
                if (strlen($tanggal) == 19) {
                    //format yy-mm-dd hh:mm:ss
                    $split = explode(' ', $tanggal);
                    $splittgl = explode('-', $split[0]);
                    $tgl = $splittgl[2] + 0;
                    $bln = $splittgl[1] + 0;
                    $tahun = $splittgl[0];
                    $jam = $split[1];
                } else {
                    if (strpos($tanggal, '-') !== false) {
                        //format yy-mm-dd
                        $split = explode('-', $tanggal);
                        $tgl = $split[2] + 0;
                        $bln = $split[1] + 0;
                        $tahun = $split[0];
                    } else {
                        //format yymmdd
                        $tgl = substr($tanggal, -2) + 0;
                        $tgl1 = substr($tanggal, -2);
                        $bln = substr($tanggal, 4, -2) + 0;
                        $bln1 = substr($tanggal, 4, -2);
                        $tahun = substr($tanggal, 0, -4);
                        if (strlen($tanggal) == 7) {
                            //format yymmd
                            $tgl = substr($tanggal, -1) + 0;
                            $tgl1 = '0' . substr($tanggal, -1);
                            $bln = substr($tanggal, 4, -1) + 0;
                            $bln1 = substr($tanggal, 4, -1);
                            $tahun = substr($tanggal, 0, -3);
                        }
                        $tanggal = $tahun . $bln1 . $tgl1;
                    }
                }
                $pdo = DB::getPdo();

                //cari hari
                if (strlen($tanggal) == 19) {
                    $sql = 'SELECT DAYOFWEEK(DATE_FORMAT(:tanggal,"%Y-%m-%d %T")) as tgl';
                } else if (strpos($tanggal, '-') !== false) {
                    $sql = 'SELECT DAYOFWEEK(:tanggal) as tgl';
                } else {
                    $sql = 'SELECT DAYOFWEEK(DATE_FORMAT(:tanggal,"%Y%m%d")) as tgl';
                }
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':tanggal', $tanggal);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $hari = $row['tgl'];

                $hasil = self::getHari($hari, $jenis) . ', ' . $tgl . ' ' . self::getBulan($bln, $jenis) . ' ' . $tahun . ' ' . $jam;
            }
        }
        return $hasil;
    }

    public static function tanggalCantikDariSampai($tanggaldari,$tanggalsampai)
    {
        //formattanggal harus yyyy-mm-dd
        $hasil = '';
        if($tanggaldari != '' and $tanggalsampai != ''){
            $pdo = DB::getPdo();

            //tanggal dari
            //split tanggal
            $split = explode('-', $tanggaldari);
            $tgldari = $split[2]+0;
            $tahundari = $split[0];

            //cari bulan tanggal dari
            $sql = 'SELECT MONTH(:tanggal) as bln';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal', $tanggaldari);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $bulandari = $row['bln'];

            //tanggal sampai
            //split tanggal
            $split = explode('-', $tanggalsampai);
            $tglsampai = $split[2]+0;
            $tahunsampai = $split[0];

            //cari bulan tanggal sampai
            $sql = 'SELECT MONTH(:tanggal) as bln';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':tanggal', $tanggalsampai);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $bulansampai = $row['bln'];

            if($tahundari != $tahunsampai){
                $hasil = $tgldari.' '.self::getBulan($bulandari, 'singkat').' '.$tahundari.' - '.$tglsampai.' '.self::getBulan($bulansampai, 'singkat').' '.$tahunsampai;
            }else{
                if($bulandari != $bulansampai){
                    $hasil = $tgldari.' '.self::getBulan($bulandari, 'singkat').' - '.$tglsampai.' '.self::getBulan($bulansampai, 'singkat').' '.$tahunsampai;
                }else{
                    if($tgldari != $tglsampai){
                        $hasil = $tgldari.' - '.$tglsampai.' '.self::getBulan($bulansampai, 'singkat').' '.$tahunsampai;
                    }else{
                        $hasil = $tgldari.' '.self::getBulan($bulandari, 'singkat').' '.$tahunsampai;
                    }
                }
            }
        }
        return $hasil;
    }

    public static function list_yymm($interval = 0) {
        $pdo = DB::getPdo();
        $sql = 'SELECT listyymm('.$interval.') as yymm';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $yymm_ex = explode('|', substr($row['yymm'], 0, -1));
        $arrhasil = array();
        for($i=0;$i<count($yymm_ex);$i++){
            $arrhasil[$i]['isi'] = $yymm_ex[$i];
            $tahun = '20'.substr($yymm_ex[$i], 0, 2);
            $bln = intval(substr($yymm_ex[$i], 2));

            $arrbulan = array("",trans('all.januari'),trans('all.februari'),trans('all.maret'),trans('all.april'),trans('all.mei'),trans('all.juni'),trans('all.juli'),trans('all.agustus'),trans('all.september'),trans('all.oktober'),trans('all.november'),trans('all.desember'));
            $arrhasil[$i]['tampilan'] = $arrbulan[$bln].' '.$tahun;

        }
        return $arrhasil;
    }

    public static function periodeCantik($yymm) {
        $hasil = '';
        if($yymm != ''){
            $tahun = '20'.substr($yymm, 0, 2);
            $bln = intval(substr($yymm, 2));

            $arrbulan = array("",trans('all.januari'),trans('all.februari'),trans('all.maret'),trans('all.april'),trans('all.mei'),trans('all.juni'),trans('all.juli'),trans('all.agustus'),trans('all.september'),trans('all.oktober'),trans('all.november'),trans('all.desember'));
            $hasil = $arrbulan[$bln].' '.$tahun;
        }
        return $hasil;
    }

    public static function getHari($hari,$singkat='')
    {
        if($singkat == 'singkat') {
            $arrhari = array("", trans('all.singkatminggu'), trans('all.singkatsenin'), trans('all.singkatselasa'), trans('all.singkatrabu'), trans('all.singkatkamis'), trans('all.singkatjumat'), trans('all.singkatsabtu'));
        }else{
            $arrhari = array("", trans('all.minggu'), trans('all.senin'), trans('all.selasa'), trans('all.rabu'), trans('all.kamis'), trans('all.jumat'), trans('all.sabtu'));
        }
        return $arrhari[$hari];
    }

    public static function getBulan($bulan, $singkat = '')
    {
        $bulan = strval(intval($bulan));
//        $bulan = $bulan[0] == 0 ? $bulan[1] : $bulan;
        if ($singkat == 'singkat') {
            $arrbulan = array("", trans('all.singkatjanuari'), trans('all.singkatfebruari'), trans('all.singkatmaret'), trans('all.singkatapril'), trans('all.singkatmei'), trans('all.singkatjuni'), trans('all.singkatjuli'), trans('all.singkatagustus'), trans('all.singkatseptember'), trans('all.singkatoktober'), trans('all.singkatnovember'), trans('all.singkatdesember'));
        }
        else {
            $arrbulan = array("", trans('all.januari'), trans('all.februari'), trans('all.maret'), trans('all.april'), trans('all.mei'), trans('all.juni'), trans('all.juli'), trans('all.agustus'), trans('all.september'), trans('all.oktober'), trans('all.november'), trans('all.desember'));
        }
        return $arrbulan[$bulan];
    }

    /**
     * ubah dari int (bulan) menjadi bulan dengan bahasa Indonesia.
     * misal 1 menjadi Januari, 12 menjadi Desember
     * Param:
     * $bulan = int (bulan)
     * $singkat = string 'singkat', untuk return bulan dengan singkatan
     *
     * Return:
     * Januari
     */
    public static function getBulanId($bulan, $singkat = '')
    {
        $bulan = strval(intval($bulan));
        if ($singkat == 'singkat') {
            $arrbulan = array("", 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
        }
        else {
            $arrbulan = array("", 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
        }
        return $arrbulan[$bulan];
    }

    public static function getMesin($arrmesin)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idmesin = implode(',',$arrmesin);
        $sql = 'SELECT nama FROM mesin WHERE id IN('.$idmesin.')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getLokasi($arrlokasi)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $idmesin = implode(',',$arrlokasi);
        $sql = 'SELECT nama FROM lokasi WHERE id IN('.$idmesin.')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getAtributdanAtributNilaiCrud($id, $jenis, $batasan=true)
    {

        //cek email user yang login
        $pdo2 = DB::getPdo();
        $sql = 'SELECT email FROM `user` WHERE id=:iduser LIMIT 1';
        $stmt = $pdo2->prepare($sql);
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $row['email'];
        $sqlFrom = '';

        $pdo = DB::connection('perusahaan_db')->getPdo();

        $idtabeljenis = 'pa.id';
        if($jenis == 'pegawai'){
            $sqlFrom = 'LEFT JOIN pegawaiatribut pa ON pa.idatributnilai=an.id AND pa.idpegawai = :id';
        }else if($jenis == 'mesin'){
            $sqlFrom = 'LEFT JOIN mesinatribut pa ON pa.idatributnilai=an.id AND pa.idmesin=:id';
        }else if($jenis == 'harilibur'){
            $sqlFrom = 'LEFT JOIN hariliburatribut pa ON pa.idatributnilai=an.id AND pa.idharilibur=:id';
        }else if($jenis == 'batasan'){
            $sqlFrom = 'LEFT JOIN batasanatribut pa ON pa.idatributnilai=an.id AND pa.idbatasan=:id';
        }else if($jenis == 'tvgroup'){
            $sqlFrom = 'LEFT JOIN tvgroupatribut pa ON pa.idatributnilai=an.id AND pa.idtvgroup=:id';
            $idtabeljenis = 'pa.idatributnilai';
        }else if($jenis == 'indexlembur'){
            $sqlFrom = 'LEFT JOIN indexlembur_atribut pa ON pa.idatributnilai=an.id AND pa.idindexlembur=:id';
        }else if($jenis == 'indexjamkerja'){
            $sqlFrom = 'LEFT JOIN indexjamkerja_atribut pa ON pa.idatributnilai=an.id AND pa.idindexjamkerja=:id';
        }else if($jenis == 'aktivitaskategori'){
            $sqlFrom = 'LEFT JOIN aktivitas_kategori_atribut pa ON pa.idatributnilai=an.id AND pa.idaktivitaskategori=:id';
        }

        $wherebatasan = '';
        if($batasan) {
            $idbatasan = self::getBatasanAtribut(Session::get('iduser_perusahaan'), false);
            if ($idbatasan != '') {
                $wherebatasan = ' AND at.id IN(SELECT idatribut FROM atributnilai WHERE id IN ' . $idbatasan . ')';
            }
        }

        //atribut nilai
        if($id == 0) {
            $sql = 'SELECT at.id,at.atribut,at.jumlahinputan FROM atribut at WHERE 1=1 '.$wherebatasan.' ORDER BY atribut';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }else{
            $sql = 'SELECT
                        at.id,
                        at.atribut,
                        at.jumlahinputan,
                        COUNT(*)=SUM(IF(ISNULL('.$idtabeljenis.')=false,1,0)) as flag,
                        SUM(IF(ISNULL('.$idtabeljenis.')=false,1,0))>0 as pakaiheader
                    FROM
                        atribut at,
                        atributnilai an
                        ' . $sqlFrom . '
                    WHERE
                        an.idatribut=at.id
                        ' . $wherebatasan . '
                    GROUP BY
                        at.id
                    ORDER BY
                        at.atribut';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
        }

        $arrAtribut = array();
        $i=0;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $arrAtribut[$i]['idatribut'] = $row['id'];
            $arrAtribut[$i]['atribut'] = $row['atribut'];
            $arrAtribut[$i]['jumlahinputan'] = $row['jumlahinputan'];

            if($id == 0) {
                //select atribut nilai sesuai dengan batasan user yang login
                if ($batasan) {
                    $sqlAtributNilai = 'SELECT
                                          an.id,
                                          an.idatribut,
                                          an.nilai,
                                          0 as dipilih,
                                          IF(ISNULL(e.id),"1",IF(ISNULL(b.idatributnilai)=false,"1","0")) as enable
                                        FROM
                                          atributnilai an
                                          LEFT JOIN (SELECT id FROM batasanemail WHERE email=:email1 LIMIT 1) e ON 1=1
                                          LEFT JOIN (SELECT ba.idatributnilai FROM batasanemail be, batasanatribut ba WHERE be.idbatasan=ba.idbatasan AND be.email=:email2) b ON b.idatributnilai=an.id
                                        WHERE
                                          an.idatribut = :idatribut
                                        ORDER BY
                                          an.nilai';
                    $stmtAtributNilai = $pdo->prepare($sqlAtributNilai);
                    $stmtAtributNilai->bindValue(':email1', $email);
                    $stmtAtributNilai->bindValue(':email2', $email);
                }
                else {
                    $sqlAtributNilai = 'SELECT
                                          an.id,
                                          an.idatribut,
                                          an.nilai,
                                          0 as dipilih,
                                          "1" as enable
                                        FROM
                                          atributnilai an
                                        WHERE
                                          an.idatribut = :idatribut
                                        ORDER BY
                                          an.nilai';
                    $stmtAtributNilai = $pdo->prepare($sqlAtributNilai);
                }
                $stmtAtributNilai->bindValue(':idatribut', $row['id']);
                $stmtAtributNilai->execute();
            }else{
                $arrAtribut[$i]['flag'] = $row['flag'];
                $arrAtribut[$i]['pakaiheader'] = $row['pakaiheader'];

                if ($batasan) {
                    //select atribut nilai yang terpilih pada pegawai dan sesuai dengan batasan user yang login
                    $sqlAtributNilai = 'SELECT
                                            an.id,
                                            an.idatribut,
                                            an.nilai,
                                            IF(ISNULL(' . $idtabeljenis . '),0,1) as dipilih,
                                            IF(ISNULL(e.id),"1",IF(ISNULL(b.idatributnilai)=false,"1","0")) as enable
                                        FROM
                                            atributnilai an
                                            ' . $sqlFrom . '
                                            LEFT JOIN (SELECT id FROM batasanemail WHERE email=:email1 LIMIT 1) e ON 1=1
                                            LEFT JOIN (SELECT ba.idatributnilai FROM batasanemail be, batasanatribut ba WHERE be.idbatasan=ba.idbatasan AND be.email=:email2) b ON b.idatributnilai=an.id
                                        WHERE
                                            an.idatribut = :idatribut
                                        GROUP BY
                                            an.id
                                        ORDER BY
                                            an.nilai';
                    $stmtAtributNilai = $pdo->prepare($sqlAtributNilai);
                    $stmtAtributNilai->bindValue(':email1', $email);
                    $stmtAtributNilai->bindValue(':email2', $email);
                }
                else {
                    //select atribut nilai yang terpilih pada pegawai dan sesuai dengan batasan user yang login
                    $sqlAtributNilai = 'SELECT
                                            an.id,
                                            an.idatribut,
                                            an.nilai,
                                            IF(ISNULL(' . $idtabeljenis . '),0,1) as dipilih,
                                            "1" as enable
                                        FROM
                                            atributnilai an
                                            ' . $sqlFrom . '
                                        WHERE
                                            an.idatribut = :idatribut
                                        GROUP BY
                                            an.id
                                        ORDER BY
                                            an.nilai';
                    $stmtAtributNilai = $pdo->prepare($sqlAtributNilai);
                }
                $stmtAtributNilai->bindValue(':id', $id);
                $stmtAtributNilai->bindValue(':idatribut', $row['id']);
                $stmtAtributNilai->execute();
            }
            $arrAtribut[$i]['atributnilai'] = $stmtAtributNilai->fetchAll(PDO::FETCH_OBJ);
            $i++;
        }

        return $arrAtribut;
    }

    public static function hapusJadwalShift($idpegawai,$tanggal)
    {
        //formattanggal = yme(16011 /tahun 2 digit terakhir,bulan,tanggal dari 1-31 normal nya 01-31)
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'DELETE FROM jadwalshift WHERE idpegawai = :idpegawai AND tanggal = DATE_FORMAT(:tanggal, "%y%m%e")';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':tanggal', $tanggal);
        $stmt->execute();
    }

    public static function getNamaPegawai($idpegawai)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
//        $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai AND del = "t"';
        $sql = 'SELECT nama FROM pegawai WHERE id = :idpegawai';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['nama'];
    }

    public static function saveUploadImage($file_dari_upload, $file_save, $flag_resize = true) {
        //*** siapkan thumbnail, penamaan adalah ditambahkan suffix: _thumb
        //hapus thumb yang lama (jika ada)
        if (file_exists($file_save)) {
            unlink($file_save);
        }

        $file_temp = $file_save.'~temp';
        //hapus temporary file jika ada
        if (file_exists($file_temp)) {
            unlink($file_temp);
        }

        if($file_dari_upload->getMimeType() == 'image/jpeg') {
            $img = imagecreatefromjpeg($file_dari_upload);
        }else if($file_dari_upload->getMimeType() == 'image/png'){
            $img = imagecreatefrompng($file_dari_upload);
        }else if($file_dari_upload->getMimeType() == 'image/bmp'){
            $img = imagecreatefrombmp($file_dari_upload);
        }
        //$img = imagecreatefromjpeg($file_dari_upload);

        $width = imagesx($img);
        $height = imagesy($img);

        if($flag_resize==true && $width> 640){
            //ukuran gambar maksimal
            $desired_width = 640;
            $desired_height = floor($height * ($desired_width / $width));

            $temp_img = imagecreatetruecolor($desired_width, $desired_height);

            imagecopyresized( $temp_img, $img, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height );
            if($file_dari_upload->getMimeType() == 'image/jpeg') {
                imagejpeg($temp_img, $file_temp);
            }else if($file_dari_upload->getMimeType() == 'image/png'){
                imagepng($temp_img, $file_temp);
            }else{
                imagejpeg($temp_img, $file_temp);
            }

            file_put_contents($file_save, self::encrypt($file_temp));

            //hapus temporary file jika ada
            if (file_exists($file_temp)) {
                unlink($file_temp);
            }
        }
        else {
            file_put_contents($file_save, self::encrypt($file_dari_upload));
        }
    }

    public static function makeThumbnail($file_dari_upload, $file_thumbnail, $width_expected = 100) {
        //*** siapkan thumbnail, penamaan adalah ditambahkan suffix: _thumb
        //hapus thumb yang lama (jika ada)
        if (file_exists($file_thumbnail)) {
            unlink($file_thumbnail);
        }

        $file_temp = $file_thumbnail.'~temp';
        //hapus temporary file jika ada
        if (file_exists($file_temp)) {
            unlink($file_temp);
        }

        $img = '';
        if($file_dari_upload->getMimeType() == 'image/jpeg') {
            $img = imagecreatefromjpeg($file_dari_upload);
        }else if($file_dari_upload->getMimeType() == 'image/png'){
            $img = imagecreatefrompng($file_dari_upload);
        }else if($file_dari_upload->getMimeType() == 'image/bmp'){
            $img = imagecreatefrombmp($file_dari_upload);
        }
        //$img = imagecreatefromjpeg($file_dari_upload);
        if($img != '') {
            $width = imagesx($img);
            $height = imagesy($img);

            $desired_width = $width < $width_expected ? $width : $width_expected;
            $desired_height = floor($height * ($desired_width / $width));

            $temp_img = imagecreatetruecolor($desired_width, $desired_height);

            if($file_dari_upload->getMimeType() == 'image/png'){
                imagecolortransparent($temp_img, imagecolorallocatealpha($temp_img, 0, 0, 0, 127));
                imagealphablending($temp_img, false);
                imagesavealpha($temp_img, true);
            }

            imagecopyresampled($temp_img, $img, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
            if($file_dari_upload->getMimeType() == 'image/jpeg') {
                imagejpeg($temp_img, $file_temp, 50);
            }else if($file_dari_upload->getMimeType() == 'image/png'){
                imagepng($temp_img, $file_temp);
            }else{
                imagejpeg($temp_img, $file_temp, 50);
            }

            //kembalikan ke format enkripsi
            file_put_contents($file_thumbnail . '_thumb', self::encrypt($file_temp));

            //hapus temporary file jika ada
            if (file_exists($file_temp)) {
                unlink($file_temp);
            }
        }
    }

    public static function int2time($int,$jenis,$satuan=false,$pakaikoma=true){
        $hasil = '';
	    if($jenis == 'detik'){
            if($satuan == true){
                $hasil = strval($int) . ' ' . strtolower(trans('all.detik'));
            }else{
                $hasil = strval($int);
            }
        }else if($jenis == 'menit'){
            $angka = floor($int/60);
            if($pakaikoma == true){
                $koma = intval(floor($int*10/60)) % 10;
                if ($koma!=0) {
                    $angka = $angka.','.$koma;
                }
            }
            if($satuan == true){
                $hasil = $angka . ' ' . strtolower(trans('all.menit'));
            }else{
                $hasil = $angka;
            }
        }else if($jenis == 'jam'){
            $angka = floor($int/3600);
            if($pakaikoma == true){
                $koma = intval(floor($int*10/3600)) % 10;
                if ($koma!=0) {
                    $angka = $angka.','.$koma;
                }
            }
            if($satuan == true){
                $hasil = $angka . ' ' . strtolower(trans('all.jam'));
            }else{
                $hasil = $angka;
            }
        }
        return $hasil;
    }

    public static function sec2prettyCustom ($sec,$satuan = true,$pakaikoma = false,$jenis = '') {
        if($jenis != ''){
            $hasil = self::int2time($sec,$jenis,$satuan,$pakaikoma);
        }else{
            if ($sec<60) {
                //dalam detik
                if($satuan == true){
                    $hasil = strval($sec) . ' ' . strtolower(trans('all.detik'));
                }else{
                    $hasil = strval($sec);
                }
            } else if ($sec/60<60) {
                //dalam menit
                $angka = floor($sec/60);
                if($pakaikoma == true){
                    $koma = intval(floor($sec*10/60)) % 10;
                    if ($koma!=0) {
                        $angka = $angka.','.$koma;
                    }
                }
                if($satuan == true){
                    $hasil = $angka . ' ' . strtolower(trans('all.menit'));
                }else{
                    $hasil = $angka;
                }
            } else {
                //dalam jam
                $angka = floor($sec/3600);
                if($pakaikoma == true){
                    $koma = intval(floor($sec*10/3600)) % 10;
                    if ($koma!=0) {
                        $angka = $angka.','.$koma;
                    }
                }
                if($satuan == true){
                    $hasil = $angka . ' ' . strtolower(trans('all.jam'));
                }else{
                    $hasil = $angka;
                }
            }
        }
        return $hasil;
    }

    public static function sec2pretty ($sec) {
        if ($sec<60) {
            //dalam detik
            $hasil = strval($sec) . ' ' . strtolower(trans('all.detik'));
        }
        else if ($sec/60<60) {
            //dalam menit
            $angka = floor($sec/60);
            $koma = intval(floor($sec*10/60)) % 10;
            if ($koma!=0) {
                $angka = $angka.','.$koma;
            }
            $hasil = $angka . ' ' . strtolower(trans('all.menit'));
        }
        else {
            //dalam jam
            $angka = floor($sec/3600);
            $koma = intval(floor($sec*10/3600)) % 10;
            if ($koma!=0) {
                $angka = $angka.','.$koma;
            }
            $hasil = $angka . ' ' . strtolower(trans('all.jam'));
        }
        return $hasil;
    }

    public static function sec2hhmm ($sec){
        $response = '';
        if($sec != ''){
            $jam = floor($sec / 3600);
            $menit = floor($sec / 60 % 60);
            if($jam > 0 || $menit > 0){
                $response = sprintf('%02d:%02d', $jam, $menit);
            }
        }
        return $response;
    }

    public static function min2hhmm ($min){
        $response = '';
        if($min != ''){
            $jam = floor($min / 60);
            $menit = $min % 60;
            if($jam > 0 || $menit > 0){
                $response = sprintf('%02d:%02d', $jam, $menit);
            }
        }
        return $response;
    }

//    public static function getDataWhere($pdo,$tabel,$field,$where = '', $wherevalue = '')
//    {
//        $wherevalue = str_replace('"','',$wherevalue);
//        $wherevalue = str_replace( "'",'',$wherevalue);
//
//        $sqlwhere = '';
//
//        if($where != ''){
//            if(is_int($wherevalue) == true){
//	            $sqlwhere = ' WHERE '.$where.' = '.$wherevalue;
//            }else{
//                $sqlwhere = ' WHERE '.$where.' = "'.$wherevalue.'"';
//            }
//        }
//        $sql = 'SELECT '.$field.' FROM '.$tabel.$sqlwhere;
//        $stmt = $pdo->prepare($sql);
//        $stmt->execute();
//        if($stmt->rowCount() > 0){
//	        $row = $stmt->fetch(PDO::FETCH_ASSOC);
//	        return $row[$field];
//        }else{
//            return '';
//        }
//    }

    public static function getDataWhere($pdo,$tabel,$field,$where = '', $wherevalue = '')
    {
        $sqlwhere = '';
        if($where != ''){
            $sqlwhere = ' WHERE '.$where.' = :wherevalue';
        }
        $sql = 'SELECT '.$field.' FROM '.$tabel.$sqlwhere;
        $stmt = $pdo->prepare($sql);
        if($where != ''){
            $stmt->bindValue(':wherevalue', $wherevalue);
        }
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row[$field];
        }else{
            return '';
        }
    }

    public static function getDataCustomWhere($pdo,$tabel,$field,$where = '')
    {
        $sqlwhere = '';
        if($where != ''){
            $sqlwhere = ' WHERE '.$where;
        }
        $sql = 'SELECT '.$field.' FROM '.$tabel.$sqlwhere;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if($stmt->rowCount() > 0){
	        $row = $stmt->fetch(PDO::FETCH_ASSOC);
	        return $row[$field];
        }else{
            return '';
        }
    }

    public static function getData($pdo,$tabel,$field = '',$where = '',$orderby = '')
    {
        $sqlorderby = '';
        if($orderby != ''){
//            $orderby = str_replace('"','',$orderby);
//            $orderby = str_replace( "'",'',$orderby);
            $sqlorderby = ' ORDER BY '.$orderby;
        }
        $sqlwhere = '';
        if($where != ''){
            $sqlwhere = ' WHERE '.$where;
        }
        $sql = 'SELECT '.$field.' FROM '.$tabel.$sqlwhere.$sqlorderby;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if($stmt->rowCount() > 0){
	        return $stmt->fetchAll(PDO::FETCH_OBJ);
        }else{
            return '';
        }
    }

    public static function getIdIndexLemburFromPegawai($idxhari,$harilibur='t',$tanggal,$idpegawai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        if($harilibur == 'y'){
            $where = ' AND jenishari = "harilibur"';
        }else if($idxhari != 0){
            $where = ' AND jenishari = "biasa"';
        }else{
            $where = ' AND jenishari = "hariminggu"';
        }
        $sql = 'SELECT
                    IFNULL(GROUP_CONCAT(idindexlembur),"") as idindexlembur
                FROM
                    indexlembur_atribut
                WHERE
                    idatributnilai IN(SELECT idatributnilai FROM pegawaiatribut WHERE idpegawai = :idpegawai )';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row['idindexlembur'] != '') {
                $where .= 'AND id IN(' . $row['idindexlembur'] . ')';
            }
        }
        $sql1 = 'SELECT id FROM indexlembur WHERE berlakumulai <= :tanggal '.$where.' ORDER BY berlakumulai DESC, id DESC LIMIT 1';
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->bindValue(':tanggal', $tanggal);
        $stmt1->execute();
        $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
        return $row1['id'];
    }

    // lama lembur formatnya menit, idx hari 0 adalah minggu 6 adalah sabtu
    public static function getIndexLembur($lamalembur,$idxhari,$harilibur='t',$tanggal='',$idpegawai=''){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $tanggal = $tanggal == '' ? 'CURRENT_DATE()' : $tanggal;
        $indexlembur = 0;
        $where = '';
        if($idpegawai != '') {
            $idindexlembur = self::getIdIndexLemburFromPegawai($idxhari,$harilibur,$tanggal,$idpegawai);
            if($idindexlembur != ''){
                $where .= ' AND id = '.$idindexlembur;
            }
        }
        $sql = 'SELECT id,`index` FROM indexlembur WHERE berlakumulai <= :tanggal '.$where.' ORDER BY berlakumulai DESC LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggal', $tanggal);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $lanjut = true;
        if($row['index'] != '') {
            // jika idpegawai != '' maka cek dulu atribut pegawainya apakah masuk dalam indexatribut
//            if($idpegawai != ''){
//                $dataidxatribut = self::getDataCustomWhere($pdo,'indexlembur_atribut','IFNULL(GROUP_CONCAT(idatributnilai),"")','idindexlembur='.$row['id']);
//                if($dataidxatribut != '') {
//                    $datapegawaiatribut = self::getDataCustomWhere($pdo, 'pegawaiatribut', 'id', 'idpegawai=' . $idpegawai . ' AND idatributnilai IN(' . $dataidxatribut . ')');
//                    if ($datapegawaiatribut == '') {
//                        $lanjut = false;
//                    }
//                }
//            }
            if($lanjut) {
                $index = array();
                $ex_index = explode(';', $row['index']);
                for ($i = 0; $i < count($ex_index); $i++) {
                    $ex_value = explode('=', $ex_index[$i]); // 0 brp menit, 1 index nya
                    $index[$i] = array();
                    $index[$i]['lebihdari'] = $ex_value[0];
                    $index[$i]['indexlembur'] = $ex_value[1];
                }
                for ($i = 0; $i < count($index); $i++) {
                    if ($lamalembur > 0) {
                        if ($i < count($index) - 1) {
                            $blok = $index[$i + 1]['lebihdari'] - $index[$i]['lebihdari'];
                            if ($lamalembur > $blok) {
                                $durasi = $blok;
                            } else {
                                $durasi = $lamalembur;
                            }
                            $lamalembur = $lamalembur - $durasi;
                            $indexlembur = $indexlembur + ($index[$i]['indexlembur'] * ($durasi / 60));
                        } else {
                            //jika yang paling terakhir
                            $indexlembur = $indexlembur + ($index[$i]['indexlembur'] * ($lamalembur / 60));
                        }
                    }
                }
            }
        }

        return round($indexlembur, 1);
    }

//    public static function getIndexLembur($menit,$idxhari,$harilibur='t',$idpegawai=''){
//        $pdo = DB::connection('perusahaan_db')->getPdo();
//        $data = self::getData($pdo,'indexlembur','*','berlakumulai <= CURRENT_DATE()','nama');
//        $hasil = 0;
//        if($data != ''){
//            foreach($data as $key){
//                $index = $key->index;
//                // khusus hari libur, karena hari libur adalah hirarki tertinggi
//                if($key->jenishari == 'harilibur' && $index != '' && $harilibur == 'y'){
//                    if($idpegawai != ''){
//                        // data indexlembur atribut
//                        $dataidxatribut = self::getDataCustomWhere($pdo,'indexlembur_atribut','IFNULL(GROUP_CONCAT(idatributnilai),"")','idindexlembur='.$key->id);
//                        if($dataidxatribut != ''){
//                            $datapegawaiatribut = self::getDataCustomWhere($pdo,'pegawaiatribut','id','idpegawai='.$idpegawai.' AND idatributnilai IN('.$dataidxatribut.')');
//                            if($datapegawaiatribut != ''){
//                                $index_ex = explode(';', $index);
//                                for($i=0;$i<count($index_ex);$i++){
//                                    $index_ex2 = explode('=', $index_ex[$i]); // 0 brp menit, 1 index nya
//                                    if($menit > $index_ex2[0]){
//                                        $hasil = $hasil + $index_ex2[1];
//                                    }
//                                }
//                            }
//                        }else{
//                            $index_ex = explode(';', $index);
//                            for($i=0;$i<count($index_ex);$i++){
//                                $index_ex2 = explode('=', $index_ex[$i]); // 0 brp menit, 1 index nya
//                                if($menit > $index_ex2[0]){
//                                    $hasil = $hasil + $index_ex2[1];
//                                }
//                            }
//                        }
//                    }else{
//                        $index_ex = explode(';', $index);
//                        for($i=0;$i<count($index_ex);$i++){
//                            $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
//                            if($menit > $index_ex2[0]){
//                                $hasil = $hasil + $index_ex2[1];
//                            }
//                        }
//                    }
//                } else if($idxhari != 0  && $harilibur == 't'){ // 1 - 6(senin sampai jum'at)
//                    if($key->jenishari == 'biasa' && $index != ''){
//                        if($idpegawai != ''){
//                            // data indexlembur atribut
//                            $dataidxatribut = self::getDataCustomWhere($pdo,'indexlembur_atribut','IFNULL(GROUP_CONCAT(idatributnilai),"")','idindexlembur='.$key->id);
//                            if($dataidxatribut != ''){
//                                $datapegawaiatribut = self::getDataCustomWhere($pdo,'pegawaiatribut','id','idpegawai='.$idpegawai.' AND idatributnilai IN('.$dataidxatribut.')');
//                                if($datapegawaiatribut != ''){
//                                    $index_ex = explode(';', $index);
//                                    for($i=0;$i<count($index_ex);$i++){
//                                        $index_ex2 = explode('=', $index_ex[$i]); // 0 brp menit, 1 index nya
//                                        if($menit > $index_ex2[0]){
//                                            $hasil = $hasil + $index_ex2[1];
//                                        }
//                                    }
//                                }
//                            }else{
//                                $index_ex = explode(';', $index);
//                                for($i=0;$i<count($index_ex);$i++){
//                                    $index_ex2 = explode('=', $index_ex[$i]); // 0 brp menit, 1 index nya
//                                    if($menit > $index_ex2[0]){
//                                        $hasil = $hasil + $index_ex2[1];
//                                    }
//                                }
//                            }
//                        }else{
//                            $index_ex = explode(';', $index);
//                            for($i=0;$i<count($index_ex);$i++){
//                                $index_ex2 = explode('=', $index_ex[$i]); // 0 brp menit, 1 index nya
//                                if($menit > $index_ex2[0]){
//                                    $hasil = $hasil + $index_ex2[1];
//                                }
//                            }
//                        }
//                    }
//                }else{
//                    // hari minggu
//                    if($key->jenishari == 'hariminggu' && $index != ''  && $harilibur == 't'){
//                        if($idpegawai != ''){
//                            // data indexlembur atribut
//                            $dataidxatribut = self::getDataCustomWhere($pdo,'indexlembur_atribut','IFNULL(GROUP_CONCAT(idatributnilai),"")','idindexlembur='.$key->id);
//                            if($dataidxatribut != ''){
//                                $datapegawaiatribut = self::getDataCustomWhere($pdo,'pegawaiatribut','id','idpegawai='.$idpegawai.' AND idatributnilai IN('.$dataidxatribut.')');
//                                if($datapegawaiatribut != ''){
//                                    $index_ex = explode(';', $index);
//                                    for($i=0;$i<count($index_ex);$i++){
//                                        $index_ex2 = explode('=', $index_ex[$i]); // 0 brp menit, 1 index nya
//                                        if($menit > $index_ex2[0]){
//                                            $hasil = $hasil + $index_ex2[1];
//                                        }
//                                    }
//                                }
//                            }else{
//                                $index_ex = explode(';', $index);
//                                for($i=0;$i<count($index_ex);$i++){
//                                    $index_ex2 = explode('=', $index_ex[$i]); // 0 brp menit, 1 index nya
//                                    if($menit > $index_ex2[0]){
//                                        $hasil = $hasil + $index_ex2[1];
//                                    }
//                                }
//                            }
//                        }else{
//                            $index_ex = explode(';', $index);
//                            for($i=0;$i<count($index_ex);$i++){
//                                $index_ex2 = explode('=', $index_ex[$i]); // 0 brp menit, 1 index nya
//                                if($menit > $index_ex2[0]){
//                                    $hasil = $hasil + $index_ex2[1];
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }
//        return $hasil;
//    }

//    update 20190611
    public static function getIdIndexJamKerjaFromPegawai($idxhari,$harilibur='t',$tanggal,$idpegawai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        if($harilibur == 'y'){
            $where = ' AND jenishari = "harilibur"';
        }else if($idxhari != 0){
            $where = ' AND jenishari = "biasa"';
        }else{
            $where = ' AND jenishari = "hariminggu"';
        }
        $hasil = '';
        $sql = 'SELECT
                    IFNULL(GROUP_CONCAT(idindexjamkerja),"") as idindexjamkerja
                FROM
                    indexjamkerja_atribut
                WHERE
                    idatributnilai IN(SELECT idatributnilai FROM pegawaiatribut WHERE idpegawai = :idpegawai )';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row['idindexjamkerja'] != '') {
                $where .= 'AND id IN(' . $row['idindexjamkerja'] . ')';
            }
        }
        $sql1 = 'SELECT id FROM indexjamkerja WHERE berlakumulai <= :tanggal '.$where.' ORDER BY berlakumulai DESC, id DESC LIMIT 1';
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->bindValue(':tanggal',$tanggal);
        $stmt1->execute();
        if($stmt->rowCount() > 0) {
            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            $hasil = $row1['id'];
        }
        return $hasil;
    }

    public static function getIndexJamKerja($menit,$idxhari,$harilibur='t',$tanggal='',$idpegawai=''){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $tanggal = $tanggal == '' ? 'CURRENT_DATE()' : $tanggal;
        $hasil = 0;
        $namapegawai = '';
        $where = '';
        if($idpegawai != '') {
            $namapegawai = self::getDataWhere($pdo, 'pegawai', 'nama', 'id', $idpegawai);
            $idindexjamkerja = self::getIdIndexJamKerjaFromPegawai($idxhari,$harilibur,$tanggal,$idpegawai);
            if($idindexjamkerja != ''){
                $where .= ' AND id = '.$idindexjamkerja;
            }
        }
        $data = self::getData($pdo,'indexjamkerja','*','berlakumulai <= "'.$tanggal.'" '.$where,'berlakumulai DESC LIMIT 1');
        if($data != ''){
            foreach($data as $key){
                $index = $key->index;
                //khusus hari libur, karena hari libur adalah hirarki tertinggi
                if($key->jenishari == 'harilibur' && $index != '' && $harilibur == 'y'){
                    if($idpegawai != ''){
                        if($namapegawai != '') {
                            //data indexjamkerja atribut
//                            $dataidxatribut = self::getDataCustomWhere($pdo, 'indexjamkerja_atribut', 'IFNULL(GROUP_CONCAT(idatributnilai),"")', 'idindexjamkerja=' . $key->id);
                            $dataidxatribut = self::getDataWhere($pdo, 'indexjamkerja_atribut', 'IFNULL(GROUP_CONCAT(idatributnilai),"")', 'idindexjamkerja',$key->id);
                            if ($dataidxatribut != '') {
                                $datapegawaiatribut = self::getDataCustomWhere($pdo, 'pegawaiatribut', 'id', 'idpegawai=' . $idpegawai . ' AND idatributnilai IN(' . $dataidxatribut . ')');
                                if ($datapegawaiatribut != '') {
                                    $index_ex = explode(';', $index);
                                    for ($i = 0; $i < count($index_ex); $i++) {
                                        $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
                                        if ($menit > $index_ex2[0]) {
                                            $hasil = $hasil + $index_ex2[1];
                                        }
                                    }
                                }
                            } else {
                                $index_ex = explode(';', $index);
                                for ($i = 0; $i < count($index_ex); $i++) {
                                    $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
                                    if ($menit > $index_ex2[0]) {
                                        $hasil = $hasil + $index_ex2[1];
                                    }
                                }
                            }
                        }
                    }else{
                        $index_ex = explode(';', $index);
                        for($i=0;$i<count($index_ex);$i++){
                            $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
                            if($menit > $index_ex2[0]){
                                $hasil = $hasil + $index_ex2[1];
                            }
                        }
                    }
                } else if($idxhari != 0  && $harilibur == 't'){ // 1 - 6(senin sampai jum'at)
                    if($key->jenishari == 'biasa' && $index != ''){
                        if($idpegawai != ''){
                            if($namapegawai != '') {
                                //data indexjamkerja atribut
//                                $dataidxatribut = self::getDataCustomWhere($pdo, 'indexjamkerja_atribut', 'IFNULL(GROUP_CONCAT(idatributnilai),"")', 'idindexjamkerja=' . $key->id);
                                $dataidxatribut = self::getDataWhere($pdo, 'indexjamkerja_atribut', 'IFNULL(GROUP_CONCAT(idatributnilai),"")', 'idindexjamkerja',$key->id);
                                if ($dataidxatribut != '') {
                                    $datapegawaiatribut = self::getDataCustomWhere($pdo, 'pegawaiatribut', 'id', 'idpegawai=' . $idpegawai . ' AND idatributnilai IN(' . $dataidxatribut . ')');
                                    if ($datapegawaiatribut != '') {
                                        $index_ex = explode(';', $index);
                                        for ($i = 0; $i < count($index_ex); $i++) {
                                            $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
                                            if ($menit > $index_ex2[0]) {
                                                $hasil = $hasil + $index_ex2[1];
                                            }
                                        }
                                    }
                                } else {
                                    $index_ex = explode(';', $index);
                                    for ($i = 0; $i < count($index_ex); $i++) {
                                        $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
                                        if ($menit > $index_ex2[0]) {
                                            $hasil = $hasil + $index_ex2[1];
                                        }
                                    }
                                }
                            }
                        }else{
                            $index_ex = explode(';', $index);
                            for($i=0;$i<count($index_ex);$i++){
                                $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
                                if($menit > $index_ex2[0]){
                                    $hasil = $hasil + $index_ex2[1];
                                }
                            }
                        }
                    }
                }else{
                    //hari minggu
                    if($key->jenishari == 'hariminggu' && $index != ''  && $harilibur == 't'){
                        if($idpegawai != ''){
                            if($namapegawai != '') {
                                //data indexjamkerja atribut
                                $dataidxatribut = self::getDataCustomWhere($pdo, 'indexjamkerja_atribut', 'IFNULL(GROUP_CONCAT(idatributnilai),"")', 'idindexjamkerja=' . $key->id);
                                if ($dataidxatribut != '') {
                                    $datapegawaiatribut = self::getDataCustomWhere($pdo, 'pegawaiatribut', 'id', 'idpegawai=' . $idpegawai . ' AND idatributnilai IN(' . $dataidxatribut . ')');
                                    if ($datapegawaiatribut != '') {
                                        $index_ex = explode(';', $index);
                                        for ($i = 0; $i < count($index_ex); $i++) {
                                            $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
                                            if ($menit > $index_ex2[0]) {
                                                $hasil = $hasil + $index_ex2[1];
                                            }
                                        }
                                    }
                                } else {
                                    $index_ex = explode(';', $index);
                                    for ($i = 0; $i < count($index_ex); $i++) {
                                        $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
                                        if ($menit > $index_ex2[0]) {
                                            $hasil = $hasil + $index_ex2[1];
                                        }
                                    }
                                }
                            }
                        }else{
                            $index_ex = explode(';', $index);
                            for($i=0;$i<count($index_ex);$i++){
                                $index_ex2 = explode('=', $index_ex[$i]); //0 brp menit, 1 index nya
                                if($menit > $index_ex2[0]){
                                    $hasil = $hasil + $index_ex2[1];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $hasil;
    }

    public static function redirectForm($msg,$param='message')
    {
        return redirect()->back()->withInput()->with($param, $msg);
    }

    public static function getTotalData($con,$tablename,$where='')
    {
        if($con == 0){
            $pdo = DB::getPDO();
        }else{
            $pdo = DB::connection('perusahaan_db')->getPDO();
        }
        $sqlwhere = '';
        if($where != ''){
            $sqlwhere = ' AND '.$where;
        }
        $sql = 'SELECT COUNT(*) as total FROM '.$tablename.' WHERE 1=1 '.$sqlwhere;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public static function captchaCheck($captcha)
    {
        $response = $captcha;
        $remoteip = $_SERVER['REMOTE_ADDR'];
        $secret   = env('RE_CAP_SECRET');

        $recaptcha = new ReCaptcha($secret);
        $resp = $recaptcha->verify($response, $remoteip);
        if ($resp->isSuccess()) {
            return 1;
        } else {
            return 0;
        }
    }

    public static function valueTanggalAwalAkhir($darisekarang = false)
    {
        $pdo = DB::getPDO();
        if($darisekarang == false) {
            $sql = 'SELECT CONCAT("01/",DATE_FORMAT(NOW(), "%m/%Y")) as tanggalawal, DATE_FORMAT(LAST_DAY(NOW()), "%d/%m/%Y") as tanggalakhir';
        }else{
            $sql = 'SELECT DATE_FORMAT(NOW(), "%d/%m/%Y") as tanggalawal, DATE_FORMAT(LAST_DAY(NOW()), "%d/%m/%Y") as tanggalakhir';
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $hasil = $stmt->fetch(PDO::FETCH_OBJ);
        return $hasil;
    }

    public static function tahunDropdown()
    {
	    $pdo = DB::getPdo();
        $sql = 'SELECT year(now())+1 as tahun1, year(now()) as tahun2, year(now())-1 as tahun3, year(now())-2 as tahun4, year(now())-3 as tahun5';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $tahun = $stmt->fetch(PDO::FETCH_OBJ);
        return $tahun;
    }

    public static function cekPegawaiAtributNilai($idpegawai,$idatributnilai)
    {
        $hasil = false;
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //cari tau jumlah inputan artibut
        $sql = 'SELECT jumlahinputan FROM atribut WHERE id = (SELECT idatribut FROM atributnilai WHERE id=:idatributnilai)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idatributnilai', $idatributnilai);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row['jumlahinputan'] == 'satu'){
                //select atribut sesuai idatributnilai
                $sql = 'SELECT
                            pa.idpegawai
                        FROM
                            pegawaiatribut pa,
                            atributnilai an
                        WHERE
                            pa.idatributnilai = an.id AND
                            pa.idpegawai = :idpegawai AND
                            an.idatribut IN (SELECT idatribut FROM atributnilai WHERE id=:idatributnilai)
                        LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':idpegawai', $idpegawai);
                $stmt->bindValue(':idatributnilai', $idatributnilai);
                $stmt->execute();
                if($stmt->rowCount() > 0){
                    $hasil =true;
                }
            }
        }
        return $hasil;
    }

    public static function cekGuide()
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        //perlengkapan show_guide cek total pegawai, cek total jamkerja, cek jamkerja pegawai, cek mesin
        $sql = 'SELECT COUNT(id) as totalpegawai FROM pegawai WHERE status = "a" AND del = "t"';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idperusahaan', Session::get('conf_webperusahaan'));
        $stmt->execute();
        $totalpegawai = $stmt->rowCount();

        $sql = 'SELECT id FROM jamkerja';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $totaljamkerja = $stmt->rowCount();

        $sql = 'SELECT id FROM pegawaijamkerja';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $totaljamkerjapegawai = $stmt->rowCount();

        $sql1 = 'SELECT id FROM mesin';
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute();
        $totalmesin = $stmt1->rowCount();

        $arrtotalcek = [$totalpegawai, $totaljamkerja, $totaljamkerjapegawai, $totalmesin];
        if(Session::has('perusahaan_showguide')){
            Session::forget('perusahaan_showguide');
        }
        if($totalpegawai == 0 or $totaljamkerja == 0 or $totaljamkerjapegawai == 0 or $totalmesin == 0){
            Session::set('perusahaan_showguide', $arrtotalcek);
        }
    }

    public static function enumCustomDashboard_Kehadiran(
        $tanggal,
        $customdashboard,
        &$where_data,
        $nodedetail,
        &$kolom_keterangan,
        &$kolom_order_by,
        &$adatable_rekapabsen,
        &$adatable_rekapshift,
        &$adatable_ijintidakmasuk,
        &$adatable_alasantidakmasuk
    ) {
        if ($customdashboard['query_kehadiran'] == 'semua') {
            if ($customdashboard['query_kehadiran_data'] == 'sudahabsen') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id = ra.idpegawai
                            AND ra.tanggal="'.$tanggal.'"
                            AND ra.masukkerja="y"';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'waktumasuk';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'DATE_FORMAT(ra.waktumasuk, "%d/%m/%Y %T")';
                    $kolom_keterangan = 'CONCAT("tanggalCantik(\"",ra.waktumasuk,"\")")';
//                    $kolom_keterangan = 'ra.waktumasuk';
//                $kolom_order_by = 'ra.waktumasuk DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'belumabsen') {
                $where_data = ' AND p.id=psa.idpegawai AND p.id NOT IN (SELECT idpegawai FROM rekapabsen WHERE tanggal="'.$tanggal.'" AND masukkerja="y")';
            } else if ($customdashboard['query_kehadiran_data'] == 'adadikantor') {
                $where_data = ' AND p.id IN (
                                        SELECT
                                            la.idpegawai
                                        FROM
                                            logabsen la,
                                            (
                                            SELECT
                                                idpegawai,
                                                MAX(waktu) as waktu
                                            FROM
                                                logabsen
                                            WHERE
                                                waktu>=CONCAT("'.$tanggal.'"," 00:00:00") AND waktu<=CONCAT("'.$tanggal.'"," 23:59:59")
                                            GROUP BY
                                                idpegawai
                                            ) la_last
                                        WHERE
                                            la.idpegawai=la_last.idpegawai AND
                                            la.waktu=la_last.waktu AND
                                            la.masukkeluar="k"
                                        )';
            } else if ($customdashboard['query_kehadiran_data'] == 'ijintidakmasuk') {
                $adatable_ijintidakmasuk = 'y';
                $adatable_alasantidakmasuk = 'y';
                $where_data = ' AND p.id=itm.idpegawai
                            AND ("'.$tanggal.'" BETWEEN itm.tanggalawal AND itm.tanggalakhir)
                            AND itm.status="a"';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'keterangan';
                }
                else if ($nodedetail=='detail') {
                    $kolom_keterangan = 'itm.keterangan';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'terlambat') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.selisihmasuk<0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'terlambat';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = '-1*ra.selisihmasuk';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",-1*ra.selisihmasuk,"\")")';
//                $kolom_order_by = 'ra.selisihmasuk ASC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'pulangawal') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.selisihkeluar<0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'pulangawal';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = '-1*ra.selisihkeluar';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",-1*ra.selisihkeluar,"\")")';
//                $kolom_order_by = 'ra.selisihkeluar ASC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'lamalembur') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.lamalembur>0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'lamalembur';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'ra.lamalembur';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",ra.lamalembur,"\")")';
//                $kolom_order_by = 'ra.lamalembur DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'lamakerja') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.lamakerja>0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'lamakerja';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'ra.lamakerja';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",ra.lamakerja,"\")")';
//                $kolom_order_by = 'ra.lamakerja DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'masuknormal') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.selisihmasuk>=0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'masuknormal';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'ra.selisihmasuk';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",ra.selisihmasuk,"\")")';
//                $kolom_order_by = 'ra.selisihmasuk DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'pulangnormal') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.selisihkeluar>=0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'pulangnormal';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'ra.selisihkeluar';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",ra.selisihkeluar,"\")")';
//                $kolom_order_by = 'ra.selisihkeluar DESC, ';
                }
            }
        } else if ($customdashboard['query_kehadiran'] == 'full') {
            if ($customdashboard['query_kehadiran_data'] == 'sudahabsen') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.jenisjamkerja="full"
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'waktumasuk';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'ra.waktumasuk';
                    $kolom_keterangan = 'CONCAT("tanggalCantik(\"",ra.waktumasuk,"\")")';
//                    $kolom_keterangan = 'DATE_FORMAT(ra.waktumasuk, "%d/%m/%Y %T")';
//                $kolom_order_by = 'ra.waktumasuk DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'belumabsen') {
                $where_data = ' AND p.id=psa.idpegawai
                                AND getpegawaijamkerja(p.id, "jenis", "'.$tanggal.'")="full"
                                AND p.id NOT IN (SELECT idpegawai FROM rekapabsen WHERE jenisjamkerja="full" AND tanggal="'.$tanggal.'" AND masukkerja="y")';
            } else if ($customdashboard['query_kehadiran_data'] == 'terlambat') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.jenisjamkerja="full"
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.selisihmasuk<0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'terlambat';
                }
                else if ($nodedetail=='detail') {
                    $kolom_keterangan = 'CONCAT("Utils::sec2pretty(",-1*ra.selisihmasuk,")")';
//                    $kolom_keterangan = '-1*ra.selisihmasuk';
//                $kolom_order_by = 'ra.selisihmasuk ASC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'pulangawal') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.jenisjamkerja="full"
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.selisihkeluar<0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'pulangawal';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = '-1*ra.selisihkeluar';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",-1*ra.selisihkeluar,"\")")';
//                $kolom_order_by = 'ra.selisihkeluar ASC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'lamalembur') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.jenisjamkerja="full"
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.lamalembur>0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'lamalembur';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'ra.lamalembur';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",ra.lamalembur,"\")")';
//                $kolom_order_by = 'ra.lamalembur DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'lamakerja') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.jenisjamkerja="full"
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.lamakerja>0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'lamakerja';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'ra.lamakerja';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",ra.lamakerja,"\")")';
//                $kolom_order_by = 'ra.lamakerja DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'masuknormal') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.jenisjamkerja="full"
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.selisihmasuk>=0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'masuknormal';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'ra.selisihmasuk';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",ra.selisihmasuk,"\")")';
//                $kolom_order_by = 'ra.selisihmasuk DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'pulangnormal') {
                $adatable_rekapabsen = 'y';
                $where_data = ' AND p.id=ra.idpegawai
                                AND ra.jenisjamkerja="full"
                                AND ra.tanggal="'.$tanggal.'"
                                AND ra.masukkerja="y"
                                AND ra.selisihkeluar>=0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'pulangnormal';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'ra.selisihkeluar';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",ra.selisihkeluar,"\")")';
//                $kolom_order_by = 'ra.selisihkeluar DESC, ';
                }
            }
        } else if ($customdashboard['query_kehadiran'] == 'shift') {
            if ($customdashboard['query_kehadiran_data'] == 'sudahabsen') {
                $adatable_rekapshift = 'y';
                $where_data = ' AND p.id=rs.idpegawai
                                AND rs.tanggal="'.$tanggal.'"
                                AND rs.masukkerja="y"';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'waktumasuk';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'rs.waktumasuk';
                    $kolom_keterangan = 'CONCAT("tanggalCantik(\"",rs.waktumasuk,"\")")';
//                $kolom_order_by = 'rs.waktumasuk DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'belumabsen') {
                $where_data = ' AND p.id=psa.idpegawai
                                AND getpegawaijamkerja(p.id, "jenis", "'.$tanggal.'")="shift"
                                AND p.id NOT IN (SELECT idpegawai FROM rekapshift WHERE tanggal="'.$tanggal.'" AND masukkerja="y")';
            } else if ($customdashboard['query_kehadiran_data'] == 'terlambat') {
                $adatable_rekapshift = 'y';
                $where_data = ' AND p.id=rs.idpegawai
                                AND rs.tanggal="'.$tanggal.'"
                                AND rs.masukkerja="y"
                                AND rs.selisihmasuk<0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'terlambat';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = '-1*rs.selisihmasuk';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",-1*rs.selisihmasuk,"\")")';
//                $kolom_order_by = 'rs.selisihmasuk ASC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'pulangawal') {
                $adatable_rekapshift = 'y';
                $where_data = ' AND p.id=rs.idpegawai
                                AND rs.tanggal="'.$tanggal.'"
                                AND rs.masukkerja="y"
                                AND rs.selisihkeluar<0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'pulangawal';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = '-1*rs.selisihkeluar';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",-1*rs.selisihkeluar,"\")")';
//                $kolom_order_by = 'rs.selisihkeluar ASC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'lamalembur') {
                $adatable_rekapshift = 'y';
                $where_data = ' AND p.id=rs.idpegawai
                            AND rs.tanggal="'.$tanggal.'"
                            AND rs.masukkerja="y"
                            AND rs.lamalembur>0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'lamalembur';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'rs.lamalembur';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",rs.lamalembur,"\")")';
//                $kolom_order_by = 'rs.lamalembur DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'lamakerja') {
                $adatable_rekapshift = 'y';
                $where_data = ' AND p.id=rs.idpegawai
                            AND rs.tanggal="'.$tanggal.'"
                            AND rs.masukkerja="y"
                            AND rs.lamakerja>0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'lamakerja';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'rs.lamakerja';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",rs.lamakerja,"\")")';
//                $kolom_order_by = 'rs.lamakerja DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'masuknormal') {
                $adatable_rekapshift = 'y';
                $where_data = ' AND p.id=rs.idpegawai
                                AND rs.tanggal="'.$tanggal.'"
                                AND rs.masukkerja="y"
                                AND rs.selisihmasuk>=0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'masuknormal';
                }
                else if ($nodedetail=='detail') {
//                    $kolom_keterangan = 'rs.selisihmasuk';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",rs.selisihmasuk,"\")")';
//                $kolom_order_by = 'rs.selisihmasuk DESC, ';
                }
            } else if ($customdashboard['query_kehadiran_data'] == 'pulangnormal') {
                $adatable_rekapshift = 'y';
                $where_data = ' AND p.id=rs.idpegawai
                                AND rs.tanggal="'.$tanggal.'"
                                AND rs.masukkerja="y"
                                AND rs.selisihkeluar>=0';
                if ($nodedetail=='node') {
                    $kolom_keterangan = 'pulangnormal';
                }
                else if ($nodedetail=='detail') {
                    $kolom_keterangan = 'rs.selisihkeluar';
                    $kolom_keterangan = 'CONCAT("sec2pretty(\"",ra.selisihkeluar,"\")")';
//                $kolom_order_by = 'rs.selisihkeluar DESC, ';
                }

            }
        }
    }

    public static function enumCustomDashboard_WhereIf(
        $query_jenis,
        &$where_if,
        &$adatable_rekapabsen,
        &$adatable_rekapshift,
        &$adatable_jadwalshift,
        &$adatable_ijintidakmasuk,
        &$adatable_alasantidakmasuk,
        &$adatable_atributnilai,
        &$adatable_agama,
        &$adatable_jamkerja,
        &$adatable_lokasi,
        &$adatable_jamkerjashift,
        &$adatable_jamkerjashift_jenis,
        &$adatable_jamkerjakategori
    ) {
        if (stripos($where_if, '[idpegawai]')!==false) {
            $where_if = str_ireplace('[idpegawai]', 'p.id', $where_if);
        }
        if (stripos($where_if, '[idatributnilai]')!==false) {
            $adatable_atributnilai = 'y';
            $where_if = preg_replace('/\[idatributnilai\]=(\d+)/i', 'INSTR(an.idatributnilai,CONCAT("|",$1,"|"))>0', $where_if);
        }
        if (stripos($where_if, '[idagama]')!==false) {
            $adatable_agama = 'y';
            $where_if = str_ireplace('[idagama]', 'ag.id', $where_if);
        }
        if (stripos($where_if, '[idjamkerja]')!==false) {
            $adatable_jamkerja = 'y';
            $where_if = str_ireplace('[idjamkerja]', 'jk.id', $where_if);
        }
        if (stripos($where_if, '[idlokasi]')!==false) {
            $adatable_lokasi = 'y';
            $where_if = preg_replace('/\[idlokasi\]=(\d+)/i', 'INSTR(l.idlokasi,CONCAT("|",$1,"|"))>0', $where_if);
            //$where_if = str_ireplace('[idlokasi]', 'l.id', $where_if);
        }
        if (stripos($where_if, '[idjamkerjashift]')!==false) {
            $adatable_jamkerjashift = 'y';
            if ($query_jenis=='kehadiran') {
                $adatable_rekapshift = 'y';
            }
            else if ($query_jenis=='master') {
                $adatable_jadwalshift = 'y';
            }
            $where_if = str_ireplace('[idjamkerjashift]', 'jks.id', $where_if);
        }
        if (stripos($where_if, '[idjamkerjashift_jenis]')!==false) {
            $adatable_jamkerjashift_jenis = 'y';
            $adatable_jamkerjashift = 'y';
            if ($query_jenis=='kehadiran') {
                $adatable_rekapshift = 'y';
            }
            else if ($query_jenis=='master') {
                $adatable_jadwalshift = 'y';
            }
            $where_if = str_ireplace('[idjamkerjashift_jenis]', 'jksj.id', $where_if);
        }
        if (stripos($where_if, '[idjamkerjakategori]')!==false) {
            $adatable_jamkerjakategori = 'y';
            $adatable_jamkerja = 'y';
            if ($query_jenis=='kehadiran') {
                $adatable_rekapabsen = 'y';
            }
            $where_if = str_ireplace('[idjamkerjakategori]', 'jkk.id', $where_if);
        }
        if (stripos($where_if, '[idalasantidakmasuk]')!==false) {
            $adatable_ijintidakmasuk = 'y';
            $adatable_alasantidakmasuk = 'y';
            $where_if = str_ireplace('[idalasantidakmasuk]', 'atm.id', $where_if);
        }
        if (stripos($where_if, '[alasantidakmasuk_kategori]')!==false) {
            $adatable_ijintidakmasuk = 'y';
            $adatable_alasantidakmasuk = 'y';
            $where_if = preg_replace('/\[alasantidakmasuk_kategori\]=(\w+)/i', 'atm.kategori="$1"', $where_if);
            $where_if = str_ireplace('[alasantidakmasuk_kategori]', 'atm.kategori', $where_if);
        }
        if ($where_if!='') {
            $where_if = ' AND ('.$where_if.') ';
        }
    }

    public static function enumCustomDashboard_PrepareGroupBy ($group_by) {
        $hasil = '';
        if ($group_by=='agama') {
            $hasil = 'ag.id';
        }
        else if ($group_by=='jamkerja') {
            $hasil = 'jk.id';
        }
        else if ($group_by=='jamkerjajenis') {
            $hasil = 'jk.jenis';
        }
        else if ($group_by=='jamkerjashift_jenis') {
            $hasil = 'jksj.id';
        }
        else if ($group_by=='alasantidakmasuk') {
            $hasil = 'atm.id';
        }
        else if ($group_by=='alasantidakmasuk_kategori') {
            $hasil = 'atm.kategori';
        }
        else if ($group_by=='jamkerjakategori') {
            $hasil = 'jkk.nama';
        }
        return $hasil;
    }

    public static function enumCustomDashboard_GroupBy(
        $query_jenis,
        &$group_by,
        &$group_by_select,
        &$group_by_order,
        &$adatable_rekapshift,
        &$adatable_jadwalshift,
        &$adatable_ijintidakmasuk,
        &$adatable_alasantidakmasuk,
        &$adatable_agama,
        &$adatable_jamkerja,
        &$adatable_jamkerjashift,
        &$adatable_jamkerjashift_jenis,
        &$adatable_jamkerjakategori
    ) {
        if ($group_by=='agama') {
            $adatable_agama = 'y';
            $group_by = 'ag.id';
            $group_by_select = ' IFNULL(ag.id,0) as `key`, IFNULL(ag.agama,"-") as nama, COUNT(*) as jumlah';
            $group_by_order = ' ag.agama ASC';
        }
        else if ($group_by=='jamkerja') {
            $adatable_jamkerja = 'y';
            $group_by = 'jk.id';
            $group_by_select = ' jk.id as `key`, jk.nama, COUNT(*) as jumlah';
            $group_by_order = ' jk.nama ASC';
        }
        else if ($group_by=='jamkerjajenis') {
            $adatable_jamkerja = 'y';
            $group_by = 'jk.jenis';
            $group_by_select = ' jk.jenis as `key`, jk.jenis as nama, COUNT(*) as jumlah';
            $group_by_order = ' jk.jenis ASC';
        }
        else if ($group_by=='jamkerjashift_jenis') {
            $adatable_jamkerjashift_jenis = 'y';
            $adatable_jamkerjashift = 'y';
            if ($query_jenis=='kehadiran') {
                $adatable_rekapshift = 'y';
            }
            else if ($query_jenis=='master') {
                $adatable_jadwalshift = 'y';
            }
            $group_by = 'jksj.id';
            $group_by_select = ' jksj.id as `key`, jksj.nama, COUNT(*) as jumlah';
            $group_by_order = ' jksj.nama ASC';
        }
        else if ($group_by=='alasantidakmasuk') {
            $adatable_alasantidakmasuk = 'y';
            $adatable_ijintidakmasuk = 'y';
            $group_by = 'atm.id';
            $group_by_select = ' IFNULL(atm.id,0) as `key`, IFNULL(atm.alasan,"-") as nama, COUNT(*) as jumlah';
            $group_by_order = ' atm.alasan ASC';
        }
        else if ($group_by=='alasantidakmasuk_kategori') {
            $adatable_alasantidakmasuk = 'y';
            $adatable_ijintidakmasuk = 'y';
            $group_by = 'atm.kategori';
            $group_by_select = ' IFNULL(atm.kategori,"") as `key`, IFNULL(atm.kategori,"-") as nama, COUNT(*) as jumlah';
            $group_by_order = ' atm.kategori ASC';
        }
        else if ($group_by=='jamkerjakategori') {
            $adatable_jamkerjakategori = 'y';
            $adatable_jamkerja = 'y';
            $group_by = 'jkk.nama';
            $group_by_select = ' jkk.id as `key`, jkk.nama, COUNT(*) as jumlah';
            $group_by_order = ' jkk.nama ASC';
        }
    }

    public static function enumCustomDashboard_FromWhere(
        $tanggal,
        &$from01,
        &$where01,
        &$where_del,
        $adatable_logabsen,
        $adatable_rekapabsen,
        $adatable_rekapshift,
        $adatable_jadwalshift,
        $adatable_ijintidakmasuk,
        $adatable_alasantidakmasuk,
        $adatable_atributnilai,
        $adatable_agama,
        $adatable_jamkerja,
        $adatable_lokasi,
        $adatable_jamkerjashift,
        $adatable_jamkerjashift_jenis,
        $adatable_jamkerjakategori
    ){
        $from01 = ' pegawai p';
        $where01 = '';

        if ($adatable_agama=='y') {
            $from01 = $from01 . ' LEFT JOIN agama ag ON p.idagama=ag.id';
        }
        if ($adatable_atributnilai=='y') {
            $from01 = $from01 . ' LEFT JOIN (SELECT pa.idpegawai, CONCAT("|",GROUP_CONCAT(an.id SEPARATOR "|"),"|") as idatributnilai FROM pegawaiatribut pa, atributnilai an WHERE pa.idatributnilai=an.id GROUP BY pa.idpegawai) an ON an.idpegawai=p.id';
        }
        if ($adatable_lokasi=='y') {
            $from01 = $from01 . ' LEFT JOIN (SELECT pl.idpegawai, CONCAT("|",GROUP_CONCAT(l.id SEPARATOR "|"),"|") as idlokasi FROM lokasi l, pegawailokasi pl WHERE l.id=pl.idlokasi GROUP BY pl.idpegawai) l ON l.idpegawai=p.id';
        }
        if ($adatable_logabsen=='y') {
            $where_del = '';
            $from01 = $from01 . ',logabsen la';
            $where01 = $where01 . ' AND la.idpegawai=p.id AND la.tanggal="'.$tanggal.'" ';
        }
        if ($adatable_rekapabsen=='y') {
            $where_del = '';
            $from01 = $from01 . ',rekapabsen ra';
            $where01 = $where01 . ' AND ra.idpegawai=p.id AND ra.tanggal="'.$tanggal.'" ';
        }
        if ($adatable_rekapshift=='y') {
            $where_del = '';
            $from01 = $from01 . ',rekapshift rs';
            $where01 = $where01 . ' AND rs.idpegawai=p.id AND rs.tanggal="'.$tanggal.'" ';
        }
        if ($adatable_jadwalshift=='y') {
            $from01 = $from01 . ',jadwalshift js';
            $where01 = $where01 . ' AND js.idpegawai=p.id AND js.tanggal="'.$tanggal.'" ';
        }
        if ($adatable_ijintidakmasuk=='y') {
            $from01 = $from01 . ',ijintidakmasuk itm';
            if ($adatable_alasantidakmasuk=='y') {
                $from01 = $from01 . ' LEFT JOIN alasantidakmasuk atm ON atm.id=itm.idalasantidakmasuk';
            }
            $where01 = $where01 . ' AND itm.idpegawai=p.id';
        }
        if ($adatable_jamkerja=='y') {
            $from01 = $from01 . ',jamkerja jk';
            $where01 = $where01 . ' AND getpegawaijamkerja(p.id, "id", "'.$tanggal.'")=jk.id';
        }
        if ($adatable_jamkerjashift=='y') {
            $from01 = $from01 . ',jamkerjashift jks';
            if ($adatable_rekapshift=='y') {
                $where01 = $where01 . ' AND jks.id=rs.idjamkerjashift';
            }
            else if ($adatable_jadwalshift=='y') {
                $where01 = $where01 . ' AND jks.id=js.idjamkerjashift';
            }
        }
        if ($adatable_jamkerjashift_jenis=='y') {
            $from01 = $from01 . ',jamkerjashift_jenis jksj';
            if ($adatable_jamkerjashift=='y') {
                $where01 = $where01 . ' AND jksj.id=jks.idjenis';
            }
        }
        if ($adatable_jamkerjakategori=='y') {
            $from01 = $from01 . ',jamkerjakategori jkk';
            if ($adatable_jamkerja=='y') {
                $where01 = $where01 . ' AND jkk.id=jk.idkategori';
            }
        }
    }

    public static function enumCustomDashboard_TentukanStartFrom(
        $pdo,
        $query_jenis,
        $customdashboard,
        &$tentukan_startfrom,
        &$tentukan_orderby,
        &$startfrom_nama,
        &$sqlWhere_StartFrom,
        $startfrom
    ) {
        //tentukan cara membuat startfrom dan order by
        if ($query_jenis=='kehadiran') {
            if ($customdashboard['query_kehadiran'] == 'semua') {
                if ($customdashboard['query_kehadiran_data'] == 'sudahabsen') {
                    $tentukan_startfrom = ' CONCAT(DATE_FORMAT(ra.waktumasuk,"%Y%m%d%H%i%s"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(DATE_FORMAT(ra.waktumasuk,"%Y%m%d%H%i%s"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(DATE_FORMAT(ra.waktumasuk,"%Y%m%d%H%i%s"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if (
                    $customdashboard['query_kehadiran_data'] == 'belumabsen' ||
                    $customdashboard['query_kehadiran_data'] == 'adadikantor' ||
                    $customdashboard['query_kehadiran_data'] == 'ijintidakmasuk'
                ) {
                    $tentukan_startfrom = ' p.id ';
                    $tentukan_orderby = ' p.nama ';
                    if ($startfrom!='') {
                        $sql = 'SELECT nama FROM pegawai WHERE del="t" AND status="a" AND id=:startfrom LIMIT 1';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':startfrom', $startfrom);
                        $stmt->execute();
                        if ($stmt->rowCount()>0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $startfrom_nama = $row['nama'];
                            $sqlWhere_StartFrom = ' AND p.nama > :startfrom_nama ';
                        }
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'terlambat') {
                    $tentukan_startfrom = ' CONCAT(LPAD(-1*ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(-1*ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(-1*ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'pulangawal') {
                    $tentukan_startfrom = ' CONCAT(LPAD(-1*ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(-1*ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(-1*ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'lamalembur') {
                    $tentukan_startfrom = ' CONCAT(LPAD(ra.lamalembur,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(ra.lamalembur,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(ra.lamalembur,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'lamakerja') {
                    $tentukan_startfrom = ' CONCAT(LPAD(ra.lamakerja,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(ra.lamakerja,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(ra.lamakerja,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'masuknormal') {
                    $tentukan_startfrom = ' CONCAT(LPAD(ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'pulangnormal') {
                    $tentukan_startfrom = ' CONCAT(LPAD(ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                }
            } else if ($customdashboard['query_kehadiran'] == 'full') {
                if ($customdashboard['query_kehadiran_data'] == 'sudahabsen') {
                    $tentukan_startfrom = ' CONCAT(DATE_FORMAT(ra.waktumasuk,"%Y%m%d%H%i%s"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(DATE_FORMAT(ra.waktumasuk,"%Y%m%d%H%i%s"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(DATE_FORMAT(ra.waktumasuk,"%Y%m%d%H%i%s"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'belumabsen') {
                    $tentukan_startfrom = ' p.id ';
                    $tentukan_orderby = ' p.nama ';
                    if ($startfrom!='') {
                        $sql = 'SELECT nama FROM pegawai WHERE del="t" AND status="a" AND id=:startfrom LIMIT 1';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':startfrom', $startfrom);
                        $stmt->execute();
                        if ($stmt->rowCount()>0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $startfrom_nama = $row['nama'];
                            $sqlWhere_StartFrom = ' AND p.nama > :startfrom_nama ';
                        }
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'terlambat') {
                    $tentukan_startfrom = ' CONCAT(LPAD(-1*ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(-1*ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(-1*ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'pulangawal') {
                    $tentukan_startfrom = ' CONCAT(LPAD(-1*ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(-1*ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(-1*ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'lamalembur') {
                    $tentukan_startfrom = ' CONCAT(LPAD(ra.lamalembur,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(ra.lamalembur,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(ra.lamalembur,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'lamakerja') {
                    $tentukan_startfrom = ' CONCAT(LPAD(ra.lamakerja,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(ra.lamakerja,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(ra.lamakerja,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'masuknormal') {
                    $tentukan_startfrom = ' CONCAT(LPAD(ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(ra.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'pulangnormal') {
                    $tentukan_startfrom = ' CONCAT(LPAD(ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(ra.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                }
            } else if ($customdashboard['query_kehadiran'] == 'shift') {
                if ($customdashboard['query_kehadiran_data'] == 'sudahabsen') {
                    $tentukan_startfrom = ' CONCAT(DATE_FORMAT(rs.waktumasuk,"%Y%m%d%H%i%s"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(DATE_FORMAT(rs.waktumasuk,"%Y%m%d%H%i%s"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(DATE_FORMAT(rs.waktumasuk,"%Y%m%d%H%i%s"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'belumabsen') {
                    $tentukan_startfrom = ' p.id ';
                    $tentukan_orderby = ' p.nama ';
                    if ($startfrom!='') {
                        $sql = 'SELECT nama FROM pegawai WHERE del="t" AND status="a" AND id=:startfrom LIMIT 1';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':startfrom', $startfrom);
                        $stmt->execute();
                        if ($stmt->rowCount()>0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $startfrom_nama = $row['nama'];
                            $sqlWhere_StartFrom = ' AND p.nama > :startfrom_nama ';
                        }
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'terlambat') {
                    $tentukan_startfrom = ' CONCAT((-1*rs.selisihmasuk),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT((-1*rs.selisihmasuk),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT((-1*rs.selisihmasuk),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'pulangawal') {
                    $tentukan_startfrom = ' CONCAT(LPAD(-1*rs.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(-1*rs.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(-1*rs.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'lamalembur') {
                    $tentukan_startfrom = ' CONCAT(LPAD(rs.lamalembur,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(rs.lamalembur,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(rs.lamalembur,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'lamakerja') {
                    $tentukan_startfrom = ' CONCAT(LPAD(rs.lamakerja,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(rs.lamakerja,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAd(rs.lamakerja,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'masuknormal') {
                    $tentukan_startfrom = ' CONCAT(LPAD(rs.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(rs.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(rs.selisihmasuk,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                } else if ($customdashboard['query_kehadiran_data'] == 'pulangnormal') {
                    $tentukan_startfrom = ' CONCAT(LPAD(rs.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) ';
                    $tentukan_orderby = ' CONCAT(LPAD(rs.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) DESC';
                    if ($startfrom!='') {
                        $startfrom_nama = $startfrom;
                        $sqlWhere_StartFrom = ' AND CONCAT(LPAD(rs.selisihkeluar,9,"0"),"_",LPAD(999999999-p.id,9,"0")) < :startfrom_nama ';
                    }
                }
            }
        }
        else if ($query_jenis=='master') {
            $tentukan_startfrom = ' p.id ';
            $tentukan_orderby = ' p.nama ';

            if ($startfrom!='') {
                $sql = 'SELECT nama FROM pegawai WHERE del="t" AND status="a" AND id=:startfrom LIMIT 1';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':startfrom', $startfrom);
                $stmt->execute();
                if ($stmt->rowCount()>0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $startfrom_nama = $row['nama'];
                    $sqlWhere_StartFrom = ' AND p.nama > :startfrom_nama ';
                }
            }
        }
    }

    public static function generateCustomDashboard($pdo, $tanggal, $batasan, $idcustomdashboard) {
        $where_batasan = '';
        if ($batasan!='') {
            $where_batasan = ' AND p.id IN '.$batasan;
        }

        $utcdefault = '+08:00';
        $sql = 'SELECT substring(utc,1,LOCATE(":",utc)-1)+0 as utc FROM pengaturan LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row['utc']!='') {
                $utcdefault = $row['utc'];
            }
        }

        //proses custom
        $sql = 'SELECT
                tampil_konfirmasi,
                tampil_peringkat,
                tampil_3lingkaran,
                tampil_sudahbelumabsen,
                tampil_terlambatdll,
                tampil_pulangawaldll,
                tampil_totalgrafik,
                tampil_peta,
                tampil_harilibur,
                tampil_riwayatdashboard
	        FROM
	            customdashboard
	        WHERE
                id=:id
	        ';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $idcustomdashboard);
        $stmt->execute();
        $jsonTampilNode = $stmt->fetchAll(PDO::FETCH_OBJ);
        $response['tampil']=$jsonTampilNode;

        // proses customnode
        $custom_node = array();

        $sql = 'SELECT
                cdn.id,
                cdn.judul,
                cdn.icon,
                cdn.warna,
                cdn.query_jenis,
                cdn.query_kehadiran,
                cdn.query_kehadiran_data,
                cdn.query_kehadiran_if,
                cdn.query_kehadiran_group,
                cdn.query_kehadiran_periode,
                cdn.query_master_data,
                cdn.query_master_if,
                cdn.query_master_group,
                cdn.query_master_periode
	        FROM
	            customdashboard_detail cdd,
	        	customdashboard_node cdn
	        WHERE
	            cdd.idcustomdashboard_node=cdn.id AND
	            (cdn.waktutampil="t" OR (ADDDATE(UTC_TIME(), INTERVAL '.$utcdefault.' HOUR) BETWEEN waktutampil_awal AND waktutampil_akhir)) AND
                cdd.idcustomdashboard=:id
            ORDER BY
                cdd.urutan ASC
	        ';
//    AND cdn.id=3
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $idcustomdashboard);
        $stmt->execute();
        $customdashboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
        for($i=0;$i<count($customdashboard);$i++) {

            $total = '';
            $navigasi_periode = '';
            $tampil_group = 't';

            $from01 = '';
            $where01 ='';

            $where_data = '';
            $kolom_keterangan='';
            $kolom_order_by='';

            $adatable_logabsen = 't';
            $adatable_rekapabsen = 't';
            $adatable_rekapshift = 't';
            $adatable_jadwalshift = 't';
            $adatable_ijintidakmasuk = 't';
            $adatable_alasantidakmasuk = 't';
            $adatable_atributnilai = 't';
            $adatable_agama = 't';
            $adatable_jamkerja = 't';
            $adatable_lokasi = 't';
            $adatable_jamkerjashift = 't';
            $adatable_jamkerjashift_jenis = 't';
            $adatable_jamkerjakategori = 't';

            if ($customdashboard[$i]['query_jenis']=='kehadiran') {
                $tampil_group = $customdashboard[$i]['query_kehadiran_group']==''?'t':'y';
                $navigasi_periode = $customdashboard[$i]['query_kehadiran_periode'];

                //jika tidak punya group, tampilkan totalnya
                if ($customdashboard[$i]['query_kehadiran_group'] == '') {

                    self::enumCustomDashboard_Kehadiran(
                        $tanggal,
                        $customdashboard[$i],
                        $where_data,
                        'node',
                        $kolom_keterangan,
                        $kolom_order_by,
                        $adatable_rekapabsen,
                        $adatable_rekapshift,
                        $adatable_ijintidakmasuk,
                        $adatable_alasantidakmasuk
                    );

                    $where_if = $customdashboard[$i]['query_kehadiran_if']; //idpegawai, idatributnilai, idagama, idjamkerja, idlokasi, idjamkerjashift, idjamkerjashift_jenis
                    self::enumCustomDashboard_WhereIf(
                        'kehadiran',
                        $where_if,
                        $adatable_rekapabsen,
                        $adatable_rekapshift,
                        $adatable_jadwalshift,
                        $adatable_ijintidakmasuk,
                        $adatable_alasantidakmasuk,
                        $adatable_atributnilai,
                        $adatable_agama,
                        $adatable_jamkerja,
                        $adatable_lokasi,
                        $adatable_jamkerjashift,
                        $adatable_jamkerjashift_jenis,
                        $adatable_jamkerjakategori
                    );

                    self::enumCustomDashboard_FromWhere(
                        $tanggal,
                        $from01,
                        $where01,
                        $where_del,
                        $adatable_logabsen,
                        $adatable_rekapabsen,
                        $adatable_rekapshift,
                        $adatable_jadwalshift,
                        $adatable_ijintidakmasuk,
                        $adatable_alasantidakmasuk,
                        $adatable_atributnilai,
                        $adatable_agama,
                        $adatable_jamkerja,
                        $adatable_lokasi,
                        $adatable_jamkerjashift,
                        $adatable_jamkerjashift_jenis,
                        $adatable_jamkerjakategori
                    );

                    if ($customdashboard[$i]['query_kehadiran_data'] == 'belumabsen') {
                        $from01 = '_pegawai_seharusnya_absen psa, '.$from01;

                        //persiapkan temporary table dahulu
                        $sql = 'CALL pegawai_seharusnya_absen(:tanggal);';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':tanggal', $tanggal);
                        $stmt->execute();
                    }

                    try {
                        $sql = 'SELECT
                              COUNT(*) as total
                            FROM
                            '.$from01.'
                            WHERE
                              1=1
                            '.$where01.'
                            '.$where_del.'
                            '.$where_data.'
                            '.$where_if.'
                            '.$where_batasan.'
                            LIMIT 1
                            ';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $total = $row['total'];
                    } catch (\Exception $e) { }
                }
                else {
//                $group_by = enumCustomDashboard_PrepareGroupBy($customdashboard[$i]['query_kehadiran_group']);
                }
            }
            else if ($customdashboard[$i]['query_jenis']=='master') {
                $tampil_group = $customdashboard[$i]['query_master_group']==''?'t':'y';
                $navigasi_periode = $customdashboard[$i]['query_master_periode'];

                if ($customdashboard[$i]['query_master_group'] == '') {

                    $where_if = $customdashboard[$i]['query_master_if']; //idpegawai, idatributnilai, idagama, idjamkerja, idlokasi, idjamkerjashift, idjamkerjashift_jenis
                    self::enumCustomDashboard_WhereIf(
                        'master',
                        $where_if,
                        $adatable_rekapabsen,
                        $adatable_rekapshift,
                        $adatable_jadwalshift,
                        $adatable_ijintidakmasuk,
                        $adatable_alasantidakmasuk,
                        $adatable_atributnilai,
                        $adatable_agama,
                        $adatable_jamkerja,
                        $adatable_lokasi,
                        $adatable_jamkerjashift,
                        $adatable_jamkerjashift_jenis,
                        $adatable_jamkerjakategori
                    );

                    //jika tidak ada logabsen, rekapabsen, rekapshift, maka, pegawai harus ada del='t'
                    $where_del = ' AND ((p.status="a" AND (ISNULL(p.tanggaltdkaktif)=true OR (ISNULL(p.tanggaltdkaktif)=false AND p.tanggalaktif<=CURRENT_DATE()))) OR (p.status="t" AND ISNULL(p.tanggaltdkaktif)=false AND p.tanggaltdkaktif>CURRENT_DATE()))';

                    if ($customdashboard[$i]['query_master_data'] == 'pegawai') {
                        self::enumCustomDashboard_FromWhere(
                            $tanggal,
                            $from01,
                            $where01,
                            $where_del,
                            $adatable_logabsen,
                            $adatable_rekapabsen,
                            $adatable_rekapshift,
                            $adatable_jadwalshift,
                            $adatable_ijintidakmasuk,
                            $adatable_alasantidakmasuk,
                            $adatable_atributnilai,
                            $adatable_agama,
                            $adatable_jamkerja,
                            $adatable_lokasi,
                            $adatable_jamkerjashift,
                            $adatable_jamkerjashift_jenis,
                            $adatable_jamkerjakategori
                        );

                        try {
                            $sql = 'SELECT
                          COUNT(*) as total
                        FROM
                        ' . $from01 . '
                        WHERE
                          1=1
                        ' . $where01 . '
                        ' . $where_del . '
                        ' . $where_data . '
                        ' . $where_if . '
                        ' .$where_batasan.'
                        LIMIT 1
                        ';
//                        echo $sql;return;
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $total = $row['total'];
                        } catch (\Exception $e) {}

                    }
                }
                else {
//                $group_by = enumCustomDashboard_PrepareGroupBy($customdashboard[$i]['query_master_group']);
                }
            }

            $custom_node[$i]['id']=$customdashboard[$i]['id'];
            $custom_node[$i]['judul']=$customdashboard[$i]['judul'];
            $custom_node[$i]['icon']=$customdashboard[$i]['icon'];
            $custom_node[$i]['warna']=$customdashboard[$i]['warna'];
            $custom_node[$i]['navigasi_periode']=$navigasi_periode;
            $custom_node[$i]['tampil_group']=$tampil_group;
            $custom_node[$i]['kolom_keterangan']=$kolom_keterangan;
            $custom_node[$i]['total']=$total;
        }

        //$response['custom']=$custom_node;
        return $custom_node;
    }

    public static function getidCustomDashboard()
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT idcustomdashboard FROM customdashboard_email WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', Session::get('emailuser_perusahaan'));
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil = $row['idcustomdashboard'];
        }else{
            $hasil = '';
        }
        return $hasil;
    }

    public static function customDashboard(){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT idcustomdashboard FROM customdashboard_email WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', Session::get('emailuser_perusahaan'));
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            $rowCD = $stmt->fetch(PDO::FETCH_ASSOC);
            $sql = 'SELECT nama,tampil_konfirmasi,tampil_peringkat,tampil_3lingkaran,tampil_sudahbelumabsen,tampil_terlambatdll,tampil_pulangawaldll,tampil_totalgrafik,tampil_peta,tampil_harilibur,tampil_riwayatdashboard FROM customdashboard WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $rowCD['idcustomdashboard']);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);
        }else{
            $sql = 'SELECT "default" as nama,"y" as tampil_konfirmasi,"y" as tampil_peringkat,"y" as tampil_3lingkaran,"y" as tampil_sudahbelumabsen,"y" as tampil_terlambatdll,"y" as tampil_pulangawaldll,"y" as tampil_totalgrafik,"y" as tampil_peta,"y" as tampil_harilibur,"y" as tampil_riwayatdashboard';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_OBJ);
        }
        return $data;
    }

    public static function dataExplode($separator, $data, $label=false)
    {
        $hasil = '';
        $split = explode($separator, $data);
        $totaldata = count($split);
        for($i=0;$i<$totaldata;$i++){
            if($label == true){
                $hasil .= '<label class="label label-primary"">' . $split[$i] . "</label> ";
            }else {
                $hasil .= $split[$i] . ' ';
            }
        }
        //return substr($hasil, -1);
        return $hasil;
    }

    public static function getWarnaHex($nama)
    {
        $warna = ($nama[0] != '#' ? $nama : '#'.$nama);
        switch($warna){
          case "merah" :
              $warna = "#E26A6A";
              break;
          case "kuning" :
              $warna = "#FABE58";
              break;
          case "hijau" :
              $warna = "#00B16A";
              break;
          case "biru" :
              $warna = "#2980b9";
              break;
          case "ungu" :
              $warna = "#8E44AD";
              break;
          case "hitam" :
              $warna = "#22313F";
              break;
          case "putih" :
              $warna = "#d2d2d2";
              break;
          case "soft red" :
              $warna = "#EC644B";
              break;
          case "chestnut" :
              $warna = "#D24D57";
              break;
          case "flamingo" :
              $warna = "#EF4836";
              break;
          case "tall poppy" :
              $warna = "#C0392B";
              break;
          case "razzmatazz" :
              $warna = "#DB0A5B";
              break;
          case "wax flower" :
              $warna = "#F1A9A0";
              break;
          case "cabaret" :
              $warna = "#D2527F";
              break;
          case "lavender" :
              $warna = "#947CB0";
              break;
          case "honey" :
              $warna = "#674172";
              break;
          case "wistful" :
              $warna = "#AEA8D3";
              break;
          case "medium" :
              $warna = "#BF55EC";
              break;
          case "wisteria" :
              $warna = "#9B59B6";
              break;
          case "sherpa" :
              $warna = "#013243";
              break;
          case "picton" :
              $warna = "#59ABE3";
              break;
          case "royal blue" :
              $warna = "#4183D7";
              break;
          case "alice blue" :
              $warna = "#E4F1FE";
              break;
          case "shakespear" :
              $warna = "#52B3D9";
              break;
          case "madison" :
              $warna = "#2C3E50";
              break;
          case "ming" :
              $warna = "#336E7B";
              break;
          case "chambray" :
              $warna = "#3A539B";
              break;
          case "jacksons" :
              $warna = "#1F3A93";
              break;
          case "fountain" :
              $warna = "#5C97BF";
              break;
          case "malachite" :
              $warna = "#00E640";
              break;
          case "summer" :
              $warna = "#91B496";
              break;
          case "aqua" :
              $warna = "#A2DED0";
              break;
          case "gossip" :
              $warna = "#87D37C";
              break;
          case "mountain" :
              $warna = "#1BBC9B";
              break;
          case "riptide" :
              $warna = "#86E2D5";
              break;
          case "shamrock" :
              $warna = "#2ECC71";
              break;
          case "confetty" :
              $warna = "#E9D460";
              break;
          case "jungle" :
              $warna = "#26C281";
              break;
          case "california" :
              $warna = "#F89406";
              break;
          case "casablanca" :
              $warna = "#F4B350";
              break;
          case "buttercup" :
              $warna = "#F39C12";
              break;
          case "jaffa" :
              $warna = "#F27935";
              break;
          case "lynch" :
              $warna = "#6C7A89";
              break;
          case "porcelain" :
              $warna = "#ECF0F1";
              break;
          case "silver" :
              $warna = "#BFBFBF";
              break;
          case "iron" :
              $warna = "#DADFE1";
              break;
        }
        return $warna;
    }

    //excel export utils
    public static function setPropertiesExcel($objPHPExcel,$menu='')
    {
        $objPHPExcel->getProperties()->setCreator('SmartPresence')
                    ->setLastModifiedBy('SmartPresence')
                    ->setTitle($menu == '' ? 'Office 2007 XLSX Document' : $menu)
                    ->setSubject($menu == '' ? 'Office 2007 XLSX Document' : $menu);
    }

    public static function header5baris($objPHPExcel,$cell,$isi){
        for ($i = 1; $i <= 5; $i++) {
            // merge
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':' . $cell . $i);

            // value
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $isi['header_' . $i . '_teks']);

            //font style
            if ($isi['header_' . $i . '_fontstyle'] == 'bold') {
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getFont()->setBold(true);
            } else if ($isi['header_' . $i . '_fontstyle'] == 'italic') {
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getFont()->setItalic(true);
            } else if ($isi['header_' . $i . '_fontstyle'] == 'underline') {
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getFont()->setUnderline(true);
            }

            // center text
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
    }

    public static function passwordExcel($objPHPExcel)
    {
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT gunakanpwd,pwd FROM parameterekspor';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rowPE = $stmt->fetch(PDO::FETCH_ASSOC);
        // password
        if ($rowPE['gunakanpwd'] == 'y') {
            $objPHPExcel->getSecurity()->setLockWindows(true);
            $objPHPExcel->getSecurity()->setLockStructure(true);
            $objPHPExcel->getSecurity()->setWorkbookPassword($rowPE['pwd']);
            $objPHPExcel->getActiveSheet()->getProtection()->setPassword($rowPE['pwd']);
            $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
            $objPHPExcel->getActiveSheet()->getProtection()->setSort(true);
            $objPHPExcel->getActiveSheet()->getProtection()->setInsertRows(true);
            $objPHPExcel->getActiveSheet()->getProtection()->setFormatCells(true);
            $objPHPExcel->getActiveSheet()->getProtection()->setSelectLockedCells(true);
            $objPHPExcel->getActiveSheet()->getProtection()->setSelectUnlockedCells(true);
        }
    }

    public static function logoExcel($posisi,$objPHPExcel,$path,$height,$width,$cell)
    {
        $offsetX = 0;
        if($posisi == 'kanan'){
            $offsetX = -30;
        }
        if (file_exists($path)) {
            $raw = self::decrypt($path);
            $im = imagecreatefromstring($raw);

            $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
            $objDrawing->setName(trans('all.logo'));
            $objDrawing->setDescription('Logo');
            $objDrawing->setImageResource($im);
            $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
            $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
            $objDrawing->setHeight($height);
            $objDrawing->setWidth($width);
            $objDrawing->setCoordinates($cell);
            $objDrawing->setOffsetX($offsetX);
            $objDrawing->setOffsetY(0);
            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        }
    }

    public static function customizeColumn($objPHPExcel,$cell,$row,$value,$width,$bold,$center,$styleArray)
    {
        $objPHPExcel->getActiveSheet()->setCellValue($cell.$row, $value);
        $objPHPExcel->getActiveSheet()->getStyle($cell.$row)->getFont()->setBold($bold);
        if($center == true) {
            $objPHPExcel->getActiveSheet()->getStyle($cell . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($cell . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }
        $objPHPExcel->getActiveSheet()->getStyle($cell.$row)->applyFromArray($styleArray);
        if($width != 0) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($cell)->setWidth($width);
        }
    }

    public static function footerExcel($objPHPExcel,$posisi,$huruf1,$huruf2,$i,$isi)
    {
        $l = $i;
        for ($k = 1; $k <= 6; $k++) {
            if ($k == 4) {
                $l = $i + $isi['footer'.$posisi.'_4_separator'] - 1;
            } else {
                if ($isi['footer'.$posisi.'_' . $k . '_teks'] != '') {
                    $objPHPExcel->getActiveSheet()->mergeCells($huruf1 . $l . ':' . $huruf2 . $l);
                    $objPHPExcel->getActiveSheet()->setCellValue($huruf1 . $l, $isi['footer'.$posisi.'_' . $k . '_teks']);

                    //font style
                    if ($isi['footer'.$posisi.'_' . $k . '_fontstyle'] == 'bold') {
                        $objPHPExcel->getActiveSheet()->getStyle($huruf1 . $l)->getFont()->setBold(true);
                    } else if ($isi['footer'.$posisi.'_' . $k . '_fontstyle'] == 'italic') {
                        $objPHPExcel->getActiveSheet()->getStyle($huruf1 . $l)->getFont()->setItalic(true);
                    } else if ($isi['footer'.$posisi.'_' . $k . '_fontstyle'] == 'underline') {
                        $objPHPExcel->getActiveSheet()->getStyle($huruf1 . $l)->getFont()->setUnderline(true);
                    }

                    // center text
                    $objPHPExcel->getActiveSheet()->getStyle($huruf1 . $l)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }
            }
            $l++;
        }
    }

    public static function setHeaderStyleExcel($objPHPExcel,$arrWidth,$styleArray=array()){
        for ($j = 0; $j < count($arrWidth); $j++) {
            $huruf = Utils::angkaToHuruf($j+1);
            $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->getFont()->setBold(true);
            if(count($styleArray) > 0){
                $objPHPExcel->getActiveSheet()->getStyle($huruf . '1')->applyFromArray($styleArray);
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($huruf)->setWidth($arrWidth[$j]);
        }
    }

    public static function setFileNameExcel($name){
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . time() . '_' . $name . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
    }

    public static function getTotalPesanUser(){
        $pdo = DB::getPdo();
        $sql = 'SELECT id,pesan FROM user_kotakpesan WHERE iduser = :iduser AND isread = "t"';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':iduser', Session::get('iduser_perusahaan'));
        $stmt->execute();
        return $stmt->rowCount();
    }

    public static function simpanUserKotakPesan($iduser,$pesan){
        $pdo = DB::getPdo();
        //tambahkan di table user_kotakpesan
        $sql = 'INSERT INTO user_kotakpesan VALUES(NULL, :iduser, :pesan, "t", NOW())';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':iduser', $iduser);
        $stmt->bindParam(':pesan', $pesan);
        $stmt->execute();

        $to = '';

        $sql = 'SELECT gcmid FROM `user` WHERE id=:iduser LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':iduser', $iduser);
        $stmt->execute();
        if ($stmt->rowCount() != 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $to = $row['gcmid'];
        }

        //hanya kirim gcm jika status nya ada (ada pada parameter) dan berubah dari yg sebelumnya.
        if ($to!='') {
            //kirim gcm
            self::kirimGCM($to, 'konfirmasi', 'server', $pesan);
        }

    }

    public static function getLastDataFromArray($arr,$wanteddata)
    {
        $numItems = count($arr);
        $i = 0;
        $laststartfrom = '';
        foreach($arr as $key) {
            if(++$i === $numItems) {
                $laststartfrom = $key->$wanteddata;
            }
        }
        return $laststartfrom;
    }

    public static function getTextColor($warna) {
        //atur warna teks
        $r = hexdec(substr($warna,0,1)) / 255.0;
        if ($r <= 0.03928) {
            $r = $r/12.92;
        }
        else {
            $r = pow(($r+0.055)/1.055, 2.4);
        }
        $g = hexdec(substr($warna,2,2)) / 255.0;
        if ($g <= 0.03928) {
            $g = $g/12.92;
        }
        else {
            $g = pow(($g+0.055)/1.055, 2.4);
        }
        $b = hexdec(substr($warna,4,2)) / 255.0;
        if ($b <= 0.03928) {
            $b = $b/12.92;
        }
        else {
            $b = pow(($b+0.055)/1.055, 2.4);
        }

        $l = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        if ($l > 0.279) {
            return '#000000';
        }
        else {
            return '#ffffff';
        }
    }

    public static function getDataSelected($pdo,$field,$table,$whereid){
        $sql = 'SELECT '.$field.' FROM '.$table.' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $whereid);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row[$field];
    }

    public static function getDataSelectedUniversal($pdo,$field,$table,$whereid){
        if($pdo == 'perusahaan'){
            $pdo = DB::connection('perusahaan_db')->getPdo();
        }else{
            $pdo = DB::getPdo();
        }
        $sql = 'SELECT '.$field.' FROM '.$table.' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $whereid);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row[$field];
    }

    public static function getidLogAbsenFromRekapAben($idrekapabsen){
        $hasil = '';
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT idlogabsen FROM rekapabsen_logabsen WHERE masukkeluar = "m" AND idrekapabsen = :idrekapabsen ORDER BY waktu DESC LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idrekapabsen', $idrekapabsen);
        $stmt->execute();

        if($stmt->rowCount() > 0 ){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil = $row['idlogabsen'];
        }
        return $hasil;
    }

    public static function getItemPekerjaan($idpekerjaaninput){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT
                    pit.item,
                    IFNULL(pii.jumlah,"") as jumlah,
                    pit.satuan
                FROM
                    pekerjaanitem pit,
                    pekerjaaniteminput pii,
                    pekerjaaninput pi
                WHERE
                    pii.idpekerjaanitem=pit.id AND
                    pii.idpekerjaaninput=pi.id AND
                    pii.idpekerjaaninput = :idpekerjaaninput
                ORDER BY
                    pit.urutan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpekerjaaninput', $idpekerjaaninput);
        $stmt->execute();
        $hasil = '';
        if($stmt->rowCount() > 0){
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $hasil .= $row['item'].' : '.$row['jumlah'].' '.$row['satuan'].'<br>';
            }
        }
        return $hasil;
    }

    public static function errHandlerMsg($msg){
        if (strpos($msg, 'Duplicate entry') !== false) {
            $errmsg = trans('all.datasudahada');
        }else{
            $errmsg = trans('all.terjadigangguan');
        }
        return $errmsg;
    }

    public static function getCurrentDate(){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT get_tanggal_mengacu_eod(NULL) as tanggal';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['tanggal'];
    }

    //jika tanggal ada, formatnya yyyymmdd
    public static function getCurrentDateTime($tanggal = ''){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $currentdate = self::getCurrentDate();
        if($tanggal != ''){
            $sql = 'SELECT STR_TO_DATE("'.$tanggal.'", "%Y%m%d") as tanggal';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentdate = $row['tanggal'];
        }
        $sql = 'SET @a=NULL';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $sql = 'SET @b=NULL';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $sql = 'CALL get_waktu_mengacu_eod("'.$currentdate.'", @a, @b)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $sql = 'SELECT @a as waktuawal, @b as waktuakhir';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public static function convertYmd2Dmy($tanggal){
        if(self::cekDateTime($tanggal) && $tanggal != '') {
            $tgl = explode('-', $tanggal);
            return $tgl[2] . '/' . $tgl[1] . '/' . $tgl[0];
        }else{
            return '';
        }
    }

    public static function convertDmy2Ymd($tanggal){
        if(self::cekDateTime($tanggal) && $tanggal != '') {
            $tgl = explode('/', $tanggal);
            return $tgl[2] . '-' . $tgl[1] . '-' . $tgl[0];
        }else{
            return '';
        }
    }

//    public static function convertTanggal($tanggal,$jenis='id'){
//        $pdo = DB::getPdo();
//        if($jenis == 'id'){
//            $sql = 'SELECT DATE_FORMAT("'.$tanggal.'","%d/%m/%Y") as tanggal';
//        }else{
//            $sql = 'SELECT STR_TO_DATE("'.$tanggal.'","%d/%m/%Y") as tanggal';
//        }
//        $stmt = $pdo->prepare($sql);
//        $stmt->execute();
//        $row = $stmt->fetch(PDO::FETCH_ASSOC);
//        return $row['tanggal'];
//    }

//    public static  function formatDataCantik($data){
//        if(is_int($data) == true){
//            $hasil = self::sec2pretty($data);
//        }else{
//            $hasil = self::tanggalCantik($data);
//        }
//        return $hasil;
//    }

    public static function getCharFromSeparator($char,$separator,$position){
        $char_ex = explode($separator, $char);
        if($position == 'first'){
            $return = $char_ex[0];
        }else{
            $return = $char_ex[1];
        }
        return $return;
    }

    public static function pegawaiAtributCheck($idpegawai, $idatributnilai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM pegawaiatribut WHERE idpegawai = :idpegawai AND idatributnilai IN('.$idatributnilai.') LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        $hasil = false;
        if($stmt->rowCount() == 1){
            $hasil = true;
        }
        return $hasil;
    }

    public static function dataHariLiburBulanIni(){
        $currentdate = self::getCurrentDate();
        // dapatkan daftar harilibur
        $pdo = DB::connection('perusahaan_db')->getPdo();
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
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function payroll_SliceTag($s, $regexpr) {
        $output_array = array();
        preg_match_all($regexpr, $s, $output_array);
        if (count($output_array)>0) {
            $output_array = array_unique($output_array[0]);
        }
        return $output_array;
    }

    public static function payroll_CopyArray1D($arr) {
        $arr_new = array();
        for($i=0;$i<count($arr);$i++) {
            $arr_new[$i] = $arr[$i];
        }
        return $arr_new;
    }

    public static function payroll_Explode3level($s, $splitter) {
        $result = array();
        // format atribut_payroll adalah: abc:a;b;c|xyz:x;y;z
        $level1 = explode($splitter[0], $s); // --> [abc:a;b;c] [xyz:x;y;z]
        for($j=0;$j<count($level1);$j++) {
            $level2 = explode($splitter[1], $level1[$j]); // --> [abc] [a;b;c]
            $key = $level2[0];
            if ($key!='') {
                $result[$key] = explode($splitter[2], $level2[1]);
            }
        }
        return $result;
    }

    public static function payroll_Explode2level($s, $splitter) {
        $result = array();
        // format atribut_payroll adalah: abc:a;b;c|xyz:x;y;z
        $level1 = explode($splitter[0], $s); // --> [abc:a;b;c] [xyz:x;y;z]
        for($j=0;$j<count($level1);$j++) {
            $level2 = explode($splitter[1], $level1[$j]); // --> [abc] [a;b;c]
            $key = $level2[0];
            if ($key!='') {
                $result[$key] = $level2[1];
            }
        }
        return $result;
    }

    //$tipedata adalah "array", "teks", "angka"
    public static function payroll_ReplaceTag($arr_dicari, $arr_pengganti, $formula, $tipedata = "teks") {
        $result = $formula;
        for($i=0;$i<count($arr_dicari);$i++) {
            $dicari = $arr_dicari[$i];
            $key = strtolower($dicari);
            if (strtolower($tipedata)=="array") {
                $pengganti = 'array()';
                if (array_key_exists($key, $arr_pengganti)) {
                    $pengganti = 'array("'.implode('","',$arr_pengganti[$key]).'")';
                }
            }
            else if (strtolower($tipedata)=="teks") {
                $pengganti = '""';
                if (array_key_exists($key, $arr_pengganti)) {
                    $pengganti = '"'.$arr_pengganti[$key].'"';
                }
            }
            else if (strtolower($tipedata)=="angka") {
                $pengganti = '0';
                if (array_key_exists($key, $arr_pengganti)) {
                    $pengganti = $arr_pengganti[$key];
                }
            }
            $result = str_replace($dicari, $pengganti, $result);
        }
        return $result;
    }

    public static function payroll_init_eval() {
        $predefine = '
        function in_arrayi($needle, $haystack) {
            if (is_array($haystack)) {
                return in_array(strtolower($needle), array_map("strtolower", $haystack));
            }
            else {
                return false;
            }
        }

        function get($array, $key, $defaultvalue=0) {
            $key = strtolower($key);
            if (array_key_exists($key, $array)) {
                return $array[$key];
            }
            return $defaultvalue;
        };

        function getvalue($array, $key, $defaultvalue="") {
            $key = strtolower($key);
            if (array_key_exists($key, $array)) {
                if (is_array($array[$key])) {
                    return implode(", ",$array[$key]);
                }
                else {
                    return $array[$key];
                }
            }
            return $defaultvalue;
        };

        function getatributnilai($array, $key, $defaultvalue="") {
            $key = strtolower($key);
            $val = "";
            if (array_key_exists($key, $array)) {
                if (is_array($array[$key])) {
                    $val = implode(",",$array[$key]);
                }
                else {
                    $val = $array[$key];
                }
            }
            if($val != ""){
                $pdo = DB::connection("perusahaan_db")->getPdo();
                $valexplode = explode(",", $val);
                for($i = 0;$i<count($valexplode);$i++){
                    $sql = "SELECT nilai FROM atributnilai WHERE kode = :kode";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(":kode", $valexplode[$i]);
                    $stmt->execute();
                    if($stmt->rowCount() > 0){
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $defaultvalue .= $row["nilai"].", ";
                    }
                }
                $defaultvalue = $defaultvalue != "" ? substr($defaultvalue, 0, -2) : "";
            }
            return $defaultvalue;
        };
        '.PHP_EOL;
        eval($predefine);
    }

    public static function payroll_fetch_pegawai(&$PEGAWAI, &$ATRIBUTNILAI, &$ATRIBUTVARIABLE, $row) {
        $PEGAWAI = array();
        $PEGAWAI['nama'] = $row['nama'];
        $PEGAWAI['idagama'] = $row['idagama'];
        $PEGAWAI['agama'] = $row['agama'];
        $PEGAWAI['pin'] = $row['pin'];
        $PEGAWAI['pemindai'] = $row['pemindai'];
        $PEGAWAI['nomorhp'] = $row['nomorhp'];
        $PEGAWAI['flexytime'] = $row['flexytime'];
        $PEGAWAI['status'] = $row['status'];
        $PEGAWAI['tanggalaktif'] = $row['tanggalaktif'];
        $PEGAWAI['lamacuti'] = isset($row['lamacuti']) ? $row['lamacuti'] : 0;

        $ATRIBUTNILAI = self::payroll_Explode3level($row['payroll_atributnilai'], '|:;');
        $ATRIBUTVARIABLE = self::payroll_Explode2level($row['payroll_atributvariable'], '|:');

    }

    public static function payroll_replace_variablescript(&$script) {
        $script = preg_replace('/\$([^(COUNTER|PEGAWAI|ATRIBUTNILAI|ATRIBUTVARIABLE|LOGABSEN|REKAPABSEN|PAYROLL|JAMKERJA|JADWALSHIFT|sedang_memproses)])/', '\$___$1', $script);
    }

    public static function eval_not_evil($code) {
        require_once('safereval/class.safereval.php');
        self::payroll_init_eval();
        $se = new SaferEval();
        $hasil = '';
        $errors = $se->checkScript($code, false);
        if($errors != '' && count($errors) > 0){
            foreach ($errors as $key => $value) {
                $e = $value;
            }
            if(isset($e['line'])){
                $hasil = $e['name'].' On Line '.$e['line'];
            }else{
                $hasil = $e['name'];
            }
        }
        return $hasil;
    }

    public static function selisihBulan($tanggalawal,$tanggalakhir){
        // @link http://www.php.net/manual/en/class.datetime.php
        $d1 = new DateTime($tanggalawal);
        $d2 = new DateTime($tanggalakhir);

        // @link http://www.php.net/manual/en/class.dateinterval.php
        $interval = $d2->diff($d1);

        return $interval->format('%m');
    }

    public static function cekHariLibur($tanggal,$atributnilai=""){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM harilibur WHERE :tanggal BETWEEN tanggalawal AND tanggalakhir';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggal', $tanggal);
        $stmt->execute();
        $hasil = false;
        if($stmt->rowCount() > 0){
            if($atributnilai != '') {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $sql1 = 'SELECT id FROM hariliburatribut WHERE idharilibur = :idharilibur AND idatributnilai IN('.$atributnilai.')';
                $stmt1 = $pdo->prepare($sql1);
                $stmt1->bindValue(':idharilibur',$row['id']);
                $stmt1->execute();
                if($stmt1->rowCount() > 0){
                    $hasil = true;
                }
            } else {
                $hasil = true;
            }
        }
        return $hasil;
    }

    public static function cekMasukdiHariLibur($tanggal,$idpegawai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $hasil = false;
        if(self::cekHariLibur($tanggal) == true){
            $sql1 = 'SELECT id FROM rekapabsen WHERE idpegawai = :idpegawai AND tanggal = :tanggal AND lamakerja > 0';
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->bindValue(':idpegawai', $idpegawai);
            $stmt1->bindValue(':tanggal', $tanggal);
            $stmt1->execute();
            if($stmt1->rowCount() > 0){
                $hasil = true;
            }
        }
        return $hasil;
    }

    public static function getArrayTotalKomponenMasterGroup($idpayroll_posting){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT ppk.* FROM payroll_posting_komponen ppk, payroll_komponen_master pkm WHERE ppk.komponenmaster_id=pkm.id AND pkm.digunakan = "y" AND pkm.tampilkan = "y" AND ppk.idpayroll_posting = :idpayroll_posting ORDER BY ppk.komponenmaster_urutan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpayroll_posting', $idpayroll_posting);
        $stmt->execute();
        $totalkolom = $stmt->rowCount();
        $arr = [];
        if($totalkolom > 0) {
            $rowKomponen = $stmt->fetchAll(PDO::FETCH_ASSOC);
            for ($i = 0; $i < count($rowKomponen); $i++) {
                if ($rowKomponen[$i]['komponenmaster_group'] != '') {
                    array_push($arr, $rowKomponen[$i]['komponenmaster_group']);
                }
            }
        }
        $hasil = array_count_values($arr);
        return $hasil;
    }

    public static function getSelisihHari($tanggalawal, $tanggalakhir){
        // format tanggalawal dan tanggal akhir adalah dd/mm/yyyy
        $tanggalawal_str = strtotime(Utils::convertDmy2Ymd($tanggalawal));
        $tanggalakhir_str = strtotime(Utils::convertDmy2Ymd($tanggalakhir));
        $jumlahhari_diff = $tanggalakhir_str - $tanggalawal_str;
        return round($jumlahhari_diff / (60 * 60 * 24)) + 1;
    }

    public static function deleteData($pdo,$table,$id,$field=''){
	    $field_id = 'id';
	    if($field != ''){
	        $field_id = $field;
        }
        $sql = 'DELETE FROM '.$table.' WHERE '.$field_id.' = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public static function tombolHapusDatatable($menu,$id,$alert=''){
	    $alert = $alert == '' ? trans('all.alerthapus') : $alert;
        return '<a title="' . trans('all.hapus') . '" href="#" onclick="return submithapus(\'' . $id . '\',\''.$alert.'\',\''.trans('all.ya').'\',\''.trans('all.tidak').'\')"><i class="fa fa-trash" style="color:#ed5565"></i></a>
                <form id="formhapus" action="' . $menu . '/' . $id . '" method="post">
                  <input type="hidden" name="_token" value="' . csrf_token() . '">
                  <input type="hidden" name="_method" value="delete">
                  <input type="submit" id="' . $id . '" style="display:none" name="delete" value="' . trans('all.hapus') . '">
                </form>';
    }

    public static  function cekDateTime($param){
//        $reg_datetime = '/^\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9]):([0-9]|[0-5][0-9])$/'; // yyyy-mm-dd hh:mm:ss
        $reg_datetime = '/^\d{4}(-)(((0)[0-9])|((1)[0-2]))(-)([0-2][0-9]|(3)[0-1]) ([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/'; // yyyy-mm-dd hh:mm:ss
        $reg_datetime_ws = '/^\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9])$/'; // yyyy-mm-dd hh:mm
//        $reg_date = '/^\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01])$/'; // yyyy-mm-dd
        $reg_date = '/^\d{4}(-)(((0)[0-9])|((1)[0-2]))(-)([0-2][0-9]|(3)[0-1])$/'; // yyyy-mm-dd
        $reg_date_ymd = '/^\d{4}(((0)[0-9])|((1)[0-2]))([0-2][0-9]|(3)[0-1])$/'; // yyyymmdd
        $reg_time = '/^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/';
//        $reg_datetime_dmy = '~^(((0[1-9]|[12]\\d|3[01])\\/(0[13578]|1[02])\\/((19|[2-9]\\d)\\d{2}))|((0[1-9]|[12]\\d|30)\\/(0[13456789]|1[012])\\/((19|[2-9]\\d)\\d{2}))|((0[1-9]|1\\d|2[0-8])\\/02\\/((19|[2-9]\\d)\\d{2}))|(29\\/02\\/((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))))$~'; // dd/mm/yyyy
        $reg_date_dmy = '/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/i'; // dd/mm/yyyy
//        $reg_date_dmy = '/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/i'; // dd/mm/yyyy
        $hasil = false;
        if($param == ''){
           $hasil = true;
        } else {
            $arr_reg = [$reg_datetime, $reg_datetime_ws, $reg_date, $reg_time, $reg_date_dmy, $reg_date_ymd];
            for ($i = 0; $i < count($arr_reg); $i++) {
                if (preg_match($arr_reg[$i], $param) == 1) {
                    $hasil = true;
                    break;
                }
            }
        }
        return $hasil;
    }

    // tanggal format yyyy-mm-dd hh:mm:ss
    public static function cekKunciDataPosting($tanggal){
        if(self::cekDateTime($tanggal)) {
            if($tanggal != '') {
                $pdo = DB::connection('perusahaan_db')->getPdo();
                $sql = 'SELECT IF(IFNULL(kuncidatasebelumtanggal, "")="","0",IF(kuncidatasebelumtanggal>=:tanggal,"1","0")) as pengaruh FROM pengaturan';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':tanggal', $tanggal);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['pengaruh']; // jika 0 berarti tidak masalah, jka 1 berarti tidak boleh
            }else{
                return 0;
            }
        }
        return 1;
    }

    public static function cekJadwalShiftAda($idpegawai,$tanggalberlakumulai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $hasil = 't';
        $sql = 'SELECT id FROM jadwalshift WHERE idpegawai = :idpegawai AND tanggal >= STR_TO_DATE(:berlakumulai,"%d/%m/%Y")';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':berlakumulai', $tanggalberlakumulai);
        $stmt->execute();
        if($stmt->rowCount()>0){
            $hasil = 'y';
        }
        return $hasil;
    }

    public static function cekPengaruhJadwalShift($idpegawai,$idjamkerja,$berlakumulai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $hasil = 't';
        $sql = 'SELECT cekpengaruhjadwalshift(NULL, :idpegawai, :idjamkerja, STR_TO_DATE(:berlakumulai,"%d/%m/%Y")) as pengaruh';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':idjamkerja', $idjamkerja);
        $stmt->bindValue(':berlakumulai', $berlakumulai);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row['pengaruh'] == '1') {
            $hasil = 'y';
        }
        return $hasil;
    }

    public static function getJumlahJadwalShift($idpegawai,$tanggal){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT id FROM jadwalshift WHERE idpegawai = :idpegawai AND tanggal = :tanggal';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':berlakumulai', $tanggal);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public static function getTotalHariFrom2Date($tanggalakhir, $tanggalawal){
        $pdo = DB::getPdo();
        $sql = 'SELECT DATEDIFF(:tanggalakhir, :tanggalawal) as totalhari';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggalakhir', $tanggalakhir);
        $stmt->bindValue(':tanggalawal', $tanggalawal);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['totalhari']+1;
    }

    public static function cekJumlahLibur($tanggalawal,$tanggalakhir,$idpegawai){
        $hasil = 0;
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $selisihhari = self::getTotalHariFrom2Date($tanggalakhir,$tanggalawal);
        for($i = 0;$i<$selisihhari;$i++) {
            $tgl = date('Y-m-d', strtotime(date('Y-m-d', strtotime($tanggalawal)) . ' +' . $i . ' day')); // Y-m-d
            $hari = date('w', strtotime(date('Y-m-d',strtotime($tanggalawal)) . ' +' . $i . ' day')) + 1; // 0 minggu 6 sabtu

            // cari tau jamkerja pegawai pada tanggal tsb?
            $sql = 'SELECT getpegawaijamkerja(:idpegawai,"id",:tanggal) as idjamkerja';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':idpegawai',$idpegawai);
            $stmt->bindValue(':tanggal',$tgl);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $idjamkerja = $row['idjamkerja'];
            if($idjamkerja != '') {
                $jenisjamkerja = self::getDataWhere($pdo, 'jamkerja', 'jenis', 'id', $idjamkerja);
                if ($jenisjamkerja == 'full') {
                    $sql = 'SELECT * FROM jamkerjafull WHERE idjamkerja = :idjamkerja ORDER BY berlakumulai DESC LIMIT 1';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':idjamkerja', $idjamkerja);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($row['_' . $hari . '_masukkerja'] == 't') {
                            $hasil = $hasil + 1;
                        }
                    }
                } else if ($jenisjamkerja == 'shift') {
                    $jadwalshift = Utils::getDataCustomWhere($pdo, 'jadwalshift', 'idjamkerjashift', 'idpegawai = ' . $idpegawai . ' AND tanggal = "' . $tgl.'"');
                    if ($jadwalshift == '') {
                        $hasil = $hasil + 1;
                    }
//                    $sql = 'SELECT id as idjamkerjashift FROM jamkerjashift WHERE idjamkerja = :idjamkerja';
//                    $stmt = $pdo->prepare($sql);
//                    $stmt->bindValue(':idjamkerja', $idjamkerja);
//                    $stmt->execute();
//                    if ($stmt->rowCount() > 0) {
//                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                            //                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
//                            //                    $idjamkerjashift = $row['idjamkerjashift'];
//                            $jadwalshift = Utils::getDataCustomWhere($pdo, 'jadwalshift', 'idjamkerjashift', 'idpegawai = ' . $idpegawai . ' AND tanggal = ' . $tgl);
//                            if ($jadwalshift == '') {
//                                $hasil = $hasil + 1;
//                            }
//                        }
//                    }
                }
            }else{
                $hasil = $hasil + 1;
            }
        }

        // cek apakah ada harilibur antara tanggal awal dan tanggal akhir
        $idagamapegawai = self::getDataWhere($pdo,'pegawai','idagama','id',$idpegawai);
        $sql = 'SELECT id FROM harilibur WHERE tanggalawal >= :tanggalawal AND tanggalakhir <= :tanggalakhir';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tanggalawal', $tanggalawal);
        $stmt->bindValue(':tanggalakhir', $tanggalakhir);
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $adadata = true;
                //cek apakah ada hariliburagama?
                $sql1 = 'SELECT idagama FROM hariliburagama WHERE idharilibur = :idharilibur';
                $stmt1 = $pdo->prepare($sql1);
                $stmt1->bindValue(':idharilibur',$row['id']);
                $stmt1->execute();
                if($stmt1->rowCount() > 0) {
                    $adadata = false;
                    // cek apakah sama idagama harillibur dengan idagama pegawai
                    while($row1 = $stmt1->fetch(PDO::FETCH_ASSOC)){
                        if($row1['idagama'] == $idagamapegawai) {
                            $adadata = true;
                        }
                    }
                }

                //cek apakah ada harilibur atribut
                $sql2 = 'SELECT id FROM hariliburatribut WHERE idharilibur = :idharilibur';
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->bindValue(':idharilibur',$row['id']);
                $stmt2->execute();
                if($stmt2->rowCount() > 0) {
                    $adadata = false;
                    // cek apakah pegawai memiliki atribut sama dengan yang di harilibur atribut
                    $sql2 = 'SELECT id FROM hariliburatribut WHERE idharilibur = :idharilibur AND idatributnilai IN(SELECT idatributnilai FROM pegawaiatribut WHERE idpegawai = :idpegawai)';
                    $stmt2 = $pdo->prepare($sql2);
                    $stmt2->bindValue(':idharilibur',$row['id']);
                    $stmt2->bindValue(':idpegawai',$idpegawai);
                    $stmt2->execute();
                    if($stmt2->rowCount() > 0) {
                        $adadata = true;
                    }
                }

                if($adadata) {
                    $hasil = $hasil + 1;
                }
            }
        }
        return $hasil;
    }

    public static function cekPegawaiPenempatan($idpegawai, $idpenempatan, $berlakumulai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $hasil = false;
        $sql = 'SELECT id FROM penempatan_pegawai WHERE idpegawai = :idpegawai AND idpenempatan = :idpenempatan AND berlakumulai = :berlakumulai LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':idpenempatan', $idpenempatan);
        $stmt->bindValue(':berlakumulai', $berlakumulai);
        $stmt->execute();
        if($stmt->rowCount() == 1){
            $hasil = true;
        }
        return $hasil;
    }

    // parameter $kolom(kolom tabel), $value(isi yang di where), $jenis(jenis where ex:"=,like,between"), $sambungan(jenis sambungan where(AND|OR)) $lpad(alias tabel ex:"p.")
    public static function generateWhere($kolom,$value,$jenis,$sambungan="",$lpad=""){
	    $hasil = '';
	    $sambungan = $sambungan == '' ? 'AND' : $sambungan;
	    $temphasil = ' '.$sambungan.' '.$lpad.$kolom;
	    if($jenis == '='){
	        $value = is_int($value) ? $value : '"'.$value.'"';
	        $hasil = $temphasil.' = '.$value.' ';
        }
	    if($jenis == 'like'){
	        $hasil = $temphasil.' LIKE "%'.$value.'%" ';
        }
        return $hasil;
    }

    public static function labelKolom($val){
	    $hasil = $val;
	    if($val == 'y'){
            $hasil = '<center><span class="label label-primary">' . trans('all.ya') . '</span></center>';
        }
	    if($val == 't'){
	        $hasil = '<center><span class="label label-danger">' . trans('all.tidak') . '</span></center>';
        }
	    if($val == 'm'){
	        $hasil = '<center><span class="label label-primary">' . trans('all.masuk') . '</span></center>';
        }
	    if($val == 'k'){
	        $hasil = '<center><span class="label label-danger">' . trans('all.keluar') . '</span></center>';
        }
        if($val == 'mk'){
            $hasil = '<center><span class="label label-warning">' . trans('all.masukkeluar') . '</span></center>';
        }
        if($val == 's'){
            $hasil = '<center><span class="label label-warning">' . trans('all.sakit') . '</span></center>';
        }
        if($val == 'i'){
            $hasil = '<center><span class="label label-info">' . trans('all.ijin') . '</span></center>';
        }
        if($val == 'd'){
            $hasil = '<center><span class="label label-success">' . trans('all.dispensasi') . '</span></center>';
        }
        if($val == 'a'){
            $hasil = '<center><span class="label label-danger">' . trans('all.tidakmasuk') . '</span></center>';
        }
        if($val == 'c'){
            $hasil = '<center><span class="label label-primary">' . trans('all.cuti') . '</span></center>';
        }
        if($val == 'satu'){
            $hasil = '<center><span class="label label-primary">' . trans('all.satu') . '</span></center>';
        }
        if($val == 'multiple'){
            $hasil = '<center><span class="label label-info">' . trans('all.multiple') . '</span></center>';
        }
        if($val == 'aktif'){
            $hasil = '<center><span class="label label-primary">' . trans('all.aktif') . '</span></center>';
        }
        if($val == 'tidakaktif'){
            $hasil = '<center><span class="label label-danger">' . trans('all.tidakaktif') . '</span></center>';
        }
        if($val == 'bs'){
            $hasil = '<center><span class="label label-info">' . trans('all.bebas') . '</span></center>';
        }
        if($val == 'th'){
            $hasil = '<center><span class="label label-success">' . trans('all.terhubung') . '</span></center>';
        }
        if($val == 'confirm'){
            $hasil = '<center><span class="label label-info">' . trans('all.confirm') . '</span></center>';
        }
        if($val == 'approve'){
            $hasil = '<center><span class="label label-primary">' . trans('all.diterima') . '</span></center>';
        }
        if($val == 'notapprove'){
            $hasil = '<center><span class="label label-danger">' . trans('all.ditolak') . '</span></center>';
        }
        if($val == 'full'){
            $hasil = '<center><span class="label label-primary">' . trans('all.full') . '</span></center>';
        }
        if($val == 'shift'){
            $hasil = '<center><span class="label label-success">' . trans('all.shift') . '</span></center>';
        }
        if($val == 'valid'){
            $hasil = '<center><span class="label label-success">' . trans('all.valid') . '</span></center>';
        }
	    return $hasil;
    }

    public static function tombolManipulasi($jenis,$menu,$id,$alert=''){
	    if($jenis == 'ubah'){
            return '<a title="' . trans('all.'.$jenis) . '" href="' . $menu . '/' . $id . '/edit"><i class="fa fa-pencil" style="color:#1ab394"></i></a>&nbsp;&nbsp;';
        } elseif($jenis == 'detail') {
            return '<a title="' . trans('all.'.$jenis) . '" href="' . $menu . '/' . $id . '/detail"><i class="fa fa-pencil-square" style="color:#A2A2A2"></i></a>&nbsp;&nbsp;';
        } elseif($jenis == 'resetkatasandi') {
            return '<a title="' . trans('all.'.$jenis) . '" href="#" onclick="return resetPassword(' . $id . ')"><i class="fa fa-key" style="color:#f8ac59"></i></a>&nbsp;&nbsp;';
        } elseif($jenis == 'ubahcustom') {
	        $url = $menu;
            return '<a title="' . trans('all.'.$jenis) . '" href="' . $url . '"><i class="fa fa-pencil" style="color:#1ab394"></i></a>&nbsp;&nbsp;';
        } elseif($jenis == 'sinkronisasi') {
	        // id adalah pushapi
	        return '<a title="' . trans('all.'.$jenis) . '" href="#" onclick="return sinkronisasi(\'' . $id . '\')"><i class="fa fa-refresh" style="color:#1c84c6"></i></a>&nbsp;&nbsp;';
        } else {
            return self::tombolHapusDatatable($menu,$id,$alert);
        }
    }

    public static function searchDatatableQuery($columns,$multipletable=false){
        $searchkolom = '';
        $where = '';
        for ($i = 0; $i < count($columns); $i++) {
            if($columns[$i] != '') {
                if($multipletable) {
//                    $searchkolom .= ' ' . $columns[$i] . ' like :search OR';
                    $searchkolom .= ' ' . $columns[$i] . ' like :' . $columns[$i] . ' OR';
                }else{
//                    $searchkolom .= ' `' . $columns[$i] . '` like :search OR';
                    $searchkolom .= ' `' . $columns[$i] . '` like :' . $columns[$i] . ' OR';
                }
            }
        }
        if ($searchkolom != '') {
            $where .= ' AND (' . substr($searchkolom, 0, -2) . ')';
        }
        return $where;
    }

//    public static function searchDatatableQuery($search,$columns,$multipletable=false){
//        $searchkolom = '';
//        $where = '';
//        $search = str_replace('"','', $search);
//        $search = str_replace("'",'', $search);
//        for ($i = 0; $i < count($columns); $i++) {
//            if($columns[$i] != '') {
//                if($multipletable) {
//                    $searchkolom .= ' ' . $columns[$i] . ' like "%'.$search.'%" OR';
//                }else{
//                    $searchkolom .= ' `' . $columns[$i] . '` like "%'.$search.'%" OR';
//                }
//            }
//        }
//        if ($searchkolom != '') {
//            $where .= ' AND (' . substr($searchkolom, 0, -2) . ')';
//        }
//        return $where;
//    }

    public static function jsonDatatable($draw,$recordsTotal,$recordsFiltered,$data){
        $json_data = array(
            "draw"            => intval($draw),
            "recordsTotal"    => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data"            => $data
        );
        return json_encode($json_data);
    }

    public static function cekHakakses($menu,$perintah){
        $ada = false;
        if(Session::get('perusahaan_expired') == 'tidak') {
            for ($i = 0; $i < strlen($perintah); $i++) {
                if (strpos(Session::get('hakakses_perusahaan')->$menu, $perintah[$i]) !== false) {
                    $ada = true;
                    break;
                }
            }
        } else {
            // ketika mengakses menu pegawai
            if($menu == "pegawai" || $menu == "hapusdata") {
                for ($i = 0; $i < strlen($perintah); $i++) {
                    if (strpos(Session::get('hakakses_perusahaan')->$menu, $perintah[$i]) !== false) {
                        $ada = true;
                        break;
                    }
                }
            }
        }

       return $ada;
    }

    public static function getLamaCuti($tahun,$idpegawai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'CALL get_cuti(:tahun)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tahun', $tahun);
        $stmt->execute();

        $sql = 'SELECT lama FROM _cuti WHERE idpegawai = :idpegawai';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();
        $hasil = 0;
        if($stmt->rowCOunt() > 0) {
            $rowCuti = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil = $rowCuti['lama'] == '' ? 0 : $rowCuti['lama'];
        }
        return $hasil;
    }

    public static function getJatahCuti($tahun,$idpegawai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'CALL get_cuti_pegawai(:tahun,:idpegawai,null)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tahun', $tahun);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->execute();

        $sql = 'SELECT jatah FROM _cuti_pegawai';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $hasil = 0;
        if($stmt->rowCOunt() > 0) {
            $rowCuti = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil = $rowCuti['jatah'] == '' ? 0 : $rowCuti['jatah'];
        }
        return $hasil;
    }

    public static function connectPerusahaan($idperusahaan)
    {
        $pdo = DB::getPdo();
        $sql = 'SELECT dbhost,dbport,dbuser,AES_DECRYPT(dbpass, "e754251708594345576d9407126e4d46") as dbpass,dbname,folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idperusahaan', $idperusahaan);
        $stmt->execute();
        $route = $stmt->fetch(PDO::FETCH_OBJ);
        $pdop = 'error';
        if($stmt->rowCount() > 0) {
            // set koneksi database
            Config::set('database.connections.perusahaan_db.host', $route->dbhost);
            Config::set('database.connections.perusahaan_db.port', $route->dbport);
            Config::set('database.connections.perusahaan_db.username', $route->dbuser);
            Config::set('database.connections.perusahaan_db.password', $route->dbpass);
            Config::set('database.connections.perusahaan_db.database', $route->dbname);
            try {
                $pdop = DB::connection('perusahaan_db')->getPdo();
            } catch (\Exception $e){
                $pdop = 'error';
            }
        }
        return $pdop;
    }

    public static function decodeJWT($jwttoken)
    {
        $arr = array();

        try {
            $bearer = 'Bearer';
            $jwttoken = substr($jwttoken, strpos($jwttoken, $bearer) + strlen('Bearer') + 1);

            if ($jwttoken != "") {
                $token = (new Parser())->parse((string)$jwttoken); // Parses from a string

                $arr['typ'] = $token->getHeader('typ');
                $arr['alg'] = $token->getHeader('alg');
                $arr['iss'] = $token->getClaim('iss');
                $arr['jti'] = $token->getClaim('jti');
                $arr['exp'] = $token->getClaim('exp');
                $arr['as'] = $token->getClaim('as');
                $arr['id'] = $token->getClaim('id');
                $arr['pid'] = $token->getClaim('pid');
                $arr['scopes'] = implode($token->getClaim('scopes'));
            }
        } catch (\Exception $e) {

        }
        return $arr;
    }

    // untuk memastikan atributnilai pegawai
    public static function filterAtributPegawai($idpegawai,$atributnilai){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $atributharilibur = '';
        $where = '';
        if($atributnilai != ''){
            $where = ' AND idatributnilai IN('.$atributnilai.')';
        }
        $sql_ahl = 'SELECT IFNULL(GROUP_CONCAT(idatributnilai),"") as idatributnilai FROM pegawaiatribut WHERE idpegawai = :idpegawai'.$where;
        $stmt_ahl = $pdo->prepare($sql_ahl);
        $stmt_ahl->bindValue(':idpegawai', $idpegawai);
        $stmt_ahl->execute();
        if($stmt_ahl->rowCount() > 0){
            $row_ahl = $stmt_ahl->fetch(PDO::FETCH_ASSOC);
            $atributharilibur = $row_ahl['idatributnilai'];
        }
        return $atributharilibur;
    }

    public static function getYearFromDate($tanggal,$separator="-"){
	    $hasil = '';
        $tgl = explode($separator, $tanggal);
        if($separator == '-'){
            $hasil = $tgl[0];
        }
        if($separator == '/'){
            $hasil = $tgl[2];
        }
        return $hasil;
    }

    public static function deleteSession($session){
        if(Session::has($session)){
            Session::forget($session);
        }
    }

    public static function getPegawaiJamKerja($kolom,$idpegawai,$tanggal){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT getpegawaijamkerja(:idpegawai,:kolom,:tanggal) as jamkerja';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':kolom', $kolom);
        $stmt->bindValue(':tanggal', $tanggal);
        $stmt->execute();
        $hasil = '';
        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasil = $row['jamkerja'];
        }
        return $hasil;
    }

    public static function getPegawaiJadwalShift($idpegawai,$tanggal){
        $pdo = DB::connection('perusahaan_db')->getPdo();
        $sql = 'SELECT js.idjamkerjashift,IFNULL(jks.namashift,"") as namashift,IFNULL(jks.kode,"") as kode FROM jadwalshift js LEFT JOIN jamkerjashift jks ON js.idjamkerjashift=jks.id WHERE js.idpegawai = :idpegawai AND js.tanggal = :tanggal';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idpegawai', $idpegawai);
        $stmt->bindValue(':tanggal', $tanggal);
        $stmt->execute();
        $hasil = array();
        if($stmt->rowCount() > 0){
            $hasil = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $hasil;
    }

    /*
     * method for generate invoice pdf, which path get from perusahaan_route.folderroot + '/invoice-pdf/' + filename.pdf
     * return $pathFilePDF
     * ex: /Users/hadi/works/smartpresence/folderroot/_0263_SMARTPRESENCETESTING/invoice-pdf/20200720_SPA10950-1_SMARTPRESENCE TRIAL.pdf
     */

    public static function generatePDFInvoice($invoice, $cannopus){

        $periodeWording = self::getBulanId(date("m", strtotime($invoice->updated_at))) . ' ' . date("Y", strtotime($invoice->updated_at));

        //convert number to wording
        $numberFormatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        $totalWording = $numberFormatter->format($invoice->amount_received);

        if($invoice->periode == 12) {
            $discount = $invoice->unitprice > env('YEARLY_DISCOUNT',0) ? env('YEARLY_DISCOUNT',0) : 0;
            $invoice->unitprice = $invoice->unitprice - $discount;
        }

        $subtotal = $invoice->unitprice * $invoice->limitpegawai * $invoice->periode;

        $item = array(
            'no' => 1,
            'productName' => 'SMARTPRESENCE - ABSENSI '. (($invoice->periode < 12) ? 'MONTHLY' : 'YEARLY') . ' - Periode ' . $periodeWording,
            'qty' => $invoice->limitpegawai,
            'period' => $invoice->periode .' Bulan',
            'unitPrice' => number_format($invoice->unitprice, 2, '.', ','),
            'taxed' => 'X',
            'amount' => number_format($subtotal, 2, '.', ',')
        );

        $items = array();
        array_push($items, $item);

        $ppn = $subtotal * 0.1; //ppn 10%
        $total = $subtotal + $ppn + $cannopus->request->data->paymentCode; //subtotal + ppn + kodeunik

        $data = array(
            'isVoid' => true,
            'isTaxIncluded' => env('INVOICE_IS_TAX_INCLUDED'),
            'invoiceNo' => $invoice->order_id,
            'invoiceTanggal' => date("d-m-Y", strtotime($invoice->updated_at)),
            'namaCustomer' => $invoice->nama,
            'alamatCustomer' => $invoice->pic_alamat,
            'telephoneCustomer' => $invoice->pic_notelp,
            'dueDateCustomer' => date("d-m-Y", strtotime($invoice->due_date)),
            'items' => $items,
            'subTotal' => number_format($subtotal, 2, '.', ','),
            'ppn' => number_format($ppn, 2, '.', ','),
            'kodeUnik' => number_format($cannopus->request->data->paymentCode, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amountReceived' => number_format($invoice->amount_received, 2, '.', ','),
            'balanceDue' => number_format($invoice->amount_received, 2, '.', ','),
            'namaBank' => $cannopus->request->data->bank,
            'cabangBank' => env('INVOICE_BRANCH_BANK'),
            'nomorBank' => $cannopus->request->data->accountNumber,
            'atasNamaBank' => $cannopus->request->data->accountName,
            'terbilang' => $totalWording . ' RUPIAH',
        );

        $fileName = date("Ymd", strtotime($invoice->updated_at)) . '_' . $invoice->order_id . '_' . $invoice->nama .'.pdf';
        $path = $invoice->folderroot . '/invoice-pdf/';

        //check file existing or not, if file is not existing so create folder
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        //generate pdf
        try{
            $pdf = PDF::loadView('invoicepdf', $data);
            $pdf->save($path . $fileName);

            $obj = (object)array();
            $obj->pdf_status = true;
            $obj->pdf_path = $path . $fileName;
            $obj->message = 'pdf success generate';
            $json = json_encode($obj);

            return $json;
        }
        catch(Exception $e){

            $obj = (object)array();
            $obj->pdf_status = false;
            $obj->pdf_path = '';
            $obj->message = 'pdf failed generate';
            $json = json_encode($obj);

            return $json;
        }

    }

    /*
     * method for send email invoice with attach pdf
     */
    public static function kirimEmailInvoice($invoice, $pathFilePDF){

        $subject = '[SmartPresence] Pembayaran Berlangganan Anda Berhasil - No. Pembayaran ' . $invoice->order_id;

        //Kalau tanggal pembayaran <= masa aktif, berarti start langganannya dari tanggal masa aktif.
        if($invoice->updated_at <= $invoice->due_date){
            $startBerlanggananDate = $invoice->due_date;
        }else {
            $startBerlanggananDate = $invoice->updated_at;
        }
        //convert 2020-01-31 jadi 31 Januari 2020 (dengan bahasa default indonesia)
        $startBerlanggananDate = date("d", strtotime($startBerlanggananDate)) . ' ' . self::getBulanId(date("m", strtotime($startBerlanggananDate))) . ' ' . date("Y", strtotime($startBerlanggananDate));
        $endBerlanggananDate = date("d", strtotime($invoice->aktifsampai)) . ' ' . self::getBulanId(date("m", strtotime($invoice->aktifsampai))) . ' ' . date("Y", strtotime($invoice->aktifsampai));

        $data = array(
            'forward_mail' => ENV('FORWARD_MAIL'),
            'email' => $invoice->pic_email,
            'subject' => $subject,
            'firstName' => $invoice->pic_nama,
            'kodePerusahaan' => $invoice->kode,
            'namaPerusahaan' => $invoice->nama,
            'invoiceNo' => $invoice->order_id,
            'jumlahBayar' => number_format($invoice->amount_received, 2, '.', ','),
            'invoiceTanggal' => date("d/m/Y", strtotime($invoice->updated_at)),
            'jumlahUser' => $invoice->limitpegawai,
            'pricePerUser' => number_format($invoice->unitprice, 2, '.', ','),
            'startDate' => $startBerlanggananDate,
            'endDate' => $endBerlanggananDate,
            'periodeBayar' => 'Per ' . (($invoice->periode != 1) ? ($invoice->periode . ' ') : '') . 'Bulan',
            'pathFile' => $pathFilePDF
        );

        //send email + attach pdf invoice
        Mail::send('templateemail.invoice', $data, function($message) use ($data) {
            $message->to($data['email'])->subject($data['subject']);
            $message->from('no-reply@smartpresence.id','Smart Presence');
            $message->attach($data['pathFile']);
        });

        Mail::send('templateemail.invoice', $data, function($message) use ($data) {
            $message->to($data['forward_mail'])->subject($data['subject']);
            $message->from('no-reply@smartpresence.id','Smart Presence');
            $message->attach($data['pathFile']);
        });

        return true;
    }

    /*
     * method integrate data for send email invoice with attach pdf
     */
    public static function sendEmailwithPDFInvoice($cannopus){

        $cannopus = json_decode($cannopus);

        $order_id = $cannopus->request->data->merchantOrderId;

        $sql = 'SELECT invoice.id,
            invoice.order_id,
            invoice.status_bayar,
            invoice.total,
            invoice.periode,
            invoice.amount_received,
            invoice.due_date,
            invoice.created_at,
            invoice.updated_at,
            perusahaan.nama,
            perusahaan.kode,
            perusahaan.pic_nama,
            perusahaan.pic_alamat,
            perusahaan.pic_notelp,
            perusahaan.pic_email,
            perusahaan_kuota.limitpegawai,
            perusahaan_kuota.aktifsampai,
            perusahaan_kuota.unitprice,
            perusahaan_route.folderroot
            FROM invoice
            LEFT JOIN perusahaan ON invoice.idperusahaan = perusahaan.id
            LEFT JOIN perusahaan_kuota ON perusahaan_kuota.idperusahaan = perusahaan.id
            LEFT JOIN perusahaan_route ON perusahaan_route.idperusahaan = perusahaan.id
            WHERE order_id = :order_id';

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':order_id', $order_id);
        $stmt->execute();
        $invoice = $stmt->fetch(PDO::FETCH_OBJ);

        $statusBayarWording = array(
            '[0] Initial',
            '[1] Pending',
            '[2] Success',
            '[3] Expired'
        );
        switch ($invoice->status_bayar) {
            case 0:
            case 1:
            case 3:
                Log::info('Failed to send email invoice with order id ' . $order_id . ' because status_bayar is ' . $statusBayarWording[$invoice->status_bayar]);
                break;
            case 2: //2: Success
                Log::info('Process to send email invoice with order id ' . $order_id);

                $resultPDF = Utils::generatePDFInvoice($invoice, $cannopus);

                $resultPDF = json_decode($resultPDF);

                if($resultPDF->pdf_status) {
                    $resultEmail = Utils::kirimEmailInvoice($invoice, $resultPDF->pdf_path);
                    if($resultEmail){
                        $resultPDF->email_status = true;
                        $resultPDF->message = "pdf and email success send";
                        $resultPDF = json_encode($resultPDF);
                        return $resultPDF;
                    }else {
                        $resultPDF->email_status = true;
                        $resultPDF->message = "pdf success generate but email fail send";
                        $resultPDF = json_encode($resultPDF);
                        return $resultPDF;
                    }
                }else {

                    $resultPDF->email_status = false;
                    $resultPDF = json_encode($resultPDF);
                    return $resultPDF;
                }

                break;
            default:
                Log::info('Failed to send email invoice with order id ' . $order_id . ' because status_bayar is [' . $invoice->status_bayar .'] unrecognized');
        }
    }

    // format paramter tanggal ex: 2020-01-01,2020-01-02
    public static function KumpulanTanggal($paramkumpulantanggal){
        $result = '';
        if($paramkumpulantanggal != '') {
            $tglnew = '';
            $ex_tgl = explode(',', $paramkumpulantanggal);
            $arrbulan = array("", trans('all.januari'), trans('all.februari'), trans('all.maret'), trans('all.april'), trans('all.mei'), trans('all.juni'), trans('all.juli'), trans('all.agustus'), trans('all.september'), trans('all.oktober'), trans('all.november'), trans('all.desember'));
            $bulantahunlama = '';
            for ($i = 0; $i < count($ex_tgl); $i++) {
                $ex = explode('-', $ex_tgl[$i]);
                $tgl = (int)$ex[2];
                $bln = (int)$ex[1];
                $thn = $ex[0];

                $bulantahun = $arrbulan[$bln] . ' ' . $thn;
                $date = ', ' . $tgl . ' ' . $bulantahun;
                $tglnew = $bulantahunlama == $bulantahun ? str_replace(' ' . $bulantahun, '', $tglnew) : $tglnew;
                $tglnew .= $date;
                $bulantahunlama = $bulantahun;
            }
            if ($tglnew != '') {
                $result = ltrim($tglnew, $tglnew[0] . $tglnew[1]);
            }
        }
        return $result;
    }

    /**
     * Functions curl_get
     * melakukan hit api menggunakan method GET
     *
     * @param string $url
     * @param array $header
     *
     * @return array
     * @author apriana@bsa.id
     */
    public static function curl_get($url, $header){
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, "PDAMInfo/request at ".date("Y.m.d"));
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        // $output contains the output string
        $result_curl = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($ch);

        // tutup curl
        curl_close($ch);

        $hasil = json_decode($result_curl, true);

        return array("result"=>$result_curl, "code"=>$code);
    }

    /**
     * Functions curl_post
     * melakukan hit api menggunakan method POST
     *
     * @param string $url
     * @param array $header
     * @param array $params
     *
     * @return array
     * @author apriana@bsa.id
     */
    public static function curl_post($url, $header, $params){
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, "PDAMInfo/request at ".date("Y.m.d"));
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        // curl_setopt($ch, CURLOPT_HEADER, 0);

        //execute post
        $result_curl = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($ch);

        curl_close($ch);

        $hasil = json_decode($result_curl, true);

        return array("result"=>$result_curl, "code"=>$code);
    }

    public static function create_log($idperusahaan, $folder, $log){
        $pdo = DB::getPdo();
        $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idperusahaan', $idperusahaan);
        $stmt->execute();
        if($stmt->rowCount() > 0) {

            $rowFolder = $stmt->fetch(PDO::FETCH_ASSOC);

            $dirPath = $rowFolder["folderroot"] . '/canopus/' . $folder . '/';
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0777, true);
            }

            $log_file = $dirPath . "log-" . date("Y-m-d") . ".log";

            $currentLog = $log . "\n";

            file_put_contents($log_file, $currentLog, FILE_APPEND | LOCK_EX);

            unset($currentLog);
        }

    }
}
