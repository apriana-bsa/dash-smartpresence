<?php

namespace App\Http\Controllers;

use App\Canopus;
use App\Invoice;
use App\Utils;
use DB;
use PDO;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotifikasiCanopusController extends Controller
{
    public function notificationHandler(Request $request) {
        $bodyRequest = $request->getContent();
        $bodyRequest = json_decode($bodyRequest);

        try {
            Log::info('Notification Callback: Callback Response=  ',[$bodyRequest]);
            $validSignature = Canopus::validateSignature($bodyRequest);
            if ($validSignature == 1) {
                $invoice = Invoice::where('order_id', $bodyRequest -> request -> data -> merchantOrderId)->first();
                if ($invoice -> status_bayar == 2 || $invoice -> status_bayar == 3) {
                    Log::info('Notification Callback OrderID: ' . $invoice -> order_id . 'Cannot Update Payment Status, Already Updated ' . $invoice -> status_bayar);
                    $responseNotif = [
                        'status' => 'SUCCESS',
                        'message' => 'Callback Received, Payment Already Updated'
                    ];

                    $arrLogCanopus = array(
                        "request" => $bodyRequest,
                        "response" => $responseNotif
                    );
                    Utils::create_log($invoice -> idperusahaan, 'notification', json_encode($arrLogCanopus));

                    return response() -> json($responseNotif);
                } else {
                    if (in_array($bodyRequest -> request -> data -> status, explode(',',env('CANOPUS_PENDING_STATUS')))) {
                        $invoice -> status_bayar = 1;
                        $invoice -> updated_by = 0;
                        $invoice -> save();
                        Log::info('Notification Callback Invoice OrderID: ' . $invoice -> order_id . ' Updated to Pending');

                        $responseNotif = [
                            'status' => 'SUCCESS',
                            'message' => 'Callback Received, Payment Updated to Pending'
                        ];

                        $arrLogCanopus = array(
                            "request" => $bodyRequest,
                            "response" => $responseNotif
                        );

                        Utils::create_log($invoice -> idperusahaan, 'notification', json_encode($arrLogCanopus));
                        return response() -> json($responseNotif);

                    } else if (in_array($bodyRequest -> request -> data -> status, explode(',',env('CANOPUS_FAILED_STATUS')))) {
                        $invoice -> status_bayar = 3;
                        $invoice -> updated_by = 0;
                        $invoice -> save();
                        Log::info('Notification Callback Invoice OrderID: ' . $invoice -> order_id . ' Updated to Expired');

                        $responseNotif = [
                            'status' => 'SUCCESS',
                            'message' => 'Callback Received, Payment Updated to Expired'
                        ];

                        $arrLogCanopus = array(
                            "request" => $bodyRequest,
                            "response" => $responseNotif
                        );

                        Utils::create_log($invoice -> idperusahaan, 'notification', json_encode($arrLogCanopus));

                        return response() -> json($responseNotif);
                    } else if (in_array($bodyRequest -> request -> data -> status, explode(',',env('CANOPUS_SUCCESS_STATUS')))) {
                        $invoice -> status_bayar = 2;
                        $invoice -> updated_by = 0;
                        $invoice -> amount_received = $bodyRequest -> request -> data -> amount;

                        // get aktif sampai perusahaan
                        $pdo = DB::getPdo();
                        $sql = 'SELECT aktifsampai FROM `perusahaan_kuota` WHERE idperusahaan = :idperusahaan';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':idperusahaan', $invoice -> idperusahaan);
                        $stmt->execute();
                        $perusahaanKuota = $stmt->fetch(PDO::FETCH_ASSOC);

                        // get new aktif sampai
                        $masaAktif = $perusahaanKuota['aktifsampai'];
                        $tanggalBayar = $invoice -> created_at;
                        $splitTanggal = explode(' ', $tanggalBayar);
                        $tanggalBayar = $splitTanggal[0];
                        $aktifSampai = self::getAktifSampai($tanggalBayar, $masaAktif, $invoice -> periode);

                        Log::info('CheckPoint masaAktif: ' . $masaAktif . ', aktifSampai: ' . $aktifSampai->format('Y-m-d') . ', idperusahaan: ' . $invoice -> idperusahaan);

                        // update aktifsampai dan invoice
                        $sql = "UPDATE perusahaan_kuota SET aktifsampai=?, limitpegawai =?, updated = NOW() WHERE idperusahaan=?";
                        $stmt= $pdo->prepare($sql);
                        $aktifSampai = $aktifSampai->format('Y-m-d');
                        $stmt->execute([$aktifSampai, $invoice->user_kuota, $invoice -> idperusahaan]);
                        $invoice -> save();


                        $bodyRequest = json_encode($bodyRequest);

                        $mail = Utils::sendEmailwithPDFInvoice($bodyRequest);
                        $mail = json_decode($mail);
                        $mail -> status = 'SUCCESS';
                        $mail -> message = 'Callback Received, Payment Updated to Success, ' . $mail -> message;
                        Log::info('Notification Callback Invoice OrderID: ' . $invoice -> order_id . ' Updated to Success');

                        $arrLogCanopus = array(
                            "request" => $bodyRequest,
                            "response" => $mail
                        );

                        Utils::create_log($invoice -> idperusahaan, 'notification', json_encode($arrLogCanopus));

                        return response() -> json($mail);
                    }
                }
            } else {
                $invoice = Invoice::where('order_id', $bodyRequest -> request -> data -> merchantOrderId)->first();
                $responseNotif = [
                    'status' => 'FAILED',
                    'message' => 'Invalid Signature'
                ];

                $arrLogCanopus = array(
                    "request" => $bodyRequest,
                    "response" => $responseNotif
                );

                Utils::create_log($invoice -> idperusahaan,'notification', json_encode($arrLogCanopus));

                Log::error('Invalid Canopus Signature');
                return response() -> json($responseNotif);
            }
        } catch (Exception $e) {

            $invoice = Invoice::where('order_id', $bodyRequest -> request -> data -> merchantOrderId)->first();

            $arrLogCanopus = array(
                "request" => $bodyRequest,
                "response" => $e->getMessage()
            );

            Utils::create_log($invoice -> idperusahaan,'notification', json_encode($arrLogCanopus));
            Log::error('System Error');
            return response() -> json([
                'status' => 'ERROR',
                'message' => 'ERROR'
            ]);

        }
    }

    public static function getAktifSampai ($tanggalBayar, $masaAktif, $periode) {
        if ($tanggalBayar > $masaAktif) {
            $cekBulan = new DateTime($tanggalBayar);
            $updateTanggal = $tanggalBayar;
        } else {
            $cekBulan = new DateTime($masaAktif);
            $updateTanggal = $masaAktif;
        }
        $akhirBulan = array ('29', '30');
        $tanggal = explode('-', $updateTanggal);
        $updateTanggalDate = new DateTime($updateTanggal);
        $cekBulan = $cekBulan ->modify('first day of +' . $periode . ' month' );
        $cekBulan = $cekBulan->format('m');
        Log::info(in_array($tanggal[2], $akhirBulan));
        if ($tanggal[2] == '31') {
            $aktifSampai = $updateTanggalDate ->modify('last day of +' . $periode . ' month' );
        } elseif ($cekBulan == '02' && in_array($tanggal[2], $akhirBulan)) {
            $aktifSampai = $updateTanggalDate ->modify('last day of +' . $periode . ' month' );
        } else {
            $aktifSampai = $updateTanggalDate->modify('+' . $periode . ' month');
        }
        return $aktifSampai;
    }

}
