@extends('layouts.master')
@section('title', trans('all.jamkerja'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	</style>
	<script>
    function _checkboxclick(param,disabled,input,input2){
        if($("#"+param).prop('checked')){
            $("#"+param).prop('checked', true);
            if(disabled == false){
                $('.'+input).css('display', 'none');
            }else{
                $('#'+input).attr('disabled', 'disabled');
            }
        }else{
            $("#"+param).prop('checked', false);
            if(disabled == false){
                $('.'+input).css('display','');
            }else{
                $('#'+input).removeAttr('disabled');
            }
        }
    }

    function _spanclick(param,disabled,input){
        if($("#"+param).prop('checked')){
            $("#"+param).prop('checked', false);
            if(disabled == false){
                $('.'+input).css('display','');
            }else{
                $('#'+input).removeAttr('disabled');
            }
        }else{
            $("#"+param).prop('checked', true);
            if(disabled == false){
                $('.'+input).css('display', 'none');
            }else{
                $('#'+input).attr('disabled', 'disabled');
            }
        }
    }


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

        @if($jamkerjashift->_2_masuk == 'y' and $jamkerjashift->_2_masuk == 'y' and $jamkerjashift->_3_masuk == 'y' and $jamkerjashift->_4_masuk == 'y' and $jamkerjashift->_5_masuk == 'y' and $jamkerjashift->_6_masuk == 'y' and $jamkerjashift->_7_masuk == 'y') setTimeout(_checkboxclick('setiaphari',false,'flaghari'),100); @endif
    });
    
    function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
        var nama = $("#nama").val();
        var kode = $("#kode").val();
        var digunakan = $("#digunakan").val();
        var urutan = $("#urutan").val();
        
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

        if(kode == ""){
          alertWarning("{{ trans('all.kodekosong') }}",
                  function() {
                    aktifkanTombol();
                    setFocus($('#kode'));
                  });
            return false;
        }

        if (cekAlertAngkaValid(urutan,0,999,0,"{{ trans('all.urutan') }}",
                        function() {
                            aktifkanTombol();
                            setFocus($('#urutan'));
                        }
                )==false) return false;
        urutan=replaceAll(urutan.trim(),',','.');
    }
  </script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.jamkerja')." (".$jamkerja.")" }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.jamkerja') }}</li>
        <li>{{ trans('all.shift') }}</li>
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
          	<form action="../{{ $jamkerjashift->id }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="put">
                <table width="480px">
                    <tr>
                      <td width="150px">{{ trans('all.nama') }}</td>
                      <td>
                        <input type="text" class="form-control" autofocus autocomplete="off" value="{{ $jamkerjashift->namashift }}" id="nama" name="nama" maxlength="100">
                      </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.jenis') }}</td>
                        <td style="float:left">
                            <select name="jenis" id="jenis" class="form-control">
                                <option value=""></option>
                                @if($jenis != '')
                                    @foreach($jenis as $key)
                                        <option value="{{ $key->id }}" @if($jamkerjashift->idjenis == $key->id) selected @endif>{{ $key->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.kode') }}</td>
                        <td>
                            <input type="text" class="form-control" autocomplete="off" value="{{ $jamkerjashift->kode }}" id="kode" name="kode" maxlength="20">
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="padding-left:0">
                            <table>
                                <tr>
                                    <td><input onclick="checkboxclick('harilibur')" @if($jamkerjashift->_0_masuk == 'y') checked @endif type="checkbox" id="harilibur" name="harilibur"></td>
                                    <td><span onclick="spanclick('harilibur')">{{ trans('all.harilibur') }}</span></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" @if($jamkerjashift->_2_masuk == 'y' and $jamkerjashift->_2_masuk == 'y' and $jamkerjashift->_3_masuk == 'y' and $jamkerjashift->_4_masuk == 'y' and $jamkerjashift->_5_masuk == 'y' and $jamkerjashift->_6_masuk == 'y' and $jamkerjashift->_7_masuk == 'y') checked @endif id="setiaphari" onclick="_checkboxclick('setiaphari',false,'flaghari')" name="setiaphari"></td>
                                    <td><span class="setiaphari" onclick="_spanclick('setiaphari',false,'flaghari')">{{ trans('all.berlakusetiaphari') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('senin')" @if($jamkerjashift->_2_masuk == 'y') checked @endif type="checkbox" id="senin" name="senin"></td>
                                    <td><span onclick="spanclick('senin')">{{ trans('all.senin') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('selasa')" @if($jamkerjashift->_3_masuk == 'y') checked @endif type="checkbox" id="selasa" name="selasa"></td>
                                    <td><span onclick="spanclick('selasa')">{{ trans('all.selasa') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('rabu')" @if($jamkerjashift->_4_masuk == 'y') checked @endif type="checkbox" id="rabu" name="rabu"></td>
                                    <td><span onclick="spanclick('rabu')">{{ trans('all.rabu') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('kamis')" @if($jamkerjashift->_5_masuk == 'y') checked @endif type="checkbox" id="kamis" name="kamis"></td>
                                    <td><span onclick="spanclick('kamis')">{{ trans('all.kamis') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('jumat')" @if($jamkerjashift->_6_masuk == 'y') checked @endif type="checkbox" id="jumat" name="jumat"></td>
                                    <td><span onclick="spanclick('jumat')">{{ trans('all.jumat') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('sabtu')" @if($jamkerjashift->_7_masuk == 'y') checked @endif type="checkbox" id="sabtu" name="sabtu"></td>
                                    <td><span onclick="spanclick('sabtu')">{{ trans('all.sabtu') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('minggu')" @if($jamkerjashift->_1_masuk == 'y') checked @endif type="checkbox" id="minggu" name="minggu"></td>
                                    <td><span onclick="spanclick('minggu')">{{ trans('all.minggu') }}</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.digunakan') }}</td>
                      <td style="float:left">
                        <select class="form-control" id="digunakan" name="digunakan">
                          <option value="y" @if($jamkerjashift->digunakan == 'y') selected @endif>{{ trans('all.ya') }}</option>
                          <option value="t" @if($jamkerjashift->digunakan == 't') selected @endif>{{ trans('all.tidak') }}</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.urutan') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" onkeypress="return onlyNumber(0,event)" size="4" value="{{ $jamkerjashift->urutan }}" name="urutan" autocomplete="off" id="urutan" maxlength="3">
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../../shift')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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