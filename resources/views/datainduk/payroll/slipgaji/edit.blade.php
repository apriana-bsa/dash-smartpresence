@extends('layouts.master')
@section('title', trans('all.slipgaji'))
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

		$('.date').mask("00/00/0000", {clearIfNotMatch: true});

		$('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
			$(this).datepicker('hide');
		});
	});

	function validasi(){
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');

		var nama = $("#nama").val();
		var berlakumulai = $("#berlakumulai").val();

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

		if(berlakumulai == ""){
			alertWarning("{{ trans('all.berlakumulaikosong') }}",
					function() {
						aktifkanTombol();
						setFocus($('#berlakumulai'));
					});
			return false;
		}

	}

	function hapusFile(){
		alertConfirm('{{ trans('all.alerthapustemplate') }}',function(){ window.location.href="{{ url('datainduk/payroll/slipgaji/hapustemplate').'/'.$data->id  }}" })
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.slipgaji') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
          <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.slipgaji') }}</li>
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
          	<form action="../{{ $data->id }}" method="post" onsubmit="return validasi()" enctype="multipart/form-data">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="_method" value="put">
				<table width="640px">
					<tr>
						<td width="120px">{{ trans('all.kelompok') }}</td>
						<td>
							<select class="form-control" name="payrollkelompok" id="payrollkelompok">
								@foreach($datapayrollkelompok as $key)
									<option value="{{$key->id }}" @if($data->idpayrollkelompok == $key->id) selected @endif>{{$key->nama}}</option>
								@endforeach
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.nama') }}</td>
						<td>
							<input type="text" class="form-control" value="{{ $data->nama }}" autocomplete="off" name="nama" id="nama" maxlength="100">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.berlakumulai') }}</td>
						<td>
							<input type="text" class="form-control date" value="{{ \App\Utils::convertYmd2Dmy($data->berlakumulai) }}" autocomplete="off" name="berlakumulai" id="berlakumulai">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.templateslipgaji') }}</td>
						<td style="padding-left:0">
							<table>
								<tr>
									@if($data->template_excel != '')
										<td style="padding-top:0;padding-bottom:0"><button type="button" onclick="return hapusFile()" class="btn btn-danger"><i class="fa fa-trash"></i></button></td>
										<td style="padding-top:0;padding-bottom:0">{{ $data->template_excel }}</td>
									@endif
									<td style="padding-top:0;padding-bottom:0"><input type="file" name="template_excel"></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.keterangan') }}</td>
						<td>
							<textarea class="form-control" style="resize:none" name="keterangan">{{$data->keterangan}}</textarea>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../slipgaji')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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