@extends('layouts.master')
@section('title', trans('all.riwayatsms'))
@section('content')
  
  <script>
  $(function(){
    $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
      $(this).datepicker('hide');
    });
    
    $('.date').mask("00/00/0000", {clearIfNotMatch: true});
  });
  function validasi(){
      $('#submit').attr( 'data-loading', '' );
      $('#submit').attr('disabled', 'disabled');
      $('#setulang').attr('disabled', 'disabled');
      
    var tanggalawal = $("#tanggalawal").val();
    var tanggalakhir = $("#tanggalakhir").val();

    if(tanggalawal === ''){
      alertWarning("{{ trans('all.tanggalkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#tanggalawal'));
              $('#setulang').removeAttr('disabled');
            });
      return false;
    }

    if(tanggalakhir === ''){
      alertWarning("{{ trans('all.tanggalkosong') }}",
            function() {
              aktifkanTombol();
              setFocus($('#tanggalakhir'));
                $('#setulang').removeAttr('disabled');
            });
      return false;
    }

    if(tanggalakhir.split("/").reverse().join("-") < tanggalawal.split("/").reverse().join("-")){
        alertWarning("{{ trans('all.tanggalakhirlebihkecildaritanggalawal') }}",
            function() {
                aktifkanTombol();
                setFocus($('#tanggalakhir'));
                $('#setulang').removeAttr('disabled');
            });
        return false;
    }

    if(cekSelisihTanggal(tanggalawal,tanggalakhir) == true){
        alertWarning("{{ trans('all.selisihharimaksimal31') }}",
            function() {
                aktifkanTombol();
                setFocus($('#tanggalakhir'));
                $('#setulang').removeAttr('disabled');
            });
        return false;
    }
  }
  </script>
  <style type="text/css">
  td{
    padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.sms') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li>{{ trans('all.riwayat') }}</li>
        <li class="active"><strong>{{ trans('all.sms') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <form action="{{ url('laporan/riwayat/sms') }}" method="post" onsubmit="return validasi()">
            {{ csrf_field() }}
            <table width="100%">
              <tr>
                <td style="float:left;margin-top:8px">{{ trans('all.tanggal') }}</td>
                <td style="float:left">
                  <input type="text" name="tanggalawal" size="11" id="tanggalawal" @if(Session::has('lapriwayatsms_tanggalawal')) value="{{ Session::get('lapriwayatsms_tanggalawal') }}" @else value="{{ $valuetglawalakhir->tanggalawal }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                </td>
                <td style="float:left;margin-top:8px">-</td>
                <td style="float:left">
                  <input type="text" name="tanggalakhir" size="11" id="tanggalakhir" @if(Session::has('lapriwayatsms_tanggalakhir')) value="{{ Session::get('lapriwayatsms_tanggalakhir') }}" @else value="{{ $valuetglawalakhir->tanggalakhir }}" @endif class="form-control date" placeholder="dd/mm/yyyy">
                </td>
                <td style="float:left">
                    <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-check-circle'></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button type="button" id="setulang" onclick="ke('../setulang/riwayatsms')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                </td>
                <td class="pull-right">
                    <button type="button" class="btn btn-primary" onclick="return ke('{{ url('laporan/riwayat/sms/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                </td>
              </tr>
            </table>
          </form>
          <p></p>
          <div class="ibox float-e-margins">
            <div class="ibox-content">
              <table width=100% class="table datatable table-striped table-condensed table-hover">
                <thead>
                  <tr>
                    <td class="opsi5"><b>{{ trans('all.tanggal') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.tujuan') }}</b></td>
                    <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
      </div>
    </div>
  </div>

  <!-- Modal sms-->
  <a href="" id="showmodalsms" data-toggle="modal" data-target="#modalsms" style="display:none"></a>
  <div class="modal modalsms fade" id="modalsms" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">

          </div>
      </div>
  </div>
  <!-- Modal sms-->

@stop

@push('scripts')
<script>
$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("laporan/riwayat/sms/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'tanggal', name: 'tanggal',
                render: function (data) {
                    var ukDateTime = data.split(' ');
                    var ukDate = ukDateTime[0].split('-');
                    return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                }
            },
            { data: 'tujuan', name: 'tujuan' },
            { data: 'isi', name: 'isi' }
        ],
        order: [[0, 'desc']]
    });
});
</script>
@endpush