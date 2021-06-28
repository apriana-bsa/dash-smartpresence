@extends('layouts.master')
@section('title', trans('all.kategoripekerjaan'))
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
    
	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var nama = $("#nama").val();

		@if(!Session::has('conf_webperusahaan'))
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
                aktifkanTombol();
            });
            return false;
		@endif

		if(nama == ""){
			alertWarning("{{ trans('all.nama').' '.trans('all.sa_kosong') }}",
            function() {
                  aktifkanTombol();
                  setFocus($('#nama'));
            });
            return false;
		}

        var itemnama = document.getElementsByClassName("itemnama");
        for(var i=0; i<itemnama.length; i++) {
            if(itemnama[i].value == ""){
                alertWarning("{{ trans('all.nama').' '.trans('all.sa_kosong')}}",
                    function() {
                        aktifkanTombol();
                    });
                return false;
            }
        }

        var itemsatuan = document.getElementsByClassName("itemsatuan");
        for(var i=0; i<itemsatuan.length; i++) {
            if(itemsatuan[i].value == ""){
                alertWarning("{{ trans('all.satuan').' '.trans('all.sa_kosong')}}",
                    function() {
                        aktifkanTombol();
                    });
                return false;
            }
        }
	}

    var i = -1;
    function addPekerjaanDetail(){
        i++;
        $('#tablepekerjaandetail').append(
            "<tr id='addr_pekerjaandetail"+i+"'>" +
                "<td style=padding-left:0px;>{{ trans('all.nama') }}</td>" +
                "<td style=padding-left:0px;>" +
                    "<input autocomplete='off' name='itemnama[]' class='form-control itemnama' type='text' maxlength=200>" +
                "</td>" +
                "<td style=padding-left:0px;>{{ trans('all.satuan') }}</td>" +
                "<td style=padding-left:0px;>" +
                    "<input autocomplete='off' name='itemsatuan[]' class='form-control itemsatuan' type='text' maxlength=20>" +
                "</td>" +
                "<td style=padding-left:0px;>{{ trans('all.digunakan') }}</td>" +
                "<td style=padding-left:0px;>" +
                    "<select name='itemdigunakan[]' class='form-control'>" +
                        "<option value='y'>{{ trans('all.ya') }}</option>" +
                        "<option value='t'>{{ trans('all.tidak') }}</option>" +
                    "</select>" +
                "</td>" +
                "<td style=padding-left:0px;width:20px;float:left;margin-top:4px>" +
                    "<button type='button' onclick='deletePekerjaanDetail("+i+")' title='{{ trans('all.hapus') }}' class='btn btn-danger glyphicon glyphicon-remove row-remove'></button>"+
                "</td>" +
            "</tr>"
        );
        document.getElementById('nilai'+i).focus();
    }

    function deletePekerjaanDetail(i){
        $("#addr_pekerjaandetail"+i).remove();
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.kategoripekerjaan') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.kepegawaian') }}</li>
            <li>{{ trans('all.kategoripekerjaan') }}</li>
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
          	<form action="{{ url('datainduk/pegawai/pekerjaankategori') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="110px">{{ trans('all.nama') }}</td>
                        <td width=100%>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="200">
                        </td>
                    </tr>
                    {{--<tr>--}}
                        {{--<td>{{ trans('all.satuan') }}</td>--}}
                        {{--<td style="float:left">--}}
                            {{--<input type="text" class="form-control" size="15" value="" name="satuan" autocomplete="off" id="satuan" maxlength="20">--}}
                        {{--</td>--}}
                    {{--</tr>--}}
                    <tr>
                        <td>{{ trans('all.digunakan') }}</td>
                        <td style="float:left">
                            <select class="form-control" id="digunakan" name="digunakan">
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <table id="tablepekerjaandetail"></table>
                <table>
                    <tr>
                        <td colspan=2>
                            <button type="button" onclick='addPekerjaanDetail()' class="btn btn-success"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;{{ trans('all.tambahdetail') }}</button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../pekerjaankategori')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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