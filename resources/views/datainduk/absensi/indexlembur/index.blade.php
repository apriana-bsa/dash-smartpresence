@extends('layouts.master')
@section('title', trans('all.indexlembur'))
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
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.indexlemburdanjamkerja') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.indexlemburdanjamkerja') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
              <li><a href="{{ url('datainduk/absensi/indexjamkerja') }}">{{ trans('all.jamkerja') }}</a></li>
              <li class="active"><a href="{{ url('datainduk/absensi/indexlembur') }}">{{ trans('all.lembur') }}</a></li>
          </ul>
          <br>
          <table width="100%">
              <tr>
                  @if(strpos(Session::get('hakakses_perusahaan')->mesin, 't') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'm') !== false)
                      <td style="float:left">
                          <a href="{!! url('datainduk/absensi/indexlembur/create') !!}" class="btn btn-primary pull-left"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('indexlembur/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->mesin, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="opsi4"><b>{{ trans('all.jenishari') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.berlakumulai') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.index') }}</b></td>
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
@if(strpos(Session::get('hakakses_perusahaan')->mesin, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'm') !== false)
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
            url: '{!! url("datainduk/absensi/indexlembur/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->mesin, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'nama', name: 'nama' },
            { data: 'jenishari', name: 'jenishari' },
            { data: 'berlakumulai', name: 'berlakumulai',
                render: function (data) {
                    var ukDate = data.split('-');
                    return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0];
                }
            },
            { data: 'index', name: 'index' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush