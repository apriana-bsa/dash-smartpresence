@extends('layouts.master')
@section('title', trans('all.atributvariable'))
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
      <h2>{{ trans('all.atributvariable') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li class="active"><strong>{{ trans('all.atributvariable') }}</strong></li>
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
                  @if(strpos(Session::get('hakakses_perusahaan')->atribut, 't') !== false || strpos(Session::get('hakakses_perusahaan')->atribut, 'm') !== false)
                      <td>
                          <a href="{!! url('datainduk/pegawai/atributvariable/create') !!}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('atributvariable/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->atribut, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->atribut, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->atribut, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="nama"><b>{{ trans('all.atribut') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.kode') }}</b></td>
                    <td class="opsi1"><center><b>{{ trans('all.penting') }}</b></center></td>
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
@if(strpos(Session::get('hakakses_perusahaan')->atribut, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->atribut, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->atribut, 'm') !== false)
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
            url: '{!! url("datainduk/pegawai/atributvariable/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->atribut, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->atribut, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->atribut, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'atribut', name: 'atribut' },
            { data: 'kode', name: 'kode' },
            { data: 'penting', name: 'penting' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush