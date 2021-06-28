@extends('layouts.master')
@section('title', trans('all.pegawai'))
@section('content')
  
  <script>
  @if(Session::get('message'))
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
  @endif

  function hapusdata(id, jenis){
    var msg = "{{ trans('all.alerthapus') }}";
    if(jenis == 'shift'){
        msg = '{{ trans('all.hapusdatainijugaakanmenghapusdatadijadwalhshift') }}';
    }
    alertConfirm(msg,
      function(){
        window.location.href="{{ url('datainduk/pegawai/pegawai/jamkerja/hapus') }}/"+id;
      },function(){},'{{ trans('all.ya') }}','{{ trans('all.tidak') }}'
    );
  }
  </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.jamkerja').' ('.$namapegawai->nama.')' }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.pegawai') }}</li>
        <li class="active"><strong>{{ trans('all.jamkerja') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 't') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
          <a href="{!! url('datainduk/pegawai/pegawai/jamkerja/'.$idpegawai.'/tambah') !!}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>&nbsp;&nbsp;
        @endif
        <button class="btn btn-primary" onclick="return ke('{{ url('datainduk/absensi/jamkerja') }}')"><i class="fa fa-clock-o"></i>&nbsp;&nbsp;{{ trans('all.jamkerja') }}</button>&nbsp;&nbsp;
        <button class="btn btn-primary" onclick="return ke('{{ url('datainduk/pegawai/pegawai') }}')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
        <button class="btn btn-primary pull-right" onclick="return ke('{{ url('datainduk/pegawai/pegawai/jamkerja/excel/'.$idpegawai) }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button><p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
                      <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="nama"><b>{{ trans('all.jamkerja') }}</b></td>
                    <td class="alamat"><b>{{ trans('all.jenis') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.berlakumulai') }}</b></td>
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

@if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
  var ordercolumn = 3;
@else
  var ordercolumn = 2;
@endif
$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("datainduk/pegawai/pegawai/jamkerja/".$idpegawai."/data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
              { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'jamkerja', name: 'jamkerja' },
            { data: 'jenis', name: 'jenis' },
            { data: 'berlakumulai', name: 'berlakumulai',
                render: function (data) {
                    var ukDate = data.split('-');
                    return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0];
                }
            }
        ],
        order: [[ordercolumn, 'desc']]
    });
});
</script>
@endpush