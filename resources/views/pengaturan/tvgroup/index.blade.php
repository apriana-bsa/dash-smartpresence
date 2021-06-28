@extends('layouts.master')
@section('title', trans('all.tvgroup'))
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
      <h2>{{ trans('all.tvgroup') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.pengaturan') }}</li>
        <li class="active"><strong>{{ trans('all.node') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
              <li><a href="{{ url('pengaturan/tv') }}">{{ trans('all.tv') }}</a></li>
              <li class="active"><a href="{{ url('pengaturan/tvgroup') }}">{{ trans('all.tvgroup') }}</a></li>
          </ul>
          <br>
          <table width="100%">
              <tr>
                  @if(strpos(Session::get('hakakses_perusahaan')->pengaturan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengaturan, 'm') !== false)
                      <td>
                          <a href="{!! url('pengaturan/tvgroup/create') !!}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('pengaturan/tvgroup/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->pengaturan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengaturan, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="opsi5"><b>{{ trans('all.nama') }}</b></td>
                    <td class="nama"><b>{{ trans('all.judul') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.baris') }}</b></td>
                    <td class="alamat"><b>{{ trans('all.warna') }}</b></td>
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
$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("pengaturan/tvgroup/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->pengaturan, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pengaturan, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'nama', name: 'nama' },
            { data: 'judul', name: 'judul' },
            { data: 'baris', name: 'baris1_label' },
            { data: 'warna', name: 'warna_teks' },
            { data: 'atribut', name: 'atribut' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush