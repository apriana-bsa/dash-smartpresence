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
		
		var email = $("#email").val();
	
		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
			function() {
			  aktifkanTombol();
			});
	  		return false;
		@endif

        if(email === ""){
            alertWarning("{{ trans('all.emailkosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#email'));
                });
            return false;
        }

		if(!is_valid_email(email)){
			alertWarning("{{ trans('all.emailtidakvalid') }}",
					function() {
						aktifkanTombol();
						setFocus($('#email'));
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
        <li>{{ trans('all.email') }}</li>
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
						<td width=150px>{{ trans('all.email') }}</td>
						<td>
							<input type="text" class="form-control" value="{{ $data->email }}" autofocus autocomplete="off" name="email" id="email" maxlength="100">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.customdashboard') }}</td>
						<td style="float:left">
							<select id="customdashboard" class="form-control" name="customdashboard">
								@if($datacustomdashboard != '')
									@foreach($datacustomdashboard as $key)
										<option value="{{ $key->id }}" @if($key->id == $data->idcustomdashboard) selected @endif>{{ $key->nama }}</option>
									@endforeach
								@endif
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../customdashboardemail')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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