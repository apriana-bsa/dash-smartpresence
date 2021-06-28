@extends('layouts.master')
@section('title', trans('all.laporankomponenmaster'))
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
      <h2>{{ trans('all.komponenmaster') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li>{{ trans('all.custom') }}</li>
        <li class="active"><strong>{{ trans('all.komponenmaster') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
              <li class="active"><a href="{{ url('laporan/custom/kelompok') }}">{{ trans('all.kelompok') }}</a></li>
              <li><a href="{{ url('laporan/custom/komponenmastergroup') }}">{{ trans('all.komponenmastergrup') }}</a></li>
          </ul>
          <br>
          <table width="100%">
              <tr>
{{--                  @if(strpos(Session::get('hakakses_perusahaan')->laporan, 't') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'm') !== false)--}}
                        <td style="float:left">
                            <a href="{!! url('laporan/custom/kelompok/create') !!}" class="btn btn-primary pull-left"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                        </td>
{{--                  @endif--}}
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('laporan/custom/kelompok/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
{{--                    @if(strpos(Session::get('hakakses_perusahaan')->laporan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'm') !== false)--}}
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
{{--                    @endif--}}
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="opsi3"><center><b>{{ trans('all.jenis') }}</b></center></td>
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
{{--@if(strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'm') !== false)--}}
{{--    var ordercolumn = 1;--}}
{{--@else--}}
{{--    var ordercolumn = 0;--}}
{{--@endif--}}
var ordercolumn = 1;
$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("laporan/custom/kelompok/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
{{--            @if(strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'm') !== false)--}}
                { data: 'action', name: 'action', orderable: false, searchable: false },
{{--            @endif--}}
            { data: 'nama', name: 'nama' },
            { data: 'jenis', name: 'jenis' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush