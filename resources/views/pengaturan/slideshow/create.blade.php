@extends('layouts.master')
@section('title', trans('all.slideshow'))
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
    });

	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');

        var nama = $("#nama").val();
        var timeout = $('#timeout').val();
        var durasiperslide = $('#durasiperslide').val();

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

        if(timeout == ""){
            alertWarning("{{ trans('all.timeoutkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#timeout'));
                    });
            return false;
        }

        if(durasiperslide == ""){
            alertWarning("{{ trans('all.durasiperslidekosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#durasiperslide'));
                    });
            return false;
        }

        var waktumulai = document.getElementsByClassName("waktumulai");
        var waktuselesai = document.getElementsByClassName("waktuselesai");
        for(var i=0; i<waktumulai.length; i++) {
            if(waktumulai[i].value == ""){
                alertWarning("{{ trans('all.waktukosong') }}",
                      function() {
                        aktifkanTombol();
                        setTimeout(function(){ $('#waktumulai_'+i).focus(); },200);
                      });
                return false;
            }

            if(waktuselesai[i].value == ""){
                alertWarning("{{ trans('all.waktukosong') }}",
                        function() {
                            aktifkanTombol();
                            $('#waktuselesai_'+i).focus();
                            setTimeout(function(){ $('#waktuselesai_'+i).focus(); },200);
                        });
                return false;
            }
        }
	}

	var i = -1;
    function addWaktu(){
        i++;
        $('#tabWaktu').append("<tr id='addr_waktu"+i+"'>" +
                                    "<td style=padding-left:0px;float:left>" +
                                        "<input autocomplete='off' size=7 name='waktumulai[]' class='form-control jam waktumulai' placeholder='hh:mm' type='text' id='waktumulai_"+i+"'>" +
                                    "</td>" +
                                    "<td style=padding-left:0px;margin-top:6px;float:left>-</td>" +
                                    "<td style=padding-left:0px;float:left>" +
                                        "<input autocomplete='off' size=7 name='waktuselesai[]' class='form-control jam waktuselesai' placeholder='hh:mm' type='text' id='waktuselesai_"+i+"'>" +
                                    "</td>" +
                                    "<td style=padding-left:0px;width:20px;float:left;>" +
                                        "<button onclick='deleteWaktu("+i+")' title='{{ trans('all.hapus') }}' class='btn btn-danger glyphicon glyphicon-remove row-remove'></button>" +
                                    "</td>" +
                                "</tr>");
        $('.jam').inputmask( 'h:s' );
        document.getElementById('waktumulai_'+i).focus();
    }
    
    function deleteWaktu(i){
        $("#addr_waktu"+i).remove();
        i--;
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.slideshow') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.pengaturan') }}</li>
            <li>{{ trans('all.slideshow') }}</li>
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
          	<form action="{{ url('pengaturan/slideshow') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="125px">{{ trans('all.nama') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.timeout') }}</td>
                        <td style="float:left">
                            <table>
                                <tr>
                                    <td style="padding:0">
                                        <input type="text" class="form-control" size="5" autocomplete="off" name="timeout" id="timeout" maxlength="5">
                                    </td>
                                    <td style="padding:0;padding-left:10px">{{ trans('all.detik') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.durasiperslide') }}</td>
                        <td style="float:left">
                            <table>
                                <tr>
                                    <td style="padding:0">
                                        <input type="text" class="form-control" size="5" autocomplete="off" name="durasiperslide" id="durasiperslide" maxlength="5">
                                    </td>
                                    <td style="padding:0;padding-left:10px">{{ trans('all.detik') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td valign=top style="padding-top:15px">{{ trans('all.waktu') }}</td>
                        <td colspan="2">
                            <table width=100% id='tabWaktu'></table>
                            <table>
                            <tr>
                                <td style='padding-left:0px;'>
                                    <a id="tambahwaktu" title="{{ trans('all.tambah') }}" onclick='addWaktu()' class="btn btn-success glyphicon glyphicon-plus"></a>
                                </td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../slideshow')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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