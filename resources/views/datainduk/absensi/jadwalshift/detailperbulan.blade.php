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
function onScroll(el){
//    console.log(el.scrollLeft);
    $(".table_tanggal").css("left", el.scrollLeft);
}
</script>
<style>
.checkboxspan{
    cursor:pointer;
}
</style>
<div class="modal-header">
    <button type="button" class="close" id='closemodal2' data-dismiss="modal">&times;</button>
    <h4 class="modal-title">{{ trans('all.jadwalshiftperbulan') }}</h4>
</div>
<form id="form1" method="post" action="{{ url('datainduk/absensi/jadwalshiftperbulan/'.$idpegawai) }}">
    {{ csrf_field() }}
    <input type="hidden" name="totalhari" value="{{ count($data) }}">
    {{--<div class="modal-body" style="padding:15px;max-height:480px;overflow: auto;white-space: nowrap">--}}
    <div class="modal-body" style="padding:0;margin:15px;margin-right:0;height:480px;overflow: auto;white-space: nowrap" onscroll="onScroll(this)">
        <div class="table_tanggal" style="position: absolute;top:0;left:0; z-index: 2">
            <b><i>{{ $namapegawai.' ('.trans('all.pin').' : '.$pinpegawai.')' }}</i></b>
            <br>
            <b>{{ $keterangan }}</b>
            <br>
        </div>
        <div style="position: absolute;padding-top: 48px;">
            <table class="table_tanggal" style="background-color:white; position: absolute;left:0; z-index: 2">
                @for($i=0;$i<count($data);$i++)
                <tr style="@if($data[$i]['idijintidakmasuk'] != null && $data[$i]['shift'][0]['dijadwalkan'] == 0) color:#fff;background-color:#f8ac59;  @endif @if($data[$i]['harilibur'] == 'y') background-color: rgb(221, 107, 85);color:#fff; @elseif($data[$i]['dayinweek'] == 1) background-color: #ddd;color:#fff; @endif">
                    <td width="8px" height="35px">
                        {{--{{ $i+1 }},--}}
                        {{ $data[$i]['hanyatanggal'] }},
                    </td>
                    <td width="75px">
                        {{ $data[$i]['hari'] }}
                    </td>
                    <td><i class="fa fa-arrow-right"></i></td>
                </tr>
                @endfor
            </table>

            <table style="background-color: white; position: absolute;left:107px;z-index: 1">
                @for($i=0;$i<count($data);$i++)
                    <tr style="@if($data[$i]['idijintidakmasuk'] != null && $data[$i]['shift'][0]['dijadwalkan'] == 0) color:#fff;background-color:#f8ac59;  @endif @if($data[$i]['harilibur'] == 'y') background-color: rgb(221, 107, 85);color:#fff; @elseif($data[$i]['dayinweek'] == 1) background-color: #ddd;color:#fff; @endif">
                        <td height="35px" style="padding:0;">
                            <table>
                                <tr>
                                    {{--@if($data[$i]['idijintidakmasuk'] != null)--}}
                                        {{--<td>--}}
                                            {{--{{ trans('all.ijintidakmasuk').' '.trans('all.alasan').': '.$data[$i]['ijintidakmasuk'].' '.trans('all.keterangan').': '.$data[$i]['ijintidakmasukketerangan'] }}--}}
                                        {{--</td>--}}
                                    {{--@elseif($data[$i]['idjamkerja'] == null)--}}
                                    @if($data[$i]['idjamkerja'] == null)
                                        <td>{{ trans('all.tidakadajamkerja') }}</td>
                                    @else
                                        @if($data[$i]['idjamkerja'] != null && $data[$i]['jenis'] == 'full')
                                            @if($data[$i]['idijintidakmasuk'] != null)
                                                <td>{{ trans('all.ijintidakmasuk').' '.trans('all.alasan').': '.$data[$i]['ijintidakmasuk'].' '.trans('all.keterangan').': '.$data[$i]['ijintidakmasukketerangan'] }}</td>
                                            @else
                                                <td>{{ trans('all.jamkerjafull').' : '.$data[$i]['nama'] }}</td>
                                            @endif
                                        @else
                                            @if($data[$i]['jenis'] == 'shift')
                                                @if($data[$i]['idijintidakmasuk'] != null && $data[$i]['shift'][0]['dijadwalkan'] == 0)
                                                    <td>{{ trans('all.ijintidakmasuk').' '.trans('all.alasan').': '.$data[$i]['ijintidakmasuk'].' '.trans('all.keterangan').': '.$data[$i]['ijintidakmasukketerangan'] }}</td>
                                                @else
                                                    @for($j=0;$j<count($data[$i]['shift']);$j++)
                                                        @if($data[$i]['shift'][$j]['idjamkerjashift'] == null)
                                                            <td class="itemjamkerjashift">
                                                                <input type="checkbox" name="jadwalshift_{{ $data[$i]['tanggalymd'] }}[]" id="jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}" value="" onclick="_checkboxclickModal('ya','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['tanggalymd'] }}')"> <span class="checkboxspan" onclick="_spanclickModal('ya','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['tanggalymd'] }}')">{{ trans('all.off') }}</span>
                                                            </td>
                                                        @else
                                                            <td class="itemjamkerjashift_{{ $i }} jadwalshift_{{ $i }}" title="{{ $data[$i]['nama'] }}"  style="@if (
                                                                                                    ($data[$i]['harilibur']=='y' && $data[$i]['shift'][$j]['tampillibur']=='y') ||
                                                                                                    ($data[$i]['harilibur']=='t' && $data[$i]['shift'][$j]['tampilharian']=='y') ||
                                                                                                    ($data[$i]['shift'][$j]['dijadwalkan']=='1')
                                                                                                 )

                                                            @else
                                                                    display: none;
                                                            @endif">
                                                                <input class="checkboxjadwalshift_{{ $data[$i]['tanggalymd'] }}" @if($data[$i]['shift'][$j]['dijadwalkan'] == 1) checked @endif type="checkbox" name="jadwalshift_{{ $data[$i]['tanggalymd'] }}[]" id="jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}" value="{{ $data[$i]['shift'][$j]['idjamkerjashift'] }}" onclick="_checkboxclickModal('tidak','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['tanggalymd'] }}')"> <span class="checkboxspan" onclick="_spanclickModal('tidak','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['tanggalymd'] }}')">
                                                                    {{ $data[$i]['shift'][$j]['namashift'] == '' ? trans('all.off') : $data[$i]['shift'][$j]['namashift'] }}
                                                                </span>
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
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endfor
            </table>
        </div>

    </div>
    <div class="modal-footer">
        <table width="100%">
            <tr>
                <td valign="top" style="text-align: left">
                    @if($legend_harilibur == 'y')
                        <table style="margin-top:-5px">
                            <tr>
                                <td><div style="width:10px;height:10px;background: rgb(221, 107, 85);"></div></td>
                                <td> {{ trans('all.harilibur') }}</td>
                            </tr>
                            @if($dataharilibur != '')
                                @foreach($dataharilibur as $key)
                                    <tr>
                                        <td style="padding:0" colspan="2">{{ \App\Utils::tanggalCantikDariSampai($key->tanggalawal,$key->tanggalakhir).' '.$key->keterangan }}</td>
                                    </tr>
                                @endforeach
                            @endif
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
                <td>
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>
                    <button class="btn btn-primary" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                </td>
            </tr>
        </table>
    </div>
</form>