@extends('layouts.master')
@section('title', trans('all.atribut'))
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
		
	function validasi(){
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');
		
		var atribut = $("#atribut").val();
		var tampilpadaringkasan = $("#tampilpadaringkasan").val();
	
		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
			function() {
			  aktifkanTombol();
			});
	  		return false;
		@endif
	
		if(atribut == ""){
			alertWarning("{{ trans('all.atributkosong') }}",
			function() {
			  aktifkanTombol();
			  setFocus($('#atribut'));
			});
	  		return false;
		}

		if(tampilpadaringkasan == ""){
			alertWarning("{{ trans('all.tampilpadaringkasankosong') }}",
					function() {
						aktifkanTombol();
						setFocus($('#tampilpadaringkasan'));
					});
			return false;
		}
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.atribut') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.atribut') }}</li>
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
          	<form action="../{{ $atribut->id }}" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="_method" value="put">
				<table width="480px">
					<tr>
						<td width=150px>{{ trans('all.atribut') }}</td>
						<td>
							<input type="text" class="form-control" value="{{ $atribut->atribut }}" autofocus autocomplete="off" name="atribut" id="atribut" maxlength="100">
						</td>
					</tr>
					<tr>
							<td width="110px">{{ trans('all.kode') }}</td>
							<td style="float:left">
									<input type="text" size="25" value="{{ $atribut->kode }}" class="form-control" autocomplete="off" name="kode" id="kode" maxlength="20">
							</td>
					</tr>
					<tr>
						<td>{{ trans('all.tampilpadaringkasan') }}</td>
						<td style="float:left">
							<select class="form-control" name="tampilpadaringkasan" id="tampilpadaringkasan">
								<option value="y" @if($atribut->tampilpadaringkasan == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($atribut->tampilpadaringkasan == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.penting') }}</td>
						<td style="float:left">
							<select class="form-control" name="penting" id="penting">
								<option value="y" @if($atribut->penting == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($atribut->penting == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.jumlahinputan') }}</td>
						<td style="float:left">
							<select class="form-control" name="jumlahinputan" id="jumlahinputan">
								<option value="satu" @if($atribut->jumlahinputan == 'satu') selected @endif>{{ trans('all.satu') }}</option>
								<option value="multiple" @if($atribut->jumlahinputan == 'multiple') selected @endif>{{ trans('all.multiple') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../atribut')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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