<style>
.tdmodalDP{
    padding:3px;
}
</style>
<!-- Modal content-->
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{ trans('all.detailpegawai') }}</h4>
    </div>
    <div class="modal-body body-modal">
        <table>
            <tr>
                <td class="tdmodalDP">
                    <a href="{{ url('fotonormal/pegawai/'.$data->id) }}" title="{{ $data->nama }}" data-gallery="">
                        <img id="imgInp" width=120 style='border-radius: 50%;' height=120 src="{{ url('foto/pegawai/'.$data->id) }}">
                    </a>
                </td>
                <td valign="top" class="tdmodalDP">
                    <table>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.nama') }}</td>
                            <td class="tdmodalDP">:</td>
                            <td class="tdmodalDP"><b>{{ $data->nama }}</b></td>
                        </tr>
                        @if($data->nomorhp != '')
                            <tr>
                                <td class="tdmodalDP">{{ trans('all.nomorhp') }}</td>
                                <td class="tdmodalDP">:</td>
                                <td class="tdmodalDP">{{ $data->nomorhp }}</td>
                            </tr>
                        @endif
                        @if($data->pin != '')
                            <tr>
                                <td class="tdmodalDP">{{ trans('all.pin') }}</td>
                                <td class="tdmodalDP">:</td>
                                <td class="tdmodalDP">{{ $data->pin }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.status') }}</td>
                            <td class="tdmodalDP">:</td>
                            <td class="tdmodalDP"><span class="label @if($data->status == trans('all.aktif')) label-primary @else label-danger @endif">{{ $data->status }}</span></td>
                        </tr>
                        @if($data->jamkerja != '')
                            <tr>
                                <td class="tdmodalDP">{{ trans('all.jamkerja') }}</td>
                                <td class="tdmodalDP">:</td>
                                <td class="tdmodalDP">{{ $data->jamkerja }}</td>
                            </tr>
                        @endif
                    </table>
                    <div id="datatambahan"></div>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td class="tdmodalDP">
                    {!! str_replace('; ', '<br>', $data->atributpegawai) !!}
                    @if($lokasipegawai->lokasi != '')
                        {{ trans('all.lokasi').' : '.$lokasipegawai->lokasi }}
                    @endif
                </td>
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