@extends('layouts.master')
@section('title', trans('all.ijinkansambungdatacapture'))
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

  var dtable = '';
  var dtable2 = '';
  var _token = '{!! csrf_token() !!}';
  var urlkiri = '{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/ijinkansambungdatacapture/index-data/t") !!}';
  var urlkanan = '{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/ijinkansambungdatacapture/index-data/y") !!}';

  function ceksemua(ke){
      if(ke === 1) {
          if ($("#ceksemuapegawai").prop("checked")) {
              $(".cekpegawai").prop("checked", true);
          } else {
              $(".cekpegawai").prop("checked", false);
          }
      }else if(ke === 2){
          if ($("#ceksemuapegawai_"+ke).prop("checked")) {
              $(".cekpegawai_"+ke).prop("checked", true);
          } else {
              $(".cekpegawai_"+ke).prop("checked", false);
          }
          tampiltombolhapus();
      }
  }

  function submit(ijinkansambungdatacapture){
      var elements;
      if(ijinkansambungdatacapture === 'y') {
          //dari kanan ke kiri
          elements = document.getElementsByClassName("cekpegawai");
      }else{
          // dari kiri ke kanan
          elements = document.getElementsByClassName("cekpegawai_2");
      }
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
      s = s.substring(1);
      //jalankan ajax terus load datatable
      $.ajax({
          type: "GET",
          url: '{{ url('generatecsrftoken') }}',
          data: '',
          cache: false,
          success: function (token) {
              var dataString = "idpegawai=" + encodeURIComponent(s)+'&ijinkansambungdatacapture='+ijinkansambungdatacapture+"&_token="+token;
              $.ajax({
                  type: "POST",
                  url: "{!! url("datainduk/pegawai/pegawai/aturatributdanlokasi/ijinkansambungdatacapture/submit") !!}",
                  data: dataString,
                  cache: false,
                  success: function (resp) {
                      if (resp['status'] !== 'ok') {
                          alertError(resp['msg']);
                      }
                      _token = resp['token'];
                      dtable.ajax.url(urlkiri).load();
                      dtable2.ajax.url(urlkanan).load();
                      setTimeout(function(){
                          $("#ceksemuapegawai").prop("checked", false);
                          $("#ceksemuapegawai_2").prop("checked", false);
                      },200);
                  }
              });
          }
      });
      return false;
  }
  </script>
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
          <li class="active"><a href="{{ url('datainduk/pegawai/pegawai/aturatributdanlokasi/ijinkansambungdatacapture') }}">{{ trans('all.ijinkansambungdatacapture') }}</a></li>
        </ul>
          <div class="col-lg-12" style="padding:20px 0">
              <table>
                  <tr>
                      <td>
                          <button type="button" data-toggle="modal" data-target="#modalFilter" class="btn btn-primary"><i class="fa fa-bars"></i>&nbsp;&nbsp;{{ trans('all.filter') }}</button>
                          &nbsp;&nbsp;
                          <button @if(!Session::has('ijinkansambungdatacapture_atribut')) style="display:none" @endif type="button" id="setulang" onclick="return ke('{{ url('datainduk/pegawai/pegawai/aturatributdanlokasi/setulang/ijinkansambungdatacapture') }}')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>
                      </td>
                  </tr>
              </table>
              <div id="alertatribut" class="alert alert-success" style="margin-top:20px;margin-bottom:0;@if(!Session::has('ijinkansambungdatacapture_atribut')) display:none @endif">
                  @if(Session::has('ijinkansambungdatacapture_atribut')) {!! Session::get('ijinkansambungdatacapture_atribut') !!} @endif
              </div>
          </div>
          <table>
              <tr>
                  <td valign='top' width=50%>
                      <div class="ibox float-e-margins">

                          <div class="ibox-content">
                              <table width=100% class="table datatable table-striped table-condensed table-hover">
                                  <thead>
                                  <tr>
                                      <td class='cek'><input type='checkbox' onclick='ceksemua(1)' id='ceksemuapegawai'></td>
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
                                  <button type="button" onclick="return submit('y')" class="btn btn-primary"><i class="fa fa-arrow-right"></i></button>
                              </td>
                          </tr>
                          <tr>
                              <td style="padding-top:10px">
                                  <button type="button" id="buttonhapus" onclick="return submit('t')" class="btn btn-primary"><i class="fa fa-arrow-left"></i></button>
                              </td>
                          </tr>
                      </table>
                  </td>
                  <td valign='top' width=50%>
                      <div class="ibox float-e-margins">

                          <div class="ibox-content">
                              <table width=100% class="table datatableijinkansambungdatacapture table-striped table-condensed table-hover">
                                  <thead>
                                  <tr>
                                      <td class='cek'><input type='checkbox' onclick='ceksemua(2)' id='ceksemuapegawai_2'></td>
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

  <!-- Modal filter-->
  <div class="modal fade" id="modalFilter" role="dialog" tabindex='-1'>
      <div class="modal-dialog @if(count($atribut)<=1) modal-sm @elseif(count($atribut)==2) modal-md @else modal-lg @endif">

          <!-- Modal content-->
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">{{ trans('all.filter').' '.trans('all.atribut') }}</h4>
              </div>
              <div class="modal-body" style="max-height:480px;overflow: auto;">
                  @if(isset($atribut))
                      @for($i=0;$i<count($atribut);$i++)
                          <div class="@if(count($atribut)<=1) col-md-12 @elseif(count($atribut)==2) col-md-6 @else col-md-4 @endif">
                              @if(isset($atribut[$i]['flag']))
                                  <input type="checkbox" class="atributpopup" id="semuaatribut_{{ $atribut[$i]['idatribut'] }}" value="{{ $atribut[$i]['idatribut'] }}" onclick="checkboxallclick('semuaatribut_{{ $atribut[$i]['idatribut'] }}','attr_{{ $atribut[$i]['idatribut'] }}')" @if($atribut[$i]['flag'] == 1) checked @endif>&nbsp;&nbsp;
                              @else
                                  <input type="checkbox" class="atributpopup" id="semuaatribut_{{ $atribut[$i]['idatribut'] }}" value="{{ $atribut[$i]['idatribut'] }}" onclick="checkboxallclick('semuaatribut_{{ $atribut[$i]['idatribut'] }}','attr_{{ $atribut[$i]['idatribut'] }}')">&nbsp;&nbsp;
                              @endif
                              <span style="margin:0" id="spansemuaatribut_{{ $atribut[$i]['idatribut'] }}" onclick="spanallclick('semuaatribut_{{ $atribut[$i]['idatribut'] }}','attr_{{ $atribut[$i]['idatribut'] }}')"><strong>{{ $atribut[$i]['atribut'] }}</strong></span>
                              <table>
                                  @foreach($atribut[$i]['atributnilai'] as $key)
                                      @if($key->enable != 0)
                                          <tr>
                                              <td style="width:20px;padding:2px" valign="top">
                                                  <input type="checkbox" idatribut="{{ $key->idatribut }}" @if($key->dipilih == 1) checked @endif onchange="return checkAllAttr('attr_{{ $key->idatribut }}','semuaatribut_{{ $key->idatribut }}')" class="atributpopup attr_{{ $key->idatribut }}" name="atribut_{{ $key->idatribut }}" id="atributpopup{{ $key->id }}" value="{{ $key->id }}">
                                              </td>
                                              <td style="padding: 2px;">
                                                  <span id="attrpopup_atribut{{ $key->id }}" onclick="spanClick('atributpopup{{ $key->id }}')" atribut="{{ $atribut[$i]['atribut'] }}">{{ $key->nilai }}</span>
                                              </td>
                                          </tr>
                                    @endif
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
                if (atribut[i].id.substring(0,12)!=="semuaatribut") {
                    if (document.getElementById("atributpopup"+atribut[i].value).checked) {
                        filteratributnilai += ','+atribut[i].value;
                    }
                }
            }
            var atributnilai = filteratributnilai === '' ? 'o' : filteratributnilai.substring(1);
            $("#tutupmodal").trigger("click");
            $.ajax({
                type: "GET",
                url: "{{ url('datainduk/pegawai/pegawai/aturatributdanlokasi/ijinkansambungdatacapture/aturatribut') }}/"+atributnilai,
                data: '',
                async: true,
                cache: false,
                contentType: false,
                processData: false,
                success: function(resp){
                    if(resp['data'] !== '') {
                        $('#setulang').css('display', '');
                        $('#alertatribut').css('display', '');
                        $('#alertatribut').html(resp['data']);
                    }else{
                        $('#setulang').css('display', 'none');
                        $('#alertatribut').css('display', 'none');
                        $('#alertatribut').html('');
                    }
                    _token = resp['token'];
                    dtable.ajax.url(urlkiri).load();
                    dtable2.ajax.url(urlkanan).load();
                }
            });
            return false;
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
            ajax: {
                url: urlkiri,
                type: "POST",
                data: { _token: function() { return _token } }
            },
            language: lang_datatable,
            columns: [
                { data: 'cekpegawai', name: 'cekpegawai', orderable: false, searchable: false },
                { data: 'nama', name: 'nama' },
                { data: 'pin', name: 'pin' }
            ],
            order: [[1, 'asc']]
        });

        dtable2 = $('.datatableijinkansambungdatacapture').DataTable({
            processing: true,
            bStateSave: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: urlkanan,
                type: "POST",
                data: { _token: function() { return _token } }
            },
            language: lang_datatable,
            columns: [
                { data: 'cekpegawai', name: 'cekpegawai', orderable: false, searchable: false },
                { data: 'nama', name: 'nama' },
                { data: 'pin', name: 'pin' }
            ],
            order: [[1, 'asc']]
        });
    });
</script>
@endpush