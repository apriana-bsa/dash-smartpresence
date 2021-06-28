<style>
    .tdmodalDP{
        padding:3px;
    }
</style>
<!-- Modal content-->
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{ trans('all.rekapitulasi')." ($tanggal)" }}</h4>
    </div>
    <div class="modal-body body-modal">
        <ul class="nav nav-tabs" style="padding-bottom:0">
            <li class="active"><a data-toggle="tab" onclick="resizeDatatable('detailrekapabsenhasil')" href="#tabdetail-hasil">{{ trans('all.hasil') }}</a></li>
            <li><a data-toggle="tab" onclick="resizeDatatable('riwayat')" href="#tabdetail-jadwal">{{ trans('all.jadwal') }}</a></li>
            <li><a data-toggle="tab" onclick="resizeDatatable('ijintidakmasuk')" href="#tabdetail-logabsen">{{ trans('all.logabsen') }}</a></li>
        </ul>
        <p></p>
        <div class="tab-content">
            <div id="tabdetail-hasil" class="tab-pane active">
                <div class="full-height-scroll">
                    <table width=100% class="table table-responsive detailrekapabsenhasil table-striped table-condensed table-hover">
                        <thead>
                            <tr>
                                <td class="opsi5"><b>{{ trans('all.waktu') }}</b></td>
                                <td class="opsi1"><center><b>{{ trans('all.shift') }}</b></center></td>
                                <td class="opsi1"><center><b>{{ trans('all.masukkeluar') }}</b></center></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($datahasil as $key)
                                <tr>
                                    <td>{{ $key->waktu }}</td>
                                    <td>{{ $key->shift }}</td>
                                    <td><center>@if($key->masukkeluar=="m")<span class="label label-info">{{  trans('all.masuk') }}</span> @else <span class="label label-warning">{{ trans('all.keluar') }}</span> @endif</center></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="tabdetail-jadwal" class="tab-pane">
                <div class="full-height-scroll">
                    <table width=100% class="table table-responsive detailrekapabsenjadwal table-striped table-condensed table-hover">
                        <thead>
                            <tr>
                                <td class="opsi5"><b>{{ trans('all.waktu') }}</b></td>
                                <td class="opsi2"><center><b>{{ trans('all.masukkeluar') }}</b></center></td>
                                <td class="opsi2"><center><b>{{ trans('all.checking') }}</b></center></td>
                                <td class="opsi2"><center><b>{{ trans('all.jenis') }}</b></center></td>
                                <td class="opsi5"><center><b>{{ trans('all.shiftsambungan') }}</b></center></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($datajadwal as $key)
                                <tr>
                                    <td>{{ $key->waktu }}</td>
                                    <td><center>@if($key->masukkeluar=="m")<span class="label label-info">{{  trans('all.masuk') }}</span> @else <span class="label label-warning">{{ trans('all.keluar') }}</span> @endif</center></td>
                                    <td><center>{{ $key->checking }}</center></td>
                                    <td><center>{{ $key->shiftpertamaterakhir }}</center></td>
                                    <td><center>@if($key->shiftsambungan=="y")<span class="label label-info">{{  trans('all.ya') }}</span> @else <span class="label label-warning">{{ trans('all.tidak') }}</span> @endif</center></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="tabdetail-logabsen" class="tab-pane">
                <div class="full-height-scroll">
                    <table width=100% class="table table-responsive detailrekapabsenlogabsen table-striped table-condensed table-hover">
                        <thead>
                        <tr>
                            <td class="opsi5"><b>{{ trans('all.waktu') }}</b></td>
                            <td class="opsi2"><center><b>{{ trans('all.masukkeluar') }}</b></center></td>
                            <td class="opsi5"><b>{{ trans('all.alasan') }}</b></td>
                            <td class="opsi2"><center><b>{{ trans('all.terhitungkerja') }}</b></center></td>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($datalogabsen as $key)
                                <tr>
                                    <td>{{ $key->waktu }}</td>
                                    <td><center>@if($key->masukkeluar=="m")<span class="label label-info">{{  trans('all.masuk') }}</span> @else <span class="label label-warning">{{ trans('all.keluar') }}</span> @endif</center></td>
                                    <td>{{ $key->alasan }}</td>
                                    <td><center>@if($key->terhitungkerja=="y")<span class="label label-info">{{  trans('all.ya') }}</span> @else <span class="label label-warning">{{ trans('all.tidak') }}</span> @endif</center></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
    $('.detailrekapabsenhasil').DataTable({
        bStateSave: true
    });
    $('.detailrekapabsenhasil').resize();

    $('.detailrekapabsenjadwal').DataTable({
        bStateSave: true
    });
    $('.detailrekapabsenjadwal').resize();

    $('.detailrekapabsenlogabsen').DataTable({
        bStateSave: true
    });
    $('.detailrekapabsenlogabsen').resize();
});
</script>