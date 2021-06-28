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
            max-width: 600px;
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
            padding: 20px;
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
                            Selamat Datang Di SmartPresence
                        </td>
                    </tr>
                    <tr>
                        <td class="content-wrap">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-block">
                                        Hai teman Smart disana,<br>
                                        Terimakasih telah bergabung dengan SmartPresence!
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        Ratusan orang telah bergabung dengan SmartPresence dan telah memperoleh manfaat dalam perekaman & supervisi data kehadiran. Begitu anda melakukan konfirmasi email anda, kami akan menunjukan caranya untuk sukses dalam perekaman dan pelacakan data kehadiran.
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        Kami membuat link ini khusus untuk anda agar anda dapat ikut merasakan manfaatnya.
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        Data Akun Profil Yang daftarkan<br>
                                        <table>
                                            <tr>
                                                <td>Nama</td>
                                                <td>: {!! $nama !!}</td>
                                            </tr>
                                            <tr>
                                                <td>Email</td>
                                                <td>: {!! $email !!}</td>
                                            </tr>
                                            <tr>
                                                <td>No Telp</td>
                                                <td>: {!! $nomorhp !!}</td>
                                            </tr>
                                            <tr>
                                                <td>Password</td>
                                                <td>: ******</td>
                                            </tr>
                                        </table>
                                        <br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        Untuk melakukan konfirmasi akun klik tombol dibawah ini :<br><br>
                                        <a class="btn-primary" href="https://dash.smartpresence.id/daftar/konfirmasi/{!! $iduserkonfirmasi !!}/{!!  $kode !!}">Aktifkan</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        selanjutnya silahkan login dengan akun ini di aplikasi dashboard web / mobile dan buat perusahaan jika blm memiliki nya, jika sudah langsung pilih perusahaan anda.
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        Salam dari kami,<br>
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
                            <td class="aligncenter content-block">Chat Support Kami : <a href="https://smartpresence.id">http://smartpresence.id</a> Phone (+62 361) 413 497</td>
                        </tr>
                    </table>
                </div></div>
        </td>
        <td></td>
    </tr>
</table>

</body>
</html>