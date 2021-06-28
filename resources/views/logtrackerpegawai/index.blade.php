@extends('layouts.master')
@section('title', trans('all.pegawai'))
@section('content')

    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('/lib/css/plugins/nouslider/jquery.nouislider.css') }}">
    <script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>
    <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
            async defer></script>
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

        function resetkatasandi(id){
            alertConfirm("{{ trans('all.resetkatasandipegawaiini') }} ?",
                function(){
                    //document.getElementById(id).click();
                    window.location.href="resetkatasandi/"+id;
                }
            );
        }

        function limitpegawai(){
            alertWarning("{{ trans('all.jumlahpegawaimencapaibatasygdiijinkan') }}",
                function() {
                    aktifkanTombol();
                });
            return false;
        }

        function validasi(){
            $('#submit').attr( 'data-loading', '' );
            $('#submit').attr('disabled', 'disabled');
            $('#setulang').attr('disabled', 'disabled');

            var pegawai = $('#pegawai').val();
            var tanggalawal = $('#tanggalawal').val();
            var tanggalakhir = $('#tanggalakhir').val();
            var jamawal = $('#jamawal').val();
            var jamakhir = $('#jamakhir').val();

            if(pegawai == ''){
                alertWarning("{{ trans('all.pegawai').' '.trans('all.sa_kosong') }}",
                    function() {
                        aktifkanTombol();
                        $('#setulang').removeAttr('disabled');
                        $('#token-input-pegawai').focus();
                    });
                return false;
            }

            if(tanggalawal == ''){
                alertWarning("{{ trans('all.tanggalawal').' '.trans('all.sa_kosong') }}",
                    function() {
                        aktifkanTombol();
                        $('#setulang').removeAttr('disabled');
                        $('#tanggalawal').focus();
                    });
                return false;
            }

            if(tanggalakhir == ''){
                alertWarning("{{ trans('all.tanggalakhir').' '.trans('all.sa_kosong') }}",
                    function() {
                        aktifkanTombol();
                        $('#setulang').removeAttr('disabled');
                        $('#tanggalakhir').focus();
                    });
                return false;
            }

            if(tanggalakhir.split("/").reverse().join("-") < tanggalawal.split("/").reverse().join("-")){
                alertWarning("{{ trans('all.tanggalakhirlebihkecildaritanggalawal') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tanggalakhir'));
                    });
                return false;
            }

            if(cekSelisihTanggal(tanggalawal,tanggalakhir) == true){
                alertWarning("{{ trans('all.selisihharimaksimal31') }}",
                    function() {
                        aktifkanTombol();
                        setFocus($('#tanggalakhir'));
                    });
                return false;
            }

            if(jamawal == ''){
                alertWarning("{{ trans('all.jamawal').' '.trans('all.sa_kosong') }}",
                    function() {
                        aktifkanTombol();
                        $('#setulang').removeAttr('disabled');
                        $('#jamawal').focus();
                    });
                return false;
            }

            if(jamakhir == ''){
                alertWarning("{{ trans('all.jamakhir').' '.trans('all.sa_kosong') }}",
                    function() {
                        aktifkanTombol();
                        $('#setulang').removeAttr('disabled');
                        $('#jamakhir').focus();
                    });
                return false;
            }
        }
    </script>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ trans('all.pegawai') }}</h2>
            <ol class="breadcrumb">
                <li>{{ trans('all.laporan') }}</li>
                <li class="active"><strong>{{ trans('all.logtrackerpegawai') }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">

        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
                <ul class="nav nav-tabs">
                    <li><a href="{{ url('logtrackerpegawairealtime') }}">{{ trans('all.logtrackerpegawairealtime') }}</a></li>
                    <li class="active"><a href="{{ url('logtrackerpegawai') }}">{{ trans('all.logtrackerpegawai') }}</a></li>
                </ul>
                <br>
                <form method="post" action="" onsubmit="return validasi()">
                    {{ csrf_field() }}
                    <table width="100%">
                        <tr>
                            <td style="float:left;margin-top:8px">{{ trans('all.pegawai') }}</td>
                            <td style="float:left;min-width:200px;margin-left:10px">
                                <input type="text" class="form-control" autofocus autocomplete="off" name="pegawai" id="pegawai" maxlength="100">
                                <script type="text/javascript">
                                    $(document).ready(function(){
                                        $("#pegawai").tokenInput("{{ url('tokenpegawai') }}", {
                                            theme: "facebook",
                                            tokenLimit: 1,
                                            prePopulate: [
                                                    @if($pegawai != '')
                                                    @foreach($pegawai as $key)
                                                {id: {{ $key->id }}, nama: '{{ $key->nama }}'},
                                                @endforeach
                                                @endif
                                            ]
                                        });
                                    });
                                </script>
                            </td>
                            <td style="float:left;margin-top:8px;margin-left:10px">{{ trans('all.tanggal') }}</td>
                            <td style="float:left;margin-left:10px">
                                <table>
                                    <tr>
                                        <td style="padding:0">
                                            <input type="text" class="form-control date" size="11" value="{{ $tanggalawal }}" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalawal" id="tanggalawal" maxlength="10">
                                        </td>
                                        <td style="padding:0;padding-left:10px">
                                            <input type="text" class="form-control jam" size="8" value="{{ $jamawal }}" autocomplete="off" placeholder="hh:mm:ss" name="jamawal" id="jamawal">
                                        </td>
                                        <td style="padding:10px">-</td>
                                        <td style="padding:0">
                                            <input type="text" class="form-control date" size="11" value="{{ $tanggalakhir }}" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalakhir" id="tanggalakhir" maxlength="10">
                                        </td>
                                        <td style="padding:0;padding-left:10px">
                                            <input type="text" class="form-control jam" size="8" value="{{ $jamakhir }}" autocomplete="off" placeholder="hh:mm:ss" name="jamakhir" id="jamakhir">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="float:left;margin-left:10px">
                                <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                <button type="button" id="setulang" onclick="return ke('{{ url('logtrackerpegawai/reset') }}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                            </td>
                            <td style="display: none;" id="buttonekspor" idpegawai="">
                                <button type="button" class="btn btn-primary pull-right" onclick="return ekspor()"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
                            </td>
                        </tr>
                    </table>
                </form>
                <p></p>
                <div class="ibox float-e-margins" @if(!Session::has('logtrackerpegawai_idpegawai')) style="display: none" @endif>
                    <div class="row" style="margin-bottom: 10px;margin-top: 20px">
                        <div class="col-lg-12">
                            <p class="font-bold">Rentang Waktu</p>
                            <div id="basic_slider"></div>
                        </div>
                    </div>
                    <input id="pac-input" class="controls pac-input" type="text" placeholder="Search Box">
                    <div id="map" style="width:100%; height:70vh"></div>
                    <button id="tombolpeta" style="display:none"></button>
                    {{--<div id="map2" style="height: 100vh"></div>--}}
                    {{--<div id="map2" style="width: 100%; height: 100%"></div>--}}
                </div>
            </div>
        </div>
    </div>


@stop

@push('scripts')
<script src="{{ asset('lib/js/plugins/nouslider/jquery.nouislider.min.js') }}"></script>
<script>
    //init peta
    var locations = [];
    var map = '';
    var markerCluster = null;
    var markers = [];
    var lokasi;
    var logtracker = '';
    var marker_log = null;
    var marker_c = null;
    @if($lokasi != '')
        lokasi = [
            @foreach($lokasi as $key)
                [{{ $key->lat }}, {{ $key->lon }}, '{{ $key->nama }}'],
            @endforeach
        ];
    @endif

    var marker_line_dynamic = null;
    var newcoordinates = [];

    var latlng = {lat: -4.653079918274038, lng: 117.7734375};
    var zooms = 5;
    @if($data != '')
        logtracker = [
            @foreach($data as $key)
        [{{ $key->lat }}, {{ $key->lon }}, new Date('{{ $key->waktunormal }}'), '{{ $key->jenis }}', '{{ $key->idlogabsen }}', '{{ $key->waktu }}'],
        @endforeach
    ];

    latlng = {lat: {{ $key->lat }}, lng: {{ $key->lon }} };
    zooms = 20;
    @endif

    $(function() {
        $('.jam').inputmask( 'h:s:s' );

        $('.date').mask("00/00/0000", {clearIfNotMatch: true});

        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });

        setTimeout(function(){ $('#tombolpeta').trigger('click'); },100);
        $('#tombolpeta').click(function(){
            initMap();
        });

        function getWaktuInterpolasiSlider(waktuawal,waktuakhir,slider){
            var jarakWaktu = (waktuakhir-waktuawal)/1000;
            var rasioSlider = slider/1000;
            var detikInterpolasi = Math.round(rasioSlider*jarakWaktu);
            waktuawal.setSeconds(waktuawal.getSeconds() + detikInterpolasi);
            return waktuawal;
        }



//        function pretty2date(pretty) {
//            console.log(changeDateFormat(pretty));
////            return new Date(changeDateFormat(pretty));
//        }

        var slider = document.getElementById('basic_slider');
        noUiSlider.create(slider, {
            start: 1000,
            step: 1,
            tooltips: true,
            connect: 'lower',
            range: {
                'min':  0,
                'max':  1000
            },
            format: {
                to: function ( value ) {
                    var inputwaktuawal = new Date(changeDateFormat('{{ Session::get('logtrackerpegawai_tanggalawal') }}')+' {{ Session::get('logtrackerpegawai_jamawal') }}'); //tanggal dari inputan
                    var inputwaktuakhir = new Date(changeDateFormat('{{ Session::get('logtrackerpegawai_tanggalakhir') }}')+' {{ Session::get('logtrackerpegawai_jamakhir') }}'); //tanggal dari inputan
                    var c_waktu = getWaktuInterpolasiSlider(inputwaktuawal, inputwaktuakhir, value);
                    return date2pretty(c_waktu);
                },
                from: function ( value ) {
                    return value;
                }
            }
        });

        slider.noUiSlider.on('update', function( values, handle ) {
//            console.log(values[handle]);
//            return;
            /*
             var inputwaktuawal = new Date(changeDateFormat('{{ Session::get('logtrackerpegawai_tanggalawal') }}')+' {{ Session::get('logtrackerpegawai_jamawal') }}'); //tanggal dari inputan
             var inputwaktuakhir = new Date(changeDateFormat('{{ Session::get('logtrackerpegawai_tanggalakhir') }}')+' {{ Session::get('logtrackerpegawai_jamakhir') }}'); //tanggal dari inputan
             var interpolasi = values[handle].replace('.00','');
             var c_waktu = getWaktuInterpolasiSlider(inputwaktuawal, inputwaktuakhir, interpolasi);
             */
//            console.log(changeDateTimeFormat(values[handle]));
//            console.log(new Date(changeDateTimeFormat(values[handle])));
//            return;
            var c_waktu  = new Date(changeDateTimeFormat(values[handle]));
            var idx_a = -1;
            var idx_b = -1;
            var c_lat = 0;
            var c_lon = 0;

            for(var i=1;i<logtracker.length;i++){
                if(c_waktu >= logtracker[i-1][2] && c_waktu <= logtracker[i][2]){
                    idx_a = i-1;
                    idx_b = i;
                    break;
                }
            }

            if (idx_a != -1 && idx_b != -1) {
                var rasio_ab = (c_waktu - logtracker[idx_a][2]) / (logtracker[idx_b][2] - logtracker[idx_a][2]);
                c_lat = logtracker[idx_a][0] + (rasio_ab * (logtracker[idx_b][0]-logtracker[idx_a][0]));
                c_lon = logtracker[idx_a][1] + (rasio_ab * (logtracker[idx_b][1]-logtracker[idx_a][1]));

                marker_c.setPosition(new google.maps.LatLng(c_lat, c_lon));
//                console.log(c_lat, logtracker[idx_a][0], c_waktu);
//                console.log(c_lon, logtracker[idx_a][1], c_waktu);

                //buat garis/line
//                newcoordinates = [
//                    new google.maps.LatLng(-7.335064, 112.79566),
//                ];
//
//                var path = marker_line_dynamic.getPath();
//                path.push(new google.maps.LatLng(c_lat, c_lon));

                if(c_lat == logtracker[idx_a][0] && c_lon == logtracker[idx_a][1]) {
                    marker_log = new google.maps.Marker({
                        position: new google.maps.LatLng(logtracker[idx_a][0], logtracker[idx_a][1]),
                        map: map,
                        title: '',
                        {{--icon: '{{ url('mapmarker/absenakhir') }}',--}}
                        zIndex: 99999999
                    });
                }

            }else{
                marker_c.setPosition(new google.maps.LatLng(0, 0));
                marker_log = new google.maps.Marker({
                    position: new google.maps.LatLng(0, 0),
                    map: map,
                    title: '',
                    {{--icon: '{{ url('mapmarker/absenakhir') }}',--}}
                    zIndex: 99999999
                });
            }
        });
    });

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: latlng,
            zoom: zooms,
            mapTypeId: 'roadmap',
            gestureHandling: 'greedy',
            fullscreenControl: false
        });

        marker_c = new google.maps.Marker({
            position: new google.maps.LatLng(0, 0),
            map: map,
            title: '',
            icon: '{{ url('mapmarker/absenakhir') }}',
            zIndex: 999999999
        });

        marker_line_dynamic = new google.maps.Polyline({
            path: newcoordinates,
            geodesic: true,
            strokeColor: '#FF0000',
            strokeOpacity: 1.0,
            strokeWeight: 2,
            icons: [{
                icon: {path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW},
                offset: '100%'
            }]
        });

        marker_line_dynamic.setMap(map);

        var mapMaxZoom = 13;
        var geoloccontrol = new klokantech.GeolocationControl(map, mapMaxZoom);

        // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function () {
            searchBox.setBounds(map.getBounds());
        });

        var marker_lokasi, i;

        var icon_perusahaan = {
            url: '{{ url('lib/drawable-xhdpi/perusahaan.png') }}', // url
            scaledSize: new google.maps.Size(24, 24), // scaled size
//            origin: new google.maps.Point(0, 0), // origin
//            anchor: new google.maps.Point(10, 35) // anchor
        };

        for (i = 0; i < lokasi.length; i++) {
            marker_lokasi = new google.maps.Marker({
                position: new google.maps.LatLng(lokasi[i][0], lokasi[i][1]),
                map: map,
                icon: icon_perusahaan,
                title: lokasi[i][2]
            });

            google.maps.event.addListener(marker_lokasi, 'click', (function (marker_lokasi, i) {
                return function () {
                    //console.log(lokasi[i][2])
                    alertInfo('{{ trans('all.lokasi') }} ' + lokasi[i][2]);
                    /*infowindow.setContent(lokasi[i][0]);
                     infowindow.open(map, marker_lokasi);*/
                }
            })(marker_lokasi, i));
        }

        var marker_logtracker, l;
        for (l = 0; l < logtracker.length; l++) {
            if (logtracker[l][3] == 't') {
                if (l == logtracker.length - 1) {
                    marker_logtracker = new google.maps.Marker({
                        position: new google.maps.LatLng(logtracker[l][0], logtracker[l][1]),
                        map: map,
                        title: '{{ trans('all.waktu') }} ' + logtracker[l][5],
                        icon: '{{ url('mapmarker/absenakhir') }}',
                        zIndex: 999999
                    });
                }
                else if (l == 0) {
                    marker_logtracker = new google.maps.Marker({
                        position: new google.maps.LatLng(logtracker[l][0], logtracker[l][1]),
                        map: map,
                        title: '{{ trans('all.waktu') }} ' + logtracker[l][5],
                        icon: '{{ url('mapmarker/absenawal') }}',
                        zIndex: 999999
                    });
                }
                else {
                    marker_logtracker = new google.maps.Marker({
                        position: new google.maps.LatLng(logtracker[l][0], logtracker[l][1]),
                        map: map,
                        title: '{{ trans('all.waktu') }} ' + logtracker[l][5],
                        icon: '{{ url('mapmarker/tracker') }}',
                    });
                }
            }
            else {
                var iconawal = {
                    url: '{{ url('fotologabsen') }}/'+logtracker[l][4]+'/thumb',
                    scaledSize: new google.maps.Size(40, 40), // scaled size
//                    origin: new google.maps.Point(0, 0), // origin
                    anchor: new google.maps.Point(20, 48) // anchor
                };
                marker_logtracker = new google.maps.Marker({
                    position: new google.maps.LatLng(logtracker[l][0], logtracker[l][1]),
                    map: map,
                    title: '{{ trans('all.waktu') }} ' + logtracker[l][5],
                    icon: iconawal,
                    zIndex: 99999999
                });
                var icon_bingkai = {
                    url: 'http://i.stack.imgur.com/KOh5X.png',
                    scaledSize: new google.maps.Size(50, 65), // scaled size
//                    origin: new google.maps.Point(0, 0), // origin
                    anchor: new google.maps.Point(25, 53) // anchor
                };
                marker_logtracker = new google.maps.Marker({
                    position: new google.maps.LatLng(logtracker[l][0], logtracker[l][1]),
                    map: map,
                    title: '{{ trans('all.waktu') }} ' + logtracker[l][5],
                    icon: icon_bingkai,
                    zIndex: 99999999
                });
            }

            if (logtracker[l][3] == 't') {
                google.maps.event.addListener(marker_logtracker, 'click', (function (marker_logtracker, l) {
                    return function () {
                        //console.log(logtracker[l][2])
                        alertInfo('{{ trans('all.waktu') }} ' + date2pretty(logtracker[l][2]));
                    }
                })(marker_logtracker, l));
            }else{
                var keterangantambahan = '';
                if (logtracker[l][3] == 'm') {
                    keterangantambahan = ' ({{ trans('all.masuk') }})';
                }else{
                    keterangantambahan = ' ({{ trans('all.keluar') }})';
                }
                google.maps.event.addListener(marker_logtracker, 'click', (function (marker_logtracker, l) {
                    return function () {
                        //console.log(logtracker[l][2])
                        callModalGeneral('{{ trans('all.logtrackerpegawai') }}','{{ url('detaillogabsen') }}/'+logtracker[l][4]);
                        {{--alertInfo('{{ trans('all.waktu') }} ' + logtracker[l][2] + keterangantambahan);--}}
                    }
                })(marker_logtracker, l));
            }
        }

        var marker_line;
        var linecoordinates = [];
        @if($data != '')
            linecoordinates = [
                @foreach($data as $key)
            { lat: {{ $key->lat }}, lng: {{ $key->lon }} },
            {{--new google.maps.LatLng({{ $key->lat }}, {{ $key->lon }}),--}}
            @endforeach
        ];
        @endif

        //        var lineSymbol = {
        //            path: 'M 0,-1 0,1',
        //            strokeOpacity: 1,
        //            scale: 4
        //        };

        if(linecoordinates.length > 0) {
            for (l = 0; l < linecoordinates.length; l++) {

                var coordinates = new Array();
                for (var j = l; j < l + 2 && j < linecoordinates.length; j++) {
                    coordinates[j - l] = linecoordinates[j];
                }

                if (coordinates.length == 2) {
                    marker_line = new google.maps.Polyline({
                        path: coordinates,
                        geodesic: true,
                        strokeColor: '#FF0000',
//                        strokeOpacity: 0,
                        strokeOpacity: 1.0,
                        strokeWeight: 2,
                        icons: [{
                            icon: {path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW},
                            offset: '100%'
//                            icon: lineSymbol,
//                            offset: '0',
//                            repeat: '20px'
                        }]
                    });

                    marker_line.setMap(map);
                }
            }
        }

        searchBox.addListener('places_changed', function () {
            var places = searchBox.getPlaces();

            if (places.length == 0) {
                return;
            }

            // Clear out the old markers.
            if (markers != '') {
                //markers.setMap(null);
            }

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function (place) {
                if (!place.geometry) {
                    console.log("Returned place contains no geometry");
                    return;
                }

                // Create a marker for each place.
                markers = new google.maps.Marker({
                    //position: place.geometry.location, //untuk kasih marker
                    map: map
                });

                markers.addListener('click', function (event) {
                    //getlatlon(event);
                });

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
</script>
@endpush