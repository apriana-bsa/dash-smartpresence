@extends('layouts.master')
@section('title', trans('all.rekapabsen'))
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

    setTimeout(lapFilterMode(), 200);
  });

  function validasi(){
      var filtermode = $('#filtermode').val();
      var jenislaporan = $('#jenislaporan').val();

      if(jenislaporan === 'perpegawai'){
        var pegawai = $("#pegawai").val();
        if (pegawai === '') {
            alertWarning("{{ trans('all.pegawai').' '.trans('all.sa_kosong') }}",
                function () {
                    aktifkanTombol();
                    setFocus($('#token-input-pegawai'));
                });
            return false;
        }
        $('#jenislainnya').val('');
        var jenisperpegawai = $('#jenisperpegawai').val();
        if(jenisperpegawai === 'tipe1'){
            var arrpegawai = pegawai.split(',');
            if(arrpegawai.length > 1){
                alertWarning("Multi Pegawai Hanya Berlaku untuk laporan tipe 2",
                    function () {
                        aktifkanTombol();
                        setFocus($('#token-input-pegawai'));
                    });
                return false;
            }
        }
      }

      if(jenislaporan === 'lainnya'){
          $('#jenisperpegawai').val('');
      }

      if(filtermode === 'berdasarkantanggal'){
          if ($("#berdasarkantanggalinput").prop('checked')) {
          } else {
              $(".tanggalcheck").prop('checked', true);
          }

          var checked = $(".tanggalcheck:checked").length;
          if (checked === 0) {
              alertWarning('{{ trans('all.tanggalkosong') }}');
              return false;
          } else {
              return true;
          }
      }else if(filtermode === 'jangkauantanggal') {
          var tanggalawal = $("#tanggalawal").val();
          var tanggalakhir = $("#tanggalakhir").val();

          if (tanggalawal === '') {
              alertWarning("{{ trans('all.tanggalkosong') }}",
                  function () {
                      aktifkanTombol();
                      setFocus($('#tanggalawal'));
                  });
              return false;
          }

          if (tanggalakhir === '') {
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
      }
  }

  function gantiTab(menu) {
    $('.tabumum').css('display', 'none');
    $('.tabshift').css('display', 'none');
    $('.tabperpegawai').css('display', 'none');
    $('#filteratributjamkerja').css('display', '');
    $('.tab'+menu).css('display', '');
    $('#jenislaporan').val(menu);
    if(menu === 'perpegawai'){
        $('#filteratributjamkerja').css('display', 'none');
    }
  }

  </script>
  <style type="text/css">
  td{
    padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.ekspor') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li class="active"><strong>{{ trans('all.ekspor') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <form action="{{ url('laporan/rekapabsen/excel') }}" method="post" onsubmit="return validasi()">
          {{ csrf_field() }}
          <input type="hidden" id="jenislaporan" value="umum">
          <table>
            <tr>
              <td>{{ trans('all.filtertanggal') }}</td>
              <td style="float:left">
                <select id="filtermode" name="filtermode" class="form-control" onchange="return lapFilterMode()">
                  <option value="jangkauantanggal">{{ trans('all.jangkauantanggal') }}</option>
                  <option value="berdasarkantanggal">{{ trans('all.berdasarkantanggal') }}</option>
                </select>
              </td>
            </tr>
          </table>
          <table width="100%" id="jangkauantanggal" style="display: none;">
            <tr>
              <td style="float:left;margin-top:8px">{{ trans('all.tanggal') }}</td>
              <td style="float:left">
                <input type="text" name="tanggalawal" size="11" id="tanggalawal" class="form-control date" value="{{ $valuetglawalakhir->tanggalawal }}" placeholder="dd/mm/yyyy">
              </td>
              <td style="float:left;margin-top:8px">-</td>
              <td style="float:left">
                <input type="text" name="tanggalakhir" size="11" id="tanggalakhir" class="form-control date" value="{{ $valuetglawalakhir->tanggalakhir }}" placeholder="dd/mm/yyyy">
              </td>
            </tr>
          </table>
          <table width="100%" id="berdasarkantanggal" style="display: none;">
            <tr>
              <td style="width: 50px;">{{ trans('all.bulan') }}</td>
              <td style="float:left">
                <select name="bulan" id="bulan" class="form-control" onchange="return lapPilihBulan()">
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
                <input type="checkbox" id="berdasarkantanggalinput" onclick="lapPilihTanggal('input')">
                <span class="spancheckbox" onclick="lapPilihTanggal('span')"><b>{{ trans('all.berdasarkantanggal') }}</b></span>
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
          <div class="col-lg-12" style="margin:10px -10px;">
            <ul class="nav nav-tabs" style="padding:-10px;padding-bottom:0">
              <li class="active"><a data-toggle="tab" onclick="gantiTab('umum')" href="#tab-1">{{ trans('all.umum') }}</a></li>
              <li><a data-toggle="tab" onclick="gantiTab('shift')" href="#tab-2">{{ trans('all.shift') }}</a></li>
              <li><a data-toggle="tab" onclick="gantiTab('perpegawai')" href="#tab-3">{{ trans('all.perpegawai') }}</a></li>
              <li><a data-toggle="tab" onclick="gantiTab('lainnya')" href="#tab-4">{{ trans('all.lainnya') }}</a></li>
            </ul>
          </div>
          <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
              <table>
                <tr>
                  <td style="float:left">
                    <button type="submit" name="tombol" value="rekap" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.rekapitulasi') }}</button>
                  </td>
                </tr>
                <tr>
                  <td style="float:left">
                    <button type="submit" name="tombol" value="rekapperkategori" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.rekapitulasicatatantidakmasuk') }}</button>
                  </td>
                </tr>
                <tr>
                  <td style="float:left">
                    <button type="submit" name="tombol" value="rekapgabungan" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.rekapitulasigabungan') }}</button>
                  </td>
                </tr>
                <tr class="tr_pertanggal" style="display: none">
                  <td style="float:left">
                    <button type="submit" name="tombol" value="rekapbulanan" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.rekapitulasibulanan') }}</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" name="tombol" value="rekaptext" class="btn btn-primary"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;{{ trans('all.riwayatkehadiran') }}</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" name="tombol" value="ijintidakmasuk" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.ijintidakmasuk') }}</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" name="tombol" value="attlog" class="btn btn-primary"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;attlog</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" name="tombol" value="sepertiwa" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{trans('all.laporanisian')}}</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" name="tombol" value="sunfish" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{trans('all.laporanzip')}}</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" name="tombol" value="timesheetsudahterisi" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.timesheetsudahterisi') }}</button>
                  </td>
                </tr>
                {{-- khusus virtus id=12035 --}}
                @if(Session::get('conf_webperusahaan') == 12035)
                    <tr>
                      <td>
                        <button type="submit" name="tombol" value="timesheetsudahterisijamkerja" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.timesheetsudahterisijamkerja') }}</button>
                      </td>
                    </tr>
                @endif
                <tr>
                  <td>
                    <button type="submit" name="tombol" value="internalmemopotongan" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.internalmemopotongan') }}</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" name="tombol" value="summaryrekap" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.summaryrekap') }}</button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" name="tombol" value="peratribut" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.laporanperatribut') }}</button>
                  </td>
                </tr>
                {{-- <tr>
                  <td>
                    <button type="submit" name="tombol" value="internalmemoinsentif" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;Internal Memo Insentif</button>
                  </td>
                </tr> --}}
                {{--<tr>--}}
                  {{--<td>--}}
                    {{--<button type="submit" name="tombol" value="pekerjaan" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.pekerjaan') }}</button>--}}
                  {{--</td>--}}
                {{--</tr>--}}
              </table>
            </div>
            <div id="tab-2" class="tab-pane">
              <table>
                <tr>
                  <td style="float:left">
                    <button type="submit" name="tombol" value="rekapshift" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.rekapitulasi') }}</button>
                  </td>
                </tr>
              </table>
            </div>
            <div id="tab-3" class="tab-pane">
              <table>
                <tr>
                  <td>{{ trans('all.pegawai') }}</td>
                  <td style="float:left;min-width:200px">
                    <input type="text" class="form-control" autofocus autocomplete="off" name="pegawai" id="pegawai" maxlength="100">
                    <script type="text/javascript">
                    $(document).ready(function(){
                      $("#pegawai").tokenInput("{{ url('tokenpegawai') }}", {
                        theme: "facebook",
                        // tokenLimit: 1
                      });
                    });
                    </script>
                  </td>
                </tr>
                <tr>
                  <td>{{trans('all.laporan')}}</td>
                  <td style="float:left">
                      <select class="form-control" id="jenisperpegawai" name="jenisperpegawai">
                          <option value="tipe1">Tipe 1</option>
                          <option value="tipe2">Tipe 2</option>
                      </select>
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                    <button type="submit" name="tombol" onclick="cekJenisPerpegawai()" value="excel" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.excel') }}</button>&nbsp;&nbsp;
                    <button type="submit" name="tombol" value="pdf" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.pdf') }}</button>
                  </td>
                </tr>
              </table>
            </div>
            <div id="tab-4" class="tab-pane"> {{-- request rsfatmawati--}}
                <table>
                    <tr>
                        <td>{{trans('all.laporan')}}</td>
                        <td>
                            <select class="form-control" id="jenislainnya" name="jenislainnya">
                                <option value="tipe1">Tipe 1</option>
                                <option value="tipe2">Tipe 2</option> {{-- seperti perpegawai pdf tapi langsung banyak pegawai --}}
                                <option value="tipe3">Tipe 3</option> {{-- seperti perpegawai pdf tapi langsung banyak pegawai --}}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>&nbsp;&nbsp;
                        </td>
                    </tr>
                </table>
            </div>
          </div>
          <div id="filteratributjamkerja">
            @if(count($atributs) > 0)
              <div class="col-md-6" style="padding-left:0;margin-top:5px;">
                <div class="col-md-12"><p><b>{{ trans('all.atribut') }}</b></p></div>
                @foreach($atributs as $atribut)
                  @if(count($atribut->atributnilai) > 0)
                    <div class="col-md-4">
                      <input type="checkbox" id="semuaatribut_{{ $atribut->id }}" onclick="checkboxallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">&nbsp;&nbsp;
                      <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">{{ $atribut->atribut }}</span>
                      <br>
                      @foreach($atribut->atributnilai as $atributnilai)
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
                      @endforeach
                    </div>
                  @endif
                @endforeach
              </div>
            @endif
            @if(count($jamkerjafull) > 0 || count($jamkerjashift) > 0)
              <div class="col-md-6" style="padding-left:0;margin-top:5px;">
                <div class="col-md-12"><p><b>{{ trans('all.jamkerja') }}</b></p></div>
                <div class="col-md-12 tabumum">
                  <div class="col-md-12"><b>{{trans('all.full')}}</b></div>
                  @foreach($jamkerjafull as $key)
                    <div class="col-md-12">
                      <input type="checkbox" id="semuajamkerjafull_{{ $key->id }}" value="{{ $key->id }}" name="jamkerjafull[]" onclick="checkboxallclick('semuajamkerjafull_{{ $key->id }}','attrjkf_{{ $key->id }}')">&nbsp;&nbsp;
                      <span class="spancheckbox" onclick="spanallclick('semuajamkerjafull_{{ $key->id }}','attrjkf_{{ $key->id }}')">{{ $key->nama }}</span>
                    </div>
                  @endforeach
                  @if(Session::get('conf_webperusahaan') == 12035) {{--khusus perusahaan virtus--}}
                        <div class="col-md-12" style="margin-top:10px"><b>{{trans('all.shift')}}</b></div>
{{--                      <div class="col-md-12" style="margin-top:20px"><b>{{trans('all.shift')}}</b></div>--}}
{{--                          @foreach($jamkerjashift as $key)--}}
{{--                              <div class="col-md-6">--}}
{{--                                  <input type="checkbox" name="jamkerjashift_umum[]" id="semuajamkerja_{{ $key->id }}" value="{{ $key->id }}" onclick="checkboxallclick('semuajamkerja_{{ $key->id }}','attrjks_{{ $key->id }}')">&nbsp;&nbsp;--}}
{{--                                  <span class="spancheckbox" onclick="spanallclick('semuajamkerja_{{ $key->id }}','attrjks_{{ $key->id }}')">{{ $key->nama }}</span>--}}
{{--                              </div>--}}
{{--                          @endforeach--}}
{{--                      </div>--}}
                        @foreach($jamkerjashift as $key)
                            <div class="col-md-6">
                                <input type="checkbox" name="jamkerjashift_umum[]" id="semuajamkerja_{{ $key->id }}" value="{{ $key->id }}" onclick="checkboxallclick('semuajamkerja_{{ $key->id }}','attrjks_{{ $key->id }}')">&nbsp;&nbsp;
                                <span class="spancheckbox" onclick="spanallclick('semuajamkerja_{{ $key->id }}','attrjks_{{ $key->id }}')">{{ $key->nama }}</span>
                            </div>
                        @endforeach
                  @endif
                </div>
                <div class="col-md-8 tabshift" style="display: none;">
                  @foreach($jamkerjashift as $key)
                    <div class="col-md-6">
                      <input type="checkbox" id="semuajamkerja_{{ $key->id }}" value="{{ $key->id }}" onclick="checkboxallclick('semuajamkerja_{{ $key->id }}','attrjks_{{ $key->id }}')">&nbsp;&nbsp;
                      <span class="spancheckbox" onclick="spanallclick('semuajamkerja_{{ $key->id }}','attrjks_{{ $key->id }}')">{{ $key->nama }}</span>
                      <br>
                      @if($jamkerjashift != '')
                        @foreach($key->jamkerjashift as $keyshift)
                          <div style="padding-left:15px">
                            <table>
                              <tr>
                                <td valign="top" style="width:10px;">
                                  <input type="checkbox" class="attrjks_{{ $key->id }}" onchange="checkAllAttr('attrjks_{{ $key->id }}','semuajamkerja_{{ $key->id }}')" id="jamkerjashift_{{ $keyshift->id }}" name="jamkerjashift[]" value="{{ $keyshift->id }}">
                                </td>
                                <td valign="top">
                                  <span class="spancheckbox" onclick="spanClick('jamkerjashift_{{ $keyshift->id }}')">{{ $keyshift->namashift }}</span>
                                </td>
                              </tr>
                            </table>
                          </div>
                        @endforeach
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>

@stop
