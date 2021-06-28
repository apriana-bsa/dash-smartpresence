{{--jika paramyymm  != o, artinya itu dari ajax ganti bulan--}}
@if($paramyymm != 'o')
    @if(count($data) > 0)
        <table class="table-striped table-hover" width="100%">
            @foreach($data as $key)
                @if($jenis == 'logabsen')
                    <tr>
                        <td width="120px" style="padding:5px">
                            <a href="{{ url('fotologabsen/'.$key->id.'/normal') }}" title="{{ $namapegawai }}" data-gallery="">
                                <img src="{{ url('fotologabsen/'.$key->id.'/thumb') }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                            </a>
                        </td>
                        <td valign="top" style="padding:5px;padding-top:15px;">
                            {!! $key->masukkeluar !!}<p></p>
                            {{ $key->mesin }}<br>
                            {{ \App\Utils::tanggalCantik($key->waktu) }}
                        </td>
                    </tr>
                @elseif($jenis == 'rekapabsen')
                    <tr>
                        <td width="120px" style="padding:5px">
                            <a href="{{ url('fotonormal/pegawai/'.$idpegawai) }}" title="{{ $namapegawai }}" data-gallery="">
                                <img src="{{ url('foto/pegawai/'.$idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                            </a>
                        </td>
                        <td style="padding:5px">
                            <span style="font-size: 16px;font-weight: bold;">{{ \App\Utils::tanggalCantik($key->waktu) }}</span><br>
                            @if($key->waktumasuk != '') {{ trans('all.masuk').' '.$key->waktumasuk }}<br> @endif
                            @if($key->waktukeluar != '') {{ trans('all.keluar').' '.$key->waktukeluar }}<br> @endif
                            @if($key->lamakerja != '') {{ trans('all.lamakerja').' '.\App\Utils::sec2pretty($key->lamakerja) }}<br> @endif
                            @if($key->lamalembur != '') {{ trans('all.lamalembur').' '.\App\Utils::sec2pretty($key->lamalembur) }} @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </table>
        @if($jenis == 'logabsen')
            @if($totaldata > config('consts.LIMIT_FOTO'))
                <div class="col-md-12" id="kelompoktomboldetail" style="padding:10px">
                    <center>
                        <button id="more" class="btn btn-success" onclick="moreDetail('{{ $jenis }}',{{ $idpegawai }},'{{ $key->startfrom }}',{{ $yymm }})"><i class="fa fa-chevron-circle-down"></i>&nbsp;&nbsp;{{ trans('all.selengkapnya') }}</button>&nbsp;&nbsp;
                    </center>
                </div>
            @endif
        @endif
    @else
        <center style="margin-top:30px">{{ trans('all.nodata') }}</center>
    @endif
@else
    <style>
    .tdmodalDP{
        padding:3px;
    }

    /*.modal-content{
        width:1020px;
    }*/
    </style>
    <div class="modal-header">
        <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
        <h4 class="modal-title">@if($jenis == 'logabsen'){{ trans('all.riwayatpresensi') }} @elseif($jenis == 'rekapabsen') {{ trans('all.rekappresensi') }} @endif {{ '('.$namapegawai.')' }}</h4>
    </div>
    <div class="modal-body body-modal row" id="moredetail" style="padding-right:0;padding-bottom:0;margin-right:0">
        <div style="padding-right:40px;padding-bottom:15px;">
            @if(isset($listyymm))
                <select class="form-control" id="bulanDetailBeranda" onchange="return gantiBulan('{{ $jenis }}',{{ $idpegawai }})">
                    @for($i=0;$i<count($listyymm);$i++)
                        <option value="{{ $listyymm[$i]['isi'] }}">{{ $listyymm[$i]['tampilan'] }}</option>
                    @endfor
                </select>
            @endif
        </div>
        <div style="min-height:100px;max-height:480px;overflow: auto;width:100%;padding-right:25px" id="contentDetailBeranda">
            @if(count($data) > 0)
                <table class="table-striped table-hover" width="100%">
                    @foreach($data as $key)
                        @if($jenis == 'logabsen')
                            <tr>
                                <td width="120px">
                                    <a href="{{ url('fotologabsen/'.$key->id.'/normal') }}" title="{{ $namapegawai }}" data-gallery="">
                                        <img src="{{ url('fotologabsen/'.$key->id.'/thumb') }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                                    </a>
                                </td>
                                <td valign="top" style="padding-top:15px;">
                                    {!! $key->masukkeluar !!}<p></p>
                                    {{ $key->mesin }}<br>
                                    {{ \App\Utils::tanggalCantik($key->waktu) }}
                                </td>
                            </tr>
                        @elseif($jenis == 'rekapabsen')
                            <tr>
                                <td width="120px">
                                    <a href="{{ url('fotonormal/pegawai/'.$idpegawai) }}" title="{{ $namapegawai }}" data-gallery="">
                                        <img src="{{ url('foto/pegawai/'.$idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                                    </a>
                                </td>
                                <td>
                                    <span style="font-size: 16px;font-weight: bold;">{{ \App\Utils::tanggalCantik($key->waktu) }}</span><br>
                                    @if($key->waktumasuk != '') {{ trans('all.masuk').' '.$key->waktumasuk }}<br> @endif
                                    @if($key->waktukeluar != '') {{ trans('all.keluar').' '.$key->waktukeluar }}<br> @endif
                                    @if($key->lamakerja != '') {{ trans('all.lamakerja').' '.\App\Utils::sec2pretty($key->lamakerja) }}<br> @endif
                                    @if($key->lamalembur != '') {{ trans('all.lamalembur').' '.\App\Utils::sec2pretty($key->lamalembur) }} @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            @else
                <center style="margin-top:30px">{{ trans('all.nodata') }}</center>
            @endif
        </div>
        @if($jenis == 'logabsen')
            @if($totaldata > config('consts.LIMIT_FOTO'))
                <div class="col-md-12" id="kelompoktomboldetail">
                    <center>
                        <button id="more" class="btn btn-success" onclick="moreDetail('{{ $jenis }}',{{ $idpegawai }},'{{ $key->startfrom }}',{{ $yymm }})"><i class="fa fa-chevron-circle-down"></i>&nbsp;&nbsp;{{ trans('all.selengkapnya') }}</button>&nbsp;&nbsp;
                    </center>
                </div>
            @endif
        @endif
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
@endif

<script>
    function moreDetail(jenis,idpegawai,startfrom,yymm){
        var url = '';

        if(jenis == 'logabsen'){
            url = '{{ url('logabsen/moredetail/') }}/'+idpegawai+'/'+startfrom+'/'+yymm;
        }else if(jenis == 'rekapabsen'){
            url = '{{ url('rekapabsen/moredetail/') }}/'+idpegawai+'/'+startfrom+'/'+yymm;
        }

        if(url != ''){
            $.ajax({
                type: "GET",
                url: url,
                data: '',
                cache: false,
                success: function(html){
                    $('#kelompoktomboldetail').remove();
                    $('#contentDetailBeranda').append(html);
                }
            });
        }
        return false;
    }
</script>