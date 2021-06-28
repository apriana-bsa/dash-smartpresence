@extends('layouts.master')
@section('title', trans('all.menu_logabsen'))
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
      <h2>{{ trans('all.menu_logabsen') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.menu_logabsen') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <form method="POST" action="{{ url('datainduk/absensi/logabsen/submitperiode') }}">
              {{ csrf_field() }}
              <table width="100%">
                  <tr>
                      <td style="float:left;margin-top:8px">{{ trans('all.bulan') }}&nbsp;&nbsp;</td>
                      <td style="float:left">
                          <select name="bulan" id="bulan" class="form-control" onchange="this.form.submit()">
                              <option value="1" @if($bulanterpilih == 1) selected @endif>{{ trans('all.januari') }}</option>
                              <option value="2" @if($bulanterpilih == 2) selected @endif>{{ trans('all.februari') }}</option>
                              <option value="3" @if($bulanterpilih == 3) selected @endif>{{ trans('all.maret') }}</option>
                              <option value="4" @if($bulanterpilih == 4) selected @endif>{{ trans('all.april') }}</option>
                              <option value="5" @if($bulanterpilih == 5) selected @endif>{{ trans('all.mei') }}</option>
                              <option value="6" @if($bulanterpilih == 6) selected @endif>{{ trans('all.juni') }}</option>
                              <option value="7" @if($bulanterpilih == 7) selected @endif>{{ trans('all.juli') }}</option>
                              <option value="8" @if($bulanterpilih == 8) selected @endif>{{ trans('all.agustus') }}</option>
                              <option value="9" @if($bulanterpilih == 9) selected @endif>{{ trans('all.september') }}</option>
                              <option value="10" @if($bulanterpilih == 10) selected @endif>{{ trans('all.oktober') }}</option>
                              <option value="11" @if($bulanterpilih == 11) selected @endif>{{ trans('all.november') }}</option>
                              <option value="12" @if($bulanterpilih == 12) selected @endif>{{ trans('all.desember') }}</option>
                          </select>
                      </td>
                      <td style="float:left;margin-top:8px">&nbsp;&nbsp;{{ trans('all.tahun') }}&nbsp;&nbsp;</td>
                      <td style="float:left">
                          {{--<select class="form-control" name="tahun" id="tahun" onchange="this.form.submit()">--}}
                          <select class="form-control" name="tahun" id="tahun" onchange="this.form.submit()">
                              <option value="{{ $tahun->tahun1 }}" @if($tahunterpilih == $tahun->tahun1) selected @endif>{{ $tahun->tahun1 }}</option>
                              <option value="{{ $tahun->tahun2 }}" @if($tahunterpilih == $tahun->tahun2) selected @endif>{{ $tahun->tahun2 }}</option>
                              <option value="{{ $tahun->tahun3 }}" @if($tahunterpilih == $tahun->tahun3) selected @endif>{{ $tahun->tahun3 }}</option>
                              <option value="{{ $tahun->tahun4 }}" @if($tahunterpilih == $tahun->tahun4) selected @endif>{{ $tahun->tahun4 }}</option>
                              <option value="{{ $tahun->tahun5 }}" @if($tahunterpilih == $tahun->tahun5) selected @endif>{{ $tahun->tahun5 }}</option>
                          </select>
                      </td>
                      <td style="float:right">
                          <button type="button" class="btn btn-primary pull-right" onclick="return ke('{{ url('logabsen/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>&nbsp;&nbsp;
                      </td>
                      @if(strpos(Session::get('hakakses_perusahaan')->logabsen, 't') !== false || strpos(Session::get('hakakses_perusahaan')->logabsen, 'm') !== false)
                          <td style="float:right">
                              <a href="logabsen/createbyatribut" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdataberdasarkanatribut') }}</a>
                          </td>
                          <td style="float:right">
                              <a href="logabsen/create" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>&nbsp;&nbsp;
                          </td>
                      @endif
                  </tr>
              </table>
          </form>
          <p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->logabsen, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->logabsen, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->logabsen, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="opsi5"><b>{{ trans('all.waktu') }}</b></td>
                    <td class="nama"><b>{{ trans('all.pegawai') }}</b></td>
                    @if($atributvariablepenting_blade != '')
                        @foreach($atributvariablepenting_blade as $key)
                            @if($key != '')
                                <td class="nama"><b>{{ $key }}</b></td>
                            @endif
                        @endforeach
                    @endif
                    <td class="opsi5"><b>{{ trans('all.mesin') }}</b></td>
                    <td class="opsi4"><center><b>{{ trans('all.masukkeluar') }}</b></center></td>
                    <td class="nama"><b>{{ trans('all.alasan') }}</b></td>
                    <td class="opsi4"><b>{{ trans('all.terhitungkerja') }}</b></td>
                    <td class="alamat"><center><b>{{ trans('all.konfirmasi') }}</b></center></td>
                    <td class="opsi1"><center><b>{{ trans('all.status') }}</b></center></td>
                    @if($atributpenting_blade != '')
                        @foreach($atributpenting_blade as $key)
                            @if($key != '')
                                <td class="nama"><b>{{ $key }}</b></td>
                            @endif
                        @endforeach
                    @endif
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

@if(strpos(Session::get('hakakses_perusahaan')->logabsen, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->logabsen, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->logabsen, 'm') !== false)
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
            url: '{!! url("datainduk/absensi/logabsen/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->logabsen, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->logabsen, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->logabsen, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'waktu', name: 'waktu',
                render: function (data) {
                    if(data != null){
                        var ukDateTime = data.split(' ');
                        var ukDate = ukDateTime[0].split('-');
                        return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                    }else{
                        return data;
                    }
                }
            },
            { data: 'pegawai', name: 'pegawai' },
            @if($atributvariablepenting_controller != '')
                @foreach($atributvariablepenting_controller as $key)
                    @if($key != '')
                        { data: '{{ $key }}', name: '{{ $key }}' },
                    @endif
                @endforeach
            @endif
            { data: 'mesin', name: 'mesin' },
            { data: 'masukkeluar', name: 'masukkeluar' },
            { data: 'alasanmasukkeluar', name: 'alasanmasukkeluar' },
            { data: 'terhitungkerja', name: 'terhitungkerja' },
            { data: 'konfirmasi', name: 'konfirmasi' },
            { data: 'status', name: 'status' },
            @if($atributpenting_controller != '')
                @foreach($atributpenting_controller as $key)
                    @if($key != '')
                        { data: '{{ $key }}', name: '{{ $key }}' },
                    @endif
                @endforeach
            @endif
        ],
        order: [[ordercolumn, 'desc']]
    });
});
</script>
@endpush