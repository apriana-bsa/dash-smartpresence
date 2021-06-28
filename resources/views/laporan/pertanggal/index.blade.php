@extends('layouts.master')
@section('title', trans('all.rekapitulasi'))
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

    var bulan = $("#bulan").val();
    var tahun = $("#tahun").val();

    if(bulan == ''){
      alertWarning("{{ trans('all.periodekosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#bulan'));
            });
      return false;
    }

    if(tahun == ''){
      alertWarning("{{ trans('all.periodekosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#tahun'));
            });
      return false;
    }
  }

  function filterMode(){
      $('#jangkauantanggal').css('display', 'none');
      $('#periode').css('display', 'none');
      var filtermode = $('#filtermode').val();
      $('#'+filtermode).css('display', '');
  }

  $(function(){
      setTimeout(filterMode(), 200);
  });

  function kembali(){
    window.location.href="pertanggal";
  }

  function setulang(){
    window.location.href="pertanggal/setulang";
  }
  </script>
  <style type="text/css">
  td{
    padding:5px;
  }

  .spanlegend {
    float:left;
    padding:5px;
  }

  span{
    cursor:default;
  }
  
  .dataTables_wrapper{
      padding-bottom:0;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.pertanggal') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li class="active"><strong>{{ trans('all.pertanggal') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        @if($data == "")
          <form action="{{ url('laporan/pertanggal') }}" method="post" onsubmit="return validasi()">
            {{ csrf_field() }}
            <table>
              <tr>
                <td>{{ trans('all.filtertanggal') }}</td>
                <td style="float:left">
                  <select id="filtermode" name="filtermode" class="form-control" onchange="return filterMode()">
                      <option value="jangkauantanggal" @if($dataform['filtermode'] == 'jangkauantanggal') selected @endif>{{ trans('all.jangkauantanggal') }}</option>
                      <option value="periode" @if($dataform['filtermode'] == 'periode') selected @endif>{{ trans('all.periode') }}</option>
                  </select>
                </td>
              </tr>
            </table>
            <table width="100%" id="jangkauantanggal" style="display: none;">
              <tr>
                <td style="float:left;margin-top:8px">{{ trans('all.tanggal') }}</td>
                <td style="float:left">
                  <input type="text" name="tanggalawal" size="11" id="tanggalawal" value="{{ $dataform['tanggalawal'] }}" class="form-control date" placeholder="dd/mm/yyyy">
                </td>
                <td style="float:left;margin-top:8px">-</td>
                <td style="float:left">
                  <input type="text" name="tanggalakhir" size="11" id="tanggalakhir" value="{{ $dataform['tanggalakhir'] }}" class="form-control date" placeholder="dd/mm/yyyy">
                </td>
                <td style="float:left">
                  <button id="submit" onclick="return $('#tampilkan').trigger('click')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                  <button id="tampilkan" style="display: none" type="submit"></button>
                  <button type="button" id="setulang" onclick="ke('setulang/pertanggal')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                </td>
              </tr>
            </table>
            <table width="100%" id="periode" style="display: none;">
              <tr>
                <td style="float:left;margin-top:8px">{{ trans('all.periode') }}</td>
                <td style="float:left">
                  <select class="form-control" id="bulan" name="bulan">
                    <option value="01" @if($dataform['bulanterpilih'] == "01") selected @endif>{{ trans('all.januari') }}</option>
                    <option value="02" @if($dataform['bulanterpilih'] == "02") selected @endif>{{ trans('all.februari') }}</option>
                    <option value="03" @if($dataform['bulanterpilih'] == "03") selected @endif>{{ trans('all.maret') }}</option>
                    <option value="04" @if($dataform['bulanterpilih'] == "04") selected @endif>{{ trans('all.april') }}</option>
                    <option value="05" @if($dataform['bulanterpilih'] == "05") selected @endif>{{ trans('all.mei') }}</option>
                    <option value="06" @if($dataform['bulanterpilih'] == "06") selected @endif>{{ trans('all.juni') }}</option>
                    <option value="07" @if($dataform['bulanterpilih'] == "07") selected @endif>{{ trans('all.juli') }}</option>
                    <option value="08" @if($dataform['bulanterpilih'] == "08") selected @endif>{{ trans('all.agustus') }}</option>
                    <option value="09" @if($dataform['bulanterpilih'] == "09") selected @endif>{{ trans('all.september') }}</option>
                    <option value="10" @if($dataform['bulanterpilih'] == "10") selected @endif>{{ trans('all.oktober') }}</option>
                    <option value="11" @if($dataform['bulanterpilih'] == "11") selected @endif>{{ trans('all.november') }}</option>
                    <option value="12" @if($dataform['bulanterpilih'] == "12") selected @endif>{{ trans('all.desember') }}</option>
                  </select>
                </td>
                <td style="float:left">
                  <select class="form-control" id="tahun" name="tahun">
                    <option value="{{ $dataform['tahun']->tahun1 }}" @if($dataform['tahunterpilih'] == $dataform['tahun']->tahun1) selected @endif>{{ $dataform['tahun']->tahun1 }}</option>
                    <option value="{{ $dataform['tahun']->tahun2 }}" @if($dataform['tahunterpilih'] == $dataform['tahun']->tahun2) selected @endif>{{ $dataform['tahun']->tahun2 }}</option>
                    <option value="{{ $dataform['tahun']->tahun3 }}" @if($dataform['tahunterpilih'] == $dataform['tahun']->tahun3) selected @endif>{{ $dataform['tahun']->tahun3 }}</option>
                    <option value="{{ $dataform['tahun']->tahun4 }}" @if($dataform['tahunterpilih'] == $dataform['tahun']->tahun4) selected @endif>{{ $dataform['tahun']->tahun4 }}</option>
                    <option value="{{ $dataform['tahun']->tahun5 }}" @if($dataform['tahunterpilih'] == $dataform['tahun']->tahun5) selected @endif>{{ $dataform['tahun']->tahun5 }}</option>
                  </select>
                </td>
                <td style="float:left">
                  <button id="submit" onclick="return $('#tampilkan').trigger('click')" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                  <button id="tampilkan" style="display: none" type="submit"></button>
                  <button type="button" id="setulang" onclick="ke('setulang/pertanggal')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                </td>
              </tr>
            </table>
            @if(count($dataform['atribut']) > 0)
              <p><b>{{ trans('all.atribut') }}</b></p>
            @endif
            @foreach($dataform['atribut'] as $atribut)
              @if(count($atribut->atributnilai) > 0)
                  <div class="col-md-4">
                    <input type="checkbox" id="semuaatribut_{{ $atribut->id }}" onclick="checkboxallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">&nbsp;&nbsp;
                    <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">{{ $atribut->atribut }}</span>
                    <br>
                    @foreach($atribut->atributnilai as $atributnilai)
                      @if(Session::has('lappertanggal_atribut'))
                        {{ $checked = false }}
                        @for($i=0;$i<count(Session::get('lappertanggal_atribut'));$i++)
                          @if($atributnilai->id == Session::get('lappertanggal_atribut')[$i])
                            <span style="display:none">{{ $checked = true }}</span>
                          @endif
                        @endfor
                        <div style="padding-left:15px">
                          <table>
                            <tr>
                              <td valign="top" style="width:10px;">
                                <input type="checkbox" id="atributnilai_{{ $atributnilai->id }}" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" @if($checked == true) checked @endif name="atributnilai[]" value="{{ $atributnilai->id }}">
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
                                <input type="checkbox" id="atributnilai_{{ $atributnilai->id }}" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" name="atributnilai[]" value="{{ $atributnilai->id }}">
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
          <br>
        @else
          <div class="ibox float-e-margins">
            <button type="button" class="btn btn-primary pull-right" onclick="return ke('{{ url('laporan/pertanggal/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
            <button onclick="kembali()" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
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
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="opsi1"><b>{{ trans('all.pin') }}</b></td>
                    @for($j=0;$j<count($dataform['headertanggal']);$j++)
                      <td width="10px"><b>{{ $dataform['headertanggal'][$j] }}</b></td>
                    @endfor
                    <td class="opsi1"><b>{{ trans('all.masuk') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.tidakmasuk') }}</b></td>
                    <td class="opsi1"><b>{{ trans('all.terlambat') }}</b></td>
                    <td class="opsi1"><b>{{ trans('all.sakit') }}</b></td>
                    <td class="opsi1"><b>{{ trans('all.ijin') }}</b></td>
                    <td class="opsi1"><b>{{ trans('all.dispensasi') }}</b></td>
                    <td class="opsi1"><b>{{ trans('all.cuti') }}</b></td>
                    <td class="opsi1"><b>{{ trans('all.alpha') }}</b></td>
                  </tr>
                </thead>
                <tbody>
                  @for($i=0;$i<count($data);$i++)
                    <tr>
                      <td>{!! $data[$i]['nama'] !!}</td>
                      <td>{!! $data[$i]['pin'] !!}</td>
                      @for($j=1;$j<=$totalhari;$j++)
                        <td @if($data[$i]['tgl'][$j]['harilibur'] == 'y') style="background-color: #FFB8B8" @endif>
                          <span>
                            @if($data[$i]['tgl'][$j]['terlambat'] == 'y')
                              <i class="fa fa-circle" style="color:#A2A2A2" title="{{ $data[$i]['tgl'][$j]['tooltip'] }}"></i>
                            @elseif($data[$i]['tgl'][$j]['masukkerja'] == 'y')
                              <i class="fa fa-check"  style="color:#39CCCC" title="{{ $data[$i]['tgl'][$j]['tooltip'] }}"></i>
                            @elseif($data[$i]['tgl'][$j]['alasantidakmasukkategori'] != '')
                              <span title="" style="padding:3px;background-color:{{\App\Utils::getColorBackground($data[$i]['tgl'][$j]['alasantidakmasukkategori'])}}';color:'{{\App\Utils::getColorForeground($data[$i]['tgl'][$j]['alasantidakmasukkategori'])}} !important" class="label">{{strtoupper($data[$i]['tgl'][$j]['alasantidakmasukkategori'])}}</span>
                            @else
                              <i class="fa fa-close" style="color:#FF8080"></i>
                            @endif
                          </span>
                        </td>
                      @endfor
                      <td>{{ $data[$i]['jumlahmasuk'] }}</td>
                      <td>{{ $data[$i]['jumlahtidakmasuk'] }}</td>
                      <td>{{ $data[$i]['jumlahterlambat'] }}</td>
                      <td>{{ $data[$i]['jumlahsakit'] }}</td>
                      <td>{{ $data[$i]['jumlahijin'] }}</td>
                      <td>{{ $data[$i]['jumlahdispensasi'] }}</td>
                      <td>{{ $data[$i]['jumlahcuti'] }}</td>
                      <td>{{ $data[$i]['jumlahalpha'] }}</td>
                    </tr>
                  @endfor
                </tbody>
              </table>
              {{ trans('all.keterangan') }} :<br>
              <span class="spanlegend">
                <i class="fa fa-check" style="color:#39CCCC"></i> : {{ trans('all.masuk') }}
              </span>
              <span class="spanlegend">
                <i class="fa fa-circle" style="color:#A2A2A2"></i> : {{ trans('all.terlambat') }}
              </span>
              <span class="spanlegend">
                <i class="fa fa-close" style="color:#FF8080"></i> : {{ trans('all.tidakmasuk') }}
              </span>
              </span>
              <span class="spanlegend">
                <span title="" style="padding:3px;background-color:{{\App\Utils::getColorBackground('s')}}';color:'{{\App\Utils::getColorForeground('s')}} !important" class="label">S</span> : {{ trans('all.sakit') }}
              </span>
              </span>
              <span class="spanlegend">
                <span title="" style="padding:3px;background-color:{{\App\Utils::getColorBackground('i')}}';color:'{{\App\Utils::getColorForeground('i')}} !important" class="label">I</span> : {{ trans('all.ijin') }}
              </span>
              </span>
              <span class="spanlegend">
                <span title="" style="padding:3px;background-color:{{\App\Utils::getColorBackground('d')}}';color:'{{\App\Utils::getColorForeground('d')}} !important" class="label">D</span> : {{ trans('all.dispensasi') }}
              </span>
              </span>
              <span class="spanlegend">
                <span title="" style="padding:3px;background-color:{{\App\Utils::getColorBackground('c')}}';color:'{{\App\Utils::getColorForeground('c')}} !important" class="label">C</span> : {{ trans('all.cuti') }}
              </span>
              </span>
              <span class="spanlegend">
                <span title="" style="padding:3px;background-color:{{\App\Utils::getColorBackground('a')}}';color:'{{\App\Utils::getColorForeground('a')}} !important" class="label">A</span> : {{ trans('all.alpha') }}
              </span>
              @if(count($dataharilibur) > 0)
                <div class="row">
                  <div class="col-md-12 col-lg-12">
                    <table style="margin-top:-5px">
                        <tr>
                            <td width="10px"><div style="width:10px;height:10px;background: rgb(221, 107, 85);"></div></td>
                            <td>{{ trans('all.harilibur') }}</td>
                        </tr>
                        @foreach($dataharilibur as $key)
                            <tr>
                                <td style="padding:0" colspan="2">{{ \App\Utils::tanggalCantikDariSampai($key->tanggalawal,$key->tanggalakhir).' '.$key->keterangan }}</td>
                            </tr>
                        @endforeach
                    </table>
                  </div>
                </div>
              @else
                <br>
              @endif
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

$(function() {
    $('.datatable').DataTable({
        scrollX: true,
        bStateSave: true,
        language: lang_datatable,
        aoColumnDefs: [
          { 'bSortable': false, 'aTargets': [
              @for($i=2;$i<=$totalhari+1;$i++)
                {{ $i }},
              @endfor
            ]
          }
        ]
    });
});
</script>
@endpush