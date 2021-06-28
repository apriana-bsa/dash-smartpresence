@extends('layouts.master')
@section('title', trans('all.pengelola'))
@section('content')

	<script>
	function validasi() {
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');
	}
	</script>
	<style>
	td{
		padding:5px;
	}
	</style>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.pengelola') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.pengelola') }}</li>
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
          	<form action="../{{ $pengelola->id }}" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="_method" value="put">
				<table width="480px">
					<tr>
						<td width=100px>{{ trans('all.hakakses') }}</td>
						<td style="float:left">
							<select class="form-control" id="hakakses" name="hakakses">
								@foreach($hakaksess as $hakakses)
									<option value="{{ $hakakses->id }}" @if($pengelola->idhakakses == $hakakses->id) selected @endif>{{ $hakakses->nama }}</option>
								@endforeach
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../pengelola')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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