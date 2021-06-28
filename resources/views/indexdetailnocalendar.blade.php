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

    @if($detail == 'adadikantor' or $detail == 'totalpegawai')
        @if($totaldata > $totaldatalimit)
            $(document).ready(function() {
                var win = $(window);
                var detail = $('#bantuan').attr('detail');
                var startfrom = $('#bantuan').attr('startfrom');
                var run = true;
                // Each time the user scrolls
                win.scroll(function() {
                    // End of the document reached?
                    if ($(document).height() - win.height() == win.scrollTop()) {
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
    @endif

    //yang manggil fungsi ini adalah detailmore.blade.php
    function more(jenis,startfrom,detail){

        var url = '';

        showSpinner();

        if(jenis == 'adadikantor'){
            url = '{{ url($detail) }}/'+startfrom;
        }else if(jenis == 'totalpegawai'){
            @if(isset($jenis))
                url = '{{ url('totalpegawai/'.$jenis) }}/'+startfrom;
            @else
                url = '{{ url('totalpegawai/aktif') }}/'+startfrom;
            @endif
        }else if(jenis == 'ijintidakmasuk'){
            url = '{{ url($detail) }}/'+startfrom;
        }else{
            var tanggal = $('#tanggal_kalender').val();
            url = '{{ url($detail) }}/'+tanggal+'/'+startfrom;
        }

        if(url != ''){
            $.ajax({
                type: "GET",
                url: url,
                data: '',
                cache: false,
                success: function(html){
                    hideSpinner();
                    if(jenis == 'ijintidakmasuk'){
                        $('#moredata_2').append(html);
                    }else{
                        if(detail == 'sudahabsen' || detail == 'belumabsen'){
                            $('#tab-2').append(html);
                        }else{
                            $('#moredata').append(html);
                        }
                    }
                }
            });
        }
        return false;
    }

    //menu rekap kehadiran
    function hapussample(id){
        alertConfirm("{{ trans('all.apakahyakinakanmenghapusfacesampleini') }} ?",
                function(){
                    //window.location.href="{{ url('rekap/deletefacesample/') }}/"+id+'/{{ $tanggal == '' ? 'o' : $tanggal }}';
                    var url = "{{ url('rekap/deletefacesample/') }}/"+id+'/{{ $tanggal == '' ? 'o' : $tanggal }}';
                    $.ajax({
                        type: "GET",
                        url: url,
                        data: '',
                        cache: false,
                        success: function(html){
                            if(html['status'] == 'OK'){
                                $('#samplewajah_'+html['data']).remove();
                            }else{
                                alertError(html['msg']);
                            }
                        }
                    });
                }
        );
    }

    $(document).ready(function() {

        @if($detail != 'datacapture')
            setTimeout(function(){ $('#filterAtribut').css('display', ''); }, 1000);
        @endif

        $('#filterAtribut').BootSideMenu({side:"right"});

        $('#resetfilter').click(function(){
            $('input:checkbox').removeAttr('checked');
            $('input:radio').removeAttr('checked');
        });

        //menu rekap kehadiran
        window.modalFacesample=(function(id){
            $("#showmodalfacesample").attr("href", "");
            $("#showmodalfacesample").attr("href", "{{ url('datainduk/pegawai/facesample/all') }}/"+id);
            $('#showmodalfacesample').trigger('click');
            return false;
        });

        $('body').on('hidden.bs.modal', '.modalfacesample', function () {
            $(this).removeData('bs.modal');
            $("#" + $(this).attr("id") + " .modal-content").empty();
            $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
        });

        //menu rekap kehadiran
        window.modalDetailrekap=(function(id){
            $("#showmodaldetailrekap").attr("href", "");
            $("#showmodaldetailrekap").attr("href", "{{ url('detail/rekap') }}/"+id);
            $('#showmodaldetailrekap').trigger('click');
            return false;
        });

        $('body').on('hidden.bs.modal', '.modaldetailrekap', function () {
            $(this).removeData('bs.modal');
            $("#" + $(this).attr("id") + " .modal-content").empty();
            $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
        });

        window.modalRiwayatPresensi=(function(idpegawai){
            $("#showmodalriwayatpresensi").attr("href", "");
            $("#showmodalriwayatpresensi").attr("href", '{{ url('logabsen') }}/'+idpegawai+'/o/o');
            $('#showmodalriwayatpresensi').trigger('click');
            return false;
        });

        $('body').on('hidden.bs.modal', '.modalriwayatpresensi', function () {
            $(this).removeData('bs.modal');
            $("#" + $(this).attr("id") + " .modal-content").empty();
            $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
        });

        window.modalRekapPresensi=(function(idpegawai){
            $("#showmodalrekappresensi").attr("href", "");
            $("#showmodalrekappresensi").attr("href", '{{ url('rekapabsen') }}/'+idpegawai+'/o/o');
            $('#showmodalrekappresensi').trigger('click');
            return false;
        });

        window.modalFlag=(function(idpegawai,idlogabsen,menu){
            $("#showmodalrekappresensi").attr("href", "");
            $("#showmodalrekappresensi").attr("href", '{{ url('flaglogabsen') }}/'+idpegawai+'/'+idlogabsen+'/'+menu);
            $('#showmodalrekappresensi').trigger('click');
            return false;
        });

        $('body').on('hidden.bs.modal', '.modalrekappresensi', function () {
            $(this).removeData('bs.modal');
            $("#" + $(this).attr("id") + " .modal-content").empty();
            $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
        });

        //untuk menu ijintidakmasuk
        $('.pingrid').pinterest_grid({
            no_columns: 3,
            padding_x: 10,
            padding_y: 10,
            margin_bottom: 50,
            single_column_breakpoint: 700
        });

        $('#submitfilter').click(function(){
            $('#submitfilter').attr('disabled', 'disabled');
            $('#resetfilter').attr('disabled', 'disabled');
            var yyyymmdd = $('#tanggal_kalender').val();
            var newDate = new Date(yyyymmdd.substr(0,4), yyyymmdd.substr(4,2)-1, yyyymmdd.substr(6,2));

            @if($detail == 'totalpegawai')
                var url = '{{ url($detail.'/'.$jenis.'/'.($tanggal == '' ? 'o' : $tanggal)) }}';
            @else
                var url = '{{ url($detail.'/'.($tanggal == '' ? 'o' : $tanggal)) }}';
            @endif

            var dataString = new FormData($('#formfilter')[0]);
            $.ajax({
                type: "POST",
                url: url,
                data: dataString,
                async: true,
                cache: false,
                contentType: false,
                processData: false,
                success: function (html) {
                    console.log(html);
                    setTimeout(function(){
                        @if($detail != 'adadikantor' and $detail != 'totalpegawai' and $detail != 'ijintidakmasuk')
                            defaultCalendar.setStartDate(newDate);
                        @else
                            $('#moredata').html('').append(html);
                        @endif
                        $('#submitfilter').removeAttr('disabled');
                        $('#resetfilter').removeAttr('disabled');
                    },10);
                }
            });
            return false;
        });
    });

    function modalMarkerPeta(marker,startfrom,jenis){

        var dataString = 'marker='+marker+'&startfrom='+startfrom+'&_token={{ csrf_token() }}';

        $.ajax({
            type: "POST",
            url: '{{ url('modalmarkerpeta') }}',
            data: dataString,
            cache: false,
            success: function(html){
                if(jenis == 'modal') {
                    setTimeout(function () {
                        $('#isimodalpeta').append(html);
                    }, 200);
                }else{
                    $('#isimodalpeta').html('');
                    $('#showmodalpeta').trigger('click');
                    setTimeout(function () {
                        $('#isimodalpeta').html(html);
                    }, 200);
                }
            }
        });
    }

    function pencarianDetail(){

        $('#moredata').html('');
        showSpinner();

        var pencarian = $('#keyword_pencarian').val();
        var tanggal = $('#tanggal_pencarian').val();
        var tanggalkalender = $('#tanggal_kalender').val();
        var jenis = $('#jenis_pencarian').val();
        var jenispegawai = $('#jenis_pegawai').val();
        var token = $('#token_pencarian').val();

        var dataString =  '_token=' + token + '&pencarian=' + pencarian + '&tanggal=' + tanggal + '&tanggalkalender=' + tanggalkalender + '&jenis=' + jenis + '&jenis_pegawai=' + jenispegawai;

        $.ajax({
            type: "POST",
            url: '{{ url('pencariandetail') }}',
            data: dataString,
            cache: false,
            success: function(html) {
                console.log(html);
                hideSpinner();
                $('#moredata').html('').append(html);
                //$('#tanggal_kalender').val(newdate);
            }
        });
        return false;
    }

    function gantiBulan(jenis,idpegawai){
        var yymm = $('#bulanDetailBeranda').val();
        //alert(jenis+'|'+idpegawai+'|'+yymm);

        var url = '';
        if(jenis == 'logabsen'){
            url = '{{ url('logabsen') }}/'+idpegawai+'/o/'+yymm;
        }else if(jenis == 'rekapabsen') {
            url = '{{ url('rekapabsen') }}/' + idpegawai + '/o/' + yymm;
        }

        if(url != '') {
            $('#contentDetailBeranda').html('');
            $('#contentDetailBeranda').html('<div class="col-lg-12"><center id="spinnerDetailBeranda"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></center></div>');

            $.ajax({
                type: "GET",
                url: url,
                data: '',
                cache: false,
                success: function (html) {
                    $('#contentDetailBeranda').html('');
                    $('#contentDetailBeranda').html(html);
                    //alert(html);
                }
            });
        }
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

    @if($detail == 'adadikantor' or $detail == 'totalpegawai' or $detail == 'ijintidakmasuk')
        setTimeout(function() {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                timeOut: 4000,
                extendedTimeOut: 4000,
                positionClass: 'toast-bottom-right'
            };
            toastr.info('{{ $totaldata.' '.trans('all.pegawai') }}', '{{ trans('all.totaldata') }}');
        }, 500);
    @endif
    </script>
    <style>
    .pingrid{
        position: relative;
    }

    .pin{
        position: absolute;
    }

    {{-- .row {
        margin-right: 0;
        margin-left: 0;
    } --}}

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
            <h2>{{ trans('all.beranda_'.$detail) }}</h2>
        </div>
        <div class="col-lg-4">
            <div class="search-form" @if($detail == 'datacapture') style="display:none" @endif>
                <form action="{{ url('pencariandetail') }}" method="post" onsubmit="return pencarianDetail()">
                    <input type="hidden" id="token_pencarian" name="token" value="{{ csrf_token() }}">
                    <input type="hidden" id="jenis_pencarian" name="jenis" value="{{ $detail }}">
                    <input type="hidden" id="tanggal_pencarian" name="tanggal" value="">
                    <input type="hidden" id="tanggal_kalender" name="tanggalkalender" value="">
                    <input type="hidden" id="jenis_pegawai" name="jenis_pegawai" value="{{ isset($jenis) ? $jenis : '' }}">
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
                <tr>
                    <td class="tdfilter">
                        <span style="padding-left:10px">{{ trans('all.kategorijamkerja') }}</span>
                        <div style="padding-left:15px">
                            @if($jamkerjakategori != '')
                                @foreach($jamkerjakategori as $key)
                                    {{ $checked = false }}
                                    @if(Session::has($detail.'_kategorijamkerja'))
                                        @for($i=0;$i<count(Session::get($detail.'_kategorijamkerja'));$i++)
                                            @if($key->id == Session::get($detail.'_kategorijamkerja')[$i])
                                                <span style="display:none">{{ $checked = true }}</span>
                                            @endif
                                        @endfor
                                    @endif
                                    <table width="100%">
                                        <tr>
                                            <td valign="top" style="width:10px;">
                                                <input type="checkbox" id="kategorijamkerja_{{ $key->id }}" @if($checked == true) checked @endif name="kategorijamkerja[]" value="{{ $key->id }}">
                                            </td>
                                            <td valign="top">
                                                <span onclick="spanClick('kategorijamkerja_{{ $key->id }}')">{{ $key->nama }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                @endforeach
                            @endif
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
                @if($detail == 'totalpegawai')
                    <ul class="nav nav-tabs">
                        <li @if($jenis == 'aktif') class="active" @endif><a href="{{ url('totalpegawai/aktif/o') }}">{{ trans('all.aktif') }}</a></li>
                        <li @if($jenis == 'tidakaktif') class="active" @endif><a href="{{ url('totalpegawai/tidakaktif/o') }}">{{ trans('all.tidakaktif') }}</a></li>
                        <li @if($jenis == 'terhapus') class="active" @endif><a href="{{ url('totalpegawai/terhapus/o') }}">{{ trans('all.terhapus') }}</a></li>
                    </ul>
                    <br>
                @elseif($detail == 'adadikantor')
                    <ul class="nav nav-tabs" style="margin-left: -15px;margin-right:-15px">
                        <li class="active"><a data-toggle="tab" href="#tab-1">{{ trans('all.lokasi') }}</a></li>
                        <li><a data-toggle="tab" href="#tab-2">{{ trans('all.detail') }}</a></li>
                    </ul>
                    <br>
                @endif

                @if($detail == 'adadikantor' or $detail == 'totalpegawai')
                    <div class="ibox float-e-margins">
                        <div class="ibox-content row" style="margin-right:-15px;margin-left:-15px;">
                            <div class="tab-content">
                                <div id="tab-1" class="tab-pane @if($detail == 'adadikantor') active @endif">
                                    @if($detail == 'adadikantor')
                                        <table width=100% class="table datatable_ringkasan table-condensed table-hover">
                                            <thead>
                                            <tr>
                                                <td class="nama"><b>{{ trans('all.lokasi') }}</b></td>
                                                <td class="opsi2"><b>{{ trans('all.jumlah') }}</b></td>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($dataperlokasi as $key)
                                                <tr>
                                                    <td>{{ $key->lokasi }}</td>
                                                    <td>{{ $key->jumlah.' '.trans('all.orang') }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        <script>
                                            $(function() {
                                                $('.datatable_ringkasan').DataTable({
                                                    bStateSave: true,
                                                });
                                            });
                                        </script>
                                    @endif
                                </div>
                                <div id="tab-2" class="tab-pane @if($detail == 'totalpegawai') active @endif">
                                    <div id="moredata">
                                        @if(count($datas) > 0)
                                            @foreach($datas as $data)
                                                <div class="col-md-2" style="margin-bottom:20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                                                    <center>
                                                        <table>
                                                            <tr>
                                                                <td width="110px">
                                                                    <a href="{{ url('fotonormal/pegawai/'.$data->idpegawai) }}" title="{{ $data->namapegawai }}" data-gallery="">
                                                                        <img src="{{ url('foto/pegawai/'.$data->idpegawai) }}" width="110px" height="110px" style="border-radius:50%;margin-bottom:5px">
                                                                    </a>
                                                                    {{--<a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color:#ccc;position:absolute;">--}}
                                                                        {{--<i class="fa fa-ellipsis-v" style="padding-right: 8px;padding-left: 8px;"></i>--}}
                                                                    {{--</a>--}}
                                                                    {{--<ul class="dropdown-menu advancemenu1 pull-right">--}}
                                                                        {{--<li class="header"><a href="#" onclick="return modalFacesample({{  $data->idpegawai }})">{{ trans('all.facesample') }}</a></li>--}}
                                                                        {{--<li class="divider"></li>--}}
                                                                        {{--<li class="header"><a href="#" onclick="return modalRiwayatPresensi({{  $data->idpegawai }})">{{ trans('all.riwayatpresensi') }}</a></li>--}}
                                                                        {{--<li class="header"><a href="#" onclick="return modalRekapPresensi({{  $data->idpegawai }})">{{ trans('all.rekappresensi') }}</a></li>--}}
                                                                    {{--</ul>--}}
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <span title="{{ $data->namapegawai }}">{!! $data->nama !!}</span>
                                                        @if($detail == 'adadikantor')
                                                            <br>{{ trans('all.sejak') }} <span style="font-size:11px;">{{ $data->sejak }}</span><br>
                                                            {{ trans('all.lokasi') }} <span style="font-size:11px" title="{{ $data->lokasi }}">{{ $data->lokasi != '' ? $data->lokasi : '-' }}</span><p></p>
                                                            <button class="btn btn-primary" onclick="return modalFlag({{  $data->idpegawai }},'{{ $data->idlogabsen }}','adadikantor')"><i class="fa fa-flag"></i>&nbsp;&nbsp;{{ trans('all.flag') }}</button>
                                                        @elseif($detail == 'totalpegawai')
                                                            @if($data->atribut != '')
                                                                {{--<br><span class="label label-primary">{{ $data->atribut }}</span>--}}
                                                                <br>{!! \App\Utils::dataExplode("|", $data->atribut,true) !!}
                                                            @endif
                                                            @if($data->nomorhp != '')
                                                                <br><i class="fa fa-phone"></i>&nbsp;&nbsp;{{ $data->nomorhp }}
                                                            @else <br>&nbsp; @endif
                                                            @if($data->atribut == '') <br>&nbsp; @endif
                                                        @endif
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
                        </div>
                    </div>
                @elseif($detail == 'ijintidakmasuk')
                    @if(count($datas) > 0)
                        <div class="pingrid" id="moredata">
                            @foreach($datas as $data)
                                <div class="col-md-4 pin" style="padding-bottom:20px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                                    <div class="ibox-content">
                                        <table width="100%">
                                            <tr>
                                                <td style="padding:5px;width:50px" valign="top">
                                                    <a href="{{ url('fotonormal/pegawai/'.$data->idpegawai) }}" title="{{ $data->namapegawai }}" data-gallery="">
                                                        <img src="{{ url('foto/pegawai/'.$data->idpegawai) }}" width="50px" height="50px" style="border-radius:50%;margin-bottom:5px">
                                                    </a>
                                                </td>
                                                <td style="padding:5px;" valign="top">
                                                    <span title="{{ $data->namapegawai }}" style="max-width:250px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">{!! $data->nama !!}</span><br>
                                                    <i class="fa fa-phone"></i>&nbsp;&nbsp;{{ $data->nomorhp }}<br>
                                                    <span class="label label-primary">{{ $data->atribut }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><hr style="margin-top:5px;margin-bottom:5px"></td>
                                            </tr>
                                            @foreach($data->ijintidakmasuk as $ijintidakmasuk)
                                                <tr>
                                                    <td style="padding:5px;" valign="top" colspan="2">
                                                        <table width="100%">
                                                            <tr>
                                                                <td>
                                                                    <span style="font-size: 16px;font-weight: bold;">{{ \App\Utils::tanggalCantikDariSampai($ijintidakmasuk->tanggalawal,$ijintidakmasuk->tanggalakhir) }}</span>
                                                                </td>
                                                                <td align="right">
                                                                    {{ $ijintidakmasuk->alasantidakmasuk }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    {{ $ijintidakmasuk->keterangan }}
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="ibox-content row">
                            <center>{{ trans('all.nodata') }}</center><br>
                        </div>
                    @endif
                @endif
                <div class="col-lg-12"><center id="spinner-loadmore" style="display:none;margin-top:-30px;margin-bottom:10px"><br><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></center></div>
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

    <!-- Modal facesample-->
    <a href="" id="showmodalfacesample" data-toggle="modal" data-target="#modalFacesample" style="display:none"></a>
    <div class="modal modalfacesample fade" id="modalFacesample" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">

            </div>
        </div>
    </div>
    <!-- Modal facesample-->

    <!-- Modal riwayatpresensi-->
    <a href="" id="showmodalriwayatpresensi" data-toggle="modal" data-target="#modalriwayatpresensi" style="display:none"></a>
    <div class="modal modalriwayatpresensi fade" id="modalriwayatpresensi" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-sm" style="width:420px">

            <!-- Modal content-->
            <div class="modal-content">

            </div>
        </div>
    </div>
    <!-- Modal riwayatpresensi-->

    <!-- Modal rekappresensi-->
    <a href="" id="showmodalrekappresensi" data-toggle="modal" data-target="#modalrekappresensi" style="display:none"></a>
    <div class="modal modalrekappresensi fade" id="modalrekappresensi" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-sm" style="width:420px">

            <!-- Modal content-->
            <div class="modal-content">

            </div>
        </div>
    </div>
    <!-- Modal rekappresensi-->
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
