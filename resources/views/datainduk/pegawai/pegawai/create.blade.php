@extends('layouts.master')
@section('title', trans('all.menu_pegawai'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	</style>
	<script>
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
                toastr.warning('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
            }, 500);
        });
    @endif

    @if(Session::get('error'))
        $(function() {
            alertError('{{ Session::get('error') }}');
        });
    @endif

    $(function(){
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });

        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
        $('.time').inputmask( 'h:s' );

        $("#tambahatribut").click(function(){
          var atribut = document.getElementsByClassName("atributpopup");

          $("#tabelatribut").html("");
          $("#atributarea").html("");

          for(var i=0; i<atribut.length; i++) {
            if(document.getElementById("atributpopup"+atribut[i].value).checked){

                //dapatkan idatributnilai dan id atribut
                var nilai = $("#attrpopup_atribut"+atribut[i].value).attr("atribut")+" : "+$("#attrpopup_atribut"+atribut[i].value).html();
                //isi atribut dan idatribut
                $("#atribut"+atribut[i].value).val(atribut[i].value);
                var idatribut = $("#atributpopup"+atribut[i].value).attr("idatribut");
                //buat input nya
                $("#atributarea").append("<input type='hidden' name='atribut[]' value='"+atribut[i].value+"'>" +
                "<input type='hidden' name='idatribut[]' value='"+idatribut+"'>");
                $("#tabelatribut").append("<tr><td>"+nilai+"</td></tr>");
            }
          }
          //$('.atributpopup').prop('checked', false);
          $("#closemodal").trigger("click");
        });

        $("#tambahlokasi").click(function(){
          var lokasi = document.getElementsByClassName("lokasipopup");

          $("#tabellokasi").html("");
          $("#lokasiarea").html("");

          for(var i=0; i<lokasi.length; i++) {
            if(document.getElementById("lokasipopup"+lokasi[i].value).checked){

              //dapatkan idlokasi
              var nilai = $("#attrpopup"+lokasi[i].value).html();
              //isi lokasi
              $("#lokasi"+lokasi[i].value).val(lokasi[i].value);
              //buat input nya
              $("#lokasiarea").append("<input type='hidden' name='lokasi[]' value='"+lokasi[i].value+"'>");
              $("#tabellokasi").append("<tr><td>"+nilai+"</td></tr>");
            }
          }
          //$('.lokasipopup').prop('checked', false);
          $("#closemodal2").trigger("click");
        });

        $("#foto").change(function(){
            readURL(this);
        });

        $('#hapusfoto').click(function(){
          $('#foto').val('');
          $('#imgInp_pegawai').attr('src', '{{ url("foto/pegawai/0") }}');
          $(this).attr('disabled', 'disabled');
          return false;
        });
    });

    function readURL(input) {

        if (input.files && input.files[0]) {
          var reader = new FileReader();

          reader.onload = function (e) {
              $('#imgInp_pegawai').attr('src', e.target.result);
          }

          reader.readAsDataURL(input.files[0]);
          $('#hapusfoto').removeAttr('disabled');
        }
    }

    function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        $('#loading-saver').css('display', '');

        var nama = $("#nama").val();
        var status = $("#status").val();
        var tanggalaktif = $("#tanggalaktif").val();
        var tanggaltidakaktif = $("#tanggaltidakaktif").val();
        var nomorhp = $("#nomorhp").val();
        var pin = $("#pin").val();

        @if(Session::has('conf_webperusahaan'))
        @else
            alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
              $('#loading-saver').css('display', 'none');
            });
            return false;
        @endif

        if(nama == ""){
            $('#loading-saver').css('display', 'none');
            alertWarning("{{ trans('all.namakosong') }}",
            function() {
              aktifkanTombol();
              $('#loading-saver').css('display', 'none');
              setFocus($('#nama'));
            });
            return false;
        }

        if(pin != ''){
            if (cekAlertAngkaValid(pin,0,99999999999999999999,0,"{{ trans('all.pin') }}",
                    function() {
                        aktifkanTombol();
                        $('#loading-saver').css('display', 'none');
                        setFocus($('#pin'));
                    }
                )==false) return false;
        }

        @if($atributvariables != '')
            @foreach($atributvariables as $atributvariable)
                @if($atributvariable->carainputan != '')
                    var inputatributvariable = $('#inputatributvariable_{{ $atributvariable->id }}').val();
                    @foreach(json_decode($atributvariable->carainputan) as $key)
                        @if($key->bolehkosong == 't')
                            if(inputatributvariable == ""){
                                alertWarning("{{ $atributvariable->atribut.' '.trans('all.sa_kosong') }}",
                                    function() {
                                        aktifkanTombol();
                                        setFocus($('#inputatributvariable_{{ $atributvariable->id }}'));
                                    });
                                return false;
                            }
                        @endif
                        @if($key->tipedata == 'number')
                            @if(\App\Utils::getCharFromSeparator($key->range,'-','first') != '')
                                if(inputatributvariable != '') {
                                    if (cekAlertAngkaValid(inputatributvariable,{{ \App\Utils::getCharFromSeparator($key->range,'-','first') }},{{ \App\Utils::getCharFromSeparator($key->range,'-','last') }}, {{ $key->decimal != '' ? $key->decimal : 0 }}, "{{ $atributvariable->atribut }}",
                                        function () {
                                            aktifkanTombol();
                                            setFocus($('#inputatributvariable_{{ $atributvariable->id }}'));
                                        }
                                    ) == false) return false;
                                }
                            @endif
                        @endif
                        @if($key->tipedata == 'text')
                            if(inputatributvariable != '') {
                                @if($key->regex != '')
                                    var regex = new RegExp("{{ $key->regex }}");
                                    if (regex.exec(inputatributvariable) == null) {
                                        {{--alertWarning('{!! trans('all.harusadakarakter').' "'.$key->regex.'"' !!}',--}}
                                        alertWarning("{{ trans('all.regextidakvalid') }}",
                                            function () {
                                                aktifkanTombol();
                                                setFocus($('#inputatributvariable_{{ $atributvariable->id }}'));
                                            });
                                        return false;
                                    }
                                @endif
                            }
                        @endif
                        @if($key->tipedata == 'datetime')
                            if(inputatributvariable != '') {
                                if($('#time').val() == ''){
                                    alertWarning("{{ trans('all.jam').' '.trans('all.sa_kosong') }}",
                                        function () {
                                            aktifkanTombol();
                                            $('#time').val('{{ date('H:m') }}');
                                            setFocus($('#time'));
                                        });
                                    return false;
                                }
                            }
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endif

        if(tanggalaktif == ""){
            $('#loading-saver').css('display', 'none');
            alertWarning("{{ trans('all.tanggalaktifkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#tanggalaktif'));
            });
            return false;
        }

        if(status == ""){
            $('#loading-saver').css('display', 'none');
            alertWarning("{{ trans('all.statuskosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#status'));
                    });
            return false;
        }

        if(status == 't') {
            if (tanggaltidakaktif == "") {
                $('#loading-saver').css('display', 'none');
                alertWarning("{{ trans('all.tanggaltidakaktifkosong') }}",
                        function () {
                            aktifkanTombol();
                            setFocus($('#nomorhp'));
                        });
                return false;
            }
        }

        $.ajax({
            type: "GET",
            url: '{{ url('ceklimitpegawai') }}',
            data: '',
            cache: false,
            success: function(html){
                $('#loading-saver').css('display', 'none');
                if(html['msg'] == 'y'){
                    alertWarning("{{ trans('all.jumlahpegawaimencapaibatasygdiijinkan') }}",
                        function() {
                            aktifkanTombol();
                        });
                    return false;
                }else{
                    $.ajax({
                        type: "GET",
                        url: '{{ url('ceknamapegawai/0') }}/'+nama,
                        data: '',
                        cache: false,
                        success: function(html){
                            $('#loading-saver').css('display', 'none');
                            if(html['msg'] == 'sama'){
                                alertConfirmNotClose('{{ trans('all.namapegawaisudahdigunakan').', '.trans('all.tetapsimpan') }}?',
                                    function(){
                                        aktifkanTombol();
                                        $('#formtambah').attr('onsubmit', '');
                                        $('#submit').trigger('click');
                                        return true;
                                    },
                                    function(){
                                        aktifkanTombol();
                                        return false;
                                    }
                                );
                            }else{
                                aktifkanTombol();
                                $('#formtambah').attr('onsubmit', '');
                                $('#submit').trigger('click');
                            }
                        }
                    });
                }
            }
        });
        return false;
    }

    function pilihstatus(){
        var status = $('#status').val();
        if(status == 'a'){
            $('#flagtanggalnonaktif').css('display', 'none');
        }else if(status == 't'){
            $('#flagtanggalnonaktif').css('display', '');
        }else{
            $('#flagtanggalnonaktif').css('display', 'none');
        }
        return false;
    }

    function aturatribut(){
        @if(count($atribut) > 0)
          $("#buttonmodalatribut").trigger('click');
        @else
          alertWarning("{{ trans('all.nodata') }}");
        @endif
        return false;
    }

    function aturlokasi(){
        @if(count($lokasis) > 0)
          $("#buttonmodallokasi").trigger('click');
        @else
          alertWarning("{{ trans('all.nodata') }}");
        @endif
        return false;
    }
  </script>
  <style>
  span{
    cursor:pointer;
  }
  </style>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_pegawai') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.menu_pegawai') }}</li>
        <li class="active"><strong>{{ trans('all.tambahdata') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">
    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn" style="padding-bottom: 80px">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            @if(Session::has('message')) {{ Session::get('message') }}<br> @endif
          	<form id="formtambah" action="{{ url($onboarding ? 'datainduk/pegawai/pegawai?onboarding=true' : 'datainduk/pegawai/pegawai') }}" method="post" onsubmit="return validasi()" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="100%">
                    <tr>
                      <td>{{ trans('all.foto') }}</td>
                      <td>
                        <img id="imgInp_pegawai" width=120 style='border-radius: 50%;' height=120 src='{{ url("foto/pegawai/0") }}'>
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <table>
                          <tr>
                            <td><input type='file' name='foto' id='foto' class="filestyle"  data-badge="false" data-input="false"></td>
                            <td style='padding-left:5px;'><i disabled class='glyphicon glyphicon-trash btn btn-default' title='{{ trans("all.hapusfoto")}}' name='hapusfoto' id='hapusfoto'></i></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                        <td width="140px">{{ trans('all.nama') }}</td>
                        <td style="float:left">
                            <input type="text" size="50" class="form-control" autofocus autocomplete="off" value="{{ old('nama') }}" name="nama" id="nama" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td width="140px">{{ trans('all.agama') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="agama">
                                <option value=""></option>
                                @if($agama != '')
                                    @foreach($agama as $key)
                                        <option value="{{ $key->id }}" @if($key->id == old('agama')) selected @endif>{{ $key->agama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.pin') }}</td>
                        <td style="float: left;">
                            <input type="text" size="20" class="form-control" onkeypress="return onlyNumber(0,event)" autocomplete="off" value="{{ old('pin') }}" name="pin" id="pin" maxlength="20">
                            <div id="popover-pegawai-pin" ><div data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{ trans('onboarding.form_pegawai_pin') }}</div></div>' data-toggle="popover-pegawai-pin" data-content="content"/></div>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.pemindai') }}</td>
                        <td style="float: left;">
                            <input type="text" size="20" class="form-control" autocomplete="off" value="{{ old('pemindai') }}" name="pemindai" id="pemindai" maxlength="64">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.nomorhp') }}</td>
                        <td style="float:left">
                            <input type="text" size="20" class="form-control" autocomplete="off" value="{{ old('nomorhp') }}" name="nomorhp" id="nomorhp" maxlength="20">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.gunakantracker') }}</td>
                        <td style="float:left">
                            <select class="form-control" id="gunakantracker" name="gunakantracker" onchange="return pilihgunakantracker()">
                                <option value="d">{{ trans('all.default') }}</option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.jamkerja') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="jamkerja">
                                <option value=""></option>
                                @foreach($jamkerja as $key)
                                    <option value="{{ $key->id }}" @if($key->id == old('jamkerja')) selected @endif>{{ $key->nama }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @if($atributvariables != '')
                        @foreach($atributvariables as $atributvariable)
                            <tr>
                                <td>{{ $atributvariable->atribut }}</td>
                                <td style="float:left">
                                    <input type="hidden" value="{{ $atributvariable->id }}" name="av_id[]">
                                    @if($atributvariable->carainputan != '')
                                        @foreach(json_decode($atributvariable->carainputan) as $key)
                                            @if($key->tipedata == 'text')
                                                @if($key->jumlahkarakter > 200)
                                                    <textarea class="form-control" maxlength="{{$key->jumlahkarakter}}" rows="4" cols="100" id="inputatributvariable_{{ $atributvariable->id }}" name="av_value[]"></textarea>
                                                @else
                                                    <input type="text" @if($key->jumlahkarakter > 50) size="100" @endif class="form-control" id="inputatributvariable_{{ $atributvariable->id }}" autocomplete="off" name="av_value[]" maxlength="{{ $key->jumlahkarakter }}">
                                                @endif
                                                <input type="hidden" value="" name="av_valuetime[]">
                                            @elseif($key->tipedata == 'date')
                                                <input type="text" size="11" class="form-control date" id="inputatributvariable_{{ $atributvariable->id }}" placeholder="dd/mm/yyyy" autocomplete="off" name="av_value[]">
                                                <input type="hidden" value="" name="av_valuetime[]">
                                            @elseif($key->tipedata == 'datetime')
                                                <table>
                                                    <tr>
                                                        <td style="padding-left:0"><input type="text" size="11" class="form-control date" id="inputatributvariable_{{ $atributvariable->id }}" placeholder="dd/mm/yyyy" autocomplete="off" name="av_value[]"></td>
                                                        <td style="margin-left:10px"><input type="text" size="7" id="time" class="form-control time" placeholder="hh:mm" autocomplete="off" name="av_valuetime[]"></td>
                                                    </tr>
                                                </table>
                                            @elseif($key->tipedata == 'number')
                                                <table>
                                                    <tr>
                                                        <td style="padding-left:0">
                                                            <input type="text" class="form-control" id="inputatributvariable_{{ $atributvariable->id }}" onkeypress="return onlyNumber({{ $key->decimal != '' ? $key->decimal : 0 }},event)"  autocomplete="off" name="av_value[]" maxlength="100">
                                                            <input type="hidden" value="" name="av_valuetime[]">
                                                        </td>
                                                        @if($key->decimal != '')
                                                            <td><i title="{{ trans('all.gunakantitiksebagaidesimal') }}" style="color:#1c84c6" class="fa fa-info-circle"></i></td>
                                                        @endif
                                                    </tr>
                                                </table>
                                            @endif
                                        @endforeach
                                    @else
                                        <input type="text" size="50" class="form-control" id="inputatributvariable_{{ $atributvariable->id }}" autocomplete="off" name="av_value[]" maxlength="100">
                                        <input type="hidden" value="" name="av_valuetime[]">
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td>{{ trans('all.tanggalaktif') }}</td>
                        <td style="float:left">
                            <input type="text" size="11" class="form-control date" autocomplete="off" name="tanggalaktif" id="tanggalaktif" maxlength="10" value="{{ date('d/m/Y') }}" placeholder="dd/mm/yyyy">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.status') }}</td>
                        <td style="float:left">
                            <select class="form-control" id="status" name="status" onchange="return pilihstatus()">
                                <option value=""></option>
                                <option value="a" @if('a' == old('status')) selected @endif>{{ trans('all.aktif') }}</option>
                                <option value="t" @if('t' == old('status')) selected @endif>{{ trans('all.tidakaktif') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr style="display:none" id="flagtanggalnonaktif">
                        <td>{{ trans('all.tanggaltidakaktif') }}</td>
                        <td style="float:left">
                            <input type="text" size="11" class="form-control date" autocomplete="off" value="{{ old('tanggalawal', date('d/m/Y')) }}" name="tanggaltidakaktif" id="tanggaltidakaktif" maxlength="10" placeholder="dd/mm/yyyy">
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" style="padding-top: 7px">{{ trans('all.atribut') }}</td>
                        <td style="float: left;">
                          <table id="tabelatribut"></table>
                          <button type="button" class="btn btn-success" onclick="return aturatribut()"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturatribut') }}</button>
                          <button type="button" style="display:none" id="buttonmodalatribut" data-toggle="modal" data-target="#myModal"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturatribut') }}</button><br>
                          <span id="atributarea"></span>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" style="padding-top: 7px">{{ trans('all.lokasi') }}</td>
                        <td style="float: left;">
                          <table id="tabellokasi"></table>
                          <button type="button" class="btn btn-success" onclick="return aturlokasi()"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturlokasi') }}</button>
                          <button type="button" style="display:none" id="buttonmodallokasi" class="btn btn-success" data-toggle="modal" data-target="#myModal2"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturlokasi') }}</button><br>
                          <span id="lokasiarea"></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"
                                data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&nbsp;&times;</a>{{ trans('onboarding.button_pegawai_simpan') }}</div></div>' data-toggle="popover-pegawai-simpan" data-content="content">
                                <span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span>
                                <span class="spinner"></span>
                            </button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('{{$onboarding ? '../pegawai?onboarding=true' : '../pegawai'}}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                        </td>
                    </tr>
                </table>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal tambah atribut-->
  <div class="modal fade" id="myModal" role="dialog" tabindex='-1'>
    <div class="modal-dialog @if(count($atribut)<=1) modal-sm @elseif(count($atribut)==2) modal-md @else modal-lg @endif">
      
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
          <h4 class="modal-title">{{ trans('all.atribut') }}</h4>
        </div>
          <div class="modal-body" style="max-height:480px;overflow: auto;">
          @if(isset($atribut))
              @for($i=0;$i<count($atribut);$i++)
                  <div class="@if(count($atribut)<=1) col-md-12 @elseif(count($atribut)==2) col-md-6 @else col-md-4 @endif">
                      <b>{{ $atribut[$i]['atribut'] }}</b>
                      <table>
                          @foreach($atribut[$i]['atributnilai'] as $key)
                              <tr>
                                  <td style="width:20px;padding:2px" valign="top">
                                      @if($atribut[$i]['jumlahinputan'] == 'multiple')
                                        <input type="checkbox" @if($key->enable == 0) disabled @endif class="atributpopup" idatribut="{{ $key->idatribut }}" name="atribut_{{ $key->idatribut }}" id="atributpopup{{ $key->id }}" value="{{ $key->id }}">
                                      @else
                                        <input type="radio" @if($key->enable == 0) disabled @endif class="atributpopup" idatribut="{{ $key->idatribut }}" name="atribut_{{ $key->idatribut }}" id="atributpopup{{ $key->id }}" value="{{ $key->id }}">
                                      @endif
                                  </td>
                                  <td style="padding: 2px;">
                                      <span id="attrpopup_atribut{{ $key->id }}" @if($key->enable != 0) onclick="spanclick('atributpopup{{ $key->id }}')" @else style="color:#c1c1c1;cursor: default;" @endif atribut="{{ $atribut[$i]['atribut'] }}">{{ $key->nilai }}</span>
                                  </td>
                              </tr>
                          @endforeach
                      </table>
                  </div>
              @endfor
          @endif
        </div>
        <div class="modal-footer">
            <table width="100%">
                <tr>
                    <td style="padding:0;align:right">
                        <button class="btn btn-primary" id="tambahatribut"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</button>
                    </td>
                </tr>
            </table>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal tambah atribut-->

  <!-- Modal tambah lokasi-->
  <div class="modal fade" id="myModal2" role="dialog" tabindex='-1'>
    <div class="modal-dialog modal-sm">
      
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" id='closemodal2' data-dismiss="modal">&times;</button>
          <h4 class="modal-title">{{ trans('all.lokasi') }}</h4>
        </div>
        <div class="modal-body" style="max-height:480px;overflow: auto;">
          <table>
            @foreach($lokasis as $lokasi)
              <tr>
                <td style="padding:2px">
                  <input type="checkbox" class="lokasipopup" id="lokasipopup{{ $lokasi->id }}" value="{{ $lokasi->id }}"> <span id="attrpopup{{ $lokasi->id }}" onclick="spanclick('lokasipopup{{ $lokasi->id }}')">{{ $lokasi->nama }}</span><br>
                </td>
              </tr>
            @endforeach
          </table>
        </div>
        <div class="modal-footer">
          <table width="100%">
              <tr>
                  <td style="padding:0px;align:right">
                      <button class="btn btn-primary" id="tambahlokasi"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</button>
                  </td>
              </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal tambah lokasi-->
  <script>
    @if($onboarding)
        $(document).ready(function(){
            $('[data-toggle="popover-pegawai-pin"]').popover({
                placement : 'auto right',
                trigger : 'manual',
            });
            $('[data-toggle="popover-pegawai-pin"]').popover('show');
            $('#popover-pegawai-pin .popover').css('top', '25%');
            $('#popover-pegawai-pin .arrow').css('top', '25%');
            $(document).on("click", ".popover .close" , function(){
                $(this).parents('.popover').popover('hide');
            });
        });



        $(document).ready(function(){
            $('[data-toggle="popover-pegawai-simpan"]').popover({
                placement : 'bottom',
                trigger : 'manual',
            });
            $('[data-toggle="popover-pegawai-simpan"]').popover('show');
            $(document).on("click", ".popover .close" , function(){
                $(this).parents('.popover').popover('hide');
            });
        });
    @endif
  </script>
@stop