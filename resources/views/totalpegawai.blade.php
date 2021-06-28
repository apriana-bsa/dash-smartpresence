@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')

    <script src="{{ asset('lib/js/pinterest_grid.js') }}"></script>
    <script>
    @if(Session::get('message'))
        $(document).ready(function() {
            setTimeout(function() {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 5000,
                    extendedTimeOut: 5000,
                    positionClass: 'toast-bottom-right'
                };
                toastr.success('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
            }, 500);
        });
    @endif

    @if($totaldata > $totaldatalimit)
        $(document).ready(function() {
            var win = $(window);
            var detail = $('#bantuan').attr('detail');
            var startfrom = $('#bantuan').attr('startfrom');
            var run = true;
            // Each time the user scrolls
            win.scroll(function() {
                console.log("masuk 1");
                // End of the document reached?
                if ($(document).height() - win.height() == win.scrollTop()) {
                    console.log("masuk 2");
                    //if ($('._datamore').height() - win.height() == win.scrollTop()) {
                    if(!$('#tab-1').hasClass('active')){
                        if(run == true) {
                            run = false;
                            $('#bantuan').remove();
                            more(detail, startfrom, '{{ $detail }}');
                            return false;
                        }
                    }
                }
            });
        });
    @endif

    var locations = [];
    var map = '';
    var markerCluster = null;
    var markers = [];

    //yang manggil fungsi ini adalah detailmore.blade.php
    function more(jenis,startfrom,detail){
        showSpinner();

        $.ajax({
            type: "GET",
            url: '{{ url('totalpegawai/'.$jenis) }}/'+startfrom,
            data: '',
            cache: false,
            success: function(html){
                hideSpinner();
                $('#moredata').append(html);
            }
        });
        return false;
    }

    $(document).ready(function() {

        setTimeout(function(){ $('#filterAtribut').css('display', ''); }, 1000);

        $('#filterAtribut').BootSideMenu({side:"right"});

        $('#resetfilter').click(function(){
            $('input:checkbox').removeAttr('checked');
            $('input:radio').removeAttr('checked');
        });

        //filter kanan (bootsidemenu)
        $('#submitfilter').click(function(){
            $('#submitfilter').attr('disabled', 'disabled');
            $('#resetfilter').attr('disabled', 'disabled');
            var yyyymmdd = $('#tanggal_kalender').val();
            var newDate = new Date(yyyymmdd.substr(0,4), yyyymmdd.substr(4,2)-1, yyyymmdd.substr(6,2));

            var dataString = new FormData($('#formfilter')[0]);
            $.ajax({
                type: "POST",
                url: "{{ url($detail.'/'.($tanggal == '' ? 'o' : $tanggal)) }}",
                data: dataString,
                async: true,
                cache: false,
                contentType: false,
                processData: false,
                success: function () {

                    setTimeout(function(){
                        defaultCalendar.setStartDate(newDate);
                        $('#submitfilter').removeAttr('disabled');
                        $('#resetfilter').removeAttr('disabled');
                    },10);
                }
            });
            return false;
        });
    });

    function pencarianDetail(){

        $('#moredata').html('');
        showSpinner();

        var pencarian = $('#keyword_pencarian').val();
        var tanggal = $('#tanggal_pencarian').val();
        var tanggalkalender = $('#tanggal_kalender').val();
        var jenis = $('#jenis_pencarian').val();
        var token = $('#token_pencarian').val();

        if(tanggalkalender == ''){
            console.log('tanggal kalender kosong');return false;
        }

        var dataString =  '_token=' + token + '&pencarian=' + pencarian + '&tanggal=' + tanggal + '&tanggalkalender=' + tanggalkalender + '&jenis=' + jenis;

        $.ajax({
            type: "POST",
            url: '{{ url('pencariandetail') }}',
            data: dataString,
            cache: false,
            success: function(html) {
                //console.log(html);
                hideSpinner();

                $('#moredata').html('').append(html);
                //$('#tanggal_kalender').val(newdate);

            }
        });
        return false;
    }

    function showSpinner(){
        $('#loading-saver').css('display', '');
        $('#spinner-loadmore').css('display', '');
    }

    function hideSpinner(){
        $('#loading-saver').css('display', 'none');
        $('#spinner-loadmore').css('display', 'none');
    }
    </script>
    <style>
    .pingrid{
        position: relative;
    }

    .pin{
        position: absolute;
    }

    .row {
        margin-right: 0;
        margin-left: 0;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    #map {
        height: 500px;
    }

    td{
        padding:5px;
    }

    .navbar-static-top {
        z-index: 999;
    }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading" style="margin-right:-15px;margin-left:-15px;">
        <div class="col-lg-8">
            <h2>{{ trans('all.pegawai') }}</h2>
        </div>
        <div class="col-lg-4">
            <div class="search-form">
                <form action="{{ url('pencariandetail') }}" method="post" onsubmit="return pencarianDetail()">
                    <input type="hidden" id="token_pencarian" name="token" value="{{ csrf_token() }}">
                    <input type="hidden" id="jenis_pencarian" name="jenis" value="{{ $detail }}">
                    <input type="hidden" id="tanggal_pencarian" name="tanggal" value="{{ $tanggal == '' ? 'o' : $tanggal }}">
                    <input type="hidden" id="tanggal_kalender" name="tanggalkalender" value="">
                    <div class="input-group" style="margin-top:23px">
                        <input type="text" @if(Session::has($detail.'_pencarian_detail')) value="{{ Session::get($detail.'_pencarian_detail') }}" @endif placeholder="{{ trans('all.pencarian') }}..." autocomplete="off" class="form-control input-sm" name="pencarian" id="keyword_pencarian">
                        <div class="input-group-btn">
                            <input type="submit" class="btn btn-sm btn-primary" value="{{ trans('all.cari') }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id='filterAtribut' style="display: none;">
        <form method="POST" id='formfilter' enctype="multipart/form-data">
            {{ csrf_field() }}
            <table width=100% style="margin-bottom: 60px">
                <tr>
                    <td class='tdheader' style='height:61px;background: #f3f3f4;color:#676a6c;font-size:24px;padding-left:15px;'><i class='fa fa-filter'></i> {{ trans('all.filter') }}</td>
                </tr>
                <tr>
                    <td class="tdfilter">
                        <span style="padding-left:10px">{{ trans('all.jamkerja') }}</span>
                        <div style="padding-left:15px">
                            <table width="100%">
                                <tr>
                                    <td valign="top" style="width:10px;">
                                        @if(Session::has($detail.'_jamkerja'))
                                            <input type="radio" @if(Session::get($detail.'_jamkerja') == 'full') checked @endif id="jamkerjafull" name="jamkerja" value="full">
                                        @else
                                            <input type="radio" id="jamkerjafull" name="jamkerja" value="full">
                                        @endif
                                    </td>
                                    <td valign="top">
                                        <span onclick="spanClick('jamkerjafull')">{{ trans('all.full') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" style="width:10px;">
                                        @if(Session::has($detail.'_jamkerja'))
                                            <input type="radio" @if(Session::get($detail.'_jamkerja') == 'shift') checked @endif id="jamkerjashift" name="jamkerja" value="shift">
                                        @else
                                            <input type="radio" id="jamkerjashift" name="jamkerja" value="shift">
                                        @endif
                                    </td>
                                    <td valign="top">
                                        <span onclick="spanClick('jamkerjashift')">{{ trans('all.shift') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
                @if(isset($atribut))
                    @foreach($atribut as $key)
                        @if(count($key->atributnilai) > 0)
                            <tr>
                                <td class="tdfilter">
                                    <span style="padding-left:10px">{{ $key->atribut }}</span>
                                    @foreach($key->atributnilai as $atributnilai)
                                        @if(Session::has($detail.'_atributfilter'))
                                            {{ $checked = false }}
                                            @for($i=0;$i<count(Session::get($detail.'_atributfilter'));$i++)
                                                @if($atributnilai->id == Session::get($detail.'_atributfilter')[$i])
                                                    <span style="display:none">{{ $checked = true }}</span>
                                                @endif
                                            @endfor
                                            <div style="padding-left:15px">
                                                <table width="100%">
                                                    <tr>
                                                        <td valign="top" style="width:10px;">
                                                            <input type="checkbox" id="atributnilai_{{ $atributnilai->id }}" @if($checked == true) checked @endif name="atributnilai[]" value="{{ $atributnilai->id }}">
                                                        </td>
                                                        <td valign="top">
                                                            <span onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        @else
                                            <div style="padding-left:15px">
                                                <table width="100%">
                                                    <tr>
                                                        <td valign="top" style="width:10px;">
                                                            <input type="checkbox" id="atributnilai_{{ $atributnilai->id }}" name="atributnilai[]" value="{{ $atributnilai->id }}">
                                                        </td>
                                                        <td valign="top">
                                                            <span onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            </table>
            <div style="height:60px;position: fixed;bottom: 0; background-color: #fff">
                <table style="margin-top:10px">
                    <tr>
                        <td class='tdfilter'>
                            <button id="submitfilter" type='button' class="ladda-button btn btn-primary slide-left"><span class="label2">{{ trans('all.lanjut') }}</span> <span class="spinner"></span></button>
                        </td>
                        <td>
                            <button id="resetfilter" type='button' class="ladda-button btn btn-primary slide-left"><span class="label2">{{ trans('all.bersihkan') }}</span> <span class="spinner"></span></button>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    </div>

    <div class="wrapper wrapper-content animated fadeIn">
        <div class="row" style="margin-right:-15px;margin-left:-15px;">
            <div class="col-lg-12">
                <ul class="nav nav-tabs">
                    <li @if($jenis == 'aktif') class="active" @endif><a href="{{ url('totalpegawai/aktif/o') }}">{{ trans('all.aktif') }}</a></li>
                    <li @if($jenis == 'tidakaktif') class="active" @endif><a href="{{ url('totalpegawai/tidakaktif/o') }}">{{ trans('all.tidakaktif') }}</a></li>
                    <li @if($jenis == 'terhapus') class="active" @endif><a href="{{ url('totalpegawai/terhapus/o') }}">{{ trans('all.terhapus') }}</a></li>
                </ul>
                <br>
                <div class="ibox float-e-margins">
                    <div class="ibox-content row" style="margin-right:-15px;margin-left:-15px;">
                        <div id="moredata">
                            @if(count($datas) > 0)
                                @foreach($datas as $data)
                                    <div class="col-md-2" style="margin-bottom:20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                                        <center>
                                            <a href="{{ url('fotonormal/pegawai/'.$data->idpegawai) }}" title="{{ $data->namapegawai }}" data-gallery="">
                                                <img src="{{ url('foto/pegawai/'.$data->idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                                            </a>
                                            <br>
                                            <span title="{{ $data->namapegawai }}">{!! $data->nama !!}</span>
                                            @if($data->atribut != '')
                                                <br><span class="label label-primary">{{ $data->atribut }}</span>
                                            @endif
                                            @if($data->nomorhp != '')
                                                <br><i class="fa fa-phone"></i>&nbsp;&nbsp;{{ $data->nomorhp }}
                                            @else <br>&nbsp; @endif
                                            @if($data->atribut == '') <br>&nbsp; @endif
                                        </center>
                                    </div>
                                @endforeach
                                @if($totaldata > $totaldatalimit)
                                    <span id="bantuan" detail="{{ $detail }}" startfrom="{{ $datas[count($datas)-1]->startfrom }}"></span>
                                @endif
                            @else
                                <center>{{ trans('all.nodata') }}</center><br>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-12"><center id="spinner-loadmore" style="display:none;margin-top:-50px;"><br><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></center></div>
            </div>
        </div>
    </div>

    <!-- Modal pegawai-->
    <a href="" id="showmodalpegawai" data-toggle="modal" data-target="#modalpegawai" style="display:none"></a>
    <div class="modal modalpegawai fade" id="modalpegawai" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-md">

            <!-- Modal content-->
            <div class="modal-content">

            </div>
        </div>
    </div>
    <!-- Modal pegawai-->

@stop

@push('scripts')
<script>
window.detailpegawai=(function(idpegawai){
    $("#showmodalpegawai").attr("href", "");
    $("#showmodalpegawai").attr("href", "{{ url('detailpegawai') }}/"+idpegawai);
    $('#showmodalpegawai').trigger('click');
    return false;
});

$('body').on('hidden.bs.modal', '.modalpegawai', function () {
    $(this).removeData('bs.modal');
    $("#" + $(this).attr("id") + " .modal-content").empty();
    $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
});
</script>
@endpush