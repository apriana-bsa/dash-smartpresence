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
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.jamkerja') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.jamkerja') }}</li>
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
          	<form action="../{{ $jamkerja->id }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="put">
                <table width="480px">
                    <tr>
                        <td width="150px">{{ trans('all.jamkerja') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus value="{{ $jamkerja->nama }}" autocomplete="off" name="nama" id="nama" maxlength="100">
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.kategori') }}</td>
                        <td style="float:left">
                            <select class="form-control" id="kategori" name="kategori">
                                <option value=""></option>
                                @if($datakategori != '')
                                    @foreach($datakategori as $data)
                                        <option value="{{ $data->id }}" @if($jamkerja->idkategori == $data->id) selected @endif>{{ $data->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.jenis') }}</td>
                      <td style="float:left">
                        @if($adajamkerja == true)
                            <input type="hidden" value="{{ $jamkerja->jenis }}" name="jenis">
                            {{ trans('all.'.$jamkerja->jenis) }}
                        @else
                            <select class="form-control" id="jenis" name="jenis">
                                <option value="full" @if($jamkerja->jenis == "full") selected @endif>{{ trans('all.full') }}</option>
                                <option value="shift" @if($jamkerja->jenis == "shift") selected @endif>{{ trans('all.shift') }}</option>
                            </select>
                        @endif
                      </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.toleransi') }}</td>
                        <td style="float:left">
                            <table>
                                <tr>
                                    <td style="padding:0">
                                        <input type="text" name="toleransi" onkeypress="return onlyNumber(0,event)" size="5" class="form-control" value="{{ $jamkerja->toleransi }}" maxlength="10" id="toleransi" autocomplete="off">
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
                                <option value="jadwal" @if($jamkerja->acuanterlambat == "jadwal") selected @endif>{{ trans('all.jadwal') }}</option>
                                <option value="toleransi" @if($jamkerja->acuanterlambat == "toleransi") selected @endif>{{ trans('all.toleransi') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.hitunglembursetelah') }}</td>
                        <td style="float:left">
                            <table>
                                <tr>
                                    <td style="padding:0">
                                        <input type="text" name="hitunglemburstlh" onkeypress="return onlyNumber(0,event)" size="5" class="form-control" value="{{ $jamkerja->hitunglemburstlh }}" maxlength="10" id="hitunglembursetelah" autocomplete="off">
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
                                <option value="y" @if($jamkerja->digunakan == "y") selected @endif>{{ trans('all.ya') }}</option>
                                <option value="t" @if($jamkerja->digunakan == "t") selected @endif>{{ trans('all.tidak') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../../jamkerja')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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