@extends('layouts.master')
@section('title', trans('all.slipgaji'))
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
      <h2>{{ trans('all.slipgaji') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.payroll') }}</li>
        <li class="active"><strong>{{ trans('all.slipgaji') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
            <li class="active"><a href="{{ url('datainduk/payroll/slipgaji') }}">{{ trans('all.slipgaji') }}</a></li>
            <li><a href="{{ url('datainduk/payroll/slipgajiekspor') }}">{{ trans('all.ekspor') }}</a></li>
        </ul>
        <br>
        <table width="100%">
          <tr>
              @if(strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 't') !== false || strpos(Session::get('hakakses_perusahaan')->payrollkomponenmaster, 'm') !== false)
                <td style="float:left">
                    <a href="{!! url('datainduk/payroll/slipgaji/create') !!}" class="btn btn-primary pull-left"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                </td>
              @endif
              <td>
                  <button class="btn btn-primary pull-right" onclick="return ke('{{ url('datainduk/payroll/slipgaji/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
              </td>
          </tr>
        </table>
        <p></p>
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    <td class="alamat"><b>{{ trans('all.berlakumulai') }}</b></td>
                    <td class="nama"><b>{{ trans('all.kelompok') }}</b></td>
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
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
            url: '{!! url("datainduk/payroll/slipgaji/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'berlakumulai', name: 'berlakumulai' },
            { data: 'kelompok', name: 'kelompok' },
            { data: 'nama', name: 'nama' },
            { data: 'keterangan', name: 'keterangan' }
        ],
        order: [[1, 'desc']]
    });
});
</script>
@endpush