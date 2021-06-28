@extends('layouts.master')
@section('title', trans('all.menu_mesin'))
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

  @if(Session::get('alert'))
      <script>
          $(document).ready(function() {
              alertSuccess("{{ Session::get("alert") }}");
              return false;
          });
      </script>
  @endif
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_mesin') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.menu_mesin') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
              <li class="active"><a href="{{ url('datainduk/absensi/mesin') }}">{{ trans('all.menu_mesin') }}</a></li>
              <li><a href="{{ url('datainduk/absensi/fingerprintconnector') }}">{{ trans('all.fingerprintconnector') }}</a></li>
          </ul><br>
          <table width="100%">
              <tr>
                  @if(strpos(Session::get('hakakses_perusahaan')->mesin, 't') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'm') !== false)
                      <td>
                          <a href="mesin/create" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('mesin/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
                  </td>
              </tr>
          </table>
          <p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content" data-toggle='popover_kode' data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><button class="close">&times;</button>{{ trans('onboarding.mesin_kode') }}</div></div>' data-content='content'>
            <table width=100% class="table datatable table-striped table-condensed table-hover" data-toggle='popover_sambung' data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><button class="close">&times;</button>{{ trans('onboarding.mesin_sambung') }}</div></div>' data-content='content'>
              <thead data-toggle='popover_berhasil' data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><button class="close">&times;</button>{{ trans('onboarding.mesin_berhasil') }}</div></div>' data-content='content'>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->mesin, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'm') !== false)
                        <td class="opsi3"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="nama"><b>{{ trans('all.atribut') }}</b></td>
                    <td class="opsi4"><b>{{ trans('all.jenis') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.deviceid') }}</b></td>
                    <td class="opsi3"><b>{{ trans('all.cekjamserver') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.utc') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.lastsync') }}</b></td>
                    <td class="opsi1"><center><b>{{ trans('all.status') }}</b></center></td>
                    <td class="opsi2"><b>ID</b></td>
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
function putusSambungan(idmesin){
    alertConfirm('{{ trans('all.putussambungan') }}?',
        function(){
            window.location.href='{{ url('datainduk/absensi/mesin') }}/'+idmesin+'/putussambungan';
        },
        function(){},
        '{{ trans('all.ya') }}','{{ trans('all.tidak') }}'
    );
}

$(function () {
    @if($onboarding)
    	$('[data-toggle="popover_sambung"]').popover({
        	placement : 'auto left',
        	trigger : 'manual',
        });
        $('[data-toggle="popover_berhasil"]').popover({
        	placement : 'auto top',
        	trigger : 'manual',
        });
        $('[data-toggle="popover_kode"]').popover({
        	placement : 'auto top',
        	trigger : 'manual',
        });
        $('[data-toggle="popover_sambung"]').popover("show");	
        $('[data-toggle="popover_berhasil"]').popover("show");
        
        @if(Session::get('alert'))
            $('[data-toggle="popover_sambung"]').popover("hide");	
            $('[data-toggle="popover_berhasil"]').popover("hide");
            $('[data-toggle="popover_kode"]').popover("show");
        @endif

        $($('[data-toggle="popover_sambung"]').next()).css({"top": "180px", "left": "90px", "display": "block"});
        $($('[data-toggle="popover_berhasil"]').next()).css({"top": "-20px", "left": "500px", "display": "block"});
        $($('[data-toggle="popover_kode"]').next()).css({"top": "50px", "left": "625px", "display": "block"});
        $($('[data-toggle="popover_sambung"]').next().children()).css({"top": "50%"});
    @endif
    	$(document).on("click", ".popover .close" , function(){
        	$(this).parents('.popover').popover('hide');
    	});
  })

function pengaturanFingerPrint(id){
    window.location.href='{{ url('/mesin') }}/'+id+'/fingerprint';
}

@if(strpos(Session::get('hakakses_perusahaan')->mesin, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'm') !== false)
    var ordercolumn = 1;
@else
    var ordercolumn = 0;
@endif
$(function() {
    $('.datatable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            @if ($onboarding)
                url: '{!! url("datainduk/absensi/mesin/index-data?onboarding=true") !!}',
            @else
                url: '{!! url("datainduk/absensi/mesin/index-data") !!}',
            @endif
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->mesin, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->mesin, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'nama', name: 'nama' },
            { data: 'atribut', name: 'atribut' },
            { data: 'jenis', name: 'jenis' },
            { data: 'deviceid', name: 'deviceid' },
            { data: 'cekjamserver', name: 'cekjamserver' },
            { data: 'utcbaru', name: 'utcbaru' },
            { data: 'lastsync', name: 'lastsync',
                render: function (data) {
                    if(data !== ''){
                        var ukDateTime = data.split(' ');
                        var ukDate = ukDateTime[0].split('-');
                        return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                    }else{
                        return data;
                    }
                }
            },
            { data: 'status', name: 'status' },
            { data: 'id', name: 'id', visible: false }
        ],
        @if ($onboarding)
            order: [[9, 'desc']],
            bStateSave: false,
        @else
            order: [[ordercolumn, 'asc']],
            bStateSave: true,
        @endif
        
    });
});
</script>
@endpush