@extends('layouts.master')
@section('title', trans('all.aktivitaskategori'))
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

		$("#tambahatribut").click(function(){

			var atribut = document.getElementsByClassName("atributpopup");

			$("#tabelatribut").html("");
			$("#atributarea").html("");

			var _parent = "";
			var nilai = "";
			var _flagparent = false;
			for(var i=0; i<atribut.length; i++) {
				if (atribut[i].id.substring(0,12)!="semuaatribut") {
					if (document.getElementById("atributpopup"+atribut[i].value).checked) {
						if (_flagparent == false) {
							_flagparent = true;
							$("#tabelatribut").append(_parent);
						}
						nilai = $("#attrpopup_atribut"+atribut[i].value).html();
						$("#atributarea").append("<input type='hidden' name='atribut[]' value='"+atribut[i].value+"'>");
						$("#tabelatribut").append("</tr><tr><td style='padding:2px;padding-left:10px'>"+nilai+"</td></tr>");
					}
				} else {
					var _flagparent = false;
					_parent = "<tr><td style='padding:2px'><b>"+$("#spansemuaatribut_"+atribut[i].value).html()+"</b></td></tr>";
				}
			}
			$("#closemodal").trigger("click");
		});
	});

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

	function aturatribut(){
		@if(count($arratribut) > 0)
		$("#buttonmodalatribut").trigger('click');
		@else
		alertWarning("{{ trans('all.nodata') }}");
		@endif
				return false;
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.aktivitaskategori') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
          <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.aktivitaskategori') }}</li>
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
						<td width=110px>{{ trans('all.nama') }}</td>
						<td>
							<input type="text" class="form-control" value="{{ $data->nama }}" autofocus autocomplete="off" name="nama" id="nama" maxlength="100">
						</td>
					</tr>
					<tr>
						<td valign="top" style="padding-top: 7px">{{ trans('all.atribut') }}</td>
						<td style="float: left;">
							<table id="tabelatribut">
								@if(isset($arratribut) && $arratribut != '')
									@for($i=0;$i<count($arratribut);$i++)
										@if($arratribut[$i]['pakaiheader'] > 0)
											<tr>
												<td style="padding:2px"><b>{{ $arratribut[$i]['atribut'] }}</b></td>
											</tr>
										@endif
										@foreach($arratribut[$i]['atributnilai'] as $key)
											@if( $key->dipilih == 1)
												<tr>
													<td style="padding:2px;padding-left:10px;">{{ $key->nilai }}</td>
												</tr>
											@endif
										@endforeach
									@endfor
								@endif
							</table>
							<button type="button" class="btn btn-success" onclick="return aturatribut()"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturatribut') }}</button>
							<button type="button" style="display:none" id="buttonmodalatribut" data-toggle="modal" data-target="#myModal"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturatribut') }}</button><br>
							<span id="atributarea">
								@if(isset($arratribut) && $arratribut != '')
									@for($i=0;$i<count($arratribut);$i++)
										@foreach($arratribut[$i]['atributnilai'] as $key)
											@if( $key->dipilih == 1)
												<input type='hidden' name='atribut[]' value='{{ $key->id }}'>
											@endif
										@endforeach
									@endfor
								@endif
							</span>
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
							<button type="button" id="kembali" onclick="return ke('../../aktivitaskategori')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
						</td>
					</tr>
				</table>
			</form>
          </div>
        </div>
      </div>
    </div>
  </div>

	<!-- Modal tambah atribut-->
	<div class="modal fade" id="myModal" role="dialog" tabindex='-1'>
		<div class="modal-dialog @if(count($arratribut)<=1) modal-sm @elseif(count($arratribut)==2) modal-md @else modal-lg @endif">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
					<h4 class="modal-title">{{ trans('all.atribut') }}</h4>
				</div>
				<div class="modal-body" style="max-height:480px;overflow: auto;">
					@if(isset($arratribut) && $arratribut != '')
						@for($i=0;$i<count($arratribut);$i++)
							<div class="@if(count($arratribut)<=1) col-md-12 @elseif(count($arratribut)==2) col-md-6 @else col-md-4 @endif">
								<span style="margin-left:-10px">
									<input type="checkbox" class="atributpopup" id="semuaatribut_{{ $arratribut[$i]['idatribut'] }}" value="{{ $arratribut[$i]['idatribut'] }}" onclick="checkboxallclick('semuaatribut_{{ $arratribut[$i]['idatribut'] }}','attr_{{ $arratribut[$i]['idatribut'] }}')" @if($arratribut[$i]['flag'] == 1) checked @endif>&nbsp;&nbsp;
								</span>
								<span style="margin:0" id="spansemuaatribut_{{ $arratribut[$i]['idatribut'] }}" onclick="spanallclick('semuaatribut_{{ $arratribut[$i]['idatribut'] }}','attr_{{ $arratribut[$i]['idatribut'] }}')"><strong>{{ $arratribut[$i]['atribut'] }}</strong></span>
								<table>
									@foreach($arratribut[$i]['atributnilai'] as $key)
										<tr>
											<td style="width:20px;padding:2px" valign="top">
												<input type="checkbox" @if($key->enable == 0) disabled @endif idatribut="{{ $key->idatribut }}" onchange="return checkAllAttr('attr_{{ $key->idatribut }}','semuaatribut_{{ $key->idatribut }}')" class="atributpopup attr_{{ $key->idatribut }}" name="atribut_{{ $key->idatribut }}" id="atributpopup{{ $key->id }}" value="{{ $key->id }}"  @if($key->dipilih == 1) checked @endif>
											</td>
											<td style="padding: 2px;">
												<span id="attrpopup_atribut{{ $key->id }}" onclick="spanClick('atributpopup{{ $key->id }}')" atribut="{{ $arratribut[$i]['atribut'] }}">{{ $key->nilai }}</span>
											</td>
										</tr>
									@endforeach
								</table>
							</div>
						@endfor
					@endif
				</div>
				<div class="modal-footer">
					<table width="100%">
						<tr>
							<td style="padding:0;align:right">
								<button class="btn btn-primary" id="tambahatribut"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</button>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
	<!-- Modal tambah atribut-->

@stop