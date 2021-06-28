@extends('layouts.master')
@section('title', trans('all.jadwalshift'))
@section('content')

    @if(Session::get('message'))
        <script>
            $(document).ready(function() {
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
            });
        </script>
    @endif
    <script>
        function _checkboxclickModal(libur,param,jadwalshift,tanggal){
            if(libur == 'ya'){
                if($("#"+param).prop('checked')){
                    $("#"+param).prop('checked', true);
                    $('.'+jadwalshift).css('display','none');
                }else{
                    $("#"+param).prop('checked', false);
                    $('.'+jadwalshift).css('display','');
                }
                $(".checkboxjadwalshift_"+tanggal).prop('checked', false);
            }else{
                if($("#"+param).prop('checked')){
                    $("#"+param).prop('checked', true);
                }else{
                    $("#"+param).prop('checked', false);
                }
            }
        }

        function _spanclickModal(libur,param,jadwalshift,tanggal){
            if(!$('#'+param).is('[disabled=disabled]'))
            {
                if(libur == 'ya'){
                    if ($("#" + param).prop('checked')) {
                        $("#" + param).prop('checked', false);
                        $('.' + jadwalshift).css('display', '');
                    } else {
                        $("#" + param).prop('checked', true);
                        $('.' + jadwalshift).css('display', 'none');
                    }
                    $(".checkboxjadwalshift_"+tanggal).prop('checked', false);
                }else {
                    if ($("#" + param).prop('checked')) {
                        $("#" + param).prop('checked', false);
                    } else {
                        $("#" + param).prop('checked', true);
                    }
                }
            }
        }

        function tampilsemuajamkerjashift(i){
            $('#tampilsemuajamkerjashift_'+i).remove();
            $('.itemjamkerjashift_'+i).css('display', '');
        }

        //cek jika ada yg off(libur)
        @for($i=0;$i<count($data);$i++)
            @if($data[$i]['jenis'] == 'shift')
                @for($j=0;$j<count($data[$i]['shift']);$j++)
                    @if($data[$i]['shift'][$j]['idjamkerjashift'] == null)
                        @if($data[$i]['shift'][$j]['dijadwalkan'] == 1)

                        $(function(){
                            setTimeout(function(){ $("#jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}").trigger('click'); },10);
                        });
                        @endif
                    @endif
                 @endfor
            @endif
        @endfor
    </script>
    <style>
        .checkboxspan{
            cursor:pointer;
        }

        td{
            padding:5px;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ trans('all.jadwalshift') }}</h2>
            <ol class="breadcrumb">
                <li>{{ trans('all.datainduk') }}</li>
                <li>{{ trans('all.absensi') }}</li>
                <li class="active"><strong>{{ trans('all.jadwalshift') }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">

        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content" style="overflow: auto;white-space: nowrap">
                        <form id="form1" method="post" action="{{ url('datainduk/absensi/jadwalshiftperbulan/'.$idpegawai) }}">
                            {{ csrf_field() }}
                            <input type="hidden" name="totalhari" value="{{ count($data) }}">
                            <b><i>{{ $namapegawai.' ('.trans('all.pin').' : '.$pinpegawai.')' }}</i></b>
                            <br>
                            <b>{{ $periode }}</b>
                            <br>
                            @for($i=0;$i<count($data);$i++)
                                <table>
                                    <tr style="@if($data[$i]['idijintidakmasuk'] != null) color:#fff;background-color:#f8ac59;  @endif @if($data[$i]['harilibur'] == 'y') background-color: rgb(221, 107, 85);color:#fff; @endif">
                                        <td>
                                            {{ $i+1 }}{{ ', '.$data[$i]['hari'] }}
                                        </td>
                                        <td><i class="fa fa-arrow-right"></i></td>
                                        <td style="padding:0;">
                                            <table>
                                                <tr>
                                                    @if($data[$i]['idijintidakmasuk'] != null)
                                                        <td>
                                                            {{ trans('all.ijintidakmasuk').' '.trans('all.alasan').': '.$data[$i]['ijintidakmasuk'].' '.trans('all.keterangan').': '.$data[$i]['ijintidakmasukketerangan'] }}
                                                        </td>
                                                    @elseif($data[$i]['idjamkerja'] == null)
                                                        <td>
                                                            {{ trans('all.tidakadajamkerja') }}
                                                        </td>
                                                    @else
                                                        @if($data[$i]['idjamkerja'] != null && $data[$i]['jenis'] == 'full')
                                                            {{ trans('all.jamkerjafull').' : '.$data[$i]['nama'] }}
                                                        @else
                                                            @if($data[$i]['jenis'] == 'shift')
                                                                @for($j=0;$j<count($data[$i]['shift']);$j++)
                                                                    @if($data[$i]['shift'][$j]['idjamkerjashift'] == null)
                                                                        <td class="itemjamkerjashift">
                                                                            <input type="checkbox" name="jadwalshift_{{ $data[$i]['hanyatanggal'] }}[]" id="jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}" value="" onclick="_checkboxclickModal('ya','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['hanyatanggal'] }}')">&nbsp;
                                                                            <span class="checkboxspan" onclick="_spanclickModal('ya','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['hanyatanggal'] }}')">
                                                        {{ trans('all.off') }}
                                                    </span>
                                                                        </td>
                                                                    @else
                                                                        <td class="jadwalshift_{{ $i }}" title="{{ $data[$i]['nama'] }}">
                                                                            <div class="itemjamkerjashift_{{ $i }}" style="@if (
                                                                                                ($data[$i]['harilibur']=='y' && $data[$i]['shift'][$j]['tampillibur']=='y') ||
                                                                                                ($data[$i]['harilibur']=='t' && $data[$i]['shift'][$j]['tampilharian']=='y') ||
                                                                                                ($data[$i]['shift'][$j]['dijadwalkan']=='1')
                                                                                             )

                                                                            @else
                                                                                    display: none;
                                                                            @endif">
                                                                                <input class="checkboxjadwalshift_{{ $data[$i]['hanyatanggal'] }}" @if($data[$i]['shift'][$j]['dijadwalkan'] == 1) checked @endif type="checkbox" name="jadwalshift_{{ $data[$i]['hanyatanggal'] }}[]" id="jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}" value="{{ $data[$i]['shift'][$j]['idjamkerjashift'] }}" onclick="_checkboxclickModal('tidak','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['hanyatanggal'] }}')">&nbsp;
                                                                                <span class="checkboxspan" onclick="_spanclickModal('tidak','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['hanyatanggal'] }}')">
                                                            {{ $data[$i]['shift'][$j]['namashift'] == '' ? trans('all.off') : $data[$i]['shift'][$j]['namashift'] }}
                                                        </span>
                                                                            </div>
                                                                        </td>
                                                                    @endif
                                                                @endfor
                                                                @if($data[$i]['tampilsemua']=='t')
                                                                    <td style="cursor:pointer;" title="{{ trans('all.selengkapnya') }}" class="jadwalshift_{{ $i }}" id="tampilsemuajamkerjashift_{{ $i }}">
                                                                        <i onclick="tampilsemuajamkerjashift({{ $i }})" class="fa fa-ellipsis-h"></i>
                                                                    </td>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    @endif
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @endfor
                            <table width="100%">
                                <tr>
                                    <td valign="top" style="text-align: left">
                                        @if($legend_harilibur == 'y')
                                            <table style="margin-top:-5px">
                                                <tr>
                                                    <td><div style="width:10px;height:10px;background: rgb(221, 107, 85);"></div></td>
                                                    <td> {{ trans('all.harilibur') }}</td>
                                                </tr>
                                            </table>
                                        @endif
                                        @if($legend_ijintidakmasuk == 'y')
                                            <table style="margin-top:-10px">
                                                <tr>
                                                    <td><div style="width:10px;height:10px;background: #f8ac59;"></div></td>
                                                    <td> {{ trans('all.ijintidakmasuk') }}</td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>
                                        <button class="btn btn-primary" type="button" onclick="ke('{{ url('datainduk/absensi/jadwalshift') }}')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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