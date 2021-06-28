@extends('layouts.master')
@section('title', trans('all.tv'))
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
      <h2>{{ trans('all.tv') }}</h2>
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
              <li class="active"><a href="{{ url('pengaturan/tv') }}">{{ trans('all.tv') }}</a></li>
              <li><a href="{{ url('pengaturan/tvgroup') }}">{{ trans('all.tvgroup') }}</a></li>
          </ul>
          <br>
          <table width="100%">
              <tr>
                  @if(strpos(Session::get('hakakses_perusahaan')->pengaturan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengaturan, 'm') !== false)
                      <td>
                          <a href="{!! url('pengaturan/tv/create') !!}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('pengaturan/tv/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->pengaturan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengaturan, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="alamat"><b>{{ trans('all.nama') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.header') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.layout') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.interval') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.warna') }}</b></td>
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
            url: '{!! url("pengaturan/tv/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->pengaturan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengaturan, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'nama', name: 'nama' },
            { data: 'header_baris1', name: 'header_baris1' },
            { data: 'orientasi', name: 'orientasi' },
            { data: 'interval_refresh_data', name: 'interval_refresh_data' },
            { data: 'warna_background', name: 'warna_background' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush