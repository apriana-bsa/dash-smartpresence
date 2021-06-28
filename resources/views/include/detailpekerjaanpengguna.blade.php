<!-- Modal content-->
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{ trans('all.pekerjaan').' ('.\App\Utils::getNamaPegawai($idpegawai).')' }}</h4>
    </div>
    <div class="modal-body body-modal">
        <table width=100% id="tabledetailpekerjaanpengguna" class="table datatable table-striped table-condensed table-hover">
            <thead>
            <tr>
                <td class="alamat"><b>{{ trans('all.tanggal') }}</b></td>
                {{--<td class="opsi5"><b>{{ trans('all.pengguna') }}</b></td>--}}
                {{--<td class="opsi5"><b>{{ trans('all.pekerjaan') }}</b></td>--}}
                <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                <td class="keterangan"><b>{{ trans('all.jumlah') }}</b></td>
            </tr>
            </thead>
            <tbody>
            @if(count($data) != '')
                @foreach($data as $key)
                    <tr>
                        <td>{{ \App\Utils::tanggalCantik($key->tanggal,"panjang") }}</td>
                        {{--<td>{{ \App\Utils::getDataSelectedUniversal('nonperusahaan','nama','user',$key->iduser) }}</td>--}}
                        {{--<td>{{ $key->pekerjaan }}</td>--}}
                        {{--<td>{{ $key->keterangan }}</td>--}}
                        <td>
                            {{ trans('all.pengguna').' : '.\App\Utils::getDataSelectedUniversal('nonperusahaan','nama','user',$key->iduser) }}<br>
                            {{ trans('all.pekerjaan').' : '.$key->pekerjaan }}<br>
                            {{ trans('all.keterangan').' : '.$key->keterangan }}
                        </td>
                        <td>{!! \App\Utils::getItemPekerjaan($key->idpekerjaaninput) !!}</td>
                    </tr>
                @endforeach
            @endif
            </tbody>
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

<script>
$(function() {
    setTimeout(function(){ $('#tabledetailpekerjaanpengguna').resize(); },100);

    $('#tabledetailpekerjaanpengguna').DataTable({
        processing: true,
        bStateSave: true,
        scrollX: true,
        columnDefs: [
            { "orderable": false, "searchable": true, "targets": 0 },
//            { "orderable": false, "searchable": true, "targets": 3 }
        ],
        order: [[1, 'asc']]
    });
});
</script>