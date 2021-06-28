@extends('layouts.master')
@section('title', trans('all.pengelola'))
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
      <h2>{{ trans('all.pengelola') }}</h2>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class='pull-right'>
            <button class="btn btn-primary" onclick="return ke('{{ url('pengelolas/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
          </div>
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->pengelola, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengelola, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pengelola, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="alamat"><b>{{ trans('all.email') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.hakakses') }}</b></td>
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
@if(strpos(Session::get('hakakses_perusahaan')->pengelola, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengelola, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pengelola, 'm') !== false)
    var ordercolumn = 1;
@else
    var ordercolumn = 0;
@endif
$(function() {
    $('.datatable').DataTable({
        scrollX: true,
        bStateSave: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! url("pengelola/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->pengelola, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengelola, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pengelola, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'nama', name: 'nama' },
            { data: 'email', name: 'email' },
            { data: 'hakakses', name: 'hakakses' },
            { data: 'status', name: 'status' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush