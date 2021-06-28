@if(count($data) > 0)
    <table width="100%">
        @foreach($data as $key)
            <tr>
                <td width="110px">
                    <a href="{{ url('fotologabsen/'.$key->id.'/normal') }}" title="{{ $key->nama }}" data-gallery="">
                        <img src="{{ url('fotologabsen/'.$key->id.'/thumb') }}" width="110px" height="110px" style="border-radius:50%;">
                    </a>
                </td>
                <td valign="top">
                    <table>
                        <tr>
                            <td style="padding-top:0px;padding: 2px;"><b>{{ $key->nama }}</b></td>
                        </tr>
                        <tr>
                            <td style="padding:2px">
                                @if($key->masukkeluar == 'm')
                                    <span class="label label-info">{{ trans('all.masuk') }}</span>
                                @elseif($key->masukkeluar == 'k')
                                    <span class="label label-danger">{{ trans('all.keluar') }}</span>
                                @endif
                                {{ \App\Utils::tanggalCantik($key->tanggal).' '.$key->jam }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:2px">
                                @if($key->status == 'v')
                                    <span class="label label-success">{{ trans('all.valid') }}</span>
                                @elseif($key->status == 'c')
                                    <span class="label label-warning">{{ trans('all.confirm') }}</span>
                                @elseif($key->status == 'na')
                                    <span class="label label-danger">{{ trans('all.ditolak') }}</span>
                                @endif
                                {{ $key->mesin }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endforeach
    </table>
    @if($totaldata > 10)
        <span id="modaldetailpeta" startfrom="{{ $key->id }}">
            <script>
            $(document).ready(function() {
                var win = $('#modalbody-peta');
                var marker = $('#modaldetailpeta').attr('markerpeta');
                var startfrom = $('#modaldetailpeta').attr('startfrom'); //startfrom berisi idlogabsen terakhir untuk loadmore
                var run = true;
                win.scroll(function() {
                    if ($('#isimodalpeta').height() - win.height() == win.scrollTop()) {
                        if(run == true){
                            run = false;
                            $('#modaldetailpeta').remove();
                            //console.log(marker+'|'+startfrom);
                            //modalMarkerPeta(marker,startfrom,'modal');
                            dataMesinDetail('{{$tanggal}}',{{$idmesin}},'modal',startfrom);
                            return false;
                        }
                    }
                });
            });
            </script>
        </span>
    @endif
@endif