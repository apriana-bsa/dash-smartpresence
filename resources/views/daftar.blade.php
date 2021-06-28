<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Smart Presence | {{ trans('all.daftar') }}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Facebook Pixel Code -->
    <script>
      !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
          n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
          n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
          t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
          document,'script','https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '116219162373473');
      fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id=116219162373473&ev=PageView&noscript=1"
    /></noscript>
    <!-- DO NOT MODIFY -->
    <!-- End Facebook Pixel Code -->

    <link href="{{ asset('lib/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <!-- Toastr style -->
    <link href="{{ asset('lib/css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">

    <link href="{{ asset('lib/css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/css/style.css') }}" rel="stylesheet">

    <script src="{{ asset('lib/js/jQuery-2.1.4.min.js') }}"></script>
      <script src="{{ asset('lib/js/i18next.js') }}"></script>
      <script src="{{ asset('lib/js/pwstrength.js') }}"></script>
      <script src="{{ asset('lib/js/bootstrap.min.js') }}"></script>
    
      <link rel="stylesheet" href="{{ asset('lib/css/sweetalert2.css') }}" type="text/css" />
      <script src="{{ asset('lib/js/sweetalert2.js') }}"></script>
      <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/button_loading.css') }}">
      <script type='text/javascript' src="{{ asset('/lib/js/util.js') }}"></script>
      <!-- Toastr script -->
      <script src="{{ asset('lib/js/plugins/toastr/toastr.min.js') }}"></script>
      <script src='https://www.google.com/recaptcha/api.js'></script>
    <script>
    $(document).ready( function() {
      
        $('.formdaftar').keypress(function (e) {
            var key = e.which;
            if(key == 13)  // the enter key code
            {
              $('#daftar').click();
              return false;
            }
        });
    
        "use strict";
        i18next.init({
            lng: 'id',
            resources: {
                id: {
                    translation: {
                        "veryWeak": "",
                        "weak": "{{ trans('all.lemah') }}",
                        "normal": "{{ trans('all.normal') }}",
                        "medium": "{{ trans('all.sedang') }}",
                        "strong": "{{ trans('all.kuat') }}",
                        "veryStrong": "{{ trans('all.sangatkuat') }}"
                    }
                }
            }
        }, function () {
            // Initialized and ready to go
            var options = {};
            options.ui = {
                container: "#formdaftar",
                showVerdictsInsideProgressBar: true,
                viewports: {
                    progress: ".pwstrength_viewport_progress"
                },
                progressBarExtraCssClasses: "progress-bar-striped active"
            };
            options.common = {
                debug: false
            };
            $('#password').pwstrength(options);
        });

        $(".fa-eye").hide();

        $(".password").on("keyup",function(){
            if($(this).val())
                $(".fa-eye").show();
            else
                $(".fa-eye").hide();
        });

        $(".fa-eye").mousedown(function(){
            $(".password").removeAttr('id');
        }).mouseup(function(){
            $(".password").attr('id','password');
        }).mouseout(function(){
            $(".password").attr('id','password');
        });
    });

    function validasi(){
      $('#submit').attr( 'data-loading', '' );
      $('#submit').attr('disabled', 'disabled');

      var nama = $('#nama').val();
      var email = $('#email').val();
      var nomorhp = $('#nomorhp').val();
      var pass = $('#password').val();
      var letters = /^[A-Za-z]+$/;

      if(nama == ""){
        alertWarning("{{ trans('all.namakosong') }}",
              function() {
                aktifkanTombol();
                setFocus($('#nama'));
              });
        return false;
      }

    //   if(!nama.match(letters)){
    //       alertWarning("{{ trans('all.isikanhurufsaja') }}",
    //           function() {
    //               aktifkanTombol();
    //               setFocus($('#nama'));
    //           });
    //       return false;
    //   }
      
      if(email === ""){
        alertWarning("{{ trans('all.emailkosong') }}",
              function() {
                aktifkanTombol();
                setFocus($('#email'));
              });
        return false;
      }
      
      if(nomorhp === ""){
        alertWarning("{{ trans('all.nomorhpkosong') }}",
              function() {
                aktifkanTombol();
                setFocus($('#nomorhp'));
              });
        return false;
      }

      if(isNaN(nomorhp)){
        alertWarning("{{ trans('all.nomorhptidakvalid') }}",
              function() {
                aktifkanTombol();
                setFocus($('#nomorhp'));
              });
        return false;
      }

      if(pass === ""){
        alertWarning("{{ trans('all.katasandikosong') }}",
              function() {
                aktifkanTombol();
                setFocus($('#password'));
              });
        return false;
      }
    }

    function gantibahasa(){
      var bahasa = $("#bahasa").val();
      if(bahasa !== ""){
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
                        positionClass: 'toast-bottom-right'
                    };
                    toastr.warning('{{ Session::get("message") }}', '{{ trans("all.peringatan") }}');
                }, 500);
      });
    @endif

    @if(Session::get('alert'))
      $(document).ready(function() {
        alertWarning("{{ Session::get("alert") }}",
            function() {
                setFocus($('#nama'));
            });
        return false;
      });
    @endif

    function validateEmail(emailField){
        if(emailField.value !== '') {
            var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

            if (reg.test(emailField.value) == false) {
                alertWarning("{{ trans('all.emailtidakvalid') }}",
                    function () {
                        aktifkanTombol();
                        setFocus($('#email'));
                    });
                return false;
            }
        }

        return true;

    }
    </script>
  <style>
  #password {
      text-security:disc;
      -webkit-text-security:disc;
      -mox-text-security:disc;
  }
  </style>
  </head>
  <body class="gray-bg">

    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <div>
                <!-- <h1 class="logo-name">IN+</h1> -->
            </div>
            <p>{{ trans('all.daftar') }}</p>
            <form class="m-t" id="formdaftar" role="form" method="POST" action="{{ url('/daftar') }}" onsubmit="return validasi()">
              {{ csrf_field() }}
              <input type="hidden" name="origin" value="{{$origin}}">
              <div class="form-group">
                <input type="text" name="nama" id="nama" value="{{ old('nama') }}" autocomplete="off" autofocus class="form-control" placeholder="{{ trans('all.nama') }}">
              </div>
              <div class="form-group">
                <input type="text" name="email" id="email" value="{{ old('email') }}" onblur="validateEmail(this);" autocomplete="off" class="form-control" placeholder="{{ trans('all.email') }}">
              </div>
              <div class="form-group">
                <input type="text" name="nomorhp" id="nomorhp" onkeypress="return onlyNumber(0,event)" value="{{ old('nomorhp') }}" autocomplete="off" class="form-control" placeholder="{{ trans('all.nomorhp') }}">
              </div>
              <div class="form-group">
                <input type="text" name="katasandi" autocomplete="off" value="{{ old('katasandi') }}" id="password" class="form-control password" placeholder="{{ trans('all.katasandi') }}">
                  <i class="fa fa-eye" style="cursor: pointer;position: absolute;right:10px;margin-top:-24px"></i>
              </div>
              <div class="form-group">
                  <div class="pwstrength_viewport_progress"></div>
              </div>
              <div class="form-group">
                  <div class="g-recaptcha" data-sitekey="{{ env('RE_CAP_SITE') }}"></div>
              </div>
              <button id="submit" type="submit" class="ladda-button btn btn-primary block full-width m-b slide-left"><span class="label2">{{ trans('all.daftar') }}</span> <span class="spinner"></span></button>
              <!-- <p class="text-muted text-center"><small>Do not have an account?</small></p> -->
              <p></p>
              <a class="btn btn-sm btn-white btn-block" href="login">{{ trans('all.login') }}</a>
            </form>
            <p></p>
            <div class="col-xs-12">
              <select class='pull-right' id='bahasa' name='bahasa' onchange='gantibahasa()'>
                <option value='id' @if (Lang::locale() == 'id') selected @endif>Indonesia</option>
                <option value='en' @if (Lang::locale() == 'en') selected @endif>English</option>
              </select>
              <span class='pull-right' style='margin:2px 0'>{{ trans('all.bahasa') }} :&nbsp;&nbsp;</span>
            </div><!-- /.col -->
            <br>
            <p class="m-t"> <small>{{config('consts.PERUSAHAAN_COPYRIGHT')}} &copy; {{ date('Y') }}</small> </p>
        </div>
    </div>
  </body>
</html>