@extends('layouts.master')
@section('title', trans('all.payrollkomponenmaster'))
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
        pilihCaraInput();
    });
    
	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var kelompok = $("#kelompok").val();
		var nama = $("#nama").val();
		var kode = $("#kode").val();
		
		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
                aktifkanTombol();
            });
            return false;
		@endif

		if(kelompok == ""){
			alertWarning("{{ trans('all.kelompok').' '.trans('all.sa_kosong') }}",
            function() {
                  aktifkanTombol();
                  setFocus($('#kelompok'));
            });
            return false;
		}

		if(nama == ""){
			alertWarning("{{ trans('all.nama').' '.trans('all.sa_kosong') }}",
            function() {
                  aktifkanTombol();
                  setFocus($('#nama'));
            });
            return false;
		}

		if(kode == ""){
			alertWarning("{{ trans('all.kode').' '.trans('all.sa_kosong') }}",
            function() {
                  aktifkanTombol();
                  setFocus($('#kode'));
            });
            return false;
		}
    }
    
    function pilihCaraInput(){
        var carainput = $('#carainput').val();
        $('.tr_inputmanual').css('display', 'none');
        $('.tr_formula').css('display', 'none');
        if(carainput == 'inputmanual'){
            $('.tr_inputmanual').css('display', '');
        }else if(carainput == 'formula'){
            $('.tr_formula').css('display', '');
        }
    }
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.komponenmaster') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.payroll') }}</li>
            <li>{{ trans('all.komponenmaster') }}</li>
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
          	<form action="{{ url('datainduk/payroll/payrollkelompok/'.$idpayrollkelompok.'/komponenmaster') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="100%">
                    <tr>
                        <td width="150px">{{ trans('all.nama') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" autofocus size="40" autocomplete="off" name="nama" id="nama" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.kode') }}</td>
                        <td style="float:left">
                            <input type="text" size="20" class="form-control" autocomplete="off" name="kode" id="kode" maxlength="20">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.komponenmastergrup') }}</td>
                        <td style="float:left">
                            <select name="payrollkomponenmastergroup" id="payrollkomponenmastergroup" class="form-control">
                                <option value=""></option>
                                @if($datapayrollkomponenmastergroup != '')
                                    @foreach($datapayrollkomponenmastergroup as $key)
                                        <option value="{{ $key->id }}">{{ $key->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tipedata') }}</td>
                        <td style="float:left">
                            <select name="tipedata" id="tipedata" class="form-control">
                                <option value="teks">{{ trans('all.teks') }}</option>
                                <option value="angka">{{ trans('all.angka') }}</option>
                                <option value="uang">{{ trans('all.uang') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.digunakan') }}</td>
                        <td style="float:left">
                            <select name="digunakan" id="digunakan" class="form-control">
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.tampilkan') }}</td>
                        <td style="float:left">
                            <select name="tampilkan" id="tampilkan" class="form-control">
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.carainput') }}</td>
                        <td style="float:left">
                            <select name="carainput" id="carainput" class="form-control">
                                <option value="inputmanual">{{ trans('all.inputmanual') }}</option>
                                <option value="formula">{{ trans('all.formula') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../komponenmaster')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                        </td>
                    </tr>
                </table>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal atribut-->
    <a href="" id="showmodalatribut" data-toggle="modal" data-target="#modalatribut" style="display:none"></a>
    <div class="modal modalatribut fade" id="modalatribut" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">
          </div>
      </div>
    </div>
  <!-- Modal atribut-->
  <script>
    window.atributVariable=(function(formid){
        $("#showmodalatribut").attr("href", "");
        $("#showmodalatribut").attr("href", "{{ url('atributvariable') }}/"+formid);
        $('#showmodalatribut').trigger('click');
        return false;
    });

    window.atributNilai=(function(idatribut,formid){
        $("#showmodalatribut").attr("href", "");
        $("#showmodalatribut").attr("href", "{{ url('atributnilai') }}/"+idatribut+"/"+formid);
        $('#showmodalatribut').trigger('click');
        return false;
    });

    $('body').on('hidden.bs.modal', '.modalatribut', function () {
        $(this).removeData('bs.modal');
        $("#" + $(this).attr("id") + " .modal-content").empty();
        $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
    });
  </script>
@stop