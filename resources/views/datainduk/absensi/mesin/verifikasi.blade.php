@extends('layouts.master')
@section('title', trans('all.menu_mesin'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	</style>
	<script>
	function validasi(){
		var deviceid = $("#deviceid").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
      return false;
		@endif

		if(deviceid == ""){
			alertWarning("{{ trans('all.deviceidkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#deviceid'));
            });
      	return false;
		}
	}

	$(function () {
	  @if($onboarding)
    	$('#deviceid').popover({
        	placement : 'auto right',
        	trigger : 'manual',
		});
		
		$('#deviceid').popover("show");
      @endif  
    	$(document).on("click", ".popover .close" , function(){
        	$(this).parents('.popover').popover('hide');
    	});
  })
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_mesin') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.menu_mesin') }}</li>
        <li class="active"><strong>{{ trans('all.verifikasi') }}</strong></li>
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
          	<form action="@if($onboarding) ../{{ $idmesin }}/submitverifikasi?onboarding=true @else ../{{ $idmesin }}/submitverifikasi @endif" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="_method" value="put">
				<table width="480px">
					<tr>
						<td width=110px>{{ trans('all.deviceid') }}</td>
						<td style="float:left">
							<input type="text" size="8" style="text-transform:uppercase" class="form-control" value="" autofocus autocomplete="off" name="deviceid" id="deviceid" maxlength="4" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{ trans('onboarding.mesin_masukkan') }}</div></div>' data-content='content'>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button type="submit" name="simpan" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>&nbsp;&nbsp;
							<button type="button" onclick="@if($onboarding) return ke('../../mesin?onboarding=true') @else return ke('../../mesin') @endif" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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