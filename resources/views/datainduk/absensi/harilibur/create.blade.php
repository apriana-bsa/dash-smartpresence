@extends('layouts.master')
@section('title', trans('all.harilibur'))
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
    
    $(function(){

        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
    
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });

        setTimeout(function(){ $('#tanggalawal').focus(); },200);

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
                    _flagparent = false;
                    _parent = "<tr><td style='padding:2px'><b>"+$("#spansemuaatribut_"+atribut[i].value).html()+"</b></td></tr>";
                }
            }
            $("#closemodal").trigger("click");
        });

        $("#tambahagama").click(function(){

            var agama = document.getElementsByClassName("agamapopup");

            $("#tabelagama").html("");
            $("#agamaarea").html("");

            var _parent = "";
            var nilai = "";
            var _flagparent = false;
            for(var i=0; i<agama.length; i++) {
                if (agama[i].id.substring(0,12)!="semuaagama") {
                    if (document.getElementById("agamapopup"+agama[i].value).checked) {
                        if (_flagparent == false) {
                            _flagparent = true;
                            $("#tabelagama").append(_parent);
                        }
                        nilai = $("#attrpopup_agama"+agama[i].value).html();
                        $("#agamaarea").append("<input type='hidden' name='agama[]' value='"+agama[i].value+"'>");
                        $("#tabelagama").append("</tr><tr><td style='padding:2px;padding-left:10px'>"+nilai+"</td></tr>");
                    }
                }
            }
            $("#closemodalagama").trigger("click");
        });
    });
    
	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var tanggalawal = $("#tanggalawal").val();
        var tanggalakhir = $("#tanggalakhir").val();
        var keterangan = $("#keterangan").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
                aktifkanTombol();
            });
            return false;
        @endif

        if(tanggalawal == ""){
            alertWarning("{{ trans('all.tanggalkosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#tanggalawal'));
            });
            return false;
        }

        if(tanggalakhir == ""){
            alertWarning("{{ trans('all.tanggalkosong') }}",
                    function() {
                    aktifkanTombol();
                    setFocus($('#tanggalakhir'));
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

        if(cekSelisihTanggal(tanggalawal,tanggalakhir) == true){
            alertWarning("{{ trans('all.selisihharimaksimal31') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#tanggalakhir'));
                });
            return false;
        }
        
        if(keterangan == ""){
        alertWarning("{{ trans('all.keterangankosong') }}",
                function() {
                aktifkanTombol();
                setFocus($('#keterangan'));
                });
        return false;
        }
	}

  function aturatribut(){
    @if(count($atributs) > 0)
      $("#buttonmodalatribut").trigger('click');
    @else
      alertWarning("{{ trans('all.nodata') }}");
    @endif
    return false;
  }

  function aturagama(){
    @if($agama != '')
        @if(count($agama) > 0)
        $("#buttonmodalagama").trigger('click');
        @else
        alertWarning("{{ trans('all.nodata') }}");
        @endif
    @endif
    return false;
  }
  </script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.harilibur') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.harilibur') }}</li>
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
          	<form action="{{ url('datainduk/absensi/harilibur') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="110px">{{ trans('all.tanggal') }}</td>
                        <td>
                            <table>
                              <tr>
                                <td style="padding-left:0px;padding-bottom: 0px;float:left">
                                  <input type="text" class="form-control date" size="11" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalawal" id="tanggalawal" maxlength="10">
                                </td>
                                <td style="padding-bottom: 0px">-</td>
                                <td style="padding-bottom: 0px;padding-right: 0px;float:left">
                                  <input type="text" class="form-control date" size="11" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalakhir" id="tanggalakhir" maxlength="10">
                                </td>
                              </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.keterangan') }}</td>
                      <td>
                        <textarea name="keterangan" class="form-control" id="keterangan" rows="4" style="resize:none"></textarea>
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
                        <td valign="top" style="padding-top: 7px">{{ trans('all.agama') }}</td>
                        <td style="float: left;">
                        <table id="tabelagama">
                        </table>
                        <button type="button" class="btn btn-success" onclick="return aturagama()"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturagama') }}</button>
                        <button type="button" style="display:none" id="buttonmodalagama" data-toggle="modal" data-target="#modalagama"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturagama') }}</button><br>
                        <span id="agamaarea"></span>
                      </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../harilibur')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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
                                                <span id="attrpopup_atribut{{ $key->id }}" onclick="spanClick('atributpopup{{ $key->id }}')" atribut="{{ $atribut[$i]['atribut'] }}">{{ $key->nilai }}</span>
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

    <!-- Modal tambah agama-->
    <div class="modal fade" id="modalagama" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-sm">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodalagama' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('all.agama') }}</h4>
                </div>
                <div class="modal-body" style="max-height:480px;overflow: auto;">
                    @if(isset($agama) && $agama != '')
                        <div class="col-md-4">
                            <table>
                                <tr>
                                    <td style="width:20px;padding:2px" valign="top"><input type="checkbox" class="agamapopup" id="semuaagama" onclick="checkboxallclick('semuaagama','semuaagama')"></td>
                                    <td style="padding: 2px;"><span style="margin:0" id="spansemuaagama" onclick="spanallclick('semuaagama','semuaagama')"><strong>{{ trans('all.semua') }}</strong></span></td>
                                </tr>
                                @foreach($agama as $key)
                                    <tr>
                                        <td style="width:20px;padding:2px" valign="top">
                                            <input type="checkbox" idagama="{{ $key->id }}" onchange="return checkAllAttr('semuaagama','semuaagama')" class="agamapopup semuaagama" name="agama_{{ $key->id }}" id="agamapopup{{ $key->id }}" value="{{ $key->id }}">
                                        </td>
                                        <td style="padding: 2px;">
                                            <span id="attrpopup_agama{{ $key->id }}" onclick="spanClick('agamapopup{{ $key->id }}')" agama="{{ $key->agama }}">{{ $key->agama }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <table width="100%">
                        <tr>
                            <td style="padding:0;align:right">
                                <button class="btn btn-primary" id="tambahagama"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal tambah agama-->

@stop