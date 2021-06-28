@extends('layouts.master')
@section('title', trans('all.pekerjaanuser'))
@section('content')
  <style type="text/css">
  td{
    padding:5px;
  }
  </style>
  <script>
  $(function(){
      $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
          $(this).datepicker('hide');
      });

      $('.date').mask("00/00/0000", {clearIfNotMatch: true});

      setTimeout(filterMode(), 200);
      checkAllAttr('attrpengelola','semuapengelola');
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
  </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.pekerjaanuser') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li class="active"><strong>{{ trans('all.pekerjaanuser') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          @if(!isset($keterangan))
              <form action="{{ url('laporan/pekerjaaninput') }}" method="post" onsubmit="return validasi()">
                  {{ csrf_field() }}
                  <table>
                      <tr>
                          <td width="100px">{{ trans('all.filtertanggal') }}</td>
                          <td style="float:left">
                              <select id="filtermode" name="filtermode" class="form-control" onchange="return filterMode()">
                                  @if(Session::has('lappekerjaaninput_filtermode'))
                                      <option value="jangkauantanggal" @if(Session::get('lappekerjaaninput_filtermode') == 'jangkauantanggal') selected @endif>{{ trans('all.jangkauantanggal') }}</option>
                                      <option value="berdasarkantanggal" @if(Session::get('lappekerjaaninput_filtermode') == 'berdasarkantanggal') selected @endif>{{ trans('all.berdasarkantanggal') }}</option>
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
                          <td width="100px">{{ trans('all.tanggal') }}</td>
                          <td style="float:left">
                              <input type="text" name="tanggalawal" size="11" id="tanggalawal" @if(Session::has('lappekerjaaninput_tanggalawal')) value="{{ Session::get('lappekerjaaninput_tanggalawal') }}" @else value="{{ $valuetglawalakhir->tanggalawal }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                          </td>
                          <td style="float:left;margin-top:8px">-</td>
                          <td style="float:left">
                              <input type="text" name="tanggalakhir" size="11" id="tanggalakhir" @if(Session::has('lappekerjaaninput_tanggalakhir')) value="{{ Session::get('lappekerjaaninput_tanggalakhir') }}" @else value="{{ $valuetglawalakhir->tanggalakhir }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                          </td>
                      </tr>
                  </table>
                  <table width="100%" id="berdasarkantanggal" style="display: none;">
                      <tr>
                          <td width="100px">{{ trans('all.bulan') }}</td>
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
                  <table>
                      <tr>
                          <td width="100px">{{ trans('all.pegawai') }}</td>
                          <td style="min-width:200px">
                              <input type="text" class="form-control" autofocus autocomplete="off" name="pegawai" id="pegawai" maxlength="100">
                              <script type="text/javascript">
                                  $(document).ready(function(){
                                      $("#pegawai").tokenInput("{{ url('tokenpegawai') }}", {
                                          theme: "facebook",
                                          prePopulate: [
                                              @if(Session::has('lappekerjaaninput_pegawai') && Session::get('lappekerjaaninput_pegawai') != '')
                                                  <?php
                                                  $datapegawai = explode(',', Session::get('lappekerjaaninput_pegawai'));
                                                  for($i=0;$i<count($datapegawai);$i++){
                                                      ?>
                                                          {id: {{ $datapegawai[$i] }}, nama: '{{ \App\Utils::getNamaPegawai($datapegawai[$i]) }}'},
                                                      <?php
                                                  }
                                                  ?>
                                              @endif
                                          ]
                                      });
                                  });
                              </script>
                          </td>
                      </tr>
                      <tr>
                          <td>{{ trans('all.pekerjaan') }}</td>
                          <td style="float:left">
                              <select class="form-control" name="pekerjaankategori">
                                @if($datapekerjaankategori != '')
                                  @foreach($datapekerjaankategori as $key)
                                      <option value="{{ $key->id }}">{{ $key->nama }}</option>
                                  @endforeach
                                @endif
                              </select>
                          </td>
                      </tr>
                      <tr>
                          <td colspan="2">
                              <button id="submit" type="submit" class="submit ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                              <button type="button" id="setulang" onclick="ke('setulang/pekerjaaninput')" class="setulang btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                          </td>
                      </tr>
                  </table>
                  {{--@if(count($datapengelola) > 0)--}}
                      {{--<p><b>{{ trans('all.pengelola') }}</b></p>--}}
                      {{--<div class="col-md-12">--}}
                          {{--<div class="col-md-6">--}}
                              {{--<input type="checkbox" id="semuapengelola" onclick="checkboxallclick('semuapengelola','attrpengelola')">&nbsp;&nbsp;--}}
                              {{--<span class="spancheckbox" onclick="spanallclick('semuapengelola','attrpengelola')"><b>{{ trans('all.semuapengelola') }}</b></span><p></p>--}}
                              {{--@foreach($datapengelola as $key)--}}
                                  {{--{{ $checked = false }}--}}
                                  {{--@if(Session::has('lappekerjaaninput_pengelola'))--}}
                                      {{--@for($i=0;$i<count(Session::get('lappekerjaaninput_pengelola'));$i++)--}}
                                          {{--@if($key->iduser == Session::get('lappekerjaaninput_pengelola')[$i])--}}
                                              {{--<span style="display:none">{{ $checked = true }}</span>--}}
                                          {{--@endif--}}
                                      {{--@endfor--}}
                                  {{--@endif--}}
                                  {{--<table>--}}
                                      {{--<tr>--}}
                                          {{--<td valign="top" style="width:10px;">--}}
                                              {{--<input type="checkbox" class="attrpengelola" @if($checked == true) checked @endif onchange="checkAllAttr('attrpengelola','semuapengelola')" id="pengelola_{{ $key->iduser }}" name="pengelola[]" value="{{ $key->iduser }}">--}}
                                          {{--</td>--}}
                                          {{--<td valign="top">--}}
                                              {{--<span class="spancheckbox" onclick="spanClick('pengelola_{{ $key->iduser }}')">{{ \App\Utils::getDataSelected(DB::getPdo(),'nama','user',$key->iduser) }}</span>--}}
                                          {{--</td>--}}
                                      {{--</tr>--}}
                                  {{--</table>--}}
                              {{--@endforeach--}}
                          {{--</div>--}}
                      {{--</div>--}}
                  {{--@endif--}}
              </form>
          @else
              {{--@if($totaldata > 0)--}}
                  <button type="button" class="btn btn-primary pull-right" onclick="return ke('{{ url('laporan/pekerjaaninput/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
              {{--@endif--}}
              <button onclick="ke('pekerjaaninput')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
              <p></p>
              <div class="ibox float-e-margins">
                <div class="alert alert-danger">
                  <center>
                      {{ $keterangan }}
                  </center>
                </div>
                <div class="ibox-content">
                  <table width=100% class="table datatable table-striped table-condensed table-hover">
                    <thead>
                      <tr>
                          <td class="opsi1"><center><b>{{ trans('all.detail') }}</b></center></td>
                          <td class="nama"><b>{{ trans('all.pegawai') }}</b></td>
                          @if(count($datapekerjaan) > 0)
                              @for($i=0;$i<count($datapekerjaan);$i++)
                                  <td class="opsi5"><b>{{ $datapekerjaan[$i]['item'] }}</b></td>
                              @endfor
                          @endif
                      </tr>
                    </thead>
                    <tbody>
                        @if(count($data) > 0)
                            @for($i=0;$i<count($data);$i++)
                                <tr>
                                    <td><center><i style="cursor: pointer" title="{{ trans('all.detail') }}" onclick="return detailPekerjaan({{ $data[$i]['idpegawai'] }})" class="fa fa-info-circle"></i></center></td>
                                    <td>{{ $data[$i]['pegawai'] }}</td>
                                    @if(count($datapekerjaan) > 0)
                                        @for($j=0;$j<count($datapekerjaan);$j++)
                                            <td class="opsi3">{{ $data[$i]['kategoripekerjaan_'.$datapekerjaan[$j]['idpekerjaankategori']].' '.$datapekerjaan[$j]['satuan'] }}</td>
                                        @endfor
                                    @endif
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                  </table>
                </div>
              </div>
          @endif
      </div>
    </div>
  </div>

  <!-- Modal pekerjaan-->
  <a href="" id="showmodalpekerjaan" data-toggle="modal" data-target="#modalpekerjaan" style="display:none"></a>
  <div class="modal modalpekerjaan fade" id="modalpekerjaan" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-lg">

          <!-- Modal content-->
          <div class="modal-content">

          </div>
      </div>
  </div>
  <!-- Modal pekerjaan-->
@stop

@push('scripts')
<script>
window.detailPekerjaan=(function(idpegawai){
    $("#showmodalpekerjaan").attr("href", "");
    $("#showmodalpekerjaan").attr("href", "{{ url('detailpekerjaan') }}/"+idpegawai);
    $('#showmodalpekerjaan').trigger('click');
    return false;
});

$('body').on('hidden.bs.modal', '.modalpekerjaan', function () {
    $(this).removeData('bs.modal');
    $("#" + $(this).attr("id") + " .modal-content").empty();
    $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
});

$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        scrollX: true,
        language: lang_datatable,
        columnDefs: [
            { "orderable": false, "searchable": false, "targets": 0 }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush