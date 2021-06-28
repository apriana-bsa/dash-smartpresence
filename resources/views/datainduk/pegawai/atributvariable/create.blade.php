@extends('layouts.master')
@section('title', trans('all.atributvariable'))
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

//        setTimeout(hideFormula(),100);
    });
    
	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var atribut = $("#atribut").val();
		var tabelcarainputan = $("#tabelcarainputan").html();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
                aktifkanTombol();
            });
            return false;
		@endif

		if(atribut == ""){
			alertWarning("{{ trans('all.atributkosong') }}",
            function() {
                  aktifkanTombol();
                  setFocus($('#atribut'));
            });
            return false;
		}
	}

//	function hideFormula(){
//        var carainput = $('#carainput').val();
//        $('#trformula').css('display', 'none');
//        if(carainput == 'otomatis'){
//            $('#trformula').css('display', '');
//        }
//    }

    function pilihTipeData(){
        var tipedata = $('#tipedata').val();
        $('#datatambahan_jumlahkarakter').css('display', 'none');
        $('#datatambahan_regex').css('display', 'none');
        $('#datatambahan_bolehkosong').css('display', 'none');
        $('#datatambahan_range').css('display', 'none');
        $('#datatambahan_decimal').css('display', 'none');
        if(tipedata != ''){
            $('#datatambahan_bolehkosong').css('display', '');
        }
        if(tipedata == 'text') {
            $('#datatambahan_jumlahkarakter').css('display', '');
            $('#datatambahan_regex').css('display', '');
        }else if(tipedata == 'number'){
            $('#datatambahan_range').css('display', '');
            $('#datatambahan_decimal').css('display', '');
        }
    }

    function tambahCaraInputan(){
        var tipedata = $('#tipedata').val();
        var addtext = ''; // untuk preview di atas tombol atur
        $('#i_carainputan_tipedata').val('');
        if(tipedata != '') {
            var tipedatalabel = tipedata == 'text' ? '{{ trans('all.text') }}' : (tipedata == 'date' ? '{{ trans('all.tanggal') }}' : (tipedata == 'datetime' ? '{{ trans('all.datetime') }}' : (tipedata == 'number' ? '{{ trans('all.number') }}' : '')));
            var tipedata_jumlahkarakter = $('#tipedata_jumlahkarakter').val();
            var tipedata_regex = $('#tipedata_regex').val();
            var tipedata_decimal = $('#tipedata_decimal').val();
            var tipedata_rangedari = $('#tipedata_rangedari').val();
            var tipedata_rangesampai = $('#tipedata_rangesampai').val();

            //set value carainputan hidden
            $('#i_carainputan_tipedata').val(tipedata);
            $('#i_carainputan_jangkauan').val(tipedata_rangedari + '-' + tipedata_rangesampai);
            $('#i_carainputan_desimal').val(tipedata_decimal);
            $('#i_carainputan_jumlahkarakter').val(tipedata_jumlahkarakter);
            $('#i_carainputan_regex').val(tipedata_regex);

            addtext += '<tr><td>{{ trans('all.tipedata') }} : ' + tipedatalabel + '</td></tr>';
            if ($("#tipedata_bolehkosong").prop('checked')) {
                $('#i_carainputan_bolehkosong').val('y');
                addtext += '<tr><td>{{ trans('all.bolehkosong') }}</tr>';
            }else{
                $('#i_carainputan_bolehkosong').val('t');
            }

            if (tipedata == 'text') {
                if (tipedata_jumlahkarakter == '') {
                    alertWarning("{{ trans('all.jumlahkarakter').' '.trans('all.sa_kosong') }}",
                        function () {
                            aktifkanTombol();
                            setFocus($('#tipedata_jumlahkarakter'));
                        });
                    return false;
                }
                addtext += '<tr><td>{{ trans('all.jumlahkarakter') }} : ' + tipedata_jumlahkarakter + '</td></tr>';
                if(tipedata_regex != ''){
                    addtext += '<tr><td>{{ trans('all.regex') }} : ' + tipedata_regex + '</td></tr>';
                }
            }
            if (tipedata == 'number') {
                if(tipedata_rangedari == ''){
                    if (cekAlertAngkaValid(tipedata_rangedari,0,999,0,"{{ trans('all.range') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#tipedata_rangesampai'));
                        }
                    )==false) return false;
                }

                if(tipedata_rangesampai == ''){
                    if (cekAlertAngkaValid(tipedata_rangesampai,0,999,0,"{{ trans('all.range') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#tipedata_rangesampai'));
                        }
                    )==false) return false;
                }

                if(tipedata_rangedari != '' && tipedata_rangesampai != ''){
                    if(tipedata_rangedari > tipedata_rangesampai){
                        alertWarning("{{ trans('all.rangetidakvalid') }}",
                            function () {
                                aktifkanTombol();
                                setFocus($('#tipedata_rangedari'));
                            });
                        return false;
                    }
                    addtext += '<tr><td>{{ trans('all.range') }} : ' + tipedata_rangedari + '-' + tipedata_rangesampai + '</td></tr>';
                }

                if(tipedata_decimal != ''){
                    if (cekAlertAngkaValid(tipedata_decimal,0,16,0,"{{ trans('all.decimal') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#tipedata_decimal'));
                        }
                    )==false) return false;
                    addtext += '<tr><td>' + tipedata_decimal + ' {{ trans('all.angkasetelahdesimal') }}</td></tr>';
                }
            }
        }
        $('#tabelcarainputan').html(addtext);
        $('#closemodalcarainputan').trigger('click');
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.atributvariable') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.kepegawaian') }}</li>
            <li>{{ trans('all.atributvariable') }}</li>
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
          	<form action="{{ url('datainduk/pegawai/atributvariable') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" id="i_carainputan_tipedata" name="carainputan_tipedata" value="">
                <input type="hidden" id="i_carainputan_jangkauan" name="carainputan_jangkauan" value="">
                <input type="hidden" id="i_carainputan_desimal" name="carainputan_desimal" value="">
                <input type="hidden" id="i_carainputan_bolehkosong" name="carainputan_bolehkosong" value="">
                <input type="hidden" id="i_carainputan_jumlahkarakter" name="carainputan_jumlahkarakter" value="">
                <input type="hidden" id="i_carainputan_regex" name="carainputan_regex" value="">
                <table width="480px">
                    <tr>
                        <td width="80px">{{ trans('all.atribut') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="atribut" id="atribut" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td width="80px">{{ trans('all.kode') }}</td>
                        <td style="float:left">
                            <input type="text" size="25" class="form-control" autocomplete="off" name="kode" id="kode" maxlength="20">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.carainput') }}</td>
                        <td style="float:left">
                            <table id="tabelcarainputan"></table>
                            <Button type="button" data-toggle="modal" onclick="pilihTipeData()" data-target="#myModalcarainputan" class="btn btn-success"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</Button>
                        </td>
                    </tr>
                    {{--<tr id="trformula">--}}
                        {{--<td>{{ trans('all.formula') }}</td>--}}
                        {{--<td>--}}
                            {{--<textarea class="form-control" autocomplete="off" name="formula" style="resize:none" id="formula"></textarea>--}}
                            {{--<p></p>--}}
                            {{--<b style='cursor:pointer' title="{{ trans('all.menyisipkanbarisbaru') }}" class='label pilihan' onclick='give("{crlf}","formula")'>{crlf}</b>&nbsp;&nbsp;--}}
                        {{--</td>--}}
                    {{--</tr>--}}
                    <tr>
                        <td>{{ trans('all.penting') }}</td>
                        <td style="float:left">
                            <select id="penting" name="penting" class="form-control">
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../atributvariable')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                        </td>
                    </tr>
                </table>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
    
    <!-- Modal tambah carainputan-->
    <div class="modal fade" id="myModalcarainputan" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-md">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodalcarainputan' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('all.carainput') }}</h4>
                </div>
                <div class="modal-body" style="max-height:480px;overflow: auto;">
                    <div class="col-md-12">
                        <table>
                            <tr>
                                <td>{{ trans('all.tipedata') }}</td>
                                <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                                <td colspan="2" style="float:left">
                                    <select class="form-control" value="tipedata" id="tipedata" onchange="return pilihTipeData()">
                                        <option value=""></option>
                                        <option value="number">{{ trans('all.number') }}</option>
                                        <option value="text">{{ trans('all.text') }}</option>
                                        <option value="date">{{ trans('all.date') }}</option>
                                        <option value="datetime">{{ trans('all.datetime') }}</option>
                                    </select>
                                </td>
                            </tr>
                            <tr id="datatambahan_jumlahkarakter" style="display: none">
                                <td>{{ trans('all.jumlahkarakter') }}</td>
                                <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                                <td style="float:left">
                                    <input type="text" maxlength="3" size="7" onkeypress="return onlyNumber(0,event)" class="form-control" name="tipedata_jumlahkarakter" id="tipedata_jumlahkarakter">
                                </td>
                            </tr>
                            <tr id="datatambahan_regex" style="display: none">
                                <td>{{ trans('all.regex') }}</td>
                                <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                                <td>
                                    <input type="text" maxlength="20" value="" class="form-control" name="tipedata_regex" id="tipedata_regex">
                                </td>
                            </tr>
                            <tr id="datatambahan_range" style="display: none">
                                <td>{{ trans('all.range') }}</td>
                                <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                                <td style="float:left">
                                    <table>
                                        <tr>
                                            <td style="padding-left: 0;"><input type="text" maxlength="3" size="7" onkeypress="return onlyNumber(0,event)" class="form-control" name="tipedata_rangedari" id="tipedata_rangedari"></td>
                                            <td>&nbsp;&nbsp;-&nbsp;&nbsp;</td>
                                            <td><input type="text" maxlength="6" size="7" onkeypress="return onlyNumber(0,event)" class="form-control" name="tipedata_rangesampai" id="tipedata_rangesampai"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr id="datatambahan_decimal" style="display: none">
                                <td>{{ trans('all.decimal') }}</td>
                                <td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
                                <td style="float:left">
                                    {{--<input type="checkbox" @if($decimal != '' && $decimal == 'y') checked @endif id="tipedata_decimal" name="tipedata_decimal">&nbsp;&nbsp;<span style="cursor: pointer" onclick="spanClick('tipedata_decimal')">{{ trans('all.decimal') }}</span>--}}
                                    <table>
                                        <tr>
                                            <td style="padding-left: 0;"><input type="text" maxlength="2" size="7" onkeypress="return onlyNumber(0,event)" value="" class="form-control" name="tipedata_decimal" id="tipedata_decimal"></td>
                                            <td><i title="{{ trans('all.keteranganinputandesimal') }}" class="fa fa-info-circle"></i></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr id="datatambahan_bolehkosong" style="display: none">
                                <td colspan="2">
                                    <input type="checkbox" id="tipedata_bolehkosong" checked name="tipedata_bolehkosong">&nbsp;&nbsp;<span style="cursor: pointer" onclick="spanClick('tipedata_bolehkosong')">{{ trans('all.bolehkosong') }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <table width="100%">
                        <tr>
                            <td style="padding:0;align:right">
                                <button class="btn btn-primary" id="tambahcarainputan" onclick="return tambahCaraInputan()"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.atur') }}</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal tambah carainputan-->
@stop