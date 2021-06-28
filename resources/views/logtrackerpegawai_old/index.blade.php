@extends('layouts.master')
@section('title', trans('all.pegawai'))
@section('content')
  
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
              <li><a href="{{ url('logtrackerpegawaislider') }}">{{ trans('all.logtrackerpegawaislider') }}</a></li>
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
            <input id="pac-input" class="controls pac-input" type="text" placeholder="Search Box">
            <div id="map" style="height:500px"></div>
            {{--<div id="map2" style="height: 100vh"></div>--}}
            {{--<div id="map2" style="width: 100%; height: 100%"></div>--}}
        </div>
      </div>
    </div>
  </div>


@stop

@push('scripts')
<script>

    $(function() {
        $('.jam').inputmask( 'h:s:s' );

        $('.date').mask("00/00/0000", {clearIfNotMatch: true});

        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });
        initMap();
    });

    //init peta
    var locations = [];
    var map = '';
    var markerCluster = null;
    var markers = [];
    var lokasi;
    var logtracker = '';
    @if($lokasi != '')
        lokasi = [
        @foreach($lokasi as $key)
            [{{ $key->lat }}, {{ $key->lon }}, '{{ $key->nama }}'],
        @endforeach
    ];
    @endif

    var latlng = {lat: -4.653079918274038, lng: 117.7734375};
    var zooms = 5;
    @if($data != '')
        logtracker = [
            @foreach($data as $key)
                [{{ $key->lat }}, {{ $key->lon }}, '{{ $key->waktu }}', '{{ $key->jenis }}', '{{ $key->idlogabsen }}'],
            @endforeach
        ];

        latlng = {lat: {{ $key->lat }}, lng: {{ $key->lon }} };
        zooms = 20;
    @endif

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: latlng,
            zoom: zooms,
            mapTypeId: 'roadmap',
            gestureHandling: 'greedy',
            fullscreenControl: false,
            styles: [
                {
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#f5f5f5"
                        }
                    ]
                },
                {
                    "elementType": "labels.icon",
                    "stylers": [
                        {
                            "visibility": "on"
                        }
                    ]
                },
                {
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#616161"
                        }
                    ]
                },
                {
                    "elementType": "labels.text.stroke",
                    "stylers": [
                        {
                            "color": "#f5f5f5"
                        }
                    ]
                },
                {
                    "featureType": "administrative.land_parcel",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#bdbdbd"
                        }
                    ]
                },
                {
                    "featureType": "poi",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#eeeeee"
                        }
                    ]
                },
                {
                    "featureType": "poi",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#757575"
                        }
                    ]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#e5e5e5"
                        }
                    ]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#9e9e9e"
                        }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#ffffff"
                        }
                    ]
                },
                {
                    "featureType": "road.arterial",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#757575"
                        }
                    ]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#dadada"
                        }
                    ]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#616161"
                        }
                    ]
                },
                {
                    "featureType": "road.local",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#9e9e9e"
                        }
                    ]
                },
                {
                    "featureType": "transit.line",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#e5e5e5"
                        }
                    ]
                },
                {
                    "featureType": "transit.station",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#eeeeee"
                        }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#c9c9c9"
                        }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#9e9e9e"
                        }
                    ]
                }
            ]
        });

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

        var icon = {
            url: '{{ url('lib/drawable-xhdpi/perusahaan.png') }}', // url
            scaledSize: new google.maps.Size(24, 24), // scaled size
//            origin: new google.maps.Point(0, 0), // origin
//            anchor: new google.maps.Point(10, 35) // anchor
        };

        for (i = 0; i < lokasi.length; i++) {
            marker_lokasi = new google.maps.Marker({
                position: new google.maps.LatLng(lokasi[i][0], lokasi[i][1]),
                map: map,
                icon: icon,
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
                        title: '{{ trans('all.waktu') }} ' + logtracker[l][2],
                        icon: '{{ url('mapmarker/absenakhir') }}',
                        zIndex: 999999
                    });
                }
                else if (l == 0) {
                    marker_logtracker = new google.maps.Marker({
                        position: new google.maps.LatLng(logtracker[l][0], logtracker[l][1]),
                        map: map,
                        title: '{{ trans('all.waktu') }} ' + logtracker[l][2],
                        icon: '{{ url('mapmarker/absenawal') }}',
                        zIndex: 999999
                    });
                }
                else {
                    marker_logtracker = new google.maps.Marker({
                        position: new google.maps.LatLng(logtracker[l][0], logtracker[l][1]),
                        map: map,
                        title: '{{ trans('all.waktu') }} ' + logtracker[l][2],
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
                    title: '{{ trans('all.waktu') }} ' + logtracker[l][2],
                    icon: iconawal,
                    zIndex: 99999999
                });
                var iconawal2 = {
                    url: 'http://i.stack.imgur.com/KOh5X.png',
                    scaledSize: new google.maps.Size(50, 65), // scaled size
//                    origin: new google.maps.Point(0, 0), // origin
                    anchor: new google.maps.Point(25, 53) // anchor
                };
                marker_logtracker = new google.maps.Marker({
                    position: new google.maps.LatLng(logtracker[l][0], logtracker[l][1]),
                    map: map,
                    title: '{{ trans('all.waktu') }} ' + logtracker[l][2],
                    icon: iconawal2,
                    zIndex: 99999999
                });
            }

            if (logtracker[l][3] == 't') {
                google.maps.event.addListener(marker_logtracker, 'click', (function (marker_logtracker, l) {
                    return function () {
                        //console.log(logtracker[l][2])
                        alertInfo('{{ trans('all.waktu') }} ' + logtracker[l][2]);
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

        var marker_line, linecoordinates = '';
        @if($data != '')
            linecoordinates = [
                @foreach($data as $key)
                    { lat: {{ $key->lat }}, lng: {{ $key->lon }} },
                    {{--new google.maps.LatLng({{ $key->lat }}, {{ $key->lon }}),--}}
                @endforeach
            ];
        @endif
        if(linecoordinates != '') {
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
                        strokeOpacity: 1.0,
                        strokeWeight: 2,
                        icons: [{
                            icon: {path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW},
                            offset: '100%'
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