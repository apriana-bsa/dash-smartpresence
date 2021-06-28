@extends('layouts.master')
@section('title', trans('all.jamkerjapegawai'))
@section('content')
  
  @if(Session::get('message'))
    <script>
    $(document).ready(function() {
      setTimeout(function() {
          toastr.options = {
              closeButton: true,
              progressBar: true,
              timeOut: 5000,
              extendedTimeOut: 5000,
              positionClass: 'toast-bottom-right'
          };
          toastr.success('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
      }, 500);
    });
    </script>
  @endif
  <script>
  var dtable = '';
  var dtable2 = '';
  var _token = '{!! csrf_token() !!}';
  var urlkiri = '{!! url("datainduk/absensi/jamkerjapegawai/pegawai") !!}';
  var urlkanan = '{!! url("datainduk/absensi/jamkerjapegawai/pegawaijamkerja") !!}';

  function ceksemua(ke){
      if(ke === 1) {
          if ($("#ceksemuapegawai").prop("checked")) {
              $(".cekpegawai").prop("checked", true);
          } else {
              $(".cekpegawai").prop("checked", false);
          }
      }else if(ke === 2){
          if ($("#ceksemuapegawai_"+ke).prop("checked")) {
              $(".cekpegawai_"+ke).prop("checked", true);
          } else {
              $(".cekpegawai_"+ke).prop("checked", false);
          }
          tampiltombolhapus();
      }
  }

  function cariJamKerja(){
      var jamkerja = $('#jamkerja').val();
      var berlakumulai = $('#berlakumulai').val();
      if(jamkerja === ""){
          alertWarning("{{ trans('all.jamkerjakosong') }}",
                  function() {
                      aktifkanTombol();
                      setFocus($('#jamkerja'));
                  });
          return false;
      }
      if(berlakumulai === ""){
          alertWarning("{{ trans('all.berlakumulaikosong') }}",
                  function() {
                      aktifkanTombol();
                      setFocus($('#berlakumulai'));
                  });
          return false;
      }
      //jalankan ajax terus load datatable
      $.ajax({
          type: "GET",
          url: '{{ url('datainduk/absensi/getpegawaijamkerja') }}/'+jamkerja+'/'+berlakumulai.replace(new RegExp('/', 'g'), '-'),
          data: '',
          cache: false,
          success: function(resp){
              $('#buttonhapusjamkerja').attr('jenisjamkerja', resp[0]);
              $('#setulang').css('display', '');
              $('#closepilihjamkerja').trigger('click');
              $('#alertpilihjamkerja').css('display', '').html('').html(resp[1]);
              $('#alertpilihjamkerja');
              dtable2.ajax.url(urlkanan).load();
          }
      });
      @if($onboarding)
       $('[data-toggle="popover_jam_ditentukan"]').popover('show')
       $('[data-toggle="popover_klik_pegawai"]').popover('show')
       $('[data-toggle="modal"]').popover('hide')
      @endif
      return false;
  }

  function setJamKerja(){
      var elements = document.getElementsByClassName("cekpegawai");
      var s = '';
      for(var i=0; i<elements.length; i++) {
          if (elements[i].checked===true) {
              s += '|'+elements[i].id;
          }
      }

      if (s==='') {
          alertWarning('{{ trans('all.pegawaikosong') }}');
          return false;
      }

      //cek apakah jamkerja sudah ada
      var jamkerja = $('#jamkerja').val();
      if(jamkerja === "") {
          alertWarning("{{ trans('all.andabelummenentukanperiodedanjamkerja') }}",
              function() {
                  aktifkanTombol();
              });
          return false;
      }

      s = s.substring(1);
      //jalankan ajax terus load datatable
      $.ajax({
          type: "GET",
          url: '{{ url('generatecsrftoken') }}',
          data: '',
          cache: false,
          success: function (token) {
              var dataString = "idpegawai=" + encodeURIComponent(s)+"&_token="+token;
              $.ajax({
                  type: "POST",
                  url: "{!! url("datainduk/absensi/jamkerjapegawai/submit") !!}",
                  data: dataString,
                  cache: false,
                  success: function (resp) {
                      if (resp['msg'] !== "") {
                          alertError(resp['msg']);
                      } else {
                          @if($onboarding)
                          $('[data-toggle="popover_jam_ditambah"]').popover('show')
                          $('[data-toggle="popover_jam_ditentukan"]').popover('hide')
                          $('[data-toggle="popover_klik_pegawai"]').popover('hide')
                          $('[data-toggle="popover_centang"]').popover('hide')
                          $('#buttonfilter').popover('hide')
                          $('#pilihjam').popover('hide')
                          @endif
                      }
                      _token = resp['token'];
                      dtable.ajax.url(urlkiri).load();
                      dtable2.ajax.url(urlkanan).load();
                      setTimeout(function(){
                          $("#ceksemuapegawai").prop("checked", false);
                      },200);
                      if (resp['msg'] == "") {
                        @if(Session::get('onboardingstep')==4 && $onboarding)
                            $('[data-toggle="popover-device"]').popover('show')
                            $('[data-toggle="popover-jamkerjapegawai"]').popover('hide')
                            $('[data-toggle="popover-device"]').children().attr("href", '/datainduk/absensi/mesin/create?onboarding=true');
                        @endif
                      }         
                  }
              });
          }
      });
      return false;
  }

  $(function () {
    @if ($onboarding)
    $('#pilihjam').popover({
        placement : 'auto left',
        trigger : 'manual',
    });
    $('[data-toggle="popover_tentukan_jam"]').popover({
        trigger : 'manual',
     });
    $('[data-toggle="popover_jam_ditentukan"]').popover({
        trigger : 'manual',
        placement: "auto top"
    });
    $('[data-toggle="popover_klik_pegawai"]').popover({
        trigger : 'manual',
        placement: "auto top"
    });
    $('[data-toggle="popover_jam_ditambah"]').popover({
        trigger : 'manual',
        placement: "auto top"
    });
    $('#buttonfilter').popover({
        trigger : 'manual',
        placement: "auto right"
    });

    $('#pilihjam').popover('show')
    $('#buttonfilter').popover('show')

    $('[data-toggle="popover_centang"]').popover('show');
    $($('[data-toggle="popover_centang"]').next()).css({"top": "30px", "left": "40px", "display": "block"});
    @endif
             
    $(document).on("click", ".popover .close" , function(){
        $(this).parents('.popover').popover('hide');
    });
  })
    

    function triggerPopover(){
        @if($onboarding)
        $('[data-toggle="popover_tentukan_jam"]').popover('show')
        $($("#form_modalfilter").next()).css({"top": "89px", "left": "-276px", "display": "block"});
        @endif
  }

  function tampiltombolhapus(){
      var elements = document.getElementsByClassName("cekpegawai_2");
      for(var i=0; i<elements.length; i++) {
          if (elements[i].checked===true) {
              $('#buttonhapusjamkerja').css('display', '');
              return;
          }
      }
      $('#buttonhapusjamkerja').css('display', 'none');
  }

  function hapusjamkerja(){
      var elements = document.getElementsByClassName("cekpegawai_2");
      var s = '';
      for(var i=0; i<elements.length; i++) {
          if (elements[i].checked===true) {
              s += '|'+elements[i].id;
          }
      }
      if (s===''){
          alertWarning('{{ trans('all.pegawaikosong') }}');
          return false;
      }
      var jenisjamkerja = $('#buttonhapusjamkerja').attr('jenisjamkerja');
      var msg = '{{ trans('all.alerthapusterpilih') }}';
      if(jenisjamkerja.trim() === 'shift'){
          msg = '{{ trans('all.hapusdatainijugaakanmenghapusdatadijadwalhshift') }}';
      }

      alertConfirm(msg,function(){
          s = s.substring(1);
          //jalankan ajax terus load datatable
          $.ajax({
              type: "GET",
              url: '{{ url('generatecsrftoken') }}',
              data: '',
              cache: false,
              success: function (token) {
                  var dataString = "idpegawai=" + encodeURIComponent(s)+"&_token="+token;
                  $.ajax({
                      type: "POST",
                      url: "{!! url("datainduk/absensi/jamkerjapegawai/hapus") !!}",
                      data: dataString,
                      cache: false,
                      success: function (resp) {
                          if (resp['status'] !== 'ok') {
                              alertError(resp['msg']);
                          }
                          _token = resp['token'];
                          dtable.ajax.url(urlkiri).load();
                          dtable2.ajax.url(urlkanan).load();
                          setTimeout(function(){
                              $("#ceksemuapegawai_2").prop("checked", false);
                              $("#buttonhapusjamkerja").css('display', 'none');
                          },200);
                      }
                  });
              }
          });
          return false;
      });
  }
  </script>
  <style>
  .alert{
      margin-bottom:10px;
  }

  span{
      cursor:pointer;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.jamkerjapegawai') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.jamkerjapegawai') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-lg-12">
            <table>
                <tr>
                    <td valign='top' width=50% style='padding-left:15px;'>
                        <table>
                            <tr>
                                <td>
                                    <button type="button" id="buttonfilter" data-toggle="modal" data-target="#modalFilter" class="btn btn-primary" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><button class="close">&times;</button>{{ trans('onboarding.button_filter') }}</div></div>' data-content='content'><i class="fa fa-bars"></i>&nbsp;&nbsp;{{ trans('all.filter') }}</button>
                                    &nbsp;&nbsp;
                                    <button @if(!Session::has('jamkerjapegawai_idatribut') || Session::get('jamkerjapegawai_atribut') == '') style="display:none" @endif type="button" id="setulang" onclick="return ke('{{ url('datainduk/absensi/jamkerjapegawai/setulang') }}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                                </td>
                            </tr>
                        </table>
                        <p></p>
                        <div id="alertatribut" @if(!Session::has('jamkerjapegawai_atribut') || Session::get('jamkerjapegawai_atribut') == '') style="display:none" @endif class="alert alert-success">
                            @if(Session::has('jamkerjapegawai_atribut')) {!! Session::get('jamkerjapegawai_atribut') !!} @endif
                        </div>
                        <div class="ibox float-e-margins">

                            <div class="ibox-content">
                                <table width=100% class="table datatable table-striped table-condensed table-hover">
                                    <thead data-toggle="popover_centang" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><button class="close">&times;</button>{{ trans('onboarding.centang_nama') }}</div></div>' data-content='content'>
                                    <tr>
                                        <td class='cek'><input type='checkbox' onclick='ceksemua(1)' id='ceksemuapegawai'></td>
                                        <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                                        <td class="pin"><b>{{ trans('all.pin') }}</b></td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </td>
                    <td width='65px' style='text-align:center !important;margin-top:275px;padding:10px;'>
                        <table>
                            <tr>
                                <td>
                                    <button type="button" onclick="return setJamKerja()" class="btn btn-primary" data-toggle="popover_klik_pegawai" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><button class="close">&times;</button>{{ trans('onboarding.klik_pegawai') }}</div></div>' data-content='content'><i class="fa fa-arrow-right"></i></button>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td valign='top' width=50% style='padding-right:15px;'>
                        <table width="100%">
                            <tr>
                                <td>
                                    <button type="button" id="pilihjam" onclick='triggerPopover()' data-toggle="modal" data-target="#modalpilihjamkerja" class="btn btn-primary pull-right" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><button class="close">&times;</button>{{ trans('onboarding.klik_periode') }}</div></div>' data-content='content'>{{ trans('all.tentukanperiodedanjamkerja') }}</button>
                                    <button type="button" style="margin-right:10px;display: none" onclick="hapusjamkerja()" jenisjamkerja="@if(Session::has('jamkerjapegawai_jenisjamkerja')) {{ Session::get('jamkerjapegawai_jenisjamkerja') }} @endif" id="buttonhapusjamkerja" class="btn btn-danger pull-right">{{ trans('all.hapus') }}</button>
                                </td>
                            </tr>
                        </table>
                        <p></p>
                        <div id="alertpilihjamkerja" @if(!Session::has('jamkerjapegawai_keterangan')) style="display:none" @endif class="alert alert-success" data-toggle="popover_jam_ditentukan" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><button class="close">&times;</button>{{ trans('onboarding.jam_kerja_ditentukan') }}</div></div>' data-content='content'>
                            @if(Session::has('jamkerjapegawai_keterangan')) {!! Session::get('jamkerjapegawai_keterangan') !!} @endif
                        </div>
                        <div class="ibox float-e-margins">

                            <div class="ibox-content">
                                <table width=100% class="table datatablejamkerjashift table-striped table-condensed table-hover" data-toggle="popover_jam_ditambah" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><button class="close">&times;</button>{{ trans('onboarding.jam_kerja_ditambah') }}</div></div>' data-content='content'>
                                    <thead>
                                    <tr>
                                        <td class='cek'><input type='checkbox' onclick='ceksemua(2)' id='ceksemuapegawai_2'></td>
                                        <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                                        <td class="pin"><b>{{ trans('all.pin') }}</b></td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
  </div>

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

  <!-- Modal pilihjamkerja-->
  <div class="modal modalpilihjamkerja fade" id="modalpilihjamkerja" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-sm">

          <!-- Modal content-->
          <div class="modal-content" style="width:360px;">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">{{ trans('all.filter').' '.trans('all.atribut') }}</h4>
              </div>
              <form id="form_modalfilter" data-toggle="popover_tentukan_jam" data-placement="auto left" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><button class="close">&times;</button>{{ trans('onboarding.tentukan_jam_kerja') }}</div></div>' data-content='content' method="post" action="{{ url('datainduk/pegawai/facesample') }}" onsubmit="return validasi()">
                  <div class="modal-body body-modal" style="max-height:460px;overflow: auto;">
                      <table>
                          <tr>
                              <td width="100px" style="padding:5px;">{{ trans('all.jamkerja') }}</td>
                              <td style="padding:5px;">
                                  <select id="jamkerja" name="jamkerja" class="form-control">
                                      <option value=""></option>
                                      @if(isset($jamkerja))
                                          @foreach($jamkerja as $key)
                                              @if(Session::has('jamkerjapegawai_idjamkerja'))
                                                  <option value="{{ $key->id }}" @if(Session::get('jamkerjapegawai_idjamkerja') == $key->id) selected @endif>{{ $key->nama }}</option>
                                              @else
                                                  <option value="{{ $key->id }}">{{ $key->nama }}</option>
                                              @endif
                                          @endforeach
                                      @endif
                                  </select>
                              </td>
                          </tr>
                          <tr>
                              <td style="padding:5px;">{{ trans('all.berlakumulai') }}</td>
                              <td style="padding:5px;float:left">
                                  <input type="text" placeholder="dd/mm/yyyy" @if(Session::has('jamkerjapegawai_berlakumulai')) value="{{ Session::get('jamkerjapegawai_berlakumulai') }}" @endif id="berlakumulai" class="form-control date" size="11">
                              </td>
                          </tr>
                      </table>
                  </div>
                  <div class="modal-footer">
                      <table width="100%">
                          <tr>
                              <td style="padding:0px;align:right">
                                  <button class="btn btn-primary" onclick="return cariJamKerja()"><i class="fa fa-search"></i>&nbsp;&nbsp;{{ trans('all.lanjut') }}</button>
                                  <button class="btn btn-primary" id="closepilihjamkerja" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                              </td>
                          </tr>
                      </table>
                  </div>
              </form>
          </div>
      </div>
  </div>
  <!-- Modal pilihjamkerja-->

  <!-- Modal filter-->
  <div class="modal fade" id="modalFilter" role="dialog" tabindex='-1'>
      <div class="modal-dialog @if(count($atribut)<=1) modal-sm @elseif(count($atribut)==2) modal-md @else modal-lg @endif">

          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
{{--                  <h4 class="modal-title">{{ trans('all.filter').' '.trans('all.atribut') }}</h4>--}}
                  <h4 class="modal-title">{{ trans('all.filter') }}</h4>
              </div>
              <div class="modal-body" style="max-height:480px;overflow: auto;">
                  <ul class="nav nav-tabs">
                      <li class="active"><a data-toggle="tab" href="#tab-1">{{ trans('all.atribut') }}</a></li>
                      <li><a data-toggle="tab" href="#tab-2">{{ trans('all.jamkerja') }}</a></li>
                  </ul>
                  <p></p>
                  <div class="tab-content">
                      <div id="tab-1" class="tab-pane active">
                          @if(isset($atribut))
                              @for($i=0;$i<count($atribut);$i++)
                                  <div class="@if(count($atribut)<=1) col-md-12 @elseif(count($atribut)==2) col-md-6 @else col-md-4 @endif">
                                      @if(isset($atribut[$i]['flag']))
                                          <input type="checkbox" class="atributpopup" id="semuaatribut_{{ $atribut[$i]['idatribut'] }}" value="{{ $atribut[$i]['idatribut'] }}" onclick="checkboxallclick('semuaatribut_{{ $atribut[$i]['idatribut'] }}','attr_{{ $atribut[$i]['idatribut'] }}')" @if($atribut[$i]['flag'] == 1) checked @endif>&nbsp;&nbsp;
                                      @else
                                          <input type="checkbox" class="atributpopup" id="semuaatribut_{{ $atribut[$i]['idatribut'] }}" value="{{ $atribut[$i]['idatribut'] }}" onclick="checkboxallclick('semuaatribut_{{ $atribut[$i]['idatribut'] }}','attr_{{ $atribut[$i]['idatribut'] }}')">&nbsp;&nbsp;
                                      @endif
                                      <span style="margin:0" id="spansemuaatribut_{{ $atribut[$i]['idatribut'] }}" onclick="spanallclick('semuaatribut_{{ $atribut[$i]['idatribut'] }}','attr_{{ $atribut[$i]['idatribut'] }}')"><strong>{{ $atribut[$i]['atribut'] }}</strong></span>
                                      <table>
                                          @foreach($atribut[$i]['atributnilai'] as $key)
                                              <tr>
                                                  <td style="width:20px;padding:2px" valign="top">
                                                      <input type="checkbox" @if($key->enable == 0) disabled @endif idatribut="{{ $key->idatribut }}" @if($key->dipilih == 1) checked @endif onchange="return checkAllAttr('attr_{{ $key->idatribut }}','semuaatribut_{{ $key->idatribut }}')" class="atributpopup attr_{{ $key->idatribut }}" name="atribut_{{ $key->idatribut }}" id="atributpopup{{ $key->id }}" value="{{ $key->id }}">
                                                  </td>
                                                  <td style="padding: 2px;">
                                                      <span id="attrpopup_atribut{{ $key->id }}" onclick="spanClick('atributpopup{{ $key->id }}')" atribut="{{ $atribut[$i]['atribut'] }}">{{ $key->nilai }}</span>
                                                  </td>
                                              </tr>
                                          @endforeach
                                      </table>
                                  </div>
                              @endfor
                          @endif
                      </div>
                      <div id="tab-2" class="tab-pane">
                          <table>
                              @foreach($jamkerja as $key)
                                  <tr>
                                      <td style="width:20px;padding:2px" valign="top">
                                          {{--<input type="checkbox" @if($key->enable == 0) disabled @endif idjamkerja="{{ $key->id }}" @if($key->dipilih == 1) checked @endif onchange="return checkAllAttr('attr_{{ $key->id }}','semuajamkerja_{{ $key->idjamkerja }}')" class="jamkerjapopup attr_{{ $key->idjamkerja }}" name="jamkerja_{{ $key->idjamkerja }}" id="jamkerjapopup{{ $key->id }}" value="{{ $key->id }}">--}}
                                          <input type="checkbox" idjamkerja="{{ $key->id }}" @if($key->dipilih == 1) checked @endif onchange="return checkAllAttr('attr_{{ $key->id }}','semuajamkerja_{{ $key->id }}')" class="jamkerjapopup attr_{{ $key->id }}" name="jamkerja_{{ $key->id }}" id="jamkerjapopup{{ $key->id }}" value="{{ $key->id }}">
                                      </td>
                                      <td style="padding: 2px;">
                                          <span id="attrpopup_jamkerja{{ $key->id }}" onclick="spanClick('jamkerjapopup{{ $key->id }}')" jamkerja="{{ $key->nama }}">{{ $key->nama }}</span>
                                      </td>
                                  </tr>
                              @endforeach
                          </table>
                      </div>
                  </div>
              </div>
              <div class="modal-footer">
                  <table width="100%">
                      <tr>
                          <td style="padding:0px;align:right">
                              <button class="btn btn-primary" id="tambahatribut"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.terapkan') }}</button>
                              <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                          </td>
                      </tr>
                  </table>
              </div>
          </div>
      </div>
  </div>
  <!-- Modal filter-->

@stop

@push('scripts')
<script>
$(function() {

    $('#tambahatribut').click(function(){
        var atribut = document.getElementsByClassName("atributpopup");
        var filteratributnilai = "";
        for(var i=0; i<atribut.length; i++) {
            if (atribut[i].id.substring(0,12)!=="semuaatribut") {
                if (document.getElementById("atributpopup"+atribut[i].value).checked) {
                    filteratributnilai += ','+atribut[i].value;
                }
            }
        }
        var jamkerja = document.getElementsByClassName("jamkerjapopup");
        var filterjamkerjanilai = "";
        for(var i=0; i<jamkerja.length; i++) {
            if (jamkerja[i].id.substring(0,12)!=="semuajamkerja") {
                if (document.getElementById("jamkerjapopup"+jamkerja[i].value).checked) {
                    filterjamkerjanilai += ','+jamkerja[i].value;
                }
            }
        }

        var atributnilai = filteratributnilai === '' ? 'o' : filteratributnilai.substring(1);
        var jamkerja = filterjamkerjanilai === '' ? '' : '/'+filterjamkerjanilai.substring(1);
        $("#tutupmodal").trigger("click");

        // setTimeout(function(){
        $.ajax({
            type: "GET",
            url: "{{ url('datainduk/absensi/jamkerjapegawai/aturatribut') }}/"+atributnilai+jamkerja,
            data: '',
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function(resp){
                if(resp['data'] != '') {
                    $('#setulang').css('display', '');
                    $('#alertatribut').css('display', '');
                    $('#alertatribut').html(resp['data']);
                }else{
                    $('#setulang').css('display', 'none');
                    $('#alertatribut').css('display', 'none');
                    $('#alertatribut').html('');
                }
                _token = resp['token'];
                dtable.ajax.url(urlkiri).load();
                dtable2.ajax.url(urlkanan).load();
            }
        });
        return false;
    });

    $('.date').mask("00/00/0000", {clearIfNotMatch: true});

    $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
        $(this).datepicker('hide');
    });

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

    dtable = $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: urlkiri,
            type: "POST",
            data: { _token: function() { return _token } }
        },
        language: lang_datatable,
        columns: [
            { data: 'cekpegawai', name: 'cekpegawai', orderable: false, searchable: false },
            { data: 'nama', name: 'nama' },
            { data: 'pin', name: 'pin' }
        ],
        order: [[1, 'asc']]
    }).on('draw', function(){
        $('[data-toggle="popover_centang"]').popover({
            trigger : 'manual',
        });
    });

    dtable2 = $('.datatablejamkerjashift').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: urlkanan,
            type: "POST",
            data: { _token: function() { return _token } }
        },
        language: lang_datatable,
        columns: [
            { data: 'cekpegawai', name: 'cekpegawai', orderable: false, searchable: false },
            { data: 'nama', name: 'nama' },
            { data: 'pin', name: 'pin' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush