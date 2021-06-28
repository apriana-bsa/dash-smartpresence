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
        
		var payrollkelompok = $("#payrollkelompok").val();
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

		if(payrollkelompok == ""){
			alertWarning("{{ trans('all.kelompokkosong') }}",
            function() {
                  aktifkanTombol();
                  setFocus($('#payrollkelompok'));
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

		if(berlakumulai == ""){
			alertWarning("{{ trans('all.berlakumulaikosong') }}",
            function() {
                  aktifkanTombol();
                  setFocus($('#berlakumulai'));
            });
            return false;
		}
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.slipgaji') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.payroll') }}</li>
            <li>{{ trans('all.slipgaji') }}</li>
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
          	<form action="{{ url('datainduk/payroll/slipgaji') }}" method="post" onsubmit="return validasi()" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="640px">
                    <tr>
                        <td width="120px">{{ trans('all.kelompok') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="payrollkelompok" id="payrollkelompok">
                                <option value=""></option>
                                @foreach($datapayrollkelompok as $key)
                                    <option value="{{$key->id }}">{{$key->nama}}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.nama') }}</td>
                        <td>
                            <input type="text" class="form-control" autocomplete="off" name="nama" id="nama" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.berlakumulai') }}</td>
                        <td style="float:left">
                            <input type="text" size="10" class="form-control date" value="{{date('d/m/Y')}}" autocomplete="off" name="berlakumulai" id="berlakumulai" placeholder="dd/mm/yyyy">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.templateslipgaji') }}</td>
                        <td>
                            <input type="file" name="template_excel">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.keterangan') }}</td>
                        <td>
                            <textarea class="form-control" style="resize:none" name="keterangan"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../slipgaji')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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