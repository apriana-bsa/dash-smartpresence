@extends('layouts.master')
@section('title', trans('all.kategoripekerjaan'))
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
        
		var nama = $("#item").val();
		var satuan = $("#satuan").val();
		var urutan = $("#urutan").val();

		@if(!Session::has('conf_webperusahaan'))
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
                  setFocus($('#item'));
            });
            return false;
		}

		if(satuan == ""){
			alertWarning("{{ trans('all.satuan').' '.trans('all.sa_kosong') }}",
            function() {
                  aktifkanTombol();
                  setFocus($('#satuan'));
            });
            return false;
		}

        if (cekAlertAngkaValid(urutan,0,999,0,"{{ trans('all.urutan') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#urutan'));
                }
            )==false) return false;
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ $pekerjaankategori }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.kepegawaian') }}</li>
            <li>{{ trans('all.kategoripekerjaan') }}</li>
            <li>{{ $pekerjaankategori }}</li>
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
          	<form action="{{ url('datainduk/pegawai/pekerjaanitem/'.$idpekerjaankategori) }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="110px">{{ trans('all.nama') }}</td>
                        <td width=100%>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="item" id="item" maxlength="200">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.satuan') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" autocomplete="off" name="satuan" id="satuan" maxlength="20">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.urutan') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" onkeypress="return onlyNumber(0,event)" size="7" name="urutan" autocomplete="off" id="urutan" maxlength="3">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.digunakan') }}</td>
                        <td style="float:left">
                            <select class="form-control" id="digunakan" name="digunakan">
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../{{ $idpekerjaankategori }}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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