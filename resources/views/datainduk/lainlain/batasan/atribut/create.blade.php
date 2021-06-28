@extends('layouts.master')
@section('title', trans('all.batasan'))
@section('content')

  <style>
  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
  }
  td{
      padding:5px;
  }
  span{
      cursor: pointer;
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
        
		var namabatasan = $("#namabatasan").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
            return false;
		@endif

		if(namabatasan == ""){
			alertWarning("{{ trans('all.namakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#namabatasan'));
            });
            return false;
		}
	}

  function switchmenu(menu,opsi){
    if(opsi == "id_klik"){
      if($("#"+menu).prop('checked')){
        $("#"+menu).prop('checked', true);
      }else{
        $("#"+menu).prop('checked', false);
      }
    }
    else
    if(opsi == "class_klik"){
      if($("#"+menu).prop('checked')){
        $("#"+menu).prop('checked', false)
      }else{
        $("#"+menu).prop('checked', true);
      }
    }
    else
    if(opsi == "manipulasidata_klik"){
      if ($('#'+menu+'manipulasidata').is(':disabled')) {
        return false;
      }
      if($("#"+menu).prop('checked')){
        $("#"+menu).prop('checked', false);
      }else{
        $("#"+menu).prop('checked', true);
      }
    }
  }
    </script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.atribut') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.lainlain') }}</li>
        <li>{{ trans('all.batasan') }}</li>
        <li>{{ trans('all.atribut') }}</li>
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
          	<form action="{{ url('datainduk/lainlain/batasanatribut') }}" method="post" onsubmit="return validasi()">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <table width="480px">
                <tr>
                    <td width="140px">{{ trans('all.nama') }}</td>
                    <td>
                        <input type="text" class="form-control" autofocus autocomplete="off" name="namabatasan" id="namabatasan" maxlength="100">
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
                  <td valign="top" style="padding-top: 7px">{{ trans('all.pekerjaankategori') }}</td>
                  <td style="float: left;">
                    <table id="tabelpekerjaankategori"></table>
                    <button type="button" class="btn btn-success" onclick="return aturPekerjaanKategori()"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturpekerjaankategori') }}</button>
                    <button type="button" style="display:none" id="buttonmodalpekerjaankategori" data-toggle="modal" data-target="#myModalpekerjaankategori"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturpekerjaankategori') }}</button><br>
                    <span id="pekerjaankategoriarea"></span>
                  </td>
                </tr>
                <tr>
                    <td colspan=2>
                      <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                      <button type="button" id="kembali" onclick="return ke('../batasanatribut')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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
          @if(isset($atribut) && $atribut != '')
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

  <!-- Modal tambah pekerjaankategori-->
  <div class="modal fade" id="myModalpekerjaankategori" role="dialog" tabindex='-1'>
    <div class="modal-dialog modal-sm">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" id='closemodalpekerjaankategori' data-dismiss="modal">&times;</button>
          <h4 class="modal-title">{{ trans('all.pekerjaankategori') }}</h4>
        </div>
        <div class="modal-body" style="max-height:480px;overflow: auto;">
            <div class="col-md-12">
              <input type="checkbox" id="semuapekerjaankategori" value="1" onclick="checkboxallclick('semuapekerjaankategori','pekerjaankategoripopup')">&nbsp;&nbsp;
              <span style="margin:0" id="spansemuapekerjaankategori" onclick="spanallclick('semuapekerjaankategori','pekerjaankategoripopup')"><strong>{{ trans('all.pilihsemua') }}</strong></span>
              <table>
                @if($datapekerjaankategori != '')
                  @foreach($datapekerjaankategori as $key)
                    <tr>
                      <td style="width:20px;padding:2px" valign="top">
                        <input type="checkbox" onchange="return checkAllAttr('pekerjaankategoripopup','semuapekerjaankategori')" class="pekerjaankategoripopup" name="pekerjaankategori_{{ $key->id }}" id="pekerjaankategoripopup{{ $key->id }}" value="{{ $key->id }}">
                      </td>
                      <td style="padding: 2px;">
                        <span id="spanpekerjaankategori_{{ $key->id }}" onclick="spanClick('pekerjaankategoripopup{{ $key->id }}')" pekerjaankategori="{{ $key->id }}">{{ $key->nama }}</span>
                      </td>
                    </tr>
                  @endforeach
                @endif
              </table>
            </div>
        </div>
        <div class="modal-footer">
          <table width="100%">
            <tr>
              <td style="padding:0;align:right">
                <button class="btn btn-primary" id="tambahpekerjaankategori"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</button>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal tambah pekerjaankategori-->

  <script>
  function aturatribut(){
    @if(count($atributs) > 0)
      $("#buttonmodalatribut").trigger('click');
    @else
      alertWarning("{{ trans('all.nodata') }}");
    @endif
    return false;
  }

  function aturPekerjaanKategori(){
    @if(count($datapekerjaankategori) > 0)
      $("#buttonmodalpekerjaankategori").trigger('click');
    @else
      alertWarning("{{ trans('all.nodata') }}");
    @endif
    return false;
  }

  $(function(){
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

    $("#tambahpekerjaankategori").click(function() {
        $("#tabelpekerjaankategori").html("");
        $("#pekerjaankategoriarea").html("");
        var pekerjaankategori = document.getElementsByClassName("pekerjaankategoripopup");
        for(var i = 0; i< pekerjaankategori.length; i++){
            if(pekerjaankategori[i].checked == true){
                $("#pekerjaankategoriarea").append("<input type='hidden' name='pekerjaankategori[]' value='"+pekerjaankategori[i].value+"'>");
                $("#tabelpekerjaankategori").append("</tr><tr><td style='padding:2px'>"+$('#spanpekerjaankategori_'+pekerjaankategori[i].value).html()+"</td></tr>");
            }
        }
        $("#closemodalpekerjaankategori").trigger("click");
    });
  });
  </script>

@stop