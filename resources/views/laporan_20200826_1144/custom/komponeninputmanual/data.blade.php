@extends('layouts.master')
@section('title', trans('all.laporankomponeninputmanual'))
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
        $('.money').mask("#.##0.##0.##0.##0", {reverse: true});
    })

    function back(){
      $('#kembali_submit').trigger('click');
    }

    const formatter = new Intl.NumberFormat('de-DE');

    function validasi(){
      $('#submit').attr( 'data-loading', '' );
      $('#submit').attr('disabled', 'disabled');
      $('.kembali').attr('disabled', 'disabled');
      $('#ekspor').attr('disabled', 'disabled');
      $('#setsemuanominal').attr('disabled', 'disabled');
      var lanjut;
      $('.nominal').each(function() {
        var nominal = $(this).val().replace(/\./g, "");
        var namapegawai = $(this).attr('namapegawai');
        var batas_bawah = $(this).attr('batas_bawah');
        var batas_atas = $(this).attr('batas_atas');
        if(nominal != 0){
          lanjut = parseInt(nominal) >= parseInt(batas_bawah) && parseInt(nominal) <= parseInt(batas_atas);
          if(!(parseInt(nominal) >= parseInt(batas_bawah) && parseInt(nominal) <= parseInt(batas_atas))){
            alertWarning("{{ trans('all.nominaluntuk') }} <b>"+namapegawai+"</b> {{ trans('all.hanyabolehantara') }} <b>"+formatter.format(batas_bawah)+"</b> {{ trans('all.dan') }} <b>"+formatter.format(batas_atas)+"</b>",
              function() {
                  aktifkanTombol();
                  $('.kembali').removeAttr('disabled');
                  $('#ekspor').removeAttr('disabled');
                  $('#setsemuanominal').removeAttr('disabled');
              });
              return false;
          }
        }else{
          lanjut = true;
        }
      });
      return lanjut;
    }
  </script>
  <style>
    .money{
        text-align:right;
    }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.laporankomponeninputmanual') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li>{{ trans('all.custom') }}</li>
        <li class="active"><strong>{{ trans('all.laporankomponeninputmanual') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">
    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      @if($data != '')
        <form action="{{ url('laporan/custom/komponeninputmanual/submitsimpan') }}" method="post" @if($tipedata != 'teks') onsubmit="return validasi()" @endif>
          <div class="ibox float-e-margins">
            <div class="alert alert-danger">
              <center>
                {{ $keterangan }}
              </center>
            </div>
            {{ csrf_field() }}
            <input type="hidden" value="{{ $tipedata }}" name="tipedata">
{{--            @if(strpos(Session::get('hakakses_perusahaan')->laporankomponeninputmanual, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponeninputmanual, 'm') !== false)--}}
                <button type="submit" id="submit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>&nbsp;&nbsp;
{{--            @endif--}}
            @if($tipedata != 'teks')
              <button type="button" id="setsemuanominal" class="btn btn-primary" data-toggle="modal" data-target="#modalsetsemuanominal"><i class="fa fa-money"></i>&nbsp;&nbsp;{{ trans('all.setsemuanominal') }}</button>&nbsp;&nbsp;
            @else
              <button type="button" id="setsemuaketerangan" class="btn btn-primary" data-toggle="modal" data-target="#modalsetsemuaketerangan"><i class="fa fa-sticky-note"></i>&nbsp;&nbsp;{{ trans('all.setsemuaketerangan') }}</button>&nbsp;&nbsp;
            @endif
            <button type="button" class="kembali btn btn-primary" onclick="back()"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
            <button type="button" id="ekspor" class="btn btn-primary pull-right" onclick="ke('{{ url('datainduk/laporan/laporankomponeninputmanual/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
            <br><p></p>
            <div class="ibox-content">
              <table width=100% class="table datatable table-striped table-condensed table-hover">
                <thead>
                  <tr>
                      <td class="pin"><b>{{ trans('all.pin') }}</b></td>
                      <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                      <td class="nama"><b>{{ trans('all.atribut') }}</b></td>
                      @if($tipedata != 'teks')
                        <td class="opsi4"><b>{{ trans('all.nominal') }}</b></td>
                      @else
                        <td class="opsi4"><b>{{ trans('all.keterangan') }}</b></td>
                      @endif
                  </tr>
                </thead>
                <tbody>
                  @for($i = 0; $i<count($data);$i++)
                    <tr>
                      <td style="padding-top:15px">{{ $data[$i]['pin'] }}</td>
                      <td style="padding-top:15px">
                          <span class="detailpegawai" title="{{ $data[$i]['nama'] }}" onclick="detailpegawai({{ $data[$i]['id'] }})" style="cursor:pointer;">{{ $data[$i]['nama'] }}</span>
                      </td>
                      <td style="padding-top:15px">{!! $data[$i]['atribut'] !!}</td>
                      <td>
                          <input type="hidden" name="idpegawai[]" value="{{ $data[$i]['id'] }}">
{{--                          @if(strpos(Session::get('hakakses_perusahaan')->laporankomponeninputmanual, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponeninputmanual, 'm') !== false)--}}
                            @if($tipedata != 'teks')
                              <input type="text" name="nominal[]" autocomplete="off" class="nominal money form-control" value="{{ $data[$i]['nominal'] }}" idpegawai="{{$data[$i]['id']}}" namapegawai="{{$data[$i]['nama']}}" batas_bawah="{{ $data[$i]['batas_bawah'] }}" batas_atas="{{ $data[$i]['batas_atas'] }}">
                            @else
                              <input type="text" name="keterangan[]" autocomplete="off" class="keterangan form-control" value="{{ $data[$i]['keterangan'] }}" idpegawai="{{$data[$i]['id']}}">
                            @endif
{{--                          @else--}}
{{--                            @if($tipedata != 'teks')--}}
{{--                              {{ $data[$i]['nominal'] }}--}}
{{--                            @else--}}
{{--                              {{ $data[$i]['keterangan'] }}--}}
{{--                            @endif--}}
{{--                          @endif--}}
                      </td>
                    </tr>
                  @endfor
                </tbody>
              </table>
{{--              @if(strpos(Session::get('hakakses_perusahaan')->laporankomponeninputmanual, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponeninputmanual, 'm') !== false)--}}
                  <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>&nbsp;&nbsp;
{{--              @endif--}}
              <button type="button" class="kembali btn btn-primary" onclick="back()"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
            </div>
          </div>
        </form>
      @else
          <center>{{ trans('all.nodata') }}</center>
      @endif
    </div>
  </div>

  {{--  form kembali  --}}
  <form action="{{ url('laporan/custom/komponeninputmanual/nextstep') }}" method="post">
    {{ csrf_field() }}
    <input type="hidden" value="{{ Session::get('laporankomponeninputmanual_bulan') }}" name="bulan">
    <input type="hidden" value="{{ Session::get('laporankomponeninputmanual_tahun') }}" name="tahun">
    @if(Session::has('laporankomponeninputmanual_atribut'))
      <input type="hidden" value="{{ implode(Session::get('laporankomponeninputmanual_atribut'),"|") }}" name="atributnilai">
    @else
      <input type="hidden" value="" name="atributnilai">
    @endif
    <button type="submit" id="kembali_submit" style="display: none" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>&nbsp;&nbsp;
  </form>

    <!-- Modal setsemuanominal-->
    <div class="modal fade" id="modalsetsemuanominal" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-sm">
        
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ trans('all.setsemuanominal') }}</h4>
          </div>
          <!-- <div class="modal-body" style="height:460px;overflow: auto;"> -->
          <div>
            <table width='100%'>
              <tr>
                <td style="padding:10px">
                  <select class="form-control" id="pilihan_nominal" onchange="pilihJenisInputan('nominal')">
                    {{--  <option value="periodesebelumnya">{{ trans('all.periodesebelumnya') }}</option>  --}}
                    <option value="pilihperiode">{{ trans('all.pilihperiode') }}</option>
                    <option value="inputmanual" selected>{{ trans('all.inputmanual') }}</option>
                  </select>
                </td>
              </tr>
              <tr id="tr_universal_periodesebelumnya_nominal">
                <td style="padding:10px;padding-top:0">
                  {{ trans('all.akanmengambilnominalsemuapegawaidariperiodesebelumnya') }}
                </td>
              </tr>
              <tr id="tr_universal_pilihperiode_nominal">
                <td style="padding:10px;padding-top:0">
                  {{ trans('all.periode') }}
                  <select class="form-control" id="universal_pilihperiode_nominal">
                    @foreach($listyymm as $key)
                      <option value="{{ $key['isi'] }}">{{ $key['tampilan']}}</option>
                    @endforeach
                  </select>
                </td>
              </tr>
              <tr id="tr_universal_inputmanual_nominal">
                <td style="padding:10px;padding-top:0">
                  {{ trans('all.nominal') }}
                  <input id="universalnominal" class="form-control money" value="0" type="text">
                </td>
              </tr>
              <tr>
                <td align=right style="padding:10px">
                  <button type="button" id="dapatkanlokasi" onclick="return setSemuaPegawai('nominal')" class="btn btn-success"><i class='fa fa-save'></i> {{ trans('all.set') }}</button>&nbsp;&nbsp;
                  <button data-dismiss="modal" id="tutupmodalnominal" class="btn btn-primary"><i class='fa fa-undo'></i> {{ trans('all.tutup') }}</button>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal setsemuanominal-->

    <!-- Modal setsemuaketerangan-->
    <div class="modal fade" id="modalsetsemuaketerangan" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-sm">
        
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" id='closemodalketerangan' data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ trans('all.setsemuaketerangan') }}</h4>
          </div>
          <!-- <div class="modal-body" style="height:460px;overflow: auto;"> -->
          <div>
            <table width='100%'>
              <tr>
                <td style="padding:10px">
                  <select class="form-control" id="pilihan_keterangan" onchange="pilihJenisInputan('keterangan')">
                    {{--  <option value="periodesebelumnya">{{ trans('all.periodesebelumnya') }}</option>  --}}
                    <option value="pilihperiode">{{ trans('all.pilihperiode') }}</option>
                    <option value="inputmanual" selected>{{ trans('all.inputmanual') }}</option>
                  </select>
                </td>
              </tr>
              <tr id="tr_universal_periodesebelumnya_keterangan">
                <td style="padding:10px;padding-top:0">
                  {{ trans('all.akanmengambilnominalsemuapegawaidariperiodesebelumnya') }}
                </td>
              </tr>
              <tr id="tr_universal_pilihperiode_keterangan">
                <td style="padding:10px;padding-top:0">
                  {{ trans('all.periode') }}
                  <select class="form-control" id="universal_pilihperiode_keterangan">
                    @foreach($listyymm as $key)
                      <option value="{{ $key['isi'] }}">{{ $key['tampilan']}}</option>
                    @endforeach
                  </select>
                </td>
              </tr>
              <tr id="tr_universal_inputmanual_keterangan">
                <td style="padding:10px;padding-top:0">
                  {{ trans('all.keterangan') }}
                  <input id="universalketerangan" class="form-control" type="text">
                </td>
              </tr>
              <tr>
                <td align=right style="padding:10px">
                  <button type="button" id="dapatkanlokasi" onclick="return setSemuaPegawai('keterangan')" class="btn btn-success"><i class='fa fa-save'></i> {{ trans('all.set') }}</button>&nbsp;&nbsp;
                  <button data-dismiss="modal" id="tutupmodalketerangan" class="btn btn-primary"><i class='fa fa-undo'></i> {{ trans('all.tutup') }}</button>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal setsemuaketerangan-->

  <script>
    function pilihJenisInputan(jenis){
      var pilihan = $('#pilihan_'+jenis).val();
      $('#tr_universal_periodesebelumnya_'+jenis).css('display','none');
      $('#tr_universal_pilihperiode_'+jenis).css('display','none');
      $('#tr_universal_inputmanual_'+jenis).css('display','none');
      
      $('#tr_universal_'+pilihan+'_'+jenis).css('display','');
    }

    function setNominalSemuaPegawai(){
      var universalnominal = $('#universalnominal').val();
      if(universalnominal != ''){
          $('.nominal').val(universalnominal);
          $('#tutupmodal').trigger('click');
      }
    }

    function setSemuaPegawai(jenis){
      var pilihan = $('#pilihan_'+jenis).val();
      if(pilihan == 'inputmanual'){
        var universalketerangan = $('#universal'+jenis).val();
        if(universalketerangan != ''){
            $('.'+jenis).val(universalketerangan);
            $('#tutupmodal'+jenis).trigger('click');
        }
      }else{
        var periode = '';
        if(pilihan == 'pilihperiode'){
          periode = $('#universal_pilihperiode_'+jenis).val();
        }
        // dapatkan value melalui ajax
        var url = '{{ url("laporangetinput")}}/'+periode;
        $.ajax({
          type: "GET",
          url: url,
          data: '',
          cache: false,
          success: function(resp){
            {{--  console.log(resp);  --}}
            if(resp.length > 0){
              // looping resp
              for(var i = 0;i<resp.length;i++){
                // looping data yg akan di input(class keterangan)
                $('.'+jenis).each(function() {
                  var idpegawai = $(this).attr('idpegawai');
                  // jika idpegawai sama, isikan value dari resp
                  if(resp[i]['idpegawai'] == idpegawai){
                    $(this).val(resp[i][jenis]);
                  }
                });
              }
              $('#tutupmodal'+jenis).trigger('click');
            }else{
              alertWarning("{{ trans('all.datatidakditemukan') }}");
              return false;
            }
          }
        });
      }
    }

    $(function(){
      pilihJenisInputan('nominal');
      pilihJenisInputan('keterangan');
      $('body').on('show.bs.modal', '#modalsetsemuanominal', function () {
        setTimeout(function(){ $("#universalnominal").focus(); pilihJenisInputan('nominal')},500);
      });

      $('body').on('show.bs.modal', '#modalsetsemuaketerangan', function () {
        setTimeout(function(){ $("#universalketerangan").focus();pilihJenisInputan('keterangan'); },500);
      });

      $('.datatable').DataTable({
          bStateSave: true,
          columnDefs: [{
              "targets": 3,
              "orderable": false,
              "searchable": false
          }],
          lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "{{ trans('all.semua') }}"]],
          language: lang_datatable,
          order: [[1, 'asc']]
      });
    });
  </script>
@stop

