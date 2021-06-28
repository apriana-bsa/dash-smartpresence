@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')
    
    <script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>
    <script src="{{ asset('lib/js/pinterest_grid.js') }}"></script>
    <script>
    var defaultCalendar;
    //jquery
    function dateDiffInDays(a, b) {
        const _MS_PER_DAY = 1000 * 60 * 60 * 24;

        // Discard the time and time-zone information.
        const utc1 = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate());
        const utc2 = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate());

        return Math.floor((utc2 - utc1) / _MS_PER_DAY);
    }
    $(function(){
        @if($detail != 'datacapture')
            setTimeout(function(){ $('#filterAtribut').css('display', ''); }, 1000);
            $('#filterAtribut').BootSideMenu({side:"right"});

            $('#resetfilter').click(function(){
                $('input:checkbox').removeAttr('checked');
                $('input:radio').removeAttr('checked');
            });
        @endif

        var newDate = new Date({{ date('Y') }}, {{ date('m') }}-1, {{ date('d') }});
        var selisihDay=0;
        @if($tanggal != '')
            var tempDate = new Date({{ substr($tanggal, 0, -4) }}, {{ substr($tanggal, 4, -2) }}-1, {{ substr($tanggal, -2) }});
            if (newDate!=tempDate) {
                var tempSelisih = dateDiffInDays(newDate, tempDate);
                if (tempSelisih>=0) {
                    selisihDay = Math.min(1, tempSelisih)+1;
                }
                newDate = tempDate;
            }
        @endif
        var enddate = moment().add('days', selisihDay);

//        console.log(moment().add('days', 0));
//        console.log(moment(newDate));
//
//        console.log(moment("10/10/2018", "DD/MM/YYYY"));

        @if(!isset($navigasitanggal) or $navigasitanggal == 'y')
            defaultCalendar = $("#datepicker").rangeCalendar({changeRangeCallback: rangeChanged,lang:"{{ Session::get('conf_bahasaperusahaan') }}"});
            setTimeout(function(){ defaultCalendar.setStartDate(newDate); },200);
        @else
            getData('{{ $detail }}','');
        @endif

        //function yg membutuhkan modal(pop up)
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
            $("#showmodalrekappresensi").attr("href", "");
            $("#showmodalrekappresensi").attr("href", '{{ url('logabsen') }}/'+idpegawai+'/o/o');
            $('#showmodalrekappresensi').trigger('click');
            return false;
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
    });

    //yang manggil fungsi ini adalah detailmore.blade.php
    function more(jenis,startfrom,detail){
        showSpinner();
        var tanggal = $('#tanggal_kalender').val();
        var url = '{{ url($detail) }}/'+tanggal+'/'+startfrom;
        $.ajax({
            type: "GET",
            url: url,
            data: '',
            cache: false,
            success: function(html){
                hideSpinner();
                if(detail == 'sudahabsen' || detail == 'belumabsen'){
                    $('#tab-3').append(html);
                }else{
                    $('#moredata').append(html);
                }
            }
        });
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

    function rangeChanged(el, cont, dateProp) {
        getData('{{ $detail }}',cont.start);
    }

    function getData(menu,tanggal){
        var url = '';
        if(menu == 'customdashboard'){
            @if($detail == 'customdashboard')
                url = '{{ url('customdashboarddata') }}/{{ $customdashboard_node->id }}/'+tanggal+'/@if(isset($keys)){{ $keys }}@endif';
            @endif
        }else{
            url = '{{ url($detail) }}/'+tanggal+'/o';
        }

        $('#moredata').html('');
        showSpinner();

        $.ajax({
            type: "GET",
            url: url,
            data: '',
            cache: false,
            success: function(html){
//                console.log(html);
                hideSpinner();
                $('#moredata').html('').append(html);
                $('#tanggal_kalender').val(tanggal);
            }
        });
    }

    function submitFilter(){
        $('#submitfilter').attr('disabled', 'disabled');
        $('#resetfilter').attr('disabled', 'disabled');
        var yyyymmdd = $('#tanggal_kalender').val();
        var newDate = new Date(yyyymmdd.substr(0,4), yyyymmdd.substr(4,2)-1, yyyymmdd.substr(6,2));

        @if($detail == 'customdashboard')
            var url = '{{ url($detail.'/'.$customdashboard_node->id.'/'.($tanggal == '' ? 'o' : $tanggal)) }}';
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
                //console.log(html);
                setTimeout(function(){
                    @if(!isset($navigasitanggal) or $navigasitanggal == 'y')
                        defaultCalendar.setStartDate(newDate);
                    @else
                        getData('{{ $detail }}','{{ $tanggal }}');
                    @endif
                    $('#submitfilter').removeAttr('disabled');
                    $('#resetfilter').removeAttr('disabled');
                },10);
            }
        });
        return false;
    }

    //fungsi untuk ganti bulan di modal(pop up) rekappresensi dan riwayat presensi
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

    function pencarianDetail(){

        $('#moredata').html('');
        showSpinner();

        var pencarian = $('#keyword_pencarian').val();
        var tanggal = $('#tanggal_kalender').val();
        var jenis = $('#jenis_pencarian').val();
        var keys = $('#keys_pencarian').val();
        var token = $('#token_pencarian').val();

        if(jenis == 'customdashboard') {
            @if($detail == 'customdashboard')
                var dataString = '_token=' + token + '&pencarian=' + pencarian + '&tanggal=' + tanggal + '&jenis=' + jenis + '&keys=' + keys + '&idcustomdashboard_node={{ $customdashboard_node->id }}';
            @endif
        }else {
            var dataString = '_token=' + token + '&pencarian=' + pencarian + '&tanggal=' + tanggal + '&jenis=' + jenis;
        }
//        console.log(dataString);
        $.ajax({
            type: "POST",
            url: '{{ url('pencariandetail') }}',
            data: dataString,
            cache: false,
            success: function(html) {
                // console.log(html);
                hideSpinner();
                if (jenis == 'peta'){
                    //hapus marker lama
                    if (markerCluster != null) {
                        markerCluster.clearMarkers();
                    }
                    //set zoom map
                    map.setZoom(5);
                    //set lokasi map ke awal
                    map.setCenter({lat: -4.653079918274038, lng: 117.7734375});

                    if (html != '') {
                        locations = html;

                        var icon = {
                            url: '{{ url('lib/drawable-xhdpi/flag_orang_absen.png') }}', // url
                            scaledSize: new google.maps.Size(40, 40), // scaled size
                            origin: new google.maps.Point(0, 0), // origin
                            anchor: new google.maps.Point(10, 35) // anchor
                        };

                        //kasih marker baru
                        markers = locations.map(function (location) {
                            //console.log(location);
                            return new google.maps.Marker({
                                position: location,
                                map: map,
                                flag: 'log',
                                id: location.id,
                                icon: icon
                            });
                        });

                        for (var i = 0; i < markers.length; i++) {
                            google.maps.event.addDomListener(markers[i], 'click', function () {
                                modalMarkerPeta(this.id,'o','bukanmodal');
                            });
                        }


                        markerCluster = new MarkerClusterer(map, markers,
                            {
                                zoomOnClick: false,
                                imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
                            });

                        google.maps.event.addListener(markerCluster, "clusterclick", function (cluster) {
                            var _marker = '';
                            for (var i = 0; i < cluster.markers_.length; i++) {
                                _marker += ',' + cluster.markers_[i].id;
                            }
                            modalMarkerPeta(_marker.substring(1),'o','bukanmodal');

                        });
                    }
                    //initMap();
                }else{
                    $('#moredata').html('').append(html);
                    //$('#tanggal_kalender').val(newdate);
                }
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
            @if($detail != 'customdashboard')
                <h2>
                @if($detail == 'rekap')
                    {{ trans('all.beranda_rekapitulasi') }}
                @elseif($detail == 'terlambat')
                    {{ trans('all.terlambat') }}
                @else
                    {{ trans('all.beranda_'.$detail) }}
                @endif
                </h2>
            @else
                <h2>{{ $customdashboard_node->judul }}</h2>
            @endif
        </div>
        <div class="col-lg-4">
            <div class="search-form" @if($detail == 'datacapture') style="display:none" @endif>
            {{--<div class="search-form" style="display:none">--}}
                <form action="{{ url('pencariandetail') }}" method="post" onsubmit="return pencarianDetail()">
                    <input type="hidden" id="token_pencarian" name="token" value="{{ csrf_token() }}">
                    <input type="hidden" id="jenis_pencarian" name="jenis" value="{{ $detail }}">
                    <input type="hidden" id="keys_pencarian" name="key" value="@if(isset($keys)){{ $keys }}@endif">
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
                @if($detail != 'customdashboard')
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
                @endif
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
                            <button id="submitfilter" onclick="return submitFilter()" type='button' class="ladda-button btn btn-primary slide-left"><span class="label2">{{ trans('all.lanjut') }}</span> <span class="spinner"></span></button>
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
                @if($detail == 'customdashboard')
                    {{--@if($customdashboard_node->query_master_periode == 'navigasi-tanggal')--}}
                    @if(!isset($navigasitanggal) or $navigasitanggal == 'y')
                        <div id="datepicker" style="width:100%;"></div>
                    @endif
                    <div id="moredata"></div>
                @elseif($detail == 'sudahabsen' or $detail == 'belumabsen' or $detail == 'terlambat' or $detail == 'datangawal' or $detail == 'datacapture')
                    <div class="ibox float-e-margins">
                        <div class="ibox-content row" style="margin-right:-15px;margin-left:-15px;">
                            <div id="datepicker" style="width:100%;"></div>
                            <div id="moredata"></div>
                        </div>
                    </div>
                @elseif($detail == 'pulangawal' or $detail == 'riwayat' or $detail == 'rekap')
                    <div id="datepicker" style="width:100%;"></div>
                    <div id="moredata"></div>
                @endif
                <div class="col-lg-12"><center id="spinner-loadmore" style="display:none;margin-bottom:10px"><br><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></center></div>
            </div>
        </div>
    </div>

    <!-- Modal peta-->
    <a href="" id="showmodalpeta" data-toggle="modal" data-target="#modalpeta" style="display:none"></a>
    <div class="modal modalpeta fade" id="modalpeta" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-sm" style="width:435px">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('all.'.$detail) }}</h4>
                </div>
                <div class="modal-body body-modal" id="modalbody-peta" style="max-height:480px;overflow: auto;">
                    <div id="isimodalpeta"></div>
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
        </div>
    </div>
    <!-- Modal peta-->

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

    <!-- Modal detailrekap-->
    <a href="" id="showmodaldetailrekap" data-toggle="modal" data-target="#modaldetailrekap" style="display:none"></a>
    <div class="modal modaldetailrekap fade" id="modaldetailrekap" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-sm" style="width:480px">

            <!-- Modal content-->
            <div class="modal-content">

            </div>
        </div>
    </div>
    <!-- Modal detailrekap-->

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
    <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script>
@stop
