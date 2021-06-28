@extends('layouts.master')
@section('title', trans('all.komponenmaster'))
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
  <style>
  .btn-primary.active.focus, .btn-primary.active:focus, .btn-primary.active:hover, .btn-primary:active.focus, .btn-primary:active:focus, .btn-primary:active:hover, .open>.dropdown-toggle.btn-primary.focus, .open>.dropdown-toggle.btn-primary:focus, .open>.dropdown-toggle.btn-primary:hover {
      background-color: #18a689;
      border-color: #18a689;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.kelompok') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li>{{ trans('all.custom') }}</li>
        <li>{{ trans('all.kelompok') }}</li>
        <li class="active"><strong>{{ $kelompok }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
            <li class="active"><a href="{{ url('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster') }}">{{ trans('all.komponenmaster') }}</a></li>
            <li><a href="{{ url('laporan/custom/kelompok/'.$idlaporankelompok.'/atribut') }}">{{ trans('all.atributnilai') }}</a></li>
        </ul>
        <br>
          <table width="100%">
              <tr>
{{--                  @if(strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 't') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'm') !== false)--}}
                      <td style="float:left">
                          <a href="{!! url('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster/create') !!}" class="btn btn-primary pull-left"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                    @if($totaldata > 0)
                          <td style="float:left;margin-left:10px">
                              {{--<a href="{!! url('urutan/laporankomponenmaster') !!}" class="btn btn-primary pull-left"><i class="fa fa-sort"></i>&nbsp;&nbsp;{{ trans('all.urutan') }}</a>--}}
                              <div class="input-group" style="margin-bottom:10px">
                                  <div class="input-group-prepend">
                                      <button data-toggle="dropdown" class="btn btn-primary dropdown-toggle" type="button"><i class="fa fa-sort"></i>&nbsp;&nbsp;{{ trans('all.urutan') }}</button>
                                      <ul class="dropdown-menu" style="left:-67px !important">
                                          <li><a href="#" onclick="return ke('{!! url('urutan/laporankomponenmasterurutanperhitungan/'.$idlaporankelompok) !!}')">{{ trans('all.digunakan') }}</a></li>
                                          <li><a href="#" onclick="return ke('{!! url('urutan/laporankomponenmasterurutantampilan/'.$idlaporankelompok) !!}')">{{ trans('all.tampilan') }}</a></li>
                                      </ul>
                                  </div>
                              </div>
                          </td>
                    @endif
{{--                  @endif--}}
                  <td style="padding-left:10px;float:left">
                      <button onclick="return ke('{!! url('laporan/custom/kelompok') !!}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                  </td>
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('laporan/custom/kelompok/'.$idlaporankelompok.'/komponenmaster/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
{{--                    @if(strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'm') !== false)--}}
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
{{--                    @endif--}}
                    <td class="urutan"><b>{{ trans('all.urutan') }}</b></td>
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="kode"><b>{{ trans('all.kode') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.tipekolom') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.tipedata') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.carainput') }}</b></td>
                    <td class="opsi1"><center><b>{{ trans('all.digunakan') }}</b></center></td>
                    <td class="opsi1"><center><b>{{ trans('all.tampilkan') }}</b></center></td>
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
{{--@if(strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'm') !== false)--}}
{{--    var ordercolumn = 1;--}}
{{--@else--}}
{{--    var ordercolumn = 0;--}}
{{--@endif--}}
var ordercolumn = 1;
$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("laporan/custom/kelompok/".$idlaporankelompok."/komponenmaster/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
{{--            @if(strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->laporankomponenmaster, 'm') !== false)--}}
                { data: 'action', name: 'action', orderable: false, searchable: false },
{{--            @endif--}}
            { data: 'urutan', name: 'urutan' },
            { data: 'nama', name: 'nama' },
            { data: 'kode', name: 'kode' },
            { data: 'tipekolom', name: 'tipekolom' },
            { data: 'tipedata', name: 'tipedata' },
            { data: 'carainput', name: 'carainput' },
            { data: 'digunakan', name: 'digunakan' },
            { data: 'tampilkan', name: 'tampilkan' }
        ],
        order: [[ordercolumn, 'asc']]
    });
});
</script>
@endpush