@extends('layouts.master')
@section('title', trans('all.postingdata'))
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
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });
        
        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
    });
    
	function validasi(){
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        
		var periode = $("#periode").val();
		
		if(periode == ""){
			alertWarning("{{ trans('all.periode').' '.trans('all.sa_kosong') }}",
            function() {
                  aktifkanTombol();
                  setFocus($('#periode'));
            });
            return false;
        }
        var dataString = 'periode='+periode+'&_token={{ csrf_token() }}';
		var adadata = false;
		//$.ajax({
		//	type: "POST",
		//	url: '{{ url('payrollpostingdata/checkperiode') }}',
		//	data: dataString,
		//	cache: false,
		//	async: false,
		//	success: function(html){
		//		if(html != ''){
        //          adadata = true;
		//		}
		//	}
		//});
		if(adadata == true){
            alertConfirmNotClose('{{ trans('all.datasudahada').",".trans('all.tetaplanjutkan')}}?',
                function(){
                    $('#submitform').trigger('click');
                },
                function(){
                    aktifkanTombol();
                }
            );
		}else{
            $('#submitform').trigger('click');
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
        <h2>{{ trans('all.postingdata') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.datainduk') }}</li>
            <li>{{ trans('all.payroll') }}</li>
            <li>{{ trans('all.postingdata') }}</li>
        <li class="active"><strong>{{ trans('all.postingdata') }}</strong></li>
        </ol>
    </div>
    <div class="col-lg-2"></div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <form action="{{ url('datainduk/payroll/payrollposting/generatepayroll') }}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <table width="100%">
                        <tr>
                            <td width="70px">{{ trans('all.periode') }}</td>
                            <td style="float:left">
                                <select name="periode" id="periode" class="form-control">
                                    @if($listyymm != '')
                                        @for($i=0;$i<count($listyymm);$i++)
                                            <option value="{{ $listyymm[$i]['isi'] }}" @if($listyymm[$i]['isi'] == date('ym')) selected @endif>{{ $listyymm[$i]['tampilan'] }}</option>
                                        @endfor
                                    @endif
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding:0">
                                <table width="100%">
                                    <tr>
                                        <td style="float:left;margin-top:8px">{{ trans('all.tanggal') }}</td>
                                        <td style="float:left">
                                            <input type="text" name="tanggalawal" size="11" id="tanggalawal" value="{{ $tanggal->tanggalawal }}" class="form-control date" placeholder="dd/mm/yyyy">
                                        </td>
                                        <td style="float:left;margin-top:8px">-</td>
                                        <td style="float:left">
                                            <input type="text" name="tanggalakhir" size="11" id="tanggalakhir" value="{{ $tanggal->tanggalakhir }}" class="form-control date" placeholder="dd/mm/yyyy">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <button id="submitform" type="submit" style="display:none"></button>
                                <button type="button" id="submit" onclick="return validasi()" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                <button type="button" id="kembali" onclick="return ke('../payrollposting')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                            </td>
                        </tr>
                    </table>
                    <div class="row">
                        @if(count($dataatribut) > 0)
                            <div class="col-md-12" style="margin-top:5px;"><p><b>{{ trans('all.atribut') }}</b></p></div>
                            @foreach($dataatribut as $atribut)
                                @if(count($atribut->atributnilai) > 0)
                                    <div class="col-md-4">
                                        <input type="checkbox" id="semuaatribut_{{ $atribut->id }}" onclick="checkboxallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">&nbsp;&nbsp;
                                        <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">{{ $atribut->atribut }}</span>
                                        <br>
                                        @foreach($atribut->atributnilai as $atributnilai)
                                            @if(Session::has('lapkehadiran_atribut'))
                                                {{ $checked = false }}
                                                @for($i=0;$i<count(Session::get('lapkehadiran_atribut'));$i++)
                                                    @if($atributnilai->id == Session::get('lapkehadiran_atribut')[$i])
                                                        <span style="display:none">{{ $checked = true }}</span>
                                                    @endif
                                                @endfor
                                                <div style="padding-left:15px">
                                                    <table>
                                                        <tr>
                                                            <td valign="top" style="width:10px;">
                                                                <input type="checkbox" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" id="atributnilai_{{ $atributnilai->id }}" @if($checked == true) checked @endif name="atributnilai[]" value="{{ $atributnilai->id }}">
                                                            </td>
                                                            <td valign="top">
                                                                <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            @else
                                                <div style="padding-left:15px">
                                                    <table>
                                                        <tr>
                                                            <td valign="top" style="width:10px;">
                                                                <input type="checkbox" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" id="atributnilai_{{ $atributnilai->id }}" name="atributnilai[]" value="{{ $atributnilai->id }}">
                                                            </td>
                                                            <td valign="top">
                                                                <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </form>
            </div>
        </div>
      </div>
    </div>
  </div>

@stop