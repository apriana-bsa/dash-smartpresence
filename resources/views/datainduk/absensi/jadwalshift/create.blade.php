@extends('layouts.master')
@section('title', trans('all.jadwalshift'))
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

		var tanggal = $("#tanggal").val();
        var pegawai = $("#pegawai").val();
        var jamkerja = $("#jamkerja").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
            return false;
		@endif
        
        if(tanggal == ""){
            alertWarning("{{ trans('all.tanggalkosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#tanggal'));
            });
            return false;
        }
        
        if(pegawai == ""){
            alertWarning("{{ trans('all.pegawaikosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#token-input-pegawai'));
            });
            return false;
        }
        
        if(jamkerja == ""){
            alertWarning("{{ trans('all.jamkerjakosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#jamkerja'));
            });
            return false;
        }
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.jadwalshift') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.jadwalshift') }}</li>
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
          	<form action="{{ url('datainduk/absensi/jadwalshift') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="90px">{{ trans('all.tanggal') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control date"  size="9" autofocus autocomplete="off" placeholder="dd/mm/yyyy" name="tanggal" id="tanggal" maxlength="10">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.pegawai') }}</td>
                        <td>
                            <input type="text" class="form-control" autocomplete="off" name="pegawai" id="pegawai" maxlength="100">
                            <script type="text/javascript">
                                $(document).ready(function(){
                                    $("#pegawai").tokenInput("{{ url('pegawai') }}", {
                                        theme: "facebook",
                                        tokenLimit: 1
                                    });
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.jamkerja') }}</td>
                      <td style="float:left">
                        <select class="form-control" id="jamkerja" name="jamkerja">
                            <option value=""></option>
                            @foreach($jamkerja as $key)
                                <option value="{{ $key->id }}">{{ $key->namashift }}</option>
                            @endforeach
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td colspan=2>
                        <button type="submit" name="simpan" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>&nbsp;&nbsp;
                        <button type="button" onclick="return ke('../jadwalshift')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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