@extends('layouts.master')
@section('title', trans('all.indexlemburdanjamkerja'))
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
        addIndex();

        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });
        $('.date').mask("00/00/0000", {clearIfNotMatch: true});

        $("#tambahatribut").click(function(){
            var atribut = document.getElementsByClassName("atributpopup");

            $("#tabelatribut").html("");
            $("#atributarea").html("");

            for(var i=0; i<atribut.length; i++) {
                if(document.getElementById("atributpopup"+atribut[i].value).checked){

                    //dapatkan idatributnilai dan id atribut
                    var nilai = $("#attrpopup_atribut"+atribut[i].value).attr("atribut")+" : "+$("#attrpopup_atribut"+atribut[i].value).html();
                    //isi atribut dan idatribut
                    $("#atribut"+atribut[i].value).val(atribut[i].value);
                    var idatribut = $("#atributpopup"+atribut[i].value).attr("idatribut");
                    //buat input nya
                    $("#atributarea").append("<input type='hidden' name='atribut[]' value='"+atribut[i].value+"'>" +
                    "<input type='hidden' name='idatribut[]' value='"+idatribut+"'>");
                    $("#tabelatribut").append("<tr><td>"+nilai+"</td></tr>");
                }
            }
            //$('.atributpopup').prop('checked', false);
            $("#closemodal").trigger("click");
        });
    });

	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');

        var nama = $('#nama').val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
                aktifkanTombol();
            });
            return false;
        @endif

        if(nama == ''){
            alertWarning("{{ trans('all.namakosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#nama'));
            });
            return false;
        }
        
        if($('#tabIndex tr td').length == 0){
            alertWarning("{{ trans('all.indexbelumditambahkan') }}",
            function() {
                aktifkanTombol();
            });
            return false;
        }else{
            var success = true;
            //$('#tabIndex tr td .index').each(function(){
            //    var iditem = '#'+this.id;
            //    if (cekAlertAngkaValid(this.value,0,9999,0,"{{ trans('all.jumlahmenit') }}",
            //        function() {
            //            aktifkanTombol();
            //            setFocus($(iditem));
            //        }
            //    )==false)
            //    success = false;
            //})
            $('#tabIndex tr td').each(function(){
                var jumlahmenit = $(this).find('.jumlahmenit').val();
                var pengali = $(this).find('.pengali').val();
                var iditem = '';
                if(success == true && jumlahmenit != undefined){
                    iditem = '#'+$(this).find('.jumlahmenit').attr('id');
                    if (cekAlertAngkaValid(jumlahmenit,0,9999,2,"{{ trans('all.jumlahmenit') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($(iditem));
                        }
                    )==false)
                    success = false;
                }
                if(success == true && pengali != undefined){
                    iditem = '#'+$(this).find('.pengali').attr('id');
                    if (cekAlertAngkaValid(pengali,0,9999,2,"{{ trans('all.indexlembur') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($(iditem));
                        }
                    )==false)
                    success = false;
                }
            })
            if(success == false){
                return false;
            }
        }
    }
    
    var i = -1;
    function addIndex(){
        i++;
        $('#tabIndex').append("<tr id='addr_index"+i+"'>"+
                                "<td style=padding-left:0px;margin-top:7px;float:left>{{ trans('all.lebihdari') }}</td>"+
                                "<td style=padding-left:0px;float:left>"+
                                    "<input autocomplete='off' size='10' name='jumlahmenit[]' onkeypress='return onlyNumber(2,event)' placeholder='0' class='form-control jumlahmenit' type='text' id='jumlahmenit"+i+"' maxlength='50'>"+
                                "</td>"+
                                "<td style=padding-left:0px;margin-top:7px;float:left>{{ trans('all.menit') }}, {{ trans('all.indexlemburadalah')}} </td>"+
                                "<td style=padding-left:0px;float:left>"+
                                    "<input autocomplete='off' name='pengali[]' size='5' onkeypress='return onlyNumber(2,event)' class='form-control pengali' placeholder='0' type='text' id='pengali"+i+"' maxlength='50'>"+
                                "</td>"+
                                "<td style=padding-left:0px;width:20px;float:left>"+
                                    "<button type='button' onclick='deleteIndex("+i+")' title='{{ trans('all.hapus') }}' class='btn btn-danger glyphicon glyphicon-remove row-remove'></button>"+
                                "</td>"+
                            "</tr>");
        document.getElementById('jumlahmenit'+i).focus();
    }
    
    function deleteIndex(i){
        $("#addr_index"+i).remove();
        i--;
    }

    function aturatribut(){
        @if(count($dataatribut) > 0)
          $("#buttonmodalatribut").trigger('click');
        @else
          alertWarning("{{ trans('all.nodata') }}");
        @endif
        return false;
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.indexlembur') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.absensi') }}</li>
            <li>{{ trans('all.indexlemburdanjamkerja') }}</li>
            <li>{{ trans('all.indexlembur') }}</li>
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
          	<form action="{{ url('datainduk/absensi/indexlembur') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="100%">
                    <tr>
                        <td width="140px">{{ trans('all.nama') }}</td>
                        <td style="float:left">
                            <input type="text" size="50" class="form-control" autofocus autocomplete="off" value="{{ old('nama') }}" name="nama" id="nama" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td width="110px">{{ trans('all.jenishari') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="jenishari">
                                <option value="biasa">{{ trans('all.biasa') }}</option>
                                <option value="hariminggu">{{ trans('all.hariminggu') }}</option>
                                <option value="harilibur">{{ trans('all.harilibur') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.berlakumulai') }}</td>
                        <td style="float:left">
                            <input type="text" size="11" class="form-control date" autocomplete="off" value="{{ date('d/m/Y') }}" name="berlakumulai" id="berlakumulai" maxlength="10" placeholder="dd/mm/yyyy">
                        </td>
                    </tr>
                    <tr>
                        <td width="110px">{{ trans('all.index') }}</td>
                        <td>
                            <table width="50%" id='tabIndex'></table>
                            <table>
                                <tr>
                                    <td style='padding-left:0px;' colspan=2>
                                        <a id="tambahindex" title="{{ trans('all.tambahindex') }}" onclick='addIndex()' class="btn btn-success glyphicon glyphicon-plus"></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" style="padding-top: 7px">{{ trans('all.atribut') }}</td>
                        <td style="float: left;">
                          <table id="tabelatribut"></table>
                          <button type="button" class="btn btn-success" onclick="return aturatribut()"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturatribut') }}</button>
                          <button type="button" style="display:none" id="buttonmodalatribut" data-toggle="modal" data-target="#myModal"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturatribut') }}</button><br>
                          <span id="atributarea"></span>
                        </td>
                    </tr>
                    {{-- <tr>
                        <td width="110px">{{ trans('all.sampaidengan') }}</td>
                        <td style="float:left">
                            <input type="text" autofocus class="form-control" onkeypress="return onlyNumber(0,event)" size="10" name="sampaidengan" autocomplete="off" id="sampaidengan" maxlength="6">
                        </td>
                        <td style="float:left;margin-top:7px;">{{ trans('all.jam') }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.index') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" onkeypress="return onlyNumber(2,event)" size="10" name="index" autocomplete="off" id="index" maxlength="6">
                        </td>
                        <td style="float:left;margin-top:7px;">{{ trans('all.pengali') }}</td>
                    </tr> --}}
                    <tr>
                        <td colspan="3">
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../indexlembur')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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
    <div class="modal-dialog @if(count($dataatribut)<=1) modal-sm @elseif(count($dataatribut)==2) modal-md @else modal-lg @endif">
      
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
          <h4 class="modal-title">{{ trans('all.atribut') }}</h4>
        </div>
          <div class="modal-body" style="max-height:480px;overflow: auto;">
          @if(isset($dataatribut))
              @for($i=0;$i<count($dataatribut);$i++)
                  <div class="@if(count($dataatribut)<=1) col-md-12 @elseif(count($dataatribut)==2) col-md-6 @else col-md-4 @endif">
                      <b>{{ $dataatribut[$i]['atribut'] }}</b>
                      <table>
                          @foreach($dataatribut[$i]['atributnilai'] as $key)
                              <tr>
                                  <td style="width:20px;padding:2px" valign="top">
                                    <input type="checkbox" @if($key->enable == 0) disabled @endif class="atributpopup" idatribut="{{ $key->idatribut }}" name="atribut_{{ $key->idatribut }}" id="atributpopup{{ $key->id }}" value="{{ $key->id }}">
                                  </td>
                                  <td style="padding: 2px;">
                                      <span id="attrpopup_atribut{{ $key->id }}" @if($key->enable != 0) onclick="spanclick('atributpopup{{ $key->id }}')" @else style="color:#c1c1c1;cursor: default;" @endif atribut="{{ $dataatribut[$i]['atribut'] }}">{{ $key->nilai }}</span>
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