@extends('layouts.master')
@section('title', trans('all.menu_laporan_logabsen'))
@section('content')

  <script src="https://cdn.klokantech.com/maptilerlayer/v1/index.js"></script>
  <script>
  $(function(){
    $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
      $(this).datepicker('hide');
    });
    
    $('.date').mask("00/00/0000", {clearIfNotMatch: true});
  });
  function validasi(){
    freezeButtons('submit','setulang','.');

    var filtermode = $('#filtermode').val();
    if(filtermode == 'berdasarkantanggal'){
        if ($("#berdasarkantanggalinput").prop('checked')) {
        } else {
            $(".tanggalcheck").prop('checked', true);
        }

        var checked = $(".tanggalcheck:checked").length;
        if (checked == 0) {
            alertWarning('{{ trans('all.tanggalkosong') }}',unfreezeButtons('submit','setulang','.'));
            return false;
        } else {
            return true;
        }
    }else if(filtermode == 'jangkauantanggal') {
        var tanggalawal = $("#tanggalawal").val();
        var tanggalakhir = $("#tanggalakhir").val();

        if (tanggalawal == '') {
            alertWarning("{{ trans('all.tanggalkosong') }}",
                function () {
                    unfreezeButtons('submit','setulang','.');
                    setFocus($('#tanggalawal'));
                });
            return false;
        }

        if (tanggalakhir == '') {
            alertWarning("{{ trans('all.tanggalkosong') }}",
                function () {
                    unfreezeButtons('submit','setulang','.');
                    setFocus($('#tanggalakhir'));
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

        {{--if(cekSelisihTanggal(tanggalawal,tanggalakhir) == true){--}}
            {{--alertWarning("{{ trans('all.selisihharimaksimal31') }}",--}}
                {{--function() {--}}
                    {{--aktifkanTombol();--}}
                    {{--setFocus($('#tanggalakhir'));--}}
                {{--});--}}
            {{--return false;--}}
        {{--}--}}
    }
  }

  function filterMode(){
    $('#jangkauantanggal').css('display', 'none');
    $('#berdasarkantanggal').css('display', 'none');
    var filtermode = $('#filtermode').val();
    $('#'+filtermode).css('display', '');
  }

  function pilihTanggal(dari){
      if(dari == 'input') {
          if ($("#berdasarkantanggalinput").prop('checked')) {
              $(".pilihtanggal").css('display', '');
          } else {
              $(".pilihtanggal").css('display', 'none');
          }
      }else{
          if ($("#berdasarkantanggalinput").prop('checked')) {
              $(".pilihtanggal").css('display', 'none');
              $("#berdasarkantanggalinput").prop('checked', false);
          } else {
              $("#berdasarkantanggalinput").prop('checked', true);
              $(".pilihtanggal").css('display', '');
          }
      }
  }

  function pilihBulan(){
      var bulan = $('#bulan').val();
      var tahun = $('#tahun').val();
      $.ajax({
          type: "GET",
          url: '{{ url('totalhari') }}/'+bulan+'/'+tahun,
          data: '',
          cache: false,
          success: function (response) {
              var data = "";
              for(var i = 16; i<=response; i++){
                  data += "<input type='checkbox' class='tanggalcheck' onchange='checkAllAttr(\'tanggalcheck\',\'ceksemuatanggal\')' id='tanggal_"+i+"' name='tanggal[]' value='"+i+"'>&nbsp;" +
                      "<span onclick='spanClick(\'tanggal_"+i+"\')'>"+i+"</span>&nbsp;&nbsp;";
              }
              $('#changeable_pilihtanggal').html('').append(data);
          }
      });
  }

  $(function(){
      setTimeout(filterMode(), 200);
  });
  </script>
  <style type="text/css">
  td{
    padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_laporan_logabsen') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li class="active"><strong>{{ trans('all.menu_laporan_logabsen') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        @if($data == '')
          <form action="{{ url('laporan/logabsen') }}" method="post" onsubmit="return validasi()">
            {{ csrf_field() }}
            <table>
                <tr>
                    <td>{{ trans('all.filtertanggal') }}</td>
                    <td style="float:left">
                        <select id="filtermode" name="filtermode" class="form-control" onchange="return filterMode()">
                            @if(Session::has('laplogabsen_filtermode'))
                                <option value="jangkauantanggal" @if(Session::get('laplogabsen_filtermode') == 'jangkauantanggal') selected @endif>{{ trans('all.jangkauantanggal') }}</option>
                                <option value="berdasarkantanggal" @if(Session::get('laplogabsen_filtermode') == 'berdasarkantanggal') selected @endif>{{ trans('all.berdasarkantanggal') }}</option>
                            @else
                                <option value="jangkauantanggal">{{ trans('all.jangkauantanggal') }}</option>
                                <option value="berdasarkantanggal">{{ trans('all.berdasarkantanggal') }}</option>
                            @endif
                        </select>
                    </td>
                </tr>
            </table>
            <table width="100%" id="jangkauantanggal" style="display: none;">
              <tr>
                <td style="float:left;margin-top:8px">{{ trans('all.tanggal') }}</td>
                <td style="float:left">
                  <input type="text" name="tanggalawal" size="11" id="tanggalawal" @if(Session::has('laplogabsen_tanggalawal')) value="{{ Session::get('laplogabsen_tanggalawal') }}" @else value="{{ $valuetglawalakhir->tanggalawal }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                </td>
                <td style="float:left;margin-top:8px">-</td>
                <td style="float:left">
                  <input type="text" name="tanggalakhir" size="11" id="tanggalakhir" @if(Session::has('laplogabsen_tanggalakhir')) value="{{ Session::get('laplogabsen_tanggalakhir') }}" @else value="{{ $valuetglawalakhir->tanggalakhir }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                </td>
                <td style="float:left">
                    <button type="submit" onclick="return $('#tampilkan1').trigger('click')" class="submit ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button id="tampilkan1" style="display: none" type="submit"></button>
                    <button type="button" id="setulang" onclick="ke('setulang/logabsen')" class="setulang btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                </td>
              </tr>
            </table>
            <table width="100%" id="berdasarkantanggal" style="display: none;">
              <tr>
                  <td style="width: 50px;">{{ trans('all.bulan') }}</td>
                  <td style="float:left">
                      <select name="bulan" id="bulan" class="form-control" onchange="return pilihBulan()">
                          <option value="1" @if($bulanterpilih == 1) selected @endif>{{ trans('all.januari') }}</option>
                          <option value="2" @if($bulanterpilih == 2) selected @endif>{{ trans('all.februari') }}</option>
                          <option value="3" @if($bulanterpilih == 3) selected @endif>{{ trans('all.maret') }}</option>
                          <option value="4" @if($bulanterpilih == 4) selected @endif>{{ trans('all.april') }}</option>
                          <option value="5" @if($bulanterpilih == 5) selected @endif>{{ trans('all.mei') }}</option>
                          <option value="6" @if($bulanterpilih == 6) selected @endif>{{ trans('all.juni') }}</option>
                          <option value="7" @if($bulanterpilih == 7) selected @endif>{{ trans('all.juli') }}</option>
                          <option value="8" @if($bulanterpilih == 8) selected @endif>{{ trans('all.agustus') }}</option>
                          <option value="9" @if($bulanterpilih == 9) selected @endif>{{ trans('all.september') }}</option>
                          <option value="10" @if($bulanterpilih == 10) selected @endif>{{ trans('all.oktober') }}</option>
                          <option value="11" @if($bulanterpilih == 11) selected @endif>{{ trans('all.november') }}</option>
                          <option value="12" @if($bulanterpilih == 12) selected @endif>{{ trans('all.desember') }}</option>
                      </select>
                  </td>
                  <td style="float:left;margin-top:8px">{{ trans('all.tahun') }}</td>
                  <td style="float:left">
                      {{--<select class="form-control" name="tahun" id="tahun" onchange="this.form.submit()">--}}
                      <select class="form-control" name="tahun" id="tahun">
                          <option value="{{ $tahun->tahun1 }}" @if($tahunterpilih == $tahun->tahun1) selected @endif>{{ $tahun->tahun1 }}</option>
                          <option value="{{ $tahun->tahun2 }}" @if($tahunterpilih == $tahun->tahun2) selected @endif>{{ $tahun->tahun2 }}</option>
                          <option value="{{ $tahun->tahun3 }}" @if($tahunterpilih == $tahun->tahun3) selected @endif>{{ $tahun->tahun3 }}</option>
                          <option value="{{ $tahun->tahun4 }}" @if($tahunterpilih == $tahun->tahun4) selected @endif>{{ $tahun->tahun4 }}</option>
                          <option value="{{ $tahun->tahun5 }}" @if($tahunterpilih == $tahun->tahun5) selected @endif>{{ $tahun->tahun5 }}</option>
                      </select>
                  </td>
                  <td style="float: left;">
                      <button onclick="return $('#tampilkan2').trigger('click')" type="submit" class="submit ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                      <button id="tampilkan2" style="display: none" type="submit"></button>
                      <button type="button" id="setulang" onclick="ke('setulang/logabsen')" class="setulang btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                  </td>
              </tr>
              <tr>
                  <td colspan="4">
                      <input type="checkbox" id="berdasarkantanggalinput" onclick="pilihTanggal('input')">
                      <span class="spancheckbox" onclick="pilihTanggal('span')"><b>{{ trans('all.berdasarkantanggal') }}</b></span>
                  </td>
              </tr>
              <tr class="pilihtanggal" style="display:none">
                  <td colspan="4">
                      @for($i=1;$i<=15;$i++)
                          <input type="checkbox" class="tanggalcheck" onchange="checkAllAttr('tanggalcheck','ceksemuatanggal')" id="tanggal_{{ $i }}" name="tanggal[]" value="{{ $i }}"><span onclick="spanClick('tanggal_{{ $i }}')">&nbsp;&nbsp;{{ $i }}</span>&nbsp;&nbsp;
                      @endfor
                  </td>
              </tr>
              <tr class="pilihtanggal" style="display:none">
                  <td colspan="4" id="changeable_pilihtanggal">
                      @for($i=16;$i<=$totalhari;$i++)
                          <input type="checkbox" class="tanggalcheck" onchange="checkAllAttr('tanggalcheck','ceksemuatanggal')" id="tanggal_{{ $i }}" name="tanggal[]" value="{{ $i }}"><span onclick="spanClick('tanggal_{{ $i }}')">&nbsp;&nbsp;{{ $i }}</span>&nbsp;&nbsp;
                      @endfor
                  </td>
              </tr>
            </table>
            @if(isset($mesinterhubung) or isset($mesinbebas))
                <p><b>{{ trans('all.mesin') }}</b></p>
                <div class="col-md-12">
                    <div class="col-md-6">
                        <input type="checkbox" id="semuamesinterhubung" onclick="checkboxallclick('semuamesinterhubung','attrmesinterhubung')">&nbsp;&nbsp;
                        <span class="spancheckbox" onclick="spanallclick('semuamesinterhubung','attrmesinterhubung')"><b>{{ trans('all.terhubung') }}</b></span>
                        @foreach($mesinterhubung as $key)
                            <table>
                                <tr>
                                    <td valign="top" style="width:10px;">
                                        <input type="checkbox" class="attrmesinterhubung" onchange="checkAllAttr('attrmesinterhubung','semuamesinterhubung')" id="mesin_{{ $key->id }}" name="mesin[]" value="{{ $key->id }}">
                                    </td>
                                    <td valign="top">
                                        <span class="spancheckbox" onclick="spanClick('mesin_{{ $key->id }}')">{{ $key->nama }}</span>
                                    </td>
                                </tr>
                            </table>
                        @endforeach
                    </div>
                    <div class="col-md-6">
                        <input type="checkbox" id="semuamesinbebas" onclick="checkboxallclick('semuamesinbebas','attrmesinbebas')">&nbsp;&nbsp;
                        <span class="spancheckbox" onclick="spanallclick('semuamesinbebas','attrmesinbebas')"><b>{{ trans('all.bebas') }}</b></span>
                        @foreach($mesinbebas as $key)
                            <table>
                                <tr>
                                    <td valign="top" style="width:10px;">
                                        <input type="checkbox" class="attrmesinbebas" onchange="checkAllAttr('attrmesinbebas','semuamesinbebas')" id="mesin_{{ $key->id }}" name="mesin[]" value="{{ $key->id }}">
                                    </td>
                                    <td valign="top">
                                        <span class="spancheckbox" onclick="spanClick('mesin_{{ $key->id }}')">{{ $key->nama }}</span>
                                    </td>
                                </tr>
                            </table>
                        @endforeach
                    </div>
                </div>
            @endif
            @if(count($atributs) > 0)
              <p><b>{{ trans('all.atribut') }}</b></p>
            @endif
            @foreach($atributs as $atribut)
              @if(count($atribut->atributnilai) > 0)
                  <div class="col-md-4">
                      <input type="checkbox" id="semuaatribut_{{ $atribut->id }}" onclick="checkboxallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">&nbsp;&nbsp;
                      <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">{{ $atribut->atribut }}</span>
                      <br>
                      @foreach($atribut->atributnilai as $atributnilai)
                        @if(Session::has('laplogabsen_atribut'))
                          {{ $checked = false }}
                          @for($i=0;$i<count(Session::get('laplogabsen_atribut'));$i++)
                            @if($atributnilai->id == Session::get('laplogabsen_atribut')[$i])
                              <span style="display:none">{{ $checked = true }}</span>
                            @endif
                          @endfor
                          <div style="padding-left:15px">
                              <table>
                                <tr>
                                    <td valign="top" style="width:10px;">
                                        <input type="checkbox" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" id="atributnilai_{{ $atributnilai->id }}" @if($checked == true) checked @endif name="atributnilai[]" value="{{ $atributnilai->id }}">
                                    </td>
                                    <td valign="top">
                                        <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                    </td>
                                </tr>
                            </table>
                          </div>
                        @else
                          <div style="padding-left:15px">
                            <table>
                                <tr>
                                    <td valign="top" style="width:10px;">
                                        <input type="checkbox" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" id="atributnilai_{{ $atributnilai->id }}" name="atributnilai[]" value="{{ $atributnilai->id }}">
                                    </td>
                                    <td valign="top">
                                        <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                    </td>
                                </tr>
                            </table>
                          </div>
                        @endif
                      @endforeach
                  </div>
              @endif
            @endforeach
          </form>
          <p></p>
        @else
          <div class="ibox float-e-margins">
            @if($totaldata > 0)
                <button type="button" class="btn btn-primary pull-right" onclick="return ke('{{ url('laporan/logabsen/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
            @endif
            <button onclick="ke('logabsen')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
            <p></p>
            <div class="alert alert-danger">
              <center>
                {{ $keterangan }}
              </center>
            </div>
            <div class="ibox-content">
              <table width=100% class="table datatable table-striped table-condensed table-hover">
                <thead>
                  <tr>
                      <td class="opsi5"><b>{{ trans('all.tanggal') }}</b></td>
                      <td class="nama"><b>{{ trans('all.pegawai') }}</b></td>
                      <td class="opsi1"><b>{{ trans('all.lokasi') }}</b></td>
                      <td class="opsi5"><b>{{ trans('all.pin') }}</b></td>
                      @if($atributvariablepenting_blade != '')
                          @foreach($atributvariablepenting_blade as $key)
                              @if($key != '')
                                  <td class="nama"><b>{{ $key }}</b></td>
                              @endif
                          @endforeach
                      @endif
                      <td class="opsi2"><center><b>{{ trans('all.masukkeluar') }}</b></center></td>
                      <td class="opsi5"><b>{{ trans('all.flag') }}</b></td>
                      <td class="opsi4"><b>{{ trans('all.alasan') }}</b></td>
                      <td class="posi5"><center><b>{{ trans('all.terhitungkerja') }}</b></center></td>
                      <td class="opsi1"><center><b>{{ trans('all.status') }}</b></center></td>
                      <td class="alamat"><b>{{ trans('all.mesin') }}</b></td>
                      @if($atributpenting_blade != '')
                          @foreach($atributpenting_blade as $key)
                              @if($key != '')
                                  <td class="nama"><b>{{ $key }}</b></td>
                              @endif
                          @endforeach
                      @endif
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        @endif
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
});

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: -4.653079918274038, lng:117.7734375},
        zoom: 18,
        mapTypeId: 'roadmap',
        gestureHandling: 'greedy',
        fullscreenControl: false
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

@if($data != '')
$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("laporan/logabsen/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'tanggal', name: 'tanggal',
                render: function (data) {
                    var ukDateTime = data.split(' ');
                    var ukDate = ukDateTime[0].split('-');
                    return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                }
            },
            { data: 'namapegawai', name: 'namapegawai' },
            { data: 'lokasi', name: 'lat' },
            { data: 'pin', name: 'pin' },
            @if($atributvariablepenting_controller != '')
                @foreach($atributvariablepenting_controller as $key)
                    @if($key != '')
                        { data: '{{ $key }}', name: '{{ $key }}' },
                    @endif
                @endforeach
            @endif
            { data: 'masukkeluar', name: 'masukkeluar' },
            { data: 'flag', name: 'flag' },
            { data: 'alasan', name: 'alasan' },
            { data: 'terhitungkerja', name: 'terhitungkerja' },
            { data: 'status', name: 'status' },
            { data: 'mesin', name: 'mesin' },
            @if($atributpenting_controller != '')
                @foreach($atributpenting_controller as $key)
                    @if($key != '')
                        { data: '{{ $key }}', name: '{{ $key }}' },
                    @endif
                @endforeach
            @endif
        ],
        order: [[0, 'desc']]
    });
});
@endif
</script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Session::get('conf_googlemapsapi') }}&libraries=places"
        async defer></script>
@endpush