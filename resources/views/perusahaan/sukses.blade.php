<!DOCTYPE html>
<html>
<!-- Mirrored from webapplayers.com/inspinia_admin-v2.5/404.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 09 Jun 2016 07:24:41 GMT -->
<head>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>{{config('consts.PERUSAHAAN_COPYRIGHT')}} - {{ trans('all.perusahaanberhasildibuat') }}</title>
    
    <link href="{{ asset('lib/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    
    <link href="{{ asset('lib/css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/css/style.css') }}" rel="stylesheet">

</head>

<body class="gray-bg">
<div id="keterangan" class="middle-box text-center animated fadeInDown">
    @if($message == 'ok')
        <h2>{{ trans('all.berhasil') }}</h2>
        <h3 class="font-bold">{{ trans('all.perusahaanberhasildibuat') }}</h3>
        <div class="error-desc">
            {{ trans('all.keteranganperusahaanberhasildibuat') }}
        </div>
    @else
        <h3 class="font-bold">{{ $message }}</h3>
        <div class="error-desc">
            {{ trans('all.keteranganperusahaanberhasildibuat') }}
        </div>
    @endif
    <a href="http://dash.smartpresence.id">{{ trans('all.kehalamanlogin') }}</a>
</div>

<link rel="stylesheet" href="{{ asset('lib/css/sweetalert2.css') }}" type="text/css" />
<!-- Mainly scripts -->
<script src="{{ asset('lib/js/jQuery-2.1.4.min.js') }}"></script>
<script src="{{ asset('lib/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('lib/js/sweetalert2.js') }}"></script>
<script src="{{ asset('lib/js/util.js') }}"></script>
</body>
</html>
