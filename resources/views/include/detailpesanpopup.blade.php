<style>
    .tdmodalDP{
        padding:3px;
    }
</style>
@if($totaldata > 0)
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ trans('all.detailpesan') }}</h4>
        </div>
        <div class="modal-body body-modal">
            <table>
                <tr>
                    <td class="tdmodalDP"><b>{{ \App\Utils::tanggalCantik($data->tanggal,"panjang").' '.$data->jam }}</b></td>
                </tr>
                <tr>
                    <td class="tdmodalDP">{{ $data->pesan }}</td>
                </tr>
            </table>
        </div>
        <div class="modal-footer">
            <table width="100%">
                <tr>
                    <td align="right" style="padding:0">
                        <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endif