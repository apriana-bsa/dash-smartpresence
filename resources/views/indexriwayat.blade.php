@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')
  
  <script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>
  <script>
  function goto(page){
    window.location.href=page;
  }

  $(function(){
      var newDate = new Date({{ date('Y') }}, {{ date('m') }}-1, {{ date('d') }});
      var defaultCalendar = $("#datepicker").rangeCalendar({changeRangeCallback: rangeChanged,lang:"{{ Session::get('conf_bahasaperusahaan') }}"});
      setTimeout(function(){ defaultCalendar.setStartDate(newDate); },200);

      function rangeChanged(el, cont) {
          var newdate = cont.start;
          var url = 'riwayatberanda/'+newdate+'/o';

          $('#loading-saver').css('display', '');
          $('#moredata').html('<center><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></center>');

          $.ajax({
              type: "GET",
              url: url,
              data: '',
              cache: false,
              success: function(html){
                  $('#loading-saver').css('display', 'none');
                  $('#moredata').html('').append(html);
              }
          });
      }
  });
  
  function modalMarkerPeta(marker,startfrom){

      $('#isimodalpeta').append('<center class="spinner-loadpeta"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></center>');
      setTimeout(function(){
          $.ajax({
              type: "GET",
              url: '{{ url('generatecsrftoken') }}',
              data: '',
              cache: false,
              success: function(token){
                  var dataString = 'marker='+marker+'&startfrom='+startfrom+'&_token='+token;
                  $.ajax({
                      type: "POST",
                      url: '{{ url('modalmarkerpeta') }}',
                      data: dataString,
                      cache: false,
                      success: function(html){
                          if(startfrom == 'o') {
                              $('#isimodalpeta').html('');
                              $('#showmodalpeta').trigger('click');
                              setTimeout(function () {
                                  $('#isimodalpeta').html(html);
                              }, 200);
                          }else{
                              setTimeout(function () {
                                  $('.spinner-loadpeta').remove();
                                  $('#isimodalpeta').append(html);
                              }, 200);
                          }
                      }
                  });
              }
          });
      },500);
      return false;
  }
  </script>
  <style>
  .progress{
      margin-bottom:30px;
  }

  .pingrid{
      position: relative;
  }

  .pin{
      position: absolute;
  }

  {{-- .row {
      margin-right: 0;
      margin-left: 0;
  } --}}

  html, body {
      height: 100%;
      margin: 0;
      padding: 0;
  }

  #map {
      height: 460px;
  }

  td{
      padding:5px;
  }

  .controls {
      margin-top: 10px;
      border: 1px solid transparent;
      border-radius: 2px 0 0 2px;
      box-sizing: border-box;
      -moz-box-sizing: border-box;
      height: 32px;
      outline: none;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
  }

  #pac-input {
      background-color: #fff;
      font-family: Roboto;
      font-size: 15px;
      font-weight: 300;
      margin-left: 12px;
      padding: 0 11px 0 13px;
      text-overflow: ellipsis;
      width: 300px;
      z-index:99999999;
  }

  #pac-input:focus {
      border-color: #4d90fe;
  }

  .pac-container {
      font-family: Roboto;
  }

  #type-selector {
      color: #fff;
      background-color: #4d90fe;
      padding: 5px 11px 0px 11px;
  }

  #type-selector label {
      font-family: Roboto;
      font-size: 13px;
      font-weight: 300;
  }
  #target {
      width: 345px;
  }

  .pac-container{
      z-index: 99999;
  }
  
  .info-box{
      display: block;
      min-height: 80px;
      background: #fff;
      width: 100%;
      box-shadow: 0 1px 1px rgba(0,0,0,0.1);
      border-radius: 2px;
      margin-bottom: 15px;
  }

  .info-box-icon {
      border-top-left-radius: 2px;
      border-top-right-radius: 0;
      border-bottom-right-radius: 0;
      border-bottom-left-radius: 2px;
      display: block;
      float: left;
      height: 80px;
      width: 80px;
      text-align: center;
      font-size: 45px;
      line-height: 72px;
      background: rgba(0,0,0,0.1);
  }

  .info-box-content {
      padding: 5px 10px;
      margin-left: 90px;
  }

  .info-box-text {
      display: block;
      font-size: 18px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      text-transform: uppercase;
  }

  .info-box-number {
      display: block;
      font-weight: bold;
      font-size: 28px;
  }

  .dataTables_wrapper{
      padding-bottom:0;
      margin-top:-8px;
  }

  #page-wrapper{
      padding-left: 0;
      padding-right: 0;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
        <h3 style="margin-top:15px;margin-bottom:0">
            <ul class="nav nav-tabs">
                <li><a href="{{ url('/') }}">{{ trans('all.beranda_beranda') }}</a></li>
                <li class="active"><a href="{{ url('riwayatberanda') }}">{{ trans('all.riwayat') }}</a></li>
                @if(strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'l') !== false)
                    <li><a href="{{ url('pekerjaaninput') }}">{{ trans('all.pekerjaan') }}</a></li>
                @endif
            </ul>
        </h3>
    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    @if(Session::has('conf_webperusahaan'))
      @if(Session::get('perusahaan_expired') == 'tidak')
          <div class="row">
            <div class="col-lg-12">
                <div id="datepicker"></div>
            </div>
            <div id="moredata">

            </div>
          </div>
      @else
          <div class="row">
              <div class="col-lg-12">
                  <div class="ibox float-e-margins">
                      <div class="ibox-content text-center p-md">
                          <h2>
                              {{ trans('all.perusahaantelahexpired') }}
                          </h2>
                      </div>
                  </div>
              </div>
          </div>
      @endif
    @else
      <div class="row">
        <div class="col-lg-12">
          <div class="ibox float-e-margins">
            <div class="ibox-content text-center p-md">
              <h2>
                {{ trans('all.selamatdatang') }}
              </h2>
              @if(Session::get('conf_totalperusahaan') == 0)
                <p>
                  {{ trans('all.andatidakberelasidenganperusahaanmanapun') }}
                </p>
              @else
                {{ trans('all.silahkanpilihperusahaan') }} /
              @endif
                    <a href="{!! url('tambahperusahaanbaru') !!}">{{ trans('all.tambahkanperusahaan') }}</a>
            </div>
          </div>
        </div>
      </div>
    @endif

    <!-- Modal peta-->
    <a href="" id="showmodalpeta" data-toggle="modal" data-target="#modalpeta" style="display:none"></a>
    <div class="modal modalpeta fade" id="modalpeta" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-sm" style="width:435px">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('all.peta') }}</h4>
                </div>
                <div class="modal-body body-modal" id="modalbody-peta" style="max-height:480px;overflow: auto;">
                    <div id="isimodalpeta"></div>
                </div>
                <div class="modal-footer">
                    <table width="100%">
                        <tr>
                            <td align="right" style="padding:0">
                                <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal peta-->

    <!-- Flot -->
    <script src="{{ asset('lib/js/plugins/flot/jquery.flot.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/flot/jquery.flot.tooltip.min.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/flot/jquery.flot.spline.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/flot/jquery.flot.resize.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/flot/jquery.flot.pie.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/flot/jquery.flot.symbol.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/flot/jquery.flot.time.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/chartJs/Chart.min_old.js') }}"></script>

    <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script>
@stop