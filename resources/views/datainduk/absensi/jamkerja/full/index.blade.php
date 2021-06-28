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
        <li class="active"><strong>{{ trans('all.full') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 't') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
          <button onclick="return ke('{!! url( $onboarding ? 'datainduk/absensi/jamkerja/'.$idjamkerja.'/full/create?onboarding=true' : 'datainduk/absensi/jamkerja/'.$idjamkerja.'/full/create' ) !!}')" class="btn btn-primary"
            id="popover-botton-add-data" data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&nbsp;&times;</a>{{ trans('onboarding.button_tambahdata_fulltime_jamkerja') }}</div></div>' data-toggle="popover-botton-add-data" data-content="content"/>
            <i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}
           </button>&nbsp;&nbsp;
        @endif
        <button class="btn btn-primary pull-right" onclick="return ke('{{ url('jamkerjafull/excel/'.$idjamkerja) }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
        <button onclick="return ke('{!! url('datainduk/absensi/jamkerja/') !!}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button><p></p>
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
                      <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="opsi5"><b>{{ trans('all.berlakumulai') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                </tr>
                <div id="popover-jamkerja-detail"><div data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&nbsp;&times;</a>{{ trans('onboarding.detail_fulltime_jamkerja') }}</div></div>' data-toggle="popover-jamkerja-detail" data-content="content"></div>
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
            url: '{!! url("datainduk/absensi/jamkerja/$idjamkerja/full/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'berlakumulai', name: 'berlakumulai' },
            { data: 'keterangan', name: 'keterangan' }
        ],
        order: [[ordercolumn, 'asc']]
    })
    .on('draw', function() {

        //ref: https://www.tutorialrepublic.com/codelab.php?topic=faq&file=bootstrap-add-close-button-to-popover
        //start tooltip
        @if($onboarding)
            var table = $('.datatable').DataTable();
            if ( !table.data().any() ) {
                $('[data-toggle="popover-jamkerja-detail"]').popover('hide');
            }
        @endif
        //end tooltip

    });
});
@if($onboarding)
    $(document).ready(function(){
        $('[data-toggle="popover-botton-add-data"]').popover({
            placement : 'auto top',
            trigger : 'manual',
        });

        $('[data-toggle="popover-jamkerja-detail"]').popover({
            placement : 'auto top',
            trigger : 'manual',
        });

        @if(Session::get('success_add_data') == '1')
            $('[data-toggle="popover-botton-add-data"]').popover('hide');
            $('[data-toggle="popover-jamkerja-detail"]').popover('show');
        @else
            $('[data-toggle="popover-botton-add-data"]').popover('show');
            $('[data-toggle="popover-jamkerja-detail"]').popover('hide');
        @endif

        $(document).on("click", ".popover .close" , function(){
            $(this).parents('.popover').popover('hide');
        });

        $('#popover-jamkerja-detail .popover').css('top', '10%');
        $('#popover-jamkerja-detail .popover').css('left', '270px');
        $('#popover-jamkerja-detail .arrow').css('left', '30%');

        $(document).on("click", ".popover .close" , function(){
            $(this).parents('.popover').popover('hide');
        });
    });
@endif

</script>
@endpush