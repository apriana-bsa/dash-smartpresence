@extends('layouts.master')
@section('title', trans('all.ijintidakmasuk'))
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
      <h2>{{ trans('all.ijintidakmasuk') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.ijintidakmasuk') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <table width="100%">
              <tr>
                  <td style="float:left">
                      <form action="{{ url('datainduk/abensi/ijintidakmasukfilter') }}" method="post">
                          {{ csrf_field() }}
                          <select name="tahun" id="tahun" class="form-control" onchange="this.form.submit()" style="float:left">
                              <option value="{{ $tahun->tahun1 }}" @if($tahunterpilih == $tahun->tahun1) selected @endif>{{ $tahun->tahun1 }}</option>
                              <option value="{{ $tahun->tahun2 }}" @if($tahunterpilih == $tahun->tahun2) selected @endif>{{ $tahun->tahun2 }}</option>
                              <option value="{{ $tahun->tahun3 }}" @if($tahunterpilih == $tahun->tahun3) selected @endif>{{ $tahun->tahun3 }}</option>
                              <option value="{{ $tahun->tahun4 }}" @if($tahunterpilih == $tahun->tahun4) selected @endif>{{ $tahun->tahun4 }}</option>
                              <option value="{{ $tahun->tahun5 }}" @if($tahunterpilih == $tahun->tahun5) selected @endif>{{ $tahun->tahun5 }}</option>
                          </select>
                      </form>
                  </td>
                  @if(strpos(Session::get('hakakses_perusahaan')->ijintidakmasuk, 't') !== false || strpos(Session::get('hakakses_perusahaan')->ijintidakmasuk, 'm') !== false)
                      <td width="100px">
                          <a href="ijintidakmasuk/create" class="btn btn-primary pull-right"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  {{-- <td width="100px">
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('datainduk/absensi/ijintidakmasukexcel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td> --}}
              </tr>
          </table>
          <br>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    <td class="nama"><b>{{ trans('all.pegawai') }}</b></td>
                    <td class="alamat"><b>{{ trans('all.tanggal') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.alasan') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                    <td class="opsi1"><center><b>{{ trans('all.status') }}</b></center></td>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal pegawai-->
  <a href="" id="showmodalpegawai" data-toggle="modal" data-target="#modalpegawai" style="display:none"></a>
  <div class="modal modalpegawai fade" id="modalpegawai" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">

          </div>
      </div>
  </div>
  <!-- Modal pegawai-->

@stop

@push('scripts')
<script>
window.detailpegawai=(function(idpegawai){
    $("#showmodalpegawai").attr("href", "");
    $("#showmodalpegawai").attr("href", "{{ url('detailpegawai') }}/"+idpegawai);
    $('#showmodalpegawai').trigger('click');
    return false;
});

$('body').on('hidden.bs.modal', '.modalpegawai', function () {
    $(this).removeData('bs.modal');
    $("#" + $(this).attr("id") + " .modal-content").empty();
    $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
});

$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("datainduk/absensi/ijintidakmasuk/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'nama', name: 'nama' },
            { data: 'tanggalawal', name: 'tanggalawal' },
            { data: 'alasan', name: 'alasan' },
            { data: 'keterangan', name: 'keterangan' },
            { data: 'status', name: 'status' }
        ],
        order: [[2, 'desc']]
    });
});
</script>
@endpush