@extends('layouts.master')
@section('title', trans('all.customdashboard'))
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
        var judul = $("#judul").val();

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
        var jenis = $('#query_jenis').val();
        $('.kehadiran').css('display', 'none');
        $('.master').css('display', 'none');
        $('.'+jenis).css('display', '');
    }

    function pilihWaktuTampil(){
        var waktutampil = $('#waktutampil').val();
        $('.waktutampil').css('display', 'none');
        if(waktutampil == 'y'){
            $('.waktutampil').css('display', '');
        }
    }

    function pilihQueryData(param){
        var pilihan = $('#query_'+param).val();
        var data = '';
        if(pilihan == 'semua'){
            data = '<option value="sudahabsen">{{ trans('all.sudahabsen') }}</option>' +
                       '<option value="belumabsen">{{ trans('all.belumabsen') }}</option>' +
                       '<option value="adadikantor">{{ trans('all.adadikantor') }}</option>' +
                       '<option value="ijintidakmasuk">{{ trans('all.ijintidakmasuk') }}</option>' +
                       '<option value="terlambat">{{ trans('all.terlambat') }}</option>' +
                       '<option value="pulangawal">{{ trans('all.pulangawal') }}</option>' +
                       '<option value="lamalembur">{{ trans('all.lamalembur') }}</option>' +
                       '<option value="lamakerja">{{ trans('all.lamakerja') }}</option>' +
                       '<option value="masuknormal">{{ trans('all.masuknormal') }}</option>' +
                       '<option value="pulangnormal">{{ trans('all.pulangnormal') }}</option>';
        }else if(pilihan == 'full'){
            data = '<option value="sudahabsen">{{ trans('all.sudahabsen') }}</option>' +
                       '<option value="belumabsen">{{ trans('all.belumabsen') }}</option>' +
                       '<option value="terlambat">{{ trans('all.terlambat') }}</option>' +
                       '<option value="pulangawal">{{ trans('all.pulangawal') }}</option>' +
                       '<option value="lamalembur">{{ trans('all.lamalembur') }}</option>' +
                       '<option value="lamakerja">{{ trans('all.lamakerja') }}</option>' +
                       '<option value="masuknormal">{{ trans('all.masuknormal') }}</option>' +
                       '<option value="pulangnormal">{{ trans('all.pulangnormal') }}</option>';
        }else if(pilihan == 'shift'){
            data = '<option value="sudahabsen">{{ trans('all.sudahabsen') }}</option>' +
                       '<option value="belumabsen">{{ trans('all.belumabsen') }}</option>' +
                       '<option value="terlambat">{{ trans('all.terlambat') }}</option>' +
                       '<option value="pulangawal">{{ trans('all.pulangawal') }}</option>' +
                       '<option value="lamalembur">{{ trans('all.lamalembur') }}</option>' +
                       '<option value="lamakerja">{{ trans('all.lamakerja') }}</option>' +
                       '<option value="masuknormal">{{ trans('all.masuknormal') }}</option>' +
                       '<option value="pulangnormal">{{ trans('all.pulangnormal') }}</option>';
        }
        $('#query_'+param+'_data').html(data)
    }

    function cari_id(jenis){
        callModalGeneral('customdashboardnode','{{ url('cariid') }}/'+jenis);
    }

    function pilihWarna(){
        var warna = $('#pilihwarna').val();
        //$('#warna').val($('#'+warna.substring(1)).html());
        alert(warna.substring(1));
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.customdashboard') }}</h2>
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
          	<form action="{{ url('pengaturan/customdashboardnode') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="100%">
                    <tr>
                        <td width="200px">{{ trans('all.nama') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.judul') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" autocomplete="off" name="judul" id="judul" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.icon') }}</td>
                        <td>
                            <button type="button" class="btn btn-default" id='faicon' data-iconset="fontawesome" data-placement="right" data-icon="" role="iconpicker"></button>
                            <input type="hidden" class="form-control" autocomplete="off" name="icon" id="icon" value="" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.warna') }}</td>
                        <td style="float:left">
                            {{--<input type="text" class="form-control color" autocomplete="off" name="warna" id="warna" maxlength="10">--}}
                            <input type="hidden" class="form-control" autocomplete="off" value="soft red" name="warna" id="warna" maxlength="10">
                            <select name="pilihwarna" id="pilihwarna" class="form-control">
                                {{--<option value=""></option>--}}
                                {{--<option value="merah">{{ trans('all.merah') }}</option>--}}
                                {{--<option value="kuning">{{ trans('all.kuning') }}</option>--}}
                                {{--<option value="hijau">{{ trans('all.hijau') }}</option>--}}
                                {{--<option value="biru">{{ trans('all.biru') }}</option>--}}
                                {{--<option value="ungu">{{ trans('all.ungu') }}</option>--}}
                                {{--<option value="hitam">{{ trans('all.hitam') }}</option>--}}
                                {{--<option value="putih">{{ trans('all.putih') }}</option>--}}
                                <option value="#EC644B">soft red</option>
                                <option value="#D24D57">chestnut</option>
                                <option value="#EF4836">flamingo</option>
                                <option value="#C0392B">tall poppy</option>
                                <option value="#DB0A5B">razzmatazz</option>
                                <option value="#F1A9A0">wax flower</option>
                                <option value="#D2527F">cabaret</option>
                                <option value="#947CB0">lavender</option>
                                <option value="#674172">honey</option>
                                <option value="#AEA8D3">wistful</option>
                                <option value="#BF55EC">medium</option>
                                <option value="#9B59B6">wisteria</option>
                                <option value="#013243">sherpa</option>
                                <option value="#59ABE3">picton</option>
                                <option value="#4183D7">royal blue</option>
                                <option value="#E4F1FE">alice blue</option>
                                <option value="#52B3D9">shakespear</option>
                                <option value="#2C3E50">madison</option>
                                <option value="#336E7B">ming</option>
                                <option value="#3A539B">chambray</option>
                                <option value="#1F3A93">jacksons</option>
                                <option value="#5C97BF">fountain</option>
                                <option value="#00E640">malachite</option>
                                <option value="#91B496">summer</option>
                                <option value="#A2DED0">aqua</option>
                                <option value="#87D37C">gossip</option>
                                <option value="#1BBC9B">mountain</option>
                                <option value="#86E2D5">riptide</option>
                                <option value="#2ECC71">shamrock</option>
                                <option value="#E9D460">confetty</option>
                                <option value="#26C281">jungle</option>
                                <option value="#F89406">california</option>
                                <option value="#F4B350">casablanca</option>
                                <option value="#F39C12">buttercup</option>
                                <option value="#F27935">jaffa</option>
                                <option value="#6C7A89">lynch</option>
                                <option value="#ECF0F1">porcelain</option>
                                <option value="#BFBFBF">silver</option>
                                <option value="#DADFE1">iron</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.query_jenis') }}</td>
                        <td style="float:left">
                            <select name="query_jenis" id="query_jenis" class="form-control" onchange="return pilihJenis()">
                                <option value=""></option>
                                <option value="kehadiran">{{ trans('all.kehadiran') }}</option>
                                <option value="master">{{ trans('all.master') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="kehadiran" style="display:none">
                        <td>{{ trans('all.query_kehadiran') }}</td>
                        <td style="float:left">
                            <select name="query_kehadiran" id="query_kehadiran" class="form-control" onchange="pilihQueryData('kehadiran')">
                                <option value=""></option>
                                <option value="semua">{{ trans('all.semua') }}</option>
                                <option value="full">{{ trans('all.full') }}</option>
                                <option value="shift">{{ trans('all.shift') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="kehadiran" style="display:none">
                        <td>{{ trans('all.query_kehadiran_data') }}</td>
                        <td style="float:left">
                            <select name="query_kehadiran_data" id="query_kehadiran_data" class="form-control">
                                <option value=""></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="kehadiran" style="display:none">
                        <td valign="top">{{ trans('all.query_kehadiran_if') }}</td>
                        <td>
                            <textarea type="text" style="resize:none" class="form-control" autofocus autocomplete="off" name="query_kehadiran_if" id="query_kehadiran_if"></textarea>
                            <p style="margin-bottom: 0"></p>
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idpegawai]","query_kehadiran_if")'>[idpegawai]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idatributnilai]","query_kehadiran_if")'>[idatributnilai]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idagama]","query_kehadiran_if")'>[idagama]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idjamkerja]","query_kehadiran_if")'>[idjamkerja]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idlokasi]","query_kehadiran_if")'>[idlokasi]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idjamkerjashift]","query_kehadiran_if")'>[idjamkerjashift]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idjamkerjashift_jenis]","query_kehadiran_if")'>[idjamkerjashift_jenis]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idjamkerjakategori]","query_kehadiran_if")'>[idjamkerjakategori]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idalasantidakmasuk]","query_kehadiran_if")'>[idalasantidakmasuk]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idalasantidakmasuk_kategori]","query_kehadiran_if")'>[idalasantidakmasuk_kategori]</b>&nbsp;&nbsp;
                            <p></p>
                            <button type="button" id="cari_id_kehadiran" onclick="return cari_id('kehadiran')" class="btn btn-primary"><i class="fa fa-search"></i>&nbsp;&nbsp;{{ trans('all.cari_id') }}</button>
                        </td>
                    </tr>
                    <tr class="kehadiran" style="display:none">
                        <td>{{ trans('all.query_kehadiran_group') }}</td>
                        <td style="float:left">
                            <select name="query_kehadiran_group" id="query_kehadiran_group" class="form-control">
                                <option value=""></option>
                                <option value="agama">{{ trans('all.agama') }}</option>
                                <option value="jamkerja">{{ trans('all.jamkerja') }}</option>
                                <option value="jamkerjajenis">{{ trans('all.jamkerjajenis') }}</option>
                                <option value="jamkerjashift_jenis">{{ trans('all.jamkerjashift_jenis') }}</option>
                                <option value="jamkerjakategori">{{ trans('all.jamkerjakategori') }}</option>
{{--                                <option value="lokasi">{{ trans('all.lokasi') }}</option>--}}
                                <option value="alasantidakmasuk">{{ trans('all.alasantidakmasuk') }}</option>
                                <option value="alasantidakmasuk_kategori">{{ trans('all.alasantidakmasuk_kategori') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="kehadiran" style="display:none">
                        <td>{{ trans('all.query_kehadiran_periode') }}</td>
                        <td style="float:left">
                            <select name="query_kehadiran_periode" id="query_kehadiran_periode" class="form-control">
                                <option value=""></option>
                                <option value="navigasi-tanggal">{{ trans('all.navigasi-tanggal') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="master" style="display:none">
                        <td>{{ trans('all.query_master_data') }}</td>
                        <td style="float:left">
                            <select name="query_master_data" id="query_master_data" class="form-control">
                                <option value=""></option>
                                <option value="pegawai">{{ trans('all.pegawai') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="master" style="display:none">
                        <td>{{ trans('all.query_master_if') }}</td>
                        <td>
                            <textarea type="text" style="resize:none" class="form-control" autofocus autocomplete="off" name="query_master_if" id="query_master_if"></textarea>
                            <p style="margin-bottom: 0"></p>
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idpegawai]","query_master_if")'>[idpegawai]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idatributnilai]","query_master_if")'>[idatributnilai]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idagama]","query_master_if")'>[idagama]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idjamkerja]","query_master_if")'>[idjamkerja]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idlokasi]","query_master_if")'>[idlokasi]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idjamkerjashift]","query_master_if")'>[idjamkerjashift]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idjamkerjashift_jenis]","query_master_if")'>[idjamkerjashift_jenis]</b>&nbsp;&nbsp;
                            <b style='cursor:default' class='label pilihan sortcutpilihan' onclick='give("[idjamkerjakategori]","query_master_if")'>[idjamkerjakategori]</b>&nbsp;&nbsp;
                            <p></p>
                            <button type="button" id="cari_id_kehadiran" onclick="return cari_id('master')" class="btn btn-primary"><i class="fa fa-search"></i>&nbsp;&nbsp;{{ trans('all.cari_id') }}</button>
                        </td>
                    </tr>
                    <tr class="master" style="display:none">
                        <td>{{ trans('all.query_master_group') }}</td>
                        <td style="float:left">
                            <select name="query_master_group" id="query_master_group" class="form-control">
                                <option value=""></option>
                                <option value="agama">{{ trans('all.agama') }}</option>
                                <option value="jamkerja">{{ trans('all.jamkerja') }}</option>
                                <option value="jamkerjajenis">{{ trans('all.jamkerjajenis') }}</option>
                                <option value="jamkerjashif_jenis">{{ trans('all.jamkerjashift_jenis') }}</option>
                                <option value="jamkerjakategori">{{ trans('all.jamkerjakategori') }}</option>
                                <option value="lokasi">{{ trans('all.lokasi') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="master" style="display:none">
                        <td>{{ trans('all.query_master_periode') }}</td>
                        <td style="float:left">
                            <select name="query_master_periode" id="query_master_periode" class="form-control">
                                <option value=""></option>
                                <option value="navigasi-tanggal">{{ trans('all.navigasi-tanggal') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.waktutampil') }}</td>
                        <td style="float:left">
                            <select name="waktutampil" class="form-control" id="waktutampil" onchange="pilihWaktuTampil()">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="waktutampil" style="display:none">
                        <td>{{ trans('all.waktutampil_awal') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control jam" placeholder="hh:mm" name="waktutampil_awal" id="waktutampil_awal" size="6">
                        </td>
                    </tr>
                    <tr class="waktutampil" style="display:none">
                        <td>{{ trans('all.waktutampil_akhir') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control jam" placeholder="hh:mm" name="waktutampil_akhir" id="waktutampil_akhir" size="6">
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../customdashboardnode')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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