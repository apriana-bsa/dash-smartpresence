@extends('layouts.master')
@section('title', trans('all.menu_logabsen'))
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
    
    $(function(){
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });
        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
        $('.jam').inputmask( 'h:s' );
        selectInput('#pegawai','{{ url('select2pegawai') }}');
    });
        
	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var tanggal = $("#tanggal").val();
        var jam = $("#jam").val();
		var pegawai = $("#pegawai").val();
		var masukkeluar = $("#masukkeluar").val();
		var terhitungkerja = $("#terhitungkerja").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
            return false;
		@endif

		if(tanggal == ""){
			alertWarning("{{ trans('all.waktukosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#tanggal'));
            });
            return false;
		}
        
        if(jam == ""){
            alertWarning("{{ trans('all.waktukosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#jam'));
            });
            return false;
        }
        
        if(pegawai == null){
            alertWarning("{{ trans('all.pegawaikosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#pegawai'));
            });
            return false;
        }
        
        if(masukkeluar == ""){
            alertWarning("{{ trans('all.masukkeluarkosong') }}",
            function() {
                aktifkanTombol();
                setFocus($('#masukkeluar'));
            });
            return false;
        }
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_logabsen') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.menu_logabsen') }}</li>
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
          	<form action="{{ url('datainduk/absensi/logabsen') }}" method="post" onsubmit="return validasi()">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<table>
					<tr>
						<td width="110px">{{ trans('all.waktu') }}</td>
						<td style="float: left;">
							<input type="text" size="11" value="{{ date('d/m/Y') }}" class="form-control date" autofocus autocomplete="off" name="tanggal" id="tanggal" maxlength="10" placeholder="dd/mm/yyyy">
						</td>
                        <td style="float: left;">
							<input type="text" size="6" value="{{ date('H:i') }}" class="form-control jam" autocomplete="off" name="jam" id="jam" placeholder="hh:mm">
						</td>
					</tr>
					<tr>
						<td width="110px">{{ trans('all.pegawai') }}</td>
						<td colspan="2">
                            <select class="form-control" id="pegawai" name="pegawai[]" multiple="multiple"></select>
						</td>
					</tr>
                    <tr>
                        <td>{{ trans('all.masukkeluar') }}</td>
                        <td colspan="2" style="float:left">
                            <select class="form-control" name="masukkeluar" id="masukkeluar">
                                <option value=""></option>
                                <option value="m">{{ trans('all.masuk') }}</option>
                                <option value="k">{{ trans('all.keluar') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.alasan') }}</td>
                        <td colspan="2" style="float:left">
                            <select class="form-control" name="alasan" id="alasan">
                                <option value=""></option>
                                @foreach($alasanmasukkeluar as $key)
                                    <option value="{{ $key->id }}">{{ $key->alasan }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.flag') }}</td>
                        <td colspan="2" style="float:left">
                            <select class="form-control" name="flag" id="flag">
                                <option value=""></option>
                                <option value="tidak-terlambat">{{ trans('all.tidakterlambat') }}</option>
                                <option value="tidak-pulangawal">{{ trans('all.tidakpulangawal') }}</option>
                                <option value="lembur">{{ trans('all.lembur') }}</option>
                                <option value="tidak-lembur">{{ trans('all.tidaklembur') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td width="110px">{{ trans('all.keterangan') }}</td>
                        <td colspan="2" style="float:left">
                            <textarea type="text" class="form-control" autocomplete="off" name="flagketerangan" id="flagketerangan" maxlength="255" style="width:480px;resize:none"></textarea>
                        </td>
                    </tr>
					<tr>
						<td colspan=3>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
							<button type="button" id="kembali" onclick="return ke('../logabsen')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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