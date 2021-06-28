<script>
function _checkboxclickModal(libur,param,jadwalshift,tanggal,dari){
    if(libur == 'ya'){
        if($("#"+param+dari).prop('checked')){
            $("#"+param+dari).prop('checked', true);
            $('.'+jadwalshift+dari).css('display','none');
        }else{
            $("#"+param+dari).prop('checked', false);
            $('.'+jadwalshift+dari).css('display','');
        }
        $(".checkboxjadwalshift_"+tanggal+dari).prop('checked', false);
    }else{
        if($("#"+param+dari).prop('checked')){
            $("#"+param+dari).prop('checked', true);
        }else{
            $("#"+param+dari).prop('checked', false);
        }
    }
}

function _spanclickModal(libur,param,jadwalshift,tanggal,dari){
    if(!$('#'+param+dari).is('[disabled=disabled]'))
    {
        if(libur == 'ya'){
            if ($("#" + param+dari).prop('checked')) {
                $("#" + param+dari).prop('checked', false);
                $('.' + jadwalshift+dari).css('display', '');
            } else {
                $("#" + param+dari).prop('checked', true);
                $('.' + jadwalshift+dari).css('display', 'none');
            }
            $(".checkboxjadwalshift_"+tanggal+dari).prop('checked', false);
        }else {
            if ($("#" + param+dari).prop('checked')) {
                $("#" + param+dari).prop('checked', false);
            } else {
                $("#" + param+dari).prop('checked', true);
            }
        }
    }
}

function tampilsemuajamkerjashift(i,dari){
    $('#tampilsemuajamkerjashift_'+i+dari).remove();
    $('.itemjamkerjashift_'+i+dari).css('display', '');
}

//cek jika ada yg off(libur)
@for($i=0;$i<count($data);$i++)
    @if($data[$i]['jenis'] == 'shift')
        @for($j=0;$j<count($data[$i]['shift']);$j++)
            @if($data[$i]['shift'][$j]['idjamkerjashift'] == null)
                @if($data[$i]['shift'][$j]['dijadwalkan'] == 1)
                    $(function(){
                        $("#jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'].'_'.$dari }}").trigger('click');
                        //setTimeout(function(){ $("#jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'].'_'.$dari }}").trigger('click'); },10);
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
</style>
<div class="ibox float-e-margins">
    <div class="ibox-content" style="white-space: nowrap;overflow: auto;">
        <input type="hidden" name="totalhari_{{ $dari }}" value="{{ count($data) }}">
        <b><i>{{ $namapegawai.' ('.trans('all.pin').' : '.$pinpegawai.')' }}</i></b>
        <br>
        <b>{{ $periode }}</b>
        <br>
        @for($i=0;$i<count($data);$i++)
            <table>
                <tr style="@if($data[$i]['idijintidakmasuk'] != null) color:#fff;background-color:#f8ac59;  @endif @if($data[$i]['harilibur'] == 'y') background-color: rgb(221, 107, 85);color:#fff; @endif">
                    <td style="width:80px !important;">
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
                                        {{ trans('all.tidakadajadwal') }}
                                    </td>
                                @else
                                    @if($data[$i]['idjamkerja'] != null && $data[$i]['jenis'] == 'full')
                                        {{ trans('all.jamkerjafull').' : '.$data[$i]['nama'] }}
                                    @else
                                        @if($data[$i]['jenis'] == 'shift')
                                            @for($j=0;$j<count($data[$i]['shift']);$j++)
                                                @if($data[$i]['shift'][$j]['idjamkerjashift'] == null)
                                                    <td class="itemjamkerjashift">
                                                        <input type="checkbox" name="jadwalshift_{{ $data[$i]['hanyatanggal'].'_'.$dari }}[]" id="jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'].'_'.$dari }}" value="" onclick="_checkboxclickModal('ya','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['hanyatanggal'] }}','_{{ $dari }}')">&nbsp;
                                                        <span class="checkboxspan" onclick="_spanclickModal('ya','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['hanyatanggal'] }}','_{{ $dari }}')">
                                                            {{ trans('all.off') }}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td class="jadwalshift_{{ $i.'_'.$dari }}" title="{{ $data[$i]['nama'] }}">
                                                        <div class="itemjamkerjashift_{{ $i.'_'.$dari }}" style="@if (
                                                                                                    ($data[$i]['harilibur']=='y' && $data[$i]['shift'][$j]['tampillibur']=='y') ||
                                                                                                    ($data[$i]['harilibur']=='t' && $data[$i]['shift'][$j]['tampilharian']=='y') ||
                                                                                                    ($data[$i]['shift'][$j]['dijadwalkan']=='1')
                                                                                                 )

                                                                                            @else
                                                                                                    display: none;
                                                                                            @endif">
                                                            <input class="checkboxjadwalshift_{{ $data[$i]['hanyatanggal'].'_'.$dari }}" @if($data[$i]['shift'][$j]['dijadwalkan'] == 1) checked @endif type="checkbox" name="jadwalshift_{{ $data[$i]['hanyatanggal'].'_'.$dari }}[]" id="jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'].'_'.$dari }}" value="{{ $data[$i]['shift'][$j]['idjamkerjashift'] }}" onclick="_checkboxclickModal('tidak','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['hanyatanggal'] }}','_{{ $dari }}')">&nbsp;
                                                            <span class="checkboxspan" onclick="_spanclickModal('tidak','jadwalshift_{{ $i.'_'.$data[$i]['shift'][$j]['idjamkerjashift'] }}','jadwalshift_{{ $i }}','{{ $data[$i]['hanyatanggal'] }}','_{{ $dari }}')">
                                                                {{ $data[$i]['shift'][$j]['namashift'] == '' ? trans('all.off') : $data[$i]['shift'][$j]['namashift'] }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                @endif
                                            @endfor
                                            @if($data[$i]['tampilsemua']=='t')
                                                <td style="cursor:pointer;" title="{{ trans('all.selengkapnya') }}" class="jadwalshift_{{ $i.'_'.$dari }}" id="tampilsemuajamkerjashift_{{ $i.'_'.$dari }}">
                                                    <i onclick="tampilsemuajamkerjashift({{ $i }},'_{{ $dari }}')" class="fa fa-ellipsis-h"></i>
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
    </div>
</div>