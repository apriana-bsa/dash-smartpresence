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
      <h2>{{ trans('all.jamkerja') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.jamkerja') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
              <li class="active"><a href="{{ url('datainduk/absensi/jamkerja') }}">{{ trans('all.jamkerja') }}</a></li>
              <li><a href="{{ url('datainduk/absensi/jamkerjakategori') }}">{{ trans('all.kategorijamkerja') }}</a></li>
              <li><a href="{{ url('datainduk/absensi/jamkerjashiftjenis') }}">{{ trans('all.jenisjamkerjashift') }}</a></li>
          </ul>
          <br>
          <table width="100%">
              <tr>
                  @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 't') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
                      <td>
                          <a href="jamkerja/create" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('jamkerja/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">

          <div class="ibox-content">
            <div id="popover-jamkerja-bt-detail"><div data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&times;</a>{{  trans('onboarding.button_detail_jamkerja') }}</div></div>' data-toggle="popover-jamkerja-bt-detail" data-content="content"/></div>
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.kategori') }}</b></td>
                    <td class="opsi5"><center><b>{{ trans('all.jenis') }}</b></center></td>
                    <td class="opsi1"><b>{{ trans('all.toleransi') }}</b></td>
                    <td class="opsi3"><b>{{ trans('all.acuanterlambat') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.hitunglembursetelah') }}</b></td>
                    <td class="opsi1"><center><b>{{ trans('all.digunakan') }}</b></center></td>
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
        bStateSave: false,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url($onboarding ? "datainduk/absensi/jamkerja/index-data?onboarding=true" : "datainduk/absensi/jamkerja/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'nama', name: 'nama' },
            { data: 'kategori', name: 'kategori' },
            { data: 'jenis', name: 'jenis' },
            { data: 'toleransi', name: 'toleransi' },
            { data: 'acuanterlambat', name: 'acuanterlambat' },
            { data: 'hitunglemburstlh', name: 'hitunglemburstlh' },
            { data: 'digunakan', name: 'digunakan' }
        ],
        order: [[0, 'desc']]
    })
    .on('draw', function() {
        //start tooltip
        @if($onboarding)
            var table = $('.datatable').DataTable();
            if ( !table.data().any() ) {
                $('[data-toggle="popover-jamkerja-bt-detail"]').popover('hide');
            }
        @endif
        //end tooltip
    });

    //start tooltip
    @if($onboarding)
    $(document).ready(function(){
        $('[data-toggle="popover-jamkerja-bt-detail"]').popover({
            placement : 'auto top',
            trigger : 'manual',
        });

        $('[data-toggle="popover-jamkerja-bt-detail"]').popover('show');
        $('#popover-jamkerja-bt-detail .popover').css('left', '2%');
        $('#popover-jamkerja-bt-detail .arrow').css('left', '14%');
        $('#popover-jamkerja-bt-detail .popover').css('top', '150px');

        $(document).on("click", ".popover .close" , function(){
            $(this).parents('.popover').popover('hide');
        });
    });
    @endif
    //end tooltip

});
</script>

@endpush
