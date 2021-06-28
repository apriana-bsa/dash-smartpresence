@extends('layouts.master')
@section('title', trans('all.customdashboard'))
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
        var tampil_konfirmasi = $("#tampil_konfirmasi").val();
        var tampil_peringkat = $("#tampil_peringkat").val();
        var tampil_3lingkaran = $("#tampil_3lingkaran").val();
        var tampil_sudahbelumabsen = $("#tampil_sudahbelumabsen").val();
        var tampil_terlambatdll = $("#tampil_terlambatdll").val();
        var tampil_pulangawaldll = $("#tampil_pulangawaldll").val();
        var tampil_totalgrafik = $("#tampil_totalgrafik").val();
        var tampil_peta = $("#tampil_peta").val();
        var tampil_harilibur = $("#tampil_harilibur").val();
        var tampil_riwayatdashboard = $("#tampil_riwayatdashboard").val();

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
        
        if(tampil_konfirmasi == ""){
            alertWarning("{{ trans('all.tampil_konfirmasikosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tampil_konfirmasi'));
                    });
            return false;
        }
        
        if(tampil_peringkat == ""){
            alertWarning("{{ trans('all.tampil_peringkatkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tampil_peringkat'));
                    });
            return false;
        }
        
        if(tampil_3lingkaran == ""){
            alertWarning("{{ trans('all.tampil_3lingkarankosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tampil_3lingkaran'));
                    });
            return false;
        }
        
        if(tampil_sudahbelumabsen == ""){
            alertWarning("{{ trans('all.tampil_sudahbelumabsenkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tampil_sudahbelumabsen'));
                    });
            return false;
        }
        
        if(tampil_terlambatdll == ""){
            alertWarning("{{ trans('all.tampil_terlambatdllkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tampil_terlambatdll'));
                    });
            return false;
        }
        
        if(tampil_pulangawaldll == ""){
            alertWarning("{{ trans('all.tampil_pulangawaldllkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tampil_pulangawaldll'));
                    });
            return false;
        }
        
        if(tampil_totalgrafik == ""){
            alertWarning("{{ trans('all.tampil_totalgrafikkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tampil_totalgrafik'));
                    });
            return false;
        }
        
        if(tampil_peta == ""){
            alertWarning("{{ trans('all.tampil_petakosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tampil_peta'));
                    });
            return false;
        }
        
        if(tampil_harilibur == ""){
            alertWarning("{{ trans('all.tampil_hariliburkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tampil_harilibur'));
                    });
            return false;
        }
        
        if(tampil_riwayatdashboard == ""){
            alertWarning("{{ trans('all.tampil_riwayatdashboardkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tampil_riwayatdashboard'));
                    });
            return false;
        }
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.customdashboard') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.pengaturan') }}</li>
            <li>{{ trans('all.customdashboard') }}</li>
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
          	<form action="{{ url('pengaturan/customdashboard') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="200px">{{ trans('all.nama') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampil_konfirmasi') }}</td>
                        <td style="float:left">
                            <select id="tampil_konfirmasi" name="tampil_konfirmasi" class="form-control">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampil_peringkat') }}</td>
                        <td style="float:left">
                            <select id="tampil_peringkat" name="tampil_peringkat" class="form-control">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampil_3lingkaran') }}</td>
                        <td style="float:left">
                            <select id="tampil_3lingkaran" name="tampil_3lingkaran" class="form-control">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampil_sudahbelumabsen') }}</td>
                        <td style="float:left">
                            <select id="tampil_sudahbelumabsen" name="tampil_sudahbelumabsen" class="form-control">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampil_terlambatdll') }}</td>
                        <td style="float:left">
                            <select id="tampil_terlambatdll" name="tampil_terlambatdll" class="form-control">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampil_pulangawaldll') }}</td>
                        <td style="float:left">
                            <select id="tampil_pulangawaldll" name="tampil_pulangawaldll" class="form-control">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampil_totalgrafik') }}</td>
                        <td style="float:left">
                            <select id="tampil_totalgrafik" name="tampil_totalgrafik" class="form-control">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampil_peta') }}</td>
                        <td style="float:left">
                            <select id="tampil_peta" name="tampil_peta" class="form-control">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampil_harilibur') }}</td>
                        <td style="float:left">
                            <select id="tampil_harilibur" name="tampil_harilibur" class="form-control">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampil_riwayatdashboard') }}</td>
                        <td style="float:left">
                            <select id="tampil_riwayatdashboard" name="tampil_riwayatdashboard" class="form-control">
                                <option value=""></option>
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../customdashboard')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                        </td>
                    </tr>
                </table>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
<link href="{{ asset('lib/css/appSortable.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('lib/js/Sortable.js') }}"></script>
<script src="{{ asset('lib/js/appSortable.js') }}"></script>
@stop