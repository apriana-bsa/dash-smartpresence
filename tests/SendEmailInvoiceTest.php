<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Utils;

class SendEmailInvoiceTest extends TestCase
{

    /**
    * Before running this test, make sure that the invoice.order_id contains the equal to merchantOrderId
    * `merchantOrderId`  used to check at database field `invoice.order_id`
    */
    public function testEmailSend()
    {
        $mockCallbackFromCanopus =
        '{
          "request": {
            "data": {
              "accountName": "Test Name BanK",
              "accountNumber": "123456789",
              "amount": 231001,
              "bank": "BCA",
              "instruction": [
                {
                  "name": "via <font color=\"#ef5350\"><b>ATM BCA</b></font>",
                  "step": "<ol><li>Masukkan <strong>Kartu ATM</strong> dan <strong>PIN ATM</strong> <strong>BCA</strong> Anda.</li><li>Pilih <strong>Transaksi Lainnya</strong>.</li><li>Pilih <strong>Transfer</strong> lalu pilih <strong>Ke Rek BCA.</strong></li><li>Masukkan <strong>Nomor Rekening yang tertera di aplikasi.</strong></li><li>Masukkan nominal<strong> Jumlah Bayar sampai dengan 3 digit terakhir.</strong></li><li>Pastikan detil pembayaran Anda benar. Jika benar, pilih <strong>Ya</strong>.</li><li>Pembayaran Anda dengan <strong>BCA Transfer</strong> telah selesai. Mohon simpan struk untuk bukti pembayaran Anda.</li><li>Saldo Anda akan masuk dalam beberapa menit tanpa perlu melakukan konfirmasi.</li></ol>"
                },
                {
                  "name": "via <font color=\"#ef5350\"><b>m-Banking BCA</b></font>",
                  "step": "<ol><li>Pilih <strong>m-BCA</strong> di aplikasi m-Banking Anda, lalu pilih <strong>m-Transfer.</strong></li><li>Jika Anda baru pertama kali melakukan pembayaran AlphaPay melalui <strong>m-Banking</strong>, Anda harus menambahkan Rekening ke Daftar Transfer Anda dengan cara pilih<strong> Daftar Transfer - Antar Rekening.&nbsp;</strong></li><li>Kemudian masukkan<strong> Nomor Rekening yang tertera di aplikasi AlphaPay </strong>lalu pilih<strong> Send.</strong></li><li>Namun, jika sudah pernah melakukan pembayaran AlphaPay melalui<strong> </strong>m-Banking,<strong> </strong>silakan<strong> </strong>pilih<strong> Transfer - Antar Rekening.</strong></li><li>Pilih <strong>Nomor Rekening yang tertera di aplikasi.</strong></li><li>Masukkan nominal <strong>Jumlah Bayar sampai dengan 3 digit terakhir</strong> lalu pilih <strong>Send.</strong></li><li>Pastikan detil pembayaran Anda benar. Jika benar, pilih <strong>OK.</strong></li><li>Masukkan <strong>PIN m-BCA</strong> Anda lalu pilih <strong>OK.</strong></li><li>Pembayaran Anda dengan <strong>BCA Transfer</strong> telah selesai. Mohon simpan bukti pembayaran Anda.</li><li>Saldo Anda akan masuk dalam beberapa menit tanpa perlu melakukan konfirmasi.<br>&nbsp;</li></ol>"
                }
              ],
              "merchantID": "M-0001",
              "merchantOrderId": "SPA10950-00",
              "mutationId": "6354586c36f814c1e1fe1650",
              "orderID": "PSABTDEV-1596021747938",
              "paidAmount": 231001,
              "paymentCode": 1,
              "status": "settlement",
              "time": {
                "created": "2020-07-29T11:22:27.982Z",
                "updated": "2020-07-29T11:22:56.806Z"
              },
              "transactionID": "ed81335048d16f3049538846",
              "type": "bt"
            },
            "result": {
              "code": "00000000",
              "message": "Notification Payment Received",
              "status": "SUCCESS"
            }
          },
          "signature": "cAFNCvQoX6q7NWSDsc+7RKCSVyiaGwXkgf0iVkP/qW/fslhmDPfChkJEgE/wr8OqMnU7tUZnTWEP4Z9MR4w15aO4BgVeZ0RzwVTnubcemBG0NuJ/SODNZh5VQzT9BFo2NIYdphV5AFX+DOyhciDiyGx3tj+mS2PbyRWZZxuAhrzbtSFQ0mY5yhXK0SMcjck8mY+xWH4PcwJNfE7/RkJvcdfO96MHkOrNP9neBtN/jX8rB3PdouylVaJOV8xnplghGqGBkSMaMhaeNVMbmKdWPguHUs9fAO/n+bk6wvZX5QcfYgr7z7c9oMZ5WaTiPFg+2G95ORYArN+iUmJ40MDVbQ=="
        }';

        $response = Utils::sendEmailwithPDFInvoice($mockCallbackFromCanopus);
        $response = json_decode($response);
        $this->assertEquals(true, $response->email_status);
    }
}
