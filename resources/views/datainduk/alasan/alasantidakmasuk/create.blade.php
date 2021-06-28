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
		var kategori = $("#kategori").val();
		var urutan = $("#urutan").val();
		var digunakan = $("#digunakan").val();

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

		if(kategori == ""){
			alertWarning("{{ trans('all.kategorikosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#kategori'));
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

		if(digunakan == ""){
			alertWarning("{{ trans('all.digunakankosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#digunakan'));
            });
      return false;
		}
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_alasantidakmasuk') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.catatankehadiran') }}</li>
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
          	<form action="{{ url('datainduk/alasan/alasantidakmasuk') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="140px">{{ trans('all.alasan') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="alasan" id="alasan" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.kategori') }}</td>
                        <td style="float:left">
                            <select id="kategori" name="kategori" class="form-control">
                                <option value=""></option>
                                <option value="s">{{ trans('all.sakit') }}</option>
                                <option value="i">{{ trans('all.ijin') }}</option>
                                <option value="d">{{ trans('all.dispensasi') }}</option>
                                <option value="a">{{ trans('all.tidakmasuk') }}</option>
                                <option value="c">{{ trans('all.cuti') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.hitunguangmakan') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="hitunguangmakan" id="hitunguangmakan">
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t" selected>{{ trans('all.tidak') }}</option>
                            </select>
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
                            <select class="form-control" name="digunakan" id="digunakan">
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../alasantidakmasuk')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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