@extends('layouts.master')
@section('title', trans('all.customdashboard'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	</style>
	<script>
	$(document).ready(function() {
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

		$('.jam').inputmask( 'h:s' );
	});

	function validasi(){
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');
		
		var nama = $("#nama").val();
	
		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
			function() {
			  aktifkanTombol();
			});
	  		return false;
		@endif
	
		if(nama == ""){
			alertWarning("{{ trans('all.namakosong') }}",
			function() {
			  aktifkanTombol();
			  setFocus($('#nama'));
			});
	  		return false;
		}
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.customdashboard') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.pengaturan') }}</li>
        <li>{{ trans('all.customdashboard') }}</li>
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
          	<form action="../{{ $data->id }}" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="_method" value="put">
				<table width="480px">
					<tr>
						<td width=200px>{{ trans('all.nama') }}</td>
						<td>
							<input type="text" class="form-control" value="{{ $data->nama }}" autofocus autocomplete="off" name="nama" id="nama" maxlength="100">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampil_konfirmasi') }}</td>
						<td style="float:left">
							<select id="tampil_konfirmasi" name="tampil_konfirmasi" class="form-control">
								<option value="y" @if($data->tampil_konfirmasi == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampil_konfirmasi == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampil_peringkat') }}</td>
						<td style="float:left">
							<select id="tampil_peringkat" name="tampil_peringkat" class="form-control">
								<option value="y" @if($data->tampil_peringkat == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampil_peringkat == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampil_3lingkaran') }}</td>
						<td style="float:left">
							<select id="tampil_3lingkaran" name="tampil_3lingkaran" class="form-control">
								<option value="y" @if($data->tampil_3lingkaran == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampil_3lingkaran == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampil_sudahbelumabsen') }}</td>
						<td style="float:left">
							<select id="tampil_sudahbelumabsen" name="tampil_sudahbelumabsen" class="form-control">
								<option value="y" @if($data->tampil_sudahbelumabsen == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampil_sudahbelumabsen == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampil_terlambatdll') }}</td>
						<td style="float:left">
							<select id="tampil_terlambatdll" name="tampil_terlambatdll" class="form-control">
								<option value="y" @if($data->tampil_terlambatdll == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampil_terlambatdll == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampil_pulangawaldll') }}</td>
						<td style="float:left">
							<select id="tampil_pulangawaldll" name="tampil_pulangawaldll" class="form-control">
								<option value="y" @if($data->tampil_pulangawaldll == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampil_pulangawaldll == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampil_totalgrafik') }}</td>
						<td style="float:left">
							<select id="tampil_totalgrafik" name="tampil_totalgrafik" class="form-control">
								<option value="y" @if($data->tampil_totalgrafik == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampil_totalgrafik == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampil_peta') }}</td>
						<td style="float:left">
							<select id="tampil_peta" name="tampil_peta" class="form-control">
								<option value="y" @if($data->tampil_peta == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampil_peta == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampil_harilibur') }}</td>
						<td style="float:left">
							<select id="tampil_harilibur" name="tampil_harilibur" class="form-control">
								<option value="y" @if($data->tampil_harilibur == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampil_harilibur == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampil_riwayatdashboard') }}</td>
						<td style="float:left">
							<select id="tampil_riwayatdashboard" name="tampil_riwayatdashboard" class="form-control">
								<option value="y" @if($data->tampil_riwayatdashboard == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampil_riwayatdashboard == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../customdashboard')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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