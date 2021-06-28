@extends('layouts.master')
@section('title', trans('all.tukarshift'))
@section('content')
  

    <script>
    $(document).ready(function() {
        @if(Session::get('message'))
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
        @endif

        $(".pegawaiinput").tokenInput("{{ url('tokenpegawai') }}", {
            theme: "facebook",
            tokenLimit: 1
        });

        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });

        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
    });

    function submitTampilkan(i){

        $('#submit'+i).attr( 'data-loading', '' );
        $('#submit'+i).attr('disabled', 'disabled');

        var pegawai = $("#pegawai"+i).val();
        var jenis = $('#myform'+i).attr('jenis');

        if(pegawai == ""){
            alertWarning("{{ trans('all.pegawaikosong') }}",
                    function() {
                        aktifkanTombol();
                        $('#submit'+i).removeAttr('disabled').removeAttr('data-loading');
                        setFocus($('#token-input-pegawai'+i));
                    });
            return false;
        }

        var dataString = new FormData($('#myform'+i)[0]);

        $.ajax({
            type: "POST",
            url: "{{ url('datainduk/absensi') }}/"+jenis+'/submittampilkan',
            data: dataString,
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function (html) {
                $('#submit' + i).removeAttr('data-loading').removeAttr('disabled');
                $('#content_' + i + '_'+jenis).html(html);
                if ($('#content_1_'+jenis).html() != '' && $('#content_2_'+jenis).html() != '') {
                    $('#tombol'+jenis).css('display', '');
                }
            }
        });
        return false;
    }

    function gantiTab(param){
        $('#myform1').attr('jenis', param);
        $('#myform2').attr('jenis', param);
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
      <h2>{{ trans('all.tukarshift') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.tukarshift') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-lg-12" style="margin-bottom:10px;">
            <ul class="nav nav-tabs" style="padding:10px;padding-bottom:0">
                <li class="active"><a data-toggle="tab" onclick="gantiTab('tukarshift')" href="#tab-1">{{ trans('all.tukarshift') }}</a></li>
                <li><a data-toggle="tab" onclick="gantiTab('koreksishift')" href="#tab-2">{{ trans('all.koreksishift') }}</a></li>
            </ul>
        </div>
        <div class="col-lg-6">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <form id="myform1" method="post" jenis="tukarshift" onsubmit="return submitTampilkan(1)">
                        {{csrf_field()}}
                        <input type="hidden" name="dari" value="1">
                        <table width="100%">
                            <tr>
                                <td width="80px">{{ trans('all.pegawai') }}</td>
                                <td style="min-width:200px" colspan="2">
                                    <input type="text" name="pegawai" class="pegawaiinput form-control" id="idpegawai_1">
                                </td>
                            </tr>
                            <tr>
                                <td width="80px">{{ trans('all.periode') }}</td>
                                <td colspan="2" style="float:left">
                                    <select class="form-control" name="periode" id="periode1">
                                        @for($i=0;$i<count($periode);$i++)
                                            <option value="{{ $periode[$i]['isi'] }}" @if($periode[$i]['isi'] == date('ym')) selected @endif>{{ $periode[$i]['tampilan'] }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td style="float: left;">
                                    <input type="submit" id="submit1" class="btn btn-primary" value="{{ trans('all.tampilkan') }}">
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <form id="myform2"  method="post" jenis="tukarshift" onsubmit="return submitTampilkan(2)">
                        {{csrf_field()}}
                        <input type="hidden" name="dari" value="2">
                        <table width="100%">
                            <tr>
                                <td width="80px">{{ trans('all.pegawai') }}</td>
                                <td style="min-width:100px" colspan="2">
                                    <input type="text" name="pegawai" class="pegawaiinput form-control" id="idpegawai_2">
                                </td>
                            </tr>
                            <tr>
                                <td>{{ trans('all.periode') }}</td>
                                <td colspan="2" style="float:left">
                                    <select class="form-control" name="periode" id="periode2">
                                        @for($i=0;$i<count($periode);$i++)
                                            <option value="{{ $periode[$i]['isi'] }}" @if($periode[$i]['isi'] == date('ym')) selected @endif>{{ $periode[$i]['tampilan'] }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td style="float: left;">
                                    <input type="submit" id="submit2" class="btn btn-primary" value="{{ trans('all.tampilkan') }}">
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="tab-content">
                <div id="tab-1" class="tab-pane active">
                    <div class="col-lg-6" style="padding-left:0">
                        <div class="ibox float-e-margins">
                            <div id="content_1_tukarshift"></div>
                        </div>
                    </div>
                    <div class="col-lg-6" style="padding-right:0">
                        <div class="ibox float-e-margins">
                            <div id="content_2_tukarshift"></div>
                        </div>
                    </div>
                    <div class="col-lg-12" style="padding-bottom:20px">
                        <center><input type="button" id="tomboltukarshift" style="display: none;" onclick="submitTukarShift()" class="btn btn-primary" value="{{ trans('all.tukarshift') }}"></center>
                    </div>
                    {{-- form untuk submit tukar shift --}}
                    <form id="formtukarshift">
                        {{ csrf_field() }}
                        <input type="hidden" name="idjamkerja_a" id="idjamkerja_a" value="">
                        <input type="hidden" name="idjamkerjashift_a" id="idjadwalshift_a" value="">
                        <input type="hidden" name="tanggal_a" id="tanggal_a" value="">
                        <input type="hidden" name="idjamkerja_b" id="idjamkerja_b" value="">
                        <input type="hidden" name="idjamkerjashift_b" id="idjadwalshift_b" value="">
                        <input type="hidden" name="tanggal_b" id="tanggal_b" value="">
                    </form>
                </div>
                <div id="tab-2" class="tab-pane">
                    <form id="form_content">
                        {{ csrf_field() }}
                        <div class="col-lg-6" style="padding-left:0">
                            <div id="content_1_koreksishift"></div>
                        </div>
                        <div class="col-lg-6" style="padding-right:0">
                            <div id="content_2_koreksishift"></div>
                        </div>
                    </form>
                    <div class="col-lg-12" style="display:none;margin-bottom:20px" id="tombolkoreksishift">
                        <center><input type="button" onclick="submitKoreksiShift()" class="btn btn-primary" value="{{ trans('all.koreksishift') }}"></center>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
      function submitTukarShift()
      {
          var idjamkerja1 = '';
          var idjamkerja2 = '';

          var element1 = document.getElementsByClassName('idjadwalshift_1');
          var element2 = document.getElementsByClassName('idjadwalshift_2');
          //var el = new Array();
          var elcheck1 = 0;
          var jadwalshift1 = '';
          var tanggal1 = '';
          for(var i = 0;i<element1.length;i++){
              if(element1[i].checked) {
                  elcheck1++;
                  var val1 = element1[i].value.split('@');
                  idjamkerja1 +='|'+val1[0];
                  var js_ex1 = val1[1].split(':'); //js_ex = jadwalshit explode
                  //jadwalshift1 += '|' + val1[1];
                  tanggal1 += '|' + js_ex1[0];
                  jadwalshift1 += '|' + js_ex1[1];
              }
          }
          var elcheck2 = 0;
          var jadwalshift2 = '';
          var tanggal2 = '';
          for(var i = 0;i<element2.length;i++){
              if(element2[i].checked) {
                  elcheck2++;
                  var val2 = element2[i].value.split('@');
                  idjamkerja2 +='|'+val2[0];
                  //jadwalshift2 += '|' + val2[1];
                  var js_ex2 = val2[1].split(':'); //js_ex = jadwalshit explode
                  //jadwalshift1 += '|' + val1[1];
                  tanggal2 += '|' + js_ex2[0];
                  jadwalshift2 += '|' + js_ex2[1];
              }
          }

          if(elcheck1 != elcheck2){
              alertWarning("{{ trans('all.jumlahshifttidaksesuai') }}");
          }

          //ngisi input hidden
          var idjamkerja1 = idjamkerja1.substr(1);
          var idjadwalshift1 = jadwalshift1.substr(1);
          var tanggal1 = tanggal1.substr(1);
          var idjamkerja2 = idjamkerja2.substr(1);
          var idjadwalshift2 = jadwalshift2.substr(1);
          var tanggal2 = tanggal2.substr(1);

          $('#idjamkerja_a').val(idjamkerja1);
          $('#idjadwalshift_a').val(idjadwalshift1);
          $('#tanggal_a').val(tanggal1);
          $('#idjamkerja_b').val(idjamkerja2);
          $('#idjadwalshift_b').val(idjadwalshift2);
          $('#tanggal_b').val(tanggal2);

          var dataString = new FormData($('#formtukarshift')[0]);
          // setTimeout(function(){
          $.ajax({
              type: "POST",
              url: "{{ url('datainduk/absensi/tukarshift/submit') }}",
              data: dataString,
              async: true,
              cache: false,
              contentType: false,
              processData: false,
              success: function(html){
                  if(html['status'] == 'OK') {
                      alertSuccess('{{ trans('all.tukarshiftberhasil') }}',function(){
                          submitTampilkan(1);
                          submitTampilkan(2);
                      });
                  }else{
                      alertError(html['msg']);
                  }
              }
          });
    }

    function submitKoreksiShift()
    {
      var dataString = new FormData($('#form_content')[0]);
      // setTimeout(function(){
      $.ajax({
          type: "POST",
          url: "{{ url('datainduk/absensi/koreksishift/submit') }}",
          data: dataString,
          async: true,
          cache: false,
          contentType: false,
          processData: false,
          success: function(html){
              if(html['status'] == 'OK') {
                  alertSuccess('{{ trans('all.databerhasildisimpan') }}');
              }else{
                  alertError(html['msg']);
              }
          }
      });
    }
    </script>
  </div>
@stop