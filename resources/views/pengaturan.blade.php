@extends('layouts/master')
@section('title', trans('all.umum'))
@section('content')

  <!-- Switchery -->
  <link href="{{ asset('lib/css/plugins/switchery/switchery.css') }}" rel="stylesheet">
  <script src="{{ asset('lib/js/plugins/switchery/switchery.js') }}"></script>
  <link href="{{ asset('lib/css/patternLock.css') }}"  rel="stylesheet" type="text/css" />
  <script src="{{ asset('lib/js/patternLock.min.js') }}"></script>
  <!-- NouSlider -->
  <script src="{{ asset('lib/js/plugins/nouslider/jquery.nouislider.min.js') }}"></script>
  <link href="{{ asset('lib/css/plugins/nouslider/jquery.nouislider.css') }}" rel="stylesheet">
  <script>
  $(function(){

    @if(Session::get('message'))
      setTimeout(function() {
                  toastr.options = {
                      closeButton: true,
                      progressBar: true,
                      timeOut: 4000,
                      extendedTimeOut: 4000,
                      positionClass: 'toast-bottom-right'
                  };
                  toastr.info('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
              }, 500);
    @endif

    $('.jam').inputmask( 'h:s' );
    $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
        $(this).datepicker('hide');
    });

    $('.date').mask("00/00/0000", {clearIfNotMatch: true});
  });

  function validasi(){

    $('#submit').attr( 'data-loading', '' );
    $('#submit').attr('disabled', 'disabled');
    $('#aturmesin').attr( 'data-loading', '' );
    $('#aturmesin').attr('disabled', 'disabled');
    $('#clear').attr('disabled', 'disabled');
    
    var batas_kemiripan_absen_wajah = $('#batas_kemiripan_absen_wajah').val();
    var batas_kemiripan_konfirmasi_absen_wajah = $('#batas_kemiripan_konfirmasi_absen_wajah').val();
    var batas_kemiripan_pendaftaran_wajah = $('#batas_kemiripan_pendaftaran_wajah').val();
    var batas_kemiripan_absen_wajah_otomatis = $('#batas_kemiripan_absen_wajah_otomatis').val();
    var batas_kemiripan_konfirmasi_absen_wajah_otomatis = $('#batas_kemiripan_konfirmasi_absen_wajah_otomatis').val();
    var batas_konfirmasi_absen = $('#batas_konfirmasi_absen').val();
    var toleransi_waktu_server = $('#toleransi_waktu_server').val();
    var toleransi_jarak_gps = $('#toleransi_jarak_gps').val();
    var end_of_day = $('#end_of_day').val();
    var trackerintervaldefault = $('#trackerintervaldefault').val();
    var employee_tracker_lamashiftberakhir = $('#employee_tracker_lamashiftberakhir').val();
    var mesin_polapengaman_pakai = $('#mesin_polapengaman_pakai').val();
    var mesin_polapengaman = $('#mesin_polapengaman').val();
    var mesin_deteksiekspresi = $('#mesin_deteksiekspresi').val();
    var mesin_deteksiekspresi_batas = $('#mesin_deteksiekspresi_batas').val();
    
    if (cekAlertAngkaValid(batas_kemiripan_absen_wajah,0,100,0,"{{ trans('all.bataskemiripanwajah') }}",
                      function() {
                        aktifkanTombol();
                        setFocus($('#batas_kemiripan_absen_wajah'));
                      }
                    )==false) return false;
  
    if (cekAlertAngkaValid(batas_kemiripan_konfirmasi_absen_wajah,0,100,0,"{{ trans('all.konfirmasibataskemiripanwajah') }}",
                      function() {
                        aktifkanTombol();
                        setFocus($('#batas_kemiripan_absen_wajah'));
                      }
                    )==false) return false;

    if(batas_kemiripan_konfirmasi_absen_wajah < 40){
      alertWarning("{{ trans('all.konfirmasibataskemiripanwajahminimal40') }}",
              function() {
                aktifkanTombol();
                setFocus($('#end_of_day'));
              });
      return false;
    }
    
    if (cekAlertAngkaValid(batas_kemiripan_pendaftaran_wajah,0,100,0,"{{ trans('all.bataskemiripanwajahwaktupendaftaran') }}",
                      function() {
                        aktifkanTombol();
                        setFocus($('#batas_kemiripan_pendaftaran_wajah'));
                      }
                    )==false) return false;

    if (cekAlertAngkaValid(batas_kemiripan_absen_wajah_otomatis,0,100,0,"{{ trans('all.bataskemiripanabsenwajahotomatis') }}",
                      function() {
                        aktifkanTombol();
                        setFocus($('#batas_kemiripan_pendaftaran_wajah'));
                      }
                    )==false) return false;

    if (cekAlertAngkaValid(batas_kemiripan_konfirmasi_absen_wajah_otomatis,0,100,0,"{{ trans('all.bataskemiripankonfirmasiabsenwajahotomatis') }}",
                      function() {
                        aktifkanTombol();
                        setFocus($('#batas_kemiripan_pendaftaran_wajah'));
                      }
                    )==false) return false;
    
    if (cekAlertAngkaValid(batas_konfirmasi_absen,0,100,0,"{{ trans('all.bataskonfirmasiabsen') }}",
                      function() {
                        aktifkanTombol();
                        setFocus($('#batas_konfirmasi_absen'));
                      }
                    )==false) return false;

    if (cekAlertAngkaValid(toleransi_waktu_server,0,99999,0,"{{ trans('all.toleransiperbedaanwaktuserver') }}",
                      function() {
                        aktifkanTombol();
                        setFocus($('#batas_konfirmasi_absen'));
                      }
                    )==false) return false;

    if (cekAlertAngkaValid(toleransi_jarak_gps,0,99999,0,"{{ trans('all.toleransijarakgps') }}",
                      function() {
                        aktifkanTombol();
                        setFocus($('#toleransi_jarak_gps'));
                      }
                    )==false) return false;

    if (cekAlertAngkaValid(trackerintervaldefault,0,99999,0,"{{ trans('all.trackerintervaldefault') }}",
                      function() {
                        aktifkanTombol();
                        setFocus($('#trackerintervaldefault'));
                      }
                    )==false) return false;

    if (cekAlertAngkaValid(employee_tracker_lamashiftberakhir,0,99999,0,"{{ trans('all.trackerlamashiftberakhir') }}",
                      function() {
                        aktifkanTombol();
                        setFocus($('#trackerintervaldefault'));
                      }
                    )==false) return false;

    if(end_of_day == ""){
      alertWarning("{{ trans('all.perhitunganhariberakhirkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#end_of_day'));
            });
      return false;
    }

    if($("#mesin_polapengaman_pakai").prop('checked')){
      if (mesin_polapengaman == "") {
        alertWarning("{{ trans('all.polapengamanmesinkosong') }}",
                function () {
                  aktifkanTombol();
                });
        return false;
      }
    }

    if($("#mesin_deteksiekspresi").prop('checked')){
      if (mesin_deteksiekspresi_batas == "") {
        alertWarning("{{ trans('all.batasdeteksiekspresikosong') }}",
                function () {
                  aktifkanTombol();
                });
        return false;
      }
    }
  }

  function givePotoPegawai(){

      $('#givepoto').attr( 'data-loading', '' );
      $('#givepoto').attr('disabled', 'disabled');
      $('#submit').attr('disabled', 'disabled');
      $('#aturmesin').attr('disabled', 'disabled');
      $('#clear').attr('disabled', 'disabled');

      alertConfirmNotClose('{{ trans('all.pegawaiygtidakmemilikifotoakanmenggunakanfacesamplesebagaifoto') }}',
        function(){
            $.ajax({
                type: "GET",
                url: '{{ url('pengaturan/givefotopegawai') }}',
                data: '',
                cache: false,
                success: function (html) {
                    aktifkanTombol();
                    $('#givepoto').removeAttr('data-loading');
                    $('#givepoto').removeAttr('disabled');
                    $('#aturmesin').removeAttr('disabled');
                    if (html == 'ok') {
                        alertSuccess('{{ trans('all.prosesselesai') }}');
                    }else{
                        alertError(html);
                    }
                }
            });
            return false;
        },
        function(){
            aktifkanTombol();
            $('#givepoto').removeAttr('data-loading');
            $('#givepoto').removeAttr('disabled');
            $('#aturmesin').removeAttr('disabled');
            return false;
        },
        '{{ trans('all.lanjut') }}','{{ trans('all.batal') }}');
  }

  function dataposting(){
      if($('#cekkuncidataposting').prop('checked')){
          $('.tr_kuncidatasebelumtanggal').css('display','');
      }else{
          $('.tr_kuncidatasebelumtanggal').css('display','none');
          $("#kuncidatasebelumtanggal").val('');
          $("#kuncidatasebelumtanggal_jam").val('');
      }
  }
  </script>
  <style>
  .spanmesin{
    cursor: pointer;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.umum') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.pengaturan') }}</li>
        <li class="active"><strong>{{ trans('all.umum') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content p-md">
            @if(count($pengaturan) > 0)
              @foreach($pengaturan as $key)
                <form method="post" id='myform' action="{{ url('/pengaturan/umum') }}" onsubmit="return validasi()">
                  {{ csrf_field() }}
                  <input type="hidden" name="mesin_polapengaman" id="mesin_polapengaman" value="{{ $key->mesin_polapengaman }}">
                  <table cellpadding="0" width='100%' cellspacing="0" border="0">
                    <tr>
                      <td><u><b>{{ trans('all.kemiripanwajah') }}</b></u></td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>
                              {{ trans('all.bataskemiripanwajah') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_bataskemiripanwajah') }}" class="fa fa-info-circle"></i>
                              {{--<br><span style="font-size:8px">--}}
                                {{--berfungsi untuk aplikasi datacapture--}}
                                {{--semakin tinggi nilai yang diberikan semakin tingig tingkat keamanan pada saat proses validasi wajah--}}
                              {{--</span>--}}
                            </td>
                            <td style='padding:5px;float:left'>
                              <input autofocus autocomplete='off' onkeypress="return onlyNumber(0,event)" size='1' type='text' value='{{ $key->batas_kemiripan_absen_wajah }}' name='batas_kemiripan_absen_wajah' class='form-control' id='batas_kemiripan_absen_wajah' maxlength="3">
                            </td>
                            <td style="float:left;margin-top:12px">%</td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.konfirmasibataskemiripanwajah') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_konfirmasibataskemiripanwajah') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <input autocomplete='off' onkeypress="return onlyNumber(0,event)" size='1' type='text' value='{{ $key->batas_kemiripan_konfirmasi_absen_wajah}}' name='batas_kemiripan_konfirmasi_absen_wajah' class='form-control' id='batas_kemiripan_konfirmasi_absen_wajah' maxlength="3">
                            </td>
                            <td style="float:left;margin-top:12px">%</td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.bataskemiripanwajahwaktupendaftaran') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_bataskemiripanwajahwaktupendaftaran') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <input autocomplete='off' onkeypress="return onlyNumber(0,event)" size='1' type='text' value='{{ $key->batas_kemiripan_pendaftaran_wajah }}' name='batas_kemiripan_pendaftaran_wajah' class='form-control' id='batas_kemiripan_pendaftaran_wajah' maxlength="3">
                            </td>
                            <td style="float:left;margin-top:12px">%</td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td><u><b>{{ trans('all.kemiripanwajahotomatis') }}</b></u></td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.gunakanabsenwajahotomatis') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_gunakanabsenwajahotomatis') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch">
                                <input type="checkbox" class="onoffswitch-checkbox" name="gunakan_absen_wajah_otomatis" @if($key->gunakan_absen_wajah_otomatis == 'y') checked @endif id="gunakan_absen_wajah_otomatis"  onchange="return checkboxclick('gunakan_absen_wajah_otomatis')">
                                <label class="onoffswitch-label" for="gunakan_absen_wajah_otomatis">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.bataskemiripanabsenwajahotomatis') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_bataskemiripanabsenwajahotomatis') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <input autocomplete='off' onkeypress="return onlyNumber(0,event)" size='1' type='text' value='{{ $key->batas_kemiripan_absen_wajah_otomatis }}' name='batas_kemiripan_absen_wajah_otomatis' class='form-control' id='batas_kemiripan_absen_wajah_otomatis' maxlength="3">
                            </td>
                            <td style="float:left;margin-top:12px">%</td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.bataskemiripankonfirmasiabsenwajahotomatis') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_bataskemiripankonfirmasiabsenwajahotomatis') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <input autocomplete='off' onkeypress="return onlyNumber(0,event)" size='1' type='text' value='{{ $key->batas_kemiripan_konfirmasi_absen_wajah_otomatis }}' name='batas_kemiripan_konfirmasi_absen_wajah_otomatis' class='form-control' id='batas_kemiripan_konfirmasi_absen_wajah_otomatis' maxlength="3">
                            </td>
                            <td style="float:left;margin-top:12px">%</td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td><u><b>{{ trans('all.rekamdata') }}</b></u></td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>
                              {{ trans('all.opsigetid') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_opsigetid') }}" class="fa fa-info-circle"></i>
                            </td>
                            <td style='padding:5px;float:left' colspan="2">
                              <select name="mesin_getid_opsi" class="form-control">
                                <option value="pin" @if($key->mesin_getid_opsi == 'pin') selected @endif>{{ trans('all.pin') }}</option>
                                <option value="daftar" @if($key->mesin_getid_opsi == 'daftar') selected @endif>{{ trans('all.daftar') }}</option>
                                <option value="otomatis" @if($key->mesin_getid_opsi == 'otomatis') selected @endif>{{ trans('all.otomatis') }}</option>
                              </select>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>
                              {{ trans('all.ijinkanpendaftaran').' '.trans('all.mesin') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_bataskemiripankonfirmasiabsenwajahotomatis') }}" class="fa fa-info-circle"></i>
                              @if($totalselectedmesin != 0)
                                <br><span style="font-size:8px">{{ $totalselectedmesin.' '.trans('all.mesinyangdiijinpendaftaran') }}</span>
                              @endif
                            </td>
                            <td style='padding:5px;float:left' colspan="2">
                              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalmesin"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</button>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.absenharusdenganalasan') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_absenharusdenganalasan') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left' colspan="2">
                              <div class="onoffswitch">
                                <input type="checkbox" class="onoffswitch-checkbox" name="absen_harus_dengan_alasan" @if($key->absen_harus_dengan_alasan == 'y') checked @endif id="absen_harus_dengan_alasan">
                                <label class="onoffswitch-label" for="absen_harus_dengan_alasan">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.toleransiperbedaanwaktuserver') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_toleransiperbedaanwaktuserver') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left' colspan="2">
                              <input autofocus autocomplete='off' onkeypress="return onlyNumber(0,event)" size='3' type='text' value='{{ $key->toleransi_waktu_server }}' name='toleransi_waktu_server' class='form-control' id='toleransi_waktu_server' maxlength="3">
                            </td>
                            <td style="float:left;margin-top:12px">{{ trans('all.detik') }}</td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.gpsharusaktif') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_gpsharusaktif') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left' colspan="2">
                              <div class="onoffswitch">
                                <input type="checkbox" class="onoffswitch-checkbox" name="gps_harus_aktif" @if($key->gps_harus_aktif == 'y') checked @endif id="gps_harus_aktif">
                                <label class="onoffswitch-label" for="gps_harus_aktif">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.gpsperbolehkanabsendiluararea') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_gpsperbolehkanabsendiluararea') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left' colspan="2">
                              <div class="onoffswitch">
                                <input type="checkbox" class="onoffswitch-checkbox" name="gps_perbolehkan_absen_diluar_area" @if($key->gps_perbolehkan_absen_diluar_area == 'y') checked @endif id="gps_perbolehkan_absen_diluar_area">
                                <label class="onoffswitch-label" for="gps_perbolehkan_absen_diluar_area">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.toleransijarakgps') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_toleransijarakgps') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <input autocomplete='off' onkeypress="return onlyNumber(0,event)" size='3' type='text' value='{{ $key->toleransi_jarak_gps }}' name='toleransi_jarak_gps' class='form-control' id='toleransi_jarak_gps' maxlength="3">
                            </td>
                            <td style="float:left;margin-top:12px">{{ trans('all.meter') }}</td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.tampilkanflexytime') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_tampilkanflexytime') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left' colspan="2">
                              <div class="onoffswitch">
                                <input type="checkbox" class="onoffswitch-checkbox" name="tampilkan_flexytime" @if($key->tampilkan_flexytime == 'y') checked @endif id="tampilkan_flexytime">
                                <label class="onoffswitch-label" for="tampilkan_flexytime">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                          <tr style="height:50px;">
                            <td style='padding:5px;'>{{ trans('all.pakaipolapengamanmesin') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_pakaipolapengamanmesin') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left;'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="mesin_polapengaman_pakai" id="mesin_polapengaman_pakai" @if($key->mesin_polapengaman_pakai == 'y') checked @endif onchange="return checkboxclick('mesin_polapengaman_pakai',disabled,'flagsetpolapengaman')">
                                <label class="onoffswitch-label" for="mesin_polapengaman_pakai">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                            <td class="flagsetpolapengaman" id="flagsetpolapengaman" @if($key->mesin_polapengaman_pakai == 'y') style='padding:5px;float:left' @else style='padding:5px;float:left;display:none' @endif>
                              <button type="button" id="tombolsetpolapengaman" class="btn btn-primary" data-toggle="modal" data-target="#modalpolapengaman">{{ trans('all.setpolapengamanmesin') }}</button>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.deteksiekspresi') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_deteksiekspresi') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="mesin_deteksiekspresi" id="mesin_deteksiekspresi" @if($key->mesin_deteksiekspresi == 'y') checked @endif onchange="return checkboxclick('mesin_deteksiekspresi',disabled,'flagsetdeteksikedip')">
                                <label class="onoffswitch-label" for="mesin_deteksiekspresi">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                          <tr class="flagsetdeteksikedip" @if($key->mesin_deteksiekspresi == 't') style="display:none" @endif>
                            <td style='padding:5px;'>{{ trans('all.batasdeteksiekspresi') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_batasdeteksiekspresi') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <input autocomplete='off' onkeypress="return onlyNumber(0,event)" size='1' type='text' value='{{ $key->mesin_deteksiekspresi_batas }}' name='mesin_deteksiekspresi_batas' class='form-control' id='mesin_deteksiekspresi_batas' maxlength="4">
                            </td>
                            <td style="float:left;margin-top:12px">%</td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.tampilportal') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_tampilportal') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="mesin_tampilportal" id="mesin_tampilportal" @if($key->mesin_tampilportal == 'y') checked @endif onchange="return checkboxclick('mesin_tampilportal')">
                                <label class="onoffswitch-label" for="mesin_tampilportal">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.tampillatarpeta') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_tampillatarpeta') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="mesin_tampillatarpeta" id="mesin_tampillatarpeta" @if($key->mesin_tampillatarpeta == 'y') checked @endif onchange="return checkboxclick('mesin_tampillatarpeta')">
                                <label class="onoffswitch-label" for="mesin_tampillatarpeta">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style='padding:5px;'><u><b>{{ trans('all.aplikasipegawai') }}</b></u></td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.ijinkantukarshift') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_ijinkantukarshift') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="employee_ijinkantukarshift" id="employee_ijinkantukarshift" @if($key->employee_ijinkantukarshift == 'y') checked @endif onchange="return checkboxclick('employee_ijinkantukarshift')">
                                <label class="onoffswitch-label" for="employee_ijinkantukarshift">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.ijinkanpengajuanlembur') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_ijinkanpengajuanlembur') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="employee_ijinkanpengajuanlembur" id="employee_ijinkanpengajuanlembur" @if($key->employee_ijinkanpengajuanlembur == 'y') checked @endif onchange="return checkboxclick('employee_ijinkanpengajuanlembur')">
                                <label class="onoffswitch-label" for="employee_ijinkanpengajuanlembur">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.ijinkanpengajuanlupaabsen') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_ijinkanpengajuanlupaabsen') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="employee_ijinkanpengajuanlupaabsen" id="employee_ijinkanpengajuanlupaabsen" @if($key->employee_ijinkanpengajuanlupaabsen == 'y') checked @endif onchange="return checkboxclick('employee_ijinkanpengajuanlupaabsen')">
                                <label class="onoffswitch-label" for="employee_ijinkanpengajuanlupaabsen">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.ijinkanpengajuantidakterlambat') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_ijinkanpengajuantidakterlambat') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="employee_ijinkanpengajuantidakterlambat" id="employee_ijinkanpengajuantidakterlambat" @if($key->employee_ijinkanpengajuantidakterlambat == 'y') checked @endif onchange="return checkboxclick('employee_ijinkanpengajuantidakterlambat')">
                                <label class="onoffswitch-label" for="employee_ijinkanpengajuantidakterlambat">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.ijinkanpengajuantidakpulangawal') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_ijinkanpengajuantidakpulangawal') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="employee_ijinkanpengajuantidakpulangawal" id="employee_ijinkanpengajuantidakpulangawal" @if($key->employee_ijinkanpengajuantidakpulangawal == 'y') checked @endif onchange="return checkboxclick('employee_ijinkanpengajuantidakpulangawal')">
                                <label class="onoffswitch-label" for="employee_ijinkanpengajuantidakpulangawal">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.ijinkangantifotoprofile') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_ijinkangantifotoprofile') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="employee_ijinkangantifotoprofile" id="employee_ijinkangantifotoprofile" @if($key->employee_ijinkangantifotoprofile == 'y') checked @endif onchange="return checkboxclick('employee_ijinkangantifotoprofile')">
                                <label class="onoffswitch-label" for="employee_ijinkangantifotoprofile">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.perlakuanlembur') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_perlakuanlembur') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <select name="default_perlakuanlembur" id="default_perlakuanlembur" class="form-control">
                                <option value="tanpalembur" @if($key->default_perlakuanlembur == 'tanpalembur') selected @endif>{{ trans('all.tanpalembur') }}</option>
                                <option value="konfirmasi" @if($key->default_perlakuanlembur == 'konfirmasi') selected @endif>{{ trans('all.konfirmasi') }}</option>
                                <option value="lembur" @if($key->default_perlakuanlembur == 'lembur') selected @endif>{{ trans('all.lembur') }}</option>
                              </select>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.gunakandefaulttracker') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_gunakandefaulttracker') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="gunakandefaulttracker" id="gunakandefaulttracker" @if($key->employee_tracker_gunakandefault == 'y') checked @endif onchange="return checkboxclick('gunakandefaulttracker')">
                                <label class="onoffswitch-label" for="gunakandefaulttracker">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.trackerintervaldefault') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_trackerintervaldefault') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
{{--                              <input autocomplete='off' onkeypress="return onlyNumber(0,event)" size='3' type='text' value='{{ round($key->employee_tracker_intervaldefault/60,2) }}' name='trackerintervaldefault' class='form-control' id='trackerintervaldefault' maxlength="3">--}}
                              <input autocomplete='off' onkeypress="return onlyNumber(0,event)" size='3' type='text' value='{{ $key->employee_tracker_intervaldefault }}' name='trackerintervaldefault' class='form-control' id='trackerintervaldefault' maxlength="3">
                            </td>
                            <td>{{ trans('all.menit') }}</td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.trackerlamashiftberakhir') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_trackerlamashiftberakhir') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <input autocomplete='off' onkeypress="return onlyNumber(0,event)" size='3' type='text' value='{{ $key->employee_tracker_lamashiftberakhir }}' name='employee_tracker_lamashiftberakhir' class='form-control' id='employee_tracker_lamashiftberakhir' maxlength="3">
                            </td>
                            <td>{{ ucfirst(trans('all.jam'))  }}</td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.ijinkansambungdatacapture') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_ijinkansambungdatacapture') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch" style="margin-top:8px">
                                <input type="checkbox" class="onoffswitch-checkbox" value="y" name="employee_ijinkansambungdatacapture" id="employee_ijinkansambungdatacapture" @if($key->employee_ijinkansambungdatacapture == 'y') checked @endif onchange="return checkboxclick('employee_ijinkansambungdatacapture')">
                                <label class="onoffswitch-label" for="employee_ijinkansambungdatacapture">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                      <tr>
                        <td style="padding-left:15px;">
                          <table>
                            <tr>
                              <td style='width:300px;padding:5px;'>{{ trans('all.gunakanaktivitas') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_gunakanaktivitas') }}" class="fa fa-info-circle"></i></td>
                              <td style='padding:5px;float:left'>
                                <div class="onoffswitch" style="margin-top:8px">
                                  <input type="checkbox" class="onoffswitch-checkbox" value="y" name="employee_gunakanaktivitas" id="employee_gunakanaktivitas" @if($key->employee_gunakanaktivitas == 'y') checked @endif onchange="return checkboxclick('employee_gunakanaktivitas')">
                                  <label class="onoffswitch-label" for="employee_gunakanaktivitas">
                                    <span class="onoffswitch-inner"></span>
                                    <span class="onoffswitch-switch"></span>
                                  </label>
                                </div>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    <tr>
                      <td><u><b>{{ trans('all.pemindai') }}</b></u></td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.rfid') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_rfid') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;'>
                              <div class="onoffswitch">
                                <input type="checkbox" class="onoffswitch-checkbox" name="pemindai_rfid" @if($key->pemindai_rfid == 'y') checked @endif id="pemindai_rfid">
                                <label class="onoffswitch-label" for="pemindai_rfid">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.nfc') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_nfc') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;'>
                              <div class="onoffswitch">
                                <input type="checkbox" class="onoffswitch-checkbox" name="pemindai_nfc" @if($key->pemindai_nfc == 'y') checked @endif id="pemindai_nfc">
                                <label class="onoffswitch-label" for="pemindai_nfc">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.barcode') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_barcode') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <div class="onoffswitch">
                                <input type="checkbox" class="onoffswitch-checkbox" name="pemindai_barcode" @if($key->pemindai_barcode == 'y') checked @endif id="pemindai_barcode">
                                <label class="onoffswitch-label" for="pemindai_barcode">
                                  <span class="onoffswitch-inner"></span>
                                  <span class="onoffswitch-switch"></span>
                                </label>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td><u><b>{{ trans('all.lainnya') }}</b></u></td>
                    </tr>
                    <tr>
                      <td style="padding-left:15px;">
                        <table>
                          <tr>
                            <td style='width:300px;padding:5px;'>{{ trans('all.bataskonfirmasiabsen') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_bataskonfirmasiabsen') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <input autofocus autocomplete='off' onkeypress="return onlyNumber(0,event)" size='1' type='text' value='{{ $key->batas_konfirmasi_absen }}' name='batas_konfirmasi_absen' class='form-control' id='batas_konfirmasi_absen' maxlength="3">
                            </td>
                            <td style="float:left;margin-top:12px">{{ trans('all.hari') }}</td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.setelandasarkonfirmasiabsen') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_setelandasarkonfirmasiabsen') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <select id="default_konfirmasi_absen" name="default_konfirmasi_absen" class="form-control">
                                <option value="v" @if($key->default_konfirmasi_absen == 'v') selected @endif>{{ trans('all.valid') }}</option>
                                <option value="na" @if($key->default_konfirmasi_absen == 'na') selected @endif>{{ trans('all.ditolak') }}</option>
                              </select>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.zonawaktu') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_zonawaktu') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <select id="utc" name="utc" class="form-control">
                                <option value="-12:00" @if($key->utc == "-12:00") selected @endif>-12:00</option>
                                <option value="-11:30" @if($key->utc == "-11:30") selected @endif>-11:30</option>
                                <option value="-11:00" @if($key->utc == "-11:00") selected @endif>-11:00</option>
                                <option value="-10:30" @if($key->utc == "-10:30") selected @endif>-10:30</option>
                                <option value="-10:00" @if($key->utc == "-10:00") selected @endif>-10:00</option>
                                <option value="-09:30" @if($key->utc == "-09:30") selected @endif>-09:30</option>
                                <option value="-09:00" @if($key->utc == "-09:00") selected @endif>-09:00</option>
                                <option value="-08:30" @if($key->utc == "-08:30") selected @endif>-08:30</option>
                                <option value="-08:00" @if($key->utc == "-08:00") selected @endif>-08:00</option>
                                <option value="-07:30" @if($key->utc == "-07:30") selected @endif>-07:30</option>
                                <option value="-07:00" @if($key->utc == "-07:00") selected @endif>-07:00</option>
                                <option value="-06:30" @if($key->utc == "-06:30") selected @endif>-06:30</option>
                                <option value="-06:00" @if($key->utc == "-06:00") selected @endif>-06:00</option>
                                <option value="-05:30" @if($key->utc == "-05:30") selected @endif>-05:30</option>
                                <option value="-05:00" @if($key->utc == "-05:00") selected @endif>-05:00</option>
                                <option value="-04:30" @if($key->utc == "-04:30") selected @endif>-04:30</option>
                                <option value="-04:00" @if($key->utc == "-04:00") selected @endif>-04:00</option>
                                <option value="-03:30" @if($key->utc == "-03:30") selected @endif>-03:30</option>
                                <option value="-03:00" @if($key->utc == "-03:00") selected @endif>-03:00</option>
                                <option value="-02:30" @if($key->utc == "-02:30") selected @endif>-02:30</option>
                                <option value="-02:00" @if($key->utc == "-02:00") selected @endif>-02:00</option>
                                <option value="-01:30" @if($key->utc == "-01:30") selected @endif>-01:30</option>
                                <option value="-01:00" @if($key->utc == "-01:00") selected @endif>-01:00</option>
                                <option value="00:00" @if($key->utc == "00:00") selected @endif>00:00</option>
                                <option value="+01:00" @if($key->utc == "+01:00") selected @endif>+01:00</option>
                                <option value="+01:30" @if($key->utc == "+01:30") selected @endif>+01:30</option>
                                <option value="+02:00" @if($key->utc == "+02:00") selected @endif>+02:00</option>
                                <option value="+02:30" @if($key->utc == "+02:30") selected @endif>+02:30</option>
                                <option value="+03:00" @if($key->utc == "+03:00") selected @endif>+03:00</option>
                                <option value="+03:30" @if($key->utc == "+03:30") selected @endif>+03:30</option>
                                <option value="+04:00" @if($key->utc == "+04:00") selected @endif>+04:00</option>
                                <option value="+04:30" @if($key->utc == "+04:30") selected @endif>+04:30</option>
                                <option value="+05:00" @if($key->utc == "+05:00") selected @endif>+05:00</option>
                                <option value="+05:30" @if($key->utc == "+05:30") selected @endif>+05:30</option>
                                <option value="+06:00" @if($key->utc == "+06:00") selected @endif>+06:00</option>
                                <option value="+06:30" @if($key->utc == "+06:30") selected @endif>+06:30</option>
                                <option value="+07:00" @if($key->utc == "+07:00") selected @endif>+07:00</option>
                                <option value="+07:30" @if($key->utc == "+07:30") selected @endif>+07:30</option>
                                <option value="+08:00" @if($key->utc == "+08:00") selected @endif>+08:00</option>
                                <option value="+08:30" @if($key->utc == "+08:30") selected @endif>+08:30</option>
                                <option value="+09:00" @if($key->utc == "+09:00") selected @endif>+09:00</option>
                                <option value="+09:30" @if($key->utc == "+09:30") selected @endif>+09:30</option>
                                <option value="+10:00" @if($key->utc == "+10:00") selected @endif>+10:00</option>
                                <option value="+10:30" @if($key->utc == "+10:30") selected @endif>+10:30</option>
                                <option value="+11:00" @if($key->utc == "+11:00") selected @endif>+11:00</option>
                                <option value="+11:30" @if($key->utc == "+11:30") selected @endif>+11:30</option>
                                <option value="+12:00" @if($key->utc == "+12:00") selected @endif>+12:00</option>
                              </select>
                            </td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>{{ trans('all.perhitunganhariberakhir') }}&nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_perhitunganhariberakhir') }}" class="fa fa-info-circle"></i></td>
                            <td style='padding:5px;float:left'>
                              <input type="text" class="form-control jam" value='{{ $key->end_of_day }}' size=4 autocomplete="off" placeholder="hh:mm" name="end_of_day" id="end_of_day">
                            </td>
                            <td style="float:left;margin-top:12px">{{ trans('all.format24jam') }}</td>
                          </tr>
                          <tr>
                            <td style='padding:5px;'>
                              <input type="checkbox" onclick="dataposting()" @if($key->kuncidatasebelumtanggal != '') checked @endif id="cekkuncidataposting">&nbsp;&nbsp;
                              <span style="cursor:pointer" id="spankuncidataposting" onclick="spanClick('cekkuncidataposting');dataposting()">{{ trans('all.kuncidataposting') }}</span>
                              &nbsp;&nbsp;<i style="color:#1c84c6" title="{{ trans('all.tooltip_kuncidataposting') }}" class="fa fa-info-circle"></i>
                            </td>
                          </tr>
                          <tr class="tr_kuncidatasebelumtanggal" @if($key->kuncidatasebelumtanggal == '') style="display: none" @endif>
                            <td style='padding:5px;'>{{ trans('all.sebelumtanggal') }}</td>
                            <td style='padding:5px;float:left'>
                              <input type="text" size="12" class="form-control date" value='{{ \App\Utils::convertYmd2Dmy($key->kuncidatasebelumtanggal) }}' autocomplete="off" placeholder="dd/mm/yyyy" name="kuncidatasebelumtanggal" id="kuncidatasebelumtanggal">
                            </td>
                            <td style='padding:5px;float:left'>
                              <input type="text" class="form-control jam" value='{{ $key->kuncidatasebelumtanggal_jam }}' size=7 autocomplete="off" placeholder="hh:mm" name="kuncidatasebelumtanggal_jam" id="kuncidatasebelumtanggal_jam">
                            </td>
                          </tr>
                          {{--<tr>--}}
                            {{--<td style='padding:5px;'>{{ trans('all.kirimsms') }}</td>--}}
                            {{--<td style='padding:5px;float:left'>--}}
                              {{--<div class="onoffswitch">--}}
                                {{--<input type="checkbox" class="onoffswitch-checkbox" name="kirimsms" @if($key->kirimsms == 'y') checked @endif id="kirimsms">--}}
                                {{--<label class="onoffswitch-label" for="kirimsms">--}}
                                  {{--<span class="onoffswitch-inner"></span>--}}
                                  {{--<span class="onoffswitch-switch"></span>--}}
                                {{--</label>--}}
                              {{--</div>--}}
                            {{--</td>--}}
                          {{--</tr>--}}
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding-top: 10px">
                        <button id="givepoto" type="button" onclick="return givePotoPegawai()" class="ladda-button btn btn-success slide-left"><span class="label2">{{ trans('all.fotopegawaimenggunakanfacesample') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                        <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>
                      </td>
                    </tr>
                  </table>

                  <!-- Modal polapengaman-->
                  <div class="modal modalpegawai fade" id="modalpolapengaman" role="dialog" tabindex='-1'>
                    <div class="modal-dialog modal-md">

                      <!-- Modal content-->
                      <div class="modal-content">

                        <div class="modal-header">
                          <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                          <h4 class="modal-title">{{ trans('all.polapengamanmesin') }}</h4>
                        </div>
                        <div class="modal-body" style="max-height:460px;overflow: auto;">
                          <div class="col-md-8">
                            <div id="pattern"></div>
                          </div>
                          <div class="col-md-4" style="padding-left:15px">
                            @if($key->mesin_polapengaman_pakai == 'y')
                              <button class="btn btn-success" id="tunjukanpola"><i class="fa fa-eye"></i>&nbsp;&nbsp;{{ trans('all.tunjukanpola') }}</button><br><br>
                            @endif
                            <button class="btn btn-success" id="setulangpola"><i class="fa fa-refresh"></i>&nbsp;&nbsp;{{ trans('all.setulangpola') }}</button>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <table>
                            <tr>
                              <td style="padding:0px;">
                                <button class="btn btn-primary" data-dismiss="modal"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</button>
                              </td>
                            </tr>
                          </table>
                        </div>

                      </div>
                    </div>
                  </div>
                  <!-- Modal polapengaman-->

                </form>
              @endforeach
            @else
              <center>{{ trans('all.perusahaanbelumdipilih') }}</center>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal mesin-->
  <div class="modal fade" id="modalmesin" role="dialog" tabindex='-1'>
    <div class="modal-dialog modal-sm">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" id='closemodal2' data-dismiss="modal">&times;</button>
          <h4 class="modal-title">{{ trans('all.ijinkanpendaftaran').' '.trans('all.mesin') }}</h4>
        </div>
        <form method="post" id='myform' action="{{ url('/pengaturan/umum') }}" onsubmit="return validasi()">
          {{ csrf_field() }}
          <input type="hidden" name="ijinkanpendaftaranmesin" value="">
          <div class="modal-body" style="height:480px;overflow: auto;">
            <table>
              <tr>
                <td style="padding:2px">
                  <div class="col-md-10">
                    <span class="spanmesin" onclick="spanallclick('headerattrmesin','ijinkanpendaftaranpopup')"><u><b>{{ trans('all.rekamdata') }}</b></u></span>
                  </div>
                  <div class="col-md-2">
                    <input value="" @if($totalselectedmesin == count($mesin)) checked @endif type="checkbox" onclick="checkboxallclick('headerattrmesin','ijinkanpendaftaranpopup');checkAllAttr('ijinkanpendaftaranpopup','headerattrmesin')" id="headerattrmesin">
                  </div>
                  <br>
                </td>
              </tr>
              @foreach($mesin as $keys)
                <tr>
                  <td style="padding:2px">
                    <div class="col-md-10">
                      <span class="spanmesin" id="attrpopup{{ $keys->id }}" onclick="spanClick('ijinkanpendaftaranpopup{{ $keys->id }}')">{{ $keys->nama }}</span>
                    </div>
                    <div class="col-md-2">
                      <input onchange="return checkAllAttr('ijinkanpendaftaranpopup','headerattrmesin')" name="ijinkanpendaftaran[]" value="{{ $keys->id }}" type="checkbox" @if($keys->ijinkanpendaftaran == 'y') checked @endif class="ijinkanpendaftaranpopup" id="ijinkanpendaftaranpopup{{ $keys->id }}" value="{{ $keys->id }}">
                    </div>
                    <br>
                  </td>
                </tr>
              @endforeach
            </table>
          </div>
          <div class="modal-footer">
            <table width="100%">
              <tr>
                <td style="padding:0px;align:right">
                  <button id="aturmesin" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</span> <span class="spinner"></span></button>
                </td>
              </tr>
            </table>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Modal mesin-->

  <script>
  var lock = '';
  $(function(){

    for(var i=0;i<{{count($mesin)}};i++){
      var elem = document.querySelector('.js-switch-mesin-'+i);
      new Switchery(elem, { color: '#1AB394' });
    }

    //var lock = new PatternLock("#pattern");
    lock = new PatternLock("#pattern",{
      enableSetPattern : true,
      /*mapper: function(idx){
        console.log(idx);
      }*/
      onDraw:function(pattern){
        //do something with pattern
        if(pattern.length < 4){
          lock.error();
        }else{
          $("#mesin_polapengaman").val(pattern);
        }
      }
    });
    /*lock.checkForPattern('12369',function(){
      alert("You unlocked your app");
    },function(){
      //alert("Pattern is not correct");
    });*/
    $('#tunjukanpola').click(function(){
      lock.setPattern('{{ $key->mesin_polapengaman }}');
      $('#mesin_polapengaman').val('{{ $key->mesin_polapengaman }}');
    });
    $('#setulangpola').click(function(){
      lock.reset();
      $('#mesin_polapengaman').val('');
    });
  });

  function polapengamanmesin(){
    var mesin_polapengaman_pakai = $('#mesin_polapengaman_pakai').val();
    if(mesin_polapengaman_pakai == 'y'){
      $('#flagsetpolapengaman').css('display','');
    }else{
      $('#flagsetpolapengaman').css('display','none');
      $('#mesin_polapengaman').val('');
    }
  }
  </script>
@stop