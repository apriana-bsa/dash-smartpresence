@extends('layouts.master')
@section('title', trans('all.menu_mesin'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	
    span{
        cursor:default;
    }

	#map {
      height: 500px;
    }
	</style>
	<script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>
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

	var firstRun = true;
	var markers = '';
	var map;
        
	function validasi(){
		$('#submit').attr( 'data-loading', '' );
		$('#submit').attr('disabled', 'disabled');
		$('#kembali').attr('disabled', 'disabled');
		
		var nama = $("#nama").val();
		var cekjamserver = $("#cekjamserver").val();
		var utcdefault = $("#utcdefault").val();
		var utc = $("#utc").val();
		var ijinkanpendaftaran = $("#ijinkanpendaftaran").val();
		var jenis = $("#jenis").val();

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

		if(cekjamserver == ""){
			alertWarning("{{ trans('all.cekjamserverkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#cekjamserver'));
            });
      return false;
		}

		if(utcdefault == ""){
			alertWarning("{{ trans('all.utcdefaultkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#utcdefault'));
            });
      return false;
		}

		if(utcdefault == "t"){
			if(utc == ""){
				alertWarning("{{ trans('all.utckosong') }}",
	            function() {
	              aktifkanTombol();
	              setFocus($('#utc'));
	            });
	      return false;
			}
		}

		if(jenis == 'smartphone') {
			var fixgps_gunakan = $('#fixgps_gunakan').val();
			var fixgps_latitude = $('#fixgps_latitude').val();
			var fixgps_longitude = $('#fixgps_longitude').val();
			if(fixgps_gunakan == 'y'){
				if(fixgps_latitude == ''){
					alertWarning("{{ trans('all.latkosong') }}",
						function() {
							aktifkanTombol();
							setFocus($('#fixgps_latitude'));
						});
					return false;
				}
				if(fixgps_longitude == ''){
					alertWarning("{{ trans('all.lonkosong') }}",
						function() {
							aktifkanTombol();
							setFocus($('#fixgps_longitude'));
						});
					return false;
				}
			}

            if (ijinkanpendaftaran == "") {
                alertWarning("{{ trans('all.ijinkanpendaftarankosong') }}",
                    function () {
                        aktifkanTombol();
                        setFocus($('#ijinkanpendaftaran'));
                    });
                return false;
            }
        }

		if(jenis == ""){
			alertWarning("{{ trans('all.jeniskosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#jenis'));
            });
      		return false;
		}

		if(jenis == 'fingerprint'){
		    var comkey = $('#fp_comkey').val();
		    var ip = $('#fp_ip').val();
		    var soapport = $('#fp_soapport').val();
		    var udpport = $('#fp_udpport').val();
		    var idfingerprintconnector = $('#fp_idfingerprintconnector').val();
		    var serialnumber = $('#fp_serialnumber').val();
		    var algoritma = $('#fp_algoritma').val();
		    var intervaltarik = $('#fp_intervaltarik').val();
		    var ijinkanadmin = $('#fp_ijinkanadmin').val();
		    var lat = $('#fp_lat').val();
		    var lon = $('#fp_lon').val();
		    var status = $('#fp_status').val();

            if(comkey == ""){
                alertWarning("{{ trans('all.comkeykosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#kosong'));
                    });
                return false;
            }

            if(ip == ""){
                alertWarning("{{ trans('all.ipkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_ip'));
                    });
                return false;
            }

            if(soapport == ""){
                alertWarning("{{ trans('all.soapportkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_soapport'));
                    });
                return false;
            }
            
            if(udpport == ""){
                alertWarning("{{ trans('all.udpportkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_udport'));
                    });
                return false;
            }

            if(idfingerprintconnector == ""){
                alertWarning("{{ trans('all.fingerprintconnectorkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_fingerprintconnector'));
                    });
                return false;
            }

            if(serialnumber == ""){
                alertWarning("{{ trans('all.serialnumberkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_serialnumber'));
                    });
                return false;
            }
            
            if(algoritma == ""){
                alertWarning("{{ trans('all.algoritmakosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_algoritma'));
                    });
                return false;
            }

            if(intervaltarik == ""){
                alertWarning("{{ trans('all.intervaltarikkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_intervaltarik'));
                    });
                return false;
            }

            if(ijinkanadmin == ""){
                alertWarning("{{ trans('all.ijinkanadminkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_ijinkanadmin'));
                    });
                return false;
            }

            if(lat == ""){
                alertWarning("{{ trans('all.latkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_lat'));
                    });
                return false;
            }

            if(lon == ""){
                alertWarning("{{ trans('all.lonkosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_lon'));
                    });
                return false;
            }

            if(status == ""){
                alertWarning("{{ trans('all.statuskosong') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#fp_status'));
                    });
                return false;
            }
		}
	}

	function changeutc(){
		var utcdefault = $("#utcdefault").val();
		if(utcdefault == "y"){
			$("#label-utc").css("display", "none");
		}else if(utcdefault == "t"){
			$("#label-utc").css("display", "");
		}else{
			$("#label-utc").css("display", "none");
		}
	}

	$(function(){
		$("#tambahatribut").click(function(){
			
			var atribut = document.getElementsByClassName("atributpopup");
			
			$("#tabelatribut").html("");
			$("#atributarea").html("");
			
			var _parent = "";
			var nilai = "";
			var _flagparent = false;
			for(var i=0; i<atribut.length; i++) {
				if (atribut[i].id.substring(0,12)!="semuaatribut") {
					if (document.getElementById("atributpopup"+atribut[i].value).checked) {
						if (_flagparent == false) {
							_flagparent = true;
							$("#tabelatribut").append(_parent);
						}
						nilai = $("#attrpopup_atribut"+atribut[i].value).html();
						$("#atributarea").append("<input type='hidden' name='atribut[]' value='"+atribut[i].value+"'>");
					$("#tabelatribut").append("</tr><tr><td style='padding:2px;padding-left:10px'>"+nilai+"</td></tr>");
					}
				}
				else {
					var _flagparent = false;
					_parent = "<tr><td style='padding:2px'><b>"+$("#spansemuaatribut_"+atribut[i].value).html()+"</b></td></tr>";
				}
			}
			$("#closemodal").trigger("click");
		});
	  
	  	// tampilLatLon();
		$('#dapatkanlokasi').click(function(){
			var lat = $("#latpopup").html();
			var lon = $("#lonpopup").html();
			if($('#jenis').val() == 'smartphone'){
				$("#fixgps_latitude").val(lat);
				$("#fixgps_longitude").val(lon);
			}else if($('#jenis').val() == 'fingerprint'){
				$("#fp_lat").val(lat);
				$("#fp_lon").val(lon);
			}
			$("#latpopup").html('');
			$("#lonpopup").html('');
			$("#closemodalpeta").trigger('click');
		});

		@if($onboarding)
			$('#nama').popover({
        		placement : 'auto right',
        		trigger : 'focus',
			});
			$('#nama').popover('show')
			$('#jenis').popover({
        		placement : 'auto right',
        		trigger : 'focus',
			});
			$('#cekjamserver').popover({
        		placement : 'auto right',
        		trigger : 'focus',
			});
			$('#utcdefault').popover({
        		placement : 'auto right',
        		trigger : 'focus',
			});
			$('[data-toggle="popover_peta"]').popover({
        		placement : 'auto right',
        		trigger : 'manual',
			});

			$('[data-toggle="popover_atribut"]').popover({
        		placement : 'auto right',
        		trigger : 'manual',
			});
		@endif

		$(document).on("click", ".popover .close" , function(){
        	$(this).parents('.popover').popover('hide');
    	});
	});

  function aturatribut(){
    @if(count($atributs) > 0)
      $("#buttonmodalatribut").trigger('click');
    @else
      alertWarning("{{ trans('all.nodata') }}");
    @endif
    return false;
  }

  function tampilDetail(){
	$('#detail').css('display','');
	$('#spantampildetail').remove();
  }

  function cekJenis(){
      $('.flagfingerprint').css('display', 'none');
      $('.flagsmartphone').css('display', 'none');
      var jenis = $('#jenis').val();
      if(jenis == 'fingerprint'){
		$('.flagfingerprint').css('display', '');
		@if($onboarding)
		  $('[data-toggle="popover_peta"]').popover("show");
		  $($('[data-toggle="popover_peta"]').next()).css({"top": "579px", "left": "300px", "display": "block"});
		  $($('[data-toggle="popover_peta"]').next().children()).css({"top": "50%"});
		  $('[data-toggle="popover_atribut"]').popover("show");
		  $($('[data-toggle="popover_atribut"]').next()).css({"top": "790px", "left": "362px", "display": "block"});
		  $($('[data-toggle="popover_atribut"]').next().children()).css({"top": "50%"});
		@endif
	  }
	  if(jenis == 'smartphone'){
		$('.flagsmartphone').css('display', '');
		@if($onboarding)
		  $('[data-toggle="popover_peta"]').popover("show");
		  $($('[data-toggle="popover_peta"]').next()).css({"top": "227px", "left": "300px", "display": "block"});
		  $('[data-toggle="popover_atribut"]').popover("show");
		  $($('[data-toggle="popover_atribut"]').next()).css({"top": "440px", "left": "362px", "display": "block"});
		  $($('[data-toggle="popover_atribut"]').next().children()).css({"top": "50%"});
		@endif
		tampilLatLon();
	  }
      $('#spantampildetail').css('display','');
  }

  function tampilLatLon(){
	  var fixgps_gunakan = $('#fixgps_gunakan').val();
	  $('.fixgps_gunakan').css('display', 'none');
	  if(fixgps_gunakan == 'y'){
		  $('.fixgps_gunakan').css('display','');
		  @if($onboarding)
		  	$($('[data-toggle="popover_atribut"]').next()).css({"top": "570px", "left": "362px", "display": "block"});
		  @endif
	  }
  }
</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_mesin') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.menu_mesin') }}</li>
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
          	<form action="@if($onboarding){{ url('datainduk/absensi/mesin?onboarding=true') }} @else {{ url('datainduk/absensi/mesin') }} @endif" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<table width="480px">
					<tr>
						<td width=200px>{{ trans('all.nama') }}</td>
						<td>
							<input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="30" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{ trans('onboarding.mesin_nama') }}</div></div>' data-content='content'>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.jenis') }}</td>
						<td style="float:left">
							<select id="jenis" name="jenis" class="form-control" onchange="return cekJenis()" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{ trans('onboarding.mesin_jenis') }}</div></div>' data-content='content'>
								<option value=""></option>
								<option value="smartphone">{{ trans('all.smartphone') }}</option>
								<option value="fingerprint">{{ trans('all.fingerprint') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.cekjamserver') }}</td>
						<td style="float:left">
							<select id="cekjamserver" name="cekjamserver" class="form-control" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{ trans('onboarding.mesin_jam') }}</div></div>' data-content='content'>
								<option value=""></option>
								<option value="y">{{ trans('all.ya') }}</option>
								<option value="t">{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.utcdefault') }}</td>
						<td style="float:left">
							<select id="utcdefault" name="utcdefault" class="form-control" onchange="return changeutc()" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{ trans('onboarding.mesin_zona') }}</div></div>' data-content='content'>
								<option value=""></option>
								<option value="y">{{ trans('all.ya') }}</option>
								<option value="t">{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr id="label-utc" style="display:none">
						<td>{{ trans('all.utc') }}</td>
						<td style="float:left">
							<select id="utc" name="utc" class="form-control">
								<option value=""></option>
								<option value="-12:00">-12:00</option>
								<option value="-11:30">-11:30</option>
								<option value="-11:00">-11:00</option>
								<option value="-10:30">-10:30</option>
								<option value="-10:00">-10:00</option>
								<option value="-09:30">-09:30</option>
								<option value="-09:00">-09:00</option>
								<option value="-08:30">-08:30</option>
								<option value="-08:00">-08:00</option>
								<option value="-07:30">-07:30</option>
								<option value="-07:00">-07:00</option>
								<option value="-06:30">-06:30</option>
								<option value="-06:00">-06:00</option>
								<option value="-05:30">-05:30</option>
								<option value="-05:00">-05:00</option>
								<option value="-04:30">-04:30</option>
								<option value="-04:00">-04:00</option>
								<option value="-03:30">-03:30</option>
								<option value="-03:00">-03:00</option>
								<option value="-02:30">-02:30</option>
								<option value="-02:00">-02:00</option>
								<option value="-01:30">-01:30</option>
								<option value="-01:00">-01:00</option>
								<option value="00:00">00:00</option>
								<option value="+01:00">+01:00</option>
								<option value="+01:30">+01:30</option>
								<option value="+02:00">+02:00</option>
								<option value="+02:30">+02:30</option>
								<option value="+03:00">+03:00</option>
								<option value="+03:30">+03:30</option>
								<option value="+04:00">+04:00</option>
								<option value="+04:30">+04:30</option>
								<option value="+05:00">+05:00</option>
								<option value="+05:30">+05:30</option>
								<option value="+06:00">+06:00</option>
								<option value="+06:30">+06:30</option>
								<option value="+07:00">+07:00</option>
								<option value="+07:30">+07:30</option>
								<option value="+08:00">+08:00</option>
								<option value="+08:30">+08:30</option>
								<option value="+09:00">+09:00</option>
								<option value="+09:30">+09:30</option>
								<option value="+10:00">+10:00</option>
								<option value="+10:30">+10:30</option>
								<option value="+11:00">+11:00</option>
								<option value="+11:30">+11:30</option>
								<option value="+12:00">+12:00</option>
							</select>
						</td>
					</tr>
					<tr class="flagsmartphone" style="display: none;">
						<td valign="top" style="padding-top: 7px">{{ trans('all.fixgps') }}</td>
						<td style="float: left;">
							<select class="form-control" id="fixgps_gunakan" name="fixgps_gunakan" onChange="tampilLatLon()">
								<option value="y">{{ trans('all.ya') }}</option>
								<option value="t" selected>{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr class="fixgps_gunakan flagsmartphone" style="display: none;">
						<td></td>
						<td data-toggle="popover_peta" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{ trans('onboarding.mesin_lokasi') }}</div></div>' data-content='content'>
							<button style='margin-bottom:0' type="button" id="tombolpeta" class="btn btn-success" data-toggle="modal" data-target="#modalPeta"><i class="fa fa-map-marker"></i>&nbsp;&nbsp;{{ trans('all.peta') }}</button>&nbsp;
						</td>
					</tr>
					<tr class="fixgps_gunakan flagsmartphone" style="display: none;">
						<td valign="top" style="padding-top: 7px">{{ trans('all.lat') }}</td>
						<td style="float: left;">
							<input type="text" class="form-control" size="20" autocomplete="off" name="fixgps_latitude" id="fixgps_latitude" maxlength="30">
						</td>
					</tr>
					<tr class="fixgps_gunakan flagsmartphone" style="display: none;">
						<td valign="top" style="padding-top: 7px">{{ trans('all.lon') }}</td>
						<td style="float: left;">
							<input type="text" class="form-control" size="20" autocomplete="off" name="fixgps_longitude" id="fixgps_longitude" maxlength="30">
						</td>
					</tr>
					<tr class="flagsmartphone flagsmartphone" style="display: none;">
						<td valign="top" style="padding-top: 7px">{{ trans('all.deteksiekspresi') }}</td>
						<td style="float: left;">
							<select class="form-control" id="deteksiekspresi" name="deteksiekspresi">
								<option value="default">{{ trans('all.default') }}</option>
								<option value="y">{{ trans('all.ya') }}</option>
								<option value="t">{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr class="flagsmartphone" style="display: none;">
						<td valign="top" style="padding-top: 7px">{{ trans('all.ijinkanpendaftaran') }}</td>
						<td style="float: left;">
							<select class="form-control" id="ijinkanpendaftaran" name="ijinkanpendaftaran">
								<option value=""></option>
								<option value="y">{{ trans('all.ya') }}</option>
								<option value="t">{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr class="flagsmartphone" style="display: none;">
						<td valign="top" style="padding-top: 7px">{{ trans('all.slideshow') }}</td>
						<td style="float: left;">
							<select class="form-control" id="slideshow" name="slideshow">
								<option value=""></option>
								@if($slideshows != '')
									@foreach($slideshows as $slideshow)
										<option value="{{ $slideshow->id }}">{{ $slideshow->nama }}</option>
									@endforeach
								@endif
							</select>
						</td>
					</tr>
					<tr class="flagsmartphone" style="display: none;">
						<td valign="top" style="padding-top: 7px">{{ trans('all.opsikamera') }}</td>
						<td style="float: left;">
							<select class="form-control" id="kamera_opsi" name="kamera_opsi">
								<option value="depan">{{ trans('all.kameradepan') }}</option>
								<option value="belakang">{{ trans('all.kamerabelakang') }}</option>
								<option value="bebas">{{ trans('all.bebas') }}</option>
							</select>
						</td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.comkey') }}</td>
						<td style="float:left"><input type="text" class="form-control" onkeypress="return onlyNumber(0,event)" autocomplete="off" name="fp_comkey" id="fp_comkey" maxlength="11"></td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.ip') }}</td>
						<td style="float:left"><input type="text" class="form-control" autocomplete="off" name="fp_ip" id="fp_ip" maxlength="50"></td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.soapport') }}</td>
						<td style="float:left"><input type="text" class="form-control" onkeypress="return onlyNumber(0,event)" autocomplete="off" name="fp_soapport" id="fp_soapport" maxlength="11"></td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.udpport') }}</td>
						<td style="float:left"><input type="text" class="form-control" onkeypress="return onlyNumber(0,event)" autocomplete="off" name="fp_udpport" id="fp_udpport" maxlength="11"></td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.fingerprintconnector') }}</td>
						<td style="float:left">
							<select name="fp_idfingerprintconnector" id="fp_idfingerprintconnector" class="form-control">
								<option value=""></option>
								@if($fingerprintconnector != '')
									@foreach($fingerprintconnector as $key)
										<option value="{{ $key->id }}">{{ $key->nama }}</option>
									@endforeach
								@endif
							</select>
						</td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.serialnumber') }}</td>
						<td style="float:left"><input type="text" class="form-control" autocomplete="off" name="fp_serialnumber" id="fp_serialnumber" maxlength="32"></td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.algoritma') }}</td>
						<td style="float:left"><input type="text" class="form-control" autocomplete="off" name="fp_algoritma" id="fp_algoritma" maxlength="32"></td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.intervaltarik') }}</td>
						<td style="float:left"><input type="text" class="form-control" onkeypress="return onlyNumber(0,event)" autocomplete="off" name="fp_intervaltarik" id="fp_intervaltarik" maxlength="32"></td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.ijinkanadmin') }}</td>
						<td style="float:left">
							<select name="fp_ijinkanadmin" id="fp_ijinkanadmin" class="form-control">
								<option value=""></option>
								<option value="y">{{ trans('all.ya') }}</option>
								<option value="t">{{ trans('all.tidak') }}</option>
							</select>
						</td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td></td>
						<td data-toggle="popover_peta" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{ trans('onboarding.mesin_lokasi') }}</div></div>' data-content='content'>
							<button style='margin-bottom:0' type="button" id="tombolpeta" class="btn btn-success" data-toggle="modal" data-target="#modalPeta"><i class="fa fa-map-marker"></i>&nbsp;&nbsp;{{ trans('all.peta') }}</button>
						</td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.lat') }}</td>
						<td style="float:left"><input type="text" class="form-control" autocomplete="off" name="fp_lat" id="fp_lat" maxlength="32"></td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.lon') }}</td>
						<td style="float:left"><input type="text" class="form-control" autocomplete="off" name="fp_lon" id="fp_lon" maxlength="32"></td>
					</tr>
					<tr class="flagfingerprint" style="display: none;">
						<td>{{ trans('all.status') }}</td>
						<td style="float:left">
							<select name="fp_status" id="fp_status" class="form-control">
								<option value=""></option>
								<option value="i">{{ trans('all.inactive') }}</option>
								<option value="r">{{ trans('all.ready') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.opsigetid') }}</td>
						<td style="float:left">
							<select name="getid_opsi" class="form-control">
								<option value="default">{{ trans('all.default') }}</option>
								<option value="pin">{{ trans('all.pin') }}</option>
								<option value="daftar">{{ trans('all.daftar') }}</option>
								<option value="otomatis">{{ trans('all.otomatis') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td valign="top" style="padding-top: 7px">{{ trans('all.atribut') }}</td>
						<td style="float: left;">
							<table id="tabelatribut">
							</table>
							<button type="button" class="btn btn-success" onclick="return aturatribut()" data-toggle="popover_atribut" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{ trans('onboarding.mesin_atribut') }}</div></div>' data-content='content'><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturatribut') }}</button>
							<button type="button" style="display:none" id="buttonmodalatribut" data-toggle="modal" data-target="#myModal"><i class="fa fa-pencil"></i>&nbsp;&nbsp;{{ trans('all.aturatribut') }}</button><br>
							<span id="atributarea"></span>
						</td>
					</tr>
				</table>
				<span style="color:#aaa;cursor: pointer;display:none" id="spantampildetail" onclick="tampilDetail()"><p></p><i>{{ trans('all.datalainnya') }}</i><p></p></span>
				<div id="detail" style="display:none;padding-bottom:16px">
					<table>
						<tr class="flagsmartphone" style="display: none;">
							<td valign="top" style="padding-top: 7px" width=200px>{{ trans('all.perangkat_bt_rfidnfc') }}</td>
							<td>
								<input type="text" class="form-control" id="perangkat_bt_rfidnfc" name="perangkat_bt_rfidnfc" maxlength="17">
							</td>
						</tr>
						<tr class="flagsmartphone" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.perangkat_bt_bukakunci') }}</td>
							<td>
								<input type="text" class="form-control" id="perangkat_bt_bukakunci" name="perangkat_bt_bukakunci" maxlength="17">
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px" width=200px>{{ trans('all.metodeutilitymesin') }}</td>
							<td>
								<select name="metode_utility_mesin" class="form-control">
									<option value="s">SOAP</option>
									<option value="u">UDP</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.metodepegawairead') }}</td>
							<td>
								<select name="metode_pegawai_read" class="form-control">
									<option value="s">SOAP</option>
									<option value="u">UDP</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.metodepegawaiinsert') }}</td>
							<td>
								<select name="metode_pegawai_insert" class="form-control">
									<option value="s">SOAP</option>
									<option value="u">UDP</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.metodepegawaidelete') }}</td>
							<td>
								<select name="metode_pegawai_delete" class="form-control">
									<option value="s">SOAP</option>
									<option value="u">UDP</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.metodefingersampleread') }}</td>
							<td>
								<select name="metode_fingersample_read" class="form-control">
									<option value="s">SOAP</option>
									<option value="u">UDP</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.metodefingersampleinsert') }}</td>
							<td>
								<select name="metode_fingersample_insert" class="form-control">
									<option value="s">SOAP</option>
									<option value="u">UDP</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.metodefingersampledelete') }}</td>
							<td>
								<select name="metode_fingersample_delete" class="form-control">
									<option value="s">SOAP</option>
									<option value="u">UDP</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.metodelogabsenread') }}</td>
							<td>
								<select name="metode_logabsen_read" class="form-control">
									<option value="s">SOAP</option>
									<option value="u">UDP</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.metodelogabsendeleteall') }}</td>
							<td>
								<select name="metode_logabsen_deleteall" class="form-control">
									<option value="s">SOAP</option>
									<option value="u">UDP</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.kuncisaatsinkron') }}</td>
							<td>
								<select name="kunci_saat_sinkron" class="form-control">
									<option value="y">{{ trans('all.ya') }}</option>
									<option value="t" selected>{{ trans('all.tidak') }}</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.kuncisaatdeleteall') }}</td>
							<td>
								<select name="kunci_setelah_deleteall" class="form-control">
									<option value="y">{{ trans('all.ya') }}</option>
									<option value="t" selected>{{ trans('all.tidak') }}</option>
								</select>
							</td>
						</tr>
						<tr class="flagfingerprint" style="display: none;">
							<td valign="top" style="padding-top: 7px">{{ trans('all.restartsetelahdeleteall') }}</td>
							<td>
								<select name="restart_setelah_delete_all" class="form-control">
									<option value="y">{{ trans('all.ya') }}</option>
									<option value="t" selected>{{ trans('all.tidak') }}</option>
								</select>
							</td>
						</tr>
					</table>
				</div>
				<table>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../mesin')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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
		<div class="modal-dialog @if(count($atribut)<=1) modal-sm @elseif(count($atribut)==2) modal-md @else modal-lg @endif">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
					<h4 class="modal-title">{{ trans('all.atribut') }}</h4>
				</div>
				<div class="modal-body" style="max-height:480px;overflow: auto;">
					@if(isset($atribut))
						@for($i=0;$i<count($atribut);$i++)
							<div class="@if(count($atribut)<=1) col-md-12 @elseif(count($atribut)==2) col-md-6 @else col-md-4 @endif">
								<span style="margin-left:-10px">
									<input type="checkbox" class="atributpopup" id="semuaatribut_{{ $atribut[$i]['idatribut'] }}" value="{{ $atribut[$i]['idatribut'] }}" onclick="checkboxallclick('semuaatribut_{{ $atribut[$i]['idatribut'] }}','attr_{{ $atribut[$i]['idatribut'] }}')">&nbsp;&nbsp;<span style="margin:0" id="spansemuaatribut_{{ $atribut[$i]['idatribut'] }}" onclick="spanallclick('semuaatribut_{{ $atribut[$i]['idatribut'] }}','attr_{{ $atribut[$i]['idatribut'] }}')"><strong>{{ $atribut[$i]['atribut'] }}</strong></span>
								</span>
								<table>
									@foreach($atribut[$i]['atributnilai'] as $key)
										<tr>
											<td style="width:20px;padding:2px" valign="top">
												<input type="checkbox" @if($key->enable == 0) disabled @endif idatribut="{{ $key->idatribut }}" onchange="return checkAllAttr('attr_{{ $key->idatribut }}','semuaatribut_{{ $key->idatribut }}')" class="atributpopup attr_{{ $key->idatribut }}" name="atribut_{{ $key->idatribut }}" id="atributpopup{{ $key->id }}" value="{{ $key->id }}">
											</td>
											<td style="padding: 2px;">
												<span id="attrpopup_atribut{{ $key->id }}" onclick="spanClick('atributpopup{{ $key->id }}')" atribut="{{ $atribut[$i]['atribut'] }}">{{ $key->nilai }}</span>
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

	<!-- Modal peta-->
    <div class="modal fade" id="modalPeta" role="dialog" tabindex='-1'>
		<div class="modal-dialog modal-md">
		  
		  <!-- Modal content-->
		  <div class="modal-content">
			<div class="modal-header">
			  <button type="button" class="close" id='closemodalpeta' data-dismiss="modal">&times;</button>
			  <h4 class="modal-title">{{ trans('all.peta') }}</h4>
			</div>
			<!-- <div class="modal-body" style="height:460px;overflow: auto;"> -->
			<div>
			  <table width='100%'>
				<tr>
					<td colspan=2>
						<table>
							<tr>
								<td style="padding-left:0;padding-right:10px">{{ trans('all.lokasi') }}</td>
								<td style="padding-left:0;padding-right:0">
									<select class="form-control" id="lokasi" onchange="pilihLokasi()">
										@if($datalokasi != '')
											<option value=''></option>
											@foreach($datalokasi as $key)
												<option value="{{ $key->lat.'|'.$key->lon }}">{{ $key->nama }}</option>
											@endforeach
										@endif
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="padding-left:0;padding-right:0">
						<input id="pac-input" class="controls pac-input" type="text" placeholder="Search Box">
						<div id="map"></div>
				  	</td>
				</tr>
				<tr>
				  <td>
					<i>
					  Lat : <span id="latpopup"></span><br>
					  Lon : <span id="lonpopup"></span>
					</i>
				  </td>
				  <td align=right>
					<button type="button" id="dapatkanlokasi" class="btn btn-success"><i class='fa fa-map-marker'></i> {{ trans('all.simpan') }}</button>&nbsp;&nbsp;
					<button data-dismiss="modal" id="tutupmodal" class="btn btn-primary"><i class='fa fa-undo'></i> {{ trans('all.tutup') }}</button>
				  </td>
				</tr>
			  </table>
			</div>
		  </div>
		</div>
	</div>
	<!-- Modal peta-->
  
	<script>
	function initMap() {
		map = new google.maps.Map(document.getElementById('map'), {
			center: {lat: -8.699, lng: 115.201},
			zoom: 4,
			mapTypeId: 'roadmap',
			gestureHandling: 'greedy',
			fullscreenControl: false,
			//styles: styleGoogleMaps
		});

		var mapMaxZoom = 18;
		var geoloccontrol = new klokantech.GeolocationControl(map, mapMaxZoom);

		// Create the search box and link it to the UI element.
		var input = document.getElementById('pac-input');
		var searchBox = new google.maps.places.SearchBox(input);
		map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

		// Bias the SearchBox results towards current map's viewport.
		map.addListener('bounds_changed', function() {
			searchBox.setBounds(map.getBounds());
		});

		//click on any place of maps / klik di manapun di daerah map
		map.addListener('click', function( event ){
			if(markers != ''){
				markers.setMap(null);
			}
			$('#lokasi').val('');
			getlatlon(event);
			var myLatlng = new google.maps.LatLng(event.latLng.lat(),event.latLng.lng());
			markers = new google.maps.Marker({
				position: myLatlng,
				map: map
			});
		});

		// Listen for the event fired when the user selects a prediction and retrieve
		// more details for that place. / pencarian lokasi
		searchBox.addListener('places_changed', function() {
			var places = searchBox.getPlaces();

			if (places.length == 0) {
				return;
			}

			// Clear out the old markers.
			if(markers != ''){
				markers.setMap(null);
			}

			// For each place, get the icon, name and location.
			var bounds = new google.maps.LatLngBounds();
			places.forEach(function(place) {
				if (!place.geometry) {
//					console.log("Returned place contains no geometry");
					return;
				}

				// Create a marker for each place.
				markers = new google.maps.Marker({
					position: place.geometry.location,
					map: map
				});

				markers.addListener('click', function(event) {
					getlatlon(event);
				});

				if (place.geometry.viewport) {
					// Only geocodes have viewport.
					bounds.union(place.geometry.viewport);
				} else {
					bounds.extend(place.geometry.location);
				}
			});
			map.fitBounds(bounds);
		});
	}

	function getlatlon(event){
		document.getElementById('latpopup').innerHTML=event.latLng.lat();
		document.getElementById('lonpopup').innerHTML=event.latLng.lng();
	}

	function pilihLokasi(){
		// Clear out the old markers.
		if(markers != ''){
			markers.setMap(null);
		}
		if($('#lokasi').val() != ''){
			var lokasi = $('#lokasi').val().split('|');
			document.getElementById('latpopup').innerHTML=lokasi[0];
			document.getElementById('lonpopup').innerHTML=lokasi[1];
			var myLatlng = new google.maps.LatLng(lokasi[0],lokasi[1]);
			markers = new google.maps.Marker({
				position: myLatlng,
				map: map
			});
		}
	}

	$('#modalPeta').on('shown.bs.modal', function(){
		if (firstRun==true) {
			firstRun = false;
			initMap();
		}
		if($('#jenis').val() == 'smartphone'){
			if($('#latpopup').html() == ''){
				$('#latpopup').html($('#fixgps_latitude').val());
			}
			if($('#lonpopup').html() == ''){
				$('#lonpopup').html($('#fixgps_longitude').val());
			}
		}else if($('#jenis').val() == 'fingerprint'){
			if($('#latpopup').html() == ''){
				$('#latpopup').html($('#fp_lat').val());
			}
			if($('#lonpopup').html() == ''){
				$('#lonpopup').html($('#fp_lon').val());
			}
		}
	});
	</script>
	<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script>
@stop