@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')

    <script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>
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

        var locations = [];
        var map = '';
        var markerCluster = null;
        var markers = [];

        //yang manggil fungsi ini adalah detailmore.blade.php
        function more(jenis,startfrom,detail){

            var url = '';

            showSpinner();

            if(jenis == 'adadikantor'){
                url = '{{ url($jenis) }}/'+startfrom;
            }else if(jenis == 'totalpegawai'){
                url = '{{ url($jenis) }}/'+startfrom;
            }else if(jenis == 'ijintidakmasuk'){
                url = '{{ url($jenis) }}/'+startfrom;
            }else{
                var tanggal = $('#tanggal_kalender').val();
                url = '{{ url($jenis) }}/'+tanggal+'/'+startfrom;
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

        $(document).ready(function() {

            setTimeout(function(){ $('#filterAtribut').css('display', ''); }, 1000);

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

            $('body').on('hidden.bs.modal', '.modalrekappresensi', function () {
                $(this).removeData('bs.modal');
                $("#" + $(this).attr("id") + " .modal-content").empty();
                $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
            });

            //filter kanan (bootsidemenu)
            {{--$('#submitfilter').click(function(){--}}
                {{--$('#submitfilter').attr('disabled', 'disabled');--}}
                {{--$('#resetfilter').attr('disabled', 'disabled');--}}
                {{--var yyyymmdd = $('#tanggal_kalender').val();--}}
                {{--var newDate = new Date(yyyymmdd.substr(0,4), yyyymmdd.substr(4,2)-1, yyyymmdd.substr(6,2));--}}

                {{--var dataString = new FormData($('#formfilter')[0]);--}}
                {{--$.ajax({--}}
                    {{--type: "POST",--}}
                    {{--url: "{{ url('notifdetail/'.$jenis) }}",--}}
                    {{--data: dataString,--}}
                    {{--async: true,--}}
                    {{--cache: false,--}}
                    {{--contentType: false,--}}
                    {{--processData: false,--}}
                    {{--success: function () {--}}

                        {{--setTimeout(function(){--}}
                            {{--defaultCalendar.setStartDate(newDate);--}}
                            {{--$('#submitfilter').removeAttr('disabled');--}}
                            {{--$('#resetfilter').removeAttr('disabled');--}}
                        {{--},10);--}}
                    {{--}--}}
                {{--});--}}
                {{--return false;--}}
            {{--});--}}
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

        function setPilihan(id) {
            var notifterpilih = $('#notifterpilih_'+id).prop('checked');
            if(notifterpilih == true){
                $('#notifterpilih_'+id).prop('checked', true);
                prepareCeksemua();
                $('#cardnotif_'+id).css('background-color','#cccccc');
            }else{
                $('#notifterpilih_'+id).prop('checked', false);
                prepareCeksemua();
                $('#cardnotif_'+id).css('background-color','#ffffff');
            }
        }

        //tf = true/false
        function prepareCeksemua(){
            var hasil = '';
            $('.notifterpilih').each(function(){
                if($(this).prop('checked') == true){
                    hasil += '1';
                }else{
                    hasil += '0';
                }
            });
            if(hasil.indexOf('0') == -1){
                $('#ceksemua').css('display','none');
                $('#hapusceksemua').css('display','');
            }else{
                $('#ceksemua').css('display','');
                $('#hapusceksemua').css('display','none');
            }
        }

        function ceksemua(tf){
            $('.notifterpilih').prop('checked', tf);
            prepareCeksemua();
//            if(prepareCeksemua(tf) == true){
            if(tf == true){
                $('.cardnotif').css('background-color','#cccccc');
            }else{
                $('.cardnotif').css('background-color','#ffffff');
            }
        }
    </script>
    <style>
    .tdmodalDP{
        padding:3px;
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
            <h2>{{ trans('all.'.$jenis) }}</h2>
        </div>
        <div class="col-lg-4">
        </div>
    </div>

    <div id='filterAtribut' style="display: none;">
        <form method="POST" id='formfilter' enctype="multipart/form-data">
            {{ csrf_field() }}
            <table width=100% style="margin-bottom: 60px">
                <tr>
                    <td class='tdheader' style='height:61px;background: #f3f3f4;color:#676a6c;font-size:24px;padding-left:15px;'><i class='fa fa-filter'></i> {{ trans('all.filter') }}</td>
                </tr>
                @if(isset($atribut))
                    @foreach($atribut as $key)
                        @if(count($key->atributnilai) > 0)
                            <tr>
                                <td class="tdfilter">
                                    <span style="padding-left:10px">{{ $key->atribut }}</span>
                                    @foreach($key->atributnilai as $atributnilai)
                                        @if(Session::has('notif'.$jenis.'_atributfilter'))
                                            {{ $checked = false }}
                                            @for($i=0;$i<count(Session::get('notif'.$jenis.'_atributfilter'));$i++)
                                                @if($atributnilai->id == Session::get('notif'.$jenis.'_atributfilter')[$i])
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
                            <button id="submitfilter" type='submit' class="ladda-button btn btn-primary slide-left"><span class="label2">{{ trans('all.lanjut') }}</span> <span class="spinner"></span></button>
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
        @if(Session::has('notif'.$jenis.'_keteranganatributfilter'))
            <div class="alert alert-info">
                <center>
                    {{ Session::get('notif'.$jenis.'_keteranganatributfilter') }}
                </center>
            </div>
        @endif
        @if(strpos(Session::get('hakakses_perusahaan')->$jenis, 'k') !== false || strpos(Session::get('hakakses_perusahaan')->$jenis, 'm') !== false)
            @if(count($data) > 0)
                <div style="margin-bottom: 20px;">
                    {{--<button id="terimasemua" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'terimasemua', 0)" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check'></i>&nbsp;&nbsp;{{ trans('all.terimasemua') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;--}}
                    {{--<button id="tolaksemua" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'tolaksemua', 0)" class="ladda-button btn btn-danger slide-left"><span class="label2"><i class='fa fa-times'></i>&nbsp;&nbsp;{{ trans('all.tolaksemua') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;--}}
                    <button id="terimaterpilih" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'terimaterpilih', 0)" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-times'></i>&nbsp;&nbsp;{{ trans('all.terimaterpilih') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button id="tolakterpilih" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'tolakterpilih', 0)" class="ladda-button btn btn-danger slide-left"><span class="label2"><i class='fa fa-times'></i>&nbsp;&nbsp;{{ trans('all.tolakterpilih') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button id="ceksemua" type="button" onclick="return ceksemua(true)" class="btn btn-primary"><i class='fa fa-check'></i></button>
                    <button id="hapusceksemua" style="display: none;" type="button" onclick="return ceksemua(false)" class="btn btn-danger"><i class='fa fa-times'></i></button>
                </div>
            @endif
        @endif

        <div class="row" style="margin-right:-30px;margin-left:-30px;">
            <div class="col-lg-12">
                <div class="ibox float-e-margins box">
                    <form id="form" method="post" action="">
                        {{csrf_field()}}
                        @if(count($data) > 0)
                            @foreach($data as $key)
                                <input type="hidden" value="{{$jenis}}" name="menu">
                                <input type="hidden" value="" name="status" id="status">
                                <div class="col-md-6" style="margin-bottom:20px;">
                                    <div class="ibox-content cardnotif" id="cardnotif_{{ $key->id }}">
                                        <table width="100%">
                                            <tr>
                                                @if($jenis == 'logabsen')
                                                    <td class="tdmodalDP" style="width:120px">
                                                        <center>
                                                            <a href="{{ url('fotologabsen/'.$key->id.'/normal') }}" title="{{ $key->nama }}" data-gallery="">
                                                                <img id="imgInp" width=120 style='border-radius: 50%;' height=120 src="{{ url('fotologabsen/'.$key->id.'/thumb') }}">
                                                            </a><p></p>
                                                            {{ trans('all.fotologabsen') }}
                                                        </center>
                                                    </td>
                                                @endif
                                                <td class="tdmodalDP" style="width:150px">
                                                    <center>
                                                        <img id="imgInp" width=120 style='border-radius: 50%;' height=120 src="{{ url('foto/pegawai/'.$key->idpegawai) }}"><br>
                                                        @if($jenis == 'logabsen') <p></p>{{ trans('all.fotoprofil') }} @endif
                                                    </center>
                                                </td>
                                                <td class="tdmodalDP">
                                                    <table>
                                                        @if($jenis != 'konfirmasi_flag')
                                                            <tr>
                                                                @if($jenis != 'logabsen')
                                                                    <td class="tdmodalDP">{{ trans('all.nama') }}</td>
                                                                    <td class="tdmodalDP">:</td>
                                                                    <td class="tdmodalDP"><b>{{ $key->nama }}</b></td>
                                                                @else
                                                                    <td class="tdmodalDP"><center><b>{{ $key->nama }}</b></center></td>
                                                                @endif
                                                            </tr>
                                                        @else
                                                            <tr>
                                                                <td><b>{{ $key->nama }}</b></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    {!! $key->masukkeluar !!}
                                                                    <span class="pull-right text-muted small">{{ \App\Utils::tanggalCantik($key->waktu) }}</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><span class="label label-warning">{{ trans('all.pengajuan').' : '.trans('all.'.str_replace('-','',$key->flag)) }}</span></td>
                                                            </tr>
                                                        @endif
                                                        @if($jenis == 'logabsen')
                                                            <tr>
                                                                <td class="tdmodalDP"><center>{{ $key->waktu }}</center></td>
                                                            </tr>
                                                            @if($key->konfirmasi != '')
                                                                <tr>
                                                                    <td class="tdmodalDP"><center>{{ $key->konfirmasi }}</center></td>
                                                                </tr>
                                                            @endif
                                                            <tr>
                                                                <td class="tdmodalDP"><center>{!! $key->masukkeluar !!}</center></td>
                                                            </tr>
                                                        @endif
                                                        @if($jenis == 'ijintidakmasuk')
                                                            <tr>
                                                                <td class="tdmodalDP">{{ trans('all.pin') }}</td>
                                                                <td class="tdmodalDP">:</td>
                                                                <td class="tdmodalDP">{{ $key->pin }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="tdmodalDP">{{ trans('all.nomorhp') }}</td>
                                                                <td class="tdmodalDP">:</td>
                                                                <td class="tdmodalDP">{{ $key->nomorhp }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="tdmodalDP">{{ trans('all.jamkerja') }}</td>
                                                                <td class="tdmodalDP">:</td>
                                                                <td class="tdmodalDP">{{ $key->jamkerja }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="tdmodalDP">{{ trans('all.waktu') }}</td>
                                                                <td class="tdmodalDP">:</td>
                                                                <td class="tdmodalDP">{{ \App\Utils::tanggalCantikDariSampai($key->tanggalawal, $key->tanggalakhir) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="tdmodalDP">{{ trans('all.keterangan') }}</td>
                                                                <td class="tdmodalDP">:</td>
                                                                <td class="tdmodalDP">{{ $key->keterangan }}</td>
                                                            </tr>
                                                        @endif
                                                        @if(strpos(Session::get('hakakses_perusahaan')->$jenis, 'k') !== false || strpos(Session::get('hakakses_perusahaan')->$jenis, 'm') !== false)
{{--                                                            @if($jenis == 'ijintidakmasuk' || $jenis == 'konfirmasi_flag')--}}
                                                                <tr>
                                                                    <td colspan="3" style="padding-top:10px">
                                                                        <textarea class="form-control" placeholder="{{trans('all.keterangan')}}" style="resize:none" id="keterangan_{{$key->id}}" name="keterangan_{{$key->id}}"></textarea>
                                                                    </td>
                                                                </tr>
{{--                                                            @endif--}}
                                                            <tr>
                                                                <td colspan="3" style="padding-top:10px">
                                                                    @if($jenis == 'logabsen')
                                                                        <a class="btn btn-info" href="#" onclick="return lokasiAbsen('{{ $key->lat }}','{{ $key->lon }}')"><i class="fa fa-map-marker"></i></a>&nbsp;&nbsp;
                                                                    @endif
                                                                    @if($jenis == 'ijintidakmasuk')
                                                                        @if($key->filename != '')
                                                                            <a class="lampiran btn btn-info" href="{{ url('fotonormal/ijintidakmasuk/'.$key->id) }}" title="{{ trans('all.lampiran') }}" data-gallery=""><i class="fa fa-file"></i>&nbsp;&nbsp;{{ trans('all.lampiran') }}</a>&nbsp;&nbsp;
                                                                        @endif
                                                                    @endif
                                                                    <button id="terima_{{ $key->id }}" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'terima', {{ $key->id }})" class="terimakonfirmasi ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check'></i>&nbsp;&nbsp;{{ trans('all.terima') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                                                    <button id="tolak_{{ $key->id }}" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'tolak', {{ $key->id }})" class="tolakkonfirmasi ladda-button btn btn-danger slide-left"><span class="label2"><i class='fa fa-times'></i>&nbsp;&nbsp;{{ trans('all.tolak') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </table>
                                                </td>
                                                @if(strpos(Session::get('hakakses_perusahaan')->$jenis, 'k') !== false || strpos(Session::get('hakakses_perusahaan')->$jenis, 'm') !== false)
                                                    <td valign="top">
                                                        <input class="notifterpilih" type="checkbox" style="cursor:pointer" value="{{ $key->id }}" id="notifterpilih_{{ $key->id }}" name="id[]" onclick="return setPilihan({{ $key->id }})">
                                                    </td>
                                                @endif
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="ibox-content row">
                                <center>{{ trans('all.nodata') }}</center>
                            </div>
                        @endif
                    </form>
                </div>
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

    <!-- Modal peta-->
    <a class="btn btn-info" style="display:none" href="#" id="buttonmodallogabsen" data-toggle="modal" data-target="#modallogabsen"></a>
    <div class="modal modallogabsen fade" id="modallogabsen" role="dialog" tabindex='-1'>
        <div class="modal-dialog modal-sm" style="width:640px">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('all.peta') }}</h4>
                </div>
                <div class="modal-body body-modal" id="modalbody-peta" style="overflow: auto;padding: 0">
                    <input id="pac-input_konfirmasiabsen" class="controls pac-input" type="text" placeholder="Search Box">
                    <div id="map_logabsen" style="height:480px"></div>
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
@stop

@push('scripts')
<script>
    var firstRun = true;
    var lat = '';
    var lon = '';
    var markers = null;
    var map;
    var lokasi;

    window.lokasiAbsen=(function(lat,lon){
        //menghapus keterangan yg lama dan mengganti keterangan yg baru keterangan peta riwayat presensi
        if(markers != null){
            //hilangkan marker yg lama
            markers.setMap(null);
            //set posisi peta ke default
            //map.setCenter({lat: -4.653079918274038, lng:117.7734375});
        }
        setTimeout(function(){
            //kasih marker di lokasi baru
            var myLatlng = new google.maps.LatLng(lat,lon);
            markers = new google.maps.Marker({
                position: myLatlng,
                map: map
            });

            var bounds = new google.maps.LatLngBounds();
            bounds.extend(myLatlng);
            map.fitBounds(bounds);

            //set posisi peta berdasarkan marker
            map.setCenter(markers.getPosition());
            //jika marker di klik
            markers.addListener('click', function(event) {
                map.setZoom(18);
                map.setCenter(markers.getPosition());
            });
        },500);
        //tampilkan modal marker
        $('#buttonmodallogabsen').trigger('click');
    })

    @if($lokasi != '')
        lokasi = [
            @foreach($lokasi as $key)
                [{{ $key->lat }}, {{ $key->lon }}, '{{ $key->nama }}'],
            @endforeach
        ];
    @endif
    function initMap() {
        map = new google.maps.Map(document.getElementById('map_logabsen'), {
            center: {lat: -4.653079918274038, lng:117.7734375},
            zoom: 4,
            mapTypeId: 'roadmap',
            gestureHandling: 'greedy',
            fullscreenControl: false,
            //styles: styleGoogleMaps
        });

        var mapMaxZoom = 18;
        var geoloccontrol = new klokantech.GeolocationControl(map, mapMaxZoom);

        // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input_konfirmasiabsen');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
            searchBox.setBounds(map.getBounds());
        });

        var marker_lokasi, i;

        var icon = {
            url: '{{ url('lib/drawable-xhdpi/perusahaan.png') }}', // url
            scaledSize: new google.maps.Size(30, 30), // scaled size
            origin: new google.maps.Point(0,0), // origin
            anchor: new google.maps.Point(10, 35) // anchor
        };

        for (i = 0; i < lokasi.length; i++) {
            marker_lokasi = new google.maps.Marker({
                position: new google.maps.LatLng(lokasi[i][0], lokasi[i][1]),
                map: map,
                icon: icon
            });

            google.maps.event.addListener(marker_lokasi, 'click', (function(marker_lokasi, i) {
                return function() {
                    console.log(lokasi[i][2])
                    alertInfo('{{ trans('all.lokasi') }} '+lokasi[i][2]);
                    infowindow.setContent(lokasi[i][0]);
                    infowindow.open(map, marker_lokasi);
                }
            })(marker_lokasi, i));
        };

        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
            var places = searchBox.getPlaces();

            if (places.length == 0) {
                return;
            }

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function(place) {
                if (!place.geometry) {
                    console.log("Returned place contains no geometry");
                    return;
                }

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
            });
            map.fitBounds(bounds);
        });
    }

    $('#modallogabsen').on('shown.bs.modal', function(){
         if (firstRun==true) {
             firstRun = false;
             initMap();
         }
    });

    function toogleButtonDisabled(status, id, toogle){
        if(toogle == true){
            if(status == 'terimaterpilih' || status == 'tolakterpilih') {
                $('#' + status).attr('data-loading', '');
            }else {
                $('#' + status + '_' + id).attr('data-loading', '');
            }
            $('.terimakonfirmasi').attr('disabled', 'disabled');
            $('.tolakkonfirmasi').attr('disabled', 'disabled');
//            $('#terimasemua').attr('disabled', 'disabled');
//            $('#tolaksemua').attr('disabled', 'disabled');
            $('#terimaterpilih').attr('disabled', 'disabled');
            $('#tolakterpilih').attr('disabled', 'disabled');
            $('#ceksemua').css('disabled','disabled');
            $('#hapusceksemua').css('disabled','disabled');
            $('.lampiran').css('disabled','disabled');
        }else{
            if(status == 'terimaterpilih' || status == 'tolakterpilih') {
                $('#' + status).removeAttr('data-loading');
            }else {
                $('#' + status + '_' + id).removeAttr('data-loading');
            }
            $('#'+status+'_'+id).removeAttr('data-loading');
            $('.terimakonfirmasi').removeAttr('disabled');
            $('.tolakkonfirmasi').removeAttr('disabled');
//            $('#terimasemua').removeAttr('disabled');
//            $('#tolaksemua').removeAttr('disabled');
            $('#terimaterpilih').removeAttr('disabled');
            $('#tolakterpilih').removeAttr('disabled');
            $('#ceksemua').removeAttr('disabled');
            $('#hapusceksemua').removeAttr('disabled');
            $('.lampiran').removeAttr('disabled');
        }
    }

    function submitKonfirmasi(menu,status,id){
        $('#status').val(status);
        toogleButtonDisabled(status, id, true);
        if(status == 'terimaterpilih' || status == 'tolakterpilih'){
            var idkonfirmasiabsen = '';
            $('.notifterpilih').each(function(){
                if($(this).prop('checked') == true){
                    idkonfirmasiabsen += '|'+$(this).val();
                }
            });
            if(idkonfirmasiabsen == ''){
                alertWarning('{{ trans('all.andabelummemilih') }}',
                    function(){
                        toogleButtonDisabled(status, id, false);
                    }
                );
                return false;
            }
            idkonfirmasiabsen = idkonfirmasiabsen.substring(1);
        }else{
            // satuan
            var idsatuan = $("<input>")
                .attr("type", "hidden")
                .attr("name", "idsatuan").val(id);
            $('#form').append(idsatuan);
        }

        // console.log($('#form').serialize());
        // console.log(new FormData($('#form')[0]));
        // return false;

        alertConfirm('{{ trans('all.apakahandayakin') }}',
            function(){
                var dataString = new FormData($('#form')[0]);
                // var dataString = 'menu=' + menu + '&status=' + status + '&idkonfirmasiabsen=' + idkonfirmasiabsen + '&_token=' + token + '&keterangan=' + keterangan;
                $.ajax({
                    type: "POST",
                    url: '{{ url('submitkonfirmasiabsenterpilih') }}/'+status,
                    data: dataString,
                    async: true,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        console.log(data);
                        if(data == 'ok') {
                            window.location.href = '{{ url('notifdetail') }}/' + menu;
                        }else{
                            alertError('{{ trans('all.terjadigangguan') }}',function(){
                                toogleButtonDisabled(status, id, false);
                            });
                        }
                    }
                });
            },
            function(){
                toogleButtonDisabled(status, id, false);
            },
            "{{ trans('all.ya') }}","{{ trans('all.tidak') }}"
        );
    }

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

    @if($jenis == 'konfirmasi_flag')
        $('.datatable').DataTable({
            bStateSave: true,
            scrollX: true,
            columnDefs: [{
                orderable: false,
                targets: 0
            }],
            order: [[ 1, 'desc']]
        });
    @endif
</script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script>
@endpush