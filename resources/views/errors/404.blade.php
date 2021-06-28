<!DOCTYPE html>
<html>


<!-- Mirrored from webapplayers.com/inspinia_admin-v2.5/404.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 09 Jun 2016 07:24:41 GMT -->
<head>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>{{ Session::get('perusahaan_perusahaan') }} - {{ trans('all.halamantidakditemukan') }}</title>
    
    <link href="{{ asset('lib/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    
    <link href="{{ asset('lib/css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/css/style.css') }}" rel="stylesheet">

</head>

<body class="gray-bg">


<div class="middle-box text-center animated fadeInDown">
    <h1>404</h1>
    <h3 class="font-bold">{{ trans('all.halamantidakditemukan') }}</h3>
    
    <div class="error-desc">
        {{ trans('all.halamantidakditemukan_desc') }}
    </div>
</div>

<!-- Mainly scripts -->
<script src="{{ asset('lib/js/jQuery-2.1.4.min.js') }}"></script>
<script src="{{ asset('lib/js/bootstrap.min.js') }}"></script>

</body>
</html>
