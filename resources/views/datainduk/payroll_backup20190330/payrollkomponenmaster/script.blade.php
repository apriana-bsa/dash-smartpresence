@extends('layouts.master')
@section('title', trans('all.payrollkomponenmaster'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	</style>
	<script>
	$(function(){
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

		$('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
		  $(this).datepicker('hide');
		});
		
		$('.date').mask("00/00/0000", {clearIfNotMatch: true});

		$('.money').mask("#.##0.##0.##0.##0", {reverse: true});
	});
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.payrollkomponenmaster').' ('.$data->nama.')' }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.payroll') }}</li>
        <li>{{ trans('all.payrollkomponenmaster') }}</li>
        <li class="active"><strong>{{ trans('all.script') }}</strong></li>
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
          	<form action="" id="form1" method="post" onsubmit="return checkScript(true)">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<table width="100%">
					@if($data->carainput == 'inputmanual')
						<tr class="tr_inputmanual">
							<td valign="top" style="padding-top:10px">{{ trans('all.filter') }}</td>
							<td>
								<div style="padding:0" class="col-lg-12 col-md-12 col-sm-12">
									<textarea class="form-control" rows="10" name="inputmanual_filter" id="inputmanual_filter">{{$data->inputmanual_filter}}</textarea>
								</div>
								<div style="padding:0;padding-top:20px;padding-bottom:10px" class="col-lg-12 col-md-12 col-sm-12">
									<button type="button" onclick="return checkScript(false)" id="checkscript">{{ trans('all.checkscript') }}</button>
								</div>
								<div style="padding-left:0" class="col-lg-12 col-md-12 col-sm-12">
									<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.nilaikembali') }}</b></div>
									<div class="col-lg-12" style="padding-left:0;padding-top:5px;padding-bottom:10px">
										<b title="{{ trans('all.variabelinputminimal') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$batas_bawah","inputmanual_filter")'>$batas_bawah</b>&nbsp;
										<b title="{{ trans('all.variabelinputmaksimal') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$batas_atas","inputmanual_filter")'>$batas_atas</b>&nbsp;
									</div>
									<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.variableglobal') }}</b></div>
									<div class="col-lg-12" style="padding-left:0;padding-top:5px;padding-bottom:10px">
										<b title="{{ trans('all.variabelglobaldatapegawai') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$PEGAWAI","inputmanual_filter")'>$PEGAWAI</b>&nbsp;
										<b title="{{ trans('all.variabelglobaldataatributnilai') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$ATRIBUTNILAI","inputmanual_filter")'>$ATRIBUTNILAI</b>&nbsp;
										<b title="{{ trans('all.variabelglobaldataatributvariable') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$ATRIBUTVARIABLE","inputmanual_filter")'>$ATRIBUTVARIABLE</b>&nbsp;
										<b title="{{ trans('all.variabelglobaldatarekapabsen') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$REKAPABSEN","inputmanual_filter")'>$REKAPABSEN</b>&nbsp;
									</div>
									<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.fungsibantuan') }}</b></div>
									<div class="col-lg-12" style="padding-left:0;padding-top:5px;padding-bottom:10px">
										<b title="{{ trans('all.fungsigetscriptpayroll') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("get(... , ...)","inputmanual_filter")'>get(... , ...)</b>&nbsp;
										<b title="{{ trans('all.fungsigetvaluescriptpayroll') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("getvalue(... , ...)","formula")'>getvalue(... , ...)</b>&nbsp;
										<b title="{{ trans('all.fungsigetatributnilaiscriptpayroll') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("getatributnilai(... , ...)","formula")'>getatributnilai(... , ...)</b>&nbsp;
										<b title="{{ trans('all.fungsiinarrayscriptpayroll') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("inarrayi(... , ...)","inputmanual_filter")'>in_arrayi(... , ...)</b>&nbsp;
									</div>
									<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.atributnilai') }}</b></div>
									<div class="col-lg-12" style="padding-left:0;padding-top:5px;padding-bottom:10px">
										@if($dataatribut != '')
											@foreach($dataatribut as $key)
												<b title="{{ $key->nama }}" style='cursor:pointer;margin-bottom:5px' onclick="return atributNilai({{$key->id}},'inputmanual_filter')" class='label pilihan'>{{$key->nama}}</b>&nbsp;&nbsp;
											@endforeach
										@endif
									</div>
									<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.atributvariable') }}</b></div>
									<div class="col-lg-12" style="padding-left:0;padding-top:5px;padding-bottom:10px">
										<b title="{{ trans('all.atributvariable') }}" style='cursor:pointer;margin-bottom:5px' onclick="return atributVariable('inputmanual_filter')" class='label pilihan'>{{ trans('all.atributvariable') }}</b>&nbsp;&nbsp;
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<button id="submit2" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
								<button type="button" id="kembali" onclick="return ke('../../payrollkomponenmaster')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
							</td>
						</tr>
					@elseif($data->carainput == 'formula')
						<tr class="tr_formula">
							<td valign="top" style="padding-top:10px">{{ trans('all.formula') }}</td>
							<td>
								<div style="padding:0" class="col-lg-6 col-md-6 col-sm-12">
									<div style="padding:0" class="col-lg-12 col-md-12 col-sm-12">
										<textarea class="form-control" rows="10" name="formula" id="formula">{{$data->formula}}</textarea>
									</div>
									<div style="padding-left:0;padding-top:10px" class="col-lg-12 col-md-12 col-sm-12">
										<div style="padding:5px;padding-left:0;" class="col-lg-12">
											<input type="checkbox" onchange="return debug()" id="tesdebug">&nbsp;
											<span onclick="spanClick('tesdebug')" style="cursor:pointer"><b>{{ trans('all.debug') }}</b></span>&nbsp;&nbsp;
											<button type="button" onclick="return tesOutput()" class="tesoutput" id="tesoutput">{{ trans('all.tesoutput') }}</button>
										</div>
										<div style="padding:5px;padding-left:0;" class="pilihpegawai col-lg-12">
											<p style="margin-bottom:5px"><b>{{ trans('all.pegawai') }}</b></p>
											<input type="text" class="form-control" autofocus autocomplete="off" name="pegawai" id="pegawai" maxlength="100">
											<script type="text/javascript">
												$(document).ready(function(){
													$("#pegawai").tokenInput("{{ url('tokenpegawai') }}", {
														theme: "facebook",
														tokenLimit: 1
													});
												});
											</script>
										</div>
										<div style="padding:5px;padding-left:0;" class="pilihpegawai col-lg-12">
											<p style="margin-bottom:5px"><b>{{ trans('all.tanggal') }}</b></p>
											<table>
												<tr>
													<td style="padding-left:0px;padding-top:0px;padding-bottom: 0px;float:left">
														<input type="text" class="form-control date" size="11" value="{{ $valuetglawalakhir->tanggalawal }}" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalawal" id="tanggalawal" maxlength="10">
													</td>
													<td style="padding-bottom: 0px;padding-top:0px;">-</td>
													<td style="padding-bottom: 0px;padding-top:0px;padding-right: 0px;float:left">
														<input type="text" class="form-control date" size="11" value="{{ $valuetglawalakhir->tanggalakhir }}" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalakhir" id="tanggalakhir" maxlength="10">
													</td>
												</tr>
											</table>
										</div>
										<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.nilaikembali') }}</b></div>
										<div class="col-lg-12" style="padding-left:0;padding-top:5px;padding-bottom:10px">
											<b title="{{ trans('all.variabelinputminimal') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$batas_bawah","formula")'>$batas_bawah</b>&nbsp;
											<b title="{{ trans('all.variabelinputmaksimal') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$batas_atas","formula")'>$batas_atas</b>&nbsp;
											<b title="{{ trans('all.variabelresult') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$result","formula")'>$result</b>&nbsp;
										</div>
										<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.variableglobal') }}</b></div>
										<div class="col-lg-12" style="padding-left:0;padding-top:5px;padding-bottom:10px">
											<b title="{{ trans('all.variabelglobaldatapegawai') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$PEGAWAI","formula")'>$PEGAWAI</b>&nbsp;
											<b title="{{ trans('all.variabelglobaldataatributnilai') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$ATRIBUTNILAI","formula")'>$ATRIBUTNILAI</b>&nbsp;
											<b title="{{ trans('all.variabelglobaldataatributvariable') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$ATRIBUTVARIABLE","formula")'>$ATRIBUTVARIABLE</b>&nbsp;
											<b title="{{ trans('all.variabelglobaldatarekapabsen') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$REKAPABSEN","formula")'>$REKAPABSEN</b>&nbsp;
											<b title="{{ trans('all.variabelglobaldatacounter') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$COUNTER","formula")'>$COUNTER</b>&nbsp;
											<b title="{{ trans('all.variabelglobaldatapayroll') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("$PAYROLL","formula")'>$PAYROLL</b>&nbsp;
										</div>
										<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.fungsibantuan') }}</b></div>
										<div class="col-lg-12" style="padding-left:0;padding-top:5px;padding-bottom:10px">
											<b title="{{ trans('all.fungsigetscriptpayroll') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("get(... , ...)","formula")'>get(... , ...)</b>&nbsp;
											<b title="{{ trans('all.fungsigetvaluescriptpayroll') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("getvalue(... , ...)","formula")'>getvalue(... , ...)</b>&nbsp;
											<b title="{{ trans('all.fungsigetatributnilaiscriptpayroll') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("getatributnilai(... , ...)","formula")'>getatributnilai(... , ...)</b>&nbsp;
											<b title="{{ trans('all.fungsiinarrayscriptpayroll') }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("inarrayi(... , ...)","formula")'>in_arrayi(... , ...)</b>&nbsp;
										</div>
										<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.atributnilai') }}</b></div>
										<div class="col-lg-12" style="padding-left:0;padding-top:5px;padding-bottom:10px">
											@if($dataatribut != '')
												@foreach($dataatribut as $key)
													<b title="{{ $key->nama }}" style='cursor:pointer;margin-bottom:5px' onclick="return atributNilai({{$key->id}},'formula')" class='label pilihan'>{{$key->nama}}</b>&nbsp;&nbsp;
												@endforeach
											@endif
										</div>
										<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.atributvariable') }}</b></div>
										<div class="col-lg-12" style="padding-left:0;padding-top:5px;padding-bottom:10px">
											<b title="{{ trans('all.atributvariable') }}" style='cursor:pointer;margin-bottom:5px' onclick="return atributVariable('formula')" class='label pilihan'>{{ trans('all.atributvariable') }}</b>&nbsp;&nbsp;
										</div>
										<div class="col-lg-12" style="margin-left:-60px;padding-left:0;padding-top:15px;padding-bottom:10px">
											<button id="submit2" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
											<button type="button" id="kembali" onclick="return ke('../../payrollkomponenmaster')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
										</div>
									</div>
								</div>
								<div class="col-lg-6 col-md-6 col-sm-12">
									<div style="padding-left:0" class="col-lg-12"><b>{{ trans('all.komponenmaster') }}</b></div>
									<div class="col-lg-12" id="formula_komponenmaster" style="padding-left:0;padding-top:5px;padding-bottom:10px">
										@if($datapayrollkomponenmaster != '')
											@foreach($datapayrollkomponenmaster as $key)
												<div style="padding-top:5px">
													<div class="input_komponenmaster" style="display:inline-block">
														<input type="text" size="15" autocomplete="off" placeholder="{{ trans('all.'.$key->tipedata) }}" @if($key->tipedata == 'angka') onkeypress="return onlyNumber(0,event)" @endif idpayrollkomponenmaster="{{$key->id}}" tipedata={{$key->tipedata}} id="input_{{$key->id}}" class="form-control @if($key->tipedata == 'uang') money @endif">
													</div>
													<div style="display:inline-block">
														@if($key->kode != '')
															@if($key->tipedata != 'teks')
																<b title="{{ $key->nama }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("get($PAYROLL,\"{{strtoupper($key->kode)}}\",0)","formula")'>{{strtoupper($key->kode)}}</b>&nbsp;&nbsp;{{ $key->nama }}
															@else
																<b title="{{ $key->nama }}" style='cursor:pointer;margin-bottom:5px' class='label pilihan' onclick='give("get($PAYROLL,\"{{strtoupper($key->kode)}}\",\"\")","formula")'>{{strtoupper($key->kode)}}</b>&nbsp;&nbsp;{{ $key->nama }}
															@endif
														@else
															{{ $key->nama }}
														@endif
													</div>
												</div>
											@endforeach
										@endif
									</div>
								</div>
							</td>
						</tr>
					@endif
				</table>
			</form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <form method="POST" id='formscript'>
	{{ csrf_field() }}
	<input type="hidden" value="" id="script" name="script">
	<input type="hidden" value="" id="idpegawai" name="idpegawai">
	<input type="hidden" value="" id="komponenmaster" name="komponenmaster">
	<input type="hidden" value="" id="s_tanggalawal" name="tanggalawal">
	<input type="hidden" value="" id="s_tanggalakhir" name="tanggalakhir">
	<input type="hidden" value="{{$data->id}}" id="idkomponenmaster" name="idkomponenmaster">
  </form>

  <!-- Modal atribut-->
  <a href="" id="showmodalatribut" data-toggle="modal" data-target="#modalatribut" style="display:none"></a>
  <div class="modal modalatribut fade" id="modalatribut" role="dialog" tabindex='-1'>
	<div class="modal-dialog modal-md">
		<!-- Modal content-->
		<div class="modal-content"></div>
	</div>
  </div>
<!-- Modal atribut-->
<script>
	$(function(){
		debug();
	});

	window.atributVariable=(function(formid){
		$("#showmodalatribut").attr("href", "");
		$("#showmodalatribut").attr("href", "{{ url('atributvariable') }}/"+formid);
		$('#showmodalatribut').trigger('click');
		return false;
	});

	window.atributNilai=(function(idatribut,formid){
		$("#showmodalatribut").attr("href", "");
		$("#showmodalatribut").attr("href", "{{ url('atributnilai') }}/"+idatribut+"/"+formid);
		$('#showmodalatribut').trigger('click');
		return false;
	});

	$('body').on('hidden.bs.modal', '.modalatribut', function () {
		$(this).removeData('bs.modal');
		$("#" + $(this).attr("id") + " .modal-content").empty();
		$("#" + $(this).attr("id") + " .modal-content").append("Loading...");
	});

	function debug(){
		if($("#tesdebug").prop('checked')){
			$('.tesoutput').css('display','');
			$('.pilihpegawai').css('display','');
			$('.input_komponenmaster').css('display','inline-block');
		}else{
			$('.tesoutput').css('display','none');
			$('.pilihpegawai').css('display','none');
			$('.input_komponenmaster').css('display','none');
		}
	}

	function tesOutput(){
		@if($data->carainput == 'inputmanual')
			var script = $('#inputmanual_filter').val();
		@else
			var script = $('#formula').val();
		@endif
		var pegawai = $('#pegawai').val();
		var tanggalawal = $('#tanggalawal').val();
		var tanggalakhir = $('#tanggalakhir').val();
		if(pegawai == ''){
			alertWarning("{{ trans('all.pegawai').' '.trans('all.sa_kosong') }}",
			function() {
				aktifkanTombol();
			});
			return false;
		}

		if(tanggalakhir.split("/").reverse().join("-") < tanggalawal.split("/").reverse().join("-")){
			alertWarning("{{ trans('all.tanggalakhirlebihkecildaritanggalawal') }}",
				function() {
					aktifkanTombol();
					setFocus($('#tanggalakhir'));
				});
			return false;
		}

        {{--if(cekSelisihTanggal(tanggalawal,tanggalakhir) == true){--}}
            {{--alertWarning("{{ trans('all.selisihharimaksimal31') }}",--}}
                {{--function() {--}}
                    {{--aktifkanTombol();--}}
                    {{--setFocus($('#tanggalakhir'));--}}
                {{--});--}}
            {{--return false;--}}
        {{--}--}}
		var komponenmaster = [];
		var i = 0;
		var tipedata = '';
		$('.input_komponenmaster').each(function(){
			//komponenmaster[i] = $(this).find("input").val();
			tipedata = $(this).find("input").attr('tipedata');
			komponenmaster[i] = {};
			komponenmaster[i]['id'] = $(this).find("input").attr('idpayrollkomponenmaster');
			if(tipedata == 'uang'){
				komponenmaster[i]['value'] = $(this).find("input").val().replace(/\./g, "");
			}else{
				komponenmaster[i]['value'] = $(this).find("input").val();
			}
			i++;
		});
		$('#script').val(script);
		$('#idpegawai').val(pegawai);
		$('#komponenmaster').val(JSON.stringify(komponenmaster));
		$('#s_tanggalawal').val(tanggalawal);
		$('#s_tanggalakhir').val(tanggalakhir);
		var dataString = new FormData($('#formscript')[0]);
		$.ajax({
			type: "POST",
			url: '{{ url('payrollkomponenmaster/tesoutput') }}',
			data: dataString,
			async: false,
			cache: false,
			contentType: false,
			processData: false,
			success: function(html){
				alertInfo(html);
			}
		});
	}

	function checkScript(silent){
		@if($data->carainput == 'inputmanual')
			var script = $('#inputmanual_filter').val();
		@else
			var script = $('#formula').val();
		@endif
		var dataString = 'script='+script+'&_token={{ csrf_token() }}';
		var error = false;
		$.ajax({
			type: "POST",
			url: '{{ url('payrollkomponenmaster/checkscript') }}',
			data: dataString,
			cache: false,
			async: false,
			success: function(html){
				if(html != ''){
					alertWarning(html);
					error = true;
				}else{
					if(silent == false){
						alertInfo('OK');
					}
				}
			}
		});
		if(error == true){
			return false;
		}
	}
  </script>
@stop