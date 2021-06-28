@extends('layouts/master')
@section('title', trans('all.beranda'))
@section('content')
  
  <script>
  @if(Session::get('message'))
    $(document).ready(function() {
      setTimeout(function() {
                  toastr.options = {
                      closeButton: true,
                      progressBar: true,
                      timeOut: 4000,
                      extendedTimeOut: 4000,
                      positionClass: 'toast-bottom-right'
                  };
                  toastr.info('{{ Session::get("message") }}', '{{ trans("all.loginberhasil") }}');
              }, 500);
    });
  @endif

  function goto(page){
    window.location.href=page;
  }

  function validasi(){
      var tanggalawal = $('#tanggalawal').val();
      var tanggalakhir = $('#tanggalakhir').val();

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
  }

  $(function(){
      $('.date').mask("00/00/0000", {clearIfNotMatch: true});

      $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
          $(this).datepicker('hide');
      });
  });
  </script>
  <style>
  .progress{
      margin-bottom:30px;
  }
  
  .info-box{
      display: block;
      min-height: 80px;
      background: #fff;
      width: 100%;
      box-shadow: 0 1px 1px rgba(0,0,0,0.1);
      border-radius: 2px;
      margin-bottom: 15px;
  }

  .info-box-icon {
      border-top-left-radius: 2px;
      border-top-right-radius: 0;
      border-bottom-right-radius: 0;
      border-bottom-left-radius: 2px;
      display: block;
      float: left;
      height: 80px;
      width: 80px;
      text-align: center;
      font-size: 45px;
      line-height: 72px;
      background: rgba(0,0,0,0.1);
  }

  .info-box-content {
      padding: 5px 10px;
      margin-left: 90px;
  }

  .info-box-text {
      display: block;
      font-size: 18px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      text-transform: uppercase;
  }

  .info-box-number {
      display: block;
      font-weight: bold;
      font-size: 28px;
  }

  .dataTables_wrapper{
      padding-bottom:0;
      margin-top:-8px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.ekspor') }}</h2>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
      <div class="row">
        <div class="col-lg-12">
          <div class="ibox float-e-margins">
            <div class="ibox-content text-center p-md">
                <form method="post" name="form1" action="txtexport" onsubmit="return validasi()">
                    {{ csrf_field() }}
                    <table>
                        <tr>
                            <td>
                                <input class="form-control date" id="tanggalawal" value="{{ date('d/m/Y') }}" placeholder="dd/mm/yyyy" name="tanggalawal">
                            </td>
                            <td style="padding-left:10px">-</td>
                            <td style="padding-left:10px">
                                <input class="form-control date" id="tanggalakhir" value="{{ date('d/m/Y') }}" placeholder="dd/mm/yyyy" name="tanggalakhir">
                            </td>
                            <td style="padding-left:10px">
                                <input type="submit" class="btn btn-primary" value="{{ trans('all.ekspor') }}">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
          </div>
        </div>
      </div>
  </div>

  <!-- Flot -->
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.tooltip.min.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.spline.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.resize.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.pie.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.symbol.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/flot/jquery.flot.time.js') }}"></script>
  <script src="{{ asset('lib/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
@stop