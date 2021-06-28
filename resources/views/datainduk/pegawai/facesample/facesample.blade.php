<div class="modal-header">
    <button type="button" class="close" id='tombolx' data-dismiss="modal">&times;</button>
    <h4 class="modal-title">{{ trans('all.facesample').' '.$namapegawai->nama }}</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-2" style="width: 120px">
            <a href="{{ url('fotonormal/pegawai/'.$idpegawai) }}" title="{{ $namapegawai->nama }}" data-gallery="">
                <img src="{{ url('foto/pegawai/'.$idpegawai) }}" width=100 height=100 style="border-radius: 50%">
            </a>
            <center style="margin-top:10px;margin-bottom:10px">{{ trans('all.fotoprofil') }}</center>
        </div>
        @if(count($data) > 0)
            @foreach($data as $key)
                <div class="col-md-2" style="width: 120px" id="samplewajah_{{ $key->id }}">
                    <a href="{{ url('getfacesample/'.$key->id) }}" title="{{ $namapegawai->nama }}" data-gallery="">
                        <img src="{{ url('getfacesample/'.$key->id.'/_thumb') }}" width=100 height=100 style="border-radius: 50%">
                    </a>
                    <center style="margin-top:10px;margin-bottom:10px">{{ trans('all.facesample') }}</center>
                    @if(strpos(Session::get('hakakses_perusahaan')->facesample, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->facesample, 'm') !== false)
                        <center style="margin-top:10px;margin-bottom:10px">
                            <button onclick="return hapussample({{ $key->id }})" class="btn btn-primary"><i class="fa fa-trash"></i></button>
                        </center>
                    @endif
                </div>
            @endforeach
        @else
            <center>{{trans('all.nodata') }}</center>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" id='tutupmodal' class="btn btn-primary pull-right" data-dismiss="modal"><i class='fa fa-undo'></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
</div>