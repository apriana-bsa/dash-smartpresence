@extends('layouts.master')
@section('title', trans('all.jadwalshift'))
@section('content')

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
  <script>
  $(function(){
      $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
          $(this).datepicker('hide');
      });

      $('.date').mask("00/00/0000", {clearIfNotMatch: true});

      window.detailjadwalshift=(function(idpegawai,tanggal){
          var idjamkerja = $('#item_'+idpegawai+''+tanggal).attr('idjamkerja');
          $("#tomboljadwalshift").attr("href", "");
          $("#tomboljadwalshift").attr("href", "{!! url('datainduk/absensi/jadwalshiftdetail') !!}/"+idpegawai+"/"+tanggal+'/'+idjamkerja);
          $('#tomboljadwalshift').trigger('click');
          return false;
      });

      $('body').on('hidden.bs.modal', '.modaljadwalshift', function () {
          $(this).removeData('bs.modal');
          $("#" + $(this).attr("id") + " .modal-content").empty();
          $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
      });

      window.modalJadwalPerBulan=(function(idpegawai){

          $("#tomboljadwalperbulan").attr("href", "");
          $("#tomboljadwalperbulan").attr("href", "{!! url('datainduk/absensi/jadwalshiftperbulan') !!}/"+idpegawai);
          $('#tomboljadwalperbulan').trigger('click');
          return false;
      });

      $('body').on('hidden.bs.modal', '.modaljadwalperbulan', function () {
          $(this).removeData('bs.modal');
          $("#" + $(this).attr("id") + " .modal-content").empty();
          $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
      });
  });

  function importFileExcel(){
    $('#fileexcel').click();
    return;
  }

  function submitImportFileExcel(){
    $('#loading-saver-withspinner').css('display', '');
    $('#submitfileexcel').click();
    $('#mainbody').css('overflow','hidden');
    return;
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

  function validasi(){
      var filtermode = $('#filtermode').val();
      var tanggalawal = $('#tanggalawal').val();
      var tanggalakhir = $('#tanggalakhir').val();

      if(filtermode == 'jangkauantanggal'){
          if(tanggalawal == ""){
              alertWarning("{{ trans('all.tanggalkosong') }}",
                  function() {
                      aktifkanTombol();
                      setFocus($('#tanggalawal'));
                  });
              return false;
          }

          if(tanggalakhir == ""){
              alertWarning("{{ trans('all.tanggalkosong') }}",
                  function() {
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

          var jumharimaks = 365;
          if(cekSelisihTanggal(tanggalawal,tanggalakhir,jumharimaks) == true){
              alertWarning("{{ trans('all.selisihharimaksimal') }}"+jumharimaks+" {{ trans('all.hari') }}",
                  function() {
                      aktifkanTombol();
                      setFocus($('#tanggalakhir'));
                  });
              return false;
          }
      }
  }
  </script>
  <style>
  td{
      padding:5px;
  }

  .dataTables_wrapper{
      padding-bottom:0;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.jadwalshift') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.jadwalshift') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-lg-12">
            @if(count($data)==0)
                <form action="{{ url('datainduk/absensi/jadwalshift') }}" method="post" onsubmit="return validasi()">
                    {{ csrf_field() }}
                    <table>
                        <tr>
                            <td>{{ trans('all.filtertanggal') }}</td>
                            <td style="float:left">
                                <select id="filtermode" name="filtermode" class="form-control" onchange="return filterMode()">
                                    @if(Session::has('jadwalshift_filtermode'))
                                        <option value="jangkauantanggal" @if(Session::get('jadwalshift_filtermode') == 'jangkauantanggal') selected @endif>{{ trans('all.jangkauantanggal') }}</option>
                                        <option value="periode" @if(Session::get('jadwalshift_filtermode') == 'periode') selected @endif>{{ trans('all.periode') }}</option>
                                    @else
                                        <option value="jangkauantanggal">{{ trans('all.jangkauantanggal') }}</option>
                                        <option value="periode" selected>{{ trans('all.periode') }}</option>
                                    @endif
                                </select>
                            </td>
                        </tr>
                    </table>
                    <table width="100%" id="jangkauantanggal" style="display: none;">
                        <tr>
                            <td style="float:left;margin-top:8px">{{ trans('all.tanggal') }}</td>
                            <td style="float:left">
                                <input type="text" name="tanggalawal" size="11" id="tanggalawal" @if(Session::has('jadwalshift_tanggalawal')) value="{{ Session::get('jadwalshift_tanggalawal') }}" @else value="{{ $valuetglawalakhir->tanggalawal }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                            </td>
                            <td style="float:left;margin-top:8px">-</td>
                            <td style="float:left">
                                <input type="text" name="tanggalakhir" size="11" id="tanggalakhir" @if(Session::has('jadwalshift_tanggalakhir')) value="{{ Session::get('jadwalshift_tanggalakhir') }}" @else value="{{ $valuetglawalakhir->tanggalakhir }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                            </td>
                            <td style="float:left">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-check-circle"></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</button>&nbsp;&nbsp;
                                <button type="button" onclick="ke('jadwalshift/setulang')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>&nbsp;&nbsp;
                                <button type="button" onclick="importFileExcel()" class="btn btn-primary"><i class="fa fa-upload"></i>&nbsp;&nbsp;{{ trans('all.impordataexcel') }}</button>
                            </td>
                        </tr>
                    </table>
                    <table width="100%" id="periode" style="display: none;">
                      <tr>
                          <td style="float:left;margin-top:8px">{{ trans('all.periode') }}</td>
                          <td style="float:left">
                              <select class="form-control" id="bulan" name="bulan">
                                  <option value="01" @if($bulansekarang == "01") selected @endif>{{ trans('all.januari') }}</option>
                                  <option value="02" @if($bulansekarang == "02") selected @endif>{{ trans('all.februari') }}</option>
                                  <option value="03" @if($bulansekarang == "03") selected @endif>{{ trans('all.maret') }}</option>
                                  <option value="04" @if($bulansekarang == "04") selected @endif>{{ trans('all.april') }}</option>
                                  <option value="05" @if($bulansekarang == "05") selected @endif>{{ trans('all.mei') }}</option>
                                  <option value="06" @if($bulansekarang == "06") selected @endif>{{ trans('all.juni') }}</option>
                                  <option value="07" @if($bulansekarang == "07") selected @endif>{{ trans('all.juli') }}</option>
                                  <option value="08" @if($bulansekarang == "08") selected @endif>{{ trans('all.agustus') }}</option>
                                  <option value="09" @if($bulansekarang == "09") selected @endif>{{ trans('all.september') }}</option>
                                  <option value="10" @if($bulansekarang == "10") selected @endif>{{ trans('all.oktober') }}</option>
                                  <option value="11" @if($bulansekarang == "11") selected @endif>{{ trans('all.november') }}</option>
                                  <option value="12" @if($bulansekarang == "12") selected @endif>{{ trans('all.desember') }}</option>
                              </select>
                          </td>
                          <td style="float:left">
                              <select class="form-control" id="tahun" name="tahun">
                                  <option value="{{ substr($tahundropdown->tahun1, 2) }}" @if($tahunsekarang == substr($tahundropdown->tahun1, 2)) selected @endif>{{ $tahundropdown->tahun1 }}</option>
                                  <option value="{{ substr($tahundropdown->tahun2, 2) }}" @if($tahunsekarang == substr($tahundropdown->tahun2, 2)) selected @endif>{{ $tahundropdown->tahun2 }}</option>
                                  <option value="{{ substr($tahundropdown->tahun3, 2) }}" @if($tahunsekarang == substr($tahundropdown->tahun3, 2)) selected @endif>{{ $tahundropdown->tahun3 }}</option>
                                  <option value="{{ substr($tahundropdown->tahun4, 2) }}" @if($tahunsekarang == substr($tahundropdown->tahun4, 2)) selected @endif>{{ $tahundropdown->tahun4 }}</option>
                                  <option value="{{ substr($tahundropdown->tahun5, 2) }}" @if($tahunsekarang == substr($tahundropdown->tahun5, 2)) selected @endif>{{ $tahundropdown->tahun5 }}</option>
                              </select>
                          </td>
                          <td colspan="3" style="float:left">
                              <button type="submit" class="btn btn-primary"><i class="fa fa-check-circle"></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</button>&nbsp;&nbsp;
                              <button type="button" onclick="ke('jadwalshift/setulang')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>&nbsp;&nbsp;
                              <button type="button" onclick="importFileExcel()" class="btn btn-primary"><i class="fa fa-upload"></i>&nbsp;&nbsp;{{ trans('all.impordataexcel') }}</button>
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
                                    @if(Session::has('jadwalshift_atribut'))
                                        {{ $checked = false }}
                                        @for($i=0;$i<count(Session::get('jadwalshift_atribut'));$i++)
                                            @if($atributnilai->id == Session::get('jadwalshift_atribut')[$i])
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
            @else
                <button class="btn btn-primary" onclick="return ke('jadwalshift')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                <button class="btn btn-primary pull-right" onclick="return ke('{{ url('datainduk/absensi/jadwalshift/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                <button class="btn btn-primary pull-right" style="margin-right:10px" onclick="return ke('{{ url('datainduk/absensi/jadwalshift/templateexcel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.eksportemplate') }}</button><p></p>
                <div class="alert alert-danger">
                  <center>
                      {{ $keterangan }}
                  </center>
                </div>
                <div class="ibox float-e-margins">
                  <div class="ibox-content">
                    @if(count($data) > 0)
                        <table width=100% class="table datatable table-striped table-condensed table-hover">
                          <thead>
                            <tr>
                                @foreach($data[0] as $key => $value)
                                    @if ($key!='id')
                                        <td @if($key != 'nama') style="width:20px" @else class="nama" @endif>
                                            <b>{{$key == 'nama' ? trans('all.pegawai') : ($key == 'pin' ? trans('all.pin') : ($key == "jadwalperbulan" ? " " : substr($key,5))) }}</b>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                          </thead>
                          <tbody>
                            @for($i=0;$i<count($data);$i++)
                                <tr>
                                    @foreach($data[$i] as $key => $value)
                                        @if ($key!='id')
                                            {{-- <td> --}}
                                                {!! $value !!}
                                            {{-- </td> --}}
                                        @endif
                                    @endforeach
                                </tr>
                            @endfor
                          </tbody>
                        </table>
                        {{ trans('all.keterangan') }} :
                        <p>
                            <i class="fa fa-ban" style="color:red"></i> : {{ trans('all.libur') }}<br>
                            <i class="fa fa-minus-circle" style="color:#c0c0c0"></i> : {{ trans('all.tidakadajamkerja') }}<br>
                        </p>
                        <p>
                            @for($i=0;$i<count($jamkerjashift);$i++)
                                {!! $jamkerjashift[$i] !!}
                            @endfor
                        </p>
                        @if(count($dataharilibur) > 0)
                            <p>
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
                            </p>
                        @endif
                    @else
                        <center>{{ trans('all.nodata') }}</center>
                    @endif
                  </div>
                </div>
            @endif
        </div>
    </div>
  </div>

  <!-- form untuk upload file excel -->
  <form id="formimporexcel" method="post" action="{{ url('datainduk/absensi/jadwalshift/importexcel')}}" enctype="multipart/form-data">
    {{ csrf_field()}}
    <input onchange="submitImportFileExcel()" type="file" id="fileexcel" name="fileexcel" style="display:none">
    <button type="submit" id="submitfileexcel" style="display:none">{{ trans('all.simpan') }}</button>
  </form>

  <!-- Modal jadwalshift-->
  <a href="#" id="tomboljadwalshift" data-toggle="modal" data-target="#modaljadwalshift"></a>
  <div class="modal fade modaljadwalshift" id="modaljadwalshift" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-sm">

          <!-- Modal content-->
          <div class="modal-content">

          </div>
      </div>
  </div>
  <!-- Modal jadwalshift-->

  <!-- Modal jadwalperbulan-->
  <a href="#" id="tomboljadwalperbulan" data-toggle="modal" data-target="#modaljadwalperbulan"></a>
  <div class="modal fade modaljadwalperbulan" id="modaljadwalperbulan" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-lg">

          <!-- Modal content-->
          <div class="modal-content">

          </div>
      </div>
  </div>
  <!-- Modal jadwalperbulan-->

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
    $("#" + $(this).attr("id") + " .modal-content").append("<p>Loading...</p>");
});

$(function() {
    $('.datatable').DataTable({
        scrollX: true,
        bStateSave: true,
        language: lang_datatable,
        aoColumnDefs: [
            { 'bSortable': false, 'aTargets': [ 2,
                @if($data != '' && count($data) > 0)
                    <?php $i = -1; ?>
                    @foreach($data[0] as $key => $value)
                        @if (!($key== 'id' or $key== 'nama' or $key== 'pin' or $key== 'jadwalperbulan'))
                            {{$i}},
                        @endif
                        <?php $i++; ?>
                    @endforeach
                @endif
            ] }
        ]
    });
});
</script>
@endpush
