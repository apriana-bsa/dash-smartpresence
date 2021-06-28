<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Smart Present | {{ trans('all.lupakatasandi') }}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
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
      /*$('#bg').blurjs({
				source: 'body',
				radius: 10,
				//overlay: 'rgba(0, 0, 0, .2)'
			});     */
      
      $('.formlupakatasandi_verifikasi').keypress(function (e) {
        var key = e.which;
        if(key == 13)  // the enter key code
        {
          $('#submit').click();
          return false;  
        }
      });
    
    });

    function validasi(){
      $('#submit').attr( 'data-loading', '' );
      $('#submit').attr('disabled', 'disabled');
      
      var kodeverifikasi = $('#kodeverifikasi').val();
      var katasandibaru = $('#katasandibaru').val();
      var ulangikatasandibaru = $('#ulangikatasandibaru').val();
      
      if(kodeverifikasi == ""){
        alertWarning("{{ trans('all.kodeverifikasikosong') }}",
              function() {
                aktifkanTombol();
                setFocus($('#kodeverifikasi'));
              });
        return false;
      }

      if(katasandibaru == ""){
        alertWarning("{{ trans('all.katasandibarukosong') }}",
              function() {
                aktifkanTombol();
                setFocus($('#katasandibaru'));
              });
        return false;
      }

      if(ulangikatasandibaru == ""){
        alertWarning("{{ trans('all.ulangikatasandibarukosong') }}",
              function() {
                aktifkanTombol();
                setFocus($('#ulangikatasandibaru'));
              });
        return false;
      }

      if(katasandibaru != ulangikatasandibaru){
        alertWarning("{{ trans('all.katasandibarudanulangikatasandibarutidaksama') }}",
              function() {
                aktifkanTombol();
                setFocus($('#ulangikatasandibaru'));
              });
        return false;
      }
    }

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
                        positionClass: 'toast-bottom-right'
                    };
                    toastr.info('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
                }, 500);
      });
    @endif
    </script>
  </head>
  <body class="gray-bg">

    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <div>

                <!-- <h1 class="logo-name">IN+</h1> -->

            </div>
            <!-- <h3>Welcome to IN+</h3>
            <p>Perfectly designed and precisely prepared admin theme with over 50 pages with extra new web app views. -->
                <!--Continually expanded and constantly improved Inspinia Admin Them (IN+)-->

            </p>
            <p>{{ trans('all.lupakatasandi') }}</p>
            <form class="m-t" id="formdaftar" role="form" method="POST" action="{{ url('/'.$jenis) }}" onsubmit="return validasi()">
              {{ csrf_field() }}
              @if($jenis == 'lupakatasandi_verifikasialternatif')
                    <div class="form-group">
                        <input type="text" name="email" id="email" autofocus autocomplete="off" class="form-control" placeholder="{{ trans('all.email') }}">
                    </div>
              @endif
              <div class="form-group">
                <input type="text" name="kodeverifikasi" id="kodeverifikasi" autofocus maxlength="6" autocomplete="off" class="form-control" placeholder="{{ trans('all.kodeverifikasi') }}">
              </div>
              <div class="form-group">
                <input type="password" name="katasandibaru" id="katasandibaru" maxlength="255" autocomplete="off" class="form-control" placeholder="{{ trans('all.katasandibaru') }}">
              </div>
              <div class="form-group">
                <input type="password" name="ulangikatasandibaru" id="ulangikatasandibaru" maxlength="255" autocomplete="off" class="form-control" placeholder="{{ trans('all.ulangikatasandibaru') }}">
              </div>
              <button type="submit" id="submit" class="btn btn-primary block full-width m-b">{{ trans('all.lanjut') }}</button>
              <p></p>
              <a class="btn btn-sm btn-white btn-block" href="lupakatasandi">{{ trans('all.kembali') }}</a>
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