@extends('layouts.master')
@section('title', trans('all.menu_pegawai'))
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

  function resetkatasandi(id){
    alertConfirm("{{ trans('all.resetkatasandipegawaiini') }} ?",
      function(){
        //document.getElementById(id).click();
        window.location.href="resetkatasandi/"+id;
      }
    );
  }

  function limitpegawai(){
      alertWarning("{{ trans('all.jumlahpegawaimencapaibatasygdiijinkan') }}",
              function() {
                  aktifkanTombol();
              });
      return false;
  }
  </script>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.menu_pegawai') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li class="active"><strong>{{ trans('all.menu_pegawai') }}</strong></li>
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
              @if(strpos(Session::get('hakakses_perusahaan')->pegawai, 't') !== false || strpos(Session::get('hakakses_perusahaan')->pegawai, 'm') !== false)
                  <td>
                      @if($limitpegawai == false)
                          <a href="#" onclick="return limitpegawai()" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      @else
                          <a href="{!! url('datainduk/pegawai/pegawai/create') !!}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>
                      @endif
                          &nbsp;&nbsp;<a href="{!! url('datainduk/pegawai/pegawai/imporexcel') !!}" class="btn btn-primary"><i class="fa fa-upload"></i>&nbsp;&nbsp;{{ trans('all.impordataexcel') }}</a>
                  </td>
              @endif
              <!-- <td>
                <div style="display: flex ; justify-content: flex-end">
{{--                      Button sinkron _5048_PTBrinksSolutionsIndonesia --}}
                    @if(Session::get('perusahaan_kode') == "5048")
                        &nbsp;&nbsp;&nbsp;&nbsp;<button class="btn btn-primary pull-right" id="btn-singkronisasi"><i class="fa fa-refresh"></i>&nbsp;&nbsp;&nbsp;&nbsp;Sinkronisasi </button>
                    @endif
                    &nbsp;&nbsp;&nbsp;<button class="btn btn-primary pull-right" onclick="return ke('{{ url('pegawai/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                </div>
              </td> -->
          </tr>
        </table>
        <p></p>
        <div class="ibox float-e-margins">
          
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr id="popover-pegawai-list"><div data-template='<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-body"><a class="close">&nbsp;&times;</a>{{ trans('onboarding.list_pegawai') }}</div></div>' data-toggle="popover-pegawai-list" data-content="content"/></tr>
                <tr>
                    <td class="opsi4"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.jamkerja') }}</b></td>
                    @if($atributvariablepenting_blade != '')
                        @foreach($atributvariablepenting_blade as $key)
                            @if($key != '')
                                <td class="nama"><b>{{ $key }}</b></td>
                            @endif
                        @endforeach
                    @endif
                    <td class="opsi1"><b>{{ trans('all.pin') }}</b></td>
                    <td class="opsi3"><b>{{ trans('all.nomorhp') }}</b></td>
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

  <!-- Modal voice-->
  <a href="" id="showmodalvoice" data-toggle="modal" data-target="#modalvoice" style="display:none"></a>
  <div class="modal modalvoice fade" id="modalvoice" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-sm">

          <!-- Modal content-->
          <div class="modal-content">

          </div>
      </div>
  </div>
  <!-- Modal voice-->

 <!-- Modal Sinkronisasi -->
  <div class="modal fade" id="modalSinkronisasi" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document" style="width: 1000px;">
          <div class="modal-content">
              <div class="modal-body">
                  <ul class="nav nav-tabs" role="tablist">
                      <li class="active">
                          <a href="#pegawailama" role="tab" data-toggle="tab">
                              Pegawai Lama Yang tidak ditemukan<span class="label label-danger total-pegawai-lama">0</span>
                          </a>
                      </li>
                      <li><a href="#pegawaiupdate" role="tab" data-toggle="tab">
                              Data Pegawai Update <span class="label label-primary total-pegawai-update">0</span>
                          </a>
                      </li>
                      <li>
                          <a href="#pegawaibaru" role="tab" data-toggle="tab">
                              Pegawai Baru <span class="label label-warning total-pegawai-baru">0</span>
                          </a>
                      </li>
                  </ul>

                  <!-- Tab panes -->
                  <div class="tab-content">
                      <!-- PEGAWAI LAMA -->
                      <div class="tab-pane fade active in" id="pegawailama">
                          <h2> Pegawai Lama Yang tidak ditemukan <span class="label label-danger total-pegawai-lama">0</span> </h2>
                          <table width=100% class="table table-striped table-condensed table-hover datatable-pegawai-lama">
                              <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Pin</th>
                                    <th>Status</th>
                                    <th>Manipulasi</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                    <td>Nama</td>
                                    <td>Pin</td>
                                    <td>Status</td>
                                    <td>Manipulasi</td>
                                </tr>
                              </tbody>
                          </table>
                      </div>
                      <!-- DATA PEGAWAI -->
                      <div class="tab-pane fade" id="pegawaiupdate">
                          <h2>Data Pegawai <span class="label label-primary total-pegawai-update">0</span></h2>
                          <table width=100% class="table table-striped table-condensed table-hover datatable-pegawai-update">
                              <thead>
                              <tr>
                                  <th>Nama</th>
                                  <th>Pin</th>
                                  <th>Status</th>
                                  <th>Manipulasi</th>
                              </tr>
                              </thead>
                              <tbody>
                              <tr>
                                  <td>Nama</td>
                                  <td>Pin</td>
                                  <td>Status</td>
                                  <td>Manipulasi</td>
                              </tr>
                              </tbody>
                          </table>
                      </div>
                      <!-- DATA PEGAWAI BARU -->
                      <div class="tab-pane fade" id="pegawaibaru">
                          <h2>Daftar Pegawai Baru <span class="label label-warning total-pegawai-baru">0</span></h2>
                          <table width=100% class="table table-striped table-condensed table-hover datatable-pegawai-baru">
                              <thead>
                              <tr>
                                  <th>Nama</th>
                                  <th>Pin</th>
                                  <th>Status</th>
                                  <th>Manipulasi</th>
                              </tr>
                              </thead>
                              <tbody>
                              <tr>
                                  <td>Nama</td>
                                  <td>Pin</td>
                                  <td>Status</td>
                                  <td>Manipulasi</td>
                              </tr>
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
{{--              <div class="modal-footer">--}}
{{--                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>--}}
{{--                  <button type="button" class="btn btn-primary">Save changes</button>--}}
{{--              </div>--}}
          </div>
      </div>
  </div>

@stop

@push('scripts')
<script>
window.detailpegawai=(function(idpegawai){
    $("#showmodalpegawai").attr("href", "");
    $("#showmodalpegawai").attr("href", "{{ url('detailpegawai') }}/"+idpegawai);
    $('#showmodalpegawai').trigger('click');
    return false;
});

window.aturVoice=(function(idpegawai){
    $("#showmodalvoice").attr("href", "");
    $("#showmodalvoice").attr("href", "{{ url('detailvoicepegawai') }}/"+idpegawai);
    $('#showmodalvoice').trigger('click');
    return false;
});

$('body').on('hidden.bs.modal', '.modalpegawai', function () {
    $(this).removeData('bs.modal');
    $("#" + $(this).attr("id") + " .modal-content").empty();
    $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
});

$('body').on('hidden.bs.modal', '.modalvoice', function () {
    $(this).removeData('bs.modal');
    $("#" + $(this).attr("id") + " .modal-content").empty();
    $("#" + $(this).attr("id") + " .modal-content").append("Loading...");
});

// Proses singkron
$("#btn-singkronisasi").click(function () {
    $("#modalSinkronisasi").modal("show");
    console.log("proses");
    $.get( '{!! url("datainduk/pegawai/sinkronisasi") !!}').done(function (data) {
        console.log(data);
        if(data["status"]) {
            $(".total-pegawai-lama").text(data["jumlah_pegawai_lama_tidak_ditemukan"]);
            $(".total-pegawai-update").text(data["jumlah_pegawai_yang_cocok"]);
            $(".total-pegawai-baru").text(data["jumlah_pegawai_baru_yang_ditemukan"]);

            // datatable data pegawai lama
            $(".datatable-pegawai-lama").DataTable ({
                "data" : data["pegawai_lama"],
                "columns" : [
                    { "data" : "nama" },
                    { "data" : "pin" },
                    { "data" : "status" },
                    { "data" : "tanggalaktif" }
                ]
            });

            // datatable data pegawai update
            $(".datatable-pegawai-update").DataTable ({
                "data" : data["pegawai_update"],
                "columns" : [
                    { "data" : "nama_lengkap" },
                    { "data" : "pin" },
                    { "data" : "status_aktif" },
                    { "data" : "createdate" }
                ]
            });

            // datatable data pegawai lama
            $(".datatable-pegawai-baru").DataTable ({
                "data" : data["pegawai_baru"],
                "columns" : [
                    { "data" : "nama_lengkap" },
                    { "data" : "pin" },
                    { "data" : "status_aktif" },
                    { "data" : "createdate" }
                ]
            });
        }
    }).error(function ($error) {
        
    });
    console.log("done");
})


$(function() {
    $('.datatable').DataTable({
        processing: true,
        bStateSave: false,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("datainduk/pegawai/pegawai/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'nama', name: 'nama' },
            { data: 'jamkerja', name: 'jamkerja' },
            @if($atributvariablepenting_controller != '')
                @foreach($atributvariablepenting_controller as $key)
                    @if($key != '')
                        { data: '{{ $key }}', name: '{{ $key }}' },
                    @endif
                @endforeach
            @endif
            { data: 'pin', name: 'pin' },
            { data: 'nomorhp', name: 'nomorhp' },
            { data: 'status', name: 'status' },
            @if($atributpenting_controller != '')
                @foreach($atributpenting_controller as $key)
                    @if($key != '')
                        { data: '{{ $key }}', name: '{{ $key }}' },
                    @endif
                @endforeach
            @endif
        ],
        order: [[0, 'desc']]
    })
    .on('draw', function() {

    });

    //start tooltip
    @if($onboarding)
        $(document).ready(function(){
            $('[data-toggle="popover-pegawai-list"]').popover({
                placement : 'auto top',
                trigger : 'manual',
            });

            $('[data-toggle="popover-pegawai-list"]').popover('show');

            $(document).on("click", ".popover .close" , function(){
                $(this).parents('.popover').popover('hide');
            });
        });
    @endif
    //end tooltip
});
</script>
@endpush