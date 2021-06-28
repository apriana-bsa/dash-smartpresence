@extends('layouts.master')
@section('title', trans('all.pegawai'))
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

        $("#tambahlokasi").click(function(){
          var lokasi = document.getElementsByClassName("lokasipopup");

          $("#tabellokasi").html("");
          $("#lokasiarea").html("");

          for(var i=0; i<lokasi.length; i++) {
            if(document.getElementById("lokasipopup"+lokasi[i].value).checked){

              //dapatkan idlokasi
              var nilai = $("#attrpopup"+lokasi[i].value).html();
              //isi lokasi
              $("#lokasi"+lokasi[i].value).val(lokasi[i].value);
              //buat input nya
              $("#lokasiarea").append("<input type='hidden' name='lokasi[]' value='"+lokasi[i].value+"'>");
              $("#tabellokasi").append("<tr><td>"+nilai+"</td></tr>");
            }
          }
          //$('.lokasipopup').prop('checked', false);
          $("#closemodal2").trigger("click");
        });

        $("#foto").change(function(){
            readURL(this);
        });

        $('#hapusfoto').click(function(){

          $('#foto').val('');
          $('#imgInp_pegawai').attr('src', '{{ url("foto/pegawai/0") }}');
          $(this).attr('disabled', 'disabled');
          return false;
        });
    });

    function readURL(input) {

        if (input.files && input.files[0]) {
          var reader = new FileReader();

          reader.onload = function (e) {
              $('#imgInp_pegawai').attr('src', e.target.result);
          }

          reader.readAsDataURL(input.files[0]);
          $('#hapusfoto').removeAttr('disabled');
        }
    }

    function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        $('#loading-saver').css('display', '');

        var barismulaidata = $("#barismulaidata").val();
        var barissampaidata = $("#barissampaidata").val();
        var fileexcel = $("#fileexcel").val();
        
        if(barismulaidata == ""){
            $('#loading-saver').css('display', 'none');
            alertWarning("{{ trans('all.barismulaidatakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#barismulaidata'));
            });
            return false;
        }

        if(barissampaidata == ""){
            $('#loading-saver').css('display', 'none');
            alertWarning("{{ trans('all.barissampaidatakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#barissampaidata'));
            });
            return false;
        }

        if(barissampaidata < barismulaidata){
            $('#loading-saver').css('display', 'none');
            alertWarning("{{ trans('all.barissampaidatatidakbolehlebihbesar') }}",
                function() {
                    aktifkanTombol();
                });
            return false;
        }

        if(fileexcel == ""){
            $('#loading-saver').css('display', 'none');
            alertWarning("{{ trans('all.filekosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fileexcel'));
                    });
            return false;
        }

        if(fileexcel.split('.').pop() != 'xlsx' && fileexcel.split('.').pop() != 'xls'){
            $('#loading-saver').css('display', 'none');
            alertWarning("{{ trans('all.filetidakvalid') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#fileexcel'));
                });
            return false;
        }
    }

    function pilihData(param){
        var variable = $('#'+param).val();
        $('.list'+param).css('display','none');
        if(variable != ''){
            $('.list'+param).css('display','');
        }
    }
  </script>
  <style>
  span{
    cursor:pointer;
  }
  </style>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.impordataexcel') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.pegawai') }}</li>
        <li class="active"><strong>{{ trans('all.impordataexcel') }}</strong></li>
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
            @if(Session::has('message')) {{ Session::get('message') }}<br> @endif
          	<form id="formtambah" action="{{ url('datainduk/pegawai/pegawai/imporexcel') }}" method="post" onsubmit="return validasi()" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="140px">{{ trans('all.nama') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="nama" id="nama">
                                @for($i=1;$i<=26;$i++)
                                    <option value="{{ $i }}">{{ trans('all.kolom').' '.$kolom[$i] }}</option>
                                @endfor
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.agama') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="agama" id="agama" onchange="pilihData('agama')">
                                <option value="">{{ trans('all.tidakada') }}</option>
                                @for($i=1;$i<=26;$i++)
                                    <option value="{{ $i }}">{{ trans('all.kolom').' '.$kolom[$i] }}</option>
                                @endfor
                            </select>
                        </td>
                    </tr>
                    @if($agama != '')
                        @foreach($agama as $key)
                            <tr class="listagama" style="display: none;">
                                <td style="padding-left:20px">{{ $key->agama }}</td>
                                <td style="float: left;">
                                    <input type="text" size="8" class="form-control" placeholder="{{ trans('all.kode') }}" autocomplete="off" name="agama_{{ $key->id }}" id="agama_{{ $key->id }}" maxlength="8">
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td>{{ trans('all.pin') }}</td>
                        <td style="float: left;">
                            <select class="form-control" name="pin" id="pin">
                                <option value="">{{ trans('all.tidakada') }}</option>
                                @for($i=1;$i<=26;$i++)
                                    <option value="{{ $i }}">{{ trans('all.kolom').' '.$kolom[$i] }}</option>
                                @endfor
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.pemindai') }}</td>
                        <td style="float: left;">
                            <select class="form-control" name="pemindai" id="pemindai">
                                <option value="">{{ trans('all.tidakada') }}</option>
                                @for($i=1;$i<=26;$i++)
                                    <option value="{{ $i }}">{{ trans('all.kolom').' '.$kolom[$i] }}</option>
                                @endfor
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.nomorhp') }}</td>
                        <td style="float: left;">
                            <select class="form-control" name="nomorhp" id="nomorhp">
                                <option value="">{{ trans('all.tidakada') }}</option>
                                @for($i=1;$i<=26;$i++)
                                    <option value="{{ $i }}">{{ trans('all.kolom').' '.$kolom[$i] }}</option>
                                @endfor
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.jamkerja') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="jamkerja" id="jamkerja" onchange="pilihData('jamkerja')">
                                <option value="">{{ trans('all.tidakada') }}</option>
                                @for($i=1;$i<=26;$i++)
                                    <option value="{{ $i }}">{{ trans('all.kolom').' '.$kolom[$i] }}</option>
                                @endfor
                            </select>
                        </td>
                    </tr>
                    @foreach($jamkerja as $key)
                        <tr class="listjamkerja" style="display: none;">
                            <td style="padding-left:20px">{{ $key->jamkerja }}</td>
                            <td style="float: left;">
                                <input type="text" size="8" class="form-control" placeholder="{{ trans('all.kode') }}" autocomplete="off" name="jamkerja_{{ $key->id }}" id="jamkerja_{{ $key->id }}" maxlength="8">
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>{{ trans('all.datamulaibariske') }}</td>
                        <td>
                            <table>
                                <tr>
                                    <td style="padding-left:0;width:70px"><input type="text" autocomplete="off" class="form-control" id="barismulaidata" name="barismulaidata"></td>
                                    <td>{{ trans('all.sampai') }}</td>
                                    <td style="width:70px"><input type="text" autocomplete="off" class="form-control" id="barissampaidata" name="barissampaidata"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.file') }}</td>
                        <td><input type="file" name="fileexcel" id="fileexcel"></td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../pegawai')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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