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
  </script>
  <script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>
  <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script>
  <style>
      td{
          padding:5px;
      }
  </style>
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

  {{-- filter --}}
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
                                      @if(Session::has('logtrackerpegawairealtime_jamkerja'))
                                          <input type="radio" @if(Session::get('logtrackerpegawairealtime_jamkerja') == 'full') checked @endif id="jamkerjafull" name="jamkerja" value="full">
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
                                      @if(Session::has('logtrackerpegawairealtime_jamkerja'))
                                          <input type="radio" @if(Session::get('logtrackerpegawairealtime_jamkerja') == 'shift') checked @endif id="jamkerjashift" name="jamkerja" value="shift">
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
              {{--<tr>--}}
                  {{--<td class="tdfilter">--}}
                      {{--<span style="padding-left:10px">{{ trans('all.kategorijamkerja') }}</span>--}}
                      {{--<div style="padding-left:15px">--}}
                          {{--@foreach($jamkerjakategori as $key)--}}
                              {{--{{ $checked = false }}--}}
                              {{--@if(Session::has('logtrackerpegawairealtime_kategorijamkerja'))--}}
                                  {{--@for($i=0;$i<count(Session::get('logtrackerpegawairealtime_kategorijamkerja'));$i++)--}}
                                      {{--@if($key->id == Session::get('logtrackerpegawairealtime_kategorijamkerja')[$i])--}}
                                          {{--<span style="display:none">{{ $checked = true }}</span>--}}
                                      {{--@endif--}}
                                  {{--@endfor--}}
                              {{--@endif--}}
                              {{--<table width="100%">--}}
                                  {{--<tr>--}}
                                      {{--<td valign="top" style="width:10px;">--}}
                                          {{--<input type="checkbox" id="kategorijamkerja_{{ $key->id }}" @if($checked == true) checked @endif name="kategorijamkerja[]" value="{{ $key->id }}">--}}
                                      {{--</td>--}}
                                      {{--<td valign="top">--}}
                                          {{--<span onclick="spanClick('kategorijamkerja_{{ $key->id }}')">{{ $key->nama }}</span>--}}
                                      {{--</td>--}}
                                  {{--</tr>--}}
                              {{--</table>--}}
                          {{--@endforeach--}}
                      {{--</div>--}}
                  {{--</td>--}}
              {{--</tr>--}}
              @if(isset($atribut))
                  @foreach($atribut as $key)
                      @if(count($key->atributnilai) > 0)
                          <tr>
                              <td class="tdfilter">
                                  <span style="padding-left:10px">{{ $key->atribut }}</span>
                                  @foreach($key->atributnilai as $atributnilai)
                                      @if(Session::has('logtrackerpegawairealtime_atributfilter'))
                                          {{ $checked = false }}
                                          @for($i=0;$i<count(Session::get('logtrackerpegawairealtime_atributfilter'));$i++)
                                              @if($atributnilai->id == Session::get('logtrackerpegawairealtime_atributfilter')[$i])
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
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
            <li class="active"><a href="{{ url('logtrackerpegawairealtime') }}">{{ trans('all.logtrackerpegawairealtime') }}</a></li>
            <li><a href="{{ url('logtrackerpegawai') }}">{{ trans('all.logtrackerpegawai') }}</a></li>
        </ul>
        <br>
        <div class="ibox float-e-margins">
            <input id="pac-input" class="controls pac-input" type="text" placeholder="Search Box">
            <div id="map" style="width:100%;height:80vh"></div>
            <button id="tombolpeta" style="display:none"></button>
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

@stop

@push('scripts')
<script>
    var map = '';
    var markers_pencarian = [];
    var lokasi;
    var logtracker = '';
    var logtrackers = '';
    var numDeltas = 100;
    var delay = 10; //milliseconds
    var markers = [];
    var markers_bingkai = [];
    var old_data = [];

    @if($lokasi != '')
        lokasi = [
            @foreach($lokasi as $key)
        [{{ $key->lat }}, {{ $key->lon }}, '{{ $key->nama }}'],
        @endforeach
    ];
    @endif

    @if($data != '')
        logtracker = [
            @foreach($data as $key)
                [{{ $key->lat }}, {{ $key->lon }}, '{{ $key->waktu }}', {{ $key->idpegawai }}, '{{ $key->pegawai }}'],
            @endforeach
        ];
        logtrackers = [
            @foreach($data as $key)
                { 'id': {{ $key->id }}, 'idlogabsen': @if($key->idlogabsen == null) null @else {{ $key->idlogabsen }} @endif, 'idpegawai': {{ $key->idpegawai }}, 'inserted': '{{ $key->inserted }}', 'lat': {{ $key->lat }}, 'lon': {{ $key->lon }}, 'pegawai': '{{ $key->pegawai }}', 'waktu': '{{ $key->waktu }}' },
            @endforeach
        ];
    @endif

    function transitions(marker_el,bingkai_el,latlng,old_latlng){
        var deltaLat = (latlng[0] - old_latlng[0])/numDeltas;
        var deltaLng = (latlng[1] - old_latlng[1])/numDeltas;
        moveMarkers(marker_el,bingkai_el,deltaLat,deltaLng,old_latlng, 0);
    }

    function moveMarkers(marker_el,bingkai_el,deltaLat,deltaLng,old_latlng, i){
        old_latlng[0] += deltaLat;
        old_latlng[1] += deltaLng;
        var latlng = new google.maps.LatLng(old_latlng[0], old_latlng[1]);
        marker_el.setPosition(latlng);
        bingkai_el.setPosition(latlng);
        if(i<numDeltas){
            setTimeout(function(){moveMarkers(marker_el,bingkai_el,deltaLat,deltaLng,old_latlng, i+1)}, delay);
        }
    }

    function createMarker(obj){
        var marker_refresh, marker_refresh_bingkai;
        var iconawal = {
            url: '{{ url('foto') }}/pegawai/'+obj['idpegawai'],
            scaledSize: new google.maps.Size(40, 40), // scaled size
//            origin: new google.maps.Point(0, 0), // origin
            anchor: new google.maps.Point(20, 48) // anchor
        };
        var icon_bingkai = {
            url: 'http://i.stack.imgur.com/KOh5X.png',
            scaledSize: new google.maps.Size(50, 65), // scaled size
//                    origin: new google.maps.Point(0, 0), // origin
            anchor: new google.maps.Point(25, 53) // anchor
        };
        marker_refresh = new google.maps.Marker({
            position: new google.maps.LatLng(obj['lat'], obj['lon']),
            map: map,
            title: obj['pegawai'],
            icon: iconawal,
            zIndex: 100000+parseInt(obj['idpegawai'])
        });
        marker_refresh_bingkai = new google.maps.Marker({
            position: new google.maps.LatLng(obj['lat'], obj['lon']),
            map: map,
            title: obj['pegawai'],
            icon: icon_bingkai,
            zIndex: 100000+parseInt(obj['idpegawai'])
        });

        google.maps.event.addListener(marker_refresh_bingkai, 'click', (function (marker_refresh_bingkai) {
            return function () {
                detailpegawai(obj['idpegawai'], obj['waktu']);
            }
        })(marker_refresh_bingkai));
        addMarker(markers,obj['idpegawai'],marker_refresh);
        addMarker(markers_bingkai,obj['idpegawai'],marker_refresh_bingkai);
    }

    function addMarker(arr,search,marker) {
        var found = arr.some(function (el) {
            return el.idpegawai === search;
        });
        if (!found) {
            arr.push({ 'idpegawai': search, 'marker': marker });
        }
    }

    function checkMarker(arr,search) {
        var hasil = false;
        var found = arr.some(function (el) {
            return el.idpegawai === search;
        });
        if (found) {
            hasil = true;
        }
        return hasil;
    }

    function getData(){
        $.ajax({
            type: "GET",
            url: '{{ url('logtrackerpegawairealtime/data') }}',
            data: '',
            cache: false,
            success: function(data) {
                try {
                    //penghapusan marker
                    for (var i = 0; i < old_data.length; i++) {
                        if (checkMarker(data, old_data[i]['idpegawai']) == false) {
                            for (var j = 0; j < markers.length; j++) {
                                if (markers[j]['idpegawai'] == old_data[i]['idpegawai']) {
                                    // hapus marker
                                    markers[j]['marker'].setMap(null);
                                    markers.splice(j, 1);
                                    markers_bingkai[j]['marker'].setMap(null);
                                    markers_bingkai.splice(j, 1);
                                }
                            }
                        }
                    }

                    //penambahan marker
                    for (var i = 0; i < data.length; i++) {
                        if (checkMarker(old_data, data[i]['idpegawai']) == false) {
                            // tambah marker
                            createMarker(data[i]);
                        }
                    }

                    //pergerakan marker
                    for (var i = 0; i < data.length; i++) {
                        for (var j = 0; j < old_data.length; j++) {
                            if (data[i]['idpegawai'] == old_data[j]['idpegawai']) {
                                 if (!(data[i]['lat'] == old_data[j]['lat'] && data[i]['lon'] == old_data[j]['lon'])) {
                                    var latlng = [data[i]['lat'], data[i]['lon']];
                                    var old_latlng = [old_data[j]['lat'], old_data[j]['lon']];
                                    for (var k = 0; k < markers.length; k++) {
                                        if (markers[k]['idpegawai'] == data[i]['idpegawai']) {
                                            transitions(markers[k]['marker'], markers_bingkai[k]['marker'], latlng, old_latlng);
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }
                    old_data = data.slice();
                }
                catch(err) {
                    console.log(err.message)
                }
                setTimeout(function(){ getData(); },5000);
            },
            error: function () {
                setTimeout(function(){ getData(); },5000);
            }
        });
    }

    $(function () {
        setTimeout(function(){ $('#tombolpeta').trigger('click'); },100);
        $('#tombolpeta').click(function(){
            initMap();
            getData();
        });
        setTimeout(function(){ $('#filterAtribut').css('display', ''); }, 1000);
        $('#filterAtribut').BootSideMenu({side:"right"});
        $('#resetfilter').click(function(){
            $('input:checkbox').removeAttr('checked');
            $('input:radio').removeAttr('checked');
        });
    });

    window.detailpegawai=(function(idpegawai,waktu){
        $("#showmodalpegawai").attr("href", "");
        $("#showmodalpegawai").attr("href", "{{ url('detailpegawai') }}/"+idpegawai);
        $('#showmodalpegawai').trigger('click');
        setTimeout(function(){
            $('#datatambahan').html(
                '<table style="margin-top:3px;">' +
                    '<tr>' +
                        '<td style="padding:0;width:70px">{{ trans('all.waktu') }}</td>' +
                        '<td style="padding:0">&nbsp;:&nbsp;&nbsp;<b>'+waktu+'</b></td>' +
                    '</tr>' +
                '</table>'
            );
        },200);
        return false;
    });

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: -4.653079918274038, lng: 117.7734375},
            zoom: 5,
            mapTypeId: 'roadmap',
            gestureHandling: 'greedy',
            fullscreenControl: false
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
            origin: new google.maps.Point(0, 0), // origin
            anchor: new google.maps.Point(10, 35) // anchor
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
                    alertInfo('{{ trans('all.lokasi') }} ' + lokasi[i][2]);
                    /*infowindow.setContent(lokasi[i][0]);
                     infowindow.open(map, marker_lokasi);*/
                }
            })(marker_lokasi, i));
        }

        if(logtrackers != '') {
            var initalBound = new google.maps.LatLngBounds();
            for (var l = 0; l < logtracker.length; l++) {
                initalBound.extend(new google.maps.LatLng(logtrackers[l]['lat'], logtrackers[l]['lon']));
                createMarker(logtrackers[l]);
            }
            old_data = logtrackers.slice();
            map.fitBounds(initalBound);
        }

        searchBox.addListener('places_changed', function () {
            var places = searchBox.getPlaces();

            if (places.length == 0) {
                return;
            }

            // Clear out the old markers.
            if (markers_pencarian != '') {
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
                markers_pencarian = new google.maps.Marker({
                    //position: place.geometry.location, //untuk kasih marker
                    map: map
                });

                markers_pencarian.addListener('click', function (event) {
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