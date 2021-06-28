@extends('layouts.master')
@section('title', trans('all.jamkerja'))
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
        $('.jam').inputmask( 'h:s' );
    });

	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var berlakumulai = $("#berlakumulai").val();
        var jammasuk = $("#berlakumulai").val();
        var jampulang = $("#berlakumulai").val();
        var jamistirahatmulai = $("#jamistirahatmulai").val();
        var jamistirahatselesai = $("#jamistirahatselesai").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
                        function() {
                          aktifkanTombol();
                        });
                        return false;
		@endif

		if(berlakumulai == ""){
          alertWarning("{{ trans('all.berlakumulaikosong') }}",
                function() {
                  aktifkanTombol();
                  setFocus($('#berlakumulai'));
                });
          return false;
        }
    
        if(jammasuk == ""){
          alertWarning("{{ trans('all.waktukerjakosong') }}",
                function() {
                  aktifkanTombol();
                  setFocus($('#jammasuk'));
                });
          return false;
        }
    
        if(jampulang == ""){
          alertWarning("{{ trans('all.waktukerjakosong') }}",
                function() {
                  aktifkanTombol();
                  setFocus($('#jampulang'));
                });
          return false;
        }
    
        if(jamistirahatmulai == ""){
          alertWarning("{{ trans('all.istirahatkosong') }}",
                function() {
                  aktifkanTombol();
                  setFocus($('#jamistirahatmulai'));
                });
          return false;
        }
    
        if(jamistirahatselesai == ""){
          alertWarning("{{ trans('all.istirahatkosong') }}",
                function() {
                  aktifkanTombol();
                  setFocus($('#jamistirahatselesai'));
                });
          return false;
        }
	}
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.shift')." (".$jamkerjashift.")" }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.jamkerja') }}</li>
        <li>{{ trans('all.shift') }}</li>
        <li>{{ trans('all.detail') }}</li>
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
          	<form action="../{{ $shiftdetail->id }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="put">
                <table width="480px">
                    <tr>
                      <td width='150px'>{{ trans('all.berlakumulai') }}</td>
                      <td style='float:left'>
                        <input type='text' class='form-control date' autofocus value='{{ date_format(date_create($shiftdetail->berlakumulai), "d/m/Y") }}' autocomplete='off' id='berlakumulai' size='11' placeholder='dd/mm/yyyy' name='berlakumulai'>
                      </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.waktukerja') }}</td>
                      <td>
                        <table width='200px'>
                          <tr>
                            <td style='padding-left:0px;padding-top:0px;padding-bottom:0px'>
                              <input type='text' class='form-control jam' value="{{ $shiftdetail->jammasuk }}" placeholder='hh:mm' id='jammasuk' name='jammasuk'>
                            </td>
                            <td style='padding-left:0px;padding-top:0px;padding-bottom:0px'>-</td>
                            <td style='padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px'>
                              <input type='text' class='form-control jam' value="{{ $shiftdetail->jampulang }}" placeholder='hh:mm' id='jampulang' name='jampulang'>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.waktuistirahat') }}</td>
                      <td>
                        <table width='200px'>
                          <tr>
                            <td style='padding-left:0px;padding-top:0px;padding-bottom:0px'>
                              <input type='text' class='form-control jam' value="{{ $shiftdetail->jamistirahatmulai }}" placeholder='hh:mm' id='jamistirahatmulai' name='jamistirahatmulai'>
                            </td>
                            <td style='padding-left:0px;padding-top:0px;padding-bottom:0px'>-</td>
                            <td style='padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px'>
                              <input type='text' class='form-control jam' value="{{ $shiftdetail->jamistirahatselesai }}" placeholder='hh:mm' id='jamistirahatselesai' name='jamistirahatselesai'>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../../detail')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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