@extends('layouts.master')
@section('title', trans('all.menu_alasanmasukkeluar'))
@section('content')

	<style>
	td{
		padding:5px;
	}

	.icon-select .icon {
		background-color: #f8ac59 !important;
	}
	.selected-icon{
		background-color: #f8ac59 !important;	
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
		var icon = $("#icon").val();
		var tampilsaat = $("#tampilsaat").val();
		var tampilpadamesin = $("#tampilapdamesin").val();
		var terhitungkerja = $("#terhitungkerja").val();
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

		if(icon == ""){
			alertWarning("{{ trans('all.iconkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#icon'));
            });
      return false;
		}

		if(tampilsaat == ""){
			alertWarning("{{ trans('all.tampilsaatkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#tampilsaat'));
            });
      return false;
		}

		if(tampilpadamesin == ""){
			alertWarning("{{ trans('all.tampilpadamesinkosong') }}",
					function() {
						aktifkanTombol();
						setFocus($('#tampilpadamesin'));
					});
			return false;
		}

		if(terhitungkerja == ""){
			alertWarning("{{ trans('all.terhitungkerjakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#terhitungkerja'));
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

	var iconSelect;
	var selectedText;
  window.onload = function(){

    iconSelect = new IconSelect("my-icon-select", 
        {'selectedIconWidth':50,
        'selectedIconHeight':50,
        'selectedBoxPadding':1,
        'iconsWidth':48,
        'iconsHeight':48,
        'boxIconSpace':1,
        'vectoralIconNumber':4,
        'horizontalIconNumber':4});

    selectedText = document.getElementById('icon');

    document.getElementById('my-icon-select').addEventListener('changed', function(e){
       selectedText.value = iconSelect.getSelectedValue();
    });

    var icons = [];
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_banjir.png") }}', 'iconValue':'alasan_banjir'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_cuaca1.png") }}', 'iconValue':'alasan_cuaca1'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_cuaca2.png") }}', 'iconValue':'alasan_cuaca2'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_gantishift.png") }}', 'iconValue':'alasan_gantishift'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_ibadah.png") }}', 'iconValue':'alasan_ibadah'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_ijin.png") }}', 'iconValue':'alasan_ijin'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_kecelakaan1.png") }}', 'iconValue':'alasan_kecelakaan1'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_kecelakaan2.png") }}', 'iconValue':'alasan_kecelakaan2'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_keperluanpribadi1.png") }}', 'iconValue':'alasan_keperluanpribadi1'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_keperluanpribadi2.png") }}', 'iconValue':'alasan_keperluanpribadi2'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_kirimbarang1.png") }}', 'iconValue':'alasan_kirimbarang1'});
    icons.push({'iconFilePath':'{{ asset("lib/icon_alasan/alasan_kirimbarang2.png") }}', 'iconValue':'alasan_kirimbarang2'});
    iconSelect.refresh(icons);

    $(".component-icon").html('');
    $(".component-icon").append('<i class="fa fa-caret-down"></i>');
  };
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_alasanmasukkeluar') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.catatankehadiran') }}</li>
        <li>{{ trans('all.menu_alasanmasukkeluar') }}</li>
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
          	<form action="{{ url('datainduk/alasan/alasanmasukkeluar') }}" method="post" onsubmit="return validasi()">
							<input type="hidden" name="_token" value="{{ csrf_token() }}">
							<table width="480px">
								<tr>
									<td width="140px">{{ trans('all.alasan') }}</td>
									<td>
										<input type="text" class="form-control" autofocus autocomplete="off" name="alasan" id="alasan" maxlength="100">
									</td>
								</tr>
								<tr>
									<td>{{ trans('all.icon') }}</td>
									<td>
										<input type="text" class="form-control" autocomplete="off" style="display:none" name="icon" id="icon" maxlength="30">
										<div id="my-icon-select"></div>
									</td>
								</tr>
								<tr>
									<td>{{ trans('all.tampilsaat') }}</td>
									<td style="float:left">
										<select id="tampilsaat" name="tampilsaat" class="form-control">
											<option value=""></option>
											<option value="m">{{ trans('all.masuk') }}</option>
											<option value="k">{{ trans('all.keluar') }}</option>
											<option value="mk">{{ trans('all.masukkeluar') }}</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>{{ trans('all.tampilpadamesin') }}</td>
									<td style="float:left">
										<select id="tampilpadamesin" name="tampilpadamesin" class="form-control">
											<option value=""></option>
											<option value="y">{{ trans('all.ya') }}</option>
											<option value="t">{{ trans('all.tidak') }}</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>{{ trans('all.terhitungkerja') }}</td>
									<td style="float:left">
										<select id="terhitungkerja" name="terhitungkerja" class="form-control">
											<option value=""></option>
											<option value="y">{{ trans('all.ya') }}</option>
											<option value="t">{{ trans('all.tidak') }}</option>
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
											<option value=""></option>
											<option value="y">{{ trans('all.ya') }}</option>
											<option value="t">{{ trans('all.tidak') }}</option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan=2>
										<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
										<button type="button" id="kembali" onclick="return ke('../alasanmasukkeluar')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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