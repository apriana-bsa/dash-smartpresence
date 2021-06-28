<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Pendaftaran Sukses</title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
            box-sizing: border-box;
            font-size: 14px;
        }

        img {
            max-width: 100%;
        }

        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
            line-height: 1.6;
        }

        /* Let's make sure all tables have defaults */
        table td {
            vertical-align: top;
        }

        body {
            background-color: #f6f6f6;
        }

        .body-wrap {
            background-color: #f6f6f6;
            width: 100%;
        }

        .container {
            display: block !important;
            max-width: 600px !important;
            margin: 0 auto !important;
            /* makes it centered */
            clear: both !important;
        }

        .content {
            max-width: 640px;
            margin: 0 auto;
            display: block;
            padding: 20px;
        }

        .main {
            background: #fff;
            border: 1px solid #e9e9e9;
            border-radius: 3px;
        }

        .content-wrap {
            padding: 20px;
        }

        .content-block {
            padding: 0 0 20px;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
        }

        .footer {
            width: 100%;
            clear: both;
            color: #999;
            padding: 10px;
        }
        .footer a {
            color: #999;
        }
        .footer p, .footer a, .footer unsubscribe, .footer td {
            font-size: 12px;
        }

        h1, h2, h3 {
            font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
            color: #000;
            margin: 40px 0 0;
            line-height: 1.2;
            font-weight: 400;
        }

        h1 {
            font-size: 32px;
            font-weight: 500;
        }

        h2 {
            font-size: 24px;
        }

        h3 {
            font-size: 18px;
        }

        h4 {
            font-size: 14px;
            font-weight: 600;
        }

        p, ul, ol {
            margin-bottom: 10px;
            font-weight: normal;
        }
        p li, ul li, ol li {
            margin-left: 5px;
            list-style-position: inside;
        }

        a {
            color: #1ab394;
            text-decoration: underline;
        }

        .btn-primary {
            text-decoration: none;
            color: #FFF;
            background-color: #112e7e;
            border: solid #112e7e;
            border-width: 5px 10px;
            line-height: 2;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            display: inline-block;
            border-radius: 5px;
            text-transform: capitalize;
        }

        .last {
            margin-bottom: 0;
        }

        .first {
            margin-top: 0;
        }

        .aligncenter {
            text-align: center;
        }

        .alignright {
            text-align: right;
        }

        .alignleft {
            text-align: left;
        }

        .clear {
            clear: both;
        }

        .alert {
            font-size: 16px;
            color: #fff;
            font-weight: 500;
            padding: 20px;
            text-align: center;
            border-radius: 3px 3px 0 0;
        }
        .alert a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
        }
        .alert.alert-warning {
            background: #f8ac59;
        }
        .alert.alert-bad {
            background: #ed5565;
        }
        .alert.alert-good {
            background: #112e7e;
        }


        @media only screen and (max-width: 640px) {
            h1, h2, h3, h4 {
                font-weight: 600 !important;
                margin: 20px 0 5px !important;
            }

            h1 {
                font-size: 22px !important;
            }

            h2 {
                font-size: 18px !important;
            }

            h3 {
                font-size: 16px !important;
            }

            .container {
                width: 100% !important;
            }

            .content, .content-wrap {
                padding: 10px !important;
            }
        }
    </style>
</head>

<body>

<table class="body-wrap">
    <tr>
        <td></td>
        <td class="container" width="600">
            <div class="content">
                <table class="main" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="alert alert-good">
                            {!! $subject !!}
                        </td>
                    </tr>
                    <tr>
                        <td class="content-wrap">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-block">
                                        Dear {!! $firstName !!},<br><br>
                                        Terima kasih telah melakukan pembayaran atas pemesanan berlangganan Anda di SmartPresence.
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        Berikut detail pembayaran Anda.<br><br>
                                        <u>Detail Pelanggan</u>
                                        <table style="width:100%">
                                            <tr>
                                                <td style="width: 30%">Kode Perusahaan</td>
                                                <td>: {!! $kodePerusahaan !!}</td>
                                            </tr>
                                            <tr>
                                                <td>Nama Perusahaan</td>
                                                <td>: {!! $namaPerusahaan !!}</td>
                                            </tr>
                                        </table>
                                    </td>
                                  </tr>
                                  <tr>
                                      <td class="content-block">
                                          <u>Detail Pembayaran</u>
                                          <table style="width:100%">
                                              <tr>
                                                  <td style="width: 30%">No Pembayaran</td>
                                                  <td>: {!! $invoiceNo !!}</td>
                                              </tr>
                                              <tr>
                                                  <td>Jumlah Pembayaran</td>
                                                  <td>: {!! $jumlahBayar !!}</td>
                                              </tr>
                                              <tr>
                                                  <td>Tanggal Pembayaran</td>
                                                  <td>: {!! $invoiceTanggal !!}</td>
                                              </tr>
                                          </table>
                                      </td>
                                    </tr>
                                    <tr>
                                        <td class="content-block">
                                            <u>Detail Berlangganan</u>
                                            <table style="width:100%">
                                                <tr>
                                                    <td style="width: 30%">Jumlah User</td>
                                                    <td>: {!! $jumlahUser !!}</td>
                                                </tr>
                                                <tr>
                                                    <td>Harga per User</td>
                                                    <td>: {!! $pricePerUser !!}</td>
                                                </tr>
                                                <tr>
                                                    <td>Masa Berlangganan</td>
                                                    <td>: {!! $startDate !!} hingga {!! $endDate !!}</td>
                                                </tr>
                                                <tr>
                                                    <td>Periode Pembayaran</td>
                                                    <td>: {!! $periodeBayar !!}</td>
                                                </tr>
                                            </table>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="content-block">
                                            Terima kasih,<br>
                                            Tim SmartPresence
                                        </td>
                                      </tr>
                            </table>
                        </td>
                    </tr>

                </table>
                <div class="footer">
                    <table width="100%">
                        <tr>
                            <td class="aligncenter">Butuh bantuan? Hubungi kami di info@smartpresence.id atau Divisi SmartPresence (0361) 419 145.</td>
                        </tr>
                    </table>
                </div></div>
        </td>
        <td></td>
    </tr>
</table>

</body>
</html>
