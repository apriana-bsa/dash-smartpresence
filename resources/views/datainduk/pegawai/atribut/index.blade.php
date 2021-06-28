@extends('layouts.master')
@section('title', trans('all.atribut'))
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
      <h2>{{ trans('all.atribut') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li class="active"><strong>{{ trans('all.atribut') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
            <li class="active"><a href="{{ url('datainduk/pegawai/atribut') }}">{{ trans('all.atribut') }}</a></li>
            <li><a href="{{ url('datainduk/pegawai/atribut/perlakuanlembur') }}">{{ trans('all.perlakuanlembur') }}</a></li>
        </ul>
        <br>
        <table width="100%">
            <tr>
                @if(strpos(Session::get('hakakses_perusahaan')->atribut, 't') !== false || strpos(Session::get('hakakses_perusahaan')->atribut, 'm') !== false)
                    <td>
                        <a href="{!! url('datainduk/pegawai/atribut/create') !!}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                    </td>
                @endif
                <td>
                    <button class="btn btn-primary pull-right" onclick="return ke('{{ url('atribut/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                </td>
            </tr>
        </table>
        <p></p>
        <div class="ibox float-e-margins">

          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover"  data-toggle="popover-atributdone" data-placement="auto top" data-content="{{ trans('onboarding.sukses_tambah_atribut') }}">
              <thead>
                <tr>
                    <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    <td class="nama"><b>{{ trans('all.atribut') }}</b></td>
                    <td class="opsi5"><center><b>{{ trans('all.tampilpadaringkasan') }}</b></center></td>
                    <td class="opsi1"><center><b>{{ trans('all.penting') }}</b></center></td>
                    <td class="opsi5"><center><b>{{ trans('all.jumlahinputan') }}</b></center></td>
                    <td class="nama"><b>{{ trans('all.nilai') }}</b></td>
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
@if(Session::get('success_add_attribut') || $onboarding)
  $(document).ready(function(){
    $('[data-toggle="popover-atributdone"]').popover({
        template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><a class="popover-cancel">&times;</a><div class="popover-content"></div></div>',
        placement : 'auto top',
        trigger : 'manual',
    });
    $('[data-toggle="popover-atributdone"]').popover('show');
    $(document).on("click", ".popover .popover-cancel" , function(){
        $(this).parents('.popover').popover('hide');
    });
  });
@endif
$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: false,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("datainduk/pegawai/atribut/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'atribut', name: 'atribut' },
            { data: 'tampilpadaringkasan', name: 'tampilpadaringkasan' },
            { data: 'penting', name: 'penting' },
            { data: 'jumlahinputan', name: 'jumlahinputan' },
            { data: 'nilai', name: 'atributnilai.nilai' }
        ],
        order: [[0, 'desc']]
    });
});
</script>
@endpush
