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
            <table width="100%">
              <tr>
                <td style="float:left;margin-top:8px">{{ trans('all.periode') }}</td>
                <td style="float:left">
                  <select class="form-control" id="bulan" name="bulan">
                    <option value="01" @if($bulanterpilih == "01") selected @endif>{{ trans('all.januari') }}</option>
                    <option value="02" @if($bulanterpilih == "02") selected @endif>{{ trans('all.februari') }}</option>
                    <option value="03" @if($bulanterpilih == "03") selected @endif>{{ trans('all.maret') }}</option>
                    <option value="04" @if($bulanterpilih == "04") selected @endif>{{ trans('all.april') }}</option>
                    <option value="05" @if($bulanterpilih == "05") selected @endif>{{ trans('all.mei') }}</option>
                    <option value="06" @if($bulanterpilih == "06") selected @endif>{{ trans('all.juni') }}</option>
                    <option value="07" @if($bulanterpilih == "07") selected @endif>{{ trans('all.juli') }}</option>
                    <option value="08" @if($bulanterpilih == "08") selected @endif>{{ trans('all.agustus') }}</option>
                    <option value="09" @if($bulanterpilih == "09") selected @endif>{{ trans('all.september') }}</option>
                    <option value="10" @if($bulanterpilih == "10") selected @endif>{{ trans('all.oktober') }}</option>
                    <option value="11" @if($bulanterpilih == "11") selected @endif>{{ trans('all.november') }}</option>
                    <option value="12" @if($bulanterpilih == "12") selected @endif>{{ trans('all.desember') }}</option>
                  </select>
                </td>
                <td style="float:left">
                  <select class="form-control" id="tahun" name="tahun">
                    <option value="{{ $tahun->tahun1 }}" @if($tahunterpilih == $tahun->tahun1) selected @endif>{{ $tahun->tahun1 }}</option>
                    <option value="{{ $tahun->tahun2 }}" @if($tahunterpilih == $tahun->tahun2) selected @endif>{{ $tahun->tahun2 }}</option>
                    <option value="{{ $tahun->tahun3 }}" @if($tahunterpilih == $tahun->tahun3) selected @endif>{{ $tahun->tahun3 }}</option>
                    <option value="{{ $tahun->tahun4 }}" @if($tahunterpilih == $tahun->tahun4) selected @endif>{{ $tahun->tahun4 }}</option>
                    <option value="{{ $tahun->tahun5 }}" @if($tahunterpilih == $tahun->tahun5) selected @endif>{{ $tahun->tahun5 }}</option>
                  </select>
                </td>
                <td style="float:left">
                  <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                  <button type="button" id="setulang" onclick="ke('setulang/pertanggal')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
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
                    @for($i=1;$i<=$totalhari;$i++)
                      <td width="10px"><b>{{ $i }}</b></td>
                    @endfor
                    <td class="opsi1"><b>{{ trans('all.masuk') }}</b></td>
                    <td class="opsi1"><b>{{ trans('all.terlambat') }}</b></td>
                  </tr>
                </thead>
                <tbody>
                  @for($i=0;$i<$totaldata;$i++)
                    <tr>
                      <td>{!! $data[$i]['nama'] !!}</td>
                      <td>{!! $data[$i]['pin'] !!}</td>
                      @for($j=1;$j<=$totalhari;$j++)
                        @if($data[$i]['tgl'][$j]['harilibur'] == 'y')
                          <td style="background-color: #FFB8B8">
                        @else
                          <td>
                        @endif
                          <span>
                            @if($data[$i]['tgl'][$j]['terlambat'] == 'y')
                              <i class="fa fa-circle" style="color:#A2A2A2" title="{{ $data[$i]['tgl'][$j]['tooltip'] }}"></i>
                            @elseif($data[$i]['tgl'][$j]['masukkerja'] == 'y')
                              <i class="fa fa-check"  style="color:#39CCCC" title="{{ $data[$i]['tgl'][$j]['tooltip'] }}"></i>
                            @else
                              <i class="fa fa-close" style="color:#FF8080"></i>
                            @endif
                          </span>
                        </td>
                      @endfor
                      <td>{{ $data[$i]['jumlahmasuk'] }}</td>
                      <td>{{ $data[$i]['jumlahterlambat'] }}</td>
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
              <br>
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
        aoColumnDefs: [
          { 'bSortable': false, 'aTargets': [
              @for($i=2;$i<=$totalhari;$i++)
                {{ $i }},
              @endfor
            ] }
         ]
    });
});
</script>
@endpush