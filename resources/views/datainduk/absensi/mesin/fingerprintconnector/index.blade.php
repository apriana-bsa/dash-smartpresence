@extends('layouts.master')
@section('title', trans('all.menu_mesin'))
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

  @if(Session::get('alert'))
      <script>
          $(document).ready(function() {
              alertSuccess("{!! Session::get("alert") !!}");
              return false;
          });
      </script>
  @endif
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_mesin') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.menu_mesin') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
              <li><a href="{{ url('datainduk/absensi/mesin') }}">{{ trans('all.menu_mesin') }}</a></li>
              <li class="active"><a data-toggle="tab" href="{{ url('datainduk/absensi/fingerprintconnector') }}">{{ trans('all.fingerprintconnector') }}</a></li>
          </ul><br>
          <table width="100%">
              <tr>
                  @if(strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 't') !== false || strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 'm') !== false)
                      <td>
                          <a href="fingerprintconnector/create" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('fingerprintconnector/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 'm') !== false)
                        <td class="opsi3"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="alamat"><b>{{ trans('all.nama') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.username') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.pushapi') }}</b></td>
                    <td class="opsi3"><b>{{ trans('all.intervalceksync') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.syncdatapada') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.cleardatapada') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.lastsync') }}</b></td>
                    <td class="opsi1"><center><b>{{ trans('all.status') }}</b></center></td>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

@stop

@push('scripts')
<script>
function resetPassword(id){
    alertConfirm('{{ trans('all.resetkatasandi') }} ?',
        function(){
            window.location.href='{{ url('datainduk/absensi/fingerptintconnector') }}/'+id+'/resetpassword';
        },
        function(){},
        '{{ trans('all.ya') }}','{{ trans('all.tidak') }}'
    );
}

function sinkronisasi(pushapi){
    alertConfirm('{{ trans('all.sinkronisasidata') }} ?',
        function(){
            var url = pushapi+'/requestsync';
            $.ajax({
                type: "GET",
                url: url,
                data: '',
                cache: false,
                success: function (data) {
                    //console.log(data);
                    if(data['status'] == 'OK'){
                        setTimeout(function() { alertSuccess('{{ trans('all.sinkronisasisukses') }}'); },200);
                    }else{
                        setTimeout(function() { alertError(data['status']); },200);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    setTimeout(function() { alertError('{{ trans('all.terjadigangguan') }}'); },200);
                    //alert(thrownError);
                }
            });
        },
        function(){},
        '{{ trans('all.ya') }}','{{ trans('all.tidak') }}'
    );
}

@if(strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 'm') !== false)
    var ordercolumn = 1;
@else
    var ordercolumn = 0;
@endif

$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("datainduk/absensi/fingerprintconnector/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->fingerprintconnector, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'nama', name: 'nama' },
            { data: 'username', name: 'username' },
            { data: 'keterangan', name: 'keterangan' },
            { data: 'pushapi', name: 'pushapi' },
            { data: 'intervalceksync', name: 'intervalceksync' },
            { data: 'sync_data_pada', name: 'sync_data_pada' },
            { data: 'clear_data_pada', name: 'clear_data_pada' },
            { data: 'lastsync', name: 'lastsync',
                render: function (data) {
                    if(data != null){
                        var ukDateTime = data.split(' ');
                        var ukDate = ukDateTime[0].split('-');
                        return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                    }else{
                        return data;
                    }
                }
            },
            { data: 'status', name: 'status' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush