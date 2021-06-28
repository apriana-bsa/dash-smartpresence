<script>

function _checkboxclickModal(libur,param,jadwalshift){
    if(libur == 'ya'){
        if($("#"+param).prop('checked')){
            $("#"+param).prop('checked', true);
            $('.'+jadwalshift).css('display','none');
        }else{
            $("#"+param).prop('checked', false);
            $('.'+jadwalshift).css('display','');
        }
        $(".checkboxjadwalshift").prop('checked', false);
    }else{
        if($("#"+param).prop('checked')){
            $("#"+param).prop('checked', true);
        }else{
            $("#"+param).prop('checked', false);
        }
    }
}

function _spanclickModal(libur,param,jadwalshift){
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
            $(".checkboxjadwalshift").prop('checked', false);
        }else {
            if ($("#" + param).prop('checked')) {
                $("#" + param).prop('checked', false);
            } else {
                $("#" + param).prop('checked', true);
            }
        }
    }
}

//cek jika ada yg off(libur)
@for($i=0;$i<count($jadwalshift);$i++)
    @if($jadwalshift[$i]['idjamkerjashift'] == null)
        @if($jadwalshift[$i]['dijadwalkan'] == 1)
            $(function(){
                setTimeout(function(){ $("#jadwalshift_{{ $i.'_'.$jadwalshift[$i]['idjamkerjashift'] }}").trigger('click'); },10);
            });
        @endif
    @endif
@endfor
</script>
<style>
.checkboxspan{
    cursor:pointer;
}
</style>
<div class="modal-header">
    <button type="button" class="close" id='closemodal2' data-dismiss="modal">&times;</button>
    <h4 class="modal-title">{{ trans('all.jadwalshift') }}</h4>
</div>
<form id="form1" method="post" action="{{ url('datainduk/absensi/jadwalshift/submit') }}">
    {{ csrf_field() }}
    <input type="hidden" value="{{ $idpegawai }}" name="idpegawai">
    <input type="hidden" value="{{ $tanggal }}" name="tanggal">
    <div class="modal-body" style="padding:15px">
        <b><i>{{ $pegawai }}</i></b>
        <br>
        <b>{{ $tanggallengkap }}</b>
        <br>
        <i>{{ trans('all.jamkerja').' : '.$jamkerja }}</i>
        <br>
        <table width="100%" id="tabJadwalShift">
            @for($i=0;$i<count($jadwalshift);$i++)
                <tr>
                    <td title="{{ $jadwalshift[$i]['namashift'] }}">
                        @if($jadwalshift[$i]['idjamkerjashift'] == null)
                            <input type="checkbox" name="jadwalshift[]" id="jadwalshift_{{ $i.'_'.$jadwalshift[$i]['idjamkerjashift'] }}" value="" onclick="_checkboxclickModal('ya','jadwalshift_{{ $i.'_'.$jadwalshift[$i]['idjamkerjashift'] }}','jadwalshift')">&nbsp;
                            <span class="checkboxspan" onclick="_spanclickModal('ya','jadwalshift_{{ $i.'_'.$jadwalshift[$i]['idjamkerjashift'] }}','jadwalshift')">
                                {{ trans('all.off') }}
                            </span>
                        @else
                            <div class="jadwalshift" style="">
                                <input class="checkboxjadwalshift" @if($jadwalshift[$i]['dijadwalkan'] == 1) checked @endif type="checkbox" name="jadwalshift[]" id="jadwalshift_{{ $i.'_'.$jadwalshift[$i]['idjamkerjashift'] }}" value="{{ $jadwalshift[$i]['idjamkerjashift'] }}" onclick="_checkboxclickModal('tidak','jadwalshift_{{ $i.'_'.$jadwalshift[$i]['idjamkerjashift'] }}','')">&nbsp;
                                <span class="checkboxspan" onclick="_spanclickModal('tidak','jadwalshift_{{ $i.'_'.$jadwalshift[$i]['idjamkerjashift'] }}','')">
                                    {{ $jadwalshift[$i]['namashift'] == '' ? trans('all.off') : $jadwalshift[$i]['namashift'] }}
                                </span>
                            </div>
                        @endif
                    </td>
                </tr>
            @endfor
        </table>
    </div>
    <div class="modal-footer">
        <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>
        <button class="btn btn-primary" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
    </div>
</form>