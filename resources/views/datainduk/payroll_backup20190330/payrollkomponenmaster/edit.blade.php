@extends('layouts.master')
@section('title', trans('all.payrollkomponenmaster'))
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
		pilihCaraInput();
	});
	
	function validasi(){
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');
		
		var nama = $("#nama").val();
		var kode = $("#kode").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
      		return false;
		@endif

		if(nama == ""){
			alertWarning("{{ trans('all.nama').' '.trans('all.sa_kosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#nama'));
            });
      		return false;
		}

		if(kode == ""){
			alertWarning("{{ trans('all.kode').' '.trans('all.sa_kosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#kode'));
            });
      		return false;
		}
	}

	function pilihCaraInput(){
		var carainput = $('#carainput').val();
		$('.tr_inputmanual').css('display', 'none');
		$('.tr_formula').css('display', 'none');
		if(carainput == 'inputmanual'){
				$('.tr_inputmanual').css('display', '');
		}else if(carainput == 'formula'){
				$('.tr_formula').css('display', '');
		}
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.payrollkomponenmaster') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.payroll') }}</li>
        <li>{{ trans('all.payrollkomponenmaster') }}</li>
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
				<table width="100%">
					<tr>
						<td width=110px>{{ trans('all.nama') }}</td>
						<td style="float:left">
							<input type="text" class="form-control" value="{{ $data->nama }}" autofocus size="40" autocomplete="off" name="nama" id="nama" maxlength="100">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.kode') }}</td>
						<td style="float:left">
								<input type="text" size="20" class="form-control" value="{{ $data->kode }}" autocomplete="off" name="kode" id="kode" maxlength="20">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tipedata') }}</td>
						<td style="float:left">
							<select name="tipedata" id="tipedata" class="form-control">
								<option value="teks" @if($data->tipedata == 'teks') selected @endif>{{ trans('all.teks') }}</option>
								<option value="angka" @if($data->tipedata == 'angka') selected @endif>{{ trans('all.angka') }}</option>
								<option value="uang" @if($data->tipedata == 'uang') selected @endif>{{ trans('all.uang') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.komponenmastergrup') }}</td>
						<td style="float:left">
							<select name="payrollkomponenmastergroup" id="payrollkomponenmastergroup" class="form-control">
								<option value=""></option>
								@if($datapayrollkomponenmastergroup != '')
									@foreach($datapayrollkomponenmastergroup as $key)
										<option value="{{ $key->id }}" @if($data->idpayroll_komponen_master_group == $key->id) selected @endif>{{ $key->nama }}</option>
									@endforeach
								@endif
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.digunakan') }}</td>
						<td style="float:left">
							<select name="digunakan" id="digunakan" class="form-control">
								<option value="y" @if($data->digunakan == "y") selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->digunakan == "t") selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampilkan') }}</td>
						<td style="float:left">
							<select name="tampilkan" id="tampilkan" class="form-control">
								<option value="y" @if($data->tampilkan == "y") selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->tampilkan == "t") selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.carainput') }}</td>
						<td style="float:left">
							<select name="carainput" id="carainput" class="form-control" onchange="pilihCaraInput()">
									<option value="inputmanual" @if($data->carainput == 'inputmanual') selected @endif>{{ trans('all.inputmanual') }}</option>
									<option value="formula" @if($data->carainput == 'formula') selected @endif>{{ trans('all.formula') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../payrollkomponenmaster')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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