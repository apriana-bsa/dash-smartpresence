@extends('layouts.master')
@section('title', trans('all.batasan'))
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
      <h2>{{ trans('all.batasan') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.lainlain') }}</li>
        <li class="active"><strong>{{ trans('all.batasan') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
          <li><a href="{{ url('datainduk/lainlain/batasanemail') }}">{{ trans('all.email') }}</a></li>
          <li class="active"><a href="{{ url('datainduk/lainlain/batasanatribut') }}">{{ trans('all.atribut') }}</a></li>
        </ul>
        <p></p>
          <table width="100%">
              <tr>
                  @if(strpos(Session::get('hakakses_perusahaan')->batasan, 't') !== false || strpos(Session::get('hakakses_perusahaan')->batasan, 'm') !== false)
                      <td>
                          <a href="{!! url('datainduk/lainlain/batasanatribut/create') !!}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('batasanatribut/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">

          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->batasan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->batasan, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->batasan, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.atribut') }}</b></td>
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
@if(strpos(Session::get('hakakses_perusahaan')->batasan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->batasan, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->batasan, 'm') !== false)
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
            url: '{!! url("datainduk/lainlain/batasanatribut/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->batasan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->batasan, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->batasan, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'namabatasan', name: 'namabatasan' },
            { data: 'atributnilai', name: 'atributnilai' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush