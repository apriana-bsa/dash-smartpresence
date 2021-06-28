@extends('layouts.master')
@section('title', trans('all.customdashboard'))
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
      <h2>{{ trans('all.customdashboard') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.pengaturan') }}</li>
        <li class="active"><strong>{{ trans('all.node') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
              <li><a href="{{ url('pengaturan/customdashboard') }}">{{ trans('all.customdashboard') }}</a></li>
              <li class="active"><a href="{{ url('pengaturan/customdashboardnode') }}">{{ trans('all.node') }}</a></li>
              <li><a href="{{ url('pengaturan/customdashboardemail') }}">{{ trans('all.email') }}</a></li>
          </ul>
          <br>
          <table width="100%">
              <tr>
                  @if(strpos(Session::get('hakakses_perusahaan')->customdashboard, 't') !== false || strpos(Session::get('hakakses_perusahaan')->customdashboard, 'm') !== false || strpos(Session::get('hakakses_perusahaan')->customdashboard, 'm') !== false)
                      <td>
                          <a href="{!! url('pengaturan/customdashboardnode/create') !!}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('pengaturan/customdashboardnode/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->customdashboard, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->customdashboard, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->customdashboard, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="nama"><b>{{ trans('all.judul') }}</b></td>
                    <td class="opsi1"><center><b>{{ trans('all.icon') }}</b></center></td>
                    <td class="opsi1"><center><b>{{ trans('all.warna') }}</b></center></td>
                    <td class="nama"><b>{{ trans('all.query_jenis') }}</b></td>
                    <td class="nama"><b>{{ trans('all.query_kehadiran_data') }}</b></td>
                    <td class="nama"><b>{{ trans('all.query_kehadiran_if') }}</b></td>
                    <td class="nama"><b>{{ trans('all.query_kehadiran_group') }}</b></td>
                    <td class="nama"><b>{{ trans('all.query_kehadiran_periode') }}</b></td>
                    <td class="nama"><b>{{ trans('all.query_master_data') }}</b></td>
                    <td class="nama"><b>{{ trans('all.query_master_if') }}</b></td>
                    <td class="nama"><b>{{ trans('all.query_master_group') }}</b></td>
                    <td class="nama"><b>{{ trans('all.query_master_periode') }}</b></td>
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
$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("pengaturan/customdashboardnode/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->customdashboard, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->customdashboard, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->customdashboard, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'nama', name: 'nama' },
            { data: 'judul', name: 'judul' },
            { data: 'icon', name: 'icon', orderable: false },
            { data: 'warna', name: 'warna', orderable: false },
            { data: 'query_jenis', name: 'query_jenis' },
            { data: 'query_kehadiran_data', name: 'query_kehadiran_data' },
            { data: 'query_kehadiran_if', name: 'query_kehadiran_if' },
            { data: 'query_kehadiran_group', name: 'query_kehadiran_group' },
            { data: 'query_kehadiran_periode', name: 'query_kehadiran_periode' },
            { data: 'query_master_data', name: 'query_master_data' },
            { data: 'query_master_if', name: 'query_master_if' },
            { data: 'query_master_group', name: 'query_master_group' },
            { data: 'query_master_periode', name: 'query_master_periode' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush