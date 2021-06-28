@extends('layouts.master')
@section('title', trans('all.lokasi'))
@section('content')
  
  <script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>
  @if(Session::get('message'))
    <script>
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
    </script>
  @endif
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_lokasi') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li class="active"><strong>{{ trans('all.menu_lokasi') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <table width="100%">
          <tr>
              @if(strpos(Session::get('hakakses_perusahaan')->lokasi, 't') !== false || strpos(Session::get('hakakses_perusahaan')->lokasi, 'm') !== false)
                  <td>
                      <a href="{!! url('datainduk/pegawai/lokasi/create') !!}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                  </td>
              @endif
              <td>
                  <button class="btn btn-primary pull-right" onclick="return ke('{{ url('lokasi/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
              </td>
          </tr>
        </table>
        <p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    <td class="opsi2"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    <td class="nama"><b>{{ trans('all.lokasi') }}</b></td>
                    <td class="nama"><b>{{ trans('all.lat') }}</b></td>
                    <td class="nama"><b>{{ trans('all.lon') }}</b></td>
                    <td class="opsi5"><b><center>{{ trans('all.jaraktoleransi') }}</center></b></td>
                    <td class="opsi2"><b>{{ trans('all.radius') }}</b></td>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal peta-->
  <a href="#" id="tombolmodalpeta" style="display:none" data-toggle="modal" data-target="#modalPeta"></a>
  <div class="modal fade" id="modalPeta" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">{{ trans('all.peta') }}</h4>
              </div>
              <div>
                  <table width='100%'>
                      <tr>
                          <td colspan=2>
                              <input id="pac-input" class="controls pac-input" type="text" placeholder="Search Box">
                              <div id="map" style="height:500px"></div>
                          </td>
                      </tr>
                  </table>
              </div>
              <div class="footer">
                  <button data-dismiss="modal" id="tutupmodal" class="btn btn-primary pull-right"><i class='fa fa-undo'></i> {{ trans('all.tutup') }}</button>
              </div>
          </div>
      </div>
  </div>
  <!-- Modal peta-->
@stop

@push('scripts')
<script>
var firstRun = true;
var markers = null;
var map = '';
var lokasi;
window.lihatLokasi=(function(lat,lon){
    //menghapus keterangan yg lama dan mengganti keterangan yg baru keterangan peta riwayat presensi
    if(markers != null){
        //hilangkan marker yg lama
        markers.setMap(null);
        //set posisi peta ke default
        map.setCenter({lat: -4.653079918274038, lng:117.7734375});
    }
    setTimeout(function(){
        //kasih marker di lokasi baru
        var myLatlng = new google.maps.LatLng(lat,lon);
        markers = new google.maps.Marker({
            position: myLatlng,
            map: map
        });
        //set posisi peta berdasarkan marker
        map.setCenter(markers.getPosition());
        //jika marker di klik
        markers.addListener('click', function(event) {
            map.setZoom(18);
            map.setCenter(markers.getPosition());
        });
    },1000);
    //tampilkan modal marker
    $('#tombolmodalpeta').trigger('click');
})

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: -4.653079918274038, lng:117.7734375},
        zoom: 18,
        mapTypeId: 'roadmap',
        gestureHandling: 'greedy',
        fullscreenControl: false,
        //styles: styleGoogleMaps
    });

    var mapMaxZoom = 18;
    var geoloccontrol = new klokantech.GeolocationControl(map, mapMaxZoom);

    // Create the search box and link it to the UI element.
    var input = document.getElementById('pac-input');
    var searchBox = new google.maps.places.SearchBox(input);
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });

    var icon = {
        url: '{{ url('lib/drawable-xhdpi/perusahaan.png') }}', // url
        scaledSize: new google.maps.Size(30, 30), // scaled size
        origin: new google.maps.Point(0,0), // origin
        anchor: new google.maps.Point(10, 35) // anchor
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
//                console.log("Returned place contains no geometry");
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

$('#modalPeta').on('shown.bs.modal', function(){
    if (firstRun==true) {
        firstRun = false;
        initMap();
    }
});

$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("datainduk/pegawai/lokasi/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'nama', name: 'nama' },
            { data: 'lat', name: 'lat' },
            { data: 'lon', name: 'lon' },
            { data: 'jaraktoleransi', name: 'jaraktoleransi' },
            { data: 'radius', name: 'radius' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script>
@endpush