@extends('layouts.master')
@section('title', trans('all.ekspor'))
@section('content')

	<style>
	td{
		padding:5px;
	}
	</style>
	<script>
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
                toastr.success('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
            }, 500);
        @endif

        filterMode();
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });
        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
    });

    function validasi(){
        var kelompok = $('#kelompok').val();
        if(kelompok == ''){
            alertWarning("{{ trans('all.kelompok').' '.trans('all.sa_kosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#kelompok'))
                });
            return false;
        }
    }

    function filterMode(){
        $('#jangkauantanggal').css('display', 'none');
        $('#periode').css('display', 'none');
        var filtermode = $('#filtermode').val();
        $('#'+filtermode).css('display', '');
    }

	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.ekspor') }}</h2>
        <ol class="breadcrumb">
            <li>{{ trans('all.laporan') }}</li>
            <li>{{ trans('all.custom') }}</li>
            <li class="active"><strong>{{ trans('all.ekspor') }}</strong></li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <form name="form1" method="post" action="" onsubmit="return validasi()">
            {{ csrf_field() }}
            <table>
                <tr>
                    <td>{{ trans('all.filtertanggal') }}</td>
                    <td style="float:left">
                        <select id="filtermode" name="filtermode" class="form-control" onchange="return filterMode()">
                            <option value="jangkauantanggal">{{ trans('all.jangkauantanggal') }}</option>
                            <option value="periode">{{ trans('all.periode') }}</option>
                        </select>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>{{ trans('all.kelompok') }}</td>
                    <td>
                        <select class="form-control" id="kelompok" name="kelompok">
                            <option value=""></option>
                            @if($datakelompok != '')
                                @foreach($datakelompok as $key)
                                    <option value="{{ $key->id }}">{{ $key->nama }}</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                    <td id="jangkauantanggal" colspan="2" style="padding:0;display:none">
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
                    <td id="periode" colspan="2" style="padding:0;display:none">
                        <table>
                            <tr>
                                <td style="float:left;margin-top:8px">{{ trans('all.periode') }}</td>
                                <td style="float:left">
                                    <select class="form-control" id="bulan" name="bulan">
                                        <option value="01">{{ trans('all.januari') }}</option>
                                        <option value="02">{{ trans('all.februari') }}</option>
                                        <option value="03">{{ trans('all.maret') }}</option>
                                        <option value="04">{{ trans('all.april') }}</option>
                                        <option value="05">{{ trans('all.mei') }}</option>
                                        <option value="06">{{ trans('all.juni') }}</option>
                                        <option value="07">{{ trans('all.juli') }}</option>
                                        <option value="08">{{ trans('all.agustus') }}</option>
                                        <option value="09">{{ trans('all.september') }}</option>
                                        <option value="10">{{ trans('all.oktober') }}</option>
                                        <option value="11">{{ trans('all.november') }}</option>
                                        <option value="12">{{ trans('all.desember') }}</option>
                                    </select>
                                </td>
                                <td style="float:left">
                                    <select class="form-control" id="tahun" name="tahun">
                                        <option value="{{ $tahun->tahun1 }}">{{ $tahun->tahun1 }}</option>
                                        <option value="{{ $tahun->tahun2 }}">{{ $tahun->tahun2 }}</option>
                                        <option value="{{ $tahun->tahun3 }}">{{ $tahun->tahun3 }}</option>
                                        <option value="{{ $tahun->tahun4 }}">{{ $tahun->tahun4 }}</option>
                                        <option value="{{ $tahun->tahun5 }}">{{ $tahun->tahun5 }}</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                    </td>
                </tr>
            </table>
            @if(count($dataatribut) > 0)
                <p><b>{{ trans('all.atribut') }}</b></p>
            @endif
            @foreach($dataatribut as $atribut)
                @if(count($atribut->atributnilai) > 0)
                    <div class="col-md-4">
                        <input type="checkbox" id="semuaatribut_{{ $atribut->id }}" onclick="checkboxallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">&nbsp;&nbsp;
                        <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">{{ $atribut->atribut }}</span>
                        <br>
                        @foreach($atribut->atributnilai as $atributnilai)
                            @if(Session::has('laplogabsen_atribut'))
                                {{ $checked = false }}
                                @for($i=0;$i<count(Session::get('laplogabsen_atribut'));$i++)
                                    @if($atributnilai->id == Session::get('laplogabsen_atribut')[$i])
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
        </form>
      </div>
    </div>
  </div>
@stop