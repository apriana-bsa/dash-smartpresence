@extends('layouts.master')
@section('title', trans('all.tvgroup'))
@section('content')

	<style>
	td{
		padding:5px;
	}

    .sortcutpilihan{
        display: inline-block;
        padding: 5px;
        margin-top: 10px;
    }
	</style>
    <script src="{{ asset('lib/js/jscolor/jscolor.js') }}"></script>
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

        pilihJenis();

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

	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');

        var nama = $("#nama").val();
        var judul = $("#judul").val();

        @if(Session::has('conf_webperusahaan'))
        @else
            alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
                function() {
                    aktifkanTombol();
                }
            );
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

        if(judul == ""){
            alertWarning("{{ trans('all.judulkosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#judul'));
                });
            return false;
        }
	}

	function pilihJenis(){
	    var jenis = $('#jenis').val();
	    var data = '<option value="[pegawai]nama">[pegawai]nama</option>';
        data += '<option value="[pegawai]pin">[pegawai]pin</option>';
        @if($dataatribut != '')
            @foreach($dataatribut as $key)
                data += '<option value="[atribut]{{ $key->id }}">[atribut]{{ $key->atribut }}</option>';
            @endforeach
        @endif
        @if($dataatributvariable != '')
            @foreach($dataatributvariable as $key)
                data += '<option value="[atributvariable]{{ $key->id }}">[atributvariable]{{ $key->atribut }}</option>';
            @endforeach
        @endif
	    if(jenis == 'terlambat'){
            data += '<option value="[terlambat]durasi">[terlambat]durasi</option>';
        }else if(jenis == 'pulangawal'){
            data += '<option value="[pulangawal]durasi">[pulangawal]durasi</option>';
        }else if(jenis == 'ijintidakmasuk'){
            data += '<option value="[ijintidakmasuk]keterangan">[ijintidakmasuk]keterangan</option>';
            data += '<option value="[ijintidakmasuk]kategori">[ijintidakmasuk]kategori</option>';
        }else if(jenis == 'kehadiranterbaik'){
            data += '<option value="[kehadiranterbaik]peringkat">[kehadiranterbaik]peringkat</option>';
            data += '<option value="[kehadiranterbaik]masukkerja">[kehadiranterbaik]masukkerja</option>';
            data += '<option value="[kehadiranterbaik]lamakerja">[kehadiranterbaik]lamakerja</option>';
            data += '<option value="[kehadiranterbaik]terlambat">[kehadiranterbaik]terlambat</option>';
            data += '<option value="[kehadiranterbaik]terlambatlama">[kehadiranterbaik]terlambatlama</option>';
            data += '<option value="[kehadiranterbaik]pulangawal">[kehadiranterbaik]pulangawal</option>';
            data += '<option value="[kehadiranterbaik]pulangawallama">[kehadiranterbaik]pulangawallama</option>';
            data += '<option value="[kehadiranterbaik]lamalembur">[kehadiranterbaik]lamalembur</option>';
        }else if(jenis == 'kehadiranterburuk'){
            data += '<option value="[kehadiranterburuk]peringkat">[kehadiranterburuk]peringkat</option>';
            data += '<option value="[kehadiranterburuk]masukkerja">[kehadiranterburuk]masukkerja</option>';
            data += '<option value="[kehadiranterburuk]lamakerja">[kehadiranterburuk]lamakerja</option>';
            data += '<option value="[kehadiranterburuk]terlambat">[kehadiranterburuk]terlambat</option>';
            data += '<option value="[kehadiranterburuk]terlambatlama">[kehadiranterburuk]terlambatlama</option>';
            data += '<option value="[kehadiranterburuk]pulangawal">[kehadiranterburuk]pulangawal</option>';
            data += '<option value="[kehadiranterburuk]pulangawallama">[kehadiranterburuk]pulangawallama</option>';
            data += '<option value="[kehadiranterburuk]lamalembur">[kehadiranterburuk]lamalembur</option>';
        }else if(jenis == 'logabsen'){
            data += '<option value="[riwayatpresensi]waktu">[riwayatpresensi]waktu</option>';
            data += '<option value="[riwayatpresensi]masukkeluar">[riwayatpresensi]masukkeluar</option>';
        }
        $('.fielddata').html('').html(data);
    }

    function aturatribut(){
        @if(count($dataatributnilai) > 0)
          $("#buttonmodalatribut").trigger('click');
        @else
          alertWarning("{{ trans('all.nodata') }}");
        @endif
            return false;
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.tvgroup') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.pengaturan') }}</li>
            <li>{{ trans('all.node') }}</li>
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
          	<form action="{{ url('pengaturan/tvgroup') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="100%">
                    <tr>
                        <td width="200px">{{ trans('all.nama') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="20" autofocus autocomplete="off" name="nama" id="nama" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.judul') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="20" autofocus autocomplete="off" name="judul" id="judul" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.jenis') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="jenis" id="jenis" onchange="return pilihJenis()">
                                <option value="terlambat">{{ trans('all.terlambat') }}</option>
                                <option value="pulangawal">{{ trans('all.pulangawal') }}</option>
                                <option value="ijintidakmasuk">{{ trans('all.ijintidakmasuk') }}</option>
                                <option value="kehadiranterbaik">{{ trans('all.kehadiranterbaik') }}</option>
                                <option value="kehadiranterburuk">{{ trans('all.kehadiranterburuk') }}</option>
                                <option value="belumabsen">{{ trans('all.belumabsen') }}</option>
                                <option value="logabsen">{{ trans('all.riwayatpresensi') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.baris1_label') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="50" autocomplete="off" name="baris1_label" id="baris1_label" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.baris1_data') }}</td>
                        <td style="float:left">
                            <select name="baris1_data" class="form-control fielddata"></select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.baris2_label') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="50" autocomplete="off" name="baris2_label" id="baris2_label" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.baris2_data') }}</td>
                        <td style="float:left">
                            <select name="baris2_data" class="form-control fielddata"></select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.baris3_label') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="50" autocomplete="off" name="baris3_label" id="baris3_label" maxlength="50">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.baris3_data') }}</td>
                        <td style="float:left">
                            <select name="baris3_data" class="form-control fielddata"></select>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding:5px;'>{{ trans('all.warna_background') }}</td>
                        <td style='padding:5px;float:left'>
                            <input type="text" class="form-control color" size="7" autocomplete="off" value="1b1464" name="warna_background" id="warna_background" maxlength="10">
                        </td>
                    </tr>
                    <tr>
                        <td style='padding:5px;'>{{ trans('all.warna_teks') }}</td>
                        <td style='padding:5px;float:left'>
                            <input type="text" class="form-control color" size="7" autocomplete="off" value="FFFFFF" name="warna_teks" id="warna_teks" maxlength="10">
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
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../tvgroup')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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
        <div class="modal-dialog @if(count($dataatributnilai)<=1) modal-sm @elseif(count($dataatributnilai)==2) modal-md @else modal-lg @endif">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('all.atribut') }}</h4>
                </div>
                <div class="modal-body" style="max-height:480px;overflow: auto;">
                    @if(isset($dataatributnilai) && $dataatributnilai != '')
                        @for($i=0;$i<count($dataatributnilai);$i++)
                            <div class="@if(count($dataatributnilai)<=1) col-md-12 @elseif(count($dataatributnilai)==2) col-md-6 @else col-md-4 @endif">
                                <span style="margin-left:-10px">
                                    <input type="checkbox" class="atributpopup" id="semuaatribut_{{ $dataatributnilai[$i]['idatribut'] }}" value="{{ $dataatributnilai[$i]['idatribut'] }}" onclick="checkboxallclick('semuaatribut_{{ $dataatributnilai[$i]['idatribut'] }}','attr_{{ $dataatributnilai[$i]['idatribut'] }}')">&nbsp;&nbsp;<span style="margin:0" id="spansemuaatribut_{{ $dataatributnilai[$i]['idatribut'] }}" onclick="spanallclick('semuaatribut_{{ $dataatributnilai[$i]['idatribut'] }}','attr_{{ $dataatributnilai[$i]['idatribut'] }}')"><strong>{{ $dataatributnilai[$i]['atribut'] }}</strong></span>
                                </span>
                                <table>
                                    @foreach($dataatributnilai[$i]['atributnilai'] as $key)
                                        <tr>
                                            <td style="width:20px;padding:2px" valign="top">
                                                <input type="checkbox" @if($key->enable == 0) disabled @endif idatribut="{{ $key->idatribut }}" onchange="return checkAllAttr('attr_{{ $key->idatribut }}','semuaatribut_{{ $key->idatribut }}')" class="atributpopup attr_{{ $key->idatribut }}" name="atribut_{{ $key->idatribut }}" id="atributpopup{{ $key->id }}" value="{{ $key->id }}">
                                            </td>
                                            <td style="padding: 2px;">
                                                <span id="attrpopup_atribut{{ $key->id }}" onclick="spanClick('atributpopup{{ $key->id }}')" atribut="{{ $dataatributnilai[$i]['atribut'] }}">{{ $key->nilai }}</span>
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