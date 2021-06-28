<style>
.tdmodalDP{
    padding:3px;
}
</style>
<div class="modal-header">
    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
    <h4 class="modal-title">{{ trans('all.rekapabsen') }} {{ '('.$data['pegawai_nama'].')' }}</h4>
</div>
<ul class="nav nav-tabs" style="padding:10px;padding-bottom:0">
    <li class="active"><a data-toggle="tab" href="#tab-1">{{ trans('all.rekapitulasi') }}</a></li>
    <li class=""><a data-toggle="tab" href="#tab-2">{{ trans('all.jadwal') }}</a></li>
    <li class=""><a data-toggle="tab" href="#tab-3">{{ trans('all.kehadiran') }}</a></li>
    <li class=""><a data-toggle="tab" href="#tab-4">{{ trans('all.hasil') }}</a></li>
</ul>
<div class="modal-body body-modal row" style="max-height: 480px;overflow: auto;">
    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <div class="full-height-scroll">
                <div class="table-responsive">
                    <table width="100%">
                        <tr>
                            <td class="tdmodalDP" valign="top" colspan="2">
                                <a href="{{ url('fotonormal/pegawai/'.$data['idpegawai']) }}" title="{{ $data['pegawai_nama'] }}" data-gallery="">
                                    <center><img src="{{ url('foto/pegawai/'.$data['idpegawai']) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px"></center>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.nama') }}</td>
                            <td class="tdmodalDP">: {{ $data['pegawai_nama'] }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.pin') }}</td>
                            <td class="tdmodalDP">: {{ $data['pegawai_pin'] }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.tanggal') }}</td>
                            <td class="tdmodalDP">: {{ \App\Utils::tanggalCantik(date($data['tanggal'])) }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.masukkerja') }}</td>
                            <td class="tdmodalDP">: {{ $data['masukkerja'] }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.jumlahsesi') }}</td>
                            <td class="tdmodalDP">: {{ $data['jumlahsesi'] }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.jamkerja') }}</td>
                            <td class="tdmodalDP">: {{ $data['jamkerja'] }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.jamkerjakhusus') }}</td>
                            <td class="tdmodalDP">: {{ $data['jamkerjakhusus'] }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.jadwalmasukkerja') }}</td>
                            <td class="tdmodalDP">: {{ $data['jadwalmasukkerja'] }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.jenisjamkerja') }}</td>
                            <td class="tdmodalDP">: {{ $data['jenisjamkerja'] }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.jadwallamakerja') }}</td>
                            <td class="tdmodalDP">: {{ \App\Utils::sec2pretty($data['jadwallamakerja']) }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.waktumasuk') }}</td>
                            <td class="tdmodalDP">: {{ $data['waktumasuk'] == '' ? '-' : \App\Utils::tanggalCantik($data['waktumasuk']) }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.waktukeluar') }}</td>
                            <td class="tdmodalDP">: {{ $data['waktukeluar'] == '' ? '-' : \App\Utils::tanggalCantik($data['waktukeluar']) }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">@if($data['selisihmasuk']<0) {{ trans('all.masukterlambat') }} @else {{ trans('all.masukawal') }} @endif</td>
                            <td class="tdmodalDP">: {{ \App\Utils::sec2pretty(abs($data['selisihmasuk'])) }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">@if($data['selisihkeluar']<0) {{ trans('all.pulangawal') }} @else {{ trans('all.pulangterlambat') }} @endif</td>
                            <td class="tdmodalDP">: {{ \App\Utils::sec2pretty(abs($data['selisihkeluar'])) }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.lamakerja') }}</td>
                            <td class="tdmodalDP">: {{ \App\Utils::sec2pretty($data['lamakerja']) }}</td>
                        </tr>
                        <tr>
                            <td class="tdmodalDP">{{ trans('all.lamalembur') }}</td>
                            <td class="tdmodalDP">: {{ \App\Utils::sec2pretty($data['lamalembur']) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div id="tab-2" class="tab-pane">
            <div class="full-height-scroll">
                <div class="table-responsive">
                    @if(count($data['rekapabsen_jadwal']) > 0)
                        <table class="table-striped table-hover" width="100%">
                            @foreach($data['rekapabsen_jadwal'] as $key)
                                <tr>
                                    <td class="tdmodalDP">
                                        <table>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.waktu') }}</td>
                                                <td class="tdmodalDP">: {{ \App\Utils::tanggalCantik(date($key->tanggal)).' '.$key->jam }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.masukkeluar') }}</td>
                                                <td class="tdmodalDP">: {{ $key->masukkeluar }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.shiftsambungan') }}</td>
                                                <td class="tdmodalDP">: {{ $key->shiftsambungan }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <center>{{ trans('all.nodata') }}</center>
                    @endif
                </div>
            </div>
        </div>
        <div id="tab-3" class="tab-pane">
            <div class="full-height-scroll">
                <div class="table-responsive">
                    @if(count($data['rekapabsen_riwayat']) > 0)
                        <table class="table-striped table-hover" width="100%">
                            @foreach($data['rekapabsen_riwayat'] as $key)
                                <tr>
                                    <td class="tdmodalDP">
                                        <table>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.waktu') }}</td>
                                                <td class="tdmodalDP">: {{ \App\Utils::tanggalCantik(date($key->tanggal)).' '.$key->jam }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.masukkeluar') }}</td>
                                                <td class="tdmodalDP">: {{ $key->masukkeluar }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.alasan') }}</td>
                                                <td class="tdmodalDP">: {{ $key->alasan }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.terhitungkerja') }}</td>
                                                <td class="tdmodalDP">: {{ $key->terhitungkerja }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <center>{{ trans('all.nodata') }}</center>
                    @endif
                </div>
            </div>
        </div>
        <div id="tab-4" class="tab-pane">
            <div class="full-height-scroll">
                <div class="table-responsive">
                    @if(count($data['rekapabsen_hasil']) > 0)
                        <table class="table-striped table-hover" width="100%">
                            @foreach($data['rekapabsen_hasil'] as $key)
                                <tr>
                                    <td class="tdmodalDP">
                                        <table>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.waktu') }}</td>
                                                <td class="tdmodalDP">: {{ \App\Utils::tanggalCantik(date($key->tanggal)).' '.$key->jam }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.masukkeluar') }}</td>
                                                <td class="tdmodalDP">: {{ $key->masukkeluar }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.terhitung') }}</td>
                                                <td class="tdmodalDP">: {{ $key->terhitung }}</td>
                                            </tr>
                                            <tr>
                                                <td class="tdmodalDP">{{ trans('all.berdasarkan') }}</td>
                                                <td class="tdmodalDP">: {{ $key->flag}}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <center>{{ trans('all.nodata') }}</center>
                    @endif
                </div>
            </div>
        </div>
    </div>
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
function moreDetail(id){
    //alert(id);
    $("#detailshift_"+id).slideToggle();
}
</script>