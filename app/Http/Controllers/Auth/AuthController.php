<?php

namespace App\Http\Controllers\Auth;

use App\Utils;

use Validator;
use Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Redirect;
use DB;
use PDO;
use Hash;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    public function daftar(){
        $nama = Request::input('nama');
        $email = Request::input('email');
        $nomorhp = Request::input('nomorhp');
        $katasandi = Request::input('katasandi');
        $origin = Request::input('origin');
        $datacaptcha = Request::input('g-recaptcha-response');
        $captcha = Utils::captchaCheck($datacaptcha);
        if($captcha == 1) {
            $pdo = DB::getPdo();
            //cek apakah email sudah digunakan
            $sql = 'SELECT id FROM `user` WHERE email = :email';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':email', trim($email));
            $stmt->execute();
            if($stmt->rowCount() == 0) {
                try
                {
                    $pdo->beginTransaction();
                    $sql = 'INSERT INTO `user` VALUES(NULL,:nama,:email,:nomorhp,:katasandi,:freetrial,NULL,"tk",1,1,0,NULL,:origin,NOW())';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':nama', $nama);
                    $stmt->bindValue(':email', trim($email));
                    $stmt->bindValue(':nomorhp', $nomorhp);
                    $stmt->bindValue(':katasandi', Hash::make($katasandi));
                    $stmt->bindValue(':freetrial', config('consts.FREE_TRIAL'));
                    $stmt->bindValue(':origin', $origin);
                    $stmt->execute();

                    $iduser = $pdo->lastInsertId();
                    $kode = md5($iduser.'_pendafaran_smartpresence!');
                    $sql1 = 'INSERT INTO user_konfirmasi VALUES(NULL,:iduser,:kode,1,"t",NOW())';
                    $stmt1 = $pdo->prepare($sql1);
                    $stmt1->bindValue(':iduser', $iduser);
                    $stmt1->bindValue(':kode', $kode);
                    $stmt1->execute();

                    $iduserkonfirmasi = $pdo->lastInsertId();

                    $pdo->commit();

                    //kirim email
                    $sql2 = 'SELECT kode FROM user_konfirmasi WHERE id = :iduserkonfirmasi';
                    $stmt2 = $pdo->prepare($sql2);
                    $stmt2->bindValue(':iduserkonfirmasi', $iduserkonfirmasi);
                    $stmt2->execute();
                    $datauser = $stmt2->fetch(PDO::FETCH_OBJ);
                    $kode = $datauser->kode;
                    $data = array('nama' => $nama, 'kode' => $kode, 'nomorhp' => $nomorhp, 'email' => $email, 'iduserkonfirmasi' => $iduserkonfirmasi);

                    Mail::send('templateemail.daftar', $data, function($message) use ($data) {
                        $message->to($data['email'])->subject('Register');
                        $message->from('no-reply@smartpresence.id','Smart Presence');
                    });

                    if($origin == 'ads'){
//                        return redirect('daftar-ads/terimakasih')->with('message', trans('all.silahkancekemailandauntukkonfirmasi'));
                        return redirect('https://smartpresence.id/thankyou/');
                    } else {
                        return redirect('login')->with('message', trans('all.silahkancekemailandauntukkonfirmasi'));
                    }
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return Utils::redirectForm(trans('all.terjadigangguan'));
                }
            }else{
                return Utils::redirectForm(trans('all.emailsudahdigunakan'),'alert');
            }
        }else{
            return Utils::redirectForm(trans('all.captchatidakvalid'));
        }
    }

    public function konfirmasiDaftar($iduserkonfirmasi, $kode)
    {
        //return $kode;
        $pdo = DB::getPdo();
        $sql = 'SELECT iduser,status FROM user_konfirmasi WHERE id=:id AND kode = :kode';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $iduserkonfirmasi);
        $stmt->bindValue(':kode', $kode);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row['status'] == 't') {
                try {
                    $pdo->beginTransaction();

                    $sql1 = 'SELECT nama,email FROM `user` WHERE id = :iduser';
                    $stmt1 = $pdo->prepare($sql1);
                    $stmt1->bindValue(':iduser', $row['iduser']);
                    $stmt1->execute();
                    $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

                    $sql2 = 'UPDATE user_konfirmasi set status = "v" WHERE id = :iduserkonfirmasi AND kode = :kode';
                    $stmt2 = $pdo->prepare($sql2);
                    $stmt2->bindValue(':iduserkonfirmasi', $iduserkonfirmasi);
                    $stmt2->bindValue(':kode', $kode);
                    $stmt2->execute();

                    $sql3 = 'UPDATE `user` SET status = "a" WHERE id = :iduser';
                    $stmt3 = $pdo->prepare($sql3);
                    $stmt3->bindValue(':iduser', $row['iduser']);
                    $stmt3->execute();

                    // insert ke table onboardingtime
                    $sql4 = 'INSERT INTO onboardingtime VALUES(null,:iduser,1,0,NOW())';
                    $stmt4 = $pdo->prepare($sql4);
                    $stmt4->bindValue(':iduser', $row['iduser']);
                    $stmt4->execute();

                    if (Session::has('userbaru_perusahaan')) {
                        Session::forget('userbaru_perusahaan');
                    }

                    $pdo->commit();

                    return redirect('login')->with('message', trans('all.akunandaberhasildiaktifkan'));
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    return redirect('daftar')->with('message', trans('all.terjadigangguan'));
                }
            }else if($row['status'] == "v"){
                return redirect('login')->with('message', trans('all.andasudahmelakukankonfirmasi'));
            }else{
                return redirect('login')->with('message', trans('all.terjadigangguan'));
            }
        }else{
            return abort(404);
        }
    }

    public function kirimUlangKonfirmasiDaftar($iduser)
    {
        $pdo = DB::getPdo();
        $sql = 'SELECT nama,email,nomorhp,status FROM `user` WHERE id = :iduser';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':iduser', $iduser);
        $stmt->execute();
        if($stmt->rowCount() != 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $nama = $row['nama'];
            $email = $row['email'];
            $nomorhp = $row['nomorhp'];
            $status = $row['status'];

            if ($status=='tk') {
                $sql = 'SELECT id as iduserkonfirmasi,kode,status FROM user_konfirmasi WHERE iduser = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $iduser);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row['status']=='t') {
                        $sql3 = 'UPDATE user_konfirmasi SET jumlahpercobaan = jumlahpercobaan+1 WHERE id = :id';
                        $stmt3 = $pdo->prepare($sql3);
                        $stmt3->bindValue(':id', $row['iduserkonfirmasi']);
                        $stmt3->execute();

                        $data = array('nama' => $nama, 'kode' => $row['kode'], 'nomorhp' => $nomorhp, 'email' => $email, 'iduserkonfirmasi' => $row['iduserkonfirmasi']);

                        Mail::send('templateemail.daftar', $data, function ($message) use ($data) {
                            $message->to($data['email'])->subject('Register');
                            $message->from('no-reply@smartpresence.id', 'Smart Presence');
                        });

                        return redirect('/')->with('message', trans('all.kirimulangberhasil'));
                    }
                    else {
                        return redirect('/')->with('message', trans('all.andasudahmelakukankonfirmasi'));
                    }
                } else {
                    $kode = md5($iduser . '_pendafaran_smartpresence!');
                    $sql1 = 'INSERT INTO user_konfirmasi VALUES(NULL,:iduser,:kode,1,"t",NOW())';
                    $stmt1 = $pdo->prepare($sql1);
                    $stmt1->bindValue(':iduser', $iduser);
                    $stmt1->bindValue(':kode', $kode);
                    $stmt1->execute();

                    $iduserkonfirmasi = $pdo->lastInsertId();

                    $data = array('nama' => $nama, 'kode' => $kode, 'nomorhp' => $nomorhp, 'email' => $email, 'iduserkonfirmasi' => $iduserkonfirmasi);

                    Mail::send('templateemail.daftar', $data, function ($message) use ($data) {
                        $message->to($data['email'])->subject('Register');
                        $message->from('no-reply@smartpresence.id', 'Smart Presence');
                    });

                    return redirect('/')->with('message', trans('all.kirimulangberhasil'));
                }
            }
            else if ($status=='a') {
                return redirect('/')->with('message', trans('all.andasudahmelakukankonfirmasi'));
            }
            else {
                return redirect('login')->with('message', trans('all.datatidakditemukan'));
            }
        }else{
            return redirect('login')->with('message', trans('all.datatidakditemukan'));
        }
    }

    // lupa katasandi
    public function lupaKataSandi(){
        $pdo = DB::getPdo();
        $email = Request::input('email');
        $sql = 'SELECT id, nomorhp FROM user WHERE email=:email AND status="a" LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $iduser = $row['id'];
            $nomorhp = $row['nomorhp'];

            if ($nomorhp!='') {
                $kodeverifikasi = Utils::generateRandomString(4);

                $sql = 'INSERT INTO user_forgetpwd
                        VALUES(0, :iduser, :kodeverifikasi, DATE_ADD(NOW(), INTERVAL 12 HOUR))
                        ON DUPLICATE KEY UPDATE
                            kodeverifikasi=:kodeverifikasi0,
                            expired=DATE_ADD(NOW(), INTERVAL 12 HOUR)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':iduser', $iduser);
                $stmt->bindValue(':kodeverifikasi', $kodeverifikasi);
                $stmt->bindValue(':kodeverifikasi0', $kodeverifikasi);
                $stmt->execute();

                $isipesan = 'VERFICIATION CODE: '.$kodeverifikasi.' '.chr(13).chr(10).'Valid until next 12 hours.';
                //Utils::kirimSms($nomorhp,$isipesan);
                // masukkan ke dalam antrean kirimsms
                $sql3 = 'INSERT INTO _kirimsms VALUES(0, NULL, :tujuan, LEFT(:isi,159), NOW())';
                $stmt3 = $pdo->prepare($sql3);
                $stmt3->bindValue(':tujuan', $nomorhp);
                $stmt3->bindValue(':isi', $isipesan);
                $stmt3->execute();

                $sql4 = 'INSERT INTO logsms VALUES(NULL,NULL,:tujuan,:isi,NOW())';
                $stmt4 = $pdo->prepare($sql4);
                $stmt4->bindValue(':tujuan', $nomorhp);
                $stmt4->bindValue(':isi', $isipesan);
                $stmt4->execute();

                return view('lupakatasandi_verifikasi', ['jenis' => 'lupakatasandi_verifikasi']);
            }
            else {
                return redirect('lupakatasandi')->with('message', trans('all.nomorhptidakada'));
            }
        }
        else {
            return redirect('lupakatasandi')->with('message', trans('all.usersalahatautidakaktif'));
        }
    }

    // lupa katasandi verifikasi
    public function lupaKataSandi_verifikasi(){

        $pdo = DB::getPdo();
        $kodeverifikasi = Request::input('kodeverifikasi');
        $katasandibaru = Request::input('katasandibaru');

        $sql = 'SELECT id,iduser FROM user_forgetpwd WHERE kodeverifikasi=:kodeverifikasi AND expired>NOW() LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':kodeverifikasi', $kodeverifikasi);
        $stmt->execute();

        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $idforgetpwd = $row['id'];
            $iduser = $row['iduser'];

            $sql = 'UPDATE user SET password=:password WHERE id=:iduser';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':password', Hash::make($katasandibaru));
            $stmt->bindValue(':iduser', $iduser);
            $stmt->execute();

            //hapus token lama
            $sql = 'INSERT INTO authtokenblacklist_user SELECT iduser, idtoken, expired, NOW() FROM authtoken_user WHERE iduser=:iduser ON DUPLICATE KEY UPDATE authtokenblacklist_user.expired=GREATEST(authtokenblacklist_user.expired, authtoken_user.expired);';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':iduser', $iduser);
            $stmt->execute();

            Utils::deleteData($pdo,'authtoken_user',$iduser,'iduser');
            Utils::deleteData($pdo,'user_forgetpwd',$idforgetpwd);

            return redirect('login')->with('message', trans('all.katasandiberhasildiubah'));
        }
        else {
            return redirect('lupakatasandi_verifikasi')->with('message', trans('all.kodeverifikasisalahatauexpired'));
        }
    }

    public function lupaKataSandi_verifikasiAlternatif()
    {
        $pdo = DB::getPdo();
        $email = Request::input('email');
        $kodeverifikasi = Request::input('kodeverifikasi');
        $katasandibaru = Request::input('katasandibaru');

        $sql = 'SELECT
                    ufp.id,
                    ufp.iduser
                FROM
                    user_forgetpwd ufp,
                    `user` u
                WHERE
                    u.email=:email AND 
                    ufp.iduser=u.id AND
                    kodeverifikasi=:kodeverifikasi AND
                    expired>NOW()
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':kodeverifikasi', $kodeverifikasi);
        $stmt->execute();
        if ($stmt->rowCount()>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $idforgetpwd = $row['id'];
            $iduser = $row['iduser'];

            $sql = 'UPDATE `user` SET password=:password WHERE id=:iduser';
            $pwd = Hash::make($katasandibaru);
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':password', $pwd);
            $stmt->bindValue(':iduser', $iduser);
            $stmt->execute();

            //hapus token lama
            $sql = 'INSERT INTO authtokenblacklist_user SELECT iduser, idtoken, expired, NOW() FROM authtoken_user WHERE iduser=:iduser ON DUPLICATE KEY UPDATE authtokenblacklist_user.expired=GREATEST(authtokenblacklist_user.expired, authtoken_user.expired);';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':iduser', $iduser);
            $stmt->execute();

            Utils::deleteData($pdo,'authtoken_user',$iduser,'iduser');
            Utils::deleteData($pdo,'user_forgetpwd',$idforgetpwd);

            return redirect('login')->with('message', trans('all.katasandiberhasildiubah'));
        }
        else {
            return redirect('lupakatasandi_verifikasi')->with('message', trans('all.kodeverifikasisalahatauexpired'));
        }
    }

    public function loginpage(){
        if (Auth::check()) {
            return Redirect::to('/');
        }else{
            return view('login');
        }
    }

    public function loginpro()
    {
        // create our user data for the authentication
        $userdata = array(
            'email'     => Request::input('email'),
            'password'  => Request::input('password'),
            'status'    => 'a'
        );

        $pdo = DB::getPdo();
        $sql = 'SELECT id,status,wrongpwd FROM `user` WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', Request::input('email'));
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row['status']=='tk') {
                return redirect('login')->with('konfirmasi', $row['id']."|".trans('all.silahkancekemailandauntukkonfirmasi'));
            } else if ($row['status']=='b') {
                return redirect('login')->with('message', trans('all.userterblokirsilahkanhubungiadmin'));
            } else {
                if($row['wrongpwd'] < 5) {
                    if (Auth::attempt($userdata)) {
                        // validation successful!
                        $pdo = DB::getPdo();
                        $sql = 'SELECT nama,email FROM `user` WHERE id = :id';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':id', Auth::id());
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_OBJ);

                        if (Session::has('userbaru_perusahaan')) {
                            Session::forget('userbaru_perusahaan');
                        }

                        //cari tau apakah user merupakan superuser
                        $sql = 'SELECT iduser FROM superuser WHERE iduser = :id';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':id', Auth::id());
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            Session::set('superuser_perusahaan', '1');
                        } else {
                            //cari tau apakah user merupakan superuser_batasan
                            $sql = 'SELECT iduser FROM superuser_batasan WHERE iduser = :id';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':id', Auth::id());
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                Session::set('superuser_perusahaan', '0');
                            } else {
                                Session::set('superuser_perusahaan', '');
                            }
                        }

                        //langsung pilih perushaaan jika perusahaan cuma 1 dan status aktif
                        $sql = 'SELECT p.id as idperusahaan, p.nama as perusahaan, p.ispremium FROM perusahaan p, pengelola pn WHERE p.id=pn.idperusahaan AND pn.iduser = :iduser AND p.status = "a"';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':iduser', AUTH::id());
                        $stmt->execute();
                        if ($stmt->rowCount() == 1) {
                            $rowPengelola = $stmt->fetch(PDO::FETCH_ASSOC);
                            $idperusahaan = $rowPengelola['idperusahaan'];
                            Session::set('conf_webperusahaan', $idperusahaan);
                            Session::set('perusahaan_ispremium', $rowPengelola['ispremium']);
                            Session::set('perusahaan_perusahaan', $rowPengelola['perusahaan']);

                            // buat session folderroot perusahaan untuk semua foto
                            $sql = 'SELECT folderroot FROM perusahaan_route WHERE idperusahaan = :idperusahaan';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(':idperusahaan', $idperusahaan);
                            $stmt->execute();
                            $rowPR = $stmt->fetch(PDO::FETCH_ASSOC);
                            Session::set('folderroot_perusahaan', $rowPR['folderroot']);
                        }

                        Session::set('iduser_perusahaan', Auth::id());
                        Session::set('namauser_perusahaan', $row->nama);
                        Session::set('emailuser_perusahaan', $row->email);
                        Session::set('perusahaan_perusahaan', 'Smart Presence');

                        $sql = 'UPDATE `user` SET wrongpwd = 0 WHERE id = :id';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':id', Auth::id());
                        $stmt->execute();

                        Utils::insertLogUser('User ' . $row->nama . ' Telah Login');

                        return redirect('/')->with('welcome', trans('all.selamatdatang'));
                    } else {
                        $sql = 'UPDATE `user` SET wrongpwd = wrongpwd+1 WHERE email = :email';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':email', Request::input('email'));
                        $stmt->execute();

                        $msg = trans('all.emaildankatasandisalah');
                    }
                }else{
                    $msg = trans('all.userterblokirsilahkanhubungiadmin');
                }
            }
        } else {
            $msg = trans('all.emailtidakditemukan');
        }
        return redirect('login')->with('message', $msg);
    }

    public function logout()
    {
        Auth::logout();
        $bahasa = Session::get('conf_bahasaperusahaan');
        Session::flush(); //menghapus semua session
        Session::set('conf_bahasaperusahaan', $bahasa);
        return Redirect::to('login');
    }
}
