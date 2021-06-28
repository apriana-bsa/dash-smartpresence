@extends('layouts.master')
@section('title', trans('all.tv'))
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

        $('.jam').inputmask( 'h:s' );

        $('#faicon').on('change', function(e) {
            //console.log(e.icon);
            $('#faicon').attr("data-icon", e.icon);
            $('#icon').val(e.icon);
        });

        //$('select[name="pilihwarna"]').simplecolorpicker({picker: true, theme: 'glyphicons'});
        //$('#pilihwarna').simplecolorpicker({picker: true, theme: 'glyphicons'});
        $('select[name="pilihwarna"]').simplecolorpicker({
            picker: true,
            theme: 'glyphicons'
        }).on('change', function() {
            $('#warna').val($('#pilihwarna').next().attr('title'));
            //alert($('#warna').val());
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
	}

    function pilihOrientasi(){
        var orientasi = $('#orientasi').val();
        $('#jumlahkolomhorizontal').css('display', 'none');
        if(orientasi == 'horizontal'){
            $('#jumlahkolomhorizontal').css('display', '');
        }
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.tv') }}</h2>
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
          	<form action="{{ url('pengaturan/tv') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="100%">
                    <tr>
                        <td width="200px">{{ trans('all.nama') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="20" autofocus autocomplete="off" name="nama" id="nama" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.header_baris1') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="50" autocomplete="off" name="header_baris1" id="header_baris1" maxlength="200">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.header_baris2') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="50" autocomplete="off" name="header_baris2" id="header_baris2" maxlength="200">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.orientasi') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="orientasi" id="orientasi" onchange="return pilihOrientasi()">
                                <option value="vertical">{{ trans('all.vertikal') }}</option>
                                <option value="horizontal">{{ trans('all.horisontal') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr style="display:none" id="jumlahkolomhorizontal">
                        <td>{{ trans('all.jumlah_kolom_horizontal') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="jumlah_kolom_horizontal">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.interval_refresh_data') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="7" autocomplete="off" name="interval_refresh_data" value="60" id="interval_refresh_data" maxlength="5">
                        </td>
                        <td style="margin-top:7px;float:left">{{ trans('all.detik') }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.interval_slide') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="7" autocomplete="off" name="interval_slide" value="5" id="interval_slide" maxlength="5">
                        </td>
                        <td style="margin-top:7px;float:left">{{ trans('all.detik') }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.bahasa') }}</td>
                        <td style="float:left">
                            <select class="form-control" name="bahasa">
                                <option value="id">Indonesia</option>
                                <option value="en">English</option>
                                <option value="cn">中国</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding:5px;'>{{ trans('all.warna_background') }}</td>
                        <td style='padding:5px;float:left'>
                            <input type="text" class="form-control color" size="7" autocomplete="off" value="FFFFFF" name="warna_background" id="warna_background" maxlength="10">
                        </td>
                    </tr>
                    <tr>
                        <td style='padding:5px;'>{{ trans('all.headerfooter_warna_background') }}</td>
                        <td style='padding:5px;float:left'>
                            <input type="text" class="form-control color" size="7" autocomplete="off" value="1B1464" name="headerfooter_warna_background" id="headerfooter_warna_background" maxlength="10">
                        </td>
                    </tr>
                    <tr>
                        <td style='padding:5px;'>{{ trans('all.headerfooter_warna_teks') }}</td>
                        <td style='padding:5px;float:left'>
                            <input type="text" class="form-control color" size="7" autocomplete="off" value="FFFFFF" name="headerfooter_warna_teks" id="headerfooter_warna_teks" maxlength="10">
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../tv')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                        </td>
                    </tr>
                </table>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
<link rel="stylesheet" href="{{ asset('lib/js/bootstrap-iconpicker-1.9.0/dist/css/bootstrap-iconpicker.min.css') }}"/>
<script src="{{ asset('lib/js/bootstrap-iconpicker-1.9.0/dist/js/bootstrap-iconpicker-iconset-all.min.js') }}"></script>
<script src="{{ asset('lib/js/bootstrap-iconpicker-1.9.0/dist/js/bootstrap-iconpicker.min.js') }}"></script>
@stop