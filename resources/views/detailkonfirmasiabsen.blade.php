<script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>
<style>
    .tdmodalDP{
        padding:3px;
    }
</style>
<!-- Modal content-->
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
        {{--<h4 class="modal-title">{{ trans('all.'.$jenis) }}</h4>--}}
        <h4 class="modal-title">{{ trans('all.'.($jenis == 'konfirmasiflag' ? 'konfirmasi_flag' : $jenis)) }}</h4>
    </div>
    <div class="modal-body body-modal">
        <table width="100%">
            @if($jenis == 'logabsen' or $jenis == 'konfirmasiflag')
                <tr>
                    <td valign="top" class="tdmodalDP" colspan="3" style="padding-bottom:15px;">
                        <input id="pac-input_konfirmasiabsen" class="controls pac-input" type="text" placeholder="Search Box">
                        <div id="map_konfirmasiabsen" style="height:200px" lat="{{ $data->lat }}" lon="{{ $data->lon }}"></div>
                    </td>
                </tr>
            @endif
            <tr>
                @if($jenis == 'logabsen' or $jenis == 'konfirmasiflag')
                    @if($jenis == 'konfirmasiflag')
                        @if($data->idlogabsen != '')
                            <td class="tdmodalDP">
                                <center>
                                    <a href="{{ url('fotologabsen/'.$data->idlogabsen.'/normal') }}" title="{{ $data->nama }}" data-gallery="">
                                        <img id="imgInp" width=120 style='border-radius: 50%;' height=120 src="{{ url('fotologabsen/'.$data->idlogabsen.'/thumb') }}">
                                    </a><br>
                                    {{ trans('all.fotologabsen') }}
                                </center>
                            </td>
                        @endif
                    @else
                        <td class="tdmodalDP">
                            <center>
                                <a href="{{ url('fotologabsen/'.$data->idkonfirmasiabsen.'/normal') }}" title="{{ $data->nama }}" data-gallery="">
                                    <img id="imgInp" width=120 style='border-radius: 50%;' height=120 src="{{ url('fotologabsen/'.$data->idkonfirmasiabsen.'/thumb') }}">
                                </a><br>
                                {{ trans('all.fotologabsen') }}
                            </center>
                        </td>
                    @endif
                @endif
                <td class="tdmodalDP">
                    <center>
                        <img id="imgInp" width=120 style='border-radius: 50%;' height=120 src="{{ url('foto/pegawai/'.$data->idpegawai) }}"><br>
                        @if($jenis == 'logabsen' or $jenis == 'konfirmasiflag') {{ trans('all.fotoprofil') }} @endif
                    </center>
                </td>
                <td class="tdmodalDP">
                    <table>
                        <tr>
                            @if($jenis != 'logabsen' and $jenis != 'konfirmasiflag')
                                <td class="tdmodalDP">{{ trans('all.nama') }}</td>
                                <td class="tdmodalDP">:</td>
                                <td class="tdmodalDP"><b>{{ $data->nama }}</b></td>
                            @else
                                <td class="tdmodalDP"><center><b>{{ $data->nama }}</b></center></td>
                            @endif
                        </tr>
                        @if($jenis == 'logabsen' or $jenis == 'konfirmasiflag')
                            @if($data->nilai != '')
                                <tr>
                                    <td class="tdmodalDP"><center>{!! $data->nilai !!}</center></td>
                                </tr>
                            @endif
                            <tr>
                                <td class="tdmodalDP"><center>{{ $data->waktu }}</center></td>
                            </tr>
                            @if($jenis == 'logabsen' and $data->alasan != '')
                                <tr>
                                    <td class="tdmodalDP"><center>{{ $data->alasan }}</center></td>
                                </tr>
                            @endif
                            @if($data->konfirmasi != '' and $jenis == 'logabsen')
                                <tr>
                                    <td class="tdmodalDP"><center>{{ $data->konfirmasi }}</center></td>
                                </tr>
                            @endif
                            @if($data->masukkeluar != '')
                                <tr>
                                    <td class="tdmodalDP"><center>{!! $data->masukkeluar !!}</center></td>
                                </tr>
                            @endif
                            @if($jenis == 'konfirmasiflag')
                                <tr>
                                    <td class="tdmodalDP">
                                        <center><label class="label label-warning">{{ trans('all.pengajuan').' : '.trans('all.'.str_replace('-','',$data->flag)) }}</label></center>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tdmodalDP">
                                        <center>{{ $data->keterangan }}</center>
                                    </td>
                                </tr>
                            @endif
                            @if($jenis == 'logabsen' && (strpos(Session::get('hakakses_perusahaan')->logabsen, 'k') !== false || strpos(Session::get('hakakses_perusahaan')->logabsen, 'm') !== false))
                                <tr>
                                    <td class="tdmodalDP">
                                        <textarea class="form-control" name="flagketerangan" placeholder="{{ trans('all.keterangan') }}" id="flagketerangan" style="resize:none" maxlength="255"></textarea>
                                    </td>
                                </tr>
                            @endif
                            @if($jenis == 'konfirmasiflag' && (strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'k') !== false || strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'm') !== false))
                                <tr>
                                    <td class="tdmodalDP">
                                        <textarea class="form-control" name="flagketerangan" placeholder="{{ trans('all.keterangan') }}" id="flagketerangan" style="resize:none" maxlength="255"></textarea>
                                    </td>
                                </tr>
                            @endif
                        @endif
                        @if($jenis == 'ijintidakmasuk')
                            <tr>
                                <td class="tdmodalDP">{{ trans('all.pin') }}</td>
                                <td class="tdmodalDP">:</td>
                                <td class="tdmodalDP">{{ $data->pin }}</td>
                            </tr>
                            <tr>
                                <td class="tdmodalDP">{{ trans('all.nomorhp') }}</td>
                                <td class="tdmodalDP">:</td>
                                <td class="tdmodalDP">{{ $data->nomorhp }}</td>
                            </tr>
                            <tr>
                                <td class="tdmodalDP">{{ trans('all.jamkerja') }}</td>
                                <td class="tdmodalDP">:</td>
                                <td class="tdmodalDP">{{ $data->jamkerja }}</td>
                            </tr>
                        @endif
                        @if($jenis == 'logabsen' && (strpos(Session::get('hakakses_perusahaan')->logabsen, 'k') !== false ||strpos(Session::get('hakakses_perusahaan')->logabsen, 'm') !== false))
{{--                        @if(strpos(Session::get('hakakses_perusahaan')->notifikasiijintidakmasuk, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasitidakterlambat, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasipulangawal, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasilembur, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasilokasitidakcocok, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasiwajahdiragukan, 'i') !== false)--}}
                            @if($jenis != 'ijintidakmasuk')
                                <tr>
                                    <td colspan="3" style="padding-top:10px">
                                        <button id="terima" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'terima')" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check'></i>&nbsp;&nbsp;{{ trans('all.terima') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                        <button id="tolak" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'tolak')" class="ladda-button btn btn-danger slide-left"><span class="label2"><i class='fa fa-times'></i>&nbsp;&nbsp;{{ trans('all.tolak') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                    </td>
                                </tr>
                            @endif
                        @endif
                        @if($jenis == 'konfirmasiflag' && (strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'k') !== false ||strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'm') !== false))
{{--                        @if(strpos(Session::get('hakakses_perusahaan')->notifikasiijintidakmasuk, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasitidakterlambat, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasipulangawal, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasilembur, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasilokasitidakcocok, 'i') !== false || strpos(Session::get('hakakses_perusahaan')->notifikasiwajahdiragukan, 'i') !== false)--}}
                            @if($jenis != 'ijintidakmasuk')
                                <tr>
                                    <td colspan="3" style="padding-top:10px">
                                        <button id="terima" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'terima')" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check'></i>&nbsp;&nbsp;{{ trans('all.terima') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                        <button id="tolak" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'tolak')" class="ladda-button btn btn-danger slide-left"><span class="label2"><i class='fa fa-times'></i>&nbsp;&nbsp;{{ trans('all.tolak') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                    </td>
                                </tr>
                            @endif
                        @endif
                    </table>
                </td>
            </tr>
            @if($jenis == 'logabsen' or $jenis == 'konfirmasiflag')
                @if($datafacesample != '')
                    <tr>
                        <td colspan="3" class="tdmodalDP"><b>{{ trans('all.facesample') }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="tdmodalDP" style="">
                            <center style="overflow: auto;max-width:570px;white-space: nowrap">
                                @foreach($datafacesample as $key)
                                    <img id="imgInp" width=120 style='border-radius: 50%;' height=120 src="{{ url('getfacesample/'.$key->id.'/_thumb') }}">&nbsp;&nbsp;
                                @endforeach
                            </center>
                        </td>
                    </tr>
                @endif
            @endif
            @if($jenis == 'ijintidakmasuk')
                <tr>
                    <td class="tdmodalDP">
                        @if($data->filename != '')
                            <center>
                                <a href="{{ url('fotonormal/ijintidakmasuk/'.$data->idkonfirmasiabsen) }}" title="{{ trans('all.lampiran') }}" data-gallery="">
                                    <img id="imgInp" width=120 src="{{ url('foto/ijintidakmasuk/'.$data->idkonfirmasiabsen) }}">
                                </a>
                            </center>
                        @endif
                    </td>
                    <td class="tdmodalDP">
                        <table>
                            <tr>
                                <td class="tdmodalDP">{{ trans('all.kategoriijin') }}</td>
                                <td class="tdmodalDP">:</td>
                                <td class="tdmodalDP">{{ $data->alasantidakmasuk }}</td>
                            </tr>
                            <tr>
                                <td class="tdmodalDP">{{ trans('all.waktu') }}</td>
                                <td class="tdmodalDP">:</td>
                                <td class="tdmodalDP">{{ \App\Utils::tanggalCantikDariSampai($data->tanggalawal, $data->tanggalakhir) }}</td>
                            </tr>
                            <tr>
                                <td class="tdmodalDP">{{ trans('all.keterangan') }}</td>
                                <td class="tdmodalDP">:</td>
                                <td class="tdmodalDP">{{ $data->keterangan }}</td>
                            </tr>
                            @if(strpos(Session::get('hakakses_perusahaan')->ijintidakmasuk, 'k') !== false || strpos(Session::get('hakakses_perusahaan')->ijintidakmasuk, 'm') !== false)
{{--                            @if(strpos(Session::get('hakakses_perusahaan')->notifikasiijintidakmasuk, 'k') !== false)--}}
                                <tr>
                                    <td colspan="3" style="padding-top:10px">
                                        <textarea class="form-control" placeholder="keterangan konfirmasi" style="resize:none" name="flagketerangan" id="flagketerangan"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="padding-top:10px">
                                        <button id="terima" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'terima')" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check'></i>&nbsp;&nbsp;{{ trans('all.terima') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                        <button id="tolak" type="button" onclick="return submitKonfirmasi('{{ $jenis }}', 'tolak')" class="ladda-button btn btn-danger slide-left"><span class="label2"><i class='fa fa-times'></i>&nbsp;&nbsp;{{ trans('all.tolak') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            @endif
        </table>
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
<script>
    function submitKonfirmasi(jenis,status){
        $('#'+status).attr( 'data-loading', '' );
        $('#terima').attr('disabled', 'disabled');
        $('#tolak').attr('disabled', 'disabled');

        alertConfirm('{{ trans('all.apakahandayakin') }}',
            function(){
                // if(jenis == 'logabsen' || jenis == 'konfirmasiflag'){
                    var keterangan = $('#flagketerangan').val();
                    $.ajax({
                        type: "GET",
                        url: '{{ url('generatecsrftoken') }}',
                        data: '',
                        cache: false,
                        success: function (token) {
                            var dataString = 'jenis=' + jenis + '&status=' + status + '&idkonfirmasiabsen={{ $data->idkonfirmasiabsen }}' + '&keterangan=' + keterangan + '&_token=' + token;
                            $.ajax({
                                type: "POST",
                                url: '{{ url('submitkonfirmasiabsen') }}',
                                data: dataString,
                                cache: false,
                                success: function (html) {
                                    if(html['status'] == 'ok') {
                                        @if($menu == 'y')
                                            //berarti popup dari menu datainduk/absensi/konfirmasiflag manipulasi
                                            window.location.href = '{{ url('datainduk/absensi/konfirmasiflag') }}';
                                        @else
                                            window.location.href = '{{ url('/') }}';
                                        @endif
                                    }else{
                                        alertError(html['msg'],function(){
                                            $('#'+status).removeAttr('data-loading');
                                            $('#terima').removeAttr('disabled');
                                            $('#tolak').removeAttr('disabled');
                                        });
                                    }
                                }
                            });
                        }
                    });
                {{--}else{--}}
                {{--    window.location.href='{{ url('submitkonfirmasiabsen') }}/'+jenis+'/'+status+'/'+{{ $data->idkonfirmasiabsen }};--}}
                {{--}--}}
            },
            function(){
                $('#'+status).removeAttr('data-loading');
                $('#terima').removeAttr('disabled');
                $('#tolak').removeAttr('disabled');
            },
            "{{ trans('all.ya') }}","{{ trans('all.tidak') }}"
        );
    }

    var firstRun = true;
    var lat = '';
    var lon = '';
    var markers = null;
    var map;
    var lokasi;

    @if($lokasi != '')
            lokasi = [
            @foreach($lokasi as $key)
        [{{ $key->lat }}, {{ $key->lon }}, '{{ $key->nama }}'],
        @endforeach
    ];
    @endif
    function initMap() {
        map = new google.maps.Map(document.getElementById('map_konfirmasiabsen'), {
            center: {lat: -4.653079918274038, lng:117.7734375},
            zoom: 4,
            mapTypeId: 'roadmap',
            gestureHandling: 'greedy',
            fullscreenControl: false,
            //styles: styleGoogleMaps
        });

        lat = $('#map_konfirmasiabsen').attr('lat');
        lon = $('#map_konfirmasiabsen').attr('lon');
        if(lat != '' || lon != '') {

            var myLatlng = new google.maps.LatLng(lat, lon);
            markers = new google.maps.Marker({
                position: myLatlng,
                map: map
            });
            map.setCenter(markers.getPosition());
            var bounds = new google.maps.LatLngBounds();
            bounds.extend(myLatlng);
            map.fitBounds(bounds);
        }

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
                    // console.log(lokasi[i][2])
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

    setTimeout(function(){
        if (firstRun==true) {
            firstRun = false;
            initMap();
        }
    },500);
</script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
			async defer></script>