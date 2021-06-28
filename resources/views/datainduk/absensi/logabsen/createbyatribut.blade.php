@extends('layouts.master')
@section('title', trans('all.menu_logabsen'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	
    span{
        cursor:default;
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
    
    $(function(){
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });
    
        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
    
        $('.jam').inputmask( 'h:s' );

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
                }
                else {
                    var _flagparent = false;
                    _parent = "<tr><td style='padding:2px'><b>"+$("#spansemuaatribut_"+atribut[i].value).html()+"</b></td></tr>";
                }
            }
            $("#closemodal").trigger("click");
        });
    });

    function aturatribut(){
        @if(count($atribut) > 0)
          $("#buttonmodalatribut").trigger('click');
        @else
          alertWarning("{{ trans('all.nodata') }}");
        @endif
            return false;
    }

	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var tanggal = $("#tanggal").val();
        var jam = $("#jam").val();
		var pegawai = $("#pegawai").val();
		var masukkeluar = $("#masukkeluar").val();
		var terhitungkerja = $("#terhitungkerja").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
            return false;
		@endif

		if(tanggal == ""){
			alertWarning("{{ trans('all.waktukosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#tanggal'));
            });
            return false;
		}
        
        if(jam == ""){
            alertWarning("{{ trans('all.waktukosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#jam'));
            });
            return false;
        }
        
        if(pegawai == ""){
            alertWarning("{{ trans('all.pegawaikosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#token-input-pegawai'));
            });
            return false;
        }
        
        if(masukkeluar == ""){
            alertWarning("{{ trans('all.masukkeluarkosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#masukkeluar'));
            });
            return false;
        }
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_logabsen') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.menu_logabsen') }}</li>
        <li class="active"><strong>{{ trans('all.tambahdataberdasarkanatribut') }}</strong></li>
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
          	<form action="{{ url('datainduk/absensi/logabsen/submitcreatebyatribut') }}" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<table width="480px">
					<tr>
						<td width="110px">{{ trans('all.waktu') }}</td>
						<td style="float: left;">
							<input type="text" size="11" value="{{ date('d/m/Y') }}" class="form-control date" autofocus autocomplete="off" name="tanggal" id="tanggal" maxlength="10" placeholder="dd/mm/yyyy">
						</td>
                        <td style="float: left;">
							<input type="text" size="6" value="{{ date('H:i') }}" class="form-control jam" autocomplete="off" name="jam" id="jam" placeholder="hh:mm">
						</td>
					</tr>
					<tr>
                        <td>{{ trans('all.masukkeluar') }}</td>
                        <td colspan="2" style="float:left">
                            <select class="form-control" name="masukkeluar" id="masukkeluar">
                                <option value=""></option>
                                <option value="m">{{ trans('all.masuk') }}</option>
                                <option value="k">{{ trans('all.keluar') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.alasan') }}</td>
                        <td colspan="2" style="float:left">
                            <select class="form-control" name="alasan" id="alasan">
                                <option value=""></option>
                                @foreach($alasanmasukkeluar as $key)
                                    <option value="{{ $key->id }}">{{ $key->alasan }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.flag') }}</td>
                        <td colspan="2" style="float:left">
                            <select class="form-control" name="flag" id="flag">
                                <option value=""></option>
                                <option value="tidak-terlambat">{{ trans('all.tidakterlambat') }}</option>
                                <option value="tidak-pulangawal">{{ trans('all.tidakpulangawal') }}</option>
                                <option value="lembur">{{ trans('all.lembur') }}</option>
                                <option value="tidak-lembur">{{ trans('all.tidaklembur') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td width="110px">{{ trans('all.keterangan') }}</td>
                        <td colspan="2">
                            <textarea type="text" class="form-control" autocomplete="off" name="flagketerangan" id="flagketerangan" maxlength="255" style="resize:none"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" style="padding-top: 7px">{{ trans('all.atribut') }}</td>
                        <td style="float: left;">
                            <table id="tabelatribut">
                            </table>
                            <button type="button" class="btn btn-success" onclick="return aturatribut()"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturatribut') }}</button>
                            <button type="button" style="display:none" id="buttonmodalatribut" data-toggle="modal" data-target="#myModal"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturatribut') }}</button><br>
                            <span id="atributarea"></span>
                        </td>
                    </tr>
					<tr>
						<td colspan=3>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../logabsen')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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
        <div class="modal-dialog @if(count($atribut)<=1) modal-sm @elseif(count($atribut)==2) modal-md @else modal-lg @endif">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('all.atribut') }}</h4>
                </div>
                <div class="modal-body" style="max-height:480px;overflow: auto;">
                    @if(isset($atribut))
                        @for($i=0;$i<count($atribut);$i++)
                            <div class="@if(count($atribut)<=1) col-md-12 @elseif(count($atribut)==2) col-md-6 @else col-md-4 @endif">
                                <input type="checkbox" class="atributpopup" id="semuaatribut_{{ $atribut[$i]['idatribut'] }}" value="{{ $atribut[$i]['idatribut'] }}" onclick="checkboxallclick('semuaatribut_{{ $atribut[$i]['idatribut'] }}','attr_{{ $atribut[$i]['idatribut'] }}')">&nbsp;&nbsp;<span style="margin:0" id="spansemuaatribut_{{ $atribut[$i]['idatribut'] }}" onclick="spanallclick('semuaatribut_{{ $atribut[$i]['idatribut'] }}','attr_{{ $atribut[$i]['idatribut'] }}')"><strong>{{ $atribut[$i]['atribut'] }}</strong></span>
                                <table>
                                    @foreach($atribut[$i]['atributnilai'] as $key)
                                        <tr>
                                            <td style="width:20px;padding:2px" valign="top">
                                                <input type="checkbox" @if($key->enable == 0) disabled @endif idatribut="{{ $key->idatribut }}" onchange="return checkAllAttr('attr_{{ $key->idatribut }}','semuaatribut_{{ $key->idatribut }}')" class="atributpopup attr_{{ $key->idatribut }}" name="atribut_{{ $key->idatribut }}" id="atributpopup{{ $key->id }}" value="{{ $key->id }}">
                                            </td>
                                            <td style="padding: 2px;">
                                                <span id="attrpopup_atribut{{ $key->id }}" @if($key->enable != 0) onclick="spanClick('atributpopup{{ $key->id }}')" @endif atribut="{{ $atribut[$i]['atribut'] }}">{{ $key->nilai }}</span>
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