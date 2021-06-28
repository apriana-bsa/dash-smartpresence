@extends('layouts.master')
@section('title', trans('all.pekerjaanuser'))
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
    {{--<div class="col-lg-10">--}}
      {{--<h2>{{ trans('all.pekerjaanuser') }}</h2>--}}
      {{--<ol class="breadcrumb">--}}
        {{--<li class="active"><strong>{{ trans('all.pekerjaanuser') }}</strong></li>--}}
      {{--</ol>--}}
    {{--</div>--}}
    {{--<div class="col-lg-2">--}}

    {{--</div>--}}
      <div class="col-lg-12">
          <h3 style="margin-top:15px;margin-bottom:0">
              <ul class="nav nav-tabs">
                  <li><a href="{{ url('/') }}">{{ trans('all.beranda') }}</a></li>
                  @if($datacd->tampil_riwayatdashboard == 'y')
                      <li><a href="{{ url('/riwayatberanda') }}">{{ trans('all.riwayat') }}</a></li>
                  @endif
                  <li class="active"><a href="{{ url('pekerjaaninput') }}">{{ trans('all.pekerjaan') }}</a></li>
              </ul>
          </h3>
      </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <table width="100%">
              <tr>
                  @if(strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 't') !== false || strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'e') !== false || strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'm') !== false)
                      <td style="float:left">
                          <a href="{!! url('pekerjaaninput/create') !!}" class="btn btn-primary pull-left"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('pekerjaaninput/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="opsi3"><b>{{ trans('all.tanggal') }}</b></td>
                    <td class="nama"><b>{{ trans('all.pekerjaan') }}</b></td>
                    <td class="nama"><b>{{ trans('all.pegawai') }}</b></td>
                    <td class="nama"><b>{{ trans('all.keterangan') }}</b></td>
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
@if(strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'm') !== false)
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
            url: '{!! url("pekerjaaninput/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pekerjaanuser, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'tanggal', name: 'tanggal',
                render: function (data) {
                    var ukDate = data.split('-');
                    return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0];
                }
            },
            { data: 'pekerjaan', name: 'pekerjaan' },
            { data: 'pegawai', name: 'pegawai' },
            { data: 'keterangan', name: 'keterangan' }
        ],
//        columnDefs: [
//            { className: "dt-right", "targets": [5] }
//        ],
        order: [[ordercolumn, 'desc']]
    });
});
</script>
@endpush