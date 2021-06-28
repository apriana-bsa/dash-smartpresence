@extends('layouts.master')
@section('title', trans('all.menu_alasantidakmasuk'))
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
      <h2>{{ trans('all.menu_alasantidakmasuk') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.catatankehadiran') }}</li>
        <li class="active"><strong>{{ trans('all.menu_alasantidakmasuk') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <table width="100%">
              <tr>
                  @if(strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 't') !== false || strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'm') !== false)
                      <td style="float:left">
                          <a href="alasantidakmasuk/create" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                      @if($totaldata > 0)
                          <td style="float:left;margin-left:10px">
                              <a href="{!! url('urutan/alasantidakmasuk') !!}" class="btn btn-primary pull-left"><i class="fa fa-sort"></i>&nbsp;&nbsp;{{ trans('all.urutan') }}</a>
                          </td>
                      @endif
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('alasantidakmasuk/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="opsi1"><b>{{ trans('all.urutan') }}</b></td>
                    <td class="nama"><b>{{ trans('all.alasan') }}</b></td>
                    <td class="opsi3"><center><b>{{ trans('all.kategori') }}</b></center></td>
                    <td class="opsi5"><center><b>{{ trans('all.hitunguangmakan') }}</b></center></td>
                    <td class="opsi1"><center><b>{{ trans('all.digunakan') }}</b></center></td>
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
@if(strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'm') !== false)
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
            url: '{!! url("datainduk/alasan/alasantidakmasuk/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->alasantidakmasuk, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'urutan', name: 'urutan' },
            { data: 'alasan', name: 'alasan' },
            { data: 'kategori', name: 'kategori' },
            { data: 'hitunguangmakan', name: 'hitunguangmakan' },
            { data: 'digunakan', name: 'digunakan' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush