@extends('layouts/master')
@section('title', trans('all.formatsms'))
@section('content')

  <!-- Switchery -->
  <link href="{{ asset('lib/css/plugins/switchery/switchery.css') }}" rel="stylesheet">
  <script src="{{ asset('lib/js/plugins/switchery/switchery.js') }}"></script>
  <link href="{{ asset('lib/css/patternLock.css') }}"  rel="stylesheet" type="text/css" />
  <script src="{{ asset('lib/js/patternLock.min.js') }}"></script>
  <!-- NouSlider -->
  <script src="{{ asset('lib/js/plugins/nouslider/jquery.nouislider.min.js') }}"></script>
  <link href="{{ asset('lib/css/plugins/nouslider/jquery.nouislider.css') }}" rel="stylesheet">
  <script>
  $(function(){

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

    $('.jam').inputmask( 'h:s' );
  });

  function validasi(){

    $('#submit').attr( 'data-loading', '' );
    $('#submit').attr('disabled', 'disabled');
  }
  </script>
  <style>
  .spanmesin{
    cursor: pointer;
  }

  td{
    padding:5px;
  }

  .pilihan{
    cursor:pointer !important;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.formatsms') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.pengaturan') }}</li>
        <li class="active"><strong>{{ trans('all.formatsms') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
          <li @if($jenis == 'absen') class="active" @endif><a href="{{ url('pengaturan/formatsmsabsen') }}">{{ trans('all.saatpresensi') }}</a></li>
          <li @if($jenis == 'verifikasi') class="active" @endif><a href="{{ url('pengaturan/formatsmsverifikasi') }}">{{ trans('all.verifikasilupakatasandipegawai') }}</a></li>
          <li @if($jenis == 'lupapwdpegawai') class="active" @endif><a href="{{ url('pengaturan/formatsmslupapwdpegawai') }}">{{ trans('all.lupakatasandipegawai') }}</a></li>
        </ul>
        <br>
        <div class="ibox float-e-margins">
          <div class="ibox-content p-md">

              <form method="post" id='myform' action="{{ url('/pengaturan/formatsmsabsen') }}" onsubmit="return validasi()">
                {{ csrf_field() }}
                <table cellpadding="0" width='100%' cellspacing="0" border="0">
                  <tr>
                    <td>
                      <textarea name="formatsms" id="formatsms" rows="5" class="form-control">{{ $data->format_sms_absen }}</textarea>
                      <p></p>
                      <b style='cursor:default' class='label pilihan' onclick='give("{company}","formatsms")'>{company}</b>&nbsp;: {{ trans('all.menyisipkannamaperusahaan') }}
                      <p></p>
                      <b style='cursor:default' class='label pilihan' onclick='give("{name}","formatsms")'>{name}</b>&nbsp;: {{ trans('all.menyisipkannamapegawai') }}
                      <p></p>
                      <b style='cursor:default' class='label pilihan' onclick='give("{pin}","formatsms")'>{pin}</b>&nbsp;: {{ trans('all.menyisipkanpinpegawai') }}
                      <p></p>
                      <b style='cursor:default' class='label pilihan' onclick='give("{note[WITH NOTE]}","formatsms")'>{note[WITH NOTE]}</b>&nbsp;: {!!  trans('all.menyisipkancatatansaatpresensi') !!}
                      <p></p>
                      <b style='cursor:default' class='label pilihan' onclick='give("{inout[IN|OUT]}","formatsms")'>{inout[IN|OUT]}</b>&nbsp;: {!!  trans('all.menyisipkancatatansaatmasukataukeluar') !!}
                      <p></p>
                      <b style='cursor:default' class='label pilihan' onclick='give("{datetime[id]}","formatsms")'>{datetime[id]}</b>&nbsp;: {{ trans('all.menyisipkanwaktukadaluarsaformatindo') }}
                      <p></p>
                      <b style='cursor:default' class='label pilihan' onclick='give("{datetime[en]}","formatsms")'>{datetime[en]}</b>&nbsp;: {{ trans('all.menyisipkanwaktukadaluarsaformatinggris') }}
                      <p></p>
                      <b style='cursor:default' class='label pilihan' onclick='give("{crlf}","formatsms")'>{crlf}</b>&nbsp;: {{ trans('all.menyisipkanbarisbaru') }}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>
                    </td>
                  </tr>
                </table>
              </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@stop