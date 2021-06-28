@extends('layouts.master')
@section('title', trans('all.agama'))
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
      <h2>{{ trans('all.agama') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li class="active"><strong>{{ trans('all.agama') }}</strong></li>
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
                  @if(strpos(Session::get('hakakses_perusahaan')->agama, 't') !== false || strpos(Session::get('hakakses_perusahaan')->agama, 'm') !== false)
                        <td style="float:left">
                            <a href="{!! url('datainduk/pegawai/agama/create') !!}" class="btn btn-primary pull-left"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                        </td>
                    @if($totaldata > 0)
                        <td style="float:left;margin-left:10px">
                            <a href="{!! url('urutan/agama') !!}" class="btn btn-primary pull-left"><i class="fa fa-sort"></i>&nbsp;&nbsp;{{ trans('all.urutan') }}</a>
                        </td>
                    @endif
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('agama/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->agama, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->agama, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->agama, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="opsi1"><b>{{ trans('all.urutan') }}</b></td>
                    <td class="nama"><b>{{ trans('all.agama') }}</b></td>
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
@if(strpos(Session::get('hakakses_perusahaan')->agama, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->agama, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->agama, 'm') !== false)
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
            url: '{!! url("datainduk/pegawai/agama/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->agama, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->agama, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->agama, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'urutan', name: 'urutan' },
            { data: 'agama', name: 'agama' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush