@extends('layouts.master')
@section('title', trans('all.menu_pegawai'))
@section('content')

  <script>
  $(document).ready(function() {
    @if(Session::get('message'))
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
    @endif

    $("#kembali").click(function(){
      window.location.href="../../pegawai";
    });
  });

  function hapusfacesample(id, type)
  {
    if(type == 'semua'){
      alertConfirm("{{ trans('all.apakahyakinakanmenghapussemuafacesample') }} ?",
        function(){
          window.location.href="{{ url('datainduk/pegawai/facesample/deleteall') }}/"+id;
        }
      );
    }else{
      alertConfirm("{{ trans('all.apakahyakinakanmenghapusfacesampleini') }} ?",
        function(){
          window.location.href="{{ url('datainduk/pegawai/facesample/delete') }}/"+id;
        }
      );
    }
  }
  </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_pegawai') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.menu_pegawai') }}</li>
        <li class="active"><strong>{{ trans('all.'.$jenis) }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">

      <ul class="nav nav-tabs">
          <li @if($jenis == 'facesample') class="active" @endif><a href="{{ url('datainduk/pegawai/pegawai/facesample/'.$idpegawai) }}">{{ trans('all.facesample') }}</a></li>
          <li @if($jenis == 'fingerprint') class="active" @endif><a href="{{ url('datainduk/pegawai/pegawai/fingerprint/'.$idpegawai) }}">{{ trans('all.fingerprint') }}</a></li>
      </ul>
      <br>

      <div class="col-lg-12">
        <div class="ibox float-e-margins">

          <div class="ibox-content row">
            <h2>{{ trans('all.'.$jenis).' '.$namapegawai }}</h2>
            @if($jenis == 'facesample')
                @if(count($data) > 0)
                  @foreach($data as $key)
                      <div class="col-md-2" style="width: 120px">
                        <a href="{{ url('getfacesample/'.$key->id) }}" title="{{ $namapegawai }}" data-gallery="">
                            <img src="{{ url('getfacesample/'.$key->id.'/_thumb') }}" width=100 height=100 style="border-radius: 50%"><br><br>
                        </a>
                        @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
                            @if($bolehhapus == true)
                            <center><button onclick="return hapusfacesample({{ $key->id }},'satu')" class="btn btn-danger"><i class="fa fa-trash"></i></button></center>
                            @endif
                        @endif
                      </div>
                  @endforeach
                  <div class="col-md-12" style="margin-top:15px">
                    @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
                      @if($bolehhapus == true)
                        <button onclick="return hapusfacesample({{ $idpegawai }},'semua')" class="btn btn-primary"><i class="fa fa-trash"></i>&nbsp;&nbsp;{{ trans('all.hapussemuafacesample') }}</button>&nbsp;&nbsp;
                      @endif
                    @endif
                    <button id="kembali" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                  </div>
                @else
                  <center>{{trans('all.nodata') }}</center><br>
                  <center><button id="kembali" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button></center>
                @endif
            @else
                  <table class="table datatable table-striped table-condensed">
                      <thead>
                      <tr>
                        @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
                          <td class="opsi1"><b><center>{{ trans('all.manipulasi') }}</center></b></td>
                        @endif
                        <td class="opsi5"><b>{{ trans('all.algoritma') }}</b></td>
                        <td class="opsi5"><b>{{ trans('all.fingerid') }}</b></td>
                        <td class="opsi5"><b>{{ trans('all.size') }}</b></td>
                        <td class="opsi5"><b>{{ trans('all.valid') }}</b></td>
                        <td class="nama"><b>{{ trans('all.template') }}</b></td>
                        <td class="opsi5"><b>{{ trans('all.checksum') }}</b></td>
                        <td class="opsi2"><b>{{ trans('all.terhapus') }}</b></td>
                      </tr>
                      </thead>
                      <tbody>
                      @foreach($data as $key)
                          <tr>
                            @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
                              <td>
                                  <center>
                                      <a title="{{ trans('all.ubahstatus') }}" href="{{ url('fingerprint/ubahstatus/'.$key->id) }}">
                                          @if($key->deleted != '')
                                              <i class="fa fa-refresh" style="color:#ed5565"></i>
                                          @else
                                              <i class="fa fa-trash" style="color:#ed5565"></i>
                                          @endif
                                      </a>
                                  </center>
                              </td>
                            @endif
                            <td>{{ $key->algoritma }}</td>
                            <td>{{ $key->finger_id }}</td>
                            <td>{{ $key->size }}</td>
                            <td>{{ $key->valid }}</td>
                            <td>{{ $key->template }}</td>
                            <td>{{ $key->checksum }}</td>
                            <td>@if($key->deleted != '') {{ trans('all.ya') }} @else {{ trans('all.tidak') }} @endif</td>
                          </tr>
                      @endforeach
                      </tbody>
                  </table>
                  <button id="kembali" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
            @endif

          </div>
        </div>
      </div>

  </div>

@stop

@if($jenis == 'fingerprint')
    @push('scripts')
    <script>
        $(function() {
            $('.datatable').DataTable({
                scrollX: true,
                bStateSave: true,
                language: lang_datatable,
                @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
                    aoColumnDefs: [
                        { 'bSortable': false,
                            'aTargets': [ 0 ]
                        }
                    ],
                    order: [[1, 'asc']]
                @else
                    order: [[0, 'asc']]
                @endif
            });
        });
    </script>
    @endpush
@endif