@extends('layouts.master')
@section('title', trans('all.aktivitas'))
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

		setJenisInputan();
	});

	function validasi(){
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');

		var pertanyaan = $("#pertanyaan").val();
		var jenisinputan = $("#jenisinputan").val();
		var panjangkarakter = $("#panjangkarakter").val();
		var rentangnilaidari = $("#rentangnilaidari").val();
		var rentangnilaisampai = $("#rentangnilaisampai").val();

		@if(!Session::has('conf_webperusahaan'))
		alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
				function() {
					aktifkanTombol();
				});
		return false;
		@endif

		if(pertanyaan === ""){
			alertWarning("{{ trans('all.pertanyaan').' '.trans('all.sa_kosong') }}",
					function() {
						aktifkanTombol();
						setFocus($('#pertanyaan'));
					});
			return false;
		}

		if((jenisinputan === 'karakter' || jenisinputan === 'karakterpanjang')){
			if(panjangkarakter === '') {
				alertWarning("{{ trans('all.panjangkarakter').' '.trans('all.sa_kosong') }}",
						function () {
							aktifkanTombol();
							setFocus($('#pertanyaan'));
						});
				return false;
			}

			if (cekAlertAngkaValid(panjangkarakter,0,9999,2,"{{trans('all.panjangkarakter')}}",
					function() {
						aktifkanTombol();
						setFocus($('#panjangkarakter'));
					}
			)===false) return false;
		}

		if(jenisinputan === 'angka' || jenisinputan === 'desimal'){
			if(rentangnilaidari !== '') {
				if (cekAlertAngkaValid(rentangnilaidari,0,9999,2,"{{trans('all.panjangkarakter')}}",
						function() {
							aktifkanTombol();
							setFocus($('#rentangnilaidari'));
						}
				)===false) return false;

				if(rentangnilaisampai === '') {
					alertWarning("{{ trans('all.rentangnilaisampai').' '.trans('all.sa_kosong') }}",
							function () {
								aktifkanTombol();
								setFocus($('#rentangnilaisampai'));
							});
					return false;
				}
			}

			if(rentangnilaisampai !== '') {
				if (cekAlertAngkaValid(rentangnilaisampai,0,9999,2,"{{trans('all.rentangnilaisampai')}}",
						function() {
							aktifkanTombol();
							setFocus($('#rentangnilaisampai'));
						}
				)===false) return false;

				if(rentangnilaidari === '') {
					alertWarning("{{ trans('all.rentangnilaidari').' '.trans('all.sa_kosong') }}",
							function () {
								aktifkanTombol();
								setFocus($('#rentangnilaidari'));
							});
					return false;
				}
			}
		}

		if(jenisinputan === 'checkbox' || jenisinputan === 'radiobutton' || jenisinputan === 'combobox'){
			var isempty = false;
			if($('.multiple').length > 0) {
				var nonempty = $('.multiple').filter(function() {
					return this.value != ''
				});
				if(nonempty.length === 0){
					isempty = true;
				}
				// $('.multiple').each(function (i, e) {
				//     console.log($(e).val(), i);
				//     if($(e).val() === ''){
				//         isempty = true;
				//     }
				// });
			}else{
				isempty = true;
			}
			if(isempty){
				alertWarning("{{ trans('all.pilihan').' '.trans('all.sa_kosong') }}",
						function () {
							aktifkanTombol();
							setFocus($('#rentangnilaisampai'));
						});
				return false;
			}
		}
	}

	var i = 99999;
	function addPilihan(){
		i++;
		$('#tabpilihan').append("<tr id='addr_keterangan"+i+"'>" +
									"<td style=padding-left:0px;>" +
										"<input autocomplete='off' name='multiple[]' class='form-control multiple' type='text' id='keterangan"+i+"' maxlength='255'>" +
									"</td>" +
									"<td style=padding-left:0px;width:20px;float:left;margin-top:4px>" +
										"<button type='button' onclick='deletePilihan("+i+")' title='{{ trans('all.hapus') }}' class='btn btn-danger glyphicon glyphicon-remove row-remove'></button>"+
									"</td>" +
								"</tr>");
		document.getElementById('keterangan'+i).focus();
	}

	function deletePilihan(i){
		$("#addr_keterangan"+i).remove();
		i--;
	}

	function setJenisInputan() {
		var jenisinputan = $('#jenisinputan').val();
		$('#tr_multiple').css('display', 'none');
		$('#tr_karakterpanjang').css('display', 'none');
		$('#tr_rentangnilai').css('display', 'none');

		if(jenisinputan === 'karakter' || jenisinputan === 'karakterpanjang'){
			$('#tr_karakterpanjang').css('display', '');
		}

		if(jenisinputan === 'angka' || jenisinputan === 'desimal'){
			$('#tr_rentangnilai').css('display','');
		}

		if(jenisinputan === 'checkbox' || jenisinputan === 'radiobutton' || jenisinputan === 'combobox'){
			$('#tr_multiple').css('display','');
		}

	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.aktivitas') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
          <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.aktivitas') }}</li>
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
						<td width="120px">{{ trans('all.pertanyaan') }}</td>
						<td>
							<textarea class="form-control" rows="4" autofocus autocomplete="off" name="pertanyaan" id="pertanyaan">{{$data->pertanyaan}}</textarea>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.jenisinputan') }}</td>
						<td style="float:left">
							<select class="form-control" id="jenisinputan" name="jenisinputan" onchange="setJenisInputan()">
								<option value="" disabled>--{{ trans('all.pilihantunggal') }}--</option>
								<option value="karakter" @if($data->jenisinputan == 'karakter') selected @endif>{{ trans('all.karakter') }}</option>
								<option value="karakterpanjang" @if($data->jenisinputan == 'karakterpanjang') selected @endif>{{ trans('all.karakterpanjang') }}</option>
								<option value="angka" @if($data->jenisinputan == 'angka') selected @endif>{{ trans('all.angka') }}</option>
								<option value="desimal" @if($data->jenisinputan == 'desimal') selected @endif>{{ trans('all.desimal') }}</option>
								<option value="tanggaldanjam" @if($data->jenisinputan == 'tanggaldanjam') selected @endif>{{ trans('all.tanggaldanjam') }}</option>
								<option value="tanggal" @if($data->jenisinputan == 'tanggal') selected @endif>{{ trans('all.tanggal') }}</option>
								<option value="jam" @if($data->jenisinputan == 'jam') selected @endif>{{ trans('all.jam') }}</option>
								<option value="" disabled>--{{ trans('all.pilihanmultiple') }}--</option>
								<option value="checkbox" @if($data->jenisinputan == 'checkbox') selected @endif>{{ trans('all.ceklist') }}</option>
								<option value="radiobutton" @if($data->jenisinputan == 'radiobutton') selected @endif>{{ trans('all.opsional') }}</option>
								<option value="combobox" @if($data->jenisinputan == 'combobox') selected @endif>{{ trans('all.listpilihan') }}</option>
							</select>
						</td>
					</tr>
					<tr id="tr_multiple" style="display: none">
						<td valign=top style="padding-top:15px">{{ trans('all.pilihan') }}</td>
						<td>
							<table width=100% id='tabpilihan'>
								@if($dataaktivitasmultiple != '')
									<?php $i=0; ?>
									@foreach($dataaktivitasmultiple as $key)
										<tr id='addr_keterangan{{$i}}'>
											<td style=padding-left:0px;>
												<input autocomplete='off' value="{{$key->keterangan}}" name='multiple[]' class='form-control multiple' type='text' id='keterangan{{$i}}' maxlength='255'>
											</td>
											<td style=padding-left:0px;width:20px;float:left;margin-top:4px>
												<button type='button' onclick='deletePilihan({{$i}})' title='{{ trans('all.hapus') }}' class="btn btn-danger glyphicon glyphicon-remove row-remove"></button>
											</td>
										</tr>
										<?php $i++; ?>
									@endforeach
								@endif
							</table>
							<table>
								<tr>
									<td style='padding-left:0px;' colspan=2>
										<a id="tambahpilihan" title="{{ trans('all.tambah') }}" onclick='addPilihan()' class="btn btn-success glyphicon glyphicon-plus"></a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr id="tr_karakterpanjang" style="display: none">
						<td>{{ trans('all.panjangkarakter') }}</td>
						<td style="float:left"><input size="10" value="{{$data->panjangkarakter}}" type="text" onkeypress="return onlyNumber(0,event)" class="form-control" name="panjangkarakter" id="panjangkarakter" maxlength="4"></td>
					</tr>
					<tr id="tr_rentangnilai" style="display: none">
						<td>{{ trans('all.rentangnilai') }}</td>
						<td>
							<table>
								<tr>
									<td style="float:left"><input value="{{$data->rentangnilaidari}}" size="10" onkeypress="return onlyNumber(2,event)" type="text" class="form-control" name="rentangnilaidari" id="rentangnilaidari" maxlength="3"></td>
									<td style="padding:5px">-</td>
									<td style="float:left"><input value="{{$data->rentangnilaisampai}}" size="10" type="text" onkeypress="return onlyNumber(2,event)" class="form-control" name="rentangnilaisampai" id="rentangnilaisampai" maxlength="3"></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.harusdiisi') }}</td>
						<td style="float:left">
							<select class="form-control" id="harusdiisi" name="harusdiisi">
								<option value="y" @if($data->harusdiisi == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->harusdiisi == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.digunakan') }}</td>
						<td style="float:left">
							<select class="form-control" id="digunakan" name="digunakan">
								<option value="y" @if($data->digunakan == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->digunakan == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../aktivitas')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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