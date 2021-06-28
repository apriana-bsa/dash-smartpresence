@extends('layouts.master')
@section('title', trans('all.menu_alasantidakmasuk'))
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
		
		var alasan = $("#alasan").val();
		var urutan = $("#urutan").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
      return false;
		@endif

		if(alasan == ""){
			alertWarning("{{ trans('all.alasankosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#alasan'));
            });
      return false;
		}

		if (cekAlertAngkaValid(urutan,0,999,0,"{{ trans('all.urutan') }}",
		                        function() {
		                          aktifkanTombol();
		                          setFocus($('#urutan'));
		                        }
		                      )==false) return false;
		urutan=replaceAll(urutan.trim(),',','.');
	}
	</script>

	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_alasantidakmasuk') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.alasan') }}</li>
        <li>{{ trans('all.menu_alasantidakmasuk') }}</li>
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
          	<form action="../{{ $alasantidakmasuk->id }}" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="_method" value="put">
				<table width="480px">
					<tr>
						<td width=140px>{{ trans('all.alasan') }}</td>
						<td>
							<input type="text" class="form-control" value="{{ $alasantidakmasuk->alasan }}" autofocus autocomplete="off" name="alasan" id="alasan" maxlength="100">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.kategori') }}</td>
						<td style="float:left">
							<select id="kategori" name="kategori" class="form-control">
								<option value="s" @if($alasantidakmasuk->kategori == 's') selected @endif>{{ trans('all.sakit') }}</option>
								<option value="i" @if($alasantidakmasuk->kategori == 'i') selected @endif>{{ trans('all.ijin') }}</option>
								<option value="d" @if($alasantidakmasuk->kategori == 'd') selected @endif>{{ trans('all.dispensasi') }}</option>
								<option value="a" @if($alasantidakmasuk->kategori == 'a') selected @endif>{{ trans('all.tidakmasuk') }}</option>
								<option value="c" @if($alasantidakmasuk->kategori == 'c') selected @endif>{{ trans('all.cuti') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.hitunguangmakan') }}</td>
						<td style="float:left">
							<select class="form-control" name="hitunguangmakan" id="hitunguangmakan">
								<option value="y" @if($alasantidakmasuk->hitunguangmakan == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($alasantidakmasuk->hitunguangmakan == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.urutan') }}</td>
						<td style="float:left">
							<input type="text" class="form-control" onkeypress="return onlyNumber(0,event)" value="{{ $alasantidakmasuk->urutan }}" size="7" name="urutan" autocomplete="off" id="urutan" maxlength="3">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.digunakan') }}</td>
						<td style="float:left">
							<select class="form-control" name="digunakan" id="digunakan">
								<option value="y" @if($alasantidakmasuk->digunakan == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($alasantidakmasuk->digunakan == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../alasantidakmasuk')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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