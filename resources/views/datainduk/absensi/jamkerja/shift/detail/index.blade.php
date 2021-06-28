@extends('layouts.master')
@section('title', trans('all.shift'))
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
      <h2>{{ trans('all.shift')." (".$jamkerjashift.")" }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.jamkerja') }}</li>
        <li>{{ trans('all.shift') }}</li>
        <li class="active"><strong>{{ trans('all.detail') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 't') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
          <button onclick="return ke('detail/create')" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</button>&nbsp;&nbsp;
        @endif
        <button class="btn btn-primary pull-right" onclick="return ke('{{ url('jamkerjashiftdetail/excel/'.$idjamkerjashift) }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
        <button onclick="return ke('{!! url('datainduk/absensi/jamkerja/'.$idjamkerja.'/shift/') !!}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button><p></p><p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
                      <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="nama"><b>{{ trans('all.berlakumulai') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.waktukerja') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.waktuistirahat') }}</b></td>
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
@if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
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
            url: '{!! url("datainduk/absensi/jamkerja/$idjamkerja/shift/$idjamkerjashift/detail/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'berlakumulai', name: 'berlakumulai' },
            { data: 'waktukerja', name: 'waktukerja' },
            { data: 'waktuistirahat', name: 'waktuistirahat' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush