@extends('layouts/master')
@section('title', trans('all.ajakan'))
@section('content')
  
    <script>
    @if(Session::get('message'))
      $(document).ready(function() {
        setTimeout(function() {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 4000,
                        extendedTimeOut: 4000,
                        positionClass: 'toast-bottom-right'
                    };
                    toastr.info('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
                }, 500);
      });
    @endif

    function pilihHakakses(){
      $('.val_perusahaan').css('display','none');
      $('.tr_hakakses').css('display','none');
      var perusahaan = $("#perusahaan").val();
      if(perusahaan != ""){
        $('.value_idperusahaan_'+perusahaan).css('display','');
        $('.tr_hakakses').css('display','');
      }
    }

    function validasi(apa){
      
      $('#submit').attr( 'data-loading', '' );
      $('#submit').attr('disabled', 'disabled');
      $('#tutupmodal').attr('disabled', 'disabled');
    
      var email = $("#email").val();
      
      if(apa == 'mengajak') {
    
          var perusahaan = $("#perusahaan").val();
          var hakakses = $("#hakakses").val();
    
          if (email == "") {
              alertWarning("{{ trans('all.emailkosong') }}",
              function () {
                  aktifkanTombol();
                  setFocus($('#email'));
              });
              return false;
          }
    
          if (perusahaan == "") {
              alertWarning("{{ trans('all.perusahaankosong') }}",
              function () {
                  aktifkanTombol();
                  setFocus($('#perusahaan'));
              });
              return false;
          }
    
          if (hakakses == "") {
              alertWarning("{{ trans('all.hakakseskosong') }}",
              function () {
                  aktifkanTombol();
                  setFocus($('#hakakses'));
              });
              return false;
          }
      }else if(apa == 'blokir'){
          if (email == "") {
              alertWarning("{{ trans('all.emailkosong') }}",
              function () {
                  aktifkanTombol();
                  setFocus($('#email'));
              });
              return false;
          }
      }
    }

    function manipulasidiajak(url,jenis){
      var pesan = "";
      if(jenis == 'terima'){
        pesan = "{{ trans('all.terimaajakan') }}";
      }else{
        pesan = "{{ trans('all.tolakajakan') }}";
      }
      alertConfirm(pesan,
        function(){
          window.location.href=url;
        }
      );
    }

    function tutupmodal(){
      $("#closemodal").trigger('click');
    }
    </script>
  <style>
  td{
    padding:5px;
  }

  .sweet-alert{
    z-index:99999999999;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ trans('all.ajakan') }}</h2>
    </div>
    <div class="col-lg-2">

    </div>
  </div>
  
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
          <li @if($ajakan == 'diajak') class="active" @endif><a href="{{ url('ajakan/diajak') }}">{{ trans('all.diajak') }}</a></li>
          <li @if($ajakan == 'mengajak') class="active" @endif><a href="{{ url('ajakan/mengajak') }}">{{ trans('all.mengajak') }}</a></li>
          <li @if($ajakan == 'blokir') class="active" @endif><a href="{{ url('ajakan/blokir') }}">{{ trans('all.blokir') }}</a></li>
        </ul>
        <br>
        @if($ajakan == "diajak")
        @elseif($ajakan == "mengajak")
          @if($adaperusahaan == 'ada')
              @if(strpos(Session::get('hakakses_perusahaan')->ajakan, 'i') !== false)
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" onclick="setTimeout(function(){ $('#email').focus(); },500)"><i class="fa fa-plus"></i>&nbsp;&nbsp;{{ trans('all.tambahajakanbaru') }}</button>
              @endif
          @else
              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" onclick="setTimeout(function(){ $('#email').focus(); },500)"><i class="fa fa-plus"></i>&nbsp;&nbsp;{{ trans('all.tambahajakanbaru') }}</button>
          @endif
          <p></p>
        @elseif($ajakan == "blokir")
          @if($adaperusahaan == 'ada')
              @if(strpos(Session::get('hakakses_perusahaan')->ajakan, 'i') !== false)
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalBlokir"><i class="fa fa-plus"></i>&nbsp;&nbsp;{{ trans('all.tambahblokir') }}</button>
              @endif
          @else
              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalBlokir"><i class="fa fa-plus"></i>&nbsp;&nbsp;{{ trans('all.tambahblokir') }}</button>
          @endif
          <p></p>
        @endif
        <div class="ibox float-e-margins">
          <div class="ibox-content p-md">
            @if($ajakan == "diajak")
              @if($datas == "")
                <center>{{ trans('all.nodata') }}</center>
              @else
                <table class="table datatable table-striped table-condensed">
                  <thead>
                      <tr>
                          <td class="opsi1"><b><center>{{ trans('all.manipulasi') }}</center></b></td>
                          <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                          <td class="nama"><b>{{ trans('all.perusahaan') }}</b></td>
                          <td class="nama"><b>{{ trans('all.hakakses') }}</b></td>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($datas as $data)
                        <tr>
                            <td>
                                <center>
                                    <a title="{{ trans('all.terima') }}" href="#" onclick="return manipulasidiajak('diajak/{{ $data->id }}/terima','terima')"><i class="fa fa-check" style="color:#ed5565"></i></a>&nbsp;&nbsp;
                                    <a title="{{ trans('all.tolak') }}" href="#" onclick="return manipulasidiajak('diajak/{{ $data->id }}/tolak','tolak')"><i class="fa fa-trash" style="color:#ed5565"></i></a>
                                </center>
                            </td>
                            <td>{{ $data->nama }}</td>
                            <td>{{ $data->perusahaan }}</td>
                            <td>{{ $data->hakakses }}</td>
                        </tr>
                      @endforeach
                  </tbody>
                </table>
              @endif
            @elseif($ajakan == "mengajak")
              @if($datas == "")
                <center>{{ trans('all.nodata') }}</center>
              @else
                <table class="table datatable table-striped table-condensed">
                  <thead>
                      <tr>
                          @if($adaperusahaan == 'ada')
                              @if(strpos(Session::get('hakakses_perusahaan')->ajakan, 'i') !== false)
                                  <td class="opsi1"><b><center>{{ trans('all.hapus') }}</center></b></td>
                              @endif
                          @else
                              <td class="opsi1"><b><center>{{ trans('all.hapus') }}</center></b></td>
                          @endif
                          <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                          <td class="nama"><b>{{ trans('all.perusahaan') }}</b></td>
                          <td class="nama"><b>{{ trans('all.hakakses') }}</b></td>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($datas as $data)
                        <tr>
                            @if($adaperusahaan == 'ada')
                                @if(strpos(Session::get('hakakses_perusahaan')->ajakan, 'i') !== false)
                                    <td>
                                        <center><a title="{{ trans('all.hapus') }}" href="#" onclick="return submithapus({{ $data->id }},'{{ trans('all.alerthapus') }}','{{ trans('all.ya') }}','{{ trans('all.tidak') }}')"><i class="fa fa-trash" style="color:#ed5565"></i></a></center>
                                        <form id="formhapus" action="mengajak/{{ $data->id }}" method="post">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="_method" value="delete">
                                            <input type="submit" id="{{ $data->id }}" style="display:none" name="delete" value="'.trans('all.hapus').'">
                                        </form>
                                    </td>
                                @endif
                            @else
                                <td>
                                    <center><a title="{{ trans('all.hapus') }}" href="#" onclick="return submithapus({{ $data->id }},'{{ trans('all.alerthapus') }}','{{ trans('all.ya') }}','{{ trans('all.tidak') }}')"><i class="fa fa-trash" style="color:#ed5565"></i></a></center>
                                    <form id="formhapus" action="mengajak/{{ $data->id }}" method="post">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="delete">
                                        <input type="submit" id="{{ $data->id }}" style="display:none" name="delete" value="'.trans('all.hapus').'">
                                    </form>
                                </td>
                            @endif
                            <td>{{ $data->nama }}</td>
                            <td>{{ $data->perusahaan }}</td>
                            <td>{{ $data->hakakses }}</td>
                        </tr>
                      @endforeach
                  </tbody>
                </table>
              @endif
            @elseif($ajakan == "blokir")
              @if($datas == "")
                <center>{{ trans('all.nodata') }}</center>
              @else
                <table class="table datatable table-striped table-condensed">
                  <thead>
                      <tr>
                          @if($adaperusahaan == 'ada')
                              @if(strpos(Session::get('hakakses_perusahaan')->ajakan, 'i') !== false)
                                  <td class="opsi1"><b><center>{{ trans('all.hapus') }}</center></b></td>
                              @endif
                          @else
                              <td class="opsi1"><b><center>{{ trans('all.hapus') }}</center></b></td>
                          @endif
                          <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                          <td class="nama"><b>{{ trans('all.email') }}</b></td>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($datas as $data)
                        <tr>
                            @if($adaperusahaan == 'ada')
                                @if(strpos(Session::get('hakakses_perusahaan')->ajakan, 'i') !== false)
                                    <td>
                                        <center><a title="{{ trans('all.hapus') }}" href="#" onclick="return submithapus({{ $data->id }},'{{ trans('all.alerthapus') }}','{{ trans('all.ya') }}','{{ trans('all.tidak') }}')"><i class="fa fa-trash" style="color:#ed5565"></i></a></center>
                                        <form id="formhapus" action="blokir/{{ $data->id }}" method="post">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="_method" value="delete">
                                            <input type="submit" id="{{ $data->id }}" style="display:none" name="delete" value="'.trans('all.hapus').'">
                                        </form>
                                    </td>
                                @endif
                            @else
                                <td>
                                    <center><a title="{{ trans('all.hapus') }}" href="#" onclick="return submithapus({{ $data->id }},'{{ trans('all.alerthapus') }}','{{ trans('all.ya') }}','{{ trans('all.tidak') }}')"><i class="fa fa-trash" style="color:#ed5565"></i></a></center>
                                    <form id="formhapus" action="blokir/{{ $data->id }}" method="post">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="delete">
                                        <input type="submit" id="{{ $data->id }}" style="display:none" name="delete" value="'.trans('all.hapus').'">
                                    </form>
                                </td>
                            @endif
                            <td>{{ $data->nama }}</td>
                            <td>{{ $data->email }}</td>
                        </tr>
                      @endforeach
                  </tbody>
                </table>
              @endif
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  @if($ajakan == "mengajak")
    <!-- Modal tambah ajakan-->
    <div class="modal fade" id="myModal" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-sm">
        
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ trans('all.mengajak') }}</h4>
          </div>
            <!-- <div class="modal-body" style="height:460px;overflow: auto;"> -->
            <div class="modal-body">
              @if($perusahaans == "")
                {{ trans('all.andatidakberelasidenganperusahaanmanapun') }}
              @else
                <form method="post" action="{{ url('ajakan/mengajak') }}" onsubmit="return validasi('mengajak')">
                  {{ csrf_field() }}
                  <table width='100%'>
                    <tr>
                      <td>{{ trans('all.email') }}</td>
                    </tr>
                    <tr>
                      <td>
                        <input type="text" class="form-control" autocomplete="off" id="email" name="email" maxlength="255">
                      </td>
                    </tr>
                    <tr>
                      <td>{{ trans('all.perusahaan') }}</td>
                    </tr>
                    <tr>
                      <td>
                          <input type="text" class="form-control" autofocus autocomplete="off" name="perusahaan" id="perusahaan" maxlength="100">
                          <script type="text/javascript">
                              $(document).ready(function(){
                                  $("#perusahaan").tokenInput("{{ url('tokenperusahaan') }}", {
                                      theme: "facebook",
                                      tokenLimit: 1,
                                      onAdd: function(data){
                                          pilihHakakses();
                                      },
                                      onDelete: function(data){
                                          pilihHakakses();
                                      }
//                                      onResult: function (results) {
////                                          $.each(results, function (index, value) {
////                                              value.name = "OMG: " + value.name;
////                                          });
//
//                                          return results;
//                                      }
                                  });
                              });
                          </script>
                      </td>
                    </tr>
                    <tr class="tr_hakakses" style="display:none;">
                      <td>{{ trans('all.hakakses') }}</td>
                    </tr>
                    <tr class="tr_hakakses" style="display:none;">
                      <td>
                        <select class="form-control" id="hakakses" name="hakakses">
                          <option value=""></option>
                          @foreach($perusahaans as $perusahaan)
                            @foreach($perusahaan->hakakses as $hakakses)
                              <option style="display:none" class="val_perusahaan value_idperusahaan_{{ $perusahaan->id }}" value="{{ $hakakses->id }}">{{ $hakakses->nama }}</option>
                            @endforeach
                          @endforeach
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <center>
                          <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-paper-plane-o'></i>&nbsp;&nbsp;{{ trans('all.kirim') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                          <span onclick="return tutupmodal()" id="tutupmodal" class="btn btn-primary"><i class='fa fa-undo'></i> {{ trans('all.tutup') }}</span>
                        </center>
                      </td>
                    </tr>
                  </table>
                </form>
              @endif
            </div>
        </div>
      </div>
    </div>
    <!-- Modal tambah ajakan-->
  @endif

  @if($ajakan == "blokir")
    <!-- Modal tambah blokir-->
    <div class="modal fade" id="modalBlokir" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-sm">
        
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ trans('all.blokir') }}</h4>
          </div>
            <!-- <div class="modal-body" style="height:460px;overflow: auto;"> -->
            <div class="modal-body">
              <form method="post" action="{{ url('ajakan/blokir') }}" onsubmit="return validasi('blokir')">
                {{ csrf_field() }}
                <table width='100%'>
                  <tr>
                    <td>
                      <input type="text" class="form-control" autocomplete="off" placeholder="{{ trans('all.email') }}" id="email" name="email" autofocus maxlength="255">
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <center>
                        <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-paper-plane-o'></i>&nbsp;&nbsp;{{ trans('all.kirim') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                        <span onclick="return tutupmodal()" id="tutupmodal" class="btn btn-primary"><i class='fa fa-undo'></i> {{ trans('all.tutup') }}</span>
                      </center>
                    </td>
                  </tr>
                </table>
              </form>
            </div>
        </div>
      </div>
    </div>
    <!-- Modal tambah blokir-->
  @endif

@stop

@push('scripts')
<script>
    $(function() {
        @if($ajakan == "diajak")
            $('.datatable').DataTable({
                scrollX: true,
                bStateSave: true,
                aoColumnDefs: [
                    { 'bSortable': false,
                        'aTargets': [ 0 ]
                    }
                ],
                order: [[1, 'asc']]
            });
        @else
            $('.datatable').DataTable({
                scrollX: true,
                bStateSave: true,
                @if($adaperusahaan == 'ada')
                    @if(strpos(Session::get('hakakses_perusahaan')->ajakan, 'i') !== false)
                        aoColumnDefs: [
                            { 'bSortable': false,
                                'aTargets': [ 0 ]
                            }
                        ],
                        order: [[1, 'asc']]
                    @else
                        order: [[0, 'asc']]
                    @endif
                @else
                    order: [[0, 'asc']]
                @endif
            });
        @endif
    });
</script>
@endpush