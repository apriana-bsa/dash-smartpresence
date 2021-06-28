<?php

namespace Mock;

class CanopusMock {

    public static function canopusMock($command) {
        $response = array(
            "status" => true,
            "message" => 'Curl Success'
        );
        $bodyResponse = self::getMock($command);
        $bodyResponse = json_decode($bodyResponse);
        $response['bodyResponse'] = $bodyResponse;
        return $response;
    }

    public static function getMock($command) {
        $mock = array(
            "token" => '{
                "response": {
                  "result": {
                    "code": "00000000",
                    "message": "Success Generate Token",
                    "status": "SUCCESS"
                  },
                  "data": {
                    "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiIzNGczMXlhMGt4eXN1eDZlIiwiaWF0IjoxNTk2NTMxOTE1fQ.O4ZBW956n4pmcy6QOu6ScEvA5Jyo9uajzPSyIOEqq84MXe-QvXZbSG5kswMDZ7ZJnyxpZSJXCw0D7fceWbhA1aJ3rRvka7SH47kyeMLeouAM_SZ67I5QEPBr5Flsle0ujw3o5w-_RbT94mSWTnhFJD-NZ292wFbF_jp0-ZM3RFyn5ctH-3eubn_mWVxoV5L14Y2BsS6WcDubY8remLll9YI6ZHWLfYO6fR4O0HZjJclA5wxdc2Z_QP8SJgUJxe7kXVPf8jgGMbEG5S-zXhlklg41Bw9sN8aYeXo-V3GvX7CsqpxKW0SLFShrclHXObqqLttRpCwED8Rch4WwErj2Cw",
                    "expiredAt": "2020-08-11T09:05:15.906Z"
                  }
                },
                "signature": "dRvwC1h8K5s6GXFqRLxxVLWkKH0PhIXm/WErYTXcHP9S6ZklR4RBN9d7CaYwsWzTLqsB2u7Tmbp05femkoXESJjxgQr1UprbDu7cOzOK2fEtqqMcjJDEwLzpe0LZSwMpZbeLosCDMBeB8dXz3bRBErCujjqFBsCrsDul+U1CXYKgrSEdALd5Y9W2lmybqZ2I6vybjS8QI634dZBGuipjwvV3dZ/6tVarr7ihJyomtwCDisTs7fDWN1Rl+HORS2EZoxpa29YxUqHxsRehyhENFxIXijyMqHwJt5l+od0smWGkrIPjQEvfyghOHn5BQNMx2S3iK60H9aopV1q9T87k6Q=="
              }',
            "cart" => '{
                "response": {
                  "result": {
                    "code": "00000000",
                    "message": "Success to process generate cart",
                    "status": "SUCCESS"
                  },
                  "data": {
                    "cartID": "9e26144406f36e7a6adc74d2",
                    "checkoutURL": "https://canopus-auth.sumpahpalapa.com/transaction/payment/9e26144406f36e7a6adc74d2"
                  }
                },
                "signature": "dKRH8Gy8V63R8Q2XSQ7dVaQcjnTxJoiEwOY5OMXh3Qrs0mkR2Qjm45aItxXb/NJyEaDglCWY5pUucDdx2URHB0u8ZOTTt4qh8n2Rmn1PNxfSRSXpgrvq45Zg+toswweqXzX1r2n8KF+q05P0UDH6xLap3n4Yhu7fs3qhQbkG1jdWN1rgR1jsWdmHKXMksg2jlXkOeCOoDTmIc3mE417Hw+bg1AmghtD0UaaTDMjyyA9Pxtv9XTdxH5VqFqmiQx5ymYzZV6MTWabAUXcyCMZKHpwMuvnIsGP2HC4Rnx/CP3b1w/mY0nGvwcdJM1Oz5eNd23wBEWqM7MRxK3AtinAdsA=="
              }',
            "notifikasi" => '{
              "request": {
                "data": {
                  "accountName": "Test Name BanK",
                  "accountNumber": "123456789",
                  "amount": 33001,
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
                  "merchantOrderId": "SPA10950-25",
                  "mutationId": "bfa687c6d491e1df40051e2e",
                  "orderID": "PSABTDEV-1595994042078",
                  "paidAmount": 33001,
                  "paymentCode": 1,
                  "status": "settlement",
                  "time": {
                    "created": "2020-07-29T03:40:42.123Z",
                    "updated": "2020-07-29T03:41:12.464Z"
                  },
                  "transactionID": "7bda8d97a3843a8c849239d7",
                  "type": "bt"
                },
                "result": {
                  "code": "00000000",
                  "message": "Notification Payment Received",
                  "status": "SUCCESS"
                }
              },
              "signature": "gPhqrPmgXpbxFAP71Y8W9iYocVAJ83fa5jwoEegfmaQrHiS9bwZXEmsW2HQUuKdICmaCUiM1SfRXFX5pUS55H5EaOSUus6XivRZMN3OmcbF6DqEbWtaRt1n67aVJ2Hi7N14zIJmhYNmhEPogPpqNG1qjPwfLgDxgpkt7RHug9EmyoPgy5onAy3levNbnHljdV2kYYYZjir+KMrswVWJ6vN1VeXrLWZdZepQ/Yrm4X5Ttuwpv7aBsjVqCcTGTOgETTLykdZKpDpxA/YS6mfHuix9HvS9hLQ1S2zGOT2j+40axDdh3SsNykZTtOkU7gHH44UdQmDEFnHsk2GCemBQUig=="
            }',
            "notifikasiPending" => '{
              "request": {
                "data": {
                  "accountName": "Test Name BanK",
                  "accountNumber": "123456789",
                  "amount": 2310001,
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
                  "merchantOrderId": "SPA10950-27",
                  "orderID": "PSABTDEV-1597140220195",
                  "paidAmount": 0,
                  "paymentCode": 1,
                  "status": "pending",
                  "time": {
                    "created": "2020-08-11T10:03:40.241Z",
                    "updated": "2020-08-11T10:03:40.241Z"
                  },
                  "transactionID": "09150f7b4afdd611be087887",
                  "type": "bt"
                },
                "result": {
                  "code": "00000000",
                  "message": "Notification Payment Pending",
                  "status": "SUCCESS"
                }
              },
              "signature": "a7oRcHGoJUOsY+4YTP/9UQ+AeVyehkKPPXtGKMb5CFj9/rdTifPrjhYESGZoeeTpLdgh7lNazhZ9wdEaCUt5/9OivbswfNW3ZNls9KZGQ4Gjz4Z/f3KGc+Ug9VhH/VbL7pUJUQ6J2sPjCbSTBiyP0aPkSCy+BEo6rIm/r0wqCq4dVappsnmMKbhIgwR3BP/yBKvU0Gqd5XT5PJ5qwoeFFluOW2w/NUqbSZKo2kZeEblbA6at2CMYhTTqxC8I/HS1WZSSAAc1X95SjzsfTnBNlKN3WfhPG/G6VOIBwA++pwpfVimMp9Z4fGLhsuokOJHE6i3MmNRNMSD1aAh9dOuClg=="
            }',
            "notifikasiExpired" => '{
              "request": {
                "data": {
                  "amount": 500000,
                  "bank": "BCA",
                  "instruction": [
                    {
                      "name": "lagi di hardcode buat automate ke instruksi 3",
                      "step": "<p>tes</p>"
                    },
                    {
                      "name": "lagi di hardcode buat automate ke instruksi 3",
                      "step": "<p>bole di edit tp jangan dihapus</p>"
                    },
                    {
                      "name": "Automate test.UI Judul Instruction Version Auth",
                      "step": "<p>Automate test.UI isi Instruction Version Auth</p><ol><li>a</li><li>b12011437</li></ol>"
                    }
                  ],
                  "merchantID": "M-0001",
                  "merchantOrderId": "SP-20200211144917",
                  "number": "07761185140",
                  "orderID": "PSADO-1596524251158",
                  "status": "expire",
                  "time": {
                    "created": "2020-08-04T06:57:33.689Z",
                    "updated": "2020-08-05T06:57:39.006Z"
                  },
                  "transactionID": "e538b09f-427f-4da4-82bb-7ffe4d3478a1",
                  "type": "va"
                },
                "result": {
                  "code": "00000000",
                  "message": "Notification Payment Failed",
                  "status": "SUCCESS"
                }
              },
              "signature": "lz94zGuk3zi3H6xXoVKLbpwyKVGho8G4wi9lwMzG5KZUPxGvAGBavHRN+wWCKBwk3DkEzbrxheFJ+NtUHiagl9SFPvLtooOrP8LYOw/BjFEugvdIAzI0wO+9f8nzArdle6yPPV0X3iLWoafKFN5o+r9jjEl/3KanepYDbs+zBXi/WXnB7ZZH/ea7OkGEj6EveuAMjDGJi7gTbqiM6NVOHaXmUndZ+n3uMF6zvL7eH5pR4gCbi75JMN7gurZ457WTeYHZ2h8v9V0UWXygs81mNErjz16FEoDtLBoDSIKeFJzDSmpSITTAMNAFzs2yJffOwNuTDW4Lb1Vw4UUWfasPew=="
            }'
        );

        return $mock[$command];
    }
}