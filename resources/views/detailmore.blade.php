@if($totaldata > 0 OR (isset($datashift) AND count($datashift) > 0))
    @if($detail != 'rekap' && $detail != 'ijintidakmasuk' && $detail != 'riwayat' && $detail != 'pulangawal' && $detail != 'lembur')
        @if($detail == 'customdashboard')
            @if($tampilgroup == 'y')
                <div class="col-lg-12">
                    <div class="ibox-content" style="padding-top:20px">
                        <table width=100% class="table datatable_customdashboard table-condensed table-hover">
                            <thead>
                            <tr>
                                <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                                <td class="opsi5"><b>{{ trans('all.jumlah') }}</b></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($datas as $key)
                                <tr>
                                    <td><span class="detailpegawai" onclick="return ke('{{ url('customdashboard/'.$idcustomdashboard_node.'/'.str_replace('-','',$tanggal).'/'.$key->key) }}')" style="cursor: pointer;">{{ $key->nama }}</span></td>
                                    <td>{{ $key->jumlah.' '.trans('all.orang') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <script>
                            $(function() {
                                $('.datatable_customdashboard').DataTable({
                                    bStateSave: true,
                                });
                            });
                        </script>
                    </div>
                </div>
            @else
                @foreach($datas as $key)
                    <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12" style="margin-bottom: 20px">
                        <div class="ibox-content">
                            <table width="100%" style="min-height:120px;">
                                <tr>
                                    <td style="padding:5px" valign="center" width="110px">
                                        <a href="{{ url('fotonormal/pegawai/'.$key->idpegawai) }}" title="{{ $key->namapegawai }}" data-gallery="">
                                            <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px"><br>
                                        </a>
                                    </td>
                                    <td style="padding:5px;max-width:100px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" valign="center">
                                        <span title="{{ $key->namapegawai }}">{!! $key->nama !!}</span><br>
                                        @if($key->pegawai_atribut != '')
                                            <span>{!! \App\Utils::dataExplode("|", $key->pegawai_atribut,true) !!}</span><br>
                                        @endif
                                        @if($key->pegawai_nomorhp)
                                            <span>{{ $key->pegawai_nomorhp }}</span><br>
                                        @endif
                                        @if($key->keterangan!='')
                                            {{--<span>{{ \App\Utils::formatDataCantik($key->keterangan) }}</span>--}}
                                            {{--<span>{{ eval("echo \\App\\Utils::tanggalCantik(\"2018-01-02 12:56:31\");") }}</span>--}}
                                            @if($jenisdata == 'ijintidakmasuk')
                                                <span>{{ $key->keterangan }}</span>
                                            @else
                                                <span>{{ eval("echo \\App\\Utils::".$key->keterangan.";") }}</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif
        @elseif($detail != 'datacapture')
            @if(isset($more))
                @if($more == true)
                    @foreach($datas as $key)
                        <div class="_datamore col-lg-2 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                            <center>
                                <table>
                                    <tr>
                                        <td width="110px">
                                            <a href="{{ url('fotonormal/pegawai/'.$key->idpegawai) }}" title="{{ $key->namapegawai }}" data-gallery="">
                                                <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                                            </a>
                                            @if($detail != 'totalpegawai' and $detail != 'adadikantor')
                                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color:#ccc;position:absolute;">
                                                    <i class="fa fa-ellipsis-v" style="padding-right: 8px;padding-left: 8px;"></i>
                                                </a>
                                                <ul class="dropdown-menu advancemenu1 pull-right">
                                                    <li class="header"><a href="#" onclick="return modalFacesample({{  $key->idpegawai }})">{{ trans('all.facesample') }}</a></li>
                                                    {{--<li class="divider"></li>--}}
                                                    <li class="header"><a href="#" onclick="return modalRiwayatPresensi({{  $key->idpegawai }})">{{ trans('all.riwayatpresensi') }}</a></li>
                                                    <li class="header"><a href="#" onclick="return modalRekapPresensi({{  $key->idpegawai }})">{{ trans('all.rekappresensi') }}</a></li>
                                                </ul>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <span title="{{ $key->namapegawai }}" style="max-width:100px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">{!! $key->nama !!}</span>
                                @if($detail == 'sudahabsen')
                                    <br><span style="font-size:11px">{{ $key->waktu }}</span>
                                @elseif($detail == 'belumabsen')
                                    @if($key->nomorhp != '')
                                        <br><span style="font-size:11px">{{ $key->nomorhp }}</span>
                                    @else <br>&nbsp; @endif
                                @elseif($detail == 'terlambat')
                                    <br>{{ trans('all.waktumasuk') }} <span style="font-size:11px">{{ $key->waktumasuk }}</span>
                                    <br>{{ trans('all.terlambat') }} <span style="font-size:11px">{{ \App\Utils::sec2pretty($key->terlambat) }}</span>
                                @elseif($detail == 'datangawal')
                                    <br>{{ trans('all.waktumasuk') }} <span style="font-size:11px">{{ $key->waktumasuk }}</span>
                                    <br>{{ trans('all.datangawal') }} <span style="font-size:11px">{{ \App\Utils::sec2pretty($key->datangawal) }}</span>
                                @elseif($detail == 'adadikantor')
                                    <br>{{ trans('all.sejak') }} <span style="font-size:11px">{{ $key->sejak }}</span><br>
                                    {{ trans('all.lokasi') }} <span style="font-size:11px">{{ $key->lokasi != '' ? $key->lokasi : '-' }}</span><br>
                                    <button class="btn btn-primary" onclick="return modalFlag({{  $key->idpegawai }},'{{ $key->idlogabsen }}','adadikantor')"><i class="fa fa-flag"></i>&nbsp;&nbsp;{{ trans('all.flag') }}</button>
                                @elseif($detail == 'totalpegawai')
                                    @if($key->atribut != '')
                                        <br><span class="label label-primary">{!! \App\Utils::dataExplode("|", $key->atribut,true) !!}</span>
                                    @endif
                                    @if($key->nomorhp != '')
                                        <br><i class="fa fa-phone"></i>&nbsp;&nbsp;{{ $key->nomorhp }}
                                    @else <br>&nbsp; @endif
                                    @if($key->atribut == '') <br>&nbsp; @endif
                                @elseif($detail == 'riwayat')
                                    <br>{!! $key->masukkeluar !!}
                                    <br>{{ $key->mesin }}
                                    <br><span style="font-size:11px">{{ $key->waktu }}</span>
                                @elseif($detail == 'alasan')
                                    <br>{!! $key->masukkeluar !!}
                                    <br>{{ $key->alasanmasukkeluar }}
                                    <br><span style="font-size:11px">{{ $key->waktu }}</span>
                                @endif
                            </center>
                        </div>
                    @endforeach
                @else
                    @if($detail == 'sudahabsen' OR $detail == 'belumabsen')
                        <div class="col-lg-12" style="margin-bottom:20px">
                            <ul class="nav nav-tabs">
                                <li class="active"><a data-toggle="tab" href="#tab-1">{{ trans('all.ringkasan') }}</a></li>
                                <li><a data-toggle="tab" href="#tab-2">{{ trans('all.jadwalshift') }}</a></li>
                                <li><a data-toggle="tab" href="#tab-3">{{ trans('all.detail') }}</a></li>
                            </ul>
                        </div>
                    @endif
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane @if($detail == 'sudahabsen' OR $detail == 'belumabsen') active @endif">
                            @if($detail == 'sudahabsen' OR $detail == 'belumabsen')
                                <div class="col-lg-12">
                                    <table width=100% class="table datatable_ringkasan table-condensed table-hover">
                                        <thead>
                                        <tr>
                                            <td class="nama"><b>{{ trans('all.jamkerja') }}</b></td>
                                            <td class="opsi2"><b>{{ trans('all.jumlah') }}</b></td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($dataringkasan as $key)
                                            <tr>
                                                <td><span  class="detailpegawai" onclick="detailJamKerja({{$key->id}})" style="cursor: pointer;">{{ $key->jamkerja }}</span></td>
                                                <td>{{ $key->jumlah.' '.trans('all.orang') }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <script>
                                        $(function() {
                                            $('.datatable_ringkasan').DataTable({
                                                bStateSave: true,
                                            });
                                        });
                                    </script>
                                </div>
                            @endif
                        </div>
                        <div id="tab-2" class="tab-pane">
                            @if($detail == 'sudahabsen' OR $detail == 'belumabsen')
                                <div class="col-lg-12">
                                    <table width=100% class="table datatable_jadwalshift table-condensed table-hover">
                                        <thead>
                                        <tr>
                                            <td class="nama"><b>{{ trans('all.jenisjamkerja') }}</b></td>
                                            <td class="opsi5"><b>{{ trans('all.beranda_'.$detail) }}</b></td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($datajadwalshift as $key)
                                            <tr>
                                                <td><span  class="detailpegawai" onclick="detailJadwalShift({{$key->idjenis}})" style="cursor: pointer;">{{ $key->jenis }}</span></td>
                                                <td>{{ $key->masuk.' '.trans('all.orang') }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <script>
                                        $(function() {
                                            $('.datatable_jadwalshift').DataTable({
                                                bStateSave: true,
                                            });
                                        });
                                    </script>
                                </div>
                            @endif
                        </div>
                        <div id="tab-3" class="tab-pane @if($detail != 'sudahabsen' AND $detail != 'belumabsen') active @endif">
                            @foreach($datas as $key)
                                <div class="_datamore col-lg-2 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                                    <center>
                                        <table>
                                            <tr>
                                                <td width="110px">
                                                    <a href="{{ url('fotonormal/pegawai/'.$key->idpegawai) }}" title="{{ $key->namapegawai }}" data-gallery="">
                                                        <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                                                    </a>
                                                    @if($detail != 'totalpegawai' and $detail != 'adadikantor')
                                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color:#ccc;position:absolute;">
                                                            <i class="fa fa-ellipsis-v" style="padding-right: 8px;padding-left: 8px;"></i>
                                                        </a>
                                                        <ul class="dropdown-menu advancemenu1 pull-right">
                                                            <li class="header"><a href="#" onclick="return modalFacesample({{  $key->idpegawai }})">{{ trans('all.facesample') }}</a></li>
                                                            {{--<li class="divider"></li>--}}
                                                            <li class="header"><a href="#" onclick="return modalRiwayatPresensi({{  $key->idpegawai }})">{{ trans('all.riwayatpresensi') }}</a></li>
                                                            <li class="header"><a href="#" onclick="return modalRekapPresensi({{  $key->idpegawai }})">{{ trans('all.rekappresensi') }}</a></li>
                                                            @if($detail == 'terlambat')
                                                                <li class="header"><a href="#" onclick="return modalFlag({{  $key->idpegawai }},{{ \App\Utils::getidLogAbsenFromRekapAben($key->id) }},'terlambat')">{{ trans('all.flag') }}</a></li>
                                                            @endif
                                                        </ul>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <span title="{{ $key->namapegawai }}" style="max-width:100px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">{!! $key->nama !!}</span>
                                        @if($detail == 'sudahabsen')
                                            <br><span style="font-size:11px">{{ $key->waktu }}</span>
                                        @elseif($detail == 'belumabsen')
                                            @if($key->nomorhp != '')
                                                <br><span style="font-size:11px">{{ $key->nomorhp }}</span>
                                            @else <br>&nbsp; @endif
                                            {{--@if($key->idjamkerjakhusus != '')--}}
                                                {{--<br><span style="font-size:11px">{{ trans('all.jamkerjakhusus') }}</span>--}}
                                            {{--@else <br>&nbsp; @endif--}}
                                        @elseif($detail == 'terlambat')
                                            <br>{{ trans('all.waktumasuk') }} <span style="font-size:11px">{{ $key->waktumasuk }}</span>
                                            <br>{{ trans('all.terlambat') }} <span style="font-size:11px">{{ \App\Utils::sec2pretty($key->terlambat) }}</span>
                                        @elseif($detail == 'datangawal')
                                            <br>{{ trans('all.waktumasuk') }} <span style="font-size:11px">{{ $key->waktumasuk }}</span>
                                            <br>{{ trans('all.datangawal') }} <span style="font-size:11px">{{ \App\Utils::sec2pretty($key->datangawal) }}</span>
                                        @elseif($detail == 'adadikantor')
                                            <br>{{ trans('all.sejak') }} <span style="font-size:11px">{{ $key->sejak }}</span>
                                            <br>{{ trans('all.lokasi') }} <span style="font-size:11px" title="{{ $key->lokasi }}">{{ $key->lokasi != '' ? $key->lokasi : '-' }}</span><br>
                                            <button class="btn btn-primary" onclick="return modalFlag({{  $key->idpegawai }},'{{ $key->idlogabsen }}','adadikantor')"><i class="fa fa-flag"></i>&nbsp;&nbsp;{{ trans('all.flag') }}</button>
                                        @elseif($detail == 'totalpegawai')
                                            @if($key->atribut != '')
                                                <br>{!! \App\Utils::dataExplode("|", $key->atribut,true) !!}
                                                {{--<br><span class="label label-primary">{{ $key->atribut }}</span>--}}
                                            @endif
                                            @if($key->nomorhp != '')
                                                <br><i class="fa fa-phone"></i>&nbsp;&nbsp;{{ $key->nomorhp }}
                                            @else <br>&nbsp; @endif
                                            @if($key->atribut == '') <br>&nbsp; @endif
                                        @elseif($detail == 'riwayat')
                                            <br>{!! $key->masukkeluar !!}
                                            <br>{{ $key->mesin }}
                                            <br><span style="font-size:11px">{{ $key->waktu }}</span>
                                        @elseif($detail == 'alasan')
                                            <br>{!! $key->masukkeluar !!}
                                            <br>{{ $key->alasanmasukkeluar }}
                                            <br><span style="font-size:11px">{{ $key->waktu }}</span>
                                        @endif
                                    </center>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                @foreach($datas as $key)
                    <div class="_datamore col-lg-2 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                        <center>
                            <table>
                                <tr>
                                    <td width="110px">
                                        <a href="{{ url('fotonormal/pegawai/'.$key->idpegawai) }}" title="{{ $key->namapegawai }}" data-gallery="">
                                            <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                                        </a>
                                        @if($detail != 'totalpegawai' and $detail != 'adadikantor')
                                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color:#ccc;position:absolute;">
                                                <i class="fa fa-ellipsis-v" style="padding-right: 8px;padding-left: 8px;"></i>
                                            </a>
                                            <ul class="dropdown-menu advancemenu1 pull-right">
                                                <li class="header"><a href="#" onclick="return modalFacesample({{  $key->idpegawai }})">{{ trans('all.facesample') }}</a></li>
                                                {{--<li class="divider"></li>--}}
                                                <li class="header"><a href="#" onclick="return modalRiwayatPresensi({{  $key->idpegawai }})">{{ trans('all.riwayatpresensi') }}</a></li>
                                                <li class="header"><a href="#" onclick="return modalRekapPresensi({{  $key->idpegawai }})">{{ trans('all.rekappresensi') }}</a></li>
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            <span title="{{ $key->namapegawai }}" style="max-width:100px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">{!! $key->nama !!}</span>
                            @if($detail == 'sudahabsen')
                                <br><span style="font-size:11px">{{ $key->waktu }}</span>
                            @elseif($detail == 'belumabsen')
                                @if($key->nomorhp != '')
                                    <br><span style="font-size:11px">{{ $key->nomorhp }}</span>
                                @else <br>&nbsp; @endif
                            @elseif($detail == 'terlambat')
                                <br>{{ trans('all.waktumasuk') }} <span style="font-size:11px">{{ $key->waktumasuk }}</span>
                                <br>{{ trans('all.terlambat') }} <span style="font-size:11px">{{ \App\Utils::sec2pretty($key->terlambat) }}</span>
                            @elseif($detail == 'datangawal')
                                <br>{{ trans('all.waktumasuk') }} <span style="font-size:11px">{{ $key->waktumasuk }}</span>
                                <br>{{ trans('all.datangawal') }} <span style="font-size:11px">{{ \App\Utils::sec2pretty($key->datangawal) }}</span>
                            @elseif($detail == 'adadikantor')
                                <br>{{ trans('all.sejak') }} <span style="font-size:11px">{{ $key->sejak }}</span><br>
                                {{ trans('all.lokasi') }} <span style="font-size:11px" title="{{ $key->lokasi }}">{{ $key->lokasi != '' ? $key->lokasi : '-' }}</span><br>
                                <button class="btn btn-primary" onclick="return modalFlag({{  $key->idpegawai }},'{{ $key->idlogabsen }}','adadikantor')"><i class="fa fa-flag"></i>&nbsp;&nbsp;{{ trans('all.flag') }}</button>
                            @elseif($detail == 'totalpegawai')
                                @if($key->atribut != '')
                                    <br>{!! \App\Utils::dataExplode("|", $key->atribut,true) !!}
                                    {{--<br><span class="label label-primary">{{ $key->atribut }}</span>--}}
                                @endif
                                @if($key->nomorhp != '')
                                    <br><i class="fa fa-phone"></i>&nbsp;&nbsp;{{ $key->nomorhp }}
                                @else <br>&nbsp; @endif
                                @if($key->atribut == '') <br>&nbsp; @endif
                            @elseif($detail == 'riwayat')
                                <br>{!! $key->masukkeluar !!}
                                <br>{{ $key->mesin }}
                                <br><span style="font-size:11px">{{ $key->waktu }}</span>
                            @elseif($detail == 'alasan')
                                <br>{!! $key->masukkeluar !!}
                                <br>{{ $key->alasanmasukkeluar }}
                                <br><span style="font-size:11px">{{ $key->waktu }}</span>
                            @endif
                        </center>
                    </div>
                @endforeach
            @endif
        @else
            {{-- jika datacapture --}}
            <div class="col-lg-12">
                <table width=100% class="table datatable_datacapture table-condensed table-hover">
                    <thead>
                    <tr>
                        <td class="nama"><b>{{ trans('all.mesin') }}</b></td>
                        <td class="alamat"><b>{{ trans('all.lastsync') }}</b></td>
                        <td class="opsi2"><b>{{ trans('all.absensi') }}</b></td>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($datas as $key)
                        <tr>
                            <td><span  class="detailpegawai" onclick="detailMesin({{$key->id}})" style="cursor: pointer;">{{ $key->nama }}</span></td>
                            <td>{{ $key->lastsync }}</td>
                            <td>{{ $key->jumlah.' '.trans('all.pegawai') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <script>
                    $(function() {
                        $('.datatable_datacapture').DataTable({
                            bStateSave: true,
                        });
                    });
                </script>
            </div>
        @endif
    @else
        @if($detail == 'ijintidakmasuk')
            @foreach($datas as $key)
                <div class="col-md-4 pin" style="padding-bottom:20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                    <div class="ibox-content">
                        <table width="100%">
                            <tr>
                                <td style="padding:5px;width:50px" valign="top">
                                    <a href="{{ url('fotonormal/pegawai/'.$key->idpegawai) }}" title="{{ $key->namapegawai }}" data-gallery="">
                                        <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" width="50px" height="50px" style="border-radius:50%;margin-bottom:5px">
                                    </a>
                                </td>
                                <td style="padding:5px;" valign="top">
                                    <span title="{{ $key->namapegawai }}">{!! $key->nama !!}</span><br>
                                    <i class="fa fa-phone"></i>&nbsp;&nbsp;{{ $key->nomorhp }}<br>
                                    <span class="label label-primary">{{ $key->atribut }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr style="margin-top:5px;margin-bottom:5px"></td>
                            </tr>
                            @foreach($key->ijintidakmasuk as $ijintidakmasuk)
                                <tr>
                                    <td style="padding:5px;" valign="top" colspan="2">
                                        <table width="100%">
                                            <tr>
                                                <td>
                                                    <span style="font-size: 16px;font-weight: bold;">{{ $ijintidakmasuk->tanggalawal }} @if($ijintidakmasuk->tanggalakhir != '') - {{ $ijintidakmasuk->tanggalakhir }} @endif</span>
                                                </td>
                                                <td align="right">
                                                    {{ $ijintidakmasuk->alasantidakmasuk }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    {{ $ijintidakmasuk->keterangan }}
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endforeach
        @elseif($detail == 'rekap' or $detail == 'riwayat' or $detail == 'pulangawal' or $detail == 'lembur')
            @if($detail == 'rekap')
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#tabringkasannormal">{{ trans('all.normal') }}</a></li>
                    <li><a data-toggle="tab" href="#tabringkasanshift">{{ trans('all.shift') }}</a></li>
                </ul>
                <br>
                <div class="tab-content">
                    <div id="tabringkasannormal" class="tab-pane active">
            @endif
            @if($totaldata > 0)
                @foreach($datas as $key)
                    <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12" style="margin-bottom: 20px">
                        @if($detail == 'rekap')
                            @if (
                                    ($key->jenisjamkerja == 'full' AND $key->idjamkerjakhusus == '' AND ( $key->idharilibur != '' OR $key->jadwalmasukkerja == 't')) OR
                                    ($key->jenisjamkerja == 'shift' AND $key->idjamkerjakhusus == '' AND $key->jadwalmasukkerja == 't')
                                )
                                <div class="ibox-content" style="background:#ed6575;color:#fff;min-height:165px">
                            @elseif (
                                $key->masukkerja == 't' AND
                                !(
                                    ($key->jenisjamkerja == 'full' AND $key->idjamkerjakhusus == '' AND ( $key->idharilibur != '' OR $key->jadwalmasukkerja == 't')) OR
                                    ($key->jenisjamkerja == 'shift' AND $key->idjamkerjakhusus == '' AND $key->jadwalmasukkerja == 't')
                                )
                            )
                                <div class="ibox-content" style="background:#888;color:#fff;min-height:165px">
                            @else
                                <div class="ibox-content" style="min-height:165px">
                            @endif
                        @else
                            <div class="ibox-content">
                        @endif
                            @if($detail == 'rekap')
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="position:absolute;right:30px;color:#ccc">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-ellipsis-v"></i>&nbsp;
                                </a>
                                <ul class="dropdown-menu advancemenu pull-right" style="color:#000">
                                    <li class="header"><a href="#" onclick="return modalDetailrekap({{  $key->id }})">{{ trans('all.detail') }}</a></li>
                                    <li class="header"><a href="#" onclick="return modalFacesample({{  $key->idpegawai }})">{{ trans('all.facesample') }}</a></li>
                                    {{--<li class="divider"></li>--}}
                                    <li class="header"><a href="#" onclick="return modalRiwayatPresensi({{  $key->idpegawai }})">{{ trans('all.riwayatpresensi') }}</a></li>
                                    <li class="header"><a href="#" onclick="return modalRekapPresensi({{  $key->idpegawai }})">{{ trans('all.rekappresensi') }}</a></li>
                                </ul>
                            @endif
                            <table width="100%" style="min-height:165px;">
                                <tr>
                                    <td style="padding:5px" valign="center" width="110px">
                                        @if($detail == 'riwayat')
                                            @if($key->sumber == 'fingerprint')
                                                <img src="{{ url('lib/img/fingerprint.png') }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px"><br>
                                            @else
                                                <a href="{{ url('fotologabsen/'.$key->id.'/normal') }}" title="{{ $key->namapegawai }}" data-gallery="">
                                                    <img src="{{ url('fotologabsen/'.$key->id.'/thumb') }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px"><br>
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ url('fotonormal/pegawai/'.$key->idpegawai) }}" title="{{ $key->namapegawai }}" data-gallery="">
                                                <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px"><br>
                                            </a>
                                        @endif
                                    </td>
                                    <td style="padding:5px;max-width:100px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" valign="center">
                                        <table width="100%" style="table-layout: fixed;border-collapse:collapse;">
                                            <tr>
                                                <td style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;padding:0;">
                                                    <p><span title="{{ $key->namapegawai }}">{!! $key->nama !!}</span></p>
                                                </td>
                                            </tr>
                                        </table>
                                        @if($detail == 'rekap')
                                            @if (
                                                    ($key->jenisjamkerja == 'full' AND $key->idjamkerjakhusus == '' AND ( $key->idharilibur != '' OR $key->jadwalmasukkerja == 't')) OR
                                                    ($key->jenisjamkerja == 'shift' AND $key->idjamkerjakhusus == '' AND $key->jadwalmasukkerja == 't')
                                                )
                                                @if($key->harilibur != '')
                                                    {{ $key->harilibur }}
                                                @else
                                                    {{ trans('all.libur') }}
                                                @endif
                                            @elseif (
                                                    $key->masukkerja == 't' AND
                                                    !(
                                                        ($key->jenisjamkerja == 'full' AND $key->idjamkerjakhusus == '' AND ( $key->idharilibur != '' OR $key->jadwalmasukkerja == 't')) OR
                                                        ($key->jenisjamkerja == 'shift' AND $key->idjamkerjakhusus == '' AND $key->jadwalmasukkerja == 't')
                                                    )
                                                )
                                                {{ trans('all.tidakmasuk') }}
                                                @if($key->alasantidakmasuk != '')
                                                    <br>{{ $key->alasantidakmasuk }}
                                                @endif
                                            @else
                                                @if($key->waktumasuk != '') {!! '<span class="label label-primary">'.trans('all.masuk').'</span>'.' '.$key->waktumasuk !!}<br> @endif
                                                @if($key->waktukeluar != '') {!! '<span class="label label-danger">'.trans('all.keluar').'</span>'.' '.$key->waktukeluar !!}<br> @endif
                                                @if(!($key->terlambat == '' OR $key->terlambat == 0 )) {{ trans('all.masukterlambat').': '.\App\Utils::sec2pretty($key->terlambat) }}<br> @endif
                                                @if(!($key->pulangawal == '' OR $key->pulangawal == 0 )) {{ trans('all.pulangawal').': '.\App\Utils::sec2pretty($key->pulangawal) }}<br> @endif
                                                {{trans('all.lamakerja').': '.\App\Utils::sec2pretty($key->lamakerja) }}<br>
                                                @if(!($key->lamalembur == '' OR $key->lamalembur == 0)) {{ trans('all.lamalembur').': '.\App\Utils::sec2pretty($key->lamalembur) }} @endif
                                            @endif
                                            <i class="fa fa-smile-o stylehover" style="cursor:pointer" title="{{ trans('all.facesample') }}" onclick="return modalFacesample({{  $key->idpegawai }})"></i>&nbsp;&nbsp;
                                            <i class="fa fa-history stylehover" style="cursor:pointer" title="{{ trans('all.riwayatpresensi') }}" onclick="return modalRiwayatPresensi({{  $key->idpegawai }})"></i>&nbsp;&nbsp;
                                            <i class="fa fa-list-alt stylehover" style="cursor:pointer" title="{{ trans('all.rekappresensi') }}" onclick="return modalRekapPresensi({{  $key->idpegawai }})"></i>&nbsp;&nbsp;
                                        @elseif($detail == 'riwayat')
                                            {!! $key->masukkeluar !!}<br>
                                            {{ $key->mesin }}<br>
                                            <p><span style="font-size:11px">{{ $key->waktu }}</span></p>
                                            <i class="fa fa-map-marker stylehover" style="cursor:pointer" title="{{ trans('all.petariwayatpresensi') }}" onclick="return lokasiAbsen({{ $key->id }})" id="iconpeta_{{ $key->id }}" lat="{{ $key->lat }}" lon="{{ $key->lon }}" nama="{{ $key->namapegawai }}" tanggal="{{ $key->waktu }}"></i>&nbsp;&nbsp;
                                            <i class="fa fa-smile-o stylehover" style="cursor:pointer" title="{{ trans('all.facesample') }}" onclick="return modalFacesample({{  $key->idpegawai }})"></i>&nbsp;&nbsp;
                                            <i class="fa fa-history stylehover" style="cursor:pointer" title="{{ trans('all.riwayatpresensi') }}" onclick="return modalRiwayatPresensi({{  $key->idpegawai }})"></i>&nbsp;&nbsp;
                                            <i class="fa fa-list-alt stylehover" style="cursor:pointer" title="{{ trans('all.rekappresensi') }}" onclick="return modalRekapPresensi({{  $key->idpegawai }})"></i>&nbsp;&nbsp;
                                            <i class="fa fa-flag stylehover" style="cursor:pointer" title="{{ trans('all.flag') }}" onclick="return modalFlag({{  $key->idpegawai }},'{{ $key->id }}','riwayat')"></i>
                                        @elseif($detail == 'pulangawal')
                                            <span class="label label-primary" title="{{ $key->atribut }}">{{ $key->atribut }}</span><br><p></p>
                                            {{ trans('all.waktukeluar').' '.$key->waktukeluar }}<br>
                                            {{ trans('all.pulanglebihawal').' '.\App\Utils::sec2pretty($key->pulangawalmenit) }}<br>
                                            <i class="fa fa-smile-o stylehover" style="cursor:pointer" title="{{ trans('all.facesample') }}" onclick="return modalFacesample({{  $key->idpegawai }})"></i>&nbsp;&nbsp;
                                            <i class="fa fa-history stylehover" style="cursor:pointer" title="{{ trans('all.riwayatpresensi') }}" onclick="return modalRiwayatPresensi({{  $key->idpegawai }})"></i>&nbsp;&nbsp;
                                            <i class="fa fa-list-alt stylehover" style="cursor:pointer" title="{{ trans('all.rekappresensi') }}" onclick="return modalRekapPresensi({{  $key->idpegawai }})"></i>&nbsp;&nbsp;
                                        @elseif($detail == 'lembur')
                                            <span class="label label-primary" title="{{ $key->atribut }}">{{ $key->atribut }}</span><br><p></p>
                                            {{ trans('all.lamalembur').' '.\App\Utils::sec2pretty($key->lamalembur) }}<br>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <center style="margin-top:50px">{{ trans('all.nodata') }}</center>
            @endif
            @if($detail == 'rekap')
                    </div>
                    <div id="tabringkasanshift" class="tab-pane">
                        @if($totaldatashift > 0)
                            @foreach($datashift as $key)
                                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12" style="margin-bottom: 20px">
                                    <div class="ibox-content" style="min-height:165px">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="position:absolute;right:30px;color:#ccc">
                                            &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-ellipsis-v"></i>&nbsp;
                                        </a>
                                        <ul class="dropdown-menu advancemenu pull-right" style="color:#000">
                                            <li class="header"><a href="#" onclick="return modalFacesample({{  $key->idpegawai }})">{{ trans('all.facesample') }}</a></li>
                                            {{--<li class="divider"></li>--}}
                                            <li class="header"><a href="#" onclick="return modalRiwayatPresensi({{  $key->idpegawai }})">{{ trans('all.riwayatpresensi') }}</a></li>
                                            <li class="header"><a href="#" onclick="return modalRekapPresensi({{  $key->idpegawai }})">{{ trans('all.rekappresensi') }}</a></li>
                                        </ul>
                                        <table width="100%" style="min-height:165px;">
                                            <tr>
                                                <td style="padding:5px" valign="center" width="110px">
                                                    <a href="{{ url('fotonormal/pegawai/'.$key->idpegawai) }}" title="{{ $key->namapegawai }}" data-gallery="">
                                                        <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px"><br>
                                                    </a>
                                                </td>
                                                <td style="padding:5px;max-width:100px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" valign="center">
                                                    <table width="100%" style="table-layout: fixed;border-collapse:collapse;">
                                                        <tr>
                                                            <td style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;padding:0;">
                                                                <p><span title="{{ $key->namapegawai }}">{!! $key->nama !!}</span></p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    @if ($key->masukkerja == 't')
                                                        {{ trans('all.tidakmasuk') }}
                                                    @else
                                                        @if($key->waktumasuk != '') {!! '<span class="label label-primary">'.trans('all.masuk').'</span>'.' '.$key->waktumasuk !!}<br> @endif
                                                        @if($key->waktukeluar != '') {!! '<span class="label label-danger">'.trans('all.keluar').'</span>'.' '.$key->waktukeluar !!}<br> @endif
                                                        @if($key->jamkerjashift != '') {{ trans('all.jamkerjashift').': '.$key->jamkerjashift }}<br> @endif
                                                        @if(!($key->terlambat == '' OR $key->terlambat == 0 )) {{ trans('all.masukterlambat').': '.\App\Utils::sec2pretty($key->terlambat) }}<br> @endif
                                                        @if(!($key->pulangawal == '' OR $key->pulangawal == 0 )) {{ trans('all.pulangawal').': '.\App\Utils::sec2pretty($key->pulangawal) }}<br> @endif
                                                        {{trans('all.lamakerja').': '.\App\Utils::sec2pretty($key->lamakerja) }}<br>
                                                        @if(!($key->lamalembur == '' OR $key->lamalembur == 0)) {{ trans('all.lamalembur').': '.\App\Utils::sec2pretty($key->lamalembur) }} @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <center style="margin-top:50px">{{ trans('all.nodata') }}</center>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    @endif
@else
    @if(isset($more))
        @if($more == false)
            <center>{{ trans('all.nodata') }}</center><br>
        @endif
    @else
        <center>{{ trans('all.nodata') }}</center><br>
    @endif
@endif

{{--digunakan untuk loadmore--}}
@if($totaldata > $totaldatalimit)
    @if($detail != 'datacapture')
        <span id="bantuan" detail="{{ $detail }}" startfrom="{{ $key->startfrom }}"></span>
    @endif
@endif

@if($detail == 'belumabsen' or $detail == 'sudahabsen')
    <script>
    window.detailJamKerja=(function(id){
        $("#showmodaljamkerja").attr("href", "");
        $("#showmodaljamkerja").attr("href", "{{ url('jamkerjapegawai').'/'.$tanggal.'/'.$detail }}/"+id);
        $('#showmodaljamkerja').trigger('click');
        return false;
    });

    window.detailJadwalShift=(function(id){
        $("#showmodaljamkerja").attr("href", "");
        $("#showmodaljamkerja").attr("href", "{{ url('jadwalshift').'/'.$tanggal.'/'.$detail }}/"+id);
        $('#showmodaljamkerja').trigger('click');
        return false;
    });

    $('body').on('hidden.bs.modal', '.modaljamkerja', function () {
        $(this).removeData('bs.modal');
        $("#" + $(this).attr("id") + " .modal-content").empty();
        $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
    });
    </script>
    <!-- Modal jamkerja-->
    <a href="" id="showmodaljamkerja" data-toggle="modal" data-target="#modaljamkerja" style="display:none"></a>
    <div class="modal modaljamkerja fade" id="modaljamkerja" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">

            </div>
        </div>
    </div>
    <!-- Modal jamkerja-->
@endif

@if($detail == 'datacapture')
    <script>
    window.detailMesin=(function(id){
        dataMesinDetail('{{$tanggal}}',id,'bukanmodal',"");
        //$('#showmodalpeta').trigger('click');
        return false;
    });

    function dataMesinDetail(tanggal,id,jenis,startfrom){
        $.ajax({
            type: "GET",
            url: "{{ url('datacapture').'/detail' }}/"+tanggal+"/"+id+"/"+startfrom,
            cache: false,
            success: function(html){
                if(jenis == 'modal') {
                    setTimeout(function () {
                        $('#isimodalpeta').append(html);
                    }, 200);
                }else{
                    $('#isimodalpeta').html('');
                    $('#showmodalpeta').trigger('click');
                    setTimeout(function () {
                        $('#isimodalpeta').html(html);
                    }, 200);
                }
            }
        });
    }
    </script>
@endif

@if($detail == 'riwayat')
    <!-- Modal peta-->
    <a href="#" id="tombolmodalpeta" style="display:none" data-toggle="modal" data-target="#modalPeta"></a>
    <div class="modal fade" id="modalPeta" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-md">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('all.peta') }}</h4>
                </div>
                <div>
                    <table width='100%'>
                        <tr>
                            <td colspan=2>
                                <input id="pac-input" class="controls pac-input" type="text" placeholder="Search Box">
                                <div id="map"></div>
                            </td>
                        </tr>
                        <tr>
                            <td style="mergin-left:10px">
                                {{ trans('all.petariwayatpresensi') }} <span id="keteranganpeta"></span>
                            </td>
                            <td align=right>
                                <button data-dismiss="modal" id="tutupmodal" class="btn btn-primary"><i class='fa fa-undo'></i> {{ trans('all.tutup') }}</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal peta-->
    <script>
        var firstRun = true;
        var lat = '';
        var lon = '';
        var markers = null;
        var map = '';
        var lokasi;
        function lokasiAbsen(id){
            lat = $('#iconpeta_'+id).attr('lat');
            lon = $('#iconpeta_'+id).attr('lon');
            //menghapus keterangan yg lama dan mengganti keterangan yg baru keterangan peta riwayat presensi
            $('#keteranganpeta').html('');
            var keteranganpeta = $('#iconpeta_'+id).attr('nama')+' ('+$('#iconpeta_'+id).attr('tanggal')+')';
            $('#keteranganpeta').html(keteranganpeta);
            if(markers != null){
                //hilangkan marker yg lama
                markers.setMap(null);
                //set posisi peta ke default
                map.setCenter({lat: -4.653079918274038, lng:117.7734375});
            }
            setTimeout(function(){
                //kasih marker di lokasi baru
                var myLatlng = new google.maps.LatLng(lat,lon);
                markers = new google.maps.Marker({
                    position: myLatlng,
                    map: map
                });
                //set posisi peta berdasarkan marker
                map.setCenter(markers.getPosition());
                //jika marker di klik
                markers.addListener('click', function(event) {
                    map.setZoom(18);
                    map.setCenter(markers.getPosition());
                });
            },1000);
            //tampilkan modal marker
            $('#tombolmodalpeta').trigger('click');
        }

        @if($lokasi != '')
                lokasi = [
                @foreach($lokasi as $key)
            [{{ $key->lat }}, {{ $key->lon }}, '{{ $key->nama }}'],
            @endforeach
        ];
        @endif
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: -4.653079918274038, lng:117.7734375},
                zoom: 18,
                mapTypeId: 'roadmap',
                gestureHandling: 'greedy',
                fullscreenControl: false,
                //styles: styleGoogleMaps
            });

            var mapMaxZoom = 18;
            var geoloccontrol = new klokantech.GeolocationControl(map, mapMaxZoom);

            // Create the search box and link it to the UI element.
            var input = document.getElementById('pac-input');
            var searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            // Bias the SearchBox results towards current map's viewport.
            map.addListener('bounds_changed', function() {
                searchBox.setBounds(map.getBounds());
            });

            var marker_lokasi, i;

            var icon = {
                url: '{{ url('lib/drawable-xhdpi/perusahaan.png') }}', // url
                scaledSize: new google.maps.Size(30, 30), // scaled size
                origin: new google.maps.Point(0,0), // origin
                anchor: new google.maps.Point(10, 35) // anchor
            };

            for (i = 0; i < lokasi.length; i++) {
                marker_lokasi = new google.maps.Marker({
                    position: new google.maps.LatLng(lokasi[i][0], lokasi[i][1]),
                    map: map,
                    icon: icon
                });

                google.maps.event.addListener(marker_lokasi, 'click', (function(marker_lokasi, i) {
                    return function() {
                        //console.log(lokasi[i][2])
                        alertInfo('{{ trans('all.lokasi') }} '+lokasi[i][2]);
                        /*infowindow.setContent(lokasi[i][0]);
                         infowindow.open(map, marker_lokasi);*/
                    }
                })(marker_lokasi, i));
            };

            // Listen for the event fired when the user selects a prediction and retrieve
            // more details for that place.
            searchBox.addListener('places_changed', function() {
                var places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }

                // For each place, get the icon, name and location.
                var bounds = new google.maps.LatLngBounds();
                places.forEach(function(place) {
                    if (!place.geometry) {
                        console.log("Returned place contains no geometry");
                        return;
                    }

                    if (place.geometry.viewport) {
                        // Only geocodes have viewport.
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
        }

        $('#modalPeta').on('shown.bs.modal', function(){
            if (firstRun==true) {
                firstRun = false;
                initMap();
            }
        });
    </script>
@endif
<script>
@if($totaldata > $totaldatalimit)
    @if($detail != 'datacapture')
        $(document).ready(function() {
            var win = $(window);
            var detail = $('#bantuan').attr('detail');
            var startfrom = $('#bantuan').attr('startfrom');
            var run = true;
            // Each time the user scrolls
            win.scroll(function() {
                // End of the document reached?
                if ($(document).height() - win.height() == win.scrollTop()) {
                    //if ($('._datamore').height() - win.height() == win.scrollTop()) {
                    if(!$('#tab-1').hasClass('active')){
                        if(run == true) {
                            run = false;
                            $('#bantuan').remove();
                            more(detail, startfrom, '{{ $detail }}');
                            return false;
                        }
                    }
                }
            });
        });
    @endif
@endif

$(document).ready(function() {
    @if(isset($more))
        @if($more == false)
            setTimeout(function() {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 4000,
                    extendedTimeOut: 4000,
                    positionClass: 'toast-bottom-right'
                };
                @if(isset($tanggal))
                    @if($detail == 'datacapture')
                        toastr.info('{{ $totaldata }}', '{{ trans('all.totaldata').' '.\App\Utils::tanggalCantik($tanggal) }}');
                    @else
                        toastr.info('{{ $totaldata.' '.trans('all.pegawai') }}', '{{ trans('all.totaldata').' '.\App\Utils::tanggalCantik($tanggal) }}');
                    @endif
                @else
                    toastr.info('{{ $totaldata.' '.trans('all.pegawai') }}', '{{ trans('all.totaldata') }}');
                @endif
            }, 500);
        @endif
    @else
        setTimeout(function() {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                timeOut: 4000,
                extendedTimeOut: 4000,
                positionClass: 'toast-bottom-right'
            };
            @if(isset($tanggal))
                @if($detail == 'customdashboard')
                    @if($tampilgroup == 'y')
                        toastr.info('{{ $totaldata }}', '{{ trans('all.totaldata').' '.\App\Utils::tanggalCantik($tanggal) }}');
                    @else
                        toastr.info('{{ $totaldata.' '.trans('all.pegawai') }}', '{{ trans('all.totaldata').' '.\App\Utils::tanggalCantik($tanggal) }}');
                    @endif
                @else
                    toastr.info('{{ $totaldata.' '.trans('all.pegawai') }}', '{{ trans('all.totaldata').' '.\App\Utils::tanggalCantik($tanggal) }}');
                @endif
            @else
                @if($detail == 'customdashboard')
                    @if($tampilgroup == 'y')
                        toastr.info('{{ $totaldata }}', '{{ trans('all.totaldata') }}');
                    @else
                        toastr.info('{{ $totaldata.' '.trans('all.pegawai') }}', '{{ trans('all.totaldata') }}');
                    @endif
                @else
                    toastr.info('{{ $totaldata.' '.trans('all.pegawai') }}', '{{ trans('all.totaldata') }}');
                @endif
            @endif
        }, 500);
    @endif
});
</script>
<style>
    .advancemenu{
        right:35px !important;
        top:45px;
        min-width:120px;
    }

    .advancemenu1{
        right:35px !important;
        top:25px;
        min-width:120px;
    }

    .ibox-content{
        padding:8px;
    }
</style>
