@extends('layouts.master')
@section('title', trans('all.pegawai'))
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
    
    $(function(){
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });

        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
    });

    function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        $('#loading-saver').css('display', '');

        var jamkerja = $("#jamkerja").val();
        var berlakumulai = $("#berlakumulai").val();

        @if(Session::has('conf_webperusahaan'))
        @else
            alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
              $('#loading-saver').css('display', 'none');
            });
            return false;
        @endif

        if(jamkerja == ""){
            $('#loading-saver').css('display', 'none');
            alertWarning("{{ trans('all.jamkerjakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#jamkerja'));
            });
            return false;
        }

        if(berlakumulai == ""){
            $('#loading-saver').css('display', 'none');
            alertWarning("{{ trans('all.berlakumulaikosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#berlakumulai'));
                    });
            return false;
        }
    }
  </script>
  <style>
  span{
    cursor:pointer;
  }
  </style>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.jamkerja').' ('.$namapegawai->nama.')' }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.pegawai') }}</li>
        <li>{{ trans('all.jamkerja') }}</li>
        <li class="active"><strong>{{ trans('all.ubahdata') }}</strong></li>
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
          	<form id="formubah" action="{{ url('datainduk/pegawai/pegawai/jamkerja/submitubah') }}" method="post" onsubmit="return validasi()" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="idpegawai" value="{{ $idpegawai }}">
                <input type="hidden" name="idpegawaijamkerja" value="{{ $jamkerjapegawai->id }}">
                <table width="480px">
                    <tr>
                        <td width="140px">{{ trans('all.jamkerja') }}</td>
                        <td style="float:left;">
                            <select class="form-control" name="jamkerja" id="jamkerja">
                                @foreach($jamkerja as $key)
                                    <option value="{{ $key->id }}" @if($key->id == $jamkerjapegawai->idjamkerja) selected @endif>{{ $key->nama }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.berlakumulai') }}</td>
                        <td style="float:left">
                            <input type="text" size="11" class="form-control date" autocomplete="off" name="berlakumulai" value="{{ $jamkerjapegawai->berlakumulai }}" id="berlakumulai" maxlength="10" placeholder="dd/mm/yyyy">
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../../{{ $idpegawai }}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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