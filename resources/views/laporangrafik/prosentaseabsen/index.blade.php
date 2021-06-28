@extends('layouts.master')
@section('title', trans('all.prosentaseabsen'))
@section('content')
  
  <script>
  $(function(){
    $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
      $(this).datepicker('hide');
    });

    $('.date').mask("00/00/0000", {clearIfNotMatch: true});
  });
  function validasi(){
//    $('#submit').attr( 'data-loading', '' );
//    $('#submit').attr('disabled', 'disabled');
//    $('#setulang').attr('disabled', 'disabled');

    var jenis = $('#jenis').val();
    if(jenis == 'pertanggal'){
        var tanggalawal = $("#tanggalawal").val();
        var tanggalakhir = $("#tanggalakhir").val();

        if (tanggalawal == '') {
            alertWarning("{{ trans('all.tanggalkosong') }}",
                function () {
                    aktifkanTombol();
                    setFocus($('#tanggalawal'));
                });
            return false;
        }

        if (tanggalakhir == '') {
            alertWarning("{{ trans('all.tanggalkosong') }}",
                function () {
                    aktifkanTombol();
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

        if(cekSelisihTanggal(tanggalawal,tanggalakhir) == true){
            alertWarning("{{ trans('all.selisihharimaksimal31') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#tanggalakhir'));
                });
            return false;
        }
    }
  }

  function jenisLaporan(){
      $('.pertanggal').css('display', 'none');
      $('.perbulan').css('display', 'none');
      var jenis = $('#jenis').val();
      $('.'+jenis).css('display', '');
  }

  $(function(){
      setTimeout(jenisLaporan(), 200);
  });
  </script>
  <style type="text/css">
  td{
    padding:5px;
  }

  span{
    cursor:default;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.lainnya') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li>{{ trans('all.lainnya') }}</li>
        <li class="active"><strong>{{ trans('all.prosentaseabsen') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">
    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
          <li><a href="{{ url('laporan/lainnya/harilibur') }}">{{ trans('all.harilibur') }}</a></li>
          <li><a href="{{ url('laporan/lainnya/terlambat') }}">{{ trans('all.terlambat') }}</a></li>
          <li><a href="{{ url('laporan/lainnya/pulangawal') }}">{{ trans('all.pulangawal') }}</a></li>
          <li><a href="{{ url('laporan/lainnya/belumabsenmasuk') }}">{{ trans('all.belumabsenmasuk') }}</a></li>
          <li><a href="{{ url('laporan/lainnya/belumabsenpulang') }}">{{ trans('all.belumabsenpulang') }}</a></li>
          <li><a href="{{ url('laporan/lainnya/masuktanpajadwal') }}">{{ trans('all.masuktanpajadwal') }}</a></li>
          <li class="active"><a href="{{ url('laporan/lainnya/prosentaseabsen') }}">{{ trans('all.prosentaseabsen') }}</a></li>
          <li><a href="{{ url('laporan/lainnya/pegawai') }}">{{ trans('all.pegawai') }}</a></li>
        </ul>
        <br>
        @if($data == '')
          <form action="{{ url('laporangrafik/prosentaseabsen') }}" method="post" onsubmit="return validasi()">
            {{ csrf_field() }}
            <table>
                <tr>
                  <td>{{ trans('all.jenis') }}</td>
                  <td style="float:left">
                      <select id="jenis" name="jenis" class="form-control" onchange="return jenisLaporan()">
                          <option value="pertanggal" @if(Session::get('lapprosentaseabsengrafik_jenis') == 'pertanggal') selected @endif>{{ trans('all.pertanggal') }}</option>
                          <option value="perbulan" @if(Session::get('lapprosentaseabsengrafik_jenis') == 'perbulan') selected @endif>{{ trans('all.perbulan') }}</option>
                      </select>
                  </td>
                </tr>
            </table>
            <table id="perbulan" width="100%">
              <tr>
                  <td class="pertanggal" style="float:left;margin-top:8px">{{ trans('all.tanggal') }}</td>
                  <td class="pertanggal" style="float:left">
                      <input type="text" name="tanggalawal" size="11" id="tanggalawal" class="form-control date" value="{{ Session::get('lapprosentaseabsengrafik_tanggalawal') }}" placeholder="dd/mm/yyyy">
                  </td>
                  <td class="pertanggal" style="float:left;margin-top:8px">-</td>
                  <td class="pertanggal" style="float:left">
                      <input type="text" name="tanggalakhir" size="11" id="tanggalakhir" class="form-control date" value="{{ Session::get('lapprosentaseabsengrafik_tanggalakhir') }}" placeholder="dd/mm/yyyy">
                  </td>
                  <td class="perbulan" style="width: 50px;">{{ trans('all.bulan') }}</td>
                  <td class="perbulan" style="float:left">
                      <select name="bulan1" id="bulan1" class="form-control">
                          <option value="1" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 1) selected @endif>{{ trans('all.januari') }}</option>
                          <option value="2" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 2) selected @endif>{{ trans('all.februari') }}</option>
                          <option value="3" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 3) selected @endif>{{ trans('all.maret') }}</option>
                          <option value="4" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 4) selected @endif>{{ trans('all.april') }}</option>
                          <option value="5" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 5) selected @endif>{{ trans('all.mei') }}</option>
                          <option value="6" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 6) selected @endif>{{ trans('all.juni') }}</option>
                          <option value="7" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 7) selected @endif>{{ trans('all.juli') }}</option>
                          <option value="8" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 8) selected @endif>{{ trans('all.agustus') }}</option>
                          <option value="9" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 9) selected @endif>{{ trans('all.september') }}</option>
                          <option value="10" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 10) selected @endif>{{ trans('all.oktober') }}</option>
                          <option value="11" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 11) selected @endif>{{ trans('all.november') }}</option>
                          <option value="12" @if(Session::get('lapprosentaseabsengrafik_bulan1') == 12) selected @endif>{{ trans('all.desember') }}</option>
                      </select>
                  </td>
                  <td class="perbulan" style="float:left;margin-top:8px">{{ trans('all.tahun') }}</td>
                  <td class="perbulan" style="float:left">
                      {{--<select class="form-control" name="tahun" id="tahun" onchange="this.form.submit()">--}}
                      <select class="form-control" name="tahun1" id="tahun1">
                          <option value="{{ $tahun->tahun1 }}" @if(Session::get('lapprosentaseabsengrafik_tahun1') == $tahun->tahun1) selected @endif>{{ $tahun->tahun1 }}</option>
                          <option value="{{ $tahun->tahun2 }}" @if(Session::get('lapprosentaseabsengrafik_tahun1') == $tahun->tahun2) selected @endif>{{ $tahun->tahun2 }}</option>
                          <option value="{{ $tahun->tahun3 }}" @if(Session::get('lapprosentaseabsengrafik_tahun1') == $tahun->tahun3) selected @endif>{{ $tahun->tahun3 }}</option>
                          <option value="{{ $tahun->tahun4 }}" @if(Session::get('lapprosentaseabsengrafik_tahun1') == $tahun->tahun4) selected @endif>{{ $tahun->tahun4 }}</option>
                          <option value="{{ $tahun->tahun5 }}" @if(Session::get('lapprosentaseabsengrafik_tahun1') == $tahun->tahun5) selected @endif>{{ $tahun->tahun5 }}</option>
                      </select>
                  </td>
                  <td class="perbulan" style="float:left;margin-top:8px">-</td>
                  <td class="perbulan" style="float:left;margin-top:8px;width: 50px;">{{ trans('all.bulan') }}</td>
                  <td class="perbulan" style="float:left">
                      <select name="bulan2" id="bulan2" class="form-control">
                          <option value="1" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 1) selected @endif>{{ trans('all.januari') }}</option>
                          <option value="2" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 2) selected @endif>{{ trans('all.februari') }}</option>
                          <option value="3" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 3) selected @endif>{{ trans('all.maret') }}</option>
                          <option value="4" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 4) selected @endif>{{ trans('all.april') }}</option>
                          <option value="5" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 5) selected @endif>{{ trans('all.mei') }}</option>
                          <option value="6" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 6) selected @endif>{{ trans('all.juni') }}</option>
                          <option value="7" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 7) selected @endif>{{ trans('all.juli') }}</option>
                          <option value="8" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 8) selected @endif>{{ trans('all.agustus') }}</option>
                          <option value="9" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 9) selected @endif>{{ trans('all.september') }}</option>
                          <option value="10" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 10) selected @endif>{{ trans('all.oktober') }}</option>
                          <option value="11" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 11) selected @endif>{{ trans('all.november') }}</option>
                          <option value="12" @if(Session::get('lapprosentaseabsengrafik_bulan2') == 12) selected @endif>{{ trans('all.desember') }}</option>
                      </select>
                  </td>
                  <td class="perbulan" style="float:left;margin-top:8px">{{ trans('all.tahun') }}</td>
                  <td class="perbulan" style="float:left">
                      {{--<select class="form-control" name="tahun" id="tahun" onchange="this.form.submit()">--}}
                      <select class="form-control" name="tahun2" id="tahun2">
                          <option value="{{ $tahun->tahun1 }}" @if(Session::get('lapprosentaseabsengrafik_tahun2') == $tahun->tahun1) selected @endif>{{ $tahun->tahun1 }}</option>
                          <option value="{{ $tahun->tahun2 }}" @if(Session::get('lapprosentaseabsengrafik_tahun2') == $tahun->tahun2) selected @endif>{{ $tahun->tahun2 }}</option>
                          <option value="{{ $tahun->tahun3 }}" @if(Session::get('lapprosentaseabsengrafik_tahun2') == $tahun->tahun3) selected @endif>{{ $tahun->tahun3 }}</option>
                          <option value="{{ $tahun->tahun4 }}" @if(Session::get('lapprosentaseabsengrafik_tahun2') == $tahun->tahun4) selected @endif>{{ $tahun->tahun4 }}</option>
                          <option value="{{ $tahun->tahun5 }}" @if(Session::get('lapprosentaseabsengrafik_tahun2') == $tahun->tahun5) selected @endif>{{ $tahun->tahun5 }}</option>
                      </select>
                  </td>
                  <td style="float: left;">
                      <button id="submit" type="submit" class="submit ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-download'></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                      {{--<button type="button" id="setulang" onclick="ke('{{ url('laporan/setulang/prosentaseabsen') }}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>--}}
                  </td>
              </tr>
            </table>
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
                        @if(Session::has('lapprosentaseabsengrafik_atribut'))
                          {{ $checked = false }}
                          @for($i=0;$i<count(Session::get('lapprosentaseabsengrafik_atribut'));$i++)
                            @if($atributnilai->id == Session::get('lapprosentaseabsengrafik_atribut')[$i])
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
            {{--@if($totaldata > 0)--}}
                <button type="button" class="btn btn-primary pull-right" onclick="return ke('{{ url('laporan/baru') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
            {{--@endif--}}
            <button onclick="ke('prosentaseabsen')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
            <p></p>
            <div class="alert alert-danger">
              <center>
                {{ $keterangan }}
              </center>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div>
                            <canvas id="barChart" height="140"></canvas>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
@stop

@push('scripts')
<!-- Flot -->
<script src="{{ asset('lib/js/plugins/flot/jquery.flot.js') }}"></script>
<script src="{{ asset('lib/js/plugins/flot/jquery.flot.tooltip.min.js') }}"></script>
<script src="{{ asset('lib/js/plugins/flot/jquery.flot.spline.js') }}"></script>
<script src="{{ asset('lib/js/plugins/flot/jquery.flot.resize.js') }}"></script>
<script src="{{ asset('lib/js/plugins/flot/jquery.flot.pie.js') }}"></script>
<script src="{{ asset('lib/js/plugins/flot/jquery.flot.symbol.js') }}"></script>
<script src="{{ asset('lib/js/plugins/flot/jquery.flot.time.js') }}"></script>
<script src="{{ asset('lib/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('lib/js/plugins/chartJs/Chart.min.js') }}"></script>
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

$(document).ready(function() {

//    var data1 = [65, 59, 80, 81, 56, 55, 40];
//    var data2 = [28, 48, 40, 19, 86, 27, 90];
//    var data3 = [65, 59, 80, 81, 56, 55, 40];

    var barData = {
        labels: ["{{ trans('all.totalpegawai') }}", "{{ trans('all.totalpresensi') }}", "{{ trans('all.tidakterlambat') }}", "{{ trans('all.terlambat') }}", "{{ trans('all.belumabsen') }}"],
        datasets: [
            {{--@foreach($datagrafik as $key)--}}
                {{--{--}}
                    {{--label: "{{ $key->nama }}",--}}
                    {{--backgroundColor: 'rgba(220, 220, 220, 0.5)',--}}
                    {{--pointBorderColor: "#fff000",--}}
                    {{--data: [{{ $key->jumlahpekerja }}, {{ $key->totaltransaksi }}, {{ $key->tidakterlambat }}, {{ $key->terlambat }}, {{ $key->tidakabsen }}]--}}
                {{--},--}}
            {{--@endforeach--}}
            @if(isset($datagrafik))
            @for($i = 0;$i<count($datagrafik); $i++)
                {
                    label: "{{ $datagrafik[$i]->nama }}",
//                    backgroundColor: 'rgba(220, 220, 220, 0.5)',
                    backgroundColor: getcolor("{{ $datagrafik[$i]->nama }}"),
                    pointBorderColor: getcolor("{{ $datagrafik[$i]->nama }}"),
//                    pointBorderColor: "#fff000",
                    data: [{{ $datagrafik[$i]->jumlahpekerja }}, {{ $datagrafik[$i]->totaltransaksi }}, {{ $datagrafik[$i]->tidakterlambat }}, {{ $datagrafik[$i]->terlambat }}, {{ $datagrafik[$i]->tidakabsen }}]
                },
            @endfor
            @endif
        ],
//        datasets: [
//            {
//                label: "Data 1",
//                backgroundColor: 'rgba(220, 220, 220, 0.5)',
//                pointBorderColor: "#fff000",
//                data: data1
//            },
//            {
//                label: "Data 2",
//                backgroundColor: 'rgba(26,179,148,0.5)',
//                borderColor: "rgba(26,179,148,0.7)",
//                pointBackgroundColor: "rgba(26,179,148,1)",
//                pointBorderColor: "#fff",
//                data:data2
//            },
//            {
//                label: "Data 3",
//                backgroundColor: 'rgba(26,179,148,0.5)',
//                borderColor: "rgba(26,179,148,0.7)",
//                pointBackgroundColor: "rgba(26,179,148,1)",
//                pointBorderColor: "#fff",
//                data:data3
//            }
//        ]
    };

    var barOptions = {
        responsive: true
    };

    var ctx2 = document.getElementById("barChart").getContext("2d");
    new Chart(ctx2, {type: 'bar', data: barData, options: barOptions});
//    new Chart(ctx2).Bar(barData, barOptions);
});
</script>
@endpush