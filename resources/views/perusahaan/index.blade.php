@extends('layouts/master')
@section('title', trans('all.perusahaan'))
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
      <h2>{{ trans('all.perusahaan') }}</h2>
      <ol class="breadcrumb">
        <li class="active"><strong>{{ trans('all.perusahaan') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <a href="{!! url('perusahaan/create') !!}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
        <button class="btn btn-primary pull-right" onclick="return ke('{{ url('perusahaan/excel/download') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.kode') }}</b></td>
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
function konfirmasi(){
    alertWarning('{{ trans('all.perusahaanmasihdalamproseskonfirmasi') }}');
}

$(function() {
    $('.datatable').DataTable({
        responsive: true,
        bStateSave: true,
        scrollX: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! url("perusahaan/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'nama', name: 'nama' },
            { data: 'kode', name: 'kode' },
            { data: 'status', name: 'status' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush