@extends('layouts.master')
@section('title', trans('all.kelompok'))
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
        <h2>{{ trans('all.kelompok') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.payroll') }}</li>
            <li>{{ trans('all.kelompok') }}</li>
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
          	<form action="{{ url('datainduk/payroll/payrollkelompok') }}" method="post" onsubmit="return validasi()" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table>
                    <tr>
                        <td width="170px">{{ trans('all.nama') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.templatepayrollposting') }}</td>
                        <td>
                            <input type="file" name="templatepayrollposting">
                        </td>
                    </tr>
{{--                    <tr>--}}
{{--                        <td>{{ trans('all.templateslipgaji') }}</td>--}}
{{--                        <td>--}}
{{--                            <input type="file" name="templateslipgaji">--}}
{{--                        </td>--}}
{{--                    </tr>--}}
                    <tr>
                        <td colspan="2"><i>* {{ trans('all.catatanheaderfooterpayrollpengaturan') }}</i></td>
                    </tr>
                    <tr>
                        <td colspan="2"><i>* {{ trans('all.catatanformatheaderfooterpayrollpengaturan') }}</i></td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../payrollkelompok')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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