<!DOCTYPE html>
<html>


<!-- Mirrored from webapplayers.com/inspinia_admin-v2.5/login_two_columns.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 09 Jun 2016 07:24:41 GMT -->
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Smart Presence | {{ trans('all.login') }}</title>

    <link href="{{ asset('lib/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/font-awesome/css/font-awesome.css') }}" rel="stylesheet">

    <!-- Toastr style -->
    <link href="{{ asset('lib/css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">

    <link href="{{ asset('lib/css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/css/style.css') }}" rel="stylesheet">
    <script src="{{ asset('lib/js/jQuery-2.1.4.min.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.min.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('lib/css/sweetalert2.css') }}" type="text/css" />
    <script src="{{ asset('lib/js/sweetalert2.js') }}"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/button_loading.css') }}">
    <script type='text/javascript' src="{{ asset('/lib/js/util.js') }}"></script>
    <!-- Toastr script -->
    <script src="{{ asset('lib/js/plugins/toastr/toastr.min.js') }}"></script>
    <script>
        $(document).ready( function() {
            console.log('{{ trans('all.versi').' : '.Session::get('versiweb_perusahaan') }}');
            /*$('#bg').blurjs({
             source: 'body',
             radius: 10,
             //overlay: 'rgba(0, 0, 0, .2)'
             });     */

            $('.formlogin').keypress(function (e) {
                var key = e.which;
                if(key == 13)  // the enter key code
                {
                    $('#login').click();
                    return false;
                }
            });

            $('#login').click(function(){

                $('#login').attr( 'data-loading', '' );
                $('#login').attr('disabled', 'disabled');

                var email = $('#email').val();
                var pass = $('#password').val();
                var tahunajaran = $('#tahunajaran').val();
                var bahasa = $('#bahasa').val();

                if(email == ""){
                    alertWarning("{{ trans('all.emailkosong') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#email'));
                        });
                    return false;
                }

                if(pass == ""){
                    alertWarning("{{ trans('all.katasandikosong') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#password'));
                        });
                    return false;
                }

                $("#formlogin").submit();

            });

            $(".fa-eye").hide();

            $("#password").on("keyup",function(){
                if($(this).val())
                    $(".fa-eye").show();
                else
                    $(".fa-eye").hide();
            });

            $(".fa-eye").mousedown(function(){
                $("#password").attr('type','text');
            }).mouseup(function(){
                $("#password").attr('type','password');
            }).mouseout(function(){
                $("#password").attr('type','password');
            });

        });

        function gantibahasa(){
            var bahasa = $("#bahasa").val();
            if(bahasa != ""){
                window.location.href='bahasa/'+bahasa;
            }
        }
        @if(Session::get('message'))
        $(document).ready(function() {
            setTimeout(function() {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 5000,
                    extendedTimeOut: 5000,
                    positionClass: 'toast-top-center'
                };
                toastr.warning('{{ Session::get("message") }}', '{{ trans("all.peringatan") }}');
            }, 500);
        });
        @endif
        @if(Session::get('konfirmasi'))
        $(document).ready(function() {
            var message = "{{ Session::get("konfirmasi") }}";
            var data = message.split('|');
            alertConfirm(data[1],
                function(){
                    window.location.href="{{ url('daftar/kirimulang') }}/"+data[0];
                },
                function(){

                },
                "{{ trans('all.kirimulangemail') }}","{{ trans('all.tutup') }}"
            );
            return false;
        });
        @endif
    </script>

</head>

<body class="gray-bg">

<div class="loginColumns animated fadeInDown">
    <div class="row">

        <div class="col-md-6">
            <p style="margin-bottom: 10px"><img src="{{ url('logo_sp_small.png') }}"></p>
            <p>
                {!!  trans('all.keteranganlogin') !!}
            </p>
        </div>
        <div class="col-md-6">
            <div class="ibox-content">
                <h3>{{ trans('all.login') }}</h3>
                <form class="m-t" id="formlogin" role="form" method="POST" action="{{ url('/login') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <input type="text" name="email" id="email" autocomplete="off" autofocus class="form-control" placeholder="{{ trans('all.email') }}" required="">
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="{{ trans('all.katasandi') }}" required="">
                        <i class="fa fa-eye" style="cursor: pointer;position: absolute;right:45px;margin-top:-24px"></i>
                    </div>
                    <button id="login" type="submit" class="ladda-button btn btn-primary block full-width m-b slide-left"><span class="label2">{{ trans('all.login') }}</span> <span class="spinner"></span></button>
                    <a href="lupakatasandi">{{ trans('all.lupakatasandi') }}</a>
                    <p class="text-muted text-center" style="margin-top:24px">{{ trans('all.tidakpunyaakun') }}</p>
                    <p></p>
                    <a class="btn btn-sm btn-white btn-block" href="daftar">{{ trans('all.daftar') }}</a>
                </form>
                <p></p>
                <div class="row">
                    <div class="col-xs-12">
                        <select class='pull-right' id='bahasa' name='bahasa' onchange='gantibahasa()'>
                            <option value='id' @if (Lang::locale() == 'id') selected @endif>Indonesia</option>
                            <option value='en' @if (Lang::locale() == 'en') selected @endif>English</option>
                            <option value='cn' @if (Lang::locale() == 'cn') selected @endif>中国</option>
                        </select>
                        <span class='pull-right' style='margin:2px 0'>{{ trans('all.bahasa') }} :&nbsp;&nbsp;</span>
                    </div><!-- /.col -->
                </div>
            </div>
        </div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-md-6">
            <small>{{config('consts.PERUSAHAAN_COPYRIGHT')}} © {{ date('Y') }}</small>
        </div>
        <div class="col-md-6 text-right">
            <a target="_new" href="http://smartpresence.id/syarat-ketentuan-layanan/">{{ trans('all.syaratdanketentuan') }}</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <small>{{ config('consts.NO_TELP') }}</small>
        </div>
        <div class="col-md-6 text-right">
            <a target="_new" href="http://smartpresence.id/kebijakan-privasi/">{{ trans('all.kebijakanprivasi') }}</a>
        </div>
    </div>
</div>

</body>


<!-- Mirrored from webapplayers.com/inspinia_admin-v2.5/login_two_columns.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 09 Jun 2016 07:24:41 GMT -->
</html>
