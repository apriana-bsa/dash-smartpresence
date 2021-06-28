@foreach($data as $key)
    @if($jenis == 'logabsen')
        <div class="col-md-2">
            <center>
                <a href="{{ url('foto'.$jenis.'/'.$key->id.'/normal') }}" title="{{ $namapegawai }}" data-gallery="">
                    <img src="{{ url('foto'.$jenis.'/'.$key->id.'/thumb') }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px"><br>
                </a>
                {!! $key->masukkeluar !!}<br>
                {{ $key->mesin }}
            </center>
            <center title="{{ $key->waktu }}" style="width:100%;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;cursor:default">
                {{ $key->waktu }}
            </center>
            <br>
        </div>
    @elseif($jenis == 'rekapabsen')
        <div class="col-md-4" style="height:140px">
            <table>
                <tr>
                    <td style="padding:5px" valign="top">
                        <a href="{{ url('fotonormal/pegawai/'.$idpegawai) }}" title="{{ $namapegawai }}" data-gallery="">
                            <img src="{{ url('foto/pegawai/'.$idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                        </a>
                    </td>
                    <td style="padding:5px" valign="top">
                        <span style="font-size: 16px;font-weight: bold;">{{ $key->waktu }}</span><br>
                        @if($key->waktumasuk != '') {{ trans('all.masuk').' '.$key->waktumasuk }}<br> @endif
                        @if($key->waktukeluar != '') {{ trans('all.keluar').' '.$key->waktukeluar }}<br> @endif
                        @if($key->lamakerja != '') {{ trans('all.lamakerja').' '.$key->lamakerja.' '.trans('all.menit') }}<br> @endif
                        @if($key->lamalembur != '') {{ trans('all.lamalembur').' '.$key->lamalembur.' '.trans('all.menit') }} @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif
@endforeach
@if($totaldata > config('consts.LIMIT_FOTO'))
    <div class="col-md-12" id="kelompoktomboldetail">
        <center>
            <button id="more" class="btn btn-success" onclick="moreDetail('{{ $jenis }}',{{ $idpegawai }},'{{ $key->startfrom }}')"><i class="fa fa-chevron-circle-down"></i>&nbsp;&nbsp;{{ trans('all.selengkapnya') }}</button>&nbsp;&nbsp;
        </center>
    </div>
@endif