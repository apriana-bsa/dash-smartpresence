
<style>
    .tdmodalDP{
        padding:5px;
    }

    /*.modal-content{
        width:1020px;
    }*/
</style>
<div class="modal-header">
    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
    <h4 class="modal-title">{{ trans('all.voice').' ('.$namapegawai.')' }}</h4>
</div>
<div class="modal-body body-modal row" id="moredetail">
    <table>
        <tr>
            <td class="tdmodalDP">
                <button class="btn btn-primary" onclick="return dengarkan({{ Session::get('conf_webperusahaan') }},{{ $idpegawai }})"><i class="fa fa-play"></i>&nbsp;&nbsp;{{ trans('all.dengarkan') }}</button>
            </td>
        </tr>
        @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
            <tr>
                <td class="tdmodalDP">
                    <button class="btn btn-primary" onclick="return buatUlang({{ $idpegawai }})"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.buatulang') }}</button>
                </td>
            </tr>
        @endif
    </table>
</div>
<div class="modal-footer">
    <table width="100%">
        <tr>
            <td style="padding:0;align:right">
                <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
            </td>
        </tr>
    </table>
</div>
<script>
function dengarkan(idperusahaan,idpegawai){
    var audioElement = document.createElement('audio');
    audioElement.setAttribute('src', 'http://dash.smartpresence.id:9321/v321/etc/playvoice/'+idperusahaan+'/'+idpegawai);
    audioElement.play();
}

function buatUlang(idpegawai){
    window.location.href='{{ url('datainduk/pegawai/pegawai/buatulangvoice') }}/'+idpegawai;
}
</script>