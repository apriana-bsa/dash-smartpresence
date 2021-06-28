@extends('layouts.master')
@section('title', trans('all.pengguna'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	</style>
	<script>
	function validasi(){
		var hakakses = $("#hakakses").val();
		var nama = $("#nama").val();
		var email = $("#email").val();
		var password = $("#password").val();
		var status = $("#status").val();

		if(hakakses == ""){
			alertWarning("{{ trans('all.hakakseskosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#nama'));
            });
      return false;
		}

		if(nama == ""){
			alertWarning("{{ trans('all.namakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#nama'));
            });
      return false;
		}

		if(email == ""){
			alertWarning("{{ trans('all.emailkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#email'));
            });
      return false;
		}

		if(password == ""){
			alertWarning("{{ trans('all.katasandikosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#password'));
            });
      return false;
		}

		if(status == ""){
			alertWarning("{{ trans('all.statuskosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#status'));
            });
      return false;
		}
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.pengguna') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.pengguna') }}</li>
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
          	<form action="{{ url('datainduk/pengguna') }}" method="post" onsubmit="return validasi()">
							<input type="hidden" name="_token" value="{{ csrf_token() }}">
							<table width="480px">
								<tr>
									<td width=100px>{{ trans('all.hakakses') }}</td>
									<td style="float:left">
										<select class="form-control" id="hakakses" name="hakakses">
											<option value=""></option>
											@foreach($hakaksess as $hakakses)
												<option value="{{ $hakakses->id }}">{{ $hakakses->nama }}</option>
											@endforeach
										</select>
									</td>
								</tr>
								<tr>
									<td>{{ trans('all.nama') }}</td>
									<td>
										<input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="50">
									</td>
								</tr>
								<tr>
									<td>{{ trans('all.email') }}</td>
									<td>
										<input type="text" class="form-control" name="email" autocomplete="off" id="email" maxlength="255">
									</td>
								</tr>
								<tr>
									<td>{{ trans('all.katasandi') }}</td>
									<td>
										<input type="password" class="form-control" name="password" autocomplete="off" id="password" maxlength="255">
									</td>
								</tr>
								<tr>
									<td>{{ trans('all.status') }}</td>
									<td style="float:left">
										<select class="form-control" name="status" id="status">
											<option value=""></option>
											<option value="a">{{ trans('all.aktif') }}</option>
											<option value="tk">{{ trans('all.tidakaktif') }}</option>
											<option value="b">{{ trans('all.blokir') }}</option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan=2>
										<button type="submit" name="simpan" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>&nbsp;&nbsp;
										<button type="button" onclick="return ke('../pengguna')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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