@extends('layouts.master')
@section('title', trans('all.logabsen'))
@section('content')
  
  <script>
  $(function(){
    $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
      $(this).datepicker('hide');
    });

    $('.date').mask("00/00/0000", {clearIfNotMatch: true});
  });
  function validasi(){
    $('#submit').attr( 'data-loading', '' );
    $('#submit').attr('disabled', 'disabled');
    $('#setulang').attr('disabled', 'disabled');

    var filtermode = $('#filtermode').val();
    if(filtermode == 'berdasarkantanggal'){
        if ($("#berdasarkantanggalinput").prop('checked')) {
        } else {
            $(".tanggalcheck").prop('checked', true);
        }

        var checked = $(".tanggalcheck:checked").length;
        if (checked == 0) {
            alertWarning('{{ trans('all.tanggalkosong') }}');
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

  span{
    cursor:default;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.logabsen') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li class="active"><strong>{{ trans('all.logabsen') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        @if($data == '')
          <form action="{{ url('laporangrafik/logabsen') }}" method="post" onsubmit="return validasi()">
            {{ csrf_field() }}
            <table>
                <tr>
                    <td>{{ trans('all.filtertanggal') }}</td>
                    <td style="float:left">
                        <select id="filtermode" name="filtermode" class="form-control" onchange="return filterMode()">
                            @if(Session::has('laplogabsengrafik_filtermode'))
                                <option value="jangkauantanggal" @if(Session::get('laplogabsengrafik_filtermode') == 'jangkauantanggal') selected @endif>{{ trans('all.jangkauantanggal') }}</option>
                                <option value="berdasarkantanggal" @if(Session::get('laplogabsengrafik_filtermode') == 'berdasarkantanggal') selected @endif>{{ trans('all.berdasarkantanggal') }}</option>
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
                  <input type="text" name="tanggalawal" size="11" id="tanggalawal" @if(Session::has('laplogabsengrafik_tanggalawal')) value="{{ Session::get('laplogabsengrafik_tanggalawal') }}" @else value="{{ $valuetglawalakhir->tanggalawal }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                </td>
                <td style="float:left;margin-top:8px">-</td>
                <td style="float:left">
                  <input type="text" name="tanggalakhir" size="11" id="tanggalakhir" @if(Session::has('laplogabsengrafik_tanggalakhir')) value="{{ Session::get('laplogabsengrafik_tanggalakhir') }}" @else value="{{ $valuetglawalakhir->tanggalakhir }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                </td>
                <td style="float:left">
                    <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button type="button" id="setulang" onclick="ke('setulang/logabsen')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
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
                      <button type="submit" class="btn btn-primary">{{ trans('all.tampilkan') }}</button>&nbsp;&nbsp;
                      <button type="button" id="setulang" onclick="ke('setulang/logabsen')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
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
                        @if(Session::has('laplogabsengrafik_atribut'))
                          {{ $checked = false }}
                          @for($i=0;$i<count(Session::get('laplogabsengrafik_atribut'));$i++)
                            @if($atributnilai->id == Session::get('laplogabsengrafik_atribut')[$i])
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
            <button onclick="ke('logabsen')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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