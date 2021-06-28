@if($more == '')
    @if($totaldata > $limitdata)
        <script>
        $(document).ready(function() {
            var win = $('#bodymodaljamkerja');
            //var height = win.height();
            var run = true;
            // Each time the user scrolls
            win.scroll(function() {
                //console.log($(document).height()+' '+height+' '+win.scrollTop());
                // End of the document reached?
                //console.log(height+' '+win.scrollTop()+' '+document.getElementById("bodymodaljamkerja").scrollHeight);
                if (document.getElementById("bodymodaljamkerja").scrollHeight - 400 == win.scrollTop()) {
                    if(run == true) {
                        run = false;
                        loadMore();
                        return false;
                    }
                }
            });

            function loadMore(){
                var startfrom = $('#startfrom').html();
                if(startfrom != '') {
                    $('#startfrom').remove();
                    var url = '{{ url('jamkerjapegawai/'.$tanggal.'/'.$jenis.'/'.$idjamkerja) }}/' + startfrom;
                    $.ajax({
                        type: "GET",
                        url: url,
                        data: '',
                        cache: false,
                        success: function (html) {
                            $('#bodymodaljamkerja').append(html);
                            //height = win.scrollTop() + 430;
                            run = true;
                        }
                    });
                }
            }
        });
        </script>
    @endif
    <style>
        .tdmodalDP{
            padding:3px;
        }
    </style>
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ trans('all.ringkasan').' ('.trans('all.'.$jenis).')' }}</h4>
        </div>
        <div class="modal-body body-modal row" id="bodymodaljamkerja" style="white-space: nowrap;overflow: auto;ellipsis;max-height:400px;">
            <div id="tesdiv">
@endif
            @foreach($data as $key)
                <div class="_datamore col-lg-2 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                    <center>
                        <table>
                            <tr>
                                <td width="110px">
                                    <a href="{{ url('fotonormal/pegawai/'.$key->id) }}" title="{{ $key->nama }}" data-gallery="">
                                        <img src="{{ url('foto/pegawai/'.$key->id) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <span title="{{ $key->nama }}">{{ $key->nama }}</span>
                        @if($jenis == 'sudahabsen')
                            <br><span style="font-size:11px">{{ $key->waktu }}</span>
                        @elseif($jenis == 'belumabsen')
                            @if($key->nomorhp != '')
                                <br><span style="font-size:11px">{{ $key->nomorhp }}</span>
                            @else <br>&nbsp; @endif
                        @endif
                    </center>
                </div>
            @endforeach
            {{--bantuan untuk loadmore--}}
            <span id="startfrom" style="display:none">{{ $totaldata > $limitdata ? $key->startfrom : '' }}</span>
@if($more == '')
            </div>
        </div>
        <div class="modal-footer">
            <table width="100%">
                <tr>
                    <td align="right" style="padding:0">
                        <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endif