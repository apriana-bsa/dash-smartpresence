<!DOCTYPE html>
<html>
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ Session::get('perusahaan_perusahaan') }} - @yield('title')</title>

    <link rel="stylesheet" href="{{ asset('lib/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/font-awesome/css/font-awesome.css') }}">

    <!-- Toastr style -->
    <link rel="stylesheet" href="{{ asset('lib/css/plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/css/plugins/blueimp/css/blueimp-gallery.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('/lib/flag-icon-css/css/flag-icon.min.css') }}" type="text/css" />

    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('/lib/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('/lib/css/button_loading.css') }}">
    <link rel="stylesheet" href="{{ asset('/lib/css/style-custom.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('/lib/css/plugins/iCheck/custom.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('/lib/css/style.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('/lib/css/sweetalert2.css') }}" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/lib/css/dataTables.bootstrap.css') }}">
    {{--<link rel="stylesheet" type="text/css" href="{{ asset('/lib/css/dataTables.min.css') }}">--}}
    <link rel="stylesheet" href="{{ asset('lib/css/token-input-facebook.css') }}" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('lib/css/iconselect.css') }}" >
    <link rel="stylesheet" href="{{ asset('lib/css/rangecalendar.css') }}" type="text/css" media="screen">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/typeaheadjs.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/BootSideMenu.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/css/plugins/blueimp/css/blueimp-gallery.min.css') }}" type="text/css" media="screen">
    <link rel="stylesheet" href="{{ asset('lib/css/plugins/blueimp/css/blueimp-rotate.css') }}" type="text/css" media="screen">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/jquery.simplecolorpicker.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/jquery.simplecolorpicker-regularfont.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/jquery.simplecolorpicker-glyphicons.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/jquery.simplecolorpicker-fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/css/select2.min.css') }}" />

    <!-- Mainly scripts -->
    <script src="{{ asset('lib/js/jQuery-2.1.4.min.js') }}"></script>
    <script src="{{ asset('lib/js/jquery.simplecolorpicker.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/iCheck/icheck.min.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>

    <!-- Custom and plugin javascript -->
    <script src="{{ asset('lib/js/inspinia.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/pace/pace.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('lib/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/js/jquery.ui.touch-punch.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/js/moment+langs.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/js/jquery.rangecalendar.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/blueimp/jquery.blueimp-gallery.min.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/blueimp/blueimp-rotate.js') }}"></script>

    <!-- Toastr script -->
    <script src="{{ asset('lib/js/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('/lib/js/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('/lib/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('/lib/js/sweetalert2.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/lib/js/dataTables.min.js') }}"></script>
    <script type="text/javascript" language="javascript" src="{{ asset('/lib/js/dataTables.bootstrap.js') }}"></script>
    <script src="{{ asset('/lib/js/BootSideMenu.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/lib/js/jquery.tokeninput.js') }}"></script>
    <script src="{{ asset('/lib/js/jquery.inputmask.js') }}"></script>
    <script src="{{ asset('/lib/js/jquery.inputmask.date.extensions.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/js/iconselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap-filestyle.js') }}"></script>
    <script src="{{ asset('lib/js/typeahead.min.js') }}"></script>
    <script src="{{ asset('lib/js/select2.min.js') }}"></script>
    <script type='text/javascript' src="{{ asset('/lib/js/util.js') }}"></script>
    {{-- <script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script> --}}
    <script>
    function toask(jenis) {
      $(document).ready(function () {
        setTimeout(function () {
          toastr.options = {
            closeButton: true,
            progressBar: true,
            timeOut: 5000,
            extendedTimeOut: 5000,
            positionClass: 'toast-bottom-right'
          };
          toastr.jenis('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
        }, 500);
      });
    }

    @if(Session::get('message_error'))
          $(document).ready(function() {
            setTimeout(function() {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 5000,
                    extendedTimeOut: 5000,
                    positionClass: 'toast-bottom-right'
                };
                toastr.error('{{ Session::get("message_error") }}', '{{ trans("all.pemberitahuan") }}');
            }, 500);
        });
    @endif

    function sinkronisasi(){
      $('#loading-saver').css('display', '');
      $('.fa-refresh').addClass("fa-spin");
      window.location.href='{{ url('sinkronisasi') }}';
    }

    $(function(){
        console.log('{{ trans('all.versi').' : '.Session::get('versiweb_perusahaan') }}');
        {{--console.log('debug middleware: {{ Session::get('conf_debug') }}');--}}
        //sempoerna
//        $( '#dropdown_pilihperusahaan' ).on( 'mousewheel DOMMouseScroll', function ( e ) {
//            var e0 = e.originalEvent,
//                delta = e0.wheelDelta || -e0.detail;
//
//            this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
//            e.preventDefault();
//        });
//
//        $( '#panduanprogram' ).on( 'mousewheel DOMMouseScroll', function ( e ) {
//            var e0 = e.originalEvent,
//                delta = e0.wheelDelta || -e0.detail;
//
//            this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
//            e.preventDefault();
//        });

        $( '.dropdowngroups' ).on( 'mousewheel DOMMouseScroll', function ( e ) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;

            this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
            e.preventDefault();
        });

        //bener tapi elek :D
//        var wheeldelta = {
//            x: 0,
//            y: 0
//        };
//        var wheeling;
//        $('#dropdown_pilihperusahaan').on('mousewheel', function (e) {
//            if (!wheeling) {
//                console.log('start wheeling!');
//                $('body').css({'overflow':'hidden'});
//            }
//
//            clearTimeout(wheeling);
//            wheeling = setTimeout(function() {
//                console.log('stop wheeling!');
//                wheeling = undefined;
//                $('body').css({'overflow':'auto'});
//
//                // reset wheeldelta
//                wheeldelta.x = 0;
//                wheeldelta.y = 0;
//            }, 250);
//
//            wheeldelta.x += e.deltaFactor * e.deltaX;
//            wheeldelta.y += e.deltaFactor * e.deltaY;
//            console.log(wheeldelta);
//        });
    });

    var lang_datatable = {
        lengthMenu: "{{ trans('all.tampil') }}  _MENU_",
        zeroRecords: "{{ trans('all.nodata') }}",
        emptyTable: "{{ trans('all.nodata') }}",
        info: "{{ trans('all.menampilkan') }} _START_ - _END_ {{ trans('all.of') }} _TOTAL_",
        infoEmpty: "",
        infoFiltered: "({{ trans('all.filterfrom') }} _MAX_ {{ trans('all.totalrecord') }})",
        sSearchPlaceholder: "{{ trans('all.pencarian') }}",
        sProcessing: "{{ trans('all.memuat') }}..."
    };

  @if (Session::get('perusahaan_jumlah_transaksi')==0 && Session::get('perusahaan_subscription') && url()->current() == url('/pembayaran'))
    $(document).ready(function() {
      setTimeout(function() {
        var opt = {
          template: '<div class="popover" role="tooltip" style="position: fixed"><div class="arrow"></div><h3 class="popover-title"></h3><a class="popover-cancel">&times;</a><div class="popover-content"></div></div>',
          content: '{{trans('onboarding.tanya_detail')}}',
          placement: 'auto left'
        };
        $('[data-toggle="popover-bottomleft"]').popover(opt)
        $('[data-toggle="popover-bottomleft"]').popover('show')
        $('[data-toggle="popover-bottomleft"]').on('shown.bs.popover', function () {
          var $popup = $(this);
          $(this).next('.popover').find('.popover-cancel').click(function (e) {
              $popup.popover('hide');
          });
        });
      }, 3000);
    });
  @endif

    $(function () {
      var option = {
        template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><a class="popover-cancel">&times;</a><div class="popover-content"></div></div>',
        // trigger: 'manual'
      };
      @if(Session::get('conf_webperusahaan')==0 && count(Session::get('conf_perusahaan'))>0)
        $('[data-toggle="popover_pilihperusahaan"]').popover(option)
        $('[data-toggle="popover_pilihperusahaan"]').popover('show')
        $('[data-toggle="popover_pilihperusahaan"]').on('shown.bs.popover', function () {
          var $popup = $(this);
          $(this).next('.popover').find('.popover-cancel').click(function (e) {
              $popup.popover('hide');
          });
        })
      @endif

      @if(Session::get('onboardingstep')<=1)
        $('[data-toggle="popover-atribut"]').popover(option)
        $('[data-toggle="popover-atribut"]').popover('show')
        $('[data-toggle="popover-atribut"]').on('shown.bs.popover', function () {
          var $popup = $(this);
          $(this).next('.popover').find('.popover-cancel').click(function (e) {
              $popup.popover('hide');
          });
        })
      @elseif(Session::get('onboardingstep')==2)
        $('[data-toggle="popover-jamkerja"]').popover(option)
        $('[data-toggle="popover-jamkerja"]').popover('show')
        $('[data-toggle="popover-jamkerja"]').on('shown.bs.popover', function () {
          var $popup = $(this);
          $(this).next('.popover').find('.popover-cancel').click(function (e) {
              $popup.popover('hide');
          });
        })
      @elseif(Session::get('onboardingstep')==3)
        $('[data-toggle="popover-pegawai"]').popover(option)
        $('[data-toggle="popover-pegawai"]').popover('show')
        $('[data-toggle="popover-pegawai"]').on('shown.bs.popover', function () {
          var $popup = $(this);
          $(this).next('.popover').find('.popover-cancel').click(function (e) {
              $popup.popover('hide');
          });
        })
      @elseif(Session::get('onboardingstep')==4)
        $('[data-toggle="popover-jamkerjapegawai"]').popover(option)
        $('[data-toggle="popover-jamkerjapegawai"]').popover('show')
        $('[data-toggle="popover-jamkerjapegawai"]').on('shown.bs.popover', function () {
          var $popup = $(this);
          $(this).next('.popover').find('.popover-cancel').click(function (e) {
              $popup.popover('hide');
          });
        })
      @elseif(Session::get('onboardingstep')==5)
        $('[data-toggle="popover-device"]').popover(option)
        $('[data-toggle="popover-device"]').popover('show')
        $('[data-toggle="popover-device"]').on('shown.bs.popover', function () {
          var $popup = $(this);
          $(this).next('.popover').find('.popover-cancel').click(function (e) {
              $popup.popover('hide');
          });
        })
      @elseif(Session::get('onboardingstep')==6 || Session::get('onboardingstep')==7)
        $('[data-toggle="popover-payment"]').popover(option)
        $('[data-toggle="popover-payment"]').popover('show')
        $('[data-toggle="popover-payment"]').on('shown.bs.popover', function () {
          var $popup = $(this);
          $(this).next('.popover').find('.popover-cancel').click(function (e) {
              $popup.popover('hide');
          });
        })
      @endif
    });

    $(document).ready(function() {
      $("#form-pembayaran").keypress(function(event){
        if (event.which == '13') {
          event.preventDefault();
        }
      });
    });
    function numberWithSeparator(x) {
      return "Rp" + x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function changeKuota(periode, new_kuota) {
      var subtotal = periode * new_kuota * {{ Session::get('perusahaan_unitprice',0)}};
      if(periode == 12) {
          subtotal = periode * new_kuota * {{ Session::get('perusahaan_unitprice',0)-env('YEARLY_DISCOUNT',0) }};
      }
      var ppn10 = 0.1 * subtotal;
      var total = subtotal + ppn10;
      if(periode===1) {
        document.getElementById("subtotal_1bulan").textContent = numberWithSeparator(subtotal);
        document.getElementById("ppn_1bulan").textContent = numberWithSeparator(ppn10);
        document.getElementById("total_1bulan").textContent = numberWithSeparator(total);
      }
      else if(periode === 3) {
        document.getElementById("subtotal_3bulan").textContent = numberWithSeparator(subtotal);
        document.getElementById("ppn_3bulan").textContent = numberWithSeparator(ppn10);
        document.getElementById("total_3bulan").textContent = numberWithSeparator(total);
      }
      else if(periode === 6) {
        document.getElementById("subtotal_6bulan").textContent = numberWithSeparator(subtotal);
        document.getElementById("ppn_6bulan").textContent = numberWithSeparator(ppn10);
        document.getElementById("total_6bulan").textContent = numberWithSeparator(total);
      }
      else if(periode === 12) {
        document.getElementById("subtotal_12bulan").textContent = numberWithSeparator(subtotal);
        document.getElementById("ppn_12bulan").textContent = numberWithSeparator(ppn10);
        document.getElementById("total_12bulan").textContent = numberWithSeparator(total);
      }
    }
    </script>
  <style>
    .dropdown-menu-ul > li.active {
        background-color: #337ab7 !important;
        color:#fff;
    }

    .dropdown-menu-ul > li:hover {
        background-color: #f2f2f3;
    }

    .blueimp-gallery {
        z-index:999999999999999999999999 !important;
    }

    .card {
      box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.2);
      transition: 0.3s;
      border-radius: 5px; /* 5px rounded corners */
      margin-bottom: 8px;
    }

    /* On mouse-over, add a deeper shadow */
    .card:hover {
      box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
    }

    /* Add some padding inside the card container */
    .card-body {
      padding: 8px;
    }

    .card-title {
      font-size: 24px;
      font-family: sans-serif;
      font-weight: 500;
    }

    .card-text {
      font-size: 14px;
      font-weight: 200;
    }

    .benefits {
      font-family: sans-serif;
      font-weight: 400;
      font-size: 12px;
    }

    .table.table-responsive {
      width: 100%;
      margin-bottom: 15px;
      overflow-x: auto;
      overflow-y: hidden;
      -webkit-overflow-scrolling: touch;
      -ms-overflow-style: -ms-autohiding-scrollbar;
      border: 0px;
    }

    .nav.navonboarding {
      justify-content: center;
      display: flex;
    }

    .nav.navonboarding > li.active > a,
    .nav-pills>li.active>a:focus,
    .nav-pills>li.active>a:hover {
        color: #337ab7;
        background-color: transparent;
    }

    .nav.nav-pills li.active{
      background-color: white;
      border-left: 0;
      border-bottom: 3px solid #337ab7;
    }

    .popover {
      background-color: orange;
    }

    .popover .popover-title {
      color: #fff;
    }

    .popover .popover-cancel {
      position: absolute;
      top: 8px;
      right:14px;
      font-size: 21px;
      font-weight: 700;
      line-height: 1;
      color: #000;
      text-shadow: 0 1px 0 #fff;
      opacity: .2;
    }

    .popover-cancel:focus,
    .popover-cancel:hover {
      color: #000;
      text-shadow: 0 1px 0 #fff;
      opacity: .4
    }

    .popover .popover-content {
      padding-right: 34px;
      color: #fff;
    }

    .popover.bottom > .arrow:after {
      border-bottom-color: orange;
    }
    .popover.left > .arrow:after {
      border-left-color: orange;
    }
    .popover.top > .arrow:after {
      border-top-color: orange;
    }
    .popover.right > .arrow:after {
      border-right-color: orange;
    }
    /*.navbar-static-top {*/
      /*z-index:0 !important;*/
    /*}*/
    /*.navbar-static-side {*/
      /*z-index:0 !important;*/
    /*}*/
    /*#page-wrapper {*/
      /*z-index:0 !important;*/
    /*}*/
    /*.footer {*/
      /*z-index:0 !important;*/
    /*}*/
  </style>
  </head>
  <body id="mainbody">
    <div id="wrapper">
      <div id='loading-saver' style='display:none;position:absolute;color:white;background: rgba(0, 0, 0, 0);width:100%;height:100%;z-index:99999999999999;'><span style="width:100%;position:absolute;margin:20% 0%"></span></div>
      <div id='loading-saver-withspinner' style='display:none;position:absolute;color:white;background: rgba(0.5, 0.5, 0.5, 0.5);width:100%;height:100%;z-index:99999999999999;'><span style="width:100%;position:absolute;margin:20% 50%;font-size:50px"><i class="fa fa-spinner fa-spin"></i></span></div>

      <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
          <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
              <div class="dropdown profile-element">
                <span><img alt="image" class="img-circle" width="48px" height="48px" src="{{ url('foto/user/'.Session::get('iduser_perusahaan')) }}" /></span>
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                  <span class="clear">
                    <span class="block m-t-xs"> <strong class="font-bold">{{ Session::get('namauser_perusahaan') }} @if(Session::get('superuser_perusahaan') != '') <i class="fa fa-star" style="color:yellow"></i> @endif <b class="caret"></b></strong></span>
                    @if(Session::has('hakakses_perusahaan'))
                      <span class="text-muted text-xs block">
                        {{ Session::get('hakakses_perusahaan')->nama }}
                      </span>
                    @endif
                  </span>
                </a>
                <ul class="dropdown-menu animated fadeInRight m-t-xs">
                    @if(Session::has('hakakses_perusahaan'))
                      <li><a href="{{ url('perusahaan') }}">{{ trans('all.perusahaan') }}</a></li>
                    @endif
                    <li><a href="{{ url('profil') }}">{{ trans('all.profil') }}</a></li>
                    <li class="divider"></li>
                    <li><a href="{{ url('logout') }}">{{ trans('all.logout') }}</a></li>
                </ul>
              </div>
              <div class="logo-element">
                  BS
              </div>
            </li>
            <li @if($menu == 'beranda') class="active" @endif><a href="{{ url('/') }}"><i class="fa fa-home"></i> <span class="nav-label">{{ trans('all.beranda') }}</span></a></li>
            @if(Session::get('hakakses_perusahaan'))
              @if(Session::has('conf_webperusahaan'))
                @if(strpos(Session::get('hakakses_perusahaan')->pengelola, 'l') !== false)
                    <li @if($menu == 'pengelola') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('pengelola') : "#" }}"><i class="fa fa-users"></i> <span class="nav-label">{{ trans('all.pengelola') }}</span> <span class="label label-primary pull-right label-ajakan" style="display:none"></span></a></li>
                @endif
                <li @if($menu == 'ajakan') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('ajakan/diajak') : "#" }}"><i class="fa fa-envelope"></i> <span class="nav-label">{{ trans('all.ajakan') }}</span> <span class="label label-primary pull-right label-ajakan" style="display:none"></span></a></li>
                @if(strpos(Session::get('hakakses_perusahaan')->atribut, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->lokasi, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->aturatributdanlokasi, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->aturatributdanlokasi, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->facesample, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->agama, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->pekerjaan, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->alasanmasukkeluar, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->harilibur, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->ijintidakmasuk, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->cuti, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->logabsen, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->slideshow, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->batasan, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->hakakses, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->setulangkatasandipegawai, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->postingdata, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->hapusdata, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponeninputmanual, 'l') !== false)
                  <li @if($menu == 'pegawai') class="active" @elseif($menu == 'aturatributdanlokasi') class="active" @elseif($menu == 'atributvariable') class="active" @elseif($menu == 'atribut') class="active" @elseif($menu == 'lokasi') class="active" @elseif($menu == 'facesample') class="active" @elseif($menu == 'agama') class="active" @elseif($menu == 'alasanmasukkeluar') class="active" @elseif($menu == 'alasantidakmasuk') class="active" @elseif($menu == 'mesin') class="active" @elseif($menu == 'jamkerja') class="active" @elseif($menu == 'jamkerjapegawai') class="active" @elseif($menu == 'jamkerjafull') class="active" @elseif($menu == 'jamkerjakhusus') class="active" @elseif($menu == 'harilibur') class="active" @elseif($menu == 'ijintidakmasuk') class="active" @elseif($menu == 'logabsen') class="active" @elseif($menu == 'jadwalshift') class="active" @elseif($menu == 'tukarshift') class="active" @elseif($menu == 'cuti') class="active" @elseif($menu == 'setulangkatasandipegawai') class="active" @elseif($menu == 'hakakses') class="active" @elseif($menu == 'postingdata') class="active" @elseif($menu == 'slideshow') class="active" @elseif($menu == 'batasan') class="active" @elseif($menu == 'hapusdata') class="active" @elseif($menu == 'konfirmasiflag') class="active" @elseif($menu == 'pekerjaan') class="active" @elseif($menu == 'pekerjaankategori') class="active" @elseif($menu == 'indexlemburdanjamkerja') class="active" @elseif($menu == 'payrollkomponenmaster') class="active" @elseif($menu == 'payrollkomponeninputmanual') class="active" @elseif($menu == 'payrollpengaturan') class="active" @elseif($menu == 'payrollkomponenmastergroup') class="active" @elseif($menu == 'payrollposting') class="active" @elseif($menu == 'payrollslipgaji') class="active" @elseif($menu == 'aktivitas') class="active" @endif>
                    <a href="#">
                        <i class="fa fa-book"></i><span class="nav-label">{{ trans('all.datainduk') }}<span class="fa arrow"></span></span>
                    </a>
                    <ul class="nav nav-second-level collapse">
                        @if(strpos(Session::get('hakakses_perusahaan')->atribut, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->lokasi, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->aturatributdanlokasi, 'u') !== false  || strpos(Session::get('hakakses_perusahaan')->aturatributdanlokasi, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->facesample, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->agama, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->pekerjaan, 'l') !== false)
                          <li @if($menu == 'pegawai') class="active" @elseif($menu == 'aturatributdanlokasi') class="active" @elseif($menu == 'atributvariable') class="active" @elseif($menu == 'atribut') class="active" @elseif($menu == 'lokasi') class="active" @elseif($menu == 'facesample') class="active" @elseif($menu == 'agama') class="active" @elseif($menu == 'pekerjaan') class="active" @elseif($menu == 'pekerjaankategori') class="active" @elseif($menu == 'aktivitas') class="active" @endif>
                            <a href="#">
                              <i class="fa fa-angle-right"></i>{{ trans('all.kepegawaian') }}<span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-third-level">
                              @if(strpos(Session::get('hakakses_perusahaan')->atribut, 'l') !== false)
                                <li @if($menu == 'atributvariable') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/atributvariable') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.atributvariable') }}</a></li>
                                <li @if($menu == 'atribut') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/atribut') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.atribut') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->lokasi, 'l') !== false)
                                  <li @if($menu == 'lokasi') class="active" @endif>
                                      <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/lokasi') : "#" }}">
                                          <table>
                                              <tr>
                                                  <td style="padding:0" valign="top">
                                                      <i class="fa fa-angle-double-right"></i>
                                                  </td>
                                                  <td style="padding:0">
                                                      {{ trans('all.menu_lokasi') }}
                                                  </td>
                                              </tr>
                                          </table>
                                      </a>
                                  </li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'l') !== false)
                                      <li @if($menu == 'pegawai') class="active" @endif><a href="{{ url('datainduk/pegawai/pegawai')  }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.menu_pegawai') }}</a></li>
                                <!-- <li @if($menu == 'pegawai') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/pegawai') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.menu_pegawai') }}</a></li> -->
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->aturatributdanlokasi, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->aturatributdanlokasi, 'l') !== false)
                                <li @if($menu == 'aturatributdanlokasi') class="active" @endif>
                                    <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/pegawai/aturatributdanlokasi/aturatribut') : "#" }}">
                                        <table>
                                            <tr>
                                                <td style="padding:0" valign="top">
                                                    <i class="fa fa-angle-double-right"></i>
                                                </td>
                                                <td style="padding:0">
                                                    {{ trans('all.aturatributdanlokasi') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </a>
                                </li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->facesample, 'l') !== false)
                                  <li @if($menu == 'facesample') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/facesample') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.menu_facesample') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->agama, 'l') !== false)
                                  <li @if($menu == 'agama') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/agama') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.menu_agama') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->pekerjaan, 'l') !== false)
{{--                                  <li @if($menu == 'pekerjaan') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/pekerjaan') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.pekerjaan') }}</a></li>--}}
                                  <li @if($menu == 'pekerjaankategori') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/pekerjaankategori') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.menu_kategoripekerjaan') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'l') !== false)
{{--                                  <li @if($menu == 'pekerjaan') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/pekerjaan') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.pekerjaan') }}</a></li>--}}
                                  <li @if($menu == 'aktivitas') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/pegawai/aktivitas') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.aktivitas') }}</a></li>
                              @endif
                            </ul>
                          </li>
                        @endif
                        @if(strpos(Session::get('hakakses_perusahaan')->alasanmasukkeluar, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'l') !== false)
                          <li @if($menu == 'alasanmasukkeluar') class="active" @elseif($menu == 'alasantidakmasuk') class="active" @endif>
                            <a href="#">
                              <i class="fa fa-angle-right"></i>{{ trans('all.catatankehadiran') }}<span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-third-level collapse">
                              @if(strpos(Session::get('hakakses_perusahaan')->alasanmasukkeluar, 'l') !== false)
                                <li @if($menu == 'alasanmasukkeluar') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/alasan/alasanmasukkeluar') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.menu_alasanmasukkeluar') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'l') !== false)
                                <li @if($menu == 'alasantidakmasuk') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/alasan/alasantidakmasuk') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.menu_alasantidakmasuk') }}</a></li>
                              @endif
                            </ul>
                          </li>
                        @endif
                        @if(strpos(Session::get('hakakses_perusahaan')->mesin, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->harilibur, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->ijintidakmasuk, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->cuti, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->logabsen, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'l') !== false)
                          <li @if($menu == 'mesin') class="active" @elseif($menu == 'jamkerja') class="active" class="active" @elseif($menu == 'jamkerjapegawai') class="active" @elseif($menu == 'jamkerjafull') class="active" @elseif($menu == 'jamkerjakhusus') class="active" @elseif($menu == 'harilibur') class="active" @elseif($menu == 'ijintidakmasuk') class="active" @elseif($menu == 'logabsen') class="active" @elseif($menu == 'jadwalshift') class="active" @elseif($menu == 'tukarshift') class="active" @elseif($menu == 'cuti') class="active" @elseif($menu == 'konfirmasiflag') class="active" @elseif($menu == 'indexlemburdanjamkerja') class="active" @endif>
                            <a href="#">
                              <i class="fa fa-angle-right"></i>{{ trans('all.absensi') }}<span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-third-level collapse">
                              @if(strpos(Session::get('hakakses_perusahaan')->mesin, 'l') !== false)
                                <li @if($menu == 'mesin') class="active" @endif>
                                    <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/mesin') : "#" }}">
                                        <table>
                                            <tr>
                                                <td style="padding:0" valign="top">
                                                    <i class="fa fa-angle-double-right"></i>
                                                </td>
                                                <td style="padding:0">
                                                    {{ trans('all.menu_mesin') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </a>
                                </li>
                                <li @if($menu == 'indexlemburdanjamkerja') class="active" @endif>
                                    <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/indexjamkerja') : "#" }}">
                                        <table>
                                            <tr>
                                                <td style="padding:0" valign="top">
                                                    <i class="fa fa-angle-double-right"></i>
                                                </td>
                                                <td style="padding:0">
                                                    {{ trans('all.indexlemburdanjamkerja') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </a>
                                </li>
                                {{--<li @if($menu == 'indexlembur') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/indexlembur') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.indexlembur') }}</a></li>--}}
                                {{--<li @if($menu == 'indexjamkerja') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/indexjamkerja') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.indexjamkerja') }}</a></li>--}}
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 'l') !== false)
                                <li @if($menu == 'jamkerja') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/jamkerja') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.jamkerja') }}</a></li>
                                <li @if($menu == 'jamkerjapegawai') class="active" @endif>
                                    <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/jamkerjapegawai') : "#" }}">
                                        <table>
                                            <tr>
                                                <td style="padding:0" valign="top">
                                                    <i class="fa fa-angle-double-right"></i>
                                                </td>
                                                <td style="padding:0">
                                                    {{ trans('all.jamkerjapegawai') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </a>
                                </li>
                                <li @if($menu == 'jamkerjakhusus') class="active" @endif>
                                    <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/jamkerjakhusus') : "#" }}">
                                        <table>
                                            <tr>
                                                <td style="padding:0" valign="top">
                                                    <i class="fa fa-angle-double-right"></i>
                                                </td>
                                                <td style="padding:0">
                                                    {{ trans('all.jamkerjakhusus') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </a>
                                </li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->harilibur, 'l') !== false)
                                <li @if($menu == 'harilibur') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/harilibur') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.harilibur') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->ijintidakmasuk, 'l') !== false)
                                <li @if($menu == 'ijintidakmasuk') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/ijintidakmasuk') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.ijintidakmasuk') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->cuti, 'l') !== false)
                                  <li @if($menu == 'cuti') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/cuti') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.cuti') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->logabsen, 'l') !== false)
                                  <li @if($menu == 'logabsen') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/logabsen') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.menu_logabsen') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jadwalshift, 'l') !== false)
                                <li @if($menu == 'jadwalshift') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/jadwalshift') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.jadwalshift') }}</a></li>
                                <li @if($menu == 'tukarshift') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/tukarshift') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.tukarshift') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'l') !== false)
                                  <li @if($menu == 'konfirmasiflag') class="active" @endif>
                                      <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/absensi/konfirmasiflag') : "#" }}">
                                          <table>
                                              <tr>
                                                  <td style="padding:0" valign="top">
                                                      <i class="fa fa-angle-double-right"></i>
                                                  </td>
                                                  <td style="padding:0">
                                                      {{ trans('all.konfirmasi_flag') }}
                                                  </td>
                                              </tr>
                                          </table>
                                      </a>
                                  </li>
                              @endif
                            </ul>
                          </li>
                        @endif
                        @if(Session::get('perbolehkanpayroll_perusahaan') == 'y')
                            @if(strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponeninputmanual, 'l') !== false)
                                <li @if($menu == 'payrollpengaturan') class="active" @elseif($menu == 'payrollkomponenmastergroup') class="active" @elseif($menu == 'payrollkomponenmaster') class="active" @elseif($menu == 'payrollkomponeninputmanual') class="active" @elseif($menu == 'payrollkomponeninputmanual') class="active" @elseif($menu == 'payrollposting') class="active" @elseif($menu == 'payrollslipgaji') class="active" @endif>
                                    <a href="#">
                                    <i class="fa fa-angle-right"></i>{{ trans('all.payroll') }}<span class="fa arrow"></span>
                                    </a>
                                    <ul class="nav nav-third-level collapse">
                                        @if(strpos(Session::get('hakakses_perusahaan')->payrollpengaturan, 'l') !== false)
                                            <li @if($menu == 'payrollpengaturan') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/payroll/payrollpengaturan') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.pengaturan') }}</a></li>
                                        @endif
                                        @if(strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'l') !== false)
                                            <li @if($menu == 'payrollkomponenmaster') class="active" @endif>
                                                <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/payroll/payrollkelompok') : "#" }}">
                                                    <table>
                                                        <tr>
                                                            <td style="padding:0" valign="top">
                                                                <i class="fa fa-angle-double-right"></i>
                                                            </td>
                                                            <td style="padding:0">
                                                                {{ trans('all.komponenmaster') }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </a>
                                            </li>
                                        @endif
                                        @if(strpos(Session::get('hakakses_perusahaan')->payrollkomponeninputmanual, 'l') !== false)
                                            <li @if($menu == 'payrollkomponeninputmanual') class="active" @endif>
                                                <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/payroll/payrollkomponeninputmanual') : "#" }}">
                                                    <table>
                                                        <tr>
                                                            <td style="padding:0" valign="top">
                                                                <i class="fa fa-angle-double-right"></i>
                                                            </td>
                                                            <td style="padding:0">
                                                                {{ trans('all.komponeninputmanual') }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </a>
                                            </li>
                                        @endif
                                        <li @if($menu == 'payrollposting') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/payroll/payrollposting') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.postingdata') }}</a></li>
                                        <li @if($menu == 'payrollslipgaji') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/payroll/slipgaji') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.slipgaji') }}</a></li>
                                    </ul>
                                </li>
                            @endif
                        @endif
                        @if(strpos(Session::get('hakakses_perusahaan')->slideshow, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->batasan, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->hakakses, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->setulangkatasandipegawai, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->postingdata, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->hapusdata, 'i') !== false)
                          <li @if($menu == 'setulangkatasandipegawai') class="active" @elseif($menu == 'hakakses') class="active" @elseif($menu == 'slideshow') class="active" @elseif($menu == 'batasan') class="active" @elseif($menu == 'postingdata') class="active"  @elseif($menu == 'hapusdata') class="active" @endif>
                            <a href="#">
                              <i class="fa fa-angle-right"></i>{{ trans('all.lainlain') }}<span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-third-level collapse">
                              @if(strpos(Session::get('hakakses_perusahaan')->slideshow, 'l') !== false)
                                <li @if($menu == 'slideshow') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('pengaturan/slideshow') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.slideshow') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->batasan, 'l') !== false)
                                <li @if($menu == 'batasan') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/lainlain/batasanemail') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.batasan') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->hakakses, 'l') !== false)
                                <li @if($menu == 'hakakses') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/lainlain/hakakses') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.hakakses') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->setulangkatasandipegawai, 'l') !== false)
                                <li @if($menu == 'setulangkatasandipegawai') class="active" @endif>
                                    <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/lainlain/setulangkatasandipegawai') : "#" }}">
                                        <table>
                                            <tr>
                                                <td style="padding:0" valign="top">
                                                    <i class="fa fa-angle-double-right"></i>
                                                </td>
                                                <td style="padding:0">
                                                    {{ trans('all.setulangkatasandipegawai') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </a>
                                </li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->postingdata, 'i') !== false)
                                <li @if($menu == 'postingdata') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/lainlain/postingdata') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.postingdata') }}</a></li>
                              @endif
                              @if(strpos(Session::get('hakakses_perusahaan')->hapusdata, 'i') !== false)
                                 <li @if($menu == 'hapusdata') class="active" @endif><a href="{{ url('datainduk/lainlain/hapusdata/pegawai') }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.hapusdata') }}</a></li>
                                <!-- <li @if($menu == 'hapusdata') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/lainlain/hapusdata/pegawai') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.hapusdata') }}</a></li> -->
                              @endif
                            </ul>
                          </li>
                        @endif
                    </ul>
                  </li>
                @endif
                @if(strpos(Session::get('hakakses_perusahaan')->pengaturan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengaturan, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->customdashboard, 'l') !== false)
                  <li @if($menu == 'pengaturanumum') class="active" @elseif($menu == 'parameterekspor') class="active" @elseif($menu == 'formatsms') class="active" @elseif($menu == 'peringkat') class="active" @elseif($menu == 'customdashboard') class="active" @elseif($menu == 'customtv') class="active" @elseif($menu == 'tv') class="active" @endif>
                    <a href="#">
                        <i class="fa fa-gears"></i><span class="nav-label">{{ trans('all.pengaturan') }}</span><span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level collapse">
                      @if(strpos(Session::get('hakakses_perusahaan')->pengaturan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengaturan, 'l') !== false)
                        <li @if($menu == 'pengaturanumum') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('pengaturan/umum') : "#" }}"><i class="fa fa-angle-right"></i>{{ trans('all.umum') }}</a></li>
                        <li @if($menu == 'peringkat') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('pengaturan/peringkat') : "#" }}"><i class="fa fa-angle-right"></i>{{ trans('all.peringkat') }}</a></li>
                        <li @if($menu == 'formatsms') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('pengaturan/formatsmsabsen') : "#" }}"><i class="fa fa-angle-right"></i>{{ trans('all.formatsms') }}</a></li>
                        <li @if($menu == 'parameterekspor') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('pengaturan/parameterekspor') : "#" }}"><i class="fa fa-angle-right"></i>{{ trans('all.parameterekspor') }}</a></li>
                      @endif
                      @if(strpos(Session::get('hakakses_perusahaan')->customdashboard, 'l') !== false)
                        <li @if($menu == 'customdashboard') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('pengaturan/customdashboard') : "#" }}"><i class="fa fa-angle-right"></i>{{ trans('all.customdashboard') }}</a></li>
                      @endif
                      @if(strpos(Session::get('hakakses_perusahaan')->pengaturan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengaturan, 'l') !== false)
                        <li @if($menu == 'tv') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('pengaturan/tv') : "#" }}"><i class="fa fa-angle-right"></i>{{ trans('all.tv') }}</a></li>
                      @endif
                    </ul>
                  </li>
                @endif
                @if(Session::has('perusahaan_expired'))
                  @if(Session::get('perusahaan_expired') == 'tidak')
                    @if(strpos(Session::get('hakakses_perusahaan')->laporan, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->laporanperpegawai, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->laporanlogabsen, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->laporankehadiran, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->laporanrekapparuhwaktu, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->laporanpertanggal, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->laporanekspor, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->laporanlogtrackerpegawai, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->laporanlainnya, 'l') !== false)
                      <li @if($menu == 'lapperpegawai') class="active" @elseif($menu == 'logtrackerpegawai') class="active" @elseif($menu == 'laplogabsen') class="active" @elseif($menu == 'rekapitulasi') class="active" @elseif($menu == 'rekapshift') class="active" @elseif($menu == 'rekapitulasishift') class="active" @elseif($menu == 'pertanggal') class="active" @elseif($menu == 'rekapabsen') class="active" @elseif($menu == 'lapshift') class="active" @elseif($menu == 'terlambat') class="active" @elseif($menu == 'pulangawal') class="active" @elseif($menu == 'kehadiran') class="active" @elseif($menu == 'lainnya') class="active" @elseif($menu == 'perlokasi') class="active" @elseif($menu == 'lappekerjaanuser') class="active" @elseif($menu == 'laporankomponenmaster') class="active"  @elseif($menu == 'laporankomponeninputmanual') class="active" @elseif($menu == 'laporancustomekspor') class="active" @elseif($menu == 'lapaktivitas') class="active" @endif>
                        <a href="#">
                            <i class="fa fa-line-chart"></i><span class="nav-label">{{ trans('all.laporan') }}</span><span class="fa arrow"></span>
                        </a>
                        <ul class="nav nav-second-level collapse">
                            @if(strpos(Session::get('hakakses_perusahaan')->laporanperpegawai, 'l') !== false)
                                <li @if($menu == 'lapperpegawai') class="active" @endif><a href="{{ url('laporan/perpegawai') }}"><i class="fa fa-angle-right"></i>{{ trans('all.perpegawai') }}</a></li>
                            @endif
                            @if(strpos(Session::get('hakakses_perusahaan')->laporanlogabsen, 'l') !== false)
                                <li @if($menu == 'laplogabsen') class="active" @endif><a href="{{ url('laporan/logabsen') }}"><i class="fa fa-angle-right"></i>{{ trans('all.menu_laporan_logabsen') }}</a></li>
                            @endif
                            @if(strpos(Session::get('hakakses_perusahaan')->laporankehadiran, 'l') !== false)
                                <li @if($menu == 'kehadiran') class="active" @endif><a href="{{ url('laporan/kehadiran') }}"><i class="fa fa-angle-right"></i>{{ trans('all.kehadiran') }}</a></li>
                            @endif
                            @if(strpos(Session::get('hakakses_perusahaan')->laporanrekapparuhwaktu, 'l') !== false)
                                {{--<li @if($menu == 'rekapitulasi') class="active" @endif><a href="{{ url('laporan/rekapitulasi') }}"><i class="fa fa-angle-right"></i>{{ trans('all.rekapitulasi') }}</a></li>--}}
                                <li @if($menu == 'rekapshift') class="active" @endif><a href="{{ url('laporan/rekapshift') }}"><i class="fa fa-angle-right"></i>{{ trans('all.rekapshift') }}</a></li>
                            @endif
                            @if(strpos(Session::get('hakakses_perusahaan')->laporanpertanggal, 'l') !== false)
                                <li @if($menu == 'pertanggal') class="active" @endif><a href="{{ url('laporan/pertanggal') }}"><i class="fa fa-angle-right"></i>{{ trans('all.pertanggal') }}</a></li>
                            @endif
                            @if(strpos(Session::get('hakakses_perusahaan')->laporanekspor, 'l') !== false)
                                <li @if($menu == 'rekapabsen') class="active" @endif><a href="{{ url('laporan/rekapabsen') }}"><i class="fa fa-angle-right"></i>{{ trans('all.ekspor') }}</a></li>
                            @endif
                            @if(strpos(Session::get('hakakses_perusahaan')->laporanlogtrackerpegawai, 'l') !== false)
                                <li @if($menu == 'logtrackerpegawai') class="active" @endif><a href="{{ url('logtrackerpegawairealtime') }}"><i class="fa fa-angle-right"></i>{{ trans('all.logtrackerpegawai') }}</a></li>
                            @endif
                            @if(strpos(Session::get('hakakses_perusahaan')->laporanlainnya, 'l') !== false)
                                <li @if($menu == 'lainnya') class="active" @endif><a href="{{ url('laporan/lainnya/harilibur') }}"><i class="fa fa-angle-right"></i>{{ trans('all.lainnya') }}</a></li>
                            @endif
                            @if(strpos(Session::get('hakakses_perusahaan')->laporanperlokasi, 'l') !== false)
                                <li @if($menu == 'perlokasi') class="active" @endif><a href="{{ url('laporan/perlokasi') }}"><i class="fa fa-angle-right"></i>{{ trans('all.perlokasi') }}</a></li>
                            @endif
                            @if(strpos(Session::get('hakakses_perusahaan')->laporanpekerjaanuser, 'l') !== false)
{{--                                <li @if($menu == 'lappekerjaanuser') class="active" @endif><a href="{{ url('laporan/pekerjaanuser') }}"><i class="fa fa-angle-right"></i>{{ trans('all.pekerjaanuser') }}</a></li>--}}
                                <li @if($menu == 'lappekerjaanuser') class="active" @endif><a href="{{ url('laporan/pekerjaaninput') }}"><i class="fa fa-angle-right"></i>{{ trans('all.pekerjaanuser') }}</a></li>
                            @endif
                            @if(strpos(Session::get('hakakses_perusahaan')->laporanpekerjaanuser, 'l') !== false)
{{--                                <li @if($menu == 'lappekerjaanuser') class="active" @endif><a href="{{ url('laporan/pekerjaanuser') }}"><i class="fa fa-angle-right"></i>{{ trans('all.pekerjaanuser') }}</a></li>--}}
                                <li @if($menu == 'lapaktivitas') class="active" @endif><a href="{{ url('laporan/aktivitas') }}"><i class="fa fa-angle-right"></i>{{ trans('all.aktivitas') }}</a></li>
                            @endif
                            {{--@if(Session::get('superuser_perusahaan') != '')--}}
                                @if(strpos(Session::get('hakakses_perusahaan')->laporancustom, 'l') !== false)
                                    <li @if($menu == 'laporankomponenmaster') class="active" @elseif($menu == 'laporankomponenmastergroup') class="active" @elseif($menu == 'laporankomponenmaster') class="active" @elseif($menu == 'laporankomponeninputmanual') class="active" @elseif($menu == 'laporankomponeninputmanual') class="active" @elseif($menu == 'laporanposting') class="active" @elseif($menu == 'laporanslipgaji') class="active"  @elseif($menu == 'laporancustomekspor') class="active" @endif>
                                        <a href="#">
                                            <i class="fa fa-angle-right"></i>{{ trans('all.custom') }}<span class="fa arrow"></span>
                                        </a>
                                        <ul class="nav nav-third-level collapse">
{{--                                        @if(strpos(Session::get('hakakses_perusahaan')->laporanpengaturan, 'l') !== false)--}}
{{--                                            <li @if($menu == 'laporanpengaturan') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('datainduk/laporan/laporanpengaturan') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.pengaturan') }}</a></li>--}}
{{--                                        @endif--}}
{{--                                        @if(strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'l') !== false)--}}
                                            <li @if($menu == 'laporankomponenmaster') class="active" @endif>
                                                <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('laporan/custom/kelompok') : "#" }}">
                                                    <table>
                                                        <tr>
                                                            <td style="padding:0" valign="top">
                                                                <i class="fa fa-angle-double-right"></i>
                                                            </td>
                                                            <td style="padding:0">
                                                                {{ trans('all.komponenmaster') }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </a>
                                            </li>
{{--                                        @endif--}}
{{--                                        @if(strpos(Session::get('hakakses_perusahaan')->laporankomponeninputmanual, 'l') !== false)--}}
                                            <li @if($menu == 'laporankomponeninputmanual') class="active" @endif>
                                                <a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('laporan/custom/komponeninputmanual') : "#" }}">
                                                    <table>
                                                        <tr>
                                                            <td style="padding:0" valign="top">
                                                                <i class="fa fa-angle-double-right"></i>
                                                            </td>
                                                            <td style="padding:0">
                                                                {{ trans('all.komponeninputmanual') }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </a>
                                            </li>
{{--                                        @endif--}}
                                            <li @if($menu == 'laporancustomekspor') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('laporan/custom/ekspor') : "#" }}"><i class="fa fa-angle-double-right"></i>{{ trans('all.ekspor') }}</a></li>
                                        </ul>
                                    </li>
                                @endif
                            {{--@endif--}}
                        </ul>
                      </li>
                    @endif
                  @endif
                @endif
                @if(strpos(Session::get('hakakses_perusahaan')->riwayatpengguna, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->riwayatpegawai, 'l') !== false || strpos(Session::get('hakakses_perusahaan')->riwayatsms, 'l') !== false)
                  <li @if($menu == 'riwayatpenggunamobile') class="active" @elseif($menu == 'riwayatpenggunaweb') class="active" @elseif($menu == 'riwayatpegawai') class="active" @elseif($menu == 'riwayatsms') class="active" @endif>
                    <a href="#">
                        <i class="fa fa-address-book"></i><span class="nav-label">{{ trans('all.menu_riwayat') }}</span><span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level collapse">
                      @if(strpos(Session::get('hakakses_perusahaan')->riwayatpengguna, 'l') !== false)
                        <li @if($menu == 'riwayatpenggunaweb') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('laporan/riwayat/penggunaweb') : "#" }}"><i class="fa fa-angle-right"></i>{{ trans('all.pengguna') }}</a></li>
                      @endif
                      @if(strpos(Session::get('hakakses_perusahaan')->riwayatpegawai, 'l') !== false)
                        <li @if($menu == 'riwayatpegawai') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('laporan/riwayat/pegawai') : "#" }}"><i class="fa fa-angle-right"></i>{{ trans('all.pegawai') }}</a></li>
                      @endif
                      @if(strpos(Session::get('hakakses_perusahaan')->riwayatsms, 'l') !== false)
                        <li @if($menu == 'riwayatsms') class="active" @endif><a href="{{ Session::get('perusahaan_expired') == 'tidak' ? url('laporan/riwayat/sms') : "#" }}"><i class="fa fa-angle-right"></i>{{ trans('all.sms') }}</a></li>
                      @endif
                    </ul>
                  </li>
                @endif
              @else
                  <li @if($menu == 'ajakan') class="active" @endif><a href="{{ url('ajakan/diajak') }}"><i class="fa fa-envelope"></i> <span class="nav-label">{{ trans('all.ajakan') }}</span> <span class="label label-primary pull-right label-ajakan" style="display:none"></span></a></li>
              @endif
            @else
              @if(!Session::has('userbaru_perusahaan'))
                  <li @if($menu == 'ajakan') class="active" @endif><a href="{{ url('ajakan/diajak') }}"><i class="fa fa-envelope"></i> <span class="nav-label">{{ trans('all.ajakan') }}</span> @if(Session::get('ajakanbaru_perusahaan') != 0) <span class="label label-primary pull-right label-ajakan">{{ Session::get('ajakanbaru_perusahaan') }}</span> @endif</a></li>
              @endif
            @endif
            @if(!Session::has('userbaru_perusahaan'))
                <li @if($menu == 'bugsreport') class="active" @endif><a href="{{ url('bugsreport') }}"><i class="fa fa-bug"></i> <span class="nav-label">{{ trans('all.bugsreport') }}</span></a></li>
            @endif
            @if(Session::has('conf_webperusahaan'))
              <li @if($menu == 'sinkronisasi') class="active" @endif><a href="#" @if(Session::get('perusahaan_expired') == 'tidak') onclick="return sinkronisasi();" @endif><i class="fa fa-refresh"></i> <span class="nav-label">{{ trans('all.sinkronisasi') }}</span></a></li>
            @endif
              <li @if($menu == 'pembayaran') class="active" @endif><a href="{{ url('pembayaran') }}"><i class="fa fa-money"></i>{{ trans('all.menu_pembayaran') }}</a></li>
          </ul>
        </div>
      </nav>

      <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
          <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
              <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
              <form role="search" method="post" action="{{ url('pencarian') }}" class="navbar-form-custom">
                {{ csrf_field() }}
                  <div class="form-group">
                      <input type="text" autocomplete="off" @if(isset($pencarian)) value="{{ $pencarian }}" @endif placeholder="{{ trans('all.carisesuatu') }}..." autocomplete="off" class="form-control" name="pencarian" id="top-search">
                  </div>
              </form>
            </div>
            <ul class="nav navbar-top-links navbar-right">
                @if(Session::has('conf_webperusahaan'))
                    @if(Session::get('enable_onboarding') && Session::get('onboardingstep') == env('ONBOARDING_FINISHED_STEP',0) && Session::get('perusahaan_expired') == 'tidak')
                        <li id="panduanprogram" class="dropdowngroups">
                            <a href="#collapsePanduan" class="dropdown-toggle" data-toggle="collapse" aria-expanded="false" aria-controls="collapsePanduan">
                                <button class="btn btn-success" type="button">
                                    <span class='fa fa-lightbulb-o' style="cursor:pointer"></span> Panduan&nbsp;&nbsp;<b class="caret"></b>
                                </button>
                            </a>
                            {{-- <ul class="dropdown-menu" style='width:auto'>
                                <li class="header"><a href="#" onclick="return false"><center>-- {{ trans('all.langkahlangkah') }} --</center></a></li>
                                <li class="header"><a href="{{ url('datainduk/pegawai/pegawai/create') }}">@if(Session::get('perusahaan_showguide')[0] > 0) <i class="fa fa-check-square-o"></i> @else <i class="fa fa-square-o"></i> @endif&nbsp;&nbsp;{{ trans('all.tambahkanpegawai') }}</a></li>
                                <li class="header"><a href="{{ url('datainduk/absensi/jamkerja/create') }}">@if(Session::get('perusahaan_showguide')[1] > 0) <i class="fa fa-check-square-o"></i> @else <i class="fa fa-square-o"></i> @endif&nbsp;&nbsp;{{ trans('all.tambahkanjamkerja') }}</a></li>
                                <li class="header"><a href="{{ url('datainduk/absensi/jamkerjapegawai') }}">@if(Session::get('perusahaan_showguide')[2] > 0) <i class="fa fa-check-square-o"></i> @else <i class="fa fa-square-o"></i> @endif&nbsp;&nbsp;{{ trans('all.tambahkanjamkerjapegawai') }}</a></li>
                                <li class="header"><a href="{{ url('datainduk/absensi/mesin/create') }}">@if(Session::get('perusahaan_showguide')[3] > 0) <i class="fa fa-check-square-o"></i> @else <i class="fa fa-square-o"></i> @endif&nbsp;&nbsp;{{ trans('all.tambahkanmesin') }}</a></li>
                                {{--jika jamkerja yg di buat adalah jam kerja shift--}}
                                {{--<li class="header"><a href="{{ url('datainduk/absensi/jadwalshift') }}"><input type="checkbox" disabled>&nbsp;&nbsp;{{ trans('all.tambahkanjadwalshift') }}</a></li>--}}
                            {{-- </ul> --}}
                        </li>
                    @endif
                @endif
                <li data-toggle="popover_pilihperusahaan" data-placement="auto bottom" data-content="{{ trans('onboarding.pilih_perusahaan') }}" onclick="return loadHeaderContent('perusahaan','perusahaan_body')">
                  <a href="#" class="dropdown-toggle" id="pilihperusahaan" data-toggle="dropdown">
                    @if(Session::has('conf_webperusahaan'))
                      @foreach(Session::get('conf_perusahaan') as $perusahaan)
                        @if(Session::get('conf_webperusahaan') == $perusahaan->id) {{ $perusahaan->nama }}  <b class="caret"></b> @endif
                      @endforeach
                    @else
                      {{ trans('all.perusahaan') }} <b class="caret"></b>
                    @endif
                  </a>
                  <div class="dropdown-menu" id="perusahaan_body" style='min-height:50px; min-width:320px !important;'>

                  </div>
                </li>
                <li onclick="return loadHeaderContent('inbox','inbox_body')">
                    <a id="inbox" class="dropdown-toggle count-info" data-toggle="dropdown" href="#" aria-expanded="true">
                        <i class="fa fa-envelope" style="color:#ed5565"></i>  <span class="label label-primary" id="jumlahinbox" @if(Session::get('conf_user_kotakpesan') == 0) style="display: none;" @endif>{{ Session::get('conf_user_kotakpesan') }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-alerts dropdowngroups" id="inbox_body" style='max-height:400px;overflow: auto;'>
                    </ul>
                </li>
                @if(Session::has('conf_webperusahaan'))
                    @if(Session::get('tampil_konfirmasi') == 'y')
                        <div style="display: none">{{Session::get('conf_konfirmasi')}}</div>
                        @if(Session::has('conf_konfirmasi'))
                          @if(Session::get('conf_konfirmasi') > 0)
                            <li onclick="return loadHeaderContent('notif','notif_body')">
                              <a id="notif" class="dropdown-toggle count-info" data-toggle="dropdown" href="#" aria-expanded="true">
                                <i class="fa fa-bell" style="color:#f8ac59"></i>  <span class="label label-primary">{{ Session::get('conf_konfirmasi') }}</span>
                              </a>
                              <ul class="dropdown-menu dropdown-alerts dropdowngroups" id="notif_body" style='max-height:400px;min-width:360px;overflow: auto;'></ul>
                            </li>
                          @endif
                        @endif
                    @endif
                @endif
                <li>
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    @if (Lang::locale() == 'id')
                      <span class='flag-icon flag-icon-id' style="cursor:pointer"></span> <b class="caret"></b>
                    @elseif (Lang::locale() == 'en')
                      <span class='flag-icon flag-icon-gb' style="cursor:pointer"></span> <b class="caret"></b>
                    @elseif (Lang::locale() == 'cn')
                      <span class='flag-icon flag-icon-cn' style="cursor:pointer"></span> <b class="caret"></b>
                    @endif
                  </a>
                  <ul class="dropdown-menu dropdowngroups" style='width:auto'>
                      <li class="header"><a href="{{ url('bahasa/id') }}"><span class="flag-icon flag-icon-id"></span>&nbsp;&nbsp;Indonesia</a></li>
                      <li class="header"><a href="{{ url('bahasa/en') }}"><span class="flag-icon flag-icon-gb"></span>&nbsp;&nbsp;English</a></li>
                    <li class="header"><a href="{{ url('bahasa/cn') }}"><span class="flag-icon flag-icon-cn"></span>&nbsp;&nbsp;中国</a></li>
                  </ul>
                </li>
                <li>
                    <a href="{{ url('logout') }}">
                        <i class="fa fa-sign-out"></i> <span id="tulisanlogout">{{ trans('all.logout') }}</span>
                    </a>
                </li>
                @if(Session::has('conf_webperusahaan'))
                    @if(Session::get('perusahaan_sisatrial') <= 14)
                        <li style="min-height:60px;" class="li_sisatrial">
                            <a href="{{ Session::get('perusahaan_subscription') ? "/pembayaran" : "#" }}" style="color:white;cursor:pointer;">
                            {{--<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color:white;font-weight: normal">--}}
                                @if(Session::get('perusahaan_ispremium') == 'y')
                                    {!! trans('all.masaaktifsisa').' '.Session::get('perusahaan_sisatrial').' '.trans('all.hari') !!}
                                @else
                                    {!! trans('all.trialsisa').' '.Session::get('perusahaan_sisatrial').' '.trans('all.hari') !!}
                                @endif
                            </a>
                        </li>
                    @endif
                @endif
            </ul>
        </nav>
        <!--  Validasi menu pembayaran -->
        @if($menu != 'pembayaran')
            @if (Session::get('enable_onboarding'))
              @if ((Session::get('perusahaan_expired') == 'tidak' && Session::get('onboardingstep') <= env('ONBOARDING_FINISHED_STEP',0) && Session::has('conf_webperusahaan')))
                @if(Session::get('onboardingstep') == env('ONBOARDING_FINISHED_STEP',0) && Session::get('perusahaan_jumlah_transaksi')>0)
                  <div class="row collapse" id="collapsePanduan">
                @else
                  <div class="row collapse in" id="collapsePanduan">
                @endif
              @else
                <div class="row collapse" id="collapsePanduan">
              @endif
                <div class="col-lg-12 text-center">
                  <div class="ibox float-e-margins">
                    <div class="ibox-content text-center p-md">
                      <h2>Panduan</h2>
                      <ul class="nav navonboarding nav-pills">
                        <li role="presentation" @if(url()->current() == url('/datainduk/pegawai/atribut/create') && Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0)) class="active" @endif data-toggle="popover-atribut" data-placement="auto bottom" data-content="{{ trans('onboarding.langkah_1') }}">
                          <a href="{{ Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0) ? url('/datainduk/pegawai/atribut/create?onboarding=true') : "#"}}">+ Attribut</a>
                        </li>
                        <li role="presentation" @if(url()->current() == url('/datainduk/absensi/jamkerja/create') && Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0)) class="active" @endif data-toggle="popover-jamkerja" data-placement="auto bottom" data-content="{{ trans('onboarding.langkah_2') }}">
                          <a href="{{ Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0) && Session::get('onboardingstep')>1 ? url('/datainduk/absensi/jamkerja/create?onboarding=true') : "#"}}">+ Jam Kerja</a>
                        </li>
                        <li role="presentation" @if(url()->current() == url('/datainduk/pegawai/pegawai/create') && Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0)) class="active" @endif data-toggle="popover-pegawai" data-placement="auto bottom" data-content="{{ trans('onboarding.langkah_3') }}">
                          <a href="{{ Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0) && Session::get('onboardingstep')>2 ? url('/datainduk/pegawai/pegawai/create?onboarding=true') : "#"}}">+ Pegawai</a>
                        </li>
                        <li role="presentation" @if(url()->current() == url('/datainduk/absensi/jamkerjapegawai') && Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0)) class="active" @endif data-toggle="popover-jamkerjapegawai" data-placement="auto bottom" data-content="{{ trans('onboarding.langkah_4') }}">
                          <a href="{{ Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0) && Session::get('onboardingstep')>3 ? url('/datainduk/absensi/jamkerjapegawai?onboarding=true') : "#"}}">+ Jam Kerja Pegawai</a>
                        </li>
                        <li role="presentation" @if(url()->current() == url('/datainduk/absensi/mesin') && Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0)) class="active" @endif data-toggle="popover-device" data-placement="auto bottom" data-content="{{ trans('onboarding.langkah_5') }}">
                          <a href="{{ Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0) && Session::get('onboardingstep')>4 ? url('/datainduk/absensi/mesin/create?onboarding=true') : "#"}}">+ Sambungkan ke Device</a>
                        </li>
                      @if (Session::get('perusahaan_jumlah_transaksi')==0 && Session::get('perusahaan_subscription'))
                        <li role="presentation" @if(url()->current() == url('/pembayaran') && Session::get('onboardingstep') <= env('ONBOARDING_FINISHED_STEP',0)) class="active" @endif data-toggle="popover-payment" data-placement="auto bottom" data-content="{{ trans('onboarding.langkah_6') }}">
                          <a href="{{ Session::get('onboardingstep')<= env('ONBOARDING_FINISHED_STEP',0) && Session::get('onboardingstep')>5 ? url('/pembayaran') : "#"}}">+ Pembayaran</a>
                        </li>
                      @endif
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            @endif
        @endif
    </div>
    <style>
        @keyframes blink {
            0% {
                background-color: rgba(255,0,0,1)
            }
            50% {
                background-color: rgba(255,0,0,0.5)
            }
            100% {
                background-color: rgba(255,0,0,1)
            }
        }
        @-webkit-keyframes blink {
            0% {
                background-color: rgba(255,0,0,1)
            }
            50% {
                background-color: rgba(255,0,0,0.5)
            }
            100% {
                background-color: rgba(255,0,0,1)
            }
        }

        .li_sisatrial {
            -moz-transition:all 0.5s ease-in-out;
            -webkit-transition:all 0.5s ease-in-out;
            -o-transition:all 0.5s ease-in-out;
            -ms-transition:all 0.5s ease-in-out;
            transition:all 0.5s ease-in-out;
            -moz-animation:blink normal 1.5s infinite ease-in-out;
            /* Firefox */
            -webkit-animation:blink normal 1.5s infinite ease-in-out;
            /* Webkit */
            -ms-animation:blink normal 1.5s infinite ease-in-out;
            /* IE */
            animation:blink normal 1.5s infinite ease-in-out;
            /* Opera */
        }
    </style>

    @yield('content')

    <script>
    function perusahaanDalamKonfirmasi(idperusahaan){
        alertConfirm('{{ trans('all.perusahaanmasihdalamproseskonfirmasi') }}',
            function(){
                window.location.href="{{ url('perusahaan/kirimulangkonfirmasi') }}/"+idperusahaan;
            },
            function(){},
            "{{ trans('all.kirimulangemail') }}","{{ trans('all.tutup') }}"
        );
    }

    function perusahaanDalamProses(){
        alertInfo('{{ trans('all.sedangdalamprosespembuatan') }}');
    }

    function cariPerusahaan() {
        var input, filter, li, namaperusahaan, i, j;
        input = document.getElementById("cariperusahaan");
        filter = input.value.toUpperCase();
        li = document.getElementsByClassName("pilihanperusahaan");
        j = 0;
        for (i = 0; i < li.length; i++) {
            namaperusahaan = li[i].getElementsByTagName("a")[0].getElementsByTagName("table")[0].getElementsByClassName("cari_namaperusahaan")[0].getElementsByTagName("b")[0].innerHTML;
            if (namaperusahaan.toUpperCase().indexOf(filter) > -1) {
                li[i].style.display = "";
                j++;
            } else {
                li[i].style.display = "none";
            }
        }
        $('#totalperusahaandropdown').html(j);
    }

    function loadHeaderContent(jenis,iddiv){

//        if ($('#'+iddiv).html().trim()=='') {
            if(jenis == 'perusahaan'){
                $('#' + iddiv).html('').append('<center style="margin-top:13px"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></center>');
            }else {

                $('#' + iddiv).html('').append('<center><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></center>');
            }
            $.ajax({
                type: "GET",
                url: '{{ url('notif') }}/'+jenis,
                data: '',
                cache: false,
                success: function (html) {

                    $('#'+iddiv).html('').append(html);
                    if(jenis == 'perusahaan'){
                        $('#cariperusahaan').focus();
                    }
                }
            });
//        }else{
            setTimeout(function(){
                if(jenis == 'perusahaan'){
                    $('#cariperusahaan').focus();
                }
            },100);
//        }
    }

    window.detailPesan=(function(id){
        $("#showmodalgeneral").attr("href", "");
        $("#showmodalgeneral").attr("href", "{{ url('detailpesan') }}/"+id);
        $('#showmodalgeneral').trigger('click');
        // console.log($('#jumlahinbox').html());
        var jumlahinbox = $('#jumlahinbox').html();
        if(jumlahinbox > 0){
            $('#jumlahinbox').html(jumlahinbox-1);
            if(jumlahinbox-1 < 1){
                $('#jumlahinbox').css('display', 'none');
            }
        }
        return false;
    });

    function redirectSetPerushaaan(idperusahaan){
        window.stop();
        window.location.href="{{ url('setperusahaan') }}/"+idperusahaan;
    }

    //function untuk menu laporan
    function lapFilterMode(){
        $('#jangkauantanggal').css('display', 'none');
        $('#berdasarkantanggal').css('display', 'none');
        var filtermode = $('#filtermode').val();
        $('#'+filtermode).css('display', '');
        $('.tr_pertanggal').css('display', 'none');
        if(filtermode === 'jangkauantanggal'){
            $('.tr_pertanggal').css('display', '');
        }
    }

    function lapPilihTanggal(dari){
        if(dari === 'input') {
            if ($("#berdasarkantanggalinput").prop('checked')) {
                $(".pilihtanggal").css('display', '');
            } else {
                $(".pilihtanggal").css('display', 'none');
            }
        }else{
            if ($("#berdasarkantanggalinput").prop('checked')) {
                $(".pilihtanggal").css('display', 'none');
                $("#berdasarkantanggalinput").prop('checked', false);
            } else {
                $("#berdasarkantanggalinput").prop('checked', true);
                $(".pilihtanggal").css('display', '');
            }
        }
    }

    function lapPilihBulan(){
        var bulan = $('#bulan').val();
        var tahun = $('#tahun').val();
        $.ajax({
            type: "GET",
            url: '{{ url('totalhari') }}/'+bulan+'/'+tahun,
            data: '',
            cache: false,
            success: function (response) {
                var data = "";
                for(var i = 16; i<=response; i++){
                    data += "<input type='checkbox' class='tanggalcheck' onchange='checkAllAttr(\'tanggalcheck\',\'ceksemuatanggal\')' id='tanggal_"+i+"' name='tanggal[]' value='"+i+"'>&nbsp;" +
                        "<span onclick='spanClick(\'tanggal_"+i+"\')'>"+i+"</span>&nbsp;&nbsp;";
                }
                $('#changeable_pilihtanggal').html('').append(data);
            }
        });
    }

    window.detailKonfirmasiAbsen=(function(idkonfirmasiabsen,jenis,menu){
      if(menu == undefined){
          menu = 't';
      }
      $("#showmodalkonfirmasiabsen").attr("href", "");
      $("#showmodalkonfirmasiabsen").attr("href", "{{ url('detailkonfirmasiabsen') }}/"+jenis+"/"+idkonfirmasiabsen+'/'+menu);
      $('#showmodalkonfirmasiabsen').trigger('click');
      return false;
    });

    $('body').on('hidden.bs.modal', '.modalkonfirmasiabsen', function () {
      $(this).removeData('bs.modal');
      $("#" + $(this).attr("id") + " .modal-content").empty();
      $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
    });

  <!--Start of Tawk.to Script-->
  var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
  (function(){
      var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
      s1.async=true;
      s1.src='https://embed.tawk.to/5948c30750fd5105d0c81e14/default';
      s1.charset='UTF-8';
      s1.setAttribute('crossorigin','*');
      s0.parentNode.insertBefore(s1,s0);
  })();
  <!--End of Tawk.to Script-->
    window.callModalGeneral=(function(menu,url,size){
        if(size == undefined){
            size = 'modal-md';
        }
        $("#modalgeneralsize").addClass(size);
        $("#showmodalgeneral").attr("href", "");
        $("#showmodalgeneral").attr("href", url);
        $('#showmodalgeneral').trigger('click');
        return false;
    });

    $('body').on('hidden.bs.modal', '.modalgeneral', function () {
        $(this).removeData('bs.modal');
        $("#" + $(this).attr("id") + " .modal-content").empty();
        $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
    });
  </script>
    {{-- <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBKvVrSNfidfSNAVgj7N7dfwJ61L9UL4AM&libraries=places"
            async defer></script> --}}
    {{--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAER0-sl1KrqFIhFRu37bJdqiG_Z7NyduU&libraries=places"--}}
            {{--></script>--}}
    {{--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA_iruyRPWi6-pJd-diHU6qlSWJBsV6BYg&libraries=places"--}}
            {{--async defer></script> lightningmcqueen--}}
    <!-- Modal konfirmasiabsen-->

        {{-- <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script> --}}
    <a href="" id="showmodalkonfirmasiabsen" data-toggle="modal" data-target="#modalkonfirmasiabsen" style="display:none"></a>
    <div class="modal modalkonfirmasiabsen fade" id="modalkonfirmasiabsen" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md" style="width:640px">

        <!-- Modal content-->
        <div class="modal-content">
        </div>
      </div>
    </div>
    <!-- Modal konfirmasiabsen-->

    <!-- Modal general-->
    <a href="" id="showmodalgeneral" data-toggle="modal" data-target="#modalgeneral" style="display:none"></a>
    <div class="modal modalgeneral fade" id="modalgeneral" role="dialog" tabindex='-1'>
      <div class="modal-dialog" id="modalgeneralsize">

          <!-- Modal content-->
          <div class="modal-content">
          </div>
      </div>
    </div>
    <!-- Modal general-->

    <!-- Modal pegawai-->
    <a href="" id="showmodalpegawai" data-toggle="modal" data-target="#modalpegawai" style="display:none"></a>
    <div class="modal modalpegawai fade" id="modalpegawai" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">

          </div>
      </div>
    </div>
    <!-- Modal pegawai-->
    <script>
      window.detailpegawai=(function(idpegawai){
          $("#showmodalpegawai").attr("href", "");
          $("#showmodalpegawai").attr("href", "{{ url('detailpegawai') }}/"+idpegawai);
          $('#showmodalpegawai').trigger('click');
          return false;
      });

      $('body').on('hidden.bs.modal', '.modalpegawai', function () {
          $(this).removeData('bs.modal');
          $("#" + $(this).attr("id") + " .modal-content").empty();
          $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
      });
    </script>

    <!-- The Gallery as lightbox dialog, should be a child element of the document body -->
    {{--untuk popup gambar asli--}}
    <div id="blueimp-gallery" class="blueimp-gallery">
        <div class="slides"></div>
        <h3 class="title"></h3>
        <a class="prev">‹</a>
        <a class="next">›</a>
        <a class="close">×</a>
        <a class="play-pause"></a>
        <ol class="indicator"></ol>
        <a class="rotate-right"></a>
        <a class="rotate-left"></a>
    </div>

    <div class="footer">
      <div class="pull-right" style="margin-right:75px">
        &nbsp;&nbsp;
        <a target="_new" href="http://smartpresence.id/syarat-ketentuan-layanan/">{{ trans('all.syaratdanketentuan') }}</a>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <a target="_new" href="http://smartpresence.id/kebijakan-privasi/">{{ trans('all.kebijakanprivasi') }}</a>
      </div>
      <div style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap">
          {{config('consts.PERUSAHAAN_COPYRIGHT')}} &copy; {{ date('Y') }}
          &nbsp;<i class="fa fa-phone"></i>&nbsp;
          {{ trans('all.bantuan') }} : {{ config('consts.NO_TELP') }}
      </div>
      <div data-toggle="popover-bottomleft" style="padding: 10px; background-color:transparent; border-radius: 50%; position: fixed; bottom: 20px; right:20px; width: 60px; height:60px;"></div>
    </div>

    @stack('scripts')

  </body>
</html>
