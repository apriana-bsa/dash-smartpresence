@extends('layouts.master')
@section('title', trans('all.ijintidakmasuk'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	</style>
	<script>
    $(function(){
        @if(Session::get('message'))
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
        @endif

        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
          $(this).datepicker('hide');
        });
        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
        selectInput('#pegawai','{{ url('select2pegawai') }}');
    });

	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var pegawai = $("#pegawai").val();
        var tanggalawal = $("#tanggalawal").val();
        var tanggalakhir = $("#tanggalakhir").val();
        var alasan = $("#alasan").val();
        var keterangan = $("#keterangan").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
            return false;
		@endif

		if(pegawai === ''){
			alertWarning("{{ trans('all.pegawaikosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#token-input-pegawai'));
            });
            return false;
		}

        if(tanggalawal === ""){
          alertWarning("{{ trans('all.tanggalkosong') }}",
                function() {
                  aktifkanTombol();
                  setFocus($('#tanggalawal'));
                });
          return false;
        }

        if(tanggalakhir === ""){
          alertWarning("{{ trans('all.tanggalkosong') }}",
                function() {
                  aktifkanTombol();
                  setFocus($('#tanggalakhir'));
                });
          return false;
        }

        if(tanggalakhir.split("/").reverse().join("-") < tanggalawal.split("/").reverse().join("-")){
            alertWarning("{{ trans('all.tanggalakhirlebihkecildaritanggalawal') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#tanggalakhir'));
                });
            return false;
        }

        var jumharimaks = 365;
        if(cekSelisihTanggal(tanggalawal,tanggalakhir,jumharimaks) == true){
            alertWarning("{{ trans('all.selisihharimaksimal') }}"+jumharimaks+" {{ trans('all.hari') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#tanggalakhir'));
                });
            return false;
        }

        if(alasan === ""){
          alertWarning("{{ trans('all.alasankosong') }}",
                function() {
                  aktifkanTombol();
                  setFocus($('#alasan'));
                });
          return false;
        }

        if(keterangan === ""){
          alertWarning("{{ trans('all.keterangankosong') }}",
                function() {
                  aktifkanTombol();
                  setFocus($('#keternagan'));
                });
          return false;
        }
	}

    function readURL(input) {

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#imgInpLampiran').attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
            $('#hapusfoto').removeAttr('disabled');
        }
    }

    $(function(){
        $("#foto").change(function(){
            readURL(this);
        });

        $('#hapusfoto').click(function(){
            $('#foto').val('');
            $('#imgInpLampiran').attr('src', '{{ url("foto/ijintidakmasuk/0") }}');
            $(this).attr('disabled', 'disabled');
            return false;
        });
    });
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.ijintidakmasuk') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.ijintidakmasuk') }}</li>
        <li class="active"><strong>{{ trans('all.tambahdata') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content">
          	<form action="{{ url('datainduk/absensi/ijintidakmasuk/') }}" method="post" onsubmit="return validasi()" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="110px">{{ trans('all.pegawai') }}</td>
                        <td width=100%>
                            <select class="form-control" id="pegawai" name="pegawai[]" multiple="multiple"></select>
{{--                            <select class="form-control" id="pegawai" name="pegawai">--}}
{{--                                <option value=""></option>--}}
{{--                            </select>--}}
                        </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.tanggal') }}</td>
                      <td>
                        <table>
                          <tr>
                            <td style="padding-left:0px;padding-top:0px;padding-bottom: 0px;float:left">
                              <input type="text" class="form-control date" size="11" value="{{ old('tanggalawal') }}" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalawal" id="tanggalawal" maxlength="10">
                            </td>
                            <td style="padding-bottom: 0px;padding-top:0px;">-</td>
                            <td style="padding-bottom: 0px;padding-top:0px;padding-right: 0px;float:left">
                              <input type="text" class="form-control date" size="11" value="{{ old('tanggalakhir') }}" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalakhir" id="tanggalakhir" maxlength="10">
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.alasan') }}</td>
                      <td style="float:left">
                        <select id="alasan" name="alasan" class="form-control">
                          <option value=""></option>
                          @foreach($alasantidakmasuks as $alasantidakmasuk)
                            <option value="{{ $alasantidakmasuk->id }}" @if(old('alasan') == $alasantidakmasuk->id) selected @endif>{{ $alasantidakmasuk->alasan }}</option>
                          @endforeach
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.keterangan') }}</td>
                      <td>
                        <textarea id="keterangan" name="keterangan" class="form-control" style="resize:none" rows="4">{{ old('keterangan') }}</textarea>
                      </td>
                    </tr>
                    <tr>
                        <td colspan="2"><input onclick="checkboxclick('flagpakailampiran',false,'flaglampiran')" id="flagpakailampiran" type="checkbox" name="flagpakailampiran"> <span style="cursor:pointer" onclick="spanclick('flagpakailampiran',false,'flaglampiran')">{{ trans('all.pakailampiran') }}</span></td>
                    </tr>
                    <tr class="flaglampiran" style="display:none">
                        <td>{{ trans('all.lampiran') }}</td>
                        <td>
                            <img id="imgInpLampiran" width=120 height=120 src='{{ url("foto/ijintidakmasuk/0") }}'>
                        </td>
                    </tr>
                    <tr class="flaglampiran" style="display:none">
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
                      <td colspan=2>
                        <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                        <button type="button" id="kembali" onclick="return ke('../ijintidakmasuk')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                      </td>
                    </tr>
                </table>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

@stop