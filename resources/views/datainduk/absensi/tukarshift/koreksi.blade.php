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
        // setTimeout(function(){
        $.ajax({
            type: "POST",
            url: "{{ url('datainduk/absensi/koreksishift/submittampilkan') }}",
            data: dataString,
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function(html){
                //console.log(html);
                $('#submit'+i).removeAttr('data-loading').removeAttr('disabled');
                setTimeout( function(){
                    $('#content_'+i).html(html);

                    if($('#content_1').html() != '' && $('#content_2').html() != ''){
                        $('#tombolkoreksishift').css('display', '');
                    }
                },10);
            }
        });
        return false;
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
            <ul class="nav nav-tabs">
                <li @if($jenis == 'tukarshift') class="active" @endif><a href="{{ url('datainduk/absensi/tukarshift') }}">{{ trans('all.tukarshift') }}</a></li>
                <li @if($jenis == 'koreksishift') class="active" @endif><a href="{{ url('datainduk/absensi/koreksishift') }}">{{ trans('all.koreksishift') }}</a></li>
            </ul>
        </div>
        <div class="col-lg-6">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <form id="myform1" method="post" onsubmit="return submitTampilkan(1)">
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
                    <form id="myform2"  method="post" onsubmit="return submitTampilkan(2)">
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
        <form id="form_content">
            {{ csrf_field() }}
            <div class="col-lg-6">
                <div id="content_1"></div>
            </div>
            <div class="col-lg-6">
                <div id="content_2"></div>
            </div>
        </form>
        <div class="col-lg-12" style="display:none;margin-bottom:20px" id="tombolkoreksishift">
            <center><input type="button" onclick="submitKoreksiShift()" class="btn btn-primary" value="{{ trans('all.koreksishift') }}"></center>
        </div>
    </div>
  </div>
    <script>
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
                    if(html['status'] == 'ok') {
                        alertSuccess('{{ trans('all.databerhasildisimpan') }}');
                    }else{
                        alertError(html['msg']);
                    }
                }
            });
        }
    </script>
@stop