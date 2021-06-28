@extends('layouts.master')
@section('title', trans('all.konfirmasi_flag'))
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

  <div id='bootsidefilter' style="display: none;">
      <form method="POST" action="" id='formfilter' enctype="multipart/form-data">
          {{ csrf_field() }}
          <table width=100% style="margin-bottom: 60px">
              <tr>
                  <td class='tdheader' style='height:61px;background: #f3f3f4;color:#676a6c;font-size:24px;padding-left:15px;'><i class='fa fa-filter'></i> {{ trans('all.filter') }}</td>
              </tr>
              <tr>
                  <td class="tdfilter">
                      {{ trans('all.status') }}
                      <select class="form-control" id="status" name="status">
                        <option value="" @if($data['filter_status'] == '') selected @endif>{{trans('all.semua')}}</option>
                        <option value="c" @if($data['filter_status'] == 'c') selected @endif>{{trans('all.konfirmasi')}}</option>
                        <option value="a" @if($data['filter_status'] == 'a') selected @endif>{{trans('all.terima')}}</option>
                        <option value="na" @if($data['filter_status'] == 'na') selected @endif>{{trans('all.tolak')}}</option>
                      </select>
                  </td>
              </tr>
              <tr>
                  <td class="tdfilter">
                      {{ trans('all.flag') }}
                      <select class="form-control" id="flag" name="flag">
                        <option value="" @if($data['filter_flag'] == '') selected @endif>{{trans('all.semua')}}</option>
                        <option value="lupaabsenmasuk" @if($data['filter_flag'] == 'lupaabsenmasuk') selected @endif>{{trans('all.lupaabsenmasuk')}}</option>
                        <option value="lupaabsenkeluar" @if($data['filter_flag'] == 'lupaabsenkeluar') selected @endif>{{trans('all.lupaabsenkeluar')}}</option>
                        <option value="tidak-terlambat" @if($data['filter_flag'] == 'tidak-terlambat') selected @endif>{{trans('all.tidakterlambat')}}</option>
                        <option value="tidak-pulangawal" @if($data['filter_flag'] == 'tidak-pulangawal') selected @endif>{{trans('all.tidakpulangawal')}}</option>
                        <option value="lembur" @if($data['filter_flag'] == 'lembur') selected @endif>{{trans('all.lembur')}}</option>
                      </select>
                  </td>
              </tr>
          </table>
          <div style="height:60px;position: fixed;bottom: 0; background-color: #fff">
              <table style="margin-top:10px">
                  <tr>
                      <td class='tdfilter'>
                          <button id="submitfilter" type='submit' class="ladda-button btn btn-primary"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.lanjut') }}</button>
                      </td>
                  </tr>
              </table>
          </div>
      </form>
  </div>

  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.konfirmasi_flag') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.konfirmasi_flag') }}</strong></li>
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
                  <td>
                      <button id="tombolkonfirmasi" style="display: none" class="btn btn-primary" data-toggle="modal" data-target="#modalkonfirmasi"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.konfirmasi') }}</button>
                  </td>
                  <td>
                      <button class="btn btn-primary pull-right" onclick="return ke('{{ url('konfirmasiflag/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                  </td>
              </tr>
          </table>
          <p></p>
          @if($data['keteranganfilter'] != '')
              <div class="alert alert-danger" style="margin-bottom:10px">
                  <center>
                      {{ $data['keteranganfilter'] }}
                  </center>
              </div>
          @endif
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'm') !== false)
                        <td class='cek'><input type='checkbox' onclick='ceksemua()' id='ceksemuakonfirmasi'></td>
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="opsi5"><b>{{ trans('all.waktu') }}</b></td>
                    <td class="nama"><b>{{ trans('all.pegawai') }}</b></td>
                    <td class="opsi5"><center><b>{{ trans('all.flag') }}</b></center></td>
                    <td class="opsi5"><center><b>{{ trans('all.status') }}</b></center></td>
                    <td class="nama"><b>{{ trans('all.keteranganpengajuan') }}</b></td>
                    <td class="nama"><b>{{ trans('all.keterangankonfirmasi') }}</b></td>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal pegawai-->
    <!-- <a href="" id="showmodalkonfirmasi" data-toggle="modal" data-target="#modalkonfirmasi" style="display:none"></a> -->
    <div class="modal modalkonfirmasi fade" id="modalkonfirmasi" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{ trans('all.konfirmasi') }}</h4>
            </div>
            <div class="modal-body body-modal row" id="bodymodaljadwalshift" style="white-space: nowrap;overflow: auto;ellipsis;max-height:400px;">
              <h2>{{ trans('all.konfirmasidataterpilih') }}</h2>
               <Table>
                <tr>
                  <td>{{ trans('all.keterangan') }}</td>
                  <td colspan="2" style="padding-bottom:10px">
                    <textarea id="keterangan" class="form-control" style="resize:none"></textarea>
                  </td>
                </tr>
                <tr>
                  <td><button class="btn btn-primary" onclick="konfirmasiDataTerpilih('a')"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.terima') }}</button></td>
                  <td style="padding-left:10px"><button class="btn btn-danger"onclick="konfirmasiDataTerpilih('na')"><i class="fa fa-times"></i>&nbsp;&nbsp;{{ trans('all.tolak') }}</button></td>
                  <td style="padding-left:10px"><button class="btn btn-warning" onclick="konfirmasiDataTerpilih('c')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.konfirmasi') }}</button></td>
                </tr>
               </Table>
            </div>
            <div class="modal-footer">
                <table width="100%">
                    <tr>
                        <td align="right" style="padding:0">
                            <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                        </td>
                    </tr>
                </table>
            </div>
          </div>
      </div>
    </div>
    <!-- Modal pegawai-->

@stop

@push('scripts')
<script>
setTimeout(function(){ $('#bootsidefilter').css('display', ''); }, 1000);
$('#bootsidefilter').BootSideMenu({side:"right"});

function ceksemua(){
    if ($("#ceksemuakonfirmasi").prop("checked")) {
        $(".cek_konfirmasi").prop("checked", true);
    } else {
        $(".cek_konfirmasi").prop("checked", false);
    }
    cekKonfirmasiTerpilih();
}

function cekKonfirmasiTerpilih(){
    console.log($('input[class="cek_konfirmasi"]:checked').length);
    if($('input[class="cek_konfirmasi"]:checked').length > 0){
        $('#tombolkonfirmasi').css('display', '');
    }else{
        $('#tombolkonfirmasi').css('display', 'none');
    }

    if($('input[class="cek_konfirmasi"]').length == $('input[class="cek_konfirmasi"]:checked').length){
        $("#ceksemuakonfirmasi").prop("checked", true);
    }else{
        $("#ceksemuakonfirmasi").prop("checked", false);
    }
    // console.log($('input[class="cek_konfirmasi"]').length, $('input[class="cek_konfirmasi"]:checked').length);
}

function konfirmasiDataTerpilih(status){
  alertConfirm('{{ trans('all.apakahandayakin') }}',
        function(){
            var idkonfirmasi = [];
            $.each($('input[class="cek_konfirmasi"]:checked'), function(){
                idkonfirmasi.push($(this).val());
            });
            var dataString = 'idkonfirmasi='+idkonfirmasi.join(',')+'&keterangan='+$('#keterangan').val()+'&status='+status+'&_token={{ csrf_token() }}';
            $.ajax({
                type: "POST",
                url: '{{ url('datainduk/absensi/konfirmasiflag/konfirmasidataterpilih') }}',
                data: dataString,
                cache: false,
                success: function(response){
                    console.log(response);
                    if(response['status'] == 'ok'){
                        window.location.href='{{ url('datainduk/absensi/konfirmasiflag') }}';
                    }else{
                        alertError(response['msg']);
                    }
                }
            });
        },
        function(){
        },
        "{{ trans('all.ya') }}","{{ trans('all.tidak') }}"
    );
}

function setKonfirmasi(id){
    alertConfirm('{{ trans('all.jadikanstatusmenjadikonfirmasi') }}',
        function(){
            window.location.href='{{ url('datainduk/absensi/konfirmasiflag/setkonfirmasi') }}/'+id;
        },
        function(){
        },
        "{{ trans('all.ya') }}","{{ trans('all.tidak') }}"
    );
}
$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("datainduk/absensi/konfirmasiflag/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'u') !== false || strpos(Session::get('hakakses_perusahaan')->konfirmasi_flag, 'm') !== false)
                { data: 'cekkonfirmasi', name: 'cekkonfirmasi', orderable: false, searchable: false },
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
            { data: 'nama', name: 'nama' },
            { data: 'flag', name: 'flag' },
            { data: 'status', name: 'status' },
            { data: 'keterangan', name: 'keterangan' },
            { data: 'keterangankonfirmasi', name: 'keterangankonfirmasi' }
        ],
        order: [[2, 'desc']]
    });
});
</script>
@endpush
