@extends('layouts.master')
@section('title', trans('all.jamkerja'))
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
      <h2>{{ trans('all.jamkerja')." (".$jamkerja.")" }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.jamkerja') }}</li>
        <li class="active"><strong>{{ trans('all.shift') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2"></div>
  </div>
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 't') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
          <button onclick="return ke('{!! url('datainduk/absensi/jamkerja/'.$idjamkerja.'/shift/create') !!}')" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</button>&nbsp;&nbsp;
        @endif
        <button class="btn btn-primary pull-right" onclick="return ke('{{ url('jamkerjashift/excel/'.$idjamkerja) }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
        <button onclick="return ke('{!! url('datainduk/absensi/jamkerja/') !!}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button><p></p>
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    <td class="opsi1"><b>{{ trans('all.urutan') }}</b></td>
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="nama"><b>{{ trans('all.jenis') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.berlaku') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.kode') }}</b></td>
                    <td class="opsi1"><b><center>{{ trans('all.digunakan') }}</center></b></td>
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
            url: '{!! url("datainduk/absensi/jamkerja/$idjamkerja/shift/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'urutan', name: 'urutan' },
            { data: 'namashift', name: 'namashift' },
            { data: 'jenis', name: 'jenis' },
            { data: 'keterangan', name: 'keterangan' },
            { data: 'kode', name: 'kode' },
            { data: 'digunakan', name: 'digunakan' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush