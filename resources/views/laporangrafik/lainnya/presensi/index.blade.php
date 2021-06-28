@extends('layouts.master')
@section('title', trans('all.lainnya'))
@section('content')
  
  <script>
  $(function(){
    $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
      $(this).datepicker('hide');
    });
    
    $('.date').mask("00/00/0000", {clearIfNotMatch: true});
  });

  function validasi(){
      if ($("#berdasarkantanggal").prop('checked')) {
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
  }

  function pilihTanggal(dari){
      if(dari == 'input') {
          if ($("#berdasarkantanggal").prop('checked')) {
              $(".pilihtanggal").css('display', '');
          } else {
              $(".pilihtanggal").css('display', 'none');
          }
      }else{
          if ($("#berdasarkantanggal").prop('checked')) {
              $(".pilihtanggal").css('display', 'none');
              $("#berdasarkantanggal").prop('checked', false);
          } else {
              $("#berdasarkantanggal").prop('checked', true);
              $(".pilihtanggal").css('display', '');
          }
      }
  }

  function pilihBulan(jenis){
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
              $('#changeable_pilihtanggal_' + jenis).html('').append(data);
          }
      });
  }
  </script>
  <style type="text/css">
  td{
    padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.lainnya') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li>{{ trans('all.lainnya') }}</li>
        <li class="active"><strong>{{ trans('all.'.$jenis) }}</strong></li>
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
            <li @if($jenis == 'terlambat') class="active" @endif><a href="{{ url('laporan/lainnya/terlambat') }}">{{ trans('all.terlambat') }}</a></li>
            <li @if($jenis == 'pulangawal') class="active" @endif><a href="{{ url('laporan/lainnya/pulangawal') }}">{{ trans('all.pulangawal') }}</a></li>
            <li @if($jenis == 'belumabsenpulang') class="active" @endif><a href="{{ url('laporan/lainnya/belumabsenpulang') }}">{{ trans('all.belumabsenpulang') }}</a></li>
          </ul>
          @if($keterangan != '')
              <p></p>
              <table width="100%">
                  <tr>
                      <td>
                          <button type="button" onclick="ke('{{ $jenis }}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                      </td>
                      <td class="pull-right">
                          <button type="button" onclick="ke('{{ url('laporan/lainnya').'/'.$jenis }}/excel')" class="btn btn-primary"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                      </td>
                  </tr>
              </table>
              <p></p>
          @endif
          @if($keterangan == '')
              <p></p>
              <form id="form1" method="post" action="" onsubmit="return validasi()">
                {{ csrf_field() }}
                <table width="100%" id="tabelwaktu_{{ $jenis }}">
                  <tr>
                    <td style="width: 50px;">{{ trans('all.bulan') }}</td>
                    <td style="float:left">
                      <select name="bulan" id="bulan" class="form-control" onchange="return pilihBulan('{{ $jenis }}')">
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
                      <button type="submit" class="btn btn-primary">{{ trans('all.tampilkan') }}</button>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="4">
                      <input type="checkbox" id="berdasarkantanggal" onclick="pilihTanggal('input')">
                      <span class="spancheckbox" onclick="pilihTanggal('span')"><b>{{ trans('all.berdasarkantanggal') }}</b></span>
                    </td>
                  </tr>
                  <tr class="pilihtanggal" style="display:none">
                    <td colspan="4">
                      @for($i=1;$i<=15;$i++)
                        <input type="checkbox" class="tanggalcheck" onchange="checkAllAttr('tanggalcheck','ceksemuatanggal')" id="tanggal_{{ $i }}" name="tanggal[]" value="{{ $i }}">
                        <span onclick="spanClick('tanggal_{{ $i }}')">{{ $i }}</span>&nbsp;&nbsp;
                      @endfor
                    </td>
                  </tr>
                  <tr class="pilihtanggal" style="display:none">
                    <td colspan="4" id="changeable_pilihtanggal_{{ $jenis }}">
                        @for($i=16;$i<=$totalhari;$i++)
                            <input type="checkbox" class="tanggalcheck" onchange="checkAllAttr('tanggalcheck','ceksemuatanggal')" id="tanggal_{{ $i }}" name="tanggal[]" value="{{ $i }}">
                            <span onclick="spanClick('tanggal_{{ $i }}')">{{ $i }}</span>&nbsp;&nbsp;
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
                              @if(Session::has('laporanpresensi'.$jenis.'_atribut'))
                                  {{ $checked = false }}
                                  @for($i=0;$i<count(Session::get('laporanpresensi'.$jenis.'_atribut'));$i++)
                                      @if($atributnilai->id == Session::get('laporanpresensi'.$jenis.'_atribut')[$i])
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
          @else
            <div class="alert alert-danger">
              <center>
                {{ $keterangan }}
              </center>
            </div>
            <div class="ibox float-e-margins">
              <div class="ibox-content">
                <table width=100% class="table datatable table-striped table-condensed table-hover">
                  <thead>
                  <tr>
                    <td class="alamat"><b>{{ trans('all.tanggal') }}</b></td>
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    @if($atributvariablepenting_blade != '')
                      @foreach($atributvariablepenting_blade as $key)
                          @if($key != '')
                              <td class="nama"><b>{{ $key }}</b></td>
                          @endif
                      @endforeach
                    @endif
                    <td class="alamat"><b>{{ trans('all.jamkerja') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.jammasuk') }}</b></td>
                    @if($jenis == 'terlambat')
                        <td class="opsi5"><b>{{ trans('all.durasi') }}</b></td>
                    @elseif($jenis == 'pulangawal')
                        <td class="opsi5"><b>{{ trans('all.jampulang') }}</b></td>
                        <td class="opsi5"><b>{{ trans('all.durasi') }}</b></td>
                    @endif
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

  <script>
  @if($keterangan != '')
    $(function() {
      $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
          url: '{!! url("laporan/lainnya/".$jenis."/index-data") !!}',
          type: "POST",
          data: { _token: '{!! csrf_token() !!}', bulan: '{{ $bulanterpilih }}', tahun: '{{ $tahunterpilih }}' }
        },
        columns: [
          { data: 'tanggal', name: 'tanggal' },
          { data: 'nama', name: 'nama' },
          @if($atributvariablepenting_controller != '')
            @foreach($atributvariablepenting_controller as $key)
              @if($key != '')
                { data: '{{ $key }}', name: '{{ $key }}' },
              @endif
            @endforeach
          @endif
          { data: 'jamkerja', name: 'jamkerja' },
          { data: 'jammasuk', name: 'jammasuk' },
          @if($jenis == 'terlambat')
            { data: 'durasi', name: 'durasi' },
          @elseif($jenis == 'pulangawal')
            { data: 'jampulang', name: 'jampulang' },
            { data: 'durasi', name: 'durasi' },
          @endif
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
@stop