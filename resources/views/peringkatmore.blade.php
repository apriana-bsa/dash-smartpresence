@if(count($data) > 0)
    @foreach($data as $key)
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="ibox-content" style="margin-bottom:20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                @if($jenis == 'peringkatterbaik')
                    <div style="position:absolute;right:15px;top:0;padding:6px">
                        <span class="label">#{{ $key->peringkat }}</span>
                    </div>
                @endif
                <div class="col-md-3 col-sm-12" style="padding-left:0;padding-bottom:10px;min-width:110px;max-width:110px">
                    <center>
                        <a href="{{ url('fotonormal/pegawai/'.$key->idpegawai) }}" title="{{ $key->namapegawai }}" data-gallery="">
                            <img src="{{ url('foto/pegawai/'.$key->idpegawai) }}" width="110px" height="110px" style="border-radius:50%">
                        </a>
                    </center>
                </div>
                <div class="col-md-9 col-sm-12">
                    @if($jenis == 'peringkatterbaik')
                        <table width="100%">
                            <tr>
                                <td class="tdPeringkat" colspan="2">
                                    <b title='{{ $key->namapegawai }}'>
                                        {!! $key->nama !!}
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style="width:95px;padding-bottom:3px;">{{ trans('all.masukkerja') }}</td>
                                <td class="tdPeringkat">&nbsp;&nbsp;:&nbsp;&nbsp;{{ $key->masukkerja.' '.strtolower(trans('all.hari')) }}</td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style="padding-bottom:3px;">{{ trans('all.terlambat') }}</td>
                                <td class="tdPeringkat">&nbsp;&nbsp;:&nbsp;&nbsp;{{ $key->terlambat.' '.trans('all.kali').' ('.\App\Utils::sec2pretty($key->terlambatlama).')' }}</td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style="padding-bottom:3px;">{{ trans('all.pulangawal') }}</td>
                                <td class="tdPeringkat">&nbsp;&nbsp;:&nbsp;&nbsp;{{ $key->pulangawal.' '.trans('all.kali').' ('.\App\Utils::sec2pretty($key->pulangawallama).')' }}</td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style="padding-bottom:3px;">{{ trans('all.lamakerja') }}</td>
                                <td class="tdPeringkat">&nbsp;&nbsp;:&nbsp;&nbsp;{{ \App\Utils::sec2pretty($key->lamakerja) }}</td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style="padding-bottom:3px;">{{ trans('all.lamalembur') }}</td>
                                <td class="tdPeringkat">&nbsp;&nbsp;:&nbsp;&nbsp;{{ \App\Utils::sec2pretty($key->lamalembur) }}</td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style="padding-bottom:3px;" colspan="2">
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.profil') }}" class="fa fa-user" onclick="return rincian('profil',{{ $key->idpegawai }})"></i>&nbsp;
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.logabsen') }}" class="fa fa-calendar-o" onclick="return rincian('logabsen',{{ $key->idpegawai }})"></i>&nbsp;
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.rekapabsen') }}" class="fa fa-calendar" onclick="return rincian('rekapabsen',{{ $key->idpegawai }})"></i>
                                </td>
                            </tr>
                        </table>
                    @elseif($jenis == 'peringkatterlambat')
                        <table width="100%">
                            <tr>
                                <td class="tdterlambat"><b title='{{ $key->namapegawai }}'>{!! $key->nama !!}</b></td>
                            </tr>
                            <tr>
                                <td class="tdterlambat" style='height:23px;padding-bottom:5px;width:210px;white-space:nowrap;overflow:hidden;text-overflow: ellipsis'><span class="label label-primary" title="{{ $key->atribut }}">{{ $key->atribut }}</span></td>
                            </tr>
                            <tr>
                                <td class="tdterlambat">{{ trans('all.terlambat').' '.$key->terlambat.' '.trans('all.kali').' ('.\App\Utils::sec2pretty($key->terlambatlama).')' }}</td>
                            </tr>
                            <tr>
                                <td class="tdterlambat" style="padding-bottom:3px;" colspan="2">
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.profil') }}" class="fa fa-user" onclick="return rincian('profil',{{ $key->idpegawai }})"></i>&nbsp;
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.logabsen') }}" class="fa fa-calendar-o" onclick="return rincian('logabsen',{{ $key->idpegawai }})"></i>&nbsp;
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.rekapabsen') }}" class="fa fa-calendar" onclick="return rincian('rekapabsen',{{ $key->idpegawai }})"></i>
                                </td>
                            </tr>
                        </table>
                    @elseif($jenis == 'peringkatpulangawal')
                        <table width="100%">
                            <tr>
                                <td class="tdpulangawal" colspan="2" style='padding-bottom:5px;width:220px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;'><b title='{{ $key->namapegawai }}'>{!! $key->nama !!}</b></td>
                            </tr>
                            <tr>
                                <td class="tdpulangawal" style='height:23px;padding-bottom:5px;width:210px;white-space:nowrap;overflow:hidden;text-overflow: ellipsis'><span class="label label-primary" title="{{ $key->atribut }}">{{ $key->atribut }}</span></td>
                            </tr>
                            <tr>
                                <td class="tdpulangawal" style='padding-bottom:5px;'>{{ trans('all.pulangawal').' '.$key->pulangawal.' '.trans('all.kali').' ('.\App\Utils::sec2pretty($key->pulangawallama).')' }}</td>
                            </tr>
                            <tr>
                                <td class="tdpulangawal" style="padding-bottom:3px;" colspan="2">
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.profil') }}" class="fa fa-user" onclick="return rincian('profil',{{ $key->idpegawai }})"></i>&nbsp;
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.logabsen') }}" class="fa fa-calendar-o" onclick="return rincian('logabsen',{{ $key->idpegawai }})"></i>&nbsp;
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.rekapabsen') }}" class="fa fa-calendar" onclick="return rincian('rekapabsen',{{ $key->idpegawai }})"></i>
                                </td>
                            </tr>
                        </table>
                    @elseif($jenis == 'peringkatlamakerja')
                        <table width="100%">
                            <tr>
                                <td class="tdPeringkat" colspan="2" style='padding-bottom:5px;width:200px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;'><b title='{{ $key->namapegawai }}'>{!! $key->nama !!}</b></td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style='height:23px;padding-bottom:5px;width:210px;white-space:nowrap;overflow:hidden;text-overflow: ellipsis'><span class="label label-primary" title="{{ $key->atribut }}">{{ $key->atribut }}</span></td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style='padding-bottom:5px;'>{{ trans('all.lamakerja').' ('.\App\Utils::sec2pretty($key->lamakerja).')' }}</td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style="padding-bottom:3px;" colspan="2">
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.profil') }}" class="fa fa-user" onclick="return rincian('profil',{{ $key->idpegawai }})"></i>&nbsp;
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.logabsen') }}" class="fa fa-calendar-o" onclick="return rincian('logabsen',{{ $key->idpegawai }})"></i>&nbsp;
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.rekapabsen') }}" class="fa fa-calendar" onclick="return rincian('rekapabsen',{{ $key->idpegawai }})"></i>
                                </td>
                            </tr>
                        </table>
                    @elseif($jenis == 'peringkatlamalembur')
                        <table width="100%">
                            <tr>
                                <td class="tdPeringkat" colspan="2" style='padding-bottom:5px;width:210px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;'><b title='{{ $key->namapegawai }}'>{!! $key->nama !!}</b></td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style='height:23px;padding-bottom:5px;width:210px;white-space:nowrap;overflow:hidden;text-overflow: ellipsis'><span class="label label-primary" title="{{ $key->atribut }}">{{ $key->atribut }}</span></td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style='padding-bottom:5px;'>{{ trans('all.lamalembur').' ('.\App\Utils::sec2pretty($key->lamalembur).')' }}</td>
                            </tr>
                            <tr>
                                <td class="tdPeringkat" style="padding-bottom:3px;" colspan="2">
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.profil') }}" class="fa fa-user" onclick="return rincian('profil',{{ $key->idpegawai }})"></i>&nbsp;
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.logabsen') }}" class="fa fa-calendar-o" onclick="return rincian('logabsen',{{ $key->idpegawai }})"></i>&nbsp;
                                    <i style="cursor: pointer;font-size: 1.2em;" title="{{ trans('all.rekapabsen') }}" class="fa fa-calendar" onclick="return rincian('rekapabsen',{{ $key->idpegawai }})"></i>
                                </td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    <div class="col-md-12" id="kelompoktombol">
        <center>
            @if($totaldata > config('consts.LIMIT_FOTO'))
                <span class="loadmorebutton" onclick="more('{{ $jenis }}','{{ $key->startfrom }}')"></span>
            @endif
        </center>
    </div>

    <script>
    $(function(){
        var win = $(window);
        var run = true;
        // Each time the user scrolls
        win.scroll(function() {
            // End of the document reached?
            if ($(document).height() - win.height() == win.scrollTop()) {
                if(run == true) {
                    run = false;
                    //more(detail, startfrom);
                    $('.loadmorebutton').trigger('click');
                    //alert('ok');
                }
            }
        });
    });
    </script>
@else
    <center>{{ trans('all.nodata') }}</center>
@endif