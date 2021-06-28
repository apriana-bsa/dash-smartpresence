@extends('layouts.master')
@section('title', trans('all.lokasi'))
@section('content')

	<script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>
	<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script>
	<style>
	td{
		padding:5px;
	}

	#map {
        height: 480px;
    }

    #pac-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 300px;
        z-index:99999999;
    }

    #pac-input:focus {
        border-color: #4d90fe;
    }

    .pac-container {
        font-family: Roboto;
    }

	.delete-menu {
        position: absolute;
        background: white;
        padding: 3px;
        color: #666;
        font-weight: bold;
        border: 1px solid #999;
        font-family: sans-serif;
        font-size: 12px;
        box-shadow: 1px 3px 3px rgba(0, 0, 0, .3);
        margin-top: -10px;
        margin-left: 10px;
        cursor: pointer;
      }
      .delete-menu:hover {
        background: #eee;
      }

	.wrapper-content {
    	padding: 5px 10px 40px !important;
	}
	</style>
	<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_lokasi').' ('.$namalokasi.')' }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
	  	<li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.menu_lokasi') }}</li>
        <li class="active"><strong>{{ trans('all.area') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
			<form action="{{ url('datainduk/pegawai/lokasi/'.$idlokasi.'/submitarea')}}" method="post">
				{{ csrf_field() }}
				<input type="hidden" name="latlng" id="latlng" value="{{ $dataarealatlng }}">
				<table width="480px">
					<tr>
						<td style="padding-left: 0">
							<button type="submit" name="simpan" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>&nbsp;&nbsp;
							<button type="button" onclick="return restorePolygon()" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>&nbsp;&nbsp;
							<button type="button" onclick="return ke('{{ url('datainduk/pegawai/lokasi')}}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
						</td>
					</tr>
				</table>
			</form>
          {{-- <div class="ibox-content"> --}}
						<input id="pac-input" class="controls" type="text" placeholder="Search Box">
            <div id="map"></div>
          {{-- </div> --}}
        </div>
      </div>
    </div>
  </div>
  
@stop

@push('scripts')
	<script>
		$(function(){
			initMap();
		})
		//init peta
		var map = '';
		var myPolygon;
		var deleteMenu;
		function initMap() {
			map = new google.maps.Map(document.getElementById('map'), {
					center: {lat: -4.653079918274038, lng: 117.7734375},
					zoom: 5,
					gestureHandling: 'greedy',
					mapTypeId: 'roadmap',
					//styles: styleGoogleMaps
			});

			// Polygon Coordinates
			var triangleCoords = [
					@if(count($datapoligon) > 1)
						@foreach($datapoligon as $key)
							new google.maps.LatLng({{ $key->lat }},{{ $key->lon }}),
						@endforeach
					@else
						new google.maps.LatLng(-5.94005,110.79068),
						new google.maps.LatLng(-8.03983,114.23133),
						new google.maps.LatLng(-7.92633,104.99376)
					@endif
			];

			@if(count($datapoligon) > 1)
				var bounds = new google.maps.LatLngBounds();
				for (var i = 0; i < triangleCoords.length; i++) {
					bounds.extend(triangleCoords[i]);
				}
				map.fitBounds(bounds);
			@endif

			// Styling & Controls
			myPolygon = new google.maps.Polygon({
				paths: triangleCoords,
				draggable: true, // turn off if it gets annoying
				editable: true,
				strokeColor: '#FF0000',
				strokeOpacity: 0.8,
				strokeWeight: 2,
				fillColor: '#FF0000',
				fillOpacity: 0.35
			});

			myPolygon.setMap(map);

			deleteMenu = new DeleteMenu();

			google.maps.event.addListener(myPolygon, 'rightclick', function(e) {
				// Check if click was on a vertex control point
				if (e.vertex == undefined) {
					return;
				}
				deleteMenu.open(map, myPolygon.getPath(), e.vertex);
			});

			google.maps.event.addListener(myPolygon.getPath(), "insert_at", getPolygonCoords);
	//            google.maps.event.addListener(myPolygon.getPath(), "remove_at", getPolygonCoords);
			google.maps.event.addListener(myPolygon.getPath(), "set_at", getPolygonCoords);

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

			searchBox.addListener('places_changed', function () {
					var places = searchBox.getPlaces();

					if (places.length == 0) {
							return;
					}

					// For each place, get the icon, name and location.
					var bounds = new google.maps.LatLngBounds();
					places.forEach(function (place) {
							if (!place.geometry) {
//									console.log("Returned place contains no geometry");
									return;
							}

							// Create a marker for each place.
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

		function DeleteMenu() {
			this.div_ = document.createElement('div');
			this.div_.className = 'delete-menu';
			this.div_.innerHTML = 'Delete';

			var menu = this;
			google.maps.event.addDomListener(this.div_, 'click', function() {
				menu.removeVertex();
			});
		}

		DeleteMenu.prototype = new google.maps.OverlayView();

		DeleteMenu.prototype.onAdd = function() {
			var deleteMenu = this;
			var map = this.getMap();
			this.getPanes().floatPane.appendChild(this.div_);

			// mousedown anywhere on the map except on the menu div will close the
			// menu.
			this.divListener_ = google.maps.event.addDomListener(map.getDiv(), 'mousedown', function(e) {
				if (e.target != deleteMenu.div_) {
					deleteMenu.close();
				}
			}, true);
		};

		DeleteMenu.prototype.onRemove = function() {
			google.maps.event.removeListener(this.divListener_);
			this.div_.parentNode.removeChild(this.div_);

			// clean up
			this.set('position');
			this.set('path');
			this.set('vertex');
		};

		DeleteMenu.prototype.close = function() {
			this.setMap(null);
		};

		DeleteMenu.prototype.draw = function() {
			var position = this.get('position');
			var projection = this.getProjection();

			if (!position || !projection) {
				return;
			}
			
			var point = projection.fromLatLngToDivPixel(position);
			this.div_.style.top = point.y + 'px';
			this.div_.style.left = point.x + 'px';
		};

		/**
		* Opens the menu at a vertex of a given path.
		*/
		DeleteMenu.prototype.open = function(map, path, vertex) {
			this.set('position', path.getAt(vertex));
			this.set('path', path);
			this.set('vertex', vertex);
			this.setMap(map);
			this.draw();
		};

		/**
		* Deletes the vertex from the path.
		*/
		DeleteMenu.prototype.removeVertex = function() {
			var path = this.get('path');
			var vertex = this.get('vertex');
			if (!path || vertex == undefined) {
				this.close();
				return;
			}
			path.removeAt(vertex);
			this.close();
			getPolygonCoords();
		};

		function restorePolygon(){
			alertConfirm('{{ trans('all.apakahandayakin') }}', function(){
				myPolygon.setMap(null);

				var triangleCoords = [
						new google.maps.LatLng(-5.94005,110.79068),
						new google.maps.LatLng(-8.03983,114.23133),
						new google.maps.LatLng(-7.92633,104.99376)
				];
				// Styling & Controls
				myPolygon = new google.maps.Polygon({
						paths: triangleCoords,
						draggable: true, // turn off if it gets annoying
						editable: true,
						strokeColor: '#FF0000',
						strokeOpacity: 0.8,
						strokeWeight: 2,
						fillColor: '#FF0000',
						fillOpacity: 0.35
				});

				myPolygon.setMap(map);

				$('#latlng').val('-5.94005,110.79068#-8.03983,114.23133#-7.92633,104.99376');
			});
		}

		function getPolygonCoords() {
			var len = myPolygon.getPath().getLength();
			// console.log(myPolygon.getPath().getAt(8).toUrlValue(5));
			// if(len > 9){
			// 	alertWarning('{{ trans('all.jumlahtitikmaksimal30') }}');
			// }
			var htmlStr = "";
			for (var i = 0; i < len; i++) {
					htmlStr += "#" + myPolygon.getPath().getAt(i).toUrlValue(5);
			}
			$('#latlng').val(htmlStr.substring(1));
		}
	</script>
@endpush