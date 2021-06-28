@extends('layouts.master')
@section('title', trans('all.atribut'))
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
  <script>
  var dtable = '';
  var dtable2 = '';

  function ceksemua(sisi){
      if ($("#ceksemua"+sisi).prop("checked")) {
          $(".cek"+sisi).prop("checked", true);
      } else {
          $(".cek"+sisi).prop("checked", false);
      }
  }

  function changeContent(sisi){
      if(sisi === 'kiri') {
          var atribut = $('#atribut').val();
          //load data berdasarkan atribut nilai yg dipilih
          dtable.ajax.url('{!! url("datainduk/pegawai/atribut/perlakuanlembur/kiri/index-data") !!}/' + atribut).load();
      }else{
          var perlakuanlembur = $('#perlakuanlembur').val();
          //load data berdasarkan atribut nilai yg dipilih
          dtable2.ajax.url('{!! url("datainduk/pegawai/atribut/perlakuanlembur/kanan/index-data") !!}/' + perlakuanlembur).load();
      }
      return false;
  }

  function submit(sisi){
      var elements = document.getElementsByClassName("cek"+sisi);
      var perlakuanlembur = $('#perlakuanlembur').val();
      var atribut = $('#atribut').val();
      var s = '';
      for(var i=0; i<elements.length; i++) {
          if (elements[i].checked===true)
          {
              s += '|'+elements[i].id;
          }
      }

      if (s==='')
      {
          alertWarning('{{ trans('all.andabelummemilih') }}');
          return false;
      }

      if(perlakuanlembur === ''){
          alertWarning('{{ trans('all.perlakuanlemburkosong') }}');
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

              var dataString = "idatributnilai=" + encodeURIComponent(s)+"&perlakuanlembur="+perlakuanlembur+"&sisi="+sisi+"&_token="+token;

              $.ajax({
                  type: "POST",
                  url: "{!! url("datainduk/pegawai/atribut/perlakuanlembur") !!}",
                  data: dataString,
                  cache: false,
                  success: function (html) {
                      //console.log(html);
                      if (html['msg'] !== "") {
                          alertError(html['msg']);
                      }
                      dtable.ajax.url('{!! url("datainduk/pegawai/atribut/perlakuanlembur/kiri/index-data") !!}/' + atribut).load();
                      dtable2.ajax.url('{!! url("datainduk/pegawai/atribut/perlakuanlembur/kanan/index-data") !!}/' + perlakuanlembur).load();
                      setTimeout(function(){
                          $("#ceksemuakiri").prop("checked", false);
                          $("#ceksemuakanan").prop("checked", false);
                      },200);
                  }
              });
          }
      });
      return false;
  }
  </script>
  <style>
  span{
      cursor: default;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.atribut') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li>{{ trans('all.atribut') }}</li>
        <li class="active"><strong>{{ trans('all.perlakuanlembur') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
              <li><a href="{{ url('datainduk/pegawai/atribut') }}">{{ trans('all.atribut') }}</a></li>
              <li class="active"><a href="{{ url('datainduk/pegawai/atribut/perlakuanlembur') }}">{{ trans('all.perlakuanlembur') }}</a></li>
          </ul>
          <br>
          <table>
              <tr>
                  <td valign='top' width=50%>
                      <table>
                          <tr>
                              <td style="float:left">
                                  <select id="atribut" class="form-control" onchange="return changeContent('kiri')">
                                      <option value="">-- {{ trans('all.atribut') }} --</option>
                                      @if($dataatribut != '')
                                        @foreach($dataatribut as $key)
                                            <option value="{{ $key->id }}">{{ $key->atribut }}</option>
                                        @endforeach
                                      @endif
                                  </select>
                              </td>
                          </tr>
                      </table>
                      <p></p>
                      <div class="ibox float-e-margins">

                          <div class="ibox-content">
                              <table width=100% class="table datatablekiri table-striped table-condensed table-hover">
                                  <thead>
                                  <tr>
                                      <td class='cek'><input type='checkbox' onclick='ceksemua("kiri")' id='ceksemuakiri'></td>
                                      <td class="opsi5"><b>{{ trans('all.atribut') }}</b></td>
                                      <td class="nama"><b>{{ trans('all.atributnilai') }}</b></td>
                                  </tr>
                                  </thead>
                              </table>
                          </div>
                      </div>
                  </td>
                  <td width='65px' style='text-align:center !important;margin-top:275px;padding:10px;'>
                      @if(\App\Utils::cekHakakses('atribut','u') || \App\Utils::cekHakakses('atribut','m'))
                          <table>
                              <tr>
                                  <td>
                                      <button type="button" onclick="return submit('kiri')" class="btn btn-primary"><i class="fa fa-arrow-right"></i></button>
                                  </td>
                              </tr>
                              <tr>
                                  <td style="padding-top:10px">
                                      <button type="button" id="buttonhapus" onclick="return submit('kanan')" class="btn btn-primary"><i class="fa fa-arrow-left"></i></button>
                                  </td>
                              </tr>
                          </table>
                      @endif
                  </td>
                  <td valign='top' width=50%>
                      <table>
                          <tr>
                              <td style="float:left">
                                  <select id="perlakuanlembur" class="form-control" onchange="return changeContent('kanan')">
                                      <option value="">-- {{ trans('all.perlakuanlembur') }} --</option>
                                      <option value="tanpalembur">{{ trans('all.tanpalembur') }}</option>
                                      <option value="konfirmasi">{{ trans('all.konfirmasi') }}</option>
                                      <option value="lembur">{{ trans('all.lembur') }}</option>
                                  </select>
                              </td>
                          </tr>
                      </table>
                      <p></p>
                      <div class="ibox float-e-margins">
                          <div class="ibox-content">
                              <table width=100% class="table datatablekanan table-striped table-condensed table-hover">
                                  <thead>
                                  <tr>
                                      <td class='cek'><input type='checkbox' onclick='ceksemua("kanan")' id='ceksemuakanan'></td>
                                      <td class="opsi5"><b>{{ trans('all.atribut') }}</b></td>
                                      <td class="nama"><b>{{ trans('all.atributnilai') }}</b></td>
                                  </tr>
                                  </thead>
                              </table>
                          </div>
                      </div>
                  </td>
              </tr>
          </table>
      </div>
    </div>
  </div>
@stop

@push('scripts')
<script>
$(function() {
    dtable = $('.datatablekiri').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: '{!! url("datainduk/pegawai/atribut/perlakuanlembur/kiri/index-data") !!}',
        language: lang_datatable,
        columns: [
            { data: 'cekkiri', name: 'cekkiri', orderable: false, searchable: false },
            { data: 'atribut', name: 'atribut' },
            { data: 'atributnilai', name: 'atributnilai' }
        ],
        order: [[1, 'asc']]
    });

    dtable2 = $('.datatablekanan').DataTable({
        processing: true,
        bStateSave: true,
        serverSide: true,
        scrollX: true,
        ajax: '{!! url("datainduk/pegawai/atribut/perlakuanlembur/kanan/index-data") !!}',
        language: lang_datatable,
        columns: [
            { data: 'cekkanan', name: 'cekkanan', orderable: false, searchable: false },
            { data: 'atribut', name: 'atribut' },
            { data: 'atributnilai', name: 'atributnilai' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endpush