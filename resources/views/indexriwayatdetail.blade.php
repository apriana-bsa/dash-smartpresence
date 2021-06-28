@if($deskripsibatasan != '')
    <div class="col-lg-12">
        <div class="alert alert-warning">
            <i class="fa fa-warning"></i>
            {{ $deskripsibatasan }}
        </div>
    </div>
@endif
<div class="col-lg-6">
    <div class="ibox float-e-margins">
        <div class="ibox-content" style="height:70vh">
            <ul class="list-group clear-list m-t">
                <li class="list-group-item" style="border-top: 0;">
                    <span class="pull-right">{{ $riwayat }}</span>
                    &nbsp;&nbsp;<span onclick="return goto('riwayat/{{ $tanggal }}')" style="cursor: pointer;" class="label label-info">{{ trans('all.beranda2_riwayat') }}</span></a>
                </li>
                @if($datacd->tampil_sudahbelumabsen == 'y')
                    <li class="list-group-item">
                        <span class="pull-right">{{ $sudahabsen.' ('.$persensudahabsen }}%)</span>
                        &nbsp;&nbsp;<span onclick="return goto('sudahabsen/{{ $tanggal }}')" style="cursor: pointer;" class="label label-primary">{{ trans('all.beranda2_sudahabsen') }}</span>
                    </li>
                    <li class="list-group-item">
                        <span class="pull-right">{{ $belumabsen.' ('.$persenbelumabsen }}%)</span>
                        &nbsp;&nbsp;<span onclick="return goto('belumabsen/{{ $tanggal }}')" style="cursor: pointer;" class="label label-danger">{{ trans('all.beranda2_belumabsen') }}</span>
                    </li>
                @endif
                @if($datacd->tampil_terlambatdll == 'y')
                    <li class="list-group-item">
                        <span class="pull-right">{{ $terlambat.' ('.$persenterlambat }}%)</span>
                        &nbsp;&nbsp;<span onclick="return goto('terlambat/{{ $tanggal }}')" style="cursor: pointer;" class="label label-warning">{{ trans('all.beranda2_terlambat') }}</span>
                    </li>
                @endif
                @if($datacd->tampil_pulangawaldll == 'y')
                    <li class="list-group-item">
                        <span class="pull-right">{{ $pulangawal }}</span>
                        &nbsp;&nbsp;<span onclick="return goto('pulangawal/{{ $tanggal }}')" style="cursor: pointer;" class="label label-danger">{{ trans('all.beranda2_pulangawal') }}</span>
                    </li>
                @endif
                <li class="list-group-item">
                    <span class="pull-right">{{ $lembur }}</span>
                    &nbsp;&nbsp;<span onclick="return goto('lembur/{{ $tanggal }}')" style="cursor: pointer;" class="label label-success">{{ trans('all.lembur') }}</span>
                </li>
                <li class="list-group-item">
                    <span class="pull-right">{{ $alasan }}</span>
                    &nbsp;&nbsp;<span onclick="return goto('alasan/{{ $tanggal }}')" style="cursor: pointer;" class="label label-info">{{ trans('all.beranda2_presensidenganalasan') }}</span>
                </li>
                <li class="list-group-item">
                    <span class="pull-right">{{ $datangawal }}</span>
                    &nbsp;&nbsp;<span onclick="return goto('datangawal/{{ $tanggal }}')" style="cursor: pointer;" class="label label-primary">{{ trans('all.beranda2_datangawal') }}</span>
                </li>
            </ul>
            <div class="row text-center">
                <div class="col-lg-4 col-md-3 col-sm-3 col-xs-4">
                    <canvas id="doughnutChart" style="margin-left:-10px"></canvas>
                    <h5>{{ trans('all.beranda2_ijintidakmasuk') }}</h5>
                </div>
                @if($datacd->tampil_terlambatdll == 'y')
                    <div class="col-lg-4 col-md-3 col-sm-3 col-xs-4">
                        <canvas id="doughnutChart2" style="margin-left:-10px"></canvas>
                        <h5>{{ trans('all.beranda2_terlambat') }}</h5>
                    </div>
                @endif
                <div class="col-lg-4 col-md-3 col-sm-3 col-xs-4">
                    <canvas id="doughnutChart3" style="margin-left:-10px"></canvas>
                    <h5>{{ trans('all.beranda2_presensidenganalasan') }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-6">
    <div class="ibox float-e-margins">
        <div style="padding: 0;">
            <input id="pac-input" class="controls" type="text" placeholder="Search Box">
            <div id="map" style="width:100%;height:70vh"></div>
            <button id="tombolpeta" style="display:none"></button>
        </div>
    </div>
</div>
{{--@if($datacd->tampil_totalgrafik == 'y')--}}
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>{{ trans('all.beranda2_keteranganchart') }}</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-lg-9">
                        <div class="flot-chart">
                            <div class="flot-chart-content" id="flot-dashboard-chart"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <ul class="stat-list">
                            <li>
                                <h2 class="no-margins">@if($jummasuk == "") 0 @else {{ $jummasuk }} @endif</h2>
                                <small>{{ trans('all.beranda2_rataratamasuk') }}</small>
                                <div class="stat-percent">{{ $rataratamasuk+0 }}%</div>
                                <div class="progress progress-mini">
                                    <div style="width: {{ $rataratamasuk+0 }}%;" class="progress-bar"></div>
                                </div>
                            </li>
                            <li>
                                <h2 class="no-margins">@if($jumtidakmasuk == "") 0 @else {{ $jumtidakmasuk }} @endif</h2>
                                <small>{{ trans('all.beranda_rataratatidakmasuk') }}</small>
                                <div class="stat-percent">{{ $rataratatidakmasuk+0 }}%</div>
                                <div class="progress progress-mini">
                                    <div style="width: {{ $rataratatidakmasuk+0 }}%;" class="progress-bar"></div>
                                </div>
                            </li>
                            <li>
                                <h2 class="no-margins">@if($jumterlambat == "") 0 @else {{ $jumterlambat }} @endif</h2>
                                <small>{{ trans('all.beranda2_ratarataterlambat') }}</small>
                                <div class="stat-percent">{{ $ratarataterlambat+0 }}%</div>
                                <div class="progress progress-mini">
                                    <div style="width: {{ $ratarataterlambat+0 }}%;" class="progress-bar"></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{--@endif--}}
<script type="text/javascript">
var locations = [];
var map = '';
var markerCluster = null;
var markers = [];

var dataTerlambat = [
  @if(Session::get('conf_webperusahaan') != '')
      @foreach($grafik as $key)
        [gd({{ $key->tanggal }}), {{ $key->jum_terlambat}}],
      @endforeach
  @endif
];

var dataMasuk = [
  @if(Session::get('conf_webperusahaan') != '')
        @foreach($grafik as $key)
        [gd({{ $key->tanggal }}), {{ $key->jum_masuk }}],
        @endforeach
  @endif
];

var dataset = [
  {
      label: "{{ trans('all.beranda2_hadir') }}",
      data: dataMasuk,
      color: "#1ab394",
      bars: {
          show: true,
          align: "center",
          barWidth: 24 * 60 * 60 * 600,
          lineWidth:0
      }
  },{
      label: "{{ trans('all.terlambat') }}",
      data: dataTerlambat,
      yaxis: 2,
      color: "#1C84C6",
      lines: {
          lineWidth:1,
          show: true,
          fill: true,
          fillColor: {
              colors: [{
                  opacity: 0.2
              }, {
                  opacity: 0.4
              }]
          }
      },
      splines: {
          show: false,
          tension: 0.6,
          lineWidth: 1,
          fill: 0.1
      },
  }
];


var options = {
  xaxis: {
      mode: "time",
      tickSize: [1, "day"],
      tickLength: 0,
      axisLabel: "Date",
      axisLabelUseCanvas: true,
      axisLabelFontSizePixels: 12,
      axisLabelFontFamily: 'Arial',
      axisLabelPadding: 10,
      color: "#d5d5d5"
  },
  yaxes: [{
      position: "left",
      max: {{ $totalpegawai }},
      color: "#d5d5d5",
      axisLabelUseCanvas: true,
      axisLabelFontSizePixels: 12,
      axisLabelFontFamily: 'Arial',
      axisLabelPadding: 3
  }, {
      position: "right",
      max: {{ $totalpegawai }},
      clolor: "#d5d5d5",
      axisLabelUseCanvas: true,
      axisLabelFontSizePixels: 12,
      axisLabelFontFamily: ' Arial',
      axisLabelPadding: 67
  }
  ],
  legend: {
      noColumns: 1,
      labelBoxBorderColor: "#000000",
      position: "nw"
  },
  grid: {
      hoverable: true,
      borderWidth: 0
  }
};

var previousPoint = null, previousLabel = null;

$.plot($("#flot-dashboard-chart"), dataset, options);

function gd(year, month, day) {
  return new Date(year, month - 1, day).getTime();
}

var lokasi;
@if($lokasi != '')
        lokasi = [
        @foreach($lokasi as $key)
    [{{ $key->lat }}, {{ $key->lon }}, '{{ $key->nama }}'],
    @endforeach
];
@endif

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        //center: {lat: -8.699, lng: 115.201}, //{lat: -31.563910, lng: 147.154312}
        //center: {lat: -31.563910, lng: 147.154312},
        center: {lat: -4.653079918274038, lng:117.7734375},
        //zoom: 13,
        zoom: 4,
        mapTypeId: 'roadmap',
        gestureHandling: 'greedy',
        fullscreenControl: false,
        //styles: styleGoogleMaps
    });

    var mapMaxZoom = 13;
    var geoloccontrol = new klokantech.GeolocationControl(map, mapMaxZoom);

    // Create the search box and link it to the UI element.
    var input = document.getElementById('pac-input');
    var searchBox = new google.maps.places.SearchBox(input);
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });

    var marker_lokasi, i;

    var icon = {
        url: '{{ url('lib/drawable-xhdpi/perusahaan.png') }}', // url
        scaledSize: new google.maps.Size(24, 24), // scaled size
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
                //console.log(lokasi[i][2])
                alertInfo('{{ trans('all.lokasi') }} '+lokasi[i][2]);
                /*infowindow.setContent(lokasi[i][0]);
                 infowindow.open(map, marker_lokasi);*/
            }
        })(marker_lokasi, i));
    };

    searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        // Clear out the old markers.
        if(markers != ''){
            //markers.setMap(null);
        }

        // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();
        places.forEach(function(place) {
            if (!place.geometry) {
//                console.log("Returned place contains no geometry");
                return;
            }

            // Create a marker for each place.
            markers = new google.maps.Marker({
                //position: place.geometry.location, //untuk kasih marker
                map: map
            });

            markers.addListener('click', function(event) {
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

$(document).ready(function() {
    setTimeout(function(){ $('#tombolpeta').trigger('click'); },100);
    $('#tombolpeta').click(function(){
        initMap();
    });

    setTimeout(function(){
        $.ajax({
            type: "GET",
            url: '{{ url('lokasiabsen/'.$tanggal) }}',
            data: '',
            cache: false,
            success: function(html){
                $('#loading-saver').css('display', 'none');
                //hapus marker lama
                if (markerCluster!=null) {
                    markerCluster.clearMarkers();
                }

                //set lokasi map ke awal
                map.setCenter({lat: -4.653079918274038, lng:117.7734375});

                if(html != ''){
                    locations = html;

                    var icon = {
                        url: '{{ url('lib/drawable-xhdpi/flag_orang_absen.png') }}', // url
                        scaledSize: new google.maps.Size(40, 40), // scaled size
                        origin: new google.maps.Point(0,0), // origin
                        anchor: new google.maps.Point(10, 35) // anchor
                    };

                    //kasih marker baru
                    markers = locations.map(function(location) {
                        return new google.maps.Marker({
                            position: location,
                            map: map,
                            flag: 'log',
                            id: location.id,
                            icon: icon
                        });
                    });

                    for(var i=0;i<markers.length;i++) {
                        google.maps.event.addDomListener(markers[i], 'click', function() {
                            modalMarkerPeta(this.id,'o');
                        });
                    }


                    markerCluster = new MarkerClusterer(map, markers,
                            {zoomOnClick: false, imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});

                    google.maps.event.addListener(markerCluster, "clusterclick", function (cluster) {
                        //console.log(cluster.markers_[0].id);
                        var _marker = '';
                        for(var i=0;i<cluster.markers_.length;i++){
                            //console.log(cluster.markers_[i].id);
                            _marker += ','+cluster.markers_[i].id;
                        }
                        modalMarkerPeta(_marker.substring(1),'o');
                    });


                    //console.log(markerCluster.clusters_);
                    //markerCluster = new MarkerClusterer(map, markers, mcOptions);
                }
            }
        });
    },200);

    var ijintidakmasukData = [
        {
            value: {{ $sudahabsen }},
            color: "#a3e1d4",
            highlight: "#1ab394",
            label: "{{ trans('all.sudahabsen') }}"
        },
        {
            value: {{ $ijintidakmasuk }},
            color: "#dedede",
            highlight: "#1ab394",
            label: "{{ trans('all.ijintidakmasuk') }}"
        },
        {
            value: {{ $belumabsen }},
            color: "#b5b8cf",
            highlight: "#1ab394",
            label: "{{ trans('all.belumabsen') }}"
        }
    ];

    var terlambatData = [
        {
            value: {{ $sudahabsen }},
            color: "#a3e1d4",
            highlight: "#1ab394",
            label: "{{ trans('all.sudahabsen') }}"
        },
        {
            value: {{ $terlambat }},
            color: "#dedede",
            highlight: "#1ab394",
            label: "{{ trans('all.terlambat') }}"
        },
        {
            value: {{ $belumabsen }},
            color: "#b5b8cf",
            highlight: "#1ab394",
            label: "{{ trans('all.belumabsen') }}"
        }
    ];

    var presensidenganalasanData = [
        {
            value: {{ $sudahabsen }},
            color: "#a3e1d4",
            highlight: "#1ab394",
            label: "{{ trans('all.sudahabsen') }}"
        },
        {
            value: {{ $alasan }},
            color: "#dedede",
            highlight: "#1ab394",
            label: "{{ trans('all.presensidenganalasan') }}"
        },
        {
            value: {{ $belumabsen }},
            color: "#b5b8cf",
            highlight: "#1ab394",
            label: "{{ trans('all.belumabsen') }}"
        }
    ];

    var doughnutOptions = {
        segmentShowStroke: true,
        segmentStrokeColor: "#fff",
        segmentStrokeWidth: 2,
        percentageInnerCutout: 45, // This is 0 for Pie charts
        animationSteps: 100,
        animationEasing: "easeOutBounce",
        animateRotate: true,
        animateScale: false,
        responsive: true
    };


    var ctx = document.getElementById("doughnutChart").getContext("2d");
    var ctx2 = document.getElementById("doughnutChart2").getContext("2d");
    var ctx3 = document.getElementById("doughnutChart3").getContext("2d");
    var myNewChart = new Chart(ctx).Doughnut(ijintidakmasukData, doughnutOptions);
    var myNewChart2 = new Chart(ctx2).Doughnut(terlambatData, doughnutOptions);
    var myNewChart3 = new Chart(ctx3).Doughnut(presensidenganalasanData, doughnutOptions);
});
</script>