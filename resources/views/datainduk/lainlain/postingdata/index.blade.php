@extends('layouts/master')
@section('title', trans('all.postingdata'))
@section('content')

  <script>
  $(function(){

    setTimeout(function(){ $('#tanggalawal').focus();},200);

    @if(Session::get('message'))
      setTimeout(function() {
                  toastr.options = {
                      closeButton: true,
                      progressBar: true,
                      timeOut: 4000,
                      extendedTimeOut: 4000,
                      positionClass: 'toast-bottom-right'
                  };
                  toastr.info('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
              }, 500);
    @endif

    $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
      $(this).datepicker('hide');
    });
    
    $('.date').mask("00/00/0000", {clearIfNotMatch: true});
  });

  function validasi(){
    $('#submit').attr( 'data-loading', '' );
    $('#submit').attr('disabled', 'disabled');
    
    var tanggalawal = $("#tanggalawal").val();
    var tanggalakhir = $("#tanggalakhir").val();
    var jenisposting = $('#jenisposting').val();

    @if(Session::has('conf_webperusahaan'))
    @else
      alertWarning("{{ trans('all.perusahaanbelumdipilih') }}",
            function() {
              aktifkanTombol();
            });
      return false;
    @endif

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

    if(cekSelisihTanggal(tanggalawal,tanggalakhir) == true){
        alertWarning("{{ trans('all.selisihharimaksimal31') }}",
            function() {
                aktifkanTombol();
                setFocus($('#tanggalakhir'));
            });
        return false;
    }

    var tglakhir = parseDate(tanggalakhir);
    var tglawal = parseDate(tanggalawal);

    if(jenisposting == 'semuapegawai') {
        $.ajax({
            type: "GET",
            url: '{{ url('gettotalpegawai') }}',
            data: '',
            cache: false,
            success: function (totalpegawai) {
                if (totalpegawai > 0) {
                    var lompatan = 14;
                    if (totalpegawai > 100) {
                        lompatan = 1;
                    } else if (totalpegawai > 50) {
                        lompatan = 2;
                    } else if (totalpegawai > 25) {
                        lompatan = 4;
                    } else if (totalpegawai > 10) {
                        lompatan = 7;
                    } else {
                        lompatan = 14;
                    }
                    $('#hasilPosting').css('display', '').html('');
                    looping(tglawal, tglakhir, lompatan);
                }
            }
        });
    }else if(jenisposting == 'pegawaitertentu'){
        var pegawai = $('#pegawai').val();
        var arrpegawai = pegawai.split(',');
        var param_tglawal_separatorminus = tglawal.getFullYear() + "-" + LPAD(tglawal.getMonth() + 1, 2, '0') + "-" + LPAD(tglawal.getDate(), 2, '0');
        var param_tglakhir_separatorminus = tglakhir.getFullYear() + "-" + LPAD(tglakhir.getMonth() + 1, 2, '0') + "-" + LPAD(tglakhir.getDate(), 2, '0');

        $('#_tanggalawal_separatorminus').val(param_tglawal_separatorminus);
        $('#_tanggalakhir_separatorminus').val(param_tglakhir_separatorminus);
        $('#hasilPosting').css('display', '').html('');
        loopingPerPegawai(arrpegawai,0);
//        var pegawaisplit = pegawai.split(',');
//        var totalpegawai = pegawaisplit.length;
//
//        $('#hasilPosting').css('display', '').html('');
//
//        var param_tglawal_separatorminus = tglawal.getFullYear() + "-" + LPAD(tglawal.getMonth() + 1, 2, '0') + "-" + LPAD(tglawal.getDate(), 2, '0');
//        var param_tglakhir_separatorminus = tglakhir.getFullYear() + "-" + LPAD(tglakhir.getMonth() + 1, 2, '0') + "-" + LPAD(tglakhir.getDate(), 2, '0');
//
//        $('#_tanggalawal_separatorminus').val(param_tglawal_separatorminus);
//        $('#_tanggalakhir_separatorminus').val(param_tglakhir_separatorminus);
//        $('#pegawai').val(pegawaisplit[i]);
//        loopingPerPegawai(pegawai);


        {{--for(var i = 0;i<totalpegawai;i++){--}}
            {{--if(totalpegawai > 0)--}}
            {{--{--}}
                {{--$('#hasilPosting').css('display', '').html('');--}}

                {{--var param_tglawal_separatorminus = tglawal.getFullYear() + "-" + LPAD(tglawal.getMonth() + 1, 2, '0') + "-" + LPAD(tglawal.getDate(), 2, '0');--}}
                {{--var param_tglakhir_separatorminus = tglakhir.getFullYear() + "-" + LPAD(tglakhir.getMonth() + 1, 2, '0') + "-" + LPAD(tglakhir.getDate(), 2, '0');--}}

                {{--$('#_tanggalawal_separatorminus').val(param_tglawal_separatorminus);--}}
                {{--$('#_tanggalakhir_separatorminus').val(param_tglakhir_separatorminus);--}}
                {{--$('#pegawai').val(pegawaisplit[i]);--}}
                {{--loopingPerPegawai(totalpegawai,pegawai);--}}
                {{--console.log(pegawaisplit[i]);--}}
                {{--console.log(totalpegawai[i]);--}}
                {{--var dataString = new FormData($('#myform')[0]);--}}
                {{--$.ajax({--}}
                    {{--type: "POST",--}}
                    {{--url: "{{ url('datainduk/lainlain/postingdata') }}",--}}
                    {{--data: dataString,--}}
                    {{--async: true,--}}
                    {{--cache: false,--}}
                    {{--contentType: false,--}}
                    {{--processData: false,--}}
                    {{--success: function (data) {--}}
                        {{--console.log(data['msg']);--}}
                        {{--var hasil = '<span style="color:'+data['warna']+'">'+data['msg']+'</span><br>';--}}
                        {{--$('#hasilPosting').append(hasil);--}}
                        {{--//looping(tglawal, tglakhir, lompatan);--}}
                    {{--}--}}
                {{--});--}}
            {{--}--}}
{{--//            aktifkanTombol();--}}
        {{--}--}}
        //console.log(pegawaisplit[0]);
    }
    //console.log(totalpegawai);
    return false;
  }

  function loopingPerPegawai(pegawai,i){
    if(i<pegawai.length){
        $('#pegawai').val(pegawai[i]);
        var dataString = new FormData($('#myform')[0]);
        $.ajax({
            type: "POST",
            url: "{{ url('datainduk/lainlain/postingdata') }}",
            data: dataString,
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                console.log(data['msg']);
                var hasil = '<span style="color:'+data['warna']+'">'+data['msg']+'</span><br>';
                $('#hasilPosting').append(hasil);
                i++;
                loopingPerPegawai(pegawai, i);
            }
        });
    }else{
        aktifkanTombol();
    }
  }

  function LPAD(str, len, ch) {
    var hasil = str.toString();
    while (hasil.length<len) {
      hasil = ch + hasil;
    }
    return hasil;
  }

  function looping(tglawal, tglakhir, lompatan){
    if (tglawal<=tglakhir) {
      var param_tglawal_separatorminus = tglawal.getFullYear() + "-" + LPAD(tglawal.getMonth() + 1, 2, '0') + "-" + LPAD(tglawal.getDate(), 2, '0');
      var param_tglawal = LPAD(tglawal.getDate(), 2, '0') + '/' + LPAD(tglawal.getMonth() + 1, 2, '0') + '/' + tglawal.getFullYear();
      var tgllompatan = new Date(tglawal.valueOf());
      tgllompatan.setDate(tgllompatan.getDate() + lompatan - 1);
      if (tgllompatan <= tglakhir) {
        tglawal = new Date(tgllompatan.valueOf());
      }
      else {
        tglawal = new Date(tglakhir.valueOf());
      }
      var param_tglakhir_separatorminus = tglawal.getFullYear() + "-" + LPAD(tglawal.getMonth() + 1, 2, '0') + "-" + LPAD(tglawal.getDate(), 2, '0');
//      var param_tglakhir = LPAD(tglawal.getDate(), 2, '0') + '/' + LPAD(tglawal.getMonth() + 1, 2, '0') + '/' + tglawal.getFullYear();

      tglawal.setDate(tglawal.getDate() + 1);

      $('#_tanggalawal_separatorminus').val(param_tglawal_separatorminus);
      $('#_tanggalakhir_separatorminus').val(param_tglakhir_separatorminus);
      var dataString = new FormData($('#myform')[0]);
      console.log('proses posting tanggal '+param_tglawal_separatorminus+' - '+param_tglakhir_separatorminus);
      $.ajax({
        type: "POST",
        url: "{{ url('datainduk/lainlain/postingdata') }}",
        data: dataString,
        async: true,
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
//          console.log(data);
          var hasil = '<span style="color:'+data['warna']+'">'+data['msg']+'</span><br>';
          $('#hasilPosting').append(hasil);
          looping(tglawal, tglakhir, lompatan);
        }
      });
    }else{
      aktifkanTombol();
    }
  }

  function parseDate(str) {
    var mdy = str.split('/');
    return new Date(mdy[2], mdy[1]-1, mdy[0]);
  }

  function jenisPosting(){
      var jenisposting = $('#jenisposting').val();
      $('.semuapegawai').css('display', 'none');
      $('.pegawaitertentu').css('display', 'none');
      if(jenisposting == 'semuapegawai'){
          $('.semuapegawai').css('display', '');
      }
      if(jenisposting == 'pegawaitertentu'){
          $('.pegawaitertentu').css('display', '');
      }
  }
  </script>
  <style>
  td{
    padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.postingdata') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.lainlain') }}</li>
        <li class="active"><strong>{{ trans('all.postingdata') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content p-md">
            <form method="post" id='myform' action="{{ url('datainduk/lainlain/postingdata') }}" onsubmit="return validasi()">
              <input type="hidden" value="" id="_tanggalawal_separatorminus" name="tanggalawal_separatorminus">
              <input type="hidden" value="" id="_tanggalakhir_separatorminus" name="tanggalakhir_separatorminus">
              <table>
                <tr>
                  <td width="70px">{{ trans('all.jenis') }}</td>
                  <td>
                    <select id="jenisposting" name="jenisposting" class="form-control" onchange="return jenisPosting()">
                      <option value="semuapegawai">{{ trans('all.semuapegawai') }}</option>
                      <option value="pegawaitertentu">{{ trans('all.pegawaitertentu') }}</option>
                    </select>
                  </td>
                </tr>
              </table>
              <table>
                <tr>
                  <td width="70px">{{ trans('all.tanggal') }}</td>
                  <td colspan="2">
                    <table>
                      <tr>
                        <td style="padding-left:0px;padding-bottom: 0px;float:left;">
                          <input type="text" class="form-control date" size="11" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalawal" id="tanggalawal" value="{{ $valuetglawalakhir->tanggalawal }}" maxlength="10">
                        </td>
                        <td style="padding-bottom: 0px">-</td>
                        <td style="padding-bottom: 0px;padding-right: 0px;float:left;">
                          <input type="text" class="form-control date" size="11" autocomplete="off" placeholder="dd/mm/yyyy" name="tanggalakhir" id="tanggalakhir" value="{{ $valuetglawalakhir->tanggalakhir }}" maxlength="10">
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td class="pegawaitertentu" style="display: none;" width="70px">{{ trans('all.pegawai') }}</td>
                  <td class="pegawaitertentu" style="display: none;">
                    <table>
                      <tr>
                        <td style="padding-left:0;padding-bottom: 0px;padding-right: 0px;float:left;min-width:200px">
                          <input type="text" class="form-control" autofocus autocomplete="off" name="pegawai" id="pegawai" maxlength="100">
                          <script type="text/javascript">
                              $(document).ready(function(){
                                  $("#pegawai").tokenInput("{{ url('tokenpegawai') }}", {
                                      theme: "facebook",
                                  });
                              });
                          </script>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td style="padding-top:15px" colspan="3">
                    <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.proses') }}</span> <span class="spinner"></span></button>
                  </td>
                </tr>
              </table>
              <div class="row semuapegawai">
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
                @if(count($jamkerja) > 0)
                  <div class="col-md-6" style="padding-left:0;margin-top:5px;">
                    <div class="col-md-12"><p><b>{{ trans('all.jamkerja') }}</b></p></div>
                    <div class="col-md-4">
                      @foreach($jamkerja as $key)
                        <div class="col-md-12">
                          <table>
                            <tr>
                              <td valign="top" style="width:10px;">
                                <input type="checkbox" id="semuajamkerja_{{ $key->id }}" value="{{ $key->id }}" name="jamkerja[]" onclick="checkboxallclick('semuajamkerja_{{ $key->id }}','attrjkf_{{ $key->id }}')">&nbsp;&nbsp;
                              </td>
                              <td valign="top">
                                <span class="spancheckbox" onclick="spanallclick('semuajamkerja_{{ $key->id }}','attrjkf_{{ $key->id }}')">{{ $key->nama }}</span>
                              </td>
                            </tr>
                          </table>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
            </form>
            <br>
            <pre style="display: none;" id="hasilPosting"></pre>
          </div>
        </div>
      </div>
    </div>
  </div>

@stop