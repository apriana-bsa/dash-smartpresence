<script>
    $(function() {
        $('#simpan').click(function(){
            $('#simpan').attr( 'data-loading', '' );
            $('#simpan').attr('disabled', 'disabled');
            $('#tutupmodal').attr('disabled', 'disabled');

            var idlogabsen = $('#idlogabsen').val();
            var flag = $('#flag').val();
            var flagketerangan = $('#flagketerangan').val();

            $.ajax({
                type: "GET",
                url: '{{ url('generatecsrftoken') }}',
                data: '',
                cache: false,
                success: function (token) {
                    var dataString = 'idlogabsen=' + idlogabsen + '&flag=' + flag + '&flagketerangan=' + flagketerangan + '&_token=' + token;
                    $.ajax({
                        type: "POST",
                        url: '{{ url('flaglogabsen/submit') }}',
                        data: dataString,
                        cache: false,
                        success: function (html) {
                            aktifkanTombol();
                            $('#tutupmodal').removeAttr('disabled');
                            if (html['status'] == 'ok') {
                                alertSuccess(html['msg'],function(){
//                                    setTimeout(function(){
                                        $('#closemodalflag').click();
//                                        $('#closemodal').trigger('click');
//                                    },100);
                                });
                            } else {
                                alertError(html['msg']);
                            }
                        },
                        error: function () {
                            alertError('{{ trans('all.terjadigangguan') }}',
                            function(){
                                aktifkanTombol();
                                $('#tutupmodal').removeAttr('disabled');
                            });
                        }
                    });
                }
            });
        });
    });
</script>
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" id='closemodalflag' data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{ trans('all.'.$menu) . ' ('.$nama.')' }}</h4>
    </div>
    <div class="modal-body body-modal row" id="bodymodaljamkerja" style="white-space: nowrap;overflow: auto;ellipsis;max-height:400px;">
        <table width="100%">
            <tr>
                <td>{{ trans('all.waktu') }}</td>
                <td>: {{ $data->waktu }}</td>
            </tr>
            <tr>
                <td>{{ trans('all.flag') }}</td>
                <td style="float: left;">
                    <input type="hidden" value="{{ $data->id }}" id="idlogabsen">
                    <select class="form-control" id="flag">
                        <option value="" @if($data->flag == '') selected @endif></option>
                        <option value="tidak-terlambat" @if($data->flag == 'tidak-terlambat') selected @endif>{{ trans('all.tidakterlambat') }}</option>
                        @if($menu != 'adadikantor')
                            <option value="tidak-pulangawal" @if($data->flag == 'tidak-pulangawal') selected @endif>{{ trans('all.tidakpulangawal') }}</option>
                        @endif
                        <option value="lembur" @if($data->flag == 'lembur') selected @endif>{{ trans('all.lembur') }}</option>
                        @if($menu != 'adadikantor')
                            <option value="tidak-lembur" @if($data->flag == 'tidak-lembur') selected @endif>{{ trans('all.tidaklembur') }}</option>
                        @endif
                    </select>
                </td>
            </tr>
            <tr>
                <td>{{ trans('all.keterangan') }}</td>
                <td>
                    <textarea style="resize:none" name="flagketerangan" id="flagketerangan" class="form-control">{{ $data->flag_keterangan }}</textarea>
                </td>
            </tr>
        </table>
    </div>
    <div class="modal-footer">
        <table width="100%">
            <tr>
                <td align="right" style="padding:0">
                    <button id="simpan" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button class="btn btn-primary" type="button" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                </td>
            </tr>
        </table>
    </div>
</div>