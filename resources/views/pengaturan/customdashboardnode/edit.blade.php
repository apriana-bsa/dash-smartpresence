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
		pilihJenis();
		pilihWaktuTampil();
		pilihQueryData('kehadiran');

        $('#faicon').on('change', function(e) {
            //console.log(e.icon);
            $('#faicon').attr("data-icon", e.icon);
            $('#icon').val(e.icon);
        });

        //$('select[name="pilihwarna"]').simplecolorpicker({picker: true, theme: 'glyphicons'});
        $('select[name="pilihwarna"]').simplecolorpicker({
            picker: true,
            theme: 'glyphicons'
        }).on('change', function() {
            $('#warna').val($('#pilihwarna').next().attr('title'));
            //console.log($('#pilihwarna').next().attr('class'));
        });
        $('select[name="pilihwarna"]').simplecolorpicker('selectColor', '{{ $warna }}');
	});

	function validasi(){
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');
		
		var nama = $("#nama").val();
		var judul = $("#judul").val();
		var icon = $("#icon").val();
		var warna = $("#warna").val();

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

        if(warna == ""){
            alertWarning("{{ trans('all.warnakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#warna'));
                });
            return false;
        }
	}

	var i = 999;
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
										"<button type=button onclick='deleteWaktu("+i+")' title='{{ trans('all.hapus') }}' class='btn btn-danger glyphicon glyphicon-remove row-remove'></button>" +
									"</td>" +
								"</tr>");
		$('.jam').inputmask( 'h:s' );
		document.getElementById('waktumulai_'+i).focus();
	}

	function deleteWaktu(i){
		$("#addr_waktu"+i).remove();
		i--;
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

    //hanya milik query_kehadiran
    function pilihQueryData(param){
        var pilihan = $('#query_'+param).val();
        var data = '';
        if(pilihan == 'semua'){
            data = '<option value="sudahabsen" @if($data->query_kehadiran_data == 'sudahabsen') selected @endif>{{ trans('all.sudahabsen') }}</option>' +
                '<option value="belumabsen" @if($data->query_kehadiran_data == 'belumabsen') selected @endif>{{ trans('all.belumabsen') }}</option>' +
                '<option value="adadikantor" @if($data->query_kehadiran_data == 'adadikantor') selected @endif>{{ trans('all.adadikantor') }}</option>' +
                '<option value="ijintidakmasuk" @if($data->query_kehadiran_data == 'ijintidakmasuk') selected @endif>{{ trans('all.ijintidakmasuk') }}</option>' +
                '<option value="terlambat" @if($data->query_kehadiran_data == 'terlambat') selected @endif>{{ trans('all.terlambat') }}</option>' +
                '<option value="pulangawal" @if($data->query_kehadiran_data == 'pulangawal') selected @endif>{{ trans('all.pulangawal') }}</option>' +
                '<option value="lamalembur" @if($data->query_kehadiran_data == 'lamalembur') selected @endif>{{ trans('all.lamalembur') }}</option>' +
                '<option value="lamakerja" @if($data->query_kehadiran_data == 'lamakerja') selected @endif>{{ trans('all.lamakerja') }}</option>' +
                '<option value="masuknormal" @if($data->query_kehadiran_data == 'masuknormal') selected @endif>{{ trans('all.masuknormal') }}</option>' +
                '<option value="pulangnormal" @if($data->query_kehadiran_data == 'pulangnormal') selected @endif>{{ trans('all.pulangnormal') }}</option>';
        }else if(pilihan == 'full'){
            data = '<option value="sudahabsen" @if($data->query_kehadiran_data == 'sudahabsen') selected @endif>{{ trans('all.sudahabsen') }}</option>' +
                '<option value="belumabsen" @if($data->query_kehadiran_data == 'belumabsen') selected @endif>{{ trans('all.belumabsen') }}</option>' +
                '<option value="terlambat" @if($data->query_kehadiran_data == 'terlambat') selected @endif>{{ trans('all.terlambat') }}</option>' +
                '<option value="pulangawal" @if($data->query_kehadiran_data == 'pulangawal') selected @endif>{{ trans('all.pulangawal') }}</option>' +
                '<option value="lamalembur" @if($data->query_kehadiran_data == 'lamalembur') selected @endif>{{ trans('all.lamalembur') }}</option>' +
                '<option value="lamakerja" @if($data->query_kehadiran_data == 'lamakerja') selected @endif>{{ trans('all.lamakerja') }}</option>' +
                '<option value="masuknormal" @if($data->query_kehadiran_data == 'masuknormal') selected @endif>{{ trans('all.masuknormal') }}</option>' +
                '<option value="pulangnormal" @if($data->query_kehadiran_data == 'pulangnormal') selected @endif>{{ trans('all.pulangnormal') }}</option>';
        }else if(pilihan == 'shift'){
            data = '<option value="sudahabsen" @if($data->query_kehadiran_data == 'sudahabsen') selected @endif>{{ trans('all.sudahabsen') }}</option>' +
                '<option value="belumabsen" @if($data->query_kehadiran_data == 'belumabsen') selected @endif>{{ trans('all.belumabsen') }}</option>' +
                '<option value="terlambat" @if($data->query_kehadiran_data == 'terlambat') selected @endif>{{ trans('all.terlambat') }}</option>' +
                '<option value="pulangawal" @if($data->query_kehadiran_data == 'pulangawal') selected @endif>{{ trans('all.pulangawal') }}</option>' +
                '<option value="lamalembur" @if($data->query_kehadiran_data == 'lamalembur') selected @endif>{{ trans('all.lamalembur') }}</option>' +
                '<option value="lamakerja @if($data->query_kehadiran_data == 'lamakerja') selected @endif">{{ trans('all.lamakerja') }}</option>' +
                '<option value="masuknormal" @if($data->query_kehadiran_data == 'masuknormal') selected @endif>{{ trans('all.masuknormal') }}</option>' +
                '<option value="pulangnormal" @if($data->query_kehadiran_data == 'pulangnormal') selected @endif>{{ trans('all.pulangnormal') }}</option>';
        }
        $('#query_'+param+'_data').html(data)
    }

    function cari_id(jenis){
        callModalGeneral('customdashboardnode','{{ url('cariid') }}/'+jenis);
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.customdashboard') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.pengaturan') }}</li>
        <li>{{ trans('all.node') }}</li>
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
          	<form action="../{{ $data->id }}" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<input type="hidden" name="_method" value="put">
				<table width="100%">
					<tr>
						<td width="200px">{{ trans('all.nama') }}</td>
						<td style="float:left">
							<input type="text" class="form-control" autofocus autocomplete="off" value="{{ $data->nama }}" name="nama" id="nama" maxlength="100">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.judul') }}</td>
						<td style="float:left">
							<input type="text" class="form-control" autocomplete="off" name="judul" value="{{ $data->judul }}" id="judul" maxlength="100">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.icon') }}</td>
						<td>
							<button type="button" class="btn btn-default" id='faicon' data-iconset="fontawesome" data-placement="right" data-icon="{{ $data->icon }}" role="iconpicker"></button>
							<input type="hidden" class="form-control" autocomplete="off" name="icon" value="{{ $data->icon }}" id="icon" maxlength="100">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.warna') }}</td>
						<td style="float:left">
							{{--<input type="text" class="form-control color" autocomplete="off" name="warna" value="{{ $data->warna }}" id="warna" maxlength="10">--}}
							<input type="hidden" class="form-control" autocomplete="off" value="{{ $data->warna }}" name="warna" id="warna" maxlength="10">
							<select name="pilihwarna" id="pilihwarna" class="form-control">
								{{--<option value="" @if($data->warna == '') selected @endif></option>--}}
								{{--<option value="merah" @if($data->warna == 'merah') selected @endif>{{ trans('all.merah') }}</option>--}}
								{{--<option value="kuning" @if($data->warna == 'kuning') selected @endif>{{ trans('all.kuning') }}</option>--}}
								{{--<option value="hijau" @if($data->warna == 'hijau') selected @endif>{{ trans('all.hijau') }}</option>--}}
								{{--<option value="biru" @if($data->warna == 'biru') selected @endif>{{ trans('all.biru') }}</option>--}}
								{{--<option value="ungu" @if($data->warna == 'ungu') selected @endif>{{ trans('all.ungu') }}</option>--}}
								{{--<option value="hitam" @if($data->warna == 'hitam') selected @endif>{{ trans('all.hitam') }}</option>--}}
								{{--<option value="putih" @if($data->warna == 'putih') selected @endif>{{ trans('all.putih') }}</option>--}}
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
								<option value="kehadiran" @if($data->query_jenis == 'kehadiran') selected @endif>{{ trans('all.kehadiran') }}</option>
								<option value="master" @if($data->query_jenis == 'master') selected @endif>{{ trans('all.master') }}</option>
							</select>
						</td>
					</tr>
					<tr class="kehadiran" style="display:none">
						<td>{{ trans('all.query_kehadiran') }}</td>
						<td style="float:left">
							<select name="query_kehadiran" id="query_kehadiran" class="form-control" onchange="pilihQueryData('kehadiran')">
								<option value="semua" @if($data->query_kehadiran == 'semua') selected @endif>{{ trans('all.semua') }}</option>
								<option value="full" @if($data->query_kehadiran == 'full') selected @endif>{{ trans('all.full') }}</option>
								<option value="shift" @if($data->query_kehadiran == 'shift') selected @endif>{{ trans('all.shift') }}</option>
							</select>
						</td>
					</tr>
					<tr class="kehadiran" style="display:none">
						<td>{{ trans('all.query_kehadiran_data') }}</td>
						<td style="float:left">
							<select name="query_kehadiran_data" id="query_kehadiran_data" class="form-control">
							</select>
						</td>
					</tr>
					<tr class="kehadiran" style="display:none">
						<td>{{ trans('all.query_kehadiran_if') }}</td>
						<td>
							<textarea type="text" style="resize:none" class="form-control" autofocus autocomplete="off" name="query_kehadiran_if" id="query_kehadiran_if">{{ $data->query_kehadiran_if }}</textarea>
							<p></p>
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
								<option value="" @if($data->query_kehadiran_group == '') selected @endif></option>
								<option value="agama" @if($data->query_kehadiran_group == 'agama') selected @endif>{{ trans('all.agama') }}</option>
								<option value="jamkerja" @if($data->query_kehadiran_group == 'jamkerja') selected @endif>{{ trans('all.jamkerja') }}</option>
								<option value="jamkerjajenis" @if($data->query_kehadiran_group == 'jamkerjajenis') selected @endif>{{ trans('all.jamkerjajenis') }}</option>
								<option value="jamkerjashift_jenis" @if($data->query_kehadiran_group == 'jamkerjashift_jenis') selected @endif>{{ trans('all.jamkerjashift_jenis') }}</option>
								<option value="jamkerjakategori" @if($data->query_kehadiran_group == 'jamkerjakategori') selected @endif>{{ trans('all.jamkerjakategori') }}</option>
{{--								<option value="lokasi" @if($data->query_kehadiran_group == 'lokasi') selected @endif>{{ trans('all.lokasi') }}</option>--}}
								<option value="alasantidakmasuk" @if($data->query_kehadiran_group == 'alasantidakmasuk') selected @endif>{{ trans('all.alasantidakmasuk') }}</option>
								<option value="alasantidakmasuk_kategori" @if($data->query_kehadiran_group == 'alasantidakmasuk_kategori') selected @endif>{{ trans('all.alasantidakmasuk_kategori') }}</option>
							</select>
						</td>
					</tr>
					<tr class="kehadiran" style="display:none">
						<td>{{ trans('all.query_kehadiran_periode') }}</td>
						<td style="float:left">
							<select name="query_kehadiran_periode" id="query_kehadiran_periode" class="form-control">
								<option value="" @if($data->query_kehadiran_periode == '') selected @endif></option>
								<option value="navigasi-tanggal" @if($data->query_kehadiran_periode == 'navigasi-tanggal') selected @endif>{{ trans('all.navigasi-tanggal') }}</option>
							</select>
						</td>
					</tr>
					<tr class="master" style="display:none">
						<td>{{ trans('all.query_master_data') }}</td>
						<td style="float:left">
							<select name="query_master_data" id="query_master_data" class="form-control">
								<option value="pegawai" @if($data->query_master_data == 'pegawai') selected @endif>{{ trans('all.pegawai') }}</option>
							</select>
						</td>
					</tr>
					<tr class="master" style="display:none">
						<td>{{ trans('all.query_master_if') }}</td>
						<td>
							<textarea type="text" style="resize:none" class="form-control" autofocus autocomplete="off" name="query_master_if" id="query_master_if">{{ $data->query_master_if }}</textarea>
							<p></p>
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
								<option value="" @if($data->query_master_group == '') selected @endif></option>
								<option value="agama" @if($data->query_master_group == 'agama') selected @endif>{{ trans('all.agama') }}</option>
								<option value="jamkerja" @if($data->query_master_group == 'jamkerja') selected @endif>{{ trans('all.jamkerja') }}</option>
								<option value="jamkerjajenis" @if($data->query_master_group == 'jamkerjajenis') selected @endif>{{ trans('all.jamkerjajenis') }}</option>
								<option value="jamkerjashift_jenis" @if($data->query_master_group == 'jamkerjashift_jenis') selected @endif>{{ trans('all.jamkerjashift_jenis') }}</option>
								<option value="jamkerjakategori" @if($data->query_master_group == 'jamkerjakategori') selected @endif>{{ trans('all.jamkerjakategori') }}</option>
								<option value="lokasi" @if($data->query_master_group == 'lokasi') selected @endif>{{ trans('all.lokasi') }}</option>
							</select>
						</td>
					</tr>
					<tr class="master" style="display:none">
						<td>{{ trans('all.query_master_periode') }}</td>
						<td style="float:left">
							<select name="query_master_periode" id="query_master_periode" class="form-control">
								<option value="" @if($data->query_master_periode == '') selected @endif></option>
								<option value="navigasi-tanggal" @if($data->query_master_periode == 'navigasi-tanggal') selected @endif>{{ trans('all.navigasi-tanggal') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.waktutampil') }}</td>
						<td style="float:left">
							<select name="waktutampil" class="form-control" id="waktutampil" onchange="pilihWaktuTampil()">
								<option value="y" @if($data->waktutampil == 'y') selected @endif>{{ trans('all.ya') }}</option>
								<option value="t" @if($data->waktutampil == 't') selected @endif>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr class="waktutampil" style="display:none">
						<td>{{ trans('all.waktutampil_awal') }}</td>
						<td style="float:left">
							<input type="text" class="form-control jam" placeholder="hh:mm" name="waktutampil_awal" id="waktutampil_awal" value="{{ $data->waktutampil_awal }}" size="6">
						</td>
					</tr>
					<tr class="waktutampil" style="display:none">
						<td>{{ trans('all.waktutampil_akhir') }}</td>
						<td style="float:left">
							<input type="text" class="form-control jam" placeholder="hh:mm" name="waktutampil_akhir" id="waktutampil_akhir" value="{{ $data->waktutampil_akhir }}" size="6">
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../../customdashboardnode')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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