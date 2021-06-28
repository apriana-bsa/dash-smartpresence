@extends('layouts.master')
@section('title', trans('all.hakakses'))
@section('content')

	<style>
  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
  }
  td{
      padding:5px;
  }
  span{
      cursor: pointer;
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

    $('.manipulasi').css('display','none');
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
          	<form action="{{ url('datainduk/lainlain/hakakses') }}" method="post" onsubmit="return validasi()">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <table width="640px">
                <tr>
                    <td width="210px">{{ trans('all.nama') }}</td>
                    <td>
                        <input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="30">
                    </td>
                </tr>
                <tr>
                  <td colspan=2>
                    <table>
                      <tr>
                        <td style='padding-left:0px;' width="230px">{{ trans('all.ajakan') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' value='i' name='ajakan_ijinkan' id='ajakan_ijinkan'></td>
                        <td><span class='ajakan_ijinkan' onclick="switchmenu('ajakan','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:0px;'>{{ trans('all.pengelola') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('pengelola','lihat')" value='l' name='pengelola_lihat' id='pengelola_lihat'></td>
                        <td><span class='pengelola_lihat' onclick="switchmenu('pengelola','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='pengelola_manipulasi manipulasi'><input type='checkbox' class='pengelola_manipulasi_checkbox' value='u' name='pengelola_ubah' id='pengelola_ubah'></td>
                        <td class='pengelola_manipulasi manipulasi'><span class='pengelola_ubah' onclick="switchmenu('pengelola','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='pengelola_manipulasi manipulasi'><input type='checkbox' class='pengelola_manipulasi_checkbox' value='h' name='pengelola_hapus' id='pengelola_hapus'></td>
                        <td class='pengelola_manipulasi manipulasi'><span class='pengelola_hapus' onclick="switchmenu('pengelola','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:0px;'>{{ trans('all.datainduk') }}</td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.perusahaan') }}</td>
                        <td style='padding-left:0px;' class='perusahaan_manipulasi'><input type='checkbox' class='perusahaan_manipulasi_checkbox' value='u' name='perusahaan_ubah' id='perusahaan_ubah'></td>
                        <td class='perusahaan_manipulasi'><span class='perusahaan_ubah' onclick="switchmenu('perusahaan','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='perusahaan_manipulasi'><input type='checkbox' class='perusahaan_manipulasi_checkbox' value='h' name='perusahaan_hapus' id='perusahaan_hapus'></td>
                        <td class='perusahaan_manipulasi'><span class='perusahaan_hapus' onclick="switchmenu('perusahaan','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.pegawai') }}</td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.pegawai') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('pegawai','lihat')" value='l' name='pegawai_lihat' id='pegawai_lihat'></td>
                        <td><span class='pegawai_lihat' onclick="switchmenu('pegawai','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='pegawai_manipulasi manipulasi'><input type='checkbox' class='pegawai_manipulasi_checkbox' value='t' name='pegawai_tambah' id='pegawai_tambah'></td>
                        <td class='pegawai_manipulasi manipulasi'><span class='pegawai_tambah' onclick="switchmenu('pegawai','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='pegawai_manipulasi manipulasi'><input type='checkbox' class='pegawai_manipulasi_checkbox' value='u' name='pegawai_ubah' id='pegawai_ubah'></td>
                        <td class='pegawai_manipulasi manipulasi'><span class='pegawai_ubah' onclick="switchmenu('pegawai','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='pegawai_manipulasi manipulasi'><input type='checkbox' class='pegawai_manipulasi_checkbox' value='h' name='pegawai_hapus' id='pegawai_hapus'></td>
                        <td class='pegawai_manipulasi manipulasi'><span class='pegawai_hapus' onclick="switchmenu('pegawai','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.aturatributdanlokasi') }}</td>
                        <td style="padding-left:0;"><input type='checkbox' value='u' name='aturatributdanlokasi_ubah' id='aturatributdanlokasi_ubah'></td>
                        <td><span class='aturatributdanlokasi_ubah' onclick="switchmenu('aturatributdanlokasi','ubah')">{{ trans('all.ubah') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.atribut') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('atribut','lihat')" value='l' name='atribut_lihat' id='atribut_lihat'></td>
                        <td><span class='atribut_lihat' onclick="switchmenu('atribut','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='atribut_manipulasi manipulasi'><input type='checkbox' class='atribut_manipulasi_checkbox' value='t' name='atribut_tambah' id='atribut_tambah'></td>
                        <td class='atribut_manipulasi manipulasi'><span class='atribut_tambah' onclick="switchmenu('atribut','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='atribut_manipulasi manipulasi'><input type='checkbox' class='atribut_manipulasi_checkbox' value='u' name='atribut_ubah' id='atribut_ubah'></td>
                        <td class='atribut_manipulasi manipulasi'><span class='atribut_ubah' onclick="switchmenu('atribut','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='atribut_manipulasi manipulasi'><input type='checkbox' class='atribut_manipulasi_checkbox' value='h' name='atribut_hapus' id='atribut_hapus'></td>
                        <td class='atribut_manipulasi manipulasi'><span class='atribut_hapus' onclick="switchmenu('atribut','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.penempatan') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('penempatan','lihat')" value='l' name='penempatan_lihat' id='penempatan_lihat'></td>
                        <td><span class='penempatan_lihat' onclick="switchmenu('penempatan','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='penempatan_manipulasi manipulasi'><input type='checkbox' class='penempatan_manipulasi_checkbox' value='t' name='penempatan_tambah' id='penempatan_tambah'></td>
                        <td class='penempatan_manipulasi manipulasi'><span class='penempatan_tambah' onclick="switchmenu('penempatan','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='penempatan_manipulasi manipulasi'><input type='checkbox' class='penempatan_manipulasi_checkbox' value='u' name='penempatan_ubah' id='penempatan_ubah'></td>
                        <td class='penempatan_manipulasi manipulasi'><span class='penempatan_ubah' onclick="switchmenu('penempatan','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='penempatan_manipulasi manipulasi'><input type='checkbox' class='penempatan_manipulasi_checkbox' value='h' name='penempatan_hapus' id='penempatan_hapus'></td>
                        <td class='penempatan_manipulasi manipulasi'><span class='penempatan_hapus' onclick="switchmenu('penempatan','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.lokasi') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('lokasi','lihat')" value='l' name='lokasi_lihat' id='lokasi_lihat'></td>
                        <td><span class='lokasi_lihat' onclick="switchmenu('lokasi','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='lokasi_manipulasi manipulasi'><input type='checkbox' class='lokasi_manipulasi_checkbox' value='t' name='lokasi_tambah' id='lokasi_tambah'></td>
                        <td class='lokasi_manipulasi manipulasi'><span class='lokasi_tambah' onclick="switchmenu('lokasi','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='lokasi_manipulasi manipulasi'><input type='checkbox' class='lokasi_manipulasi_checkbox' value='u' name='lokasi_ubah' id='lokasi_ubah'></td>
                        <td class='lokasi_manipulasi manipulasi'><span class='lokasi_ubah' onclick="switchmenu('lokasi','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='lokasi_manipulasi manipulasi'><input type='checkbox' class='lokasi_manipulasi_checkbox' value='h' name='lokasi_hapus' id='lokasi_hapus'></td>
                        <td class='lokasi_manipulasi manipulasi'><span class='lokasi_hapus' onclick="switchmenu('lokasi','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.facesample') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('facesample','lihat')" value='l' name='facesample_lihat' id='facesample_lihat'></td>
                        <td><span class='facesample_lihat' onclick="switchmenu('facesample','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='facesample_manipulasi manipulasi'><input type='checkbox' class='facesample_manipulasi_checkbox' value='t' name='facesample_tambah' id='facesample_tambah'></td>
                        <td class='facesample_manipulasi manipulasi'><span class='facesample_tambah' onclick="switchmenu('facesample','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='facesample_manipulasi manipulasi'><input type='checkbox' class='facesample_manipulasi_checkbox' value='u' name='facesample_ubah' id='facesample_ubah'></td>
                        <td class='facesample_manipulasi manipulasi'><span class='facesample_ubah' onclick="switchmenu('facesample','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='facesample_manipulasi manipulasi'><input type='checkbox' class='facesample_manipulasi_checkbox' value='h' name='facesample_hapus' id='facesample_hapus'></td>
                        <td class='facesample_manipulasi manipulasi'><span class='facesample_hapus' onclick="switchmenu('facesample','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.fingersample') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('fingersample','lihat')" value='l' name='fingersample_lihat' id='fingersample_lihat'></td>
                        <td><span class='fingersample_lihat' onclick="switchmenu('fingersample','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='fingersample_manipulasi manipulasi'><input type='checkbox' class='fingersample_manipulasi_checkbox' value='t' name='fingersample_tambah' id='fingersample_tambah'></td>
                        <td class='fingersample_manipulasi manipulasi'><span class='fingersample_tambah' onclick="switchmenu('fingersample','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='fingersample_manipulasi manipulasi'><input type='checkbox' class='fingersample_manipulasi_checkbox' value='u' name='fingersample_ubah' id='fingersample_ubah'></td>
                        <td class='fingersample_manipulasi manipulasi'><span class='fingersample_ubah' onclick="switchmenu('fingersample','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='fingersample_manipulasi manipulasi'><input type='checkbox' class='fingersample_manipulasi_checkbox' value='h' name='fingersample_hapus' id='fingersample_hapus'></td>
                        <td class='fingersample_manipulasi manipulasi'><span class='fingersample_hapus' onclick="switchmenu('fingersample','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.agama') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('agama','lihat')" value='l' name='agama_lihat' id='agama_lihat'></td>
                        <td><span class='agama_lihat' onclick="switchmenu('agama','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='agama_manipulasi manipulasi'><input type='checkbox' class='agama_manipulasi_checkbox' value='t' name='agama_tambah' id='agama_tambah'></td>
                        <td class='agama_manipulasi manipulasi'><span class='agama_tambah' onclick="switchmenu('agama','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='agama_manipulasi manipulasi'><input type='checkbox' class='agama_manipulasi_checkbox' value='u' name='agama_ubah' id='agama_ubah'></td>
                        <td class='agama_manipulasi manipulasi'><span class='agama_ubah' onclick="switchmenu('agama','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='agama_manipulasi manipulasi'><input type='checkbox' class='agama_manipulasi_checkbox' value='h' name='agama_hapus' id='agama_hapus'></td>
                        <td class='agama_manipulasi manipulasi'><span class='agama_hapus' onclick="switchmenu('agama','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.pekerjaan') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('pekerjaan','lihat')" value='l' name='pekerjaan_lihat' id='pekerjaan_lihat'></td>
                        <td><span class='pekerjaan_lihat' onclick="switchmenu('pekerjaan','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='pekerjaan_manipulasi manipulasi'><input type='checkbox' class='pekerjaan_manipulasi_checkbox' value='t' name='pekerjaan_tambah' id='pekerjaan_tambah'></td>
                        <td class='pekerjaan_manipulasi manipulasi'><span class='pekerjaan_tambah' onclick="switchmenu('pekerjaan','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='pekerjaan_manipulasi manipulasi'><input type='checkbox' class='pekerjaan_manipulasi_checkbox' value='u' name='pekerjaan_ubah' id='pekerjaan_ubah'></td>
                        <td class='pekerjaan_manipulasi manipulasi'><span class='pekerjaan_ubah' onclick="switchmenu('pekerjaan','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='pekerjaan_manipulasi manipulasi'><input type='checkbox' class='pekerjaan_manipulasi_checkbox' value='h' name='pekerjaan_hapus' id='pekerjaan_hapus'></td>
                        <td class='pekerjaan_manipulasi manipulasi'><span class='pekerjaan_hapus' onclick="switchmenu('pekerjaan','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.pekerjaanuser') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('pekerjaanuser','lihat')" value='l' name='pekerjaanuser_lihat' id='pekerjaanuser_lihat'></td>
                        <td><span class='pekerjaanuser_lihat' onclick="switchmenu('pekerjaanuser','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='pekerjaanuser_manipulasi manipulasi'><input type='checkbox' class='pekerjaanuser_manipulasi_checkbox' value='t' name='pekerjaanuser_tambah' id='pekerjaanuser_tambah'></td>
                        <td class='pekerjaanuser_manipulasi manipulasi'><span class='pekerjaanuser_tambah' onclick="switchmenu('pekerjaanuser','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='pekerjaanuser_manipulasi manipulasi'><input type='checkbox' class='pekerjaanuser_manipulasi_checkbox' value='u' name='pekerjaanuser_ubah' id='pekerjaanuser_ubah'></td>
                        <td class='pekerjaanuser_manipulasi manipulasi'><span class='pekerjaanuser_ubah' onclick="switchmenu('pekerjaanuser','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='pekerjaanuser_manipulasi manipulasi'><input type='checkbox' class='pekerjaanuser_manipulasi_checkbox' value='h' name='pekerjaanuser_hapus' id='pekerjaanuser_hapus'></td>
                        <td class='pekerjaanuser_manipulasi manipulasi'><span class='pekerjaanuser_hapus' onclick="switchmenu('pekerjaanuser','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.alasan') }}</td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.alasanmasukkeluar') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('alasanmasukkeluar','lihat')" value='l' name='alasanmasukkeluar_lihat' id='alasanmasukkeluar_lihat'></td>
                        <td><span class='alasanmasukkeluar_lihat' onclick="switchmenu('alasanmasukkeluar','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='alasanmasukkeluar_manipulasi manipulasi'><input type='checkbox' class='alasanmasukkeluar_manipulasi_checkbox' value='t' name='alasanmasukkeluar_tambah' id='alasanmasukkeluar_tambah'></td>
                        <td class='alasanmasukkeluar_manipulasi manipulasi'><span class='alasanmasukkeluar_tambah' onclick="switchmenu('alasanmasukkeluar','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='alasanmasukkeluar_manipulasi manipulasi'><input type='checkbox' class='alasanmasukkeluar_manipulasi_checkbox' value='u' name='alasanmasukkeluar_ubah' id='alasanmasukkeluar_ubah'></td>
                        <td class='alasanmasukkeluar_manipulasi manipulasi'><span class='alasanmasukkeluar_ubah' onclick="switchmenu('alasanmasukkeluar','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='alasanmasukkeluar_manipulasi manipulasi'><input type='checkbox' class='alasanmasukkeluar_manipulasi_checkbox' value='h' name='alasanmasukkeluar_hapus' id='alasanmasukkeluar_hapus'></td>
                        <td class='alasanmasukkeluar_manipulasi manipulasi'><span class='alasanmasukkeluar_hapus' onclick="switchmenu('alasanmasukkeluar','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.alasantidakmasuk') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('alasantidakmasuk','lihat')" value='l' name='alasantidakmasuk_lihat' id='alasantidakmasuk_lihat'></td>
                        <td><span class='alasantidakmasuk_lihat' onclick="switchmenu('alasantidakmasuk','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='alasantidakmasuk_manipulasi manipulasi'><input type='checkbox' class='alasantidakmasuk_manipulasi_checkbox' value='t' name='alasantidakmasuk_tambah' id='alasantidakmasuk_tambah'></td>
                        <td class='alasantidakmasuk_manipulasi manipulasi'><span class='alasantidakmasuk_tambah' onclick="switchmenu('alasantidakmasuk','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='alasantidakmasuk_manipulasi manipulasi'><input type='checkbox' class='alasantidakmasuk_manipulasi_checkbox' value='u' name='alasantidakmasuk_ubah' id='alasantidakmasuk_ubah'></td>
                        <td class='alasantidakmasuk_manipulasi manipulasi'><span class='alasantidakmasuk_ubah' onclick="switchmenu('alasantidakmasuk','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='alasantidakmasuk_manipulasi manipulasi'><input type='checkbox' class='alasantidakmasuk_manipulasi_checkbox' value='h' name='alasantidakmasuk_hapus' id='alasantidakmasuk_hapus'></td>
                        <td class='alasantidakmasuk_manipulasi manipulasi'><span class='alasantidakmasuk_hapus' onclick="switchmenu('alasantidakmasuk','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.absensi') }}</td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.mesin') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('mesin','lihat')" value='l' name='mesin_lihat' id='mesin_lihat'></td>
                        <td><span class='mesin_lihat' onclick="switchmenu('mesin','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='mesin_manipulasi manipulasi'><input type='checkbox' class='mesin_manipulasi_checkbox' value='t' name='mesin_tambah' id='mesin_tambah'></td>
                        <td class='mesin_manipulasi manipulasi'><span class='mesin_tambah' onclick="switchmenu('mesin','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='mesin_manipulasi manipulasi'><input type='checkbox' class='mesin_manipulasi_checkbox' value='u' name='mesin_ubah' id='mesin_ubah'></td>
                        <td class='mesin_manipulasi manipulasi'><span class='mesin_ubah' onclick="switchmenu('mesin','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='mesin_manipulasi manipulasi'><input type='checkbox' class='mesin_manipulasi_checkbox' value='h' name='mesin_hapus' id='mesin_hapus'></td>
                        <td class='mesin_manipulasi manipulasi'><span class='mesin_hapus' onclick="switchmenu('mesin','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.jamkerja') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('jamkerja','lihat')" value='l' name='jamkerja_lihat' id='jamkerja_lihat'></td>
                        <td><span class='jamkerja_lihat' onclick="switchmenu('jamkerja','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='jamkerja_manipulasi manipulasi'><input type='checkbox' class='jamkerja_manipulasi_checkbox' value='t' name='jamkerja_tambah' id='jamkerja_tambah'></td>
                        <td class='jamkerja_manipulasi manipulasi'><span class='jamkerja_tambah' onclick="switchmenu('jamkerja','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='jamkerja_manipulasi manipulasi'><input type='checkbox' class='jamkerja_manipulasi_checkbox' value='u' name='jamkerja_ubah' id='jamkerja_ubah'></td>
                        <td class='jamkerja_manipulasi manipulasi'><span class='jamkerja_ubah' onclick="switchmenu('jamkerja','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='jamkerja_manipulasi manipulasi'><input type='checkbox' class='jamkerja_manipulasi_checkbox' value='h' name='jamkerja_hapus' id='jamkerja_hapus'></td>
                        <td class='jamkerja_manipulasi manipulasi'><span class='jamkerja_hapus' onclick="switchmenu('jamkerja','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.harilibur') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('harilibur','lihat')" value='l' name='harilibur_lihat' id='harilibur_lihat'></td>
                        <td><span class='harilibur_lihat' onclick="switchmenu('harilibur','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='harilibur_manipulasi manipulasi'><input type='checkbox' class='harilibur_manipulasi_checkbox' value='t' name='harilibur_tambah' id='harilibur_tambah'></td>
                        <td class='harilibur_manipulasi manipulasi'><span class='harilibur_tambah' onclick="switchmenu('harilibur','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='harilibur_manipulasi manipulasi'><input type='checkbox' class='harilibur_manipulasi_checkbox' value='u' name='harilibur_ubah' id='harilibur_ubah'></td>
                        <td class='harilibur_manipulasi manipulasi'><span class='harilibur_ubah' onclick="switchmenu('harilibur','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='harilibur_manipulasi manipulasi'><input type='checkbox' class='harilibur_manipulasi_checkbox' value='h' name='harilibur_hapus' id='harilibur_hapus'></td>
                        <td class='harilibur_manipulasi manipulasi'><span class='harilibur_hapus' onclick="switchmenu('harilibur','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.ijintidakmasuk') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('ijintidakmasuk','lihat')" value='l' name='ijintidakmasuk_lihat' id='ijintidakmasuk_lihat'></td>
                        <td><span class='ijintidakmasuk_lihat' onclick="switchmenu('ijintidakmasuk','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='ijintidakmasuk_manipulasi manipulasi'><input type='checkbox' class='ijintidakmasuk_manipulasi_checkbox' value='t' name='ijintidakmasuk_tambah' id='ijintidakmasuk_tambah'></td>
                        <td class='ijintidakmasuk_manipulasi manipulasi'><span class='ijintidakmasuk_tambah' onclick="switchmenu('ijintidakmasuk','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='ijintidakmasuk_manipulasi manipulasi'><input type='checkbox' class='ijintidakmasuk_manipulasi_checkbox' value='u' name='ijintidakmasuk_ubah' id='ijintidakmasuk_ubah'></td>
                        <td class='ijintidakmasuk_manipulasi manipulasi'><span class='ijintidakmasuk_ubah' onclick="switchmenu('ijintidakmasuk','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='ijintidakmasuk_manipulasi manipulasi'><input type='checkbox' class='ijintidakmasuk_manipulasi_checkbox' value='h' name='ijintidakmasuk_hapus' id='ijintidakmasuk_hapus'></td>
                        <td class='ijintidakmasuk_manipulasi manipulasi'><span class='ijintidakmasuk_hapus' onclick="switchmenu('ijintidakmasuk','hapus')">{{ trans('all.hapus') }}</span></td>
                        <td class='ijintidakmasuk_manipulasi manipulasi'><input type='checkbox' class='ijintidakmasuk_manipulasi_checkbox' value='k' name='ijintidakmasuk_konfirmasi' id='ijintidakmasuk_konfirmasi'></td>
                        <td class='ijintidakmasuk_manipulasi manipulasi'><span class='ijintidakmasuk_konfirmasi' onclick="switchmenu('ijintidakmasuk','konfirmasi')">{{ trans('all.konfirmasi') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.cuti') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('cuti','lihat')" value='l' name='cuti_lihat' id='cuti_lihat'></td>
                        <td><span class='cuti_lihat' onclick="switchmenu('cuti','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='cuti_manipulasi manipulasi'><input type='checkbox' class='cuti_manipulasi_checkbox' value='u' name='cuti_ubah' id='cuti_ubah'></td>
                        <td class='cuti_manipulasi manipulasi'><span class='cuti_ubah' onclick="switchmenu('cuti','ubah')">{{ trans('all.ubah') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.logabsen') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('logabsen','lihat')" value='l' name='logabsen_lihat' id='logabsen_lihat'></td>
                        <td><span class='logabsen_lihat' onclick="switchmenu('logabsen','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='logabsen_manipulasi manipulasi'><input type='checkbox' class='logabsen_manipulasi_checkbox' value='t' name='logabsen_tambah' id='logabsen_tambah'></td>
                        <td class='logabsen_manipulasi manipulasi'><span class='logabsen_tambah' onclick="switchmenu('logabsen','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='logabsen_manipulasi manipulasi'><input type='checkbox' class='logabsen_manipulasi_checkbox' value='u' name='logabsen_ubah' id='logabsen_ubah'></td>
                        <td class='logabsen_manipulasi manipulasi'><span class='logabsen_ubah' onclick="switchmenu('logabsen','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='logabsen_manipulasi manipulasi'><input type='checkbox' class='logabsen_manipulasi_checkbox' value='h' name='logabsen_hapus' id='logabsen_hapus'></td>
                        <td class='logabsen_manipulasi manipulasi'><span class='logabsen_hapus' onclick="switchmenu('logabsen','hapus')">{{ trans('all.hapus') }}</span></td>
                        <td class='logabsen_manipulasi manipulasi'><input type='checkbox' class='logabsen_manipulasi_checkbox' value='k' name='logabsen_konfirmasi' id='logabsen_konfirmasi'></td>
                        <td class='logabsen_manipulasi manipulasi'><span class='logabsen_konfirmasi' onclick="switchmenu('logabsen','konfirmasi')">{{ trans('all.konfirmasi') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.fingerprintconnector') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('fingerprintconnector','lihat')" value='l' name='fingerprintconnector_lihat' id='fingerprintconnector_lihat'></td>
                        <td><span class='fingerprintconnector_lihat' onclick="switchmenu('fingerprintconnector','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='fingerprintconnector_manipulasi manipulasi'><input type='checkbox' class='fingerprintconnector_manipulasi_checkbox' value='t' name='fingerprintconnector_tambah' id='fingerprintconnector_tambah'></td>
                        <td class='fingerprintconnector_manipulasi manipulasi'><span class='fingerprintconnector_tambah' onclick="switchmenu('fingerprintconnector','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='fingerprintconnector_manipulasi manipulasi'><input type='checkbox' class='fingerprintconnector_manipulasi_checkbox' value='u' name='fingerprintconnector_ubah' id='fingerprintconnector_ubah'></td>
                        <td class='fingerprintconnector_manipulasi manipulasi'><span class='fingerprintconnector_ubah' onclick="switchmenu('fingerprintconnector','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='fingerprintconnector_manipulasi manipulasi'><input type='checkbox' class='fingerprintconnector_manipulasi_checkbox' value='h' name='fingerprintconnector_hapus' id='fingerprintconnector_hapus'></td>
                        <td class='fingerprintconnector_manipulasi manipulasi'><span class='fingerprintconnector_hapus' onclick="switchmenu('fingerprintconnector','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.jadwalshift') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('jadwalshift','lihat')" value='l' name='jadwalshift_lihat' id='jadwalshift_lihat'></td>
                        <td><span class='jadwalshift_lihat' onclick="switchmenu('jadwalshift','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='jadwalshift_manipulasi manipulasi'><input type='checkbox' class='jadwalshift_manipulasi_checkbox' value='u' name='jadwalshift_ubah' id='jadwalshift_ubah'></td>
                        <td class='jadwalshift_manipulasi manipulasi'><span class='jadwalshift_ubah' onclick="switchmenu('jadwalshift','ubah')">{{ trans('all.ubah') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.konfirmasi_flag') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('konfirmasi_flag','lihat')" value='l' name='konfirmasi_flag_lihat' id='konfirmasi_flag_lihat'></td>
                        <td><span class='konfirmasi_flag_lihat' onclick="switchmenu('konfirmasi_flag','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='konfirmasi_flag_manipulasi manipulasi'><input type='checkbox' class='konfirmasi_flag_manipulasi_checkbox' value='u' name='konfirmasi_flag_ubah' id='konfirmasi_flag_ubah'></td>
                        <td class='konfirmasi_flag_manipulasi manipulasi'><span class='konfirmasi_flag_ubah' onclick="switchmenu('konfirmasi_flag','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='konfirmasi_flag_manipulasi manipulasi'><input type='checkbox' class='konfirmasi_flag_manipulasi_checkbox' value='k' name='konfirmasi_flag_konfirmasi' id='konfirmasi_flag_konfirmasi'></td>
                        <td class='konfirmasi_flag_manipulasi manipulasi'><span class='konfirmasi_flag_konfirmasi' onclick="switchmenu('konfirmasi_flag','konfirmasi')">{{ trans('all.konfirmasi') }}</span></td>
                      </tr>
                      @if(Session::get('perbolehkanpayroll_perusahaan') == 'y')
                        <tr>
                          <td style='padding-left:20px;'>{{ trans('all.payroll') }}</td>
                        </tr>
                        <tr>
                            <td style='padding-left:40px;'>{{ trans('all.pengaturan') }}</td>
                            <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('payrollpengaturan','lihat')" value='l' name='payrollpengaturan_lihat' id='payrollpengaturan_lihat'></td>
                            <td><span class='payrollpengaturan_lihat' onclick="switchmenu('payrollpengaturan','lihat')">{{ trans('all.lihat') }}</span></td>
                            <td class='payrollpengaturan_manipulasi manipulasi'><input type='checkbox' class='payrollpengaturan_manipulasi_checkbox' value='u' name='payrollpengaturan_ubah' id='payrollpengaturan_ubah'></td>
                            <td class='payrollpengaturan_manipulasi manipulasi'><span class='payrollpengaturan_ubah' onclick="switchmenu('payrollpengaturan','ubah')">{{ trans('all.ubah') }}</span></td>
                          </tr>
                        <tr>
                            <td style='padding-left:40px;'>{{ trans('all.komponenmaster') }}</td>
                            <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('payrollkomponenmaster','lihat')" value='l' name='payrollkomponenmaster_lihat' id='payrollkomponenmaster_lihat'></td>
                            <td><span class='payrollkomponenmaster_lihat' onclick="switchmenu('payrollkomponenmaster','lihat')">{{ trans('all.lihat') }}</span></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><input type='checkbox' class='payrollkomponenmaster_manipulasi_checkbox' value='t' name='payrollkomponenmaster_tambah' id='payrollkomponenmaster_tambah'></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><span class='payrollkomponenmaster_tambah' onclick="switchmenu('payrollkomponenmaster','tambah')">{{ trans('all.tambah') }}</span></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><input type='checkbox' class='payrollkomponenmaster_manipulasi_checkbox' value='u' name='payrollkomponenmaster_ubah' id='payrollkomponenmaster_ubah'></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><span class='payrollkomponenmaster_ubah' onclick="switchmenu('payrollkomponenmaster','ubah')">{{ trans('all.ubah') }}</span></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><input type='checkbox' class='payrollkomponenmaster_manipulasi_checkbox' value='h' name='payrollkomponenmaster_hapus' id='payrollkomponenmaster_hapus'></td>
                            <td class='payrollkomponenmaster_manipulasi manipulasi'><span class='payrollkomponenmaster_hapus' onclick="switchmenu('payrollkomponenmaster','hapus')">{{ trans('all.hapus') }}</span></td>
                        </tr>
                        <tr>
                            <td style='padding-left:40px;'>{{ trans('all.komponeninputmanual') }}</td>
                            <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('payrollkomponeninputmanual','lihat')" value='l' name='payrollkomponeninputmanual_lihat' id='payrollkomponeninputmanual_lihat'></td>
                            <td><span class='payrollkomponeninputmanual_lihat' onclick="switchmenu('payrollkomponeninputmanual','lihat')">{{ trans('all.lihat') }}</span></td>
                            <td class='payrollkomponeninputmanual_manipulasi manipulasi'><input type='checkbox' class='payrollkomponeninputmanual_manipulasi_checkbox' value='u' name='payrollkomponeninputmanual_ubah' id='payrollkomponeninputmanual_ubah'></td>
                            <td class='payrollkomponeninputmanual_manipulasi manipulasi'><span class='payrollkomponeninputmanual_ubah' onclick="switchmenu('payrollkomponeninputmanual','ubah')">{{ trans('all.ubah') }}</span></td>
                        </tr>
                      @endif
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.lainlain') }}</td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.slideshow') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('slideshow','lihat')" value='l' name='slideshow_lihat' id='slideshow_lihat'></td>
                        <td><span class='slideshow_lihat' onclick="switchmenu('slideshow','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='slideshow_manipulasi manipulasi'><input type='checkbox' class='slideshow_manipulasi_checkbox' value='t' name='slideshow_tambah' id='slideshow_tambah'></td>
                        <td class='slideshow_manipulasi manipulasi'><span class='slideshow_tambah' onclick="switchmenu('slideshow','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='slideshow_manipulasi manipulasi'><input type='checkbox' class='slideshow_manipulasi_checkbox' value='u' name='slideshow_ubah' id='slideshow_ubah'></td>
                        <td class='slideshow_manipulasi manipulasi'><span class='slideshow_ubah' onclick="switchmenu('slideshow','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='slideshow_manipulasi manipulasi'><input type='checkbox' class='slideshow_manipulasi_checkbox' value='h' name='slideshow_hapus' id='slideshow_hapus'></td>
                        <td class='slideshow_manipulasi manipulasi'><span class='slideshow_hapus' onclick="switchmenu('slideshow','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.batasan') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('batasan','lihat')" value='l' name='batasan_lihat' id='batasan_lihat'></td>
                        <td><span class='batasan_lihat' onclick="switchmenu('batasan','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='batasan_manipulasi manipulasi'><input type='checkbox' class='batasan_manipulasi_checkbox' value='t' name='batasan_tambah' id='batasan_tambah'></td>
                        <td class='batasan_manipulasi manipulasi'><span class='batasan_tambah' onclick="switchmenu('batasan','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='batasan_manipulasi manipulasi'><input type='checkbox' class='batasan_manipulasi_checkbox' value='u' name='batasan_ubah' id='batasan_ubah'></td>
                        <td class='batasan_manipulasi manipulasi'><span class='batasan_ubah' onclick="switchmenu('batasan','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='batasan_manipulasi manipulasi'><input type='checkbox' class='batasan_manipulasi_checkbox' value='h' name='batasan_hapus' id='batasan_hapus'></td>
                        <td class='batasan_manipulasi manipulasi'><span class='batasan_hapus' onclick="switchmenu('batasan','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.hakakses') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('hakakses','lihat')" value='l' name='hakakses_lihat' id='hakakses_lihat'></td>
                        <td><span class='hakakses_lihat' onclick="switchmenu('hakakses','lihat')">{{ trans('all.lihat') }}</span></td>
                        <td class='hakakses_manipulasi manipulasi'><input type='checkbox' class='hakakses_manipulasi_checkbox' value='t' name='hakakses_tambah' id='hakakses_tambah'></td>
                        <td class='hakakses_manipulasi manipulasi'><span class='hakakses_tambah' onclick="switchmenu('hakakses','tambah')">{{ trans('all.tambah') }}</span></td>
                        <td class='hakakses_manipulasi manipulasi'><input type='checkbox' class='hakakses_manipulasi_checkbox' value='u' name='hakakses_ubah' id='hakakses_ubah'></td>
                        <td class='hakakses_manipulasi manipulasi'><span class='hakakses_ubah' onclick="switchmenu('hakakses','ubah')">{{ trans('all.ubah') }}</span></td>
                        <td class='hakakses_manipulasi manipulasi'><input type='checkbox' class='hakakses_manipulasi_checkbox' value='h' name='hakakses_hapus' id='hakakses_hapus'></td>
                        <td class='hakakses_manipulasi manipulasi'><span class='hakakses_hapus' onclick="switchmenu('hakakses','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.setulangkatasandipegawai') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('setulangkatasandipegawai','lihat')" value='l' name='setulangkatasandipegawai_lihat' id='setulangkatasandipegawai_lihat'></td>
                        <td><span class='setulangkatasandipegawai_lihat' onclick="switchmenu('setulangkatasandipegawai','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.postingdata') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('postingdata','ijinkan')" value='i' name='postingdata_ijinkan' id='postingdata_ijinkan'></td>
                        <td><span class='postingdata_ijinkan' onclick="switchmenu('postingdata','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.hapusdata') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('hapusdata','ijinkan')" value='i' name='hapusdata_ijinkan' id='hapusdata_ijinkan'></td>
                        <td><span class='hapusdata_ijinkan' onclick="switchmenu('hapusdata','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:40px;'>{{ trans('all.supervisi') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('supervisi','ijinkan')" value='i' name='supervisi_ijinkan' id='supervisi_ijinkan'></td>
                        <td><span class='supervisi_ijinkan' onclick="switchmenu('supervisi','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:0px;'>{{ trans('all.pengaturan') }}</td>
                        <td style="padding-left:0;"><input type='checkbox' value='u' name='pengaturan_ubah' id='pengaturan_ubah'></td>
                        <td><span class='pengaturan_ubah' onclick="switchmenu('pengaturan','ubah')">{{ trans('all.ubah') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:0;'>{{ trans('all.laporan') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporan','lihat')" value='l' name='laporan_lihat' id='laporan_lihat'></td>
                        <td><span class='laporan_lihat' onclick="switchmenu('laporan','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.perpegawai') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanperpegawai','lihat')" value='l' name='laporanperpegawai_lihat' id='laporanperpegawai_lihat'></td>
                        <td><span class='laporanperpegawai_lihat' onclick="switchmenu('laporanperpegawai','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.logabsen') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanlogabsen','lihat')" value='l' name='laporanlogabsen_lihat' id='laporanlogabsen_lihat'></td>
                        <td><span class='laporanlogabsen_lihat' onclick="switchmenu('laporanlogabsen','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.kehadiran') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporankehadiran','lihat')" value='l' name='laporankehadiran_lihat' id='laporankehadiran_lihat'></td>
                        <td><span class='laporankehadiran_lihat' onclick="switchmenu('laporankehadiran','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.rekapshift') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanrekapparuhawktu','lihat')" value='l' name='laporanrekapparuhwaktu_lihat' id='laporanrekapparuhwaktu_lihat'></td>
                        <td><span class='laporanrekapparuhwaktu_lihat' onclick="switchmenu('laporanrekapparuhwaktu','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.pertanggal') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanpertanggal','lihat')" value='l' name='laporanpertanggal_lihat' id='laporanpertanggal_lihat'></td>
                        <td><span class='laporanpertanggal_lihat' onclick="switchmenu('laporanpertanggal','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.ekspor') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanekspor','lihat')" value='l' name='laporanekspor_lihat' id='laporanekspor_lihat'></td>
                        <td><span class='laporanekspor_lihat' onclick="switchmenu('laporanekspor','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.logtrackerpegawai') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanlogtrackerpegawai','lihat')" value='l' name='laporanlogtrackerpegawai_lihat' id='laporanlogtrackerpegawai_lihat'></td>
                        <td><span class='laporanlogtrackerpegawai_lihat' onclick="switchmenu('laporanlogtrackerpegawai','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.lainnya') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanlainnya','lihat')" value='l' name='laporanlainnya_lihat' id='laporanlainnya_lihat'></td>
                        <td><span class='laporanlainnya_lihat' onclick="switchmenu('laporanlainnya','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.perlokasi') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanperlokasi','lihat')" value='l' name='laporanperlokasi_lihat' id='laporanperlokasi_lihat'></td>
                        <td><span class='laporanperlokasi_lihat' onclick="switchmenu('laporanperlokasi','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.pekerjaanuser') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporanpekerjaanuser','lihat')" value='l' name='laporanpekerjaanuser_lihat' id='laporanpekerjaanuser_lihat'></td>
                        <td><span class='laporanpekerjaanuser_lihat' onclick="switchmenu('laporanpekerjaanuser','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                          <td style='padding-left:20px;'>{{ trans('all.laporancustom') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('laporancustom','lihat')" value='l' name='laporancustom_lihat' id='laporancustom_lihat'></td>
                          <td><span class='laporancustom_lihat' onclick="switchmenu('laporancustom','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='laporancustom_manipulasi manipulasi'><input type='checkbox' class='laporancustom_manipulasi_checkbox' value='t' name='laporancustom_tambah' id='laporancustom_tambah'></td>
                          <td class='laporancustom_manipulasi manipulasi'><span class='laporancustom_tambah' onclick="switchmenu('laporancustom','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='laporancustom_manipulasi manipulasi'><input type='checkbox' class='laporancustom_manipulasi_checkbox' value='u' name='laporancustom_ubah' id='laporancustom_ubah'></td>
                          <td class='laporancustom_manipulasi manipulasi'><span class='laporancustom_ubah' onclick="switchmenu('laporancustom','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='laporancustom_manipulasi manipulasi'><input type='checkbox' class='laporancustom_manipulasi_checkbox' value='h' name='laporancustom_hapus' id='laporancustom_hapus'></td>
                          <td class='laporancustom_manipulasi manipulasi'><span class='laporancustom_hapus' onclick="switchmenu('laporancustom','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                          <td style='padding-left:20px;'>{{ trans('all.customdashboard') }}</td>
                          <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('customdashboard','lihat')" value='l' name='customdashboard_lihat' id='customdashboard_lihat'></td>
                          <td><span class='customdashboard_lihat' onclick="switchmenu('customdashboard','lihat')">{{ trans('all.lihat') }}</span></td>
                          <td class='customdashboard_manipulasi manipulasi'><input type='checkbox' class='customdashboard_manipulasi_checkbox' value='t' name='customdashboard_tambah' id='customdashboard_tambah'></td>
                          <td class='customdashboard_manipulasi manipulasi'><span class='customdashboard_tambah' onclick="switchmenu('customdashboard','tambah')">{{ trans('all.tambah') }}</span></td>
                          <td class='customdashboard_manipulasi manipulasi'><input type='checkbox' class='customdashboard_manipulasi_checkbox' value='u' name='customdashboard_ubah' id='customdashboard_ubah'></td>
                          <td class='customdashboard_manipulasi manipulasi'><span class='customdashboard_ubah' onclick="switchmenu('customdashboard','ubah')">{{ trans('all.ubah') }}</span></td>
                          <td class='customdashboard_manipulasi manipulasi'><input type='checkbox' class='customdashboard_manipulasi_checkbox' value='h' name='customdashboard_hapus' id='customdashboard_hapus'></td>
                          <td class='customdashboard_manipulasi manipulasi'><span class='customdashboard_hapus' onclick="switchmenu('customdashboard','hapus')">{{ trans('all.hapus') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:0;'>{{ trans('all.riwayat') }}</td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.pengguna') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('riwayatpengguna','lihat')" value='l' name='riwayatpengguna_lihat' id='riwayatpengguna_lihat'></td>
                        <td><span class='riwayatpengguna_lihat' onclick="switchmenu('riwayatpengguna','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.pegawai') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('riwayatpegawai','lihat')" value='l' name='riwayatpegawai_lihat' id='riwayatpegawai_lihat'></td>
                        <td><span class='riwayatpegawai_lihat' onclick="switchmenu('riwayatpegawai','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.sms') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('riwayatsms','lihat')" value='l' name='riwayatsms_lihat' id='riwayatsms_lihat'></td>
                        <td><span class='riwayatsms_lihat' onclick="switchmenu('riwayatsms','lihat')">{{ trans('all.lihat') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:0;'>{{ trans('all.notifikasi') }}</td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.notifikasiijintidakmasuk') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('notifikasiijintidakmasuk','ijinkan')" value='i' name='notifikasiijintidakmasuk_ijinkan' id='notifikasiijintidakmasuk_ijinkan'></td>
                        <td><span class='notifikasiijintidakmasuk_ijinkan' onclick="switchmenu('notifikasiijintidakmasuk','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.notifikasiriwayatabsen') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('notifikasiriwayatabsen','ijinkan')" value='i' name='notifikasiriwayatabsen_ijinkan' id='notifikasiriwayatabsen_ijinkan'></td>
                        <td><span class='notifikasiriwayatabsen_ijinkan' onclick="switchmenu('notifikasiriwayatabsen','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.notifikasiterlambat') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('notifikasiterlambat','ijinkan')" value='i' name='notifikasiterlambat_ijinkan' id='notifikasiterlambat_ijinkan'></td>
                        <td><span class='notifikasiterlambat_ijinkan' onclick="switchmenu('notifikasiterlambat','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.notifikasipulangawal') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('notifikasipulangawal','ijinkan')" value='i' name='notifikasipulangawal_ijinkan' id='notifikasipulangawal_ijinkan'></td>
                        <td><span class='notifikasipulangawal_ijinkan' onclick="switchmenu('notifikasipulangawal','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                      </tr>
                      <tr>
                        <td style='padding-left:20px;'>{{ trans('all.notifikasilembur') }}</td>
                        <td style='padding-left:0px;'><input type='checkbox' onclick="switchmenucheckbox('notifikasilembur','ijinkan')" value='i' name='notifikasilembur_ijinkan' id='notifikasilembur_ijinkan'></td>
                        <td><span class='notifikasilembur_ijinkan' onclick="switchmenu('notifikasilembur','ijinkan')">{{ trans('all.ijinkan') }}</span></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                    <td colspan=2>
                      <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                        <button type="button" id="kembali" onclick="return ke('../hakakses')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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