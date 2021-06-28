@extends('layouts.master')
@section('title', trans('all.fingerprintconnector'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	
    span{
        cursor:default;
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
		var username = $("#username").val();
		var katasandi = $("#katasandi").val();
		var intervalceksync = $("#intervalceksync").val();
		var syncdatapadadari = $("#syncdatapadadari").val();
		var syncdatapadake = $("#syncdatapadake").val();
		var cleardatapadadari = $("#cleardatapadadari").val();
		var cleardatapadake = $("#cleardatapadake").val();
		var status = $("#status").val();

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

		if(username == ""){
			alertWarning("{{ trans('all.usernamekosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#username'));
            });
      		return false;
		}

		if(katasandi == ""){
			alertWarning("{{ trans('all.katasandikosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#katasandi'));
            });
      		return false;
		}

		if (cekAlertAngkaValid(intervalceksync,0,99999999999,0,"{{ trans('all.intervalceksync') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#intervalceksync'));
                }
            )==false) return false;

		if(syncdatapadadari == ""){
			alertWarning("{{ trans('all.syncdatapadakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#syncdatapadadari'));
            });
      		return false;
		}

		if(syncdatapadake == ""){
			alertWarning("{{ trans('all.syncdatapadakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#syncdatapadake'));
            });
      		return false;
		}

		if(cleardatapadadari == ""){
			alertWarning("{{ trans('all.cleardatapadakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#cleardatapadadari'));
            });
      		return false;
		}

		if(cleardatapadake == ""){
			alertWarning("{{ trans('all.cleardatapadakosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#cleardatapadake'));
            });
      		return false;
		}

		if(status == ""){
			alertWarning("{{ trans('all.statuskosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#status'));
            });
      		return false;
		}
	}

	$(function(){
		$('.jam').inputmask( 'h:s' );
	});

	var i = -1;
	function addData(param){
		if($('.tr'+param).length < 4) {
            i++;
            $('#tab' + param).append("<tr id='addr_" + param + i + "' class='tr" + param + "'>" +
                "<td style=padding-left:0px;float:left>" +
                "<input autocomplete='off' size=7 name='" + param + "dari[]' class='form-control jam' placeholder='hh:mm' type='text' id='" + param + "_" + i + "'>" +
                "</td>" +
                "<td style='margin-top:7px;padding-left:0px;float:left;'>-</td>" +
                "<td style=padding-left:0px;float:left>" +
                	"<input autocomplete='off' size=7 name='" + param + "ke[]' class='form-control jam' placeholder='hh:mm' type='text' id='" + param + "_" + i + "'>" +
                "</td>" +
                "<td style=padding-left:0px;width:20px;float:left;>" +
                	"<button type='button' onclick='deleteData(\"" + param + "\"," + i + ")' title='{{ trans('all.hapus') }}' class='btn btn-danger glyphicon glyphicon-remove row-remove'></button>" +
                "</td>" +
                "</tr>");
            $('.jam').inputmask('h:s');
            //document.getElementById('waktumulai_'+i).focus();
        }else{
            alertWarning("{{ trans('all.jumlahtelahmencapaibatasmaksimal') }}");
		}
	}

	function deleteData(param,i){
		$("#addr_"+param+i).remove();
		i--;
	}
</script>
<style>
#password {
	text-security:disc;
	-webkit-text-security:disc;
	-mox-text-security:disc;
}
</style>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.fingerprintconnector') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.menu_mesin') }}</li>
        <li>{{ trans('all.fingerprintconnector') }}</li>
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
          	<form action="{{ url('datainduk/absensi/fingerprintconnector') }}" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<table width="480px">
					<tr>
						<td width=150px>{{ trans('all.nama') }}</td>
						<td>
							<input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="30">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.keterangan') }}</td>
						<td>
							<textarea class="form-control" style="resize:none" autocomplete="off" name="keterangan" id="keterangan"></textarea>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.pushapi') }}</td>
						<td>
							<input type="text" class="form-control" autocomplete="off" name="pushapi" id="pushapi" maxlength="100">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.intervalceksync') }}</td>
						<td>
							<input type="text" onkeypress="return onlyNumber(0,event)" class="form-control" autocomplete="off" name="intervalceksync" id="intervalceksync" maxlength="11">
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.syncdatapada') }}</td>
						<td>
							<table width=100%>
								<tr>
									<td style='padding-left:0px;width:88px;float:left;'><input type="text" class="form-control jam" value="00:00" placeholder='hh:mm' autocomplete="off" name="syncdatapadadari[]" id="syncdatapadadari"></td>
									<td style='margin-top:7px;padding-left:0px;float:left;'>-</td>
									<td style='padding-left:0px;width:88px;float:left;'><input type="text" class="form-control jam" value="23:59" placeholder='hh:mm' autocomplete="off" name="syncdatapadake[]" id="syncdatapadake"></td>
								</tr>
							</table>
							<table width=100% id='tabsyncdatapada'></table>
							<a id="tambahsyncdatapada" title="{{ trans('all.tambah') }}" onclick='addData("syncdatapada")' class="btn btn-success glyphicon glyphicon-plus"></a>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.cleardatapada') }}</td>
						<td>
							<table width=100%>
								<tr>
									<td style='padding-left:0px;width:88px;float:left;'><input type="text" class="form-control jam" value="00:00" placeholder='hh:mm' autocomplete="off" name="cleardatapadadari[]" id="cleardatapadadari"></td>
									<td style='margin-top:7px;padding-left:0px;float:left;'>-</td>
									<td style='padding-left:0px;width:88px;float:left;'><input type="text" class="form-control jam" value="23:59" placeholder='hh:mm' autocomplete="off" name="cleardatapadake[]" id="cleardatapadake"></td>
								</tr>
							</table>
							<table width=100% id='tabcleardatapada'></table>
							<a id="tambahcleardatapada" title="{{ trans('all.tambah') }}" onclick='addData("cleardatapada")' class="btn btn-success glyphicon glyphicon-plus"></a>
						</td>
					</tr>
					<tr>
						<td>{{ trans('all.status') }}</td>
						<td style="float:left">
							<select id="status" name="status" class="form-control">
								<option value=""></option>
								<option value="a">{{ trans('all.aktif') }}</option>
								<option value="t">{{ trans('all.tidakaktif') }}</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../fingerprintconnector')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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