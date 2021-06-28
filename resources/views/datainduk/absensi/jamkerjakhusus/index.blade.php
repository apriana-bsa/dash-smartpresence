@extends('layouts.master')
@section('title', trans('all.jamkerjakhusus'))
@section('content')

  <script>
  var dtable = '';

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

    setTimeout(pilihtahun(),200);
  });

  function pilihtahun(){
      var tahun = $('#tahun').val();
      $.ajax({
          type: "GET",
          url: "{{ url('datainduk/abensi/jamkerjakhususfilter') }}/"+tahun,
          data: '',
          cache: false,
          success: function(html){
              if(html['status'] == 'OK'){
                  dtable.ajax.url( '{!! url("datainduk/absensi/jamkerjakhusus/index-data") !!}').load();
              }
          }
      });
      return false;
  }
  </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.jamkerjakhusus') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.jamkerjakhusus') }}</strong></li>
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
                      <form action="{{ url('datainduk/abensi/jamkerjakhususfilter') }}" method="post">
                          {{ csrf_field() }}
                          <select name="tahun" id="tahun" class="form-control" onchange="pilihtahun()" style="float:left">
                              @if(Session::has('jamkerjakhusus_tahun'))
                                  <option value="{{ $tahun->tahun1 }}" @if(Session::get('jamkerjakhusus_tahun') == $tahun->tahun1) selected @endif>{{ $tahun->tahun1 }}</option>
                                  <option value="{{ $tahun->tahun2 }}" @if(Session::get('jamkerjakhusus_tahun') == $tahun->tahun2) selected @endif>{{ $tahun->tahun2 }}</option>
                                  <option value="{{ $tahun->tahun3 }}" @if(Session::get('jamkerjakhusus_tahun') == $tahun->tahun3) selected @endif>{{ $tahun->tahun3 }}</option>
                                  <option value="{{ $tahun->tahun4 }}" @if(Session::get('jamkerjakhusus_tahun') == $tahun->tahun4) selected @endif>{{ $tahun->tahun4 }}</option>
                                  <option value="{{ $tahun->tahun5 }}" @if(Session::get('jamkerjakhusus_tahun') == $tahun->tahun5) selected @endif>{{ $tahun->tahun5 }}</option>
                              @else
                                  <option value="{{ $tahun->tahun1 }}" @if($tahun->tahun1 == date('Y')) selected @endif>{{ $tahun->tahun1 }}</option>
                                  <option value="{{ $tahun->tahun2 }}" @if($tahun->tahun2 == date('Y')) selected @endif>{{ $tahun->tahun2 }}</option>
                                  <option value="{{ $tahun->tahun3 }}" @if($tahun->tahun3 == date('Y')) selected @endif>{{ $tahun->tahun3 }}</option>
                                  <option value="{{ $tahun->tahun4 }}" @if($tahun->tahun4 == date('Y')) selected @endif>{{ $tahun->tahun4 }}</option>
                                  <option value="{{ $tahun->tahun5 }}" @if($tahun->tahun5 == date('Y')) selected @endif>{{ $tahun->tahun5 }}</option>
                              @endif
                          </select>
                      </form>
                  </td>
                  @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 't') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
                      <td width="100px">
                          <a href="jamkerjakhusus/create" class="btn btn-primary pull-right"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      </td>
                  @endif
                  <td width="100px">
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('jamkerjakhusus/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <br>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                    <td class="nama"><b>{{ trans('all.jamkerja') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.ditujukanpada') }}</b></td>
                    <td class="alamat"><b>{{ trans('all.tanggal') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.waktukerja') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.toleransi') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.perhitunganjamkerja') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.hitunglembursetelah') }}</b></td>
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
    dtable = $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("datainduk/absensi/jamkerjakhusus/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'keterangan', name: 'keterangan' },
            { data: 'jamkerja', name: 'jamkerja' },
            { data: 'ditujukan', name: 'ditujukan' },
            { data: 'tanggalawal', name: 'tanggalawal' },
            { data: 'waktukerja', name: 'waktukerja' },
            { data: 'toleransi', name: 'toleransi' },
            { data: 'perhitunganjamkerja', name: 'perhitunganjamkerja' },
            { data: 'hitunglemburstlh', name: 'hitunglemburstlh' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush