<style>
.tdmodalDP{
    padding:3px;
}
</style>
<!-- Modal content-->
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{ trans('all.logabsen') }}</h4>
    </div>
    <div class="modal-body body-modal">
        <table>
            <tr>
                <td class="tdmodalDP" style="width:140px">
                    <a href="{{ url('fotologabsen/'.$data->id) }}" title="{{ $data->pegawai }}" data-gallery="">
                        <img id="imgInp" width=120 style='border-radius: 50%;' height=120 src="{{ url('fotologabsen/'.$data->id.'/thumb') }}">
                    </a>
                </td>
                <td valign="top" class="tdmodalDP">
                    <table>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.nama') }}</td>
                            <td class="tdmodalDP">:</td>
                            <td class="tdmodalDP"><b>{{ $data->pegawai }}</b></td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.mesin') }}</td>
                            <td class="tdmodalDP">:</td>
                            <td class="tdmodalDP">{{ $data->mesin }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.masukkeluar') }}</td>
                            <td class="tdmodalDP">:</td>
                            <td class="tdmodalDP">{{ $data->masukkeluar == 'm' ? trans('all.masuk') : trans('all.keluar') }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.status') }}</td>
                            <td class="tdmodalDP">:</td>
                            <td class="tdmodalDP">{{ $data->status == 'v' ? trans('all.valid') : ($data->status == 'c' ? trans('all.konfirmasi') : ($data->status == 'na' ? trans('all.ditolak') : '')) }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.terhitungkerja') }}</td>
                            <td class="tdmodalDP">:</td>
                            <td class="tdmodalDP">{{ $data->terhitungkerja == 'y' ? trans('all.ya') : trans('all.tidak') }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.sumber') }}</td>
                            <td class="tdmodalDP">:</td>
                            <td class="tdmodalDP">{{ trans('all.'.$data->sumber) }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.waktu') }}</td>
                            <td class="tdmodalDP">:</td>
                            <td class="tdmodalDP"><b>{{ \App\Utils::tanggalCantik($data->waktu,'full').' '.substr($data->waktu, 11) }}</b></td>
                        </tr>
                    </table>
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