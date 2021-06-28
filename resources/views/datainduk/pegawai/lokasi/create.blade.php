@extends('layouts.master')
@section('title', trans('all.lokasi'))
@section('content')

	<style>
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
    </style>
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
            toastr.warning('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
          }, 500);
        });
    @endif
    
    var firstRun = true;
	function validasi(){
        
        $('#submit').attr( 'data-loading', '' );
        $('#submit').attr('disabled', 'disabled');
        $('#kembali').attr('disabled', 'disabled');
        $('#tombolpeta').attr('disabled', 'disabled');

		var nama = $("#nama").val();
        var lat = $("#lat").val();
        var lon = $("#lon").val();
        var radius = $("#radius").val();
        var jaraktoleransi = $('#jaraktoleransi').val();

		@if(Session::has('conf_webperusahaan'))
		@else
			alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
                $('#tombolpeta').removeAttr('disabled');
			    aktifkanTombol();
            });
            return false;
		@endif

		if(nama === ""){
			alertWarning("{{ trans('all.namakosong') }}",
            function() {
              aktifkanTombol();
              $('#tombolpeta').removeAttr('disabled');
              setFocus($('#nama'));
            });
            return false;
		}

        if(lat === ""){
          alertWarning("{{ trans('all.latkosong') }}",
                function() {
                  aktifkanTombol();
                  $('#tombolpeta').removeAttr('disabled');
                  setFocus($('#lat'));
                });
          return false;
        }

        if(lon === ""){
          alertWarning("{{ trans('all.lonkosong') }}",
                function() {
                  aktifkanTombol();
                  $('#tombolpeta').removeAttr('disabled');
                  setFocus($('#lon'));
                });
          return false;
        }

        if(!is_valid_lat_lon(lat+','+lon)){
            alertWarning("{{ trans('all.latlontidakvalid') }}",
                function() {
                    aktifkanTombol();
                    $('#tombolpeta').removeAttr('disabled');
                });
            return false;
        }

        if(jaraktoleransi === 'ditentukan'){
            if (cekAlertAngkaValid(radius,1,99999,0,"{{ (trans('all.radius')) }}",
                    function() {
                        $('#tombolpeta').removeAttr('disabled');
                        aktifkanTombol();
                        setFocus($('#radius'));
                    }
                )==false) return false;
        }
    }

    function setRadius(){
	    var jaraktoleransi = $('#jaraktoleransi').val();
        $('#tr_radius').css('display', 'none');
        $('#radius').val('0');
	    if(jaraktoleransi === 'ditentukan'){
	        $('#tr_radius').css('display', '');
        }
    }

    function penentuanLokasi(){
        var penentuanlokasi = $('#penentuanlokasi').val();
        $('.tr_radius').css('display', 'none');
        $('#jaraktoleransi').val('default');
        if(penentuanlokasi === 'radius'){
            $('.tr_radius').css('display', '');
        }
        setRadius();
    }

  $(function(){
    $('#dapatkanlokasi').click(function(){
      var lat = $("#latpopup").html();
      var lon = $("#lonpopup").html();
      $("#lat").val(lat);
      $("#lon").val(lon);
      $("#latpopup").html('');
      $("#lonpopup").html('');
      $("#closemodal").trigger('click');
    });
    setRadius();
    penentuanLokasi();
  });
	</script>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_lokasi') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.menu_lokasi') }}</li>
        <li class="active"><strong>{{ trans('all.tambahdata') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content">
          	<form action="{{ url('datainduk/pegawai/lokasi') }}" method="post" onsubmit="return validasi()">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <table width="480px">
                    <tr>
                        <td width="160px">{{ trans('all.nama') }}</td>
                        <td>
                            <input type="text" class="form-control" autofocus autocomplete="off" name="nama" id="nama" maxlength="30">
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <button style='margin-bottom:0' type="button" id="tombolpeta" class="btn btn-success" data-toggle="modal" data-target="#modalPeta"><i class="fa fa-map-marker"></i>&nbsp;&nbsp;{{ trans('all.peta') }}</button>&nbsp;
                        </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.lat') }}</td>
                      <td style="float:left">
                        <input type="text" class="form-control" size="20" autocomplete="off" name="lat" id="lat" maxlength="30">
                      </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.lon') }}</td>
                      <td style="float:left">
                        <input type="text" class="form-control" size="20" autocomplete="off" name="lon" id="lon" maxlength="30">
                      </td>
                    </tr>
                    <tr>
                        <td>{{ trans('all.penentuanlokasi') }}</td>
                        <td style="float:left">
                            <select name="penentuanlokasi" id="penentuanlokasi" class="form-control" onchange="penentuanLokasi()">
                                <option value="radius">{{ trans('all.radius') }}</option>
                                <option value="poligon">{{ trans('all.polygon') }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="tr_radius">
                      <td>{{ trans('all.jaraktoleransi') }}</td>
                      <td style="float:left">
                        <select name="jaraktoleransi" id="jaraktoleransi" class="form-control" onchange="setRadius()">
                            <option value="default">{{ trans('all.default') }}</option>
                            <option value="ditentukan">{{ trans('all.ditentukan') }}</option>
                        </select>
                      </td>
                    </tr>
                    <tr id="tr_radius">
                        <td>{{ trans('all.radius') }}</td>
                        <td style="float:left">
                            <input type="text" class="form-control" size="5" onkeypress="return onlyNumber(0,event)" autocomplete="off" name="radius" id="radius" maxlength="5">
                        </td>
                        <td style="float:left;margin-top: 7px;">{{ trans('all.meter') }}</td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                            <button type="button" id="kembali" onclick="return ke('../lokasi')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                        </td>
                    </tr>
                </table>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal peta-->
    <div class="modal fade" id="modalPeta" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">
        
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ trans('all.peta') }}</h4>
          </div>
          <!-- <div class="modal-body" style="height:460px;overflow: auto;"> -->
          <div>
            <table width='100%'>
              <tr>
                <td colspan=2>
                  <input id="pac-input" class="controls pac-input" type="text" placeholder="Search Box">
                  <div id="map"></div>
                </td>
              </tr>
              <tr>
                <td>
                  <i>
                    Lat : <span id="latpopup"></span><br>
                    Lon : <span id="lonpopup"></span>
                  </i>
                </td>
                <td align=right>
                  <button type="button" id="dapatkanlokasi" class="btn btn-success"><i class='fa fa-map-marker'></i> {{ trans('all.simpan') }}</button>&nbsp;&nbsp;
                  <button data-dismiss="modal" id="tutupmodal" class="btn btn-primary"><i class='fa fa-undo'></i> {{ trans('all.tutup') }}</button>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal peta-->

    <script>
    function initMap() {
      var map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: -8.699, lng: 115.201},
        zoom: 13,
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

      var markers = '';

      //click on any place of maps / klik di manapun di daerah map
      map.addListener('click', function( event ){
        if(markers != ''){
            markers.setMap(null);
        }

        getlatlon(event);
        var myLatlng = new google.maps.LatLng(event.latLng.lat(),event.latLng.lng());
        markers = new google.maps.Marker({
          position: myLatlng,
          map: map
        });
      });

      // Listen for the event fired when the user selects a prediction and retrieve
      // more details for that place. / pencarian lokasi
      searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
          return;
        }

        // Clear out the old markers.
          if(markers != ''){
              markers.setMap(null);
          }

        // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();
        places.forEach(function(place) {
          if (!place.geometry) {
//            console.log("Returned place contains no geometry");
            return;
          }

          // Create a marker for each place.
            markers = new google.maps.Marker({
                position: place.geometry.location,
                map: map
            });

            markers.addListener('click', function(event) {
              getlatlon(event);
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

    function getlatlon(event){
      document.getElementById('latpopup').innerHTML=event.latLng.lat();
      document.getElementById('lonpopup').innerHTML=event.latLng.lng();
    }
    $('#modalPeta').on('shown.bs.modal', function(){
      if (firstRun==true) {
        firstRun = false;
        initMap();
      }
        if($('#latpopup').html() == ''){
            $('#latpopup').html($('#lat').val());
        }
        if($('#lonpopup').html() == ''){
            $('#lonpopup').html($('#lon').val());
        }
    });
    </script>
    <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script>
@stop