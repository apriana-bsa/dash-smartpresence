@foreach($data as $key)
    <div class="col-md-4">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <center @if($key->totalfacesample > 0) onmouseover="hoverFoto('ya','delfoto{{ $key->idpegawai }}')" onmouseleave="hoverFoto('tidak','delfoto{{ $key->idpegawai }}')" @endif>
                    <table>
                        <tr>
                            <td colspan="2" style="padding-bottom:15px"><center title="{!! $key->namalengkap !!}" style="max-width: 300px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><b>{!! $key->nama !!}</b></center></td>
                        </tr>
                        <tr>
                            <td>
                                <img width=120 style='border-radius: 50%;' height=120 src="{{ url('foto/pegawai/'.$key->idpegawai) }}">
                                <center style="margin-top:10px;">{{ trans('all.fotoprofil') }}</center>
                            </td>
                            <td style="padding-left:25px">
                                @if($key->totalfacesample > 0)
                                    <center><span id="delfoto{{ $key->idpegawai }}" style="position: absolute;margin-left:70px;display:none;" onclick="return hapussample({{ $key->idfacesample }})" title="{{ trans('all.hapus') }}"><i style="cursor: pointer;font-size:18px;" class="fa fa-trash"></i></span></center>
                                @endif
                                <img width=120 style='border-radius: 50%;' height=120 src="{{ url('facesample/'.$key->idpegawai.'/1') }}">
                                <center style="margin-top:10px;">{{ trans('all.facesample') }}</center>
                            </td>
                        </tr>
                    </table>
                </center>
                <br>
                @if($key->totalfacesample > 1)
                    <span class="pull-right"><a href="#" onclick="return modalFacesample({{ $key->idpegawai }})">{{ trans('all.selengkapnya').' '.$key->totalfacesample }}</a></span>
                @endif
                <p></p>
            </div>
        </div>
    </div>
@endforeach
@if($totaldata > config('consts.LIMIT_FOTO'))
    <div class="col-md-12" id="more">
        <center style="padding-bottom: 15px;">
            <button id="facesampleloadmore" type="button" class="ladda-button btn btn-primary slide-left" onclick="return loadmore('{{ $startfrom }}')"><span class="label2">{{ trans('all.muatselebihnya') }}</span> <span class="spinner"></span></button>
        </center>
    </div>
@endif