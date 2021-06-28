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
        
	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var nama = $("#nama").val();
        var jenis = $("#jenis").val();
        var toleransi = $("#toleransi").val();
        var hitunglembursetelah = $("#hitunglembursetelah").val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
            return false;
		@endif

		if(nama == ""){
            alertWarning("{{ trans('all.jamkerjakosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#nama'));
                });
            return false;
        }

        if(jenis == ""){
            alertWarning("{{ trans('all.jeniskosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#jenis'));
                });
            return false;
        }

        if (cekAlertAngkaValid(toleransi,0,9999,0,"{{ trans('all.toleransi') }}",
            function() {
                aktifkanTombol();
                setFocus($('#toleransi'));
            }
        )==false) return false;

        if (cekAlertAngkaValid(hitunglembursetelah,0,9999,0,"{{ trans('all.hitunglembursetelah') }}",
            function() {
                aktifkanTombol();
                setFocus($('#hitunglembursetelah'));
            }
        )==false) return false;
  }
    @if($onboarding)
        $(document).ready(function(){
            $('[data-toggle="popover-jamkerjaform"]').popover({
                placement : 'auto right',
                trigger : 'manual',
            });
            $('[data-toggle="popover-jamkerjaform"]').popover('show');
            $(document).on("click", ".popover .close" , function(){
                $(this).parents('.popover').popover('hide');
            });
        });
    @endif
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.jamkerja') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.jamkerja') }}</li>
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
          	<form action="{{ url($onboarding ? 'datainduk/absensi/jamkerja?onboarding=true' : 'datainduk/absensi/jamkerja') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px" id='tabShift'>
                    <tr>
                        <td width="150px">{{ trans('all.jamkerja') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="100">
                        </td>
                        <td id="popover-jamkerjaform"><div data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{ trans('onboarding.form_jamkerja') }}</div></div>' data-toggle="popover-jamkerjaform" data-content="content"/></td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.kategori') }}</td>
                      <td style="float:left">
                        <select class="form-control" id="kategori" name="kategori">
                            <option value=""></option>
                            @if($datakategori != '')
                                @foreach($datakategori as $data)
                                    <option value="{{ $data->id }}">{{ $data->nama }}</option>
                                @endforeach
                            @endif
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.jenis') }}</td>
                      <td style="float:left">
                        <select class="form-control" id="jenis" name="jenis">
                          <option value=""></option>
                          <option value="full">{{ trans('all.full') }}</option>
                          <option value="shift">{{ trans('all.shift') }}</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.toleransi') }}</td>
                        <td style="float:left">
                            <table>
                                <tr>
                                    <td style="padding:0">
                                        <input type="text" name="toleransi" onkeypress="return onlyNumber(0,event)" size="5" class="form-control" maxlength="10" id="toleransi" autocomplete="off">
                                    </td>
                                    <td style="padding:0;padding-left:10px">{{ trans('all.menit') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.acuanterlambat') }}</td>
                        <td style="float:left">
                            <select class="form-control" id="acuanterlambat" name="acuanterlambat">
                                <option value="jadwal">{{ trans('all.jadwal') }}</option>
                                <option value="toleransi">{{ trans('all.toleransi') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.hitunglembursetelah') }}</td>
                        <td style="float:left">
                            <table>
                                <tr>
                                    <td style="padding:0">
                                        <input type="text" name="hitunglemburstlh" onkeypress="return onlyNumber(0,event)" size="5" class="form-control" maxlength="10" id="hitunglembursetelah" autocomplete="off">
                                    </td>
                                    <td style="padding:0;padding-left:10px">{{ trans('all.menit') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.digunakan') }}</td>
                        <td style="float:left">
                            <select class="form-control" id="digunakan" name="digunakan">
                                <option value="y">{{ trans('all.ya') }}</option>
                                <option value="t">{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                      <td colspan=2>
                          <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                        <button type="button" id="kembali" onclick="return ke('../jamkerja')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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