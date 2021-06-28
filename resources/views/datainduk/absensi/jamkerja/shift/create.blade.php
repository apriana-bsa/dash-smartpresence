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

        setTimeout(_checkboxclick('setiaphari',false,'flaghari'),100);
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
    
        if(digunakan == ""){
          alertWarning("{{ trans('all.digunakankosong') }}",
                  function() {
                    aktifkanTombol();
                    setFocus($('#digunakan'));
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
    
        var berlakumulai = document.getElementsByClassName("berlakumulai");
        for(var i=0; i<berlakumulai.length; i++) {
          if(berlakumulai[i].value == ""){
            alertWarning("{{ trans('all.berlakumulaikosong') }}",
                  function() {
                    aktifkanTombol();
                  });
            return false;
          }
    
          if(toleransi[i].value == ""){
            alertWarning("{{ trans('all.toleransikosong') }}",
                  function() {
                    aktifkanTombol();
                  });
            return false;
          }
    
          if(hitunglembursetelah[i].value == ""){
            alertWarning("{{ trans('all.hitunglembursetelahkosong') }}",
                  function() {
                    aktifkanTombol();
                  });
            return false;
          }
    
          if(jammasuk[i].value == ""){
            alertWarning("{{ trans('all.waktukerjakosong') }}",
                  function() {
                    aktifkanTombol();
                  });
            return false;
          }
    
          if(jampulang[i].value == ""){
            alertWarning("{{ trans('all.waktukerjakosong') }}",
                  function() {
                    aktifkanTombol();
                  });
            return false;
          }
    
          if(istirahat[i].value == ""){
            alertWarning("{{ trans('all.istirahatkosong') }}",
                  function() {
                    aktifkanTombol();
                  });
            return false;
          }
    
        }
	}

    var i = -1;
    function addShift(){
        i++;
        $('#tabShift').append("<tr id='addr_shift"+i+"'>"
                                +"<td colspan='2' style='padding-left:0px'>"
                                  +"<table width='100%'>"
                                    +"<tr>"
                                      +"<td width='150px'>{{ trans('all.berlakumulai') }}</td>"
                                      +"<td style='float:left'>"
                                        +"<input type='text' class='form-control date berlakumulai' autocomplete='off' id='berlakumulai"+i+"' size='11' placeholder='dd/mm/yyyy' name='berlakumulai[]'>"
                                      +"</td>"
                                      +"<td style=width:20px;><button onclick='deleteShift("+i+")' title='{{ trans('all.hapus') }}' class='btn btn-danger glyphicon glyphicon-remove row-remove'></button></td>"
                                    +"</tr>"
                                    +"<tr>"
                                      +"<td>{{ trans('all.waktukerja') }}</td>"
                                      +"<td>"
                                        +"<table width='200px'>"
                                          +"<tr>"
                                            +"<td style='padding-left:0px;padding-top:0px;padding-bottom:0px'>"
                                              +"<input type='text' class='form-control jam jammasuk' placeholder='hh:mm' id='jammasuk"+i+"' name='jammasuk[]'>"
                                            +"</td>"
                                            +"<td style='padding-left:0px;padding-top:0px;padding-bottom:0px'>-</td>"
                                            +"<td style='padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px'>"
                                              +"<input type='text' class='form-control jam jampulang' placeholder='hh:mm' id='jampulang"+i+"' name='jampulang[]'>"
                                            +"</td>"
                                          +"</tr>"
                                        +"</table>"
                                      +"</td>"
                                    +"</tr>"
                                    +"<tr>"
                                      +"<td>{{ trans('all.waktuistirahat') }}</td>"
                                      +"<td>"
                                        +"<table width='200px'>"
                                          +"<tr>"
                                            +"<td style='padding-left:0px;padding-top:0px;padding-bottom:0px'>"
                                              +"<input type='text' class='form-control jam istirahatmulai' placeholder='hh:mm' id='istirahatmulai"+i+"' name='istirahatmulai[]'>"
                                            +"</td>"
                                            +"<td style='padding-left:0px;padding-top:0px;padding-bottom:0px'>-</td>"
                                            +"<td style='padding-left:0px;padding-top:0px;padding-bottom:0px;padding-right:0px'>"
                                              +"<input type='text' class='form-control jam istirahatselesai' placeholder='hh:mm' id='istirahatselesai"+i+"' name='istirahatselesai[]'>"
                                            +"</td>"
                                          +"</tr>"
                                        +"</table>"
                                      +"</td>"
                                    +"</tr>"
                                  +"</table>"
                                +"</td>"
                              +"</tr>");
        //document.getElementById('shift'+i).focus();
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
          $(this).datepicker('hide');
        });
        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
        $('.jam').inputmask( 'h:s' );
    }
    
    function deleteShift(i){
        $("#addr_shift"+i).remove();
        i--;
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
          	<form action="{{ url('datainduk/absensi/jamkerja/'.$idjamkerja.'/shift') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px" id='tabShift'>
                    <tr>
                      <td width="150px">{{ trans('all.nama') }}</td>
                      <td>
                        <input type="text" class="form-control" autofocus autocomplete="off" id="nama" name="nama" maxlength="100">
                      </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.jenis') }}</td>
                      <td style="float:left">
                        <select name="jenis" id="jenis" class="form-control">
                            <option value=""></option>
                            @if($jenis != '')
                              @foreach($jenis as $key)
                                  <option value="{{ $key->id }}">{{ $key->nama }}</option>
                              @endforeach
                            @endif
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.kode') }}</td>
                      <td>
                        <input type="text" class="form-control" autocomplete="off" id="kode" name="kode" maxlength="20">
                      </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="padding-left:0">
                            <table>
                                <tr>
                                    <td><input onclick="checkboxclick('harilibur')" checked type="checkbox" id="harilibur" name="harilibur"></td>
                                    <td><span onclick="spanclick('harilibur')">{{ trans('all.harilibur') }}</span></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" checked id="setiaphari" onclick="_checkboxclick('setiaphari',false,'flaghari')" name="setiaphari"></td>
                                    <td><span class="setiaphari" onclick="_spanclick('setiaphari',false,'flaghari')">{{ trans('all.berlakusetiaphari') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('senin')" type="checkbox" id="senin" name="senin"></td>
                                    <td><span onclick="spanclick('senin')">{{ trans('all.senin') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('selasa')" type="checkbox" id="selasa" name="selasa"></td>
                                    <td><span onclick="spanclick('selasa')">{{ trans('all.selasa') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('rabu')" type="checkbox" id="rabu" name="rabu"></td>
                                    <td><span onclick="spanclick('rabu')">{{ trans('all.rabu') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('kamis')" type="checkbox" id="kamis" name="kamis"></td>
                                    <td><span onclick="spanclick('kamis')">{{ trans('all.kamis') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('jumat')" type="checkbox" id="jumat" name="jumat"></td>
                                    <td><span onclick="spanclick('jumat')">{{ trans('all.jumat') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('sabtu')" type="checkbox" id="sabtu" name="sabtu"></td>
                                    <td><span onclick="spanclick('sabtu')">{{ trans('all.sabtu') }}</span></td>
                                </tr>
                                <tr class="flaghari">
                                    <td><input onclick="checkboxclick('minggu')" type="checkbox" id="minggu" name="minggu"></td>
                                    <td><span onclick="spanclick('minggu')">{{ trans('all.minggu') }}</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.digunakan') }}</td>
                      <td style="float:left">
                        <select class="form-control" id="digunakan" name="digunakan">
                          <option value=""></option>
                          <option value="y">{{ trans('all.ya') }}</option>
                          <option value="t">{{ trans('all.tidak') }}</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.urutan') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" onkeypress="return onlyNumber(0,event)" size="4" name="urutan" autocomplete="off" id="urutan" maxlength="3">
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                      <td>
                        <table width=100% id='tabShift'></table>
                        <table>
                          <tr>
                            <td style='padding:0px;'>
                              <a id="tambahshift" title="{{ trans('all.tambahdetail') }}" onclick='addShift()' class="btn btn-success"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;{{ trans('all.tambahdetail') }}</a>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td>
                          <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                        <button type="button" id="kembali" onclick="return ke('../shift')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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