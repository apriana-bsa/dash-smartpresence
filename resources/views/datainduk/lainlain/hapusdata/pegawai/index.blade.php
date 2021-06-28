@extends('layouts.master')
@section('title', trans('all.hapusdata'))
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
</script>
  <style>
  .tdmodalDP{
      padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.hapusdata') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.lainlain') }}</li>
        <li>{{ trans('all.hapusdata') }}</li>
        <li class="active"><strong>{{ trans('all.pegawai') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
            <li class="active"><a href="{{ url('datainduk/lainlain/hapusdata/pegawai') }}">{{ trans('all.pegawai') }}</a></li>
            <li><a href="{{ url('datainduk/lainlain/hapusdata/mesin') }}">{{ trans('all.mesin') }}</a></li>
        </ul>
        <p></p>
        <table width="100%">
            <tr>
                <td class="pull-right">
                    <button class="btn btn-primary" onclick="return ke('{{ url('datainduk/lainlain/hapusdata/pegawai/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                </td>
            </tr>
        </table>
        <p></p>
        <div class="ibox float-e-margins">
          <div class="ibox-content">
            <div class="tab-content">
              <div id="tab-1" class="tab-pane active">
                  <div class="ibox float-e-margins">
                      <table width=100% class="table datatablehapus table-striped table-condensed table-hover">
                          <thead>
                          <tr>
                              <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                              <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                              <td class="opsi5"><b>{{ trans('all.nomorhp') }}</b></td>
                              <td class="opsi1"><center><b>{{ trans('all.status') }}</b></center></td>
                              <td class="alamat"><b>{{ trans('all.delwaktu') }}</b></td>
                          </tr>
                          </thead>
                      </table>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@stop

@push('scripts')
<script>
function resizeDatatable(jenis){
    setTimeout(function(){
        $('.datatable'+jenis).resize();
    },1);
}

function validasi(){
    var perusahaan = $('#perusahaan').val();
    if(perusahaan == ''){
        alertWarning("{{ trans('all.perusahaankosong') }}",
                function() {
                    aktifkanTombol();
                    setFocus($('#token-input-perusahaan'));
                });
        return false;
    }
}

function hapusdata(menu,id,msg){
    if(msg === undefined){
        msg = "{{ trans('all.alerthapus') }}";
    }
    alertConfirm(msg,
        function(){
            window.location.href='{{ url('datainduk/lainlain/hapusdata') }}/'+menu+'/hapus/'+id;
        }
    );
}

function batalHapus(menu,id){
    alertConfirm("{{ trans('all.alertbatalhapus') }}",
        function(){
            window.location.href='{{ url('datainduk/lainlain/hapusdata') }}/'+menu+'/restore/'+id;
        }
    );
}

$(function() {

    $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
        $(this).datepicker('hide');
    });

    $('.date').mask("00/00/0000", {clearIfNotMatch: true});

    $('#jamkadaluarsa').inputmask( 'h:s' );
    $('#jamkadaluarsa_edit').inputmask( 'h:s' );

    var datatable = $('.datatablehapus').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        scrollX: true,
        ajax: {
            url: '{!! url("datainduk/lainlain/hapusdata/pegawai/index-data") !!}',
            type: "POST",
            data: { _token: '{!! csrf_token() !!}' }
        },
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'nama', name: 'nama' },
            { data: 'nomorhp', name: 'nomorhp' },
            { data: 'status', name: 'status' },
            { data: 'del_waktu', name: 'del_waktu',
                render: function (data) {
                    var ukDateTime = data.split(' ');
                    var ukDate = ukDateTime[0].split('-');
                    return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                }
            }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush