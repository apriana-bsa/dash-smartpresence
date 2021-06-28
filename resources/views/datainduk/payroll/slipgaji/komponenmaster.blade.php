@extends('layouts.master')
@section('title', trans('all.atribut'))
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
  var dtable = '';
  var dtable2 = '';

  function ceksemua(ke){
      if(ke == 1) {
          if ($("#ceksemuatersedia").prop("checked")) {
              $(".cektersedia").prop("checked", true);
          } else {
              $(".cektersedia").prop("checked", false);
          }
      }else if(ke == 2){
          if ($("#ceksemuatersimpan").prop("checked")) {
              $(".cektersimpan").prop("checked", true);
          } else {
              $(".cektersimpan").prop("checked", false);
          }
          tampiltombolhapus();
      }
  }

  // jenis (simpan,hapus)
  function submit(jenis){
      if(jenis == 'simpan') {
          // tabel kiri
          var elements = document.getElementsByClassName("cektersedia");
      }else{
          // tabel kanan
          var elements = document.getElementsByClassName("cektersimpan");
      }
      var s = '';
      for(var i=0; i<elements.length; i++) {
          if (elements[i].checked==true)
          {
              s += ','+elements[i].id;
          }
      }

      if (s=='')
      {
          alertWarning('{{ trans('all.andabelummemilih') }}');
          return false;
      }

      s = s.substring(1);
      //jalankan ajax terus load datatable
      $.ajax({
          type: "GET",
          url: '{{ url('generatecsrftoken') }}',
          data: '',
          cache: false,
          success: function (token) {
              var dataString = "idkomponenmaster=" + encodeURIComponent(s)+'&jenis='+jenis+'&_token='+token;
              $.ajax({
                  type: "POST",
                  url: "{!! url("datainduk/payroll/slipgaji/$idslipgaji/komponenmaster") !!}",
                  data: dataString,
                  cache: false,
                  success: function (resp) {
                      console.log(resp);
                      if (resp != "ok") {
                          alertError(resp);
                      }
                      dtable.ajax.url( '{!! url("datainduk/payroll/slipgaji/$idslipgaji/komponenmaster/tersedia") !!}' ).load();
                      dtable2.ajax.url( '{!! url("datainduk/payroll/slipgaji/$idslipgaji/komponenmaster/tersimpan") !!}' ).load();
                      setTimeout(function(){
                          $("#ceksemuatersedia").prop("checked", false);
                          $("#ceksemuatersimpan").prop("checked", false);
                      },200);
                  }
              });
          }
      });
      return false;
  }
  </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.slipgaji').' ('.$slipgaji.')' }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.payroll') }}</li>
        <li class="active"><strong>{{ trans('all.slipgaji') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2"></div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
              <li class="active"><a href="{{ url('datainduk/payroll/slipgaji/'.$idslipgaji.'/komponenmaster') }}">{{ trans('all.komponenmaster') }}</a></li>
              <li><a href="{{ url('datainduk/payroll/slipgaji/'.$idslipgaji.'/pegawai') }}">{{ trans('all.pegawai') }}</a></li>
          </ul>
          <br>
          <button type="button" onclick="ke('{{url('datainduk/payroll/slipgaji')}}')" class="btn btn-primary"><i class='fa fa-undo'></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
          <br><p></p>
          <table>
              <tr>
                  <td valign='top' width='50%'>
                      <div class="ibox float-e-margins">
                          <div class="ibox-content">
                              <table width=100% class="table datatabletersedia table-striped table-condensed table-hover">
                                  <thead>
                                  <tr>
                                      <td class='cek'><input type='checkbox' onclick='ceksemua(1)' id='ceksemuatersedia'></td>
                                      <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                                  </tr>
                                  </thead>
                              </table>
                          </div>
                      </div>
                  </td>
                  <td width='65px' style='text-align:center !important;margin-top:275px;padding:10px;'>
                      <table>
                          <tr>
                              <td>
                                  <button type="button" onclick="return submit('simpan')" class="btn btn-primary"><i class="fa fa-arrow-right"></i></button>
                              </td>
                          </tr>
                          <tr>
                              <td style="padding-top:10px">
                                  <button type="button" id="buttonhapus" onclick="return submit('hapus')" class="btn btn-primary"><i class="fa fa-arrow-left"></i></button>
                              </td>
                          </tr>
                      </table>
                  </td>
                  <td valign='top' width='50%'>
                      <div class="ibox float-e-margins">
                          <div class="ibox-content">
                              <table width=100% class="table datatabletersimpan table-striped table-condensed table-hover">
                                  <thead>
                                  <tr>
                                      <td class='cek'><input type='checkbox' onclick='ceksemua(2)' id='ceksemuatersimpan'></td>
                                      <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                                  </tr>
                                  </thead>
                              </table>
                          </div>
                      </div>
                  </td>
              </tr>
          </table>
      </div>
    </div>
  </div>
@stop

@push('scripts')
<script>
    $(function() {
        dtable = $('.datatabletersedia').DataTable({
            processing: true,
            bStateSave: true,
            serverSide: true,
            scrollX: true,
            language: lang_datatable,
            ajax: {
                url: '{!! url("datainduk/payroll/slipgaji/$idslipgaji/komponenmaster/tersedia") !!}',
                {{--type: "POST",--}}
                {{--data: { _token: '{!! csrf_token() !!}' }--}}
            },
            columns: [
                { data: 'cek', name: 'cek', orderable: false, searchable: false },
                { data: 'nama', name: 'nama' }
            ],
            order: [[1, 'asc']]
        });

        dtable2 = $('.datatabletersimpan').DataTable({
            processing: true,
            bStateSave: true,
            serverSide: true,
            scrollX: true,
            language: lang_datatable,
            ajax: {
                url: '{!! url("datainduk/payroll/slipgaji/$idslipgaji/komponenmaster/tersimpan") !!}',
                {{--type: "POST",--}}
                {{--data: { _token: '{!! csrf_token() !!}' }--}}
            },
            columns: [
                { data: 'cek', name: 'cek', orderable: false, searchable: false },
                { data: 'nama', name: 'nama' }
            ],
            order: [[1, 'asc']]
        });
    });
</script>
@endpush