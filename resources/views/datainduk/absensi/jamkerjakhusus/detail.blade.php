@extends('layouts.master')
@section('title', trans('all.jamkerjakhusus'))
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
  <style>
  #modalbody{
      padding-bottom:0;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      @if($jamkerjakhusus != '')
          <h2>{{ trans('all.jamkerjakhusus').' ('.$jamkerjakhusus.')' }}</h2>
      @else
          <h2>{{ trans('all.jamkerjakhusus') }}</h2>
      @endif
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li>{{ trans('all.jamkerjakhusus') }}</li>
        <li class="active"><strong>{{ trans('all.detail') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <table width="100%">
                <tr>
                    <td>
                        @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 't') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
                            <a href="#" class="btn btn-primary" onclick="tambahdata()"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;{{ trans('all.tambahdata') }}</a>&nbsp;
                        @endif
                        <button class="btn btn-primary" onclick="return ke('{{ url('datainduk/absensi/jamkerjakhusus') }}')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                    </td>
                    <td class="pull-right">
                        <button class="btn btn-primary" onclick="return ke('{{ url('jamkerjakhusus/'.$idjamkerjakhusus.'/detail/excel') }}')"><i class="fa fa-download"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
                    </td>
                </tr>
            </table>
          <div class="ibox-content">
            <table width=100% class="table datatable table-striped table-condensed table-hover">
              <thead>
                <tr>
                    @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
                        <td class="opsi1"><center><b>{{ trans('all.manipulasi') }}</b></center></td>
                    @endif
                    <td class="nama"><b>{{ trans('all.pegawai') }}</b></td>
                    <td class="opsi2"><b>{{ trans('all.pin') }}</b></td>
                    <td class="opsi5"><b>{{ trans('all.nomorhp') }}</b></td>
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

  <!-- Modal tambah atribut-->
  <a href="#" id="tomboltambahdata" style="display:none" data-toggle="modal" data-target="#modaltambahdata"></a>
  <div class="modal fade" id="modaltambahdata" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md">

          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">{{ trans('all.tambahdata') }}</h4>
              </div>
              <div class="modal-body" id="modalbody">
                  <table width="100%">
                      <tr>
                          <td width="50px">{{ trans('all.atribut') }}</td>
                          <td>
                              <input type="text" class="form-control" autofocus autocomplete="off" name="atributnilai" id="atributnilai">
                              <script type="text/javascript">
                                  $(document).ready(function(){
                                      $("#atributnilai").tokenInput("{{ url('tokenatributnilai') }}", {
                                          theme: "facebook"
                                      });
                                  });
                              </script>
                          </td>
                          <td width="50px" style="padding-left:10px">
                              <input type="button" onclick="filterpegawai()" class="btn btn-primary" value="{{ trans('all.filter') }}">
                          </td>
                      </tr>
                  </table>
                  <br>
                  <table class="table datatabletambahdata table-striped table-condensed table-hover nowrap">
                      <thead>
                          <tr>
                              <td width="50px"><center><input type="checkbox" onclick='ceksemua()' id="ceksemuapegawai"></center></td>
                              <td class="nama"><b>{{ trans('all.pegawai') }}</b></td>
                              <td class="opsi2"><b>{{ trans('all.pin') }}</b></td>
                              <td class="opsi5"><b>{{ trans('all.nomorhp') }}</b></td>
                              <td class="opsi1"><center><b>{{ trans('all.status') }}</b></center></td>
                          </tr>
                      </thead>
                  </table>
              </div>
              <div class="modal-footer">
                  <table width="100%">
                      <tr>
                          <td style="padding:0;align:right">
                              <button class="btn btn-primary" onclick="return submitTambahData()" id="tambahatribut"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</button>
                          </td>
                      </tr>
                  </table>
              </div>
          </div>
      </div>
  </div>
  <!-- Modal tambah atribut-->

@stop

@push('scripts')
<script>
var dtable = '';
var dtable2 = '';

function tambahdata(){
    $('#tomboltambahdata').trigger('click');
}

function filterpegawai() {
    var idatribut = $('#atributnilai').val();
    if(idatribut == ''){
        idatribut = 'o';
    }
    dtable2.ajax.url('{!! url("datainduk/absensi/jamkerjakhusus/".$idjamkerjakhusus."/detailtambah/index-data") !!}/'+idatribut).load();
}

function ceksemua(){
    if ($("#ceksemuapegawai").prop("checked"))
    {
        $(".cekpegawai").prop("checked", true);
    }else{
        $(".cekpegawai").prop("checked", false);
    }
}

function submitTambahData(){
    var elements = document.getElementsByClassName("cekpegawai");
    var s = '';
    for(var i=0; i<elements.length; i++) {
        if (elements[i].checked==true)
        {
            s += '|'+elements[i].value;
        }
    }

    if (s=='')
    {
        alertWarning('{{ trans('all.pegawaikosong') }}');
        return false;
    }

    s = s.substring(1);
    //jalankan ajax terus load datatable
    $.ajax({
        type: "GET",
        url: '{{ url('generatecsrftoken') }}',
        data: '',
        cache: false,
        success: function (token) {
            //alert(token);
            var dataString = "idpegawai=" + encodeURIComponent(s)+"&_token="+token;

            $.ajax({
                type: "POST",
                url: "{!! url("datainduk/absensi/jamkerjakhusus/".$idjamkerjakhusus."/detail") !!}",
                data: dataString,
                cache: false,
                success: function (html) {
                    if (html != '') {
                        // console.log(html);
                        alertError(html);
                    }else{
                        dtable.ajax.url( '{!! url("datainduk/absensi/jamkerjakhusus/".$idjamkerjakhusus."/detail/index-data") !!}' ).load();
                        $("#ceksemuapegawai").prop("checked", false);
                        $('#closemodal').trigger('click');
                    }
                }
            });
        }
    });
    return false;
}

function hapusdata(idpegawai){
    alertConfirm("{{ trans('all.alerthapus') }}",
        function(){
            window.location.href='{{ url('datainduk/absensi/jamkerjakhusus/'.$idjamkerjakhusus.'/delete') }}/'+idpegawai;
        }
    );
}

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

@if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
    var ordercolumn = 1;
@else
    var ordercolumn = 0;
@endif
$(function() {

    dtable = $('.datatable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: '{!! url("datainduk/absensi/jamkerjakhusus/".$idjamkerjakhusus."/detail/index-data") !!}',
        language: lang_datatable,
        columns: [
            @if(strpos(Session::get('hakakses_perusahaan')->jamkerja, 'h') !== false || strpos(Session::get('hakakses_perusahaan')->jamkerja, 'm') !== false)
                { data: 'action', name: 'action', orderable: false, searchable: false },
            @endif
            { data: 'nama', name: 'nama' },
            { data: 'pin', name: 'pin' },
            { data: 'nomorhp', name: 'nomorhp' },
            { data: 'status', name: 'status' }
        ],
        order: [[ordercolumn, 'asc']]
    });

    dtable2 = $('.datatabletambahdata').DataTable({
        processing: true,
        serverSide: true,
        aLengthMenu: [[10, 100, 1000, -1], [10, 100, 1000, "∞"]],
        iDisplayLength: 10,
        scrollX: true,
        ajax: '{!! url("datainduk/absensi/jamkerjakhusus/".$idjamkerjakhusus."/detailtambah/index-data") !!}/o',
        language: lang_datatable,
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'nama', name: 'nama' },
            { data: 'pin', name: 'pin' },
            { data: 'nomorhp', name: 'nomorhp' },
            { data: 'status', name: 'status' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush