@extends('layouts.master')
@section('title', trans('all.hakakses'))
@section('content')

	<style>
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

  $(function(){
    switchmenucheckbox('notifikasi','lihat');
    switchmenucheckbox('pengelola','lihat');
    switchmenucheckbox('pegawai','lihat');
    switchmenucheckbox('atribut','lihat');
    switchmenucheckbox('lokasi','lihat');
    switchmenucheckbox('facesample','lihat');
    switchmenucheckbox('fingersample','lihat');
    switchmenucheckbox('agama','lihat');
    switchmenucheckbox('pekerjaan','lihat');
    switchmenucheckbox('pekerjaanuser','lihat');
    switchmenucheckbox('alasanmasukkeluar','lihat');
    switchmenucheckbox('alasantidakmasuk','lihat');
    switchmenucheckbox('mesin','lihat');
    switchmenucheckbox('jamkerja','lihat');
    switchmenucheckbox('harilibur','lihat');
    switchmenucheckbox('ijintidakmasuk','lihat');
    switchmenucheckbox('cuti','lihat');
    switchmenucheckbox('logabsen','lihat');
    switchmenucheckbox('fingerprintconnector','lihat');
    switchmenucheckbox('jadwalshift','lihat');
    switchmenucheckbox('konfirmasi_flag','lihat');
    switchmenucheckbox('payrollpengaturan','lihat');
    switchmenucheckbox('payrollkomponenmaster','lihat');
    switchmenucheckbox('payrollkomponeninputmanual','lihat');
    switchmenucheckbox('slideshow','lihat');
    switchmenucheckbox('batasan','lihat');
    switchmenucheckbox('hakakses','lihat');
    switchmenucheckbox('laporancustom','lihat');
    switchmenucheckbox('customdashboard','lihat');
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
	}

  //buat checkbox
  function switchmenucheckbox(menu, opsi){
    if($("#"+menu+"_lihat").prop('checked')){
      $("#"+menu+"_lihat").prop('checked', true);
      $("."+menu+"_manipulasi").css('display', '');
    }else{
      $("#"+menu+"_lihat").prop('checked', false);
      $("."+menu+"_manipulasi").css('display', 'none');
      $("."+menu+"_manipulasi_checkbox").prop('checked', false);
    }
  }

  //buat span
  function switchmenu(menu,opsi){
    if(opsi == 'lihat'){
      if($("#"+menu+"_lihat").prop('checked')){
        $("#"+menu+"_lihat").prop('checked', false);
        $("."+menu+"_manipulasi").css('display', 'none');
        $("."+menu+"_manipulasi_checkbox").prop('checked', false);
      }else{
        $("#"+menu+"_lihat").prop('checked', true);
        $("."+menu+"_manipulasi").css('display', '');
      }
    }else{
      if($("#"+menu+"_"+opsi).prop('checked')){
        $("#"+menu+"_"+opsi).prop('checked', false);
      }else{
        $("#"+menu+"_"+opsi).prop('checked', true);
      }
    }
  }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.hakakses') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.lainlain') }}</li>
        <li>{{ trans('all.hakakses') }}</li>
        <li class="active"><strong>{{ trans('all.ubahdata') }}</strong></li>
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
          	<form action="../{{ $hakakses->id }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="put">
                <table width="640px">
                    <tr>
                        <td width="210px">{{ trans('all.nama') }}</td>
                        <td>
                            <input type="text" class="form-control" value="{{ $hakakses->nama }}" autofocus autocomplete="off" name="nama" id="nama" maxlength="30">
                        </td>
                    </tr>
                    <tr>
                  <td colspan=2>
                    <table>
                      <tr>
                        <td style='padding-left:0px;' width="230px">{{ trans('all.ajakan') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' @if(strpos($hakakses->ajakan, 'i') !== false) checked @endif value='i' name='ajakan_ijinkan' id='ajakan_ijinkan'></td>
                        <td><span class='ajakan_ijinkan' onclick="switchmenu('ajakan','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                      </tr>
                      <tr>
                          <td style='padding-left:0px;'>{{ trans('all.pengelola') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('pengelola','lihat')" @if(strpos($hakakses->pengelola, 'l') !== false) checked @endif value='l' name='pengelola_lihat' id='pengelola_lihat'></td>
                          <td><span class='pengelola_lihat' onclick="switchmenu('pengelola','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='pengelola_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pengelola, 'u') !== false || strpos($hakakses->pengelola, 'm') !== false) checked @endif class='pengelola_manipulasi_checkbox' value='u' name='pengelola_ubah' id='pengelola_ubah'></td>
                          <td class='pengelola_manipulasi manipulasi'><span class='pengelola_ubah' onclick="switchmenu('pengelola','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='pengelola_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pengelola, 'h') !== false || strpos($hakakses->pengelola, 'm') !== false) checked @endif class='pengelola_manipulasi_checkbox' value='h' name='pengelola_hapus' id='pengelola_hapus'></td>
                          <td class='pengelola_manipulasi manipulasi'><span class='pengelola_hapus' onclick="switchmenu('pengelola','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:0px;'>{{ trans('all.datainduk') }}</td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.perusahaan') }}</td>
                          <td style='padding-left:0px;' class='perusahaan_manipulasi'><input type='checkbox' @if(strpos($hakakses->perusahaan, 'u') !== false || strpos($hakakses->perusahaan, 'm') !== false) checked @endif class='perusahaan_manipulasi_checkbox' value='u' name='perusahaan_ubah' id='perusahaan_ubah'></td>
                          <td class='perusahaan_manipulasi'><span class='perusahaan_ubah' onclick="switchmenu('perusahaan','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='perusahaan_manipulasi'><input type='checkbox' @if(strpos($hakakses->perusahaan, 'h') !== false || strpos($hakakses->perusahaan, 'm') !== false) checked @endif class='perusahaan_manipulasi_checkbox' value='h' name='perusahaan_hapus' id='perusahaan_hapus'></td>
                          <td class='perusahaan_manipulasi'><span class='perusahaan_hapus' onclick="switchmenu('perusahaan','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.pegawai') }}</td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.pegawai') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('pegawai','lihat')" @if(strpos($hakakses->pegawai, 'l') !== false) checked @endif value='l' name='pegawai_lihat' id='pegawai_lihat'></td>
                          <td><span class='pegawai_lihat' onclick="switchmenu('pegawai','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='pegawai_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pegawai, 't') !== false || strpos($hakakses->pegawai, 'm') !== false) checked @endif class='pegawai_manipulasi_checkbox' value='t' name='pegawai_tambah' id='pegawai_tambah'></td>
                          <td class='pegawai_manipulasi manipulasi'><span class='pegawai_tambah' onclick="switchmenu('pegawai','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='pegawai_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pegawai, 'u') !== false || strpos($hakakses->pegawai, 'm') !== false) checked @endif class='pegawai_manipulasi_checkbox' value='u' name='pegawai_ubah' id='pegawai_ubah'></td>
                          <td class='pegawai_manipulasi manipulasi'><span class='pegawai_ubah' onclick="switchmenu('pegawai','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='pegawai_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pegawai, 'h') !== false || strpos($hakakses->pegawai, 'm') !== false) checked @endif class='pegawai_manipulasi_checkbox' value='h' name='pegawai_hapus' id='pegawai_hapus'></td>
                          <td class='pegawai_manipulasi manipulasi'><span class='pegawai_hapus' onclick="switchmenu('pegawai','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.aturatributdanlokasi') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('aturatributdanlokasi','ubah')" @if(strpos($hakakses->aturatributdanlokasi, 'u') !== false || strpos($hakakses->aturatributdanlokasi, 'm') !== false || strpos($hakakses->aturatributdanlokasi, 'l') !== false) checked @endif value='u' name='aturatributdanlokasi_ubah' id='aturatributdanlokasi_ubah'></td>
                          <td><span class='aturatributdanlokasi_ubah' onclick="switchmenu('aturatributdanlokasi','ubah')">{{ trans('all.ubah') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.atribut') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('atribut','lihat')" @if(strpos($hakakses->atribut, 'l') !== false) checked @endif value='l' name='atribut_lihat' id='atribut_lihat'></td>
                          <td><span class='atribut_lihat' onclick="switchmenu('atribut','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='atribut_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->atribut, 't') !== false || strpos($hakakses->atribut, 'm') !== false) checked @endif class='atribut_manipulasi_checkbox' value='t' name='atribut_tambah' id='atribut_tambah'></td>
                          <td class='atribut_manipulasi manipulasi'><span class='atribut_tambah' onclick="switchmenu('atribut','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='atribut_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->atribut, 'u') !== false || strpos($hakakses->atribut, 'm') !== false) checked @endif class='atribut_manipulasi_checkbox' value='u' name='atribut_ubah' id='atribut_ubah'></td>
                          <td class='atribut_manipulasi manipulasi'><span class='atribut_ubah' onclick="switchmenu('atribut','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='atribut_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->atribut, 'h') !== false || strpos($hakakses->atribut, 'm') !== false) checked @endif class='atribut_manipulasi_checkbox' value='h' name='atribut_hapus' id='atribut_hapus'></td>
                          <td class='atribut_manipulasi manipulasi'><span class='atribut_hapus' onclick="switchmenu('atribut','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.lokasi') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('lokasi','lihat')" @if(strpos($hakakses->lokasi, 'l') !== false) checked @endif value='l' name='lokasi_lihat' id='lokasi_lihat'></td>
                          <td><span class='lokasi_lihat' onclick="switchmenu('lokasi','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='lokasi_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->lokasi, 't') !== false || strpos($hakakses->lokasi, 'm') !== false) checked @endif class='lokasi_manipulasi_checkbox' value='t' name='lokasi_tambah' id='lokasi_tambah'></td>
                          <td class='lokasi_manipulasi manipulasi'><span class='lokasi_tambah' onclick="switchmenu('lokasi','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='lokasi_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->lokasi, 'u') !== false || strpos($hakakses->lokasi, 'm') !== false) checked @endif class='lokasi_manipulasi_checkbox' value='u' name='lokasi_ubah' id='lokasi_ubah'></td>
                          <td class='lokasi_manipulasi manipulasi'><span class='lokasi_ubah' onclick="switchmenu('lokasi','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='lokasi_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->lokasi, 'h') !== false || strpos($hakakses->lokasi, 'm') !== false) checked @endif class='lokasi_manipulasi_checkbox' value='h' name='lokasi_hapus' id='lokasi_hapus'></td>
                          <td class='lokasi_manipulasi manipulasi'><span class='lokasi_hapus' onclick="switchmenu('lokasi','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.facesample') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('facesample','lihat')" @if(strpos($hakakses->facesample, 'l') !== false) checked @endif value='l' name='facesample_lihat' id='facesample_lihat'></td>
                          <td><span class='facesample_lihat' onclick="switchmenu('facesample','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='facesample_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->facesample, 't') !== false || strpos($hakakses->facesample, 'm') !== false) checked @endif class='facesample_manipulasi_checkbox' value='t' name='facesample_tambah' id='facesample_tambah'></td>
                          <td class='facesample_manipulasi manipulasi'><span class='facesample_tambah' onclick="switchmenu('facesample','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='facesample_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->facesample, 'u') !== false || strpos($hakakses->facesample, 'm') !== false) checked @endif class='facesample_manipulasi_checkbox' value='u' name='facesample_ubah' id='facesample_ubah'></td>
                          <td class='facesample_manipulasi manipulasi'><span class='facesample_ubah' onclick="switchmenu('facesample','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='facesample_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->facesample, 'h') !== false || strpos($hakakses->facesample, 'm') !== false) checked @endif class='facesample_manipulasi_checkbox' value='h' name='facesample_hapus' id='facesample_hapus'></td>
                          <td class='facesample_manipulasi manipulasi'><span class='facesample_hapus' onclick="switchmenu('facesample','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.fingersample') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('fingersample','lihat')" @if(strpos($hakakses->fingersample, 'l') !== false) checked @endif value='l' name='fingersample_lihat' id='fingersample_lihat'></td>
                          <td><span class='fingersample_lihat' onclick="switchmenu('fingersample','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='fingersample_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->fingersample, 't') !== false || strpos($hakakses->fingersample, 'm') !== false) checked @endif class='fingersample_manipulasi_checkbox' value='t' name='fingersample_tambah' id='fingersample_tambah'></td>
                          <td class='fingersample_manipulasi manipulasi'><span class='fingersample_tambah' onclick="switchmenu('fingersample','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='fingersample_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->fingersample, 'u') !== false || strpos($hakakses->fingersample, 'm') !== false) checked @endif class='fingersample_manipulasi_checkbox' value='u' name='fingersample_ubah' id='fingersample_ubah'></td>
                          <td class='fingersample_manipulasi manipulasi'><span class='fingersample_ubah' onclick="switchmenu('fingersample','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='fingersample_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->fingersample, 'h') !== false || strpos($hakakses->fingersample, 'm') !== false) checked @endif class='fingersample_manipulasi_checkbox' value='h' name='fingersample_hapus' id='fingersample_hapus'></td>
                          <td class='fingersample_manipulasi manipulasi'><span class='fingersample_hapus' onclick="switchmenu('fingersample','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.agama') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('agama','lihat')" @if(strpos($hakakses->agama, 'l') !== false) checked @endif value='l' name='agama_lihat' id='agama_lihat'></td>
                          <td><span class='agama_lihat' onclick="switchmenu('agama','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='agama_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->agama, 't') !== false || strpos($hakakses->agama, 'm') !== false) checked @endif class='agama_manipulasi_checkbox' value='t' name='agama_tambah' id='agama_tambah'></td>
                          <td class='agama_manipulasi manipulasi'><span class='agama_tambah' onclick="switchmenu('agama','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='agama_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->agama, 'u') !== false || strpos($hakakses->agama, 'm') !== false) checked @endif class='agama_manipulasi_checkbox' value='u' name='agama_ubah' id='agama_ubah'></td>
                          <td class='agama_manipulasi manipulasi'><span class='agama_ubah' onclick="switchmenu('agama','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='agama_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->agama, 'h') !== false || strpos($hakakses->agama, 'm') !== false) checked @endif class='agama_manipulasi_checkbox' value='h' name='agama_hapus' id='agama_hapus'></td>
                          <td class='agama_manipulasi manipulasi'><span class='agama_hapus' onclick="switchmenu('agama','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.pekerjaan') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('pekerjaan','lihat')" @if(strpos($hakakses->pekerjaan, 'l') !== false) checked @endif value='l' name='pekerjaan_lihat' id='pekerjaan_lihat'></td>
                          <td><span class='pekerjaan_lihat' onclick="switchmenu('pekerjaan','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='pekerjaan_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pekerjaan, 't') !== false || strpos($hakakses->pekerjaan, 'm') !== false) checked @endif class='pekerjaan_manipulasi_checkbox' value='t' name='pekerjaan_tambah' id='pekerjaan_tambah'></td>
                          <td class='pekerjaan_manipulasi manipulasi'><span class='pekerjaan_tambah' onclick="switchmenu('pekerjaan','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='pekerjaan_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pekerjaan, 'u') !== false || strpos($hakakses->pekerjaan, 'm') !== false) checked @endif class='pekerjaan_manipulasi_checkbox' value='u' name='pekerjaan_ubah' id='pekerjaan_ubah'></td>
                          <td class='pekerjaan_manipulasi manipulasi'><span class='pekerjaan_ubah' onclick="switchmenu('pekerjaan','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='pekerjaan_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pekerjaan, 'h') !== false || strpos($hakakses->pekerjaan, 'm') !== false) checked @endif class='pekerjaan_manipulasi_checkbox' value='h' name='pekerjaan_hapus' id='pekerjaan_hapus'></td>
                          <td class='pekerjaan_manipulasi manipulasi'><span class='pekerjaan_hapus' onclick="switchmenu('pekerjaan','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.pekerjaanuser') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('pekerjaanuser','lihat')" @if(strpos($hakakses->pekerjaanuser, 'l') !== false) checked @endif value='l' name='pekerjaanuser_lihat' id='pekerjaanuser_lihat'></td>
                          <td><span class='pekerjaanuser_lihat' onclick="switchmenu('pekerjaanuser','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='pekerjaanuser_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pekerjaanuser, 't') !== false || strpos($hakakses->pekerjaanuser, 'm') !== false) checked @endif class='pekerjaanuser_manipulasi_checkbox' value='t' name='pekerjaanuser_tambah' id='pekerjaanuser_tambah'></td>
                          <td class='pekerjaanuser_manipulasi manipulasi'><span class='pekerjaanuser_tambah' onclick="switchmenu('pekerjaanuser','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='pekerjaanuser_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pekerjaanuser, 'u') !== false || strpos($hakakses->pekerjaanuser, 'm') !== false) checked @endif class='pekerjaanuser_manipulasi_checkbox' value='u' name='pekerjaanuser_ubah' id='pekerjaanuser_ubah'></td>
                          <td class='pekerjaanuser_manipulasi manipulasi'><span class='pekerjaanuser_ubah' onclick="switchmenu('pekerjaanuser','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='pekerjaanuser_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->pekerjaanuser, 'h') !== false || strpos($hakakses->pekerjaanuser, 'm') !== false) checked @endif class='pekerjaanuser_manipulasi_checkbox' value='h' name='pekerjaanuser_hapus' id='pekerjaanuser_hapus'></td>
                          <td class='pekerjaanuser_manipulasi manipulasi'><span class='pekerjaanuser_hapus' onclick="switchmenu('pekerjaanuser','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.alasan') }}</td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.alasanmasukkeluar') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('alasanmasukkeluar','lihat')" @if(strpos($hakakses->alasanmasukkeluar, 'l') !== false) checked @endif value='l' name='alasanmasukkeluar_lihat' id='alasanmasukkeluar_lihat'></td>
                          <td><span class='alasanmasukkeluar_lihat' onclick="switchmenu('alasanmasukkeluar','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='alasanmasukkeluar_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->alasanmasukkeluar, 't') !== false || strpos($hakakses->alasanmasukkeluar, 'm') !== false) checked @endif class='alasanmasukkeluar_manipulasi_checkbox' value='t' name='alasanmasukkeluar_tambah' id='alasanmasukkeluar_tambah'></td>
                          <td class='alasanmasukkeluar_manipulasi manipulasi'><span class='alasanmasukkeluar_tambah' onclick="switchmenu('alasanmasukkeluar','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='alasanmasukkeluar_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->alasanmasukkeluar, 'u') !== false || strpos($hakakses->alasanmasukkeluar, 'm') !== false) checked @endif class='alasanmasukkeluar_manipulasi_checkbox' value='u' name='alasanmasukkeluar_ubah' id='alasanmasukkeluar_ubah'></td>
                          <td class='alasanmasukkeluar_manipulasi manipulasi'><span class='alasanmasukkeluar_ubah' onclick="switchmenu('alasanmasukkeluar','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='alasanmasukkeluar_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->alasanmasukkeluar, 'h') !== false || strpos($hakakses->alasanmasukkeluar, 'm') !== false) checked @endif class='alasanmasukkeluar_manipulasi_checkbox' value='h' name='alasanmasukkeluar_hapus' id='alasanmasukkeluar_hapus'></td>
                          <td class='alasanmasukkeluar_manipulasi manipulasi'><span class='alasanmasukkeluar_hapus' onclick="switchmenu('alasanmasukkeluar','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.alasantidakmasuk') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('alasantidakmasuk','lihat')" @if(strpos($hakakses->alasantidakmasuk, 'l') !== false) checked @endif value='l' name='alasantidakmasuk_lihat' id='alasantidakmasuk_lihat'></td>
                          <td><span class='alasantidakmasuk_lihat' onclick="switchmenu('alasantidakmasuk','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='alasantidakmasuk_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->alasantidakmasuk, 't') !== false || strpos($hakakses->alasantidakmasuk, 'm') !== false) checked @endif class='alasantidakmasuk_manipulasi_checkbox' value='t' name='alasantidakmasuk_tambah' id='alasantidakmasuk_tambah'></td>
                          <td class='alasantidakmasuk_manipulasi manipulasi'><span class='alasantidakmasuk_tambah' onclick="switchmenu('alasantidakmasuk','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='alasantidakmasuk_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->alasantidakmasuk, 'u') !== false || strpos($hakakses->alasantidakmasuk, 'm') !== false) checked @endif class='alasantidakmasuk_manipulasi_checkbox' value='u' name='alasantidakmasuk_ubah' id='alasantidakmasuk_ubah'></td>
                          <td class='alasantidakmasuk_manipulasi manipulasi'><span class='alasantidakmasuk_ubah' onclick="switchmenu('alasantidakmasuk','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='alasantidakmasuk_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->alasantidakmasuk, 'h') !== false || strpos($hakakses->alasantidakmasuk, 'm') !== false) checked @endif class='alasantidakmasuk_manipulasi_checkbox' value='h' name='alasantidakmasuk_hapus' id='alasantidakmasuk_hapus'></td>
                          <td class='alasantidakmasuk_manipulasi manipulasi'><span class='alasantidakmasuk_hapus' onclick="switchmenu('alasantidakmasuk','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.absensi') }}</td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.mesin') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('mesin','lihat')" @if(strpos($hakakses->mesin, 'l') !== false) checked @endif value='l' name='mesin_lihat' id='mesin_lihat'></td>
                          <td><span class='mesin_lihat' onclick="switchmenu('mesin','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='mesin_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->mesin, 't') !== false || strpos($hakakses->mesin, 'm') !== false) checked @endif class='mesin_manipulasi_checkbox' value='t' name='mesin_tambah' id='mesin_tambah'></td>
                          <td class='mesin_manipulasi manipulasi'><span class='mesin_tambah' onclick="switchmenu('mesin','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='mesin_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->mesin, 'u') !== false || strpos($hakakses->mesin, 'm') !== false) checked @endif class='mesin_manipulasi_checkbox' value='u' name='mesin_ubah' id='mesin_ubah'></td>
                          <td class='mesin_manipulasi manipulasi'><span class='mesin_ubah' onclick="switchmenu('mesin','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='mesin_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->mesin, 'h') !== false || strpos($hakakses->mesin, 'm') !== false) checked @endif class='mesin_manipulasi_checkbox' value='h' name='mesin_hapus' id='mesin_hapus'></td>
                          <td class='mesin_manipulasi manipulasi'><span class='mesin_hapus' onclick="switchmenu('mesin','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.jamkerja') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('jamkerja','lihat')" @if(strpos($hakakses->jamkerja, 'l') !== false) checked @endif value='l' name='jamkerja_lihat' id='jamkerja_lihat'></td>
                          <td><span class='jamkerja_lihat' onclick="switchmenu('jamkerja','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='jamkerja_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->jamkerja, 't') !== false || strpos($hakakses->jamkerja, 'm') !== false) checked @endif class='jamkerja_manipulasi_checkbox' value='t' name='jamkerja_tambah' id='jamkerja_tambah'></td>
                          <td class='jamkerja_manipulasi manipulasi'><span class='jamkerja_tambah' onclick="switchmenu('jamkerja','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='jamkerja_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->jamkerja, 'u') !== false || strpos($hakakses->jamkerja, 'm') !== false) checked @endif class='jamkerja_manipulasi_checkbox' value='u' name='jamkerja_ubah' id='jamkerja_ubah'></td>
                          <td class='jamkerja_manipulasi manipulasi'><span class='jamkerja_ubah' onclick="switchmenu('jamkerja','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='jamkerja_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->jamkerja, 'h') !== false || strpos($hakakses->jamkerja, 'm') !== false) checked @endif class='jamkerja_manipulasi_checkbox' value='h' name='jamkerja_hapus' id='jamkerja_hapus'></td>
                          <td class='jamkerja_manipulasi manipulasi'><span class='jamkerja_hapus' onclick="switchmenu('jamkerja','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.harilibur') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('harilibur','lihat')" @if(strpos($hakakses->harilibur, 'l') !== false) checked @endif value='l' name='harilibur_lihat' id='harilibur_lihat'></td>
                          <td><span class='harilibur_lihat' onclick="switchmenu('harilibur','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='harilibur_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->harilibur, 't') !== false || strpos($hakakses->harilibur, 'm') !== false) checked @endif class='harilibur_manipulasi_checkbox' value='t' name='harilibur_tambah' id='harilibur_tambah'></td>
                          <td class='harilibur_manipulasi manipulasi'><span class='harilibur_tambah' onclick="switchmenu('harilibur','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='harilibur_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->harilibur, 'u') !== false || strpos($hakakses->harilibur, 'm') !== false) checked @endif class='harilibur_manipulasi_checkbox' value='u' name='harilibur_ubah' id='harilibur_ubah'></td>
                          <td class='harilibur_manipulasi manipulasi'><span class='harilibur_ubah' onclick="switchmenu('harilibur','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='harilibur_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->harilibur, 'h') !== false || strpos($hakakses->harilibur, 'm') !== false) checked @endif class='harilibur_manipulasi_checkbox' value='h' name='harilibur_hapus' id='harilibur_hapus'></td>
                          <td class='harilibur_manipulasi manipulasi'><span class='harilibur_hapus' onclick="switchmenu('harilibur','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.ijintidakmasuk') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('ijintidakmasuk','lihat')" @if(strpos($hakakses->ijintidakmasuk, 'l') !== false) checked @endif value='l' name='ijintidakmasuk_lihat' id='ijintidakmasuk_lihat'></td>
                          <td><span class='ijintidakmasuk_lihat' onclick="switchmenu('ijintidakmasuk','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='ijintidakmasuk_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->ijintidakmasuk, 't') !== false || strpos($hakakses->ijintidakmasuk, 'm') !== false) checked @endif class='ijintidakmasuk_manipulasi_checkbox' value='t' name='ijintidakmasuk_tambah' id='ijintidakmasuk_tambah'></td>
                          <td class='ijintidakmasuk_manipulasi manipulasi'><span class='ijintidakmasuk_tambah' onclick="switchmenu('ijintidakmasuk','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='ijintidakmasuk_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->ijintidakmasuk, 'u') !== false || strpos($hakakses->ijintidakmasuk, 'm') !== false) checked @endif class='ijintidakmasuk_manipulasi_checkbox' value='u' name='ijintidakmasuk_ubah' id='ijintidakmasuk_ubah'></td>
                          <td class='ijintidakmasuk_manipulasi manipulasi'><span class='ijintidakmasuk_ubah' onclick="switchmenu('ijintidakmasuk','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='ijintidakmasuk_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->ijintidakmasuk, 'h') !== false || strpos($hakakses->ijintidakmasuk, 'm') !== false) checked @endif class='ijintidakmasuk_manipulasi_checkbox' value='h' name='ijintidakmasuk_hapus' id='ijintidakmasuk_hapus'></td>
                          <td class='ijintidakmasuk_manipulasi manipulasi'><span class='ijintidakmasuk_hapus' onclick="switchmenu('ijintidakmasuk','hapus')">{{ trans('all.hapus') }}</span></td>
                          <td class='ijintidakmasuk_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->ijintidakmasuk, 'k') !== false || strpos($hakakses->ijintidakmasuk, 'm') !== false) checked @endif class='ijintidakmasuk_manipulasi_checkbox' value='k' name='ijintidakmasuk_konfirmasi' id='ijintidakmasuk_konfirmasi'></td>
                          <td class='ijintidakmasuk_manipulasi manipulasi'><span class='ijintidakmasuk_konfirmasi' onclick="switchmenu('ijintidakmasuk','konfirmasi')">{{ trans('all.konfirmasi') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.cuti') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('cuti','lihat')" @if(strpos($hakakses->cuti, 'l') !== false) checked @endif value='l' name='cuti_lihat' id='cuti_lihat'></td>
                          <td><span class='cuti_lihat' onclick="switchmenu('cuti','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='cuti_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->cuti, 'u') !== false || strpos($hakakses->cuti, 'm') !== false) checked @endif class='cuti_manipulasi_checkbox' value='u' name='cuti_ubah' id='cuti_ubah'></td>
                          <td class='cuti_manipulasi manipulasi'><span class='cuti_ubah' onclick="switchmenu('cuti','ubah')">{{ trans('all.ubah') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.logabsen') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('logabsen','lihat')" @if(strpos($hakakses->logabsen, 'l') !== false) checked @endif value='l' name='logabsen_lihat' id='logabsen_lihat'></td>
                          <td><span class='logabsen_lihat' onclick="switchmenu('logabsen','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='logabsen_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->logabsen, 't') !== false || strpos($hakakses->logabsen, 'm') !== false) checked @endif class='logabsen_manipulasi_checkbox' value='t' name='logabsen_tambah' id='logabsen_tambah'></td>
                          <td class='logabsen_manipulasi manipulasi'><span class='logabsen_tambah' onclick="switchmenu('logabsen','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='logabsen_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->logabsen, 'u') !== false || strpos($hakakses->logabsen, 'm') !== false) checked @endif class='logabsen_manipulasi_checkbox' value='u' name='logabsen_ubah' id='logabsen_ubah'></td>
                          <td class='logabsen_manipulasi manipulasi'><span class='logabsen_ubah' onclick="switchmenu('logabsen','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='logabsen_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->logabsen, 'h') !== false || strpos($hakakses->logabsen, 'm') !== false) checked @endif class='logabsen_manipulasi_checkbox' value='h' name='logabsen_hapus' id='logabsen_hapus'></td>
                          <td class='logabsen_manipulasi manipulasi'><span class='logabsen_hapus' onclick="switchmenu('logabsen','hapus')">{{ trans('all.hapus') }}</span></td>
                          <td class='logabsen_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->logabsen, 'k') !== false || strpos($hakakses->logabsen, 'm') !== false) checked @endif class='logabsen_manipulasi_checkbox' value='k' name='logabsen_konfirmasi' id='logabsen_konfirmasi'></td>
                          <td class='logabsen_manipulasi manipulasi'><span class='logabsen_konfirmasi' onclick="switchmenu('logabsen','konfirmasi')">{{ trans('all.konfirmasi') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.fingerprintconnector') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('fingerprintconnector','lihat')" @if(strpos($hakakses->fingerprintconnector, 'l') !== false) checked @endif value='l' name='fingerprintconnector_lihat' id='fingerprintconnector_lihat'></td>
                          <td><span class='fingerprintconnector_lihat' onclick="switchmenu('fingerprintconnector','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='fingerprintconnector_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->fingerprintconnector, 't') !== false || strpos($hakakses->fingerprintconnector, 'm') !== false) checked @endif class='fingerprintconnector_manipulasi_checkbox' value='t' name='fingerprintconnector_tambah' id='fingerprintconnector_tambah'></td>
                          <td class='fingerprintconnector_manipulasi manipulasi'><span class='fingerprintconnector_tambah' onclick="switchmenu('fingerprintconnector','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='fingerprintconnector_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->fingerprintconnector, 'u') !== false || strpos($hakakses->fingerprintconnector, 'm') !== false) checked @endif class='fingerprintconnector_manipulasi_checkbox' value='u' name='fingerprintconnector_ubah' id='fingerprintconnector_ubah'></td>
                          <td class='fingerprintconnector_manipulasi manipulasi'><span class='fingerprintconnector_ubah' onclick="switchmenu('fingerprintconnector','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='fingerprintconnector_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->fingerprintconnector, 'h') !== false || strpos($hakakses->fingerprintconnector, 'm') !== false) checked @endif class='fingerprintconnector_manipulasi_checkbox' value='h' name='fingerprintconnector_hapus' id='fingerprintconnector_hapus'></td>
                          <td class='fingerprintconnector_manipulasi manipulasi'><span class='fingerprintconnector_hapus' onclick="switchmenu('fingerprintconnector','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.jadwalshift') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('jadwalshift','lihat')" @if(strpos($hakakses->jadwalshift, 'l') !== false) checked @endif value='l' name='jadwalshift_lihat' id='jadwalshift_lihat'></td>
                          <td><span class='jadwalshift_lihat' onclick="switchmenu('jadwalshift','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='jadwalshift_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->jadwalshift, 'u') !== false || strpos($hakakses->jadwalshift, 'm') !== false) checked @endif class='jadwalshift_manipulasi_checkbox' value='u' name='jadwalshift_ubah' id='jadwalshift_ubah'></td>
                          <td class='jadwalshift_manipulasi manipulasi'><span class='jadwalshift_ubah' onclick="switchmenu('jadwalshift','ubah')">{{ trans('all.ubah') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.konfirmasi_flag') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('konfirmasi_flag','lihat')" @if(strpos($hakakses->konfirmasi_flag, 'l') !== false) checked @endif value='l' name='konfirmasi_flag_lihat' id='konfirmasi_flag_lihat'></td>
                          <td><span class='konfirmasi_flag_lihat' onclick="switchmenu('konfirmasi_flag','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='konfirmasi_flag_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->konfirmasi_flag, 'u') !== false || strpos($hakakses->konfirmasi_flag, 'm') !== false) checked @endif class='konfirmasi_flag_manipulasi_checkbox' value='u' name='konfirmasi_flag_ubah' id='konfirmasi_flag_ubah'></td>
                          <td class='konfirmasi_flag_manipulasi manipulasi'><span class='konfirmasi_flag_ubah' onclick="switchmenu('konfirmasi_flag','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='konfirmasi_flag_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->konfirmasi_flag, 'k') !== false || strpos($hakakses->konfirmasi_flag, 'm') !== false) checked @endif class='konfirmasi_flag_manipulasi_checkbox' value='k' name='konfirmasi_flag_konfirmasi' id='konfirmasi_flag_konfirmasi'></td>
                          <td class='konfirmasi_flag_manipulasi manipulasi'><span class='konfirmasi_flag_konfirmasi' onclick="switchmenu('konfirmasi_flag','konfirmasi')">{{ trans('all.konfirmasi') }}</span></td>
                        </tr>
                        @if(Session::get('perbolehkanpayroll_perusahaan') == 'y')
                          <tr>
                            <td style='padding-left:20px;'>{{ trans('all.payroll') }}</td>
                          </tr>
                          <tr>
                              <td style='padding-left:40px;'>{{ trans('all.pengaturan') }}</td>
                              <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('payrollpengaturan','lihat')" @if(strpos($hakakses->payrollpengaturan, 'l') !== false) checked @endif value='l' name='payrollpengaturan_lihat' id='payrollpengaturan_lihat'></td>
                              <td><span class='payrollpengaturan_lihat' onclick="switchmenu('payrollpengaturan','lihat')">{{ trans('all.lihat') }}</span></td>
                              <td class='payrollpengaturan_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->payrollpengaturan, 'u') !== false || strpos($hakakses->payrollpengaturan, 'm') !== false) checked @endif class='payrollpengaturan_manipulasi_checkbox' value='u' name='payrollpengaturan_ubah' id='payrollpengaturan_ubah'></td>
                              <td class='payrollpengaturan_manipulasi manipulasi'><span class='payrollpengaturan_ubah' onclick="switchmenu('payrollpengaturan','ubah')">{{ trans('all.ubah') }}</span></td>
                            </tr>
                          <tr>
                            <td style='padding-left:40px;'>{{ trans('all.komponenmaster') }}</td>
                            <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('payrollkomponenmaster','lihat')" @if(strpos($hakakses->payrollkomponenmaster, 'l') !== false) checked @endif value='l' name='payrollkomponenmaster_lihat' id='payrollkomponenmaster_lihat'></td>
                            <td><span class='payrollkomponenmaster_lihat' onclick="switchmenu('payrollkomponenmaster','lihat')">{{ trans('all.lihat') }}</span></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->payrollkomponenmaster, 't') !== false || strpos($hakakses->payrollkomponenmaster, 'm') !== false) checked @endif class='payrollkomponenmaster_manipulasi_checkbox' value='t' name='payrollkomponenmaster_tambah' id='payrollkomponenmaster_tambah'></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><span class='payrollkomponenmaster_tambah' onclick="switchmenu('payrollkomponenmaster','tambah')">{{ trans('all.tambah') }}</span></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->payrollkomponenmaster, 'u') !== false || strpos($hakakses->payrollkomponenmaster, 'm') !== false) checked @endif class='payrollkomponenmaster_manipulasi_checkbox' value='u' name='payrollkomponenmaster_ubah' id='payrollkomponenmaster_ubah'></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><span class='payrollkomponenmaster_ubah' onclick="switchmenu('payrollkomponenmaster','ubah')">{{ trans('all.ubah') }}</span></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->payrollkomponenmaster, 'h') !== false || strpos($hakakses->payrollkomponenmaster, 'm') !== false) checked @endif class='payrollkomponenmaster_manipulasi_checkbox' value='h' name='payrollkomponenmaster_hapus' id='payrollkomponenmaster_hapus'></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><span class='payrollkomponenmaster_hapus' onclick="switchmenu('payrollkomponenmaster','hapus')">{{ trans('all.hapus') }}</span></td>
                          </tr>
                          <tr>
                            <td style='padding-left:40px;'>{{ trans('all.komponeninputmanual') }}</td>
                            <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('payrollkomponeninputmanual','lihat')" @if(strpos($hakakses->payrollkomponeninputmanual, 'l') !== false) checked @endif value='l' name='payrollkomponeninputmanual_lihat' id='payrollkomponeninputmanual_lihat'></td>
                            <td><span class='payrollkomponeninputmanual_lihat' onclick="switchmenu('payrollkomponeninputmanual','lihat')">{{ trans('all.lihat') }}</span></td>
                            <td class='payrollkomponeninputmanual_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->payrollkomponeninputmanual, 'u') !== false || strpos($hakakses->payrollkomponeninputmanual, 'm') !== false) checked @endif class='payrollkomponeninputmanual_manipulasi_checkbox' value='u' name='payrollkomponeninputmanual_ubah' id='payrollkomponeninputmanual_ubah'></td>
                            <td class='payrollkomponeninputmanual_manipulasi manipulasi'><span class='payrollkomponeninputmanual_ubah' onclick="switchmenu('payrollkomponeninputmanual','ubah')">{{ trans('all.ubah') }}</span></td>
                          </tr>
                        @endif
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.lainlain') }}</td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.slideshow') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('slideshow','lihat')" @if(strpos($hakakses->slideshow, 'l') !== false) checked @endif value='l' name='slideshow_lihat' id='slideshow_lihat'></td>
                          <td><span class='slideshow_lihat' onclick="switchmenu('slideshow','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='slideshow_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->slideshow, 't') !== false || strpos($hakakses->slideshow, 'm') !== false) checked @endif class='slideshow_manipulasi_checkbox' value='t' name='slideshow_tambah' id='slideshow_tambah'></td>
                          <td class='slideshow_manipulasi manipulasi'><span class='slideshow_tambah' onclick="switchmenu('slideshow','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='slideshow_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->slideshow, 'u') !== false || strpos($hakakses->slideshow, 'm') !== false) checked @endif class='slideshow_manipulasi_checkbox' value='u' name='slideshow_ubah' id='slideshow_ubah'></td>
                          <td class='slideshow_manipulasi manipulasi'><span class='slideshow_ubah' onclick="switchmenu('slideshow','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='slideshow_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->slideshow, 'h') !== false || strpos($hakakses->slideshow, 'm') !== false) checked @endif class='slideshow_manipulasi_checkbox' value='h' name='slideshow_hapus' id='slideshow_hapus'></td>
                          <td class='slideshow_manipulasi manipulasi'><span class='slideshow_hapus' onclick="switchmenu('slideshow','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.batasan') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('batasan','lihat')" @if(strpos($hakakses->batasan, 'l') !== false) checked @endif value='l' name='batasan_lihat' id='batasan_lihat'></td>
                          <td><span class='batasan_lihat' onclick="switchmenu('batasan','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='batasan_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->batasan, 't') !== false || strpos($hakakses->batasan, 'm') !== false) checked @endif class='batasan_manipulasi_checkbox' value='t' name='batasan_tambah' id='batasan_tambah'></td>
                          <td class='batasan_manipulasi manipulasi'><span class='batasan_tambah' onclick="switchmenu('batasan','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='batasan_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->batasan, 'u') !== false || strpos($hakakses->batasan, 'm') !== false) checked @endif class='batasan_manipulasi_checkbox' value='u' name='batasan_ubah' id='batasan_ubah'></td>
                          <td class='batasan_manipulasi manipulasi'><span class='batasan_ubah' onclick="switchmenu('batasan','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='batasan_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->batasan, 'h') !== false || strpos($hakakses->batasan, 'm') !== false) checked @endif class='batasan_manipulasi_checkbox' value='h' name='batasan_hapus' id='batasan_hapus'></td>
                          <td class='batasan_manipulasi manipulasi'><span class='batasan_hapus' onclick="switchmenu('batasan','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.hakakses') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('hakakses','lihat')" @if(strpos($hakakses->hakakses, 'l') !== false) checked @endif value='l' name='hakakses_lihat' id='hakakses_lihat'></td>
                          <td><span class='hakakses_lihat' onclick="switchmenu('hakakses','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='hakakses_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->hakakses, 't') !== false || strpos($hakakses->hakakses, 'm') !== false) checked @endif class='hakakses_manipulasi_checkbox' value='t' name='hakakses_tambah' id='hakakses_tambah'></td>
                          <td class='hakakses_manipulasi manipulasi'><span class='hakakses_tambah' onclick="switchmenu('hakakses','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='hakakses_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->hakakses, 'u') !== false || strpos($hakakses->hakakses, 'm') !== false) checked @endif class='hakakses_manipulasi_checkbox' value='u' name='hakakses_ubah' id='hakakses_ubah'></td>
                          <td class='hakakses_manipulasi manipulasi'><span class='hakakses_ubah' onclick="switchmenu('hakakses','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='hakakses_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->hakakses, 'h') !== false || strpos($hakakses->hakakses, 'm') !== false) checked @endif class='hakakses_manipulasi_checkbox' value='h' name='hakakses_hapus' id='hakakses_hapus'></td>
                          <td class='hakakses_manipulasi manipulasi'><span class='hakakses_hapus' onclick="switchmenu('hakakses','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.setulangkatasandipegawai') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('setulangkatasandipegawai','lihat')" @if(strpos($hakakses->setulangkatasandipegawai, 'l') !== false) checked @endif value='l' name='setulangkatasandipegawai_lihat' id='setulangkatasandipegawai_lihat'></td>
                          <td><span class='setulangkatasandipegawai_lihat' onclick="switchmenu('setulangkatasandipegawai','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.postingdata') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('postingdata','ijinkan')" @if(strpos($hakakses->postingdata, 'i') !== false) checked @endif value='i' name='postingdata_ijinkan' id='postingdata_ijinkan'></td>
                          <td><span class='postingdata_ijinkan' onclick="switchmenu('postingdata','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.hapusdata') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('hapusdata','ijinkan')" @if(strpos($hakakses->hapusdata, 'i') !== false) checked @endif value='i' name='hapusdata_ijinkan' id='hapusdata_ijinkan'></td>
                          <td><span class='hapusdata_ijinkan' onclick="switchmenu('hapusdata','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:40px;'>{{ trans('all.supervisi') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('supervisi','ijinkan')" @if(strpos($hakakses->supervisi, 'i') !== false) checked @endif value='i' name='supervisi_ijinkan' id='supervisi_ijinkan'></td>
                          <td><span class='supervisi_ijinkan' onclick="switchmenu('supervisi','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:0px;'>{{ trans('all.pengaturan') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('pengaturan','ubah')" @if(strpos($hakakses->pengaturan, 'u') !== false || strpos($hakakses->pengaturan, 'l') !== false || strpos($hakakses->pengaturan, 'm') !== false) checked @endif value='u' name='pengaturan_ubah' id='pengaturan_ubah'></td>
                          <td><span class='pengaturan_ubah' onclick="switchmenu('pengaturan','ubah')">{{ trans('all.ubah') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:0;'>{{ trans('all.laporan') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporan','lihat')" @if(strpos($hakakses->laporan, 'l') !== false) checked @endif value='l' name='laporan_lihat' id='laporan_lihat'></td>
                          <td><span class='laporan_lihat' onclick="switchmenu('laporan','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.perpegawai') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanperpegawai','lihat')" @if(strpos($hakakses->laporanperpegawai, 'l') !== false) checked @endif value='l' name='laporanperpegawai_lihat' id='laporanperpegawai_lihat'></td>
                          <td><span class='laporanperpegawai_lihat' onclick="switchmenu('laporanperpegawai','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.logabsen') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanlogabsen','lihat')" @if(strpos($hakakses->laporanlogabsen, 'l') !== false) checked @endif value='l' name='laporanlogabsen_lihat' id='laporanlogabsen_lihat'></td>
                          <td><span class='laporanlogabsen_lihat' onclick="switchmenu('laporanlogabsen','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.kehadiran') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporankehadiran','lihat')" @if(strpos($hakakses->laporankehadiran, 'l') !== false) checked @endif value='l' name='laporankehadiran_lihat' id='laporankehadiran_lihat'></td>
                          <td><span class='laporankehadiran_lihat' onclick="switchmenu('laporankehadiran','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.rekapshift') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanrekapparuhawktu','lihat')" @if(strpos($hakakses->laporanrekapparuhwaktu, 'l') !== false) checked @endif value='l' name='laporanrekapparuhwaktu_lihat' id='laporanrekapparuhwaktu_lihat'></td>
                          <td><span class='laporanrekapparuhwaktu_lihat' onclick="switchmenu('laporanrekapparuhwaktu','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.pertanggal') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanpertanggal','lihat')" @if(strpos($hakakses->laporanpertanggal, 'l') !== false) checked @endif value='l' name='laporanpertanggal_lihat' id='laporanpertanggal_lihat'></td>
                          <td><span class='laporanpertanggal_lihat' onclick="switchmenu('laporanpertanggal','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.ekspor') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanekspor','lihat')" @if(strpos($hakakses->laporanekspor, 'l') !== false) checked @endif value='l' name='laporanekspor_lihat' id='laporanekspor_lihat'></td>
                          <td><span class='laporanekspor_lihat' onclick="switchmenu('laporanekspor','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.logtrackerpegawai') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanlogtrackerpegawai','lihat')" @if(strpos($hakakses->laporanlogtrackerpegawai, 'l') !== false) checked @endif value='l' name='laporanlogtrackerpegawai_lihat' id='laporanlogtrackerpegawai_lihat'></td>
                          <td><span class='laporanlogtrackerpegawai_lihat' onclick="switchmenu('laporanlogtrackerpegawai','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.lainnya') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanlainnya','lihat')" @if(strpos($hakakses->laporanlainnya, 'l') !== false) checked @endif value='l' name='laporanlainnya_lihat' id='laporanlainnya_lihat'></td>
                          <td><span class='laporanlainnya_lihat' onclick="switchmenu('laporanlainnya','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.perlokasi') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanperlokasi','lihat')" @if(strpos($hakakses->laporanperlokasi, 'l') !== false) checked @endif value='l' name='laporanperlokasi_lihat' id='laporanperlokasi_lihat'></td>
                          <td><span class='laporanperlokasi_lihat' onclick="switchmenu('laporanperlokasi','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.pekerjaanuser') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanpekerjaanuser','lihat')" @if(strpos($hakakses->laporanpekerjaanuser, 'l') !== false) checked @endif value='l' name='laporanpekerjaanuser_lihat' id='laporanpekerjaanuser_lihat'></td>
                          <td><span class='laporanpekerjaanuser_lihat' onclick="switchmenu('laporanpekerjaanuser','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.custom') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporancustom','lihat')" @if(strpos($hakakses->laporancustom, 'l') !== false) checked @endif value='l' name='laporancustom_lihat' id='laporancustom_lihat'></td>
                          <td><span class='laporancustom_lihat' onclick="switchmenu('laporancustom','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='laporancustom_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->laporancustom, 't') !== false || strpos($hakakses->laporancustom, 'm') !== false) checked @endif class='laporancustom_manipulasi_checkbox' value='t' name='laporancustom_tambah' id='laporancustom_tambah'></td>
                          <td class='laporancustom_manipulasi manipulasi'><span class='laporancustom_tambah' onclick="switchmenu('laporancustom','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='laporancustom_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->laporancustom, 'u') !== false || strpos($hakakses->laporancustom, 'm') !== false) checked @endif class='laporancustom_manipulasi_checkbox' value='u' name='laporancustom_ubah' id='laporancustom_ubah'></td>
                          <td class='laporancustom_manipulasi manipulasi'><span class='laporancustom_ubah' onclick="switchmenu('laporancustom','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='laporancustom_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->laporancustom, 'h') !== false || strpos($hakakses->laporancustom, 'm') !== false) checked @endif class='laporancustom_manipulasi_checkbox' value='h' name='laporancustom_hapus' id='laporancustom_hapus'></td>
                          <td class='laporancustom_manipulasi manipulasi'><span class='laporancustom_hapus' onclick="switchmenu('laporancustom','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.customdashboard') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('customdashboard','lihat')" @if(strpos($hakakses->customdashboard, 'l') !== false) checked @endif value='l' name='customdashboard_lihat' id='customdashboard_lihat'></td>
                          <td><span class='customdashboard_lihat' onclick="switchmenu('customdashboard','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='customdashboard_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->customdashboard, 't') !== false || strpos($hakakses->customdashboard, 'm') !== false) checked @endif class='customdashboard_manipulasi_checkbox' value='t' name='customdashboard_tambah' id='customdashboard_tambah'></td>
                          <td class='customdashboard_manipulasi manipulasi'><span class='customdashboard_tambah' onclick="switchmenu('customdashboard','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='customdashboard_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->customdashboard, 'u') !== false || strpos($hakakses->customdashboard, 'm') !== false) checked @endif class='customdashboard_manipulasi_checkbox' value='u' name='customdashboard_ubah' id='customdashboard_ubah'></td>
                          <td class='customdashboard_manipulasi manipulasi'><span class='customdashboard_ubah' onclick="switchmenu('customdashboard','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='customdashboard_manipulasi manipulasi'><input type='checkbox' @if(strpos($hakakses->customdashboard, 'h') !== false || strpos($hakakses->customdashboard, 'm') !== false) checked @endif class='customdashboard_manipulasi_checkbox' value='h' name='customdashboard_hapus' id='customdashboard_hapus'></td>
                          <td class='customdashboard_manipulasi manipulasi'><span class='customdashboard_hapus' onclick="switchmenu('customdashboard','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:0;'>{{ trans('all.riwayat') }}</td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.pengguna') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('riwayatpengguna','lihat')" @if(strpos($hakakses->riwayatpengguna, 'l') !== false) checked @endif value='l' name='riwayatpengguna_lihat' id='riwayatpengguna_lihat'></td>
                          <td><span class='riwayatpengguna_lihat' onclick="switchmenu('riwayatpengguna','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.pegawai') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('riwayatpegawai','lihat')" @if(strpos($hakakses->riwayatpegawai, 'l') !== false) checked @endif value='l' name='riwayatpegawai_lihat' id='riwayatpegawai_lihat'></td>
                          <td><span class='riwayatpegawai_lihat' onclick="switchmenu('riwayatpegawai','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.sms') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('riwayatsms','lihat')" @if(strpos($hakakses->riwayatsms, 'l') !== false) checked @endif value='l' name='riwayatsms_lihat' id='riwayatsms_lihat'></td>
                          <td><span class='riwayatsms_lihat' onclick="switchmenu('riwayatsms','lihat')">{{ trans('all.lihat') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:0;'>{{ trans('all.notifikasi') }}</td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.notifikasiijintidakmasuk') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('notifikasiijintidakmasuk','ijinkan')" @if(strpos($hakakses->notifikasiijintidakmasuk, 'i') !== false) checked @endif value='i' name='notifikasiijintidakmasuk_ijinkan' id='notifikasiijintidakmasuk_ijinkan'></td>
                          <td><span class='notifikasiijintidakmasuk_ijinkan' onclick="switchmenu('notifikasiijintidakmasuk','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.notifikasiriwayatabsen') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('notifikasiriwayatabsen','ijinkan')" @if(strpos($hakakses->notifikasiriwayatabsen, 'i') !== false) checked @endif value='i' name='notifikasiriwayatabsen_ijinkan' id='notifikasiriwayatabsen_ijinkan'></td>
                          <td><span class='notifikasiriwayatabsen_ijinkan' onclick="switchmenu('notifikasiriwayatabsen','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.notifikasiterlambat') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('notifikasiterlambat','ijinkan')" @if(strpos($hakakses->notifikasiterlambat, 'i') !== false) checked @endif value='i' name='notifikasiterlambat_ijinkan' id='notifikasiterlambat_ijinkan'></td>
                          <td><span class='notifikasiterlambat_ijinkan' onclick="switchmenu('notifikasiterlambat','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.notifikasipulangawal') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('notifikasipulangawal','ijinkan')" @if(strpos($hakakses->notifikasipulangawal, 'i') !== false) checked @endif value='i' name='notifikasipulangawal_ijinkan' id='notifikasipulangawal_ijinkan'></td>
                          <td><span class='notifikasipulangawal_ijinkan' onclick="switchmenu('notifikasipulangawal','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                        </tr>
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.notifikasilembur') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('notifikasilembur','ijinkan')" @if(strpos($hakakses->notifikasilembur, 'i') !== false) checked @endif value='i' name='notifikasilembur_ijinkan' id='notifikasilembur_ijinkan'></td>
                          <td><span class='notifikasilembur_ijinkan' onclick="switchmenu('notifikasilembur','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                        </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td colspan=2>
                    <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button type="button" id="kembali" onclick="return ke('../../hakakses')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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