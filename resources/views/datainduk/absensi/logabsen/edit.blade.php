@extends('layouts.master')
@section('title', trans('all.menu_logabsen'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	
    span{
        cursor:default;
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
        
	function validasi(){
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');
		
		var tanggal = $("#tanggal").val();
		var jam = $("#jam").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
      		return false;
		@endif
		
		if(tanggal == ""){
			alertWarning("{{ trans('all.waktukosong') }}",
			function() {
				aktifkanTombol();
				setFocus($('#tanggal'));
			});
			return false;
		}
	
		if(jam == ""){
			alertWarning("{{ trans('all.waktukosong') }}",
			function() {
				aktifkanTombol();
				setFocus($('#jam'));
			});
			return false;
		}
	}

	$(function(){
		$('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
			$(this).datepicker('hide');
		});
	
		$('.date').mask("00/00/0000", {clearIfNotMatch: true});
	
		$('.jam').inputmask( "h:s:s", {
            placeholder: "hh:mm:ss",
            insertMode: false,
            showMaskOnHover: false,
            hourFormat: 24
        });
	});
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_logabsen') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.menu_logabsen') }}</li>
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
          	<form action="../{{ $logabsen->id }}" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="_method" value="put">
				<table width="480px">
					<tr>
						<td width="110px">{{ trans('all.waktu') }}</td>
						<td style="float: left;">
							<input type="text" size="11" class="form-control date" value="{{ $logabsen->tanggal }}" autofocus autocomplete="off" name="tanggal" id="tanggal" maxlength="10" placeholder="dd/mm/yyyy">
						</td>
						<td style="float: left;">
							<input type="text" size="7" class="form-control jam" value="{{ $logabsen->jam }}" autocomplete="off" name="jam" id="jam" placeholder="hh:mm:ss">
						</td>
					</tr>
					<tr>
						<td width="110px">{{ trans('all.pegawai') }}</td>
						<td colspan="2">
							<input type="text" disabled class="form-control" value="{{ $logabsen->pegawai }}" autocomplete="off" name="pegawai" id="pegawai" maxlength="100">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.alasan') }}</td>
						<td colspan="2" style="float:left">
							<select class="form-control" name="alasan" id="alasan">
								<option value=""></option>
								@foreach($alasanmasukkeluar as $key)
									<option value="{{ $key->id }}" @if($key->id == $logabsen->idalasanmasukkeluar) selected @endif>{{ $key->alasan }}</option>
								@endforeach
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.masukkeluar') }}</td>
						<td style="float:left">
							<select id="masukkeluar" name="masukkeluar" class="form-control">
								<option value="m" @if($logabsen->masukkeluar == 'm') selected @endif>{{ trans('all.masuk') }}</option>
								<option value="k" @if($logabsen->masukkeluar == 'k') selected @endif>{{ trans('all.keluar') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.status') }}</td>
						<td style="float:left">
							<select id="status" name="status" class="form-control">
								<option value="v" @if($logabsen->status == 'v') selected @endif>{{ trans('all.valid') }}</option>
								<option value="c" @if($logabsen->status == 'c') selected @endif>{{ trans('all.konfirmasi') }}</option>
								<option value="na" @if($logabsen->status == 'na') selected @endif>{{ trans('all.ditolak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.flag') }}</td>
						<td colspan="2" style="float:left">
							<select class="form-control" name="flag" id="flag">
								<option value="" @if($logabsen->flag == '') selected @endif></option>
								<option value="tidak-terlambat" @if($logabsen->flag == 'tidak-terlambat') selected @endif>{{ trans('all.tidakterlambat') }}</option>
								<option value="tidak-pulangawal" @if($logabsen->flag == 'tidak-pulangawal') selected @endif>{{ trans('all.tidakpulangawal') }}</option>
								<option value="lembur" @if($logabsen->flag == 'lembur') selected @endif>{{ trans('all.lembur') }}</option>
								<option value="tidak-lembur" @if($logabsen->flag == 'tidak-lembur') selected @endif>{{ trans('all.tidaklembur') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td width="110px">{{ trans('all.keterangan') }}</td>
						<td colspan="2">
							<textarea type="text" class="form-control" autocomplete="off" name="flagketerangan" id="flagketerangan" maxlength="255" style="resize:none">{{ $logabsen->flag_keterangan }}</textarea>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../logabsen')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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