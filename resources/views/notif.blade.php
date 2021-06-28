<script>
    $(function(){
        $('#search_company').click(function(e){
            e.stopPropagation();
        });
    })
</script>
@if($jenis == 'perusahaan')
    @if($totalperusahaan > 4)
        <div style="padding:10px;padding-bottom:5px;position: relative;width:100%;" id="search_company" class="liheader">
            <span id="tescariperusahaan">
                <input class="form-control" autocomplete="off" autofocus id="cariperusahaan" onkeyup="cariPerusahaan()" placeholder="{{ trans('all.pencarian') }}">
                <i class="fa fa-search" style="position:absolute;right:18px;top:18px;font-size:18px;"></i>
            </span>
        </div>
    @endif
    <ul class="dropdown-menu-ul dropdowngroups" id="perusahaan_body" style="position: relative;max-height:400px;overflow: auto;padding-left:8px;">
        @foreach($perusahaan as $key)
            <li style="padding-left:8px;height:48px;margin:5px 10px 5px 2px;@if($key->status == 'c') background:#eee @endif" class="pilihanperusahaan @if(Session::get('conf_webperusahaan') == $key->id) header active @else header @endif">
                <a
                @if($key->status == 'c')
                    onclick="return perusahaanDalamKonfirmasi({{ $key->id }})" href="#"
                @elseif($key->status == 'w')
                    onclick="return perusahaanDalamProses()" href="#"
                @else
                    {{--href="{{ url('setperusahaan/'.$key->id) }}"--}}
                    onclick="return redirectSetPerushaaan({{ $key->id }})" href="#"
                @endif
                style="line-height: 24px !important;margin:0px !important;padding:0px !important;">
                    <table cellpadding="0" cellspacing="" style="margin-top:0px;width:100%">
                        <tr>
                            {{--<td style="padding:0;width:45px" rowspan="2"><img id="imgInp" width=42 style='border-radius: 50%;' height=42 src="{{ url('foto/perusahaan/'.$key->id) }}"></td>--}}
                            <td style="padding:0;white-space: nowrap;">
                                <span class="cari_namaperusahaan" style="margin-left:10px"><b>{{ $key->nama }}</b></span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0">
                                <span style="margin-left:10px;font-size:12px">{{ $key->kode }}</span>
                            </td>
                        </tr>
                    </table>
                </a>
            </li>
        @endforeach
    </ul>
    @if($totalperusahaan > 4)
        <div style="padding:4px;position: relative;width:100%" class="liheader">
            <div class="text-center">
                <b>{{ trans('all.totaldata') }} <span id="totalperusahaandropdown">{{ $totalperusahaan }}</span></b>
            </div>
        </div>
    @endif
@elseif($jenis == 'inbox')
    @if($totaldata > 0)
        <li>
            <a style="padding:5px"><b>{{ trans('all.kotakpesan') }}</b></a>
        </li>
        @foreach($data as $key)
            <li @if($key->isread == 't') style="background-color: #eee" @endif>
                <a style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" onclick="return detailPesan({{ $key->id }})">
                    <b>{{ \App\Utils::tanggalCantik($key->tanggal,"panjang").' '.$key->jam }}</b><br>
                    {{ $key->pesan }}
                </a>
            </li>
        @endforeach
    @else
        <li>
            {{ trans('all.nodata') }}
        </li>
    @endif
@elseif($jenis == 'notif')
{{--    @if($konfirmasiflag != '')--}}
    @if($konfirmasiflag != '' && (strpos(Session::get('hakakses_perusahaan')->notifikasiriwayatabsen, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasiterlambat, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasipulangawal, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasilembur, 'i') !== false))
        <li>
{{--            <a style="padding:5px"><b>{{ trans('all.konfirmasi_flag') }}</b><span class="pull-right" onclick="return ke('{{ url('datainduk/absensi/konfirmasiflag') }}')">{{ trans('all.selengkapnya') }}</span></a>--}}
            <a style="padding:5px"><b>{{ trans('all.konfirmasi_flag') }}</b><span class="pull-right" onclick="return ke('{{ url('notifdetail/konfirmasi_flag') }}')">{{ trans('all.selengkapnya') }}</span></a>
        </li>
        @foreach($konfirmasiflag as $key)
            <li>
                <a href="#" onclick="return detailKonfirmasiAbsen({{ $key->id }},'konfirmasiflag')">
                    <div>
                        <table width="100%">
                            <tr>
                                <td width="45px">
                                    <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" class="img-circle" width="45px" height="45px">
                                </td>
                                <td style="padding-left:10px;">
                                    <b>{{ $key->nama }}</b><br>
                                    {!! $key->masukkeluar !!}
                                    <span class="pull-right text-muted small">{{ $key->waktu }}</span>
                                    <br>
                                    <span class="label label-warning">{{ trans('all.pengajuan').' : '.trans('all.'.str_replace('-','',$key->flag)) }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </a>
            </li>
        @endforeach
    @endif

{{--    @if($ijintidakmasuk != '')--}}
    @if($ijintidakmasuk != '' && strpos(Session::get('hakakses_perusahaan')->notifikasiijintidakmasuk, 'i') !== false)
        <li>
            <a style="padding:5px"><b>{{ trans('all.ijintidakmasuk') }}</b><span class="pull-right" onclick="return ke('{{ url('notifdetail/ijintidakmasuk') }}')">{{ trans('all.selengkapnya') }}</span></a>
        </li>
        <li class="divider"></li>
        @foreach($ijintidakmasuk as $key)
            <li>
                <a style="line-height: 15px;" href="#" onclick="return detailKonfirmasiAbsen({{ $key->idijintidakmasuk }},'ijintidakmasuk')">
                    <div>
                        <table width="100%">
                            <tr>
                                <td valign="top" width="45px">
                                    <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" class="img-circle" width="45px" height="45px">
                                </td>
                                <td style="padding:0px;padding-left:10px !important;" valign="top">
                                    <table width="100%">
                                        <tr>
                                            <td style="padding:0px">
                                                <b>{{ $key->nama }}</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:0px">
                                                <span class="text-muted small">{{ \App\Utils::tanggalCantikDariSampai($key->tanggalawal, $key->tanggalakhir)/* $key->waktu*/ }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                    {{ $key->keterangan }}
                                </td>
                            </tr>
                            @if($key->filename != '')
                                <tr>
                                    <td colspan="2" style="padding-top: 5px;padding-bottom: 5px;">
                                        <center style='width:100%;height:72px;overflow: hidden' >
                                            <img style='margin-top:-24px' width=100% src='{{ url("foto/ijintidakmasuk/".$key->idijintidakmasuk) }}'>
                                        </center>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </a>
            </li>
        @endforeach
    @endif

{{--    @if($logabsen != '')--}}
    @if($logabsen != '' && strpos(Session::get('hakakses_perusahaan')->notifikasiriwayatabsen, 'i') !== false)
        <li>
            <a style="padding:5px"><b>{{ trans('all.beranda_notif_logabsen') }}</b><span class="pull-right" onclick="return ke('{{ url('notifdetail/logabsen') }}')">{{ trans('all.selengkapnya') }}</span></a>
        </li>
        <li class="divider"></li>
        @foreach($logabsen as $key)
            <li>
                <a href="#" onclick="return detailKonfirmasiAbsen({{ $key->idlogabsen }},'logabsen')">
                    <div>
                        <table width="100%">
                            <tr>
                                <td width="45px">
                                    <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" class="img-circle" width="45px" height="45px">
                                </td>
                                <td style="padding-left:10px;">
                                    <b>{{ $key->nama }}</b><br>
                                    {!! $key->masukkeluar !!}
                                    <span class="pull-right text-muted small">{{ $key->waktu }}</span>
                                    @if($key->konfirmasi != '')
                                        <span class="label label-warning">{{ $key->konfirmasi }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </a>
            </li>
        @endforeach
    @endif
@endif

