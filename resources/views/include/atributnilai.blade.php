<style>
.tdmodalDP{
    padding:3px;
}
</style>
<!-- Modal content-->
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{ trans('all.atributnilai').' ('.$atribut.')' }}</h4>
    </div>
    <div class="modal-body body-modal" style="max-height:300px;overflow:auto">
        <div>
            <table width="100%">
                @if($data != '')
                    @foreach($data as $key)
                        <tr>
                            <td valign="top" class="tdmodalDP">
                                <table>
                                    <tr>
                                        <td class="tdmodalDP">
                                            <input class="atributnilairadio" type="radio" @if($key->kode == '') disabled @endif id="atributnilai_{{$key->id}}" name="atributnilai" value="{{$key->kode}}">
                                        </td>
                                        <td class="tdmodalDP" @if($key->kode != '') style="cursor:pointer" onclick="spanClick('atributnilai_{{$key->id}}')" @else style="cursor: pointer;text-decoration: line-through;color: #ccc" @endif>{{ $key->nama }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <table width="100%">
            <tr>
                <td align="left">
                    <Button onclick="return giveAtributTerpilih('pakaisebagaikondisi')">{{ trans('all.pakaisebagaikondisi') }}</Button>&nbsp;&nbsp;
                    <Button onclick="return giveAtributTerpilih('sisipkankode')">{{ trans('all.sisipkankode') }}</Button>&nbsp;&nbsp;
                    <Button onclick="return giveAtributTerpilih('dapatkannilai')">{{ trans('all.dapatkannilai') }}</Button>
                </td>
                <td align="right" style="padding:0">
                    <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                </td>
            </tr>
        </table>
    </div>
</div>

<script>


function giveAtributTerpilih(jenis) {
    var atributkode = '{{$atributkode}}';

    var atributnilairadio = '';
    $('.atributnilairadio').each(function(i,el){
        if (el.checked==true) {
            atributnilairadio = el.value;
        }
    });

    if (atributnilairadio!='') {
        if(atributkode != ''){
            if (jenis == 'pakaisebagaikondisi') {
                give('in_arrayi("{{$atributkode}}", get($ATRIBUTNILAI, "'+atributnilairadio+'", array()))','{{$formid}}');
            } else if(jenis == 'dapatkannilai') {
                give('getvalue($ATRIBUTNILAI, "'+atributnilairadio+'")','{{$formid}}');
            } else {
                give('get($ATRIBUTNILAI, "'+atributnilairadio+'")','{{$formid}}');
            }
            $('#tutupmodal').trigger('click');
        }
        else {
            alertWarning('{{ trans('all.atributtidakmempunyaikode')}}');
            return false;
        }
    }
    else {
        alertWarning('{{ trans('all.andabelummemilih')}}');
        return false;
    }
}

</script>