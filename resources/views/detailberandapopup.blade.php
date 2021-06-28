<style>
.tdmodalDP{
    padding:3px;
}

.modal-content{
    width:1020px;
}
</style>
<div class="modal-header">
    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
    <h4 class="modal-title">@if($jenis == 'logabsen'){{ trans('all.logabsen') }} @elseif($jenis == 'rekapabsen') {{ trans('all.rekapabsen') }} @endif {{ '('.$namapegawai.')' }}</h4>
</div>
<div class="modal-body body-modal row" id="moredetail">
    @foreach($data as $key)
        @if($jenis == 'logabsen')
            <div class="col-md-2">
                <center>
                    <a href="{{ url('foto'.$jenis.'/'.$key->id) }}" title="{{ $namapegawai }}" data-gallery="">
                        <img src="{{ url('foto'.$jenis.'/'.$key->id) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px"><br>
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
                            @if($key->lamakerja != '') {{ trans('all.lamakerja').' '.\App\Utils::sec2pretty($key->lamakerja) }}<br> @endif
                            @if($key->lamalembur != '') {{ trans('all.lamalembur').' '.\App\Utils::sec2pretty($key->lamalembur) }} @endif
                        </td>
                    </tr>
                </table>
            </div>
        @endif
    @endforeach
    @if($jenis == 'logabsen')
        @if($totaldata > config('consts.LIMIT_FOTO'))
            <div class="col-md-12" id="kelompoktomboldetail">
                <center>
                    <button id="more" class="btn btn-success" onclick="moreDetail('{{ $jenis }}',{{ $idpegawai }},'{{ $key->startfrom }}')"><i class="fa fa-chevron-circle-down"></i>&nbsp;&nbsp;{{ trans('all.selengkapnya') }}</button>&nbsp;&nbsp;
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
<script>
function moreDetail(jenis,idpegawai,startfrom){
    var url = '';

    if(jenis == 'logabsen'){
        url = '{{ url('logabsen/moredetail/') }}/'+idpegawai+'/'+startfrom;
    }else if(jenis == 'rekapabsen'){
        url = '{{ url('rekapabsen/moredetail/') }}/'+idpegawai+'/'+startfrom;
    }

    if(url != ''){
        $.ajax({
            type: "GET",
            url: url,
            data: '',
            cache: false,
            success: function(html){
                $('#kelompoktomboldetail').remove();
                $('#moredetail').append(html);
                console.log(html);
            }
        });
    }
    return false;
}
</script>