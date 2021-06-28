@extends('layouts.master')
@section('title', trans('all.aturatributdanlokasi'))
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

  function ceksemua(jenis){
      if ($("#ceksemuapegawai_"+jenis).prop("checked")) {
          $(".cekpegawai_"+jenis).prop("checked", true);
      } else {
          $(".cekpegawai_"+jenis).prop("checked", false);
      }
  }

  function submit(jenis){
      var elements = document.getElementsByClassName("cekpegawai_"+jenis);
      var s = '';
      for(var i=0; i<elements.length; i++) {
          if (elements[i].checked==true)
          {
              s += '|'+elements[i].id;
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
              var dataString = "idpegawai=" + encodeURIComponent(s)+"&jenis="+jenis+"&_token="+token;
              $.ajax({
                  type: "POST",
                  url: "{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/submit") !!}",
                  data: dataString,
                  cache: false,
                  success: function (html) {
                      console.log(html);
                      if (html['msg'] != "") {
                          alertError(html['msg']);
                          return false;
                      }
                      dtable.ajax.url( '{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/index-data/kiri") !!}' ).load();
                      dtable2.ajax.url( '{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/index-data/kanan") !!}').load();
                      $(".ceksemuapegawai").prop("checked", false);
                  }
              });
          }
      });
      return false;
  }

  function filterPenempatan() {
      var penempatan = $('#penempatan').val();
      var tanggal = $('#tanggal').val();
      var jam = $('#jam').val();

      if(penempatan == ''){
          alertWarning("{{ trans('all.penempatan').' '.trans('all.sa_kosong') }}",
              function() {
                  aktifkanTombol();
                  setFocus($('#penempatan'));
              });
          return false;
      }

      if(tanggal == ''){
          alertWarning("{{ trans('all.tanggal').' '.trans('all.sa_kosong') }}",
              function() {
                  aktifkanTombol();
                  setFocus($('#tanggal'));
              });
          return false;
      }

      if(!is_valid_date(tanggal)){
          alertWarning("{{ trans('all.tanggaltidakvalid') }}",
              function() {
                  aktifkanTombol();
                  setFocus($('#tanggal'));
              });
          return false;
      }

      if(jam == ''){
          alertWarning("{{ trans('all.jam').' '.trans('all.sa_kosong') }}",
              function() {
                  aktifkanTombol();
                  setFocus($('#jam'));
              });
          return false;
      }

      if(!is_valid_time(jam)){
          alertWarning("{{ trans('all.jamtidakvalid') }}",
              function() {
                  aktifkanTombol();
                  setFocus($('#jam'));
              });
          return false;
      }

      $.ajax({
          type: "GET",
          url: '{{ url('generatecsrftoken') }}',
          data: '',
          cache: false,
          success: function (token) {
              var dataString = "penempatan="+penempatan+"&tanggal="+tanggal+"&jam="+jam+"&_token="+token;
              $.ajax({
                  type: "POST",
                  url: "{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/submitfilter") !!}",
                  data: dataString,
                  cache: false,
                  success: function (resp) {
                      dtable.ajax.url( '{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/index-data/kiri") !!}' ).load();
                      dtable2.ajax.url( '{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/index-data/kanan") !!}').load();
                      $('#tutupmodalpenempatan').trigger('click');
                      $('#alertpenempatan').html(resp).css('display','');
                      $("#ceksemuapegawai").prop("checked", false);
                  }
              });
          }
      });
  }
  </script>
  <style>
      .alert{
          margin-bottom:10px
      }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.aturatributdanlokasi') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.kepegawaian') }}</li>
        <li class="active"><strong>{{ trans('all.aturatributdanlokasi') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <ul class="nav nav-tabs">
          <li><a href="{{ url('datainduk/pegawai/pegawai/aturatributdanlokasi/aturatribut') }}">{{ trans('all.atribut') }}</a></li>
          <li><a href="{{ url('datainduk/pegawai/pegawai/aturatributdanlokasi/aturlokasi') }}">{{ trans('all.lokasi') }}</a></li>
          <li><a href="{{ url('datainduk/pegawai/pegawai/aturatributdanlokasi/aturflexytime') }}">{{ trans('all.flexytime') }}</a></li>
          <li><a href="{{ url('datainduk/pegawai/pegawai/aturatributdanlokasi/ijinkansambungdatacapture') }}">{{ trans('all.ijinkansambungdatacapture') }}</a></li>
          <li class="active"><a href="{{ url('datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai') }}">{{ trans('all.penempatanpegawai') }}</a></li>
        </ul>
        <br>
          <table>
              <tr>
                  <td valign='top' width='50%'>
                      <table>
                          <tr>
                              <td>
                                  <button type="button" data-toggle="modal" data-target="#modalFilter" class="btn btn-primary"><i class="fa fa-bars"></i>&nbsp;&nbsp;{{ trans('all.filter') }}</button>
                                  &nbsp;&nbsp;
                                  <button @if(!Session::has('penempatanpegawai_atribut')) style="display:none" @else @endif type="button" id="setulang" onclick="return ke('{{ url('datainduk/pegawai/pegawai/aturatributdanlokasi/setulang/penempatanpegawai') }}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                              </td>
                          </tr>
                      </table>
                      <p></p>
                      <div id="alertatribut" @if(!Session::has('penempatanpegawai_atribut')) style="display:none" @endif class="alert alert-success">
                          @if(Session::has('penempatanpegawai_atribut')) {!! Session::get('penempatanpegawai_atribut') !!} @endif
                      </div>
                      <div class="ibox float-e-margins">

                          <div class="ibox-content">
                              <table width=100% class="table datatable table-striped table-condensed table-hover">
                                  <thead>
                                  <tr>
                                      <td class='cek'><input type='checkbox' onclick='ceksemua("kiri")' id='ceksemuapegawai_kiri' class="ceksemuapegawai"></td>
                                      <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                                      <td class="pin"><b>{{ trans('all.pin') }}</b></td>
                                  </tr>
                                  </thead>
                              </table>
                          </div>
                      </div>
                  </td>
                  <td width='65px' style='text-align:center !important;margin-top:275px;padding:10px;'>
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
                  </td>
                  <td valign='top' width='50%'>
                      <table>
                          <tr>
                              <td style="float:left">
                                  <button type="button" data-toggle="modal" data-target="#modalpenempatan" class="btn btn-primary"><i class="fa fa-bars"></i>&nbsp;&nbsp;{{ trans('all.penempatan') }}</button>
                              </td>
                          </tr>
                      </table>
                      <p></p>
                      <div id="alertpenempatan" @if(!Session::has('penempatanpegawai_keteranganpenempatan')) style="display:none" @endif class="alert alert-success">
                          @if(Session::has('penempatanpegawai_keteranganpenempatan')) {!! Session::get('penempatanpegawai_keteranganpenempatan') !!} @endif
                      </div>
                      <div class="ibox float-e-margins">

                          <div class="ibox-content">
                              <table width=100% class="table datatablejamkerjashift table-striped table-condensed table-hover">
                                  <thead>
                                  <tr>
                                      <td class='cek'><input type='checkbox' onclick='ceksemua("kanan")' id='ceksemuapegawai_kanan' class="ceksemuapegawai"></td>
                                      <td class="nama"><b>{{ trans('all.nama') }}</b></td>
                                      <td class="pin"><b>{{ trans('all.pin') }}</b></td>
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

  <!-- Modal Filter penempatan -->
  <div class="modal modalpenempatan fade" id="modalpenempatan" role="dialog" tabindex='-1'>
      <div class="modal-dialog modal-md" style="width:420px">
          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">{{ trans('all.filter').' '.trans('all.penempatan') }}</h4>
              </div>
              <div class="modal-body" style="max-height:480px;overflow: auto;">
                  <table>
                      <tr>
                          <td style="width:110px;padding:10px;padding-bottom:0">{{trans('all.penempatan')}}</td>
                          <td style="float:left;padding:10px;padding-bottom:0" colspan="2">
                              <select id="penempatan" class="form-control">
                                  <option value=""></option>
                                  @foreach($data['penempatan'] as $key)
                                      <option value="{{ $key->id }}" @if($key->id == $data['penempatanterpilih']) selected @endif>{{ $key->nama }}</option>
                                  @endforeach
                              </select>
                          </td>
                      </tr>
                      <tr>
                          <td style="padding:10px;">{{trans('all.berlakumulai')}}</td>
                          <td style="padding:10px;float:left">
                              <input type="text" size="12" value="{{$data['tanggal']}}" name="tanggal" class="date form-control" id="tanggal"/>
                          </td>
                          <td style="padding:10px;padding-left:0;float:left">
                              <input type="text" size="7" value="{{$data['jam']}}" name="jam" class="form-control" id="jam"/>
                          </td>
                      </tr>
                  </table>
              </div>
              <div class="modal-footer">
                  <table width="100%">
                      <tr>
                          <td style="padding:0px;align:right">
                              <button class="btn btn-primary" onclick="filterPenempatan()"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.terapkan') }}</button>
                              <button class="btn btn-primary" id="tutupmodalpenempatan" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                          </td>
                      </tr>
                  </table>
              </div>
          </div>
      </div>
  </div>
  <!-- Modal Filter penempatan -->

  <!-- Modal filter-->
  <div class="modal fade" id="modalFilter" role="dialog" tabindex='-1'>
      <div class="modal-dialog @if(count($data['atribut'])<=1) modal-sm @elseif(count($data['atribut'])==2) modal-md @else modal-lg @endif">

          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">{{ trans('all.filter').' '.trans('all.atribut') }}</h4>
              </div>
              <div class="modal-body" style="max-height:480px;overflow: auto;">
                  @if(isset($data['atribut']))
                      @for($i=0;$i<count($data['atribut']);$i++)
                          <div class="@if(count($data['atribut'])<=1) col-md-12 @elseif(count($data['atribut'])==2) col-md-6 @else col-md-4 @endif">
                              @if(isset($data['atribut'][$i]['flag']))
                                  <input type="checkbox" class="atributpopup" id="semuaatribut_{{ $data['atribut'][$i]['idatribut'] }}" value="{{ $data['atribut'][$i]['idatribut'] }}" onclick="checkboxallclick('semuaatribut_{{ $data['atribut'][$i]['idatribut'] }}','attr_{{ $data['atribut'][$i]['idatribut'] }}')" @if($data['atribut'][$i]['flag'] == 1) checked @endif>&nbsp;&nbsp;
                              @else
                                  <input type="checkbox" class="atributpopup" id="semuaatribut_{{ $data['atribut'][$i]['idatribut'] }}" value="{{ $data['atribut'][$i]['idatribut'] }}" onclick="checkboxallclick('semuaatribut_{{ $data['atribut'][$i]['idatribut'] }}','attr_{{ $data['atribut'][$i]['idatribut'] }}')">&nbsp;&nbsp;
                              @endif
                              <span style="margin:0" id="spansemuaatribut_{{ $data['atribut'][$i]['idatribut'] }}" onclick="spanallclick('semuaatribut_{{ $data['atribut'][$i]['idatribut'] }}','attr_{{ $data['atribut'][$i]['idatribut'] }}')"><strong>{{ $data['atribut'][$i]['atribut'] }}</strong></span>
                              <table>
                                  @foreach($data['atribut'][$i]['atributnilai'] as $key)
                                      <tr>
                                          <td style="width:20px;padding:2px" valign="top">
                                              <input type="checkbox" @if($key->enable == 0) disabled @endif idatribut="{{ $key->idatribut }}" @if($key->dipilih == 1) checked @endif onchange="return checkAllAttr('attr_{{ $key->idatribut }}','semuaatribut_{{ $key->idatribut }}')" class="atributpopup attr_{{ $key->idatribut }}" name="atribut_{{ $key->idatribut }}" id="atributpopup{{ $key->id }}" value="{{ $key->id }}">
                                          </td>
                                          <td style="padding: 2px;">
                                              <span id="attrpopup_atribut{{ $key->id }}" onclick="spanClick('atributpopup{{ $key->id }}')" atribut="{{ $data['atribut'][$i]['atribut'] }}">{{ $key->nilai }}</span>
                                          </td>
                                      </tr>
                                  @endforeach
                              </table>
                          </div>
                      @endfor
                  @endif
              </div>
              <div class="modal-footer">
                  <table width="100%">
                      <tr>
                          <td style="padding:0px;align:right">
                              <button class="btn btn-primary" id="tambahatribut"><i class="fa fa-check"></i>&nbsp;&nbsp;{{ trans('all.terapkan') }}</button>
                              <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                          </td>
                      </tr>
                  </table>
              </div>
          </div>
      </div>
  </div>
  <!-- Modal filter-->

@stop

@push('scripts')
<script>
    $(function() {
        $('#tambahatribut').click(function(){
            var atribut = document.getElementsByClassName("atributpopup");

            var filteratributnilai = "";
            for(var i=0; i<atribut.length; i++) {
                if (atribut[i].id.substring(0,12)!="semuaatribut") {
                    if (document.getElementById("atributpopup"+atribut[i].value).checked) {
                        filteratributnilai += ','+atribut[i].value;
                    }
                }
            }

            var atribut = filteratributnilai === '' ? '' : filteratributnilai.substring(1);
            $("#tutupmodal").trigger("click");

            $.ajax({
                type: "GET",
                url: '{{ url('generatecsrftoken') }}',
                data: '',
                cache: false,
                success: function (token) {
                    var dataString = "atribut="+atribut+"&_token=" + token;
                    $.ajax({
                        type: "POST",
                        url: "{{ url('datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/submitfilter') }}",
                        data: dataString,
                        cache: false,
                        success: function (resp) {
                            // console.log(resp);
                            if (resp != '') {
                                $('#setulang').css('display', '');
                                $('#alertatribut').css('display', '');
                                $('#alertatribut').html(resp);
                            } else {
                                $('#setulang').css('display', 'none');
                                $('#alertatribut').css('display', 'none');
                                $('#alertatribut').html('');
                            }
                            dtable.ajax.url('{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/index-data/kiri") !!}').load();
                            dtable2.ajax.url('{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/index-data/kanan") !!}').load();
                        }
                    });
                }
            });
            return false;
        });

        $('.date').mask("00/00/0000", {clearIfNotMatch: true});
        $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
            $(this).datepicker('hide');
        });

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

        dtable = $('.datatable').DataTable({
            processing: true,
            bStateSave: true,
            serverSide: true,
            scrollX: true,
            language: lang_datatable,
            ajax: '{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/index-data/kiri") !!}',
            columns: [
                { data: 'cekpegawai', name: 'cekpegawai', orderable: false, searchable: false },
                { data: 'nama', name: 'nama' },
                { data: 'pin', name: 'pin' }
            ],
            order: [[1, 'asc']]
        });

        dtable2 = $('.datatablejamkerjashift').DataTable({
            processing: true,
            bStateSave: true,
            serverSide: true,
            scrollX: true,
            language: lang_datatable,
            ajax: '{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/penempatanpegawai/index-data/kanan") !!}',
            columns: [
                /*{ data: 'action', name: 'action', orderable: false, searchable: false },*/
                { data: 'cekpegawai', name: 'cekpegawai', orderable: false, searchable: false },
                { data: 'nama', name: 'nama' },
                { data: 'pin', name: 'pin' }
            ],
            order: [[1, 'asc']]
        });
    });
</script>
@endpush